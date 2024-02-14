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
<?php	$this->load->view("Site/inc/header");?>

<?php	//$this->load->view("account/inc/leftnav");	?>


<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1><strong>Dashboard</strong></h1>
</section>
<!-- Main content -->
<section class="content">
  <!-- Info boxes -->


<?php
if ($GLOBALS["loguser"]["role"] == "Company") {
	$cdata = $this->crm_model->get_company_details($GLOBALS["loguser"]["id"]);
	$client_url = base_url($cdata['slug'] . "/account");
	?>
<div class="active tab-pane" id="settings">
								<?php	$this->load->view("template/alert.php");?>
  <div class="row">
    <div class="col-md-12">
      <div style="background:#f8f8f8; padding:15px;">
        <p><strong>Client Portal URL:</strong></p>
          <p><a href="<?php	echo $client_url; ?>" target="_blank"><?php	echo $client_url; ?></a> &nbsp;&nbsp;&nbsp;
          <button title="Copy Link" class="btn btn-sm btn-primary" onclick="copyClipboard('<?php echo $client_url; ?>')">Copy Link</button></p>
      </div>
    </div>
  </div>
  </div>
<?php	}?>


<?php
if ($GLOBALS["loguser"]["role"] == "Company" || $GLOBALS["loguser"]["role"] == "Company User") {
	if ($GLOBALS["loguser"]["role"] == "Company") {$cndvar = "company_id";} else { $cndvar = "company_id";}
	// if($GLOBALS["loguser"]["role"] == "Company")  { $cndvar = "company_id"; } else {  $cndvar = "parent_id";  }
	?>
<div class="row">
<div class="col-md-7">
<?php
$q = $this->db->query("select * from users where $cndvar='" . $GLOBALS["loguser"]["company_id"] . "' and role='Customer' order by id desc limit 10");
	$nc = $q->num_rows();

	?>
<div class="table-responsive" style="background:#FFFFFF;">
<table class="table table-bordered" style="margin-bottom:0px;">
        <thead>
        <tr><th colspan="5" style="background:#81b1c9;">
        	<span style="font-size:18px; color:#FFFFFF;">CLIENT</span>
            <a href="<?php echo base_url('account/customer/new'); ?>" class="btn btn-primary btn-xs pull-right" style="text-transform:uppercase;"><i class="fa fa-user-plus"></i> Create New Client</a>
        </th></tr>
        <tr class="info">
          <th>Client ID</th>
          <th>Name</th>
          <th>Phone</th>
          <th>Email</th>
          <th>View</th>
        </tr>
        </thead>
        <tbody>
<?php
if ($nc > 0) {
		foreach ($q->result() as $row) {
			$view_url = base_url($this->uri->segment(1) . '/customer/view/' . $row->id);
			?>
<tr>
    <td><a href="<?php echo $view_url; ?>" class="text-black"><?php echo $row->id; ?></a></td>
    <td><a href="<?php echo $view_url; ?>" class="text-black"><?php echo $row->lname ?>, <?php echo $row->name ?></a></td>
    <td><a href="tel:<?php echo $row->phone ?>" class="text-black"><?php echo $row->phone ?></a></td>
    <td><a href="mailto:<?php echo $row->email ?>" class="text-black"><?php echo $row->email ?></a></td>
    <td><a href="<?php echo $view_url; ?>" title="View" class="btn btn-link"><i class="fa fa-eye" aria-hidden="true"></i> View Details</a></td>
</tr>
<?php	}} else {?>
<tr>	<td colspan="5" style="color:#FF0000; background:#FFFFFF; padding:25px 15px 170px 15px;">No Record Found.</td>	</tr>
<?php	}?>
        </tbody>

      </table>
</div>
</div>
<div class="col-md-5">
<div class="table-responsive" style="background:#FFFFFF;">
<table class="table table-bordered" style="margin-bottom:0px;">
<tr class="info">	<th><a href="<?php echo base_url("account/programs"); ?>">Program</a></th>	<th style="color:green;">Current</th>	<th style="color:red;">Late</th>	<th>Total</th>	</tr>

<?php	$this->load->view("account/Programs/Table");?>
</table>
</div>
</div>
</div>
<?php	}?>





  <!-- /.row -->
</section>
<!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php	$this->load->view("account/inc/footer");?>

</div>
<?php	$this->load->view("account/inc/template_js.php");?>
<script type="text/javascript">

function copyClipboard(text) {
  navigator.clipboard.writeText(text);

  // Alert the copied text
  alert("Copied Link: " + text);
}
</script>
</body>
</html>
