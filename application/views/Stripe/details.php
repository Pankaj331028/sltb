<!DOCTYPE html>
<html lang="en-US">
<head>
<title>Purchase - Stripe Payment Gateway Integration by CodexWorld</title>
<meta charset="utf-8">

<!-- Stylesheet file -->
<!--<link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">-->
<style type="text/css">
.container{padding:20px}h1{color:#7a7a7a;font-size:28px;text-transform:uppercase;text-align:center}.pro-box{width:25%;position:relative;display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-ms-flex-direction:column;flex-direction:column;min-width:0;word-wrap:break-word;background-color:#fff;background-clip:border-box;border:1px solid rgba(0,0,0,.125);border-radius:.25rem;float:left;margin-right:10px;margin-bottom:10px}.pro-box .info{-webkit-box-flex:1;-ms-flex:1 1 auto;flex:1 1 auto;padding:1.25rem}.pro-box h4{font-size:1.25rem;font-weight:500;margin-bottom:.75rem;color:#333;margin-top:0}.pro-box h5{font-size:1rem;font-weight:500;margin-bottom:.75rem;color:#666}.action{padding:10px}.action a{display:inline-block;font-weight:400;text-align:center;white-space:nowrap;vertical-align:middle;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;border:1px solid transparent;padding:.375rem .75rem;font-size:1rem;line-height:1.5;border-radius:.25rem;transition:color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;color:#fff;background-color:#007bff;border-color:#007bff;text-decoration:none}.action a:active,.action a:hover{color:#fff;background-color:#0069d9;border-color:#0062cc}.action a:hover{color:#fff;background-color:#0069d9;border-color:#0062cc}.action a:focus{box-shadow:0 0 0 .2rem rgba(0,123,255,.5)}.panel{width:350px;margin:0 auto;background-color:#fff;border:1px solid transparent;border-radius:4px;-webkit-box-shadow:0 1px 1px rgba(0,0,0,.05);box-shadow:0 2px 5px 0 rgba(0,0,0,.16),0 2px 10px 0 rgba(0,0,0,.12);border-color:#ddd}.panel-heading{padding:10px 15px;border-bottom:1px solid transparent;border-top-left-radius:3px;border-top-right-radius:3px}.panel>.panel-heading{color:#333;background-color:#f5f5f5;border-color:#ddd}.panel-title{margin-top:0;margin-bottom:0;font-size:20px;color:#333;font-weight:600}.panel-body{padding:15px}.form-group{margin-bottom:15px}label{display:inline-block;margin-bottom:5px;font-weight:700}.field{display:block;width:90%;height:30px;padding:6px 12px;font-size:15px;line-height:1.2;color:#555;background-color:#fff;background-image:none;border:1px solid #ccc;border-radius:4px;-webkit-box-shadow:inset 0 1px 1px rgba(0,0,0,.075);box-shadow:inset 0 1px 1px rgba(0,0,0,.075);-webkit-transition:border-color ease-in-out .15s,box-shadow ease-in-out .15s;transition:border-color ease-in-out .15s,box-shadow ease-in-out .15s}div.field{padding-bottom:0}.field:focus{border-color:#66afe9;outline:0;-webkit-box-shadow:inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgba(102,175,233,.6);box-shadow:inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgba(102,175,233,.6)}.row .left{width:45%;float:left}.row .right{width:35%;float:right}.right .field{width:75%}.form-group iframe{height:30px!important}.btn{width:100%;padding:10px 16px;font-size:18px;line-height:1.33;border-radius:6px;border:none;cursor:pointer}.btn-success{color:#fff;background-color:#5cb85c;border-color:#4cae4c}.btn-success.active,.btn-success:active,.btn-success:focus,.btn-success:hover{color:#fff;background-color:#47a447;border-color:#398439}#paymentResponse p{font-size:17px;border:1px dashed;padding:10px;color:#ea4335;margin-top:0;margin-bottom:10px}.status{padding:15px;color:#000;background-color:#f1f1f1;box-shadow:0 2px 5px 0 rgba(0,0,0,.16),0 2px 10px 0 rgba(0,0,0,.12);margin-bottom:20px}.status h1{font-size:1.8em}.status h4{font-size:1.3em;margin-bottom:0}.status p{font-size:1em;margin-bottom:0;margin-top:8px}.btn-link{display:inline-block;font-weight:400;text-align:center;white-space:nowrap;vertical-align:middle;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;border:1px solid transparent;padding:.375rem .75rem;font-size:1rem;line-height:1.5;border-radius:.25rem;transition:color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;text-decoration:none}.btn-link{color:#007bff;background-color:transparent;border-color:#007bff}.btn-link:active,.btn-link:focus,.btn-link:hover{color:#fff;background-color:#007bff;border-color:#007bff;text-decoration:none}.success{color:#34a853}.error{color:#ea4335}
</style>
	
<!-- Stripe JS library -->
<script src="https://js.stripe.com/v3/"></script>

</head>
<body>
<div class="container">
	<h1>Purchase - Stripe Payment Gateway Integration</h1>
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Charge <?php echo '$'.$product['price']; ?> with Stripe</h3>
			
			<!-- Product Info -->
			<p><b>Item Name:</b> <?php echo $product['name']; ?></p>
			<p><b>Price:</b> <?php echo '$'.$product['price'].' '.$product['currency']; ?></p>
		</div>
		<div class="panel-body">
			<!-- Display errors returned by createToken -->
			<div id="paymentResponse"></div>
			
			<!-- Payment form -->
			<form action="" method="POST" id="paymentFrm">
				<div class="form-group">
					<label>NAME</label>
					<input type="text" name="name" id="name" class="field" placeholder="Enter name" required="" autofocus="">
				</div>
				<div class="form-group">
					<label>EMAIL</label>
					<input type="email" name="email" id="email" class="field" placeholder="Enter email" required="">
				</div>
				<div class="form-group">
					<label>CARD NUMBER</label>
					<div id="card_number" class="field"></div>
				</div>
				<div class="row">
					<div class="left">
						<div class="form-group">
							<label>EXPIRY DATE</label>
							<div id="card_expiry" class="field"></div>
						</div>
					</div>
					<div class="right">
						<div class="form-group">
							<label>CVC CODE</label>
							<div id="card_cvc" class="field"></div>
						</div>
					</div>
				</div>
				<button type="submit" class="btn btn-success" id="payBtn">Submit Payment</button>
			</form>
		</div>
	</div>
</div>

<script>
// Create an instance of the Stripe object
// Set your publishable API key
var stripe = Stripe('<?php echo $this->config->item('stripe_publishable_key'); ?>');

// Create an instance of elements
var elements = stripe.elements();

var style = {
    base: {
		fontWeight: 400,
		fontFamily: 'Roboto, Open Sans, Segoe UI, sans-serif',
		fontSize: '16px',
		lineHeight: '1.4',
		color: '#555',
		backgroundColor: '#fff',
		'::placeholder': {
			color: '#888',
		},
	},
	invalid: {
	  color: '#eb1c26',
	}
};

var cardElement = elements.create('cardNumber', {
	style: style
});
cardElement.mount('#card_number');

var exp = elements.create('cardExpiry', {
  'style': style
});
exp.mount('#card_expiry');

var cvc = elements.create('cardCvc', {
  'style': style
});
cvc.mount('#card_cvc');

// Validate input of the card elements
var resultContainer = document.getElementById('paymentResponse');
cardElement.addEventListener('change', function(event) {
	if (event.error) {
		resultContainer.innerHTML = '<p>'+event.error.message+'</p>';
	} else {
		resultContainer.innerHTML = '';
	}
});

// Get payment form element
var form = document.getElementById('paymentFrm');

// Create a token when the form is submitted.
form.addEventListener('submit', function(e) {
	e.preventDefault();
	createToken();
});

// Create single-use token to charge the user
function createToken() {
	stripe.createToken(cardElement).then(function(result) {
		if (result.error) {
			// Inform the user if there was an error
			resultContainer.innerHTML = '<p>'+result.error.message+'</p>';
		} else {
			// Send the token to your server
			stripeTokenHandler(result.token);
		}
	});
}

// Callback to handle the response from stripe
function stripeTokenHandler(token) {
	// Insert the token ID into the form so it gets submitted to the server
	var hiddenInput = document.createElement('input');
	hiddenInput.setAttribute('type', 'hidden');
	hiddenInput.setAttribute('name', 'stripeToken');
	hiddenInput.setAttribute('value', token.id);
	form.appendChild(hiddenInput);
	
	// Submit the form
	form.submit();
}
</script>
</body>
</html>