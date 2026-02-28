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
