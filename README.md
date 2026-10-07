# Legacy-style Todo application

A small Todo list with a PHP-rendered page and a PHP JSON API. The browser calls the API with vanilla JavaScript. Todo data is stored in a local file, so there is no database or external service.

## Requirements

- PHP 8 or newer.
- A web browser.

On macOS, install PHP with Homebrew if it is not already installed:

```sh
brew install php
```

## Start the application

In Terminal, move into the project folder and start PHP's built-in development server:

```sh
php -S localhost:8000
```

Keep that Terminal window running while you use the application. Press `Control-C` to stop the server.

## Open it in your browser

Visit [http://localhost:8000](http://localhost:8000). The browser loads the page and JavaScript, then JavaScript requests todo data from `api.php`.

## Where the todos are stored

The API saves todos in `data/todos.php`. The file is created when the first todo is added and persists between page refreshes and server restarts. It is a local data file, not a database. Back it up if you want to keep the list when moving the project.

## What each part does

- **PHP (`index.php`)** renders the page shell and loads its stylesheet and script.
- **PHP (`api.php`)** provides the JSON API. It reads and writes todo data and handles list, add, edit, delete, completion, filtering, and clear-completed requests.
- **HTML (`index.php`)** provides the form, list area, filter controls, and buttons.
- **CSS (`css/style.css`)** gives the page its simple, slightly old-school appearance.
- **JavaScript (`js/app.js`)** calls the PHP API with `fetch()`, updates the page, and handles user interactions.
