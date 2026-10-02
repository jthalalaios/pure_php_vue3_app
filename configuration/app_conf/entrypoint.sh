#!/bin/sh
set -e

APP_DIR="/var/www/html"
REDIS_HOST="${REDIS_HOST:-redis-alpine}"
REDIS_PORT="${REDIS_PORT:-6379}"

# Write Redis session configuration at startup so the host/port come from env vars
cat > /usr/local/etc/php/conf.d/session-redis.ini << EOF
session.save_handler = redis
session.save_path    = "tcp://${REDIS_HOST}:${REDIS_PORT}?timeout=2"
session.gc_maxlifetime = 7200
session.cookie_httponly = 1
session.cookie_samesite = Lax
EOF

# Fix permissions on the app directory (only when running as root).
# If HOST_UID/HOST_GID are provided (from the host), make the host user the owner
# and keep group as www-data so both the host user and the webserver can write.
HOST_UID=${HOST_UID:-}
HOST_GID=${HOST_GID:-}
if [ -z "$HOST_GID" ] && [ -n "$HOST_UID" ]; then
  HOST_GID="$HOST_UID"
fi

if [ "$(id -u)" -eq 0 ]; then
  if [ -n "$HOST_UID" ]; then
    chown -R "${HOST_UID}:www-data" "${APP_DIR}" || true
    find "${APP_DIR}" -type d -exec chmod 2775 {} \; || true
    find "${APP_DIR}" -type f -exec chmod 664 {} \; || true
  else
    # Default behaviour: keep www-data ownership for container-only setups
    find "${APP_DIR}" -type d -exec chmod 755 {} \; || true
    find "${APP_DIR}" -type f -exec chmod 644 {} \; || true
    chown -R www-data:www-data "${APP_DIR}" || true
  fi
fi

DB_HOST=${DB_HOST:-${POSTGRES_DB_HOST:-postgres-18.3-alpine}}
DB_PORT=${DB_PORT:-${POSTGRES_DB_PORT:-5432}}
DB_DATABASE=${DB_DATABASE:-${POSTGRES_DB_DATABASE}}
DB_USERNAME=${DB_USERNAME:-${POSTGRES_DB_USERNAME}}
DB_PASSWORD=${DB_PASSWORD:-${POSTGRES_DB_PASSWORD}}

MIGRATIONS_DIR="$APP_DIR/migrations"
MIGRATED_MARKER_DIR="$APP_DIR/.migrated"

echo "Running SQL migrations (if any) against ${DB_HOST}:${DB_PORT}..."
if [ -d "$MIGRATIONS_DIR" ]; then
  # wait for Postgres to be ready
  if command -v pg_isready >/dev/null 2>&1; then
    echo "Waiting for Postgres..."
    until pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" >/dev/null 2>&1; do
      sleep 1
    done
  fi

  mkdir -p "$MIGRATED_MARKER_DIR"
  for sql in $(ls -1 "$MIGRATIONS_DIR"/*.sql 2>/dev/null | sort); do
    base=$(basename "$sql")
    marker="$MIGRATED_MARKER_DIR/$base"
    if [ -f "$marker" ]; then
      echo "Skipping $base (already applied)"
      continue
    fi
    echo "Applying $base..."
    PGPASSWORD="$DB_PASSWORD" psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" -d "$DB_DATABASE" -f "$sql" && \
      touch "$marker" || echo "Failed to apply $base"
  done
else
  echo "No migrations directory found at $MIGRATIONS_DIR"
fi

# If a custom command was passed to the container (e.g. worker daemon), run it directly
if [ "$#" -gt 0 ]; then
  echo "Executing command: $@"
  exec "$@"
fi

# Start background workers (if worker script exists and WORKER_COUNT > 0).
WORKER_COUNT=${WORKER_COUNT:-0}
if [ "$WORKER_COUNT" -gt 0 ] && [ -f "$APP_DIR/workers/email_worker.php" ]; then
  echo "Starting $WORKER_COUNT email worker(s)..."
  mkdir -p "$APP_DIR/workers/logs"
  if [ "$(id -u)" -eq 0 ]; then
    chown -R "${HOST_UID:-www-data}:www-data" "$APP_DIR/workers/logs" >/dev/null 2>&1 || true
  fi
  i=1
  while [ $i -le $WORKER_COUNT ]; do
    logfile="$APP_DIR/workers/logs/email_worker.$i.log"
    # Start worker in background; it will keep running until container stops.
    php "$APP_DIR/workers/email_worker.php" >> "$logfile" 2>&1 &
    echo "Started email worker #$i (log: $logfile)"
    i=$((i+1))
  done
fi

echo "Starting PHP-FPM..."
exec php-fpm -F