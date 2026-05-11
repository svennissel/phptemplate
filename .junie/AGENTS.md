# Project Guidelines

## 1. Architecture & Code Structure
*   **Separation of Logic and Presentation**: Business logic belongs in PHP files (Controller level), while presentation is exclusively handled in Twig templates. No HTML code should be generated within PHP files.
*   **Reusable Components**: Complex UI elements such as modals or list components should be moved to separate Twig files and included via `{% include %}` (e.g., `user_edit_modal.html.twig`).
*   **Includes**: Shared logic (Auth, DB, Twig setup) must be included via the files in the `public/includes/` directory.
*   **Language**: The code and developer documentation should be in English.

## 2. Security
*   **SQL Injection**: Use PDO with prepared statements for all database queries without exception. Avoid `SELECT *` and explicitly specify the required columns instead.
*   **XSS Protection**: Use Twig's automatic escaping. Variables that might contain HTML must be handled correctly.
*   **Authentication**: Sensitive pages must call `requireLogin()`.

## 3. UI/UX & Design (Mobile-First)
*   **Viewport**: The project is optimized for mobile devices (max-width: 480px). New styles must be defined in `style.css` and respect the mobile-first concept.
*   **Interaction**: Use modals instead of page changes for important actions (such as editing users) to maintain an app-like experience.
*   **Browser History**: Modals that could fill the entire screen should be managed via `history.pushState` so that the browser's back button closes the modal.
*   **Font Awesome**: Use Font Awesome for icons. The `update_fontawesome.sh` script updates the icons and must be executed after every change to the icon set. Before running it, the unicode must be added/adjusted in `css/fontawesome.css` and in the `update_fontawesome.sh` script in the UNICODES variable.
*   **Language**: The application language is German.

## 4. Database Schema
*   **Migrations**: Changes to the database schema must be updated in both `public/sql/schema.sql` (for new installations) and `tests/setup_test_db.sql` (for tests).
*   **Migrations scripts**: For each database change, a migration script must be created in `public/sql/`. 

## 5. Quality Assurance (Testing)
*   **Test Coverage**: New features (e.g., new pages or complex logic) must be secured by corresponding Playwright tests in `tests/e2e/` or PHPUnit tests in `tests/`.
*   **Syntax Check**: Perform a syntax check for affected PHP files before committing (`php -l path/to/file.php`).
*   **Test Execution**: After each change, Junie will run the  unit tests to check the correctness of the proposed solution: `./run_unittest.sh`
    The playwright test will be executed after bigger or complexer changes: `./run_playwright.sh --project=Chromium` 
*   **Unit Tests**: All unit tests are located in the `tests` folder.
*   **Playwright Tests**: All playwright tests are located in the `tests/e2e` folder.
