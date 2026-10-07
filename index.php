<?php
$pageTitle = 'Todo List';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/app.js" defer></script>
</head>
<body>
    <main class="page">
        <header class="page-header">
            <p class="eyebrow">PERSONAL ORGANIZER</p>
            <h1><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="intro">A small list for the things you need to get done.</p>
        </header>

        <section class="todo-panel" aria-label="Todo list">
            <p id="error-message" class="error-message" role="alert" hidden></p>
            <form id="todo-form" class="new-todo-form">
                <label for="new-todo">Add a new todo</label>
                <div class="form-row">
                    <input id="new-todo" type="text" maxlength="200" placeholder="What needs doing?" autocomplete="off" required>
                    <button type="submit">Add Todo</button>
                </div>
            </form>

            <div class="list-heading">
                <h2>My Todos</h2>
                <span id="todo-count" class="todo-count" aria-live="polite">Loading...</span>
            </div>
            <ul id="todo-list" class="todo-list" aria-label="Todos"></ul>
            <p id="empty-message" class="empty-message" hidden>Nothing here yet. Add a todo above to get started.</p>

            <footer class="list-footer">
                <nav class="filters" aria-label="Filter todos">
                    <button type="button" class="filter-button selected" data-filter="all" aria-pressed="true">All</button>
                    <button type="button" class="filter-button" data-filter="active" aria-pressed="false">Active</button>
                    <button type="button" class="filter-button" data-filter="completed" aria-pressed="false">Completed</button>
                </nav>
                <button id="clear-completed" type="button" class="text-button">Clear completed</button>
            </footer>
        </section>
        <p class="storage-note">Your list is saved on this computer by the local PHP server.</p>
    </main>
</body>
</html>
