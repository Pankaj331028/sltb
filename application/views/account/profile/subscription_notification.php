<?php

$billing_amt = 0;
$q = $this->db->query("SELECT ac.* FROM account_payment_info ac join users_company uc on uc.id=ac.company_id where ac.company_id='" . $GLOBALS["loguser"]["company_id"] . "' and uc.next_payment_date < '" . date('Y-m-d') . "' order by updated_at desc");
$nr = $q->num_rows();
if ($nr > 0) {foreach ($q->result_array() as $row) {$billing_amt += ($row['1st_user_fee'] + $row['additional_user_fee']);}}

if ($billing_amt == 0) {
	//redirect(base_url('account'));
	//exit;
}

?>
<!DOCTYPE html>
<html lang="en-US">
<head>
<title>Subscription Notification</title>
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
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Subscription Payment</h3>
		</div>
		<div class="panel-body">
        	<!-- Display errors returned by createToken -->
			<div id="paymentResponse">
            	<p>There is a billing issue with your company’s account. Please contact the Company Owner to correct the issue or have that person email <a href="mailto:support@studentloantoolbox.com">support@studentloantoolbox.com</a> for clarification.
                </p>
            </div>

			<p style="margin-top:25px;">
            	<a href="<?php echo base_url(''); ?>">Back</a>
                <a href="<?php echo base_url('account/logout'); ?>" style="float:right;">Logout</a>
            </p>

		</div>
	</div>
</div>
</body>
</html>