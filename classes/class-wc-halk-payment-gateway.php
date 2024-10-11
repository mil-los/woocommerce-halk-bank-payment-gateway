<?php
/**
 * WooCommerce Tebank Payment Gateway
 *
 * Provides Payment gateway for Tebank service.
 *
 * @class 		WC_Halk_Payment_Gateway
 * @extends		WC_Payment_Gateway
 * @version		1.3
 * @package		WooCommerce/Classes/Payment
 * @author 		mil-los
 */
class WC_Halk_Payment_Gateway extends WC_Payment_Gateway {
	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {

		$this->id                 = 'halk_gateway';
		$this->icon               = '';
		$this->has_fields         = false;
		$this->method_title       = __( 'Halk Bank Payment', 'halk-payment-gateway-for-woocommerce' );
		$this->method_description = __( 'Allows your store to use the Halk Bank Payment method.', 'halk-payment-gateway-for-woocommerce' );
		$this->payment_url        = $this->get_option( 'testing_mode', 'no' ) == 'yes' ? 'https://torus-stage-halkbankmacedonia.asseco-see.com.tr/fim/est3Dgate' : 'https://epay.halkbank.mk/fim/est3Dgate';
		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->title        = $this->get_option( 'title' );
		$this->description  = $this->get_option( 'description' );
		$this->client_id    = $this->get_option( 'client_id' );
		$this->store_key    = $this->get_option( 'store_key' );
		$this->username     = $this->get_option( 'username' );
		$this->password     = $this->get_option( 'password' );
		$this->status_transaction     = $this->get_option( 'status_transaction', 'yes' ) == 'yes' ? 1 : 0;
		$this->transaction_type       = $this->get_option( 'transaction_type', 'Auth' );

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		// 3D functions.
		add_action( 'woocommerce_api_' . $this->id, array( $this, 'secure_3d_process_response' ), 10, 1 );
		add_action( 'wp_footer', array( $this, 'add_3d_container_to_footer' ) );
		add_filter( 'woocommerce_update_order_review_fragments', array( $this, 'refresh_form' ), 10, 1 );
	}

