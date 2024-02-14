<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php
error_reporting(0);
$sg_1 = $this->uri->segment(1);

?>
<!DOCTYPE html>
<html>
<head>
<?php	$this->load->view("account/inc/head");?>
</head>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
<?php	$this->load->view("Site/inc/header");?>


<div class="content-wrapper"style="background:#FFFFFF;">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><strong><?php echo $data["name"]; ?></strong>
      	<a href="<?php echo base_url("account/customer/new"); ?>" class="btn btn-primary pull-right"><i class="fa fa-user-plus"></i> Create New Client</a>
      </h1>
    </section>

    <!-- Main content -->
    <section class="content">


      <div class="row">
        <div class="col-md-12">
          <?php	$this->load->view("template/alert.php");?>

<?php
//$this->cron_model->check_users_current_program_date();
//$this->cron_model->set_client_current_program();
?>

<div class="table-responsive">
<table id="show_datatable" class="table table-bordered">
        <thead>
        <tr class="info">
          <th width="105">Current Program</th>
          <th width="100">Action</th>
          <th>Client Details</th>
          <?php	if ($GLOBALS["loguser"]["role"] == "Company") {?><th>Case Manager Details</th><?php	}?>
          <th>NSLDS</th>
          <th>Analysis</th>
          <th>Form</th>
          <th width="60">Reg Date</th>
          <th width="0">Clientname</th>
        </tr>
        </thead>
        <tbody>
<?php
$sno = 0;
$cnd = "role='Customer'";
if ($GLOBALS["loguser"]["role"] == "Company") {
	$cnd .= " and company_id='" . $GLOBALS["loguser"]["id"] . "'";

	$q = $this->db->query("SELECT * FROM users where (role='Company User' or role='Company') and (parent_id='" . $GLOBALS["loguser"]["id"] . "' or company_id='" . $GLOBALS["loguser"]["id"] . "') order by id desc");
	foreach ($q->result() as $r) {
		$arr_cm_name[$r->id] = $r->name;
		$arr_cm_phone[$r->id] = $r->phone;
		$arr_cm_email[$r->id] = $r->email;
	}
}
if ($GLOBALS["loguser"]["role"] == "Company User") {$cnd .= " and company_id='" . $GLOBALS["loguser"]["company_id"] . "'";}
// if ($GLOBALS["loguser"]["role"] == "Company User") {$cnd .= " and parent_id='" . $GLOBALS["loguser"]["id"] . "'";}
$query = $this->db->query("SELECT * FROM users where $cnd");
$rows = $query->result();
$tmp_cl_ids = array();
foreach ($rows as $row) {
	$tmp_cl_ids[] = $row->id;
	$client_list[$row->id] = $row;}

$client_list_ids = array();
array_unique($tmp_cl_ids);

if (count($tmp_cl_ids) > 0) {
	//	Get Clilent List
	$query = $this->db->query("SELECT * FROM clients where client_id in (" . implode(",", $tmp_cl_ids) . ") order by current_program_date desc");
	foreach ($query->result() as $row) {$client_list_ids[] = $row;}

	//	Intale Client Status
	$q = $this->db->query("SELECT * FROM intake_client_status where client_id in (" . implode(",", $tmp_cl_ids) . ")  order by id desc");
	foreach ($q->result_array() as $res) {
		$client_id = $res['client_id'];
		$intake_id = $res['intake_id'];
		$arr_ics[$client_id][$intake_id] = $res;
	}

	//	Client Attestation
	$q = $this->db->query("SELECT * FROM client_attestation where client_id in (" . implode(",", $tmp_cl_ids) . ")  order by id desc");
	foreach ($q->result_array() as $res) {
		$client_id = $res['client_id'];
		$arr_catstn[$client_id] = $res;
	}
}

$query = $this->db->query("SELECT distinct(program_title) as program_title, program_definition_id FROM program_definitions where 1");
foreach ($query->result() as $row) {$arr_cpp[$row->program_definition_id] = $row->program_title;}

