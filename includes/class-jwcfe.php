<?php
/**
 * The file that defines the core plugin class.
 *
 * @link       https://jcodex.com
 * @since      3.5.0
 *
 * @package    woo-checkout-regsiter-field-editor-premium
 * @subpackage woo-checkout-regsiter-field-editor-premium/includes
 */
if (!defined('WPINC')) { die; }

if (!class_exists('JWCFE')) :

class JWCFE {

    protected $plugin_name = 'woo-checkout-regsiter-field-editor-premium';
    protected $version = '1.0.0';
    const TEXT_DOMAIN = 'jwcfe';

    public function __construct() {
        if (defined('JWCFE_VERSION')) {
            $this->version = JWCFE_VERSION;
        }

        // Define the JWCFE_URL constant
        if (!defined('JWCFE_URL')) {
            define('JWCFE_URL', plugin_dir_url(__FILE__));
        }

        // Define the JWCFE_BASE_NAME constant
        if (!defined('JWCFE_BASE_NAME')) {
            define('JWCFE_BASE_NAME', plugin_basename(__FILE__));
        }

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_checkout_hooks();
        // $this->define_public_account_hooks();
        add_action('init', array($this, 'init'));
    }

    private function load_dependencies() {
        if (!function_exists('is_plugin_active')) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        // Get the plugin directory path
        $plugin_dir = plugin_dir_path(__FILE__);

        // Construct the paths to admin and public directories
        // require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-jwcfe-autoloader.php';
        require_once plugin_dir_path( __FILE__ ) . 'class-jwcfe-checkout-fields-utils.php';
        require_once plugin_dir_path( __FILE__ ) . '../public/class-jwcfe-print-invoice-wp-overnight.php';
        require_once plugin_dir_path( __FILE__ ) . '../public/class-jwcfe-print-invoice.php';
        require_once plugin_dir_path( __FILE__ ) . '../public/class-jwcfe-wc-checkout-field-editor-export-handler.php';


        if (!function_exists('initialize_checkout_field_export_handler')) {
            function initialize_checkout_field_export_handler() {
                new JWCFE_WC_Checkout_Field_Editor_Export_Handler();
            }
        }
        
        add_action( 'plugins_loaded', 'initialize_checkout_field_export_handler' );


        
        $autoloader_class_path = $plugin_dir . '/class-jwcfe-autoloader.php';
        $admin_class_path = $plugin_dir . '../admin/class-jwcfe-admin.php';
        $public_class_path = $plugin_dir . '../public/class-jwcfe-public-checkout.php';
        // Include the required files
        if (file_exists($autoloader_class_path)) {
            require_once $autoloader_class_path;
        } else {
            // error_log("Autoloader class file not found: $admin_class_path");
        }

        if (file_exists($admin_class_path)) {
            require_once $admin_class_path;
        } else {
            // error_log("Admin class file not found: $admin_class_path");
        }

        // Load Advanced Settings class
        $advanced_settings_path = $plugin_dir . '../admin/class-jwcfe-admin-settings-advanced.php';
        if (file_exists($advanced_settings_path)) {
            require_once $advanced_settings_path;
        }

        if (file_exists($public_class_path)) {
            require_once $public_class_path;
        } else {
            // error_log("Public class file not found: $public_class_path");
        }

    }

    private function set_locale() {
        add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
    }

    public function load_plugin_textdomain() {
        $locale = apply_filters('plugin_locale', get_locale(), self::TEXT_DOMAIN);

        load_textdomain(self::TEXT_DOMAIN, WP_LANG_DIR . '/woo-checkout-regsiter-field-editor-premium/' . self::TEXT_DOMAIN . '-' . $locale . '.mo');
        load_plugin_textdomain(self::TEXT_DOMAIN, false, dirname(JWCFE_BASE_NAME) . '/languages/');
    }

    private function define_admin_hooks() {

        $plugin_admin = new JWCFE_Admin($this->plugin_name, $this->version);
        add_action('admin_menu', array($plugin_admin, 'admin_menu'));
        add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_admin_scripts'));
        add_filter('woocommerce_screen_ids', array($plugin_admin, 'add_screen_id'));
        add_action('wp_ajax_save_custom_form_fields', array($plugin_admin, 'save_jwcfe_options'));
        add_action('woocommerce_admin_order_data_after_billing_address', array($plugin_admin, 'jwcfe_checkout_field_display_admin_order_meta_billing'), 10, 1);
        add_action('woocommerce_admin_order_data_after_shipping_address', array($plugin_admin, 'jwcfe_checkout_field_display_admin_order_meta_shipping'), 10, 1);
        add_action('woocommerce_order_details_after_order_table', array($plugin_admin, 'jwcfe_order_details_after_customer_details_lite'), 20, 1);
        add_action('wp_ajax_get_product_attributes', array($plugin_admin, 'get_all_variations_of_product'));
    }

    private function define_public_checkout_hooks() {
        $plugin_checkout = new JWCFE_Public_Checkout($this->plugin_name, $this->version);
        add_action('wp_enqueue_scripts', array($plugin_checkout, 'jwcfe_checkout_fields_frontend_scripts'));
        $plugin_checkout->define_public_checkout_hooks();
    }


    
    

    public function init() {
        $this->define_constants();
    }

    private function define_constants ( ) {
        !defined('JWCFE_STORE_URL') && define('JWCFE_STORE_URL', 'https://jcodex.com/');
        !defined('JWCFE_ITEM_ID') && define('JWCFE_ITEM_ID', 4111);
        !defined('JWCFE_ASSETS_URL_ADMIN') && define('JWCFE_ASSETS_URL_ADMIN', JWCFE_URL . 'admin/assets/');
        !defined('JWCFE_ASSETS_URL_PUBLIC') && define('JWCFE_ASSETS_URL_PUBLIC', JWCFE_URL . 'public/assets/');
    }

    public function run() {
        // Run the plugin
    }
}

endif;