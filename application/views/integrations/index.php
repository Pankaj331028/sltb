<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<!DOCTYPE html>
<html>
<head>
<?php
@extract($page_data);
$this->load->view("account/inc/head");?>

</head>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
	<?php	$this->load->view("Site/inc/header");?>

	<?php
$user = $GLOBALS["loguser"];

?>
	<div class="content-wrapper">
	    <!-- Content Header (Page header) -->
	    <section class="content-header">
	      	<h1><strong>Integrations</strong></h1>
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
							<li><a href="<?php echo base_url("account/billing"); ?>"><i class="fa fa-credit-card-alt"></i> Payment</a></li>
							<li class="active"><a href="<?php echo base_url("integration"); ?>"><i class="fa fa-share"></i> Integrations</a></li>
							<li><a href="<?php echo base_url("account/reminders"); ?>"><i class="fa fa-credit-card-alt"></i> Reminders</a></li>
							<?php	}?>
							<li><a href="<?php echo base_url("account/profile"); ?>"><i class="fa fa-pencil"></i> My Profile</a></li>
							<li><a href="<?php echo base_url("account/cp"); ?>"><i class="fa fa-lock"></i> Change Password</a></li>
						</ul>
	            		<div class="tab-content">

	              			<div class="active tab-pane" id="settings">
								<?php	$this->load->view("template/alert.php");?>

								<div class="row">
									<div class="col-md-6">
										<div class="panel panel-primary">
								  			<div class="panel-heading"> <strong>Add/Update Integration</strong></div>
							  				<div class="panel-body" style="padding:10px;">
												<form method="post" enctype="multipart/form-data" name="integration_form">
													<div class="row">
														<input type="hidden" name="action" value="add">
														<div class="form-group col-md-6">
														    <label for="partner_id" class="control-label">Partner *</label>
														    <div><?php	echo form_dropdown('partner_id', $partners, '', ['class' => 'form-control', 'id' => 'partner_id', 'required' => 'required']); ?></div>
														</div>
														<div class="form-group col-md-6">
														    <label for="partner_account_id" class="control-label">Partner Account ID *</label>
														    <div><?php	echo form_input(['type' => 'text', 'name' => 'partner_account_id', 'id' => 'partner_account_id', 'class' => 'form-control', 'required' => 'required']); ?></div>
														</div>
														<div class="clr"></div>
														<div class="form-group col-md-6">
														    <label for="partner_account_login" class="control-label">Partner Account Login Username *</label>
														    <div><?php	echo form_input(['type' => 'text', 'name' => 'partner_account_login', 'id' => 'partner_account_login', 'class' => 'form-control', 'required' => 'required']); ?></div>
														</div>
														<div class="form-group col-md-6">
														    <label for="partner_account_pswd" class="control-label">Partner Account Password *</label>
														    <div><?php	echo form_input(['type' => 'password', 'name' => 'partner_account_pswd', 'id' => 'partner_account_pswd', 'class' => 'form-control', 'required' => 'required']); ?></div>
														</div>
														<div class="clr"></div>
													</div>
													<div class="form-group">
														<button type="submit" name="Submit" class="btn btn-success">Save</button>
														<button type="reset" name="reset" class="btn btn-default">Cancel</button>
													</div>
												</form>
											</div>
										</div>
									</div>

									<?php
										if (isset($company_integrations)) {
											if (count($company_integrations) > 0) {
												?>
									<div class="col-md-6">
										<div class="panel panel-primary">
										  	<div class="panel-heading"> <strong>All Integrations</strong></div>
										  	<div class="panel-body" style="padding:0px;">

												<table class="table table-bordered" style="margin-bottom:0px;">
													<tr class="info" style="font-weight:bold;">
														<td>Partner</td>
														<td>Partner Account ID</td>
														<td>Account Login</td>
														<td>Account Password</td>
													</tr>
													<?php
													foreach ($company_integrations as $row) {
																?>
													<tr>
														<td><?php echo $row['partner_name']; ?></td>
														<td><?php echo $row['partner_account_id']; ?></td>
														<td><?php echo $row['partner_account_login']; ?></td>
														<td><?php echo '*******'; //$row['partner_account_pswd'];     ?></td>
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

</script>
</body>
</html>
