=== Halk Bank Payment Gateway For Woocommerce with working hash ver3 ===

- Developer: mil-los
- Contributors: m1tk00, Webpigment
- Tags: woocommerce, payment gateway, gateway, manual payment, halk, halkbank, nestpay
- Requires at least: 3.8
- Tested up to: 6.4.3
- Requires WooCommerce at least: 3.2
- Tested WooCommerce up to: 8.4.0
- Stable Tag: 1.2.1
- License: GPLv3
- Requires PHP: 5.2.4
- License URI: http://www.gnu.org/licenses/gpl-3.0.html

== **Notice of Hash ver3 Update** ==
> Due to a change in the hashing version, we are pleased to announce that we have developed a new plugin capable of handling the new ver3 hashing. This plugin is fully functional starting 01-April-2024.

== Description ==

> **Requires: WooCommerce 2.1+**

This plugin allows your store to make payments via Halk Bank payment service.

== Installation ==

1. Be sure you're running WooCommerce 2.1+ in your shop.
2. You can: (1) upload the entire `halk-bank-payment-woocommerce` folder to the `/wp-content/plugins/` directory, (2) upload the .zip file with the plugin under **Plugins &gt; Add New &gt; Upload**
3. Activate the plugin through the **Plugins** menu in WordPress
4. Go to **WooCommerce &gt; Settings &gt; Payments** and select "Halk Bank Payment" to configure.
5. Make sure you fill in all Halk Bank fields.

== Frequently Asked Questions ==

**What is the text domain for translations?**
The text domain is `halk-payment-gateway-for-woocommerce`.

== Changelog ==
1.2 Add live/test mode. Add transaction type.
1.1.1 Clean some code.
1.1 Add filter support for currency switcher. Filter name halk_amount_fix
Example for EUR to MKD
add_filter( 'halk_amount_fix', 'switch_currencies' );
function switch_currencies( $amount ) {
	return number_format( $amount * 61.5, 2, '.', ''  );
}
1.0.1 Fix bug with older versions
1.0 Initial version.