foreach ($client_list_ids as $clir) {
	$client_id = $clir->client_id;

	$row = $client_list[$client_id];
	$client_id = $row->id;
	if ($row->parent_id == 0) {$row->parent_id = $row->company_id;}
	$initial_intake_status = "Pending";
	$update_intake_status = "Pending";
	if (isset($arr_ics[$client_id]['1'])) {if ($arr_ics[$client_id]['1']['status'] == "Complete") {$initial_intake_status = "Complete";}}
	if (isset($arr_ics[$client_id]['4'])) {if ($arr_ics[$client_id]['4']['status'] == "Complete") {$update_intake_status = "Complete";}}

	$nslds_file_upload_status = $this->crm_model->client_nslds_file_upload_status($client_id);

	?>
<tr>
    <td><a href="<?php echo base_url($sg_1 . "/customer/add_program/" . $row->id); ?>"><?php echo $arr_cpp[$clir->current_program]; ?></a></td>
    <td>
    <a href="<?php echo base_url('account/customer/view/' . $row->id) ?>" title="View" class="btn btn-sm btn-primary"><i class="fa fa-eye" aria-hidden="true"></i></a> &nbsp;
    <a href="<?php echo base_url('account/customer/edit/' . $row->id) ?>" title="Edit" class="btn btn-sm btn-info"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a> &nbsp;
    <a href="javascript:;" onclick="deleteCustomer(<?=$row->id?>)" title="Delete" class="btn btn-sm btn-danger"><i class="fa fa-trash" aria-hidden="true"></i></a>
    </td>
    <td>
		<span><i class="fa fa-user"></i> &nbsp; <?php echo $row->lname ?>, <?php echo $row->name ?></span><br />
        <span><i class="fa fa-phone"></i> &nbsp; <a href="tel:<?php echo $row->phone; ?>"><?php echo $row->phone; ?></a></span><br />
        <span><i class="fa fa-envelope-o"></i> &nbsp; <a href="mailto:<?php echo strtolower($row->email); ?>"><?php echo strtolower($row->email); ?></a></span>
    </td>

    <?php	if ($GLOBALS["loguser"]["role"] == "Company") {?>
    <td>
		<span><i class="fa fa-user"></i> &nbsp; <?php echo $arr_cm_name[$row->parent_id]; ?></span><br />
        <span><i class="fa fa-phone"></i> &nbsp; <a href="tel:<?php echo $arr_cm_phone[$row->parent_id] ?>"><?php echo $arr_cm_phone[$row->parent_id] ?></a></span><br />
        <span><i class="fa fa-envelope-o"></i> &nbsp; <a href="mailto:<?php echo $arr_cm_email[$row->parent_id] ?>"><?php echo $arr_cm_email[$row->parent_id] ?></a></span>
    </td>
	<?php	}?>
    <td>
<?php	if ($nslds_file_upload_status == "Uploaded") {?>
<a href="javascript:void(0)" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#myModal_nslds" onClick="view_nslds_snapshot_body('<?php	echo base_url("account/view_nslds_snapshot/" . $client_id) ?>', 'nslds_snapshot_body')">NSLDS Snapshot</a>
<?php	} else if ($initial_intake_status == "Complete") {?>

<?php } else {?>
<form action="<?=base_url('account/customer/view/' . $row->id)?>" method="post" enctype="multipart/form-data" target="_blank">
<p><button type="button" name="submit_send_intake" class="btn btn-warning btn-xs" onClick="send_intake_email('<?php	echo $row->id; ?>')">Send intake</button></p>
</form>
<?php	}?>
    </td>
    <td>
      <?php
$fields = $this->db->query("SELECT * from settings Limit 1")->row_array();
	$accountType = $this->db->query("SELECT * from users_company where `id` = {$GLOBALS['loguser']['company_id']}")->row_array();
	$clientData = $this->db->query("SELECT * from clients where `client_id` = {$client_id}")->row_array();

	?>
<?php
//if($initial_intake_status == "Complete" && $nslds_file_upload_status == "Uploaded") {
	if ($initial_intake_status == "Complete") {
		?>



<?php
if ($accountType['account_type'] == 0) {
			if ($clientData['date_initially_viewed']) {
				?>
<a href="<?php	echo base_url($sg_1 . "/customer/current_analysis/" . $client_id) ?>" class="btn btn-primary btn-xs">Latest Analysis</a>
<?php
} else {
				?>

<a href="javascript:void(0)" data-toggle="modal" id="myModal_confirm_completed_task2" class="btn btn-primary btn-xs" data-target="#myModal_confirm_completed_task2Modal<?=$client_id?>">Latest Analysis</a>

<div id="myModal_confirm_completed_task2Modal<?=$client_id?>" class="modal fade" data-backdrop="static" role="dialog">
  <div class="modal-dialog ">
    <div class="modal-content">
      <div class="modal-body text-center">
        <h4>You are about to view the analysis for this Client for the first time which will incur a fee of <strong>$<?php	echo $fields['review_fee']; ?></strong> Do you wish to continue?</h4>
      </div>
      <div class="modal-footer">
        <a id="cct_y_url2244" href="<?php echo base_url('account/paywall_payment_process/' . $client_id . '/' . $sg_1); ?>" class="btn btn-primary">Yes</a>
        <a id="cct_y_url33" href="<?php echo base_url('account/dashboard'); ?>" class="btn btn-danger">No</a>
        <!-- <button type="button" class="btn btn-danger" data-dismiss="modal">No</button> -->
      </div>
    </div>
    <!-- base_url($sg_1 . "/customer_intake_form/" . $client_id . "/" . $tif['id']) -->
  </div>
</div>

<?php
}
			?>

<?php
} else {
			?>
<a href="<?php	echo base_url($sg_1 . "/customer/current_analysis/" . $client_id) ?>" class="btn btn-primary btn-xs">Latest Analysis</a>
<?php
}
		?>










<?php	} else {?>
<form action="<?=base_url('account/customer/view/' . $row->id)?>" method="post" enctype="multipart/form-data" target="_blank">
<p><button type="button" name="submit_send_intake" class="btn btn-warning btn-xs" onClick="send_intake_email('<?php	echo $row->id; ?>')">Send intake</button></p>
</form>
<?php	}?>
    </td>
    <td>
<?php	//if($nslds_file_upload_status == "Uploaded") {	?>

<?php
if (isset($arr_ics[$client_id]['2'])) {
		$tif = $arr_ics[$client_id]['2'];if ($tif['status'] == "Complete") {
			$cif_id = "cif_" . $tif['id'];
			if ($tif['status2'] != "Approved") {
				$cif_cls = 'class="btn btn-warning btn-xs"';
				$i_cls = "file-text-o";
				$vd_text = "Generate";} else {
				$cif_cls = 'class="btn btn-primary btn-xs"';
				$i_cls = "download";
				$vd_text = "Generate";}
			?>

<div><a href="<?php echo base_url($sg_1 . "/customer_intake_form/" . $client_id . "/" . $tif['id']); ?>" target="_blank" <?php echo $cif_cls; ?>><i class="fa fa-<?php echo $i_cls; ?>"></i> <?php echo $vd_text; ?> IDR Form</a></div>

<?php }}?>

<?php
if (isset($arr_ics[$client_id]['5'])) {
		$tif = $arr_ics[$client_id]['5'];if ($tif['status'] == "Complete") {
			$cif_id = "cif_" . $tif['id'];
			if ($tif['status2'] != "Approved") {
				$cif_cls = 'class="btn btn-warning btn-xs"';
				$i_cls = "file-text-o";
				$vd_text = "Generate";} else {
				$cif_cls = 'class="btn btn-primary btn-xs"';
				$i_cls = "download";
				$vd_text = "Generate";}
			?>

<div><a href="<?php echo base_url($sg_1 . "/customer_intake_form/" . $client_id . "/" . $tif['id']); ?>" target="_blank" <?php echo $cif_cls; ?>><i class="fa fa-<?php echo $i_cls; ?>"></i> <?php echo $vd_text; ?> Recertification Form</a></div>

<?php }}?>

<?php
if (isset($arr_ics[$client_id]['6'])) {
		$tif = $arr_ics[$client_id]['6'];if ($tif['status'] == "Complete") {
			$cif_id = "cif_" . $tif['id'];
			if ($tif['status2'] != "Approved") {
				$cif_cls = 'class="btn btn-warning btn-xs"';
				$i_cls = "file-text-o";
				$vd_text = "Generate";} else {
				$cif_cls = 'class="btn btn-primary btn-xs"';
				$i_cls = "download";
				$vd_text = "Generate";}
			?>

<div><a href="<?php echo base_url($sg_1 . "/customer_intake_form/" . $client_id . "/" . $tif['id']); ?>" target="_blank" <?php echo $cif_cls; ?>><i class="fa fa-<?php echo $i_cls; ?>"></i> <?php echo $vd_text; ?> Recalculation Form</a></div>

<?php }}?>

<?php
if (isset($arr_ics[$client_id]['7'])) {
		$tif = $arr_ics[$client_id]['7'];if ($tif['status'] == "Complete") {
			$cif_id = "cif_" . $tif['id'];
			if ($tif['status2'] != "Approved") {
				$cif_cls = 'class="btn btn-warning btn-xs"';
				$i_cls = "file-text-o";
				$vd_text = "Generate";} else {
				$cif_cls = 'class="btn btn-primary btn-xs"';
				$i_cls = "download";
				$vd_text = "Generate";}
			?>

<div><a href="<?php echo base_url($sg_1 . "/customer_intake_form/" . $client_id . "/" . $tif['id']); ?>" target="_blank" <?php echo $cif_cls; ?>><i class="fa fa-<?php echo $i_cls; ?>"></i> <?php echo $vd_text; ?> Switch IDR Form</a></div>

<?php }}?>


<?php
if (isset($arr_ics[$client_id]['3'])) {
		$tif = $arr_ics[$client_id]['3'];if ($tif['status'] == "Complete") {
			$cif_id = "cif_" . $tif['id'];
			if ($tif['status2'] != "Approved") {
				$cif_cls = 'class="btn btn-warning btn-xs"';
				$i_cls = "file-text-o";
				$vd_text = "Generate";} else {
				$cif_cls = 'class="btn btn-primary btn-xs"';
				$i_cls = "download";
				$vd_text = "Generate";}
			?>

<div><a href="<?php echo base_url($sg_1 . "/customer_intake_form/" . $client_id . "/" . $tif['id']); ?>" target="_blank" <?php echo $cif_cls; ?>><i class="fa fa-<?php echo $i_cls; ?>"></i> <?php echo $vd_text; ?> Consolidation Form</a></div>

<?php }}?>

<!--  Client Attestation  -->
<?php

	if (isset($arr_catstn[$client_id])) {
		if ($arr_catstn[$client_id]['status'] == "Pending") {?><div><a href="<?php echo base_url($sg_1 . "/attestation_form/" . $client_id); ?>" target="_blank" class="btn btn-warning btn-xs"><i class="fa fa-file-text-o"></i> Generate Attestation</a></div>
<?php } else {
			?>
<div><a href="<?php echo base_url($sg_1 . "/attestation_form/view/" . $client_id); ?>" target="_blank" class="btn btn-primary btn-xs"><i class="fa fa-download"></i> View Attestation</a><br><a href="<?php echo base_url($sg_1 . "/attestation_form/edit/" . $client_id); ?>" target="_blank" class="btn btn-warning btn-xs" style="margin-top: 5px;"><i class="fa fa-pencil"></i> Edit Attestation</a>
<?php
if ($arr_catstn[$client_id]['status'] != "Approved") {
				?>
<br><a href="<?php echo base_url($sg_1 . "/attestation_form/approve/" . $client_id); ?>" class="btn btn-info btn-xs" style="margin-top: 5px;"><i class="fa fa-check-square-o"></i> Approve Attestation</a>
  <?php
}
		}
		?>
</div>
<?php
}?>


<?php	//}	?>
    </td>
    <td><?php echo date('m/d/Y', strtotime($row->add_date)) ?></td>
    <td><?php echo $row->lname ?>, <?php echo $row->name ?></td>

</tr>
<?php	}?>
        </tbody>

      </table>
