<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php
//ALTER TABLE `intake_client_status` ADD `form_data` TEXT NOT NULL AFTER `intake_id`;

error_reporting(0);
@extract($_POST);
$fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);

$sg_1 = $this->uri->segment(1);
$client_id = $this->uri->segment(3);
if ($GLOBALS["loguser"]["role"] == "Customer") {$client_id = $GLOBALS["loguser"]["id"];}

$user = $client_data["client"];
@extract($user);
$client_id = $id;

if ($client_id != '') {
	if (isset($ics['id'])) {
		$intake_id = $ics['intake_id'];
		$program_id_primary = $intkR['program_definition_id'];
		$intk_idr = $client_data['intake_client_status']['form_data'];
		$idr_formdata = [];

		if (!empty($intk_idr)) {
			$idr_formdata = (array) json_decode($client_data['intake_client_status']['form_data']);
		}

// Submit Data
		if (isset($_POST['Submit_save']) || isset($_POST['Submit_approve'])) {
			$error = "";

			$ssn_validation_regex = '/^(?!666|000|9\\d{2})\\d{3}-(?!00)\\d{2}-(?!0{4})\\d{4}$/';
			if (isset($_POST['inputr']['user']['spouse_ssn'])) {
				if (trim($_POST['inputr']['user']['spouse_ssn']) != "") {
					$rss = preg_match($ssn_validation_regex, $_POST['inputr']['user']['spouse_ssn']); // returns 1
					if ($rss != '1') {$error .= "Please enter valid <em>Spouse SSN</em>" . "<br />";}
				}
			}

			if ($error == "") {
				$form_data = $_POST;
				unset($form_data['Submit_save']);
				unset($form_data['Submit_approve']);
				$form_data = json_encode($form_data);
				$this->db->query("UPDATE intake_client_status set form_data='$form_data' where client_id='$client_id' and id='" . $this->uri->segment(4) . "' limit 1");

				if (isset($_POST['Submit_approve'])) {
					$this->db->query("UPDATE intake_client_status set exp_date='" . date('Y-m-d') . "', status2='Approved' where client_id='$client_id' and id='" . $this->uri->segment(4) . "' limit 1");

					$q = $this->db->query("SELECT * FROM client_program_progress where client_id='$client_id' and program_id_primary='$program_id_primary' and step_id='6' limit 1");
					$cpp = $q->row_array();
					$url = base_url('account/customer/status/' . $client_id . '/complete/' . $cpp['program_definition_id'] . "/redirect_to_document/" . $ics['id']);
					redirect($url);
					exit;
				}

			} else {
				$this->session->set_flashdata('error', $error);
			}
			redirect(base_url('account/customer_intake_form/' . $client_id . '/' . $ics['id']));
			exit;
		}

		$q = $this->db->query("SELECT * FROM client_program_progress where client_id='$client_id' and program_id_primary='$program_id_primary' and step_id='6' limit 1");
		$cpp = $q->row_array();
		$program_definition_id = $cpp['program_definition_id'];

		if ($ics['status2'] == 'Approved') {
			if ($cpp['step_completed_date'] == '' || $cpp['status'] == 'Pending') {
				redirect(base_url('account/customer/status/' . $client_id . '/complete/' . $program_definition_id));
				exit;
			} else {
				$q = $this->db->query("SELECT * FROM client_documents where client_id='$client_id' and intake_client_status_id='" . $ics['id'] . "' limit 1");
				$cdr = $q->row_array();

				$docname = $user['lname'] . " " . $user['name'] . " " . $cdr['document_name'] . " " . $cdr['document_name'] . "-Internal.pdf";
				$docname = str_replace("Intake", "", $docname);
				$docname = trim($docname);

				redirect(base_url('account/intake_form_document/' . $client_id . '/' . $cdr['document_id'] . "/" . $docname));
				exit;
			}
		}

		$file_name = $user['lname'] . " " . $user['name'] . " " . str_replace("Intake", "", $intkR['intake_title']) . " " . date('Y-m-d', strtotime($ics['add_date'])) . "-Internal.pdf";
		if ($this->db->query('select * from intake_client_status where client_id=' . $client_id . ' and intake_id=4')->num_rows() > 0) {
			$intake_question_data = $this->default_model->get_arrby_tbl('intake_question', '*', "intake_id='4' order by placement_order asc", '500');
		} else {
			$intake_question_data = $this->default_model->get_arrby_tbl('intake_question', '*', "intake_id='1' order by placement_order asc", '500');
		}

		foreach ($intake_question_data as $row) {
			$intake_question_id = $row['intake_question_id'];
			$placement_order = $row['placement_order'];
			$ansintkR[$placement_order] = $this->crm_model->admin_intake_answer_by_client($client_id, $intake_question_id);
		}

		$ansR = array();
		$intake_question_data = $this->default_model->get_arrby_tbl('intake_question', '*', "intake_id='" . $intake_id . "' order by placement_order asc", '500');
		foreach ($intake_question_data as $row) {
			$intake_question_id = $row['intake_question_id'];
			$placement_order = $row['placement_order'];
			$ansR[$placement_order] = $this->crm_model->admin_intake_answer_by_client($client_id, $intake_question_id);
		}

		$it = $intkR['intake_title'];
		if ($it == "IDR Intake" || $it == "Consolidation Intake") {$opt_1 = 1;} else if ($it == "Recertification Intake") {$opt_1 = 2;} else if ($it == "Recalculation Intake") {$opt_1 = 3;} else { $opt_1 = 4;}

		$marital_status = $car['marital_status'];
		$include_in_client_report = $car['include_in_client_report'];
		$scenario_selected = $car['scenario_selected'];
		$payment_plan_selected = $car['payment_plan_selected'];
		$file_joint_or_separate = $car['file_joint_or_separate'];
		$car_icr = json_decode($car['include_in_client_report'], true);

		$scenario_monthly = 0;
		if (trim($car['client_monthly'])) {$scenario_monthly += ($car['client_monthly'] + 0);}
		if (trim($car['spouse_monthly'])) {$scenario_monthly += ($car['spouse_monthly'] + 0);}

		if ($payment_plan_selected == "10-Year Standard" || $payment_plan_selected == "25-Year Extended") {$opt_2 = 1;} else if ($payment_plan_selected == "REPAYE") {$opt_2 = 2;} else if ($payment_plan_selected == "IBR" || $payment_plan_selected == "New IBR") {$opt_2 = 3;} else if ($payment_plan_selected == "PAYE") {$opt_2 = 4;} else if ($payment_plan_selected == "ICR") {$opt_2 = 5;} else { $opt_2 = 1;}

		$sql = "SELECT distinct(loan_contact_name) as loan_contact_name FROM nslds_loans where client_id='$client_id' and (loan_outstanding_principal_balance>'0' or loan_outstanding_interest_balance>'0')";
		$q = $this->db->query($sql);
		$ln_num = $q->num_rows();
		if ($ln_num > 1) {$opt_3 = 1;}
		if ($ln_num <= 1) {$opt_3 = 2;}

		if ($car['deferment_forbearance_status'] == "0") {$opt_4 = 1;}
		if ($car['deferment_forbearance_status'] == "1") {$opt_4 = 2;}
		if ($car['deferment_forbearance_status'] == "2") {$opt_4 = 3;}

		$fd = array();
		$fd['name'] = trim($user['name'] . " " . $ansR[1]['intake_comment_body'] . " " . $user['lname']);
		$fd['ssn'] = $ansR[3]['intake_comment_body'];
		$fd['address'] = $ansR[5]['intake_comment_body'];
		$fd['city'] = $ansR[6]['intake_comment_body'];
		$fd['state'] = $ansR[7]['intake_comment_body'];
		$fd['zipcode'] = $ansR[8]['intake_comment_body'];
		$fd['telephone_primary'] = $user['phone'];
		$fd['telephone_alternate'] = "";
		$fd['email'] = $ansR[10]['intake_comment_body'];

		$fd['spouse_ssn'] = $fd['spouse_name'] = $fd['spouse_dob'] = "";

		if ($ics['form_data'] != "") {
			$form_data = json_decode($ics['form_data'], true);
			if (isset($form_data['inputr'])) {
				$fd['spouse_ssn'] = $form_data['inputr']['user']['spouse_ssn'];
				$fd['spouse_name'] = $form_data['inputr']['user']['spouse_name'];
				$fd['spouse_dob'] = $form_data['inputr']['user']['spouse_dob'];
			}
		}

		$radio[1] = $opt_1;
		$radio[2] = $opt_2;
		$radio[3] = $opt_3;
		$radio[4] = $opt_4;

		if ($ansintkR[12]['intake_answer_id'] == "16") {$radio[8] = 1;}
		if ($ansintkR[12]['intake_answer_id'] == "17") {$radio[8] = 2;}

		if ($file_joint_or_separate == "18") {$radio[10] = 1;} else { $radio[10] = 2;}

		$ss_arr = explode(" ", $car['scenario_selected']);
		$ss_group = $ss_arr[0];
		$ss_group_indx = $ss_arr[1];

		$payment_plan_scenario_group = $this->array_model->stlb_payment_plan_scenario_group();
		$ss_arr_selected_arr = explode(" ", $payment_plan_scenario_group[$ss_group][$ss_group_indx]["name"]);
		$ss_arr_selected = trim(strtoupper($ss_arr_selected_arr[1]));

		$ss_arr_selected_arr = explode(" ", $car['scenario_selected']);
		$ss_arr_selected = trim(strtoupper($ss_arr_selected_arr[1]));

		if ($ss_arr_selected == "MONTHLY") {$radio[11] = $radio[13] = $radio[14] = $radio[17] = $radio[19] = 1;}
		if ($ss_arr_selected == "AGI") {$radio[11] = $radio[13] = $radio[14] = $radio[17] = $radio[19] = 2;}
		if ($ss_arr_selected != "MONTHLY" && $ss_arr_selected != "AGI") {$radio[11] = $radio[13] = $radio[17] = $radio[19] = 3;}

		if ($scenario_monthly > 0) {$radio[12] = $radio[15] = $radio[16] = $radio[18] = $radio[20] = 1;}
		if ($scenario_monthly <= 0) {$radio[12] = $radio[15] = $radio[16] = $radio[18] = $radio[20] = 2;}

/*print_r("<pre>");
print_r($radio);
print_r("</pre>");
echo "<hr />";*/

		$print_div_id = "print_" . time();

		?>
<!DOCTYPE html>
<html>

<head>
	<?php
$page_data['data']['name'] = $page_data['data']['meta_title'] = ucfirst($file_name);

		$this->load->view("account/inc/head", $page_data);?>
	<style type="text/css">
	body {
		margin: 0px;
		padding: 0px;
		color: #000000;
		font-family: Calibri, sans-serif;
	}

	* {
		box-sizing: border-box;
		-moz-box-sizing: border-box;
	}

	strong {
		font-weight: bold;
	}

	.clr {
		clear: both;
		height: 0px;
	}

	.disp_none {
		display: none;
	}

	.font-10 {
		font-size: 10px;
	}

	.font-13 {
		font-size: 13px;
	}

	.font-15 {
		font-size: 15px;
	}

	.mb_3 {
		margin-bottom: 3px;
	}

	.mb_5 {
		margin-bottom: 5px;
	}

	.mb_7 {
		margin-bottom: 7px;
	}

	.mb_8 {
		margin-bottom: 8px;
	}

	.mb_10 {
		margin-bottom: 10px;
	}

	.mb_15,
	.mb-3 {
		margin-bottom: 15px;
	}

	.mb_20 {
		margin-bottom: 20px;
	}

	.mb_20_ {
		margin-bottom: -20px;
	}

	.mb_25_ {
		margin-bottom: -25px;
	}

	.pb_5 {
		padding-bottom: 5px;
	}

	.bg_1 {
		background: #f1f4ff;
		color: #777777;
	}

	.style_1 {
		color: black;
		font-family: Calibri, sans-serif;
		font-style: normal;
		font-weight: normal;
		text-decoration: none;
		font-size: 8pt;
		line-height: 10pt;
	}

	.em_ul_style {
		text-decoration: underline;
		font-style: normal;
	}

	.line_border_1 {
		width: 100%;
		height: 2px;
		margin: 5px 0px;
		background: #000000;
	}

	.line_border_2 {
		width: 100%;
		height: 1px;
		margin: 5px 0px;
		background: #000000;
	}

	.input_td {
		background: transparent;
		color: #777777;
		font-size: 13px;
		padding: 3px 5px;
		border-bottom: 1px solid #888888;
	}

	.input_div_2 {
		width: auto;
		height: 13px;
		font-size: 12px;
		color: #000000;
		border-bottom: 1px solid #666666;
		padding: 2px 5px;
	}

	.input_2 {
		width: 100%;
		height: 15px;
		padding: 2px 2px 2px 2px;
		background: transparent;
		font-size: 13px;
		border: none;
		border-bottom: 1px solid #000000;
	}

	.input_3 {
		width: 100%;
		height: 15px;
		padding: 2px 2px 2px 2px;
		background: transparent;
		font-size: 13px;
		border: none;
	}

	.input_4 {
		width: 100%;
		height: 16px;
		padding: 2px 2px 1px 2px;
		background: transparent;
		font-size: 11px;
		border: none;
		border-bottom: 1px solid #000000;
	}


	.page_wrapper {
		width: 100%;
		margin: 0 auto;
		overflow: hidden;
	}

	.mrgn_consolidation {
		margin: 0;
	}

	.mrgn_idr {
		margin: 0;
	}

	.pagebreak {
		display: block;
		height: 0px;
		clear: both;
		page-break-after: always;
		border: 1px solid #CCCCCC;
	}

	.pagging_text {
		font-size: 9px;
		text-align: center;
	}

	.pagging_text_1 {
		font-size: 10px;
		text-align: right;
		margin-top: -20px;
		font-weight: bold;
	}
	</style>
</head>

<body style="padding-bottom:25px;">
	<form action="" method="post" enctype="multipart/form-data">
		<?php
$fi = 1;
		if ($intkR['intake_title'] == "IDR Intake" || $intkR['intake_title'] == "Consolidation Intake" || $intkR['intake_title'] == "Recertification Intake" || $intkR['intake_title'] == "Recalculation Intake" || $intkR['intake_title'] == "Switch IDR Intake") {
			$ssn = explode("-", $ansR[3]['intake_comment_body']);
			?>
		<div style="position:fixed; top:0px; left:0px; width:100%; height:60px; background:#F8F8F8; z-index:9999;">
			<div class="container">
				<div class="row">
					<div class="col-md-7">
						<h3 style="margin:5px 0 0 0px;"><strong>Data Confirmation -
								<?php echo str_replace("Intake", "", $intkR['intake_title']); ?>
								<!--for the <?php //echo $intkR['intake_title']; ?>--></strong></h3>
						<?php if ($cpp['step_completed_date'] == '' || $ics['status2'] != 'Approved') {} else {?><span style="font-size:14px; color:#009900;"><i class="fa fa-check-square-o" aria-hidden="true"></i> Already Approved</span>
						<?php }?>
					</div>
					<div class="col-md-5">
						<div class="text-right" style="margin:10px 0 0 0;">
							<!--<a href="javascript:void(0)" class="btn btn-warning" onClick="printDiv('<?php echo $print_div_id; ?>')"><i class="fa fa-print"></i> Print</a> &nbsp; -->
							<?php //if($ics['status2']=="Pending"){ ?><button type="submit" name="Submit_save" class="btn btn-success"><i class="fa fa-floppy-o" aria-hidden="true"></i> Save</button> &nbsp;
							<?php //} ?>
							<?php //if($cpp['step_completed_date']=='' || $cpp['status']!='Complete') { ?><button type="submit" name="Submit_approve" class="btn btn-primary"><i class="fa fa-check-square-o" aria-hidden="true"></i> Approve</button>
							<?php //} ?>
							&nbsp;
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="clr"></div>
		<div style="width:100%; height:80px;"></div>
		<div class="clr"></div>
		<div class="container">
			<?php	if ($GLOBALS["loguser"]["role"] != "Customer") {?>
			<div class="alert" style="background:#dff0d8; border:#dff0d8; color:#3c763d;"><strong>Note:</strong> To make corrections to the Recertification, return to <a href="<?php echo base_url($sg_1 . "/customer/current_analysis/" . $client_id); ?>" style="color:#337ab7;">the Analysis</a> page and make your updates including possible payment plan calculation changes there. Then return to this page for it to be updated.</div>
			<?php }?>
			<div id="<?php echo $print_div_id; ?>">
				<div>
					<?php	$this->load->view("template/alert.php");?>
				</div>
				<div class="row">
					<div class="col-md-12"><span>Please enter or correct the following information.</span></div>
					<div>
						<div class="col-md-12">
							<h4 style="margin-bottom:0px;"><strong>Section 1: Borrower Information</strong></h4>
							<hr style="margin-top:5px;" />
						</div>
						<div class="col-md-4">
							<div class="mb-3 mt-3"><label for="email" class="form-label">SSN:</label> <input type="text" class="form-control" value="<?php echo $fd['ssn']; ?>" readonly required /></div>
						</div>
						<div class="col-md-4">
							<div class="mb-3 mt-3"><label for="email" class="form-label">Name:</label> <input type="text" class="form-control" value="<?php echo $fd['name']; ?>" readonly required /></div>
						</div>
						<div class="clr"></div>
						<div class="col-md-12">
							<div class="mb-3 mt-3"><label for="email" class="form-label">Address: </label> <input type="text" class="form-control" value="<?php echo $fd['address']; ?>" readonly /></div>
						</div>
						<div class="clr"></div>
						<div class="col-md-4">
							<div class="mb-3 mt-3"><label for="email" class="form-label">City:</label><input type="text" class="form-control" value="<?php echo $fd['city']; ?>" readonly /></div>
						</div>
						<div class="col-md-4">
							<div class="mb-3 mt-3"><label for="email" class="form-label">State:</label><input type="text" class="form-control" value="<?php echo $fd['state']; ?>" readonly /></div>
						</div>
						<div class="col-md-4">
							<div class="mb-3 mt-3"><label for="email" class="form-label">Zip Code:</label><input type="text" class="form-control" value="<?php echo $fd['zipcode']; ?>" readonly /></div>
						</div>
						<div class="clr"></div>
						<div class="col-md-4">
							<div class="mb-3 mt-3"><label for="email" class="form-label">Telephone - Primary:</label>
								<input type="text" class="form-control" value="<?php echo $fd['phone']; ?>" readonly required /></div>
						</div>
						<div class="col-md-4">
							<div class="mb-3 mt-3"><label for="email" class="form-label">Telephone - Alternate:</label>
								<input type="text" class="form-control" value="<?php echo $fd['telephone_alternate']; ?>" readonly /></div>
						</div>
						<div class="col-md-4">
							<div class="mb-3 mt-3"><label for="email" class="form-label">Email (Optional):</label>
								<input type="email" class="form-control" value="<?php echo $fd['email']; ?>" readonly /></div>
						</div>
						<div class="clr"></div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<?php
$q = 0;
			$a = 0;
			$a2 = 0;
			if ($intake_type == 'update') {
				$q = 96;
				$a = 74;
				$a2 = 62;
			}

			if ($radio['1'] == "2") {$radio['2'] = "";}
			if ($marital_status != ($a + 15)) {$radio['8'] = $radio['10'] = $fd['spouse_ssn'] = $fd['spouse_name'] = $fd['spouse_dob'] = "";}
			if ($radio['8'] == "2") {$fd['spouse_ssn'] = $fd['spouse_name'] = $fd['spouse_dob'] = "";}
			if ($radio['10'] == "2") {$radio['13'] = $radio['14'] = $radio['15'] = $radio['16'] = $radio['17'] = $radio['18'] = $radio['19'] = $radio['20'] = "";}
			if ($radio['11'] == "2") {$radio['12'] = $radio['13'] = $radio['14'] = $radio['15'] = $radio['16'] = $radio['17'] = $radio['18'] = $radio['19'] = $radio['20'] = "";}
			if ($radio['12'] == "1" || $radio['12'] == "2") {$radio['13'] = $radio['14'] = $radio['15'] = $radio['16'] = $radio['17'] = $radio['18'] = $radio['19'] = $radio['20'] = "";}
			if ($radio['13'] == "1" || $radio['13'] == "3") {$radio['15'] = $radio['16'] = $radio['17'] = $radio['18'] = $radio['19'] = $radio['20'] = "";}
			if ($radio['14'] == "2") {$radio['15'] = $radio['16'] = $radio['17'] = $radio['18'] = $radio['19'] = $radio['20'] = "";}
			if ($radio['16'] == "2") {$radio['19'] = $radio['20'] = "";}
			if ($radio['17'] == "2") {$radio['18'] = "";}
			if ($radio['19'] == "2") {$radio['20'] = "";}

			$idr_disp_1 = $idr_disp_2 = $idr_disp_8 = $idr_disp_9 = $idr_disp_10 = $idr_disp_11 = $idr_disp_12 = $idr_disp_13 = $idr_disp_14 = $idr_disp_15 = $idr_disp_16 = $idr_disp_17 = $idr_disp_18 = $idr_disp_19 = $idr_disp_20 = $section_4c = $section_4d = "";

			if ($radio['19'] == "2") {$idr_disp_20 = "disp_none";}
			if ($radio['17'] == "2") {$idr_disp_18 = "disp_none";}
			if ($radio['16'] == "2") {$idr_disp_19 = $idr_disp_20 = "disp_none";}
			if ($radio['14'] == "2") {$idr_disp_15 = $idr_disp_16 = $section_4d = "disp_none";}
			if ($radio['13'] == "1" || $radio['13'] == "3") {$idr_disp_15 = $idr_disp_16 = $section_4d = "disp_none";}
			if ($radio['12'] == "1" || $radio['12'] == "2") {$section_4c = $section_4d = "disp_none";}
			if ($radio['11'] == "2") {$idr_disp_12 = $section_4c = $section_4d = "disp_none";}
			if ($radio['10'] == "2") {$section_4c = $section_4d = "disp_none";}
			if ($radio['8'] == "2") {$idr_disp_9 = "disp_none";}
			if ($marital_status == ($a + 14) || $marital_status == ($a + 72) || $radio['11'] == ($a + 73)) {$idr_disp_8 = $idr_disp_9 = $idr_disp_10 = "disp_none";}
			if ($radio['1'] == "2") {$idr_disp_2 = "disp_none";}

			?>
					</div>
					<div class="col-md-12">
						<h4 style="margin-bottom:0px;"><strong>SECTION 2: REPAYMENT PLAN OR RECERTIFICATION REQUEST</strong></h4>
						<hr style="margin-top:5px;" />
					</div>
					<div class="col-md-6">
						<div class="mb-3 mt-3"><label for="email" class="form-label">1. Select the reason you are submitting this form (Check only one):</label>
							<div class="form-check"><input type="radio" class="form-check-input" value="1" <?php if ($radio[1] == "1") {echo ' checked="checked"';} else {echo 'disabled';}?> /> I want to <em class="em_ul_style">enter an income-driven plan</em> - Continue to Item 2.</div>
							<div class="form-check"><input type="radio" class="form-check-input" value="2" <?php if ($radio[1] == "2") {echo ' checked="checked"';} else {echo 'disabled';}?> /> I am submitting documentation for the <em class="em_ul_style">annual recertification</em> of my income-driven payment - Skip to Item 3.</div>
							<div class="form-check"><input type="radio" class="form-check-input" value="3" <?php if ($radio[1] == "3") {echo ' checked="checked"';} else {echo 'disabled';}?> /> I am submitting documentation early to have my income-driven <em class="em_ul_style">payment recalculated immediately</em> - Skip to Item 3.</div>
							<div class="form-check"><input type="radio" class="form-check-input" value="4" <?php if ($radio[1] == "4") {echo ' checked="checked"';} else {echo 'disabled';}?> /> I want to <em class="em_ul_style">change to a different income-driven plan</em> - Continue to Item 2.</div>
						</div>
					</div>
					<div class="col-md-6" id="div_radio_2">
						<div class="mb-3 mt-3"><label for="email" class="form-label">2. Choose a plan and then continue to Item 3.</label>
							<div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[2] == "1") {echo ' checked="checked"';} else {echo 'disabled';}?> /> (Recommended) I want the income-driven repayment plan with the lowest monthly payment.</div>
							<div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[2] == "2") {echo ' checked="checked"';} else {echo 'disabled';}?> /> SAVE (formerly known as REPAYE)</div>
							<div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[2] == "3") {echo ' checked="checked"';} else {echo 'disabled';}?> /> IBR</div>
							<div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[2] == "4") {echo ' checked="checked"';} else {echo 'disabled';}?> /> PAYE</div>
							<div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[2] == "5") {echo ' checked="checked"';} else {echo 'disabled';}?> /> ICR</div>
						</div>
					</div>
					<div class="clr"></div>
					<div class="col-md-6" id="div_radio_3">
						<div class="mb-3 mt-3"><label for="email" class="form-label">3. Do you have multiple loan holders or servicers?</label>
							<div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[3] == "1") {echo ' checked="checked"';} else {echo 'disabled';}?> /> Yes - Submit a request to each holder or servicer. Continue to Item 4.</div>
							<div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[3] == "2") {echo ' checked="checked"';} else {echo 'disabled';}?> /> No - Continue to Item 4.</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="mb-3 mt-3"><label for="email" class="form-label">4. Are you currently in deferment or forbearance?<br />After answering, continue to Item 5.</label>
							<div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[4] == "1") {echo ' checked="checked"';} else {echo 'disabled';}?> /> No.</div>
							<div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[4] == "2") {echo ' checked="checked"';} else {echo 'disabled';}?> /> Yes, but I want to start making payments under my plan immediately.</div>
							<div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[4] == "3") {echo ' checked="checked"';} else {echo 'disabled';}?> /> Yes, and I do not want to start repaying my loans until the deferment or forbearance ends.</div>
						</div>
					</div>
					<div class="clr"></div>
					<div class="col-md-12">
						<h4 style="margin-bottom:0px;"><strong>SECTION 3: FAMILY SIZE INFORMATION</strong></h4>
						<hr style="margin-top:5px;" />
					</div>
					<div class="col-md-6">
						<div class="mb-3 mt-3"><label for="email" class="form-label">5. How many children, including unborn children, are in your family and receive more than half of their support from you?</label><input type="text" class="form-control" value="<?php echo $ansintkR['19']['intake_comment_body']; ?>" /></div>
					</div>
					<div class="col-md-6">
						<div class="mb-3 mt-3"><label for="email" class="form-label">6. How many other people, excluding your spouse and children, live with you and receive more than half of their support from you?</label><input type="text" class="form-control" value="<?php echo $ansintkR['20']['intake_comment_body']; ?>" /></div>
					</div>
					<div class="clr"></div>
					<div class="col-md-12">
						<h4 style="margin-bottom:0px;"><strong>SECTION 4A: MARITAL STATUS INFORMATION</strong></h4>
						<hr style="margin-top:5px;" />
					</div>
					<div class="col-md-6">
						<div class="mb-3 mt-3"><label for="email" class="form-label">7. What is your marital status?</label>
							<div class="form-check"><input type="radio" class="form-check-input" <?php if ($marital_status == ($a + 14)) {echo ' checked="checked"';} else {echo 'disabled';}?> /> Single - Skip to Item 11.</div>
							<div class="form-check"><input type="radio" class="form-check-input" <?php if ($marital_status == ($a + 15)) {echo ' checked="checked"';} else {echo 'disabled';}?> /> Married - Continue to Item 8.</div>
							<div class="form-check"><input type="radio" class="form-check-input" <?php if ($marital_status == ($a2 + 72)) {echo ' checked="checked"';} else {echo 'disabled';}?> /> Married, but separated - You will be treated as single. Skip to Item 11.</div>
							<div class="form-check"><input type="radio" class="form-check-input" <?php if ($marital_status == ($a2 + 73)) {echo ' checked="checked"';} else {echo 'disabled';}?> /> Married, but cannot reasonably access my spouse's income information - You will be treated as single. Skip to Item 11.</div>
						</div>
					</div>
					<div class="col-md-6" id="div_radio_8">
						<div class="mb-3 mt-3"><label for="email" class="form-label">8. Does your spouse have federal student loans?</label>
							<div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[8] == "1") {echo ' checked="checked"';} else {echo 'disabled';}?> /> Yes - Continue to Item 9.</div>
							<div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[8] == "2") {echo ' checked="checked"';} else {echo 'disabled';}?> /> No - Skip to Item 10.</div>
						</div>
					</div>
					<div class="clr"></div>
					<div class="col-md-6" id="div_radio_9">
						<div class="mb-3 mt-3"><label for="email" class="form-label">9. Provide the following information about your spouse and then continue to Item 10:</label>
							<div class="mb-3 mt-3"><label for="email" class="form-label">a. Spouse's SSN</label><input type="text" class="form-control" <?php if ($radio[8] == "1") {echo ' name="inputr[user][spouse_ssn]"';} else {echo 'disabled';}?> value="
								<?php echo $fd['spouse_ssn']; ?>" /></div>
							<div class="mb-3 mt-3"><label for="email" class="form-label">b. Spouse's Name</label><input type="text" class="form-control" <?php if ($radio[8] == "1") {echo ' name="inputr[user][spouse_name]"';} else {echo 'disabled';}?> value="
								<?php echo $fd['spouse_name']; ?>" /></div>
							<div class="mb-3 mt-3"><label for="email" class="form-label">c. Spouse's Date of Birth</label><input type="date" class="form-control" <?php if ($radio[8] == "1") {echo ' name="inputr[user][spouse_dob]"';} else {echo 'disabled';}?> value="
								<?php echo $fd['spouse_dob']; ?>" /></div>
						</div>
					</div>
					<div class="col-md-6" id="div_radio_10">
						<div class="mb-3 mt-3"><label for="email" class="form-label">10. When you filed your last federal income tax return, did you file jointly with your spouse?</label>
							<div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[10] == "1") {echo ' checked="checked"';} else {echo 'disabled';}?> /> Yes - Continue to Item 13.</div>
							<div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[10] == "2") {echo ' checked="checked"';} else {echo 'disabled';}?> /> No - Continue to Item 11.</div>
						</div>
					</div>
					<div class="clr"></div>
					<div id="section_4b">
						<div class="col-md-12">
							<h4 style="margin-bottom:0px;"><strong>SECTION 4B: INCOME INFORMATION FOR SINGLE BORROWERS AND MARRIED BORROWERS TREATED AS SINGLE</strong></h4>
							<hr style="margin-top:5px;" />
						</div>
						<div class="col-md-6">
							<div class="mb-3 mt-3"><label for="email" class="form-label">11. Has your income significantly decreased, or your marital status changed since you filed your last federal income tax return?<br />
									For example, have you lost your job, experienced a drop in income, or gotten divorced, or did you most recently file a joint return with your spouse, but you have since become separated. </label>
								<div class="form-check"><input type="radio" class="form-check-input" <?php if ($marital_status == ($a + 15) && $radio[10] == '1') {echo 'disabled';} else {if ($radio[11] == "1") {echo ' checked="checked"';} else {echo 'disabled';}}?> /> Yes - Continue to Item 12.</div>
								<div class="form-check"><input type="radio" class="form-check-input" <?php if ($marital_status == ($a + 15) && $radio[10] == '1') {echo 'disabled';} else {if ($radio[11] == "2") {echo ' checked="checked"';} else {echo 'disabled';}}?> /> No - Provide your most recent federal income tax return or transcript. Skip to Section 6.</div>
								<div class="form-check"><input type="radio" class="form-check-input" <?php if ($marital_status == ($a + 15) && $radio[10] == '1') {echo 'disabled';} else {if ($radio[11] == "3") {echo ' checked="checked"';} else {echo 'disabled';}}?> /> I haven't filed a federal income tax return in the last two years - Continue to Item 12.</div>
							</div>
						</div>
						<div class="col-md-6" id="div_radio_12">
							<div class="mb-3 mt-3"><label for="email" class="form-label">12. Do you currently have taxable income?<br />Check "No" if you do not have any income or receive only untaxed income.</label>
								<div class="form-check"><input type="radio" class="form-check-input" <?php if ($marital_status == ($a + 15) && $radio[10] == '1') {echo 'disabled';} else {if ($radio[12] == "1") {echo ' checked="checked"';} else {echo 'disabled';}}?> /> Yes - Provide documentation of your income as instructed in Section 5. Skip to that section</div>
								<div class="form-check"><input type="radio" class="form-check-input" <?php if ($marital_status == ($a + 15) && $radio[10] == '1') {echo 'disabled';} else {if ($radio[12] == "2") {echo ' checked="checked"';} else {echo 'disabled';}}?> /> No - You are not required to provide documentation of your income. Skip to Section 6.</div>
							</div>
						</div>
					</div>
					<div class="clr"></div>
					<div id="section_4c">
						<div class="col-md-12">
							<h4 style="margin-bottom:0px;"><strong>SECTION 4C: INCOME INFORMATION FOR MARRIED BORROWERS FILING JOINTLY</strong></h4>
							<hr style="margin-top:5px;" />
						</div>
						<div class="col-md-6" id="div_radio_13">
							<div class="mb-3 mt-3"><label for="email" class="form-label">13. Has your income significantly decreased since you filed your last federal income tax return?<br />For example, have you lost your job or experienced a drop in income?</label>
								<div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[13] == "1") {echo ' checked="checked"';} else {echo 'disabled';}?> onChange="check_question_13(this.value)" /> Yes - Skip to Item 15.</div>
								<div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[13] == "2") {echo ' checked="checked"';} else {echo 'disabled';}?> onChange="check_question_13(this.value)" /> No - Continue to Item 14.</div>
								<div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[13] == "3") {echo ' checked="checked"';} else {echo 'disabled';}?> onChange="check_question_13(this.value)" /> We haven't filed a federal income tax return in the last two years - Skip to Item 15.</div>
							</div>
						</div>
						<div class="col-md-6" id="div_radio_14">
							<div class="mb-3 mt-3"><label for="email" class="form-label">14. Has your spouse's income significantly decreased since your spouse filed his or her last federal income tax return?<br />For example, has your spouse lost his or her job or experienced a drop in income?</label>
								<div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[14] == "1") {echo ' checked="checked"';} else {echo 'disabled';}?> onChange="check_question_14(this.value)" /> Yes - Continue to Item 15.</div>
								<div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[14] == "2") {echo ' checked="checked"';} else {echo 'disabled';}?> onChange="check_question_14(this.value)" /> No - Provide your and your spouse's most recent federal income tax return or transcript. Skip to Section 6</div>
							</div>
						</div>
						<div class="clr"></div>
						<div class="col-md-6" id="div_radio_15">
							<div class="mb-3 mt-3"><label for="email" class="form-label">15. Do you currently have taxable income?<br /> Check "No" if you do not have any income or receive only untaxed income.</label>
								<div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[15] == "1") {echo ' checked="checked"';} else {echo 'disabled';}?> onChange="check_question_15(this.value)" /> Yes - You must provide documentation of your income according to the instructions in Section 5. Continue to Item 16.</div>
								<div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[15] == "2") {echo ' checked="checked"';} else {echo 'disabled';}?> onChange="check_question_15(this.value)" /> No - You are not required to provide documentation of your income. Continue to Item 16.</div>
							</div>
						</div>
						<div class="col-md-6" id="div_radio_16">
							<div class="mb-3 mt-3"><label for="email" class="form-label">16. Does your spouse currently have taxable income?<br />Check "No" if your spouse does not have any income or receives only untaxed income.</label>
								<div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[16] == "1") {echo ' checked="checked"';} else {echo 'disabled';}?> onChange="check_question_16(this.value)" /> Yes - Skip to Section 5 and provide documentation of your spouse's income as instructed in that section.</div>
								<div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[16] == "2") {echo ' checked="checked"';} else {echo 'disabled';}?> onChange="check_question_16(this.value)" /> No - You are not required to provide documentation of your spouse's income. If you selected "Yes" to Item 15, skip to Section 5 and document your income. If you selected "No" to Item 15, skip to Section 6.</div>
							</div>
						</div>
					</div>
					<div class="clr"></div>
					<div id="section_5a">
						<div class="col-md-12">
							<h4 style="margin-bottom:0px;"><strong>SECTION 5A: AUTHORIZATION TO RETRIEVE FEDERAL TAX INFORMATION FROM THE IRS</strong></h4>
							<hr style="margin-top:5px;" />
						</div>
						<div class="col-md-6" id="div_radio_consent">
							<div class="mb-3 mt-3">
								<label for="email" class="form-label">By checking ‘I approve, consent, and agree’ below, I consent to, affirmatively approve of, and agree to, as applicable, the following:</label>
								<ol style="margin:0px; padding:5px 0px 10px 20px;">
									<li>The U.S. Department of Education may disclose my Social Security number (SSN)/Taxpayer IdentificationNumber (TIN), last name, and date of birth that I provided in Section 1 (Borrower Information) of this form, as well as my unique identifier and the tax year for which FTI is required, to the IRS for the U.S. Department of Education to receive my FTI for the purpose of, and to the extent necessary in, determining my eligibility for, or repayment obligations under, IDR plans as authorized under part D of title IV of the Higher Education Act of 1965, as amended, as described in 26 U.S.C. § 6103(l)(13)(A)</li>
									<li>The U.S. Department of Education may use my FTI on an annual basis for the purposes of determining my eligibility for, and repayment obligations under, a qualifying IDR plan until I fulfill my repayment obligations under an IDR plan, withdraw from my IDR plan, or, as described below, revoke my approval and consent; and</li>
									<li>The U.S. Department of Education may automatically execute the recertification of eligibility determination and repayment obligations for a qualifying IDR plan on an annual basis until I fulfill my repayment obligations under an IDR plan, withdraw from my IDR plan, or, as described below, revoke my approval and consent</li>
								</ol>
							</div>
						</div>
						<div class="col-md-6" id="div_radio_consent2">
							<div class="mb-3 mt-3">
								<label for="email" class="form-label">By checking ‘I approve, consent, and agree’ below, I further understand that:</label>
								<ol style="margin:0px; padding:5px 0px 10px 20px;">
	                                <li>During recertification, my eligibility and monthly payment amount for a previously approved IDR plan may change based on the FTI that the U.S. Department of Education receives from the IRS when my IDR plan is automatically recertified on annual basis;</li>
	                                <li>I am also providing my written consent for the redisclosure of my FTI by the U.S. Department of Education to the Office of Inspector General of the U.S. Department of Education for audit purposes, as described in 26 U.S.C. § 6103(l)(13)(D)(iv); and</li>
	                                <li>I may revoke my consent for the disclosure of the SSN/TIN, last name, and date of birth information that I provided in Section 1 (Borrower Information) of this form, as well as my unique identifier and the tax year for which FTI is required, and my affirmative approval for the receipt and use of my FTI by the U.S. Department of Education within the user settings of my account at StudentAid.gov. (You must be logged into your account with your FSA ID in order to revoke approval and consent.) However, by revoking my affirmative approval and consent, I understand and acknowledge that the U.S. Department of Education will be unable to automatically determine my eligibility for, and repayment obligations under, an IDR plan on an annual basis, and will require that I, and my spouse (if applicable), provide alternative documentation of income on an annual basis if I wish to continue participating in an IDR plan.</li>
	                            </ol>
							</div>
						</div>
						<div class="clr"></div>
						<div class="col-md-12">
							<div class="mb-3 mt-3">
								<div class="form-check"><input type="radio" name="consent" value="5a" class="form-check-input" <?php if ($idr_formdata['consent'] == "5a") {echo ' checked="checked"';}?> checked/> I <strong>APPROVE, CONSENT</strong>, and <strong>AGREE</strong> and certify under penalty of perjury under the laws of the United States of America, that the foregoing is true and correct, and that I am the person named in Section 1 (Borrower Information) of this form providing consent to disclose and authorize the disclosure of my records, as set forth above. I further authorize the disclosure of my personally identifiable information, as outlined above, to the IRS for ED to receive my FTI for purposes of determining my eligibility for, or repayment obligations under, an IDR plan request. I understand that any falsification of this statement is punishable under the provisions of 18 U.S.C. § 1001 by a fine, imprisonment of not more than five years, or both, and that the knowing and willful request for or acquisition of a record pertaining to an individual under false pretenses is a criminal offense under the Privacy Act of 1974, as amended, subject to a fine of not more than $5,000 (5 U.S.C. § 552a(i)(3)). <strong>(Skip to Section 6)</strong></div>
								<div class="form-check"><input type="radio" name="consent" value="5b" class="form-check-input" <?php if ($idr_formdata['consent'] == "5b") {echo ' checked="checked"';}?> checked/> I <strong> DO NOT</strong> approve, consent, and agree to the disclosure of my information to the IRS for the U.S. Department of Education to receive my FTI, as described above. <strong>(Continue to Section 5B).</strong></div>
							</div>
						</div>
					</div>
					<div id="section_6">
						<div class="col-md-12">
							<h4 style="margin-bottom:0px;"><strong>SECTION 6: BORROWER REQUESTS, UNDERSTANDINGS, AUTHORIZATION AND CERTIFICATION</strong></h4>
							<hr style="margin-top:5px;" />
						</div>
						<div class="col-md-12">
							<div class="mb-3 mt-3">
								If I am requesting an income-driven repayment plan or seeking to change income-driven repayment plans, I request:<br />

	                            <div class="clr mb_5"></div>

	                            <ul style="margin:0px; padding:5px 0px 10px 20px;">
	                                <li>That my loan holder place me on the plan I selected in Section 2 to repay my eligible Direct Loan or FFEL Program loans held by the holder to which I submit this form.</li>
	                                <li>If I do not qualify for the plan or plans I requested, or did not make a selection in Item 2, that my loan holder place me on the plan with the lowest monthly payment amount.</li>
	                                <li>If I selected more than one plan, that my loan holder place me on the plan with the lowest monthly payment amount from the plans that I requested.</li>
	                                <li>If more than one of the plans that I selected provides the same initial payment amount, or if my loan holder is determining which of the income-driven plans I qualify for, that my loan holder use the following order in choosing my plan: SAVE (if my repayment period is 20 years), PAYE, SAVE (if my repayment period is 25 years), IBR, and then ICR.</li>
	                            </ul>
	                            <div class="clr mb_15"></div>
	                            If I am not currently on an income-driven repayment plan, but I did not complete Item 1 or I incorrectly indicated in Item 1that I was already in an income-driven repayment plan, I request that my loan holder treat my request as if I had indicated in Item 1 that I wanted to enter an income-driven repayment plan.
	                            <p>If I am currently repaying my Direct Loans under the IBR plan and I am requesting a change to a different income-driven plan, I request a one-month reduced-payment forbearance in the amount of my current monthly IBR payment or $5, whichever is greater (unless I request another amount below or I decline the forbearance), to help me move from IBR to the new income-driven plan I requested.</p>
							</div>
						</div>
						<div class="clr"></div>
						<div class="col-md-12">
							<div class="mb-3 mt-3">
								<div class="form-check"><input type="radio" name="requested_reduced_payment_forbearance" value="1" class="form-check-input" <?php if ($idr_formdata['requested_reduced_payment_forbearance'] == "1") {echo ' checked="checked"';}?> /> <strong>I request</strong> a one-month reduced-payment forbearance in the amount of: &nbsp; &nbsp; &nbsp; <input type="text" class="form-control" name="reduced_payment_forbearance" style="width:auto;display: inline-block;" value="<?=$idr_formdata['reduced_payment_forbearance']?>" checked/> (must be at least $5).</div>
							</div>
						</div>
					</div>
					<!--
