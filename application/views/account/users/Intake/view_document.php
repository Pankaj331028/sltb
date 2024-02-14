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
			foreach ($this->array_model->arr_intake_program_id() as $k => $v) {if ($v == $intake_id) {$program_id_primary = $k;}}

			$q = $this->db->query("SELECT * FROM intake where intake_id='$intake_id'");
			$intkR = $q->row_array();

			$q = $this->db->query("SELECT * FROM client_program_progress where client_id='$client_id' and program_id_primary='$program_id_primary' and step_id='6' limit 1");
			$cpp = $q->row_array();

			$program_definition_id = $cpp['program_definition_id'];
			$program_id_primary = $cpp['program_id_primary'];

			$print_div_id = "print_" . time();

			$file_name = $user['lname'] . " " . $user['name'] . " " . str_replace("Intake", "", $docr['document_name']) . " " . date('Y-m-d', strtotime($docr['uploaded_date'])) . "-Internal.pdf";

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

			if ($ics['form_data'] != "") {
				$form_data = json_decode($ics['form_data'], true);
				if (isset($form_data['inputr'])) {
					@extract($form_data['inputr']);
					$data_found = "Yes";}
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
			$car_icr = json_decode($car['include_in_client_report'], true);

			$file_joint_or_separate = $car['file_joint_or_separate'];
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

			$sql = "SELECT distinct(loan_contact_name) as loan_contact_name FROM nslds_loans where client_id='$client_id' and (loan_outstanding_principal_balance>'0' or loan_outstanding_interest_balance>'0')";
			$q = $this->db->query($sql);
			$ln_num = $q->num_rows();
			if ($ln_num > 1) {$radio[3] = 1;}
			if ($ln_num <= 1) {$radio[3] = 2;}

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

.input_td { background:#f1f4ff; color:#777777; font-size:13px; padding:3px 5px; border-bottom:1px solid #888888; }
.input_div_2 {width:auto; height:13px; font-size:12px; color:#000000; border-bottom:1px solid #666666; padding:2px 5px;}
.input_2 {width:100%; height:12px; padding:2px 2px 2px 2px; color:#444444; background:#f1f4ff; font-size:10pt; border:none; border-bottom:.5px solid #888888;}
.input_3 {width:100%; height:15px; padding:2px 2px 2px 2px; background:#f1f4ff; font-size:13px; border:none;}
.input_4 {width:100%; height:13px; padding:2px 2px 2px 2px; color:#666666; background:#f1f4ff; font-size:7.5pt; border:none; border-bottom:.5px solid #888888;}
.input_5 {width:100%; height:12px; padding:2px 2px 2px 2px; color:#444444; background:#f1f4ff; font-size:10pt; font-weight:bold; border:none; border-bottom:.5px solid #888888;}

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

				$ssn = explode("-", $ansR[3]['intake_comment_body']);

				?>


<?php if ($intkR['intake_title'] == "Consolidation Intake") {
					?>

<div>

<!-- PAGE 1 -->
<table class="page_wrapper" cellpadding="0" cellspacing="0">
<tr>
<td class="page_wrapper_inner">
<table width="100%" border="0" style="border:0px;">
<tr style="border:0px;">
<td width="6.7%" valign="top"><img src="<?php echo 'data:image/png;base64,' . base64_encode(file_get_contents(("assets/img/logo_doe.png"))); ?>" width="47" alt="Logo" /></td>
<td width="73.3%" valign="top">
<div style="padding:-3px 0 0 6px; font-size:12pt; letter-spacing:.1px; line-height:19pt; font-weight:bold; font-family:Arial, Helvetica, sans-serif;">Direct Consolidation Loan Application and Promissory Note<br />William D. Ford Federal Direct Loan Program</div></td>
<td width="20%" valign="top">
<div class="omb_box">
OMB No. 1845-0007<br />
Form Approved<br />
Exp. Date 07/31/2022
</div>
</td>
</tr>
</table>
<div class="clr"></div>
<table width="100%">
<tr><td class="style_1"><strong>WARNING:</strong> Any person who knowingly makes a false statement or misrepresentation on this form or any accompanying document is subject to penalties that may include fines, imprisonment, or both, under the U.S. Criminal Code and 20 U.S.C. 1097.</td></tr>
<tr><td class="style_1"><strong>BEFORE YOU BEGIN</strong><div class="line_border_1"></div><div class="clr"></div></td></tr>

<tr><td class="style_1">Read the Instructions for Completing the Direct Consolidation Loan Application and Promissory Note.<br /><strong style="font-size:6.6pt;">NOTE: PAGES 1 THROUGH 5 MUST BE SUBMITTED FOR YOUR LOAN APPLICATION TO BE PROCESSED.</strong></td></tr>
<tr><td class="style_1" style="padding-top:7px;"><strong>BORROWER INFORMATION</strong><div class="line_border_1"></div><div class="clr"></div></td></tr>

</table>


<table width="100%" border="0" class="mb_1">
<tr>
	<td class="style_1" width="9.5%" valign="bottom">1. Last Name: </td>
    <td><input type="text" class="input_4" name="name" value="<?php echo $user['lname']; ?>" /></td>
    <td class="style_1" width="8%" valign="bottom">First Name: </td>
    <td><input type="text" class="input_4" name="name" value="<?php echo $user['name']; ?>" /></td>
    <td class="style_1" width="8.9%" valign="bottom">Middle Initial: </td>
    <td><input type="text" class="input_4" name="name" value="<?php echo $ansR[1]['intake_comment_body']; ?>" /></td>
</tr>
</table>

<table width="100%" border="0" class="mb_1"><tr><td class="style_1" width="13.3%" valign="bottom">2. Former Name(s): </td><td><input type="text" class="input_4" name="name" value="<?php echo $ansR[2]['intake_comment_body']; ?>" /></td></tr></table>


<table width="100%" border="0" class="mb_1"><tr>
<td class="style_1" width="17.1%" valign="bottom">3. Social Security Number: </td>
<td><input type="text" class="input_4" name="name" value="<?php echo $ansR[3]['intake_comment_body']; ?>" style="width:150px;" /></td>
</tr></table>

<table width="100%" border="0" class="mb_1"><tr>
<td class="style_1" width="10.5%" valign="bottom">4. Date of Birth: </td>
<td><input type="text" class="input_4" name="name" value="<?php echo date("m/d/Y", strtotime($ansR[4]['intake_comment_body'])); ?>" style="width:150px;" /></td>
</tr></table>

<table width="100%" border="0" class="mb_1">
<tr><td class="style_1" width="100%" valign="bottom">5. Permanent Address (Street, City, State, Zip Code) (if P.O. box or general delivery, see Instructions):</td></tr>
<tr><td><input type="text" class="input_4" name="name" value="<?php echo $ansR['permanent_address']; ?>" /></td></tr>
</table>

<table width="100%" border="0" class="mb_1"><tr>
<td class="style_1" width="21.5%" valign="bottom">6. Area Code/Telephone Number: </td>
<td><input type="text" class="input_4" name="name" value="<?php echo preg_replace('/\d{3}/', '$0-', str_replace('.', null, trim($user['phone'])), 2); ?>" style="width:200px;" /></td>
</tr></table>

<table width="100%" border="0" class="mb_1"><tr><td class="style_1" width="17.8%" valign="bottom">7. Email Address (optional): </td><td><input type="text" class="input_4" name="name" value="<?php echo $ansR[10]['intake_comment_body']; ?>"/></td></tr></table>

<table width="100%" border="0" class="mb_1"><tr><td class="style_1" width="24%" valign="bottom">8. Driver's License State and Number: </td><td><input type="text" class="input_4" name="name" value="<?php echo $ansR['dl_state_and_number']; ?>" style="width:200px;" /></td></tr></table>

<table width="100%" border="0" class="mb_1">
<tr><td class="style_1" valign="bottom">9. Employer's Name and Address (Street, City, State, Zip Code): </td></tr>
<tr><td><input type="text" class="input_4" name="name" value="<?php echo $ansR['employee_address']; ?>" /></td></tr></table>

<table width="100%" border="0" class="mb_1"><tr>
<td class="style_1" width="25.8%" valign="bottom">10. Work Area Code/Telephone Number: </td>
<td><input type="text" class="input_4" name="name" value="<?php echo preg_replace('/\d{3}/', '$0-', str_replace('.', null, trim($ansR[19]['intake_comment_body'])), 2); ?>" style="width:200px;" /></td>
</tr></table>

<div class="clr"></div>
<div class="style_1" style="height:14px; margin-top:7px;"><strong>REFERENCE INFORMATION</strong></div>
<div class="line_border_1"></div>
<div class="clr"></div>

<span class="style_1">List two persons with different U.S. addresses who do not live with you and who have known you for at least three years</span>

<table width="100%" border="0" class="mb_1">
<tr>
	<td class="style_1" width="10.2%" valign="bottom">11. Last Name: </td>
    <td><input type="text" class="input_4" name="name" value="<?php echo $ansR[22]['intake_comment_body']; ?>" /></td>
    <td class="style_1" width="8.1%" valign="bottom">First Name: </td>
    <td><input type="text" class="input_4" name="name" value="<?php echo $ansR[20]['intake_comment_body']; ?>" /></td>
    <td class="style_1" width="8.9%" valign="bottom">Middle Initial: </td>
    <td><input type="text" class="input_4" name="name" value="<?php echo $ansR[21]['intake_comment_body']; ?>" /></td>
</tr>
</table>

<table width="100%" border="0" class="mb_1">
<tr><td class="style_1" width="100%" valign="bottom">Permanent Address (Street, City, State, Zip Code):</td></tr>
<tr><td><input type="text" class="input_4" name="name" value="<?php echo $ansR['reference_permanent_address']; ?>" /></td></tr>
</table>

<table width="100%" border="0" class="mb_1"><tr><td class="style_1" width="16.5%" valign="bottom">Email Address (optional): </td><td><input type="text" class="input_4" name="name" value="<?php echo $ansR[28]['intake_comment_body']; ?>" /></td></tr></table>

<table width="100%" border="0" class="mb_1"><tr>
<td class="style_1" width="20%" valign="bottom">Area Code/Telephone Number: </td>
<td><input type="text" class="input_4" name="name" value="<?php echo preg_replace('/\d{3}/', '$0-', str_replace('.', null, trim($ansR[27]['intake_comment_body'])), 2); ?>" style="width:150px;" /></td>
</tr></table>

<table width="100%" border="0" class="mb_1"><tr><td class="style_1" width="13.2%" valign="bottom">Relationship to You: </td><td><input type="text" class="input_4" name="name" value="<?php echo $ansR[29]['intake_comment_body']; ?>" style="width:250px;" /></td></tr></table>

<div class="clr"></div>

<table width="100%" border="0" class="mb_1">
<tr>
	<td class="style_1" width="10.2%" valign="bottom">12. Last Name: </td>
    <td><input type="text" class="input_4" name="name" value="<?php echo $ansR[32]['intake_comment_body']; ?>" /></td>
    <td class="style_1" width="8.1%" valign="bottom">First Name: </td>
    <td><input type="text" class="input_4" name="name" value="<?php echo $ansR[30]['intake_comment_body']; ?>" /></td>
    <td class="style_1" width="8.9%" valign="bottom">Middle Initial: </td>
    <td><input type="text" class="input_4" name="name" value="<?php echo $ansR[31]['intake_comment_body']; ?>" /></td>
</tr>
</table>

<table width="100%" border="0" class="mb_1">
<tr><td class="style_1" width="100%" valign="bottom">Permanent Address (Street, City, State, Zip Code):</td></tr>
<tr><td><input type="text" class="input_4" name="name" value="<?php echo $ansR['reference_permanent_address_2']; ?>" /></td></tr>
</table>

<table width="100%" border="0" class="mb_1"><tr><td class="style_1" width="16.5%" valign="bottom">Email Address (optional): </td><td><input type="text" class="input_4" name="name" value="<?php echo $ansR[38]['intake_comment_body']; ?>" /></td></tr></table>

<table width="100%" border="0" class="mb_1"><tr>
<td class="style_1" width="20%" valign="bottom">Area Code/Telephone Number: </td>
<td><input type="text" class="input_4" name="name" value="<?php echo preg_replace('/\d{3}/', '$0-', str_replace('.', null, trim($ansR[37]['intake_comment_body'])), 2); ?>" style="width:150px;" /></td>
</tr></table>

<table width="100%" border="0"><tr><td class="style_1" width="13.2%" valign="bottom">Relationship to You: </td><td><input type="text" class="input_4" name="name" value="<?php echo $ansR[39]['intake_comment_body']; ?>" style="width:200px;" /></td></tr></table>

<div class="clr"></div>

</td>
</tr>

<tr><td>
<table width="100%" border="0" class="pagging_text"><tr><td><span class="pagging_text_font_2">S</span>UBMIT PAGES 1 THROUGH 5<br /><span class="pagging_text_font_2">P</span>AGE <strong>1</strong> OF <strong>16</strong> </td></tr></table>
<table width="100%" class="pagging_text_1"><tr><td align="right">12/2019</td></tr></table>
<div class="clr"></div>

</td>
</tr>
</table>
<div class="pagebreak"></div>



<!-- PAGE 2 -->
<table class="page_wrapper mrgn_consolidation" width="100%" cellpadding="0" cellspacing="0">
<tr>
<td class="page_wrapper_inner">

<table width="100%"><tr><td><div class="omb_box">
OMB No. 1845-0007<br />
Form Approved<br />
Exp. Date 07/31/2022
</div></td></tr></table>
<div class="clr"></div>

<table width="100%" border="0"><tr>
<td class="style_1" width="11.5%" valign="bottom">Borrower's Name </td>
<td width="45%"><input type="text" class="input_4" name="name" value="<?php echo $user['name'] . " " . $ansR[1]['intake_comment_body'] . " " . $user['lname']; ?>" /></td>
<td class="style_1" width="15.7%" valign="bottom">Social Security Number: </td>
<td><input type="text" class="input_4" name="name" value="<?php echo $ansR[3]['intake_comment_body']; ?>" /></td>
</tr></table>

<div class="clr"></div>
<div class="style_1" style="height:16px; margin-top:0px;"><strong>LOANS YOU WANT TO CONSOLIDATE</strong></div>
<div class="line_border_1"></div>
<div class="clr"></div>

<table><tr><td class="style_1"><strong>READ THE INSTRUCTIONS BEFORE COMPLETING THIS SECTION.</strong> List each federal education loan that you want to consolidate, including any Direct Loans that you want to include in your Direct Consolidation Loan. If you need more space to list loans, use the Additional Loan Listing Sheet included with this Direct Consolidation Loan Application and Promissory Note (Note). List each loan separately</td></tr></table>

<div class="style_1" style="height:20px; text-align:center; margin-top:10px;"><strong>IN THIS SECTION, LIST ONLY LOANS THAT YOU WANT TO CONSOLIDATE</strong></div>

<table width="100%" cellpadding="5" cellspacing="0" border="1">
<tr class="style_1">
<td><strong>13.</strong> Loan Code<br />(see Instructions)</td>
<td><strong>14.</strong> Loan Holder/Servicer Name, Address, and<br />Area Code/Telephone Number (see Instructions)</td>
<td><strong>15.</strong> Loan Account<br />Number</td>
<td><strong>16.</strong> Estimated Payoff<br />Amount </td>
</tr>

<?php
$lrows = $this->default_model->get_arrby_tbl('nslds_loans', '*', "client_id='" . $client_id . "' and (loan_outstanding_principal_balance>'0' or loan_outstanding_interest_balance>'0') order by loan_date desc", '9');

					foreach ($lrows as $lrow) {
						$loan_type = strtoupper(trim($lrow['loan_type']));
						$loan_code_arr = $this->array_model->loan_type_code();
						?>
<tr class="style_1" style="font-size:6.5pt; vertical-align:top;">
<td align="left" style="vertical-align:middle; padding:17px;"><div style="background:#f1f4ff; width:55px; height:16px; margin:0 auto; padding:2px 1px;"><?php echo $loan_code_arr[$loan_type]; ?></div></td>
<td style="padding:2px 4px;"><div style="background:#f1f4ff; width:98%; height:50px; margin:0 auto;"><?php echo $lrow['loan_contact_name'] . "<br />" . $lrow['loan_contact_street_address_1'] . " " . $lrow['loan_contact_street_address_1'] . " " . $lrow['loan_contact_city'] . " " . $lrow['loan_contact_state'] . " " . $lrow['loan_contact_zip_code']; ?></div></td>
<td style="padding:2px 4px;"><div style="background:#f1f4ff; width:98%; height:50px; margin:0 auto;"></div></td>
<td style="padding:2px 4px;"><div style="background:#f1f4ff; width:98%; height:20px; margin:0 auto; padding:15px 1px;"><?php echo $fmt->formatCurrency(($lrow['loan_outstanding_principal_balance'] + $lrow['loan_outstanding_interest_balance']), "USD"); ?></div></td>
</tr>
<?php }?>


<?php for ($i = count($lrows); $i < 9; $i++) {?>
<tr class="style_1" style="font-size:6.5pt; vertical-align:top;">
<td align="left" style="vertical-align:middle; padding:17px;"><div style="background:#f1f4ff; width:55px; height:20px; margin:0 auto;">&nbsp;</div></td>
<td style="padding:4px;"><div style="background:#f1f4ff; width:98%; height:48px; margin:0 auto;">&nbsp;</div></td>
<td style="padding:4px;"><div style="background:#f1f4ff; width:98%; height:48px; margin:0 auto;">&nbsp;</div></td>
<td style="padding:4px;"><div style="background:#f1f4ff; width:98%; height:48px; margin:0 auto;">&nbsp;</div></td>
</tr>
<?php }?>
</table>

<table><tr><td class="style_1"><strong>17. Grace Period End Date.</strong> If any of the loans you want to consolidate are in a grace period, you can have the processing of your Direct Consolidation Loan application delayed until the end of your grace period by entering your expected grace period end date in the space provided.<br />
<div class="clr mb_5"></div>
If you leave this item blank or if you are not consolidating any loans that are in a grace period, we will begin processing your Direct Consolidation Loan application as
soon as we receive this Note and any other required documents. Any loans listed in the Loans You Want to Consolidate section that are in a grace period will enter
repayment immediately upon consolidation. You will then lose the remaining portion of the grace period on those loans.</td></tr></table>

<table width="100%" border="0"><tr>
<td class="style_1" width="32%" valign="bottom"><strong>Expected Grace Period End Date (month/year):</strong> </td>
<td align="center"><input type="text" class="input_4" name="name" value="<?php echo $grace_period_end_date; ?>" style="width:150px; text-align:left;" /></td>
</tr></table>

</td></tr>

<tr><td>
<table width="100%" border="0" class="pagging_text"><tr><td><span class="pagging_text_font_2">S</span>UBMIT PAGES 1 THROUGH 5<br /><span class="pagging_text_font_2">P</span>AGE <strong>2</strong> OF <strong>16</strong> </td></tr></table>
<table width="100%" class="pagging_text_1"><tr><td align="right">12/2019</td></tr></table>
<div class="clr"></div>

</td>
</tr>
</table>
<div class="pagebreak"></div>



<!-- PAGE 3 -->
<table class="page_wrapper mrgn_consolidation" width="100%" cellpadding="0" cellspacing="0">
<tr>
<td class="page_wrapper_inner">

<table width="100%"><tr><td><div class="omb_box">
OMB No. 1845-0007<br />
Form Approved<br />
Exp. Date 07/31/2022
</div></td></tr></table>
<div class="clr"></div>

<table width="100%" border="0"><tr>
<td class="style_1" width="11.5%" valign="bottom">Borrower's Name </td>
<td width="45%"><input type="text" class="input_4" name="name" value="<?php echo $user['name'] . " " . $ansR[1]['intake_comment_body'] . " " . $user['lname']; ?>" /></td>
<td class="style_1" width="15.7%" valign="bottom">Social Security Number: </td>
<td><input type="text" class="input_4" name="name" value="<?php echo $ansR[3]['intake_comment_body']; ?>" /></td>
</tr></table>
<div class="clr mb_10"></div>

<table class="style_1" width="100%"><tr><td><strong>LOANS YOU DO NOT WANT TO CONSOLIDATE</strong><div class="line_border_1"></div></td></tr></table>
<div class="clr"></div>


<table class="style_1"><tr><td><strong>READ THE INSTRUCTIONS BEFORE COMPLETING THIS SECTION.</strong> List all education loans that you do not want to consolidate, but want us to consider when we
calculate the maximum repayment period for your Direct Consolidation Loan (see Item 11 of the Borrower's Rights and Responsibilities Statement that accompanies
this Note). Remember to include any Direct Loans that you do not want to consolidate. If you need more space to list loans, use the Additional Loan Listing Sheet
included with this Note. List each loan separately.</strong></td></tr></table>

<div class="style_1" style="height:20px; text-align:center; margin-top:10px;"><strong>IN THIS SECTION, LIST ONLY LOANS THAT YOU DO NOT WANT TO CONSOLIDATE</strong></div>

<table width="100%" cellpadding="1" cellspacing="0" border="1">
<tr class="style_1" valign="top">
<td style="padding:0px 3px;"><strong>18.</strong> Loan Code<br />(see Instructions)</td>
<td style="padding:0px 3px;"><strong>19.</strong> Loan Holder/Servicer Name, Address, and<br />Area Code/Telephone Number (see Instructions)</td>
<td style="padding:0px 3px;"><strong>20.</strong> Loan Account<br />Number</td>
<td style="padding:0px 3px;"><strong>21.</strong> Current Balance </td>
</tr>

<?php for ($i = 0; $i < 4; $i++) {?>
<tr class="style_1" style="font-size:6.5pt; vertical-align:top;">
<td align="left" style="vertical-align:middle; padding:13px;"><div style="background:#f1f4ff; width:50px; height:20px; margin:0 auto;">&nbsp;</div></td>
<td style="padding:3px;"><div style="background:#f1f4ff; width:98%; height:40px; margin:0 auto;">&nbsp;</div></td>
<td style="padding:3px;"><div style="background:#f1f4ff; width:98%; height:40px; margin:0 auto;">&nbsp;</div></td>
<td style="padding:3px;"><div style="background:#f1f4ff; width:98%; height:40px; margin:0 auto;">&nbsp;</div></td>
</tr>
<?php }?>

</table>

<div class="clr mb_10"></div>
<table class="style_1" width="100%"><tr><td>
<strong>NOTICE ABOUT LOANS TO BE CONSOLIDATED OR NOT CONSOLIDATED</strong><div class="line_border_1"></div>
We will send you a notice before we consolidate your loans. This notice will <strong>(1)</strong> identify all of your loans that will be consolidated and show the verified payoff
amounts for those loans, and <strong>(2)</strong> tell you the deadline by which you must notify us if you want to cancel the Direct Consolidation Loan, or if you do not want to
consolidate one or more of the loans listed in the notice. If you have any loans that will not be consolidated, the notice will also identify those loans. <strong>See the
Instructions for more information about the notice we will send.</strong>
<div class="clr mb_7"></div>

<strong>REPAYMENT PLAN SELECTION</strong><div class="line_border_1"></div>
To understand your repayment plan options, carefully read the repayment plan information in Item 11 of the Borrower's Rights and Responsibilities Statement (BRR) that accompanies this Note and in any other materials you receive with this Note. Then select a repayment plan for your Direct Consolidation Loan:
<div class="clr mb_7"></div>

<ul class="style_1" style="height:75px; margin:0px; padding:0px 0px 7px 20px;">
<li style="height:32px;">To select the Standard Repayment Plan, the Graduated Repayment Plan, or the Extended Repayment Plan, complete the Repayment Plan Selection form
that accompanies this Note.</li>
<li style="height:40px;">To select the SAVE (formerly known as REPAYE) Plan, the Pay As You Earn Repayment Plan (PAYE Plan), the Income-Based Repayment Plan (IBR Plan), or the Income-Contingent Repayment Plan (ICR Plan), visit <a href="https://studentaid.gov" target="_blank">StudentAid.gov</a> to complete the Income-Driven Repayment Plan Request online, or complete the Income-Driven Repayment Plan Request form that accompanies this Note.</li>
</ul>
<div class="clr"></div>
<strong>NOTE:</strong> You <strong>must</strong> select either the SAVE (formerly known as REPAYE) Plan, the PAYE Plan, the IBR Plan, or the ICR Plan if:
<div class="clr mb_5"></div>
<ol style="height:60px; margin:0px; padding:3px 0px 0px 20px;">
<li style="height:15px;">You want to consolidate a defaulted loan and you have not made a satisfactory repayment arrangement with the current holder of the defaulted loan;</li>
<li style="height:30px;">You are consolidating a delinquent Federal Consolidation Loan (a consolidation loan made under the Federal Family Education Loan Program) that the lender has submitted to the guaranty agency for default aversion; or</li>
<li>You are consolidating a defaulted Federal Consolidation Loan, and you are not consolidating any other eligible loans.</li>
</ol>
</td></tr></table>


<table class="style_1" style="margin-top:10px;"><tr><td><strong>BORROWER UNDERSTANDINGS, CERTIFICATIONS, AND AUTHORIZATIONS</strong></td></tr></table>
<div class="line_border_1"></div>
<div class="clr"></div>

<table width="100%" class="style_1"><tr>

<td width="50%" valign="top">
<strong>22.</strong> I understand the following:
<div class="clr mb_7"></div>
<strong>A.</strong> Applying for a Direct Consolidation Loan does not mean that I must accept the loan. Before my loans are consolidated, the U.S. Department of Education (ED) will send me a notice that:<br /><div class="clr mb_7"></div>

<ul style="height:40px; margin:0px; padding:0px 0px 0px 20px;">
<li>Identifies my loans that will be consolidated and shows the payoff amounts for those loans that have been verified with my loan holders or through the National Student Loan Data System (NSLDS), and</li>
</ul>
</td>
<td width="50%" valign="top">
<ul style="height:60px; margin:0px; padding:0px 0px 7px 20px;">
<li>Tells me the deadline by which I must notify ED if I want to cancel my application for the Direct Consolidation Loan, or if I do not want to consolidate one or more of the loans identified in the notice as loans that will be consolidated. If I have any loans that will not be consolidated, the notice will also identify those loans.</li>
</ul>

<strong>B.</strong> If I do not want all of the loans listed in the notice described in 22.A. to be consolidated, I must inform ED by the deadline specified in that notice.
</td>

</tr></table>


</td></tr>

<tr><td>
<table width="100%" border="0" class="pagging_text"><tr><td><span class="pagging_text_font_2">S</span>UBMIT PAGES 1 THROUGH 5<br /><span class="pagging_text_font_2">P</span>AGE <strong>3</strong> OF <strong>16</strong> </td></tr></table>
<table width="100%" class="pagging_text_1"><tr><td align="right">12/2019</td></tr></table>
<div class="clr"></div>

</td>
</tr>

</table>
<div class="pagebreak"></div>



<!-- PAGE 4 -->
<table class="page_wrapper mrgn_consolidation style_1" width="100%" cellpadding="0" cellspacing="0">
<tr>
<td class="page_wrapper_inner">

<table width="100%"><tr><td><div class="omb_box">
OMB No. 1845-0007<br />
Form Approved<br />
Exp. Date 07/31/2022
</div></td></tr></table>
<div class="clr"></div>

<table width="100%" border="0"><tr>
<td class="style_1" width="11.5%" valign="bottom">Borrower's Name </td>
<td width="45%"><input type="text" class="input_4" name="name" value="<?php echo $user['name'] . " " . $ansR[1]['intake_comment_body'] . " " . $user['lname']; ?>" /></td>
<td class="style_1" width="15.7%" valign="bottom">Social Security Number: </td>
<td><input type="text" class="input_4" name="name" value="<?php echo $ansR[3]['intake_comment_body']; ?>" /></td>
</tr></table>
<div class="clr mb_10"></div>


<table width="100%"><tr><td><strong>BORROWER UNDERSTANDINGS, CERTIFICATIONS, AND AUTHORIZATIONS (CONTINUED)</strong><div class="line_border_1"></div></td></tr></table>

<table><tr valign="top">

<td width="48%" class="style_1">

<strong>C.</strong> If ED accepts this application for a Direct Consolidation Loan, ED will send funds to the holders of the loans that I want to consolidate to pay off those
loans. The amount of my Direct Consolidation Loan will be the sum of the balances of my outstanding eligible loans that I have chosen to consolidate. The payoff amount may be greater than or less than the estimated total balance I indicated in the Loans I Want to Consolidate section of this Note.
<div class="clr mb_7"></div>
The outstanding balance on each loan to be consolidated includes unpaid principal, unpaid accrued interest and late charges as defined by federal regulations and as certified by the loan holder. If you are consolidating a defaulted loan, collection costs may also be included. For a Direct Loan or for a Federal Family Education Loan (FFEL) Program loan that is in default, the amount of any collection costs that may be included in the payoff balances of the loans is limited to a maximum of 18.5% of the outstanding principal and interest. For any other defaulted federal education loans, all collection costs that are owed may be included in the payoff balances of the loans.
<div class="clr mb_7"></div>
<strong>D.</strong> If the amount ED sends to my loan holders is more than the amount needed to pay off the balances of the selected loans, the holders will refund the excess amount to ED and this amount will be applied against the outstanding balance of my Direct Consolidation Loan. If the amount that ED sends to my holders is less than the amount needed to pay off the balances of the loans selected for consolidation, ED will include the remaining amount in my Direct Consolidation Loan.
<div class="clr mb_7"></div>

<strong>E.</strong> If I am consolidating loans made under the FFEL, Direct Loan, or Federal Perkins Loan (Perkins Loan) programs, the outstanding balance of my Direct Consolidation Loan counts against the applicable aggregate loan limits for each type of loan. Under the Act, as defined under "Laws That Apply to this Note and Other Legal Information" in the Note Terms and Conditions section of this Note, the percentage of the original amount of my Direct Consolidation Loan that is attributable to each loan type is counted against the loan limit for that type of loan.
<div class="clr mb_7"></div>

<strong>F.</strong> I may not consolidate an existing Direct Consolidation Loan unless I include at least one additional eligible loan in the consolidation.
<div class="clr mb_7"></div>

<strong>G.</strong> I may consolidate an existing Federal Consolidation Loan (a consolidation loan made under the FFEL Program) without including an additional eligible loan in the consolidation if I am:
<div class="clr mb_10"></div>

<ul style="height:140px; margin:0px; padding:0px 0px 0px 20px;">
<li style="height:30px;">Consolidating a delinquent Federal Consolidation Loan that the lender has submitted to the guaranty agency for default aversion;</li>
<li style="height:45px;">Consolidating a defaulted Federal Consolidation Loan, and I agree to repay my new Direct Consolidation Loan under the SAVE (formerly known as REPAYE) Plan, the PAYE Plan, the IBR Plan, or the ICR Plan;</li>
<li style="height:30px;">Consolidating a Federal Consolidation Loan to use the Public Service
Loan Forgiveness Program (see BRR Item 16); or</li>
<li>Consolidating a Federal Consolidation Loan to use the no accrual of interest benefit for active duty service members (see BRR Item 7).</li>
</ul>

<div class="clr mb_5"></div>

<strong>H.</strong> If I consolidate my loans, I may no longer be eligible for certain deferments, subsidized deferment periods, certain types of loan discharges or loan forgiveness, borrower defenses to repayment based on acts or omissions of the school I attended, reduced interest rates, or repayment incentive programs that were available on the loans I am consolidating.
<div class="clr mb_7"></div>
<strong>I.</strong> If I am consolidating a Perkins Loan:

</td>

<td width="4%">&nbsp;</td>

<td width="48%" class="style_1">

<ul style="height:225px; margin:0px; padding:0px 0px 0px 20px;">
<li style="height:43px;">I will no longer be eligible for interest-free periods while I am enrolled in school at least half time, in the grace period on my loan, and during deferment periods; and</li>
<li>I will no longer be eligible for full or partial loan cancellation under the Perkins Loan Program based on years of service in one of the following occupations: teacher in a low-income elementary or secondary school; staff member in an eligible preschool program; special education teacher; member of the Armed Forces who qualifies for special pay; Peace Corps volunteer or volunteer under the Domestic Volunteer Service Act of 1973; law enforcement or corrections officer; attorney in an eligible defender organization; teacher of mathematics, science, foreign languages, bilingual education or any other high-need field; nurse or medical technician providing health care services; employee of a public or private nonprofit child or family service agency that services high-risk children from low-income families and their families; fire fighter; faculty member at a Tribal College or University; librarian; or speech language pathologist.</li>
</ul>
<div class="clr mb_10"></div>

<strong>J.</strong> Any payments I made on the loans I am consolidating (including any Direct Loans) before the date of consolidation will not count toward:
<div class="clr mb_10"></div>

<ul style="height:75px; margin:0px; padding:0px 0px 0px 20px;">
<li style="height:45px;">The number of years of qualifying repayment required for loan forgiveness under the SAVE (formerly known as REPAYE) Plan, the PAYE Plan, the IBR Plan, or the ICR Plan (see BRR Item 11), or</li>
<li>The 120 qualifying payments required for Public Service Loan Forgiveness (see BRR Item 16).</li>
</ul>
<div class="clr mb_10"></div>

<strong>K.</strong> If I am consolidating a Direct PLUS Loan or a Federal PLUS Loan (a Federal PLUS Loan is a PLUS loan made under the Federal Family Education Loan Program) that I obtained to help pay for my child's undergraduate education, I am not eligible to repay my Direct Consolidation Loan under the SAVE (formerly known as REPAYE) Plan, the PAYE Plan, or the IBR Plan. However, I may repay my Direct Consolidation Loan under the ICR Plan.
<div class="clr mb_7"></div>

<strong>L.</strong> If I am consolidating a Direct Loan Program loan first disbursed before July 1, 2012 on which I received an up-front interest rebate, and I have not yet made the first 12 required on-time payments on that loan at the time the loan is consolidated, I will lose the rebate. This means that the rebate amount will be added back to the principal balance of the loan before it is consolidated.
<div class="clr mb_7"></div>

<strong>M.</strong> I have the option of paying the interest that accrues on my Direct Consolidation Loan during deferment (including in-school deferment), forbearance, and certain other periods, but if I do not do so, ED may add unpaid interest that accrues on my loan to the principal balance of my loan at the end of the deferment, forbearance, or other period. This is called "capitalization." Capitalization will increase the principal amount owed on the loan and the total amount of interest I must pay.
<div class="clr mb_7"></div>

<strong>N.</strong> If I consolidate my loans after I have begun active duty military service, my new Direct Consolidation Loan will not qualify for the 6% interest rate limit under the Servicemembers Civil Relief Act as described in BRR Item 6 during that period of military service.

</td>

</tr></table>

</td></tr>

<tr><td>
<table width="100%" border="0" class="pagging_text"><tr><td><span class="pagging_text_font_2">S</span>UBMIT PAGES 1 THROUGH 5<br /><span class="pagging_text_font_2">P</span>AGE <strong>4</strong> OF <strong>16</strong> </td></tr></table>
<table width="100%" class="pagging_text_1"><tr><td align="right">12/2019</td></tr></table>
<div class="clr"></div>

</td></tr></table>
<div class="pagebreak"></div>


<!-- PAGE 5 -->
<table class="page_wrapper mrgn_consolidation" width="100%" cellpadding="0" cellspacing="0">
<tr>
<td class="page_wrapper_inner style_1">

<table width="100%"><tr><td><div class="omb_box">
OMB No. 1845-0007<br />
Form Approved<br />
Exp. Date 07/31/2022
</div></td></tr></table>
<div class="clr"></div>

<table width="100%" border="0"><tr>
<td class="style_1" width="11.5%" valign="bottom">Borrower's Name </td>
<td width="45%"><input type="text" class="input_4" name="name" value="<?php echo $user['name'] . " " . $ansR[1]['intake_comment_body'] . " " . $user['lname']; ?>" /></td>
<td class="style_1" width="15.7%" valign="bottom">Social Security Number: </td>
<td><input type="text" class="input_4" name="name" value="<?php echo $ansR[3]['intake_comment_body']; ?>" /></td>
</tr></table>


<table><tr valign="top">

<td width="48%">
<div class="clr mb_10"></div>
<strong style="font-size:7pt;">BORROWER UNDERSTANDINGS, CERTIFICATIONS, AND AUTHORIZATIONS<br />(CONTINUED)</strong>
<div class="line_border_1"></div>
<div class="clr mb_2"></div>

<strong>O.</strong> ED has the authority to verify information reported on this Note with other federal agencies and to report information about my loan status to persons and organizations permitted by law to receive that information.
<div class="clr mb_7"></div>

<strong>P.</strong> I am entitled to an exact copy of this Note and the Borrower's Rights and Responsibilities Statement.
<div class="clr mb_7"></div>

<strong>23.</strong> Under penalty of perjury, I certify that:
<div class="clr mb_7"></div>

<strong>A.</strong> The information I provide on this Note and that I update from time to time is true, complete, and correct to the best of my knowledge and belief.
<div class="clr mb_7"></div>

<strong>B.</strong> All of the loans I have selected for consolidation have been used to finance my education or the education of one or more of my children.
<div class="clr mb_7"></div>

<strong>C.</strong> All of the loans I have selected for consolidation are in a grace period or in repayment ("in repayment" includes loans in deferment or forbearance).
<div class="clr mb_7"></div>

<strong>D.</strong> If I owe an overpayment on a Federal Perkins Loan or on a grant made under the federal student aid programs (as defined in the Note Terms and Conditions), I have made satisfactory arrangements to repay the amount owed.
<div class="clr mb_7"></div>

<strong>E.</strong> If I am in default on any loan I am consolidating, I have either made a satisfactory repayment arrangement with the holder of that defaulted loan, or I will repay my Direct Consolidation Loan under the SAVE (formerly known as REPAYE) Plan, the PAYE Plan, the IBR Plan, or the ICR Plan, except that I <strong>MUST</strong> repay my Direct Consolidation Loan under the SAVE (formerly known as REPAYE) Plan, the PAYE Plan, the IBR Plan, or the ICR Plan under the conditions described above in the Repayment Plan Selection section of this Note.
<div class="clr mb_7"></div>

<strong>F.</strong> If I have been convicted of, or if I have pled nolo contendere (no contest) or guilty to, a crime involving fraud in obtaining federal student aid funds, I have fully repaid those funds.
<div class="clr mb_7"></div>

<strong>24.</strong> I authorize:
<div class="clr mb_7"></div>

<strong>A.</strong> ED to contact the holders of the loans I have selected for consolidation to determine the eligibility for consolidation and the payoff amounts of:
<div class="clr mb_10"></div>

<ul style="height:58px; padding:0px; margin:0 0 0 20px;">
<li style="height:28px;">The loans listed in the Loans I Want to Consolidate section of this Note, and</li>
<li>Any of my other federal education loans that are held by a holder of a loan listed in the Loans I Want to Consolidate section.</li>
</ul>
<div class="clr mb_10"></div>

<strong>B.</strong> The holders of the loans I want to consolidate to release any information required to consolidate my loans, in accordance with the Act, to ED or its
agents and contractors.
<div class="clr mb_7"></div>

<strong>C.</strong> ED to pay the full amount I owe to the holders of the loans that I want to consolidate to pay off those loans.
<div class="clr mb_7"></div>

<strong>D.</strong> My schools, ED, and their agents and contractors to release information about my Direct Consolidation Loan to the references I provide and to my immediate family members, unless I submit written directions otherwise or as otherwise permitted by law.
<div class="clr mb_7"></div>

<strong>E.</strong> My schools, ED, and their agents and contractors to contact me regarding my loan request or my loan, including repayment of my loan, at any cellular telephone number I provide now or in the future using automated dialing equipment or artificial or prerecorded voice or text messages.

</td>


<td width="4%">&nbsp;</td>


<td width="48%">
<div class="clr mb_10"></div>

<strong>PROMISES</strong><div class="line_border_1"></div>

<strong>25.</strong> I promise to pay ED the full amount disbursed under the terms of this Note to pay off the loans that I have chosen to consolidate, plus interest and other charges and fees that I may be required to pay under the terms of the Note.
<div class="clr mb_7"></div>

<strong>26.</strong> If I do not make a payment on my Direct Consolidation Loan when it is due, I promise to pay reasonable collection costs, including but not limited to attorney fees, court costs, and other fees.
<div class="clr mb_7"></div>

<strong>27.</strong> I promise that I will not sign this Note before reading the entire Note, even if I am told not to read it, or told that I am not required to read it.
<div class="clr mb_7"></div>

<strong>28.</strong> By signing this Note, whether electronically or on a paper copy, I promise that I have read, understand, and agree to the terms and conditions of this Note, including the Borrower Understandings, Certifications, and Authorizations section, and the Borrower's Rights and Responsibilities Statement.
<div class="clr mb_10"></div>

<p style="width:100%; height:50px; text-align:center;"><strong>I UNDERSTAND THAT THIS IS A LOAN THAT I MUST REPAY.</strong></p>
<div class="clr mb_10"></div>

<strong>29. Borrower's Signature:</strong> <div class="line_border_2" style="margin:50px 0 30px 0;"></div>

<table width="100%"><tr>
<td width="40%" valign="bottom"><strong>Today's Date (mm-dd-yyyy)</strong> </td>
<td><input type="text" class="input_4" name="date" value="<?php echo date("m-d-Y"); ?>" style="width:100px;" />
</tr></table>


</td>

</tr></table>

</td></tr>

<tr><td>
<table width="100%" border="0" class="pagging_text"><tr><td><span class="pagging_text_font_2">S</span>UBMIT PAGES 1 THROUGH 5<br /><span class="pagging_text_font_2">P</span>AGE <strong>5</strong> OF <strong>16</strong> </td></tr></table>
<table width="100%" class="pagging_text_1"><tr><td align="right">12/2019</td></tr></table>
<div class="clr"></div>

</td></tr></table>
<div class="pagebreak"></div>


<!-- PAGE 6 -->
<table class="page_wrapper mrgn_consolidation style_1" width="100%" cellpadding="0" cellspacing="0">
<tr>
<td class="page_wrapper_inner" valign="middle" style="text-align:center; vertical-align:middle; font-size:18px; font-weight:bold;">THIS PAGE IS INTENTIONALLY BLANK
</td></tr>

<tr><td>
<table width="100%" border="0" class="pagging_text"><tr><td><span class="pagging_text_font_2">S</span>UBMIT PAGES 1 THROUGH 5<br /><span class="pagging_text_font_2">P</span>AGE <strong>6</strong> OF <strong>16</strong> </td></tr></table>
<table width="100%" class="pagging_text_1"><tr><td align="right">12/2019</td></tr></table>
<div class="clr"></div>

</td></tr></table>
<div class="pagebreak"></div>


<!-- PAGE 7 -->
<table class="page_wrapper mrgn_consolidation style_1" width="100%" cellpadding="0" cellspacing="0">
<tr><td class="page_wrapper_inner style_1">

<table width="100%"><tr><td><div class="omb_box">
OMB No. 1845-0007<br />
Form Approved<br />
Exp. Date 07/31/2022
</div></td></tr></table>
<div class="clr"></div>



<strong>NOTE TERMS AND CONDITIONS</strong>
<div class="line_border_1"></div>

<table width="100%"><tr valign="top">

<td width="48%">
This section summarizes some of the major terms and conditions of your Direct Consolidation Loan. You can find more detailed information about the terms and conditions of your loan in the Borrower's Rights and Responsibilities Statement (BRR) that accompanies this Direct Consolidation Loan Application and Promissory Note (Note). Each topic covered in this section of the Note is followed by the number of the item in the BRR that provides additional information about that topic. The BRR is considered to be part of the Note. Whenever we refer to the Note, the term "Note" includes the BRR.
<div class="clr mb_10"></div>

We contract with servicers to process Direct Loan payments, deferment and forbearance requests, and other transactions, and to answer questions about Direct Loans. We will provide you with information about how to contact us or our servicers after your loan is made. It is important to keep in contact with your servicer.
<div class="clr mb_10"></div>

The words "we," "us," and "our" refer to the U.S. Department of Education or our servicers. The word "loan" refers to the Direct Consolidation Loan you receive under this Note.
<div class="clr mb_10"></div>

The term "federal student aid" refers to aid awarded under the following programs: the Federal Pell Grant Program; the Federal Supplemental Educational Opportunity Grant (FSEOG) Program; the Federal Work-Study (FWS) Program; the Leveraging Educational Assistance Partnership Grant Program; the Teacher Education Assistance for College and Higher Education (TEACH) Grant Program; the William D. Ford Federal Direct Loan (Direct Loan) Program; the Federal Family Education Loan (FFEL) Program; and the Federal Perkins Loan Program.
<div class="clr mb_10"></div>

<strong>LAWS THAT APPLY TO THIS NOTE AND OTHER LEGAL INFORMATION (BRR Item 1)</strong>
<div class="clr mb_10"></div>

The terms of this Note are determined in accordance with the Higher Education Act of 1965, as amended (the HEA), our regulations, and other federal laws and regulations. Throughout this Note, we refer to these laws and regulations as "the Act."
<div class="clr mb_10"></div>

Any notice we are required to send you about your loan, even if you do not receive the notice, will be effective if it is sent by first-class mail to the most recent address that we have for you, emailed to an email address you have provided, or sent by any other method of notification that is permitted or required by the Act. You must immediately notify your servicer of a change in your contact information or status (see BRR Item 10).
<div class="clr mb_10"></div>

If we do not enforce a term of this Note, that does not waive any of our rights to enforce that term or any other term in the future. No term of your loan may be modified or waived, unless we do so in writing. If any term of your loan is determined to be unenforceable, the remaining terms remain in force.
<div class="clr mb_10"></div>

<strong>TYPES OF LOANS YOU CAN RECEIVE UNDER THIS NOTE (BRR Item 3)</strong>
<div class="clr mb_10"></div>

This Note is used to make a Direct Consolidation Loan. You will have a single Direct Consolidation Loan, but depending on the types of loans you consolidate, it may have up to two components (each of which will have a separate loan identification number) representing subsidized loans and unsubsidized loans.
<div class="clr mb_10"></div>

When the loans you are consolidating are paid off, we will send you a disclosure statement. The disclosure statement will identify the amount of your Direct Consolidation Loan, the loan identification number(s), and additional terms of the loan, such as the interest rate and repayment
</td>

<td width="4%">&nbsp;</td>
<td width="48%">schedule. Any disclosure statement we send to you in connection with the loan made under this Note is considered to be part of the Note. If you have questions about a disclosure statement that you receive, contact your servicer.
<div class="clr mb_10"></div>

<strong>INTEREST RATE (BRR Item 6)</strong>
<div class="clr mb_10"></div>

Unless we notify you in writing that a different rate will apply, the interest
rate on your Direct Consolidation Loan is a fixed rate (meaning that your
interest rate will never change) that is based on the weighted average of the
interest rates on the loans being consolidated, rounded to the nearest
higher one-eighth of one percent.
<div class="clr mb_10"></div>

If you are in the military and the interest rate on your loan is greater than
6%, you may qualify to have the rate limited to 6% during your period of
service.
<div class="clr mb_10"></div>

<strong>PAYMENT OF INTEREST (BRR Item 7)</strong>
<div class="clr mb_10"></div>

Generally, we do not charge interest on the subsidized component of your
Direct Consolidation Loan during deferment periods and during certain
periods of repayment under certain repayment plans that base your
monthly payment amount on your income. We generally charge interest on
the subsidized component of your Direct Consolidation Loan during all other
periods, starting on the date the loans you choose to consolidate are paid
off.
<div class="clr mb_10"></div>

Generally, we charge interest on the unsubsidized component of your Direct
Consolidation Loan during all periods, starting on the date the loans you
choose to consolidate are paid off.
<div class="clr mb_10"></div>

You are responsible for paying all interest we charge on your Direct
Consolidation Loan. If you do not pay this interest, we may capitalize the
interest (add it to the principal balance of your loan).
<div class="clr mb_10"></div>

<strong style="font-size:7pt;">RESPONSIBILITY FOR PAYING ALL INTEREST ON ALL OR PART OF THE
SUBSIDIZED COMPONENT OF A DIRECT CONSOLIDATION LOAN (IF YOU
ARE A FIRST-TIME BORROWER ON OR AFTER JULY 1, 2013) (BRR Item 8)</strong>
<div class="clr mb_10"></div>

If you were a <strong>first-time borrower on or after July 1, 2013</strong> when you
received a Direct Subsidized Loan and you are now consolidating that loan,
you may be responsible for paying the interest that accrues during all
periods on the portion of your Direct Consolidation Loan that repaid the
Direct Subsidized Loan.
<div class="clr mb_10"></div>

<strong>LATE CHARGES AND COLLECTION COSTS (BRR Item 9)</strong>
<div class="clr mb_10"></div>

If you do not make your full monthly loan payment within 30 days of your
due date, we may require you to pay a late charge of not more than six
cents for each dollar of each late payment.
<div class="clr mb_10"></div>

We may also require you to pay any other charges and fees that are
permitted by the Act related to the collection of your loan. If you default on
a loan, you must pay reasonable collection costs, plus any court costs and
attorney fees.
<div class="clr mb_10"></div>

<strong>REPAYING YOUR LOAN (BRR Item 11)</strong>
<div class="clr mb_10"></div>

You must repay your loan in monthly installments during a repayment
period that begins on the date of the first payoff of the loans that you have
chosen to consolidate. You have a choice of several repayment plans,
including plans that base your required monthly payment amount on your
income. </td>

</tr></table>

</td></tr>

<tr><td>
<table width="100%" border="0" class="pagging_text"><tr><td><span class="pagging_text_font_2">S</span>UBMIT PAGES 1 THROUGH 5<br /><span class="pagging_text_font_2">P</span>AGE <strong>7</strong> OF <strong>16</strong> </td></tr></table>
<table width="100%" class="pagging_text_1"><tr><td align="right">12/2019</td></tr></table>
<div class="clr"></div>

</td></tr></table>
<div class="pagebreak"></div>


<!-- PAGE 8 -->
<table class="page_wrapper mrgn_consolidation style_1" width="100%" cellpadding="0" cellspacing="0">
<tr><td class="page_wrapper_inner style_1">

<table width="100%"><tr><td><div class="omb_box">
OMB No. 1845-0007<br />
Form Approved<br />
Exp. Date 07/31/2022
</div></td></tr></table>
<div class="clr"></div>


<table><tr valign="top">
<td width="48%" class="style_1">
<strong>NOTE TERMS AND CONDITIONS (CONTINUED)</strong><div class="line_border_1"></div>
If you are temporarily unable to make your monthly loan payments, you can
request a deferment or forbearance that allows you to temporarily stop
making payments or to temporarily make a smaller payment amount (see
BRR Item 15). In some cases, we may grant you a forbearance without a
request.
<div class="clr mb_10"></div>

You may prepay all or any part of your loan at any time without penalty.
After you have fully repaid a loan, we will send you a notice telling you that
you have paid off your loan.
<div class="clr mb_10"></div>

<strong>DEFAULTING ON YOUR LOAN (BRR Item 12)</strong>
<div class="clr mb_10"></div>

You will be considered in default on your loan if:
<div class="clr mb_10"></div>

<ul style="height:105px; padding:0px; margin:0 0 0 20px;">
<li style="height:30px;">You do not make your monthly loan payments for a total of at least 270 days;</li>
<li style="height:30px;">You do not comply with other terms of the loan, and we determine that you do not intend to repay your loan; or</li>
<li>We accelerate your loan (see "CONDITIONS WHEN WE MAY REQUIRE YOU TO IMMEDIATELY REPAY THE FULL AMOUNT OF YOUR LOAN") and you do not pay the amount due.</li>
</ul>
<div class="clr mb_10"></div>

If you default, we may:
<div class="clr mb_10"></div>

<ul style="height:190px; padding:0px; margin:0 0 0 20px;">
<li style="height:32px;">Capitalize all outstanding interest, which will increase the principal amount due on the loan and the total amount of interest you will pay;</li>
<li style="height:32px;">Report the default to nationwide consumer reporting agencies (credit bureaus), which will significantly and negatively affect your credit history;</li>
<li style="height:17px;">Demand that you immediately repay the loan in full;</li>
<li style="height:17px;">Order administrative wage garnishment (AWG) of your wages;</li>
<li style="height:45px;">Take (offset) your federal income tax refund or Social Security Administration payments or any other payment authorized for offset under federal law and use it to pay off part of your loan;</li>
<li style="height:16px;">File a lawsuit against you to collect on the loan; and</li>
<li>Require you to pay collection costs, which will increase the total amount you must pay on your loan.</li>
</ul>
<div class="clr mb_10"></div>

<strong>CONDITIONS WHEN WE MAY REQUIRE YOU TO IMMEDIATELY REPAY THE FULL AMOUNT OF YOUR LOAN (BRR Item 13)</strong>
<div class="clr mb_10"></div>

We may require you to immediately repay the entire unpaid balance of your loan (this is called "acceleration") if you:
<div class="clr mb_10"></div>

<ul style="height:55px; padding:0px; margin:0 0 0 20px;">
<li style="height:30px;">Make a false statement that causes you to receive a loan that you are not eligible for; or</li>
<li>Default on your loan (see "DEFAULTING ON YOUR LOAN").</li>
</ul>

<strong>INFORMATION WE REPORT ABOUT YOUR LOAN (BRR Item 14)</strong>
<div class="clr mb_10"></div>
We will report information about your loan to nationwide consumer
reporting agencies (credit bureaus) and the National Student Loan Data
System (NSLDS) on a regular basis. This information will include the amount
and repayment status of your loan (for example, whether you are current or
delinquent in making payments). If you default on a loan, we will report this
to nationwide consumer reporting agencies. Schools may access
information in NSLDS for specific purposes that we authorize.
</td>


<td width="52%"></td>

</tr></table>

</td></tr>

<tr><td>
<table width="100%" border="0" class="pagging_text"><tr><td><span class="pagging_text_font_2">S</span>UBMIT PAGES 1 THROUGH 5<br /><span class="pagging_text_font_2">P</span>AGE <strong>8</strong> OF <strong>16</strong> </td></tr></table>
<table width="100%" class="pagging_text_1"><tr><td align="right">12/2019</td></tr></table>
<div class="clr"></div>

</td></tr></table>
<div class="pagebreak"></div>


<!-- PAGE 9 -->
<table class="page_wrapper mrgn_consolidation style_1" width="100%" cellpadding="0" cellspacing="0">
<tr><td class="page_wrapper_inner style_1">

<table width="100%"><tr><td><div class="omb_box">
OMB No. 1845-0007<br />
Form Approved<br />
Exp. Date 07/31/2022
</div></td></tr></table>
<div class="clr"></div>

<strong>IMPORTANT NOTICES </strong>
<div class="line_border_1"></div>

<table><tr valign="top">

<td width="48%" class="style_1">
<strong>GRAMM-LEACH-BLILEY ACT NOTICE</strong>
<div class="clr mb_5"></div>

The Gramm-Leach-Bliley Act (Public Law 106-102) requires that lenders provide certain information to their customers regarding the collection and use of nonpublic personal information.
<div class="clr mb_7"></div>

We disclose nonpublic personal information to third parties only as necessary to process and service your loan and as permitted by the Privacy Act of 1974. See the Privacy Act Notice below. We do not sell or otherwise make available any information about you to any third parties for marketing purposes.
<div class="clr mb_7"></div>

We protect the security and confidentiality of nonpublic personal
information by implementing the following policies and practices. All
physical access to the sites where nonpublic personal information is
maintained is controlled and monitored by security personnel. Our
computer systems offer a high degree of resistance to tampering and
circumvention. These systems limit data access to our staff and contract
staff on a "need-to-know" basis, and control individual users’ ability to
access and alter records within the systems. All users of these systems are
given a unique user ID with personal identifiers. All interactions by
individual users with the systems are recorded.
<div class="clr mb_7"></div>

<strong>PRIVACY ACT NOTICE</strong>
<div class="clr mb_7"></div>

The Privacy Act of 1974 (5 U.S.C. 552a) requires that the following notice be provided to you:
<div class="clr mb_7"></div>

The authority for collecting the requested information from and about you
is §451 <span style="text-decoration:underline;">et seq</span>. of the Higher Education Act (HEA) of 1965, as amended (20
U.S.C. 1087a <span style="text-decoration:underline;">et seq</span>.) and the authorities for collecting and using your Social
Security Number (SSN) are §484(a)(4) of the HEA (20 U.S.C. 1091(a)(4)) and
31 U.S.C. 7701(b). Participating in the William D. Ford Federal Direct Loan
(Direct Loan) Program and giving us your SSN are voluntary, but you must
provide the requested information, including your SSN, to participate.
<div class="clr mb_7"></div>

The principal purposes for collecting the information on this form, including
your SSN, are to verify your identity, to determine your eligibility to receive
a loan or a benefit on a loan (such as a deferment, forbearance, discharge,
or forgiveness) under the Direct Loan Program, to permit the servicing of
your loan(s), and, if it becomes necessary, to locate you and to collect and
report on your loan(s) if your loan(s) become delinquent or in default. We
also use your SSN as an account identifier and to permit you to access your
account information electronically.
<div class="clr mb_7"></div>

The information in your file may be disclosed, on a case-by-case basis or
under a computer matching program, to third parties as authorized under
routine uses in the appropriate systems of records notices. The routine uses
of this information include, but are not limited to, its disclosure to federal,
state, or local agencies, to private parties such as relatives, present and
former employers, business and personal associates, to consumer reporting
agencies, to financial and educational institutions, and to guaranty agencies
in order to verify your identity, to determine your eligibility to receive a loan
or a benefit on a loan, to permit the servicing or collection of your loan(s),
to enforce the terms of the loan(s), to investigate possible fraud and to
verify compliance with federal student financial aid program regulations, or
to locate you if you become delinquent in your loan payments or if you
default. To provide default rate calculations, disclosures may be made to
guaranty agencies, to financial and educational institutions, or to state
agencies. To provide financial aid history information, disclosures may be
made to educational institutions. To assist program administrators with
tracking refunds and cancellations, disclosures may be made to guaranty</td>

<td width="4%">&nbsp;</td>

<td width="48%" class="style_1">agencies, to financial and educational institutions, or to federal or state
agencies. To provide a standardized method for educational institutions to
efficiently submit student enrollment status, disclosures may be made to
guaranty agencies or to financial and educational institutions. To counsel
you in repayment efforts, disclosures may be made to guaranty agencies, to
financial and educational institutions, or to federal, state, or local agencies.
<div class="clr mb_7"></div>

In the event of litigation, we may send records to the Department of Justice,
a court, adjudicative body, counsel, party, or witness if the disclosure is
relevant and necessary to the litigation. If this information, either alone or
with other information, indicates a potential violation of law, we may send
it to the appropriate authority for action. We may send information to
members of Congress if you ask them to help you with federal student aid
questions. In circumstances involving employment complaints, grievances,
or disciplinary actions, we may disclose relevant records to adjudicate or
investigate the issues. If provided for by a collective bargaining agreement,
we may disclose records to a labor organization recognized under 5 U.S.C.
Chapter 71. Disclosures may be made to our contractors for the purpose of
performing any programmatic function that requires disclosure of records.
Before making any such disclosure, we will require the contractor to
maintain Privacy Act safeguards. Disclosures may also be made to qualified
researchers under Privacy Act safeguards.
<div class="clr mb_7"></div>

<strong>FINANCIAL PRIVACY ACT NOTICE</strong>
<div class="clr mb_7"></div>

Under the Right to Financial Privacy Act of 1978 (12 U.S.C. 3401-3421), ED
will have access to financial records in your student loan file maintained in
compliance with the administration of the Direct Loan Program, and also to
the financial records of any account at a financial institution used to
disburse Direct Loan Funds to you.
<div class="clr mb_7"></div>

<strong>PAPERWORK REDUCTION NOTICE</strong>
<div class="clr mb_7"></div>
According to the Paperwork Reduction Act of 1995, no persons are required
to respond to a collection of information unless the collection displays a
valid OMB control number. The valid OMB control number for this
information collection is 1845-0007. Public reporting burden for this
collection of information is estimated to average 30 minutes (0.5 hours) per
response, including time for reviewing instructions, searching existing data
sources, gathering and maintaining the data needed, and completing and
reviewing the collection of information. The obligation to respond to this
collection is required to obtain a benefit in accordance with 34 CFR
685.201(c)(1).
<div class="clr mb_7"></div>

<strong>If you have comments or concerns regarding the status of your individual submission of this form, contact:</strong>
<div class="clr mb_20"></div>

<div style="text-align:center; padding:25px 0px;">
<div style=" width:160px; height:14px; margin:0 auto; font-weight:bold; background:#FFFF00; padding:2px;">[INSERT SERVICER ADDRESS]</div>
</div>
</td>

</tr>
</table>

</td></tr>

<tr><td>
<table width="100%" border="0" class="pagging_text"><tr><td><span class="pagging_text_font_2">S</span>UBMIT PAGES 1 THROUGH 5<br /><span class="pagging_text_font_2">P</span>AGE <strong>9</strong> OF <strong>16</strong> </td></tr></table>
<table width="100%" class="pagging_text_1"><tr><td align="right">12/2019</td></tr></table>
<div class="clr"></div>

</td></tr></table>
<div class="pagebreak"></div>


<!-- PAGE 10 -->
<table class="page_wrapper mrgn_consolidation style_1" width="100%" cellpadding="0" cellspacing="0">
<tr><td class="page_wrapper_inner style_1">

<table width="100%"><tr>
<td style="padding-top:20px;"><strong>WILLIAM D. FORD FEDERAL DIRECT LOAN PROGRAM<br />DIRECT CONSOLIDATION LOAN BORROWER'S RIGHTS AND RESPONSIBILITIES STATEMENT </strong></td>

<td style="padding-bottom:5px;"><div class="omb_box">
OMB No. 1845-0007<br />
Form Approved<br />
Exp. Date 07/31/2022
</div></td></tr></table>
<div class="clr"></div>
<div class="line_border_1"></div>
<div class="clr mb_10"></div>



<table width="100%">
<tr>
<td width="48%" valign="top" class="style_1">
<strong>ABOUT THE BORROWER’S RIGHTS AND RESPONSIBILITIES STATEMENT (BRR)</strong>
<div class="clr mb_7"></div>

This BRR provides additional information about the terms and conditions of
the loan you will receive under the accompanying Direct Consolidation Loan
Application and Promissory Note (Note). Please keep a copy of the Note and
this BRR for your records. You may request another copy of this BRR at any
time by contacting your servicer. You can also obtain a complete copy of the
Note that you signed, including the BRR, on <a href="https://studentaid.gov/" target="_blank">StudentAid.gov.</a>
<div class="clr mb_7"></div>

Throughout this BRR, the words "we," "us," and "our" refer to the U.S.
Department of Education or our servicers.
<div class="clr mb_7"></div>

<strong style="font-size:7pt;">1. LAWS THAT APPLY TO THIS NOTE AND OTHER LEGAL INFORMATION</strong>
<div class="clr mb_7"></div>

The terms and conditions of loans made under this Note are determined by
the Higher Education Act of 1965, as amended (the HEA), and other federal
laws and regulations. We refer to these laws and regulations as "the Act"
throughout this BRR. Under applicable state law (unless federal law
preempts a state law), you may have certain borrower rights, remedies, and
defenses in addition to those stated in the Note and this BRR.
<div class="clr mb_7"></div>

Any notice we are required to send you related to a loan made under this
Note, even if you do not receive the notice, will be effective if it is sent by
first-class mail to the most recent address that we have for you, sent by
electronic means to an email address you have provided, or sent by any
other method of notification that is permitted or required by the Act. You
must immediately notify your servicer of a change in your contact
information or status (see BRR Item 10).
<div class="clr mb_7"></div>

If we do not enforce a term of this Note, that does not waive our right to
enforce that term or any other term in the future. No term of this Note may
be modified or waived, unless we do so in writing. If any term of this Note is
determined to be unenforceable, the remaining terms remain in force.
<div class="clr mb_7"></div>

<strong>NOTE: Amendments to the Act may change the terms of this Note. Any
amendment to the Act that changes the terms of this Note will be applied
to your loan in accordance with the effective date of the amendment.
Depending on the effective date of the amendment, amendments to the
Act may modify or remove a benefit that existed at the time that you
signed this Note.</strong>
<div class="clr mb_7"></div>

<strong>2. THE WILLIAM D. FORD FEDERAL DIRECT LOAN PROGRAM</strong>
<div class="clr mb_7"></div>

The Direct Loan Program (formally known as the William D. Ford Federal
Direct Loan Program) includes the following types of loans, known
collectively as "Direct Loans":
<div class="clr mb_7"></div>
<ul style="height:100px; padding:0px; margin:0 0 0 20px;">
<li style="height:29px;">Direct Subsidized Loans (formally known as Federal Direct Stafford/Ford Loans)</li>
<li style="height:29px;">Direct Unsubsidized Loans (formally known as Federal Direct Unsubsidized Stafford/Ford Loans)</li>
<li style="height:16px;">Direct PLUS Loans (formally known as Federal Direct PLUS Loans)</li>
<li>Direct Consolidation Loans (formally known as Federal Direct Consolidation Loans)</li>
</ul>
<div class="clr mb_7"></div>

Direct Loans are made by the U.S. Department of Education. We contract with servicers to process Direct Loan payments, deferment and forbearance requests, and other transactions, and to answer questions about Direct Loans. We will provide you with information about how to contact us or our servicers after your loan is made. It is important to keep in contact with your servicer.
<div class="clr mb_7"></div>

If we transfer your loan to another servicer, we will notify you of who your new servicer is, how to contact your new servicer, and when your loan will</td>

<td width="4%"></td>

<td width="48%" valign="top" class="style_1">
be transferred. A transfer of the servicing of your loan to a different servicer
does not affect any of your rights and responsibilities under that loan. You
can find the name of your servicer in the National Student Loan Data System (NSLDS) (see BRR Item 14).
<div class="clr mb_7"></div>

<strong>3. DIRECT CONSOLIDATION LOAN COMPONENTS</strong>
<div class="clr mb_7"></div>

Depending on the types of federal education loans that you consolidate,
your Direct Consolidation Loan may have up to two components, with each
component having a separate loan identification number. However, you will
have only one Direct Consolidation Loan and will receive only one bill.
<div class="clr mb_7"></div>

<strong>3a.</strong> The subsidized component of your Direct Consolidation Loan (identified
as a "Direct Subsidized Consolidation Loan") will have one loan
identification number representing the amount of the following types of
loans that you consolidate:
<div class="clr mb_7"></div>

<ul style="height:90px; padding:0px; margin:0 0 0 20px;">
<li style="height:15px;">Subsidized Federal Stafford Loans</li>
<li style="height:15px;">Direct Subsidized Loans</li>
<li style="height:15px;">Subsidized Federal Consolidation Loans</li>
<li style="height:15px;">Direct Subsidized Consolidation Loans</li>
<li style="height:15px;">Federal Insured Student Loans (FISL)</li>
<li style="height:15px;">Guaranteed Student Loans (GSL)</li>
</ul>
<div class="clr mb_7"></div>

<strong>3b.</strong> The unsubsidized component of your Direct Consolidation Loan
(identified as a "Direct Unsubsidized Consolidation Loan") will have one
identification number representing the amount of the following types of
loans that you consolidate:
<div class="clr mb_7"></div>

<ul style="height:255px; padding:0px; margin:0 0 0 20px;">
<li style="height:15px;">Unsubsidized and Nonsubsidized Federal Stafford Loans</li>
<li style="height:15px;">Direct Unsubsidized Loans</li>
<li style="height:15px;">Unsubsidized Federal Consolidation Loans</li>
<li style="height:15px;">Direct Unsubsidized Consolidation Loans</li>
<li style="height:30px;">Federal PLUS Loans (for parents or for graduate and professional students)</li>
<li style="height:15px;">Direct PLUS Loans (for parents or for graduate and professional students)</li>
<li style="height:15px;">Direct PLUS Consolidation Loans</li>
<li style="height:15px;">Federal Perkins Loans</li>
<li style="height:15px;">National Direct Student Loans (NDSL)</li>
<li style="height:15px;">National Defense Student Loans (NDSL)</li>
<li style="height:15px;">Federal Supplemental Loans for Students (SLS)</li>
<li style="height:15px;">Auxiliary Loans to Assist Students (ALAS)</li>
<li style="height:15px;">Health Professions Student Loans (HPSL)</li>
<li style="height:15px;">Health Education Assistance Loans (HEAL)</li>
<li style="height:15px;">Nursing Student Loans (NSL) and Nurse Faculty Loans</li>
<li>Loans for Disadvantaged Students (LDS)</li>
</ul>
<div class="clr mb_7"></div>

<strong>4. ADDING ELIGIBLE LOANS TO YOUR DIRECT CONSOLIDATION LOAN</strong>
<div class="clr mb_7"></div>

You may add eligible loans to your Direct Consolidation Loan by submitting
a request to us within 180 days of the date your Direct Consolidation Loan is
made. (Your Direct Consolidation Loan is "made" on the date we pay off the
first loan that you are consolidating.) After we pay off any loans that you
add during the 180-day period, we will notify you of the new total amount
of your Direct Consolidation Loan and of any adjustments that must be
made to your monthly payment amount and/or interest rate. If you want to
consolidate any additional eligible loans after the 180-day period, you must
apply for a new Direct Consolidation Loan.
<div class="clr mb_7"></div>

<strong>5. TYPES OF LOANS THAT YOU CAN CONSOLIDATE INTO A DIRECT CONSOLIDATION LOAN</strong>
</td>

</tr>
</table>


</td></tr>

<tr><td>
<table width="100%" border="0" class="pagging_text"><tr><td><span class="pagging_text_font_2">S</span>UBMIT PAGES 1 THROUGH 5<br /><span class="pagging_text_font_2">P</span>AGE <strong>10</strong> OF <strong>16</strong> </td></tr></table>
<table width="100%" class="pagging_text_1"><tr><td align="right">12/2019</td></tr></table>
<div class="clr"></div>

</td></tr></table>
<div class="pagebreak"></div>


<!-- PAGE 11 -->
<table class="page_wrapper mrgn_consolidation style_1" width="100%" cellpadding="0" cellspacing="0">
<tr><td class="page_wrapper_inner style_1">

<table width="100%"><tr>
<td style="padding-top:20px;"><strong>WILLIAM D. FORD FEDERAL DIRECT LOAN PROGRAM<br />DIRECT CONSOLIDATION LOAN BORROWER'S RIGHTS AND RESPONSIBILITIES STATEMENT </strong></td>

<td style="padding-bottom:5px;"><div class="omb_box">
OMB No. 1845-0007<br />
Form Approved<br />
Exp. Date 07/31/2022
</div></td></tr></table>
<div class="clr"></div>
<div class="line_border_1"></div>



<table width="100%">
<tr>
<td width="48%" valign="top">
<strong>General</strong>
<div class="clr mb_7"></div>

Only the federal education loans listed in Items 3a.and 3b. of this BRR may
be consolidated into a Direct Consolidation Loan. You may only consolidate
loans that are in a grace period or in the repayment period (including loans
in deferment or forbearance).
<div class="clr mb_7"></div>

<strong>Defaulted loans</strong>
<div class="clr mb_5"></div>

You may consolidate a loan that is in default if:
<div class="clr mb_5"></div>

<ul style="height:65px; margin:0 0 0 20px; padding:0px;">
<li style="height:30px;">You first make satisfactory repayment arrangements with the holder of the defaulted loan, or</li>
<li>You agree to repay your Direct Consolidation Loan under the SAVE (formerly known as REPAYE) Plan, the PAYE Plan, the IBR Plan, or the ICR Plan (see BRR Item 11).</li>
</ul>
<div class="clr mb_5"></div>

<strong>Consolidation of existing consolidation loans</strong>
<div class="clr mb_5"></div>

If you already have a Direct Consolidation Loan, you may not consolidate
that loan into a new Direct Consolidation Loan unless you include at least
one additional eligible loan in the consolidation.
<div class="clr mb_5"></div>

If you currently have a Federal Consolidation Loan (a consolidation loan that
was made under the FFEL Program), you may consolidate that loan into a
new Direct Consolidation Loan without including an additional loan if you
are:
<div class="clr mb_5"></div>

<ul style="height:140px; margin:0 0 0 20px; padding:0px;">
<li style="height:30px;">Consolidating a delinquent Federal Consolidation Loan that the lender has submitted to the guaranty agency for default aversion;</li>
<li style="height:45px;">Consolidating a defaulted Federal Consolidation Loan, and you agree to repay your new Direct Consolidation Loan under the SAVE (formerly known as REPAYE) Plan, the PAYE Plan, the IBR Plan, or the ICR Plan;</li>
<li style="height:30px;">Consolidating a Federal Consolidation Loan to use the Public Service Loan Forgiveness program described in BRR Item 16; or</li>
<li>Consolidating a Federal Consolidation Loan to use the no accrual of interest benefit for active duty service members described in BRR Item 7.</li>
</ul>
<div class="clr mb_5"></div>

<strong>Consolidation of existing joint consolidation loans</strong>
<div class="clr mb_5"></div>

Before July 1, 2006, married borrowers could combine their individual
federal education loans into a single joint Direct Consolidation Loan or joint
Federal Consolidation Loan. Both borrowers of a joint consolidation loan are
equally responsible for repayment of the full amount of the joint
consolidation loan, regardless of the amount of each individual borrower’s
original loans that were repaid by the joint consolidation loan, and
regardless of any change in marital status.
<div class="clr mb_5"></div>

Because the Act no longer allows borrowers to jointly consolidate, if you
have an existing joint consolidation loan, you and the other borrower with
whom you obtained the loan may not jointly consolidate the loan into a
new joint consolidation loan. In addition, you may not individually
consolidate an existing joint consolidation loan into a new Direct
Consolidation Loan under your name only.
<div class="clr mb_7"></div>

<strong>6. INTEREST RATE</strong>
<div class="clr mb_5"></div>
The interest rate on your Direct Consolidation Loan will be the weighted
average of the interest rates on the loans you are consolidating, rounded to
the nearest higher one-eighth of one percent. We will send you a notice
that tells you the interest rate on your loan.
<div class="clr mb_5"></div>
The interest rate on a Direct Consolidation Loan is a fixed rate. This means
that the interest rate will never change.
<div class="clr mb_5"></div>

<strong>Servicemembers Civil Relief Act</strong>
<div class="clr mb_5"></div>
<strong>If you are in military service, you may qualify for a lower interest rate on your loans.</strong>
</td>

<td width="4%"></td>

<td width="48%" valign="top">
Under the Servicemembers Civil Relief Act (SCRA), the interest rate on loans
you received before you began your military service may be limited to 6%
during your military service. We will determine if you are eligible for this
benefit based on information from the U.S. Department of Defense. If you
are eligible and have qualifying loans with an interest rate greater than 6%,
we will automatically reduce the interest rate on those loans to 6% during
your military service. If you think you qualify for the 6% interest rate but
have not received it, contact your servicer.
<div class="clr mb_5"></div>

Because the SCRA interest rate limit applies only to loans you obtained
before entering military service, if you consolidate your loans after you have
begun a period of active duty military service, your new Direct
Consolidation Loan will not be eligible for the 6% interest rate limit under
the SCRA for that period of active duty.
<div class="clr mb_5"></div>

If you consolidate loans you obtained before you began a period of active
duty military service and the interest rate on those loans is reduced to 6%
under the SCRA, the 6% interest rate will be used to determine the fixed
weighted average interest rate on your Direct Consolidation Loan.
<div class="clr mb_5"></div>

<strong>Interest rate reduction for automatic withdrawal of payments</strong>
<div class="clr mb_5"></div>

You will receive a 0.25% reduction in the interest rate on your loan if you
choose to repay the loan under the automatic withdrawal option. Under the
automatic withdrawal option, we automatically deduct your monthly loan
payment from your checking or savings account. In addition to lowering
your interest rate, automatic withdrawal ensures that your payments are
made on time. We will provide you with information about the automatic withdrawal option.
<div class="clr mb_7"></div>

<strong>7. PERIODS WHEN WE CHARGE INTEREST</strong>
<div class="clr mb_5"></div>

<strong>General</strong>
<div class="clr mb_5"></div>

In general, we charge interest on a Direct Consolidation Loan during all
periods, from the date the loan is made until it is paid in full or discharged.
You are responsible for paying the interest that accrues as explained below.
<div class="clr mb_5"></div>

<strong>Direct Subsidized Consolidation Loans</strong>
<div class="clr mb_5"></div>

We <strong>charge interest</strong> on Direct Subsidized Consolidation Loans—
<div class="clr mb_5"></div>

<ul style="height:60px; margin:0 0 0 20px; padding:0px;">
<li style="height:15px;">During most periods when you are repaying your loans;</li>
<li style="height:15px;">During forbearance periods; and</li>
<li style="height:15px;">During all periods, if you become responsible for paying all interest on your Direct Subsidized Loans (see BRR Item 8).</li>
</ul>
<div class="clr mb_5"></div>

We <strong>do not charge interest</strong> on Direct Subsidized Consolidation Loans—
<div class="clr mb_7"></div>

<ul style="height:95px; margin:0 0 0 20px; padding:0px;">
<li style="height:15px;">While you are enrolled in school at least half-time;</li>
<li style="height:15px;">During deferment periods;</li>
<li style="height:30px;">During some periods of repayment under the SAVE (formerly known as REPAYE), PAYE, and IBR plans; and</li>
<li>During periods of active duty military service that qualify you for the no accrual of interest benefit for active duty service members (see below).</li>
</ul>
<div class="clr mb_5"></div>

<strong>Direct Unsubsidized Consolidation Loans</strong>
<div class="clr mb_5"></div>

We <strong>charge interest</strong> on a Direct Unsubsidized Consolidation Loan—
<div class="clr mb_5"></div>

<ul style="height:60px; margin:0 0 0 20px; padding:0px;">
<li style="height:15px;">While you are enrolled in school at least half-time;</li>
<li style="height:15px;">During most periods when you are repaying your loans;</li>
<li style="height:15px;">During most deferment periods; and</li>
<li style="height:15px;">During forbearance periods.</li>
</ul>
<div class="clr mb_5"></div>

We <strong>do not charge</strong> interest on Direct Unsubsidized Consolidation Loans—
<div class="clr mb_5"></div>
<ul style="margin:0 0 0 20px; padding:0px;">
<li>During some periods of repayment under the SAVE (formerly known as REPAYE) Plan;</li>
</ul>
</td>
</tr>
</table>


</td></tr>

<tr><td>
<table width="100%" border="0" class="pagging_text"><tr><td><span class="pagging_text_font_2">S</span>UBMIT PAGES 1 THROUGH 5<br /><span class="pagging_text_font_2">P</span>AGE <strong>11</strong> OF <strong>16</strong> </td></tr></table>
<table width="100%" class="pagging_text_1"><tr><td align="right">12/2019</td></tr></table>
<div class="clr"></div>

</td></tr></table>
<div class="pagebreak"></div>


<!-- PAGE 12 -->
<table class="page_wrapper mrgn_consolidation style_1" width="100%" cellpadding="0" cellspacing="0">
<tr><td class="page_wrapper_inner style_1">

<table width="100%"><tr>
<td style="padding-top:20px;"><strong>WILLIAM D. FORD FEDERAL DIRECT LOAN PROGRAM<br />DIRECT CONSOLIDATION LOAN BORROWER'S RIGHTS AND RESPONSIBILITIES STATEMENT </strong></td>

<td style="padding-bottom:5px;"><div class="omb_box">
OMB No. 1845-0007<br />
Form Approved<br />
Exp. Date 07/31/2022
</div></td></tr></table>
<div class="clr"></div>
<div class="line_border_1"></div>


<table width="100%">
<tr>
<td width="48%" valign="top">
<ul style="height:60px; margin:0 0 0 20px; padding:0px;">
<li style="height:42px;">During periods of active duty military service that qualify you for the
no accrual of interest benefit for active duty service members (see
below); and</li>
<li>During periods of deferment for cancer treatment (see BRR Item 15).</li>
</ul>
<div class="clr mb_5"></div>

<strong>No accrual of interest benefit for active duty service members</strong>
<div class="clr mb_5"></div>

We do not charge interest during periods while you are on qualifying active
military duty in an area of hostilities where your service qualifies you for
special pay (for up to 60 months) on the portion of a Direct Consolidation
Loan that repaid a Direct Loan or a FFEL Program loan first disbursed on or
after October 1, 2008.
<div class="clr mb_5"></div>

<strong>Interest capitalization</strong>
<div class="clr mb_5"></div>
If you do not pay the interest as it accrues on either a Direct Subsidized
Consolidation Loan or a Direct Unsubsidized Consolidation Loan, we will add
the accrued interest to the unpaid principal balance of your loan. This is
called "capitalization." Capitalization increases the principal amount you
owe on the loan and the total amount of interest that you will pay. We
capitalize unpaid interest when you start making payments again after
periods of deferment or forbearance.
<div class="clr mb_5"></div>

The chart below shows the difference in the total amount you would repay
if you pay the interest as it accrues during a 12-month deferment or
forbearance period, compared to the amount you would repay if you do not
pay the interest and it is capitalized at the end of the deferment or
forbearance period. The example illustrated in the chart assumes the following—
<div class="clr mb_5"></div>

<ul style="height:135px; margin:0 0 0 20px; padding:0px;">
<li style="height:30px;">You consolidated only unsubsidized loans and your Direct Consolidation Loan balance was $40,000 when the loan entered repayment;</li>
<li style="height:15px;">The interest rate on your loan is 6%;</li>
<li style="height:15px;">You are repaying your loans under the Standard Repayment Plan;</li>
<li style="height:30px;">Based on the amount of your Direct Consolidation Loan, your repayment period is 25 years; and</li>
<li>You received a 12-month deferment or forbearance that began on the day after loan entered repayment.</li>
</ul>
<div class="clr mb_5"></div>

<table cellpadding="4" cellspacing="0" border="1" style="width:90%; margin-left:10%;">
<tr>	<td></td>	<td align="center"><strong>If you pay the interest as it accrues…</strong></td>	<td align="center"><strong>If you do not pay the interest and it is capitalized…</strong></td>	</tr>
<tr valign="top"><td>Loan principal amount owed at beginning of deferment or forbearance</td>	<td>$40,000</td>	<td>$40,000</td>	</tr>
<tr valign="top"><td>Interest for 12 months at an annual interest rate of 6%</td>	<td>$2,400<br />(paid as accrued)</td>	<td>$2,400<br />(unpaid and capitalized)</td>	</tr>
<tr valign="top"><td>Loan principal amount to be repaid at end of deferment or forbearance</td>	<td>$40,000</td>	<td>$42,000</td>	</tr>
<tr valign="top"><td>Monthly Payment</td>	<td>$258</td>	<td>$273</td>	</tr>
<tr valign="top"><td>Number of Payments</td>	<td>300</td>	<td>300</td>	</tr>
<tr valign="top"><td>Total Repaid</td>	<td>$79,716*</td>	<td>$81,955</td>	</tr>
</table>

</td>

<td width="4%"></td>

<td width="48%" valign="top">
*The total repaid includes $2,400 in interest that was repaid as it accrued
during the 12-month deferment or forbearance period.
<div class="clr mb_5"></div>

In this example, you would pay $15 less per month and $2,239 less
altogether if you pay the interest as it accrues during the 12-month
deferment or forbearance period.
<div class="clr mb_5"></div>

<strong>Federal income tax deduction for student loan interest payments</strong>
<div class="clr mb_5"></div>

You may be able to claim a federal income tax deduction for interest
payments you make on Direct Loans. For further information, refer to IRS
Publication 970, available at <a href="https://www.irs.gov/publications/p970" target="_blank">https://irs.gov/publications/p970.</a>
<div class="clr mb_5"></div>

<strong>8. RESPONSIBILITY FOR PAYING ALL INTEREST ON ALL OR PART OF THE SUBSIDIZED COMPONENT OF A DIRECT CONSOLIDATION LOAN (IF YOU ARE A FIRST-TIME BORROWER ON OR AFTER JULY 1, 2013)</strong>
<div class="clr mb_5"></div>

If you were a <strong>first-time borrower on or after July 1, 2013</strong> (see Note below) when you received a Direct Subsidized Loan and you are now consolidating that loan, you may be responsible for paying the interest that accrues during all periods on the portion of your Direct Consolidation Loan that repaid the Direct Subsidized Loan, as explained below.
<div class="clr mb_5"></div>

There is a limit on the maximum period of time (measured in academic years) for which a first-time borrower on or after July 1, 2013 can receive Direct Subsidized Loans. In general, a first-time borrower may not receive Direct Subsidized Loans for more than 150% of the published length of his or her program of study. This is called the "maximum eligibility period."
<div class="clr mb_5"></div>

Generally, a first-time borrower on or after July 1, 2013 will become responsible for paying the interest that accrues during all periods on previously received Direct Subsidized Loans if the borrower:
<div class="clr mb_5"></div>

<ul style="height:73px; margin:0 0 0 20px; padding:0px;">
<li style="height:45px;">Continues to be enrolled in any undergraduate program after having received Direct Subsidized Loans for his or her maximum eligibility period, or</li>
<li>Enrolls in another undergraduate program that is the same length as or shorter than the borrower’s previous program.</li>
</ul>
<div class="clr mb_5"></div>

There are a few exceptions to this rule. You may obtain additional information about this requirement and the exceptions at <a href="https://studentaid.gov/" target="_blank">StudentAid.gov.</a>
<div class="clr mb_5"></div>

You must pay the interest that accrues during all periods (including deferment periods) on the portion of your Direct Consolidation Loan that repaid a Direct Subsidized Loan you received as a first-time borrower on or after July 1, 2013 if:
<div class="clr mb_5"></div>

<ul style="height:75px; margin:0 0 0 20px; padding:0px;">
<li style="height:45px;">Before consolidating the Direct Subsidized Loan, you become responsible for paying all interest that accrues on that loan, as explained above; or</li>
<li>After consolidating the Direct Subsidized Loan you become responsible for paying all interest that accrues on that loan, as explained above.</li>
</ul>
<div class="clr mb_5"></div>

<strong>Note:</strong> You are a first-time borrower on or after July 1, 2013 if you had no outstanding balance on a Direct Loan or a Federal Family Education Loan (FFEL) Program loan on July 1, 2013, or if you had no outstanding balance on a Direct Loan or a FFEL program loan on the date you obtained a Direct Loan after July 1, 2013.
<div class="clr mb_5"></div>

<strong>9. LATE CHARGES AND COLLECTION COSTS</strong>
<div class="clr mb_5"></div>

If you do not make any part of a payment within 30 days after it is due, we may require you to pay a late charge. This charge will not be more than 6% of each late payment. We may also require you to pay other charges and fees involved in collecting your loan.
<div class="clr mb_5"></div>

<strong>10. INFORMATION YOU MUST REPORT TO US AFTER YOU RECEIVE YOUR LOAN</strong>
<div class="clr mb_5"></div>
Until your loan is repaid, you must notify your servicer if you:
</td>
</tr>
</table>


</td></tr>

<tr><td>
<table width="100%" border="0" class="pagging_text"><tr><td><span class="pagging_text_font_2">S</span>UBMIT PAGES 1 THROUGH 5<br /><span class="pagging_text_font_2">P</span>AGE <strong>12</strong> OF <strong>16</strong> </td></tr></table>
<table width="100%" class="pagging_text_1"><tr><td align="right">12/2019</td></tr></table>
<div class="clr"></div>

</td></tr></table>
<div class="pagebreak"></div>


<!-- PAGE 13 -->
<table class="page_wrapper mrgn_consolidation style_1" width="100%" cellpadding="0" cellspacing="0">
<tr><td class="page_wrapper_inner style_1">

<table width="100%"><tr>
<td style="padding-top:20px;"><strong>WILLIAM D. FORD FEDERAL DIRECT LOAN PROGRAM<br />DIRECT CONSOLIDATION LOAN BORROWER'S RIGHTS AND RESPONSIBILITIES STATEMENT </strong></td>

<td style="padding-bottom:5px;"><div class="omb_box">
OMB No. 1845-0007<br />
Form Approved<br />
Exp. Date 07/31/2022
</div></td></tr></table>
<div class="clr"></div>
<div class="line_border_1"></div>


<table width="100%">
<tr>
<td width="48%" valign="top">
<ul style="height:120px; margin:0 0 0 20px; padding:0px;">
<li style="height:15px;">Change your address or telephone number;</li>
<li style="height:15px;">Change your name (for example, maiden name to married name);</li>
<li style="height:30px;">Change your employer or your employer’s address or telephone number changes; or</li>
<li>Have any other change in status that would affect your loan (for example, if you receive a deferment while you are unemployed, but you find a job and therefore no longer meet the eligibility requirements for the deferment).</li>
</ul>
<div class="clr mb_7"></div>

<strong>11. REPAYING YOUR LOAN</strong>
<div class="clr mb_7"></div>

<strong>General</strong>
<div class="clr mb_7"></div>

Unless you receive a deferment or forbearance on your loan (see BRR Item
15), your first payment will be due within 60 days after the first payoff
amount for your Direct Consolidation Loan is issued. We will notify you of
the date your first payment is due.
<div class="clr mb_7"></div>

You must make payments on your loan even if you do not receive a bill or
repayment notice.
<div class="clr mb_7"></div>

You must repay the full amount disbursed under the terms of this Note to
pay off the loans that you consolidated, plus interest and other charges and
fees that you may be required to pay under the terms of this Note.
<div class="clr mb_7"></div>

You must generally repay all of your Direct Loans under the same repayment plan.
<div class="clr mb_7"></div>

There are two types of repayment plans: traditional repayment plans and
income-driven repayment plans. We will ask you to choose a repayment
plan before your loan enters repayment. If you do not choose a repayment
plan, we will place you on the Standard Repayment Plan, which may require
you to make a higher monthly payment than other repayment plans.
<div class="clr mb_7"></div>

If you choose a repayment plan that reduces your monthly payment
amount by extending the period of time you have to repay your loan or by
basing your payment on your income, you will likely pay more in interest
over time than you would pay on another repayment plan.
<div class="clr mb_7"></div>

<strong>TRADITIONAL REPAYMENT PLANS</strong>
<div class="clr mb_7"></div>

Under a traditional repayment plan, your required monthly payment
amount is based on the loan amount that you owe, the interest rate on your
loans, and the length of the repayment period.
<div class="clr mb_7"></div>

<strong>The traditional repayment plans described below are available for all Direct Consolidation Loans</strong>
<div class="clr mb_7"></div>

<strong>Standard Repayment Plan</strong>
<div class="clr mb_7"></div>

Under the Standard Repayment Plan, you will make fixed monthly payments
and repay your loan in full within 10 to 30 years (not including periods of
deferment or forbearance) from the date the loan entered repayment,
depending on the amount of your Direct Consolidation Loan and the
amount of your other student loan debt (which may not exceed the amount
you are consolidating) listed in the Loans I Do Not Want to Consolidate
section of your Note (see the chart below). Your payments must be at least
$50 a month and will be more, if necessary, to repay the loan within the
required time period.
<div class="clr mb_7"></div>

<strong>Graduated Repayment Plan</strong>
<div class="clr mb_7"></div>

Under the Graduated Repayment Plan, you will make lower payments at
first, and your payments will gradually increase over time. You will repay
your loan in full within 10 to 30 years (not including periods of deferment or
forbearance) from the date the loan entered repayment, depending on the
total amount of your Direct Consolidation Loan and the amount of your
other student loan debt (which may not exceed the amount you are
consolidating) listed in <strong>Loans I Do Not Want to Consolidate</strong> section of your
<div class="clr"></div>
</td>

<td width="4%"></td>

<td width="48%" valign="top">
Note (see the chart below). Your scheduled monthly payment must at least be equal to the amount of interest that accrues each month. No single scheduled payment will be more than three times greater than any other payment.
<div class="clr mb_7"></div>

<table width="100%" cellpadding="2" cellspacing="0" border="1">
<tr>	<td colspan="2" align="center"><strong>Standard and Graduated Plans: Maximum Repayment Periods</strong></td>	</tr>
<tr>	<td align="center"><strong>Total Education Loan Indebtedness</strong></td>	<td align="center"><strong>Maximum Repayment Period</strong></td>	</tr>
<tr>	<td align="center">Less than $7,500</td>	<td align="center">10 years</td>	</tr>
<tr>	<td align="center">$7,500 to $9,999</td>	<td align="center">12 years</td>	</tr>
<tr>	<td align="center">$10,000 to $19,999</td>	<td align="center">15 years</td>	</tr>
<tr>	<td align="center">$20,000 to $39,999</td>	<td align="center">20 years</td>	</tr>
<tr>	<td align="center">$40,000 to $59,999</td>	<td align="center">25 years</td>	</tr>
<tr>	<td align="center">$60,000 or more</td>	<td align="center">30 years</td>	</tr>
</table>
<div class="clr mb_7"></div>

<strong>Extended Repayment Plan</strong>
<div class="clr mb_7"></div>
You are eligible for the Extended Repayment Plan only if (1) you have an
outstanding balance on Direct Loans that exceeds $30,000, and (2) you did
not have an outstanding balance on a Direct Loan as of October 7, 1998, or
on the date you obtained a Direct Loan on or after October 7, 1998.
<div class="clr mb_7"></div>

Under this plan, you will repay your loan in full over a period not to exceed
25 years (not including periods of deferment or forbearance) from the date
the loan entered repayment. You may choose to make fixed monthly
payments or graduated monthly payments that start out lower and
gradually increase over time. If you make fixed monthly payments, your
payments must be at least $50 a month and will be more, if necessary, to
repay the loan within the required time period. If you make graduated
payments, your scheduled monthly payment must at least be equal to the
amount of interest that accrues each month. No single scheduled payment
under the graduated option will be more than three times greater than any
other payment.
<div class="clr mb_7"></div>

<strong>INCOME DRIVEN REPAYMENT PLANS</strong>
<div class="clr mb_7"></div>

Under an income-driven repayment plan, your required monthly payment
amount is based on your income and family size, instead of being based on
your loan debt, interest rate, and repayment period, as under a traditional
repayment plan. Changes in your income or family size will result in changes
to your monthly payment amount. If you choose an income-driven plan, you
must certify your family size and provide documentation of your income
(and, if you are married, your spouse’s income) each year so that we can
recalculate your payment amount.
<div class="clr mb_7"></div>

Your required monthly payment amount under an income-driven
repayment plan is generally a percentage of your discretionary income. For
all of the income-driven repayment plans except for the Income-Contingent
Repayment Plan, discretionary income is defined as the difference between
your adjusted gross income and 150% of the poverty guideline amount for
your state of residence and family size, divided by 12. For the IncomeContingent Repayment Plan, discretionary income is defined as the
difference between your adjusted gross income and the poverty guideline
amount for your state of residence and family size, divided by 12.
<div class="clr mb_7"></div>

Not all of the income-driven repayment plans are available for all Direct
Consolidation Loans:
<div class="clr mb_7"></div>

<ul style="margin:0 0 0 20px; padding:0px;">
<li><strong>If you are consolidating a parent PLUS loan</strong>, the only income-driven
repayment plan that is available to you is the Income-Contingent
Repayment Plan (ICR Plan). A parent PLUS loan is a Direct PLUS Loan or
Federal PLUS Loan that you obtained to help pay for your child’s
undergraduate education.</li>
</ul>
</td>
</tr>
</table>


</td></tr>

<tr><td>
<table width="100%" border="0" class="pagging_text"><tr><td><span class="pagging_text_font_2">S</span>UBMIT PAGES 1 THROUGH 5<br /><span class="pagging_text_font_2">P</span>AGE <strong>13</strong> OF <strong>16</strong> </td></tr></table>
<table width="100%" class="pagging_text_1"><tr><td align="right">12/2019</td></tr></table>
<div class="clr"></div>

</td></tr></table>
<div class="pagebreak"></div>


<!-- PAGE 14 -->
<table class="page_wrapper mrgn_consolidation style_1" width="100%" cellpadding="0" cellspacing="0">
<tr><td class="page_wrapper_inner style_1">

<table width="100%"><tr>
<td style="padding-top:20px;"><strong>WILLIAM D. FORD FEDERAL DIRECT LOAN PROGRAM<br />DIRECT CONSOLIDATION LOAN BORROWER'S RIGHTS AND RESPONSIBILITIES STATEMENT </strong></td>

<td style="padding-bottom:5px;"><div class="omb_box">
OMB No. 1845-0007<br />
Form Approved<br />
Exp. Date 07/31/2022
</div></td></tr></table>
<div class="clr"></div>
<div class="line_border_1"></div>



<table width="100%">
<tr>
<td width="48%" valign="top">
<ul style="height:30px; margin:0 0 0 20px; padding:0px;">
<li>If you are not consolidating any parent PLUS loans, you may also choose any of the four income-driven repayment plans described below.</li>
</ul>
<div class="clr mb_7"></div>

<strong>SAVE (formerly known as REPAYE) Plan</strong>
<div class="clr mb_7"></div>

The SAVE (formerly known as REPAYE) Plan is available only for Direct Consolidation Loans that did not repay any parent PLUS loans.
<div class="clr mb_7"></div>

Under the SAVE (formerly known as REPAYE) Plan, your monthly payment amount is generally 10% of your discretionary income.
<div class="clr mb_7"></div>

If you are married, the income used to determine your SAVE (formerly known as REPAYE) Plan
payment amount will generally be the combined income of you and your
spouse, regardless of whether you file a joint or separate federal income tax
return. However, your payment amount will be reduced if your spouse also has federal student loans.
<div class="clr mb_7"></div>

Under the SAVE (formerly known as REPAYE) Plan, any remaining loan amount will be forgiven after
you have made the equivalent of either 20 years of qualifying monthly
payments over a period of at least 20 years (if all of the loans repaid by your
Direct Consolidation Loan were obtained for undergraduate study) or 25
years of qualifying payments over a period of at least 25 years (if any of the
loans repaid by your Direct Consolidation Loan were obtained for graduate
or professional study). You may have to pay federal income tax on the loan
amount that is forgiven.
<div class="clr mb_7"></div>

<strong>Pay As You Earn Repayment Plan (PAYE Plan)</strong>
<div class="clr mb_7"></div>

The PAYE Plan is available only for Direct Consolidation Loans that did not repay any parent PLUS loans.
<div class="clr mb_7"></div>

Under the PAYE Plan, your monthly payment amount is generally 10% of
your discretionary income, but it will never be more than the Standard
Repayment Plan amount.
<div class="clr mb_7"></div>

If you are married and file a joint federal income tax return, the income
used to determine your PAYE Plan payment amount will be the combined
adjusted gross income of you and your spouse, but your payment amount
will be reduced if your spouse also has federal student loans.
<div class="clr mb_7"></div>

If you are married and file a separate federal income tax return from your
spouse, only your individual adjusted gross income will be used to
determine your PAYE Plan payment amount.
<div class="clr mb_7"></div>

To initially qualify for the PAYE Plan, the monthly amount you would be
required to pay under this plan, based on your income and family size, must
be less than the amount you would have to pay under the Standard
Repayment Plan.
<div class="clr mb_7"></div>

Under the PAYE Plan, if your loan is not repaid in full after you have made
the equivalent of 20 years of qualifying monthly payments over a period of
at least 20 years, any remaining loan amount will be forgiven. You may have
to pay federal income tax on the loan amount that is forgiven.
<div class="clr mb_7"></div>

<strong>Income-Based Repayment Plan (IBR Plan)</strong>
<div class="clr mb_7"></div>

The IBR Plan is available <strong>only</strong> for Direct Consolidation Loans that did not repay any parent PLUS loans.
<div class="clr mb_7"></div>

Under the IBR Plan, your monthly payment amount is generally 15% of your discretionary income, but it will never be more than the Standard Repayment Plan amount.
<div class="clr mb_7"></div>

If you are married and file a joint federal income tax return, the income used to determine your IBR Plan payment amount will be the combined adjusted gross income of you and your spouse, but your payment amount will be reduced if your spouse also has federal student loans.
</td>

<td width="4%"></td>

<td width="48%" valign="top">
If you are married and file a separate federal income tax return from your
spouse, only your individual adjusted gross income will be used to
determine your IBR Plan payment amount.
<div class="clr mb_7"></div>

To initially qualify for the IBR Plan, the monthly amount you would be required to pay under this plan, based on your income and family size, must
be less than the amount you would have to pay under the Standard Repayment Plan.
<div class="clr mb_7"></div>

Under the IBR Plan, if your loan is not repaid in full after you have made the
equivalent of 25 years of qualifying monthly payments over a period of at
least 25 years, any remaining loan amount will be forgiven. You may have to
pay federal income tax on the loan amount that is forgiven.
<div class="clr mb_7"></div>

<strong>Income-Contingent Repayment Plan (ICR Plan)</strong>
<div class="clr mb_7"></div>

The ICR Plan is available for all Direct Consolidation Loans, including Direct
Consolidation Loans that repaid parent PLUS loans.
<div class="clr mb_7"></div>

Under the ICR Plan, your monthly payment amount will be the lesser of—
<div class="clr mb_7"></div>

<ul style="height:45px; margin:0 0 0 20px; padding:0px;">
<li style="height:15px;">20% of your discretionary income, or</li>
<li>A percentage of what you would repay under a Standard Repayment Plan with a 12-year repayment period.</li>
</ul>
<div class="clr mb_7"></div>

If you are married and file a joint federal income tax return, the income
used to determine your ICR Plan payment amount will be the combined
adjusted gross income of you and your spouse.
<div class="clr mb_7"></div>

If you are married and file a separate federal income tax return from your
spouse, only your individual adjusted gross income will be used to
determine your ICR Plan payment amount.
<div class="clr mb_7"></div>

Until we obtain the information needed to calculate your monthly payment
amount, your payment will equal the amount of interest that accrues
monthly on your loan unless you request a forbearance.
<div class="clr mb_7"></div>

Under the ICR Plan, if your loan is not repaid in full after you have made the
equivalent of 25 years of qualifying monthly payments over a period of at
least 25 years, any remaining loan amount will be forgiven. You may have to
pay federal income tax on the loan amount that is forgiven.
<div class="clr mb_7"></div>

<strong>Additional repayment information</strong>
<div class="clr mb_7"></div>
Under each plan, the number or amount of payments may need to be
adjusted to reflect capitalized interest and/or new loans made to you. We
may also adjust payment dates on your loans or may grant you a
forbearance (see BRR Item 15) to eliminate a past delinquency that remains
even though you are making your scheduled monthly payments.
<div class="clr mb_7"></div>

If you can show to our satisfaction that the terms and conditions of the
repayment plans described above are not adequate to meet your
exceptional circumstances, we may provide you with an alternative
repayment plan.
<div class="clr mb_7"></div>

You can use the Loan Simulator at <a href="https://studentaid.gov/loan-simulator" target="_blank">StudentAid.gov/Loan-Simulator</a> to
evaluate your eligibility for the PAYE and IBR plans and to estimate your
monthly and total payment amounts under all of the repayment plans. The
Loan Simulator is for informational purposes only. We will make the official
determination of your eligibility and payment amount.
<div class="clr mb_7"></div>

Generally, you may change from your current repayment plan to any other
repayment plan you qualify for at any time after you have begun repaying
your loan.
<div class="clr mb_7"></div>
Unless you are required to pay late charges or collection costs, when you
make a payment on your loan, we apply the payment first to outstanding
interest. If the payment amount is more than the amount of outstanding
interest, we apply the remainder of your payment to your loan principal
</td>
</tr>
</table>


</td></tr>

<tr><td>
<table width="100%" border="0" class="pagging_text"><tr><td><span class="pagging_text_font_2">S</span>UBMIT PAGES 1 THROUGH 5<br /><span class="pagging_text_font_2">P</span>AGE <strong>14</strong> OF <strong>16</strong> </td></tr></table>
<table width="100%" class="pagging_text_1"><tr><td align="right">12/2019</td></tr></table>
<div class="clr"></div>

</td></tr></table>
<div class="pagebreak"></div>


<!-- PAGE 15 -->
<table class="page_wrapper mrgn_consolidation style_1" width="100%" cellpadding="0" cellspacing="0">
<tr><td class="page_wrapper_inner style_1">

<table width="100%"><tr>
<td style="padding-top:20px;"><strong>WILLIAM D. FORD FEDERAL DIRECT LOAN PROGRAM<br />DIRECT CONSOLIDATION LOAN BORROWER'S RIGHTS AND RESPONSIBILITIES STATEMENT </strong></td>

<td style="padding-bottom:5px;"><div class="omb_box">
OMB No. 1845-0007<br />
Form Approved<br />
Exp. Date 07/31/2022
</div></td></tr></table>
<div class="clr"></div>
<div class="line_border_1"></div>


<table width="100%">
<tr>
<td width="48%" valign="top">
If you are required to pay late charges or collection costs, we apply your
payment differently depending on your repayment plan. If you are repaying
under a traditional repayment plan or the ICR Plan, we apply your payment
first to late charges and collection costs, then to outstanding interest, and
then to loan principal. If you are repaying under any income-driven
repayment plan other than the ICR Plan, we apply your payment first to
outstanding interest, then to late charges and collection costs, and then to
loan principal.
<div class="clr mb_7"></div>

You can prepay your loan (that is, make loan payments before they are due,
or pay more than the amount due in a month) at any time without penalty.
We apply any prepayments in accordance with the Act. Your servicer can
provide more information about how prepayments are applied.
<div class="clr mb_7"></div>

When you have repaid a loan in full, your servicer will send you a notice
telling you that you have paid off your loan. You should keep this notice in a
safe place.
<div class="clr mb_7"></div>

<strong>12. DEFAULTING ON YOUR LOAN</strong>
<div class="clr mb_7"></div>

Default (failing to repay your loan) is defined in detail in the Terms and
Conditions section of your Note. If you default:
<div class="clr mb_7"></div>

<ul style="height:235px; margin:0 0 0 20px; padding:0px;">
<li style="height:30px;">We will require you to immediately repay the entire unpaid amount of
your loan (this is called "acceleration").</li>
<li style="height:60px;">We may sue you, take all or part of your federal and state tax refunds
and other federal or state payments as authorized by law, and/or
administratively garnish your wages so that your employer is required
to send us part of your salary to pay off your loan.</li>
<li style="height:30px;">You will have to pay reasonable collection fees and costs, plus court
costs and attorney fees in addition to the amount of your loan.</li>
<li style="height:30px;">You will lose eligibility for other federal student financial aid and for
assistance under most federal benefit programs.</li>
<li style="height:30px;">You will lose eligibility for loan deferments, forbearances, and
repayment plans.</li>
<li>We will report your default to nationwide consumer reporting
agencies (see BRR Item 14). This will harm your credit history and may
make it difficult for you to obtain credit cards, home or car loans, or
other forms of consumer credit.</li>
</ul>
<div class="clr mb_7"></div>

If you default on your loan, you will not be charged collection costs if you
respond within 60 days to the initial notice of default that we send to you,
and you enter into a repayment agreement with us, including a loan
rehabilitation agreement, and fulfill that agreement.
<div class="clr mb_7"></div>

<strong>13. CONDITIONS WHEN WE MAY REQUIRE YOU TO IMMEDIATELY REPAY THE FULL AMOUNT OF YOUR LOAN</strong>
<div class="clr mb_7"></div>

We may require you to immediately repay the entire unpaid amount of your loan (this is called "acceleration") if you:
<div class="clr mb_7"></div>

<ul style="height:45px; margin:0 0 0 20px; padding:0px;">
<li style="height:30px;">Make a false statement that causes you to receive a loan that you are not eligible to receive; or</li>
<li>Default on your loan (see BRR Item 12).</li>
</ul>
<div class="clr mb_7"></div>

<strong>14. INFORMATION WE REPORT ABOUT YOUR LOAN</strong>
<div class="clr mb_7"></div>

We will report information about your loan to nationwide consumer
reporting agencies (commonly known as "credit bureaus") and to the
National Student Loan Data System (NSLDS) on a regular basis. This
information will include the amount and repayment status of your loan (for
example, whether you are current or delinquent in making payments). The
information in NSLDS will also identify the servicer of your loan. Schools
may access information in NSLDS for specific purposes that we authorize.
</td>

<td width="4%"></td>

<td width="48%" valign="top">
If you default on a loan, we will report this to nationwide consumer
reporting agencies. We will notify you at least 30 days in advance that we
plan to report default information to a consumer reporting agency unless
you resume making payments on the loan within 30 days of the date of the
notice. You will be given a chance to ask for a review of the debt before we
report a default.<div class="clr mb_7"></div>

If a consumer reporting agency contacts us regarding objections you have
raised about the accuracy or completeness of any information we have
reported, we are required to provide the agency with a prompt response.
We respond to objections submitted to consumer reporting agencies using
the methods established by those agencies.
<div class="clr mb_7"></div>

<strong>15. DEFERMENT AND FORBEARANCE (POSTPONING PAYMENTS)</strong>
<div class="clr mb_7"></div>

<strong>General</strong>
<div class="clr mb_7"></div>

If you meet certain requirements, you may receive a deferment that allows
you to temporarily stop making payments on your loan. If you cannot make
your scheduled loan payments, but do not qualify for a deferment, we may
give you a <strong>forbearance</strong>. A forbearance allows you to temporarily stop
making payments on your loan, temporarily make smaller payments, or
extend the time for making payments.
<div class="clr mb_7"></div>

<strong>Deferment</strong>
<div class="clr mb_7"></div>

<strong>You may receive a deferment:</strong>
<div class="clr mb_7"></div>

<ul style="height:330px; margin:0 0 0 20px; padding:0px;">
<li style="height:15px;">While you are enrolled at least half-time at an eligible school;</li>
<li style="height:30px;">While you are in a full-time course of study in a graduate fellowship program;</li>
<li style="height:30px;">While you are in an approved full-time rehabilitation program for individuals with disabilities;</li>
<li style="height:30px;">While you are unemployed and seeking work (for a maximum of three years);</li>
<li style="height:30px;">While you are experiencing an economic hardship, including serving in the Peace Corps (for a maximum of three years);</li>
<li style="height:60px;">While you are serving on active duty or performing qualifying National Guard duty during a war or other military operation or national emergency and for an additional 180-day period following the demobilization date for your qualifying service;</li>
<li style="height:70px;">For a maximum of 13 months following your active duty service, if you are a current or retired member of the National Guard or reserve component of the U.S. Armed Forces and you are called or ordered to active duty while you are enrolled at least half-time at an eligible school or during your grace period; or</li>
<li>For Direct Loans that were first disbursed on or after September 28, 2018, or for Direct Loans first disbursed before that date that entered repayment on or before September 28, 2018, while you are receiving treatment for cancer and for an additional 6 months after your treatment has ended.</li>
</ul>
<div class="clr mb_7"></div>

In most cases, you will automatically receive a deferment based on your
enrollment in school on at least a half-time basis based on information that
we receive from the school you are attending.
<div class="clr mb_7"></div>

If we process a deferment based on information received from your school,
you will be notified of the deferment and will have the option of canceling
the deferment and continuing to make payments on your loan.
<div class="clr mb_7"></div>

For all other deferments, you (or, for a deferment based on active duty
military service or National Guard duty, a representative acting on your
behalf) must submit a deferment request to your servicer, along with
documentation of your eligibility for the deferment.
</td>
</tr>
</table>


</td></tr>

<tr><td>
<table width="100%" border="0" class="pagging_text"><tr><td><span class="pagging_text_font_2">S</span>UBMIT PAGES 1 THROUGH 5<br /><span class="pagging_text_font_2">P</span>AGE <strong>15</strong> OF <strong>16</strong> </td></tr></table>
<table width="100%" class="pagging_text_1"><tr><td align="right">12/2019</td></tr></table>
<div class="clr"></div>

</td></tr></table>
<div class="pagebreak"></div>


<!-- PAGE 16 -->
<table class="page_wrapper mrgn_consolidation style_1" width="100%" cellpadding="0" cellspacing="0">
<tr><td class="page_wrapper_inner style_1">

<table width="100%"><tr>
<td style="padding-top:20px;"><strong>WILLIAM D. FORD FEDERAL DIRECT LOAN PROGRAM<br />DIRECT CONSOLIDATION LOAN BORROWER'S RIGHTS AND RESPONSIBILITIES STATEMENT </strong></td>

<td style="padding-bottom:5px;"><div class="omb_box">
OMB No. 1845-0007<br />
Form Approved<br />
Exp. Date 07/31/2022
</div></td></tr></table>
<div class="clr"></div>
<div class="line_border_1"></div>



<table width="100%">
<tr>
<td width="48%" valign="top">
<div class="clr mb_2"></div>
<strong>Forbearance</strong>
<div class="clr mb_7"></div>
We may give you a forbearance if you are temporarily unable to make your
scheduled loan payments for reasons including, but not limited to, financial
hardship and illness.
<div class="clr mb_7"></div>

You may also receive a forbearance if:
<div class="clr mb_7"></div>

<ul style="height:175px; margin:0 0 0 20px; padding:0px;">
<li style="height:32px;">You are serving in a qualifying medical or dental internship or residency program;</li>
<li style="height:45px;">The total amount you owe each month for all of your federal student loans is 20% or more of your total monthly gross income (for a maximum of three years);</li>
<li style="height:17px;">You are serving in an AmeriCorps position;</li>
<li style="height:33px;">You are performing service that would qualify you for loan forgiveness under the Teacher Loan Forgiveness program (see BRR Item 16);</li>
<li style="height:33px;">You qualify for partial repayment of your loans under a student loan repayment program administered by the Department of Defense; or</li>
<li>You are called to active duty in the U.S. Armed Forces.</li>
</ul>
<div class="clr mb_7"></div>

<strong>To request a forbearance, contact your servicer.</strong>
<div class="clr mb_7"></div>

Under certain circumstances, we may also give you a forbearance without
requiring you to submit a request or documentation (for example, while we
are determining your eligibility for a loan discharge, or during periods when
you are affected by a local or national emergency).
<div class="clr mb_7"></div>

<strong>16. DISCHARGE (HAVING YOUR LOAN FORGIVEN)</strong>
<div class="clr mb_7"></div>

<strong>General</strong>
<div class="clr mb_7"></div>

If you meet certain conditions as described below, we may discharge
(forgive) some or all of your loans.
For a discharge based on your death, a family member must contact your
servicer. To request a loan discharge based on one of the other conditions
described below (except for a discharge due to bankruptcy), you must
complete a loan discharge or forgiveness application and send it to your
servicer. Your servicer can tell you how to apply.
<div class="clr mb_7"></div>

We do not guarantee the quality of the academic programs provided by
schools that participate in federal student financial aid programs. You
cannot have your loan discharged solely because you do not complete the
education paid for with your loan, are unable to obtain employment in the
field of study for which your school provided training, or are dissatisfied
with, or do not receive, the education you paid for with your loan.
<div class="clr mb_7"></div>

<strong>Death, total and permanent disability, and bankruptcy</strong>
<div class="clr mb_7"></div>

We will discharge (forgive) your loan if:
<div class="clr mb_7"></div>

<ul style="height:110px; margin:0 0 0 20px; padding:0px;">
<li style="height:58px;">You die. We must receive acceptable documentation (as defined in the
Act) of your death. We will also discharge the portion of a Direct
Consolidation Loan that repaid one or more Direct PLUS Loans or
Federal PLUS Loans obtained on behalf of a child who dies;</li>
<li style="height:17px;">You become totally and permanently disabled; or</li>
<li>Your loan is discharged in bankruptcy after you have proven to the
bankruptcy court that repaying the loan would cause undue hardship.</li>
</ul>
<div class="clr mb_7"></div>

<strong>School closure, false certification, identity theft, and unpaid refund</strong>
<div class="clr mb_7"></div>

We may also discharge all or a portion of your loan if:
<div class="clr mb_7"></div>

<ul style="height:75px; margin:0 0 0 20px; padding:0px;">
<li>One or more Direct Loan Program, FFEL Program, or Federal Perkins
Loan Program loans that you consolidated was used to pay for a
program of study that you (or the child for whom you borrowed a
Direct PLUS Loan or Federal PLUS Loan) were unable to complete
because the school closed;</li>
</ul>
</td>

<td width="4%"></td>

<td width="48%" valign="top">
<div class="clr mb_2"></div>

<ul style="height:135px; margin:0 0 0 20px; padding:0px;">
<li style="height:60px;">Your eligibility (or the eligibility of the child for whom you borrowed a Direct PLUS Loan or Federal PLUS Loan) for one or more of the Direct Loan Program or FFEL Program loans that you consolidated was falsely certified by the school;</li>
<li style="height:45px;">Your eligibility for one or more of the Direct Loan Program or FFEL Program loans that you consolidated was falsely certified as a result of a crime of identity theft; or</li>
<li>The school did not pay a required refund of one or more Direct Loan Program or FFEL Program loans that you consolidated.</li>
</ul>
<div class="clr mb_7"></div>

<strong>Teacher Loan Forgiveness</strong>
<div class="clr mb_7"></div>

We may forgive a portion of your Direct Consolidation Loan that repaid
eligible student loans you received under the Direct Loan Program or the
FFEL Program if you teach full time for five consecutive years in certain lowincome elementary or secondary schools, or for low-income educational
service agencies, and meet certain other requirements.
<div class="clr mb_7"></div>

Eligible teachers of math, science, or special education may receive up to $17,500 in loan forgiveness. Other teachers may receive up to $5,000 in loan forgiveness.
<div class="clr mb_7"></div>

<strong>Public Service Loan Forgiveness</strong>
<div class="clr mb_7"></div>

A Public Service Loan Forgiveness (PSLF) program is also available. Under
this program, we will forgive the remaining balance due on your Direct
Loans after you have made 120 payments (after October 1, 2007) on those
loans under certain repayment plans while you are employed full-time by a
qualifying employer. The required 120 payments do not have to be
consecutive. Qualifying repayment plans include the SAVE (formerly known as REPAYE) Plan, the PAYE
Plan, the IBR Plan, the ICR Plan, and the Standard Repayment Plan with a
10-year repayment period.
<div class="clr mb_7"></div>

<strong>Note:</strong> Although the Standard Repayment Plan with a 10-year repayment
period is a qualifying repayment plan for PSLF, to receive any loan
forgiveness under this program you must enter the SAVE (formerly known as REPAYE) Plan, the PAYE
Plan, the IBR Plan, or the ICR Plan, and make the majority of the 120
payments under one of those plans.
<div class="clr mb_7"></div>

<strong>Borrower defense to repayment</strong>
<div class="clr mb_7"></div>

We may discharge some or all of the portion of your Direct Consolidation
Loan that repaid loans you obtained to attend a school if that school did
something or failed to do something related to those loans or to the
educational services that the loans were intended to pay for.
<div class="clr mb_7"></div>

The specific requirements to qualify for a borrower defense to repayment
discharge vary depending on when you received the loans that were repaid
by your Direct Consolidation Loan. Contact your servicer for more
information.
<div class="clr mb_7"></div>
<strong>END OF BORROWER’S RIGHTS AND RESPONSIBILITIES STATEMENT</strong>
</td>
</tr>
</table>


</td></tr>

<tr><td>
<table width="100%" border="0" class="pagging_text"><tr><td><span class="pagging_text_font_2">S</span>UBMIT PAGES 1 THROUGH 5<br /><span class="pagging_text_font_2">P</span>AGE <strong>16</strong> OF <strong>16</strong> </td></tr></table>
<table width="100%" class="pagging_text_1"><tr><td align="right">12/2019</td></tr></table>
<div class="clr"></div>

</td></tr></table>


</div>


<?php
for ($ia = 0; $ia <= 4; $ia++) {
						$limit_start = (9 + ($i * 4));
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
<td align="center" style="padding:3px; background:#f1f4ff;"><?php echo $loan_code_arr[$loan_type]; ?></td>

<td style="padding:3px; background:#f1f4ff;"><?php echo $lrow['loan_contact_name'] . "<br />" . $lrow['loan_contact_street_address_1'] . " " . $lrow['loan_contact_street_address_1'] . " " . $lrow['loan_contact_city'] . " " . $lrow['loan_contact_state'] . " " . $lrow['loan_contact_zip_code']; ?></td>
<td style="padding:3px; background:#f1f4ff;"></td>
<td style="padding:3px; background:#f1f4ff;"><?php echo $fmt->formatCurrency(($lrow['loan_outstanding_principal_balance'] + $lrow['loan_outstanding_interest_balance']), "USD"); ?></td>
</tr>
<?php }?>

<?php for ($i = count($lrows); $i < 4; $i++) {?>
<tr style="font-size:13px; vertical-align:top;">
<td align="center" style="vertical-align:middle; padding:3px; background:#f1f4ff;">&nbsp;</td>
<td style="padding:3px; background:#f1f4ff;">&nbsp;</td>
<td style="padding:3px; background:#f1f4ff;">&nbsp;</td>
<td style="padding:3px; background:#f1f4ff;">&nbsp;</td>
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
<td align="center" style="vertical-align:middle; padding:3px; background:#f1f4ff;">&nbsp;</td>
<td style="padding:3px; background:#f1f4ff;">&nbsp;</td>
<td style="padding:3px; background:#f1f4ff;">&nbsp;</td>
<td style="padding:3px; background:#f1f4ff;">&nbsp;</td>
</tr>
<?php }?>
</table>


</td>
</tr>
</table>
<?php }?>

<?php }?>


<div class="pagebreak"></div>
<?php }?>



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
    <td colspan="5"><input type="text" class="input_2" name="ssn" value="<?php echo $ansR[3]['intake_comment_body']; ?>" style="width:150px;" /></td>
</tr>

<tr>
	<td align="right" class="font-13">Name</td>	<td width="1%">&nbsp;</td>
    <td colspan="5"><input type="text" class="input_2" name="name" value="<?php echo $idr['name']; ?>" /></td>
</tr>

<tr>
	<td align="right" class="font-13">Address</td>	<td width="1%">&nbsp;</td>
    <td colspan="5"><input type="text" class="input_2" name="address" value="<?php echo $ansR[5]['intake_comment_body']; ?>" /></td>
</tr>

<tr>
	<td align="right" class="font-13">City</td>	<td width="1%">&nbsp;</td>
    <td><input type="text" class="input_2" name="address" value="<?php echo $ansR[6]['intake_comment_body']; ?>" /></td>

    <td width="1%" align="right" class="font-13">State</td>
    <td><input type="text" class="input_2" name="address" value="<?php echo $ansR[7]['intake_comment_body']; ?>" /></td>

    <td width="45" align="right" class="font-13">Zip Code</td>
    <td width="40"><input type="text" class="input_2" name="address" value="<?php echo $ansR[8]['intake_comment_body']; ?>" /></td>
</tr>

<tr>
	<td align="right" class="font-13">Telephone - Primary</td>	<td width="1%">&nbsp;</td>
    <td colspan="5"><input type="text" class="input_2" name="address" value="<?php echo preg_replace('/\d{3}/', '$0-', str_replace('.', null, trim($user['phone'])), 2); ?>" /></td>
</tr>

<tr>
	<td align="right" class="font-13">Telephone - Alternate</td>	<td width="1%">&nbsp;</td>
    <td colspan="5"><input type="text" class="input_2" name="address" value="<?php echo preg_replace('/\d{3}/', '$0-', str_replace('.', null, trim($user['telephone_alternate'])), 2); ?>" /></td>
</tr>

<tr>
	<td align="right" class="font-13">Email (Optional)</td>	<td width="1%">&nbsp;</td>
    <td colspan="5"><input type="text" class="input_2" name="address" value="<?php echo $ansR[10]['intake_comment_body']; ?>" /></td>
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
    <td><input type="text" class="input_5" name="bn" value="<?php echo $idr['name']; ?>" style="width:90%;" /></td>
	<td width="70"><strong>Borrower SSN</strong></td>
    <td width="100"><input type="text" class="input_5" name="bssn" value="<?php echo $ansR[3]['intake_comment_body']; ?>" /></td>
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
    	<tr><td width="15" valign="top"><input type="checkbox" <?php if ($marital_status == "14") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>Single - Skip to Item 11.</td></tr>
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($marital_status == "15") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>Married - Continue to Item 8.</td></tr>
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($marital_status == "72") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>Married, but separated - You will be treated as single. Skip to Item 11.</td></tr>
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($marital_status == "73") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>Married, but cannot reasonably access my spouse's income information - You will be treated as single. Skip to Item 11.</td></tr>
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
    	<tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[11] == "1") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>Yes - Continue to Item 12.</td></tr>
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[11] == "2") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>No - Provide your most recent federal income tax return or transcript. Skip to Section 6.</td></tr>
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[11] == "3") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>I haven't filed a federal income tax return in the last two years - Continue to Item 12.</td></tr>
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
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[12] == "1") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>Yes - Provide documentation of your income as instructed in Section 5. Skip to that section</td></tr>
        <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[12] == "2") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>No - You are not required to provide documentation
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
    <td><input type="text" class="input_5" name="bn" value="<?php echo $idr['name']; ?>" style="width:90%;" /></td>
	<td width="70"><strong>Borrower SSN</strong></td>
    <td width="100"><input type="text" class="input_5" name="bssn" value="<?php echo $ansR[3]['intake_comment_body']; ?>" /></td>
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
    <td><input type="text" class="input_5" name="bn" value="<?php echo $idr['name']; ?>" style="width:90%;" /></td>
	<td width="70"><strong>Borrower SSN</strong></td>
    <td width="100"><input type="text" class="input_5" name="bssn" value="<?php echo $ansR[3]['intake_comment_body']; ?>" /></td>
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
<td align="center" style="padding:3px; background:#f1f4ff;"><?php echo $loan_code_arr[$loan_type]; ?></td>
<td style="padding:3px; background:#f1f4ff;"><?php echo $lrow['loan_contact_name'] . "<br />" . $lrow['loan_contact_street_address_1'] . " " . $lrow['loan_contact_street_address_1'] . " " . $lrow['loan_contact_city'] . " " . $lrow['loan_contact_state'] . " " . $lrow['loan_contact_zip_code']; ?></td>
<td style="padding:3px; background:#f1f4ff;"></td>
<td style="padding:3px; background:#f1f4ff;"><?php echo $fmt->formatCurrency(($lrow['loan_outstanding_principal_balance'] + $lrow['loan_outstanding_interest_balance']), "USD"); ?></td>
</tr>
<?php }?>

<?php for ($i = count($lrows); $i < 4; $i++) {?>
<tr style="font-size:13px; vertical-align:top;">
<td align="center" style="vertical-align:middle; padding:3px; background:#f1f4ff;">&nbsp;</td>
<td style="padding:3px; background:#f1f4ff;">&nbsp;</td>
<td style="padding:3px; background:#f1f4ff;">&nbsp;</td>
<td style="padding:3px; background:#f1f4ff;">&nbsp;</td>
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
<td align="center" style="vertical-align:middle; padding:3px; background:#f1f4ff;">&nbsp;</td>
<td style="padding:3px; background:#f1f4ff;">&nbsp;</td>
<td style="padding:3px; background:#f1f4ff;">&nbsp;</td>
<td style="padding:3px; background:#f1f4ff;">&nbsp;</td>
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