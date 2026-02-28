# Repository Guidelines

## Project Structure & Module Organization
This is a PHP 8.2 + Apache app with a public web root.

- `public/`: web entry points (`index.php`, `login.php`, `register.php`, `profile.php`), API routes in `public/api/`, and static assets in `public/css/` and `public/js/`.
- `src/`: core backend code.
- `src/service/`: application services (user auth, favorites, API-facing logic).
- `src/buslogic/`: transport/business logic implementations.
- `src/db/`: schema and DB access helpers.
- `src/config/`: runtime config loading (`config.php`, `config.ini`).
- `_db/`: local database-related runtime files (if present in local setup).

## Build, Test, and Development Commands
- `docker compose up --build`: build and start PHP + MySQL locally.
- `docker compose down`: stop containers.
- `docker compose down -v`: stop containers and remove DB volume (reset local data).
- `docker compose logs -f php`: tail PHP/Apache logs.
- App URL: `http://localhost:8080` (MySQL exposed on `3306`).

## Coding Style & Naming Conventions
- Use 4-space indentation in PHP and keep braces on new lines for functions/blocks.
- Prefer `snake_case` for PHP function names (existing pattern: `create_user`, `login_user`).
- Keep files focused by responsibility (`*_service.php` for service code, API handlers under `public/api/`).
- Favor small, explicit helper functions over deeply nested inline logic.

## Testing Guidelines
Automated tests are not set up yet (no PHPUnit config currently in repo). For now:

- Validate changes manually via `http://localhost:8080`.
- Exercise affected routes/pages directly (e.g., login/register/profile and `public/api/*` endpoints).
- For DB changes, update `src/db/schema.sql` and verify containerized MySQL initialization.
- If adding tests, place them under a new `tests/` directory and use `*Test.php` naming.

## Commit & Pull Request Guidelines
- Follow the existing Conventional Commit pattern from history: `feat: ...`, `fix: ...`.
- Keep commits scoped to one logical change.
- PRs should include:
1. concise summary of behavior changes,
2. impacted paths/endpoints,
3. local verification steps,
4. screenshots for UI changes (`public/` pages).

## Security & Configuration Tips
- Treat credentials in `docker-compose.yml` and `src/config/config.ini` as local-only defaults.
- Do not commit production secrets.
- Sanitize and validate all user input in public endpoints and auth-related flows.

## Current Frontend Notes
- Index page JavaScript is split under `public/js/index/` (`api.js`, `helpers.js`, `map.js`, `favorites.js`, `app.js`).
- Keep `public/js/script.js` as a thin bootstrap that only initializes the index app.
- Avoid inline JavaScript handlers in `public/index.php`; bind UI events from `public/js/index/app.js`.
- Fair Usage modal open/close interactions are also bound from JS (`app.js`) via element IDs, not inline attributes.
- Shared page styling should use centralized CSS variables in `public/css/index.css` (`:root`) and reuse card/accent tokens instead of hardcoded per-rule colors.
- Visual style preference: keep UI flat (no drop shadows, no 3D depth effects); prioritize clean spacing, alignment, and consistent card widths for structure.
- Index layout should follow a two-step workflow structure (`Step 1: Find a Station`, `Step 2: Live Arrivals`) using a responsive grid that stacks on mobile.
- Keep auth forms (`login/register`) narrow via `.page-shell form` constraints; only `#myForm` on index should use full workflow column width.
- Profile page should surface username prominently in a dedicated profile header/hero block, with quick actions (`Back to home`, `Logout`) grouped near the greeting.
- Station selection UX on index should expose explicit mode toggles and a live selection status summary (city/mode/station) before arrivals are started.
- Index submit action should be stateful: enable only when a station is selected for the active mode and keep workflow status chips synchronized with user selections.
- Mode selection on index is tab-only (`.mode-toggle-btn` + hidden `#searchMode`); do not render a visible “Tip pretrage” select.
- Mode tabs must directly toggle `.name-search` / `.coords-search` visibility in JS without relying on `<option>` iteration.
- Apply theme-consistent styling to Select2 controls/dropdowns in `public/css/index.css` (`.select2-*`) so they match native form fields.
- When Select2 defaults override theme colors, use higher-specificity `.select2-container--default ...` rules (and `!important` if required) to enforce dropdown/search/input colors.
- Keep vertical spacing explicit around Select2 controls (especially in `.coords-search`) so dropdown fields are not visually glued to action buttons.
- Keep a dedicated top margin on `#submit` and explicit bottom margin on Select2 containers to preserve separation before the main submit action.
- Keep Select2 single-select control height aligned with native input/select height to avoid slimmer visual appearance.
- For Select2 single selects, vertically center text with flex (`display:flex; align-items:center`) on `.select2-selection__rendered` instead of relying on large line-height values.
- On mobile, keep map width constrained to its card (`#map { width: 100%; }`) and tune control/table spacing for readability; avoid `100vw` inside padded cards.
- For two-button mode tabs on mobile, use strict equal grid columns (`repeat(2, minmax(0,1fr))`) and `box-sizing: border-box` to prevent horizontal overflow.
- Workflow containers that use `width: 100%` with padding/border (e.g., `#result`, `#myForm`) must use `box-sizing: border-box` to avoid mobile overflow.
- Location-search helpers must defensively normalize/validate station coordinates and skip malformed entries instead of throwing on `station.coords`.
- Index supports deep links via query params: `?city=<cityKey>` preselects city; `?city=<cityKey>&uid=<stationUid>` auto-selects station in name mode and starts arrivals.
- Profile favorites rows act as quick links to index deep links; keep remove-button behavior separate from row-click navigation.
- PHP code style: use `snake_case` for custom PHP function/method names, parameter names, and local variables; keep call sites aligned when renaming.
- Remove dead PHP helpers when unreferenced (verify via `rg` before deletion) to keep services lean.
- Persist index workflow state in cookies: selected city (`bgpp_city`) and search mode tab (`bgpp_search_mode`) should be restored on load; URL query params still override restored defaults.
- Session policy is centralized in `src/service/session_service.php` via `start_secure_session()`: 30-day cookie lifetime, `HttpOnly`, `SameSite=Lax`, strict cookie-only session settings, and `Secure` enabled automatically on HTTPS.
- In location search mode, keep the max-distance range slider visually centered (label + slider track) using dedicated `#stationsMaxDistance-*` rules instead of generic input styling.
- Keep the UI flat but not plain: prefer subtle layered gradients for page backgrounds and ensure global link colors come from theme variables (`--link-main`, `--link-hover`) with profile quick-links explicitly using accent styling.
