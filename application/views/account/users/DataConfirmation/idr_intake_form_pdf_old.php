<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php
error_reporting(0);
@extract($_POST);
$fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);

$client_id = $this->uri->segment(3);

if ($GLOBALS["loguser"]["role"] == "Customer") {$client_id = $GLOBALS["loguser"]["id"];}

$user = $this->default_model->get_arrby_tbl('users', '*', "role='Customer' and id='" . $client_id . "'", '1');
$user = $user["0"];
@extract($user);
$client_id = $id;

if ($client_id != '') {
	if (isset($docr['document_id'])) {
		if (isset($ics['id'])) {

			$intake_id = $ics['intake_id'];
			$program_id_primary = $intkR['program_definition_id'];

			$q = $this->db->query("SELECT * FROM client_program_progress where client_id='$client_id' and program_id_primary='$program_id_primary' and step_id='6' limit 1");
			$cpp = $q->row_array();

			$program_definition_id = $cpp['program_definition_id'];

			$file_name = $user['lname'] . " " . $user['name'] . " " . str_replace("Intake", "", $docr['document_name']) . " " . date('Y-m-d', strtotime($docr['uploaded_date'])) . "-Internal.pdf";

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
			if ($marital_status == ($a + 14) || $marital_status == ($a2 + 72) || $radio['11'] == ($a2 + 73)) {$idr_disp_8 = $idr_disp_9 = $idr_disp_10 = "disp_none";}
			if ($radio['1'] == "2") {$idr_disp_2 = "disp_none";}

			?>


<!DOCTYPE html>
<html lang="en">
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo ucfirst($file_name); ?></title>

<style type="text/css">
/*@media print {*/
body {
margin: 0px;
padding:0px;
color: #000000;
font-family:Calibri;
}

* {
  box-sizing: border-box;
  -moz-box-sizing: border-box;
}
strong { font-weight:bold; }

.clr { clear:both; height:0px;}
/*.disp_none { display:none;}*/
.font-10 { font-size:10px;}
.font-13 { font-size:13px;}
.font-15 { font-size:15px;}

.mb_1 { margin-bottom:1px;}
.mb_2 { margin-bottom:2px;}
.mb_3 { margin-bottom:3px;}
.mb_5 { margin-bottom:5px;}
.mb_7 { margin-bottom:7px;}
.mb_8 { margin-bottom:8px;}
.mb_10 { margin-bottom:10px;}
.mb_15 { margin-bottom:15px;}
.mb_20 { margin-bottom:20px;}


.mb_20_ { margin-bottom:-20px;}
.mb_25_ { margin-bottom:-25px;}
.mb_30_ { margin-bottom:-30px;}
.mb_35_ { margin-bottom:-35px;}

.mt_5_ { margin-top:-5px;}
.mt_20_ { margin-top:-20px;}
.mt_25_ { margin-top:-25px;}
.mt_30_ { margin-top:-30px;}
.mt_35_ { margin-top:-35px;}

.pb_5 { padding-bottom:5px;}

.bg_1 { background:#f1f4ff; color:#777777; }
.style_1 {color:#000000; font-size:7.4pt; line-height:10pt;}
.style_2 {color:#000000; font-size:10pt;}
.style_3 {color:#000000; font-size:10.3pt;}
.em_ul_style { text-decoration:underline; font-style:normal;}
.line_border_1 { width:100%; height:4px; margin:3px 0px; background:#000000;}
.line_border_2 { width:100%; height:1px; margin:5px 0px; background:#000000;}
.line_border_3 { width:100%; height:2px; margin:2px 0px 0px 0px; background:#000000;}

.input_td { background:transparent; color:#777777; font-size:13px; padding:3px 5px; border-bottom:1px solid #888888; }
.input_div_2 {width:auto; height:13px; font-size:12px; color:#000000; border-bottom:1px solid #666666; padding:2px 5px;}
.input_2 {width:100%; height:12px; padding:2px 2px 2px 2px; color:#444444; background:transparent; font-size:10pt; border:none; border-bottom:.5px solid #888888;}
.input_3 {width:100%; height:15px; padding:2px 2px 2px 2px; background:transparent; font-size:13px; border:none;}
.input_4 {width:100%; height:13px; padding:2px 2px 2px 2px; color:#666666; background:transparent; font-size:7.5pt; border:none; border-bottom:.5px solid #888888;}
.input_5 {width:100%; height:12px; padding:2px 2px 2px 2px; color:#444444; background:transparent; font-size:10pt; font-weight:bold; border:none; border-bottom:.5px solid #888888;}

.omb_box { float:right; width:80px; height:35px; padding:5px 10px; margin-right:10px; border:1px solid #000000; font-size:5.5pt; line-height:8pt; font-family:Arial, Helvetica, sans-serif; }

.page_wrapper { width:725px; margin:0 auto; margin-bottom:-30px; overflow:hidden; font-family:Arial, Helvetica, sans-serif; }
.page_wrapper_inner { width:100%; height:925px; overflow:hidden; vertical-align:top; }
.page_wrapper_inner_2 { width:100%; height:975px; overflow:hidden; vertical-align:top; }

.mrgn_consolidation { margin-top:-15px; }
.mrgn_idr { margin:-22px -25px -25px -23px; }
.pagebreak { display: block; height:0px; clear: both; page-break-after: always; }
.pagging_text { font-size:6pt; text-align:center; }
.pagging_text_font_2 { font-size:7.5pt; }
.pagging_text_1 { font-size:8pt; text-align:right; margin-top:-5px; font-weight:bold; font-style:italic; }
.idr_paging { font-size:9pt; text-align:center; }


/*}*/
</style>
</head>
<body>
<?php
if ($intkR['intake_title'] == "IDR Intake" || $intkR['intake_title'] == "Consolidation Intake") {

				$ssn = explode("-", $fd['ssn']);
				?>


<div>

<!--	18C IDR 2021 CODED (PAGE 1)	-->
<table class="page_wrapper mrgn_idr" cellpadding="0" cellspacing="0">
<tr>
<td class="page_wrapper_inner_2">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:0px;">
<tr style="border:0px;">
<td width="15%" rowspan="2" valign="top" align="center"><img src="<?php echo 'data:image/png;base64,' . base64_encode(file_get_contents(("assets/img/logo_doe2.png"))); ?>" width="95" alt="Logo" /><br /><strong style="font-size:14px;">IDR</strong></td>
<td width="65%" valign="top" style="padding-left:5px;">
<strong style="font-size:14.5pt;">INCOME-DRIVEN REPAYMENT (IDR) PLAN REQUEST</strong>
<div class="clr mb_2"></div>
<strong style="font-size:10.3pt;">For the SAVE (formerly known as REPAYE), Pay As You Earn (PAYE),
Income-Based Repayment (IBR), and Income-Contingent Repayment (ICR)
plans under the William D. Ford Federal Direct Loan (Direct Loan) Program
and Federal Family Education Loan (FFEL) Programs</strong></td>
<td width="20%" valign="top" style="font-size:10.3pt; line-height:13pt;"><div style="float:right;">OMB No. 1845-0102 <br />Form Approved<br />Expiration Date:<br /> 8/31/2021</div></td>
</tr>
<tr>	<td colspan="2" style="font-size:10.2pt; padding-left:5px;"><strong>WARNING:</strong> Any person who knowingly makes a false statement or misrepresentation on this form or on any accompanying document is subject to penalties that may include fines, imprisonment, or both, under the U.S. Criminal Code and 20 U.S.C. 1097.</td>	</tr>
</table>
<div class="clr"></div>


<strong class="style_2"> &nbsp; SECTION 1: BORROWER INFORMATION</strong>
<div class="line_border_3"></div>
<div class="clr"></div>

<table width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;">
<tr><td width="40%">&nbsp;</td><td colspan="2"><span>Please enter or correct the following information.</span></td></tr>
<tr><td></td>
<td width="1%"><input type="checkbox" <?php if (isset($radio['idr_page_1_correct_info'])) {if ($radio['idr_page_1_correct_info'] == "Yes") {echo ' checked="checked"';}}?> style="margin-top:0px;" /></td>
<td><strong>Check this box if any of your information has changed.</strong></td>
</tr></table>
<div class="clr"></div>

<table border="0" style="width:100%;" cellpadding="1.5" cellspacing="0">
<tr>
	<td width="60%" align="right" class="font-13">SSN</td>	<td width="1%">&nbsp;</td>
    <td colspan="5"><input type="text" class="input_2" name="ssn" value="<?php echo $fd['ssn']; ?>" style="width:150px;" /></td>
</tr>

<tr>
	<td align="right" class="font-13">Name</td>	<td width="1%">&nbsp;</td>
    <td colspan="5"><input type="text" class="input_2" name="name" value="<?php echo $fd['name']; ?>" /></td>
</tr>

<tr>
	<td align="right" class="font-13">Address</td>	<td width="1%">&nbsp;</td>
    <td colspan="5"><input type="text" class="input_2" name="address" value="<?php echo $fd['address']; ?>" /></td>
</tr>

<tr>
	<td align="right" class="font-13">City</td>	<td width="1%">&nbsp;</td>
    <td><input type="text" class="input_2" name="address" value="<?php echo $fd['city']; ?>" /></td>

    <td width="1%" align="right" class="font-13">State</td>
    <td><input type="text" class="input_2" name="address" value="<?php echo $fd['state']; ?>" /></td>

    <td width="45" align="right" class="font-13">Zip Code</td>
    <td width="40"><input type="text" class="input_2" name="address" value="<?php echo $fd['zipcode']; ?>" /></td>
</tr>

<tr>
	<td align="right" class="font-13">Telephone - Primary</td>	<td width="1%">&nbsp;</td>
    <td colspan="5"><input type="text" class="input_2" name="address" value="<?php echo preg_replace('/\d{3}/', '$0-', str_replace('.', null, trim($fd['telephone_primary'])), 2); ?>" /></td>
</tr>

<tr>
	<td align="right" class="font-13">Telephone - Alternate</td>	<td width="1%">&nbsp;</td>
    <td colspan="5"><input type="text" class="input_2" name="address" value="<?php echo preg_replace('/\d{3}/', '$0-', str_replace('.', null, trim($fd['telephone_alternate'])), 2); ?>" /></td>
</tr>

<tr>
	<td align="right" class="font-13">Email (Optional)</td>	<td width="1%">&nbsp;</td>
    <td colspan="5"><input type="text" class="input_2" name="address" value="<?php echo $fd['email']; ?>" /></td>
</tr>


</table>

<div class="clr mb_3"></div>

<strong class="style_2"> &nbsp; SECTION 2: REPAYMENT PLAN OR RECERTIFICATION REQUEST</strong>
<div class="line_border_3"></div>
<div class="clr"></div>
<table width="100%"><tr><td class="style_3" style="padding:0 5px;">It's faster and easier to complete this form online at <a href="https://studentAid.gov" target="_blank">StudentAid.gov.</a> You can learn more at <a href="https://studentAid.gov/IDR" target="_blank">StudentAid.gov/IDR</a> and by
reading Sections 9 and 10. It's simple to get repayment estimates at <a href="https://studentAid.gov/repayment-estimator" target="_blank">StudentAid.gov/repayment-estimator.</a> If you need help with this form, contact your loan holder or servicer for free assistance. You can find out who your loan holder or servicer is at <a href="https://studentAid.gov/login" target="_blank">StudentAid.gov/login.</a> You may have to pay income tax on any loan amount forgiven under an income-driven plan.</td></tr></table>
<div class="clr mb_5"></div>

<table width="100%" class="style_3" style="margin:0px 5px;" cellpadding="0" cellspacing="0"><tr>

<td width="50%" valign="top">
<table width="100%">
<tr>
<td valign="top"><strong>1. </strong></td>
<td><strong>Select the reason you are submitting this form<br />(Check only one):</strong>
	<table width="100%">
    	<tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[1] == "1") {echo ' checked="checked"';}?> /></td> <td>I want to <em class="em_ul_style">enter an income-driven plan</em> - Continue to Item 2.</td></tr>
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[1] == "2") {echo ' checked="checked"';}?> /></td> <td>I am submitting documentation for the <em class="em_ul_style">annual recertification</em> of my income-driven payment - Skip to Item 3.</td></tr>
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[1] == "3") {echo ' checked="checked"';}?> /></td> <td>I am submitting documentation early to have my income-driven <em class="em_ul_style">payment recalculated immediately</em> - Skip to Item 3.</td></tr>
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[1] == "4") {echo ' checked="checked"';}?> /></td> <td>I want to <em class="em_ul_style">change to a different income-driven plan</em> - Continue to Item 2.</td></tr>
    </table>
</td>
</tr>


<tr>
<td valign="top"><strong>2. </strong></td>
<td><strong>Choose a plan and then continue to Item 3.</strong>
	<table width="100%">
    	<tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[2] == "1") {echo ' checked="checked"';}?> /></td> <td colspan="3">(Recommended) I want the income-driven repayment plan with the lowest monthly payment.</td></tr>
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[2] == "2") {echo ' checked="checked"';}?> style="margin-top:-5px;" /></td> <td>SAVE (formerly known as REPAYE)</td>
        <td width="15" valign="top"><input type="checkbox" <?php if ($radio[2] == "3") {echo ' checked="checked"';}?> style="margin-top:-5px;" /></td> <td>IBR</td></tr>

        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[2] == "4") {echo ' checked="checked"';}?> style="margin-top:-5px;" /></td> <td>PAYE</td>
        <td width="15" valign="top"><input type="checkbox" <?php if ($radio[2] == "5") {echo ' checked="checked"';}?> style="margin-top:-5px;" /></td> <td>ICR</td></tr>


    </table>
</td>
</tr>

</table>
</td>

<td width="50%" valign="top">
<table width="100%">
<tr>
<td valign="top"><strong>3. </strong></td>
<td><strong>Do you have multiple loan holders or servicers?</strong>
	<table width="100%">
    	<tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[3] == "1") {echo ' checked="checked"';}?> style="margin-top:-5px;" /></td> <td>Yes - Submit a request to each holder or servicer. Continue to Item 4.</td></tr>
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[3] == "2") {echo ' checked="checked"';}?> style="margin-top:-5px;" /></td> <td>No - Continue to Item 4.</td></tr>
    </table>
</td>
</tr>

<tr>
<td valign="top" style="padding-top:5px;"><strong>4. </strong></td>
<td style="padding-top:5px;"><strong>Are you currently in deferment or forbearance?</strong>
	<table width="100%">
    	<tr> <td></td> <td>After answering, continue to Item 5.</td></tr>
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[4] == "1") {echo ' checked="checked"';}?> style="margin-top:-5px;" /></td> <td>No.</td></tr>
        <tr><td valign="top"><input type="checkbox" <?php if ($radio[4] == "2") {echo ' checked="checked"';}?> style="margin-top:-5px;" /></td> <td>Yes, but I want to start making payments under my plan immediately.</td></tr>
        <tr><td valign="top"><input type="checkbox" <?php if ($radio[4] == "3") {echo ' checked="checked"';}?> style="margin-top:-5px;" /></td> <td>Yes, and I do not want to start repaying my loans until the deferment or forbearance ends.</td></tr>
    </table>
</td>
</tr>


</table>

<table><tr><td style="background:#dfdfdf; width:100%; padding:5px; border:1px solid #999999;"><strong>Note:</strong> If you have FFEL Program loans, they are only eligible for IBR. However, you can consolidate your loans at <a href="https://studentaid.gov" target="_blank">StudentAid.gov</a> to access more beneficial income-driven repayment plans.</td></tr></table>



</td>

</tr></table>

<strong class="style_2"> &nbsp; SECTION 3: FAMILY SIZE INFORMATION</strong><div class="line_border_3"></div><div class="clr"></div>

<table width="100%" style="margin:0px 5px;" class="style_2">
<tr><td width="5%" valign="top">
    <table width="100%">
    <tr>
    <td width="1%" valign="top"><strong>5. </strong></td>
    <td valign="top"><strong>How many children, including unborn <span style="text-decoration:underline;">children</span>, are in your family and receive more than half of their support from you?</strong></td>
    <td valign="top" width="1%"><input type="text" class="input_2" name="address" value="<?php echo $ansintkR['19']['intake_comment_body']; ?>" style="width:30px; height:20px; padding:15px 2px;" /></td>
    </tr>
    </table>
</td>

<td width="5%" valign="top">
    <table width="100%">
    <tr>
    <td width="1%" valign="top"><strong>6. </strong></td>
    <td valign="top"><strong>How many other people, <span style="text-decoration:underline;">excluding your spouse and children</span>, live with you and receive more than half of their support from you?</strong></td>
    <td valign="top" width="1%"><input type="text" class="input_2" name="address" value="<?php echo $ansintkR['20']['intake_comment_body']; ?>" style="width:30px; height:20px; padding:15px 2px;" /></td>
    </tr>
    </table>
</td>

</tr>

</table>

<table width="100%" style="margin:0px 5px;" class="style_2"><tr><td style="background:#dfdfdf; width:100%; padding:5px; border:1px solid #333333;"><strong>Note:</strong> A definition of "family size" is provided in Section 9. Do not enter a value for you or your spouse. Those values are automatically included in your family size, if appropriate.</td></tr></table>
<div class="clr mb_20"></div>

</td></tr>
<tr><td class="idr_paging">Page 1 of 10</td></tr>

</table>
<div class="pagebreak"></div>



<!--	18C IDR 2021 CODED (PAGE 2)	-->
<table class="page_wrapper mrgn_idr" cellpadding="0" cellspacing="0">
<tr>
<td class="page_wrapper_inner_2 style_2" style="padding:0px 8px;">

<table width="100%" border="0">
<tr>
	<td width="80"><strong>Borrower Name</strong></td>
    <td><input type="text" class="input_5" name="bn" value="<?php echo $fd['name']; ?>" style="width:90%;" /></td>
	<td width="70"><strong>Borrower SSN</strong></td>
    <td width="100"><input type="text" class="input_5" name="bssn" value="<?php echo $fd['ssn']; ?>" /></td>
</tr>
</table>
<div class="clr mb_15"></div>

<strong>SECTION 4A: MARITAL STATUS INFORMATION</strong><div class="line_border_3"></div><div class="clr"></div>
<table width="100%"><tr>

<td width="50%" valign="top">
<table width="100%">
<tr>
<td valign="top"><strong>7. </strong></td>
<td><strong>What is your marital status?</strong>
	<table width="100%">
    	<tr><td width="15" valign="top"><input type="checkbox" <?php if ($marital_status == ($a + 14)) {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>Single - Skip to Item 11.</td></tr>
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($marital_status == ($a + 15)) {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>Married - Continue to Item 8.</td></tr>
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($marital_status == ($a2 + 72)) {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>Married, but separated - You will be treated as single. Skip to Item 11.</td></tr>
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($marital_status == ($a2 + 73)) {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>Married, but cannot reasonably access my spouse's income information - You will be treated as single. Skip to Item 11.</td></tr>
    </table>
</td>
</tr>


<tr>
<td valign="top"><strong>8. </strong></td>
<td><strong> Does your spouse have federal student loans?</strong>
	<table width="100%">
    	<tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[8] == "1") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>Yes - Continue to Item 9.</td></tr>
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[8] == "2") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>No - Skip to Item 10.</td></tr>
    </table>
</td>
</tr>




</table>
</td>

<td width="50%" valign="top">
<table width="100%">
<tr>
<td valign="top"><strong>9. </strong></td>
<td><strong>Provide the following information about your spouse and then continue to Item 10:</strong>
	<table width="100%" style="margin:10px 0px 3px 0;">
    	<tr>
            <td width="10" valign="middle"><strong class="font-13">a.</strong></td>
            <td valign="middle"><strong>Spouse's SSN</strong> <input type="text" class="input_2" name="sname" value="<?php echo $user['spouse_ssn']; ?>" style="width:227px;" /></td>
        </tr>

        <tr>
            <td valign="middle"><strong class="font-13">b.</strong></td>
            <td valign="middle"><strong>Spouse's Name</strong> <input type="text" class="input_2" name="sname" value="<?php echo $user['spouse_name']; ?>" style="width:222px;" /></td>
        </tr>

        <tr>
            <td valign="middle"><strong class="font-13">c.</strong></td>
            <td valign="middle"><strong>Spouse's Date of Birth</strong> <input type="text" class="input_2" name="sname" value="<?php echo $user['spouse_dob']; ?>" style="width:180px;" /></td>
        </tr>

    </table>
</td>
</tr>

<tr>
<td valign="top"><strong>10. </strong></td>
<td><strong>When you filed your last federal income tax return, did you file jointly with your spouse?</strong><div class="clr mb_5"></div>
	<table width="100%" border="0">
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[10] == "1") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>Yes - Continue to Item 13.</td></tr>
        <tr><td valign="top"><input type="checkbox" <?php if ($radio[10] == "2") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>No - Skip to Item 17.</td></tr>
    </table>
</td>
</tr>

</table>

</td>

</tr></table>
<div class="clr mb_15"></div>

<strong>SECTION 4B: INCOME INFORMATION FOR SINGLE BORROWERS AND MARRIED BORROWERS TREATED AS SINGLE</strong><div class="line_border_1"></div><div class="clr"></div>
<table width="100%"><tr>

<td width="50%" valign="top">
<table width="100%">
<tr>
<td valign="top"><strong>11. </strong></td>
<td><strong>Has your income significantly decreased since you filed your last federal income tax return?</strong><br />
For example, have you lost your job, experienced a drop in income, or gotten divorced, or did you most recently file a joint return with your spouse, but you have since become separated or lost the ability to access your spouse's income information?

	<table width="100%">
    	<tr><td width="15" valign="top"><input type="checkbox" <?php if ($marital_status == ($a + 15) && $radio[10] == '1') {echo 'disabled';} else {if ($radio[11] == "1") {echo ' checked="checked"';} else {echo 'disabled';}}?> class="mt_5_" /></td> <td>Yes - Continue to Item 12.</td></tr>
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($marital_status == ($a + 15) && $radio[10] == '1') {echo 'disabled';} else {if ($radio[11] == "2") {echo ' checked="checked"';} else {echo 'disabled';}}?> class="mt_5_" /></td> <td>No - Provide your most recent federal income tax return or transcript. Skip to Section 6.</td></tr>
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($marital_status == ($a + 15) && $radio[10] == '1') {echo 'disabled';} else {if ($radio[11] == "3") {echo ' checked="checked"';} else {echo 'disabled';}}?> class="mt_5_" /></td> <td>I haven't filed a federal income tax return in the last two years - Continue to Item 12.</td></tr>
    </table>
</td>
</tr>



</table>
</td>

<td width="50%" valign="top">
<table width="100%">
<tr>
<td valign="top"><strong>12. </strong></td>
<td><strong>Do you currently have taxable income?</strong><br />
Check "No" if you do not have any income or receive only untaxed income.
	<table width="100%">
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($marital_status == ($a + 15) && $radio[10] == '1') {echo 'disabled';} else {if ($radio[12] == "1") {echo ' checked="checked"';} else {echo 'disabled';}}?> class="mt_5_" /></td> <td>Yes - Provide documentation of your income as instructed in Section 5. Skip to that section</td></tr>
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($marital_status == ($a + 15) && $radio[10] == '1') {echo 'disabled';} else {if ($radio[12] == "2") {echo ' checked="checked"';} else {echo 'disabled';}}?> class="mt_5_" /></td> <td>No - You are not required to provide documentation
of your income. Skip to Section 6.</td></tr>
    </table>
</td>
</tr>


</table>

<table style="margin-top:10px;"><tr><td style="background:#dfdfdf; width:100%; padding:5px; border:1px solid #999999;">Note: Remember, any person who knowingly makes a false statement or misrepresentation on this form can be subject to penalties including fines, imprisonment, or both.</td></tr></table>



</td>

</tr></table>
<div class="clr mb_15"></div>

<strong>SECTION 4C: INCOME INFORMATION FOR MARRIED BORROWERS FILING JOINTLY</strong><div class="line_border_1"></div><div class="clr"></div>
<table width="100%"><tr>

<td width="50%" valign="top">
<table width="100%">
<tr>
<td valign="top"><strong>13. </strong></td>
<td><strong>Has your income significantly decreased since you filed your last federal income tax return?</strong><br />
For example, have you lost your job or experienced a drop in income?
	<table width="100%">
    	<tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[13] == "1") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>Yes - Skip to Item 15.</td></tr>
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[13] == "2") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>No - Continue to Item 14.</td></tr>
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[13] == "3") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>We haven't filed a federal income tax return in the last two years - Skip to Item 15.</td></tr>
    </table>
</td>
</tr>

<tr>
<td valign="top"><strong>14. </strong></td>
<td><strong>Has your spouse's income significantly decreased since your spouse filed his or her last federal income tax return?</strong><br />
For example, has your spouse lost his or her job or experienced a drop in income?
	<table width="100%">
    	<tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[14] == "1") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>Yes - Continue to Item 15.</td></tr>
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[14] == "2") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>No - Provide your and your spouse's most recent federal income tax return or transcript. Skip to Section 6</td></tr>
    </table>
</td>
</tr>


</table>
</td>

<td width="50%" valign="top">
<table width="100%">
<tr>
<td valign="top"><strong>15. </strong></td>
<td><strong> Do you currently have taxable income?</strong><br />
Check "No" if you do not have any income or receive only untaxed income.
	<table width="100%">
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[15] == "1") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>Yes - You must provide documentation of your income according to the instructions in Section 5. Continue to Item 16.</td></tr>
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[15] == "2") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>No - You are not required to provide documentation of your income. Continue to Item 16.</td></tr>
    </table>
</td>
</tr>


<tr>
<td valign="top"><strong>16. </strong></td>
<td><strong> Does your spouse currently have taxable income?</strong><br />
Check "No" if your spouse does not have any income or receives only untaxed income.
	<table width="100%">
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[16] == "1") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>Yes - Skip to Section 5 and provide documentation of your spouse's income as instructed in that section.</td></tr>
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[16] == "2") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>No - You are not required to provide documentation of your spouse's income. If you selected "Yes" to Item 15, skip to Section 5 and document your income. If you selected "No" to Item 15, skip to Section 6.</td></tr>
    </table>
