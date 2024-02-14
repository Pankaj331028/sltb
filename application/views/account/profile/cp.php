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
      <h1><strong>Change Password</strong></h1>
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
<li><a href="<?php echo base_url("account/profile"); ?>"><i class="fa fa-pencil"></i> My Profile</a></li>
<li class="active"><a href="<?php echo base_url("account/cp"); ?>"><i class="fa fa-lock"></i> Change Password</a></li>

</ul>
            <div class="tab-content">

              <div class="active tab-pane" id="settings">
<p>&nbsp;</p>
<?php	$this->load->view("template/alert.php");?>

<?php echo form_open(current_url(), array('enctype' => 'multipart/form-data')); ?>

<div class="row">
<div class="col-md-4">

<div class="form-group"><label for="e">Current Password *</label><?php	echo form_input(['type' => 'password', 'name' => 'cpassword', 'class' => 'form-control', 'required' => 'required']); ?></div>

<div class="form-group"><label for="e">New Password *</label><?php	echo form_input(['type' => 'password', 'name' => 'password', 'class' => 'form-control', 'required' => 'required']); ?></div>

<div class="form-group"><label for="e">Retype Password *</label><?php	echo form_input(['type' => 'password', 'name' => 'rpassword', 'class' => 'form-control', 'required' => 'required']); ?></div>

<div><button type="submit" class="btn btn-primary btn-block">Submit</button></div>

</div>

</div>
<p>&nbsp;</p>
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
