/**
 * Signal — Task Log controller
 * Talks to /api/tasks.php via fetch(). No frameworks, no build step.
 */

const API_URL = (window.APP_BASE_URL || '') + '/api/tasks.php';

const els = {
  list: document.getElementById('taskList'),
  loading: document.getElementById('loadingState'),
  empty: document.getElementById('emptyState'),
  search: document.getElementById('searchInput'),
  statusFilter: document.getElementById('statusFilter'),
  priorityFilter: document.getElementById('priorityFilter'),
  sortSelect: document.getElementById('sortSelect'),
  modal: document.getElementById('taskModal'),
  modalTitle: document.getElementById('modalTitle'),
  form: document.getElementById('taskForm'),
  taskId: document.getElementById('taskId'),
  titleInput: document.getElementById('titleInput'),
  descriptionInput: document.getElementById('descriptionInput'),
  statusInput: document.getElementById('statusInput'),
  priorityInput: document.getElementById('priorityInput'),
  dueDateInput: document.getElementById('dueDateInput'),
  deleteBtn: document.getElementById('deleteTaskBtn'),
  openCreateBtn: document.getElementById('openCreateModal'),
  closeModalBtn: document.getElementById('closeModal'),
  cancelModalBtn: document.getElementById('cancelModal'),
  toast: document.getElementById('toast'),
};

let debounceTimer = null;

const PRIORITY_LABEL = { low: 'Low', medium: 'Medium', high: 'High', urgent: 'Urgent' };
const STATUS_LABEL = { pending: 'Pending', in_progress: 'In progress', completed: 'Completed' };

// ---------- API helpers ----------

async function apiRequest(method, { id = null, body = null } = {}) {
  const url = id ? `${API_URL}?id=${id}` : API_URL;
  const options = { method, headers: { 'Content-Type': 'application/json' } };
  if (body) options.body = JSON.stringify(body);

  const res = await fetch(url, options);
  const data = await res.json().catch(() => ({}));

  if (!res.ok) {
    throw new Error(data.error || `Request failed (${res.status})`);
  }
  return data;
}

function buildQuery() {
  const params = new URLSearchParams();
  if (els.search.value.trim()) params.set('search', els.search.value.trim());
  if (els.statusFilter.value) params.set('status', els.statusFilter.value);
  if (els.priorityFilter.value) params.set('priority', els.priorityFilter.value);
  if (els.sortSelect.value) params.set('sort', els.sortSelect.value);
  return params.toString();
}

// ---------- Rendering ----------

function formatDueDate(dateStr) {
  if (!dateStr) return { text: 'No due date', overdue: false };
  const due = new Date(dateStr + 'T00:00:00');
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const overdue = due < today;
  const text = due.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
  return { text, overdue };
}

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str ?? '';
  return div.innerHTML;
}

function renderTasks(tasks) {
  els.loading.classList.add('hidden');

  if (tasks.length === 0) {
    els.list.innerHTML = '';
    els.empty.classList.remove('hidden');
    return;
  }
  els.empty.classList.add('hidden');

  els.list.innerHTML = tasks.map((task) => {
    const due = formatDueDate(task.due_date);
    const ticket = `TSK-${String(task.id).padStart(3, '0')}`;
    const isDone = task.status === 'completed';

    return `
      <div class="task-row group flex items-start sm:items-center gap-4 px-5 py-4 hover:bg-panel2/40 transition-colors cursor-pointer flex-wrap sm:flex-nowrap"
           data-id="${task.id}">
        <span class="ticket-id text-mist text-xs w-16 shrink-0">${ticket}</span>

        <div class="flex-1 min-w-[160px]">
          <p class="text-sm font-medium ${isDone ? 'line-through text-mist' : 'text-ivory'}">${escapeHtml(task.title)}</p>
          ${task.description ? `<p class="text-xs text-mist mt-0.5 truncate max-w-md">${escapeHtml(task.description)}</p>` : ''}
        </div>

        <span class="stamp stamp-${task.priority}">${PRIORITY_LABEL[task.priority]}</span>
        <span class="stamp stamp-${task.status}">${STATUS_LABEL[task.status]}</span>

        <span class="text-xs font-mono w-20 text-right shrink-0 ${due.overdue && !isDone ? 'text-flare' : 'text-mist'}">
          ${due.text}
        </span>
      </div>
    `;
  }).join('');

  document.querySelectorAll('.task-row').forEach((row) => {
    row.addEventListener('click', () => openEditModal(row.dataset.id));
  });
}

