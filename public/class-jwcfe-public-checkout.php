<?php
if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . '../includes/class-jwcfe-helper.php';

/**
 * WC_Checkout_Field_Editor checkout class.
 */

if (!class_exists('JWCFE_Public_Checkout')) :

	class JWCFE_Public_Checkout
	{
		private $plugin_name;
		private $version;
		private $rules;

		/**
		 * __construct function.

		 */

		public function __construct($plugin_name, $version)
		{
			$this->plugin_name = $plugin_name;
			$this->version = $version;
	
		}


		public function define_public_checkout_hooks()
		{
			add_filter('woocommerce_enable_order_notes_field', array($this, 'jwcfe_enable_order_notes_field'), 1001);
			add_filter('woocommerce_get_country_locale_base', array($this, 'jwcfe_prepare_country_locale'));
			add_filter('woocommerce_get_country_locale', array($this, 'jwcfe_woo_get_country_locale'));

			add_filter('woocommerce_billing_fields', array($this, 'jwcfe_billing_fields_lite_paid'), 1001, 2);
			add_filter('woocommerce_shipping_fields', array($this, 'jwcfe_shipping_fields_lite'), 1001, 2);
			add_filter('woocommerce_checkout_fields', array($this, 'jwcfe_checkout_fields_lite'), apply_filters('jwcfe_checkout_fields_priority', 1000));
			add_filter('woocommerce_default_address_fields', array($this, 'jwcfe_woo_default_address_fields'));
			add_action('woocommerce_checkout_update_order_meta', array($this, 'save_data'), 10, 2);
			

			add_action('woocommerce_after_checkout_validation', array($this, 'jwcfe_check_field_validations'), 10, 4);
			add_action('woocommerce_email_order_meta', array($this, 'jwcfe_display_custom_fields_in_emails_lite'), 10, 3);


			add_filter('woocommerce_form_field_checkbox', array($this, 'jwcfe_checkout_form_field'), 10, 4);
			add_filter('woocommerce_form_field_checkboxgroup', array($this, 'jwcfe_checkout_form_field'), 10, 4);
			add_filter('woocommerce_form_field_month', array($this, 'jwcfe_checkout_form_field'), 10, 4);
			add_filter('woocommerce_form_field_week', array($this, 'jwcfe_checkout_form_field'), 10, 4);
			add_filter('woocommerce_form_field_multiselect', array($this, 'jwcfe_checkout_form_field'), 10, 4);
			add_filter('woocommerce_form_field_date', array($this, 'jwcfe_checkout_form_field'), 10, 4);
			add_filter('woocommerce_form_field_textarea', array($this, 'jwcfe_checkout_form_field'), 10, 4);
			add_filter('woocommerce_form_field_text', array($this, 'jwcfe_checkout_form_field'), 10, 4);
			add_filter('woocommerce_form_field_email', array($this, 'jwcfe_checkout_form_field'), 10, 4);
			add_filter('woocommerce_form_field_phone', array($this, 'jwcfe_checkout_form_field'), 10, 4);
			add_filter('woocommerce_form_field_select', array($this, 'jwcfe_checkout_form_field'), 10, 4);
			add_filter('woocommerce_form_field_radio', array($this, 'jwcfe_checkout_form_field'), 10, 4);
			add_filter('woocommerce_form_field_timepicker', array($this, 'jwcfe_checkout_form_field'), 10, 4);
			add_filter('woocommerce_form_field_number', array($this, 'jwcfe_checkout_form_field'), 10, 4);
			add_filter('woocommerce_form_field_hidden', array($this, 'jwcfe_checkout_form_field'), 10, 4);
			add_filter('woocommerce_form_field_datetime-local', array($this, 'jwcfe_checkout_form_field'), 10, 4);
			add_filter('woocommerce_form_field_url', array($this, 'jwcfe_checkout_form_field'), 10, 4);


			add_filter('woocommerce_form_field_heading', array($this, 'jwcfe_checkout_fields_heading_field'), 10, 4);
			add_filter('woocommerce_form_field_customcontent', array($this, 'jwcfe_checkout_fields_customcontent_field'), 10, 4);
			
			add_filter('woocommerce_form_field_paragraph', array($this, 'jwcfe_checkout_fields_pro_paragraph_field'), 10, 4);
			add_action('wp_enqueue_scripts', array($this, 'jwcfe_enqueue_scripts'), 10, 4);
			add_action('woocommerce_init', array($this, 'jwcfe_load_address_blocks'), 10);
			add_action('woocommerce_init', array($this, 'jwcfe_register_checkout_fields'), 20);
			add_action('woocommerce_init', array($this, 'jwcfe_register_all_block_field_default_filters'), 25);
			add_filter('rest_post_dispatch', array($this, 'jwcfe_apply_block_defaults_to_checkout_api_response'), 10, 3);
			add_action('woocommerce_blocks_checkout_enqueue_data', array($this, 'jwcfe_enqueue_block_checkout_defaults'));
			
		}
		
		function jwcfe_register_checkout_fields()
		{
			$sections = ['billing', 'additional']; // shipping is handled by jwcfe_register_additional_address_fields()

			foreach ($sections as $section) {
				$option_name = 'jwcfe_wc_fields_block_' . $section;
				$saved_fields = maybe_unserialize(get_option($option_name, []));

				if (!is_array($saved_fields)) {
					$saved_fields = [];
				}

				if (!empty($saved_fields)) {
					foreach ($saved_fields as $field_id => $field_data) {
						if (empty($field_data['enabled'])) continue;

						// Remove any existing section prefix (billing_, shipping_, additional_)
						$clean_field_id = preg_replace('/^(billing|shipping|additional)_/', '', $field_id);

						// Ensure the correct namespace
						$field_id_formatted = "{$section}/{$clean_field_id}";

						// Shipping custom fields are handled by jwcfe_register_additional_address_fields()
						// to avoid double registration, skip shipping section here
						if ( $section === 'shipping' ) {
							continue 2;
						}

						$location = ($section === 'billing') ? 'contact' : 'order';

						// Apply conditional logic
						if (isset($field_data['rules_action']) && !empty($field_data['rules_action'])) {
							if (!empty($field_data['rules'])) {
								$rulesArr = json_decode(urldecode($field_data['rules']), true);

								foreach ($rulesArr[0][0] as $key => $singleRule) {
									$operator   = $singleRule[0]['operator'];
									$operand    = $singleRule[0]['operand'];

									// Check for user role operators
									if (in_array($operator, ['user_role_eq', 'user_role_ne'])) {
										$userObj        = wp_get_current_user();
										$arrayCompare   = array_intersect((array) $userObj->roles, (array)$operand);

										if ($field_data['rules_action'] == 'show' && (empty($arrayCompare) || ($operator == 'user_role_ne' && !empty($arrayCompare)))) {
											continue; // Show field
										} elseif ($field_data['rules_action'] == 'hide' && !empty($arrayCompare) || ($operator == 'user_role_ne' && empty($arrayCompare))) {
											continue 2; // Skip this field
										}
									}

									if (empty($singleRule[0]['operand_type']) && strstr($operator, 'total')) {
										if (!is_object($this->rules)) continue;
										$cart_check_result = $this->rules->jwcfe_check_cart_total($operator, $operand);
										if ((!$cart_check_result && $field_data['rules_action'] == 'show') || ($cart_check_result && $field_data['rules_action'] == 'hide')) {
											continue 2; // Skip this field
										}
									}

									if ($singleRule[0]['operand_type'] == 'product') {
										if (!is_object($this->rules)) continue;
										$operand = isset($singleRule[0]['operand'][0]) ? $singleRule[0]['operand'][0] : null;
										$operand_variation = isset($singleRule[0]['operand_variation'][0]) ? $singleRule[0]['operand_variation'][0] : null;
										if (($field_data['rules_action'] == 'hide' && $operator == 'cart_contains' && $this->rules->jwcfe_check_product_in_cart($operand)) ||
											($field_data['rules_action'] == 'hide' && $operator == 'cart_not_contains' && !$this->rules->jwcfe_check_product_in_cart($operand)) ||
											($field_data['rules_action'] == 'hide' && $operator == 'cart_only_contains' && !$this->rules->jwcfe_check_product_in_cart($operand))
										) {
											continue 2; // Skip this field
										} elseif (($field_data['rules_action'] == 'show' && $operator == 'cart_not_contains' && $this->rules->jwcfe_check_product_in_cart($operand)) ||
											($field_data['rules_action'] == 'show' && $operator == 'cart_contains' && !$this->rules->jwcfe_check_product_in_cart($operand)) ||
											($field_data['rules_action'] == 'show' && $operator == 'cart_only_contains' && !$this->rules->jwcfe_check_product_in_cart($operand))
										) {
											continue 2; // Skip this field
										}
									}

									if ($singleRule[0]['operand_type'] == 'category') {
										if (!is_object($this->rules)) continue;
										$operand         = isset($singleRule[0]['operand'][0]) ? $singleRule[0]['operand'][0] : null;
										$categoryInCart = $this->rules->jwcfe_check_category_in_cart($operand);

										if ($field_data['rules_action'] == 'hide') {
											if ($operator == 'cart_contains' && $categoryInCart) {
												continue 2; // Skip this field
											} elseif ($operator == 'cart_not_contains' && !$categoryInCart) {
												continue 2; // Skip this field
											} elseif ($operator == 'cart_only_contains' && $categoryInCart) {
												continue 2; // Skip this field
											}
										} elseif ($field_data['rules_action'] == 'show') {
											if ($operator == 'cart_not_contains' && $categoryInCart) {
												continue 2; // Skip this field
											} elseif ($operator == 'cart_contains' && !$categoryInCart) {
												continue 2; // Skip this field
											} elseif ($operator == 'cart_only_contains' && !$categoryInCart) {
												continue 2; // Skip this field
											}
										}
									}
								}
							}
						}

						$_label           = $field_data['label'] ?? ucfirst($clean_field_id);
						$field_attributes = [
							'id'              => $field_id_formatted,
							'label'           => $_label,
							'optionalLabel'   => !empty($field_data['optionalLabel'])
								? $field_data['optionalLabel']
								: sprintf( __( '%s (optional)', 'woocommerce' ), $_label ),
							'location'        => $location,
							'required'        => !empty($field_data['required']),
							'placeholder'     => $field_data['placeholder'] ?? '',
							'show_in_order'   => true,
							'show_in_email'   => true,
						];

						$this->jwcfe_apply_block_field_help_text_attributes( $field_attributes, $field_data );

						$field_type = $field_data['type'] ?? 'text';

						switch ($field_type) {
							case 'text':
								if (!empty($field_data['validate'])) {
									if (in_array('email', $field_data['validate'], true)) {
										// Apply email validation
										$field_attributes['validate_callback'] = function ($field_value) use ($field_data, $clean_field_id) {
											if (!is_email($field_value)) {
												return new WP_Error(
													'invalid_email',
													'Please enter a valid email address for ' . ($field_data['label'] ?? ucfirst($clean_field_id)) . '.'
												);
											}
										};
									} elseif (in_array('phone', $field_data['validate'], true)) {
										// Apply phone validation
										$field_attributes['validate_callback'] = function ($field_value) use ($field_data, $clean_field_id) {
											if (!preg_match('/^\+?[0-9]{10,15}$/', $field_value)) {
												return new WP_Error(
													'invalid_phone',
													'Please enter a valid phone number for ' . ($field_data['label'] ?? ucfirst($clean_field_id)) . '.'
												);
											}
										};
									}
								}

								if ( isset( $field_data['default'] ) && '' !== (string) $field_data['default'] ) {
									$this->jwcfe_register_block_field_default_filter( $field_id_formatted, $field_data['default'] );
								}

								woocommerce_register_additional_checkout_field($field_attributes);
								break;

							case 'checkbox':
								if ( ! empty( $field_data['default'] ) ) {
									$checkbox_default = in_array( strtolower( (string) $field_data['default'] ), array( '1', 'yes', 'true', 'on' ), true ) ? '1' : '0';
									$this->jwcfe_register_block_field_default_filter( $field_id_formatted, $checkbox_default );
								}

								woocommerce_register_additional_checkout_field([
									'type'     => 'checkbox',
									'id'       => $field_id_formatted,
									'label'    => $field_data['label'] ?? ucfirst($clean_field_id),
									'location' => $location,
								]);
								break;

							case 'select':
								if (!empty($field_data['options_json'])) {
									$options = [];
									$opts_source = $field_data['options_json'];

									// support if options_json is a JSON string or urlencoded JSON
									if (is_string($opts_source)) {
										$decoded = json_decode(urldecode($opts_source), true);
										if ($decoded !== null) {
											$opts_source = $decoded;
										} else {
											$decoded2 = json_decode($opts_source, true);
											if ($decoded2 !== null) {
												$opts_source = $decoded2;
											}
										}
									}

									if (is_array($opts_source)) {
										foreach ($opts_source as $option) {
											if (isset($option['key'], $option['text'])) {
												$options[] = [
													'value' => $option['key'],
													'label' => $option['text'],
												];
											} elseif (isset($option['value'], $option['label'])) {
												$options[] = [
													'value' => $option['value'],
													'label' => $option['label'],
												];
											} elseif (is_string($option)) {
												$options[] = [
													'value' => $option,
													'label' => $option,
												];
											}
										}
									}

									if ($options) {
										$field_attributes['type'] = 'select';
										$field_attributes['options'] = $options;
										$default = $this->jwcfe_get_block_select_default( $field_data, $options );
										if ( null !== $default ) {
											$this->jwcfe_register_block_field_default_filter( $field_id_formatted, $default );
										}
										woocommerce_register_additional_checkout_field($field_attributes);
									}
								}
								break;

						case 'radio':
			// Parse options for radio (supports multiple shapes)
			if (!empty($field_data['options_json'])) {
				$options = [];
				$opts_source = $field_data['options_json'];

				// support urlencoded JSON string or plain JSON string
				if (is_string($opts_source)) {
					$decoded = json_decode(urldecode($opts_source), true);
					if ($decoded !== null) {
						$opts_source = $decoded;
					} else {
						$decoded2 = json_decode($opts_source, true);
						if ($decoded2 !== null) {
							$opts_source = $decoded2;
						}
					}
				}

				if (is_array($opts_source)) {
					foreach ($opts_source as $option) {
						if (is_array($option) && isset($option['key'], $option['text'])) {
							$options[] = [
								'value' => $option['key'],
								'label' => $option['text'],
							];
						} elseif (is_array($option) && isset($option['value'], $option['label'])) {
							$options[] = [
								'value' => $option['value'],
								'label' => $option['label'],
							];
						} elseif (is_string($option)) {
							$options[] = [
								'value' => $option,
								'label' => $option,
							];
						}
					}
				}

				if (!empty($options)) {
					// Register as a SELECT so Blocks will render it.
					// We'll convert the select -> radio UI on the frontend with JS.
					$field_attributes['type'] = 'select';
					$field_attributes['options'] = $options;

					// Mark it so our frontend script can detect this should be radios
					// Use attributes -> data-jwcfe-type to pass through to DOM elements.
					$field_attributes['attributes'] = isset($field_attributes['attributes']) && is_array($field_attributes['attributes']) ? $field_attributes['attributes'] : [];
					$field_attributes['attributes']['data-jwcfe-type'] = 'radio';

					$default = $this->jwcfe_get_block_select_default( $field_data, $options );
					if ( null !== $default ) {
						$this->jwcfe_register_block_field_default_filter( $field_id_formatted, $default );
					}

					// context mapping as before
					$field_attributes['context'] = isset($field_data['context']) ? $field_data['context'] : $location;
					// ✅ Add custom class support
			
			
					woocommerce_register_additional_checkout_field($field_attributes);
				}
			}
			break;

							default:
								// error_log("Unsupported field type: " . $field_type);
						}
					}
				}
			}
		}

		/**
		 * Update default fields data for Block Checkout (address/shipping section)
		 * Replicating ThemeHigh behavior to sync core fields settings.
		 */
		/***************************************************
		 ******* ADDRESS SECTION FUNCTIONALITY - START *****
		 ***************************************************/

		/**
		 * Load address block hooks - called on woocommerce_init
		 * Requires WooCommerce 8.8.0+
		 */
		public function jwcfe_load_address_blocks() {

			if ( version_compare( $this->jwcfe_get_wc_version(), '8.8.0', '<' ) ) {
				return;
			}

			// Register custom address fields (non-default ones added by admin)
			$this->jwcfe_register_additional_address_fields();

			// Update default fields in Block Registry (label, required, priority, hidden)
			add_action( 'woocommerce_blocks_checkout_block_registration', array( $this, 'jwcfe_update_default_fields_with_block' ), 999 );

			// Validate custom address fields (email, phone, postcode)
			add_action( 'woocommerce_validate_additional_field', array( $this, 'jwcfe_validate_additional_address_field' ), 10, 3 );

			// Update country locale (required/hidden) for address_1, postcode, city, state
			add_filter( 'woocommerce_get_country_locale', array( $this, 'jwcfe_update_address_locale_fields' ), 999 );

			// Update woocommerce_default_address_fields filter (only when block checkout is active)
			if ( $this->jwcfe_has_block_checkout() ) {
				add_filter( 'woocommerce_default_address_fields', array( $this, 'jwcfe_update_default_address_fields' ), 999 );
			}
		}

		/**
		 * Check if block checkout is active on the checkout page
		 */
		private function jwcfe_has_block_checkout() {
			$checkout_page_id   = wc_get_page_id( 'checkout' );
			$has_block_checkout = $checkout_page_id && has_block( 'woocommerce/checkout', $checkout_page_id );
			return $has_block_checkout || apply_filters( 'jwcfe_woocommerce_blocks_has_block_checkout', false );
		}

		/**
		 * Get WooCommerce version
		 */
		private function jwcfe_get_wc_version() {
			if ( defined( 'WC_VERSION' ) ) {
				return WC_VERSION;
			}
			if ( class_exists( 'WooCommerce' ) ) {
				global $woocommerce;
				return isset( $woocommerce->version ) ? $woocommerce->version : '0';
			}
			return '0';
		}

		/**
		 * Get default core address fields (matches WooCommerce block defaults)
		 */
		private function jwcfe_get_core_address_fields() {
			return [
				'email'      => [ 'label' => __( 'Email address', 'woocommerce' ), 'optionalLabel' => __( 'Email address (optional)', 'woocommerce' ), 'required' => true,  'hidden' => false, 'autocomplete' => 'email',          'autocapitalize' => 'none',      'type'  => 'email', 'index' => 0   ],
				'country'    => [ 'label' => __( 'Country/Region', 'woocommerce' ), 'optionalLabel' => __( 'Country/Region (optional)', 'woocommerce' ), 'required' => true,  'hidden' => false, 'autocomplete' => 'country',         'index' => 1   ],
				'first_name' => [ 'label' => __( 'First name', 'woocommerce' ),     'optionalLabel' => __( 'First name (optional)', 'woocommerce' ),     'required' => true,  'hidden' => false, 'autocomplete' => 'given-name',      'autocapitalize' => 'sentences', 'index' => 10  ],
				'last_name'  => [ 'label' => __( 'Last name', 'woocommerce' ),      'optionalLabel' => __( 'Last name (optional)', 'woocommerce' ),      'required' => true,  'hidden' => false, 'autocomplete' => 'family-name',     'autocapitalize' => 'sentences', 'index' => 20  ],
				'company'    => [ 'label' => __( 'Company', 'woocommerce' ),        'optionalLabel' => __( 'Company (optional)', 'woocommerce' ),        'required' => false, 'hidden' => false, 'autocomplete' => 'organization',    'autocapitalize' => 'sentences', 'index' => 30  ],
				'address_1'  => [ 'label' => __( 'Address', 'woocommerce' ),        'optionalLabel' => __( 'Address (optional)', 'woocommerce' ),        'required' => true,  'hidden' => false, 'autocomplete' => 'address-line1',   'autocapitalize' => 'sentences', 'index' => 40  ],
				'address_2'  => [ 'label' => __( 'Apartment, suite, etc.', 'woocommerce' ), 'optionalLabel' => __( 'Apartment, suite, etc. (optional)', 'woocommerce' ), 'required' => false, 'hidden' => false, 'autocomplete' => 'address-line2', 'autocapitalize' => 'sentences', 'index' => 50 ],
				'city'       => [ 'label' => __( 'City', 'woocommerce' ),           'optionalLabel' => __( 'City (optional)', 'woocommerce' ),           'required' => true,  'hidden' => false, 'autocomplete' => 'address-level2',  'autocapitalize' => 'sentences', 'index' => 70  ],
				'state'      => [ 'label' => __( 'State/County', 'woocommerce' ),   'optionalLabel' => __( 'State/County (optional)', 'woocommerce' ),   'required' => true,  'hidden' => false, 'autocomplete' => 'address-level1',  'autocapitalize' => 'sentences', 'index' => 80  ],
				'postcode'   => [ 'label' => __( 'Postal code', 'woocommerce' ),    'optionalLabel' => __( 'Postal code (optional)', 'woocommerce' ),    'required' => true,  'hidden' => false, 'autocomplete' => 'postal-code',     'autocapitalize' => 'characters', 'index' => 90  ],
				'phone'      => [ 'label' => __( 'Phone', 'woocommerce' ),          'optionalLabel' => __( 'Phone (optional)', 'woocommerce' ),          'required' => false, 'hidden' => false, 'autocomplete' => 'tel',             'autocapitalize' => 'characters', 'type' => 'tel', 'index' => 100 ],
			];
		}

		/**
		 * Get saved shipping fields from DB (these are the admin-configured address fields)
		 */
		private function jwcfe_get_saved_address_field_set() {
			$saved = maybe_unserialize( get_option( 'jwcfe_wc_fields_block_shipping', [] ) );
			if ( ! is_array( $saved ) ) {
				$saved = [];
			}
			return $saved;
		}

		/**
		 * Default for block checkout select fields.
		 * Blocks adds a blank first <option> when no default is set; use the first real option unless a placeholder is configured.
		 *
		 * @param array $field_data Saved field config.
		 * @param array $options    Normalized options (value/label pairs).
		 * @return string|null Default value, or null to leave unset (placeholder prompt).
		 */
		private function jwcfe_get_block_select_default( $field_data, $options ) {
			if ( ! empty( $field_data['default'] ) ) {
				return (string) $field_data['default'];
			}

			if ( ! empty( $field_data['placeholder'] ) ) {
				return null;
			}

			if ( ! empty( $options[0]['value'] ) ) {
				return (string) $options[0]['value'];
			}

			return null;
		}

		/**
		 * Register WooCommerce block checkout default value for a field.
		 * Block checkout reads defaults via woocommerce_get_default_value_for_{$field_id}, not registration args.
		 *
		 * @param string $field_id      Full field id (e.g. billing/my-field or jwcfe-block/my-field).
		 * @param string $default_value Default value from plugin settings.
		 */
		/**
		 * Pass Description / Help Text to block checkout field attributes for frontend display.
		 *
		 * @param array $field_attributes Registration args (passed by reference).
		 * @param array $field_data     Saved field config from admin.
		 */
		private function jwcfe_apply_block_field_help_text_attributes( array &$field_attributes, array $field_data ) {
			if ( empty( $field_data['text'] ) ) {
				return;
			}

			$help_text = trim( wp_strip_all_tags( (string) $field_data['text'] ) );

			if ( '' === $help_text ) {
				return;
			}

			if ( ! isset( $field_attributes['attributes'] ) || ! is_array( $field_attributes['attributes'] ) ) {
				$field_attributes['attributes'] = array();
			}

			$field_attributes['attributes']['data-jwcfe-description'] = $help_text;
		}

		private function jwcfe_register_block_field_default_filter( $field_id, $default_value ) {
			if ( null === $default_value || ( '' === (string) $default_value && '0' !== $default_value ) ) {
				return;
			}

			$default_value = (string) $default_value;

			add_filter(
				'woocommerce_get_default_value_for_' . $field_id,
				static function ( $value, $group, $wc_object ) use ( $default_value ) {
					unset( $group, $wc_object );

					if ( null === $value || '' === $value ) {
						return $default_value;
					}

					return $value;
				},
				10,
				3
			);
		}

		/**
		 * Parse options_json from saved field config (same shapes as block registration).
		 *
		 * @param array $field_data Saved field row.
		 * @return array Normalized options with value/label keys.
		 */
		private function jwcfe_parse_block_field_options( $field_data ) {
			$options = array();

			if ( empty( $field_data['options_json'] ) ) {
				return $options;
			}

			$opts_source = $field_data['options_json'];

			if ( is_string( $opts_source ) ) {
				$decoded = json_decode( urldecode( $opts_source ), true );
				if ( null !== $decoded ) {
					$opts_source = $decoded;
				} else {
					$decoded2 = json_decode( $opts_source, true );
					if ( null !== $decoded2 ) {
						$opts_source = $decoded2;
					}
				}
			}

			if ( ! is_array( $opts_source ) ) {
				return $options;
			}

			foreach ( $opts_source as $option ) {
				if ( is_array( $option ) && isset( $option['key'], $option['text'] ) ) {
					$options[] = array(
						'value' => $option['key'],
						'label' => $option['text'],
					);
				} elseif ( is_array( $option ) && isset( $option['value'], $option['label'] ) ) {
					$options[] = array(
						'value' => $option['value'],
						'label' => $option['label'],
					);
				} elseif ( is_string( $option ) ) {
					$options[] = array(
						'value' => $option,
						'label' => $option,
					);
				}
			}

			return $options;
		}

		/**
		 * Collect default values for all enabled block checkout fields from saved options.
		 *
		 * @return array<string, string> Field id => default value.
		 */
		private function jwcfe_collect_block_field_defaults() {
			$defaults = array();

			foreach ( array( 'billing', 'additional' ) as $section ) {
				$saved_fields = maybe_unserialize( get_option( 'jwcfe_wc_fields_block_' . $section, array() ) );

				if ( ! is_array( $saved_fields ) ) {
					continue;
				}

				foreach ( $saved_fields as $field_id => $field_data ) {
					if ( empty( $field_data['enabled'] ) ) {
						continue;
					}

					$clean_field_id = preg_replace( '/^(billing|shipping|additional)_/', '', $field_id );
					$block_field_id = $section . '/' . $clean_field_id;
					$field_type       = isset( $field_data['type'] ) ? $field_data['type'] : 'text';

					if ( in_array( $field_type, array( 'select', 'radio' ), true ) ) {
						$options = $this->jwcfe_parse_block_field_options( $field_data );
						$value   = $this->jwcfe_get_block_select_default( $field_data, $options );
					} elseif ( 'checkbox' === $field_type ) {
						$value = ! empty( $field_data['default'] ) && in_array(
							strtolower( (string) $field_data['default'] ),
							array( '1', 'yes', 'true', 'on' ),
							true
						) ? '1' : null;
					} elseif ( isset( $field_data['default'] ) && '' !== (string) $field_data['default'] ) {
						$value = (string) $field_data['default'];
					} else {
						$value = null;
					}

					if ( null !== $value && '' !== (string) $value ) {
						$defaults[ $block_field_id ] = (string) $value;
					}
				}
			}

			$saved_address_fields = $this->jwcfe_get_saved_address_field_set();
			$core_fields          = $this->jwcfe_get_core_address_fields();

			if ( is_array( $saved_address_fields ) ) {
				foreach ( $saved_address_fields as $field_id => $field_data ) {
					if ( empty( $field_data['enabled'] ) ) {
						continue;
					}

					$clean_key = preg_replace( '/^(billing|shipping|additional)_/', '', $field_id );

					if ( isset( $core_fields[ $clean_key ] ) ) {
						continue;
					}

					$block_field_id = 'jwcfe-block/' . $clean_key;
					$type           = isset( $field_data['type'] ) ? $field_data['type'] : 'text';

					if ( in_array( $type, array( 'select', 'radio' ), true ) ) {
						$options = $this->jwcfe_parse_block_field_options( $field_data );
						$value   = $this->jwcfe_get_block_select_default( $field_data, $options );
					} elseif ( isset( $field_data['default'] ) && '' !== (string) $field_data['default'] ) {
						$value = (string) $field_data['default'];
					} else {
						$value = null;
					}

					if ( null !== $value && '' !== (string) $value ) {
						$defaults[ $block_field_id ] = (string) $value;
					}
				}
			}

			return $defaults;
		}

		/**
		 * Register default-value filters for every saved block field (covers fields skipped during registration).
		 */
		public function jwcfe_register_all_block_field_default_filters() {
			if ( ! function_exists( 'woocommerce_register_additional_checkout_field' ) ) {
				return;
			}

			foreach ( $this->jwcfe_collect_block_field_defaults() as $field_id => $default_value ) {
				$this->jwcfe_register_block_field_default_filter( $field_id, $default_value );
			}
		}

		/**
		 * Merge plugin defaults into Store API checkout response (draft orders can store empty meta).
		 *
		 * @param WP_REST_Response $response REST response.
		 * @param WP_REST_Server   $server   REST server.
		 * @param WP_REST_Request  $request  REST request.
		 * @return WP_REST_Response
		 */
		public function jwcfe_apply_block_defaults_to_checkout_api_response( $response, $server, $request ) {
			unset( $server );

			if ( ! $request instanceof \WP_REST_Request || 'GET' !== $request->get_method() ) {
				return $response;
			}

			$route = $request->get_route();

			if ( false === strpos( $route, '/wc/store/v1/checkout' ) ) {
				return $response;
			}

			$data = $response->get_data();

			if ( empty( $data['additional_fields'] ) || ! is_array( $data['additional_fields'] ) ) {
				$data['additional_fields'] = array();
			}

			foreach ( $this->jwcfe_collect_block_field_defaults() as $field_id => $default_value ) {
				if ( ! isset( $data['additional_fields'][ $field_id ] ) || '' === (string) $data['additional_fields'][ $field_id ] ) {
					$data['additional_fields'][ $field_id ] = $default_value;
				}
			}

			$response->set_data( $data );

			return $response;
		}

		/**
		 * Pass block field defaults to the checkout block and apply them in the client store.
		 */
		public function jwcfe_enqueue_block_checkout_defaults() {
			if ( ! $this->jwcfe_has_block_checkout() ) {
				return;
			}

			$defaults = $this->jwcfe_collect_block_field_defaults();

			if ( empty( $defaults ) ) {
				return;
			}

			if (
				class_exists( '\Automattic\WooCommerce\Blocks\Package' ) &&
				class_exists( '\Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry' )
			) {
				try {
					$container = \Automattic\WooCommerce\Blocks\Package::container();
					$registry  = $container->get( \Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry::class );
					$registry->add( 'jwcfeBlockFieldDefaults', $defaults );
				} catch ( \Exception $e ) {
					// Asset registry not available yet; JS still receives defaults via wp_localize_script below.
				}
			}

			wp_register_script(
				'jwcfe-block-checkout-defaults',
				plugin_dir_url( __FILE__ ) . 'assets/js/jwcfe-block-checkout-defaults.js',
				array( 'wc-blocks-checkout', 'wp-data' ),
				$this->version,
				true
			);
			wp_localize_script( 'jwcfe-block-checkout-defaults', 'jwcfeBlockFieldDefaults', $defaults );
			wp_enqueue_script( 'jwcfe-block-checkout-defaults' );
		}

		/**
		 * Register custom (non-default) address fields in WooCommerce Block Checkout
		 * Same as THWCFD_Block::register_additional_address_fields()
		 */
		public function jwcfe_register_additional_address_fields() {

			if ( ! function_exists( 'woocommerce_register_additional_checkout_field' ) ) {
				return;
			}

			$saved_fields    = $this->jwcfe_get_saved_address_field_set();
			$core_fields     = $this->jwcfe_get_core_address_fields();
			$remove_optional = apply_filters( 'jwcfe_remove_optional_label', false );

			if ( empty( $saved_fields ) ) {
				return;
			}

			// Only register fields that are NOT default WooCommerce address fields
			foreach ( $saved_fields as $field_id => $field_data ) {

				// Strip prefix e.g. shipping_custom_field -> custom_field
				$clean_key = preg_replace( '/^(billing|shipping|additional)_/', '', $field_id );

				// Skip if this is a default core field
				if ( isset( $core_fields[ $clean_key ] ) ) {
					continue;
				}

				// Skip disabled fields
				if ( isset( $field_data['enabled'] ) && ! $field_data['enabled'] ) {
					continue;
				}

				$label       = isset( $field_data['label'] ) ? $field_data['label'] : ucfirst( $clean_key );
				$type        = isset( $field_data['type'] ) ? $field_data['type'] : 'text';
				$required    = ! empty( $field_data['required'] );
				$placeholder = isset( $field_data['placeholder'] ) ? $field_data['placeholder'] : '';
				$priority    = isset( $field_data['order'] ) ? ( ( (int) $field_data['order'] + 1 ) * 10 ) : 100;

				// WooCommerce < 9.8.0 does not support required checkboxes
				if ( version_compare( $this->jwcfe_get_wc_version(), '9.8.0', '<' ) && $type === 'checkbox' ) {
					$required = false;
				}

				$optional_label = $remove_optional ? $label : sprintf(
					/* translators: %s Field label. */
					__( '%s (optional)', 'woocommerce' ), // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
					$label
				);

				// Build options for select/radio
				$options = [];
				if ( ! empty( $field_data['options_json'] ) ) {
					$opts_source = $field_data['options_json'];
					if ( is_string( $opts_source ) ) {
						$decoded = json_decode( urldecode( $opts_source ), true );
						if ( $decoded !== null ) {
							$opts_source = $decoded;
						}
					}
					if ( is_array( $opts_source ) ) {
						foreach ( $opts_source as $option ) {
							if ( isset( $option['key'], $option['text'] ) ) {
								$options[] = [ 'value' => $option['key'], 'label' => $option['text'] ];
							} elseif ( isset( $option['value'], $option['label'] ) ) {
								$options[] = [ 'value' => $option['value'], 'label' => $option['label'] ];
							}
						}
					}
				}

				// Select and radio both register as 'select' in blocks
				$register_type = ( $type === 'radio' ) ? 'select' : $type;

				$field_attrs = [];
				if ( $type === 'radio' ) {
					$field_attrs['data-jwcfe-type'] = 'radio';
				}

				$register_args = [
					'id'            => 'jwcfe-block/' . $clean_key,
					'label'         => $label,
					'optionalLabel' => $optional_label,
					'placeholder'   => $placeholder,
					'location'      => 'address',
					'type'          => $register_type,
					'required'      => $required,
					'index'         => $priority,
					'options'       => $options,
					'attributes'    => $field_attrs,
				];

				$this->jwcfe_apply_block_field_help_text_attributes( $register_args, $field_data );

				$block_field_id = 'jwcfe-block/' . $clean_key;

				if ( 'select' === $register_type && ! empty( $options ) ) {
					$default = $this->jwcfe_get_block_select_default( $field_data, $options );
					if ( null !== $default ) {
						$this->jwcfe_register_block_field_default_filter( $block_field_id, $default );
					}
				} elseif ( isset( $field_data['default'] ) && '' !== (string) $field_data['default'] ) {
					$this->jwcfe_register_block_field_default_filter( $block_field_id, $field_data['default'] );
				}

				woocommerce_register_additional_checkout_field( $register_args );
			}
		}

		/**
		 * Update default address fields in WooCommerce Block Registry
		 * (label, required, priority, hidden)
		 * Same as THWCFD_Block::update_default_fields_data_with_block()
		 */
		public function jwcfe_update_default_fields_with_block() {

			if (
				! class_exists( 'Automattic\WooCommerce\Blocks\Package' ) ||
				! class_exists( 'Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry' ) ||
				! class_exists( 'Automattic\WooCommerce\Blocks\Domain\Services\CheckoutFields' )
			) {
				return;
			}

			$change_default = apply_filters( 'jwcfe_change_default_block_address_fields', true );
			if ( ! $change_default ) {
				return;
			}

			try {
				$container           = \Automattic\WooCommerce\Blocks\Package::container();
				$checkout_fields     = $container->get( \Automattic\WooCommerce\Blocks\Domain\Services\CheckoutFields::class );
				$asset_data_registry = $container->get( \Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry::class );

				$default_fields   = $this->jwcfe_get_core_address_fields();
				$saved_fields     = $this->jwcfe_get_saved_address_field_set();
				$saved_billing    = maybe_unserialize( get_option( 'jwcfe_wc_fields_block_billing', [] ) );
				$remove_optional  = apply_filters( 'jwcfe_remove_optional_label', false );

				// Agar shipping fields reset ho gayi hain to default fields as-is use karo
				if ( empty( $saved_fields ) ) {
					$asset_data_registry->add(
						'defaultFields',
						array_merge( $default_fields, $checkout_fields->get_additional_fields() )
					);
					return;
				}

				foreach ( $default_fields as $key => &$field ) {

					// Email is controlled by billing section
					if ( $key === 'email' ) {
						if ( is_array( $saved_billing ) && isset( $saved_billing['billing_email'] ) ) {
							$billing_email = $saved_billing['billing_email'];
							if ( ! empty( $billing_email['label'] ) ) {
								$field['label']         = $billing_email['label'];
								$field['optionalLabel'] = $remove_optional ? $billing_email['label'] : sprintf( __( '%s (optional)', 'woocommerce' ), $billing_email['label'] ); // phpcs:ignore
							}
							$field['required'] = ! empty( $billing_email['required'] );
							if ( isset( $billing_email['order'] ) && $billing_email['order'] !== '' ) {
								$field['index'] = ( (int) $billing_email['order'] + 1 ) * 10;
							}
						}
						continue;
					}

					// Match shipping_<key> from saved fields
					$shipping_key = 'shipping_' . $key;

					if ( isset( $saved_fields[ $shipping_key ] ) ) {
						$field_data = $saved_fields[ $shipping_key ];

						// Skip if field is disabled -> mark hidden
						if ( isset( $field_data['enabled'] ) && ! $field_data['enabled'] ) {
							$field['hidden'] = true;
							continue;
						}

						// Update index/priority
						if ( isset( $field_data['order'] ) && $field_data['order'] !== '' ) {
							$field['index'] = ( (int) $field_data['order'] + 1 ) * 10;
						}

						// Update label
						if ( apply_filters( 'jwcfe_block_address_field_dynamic_label', true ) ) {
							if ( ! empty( $field_data['label'] ) ) {
								$field['label'] = $field_data['label'];
								if ( $remove_optional ) {
									$field['optionalLabel'] = $field_data['label'];
								} else {
									$field['optionalLabel'] = sprintf(
										/* translators: %s Field label. */
										__( '%s (optional)', 'woocommerce' ), // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
										$field_data['label']
									);
								}
							}
						}

						// Update required
						$field['required'] = ! empty( $field_data['required'] );

					} else {
						// Field not in saved set -> hide it
						$field['hidden'] = true;
					}
				}
				unset( $field );

				$asset_data_registry->add(
					'defaultFields',
					array_merge( $default_fields, $checkout_fields->get_additional_fields() )
				);

			} catch ( \Exception $e ) {
				// Silently fail
			}
		}

		/**
		 * Update woocommerce_default_address_fields filter
		 * (label, required, priority, hidden for block checkout)
		 * Same as THWCFD_Block::update_default_fields_data()
		 */
		public function jwcfe_update_default_address_fields( $fields ) {

			$change_default = apply_filters( 'jwcfe_change_default_block_address_fields', true );
			if ( ! $change_default ) {
				return $fields;
			}

			$saved_fields = $this->jwcfe_get_saved_address_field_set();

			// Agar shipping fields reset ho gayi hain to fields as-is return karo
			if ( empty( $saved_fields ) ) {
				return $fields;
			}

			foreach ( $fields as $key => &$field ) {

				if ( $key === 'email' ) {
					continue;
				}

				$shipping_key = 'shipping_' . $key;

				if ( isset( $saved_fields[ $shipping_key ] ) ) {
					$field_data = $saved_fields[ $shipping_key ];

					if ( isset( $field_data['enabled'] ) && ! $field_data['enabled'] ) {
						$field['hidden']   = true;
						$field['required'] = false;
						continue;
					}

					if ( isset( $field_data['order'] ) && $field_data['order'] !== '' ) {
						$field['priority'] = ( (int) $field_data['order'] + 1 ) * 10;
					}
					if ( ! empty( $field_data['label'] ) ) {
						$field['label'] = $field_data['label'];
					}
					$field['required'] = ! empty( $field_data['required'] );

				} else {
					$field['hidden']   = true;
					$field['required'] = false;
				}
			}
			unset( $field );

			return $fields;
		}

		/**
		 * Update country locale: control required/hidden for address_1, postcode, city, state
		 * Same as THWCFD_Block::update_address_fields_data()
		 */
		public function jwcfe_update_address_locale_fields( $locale ) {

			if ( ! function_exists( 'has_block' ) || ! has_block( 'woocommerce/checkout' ) ) {
				return $locale;
			}

			$change_default = apply_filters( 'jwcfe_change_default_block_address_fields', true );
			if ( ! $change_default ) {
				return $locale;
			}

			$saved_fields       = $this->jwcfe_get_saved_address_field_set();
			$locale_field_keys  = [ 'address_1', 'postcode', 'city', 'state' ];

			// Agar shipping fields reset ho gayi hain to locale as-is return karo
			if ( empty( $saved_fields ) ) {
				return $locale;
			}

			foreach ( $locale as $country_code => $country_fields ) {
				foreach ( $locale_field_keys as $field_name ) {
					$shipping_key = 'shipping_' . $field_name;
					if ( isset( $saved_fields[ $shipping_key ] ) && ! ( isset( $saved_fields[ $shipping_key ]['enabled'] ) && ! $saved_fields[ $shipping_key ]['enabled'] ) ) {
						$locale[ $country_code ][ $field_name ] = [
							'required' => ! empty( $saved_fields[ $shipping_key ]['required'] ),
							'hidden'   => false,
						];
					} else {
						$locale[ $country_code ][ $field_name ] = [
							'required' => false,
							'hidden'   => true,
						];
					}
				}
			}

			return $locale;
		}

		/**
		 * Validate custom address fields (email, phone, postcode rules)
		 * Same as THWCFD_Block::validate_additional_field()
		 */
		public function jwcfe_validate_additional_address_field( $errors, $field_key, $field_value ) {

			// Only handle our custom address fields
			$key_parts      = explode( 'jwcfe-block/', $field_key );
			$actual_key     = isset( $key_parts[1] ) ? $key_parts[1] : null;

			if ( empty( $actual_key ) ) {
				return $errors;
			}

			$saved_fields = $this->jwcfe_get_saved_address_field_set();

			// Try to find the field by clean key
			$field_data = null;
			foreach ( $saved_fields as $fid => $fdata ) {
				$clean = preg_replace( '/^(billing|shipping|additional)_/', '', $fid );
				if ( $clean === $actual_key ) {
					$field_data = $fdata;
					break;
				}
			}

			if ( empty( $field_data ) || empty( $field_data['validate'] ) ) {
				return $errors;
			}

			$validate_rules = is_array( $field_data['validate'] ) ? $field_data['validate'] : explode( ',', $field_data['validate'] );
			$field_label    = isset( $field_data['label'] ) ? $field_data['label'] : $actual_key;

			foreach ( $validate_rules as $rule ) {
				$rule = trim( $rule );
				switch ( $rule ) {
					case 'email':
						if ( ! empty( $field_value ) && ! is_email( $field_value ) ) {
							$errors->add(
								'invalid_email_field',
								sprintf(
									/* translators: %s is the field label */
									__( 'The provided %s is not a valid email address.', 'jwcfe' ),
									esc_html( $field_label )
								)
							);
						}
						break;

					case 'phone':
						if ( ! empty( $field_value ) && ! \WC_Validation::is_phone( $field_value ) ) {
							$errors->add(
								'invalid_phone_field',
								sprintf(
									/* translators: %s is the field label */
									__( 'The provided %s is not a valid phone number.', 'jwcfe' ),
									esc_html( $field_label )
								)
							);
						}
						break;

					case 'postcode':
						if ( ! empty( $field_value ) && ! \WC_Validation::is_postcode( $field_value, '' ) ) {
							$errors->add(
								'invalid_postcode',
								sprintf(
									/* translators: %s is the field label */
									__( 'The provided %s is not a valid postcode.', 'jwcfe' ),
									esc_html( $field_label )
								)
							);
						}
						break;
				}
			}

			return $errors;
		}

		/***************************************************
		 ******* ADDRESS SECTION FUNCTIONALITY - END *******
		 ***************************************************/

		function jwcfe_enqueue_scripts()
		{
			if ( ! is_checkout() ) {
				return;
			}

			if ( $this->jwcfe_has_block_checkout() ) {
				$this->jwcfe_enqueue_block_checkout_defaults();
			}

			wp_enqueue_style(
				'jwcfe-style-front',
				JWCFE_ASSETS_URL_PUBLIC . 'css/jwcfe-style-front.css',
				array(),
				$this->version
			);

			wp_register_script(
				'jwcfe-checkout-radios',
				plugin_dir_url( __FILE__ ) . 'assets/js/jwcfe-checkout-radios.js',
				array( 'jquery' ),
				$this->version,
				true
			);
			wp_enqueue_script( 'jwcfe-checkout-radios' );

			// localized config if needed
			wp_localize_script('jwcfe-checkout-radios', 'jwcfe_checkout_radios', array(
				'selectDataAttr' => 'data-jwcfe-type',
				'selectDataVal'  => 'radio'
			));

		}
		
		/**
		 * generating tooltip for fields in checkoutpage*
		 */

		 public function generate_tooltip($text) {
			if (empty($text)) {
				return '';
			}
			
			$tooltip_html = '<span style="position: relative; display: inline-block; cursor: pointer; margin-left: 5px;">&#9432;
							 <span style="visibility: hidden; width: 220px; background-color: #555; color: #fff; text-align: center; border-radius: 6px; padding: 5px; position: absolute; z-index: 1; bottom: 125%; left: 50%; margin-left: -60px; opacity: 0; transition: opacity 0.3s; 
							 pointer-events: none; font-size: 12px;" class="tooltip-text">' . __(stripcslashes($text), 'jwcfe') . '</span></span>';
		
			$tooltip_script = '<script>
								document.addEventListener("DOMContentLoaded", function() {
									const tooltipElements = document.querySelectorAll("label span");
									tooltipElements.forEach((element) => {
										element.addEventListener("mouseover", () => {
											const tooltipText = element.querySelector(".tooltip-text");
											if (tooltipText) {
												tooltipText.style.visibility = "visible";
												tooltipText.style.opacity = "1";
											}
										});
										element.addEventListener("mouseout", () => {
											const tooltipText = element.querySelector(".tooltip-text");
											if (tooltipText) {
												tooltipText.style.visibility = "hidden";
												tooltipText.style.opacity = "0";
											}
										});
									});
								});
							   </script>';
		
			return $tooltip_html . $tooltip_script;
		}
		/**
		 * wc_checkout_fields_scripts function.*
		 */

		public function jwcfe_checkout_fields_frontend_scripts()
		{

			global $wp_scripts;
			if (is_checkout() || is_account_page()) {
				wp_enqueue_style('jwcfe-style-front', JWCFE_ASSETS_URL_PUBLIC . '/css/jwcfe-style-front.css', JWCFE_VERSION);
				$jquery_version = isset($wp_scripts->registered['jquery-ui-core']->ver) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';
				if (is_checkout()) {
					$currentScreen = "checkout";
				} else {
					$currentScreen = "account";
				}

				wp_enqueue_style('jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/' . $jquery_version . '/themes/smoothness/jquery-ui.css');

				$in_footer = apply_filters('jwcfe_enqueue_script_in_footer', true);
				wp_register_script(
					'jwcfe-checkout-editor-frontend',
					JWCFE_ASSETS_URL_PUBLIC . 'js/jwcfe-checkout-field-editor-frontend.js',
					array('jquery', 'jquery-ui-datepicker', 'select2'),
					JWCFE_VERSION,
					$in_footer
				);
				wp_enqueue_script('jwcfe-checkout-editor-frontend');
				wp_localize_script('jwcfe-checkout-editor-frontend', 'jwcfe_checkout_obj', array(
					'ajaxurl' => admin_url('admin-ajax.php'),
				));

				$pattern = array(
					//day

					'd',		//day of the month
					'j',		//3 letter name of the day
					'l',		//full name of the day
					'z',		//day of the year
					'S',

					//month

					'F',		//Month name full
					'M',		//Month name short
					'n',		//numeric month no leading zeros
					'm',		//numeric month leading zeros

					//year

					'Y', 		//full numeric year
					'y'		//numeric year: 2 digit

				);

				$replace = array(
					'dd', 'd', 'DD', 'o', '',

					'MM', 'M', 'm', 'mm',

					'yy', 'y'
				);

				foreach ($pattern as &$p) {
					$p = '/' . $p . '/';
				}

				wp_localize_script('jwcfe-checkout-editor-frontend', 'wc_checkout_fields', array(
					'date_format' => preg_replace($pattern, $replace, wc_date_format())
				));
			}
		}


		public function jwcfe_check_field_validations($A)
		{
			$fields = get_option('jwcfe_wc_fields_billing');

			if (is_array($fields) && !empty($fields)) {
				foreach ($fields as $name => $field) {

					if (isset($field['enabled']) && $field['enabled'] == false) {
						unset($fields[$name]);
					} else {
						$new_field = $field;
						$label = '';

						if (isset($new_field['label'])) {
							$label = $new_field['label'];
						} else {
							$label = $name;
						}

						if (isset($new_field['rules_action_ajax']) && !empty($new_field['rules_action_ajax']) && isset($new_field['rules_ajax']) && !empty($new_field['rules_ajax'])) {

							if (!empty($new_field['rules_ajax'])) {
								if (!empty($A[$name])) {
									continue;
								}
								$rulesArr = json_decode(urldecode($new_field['rules_ajax']), true);
								foreach ($rulesArr[0][0] as $singleRule) {
									$operand = $singleRule[0]['operand'][0];

								}
							}
						}
					}
				}
			}
		}


		/**
		 * Hide Additional Fields title if no fields available.
		 *
		 * @param mixed $old
		 */
		
		public function jwcfe_enable_order_notes_field($fields)
		{
			global $supress_field_modification;
			if ($supress_field_modification) {
				return $fields;
			}

			$additional_fields = get_option('jwcfe_wc_fields_additional');

			// Check if additional fields exist and are enabled
			if (is_array($additional_fields)) {
				$enabled = 0;
				foreach ($additional_fields as $field) {
					if ($field['enabled']) {
						$enabled++;
					}
				}

				// If enabled, modify the 'order_comments' field to add a tooltip
				if ($enabled > 0) {
					if (isset($fields['order']['order_comments'])) {
						// Tooltip text (you can customize this)
						$tooltip_text = "Provide any specific instructions for delivery or other important order-related notes.";

						// Generate tooltip HTML
						$tooltip = $this->generate_tooltip($tooltip_text);

						// Modify label with the tooltip
						$fields['order']['order_comments']['label'] .= $tooltip;
					}
					return $fields;
				}
			}
			return $fields;
		}
		

		public function jwcfe_checkout_form_field($field, $key, $args, $value){
				$image_url = JWCFE_ASSETS_URL_PUBLIC . 'assets/ic_info.svg';
						
				if (!empty($args['clear'])) {
					$after = '<div class="clear"></div>';
				} else {
					$after = '';
				}
				$data_validations='';
				if ($args['required']) {
					$args['class'][] = 'validate-required';
					$data_validations = 'validate-required';
					$required = ' <abbr class="required" title="' . esc_attr__('required', 'jwcfe') . '">*</abbr>';
				} else {
					$required = '';
				}

				$args['maxlength'] = ($args['maxlength']) ? 'maxlength="' . absint($args['maxlength']) . '"' : '';
	
				
				if (isset($args['text'])) {
					$tooltip = $this->generate_tooltip($args['text']);
				} else {
					$tooltip = ''; 
				}
				
				switch ($args['type']) {

					case 'hidden':
						$field = '<input type="hidden" class="input-hidden ' . esc_attr(implode(' ', (array)$args['input_class'])) . '" name="' . esc_attr($key) . '" id="' . esc_attr($args['id']) . '" value="' . esc_attr($value) . '" />';
						return $field;
						break;
						
					case 'url':
					    $fieldLabel = '';
						$field = '<p class="form-row ' . esc_attr(implode(' ', (array)$args['class'])) . '" id="' . esc_attr($key) . '_field">';
						if ($args['label']) {
							$fieldLabel = $args['label'];
							$field .= '<label for="' . esc_attr($args['id']) . '" class="' . implode(' ', (array)$args['label_class']) . '">' . esc_html__($args['label'], 'jwcfe') . $required . $tooltip . '</label>';
						}
						
						$session = WC()->session;
						$previous_value = $session ? $session->get($key, '') : '';
						$display_value = !empty($previous_value) ? $previous_value : $value;
						
						$field .= '<input type="url" class="input-text ' . esc_attr(implode(' ', (array)$args['input_class'])) . '" name="' . esc_attr($key) . '" id="' . esc_attr($args['id']) . '"';
						if (!empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
							foreach ($args['custom_attributes'] as $customattr_key => $customattr_val) {
								$field .= ' ' . esc_attr($customattr_key) . '="' . esc_attr($customattr_val) . '" ';
							}
						}
						$field .= 'placeholder="' . esc_attr__($args['placeholder'], 'jwcfe') . '" ' . $args['maxlength'] . ' value="' . esc_attr($display_value) . '" />';
						$field .= '</p>' . $after;
						return $field;
						break;
						
					case 'datetime-local':
					    $fieldLabel = '';
						$field = '<p class="form-row ' . esc_attr(implode(' ', (array)$args['class'])) . '" id="' . esc_attr($key) . '_field">';
						if ($args['label']) {
							$fieldLabel = $args['label'];
							$field .= '<label for="' . esc_attr($args['id']) . '" class="' . implode(' ', (array)$args['label_class']) . '">' . esc_html__($args['label'], 'jwcfe') . $required . $tooltip . '</label>';
						}
						
						$session = WC()->session;
						$previous_value = $session ? $session->get($key, '') : '';
						$display_value = !empty($previous_value) ? $previous_value : $value;
						if (empty($display_value) && !empty($args['default'])) {
							$display_value = $args['default'];
						}
						
						$field .= '<input type="datetime-local" class="input-text ' . esc_attr(implode(' ', (array)$args['input_class'])) . '" name="' . esc_attr($key) . '" id="' . esc_attr($args['id']) . '"';
						if (!empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
							foreach ($args['custom_attributes'] as $customattr_key => $customattr_val) {
								$field .= ' ' . esc_attr($customattr_key) . '="' . esc_attr($customattr_val) . '" ';
							}
						}
						$field .= 'placeholder="' . esc_attr__($args['placeholder'], 'jwcfe') . '" ' . $args['maxlength'] . ' value="' . esc_attr($display_value) . '" />';
						$field .= '</p>' . $after;
						return $field;
						break;

					case 'checkboxgroup':
						$field 	= '';
						$field 	= '<div class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field">';
						$field .= '<fieldset><legend><label for="' . esc_attr($args['id']) . '" class="' . esc_attr(implode(' ', $args['label_class'])) . '">' . esc_html($args['label']) . $required . $tooltip . '</label></legend>';
					
						// Retrieve previous values from session
						$previous_values = WC()->session->get($key, []); // Get the array of previously checked values
					
						if (!empty($args['options_json'])) {
							foreach ($args['options_json'] as $option) {
								// Check if the current option is in the previously selected values
								$is_checked = in_array(esc_attr($option['key']), (array) $previous_values, true);
								$field .= '<label><input type="checkbox" ' . checked($is_checked, true, false) . ' name="' . esc_attr($key) . '[]" id="' . $key . '_' . esc_attr($option['key']) . '" value="' . esc_attr($option['key']) . '" /> ' . esc_html($option['text']) . '</label>';
							}
						}
					
						$field .= '</fieldset></div>' . $after;
						return $field;
						break;
					
					case 'checkbox':
						$field = '';
						$field = '<div class="form-row custom-checkbox-field ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field">';
						
						$field .= '<fieldset>';
						$field .= '<div class="checkbox-wrapper custom-checkboxes">';
						
						// Get the previous value from the session
						$session = WC()->session;
						$previous_value = $session ? $session->get($key, '') : '';
						
						// Determine if the checkbox should be checked
						$default_checked = false;
						if (isset($args['default'])) {
							$default_checked = in_array(strtolower((string) $args['default']), array('1', 'yes', 'true', 'on'), true);
						}
						$is_checked = (!empty($previous_value) || (!empty($args['checked']) && $args['checked'] === true) || $default_checked);

						// Begin label with styling
						$field .= '<label for="' . esc_attr($args['id']) . '" class="jwcfe-checkbox-label">';
						
						// Checkbox input
						$field .= '<input type="checkbox" class="jwcfe-price-field" id="' . esc_attr($args['id']) . '" name="' . esc_attr($key) . '" value="' . esc_attr($key) . '"';
						if ($is_checked) {
							$field .= ' checked="checked"';
						}
						$field .= ' />';
						
						// Label text
						$field .= '<span>' . esc_html($args['label']) . $required . $tooltip . '</span>';
						$field .= '</label>'; // end label
						
						$field .= '</div>'; // end checkbox-wrapper
						$field .= '</fieldset></div>' . $after;
						
						return $field;

						break;

					case 'month':
								$fieldLabel = '';
								$field = '<p class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field">';
							
								if ($args['label']) {
									$fieldLabel = $args['label'];
									$field .= '<label for="' . esc_attr($args['id']) . '" class="' . implode(' ', $args['label_class']) . '">' . esc_html__($args['label'], 'jwcfe') . $required . $tooltip . '</label>';
								}
							
								// Check for previous value in the session
								$session = WC()->session;
								$previous_value = $session ? $session->get($key, '') : '';
								$display_value = !empty($previous_value) ? $previous_value : $value; // Use session value if available
								if (empty($display_value) && !empty($args['default'])) {
									$display_value = $args['default'];
								}
							
								$field .= '<input type="month" class="input-text ' . esc_attr(implode(' ', $args['input_class'])) . '" name="' . esc_attr($args['id']) . '" id="' . esc_attr($args['id']) . '"';
							
								if (!empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
									foreach ($args['custom_attributes'] as $customattr_key => $customattr_val) {
										$field .= ' ' . $customattr_key . '="' . esc_attr($customattr_val) . '" ';
									}
								}
							
								// Use the value from session or posted data
								$field .= 'placeholder="' . esc_attr__($args['placeholder'], 'jwcfe') . '" ' . $args['maxlength'] . ' value="' . esc_attr($display_value) . '" />';
							
								$field .= '</p>' . $after;
							
								return $field;
							
						break;
							
					
					case 'week':
							$fieldLabel = '';
						
							$field = '<p class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field">';
						
							if ($args['label']) {
								$fieldLabel = $args['label'];
								$field .= '<label for="' . esc_attr($args['id']) . '" class="' . implode(' ', $args['label_class']) . '">' . esc_html__($args['label'], 'jwcfe') . $required . $tooltip . '</label>';
							}
						
							// Check for previous value in the session
							
							$session = WC()->session;
							$previous_value = $session ? $session->get($key, '') : '';
							$display_value = !empty($previous_value) ? $previous_value : $value; // Use session value if available
							if (empty($display_value) && !empty($args['default'])) {
								$display_value = $args['default'];
							}
						
							$field .= '<input type="week" class="input-text ' . esc_attr(implode(' ', $args['input_class'])) . '" name="' . esc_attr($args['id']) . '" id="' . esc_attr($args['id']) . '"';
						
							if (!empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
								foreach ($args['custom_attributes'] as $customattr_key => $customattr_val) {
									$field .= ' ' . $customattr_key . '="' . esc_attr($customattr_val) . '" ';
								}
							}
						
							// Use the value from session or posted data
							$field .= 'placeholder="' . esc_attr__($args['placeholder'], 'jwcfe') . '" ' . $args['maxlength'] . ' value="' . esc_attr($display_value) . '" />';
						
							$field .= '</p>' . $after;
						
							return $field;
						
							break;
					case 'multiselect':
						$customer_user_id = get_current_user_id(); 
						$customer_orders = wc_get_orders(array(
							'meta_key' => '_customer_user',
							'meta_value' => $customer_user_id,
							'posts_per_page' => 1,
							'orderby' => 'ID',
							'orderby' => 'DESC'
						));
						
						// Initialize selected values as an array
						$selectedVals = array();
					
						// Retrieve selected values from customer orders
						foreach ($customer_orders as $order) {
							$order_id = method_exists($order, 'get_id') ? $order->get_id() : $order->id;
					
							$order = wc_get_order($order_id);
							$valArr = $order->get_meta($key, true);
					
							if (!empty($valArr) && is_array($valArr)) {
								// Merge existing values into selectedVals array
								$selectedVals = array_merge($selectedVals, $valArr);
							}
						}
					
						// Handle previous values stored in session
						$session_values = WC()->session->get($key, array()); // Retrieve values from the session
						if (!empty($session_values) && is_array($session_values)) {
							$selectedVals = array_merge($selectedVals, $session_values); // Merge with selected values from orders
						}
					
						$options = '';
						if (!empty($args['options_json'])) {
							foreach ($args['options_json'] as $option) {
								// Check if the option key is in selected values
								$isSelected = in_array($option['key'], $selectedVals) ? 'selected' : '';
								$options .= '<option value="' . esc_attr($option['key']) . '" ' . $isSelected . '>' . esc_html($option['text']) . '</option>';
							}
					
							$field = '<p class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field">';
					
							if ($args['label']) {
								$fieldLabel = $args['label'];
								$field .= '<label for="' . esc_attr($args['id']) . '" class="' . implode(' ', $args['label_class']) . '">' . esc_html($args['label']) . $required . $tooltip . '</label>';
							}
							$class = '';
					
							$field .= '<select data-placeholder="' . esc_attr__('Select some options', 'jwcfe') . '" multiple="multiple"';
					
							if (!empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
								foreach ($args['custom_attributes'] as $customattr_key => $customattr_val) {
									$field .= ' ' . esc_attr($customattr_key) . '="' . esc_attr($customattr_val) . '" ';
								}
							}
					
							$field .= 'name="' . esc_attr($key) . '[]" id="' . esc_attr($key) . '" class="checkout_chosen_select select wc-enhanced-select ' . esc_attr($class) . '">';
							
							// $field .= $options; // Add options to the select field
							// $field .= '</select></p>' . $after;
							$field .= '>' . $options . '</select></p>';

							// Initialize Select2
							$field .= '<script>
								jQuery(document).ready(function($) {
									$("select#' . esc_attr($key) . '").select2({
										placeholder: "' . esc_attr__('Select some options', 'jwcfe') . '",
										width: "100%"
									});
								});
							</script>';
						}
						return $field;
					
					break;
			
					case 'date':
								$fieldLabel = '';
								$field = '';
							
								// Check for previous value in session if not provided
								$session = WC()->session;
								$previous_value = $session ? $session->get($key, '') : '';	
								$display_value = !empty($previous_value) ? $previous_value : $value; // Use session value if available
								if (empty($display_value) && !empty($args['default'])) {
									$display_value = $args['default'];
								}
							
								$field = '<p class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field">';
							
								if ($args['label']) {
									$fieldLabel = $args['label'];
									$field .= '<label for="' . esc_attr($args['id']) . '" class="' . implode(' ', $args['label_class']) . '">' . esc_html__($args['label'], 'jwcfe') . $required . $tooltip . '</label>';
								}
							
								$field .= '<input type="date" class="input-text ' . esc_attr(implode(' ', $args['input_class'])) . '" name="' . esc_attr($args['id']) . '" id="' . esc_attr($args['id']) . '"';
							
								if (!empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
									foreach ($args['custom_attributes'] as $customattr_key => $customattr_val) {
										$field .= ' ' . esc_attr($customattr_key) . '="' . esc_attr($customattr_val) . '" ';
									}
								}
							
								// Use the display value that we have ensured is from session or posted data
								$field .= 'placeholder="' . esc_attr__($args['placeholder'], 'jwcfe') . '" ' . $args['maxlength'] . ' value="' . esc_attr($display_value) . '" />';
								$field .= '</p>' . $after;
							
								return $field;
							
							break;
							
					case 'textarea':
								$field = '';
								$fieldLabel = '';
								
								// Check for previous value in session if not provided
								$session = WC()->session;
								$previous_value = $session ? $session->get($key, '') : '';
								$display_value = !empty($previous_value) ? $previous_value : $value; // Use session value if available
							
								$field = '<p class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field">';
							
								if ($args['label']) {
									$fieldLabel = $args['label'];
									$field .= '<label for="' . esc_attr($args['id']) . '" class="' . implode(' ', $args['label_class']) . '">' . esc_html__($args['label'], 'jwcfe') . $required . $tooltip . '</label>';
								}
							
								$field .= '<textarea class="input-text ' . esc_attr(implode(' ', $args['input_class'])) . '" name="' . esc_attr($args['id']) . '" id="' . esc_attr($args['id']) . '"';
							
								if (!empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
									foreach ($args['custom_attributes'] as $customattr_key => $customattr_val) {
										$field .= ' ' . esc_attr($customattr_key) . '="' . esc_attr($customattr_val) . '" ';
									}
								}
							
								// Use the display value that we have ensured is from session or posted data
								$field .= 'placeholder="' . esc_attr__($args['placeholder'], 'jwcfe') . '" ' . $args['maxlength'] . '>' . esc_html($display_value) . '</textarea>';
								$field .= '</p>' . $after;
							
								return $field;
							
							break;
					case 'text':
							$fieldLabel = '';
							$field = '';
							// Check for previous value in session if not provided
							$session = WC()->session;
							$previous_value = $session ? $session->get($key, '') : '';
							$display_value = !empty($previous_value) ? $previous_value : $value; // Use session value if available
						
							$field = '<p class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field" data-validations="' . $data_validations . '">';
						
							if ($args['label']) {
								$fieldLabel = $args['label'];
								$field .= '<label for="' . esc_attr($args['id']) . '" class="' . implode(' ', $args['label_class']) . '">' . esc_html($args['label'], 'jwcfe') . $required . $tooltip . '</label>';
							}
						
							$field .= '<input type="text" class="input-text ' . esc_attr(implode(' ', $args['input_class'])) . '" name="' . esc_attr($args['id']) . '" id="' . esc_attr($args['id']) . '"';
						
							if (!empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
								foreach ($args['custom_attributes'] as $customattr_key => $customattr_val) {
									$field .= ' ' . esc_attr($customattr_key) . '="' . esc_attr($customattr_val) . '" ';
								}
							}
						
							// Use the display value that we have ensured is from session or posted data
							$field .= 'placeholder="' . esc_attr__($args['placeholder'], 'jwcfe') . '" ' . $args['maxlength'] . ' value="' . esc_attr($display_value) . '" />';
							$field .= '</p>' . $after;
						
							return $field;
						
						break;
						
					case 'email':
							$fieldLabel = '';
							$field = '';
						
							// Check for previous value in session if not provided

							$session = WC()->session;
							$previous_value = $session ? $session->get($key, '') : '';
							$display_value = !empty($previous_value) ? $previous_value : $value; // Use session value if available
						
							$field = '<p class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field" data-validations="' . $data_validations . '">';
						
							if ($args['label']) {
								$fieldLabel = $args['label'];
						
								$tooltipText = isset($args['text']) ? stripcslashes($args['text']) : '';
								$tooltip = $this->generate_tooltip($tooltipText);
						
								$field .= '<label for="' . esc_attr($args['id']) . '" class="' . implode(' ', $args['label_class']) . '">' . esc_html__($args['label'], 'jwcfe') . $required . $tooltip . '</label>';
							}
						
							$field .= '<input type="email" class="input-text ' . esc_attr(implode(' ', $args['input_class'])) . '" name="' . esc_attr($args['id']) . '" id="' . esc_attr($args['id']) . '"';
						
							if (!empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
								foreach ($args['custom_attributes'] as $customattr_key => $customattr_val) {
									$field .= ' ' . esc_attr($customattr_key) . '="' . esc_attr($customattr_val) . '" ';
								}
							}
						
							// Use the display value that we have ensured is from session or posted data
							$field .= 'placeholder="' . esc_attr__($args['placeholder'], 'jwcfe') . '" ' . $args['maxlength'] . ' value="' . esc_attr($display_value) . '" />';
							$field .= '</p>' . $after;
						
							return $field;
						
						break;
					case 'phone':
							$fieldLabel = '';
							$field = '';

							$session = WC()->session;
							$previous_value = $session ? $session->get($key, '') : '';
							$display_value = !empty($previous_value) ? $previous_value : $value; // Use session value if available
						
							$field = '<p class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field" data-validations="' . $data_validations . '">';
							
							if ($args['label']) {
								$fieldLabel = $args['label'];
								$field .= '<label for="' . esc_attr($args['id']) . '" class="' . implode(' ', $args['label_class']) . '">' . $args['label'] . $required . $tooltip . '</label>';
							}
						
							$field .= '<input type="tel" class="input-text ' . esc_attr(implode(' ', $args['input_class'])) . '" name="' . esc_attr($args['id']) . '" id="' . esc_attr($args['id']) . '"';
						
							if (!empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
								foreach ($args['custom_attributes'] as $customattr_key => $customattr_val) {
									$field .= ' ' . $customattr_key . '="' . esc_attr($customattr_val) . '" ';
								}
							}
						
							$field .= 'placeholder="' . esc_attr__($args['placeholder'], 'jwcfe') . '" ' . $args['maxlength'] . ' value="' . esc_attr($display_value) . '" />';
							$field .= '</p>' . $after;
						
							return $field;
							break;
						
					case 'select':
								$options = '<option selected value>' . __('Please Select', 'jwcfe') . '</option>';
								// $selectedVal = WC()->session->get($key, ''); // Get value from session
								
								if ( is_callable( [ WC()->session, 'get' ] ) ) {
									$selectedVal = WC()->session->get( $key, '' );
								} else {
									$selectedVal = '';
								}								// Get the current user ID and orders
								$customer_user_id = get_current_user_id();
								$customer_orders = wc_get_orders([
									'meta_key' => '_customer_user',
									'meta_value' => $customer_user_id,
									'posts_per_page' => 1,
									'orderby' => 'ID',
									'order' => 'DESC'
								]);
							
								// Get previous value if available

								$session = WC()->session;
								$previous_value = $session ? $session->get($key, '') : '';

								$display_values = !empty($previous_value) ? $previous_value : $value;
								$display_value = is_array($display_values) ? reset($display_values) : $display_values;
								if (empty($selectedVal) && empty($display_value) && !empty($args['default'])) {
									$selectedVal = $args['default'];
								}
							
								// Loop through customer orders to retrieve previously selected value
								foreach ($customer_orders as $order) {
									$order_id = method_exists($order, 'get_id') ? $order->get_id() : $order->id;
									$order = wc_get_order($order_id);
									$valArr = $order->get_meta($key, true);
							
									if (!empty($valArr) && is_array($valArr)) {
										$selectedVal = reset($valArr); // Get the first item from array if multiple values exist
									}
								}
							
								// Ensure $args['options_json'] is defined and loop through options
								if (!empty($args['options_json']) && is_array($args['options_json'])) {
									foreach ($args['options_json'] as $option) {
										if (isset($option['key'], $option['text'])) { // Check if 'key' and 'text' exist
											$selectedOptions = selected($selectedVal, $option['key'], false); // Use session or order value
											$options .= '<option ' . $selectedOptions . ' value="' . esc_attr($option['key']) . '">' . esc_html($option['text']) . '</option>';

										}
									}
								}
							
								// Construct the field HTML
								$field = '<p class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field">';
								if (!empty($args['label'])) {
									$field .= '<label for="' . esc_attr($key) . '" class="' . implode(' ', $args['label_class']) . '">' . esc_html($args['label']) . $required . $tooltip . '</label>';
								}
								$field .= '<select name="' . esc_attr($args['id']) . '[]" id="' . esc_attr($args['id']) . '" class="checkout_chosen_select select wc-enhanced-select">';
								$field .= $options;
								$field .= '</select></p>' . $after;
							
								return $field;
							
								break;
							case 'radio':
								$field = '';										
								$field = '<div class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field">';
								$field .= '<fieldset><legend><label for="' . esc_attr($args['id']) . '" class="' . esc_attr(implode(' ', $args['label_class'])) . '">' . esc_html($args['label']) . $required . $tooltip . '</label></legend>';
								
								$field .= '<div class="custom-radio-container">';
								
								// Check for previous value in session if not provided
								$session = WC()->session;
								$previous_value = $session ? $session->get($key, '') : '';
								$display_value = !empty($previous_value) ? $previous_value : $value; // Use session value if available
								if (empty($display_value) && !empty($args['default'])) {
									$display_value = $args['default'];
								}

								
								if (!empty($args['options_json'])) {
									foreach ($args['options_json'] as $option) {
										$field .= '<div class="custom-radio-wrapper">'; 
										$field .= '<input type="radio" id="' . $args['id'] . '_' . $option['key'] . '" ';
										
										if (!empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
											foreach ($args['custom_attributes'] as $customattr_key => $customattr_val) {
												$field .= ' ' . esc_attr($customattr_key) . '="' . esc_attr($customattr_val) . '" ';
											}
										}
							
										// Use the display value that we have ensured is from session or posted data
										$field .= 'name="' . esc_attr($args['id']) . '" value="' . esc_attr($option['key']) . '"';
										$field .= checked($display_value, $option['key'], false) . '>';
										$field .= '<label for="' . $args['id'] . '_' . $option['key'] . '">' . esc_html($option['text']) . '</label>';
										$field .= '</div>'; 
									}
								}
								
								$field .= '</div>';
								$field .= '</fieldset>';
								$field .= '</div>' . $after;
							
								return $field;
							break;
					case 'number':
						$fieldLabel = '';
						$field = '';
					
						// Check for previous value in session if not provided
						$session = WC()->session;
						$previous_value = $session ? $session->get($key, '') : '';
						$display_value = !empty($previous_value) ? $previous_value : $value; // Use session value if available
					
						$field = '<p class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field" data-validations="' . $data_validations . '">';
					
						if ($args['label']) {
							$fieldLabel = $args['label'];
							$field .= '<label for="' . esc_attr($args['id']) . '" class="' . implode(' ', $args['label_class']) . '">' . $args['label'] . $required . $tooltip . '</label>';
						}
					
						// Extract and sanitize maxlength from custom_attributes array
						$max_length = isset($args['custom_attributes']['maxlength']) ? intval(preg_replace('/[^0-9]/', '', $args['custom_attributes']['maxlength'])) : '';
					
						$field .= '<input type="number" class="input-text ' . esc_attr(implode(' ', $args['input_class'])) . '" name="' . esc_attr($args['id']) . '" id="' . esc_attr($args['id']) . '"';
					
						if (!empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
							foreach ($args['custom_attributes'] as $customattr_key => $customattr_val) {
								if ($customattr_key === 'maxlength') {
									$field .= ' maxlength="' . esc_attr($max_length) . '" ';
								} else {
									$field .= ' ' . esc_attr($customattr_key) . '="' . esc_attr($customattr_val) . '" ';
								}
							}
						}
					
						// Apply placeholder and value
						$field .= ' placeholder="' . esc_attr__($args['placeholder'], 'jwcfe') . '" value="' . esc_attr($display_value) . '">';
						$field .= '</p>' . $after;
					
						// Add JavaScript to enforce maxlength
						if ($max_length) {
							$field .= "<script>
								document.addEventListener('DOMContentLoaded', function () {
									const numberInput = document.getElementById('" . esc_attr($args['id']) . "');
									const maxLength = " . (int)$max_length . ";
					
									if (numberInput && maxLength) {
										numberInput.addEventListener('input', function () {
											if (this.value.length > maxLength) {
												this.value = this.value.slice(0, maxLength);
											}
										});
									}
								});
							</script>";
						}
					
						return $field;
						break;
					
					case 'timepicker':
					// Check for previous value in session if not provided
					$session = WC()->session;
					$previous_value = $session ? $session->get($key, '') : '';
					$display_values = !empty($previous_value) ? $previous_value : $value; 
					
					if (is_array($display_values) && !empty($display_values)) {
						$display_value = $display_values[0]; // Get the first value
					} elseif (is_scalar($display_values) && '' !== (string) $display_values) {
						$display_value = (string) $display_values;
					} else {
						$display_value = ''; // Set to an empty string if no values
					}
					
					set_time_limit(0);
					$customer_user_id = get_current_user_id(); 
					$customer_orders = wc_get_orders(array(
						'meta_key' => '_customer_user',
						'meta_value' => $customer_user_id,
						'posts_per_page' => 1,
						'orderby' => 'ID',
						'order' => 'DESC'
					));
					
					$selectedVal = '';
					foreach ($customer_orders as $order) {
						$order_id = method_exists($order, 'get_id') ? $order->get_id() : $order->id;
						$valArr = $order->get_meta($key, true);
						
						if (!empty($valArr) && is_array($valArr)) {
							// Use the latest selected value if it exists
							$selectedVal = implode(',', $valArr);
						}
					}

					$default_time = isset($args['default']) ? trim((string) $args['default']) : '';
					if ('' !== $default_time && '' === $selectedVal && '' === $display_value) {
						$display_value = $default_time;
					}
				
					// Merge display value with selectedVal for comparison
					$selectedValArray = array_filter(array_merge(explode(',', $selectedVal), [$display_value]));

					if ('' !== $default_time) {
						$selectedValArray[] = $default_time;
						$default_time_stamp = strtotime($default_time);
						if (false !== $default_time_stamp) {
							$selectedValArray[] = date('H:i', $default_time_stamp);
						}
					}
					$selectedValArray = array_values(array_unique(array_map('strval', $selectedValArray)));
					
					$after = (!empty($args['clear'])) ? '<div class="clear"></div>' : '';
					$required = $args['required'] ? ' <abbr class="required" title="' . esc_attr__('required', 'jwcfe') . '">*</abbr>' : '';
					
					$options = '<option value="">' . esc_html__('Please Select', 'jwcfe') . '</option>';
					$field = '<p class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field">';
					
					if ($args['label']) {
						$field .= '<label for="' . esc_attr($key) . '" class="' . implode(' ', $args['label_class']) . '">' . esc_html__($args['label'], 'jwcfe') . $required . $tooltip . '</label>';
					}
					
					$min_time = strtotime($args['min_time']);
					$max_time = strtotime($args['max_time']);
					$time_step = $args['time_step'];
					$time_format = $args['time_format'];
					if ('' !== $default_time) {
						$default_time_stamp = strtotime($default_time);
						if (false !== $default_time_stamp) {
							$selectedValArray[] = date($time_format, $default_time_stamp);
							$selectedValArray = array_values(array_unique(array_map('strval', $selectedValArray)));
						}
					}
					$has_selected_option = false;
					
					for ($hours = 0; $hours < 24; $hours++) {
						for ($mins = 0; $mins < 60; $mins += $time_step) {
							$rawtime = str_pad(($hours % 24), 2, '0', STR_PAD_LEFT) . ':' . str_pad($mins, 2, '0', STR_PAD_LEFT);
							$formatted_time = date($time_format, strtotime($rawtime));
							if (strtotime($rawtime) >= $min_time && strtotime($rawtime) <= $max_time) {
								// Check if this time was previously selected
								$option_attributes = '';
								if (in_array($formatted_time, $selectedValArray, true) || in_array($rawtime, $selectedValArray, true)) {
									$option_attributes = ' selected'; // Add selected attribute if this time was previously selected
									$has_selected_option = true;
								}
								
								$options .= '<option value="' . esc_attr($formatted_time) . '"' . $option_attributes . '>' . esc_html($formatted_time) . '</option>';
							}
						}
					}

					if ('' !== $default_time && ! $has_selected_option) {
						$default_label = $default_time;
						$default_time_stamp = strtotime($default_time);
						if (false !== $default_time_stamp) {
							$default_label = date($time_format, $default_time_stamp);
						}
						$options .= '<option value="' . esc_attr($default_label) . '" selected>' . esc_html($default_label) . '</option>';
					}
					
					// Build the select field
					$field .= '<select name="' . esc_attr($args['id']) . '[]" id="' . esc_attr($args['id']) . '"';
					foreach ($args['custom_attributes'] as $customattr_key => $customattr_val) {
						$field .= ' ' . esc_attr($customattr_key) . '="' . esc_attr($customattr_val) . '" ';
					}
					$field .= 'class="checkout_chosen_select select wc-enhanced-select">' . $options . '</select></p>' . $after;
				
					return $field;
					break;
				
			
			}
		}	
		function jwcfe_checkout_fields_number_field( $field, $key, $args, $value ) {
		
			if ( ( ! empty( $args['clear'] ) ) ) $after = '<div class="clear"></div>'; else $after = '';
			$data_validations = '';
			if ( $args['required'] ) {
				$args['class'][] = 'validate-required';
				$data_validations = 'validate-required';
				$required = ' <abbr class="required" title="' . esc_attr__( 'required', 'jwcfe'  ) . '">*</abbr>';
			} else {
				$required = '';
			}

			$args['maxlength'] = ( $args['maxlength'] ) ? 'maxlength="' . absint( $args['maxlength'] ) . '"' : '';
			
			
			$fieldLabel = '';
			$field = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) .'" id="' . esc_attr( $key ) . '_field" data-validations="'.$data_validations.'" >';
			if ( $args['label'] ) {
				$fieldLabel = $args['label'];
				$field .= '<label for="' . esc_attr( $args['id'] ) . '" class="' . implode( ' ', $args['label_class'] ) .'">' . __($args['label'],'jwcfe') . $required . '</label>';
			}
			
			$field .= '<input type="number" class="input-number '.esc_attr( implode( ' ', $args['input_class'] ) ).'" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '"';
			if(!empty($args['custom_attributes']) && is_array($args['custom_attributes'])){
				foreach($args['custom_attributes'] as $customattr_key=>$customattr_val){
					$field .= ' '.$customattr_key.'='.'"'.$customattr_val.'" ';
				}
				
			}
			$field .= 'placeholder="' . __($args['placeholder'], 'jwcfe') . '" '.$args['maxlength'].' value="' . esc_attr( $value ) . '" />';
			
			$field .= '</p>' . $after;

			return $field;
		}
		/**
		 * jwcfe_checkout_fields_heading_field function.
		 *
		 * @param string $field (default: '')
		 * @param mixed $key
		 * @param mixed $args
		 * @param mixed $value
		 */


		
		public function jwcfe_checkout_fields_heading_field($field, $key, $args, $value) {
			if (!empty($args['clear'])) $after = '';
			else $after = '';

			$data_validations = '';

			if ($args['required']) {
				$args['class'][] = 'validate-required';
				$data_validations = 'validate-required';
				$required = ' <abbr class="required" title="' . esc_attr__('required', 'jwcfe') . '">*</abbr>';
			} else {
				$required = '';
			}

			// Start building the field HTML matching user request structure but using plugin classes
			$field = '<div class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field" data-name="' . esc_attr($key) . '" data-validations="' . $data_validations . '">';
			
			$heading_content = !empty($args['label']) ? $args['label'] : $value;
			$heading_type = !empty($args['heading_type']) ? $args['heading_type'] : 'h4';

			// User requested no label tag, and content inside the heading
			$field .= '<' . esc_attr($heading_type) . ' class="">' . esc_html($heading_content) . '</' . esc_attr($heading_type) . '>';

			$field .= '</div>' . $after;

			return $field;
		}

		/**
		 * jwcfe_checkout_fields_custom_field function.
		 *

		 * @param string $field (default: '')
		 * @param mixed $key
		 * @param mixed $args
		 * @param mixed $value
		 */

		
		
		public function jwcfe_checkout_fields_customcontent_field($field, $key, $args, $value)
		{
			if ((!empty($args['clear']))) $after = '';
			else $after = '';
		
			$data_validations = '';
		
			if ($args['required']) {
				$args['class'][] = 'validate-required';
				$data_validations = 'validate-required';
				$required = ' <abbr class="required" title="' . esc_attr__('required', 'jwcfe') . '">*</abbr>';
			} else {
				$required = '';
			}
		
			$args['maxlength'] = ($args['maxlength']) ? 'maxlength="' . absint($args['maxlength']) . '"' : '';
		
			$fieldLabel = '';
		
			// Start building the field HTML
			$field = '<p class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field"  data-validations="' . $data_validations . '" >';
		
			if ($args['label']) {
				$fieldLabel = $args['label'];
		
				// Add label with tooltip
				$tooltip = $this->generate_tooltip($args['text']);
				$field .= '<label for="' . esc_attr($args['id']) . '" class="' . implode(' ', $args['label_class']) . '">' . $args['label'] . $required . $tooltip . '</label>';
			}
		
			
				$field .= '<input type="text" class="input-text ' . esc_attr(implode(' ', $args['input_class'])) . '" name="' . esc_attr($args['id']) . '" id="' . esc_attr($args['id']) . '"';
		
				if (!empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
					foreach ($args['custom_attributes'] as $customattr_key => $customattr_val) {
						$field .= ' ' . $customattr_key . '="' . esc_attr($customattr_val) . '" ';
					}
				}
		
				$field .= 'placeholder="' . esc_attr__($args['placeholder'], 'jwcfe') . '" ' . $args['maxlength'] . ' value="' . esc_attr__($value, 'jwcfe') . '" />';
			
		
			$field .= '</p>' . $after;
		
			return $field;
		}

		//   jwcfe_checkout_fields_paragraph_field function.

		public function jwcfe_checkout_fields_pro_paragraph_field($field, $key, $args, $value) {
			// Check for previous value in session if not provided
			$session = WC()->session;
			$previous_value = $session ? $session->get($key, '') : '';
			$display_value = !empty($previous_value) ? $previous_value : $value; // Use session value if available
		
			if ((!empty($args['clear']))) $after = '';
			else $after = '';
		
			$data_validations = '';
		
			if ($args['required']) {
				$args['class'][] = 'validate-required';
				$data_validations = 'validate-required';
				$required = ' <abbr class="required" title="' . esc_attr__('required', 'jwcfe') . '">*</abbr>';
			} else {
				$required = '';
			}
		
			$args['maxlength'] = ($args['maxlength']) ? 'maxlength="' . absint($args['maxlength']) . '"' : '';
		
			$field = '<div class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($key) . '_field"  data-validations="' . $data_validations . '" >';
			
			$paragraph_content = !empty($args['texteditor']) ? $args['texteditor'] : (!empty($args['label']) ? $args['label'] : (!empty($args['text']) ? $args['text'] : ''));
			$field .= '<div class="jwcfe-paragraph-content">' . wp_kses_post($paragraph_content) . '</div>';
		
			if (!empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
				foreach ($args['custom_attributes'] as $customattr_key => $customattr_val) {
					$field .= ' ' . $customattr_key . '="' . esc_attr($customattr_val) . '" ';
				}
			}
		
			// Use the value that we have ensured is from session or posted data
		
			$field .= '</div>' . $after;
		
		
			return $field;
		}


		
		/** Save Data function. */

		public function save_data($order_id, $posted) {

			$types = array('billing', 'shipping', 'additional');
	
		
			foreach ($types as $type) {
				$fields = JWCFE_Helper::get_fields($type);
				foreach ($fields as $name => $field) {
					if (isset($field['custom']) && $field['custom'] && isset($posted[$name])) {
						$value = $posted[$name];
						
						// Ensure the value is not an array before cleaning
						if (is_array($value)) {
							$value = implode(',', $value); // or handle it as needed
						} else {
							$value = wc_clean($value);
						}
		
						if ($value) {
							// Save the value to the session
							WC()->session->set($name, $value);
							// Save the value to order meta
							$order = wc_get_order($order_id);
							$order->update_meta_data($name, $value);
							$order->save();
						}
					}
		
				}
			}
		}
		

		public static function get_current_tab() {
			$allowed_tabs = array('fields', 'block'); // Define allowed tabs
			$tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'fields';
	
			return in_array($tab, $allowed_tabs) ? $tab : 'fields';
		}

		public function jwcfe_woo_default_address_fields($fields)
		{				
			
		
			$sname = apply_filters('jwcfe_address_field_override_with', 'billing');

			if ($sname === 'billing' || $sname === 'shipping') {
				$address_fields = get_option('jwcfe_wc_fields_' . $sname);
				if (is_array($address_fields) && !empty($address_fields) && !empty($fields)) {
					$override_required = apply_filters('jwcfe_address_field_override_required', true);
					foreach ($fields as $name => $field) {
						$fname = $sname . '_' . $name;
						if ($this->jwcfe_is_locale_field($fname) && $override_required) {
							$custom_field = (isset($address_fields[$fname]) ? $address_fields[$fname] : false);
							if ($custom_field && !(isset($custom_field['enabled']) && $custom_field['enabled'] == false)) {
								$fields[$name]['required'] = (isset($custom_field['required']) && $custom_field['required'] ? true : false);
							}
						}
					}
				}
			}
			return $fields;
		}

		public function jwcfe_prepare_country_locale($fields)
		{
			if (is_array($fields)) {
				foreach ($fields as $key => $props) {
					$override_ph = apply_filters('jwcfe_address_field_override_placeholder', true);
					$override_label = apply_filters('jwcfe_address_field_override_label', true);
					$override_required = apply_filters('jwcfe_address_field_override_required', false);
					$override_priority = apply_filters('jwcfe_address_field_override_priority', true);
					if ($override_ph && isset($props['placeholder'])) {
						unset($fields[$key]['placeholder']);
					}
					if ($override_label && isset($props['label'])) {
						unset($fields[$key]['label']);
					}
					if ($override_required && isset($props['required'])) {
						unset($fields[$key]['required']);
					}

					if ($override_priority && isset($props['priority'])) {
						unset($fields[$key]['priority']);
					}
				}
			}
			return $fields;
		}

		public function jwcfe_woo_get_country_locale($locale)
		{
			if (is_array($locale)) {
				foreach ($locale as $country => $fields) {
					$locale[$country] = $this->jwcfe_prepare_country_locale($fields);
				}
			}
			return $locale;
		}

		/**
		 * wc_checkout_fields_modify_billing_fields function.
		 *
		 * @param mixed $fields
		 */
		public function jwcfe_billing_fields_lite_paid($fields, $country)
		{
			

			global  $supress_field_modification;
			if ($supress_field_modification) {
				return $fields;
			}
			if (is_wc_endpoint_url('edit-address')) {
				return $fields;
			} else {

				if (get_option('jwcfe_account_sync_fields') && get_option('jwcfe_account_sync_fields') == "on") {
					$fields_set = array();
					if (is_array(get_option('jwcfe_wc_fields_account'))) {
						foreach (get_option('jwcfe_wc_fields_account') as $name => $field) {
							if ($name == 'account_username' || $name == 'account_password') {
								continue;
							}
							if (isset($field['type']['type']) && $field['type'] === 'hidden') {
								$field['required'] = 0;
							}
							if (isset($field['type']) && $field['type'] === 'heading') {
								$field['required'] = 0;
							}
							if (isset($field['type']) && $field['type'] === 'customcontent') {
								$field['required'] = 0;
							}

							$fields_set[$name] = $field;
						}
					}
					$billing_fields = get_option('jwcfe_wc_fields_billing');

					if (isset($billing_fields) && is_array(get_option('jwcfe_wc_fields_billing'))) {
						$billing_fields = get_option('jwcfe_wc_fields_billing');
						$fields_set = array_merge($billing_fields, $fields_set);
					}
				} else {
					$fields_set = get_option('jwcfe_wc_fields_billing');
				}
				return $this->jwcfe_prepare_address_fields_paid(
					$fields_set,
					$fields,
					'billing',
					$country
				);
			}
		}
		
		/**
		 * wc_checkout_fields_modify_shipping_fields function.
		 *
		 * @param mixed $old
		 */
		public function jwcfe_shipping_fields_lite($fields, $country)
		{
			global  $supress_field_modification;
			if ($supress_field_modification) {
				return $fields;
			}

			if (is_wc_endpoint_url('edit-address')) {
				return $fields;
			} else {
				return  $this->jwcfe_prepare_address_fields_paid(
					get_option('jwcfe_wc_fields_shipping'),
					$fields,
					'shipping',
					$country
				);
			}
		}

		/**
		 * wc_checkout_fields_modify_shipping_fields function.
		 *
		 * @param mixed $old
		 */
		
		public function jwcfe_checkout_fields_lite($fields) {
			global $supress_field_modification;
			
			if ($supress_field_modification) {
				return $fields;
			}
			
			// Get additional fields from options
			if ($additional_fields = get_option('jwcfe_wc_fields_additional')) {
				if (isset($fields['order']) && is_array($fields['order'])) {
					$fields['order'] = $additional_fields + $fields['order'];
				}
				
				// Check if order_comments is enabled/disabled
				if (is_array($additional_fields) && !$additional_fields['order_comments']['enabled']) {
					unset($fields['order']['order_comments']);
				}
			}
			
			// Prepare checkout fields
			if (isset($fields['order']) && is_array($fields['order'])) {
				$fields['order'] = $this->jwcfe_prepare_checkout_fields_lite_paid($fields['order'], false);
			}
			
			// Ensure 'order' is an array
			if (isset($fields['order']) && !is_array($fields['order'])) {
				unset($fields['order']);
			}
			
			return $fields;
		}

		/**
		 *
		 */
		public function jwcfe_prepare_address_fields_paid(
			$fieldset,
			$original_fieldset = false,
			$sname = 'billing',
			$country = ''
		) {

			if (is_array($fieldset) && !empty($fieldset)) {
				$locale = WC()->countries->get_country_locale();
				if (isset($locale[$country]) && is_array($locale[$country])) {
					foreach ($locale[$country] as $key => $value) {
						if (is_array($value) && isset($fieldset[$sname . '_' . $key])) {
							if (isset($value['required'])) {
								$fieldset[$sname . '_' . $key]['required'] = $value['required'];
							}
						}
					}
				}

				if (get_option('jwcfe_wc_fields_billing')) {
					$fieldset = $this->jwcfe_prepare_checkout_fields_lite_paid($fieldset, $original_fieldset, $sname);
				} else {
					$fieldset = array_merge($original_fieldset, $fieldset);
				}

				return $fieldset;
			} else {
				return $original_fieldset;
			}
		}


		/**
		 * checkout_fields_modify_fields function.
		 *
		 * @param mixed $data
		 * @param mixed $old
		 */
		
		public function jwcfe_prepare_checkout_fields_lite_paid($fields, $original_fields, $sname = "") {
		
			if (is_array($fields) && !empty($fields)) {
				foreach ($fields as $name => $field) {
					// Check if field is enabled
					if (isset($field['enabled']) && $field['enabled'] === false) {
						unset($fields[$name]);
						continue;
					}
		
					// Prepare new field
					$new_field = $original_fields && isset($original_fields[$name]) ? $original_fields[$name] : $field;
					$new_field['label'] = isset($field['label']) ? $field['label'] : '';
					$new_field['texteditor'] = isset($field['texteditor']) ? $field['texteditor'] : '';
					$new_field['heading_type'] = isset($field['heading_type']) ? $field['heading_type'] : 'h4';

					$new_field['placeholder'] = isset($field['placeholder']) ? $field['placeholder'] : '';
					if (isset($field['default'])) {
						$new_field['default'] = $field['default'];
					}
					$new_field['class'] = isset($field['class']) && is_array($field['class']) ? $field['class'] : array();
					$new_field['validate'] = isset($field['validate']) && is_array($field['validate']) ? $field['validate'] : array();
					$new_field['required'] = isset($field['required']) ? $field['required'] : 0;
					$new_field['clear'] = isset($field['clear']) ? $field['clear'] : 0;
		
					// Add conditional field classes
					if (isset($new_field['rules_action_ajax']) && !empty($new_field['rules_action_ajax']) && isset($new_field['rules_ajax']) && !empty($new_field['rules_ajax'])) {
						$new_field['class'][] = 'jwcfe-conditional-field';
						$new_field['required'] = false;
					}
		
					if (isset($new_field['rules_action']) && !empty($new_field['rules_action']) && isset($new_field['rules']) && !empty($new_field['rules'])) {
						$new_field['class'][] = 'jwcfe-conditional-field';
					}
		
				
					// Handle specific field types
					if (isset($new_field['type']) && in_array($new_field['type'], ['hidden', 'heading', 'customcontent'])) {
						$new_field['required'] = false;
					}

					if (isset($new_field['type']) && $new_field['type'] === 'select') {
						if (apply_filters('jwcfe_enable_select2_for_select_fields', true)) {
							$new_field['input_class'][] = 'jwcfe-enhanced-select';
						}
					}
		
					// Set field order and priority
					$new_field['order'] = isset($field['order']) && is_numeric($field['order']) ? $field['order'] : 0;
					$priority = ($new_field['order'] + 1) * 10;
					$new_field['priority'] = $priority;
		
					// Translate labels and placeholders
					if (isset($new_field['label'])) {
						$new_field['label'] = __($new_field['label'], 'jwcfe');
					}
					if (isset($new_field['placeholder'])) {
						$new_field['placeholder'] = __($new_field['placeholder'], 'jwcfe');
					}
					$new_field['input_class'][] = 'jwcfe-input-field';
					$fields[$name] = $new_field;
		
				}
			}
		
			return $fields;
		}
		

		/*****************************************
     	 ----- Display Field Values - START ------
		 *****************************************/
		/**
		 * Display custom fields in emails
		 *
		 * @param array $keys
		 * @return array
		 */
		
		
		public function jwcfe_display_custom_fields_in_emails_lite($order, $sent_to_admin, $plain_text) {
			$fields_html = '';
			$fields_to_display = []; // Store fields that have data to display
		
			$fields = array_merge(
				JWCFE_Helper::get_fields('billing'),
				JWCFE_Helper::get_fields('shipping'),
				JWCFE_Helper::get_fields('additional')
			);
				
			// Retrieve order
			$order = wc_get_order($order->get_id());
			
			if(!$order){
				return;
			}
		
			// Loop through all custom fields to check if they should be displayed
			foreach ($fields as $key => $options) {
		
				if (isset($options['show_in_email']) && $options['show_in_email']) {
					// $value = $this->fetch_order_meta_value($order, $key, $options['type']);
		
					$type = isset($options['type']) ? $options['type'] : null;
					$value = $this->fetch_order_meta_value($order, $key, $type);
										
					if (!empty($value)) {
						$label = (isset($options['label']) && $options['label'] ? $options['label'] : $key);
						$fields_to_display[] = [
							'label' => esc_attr($label),
							'value' => $value,
							'type'  => $options['type'] ?? ''
						];
					}
				}
			}
		
			// Only render the section if there are fields to display
			if (!empty($fields_to_display)) {
				if ($plain_text === false) {
					$fields_html .= '<table style="width: 100%; border-collapse: collapse;">'; // Start the table
					$fields_html .= '<thead><tr><th style="border: 1px solid #ddd; padding: 8px;">Field</th><th style="border: 1px solid #ddd; padding: 8px;">Value</th></tr></thead>';
					$fields_html .= '<tbody>'; // Start the body of the table
		
					// Add rows for each field
					foreach ($fields_to_display as $field) {
						$fields_html .= '<tr>';
						$fields_html .= '<th style="border: 1px solid #ddd; padding: 8px;">' . esc_html($field['label']) . '</th>';
						$fields_html .= '<td style="border: 1px solid #ddd; padding: 8px;">';
		
						$fields_html .= esc_html($field['value']);
		
						$fields_html .= '</td>';
						$fields_html .= '</tr>';
					}
		
					$fields_html .= '</tbody>'; // Close the table body
					$fields_html .= '</table>'; // Close the table
				} else {
					foreach ($fields_to_display as $field) {
						$fields_html .= $field['label'] . ': ' . $field['value'] . "\n";
					}
				}
			}
		
			echo $fields_html; // Output the final HTML
		}
		
		private function fetch_order_meta_value($order, $key, $type) {
			if (in_array($type, ['select', 'checkboxgroup', 'timepicker', 'multiselect'])) {
				$value = $order->get_meta($key, true);
				if (is_array($value)) {
					return implode(",", $value);
				}
			}
			return $order->get_meta($key, true);
		}
		public function jwcfe_is_locale_field($field_name)
		{
			if (!empty($field_name) && in_array($field_name, array(
				'billing_address_1',
				'billing_address_2',
				'billing_state',
				'billing_postcode',
				'billing_city',
				'shipping_address_1',
				'shipping_address_2',
				'shipping_state',
				'shipping_postcode',
				'shipping_city'
			))) {
				return true;
			}
			return false;
		}

		// File upload field type is no longer supported.
	}

endif;