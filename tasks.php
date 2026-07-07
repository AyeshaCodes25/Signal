<?php
require_once __DIR__ . '/includes/session.php';
requireLogin();

$activeNav = 'tasks';
$pageTitle = 'Task Log';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/navbar.php';
?>

<main class="max-w-6xl mx-auto px-6 py-10">

  <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
    <div>
      <div class="flex items-center gap-2 mb-1">
        <span class="pulse-dot"></span>
        <span class="text-xs font-mono uppercase tracking-widest text-mist">Live log</span>
      </div>
      <h1 class="font-display font-bold text-3xl tracking-tight">Task Log</h1>
    </div>
    <button id="openCreateModal"
            class="bg-signal text-ink font-semibold text-sm rounded-md px-4 py-2.5 hover:brightness-110 transition-all">
      + New entry
    </button>
  </div>

  <!-- Filters -->
  <div class="flex flex-wrap gap-3 mb-6">
    <input id="searchInput" type="text" placeholder="Search entries…"
           class="flex-1 min-w-[180px] rounded-md bg-panel border border-panel2 px-3.5 py-2 text-sm placeholder-mist/50 focus:border-signal outline-none">

    <select id="statusFilter" class="rounded-md bg-panel border border-panel2 px-3 py-2 text-sm text-ivory focus:border-signal outline-none">
      <option value="">All statuses</option>
      <option value="pending">Pending</option>
      <option value="in_progress">In progress</option>
      <option value="completed">Completed</option>
    </select>

    <select id="priorityFilter" class="rounded-md bg-panel border border-panel2 px-3 py-2 text-sm text-ivory focus:border-signal outline-none">
      <option value="">All priorities</option>
      <option value="urgent">Urgent</option>
      <option value="high">High</option>
      <option value="medium">Medium</option>
      <option value="low">Low</option>
    </select>

    <select id="sortSelect" class="rounded-md bg-panel border border-panel2 px-3 py-2 text-sm text-ivory focus:border-signal outline-none">
      <option value="due_date_asc">Due date ↑</option>
      <option value="due_date_desc">Due date ↓</option>
      <option value="priority">Priority</option>
      <option value="newest">Newest first</option>
    </select>
  </div>

  <!-- Toast -->
  <div id="toast" class="hidden fixed top-6 right-6 z-50 px-4 py-3 rounded-md text-sm font-medium shadow-lg"></div>

  <!-- Task list -->
  <section class="ledger-rules bg-panel border border-panel2 rounded-lg overflow-hidden">
    <div id="taskList" class="divide-y divide-panel2 log-scroll max-h-[60vh] overflow-y-auto">
      <div class="p-10 text-center text-mist text-sm" id="loadingState">Loading log entries…</div>
    </div>
    <div id="emptyState" class="hidden p-10 text-center text-mist text-sm">
      No entries match this view. Try adjusting your filters, or log a new task.
    </div>
  </section>
</main>

<!-- Create / Edit Modal -->
<div id="taskModal" class="hidden fixed inset-0 modal-backdrop z-50 flex items-center justify-center px-4">
  <div class="bg-panel border border-panel2 rounded-lg w-full max-w-lg p-6 sm:p-7">
    <div class="flex items-center justify-between mb-5">
      <h2 id="modalTitle" class="font-display font-semibold text-lg">New log entry</h2>
      <button id="closeModal" class="text-mist hover:text-ivory text-xl leading-none">&times;</button>
    </div>

    <form id="taskForm" class="space-y-4">
      <input type="hidden" id="taskId">

      <div>
        <label class="block text-xs font-mono uppercase tracking-wide text-mist mb-1.5">Title</label>
        <input type="text" id="titleInput" required maxlength="150"
               class="w-full rounded-md bg-ink border border-panel2 px-3.5 py-2.5 text-sm text-ivory placeholder-mist/50 focus:border-signal outline-none"
               placeholder="What needs to happen?">
      </div>

      <div>
        <label class="block text-xs font-mono uppercase tracking-wide text-mist mb-1.5">Description</label>
        <textarea id="descriptionInput" rows="3"
                  class="w-full rounded-md bg-ink border border-panel2 px-3.5 py-2.5 text-sm text-ivory placeholder-mist/50 focus:border-signal outline-none resize-none"
                  placeholder="Optional details…"></textarea>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-mono uppercase tracking-wide text-mist mb-1.5">Status</label>
          <select id="statusInput" class="w-full rounded-md bg-ink border border-panel2 px-3 py-2.5 text-sm text-ivory focus:border-signal outline-none">
            <option value="pending">Pending</option>
            <option value="in_progress">In progress</option>
            <option value="completed">Completed</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-mono uppercase tracking-wide text-mist mb-1.5">Priority</label>
          <select id="priorityInput" class="w-full rounded-md bg-ink border border-panel2 px-3 py-2.5 text-sm text-ivory focus:border-signal outline-none">
            <option value="low">Low</option>
            <option value="medium" selected>Medium</option>
            <option value="high">High</option>
            <option value="urgent">Urgent</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block text-xs font-mono uppercase tracking-wide text-mist mb-1.5">Due date</label>
        <input type="date" id="dueDateInput"
               class="w-full rounded-md bg-ink border border-panel2 px-3.5 py-2.5 text-sm text-ivory focus:border-signal outline-none">
      </div>

      <div class="flex items-center justify-between pt-2">
        <button type="button" id="deleteTaskBtn"
                class="hidden text-flare text-sm font-medium hover:underline">
          Delete entry
        </button>
        <div class="flex gap-3 ml-auto">
          <button type="button" id="cancelModal"
                  class="px-4 py-2 rounded-md text-sm font-medium text-mist hover:text-ivory transition-colors">
            Cancel
          </button>
          <button type="submit"
                  class="bg-signal text-ink font-semibold text-sm rounded-md px-4 py-2.5 hover:brightness-110 transition-all">
            Save entry
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>window.APP_BASE_URL = "<?= BASE_URL ?>";</script>
<script src="<?= BASE_URL ?>/assets/js/tasks.js"></script>
<?php require __DIR__ . '/includes/footer.php'; ?>
