<?php

// Ensure the class doesn't exist to avoid redeclaration
if ( ! class_exists( 'JWCFE_Print_Invoice_WP_Overnight' ) ) :

class JWCFE_Print_Invoice_WP_Overnight {

    // Constructor to hook into WooCommerce PDF Invoices & Packing Slips
    public function __construct() {
        // Hook to add custom fields to the PDF invoice and packing slip
        add_action( 'wpo_wcpdf_after_order_details', array( $this, 'jwcfe_add_custom_fields_to_pdf' ), 10, 2 );
        add_action( 'wpo_wcpdf_after_order_data', array( $this, 'jwcfe_add_invoice_fields_to_pdf' ), 10, 2 );
    }

    /**
     * Add custom checkout fields to the WooCommerce PDF invoice and packing slip.
     *
     * @param string $document_type The document type (invoice, packing slip, etc.).
     * @param WC_Order $order The order object.
     */
    public function jwcfe_add_custom_fields_to_pdf( $document_type, $order ) {
        // Ensure $order is a valid WC_Order object
        if ( ! is_a( $order, 'WC_Order' ) || ! in_array( $document_type, ['invoice', 'packing-slip'] ) ) {
            return;
        }

        // Add a section heading specific to the document type
        if ( 'invoice' === $document_type ) {
            echo '<h3>Custom Checkout Fields (Invoice)</h3>';
        } elseif ( 'packing-slip' === $document_type ) {
            echo '<h3>Custom Checkout Fields (Packing Slip)</h3>';
        }

        // Get the custom order meta data
        $order_meta = $order->get_meta_data();

        // Get WooCommerce checkout fields (billing, shipping, and additional)
        $checkout_fields = WC()->checkout()->get_checkout_fields();

        // Loop through the order meta to display relevant fields
        foreach ( $order_meta as $meta ) {
            $field_key = $meta->key;
            $field_value = $meta->value;

            // Exclude unwanted meta keys
            if ( ! empty( $field_key ) 
                && ! in_array( $field_key, ['_order_key', '_order_currency'] ) 
                && strpos( $field_key, '_' ) !== 0 // Skip internal meta fields
                && ! in_array( $field_key, ['_billing_address_index', '_shipping_address_index', 'is_vat_exempt'] ) ) {

                // Check for the label in WooCommerce fields
                if ( isset( $checkout_fields['billing'][ $field_key ] ) ) {
                    $label = $checkout_fields['billing'][ $field_key ]['label'];
                } elseif ( isset( $checkout_fields['shipping'][ $field_key ] ) ) {
                    $label = $checkout_fields['shipping'][ $field_key ]['label'];
                } elseif ( isset( $checkout_fields['additional'][ $field_key ] ) ) {
                    $label = $checkout_fields['additional'][ $field_key ]['label'];
                } else {
                    // Fallback to the field key as the label
                    $label = $field_key;
                }

                // Handle arrays (for select and checkbox fields)
                if ( is_array( $field_value ) ) {
                    echo '<p><strong>' . esc_html( $label ) . ':</strong> ';
                    echo esc_html( implode( ', ', $field_value ) );
                    echo '</p>';
                } else {
                    echo '<p><strong>' . esc_html( $label ) . ':</strong> ' . esc_html( $field_value ) . '</p>';
                }
            }
        }
    }

    /**
     * Add selected custom fields to the PDF invoice.
     *
     * @param string $document_type The document type (invoice).
     * @param WC_Order $order The order object.
     */
   
    public function jwcfe_add_invoice_fields_to_pdf($document_type, $order) {
        if ('invoice' === $document_type) {
            // Check if the checkbox is enabled
            $is_checkbox_enabled = get_option('pdf', ''); // Retrieve the saved checkbox value
            if ($is_checkbox_enabled !== '1') {
                return; // Exit if the checkbox is not checked
            }
    
            // Get the selected custom fields for the invoice
            $selected_pdfinvoice_fields = get_option('jwcfe_selected_pdfinvoice_fields', array());
    
            // Check if the selected fields are set and if it's an array
            if (!empty($selected_pdfinvoice_fields) || is_array($selected_pdfinvoice_fields)) {
                foreach ($selected_pdfinvoice_fields as $field_name) {
                    // Get the custom field value from the order meta
                    $custom_field_value = $order->get_meta($field_name);
    
                    if ($custom_field_value) {
                        // Display the custom field value on the invoice
                        echo '<p style="padding-top:5px;"><strong>' . esc_html($field_name) . ':</strong> ' . esc_html($custom_field_value) . '</p>';
                    }
                }
            }
        }
    }
    

    /**
     * Add selected custom fields to the packing slip.
     *
     * @param string $document_type The document type (packing slip).
     * @param WC_Order $order The order object.
     */
    
 
    
}

// Create an instance of the class to activate the hooks
new JWCFE_Print_Invoice_WP_Overnight();

endif;
