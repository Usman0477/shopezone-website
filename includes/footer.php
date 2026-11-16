</main>

<!-- Footer -->
<footer class="site-footer">
  <div class="container">
    <div class="row gy-4">
      <div class="col-lg-4">
        <a class="footer-brand" href="<?= SITE_URL ?>/index.php"><span>⬡</span> <?= SITE_NAME ?></a>
        <p>Your modern destination for quality products. Shop confidently with secure payments and fast shipping.</p>
        <div class="social-links">
          <a href="#"><i data-feather="facebook"></i></a>
          <a href="#"><i data-feather="instagram"></i></a>
          <a href="#"><i data-feather="twitter"></i></a>
          <a href="#"><i data-feather="youtube"></i></a>
        </div>
      </div>
      <div class="col-sm-6 col-lg-2">
        <h6>Shop</h6>
        <ul>
          <li><a href="<?= SITE_URL ?>/pages/shop.php">All Products</a></li>
          <li><a href="<?= SITE_URL ?>/pages/shop.php?featured=1">Featured</a></li>
          <li><a href="<?= SITE_URL ?>/pages/shop.php?sale=1">On Sale</a></li>
          <li><a href="<?= SITE_URL ?>/pages/wishlist.php">Wishlist</a></li>
        </ul>
      </div>
      <div class="col-sm-6 col-lg-2">
        <h6>Account</h6>
        <ul>
          <li><a href="<?= SITE_URL ?>/pages/profile.php">My Profile</a></li>
          <li><a href="<?= SITE_URL ?>/pages/orders.php">My Orders</a></li>
          <li><a href="<?= SITE_URL ?>/pages/cart.php">Cart</a></li>
          <li><a href="<?= SITE_URL ?>/pages/login.php">Login / Register</a></li>
        </ul>
      </div>
      <div class="col-lg-4">
        <h6>Stay in the loop</h6>
        <p>Subscribe for deals and new arrivals.</p>
        <form class="newsletter-form" onsubmit="return false">
          <input type="email" placeholder="your@email.com">
          <button type="submit"><i data-feather="send"></i></button>
        </form>
        <div class="trust-badges mt-3">
          <span><i data-feather="shield"></i> Secure Checkout</span>
          <span><i data-feather="truck"></i> Fast Delivery</span>
          <span><i data-feather="refresh-cw"></i> Easy Returns</span>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <span>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</span>
      <span>Built with PHP + MySQL</span>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/feather-icons"></script>
<script src="<?= SITE_URL ?>/assets/js/app.js"></script>
<script>feather.replace();</script>
</body>
</html>
