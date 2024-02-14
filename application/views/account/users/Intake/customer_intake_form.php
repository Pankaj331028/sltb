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

		foreach ($this->array_model->arr_intake_program_id() as $k => $v) {if ($v == $intake_id) {$program_id_primary = $k;}}

		$q = $this->db->query("SELECT * FROM intake where intake_id='$intake_id'");
		$intkR = $q->row_array();

// Submit Data
		if (isset($_POST['Submit_save']) || isset($_POST['Submit_approve'])) {
			$error = "";
			if ($intkR['intake_title'] == "Consolidation Intake") {
				$address = $_POST['inputr']['ansR']['5']['intake_comment_body'];
				$ref_address_1 = $_POST['inputr']['ansR']['23']['intake_comment_body'];
				$ref_address_2 = $_POST['inputr']['ansR']['33']['intake_comment_body'];

				$ref_phone_1 = $_POST['inputr']['ansR']['27']['intake_comment_body'];
				$ref_phone_2 = $_POST['inputr']['ansR']['37']['intake_comment_body'];

				$ref_email_1 = $_POST['inputr']['ansR']['28']['intake_comment_body'];
				$ref_email_2 = $_POST['inputr']['ansR']['38']['intake_comment_body'];

				if (trim($ref_address_1) != "") {
					if ($ref_address_1 == $ref_address_2) {$error .= "Reference person 1 and 2 can not live at the same address<br />";}
					if ($ref_address_1 == $address) {$error .= "Reference person 1 and you can not live at the same address<br />";}
				}
				if (trim($ref_phone_1) != "") {if ($ref_phone_1 == $ref_phone_2) {$error .= "Reference person 1 and 2 can not have the same phone number<br />";}}
				if (trim($ref_email_1) != "") {if ($ref_email_1 == $ref_email_2) {$error .= "Reference person 1 and 2 can not have the same e-mail<br />";}}

				if (trim($ref_address_2) != "") {if ($ref_address_2 == $address) {$error .= "Reference person 2 and you can not live at the same address<br />";}}

			}

			$ssn_validation_regex = '/^(?!666|000|9\\d{2})\\d{3}-(?!00)\\d{2}-(?!0{4})\\d{4}$/';
			$rss = preg_match($ssn_validation_regex, $_POST['inputr']['ansR']['3']['intake_comment_body']); // returns 1
			if ($rss != '1') {$error .= "Please enter valid <em>SSN</em>" . "<br />";}

			if (isset($_POST['inputr']['user']['spouse_ssn'])) {
				if (trim($_POST['inputr']['user']['spouse_ssn']) != "") {
					$rss = preg_match($ssn_validation_regex, $_POST['inputr']['user']['spouse_ssn']); // returns 1
					if ($rss != '1') {$error .= "Please enter valid <em>Spouse SSN</em>" . "<br />";}
				}
			}

			if ($error == "") {
				$form_data = json_encode($_POST);
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
		$program_id_primary = $cpp['program_id_primary'];

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
				//redirect(base_url('account/intake_form_document/'.$client_id.'/'.$cdr['document_id']."/".$docname));
				//exit;
			}
		}

		$file_name = $user['lname'] . " " . $user['name'] . " " . str_replace("Intake", "", $intkR['intake_title']) . " " . date('Y-m-d', strtotime($ics['add_date'])) . "-Internal.pdf";
		$intake_question_data = $this->default_model->get_arrby_tbl('intake_question', '*', "intake_id='1' order by placement_order asc", '500');
		foreach ($intake_question_data as $row) {
			$intake_question_id = $row['intake_question_id'];
			$placement_order = $row['placement_order'];
			$ansintkR[$placement_order] = $this->crm_model->admin_intake_answer_by_client($client_id, $intake_question_id);
		}

		$intake_question_data = $this->default_model->get_arrby_tbl('intake_question', '*', "intake_id='" . $intake_id . "' order by placement_order asc", '500');
		foreach ($intake_question_data as $row) {
			$intake_question_id = $row['intake_question_id'];
			$placement_order = $row['placement_order'];
			$ansR[$placement_order] = $this->crm_model->admin_intake_answer_by_client($client_id, $intake_question_id);
		}

		$ansR['permanent_address'] = $ansR[5]['intake_comment_body'] . ", " . $ansR[6]['intake_comment_body'] . ", " . $ansR[7]['intake_comment_body'] . ", " . $ansR[8]['intake_comment_body'];
		$ansR['dl_state_and_number'] = $ansR[11]['intake_comment_body'] . " " . $ansR[12]['intake_comment_body'];

		$ansR['employee_address'] = $ansR[14]['intake_comment_body'] . ", " . $ansR[15]['intake_comment_body'] . ", " . $ansR[16]['intake_comment_body'] . ", " . $ansR[17]['intake_comment_body'] . ", " . $ansR[18]['intake_comment_body'];

		$ansR['reference_permanent_address'] = $ansR[23]['intake_comment_body'] . ", " . $ansR[24]['intake_comment_body'] . ", " . $ansR[25]['intake_comment_body'] . ", " . $ansR[26]['intake_comment_body'];

		$ansR['reference_permanent_address_2'] = $ansR[33]['intake_comment_body'] . ", " . $ansR[34]['intake_comment_body'] . ", " . $ansR[35]['intake_comment_body'] . ", " . $ansR[36]['intake_comment_body'];

		$idr['name'] = $user['name'] . " " . $ansR[1]['intake_comment_body'] . " " . $user['lname'];

		$grace_period_end_date = "";

		$data_found = "";
		if ($ics['form_data'] != "") {
			$form_data = json_decode($ics['form_data'], true);
			if (isset($form_data['inputr'])) {
				@extract($form_data['inputr']);
				$data_found = "Yes";}
		}

		$marital_status = $car['marital_status'];
		$include_in_client_report = $car['include_in_client_report'];
		$scenario_selected = $car['scenario_selected'];
		$payment_plan_selected = $car['payment_plan_selected'];
		$file_joint_or_separate = $car['file_joint_or_separate'];
		$car_icr = json_decode($car['include_in_client_report'], true);

		$scenario_monthly = 0;
		if (trim($car['client_monthly'])) {$scenario_monthly += ($car['client_monthly'] + 0);}
		if (trim($car['spouse_monthly'])) {$scenario_monthly += ($car['spouse_monthly'] + 0);}

		if ($intkR['intake_title'] == "IDR Intake" || $intkR['intake_title'] == "Consolidation Intake") {$radio[1] = 1;}
		if ($intkR['intake_title'] == "Recertification Intake") {$radio[1] = 2;}
		if ($intkR['intake_title'] == "Recalculation Intake") {$radio[1] = 3;}
		if ($intkR['intake_title'] != "IDR Intake" && $intkR['intake_title'] != "Consolidation Intake" && $intkR['intake_title'] != "Recertification Intake" && $intkR['intake_title'] != "Recalculation Intake") {$radio[1] = 4;}

		if ($payment_plan_selected == "10-Year Standard" || $payment_plan_selected == "25-Year Extended") {$radio[2] = 1;} else if ($payment_plan_selected == "REPAYE") {$radio[2] = 2;} else if ($payment_plan_selected == "IBR" || $payment_plan_selected == "New IBR") {$radio[2] = 3;} else if ($payment_plan_selected == "PAYE") {$radio[2] = 4;} else if ($payment_plan_selected == "ICR") {$radio[2] = 5;} else { $radio[2] = 1;}

		if ($car['deferment_forbearance_status'] == "0") {$radio[4] = 1;}
		if ($car['deferment_forbearance_status'] == "1") {$radio[4] = 2;}
		if ($car['deferment_forbearance_status'] == "2") {$radio[4] = 3;}

		if ($ansintkR[12]['intake_answer_id'] == "16") {$radio[8] = 1;}
		if ($ansintkR[12]['intake_answer_id'] == "17") {$radio[8] = 2;}

		if ($file_joint_or_separate == "Joint") {$radio[10] = 1;}
		if ($file_joint_or_separate != "Joint") {$radio[10] = 2;}
		$radio[10] = "";

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

