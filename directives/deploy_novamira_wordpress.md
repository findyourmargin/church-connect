# Deploy to Novamira WordPress

Use this when updating the Novamira WordPress app at:

- Site: `https://wordpress-1564285-6376475.cloudwaysapps.com`
- Server: `144.202.14.99`
- SFTP user: `churchconnect`
- WordPress root: `/public_html`
- Plugins path: `/public_html/wp-content/plugins`

Do not commit or write passwords/API passwords into the repo. If a password is needed, pass it through an environment variable for the single command/session only.

## Current Plugin Deploy Flow

To deploy the latest CCB plugin source:

```bash
SSHPASS='PASSWORD_FROM_USER' sshpass -e sftp \
  -o StrictHostKeyChecking=no \
  -o UserKnownHostsFile=/tmp/churchconnect_known_hosts \
  -P 22 churchconnect@144.202.14.99 <<'SFTP'
cd /public_html/wp-content/plugins/snap-ccb-church-connect
put -r snap-ccb-church-connect/* .
SFTP
```

SSH shell access is disabled for this Cloudways user, so `rsync` over SSH will fail. Use SFTP recursive upload. If a plugin update removes files, manually remove obsolete remote files with SFTP before/after upload.

## Verification

After upload, verify over WordPress REST using the application password supplied by the user at runtime:

```bash
WP_USER='WORDPRESS_USERNAME' WP_PASS='WORDPRESS_APPLICATION_PASSWORD' node - <<'NODE'
const base = 'https://wordpress-1564285-6376475.cloudwaysapps.com/wp-json';
const auth = Buffer.from(`${process.env.WP_USER}:${process.env.WP_PASS}`).toString('base64');
const headers = { Authorization: `Basic ${auth}`, Accept: 'application/json' };
const plugins = await (await fetch(base + '/wp/v2/plugins?context=edit&per_page=100', {headers})).json();
const snap = plugins.find(p => p.plugin === 'snap-ccb-church-connect/snap-ccb-church-connect');
const events = await (await fetch(base + '/church-connect/v1/events?limit=1', {headers})).json();
console.log(JSON.stringify({ plugin: snap && { name: snap.name, status: snap.status, version: snap.version }, events }, null, 2));
NODE
```

Expected:

- `Snap! CCB Church Connect` is active.
- Version matches the local plugin header.
- `/church-connect/v1/events` returns synced events.

## Notes

- The WordPress MCP/REST endpoint is reachable, and the provided WordPress application password authenticates as an administrator.
- Core WordPress REST can inspect plugins and activate/deactivate them, but it does not upload arbitrary plugin ZIP updates.
- Use SFTP for plugin file updates unless a proper MCP file/plugin upload tool is available in the session.
