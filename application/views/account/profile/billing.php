<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<!DOCTYPE html>
<html>
<head>
<?php	$this->load->view("account/inc/head");?>

</head>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
<?php	$this->load->view("Site/inc/header");?>

<?php
$user = $GLOBALS["loguser"];
if ($user['image'] != '' && $user['image'] != ' ') {$prf_img = $user['image'];} else { $prf_img = 'assets/crm/dist/img/user4-128x128.jpg';}

$fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);

?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><strong>Payment</strong></h1>
    </section>

    <!-- Main content -->
    <section class="content">

      <div class="row">
        <div class="col-md-12">
          <div class="nav-tabs-custom">
<ul class="nav nav-tabs">
<?php	if ($GLOBALS["loguser"]["role"] == "Company") {?>
<li><a href="<?php echo base_url("account/company"); ?>"><i class="fa fa-globe"></i> Company</a></li>
<li><a href="<?php echo base_url("account/team"); ?>"><i class="fa fa-user"></i> Users</a></li>
<li><a href="<?php echo base_url("account/emails"); ?>"><i class="fa fa-envelope-o"></i> SMTP Emails</a></li>
<li class="active"><a href="<?php echo base_url("account/billing"); ?>"><i class="fa fa-credit-card-alt"></i> Payment</a></li>
<li><a href="<?php echo base_url("integration"); ?>"><i class="fa fa-share"></i> Integrations</a></li>
<li><a href="<?php echo base_url("account/reminders"); ?>"><i class="fa fa-credit-card-alt"></i> Reminders</a></li>
<?php	}?>
<li><a href="<?php echo base_url("account/profile"); ?>"><i class="fa fa-pencil"></i> My Profile</a></li>
<li><a href="<?php echo base_url("account/cp"); ?>"><i class="fa fa-lock"></i> Change Password</a></li>

</ul>
            <div class="tab-content">

              <div class="active tab-pane" id="settings">
              	<?php

$fields = $this->db->query("SELECT * from settings Limit 1")->row_array();
$company_id = $GLOBALS['loguser']['company_id'];

$usersCompanyDetails = $this->db->query("SELECT * FROM users_company where id='" . $company_id . "'")->row_array();

?>
              	<?php
if (!$this->session->flashdata('error')) {
	if (trim($usersCompanyDetails['stripe_card_id']) == "" || trim($usersCompanyDetails['card_last_four']) == "") {
		$this->session->set_flashdata('error', "Please add your credit/debit card details. Failing to add this may result in the missing payments and you losing business.");
	}
}
?>
<?php	$this->load->view("template/alert.php");?>

<div class="row">

