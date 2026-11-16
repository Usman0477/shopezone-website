<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first.']);
    exit;
}

$code = strtoupper(trim($_POST['code'] ?? ''));
if (!$code) {
    echo json_encode(['success' => false, 'message' => 'Please enter a promo code.']);
    exit;
}

$db   = getDB();
$stmt = $db->prepare("
    SELECT * FROM promo_codes
    WHERE code = ?
      AND is_active = 1
      AND (max_uses IS NULL OR used_count < max_uses)
      AND (expires_at IS NULL OR expires_at >= CURDATE())
    LIMIT 1
");
$stmt->execute([$code]);
$promo = $stmt->fetch();

if (!$promo) {
    echo json_encode(['success' => false, 'message' => 'Invalid or expired promo code.']);
    exit;
}

// Compute cart total
$uid = currentUserId();
$total = $db->prepare("
    SELECT COALESCE(SUM(COALESCE(p.sale_price,p.price)*c.quantity),0)
    FROM cart c JOIN products p ON p.id=c.product_id
    WHERE c.user_id=?
");
$total->execute([$uid]);
$cartTotal = (float)$total->fetchColumn();

if ($cartTotal < $promo['min_order']) {
    echo json_encode(['success' => false, 'message' => 'Minimum order of $' . number_format($promo['min_order'],2) . ' required.']);
    exit;
}

$discount = round($cartTotal * $promo['discount_percent'] / 100, 2);
$newTotal = max(0, $cartTotal - $discount);

$_SESSION['cart_discount'] = $discount;
$_SESSION['promo_code']    = $code;

echo json_encode([
    'success'   => true,
    'message'   => $promo['discount_percent'] . '% discount applied!',
    'discount'  => $discount,
    'new_total' => $newTotal,
]);
