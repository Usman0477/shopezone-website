<?php
$pageTitle = 'Reset Password';
require_once __DIR__ . '/../includes/header.php';

$token = trim($_GET['token'] ?? '');
$error = '';

$db   = getDB();
$stmt = $db->prepare("SELECT * FROM users WHERE reset_token=? AND reset_expires_at > NOW() LIMIT 1");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    setFlash('error', 'Invalid or expired reset link. Please request a new one.');
    header('Location: ' . SITE_URL . '/pages/login.php?tab=forgot');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass  = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';
    if (strlen($pass) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($pass !== $pass2) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $db->prepare("UPDATE users SET password=?, reset_token=NULL, reset_expires_at=NULL WHERE id=?")
           ->execute([$hash, $user['id']]);
        setFlash('success', 'Password updated! Please log in.');
        header('Location: ' . SITE_URL . '/pages/login.php');
        exit;
    }
}
?>

<div class="page-header">
  <h1>Reset Password</h1>
</div>

<section class="section">
  <div class="container">
    <?php if ($error): ?>
    <div class="flash flash-error" style="max-width:480px;margin:0 auto 1.25rem">
      <i data-feather="x-circle"></i><span><?= sanitize($error) ?></span>
    </div>
    <?php endif; ?>

    <div class="form-card">
      <h4 style="margin-bottom:1.5rem;text-align:center">Choose a new password</h4>
      <form method="POST">
        <div class="mb-3">
          <label class="form-label">New Password</label>
          <input type="password" name="password" class="form-control" required placeholder="Min. 6 characters">
        </div>
        <div class="mb-4">
          <label class="form-label">Confirm Password</label>
          <input type="password" name="password2" class="form-control" required placeholder="Repeat password">
        </div>
        <button type="submit" class="btn btn-primary w-100 btn-lg">Update Password</button>
      </form>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
