<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $db->prepare("DELETE FROM promo_codes WHERE id=?")->execute([(int)$_POST['delete_id']]);
        setFlash('success','Promo code deleted.');
    } else {
        $code     = strtoupper(trim($_POST['code'] ?? ''));
        $disc     = (int)$_POST['discount_percent'];
        $minOrder = (float)$_POST['min_order'];
        $maxUses  = $_POST['max_uses'] !== '' ? (int)$_POST['max_uses'] : null;
        $expires  = $_POST['expires_at'] ?: null;
        if ($code && $disc > 0 && $disc <= 100) {
            $db->prepare("INSERT INTO promo_codes (code,discount_percent,min_order,max_uses,expires_at) VALUES (?,?,?,?,?)
                          ON DUPLICATE KEY UPDATE discount_percent=VALUES(discount_percent),min_order=VALUES(min_order),max_uses=VALUES(max_uses),expires_at=VALUES(expires_at)")
               ->execute([$code,$disc,$minOrder,$maxUses,$expires]);
            setFlash('success', "Promo code $code saved.");
        }
    }
    header('Location: promos.php'); exit;
}

$promos = $db->query("SELECT * FROM promo_codes ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Promo Codes — Admin</title>
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
    <a href="users.php"><i data-feather="users"></i> Users</a>
    <a href="categories.php"><i data-feather="tag"></i> Categories</a>
    <a href="promos.php" class="active"><i data-feather="percent"></i> Promo Codes</a>
    <div style="border-top:1px solid rgba(255,255,255,.06);margin:1rem 0"></div>
    <a href="<?= SITE_URL ?>/index.php"><i data-feather="home"></i> View Store</a>
    <a href="<?= SITE_URL ?>/pages/logout.php" style="color:#EF4444"><i data-feather="log-out"></i> Logout</a>
  </nav>
</aside>
<main class="admin-main">

<?php $f=getFlash(); if($f) echo "<div class='flash flash-{$f['type']}' style='margin-bottom:1rem'><span>{$f['msg']}</span></div>"; ?>

<div class="admin-topbar"><h1>Promo Codes</h1></div>
<div class="row g-3">
  <div class="col-lg-4">
    <div style="background:var(--card-bg);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem">
      <h5 style="margin-bottom:1rem">Create Promo Code</h5>
      <form method="POST">
        <div class="mb-3"><label class="form-label">Code *</label><input type="text" name="code" class="form-control" required placeholder="e.g. SUMMER20" style="text-transform:uppercase"></div>
        <div class="mb-3"><label class="form-label">Discount % *</label><input type="number" name="discount_percent" class="form-control" min="1" max="100" required placeholder="10"></div>
        <div class="mb-3"><label class="form-label">Min Order ($)</label><input type="number" name="min_order" step="0.01" class="form-control" value="0"></div>
        <div class="mb-3"><label class="form-label">Max Uses (blank = unlimited)</label><input type="number" name="max_uses" class="form-control" placeholder="100"></div>
        <div class="mb-3"><label class="form-label">Expires At (optional)</label><input type="date" name="expires_at" class="form-control"></div>
        <button type="submit" class="btn btn-primary w-100">Create Code</button>
      </form>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="table-card">
      <table class="table">
        <thead><tr><th>Code</th><th>Discount</th><th>Min Order</th><th>Used/Max</th><th>Expires</th><th>Active</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($promos as $pr): ?>
        <tr>
          <td><code style="background:var(--bg-2);padding:.2rem .5rem;border-radius:6px"><?= sanitize($pr['code']) ?></code></td>
          <td><strong><?= $pr['discount_percent'] ?>%</strong></td>
          <td>$<?= $pr['min_order'] ?></td>
          <td><?= $pr['used_count'] ?> / <?= $pr['max_uses'] ?? '∞' ?></td>
          <td style="font-size:.8rem"><?= $pr['expires_at'] ?? 'Never' ?></td>
          <td>
            <span class="status-badge <?= $pr['is_active'] ? 'status-delivered' : 'status-cancelled' ?>">
              <?= $pr['is_active'] ? 'Active' : 'Inactive' ?>
            </span>
          </td>
          <td>
            <form method="POST" style="display:inline">
              <input type="hidden" name="delete_id" value="<?= $pr['id'] ?>">
              <button class="btn btn-sm" style="color:#EF4444;border:1px solid #EF4444;border-radius:8px;padding:.25rem .6rem;font-size:.78rem"
                      onclick="return confirm('Delete this promo code?')">Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</main>
</div>
<script src="https://unpkg.com/feather-icons"></script>
<script>feather.replace();document.documentElement.setAttribute('data-theme',localStorage.getItem('sz_theme')||'light');</script>
</body></html>
