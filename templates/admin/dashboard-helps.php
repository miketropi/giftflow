<?php
/**
 * Template for dashboard helps
 *
 * @package GiftFlow
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$docs = array(
	array(
		'title' => __( 'GiftFlow operating model', 'giftflow' ),
		'description' => __( 'Learn how GiftFlow works and understand the core operating model of the donation platform.', 'giftflow' ),
		'url' => 'https://giftflow.beplus-agency.cloud/blog/giftflow-operating-model',
	),
	array(
		'title' => __( 'How to start your first campaign', 'giftflow' ),
		'description' => __( 'Step-by-step guide to create and launch your first donation campaign successfully.', 'giftflow' ),
		'url' => 'https://giftflow.beplus-agency.cloud/blog/how-to-start-your-first-campaign',
		'tag' => __( 'Useful', 'giftflow' ),
	),
	array(
		'title' => __( 'How to configure PayPal payment gateway', 'giftflow' ),
		'description' => __( 'Complete setup instructions for integrating PayPal as a payment method in your campaigns.', 'giftflow' ),
		'url' => 'https://giftflow.beplus-agency.cloud/blog/how-to-configure-paypal-payment-gateway',
		'tag' => __( 'Useful', 'giftflow' ),
	),
	array(
		'title' => __( 'How to configure Stripe payment gateway', 'giftflow' ),
		'description' => __( 'Detailed guide to set up Stripe payment gateway for secure online donations.', 'giftflow' ),
		'url' => 'https://giftflow.beplus-agency.cloud/blog/how-to-configure-stripe-payment-gateway',
		'tag' => __( 'Useful', 'giftflow' ),
	),
);

$contact_support_url = 'https://giftflow.beplus-agency.cloud/contact';
?>
<div class="giftflow-dashboard-helps">
	<div class="giftflow-dashboard-helps__header">
		<h2 class="giftflow-dashboard-helps__title">
			<?php esc_html_e( 'Help & Documentation', 'giftflow' ); ?>
		</h2>
		<p class="giftflow-dashboard-helps__description">
			<?php esc_html_e( 'Get started with GiftFlow and learn how to make the most of your donation campaigns.', 'giftflow' ); ?>
		</p>
	</div>

	<div class="giftflow-dashboard-helps__content">
		<!-- Documentation Section -->
		<div class="giftflow-dashboard-helps__section">
			<h3 class="giftflow-dashboard-helps__section-title">
				<?php esc_html_e( 'Documentation', 'giftflow' ); ?>
			</h3>
			<p class="giftflow-dashboard-helps__section-description">
				<?php esc_html_e( 'Learn how to use GiftFlow with our comprehensive guides.', 'giftflow' ); ?>
			</p>
			
			<div class="giftflow-dashboard-helps__docs-list">
				<?php foreach ( $docs as $doc ) : ?>
					<a 
						href="<?php echo esc_url( $doc['url'] ); ?>" 
						target="_blank" 
						rel="noopener noreferrer"
						class="giftflow-dashboard-helps__doc-item"
					>
						<div class="giftflow-dashboard-helps__doc-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M9 12H15M9 16H15M17 21H7C5.89543 21 5 20.1046 5 19V5C5 3.89543 5.89543 3 7 3H12.5858C12.851 3 13.1054 3.10536 13.2929 3.29289L18.7071 8.70711C18.8946 8.89464 19 9.149 19 9.41421V19C19 20.1046 18.1046 21 17 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>
						<div class="giftflow-dashboard-helps__doc-content">
							<div class="giftflow-dashboard-helps__doc-header">
								<h4 class="giftflow-dashboard-helps__doc-title">
									<?php echo esc_html( $doc['title'] ); ?>
								</h4>
								<?php if ( ! empty( $doc['tag'] ) ) : ?>
									<span class="giftflow-dashboard-helps__doc-tag gfw-monofont">
										<?php echo esc_html( $doc['tag'] ); ?>
									</span>
								<?php endif; ?>
							</div>
							<?php if ( ! empty( $doc['description'] ) ) : ?>
								<p class="giftflow-dashboard-helps__doc-description">
									<?php echo esc_html( $doc['description'] ); ?>
								</p>
							<?php endif; ?>
							<span class="giftflow-dashboard-helps__doc-link">
								<?php esc_html_e( 'Read more', 'giftflow' ); ?>
								<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M6 12L10 8L6 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</span>
						</div>
					</a>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- Document for Dev Section -->
		<div class="giftflow-dashboard-helps__section giftflow-dashboard-helps__section--coming-soon">
			<h3 class="giftflow-dashboard-helps__section-title">
				<?php esc_html_e( 'Document for Dev', 'giftflow' ); ?>
			</h3>
			<p class="giftflow-dashboard-helps__section-description">
				<?php esc_html_e( 'Technical documentation for developers, including API references, hooks, and customization guides.', 'giftflow' ); ?>
			</p>
			
			<div class="giftflow-dashboard-helps__coming-soon">
				<div class="giftflow-dashboard-helps__coming-soon-icon">
					<svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M24 8C15.1634 8 8 15.1634 8 24C8 32.8366 15.1634 40 24 40C32.8366 40 40 32.8366 40 24C40 15.1634 32.8366 8 24 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M24 16V24L28 28" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</div>
				<h4 class="giftflow-dashboard-helps__coming-soon-title">
					<?php esc_html_e( 'Coming Soon', 'giftflow' ); ?>
				</h4>
				<p class="giftflow-dashboard-helps__coming-soon-description">
					<?php esc_html_e( 'Developer documentation is currently being prepared and will be available soon.', 'giftflow' ); ?>
				</p>
			</div>
		</div>

		<!-- Contact Support Section -->
		<div class="giftflow-dashboard-helps__section giftflow-dashboard-helps__section--support">
			<h3 class="giftflow-dashboard-helps__section-title">
				<?php esc_html_e( 'Need More Help?', 'giftflow' ); ?>
			</h3>
			<p class="giftflow-dashboard-helps__section-description">
				<?php esc_html_e( 'Can\'t find what you\'re looking for? Our support team is here to help.', 'giftflow' ); ?>
			</p>
			
			<a 
				href="<?php echo esc_url( $contact_support_url ); ?>" 
				target="_blank" 
				rel="noopener noreferrer"
				class="giftflow-dashboard-helps__support-button button button-primary"
			>
				<?php esc_html_e( 'Contact Support', 'giftflow' ); ?>
			</a>
		</div>
	</div>
</div>