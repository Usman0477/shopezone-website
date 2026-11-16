<?php
$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';
$db = getDB();

// Featured products
$featured = $db->query("
    SELECT p.*, c.name as cat_name,
           COALESCE(AVG(r.rating),0) as avg_rating,
           COUNT(r.id) as review_count
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    LEFT JOIN reviews r ON r.product_id = p.id
    WHERE p.stock > 0
    GROUP BY p.id
    ORDER BY p.featured DESC, p.created_at DESC
    LIMIT 8
")->fetchAll();

// Categories
$categories = $db->query("
    SELECT c.*, COUNT(p.id) as product_count
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id
    GROUP BY c.id
")->fetchAll();

// Sale products
$saleItems = $db->query("
    SELECT p.*, c.name as cat_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.sale_price IS NOT NULL AND p.stock > 0
    LIMIT 4
")->fetchAll();
?>

<!-- Hero -->
<section class="hero">
  <div class="hero-grid"></div>
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-6 hero-content">
        <div class="hero-eyebrow">
          <span>⚡</span> New arrivals every week
        </div>
        <h1>Shop <em>Smarter,</em><br>Live Better.</h1>
        <p>Discover thousands of quality products at unbeatable prices. Fast shipping, easy returns, and a shopping experience you'll love.</p>
        <div class="hero-cta">
          <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-primary btn-lg">Browse Shop</a>
          <a href="<?= SITE_URL ?>/pages/shop.php?sale=1" class="btn btn-outline-light btn-lg">View Sales</a>
        </div>
        <div class="hero-stats">
          <div class="hero-stat"><span>10K+</span><span>Products</span></div>
          <div class="hero-stat"><span>50K+</span><span>Happy Customers</span></div>
          <div class="hero-stat"><span>4.9★</span><span>Avg Rating</span></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Categories -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <span class="section-eyebrow">Browse by category</span>
      <h2>What are you looking for?</h2>
    </div>
    <div class="row g-3">
      <?php foreach ($categories as $cat): ?>
      <div class="col-6 col-sm-4 col-lg-2">
        <a class="cat-card" href="<?= SITE_URL ?>/pages/shop.php?category=<?= $cat['id'] ?>">
          <div class="cat-icon"><i data-feather="<?= sanitize($cat['icon']) ?>"></i></div>
          <div class="cat-name"><?= sanitize($cat['name']) ?></div>
          <div style="font-size:.75rem;color:var(--text-3);margin-top:.2rem"><?= $cat['product_count'] ?> items</div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Featured Products -->
<section class="section section-alt">
  <div class="container">
    <div class="section-header d-flex justify-content-between align-items-end flex-wrap gap-2">
      <div>
        <span class="section-eyebrow">Handpicked for you</span>
        <h2>Featured Products</h2>
      </div>
      <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-outline-primary">View All →</a>
    </div>
    <div class="row g-3">
      <?php foreach ($featured as $p): ?>
      <div class="col-sm-6 col-lg-3">
        <?php include __DIR__ . '/includes/product_card.php'; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Promo Banner -->
<section class="section">
  <div class="container">
    <div class="row g-3">
      <div class="col-md-6">
        <div style="background:var(--nav-bg);border-radius:var(--radius-lg);padding:2.5rem;position:relative;overflow:hidden;height:100%">
          <div style="position:absolute;right:-20px;top:-20px;width:160px;height:160px;background:rgba(245,158,11,.08);border-radius:50%"></div>
          <span class="section-eyebrow">Limited time</span>
          <h3 style="color:#F5F0EB;margin:.5rem 0">Up to 30% Off<br>Electronics</h3>
          <p style="color:rgba(245,240,235,.6);margin-bottom:1.5rem">Use code <strong style="color:var(--accent)">MEGA30</strong> at checkout.</p>
          <a href="<?= SITE_URL ?>/pages/shop.php?category=1" class="btn btn-primary">Shop Electronics</a>
        </div>
      </div>
      <div class="col-md-6">
        <div style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.2);border-radius:var(--radius-lg);padding:2.5rem;height:100%">
          <span class="section-eyebrow">Free shipping</span>
          <h3 style="margin:.5rem 0">Orders Over $50</h3>
          <p style="color:var(--text-2);margin-bottom:1.5rem">Fast, reliable delivery to your doorstep — no minimum fuss.</p>
          <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-outline-primary">Start Shopping</a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php if (!empty($saleItems)): ?>
<!-- On Sale -->
<section class="section section-alt">
  <div class="container">
    <div class="section-header d-flex justify-content-between align-items-end flex-wrap gap-2">
      <div>
        <span class="section-eyebrow">🔥 Hot deals</span>
        <h2>On Sale Now</h2>
      </div>
      <a href="<?= SITE_URL ?>/pages/shop.php?sale=1" class="btn btn-outline-primary">All Sales →</a>
    </div>
    <div class="row g-3">
      <?php foreach ($saleItems as $p): ?>
      <div class="col-sm-6 col-lg-3">
        <?php include __DIR__ . '/includes/product_card.php'; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
