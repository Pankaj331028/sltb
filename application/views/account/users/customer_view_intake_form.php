<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php

error_reporting(0);
@extract($_POST);
$fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);

$user = $this->default_model->get_arrby_tbl('users', '*', "role='Customer' and id='" . $this->uri->segment(3) . "'", '1');
$user = $user["0"];
@extract($user);
$client_id = $id;

if ($client_id != '') {
	$q = $this->db->query("SELECT * FROM intake_client_status where client_id='$client_id' and id='" . $this->uri->segment(4) . "' limit 1");
	$ics = $q->row_array();
	if (isset($ics['id'])) {

		$intake_id = $ics['intake_id'];
		foreach ($this->array_model->arr_intake_program_id() as $k => $v) {if ($v == $intake_id) {$program_id_primary = $k;}}

		$q = $this->db->query("SELECT * FROM client_program_progress where client_id='$client_id' and program_id_primary='$program_id_primary' and step_id='6' limit 1");
		$cpp = $q->row_array();

		$program_definition_id = $cpp['program_definition_id'];
		$program_id_primary = $cpp['program_id_primary'];

		$print_div_id = "print_" . time();

		?>

<div class="modal-header">
    <h4 class="modal-title pull-left">
    	<strong id="intake_form_title"><?php echo $title; ?></strong>
<?php if ($cpp['step_completed_date'] == '') {} else {?><br /><span style="font-size:14px; color:#009900;"><i class="fa fa-check-square-o" aria-hidden="true"></i> Already Approved</span><?php }?>
    </h4>

<div class="text-right">
<a href="javascript:void(0)" class="btn btn-warning" onclick="printDiv('<?php echo $print_div_id; ?>')"><i class="fa fa-print"></i> Print</a> &nbsp;

<?php if ($cpp['step_completed_date'] == '') {?>
<a href="javascript:void(0)" class="btn btn-primary" onClick="return approve_intake_form_body('<?php echo base_url('account/customer/status/' . $client_id . '/complete/' . $program_definition_id) ?>', '<?php echo $_POST['aid']; ?>', '<?php echo $_POST['title']; ?>')"><i class="fa fa-check-square-o" aria-hidden="true"></i> Approve</a> &nbsp;
<?php if ($ics['status2'] == "Pending") {?>
<a href="javascript:void(0)" class="btn btn-success" onClick="return approve_intake_form_body('<?php echo base_url('account/customer_intake_client_status_change/' . $client_id . '/' . $ics['id']) ?>', '<?php echo $_POST['aid']; ?>', '<?php echo $_POST['title']; ?>')"><i class="fa fa-floppy-o" aria-hidden="true"></i> Save</a><?php }?>
<?php }?>
</div>
</div>




<div style="padding:5px 15px;" id="<?php echo $print_div_id; ?>">

<table class="table table-bordered">
<?php
//$client_id = $this->uri->segment(4);
		$arr_intake_program_id = $this->array_model->arr_intake_program_id();
		$intake_id = $ics['intake_id'];
		$intake_question_data = $this->default_model->get_arrby_tbl('intake_question', '*', "intake_id='" . $intake_id . "' order by placement_order asc", '500');

		foreach ($intake_question_data as $row) {
			$intake_question_id = $row['intake_question_id'];
			$ansR = $this->crm_model->admin_intake_answer_by_client($client_id, $intake_question_id);

			$ans = "";
			if ($row['intake_question_type'] == 'Comment') {
				$ans = $ansR['intake_comment_body'];
			} elseif ($row['intake_question_type'] == 'Radio' || $row['intake_question_type'] == 'Radio Group') {
				$arr_ans = array();
				$radiogroups = $this->default_model->get_arrby_tbl('intake_answer', '*', "intake_question_id='" . $intake_question_id . "' and status_flag='Active'", '500');
				foreach ($radiogroups as $radiogroup) {
					if ($radiogroup['intake_answer_id'] == $ansR['intake_answer_id']) {$arr_ans[] = $radiogroup['intake_answer_body'];}
				}
				$ans = implode(",", $arr_ans);
			}

			?>
<tr>	<th><?php echo $row['intake_question_body']; ?></th>	<td><?php echo $ans; ?></td>	</tr>
<?php
}

		?>
</table>
</div>



<?php
}
}

?>