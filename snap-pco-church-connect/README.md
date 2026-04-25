# Snap! PCO Church Connect

Snap! PCO Church Connect syncs public Planning Center Calendar event instances into native WordPress content. Version 0.1 focuses on events only.

## Why `church_event` instead of `pco_event`

The plugin uses a shared Church Connect Schema so templates can move between church sites and future provider plugins. Bricks, Elementor, shortcodes, and app endpoints should use universal fields like `church_event_start` and `church_event_location`, not provider-specific keys.

## Requirements

- WordPress 6.0+
- PHP 7.4+
- Planning Center Personal Access Token credentials for Calendar API access

## Installation

1. Upload the plugin folder to `wp-content/plugins/snap-pco-church-connect`.
2. Activate **Snap! PCO Church Connect** in WordPress admin.
3. Open **Snap! PCO Connect** in the admin menu.

## Connect Planning Center

Go to **Snap! PCO Connect > Connection** and enter the Planning Center Client ID / App ID and Secret for a Personal Access Token / Basic Auth setup. The secret is stored server-side only and is not printed back to the admin screen.

You can also define credentials in `wp-config.php`:

```php
define('SNAP_PCO_CHURCH_CONNECT_CLIENT_ID', 'your-client-id');
define('SNAP_PCO_CHURCH_CONNECT_SECRET', 'your-secret');
```

OAuth is recommended for distributed and multi-church use. Version 0.1 keeps the class structure ready for OAuth but does not implement it yet.

Planning Center references:

- https://api.planningcenteronline.com/docs/overview/authentication
- https://api.planningcenteronline.com/docs/apps/calendar/versions/2022-07-07/vertices/event_instance
- https://api.planningcenteronline.com/docs/overview/rate-limiting

## Manual Sync

Use **Sync Now** from the Dashboard or Sync Settings tab. Manual syncs use nonce and capability checks, run server-side, and log created, updated, skipped, and failed counts.

## Automatic Sync

Enable automatic sync in **Sync Settings**. Supported frequencies are every 15 minutes, every 30 minutes, hourly, twice daily, and daily. WP-Cron events are scheduled only when enabled and are removed on deactivation.

## Display Events

Use the shortcode:

```text
[church_connect_events limit="6" layout="cards"]
```

Supported attributes:

- `limit`
- `layout` (`cards` or `list`)
- `featured`
- `campus`
- `category`
- `ministry`

Display settings control default date format, time format, button text, image visibility, and location visibility.

## REST Endpoints

Public read-only endpoints:

```text
/wp-json/church-connect/v1/events
/wp-json/church-connect/v1/events?limit=3&featured=true
/wp-json/church-connect/v1/events/{id}
```

Query args include `limit`, `page`, `featured`, `category`, `campus`, `ministry`, `after`, and `before`.

## Bricks Usage Notes

Bricks is not required. Bricks templates should target the `church_event` post type. Dynamic data can use registered custom fields such as `church_event_start`, `church_event_start_ts`, `church_event_location`, `church_event_image_url`, and `church_event_external_url`.

## Elementor Usage Notes

Elementor is not required. Elementor templates can target the `church_event` post type and use custom fields where supported. Use universal Church Connect field keys rather than Planning Center-specific keys.

## Security Notes

- Planning Center API calls happen server-side only.
- Credentials are not exposed to JavaScript, public REST responses, frontend output, or logs.
- Internal sync metadata and raw source data are not exposed through public REST endpoints.
- Admin actions use capabilities and nonces.

## Not Included in v0.1

- ACF, JetEngine, Bricks, or Elementor requirements
- Giving, donations, payments, card data, or bank data
- Mobile app code
- CCB support
- Groups, Forms, Registrations, or Sermons sync
- Full OAuth
- Browser-side Planning Center API calls
- Licensing or update server support

## Roadmap

- OAuth connection
- Webhooks
- Groups
- Registrations/signups
- Giving links
- App API enhancements
- CCB-compatible schema
- Monetization/licensing/private update server support
