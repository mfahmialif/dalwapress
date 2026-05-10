# Dalwa Press Project Map

## Repo Shape

- Root: `C:\laragon\www\dalwapress`
- Backend: `backend/` Laravel API
- Frontend: `frontend/` Vue 3 + Vite + Pinia
- API base in frontend: `frontend/src/axios.js`, default `/api`

## Roles And Portals

- `Admin` and `Operator`: `/administrator`, guarded by default route meta roles.
- `Author`: `/author`, guarded by `roles: ['Author']`.
- `Editor`: `/editor`, guarded by `roles: ['Editor']`.

Login redirect rules:

- Author -> `AuthorDashboard`
- Editor -> `EditorDashboard`
- Admin/Operator -> `AdminDashboard`

Seeder login accounts expected:

- `admin / password`
- `operator / password`
- `author / password`
- `editor / password`
- `editor2 / password`
- `editor3 / password`

## Backend Submission Model

Core models:

- `Submission`
- `SubmissionReview`
- `SubmissionRevision`
- `SubmissionEditorAssignment`

Important relationships:

- `Submission::category()`
- `Submission::user()`
- `Submission::reviews()`
- `Submission::revisions()`
- `Submission::editorAssignments()`
- `Submission::primaryEditorAssignment()`
- `SubmissionReview::editor()`

Editor assignment design:

- One primary editor per submission in practice.
- Multiple co-editors are allowed.
- Frontend sends `primary_editor_id`, `co_editor_ids[]`, and optional `note`.
- Editor portal must only show submissions assigned to the authenticated editor.

## Backend Controllers And Routes

Routes are in `backend/routes/api.php`.

Author portal:

- Controller: `AuthorPortalController`
- Prefix: `/api/author`

Editor portal:

- Controller: `EditorPortalController`
- Prefix: `/api/editor`
- Key endpoints:
  - `GET /api/editor/dashboard`
  - `GET /api/editor/submissions`
  - `GET /api/editor/submissions/{submission}`
  - `POST /api/editor/submissions/{submission}/reviews`
  - `GET /api/editor/profile`

Admin/Operator submission routes:

- Controller: `SubmissionController`
- `GET /api/editors`
- `POST /api/submissions/{submission}/assign-editors`
- `POST /api/submissions/{submission}/reviews`

## Frontend Layouts

Admin:

- `frontend/src/layouts/AdminLayout.vue`
- `frontend/src/components/layouts/admin/*`

Author:

- `frontend/src/layouts/AuthorLayout.vue`
- `frontend/src/components/layouts/author/*`
- `frontend/src/views/author/...`

Editor:

- `frontend/src/layouts/EditorLayout.vue`
- `frontend/src/components/layouts/editor/*`
- `frontend/src/views/editor/...`

When adding a new portal page, mirror admin-style foldering:

```text
views/<portal>/<feature>/Index.vue
views/<portal>/<feature>/Detail.vue
views/<portal>/<feature>/Form.vue
```

## UI Pitfalls

- `vue-multiselect` is installed and already used in admin pages.
- Do not wrap `VueMultiselect` with `<label>`; use `<div class="field">`.
- Use `:close-on-select="true"` for single selects.
- Use `:multiple="true"` and `:close-on-select="false"` for multi-selects.
- Avoid manual `activate()`/`deactivate()` on multiselect refs unless absolutely necessary; it caused select bugs in this project.
- Assignment save should not reload the entire page. Use the API response to update `submission.value` and reset only relevant form state.

## Verification Notes

Recommended frontend build command:

```powershell
node "C:\Program Files\nodejs\node_modules\npm\bin\npm-cli.js" run build
```

`npm run build` through PowerShell may print unrelated `npm.ps1` or cursor warnings after a successful Vite build. Prefer the direct node command above for cleaner verification.
