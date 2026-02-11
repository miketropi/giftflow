<?php
/**
 * Admin template: Donation meta box event history
 *
 * @package GiftFlow
 * @var array $events List of formatted event items (event_label, status, status_label, gateway, note, date).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $events ) ) {
	echo '<p class="giftflow-event-history-empty">' . esc_html__( 'No events yet.', 'giftflow' ) . '</p>';
	return;
}
?>
<ul class="giftflow-event-history-list">
	<?php foreach ( $events as $item ) : ?>
		<?php
		$status_slug = isset( $item['status'] ) ? sanitize_title( $item['status'] ) : '';
		$status_class = $status_slug ? ' giftflow-event-status--' . $status_slug : '';
		?>
		<li class="giftflow-event-history-item">
			<div class="giftflow-event-history-item__header">
				<span class="giftflow-event-label"><?php echo esc_html( $item['event_label'] ); ?></span>
				<span class="giftflow-event-status<?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $item['status_label'] ); ?></span>
			</div>
			<?php if ( ! empty( $item['gateway'] ) ) : ?>
				<div class="giftflow-event-gateway"><?php echo esc_html( $item['gateway'] ); ?></div>
			<?php endif; ?>
			<?php if ( ! empty( $item['note'] ) ) : ?>
				<p class="giftflow-event-note"><?php echo esc_html( $item['note'] ); ?></p>
			<?php endif; ?>
			<time class="giftflow-event-date" datetime="<?php echo esc_attr( $item['date'] ); ?>"><?php echo esc_html( $item['date'] ); ?></time>
		</li>
	<?php endforeach; ?>
</ul>