<div class="clr"></div>
<div id="section_4d">
<div class="col-md-12"><h4 style="margin-bottom:0px;"><strong>SECTION 4D: INCOME INFORMATION FOR MARRIED BORROWERS FILING SEPARATELY</strong></h4><hr style="margin-top:5px;" /></div>

<div class="col-md-6" id="div_radio_17"><div class="mb-3 mt-3"><label for="email" class="form-label">17. Has your income significantly decreased since you filed your last federal income tax return?<br />For example, have you lost your job or experienced a drop in income?</label>

	<div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[17] == "1") {echo ' checked="checked"';} else {echo 'disabled';}?> onChange="check_question_17(this.value)" />  Yes - Continue to Item 18.</div>
        <div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[17] == "2") {echo ' checked="checked"';} else {echo 'disabled';}?> onChange="check_question_17(this.value)" />  No - Provide your most recent federal income tax return or transcript. Skip to Item 19.</div>
        <div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[17] == "3") {echo ' checked="checked"';} else {echo 'disabled';}?> onChange="check_question_17(this.value)" />  I haven't filed a federal income tax return in the past two years - Continue to Item 18.</div>
</div></div>



<div class="col-md-6" id="div_radio_18"><div class="mb-3 mt-3"><label for="email" class="form-label">18.  Do you currently have taxable income?<br />Check "No" if you have no taxable income or receive only untaxed income. After answering, continue to Item 19.</label>

	<div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[18] == "1") {echo ' checked="checked"';} else {echo 'disabled';}?> />  Yes - You must provide documentation of your income as instructed in Section 5</div>
        <div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[18] == "2") {echo ' checked="checked"';} else {echo 'disabled';}?> />  No.</div>
