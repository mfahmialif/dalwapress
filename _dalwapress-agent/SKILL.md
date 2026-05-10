---
name: dalwapress-agent
description: Project guide for coding agents working on the Dalwa Press codebase. Use when modifying the Laravel backend, Vue frontend, admin/author/editor dashboards, role-based routing, submission/editor assignment flows, seeders, migrations, or validation/build workflow in C:\laragon\www\dalwapress.
---

# Dalwa Press Agent

## Core Workflow

Use this skill to continue work in the Dalwa Press repo without rediscovering the project shape.

1. Treat `backend/` as the Laravel API and `frontend/` as the Vue 3/Vite app.
2. Preserve the existing role portals:
   - Admin/Operator: `/administrator`
   - Author: `/author`
   - Editor: `/editor`
3. Prefer existing page/layout patterns before inventing UI. Admin, Author, and Editor dashboards use the same admin-style shell: sidebar, navbar, horizontal nav, footer, CSS theme variables, and `simplebar-vue`.
4. Keep behavior scoped. Do not refactor public landing pages while changing dashboards unless explicitly asked.
5. Before changing shared auth, routing, migrations, or submission behavior, read `references/project-map.md`.

## Backend Conventions

- Routes live in `backend/routes/api.php`.
- Auth uses Sanctum token auth through `AuthController@login`.
- Login roles currently include `Admin`, `Operator`, `Author`, and `Editor`.
- Seeder is expected to print copy-paste login credentials after `php artisan migrate:fresh --seed`.
- Submission statuses are: `submitted`, `under_review`, `revision`, `accepted`, `rejected`, `published`.
- Author submission endpoints are under `/api/author`.
- Editor endpoints are under `/api/editor` and must only expose assigned submissions.
- Admin/Operator editor assignment uses:
  - `GET /api/editors`
  - `POST /api/submissions/{submission}/assign-editors`

When adding backend behavior:

- Add relationship methods to models instead of ad hoc queries in controllers when the relationship is reused.
- Use role checks in portal controllers (`Author`, `Editor`) and keep Admin/Operator-only operations guarded.
- For editor reviews, fill reviewer identity from the authenticated editor user, not free-form frontend input.

## Frontend Conventions

- Router lives in `frontend/src/router/index.js`.
- Axios instance lives in `frontend/src/axios.js`.
- Shared asset helpers live in `frontend/src/utils/asset.js`.
- Admin layout components live in `frontend/src/components/layouts/admin`.
- Author layout components live in `frontend/src/components/layouts/author`.
- Editor layout components live in `frontend/src/components/layouts/editor`.

For dashboard pages:

- Use the current admin visual language: `var(--bg-card)`, `var(--bg-input)`, `var(--border)`, `var(--text-heading)`, `var(--text-body)`, `var(--text-muted)`, `var(--color-accent)`.
- Use Material Symbols already present in the project for icons.
- Use `vue-multiselect` for searchable selects in admin forms, especially editor assignment.
- Avoid wrapping `VueMultiselect` in a native `<label>`; use `div.field` plus a text label to avoid click/select bugs.
- For submit buttons, add a loading state, disable while saving, and update local reactive data from the API response when possible instead of reloading the whole page.

## Validation

Run focused checks after changes:

```powershell
php -l backend\app\Http\Controllers\SomeController.php
php artisan route:list --path=editor
php artisan migrate --pretend
node "C:\Program Files\nodejs\node_modules\npm\bin\npm-cli.js" run build
```

Prefer the direct `node ... npm-cli.js` build command on this machine because `npm.ps1` may emit unrelated PowerShell host warnings after Vite succeeds.

## Reference

Read `references/project-map.md` when the task touches roles, routes, dashboard layout, submission review, editor assignment, seed users, or known pitfalls.
