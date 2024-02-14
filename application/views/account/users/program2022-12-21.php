<?php	defined('BASEPATH') OR exit('No direct script access allowed');	?>
<?php
error_reporting(0);
@extract($_POST);

$sg_3 = $this->uri->segment(3);

$client_id = $this->uri->segment(4);

if($this->uri->segment(4) > 0)
{

if($GLOBALS["loguser"]["role"] == "Company")  { $cndvar = "company_id"; } else {	$cndvar = "parent_id";	}
$user = $this->default_model->get_arrby_tbl('users','*',"role='Customer' and $cndvar='".$GLOBALS["loguser"]["id"]."' and id='".$this->uri->segment(4)."'",'1');
$user = $user["0"];
if(!isset($user['id'])) {	redirect(base_url("account/customer"));	exit;	}
}
?>
<!DOCTYPE html>
<html>
<head>
<?php	$this->load->view("account/inc/head");	?>
</head>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
<?php	$this->load->view("Site/inc/header");	?>


<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><strong>Programs</strong></h1>
      <p><strong>Client:</strong> <a href="<?php echo base_url("account/customer/view/".$this->uri->segment(4))?>"><?php echo $user['name']; ?> <?php echo $user['lname']; ?> (#<?php echo $user['id']; ?>)</a></p>
    </section>

    <!-- Main content -->
    <section class="content">

      <div class="row">
        <!-- /.col -->
        <div class="col-md-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
<?php if($this->uri->segment(4) > 0) {	?>
<li><a href="<?php echo base_url("account/customer/edit/".$this->uri->segment(4))?>"><i class="fa fa-pencil"></i> Edit</a></li>
<li><a href="<?php echo base_url("account/customer/view/".$this->uri->segment(4))?>"><i class="fa fa-eye"></i> View Client</a></li>
<li class="active"><a href="<?php echo base_url("account/customer/add_program/".$this->uri->segment(4))?>"><i class="fa fa-file-text-o"></i> Programs</a></li>
<!--<li><a href="<?php echo base_url("account/customer/status/".$this->uri->segment(4))?>"><i class="fa fa-file-text-o"></i> View Client Status</a></li>-->
<li><a href="<?php echo base_url("account/customer/document/".$this->uri->segment(4))?>"><i class="fa fa-upload"></i> Documents</a></li>
<!--<li><a href="<?php echo base_url("account/customer/report/".$this->uri->segment(4))?>"><i class="fa fa-line-chart"></i> View Reports</a></li>-->
<?php	}	else {	?>
              <li class="active"><a href=""><i class="fa fa-user-plus"></i> <?php echo $data["name"]; ?></a></li>
              <li><a href="<?php echo base_url("account/customer"); ?>"><i class="fa fa-users"></i> Clients</a></li>
<?php	}	?>
            </ul>
            <div class="tab-content">
              
              <div class="active tab-pane" id="settings">
<?php	$this->load->view("template/alert.php");	?>

<?php



$q = $this->db->query("SELECT * FROM intake_client_status where client_id='".$client_id."'  order by id desc");
foreach($q->result_array() as $res)
{
	$client_id = $res['client_id'];
	$intake_id = $res['intake_id'];
	$arr_ics[$client_id][$intake_id] = $res;
}


$ansR = $this->default_model->get_arrby_tbl_single('intake_file_result','*',"client_id='".$client_id."' and intake_question_id='6'",'1');
$intake_file_result_id = $ansR['intake_file_id'];
$nslds_file_error = "";
if(isset($ansR['intake_file_id']))
{
$client_document = $this->crm_model->document_decrypt($ansR['intake_file_location']);
if(file_exists($client_document))
{
	$file_data = read_file($client_document);
	$arr_file_data = preg_split("/\r\n|\n|\r/", $file_data);
} else {	$nslds_file_error = "NSLDS File no uploaded yet";	}
} else {	$nslds_file_error = "NSLDS File no uploaded yet";	}

if(isset($arr_ics[$client_id]['1']))
{
if($arr_ics[$client_id]['1']['status'] == "Complete") {} else {	$nslds_file_error = "Incomplete Intake";	}
} else {	$nslds_file_error = "Incomplete Intake";	}

if($nslds_file_error == "")
{

$carR = $this->default_model->get_arrby_tbl_single('client_analysis_results','*',"client_id='".$client_id."' and intake_id='1'",'1');
if(isset($carR['id']))
{

if($carR['scenario_selected']!="")
{

?>
<div>
<div style="width:250px; margin-bottom:15px; float:right;">
<form action="" method="post" enctype="multipart/form-data">
<div class="input-group">
<select name="program_id" class="form-control" required>
<option value="">Select Program</option>
<?php
$q = $this->db->query("SELECT * FROM program_definitions where 1 group by program_title order by program_title");
foreach ($q->result() as $r) {

$sql = "SELECT * FROM client_program_progress where client_id='".$this->uri->segment(4)."' and program_id='".$r->program_definition_id."'";
$qc = $this->db->query($sql);
if($qc->num_rows() == 0)
{
?>
<option value="<?php echo $r->program_definition_id?>" <?php if($program_id == $r->program_definition_id) { echo " selected"; } ?>><?php echo $r->program_title?></option>
<?php	} }	?>
</select>
    <span class="input-group-btn"><button type="submit" name="Submit_" class="btn btn-primary btn-flat">Add Program</button></span>
</div>
</form>

</div>
</div>
<?php	} } }	?>
<div class="clr"></div>
<div class="table-responsive">
<table class="table table-bordered show_datatable">
    <thead>
    <tr class="info">
      <!--<th width="1%">SNO</th>-->
      <th>Program Title</th>
      <th>Step</th>
      <th>Step Name</th>
      <th>Step Duration</th>
      <th>Due Date</th>
      <th>Complete Date</th>
      <th>Created Date</th>
      <th>Status</th>
      <th>Action</th>
    </tr>
    </thead>
    <tbody>
<?php

$arr_intake_program_id = $this->array_model->arr_intake_program_id();

$client_id = $this->uri->segment(4);
$q = $this->db->query("SELECT * FROM intake_client_status where client_id='$client_id'  order by id desc");
foreach($q->result_array() as $res)
{
	$intake_id = $res['intake_id'];
	$arr_ics[$intake_id] = $res;
}




$q = $this->db->query("SELECT * FROM program_definitions where 1");
foreach ($q->result() as $r) 
{
	$arr_pr[$r->program_definition_id] = $r;
}


$arr_checklis = array();
$sno = 0;
if($GLOBALS["loguser"]["role"] == "Company")  { $fld_name = "company_id"; } else {	$fld_name = "added_by";	}
$query = $this->db->query("SELECT * FROM client_program_progress where $fld_name='".$GLOBALS["loguser"]["id"]."' and client_id='".$client_id."' order by created_at desc");
foreach ($query->result() as $row)
{
?>
<tr id="dtbl_<?php echo $row->program_definition_id; ?>">
<!--<td><?php echo ++$sno; ?></td>-->
<td><?php echo $arr_pr[$row->program_id]->program_title?></td>
<td><?php echo $row->step_id; ?></td>
<td><?php echo $arr_pr[$row->program_id]->step_name; ?>
<?php
if($row->step_id == 6)
{
	$indx = $arr_intake_program_id[$row->program_id_primary];
	$tif = $arr_ics[$indx];
	
	if(isset($arr_ics[$indx])) { $tif = $arr_ics[$indx]; if($tif['status'] == "Complete") { $cif_id = "cif_".$tif['id'];
if($tif['status2'] != "Approved") { $cif_cls = 'class="btn btn-warning btn-xs"'; $i_cls = "file-text-o"; } else { $cif_cls = 'class="btn btn-primary btn-xs"'; $i_cls = "file-pdf-o"; }
	if($indx == "2") {	$frm_ttl = "IDR";	} else if($indx == "3") {	$frm_ttl = "Consolidation";	} else { $frm_ttl = ""; }
	
	echo '<br /><a href="'.base_url("account/customer_intake_form/".$client_id."/".$tif['id']).'" target="_blank" '.$cif_cls.'><i class="fa fa-'.$i_cls.'"></i> '.$frm_ttl.' Intake Form</a>';
	}}
}

?>
</td>
<td><?php echo $arr_pr[$row->program_id]->step_duration; ?> Day</td>
<td><?php echo date('m/d/Y',strtotime($row->step_due_date)); ?></td>
<td><?php if($row->step_completed_date!='') { echo date('m/d/Y',strtotime($row->step_completed_date)); }	?></td>
<td><?php echo date('m/d/Y',strtotime($row->created_at)); ?></td>
<td>
<?php	if(trim($row->step_completed_date)!='' && $row->status=='Complete') {	?><span style="color:green; font-weight:bold;">Completed</span><?php	} else if($row->status=='Stop') {	?><span style="color:red; font-weight:bold;">Stop</span><?php	} else  {	?><span style="color:yellow; font-weight:bold;">Pending</span><?php	}	?>
</td>
<td>
<?php	
$enable_aprv_btn = "Yes";
if($row->program_id_primary == 91) {
if($row->step_id != 4) { $enable_aprv_btn = "No"; }
}

if($row->status=='Pending' && $row->step_id != 5 && $row->step_id != 6 && $enable_aprv_btn == 'Yes') {	?>
<button title="Completed Task" class="btn btn-sm btn-primary" onClick="return confirm_completed_task('<?php echo base_url('account/customer/status/'.$row->client_id.'/complete/'.$row->program_definition_id)?>')" data-toggle="modal" data-target="#myModal_confirm_completed_task"><i class="fa fa-check" aria-hidden="true"></i> Completed Step</button>
<?php	}	?>
</td>
</tr>
<?php	}	?>
    </tbody>
    
  </table>
</div>

<div style="margin-top:15px;"><a href="javascript:void(0)" class="btn btn-danger btn-flat" onClick="cap_stop_remonder('<?php echo $client_id; ?>')"><i class="fa fa-ban"></i> Stop Reminders</a></div>

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

<?php	$this->load->view("account/inc/footer");	?>

</div>

<?php	foreach($arr_checklis as $res) {	?>
<div id="<?php echo $res['moal_target']; ?>" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <div class="modal-content">
      <div class="modal-header bg-primary">
        <button type="button" class="close" data-dismiss="modal" style="opacity:1; color:#FFFFFF;">&times;</button>
        <h4 class="modal-title"><strong><?php echo strtoupper($res['title']); ?></strong></h4>
      </div>
      
      <div class="modal-body" style="padding:0px;">
<table class="table table-bordered">
<?php

$program_id_primary = $res['program_id_primary'];
$arr_intake_program_id = $this->array_model->arr_intake_program_id();
$intake_id = $arr_intake_program_id[$program_id_primary];
$intake_question_data = $this->default_model->get_arrby_tbl('intake_question','*',"intake_id='".$intake_id."' order by placement_order asc",'500');

foreach($intake_question_data as $row)
{
	$intake_question_id = $row['intake_question_id'];
	$question_required = $row['question_required'];
	$ansR = $this->crm_model->admin_intake_answer_by_client($client_id, $intake_question_id);
	
	$ans = "";
	if($row['intake_question_type'] == 'Comment') { $ans = $ansR['intake_comment_body'];
	} elseif($row['intake_question_type'] == 'Radio' || $row['intake_question_type'] == 'Radio Group') {
		$arr_ans = array();
		$radiogroups = $this->default_model->get_arrby_tbl('intake_answer','*',"intake_question_id='".$intake_question_id."'",'500');
		foreach($radiogroups as $radiogroup)
		{
			if($radiogroup['intake_answer_id'] == $ansR['intake_answer_id']) { $arr_ans[] = $radiogroup['intake_answer_body']; }
		}
		$ans = implode(",", $arr_ans);
	}
	
?>
<tr>	<th><?php echo $row['intake_question_body']; ?></th>	<td><?php echo $ans ?></td>	</tr>
<?php
}

?>
</table>
      </div>
      
      <div class="modal-footer">
        <?php if($res['sts'] == "Pending") { ?>
        <a title="Completed Task" href="<?php echo base_url('account/customer/status/'.$client_id.'/complete/'.$res['program_definition_id'])?>" class="btn btn-sm btn-primary" onClick="return confirm('Are you sure?')"><i class="fa fa-check-square-o" aria-hidden="true"></i> Approve</a>
        <?php } else { ?><button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
        <?php	}	?>
      </div>
            
    </div>

  </div>
</div>
<?php	}	?>

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
    $('.show_datatable').DataTable({
      "paging": true,
      "lengthChange": true,
	  "pageLength": 100,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": true
    });
  });
</script>

<script type="text/javascript">

function cap_stop_remonder(client_id){
  $.post("<?php	echo base_url("account/send_intake_email");	?>", {client_id: client_id}, function(result){
    swal( 'Success!', 'Reminder successfully stop.', 'success');
  });
}



function confirm_completed_task(url)
{
	$("#cct_y_url").attr("href", url);
	return false; 
}

</script>


<!-- Modal -->
<div id="myModal_confirm_completed_task" class="modal fade" role="dialog">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-body text-center">
        <h3><strong>Do you want to continue?</strong></h3>
      </div>
      <div class="modal-footer">
        <a id="cct_y_url" href="javascript:void(0)" class="btn btn-primary">Yes</a>
        <button type="button" class="btn btn-danger" data-dismiss="modal">No</button>
      </div>
    </div>

  </div>
</div>


</body>
</html>
