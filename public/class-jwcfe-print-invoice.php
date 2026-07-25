<?php

// Ensure the class doesn't exist to avoid redeclaration
if ( ! class_exists( 'JWCFE_Print_Invoice' ) ) :

class JWCFE_Print_Invoice {

    // Constructor to hook the action into WooCommerce PDF Invoices & Packing Slips
    public function __construct() {
        // Hook to add custom fields to the invoice PDF
        add_action( 'wc_pip_after_body', array( $this, 'jwcfe_add_all_custom_fields_to_invoice' ), 10, 4 );
    }

    /**
     * Function to add custom fields to the invoice PDF.
     *
     * @param string $type       The type of document (invoice, packing-list).
     * @param string $action     The action being performed.
     * @param object $document   The document object.
     * @param object $order      The order object.
     */
    public function jwcfe_add_all_custom_fields_to_invoice( $type, $action, $document, $order ) {
        // Only process for invoices
        if ( 'invoice' === $type ) {
            // Output custom heading for the fields
            echo '<h3>Custom Checkout Fields</h3>';
    
            // Get order meta data
            $order_meta = $order->get_meta_data();
            // Get the WooCommerce checkout fields
            $checkout_fields = WC()->checkout()->get_checkout_fields();
    
            // Loop through the order meta and display each custom field
            foreach ( $order_meta as $meta ) {
                $field_key = $meta->key;
                $field_value = $meta->value;
    
                // Exclude unwanted meta keys
                if ( ! empty( $field_key ) 
                     && ! in_array( $field_key, ['_order_key', '_order_currency'] ) 
                     && strpos( $field_key, '_' ) !== 0 // Exclude fields starting with '_'
                     && ! in_array( $field_key, ['_billing_address_index', '_shipping_address_index', 'is_vat_exempt'] ) ) {
                    
                    // Check if the field exists in the checkout fields array
                    if ( isset( $checkout_fields['billing'][ $field_key ] ) ) {
                        // Get the label for the field
                        $label = $checkout_fields['billing'][ $field_key ]['label'];
                    } elseif ( isset( $checkout_fields['shipping'][ $field_key ] ) ) {
                        // Get the label for the field
                        $label = $checkout_fields['shipping'][ $field_key ]['label'];
                    } else {
                        // If the field is not found, use the field key as a fallback
                        $label = $field_key;
                    }
    
                    // Check if the field value is an array
                    if ( is_array( $field_value ) ) {
                        echo '<p><strong>' . esc_html( $label ) . ':</strong> ';
                        // Loop through the array and output each item
                        echo implode( ', ', array_map( 'esc_html', $field_value ) );
                        echo '</p>';
                    } else {
                        // Style the key in bold and the value in normal text
                        echo '<p><strong>' . esc_html( $label ) . ':</strong> ' . esc_html( $field_value ) . '</p>';
                    }
                }
            }
        }
    }
}

// Create an instance of the class to ensure the hook is added
new JWCFE_Print_Invoice();

endif;

?>
