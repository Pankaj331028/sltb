<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<!DOCTYPE html>
<html>
<head>
<?php	$this->load->view("account/inc/head");?>
</head>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
<?php	$this->load->view("Site/inc/header");?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><strong>SMTP Emails</strong></h1>
    </section>

    <!-- Main content -->
    <section class="content">

      <div class="row">

        <div class="col-md-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
<?php	if ($GLOBALS["loguser"]["role"] == "Company") {?>
<li><a href="<?php echo base_url("account/company"); ?>" onClick="window.location.href=this.href"><i class="fa fa-globe"></i> Company</a></li>
<li><a href="<?php echo base_url("account/team"); ?>"><i class="fa fa-user"></i> Users</a></li>
<li class="active"><a href="<?php echo base_url("account/emails"); ?>"><i class="fa fa-envelope-o"></i> SMTP Emails</a></li>
<li><a href="<?php echo base_url("account/billing"); ?>"><i class="fa fa-credit-card-alt"></i> Payment</a></li>
<li><a href="<?php echo base_url("integration"); ?>"><i class="fa fa-share"></i> Integrations</a></li>
<li><a href="<?php echo base_url("account/reminders"); ?>"><i class="fa fa-credit-card-alt"></i> Reminders</a></li>
<?php	}?>
<li><a href="<?php echo base_url("account/profile"); ?>"><i class="fa fa-pencil"></i> My Profile</a></li>
<li><a href="<?php echo base_url("account/cp"); ?>"><i class="fa fa-lock"></i> Change Password</a></li>

</ul>
            <div class="tab-content">

              <div class="active tab-pane" id="settings">
<?php
if (!$this->session->flashdata('error')) {

	if (trim($company_emails['smtp_hostname']) == "") {
		$this->session->set_flashdata('error', "Please complete SMTP Email Details.");
	} else if (trim($company_emails['status']) != "Confirmed") {$this->session->set_flashdata('error', "Please enter valid SMTP Email Details.");}
}

$this->load->view("template/alert.php");?>
<?php echo form_open(current_url(), array('enctype' => 'multipart/form-data')); ?>

<div>

<!-- <div class="form-group col-md-4">
    <label for="inputName" class="control-label">From Email *<br /><span class="form_input_hint">(This is the from address that will show to your clients)</span></label>
    <div><?php	//echo form_input(['type' => 'email', 'name' => 'from_email', 'value' => trim($company_emails['from_email']), 'class' => 'form-control', 'required' => 'required']); ?></div>
</div>
<div class="clr"></div>


<div class="form-group col-md-4">
    <label for="inputName" class="control-label">From Display *<br /><span class="form_input_hint">(What do you want the From to say. Example, fred@lawyer.com could show as Friendly Fred Attorney at Law)</span></label>
    <div><?php	//echo form_input(['type' => 'text', 'name' => 'from_display', 'value' => trim($company_emails['from_display']), 'class' => 'form-control', 'required' => 'required']); ?></div>
</div>
<div class="clr"></div> -->
<!--
<div class="form-group col-md-4">
    <label for="inputName" class="control-label">Reply To email *<br /><span class="form_input_hint">(Example could be the same as the From Address)</span></label>
    <div><?php	//echo form_input(['type' => 'text', 'name' => 'reply_to_email', 'value' => trim($company_emails['reply_to_email']), 'class' => 'form-control', 'required' => 'required']); ?></div>
</div>
<div class="clr"></div>
 -->

<div class="form-group col-md-4">
    <label for="inputName" class="control-label">SMTP Hostname *<br /><span class="form_input_hint">(smtp.my_domain.com)</span></label>
    <div><?php	echo form_input(['type' => 'text', 'name' => 'smtp_hostname', 'value' => trim($company_emails['smtp_hostname']), 'class' => 'form-control', 'required' => 'required']); ?></div>
</div>

<div class="form-group col-md-4">
    <label for="inputName" class="control-label">Outgoing SMTP Port *<br /><span class="form_input_hint">(25 or 465 or 567 are typical)</span></label>
    <div><?php	echo form_input(['type' => 'text', 'name' => 'smtp_outgoing_port', 'value' => trim($company_emails['smtp_outgoing_port']), 'class' => 'form-control', 'required' => 'required']); ?></div>
</div>
<div class="clr"></div>

<div class="form-group col-md-4">
    <label for="inputName" class="control-label">SMTP Security *</label>
    <div><?php	echo form_dropdown('smtp_security', ['None' => 'None', 'SSL' => 'SSL', 'TLS' => 'TLS'], trim($company_emails['smtp_security']), ['class' => 'form-control', 'required' => 'required']); ?></div>
</div>
<div class="clr"></div>

<!-- <div class="form-group col-md-4">
    <label for="inputName" class="control-label">SMTP From Email *<br /><span class="form_input_hint">(The username of the eFrom email account)</span></label>
    <div><?php	?></div>
</div> -->

<!-- <div class="form-group col-md-4">
    <label for="inputName" class="control-label">SMTP Email Password *<br /><span class="form_input_hint">(The password to the From email account)</span></label>
    <div><?php	?></div>
</div> -->
</div>


<div class="form-group col-md-12">
<button type="submit" name="Submit_Test" class="btn btn-warning">Test Email</button>
<!--<button type="submit" name="Submit_" class="btn btn-primary">Send</button>-->
<button type="submit" name="Submit_" class="btn btn-success">Save</button>
<a href="<?php	echo base_url("account/emails?reset=yes"); ?>" class="btn btn-default" onClick="return confirm('Are you sure.')">Reset</a>
</div>
<div class="clr"></div>
<?php echo form_close(); ?>
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
</body>
</html>