<div class="col-md-12">
	<div class="panel panel-primary">
		<div class="panel-heading"> <strong>Select the type of account you would like:</strong></div>
		<div class="panel-body" style="padding:10px;">
			<form method="post" enctype="multipart/form-data" name="" action="<?php echo site_url('account/billing/save-card'); ?>">
				<div class="row">
					<input type="hidden" name="action" value="add">
					<div class="form-group col-md-12">
						<div>

							<input type="radio" name="type_account" value="0" <?php if ($usersCompanyDetails['account_type'] == '0') {
	echo "checked='checked'";
}
?>/>
							<label for="" class="ml-2" >Pay As You Go  </label> <span>–This option allows you to add as many users as you want from your company and charges per client as follows:
								<br> <li> <strong> $<?php	echo $fields['review_fee']; ?></strong>-For each Client who you View, you will automatically incur this fee which allows you to complete the Intake Program, conduct a full analysis, Attestation Qualification, and use the advertising and secure document transfer features of the system. If you choose not to View the client, you will NOT be charged.  </li>
								<li>For each Client you have already Viewed/Analyzed, if you add them to a Program other than the Intake Program, you will automatically incur a <strong>$<?php	echo $fields['program_fee']; ?></strong> fee. This fee allows you to use all the services of the system for that one client including multiple Programs, the Reminder system, the Task Management system and the Secure Document Exchange for 179 days from the date the Client was added to a Program.</li>
							</span>
						</div>
					</div>
					<div class="clr"></div>
					<div class="form-group col-md-12">
						<div>
							<input type="radio" name="type_account" value="1" <?php if ($usersCompanyDetails['account_type'] == '1') {
	echo "checked='checked'";
}
?>/>
							<label for="" class="ml-2" >Monthly Subscription </label> <span>– You will automatically be billed each month a fee of <strong>$<?php	echo $fields['initial_user_fee']; ?></strong> plus <strong>$<?php	echo $fields['additional_user_fee']; ?></strong> for each additional User you add to this account. All Users will have full access to use the entire program for an unlimited number of Clients. You can add and remove Users at any time which will result in a pro-rated charge for the time they had access to the system.
							</span>
						</div>
					</div>
					<div class="form-group col-md-12">
						<div>
							<input type="checkbox" name="privacy_policy" value="1" /> <span>You understand you can switch your Account Type at any time. If you choose to switch Account Type, the fee to do so will be calculated at that time based on the number of Users and Clients you have in your Account. You will be given the opportunity to cancel the switch if you wish at that time. You understand you can cancel the Account entirely at any time but will not receive any credit for any unused time left in the month. You also acknowledge that you have read and agree to abide by the Privacy Policy and Terms and Conditions of this site.
							</span>
						</div>
					</div>
					<div class="clr"></div>
				</div>
				<div class="form-group">
				<!-- <a href="<?php echo base_url('account/billing/save-card'); ?>" class="btn btn-primary">&raquo; Save</a> -->
					<button type="submit" name="Submit" class="btn btn-success">&raquo; Save</button>
					<button type="reset" name="reset" class="btn btn-default">Cancel</button>
				</div>
			</form>
		</div>
	</div>
</div>
<div class="col-md-6">

<div class="panel panel-primary">
  <div class="panel-heading"><i class="fa fa-credit-card-alt"></i> <strong>Saved Card</strong></div>
  <div class="panel-body" style="padding:0px;">

<table class="table table-bordered" style="margin-bottom:0px;">
<tr class="info" style="font-weight:bold;">	<td>Card No.</td>	<td>Action</td>	</tr>
<?php
if (!empty($usersCompanyDetails['card_last_four'])) {
	?>
<tr>
<td>**** <?php echo $usersCompanyDetails['card_last_four']; ?></td>
<td><a href="<?=site_url('account/billing/change-card')?>">Update Card</a></td>
</tr>
<?php
} else {
	?>

<tr>
<td>&nbsp;</td>
<td><a href="<?=site_url('account/billing/change-card')?>">Add Card</a></td>
</tr>
	<?php
}
?>
</table>
  </div>
