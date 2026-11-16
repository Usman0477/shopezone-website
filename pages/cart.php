<?php
$pageTitle = 'My Cart';
require_once __DIR__ . '/../includes/header.php';
requireLogin();
$db = getDB();
$uid = currentUserId();

$items = $db->prepare("
    SELECT c.quantity, p.*,
           COALESCE(p.sale_price, p.price) as effective_price
    FROM cart c
    JOIN products p ON p.id = c.product_id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
");
$items->execute([$uid]);
$items = $items->fetchAll();

$subtotal = array_sum(array_map(fn($i) => $i['effective_price'] * $i['quantity'], $items));
$discount = $_SESSION['cart_discount'] ?? 0;
$total    = $subtotal - $discount;
?>

<div class="page-header">
  <h1>Shopping Cart</h1>
  <p><?= count($items) ?> item<?= count($items) != 1 ? 's' : '' ?> in your cart</p>
</div>

<section class="section" style="padding-top:2rem">
  <div class="container">
    <?php if (empty($items)): ?>
    <div class="text-center py-5">
      <i data-feather="shopping-cart" style="width:64px;height:64px;color:var(--text-3)"></i>
      <h3 class="mt-3">Your cart is empty</h3>
      <p class="text-muted">Start shopping to add items.</p>
      <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-primary mt-2">Browse Shop</a>
    </div>
    <?php else: ?>
    <div class="row gy-4">
      <!-- Items -->
      <div class="col-lg-8">
        <?php foreach ($items as $item): ?>
        <div class="cart-item" id="cart-row-<?= $item['id'] ?>" style="transition:opacity .3s">
          <a href="<?= SITE_URL ?>/pages/product.php?id=<?= $item['id'] ?>">
            <img src="<?= getProductImage($item['image'] ?? '') ?>" alt="<?= sanitize($item['name']) ?>">
          </a>
          <div class="cart-item-info">
            <h6><?= sanitize($item['name']) ?></h6>
            <div style="color:var(--text-2);font-size:.8rem;margin-bottom:.4rem">
              <?= formatPrice($item['effective_price']) ?> each
            </div>
            <div class="qty-control">
              <button class="qty-btn" data-cart-qty='{"productId":<?= $item['id'] ?>,"dir":"down"}'>−</button>
              <span class="qty-num" id="qty-<?= $item['id'] ?>"><?= $item['quantity'] ?></span>
              <button class="qty-btn" data-cart-qty='{"productId":<?= $item['id'] ?>,"dir":"up"}'>+</button>
            </div>
          </div>
          <div style="text-align:right">
            <div style="font-family:'Syne',sans-serif;font-weight:800;font-size:1.1rem" id="item-total-<?= $item['id'] ?>">
              <?= formatPrice($item['effective_price'] * $item['quantity']) ?>
            </div>
            <button class="btn btn-sm mt-2"
                    style="color:#EF4444;background:none;border:none;font-size:.8rem;cursor:pointer"
                    data-remove-cart="<?= $item['id'] ?>">
              <i data-feather="trash-2" style="width:14px;height:14px"></i> Remove
            </button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Summary -->
      <div class="col-lg-4">
        <div class="summary-card">
          <h5 style="margin-bottom:1rem">Order Summary</h5>
          <div class="summary-row"><span>Subtotal</span><span id="cart-total"><?= formatPrice($subtotal) ?></span></div>
          <div class="summary-row" id="discount-row" <?= $discount > 0 ? '' : 'style="display:none"' ?>>
            <span style="color:#065F46">Discount</span>
            <span id="discount-val" style="color:#065F46">-<?= formatPrice($discount) ?></span>
          </div>
          <div class="summary-row"><span>Shipping</span><span style="color:#065F46"><?= $subtotal >= 50 ? 'FREE' : '$5.00' ?></span></div>

          <!-- Promo -->
          <form class="promo-input" id="promoForm">
            <input class="form-control" type="text" id="promoCode" placeholder="Promo code">
            <button type="submit" class="btn btn-outline-primary">Apply</button>
          </form>
          <div id="promoMsg" style="font-size:.8rem;margin-top:.25rem"></div>

          <div class="summary-row summary-total">
            <span>Total</span>
            <span id="summary-total"><?= formatPrice($total) ?></span>
          </div>
          <a href="<?= SITE_URL ?>/pages/checkout.php" class="btn btn-primary w-100 btn-lg mt-2">
            Proceed to Checkout →
          </a>
          <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-outline-primary w-100 mt-2">← Continue Shopping</a>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
