# Kingdoms of Steel & Shadows — Minimal MVP

A lightweight, Torn City–style medieval text RPG starter built with **PHP + MySQL**.
This is a teaching/starter project: login, register, dashboard, train, crime, rest, jail, simple PvE duel stub.

## Quick Start

1. Create database and tables:
   ```sql
   SOURCE ./init.sql;
   ```

2. Copy `includes/config.sample.php` to `includes/config.php` and set your DB credentials.

3. Serve the `/public` folder via PHP's built-in server:
   ```bash
   php -S localhost:8080 -t public
   ```

4. Visit http://localhost:8080

## Notes
- Passwords are hashed with `password_hash` (bcrypt).
- Simple CSRF token is implemented.
- Game loop uses energy/nerve. Crimes can send you to jail (cooldown timer).
- This is intentionally compact and not production-hardened. Add rate limits, email verify, better RBAC, audit logs, etc.