//

		$sql = "SELECT distinct(loan_contact_name) as loan_contact_name FROM nslds_loans where client_id='$client_id' and (loan_outstanding_principal_balance>'0' or loan_outstanding_interest_balance>'0')";
		$q = $this->db->query($sql);
		$ln_num = $q->num_rows();
		if ($ln_num > 1) {$radio[3] = 1;}
		if ($ln_num <= 1) {$radio[3] = 2;}

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
padding:0px;
color: #000000;
font-family:Calibri, sans-serif;
}

* {
  box-sizing: border-box;
  -moz-box-sizing: border-box;
}
strong { font-weight:bold; }

.clr { clear:both; height:0px;}
.disp_none { display:none;}
.font-10 { font-size:10px;}
.font-13 { font-size:13px;}
.font-15 { font-size:15px;}

.mb_3 { margin-bottom:3px;}
.mb_5 { margin-bottom:5px;}
.mb_7 { margin-bottom:7px;}
.mb_8 { margin-bottom:8px;}
.mb_10 { margin-bottom:10px;}
.mb_15, .mb-3 { margin-bottom:15px;}
.mb_20 { margin-bottom:20px;}

.mb_20_ { margin-bottom:-20px;}
.mb_25_ { margin-bottom:-25px;}

.pb_5 { padding-bottom:5px;}

