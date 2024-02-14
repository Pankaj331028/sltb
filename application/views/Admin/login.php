<?php	defined('BASEPATH') OR exit('No direct script access allowed');	?>
<!DOCTYPE html>
<html>
<head>

<?php	$this->load->view("Admin/inc/head");	?>
</head>

<body class="hold-transition login-page" style="background:url(<?php echo base_url('assets/img/bg-home.jpg'); ?>); background-size:100%;">
<div class="login-box">
  
  <div class="login-box-body">
    <div class="login-logo"><a href="<?php echo base_url('admin/login'); ?>"><b>Student Loan Tool Box</b></a></div>
    <p class="login-box-msg">Sign in to start your session</p>
    
    <?php	$this->load->view("Admin/inc/alert");	?>
    <form action="" method="post">
    <input type="hidden" name="role" value="Admin" />
      <div class="form-group has-feedback">
        <input type="email" class="form-control" placeholder="Email" name="email">
        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="password" class="form-control" placeholder="Password" name="password">
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
    <a href="<?php echo base_url('admin/fp'); ?>">I forgot my password</a>

  </div>
  <!-- /.login-box-body -->
</div>

<?php	$this->load->view("Admin/inc/template_js");	?>
</body>
</html>