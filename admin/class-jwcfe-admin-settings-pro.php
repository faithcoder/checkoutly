<?php
/**
 * Premium Features tab page for the plugin.
 *
 * @link       https://jcodex.com
 * @since      2.6.0
 *
 * @package    woo-checkout-regsiter-field-editor
 * @subpackage woo-checkout-regsiter-field-editor/admin
 */

if ( ! defined( 'WPINC' ) ) { die; }

if ( ! class_exists( 'JWCFE_Admin_Settings_Pro' ) ) :

class JWCFE_Admin_Settings_Pro {

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Render the full Premium Features page.
	 */
	public function render_page() {
		$url = 'https://jcodex.com/plugins/woocommerce-custom-checkout-field-editor/';
		$demo_url = 'https://jcodex.com/plugins/woocommerce-custom-checkout-field-editor/';
		$assets = JWCFE_URL . 'admin/assets/';
		?>

		<div class="jwcfe-nice-box">

			<!-- ══════════════════════════════════════════
			     TOP BANNER — upgrade CTA
			     ══════════════════════════════════════════ -->
			<div class="jwcfe-ad-banner">
				<div class="jwcfe-ad-content" style="width:100%;">
					<div class="jwcfe-ad-content-container">
						<div class="jwcfe-ad-content-desc">
							<p><?php esc_html_e( 'Take full control of your WooCommerce checkout with Register Field Editor Pro — add custom fields, conditional rules, price fields, and a lot more.', 'jwcfe' ); ?></p>
						</div>
						<div class="jwcfe-upgrade-pro-btn-div">
							<a class="jwcfe-btn-upgrade-pro"
							   href="<?php echo esc_url( $url ); ?>"
							   target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'Upgrade to Pro', 'jwcfe' ); ?>
							</a>
						</div>
					</div>
					<div class="jwcfe-ad-terms">
						<div class="jwcfe-ad-guarantee">
							<img src="<?php echo esc_url( $assets . 'guarantee.svg' ); ?>" alt="<?php esc_attr_e( 'Money Back', 'jwcfe' ); ?>">
						</div>
						<p class="jwcfe-ad-term-head">
							<?php esc_html_e( '30 DAYS RISK-FREE MONEY BACK GUARANTEE', 'jwcfe' ); ?>
							<span class="jwcfe-ad-term-desc"><?php esc_html_e( '100% refund if you are not satisfied', 'jwcfe' ); ?></span>
						</p>
					</div>
				</div>
			</div><!-- /.jwcfe-ad-banner -->

			<!-- ══════════════════════════════════════════
			     MAIN CONTENT WRAPPER
			     ══════════════════════════════════════════ -->
			<div class="jwcfe-wrapper-main">

