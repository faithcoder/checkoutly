<?php
/**
 * Advanced Settings page for JWCFE plugin.
 *
 * @package    woo-checkout-regsiter-field-editor
 * @subpackage woo-checkout-regsiter-field-editor/admin
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'JWCFE_Admin_Settings_Advanced' ) ) :

class JWCFE_Admin_Settings_Advanced {

	const OPTION_KEY = 'jwcfe_advanced_settings';

	protected static $_instance = null;

	private $settings_fields = array();

	public function __construct() {
		$this->settings_fields = $this->get_settings_fields();
	}

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/*--------------------------------------------------
	 * Settings Fields Definition
	 *-------------------------------------------------*/

	public function get_settings_fields() {
		return array(
			'enable_label_override' => array(
				'name'    => 'enable_label_override',
				'label'   => __( 'Enable label override for address fields.', 'jwcfe' ),
				'type'    => 'checkbox',
				'value'   => '1',
				'checked' => 1,
			),
			'enable_placeholder_override' => array(
				'name'    => 'enable_placeholder_override',
				'label'   => __( 'Enable placeholder override for address fields.', 'jwcfe' ),
				'type'    => 'checkbox',
				'value'   => '1',
				'checked' => 1,
			),
			'enable_class_override' => array(
				'name'    => 'enable_class_override',
				'label'   => __( 'Enable class override for address fields.', 'jwcfe' ),
				'type'    => 'checkbox',
				'value'   => '1',
				'checked' => 1,
			),
			'enable_priority_override' => array(
				'name'    => 'enable_priority_override',
				'label'   => __( 'Enable priority override for address fields.', 'jwcfe' ),
				'type'    => 'checkbox',
				'value'   => '1',
				'checked' => 1,
			),
			'enable_required_override' => array(
				'name'    => 'enable_required_override',
				'label'   => __( 'Enable required validation override for address fields.', 'jwcfe' ),
				'type'    => 'checkbox',
				'value'   => '1',
				'checked' => 1,
			),
		);
	}

	/*--------------------------------------------------
	 * Static Helpers
	 *-------------------------------------------------*/

	public static function get_advanced_settings() {
		$settings = get_option( self::OPTION_KEY );
		return empty( $settings ) ? false : $settings;
	}

	public static function get_setting( $key ) {
		$settings = self::get_advanced_settings();
		if ( is_array( $settings ) && isset( $settings[ $key ] ) ) {
			return $settings[ $key ];
		}
		// Return default: enabled by default
		return '1';
	}

	public function save_advanced_settings( $settings ) {
		return update_option( self::OPTION_KEY, $settings, 'no' );
	}

	/*--------------------------------------------------
	 * Save / Reset
	 *-------------------------------------------------*/

	private function save_settings() {
		$nonce = isset( $_REQUEST['jwcfe_security_advanced_settings'] )
			? sanitize_text_field( wp_unslash( $_REQUEST['jwcfe_security_advanced_settings'] ) )
			: false;

		if ( ! wp_verify_nonce( $nonce, 'jwcfe_advanced_settings' ) || ! current_user_can( 'manage_woocommerce' ) ) {
			die();
		}

		$settings = array();
		foreach ( $this->settings_fields as $name => $field ) {
			if ( $field['type'] === 'checkbox' ) {
				$settings[ $name ] = ! empty( $_POST[ 'i_' . $name ] ) ? '1' : '';
			} else {
				$value = ! empty( $_POST[ 'i_' . $name ] ) ? $_POST[ 'i_' . $name ] : '';
				$settings[ $name ] = ! empty( $value ) ? wc_clean( wp_unslash( $value ) ) : '';
			}
		}

		$result = $this->save_advanced_settings( $settings );

		if ( $result ) {
			$this->print_notice( __( 'Your changes were saved.', 'jwcfe' ), 'updated' );
		} else {
			$this->print_notice( __( 'Your changes were not saved (or you made none).', 'jwcfe' ), 'error' );
		}
	}

	private function reset_settings() {
		$nonce = isset( $_REQUEST['jwcfe_security_advanced_settings'] )
			? sanitize_text_field( wp_unslash( $_REQUEST['jwcfe_security_advanced_settings'] ) )
			: false;

		if ( ! wp_verify_nonce( $nonce, 'jwcfe_advanced_settings' ) || ! current_user_can( 'manage_woocommerce' ) ) {
			die();
		}

		delete_option( self::OPTION_KEY );
		$this->print_notice( __( 'Settings successfully reset.', 'jwcfe' ), 'updated' );
	}

	/*--------------------------------------------------
	 * Render Page
	 *-------------------------------------------------*/

	public function render_page() {
		if ( isset( $_POST['jwcfe_reset_advanced_settings'] ) ) {
			$this->reset_settings();
		}
		if ( isset( $_POST['jwcfe_save_advanced_settings'] ) ) {
			$this->save_settings();
		}
		if ( isset( $_POST['jwcfe_import_settings'] ) ) {
			$this->save_plugin_settings();
		}
		echo '<div class="jwcfe-adv-page">';
		$this->render_locale_settings_form();
		$this->render_import_export_form();
		echo '</div>';
	}

	private function render_locale_settings_form() {
		$settings = self::get_advanced_settings();

		// Icon map for each setting
		$icons = array(
			'enable_label_override'       => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>',
			'enable_placeholder_override' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h8m-8 6h16"/></svg>',
			'enable_class_override'       => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>',
			'enable_priority_override'    => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4"/></svg>',
			'enable_required_override'    => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
		);

		$descriptions = array(
			'enable_label_override'       => __( 'Allow custom labels to override WooCommerce default address field labels.', 'jwcfe' ),
			'enable_placeholder_override' => __( 'Allow custom placeholder text to override defaults on address fields.', 'jwcfe' ),
			'enable_class_override'       => __( 'Allow custom CSS classes to override address field classes.', 'jwcfe' ),
			'enable_priority_override'    => __( 'Allow field priority/order to be overridden for address fields.', 'jwcfe' ),
			'enable_required_override'    => __( 'Allow required/optional status to be overridden on address fields.', 'jwcfe' ),
		);
		?>
		<form id="jwcfe_advanced_settings_form" method="post" action="">
			<?php wp_nonce_field( 'jwcfe_advanced_settings', 'jwcfe_security_advanced_settings' ); ?>

			<!-- ── Section Card ── -->
			<div class="jwcfe-adv-card">
				<div class="jwcfe-adv-card-header">
					<div class="jwcfe-adv-card-header-icon">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
					</div>
					<div>
						<h3 class="jwcfe-adv-card-title"><?php esc_html_e( 'Locale Override Settings', 'jwcfe' ); ?></h3>
						<p class="jwcfe-adv-card-subtitle"><?php esc_html_e( 'Control which address field properties can be overridden by your custom settings.', 'jwcfe' ); ?></p>
					</div>
				</div>

				<div class="jwcfe-adv-section-label"><?php esc_html_e( 'Address Field Overrides', 'jwcfe' ); ?></div>

				<div class="jwcfe-adv-toggle-list">
					<?php foreach ( $this->settings_fields as $name => $field ) :
						$checked = 1;
						if ( is_array( $settings ) && isset( $settings[ $name ] ) ) {
							$checked = ( $settings[ $name ] === '1' ) ? 1 : 0;
						}
						$fid  = 'jwcfe_adv_' . esc_attr( $name );
						$icon = isset( $icons[ $name ] ) ? $icons[ $name ] : '';
						$desc = isset( $descriptions[ $name ] ) ? $descriptions[ $name ] : '';
					?>
					<div class="jwcfe-adv-toggle-row">
						<div class="jwcfe-adv-toggle-left">
							<span class="jwcfe-adv-toggle-icon"><?php echo $icon; ?></span>
							<div class="jwcfe-adv-toggle-text">
								<span class="jwcfe-adv-toggle-label"><?php echo esc_html( $field['label'] ); ?></span>
								<?php if ( $desc ) : ?>
								<span class="jwcfe-adv-toggle-desc"><?php echo esc_html( $desc ); ?></span>
								<?php endif; ?>
							</div>
						</div>
						<label class="jwcfe-adv-switch" for="<?php echo esc_attr( $fid ); ?>">
							<input type="checkbox"
								id="<?php echo esc_attr( $fid ); ?>"
								name="i_<?php echo esc_attr( $name ); ?>"
								value="1"
								<?php checked( $checked, 1 ); ?> />
							<span class="jwcfe-adv-slider"></span>
						</label>
					</div>
					<?php endforeach; ?>
				</div>

				<div class="jwcfe-adv-card-footer">
					<button type="submit" name="jwcfe_save_advanced_settings" class="jwcfe-adv-btn jwcfe-adv-btn-primary">
						<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
						<?php esc_html_e( 'Save Changes', 'jwcfe' ); ?>
					</button>
					<button type="submit" name="jwcfe_reset_advanced_settings" class="jwcfe-adv-btn jwcfe-adv-btn-secondary"
						onclick="return confirm('<?php esc_attr_e( 'Are you sure? All your changes will be deleted.', 'jwcfe' ); ?>')">
						<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
						<?php esc_html_e( 'Reset to Default', 'jwcfe' ); ?>
					</button>
				</div>
			</div>

		</form>
		<?php
	}

	private function render_checkbox( $field, $settings ) {
		$name    = $field['name'];
		$label   = $field['label'];
		$checked = 1; // default on

		if ( is_array( $settings ) && isset( $settings[ $name ] ) ) {
			$checked = ( $settings[ $name ] === '1' ) ? 1 : 0;
		}

		$fid = 'jwcfe_adv_' . esc_attr( $name );
		?>
		<label for="<?php echo esc_attr( $fid ); ?>">
			<input type="checkbox"
				id="<?php echo esc_attr( $fid ); ?>"
				name="i_<?php echo esc_attr( $name ); ?>"
				value="1"
				<?php checked( $checked, 1 ); ?> />
			<?php echo esc_html( $label ); ?>
		</label>
		<?php
	}

	/*--------------------------------------------------
	 * Import / Export
	 *-------------------------------------------------*/

	private function render_import_export_form() {
		$plugin_settings = $this->prepare_export_data();
		?>
		<form id="jwcfe_import_export_form" method="post" action="">
			<?php wp_nonce_field( 'jwcfe_import_settings', 'jwcfe_import_nonce' ); ?>

			<div class="jwcfe-adv-card" style="margin-top:20px;">
				<div class="jwcfe-adv-card-header">
					<div class="jwcfe-adv-card-header-icon">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
					</div>
					<div>
						<h3 class="jwcfe-adv-card-title"><?php esc_html_e( 'Backup and Import Settings', 'jwcfe' ); ?></h3>
						<p class="jwcfe-adv-card-subtitle"><?php esc_html_e( 'Transfer settings between installs by copying the export data below.', 'jwcfe' ); ?></p>
					</div>
				</div>

				<div class="jwcfe-adv-import-body">
					<p class="jwcfe-adv-import-info">
						<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/></svg>
						<?php esc_html_e( 'To import: paste settings data from another install into the box below, then click "Import Settings".', 'jwcfe' ); ?>
					</p>
					<textarea
						name="i_settings_data"
						rows="6"
						class="jwcfe-adv-textarea"
						spellcheck="false"
						placeholder="<?php esc_attr_e( 'Paste exported settings data here to import...', 'jwcfe' ); ?>"
					><?php echo esc_textarea( $plugin_settings ); ?></textarea>
				</div>

				<div class="jwcfe-adv-card-footer">
					<button type="submit" name="jwcfe_import_settings" class="jwcfe-adv-btn jwcfe-adv-btn-primary">
						<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
						<?php esc_html_e( 'Import Settings', 'jwcfe' ); ?>
					</button>
				</div>
			</div>
		</form>
		<?php
	}

	private function prepare_export_data() {
		$data = array(
			'billing'    => get_option( 'jwcfe_wc_fields_billing' ),
			'shipping'   => get_option( 'jwcfe_wc_fields_shipping' ),
			'additional' => get_option( 'jwcfe_wc_fields_additional' ),
			'block_billing'    => get_option( 'jwcfe_wc_fields_block_billing' ),
			'block_shipping'   => get_option( 'jwcfe_wc_fields_block_shipping' ),
			'block_additional' => get_option( 'jwcfe_wc_fields_block_additional' ),
			'advanced'   => get_option( self::OPTION_KEY ),
		);
		return base64_encode( json_encode( $data ) );
	}

	public function save_plugin_settings() {
		check_admin_referer( 'jwcfe_import_settings', 'jwcfe_import_nonce' );

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die();
		}

		if ( empty( $_POST['i_settings_data'] ) ) {
			$this->print_notice( __( 'No settings data provided.', 'jwcfe' ), 'error' );
			return false;
		}

		$encoded  = sanitize_textarea_field( wp_unslash( $_POST['i_settings_data'] ) );
		$decoded  = base64_decode( $encoded );

		if ( ! $this->is_json( $decoded ) ) {
			$this->print_notice( __( 'The entered import settings data is invalid. Please try again with valid data.', 'jwcfe' ), 'error' );
			return false;
		}

		$settings = json_decode( $decoded, true );
		$saved    = false;

		$map = array(
			'billing'          => 'jwcfe_wc_fields_billing',
			'shipping'         => 'jwcfe_wc_fields_shipping',
			'additional'       => 'jwcfe_wc_fields_additional',
			'block_billing'    => 'jwcfe_wc_fields_block_billing',
			'block_shipping'   => 'jwcfe_wc_fields_block_shipping',
			'block_additional' => 'jwcfe_wc_fields_block_additional',
		);

		foreach ( $map as $key => $option ) {
			if ( isset( $settings[ $key ] ) ) {
				update_option( $option, $settings[ $key ] );
				$saved = true;
			}
		}

		if ( isset( $settings['advanced'] ) ) {
			$this->save_advanced_settings( $settings['advanced'] );
			$saved = true;
		}

		if ( $saved ) {
			$this->print_notice( __( 'Settings imported successfully.', 'jwcfe' ), 'updated' );
		} else {
			$this->print_notice( __( 'Nothing was imported (or data was empty).', 'jwcfe' ), 'error' );
		}
	}

	private function is_json( $string ) {
		json_decode( $string );
		return json_last_error() === JSON_ERROR_NONE;
	}

	/*--------------------------------------------------
	 * Notice Helper
	 *-------------------------------------------------*/

	private function print_notice( $msg, $type = 'updated' ) {
		?>
		<div class="<?php echo esc_attr( $type ); ?> notice is-dismissible" style="margin:10px 0;">
			<p><?php echo esc_html( $msg ); ?></p>
		</div>
		<?php
	}
}

endif;