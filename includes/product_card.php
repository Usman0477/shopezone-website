<?php
// Expects $p = product row with cat_name, avg_rating, review_count
// avg_rating / review_count optional
$avg  = isset($p['avg_rating'])    ? (float)$p['avg_rating']    : 0;
$rcnt = isset($p['review_count'])  ? (int)$p['review_count']    : 0;
$hasSale = !empty($p['sale_price']) && $p['sale_price'] < $p['price'];
$displayPrice = $hasSale ? $p['sale_price'] : $p['price'];
$discount = $hasSale ? round((1 - $p['sale_price'] / $p['price']) * 100) : 0;
?>
<div class="product-card">
  <div class="product-card-img">
    <a href="<?= SITE_URL ?>/pages/product.php?id=<?= $p['id'] ?>">
      <img src="<?= getProductImage($p['image'] ?? '') ?>" alt="<?= sanitize($p['name']) ?>" loading="lazy">
    </a>
    <div class="product-card-badges">
      <?php if ($hasSale): ?><span class="badge-sale">-<?= $discount ?>%</span><?php endif; ?>
      <?php if (!empty($p['featured'])): ?><span class="badge-new">Featured</span><?php endif; ?>
    </div>
    <div class="product-card-actions">
      <button data-toggle-wish="<?= $p['id'] ?>" title="Add to wishlist">
        <i data-feather="heart"></i>
      </button>
      <button onclick="location.href='<?= SITE_URL ?>/pages/product.php?id=<?= $p['id'] ?>'" title="Quick view">
        <i data-feather="eye"></i>
      </button>
    </div>
  </div>
  <div class="product-card-body">
    <?php if (!empty($p['cat_name'])): ?>
    <div class="product-cat"><?= sanitize($p['cat_name']) ?></div>
    <?php endif; ?>
    <a href="<?= SITE_URL ?>/pages/product.php?id=<?= $p['id'] ?>" style="text-decoration:none;color:inherit">
      <div class="product-name"><?= sanitize($p['name']) ?></div>
    </a>
    <?php if (!empty($p['short_description'])): ?>
    <div class="product-desc"><?= sanitize($p['short_description']) ?></div>
    <?php endif; ?>
    <?php if ($rcnt > 0): ?>
    <div class="product-rating">
      <?= getStarRating($avg) ?>
      <span class="rating-count">(<?= $rcnt ?>)</span>
    </div>
    <?php endif; ?>
    <div class="product-price">
      <span class="price-current"><?= formatPrice($displayPrice) ?></span>
      <?php if ($hasSale): ?>
        <span class="price-original"><?= formatPrice($p['price']) ?></span>
        <span class="price-sale">-<?= $discount ?>%</span>
      <?php endif; ?>
    </div>
    <div class="product-card-footer">
      <button class="btn-cart" data-add-cart="<?= $p['id'] ?>">
        <i data-feather="shopping-cart"></i> Add to Cart
      </button>
      <a href="<?= SITE_URL ?>/pages/checkout.php?buy_now=<?= $p['id'] ?>" class="btn-buy">Buy Now</a>
    </div>
  </div>
</div>