</div>
<div class="clr"></div>
<?php
if ($this->uri->segment(3) != "") {
	$q = $this->db->query("SELECT * FROM payments where company_id='" . $GLOBALS["loguser"]["id"] . "' and payment_id='" . $this->uri->segment(3) . "'");
	$nr = $q->num_rows();
	if ($nr == 1) {
		$order = $q->row_array();
		?>
<div class="status">
<?php if ($order['payment_status'] == 'succeeded') {

			if ($order['discount_amount'] > 0) {
				$chkq = $this->db->query("SELECT * FROM promo_code_usage where company_id='" . $GLOBALS["loguser"]["id"] . "' and payment_id='" . $this->uri->segment(3) . "'");
				$chkn = $chkq->num_rows();
				if ($chkn == 0) {
					$this->db->insert("promo_code_usage", ['company_id' => $GLOBALS["loguser"]["id"], 'payment_id' => $this->uri->segment(3), 'promo_code' => $order["promo_code"]]);
					$this->crm_model->set_promo_code_usage($order["promo_code"]); //	Set Promo Code Usage
				}

				$cmpR = $this->crm_model->get_company_details($GLOBALS["loguser"]["id"]);
				$cpnR = $this->crm_model->check_coupon_code($cmpR["promo_code"], $GLOBALS["loguser"]["id"]);
			}
			?>
<h1 class="success">Your Payment has been Successful!</h1>
<?php } else {?>
<h1 class="error">The transaction was successful! But your payment has been failed!</h1>
<?php }?>

<h4>Payment Information</h4>
<p><b>Reference Number:</b> <?php echo $order['payment_id']; ?></p>
<p><b>Transaction ID:</b> <?php echo $order['txn_id']; ?></p>
<p><b>Paid Amount:</b> <?php echo $order['amount_paid'] . ' ' . strtoupper($order['paid_amount_currency']); ?></p>
<p><b>Payment Status:</b> <?php echo ucfirst($order['payment_status']); ?></p>
</div>
<?php
} else {
		$this->session->set_flashdata('error', "The transaction has failed.");
		//redirect(base_url('account/billing'));
		//exit;
	}
} else {
	$sno = 0;
	$q = $this->db->query("SELECT ac.*,uc.next_payment_date FROM account_payment_info ac join users_company uc ON uc.id=ac.company_id where company_id='" . $GLOBALS["loguser"]["id"] . "' and uc.next_payment_date <= '" . date('Y-m-d') . "' order by updated_at desc");
	$nr = $q->num_rows();
	if ($nr > 0) {

		$cmpR = $this->crm_model->get_company_details($GLOBALS["loguser"]["id"]);
		$cpnR = $this->crm_model->check_coupon_code($cmpR["promo_code"], $GLOBALS["loguser"]["id"]);
		$cpnR_data = json_decode($cpnR, true);

		$coupon_code_msg = '';
		$discount_amount = 0;
		if ($cpnR_data['status'] == "Success") {
			$coupon_code_msg = '<div style="color:green; margin:15px 0 0 0;"><p><strong>Promo code applied</strong></p>
	<table class="table table-bordered">
	<tr><th width="95">Promo Code</th><td>' . $cpnR_data['data']['promo_code'] . '</td></tr>
	<tr><th>Title</th><td>' . $cpnR_data['data']['promo_code_name'] . '</td></tr>
	<tr><th>Description</th><td>' . $cpnR_data['data']['promo_code_description'] . '</td></tr>
	</table></div>';

			$cpnuq = $this->db->query("SELECT sum(1st_user_fee) as 1st_user_fee, sum(additional_user_fee) as additional_user_fee FROM account_payment_info ac join users_company uc on uc.id=ac.company_id where ac.company_id='" . $GLOBALS["loguser"]["id"] . "' and uc.next_payment_date < '" . date('Y-m-d') . "'");
			$cpnur = $cpnuq->row_array();
			$price = ($cpnur['1st_user_fee'] + $cpnur['additional_user_fee']);

			$discount_amount = $this->crm_model->calculate_coupon_code_discount($price, $cmpR, $cpnR_data);

		}
		?>

<table class="table table-bordered">
<tr class="info">	<th width="130">Date</th>	<th>Amount</th></tr>
<?php
foreach ($q->result_array() as $row) {
			?>
<tr>
	<td><?php echo date('m/d/Y', strtotime($row['next_payment_date'])) ?></td>
    <td><?php echo ($row['1st_user_fee'] + $row['additional_user_fee']); ?></td>
</tr>
<?php
if ($discount_amount > ($row['1st_user_fee'] + $row['additional_user_fee'])) {$discount_amount = 0;}
			$paidAmount = $total = ($row['1st_user_fee'] + $row['additional_user_fee']);

			if ($discount_amount > 0) {
				$total = ($paidAmount - $discount_amount);
				?>
<tr>	<th>Coupon Discount</th>	<td><?php	echo number_format($discount_amount, 2); ?></td>	</tr>
<tr>	<th>Total</th>	<td><?php	echo number_format($total, 2); ?></td>	</tr>
<?php
}
			if ($total <= 0) {
				// Insert tansaction data into the database
				$txn_id = time();
				$orderData = array(
					'company_id' => $GLOBALS["loguser"]["id"],
					'account_name' => $GLOBALS["loguser"]["name"],
					'account_email' => $GLOBALS["loguser"]["email"],
					'amount_paid' => $paidAmount,
					'discount_amount' => $discount_amount,
					'promo_code' => $cmpR["promo_code"],
					'paid_amount_currency' => "usd",
					'txn_id' => $txn_id,
					'payment_status' => "succeeded",
				);
				$this->db->insert("payments", $orderData);
				$payment_id = $this->db->insert_id();

				$this->db->insert("promo_code_usage", ['company_id' => $GLOBALS["loguser"]["id"], 'payment_id' => $payment_id, 'promo_code' => $cmpR["promo_code"]]);
				$this->crm_model->set_promo_code_usage($cmpR["promo_code"]); //	Set Promo Code Usage

				$this->db->query("delete from account_payment_info where company_id='" . $GLOBALS["loguser"]["id"] . "'");
				redirect(base_url("account/billing"));
				exit;
			}
		}
		?>
</table>

<div style="margin-top:10px;">
<div style="padding:15px; background:#f8f8f8; max-width:350px;">
<form action="javascript:void(0)" method="post" enctype="multipart/form-data" name="coupon_code_form">
<p><strong>Enter Promo Code</strong></p>
<div class="input-group">
	<input type="text" class="form-control" name="coupon_code" value="<?php echo $cmpR["promo_code"]; ?>" required />
    <span class="input-group-btn"><button type="button" class="btn btn-info btn-flat" name="Submit_" onClick="apply_coupon_code('<?php echo base_url("home/apply_coupon_code") ?>', 'coupon_code_form', 'coupon_code_msg')">Apply</button></span>
</div>
<div id="coupon_code_msg"><?php	echo $coupon_code_msg; ?></div>
</form>
</div>
<p>&nbsp;</p>
<p><a href="<?php echo base_url('account/billing/pay'); ?>" class="btn btn-primary">&raquo; Pay Now</a></p>
</div>
<?php	} else {?>

<div class="alert alert-info" style="margin:10px 0px 250px 0;">No Payment Due.</div>
<?php	}}?>
<div class="clr"></div>
</div>

