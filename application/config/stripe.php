<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
|  Stripe API Configuration
| -------------------------------------------------------------------
|
| You will get the API keys from Developers panel of the Stripe account
| Login to Stripe account (https://dashboard.stripe.com/)
| and navigate to the Developers � API keys page
| Remember to switch to your live publishable and secret key in production!
|
|  stripe_api_key        	string   Your Stripe API Secret key.
|  stripe_publishable_key	string   Your Stripe API Publishable key.
|  stripe_currency   		string   Currency code.
*/
//$config['stripe_api_key']         = 'Your_API_Secret_key'; 
//$config['stripe_publishable_key'] = 'Your_API_Publishable_key'; 

$config['stripe_api_key']         = 'sk_test_51K8qlgFTyXBXcCqlZq2o8Ei4ur5Ml9ggJmHWrPCUQxKKEpN73iLsENUz6qGrdeypuCVYrBUo3VpZl6TQCzoN1eEH00Hjf7bJ95'; 
$config['stripe_publishable_key'] = 'pk_test_51K8qlgFTyXBXcCqlGJLT9HltoZslgmjy5unyU9yk2FhMu4pbOblADSlrYHvrdYoTGIdjgqBB5BAhTvVxPIWKvTEs00tUSOdx5J';

// $config['stripe_api_key']         = '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'; 
// $config['stripe_publishable_key'] = 'pk_live_51KHLzqBwv9L6YBSJ2GpKgnke42MPvYIwyWvzqhzzcQdbFak1i1FRntebOhmc3EERuVCmJn5KFJxnxHKuev1QWIHk00Ja0WoHLS';

$config['stripe_currency']        = 'usd';