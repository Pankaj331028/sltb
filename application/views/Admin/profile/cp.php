<?php	defined('BASEPATH') OR exit('No direct script access allowed');	?>
<!DOCTYPE html>
<html>
<head>
<?php	$this->load->view("Admin/inc/head");	?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php	$this->load->view("Admin/inc/header");	?>
<?php	$this->load->view("Admin/inc/leftnav");	?>

<?php
$user = $GLOBALS["loguser"];
if($user['image']!='' && $user['image']!=' ') {	$prf_img = $user['image'];	} else {	$prf_img = 'assets/crm/dist/img/user4-128x128.jpg';	}
?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Profile</h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo base_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?php echo base_url('admin/profile'); ?>">Profile</a></li>
        <li class="active">Update Profile</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">

      <div class="row">
        <div class="col-md-3">

          <!-- Profile Image -->
          <div class="box box-primary">
            <div class="box-body box-profile">
              <img class="profile-user-img img-responsive img-circle" src="<?php echo base_url($prf_img)?>" alt="<?php echo $user['name']; ?>">

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
              <li><a href="<?php echo base_url("admin/profile"); ?>"><i class="fa fa-user"></i> Profile</a></li>
              <li class="active"><a href="<?php echo base_url("admin/cp"); ?>"><i class="fa fa-lock"></i> Change Password</a></li>
            </ul>
            <div class="tab-content">
              
              <div class="active tab-pane" id="settings">

<?php	$this->load->view("template/alert.php");	?>

<?php echo form_open(base_url("admin/cp"),array('class'=>'form-horizontal')); ?>

<div class="form-group">
    <label for="inputName" class="col-sm-3 control-label">Current Password</label>
    <div class="col-sm-9"><?php	echo form_input(['type'=>'password','name'=>'cpassword','value'=>'','class'=>'form-control','placeholder'=>'Current Password','required'=>'required']);	?></div>
</div>

<div class="form-group">
    <label for="inputName" class="col-sm-3 control-label">New Password</label>
    <div class="col-sm-9"><?php	echo form_input(['type'=>'password','name'=>'password','value'=>'','class'=>'form-control','placeholder'=>'New Password','required'=>'required']);	?></div>
</div>

<div class="form-group">
    <label for="inputName" class="col-sm-3 control-label">Retype Password</label>
    <div class="col-sm-9"><?php	echo form_input(['type'=>'password','name'=>'rpassword','value'=>'','class'=>'form-control','placeholder'=>'Retype Password','required'=>'required']);	?></div>
</div>

                  
                  <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-9">
                      <button type="submit" class="btn btn-success">Submit</button>
                    </div>
                  </div>
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

<?php	$this->load->view("Admin/inc/footer");	?>

</div>
<?php	$this->load->view("Admin/inc/template_js.php");	?>
</body>
</html>
