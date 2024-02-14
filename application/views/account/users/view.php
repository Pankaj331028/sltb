<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php
error_reporting(0);
@extract($_POST);

$sg_3 = $this->uri->segment(3);

if ($this->uri->segment(4) > 0) {
	if ($GLOBALS["loguser"]["role"] == "Company") {$cndvar = "company_id";} else { $cndvar = "company_id";}
	// if ($GLOBALS["loguser"]["role"] == "Company") {$cndvar = "company_id";} else { $cndvar = "parent_id";}
	$user = $this->default_model->get_arrby_tbl_single('users', '*', "role='Customer' and $cndvar='" . $GLOBALS["loguser"]["company_id"] . "' and id='" . $this->uri->segment(4) . "'", '1');
	if (!isset($user['id'])) {redirect(base_url("account/customer"));exit;} else { $client_id = $user['id'];}

//	Get Client Full Details
	$client_data = $this->crm_model->get_client_full_details($client_id);
	@extract($client_data['client']);
	$icsr = $client_data['intake_client_status'];
	$cmr = $client_data['case_manager'];

	$nslds_file_upload_status = $this->crm_model->client_nslds_file_upload_status($client_id);

}

?>
<!DOCTYPE html>
<html>
<head>
<?php	$this->load->view("account/inc/head");?>
</head>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
<?php	$this->load->view("Site/inc/header");?>


