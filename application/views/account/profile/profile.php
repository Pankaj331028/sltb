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
?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><strong>My Profile</strong></h1>
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
<li><a href="<?php echo base_url("integration"); ?>"><i class="fa fa-share"></i> Integrations</a></li>
<li><a href="<?php echo base_url("account/reminders"); ?>"><i class="fa fa-credit-card-alt"></i> Reminders</a></li>
<?php	}?>
<li class="active"><a href="<?php echo base_url("account/profile"); ?>"><i class="fa fa-pencil"></i> My Profile</a></li>
<li><a href="<?php echo base_url("account/cp"); ?>"><i class="fa fa-lock"></i> Change Password</a></li>

</ul>
            <div class="tab-content">

              <div class="active tab-pane" id="settings">
<?php
if (!$this->session->flashdata('error')) {
	if (trim($user['email_password']) == "" || trim($user['calendar_link']) == "") {
		$this->session->set_flashdata('error', "Please confirm your calendar link and email password. We need the email password so we may send emails to you and your clients on your behalf. Failing to add this information may result in the reminders not working and you losing business or missing important reminders.");
	}
}
?>
<?php	$this->load->view("template/alert.php");?>
<?php echo form_open(current_url(), array('enctype' => 'multipart/form-data')); ?>

<div class="row">
<div class="form-group col-md-4"><label for="e">First Name *</label><?php	echo form_input(['type' => 'text', 'name' => 'name', 'value' => $user['name'], 'class' => 'form-control', 'required' => 'required']); ?></div>

<div class="form-group col-md-4"><label for="e">Last Name *</label><?php	echo form_input(['type' => 'text', 'name' => 'lname', 'value' => $user['lname'], 'class' => 'form-control', 'required' => 'required']); ?></div>
<div class="clr"></div>


<div class="form-group col-md-4"><label for="e">Main Office Phone Number *</label><?php	echo form_input(['type' => 'text', 'name' => 'phone', 'value' => $user['phone'], 'class' => 'form-control', 'required' => 'required']); ?></div>

<div class="form-group col-md-4"><label for="e">Main Office Email *</label><?php	echo form_input(['type' => 'mail', 'name' => 'email', 'value' => trim($user['email']), 'class' => 'form-control', 'required' => 'required']); ?></div>
<div class="clr"></div>
<div class="form-group col-md-4">
  <label for="e">SMTP Email Password *</label>
  <div class="input-group">
    <input type="password" name="email_password" class="form-control input-md" id="email_password" value="<?=(!empty($user['email_password']) ? base64_decode($user['email_password']) : '')?>">
    <span class="input-group-addon"><a href="javascript:;" id="pwdShow"><i class="fa fa-eye-slash"></i></a></span>
  </div>
  <?php	//echo form_input(['type' => 'text', 'name' => 'email_password', 'value' => (!empty($user['email_password']) ? base64_decode($user['email_password']) : ''), 'class' => 'form-control', 'required' => 'required']); ?>
</div>

<div class="form-group col-md-4"><label for="e">Calendar Link *</label><?php	echo form_input(['type' => 'text', 'name' => 'calendar_link', 'value' => $user['calendar_link'], 'class' => 'form-control', 'required' => 'required']); ?></div>


<!--<div class="form-group col-md-4"><label for="e">Profile Image</label>
<input type="file" name="logo_img" class="form-control" accept="image/*" <?php	/*if(!file_exists($user['image'])) { echo " required"; }*/?> />
<a class="btn-link">(Width:100px; Max Height:100px;)</a>
<?php	if (file_exists($user['image'])) {?><br /><img src="<?php echo base_url($user['logo']); ?>" height="50" alt="Logo" /><?php	}?>
</div>-->

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

<script type="text/javascript">

    $('#pwdShow').click(function(){

      var field = $('#email_password');
      var type = field.attr('type');

      if(type == 'password'){
        field.attr('type','text');
        $(this).find('i.fa').addClass('fa-eye-slash').removeClass('fa-eye');
      }else{
        field.attr('type','password');
        $(this).find('i.fa').addClass('fa-eye').removeClass('fa-eye-slash');
      }

    })

</script>
</body>
</html>
