<?php	defined('BASEPATH') OR exit('No direct script access allowed');	?>
<?php
$user = $GLOBALS["loguser"];


?>
<!DOCTYPE html>
<html>
<head>
<?php	$this->load->view("account/inc/head");	?>
</head>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
<?php	$this->load->view("Site/inc/header");	?>
<?php	//$this->load->view("account/inc/leftnav");	?>


<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper" style=" background:#FFFFFF;">
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1><strong><?php echo $data['name']; ?></strong></h1>
</section>
<!-- Main content -->
<section class="content">
  <!-- Info boxes -->

<?php
if($GLOBALS["loguser"]["role"] == "Company" || $GLOBALS["loguser"]["role"] == "Company User")
{
	if($GLOBALS["loguser"]["role"] == "Company")  { $cndvar = "company_id"; } else {	$cndvar = "added_by";	}
?>
<div class="row">

<div class="col-md-5">
<div class="table-responsive" style="background:#FFFFFF;">
<table class="table table-bordered" style="margin-bottom:0px;">
<tr class="info">	<th>Program</th>	<th style="color:green;">Current</th>	<th style="color:red;">Late</th>	<th>Total</th>	</tr>
<?php	$this->load->view("account/Programs/Table");	?>
</table>
</div>
</div>
</div>
<?php	}	?>




  
  <!-- /.row -->
</section>
<!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php	$this->load->view("account/inc/footer");	?>

</div>
<?php	$this->load->view("account/inc/template_js.php");	?>

</body>
</html>
