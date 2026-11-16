<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$db = getDB();
$action = $_GET['action'] ?? 'list';
$editId = (int)($_GET['id'] ?? 0);

// Handle DELETE
if ($action === 'delete' && $editId) {
    $db->prepare("DELETE FROM products WHERE id=?")->execute([$editId]);
    setFlash('success', 'Product deleted.');
    header('Location: products.php'); exit;
}

// Handle ADD/EDIT form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = trim($_POST['name'] ?? '');
    $price     = (float)($_POST['price'] ?? 0);
    $salePrice = $_POST['sale_price'] !== '' ? (float)$_POST['sale_price'] : null;
    $desc      = trim($_POST['description'] ?? '');
    $shortDesc = trim($_POST['short_description'] ?? '');
    $catId     = (int)($_POST['category_id'] ?? 0);
    $stock     = (int)($_POST['stock'] ?? 0);
    $featured  = !empty($_POST['featured']) ? 1 : 0;
    $slug      = slugify($name) . '-' . time();

    $imageFile = null;
    if (!empty($_FILES['image']['name'])) {
        $imageFile = uploadProductImage($_FILES['image']);
    }

    if ($editId) {
        $q = "UPDATE products SET name=?,slug=?,price=?,sale_price=?,description=?,short_description=?,category_id=?,stock=?,featured=?";
        $p = [$name,$slug,$price,$salePrice,$desc,$shortDesc,$catId,$stock,$featured];
        if ($imageFile) { $q .= ",image=?"; $p[] = $imageFile; }
        $q .= " WHERE id=?"; $p[] = $editId;
        $db->prepare($q)->execute($p);
        setFlash('success', 'Product updated!');
    } else {
        $db->prepare("INSERT INTO products (name,slug,price,sale_price,description,short_description,image,category_id,stock,featured) VALUES (?,?,?,?,?,?,?,?,?,?)")
           ->execute([$name,$slug,$price,$salePrice,$desc,$shortDesc,$imageFile,$catId,$stock,$featured]);
        setFlash('success', 'Product added!');
    }
    header('Location: products.php'); exit;
}

$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Editing?
$product = null;
if ($editId) {
    $s = $db->prepare("SELECT * FROM products WHERE id=?");
    $s->execute([$editId]);
    $product = $s->fetch();
}

