# Task Tracker

A self-hosted Laravel app for keeping on top of household chores — a daily
checklist you tick off, tasks that repeat on their own schedule, and a
calendar of what's due and what got done.

## Features

- **Today** — the day's chores as a checklist, with progress for the day, a
  per-day bar for the week, overdue one-off tasks, prev/next day navigation,
  and a quick-add modal
- **Tasks** — full CRUD, grouped by category (cleaning, kitchen, laundry,
  bathroom, yard & garden, errands & bills, maintenance, other). Each task
  is one-off or repeats:
  - every *n* days
  - every *n* weeks on chosen weekdays (e.g. Wed & Sat)
  - every *n* months on the start date's day (clamped to shorter months)
  - with an optional end date, or paused without losing history
- **Calendar** — month grid showing each day's chores and how many were
  done; pick a day to tick things off or add a task on that date
- **Starter chores** — 32 common household chores on sensible schedules
  (daily dishes, laundry Wed & Sat, bathroom Saturdays, bills on the 10th,
  aircon filters monthly, curtains quarterly…). A step-by-step dialog asks
  whether to add or skip each one, and added chores are editable like any
  other task

## How it works

- `tasks` holds each chore and its repeat rule; `Task::occursOn()` decides
  whether it falls on a given day.
- `task_completions` holds one row per ticked-off occurrence
  (`task_id` + `due_on`), so history is kept per day.
- `chore_templates` is the starter list, kept in sync by
  `ChoreTemplateSeeder` (see `database/seeders`). Users get their own copies
  via `App\Services\StarterChores`.
- `App\Services\TaskSchedule` expands tasks into days for the dashboard and
  calendar.

## Tech stack

- Laravel 13, PHP 8.4
- Livewire + Volt for the dashboard and calendar
- Tailwind CSS, Alpine.js
- SQLite (default) — no separate database server required

## Local development

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm run build   # or `npm run dev` while working on frontend
php artisan serve
```

Visit `http://localhost:8000`.

### With DDEV

`.ddev/config.yaml` runs the app at `https://task-tracker.ddev.site`
(PHP 8.4, nginx-fpm). It has no database container: the app uses SQLite,
the same as the production image. DDEV's `.env` rewriting is also turned
off, so set `APP_URL=https://task-tracker.ddev.site` in `.env` yourself.

```bash
ddev start
ddev composer install
ddev artisan migrate --seed       # or migrate:fresh --seed to start over
npm install && npm run build      # on the host (node_modules is host-built)
```

In the `local` environment the seeder also
creates `test@example.com` / `password` with the starter chores and a
partly ticked-off past week, so the dashboard and calendar have data.

## Running the tests

```bash
php artisan test
```

## Deploying with Docker / Portainer

See [README-DOCKER.md](README-DOCKER.md).

## License

Released under the [MIT License](LICENSE.md).
