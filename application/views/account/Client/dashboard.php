<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php
$user = $GLOBALS["loguser"];

?>
<!DOCTYPE html>
<html>
<head>
<?php	$this->load->view("account/inc/head");?>
</head>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
<?php
$hc_data['client_data'] = $client_data;
$this->load->view("Site/inc/header_client", $hc_data['client_data']);
?>


<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
<!-- Content Header (Page header) -->

<!-- Main content -->
<section class="content">

<div class="nav-tabs-custom">
<ul class="nav nav-tabs">
<li class="active"><a href="<?php echo base_url("customer/dashboard"); ?>"><i class="fa fa-pencil"></i> Your Information</a></li>
<?php	$this->load->view("account/Client/Inc/intake_link", ["client_data" => $client_data]);?>
<li><a href="<?php echo base_url("customer/document"); ?>"><i class="fa fa-upload"></i> Documents</a></li>
</ul>
<div class="tab-content">
<div class="active tab-pane" id="settings">

<?php	$this->load->view("template/alert.php");?>
<?php	@extract($client_data['client']);?>


<div class="table-responsive">
<table class="table table-bordered">
<tr>	<th width="140">Client ID</th>	<td><?php echo $id; ?></td>	</tr>
<tr>	<th>Name</th>	<td><?php echo $lname; ?>, <?php echo $name; ?></td>	</tr>
<tr>	<th>Phone Number</th>	<td><?php echo $phone; ?></td>	</tr>
<tr>	<th>Email</th>	<td><?php echo $email; ?></td>	</tr>
<tr>	<th>Reg Date</th>	<td><?php echo date('m/d/Y', strtotime($add_date)); ?></td>	</tr>
<!--<tr>	<th class="info" colspan="2">Case Manager</th>	</tr>
<?php
$cm_name = $client_data['case_manager']['name'];
$cm_phone = $client_data['case_manager']['phone'];
$cm_email = $client_data['case_manager']['email'];
?>
<tr>	<th>Name</th>	<td><?php echo $cm_name; ?></td>	</tr>
<tr>	<th>Mobile Number</th>	<td><a href="tel:<?php echo $cm_phone; ?>"><?php echo $cm_phone; ?></a></td>	</tr>
<tr>	<th>Email</th>	<td><a href="mailto:<?php echo $cm_email; ?>"><?php echo $cm_email; ?></a></td>	</tr>-->

</table>
</div>
<div class="clr"></div>
<?php

//$this->crm_model->read_and_save_intake_file();

?>
<div class="clr"></div>


</div>
</div>
</div>

  <!-- /.row -->
</section>
<!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php	$this->load->view("account/inc/footer");?>

</div>
<?php	$this->load->view("account/inc/template_js.php");?>

</body>
</html>
