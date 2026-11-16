<?php
require_once __DIR__ . '/../includes/header.php';
$db = getDB();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: ' . SITE_URL . '/pages/shop.php'); exit; }

$stmt = $db->prepare("
    SELECT p.*, c.name as cat_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { header('Location: ' . SITE_URL . '/pages/shop.php'); exit; }

$pageTitle = $p['name'];

// Reviews
$reviews = $db->prepare("
    SELECT r.*, u.name as user_name
    FROM reviews r
    JOIN users u ON u.id = r.user_id
    WHERE r.product_id = ?
    ORDER BY r.created_at DESC
");
$reviews->execute([$id]);
$reviews = $reviews->fetchAll();

$avgRating = count($reviews) ? array_sum(array_column($reviews, 'rating')) / count($reviews) : 0;

// Gallery
$gallery = $p['gallery'] ? json_decode($p['gallery'], true) : [];

// Related
$related = $db->prepare("
    SELECT p.*, c.name as cat_name,
           COALESCE(AVG(r.rating),0) as avg_rating,
           COUNT(r.id) as review_count
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    LEFT JOIN reviews r ON r.product_id = p.id
    WHERE p.category_id = ? AND p.id != ?
    GROUP BY p.id LIMIT 4
");
$related->execute([$p['category_id'], $id]);
$related = $related->fetchAll();

$hasSale = !empty($p['sale_price']) && $p['sale_price'] < $p['price'];
$displayPrice = $hasSale ? $p['sale_price'] : $p['price'];
$discount = $hasSale ? round((1 - $p['sale_price'] / $p['price']) * 100) : 0;

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    requireLogin();
    $rating  = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    if ($rating >= 1 && $rating <= 5 && $comment) {
        try {
            $ins = $db->prepare("INSERT INTO reviews (user_id, product_id, rating, comment) VALUES (?,?,?,?)
                                 ON DUPLICATE KEY UPDATE rating=VALUES(rating), comment=VALUES(comment)");
            $ins->execute([currentUserId(), $id, $rating, $comment]);
            setFlash('success', 'Review submitted!');
        } catch (Exception $e) { setFlash('error', 'Could not submit review.'); }
    } else {
        setFlash('error', 'Please provide a rating and comment.');
    }
    header('Location: ' . SITE_URL . '/pages/product.php?id=' . $id . '#reviews');
    exit;
}
?>

<div class="page-header" style="text-align:left;padding:2rem 0">
  <div class="container">
    <nav style="font-size:.82rem;color:rgba(245,240,235,.5)">
      <a href="<?= SITE_URL ?>" style="color:rgba(245,240,235,.5)">Home</a> /
      <a href="<?= SITE_URL ?>/pages/shop.php" style="color:rgba(245,240,235,.5)">Shop</a> /
      <?php if ($p['cat_name']): ?>
      <a href="<?= SITE_URL ?>/pages/shop.php?category=<?= $p['category_id'] ?>" style="color:rgba(245,240,235,.5)"><?= sanitize($p['cat_name']) ?></a> /
      <?php endif; ?>
      <span style="color:#F5F0EB"><?= sanitize($p['name']) ?></span>
    </nav>
  </div>
</div>

<section class="section" style="padding-top:2rem">
  <div class="container">
    <div class="row gy-4">
      <!-- Gallery -->
      <div class="col-lg-5">
        <div class="product-gallery-main">
          <img src="<?= getProductImage($p['image'] ?? '') ?>" alt="<?= sanitize($p['name']) ?>" id="mainProductImg">
        </div>
        <?php if (!empty($gallery) || $p['image']): ?>
        <div class="product-gallery-thumbs">
          <?php if ($p['image']): ?>
          <img class="thumb-img active" src="<?= getProductImage($p['image']) ?>" alt="">
          <?php endif; ?>
          <?php foreach ($gallery as $img): ?>
          <img class="thumb-img" src="<?= getProductImage($img) ?>" alt="">
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Details -->
      <div class="col-lg-7">
        <?php if ($p['cat_name']): ?>
        <span class="section-eyebrow"><?= sanitize($p['cat_name']) ?></span>
        <?php endif; ?>
        <h1 style="font-size:2rem;margin:.3rem 0 .75rem;letter-spacing:-.03em"><?= sanitize($p['name']) ?></h1>

        <div class="product-rating" style="margin-bottom:1rem">
          <?= getStarRating($avgRating) ?>
          <span style="font-size:.85rem;color:var(--text-2)"><?= number_format($avgRating,1) ?> (<?= count($reviews) ?> reviews)</span>
        </div>

        <div class="product-price" style="margin-bottom:1.25rem">
          <span class="price-current" style="font-size:2rem"><?= formatPrice($displayPrice) ?></span>
          <?php if ($hasSale): ?>
          <span class="price-original" style="font-size:1.1rem"><?= formatPrice($p['price']) ?></span>
          <span class="badge-sale ms-1">Save <?= $discount ?>%</span>
          <?php endif; ?>
        </div>

        <p style="color:var(--text-2);margin-bottom:1.5rem"><?= nl2br(sanitize($p['description'])) ?></p>

        <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.75rem;font-size:.875rem">
          <span style="color:var(--text-2)">Stock:</span>
          <?php if ($p['stock'] > 0): ?>
          <span style="color:#065F46;font-weight:600">✓ In Stock (<?= $p['stock'] ?> left)</span>
          <?php else: ?>
          <span style="color:#991B1B;font-weight:600">✗ Out of Stock</span>
          <?php endif; ?>
        </div>

        <?php if ($p['stock'] > 0): ?>
        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="qty-control">
            <button class="qty-btn" id="decPdp">−</button>
            <span class="qty-num" id="pdpQty">1</span>
            <button class="qty-btn" id="incPdp">+</button>
          </div>
          <span style="font-size:.8rem;color:var(--text-3)">Max <?= $p['stock'] ?></span>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <button class="btn btn-primary btn-lg" data-add-cart="<?= $p['id'] ?>" id="pdpAddCart">
            <i data-feather="shopping-cart"></i> Add to Cart
          </button>
          <a href="<?= SITE_URL ?>/pages/checkout.php?buy_now=<?= $p['id'] ?>" class="btn btn-outline-primary btn-lg">Buy Now</a>
          <button class="btn btn-outline-primary" data-toggle-wish="<?= $p['id'] ?>" title="Wishlist">
            <i data-feather="heart"></i>
          </button>
        </div>
        <?php else: ?>
        <button class="btn btn-secondary btn-lg" disabled>Out of Stock</button>
        <?php endif; ?>
      </div>
    </div>

    <!-- Reviews -->
    <div id="reviews" style="margin-top:4rem">
      <h3 style="margin-bottom:1.5rem">Customer Reviews <span style="font-size:1rem;color:var(--text-3)">(<?= count($reviews) ?>)</span></h3>
      <div class="row gy-3">
        <div class="col-lg-8">
          <?php if (empty($reviews)): ?>
          <p style="color:var(--text-3)">No reviews yet. Be the first!</p>
          <?php else: ?>
          <?php foreach ($reviews as $rev): ?>
          <div class="review-card">
            <div class="review-meta">
              <div style="width:36px;height:36px;background:var(--accent);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.875rem;color:#1A1612">
                <?= strtoupper(substr($rev['user_name'],0,1)) ?>
              </div>
              <div>
                <div class="reviewer-name"><?= sanitize($rev['user_name']) ?></div>
                <div class="review-date"><?= date('M j, Y', strtotime($rev['created_at'])) ?></div>
              </div>
              <div class="ms-auto product-rating" style="margin:0"><?= getStarRating($rev['rating']) ?></div>
            </div>
            <p style="margin:0;color:var(--text-2);font-size:.9rem"><?= sanitize($rev['comment']) ?></p>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <div class="col-lg-4">
          <?php if (isLoggedIn()): ?>
          <div class="summary-card">
            <h5 style="margin-bottom:1rem">Leave a Review</h5>
            <form method="POST">
              <div class="mb-3">
                <label class="form-label">Rating</label>
                <div style="display:flex;gap:.3rem" id="starPicker">
                  <?php for ($i=1;$i<=5;$i++): ?>
                  <label style="cursor:pointer;color:var(--border);font-size:1.6rem" class="star-label">
                    <input type="radio" name="rating" value="<?= $i ?>" style="display:none" required>★
                  </label>
                  <?php endfor; ?>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">Comment</label>
                <textarea class="form-control" name="comment" rows="4" required placeholder="Share your thoughts…"></textarea>
              </div>
              <button type="submit" name="submit_review" class="btn btn-primary w-100">Submit Review</button>
            </form>
          </div>
          <?php else: ?>
          <div class="summary-card text-center">
            <p>Please <a href="<?= SITE_URL ?>/pages/login.php">log in</a> to leave a review.</p>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Related -->
    <?php if (!empty($related)): ?>
    <div style="margin-top:4rem">
      <h3 style="margin-bottom:1.5rem">You Might Also Like</h3>
      <div class="row g-3">
        <?php foreach ($related as $p): ?>
        <div class="col-sm-6 col-lg-3">
          <?php include __DIR__ . '/../includes/product_card.php'; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>
</section>

<script>
// Star picker
document.querySelectorAll('.star-label').forEach((lbl, i, all) => {
  lbl.addEventListener('mouseenter', () => all.forEach((l,j) => l.style.color = j<=i ? 'var(--accent)' : 'var(--border)'));
  lbl.addEventListener('click', () => all.forEach((l,j) => l.dataset.sel = j<=i ? '1':'0'));
  document.getElementById('starPicker').addEventListener('mouseleave', () => {
    all.forEach((l,j) => l.style.color = l.querySelector('input').checked ? 'var(--accent)' : (l.dataset.sel==='1' ? 'var(--accent)' : 'var(--border)'));
  });
  lbl.querySelector('input').addEventListener('change', () => all.forEach((l,j)=>l.style.color=j<=i?'var(--accent)':'var(--border)'));
});
// PDP quantity
let qty = 1;
document.getElementById('incPdp')?.addEventListener('click', ()=>{ qty=Math.min(<?=$p['stock']?>,qty+1); document.getElementById('pdpQty').textContent=qty; });
document.getElementById('decPdp')?.addEventListener('click', ()=>{ qty=Math.max(1,qty-1); document.getElementById('pdpQty').textContent=qty; });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