.bg_1 { background:#f1f4ff; color:#777777; }
.style_1 {color: black; font-family: Calibri, sans-serif; font-style: normal; font-weight:normal; text-decoration: none; font-size:8pt; line-height:10pt;}
.em_ul_style { text-decoration:underline; font-style:normal;}
.line_border_1 { width:100%; height:2px; margin:5px 0px; background:#000000;}
.line_border_2 { width:100%; height:1px; margin:5px 0px; background:#000000;}

.input_td { background:#f1f4ff; color:#777777; font-size:13px; padding:3px 5px; border-bottom:1px solid #888888; }
.input_div_2 {width:auto; height:13px; font-size:12px; color:#000000; border-bottom:1px solid #666666; padding:2px 5px;}
.input_2 {width:100%; height:15px; padding:2px 2px 2px 2px; background:#f1f4ff; font-size:13px; border:none; border-bottom:1px solid #000000;}
.input_3 {width:100%; height:15px; padding:2px 2px 2px 2px; background:#f1f4ff; font-size:13px; border:none;}
.input_4 {width:100%; height:16px; padding:2px 2px 1px 2px; background:#f1f4ff; font-size:11px; border:none; border-bottom:1px solid #000000;}


.page_wrapper { width:100%; margin:0 auto; overflow:hidden; }
.mrgn_consolidation { margin:0; }
.mrgn_idr { margin:0; }
.pagebreak { display: block; height:0px; clear: both; page-break-after: always; border:1px solid #CCCCCC; }
.pagging_text { font-size:9px; text-align:center; }
.pagging_text_1 { font-size:10px; text-align:right; margin-top:-20px; font-weight:bold; }



</style>
</head>
<body style="padding-bottom:25px;">



<form action="" method="post" enctype="multipart/form-data">
<?php
$fi = 1;
		if ($intkR['intake_title'] == "IDR Intake" || $intkR['intake_title'] == "Consolidation Intake") {
			$ssn = explode("-", $ansR[3]['intake_comment_body']);
			?>
<div style="position:fixed; top:0px; left:0px; width:100%; height:60px; background:#F8F8F8; z-index:9999;">
<div class="container">

<div class="row">
	<div class="col-md-7">
    	<h3 style="margin:5px 0 0 0px;"><strong>Data Confirmation <!--for the <?php //echo $intkR['intake_title']; ?>--></strong></h3>
        <?php if ($cpp['step_completed_date'] == '' || $ics['status2'] != 'Approved') {} else {?><span style="font-size:14px; color:#009900;"><i class="fa fa-check-square-o" aria-hidden="true"></i> Already Approved</span><?php }?>
    </div>
    <div class="col-md-5">

<div class="text-right" style="margin:10px 0 0 0;">
<!--<a href="javascript:void(0)" class="btn btn-warning" onClick="printDiv('<?php echo $print_div_id; ?>')"><i class="fa fa-print"></i> Print</a> &nbsp; -->

<?php //if($ics['status2']=="Pending"){ ?><button type="submit" name="Submit_save" class="btn btn-success"><i class="fa fa-floppy-o" aria-hidden="true"></i> Save</button> &nbsp; <?php //} ?>

<?php //if($cpp['step_completed_date']=='' || $cpp['status']!='Complete') { ?><button type="submit" name="Submit_approve" class="btn btn-primary"><i class="fa fa-check-square-o" aria-hidden="true"></i> Approve</button><?php //} ?>

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
<div class="alert" style="background:#dff0d8; border:#dff0d8; color:#3c763d;"><strong>Note:</strong> To make corrections to the IDR, return to <a href="<?php echo base_url($sg_1 . "/customer/current_analysis/" . $client_id); ?>" style="color:#337ab7;">the Analysis</a> page and make your updates including possible payment plan calculation changes there. Then return to this page for it to be updated.</div>
<?php }?>

<div id="<?php echo $print_div_id; ?>">

<div><?php	$this->load->view("template/alert.php");?></div>


<div class="row">

<div class="col-md-12"><span>Please enter or correct the following information.</span></div>

<div>
<div class="col-md-12"><h4 style="margin-bottom:0px;"><strong>Section 1: Borrower Information</strong></h4><hr style="margin-top:5px;" /></div>

<div class="col-md-12" style="display:none;"><div class="form-check mb-3">
<label class="form-check-label">
  <input class="form-check-input" type="checkbox" name="inputr[radio][idr_page_1_correct_info]" value="Yes" <?php if (isset($radio['idr_page_1_correct_info'])) {if ($radio['idr_page_1_correct_info'] == "Yes") {echo ' checked="checked"';}}?> /> Check this box if any of your information has changed.
</label>
</div></div>


<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Last Name: </label> <input type="text" class="form-control" name="inputr[user][lname]" value="<?php echo $user['lname']; ?>" required /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">First Name: </label> <input type="text" class="form-control" name="inputr[user][name]" value="<?php echo $user['name']; ?>" required /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Middle Initial: </label> <input type="text" class="form-control" name="inputr[ansR][1][intake_comment_body]" value="<?php echo $ansR[1]['intake_comment_body']; ?>" /></div></div>
<div class="clr"></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Former Name(s):</label>
<input type="text" class="form-control" name="inputr[ansR][2][intake_comment_body]" value="<?php echo $ansR[2]['intake_comment_body']; ?>" /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Date of Birth:</label>
<input type="date" class="form-control" name="inputr[ansR][4][intake_comment_body]" value="<?php echo $ansR[4]['intake_comment_body']; ?>" required /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">SSN:</label>
<input type="text" class="form-control" name="inputr[ansR][3][intake_comment_body]" value="<?php echo $ansR[3]['intake_comment_body']; ?>" required /></div></div>

<div class="clr"></div>






<div class="col-md-12"><div class="mb-3 mt-3"><label for="email" class="form-label">Street:</label>
<input type="text" class="form-control" name="inputr[ansR][5][intake_comment_body]" value="<?php echo $ansR[5]['intake_comment_body']; ?>" required /></div></div>


<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">City:</label>
<input type="text" class="form-control" name="inputr[ansR][6][intake_comment_body]" value="<?php echo $ansR[6]['intake_comment_body']; ?>" required /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">State:</label>
<input type="text" class="form-control" name="inputr[ansR][7][intake_comment_body]" value="<?php echo $ansR[7]['intake_comment_body']; ?>" required /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Zip Code:</label>
<input type="text" class="form-control" name="inputr[ansR][8][intake_comment_body]" value="<?php echo $ansR[8]['intake_comment_body']; ?>" required /></div></div>


<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Telephone - Primary:</label>
<input type="text" class="form-control" name="inputr[user][phone]" value="<?php echo $user['phone']; ?>" required /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Telephone - Alternate:</label>
<input type="text" class="form-control" name="inputr[user][telephone_alternate]" value="<?php echo $user['telephone_alternate']; ?>" /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Email (Optional):</label>
<input type="email" class="form-control" name="inputr[ansR][10][intake_comment_body]" value="<?php echo $ansR[10]['intake_comment_body']; ?>" /></div></div>
<div class="clr"></div>


<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Driver’s License State:</label>
<input type="text" class="form-control" name="inputr[ansR][11][intake_comment_body]" value="<?php echo $ansR[11]['intake_comment_body']; ?>" /></div></div>


<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Driver’s License Number:</label>
<input type="text" class="form-control" name="inputr[ansR][12][intake_comment_body]" value="<?php echo $ansR[12]['intake_comment_body']; ?>" /></div></div>
<div class="clr mb_20"></div>


<?php if ($intkR['intake_title'] == "Consolidation Intake") {?>

<div class="col-md-12"><h4 style="margin-bottom:0px;"><strong>EMPLOYER INFORMATION</strong></h4><hr style="margin-top:5px;" /></div>


<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Employer Name:</label>
<input type="text" class="form-control" name="inputr[ansR][14][intake_comment_body]" value="<?php echo $ansR[14]['intake_comment_body']; ?>" /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Employer Street:</label>
<input type="text" class="form-control" name="inputr[ansR][15][intake_comment_body]" value="<?php echo $ansR[15]['intake_comment_body']; ?>" /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Employer City:</label>
<input type="text" class="form-control" name="inputr[ansR][16][intake_comment_body]" value="<?php echo $ansR[16]['intake_comment_body']; ?>" /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Employer State:</label>
<input type="text" class="form-control" name="inputr[ansR][17][intake_comment_body]" value="<?php echo $ansR[17]['intake_comment_body']; ?>" /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Employer Zip Code:</label>
<input type="text" class="form-control" name="inputr[ansR][18][intake_comment_body]" value="<?php echo $ansR[18]['intake_comment_body']; ?>" /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Employer Phone Number:</label>
<input type="text" class="form-control" name="inputr[ansR][19][intake_comment_body]" value="<?php echo $ansR[19]['intake_comment_body']; ?>" /></div></div>

<div class="clr mb_20"></div>



<div class="col-md-12"><h4 style="margin-bottom:0px;"><strong>REFERENCE INFORMATION</strong></h4><hr style="margin-top:5px;" /></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Reference 1 first name:</label>
<input type="text" class="form-control" name="inputr[ansR][20][intake_comment_body]" value="<?php echo $ansR[20]['intake_comment_body']; ?>" /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Reference 1 middle initial:</label>
<input type="text" class="form-control" name="inputr[ansR][21][intake_comment_body]" value="<?php echo $ansR[21]['intake_comment_body']; ?>" /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Reference 1 last name:</label>
<input type="text" class="form-control" name="inputr[ansR][22][intake_comment_body]" value="<?php echo $ansR[22]['intake_comment_body']; ?>" /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Reference 1 Street:</label>
<input type="text" class="form-control" name="inputr[ansR][23][intake_comment_body]" value="<?php echo $ansR[23]['intake_comment_body']; ?>" /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Reference 1 City:</label>
<input type="text" class="form-control" name="inputr[ansR][24][intake_comment_body]" value="<?php echo $ansR[24]['intake_comment_body']; ?>" /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Reference 1 State:</label>
<input type="text" class="form-control" name="inputr[ansR][25][intake_comment_body]" value="<?php echo $ansR[25]['intake_comment_body']; ?>" /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Reference 1 Zip Code:</label>
<input type="text" class="form-control" name="inputr[ansR][26][intake_comment_body]" value="<?php echo $ansR[26]['intake_comment_body']; ?>" /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Reference 1 Phone Number:</label>
<input type="text" class="form-control" name="inputr[ansR][27][intake_comment_body]" value="<?php echo $ansR[27]['intake_comment_body']; ?>" /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Reference 1 e-mail:</label>
<input type="email" class="form-control" name="inputr[ansR][28][intake_comment_body]" value="<?php echo $ansR[28]['intake_comment_body']; ?>" /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Reference 1 Relationship to you:</label>
<input type="text" class="form-control" name="inputr[ansR][29][intake_comment_body]" value="<?php echo $ansR[29]['intake_comment_body']; ?>" /></div></div>

<div class="clr mb_20"></div>


<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Reference 2 first name:</label>
<input type="text" class="form-control" name="inputr[ansR][30][intake_comment_body]" value="<?php echo $ansR[30]['intake_comment_body']; ?>" /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Reference 2 middle initial:</label>
<input type="text" class="form-control" name="inputr[ansR][31][intake_comment_body]" value="<?php echo $ansR[31]['intake_comment_body']; ?>" /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Reference 2 last name:</label>
<input type="text" class="form-control" name="inputr[ansR][32][intake_comment_body]" value="<?php echo $ansR[32]['intake_comment_body']; ?>" /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Reference 2 Street:</label>
<input type="text" class="form-control" name="inputr[ansR][33][intake_comment_body]" value="<?php echo $ansR[33]['intake_comment_body']; ?>" /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Reference 2 City:</label>
<input type="text" class="form-control" name="inputr[ansR][34][intake_comment_body]" value="<?php echo $ansR[34]['intake_comment_body']; ?>" /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Reference 2 State:</label>
<input type="text" class="form-control" name="inputr[ansR][35][intake_comment_body]" value="<?php echo $ansR[35]['intake_comment_body']; ?>" /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Reference 2 Zip Code:</label>
<input type="text" class="form-control" name="inputr[ansR][36][intake_comment_body]" value="<?php echo $ansR[36]['intake_comment_body']; ?>" /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Reference 2 Phone Number:</label>
<input type="text" class="form-control" name="inputr[ansR][37][intake_comment_body]" value="<?php echo $ansR[37]['intake_comment_body']; ?>" /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Reference 2 e-mail:</label>
<input type="email" class="form-control" name="inputr[ansR][38][intake_comment_body]" value="<?php echo $ansR[38]['intake_comment_body']; ?>" /></div></div>

<div class="col-md-4"><div class="mb-3 mt-3"><label for="email" class="form-label">Reference 2 Relationship to you:</label>
<input type="text" class="form-control" name="inputr[ansR][39][intake_comment_body]" value="<?php echo $ansR[39]['intake_comment_body']; ?>" /></div></div>

<div class="clr mb_20"></div>



<div class="col-md-12"><div class="mb-3 mt-3"><label> Please choose your servicer <span class="" id="intake_form_required_96">*</span></label>
<div>
<label class="radio-inline"><input type="radio" name="inputr[ansR][40][intake_answer_id]" value="66" <?php if ($ansR[40]['intake_answer_id'] == "66") {echo " checked";}?> required="required"> Mohela – Must choose for PSLF</label> &nbsp;
<label class="radio-inline"><input type="radio" name="inputr[ansR][40][intake_answer_id]" value="67" <?php if ($ansR[40]['intake_answer_id'] == "67") {echo " checked";}?> required="required"> Nelnet</label> &nbsp;
<label class="radio-inline"><input type="radio" name="inputr[ansR][40][intake_answer_id]" value="68" <?php if ($ansR[40]['intake_answer_id'] == "68") {echo " checked";}?> required="required"> Great Lakes</label> &nbsp;
<label class="radio-inline"><input type="radio" name="inputr[ansR][40][intake_answer_id]" value="69" <?php if ($ansR[40]['intake_answer_id'] == "69") {echo " checked";}?> required="required"> HESC/Ed Financial</label> &nbsp;
<!-- <label class="radio-inline"><input type="radio" name="inputr[ansR][40][intake_answer_id]" value="70" <?php if ($ansR[40]['intake_answer_id'] == "70") {echo " checked";}?> required="required"> OSLA Servicing</label> &nbsp;  -->
<label class="radio-inline"><input type="radio" name="inputr[ansR][40][intake_answer_id]" value="71" <?php if ($ansR[40]['intake_answer_id'] == "71") {echo " checked";}?> required="required"> idvantage</label> &nbsp;
</div>

</div></div>

<div class="clr mb_20"></div>

<div class="col-md-4" style="display:none;"><div class="mb-3 mt-3"><label for="email" class="form-label">Expected Grace Period End Date (month/year):</label>
<input type="text" class="form-control" name="inputr[grace_period_end_date]" value="<?php echo $grace_period_end_date; ?>" /></div></div>

<div class="clr mb_20"></div>
<?php }?>
</div>


<div class="col-md-12">
<?php

			if ($radio['1'] == "2") {$radio['2'] = "";}
			if ($marital_status == "14" || $marital_status == "72" || $radio['11'] == "73") {$radio['8'] = $radio['10'] = $inputr['user']['spouse_ssn'] = $inputr['user']['spouse_name'] = $inputr['user']['spouse_dob'] = "";}
			if ($radio['8'] == "2") {$inputr['user']['spouse_ssn'] = $inputr['user']['spouse_name'] = $inputr['user']['spouse_dob'] = "";}
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
			if ($marital_status == "14" || $marital_status == "72" || $radio['11'] == "73") {$idr_disp_8 = $idr_disp_9 = $idr_disp_10 = "disp_none";}
			if ($radio['1'] == "2") {$idr_disp_2 = "disp_none";}

			?>
</div>

<div class="col-md-12"><h4 style="margin-bottom:0px;"><strong>SECTION 2: REPAYMENT PLAN OR RECERTIFICATION REQUEST</strong></h4><hr style="margin-top:5px;" /></div>

<div class="col-md-6"><div class="mb-3 mt-3"><label for="email" class="form-label">1.	Select the reason you are submitting this form (Check only one):</label>

<div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][1]" value="1" <?php if ($radio[1] == "1") {echo ' checked="checked"';}?> onChange="check_question_1(this.value)" />  I want to <em class="em_ul_style">enter an income-driven plan</em> - Continue to Item 2.</div>

<div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][1]" value="2" <?php if ($radio[1] == "2") {echo ' checked="checked"';}?> onChange="check_question_1(this.value)" />  I am submitting documentation for the <em class="em_ul_style">annual recertification</em> of my income-driven payment - Skip to Item 3.</div>

<div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][1]" value="3" <?php if ($radio[1] == "3") {echo ' checked="checked"';}?> onChange="check_question_1(this.value)" />  I am submitting documentation early to have my income-driven <em class="em_ul_style">payment recalculated immediately</em> - Skip to Item 3.</div>

<div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][1]" value="4" <?php if ($radio[1] == "4") {echo ' checked="checked"';}?> onChange="check_question_1(this.value)" />  I want to <em class="em_ul_style">change to a different income-driven plan</em> - Continue to Item 2.</div>

</div></div>



<div class="col-md-6 <?php echo $idr_disp_2; ?>" id="div_radio_2"><div class="mb-3 mt-3"><label for="email" class="form-label">2. Choose a plan and then continue to Item 3.</label>

    <div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][2]" value="1" <?php if ($radio[2] == "1") {echo ' checked="checked"';}?> />  (Recommended) I want the income-driven repayment plan with the lowest monthly payment.</div>

    <div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][2]" value="2" <?php if ($radio[2] == "2") {echo ' checked="checked"';}?> />  SAVE (formerly known as REPAYE)</div>

<div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][2]" value="3" <?php if ($radio[2] == "3") {echo ' checked="checked"';}?> />  IBR</div>

    <div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][2]" value="4" <?php if ($radio[2] == "4") {echo ' checked="checked"';}?> />  PAYE</div>

<div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][2]" value="5" <?php if ($radio[2] == "5") {echo ' checked="checked"';}?> />  ICR</div>

