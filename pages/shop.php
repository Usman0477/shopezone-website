<?php
$pageTitle = 'Shop';
require_once __DIR__ . '/../includes/header.php';
$db = getDB();

// Filters
$q        = trim($_GET['q'] ?? '');
$catId    = (int)($_GET['category'] ?? 0);
$sort     = $_GET['sort'] ?? 'newest';
$sale     = !empty($_GET['sale']);
$featured = !empty($_GET['featured']);
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 12;
$offset   = ($page - 1) * $perPage;

// Build query
$where  = ['p.stock > 0'];
$params = [];

if ($q) { $where[] = "(p.name LIKE ? OR p.description LIKE ?)"; $params[] = "%$q%"; $params[] = "%$q%"; }
if ($catId) { $where[] = "p.category_id = ?"; $params[] = $catId; }
if ($sale)  { $where[] = "p.sale_price IS NOT NULL"; }
if ($featured) { $where[] = "p.featured = 1"; }

$whereSQL = 'WHERE ' . implode(' AND ', $where);

$orderMap = [
    'newest'     => 'p.created_at DESC',
    'price_asc'  => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'rating'     => 'avg_rating DESC',
    'popular'    => 'review_count DESC',
];
$orderSQL = $orderMap[$sort] ?? $orderMap['newest'];

// Total count
$countStmt = $db->prepare("SELECT COUNT(*) FROM products p LEFT JOIN categories c ON c.id = p.category_id $whereSQL");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($total / $perPage));

// Products
$stmt = $db->prepare("
    SELECT p.*, c.name as cat_name,
           COALESCE(AVG(r.rating),0) as avg_rating,
           COUNT(r.id) as review_count
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    LEFT JOIN reviews r ON r.product_id = p.id
    $whereSQL
    GROUP BY p.id
    ORDER BY $orderSQL
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$activeCat  = $catId ? $db->prepare("SELECT name FROM categories WHERE id=?") : null;
if ($activeCat) { $activeCat->execute([$catId]); $activeCat = $activeCat->fetchColumn(); }
?>

<div class="page-header">
  <h1><?= $q ? 'Search: "' . sanitize($q) . '"' : ($activeCat ?: 'All Products') ?></h1>
  <p><?= $total ?> product<?= $total != 1 ? 's' : '' ?> found</p>
</div>

<section class="section">
  <div class="container">

    <!-- Filter Bar -->
    <div class="filter-bar">
      <span class="filter-label">Filter:</span>
      <a class="filter-tag <?= !$catId && !$sale && !$featured ? 'active' : '' ?>" href="?<?= $q ? 'q='.urlencode($q).'&' : '' ?>sort=<?= $sort ?>">All</a>
      <?php foreach ($categories as $cat): ?>
      <a class="filter-tag <?= $catId == $cat['id'] ? 'active' : '' ?>"
         href="?category=<?= $cat['id'] ?>&sort=<?= $sort ?><?= $q ? '&q='.urlencode($q) : '' ?>"><?= sanitize($cat['name']) ?></a>
      <?php endforeach; ?>
      <a class="filter-tag <?= $sale ? 'active' : '' ?>" href="?sale=1&sort=<?= $sort ?>">🔥 Sale</a>
      <a class="filter-tag <?= $featured ? 'active' : '' ?>" href="?featured=1&sort=<?= $sort ?>">⭐ Featured</a>

      <div class="ms-auto d-flex align-items-center gap-2">
        <span class="filter-label">Sort:</span>
        <select class="form-control form-control-sm" style="width:auto;padding:.3rem .7rem"
                onchange="location.href=this.value">
          <?php
          $base = '?' . http_build_query(array_merge($_GET, ['sort' => '']));
          $sorts = ['newest'=>'Newest','price_asc'=>'Price ↑','price_desc'=>'Price ↓','rating'=>'Top Rated','popular'=>'Most Reviewed'];
          foreach ($sorts as $k => $label):
            $url = '?' . http_build_query(array_merge($_GET, ['sort' => $k]));
          ?>
          <option value="<?= $url ?>" <?= $sort === $k ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <!-- Grid -->
    <?php if (empty($products)): ?>
    <div class="text-center py-5">
      <i data-feather="search" style="width:64px;height:64px;color:var(--text-3)"></i>
      <h3 class="mt-3">No products found</h3>
      <p class="text-muted">Try adjusting your filters or search query.</p>
      <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-primary mt-2">Clear Filters</a>
    </div>
    <?php else: ?>
    <div class="row g-3">
      <?php foreach ($products as $p): ?>
      <div class="col-sm-6 col-lg-4 col-xl-3">
        <?php include __DIR__ . '/../includes/product_card.php'; ?>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav class="mt-5 d-flex justify-content-center">
      <ul class="pagination">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
          <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page-1])) ?>">←</a>
        </li>
        <?php for ($i = max(1,$page-2); $i <= min($totalPages,$page+2); $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
          <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
          <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page+1])) ?>">→</a>
        </li>
      </ul>
    </nav>
    <?php endif; ?>
    <?php endif; ?>

  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
