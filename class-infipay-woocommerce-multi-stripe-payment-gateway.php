<?php 

class Infipay_WC_Multi_Stripe_Payment_Gateway extends WC_Payment_Gateway{

    private $order_status;
    public $active_stripe_account;

	public function __construct(){
		$this->id = 'infipay_multi_stripe_payment';
		$this->method_title = __('Infipay Multi Stripe Payment','infipay-woocommerce-multi-stripe-payment-gateway');
		//$this->title = __('Infipay Multi Stripe Payment','infipay-woocommerce-multi-stripe-payment-gateway');
		$this->has_fields = true;
		$this->init_form_fields();
		$this->init_settings();
		$this->enabled = $this->get_option('enabled');
		$this->testmode_enabled = $this->get_option('testmode_enabled');
		$this->multi_stripe_payment_server_domain = $this->get_option('multi_stripe_payment_server_domain');
		$this->title = $this->get_option('title');
		$this->description = $this->get_option('description');
		$this->hide_text_box = $this->get_option('hide_text_box');
		$this->text_box_required = $this->get_option('text_box_required');
		$this->order_status = $this->get_option('order_status');
		$this->order_button_text = $this->get_option('order_button_text');
		$this->allow_countries = strtoupper($this->get_option('allow_countries'));
		$this->active_stripe_account = null;
		
		// Payment icon show at checkout
		$this->icon = plugin_dir_url( __FILE__ ) . 'assets/images/cards.png';
		
		// Support Refund
		$this->supports[] ='refunds';
        		
		add_action('woocommerce_update_options_payment_gateways_'.$this->id, array($this, 'process_admin_options'));
	}