</div></div>

<div class="clr"></div>


<div class="col-md-6" id="div_radio_3"><div class="mb-3 mt-3"><label for="email" class="form-label">3. Do you have multiple loan holders or servicers?</label>
    <div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][3]" value="1" <?php if ($radio[3] == "1") {echo ' checked="checked"';}?> />  Yes - Submit a request to each holder or servicer. Continue to Item 4.</div>

    <div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][3]" value="2" <?php if ($radio[3] == "2") {echo ' checked="checked"';}?> />  No - Continue to Item 4.</div>

</div></div>


<div class="col-md-6"><div class="mb-3 mt-3"><label for="email" class="form-label">4. Are you currently in deferment or forbearance?<br />After answering, continue to Item 5.</label>

    <div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][4]" value="1" <?php if ($radio[4] == "1") {echo ' checked="checked"';}?> />  No.</div>

    <div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][4]" value="2" <?php if ($radio[4] == "2") {echo ' checked="checked"';}?> />  Yes, but I want to start making payments under my plan immediately.</div>

    <div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][4]" value="3" <?php if ($radio[4] == "3") {echo ' checked="checked"';}?> />  Yes, and I do not want to start repaying my loans until the deferment or forbearance ends.</div>

</div></div>


<div class="clr"></div>
<div class="col-md-12"><h4 style="margin-bottom:0px;"><strong>SECTION 3: FAMILY SIZE INFORMATION</strong></h4><hr style="margin-top:5px;" /></div>

