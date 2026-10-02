-- ============================================================
-- migrations/001_users.sql
-- Run once:
--   docker exec -i postgres-18.3-alpine \
--     psql -U php_app_user -d php_app \
--     < php-app/migrations/001_users.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS users (
    id               SERIAL PRIMARY KEY,
    name             VARCHAR(120),
    email            VARCHAR(255) UNIQUE,
    password         VARCHAR(255),
    is_verified      BOOLEAN DEFAULT FALSE,
    verify_token     VARCHAR(64) UNIQUE,
    token_expires_at TIMESTAMP WITH TIME ZONE,
    created_at       TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at       TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_users_email ON users (email);
CREATE INDEX IF NOT EXISTS idx_users_verify_token ON users (verify_token);

