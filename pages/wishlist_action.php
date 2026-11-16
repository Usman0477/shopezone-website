<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success'=>false,'message'=>'Please login.','redirect'=> SITE_URL . '/pages/login.php']);
    exit;
}

$productId = (int)($_POST['product_id'] ?? 0);
$uid = currentUserId();
$db  = getDB();

// Toggle
$chk = $db->prepare("SELECT id FROM wishlist WHERE user_id=? AND product_id=?");
$chk->execute([$uid, $productId]);
if ($chk->fetch()) {
    $db->prepare("DELETE FROM wishlist WHERE user_id=? AND product_id=?")->execute([$uid, $productId]);
    echo json_encode(['success'=>true,'wishlisted'=>false]);
} else {
    $db->prepare("INSERT IGNORE INTO wishlist (user_id,product_id) VALUES (?,?)")->execute([$uid, $productId]);
    echo json_encode(['success'=>true,'wishlisted'=>true]);
}