<div class="col-md-6"><div class="mb-3 mt-3"><label for="email" class="form-label">5. How many children, including unborn children, are in your family and receive more than half of their support from you?</label><input type="text" class="form-control" name="inputr[ansintkR][19][intake_comment_body]" value="<?php echo $ansintkR['19']['intake_comment_body']; ?>" /></div></div>


<div class="col-md-6"><div class="mb-3 mt-3"><label for="email" class="form-label">6. How many other people, excluding your spouse and children, live with you and receive more than half of their support from you?</label><input type="text" class="form-control" name="inputr[ansintkR][20][intake_comment_body]" value="<?php echo $ansintkR['20']['intake_comment_body']; ?>" /></div></div>



<div class="clr"></div>
<div class="col-md-12"><h4 style="margin-bottom:0px;"><strong>SECTION 4A: MARITAL STATUS INFORMATION</strong></h4><hr style="margin-top:5px;" /></div>

<div class="col-md-6"><div class="mb-3 mt-3"><label for="email" class="form-label">7. What is your marital status?</label>

    	<div class="form-check"><input type="radio" class="form-check-input" name="inputr[ansintkR][11][intake_answer_id]" value="14" <?php if ($marital_status == "14") {echo ' checked="checked"';}?> onChange="check_question_7(this.value)" /> Single - Skip to Item 11.</div>
        <div class="form-check"><input type="radio" class="form-check-input" name="inputr[ansintkR][11][intake_answer_id]" value="15" <?php if ($marital_status == "15") {echo ' checked="checked"';}?> onChange="check_question_7(this.value)" /> Married - Continue to Item 8.</div>
        <div class="form-check"><input type="radio" class="form-check-input" name="inputr[ansintkR][11][intake_answer_id]" value="72" <?php if ($marital_status == "72") {echo ' checked="checked"';}?> onChange="check_question_7(this.value)" /> Married, but separated - You will be treated as single. Skip to Item 11.</div>
        <div class="form-check"><input type="radio" class="form-check-input" name="inputr[ansintkR][11][intake_answer_id]" value="73" <?php if ($marital_status == "73") {echo ' checked="checked"';}?> onChange="check_question_7(this.value)" /> Married, but cannot reasonably access my spouse's income information - You will be treated as single. Skip to Item 11.</div>
