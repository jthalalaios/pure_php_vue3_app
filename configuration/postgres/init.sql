-- init.sql — runs once on first container startup.
-- The "pure_php_app" database and "php_app_user" role are already created

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

GRANT USAGE ON SCHEMA public TO "php-app-user";

GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO "php-app-user";

GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO "php-app-user";

ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO "php-app-user";

ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO "php-app-user";