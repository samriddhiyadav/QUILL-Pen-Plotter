# Architecture Summary

Quill follows a role-oriented monolith architecture.

## Layers
1. Presentation: PHP-rendered pages in `index.php` and `src/*`.
2. Access Control: session-driven role checks.
3. Business/Data Logic: shared helper functions in `includes/functions.php`.
4. Persistence: MySQL accessed via PDO in `includes/config.php`.
5. Static and upload assets: `assets/`, `uploads/`.

## Tradeoffs
- Simple and fast to iterate for portfolio and small-team projects.
- Limited scalability due to function-centric shared module design.
- Good migration path toward service/repository architecture.
