# Consumer check against flat-webapp

`bin/consumer-check` (in the 7.3 container) over the `php-7.3` branch of flat-webapp, using the
php-7.2 impact list (85 entries; the php-7.3 list has no entries of its own yet), 2026-11-20.

- 41 files scanned; 4 entries had hits (19 hits). All are in the application's own error
  traits, controllers and configuration: `TRACE` (MOD-127), `set_response_handler(` (MOD-165),
  the template design root (MOD-164) and `config::get(` (MOD-139).
- These are places to look at, not defects. They are the same set as the php-7.2 review of
  the application ([../../php-7.2/active/WEBAPP-REVIEW.md](../../php-7.2/active/WEBAPP-REVIEW.md)).
- Running it on the 7.3 environment: flat-webapp's serve script (in its docker/bin folder) starts the application on PHP 7.3.33
  against flat's `php-7.3` branch; `/view.php/home` and `/api.php/example` answer 200 and the
  server log has no PHP warning, notice or deprecation.
- The consumer check against the owner's closed-source application is still the owner's task.
