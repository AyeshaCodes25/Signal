<header class="scan-header border-b border-panel2 bg-ink/60">
  <nav class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
    <a href="<?= BASE_URL ?>/index.php" class="flex items-center gap-2 font-display font-700 text-lg tracking-tight">
      <span class="text-signal">📡</span>
      <span>SIGNAL</span>
      <span class="hidden sm:inline text-mist font-mono text-xs font-normal ml-1">/ task-control-log</span>
    </a>
    <div class="flex items-center gap-1 sm:gap-2">
      <a href="<?= BASE_URL ?>/index.php"
         class="px-3 py-2 rounded-md text-sm font-medium transition-colors <?= ($activeNav ?? '') === 'dashboard' ? 'bg-panel2 text-ivory' : 'text-mist hover:text-ivory hover:bg-panel' ?>">
        Control Room
      </a>
      <a href="<?= BASE_URL ?>/tasks.php"
         class="px-3 py-2 rounded-md text-sm font-medium transition-colors <?= ($activeNav ?? '') === 'tasks' ? 'bg-panel2 text-ivory' : 'text-mist hover:text-ivory hover:bg-panel' ?>">
        Task Log
      </a>
      <div class="w-px h-5 bg-panel2 mx-1 hidden sm:block"></div>
      <span class="hidden sm:inline text-mist text-sm mr-1">
        <?= htmlspecialchars($_SESSION['user_name'] ?? 'Operator') ?>
      </span>
      <a href="<?= BASE_URL ?>/auth/logout.php"
         class="px-3 py-2 rounded-md text-sm font-medium text-mist hover:text-flare hover:bg-panel transition-colors">
        Sign out
      </a>
    </div>
  </nav>
</header>