function showToast(message, isError = false) {
  els.toast.textContent = message;
  els.toast.className = `fixed top-6 right-6 z-50 px-4 py-3 rounded-md text-sm font-medium shadow-lg ${
    isError ? 'bg-flare text-ink' : 'bg-sage text-ink'
  }`;
  els.toast.classList.remove('hidden');
  clearTimeout(showToast._timer);
  showToast._timer = setTimeout(() => els.toast.classList.add('hidden'), 3000);
}

// ---------- Data loading ----------

async function loadTasks() {
  els.loading.classList.remove('hidden');
  els.empty.classList.add('hidden');
  try {
    const query = buildQuery();
    const res = await fetch(`${API_URL}?${query}`);
    const json = await res.json();
    if (!res.ok) throw new Error(json.error || 'Could not load tasks.');
    renderTasks(json.tasks || []);
  } catch (err) {
    showToast(err.message, true);
    els.loading.classList.add('hidden');
  }
}

// ---------- Modal handling ----------

function openCreateModal() {
  els.form.reset();
  els.taskId.value = '';
  els.modalTitle.textContent = 'New log entry';
  els.deleteBtn.classList.add('hidden');
  els.priorityInput.value = 'medium';
  els.statusInput.value = 'pending';
  els.modal.classList.remove('hidden');
  els.titleInput.focus();
}

async function openEditModal(id) {
  try {
    const res = await fetch(`${API_URL}?id=${id}`);
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Could not load entry.');

    const task = data.task;
    els.taskId.value = task.id;
    els.titleInput.value = task.title;
    els.descriptionInput.value = task.description || '';
    els.statusInput.value = task.status;
    els.priorityInput.value = task.priority;
    els.dueDateInput.value = task.due_date || '';
    els.modalTitle.textContent = `Edit TSK-${String(task.id).padStart(3, '0')}`;
    els.deleteBtn.classList.remove('hidden');
    els.modal.classList.remove('hidden');
    els.titleInput.focus();
  } catch (err) {
    showToast(err.message, true);
  }
}

function closeModal() {
  els.modal.classList.add('hidden');
}

// ---------- Form submit / delete ----------

els.form.addEventListener('submit', async (e) => {
  e.preventDefault();

  const payload = {
    title: els.titleInput.value.trim(),
    description: els.descriptionInput.value.trim(),
    status: els.statusInput.value,
    priority: els.priorityInput.value,
    due_date: els.dueDateInput.value || null,
  };

  if (!payload.title) {
    showToast('Title is required.', true);
    return;
  }

  const id = els.taskId.value;

  try {
    if (id) {
      await apiRequest('PUT', { id, body: payload });
      showToast('Entry updated.');
    } else {
      await apiRequest('POST', { body: payload });
      showToast('Entry logged.');
    }
    closeModal();
    loadTasks();
  } catch (err) {
    showToast(err.message, true);
  }
});

els.deleteBtn.addEventListener('click', async () => {
  const id = els.taskId.value;
  if (!id) return;
  if (!confirm('Delete this log entry? This cannot be undone.')) return;

  try {
    await apiRequest('DELETE', { id });
    showToast('Entry deleted.');
    closeModal();
    loadTasks();
  } catch (err) {
    showToast(err.message, true);
  }
});

// ---------- Event wiring ----------

els.openCreateBtn.addEventListener('click', openCreateModal);
els.closeModalBtn.addEventListener('click', closeModal);
els.cancelModalBtn.addEventListener('click', closeModal);
els.modal.addEventListener('click', (e) => {
  if (e.target === els.modal) closeModal();
});
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && !els.modal.classList.contains('hidden')) closeModal();
});

els.search.addEventListener('input', () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(loadTasks, 300);
});
els.statusFilter.addEventListener('change', loadTasks);
els.priorityFilter.addEventListener('change', loadTasks);
els.sortSelect.addEventListener('change', loadTasks);

// ---------- Init ----------

loadTasks();