</td>
</tr>

</table>




</td>

</tr></table>

<div class="clr mb_10"></div>

<table><tr><td width="25%">&nbsp;</td><td style="background:#dfdfdf; width:100%; padding:5px; border:1px solid #999999;"><strong>Note:</strong> Remember, any person who knowingly makes a false statement or misrepresentation on this form can be subject to penalties including fines, imprisonment, or both.</td>	<td width="25%">&nbsp;</td></tr></table>

</td></tr>
<tr><td class="idr_paging">Page 2 of 10</td></tr>
</table>
<div class="pagebreak"></div>


<!--	18C IDR 2021 CODED (PAGE 3)	-->
<table class="page_wrapper mrgn_idr" cellpadding="0" cellspacing="0"><tr><td class="page_wrapper_inner_2 style_2" style="padding:0px 8px;">

<table width="100%" border="0">
<tr>
	<td width="80"><strong>Borrower Name</strong></td>
    <td><input type="text" class="input_5" name="bn" value="<?php echo $fd['name']; ?>" style="width:90%;" /></td>
	<td width="70"><strong>Borrower SSN</strong></td>
    <td width="100"><input type="text" class="input_5" name="bssn" value="<?php echo $fd['ssn']; ?>" /></td>
</tr>
</table>
<div class="clr mb_15"></div>