</div>
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->

    </section>
    <!-- /.content -->
  </div>



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


<div id="myModal_intake_form" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-body" id="intake_form_body" style="padding:0px;"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" onClick="hidemodel('myModal_intake_form')">Close</button>
      </div>
    </div>

  </div>
</div>


<?php	$this->load->view("account/inc/footer");?>

</div>
<?php	//$this->load->view("Admin/inc/template_js.php");	?>
<script src="<?php echo base_url('assets/crm/plugins/jQuery/jquery-2.2.3.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/crm/bootstrap/js/bootstrap.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/crm/plugins/datatables/jquery.dataTables.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/crm/plugins/datatables/dataTables.bootstrap.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/crm/plugins/slimScroll/jquery.slimscroll.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/crm/plugins/fastclick/fastclick.js'); ?>"></script>
<script src="<?php echo base_url('assets/crm/dist/js/app.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/crm/dist/js/demo.js'); ?>"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js" defer="defer"></script>

<script type="text/javascript">

	function deleteCustomer(id){

		$('#deleteYes').attr('href','<?php echo base_url('account/customer/delete/' . $row->id) ?>');
    $('#closeDelete').focus();
		$('#closeDelete button[type=submit]').focus();
		$('#deleteCustomer').modal('show');
	}


function send_intake_email(uid){
  $.post("<?php	echo base_url("account/send_intake_email"); ?>", {uid: uid}, function(result){
    if(result == "Failed") {	swal( 'Failed', 'Something went wrong.', 'error');	} else {	swal( 'Success!', 'Intake email successfully sent.', 'success');	}
  });
}

<?php if ($GLOBALS["loguser"]["role"] == "Company") {?>
  $(function () {
    $('#show_datatable').DataTable({
      "paging": true,
      "lengthChange": false,
	  "pageLength": 50,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": true,
       "columnDefs": [
        { "targets": [0,1,2,3,4,5,6,7], "searchable": false },
        { "targets": [8], "visible": false }
    ]
    });
  });
<?php } else {
	?>
 $(function () {
    $('#show_datatable').DataTable({
      "paging": true,
      "lengthChange": false,
    "pageLength": 50,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": true,
       "columnDefs": [
        { "targets": [0,1,2,3,4,5,6], "searchable": false },
        { "targets": [7], "visible": false }
    ]
    });
  });
<?php
}
?>


</script>

</body>
</html>