	/**
	 * Initialize Gateway Settings Form Fields
	 */
	public function init_form_fields() {

		$this->form_fields = apply_filters( 'wc_halk_form_fields',
			array(
				'enabled' => array(
					'title'   => __( 'Enable/Disable', 'halk-payment-gateway-for-woocommerce' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable Halk Bank Payment', 'halk-payment-gateway-for-woocommerce' ),
					'default' => 'yes'
				),
				'testing_mode' => array(
					'title'   => __( 'Testing mode', 'halk-payment-gateway-for-woocommerce' ),
					'type'    => 'checkbox',
					'label'   => __( 'Testing the integration', 'halk-payment-gateway-for-woocommerce' ),
					'default' => 'no'
				),
				'transaction_type'   => array(
					'name'    => __( 'Transaction type', 'woocommerce' ),
					'id'      => 'transaction_type',
					'css'     => 'min-width:150px;',
					'std'     => 'Auth', // WooCommerce < 2.0
					'default' => 'Auth', // WooCommerce >= 2.0
					'type'    => 'select',
					'options' => array(
						'Auth'        => __( 'Capture', 'woocommerce' ),
						'PreAuth'     => __( 'Authorization', 'woocommerce' ),
					),
				),
				'status_transaction' => array(
					'title'   => __( 'Status Transactions', 'halk-payment-gateway-for-woocommerce' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable status transactions ( needed by the bank to enable live environment )', 'halk-payment-gateway-for-woocommerce' ),
					'default' => 'yes'
				),
				'title' => array(
					'title'       => __( 'Title', 'halk-payment-gateway-for-woocommerce' ),
					'type'        => 'text',
					'description' => __( 'This controls the title for the payment method the customer sees during checkout.', 'halk-payment-gateway-for-woocommerce' ),
					'default'     => __( 'Halk Bank Payment', 'halk-payment-gateway-for-woocommerce' ),
					'desc_tip'    => true,
				),
				'description' => array(
					'title'       => __( 'Description', 'halk-payment-gateway-for-woocommerce' ),
					'type'        => 'textarea',
					'description' => __( 'Payment method description that the customer will see on your checkout.', 'halk-payment-gateway-for-woocommerce' ),
					'default'     => __( 'Please remit payment to Store Name upon pickup or delivery.', 'halk-payment-gateway-for-woocommerce' ),
					'desc_tip'    => true,
				),
				'client_id' => array(
					'title'       => __( 'Client ID', 'halk-payment-gateway-for-woocommerce' ),
					'type'        => 'text',
					'description' => __( 'You need to ask your bank processor for this value.', 'halk-payment-gateway-for-woocommerce' ),
					'default'     => __( '000000000', 'halk-payment-gateway-for-woocommerce' ),
					'desc_tip'    => true,
				),
				'username' => array(
					'title'       => __( 'Username', 'halk-payment-gateway-for-woocommerce' ),
					'type'        => 'text',
					'description' => __( 'You need to ask your bank processor for this value. Used only for status transaction', 'halk-payment-gateway-for-woocommerce' ),
					'default'     => __( '000000000', 'halk-payment-gateway-for-woocommerce' ),
					'desc_tip'    => true,
				),
				'password' => array(
					'title'       => __( 'Password', 'halk-payment-gateway-for-woocommerce' ),
					'type'        => 'password',
					'description' => __( 'You need to ask your bank processor for this value. Used only for status transaction', 'halk-payment-gateway-for-woocommerce' ),
					'default'     => __( '000000000', 'halk-payment-gateway-for-woocommerce' ),
					'desc_tip'    => true,
				),
				'store_key' => array(
					'title'       => __( 'Store key', 'halk-payment-gateway-for-woocommerce' ),
					'type'        => 'text',
					'description' => __( 'You need to ask your bank processor for this value.', 'halk-payment-gateway-for-woocommerce' ),
					'default'     => __( 'SKEY0000', 'halk-payment-gateway-for-woocommerce' ),
					'desc_tip'    => true,
				),
			)
		);
	}

	/**
	 * Process the payment and return the result
	 *
	 * @param int $order_id  Created order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {

		// $order = wc_get_order( $order_id );
		wc_add_notice( __( 'Redirecting to payment page.', 'halk-payment-gateway-for-woocommerce' ), 'success' );
		WC()->session->set( $this->id . '_order_id', $order_id );
		$return = array(
			'result' 	=> 'success',
			'refresh' => true,
			'messages' => "\n\t<div class=\"woocommerce-message\" role=\"alert\">" . __('Redirecting to payment page.', 'halk-payment-gateway-for-woocommerce')  . "</div>\n\t",
		);
		return $return;
	}

	/**
	 * Process 3D response and validate data.
	 *
	 * @param array $api_request
	 * @return void
	 */
	public function secure_3d_process_response( $api_request ) {
		// Make sure we don't get any error reported.
		error_reporting(0);
		if( isset( $_GET['order'] ) && ! empty( $_GET['order'] ) ) {
			$order_id = intval( $_GET['order'] );
		} else {
			$order_id = WC()->session->get( $this->id . '_order_id' );
		}
		$order = wc_get_order( $order_id );

		$return_url = get_permalink( wc_get_page_id( 'checkout' ) );
		
		$postParams = array();
		foreach ($_POST as $key => $value){
			array_push($postParams, $key);				
			echo "<tr><td>" . $key ."</td><td>" . $value . "</td></tr>";
		}
		
		natcasesort($postParams);
		
		$hashval = "";
		foreach ($postParams as $param){				
			$paramValue = $_POST[$param];
			$escapedParamValue = str_replace("|", "\\|", str_replace("\\", "\\\\", str_replace('\\"', '"', $paramValue)));
			
			$lowerParam = strtolower($param);
			if($lowerParam != "hash" && $lowerParam != "encoding" && $lowerParam != "countdown") {
				$hashval = $hashval . $escapedParamValue . "|";
			}
		}
		
		$storeKey = $this->store_key;
		$escapedStoreKey = str_replace("|", "\\|", str_replace("\\", "\\\\", $storeKey));
		$hashval = $hashval . $escapedStoreKey;
		
        $calculatedHashValue = hash('sha512', $hashval); 
		$actualHash = base64_encode(pack('H*', $calculatedHashValue));

		$retrievedHash = $_POST["HASH"];

		if( $retrievedHash != $actualHash ){
			$order->add_order_note( __( 'Security warning. Hash values mismatch.', 'halk-payment-gateway-for-woocommerce' ) );
			wc_add_notice( __( 'Something went wrong. Please contact us for more details.', 'halk-payment-gateway-for-woocommerce' ), 'error' );
			// echo __( 'Security warning. Hash values mismatch.', 'halk-payment-gateway-for-woocommerce' );
		} else {
			$mdStatus = $_POST['mdStatus'];
			if( $mdStatus == '1' || $mdStatus == '2' || $mdStatus == '3' || $mdStatus == '4' ) {
				// echo 3D Authentication is successful.;
				$Response = $_POST["Response"];
				if ( $Response == "Approved") {
					// Your payment is approved.;
					$order->payment_complete();
					if( $this->status_transaction ) {
						$order->add_order_note( $this->make_test_status_transaction( $order_id ) );
					}
					$return_url = $this->get_return_url( $order );
				} else {
					$order->add_order_note( __( 'Your payment is not approved.', 'halk-payment-gateway-for-woocommerce' ) );
					wc_add_notice( __( 'Something went wrong. Please contact us for more details.', 'halk-payment-gateway-for-woocommerce' ), 'error' );

					// echo  __( 'Your payment is not approved.', 'halk-payment-gateway-for-woocommerce' );
				}
			} else {
				$order->add_order_note( __( '3D authentication unsuccesful.', 'halk-payment-gateway-for-woocommerce' ) );
				wc_add_notice( __( 'Something went wrong. Please contact us for more details.', 'halk-payment-gateway-for-woocommerce' ), 'error' );
				// echo __( '3D authentication unsuccesful.', 'halk-payment-gateway-for-woocommerce' );
			}
		}

		wp_safe_redirect( $return_url );
	}

	protected function make_test_status_transaction( $order_id ) {

		$clientid = $this->store_key;
		$name = $this->username;
		$password = $this->password;
		$oid= $order_id;

		$request= "DATA=<?xml version=\"1.0\" encoding=\"ISO-8859-9\"?>
		<CC5Request>
		<Name>{$name}</Name>
		<Password>{$password}</Password>
		<ClientId>{$clientid}</ClientId>
		<OrderId>{$order_id}</OrderId>	
		<Mode>P</Mode>
		<Extra><ORDERSTATUS>QUERY</ORDERSTATUS></Extra>
		</CC5Request>";

		$url = "https://entegrasyon.asseco-see.com.tr/fim/api";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 90);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);

		$result = curl_exec($ch);

		if (curl_errno($ch)) {
			print curl_error($ch);
		} else {
			curl_close($ch);
		}
		return $result;
	}

