=== Checkoutly – Custom Checkout Builder for WooCommerce ===
Contributors: checkoutly
Tags: woocommerce, checkout, checkout fields, multistep checkout, checkout builder
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 7.4
Requires Plugins: woocommerce
Stable tag: 0.7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Build a configurable WooCommerce classic checkout workflow with steps, native checkout fields, and custom checkout fields.

== Description ==

Checkoutly – Custom Checkout Builder for WooCommerce adds a dashboard-style builder for WooCommerce classic shortcode checkout pages.

The plugin lets store owners organize native WooCommerce checkout fields and basic custom fields into a guided checkout workflow. Field values are saved through WooCommerce order meta APIs and can be shown in order details, customer emails, and the thank-you page when enabled.

This initial release focuses on the classic WooCommerce checkout shortcode. WooCommerce continues to handle the cart, shipping, taxes, payments, order creation, and checkout validation while Checkoutly controls field organization and the customer-facing step layout.

= Current features =

* Classic WooCommerce checkout shortcode support.
* One to three checkout steps in the free version.
* Drag native WooCommerce checkout fields and custom fields between steps.
* Configure label, placeholder, default value, validation, field width, required state, enabled state, and display locations.
* Multi-step controls for layout, indicators, connectors, colors, navigation buttons, and step behavior.
* Optional Skip Cart and Buy Now routing.
* HPOS-compatible order meta storage.

== Installation ==

1. Upload the `checkoutly` folder to the `/wp-content/plugins/` directory, or install it from the WordPress Plugins screen.
2. Activate the plugin through the Plugins screen in WordPress.
3. Make sure WooCommerce is active.
4. Go to WooCommerce > Checkoutly Checkout > Checkout Builder.
5. Configure the checkout workflow and save changes.

== Usage ==

1. Create or edit your WooCommerce checkout page and make sure it uses the classic checkout shortcode: `[woocommerce_checkout]`.
2. Open Checkoutly Checkout > Checkout Builder in the WordPress dashboard.
3. Review the default checkout steps. The first step includes WooCommerce contact, billing, and shipping fields by default.
4. Drag fields between steps to organize the checkout flow.
5. Open a field's settings to change its label, placeholder, default value, required state, enabled state, width, and display locations.
6. Drag basic custom fields from the components panel into a step when you need extra customer information.
7. Adjust the multi-step settings for indicators, connectors, colors, navigation text, and step behavior.
8. Save the builder and test checkout with a product in the cart.

For routing settings, go to WooCommerce > Settings > Products > Checkoutly Checkout Routing. You can keep the standard product-to-cart-to-checkout flow, enable Skip Cart, or show a separate Buy Now button on simple product pages.

== Frequently Asked Questions ==

= Does this plugin support WooCommerce block checkout? =

This version is built for the classic WooCommerce checkout shortcode. Block checkout support is not included in this release.

= Which checkout shortcode should I use? =

Use `[woocommerce_checkout]` on the checkout page. Checkoutly reorganizes the classic checkout form after WooCommerce renders it.

= Does it replace WooCommerce checkout processing? =

No. Checkoutly keeps WooCommerce responsible for cart, shipping, taxes, payment, and order processing.

= Are custom field values saved to orders? =

Yes. Enabled custom fields are sanitized and saved to WooCommerce order meta using the `_checkoutly_` prefix.

== Changelog ==

= 0.7.2 =
* Added classic checkout builder workflow.
* Added custom checkout field storage and display settings.
* Added multi-step frontend controls.
* Added Skip Cart and Buy Now routing.

== Upgrade Notice ==

= 0.7.2 =
Initial public-ready classic checkout builder release.
