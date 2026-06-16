# Environment templates

Platform-specific `.env` references. Copy the right file to `.env` in the project root (or set variables in your hosting dashboard).

| File | Use when |
|------|----------|
| [../.env.example](../.env.example) | Local dev (Windows/macOS/Herd) |
| [docker.env.example](./docker.env.example) | `docker compose up` |
| [render.env.example](./render.env.example) | [Render](https://render.com) (also used by Docker entrypoint on Render) |
| [railway.env.example](./railway.env.example) | [Railway](https://railway.app) |

```bash
# Docker
cp env/docker.env.example .env

# Local (first install)
cp .env.example .env
```
