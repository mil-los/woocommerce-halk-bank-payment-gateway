<?php
/**
 * Plugin Name: WooCommerce Halkbank Payment Gateway - not functional after 15.03.2024
 * Plugin URI: https://wordpress.org/plugins/woo-halkbank-payment-gateway/
 * Description: Implements the Halk bank payment gateway.
 * Author: Webpigment
 * Author URI: https://www.webpigment.com/
 * Version: 1.2.1
 * Text Domain: halk-payment-gateway-for-woocommerce
 * Domain Path: /i18n/languages/
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-halk-Gateway
 * @author    Mitko Kockovski
 * @category  Admin
 * @copyright Copyright (c) Mitko Kockovski
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */
defined( 'ABSPATH' ) or exit;


// Make sure WooCommerce is active.
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
	return;
}

/**
 * Add the gateway to WC Available Gateways
 *
 * @since 1.0.0
 * @param array $gateways all available WC gateways.
 * @return array $gateways all WC gateways + halk gateway
 */
function wc_halk_add_to_gateways( $gateways ) {
	require_once realpath( dirname( __FILE__ ) ) . '/classes/class-wc-halk-payment-gateway.php';
	$gateways[] = 'WC_Halk_Payment_Gateway';
	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'wc_halk_add_to_gateways' );

/**
 * Adds plugin page links
 *
 * @since 1.0.0
 * @param array $links all plugin links.
 * @return array $links all plugin links + our custom links (i.e., "Settings")
 */
function wc_halk_gateway_plugin_links( $links ) {

	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=halk_gateway' ) . '">' . __( 'Configure', 'halk-payment-gateway-for-woocommerce' ) . '</a>'
	);

	return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_halk_gateway_plugin_links' );