</div></div>
<div class="clr"></div>


<div class="col-md-6 <?php echo $idr_disp_19; ?>" id="div_radio_19"><div class="mb-3 mt-3"><label for="email" class="form-label">19. Has your spouse's income significantly decreased since your spouse filed his or her last federal income tax return?<br />For example, has your spouse lost a job or experienced a drop in income?</label>

	<div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[19] == "1") {echo ' checked="checked"';} else {echo 'disabled';}?> />  Yes - Continue to Item 20.</div>
        <div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[19] == "2") {echo ' checked="checked"';} else {echo 'disabled';}?> />  No - Provide your spouse's most recent federal income tax return or transcript. This information will only be used if you are on or placed on the SAVE Plan. Skip to Section 6.</div>
        <div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[19] == "3") {echo ' checked="checked"';} else {echo 'disabled';}?> />  My spouse hasn't filed a federal income tax return in the past two years - Continue to Item 20.</div>
</div></div>



<div class="col-md-6" id="div_radio_20"><div class="mb-3 mt-3"><label for="email" class="form-label">20. Does your spouse currently have taxable income?<br />Check "No" if your spouse has no taxable income or receives only untaxed income.</label>

	<div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[20] == "1") {echo ' checked="checked"';} else {echo 'disabled';}?> />  Yes - Skip to Section 5 and provide documentation of your spouse's income as instructed in that section. This information will only be used if you are on or placed on the SAVE Plan.</div>
        <div class="form-check"><input type="radio" class="form-check-input" <?php if ($radio[20] == "2") {echo ' checked="checked"';} else {echo 'disabled';}?> />  No - You are not required to provide documentation of your spouse's income. If you selected "Yes" to Item 18, skip to Section 5 and document your income. If you selected "No" to Item 18, skip to Section 6.</div>
</div></div>
</div> -->
					<div class="clr"></div>
					<div class="clr"></div>
				</div>
			</div>
			<?php
}
		?>
		</div>
	</form>
	</div>
	<?php	$this->load->view("Admin/inc/template_js.php");?>
</body>

</html>
<?php

	}
}
?>