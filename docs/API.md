# Data Access and Module Notes

Quill is a server-rendered PHP application and uses function-level data access patterns.

## Primary Modules
- `src/auth/auth.php`: authentication/login flow
- `src/viewer/*`: public pages
- `src/author/*`: author publishing workflow
- `src/admin/*`: admin management

## Shared Data Layer
- `includes/functions.php`
  - post management helpers
  - user management helpers
  - tag/image helpers
  - session/flash utilities

## Database
- MySQL schema file: `docs/schema.sql`
- connection bootstrap: `includes/config.php`

## Notes
For scaling, migrate helper functions into service/repository classes and expose HTTP API endpoints where needed.
