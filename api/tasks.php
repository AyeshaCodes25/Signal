<?php
/**
 * /api/tasks.php
 * A small REST-style JSON API for tasks, scoped to the logged-in user.
 *
 * GET    /api/tasks.php            list tasks (supports ?status=&priority=&search=&sort=)
 * GET    /api/tasks.php?id=5       fetch a single task
 * POST   /api/tasks.php            create a task            (JSON body)
 * PUT    /api/tasks.php?id=5       update a task             (JSON body)
 * DELETE /api/tasks.php?id=5       delete a task
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
requireLoginJson();

$pdo    = getDbConnection();
$userId = currentUserId();
$method = $_SERVER['REQUEST_METHOD'];

const ALLOWED_STATUSES   = ['pending', 'in_progress', 'completed'];
const ALLOWED_PRIORITIES = ['low', 'medium', 'high', 'urgent'];

function respond(int $code, array $payload): never
{
    http_response_code($code);
    echo json_encode($payload);
    exit;
}

function readJsonBody(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

switch ($method) {

    case 'GET':
        if (isset($_GET['id'])) {
            $id = (int) $_GET['id'];
            $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ? AND user_id = ?');
            $stmt->execute([$id, $userId]);
            $task = $stmt->fetch();

            if (!$task) {
                respond(404, ['error' => 'Task not found.']);
            }
            respond(200, ['task' => $task]);
        }

        // List with optional filters
        $conditions = ['user_id = ?'];
        $params = [$userId];

        if (!empty($_GET['status']) && in_array($_GET['status'], ALLOWED_STATUSES, true)) {
            $conditions[] = 'status = ?';
            $params[] = $_GET['status'];
        }
        if (!empty($_GET['priority']) && in_array($_GET['priority'], ALLOWED_PRIORITIES, true)) {
            $conditions[] = 'priority = ?';
            $params[] = $_GET['priority'];
        }
        if (!empty($_GET['search'])) {
            $conditions[] = '(title LIKE ? OR description LIKE ?)';
            $like = '%' . $_GET['search'] . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $sortMap = [
            'due_date_asc'  => 'due_date IS NULL, due_date ASC',
            'due_date_desc' => 'due_date IS NULL, due_date DESC',
            'newest'        => 'created_at DESC',
            'priority'      => "FIELD(priority, 'urgent','high','medium','low')",
        ];
        $sort = $sortMap[$_GET['sort'] ?? ''] ?? $sortMap['due_date_asc'];

        $sql = 'SELECT * FROM tasks WHERE ' . implode(' AND ', $conditions) . ' ORDER BY ' . $sort;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        respond(200, ['tasks' => $stmt->fetchAll()]);

    case 'POST':
        $data = readJsonBody();
        $title = trim($data['title'] ?? '');

        if ($title === '' || strlen($title) > 150) {
            respond(422, ['error' => 'Title is required (max 150 characters).']);
        }

        $status   = in_array($data['status'] ?? '', ALLOWED_STATUSES, true) ? $data['status'] : 'pending';
        $priority = in_array($data['priority'] ?? '', ALLOWED_PRIORITIES, true) ? $data['priority'] : 'medium';
        $description = trim($data['description'] ?? '') ?: null;
        $dueDate = !empty($data['due_date']) ? $data['due_date'] : null;

        if ($dueDate !== null && !DateTime::createFromFormat('Y-m-d', $dueDate)) {
            respond(422, ['error' => 'due_date must be in YYYY-MM-DD format.']);
        }

        $stmt = $pdo->prepare(
            'INSERT INTO tasks (user_id, title, description, status, priority, due_date)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $title, $description, $status, $priority, $dueDate]);

        $newId = (int) $pdo->lastInsertId();
        $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ?');
        $stmt->execute([$newId]);

        respond(201, ['task' => $stmt->fetch()]);

    case 'PUT':
        if (empty($_GET['id'])) {
            respond(400, ['error' => 'Task id is required.']);
        }
        $id = (int) $_GET['id'];

        $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
        $existing = $stmt->fetch();

        if (!$existing) {
            respond(404, ['error' => 'Task not found.']);
        }

        $data = readJsonBody();
        $title = isset($data['title']) ? trim($data['title']) : $existing['title'];

        if ($title === '' || strlen($title) > 150) {
            respond(422, ['error' => 'Title is required (max 150 characters).']);
        }

        $status      = in_array($data['status'] ?? '', ALLOWED_STATUSES, true) ? $data['status'] : $existing['status'];
        $priority    = in_array($data['priority'] ?? '', ALLOWED_PRIORITIES, true) ? $data['priority'] : $existing['priority'];
        $description = array_key_exists('description', $data) ? (trim($data['description']) ?: null) : $existing['description'];
        $dueDate     = array_key_exists('due_date', $data) ? ($data['due_date'] ?: null) : $existing['due_date'];

        if ($dueDate !== null && !DateTime::createFromFormat('Y-m-d', $dueDate)) {
            respond(422, ['error' => 'due_date must be in YYYY-MM-DD format.']);
        }

        $stmt = $pdo->prepare(
            'UPDATE tasks SET title = ?, description = ?, status = ?, priority = ?, due_date = ?
             WHERE id = ? AND user_id = ?'
        );
        $stmt->execute([$title, $description, $status, $priority, $dueDate, $id, $userId]);

        $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ?');
        $stmt->execute([$id]);

        respond(200, ['task' => $stmt->fetch()]);

    case 'DELETE':
        if (empty($_GET['id'])) {
            respond(400, ['error' => 'Task id is required.']);
        }
        $id = (int) $_GET['id'];

        $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);

        if ($stmt->rowCount() === 0) {
            respond(404, ['error' => 'Task not found.']);
        }
        respond(200, ['deleted' => $id]);

    default:
        respond(405, ['error' => 'Method not allowed.']);
}
