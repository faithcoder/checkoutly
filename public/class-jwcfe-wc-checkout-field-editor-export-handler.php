<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class JWCFE_WC_Checkout_Field_Editor_Export_Handler {    
    private $fields;

    public function __construct() {
        $this->fields = $this->get_fields();

        // Hook into WooCommerce CSV export filters
        add_filter( 'wc_customer_order_csv_export_order_headers', array( $this, 'jwcfe_order_csv_export_order_headers' ), 10, 2 );
        add_filter( 'wc_customer_order_csv_export_order_row', array( $this, 'jwcfe_customer_order_csv_export_order_row' ), 10, 4 );


    }

   
	public function jwcfe_order_csv_export_order_headers($headers, $csv_generator) {
		$field_headers = array();
	
		// Get the selected fields
		$selected_fields = get_option('jwcfe_selected_csv_fields', array());
	
		// Add only the selected fields as headers
		foreach ($this->fields as $name => $options) {
			if (in_array($name, $selected_fields)) {
				$field_headers[$name] = $name;
			}
		}
	
		return array_merge($headers, $field_headers);
	}
	
	public function jwcfe_customer_order_csv_export_order_row( $order_data, $order, $csv_generator ) {
		$field_data = array();
		
		// Get the selected fields from the settings page
		$selected_fields = get_option('jwcfe_selected_csv_fields', array());
		// error_log(print_r($selected_fields));
		if ( ! empty( $selected_fields ) && is_array( $selected_fields ) ) {
			foreach ( $selected_fields as $field_key ) {
				// Get the meta value for the selected field
				$field_value = $order->get_meta( $field_key, true );
				
				// Add to field data if value exists
				if ( $field_value ) {
					$field_data[ $field_key ] = is_array( $field_value ) 
						? implode( ', ', $field_value ) 
						: $field_value;
				} else {
					$field_data[ $field_key ] = 'N/A';
				}
			}
		}
	
		$new_order_data = array();
	
		// Check export format and handle data accordingly
		if ( isset( $csv_generator->order_format ) && 
			 ( $csv_generator->order_format === 'default_one_row_per_item' || $csv_generator->order_format === 'legacy_one_row_per_item' ) ) {
			foreach ( $order_data as $data ) {
				$new_order_data[] = array_merge( $field_data, (array) $data );
			}
		} else {
			$new_order_data = array_merge( $field_data, $order_data );
		}
	
		return $new_order_data;
	}
	
    /**
     * Retrieve all checkout fields from options.
     */
    private function get_fields() {
        $fields = array();

        // Get billing fields
        $billing_fields = get_option( 'jwcfe_wc_fields_billing', array() );
        $fields = array_merge( $fields, $billing_fields );

        // Get shipping fields
        $shipping_fields = get_option( 'jwcfe_wc_fields_shipping', array() );
        $fields = array_merge( $fields, $shipping_fields );

        // Get additional fields
        $additional_fields = get_option( 'jwcfe_wc_fields_additional', array() );
        $fields = array_merge( $fields, $additional_fields );

        return $fields;
    }
}
