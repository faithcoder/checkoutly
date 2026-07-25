<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://jcodex.com
 *
 * @package    woo-checkout-regsiter-field-editor-premium
 * @subpackage woo-checkout-regsiter-field-editor-premium/admin
 */

if (!defined('WPINC')) {
	die;
}

if (!class_exists('JWCFE_Admin')):

	class JWCFE_Admin
	{
		private $plugin_name;
		private $version;
		private $screen_id;
		private $settings_fields;
		private $settings_license;
		private $locale_fields = array();


		public function __construct($plugin_name, $version)
		{

			$this->plugin_name = $plugin_name;
			$this->version = $version;
			$this->locale_fields = array(
				'billing_address_1',
				'billing_address_2',
				'billing_state',
				'billing_postcode',
				'billing_city',
				'shipping_address_1',
				'shipping_address_2',
				'shipping_state',
				'shipping_postcode',
				'shipping_city',
				'order_comments'
			);
		}



		public function enqueue_admin_scripts()
		{

			// Only load on our specific admin page
			if (isset($_GET['page']) && $_GET['page'] === 'jwcfe_checkout_register_editor') {

				wp_enqueue_style('jwcfe-newstyle', JWCFE_ASSETS_URL_ADMIN . 'css/jwcfe-newstyle.css', array(), $this->version);
				wp_enqueue_style('jwcfe-premium', JWCFE_ASSETS_URL_ADMIN . 'css/jwcfe-premium.css', array(), $this->version);
				wp_enqueue_editor();

				$deps = array('jquery', 'jquery-ui-tabs', 'jquery-ui-dialog', 'jquery-ui-sortable', 'woocommerce_admin', 'select2', 'jquery-tiptip');

				// Enqueue the polyfill script
				wp_enqueue_script('polyfill', JWCFE_ASSETS_URL_ADMIN . 'js/polyfill.js', array('jquery'), null, true);

				// Add 'polyfill' to the dependencies of jwcfe-admin-pro.js
				$deps[] = 'polyfill';

				wp_enqueue_script('jwcfe-admin-script', JWCFE_ASSETS_URL_ADMIN . 'js/jwcfe-admin-pro.js', $deps, $this->version, true);



				$tab = $this->get_current_tab();

				$fields_options = [];

				if ($tab === 'accounts') {
					$fields_options['account'] = get_option('jwcfe_wc_fields_account', []);
				} else if ($tab === 'checkoutfields') {
					$fields_options['shipping'] = get_option('jwcfe_wc_fields_block_shipping', []);
					$fields_options['additional'] = get_option('jwcfe_wc_fields_block_additional', []);
					$fields_options['billing'] = get_option('jwcfe_wc_fields_block_billing', []);
				}


				wp_localize_script('jwcfe-admin-script', 'WcfeAdmin', array(
					'MSG_INVALID_NAME' => 'NAME cannot contain spaces',
					'ajaxurl' => admin_url('admin-ajax.php'),
					'nonce' => wp_create_nonce('jwcfe_admin_nonce'),
					'wc_fields' => $fields_options,


				));

			}
		}



		public function admin_menu()
		{
			$this->screen_id = add_submenu_page(
				'woocommerce',
				esc_html__('Checkout Field Editor for Woocommerce - Checkout Manager', 'jwcfe'),
				esc_html__('Checkout Form Editor', 'jwcfe'),
				'manage_woocommerce',
				'jwcfe_checkout_register_editor',
				array($this, 'the_editor')
			);
		}

		public function add_screen_id($ids)
		{
			$ids[] = 'woocommerce_page_jwcfe_checkout_register_editor';
			$ids[] = strtolower(esc_html__('WooCommerce', 'jwcfe')) . '_page_jwcfe_checkout_register_editor';
			return $ids;
		}




		public function the_editor()
		{

			$Fields_settings = JWCFE_Admin_Settings_Fields::instance($this->plugin_name, $this->version);
			$blocks_Setting = new JWCFE_Admin_Settings_Block_Fields($this->plugin_name, $this->version);

			$tab = $this->get_current_tab();
			$c_type = $this->get_current_ctype();

			if ($tab === 'checkoutfields' && $c_type == 'classic') {
				$Fields_settings->checkout_form_field_editor();
			} else if ($tab === 'checkoutfields' && $c_type == 'block') {
				$blocks_Setting->checkout_form_field_editor();
			} elseif ($tab === 'accounts') {
				$Fields_settings->checkout_form_field_editor();
			} elseif ($tab === 'advanced_settings') {
				echo '<div class="wrap woocommerce jwcfe-wrap"><div class="icon32 icon32-attributes" id="icon-woocommerce"><br /></div>';
				$this->render_page_header();
				$this->render_tabs_and_sections();
				$advanced = JWCFE_Admin_Settings_Advanced::instance();
				$advanced->render_page();
				echo '</div>';
			}

		}


		public function get_current_tab()
		{
			$allowed_tabs = array('checkoutfields', 'accounts', 'advanced_settings');
			$tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'checkoutfields';

			return in_array($tab, $allowed_tabs, true) ? $tab : 'checkoutfields';
		}

		public function get_current_ctype()
		{
			$allowed_ctypes = array('classic', 'block'); // include accounts
			$ctype = isset($_GET['c_type']) ? sanitize_text_field($_GET['c_type']) : 'block';

			return in_array($ctype, $allowed_ctypes, true) ? $ctype : 'block';
		}




		public function get_current_section()
		{
			$tab = $this->get_current_tab();

			// Define sections based on the selected tab
			$sections_by_tab = array(
				'checkoutfields' => array('billing', 'shipping', 'additional'),
				'accounts' => array('account'), // <-- add this
			);

			// Default section per tab
			$default_section = ($tab === 'accounts') ? 'account' : 'billing';

			$sections = isset($sections_by_tab[$tab]) ? $sections_by_tab[$tab] : array();

			if (isset($_GET['section']) && in_array($_GET['section'], $sections, true)) {
				return sanitize_text_field($_GET['section']);
			}

			return $default_section;
		}

		public function compatibility_warning()
		{
			?>

			<div id="jc_block_warning" class="jc-block-warning-msg">
				<div class="jc-warning-message-panel__text jc-warning-message-panel__text--center">
					<div class="jc-warning-img">
						<img src="<?php echo plugin_dir_url(__FILE__) . 'assets/logo-blue.svg'; ?>" alt="Jcodex Logo">
					</div>
					<div class="jc-warning">

						<span class="jc-warning-message-panel__inner-text">
							<?php esc_html_e('Our Checkout Field Editor now supports WooCommerce Checkout Blocks! Currently, a few field types are available, and more will be added soon. 
									If you\'re using Block Checkout, make sure to switch to the Block Checkout Fields tab, otherwise, your changes won’t be reflected. Have questions or need help?', 'jwcfe'); ?>
							<?php esc_html_e('Reach out to our', 'jwcfe'); ?> <a
								href="<?php echo esc_url('https://jcodex.com/support/'); ?>" target="_blank"
								class="quick-widget-support-link"> <?php esc_html_e('Support team', 'jwcfe'); ?></a>.</span>
					</div>
				</div>
			</div>

			<?php
		}


		public function get_all_variations_of_product()
		{
			check_ajax_referer('jwcfe_admin_nonce');
			if (!current_user_can('manage_woocommerce')) {
				wp_send_json_error(array('message' => __('Unauthorized', 'jwcfe')), 403);
			}

			$attributes_dropdown = '';
			if (isset($_POST['pID']) && is_array($_POST['pID']) && count($_POST['pID']) >= 1) {
				$product_ids = $_POST['pID'];
				$selected_variations = $_POST['selected_variations'];

				foreach ($product_ids as $product_id) {
					$product = wc_get_product($product_id);
					$title = $product->get_name();

					if ($product && $product->is_type('variable')) {
						$variations = $product->get_available_variations(); // Get all available variations
						$attributes_dropdown .= '<select multiple="multiple" name="i_rule_operand_variation[]" data-placeholder="' . $title . ' to choose variations" class="jwcfe-enhanced-multi-select2 jwcfe-enhanced-multi-variations" style="width:200px;" value="">';
						$variation_data = array();
						foreach ($variations as $variation) {
							$variation_id = $variation['variation_id'];
							$variation_obj = wc_get_product($variation_id);
							$variation_name = implode(", ", $variation_obj->get_variation_attributes()); // Get variation attributes as a string
							$variation_price = $variation_obj->get_price(); // Get variation price
							$variation_data[] = array(
								'variation_id' => $variation_id,
								'variation_name' => $variation_name,
								'variation_price' => $variation_price
							);
							$selected = '';
							if (!empty($selected_variations) && in_array($variation_id, $selected_variations)) {
								$selected = 'selected';
							}
							$attributes_dropdown .= '<option value="' . $variation_id . '" ' . $selected . '>' . $variation_name . ' - $' . $variation_price . '</option>';
						}
						$attributes_dropdown .= '</select>';
					}
				}
			}
			echo $attributes_dropdown;
			wp_die();
		}


		public function render_page_header()
		{
			echo '<div style="display:flex;align-items:flex-start;justify-content:space-between;margin:16px 0 8px 0;"><div><h1 style="font-size:22px;font-weight:700;color:#1e1e1e;margin:0;display:flex;align-items:center;gap:10px;"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#2271b1" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>' . esc_html__('Checkout Field Editor', 'jwcfe') . ' </h1><p style="color:#666;font-size:13px;margin:4px 0 0 0;">' . esc_html__('Add, edit, and reorder WooCommerce checkout fields.', 'jwcfe') . '</p></div><a href="https://jcodex.com/docs/woocommerce-custom-checkout-field-editor/" target="_blank" style="display:inline-flex;align-items:center;gap:5px;color:#2271b1;font-size:13px;text-decoration:none;border:1px solid #2271b1;padding:6px 14px;border-radius:5px;font-weight:500;margin-top:4px;white-space:nowrap;">&#9432; ' . esc_html__('Documentation', 'jwcfe') . '</a></div>';
			if ( function_exists( 'jwcfe_render_review_notice' ) ) {
				jwcfe_render_review_notice( 'inline' );
			}
		}

		public function render_tabs_and_sections()
		{

			?>

			<div id="message" style="display:none; border-left-color: #6B2C88" class="wc-connect updated wcfe-notice">
				<div class="squeezer">
					<table>
						<tr>
							<td width="70%">
								<p><strong><i><?php esc_html_e('Custom Fields WooCommerce Checkout Page Pro Version', 'jwcfe'); ?></i></strong>
									<?php esc_html_e('premium version provides more features to design your checkout and my account page.', 'jwcfe'); ?>
								</p>
								<ul>
									<li><?php esc_html_e('18 field types are available: 15 input fields one field for title/heading and one for label.', 'jwcfe'); ?><br />(<i><?php esc_html_e('Text, Hidden, Password, Textarea, Radio, Checkbox, Select, Multi-select, Date Picker, Heading, Label', 'jwcfe'); ?></i>).
									</li>
									<li><?php esc_html_e('You can add all of these fields on my account page too.', 'jwcfe'); ?>
									</li>
									<li><?php esc_html_e('You can add more sections in addition to the core sections (billing, shipping and additional) in checkout page.', 'jwcfe'); ?>
									</li>
									<li><?php esc_html_e('You Can Integration of My Account With Checkout page', 'jwcfe'); ?></li>
									<li><?php esc_html_e('Add Conditionally based fields', 'jwcfe'); ?></li>

									<li><?php esc_html_e('See Plugin', 'jwcfe'); ?> <a
											href="<?php echo esc_url('https://jcodex.com/docs/woocommerce-custom-checkout-field-editor/'); ?>"
											target="_blank" class="doclink"><?php esc_html_e('Documentation', 'jwcfe'); ?></a>
									</li>
									<li><?php esc_html_e('You can talk to support any time if you have any queries', 'jwcfe'); ?> <a
											href="<?php echo esc_url('https://jcodex.com/contact-us/'); ?>"><?php esc_html_e('Click here', 'jwcfe'); ?></a>
									</li>

									<li><?php esc_html_e('IF you found this plugin helpful,', 'jwcfe'); ?> <a
											href="<?php echo esc_url('https://www.paypal.com/donate/?hosted_button_id=QD4H8N3QVLLML'); ?>"
											style=""><?php esc_html_e('Donate using PayPal', 'jwcfe'); ?></a></li>
								</ul>
							</td>

							<td>
								<a href="https://jcodex.com/plugins/woocommerce-custom-checkout-field-editor/" target="_blank">
									<button id="purchase" style="background: url('<?php echo plugins_url('/assets/css/upgrade.png', __FILE__); ?>'); 
											width: 302px; 
											height: 63px; 
											cursor: pointer; 
											border: none;">
										&nbsp;
									</button>

								</a>
								<br>
							</td>
						</tr>
					</table>

				</div>
			</div>

			<?php

			$this->compatibility_warning();

			// Define tabs - now with Accounts as a separate tab
			$tabs = array(
				'checkoutfields' => 'Checkout Fields',
				'advanced_settings' => 'Advanced Settings',
				'accounts' => 'Premium Features'
			);

			$allowed_tabs = array_keys($tabs);
			$tab = isset($_GET['tab']) && in_array($_GET['tab'], $allowed_tabs)
				? sanitize_text_field($_GET['tab'])
				: 'checkoutfields';  // Default to block tab

			// Define sections for each tab
			$sections = array();
			if ($tab === 'checkoutfields') {
				$sections = array('billing', 'shipping', 'additional');
			} elseif ($tab === 'accounts') {
				$sections = array('account');
			} elseif ($tab === 'advanced_settings') {
				$sections = array(); // No sections for advanced settings
			}

			// Validate section
			$section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : '';

			$c_type = isset($_GET['c_type']) ? sanitize_text_field($_GET['c_type']) : '';
			if (!empty($sections) && !in_array($section, $sections)) {
				// Redirect to first section if invalid/empty
				if (!empty($sections)) {
					$first_section = reset($sections);
					$c_type = isset($_GET['c_type']) ? sanitize_text_field($_GET['c_type']) : 'block';
					wp_safe_redirect(admin_url(
						'admin.php?page=jwcfe_checkout_register_editor&tab=' . $tab . '&c_type=' . $c_type . '&section=' . $first_section
					));
					exit;
				}
			}

			// Render tabs
			echo '<div class="jwcfe-section-card" style="padding: 10px 20px; border-radius: 8px;">';
			echo '<h2 class="nav-tab-wrapper woo-nav-tab-wrapper" style="display:flex; align-items:center; flex-wrap:wrap; border-bottom: none; margin-bottom: 0; padding: 0;">';

			// 1. Classic Checkout
			$active_classic = ($tab === 'checkoutfields' && $c_type === 'classic') ? 'nav-tab-active' : '';
			echo '<a class="nav-tab ' . esc_attr($active_classic) . '" href="' . esc_url(admin_url('admin.php?page=jwcfe_checkout_register_editor&tab=checkoutfields&c_type=classic')) . '">' . esc_html__('Classic Checkout', 'jwcfe') . '</a>';

			// 2. Block Checkout
			$active_block = ($tab === 'checkoutfields' && $c_type === 'block') ? 'nav-tab-active' : '';
			echo '<a class="nav-tab ' . esc_attr($active_block) . '" href="' . esc_url(admin_url('admin.php?page=jwcfe_checkout_register_editor&tab=checkoutfields&c_type=block')) . '">' . esc_html__('Block Checkout', 'jwcfe') . '</a>';

			// Information Tooltip for Checkout (placed inline inside the h2 element if we are on checkout fields)
			if ($tab === 'checkoutfields') {
				$captlize_ctype = ucfirst($c_type);
				echo '<div class="ct-info" id="th_info_container" style="margin-left: 10px; margin-top: 5px;">
				<img src="' . esc_url(plugins_url('assets/info.svg', __FILE__)) . '" alt="' . esc_attr__('Checkout Info', 'jwcfe') . '">
				<div class="ct-info-box" id="infoBox" style="display: none;">
					<p>' .
					sprintf(
						/* translators: %1$s: checkout type (e.g., Classic or Block), %2$s: help link */
						esc_html__(
							"You're on the %1\$s Checkout Field Editor right now. If your store is not using %1\$s Checkout, fields you add here won’t appear on the checkout page. Unsure which checkout type your store is using? %2\$s.",
							'jwcfe'
						),
						esc_html($captlize_ctype),
						'<a href="' . esc_url('https://jcodex.com/doc/classic-vs-block-checkout/') . '" target="_blank" rel="noopener noreferrer">' .
						esc_html__('Click here to find out!', 'jwcfe') .
						'</a>'
					)
					. '</p>
				</div>
				</div>';
			}

			// 3. Advanced Settings
			$active_adv = ($tab === 'advanced_settings') ? 'nav-tab-active' : '';
			echo '<a class="nav-tab ' . esc_attr($active_adv) . '" href="' . esc_url(admin_url('admin.php?page=jwcfe_checkout_register_editor&tab=advanced_settings')) . '">' . esc_html__('Advanced Settings', 'jwcfe') . '</a>';

			// 4. Premium Features
			$active_prem = ($tab === 'accounts') ? 'nav-tab-active' : '';
			echo '<a class="nav-tab ' . esc_attr($active_prem) . '" href="' . esc_url(admin_url('admin.php?page=jwcfe_checkout_register_editor&tab=accounts&section=account')) . '">' . esc_html__('Premium Features', 'jwcfe') . '</a>';

			echo '</h2>';
			echo '</div>';


		}


		public function save_options($section)
		{
			$tab = $this->get_current_tab();
			$ctype = $this->get_current_ctype();
			if (isset($_POST['woo_checkout_editor_nonce']) && wp_verify_nonce($_POST['woo_checkout_editor_nonce'], 'woo_checkout_editor_settings')) {
				// Handle settings saving
				$o_fields = JWCFE_Helper::get_fields($section);
				$fields = $o_fields;
				$f_order = !empty($_POST['f_order']) ? $_POST['f_order'] : array();
				$f_names = !empty($_POST['f_name']) ? $_POST['f_name'] : array();
				$f_names_new = !empty($_POST['f_name_new']) ? $_POST['f_name_new'] : array();
				$f_types = !empty($_POST['f_type']) ? $_POST['f_type'] : array();

				$f_labels = !empty($_POST['f_label']) ? $_POST['f_label'] : array();

				$f_texteditor = !empty($_POST['f_texteditor']) ? $_POST['f_texteditor'] : array();
				// var_dump("upper",$f_texteditor);

				$f_extoptions = !empty($_POST['f_extoptions']) ? $_POST['f_extoptions'] : array();
				$f_access = !empty($_POST['f_access']) ? $_POST['f_access'] : array();
				$f_placeholder = !empty($_POST['f_placeholder']) ? $_POST['f_placeholder'] : array();
				$f_default = !empty($_POST['f_default']) ? $_POST['f_default'] : array();
				$i_min_time = !empty($_POST['i_min_time']) ? $_POST['i_min_time'] : array();
				$i_max_time = !empty($_POST['i_max_time']) ? $_POST['i_max_time'] : array();
				$i_time_step = !empty($_POST['i_time_step']) ? $_POST['i_time_step'] : array();
				$i_time_format = !empty($_POST['i_time_format']) ? $_POST['i_time_format'] : array();
				$f_maxlength = !empty($_POST['f_maxlength']) ? $_POST['f_maxlength'] : array();
				$f_heading_type = !empty($_POST['f_heading_type']) ? $_POST['f_heading_type'] : array();

				$f_options = array();
				if (isset($_POST['f_options'])) {
					$f_options = !empty($_POST['f_options']) ? $_POST['f_options'] : array();
				}

				$f_text = !empty($_POST['f_text']) ? $_POST['f_text'] : array();

				$f_rules_action = array();
				if (isset($_POST['f_rules_action'])) {
					if (!empty($_POST['f_rules_action'])) {
						$f_rules_action = $_POST['f_rules_action'];
					} else {
						$f_rules_action = array();
					}
				}



				$f_rules = !empty($_POST['f_rules']) ? $_POST['f_rules'] : '';

				$f_rules_action_ajax = array();
				if (isset($_POST['f_rules_action_ajax'])) {
					if (!empty($_POST['f_rules_action_ajax'])) {
						$f_rules_action_ajax = $_POST['f_rules_action_ajax'];
					} else {
						$f_rules_action_ajax = array();
					}
				}



				$f_rules_ajax = !empty($_POST['f_rules_ajax']) ? $_POST['f_rules_ajax'] : '';


				// $f_label_class  = !empty($_POST['f_label_class']) ? $_POST['f_label_class'] : array();
				$f_label_class_raw = $_POST['f_label_class'] ?? [];

				if (is_array($f_label_class_raw)) {
					$f_label_class = array_filter($f_label_class_raw, function ($val) {
						return !empty($val) && $val !== 'undefined';
					});
				} elseif (is_string($f_label_class_raw) && $f_label_class_raw !== 'undefined') {
					$f_label_class = [$f_label_class_raw];
				} else {
					$f_label_class = [];
				}

				$f_class = !empty($_POST['f_class']) ? $_POST['f_class'] : array();
				$f_required = !empty($_POST['f_required']) ? $_POST['f_required'] : array();
				$f_is_include = !empty($_POST['f_is_include']) ? $_POST['f_is_include'] : array();
				$f_enabled = !empty($_POST['f_enabled']) ? $_POST['f_enabled'] : array();
				$f_show_in_email = !empty($_POST['f_show_in_email']) ? $_POST['f_show_in_email'] : array();
				$f_show_in_order = !empty($_POST['f_show_in_order']) ? $_POST['f_show_in_order'] : array();
				$f_validation = !empty($_POST['f_validation']) ? $_POST['f_validation'] : array();
				$f_deleted = !empty($_POST['f_deleted']) ? $_POST['f_deleted'] : array();
				$f_position = !empty($_POST['f_position']) ? $_POST['f_position'] : array();
				$f_display_options = !empty($_POST['f_display_options']) ? $_POST['f_display_options'] : array();

				// $max ='';

				$max = max(array_map('absint', array_keys($f_names)));

				for ($i = 0; $i <= $max; $i++) {
					$name = empty($f_names[$i]) ? '' : urldecode(sanitize_title(wc_clean(stripslashes($f_names[$i]))));
					$new_name = empty($f_names_new[$i]) ? '' : urldecode(sanitize_title(wc_clean(stripslashes($f_names_new[$i]))));


					if (!empty($f_deleted[$i]) && $f_deleted[$i] == 1) {
						unset($fields[$name]);
						continue;
					}

					// Check reserved names
					if ($this->is_reserved_field_name($new_name)) {
						continue;
					}

					//if update field
					if ($name && $new_name && $new_name !== $name) {

						if (isset($fields[$name])) {
							$fields[$new_name] = $fields[$name];
						} else {
							$fields[$new_name] = array();
						}
						unset($fields[$name]);
						$name = $new_name;
					} else {
						$name = $name ? $name : $new_name;
					}

					if (!$name) {
						continue;
					}

					// if new field

					if (!isset($fields[$name])) {
						$fields[$name] = array();
					}
					$o_type = isset($o_fields[$name]['type']) ? $o_fields[$name]['type'] : 'text';

					$allowed_tags = array(
						'a' => array(
							'class' => array(),
							'href' => array(),
							'rel' => array(),
							'title' => array(),
						),
						'abbr' => array(
							'title' => array(),
						),
						'b' => array(),
						'blockquote' => array(
							'cite' => array(),
						),
						'cite' => array(
							'title' => array(),
						),
						'code' => array(),
						'del' => array(
							'datetime' => array(),
							'title' => array(),
						),

						'dd' => array(),
						'div' => array(
							'class' => array(),
							'title' => array(),
							'style' => array(),
						),
						'dl' => array(),
						'dt' => array(),
						'em' => array(),
						'h1' => array(),
						'h2' => array(),
						'h3' => array(),
						'h4' => array(),
						'h5' => array(),
						'h6' => array(),
						'i' => array(),
						'img' => array(
							'alt' => array(),
							'class' => array(),
							'height' => array(),
							'src' => array(),
							'width' => array(),
						),

						'li' => array(
							'class' => array(),
						),
						'ol' => array(
							'class' => array(),
						),
						'p' => array(
							'class' => array(),
						),
						'q' => array(
							'cite' => array(),
							'title' => array(),
						),
						'span' => array(
							'class' => array(),
							'title' => array(),
							'style' => array(),
						),
						'strike' => array(),
						'strong' => array(),
						'ul' => array(
							'class' => array(),
						),
					);
					$fields[$name]['type'] = empty($f_types[$i]) ? $o_type : wc_clean($f_types[$i]);
					$fields[$name]['label'] = empty($f_labels[$i]) ? '' : wp_kses_post(trim(stripslashes($f_labels[$i])));
					$fields[$name]['text'] = empty($f_text[$i]) ? '' : $f_text[$i];

					$fields[$name]['texteditor'] = empty($f_texteditor[$i])
						? ''
						: wp_kses($f_texteditor[$i], $allowed_tags);


					$fields[$name]['access'] = empty($f_access[$i]) ? false : true;
					$fields[$name]['placeholder'] = empty($f_placeholder[$i]) ? '' : wc_clean(stripslashes($f_placeholder[$i]));
					$fields[$name]['default'] = empty($f_default[$i]) ? '' : wc_clean(stripslashes($f_default[$i]));
					$fields[$name]['min_time'] = empty($i_min_time[$i]) ? '' : wc_clean(stripslashes($i_min_time[$i]));
					$fields[$name]['max_time'] = empty($i_max_time[$i]) ? '' : wc_clean(stripslashes($i_max_time[$i]));
					$fields[$name]['time_step'] = empty($i_time_step[$i]) ? '' : wc_clean(stripslashes($i_time_step[$i]));
					$fields[$name]['time_format'] = empty($i_time_format[$i]) ? '' : wc_clean(stripslashes($i_time_format[$i]));
					$fields[$name]['heading_type'] = empty($f_heading_type[$i]) ? 'h4' : wc_clean(stripslashes($f_heading_type[$i]));
					$fields[$name]['options_json'] = empty($f_options[$i]) ? '' : json_decode(urldecode($f_options[$i]), true);
					$fields[$name]['maxlength'] = empty($f_maxlength[$i]) ? '' : wc_clean(stripslashes($f_maxlength[$i]));
					$fields[$name]['class'] = empty($f_class[$i]) ? array() : array_filter(array_map('sanitize_html_class', explode(' ', $f_class[$i])));
					$fields[$name]['label_class'] = empty($f_label_class[$i]) ? array() : array_map('wc_clean', explode(',', $f_label_class[$i]));
					$fields[$name]['rules_action'] = empty($f_rules_action[$i]) ? '' : $f_rules_action[$i];
					$fields[$name]['rules'] = empty($f_rules[$i]) ? '' : $f_rules[$i];
					$fields[$name]['rules_action_ajax'] = empty($f_rules_action_ajax[$i]) ? '' : $f_rules_action_ajax[$i];
					$fields[$name]['rules_ajax'] = empty($f_rules_ajax[$i]) ? '' : $f_rules_ajax[$i];
					$fields[$name]['required'] = empty($f_required[$i]) ? false : true;
					$fields[$name]['is_include'] = empty($f_is_include[$i]) ? false : true;
					$fields[$name]['enabled'] = empty($f_enabled[$i]) ? false : true;
					$fields[$name]['order'] = empty($f_order[$i]) ? '' : wc_clean($f_order[$i]);

					if (!in_array($name, $this->locale_fields)) {
						$fields[$name]['validate'] = empty($f_validation[$i]) ? array() : explode(',', $f_validation[$i]);
					}

					$fields[$name]['extoptions'] = empty($f_extoptions[$i]) ? array() : explode(',', $f_extoptions[$i]);

					if (!$this->is_default_field_name($name)) {
						$fields[$name]['custom'] = true;
						$fields[$name]['show_in_email'] = empty($f_show_in_email[$i]) ? false : true;
						$fields[$name]['show_in_order'] = empty($f_show_in_order[$i]) ? false : true;
					} else {
						$fields[$name]['custom'] = false;
					}

					$fields[$name]['label'] = $fields[$name]['label'];
					$fields[$name]['texteditor'] = $fields[$name]['texteditor'];

					$fields[$name]['placeholder'] = esc_html__($fields[$name]['placeholder'], 'woocommerce');
					$fields[$name]['maxlength'] = esc_html__($fields[$name]['maxlength'], 'woocommerce');
				}

				uasort($fields, array($this, 'sort_fields_by_order'));
				if ($tab === 'checkoutfields' && $ctype == 'classic') {
					// All-sections-on-one-page: group fields by f_section and save each
					$f_sections_post = !empty($_POST['f_section']) ? $_POST['f_section'] : array();
					if (!empty($f_sections_post)) {
						// Build per-section field arrays from the merged POST
						$sections_to_save = array('billing', 'shipping', 'additional');
						foreach ($sections_to_save as $_sec) {
							$_sec_existing = JWCFE_Helper::get_fields($_sec);
							$_sec_fields = $_sec_existing;
							$_max = !empty($f_names) ? max(array_map('absint', array_keys($f_names))) : 0;
							for ($_i = 0; $_i <= $_max; $_i++) {
								if (!isset($f_sections_post[$_i]) || $f_sections_post[$_i] !== $_sec)
									continue;
								$_name = empty($f_names[$_i]) ? '' : urldecode(sanitize_title(wc_clean(stripslashes($f_names[$_i]))));
								if (!$_name)
									continue;
								if (!empty($f_deleted[$_i]) && $f_deleted[$_i] == 1) {
									unset($_sec_fields[$_name]);
									continue;
								}
								if (isset($fields[$_name])) {
									$_sec_fields[$_name] = $fields[$_name];
								}
							}
							uasort($_sec_fields, array($this, 'sort_fields_by_order'));
							update_option('jwcfe_wc_fields_' . $_sec, $_sec_fields);
						}
						$result = true;
					} else {
						$result = update_option('jwcfe_wc_fields_' . $section, $fields);
					}


				} else if ($tab === 'checkoutfields' && $ctype == 'block') {
					if (isset($_POST['woo_checkout_editor_nonce']) && wp_verify_nonce($_POST['woo_checkout_editor_nonce'], 'woo_checkout_editor_settings')) {
						// Handle settings saving
						$o_fields = JWCFE_Helper::get_fields($section);
						$fields = $o_fields;
						$f_order = !empty($_POST['f_order']) ? $_POST['f_order'] : array();
						$f_names = !empty($_POST['f_name']) ? $_POST['f_name'] : array();
						$f_names_new = !empty($_POST['f_name_new']) ? $_POST['f_name_new'] : array();
						$f_types = !empty($_POST['f_type']) ? $_POST['f_type'] : array();
						$f_labels = !empty($_POST['f_label']) ? $_POST['f_label'] : array();
						$f_extoptions = !empty($_POST['f_extoptions']) ? $_POST['f_extoptions'] : array();
						$f_access = !empty($_POST['f_access']) ? $_POST['f_access'] : array();
						$f_placeholder = !empty($_POST['f_placeholder']) ? $_POST['f_placeholder'] : array();
						$f_default = !empty($_POST['f_default']) ? $_POST['f_default'] : array();
						$i_min_time = !empty($_POST['i_min_time']) ? $_POST['i_min_time'] : array();
						$i_max_time = !empty($_POST['i_max_time']) ? $_POST['i_max_time'] : array();
						$i_time_step = !empty($_POST['i_time_step']) ? $_POST['i_time_step'] : array();
						$i_time_format = !empty($_POST['i_time_format']) ? $_POST['i_time_format'] : array();
						$f_maxlength = !empty($_POST['f_maxlength']) ? $_POST['f_maxlength'] : array();
						$f_heading_type = !empty($_POST['f_heading_type']) ? $_POST['f_heading_type'] : array();

						$f_options = array();
						if (isset($_POST['f_options'])) {
							$f_options = !empty($_POST['f_options']) ? $_POST['f_options'] : array();
						}

						$f_text = !empty($_POST['f_text']) ? $_POST['f_text'] : array();

						if (isset($_POST['f_rules_action'])) {
							if (!empty($_POST['f_rules_action'])) {
								$f_rules_action = $_POST['f_rules_action'];
							} else {
								$f_rules_action = array();
							}
						}



						$f_rules = !empty($_POST['f_rules']) ? $_POST['f_rules'] : '';

						if (isset($_POST['f_rules_action_ajax'])) {
							if (!empty($_POST['f_rules_action_ajax'])) {
								$f_rules_action_ajax = $_POST['f_rules_action_ajax'];
							} else {
								$f_rules_action_ajax = array();
							}
						}



						$f_rules_ajax = !empty($_POST['f_rules_ajax']) ? $_POST['f_rules_ajax'] : '';


						$f_label_class = !empty($_POST['f_label_class']) ? $_POST['f_label_class'] : array();
						$f_class = !empty($_POST['f_class']) ? $_POST['f_class'] : array();
						$f_required = !empty($_POST['f_required']) ? $_POST['f_required'] : array();
						$f_is_include = !empty($_POST['f_is_include']) ? $_POST['f_is_include'] : array();
						$f_enabled = !empty($_POST['f_enabled']) ? $_POST['f_enabled'] : array();
						$f_show_in_email = !empty($_POST['f_show_in_email']) ? $_POST['f_show_in_email'] : array();
						$f_show_in_order = !empty($_POST['f_show_in_order']) ? $_POST['f_show_in_order'] : array();
						$f_validation = !empty($_POST['f_validation']) ? $_POST['f_validation'] : array();
						$f_deleted = !empty($_POST['f_deleted']) ? $_POST['f_deleted'] : array();
						$f_position = !empty($_POST['f_position']) ? $_POST['f_position'] : array();
						$f_display_options = !empty($_POST['f_display_options']) ? $_POST['f_display_options'] : array();

						$max = max(array_map('absint', array_keys($f_names)));

						for ($i = 0; $i <= $max; $i++) {
							$name = empty($f_names[$i]) ? '' : urldecode(sanitize_title(wc_clean(stripslashes($f_names[$i]))));
							$new_name = empty($f_names_new[$i]) ? '' : urldecode(sanitize_title(wc_clean(stripslashes($f_names_new[$i]))));


							if (!empty($f_deleted[$i]) && $f_deleted[$i] == 1) {
								unset($fields[$name]);
								continue;
							}

							// Check reserved names
							if ($this->is_reserved_field_name($new_name)) {
								continue;
							}

							//if update field
							if ($name && $new_name && $new_name !== $name) {

								if (isset($fields[$name])) {
									$fields[$new_name] = $fields[$name];
								} else {
									$fields[$new_name] = array();
								}
								unset($fields[$name]);
								$name = $new_name;
							} else {
								$name = $name ? $name : $new_name;
							}

							if (!$name) {
								continue;
							}

							// if new field

							if (!isset($fields[$name])) {
								$fields[$name] = array();
							}
							$o_type = isset($o_fields[$name]['type']) ? $o_fields[$name]['type'] : 'text';

							$allowed_tags = array(
								'a' => array(
									'class' => array(),
									'href' => array(),
									'rel' => array(),
									'title' => array(),
								),
								'abbr' => array(
									'title' => array(),
								),
								'b' => array(),
								'blockquote' => array(
									'cite' => array(),
								),
								'cite' => array(
									'title' => array(),
								),
								'code' => array(),
								'del' => array(
									'datetime' => array(),
									'title' => array(),
								),

								'dd' => array(),
								'div' => array(
									'class' => array(),
									'title' => array(),
									'style' => array(),
								),
								'dl' => array(),
								'dt' => array(),
								'em' => array(),
								'h1' => array(),
								'h2' => array(),
								'h3' => array(),
								'h4' => array(),
								'h5' => array(),
								'h6' => array(),
								'i' => array(),
								'img' => array(
									'alt' => array(),
									'class' => array(),
									'height' => array(),
									'src' => array(),
									'width' => array(),
								),

								'li' => array(
									'class' => array(),
								),
								'ol' => array(
									'class' => array(),
								),
								'p' => array(
									'class' => array(),
								),
								'q' => array(
									'cite' => array(),
									'title' => array(),
								),
								'span' => array(
									'class' => array(),
									'title' => array(),
									'style' => array(),
								),
								'strike' => array(),
								'strong' => array(),
								'ul' => array(
									'class' => array(),
								),
							);
							$fields[$name]['type'] = empty($f_types[$i]) ? $o_type : wc_clean($f_types[$i]);
							$fields[$name]['label'] = empty($f_labels[$i]) ? '' : wp_kses_post(trim(stripslashes($f_labels[$i])));
							$fields[$name]['text'] = empty($f_text[$i]) ? '' : $f_text[$i];
							$fields[$name]['access'] = empty($f_access[$i]) ? false : true;
							$fields[$name]['placeholder'] = empty($f_placeholder[$i]) ? '' : wc_clean(stripslashes($f_placeholder[$i]));
							$fields[$name]['default'] = empty($f_default[$i]) ? '' : wc_clean(stripslashes($f_default[$i]));
							$fields[$name]['min_time'] = empty($i_min_time[$i]) ? '' : wc_clean(stripslashes($i_min_time[$i]));
							$fields[$name]['max_time'] = empty($i_max_time[$i]) ? '' : wc_clean(stripslashes($i_max_time[$i]));
							$fields[$name]['time_step'] = empty($i_time_step[$i]) ? '' : wc_clean(stripslashes($i_time_step[$i]));
							$fields[$name]['time_format'] = empty($i_time_format[$i]) ? '' : wc_clean(stripslashes($i_time_format[$i]));
							$fields[$name]['heading_type'] = empty($f_heading_type[$i]) ? 'h4' : wc_clean(stripslashes($f_heading_type[$i]));
							$fields[$name]['options_json'] = empty($f_options[$i]) ? '' : json_decode(urldecode($f_options[$i]), true);
							$fields[$name]['maxlength'] = empty($f_maxlength[$i]) ? '' : wc_clean(stripslashes($f_maxlength[$i]));
							$fields[$name]['class'] = empty($f_class[$i]) ? array() : array_filter(array_map('sanitize_html_class', explode(' ', $f_class[$i])));
							$fields[$name]['label_class'] = empty($f_label_class[$i]) ? array() : array_map('wc_clean', explode(',', $f_label_class[$i]));
							$fields[$name]['rules_action'] = empty($f_rules_action[$i]) ? '' : $f_rules_action[$i];
							$fields[$name]['rules'] = empty($f_rules[$i]) ? '' : $f_rules[$i];
							$fields[$name]['rules_action_ajax'] = empty($f_rules_action_ajax[$i]) ? '' : $f_rules_action_ajax[$i];
							$fields[$name]['rules_ajax'] = empty($f_rules_ajax[$i]) ? '' : $f_rules_ajax[$i];
							$fields[$name]['required'] = empty($f_required[$i]) ? false : true;
							$fields[$name]['is_include'] = empty($f_is_include[$i]) ? false : true;
							$fields[$name]['enabled'] = empty($f_enabled[$i]) ? false : true;
							$fields[$name]['order'] = empty($f_order[$i]) ? '' : wc_clean($f_order[$i]);

							if (!in_array($name, $this->locale_fields)) {
								$fields[$name]['validate'] = empty($f_validation[$i]) ? array() : explode(',', $f_validation[$i]);
							}

							$fields[$name]['extoptions'] = empty($f_extoptions[$i]) ? array() : explode(',', $f_extoptions[$i]);

							if (!$this->is_default_field_name($name)) {
								$fields[$name]['custom'] = true;
								$fields[$name]['show_in_email'] = empty($f_show_in_email[$i]) ? false : true;
								$fields[$name]['show_in_order'] = empty($f_show_in_order[$i]) ? false : true;
							} else {
								$fields[$name]['custom'] = false;
							}

							$fields[$name]['label'] = $fields[$name]['label'];
							$fields[$name]['placeholder'] = esc_html__($fields[$name]['placeholder'], 'woocommerce');
							$fields[$name]['maxlength'] = esc_html__($fields[$name]['maxlength'], 'woocommerce');
						}
						$excluded_fields = ['billing_first_name', 'billing_last_name', 'billing_country', 'billing_address_1', 'billing_city'];

						// Remove excluded fields before saving
						foreach ($excluded_fields as $field) {
							unset($fields[$field]);
						}

						// Sort fields before saving
						uasort($fields, array($this, 'sort_fields_by_order'));
						// All-sections-on-one-page: save each block section separately using f_section[]
						$f_sections_post_b = !empty($_POST['f_section']) ? $_POST['f_section'] : array();
						if (!empty($f_sections_post_b)) {
							foreach (array('billing', 'shipping', 'additional') as $_bsec) {
								$_bsec_existing = JWCFE_Helper::get_fields($_bsec);
								$_bsec_fields = $_bsec_existing;
								$_bmax = !empty($f_names) ? max(array_map('absint', array_keys($f_names))) : 0;
								for ($_bi = 0; $_bi <= $_bmax; $_bi++) {
									if (!isset($f_sections_post_b[$_bi]) || $f_sections_post_b[$_bi] !== $_bsec)
										continue;
									$_bname = empty($f_names[$_bi]) ? '' : urldecode(sanitize_title(wc_clean(stripslashes($f_names[$_bi]))));
									if (!$_bname)
										continue;
									if (!empty($f_deleted[$_bi]) && $f_deleted[$_bi] == 1) {
										unset($_bsec_fields[$_bname]);
										continue;
									}
									if (isset($fields[$_bname])) {
										$_bsec_fields[$_bname] = $fields[$_bname];
									}
								}
								uasort($_bsec_fields, array($this, 'sort_fields_by_order'));
								update_option('jwcfe_wc_fields_block_' . $_bsec, $_bsec_fields);
							}
							$result = true;
						} else {
							$result = update_option('jwcfe_wc_fields_block_' . $section, $fields);
						}
						if ($result == true) {
							// echo '<div class="updated"><p>' . esc_html__('Your changes were saved.', 'jwcfe') . '</p></div>';
						} else {
							// echo '<div class="success"><p> ' . esc_html__("Your changes have been successfully saved. There's nothing more to save.", 'jwcfe') . '</p></div>';
						}
					} else {
						wp_die('Security check failed. Please try again or contact support for assistance.', 'Security Error');
					}
				}



				if ($result == true) {
					echo '<div class="updated"><p>' . esc_html__('Your changes were saved.', 'jwcfe') . '</p></div>';
				} else {
					echo '<div class="success"><p> ' . esc_html__("Your changes have been successfully saved. There's nothing more to save.", 'jwcfe') . '</p></div>';
				}
			} else {
				wp_die('Security check failed. Please try again or contact support for assistance.', 'Security Error');
			}



		}


		public function save_jwcfe_options()
		{
			check_ajax_referer('jwcfe_admin_nonce');
			if (!current_user_can('manage_woocommerce')) {
				wp_send_json_error(array('message' => __('Unauthorized', 'jwcfe')), 403);
			}

			$section_label = "";
			$sync_with_checkout = "";

			foreach ($_POST['formdata'] as $formRow) {
				if ($formRow['name'] == 'section_label') {
					$section_label = $formRow['value'];
				}
				if ($formRow['name'] == 'section_email_heading') {

					$section_email_heading = $formRow['value'];
				}
				if ($formRow['name'] == 'section_order_heading') {
					$section_order_heading = $formRow['value'];
				}

				if ($formRow['name'] == 'sync_with_checkout') {
					$sync_with_checkout = $formRow['value'];
				}
			}

			if (!empty($section_label)) {
				update_option('jwcfe_account_label', $section_label);
			}

			if (!empty($section_email_heading)) {
				update_option('jwcfe_email_label', $section_email_heading);
			}

			if (!empty($section_order_heading)) {
				update_option('jwcfe_order_label', $section_order_heading);
			}
			if (!empty($sync_with_checkout)) {
				update_option('jwcfe_account_sync_fields', $sync_with_checkout);
			} else {
				update_option('jwcfe_account_sync_fields', 'off');
			}
			echo '1';
			die();
		}



		// jwcfe order details after placed order ===============

		public function jwcfe_checkout_field_display_admin_order_meta_billing($order)
		{
			if (JWCFE_Helper::jwcfe_woocommerce_version_check()) {
				$order_id = $order->get_id();
			} else {
				$order_id = $order->id;
			}

			$fields = JWCFE_Helper::get_fields('billing');

			$fields_html = '';
			if (is_array($fields) && !empty($fields)) {
				// Loop through all custom fields to see if it should be added
				foreach ($fields as $name => $options) {

					$enabled = (isset($options['enabled']) && $options['enabled'] == false) ? false : true;
					$is_custom_field = (isset($options['custom']) && $options['custom'] == true) ? true : false;

					if (isset($options['show_in_order']) && $options['show_in_order'] && $enabled && $is_custom_field) {

						$order = wc_get_order($order_id);
						$value = $order->get_meta($name, true);

						if (is_array($value)) {
							$value = implode(", ", $value); // Convert array to comma-separated string
						}

						if (!empty($value)) {
							$label = isset($options['label']) && !empty($options['label']) ? __($options['label'], 'jwcfe') : $name;
							$fields_html .= '<p><strong>' . __($label, 'jwcfe') . ':</strong> <br/>' . esc_html($value) . '</p>';
						}
					}
				}

				$allowtags = array(
					'h2' => array(),
					'table' => array(),
					'tr' => array(),
					'td' => array(),
					'strong' => array(),
					'br' => array(),
					'th' => array(),
					'p' => array(),
					'a' => array('href' => array(), 'download' => array()) // Allow 'a' tags with 'href' and 'download' attributes
				);
				echo wp_kses($fields_html, $allowtags);
			}
		}



		public function jwcfe_checkout_field_display_admin_order_meta_shipping($order)
		{

			if (JWCFE_Helper::jwcfe_woocommerce_version_check()) {
				$order_id = $order->get_id();
			} else {
				$order_id = $order->id;
			}

			// $fields = array();
			$fields = JWCFE_Helper::get_fields('shipping');

			if (!wc_ship_to_billing_address_only() && $order->needs_shipping_address()) {
				$fields = array_merge(JWCFE_Helper::get_fields('shipping'), JWCFE_Helper::get_fields('additional'));
			}


			$fields_html = '';
			if (is_array($fields) && !empty($fields)) {


				// Loop through all custom fields to see if it should be added
				foreach ($fields as $name => $options) {

					$enabled = (isset($options['enabled']) && $options['enabled'] == false) ? false : true;
					$is_custom_field = (isset($options['custom']) && $options['custom'] == true) ? true : false;

					if (isset($options['show_in_order']) && $options['show_in_order'] && $enabled && $is_custom_field) {

						if ($options['type'] == 'select') {
							$order = wc_get_order($order_id);
							$value = $order->get_meta($name, true);

							if (is_array($value)) {
								$value = implode(",", $value);
							} else {
								$order = wc_get_order($order_id);
								$value = $order->get_meta($name, true);
							}
						} else {

							$order = wc_get_order($order_id);
							$value = $order->get_meta($name, true);
						}

						if (!empty($value)) {
							$label = isset($options['label']) && !empty($options['label']) ? __($options['label'], 'jwcfe') : $name;
							$fields_html .= '<p><strong>' . __($label, 'jwcfe') . ':</strong> <br/>' . $value . '</p>';
						}
					}
				} //end of fields loop

				$allowtags = array('h2' => array(), 'table' => array(), 'tr' => array(), 'td' => array(), 'strong' => array(), 'br' => array(), 'th' => array(), 'p' => array());
				echo wp_kses($fields_html, $allowtags);
			}
		}

		/**
		 * Display custom checkout fields on view order pages
		 *
		 * @param  object $order
		 */

		public function jwcfe_order_details_after_customer_details_lite($order)
		{

			if (JWCFE_Helper::jwcfe_woocommerce_version_check()) {
				$order_id = $order->get_id();
			} else {
				$order_id = $order->id;
			}
			$fields = array();

			if (!wc_ship_to_billing_address_only() && $order->needs_shipping_address()) {
				if (get_option('jwcfe_account_sync_fields') && get_option('jwcfe_account_sync_fields') == "on") {
					$fields = array_merge(
						JWCFE_Helper::get_fields('account'),
						JWCFE_Helper::get_fields('billing'),
						JWCFE_Helper::get_fields('shipping'),
						JWCFE_Helper::get_fields('additional')
					);
				} else {
					$fields = array_merge(JWCFE_Helper::get_fields('billing'), JWCFE_Helper::get_fields('shipping'), JWCFE_Helper::get_fields('additional'));
				}
			} else {
				if (get_option('jwcfe_account_sync_fields') && get_option('jwcfe_account_sync_fields') == "on") {
					$fields = array_merge(JWCFE_Helper::get_fields('account'), JWCFE_Helper::get_fields('billing'), JWCFE_Helper::get_fields('additional'));
				} else {
					$fields = array_merge(JWCFE_Helper::get_fields('billing'), JWCFE_Helper::get_fields('additional'));
				}
			}


			if (is_array($fields) && !empty($fields)) {
				$fields_html = '';
				// Loop through all custom fields to see if it should be added
				foreach ($fields as $name => $options) {
					$enabled = (isset($options['enabled']) && $options['enabled'] == false ? false : true);
					$is_custom_field = (isset($options['custom']) && $options['custom'] == true ? true : false);

					if (isset($options['show_in_order']) && $options['show_in_order'] && $enabled && $is_custom_field) {

						if ($options['type'] == 'select' || $options['type'] == 'checkboxgroup' || $options['type'] == 'timepicker' || $options['type'] == 'multiselect') {

							$order = wc_get_order($order_id);
							$value = $order->get_meta($name, true);

							if (is_array($value)) {
								$value = implode(",", $value);
							} else {

								$order = wc_get_order($order_id);
								$value = $order->get_meta($name, true);
							}
						} else {

							$order = wc_get_order($order_id);
							$value = $order->get_meta($name, true);
						}

						if (!empty($value)) {
							$label = (isset($options['label']) && !empty($options['label']) ? __($options['label'], 'jwcfe') : $name);

							if (is_account_page()) {

								if (apply_filters('jwcfe_view_order_customer_details_table_view', true)) {
									$fields_html .= '<tr><th class="custom-th">' . esc_attr($label) . ':</th><td class="custom-td">' . wptexturize($value) . '</td></tr>';
								} else {
									$fields_html .= '<br/><dt>' . esc_attr($label) . ':</dt><dd>' . wptexturize($value) . '</dd>';
								}
							} else {

								if (apply_filters('jwcfe_thankyou_customer_details_table_view', true)) {

									$fields_html .= '<tr><th class="custom-th">' . esc_attr($label) . ':</th><td class="custom-td">' . wptexturize($value) . '</td></tr>';
								} else {
									$fields_html .= '<br/><dt>' . esc_attr($label) . ':</dt><dd>' . wptexturize($value) . '</dd>';
								}
							}
						}
					}
				}

				if ($fields_html) {
					do_action('jwcfe_order_details_before_custom_fields_table', $order);
					?>
					<table class="woocommerce-table woocommerce-table--custom-fields shop_table custom-fields" style="	border: 1px solid hsla(0, 0%, 7%, .11);
							border-radius: 4px;
							  border-spacing: 0 ;
							  width: 100%;">
						<?php
						echo $fields_html;
						?>
					</table>
					<?php
					do_action('jwcfe_order_details_after_custom_fields_table', $order);
				}
			}
		}




		public function is_reserved_field_name($field_name)
		{
			if (
				$field_name && in_array($field_name, array(

					'billing_first_name',
					'billing_last_name',
					'billing_company',
					'billing_address_1',
					'billing_address_2',
					'billing_city',
					'billing_state',

					'billing_country',
					'billing_postcode',
					'billing_phone',
					'billing_email',

					'shipping_first_name',
					'shipping_last_name',
					'shipping_company',
					'shipping_address_1',
					'shipping_address_2',
					'shipping_city',
					'shipping_state',

					'shipping_country',
					'shipping_postcode',
					'customer_note',
					'order_comments',

					'account_username',
					'account_password'

				))
			) {

				return true;
			}
			return false;
		}

		function is_default_field_name($field_name)
		{

			if (
				$field_name && in_array($field_name, array(

					'billing_first_name',
					'billing_last_name',
					'billing_company',
					'billing_address_1',
					'billing_address_2',
					'billing_city',
					'billing_state',

					'billing_country',
					'billing_postcode',
					'billing_phone',
					'billing_email',

					'shipping_first_name',
					'shipping_last_name',
					'shipping_company',
					'shipping_address_1',
					'shipping_address_2',
					'shipping_city',
					'shipping_state',

					'shipping_country',
					'shipping_postcode',
					'customer_note',
					'order_comments',

					'account_username',
					'account_password'

				))
			) {
				return true;
			}
			return false;
		}

	}

endif;