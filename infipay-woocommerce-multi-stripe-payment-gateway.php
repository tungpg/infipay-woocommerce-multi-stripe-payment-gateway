<?php
/* @wordpress-plugin
 * Plugin Name:       Infipay WooCommerce Multi Stripe Payment Gateway
 * Description:       The plugin allows the use of multiple Stripe accounts in the same shop. These plugins are required to use: WP Session Manager.
 * Version:           0.1.1
 * WC requires at least: 5.0
 * WC tested up to: 5.9.3
 * Author:            TungPG
 * Text Domain:       infipay-woocommerce-multi-stripe-payment-gateway
 * Domain Path: /languages
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

require plugin_dir_path(__FILE__) . '/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/tungpg/infipay-woocommerce-multi-stripe-payment-gateway/',
    __FILE__,
    'infipay-woocommerce-multi-stripe-payment-gateway'
    );

//Set the branch that contains the stable release.
$myUpdateChecker->setBranch('master');

$active_plugins = apply_filters('active_plugins', get_option('active_plugins'));
if(infipay_stripe_payment_is_woocommerce_active()){
	add_filter('woocommerce_payment_gateways', 'infipay_add_multi_stripe_payment_gateway');
	function infipay_add_multi_stripe_payment_gateway( $gateways ){
		$gateways[] = 'Infipay_woocommerce_Multi_Stripe_Payment_Gateway';
		return $gateways; 
	}

	add_action('plugins_loaded', 'infipay_init_multi_stripe_payment_gateway');
	function infipay_init_multi_stripe_payment_gateway(){
		require 'class-infipay-woocommerce-multi-stripe-payment-gateway.php';
	}

	add_action( 'plugins_loaded', 'infipay_stripe_load_plugin_textdomain' );
	function infipay_stripe_load_plugin_textdomain() {
	  load_plugin_textdomain( 'infipay-woocommerce-multi-stripe-payment-gateway', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
	}

	
	add_action('wp_footer', 'infipay_action_stripe_wp_footer', 10, 1);
	function infipay_action_stripe_wp_footer()
	{
	    if (is_checkout()) {
	        $gateways = WC()->payment_gateways->get_available_payment_gateways();
	        if (isset($gateways['infipay_stripe']->enabled) && $gateways['infipay_stripe']->enabled == 'yes') {
	            echo '<div id="cs-stripe-loader">
                  <div class="cs-stripe-spinnerWithLockIcon cs-stripe-spinner" aria-busy="true">
                      <p>We\'re processing your payment...<br/>Please <b>DO NOT</b> close this page!</p>
                  </div>
            </div>';
	        }
	    }
	}

}


/**
 * @return bool
 */
function infipay_stripe_payment_is_woocommerce_active()
{
	$active_plugins = (array) get_option('active_plugins', array());

	if (is_multisite()) {
		$active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
	}

	return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins);
}