<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="row">
        <div class="col-md-6">
          <h1><strong><?php echo $data["name"]; ?></strong></h1>
          <p><strong>Client:</strong> <?php echo $user['name']; ?> <?php echo $user['lname']; ?> (#<?php echo $user['id']; ?>)</p>
        </div>
        <div class="col-md-6 text-right">
          <a href="javascript:;" onclick="deleteCustomer(<?=$client_id?>)" title="Delete" class="btn btn-sm btn-danger" style="margin-top: 20px;">Delete Client</a>
        </div>
      </div>

<?php
$dq = $this->db->query("SELECT * FROM client_documents where client_id='" . $client_id . "' and added_by='" . $client_id . "' and downloaded_date is NULL");
$dn = $dq->num_rows();
if ($dn > 0) {
	?>
      <div class="alert alert-info">
      	<p>You have <?php echo $dn; ?> New Document to Download</p><a href="<?php echo base_url("account/customer/document/" . $this->uri->segment(4)) ?>" class="btn btn-primary">View Documents</a>
      </div>
<?php	}?>
    </section>

    <!-- Main content -->
    <section class="content">

      <div class="row">
        <!-- /.col -->
        <div class="col-md-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
<?php if ($this->uri->segment(4) > 0) {?>
<li><a href="<?php echo base_url("account/customer/edit/" . $this->uri->segment(4)) ?>"><i class="fa fa-pencil"></i> Edit</a></li>
<li class="active"><a href="<?php echo base_url("account/customer/view/" . $this->uri->segment(4)) ?>"><i class="fa fa-eye"></i> View Client</a></li>
<li><a href="<?php echo base_url("account/customer/add_program/" . $this->uri->segment(4)) ?>"><i class="fa fa-file-text-o"></i> Programs</a></li>
<!--<li><a href="<?php echo base_url("account/customer/status/" . $this->uri->segment(4)) ?>"><i class="fa fa-file-text-o"></i> View Client Status</a></li>-->
<li><a href="<?php echo base_url("account/customer/document/" . $this->uri->segment(4)) ?>"><i class="fa fa-upload"></i> Documents</a></li>
<!--<li><a href="<?php echo base_url("account/customer/report/" . $this->uri->segment(4)) ?>"><i class="fa fa-line-chart"></i> View Reminder Reports</a></li>-->
<?php	} else {?>
              <li class="active"><a href=""><i class="fa fa-user-plus"></i> <?php echo $data["name"]; ?></a></li>
              <li><a href="<?php echo base_url("account/customer"); ?>"><i class="fa fa-users"></i> Clients</a></li>
<?php	}?>
            </ul>
            <div class="tab-content">

              <div class="active tab-pane" id="settings">
<?php	$this->load->view("template/alert.php");?>

<table class="table table-bordered">
<tr>	<th width="140">Client ID</th>	<td><?php echo $id; ?></td>	</tr>
<tr>	<th>Name</th>	<td><?php echo $lname; ?>, <?php echo $name; ?></td>	</tr>
<tr>	<th>Phone Number</th>	<td><a href="tel:<?php echo $phone; ?>"><?php echo $phone; ?></a></td>	</tr>
<tr>	<th>Email</th>	<td><a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a></td>	</tr>
<tr>	<th>Reg Date</th>	<td><?php echo date('m/d/Y', strtotime($add_date)); ?></td>	</tr>
<tr>	<th class="info" colspan="2">Case Manager</th>	</tr>
<tr>	<th>Name</th>	<td><?php echo $cmr['name']; ?></td>	</tr>
<tr>	<th>Mobile Number</th>	<td><a href="tel:<?php echo $cmr['phone']; ?>"><?php echo $cmr['phone']; ?></a></td>	</tr>
<tr>	<th>Email</th>	<td><a href="mailto:<?php echo $cmr['email']; ?>"><?php echo $cmr['email']; ?></a></td>	</tr>

</table>

<?php
$initial_intake_status = "Pending";
if (isset($icsr['status'])) {if ($icsr['status'] == "Complete") {$initial_intake_status = "Complete";}}
?>
<div>

<?php	if ($nslds_file_upload_status == "Uploaded") {?>
<a href="javascript:void(0)" class="btn btn-info" data-toggle="modal" data-target="#myModal_nslds" onClick="view_nslds_snapshot_body('<?php	echo base_url("account/view_nslds_snapshot/" . $client_id) ?>', 'nslds_snapshot_body')">View NSLDS Snapshot</a>
<a href="javascript:void(0)" class="btn btn-info" data-toggle="modal" data-target="#myModal_nslds_file">View NSLDS File</a> &nbsp;
<?php	} else {?><p style="color:#FF3333; font-weight:bold;">NSLDS File not found</p><?php }?>

<?php
// if (isset($icsr['status'])) {
// 	if ($icsr['status'] == "Complete" || $icsr['status2'] == "Stop") {
?>
<a href="javascript:void(0)" class="btn btn-info" onClick='$("#nslds_file_form").show("500");'>Upload NSLDS</a>
<?php
// }}
?>

<?php
//if($initial_intake_status == "Complete" && $nslds_file_upload_status == "Uploaded") {
if ($initial_intake_status == "Complete") {
	?>
<a href="<?=base_url("account/customer/current_analysis/" . $this->uri->segment(4))?>" class="btn btn-primary pull-right"><i class="fa fa-line-chart"></i> View Current Analysis</a>
<?php	}?>
</div>

<?php	if ($nslds_file_upload_status == "Uploaded") {
	?>
<div id="myModal_nslds" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">NSLDS Snapshot</h4>
      </div>
      <div class="modal-body" id="nslds_snapshot_body"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>
<div id="deleteCustomer" class="modal fade" role="dialog">
  <div class="modal-dialog ">
    <div class="modal-content">
      <form id="closeDelete">
        <div class="modal-body text-center">
          <h4>Are you sure you want to permanently delete this client form the system? You will not be able to recover this client once deleted.</h4>
        </div>
        <input type="hidden" name="customer_id" value="">
        <div class="modal-footer">
          <a id="deleteYes" href="javascript:;" class="btn btn-primary">Yes</a>
          <button type="submit" class="btn btn-danger" data-dismiss="modal">No</button>
        </div>
      </form>
    </div>
    <!-- base_url($sg_1 . "/customer_intake_form/" . $client_id . "/" . $tif['id']) -->
  </div>
</div>

<div id="myModal_nslds_file" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">NSLDS File</h4>
      </div>
      <div class="modal-body">
	  <?php
$file_data = $this->crm_model->client_nslds_file_data($client_id);
	$arr_file_data = preg_split("/\r\n|\n|\r/", $file_data);
	foreach ($arr_file_data as $v) {echo $v . "<br />";}
	?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>
<?php	}?>


<div id="nslds_file_form" style="margin-top:15px; max-width:500px; background:#F8F8F8; display:none;">
<form action="" method="post" enctype="multipart/form-data">
<input type="hidden" name="client_id" value="<?php echo $this->uri->segment(4); ?>" />
<div class="box box-primary">
<div class="box-header" style="background:#3c8dbc; color:#FFFFFF;">
	<strong>Please upload Federal loan data from Studentaid.gov</strong>
    <a href="<?php echo base_url("nslds-upload-instructions"); ?>" target="_blank" class="btn btn-default btn-xs pull-right" style="color:#0033CC;"><i class="fa fa-info-circle"></i> Upload Help</a>
</div>

<div class="box-body" style="background:#F8F8F8;">

<div class="form-group">
<input type="file" class="form-control" name="intake_file_result" accept="text/plain" required />
</div>

<div>
<input type="submit" class="btn btn-primary" name="Submit_nslds" value="Save">
<a href="javascript:void(0)" class="btn btn-danger pull-right" onClick='$("#nslds_file_form").hide("500");'>Close</a>
</div>

</div>

</div>
</form>
</div>


<?php	if ($initial_intake_status == "Pending") {?>
<div class="alert" style="background-color: #f2dede; border-color: #ebccd1; margin-top:10px;">
<p style="color: #a94442; font-weight:bold;">Intake is not complete yet</p>
<form action="" method="post" enctype="multipart/form-data">
<p><button type="submit" name="submit_send_intake" class="btn btn-primary">Send intake</button></p>
</form>
</div>
<?php	}?>


              </div>
              <!-- /.tab-pane -->
            </div>
            <!-- /.tab-content -->
          </div>
          <!-- /.nav-tabs-custom -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->

    </section>
    <!-- /.content -->
  </div>

<?php	$this->load->view("account/inc/footer");?>

</div>
<?php	$this->load->view("account/inc/template_js.php");?>
<script type="text/javascript">

  function deleteCustomer(id){

    $('#deleteYes').attr('href','<?php echo base_url('account/customer/delete/' . $client_id) ?>');
    $('#closeDelete').focus();
    $('#closeDelete button[type=submit]').focus();
    $('#deleteCustomer').modal('show');
  }
</script>
</body>
</html>
