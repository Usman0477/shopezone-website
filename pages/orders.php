<?php
$pageTitle = 'My Orders';
require_once __DIR__ . '/../includes/header.php';
requireLogin();
$db  = getDB();
$uid = currentUserId();

$orders = $db->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC");
$orders->execute([$uid]);
$orders = $orders->fetchAll();

$statusClass = fn($s) => 'status-' . strtolower($s);
?>

<div class="page-header">
  <h1>My Orders</h1>
  <p>Track and manage your purchases</p>
</div>

<section class="section">
  <div class="container">
    <?php if (empty($orders)): ?>
    <div class="text-center py-5">
      <i data-feather="package" style="width:64px;height:64px;color:var(--text-3)"></i>
      <h3 class="mt-3">No orders yet</h3>
      <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-primary mt-2">Start Shopping</a>
    </div>
    <?php else: ?>
    <div class="table-card">
      <table class="table">
        <thead>
          <tr>
            <th>Order #</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th><th>Details</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $ord): ?>
        <?php
        $oItems = $db->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON p.id=oi.product_id WHERE oi.order_id=?");
        $oItems->execute([$ord['id']]);
        $oItems = $oItems->fetchAll();
        ?>
        <tr>
          <td><strong>#<?= $ord['id'] ?></strong></td>
          <td style="font-size:.85rem"><?= date('M j, Y', strtotime($ord['created_at'])) ?></td>
          <td style="font-size:.82rem;color:var(--text-2)">
            <?= implode(', ', array_map(fn($i) => sanitize($i['name']).' ×'.$i['quantity'], $oItems)) ?>
          </td>
          <td><strong><?= formatPrice($ord['total_price']) ?></strong></td>
          <td><span class="status-badge <?= $statusClass($ord['status']) ?>"><?= $ord['status'] ?></span></td>
          <td>
            <button class="btn btn-sm btn-outline-primary" onclick="toggleDetails(<?= $ord['id'] ?>)">
              Details
            </button>
          </td>
        </tr>
        <tr id="details-<?= $ord['id'] ?>" style="display:none">
          <td colspan="6">
            <div style="padding:.75rem;background:var(--bg-2);border-radius:8px;font-size:.875rem">
              <strong>Ship to:</strong> <?= sanitize($ord['name']) ?>, <?= sanitize($ord['address']) ?>, <?= sanitize($ord['city']) ?>
              — Phone: <?= sanitize($ord['phone']) ?>
              <?php if ($ord['promo_code']): ?>
              — Promo: <code><?= sanitize($ord['promo_code']) ?></code> (saved <?= formatPrice($ord['discount_amount']) ?>)
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</section>

<script>
function toggleDetails(id) {
  const row = document.getElementById('details-' + id);
  row.style.display = row.style.display === 'none' ? '' : 'none';
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