<strong>SECTION 4D: INCOME INFORMATION FOR MARRIED BORROWERS FILING SEPARATELY</strong><div class="line_border_3"></div><div class="clr"></div>
<table width="100%"><tr>

<td width="50%" valign="top">
<table width="100%">
<tr>
<td valign="top"><strong>17. </strong></td>
<td><strong>Has your income significantly decreased since you filed your last federal income tax return?</strong><br />
For example, have you lost your job or experienced a drop in income?
	<table width="100%">
    	<tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[17] == "1") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>Yes - Continue to Item 18.</td></tr>
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[17] == "2") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>No - Provide your most recent federal income tax return or transcript. Skip to Item 19.</td></tr>
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[17] == "3") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>I haven't filed a federal income tax return in the past two years - Continue to Item 18.</td></tr>
    </table>
</td>
</tr>


<tr>
<td valign="top"><strong>18. </strong></td>
<td><strong> Do you currently have taxable income?</strong><br />
Check "No" if you have no taxable income or receive only untaxed income. After answering, continue to Item 19.
	<table width="100%">
    	<tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[18] == "1") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>Yes - You must provide documentation of your income as instructed in Section 5</td></tr>
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[18] == "2") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>No.</td></tr>
    </table>
</td>
</tr>

</table>

<table style="width:100%; margin-top:25px;"><tr><td style="background:#dfdfdf; width:100%; padding:5px; border:1px solid #999999;"><strong>Note:</strong> Remember, any person who knowingly makes a false statement or misrepresentation on this form can be subject to penalties including fines, imprisonment, or both.</td></tr></table>

</td>

<td width="50%" valign="top">
<table width="100%">
<tr>
<td valign="top"><strong>19. </strong></td>
<td><strong>Has your spouse's income significantly decreased since your spouse filed his or her last federal income tax return?</strong><br />For example, has your spouse lost a job or experienced a drop in income?
	<table width="100%">
    	<tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[19] == "1") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>Yes - Continue to Item 20.</td></tr>
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[19] == "2") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>No - Provide your spouse's most recent federal income tax return or transcript. This information will only be used if you are on or placed on the SAVE (formerly known as REPAYE) Plan. Skip to Section 6.</td></tr>
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[19] == "3") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>My spouse hasn't filed a federal income tax return in the past two years - Continue to Item 20.</td></tr>
    </table>
</td>
</tr>

