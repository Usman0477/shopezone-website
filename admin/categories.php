<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $db->prepare("DELETE FROM categories WHERE id=?")->execute([(int)$_POST['delete_id']]);
        setFlash('success','Category deleted.');
    } else {
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? 'tag');
        $slug = slugify($name);
        if ($name) {
            if ($_POST['edit_id'] ?? '') {
                $db->prepare("UPDATE categories SET name=?,slug=?,icon=? WHERE id=?")->execute([$name,$slug,$icon,(int)$_POST['edit_id']]);
                setFlash('success','Category updated.');
            } else {
                $db->prepare("INSERT INTO categories (name,slug,icon) VALUES (?,?,?)")->execute([$name,$slug,$icon]);
                setFlash('success','Category added.');
            }
        }
    }
    header('Location: categories.php'); exit;
}

$cats = $db->query("SELECT c.*, COUNT(p.id) as prod_count FROM categories c LEFT JOIN products p ON p.category_id=c.id GROUP BY c.id ORDER BY c.name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Categories — Admin</title>
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
    <a href="categories.php" class="active"><i data-feather="tag"></i> Categories</a>
    <a href="promos.php"><i data-feather="percent"></i> Promo Codes</a>
    <div style="border-top:1px solid rgba(255,255,255,.06);margin:1rem 0"></div>
    <a href="<?= SITE_URL ?>/index.php"><i data-feather="home"></i> View Store</a>
    <a href="<?= SITE_URL ?>/pages/logout.php" style="color:#EF4444"><i data-feather="log-out"></i> Logout</a>
  </nav>
</aside>
<main class="admin-main">

<?php $f=getFlash(); if($f) echo "<div class='flash flash-{$f['type']}' style='margin-bottom:1rem'><span>{$f['msg']}</span></div>"; ?>

<div class="admin-topbar"><h1>Categories</h1></div>
<div class="row g-3">
  <div class="col-lg-4">
    <div style="background:var(--card-bg);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem">
      <h5 style="margin-bottom:1rem">Add Category</h5>
      <form method="POST">
        <input type="hidden" name="edit_id" value="">
        <div class="mb-3"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" required></div>
        <div class="mb-3">
          <label class="form-label">Feather Icon name</label>
          <input type="text" name="icon" class="form-control" value="tag" placeholder="e.g. cpu, shirt, book-open">
          <small style="color:var(--text-3)">See <a href="https://feathericons.com" target="_blank">feathericons.com</a></small>
        </div>
        <button type="submit" class="btn btn-primary w-100">Add Category</button>
      </form>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="table-card">
      <table class="table">
        <thead><tr><th>Name</th><th>Slug</th><th>Icon</th><th>Products</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($cats as $c): ?>
        <tr>
          <td><strong><?= sanitize($c['name']) ?></strong></td>
          <td style="font-size:.8rem;color:var(--text-3)"><?= $c['slug'] ?></td>
          <td><i data-feather="<?= $c['icon'] ?>" style="width:18px;height:18px"></i></td>
          <td><?= $c['prod_count'] ?></td>
          <td>
            <form method="POST" style="display:inline">
              <input type="hidden" name="delete_id" value="<?= $c['id'] ?>">
              <button class="btn btn-sm" style="color:#EF4444;border:1px solid #EF4444;border-radius:8px;padding:.25rem .6rem;font-size:.78rem"
                      onclick="return confirm('Delete category?')">Delete</button>
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
