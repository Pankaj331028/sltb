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
      <h1><strong>My Account</strong></h1>
    </section>

    <!-- Main content -->
    <section class="content">

      <div class="row">

        <div class="col-md-12">
          <div class="nav-tabs-custom">
<ul class="nav nav-tabs">
<?php	if ($GLOBALS["loguser"]["role"] == "Company") {?>
<li class="active"><a href="<?php echo base_url("account/company"); ?>"><i class="fa fa-globe"></i> Company</a></li>
<li><a href="<?php echo base_url("account/team"); ?>"><i class="fa fa-user"></i> Users</a></li>
<li><a href="<?php echo base_url("account/emails"); ?>"><i class="fa fa-envelope-o"></i> SMTP Emails</a></li>
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
	$error = "";
	if (trim($company['name']) == "") {$error .= '<li>Company Name</li>';}
	if (trim($company['phone']) == "") {$error .= '<li>Main Office Phone Number</li>';}
	if (trim($company['email']) == "") {$error .= '<li>Main Office Email</li>';}
	if (trim($company['address']) == "") {$error .= '<li>Address</li>';}
	if (trim($company['city']) == "") {$error .= '<li>City</li>';}
	if (trim($company['state']) == "") {$error .= '<li>State</li>';}
//if(!file_exists($company['logo'])) {	$error .= '<li>Company logo</li>';	}
	if (trim($company['case_manager']) == "") {$error .= '<li>Default case manager</li>';}
	if ($error != "") {
		$error = '<strong>Please update below required details:</strong><hr style="margin:5px 0px 10px 0px;" /><ul>' . $error . '</ul>';
		$this->session->set_flashdata('error', $error);
	}
}

$this->load->view("template/alert.php");?>
<?php echo form_open(current_url(), array('enctype' => 'multipart/form-data')); ?>

<div class="row">
<div class="form-group col-md-6"><label for="e">Company Name *</label><?php	echo form_input(['type' => 'text', 'name' => 'name', 'value' => $company['name'], 'class' => 'form-control', 'required' => 'required']); ?></div>
<div class="clr"></div>

<div class="form-group col-md-6"><label for="e">Address 1 *</label><?php	echo form_textarea(['name' => 'address', 'value' => trim($company['address']), 'class' => 'form-control', 'rows' => '1', 'required' => 'required']); ?></div>
<div class="clr"></div>

<div class="form-group col-md-6"><label for="e">Address 2 </label><?php	echo form_textarea(['name' => 'address_2', 'value' => trim($company['address_2']), 'class' => 'form-control', 'rows' => '1']); ?></div>
<div class="clr"></div>

<div class="form-group col-md-4"><label for="e">City *</label><?php	echo form_input(['type' => 'text', 'name' => 'city', 'value' => trim($company['city']), 'class' => 'form-control', 'required' => 'required']); ?></div>

<div class="form-group col-md-4"><label for="e">State *</label><?php	echo form_dropdown('state', $this->array_model->state_list(), trim($company['state']), ['class' => 'form-control', 'required' => 'required']); ?></div>


<div class="form-group col-md-4"><label for="e">Zip Code *</label><?php	echo form_input(['type' => 'text', 'name' => 'zip_code', 'value' => trim($company['zip_code']), 'class' => 'form-control', 'required' => 'required']); ?></div>

<div class="form-group col-md-4"><label for="e">Main Office Phone Number *</label><?php	echo form_input(['type' => 'text', 'name' => 'phone', 'value' => $company['phone'], 'class' => 'form-control', 'required' => 'required']); ?></div>

<div class="form-group col-md-4"><label for="e">Main Office Email *</label><?php	echo form_input(['type' => 'email', 'name' => 'email', 'value' => trim($company['email']), 'class' => 'form-control', 'required' => 'required']); ?></div>
<div class="clr"></div>

<div class="form-group col-md-4"><label for="e">Auto-Request New NSLDS for client every *</label><?php	echo form_input(['type' => 'number', 'name' => 'auto_request_new_nslds_for_client_every', 'value' => trim($company['auto_request_new_nslds_for_client_every']), 'class' => 'form-control', 'required' => 'required']); ?></div>

<?php
$q = $this->db->query("SELECT * FROM users where (role='Company User' or role='Company') and company_id='" . $GLOBALS["loguser"]["id"] . "' order by name asc");
$cmn = $q->num_rows();
?>

<div class="form-group col-md-4"><label for="e">Default Case Manager <?php if ($cmn > 0) {echo "*";}?></label>
<select name="case_manager" class="form-control" <?php if ($cmn > 0) {echo " required";}?>>
<option value="">Select Case Manager</option>
<?php	foreach ($q->result() as $r) {?>
<option value="<?php echo $r->id ?>" <?php	if ($company['case_manager'] == $r->id) {echo " selected";}?>><?php echo $r->name ?> <?php echo $r->lname ?></option>
<?php	}?>
</select>
</div>



<div class="form-group col-md-4"><label for="e">Company Logo</label>
<input type="file" name="logo_img" class="form-control" accept="image/*" <?php	/*if(!file_exists($company['logo'])) { echo " required"; }*/?> />
<a class="btn-link">(Max Width:auto; Max Height:50px;)</a>
<?php	if (file_exists($company['logo'])) {?><br /><img src="<?php echo base_url($company['logo']); ?>" height="50" alt="Logo" /><?php	}?>
</div>

<div class="clr"></div>

<!-- <div class="form-group col-md-4"><label for="e">Send intake reminder *</label><?php	echo form_input(['type' => 'number', 'name' => 'send_intake_reminder', 'value' => trim($company['send_intake_reminder']), 'class' => 'form-control', 'min' => '1', 'required' => 'required']); ?></div>


<div class="form-group col-md-4"><label for="e">Send schedule payment reminder *</label><?php	echo form_input(['type' => 'number', 'name' => 'send_schedule_payment_reminder', 'value' => trim($company['send_schedule_payment_reminder']), 'class' => 'form-control', 'min' => '1', 'required' => 'required']); ?></div>

<div class="form-group col-md-4"><label for="e">Send analysis follow up reminder *</label><?php	echo form_input(['type' => 'number', 'name' => 'send_analysis_follow_up_reminder', 'value' => trim($company['send_analysis_follow_up_reminder']), 'class' => 'form-control', 'min' => '1', 'required' => 'required']); ?></div> -->


<div class="form-group col-md-4"><label for="e">Analysis fee *</label><?php	echo form_input(['type' => 'number', 'name' => 'analysis_fee', 'value' => trim($company['analysis_fee']), 'class' => 'form-control', 'min' => '0', 'required' => 'required']); ?></div>


<div class="form-group col-md-4"><label for="e">Payment link *</label><?php	echo form_input(['type' => 'url', 'name' => 'payment_link', 'value' => trim($company['payment_link']), 'class' => 'form-control', 'required' => 'required']); ?></div>


<!-- <div class="form-group col-md-4"><label for="e">Calendar link *</label></div> -->


</div>


<div class="form-group"><button type="submit" name="Submit_" class="btn btn-primary">Submit</button></div>
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