<tr>
<td valign="top"><strong>20. </strong></td>
<td><strong>Does your spouse currently have taxable income?</strong><br />Check "No" if your spouse has no taxable income or receives only untaxed income.
	<table width="100%">
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[20] == "1") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>Yes - Skip to Section 5 and provide documentation of your spouse's income as instructed in that section. This information will only be used if you are on or placed on the SAVE (formerly known as REPAYE) Plan.</td></tr>
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[20] == "2") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>No - You are not required to provide documentation of your spouse's income. If you selected "Yes" to Item 18, skip to Section 5 and document your income. If you selected "No" to Item 18, skip to Section 6.</td></tr>
    </table>
</td>
</tr>

</table>

</td>

</tr></table>
<div class="clr mb_15"></div>


<strong>SECTION 5: INSTRUCTIONS FOR DOCUMENTING CURRENT INCOME</strong><div class="line_border_3"></div><div class="clr mb_3"></div>
<table width="100%" cellpadding="0" cellspacing="0"><tr><td>
You only need to follow these instructions if, based on your answers in Section 4, you and your spouse (if applicable) were instructed to provide documentation of your current income instead of a tax return or tax transcript.<br />
<div class="clr mb_10"></div>
<strong>This is the income you must document:</strong><br />
<ul style="height:100px; margin:0px; padding:8px 0px 10px 20px;">
<li style="height:25px;">You must provide documentation of all taxable income you and your spouse (if applicable) currently receive.</li>
<li style="height:44px;">Taxable income includes, for example, income from employment, unemployment income, dividend income, interest income, tips, and alimony.</li>
<li>Do not provide documentation of untaxed income such as Supplemental Security Income, child support, or federal or state public assistance.</li>
</ul>

<div class="clr mb_10"></div>

<strong>This is how you document your income:</strong>
<ul style="height:185px; margin:0px; padding:5px 0px 10px 20px;">
<li style="height:25px;">Documentation will usually include a pay stub or letter from your employer listing your gross pay.</li>
<li style="height:25px;">Write on your documentation how often you receive the income, for example, "twice per month" or "every other week."</li>
<li style="height:25px;">You must provide at least one piece of documentation for each source of taxable income.</li>
<li style="height:45px;">If documentation is not available or you want to explain your income, attach a signed statement explaining each source of income and giving the name and the address of each source of income.</li>
<li style="height:43px;"><strong>The date on any supporting documentation you provide must be no older than 90 days from the date you sign this form.</strong></li>
<li>Copies of documentation are acceptable.</li>
</ul>

<strong>After gathering the appropriate documentation, continue to Section 6.</strong>
</td></tr></table>


</td></tr>
<tr><td class="idr_paging">Page 3 of 10</td></tr>
</table>
<div class="pagebreak"></div>


<!--	18C IDR 2021 CODED (PAGE 4)	-->
<table class="page_wrapper mrgn_idr" cellpadding="0" cellspacing="0"><tr><td class="page_wrapper_inner_2 style_2" style="padding:0px 8px;">

<table width="100%" border="0">
<tr>
	<td width="80"><strong>Borrower Name</strong></td>
    <td><input type="text" class="input_5" name="bn" value="<?php echo $fd['name']; ?>" style="width:90%;" /></td>
	<td width="70"><strong>Borrower SSN</strong></td>
    <td width="100"><input type="text" class="input_5" name="bssn" value="<?php echo $fd['ssn']; ?>" /></td>
</tr>
</table>
<div class="clr mb_15"></div>


<strong style="font-size:13px;">SECTION 6: BORROWER REQUESTS, UNDERSTANDINGS, AUTHORIZATION, AND CERTIFICATION</strong><div class="line_border_1"></div><div class="clr"></div>
<table width="100%" cellpadding="0" cellspacing="0"><tr><td style="font-size:13px; line-height:16px;">
If I am requesting an income-driven repayment plan or seeking to change income-driven repayment plans, <strong>I request:</strong><br />
<div class="clr mb_5"></div>
<ul style="height:160px; margin:0px; padding:5px 0px 10px 20px;">
<li style="height:35px;">That my loan holder place me on the plan I selected in Section 2 to repay my eligible Direct Loan or FFEL Program loans held by the holder to which I submit this form.</li>
<li style="height:35px;">If I do not qualify for the plan or plans I requested, or did not make a selection in Item 2, that my loan holder place me on the plan with the lowest monthly payment amount.</li>
<li style="height:35px;">If I selected more than one plan, that my loan holder place me on the plan with the lowest monthly payment amount from the plans that I requested.</li>
<li>If more than one of the plans that I selected provides the same initial payment amount, or if my loan holder is determining which of the income-driven plans I qualify for, that my loan holder use the following order in choosing my plan: SAVE (formerly known as REPAYE) (if my repayment period is 20 years), PAYE, SAVE (formerly known as REPAYE) (if my repayment period is 25 years), IBR, and then ICR.</li>
</ul>

If I am not currently on an income-driven repayment plan, but I did not complete Item 1 or I incorrectly indicated in Item 1 that I was already in an income-driven repayment plan, <strong>I request</strong> that my loan holder treat my request as if I had indicated in Item 1 that I wanted to enter an income-driven repayment plan.
<div class="clr mb_5"></div>
If I am currently repaying my Direct Loans under the IBR plan and I am requesting a change to a different income-driven plan, <strong>I request</strong> a one-month reduced-payment forbearance in the amount of my current monthly IBR payment or $5, whichever is greater (unless I request another amount below or I decline the forbearance), to help me move from IBR to the new income-driven plan I requested.
<div class="clr mb_5"></div>
<table width="100%"><tr><td valign="top" width="15"><input type="checkbox" <?php if (isset($radio['idr_page_4_i_request'])) {if ($radio['idr_page_4_i_request'] == "Yes") {echo ' checked="checked"';}}?> style="margin-top:-3px;" /></td>	<td><strong>I request</strong> a one-month reduced-payment forbearance in the amount of: &nbsp; &nbsp; &nbsp; <input type="text" class="input_2" value="" style="width:100px;" /> (must be at least $5).</td></tr></table>
<div class="clr mb_10"></div>

<strong>I understand</strong> that:
<div class="clr mb_5"></div>

<ul style="height:280px; margin:0px; padding:5px 0px 5px 20px;">
<li style="height:35px;">If I do not provide my loan holder with this completed form and any other required documentation, I will not be placed on the plan that I requested or my request for recertification or recalculation will not be processed.</li>
<li style="height:17px;">I may choose a different repayment plan for any loans that are not eligible for income-driven repayment.</li>
<li style="height:17px;">If I requested a reduced-payment forbearance of less than $5 above, my loan holder will grant my forbearance for $5.</li>
<li style="height:54px;">If I am requesting a change from the IBR Plan to a different income-driven repayment plan, I may decline the one-month reduced payment forbearance described above by contacting my loan holder. If I decline the forbearance, I will be placed on the Standard Repayment Plan and cannot change repayment plans until I make one monthly payment under that plan.</li>
<li style="height:54px;">If I am requesting the ICR plan, my initial payment amount will be the amount of interest that accrues each month on my loan until my loan holder receives the income documentation needed to calculate my payment amount. If I cannot afford the initial payment amount, I may request a forbearance by contacting my loan holder.</li>
<li style="height:35px;">If I am married and I request the ICR plan, my spouse and I have the option of repaying our Direct Loans jointly under this plan. My loan servicer can provide me with information about this option.</li>
<li style="height:35px;">If I have FFEL Program loans, my spouse may be required to give my loan holder access to his or her information in the National Student Loan Data System (NSLDS). If this applies to me, my loan holder will contact me with instructions.</li>
<li>My loan holder may grant me a forbearance while processing my application or to cover any period of delinquency that exists when I submit my application.</li>
</ul>

<strong>I authorize</strong> the entity to which I submit this request and its agents to contact me regarding my request or my loans at any cellular telephone number that I provide now or in the future using automated telephone dialing equipment or artificial or prerecorded voice or text messages.<br />
<strong>I certify</strong> that all of the information I have provided on this form and in any accompanying documentation is true, complete, and correct to the best of my knowledge and belief and that I will repay my loans according to the terms of my promissory note and repayment schedule.
<div class="clr mb_10"></div>

<table width="100%" border="0">
<tr>
	<td width="100"><strong>Borrower's Signature</strong></td>
    <td><div class="input_div_2" style="width:90%;">&nbsp;</div></td>
	<td width="1%"><strong>Date</strong></td>
    <td width="90"><input type="text" class="input_2" value="" /></td>
</tr>
<tr><td colspan="4" style="height:10px;">&nbsp;</td></tr>
<tr>
	<td><strong>Spouse's Signature</strong></td>
    <td><div class="input_div_2" style="width:90%;">&nbsp;</div></td>
	<td><strong>Date</strong></td>
    <td><input type="text" class="input_2" value="" /></td>
</tr>

</table>

</td></tr></table>
<div class="clr mb_15"></div>

<strong>If you are married, your spouse is required to sign this form unless you are separated from your spouse or you're unable to reasonably access your spouse's income information.</strong>


</td></tr>
<tr><td class="idr_paging">Page 4 of 10</td></tr>
</table>
<div class="pagebreak"></div>




<!--	18C IDR 2021 CODED (PAGE 5)	-->
<table class="page_wrapper mrgn_idr" cellpadding="0" cellspacing="0"><tr><td class="page_wrapper_inner_2 style_2" style="padding:0px 8px;">

<strong>SECTION 7: WHERE TO SEND THE COMPLETED FORM</strong><div class="line_border_3"></div><div class="clr"></div>
<table width="100%">
<tr>
	<td width="50%" style="vertical-align:top; padding:2px 15px 0px 0px; border-right:2px solid #666666;">Return the completed form and any documentation to: (If no address is shown, return to your loan holder.)<div class="clr mb_5"></div>
    <textarea class="input_3" style="height:80px;"><?php echo $idr_page_5['textarea'][1]; ?></textarea></td>
    <td width="50%" valign="top" style="padding:2px 0 0 15px;">If you need help completing this form call: (If no phone number is shown, call your loan holder.)<div class="clr mb_5"></div>
    <textarea class="input_3" style="height:80px;"><?php echo $idr_page_5['textarea'][2]; ?></textarea></td>
