<?php
/**
 * Plugin Name: Checkout Field Editor for Woocommerce - Checkout Manager
 * Description: Easily Add, Edit, Remove or re-arrange any fields on WooCommerce Checkout page.
 * Author:      Jcodex
 * Version:     2.5.4
 * Author URI:  https://www.jcodex.com
 * Plugin URI:  https://www.jcodex.com
 * Text Domain: jwcfe
 * Domain Path: /languages/
 * WC requires at least: 3.0.0
 * WC tested up to: 10.8.1
 *
 * Copyright (C) 2018-2026 Jcodex Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
// Create a helper function for easy SDK access.

if (!defined('ABSPATH')) {
    exit;
}
// Avoid defining constants if they are already defined.
if (!defined('JWCFE_VERSION')) {
    define('JWCFE_VERSION', '2.5.0');
}

if (!defined('JWCFE_BASE_NAME')) {
    define('JWCFE_BASE_NAME', plugin_basename(__FILE__));
}

if (!defined('JWCFE_PATH')) {
    define('JWCFE_PATH', plugin_dir_path(__FILE__));
}

if (!defined('JWCFE_URL')) {
    define('JWCFE_URL', plugins_url('/', __FILE__));
}



    register_activation_hook( __FILE__, 'jwcfe_activate');
    add_action( 'admin_init', 'jwcfe_activation_redirect');

    /**
     * Plugin activation callback. Registers option to redirect on next admin load.
     */

    function jwcfe_activate() {

        if (!class_exists( 'WooCommerce' )) {
            deactivate_plugins( JWCFE_BASE_NAME );
            wp_die( __( "WooCommerce is required for this plugin to work properly. Please activate WooCommerce.", 'jwcfe' ), "", array( 'back_link' => 1 ) );
        }
        
        if (is_plugin_active('woo-checkout-regsiter-field-editor-pro/main.php')) {
            deactivate_plugins('woo-checkout-regsiter-field-editor-pro/main.php');
        }
        
        add_option( 'jwcfe_activation_redirect', true );

        // Activation timestamp: review notice becomes eligible exactly this many seconds later (see jwcfe_get_review_notice_delay_seconds).
        update_option( 'jwcfe_activated_at', time() );
    }

    /**
     * Seconds to wait after plugin activation before showing the review notice (default: 3 days; first admin request after this passes may display it).
     *
     * @return int
     */
    function jwcfe_get_review_notice_delay_seconds() {
        return (int) apply_filters( 'jwcfe_review_notice_delay_seconds', 3 * DAY_IN_SECONDS );
    }

    /**
     * Admin review notice shown 3 days after activation (see jwcfe_get_review_notice_delay_seconds()).
     */
    add_action( 'admin_init', function () {
        if ( ! is_admin() || ! is_user_logged_in() ) {
            return;
        }

        // Backfill only if activation hook never stored a timestamp (e.g. manual copy of plugin files). Timer starts from this admin hit, not real activation.
        if ( false === get_option( 'jwcfe_activated_at', false ) && current_user_can( 'activate_plugins' ) ) {
            update_option( 'jwcfe_activated_at', time() );
        }

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        if ( isset( $_GET['jwcfe_dismiss_review_notice'] ) ) {
            $nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
            if ( wp_verify_nonce( $nonce, 'jwcfe_dismiss_review_notice' ) ) {
                update_user_meta( get_current_user_id(), 'jwcfe_review_notice_dismissed', 1 );
            }
        }
    } );

    /**
     * True when viewing this plugin's settings screen (notice is shown inline there, not in admin_notices).
     */
    function jwcfe_is_plugin_settings_admin_screen() {
        return isset( $_GET['page'] ) && sanitize_text_field( wp_unslash( $_GET['page'] ) ) === 'jwcfe_checkout_register_editor';
    }

    /**
     * Review notice: 'inline' = below header on plugin page (full width). 'global' = WordPress admin_notices strip on other admin pages.
     *
     * @param string $context 'inline'|'global'.
     */
    function jwcfe_render_review_notice( $context = 'inline' ) {
        if ( ! is_admin() || ! is_user_logged_in() ) {
            return;
        }
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        if ( get_user_meta( get_current_user_id(), 'jwcfe_review_notice_dismissed', true ) ) {
            return;
        }

        $activated_at = (int) get_option( 'jwcfe_activated_at', 0 );
        $delay        = jwcfe_get_review_notice_delay_seconds();
        if ( ! $activated_at || ( time() - $activated_at ) < $delay ) {
            return;
        }

        $context = ( 'global' === $context ) ? 'global' : 'inline';
        $extra_class = ( 'global' === $context ) ? 'jwcfe-review-notice--global' : 'jwcfe-review-notice--inline';

        $review_url  = 'https://wordpress.org/support/plugin/woo-checkout-regsiter-field-editor/reviews/#new-post';
        $dismiss_url = wp_nonce_url(
            add_query_arg( 'jwcfe_dismiss_review_notice', '1' ),
            'jwcfe_dismiss_review_notice'
        );

        $logo_url = plugin_dir_url( __FILE__ ) . 'admin/assets/logo-blue.svg';
        ?>
        <style>
            #jwcfe-review-notice.jwcfe-review-notice--inline {
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
                margin: 12px 0 16px 0;
                border-left: none;
                padding: 0;
                border-radius: 6px;
                overflow: hidden;
                box-shadow: 0 1px 4px rgba(0,0,0,0.1);
            }
            #jwcfe-review-notice.jwcfe-review-notice--global {
                max-width: 100%;
                box-sizing: border-box;
                border-left: none;
                padding: 0;
                border-radius: 6px;
                overflow: hidden;
                box-shadow: 0 1px 4px rgba(0,0,0,0.1);
            }
            .jwcfe-notice-inner {
                display: flex;
                align-items: center;
                gap: 15px;
                padding: 12px 16px;
                background: #f0f6ff;
                border-left: 4px solid #2271b1;
                border-radius: 6px;
            }
            .jwcfe-notice-logo img {
                width: 40px;
                height: 40px;
                display: block;
            }
            .jwcfe-notice-text {
                flex: 1;
                font-size: 13px;
                color: #1d2327;
                line-height: 1.5;
            }
            .jwcfe-notice-text strong {
                display: block;
                margin-bottom: 2px;
                font-size: 13px;
            }
            .jwcfe-notice-actions {
                margin-top: 6px;
            }
            .jwcfe-notice-actions a.jwcfe-btn-review {
                display: inline-block;
                background: #2271b1;
                color: #fff;
                padding: 5px 14px;
                border-radius: 4px;
                text-decoration: none;
                font-size: 12px;
                margin-right: 8px;
            }
            .jwcfe-notice-actions a.jwcfe-btn-review:hover {
                background: #135e96;
            }
            .jwcfe-notice-actions a.jwcfe-btn-dismiss {
                color: #2271b1;
                text-decoration: underline;
                font-size: 12px;
            }
        </style>

        <?php
        // WordPress common.js moves div.notice after the first h1 unless it has class .inline.
        $notice_classes = array( 'notice', 'is-dismissible', $extra_class );
        if ( 'inline' === $context ) {
            $notice_classes[] = 'inline';
        }
        ?>
        <div class="<?php echo esc_attr( implode( ' ', $notice_classes ) ); ?>" id="jwcfe-review-notice">
            <div class="jwcfe-notice-inner">
                <div class="jwcfe-notice-logo">
                    <img src="<?php echo esc_url( $logo_url ); ?>" alt="JCodex Logo" />
                </div>
                <div class="jwcfe-notice-text">
                    <strong>Loving WooCommerce Checkout Field Editor? 🙌</strong>
                    If this plugin helped you, a quick review would mean a lot
                    <div class="jwcfe-notice-actions">
                        <a href="<?php echo esc_url( $review_url ); ?>" target="_blank" rel="noopener noreferrer" class="jwcfe-btn-review">
                            ⭐ Leave a Review
                        </a>
                        <a href="<?php echo esc_url( $dismiss_url ); ?>" class="jwcfe-btn-dismiss">
                            Dismiss
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    add_action(
        'admin_notices',
        function () {
            if ( ! function_exists( 'jwcfe_is_plugin_settings_admin_screen' ) || jwcfe_is_plugin_settings_admin_screen() ) {
                return;
            }
            jwcfe_render_review_notice( 'global' );
        }
    );

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'jwcfe_add_plugin_action_links');
function jwcfe_add_plugin_action_links($links) {
    // Add Settings link
    $settings_url = admin_url('admin.php?page=jwcfe_checkout_register_editor');
    $settings_link = '<a href="' . esc_url($settings_url) . '">' . __('Settings', 'jwcfe') . '</a>';

   

    // Add Upgrade to Pro link
    $pro_url = 'https://jcodex.com/plugins/woocommerce-custom-checkout-field-editor/';
    $pro_link = '<a href="' . esc_url($pro_url) . '" style="color: #215125ff; font-weight: bold;" target="_blank">' . __('Get Pro', 'jwcfe') . '</a>';

    // Insert links in custom order: Settings | Deactivate | Upgrade to Pro
    if (isset($links['deactivate'])) {
        $deactivate_link = $links['deactivate'];
        unset($links['deactivate']);
    } else {
        $deactivate_link = '';
    }

    $custom_links = array();
    $custom_links['settings'] = $settings_link;
    if ($deactivate_link) {
        $custom_links['deactivate'] = $deactivate_link;
    }
    $custom_links['pro'] = $pro_link;

    return $custom_links;
}

    function jwcfe_activation_redirect() {
        if (is_plugin_active('woocommerce/woocommerce.php')) {
            if (get_option('jwcfe_activation_redirect', false)) {
                delete_option('jwcfe_activation_redirect');
                wp_safe_redirect(admin_url('admin.php?page=jwcfe_checkout_register_editor'));
                exit;
            }
        }
    }

    if (jwcfe_is_woocommerce_active()) {

        if (!class_exists('JWCFE')) {
            require_once JWCFE_PATH . 'includes/class-jwcfe.php';
        }
        if (!function_exists('run_jwcfe')) {
            function run_jwcfe() {
                $plugin = new JWCFE();
            }
        }
        run_jwcfe();
    }


    function jwcfe_is_woocommerce_active() {
        $active_plugins = (array) get_option('active_plugins', array());
        if (is_multisite()) {
            $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
        }
        return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins);
    }



    add_action('before_woocommerce_init', function () {
        if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        }
    });
    
    /**
     * Hide "Additional information" heading in WooCommerce emails while keeping the fields.
     */
    add_filter( 'woocommerce_email_additional_information_heading', function ( $heading ) {
        return '';
    } );

    /**
     * Some WooCommerce email templates use the order meta heading filter instead.
     * Hide it too, while leaving the actual meta rows intact.
     */
    add_filter( 'woocommerce_email_order_meta_heading', function ( $heading, $sent_to_admin = false, $order = null ) {
        return '';
    }, 10, 3 );

    /**
     * Last-resort: remove the literal "Additional information" heading in:
     * - WooCommerce emails (including admin previews)
     * - Order received page / My Account → View order
     *
     * Some templates output the heading as a hard-coded translated string, not a filter.
     */
    $GLOBALS['jwcfe_is_rendering_wc_email'] = false;
    add_action( 'woocommerce_email_header', function () {
        $GLOBALS['jwcfe_is_rendering_wc_email'] = true;
    }, 0 );
    add_action( 'woocommerce_email_footer', function () {
        $GLOBALS['jwcfe_is_rendering_wc_email'] = false;
    }, PHP_INT_MAX );

    $jwcfe_maybe_hide_additional_information_heading = function ( $translated, $text, $domain ) {
        // This heading is normally a WooCommerce string, but some themes/plugins may output it from other domains.
        // We keep matching strict to the literal text to avoid side effects.
        $t1 = strtolower( trim( (string) $text ) );
        $t2 = strtolower( trim( (string) $translated ) );

        $matches_heading = in_array( $t1, [ 'additional information', 'additional information:' ], true )
            || in_array( $t2, [ 'additional information', 'additional information:' ], true );

        if ( ! $matches_heading ) {
            return $translated;
        }

        $is_order_details = ( function_exists( 'is_order_received_page' ) && is_order_received_page() )
            || ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'view-order' ) );

        if ( ! empty( $GLOBALS['jwcfe_is_rendering_wc_email'] ) || $is_order_details ) {
            return '';
        }

        return $translated;
    };

    add_filter( 'gettext', $jwcfe_maybe_hide_additional_information_heading, 20, 3 );
    add_filter( 'gettext_with_context', function ( $translated, $text, $context, $domain ) use ( $jwcfe_maybe_hide_additional_information_heading ) {
        return $jwcfe_maybe_hide_additional_information_heading( $translated, $text, $domain );
    }, 20, 4 );

    /**
     * WooCommerce Blocks: remove the "Additional information" heading on the
     * Order Confirmation page additional fields wrapper block.
     *
     * Block name: woocommerce/order-confirmation-additional-fields-wrapper
     */
    add_filter( 'render_block', function ( $block_content, $block ) {
        if (
            ! is_array( $block ) ||
            empty( $block['blockName'] ) ||
            $block['blockName'] !== 'woocommerce/order-confirmation-additional-fields-wrapper'
        ) {
            return $block_content;
        }

        // Remove only the heading, keep the fields list.
        return preg_replace( '#<h2\b[^>]*>\s*Additional information\s*</h2>#i', '', (string) $block_content );
    }, 20, 2 );


require_once JWCFE_PATH . 'includes/class-jwcfe-deactivation-feedback.php';

           