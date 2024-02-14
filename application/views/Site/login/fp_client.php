<?php	defined('BASEPATH') OR exit('No direct script access allowed');	?>
<?php
$sg_1 = $this->uri->segment(1);
$sg_2 = $this->uri->segment(2);
$sg_3 = $this->uri->segment(3);
?>
<!DOCTYPE html>
<html>
<head>
<?php	$this->load->view("Site/inc/head");	?>
</head>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
<?php	if(isset($company_data['name'])) {	$this->load->view("Site/inc/header_client_login");	}	?>


<div class="content-wrapper" style="background:url(<?php	echo base_url("assets/img/bg-home.jpg");	?>); background-size:cover;">
    <div class="container">
      <!-- Content Header (Page header) -->
      <!--<section class="content-header"><h1><?php	if(isset($data['name'])) {	echo $data['name'];	}	?></h1></section>-->
      <!-- Main content -->
      <section class="content"><div style="padding:50px 0px 0px 0px;;">
	  <div class="row">
      <div class="col-md-4"></div>
      <div class="col-md-4">

<div class="clr"></div>
<div class="panel panel-primary">
  <div class="panel-heading"><i class="fa fa-lock"></i> <strong>Forgot Password</strong></div>
  <div class="panel-body">
<?php	$this->load->view("account/inc/alert");	?>
<?php
$error = "";
if(!isset($company_data['name']))
{
	$error = '<p>We apologize, but you need to get a new link from your Student Loan Professional. If you do not have a Student Loan Professional to assist you with your student loans, you can find one at:</p><p><a href="'.base_url().'">'.base_url().'</a></p>';
}
else if(!isset($company_smtp_data['id']))
{
	//$error = '<p>We apologize, Your comapny\'s email configuration is pending. Contact your comapny admin.</p><p><a href="'.base_url().'">'.base_url().'</a></p>';
}
else{		}



if($error == "")
{
?>

    <form action="" method="post" enctype="multipart/form-data">
    <input type="hidden" name="role" value="Customer" />
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
    <?php	if($sg_1 == "account") {	?>
    <p class="text-center"><a href="<?php echo base_url('account/login'); ?>">Back to Login</a></p>
    <?php	}	else	{	?>
    <p class="text-center"><a href="<?php echo base_url($sg_1.'/client_login'); ?>">Back to Login</a></p>
    <?php	}	?>

<?php	}	else	{	echo $error;	}	?>
  </div>
</div>

      </div>
      </div>
      
      </div></section>
      <!-- /.content -->
    </div>
    <!-- /.container -->
  </div>

<?php	//$this->load->view("account/inc/footer");	?>

</div>
<?php	$this->load->view("account/inc/template_js.php");	?>

</body>
</html>