<?php
if (isset($company_payments_history)) {
	if (count($company_payments_history) > 0) {
		?>
<div class="col-md-6">
<div class="panel panel-primary">
  <div class="panel-heading"><i class="fa fa-credit-card-alt"></i> <strong>Recent Payments</strong></div>
  <div class="panel-body" style="padding:0px;">

<table class="table table-bordered" style="margin-bottom:0px;">
<tr class="info" style="font-weight:bold;">	<td>Date</td>	<td>Txn. ID</td> <td>Amount</td> <td>Discount</td> <td>Paid Amount</td>	</tr>
<?php
foreach ($company_payments_history as $row) {
			?>
<tr>
<td><?php echo date("Y/m/d", strtotime($row['created_at'])); ?></td>
<td><?php echo $row['txn_id']; ?></td>
<td><?php echo $fmt->formatCurrency($row['amount_paid'], "USD"); ?></td>
<td><?php echo $fmt->formatCurrency($row['discount_amount'], "USD"); ?></td>
<td><?php echo $fmt->formatCurrency(($row['amount_paid'] - $row['discount_amount']), "USD"); ?></td>
</tr>
<?php }?>
</table>
  </div>
</div>
</div>

<?php }}?>
</div>

              </div>
              <!-- /.tab-pane -->
            </div>
            <!-- /.tab-content -->
          </div>
          <!-- /.nav-tabs-custom -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->

    </section>
    <!-- /.content -->
  </div>

<?php	$this->load->view("account/inc/footer");?>

</div>
<?php	$this->load->view("account/inc/template_js.php");?>



<script>
// Create an instance of the Stripe object
// Set your publishable API key
var stripe = Stripe('<?php echo $this->config->item('pk_test_51HybVTDElfRyXcm8p68OTlNaTC8EAZHPPWr62phdoB6eh1gK4x6Tf0Oe8k2wtsLCLUrywEctPqMNfKQqUthaEK6100adN3deSR'); ?>');

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
