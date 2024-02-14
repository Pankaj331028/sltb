<?php	defined('BASEPATH') OR exit('No direct script access allowed');	?>

<!DOCTYPE html>
<html>
<head>
<?php	$this->load->view("Site/inc/head");	?>
</head>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
<?php	$this->load->view("Site/inc/header");	?>
<?php	//$this->load->view("account/inc/leftnav");	?>


<div class="content-wrapper" style="background:#FFFFFF;">
    <div class="container" style="background:#FFFFFF;">
      <!-- Content Header (Page header) -->
      <!--<section class="content-header"><h1><?php	if(isset($data['name'])) {	echo $data['name'];	}	?></h1></section>-->
      <!-- Main content -->
      <section class="content"><div style="padding:50px 0px 0px 0px;">
	  <div class="row">
      <div class="col-md-4"></div>
      <div class="col-md-4">
<?php	if(isset($GLOBALS["loguser"]["id"])) {	?>
<div class="list-group" style="margin-top:55px;">
  <a href="<?php echo base_url('account'); ?>" class="list-group-item active"><i class="fa fa-user"></i> <?php echo $GLOBALS["loguser"]["name"]; ?> (<?php echo $GLOBALS["loguser"]["id"]?>)</a>
  <a href="<?php echo base_url('account'); ?>" class="list-group-item"><i class="fa fa-sign-in"></i> My Account</a>
  <a href="<?php echo base_url('account/cp'); ?>" class="list-group-item"><i class="fa fa-lock"></i> Change Password</a>
  <a href="<?php echo base_url('account/logout'); ?>" class="list-group-item"><i class="fa fa-sign-out"></i> Logout</a>
</div>
<?php	}	else	{	?>
<div class="panel panel-primary">
  <div class="panel-heading"><i class="fa fa-lock"></i> <strong>Forgot Password</strong></div>
  <div class="panel-body">
<?php	$this->load->view("account/inc/alert");	?>
    
    <form action="" method="post" enctype="multipart/form-data">
    <input type="hidden" name="role" value="Company" />
      <div class="form-group has-feedback">
        <input type="text" class="form-control" placeholder="User ID" name="email" required>
        <span class="glyphicon glyphicon-user form-control-feedback"></span>
      </div>
      
      <div class="row">
        <!-- /.col -->
        <div class="col-xs-12 text-right">
          <button type="submit" class="btn btn-primary btn-block btn-flat">Submit</button>
        </div>
        <!-- /.col -->
      </div>
    </form>

    <p>&nbsp;</p>
    <p class="text-center"><a href="<?php echo base_url(); ?>">Back to Login</a></p>

  </div>
</div>
<?php	}	?>
      </div>
      </div>
      
      </div></section>
      <!-- /.content -->
    </div>
    <!-- /.container -->
  </div>

<?php	$this->load->view("account/inc/footer");	?>

</div>
<?php	$this->load->view("account/inc/template_js.php");	?>

</body>
</html>
