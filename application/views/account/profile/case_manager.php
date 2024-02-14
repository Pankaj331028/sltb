<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<!DOCTYPE html>
<html>
<head>
<?php	$this->load->view("account/inc/head");?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php	$this->load->view("account/inc/header");?>
<?php	$this->load->view("account/inc/leftnav");?>

<?php
$user = $GLOBALS["loguser"];
if ($user['image'] != '' && $user['image'] != ' ') {$prf_img = $user['image'];} else { $prf_img = 'assets/crm/dist/img/user4-128x128.jpg';}
?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Case Manager</h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo base_url('account/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?php echo base_url('account/profile'); ?>">My Account</a></li>
        <li class="active">Case Manager</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">

      <div class="row">
        <div class="col-md-3">

          <!-- Profile Image -->
          <div class="box box-primary">
            <div class="box-body box-profile">
              <img class="profile-user-img img-responsive img-circle" src="<?php echo base_url($prf_img) ?>" alt="<?php echo $user['name']; ?>">

              <h3 class="profile-username text-center"><?php echo $user['name']; ?></h3>

              <ul class="list-group list-group-unbordered">
                <li class="list-group-item"><b>Contact No</b> <a class="pull-right"><?php echo $user['phone']; ?></a></li>
                <li class="list-group-item"><b>Email</b> <a class="pull-right"><?php echo $user['email']; ?></a></li>
              </ul>

            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->

          <!-- About Me Box -->

          <!-- /.box -->
        </div>
        <!-- /.col -->
        <div class="col-md-9">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li><a href="<?php echo base_url("account/profile"); ?>"><i class="fa fa-user"></i> Profile</a></li>
<?php	if ($GLOBALS["loguser"]["role"] == "Company") {?>
			 <li class="active"><a href="<?php echo base_url("account/case_manager"); ?>"><i class="fa fa-user-plus"></i> Case Manager</a></li>
             <li><a href="<?php echo base_url("account/emails"); ?>"><i class="fa fa-envelope-o"></i> SMTP Emails</a></li>
             <li><a href="<?php echo base_url("account/billing"); ?>"><i class="fa fa-file-text-o"></i> Billing</a></li>
<li><a href="<?php echo base_url("integration"); ?>"><i class="fa fa-share"></i> Integrations</a></li>
<?php	}?>
              <li><a href="<?php echo base_url("account/cp"); ?>"><i class="fa fa-lock"></i> Change Password</a></li>
            </ul>
            <div class="tab-content">

              <div class="active tab-pane" id="settings">
<?php	$this->load->view("template/alert.php");?>
<?php echo form_open(base_url("account/case_manager"), array('enctype' => 'multipart/form-data')); ?>

<div class="form-group col-md-4">
    <label for="inputName" class="control-label">Case Manager Name</label>
    <?php	echo form_input(['type' => 'text', 'name' => 'case_manager_name', 'value' => trim($user['case_manager_name']), 'class' => 'form-control', 'required' => 'required']); ?>
</div>
<div class="clr"></div>

<div class="form-group col-md-4">
    <label for="inputName" class="control-label">Case Manager Phone</label>
    <?php	echo form_input(['type' => 'text', 'name' => 'case_manager_phone', 'value' => trim($user['case_manager_phone']), 'class' => 'form-control', 'required' => 'required']); ?>
</div>
<div class="clr"></div>

<div class="form-group col-md-4">
    <label for="inputName" class="control-label">Case Manager Email</label>
    <?php	echo form_input(['type' => 'mail', 'name' => 'case_manager_email', 'value' => trim($user['case_manager_email']), 'class' => 'form-control', 'required' => 'required']); ?>
</div>
<div class="clr"></div>


<div class="form-group col-md-4"><button type="submit" name="Submit_" class="btn btn-success">Submit</button></div>
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
