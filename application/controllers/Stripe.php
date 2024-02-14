<?php	defined('BASEPATH') OR exit('No direct script access allowed');
error_reporting(E_ALL ^ E_NOTICE);
extract($_POST);
extract($_GET);

class Stripe extends CI_Controller {
function __construct(){
parent::__construct();
$this->load->database();

$GLOBALS["STRIPE_MODE"] = "Yes";
$this->load->library('stripe_lib');

}


	//	Login default Page
	public function index()
	{
		
		if(isset($_POST['stripeToken']))
		{
			$token  = $_POST['stripeToken'];
			$name = $_POST['name'];
			$email = $_POST['email'];
			
			// Add customer to stripe
			
			$customer = $this->stripe_lib->addCustomer($name, $email, $token);
			$customer_id = $customer->id;
			$intent = $this->stripe_lib->createASetupIntent($customer_id);
			$intent_id = $intent['id'];
			$client_secret = $intent['client_secret'];
			
			print_r("<pre>");
			print_r($customer);
			print_r("</pre>");
			echo "<hr /> Intent";
			
			print_r("<pre>");
			print_r($intent);
			print_r("</pre>");
			echo "<hr /> Intent 2";
			
			
			$tmp_data = array("customer_id"=>$customer_id, "intent_id"=>$intent_id, "client_secret"=>$client_secret);
			$intent2 = $this->stripe_lib->collectAPaymentMethodForSavingCards($tmp_data);
			
			print_r("<pre>");
			print_r($intent2);
			print_r("</pre>");
			
			
			exit;
			
		}
		
		$customer_id = "cus_NV2t8jfdveO5CS";
		
		$data = ["customer"=>$customer_id, "card"=>["number"=>"4242424242424242", "exp_month"=>"04", "exp_year"=>"2025", "cvc"=>"314"]];
		$data = $this->stripe_lib->create_a_card_token($data);
		print_r("<pre>");
		print_r($data);
		exit;
		
		/*
		$intent_id = "seti_1Mk2nqFTyXBXcCqldTAajgdp";
		$client_secret = "seti_1Mk2nqFTyXBXcCqldTAajgdp_secret_NV2tR68h81CkaYYioYuVIB0u4NJJJhe";
		
		
		$tmp_data = array("customer_id"=>$customer_id, "intent_id"=>$intent_id, "client_secret"=>$client_secret);
		$intent2 = $this->stripe_lib->collectAPaymentMethodForSavingCards($tmp_data);
		print_r("<pre>");
		print_r($intent2);
		print_r("</pre>");
		*/
		
		$this->load->view('Stripe/Test_Stripe');		
	}
	
	
	//	Login default Page
	public function test()
	{
		//if(isset($_POST['stripeToken']))
		//{
		//@extract($_POST);
		//$token  = $_POST['stripeToken'];
		//$name = "Test 1";
		//$email = "test1@gmail.com";
		
		//$token = "tok_1MxhxhFTyXBXcCqluZQn14CD";
		//$token = "tok_1MxmCVFTyXBXcCqlejFnpZz3";
		
		// Add customer to stripe
		//$customer = $this->stripe_lib->addCustomer($name, $email, $token);
		//$customer_id = $customer->id;
		
		$customer_id = "cus_NjEfk1hHikyXX9";
		
		$charge = $this->stripe_lib->createCharge($customer_id, "Subscription Payment", "350");
		
		echo $customer_id."<br />".$token."<br />";
		print_r("<pre>");
		print_r($charge);
		print_r("</pre>");
		exit;
		//}
		
		$this->load->view('Stripe/Test_Stripe');
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
}

