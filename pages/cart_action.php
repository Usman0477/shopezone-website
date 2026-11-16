<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success'=>false,'message'=>'Please login to use cart.','redirect'=> SITE_URL . '/pages/login.php']);
    exit;
}

$action    = $_POST['action'] ?? '';
$productId = (int)($_POST['product_id'] ?? 0);
$qty       = max(1, (int)($_POST['quantity'] ?? 1));
$uid       = currentUserId();
$db        = getDB();

function cartTotal(PDO $db, int $uid): float {
    $stmt = $db->prepare("SELECT COALESCE(SUM(COALESCE(p.sale_price,p.price)*c.quantity),0) FROM cart c JOIN products p ON p.id=c.product_id WHERE c.user_id=?");
    $stmt->execute([$uid]);
    return (float)$stmt->fetchColumn();
}

function cartCount(PDO $db, int $uid): int {
    $stmt = $db->prepare("SELECT COALESCE(SUM(quantity),0) FROM cart WHERE user_id=?");
    $stmt->execute([$uid]);
    return (int)$stmt->fetchColumn();
}

try {
    switch ($action) {
        case 'add':
            $p = $db->prepare("SELECT stock FROM products WHERE id=?");
            $p->execute([$productId]);
            $prod = $p->fetch();
            if (!$prod || $prod['stock'] < 1) {
                echo json_encode(['success'=>false,'message'=>'Product out of stock']);
                exit;
            }
            $db->prepare("INSERT INTO cart (user_id,product_id,quantity) VALUES (?,?,?)
                          ON DUPLICATE KEY UPDATE quantity = LEAST(quantity+VALUES(quantity), ?)")
               ->execute([$uid, $productId, $qty, $prod['stock']]);
            break;

        case 'update':
            $db->prepare("UPDATE cart SET quantity=? WHERE user_id=? AND product_id=?")
               ->execute([$qty, $uid, $productId]);
            // item total
            $it = $db->prepare("SELECT COALESCE(p.sale_price,p.price)*? as t FROM products p WHERE p.id=?");
            $it->execute([$qty,$productId]);
            $itemTotal = $it->fetchColumn();
            echo json_encode([
                'success'     => true,
                'cart_count'  => cartCount($db,$uid),
                'cart_total'  => cartTotal($db,$uid),
                'item_total'  => $itemTotal,
            ]);
            exit;

        case 'remove':
            $db->prepare("DELETE FROM cart WHERE user_id=? AND product_id=?")->execute([$uid,$productId]);
            break;

        default:
            echo json_encode(['success'=>false,'message'=>'Unknown action']);
            exit;
    }

    echo json_encode([
        'success'    => true,
        'cart_count' => cartCount($db,$uid),
        'cart_total' => cartTotal($db,$uid),
    ]);
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