	/**
	 * Add container to footer to refresh fragments.
	 *
	 */
	public function add_3d_container_to_footer(){
		?>
	    <div class="<?php echo esc_attr( $this->id ); ?>-3d-secure-form-container"></div>
		<?php
	}

	/**
	 * Refresh the 3d secure form.
	 *
	 * @param  array $fragments Contain refresh fragments.
	 * @return array
	 */
	public function refresh_form( $fragments ) {
		$fragments[ '.' . esc_attr( $this->id ) . '-3d-secure-form-container' ] = $this->get_display_3d_form();
		return $fragments;
	}

	/**
	 * Display the form that's automatically submited on the front-end.
	 *
	 * @return void
	 */
	protected function get_display_3d_form(){
		ob_start();
		$this->display_3d_form();
		return ob_get_clean();
	}

	/**
	 * 3D secure form that automatically submits.
	 *
	 * @return void
	 */
	public function display_3d_form(){
		?>
	    <div class="<?php echo esc_attr( $this->id ); ?>-3d-secure-form-container">
			 <?php if( ! empty( WC()->session->get( $this->id . '_order_id' ) ) ) {
				 $order_id = WC()->session->get( $this->id . '_order_id' );
				 WC()->session->__unset( $this->id . '_order_id' );
				 $order = wc_get_order( $order_id );
				 $failUrl = $okUrl = get_site_url() . '/?wc-api=' . esc_attr( $this->id ) . '&order=' . $order_id;
				 $clientId = $this->client_id;		//Merchant Id defined by bank to user
				 $amount = apply_filters( 'halk_amount_fix', number_format( $order->get_total(),  2, '.', ''), $order );  //Transaction amount
				 $oid = $order_id;				//Order Id. Must be unique. If left blank, system will generate a unique one.
				 // $oid = '';				     //Order Id. Must be unique. If left blank, system will generate a unique one.
				 $rnd = microtime();				//A random number, such as date/time
				 $currencyVal = '807';			//Currency code, 949 for TL, ISO_4217 standard
				 $storekey = $this->store_key;		//Store key value, defined by bank.
				 $storetype = '3d_pay_hosting';	//3D authentication model
				 $lang = 'en';					//Language parameter, 'tr' for Turkish (default), 'en' for English
				 $instalment = '';				//Instalment count, if there's no instalment should left blank
				 $transactionType = 'Auth';		//transaction type

				 $hashstr = $amount . "||" . $clientId . "|" . $currencyVal . "|" . $failUrl . "|ver3|" . $instalment . "|" . $lang . "|" . $oid . "|" . $okUrl . "|10|" . $rnd . "|" .  $storetype . "|" . $transactionType . "|" . $storekey;
				 echo $hashstr;

				 $hash = base64_encode(pack('H*', hash('sha512', $hashstr)));
				 echo $hash;
				 ?>
			   <form name="form" id="<?php echo $this->id ; ?>-3d-secure-form" action="<?php echo $this->payment_url; ?>"
			         method="POST">
				   <div>
					   <input type="hidden" name="amount" value="<?php echo $amount; ?>" />
					   <input type="hidden" name="callbackUrl" value="" />
					   <input type="hidden" name="clientid" value="<?php echo $clientId; ?>" />
					   <input type="hidden" name="currency" value="<?php echo $currencyVal; ?>" />
					   <input type="hidden" name="encoding" value="utf-8" />
					   <input type="hidden" name="failUrl" value="<?php echo $failUrl; ?>" />
					   <input type="hidden" name="hash" value="<?php echo $hash; ?>" />
					   <input type="hidden" name="hashAlgorithm" value="ver3" />
					   <input type="hidden" name="Instalment" value="<?php echo $instalment; ?>" />
					   <input type="hidden" name="lang" value="<?php echo $lang; ?>" />
					   <input type="hidden" name="oid" value="<?php echo $oid; ?>" />
					   <input type="hidden" name="okUrl" value="<?php echo $okUrl; ?>" />
					   <input type="hidden" name="refreshtime" value="10" />
					   <input type="hidden" name="rnd" value="<?php echo $rnd; ?>" />
					   <input type="hidden" name="storetype" value="<?php echo $storetype; ?>" />
					   <input type="hidden" name="TranType" value="<?php echo $transactionType; ?>" />
				   </div>
				   <script>
					   jQuery('#<?php echo $this->id ; ?>-3d-secure-form').submit();
				   </script>
			   </form>
			 <?php } ?>
	    </div>
		<?php
	}
}