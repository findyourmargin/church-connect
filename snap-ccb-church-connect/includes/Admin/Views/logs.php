<?php
use SnapChurchConnect\CCB\Logging\Logger;

if (! defined('ABSPATH')) {
	exit;
}

$level = isset($_GET['level']) ? sanitize_key(wp_unslash($_GET['level'])) : 'all';
$entries = Logger::get_entries();
if ('all' !== $level) {
	$entries = array_filter($entries, static function ($entry) use ($level) {
		return isset($entry['level']) && $entry['level'] === $level;
	});
}
?>
<h2><?php echo esc_html__('Logs', 'snap-ccb-church-connect'); ?></h2>
<p>
	<?php foreach (array('all', 'info', 'warning', 'error') as $filter) : ?>
		<a href="<?php echo esc_url(add_query_arg(array('page' => 'snap-ccb-church-connect', 'tab' => 'logs', 'level' => $filter), admin_url('admin.php'))); ?>"><?php echo esc_html(ucfirst($filter)); ?></a>
	<?php endforeach; ?>
</p>
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
	<?php wp_nonce_field('snap_ccb_church_connect_clear_logs'); ?>
	<input type="hidden" name="action" value="snap_ccb_church_connect_clear_logs">
	<?php submit_button(__('Clear Logs', 'snap-ccb-church-connect'), 'delete', 'submit', false); ?>
</form>
<table class="widefat striped snap-ccb-logs">
	<thead><tr><th><?php echo esc_html__('Time', 'snap-ccb-church-connect'); ?></th><th><?php echo esc_html__('Level', 'snap-ccb-church-connect'); ?></th><th><?php echo esc_html__('Source', 'snap-ccb-church-connect'); ?></th><th><?php echo esc_html__('Message', 'snap-ccb-church-connect'); ?></th><th><?php echo esc_html__('Context', 'snap-ccb-church-connect'); ?></th></tr></thead>
	<tbody>
		<?php if (empty($entries)) : ?>
			<tr><td colspan="5"><?php echo esc_html__('No logs found.', 'snap-ccb-church-connect'); ?></td></tr>
		<?php endif; ?>
		<?php foreach ($entries as $entry) : ?>
			<tr>
				<td><?php echo esc_html(isset($entry['timestamp']) ? $entry['timestamp'] : ''); ?></td>
				<td><?php echo esc_html(isset($entry['level']) ? $entry['level'] : ''); ?></td>
				<td><?php echo esc_html(isset($entry['source']) ? $entry['source'] : ''); ?></td>
				<td><?php echo esc_html(isset($entry['message']) ? $entry['message'] : ''); ?></td>
				<td><code><?php echo esc_html(! empty($entry['context']) ? wp_json_encode($entry['context']) : ''); ?></code></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
