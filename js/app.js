(function () {
    'use strict';

    const apiUrl = 'api.php';
    const form = document.getElementById('todo-form');
    const input = document.getElementById('new-todo');
    const list = document.getElementById('todo-list');
    const count = document.getElementById('todo-count');
    const emptyMessage = document.getElementById('empty-message');
    const errorMessage = document.getElementById('error-message');
    const clearButton = document.getElementById('clear-completed');
    const filterButtons = Array.from(document.querySelectorAll('.filter-button'));

    let todos = [];
    let currentFilter = 'all';
    let hasCompletedTodos = false;

    async function request(url, options) {
        const response = await fetch(url, options);
        const data = await response.json();
        if (!response.ok) throw new Error(data.error || 'The request could not be completed.');
        return data;
    }

    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.hidden = !message;
    }

    async function loadTodos() {
        try {
            showError('');
            const data = await request(apiUrl + '?filter=' + encodeURIComponent(currentFilter));
            todos = data.todos;
            hasCompletedTodos = data.hasCompleted;
            render(data.remaining);
        } catch (error) {
            showError(error.message);
            count.textContent = 'Unable to load todos';
        }
    }

    async function saveAction(action, values) {
        try {
            showError('');
            const data = await request(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(Object.assign({ action: action }, values || {}))
            });
            todos = data.todos;
            hasCompletedTodos = todos.some(function (todo) { return todo.completed; });
            render(todos.filter(function (todo) { return !todo.completed; }).length);
            return true;
        } catch (error) {
            showError(error.message);
            return false;
        }
    }

    function render(remaining) {
        list.replaceChildren();
        const visibleTodos = todos.filter(function (todo) {
            if (currentFilter === 'active') return !todo.completed;
            if (currentFilter === 'completed') return todo.completed;
            return true;
        });
        visibleTodos.forEach(function (todo) {
            const item = document.createElement('li');
            item.className = 'todo-item' + (todo.completed ? ' completed' : '');

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.checked = todo.completed;
            checkbox.setAttribute('aria-label', 'Mark "' + todo.text + '" as ' + (todo.completed ? 'active' : 'completed'));
            checkbox.addEventListener('change', function () {
                saveAction('toggle', { id: todo.id, completed: checkbox.checked });
            });

            const text = document.createElement('span');
            text.className = 'todo-text';
            text.textContent = todo.text;

            const actions = document.createElement('span');
            actions.className = 'item-actions';
            const editButton = document.createElement('button');
            editButton.type = 'button';
            editButton.textContent = 'Edit';
            editButton.setAttribute('aria-label', 'Edit "' + todo.text + '"');
            editButton.addEventListener('click', function () { beginEdit(todo, item); });

            const deleteButton = document.createElement('button');
            deleteButton.type = 'button';
            deleteButton.textContent = 'Delete';
            deleteButton.setAttribute('aria-label', 'Delete "' + todo.text + '"');
            deleteButton.addEventListener('click', function () { saveAction('delete', { id: todo.id }); });

            actions.append(editButton, deleteButton);
            item.append(checkbox, text, actions);
            list.appendChild(item);
        });

        const remainingCount = typeof remaining === 'number'
            ? remaining
            : todos.filter(function (todo) { return !todo.completed; }).length;
        count.textContent = remainingCount + (remainingCount === 1 ? ' item left' : ' items left');
        emptyMessage.hidden = visibleTodos.length > 0;
        clearButton.disabled = !hasCompletedTodos;
    }

    function beginEdit(todo, item) {
        const editor = document.createElement('input');
        editor.type = 'text';
        editor.maxLength = 200;
        editor.value = todo.text;
        editor.className = 'edit-input';
        editor.setAttribute('aria-label', 'Edit todo');

        const actions = item.querySelector('.item-actions');
        const saveButton = document.createElement('button');
        saveButton.type = 'button';
        saveButton.textContent = 'Save';
        const cancelButton = document.createElement('button');
        cancelButton.type = 'button';
        cancelButton.textContent = 'Cancel';

        function finish(save) {
            if (save && editor.value.trim()) {
                saveAction('edit', { id: todo.id, text: editor.value.trim() });
            } else {
                render();
            }
        }

        saveButton.addEventListener('click', function () { finish(true); });
        cancelButton.addEventListener('click', function () { finish(false); });
        editor.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') finish(true);
            if (event.key === 'Escape') finish(false);
        });
        item.querySelector('.todo-text').replaceWith(editor);
        actions.replaceChildren(saveButton, cancelButton);
        editor.focus();
        editor.select();
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        const text = input.value.trim();
        if (!text) return;
        saveAction('add', { text: text }).then(function (saved) {
            if (saved) {
                input.value = '';
                input.focus();
            }
        });
    });

    filterButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            currentFilter = button.dataset.filter;
            filterButtons.forEach(function (filterButton) {
                const selected = filterButton === button;
                filterButton.classList.toggle('selected', selected);
                filterButton.setAttribute('aria-pressed', String(selected));
            });
            loadTodos();
        });
    });

    clearButton.addEventListener('click', function () { saveAction('clear-completed'); });
    loadTodos();
}());