$products = $db->query("
    SELECT p.*, c.name as cat_name
    FROM products p LEFT JOIN categories c ON c.id=p.category_id
    ORDER BY p.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Products — Admin</title>
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
    <a href="products.php" class="active"><i data-feather="box"></i> Products</a>
    <a href="orders.php"><i data-feather="shopping-bag"></i> Orders</a>
    <a href="users.php"><i data-feather="users"></i> Users</a>
    <a href="categories.php"><i data-feather="tag"></i> Categories</a>
    <a href="promos.php"><i data-feather="percent"></i> Promo Codes</a>
    <div style="border-top:1px solid rgba(255,255,255,.06);margin:1rem 0"></div>
    <a href="<?= SITE_URL ?>/index.php"><i data-feather="home"></i> View Store</a>
    <a href="<?= SITE_URL ?>/pages/logout.php" style="color:#EF4444"><i data-feather="log-out"></i> Logout</a>
  </nav>
</aside>
<main class="admin-main">

<?php
$flash = getFlash();
if ($flash) echo "<div class='flash flash-{$flash['type']}' style='margin-bottom:1rem'><span>{$flash['msg']}</span></div>";
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
<!-- ADD / EDIT FORM -->
<div class="admin-topbar">
  <h1><?= $editId ? 'Edit Product' : 'Add Product' ?></h1>
  <a href="products.php" class="btn btn-outline-primary">← Back to Products</a>
</div>
<div style="background:var(--card-bg);border:1px solid var(--border);border-radius:var(--radius-lg);padding:2rem">
  <form method="POST" enctype="multipart/form-data">
    <div class="row g-3">
      <div class="col-md-8">
        <label class="form-label">Product Name *</label>
        <input type="text" name="name" class="form-control" required value="<?= sanitize($product['name'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Category</label>
        <select name="category_id" class="form-control">
          <option value="">-- None --</option>
          <?php foreach ($categories as $c): ?>
          <option value="<?= $c['id'] ?>" <?= ($product['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= sanitize($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Price *</label>
        <input type="number" name="price" step="0.01" min="0" class="form-control" required value="<?= $product['price'] ?? '' ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Sale Price (optional)</label>
        <input type="number" name="sale_price" step="0.01" min="0" class="form-control" value="<?= $product['sale_price'] ?? '' ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Stock Quantity *</label>
        <input type="number" name="stock" min="0" class="form-control" required value="<?= $product['stock'] ?? 0 ?>">
      </div>
      <div class="col-12">
        <label class="form-label">Short Description</label>
        <input type="text" name="short_description" class="form-control" maxlength="500" value="<?= sanitize($product['short_description'] ?? '') ?>" placeholder="One-liner for product cards">
      </div>
      <div class="col-12">
        <label class="form-label">Full Description</label>
        <textarea name="description" class="form-control" rows="5"><?= sanitize($product['description'] ?? '') ?></textarea>
      </div>
      <div class="col-md-6">
        <label class="form-label">Product Image</label>
        <?php if (!empty($product['image'])): ?>
        <div style="margin-bottom:.5rem"><img src="<?= getProductImage($product['image']) ?>" style="height:80px;border-radius:8px"></div>
        <?php endif; ?>
        <input type="file" name="image" class="form-control" accept="image/*" data-preview="imgPreview">
        <img id="imgPreview" style="display:none;max-height:120px;margin-top:.5rem;border-radius:8px">
      </div>
      <div class="col-md-6 d-flex align-items-end">
        <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-weight:500">
          <input type="checkbox" name="featured" value="1" <?= !empty($product['featured']) ? 'checked' : '' ?>>
          Mark as Featured Product
        </label>
      </div>
    </div>
    <div class="mt-4 d-flex gap-2">
      <button type="submit" class="btn btn-primary"><?= $editId ? 'Update Product' : 'Add Product' ?></button>
      <a href="products.php" class="btn btn-outline-primary">Cancel</a>
    </div>
  </form>
</div>

<?php else: ?>
<!-- PRODUCT LIST -->
<div class="admin-topbar">
  <h1>Products <span style="font-size:1rem;color:var(--text-2)">(<?= count($products) ?>)</span></h1>
  <a href="?action=add" class="btn btn-primary"><i data-feather="plus"></i> Add Product</a>
</div>
<div class="table-card">
  <table class="table">
    <thead>
      <tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Featured</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php foreach ($products as $p): ?>
    <tr>
      <td><img src="<?= getProductImage($p['image'] ?? '') ?>" style="width:48px;height:48px;object-fit:cover;border-radius:8px"></td>
      <td><strong><?= sanitize($p['name']) ?></strong></td>
      <td style="font-size:.82rem;color:var(--text-2)"><?= sanitize($p['cat_name'] ?? '-') ?></td>
      <td>
        <?php if ($p['sale_price']): ?>
        <span style="text-decoration:line-through;color:var(--text-3);font-size:.8rem">$<?= $p['price'] ?></span><br>
        <strong style="color:#EF4444">$<?= $p['sale_price'] ?></strong>
        <?php else: ?>
        $<?= $p['price'] ?>
        <?php endif; ?>
      </td>
      <td>
        <span style="<?= $p['stock'] < 5 ? 'color:#EF4444;font-weight:700' : '' ?>">
          <?= $p['stock'] ?>
        </span>
      </td>
      <td><?= $p['featured'] ? '⭐' : '-' ?></td>
      <td>
        <div style="display:flex;gap:.4rem">
          <a href="?action=edit&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
          <a href="<?= SITE_URL ?>/pages/product.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary" target="_blank">View</a>
          <a href="?action=delete&id=<?= $p['id'] ?>" class="btn btn-sm"
             style="color:#EF4444;border:1px solid #EF4444;border-radius:8px;padding:.3rem .6rem;font-size:.8rem"
             onclick="return confirm('Delete this product?')">Delete</a>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/feather-icons"></script>
<script src="<?= SITE_URL ?>/assets/js/app.js"></script>
<script>feather.replace();document.documentElement.setAttribute('data-theme',localStorage.getItem('sz_theme')||'light');</script>
</body></html>
