<?php
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$db = getDB();

$stats = [
    'revenue'  => $db->query("SELECT COALESCE(SUM(total_price),0) FROM orders WHERE status != 'Cancelled'")->fetchColumn(),
    'orders'   => $db->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'products' => $db->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'users'    => $db->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn(),
];

$recentOrders = $db->query("
    SELECT o.*, u.name as user_name
    FROM orders o JOIN users u ON u.id=o.user_id
    ORDER BY o.created_at DESC LIMIT 8
")->fetchAll();

// Monthly revenue for chart (last 6 months)
$monthly = $db->query("
    SELECT DATE_FORMAT(created_at,'%b') as month,
           COALESCE(SUM(total_price),0) as total
    FROM orders
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
      AND status != 'Cancelled'
    GROUP BY YEAR(created_at), MONTH(created_at)
    ORDER BY created_at ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin — <?= SITE_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="admin-layout">
  <!-- Sidebar -->
  <aside class="admin-sidebar">
    <div class="brand"><span>⬡</span> <?= SITE_NAME ?></div>
    <nav class="admin-nav">
      <a href="dashboard.php" class="active"><i data-feather="layout"></i> Dashboard</a>
      <a href="products.php"><i data-feather="box"></i> Products</a>
      <a href="orders.php"><i data-feather="shopping-bag"></i> Orders</a>
      <a href="users.php"><i data-feather="users"></i> Users</a>
      <a href="categories.php"><i data-feather="tag"></i> Categories</a>
      <a href="promos.php"><i data-feather="percent"></i> Promo Codes</a>
      <div style="border-top:1px solid rgba(255,255,255,.06);margin:1rem 0"></div>
      <a href="<?= SITE_URL ?>/index.php"><i data-feather="home"></i> View Store</a>
      <a href="<?= SITE_URL ?>/pages/logout.php" style="color:#EF4444"><i data-feather="log-out"></i> Logout</a>
    </nav>
  </aside>

  <!-- Main -->
  <main class="admin-main">
    <div class="admin-topbar">
      <div>
        <h1>Dashboard</h1>
        <p style="color:var(--text-2);margin:0;font-size:.875rem">Welcome back, <?= sanitize($_SESSION['user_name']) ?>!</p>
      </div>
      <a href="products.php?action=add" class="btn btn-primary"><i data-feather="plus"></i> Add Product</a>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
      <?php
      $cards = [
        ['Total Revenue', '$'.number_format($stats['revenue'],2), 'dollar-sign', '#D1FAE5', '#065F46'],
        ['Total Orders',  $stats['orders'],   'shopping-bag', '#DBEAFE', '#1E40AF'],
        ['Products',      $stats['products'], 'box',          '#EDE9FE', '#5B21B6'],
        ['Customers',     $stats['users'],    'users',        '#FEF3C7', '#92400E'],
      ];
      foreach ($cards as [$label,$val,$icon,$bg,$color]):
      ?>
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
          <div class="stat-icon" style="background:<?= $bg ?>">
            <i data-feather="<?= $icon ?>" style="color:<?= $color ?>"></i>
          </div>
          <div>
            <div class="stat-label"><?= $label ?></div>
            <div class="stat-value"><?= $val ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Chart + Recent -->
    <div class="row g-3">
      <div class="col-lg-7">
        <div class="table-card p-3" style="height:100%">
          <h5 style="margin-bottom:1rem">Revenue (last 6 months)</h5>
          <canvas id="revenueChart"></canvas>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="table-card">
          <div style="padding:1rem 1rem .5rem;font-family:'Syne',sans-serif;font-weight:700">Recent Orders</div>
          <table class="table table-sm">
            <thead><tr><th>#</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($recentOrders as $ord): ?>
            <tr>
              <td>#<?= $ord['id'] ?></td>
              <td style="font-size:.82rem"><?= sanitize($ord['user_name']) ?></td>
              <td><strong>$<?= number_format($ord['total_price'],2) ?></strong></td>
              <td><span class="status-badge status-<?= strtolower($ord['status']) ?>"><?= $ord['status'] ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
          <div style="padding:.75rem;text-align:center">
            <a href="orders.php" style="font-size:.85rem">View all orders →</a>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/feather-icons"></script>
<script>
feather.replace();
const months = <?= json_encode(array_column($monthly,'month')) ?>;
const totals = <?= json_encode(array_map(fn($r)=>(float)$r['total'], $monthly)) ?>;
new Chart(document.getElementById('revenueChart'), {
  type: 'bar',
  data: {
    labels: months,
    datasets: [{
      label: 'Revenue ($)',
      data: totals,
      backgroundColor: 'rgba(245,158,11,.25)',
      borderColor: '#F59E0B',
      borderWidth: 2,
      borderRadius: 6,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      y: { beginAtZero: true, grid: { color: 'rgba(128,128,128,.1)' }, ticks: { callback: v => '$'+v } },
      x: { grid: { display: false } }
    }
  }
});
// Apply saved theme
const t = localStorage.getItem('sz_theme') || 'light';
document.documentElement.setAttribute('data-theme', t);
</script>
</body>
</html>
