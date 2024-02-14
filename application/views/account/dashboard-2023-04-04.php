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
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1><strong>Dashboard</strong></h1>
</section>
<!-- Main content -->
<section class="content">
  <!-- Info boxes -->

<?php
if($GLOBALS["loguser"]["role"] == "Company")
{
	$cdata = $this->crm_model->get_company_details($GLOBALS["loguser"]["id"]);
	$client_url = base_url($cdata['slug']."/account");
?>
<div class="row">
<div class="col-md-12">
<div style="background:#f8f8f8; padding:15px;">
	<p><strong>Client Portal URL:</strong></p>
    <p><a href="<?php	echo $client_url;	?>" target="_blank"><?php	echo $client_url;	?></a></p>
</div>
</div>
</div>
<?php	}	?>


<?php
if($GLOBALS["loguser"]["role"] == "Company" || $GLOBALS["loguser"]["role"] == "Company User")
{
	if($GLOBALS["loguser"]["role"] == "Company")  { $cndvar = "company_id"; } else {	$cndvar = "added_by";	}
?>
<div class="row">
<div class="col-md-7">
<?php
$q = $this->db->query("select id from users where $cndvar='".$GLOBALS["loguser"]["id"]."' and role='Customer'");
$nc = $q->num_rows();

?>
<div class="table-responsive" style="background:#FFFFFF;">
<table class="table table-bordered" style="margin-bottom:0px;">
        <thead>
        <tr><th colspan="5" style="background:#81b1c9;">
        	<span style="font-size:18px; color:#FFFFFF;">CLIENT: <?php echo $nc?></span>
            <a href="<?php echo base_url('account/customer/new'); ?>" class="btn btn-primary btn-xs pull-right" style="text-transform:uppercase;"><i class="fa fa-user-plus"></i> Create New Client</a>
        </th></tr>
        <tr class="info">
          <th>Client ID</th>
          <th>Name</th>
          <th>Phone</th>
          <th>Email</th>
          <th>View Status</th>
        </tr>
        </thead>
        <tbody>
<?php
$arr_cpp_cids = array();
$arr_cpp_dates = array();
$q = $this->db->query("select distinct(client_id) as client_id, step_due_date from client_program_progress where $cndvar='".$GLOBALS["loguser"]["id"]."' and status='Pending' and step_due_date<='".date('Y-m-d')."'");
$nc = $q->num_rows();
foreach ($q->result() as $r) {	$arr_cpp_cids[$r->client_id] = $r->client_id;	$arr_cpp_dates[$r->client_id] = $r->step_due_date;	}
if(count($arr_cpp_cids)>0)
{
$cnd = "role='Customer'";
if($GLOBALS["loguser"]["role"] == "Company") {	$cnd .= " and company_id='".$GLOBALS["loguser"]["id"]."'";	}
if($GLOBALS["loguser"]["role"] == "Company User") {	$cnd .= " and parent_id='".$GLOBALS["loguser"]["id"]."'";	}
$query = $this->db->query("SELECT * FROM users where $cnd and id in (".implode(",",$arr_cpp_cids).") order by name asc limit 10");
foreach ($query->result() as $row)
{
	if($row->parent_id == 0) {	$row->parent_id = $row->company_id;	}
	if($arr_cpp_dates[$row->id] < date('Y-m-d')) { $txt_color = 'style="color:#FF0000;"'; } else { $txt_color = 'style="color:#006600;"'; }
?>
<tr>
    <td <?php echo $txt_color; ?>><?php echo $row->id; ?></td>
    <td <?php echo $txt_color; ?>><?php echo $row->lname?>, <?php echo $row->name?></td>
    <td><a href="tel:<?php echo $row->phone?>" <?php echo $txt_color; ?>><?php echo $row->phone?></a></td>
    <td><a href="mailto:<?php echo $row->email?>" <?php echo $txt_color; ?>><?php echo $row->email?></a></td>
    <td><a href="<?php echo base_url('account/customer/add_program/'.$row->id)?>" title="View" class="btn btn-link"><i class="fa fa-eye" aria-hidden="true"></i> View</a></td>
</tr>
<?php	} }	else	{	?>
<tr>	<td colspan="5" style="color:#FF0000; background:#FFFFFF; padding:25px 15px 170px 15px;">No Record Found.</td>	</tr>
<?php	}	?>
        </tbody>
        
      </table>
</div>
</div>
<div class="col-md-5">
<div class="table-responsive" style="background:#FFFFFF;">
<table class="table table-bordered" style="margin-bottom:0px;">
<tr class="info">	<th><a href="<?php echo base_url("account/programs"); ?>">Program</a></th>	<th style="color:green;">Current</th>	<th style="color:red;">Late</th>	<th>Total</th>	</tr>

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
