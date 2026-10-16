# Deploying Task Tracker with Docker / Portainer

This app ships as a single container: Apache + PHP 8.4, SQLite for the
database, with the DB file and `storage/` (logs, sessions, cache) persisted
on two named Docker volumes so they survive restarts and image rebuilds.

Every push of a `vX.Y.Z` tag builds and publishes a multi-arch image
(amd64 + arm64) to GitHub Container Registry via
`.github/workflows/docker-publish.yml`.

## 1. Generate an APP_KEY

Laravel requires a stable encryption key. Generate one once and keep it
somewhere safe (a password manager, or Portainer's environment variables) —
reuse the same key on every redeploy.

```bash
docker run --rm php:8.4-cli php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
```

## 2. Deploy

### Option A — Pull the prebuilt image (recommended)

> **Registry access:** the image is published to
> `ghcr.io/daezerwin/task-tracker`. Because the GitHub repo is private, the
> package is private too. Either add a GHCR registry in Portainer
> (**Registries → Add registry → Custom**, URL `ghcr.io`, your GitHub
> username, and a personal access token with `read:packages`), or make the
> package public from its page on GitHub → **Package settings** →
> **Change visibility**.

1. In Portainer, go to **Stacks → Add stack**, choose **Web editor**.
2. Paste in the contents of [`docker-compose.prod.yml`](docker-compose.prod.yml).
3. Under **Environment variables**, add:
   - `APP_KEY` = the `base64:...` value from step 1
   - `APP_URL` = e.g. `http://your-server-ip:8099`
4. Click **Deploy the stack**.

To pin a release instead of tracking `latest`, change the `image:` line to
e.g. `ghcr.io/daezerwin/task-tracker:v1.0.0`.

### Option B — Build from source

1. In Portainer, go to **Stacks → Add stack**, choose **Repository**, and
   point it at this Git repo (with credentials, since it's private).
   Portainer builds [`Dockerfile`](Dockerfile) using
   [`docker-compose.yml`](docker-compose.yml).
2. Add the same `APP_KEY` / `APP_URL` environment variables as above.
3. Deploy — the first build takes a minute or two.

Or over SSH:

```bash
git clone git@github.com:daezerwin/task-tracker.git
cd task-tracker
export APP_KEY="base64:...(from step 1)"
export APP_URL="http://your-server-ip:8099"
docker compose up -d --build
```

## 3. Ports and reverse proxy

Both compose files publish host port **8099** (`8099:80`) so this can run
alongside Baby Tracker on 8080. Point your existing reverse proxy (Nginx
Proxy Manager, Traefik, Caddy, …) at `http://<server-ip>:8099`.

## 4. Accounts

Anyone who can reach the app can register, and email verification is off
(no mail server needed). If it's exposed to the internet, register your
own account(s) and then restrict access at the proxy, or ask for
registration to be switched off.

## 5. Starter chores

On every container start, `docker/entrypoint.sh` runs migrations and then
`db:seed`, which syncs the starter chore list (`ChoreTemplateSeeder`). It
updates templates by title and removes ones no longer listed, so changes
to that list reach this deployment on the next upgrade. It never touches
users' own tasks. Each user copies the starter chores into their list from
the dashboard or the Tasks page.

## 6. Backing up your data

Everything that matters lives in two named volumes (Portainer prefixes
them with the stack name):

- `task_tracker_database` — the SQLite database file
- `task_tracker_storage` — logs, cached sessions

```bash
docker run --rm -v task_tracker_database:/data -v $(pwd):/backup alpine \
    tar czf /backup/task-tracker-database-backup.tar.gz -C /data .
```

Restore by reversing the tar direction into a fresh volume before starting
the container.

## 7. Updating to a new version

**Pulling (Option A):** in Portainer, open the stack and click **Pull and
redeploy** (or `docker compose pull && docker compose up -d` over SSH).

**Building (Option B):**

```bash
git pull
docker compose up -d --build
```

Migrations and the starter-chore sync run automatically on every start and
are safe to repeat.