</tr>

</table>
<div class="clr mb_5"></div>

<strong>SECTION 8: INSTRUCTIONS FOR COMPLETING THE FORM</strong><div class="line_border_3"></div><div class="clr"></div>
<table width="100%"><tr><td>Type or print using dark ink. Enter dates as month-day-year (mm-dd-yyyy). Example: March 14, 2019 = 03-14-2019. Include your name and account number on any documentation that you are required to submit with this form. <strong>Return the completed form and any required documentation to the address shown in Section 7.</strong></td></tr></table>

<div class="clr mb_5"></div>

<strong>SECTION 9: DEFINITIONS</strong><div class="line_border_3"></div><div class="clr"></div>
<table width="100%"><tr>
	<td width="49%" valign="top">
		<strong style="text-decoration:underline;">COMMON DEFINITIONS FOR ALL PLANS:</strong>
        <div class="clr mb_5"></div>

&nbsp; &nbsp; <strong>Capitalization</strong> is the addition of unpaid interest to the principal balance of your loan. This will increase the principal balance and the total cos  of your loan.<div class="clr mb_5"></div>

&nbsp; &nbsp; A <strong>deferment</strong> is a period during which you are entitled to postpone repayment of your loans. Interest is not generally charged to you during a deferment on your subsidized loans. Interest is always charged to you during a deferment on your unsubsidized loans.<div class="clr mb_5"></div>

&nbsp; &nbsp; The <strong>William D. Ford Federal Direct Loan (Direct Loan) Program</strong> includes Direct Subsidized Loans, Direct Unsubsidized Loans, Direct PLUS Loans, and Direct Consolidation Loans.<div class="clr mb_5"></div>

&nbsp; &nbsp; <strong>Family size always</strong> includes you and your children (including unborn children who will be born during the year for which you certify your family size), if the children will receive more than half their support from you.<div class="clr mb_5"></div>

&nbsp; &nbsp; For the PAYE, IBR, and ICR Plans, family size always includes your spouse. For the SAVE (formerly known as REPAYE) plan, family size includes your spouse unless your spouse's income is excluded from the calculation of your payment amount.<div class="clr mb_5"></div>

&nbsp; &nbsp; For all plans, family size also includes other people only if they live with you now, receive more than half their support from you now, and will continue to receive this support for the year that you certify your family size. Support includes money, gifts, loans, housing, food, clothes, car, medical and dental care, and payment of college costs. Your family size may be different from the number of exemptions you claim for tax purposes.<div class="clr mb_5"></div>

&nbsp; &nbsp; The <strong>Federal Family Education Loan (FFEL) Program</strong> includes Federal Stafford Loans (both subsidized and unsubsidized), Federal PLUS Loans, Federal Consolidation Loans, and Federal Supplemental Loans for Students (SLS).<div class="clr mb_5"></div>

&nbsp; &nbsp; A <strong>forbearance</strong> is a period during which you are permitted to postpone making payments temporarily, allowed an extension of time for making payments, or temporarily allowed to make smaller payments than scheduled.
    </td>
    <td width="2%">&nbsp;</td>
    <td width="49%" valign="top">
&nbsp; &nbsp; The <strong>holder</strong> of your Direct Loans is the U.S. Department of
Education (the Department). The holder of your FFEL Program
loans may be a lender, secondary market, guaranty agency, or
the Department. Your loan holder may use a servicer to
handle billing, payment, repayment options, and other
communications. References to "your loan holder" on this
form mean either your loan holder or your servicer.<div class="clr mb_5"></div>

