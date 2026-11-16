<?php
$pageTitle = 'My Wishlist';
require_once __DIR__ . '/../includes/header.php';
requireLogin();
$db  = getDB();
$uid = currentUserId();

$items = $db->prepare("
    SELECT p.*, c.name as cat_name,
           COALESCE(AVG(r.rating),0) as avg_rating,
           COUNT(r.id) as review_count
    FROM wishlist w
    JOIN products p ON p.id = w.product_id
    LEFT JOIN categories c ON c.id = p.category_id
    LEFT JOIN reviews r ON r.product_id = p.id
    WHERE w.user_id = ?
    GROUP BY p.id
    ORDER BY w.created_at DESC
");
$items->execute([$uid]);
$items = $items->fetchAll();
?>

<div class="page-header">
  <h1>My Wishlist</h1>
  <p><?= count($items) ?> saved item<?= count($items) != 1 ? 's' : '' ?></p>
</div>

<section class="section">
  <div class="container">
    <?php if (empty($items)): ?>
    <div class="text-center py-5">
      <i data-feather="heart" style="width:64px;height:64px;color:var(--text-3)"></i>
      <h3 class="mt-3">Your wishlist is empty</h3>
      <p class="text-muted">Save products you love for later.</p>
      <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-primary mt-2">Discover Products</a>
    </div>
    <?php else: ?>
    <div class="row g-3">
      <?php foreach ($items as $p): ?>
      <div class="col-sm-6 col-lg-3">
        <?php include __DIR__ . '/../includes/product_card.php'; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
