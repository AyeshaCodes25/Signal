<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/db.php';
requireLogin();

$pdo = getDbConnection();
$userId = currentUserId();

// --- Stats (server-rendered directly from MySQL) ---
$stmt = $pdo->prepare(
    "SELECT
        COUNT(*) AS total,
        SUM(status = 'pending') AS pending,
        SUM(status = 'in_progress') AS in_progress,
        SUM(status = 'completed') AS completed,
        SUM(due_date < CURDATE() AND status != 'completed') AS overdue
     FROM tasks WHERE user_id = ?"
);
$stmt->execute([$userId]);
$stats = $stmt->fetch();
$total = (int) $stats['total'];

$completionRate = $total > 0 ? round(((int)$stats['completed'] / $total) * 100) : 0;

// --- Upcoming tasks (next 5 by due date) ---
$stmt = $pdo->prepare(
    "SELECT id, title, status, priority, due_date FROM tasks
     WHERE user_id = ? AND status != 'completed'
     ORDER BY due_date IS NULL, due_date ASC
     LIMIT 5"
);
$stmt->execute([$userId]);
$upcoming = $stmt->fetchAll();

// --- Recent activity (last 5 updated) ---
$stmt = $pdo->prepare(
    "SELECT id, title, status, updated_at FROM tasks
     WHERE user_id = ? ORDER BY updated_at DESC LIMIT 5"
);
$stmt->execute([$userId]);
$recent = $stmt->fetchAll();

$activeNav = 'dashboard';
$pageTitle = 'Control Room';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/navbar.php';
?>

<main class="max-w-6xl mx-auto px-6 py-10">

  <div class="flex items-center gap-2 mb-1">
    <span class="pulse-dot"></span>
    <span class="text-xs font-mono uppercase tracking-widest text-mist">Live status</span>
  </div>
  <h1 class="font-display font-bold text-3xl tracking-tight mb-8">Control Room</h1>

  <!-- Stat cards -->
  <section class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
    <div class="bg-panel border border-panel2 rounded-lg p-5">
      <p class="text-mist text-xs font-mono uppercase tracking-wide mb-2">Total logged</p>
      <p class="font-display text-3xl font-bold"><?= $total ?></p>
    </div>
    <div class="bg-panel border border-panel2 rounded-lg p-5">
      <p class="text-mist text-xs font-mono uppercase tracking-wide mb-2">In progress</p>
      <p class="font-display text-3xl font-bold text-signal"><?= (int) $stats['in_progress'] ?></p>
    </div>
    <div class="bg-panel border border-panel2 rounded-lg p-5">
      <p class="text-mist text-xs font-mono uppercase tracking-wide mb-2">Completed</p>
      <p class="font-display text-3xl font-bold text-sage"><?= (int) $stats['completed'] ?></p>
    </div>
    <div class="bg-panel border border-panel2 rounded-lg p-5">
      <p class="text-mist text-xs font-mono uppercase tracking-wide mb-2">Overdue</p>
      <p class="font-display text-3xl font-bold text-flare"><?= (int) $stats['overdue'] ?></p>
    </div>
  </section>

  <div class="grid lg:grid-cols-3 gap-6">

    <!-- Completion progress -->
    <section class="lg:col-span-1 bg-panel border border-panel2 rounded-lg p-6">
      <h2 class="font-display font-semibold text-sm uppercase tracking-wide text-mist mb-4">Completion rate</h2>
      <div class="flex items-end gap-2 mb-3">
        <span class="font-display text-4xl font-bold"><?= $completionRate ?></span>
        <span class="text-mist mb-1">%</span>
      </div>
      <div class="w-full h-2 bg-ink rounded-full overflow-hidden">
        <div class="h-full bg-sage rounded-full transition-all" style="width: <?= $completionRate ?>%"></div>
      </div>
      <p class="text-mist text-xs mt-4 font-mono">
        <?= (int) $stats['completed'] ?> of <?= $total ?> entries closed out
      </p>
      <a href="<?= BASE_URL ?>/tasks.php" class="inline-block mt-5 text-signal text-sm font-medium hover:underline">
        Open task log &rarr;
      </a>
    </section>

    <!-- Upcoming -->
    <section class="lg:col-span-1 bg-panel border border-panel2 rounded-lg p-6">
      <h2 class="font-display font-semibold text-sm uppercase tracking-wide text-mist mb-4">Next due</h2>
      <?php if (empty($upcoming)): ?>
        <p class="text-mist text-sm">Nothing on the log yet.</p>
      <?php else: ?>
        <ul class="space-y-3">
          <?php foreach ($upcoming as $task): ?>
            <li class="flex items-center justify-between gap-3">
              <span class="text-sm truncate"><?= htmlspecialchars($task['title']) ?></span>
              <span class="text-xs font-mono text-mist whitespace-nowrap">
                <?= $task['due_date'] ? date('M j', strtotime($task['due_date'])) : '—' ?>
              </span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>

    <!-- Recent activity -->
    <section class="lg:col-span-1 bg-panel border border-panel2 rounded-lg p-6">
      <h2 class="font-display font-semibold text-sm uppercase tracking-wide text-mist mb-4">Recent activity</h2>
      <?php if (empty($recent)): ?>
        <p class="text-mist text-sm">No activity logged yet.</p>
      <?php else: ?>
        <ul class="space-y-3">
          <?php foreach ($recent as $task): ?>
            <li class="flex items-center justify-between gap-3">
              <span class="text-sm truncate"><?= htmlspecialchars($task['title']) ?></span>
              <span class="stamp stamp-<?= $task['status'] ?> text-[0.6rem] py-0.5 px-1.5">
                <?= str_replace('_', ' ', $task['status']) ?>
              </span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>

  </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