&nbsp; &nbsp; A <strong>partial financial hardship</strong> is an eligibility requirement
for the PAYE and IBR plans. You have a partial financial
hardship when the annual amount due on all of your eligible
loans (and, if you are required to provide documentation of
your spouse's income, the annual amount due on your
spouse's eligible loans) exceeds what you would pay under
PAYE or IBR.<div class="clr mb_5"></div>

&nbsp; &nbsp; The annual amount due is calculated based on the greater
of <strong>(1)</strong> the total amount owed on eligible loans at the time
those loans initially entered repayment, or <strong>(2)</strong> the total
amount owed on eligible loans at the time you initially
request the PAYE or IBR plan. The annual amount due is
calculated using a standard repayment plan with a 10-year
repayment period, regardless of loan type. When determining
whether you have a partial financial hardship for the PAYE
plan, the Department will include any FFEL Program loans
that you have into account even though those loans are not
eligible to be repaid under the PAYE plan, except for: <strong>(1)</strong> a
FFEL Program loan that is in default, <strong>(2)</strong> a Federal PLUS Loan
made to a parent borrower, or <strong>(3)</strong> a Federal Consolidation
Loan that repaid a Federal or Direct PLUS Loan made to a
parent borrower.<div class="clr mb_5"></div>

&nbsp; &nbsp; The <strong>poverty guideline amount</strong> is the figure for your
state and family size from the poverty guidelines
published annually by the U.S. Department of Health and
Human Services (HHS) at <a href="https://aspe.hhs.gov/" target="_blank">aspe.hhs.gov/povertyguidelines.</a> If you are not a resident of a state identified in
the poverty guidelines, your poverty guideline amount is
the amount used for the 48 contiguous states.<div class="clr mb_5"></div>

&nbsp; &nbsp; The <strong>standard repayment plan</strong> has a fixed monthly
payment amount over a repayment period of up to 10
years for loans other than Direct or Federal Consolidation
Loans, or up to 30 years for Direct and Federal
Consolidation Loans.
    </td>
</tr></table>

<div class="clr mb_15"></div>


</td></tr>
<tr><td class="idr_paging">Page 5 of 10</td></tr>
</table>
<div class="pagebreak"></div>


<!--	18C IDR 2021 CODED (PAGE 6)	-->
<table class="page_wrapper mrgn_idr" cellpadding="0" cellspacing="0"><tr><td class="page_wrapper_inner_2 style_2" style="padding:0px 8px;">

<strong>SECTION 9: DEFINITIONS (CONTINUED)</strong><div class="line_border_3"></div><div class="clr"></div>
<table width="100%"><tr>
	<td width="49%" valign="top">
		<strong style="text-decoration:underline;">DEFINITIONS FOR THE SAVE (formerly known as REPAYE) PLAN:</strong>
        <div class="clr mb_5"></div>

&nbsp; &nbsp; The <strong>SAVE (formerly known as REPAYE) plan</strong> is a repayment plan with monthly payments that are generally equal to 10% of your discretionary income, divided by 12.<div class="clr mb_5"></div>

&nbsp; &nbsp; <strong>Discretionary income for the SAVE (formerly known as REPAYE) plan</strong> is the
amount by which your income exceeds 150% of the
poverty guideline amount.<div class="clr mb_5"></div>

&nbsp; &nbsp; <strong>Eligible loans for the SAVE (formerly known as REPAYE) plan</strong> are Direct Loan
Program loans other than: <strong>(1)</strong> a loan that is in default, <strong>(2)</strong>
a Direct PLUS Loan made to a parent borrower, or <strong>(3)</strong> a
Direct Consolidation Loan that repaid a Direct or Federal
PLUS Loan made to a parent borrower.<div class="clr mb_15"></div>

<strong style="text-decoration:underline;">DEFINITIONS FOR THE PAYE PLAN:</strong><div class="clr mb_5"></div>

&nbsp; &nbsp; The <strong>Pay As You Earn (PAYE) plan</strong> is a repayment
plan with monthly payments that are generally equal to
10% of your discretionary income, divided by 12.<div class="clr mb_5"></div>

&nbsp; &nbsp; <strong>Discretionary income for the PAYE plan</strong> is the
amount by which your income exceeds 150% of the
poverty guideline amount.<div class="clr mb_5"></div>

&nbsp; &nbsp; <strong>Eligible loans for the PAYE plan</strong> are Direct Loan
Program loans other than: <strong>(1)</strong> a loan that is in default, <strong>(2)</strong> a Direct PLUS Loan made to a parent borrower, or <strong>(3)</strong> a
Direct Consolidation Loan that repaid a Direct or Federal
PLUS Loan made to a parent borrower.<div class="clr mb_5"></div>

&nbsp; &nbsp; You are a <strong>new borrower for the PAYE plan</strong> if: <strong>(1)</strong> you
have no outstanding balance on a Direct Loan or FFEL
Program loan as of October 1, 2007 or have no
outstanding balance on a Direct Loan or FFEL Program
loan when you obtain a new loan on or after October 1,
2007, and <strong>(2)</strong> you receive a disbursement of an eligible
loan on or after October 1, 2011, or you receive a Direct
Consolidation Loan based on an application received on
or after October 1, 2011.<div class="clr mb_15"></div>

<strong style="text-decoration:underline;">DEFINITIONS FOR THE IBR PLAN:</strong><div class="clr mb_5"></div>

&nbsp; &nbsp; The <strong>Income-Based Repayment (IBR) plan</strong> is a
repayment plan with monthly payments that are
generally equal to 15% (10% if you are a new borrower)
of your discretionary income, divided by 12.<div class="clr mb_5"></div>

&nbsp; &nbsp; <strong>Discretionary income for the IBR plan</strong> is the amount
by which your adjusted gross income exceeds 150% of
the poverty guideline amount.<div class="clr mb_5"></div>

&nbsp; &nbsp; <strong>Eligible loans for the IBR plan</strong> are Direct Loan and
FFEL Program loans other than: <strong>(1)</strong> a loan that is in
default, <strong>(2)</strong> a Direct or Federal PLUS Loan made to a
parent borrower, or <strong>(3)</strong> a Direct or Federal Consolidation
Loan that repaid a Direct or Federal PLUS Loan made to a
parent borrower.<div class="clr mb_5"></div>

&nbsp; &nbsp; You are a <strong>new borrower for the IBR plan</strong> if <strong>(1)</strong> you
have no outstanding balance on a Direct Loan or FFEL
Program loan as of July 1, 2014 or <strong>(2)</strong> have no
outstanding balance on a Direct Loan or FFEL Program
loan when you obtain a new loan on or after July 1, 2014.
    </td>
    <td width="2%">&nbsp;</td>
    <td width="49%" valign="top">
<strong style="text-decoration:underline;">DEFINITIONS FOR THE ICR PLAN:</strong>
        <div class="clr mb_5"></div>
&nbsp; &nbsp; The <strong>Income-Contingent Repayment (ICR) plan</strong> is a
repayment plan with monthly payments that are the
lesser of <strong>(1)</strong> what you would pay on a repayment plan
with a fixed monthly payment over 12 years, adjusted
based on your income or <strong>(2)</strong> 20% of your discretionary
income divided by 12.<div class="clr mb_5"></div>

&nbsp; &nbsp; <strong>Discretionary income for the ICR plan</strong> is the amount
by which your adjusted gross income exceeds the
poverty guideline amount for your state of residence and
family size.<div class="clr mb_5"></div>

&nbsp; &nbsp; <strong>Eligible loans for the ICR plan</strong> are Direct Loan Program
loans other than: <strong>(1)</strong> a loan that is in default, <strong>(2)</strong> a Direct PLUS
Loan made to a parent borrower, or <strong>(3)</strong> a Direct PLUS
Consolidation Loan (based on an application received prior to
July 1, 2006 that repaid Direct or Federal PLUS Loans made to
a parent borrower). However, a Direct Consolidation Loan
made based on an application received on or after July 1,
2006 that repaid a Direct or Federal PLUS Loan made to a
parent borrower is eligible for the ICR plan.
    </td>
</tr></table>


<div class="clr mb_15"></div>


</td></tr>
<tr><td class="idr_paging">Page 6 of 10</td></tr>
</table>
<div class="pagebreak"></div>


<!--	18C IDR 2021 CODED (PAGE 7)	-->
<table class="page_wrapper mrgn_idr" cellpadding="0" cellspacing="0" style="font-size:12px;"><tr><td width="100%">

<div style="width:940px; height:940px; transform: rotate(270deg);">
<table width="100%" cellpadding="0" cellspacing="0"><tr><td><div class="clr mb_15"></div>
<strong style="font-size:14px;">SECTION 10: INCOME-DRIVEN PLAN ELIGIBILITY REQUIREMENTS AND GENERAL INFORMATION</strong><div class="line_border_1"></div><div class="clr"></div>
<strong>Table 1. Income-Driven Plan Eligibility Requirements and General Information</strong><div class="clr mb_10"></div>
</td></tr></table>

<table width="100%" cellpadding="3" cellspacing="0" border="1">
<tr><td colspan="5" style="height:20px;">&nbsp;</td></tr>
<tr style="background:#666666; color:#FFFFFF; font-weight:bold;">
	<td>Plan Feature</td>
    <td>SAVE (formerly known as REPAYE)</td>
    <td>PAYE</td>
    <td>IBR</td>
    <td>ICR</td>
</tr>

<tr style="background:#EEEEEE;">
	<td><strong>Payment Amount</strong></td>
    <td>Generally, 10% of discretionary income.</td>
    <td>Generally, 10% of discretionary income.</td>
    <td>Never more than 15% of discretionary income.</td>
    <td>Lesser of 20% of discretionary income or what you would pay under a repayment plan with fixed payments over 12 years, adjusted based on your income.</td>
</tr>

<tr>
	<td><strong>Cap on Payment Amount</strong></td>
    <td>None. Your payment may exceed what you would have paid under the 10-year standard repayment plan.</td>
    <td>What you would have paid under the 10-year standard repayment plan when you entered the plan.</td>
    <td>What you would have paid under the 10-year standard repayment plan when you entered the plan.</td>
    <td>None. Your payment may exceed what you would have paid under the 10-year standard repayment plan.</td>
</tr>


<tr style="background:#EEEEEE;">
	<td><strong>Married Borrowers</strong></td>
    <td>Your payment will be based on the combined income and loan debt of you and your spouse regardless of whether you file a joint or separate Federal income tax return, unless you and your spouse <strong>(1)</strong> are separated or <strong>(2)</strong> you are unable to reasonably access your spouse's income information.</td>
    <td>Your payment will be based on the combined income and loan debt of you and your spouse only if you file a joint Federal income tax return, unless you and your spouse <strong>(1)</strong> are separated or <strong>(2)</strong> you are unable to reasonably access your spouse's income information.</td>
    <td>Your payment will be based on the combined income and loan debt of you and your spouse only if you file a joint Federal income tax return, unless you and your spouse <strong>(1)</strong> are separated or <strong>(2)</strong> you are unable to reasonably access your spouse's income information.</td>
    <td>Your payment will be based on the combined income of you and your spouse only if you file a joint Federal income tax return, unless you and your spouse <strong>(1)</strong> are separated or <strong>(2)</strong> you are unable to reasonably access your spouse's income information.</td>
</tr>


<tr>
	<td><strong>Borrower Responsibility for Interest</strong></td>
    <td>On subsidized loans, you do not have to pay the difference between your monthly payment amount and the interest that accrues for your first 3 consecutive years in the plan. On subsidized loans after this period and on unsubsidized loans during all periods, you only have to pay half the difference between your monthly payment amount and the interest that accrues.</td>
    <td>On subsidized loans, you do not have to pay the difference between your monthly payment amount and the interest that accrues for your first 3 consecutive years in the plan. </td>
    <td>On subsidized loans, you do not have to pay the difference between your monthly payment amount and the interest that accrues for your first 3 consecutive years of in the plan.</td>
    <td>You are responsible for paying all of the interest that accrues.</td>
</tr>


<tr style="background:#EEEEEE;">
	<td><strong>Forgiveness Period</strong></td>
    <td>If you only have eligible loans that you received for undergraduate study, any remaining balance is forgiven after 20 years of qualifying repayment. If you have any eligible loans that you received for graduate or professional study, any remaining balance is forgiven after 25 years of qualifying repayment on all of your loans. Forgiveness may be taxable.</td>
    <td>Any remaining balance is forgiven after 20 years of qualifying repayment, and may be taxable.</td>
    <td>Any remaining balance is forgiven after no more than 25 years of qualifying repayment, and may be taxable.</td>
    <td>Any remaining balance is forgiven after 25 years of qualifying repayment, and may be taxable.</td>
</tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" style="margin-top:10px;"><tr><td class="idr_paging">Page 7 of 10</td></tr></table>
<div class="clr"></div>
</div>

</td></tr></table>
<div class="pagebreak"></div>


<!--	18C IDR 2021 CODED (PAGE 8)	-->
<table class="page_wrapper mrgn_idr" cellpadding="0" cellspacing="0" style="font-size:12px;"><tr><td width="100%">

<div style="width:940px; height:940px; transform: rotate(270deg);">
<div class="clr mb_10"></div>
<table width="100%" cellpadding="3" cellspacing="0" border="1">
<tr><td colspan="5"><strong style="font-size:14px;">SECTION 10: INCOME-DRIVEN PLAN ELIGIBILITY REQUIREMENTS AND GENERAL INFORMATION</strong><div class="line_border_1"></div><div class="clr"></div></td></tr>
<tr style="background:#666666; color:#FFFFFF; font-weight:bold;">
	<td>Plan Feature</td>
    <td>SAVE (formerly known as REPAYE)</td>
    <td>PAYE</td>
    <td>IBR</td>
    <td>ICR</td>
</tr>

<tr>
	<td valign="top"><strong>Income Eligibility</strong></td>
    <td valign="top">None.</td>
    <td valign="top">You must have a "partial financial hardship".</td>
    <td valign="top">You must have a "partial financial hardship".</td>
    <td valign="top">None.</td>
</tr>

<tr style="background:#EEEEEE;">
	<td valign="top"><strong>Borrower Eligibility</strong></td>
    <td valign="top">You must be a Direct Loan borrower with eligible loans.</td>
    <td valign="top">You must be a "new borrower" with eligible Direct Loans.</td>
    <td valign="top">You must be a Direct Loan or FFEL borrower with eligible loans.</td>
    <td valign="top">You must be a Direct Loan borrower with eligible loans.</td>
</tr>

<tr>
	<td valign="top"><strong>Recertify Income and Family Size</strong></td>
    <td valign="top">Annually. Failure to submit documentation by the deadline will result in capitalization of interest and increasing your payment to ensure that your loan is paid in full over the lesser of 10 or the remainder of 20 or 25 years.</td>
    <td valign="top">Annually. Failure to submit documentation by the deadline may result in the capitalization of interest and will increase the payment amount to the 10-year standard payment amount.</td>
    <td valign="top">Annually. Failure to submit documentation by the deadline will result in the capitalization of interest and increase in payment amount to the 10-year standard payment amount.</td>
    <td valign="top">Annually. Failure to submit documentation by the deadline will result in the recalculation of your payment amount to be the 10-year standard payment amount.</td>
</tr>

<tr style="background:#EEEEEE;">
	<td valign="top"><strong>Leaving the Plan</strong></td>
    <td valign="top">At any time, you may change to any other repayment plan for which you are eligible.</td>
    <td valign="top">At any time, you may change to any other repayment plan for which you are eligible.</td>
    <td valign="top">If you want to leave the plan, you will be placed on the standard repayment plan. You may not change plans until you have made one payment under that plan or a reduced-payment forbearance.</td>
    <td valign="top"> At any time, you may change to any other repayment plan for which you are eligible.</td>
</tr>

<tr>
	<td valign="top"><strong>Interest Capitalization</strong></td>
    <td valign="top">Interest is capitalized when you are removed from the plan for failing to recertify your income by the deadline or when you voluntarily leave the plan.</td>
    <td valign="top">If you are determined to no longer have a "partial financial hardship" or if you fail to recertify your income by the deadline, interest is capitalized until the outstanding principal balance on your loans is 10% greater than it was when you entered the plan. It is also capitalized if you leave the plan.</td>
    <td valign="top">If you are determined to no longer have a "partial financial hardship", fail to recertify your income by the deadline, or leave the plan, interest is capitalized.</td>
    <td valign="top">Interest that accrues when your payment amount is less than accruing interest on your loans is capitalized annually until the outstanding principal balance on your loans is 10% greater than it was when your loans entered repayment.</td>
</tr>

<tr style="background:#EEEEEE;">
	<td valign="top"><strong>Re-Entering the Plan</strong></td>
    <td valign="top">Your loan holder will compare the total of what you would have paid under SAVE (formerly known as REPAYE) to the total amount you were required to pay after you left SAVE (formerly known as REPAYE). If the difference between the two shows that you were required to paid less by leaving SAVE (formerly known as REPAYE), your new SAVE (formerly known as REPAYE) payment will be increased. The increase is equal to the difference your loan holder calculated, divided by the number of months remaining in the 20- or 25-year forgiveness period.</td>
    <td valign="top">You must again show that you have a "partial financial hardship".</td>
    <td valign="top">You must again show that you have a "partial financial hardship".</td>
    <td valign="top">No restrictions.</td>
</tr>

</table>

<table width="100%" cellpadding="0" cellspacing="0" style="margin-top:10px;"><tr><td class="idr_paging">Page 8 of 10</td></tr></table>
<div class="clr"></div>
</div>

</td></tr></table>
<div class="pagebreak"></div>


<!--	18C IDR 2021 CODED (PAGE 9)	-->
<table class="page_wrapper mrgn_idr" cellpadding="0" cellspacing="0"><tr><td class="page_wrapper_inner_2 style_2" style="padding:0px 8px;">

<strong>SECTION 11: SAMPLE PAYMENT AMOUNTS</strong><div class="line_border_3"></div><div class="clr"></div>
The tables below provide repayment estimates under the traditional and income-driven repayment plans. These figures are estimates based on an interest rate of 6%, the average Direct Loan interest rate for undergraduate and graduate borrowers. The figures also assume a family size of 1, that you live in the continental U.S., and that your income increases 5% each year. Various factors, including your interest rate, your loan debt, your income, if and how quickly your income rises, and when you started borrowing may cause your repayment to differ from the estimates shown in these tables. These figures use the 2016 Poverty Guidelines and Income Percentage Factors.<div class="clr mb_10"></div>


<div class="clr mb_20"></div>

<strong>Table 2. Non-Consolidation, Undergraduate Loan Debt of $30,000 in Direct Unsubsidized Loans and Starting Income of $25,000</strong>
<div class="clr mb_15"></div>

<table width="100%" cellpadding="2" cellspacing="0" border="1">
<tr align="center" style="background:#666666; color:#FFFFFF; font-weight:bold;">
	<td>Repayment Plan</td>
    <td>Initial Payment</td>
    <td>Final Payment</td>
    <td>Time in Repayment</td>
    <td>Total Paid</td>
    <td>Loan Forgiveness</td>
</tr>
<?php
$tmp_arr = [
					"Standard" => ["$333", "$333", "10 years", "$33,967", "N/A"],
					"Graduated" => ["$190", "$571", "10 years", "$42,636", "N/A"],
					"Extended-Fixed" => ["Ineligible", "-", "-", "-", "-"],
					"Extended-Graduated" => ["Ineligible", "-", "-", "-", "-"],
					"PAYE" => ["$60", "$296", "20 years", "$38,105", "$27,823"],
					"SAVE (formerly known as REPAYE)" => ["$60", "$296", "20 years", "$38,105", "$24,253"],
					"IBR" => ["$90", "$333", "21 years, 10 months", "$61,006", "$0"],
					"ICR" => ["$195", "$253", "19 years, 6 months", "$52,233", "$0"],
				];

				$tmp_bg = "";
				foreach ($tmp_arr as $k => $rows) {if ($tmp_bg == '') {$tmp_bg = "background:#EEEEEE;";} else { $tmp_bg = "";}?>
<tr style=" <?php echo $tmp_bg; ?>"><td><strong><?php echo $k; ?></strong></td> <?php foreach ($rows as $row) {?><td><?php echo $row; ?></td><?php }?>	</tr>
<?php }?>

</table>

<div class="clr mb_20"></div>
<div class="clr mb_20"></div>

<strong>Table 3. Non-Consolidation, Graduate Loan Debt of $60,000 in Direct Unsubsidized Loans and Starting Income of $40,000</strong>
<div class="clr mb_15"></div>

<table width="100%" cellpadding="3" cellspacing="0" border="1">
<tr align="center" style="background:#666666; color:#FFFFFF; font-weight:bold;">
	<td>Repayment Plan</td>
    <td>Initial Payment</td>
    <td>Final Payment</td>
    <td>Time in Repayment</td>
    <td>Total Paid</td>
    <td>Loan Forgiveness</td>
</tr>
<?php
$tmp_arr = [
					"Standard" => ["$666", "$666", "10 years", "$79,935", "N/A"],
					"Graduated" => ["$381", "$1,143", "10 years", "$85,272", "N/A"],
					"Extended-Fixed" => ["$437", "$437", "25 years", "$130,974", "N/A"],
					"Extended-Graduated" => ["$300", "$582", "25 years", "$126,168", "N/A"],
					"PAYE" => ["$185", "$612", "20 years", "$87,705", "$41,814"],
					"SAVE (formerly known as REPAYE)" => ["$185", "$816", "25 years", "$131,444", "$0"],
					"IBR" => ["$277", "$666", "18 years, 3 months", "$107,905", "$0"],
					"ICR" => ["$469", "$588", "13 years, 9 months", "$89,468", "$0"],
				];

				$tmp_bg = "";
				foreach ($tmp_arr as $k => $rows) {if ($tmp_bg == '') {$tmp_bg = "background:#EEEEEE;";} else { $tmp_bg = "";}?>
<tr style=" <?php echo $tmp_bg; ?>"><td><strong><?php echo $k; ?></strong></td> <?php foreach ($rows as $row) {?><td><?php echo $row; ?></td><?php }?>	</tr>
<?php }?>
</table>


</td></tr>
<tr><td class="idr_paging">Page 9 of 10</td></tr>
</table>
<div class="pagebreak"></div>


<!--	18C IDR 2021 CODED (PAGE 10)	-->
<table class="page_wrapper mrgn_idr" cellpadding="0" cellspacing="0"><tr><td class="page_wrapper_inner_2 style_2" style="padding:0px 8px;">

<strong>SECTION 12: IMPORTANT NOTICES</strong><div class="line_border_3"></div><div class="clr"></div>
<table width="100%"><tr>
	<td width="49%" valign="top">
&nbsp; &nbsp; &nbsp; <strong>Privacy Act Notice.</strong> The Privacy Act of 1974 (5 U.S.C. 552a) requires that the following notice be provided to you:
<div class="clr mb_5"></div>
The authorities for collecting the requested information from and about you are &#167;421 et seq. and &#167;451 et seq. of the Higher Education Act of 1965, as amended (20 U.S.C. 1071 et seq. and 20 U.S.C. 1087a et seq.), and the authorities for collecting and using your Social Security
Number (SSN) are &#167;&#167;428B(f) and 484(a)(4) of the HEA (20
U.S.C. 1078-2(f) and 1091(a)(4)) and 31 U.S.C. 7701(b).
Participating in the Federal Family Education Loan (FFEL)
Program or the William D. Ford Federal Direct Loan (Direct
Loan) Program and giving us your SSN are voluntary, but
you must provide the requested information, including your
SSN, to participate.
<div class="clr mb_15"></div>
&nbsp; &nbsp; &nbsp; The principal purposes for collecting the
information on this form, including your SSN, are to verify
your identity, to determine your eligibility to receive a loan
or a benefit on a loan (such as a deferment, forbearance,
discharge, or forgiveness) under the FFEL and/or Direct
Loan Programs, to permit the servicing of your loans, and, if
it becomes necessary, to locate you and to collect and
report on your loans if your loans become delinquent or
default. We also use your SSN as an account identifier and to
permit you to access your account information
electronically.
<div class="clr mb_15"></div>
&nbsp; &nbsp; &nbsp; The information in your file may be disclosed, on a
case-by-case basis or under a computer matching program,
to third parties as authorized under routine uses in the
appropriate systems of records notices. The routine uses of
this information include, but are not limited to, its disclosure
to federal, state, or local agencies, to private parties such as
relatives, present and former employers, business and
personal associates, to consumer reporting agencies, to
financial and educational institutions, and to guaranty
agencies in order to verify your identity, to determine your
eligibility to receive a loan or a benefit on a loan, to permit
the servicing or collection of your loans, to enforce the
terms of the loans, to investigate possible fraud and to verify
compliance with federal student financial aid program
regulations, or to locate you if you become delinquent in
your loan payments or if you default. To provide default rate
calculations, disclosures may be made to guaranty agencies,
to financial and educational institutions, or to state
agencies. To provide financial aid history information,
disclosures may be made to educational institutions.
    </td>
    <td width="2%">&nbsp;</td>
    <td width="49%" valign="top">
