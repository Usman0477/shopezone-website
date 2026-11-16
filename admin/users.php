<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$db = getDB();
$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Users — Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:opsz,wght@9..40,400;9..40,500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="admin-layout">
<aside class="admin-sidebar">
  <div class="brand"><span>⬡</span> <?= SITE_NAME ?></div>
  <nav class="admin-nav">
    <a href="dashboard.php"><i data-feather="layout"></i> Dashboard</a>
    <a href="products.php"><i data-feather="box"></i> Products</a>
    <a href="orders.php"><i data-feather="shopping-bag"></i> Orders</a>
    <a href="users.php" class="active"><i data-feather="users"></i> Users</a>
    <a href="categories.php"><i data-feather="tag"></i> Categories</a>
    <a href="promos.php"><i data-feather="percent"></i> Promo Codes</a>
    <div style="border-top:1px solid rgba(255,255,255,.06);margin:1rem 0"></div>
    <a href="<?= SITE_URL ?>/index.php"><i data-feather="home"></i> View Store</a>
    <a href="<?= SITE_URL ?>/pages/logout.php" style="color:#EF4444"><i data-feather="log-out"></i> Logout</a>
  </nav>
</aside>
<main class="admin-main">
<div class="admin-topbar">
  <h1>Users <span style="font-size:1rem;color:var(--text-2)">(<?= count($users) ?>)</span></h1>
</div>
<div class="table-card">
  <table class="table">
    <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Verified</th><th>Joined</th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
    <tr>
      <td><?= $u['id'] ?></td>
      <td><strong><?= sanitize($u['name']) ?></strong></td>
      <td style="font-size:.85rem"><?= sanitize($u['email']) ?></td>
      <td>
        <span class="status-badge <?= $u['role']==='admin' ? 'status-shipped' : 'status-delivered' ?>">
          <?= $u['role'] ?>
        </span>
      </td>
      <td><?= $u['is_verified'] ? '✓' : '✗' ?></td>
      <td style="font-size:.8rem"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
</main>
</div>
<script src="https://unpkg.com/feather-icons"></script>
<script>feather.replace();document.documentElement.setAttribute('data-theme',localStorage.getItem('sz_theme')||'light');</script>
</body></html>
