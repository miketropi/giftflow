<?php
/**
 * Dashboard help & documentation template.
 *
 * @package GiftFlow
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$giftflow_doc_base = 'https://giftflow-doc.beplus-agency.cloud';
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$giftflow_doc_url  = trailingslashit( $giftflow_doc_base );

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$docs = array(
	array(
		'title'       => __( 'Introduction: platform overview & quick start', 'giftflow' ),
		'description' => __( 'Learn how GiftFlow fits together—campaigns, donations, and donor records—and what you need to go live (WordPress, PHP, SSL). Includes payment options (Stripe, PayPal, bank transfer), optional reCAPTCHA and Maps, and a step-by-step quick start.', 'giftflow' ),
		'url'         => $giftflow_doc_url,
		'tag'         => __( 'Essentials', 'giftflow' ),
		'icon'        => 'book',
	),
	array(
		'title'       => __( 'Creating your first campaign (walkthrough)', 'giftflow' ),
		'description' => __( 'From a blank campaign to a published page: set goals and story, add images or galleries, configure preset amounts and donation types, choose payment methods, and embed the donation experience on your site.', 'giftflow' ),
		'url'         => $giftflow_doc_url . 'usage/guide-first-campaign',
		'tag'         => __( 'Tutorial', 'giftflow' ),
		'icon'        => 'flag',
	),
	array(
		'title'       => __( 'Blocks, shortcodes & block templates', 'giftflow' ),
		'description' => __( 'Use the block editor or shortcodes to add donation buttons, campaign content, progress bars, galleries, donor account areas, and sharing. Reference covers attributes, template overrides, and when to use each block or shortcode.', 'giftflow' ),
		'url'         => $giftflow_doc_url . 'blocks-and-shortcodes',
		'tag'         => __( 'Frontend', 'giftflow' ),
		'icon'        => 'layout',
	),
	array(
		'title'       => __( 'Payment hooks, filters & gateway behavior', 'giftflow' ),
		'description' => __( 'Extend checkout and reconciliation: register custom gateways, react to payment success or failure, adjust form data, and work with webhooks. Covers patterns for one-time and recurring flows where applicable.', 'giftflow' ),
		'url'         => $giftflow_doc_url . 'hooks-and-filters/payment-hooks-filters',
		'tag'         => __( 'Developer', 'giftflow' ),
		'icon'        => 'code',
	),
);

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$dev_links = array(
	array(
		'label' => __( 'Helper functions', 'giftflow' ),
		'url'   => $giftflow_doc_url . 'helper-functions-reference',
	),
	array(
		'label' => __( 'Template overrides', 'giftflow' ),
		'url'   => $giftflow_doc_url . 'hooks-and-filters/developer-guide-template-override',
	),
	array(
		'label' => __( 'Metabox & settings', 'giftflow' ),
		'url'   => $giftflow_doc_url . 'hooks-and-filters/developer-guide-metabox-settings',
	),
);

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$contact_support_url = 'https://giftflow.beplus-agency.cloud/contact';

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$doc_host = wp_parse_url( $giftflow_doc_base, PHP_URL_HOST );
?>
<div class="giftflow-dashboard-helps">
	<div class="giftflow-dashboard-helps__shell">

		<header class="giftflow-dashboard-helps__masthead" aria-labelledby="giftflow-helps-heading">
			<div class="giftflow-dashboard-helps__masthead-main">
				<div class="giftflow-dashboard-helps__masthead-badge">
					<span class="giftflow-dashboard-helps__masthead-badge-dot" aria-hidden="true"></span>
					<?php esc_html_e( 'GiftFlow', 'giftflow' ); ?>
				</div>
				<h2 id="giftflow-helps-heading" class="giftflow-dashboard-helps__masthead-title">
					<?php esc_html_e( 'Help center', 'giftflow' ); ?>
				</h2>
				<p class="giftflow-dashboard-helps__masthead-lead">
					<?php esc_html_e( 'Everything you need to run campaigns, accept donations, and extend GiftFlow — in one documentation site.', 'giftflow' ); ?>
				</p>
				<div class="giftflow-dashboard-helps__masthead-actions">
					<a
						class="button button-primary"
						href="<?php echo esc_url( $giftflow_doc_url ); ?>"
						target="_blank"
						rel="noopener noreferrer"
					>
						<span class="dashicons dashicons-external" aria-hidden="true"></span>
						<?php esc_html_e( 'Open documentation', 'giftflow' ); ?>
					</a>
					<a
						class="button"
						href="<?php echo esc_url( $contact_support_url ); ?>"
						target="_blank"
						rel="noopener noreferrer"
					>
						<span class="dashicons dashicons-email" aria-hidden="true"></span>
						<?php esc_html_e( 'Contact support', 'giftflow' ); ?>
					</a>
				</div>
				<p class="giftflow-dashboard-helps__masthead-meta gfw-monofont">
					<span class="giftflow-dashboard-helps__masthead-meta-label"><?php esc_html_e( 'Docs', 'giftflow' ); ?></span>
					<a href="<?php echo esc_url( $giftflow_doc_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $doc_host ); ?></a>
				</p>
			</div>
			<div class="giftflow-dashboard-helps__masthead-deco" aria-hidden="true">
				<div class="giftflow-dashboard-helps__masthead-deco-grid"></div>
				<div class="giftflow-dashboard-helps__masthead-deco-glow"></div>
			</div>
		</header>

		<nav class="giftflow-dashboard-helps__jump" aria-label="<?php esc_attr_e( 'Quick links to documentation', 'giftflow' ); ?>">
			<span class="giftflow-dashboard-helps__jump-label"><?php esc_html_e( 'Jump to', 'giftflow' ); ?></span>
			<ul class="giftflow-dashboard-helps__jump-list">
				<?php foreach ( $docs as $j ) : ?>
					<li>
						<a class="giftflow-dashboard-helps__jump-link" href="<?php echo esc_url( $j['url'] ); ?>" target="_blank" rel="noopener noreferrer">
							<?php echo esc_html( $j['title'] ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>

		<div class="giftflow-dashboard-helps__body">
			<div class="giftflow-dashboard-helps__primary">

				<a
					class="giftflow-dashboard-helps__featured"
					href="<?php echo esc_url( $giftflow_doc_url ); ?>"
					target="_blank"
					rel="noopener noreferrer"
				>
					<span class="giftflow-dashboard-helps__featured-icon" aria-hidden="true">
						<span class="dashicons dashicons-book-alt"></span>
					</span>
					<span class="giftflow-dashboard-helps__featured-text">
						<span class="giftflow-dashboard-helps__featured-kicker"><?php esc_html_e( 'Full knowledge base', 'giftflow' ); ?></span>
						<span class="giftflow-dashboard-helps__featured-title"><?php esc_html_e( 'Browse all guides & API reference', 'giftflow' ); ?></span>
						<span class="giftflow-dashboard-helps__featured-desc">
							<?php esc_html_e( 'Usage, sandbox setup, blocks, hooks, examples, and helper functions — searchable on the docs site.', 'giftflow' ); ?>
						</span>
					</span>
					<span class="giftflow-dashboard-helps__featured-arrow" aria-hidden="true">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 12h14m-4-4 4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</span>
				</a>

				<div class="giftflow-dashboard-helps__grid" role="list">
					<?php foreach ( $docs as $doc ) : ?>
						<?php
						$icon_map = array(
							'book'   => 'dashicons-book-alt',
							'flag'   => 'dashicons-flag',
							'layout' => 'dashicons-layout',
							'code'   => 'dashicons-editor-code',
						);
						$icon_slug = isset( $doc['icon'] ) && isset( $icon_map[ $doc['icon'] ] ) ? $icon_map[ $doc['icon'] ] : 'dashicons-media-document';
						?>
						<a
							class="giftflow-dashboard-helps__tile"
							href="<?php echo esc_url( $doc['url'] ); ?>"
							target="_blank"
							rel="noopener noreferrer"
							role="listitem"
						>
							<?php if ( ! empty( $doc['tag'] ) ) : ?>
								<span class="giftflow-dashboard-helps__tile-tag"><?php echo esc_html( $doc['tag'] ); ?></span>
							<?php endif; ?>
							<span class="giftflow-dashboard-helps__tile-icon" aria-hidden="true">
								<span class="dashicons <?php echo esc_attr( $icon_slug ); ?>"></span>
							</span>
							<span class="giftflow-dashboard-helps__tile-body">
								<span class="giftflow-dashboard-helps__tile-title"><?php echo esc_html( $doc['title'] ); ?></span>
								<?php if ( ! empty( $doc['description'] ) ) : ?>
									<span class="giftflow-dashboard-helps__tile-desc"><?php echo esc_html( $doc['description'] ); ?></span>
								<?php endif; ?>
								<span class="giftflow-dashboard-helps__tile-cta">
									<?php esc_html_e( 'Open', 'giftflow' ); ?>
									<svg width="12" height="12" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="m6 12 4-4-4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
								</span>
							</span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>

			<aside class="giftflow-dashboard-helps__rail" aria-labelledby="giftflow-helps-dev-heading">
				<div class="giftflow-dashboard-helps__rail-card giftflow-dashboard-helps__rail-card--dev">
					<h3 id="giftflow-helps-dev-heading" class="giftflow-dashboard-helps__rail-title">
						<span class="giftflow-dashboard-helps__rail-title-icon dashicons dashicons-editor-code" aria-hidden="true"></span>
						<?php esc_html_e( 'Developers', 'giftflow' ); ?>
					</h3>
					<p class="giftflow-dashboard-helps__rail-text">
						<?php esc_html_e( 'Hooks, theme overrides, and admin extensions.', 'giftflow' ); ?>
					</p>
					<ul class="giftflow-dashboard-helps__rail-links">
						<?php
						// phpcs:ignore WoWordPress.WP.GlobalVariablesOverride.Prohibited
						foreach ( $dev_links as $_link ) :
							?>
							<li>
								<a href="<?php echo esc_url( $_link['url'] ); ?>" target="_blank" rel="noopener noreferrer">
									<?php echo esc_html( $_link['label'] ); ?>
									<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>

				<div class="giftflow-dashboard-helps__rail-card giftflow-dashboard-helps__rail-card--support">
					<h3 class="giftflow-dashboard-helps__rail-title">
						<span class="giftflow-dashboard-helps__rail-title-icon dashicons dashicons-format-chat" aria-hidden="true"></span>
						<?php esc_html_e( 'Need help?', 'giftflow' ); ?>
					</h3>
					<p class="giftflow-dashboard-helps__rail-text">
						<?php esc_html_e( 'Setup, bugs, or custom work — talk to our team.', 'giftflow' ); ?>
					</p>
					<a
						class="button button-primary giftflow-dashboard-helps__btn--block"
						href="<?php echo esc_url( $contact_support_url ); ?>"
						target="_blank"
						rel="noopener noreferrer"
					>
						<?php esc_html_e( 'Contact support', 'giftflow' ); ?>
					</a>
				</div>
			</aside>
		</div>
	</div>
</div>