&nbsp; &nbsp; &nbsp; To assist program administrators with tracking
refunds and cancellations, disclosures may be made to
guaranty agencies, to financial and educational institutions,
or to federal or state agencies. To provide a standardized
method for educational institutions to efficiently submit
student enrollment statuses, disclosures may be made to
guaranty agencies or to financial and educational
institutions. To counsel you in repayment efforts, disclosures
may be made to guaranty agencies, to financial and
educational institutions, or to federal, state, or local
agencies.
<div class="clr mb_15"></div>
&nbsp; &nbsp; &nbsp; In the event of litigation, we may send records to
the Department of Justice, a court, adjudicative body,
counsel, party, or witness if the disclosure is relevant and
necessary to the litigation. If this information, either alone or
with other information, indicates a potential violation of
law, we may send it to the appropriate authority for action.
We may send information to members of Congress if you
ask them to help you with federal student aid questions. In
circumstances involving employment complaints,
grievances, or disciplinary actions, we may disclose relevant
records to adjudicate or investigate the issues. If provided
for by a collective bargaining agreement, we may disclose
records to a labor organization recognized under 5 U.S.C.
Chapter 71. Disclosures may be made to our contractors for
the purpose of performing any programmatic function that
requires disclosure of records. Before making any such
disclosure, we will require the contractor to maintain Privacy
Act safeguards. Disclosures may also be made to qualified
researchers under Privacy Act safeguards.
<div class="clr mb_15"></div>
&nbsp; &nbsp; &nbsp; <strong>Paperwork Reduction Notice.</strong> According to the
Paperwork Reduction Act of 1995, no persons are required
to respond to a collection of information unless it displays a
valid Office of Management and Budget (OMB) control
number. The valid OMB control number for this information
collection is 1845-0102. Public reporting burden for this
collection of information is estimated to average 20 minutes
(0.33 hours) per response, including time for reviewing
instructions, searching existing data sources, gathering and
maintaining the data needed, and completing and
reviewing the information collection. Individuals are
obligated to respond to this collection to obtain a benefit in
accordance with 34 CFR 682.215, 685.209, or 685.221.
<div class="clr mb_15"></div>
&nbsp; &nbsp; &nbsp; <strong>If you have comments or concerns regarding the
status of your individual submission of this form, please
contact your loan holder directly (see Section 7).</strong>
    </td>
