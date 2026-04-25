# Changelog

## 2026-04-25

- Created the Snap! CCB Church Connect v0.1 WordPress plugin package.
- Added shared `church_event` schema support, CCB connection settings, XML API client, public calendar event sync, generated recurring occurrence keys, WP-Cron scheduler, logs, shortcode, REST endpoints, admin screens, assets, README, and conservative uninstall behavior.
- Released 0.1.1 admin hardening: avoid unnecessary option rewrites on page load, guard admin view includes, and make dashboard queries explicit.
- Released 0.1.2 diagnostics: log safe public calendar response shape, candidate paths, and item keys without storing credentials or leader/contact values.
- Released 0.1.3 diagnostics: log safe response-node keys and attributes to distinguish empty CCB responses from unmapped event structures.
- Released 0.1.4: treat CCB application-level `<errors>` responses as sync failures, log safe error text, clear option cache after status updates, and make dashboard status resilient to stale options.