</div></div>


<div class="col-md-6 <?php echo $idr_disp_8; ?>" id="div_radio_8"><div class="mb-3 mt-3"><label for="email" class="form-label">8. Does your spouse have federal student loans?</label>
<input type="radio" name="inputr[radio][8]" value="" checked="checked" class="disp_none" />

    	<div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][8]" value="1" <?php if ($radio[8] == "1") {echo ' checked="checked"';}?> onChange="check_question_8(this.value)" /> Yes - Continue to Item 9.</div>
        <div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][8]" value="2" <?php if ($radio[8] == "2") {echo ' checked="checked"';}?>  onChange="check_question_8(this.value)" /> No - Skip to Item 10.</div>
</div></div>

<div class="clr"></div>


<div class="col-md-6 <?php echo $idr_disp_9; ?>" id="div_radio_9"><div class="mb-3 mt-3"><label for="email" class="form-label">9. Provide the following information about your spouse and then continue to Item 10:</label>

	<div class="mb-3 mt-3"><label for="email" class="form-label">a. Spouse's SSN</label><input type="text" class="form-control" name="inputr[user][spouse_ssn]" value="<?php echo $user['spouse_ssn']; ?>" /></div>

        <div class="mb-3 mt-3"><label for="email" class="form-label">b. Spouse's Name</label><input type="text" class="form-control" name="inputr[user][spouse_name]" value="<?php echo $user['spouse_name']; ?>" /></div>

        <div class="mb-3 mt-3"><label for="email" class="form-label">c. Spouse's Date of Birth</label><input type="date" class="form-control" name="inputr[user][spouse_dob]" value="<?php echo $user['spouse_dob']; ?>" /></div>
</div></div>


<div class="col-md-6 <?php echo $idr_disp_10; ?>" id="div_radio_10"><div class="mb-3 mt-3"><label for="email" class="form-label">10. When you filed your last federal income tax return, did you file jointly with your spouse?</label>
<input type="radio" name="inputr[radio][10]" value="" checked="checked" class="disp_none" />

        <div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][10]" value="1" <?php if ($radio[10] == "1") {echo ' checked="checked"';}?> onChange="check_question_10(this.value)" /> Yes - Continue to Item 13.</div>
        <div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][10]" value="2" <?php if ($radio[10] == "2") {echo ' checked="checked"';}?> onChange="check_question_10(this.value)" /> No - Skip to Item 17.</div>
</div></div>




<div class="clr"></div>
<div id="section_4b">
<div class="col-md-12"><h4 style="margin-bottom:0px;"><strong>SECTION 4B: INCOME INFORMATION FOR SINGLE BORROWERS AND MARRIED BORROWERS TREATED AS SINGLE</strong></h4><hr style="margin-top:5px;" /></div>

<div class="col-md-6"><div class="mb-3 mt-3"><label for="email" class="form-label">11. Has your income significantly decreased since you filed your last federal income tax return?<br />
For example, have you lost your job, experienced a drop in income, or gotten divorced, or did you most recently file a joint return with your spouse, but you have since become separated or lost the ability to access your spouse's income information? </label>
    	<div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][11]" value="1" <?php if ($radio[11] == "1") {echo ' checked="checked"';}?> onChange="check_question_11(this.value)" /> Yes - Continue to Item 12.</div>
        <div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][11]" value="2" <?php if ($radio[11] == "2") {echo ' checked="checked"';}?> onChange="check_question_11(this.value)" /> No - Provide your most recent federal income tax return or transcript. Skip to Section 6.</div>
        <div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][11]" value="3" <?php if ($radio[11] == "3") {echo ' checked="checked"';}?> onChange="check_question_11(this.value)" /> I haven't filed a federal income tax return in the last two years - Continue to Item 12.</div>
</div></div>


<div class="col-md-6 <?php echo $idr_disp_12; ?>" id="div_radio_12"><div class="mb-3 mt-3"><label for="email" class="form-label">12. Do you currently have taxable income?<br />Check "No" if you do not have any income or receive only untaxed income.</label>
<input type="radio" name="inputr[radio][12]" value="" checked="checked" class="disp_none" />

        <div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][12]" value="1" <?php if ($radio[12] == "1") {echo ' checked="checked"';}?> onChange="check_question_12(this.value)" /> Yes - Provide documentation of your income as instructed in Section 5. Skip to that section</div>
        <div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][12]" value="2" <?php if ($radio[12] == "2") {echo ' checked="checked"';}?> onChange="check_question_12(this.value)" /> No - You are not required to provide documentation of your income. Skip to Section 6.</div>
</div></div>
</div>


<div class="clr"></div>
<div id="section_4c <?php echo $section_4c; ?>">
<div class="col-md-12"><h4 style="margin-bottom:0px;"><strong>SECTION 4C: INCOME INFORMATION FOR MARRIED BORROWERS FILING JOINTLY</strong></h4><hr style="margin-top:5px;" /></div>

