<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Stripe Library for CodeIgniter 3.x
 *
 * Library for Stripe payment gateway. It helps to integrate Stripe payment gateway
 * in CodeIgniter application.
 *
 * This library requires the Stripe PHP bindings and it should be placed in the third_party folder.
 * It also requires Stripe API configuration file and it should be placed in the config directory.
 *
 * @package     CodeIgniter
 * @category    Libraries
 * @author      CodexWorld
 * @license     http://www.codexworld.com/license/
 * @link        http://www.codexworld.com
 * @version     3.0
 */

class Stripe_lib{
    var $CI;
	var $api_error;
    
    function __construct(){
		$this->api_error = '';
        $this->CI =& get_instance();
        $this->CI->load->config('stripe');
		
		// Include the Stripe PHP bindings library
		require APPPATH .'third_party/stripe-php/init.php';
		
		$requrl = explode("/",$_SERVER['REQUEST_URI']);
		if($requrl[1] == "stripe")
		{
			$this->stripe_api_key = "sk_test_51K8qlgFTyXBXcCqlZq2o8Ei4ur5Ml9ggJmHWrPCUQxKKEpN73iLsENUz6qGrdeypuCVYrBUo3VpZl6TQCzoN1eEH00Hjf7bJ95";
		} else {
			$this->stripe_api_key = $this->CI->config->item('stripe_api_key');
		}
		
		
		
		// Set API key
		\Stripe\Stripe::setApiKey($this->stripe_api_key);
    }

    
	
	function addCustomer($name, $email, $token){
		try {
			// Add customer to stripe
			$customer = \Stripe\Customer::create(array(
				'name' => $name,
				'email' => $email,
				'source'  => $token
			));
			return $customer;
			// return 'ddd';
		}catch(Exception $e) {
			$this->api_error = $e->getMessage();
			return false;
			// return $e;
		}
    }
	
	function createCharge($customerId, $itemName, $itemPrice){
		// Convert price to cents
		$itemPrice = number_format($itemPrice, 2);
		$itemPriceCents = ($itemPrice*100);
		$currency = $this->CI->config->item('stripe_currency');
		
		try {
			// Charge a credit or a debit card
			$charge = \Stripe\Charge::create(array(
				'customer' => $customerId,
				'amount'   => $itemPriceCents,
				'currency' => $currency,
				'description' => $itemName
			));
			
			// Retrieve charge details
			$chargeJson = $charge->jsonSerialize();
			return $chargeJson;
		}catch(Exception $e) {
			$this->api_error = $e->getMessage();
			// return $e;
			return false;
		}
    }
	
	
	//	Verify Token
	function verifyToken($toekn_id){
		
		$url = "https://api.stripe.com/v1/tokens/".$toekn_id;
		
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$headers = array("Authorization: Bearer ".$this->stripe_api_key);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		$response = curl_exec($ch);
		curl_close($ch);
		$token = json_decode($response, true);
		
		return $token;
	}
	
	
	//	Find Customer
	function findCustomer($customer_id){
		
		$url = "https://api.stripe.com/v1/customers/".$customer_id;
		
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$headers = array("Authorization: Bearer ".$this->stripe_api_key);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		$response = curl_exec($ch);
		curl_close($ch);
		$customer = json_decode($response, true);
		
		return $customer;
	}
	
	
	
	//	Create a SetupIntent
	function createASetupIntent($customer_id){
		
		$url = "https://api.stripe.com/v1/setup_intents";
		$post = json_encode(['payment_method_types' => ['card_present'], 'customer' => $customer_id]);
		
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		
		$headers = array("Authorization: Bearer ".$this->stripe_api_key);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		$response = curl_exec($ch);
		curl_close($ch);
		$intent = json_decode($response, true);
		
		return $intent;
    }
	
	//	Collect a payment method for saving cards
	function collectAPaymentMethodForSavingCards($data){
		
		$url = "https://api.stripe.com/v1/terminal/readers/".$data['client_secret']."/process_setup_intent";
		
		$intent_id = json_encode(array('id' => $data['intent_id'], 'client_secret' => $data['client_secret']));
		
		print_r("<pre>");
		print_r($data);
		print_r("</pre>");
		//exit;
		
		$post = array('setup_intent' => $data['intent_id'], 'client_secret' => $data['client_secret'], 'customer_consent_collected' => "true");
		$post = json_encode($post);
		
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		
		$headers = array("Authorization: Bearer ".$this->stripe_api_key);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		$response = curl_exec($ch);
		curl_close($ch);
		$intent = json_decode($response, true);
		
		return $intent;
    }
	
	
	
	//	Create a card token
	function create_a_card_token($data=array()){
		
		$card = array("customer"=>"cus_NjAdO7rl0llzsP", "card"=>["number"=>"4242424242424242", "exp_month"=>"04", "exp_year"=>"2025", "cvc"=>"314"]);
		
		$post = json_encode($data);

		$url = "https://api.stripe.com/v1/tokens";
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		
		$headers = array("Authorization: Bearer ".$this->stripe_api_key);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		$response = curl_exec($ch);
		curl_close($ch);
		$res = json_decode($response, true);
		
		return $res;
    }
	
	
	
	
}