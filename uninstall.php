<?php
/**
 * Uninstall cleanup for Checkoutly.
 *
 * @package Checkoutly\CheckoutBuilder
 */

declare(strict_types=1);

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'checkoutly_classic_checkout_workflow' );
delete_option( 'checkoutly_routing_mode' );
delete_option( 'checkoutly_buy_now_enabled' );
delete_option( 'checkoutly_buy_now_label' );
