# Snap! CCB Church Connect

Snap! CCB Church Connect syncs public Church Community Builder / Pushpay ChMS calendar events into native WordPress content. Version 0.1 focuses on events only.

## Why `church_event` instead of `ccb_event`

The plugin uses the shared Church Connect Schema also used by Snap! PCO Church Connect. Bricks and Elementor templates can target universal fields like `church_event_start` and `church_event_location` instead of provider-specific keys.

## Requirements

- WordPress 6.0+
- PHP 7.4+
- CCB / Pushpay ChMS API user with access to the needed services

## Installation

Upload the `snap-ccb-church-connect` folder or ZIP to WordPress, activate **Snap! CCB Church Connect**, then open **Snap! CCB Connect** in the admin menu.

## CCB API Credentials

Enter these in **Connection**:

- CCB Account / Subdomain, such as `yourchurch`, `yourchurch.ccbchurch.com`, or `https://yourchurch.ccbchurch.com`
- API Username
- API Password
- Optional HTTPS API Base URL override

Use CCB API credentials, not Pushpay API credentials. Pushpay API credentials are for Pushpay services and will not authenticate against the legacy CCB endpoint at `ccbchurch.com/api.php`.

The normalized API URL is `https://{account}.ccbchurch.com/api.php`.

Constants can override saved settings:

```php
define('SNAP_CCB_CHURCH_CONNECT_ACCOUNT', 'yourchurch');
define('SNAP_CCB_CHURCH_CONNECT_USERNAME', 'api-user');
define('SNAP_CCB_CHURCH_CONNECT_PASSWORD', 'api-password');
define('SNAP_CCB_CHURCH_CONNECT_API_BASE_URL', 'https://yourchurch.ccbchurch.com/api.php');
```

Required CCB services:

- `api_status`
- `public_calendar_listing`
- `event_profile`

Optional future services:

- `event_profiles`
- `campus_list`

Reference: https://designccb.s3.amazonaws.com/helpdesk/files/official_docs/api.html

## Manual Sync

Use **Sync Now** from the Dashboard or Sync Settings tab. The sync calls `public_calendar_listing` for today through the configured future window, then optionally calls `event_profile` for each unique event ID.

## Automatic Sync

Enable automatic sync in Sync Settings. Frequencies include every 15 minutes, every 30 minutes, hourly, twice daily, and daily. WP-Cron is scheduled only when enabled and unscheduled on deactivation.

## Date Range and Recurring Events

CCB public calendar listing returns event occurrences for a date range. Recurring events may share the same CCB event ID, so this plugin generates a unique occurrence key:

```text
ccb:{event_id}:{date}:{start_time}:{end_time}
```

That key is stored in `_church_connect_external_instance_id` to prevent duplicate posts while allowing recurring occurrences to remain separate events.

## Shortcode

```text
[church_connect_events limit="6" layout="cards"]
```

Attributes: `limit`, `layout`, `featured`, `campus`, `category`, and `ministry`.

## REST Endpoints

```text
/wp-json/church-connect/v1/events
/wp-json/church-connect/v1/events?limit=3&featured=true
/wp-json/church-connect/v1/events/{id}
```

The CCB plugin returns only events where `_church_connect_provider = ccb`.

## Bricks and Elementor

Bricks and Elementor are not required. Templates should target the `church_event` post type and use shared fields like `church_event_start`, `church_event_start_ts`, `church_event_location`, `church_event_image_url`, and `church_event_registration_url`.

## Security and Privacy

- CCB API calls happen server-side only.
- Credentials are not exposed to JavaScript, frontend output, public REST responses, or logs.
- Leader phone/email and private people/member data are not synced or exposed in v0.1.
- Raw source data is stored only in protected internal meta.

## Not Included in v0.1

- ACF, JetEngine, Bricks, or Elementor requirements
- Giving/donations or payment data
- Mobile app code
- Planning Center support
- Groups, Forms, People/member directory, Queues, or Sermons sync
- Pushpay Giving API integration
- Browser-side CCB API calls
- Licensing or private update server support

## Roadmap

- Groups
- Forms
- Pushpay giving links/embed
- Queues/request workflows
- App API enhancements
- Shared schema compatibility with Snap! PCO Church Connect
- Monetization/licensing/private update server support
