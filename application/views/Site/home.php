<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>

<!DOCTYPE html>
<html>
<head>
<?php	$this->load->view("Site/inc/head");?>
</head>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
<?php	$this->load->view("Site/inc/header");?>
<?php	//$this->load->view("account/inc/leftnav");	?>


<div class="content-wrapper" style="background:#FFFFFF;">
    <div class="container" style="background:#FFFFFF;">
      <!-- Content Header (Page header) -->
      <!--<section class="content-header"><h1><?php	if (isset($data['name'])) {echo $data['name'];}?></h1></section>-->
      <!-- Main content -->
      <section class="content"><div style="padding:25px 0px;">
        <?php
$this->load->view("template/alert.php");?>
	  <div class="row">
      <div class="col-md-8">
	  <?php	if (isset($data['details'])) {echo $data['details'];}?>
      </div>
      <div class="col-md-4">
<?php	if (isset($GLOBALS["loguser"]["id"])) {?>
<div class="list-group" style="margin-top:55px;">
  <a href="<?php echo base_url('account/company'); ?>" class="list-group-item active"><i class="fa fa-user"></i> <?php	echo $GLOBALS["loguser"]["name"]; ?> (<?php echo $GLOBALS["loguser"]["id"]; ?>)</a>
  <a href="<?php echo base_url('account/company'); ?>" class="list-group-item"><i class="fa fa-sign-in"></i> My Account</a>
  <?php if ($GLOBALS["loguser"]["role"] != 'Customer') {?><a href="<?php echo base_url('account/cp'); ?>" class="list-group-item"><i class="fa fa-lock"></i> Change Password</a><?php	}?>
  <a href="<?php echo base_url('account/logout'); ?>" class="list-group-item"><i class="fa fa-sign-out"></i> Logout</a>
</div>
<?php	} else {?>
<div class="panel panel-primary" style="margin-top:55px;">
  <div class="panel-heading"><i class="fa fa-user"></i> <strong>Login</strong></div>
  <div class="panel-body">


    <form action="<?php echo base_url('account/login'); ?>" method="post" enctype="multipart/form-data" autocomplete="off">
    <input type="hidden" name="role" value="Customer" />
      <div class="form-group has-feedback">
        <input type="text" class="form-control" placeholder="User ID" name="email" autocomplete="false" onFocus="this.removeAttribute('readonly');" required>
        <span class="glyphicon glyphicon-user form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="password" class="form-control" placeholder="Password" name="password" autocomplete="false" onFocus="this.removeAttribute('readonly');" required>
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
      </div>
      <div class="row">
        <!-- /.col -->
        <div class="col-xs-12 text-right">
          <button type="submit" class="btn btn-primary btn-block btn-flat">Sign In</button>
        </div>
        <!-- /.col -->
      </div>
    </form>

    <p>&nbsp;</p>
    <p class="text-center"><a href="<?php echo base_url('account/fp'); ?>">I forgot my password</a></p>
    <p class="text-center"><a href="<?php echo base_url('account/register'); ?>">Are you new? Join Now</a></p>

  </div>
</div>
<?php	}?>
      </div>
      </div>

      </div></section>
      <!-- /.content -->
    </div>
    <!-- /.container -->
  </div>

<?php	$this->load->view("account/inc/footer");?>

</div>
<?php	$this->load->view("account/inc/template_js.php");?>

</body>
</html>
