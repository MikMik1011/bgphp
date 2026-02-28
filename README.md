# BG++ (BGPHP)

BG++ is a PHP 8.2 + Apache web app for tracking live bus arrivals, with user authentication and favorite stations.

## What It Includes

- Station search by name or by nearby location
- Live arrivals with map rendering
- User registration/login/logout
- Favorite stations with optional notes
- Profile page with quick links back to station tracking

## Tech Stack

- PHP 8.2 (Apache)
- MySQL 8
- APCu caching (stations/arrivals)
- jQuery + Leaflet + Select2 on frontend

## Recommended Run Mode

It is strongly advisable to run this project with **Docker Compose**, because APCu behavior and PHP runtime setup are most consistent in the containerized environment used by this repo.

## Quick Start

```bash
docker compose up --build
```

Then open:

- App: `http://localhost:8080`
- MySQL: `localhost:3306`

Stop:

```bash
docker compose down
```

Reset local DB volume:

```bash
docker compose down -v
```

## Project Layout

- `public/` page entry points and API routes
- `public/js/index/` modular index-page logic
- `public/css/` shared styling
- `src/service/` auth/session/favorites/business services
- `src/db/` schema and DB access