	public function init_form_fields(){
	       
    	    $default_server_domain = "payment.infipay.us";
	        
			$this->form_fields = array(
			    
			    
				'enabled' => array(
					'title' 		=> __( 'Enable/Disable', 'infipay-woocommerce-multi-stripe-payment-gateway' ),
					'type' 			=> 'checkbox',
					'label' 		=> __( 'Enable Infipay Multi Stripe Payment', 'infipay-woocommerce-multi-stripe-payment-gateway' ),
					'default' 		=> 'no'
				),
			    
			    'testmode_enabled' => array(
			        'title' 		=> __( 'Test Mode Enable/Disable', 'infipay-woocommerce-multi-stripe-payment-gateway' ),
			        'type' 			=> 'checkbox',
			        'label' 		=> __( 'Enable Test Mode Mode', 'infipay-woocommerce-multi-stripe-payment-gateway' ),
			        'description' 	=> __( 'If enabled, only test keys will be used.', 'infipay-woocommerce-multi-stripe-payment-gateway' ),
			        'default' 		=> 'no'
			    ),
			    
			    'multi_stripe_payment_server_domain' => array(
			        'title' 		=> __( 'Tool Server Domain', 'infipay-woocommerce-multi-stripe-payment-gateway' ),
			        'type' 			=> 'text',
			        'description' 	=> __( 'The domain address of the tool managing multiple stripe accounts. Example: yourtool.com.', 'infipay-woocommerce-multi-stripe-payment-gateway' ),
			        'default'		=> __( $default_server_domain, 'infipay-woocommerce-multi-stripe-payment-gateway' ),
			    ),
			    
	            'title' => array(
					'title' 		=> __( 'Method Title', 'infipay-woocommerce-multi-stripe-payment-gateway' ),
					'type' 			=> 'text',
					'description' 	=> __( 'This controls the title.', 'infipay-woocommerce-multi-stripe-payment-gateway' ),
					'default'		=> __( 'Credit Card (Stripe)', 'infipay-woocommerce-multi-stripe-payment-gateway' ),
				),
				'description' => array(
					'title' => __( 'Customer Message', 'infipay-woocommerce-multi-stripe-payment-gateway' ),
					'type' => 'textarea',
					'css' => 'width:500px;',
					'default' => 'Pay via Stripe; Accept all major debit and credit cards.',
					'description' 	=> __( 'The message which you want it to appear to the customer in the checkout page.', 'infipay-woocommerce-multi-stripe-payment-gateway' ),
				),
				'order_status' => array(
					'title' => __( 'Order Status After The Checkout', 'infipay-woocommerce-multi-stripe-payment-gateway' ),
					'type' => 'select',
					'options' => wc_get_order_statuses(),
					'default' => 'wc-pending',
					'description' 	=> __( 'The default order status if this gateway used in payment.', 'infipay-woocommerce-multi-stripe-payment-gateway' ),
				),
			    'allow_countries' => array(
			        'title' => __( 'Allow Countries', 'infipay-woocommerce-multi-stripe-payment-gateway' ),
			        'type' => 'text',
			        'default' => "US",
			        'description' 	=> __( 'Only customers from these countries are allowed to check out using this plugin. Enter <a target="_blank" href=\'https://www.nationsonline.org/oneworld/country_code_list.htm\'>country_code</a> (Alpha 2) separated by commas. Leaving blank is unlimited.', 'infipay-woocommerce-multi-stripe-payment-gateway' ),
			    ),
			    'order_button_text' => array(
			        'title' => __( 'Order Button Text', 'infipay-woocommerce-multi-stripe-payment-gateway' ),
			        'type' => 'text',
			        'default' => 'Continue to payment',
			        'description' 	=> __( 'Set if the place order button should be renamed on selection.', 'infipay-woocommerce-multi-stripe-payment-gateway' ),
			    ),			    
		 );
	}
	/**
	 * Admin Panel Options
	 * - Options for bits like 'title' and availability on a country-by-country basis
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_options() {
		?>
		<h3><?php _e( 'Infipay Multi Stripe Payment Settings', 'infipay-woocommerce-multi-stripe-payment-gateway' ); ?></h3>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-1">
					<div id="post-body-content">
						<table class="form-table">
							<?php $this->generate_settings_html();?>
						</table><!--/.form-table-->
					</div>
				</div>
				</div>
				<div class="clear"></div>
				<style type="text/css">
				.wpruby_button{
					background-color:#4CAF50 !important;
					border-color:#4CAF50 !important;
					color:#ffffff !important;
					width:100%;
					text-align:center;
					height:35px !important;
					font-size:12pt !important;
				}
                .wpruby_button .dashicons {
                    padding-top: 5px;
                }
				</style>
				<?php
	}

	public function validate_fields() {
        return true;

// 	    $textbox_value = (isset($_POST['multi_stripe_payment-admin-note']))? trim($_POST['multi_stripe_payment-admin-note']): '';
// 		if($textbox_value === ''){
// 			wc_add_notice( __('Please, complete the payment information.','woocommerce-custom-payment-gateway'), 'error');
// 			return false;
//         }
// 		return true;
	}

	public function process_payment( $order_id ) {
	    global $woocommerce;
	    // we need it to get any order details
	    $order = wc_get_order($order_id);
	    
	    $activatedProxy = $this->active_stripe_account;
	    
	    if (!isset($activatedProxy)) {
	        error_log("Can't find activated proxy!\n");
	        wc_add_notice('We cannot accept any payments right now. Please comeback to try tomorrow or select other payment methods if available.', 'error');
	        return [
	            'result' => 'failure',
	            'reload' => true
	        ];
	    }
	    
	    $shippingName = $order->get_shipping_first_name() . " " . $order->get_shipping_last_name();
	    $shippingAddress1 = $order->get_shipping_address_1();
	    $shippingAddress2 = $order->get_shipping_address_2();
	    $shippingCity = $order->get_shipping_city();
	    $shippingCountry = $order->get_shipping_country();
	    $shippingPostCode = $order->get_shipping_postcode();
	    $shippingState = $order->get_shipping_state();
	    
	    // Billing
	    $billingName = $order->get_billing_first_name() . " " . $order->get_billing_last_name();
	    $billingAddress1 = $order->get_billing_address_1();
	    $billingAddress2 = $order->get_billing_address_2();
	    $billingCity = $order->get_billing_city();
	    $billingCountry = $order->get_billing_country();
	    $billingPostCode = $order->get_billing_postcode();
	    $billingState = $order->get_billing_state();
	    
	    $shippingName = (empty($order->get_shipping_first_name()) && empty($order->get_shipping_last_name())) ? $billingName : $shippingName;
	    $shippingAddress1 = empty($shippingAddress1) ? $billingAddress1 : $shippingAddress1;
	    $shippingAddress2 = empty($shippingAddress2) ? $billingAddress2 : $shippingAddress2;
	    $shippingCity = empty($shippingCity) ? $billingCity : $shippingCity;
	    $shippingCountry = empty($shippingCountry) ? $billingCountry : $shippingCountry;
	    $shippingPostCode = empty($shippingPostCode) ? $billingPostCode : $shippingPostCode;
	    $shippingState = empty($shippingState) ? $billingState : $shippingState;
	    
	    
	    // Log processing proxyUrl
	    $order->add_order_note(sprintf(__('Starting checkout with Stripe proxy %s', 'mecom'), $activatedProxy->payment_shop_domain), 0, false);
	    
	    $items = [];
	    
	    $order_items = $order->get_items();
	    foreach ($order_items as $it) {
	        $product = wc_get_product($it->get_product_id());
	        //$product_name = $product->get_name(); // Get the product name
	        $product_name = $this->getProductTitle($product->get_name());
	        
	        $item_quantity = $it->get_quantity(); // Get the item quantity
	        
	        $amount = round($it['line_subtotal'] / $it['qty'], $this->get_number_of_decimal_digits());
	        
	        $items[] = [
	            "name" => $product_name,
	            "quantity" => $item_quantity,
	            "total" => $amount
	        ];
	    }
	    $response = wp_remote_post($activatedProxy->payment_shop_domain . '/infipay-checkout/?mecom-stripe-make-payment=1', [
	        'timeout' => 5 * 60,
	        'headers' => [
	            'Content-Type' => 'application/json',
	        ],
	        'body' => json_encode([
	            'payment_intent' => $order->get_transaction_id(),
	            'payment_method_id' => 'infipay_multi_stripe_payment',
	            'order_id' => $order->get_id(),
	            'order_invoice' => $this->invoice_prefix . $order->get_order_number(),
	            'order_items' => $items,
	            'statement_descriptor' => $this->get_option('statement_descriptor'),
	            'merchant_site' => get_home_url(),
	            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
	            'amount' => $order->get_total(),
	            'customer_zipcode' => $billingPostCode,
	            'billing_email' => $order->get_billing_email(),
	            'currency' => $order->get_currency(),
	            'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
	            'shipping' => [
	                'name' => $shippingName,
	                'phone' => method_exists($order, 'get_shipping_phone') && $order->get_shipping_phone() ? $order->get_shipping_phone() : $order->get_billing_phone(),
	                'address' => [
	                    'city' => $shippingCity,
	                    'country' => $shippingCountry,
	                    'line1' => $shippingAddress1,
	                    'line2' => $shippingAddress2,
	                    'postal_code' => $shippingPostCode,
	                    'state' => $shippingState,
	                ],
	            ]
	        ])
	    ]);
	    
	    if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
	        error_log(print_r($response, true));
	        wc_add_notice(json_encode($response), 'error');
	        // wc_add_notice('We cannot process your payment right now, please try another payment method.', 'error');
	        return false;
	    }
	    $body = wp_remote_retrieve_body($response);
	    $body = json_decode($body);
	    if ($body->status === 'success') {
	        $paymentIntent = $body->payment_intent;
	        $order->payment_complete();
	        $order->reduce_order_stock();
	        
	        //Save the processed proxy for this order (using for refund later)
	        $order->add_order_note(sprintf(__('Stripe charged by proxy %s', 'mecom'), $activatedProxy->payment_shop_domain), 0, false);
	        // some notes to customer (replace true with false to make it private)
	        $order->add_order_note(sprintf(__('Stripe Checkout charge complete (Payment Intent ID: %s)', 'mecom'), $paymentIntent->id));
	        
	        update_post_meta($order->get_id(), '_transaction_id', $paymentIntent->id);
	        update_post_meta($order->get_id(), METAKEY_STRIPE_PROXY_URL, $activatedProxy->payment_shop_domain);
	        updateFeeNetOrderStripe($body->charge, $order);
	        // Empty cart
	        $woocommerce->cart->empty_cart();
	        
	        return [
	            'result' => 'success',
	            'redirect' => $order->get_checkout_order_received_url()
	        ];
	    } else {
	        error_log(print_r($response, true));
	        update_post_meta($order->get_id(), METAKEY_STRIPE_PROXY_URL, $activatedProxy->payment_shop_domain);
	        // Empty cart
	        $order->update_status('failed');
	        if($body->code === 'domain_whitelist_not_allow') {
	            $order->add_order_note(sprintf(__('Stripe charged ERROR by proxy %s, ERROR message: %s', 'mecom'),
	                $activatedProxy->payment_shop_domain,
	                'Domain whitelist is required'
	                ));
	        } else if($body->code === 'customer_zipcode_not_allow') {
	            $order->add_order_note(sprintf(__('Stripe charged ERROR by proxy %s, ERROR message: %s', 'mecom'),
	                $activatedProxy->payment_shop_domain,
	                "Customer's zipcode is blacklisted"
	                ));
	            wc_add_notice('The selected payment method is suspended, Please contact merchant for more information.', 'error');
	            return false;
	        } else {
	            $err = $body->err;
	            $paymentIntentId = $order->get_transaction_id();
	            if (isset($err->payment_intent)) {
	                $paymentIntentId = $err->payment_intent->id;
	                update_post_meta($order->get_id(), '_transaction_id', $paymentIntentId);
	            }
	            $order->add_order_note(sprintf(__('Stripe charged ERROR by proxy %s, ERROR message: %s, Payment Intent ID: %s', 'mecom'),
	                $activatedProxy->payment_shop_domain,
	                is_string($err) ?: $err->message,
	                $paymentIntentId
	                ));
	        }
	        wc_add_notice('We cannot process your payment right now, please try another payment method.[2]', 'error');
	        return false;
	    }
	}
	
	function process_refund( $order_id, $amount = NULL, $reason = '' ) {
	    // Get order information
	    $refund_order_tool_url = "https://" . $this->multi_stripe_payment_server_domain . "/index.php?r=multi-stripe-payment/process-refund";
	    $shop_domain = $_SERVER['HTTP_HOST'];
	    
	    $options = array(
	        'http' => array(
	            'header'  => "Content-type: application/x-www-form-urlencoded\r\n" . 
	                           "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5060.114 Safari/537.36 Edg/103.0.1264.49\r\n",
	            'method'  => 'POST',
	            'content' => http_build_query([
	                'shop_domain' => $shop_domain,
	                'shop_order_id' => $order_id,
	                'amount' => $amount,
	                'reason' => $reason,
	                'testmode_enabled' => trim($this->testmode_enabled),
	            ])
	        )
	    );
	    $context  = stream_context_create($options);
	    $api_response = file_get_contents($refund_order_tool_url, false, $context);
        	    
	    $result_object = (object)json_decode( $api_response, true );
	    
	    if(isset($result_object->error)){
	        throw new Exception( __( $result_object->error, 'infipay-woocommerce-multi-stripe-payment-gateway' ) );
	        return false;
	    }
	    
	    if(!empty($result_object->success)){
	        // Take note to order
	        $order = wc_get_order( $order_id );
	        
	        $note = __("multi-stripe-payment-gateway<br/>Refunded: " . wc_price($amount) . " – Reason: $reason");
	        $order->add_order_note( $note );
	        
	        return true;
	    }
	    
	    return false;
	}
	
	/*
	 * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
	 */
	public function payment_scripts()
	{
	    // we need JavaScript to process a token only on cart/checkout pages, right?
	    if (!is_cart() && !is_checkout()) {
	        return;
	    }
	    
	    // if our payment gateway is disabled, we do not have to enqueue JS too
	    if ('no' === $this->enabled) {
	        return;
	    }
	    wp_register_style('infipay_multi_stripe_payment_styles', plugins_url('assets/css/styles.css', __FILE__), [], OPT_MECOM_STRIPE_VERSION);
	    wp_enqueue_style('infipay_multi_stripe_payment_styles');
	    
	    wp_register_script('infipay_multi_stripe_payment_js', plugins_url('assets/js/checkout_hook.js', __FILE__), array('jquery'), OPT_MECOM_STRIPE_VERSION, true);
	    wp_enqueue_script('infipay_multi_stripe_payment_js');
	}
	
