# Changelog

## 2026-04-25

- Created the Snap! PCO Church Connect v0.1 WordPress plugin scaffold.
- Added `church_event` content type, event taxonomies, shared Church Connect meta fields, admin settings, Planning Center API client, event sync service, WP-Cron scheduler, logger, REST endpoints, shortcode, assets, uninstall guard, and README documentation.
- Added an upload-ready `dist/snap-pco-church-connect` package and ZIP artifact for WordPress installation.
- Created the Snap! CCB Church Connect v0.1 WordPress plugin package and upload ZIP with CCB API connection settings, XML API client, public calendar sync, recurring occurrence keys, REST endpoints, shortcode, admin screens, logs, and shared Church Connect Schema support.
- Restructured the repository so Snap! PCO Church Connect and Snap! CCB Church Connect live in separate sibling plugin folders.
- Released Snap! CCB Church Connect 0.1.1 with admin-page hardening after a staging site returned a 503 on the plugin menu page.
- Released Snap! CCB Church Connect 0.1.2 with safe calendar-listing diagnostics to inspect CCB XML response shape when sync finds zero events.
- Released Snap! CCB Church Connect 0.1.3 with response-node diagnostics for empty or differently shaped CCB public calendar responses.
- Released Snap! CCB Church Connect 0.1.4 to surface CCB application-level errors and make Dashboard status resilient when host object caching serves stale options.
- Released Snap! CCB Church Connect 0.1.5 to clarify that legacy CCB API credentials are required and Pushpay API credentials will not authenticate against ccbchurch.com/api.php.
