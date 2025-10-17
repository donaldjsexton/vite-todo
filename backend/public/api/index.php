<?php
declare(strict_types=1);

// ---- CORS (dev) ----
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
$allowed = ['http://localhost:5173', 'http://127.0.0.1:5173'];
if (in_array($origin, $allowed, true)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: *");
}
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

// ---- DB bootstrap ----
$DB_PATH = __DIR__ . '/../../data.sqlite';
$pdo = new PDO('sqlite:' . $DB_PATH, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
$pdo->exec('CREATE TABLE IF NOT EXISTS tasks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    text TEXT NOT NULL,
    done INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL DEFAULT (datetime(\'now\'))
)');

// ---- tiny router ----
$method = $_SERVER['REQUEST_METHOD'];
$path    = $_SERVER['REQUEST_URI'] ?? '/';
$parsed  = parse_url($path);
$uri     = $parsed['path'] ?? '/';

// convenience helper
function json_input(): array {
    $raw = file_get_contents('php://input') ?: '';
    if ($raw === '') return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

if ($uri === '/api/tasks' && $method === 'GET') {
    $stmt = $pdo->query('SELECT id, text, done, created_at FROM tasks ORDER BY id DESC');
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($uri === '/api/tasks' && $method === 'POST') {
    $data = json_input();
    $text = trim((string)($data['text'] ?? ''));
    if ($text === '') {
        http_response_code(400);
        echo json_encode(['error' => 'text is required']);
        exit;
    }
    $stmt = $pdo->prepare('INSERT INTO tasks (text, done) VALUES (:text, 0)');
    $stmt->execute([':text' => $text]);
    $id = (int)$pdo->lastInsertId();

    $stmt = $pdo->prepare('SELECT id, text, done, created_at FROM tasks WHERE id = :id');
    $stmt->execute([':id' => $id]);
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    exit;
}

// match /api/tasks/{id}
if (preg_match('#^/api/tasks/(\d+)$#', $uri, $m)) {
    $id = (int)$m[1];

    if ($method === 'PUT') {
        $data = json_input();
        $fields = [];
        $params = [':id' => $id];

        if (array_key_exists('text', $data)) {
            $fields[] = 'text = :text';
            $params[':text'] = (string)$data['text'];
        }
        if (array_key_exists('done', $data)) {
            $fields[] = 'done = :done';
            $params[':done'] = (int)!!$data['done'];
        }

        if (!$fields) {
            http_response_code(400);
            echo json_encode(['error' => 'nothing to update']);
            exit;
        }

        $sql = 'UPDATE tasks SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $stmt = $pdo->prepare('SELECT id, text, done, created_at FROM tasks WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            http_response_code(404);
            echo json_encode(['error' => 'not found']);
            exit;
        }
        echo json_encode($row);
        exit;
    }

    if ($method === 'DELETE') {
        $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = :id');
        $stmt->execute([':id' => $id]);
        echo json_encode(['ok' => true]);
        exit;
    }
}

// fallback
http_response_code(404);
echo json_encode(['error' => 'route not found']);