				<!-- Hero / try demo -->
				<div class="jwcfe-try-demo">
					<h3 class="jwcfe-trydemo-heading">
						<?php esc_html_e( 'Everything You Get With Checkout Field Editor Pro', 'jwcfe' ); ?>
					</h3>
					<p class="jwcfe-try-demo-desc">
						<?php esc_html_e( 'Packed with powerful features to help you build a clean and professional checkout page. Add custom fields, conditional logic, account sync, and a whole lot more. Go Pro and transform your checkout experience.', 'jwcfe' ); ?>
					</p>
					<div class="jwcfe-pro-btn">
						<a class="jwcfe-btn-get-pro"
						   href="<?php echo esc_url( $url ); ?>"
						   target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'Get Pro', 'jwcfe' ); ?>
						</a>
						<a class="jwcfe-btn-try-demo"
						   href="<?php echo esc_url( $demo_url ); ?>"
						   target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'View Demo', 'jwcfe' ); ?>
						</a>
					</div>
				</div><!-- /.jwcfe-try-demo -->

				<!-- ── Key Features ── -->
				<section class="jwcfe-cfe-key-feature">
					<h3 class="jwcfe-feature-head">
						<?php esc_html_e( 'Key Features of WooCommerce Checkout Field Editor Pro', 'jwcfe' ); ?>
					</h3>
					<p class="jwcfe-feature-desc">
						<?php esc_html_e( 'Loaded with advanced tools to craft the perfect checkout page for your store. Unlock these premium features and take your checkout experience to the next level.', 'jwcfe' ); ?>
					</p>

					<div class="jwcfe-cfe-feature-list-ul">
						<ul class="jwcfe-cfe-feature-list">
							<li><?php esc_html_e( '18+ custom checkout field types', 'jwcfe' ); ?></li>
							<li><?php esc_html_e( 'My Account page field integration', 'jwcfe' ); ?></li>
							<li><?php esc_html_e( 'Sync My Account fields with checkout', 'jwcfe' ); ?></li>
							<li><?php esc_html_e( 'Display fields conditionally', 'jwcfe' ); ?></li>
							<li><?php esc_html_e( 'Display sections conditionally', 'jwcfe' ); ?></li>
							<li><?php esc_html_e( 'Price fields with multiple price types', 'jwcfe' ); ?></li>
							<li><?php esc_html_e( 'Custom validations for fields', 'jwcfe' ); ?></li>
							<li><?php esc_html_e( 'Display fields based on shipping & payment methods', 'jwcfe' ); ?></li>
							<li><?php esc_html_e( 'Show fields in emails and order detail pages', 'jwcfe' ); ?></li>
							<li><?php esc_html_e( 'WooCommerce Block checkout support', 'jwcfe' ); ?></li>
							<li><?php esc_html_e( 'Customise, disable, or delete default WooCommerce fields', 'jwcfe' ); ?></li>
							<li><?php esc_html_e( 'Rearrange all fields and sections', 'jwcfe' ); ?></li>
							<li><?php esc_html_e( 'Create custom CSS classes for field styling', 'jwcfe' ); ?></li>
							<li><?php esc_html_e( 'Developer-friendly with custom hooks', 'jwcfe' ); ?></li>
							<li><?php esc_html_e( 'WPML compatibility', 'jwcfe' ); ?></li>
							<li><?php esc_html_e( 'Priority support from our dedicated team', 'jwcfe' ); ?></li>
						</ul>
					</div>

					<!-- Rocket CTA inside features -->
					<div class="jwcfe-get-pro">
						<div class="jwcfe-get-pro-img">
							<img src="<?php echo esc_url( $assets . 'promo-banner.png' ); ?>" alt="<?php esc_attr_e( 'Pro', 'jwcfe' ); ?>">
						</div>
						<div class="jwcfe-wrapper-get-pro">
							<div class="jwcfe-get-pro-desc">
								<p class="jwcfe-get-pro-desc-head">
									<?php esc_html_e( 'Switch to Pro and be a part of our limitless features', 'jwcfe' ); ?>
									<span class="jwcfe-get-pro-desc-content">
										<?php esc_html_e( 'Switch to a world of seamless checkout with an ocean of possibilities to customise.', 'jwcfe' ); ?>
									</span>
								</p>
							</div>
							<div class="jwcfe-get-pro-btn">
								<a class="jwcfe-btn-upgrade-pro"
								   href="<?php echo esc_url( $url ); ?>"
								   target="_blank" rel="noopener noreferrer">
									<?php esc_html_e( 'Upgrade to Pro', 'jwcfe' ); ?>
								</a>
							</div>
						</div>
					</div>
				</section><!-- /.jwcfe-cfe-key-feature -->

				<!-- ── Users & Support bar ── -->
				<div class="jwcfe-star-support">
					<div class="jwcfe-user-star">
						<p class="jwcfe-user-star-desc"><?php esc_html_e( '2,000+ Happy Users', 'jwcfe' ); ?></p>
						<div class="jwcfe-user-star-img">
							<img src="<?php echo esc_url( $assets . 'star.svg' ); ?>" alt="<?php esc_attr_e( 'JCodex', 'jwcfe' ); ?>">
						</div>
					</div>
					<div class="jwcfe-pro-support">
						<div class="jwcfe-pro-support-img">
							<img src="<?php echo esc_url( $assets . 'support.svg' ); ?>" alt="<?php esc_attr_e( 'Support', 'jwcfe' ); ?>">
						</div>
						<p class="jwcfe-pro-support-desc">
							<?php esc_html_e( 'Enjoy the ', 'jwcfe' ); ?>
							<em><?php esc_html_e( 'Premium Support', 'jwcfe' ); ?></em>
							<?php esc_html_e( ' experience with our dedicated support team.', 'jwcfe' ); ?>
						</p>
					</div>
				</div>

				<!-- ── Available Field Types ── -->
				<section class="jwcfe-field-types">
					<h3 class="jwcfe-field-types-head"><?php esc_html_e( 'Available Field Types', 'jwcfe' ); ?></h3>
					<p class="jwcfe-field-types-desc"><?php esc_html_e( 'Following are the custom field types available in the Pro version of the Checkout Field Editor plugin.', 'jwcfe' ); ?></p>
					<div class="jwcfe-cfe-field-type-img">
						<div class="jwcfe-fields">
							<ul class="jwcfe-cfe-field-list">
								<li><?php esc_html_e( 'Text', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'Hidden', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'Password', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'Telephone', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'Email', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'Number', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'Textarea', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'Select', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'Multiselect', 'jwcfe' ); ?></li>
								<li>
									<?php esc_html_e( 'Date Picker', 'jwcfe' ); ?>
									<span class="jwcfe-crown"><img src="<?php echo esc_url( $assets . 'crown.svg' ); ?>" alt="pro"></span>
								</li>
								<li><?php esc_html_e( 'Radio', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'Checkbox', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'Checkbox Group', 'jwcfe' ); ?></li>
								<li>
									<?php esc_html_e( 'Time Picker', 'jwcfe' ); ?>
									<span class="jwcfe-crown"><img src="<?php echo esc_url( $assets . 'crown.svg' ); ?>" alt="pro"></span>
								</li>
								<li>
									<?php esc_html_e( 'File Upload', 'jwcfe' ); ?>
									<span class="jwcfe-new-rec"><p><?php esc_html_e( 'NEW', 'jwcfe' ); ?></p></span>
									<span class="jwcfe-crown"><img src="<?php echo esc_url( $assets . 'crown.svg' ); ?>" alt="pro"></span>
								</li>
								<li><?php esc_html_e( 'Heading', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'Paragraph', 'jwcfe' ); ?></li>
								<li>
									<?php esc_html_e( 'Label', 'jwcfe' ); ?>
									<span class="jwcfe-crown"><img src="<?php echo esc_url( $assets . 'crown.svg' ); ?>" alt="pro"></span>
								</li>
								<li><?php esc_html_e( 'URL', 'jwcfe' ); ?></li>
							</ul>
						</div>
						<div class="jwcfe-fields-img">
							<img src="<?php echo esc_url( $assets . 'fields.png' ); ?>" alt="<?php esc_attr_e( 'Field Types Preview', 'jwcfe' ); ?>">
						</div>
					</div>
				</section><!-- /.jwcfe-field-types -->

				<!-- ── Conditional Display: Sections & Fields ── -->
				<div class="jwcfe-fields-section-function">
					<div class="jwcfe-section-function">
						<section class="jwcfe-display-rule-section">
							<div class="jwcfe-cfe-pro">
								<img src="<?php echo esc_url( $assets . 'logo-blue.svg' ); ?>" alt="pro">
							</div>
							<div class="jwcfe-display-rule-section-head"><?php esc_html_e( 'Display Sections Conditionally', 'jwcfe' ); ?></div>
							<p class="jwcfe-display-rule-section-desc">
								<?php esc_html_e( 'Display custom sections on your checkout page based on conditions you set. Available positions include:', 'jwcfe' ); ?>
							</p>
							<ul class="jwcfe-display-section-list">
								<li><?php esc_html_e( 'Before customer details', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'After customer details', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'Before billing form', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'After billing form', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'Before shipping form', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'After shipping form', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'Before registration form', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'After registration form', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'Before order notes', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'After order notes', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'Before terms and conditions', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'After terms and conditions', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'Before submit button', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'After submit button', 'jwcfe' ); ?></li>
							</ul>
						</section>
						<section class="jwcfe-display-rule-section">
							<div class="jwcfe-cfe-pro">
								<img src="<?php echo esc_url( $assets . 'logo-blue.svg' ); ?>" alt="pro">
							</div>
							<div class="jwcfe-display-rule-section-head"><?php esc_html_e( 'My Account Page Fields Customisation ', 'jwcfe' ); ?></div>
							<p class="jwcfe-display-rule-section-desc">
								<?php esc_html_e( 'Add and manage custom fields on the WooCommerce My Account page. You can display fields at various positions including:', 'jwcfe' ); ?>
							</p>
							<ul class="jwcfe-display-section-list">
								<li><?php esc_html_e( 'Text', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'Hidden', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'Password', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'Telephone', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'Email', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'Number', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'Textarea', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'Select', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'Multiselect', 'jwcfe' ); ?></li>
								<li>
									<?php esc_html_e( 'Date Picker', 'jwcfe' ); ?>
								</li>
								<li><?php esc_html_e( 'Radio', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'Checkbox', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'Checkbox Group', 'jwcfe' ); ?></li>
								<li>
									<?php esc_html_e( 'Time Picker', 'jwcfe' ); ?>
								</li>
								<li>
									<?php esc_html_e( 'File Upload', 'jwcfe' ); ?>
								</li>
								<li><?php esc_html_e( 'Heading', 'jwcfe' ); ?></li>
								<li><?php esc_html_e( 'Paragraph', 'jwcfe' ); ?></li>
								<li>
									<?php esc_html_e( 'Label', 'jwcfe' ); ?>
								</li>
								<li><?php esc_html_e( 'URL', 'jwcfe' ); ?></li>
							</ul>
						</section>
					</div><!-- /.jwcfe-section-function -->

					<div class="jwcfe-fields-function">
						<section class="jwcfe-display-rule-fields">
							<div class="jwcfe-cfe-pro">
								<img src="<?php echo esc_url( $assets . 'logo-blue.svg' ); ?>" alt="pro">
							</div>
							<h3 class="jwcfe-display-rule-fields-head"><?php esc_html_e( 'Display Fields Conditionally', 'jwcfe' ); ?></h3>
							<p class="jwcfe-display-rule-fields-desc">
								<?php esc_html_e( 'Display custom and default checkout fields based on the conditions you provide. Available conditions:', 'jwcfe' ); ?>
							</p>
							<div class="jwcfe-dispaly-rule-list">
								<ul class="jwcfe-display-field-list">
									<li><?php esc_html_e( 'Cart Content', 'jwcfe' ); ?></li>
									<li><?php esc_html_e( 'Cart Subtotal', 'jwcfe' ); ?></li>
									<li><?php esc_html_e( 'Cart Total', 'jwcfe' ); ?></li>
									<li><?php esc_html_e( 'User Roles', 'jwcfe' ); ?></li>
									<li><?php esc_html_e( 'Product', 'jwcfe' ); ?></li>
									<li><?php esc_html_e( 'Product Variation', 'jwcfe' ); ?></li>
									<li><?php esc_html_e( 'Product Type', 'jwcfe' ); ?></li>
									<li><?php esc_html_e( 'Product Category & Tag', 'jwcfe' ); ?></li>
									<li><?php esc_html_e( 'Shipping Class', 'jwcfe' ); ?></li>
									<li><?php esc_html_e( 'Shipping Weight', 'jwcfe' ); ?></li>
									<li><?php esc_html_e( 'Based on other field values', 'jwcfe' ); ?></li>
									<li><?php esc_html_e( 'Shipping & Payment Methods', 'jwcfe' ); ?></li>
								</ul>
							</div>
						</section>

						<section class="jwcfe-price-fields">
							<div class="jwcfe-cfe-pro">
								<img src="<?php echo esc_url( $assets . 'logo-blue.svg' ); ?>" alt="pro">
							</div>
							<h3 class="jwcfe-price-fields-head"><?php esc_html_e( 'Add Price Fields and Choose the Price Type', 'jwcfe' ); ?></h3>
							<p class="jwcfe-price-fields-desc">
								<?php esc_html_e( 'Add extra price values to the cart total by creating price fields on the checkout form. Available price types:', 'jwcfe' ); ?>
							</p>
							<div class="jwcfe-price-field-list">
								<ul class="jwcfe-price-list">
									<li><?php esc_html_e( 'Fixed Price', 'jwcfe' ); ?></li>
									<li><?php esc_html_e( 'Custom Price', 'jwcfe' ); ?></li>
									<li><?php esc_html_e( 'Percentage of Cart Total', 'jwcfe' ); ?></li>
									<li><?php esc_html_e( 'Percentage of Subtotal', 'jwcfe' ); ?></li>
									<li><?php esc_html_e( 'Percentage of Subtotal (ex. tax)', 'jwcfe' ); ?></li>
									<li><?php esc_html_e( 'Dynamic Price', 'jwcfe' ); ?></li>
								</ul>
							</div>
						</section>
					</div><!-- /.jwcfe-fields-function -->
				</div><!-- /.jwcfe-fields-section-function -->

				<!-- ── Testimonial ── -->
				<div class="jwcfe-review-section">
					<div class="jwcfe-user-review">
						<h3 class="jwcfe-review-heading"><?php esc_html_e( 'Great plugin, even better support (free &amp; pro versions)', 'jwcfe' ); ?></h3>
						<p class="jwcfe-review-content">
							<?php esc_html_e( '"Started with the free version and it was already impressive. Once I upgraded to Pro, the experience got even better — more features, smoother workflow, and outstanding support. The team went above and beyond to help me out. Highly recommend upgrading to Pro, it is absolutely worth every penny!"', 'jwcfe' ); ?>
						</p>
						<p class="jwcfe-review-user-name">— <?php esc_html_e( 'Eric Kuznacic', 'jwcfe' ); ?></p>
					</div>
				</div>

				<!-- ── FAQ ── -->
				<section class="jwcfe-faq-tab">
					<div class="jwcfe-faq-desc">
						<h3><?php esc_html_e( "FAQ's", 'jwcfe' ); ?></h3>
						<p class="jwcfe-faq-para">
							<?php esc_html_e( "Don't worry! Here are answers to frequently asked questions. If you haven't been answered, feel free to contact our support team.", 'jwcfe' ); ?>
						</p>
					</div>

					<div class="jwcfe-faq-qstns">

						<!-- Q1 -->
						<div class="jwcfe-accordion">
							<div class="jwcfe-accordion-qstn">
								<p><?php esc_html_e( 'How do I upgrade to the premium version?', 'jwcfe' ); ?></p>
								<img class="jwcfe-accordion-img" src="<?php echo esc_url( $assets . 'accordion.svg' ); ?>" alt="+">
								<img class="jwcfe-accordion-img-opn" src="<?php echo esc_url( $assets . 'accordion.svg' ); ?>" alt="-">
							</div>
							<div class="jwcfe-panel">
								<p>
									<?php esc_html_e( 'Visit the link below to purchase the Pro version. After purchase you will receive a download link and license key.', 'jwcfe' ); ?>
								</p>
								<p class="jwcfe-faq-links">
									<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_url( $url ); ?></a>
								</p>
								<p class="jwcfe-faq-answer">
									<?php esc_html_e( 'Note: Please confirm whether all fields from the free version have been migrated to the premium version after upgrading. If so, you can safely deactivate and delete the free version.', 'jwcfe' ); ?>
								</p>
							</div>
						</div>

						<!-- Q2 -->
						<div class="jwcfe-accordion">
							<div class="jwcfe-accordion-qstn">
								<p><?php esc_html_e( 'Do I need to keep both the free and pro versions active?', 'jwcfe' ); ?></p>
								<img class="jwcfe-accordion-img" src="<?php echo esc_url( $assets . 'accordion.svg' ); ?>" alt="+">
								<img class="jwcfe-accordion-img-opn" src="<?php echo esc_url( $assets . 'accordion.svg' ); ?>" alt="-">
							</div>
							<div class="jwcfe-panel">
								<p class="jwcfe-faq-answer">
									<?php esc_html_e( 'No. The free and premium versions are separate plugins. Once you install and activate the Pro version, you can safely deactivate and delete the free version from your website.', 'jwcfe' ); ?>
								</p>
							</div>
						</div>

						<!-- Q3 -->
						<div class="jwcfe-accordion">
							<div class="jwcfe-accordion-qstn">
								<p><?php esc_html_e( 'How do I migrate settings from free to pro?', 'jwcfe' ); ?></p>
								<img class="jwcfe-accordion-img" src="<?php echo esc_url( $assets . 'accordion.svg' ); ?>" alt="+">
								<img class="jwcfe-accordion-img-opn" src="<?php echo esc_url( $assets . 'accordion.svg' ); ?>" alt="-">
							</div>
							<div class="jwcfe-panel">
								<p class="jwcfe-faq-answer">
									<?php esc_html_e( 'When you install the Pro version, your free plugin settings are automatically migrated. Please verify that all fields have been migrated before deactivating the free version.', 'jwcfe' ); ?>
								</p>
							</div>
						</div>

						<!-- Q4 -->
						<div class="jwcfe-accordion">
							<div class="jwcfe-accordion-qstn">
								<p><?php esc_html_e( 'Will I get a refund if the Pro version does not meet my requirements?', 'jwcfe' ); ?></p>
								<img class="jwcfe-accordion-img" src="<?php echo esc_url( $assets . 'accordion.svg' ); ?>" alt="+">
								<img class="jwcfe-accordion-img-opn" src="<?php echo esc_url( $assets . 'accordion.svg' ); ?>" alt="-">
							</div>
							<div class="jwcfe-panel">
								<p>
									<?php esc_html_e( 'We offer a 30-day money-back guarantee. If you are not satisfied, contact our support team within 30 days of purchase for a full refund.', 'jwcfe' ); ?>
								</p>
								<p class="jwcfe-faq-answer">
									<a href="<?php echo esc_url( 'https://jcodex.com/contact-us/' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Contact Support', 'jwcfe' ); ?></a>
								</p>
							</div>
						</div>

					</div><!-- /.jwcfe-faq-qstns -->
				</section><!-- /.jwcfe-faq-tab -->

				<!-- ── Final CTA ── -->
				<section class="jwcfe-switch-to-pro-tab">
					<div class="jwcfe-switch-to-pro">
						<h3 class="jwcfe-switch-to-pro-heading">
							<?php esc_html_e( 'Upgrade to Pro and Experience the Full Power of Our Features', 'jwcfe' ); ?>
						</h3>
						<p>
							<?php esc_html_e( 'Upgrade to Pro and get access to the most powerful features for your checkout page — and enjoy a seamless, one-of-a-kind experience like never before', 'jwcfe' ); ?>
						</p>
						<a class="jwcfe-button-get-pro"
						   href="<?php echo esc_url( $url ); ?>"
						   target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'Get Pro', 'jwcfe' ); ?>
						</a>
					</div>
				</section>

			</div><!-- /.jwcfe-wrapper-main -->
		</div><!-- /.jwcfe-nice-box -->

		<script>
		(function() {
			var accordions = document.querySelectorAll('.jwcfe-accordion');
			accordions.forEach(function(acc) {
				var panel = acc.querySelector('.jwcfe-panel');
				// Open all sections by default
				acc.classList.add('active');
				if (panel) {
					panel.classList.add('open');
				}
				// Toggle only this section on click (allow multiple open)
				var q = acc.querySelector('.jwcfe-accordion-qstn');
				if (q) {
					q.addEventListener('click', function() {
						acc.classList.toggle('active');
						if (panel) panel.classList.toggle('open');
					});
				}
			});
		})();
		</script>
		<?php
	}
}

endif;
