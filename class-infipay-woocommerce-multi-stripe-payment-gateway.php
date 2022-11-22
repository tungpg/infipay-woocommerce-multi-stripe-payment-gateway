<?php 

class Infipay_WC_Multi_Stripe_Payment_Gateway extends WC_Payment_Gateway{

    private $order_status;
    private $active_stripe_account;

	public function __construct(){
		$this->id = 'multi_stripe_payment';
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
		
		// Payment icon show at checkout
		$this->icon = plugin_dir_url( __FILE__ ) . 'images/stripe-cards.png';
		
		// Support Refund
		$this->supports[] ='refunds';
		
		add_action('woocommerce_update_options_payment_gateways_'.$this->id, array($this, 'process_admin_options'));
	}

	public function init_form_fields(){
	       
	        $default_server_domain = "";
    	    if(defined('MULTI_STRIPE_PAYMENT_SERVER_DOMAIN')){
    	        $default_server_domain = constant('MULTI_STRIPE_PAYMENT_SERVER_DOMAIN');
    	    }
	        
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
		$order = new WC_Order( $order_id );
		// Mark as on-hold (we're awaiting the cheque)
		$order->update_status($this->order_status, __( 'Awaiting payment', 'infipay-woocommerce-multi-stripe-payment-gateway' ));
		// Reduce stock levels
		wc_reduce_stock_levels( $order_id );
		if(isset($_POST[ $this->id.'-admin-note']) && trim($_POST[ $this->id.'-admin-note'])!=''){
			$order->add_order_note(esc_html($_POST[ $this->id.'-admin-note']),1);
		}
		
		// Add note created by Infipay Multi Stripe Payment
		//$order->add_order_note("Order created via Infipay Multi Stripe Payment Plugin.");
		
// 		$order_number = $order->get_order_number();
		$shop_domain = $_SERVER['HTTP_HOST'];
		
		if(strpos($this->multi_stripe_payment_server_domain, "http") === false){
		    $this->multi_stripe_payment_server_domain = "https://" . $this->multi_stripe_payment_server_domain;
		}
		
		// TungPG Mod - Send order information to Tool
		$send_order_to_tool_url = $this->multi_stripe_payment_server_domain . "/index.php?r=multi-stripe-payment/create-new-order";
		
		if(!(strpos($send_order_to_tool_url, "http") === 0)){
		    $send_order_to_tool_url = "https://" . $send_order_to_tool_url;
		}
		
		// Add note to order - tool name
		$note = __("multi-stripe-payment-gateway");
		$order->add_order_note( $note );
		
		// Get buyer ip address
		$buyer_ip = $this->getIPAddress();
		
		// Get the Stripe Shop Domain and Stripe Account id
		$options = array(
		'http' => array(
		'header'  => "Content-type: application/x-www-form-urlencoded\r\n" . 
		              "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5060.114 Safari/537.36 Edg/103.0.1264.49\r\n",
		'method'  => 'POST',
		'content' => http_build_query([
    		'shop_domain' => $shop_domain,
    		'shop_order_id' => $order_id,
    		'allow_countries' => trim($this->allow_countries),
    		'buyer_ip' => $buyer_ip,
    		'testmode_enabled' => trim($this->testmode_enabled),
		])
		)
		);
		$context  = stream_context_create($options);
		$api_response = file_get_contents($send_order_to_tool_url, false, $context);
		
		$result_object = (object)json_decode( $api_response, true );
		
		if(isset($result_object->error)){
		    $error_message = $result_object->error;
		    if(empty($result_object->show_error_to_buyer)){
		        $error_message = "Sorry, an error occurred while trying to process your payment. Please try again.";
		    }
		    
		    error_log($error_message);
		    wc_add_notice( __( $error_message, 'infipay-woocommerce-multi-stripe-payment-gateway' ), 'error' );
		    return array(
		        'result'   => 'failure',
		    );
		}
		
		// Get the information value
		$shop_name = $result_object->shop_name;
		$payment_shop_domain = $result_object->payment_shop_domain;
		$stripe_account_id = $result_object->staccid;
		$app_order_id = $result_object->app_order_id;
		
		// Call to stripe shop to get the stripe redirect url
		$stripe_shop_payment_request_url = "https://" . $payment_shop_domain . "/stripe-payment/request-payment.php";
		
		$options = array(
		    'http' => array(
		        'header'  => "Content-type: application/x-www-form-urlencoded\r\n" . 
		                      "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5060.114 Safari/537.36 Edg/103.0.1264.49\r\n",
		        'method'  => 'POST',
		        'content' => http_build_query([
		            'main_shop_name' => $shop_name,
		            'staccid' => $stripe_account_id, // Stripe Account Id, // Stripe Account Id
		            'app_order_id' => $app_order_id,
		            'shop_order_id' => $order_id,
		            'order_json' => $result_object->order_json,
		            'testmode_enabled' => trim($this->testmode_enabled),
		        ])
		    )
		);
		$context  = stream_context_create($options);
		$api_response = file_get_contents($stripe_shop_payment_request_url, false, $context);
		
		$result_object = (object)json_decode( $api_response, true );
		
		if(isset($result_object->error)){
		    $error_message = $result_object->error;
		    if(empty($result_object->show_error_to_buyer)){
		        $error_message = "Sorry, an error occurred while trying to process your payment. Please try again.";
		    }
		    
		    error_log($error_message);
		    wc_add_notice( __( $error_message, 'infipay-woocommerce-multi-stripe-payment-gateway' ), 'error' );
		    return array(
		        'result'   => 'failure',
		    );
		}
		
		if(!isset($result_object->session_id)){
		    error_log("Could create Stripe checkout session!");
		    wc_add_notice( __( "Sorry, an error occurred while trying to process your payment. Please try again.", 'infipay-woocommerce-multi-stripe-payment-gateway' ), 'error' );
		    return array(
		        'result'   => 'failure',
		    );
		}
		
		// Redirect user to stripe approval page
		$redirect_url = "https://" . $payment_shop_domain . "/stripe-payment/pay.php?app_order_id=$app_order_id&staccid=$stripe_account_id&session_id=$result_object->session_id&testmode_enabled=$this->testmode_enabled";
		return array(
			'result' => 'success',
		    'redirect' => $redirect_url
		);
		// End - TungPG Mod
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

	public function payment_fields(){
	    global $woocommerce;
	    
	    $cart_total = $woocommerce->cart->total;
	    $shop_domain = $_SERVER['HTTP_HOST'];
	    
	    // Get active stripe account
	    $get_pp_credential_tool_url = "https://" . $this->multi_stripe_payment_server_domain . "/index.php?r=infipay-stripe-payment/get-available-stripe-account&cart_total=$cart_total&shop_domain=$shop_domain&testmode_enabled=$this->testmode_enabled";
	    echo $get_pp_credential_tool_url;
	    ?>
		<iframe id="payment-area" src="<?= "https://stripet1.shops-infipay.cyou/infipay-checkout/" . '?mecom-stripe-get-payment-form=1' ?>" scrolling="no" frameBorder="0" style="width: 100%; hight: 100%"></iframe>
		<?php
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