<div class="col-md-6 <?php echo $idr_disp_13; ?>" id="div_radio_13"><div class="mb-3 mt-3"><label for="email" class="form-label">13. Has your income significantly decreased since you filed your last federal income tax return?<br />For example, have you lost your job or experienced a drop in income?</label>
<input type="radio" name="inputr[radio][13]" value="" checked="checked" class="disp_none" />

    	<div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][13]" value="1" <?php if ($radio[13] == "1") {echo ' checked="checked"';}?> onChange="check_question_13(this.value)" /> Yes - Skip to Item 15.</div>
        <div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][13]" value="2" <?php if ($radio[13] == "2") {echo ' checked="checked"';}?> onChange="check_question_13(this.value)" /> No - Continue to Item 14.</div>
        <div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][13]" value="3" <?php if ($radio[13] == "3") {echo ' checked="checked"';}?> onChange="check_question_13(this.value)" /> We haven't filed a federal income tax return in the last two years - Skip to Item 15.</div>
</div></div>

<div class="col-md-6 <?php echo $idr_disp_14; ?>" id="div_radio_14"><div class="mb-3 mt-3"><label for="email" class="form-label">14. Has your spouse's income significantly decreased since your spouse filed his or her last federal income tax return?<br />For example, has your spouse lost his or her job or experienced a drop in income?</label>
<input type="radio" name="inputr[radio][14]" value="" checked="checked" class="disp_none" />

    	<div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][14]" value="1" <?php if ($radio[14] == "1") {echo ' checked="checked"';}?> onChange="check_question_14(this.value)" /> Yes - Continue to Item 15.</div>
        <div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][14]" value="2" <?php if ($radio[14] == "2") {echo ' checked="checked"';}?> onChange="check_question_14(this.value)" /> No - Provide your and your spouse's most recent federal income tax return or transcript. Skip to Section 6</div>
</div></div>
<div class="clr"></div>


<div class="col-md-6 <?php echo $idr_disp_15; ?>" id="div_radio_15"><div class="mb-3 mt-3"><label for="email" class="form-label">15. Do you currently have taxable income?<br /> Check "No" if you do not have any income or receive only untaxed income.</label>
<input type="radio" name="inputr[radio][15]" value="" checked="checked" class="disp_none" />

        <div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][15]" value="1" <?php if ($radio[15] == "1") {echo ' checked="checked"';}?> onChange="check_question_15(this.value)" /> Yes - You must provide documentation of your income according to the instructions in Section 5. Continue to Item 16.</div>
        <div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][15]" value="2" <?php if ($radio[15] == "2") {echo ' checked="checked"';}?> onChange="check_question_15(this.value)" /> No - You are not required to provide documentation of your income. Continue to Item 16.</div>
</div></div>


<div class="col-md-6 <?php echo $idr_disp_16; ?>" id="div_radio_16"><div class="mb-3 mt-3"><label for="email" class="form-label">16. Does your spouse currently have taxable income?<br />Check "No" if your spouse does not have any income or receives only untaxed income.</label>
<input type="radio" name="inputr[radio][16]" value="" checked="checked" class="disp_none" />

        <div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][16]" value="1" <?php if ($radio[16] == "1") {echo ' checked="checked"';}?> onChange="check_question_16(this.value)" /> Yes - Skip to Section 5 and provide documentation of your spouse's income as instructed in that section.</div>
        <div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][16]" value="2" <?php if ($radio[16] == "2") {echo ' checked="checked"';}?> onChange="check_question_16(this.value)" /> No - You are not required to provide documentation of your spouse's income. If you selected "Yes" to Item 15, skip to Section 5 and document your income. If you selected "No" to Item 15, skip to Section 6.</div>
</div></div>
</div>



<div class="clr"></div>
<div id="section_4d <?php echo $section_4c; ?>">
<div class="col-md-12"><h4 style="margin-bottom:0px;"><strong>SECTION 4D: INCOME INFORMATION FOR MARRIED BORROWERS FILING SEPARATELY</strong></h4><hr style="margin-top:5px;" /></div>

<div class="col-md-6 <?php echo $idr_disp_17; ?>" id="div_radio_17"><div class="mb-3 mt-3"><label for="email" class="form-label">17. Has your income significantly decreased since you filed your last federal income tax return?<br />For example, have you lost your job or experienced a drop in income?</label>
<input type="radio" name="inputr[radio][17]" value="" checked="checked" class="disp_none" />

	<div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][17]" value="1" <?php if ($radio[17] == "1") {echo ' checked="checked"';}?> onChange="check_question_17(this.value)" />  Yes - Continue to Item 18.</div>
        <div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][17]" value="2" <?php if ($radio[17] == "2") {echo ' checked="checked"';}?> onChange="check_question_17(this.value)" />  No - Provide your most recent federal income tax return or transcript. Skip to Item 19.</div>
        <div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][17]" value="3" <?php if ($radio[17] == "3") {echo ' checked="checked"';}?> onChange="check_question_17(this.value)" />  I haven't filed a federal income tax return in the past two years - Continue to Item 18.</div>
</div></div>



<div class="col-md-6 <?php echo $idr_disp_18; ?>" id="div_radio_18"><div class="mb-3 mt-3"><label for="email" class="form-label">18.  Do you currently have taxable income?<br />Check "No" if you have no taxable income or receive only untaxed income. After answering, continue to Item 19.</label>
<input type="radio" name="inputr[radio][18]" value="" checked="checked" class="disp_none" />

	<div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][18]" value="1" <?php if ($radio[18] == "1") {echo ' checked="checked"';}?> onChange="check_question_18(this.value)" />  Yes - You must provide documentation of your income as instructed in Section 5</div>
        <div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][18]" value="2" <?php if ($radio[18] == "2") {echo ' checked="checked"';}?> onChange="check_question_18(this.value)" />  No.</div>
</div></div>
<div class="clr"></div>


