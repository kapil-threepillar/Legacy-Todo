<?php
// A small JSON API used by js/app.js. Todo data stays on the PHP server.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function sendJson($status, $data)
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function readTodos($file, $header)
{
    if (!is_file($file)) {
        return [];
    }
    $contents = file_get_contents($file);
    if ($contents === false || strpos($contents, $header) !== 0) {
        return [];
    }
    $todos = json_decode(substr($contents, strlen($header)), true);
    return is_array($todos) ? $todos : [];
}

function writeTodos($directory, $file, $header, $todos)
{
    if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
        return false;
    }
    $json = json_encode(array_values($todos), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return $json !== false && file_put_contents($file, $header . $json . "\n", LOCK_EX) !== false;
}

function limitTodoText($text)
{
    return function_exists('mb_substr') ? mb_substr($text, 0, 200) : substr($text, 0, 200);
}

$directory = __DIR__ . '/data';
$file = $directory . '/todos.php';
$fileHeader = "<?php http_response_code(404); exit; ?>\n";
$todos = readTodos($file, $fileHeader);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
    if (!in_array($filter, ['all', 'active', 'completed'], true)) {
        sendJson(400, ['error' => 'Unknown filter.']);
    }
    $visibleTodos = array_values(array_filter($todos, function ($todo) use ($filter) {
        if ($filter === 'active') return empty($todo['completed']);
        if ($filter === 'completed') return !empty($todo['completed']);
        return true;
    }));
    $remainingCount = count(array_filter($todos, function ($todo) {
        return empty($todo['completed']);
    }));
    sendJson(200, [
        'todos' => $visibleTodos,
        'remaining' => $remainingCount,
        'hasCompleted' => count($todos) > $remainingCount
    ]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: GET, POST');
    sendJson(405, ['error' => 'Use GET or POST.']);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input) || !isset($input['action'])) {
    sendJson(400, ['error' => 'Send a JSON body with an action.']);
}

$action = $input['action'];
$id = isset($input['id']) ? (string) $input['id'] : '';
if ($action === 'add') {
    $text = trim(isset($input['text']) ? (string) $input['text'] : '');
    if ($text === '') sendJson(400, ['error' => 'Todo text cannot be empty.']);
    $todos[] = ['id' => bin2hex(random_bytes(8)), 'text' => limitTodoText($text), 'completed' => false];
} elseif ($action === 'edit') {
    $text = trim(isset($input['text']) ? (string) $input['text'] : '');
    if ($text === '') sendJson(400, ['error' => 'Todo text cannot be empty.']);
    $found = false;
    foreach ($todos as &$todo) {
        if (isset($todo['id']) && $todo['id'] === $id) {
            $todo['text'] = limitTodoText($text);
            $found = true;
            break;
        }
    }
    unset($todo);
    if (!$found) sendJson(404, ['error' => 'Todo was not found.']);
} elseif ($action === 'toggle') {
    $found = false;
    foreach ($todos as &$todo) {
        if (isset($todo['id']) && $todo['id'] === $id) {
            $todo['completed'] = !empty($input['completed']);
            $found = true;
            break;
        }
    }
    unset($todo);
    if (!$found) sendJson(404, ['error' => 'Todo was not found.']);
} elseif ($action === 'delete') {
    $todos = array_values(array_filter($todos, function ($todo) use ($id) {
        return !isset($todo['id']) || $todo['id'] !== $id;
    }));
} elseif ($action === 'clear-completed') {
    $todos = array_values(array_filter($todos, function ($todo) {
        return empty($todo['completed']);
    }));
} else {
    sendJson(400, ['error' => 'Unknown action.']);
}

if (!writeTodos($directory, $file, $fileHeader, $todos)) {
    sendJson(500, ['error' => 'Could not save todos. Check that PHP can write to the data folder.']);
}

sendJson(200, ['todos' => $todos]);