</tr></table>


<div class="clr mb_15"></div>

</td></tr>
<tr><td class="idr_paging">Page 10 of 10</td></tr>
</table>




</div>










<?php	if ($intkR['intake_title'] == "IDR Intake") {
					?>



<?php
for ($ia = 0; $ia <= 4; $ia++) {
						$limit_start = ($i * 4);
						$limit = "$limit_start,4";
						$lrows = $this->default_model->get_arrby_tbl('nslds_loans', '*', "client_id='" . $client_id . "' and loan_type like '%CONSOLIDA%' and (loan_outstanding_principal_balance>'0' or loan_outstanding_interest_balance>'0') order by loan_date desc", $limit);

						if (count($lrows) > 0) {

							?>

<div class="pagebreak"></div>
<!-- 18D Additional Loan Listing Coded 2022-02-18 -->
<table class="page_wrapper" cellpadding="0" cellspacing="0" style="margin:-25px -5px; z-index:999999;">
<tr>
<td>
<table width="100%" border="0" style="border:0px;">
<tr style="border:0px;">
<td width="6.7%" valign="top"><img src="<?php echo 'data:image/png;base64,' . base64_encode(file_get_contents(("assets/img/logo_doe.png"))); ?>" width="70" alt="Logo" /></td>
<td width="73.3%" valign="top" style="padding-left:10px; font-size:13.5pt; font-weight:bold; font-style:normal; font-family:Calibri, sans-serif;">Direct Consolidation Loan Application and Promissory Note<br />William D. Ford Federal Direct Loan Program</td>
<td width="20%" valign="top"><div style="float:right; font-family:Trebuchet MS; font-size:10pt;">OMB No. 1845-0053<br />Form Approved<br />Exp. Date 07/31/2022</div></td>
</tr>
</table>
<div class="clr"></div>
<table width="100%">
<tr><td class="style_1"><strong>WARNING:</strong> Any person who knowingly makes a false statement or misrepresentation on this form or any accompanying document is subject to penalties that may include fines, imprisonment, or both, under the U.S. Criminal Code and 20 U.S.C. 1097</td></tr>
<tr><td class="style_1"><strong>BEFORE YOU BEGIN</strong><div class="line_border_1"></div><div class="clr"></div></td></tr>

<tr><td class="style_1">Read the Instructions for Completing the Direct Consolidation Loan Application and Promissory Note ("Instructions"). Use this form only if you need
additional space to list loans in the <strong>Loans You Want to Consolidate</strong> section or the <strong>Loans You Do Not Want to Consolidate</strong> section of your Note</strong></td></tr>
<tr><td class="style_1" style="padding-top:10px;"><strong>BORROWER INFORMATION</strong><div class="line_border_1"></div><div class="clr"></div></td></tr>

</table>


<table width="100%" border="0">
<tr>
	<td class="font-10" width="10%" valign="bottom">Last Name: </td>
    <td class="input_td"><?php echo $user['lname']; ?></td>
    <td class="font-10" width="9%" valign="bottom">First Name: </td>
    <td class="input_td"><?php echo $user['name']; ?></td>
    <td class="font-10" width="10%" valign="bottom">Middle Initial: </td>
    <td class="input_td"><?php echo $ansR[1]['intake_comment_body']; ?></td>
</tr>
</table>


<table width="100%" border="0"><tr>
<td class="font-10" width="18%" valign="bottom">Social Security Number: </td>
<td class="input_td" align="center"><?php echo $ssn[0]; ?></td><td width="1%">-</td>
<td class="input_td" align="center"><?php echo $ssn[1]; ?></td><td width="1%">-</td>
<td class="input_td" align="center"><?php echo $ssn[2]; ?></td><td width="55%">&nbsp;</td>
</tr></table><div class="clr mb_20"></div>





<strong>LOANS YOU WANT TO CONSOLIDATE</strong> <div class="line_border_1"></div> <div class="clr mb_5"></div>

<table width="100%"><tr><td style="font-size:11px; line-height:16px;">List each federal education loan that you want to consolidate, including any Direct Loan Program loans that you want to include in your Direct
Consolidation Loan. List each loan separately. We will send you a notice before we consolidate your loans. This notice will (1) provide you with
information about the loans and payoff amounts that we have verified, and (2) tell you the deadline by which you must notify us if you want to cancel the
Direct Consolidation Loan, or if you do not want to consolidate one or more of the loans listed in the notice. The notice will include information about
loans that you listed in this section. If you have additional loans with a holder of a loan that you listed in this section, the notice may also include
information about those additional loans. <strong>See the Instructions for more information about the notice we will send. IN THIS SECTION, LIST ONLY
LOANS THAT YOU WANT TO CONSOLIDATE.</strong></td></tr></table> <div class="clr mb_8"></div>


<table width="100%" cellpadding="4" cellspacing="0" border="1">
<tr style="font-size:12px; text-align:center;">
	<td><strong>13.</strong> Loan Code (see Instructions)</td>
    <td><strong>14.</strong> Loan Holder/Servicer Name, Address, and Area Code/ Telephone Number (see Instructions)</td>
    <td><strong>15.</strong> Loan Account Number</td>
    <td><strong>16.</strong> Estimated Payoff Amount</td>
</tr>
<?php
foreach ($lrows as $lrow) {
								$loan_type = strtoupper(trim($lrow['loan_type']));
								$loan_code_arr = $this->array_model->loan_type_code();
								?>
<tr style="font-size:10px; vertical-align:middle; text-align:center;">
<td align="center" style="padding:3px; background:transparent;"><?php echo $loan_code_arr[$loan_type]; ?></td>
<td style="padding:3px; background:transparent;"><?php echo $lrow['loan_contact_name'] . "<br />" . $lrow['loan_contact_street_address_1'] . " " . $lrow['loan_contact_street_address_1'] . " " . $lrow['loan_contact_city'] . " " . $lrow['loan_contact_state'] . " " . $lrow['loan_contact_zip_code']; ?></td>
<td style="padding:3px; background:transparent;"></td>
<td style="padding:3px; background:transparent;"><?php echo $fmt->formatCurrency(($lrow['loan_outstanding_principal_balance'] + $lrow['loan_outstanding_interest_balance']), "USD"); ?></td>
</tr>
<?php }?>

<?php for ($i = count($lrows); $i < 4; $i++) {?>
<tr style="font-size:13px; vertical-align:top;">
<td align="center" style="vertical-align:middle; padding:3px; background:transparent;">&nbsp;</td>
<td style="padding:3px; background:transparent;">&nbsp;</td>
<td style="padding:3px; background:transparent;">&nbsp;</td>
<td style="padding:3px; background:transparent;">&nbsp;</td>
</tr>
<?php }?>
</table> <div class="clr mb_20"></div>


<strong>LOANS YOU DO NOT WANT TO CONSOLIDATE</strong> <div class="line_border_1"></div> <div class="clr mb_5"></div>
<table width="100%"><tr><td style="font-size:11px; line-height:16px;">List all education loans that you are not consolidating, but want us to consider when we calculate the maximum repayment period for your Direct Consolidation Loan (see Item 11 of the Borrower's Rights and Responsibilities Statement that accompanies your Note). Remember to include any Direct Loan Program loans that you do not want to consolidate. List each loan separately. We will send you a notice before we consolidate your loans. This notice will (1) provide you with information about the loans and payoff amounts that we have verified, and (2) tell you the deadline by which you must notify us if you want to cancel the Direct Consolidation Loan, or if you do not want to consolidate one or more of the loans listed in the notice. The notice may also include information about any loans you listed in this section, but these loans listed will not be consolidated. <strong>See the Instructions for more information about the notice we will send. IN THIS SECTION, LIST ONLY LOANS THAT YOU DO NOT WANT TO CONSOLIDATE.</strong> </td></tr></table> <div class="clr mb_8"></div>


<table width="100%" cellpadding="4" cellspacing="0" border="1">
<tr style="font-size:12px; text-align:center;">
	<td><strong>18.</strong> Loan Code (see Instructions)</td>
    <td><strong>19.</strong> Loan Holder/Servicer Name, Address, and Area Code/ Telephone Number (see Instructions)</td>
    <td><strong>20.</strong> Loan Account Number</td>
    <td><strong>21.</strong> Estimated Payoff Amount</td>
</tr>


<?php for ($i = 0; $i < 4; $i++) {?>
<tr style="font-size:13px; vertical-align:top;">
<td align="center" style="vertical-align:middle; padding:3px; background:transparent;">&nbsp;</td>
<td style="padding:3px; background:transparent;">&nbsp;</td>
<td style="padding:3px; background:transparent;">&nbsp;</td>
<td style="padding:3px; background:transparent;">&nbsp;</td>
</tr>
<?php }?>
</table>


</td>
</tr>
</table>
<?php }}?>


<?php }?>


<?php
}
			?>
</body></html>
<?php

		}
	}
}

?>