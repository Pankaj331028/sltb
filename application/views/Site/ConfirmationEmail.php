<?php	defined('BASEPATH') OR exit('No direct script access allowed');	?>

<!DOCTYPE html>
<html>
<head>
<?php	$this->load->view("Site/inc/head");	?>
</head>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
<?php	//$this->load->view("Site/inc/header");	?>


<div class="content-wrapper" style="background:#FFFFFF;">
    <div class="container" style="background:#FFFFFF;">
     
      <section class="content"><div style="padding:50px 0px 0px 0px;;">
	  <div class="row">
      <div class="col-md-4"></div>
      <div class="col-md-4">

<?php	//$this->load->view("account/inc/alert");	?>

<?php	if($error_type == "Success") {	?>
<div class="alert alert-success text-center">
    <p><i class="fa fa-check-circle" aria-hidden="true" style="font-size:80px;"></i></p>
    <p><?php	echo $msg;	?></p>
</div>
<?php	} else {	?>
<div class="alert alert-danger text-center">
	<p><i class="fa fa-exclamation-triangle" aria-hidden="true" style="font-size:50px;"></i></p>
	<p><?php	echo $msg;	?></p>
</div>
<?php	}	?>


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