	public function payment_fields(){
	    global $woocommerce;
	    
	    $cart_total = $woocommerce->cart->total;
	    $shop_domain = $_SERVER['HTTP_HOST'];
	    
	    // Get active stripe account
	    $get_available_stripe_account_url = "https://" . $this->multi_stripe_payment_server_domain . "/index.php?r=infipay-stripe-payment/get-available-stripe-account";

		// Get the Stripe Shop Domain and Stripe Account id
		$options = array(
		'http' => array(
		'header'  => "Content-type: application/x-www-form-urlencoded\r\n" . 
		              "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5060.114 Safari/537.36 Edg/103.0.1264.49\r\n",
		'method'  => 'POST',
		'content' => http_build_query([
    		'shop_domain' => $shop_domain,
    		'cart_total' => $cart_total,
    		'testmode_enabled' => trim($this->testmode_enabled),
		])
		)
		);
		$context  = stream_context_create($options);
		$api_response = file_get_contents($get_available_stripe_account_url, false, $context);
		
		$result_object = (object)json_decode( $api_response, true );
		
		if(isset($result_object->error) || empty($result_object->payment_shop_domain)){
		    $error_message = $result_object->error;
		    if(empty($result_object->show_error_to_buyer)){
		        $error_message = "This payment method is currently unavailable. Please choose another payment method.";
		    }
		    
		    error_log($error_message);
		    wc_add_notice( __( $error_message, 'infipay-woocommerce-multi-stripe-payment-gateway' ), 'error' );
		    
		    echo "<div style='color:red'>$error_message</div>";
		}else{
		    $this->active_stripe_account = $result_object;
		    print_r($this->active_stripe_account);
		    // Get the information value
    		$payment_shop_domain = $result_object->payment_shop_domain;
    		
    		
    		if ($this->testmode_enabled == 'yes') {
    		    /* translators: %s: Link to Stripe sandbox testing guide page */
    		    echo "<div>" . sprintf(__('TEST MODE ENABLED. In test mode, you can use the card number 4242424242424242 with any CVC and a valid expiration date or check the <a href="%s" target="_blank">Testing Stripe documentation</a> for more card numbers.', 'woocommerce-gateway-stripe'), 'https://stripe.com/docs/testing') . "</div>";
    		}
    		
    	    ?>
    		<iframe id="payment-area" src="<?= "https://$payment_shop_domain/infipay-checkout/" . '?mecom-stripe-get-payment-form=1' ?>" scrolling="no" frameBorder="0" style="width: 100%; hight: 100%"></iframe>
    		<?php
		}
	}
	
	/**
	 * Get real user IP address
	 * @return String
	 */
	public function getIPAddress() {
	    //whether ip is from the share internet
	    if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
	        $ip = $_SERVER['HTTP_CLIENT_IP'];
	    }
	    //whether ip is from the proxy
	    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	    }
	    //whether ip is from the remote address
	    else{
	        $ip = $_SERVER['REMOTE_ADDR'];
	    }
	    return $ip;
	}  
}
