<?php
require_once __DIR__ . '/functions.php';
$cartCount     = getCartCount();
$wishlistCount = getWishlistCount();
$currentPage   = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? sanitize($pageTitle) . ' — ' . SITE_NAME : SITE_NAME ?></title>
<meta name="description" content="ShopZone — your modern online store">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>

<!-- Top bar -->
<div class="topbar">
    <span>🚚 Free shipping on orders over $50</span>
    <span>Use code <strong>WELCOME10</strong> for 10% off your first order</span>
</div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg main-nav" id="mainNav">
  <div class="container">
    <a class="navbar-brand" href="<?= SITE_URL ?>/index.php">
      <span class="brand-icon">⬡</span> <?= SITE_NAME ?>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span></span><span></span><span></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav mx-auto gap-1">
        <li class="nav-item"><a class="nav-link <?= $currentPage==='index'?'active':'' ?>" href="<?= SITE_URL ?>/index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage==='shop'?'active':'' ?>" href="<?= SITE_URL ?>/pages/shop.php">Shop</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Categories</a>
          <ul class="dropdown-menu">
            <?php
            $db = getDB();
            $cats = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
            foreach ($cats as $cat):
            ?>
            <li><a class="dropdown-item" href="<?= SITE_URL ?>/pages/shop.php?category=<?= $cat['id'] ?>"><?= sanitize($cat['name']) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </li>
      </ul>
      <div class="nav-actions d-flex align-items-center gap-3">
        <!-- Search -->
        <form class="nav-search" action="<?= SITE_URL ?>/pages/shop.php" method="GET">
          <input type="search" name="q" placeholder="Search products…" value="<?= sanitize($_GET['q'] ?? '') ?>">
          <button type="submit"><i data-feather="search"></i></button>
        </form>
        <!-- Dark mode toggle -->
        <button class="icon-btn" id="themeToggle" title="Toggle dark mode">
          <i data-feather="moon" id="themeIcon"></i>
        </button>
        <!-- Wishlist -->
        <a href="<?= SITE_URL ?>/pages/wishlist.php" class="icon-btn" title="Wishlist">
          <i data-feather="heart"></i>
          <?php if ($wishlistCount > 0): ?><span class="badge"><?= $wishlistCount ?></span><?php endif; ?>
        </a>
        <!-- Cart -->
        <a href="<?= SITE_URL ?>/pages/cart.php" class="icon-btn cart-btn" title="Cart">
          <i data-feather="shopping-cart"></i>
          <span class="badge" id="cartBadge"><?= $cartCount > 0 ? $cartCount : '' ?></span>
        </a>
        <!-- User -->
        <?php if (isLoggedIn()): ?>
        <div class="dropdown">
          <button class="icon-btn user-btn" data-bs-toggle="dropdown">
            <i data-feather="user"></i>
            <span><?= sanitize($_SESSION['user_name']) ?></span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="<?= SITE_URL ?>/pages/profile.php"><i data-feather="user"></i> Profile</a></li>
            <li><a class="dropdown-item" href="<?= SITE_URL ?>/pages/orders.php"><i data-feather="package"></i> Orders</a></li>
            <?php if (isAdmin()): ?>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?= SITE_URL ?>/admin/dashboard.php"><i data-feather="layout"></i> Admin Panel</a></li>
            <?php endif; ?>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="<?= SITE_URL ?>/pages/logout.php"><i data-feather="log-out"></i> Logout</a></li>
          </ul>
        </div>
        <?php else: ?>
        <a href="<?= SITE_URL ?>/pages/login.php" class="btn btn-primary btn-sm">Login</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<main>
<?php renderFlash(); ?>
