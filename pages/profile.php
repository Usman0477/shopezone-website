<?php
$pageTitle = 'My Profile';
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$db  = getDB();
$uid = currentUserId();

$user = $db->prepare("SELECT * FROM users WHERE id=?");
$user->execute([$uid]);
$user = $user->fetch();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $pass = $_POST['password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';

    if (!$name) {
        $error = 'Name is required.';
    } else {
        $db->prepare("UPDATE users SET name=? WHERE id=?")->execute([$name, $uid]);
        $_SESSION['user_name'] = $name;

        if ($newPass) {
            if (!password_verify($pass, $user['password'])) {
                $error = 'Current password is incorrect.';
            } elseif (strlen($newPass) < 6) {
                $error = 'New password must be at least 6 characters.';
            } else {
                $db->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($newPass, PASSWORD_BCRYPT), $uid]);
                setFlash('success', 'Profile and password updated!');
                header('Location: ' . SITE_URL . '/pages/profile.php');
                exit;
            }
        } else {
            setFlash('success', 'Profile updated!');
            header('Location: ' . SITE_URL . '/pages/profile.php');
            exit;
        }
    }
    $user['name'] = $name; // reflect change in form
}
?>

<div class="page-header">
  <h1>My Profile</h1>
  <p>Manage your account details</p>
</div>

<section class="section">
  <div class="container">
    <?php if ($error): ?>
    <div class="flash flash-error" style="max-width:520px;margin:0 auto 1.25rem">
      <i data-feather="x-circle"></i><span><?= sanitize($error) ?></span>
    </div>
    <?php endif; ?>

    <div class="form-card" style="max-width:520px">
      <h5 style="margin-bottom:1.5rem">Account Information</h5>
      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" name="name" class="form-control" value="<?= sanitize($user['name']) ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email Address</label>
          <input type="email" class="form-control" value="<?= sanitize($user['email']) ?>" disabled style="opacity:.6">
          <small style="color:var(--text-3)">Email cannot be changed.</small>
        </div>
        <hr style="border-color:var(--border);margin:1.5rem 0">
        <h6 style="margin-bottom:1rem">Change Password <small style="font-weight:400;color:var(--text-3)">(leave blank to keep current)</small></h6>
        <div class="mb-3">
          <label class="form-label">Current Password</label>
          <input type="password" name="password" class="form-control" placeholder="Enter current password">
        </div>
        <div class="mb-4">
          <label class="form-label">New Password</label>
          <input type="password" name="new_password" class="form-control" placeholder="Min. 6 characters">
        </div>
        <button type="submit" class="btn btn-primary w-100">Save Changes</button>
      </form>

      <div class="mt-3 text-center">
        <a href="<?= SITE_URL ?>/pages/orders.php" style="font-size:.875rem">View My Orders →</a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
