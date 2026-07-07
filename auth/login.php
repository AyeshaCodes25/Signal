<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/db.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Enter both email and password.';
    } else {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare('SELECT id, name, password_hash FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id']   = (int) $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }
        $error = 'Incorrect email or password.';
    }
}

$pageTitle = 'Sign in';
require __DIR__ . '/../includes/header.php';
?>

<main class="min-h-screen flex items-center justify-center px-6 py-16">
  <div class="w-full max-w-md">
    <div class="text-center mb-8">
      <div class="text-3xl mb-2">📡</div>
      <h1 class="font-display font-bold text-2xl tracking-tight">Signal</h1>
      <p class="text-mist text-sm mt-1">Sign in to your task control log.</p>
    </div>

    <div class="bg-panel border border-panel2 rounded-lg p-6 sm:p-8">
      <?php if ($error): ?>
        <div class="mb-5 rounded-md border border-flare/40 bg-flare/10 px-4 py-3 text-sm text-flare">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <div class="mb-5 rounded-md border border-signal/30 bg-signal/10 px-4 py-3 text-xs text-signal font-mono leading-relaxed">
        DEMO ACCESS &mdash; email: demo@signal.dev &middot; password: password123
      </div>

      <form method="POST" action="<?= BASE_URL ?>/auth/login.php" class="space-y-4" novalidate>
        <div>
          <label for="email" class="block text-xs font-mono uppercase tracking-wide text-mist mb-1.5">Email</label>
          <input type="email" id="email" name="email" required autofocus value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                 class="w-full rounded-md bg-ink border border-panel2 px-3.5 py-2.5 text-sm text-ivory placeholder-mist/50 focus:border-signal outline-none transition-colors"
                 placeholder="you@example.com">
        </div>
        <div>
          <label for="password" class="block text-xs font-mono uppercase tracking-wide text-mist mb-1.5">Password</label>
          <input type="password" id="password" name="password" required
                 class="w-full rounded-md bg-ink border border-panel2 px-3.5 py-2.5 text-sm text-ivory placeholder-mist/50 focus:border-signal outline-none transition-colors"
                 placeholder="••••••••">
        </div>

        <button type="submit"
                class="w-full bg-signal text-ink font-semibold text-sm rounded-md py-2.5 mt-2 hover:brightness-110 transition-all">
          Sign in
        </button>
      </form>
    </div>

    <p class="text-center text-mist text-sm mt-6">
      No account yet?
      <a href="<?= BASE_URL ?>/auth/register.php" class="text-signal hover:underline">Request access</a>
    </p>
  </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
