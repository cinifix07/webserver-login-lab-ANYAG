# Simple Login System

A complete beginner-friendly login application built with standalone Apache,
PHP, and PostgreSQL.

## Included pages

- Login
- Registration
- Forgot password
- Reset password
- Protected dashboard
- Profile update
- Secure logout

## Security features

- Password hashing with Argon2id when available
- PDO prepared statements
- CSRF protection on every form
- Secure PHP session configuration
- Session ID regeneration after login
- Generic login and recovery responses
- Session-based login throttling
- Hashed, single-use password-reset tokens
- Output escaping to prevent stored XSS

## Requirements

- Apache 2.4+
- PHP 8.2+ with `pdo_pgsql`
- PostgreSQL 15+

## Installation

1. Create the database:

   ```sql
   CREATE DATABASE simple_login;
   ```

2. Run `database/schema.sql` in pgAdmin or `psql`.
3. Copy `.env.example` to `.env` and enter your PostgreSQL credentials.
4. Update `apache/simple-login.conf` with the correct project path.
5. Enable the Apache site and restart Apache.
6. Open the site, create an account, and sign in.

On Windows, set Apache's `DocumentRoot` directly to this project's `public`
folder. Make sure the PHP `pdo_pgsql` and `pgsql` extensions are enabled in
`php.ini`.

## Password recovery

This school-project version displays the reset link on screen when
`APP_ENV=local`. In production, connect the generated URL to a trusted email
provider and set `APP_ENV=production`.

## Folder structure

```text
apache/        Apache virtual-host example
database/      PostgreSQL schema
public/        Browser-accessible PHP pages and CSS
src/           Database, authentication, session, CSRF, and layout code
```
