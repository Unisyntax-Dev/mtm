# Mini Task Manager

**Mini Task Manager** is a WordPress plugin that provides a simple task tracking system with a **React + TypeScript** front-end and a custom **REST API**.
It allows you to add tasks with a title and description, display the latest tasks, and delete them — all without page reloads.

## Features
- Add new tasks with a title and optional description
- Display the **last N tasks** (default: 5)
- Delete tasks directly from the list
- Update the task list without reloading the page
- Secure input handling and sanitization
- Admin settings page:
  - Configure the number of tasks shown
  - Enable or disable task deletion and editing
- Built with:
  - **React** for UI
  - **TypeScript** for type safety
  - **SCSS** for styles
  - **Webpack** for asset bundling

## Development
1. Install build dependencies:
   `npm install`
2. Build assets for production:
   `npm run build`
   (outputs to `assets/dist`)
3. Start development build (with watch mode):
   `npm run dev`
4. REST API endpoints:
   * `GET /wp-json/mtm/v1/tasks` — list tasks
   * `POST /wp-json/mtm/v1/tasks` — create task
   * `DELETE /wp-json/mtm/v1/tasks/{id}` — delete task
   * `POST /wp-json/mtm/v1/tasks/{id}` — update task

## Installation
1. Upload the plugin directory to:
   `wp-content/plugins/mini-task-manager`
   or install the zipped package via the WordPress admin panel.
2. Activate the plugin from **Plugins → Installed Plugins**.

## Usage
1. Insert the shortcode:
   `[mini_task_manager]`
   into any post or page to display the task form and recent tasks.
2. Configure settings under **Settings → Mini Task Manager**, including:
   * Number of latest tasks shown
   * Whether tasks can be deleted or edited from the list

## Uninstall
Removing the plugin from WordPress will delete its custom `mtm_tasks` database table.

