<?php
$pageTitle = 'Login / Register';
require_once __DIR__ . '/../includes/header.php';

if (isLoggedIn()) { header('Location: ' . SITE_URL); exit; }

$tab   = $_GET['tab'] ?? 'login';
$error = '';

// LOGIN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = !empty($_POST['remember']);

    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        if ($remember) {
            $token = generateToken();
            $db->prepare("UPDATE users SET remember_token=? WHERE id=?")->execute([$token, $user['id']]);
            setcookie('remember_token', $token, time() + REMEMBER_LIFETIME, '/', '', false, true);
        }

        $redirect = $_SESSION['redirect_after_login'] ?? SITE_URL;
        unset($_SESSION['redirect_after_login']);
        header("Location: $redirect");
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}

// REGISTER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $tab  = 'register';
    $name = trim($_POST['name'] ?? '');
    $email= trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $pass2= $_POST['password2'] ?? '';

    if (!$name || !$email || !$pass) {
        $error = 'All fields are required.';
    } elseif ($pass !== $pass2) {
        $error = 'Passwords do not match.';
    } elseif (strlen($pass) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $db = getDB();
        $chk = $db->prepare("SELECT id FROM users WHERE email=?");
        $chk->execute([$email]);
        if ($chk->fetch()) {
            $error = 'Email already registered.';
        } else {
            $hash = password_hash($pass, PASSWORD_BCRYPT);
            $db->prepare("INSERT INTO users (name,email,password,is_verified) VALUES (?,?,?,1)")->execute([$name,$email,$hash]);
            setFlash('success', 'Account created! Please log in.');
            header('Location: ' . SITE_URL . '/pages/login.php');
            exit;
        }
    }
}

// FORGOT PASSWORD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['forgot'])) {
    $tab  = 'forgot';
    $email= trim($_POST['email'] ?? '');
    $db   = getDB();
    $stmt = $db->prepare("SELECT id FROM users WHERE email=?");
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    if ($u) {
        $token = generateToken();
        $exp   = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $db->prepare("UPDATE users SET reset_token=?,reset_expires_at=? WHERE id=?")->execute([$token,$exp,$u['id']]);
        $link = SITE_URL . '/pages/reset_password.php?token=' . $token;
        sendMail($email, 'Password Reset — ' . SITE_NAME,
            "<p>Click the link below to reset your password (valid 1 hour):</p><p><a href='$link'>$link</a></p>");
    }
    // Always show success to prevent email enumeration
    setFlash('success', 'If that email is registered, a reset link has been sent.');
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}
?>

<div class="page-header">
  <h1>My Account</h1>
  <p>Login or create a new account</p>
</div>

<section class="section">
  <div class="container">
    <?php if ($error): ?>
    <div class="flash flash-error" style="max-width:480px;margin:0 auto 1.25rem">
      <i data-feather="x-circle"></i><span><?= sanitize($error) ?></span>
    </div>
    <?php endif; ?>

    <ul class="nav nav-tabs justify-content-center mb-4" style="border-bottom:1px solid var(--border)">
      <li class="nav-item">
        <a class="nav-link <?= $tab==='login'?'active':'' ?>" href="?tab=login">Login</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $tab==='register'?'active':'' ?>" href="?tab=register">Register</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $tab==='forgot'?'active':'' ?>" href="?tab=forgot">Forgot Password</a>
      </li>
    </ul>

    <!-- LOGIN -->
    <?php if ($tab === 'login'): ?>
    <div class="form-card">
      <h4 style="margin-bottom:1.5rem;text-align:center">Welcome Back</h4>
      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" required placeholder="you@example.com" autocomplete="email">
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required placeholder="••••••••" autocomplete="current-password">
        </div>
        <div class="mb-3 d-flex justify-content-between align-items-center">
          <label style="display:flex;align-items:center;gap:.4rem;font-size:.875rem;cursor:pointer">
            <input type="checkbox" name="remember"> Remember me
          </label>
          <a href="?tab=forgot" style="font-size:.875rem">Forgot password?</a>
        </div>
        <button type="submit" name="login" class="btn btn-primary w-100 btn-lg">Login</button>
        <p class="text-center mt-3" style="font-size:.875rem;color:var(--text-2)">
          No account? <a href="?tab=register">Register here</a>
        </p>
      </form>
    </div>

    <!-- REGISTER -->
    <?php elseif ($tab === 'register'): ?>
    <div class="form-card">
      <h4 style="margin-bottom:1.5rem;text-align:center">Create Account</h4>
      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" name="name" class="form-control" required placeholder="Jane Doe">
        </div>
        <div class="mb-3">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" required placeholder="you@example.com">
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required placeholder="Min. 6 characters">
        </div>
        <div class="mb-4">
          <label class="form-label">Confirm Password</label>
          <input type="password" name="password2" class="form-control" required placeholder="Repeat password">
        </div>
        <button type="submit" name="register" class="btn btn-primary w-100 btn-lg">Create Account</button>
        <p class="text-center mt-3" style="font-size:.875rem;color:var(--text-2)">
          Already have an account? <a href="?tab=login">Login</a>
        </p>
      </form>
    </div>

    <!-- FORGOT -->
    <?php else: ?>
    <div class="form-card">
      <h4 style="margin-bottom:.5rem;text-align:center">Reset Password</h4>
      <p style="text-align:center;color:var(--text-2);font-size:.875rem;margin-bottom:1.5rem">Enter your email and we'll send a reset link.</p>
      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" required placeholder="you@example.com">
        </div>
        <button type="submit" name="forgot" class="btn btn-primary w-100 btn-lg">Send Reset Link</button>
        <p class="text-center mt-3"><a href="?tab=login" style="font-size:.875rem">← Back to Login</a></p>
      </form>
    </div>
    <?php endif; ?>

  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