<div class="col-md-6 <?php echo $idr_disp_19; ?>" id="div_radio_19"><div class="mb-3 mt-3"><label for="email" class="form-label">19. Has your spouse's income significantly decreased since your spouse filed his or her last federal income tax return?<br />For example, has your spouse lost a job or experienced a drop in income?</label>
<input type="radio" name="inputr[radio][19]" value="" checked="checked" class="disp_none" />

	<div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][19]" value="1" <?php if ($radio[19] == "1") {echo ' checked="checked"';}?> onChange="check_question_19(this.value)" />  Yes - Continue to Item 20.</div>
        <div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][19]" value="2" <?php if ($radio[19] == "2") {echo ' checked="checked"';}?> onChange="check_question_19(this.value)" />  No - Provide your spouse's most recent federal income tax return or transcript. This information will only be used if you are on or placed on the SAVE (formerly known as REPAYE) Plan. Skip to Section 6.</div>
        <div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][19]" value="3" <?php if ($radio[19] == "3") {echo ' checked="checked"';}?> onChange="check_question_19(this.value)" />  My spouse hasn't filed a federal income tax return in the past two years - Continue to Item 20.</div>
</div></div>



<div class="col-md-6 <?php echo $idr_disp_20; ?>" id="div_radio_20"><div class="mb-3 mt-3"><label for="email" class="form-label">20. Does your spouse currently have taxable income?<br />Check "No" if your spouse has no taxable income or receives only untaxed income.</label>
<input type="radio" name="inputr[radio][20]" value="" checked="checked" class="disp_none" />

	<div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][20]" value="1" <?php if ($radio[20] == "1") {echo ' checked="checked"';}?> />  Yes - Skip to Section 5 and provide documentation of your spouse's income as instructed in that section. This information will only be used if you are on or placed on the SAVE (formerly known as REPAYE) Plan.</div>
        <div class="form-check"><input type="radio" class="form-check-input" name="inputr[radio][20]" value="2" <?php if ($radio[20] == "2") {echo ' checked="checked"';}?> />  No - You are not required to provide documentation of your spouse's income. If you selected "Yes" to Item 18, skip to Section 5 and document your income. If you selected "No" to Item 18, skip to Section 6.</div>
</div></div>
</div>








<div class="clr"></div>


<div class="col-md-12" style="display:none;"><div class="mb-3 mt-3"><div class="form-check"><input type="checkbox" class="form-check-input" name="inputr[radio][idr_page_4_i_request]" value="Yes" <?php if (isset($radio['idr_page_4_i_request'])) {if ($radio['idr_page_4_i_request'] == "Yes") {echo ' checked="checked"';}}?> /> <strong>I request</strong> a one-month reduced-payment forbearance in the amount of: <span style="text-decoration:underline;">&nbsp; SEC6 &nbsp; </span> (must be at least $5).</div></div></div>
<div class="clr"></div>




<div class="col-md-6" style="display:none;"><div class="mb-3 mt-3"><label for="email" class="form-label">Return the completed form and any documentation to: (If no address is shown, return to your loan holder.)</label>
    <textarea class="form-control" name="inputr[idr_page_5][textarea][1]" style="height:80px;"><?php echo $idr_page_5['textarea'][1]; ?></textarea></div></div>

<div class="col-md-6" style="display:none;"><div class="mb-3 mt-3"><label for="email" class="form-label">If you need help completing this form call: (If no phone number is shown, call your loan holder.)</label>
    <textarea class="form-control" name="inputr[idr_page_5][textarea][2]" style="height:80px;"><?php echo $idr_page_5['textarea'][2]; ?></textarea></div></div>


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

<script type="text/javascript">
//	Check radio Question 1
function check_question_1(val){if(val=='2'||val=='3'){$("#div_radio_2").hide('100')}else{$("#div_radio_2").show('100')}}


//	Check radio Question 7
function check_question_7(val)
{
	if(val == '15')
	{
		$("#div_radio_8").show('100');
		$("#div_radio_9").show('100');
		$("#div_radio_10").show('100');
	}
	else
	{
		$("#div_radio_8").hide('100');
		$("#div_radio_9").hide('100');
		$("#div_radio_10").hide('100');
	}
}


//	Check radio Question 8
function check_question_8(val){if(val=='2'){$("#div_radio_9").hide('100')}else{$("#div_radio_9").show('100')}}



//	Check radio Question 10
function check_question_10(val)
{
	if(val == '2')
	{
		$("#section_4c").hide('100');
		$("#section_4d").hide('100');
	}
	else
	{
		$("#section_4b").show('100');
		$("#section_4c").show('100');
	}
}



//	Check radio Question 11
function check_question_11(val)
{
	if(val == '2')
	{
		$("#div_radio_12").hide('100');
		$("#section_4c").hide('100');
		$("#section_4d").hide('100');
	}
	else
	{
		$("#div_radio_12").show('100');
		$("#section_4c").show('100');
		$("#section_4d").show('100');
	}
}


//	Check radio Question 12
function check_question_12(val)
{
	if(val == '2')
	{
		$("#section_4c").hide('100');
		$("#section_4d").hide('100');
	}
	else
	{
		$("#section_4c").show('100');
		$("#section_4d").show('100');
	}
}



//	Check radio Question 13
function check_question_13(val){if(val=='1'||val=='3'){$("#div_radio_14").hide('100')}else{$("#div_radio_14").show('100')}}


//	Check radio Question 14
function check_question_14(val)
{
	if(val == '1' | val == '2')
	{
		$("#div_radio_15").hide('100');
		$("#div_radio_16").hide('100');
		$("#section_4d").hide('100');
	}
	else
	{
		$("#div_radio_15").show('100');
		$("#div_radio_16").show('100');
		$("#section_4d").show('100');
	}
}



//	Check radio Question 16
function check_question_16(val){if(val=='1'||val=='2'){$("#section_4d").hide('100')}else{$("#section_4d").show('100')}}


//	Check radio Question 17
function check_question_17(val)
{
	$("#div_radio_18").show('100');
	$("#div_radio_19").show('100');
	$("#div_radio_20").show('100');

	if(val == '2') {	$("#div_radio_18").hide('100');	}
}


//	Check radio Question 19
function check_question_19(val){if(val=='2'){$("#div_radio_20").hide('100')}else{$("#div_radio_20").show('100')}}



</script>

</body></html>
<?php

	}
}
?>