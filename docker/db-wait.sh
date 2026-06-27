#!/bin/sh
# Shared database-wait helper, sourced by entrypoint.sh and laravel-bootstrap.sh.
# Defines log() and wait_for_database() — safe to source from any startup script.

# Provide a generic log() unless the sourcing script already defined one.
if ! command -v log >/dev/null 2>&1; then
    log() {
        echo "[db-wait] $*"
    }
fi

wait_for_database() {
    db_connection="${DB_CONNECTION:-mysql}"
    max_attempts=45
    attempt=0

    if [ -z "${DB_HOST:-}" ]; then
        log "DB_HOST not set — skipping database wait"
        return 0
    fi

    log "Waiting for database (${db_connection}) at ${DB_HOST}..."

    while [ "$attempt" -lt "$max_attempts" ]; do
        if [ "$db_connection" = "pgsql" ]; then
            if php -r "
                try {
                    new PDO(
                        'pgsql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '5432') . ';dbname=' . getenv('DB_DATABASE'),
                        getenv('DB_USERNAME'),
                        getenv('DB_PASSWORD'),
                        [PDO::ATTR_TIMEOUT => 3]
                    );
                    exit(0);
                } catch (Throwable \$e) {
                    exit(1);
                }
            " 2>/dev/null; then
                log "PostgreSQL is ready"
                return 0
            fi
        elif [ "$db_connection" = "mysql" ]; then
            if php -r "
                try {
                    new PDO(
                        'mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306'),
                        getenv('DB_USERNAME'),
                        getenv('DB_PASSWORD'),
                        [PDO::ATTR_TIMEOUT => 3]
                    );
                    exit(0);
                } catch (Throwable \$e) {
                    exit(1);
                }
            " 2>/dev/null; then
                log "MySQL is ready"
                return 0
            fi
        else
            log "Unknown DB_CONNECTION=${db_connection} — skipping wait"
            return 0
        fi

        attempt=$((attempt + 1))
        sleep 2
    done

    log "Database wait timed out after $((max_attempts * 2))s — continuing anyway"
    return 0
}
