<?php
$pageTitle = 'Checkout';
require_once __DIR__ . '/../includes/header.php';
requireLogin();
$db  = getDB();
$uid = currentUserId();

// Buy Now single product
$buyNow = (int)($_GET['buy_now'] ?? 0);
if ($buyNow) {
    $prod = $db->prepare("SELECT * FROM products WHERE id=? AND stock>0");
    $prod->execute([$buyNow]);
    $prod = $prod->fetch();
    $items = $prod ? [array_merge($prod, ['quantity'=>1,'effective_price'=>$prod['sale_price'] ?? $prod['price']])] : [];
} else {
    $stmt = $db->prepare("
        SELECT c.quantity, p.*,
               COALESCE(p.sale_price, p.price) as effective_price
        FROM cart c
        JOIN products p ON p.id = c.product_id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$uid]);
    $items = $stmt->fetchAll();
}

if (empty($items)) {
    setFlash('error', 'Your cart is empty.');
    header('Location: ' . SITE_URL . '/pages/cart.php');
    exit;
}

$subtotal = array_sum(array_map(fn($i) => $i['effective_price'] * $i['quantity'], $items));
$discount = $_SESSION['cart_discount'] ?? 0;
$promoCode = $_SESSION['promo_code'] ?? '';
$shipping = $subtotal >= 50 ? 0 : 5.00;
$total = $subtotal - $discount + $shipping;

// Place order
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city    = trim($_POST['city'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');

    if (!$name || !$address || !$phone) {
        setFlash('error', 'Please fill all required fields.');
    } else {
        $user = $db->prepare("SELECT email FROM users WHERE id=?");
        $user->execute([$uid]);
        $email = $user->fetchColumn();

        $ins = $db->prepare("INSERT INTO orders (user_id,name,email,address,city,phone,promo_code,discount_amount,subtotal,total_price) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $ins->execute([$uid,$name,$email,$address,$city,$phone,$promoCode,$discount,$subtotal,$total]);
        $orderId = $db->lastInsertId();

        foreach ($items as $item) {
            $db->prepare("INSERT INTO order_items (order_id,product_id,quantity,price) VALUES (?,?,?,?)")
               ->execute([$orderId,$item['id'],$item['quantity'],$item['effective_price']]);
            // Deduct stock
            $db->prepare("UPDATE products SET stock = stock - ? WHERE id=?")->execute([$item['quantity'],$item['id']]);
        }

        // Clear cart
        if (!$buyNow) {
            $db->prepare("DELETE FROM cart WHERE user_id=?")->execute([$uid]);
        }
        unset($_SESSION['cart_discount'], $_SESSION['promo_code']);

        setFlash('success', "🎉 Order #$orderId placed successfully! Thank you, $name.");
        header('Location: ' . SITE_URL . '/pages/orders.php');
        exit;
    }
}

$user = $db->prepare("SELECT * FROM users WHERE id=?");
$user->execute([$uid]);
$user = $user->fetch();
?>

<div class="page-header">
  <h1>Checkout</h1>
  <p>Review and confirm your order</p>
</div>

<section class="section" style="padding-top:2rem">
  <div class="container">
    <div class="row gy-4">
      <!-- Shipping Form -->
      <div class="col-lg-7">
        <div style="background:var(--card-bg);border:1px solid var(--border);border-radius:var(--radius-lg);padding:2rem">
          <h5 style="margin-bottom:1.5rem">Shipping Information</h5>
          <form method="POST" id="checkoutForm">
            <div class="mb-3">
              <label class="form-label">Full Name *</label>
              <input type="text" name="name" class="form-control" required value="<?= sanitize($user['name']) ?>" placeholder="Jane Doe">
            </div>
            <div class="mb-3">
              <label class="form-label">Phone Number *</label>
              <input type="tel" name="phone" class="form-control" required placeholder="+1 234 567 8900">
            </div>
            <div class="mb-3">
              <label class="form-label">City</label>
              <input type="text" name="city" class="form-control" placeholder="New York">
            </div>
            <div class="mb-4">
              <label class="form-label">Full Address *</label>
              <textarea name="address" class="form-control" rows="3" required placeholder="Street, Building, Zip..."></textarea>
            </div>
            <div style="background:var(--bg-2);border:1px solid var(--border);border-radius:var(--radius);padding:1.25rem;margin-bottom:1.5rem">
              <h6 style="margin-bottom:1rem">Order Summary</h6>
              <?php foreach ($items as $item): ?>
              <div style="display:flex;justify-content:space-between;font-size:.875rem;margin-bottom:.4rem">
                <span><?= sanitize($item['name']) ?> × <?= $item['quantity'] ?></span>
                <span><?= formatPrice($item['effective_price'] * $item['quantity']) ?></span>
              </div>
              <?php endforeach; ?>
              <hr style="border-color:var(--border)">
              <div style="display:flex;justify-content:space-between;font-size:.875rem"><span>Subtotal</span><span><?= formatPrice($subtotal) ?></span></div>
              <?php if ($discount > 0): ?>
              <div style="display:flex;justify-content:space-between;font-size:.875rem;color:#065F46"><span>Discount (<?= $promoCode ?>)</span><span>-<?= formatPrice($discount) ?></span></div>
              <?php endif; ?>
              <div style="display:flex;justify-content:space-between;font-size:.875rem"><span>Shipping</span><span><?= $shipping > 0 ? formatPrice($shipping) : 'FREE' ?></span></div>
              <div style="display:flex;justify-content:space-between;font-family:'Syne',sans-serif;font-weight:800;font-size:1.1rem;margin-top:.5rem;padding-top:.5rem;border-top:1px solid var(--border)">
                <span>Total</span><span><?= formatPrice($total) ?></span>
              </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 btn-lg">Place Order →</button>
            <a href="<?= SITE_URL ?>/pages/cart.php" class="btn btn-outline-primary w-100 mt-2">← Back to Cart</a>
          </form>
        </div>
      </div>

      <!-- Trust -->
      <div class="col-lg-5">
        <div class="summary-card">
          <h6 style="margin-bottom:1rem">Why Shop With Us?</h6>
          <?php
          $trust = [
            ['shield','Secure Checkout','Your data is encrypted and safe.'],
            ['truck','Fast Delivery','Orders shipped within 1-2 business days.'],
            ['refresh-cw','Easy Returns','30-day hassle-free returns.'],
            ['headphones','24/7 Support','We\'re here when you need us.'],
          ];
          foreach ($trust as [$icon,$title,$desc]):
          ?>
          <div style="display:flex;gap:.75rem;margin-bottom:1rem">
            <div style="width:36px;height:36px;background:rgba(245,158,11,.1);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--accent)">
              <i data-feather="<?= $icon ?>"></i>
            </div>
            <div>
              <div style="font-weight:700;font-size:.875rem"><?= $title ?></div>
              <div style="font-size:.8rem;color:var(--text-2)"><?= $desc ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
