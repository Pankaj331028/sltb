<?php	defined('BASEPATH') OR exit('No direct script access allowed');	?>
<?php
$user = $GLOBALS["loguser"];
?>
<!DOCTYPE html>
<html>
<head>
<?php	$this->load->view("Admin/inc/head");	?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php	$this->load->view("Admin/inc/header");	?>
<?php	$this->load->view("Admin/inc/leftnav");	?>


<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Dashboard  <small>Control Panel</small></h1>
  <ol class="breadcrumb">
    <li><a href="<?php echo base_url("admin/dashboard"); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
    <li class="active">Dashboard</li>
  </ol>
</section>
<!-- Main content -->
<section class="content">
  <!-- Info boxes -->
  <div class="row">



<div class="col-md-3 col-sm-6 col-xs-12">
  <div class="info-box">
    <span class="info-box-icon bg-blue"><i class="fa fa-user"></i></span>
    <div class="info-box-content">
      <span class="info-box-text">Company</span>
      <span class="info-box-number"><small><?php echo $this->default_model->get_num_rows("users_company", "name!=''"); ?> Record</small></span>
      <a href="<?php echo base_url("admin/company"); ?>">View Records</a>
    </div>
  </div>
</div>


<div class="col-md-3 col-sm-6 col-xs-12">
  <div class="info-box">
    <span class="info-box-icon bg-navy"><i class="fa fa-user"></i></span>
    <div class="info-box-content">
      <span class="info-box-text">Company User</span>
      <span class="info-box-number"><small><?php echo $this->default_model->get_num_rows("users", "role='Company' or role='Company User'"); ?> Record</small></span>
      <a href="<?php echo base_url("admin/case_manager"); ?>">View Records</a>
    </div>
  </div>
</div>

<div class="col-md-3 col-sm-6 col-xs-12">
  <div class="info-box">
    <span class="info-box-icon bg-purple"><i class="fa fa-user"></i></span>
    <div class="info-box-content">
      <span class="info-box-text">Clients</span>
      <span class="info-box-number"><small><?php echo $this->default_model->get_num_rows("users", "role='Customer'"); ?> Record</small></span>
      <a href="<?php echo base_url("admin/clients"); ?>">View Records</a>
    </div>
  </div>
</div>



<div class="clr"></div>

  </div>
  <!-- /.row -->
  
  <!-- /.row -->
</section>
<!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php	$this->load->view("Admin/inc/footer");	?>

</div>
<?php	$this->load->view("Admin/inc/template_js.php");	?>

</body>
</html>
