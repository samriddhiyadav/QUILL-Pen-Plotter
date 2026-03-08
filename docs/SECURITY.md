# Security Notes

## Current Controls
- Password hashing via `password_hash`
- Parameterized SQL queries via PDO prepared statements
- Role checks for author/admin flows

## Required Before Public Production
- Add CSRF tokens for all POST/PUT/DELETE style actions
- Add strict server-side validation for all user input
- Enforce file upload content-type + extension allowlist
- Add rate limiting on login endpoint
- Add account lockout/brute-force detection
- Add audit logs for admin actions

## Secret Management
Never commit `.env`; use `.env.example` template and set real secrets per environment.
