<?php	defined('BASEPATH') OR exit('No direct script access allowed');	?>
<!DOCTYPE html>
<html>
<head>

<?php	$this->load->view("account/inc/head");	?>
</head>

<body class="hold-transition login-page" style="background:url(<?php echo base_url('assets/img/bg-home.jpg'); ?>); background-size:100%;">
<div class="login-box">
  
  <div class="login-box-body">
    <div class="login-logo"><a href="<?php echo base_url(''); ?>"><b>Student Loan Tool Box</b></a></div>
    <p class="login-box-msg">Sign in to start your session</p>
    
    <?php	$this->load->view("account/inc/alert");	?>
    
    <form action="" method="post" enctype="multipart/form-data">
    <input type="hidden" name="role" value="Customer" />
      <div class="form-group has-feedback">
        <input type="text" class="form-control" placeholder="User ID" name="email" onFocus="this.removeAttribute('readonly');" />
        <span class="glyphicon glyphicon-user form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="password" class="form-control" placeholder="Password" name="password" onFocus="this.removeAttribute('readonly');" />
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
  <!-- /.login-box-body -->
</div>

<?php	$this->load->view("account/inc/template_js");	?>
</body>
</html>