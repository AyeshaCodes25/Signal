<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/db.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($name === '' || strlen($name) < 2) {
        $errors[] = 'Enter your name (at least 2 characters).';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        $pdo = getDbConnection();

        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with that email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, $hash]);

            $_SESSION['user_id']   = (int) $pdo->lastInsertId();
            $_SESSION['user_name'] = $name;
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }
    }
}

$pageTitle = 'Create account';
require __DIR__ . '/../includes/header.php';
?>

<main class="min-h-screen flex items-center justify-center px-6 py-16">
  <div class="w-full max-w-md">
    <div class="text-center mb-8">
      <div class="text-3xl mb-2">📡</div>
      <h1 class="font-display font-bold text-2xl tracking-tight">Request access</h1>
      <p class="text-mist text-sm mt-1">Create an operator account for Signal.</p>
    </div>

    <div class="bg-panel border border-panel2 rounded-lg p-6 sm:p-8">
      <?php if (!empty($errors)): ?>
        <div class="mb-5 rounded-md border border-flare/40 bg-flare/10 px-4 py-3 text-sm text-flare">
          <ul class="list-disc list-inside space-y-1">
            <?php foreach ($errors as $error): ?>
              <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="POST" action="<?= BASE_URL ?>/auth/register.php" class="space-y-4" novalidate>
        <div>
          <label for="name" class="block text-xs font-mono uppercase tracking-wide text-mist mb-1.5">Name</label>
          <input type="text" id="name" name="name" required minlength="2" value="<?= htmlspecialchars($name) ?>"
                 class="w-full rounded-md bg-ink border border-panel2 px-3.5 py-2.5 text-sm text-ivory placeholder-mist/50 focus:border-signal outline-none transition-colors"
                 placeholder="Ada Lovelace">
        </div>
        <div>
          <label for="email" class="block text-xs font-mono uppercase tracking-wide text-mist mb-1.5">Email</label>
          <input type="email" id="email" name="email" required value="<?= htmlspecialchars($email) ?>"
                 class="w-full rounded-md bg-ink border border-panel2 px-3.5 py-2.5 text-sm text-ivory placeholder-mist/50 focus:border-signal outline-none transition-colors"
                 placeholder="you@example.com">
        </div>
        <div>
          <label for="password" class="block text-xs font-mono uppercase tracking-wide text-mist mb-1.5">Password</label>
          <input type="password" id="password" name="password" required minlength="8"
                 class="w-full rounded-md bg-ink border border-panel2 px-3.5 py-2.5 text-sm text-ivory placeholder-mist/50 focus:border-signal outline-none transition-colors"
                 placeholder="At least 8 characters">
        </div>
        <div>
          <label for="confirm_password" class="block text-xs font-mono uppercase tracking-wide text-mist mb-1.5">Confirm password</label>
          <input type="password" id="confirm_password" name="confirm_password" required minlength="8"
                 class="w-full rounded-md bg-ink border border-panel2 px-3.5 py-2.5 text-sm text-ivory placeholder-mist/50 focus:border-signal outline-none transition-colors"
                 placeholder="Re-enter password">
        </div>

        <button type="submit"
                class="w-full bg-signal text-ink font-semibold text-sm rounded-md py-2.5 mt-2 hover:brightness-110 transition-all">
          Create account
        </button>
      </form>
    </div>

    <p class="text-center text-mist text-sm mt-6">
      Already have access?
      <a href="<?= BASE_URL ?>/auth/login.php" class="text-signal hover:underline">Sign in</a>
    </p>
  </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
