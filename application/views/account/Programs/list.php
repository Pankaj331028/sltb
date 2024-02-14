<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php
error_reporting(0);
@extract($_POST);

$seg_1 = $this->uri->segment(1);
$seg_3 = $this->uri->segment(3);
$seg_4 = $this->uri->segment(4);

$prgr = $program_dadta['prgr'];
$stepr = $program_dadta['stepr'];
$stepr_by_step = $program_dadta['stepr_by_step'];
$arr_cm = $program_dadta['arr_cm'];
$arr_cpp = $program_dadta['arr_cpp'];
$arr_client = $program_dadta['arr_client'];
$arr_cpp_cids_tmp = $program_dadta['arr_cpp_cids_tmp'];

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
      <h1><strong><?php echo $prgr["program_title"]; ?> - Program Reports</strong></h1>
    </section>

    <!-- Main content -->
    <section class="content">
<?php	if (is_array($stepr)) {
	?>
      <div class="row">
        <!-- /.col -->
        <div class="col-md-12">
          <div class="nav-tabs-custom">
            <div class="tab-content">

              <div class="active tab-pane" id="settings">
<?php	$this->load->view("template/alert.php");?>
<ul class="nav nav-tabs">
  <li <?php if ($seg_4 != "late") {echo 'class="active"';}?>><a href="<?php	echo base_url($seg_1 . "/programs/" . $seg_3); ?>">Current</a></li>
  <li <?php if ($seg_4 == "late") {echo 'class="active"';}?>><a href="<?php	echo base_url($seg_1 . "/programs/" . $seg_3 . "/late"); ?>">Late</a></li>
</ul>

<div class="tab-content" style="border:1px solid #CCCCCC; padding:15px;">
  <div id="home" class="tab-pane fade in active">
<div class="table-responsive">
<table id="show_datatable" class="table table-bordered table-striped show_datatable">
        <thead>
        <tr class="info">
          <th style="vertical-align:top;">Client Name</th>
          <th style="vertical-align:top;">Case Manager</th>
          <th style="vertical-align:top;">Last Action Date</th>
          <th style="vertical-align:top;">Current Action</th>
          <th style="vertical-align:top;">Status</th>
          <th style="vertical-align:top;">Next Due Date</th>
          <th style="vertical-align:top;">Next Step</th>
          <?php	foreach ($stepr as $k => $v) {?>
          <td style="background:#F8F8F8; text-align:center;"><div style="width:100px;"><div><?php echo $v['step_name']; ?></div></div></td>
		  <?php	}?>
        </tr>
        </thead>

<?php
//foreach($arr_client as $client_id=>$cv)
	foreach ($arr_cpp_cids_tmp as $client_id) {
		$cv = $arr_client[$client_id];
		$arr_cpp[$client_id][$r->step_id];
		$n1 = $arr_cpp[$client_id]['step_id'];

		$cprgr = $arr_cpp[$client_id][$n1];

//  Client Attestation
		$q = $this->db->query("SELECT * FROM client_attestation where client_id =" . $client_id . " order by id desc");
		foreach ($q->result_array() as $res) {
			$arr_catstn[$client_id] = $res;
		}

		if ($cprgr->status == "Complete") {
			$cstatus = '<span style="color:green;">Completed</span>';
		} else {
			if ($cprgr->step_due_date < date('Y-m-d')) {$cstatus = '<span style="color:red;">Late</span>';} else { $cstatus = '<span style="color:green;">Current</span>';}
			//else { $cstatus = '<span style="color:#1b00ff;">Pending</span>'; }
		}

		//	Next Step
		if (isset($stepr_by_step[($n1 + 1)]['step_name'])) {$next_step_name = $stepr_by_step[($n1 + 1)]['step_name'];} else { $next_step_name = "N/A";}

		if ($cv->parent_id == 0) {$cv->parent_id = $cv->company_id;}

		?>
<tr style="background:#FFFFFF;">
	<td><a href="<?php echo base_url($seg_1 . "/customer/add_program/" . $client_id) ?>"><?php echo $cv->lname; ?>, <?php echo $cv->name; ?></a></td>
    <td><?php echo $arr_cm[$cv->parent_id]['lname'] . ', ' . $arr_cm[$cv->parent_id]['name']; ?>
	<?php //print_r("<pre>"); print_r($cprgr);	print_r("</pre>"); ?>
    </td>
    <td><?php echo date('m/d/Y', strtotime($cprgr->created_at)); ?></td>

    <td><?php echo $stepr[$cprgr->program_id]['step_name']; ?></td>
    <td><?php echo $cstatus; ?></td>
    <td><?php echo date('m/d/Y', strtotime($cprgr->step_due_date)); ?></td>
    <td><?php echo $next_step_name; ?></td>

<?php
$sg_1 = $this->uri->segment(1);
		foreach ($stepr as $k => $v) {
			$step_id = $v['step_id'];
			$str = $arr_cpp[$client_id][$step_id];
			if (isset($str->step_completed_date)) {
				if ($str->step_completed_date != "") {if ($str->program_id_primary == 97 && $step_id == 15) {$step_completed_date = date("m/d/Y", strtotime($str->step_completed_date)) . '<br><a href="' . base_url($sg_1 . "/attestation_form/view/" . $client_id) . '" target="_blank" class="btn btn-primary btn-xs"><i class="fa fa-download"></i> View Attestation</a><br><a href="' . base_url($sg_1 . "/attestation_form/edit/" . $client_id) . '" target="_blank" class="btn btn-warning btn-xs" style="margin-top: 5px;"><i class="fa fa-pencil"></i> Edit Attestation</a>';if ($arr_catstn[$client_id]['status'] != 'Approved') {$step_completed_date .= '<br><a href="' . base_url($sg_1 . "/attestation_form/approve/" . $client_id) . '" class="btn btn-info btn-xs" style="margin-top: 5px;"><i class="fa fa-check-square-o"></i> Approve Attestation</a>';}} else { $step_completed_date = date("m/d/Y", strtotime($str->step_completed_date));}} else {if ($str->program_id_primary == 97 && $step_id == 15) {$step_completed_date = '<a href="' . base_url($sg_1 . "/attestation_form/" . $client_id) . '" target="_blank" class="btn btn-warning btn-xs"><i class="fa fa-file-text-o"></i> Generate Attestation</a>';} else { $step_completed_date = "-";}}
			} else {if ($str->program_id_primary == 97 && $step_id == 15) {$step_completed_date = '<a href="' . base_url($sg_1 . "/attestation_form/" . $client_id) . '" target="_blank" class="btn btn-warning btn-xs"><i class="fa fa-file-text-o"></i> Generate Attestation</a>';} else { $step_completed_date = "-";}}
			?>
<td style="background:#FCFCFC;"><?php echo $step_completed_date; ?></td>
<?php }?>
</tr>
<?php }?>
        <tbody>
        </tbody>

      </table>
</div>

  </div>

</div>

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
<?php } else {?>
<p style="padding:20px 25px 150px 25px; background:#FFFFFF;">You have no client.</p>
<?php	}?>

    </section>
    <!-- /.content -->
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

<script>
  $(function () {
    $('#show_datatable').DataTable({
      "paging": true,
      "lengthChange": false,
	  "pageLength": 100,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": true
    });
  });
</script>

</body>
</html>
