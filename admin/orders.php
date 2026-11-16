<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$db = getDB();

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $oid    = (int)$_POST['order_id'];
    $status = $_POST['status'];
    $allowed = ['Pending','Processing','Shipped','Delivered','Cancelled'];
    if (in_array($status, $allowed)) {
        $db->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$status, $oid]);
    }
    setFlash('success', "Order #$oid status updated to $status.");
    header('Location: orders.php'); exit;
}

$orders = $db->query("
    SELECT o.*, u.name as user_name, u.email as user_email
    FROM orders o JOIN users u ON u.id=o.user_id
    ORDER BY o.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Orders — Admin</title>
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
    <a href="orders.php" class="active"><i data-feather="shopping-bag"></i> Orders</a>
    <a href="users.php"><i data-feather="users"></i> Users</a>
    <a href="categories.php"><i data-feather="tag"></i> Categories</a>
    <a href="promos.php"><i data-feather="percent"></i> Promo Codes</a>
    <div style="border-top:1px solid rgba(255,255,255,.06);margin:1rem 0"></div>
    <a href="<?= SITE_URL ?>/index.php"><i data-feather="home"></i> View Store</a>
    <a href="<?= SITE_URL ?>/pages/logout.php" style="color:#EF4444"><i data-feather="log-out"></i> Logout</a>
  </nav>
</aside>
<main class="admin-main">

<?php $f=getFlash(); if($f) echo "<div class='flash flash-{$f['type']}' style='margin-bottom:1rem'><span>{$f['msg']}</span></div>"; ?>

<div class="admin-topbar">
  <h1>Orders <span style="font-size:1rem;color:var(--text-2)">(<?= count($orders) ?>)</span></h1>
</div>

<div class="table-card">
  <table class="table">
    <thead>
      <tr><th>#</th><th>Customer</th><th>Address</th><th>Total</th><th>Date</th><th>Status</th><th>Update</th></tr>
    </thead>
    <tbody>
    <?php foreach ($orders as $ord): ?>
    <?php
    $items = $db->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON p.id=oi.product_id WHERE oi.order_id=?");
    $items->execute([$ord['id']]);
    $items = $items->fetchAll();
    ?>
    <tr>
      <td><strong>#<?= $ord['id'] ?></strong></td>
      <td>
        <div style="font-weight:600"><?= sanitize($ord['user_name']) ?></div>
        <div style="font-size:.75rem;color:var(--text-3)"><?= sanitize($ord['user_email']) ?></div>
        <div style="font-size:.72rem;color:var(--text-3);margin-top:.2rem">
          <?= implode(', ', array_map(fn($i)=>sanitize($i['name']).'×'.$i['quantity'], $items)) ?>
        </div>
      </td>
      <td style="font-size:.8rem;max-width:160px"><?= sanitize($ord['address']) ?>, <?= sanitize($ord['city']) ?></td>
      <td><strong>$<?= number_format($ord['total_price'],2) ?></strong></td>
      <td style="font-size:.8rem"><?= date('M j, Y', strtotime($ord['created_at'])) ?></td>
      <td><span class="status-badge status-<?= strtolower($ord['status']) ?>"><?= $ord['status'] ?></span></td>
      <td>
        <form method="POST" style="display:flex;gap:.3rem">
          <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">
          <select name="status" class="form-control form-control-sm" style="min-width:110px">
            <?php foreach (['Pending','Processing','Shipped','Delivered','Cancelled'] as $s): ?>
            <option value="<?= $s ?>" <?= $ord['status']===$s?'selected':'' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-sm btn-primary">✓</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/feather-icons"></script>
<script>feather.replace();document.documentElement.setAttribute('data-theme',localStorage.getItem('sz_theme')||'light');</script>
</body></html>
