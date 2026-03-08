# Deployment Guide

## Local (XAMPP)
1. Install XAMPP and start Apache + MySQL.
2. Place project in `htdocs/quill`.
3. Copy `.env.example` to `.env` and configure credentials.
4. Create DB and import `docs/schema.sql`.
5. Visit `http://localhost/quill`.

## Production (LAMP/VPS)
1. Provision Ubuntu server with Apache, PHP 8+, MySQL.
2. Copy project to `/var/www/quill`.
3. Set Apache virtual host document root to project path.
4. Set environment values in `.env` (or server env vars).
5. Use least-privilege DB user in production.
6. Set file permissions so web server can write only to `uploads/`.
7. Enable HTTPS (Let's Encrypt).

## Hardening Checklist
- `APP_ENV=production`
- Disable PHP display_errors in production
- Rotate DB credentials
- Restrict upload file types and size
- Enable daily DB backups
