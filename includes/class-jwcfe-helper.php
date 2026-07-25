<?php
/**
 * this file includes to intilize plugin functions
 * 
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if(!class_exists('JWCFE_Helper')):

class JWCFE_Helper {

	function __construct() {}

		public static function get_current_tab()
		{
			$allowed_tabs = array('checkoutfields', 'accounts'); // include accounts
			$tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'checkoutfields';

			return in_array($tab, $allowed_tabs, true) ? $tab : 'checkoutfields';
		}

		public static function get_current_ctype()
		{
			$allowed_ctypes = array('classic', 'block'); // include accounts
			$ctype = isset($_GET['c_type']) ? sanitize_text_field($_GET['c_type']) : 'classic';

			return in_array($ctype, $allowed_ctypes, true) ? $ctype : 'classic';
		}
		
		public static function get_fields($key){

				$tab = self::get_current_tab();
				$ctype = self::get_current_ctype();
				// echo $tab;
			
				if($tab==='checkoutfields' && $ctype == 'classic'){
					$fields = array_filter(get_option('jwcfe_wc_fields_'. $key, array()));

					if(empty($fields) || sizeof($fields) == 0){
						if($key === 'billing' || $key === 'shipping'){
							$fields = WC()->countries->get_address_fields(WC()->countries->get_base_country(), $key . '_');
						} else if($key === 'additional'){
		
							$fields = array(
		
								'order_comments' => array(
									'type'        => 'textarea',
									'class'       => array('notes'),
									'label'       => esc_html__('Order Notes', 'jwcfe'),
									'placeholder' => _x('Notes about your order, e.g. special notes for delivery.', 'placeholder', 'jwcfe')
								)
							);
						}
							else if($key === 'account'){
		
							$fields = array(
								'account_username' => array(
									'type' => 'text',
									'label' => esc_html__('Email address', 'jwcfe')
								),
		
								'account_password' => array(
									'type' => 'password',
									'label' => esc_html__('Password', 'jwcfe')
								)
							);
						}
					}
					return $fields;
				}else if ($tab==='checkoutfields' && $ctype === 'block') {
					$fields = maybe_unserialize(get_option('jwcfe_wc_fields_block_' . $key, array()));
				
					if (!is_array($fields)) {
						$fields = [];
					}
				
					$fields = array_filter($fields);
					
					if (empty($fields) || sizeof($fields) == 0) {
						if ($key === 'billing') {
							// Show only the billing_email field
							$fields = [
								'billing_email' => [
									'label'       => esc_html__('Email address', 'jwcfe'),
									'required'    => 1,
									'type'        => 'email',
									'class'       => ['form-row-wide'],
									'validate'    => ['email'],
									'autocomplete' => 'email',
									'priority'    => 100,
									'placeholder' => '',
									'clear'       => 0,
									'input_class' => ['jwcfe-input-field'],
								]
							];
						} elseif ($key === 'shipping') {
							// Show only the specific shipping fields when empty
							$fields = [
								'shipping_first_name'  => ['type' => 'text',    'label' => esc_html__('First name', 'jwcfe'),                   'required' => 1, 'enabled' => 1],
								'shipping_last_name'   => ['type' => 'text',    'label' => esc_html__('Last name', 'jwcfe'),                    'required' => 1, 'enabled' => 1],
								'shipping_company'     => ['type' => 'text',    'label' => esc_html__('Company name', 'jwcfe'),                 'required' => 0, 'enabled' => 1],
								'shipping_country'     => ['type' => 'country', 'label' => esc_html__('Country / Region', 'jwcfe'),            'required' => 1, 'enabled' => 1],
								'shipping_address_1'   => ['type' => 'text',    'label' => esc_html__('Street address', 'jwcfe'),              'required' => 1, 'enabled' => 1],
								'shipping_address_2'   => ['type' => 'text',    'label' => esc_html__('Apartment, suite, unit, etc.', 'jwcfe'), 'required' => 0, 'enabled' => 1],
								'shipping_city'        => ['type' => 'text',    'label' => esc_html__('Town / City', 'jwcfe'),                 'required' => 1, 'enabled' => 1],
								'shipping_state'       => ['type' => 'state',   'label' => esc_html__('State / County', 'jwcfe'),              'required' => 1, 'enabled' => 1],
								'shipping_postcode'    => ['type' => 'text',    'label' => esc_html__('Postcode / ZIP', 'jwcfe'),              'required' => 1, 'enabled' => 1],
								'shipping_phone'       => ['type' => 'tel',     'label' => esc_html__('Phone', 'jwcfe'),                        'required' => 0, 'enabled' => 1],
							];
						} elseif ($key === 'additional') {
							$fields = [
								'order_comments' => [
									'type'        => 'textarea',
									'class'       => ['notes'],
									'label'       => esc_html__('Order Notes', 'jwcfe'),
									'placeholder' => _x('Notes about your order, e.g. special notes for delivery.', 'placeholder', 'jwcfe')
								]
							];
						} elseif ($key === 'account') {
							$fields = [
								'account_username' => [
									'type'  => 'text',
									'label' => esc_html__('Email address', 'jwcfe')
								],
								'account_password' => [
									'type'  => 'password',
									'label' => esc_html__('Password', 'jwcfe')
								]
							];
						}
					}
					
					// Ensure `$fields` is always an array before using foreach
					if (!is_array($fields)) {
						$fields = [];
					}
					
					return $fields;
					
				}
				
		}
	
		public static function jwcfe_woocommerce_version_check( $version = '3.0' ) {
		if ( function_exists( 'jwcfe_is_woocommerce_active' ) && jwcfe_is_woocommerce_active() ) {
				global  $woocommerce ;
				if ( version_compare( $woocommerce->version, $version, ">=" ) ) {
					return true;
				}
			}
			
			return false;
	}


	public static function get_version_jwcfe($license_key){
		$current_site_url = get_site_url();
		$response = wp_remote_post( 'https://jcodex.com/', array(
			'body' => array(
				'edd_action' => 'get_version',
				'item_id' => 4111,
				'license' =>  $license_key,
				'url' => $current_site_url
			)
		) );

		if (is_wp_error($response)) {
			$error_message = $response->get_error_message();
			return "Error: $error_message";
		} else {
			$response_code = wp_remote_retrieve_response_code($response);
			if ($response_code == 200) {
				$response_body = wp_remote_retrieve_body($response);
				$response_body = json_decode($response_body,true);
				return $response_body;
			} else {
				return "Error: Unexpected response code $response_code";
			}
		}
	}
}

endif;