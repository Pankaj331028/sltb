<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
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
			$intk_idr = $ics['form_data'];

			$ink = $this->db->query('select * from intake_client_status where client_id=' . $client_id . ' and (intake_id=1 or intake_id=4) order by id desc')->row_array();
			$idr_formdata = (array) json_decode($ink['form_data']);

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
    .style_2 {color:#000000; font-size:12pt;margin-top: 20px;}
    .style_3 {color:#000000; font-size:10pt;}
    .em_ul_style { text-decoration:underline; font-style:normal;}
    .line_border_1 { width:100%; height:4px; margin:3px 0px; background:#000000;}
    .line_border_2 { width:100%; height:1px; margin:5px 0px; background:#000000;}
    .line_border_3 { width:100%; height:2px; margin:2px 0px 0px 0px; background:#000000;}

    .input_td { background:transparent; color:#777777; font-size:13px; padding:3px 5px; border-bottom:1px solid #888888; }
    .input_div_2 {width:auto; height:13px; font-size:12px; color:#000000; border-bottom:1px solid #000; padding:2px 5px;}
    .input_2 {width:100%; height:15px; padding:2px 2px 2px 2px; color:#444444; font-size:10pt; border:none; border-bottom:.2px solid #000;background: transparent;}
    .input_3 {width:100%; height:15px; padding:2px 2px 2px 2px; background:transparent; font-size:13px; border:none;}
    .input_4 {width:100%; height:13px; padding:2px 2px 2px 2px; color:#000; background:transparent; font-size:7.5pt; border:none; border-bottom:.5px solid #888888;}
    .input_5 {width:100%; height:15px; padding:2px 2px 2px 2px; color:#444444; font-size:10pt; border:none; border-bottom:1px solid #000;}

    .omb_box { float:right; width:80px; height:35px; padding:5px 10px; margin-right:10px; border:1px solid #000000; font-size:5.5pt; line-height:8pt; font-family:Arial, Helvetica, sans-serif; }

    .page_wrapper { width:100%; margin:0 auto; overflow:hidden; font-family:Arial, Helvetica, sans-serif; }
    .page_wrapper_inner { width:100%; vertical-align:top; }
    .page_wrapper_inner_2 { width:100%; vertical-align:top; }

    .mrgn_consolidation { margin-top:-15px; }
    /*.mrgn_idr { margin:-22px -25px -25px -23px; }*/
    .pagebreak { display: block; height:0px; clear: both; page-break-after: always; }
    .pagging_text { font-size:6pt; text-align:center; }
    .pagging_text_font_2 { font-size:7.5pt; }
    .pagging_text_1 { font-size:8pt; text-align:right; margin-top:-5px; font-weight:bold; font-style:italic; }
    .idr_paging { font-size:9pt; text-align:center; }

    .footer {
        position: fixed;
        font-size:9pt;
        bottom: 0;
        width:100%;
        text-align: center;
        font-family:Arial, Helvetica, sans-serif;
    }
    .footer .page:after {
        content: counter(page);
    }

    ul li,ol li{
        margin-bottom: 10px;
    }
    /*}*/
</style>
</head>
<body>
<?php
if ($intkR['intake_title'] == "IDR Intake" || $intkR['intake_title'] == "Consolidation Intake" || $intkR['intake_title'] == "Recertification Intake" || $intkR['intake_title'] == "Recalculation Intake" || $intkR['intake_title'] == "Switch IDR Intake") {
				$ssn = explode("-", $fd['ssn']);
				?>
    <div class="footer">
        <p style="position: relative;">Page <strong class="page"><?php echo $PAGE_NUM ?></strong> of <strong class="total_pages" style="position: absolute;left: 54%;">DOMPDF_PAGE_COUNT_PLACEHOLDER</strong></p>
    </div>
    <div>
    <!--    18C IDR 2021 CODED (PAGE 1) -->
    <table class="page_wrapper mrgn_idr" cellpadding="0" cellspacing="0">
        <tr>
            <td class="page_wrapper_inner_2 style_2" style="padding:0px 8px;">
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:0px;">
                    <tr style="border:0px;">
                        <td width="15%" valign="top" align="center">
                            <img src="<?php echo 'data:image/png;base64,' . base64_encode(file_get_contents(("assets/img/logo_doe2.png"))); ?>" width="80" alt="Logo" /><br /><strong style="font-size:14px;">IDR</strong>
                        </td>
                        <td width="65%" valign="top" style="padding-left:5px;">
                            <strong style="font-size:13.5pt;">SWITCH INCOME-DRIVEN REPAYMENT (SWITCH IDR) PLAN REQUEST</strong>
                            <div class="clr mb_2"></div>
                            <strong style="font-size:9.7pt;">For the Saving on a Valuable Education (SAVE) (formerly known as Revised Pay As You Earn (REPAYE)), Pay As You Earn (PAYE), Income-Based Repayment (IBR), and Income-Contingent Repayment (ICR) plans under the William D. Ford Federal Direct Loan (Direct Loan) Program and Federal Family Education Loan (FFEL) Programs</strong>
                        </td>
                        <td width="20%" valign="top" style="font-size:9pt;line-height:13pt;">
                            <div style="float:right; margin-right:10px;">OMB No. 1845-0102 <br />Form Approved<br />Exp. Date: 1/31/2024</div>
                        </td>
                    </tr>
                </table>
                <div class="clr"></div>
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:0px;margin: 20px 0 10px 0;">
                    <tr>
                        <td colspan="3" style="font-size:10pt; padding-left:10px;">
                            <strong>WARNING/IMPORTANT:</strong> Any person who knowingly makes a false statement or misrepresentation on this form or on any accompanying document is subject to penalties that may include fines, imprisonment, or both, under the U.S. Criminal Code and 20 U.S.C. 1097.
                        </td>
                    </tr>
                </table>
                <strong class="style_2"> &nbsp; SECTION 1: BORROWER INFORMATION</strong>
                <div class="line_border_3"></div>
                <div class="clr"></div>
                <table width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;padding: 10px;">
                    <tr>
                        <td width="30%">&nbsp;</td>
                        <td width="70%">
                            <span>Please enter or correct the following information.</span>
                        </td>
                    </tr>
                    <tr>
                        <td width="30%"></td>
                        <td width="70%" valign="middle;">
                            <input type="checkbox" <?php if (isset($radio['idr_page_1_correct_info'])) {if ($radio['idr_page_1_correct_info'] == "Yes") {echo ' checked="checked"';}}?> style="margin-top:5px;font-size: 20px;" />
                            <strong>&nbsp;&nbsp;Check this box if any of your information has changed.</strong>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td width="30%">&nbsp;</td>
                        <td width="70%" class="font-13" style="padding: 2px 0px;">Social Security Number:
                            <input type="text" class="input_2" name="ssn" value="<?php echo $fd['ssn']; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td width="30%">&nbsp;</td>
                        <td width="70%" class="font-13" style="padding: 2px 0px;">Name:
                            <input type="text" class="input_2" name="name" value="<?php echo $fd['name']; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td width="30%">&nbsp;</td>
                        <td width="70%" class="font-13" style="padding: 2px 0px;">Address:
                           <input type="text" class="input_2" name="address" value="<?php echo $fd['address']; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td width="30%">&nbsp;</td>
                        <td width="70%" class="font-13" style="padding: 2px 0px;">
                            City:
                            <input type="text" class="input_2" name="address" style="width: 30%" value="<?php echo $fd['city']; ?>" />
                            State:
                            <input type="text" class="input_2" name="address" style="width: 20%;word-break: break-all;word-wrap: break-word;" value="<?php echo $fd['state']; ?>" />
                            Zip Code:
                            <input type="text" class="input_2" name="address" style="width: 15%" value="<?php echo $fd['zipcode']; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td width="30%">&nbsp;</td>
                        <td width="70%" class="font-13" style="padding: 2px 0px;">Telephone - Primary:
                           <input type="text" class="input_2" name="address" value="<?php echo preg_replace('/\d{3}/', '$0-', str_replace('.', null, trim($fd['telephone_primary'])), 2); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td width="30%">&nbsp;</td>
                        <td width="70%" class="font-13" style="padding: 2px 0px;">Telephone - Alternate:
                           <input type="text" class="input_2" name="address" value="<?php echo preg_replace('/\d{3}/', '$0-', str_replace('.', null, trim($fd['telephone_alternate'])), 2); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td width="30%">&nbsp;</td>
                        <td width="70%" class="font-13" style="padding: 2px 0px;">Email (Optional):
                           <input type="text" class="input_2" name="address" value="<?php echo $fd['email']; ?>" />
                        </td>
                    </tr>

                </table>
                <div class="clr"></div>

                <strong class="style_2"> &nbsp; SECTION 2: REPAYMENT PLAN OR RECERTIFICATION REQUEST</strong>
                <div class="line_border_3"></div>
                <div class="clr mb_5"></div>
                <table width="100%">
                    <tr>
                        <td class="style_3" style="padding:0 5px;">
                            It's faster and easier to complete this form online at <a href="https://studentAid.gov" target="_blank">StudentAid.gov.</a> You can learn more at <a href="https://studentAid.gov/IDR" target="_blank">StudentAid.gov/IDR</a> and by reading Sections 9 and 10. It's simple to get repayment estimates at <a href="https://studentAid.gov/repayment-estimator" target="_blank">StudentAid.gov/repayment-estimator.</a> You never need to pay for assistance completing this form, contact your loan holder or servicer for free assistance. You can find out who your loan holder or servicer is at <a href="https://studentAid.gov/login" target="_blank">StudentAid.gov/login.</a> You may have to report any loan amount forgiven under an income-driven plan as taxable income when you file your federal and/or state tax returns.
                        </td>
                    </tr>
                </table>
                <div class="clr mb_5"></div>

                <table width="100%" class="style_3" style="margin:10px 5px;" cellpadding="0" cellspacing="0">
                    <tr>
                        <td width="50%" valign="top">
                            <table width="100%">
                                <tr>
                                    <td valign="top">
                                        <strong>1. </strong>
                                    </td>
                                    <td>
                                        <strong>Select the reason you are submitting this form<br />(Check only one):</strong>
                                        <table width="100%">
                                            <tr><td width="15" style="margin: 10px 0" valign="top"><input type="checkbox" <?php if ($radio[1] == "1") {echo ' checked="checked"';}?> /></td> <td>I want to <em class="em_ul_style">enter an income-driven plan</em><strong> - Continue to Item 2.</strong></td></tr>
                                            <tr><td width="15" style="margin: 10px 0" valign="top"><input type="checkbox" <?php if ($radio[1] == "2") {echo ' checked="checked"';}?> /></td> <td>I am submitting documentation for the <em class="em_ul_style">annual recertification</em> of my income-driven payment <strong>- Skip to Item 3.</strong></td></tr>
                                            <tr><td width="15" style="margin: 10px 0" valign="top"><input type="checkbox" <?php if ($radio[1] == "3") {echo ' checked="checked"';}?> /></td> <td>I am submitting documentation early to have my income-driven <em class="em_ul_style">payment recalculated immediately</em> <strong>- Skip to Item 3.</strong></td></tr>
                                            <tr><td width="15" style="margin: 10px 0" valign="top"><input type="checkbox" <?php if ($radio[1] == "4") {echo ' checked="checked"';}?> /></td> <td>I want to <em class="em_ul_style">change to a different income-driven plan</em> <strong>- Continue to Item 2.</strong></td></tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="top"><strong>2. </strong></td>
                                    <td><strong>Choose a plan and then continue to Item 3.</strong>
                                        <table width="100%">
                                            <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[2] == "1") {echo ' checked="checked"';}?> /></td> <td colspan="5">(Recommended) I want the income-driven repayment plan with the lowest monthly payment.</td></tr>
                                            <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[2] == "2") {echo ' checked="checked"';}?> style="margin-top:-5px;" /></td> <td colspan="5">SAVE (formerly known as REPAYE)</td></tr>
                                            <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[2] == "3") {echo ' checked="checked"';}?> style="margin-top:-5px;" /></td> <td>IBR</td><td width="15" valign="top"><input type="checkbox" <?php if ($radio[2] == "4") {echo ' checked="checked"';}?> style="margin-top:-5px;" /></td> <td>PAYE</td>
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
                                    <td>
                                        <strong>Do you have multiple loan holders or servicers?</strong>
                                        <table width="100%">
                                            <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[3] == "1") {echo ' checked="checked"';}?> style="margin-top:-5px;" /></td> <td>Yes - Submit a request to each holder or servicer <strong>- Continue to Item 4.</strong></td></tr>
                                            <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[3] == "2") {echo ' checked="checked"';}?> style="margin-top:-5px;" /></td> <td>No <strong>- Continue to Item 4.</strong></td></tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="top" style="padding-top:5px;"><strong>4. </strong></td>
                                    <td style="padding-top:5px;">
                                        <strong>Are you currently in deferment or forbearance?</strong>
                                        <table width="100%">
                                            <tr> <td></td> <td>After answering, continue to Item 5.</td></tr>
                                            <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[4] == "1") {echo ' checked="checked"';}?> style="margin-top:-5px;" /></td> <td>No.</td></tr>
                                            <tr><td valign="top"><input type="checkbox" <?php if ($radio[4] == "2") {echo ' checked="checked"';}?> style="margin-top:-5px;" /></td> <td>Yes, but I want to start making payments under my plan immediately.</td></tr>
                                            <tr><td valign="top"><input type="checkbox" <?php if ($radio[4] == "3") {echo ' checked="checked"';}?> style="margin-top:-5px;" /></td> <td>Yes, and I do not want to start repaying my loans until the deferment or forbearance ends.</td></tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <table style="margin-top: 10px;">
                                <tr>
                                    <td style="background:#f0f0f0; width:100%; padding:5px; border:1px solid #999999;">
                                        <strong>Note:</strong> If you have FFEL Program loans, they are only eligible for IBR. However, you can consolidate your loans at <a href="https://studentaid.gov" target="_blank">StudentAid.gov</a> to access more beneficial income-driven repayment plans.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

    </table>
    <div class="pagebreak"></div>

    <!--    18C IDR 2021 CODED (PAGE 2) -->

    <table class="page_wrapper mrgn_idr" cellpadding="0" cellspacing="0">
        <tr>
            <td class="page_wrapper_inner_2 style_2" style="padding:0px 8px;">
                <strong class="style_2"> &nbsp; SECTION 3: FAMILY SIZE INFORMATION</strong>
                <div class="line_border_3"></div>
                <div class="clr"></div>

                <table width="100%" border="0">
                    <tr style="font-size: 10pt;margin-top: 10px;">
                        <td width="80"><strong>Borrower Identifiers:</strong> Borrower Name: <input type="text" class="input_5" name="bn" value="<?php echo $fd['name']; ?>" style="width:34%;" /> SSN: <input type="text" class="input_5" name="bssn" style="width:25%;"  value="<?php echo $fd['ssn']; ?>" /></td>
                    </tr>
                </table>
                <div class="clr mb_15"></div>
                <table width="100%" style="margin:0px 5px;" class="style_3">
                    <tr>
                        <td width="5%" valign="top">
                            <table width="100%">
                                <tr>
                                    <td width="1%" valign="top"><strong>5. </strong></td>
                                    <td valign="top">
                                        <strong>How many children, including unborn children, are in your family and receive more than half of their support from you?</strong><input type="text" class="input_2" name="address" value="<?php echo $ansintkR['19']['intake_comment_body']; ?>" style="width: auto;"/>
                                    </td>
                                </tr>
                            </table>
                        </td>

                        <td width="5%" valign="top">
                            <table width="100%">
                                <tr>
                                    <td width="1%" valign="top"><strong>6. </strong></td>
                                    <td valign="top">
                                        <strong>How many other people, excluding your spouse and children, live with you and receive more than half of their support from you?</strong><input type="text" class="input_2" name="address" value="<?php echo $ansintkR['20']['intake_comment_body']; ?>" style="width: auto;"/>
                                    </td>
                                </tr>
                            </table>
                        </td>

                    </tr>

                </table>

                <table width="100%" style="margin:5px;" class="style_3">
                    <tr>
                        <td style="width:100%; padding:5px; border:1px solid #333333;background: #f0f0f0;font-size: 9.5pt ">
                            <strong>Note:</strong> A definition of "family size" is provided in Section 9. Do not enter a value for you or your spouse. Those values are automatically included in your family size, if appropriate.
                        </td>
                    </tr>
                </table>
                <div class="clr mb_20"></div>

                <strong>SECTION 4A: MARITAL STATUS INFORMATION</strong>
                <div class="line_border_3"></div>
                <div class="clr"></div>
                <table width="100%" style="margin:0px 5px;" class="style_3">
                    <tr>
                        <td width="50%" valign="top">
                            <table width="100%">
                                <tr>
                                    <td valign="top"><strong>7. </strong></td>
                                    <td>
                                        <strong>What is your marital status?</strong>
                                        <table width="100%">
                                            <tr><td width="15" valign="top"><input type="checkbox" <?php if ($marital_status == ($a + 14)) {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>Single <strong>- Skip to Item 11.</strong></td></tr>
                                            <tr><td width="15" valign="top"><input type="checkbox" <?php if ($marital_status == ($a + 15)) {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>Married <strong>- Continue to Item 8.</strong></td></tr>
                                            <tr><td width="15" valign="top"><input type="checkbox" <?php if ($marital_status == ($a2 + 72)) {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>Married, but separated - You will be treated as single. <strong>Skip to Item 11.</strong></td></tr>
                                            <tr><td width="15" valign="top"><input type="checkbox" <?php if ($marital_status == ($a2 + 73)) {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>Married, but cannot reasonably access my spouse's income information - You will be treated as single. <strong>Skip to Item 11.</strong></td></tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="top"><strong>8. </strong></td>
                                    <td>
                                        <strong> Does your spouse have federal student loans?</strong>
                                        <table width="100%">
                                            <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[8] == "1") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>Yes <strong>- Continue to Item 9.</strong></td></tr>
                                            <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[8] == "2") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td><strong>No - Skip to Item 10.</strong></td></tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td width="50%" valign="top">
                            <table width="100%">
                                <tr>
                                    <td valign="top"><strong>9. </strong></td>
                                    <td>
                                        <strong>Provide the following information about your spouse and then continue to Item 10:</strong>
                                        <table width="100%" style="margin:10px 0px 3px 0;">
                                            <tr>
                                                <td width="10" valign="middle">a.</td>
                                                <td valign="middle">Spouse's SSN <input type="text" class="input_2" name="sname" value="<?php echo $user['spouse_ssn'] ?? '      '; ?>" style="width:auto;min-width:100px;" /></td>
                                            </tr>
                                            <tr>
                                                <td valign="middle">b.</td>
                                                <td valign="middle">Spouse's Name <input type="text" class="input_2" name="sname" value="<?php echo $user['spouse_name'] ?? '      '; ?>" style="width:auto;min-width:100px" /></td>
                                            </tr>
                                            <tr>
                                                <td valign="middle">c.</td>
                                                <td valign="middle">Spouse's Date of Birth <input type="text" class="input_2" name="sname" value="<?php echo $user['spouse_dob'] ?? '      '; ?>" style="width:auto;min-width:100px" /></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="top"><strong>10. </strong></td>
                                    <td>
                                        <strong>When you filed your last federal income tax return, did you file jointly with your spouse?</strong><div class="clr mb_5"></div>
                                        <table width="100%" border="0">
                                            <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[10] == "1") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>Yes <strong>- Continue to Item 13.</strong></td></tr>
                                            <tr><td valign="top"><input type="checkbox" <?php if ($radio[10] == "2") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>No <strong>- Continue to Item 11.</strong></td></tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <div class="clr mb_15"></div>

                <strong>SECTION 4B: INCOME INFORMATION FOR SINGLE BORROWERS AND MARRIED BORROWERS FILING SEPARATELY</strong>
                <div class="line_border_3"></div>
                <div class="clr"></div>

                <table width="100%" style="margin:5px;" class="style_3">
                    <tr>
                        <td width="50%" valign="top">
                            <table width="100%">
                                <tr>
                                    <td valign="top"><strong>11. </strong></td>
                                    <td>
                                        <strong>Has your income significantly decreased, or your marital status changed since you filed your last federal income tax return?</strong><br />
                                        <p>For example, have you lost your job, experienced a drop in income, or gotten divorced, or did you most recently file a joint return with your spouse, but you have since become separated.</p>

                                        <table width="100%">
                                            <tr><td width="15" valign="top"><input type="checkbox" <?php if ($marital_status == ($a + 15) && $radio[10] == '1') {echo 'disabled';} else {if ($radio[11] == "1") {echo ' checked="checked"';} else {echo 'disabled';}}?> class="mt_5_" /></td> <td>Yes <strong>- Continue to Item 12.</strong></td></tr>
                                            <tr><td width="15" valign="top"><input type="checkbox" <?php if ($marital_status == ($a + 15) && $radio[10] == '1') {echo 'disabled';} else {if ($radio[11] == "2") {echo ' checked="checked"';} else {echo 'disabled';}}?> class="mt_5_" /></td> <td>No - Provide your most recent federal income tax return or transcript. <strong>Skip to Section 6.</strong></td></tr>
                                            <tr><td width="15" valign="top"><input type="checkbox" <?php if ($marital_status == ($a + 15) && $radio[10] == '1') {echo 'disabled';} else {if ($radio[11] == "3") {echo ' checked="checked"';} else {echo 'disabled';}}?> class="mt_5_" /></td> <td>I haven't filed a federal income tax return in the last two years <strong>- Continue to Item 12.</strong></td></tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>

                        <td width="50%" valign="top">
                            <table width="100%">
                                <tr>
                                    <td valign="top"><strong>12. </strong></td>
                                    <td>
                                        <strong>Do you currently have taxable income?</strong><br />
                                        <p>Check "No" if you do not have any income or receive only untaxed income.</p>
                                        <table width="100%" style="margin:5px;" class="style_3">
                                            <tr><td width="15" valign="top"><input type="checkbox" <?php if ($marital_status == ($a + 15) && $radio[10] == '1') {echo 'disabled';} else {if ($radio[12] == "1") {echo ' checked="checked"';} else {echo 'disabled';}}?> class="mt_5_" /></td> <td>Yes - Provide documentation of your income as instructed in Section 5. <strong>Skip to that section</strong></td></tr>
                                            <tr><td width="15" valign="top"><input type="checkbox" <?php if ($marital_status == ($a + 15) && $radio[10] == '1') {echo 'disabled';} else {if ($radio[12] == "2") {echo ' checked="checked"';} else {echo 'disabled';}}?> class="mt_5_" /></td> <td>No - You are not required to provide documentation of your income. <strong>Skip to Section 6.</strong></td></tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <table style="margin-top: 10px;">
                                <tr>
                                    <td style="background:#f0f0f0; width:100%; padding:5px; border:1px solid #999999;">
                                        <strong>Note:</strong> Any person who knowingly makes a false statement or misrepresentation on this form can be subject to penalties including fines, imprisonment, or both.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="pagebreak"></div>


    <table class="page_wrapper mrgn_idr" cellpadding="0" cellspacing="0">
        <tr>
            <td class="page_wrapper_inner_2 style_2" style="padding:0px 8px;">
                <strong>SECTION 4C: : INCOME INFORMATION FOR MARRIED FILING JOINTLY</strong>
                <div class="line_border_3"></div>
                <div class="clr"></div>

                <table width="100%" border="0">
                    <tr style="font-size: 10pt;margin-top: 10px;">
                        <td width="80"><strong>Borrower Identifiers:</strong> Borrower Name: <input type="text" class="input_5" name="bn" value="<?php echo $fd['name']; ?>" style="width:34%;" /> SSN: <input type="text" class="input_5" name="bssn" style="width:25%;"  value="<?php echo $fd['ssn']; ?>" /></td>
                    </tr>
                </table>
                <div class="clr mb_15"></div>

                <table width="100%" style="margin:5px;" class="style_3">
                    <tr>
                        <td width="50%" valign="top">
                            <table width="100%">
                                <tr>
                                    <td valign="top"><strong>13. </strong></td>
                                    <td>
                                        <strong>Has your income significantly decreased since you filed your last federal income tax return?</strong><br />
                                        <p>For example, have you lost your job or experienced a drop in income?</p>
                                        <table width="100%">
                                            <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[13] == "1") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>Yes <strong>- Skip to Item 15.</strong></td></tr>
                                            <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[13] == "2") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>No <strong>- Continue to Item 14.</strong></td></tr>
                                            <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[13] == "3") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>We haven't filed a federal income tax return in the last two years <strong>- Skip to Item 15.</strong></td></tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="top"><strong>14. </strong></td>
                                    <td>
                                        <strong>Has your spouse's income significantly decreased since your spouse filed their last federal income tax return?</strong><br />
                                        <p>For example, has your spouse lost their job or experienced a drop in income?</p>
                                        <table width="100%">
                                            <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[14] == "1") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>Yes <strong>- Continue to Item 15.</strong></td></tr>
                                            <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[14] == "2") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>No - Provide your and your spouse's most recent federal income tax return or transcript.<strong> Skip to Section 6</strong></td></tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>

                        <td width="50%" valign="top">
                            <table width="100%">
                                <tr>
                                    <td valign="top"><strong>15. </strong></td>
                                    <td>
                                        <strong> Do you currently have taxable income?</strong><br />
                                        <p>Check "No" if you do not have any income or receive only untaxed income.</p>
                                        <table width="100%">
                                            <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[15] == "1") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>Yes - You must provide documentation of your income according to the instructions in Section 5. <strong>Continue to Item 16.</strong></td></tr>
                                            <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[15] == "2") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>No - You are not required to provide documentation of your income. <strong>Continue to Item 16.</strong></td></tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="top"><strong>16. </strong></td>
                                    <td>
                                        <strong> Does your spouse currently have taxable income?</strong><br />
                                        <p>Check "No" if your spouse does not have any income or receives only untaxed income.</p>
                                        <table width="100%">
                                            <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[16] == "1") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>Yes <strong>- Skip to Section 5</strong> and provide documentation of your spouse's income as instructed in that section.</td></tr>
                                            <tr><td width="15" valign="top"><input type="checkbox" <?php if ($radio[16] == "2") {echo ' checked="checked"';}?> class="mt_5_" /></td> <td>No - You are not required to provide documentation of your spouse's income. If you selected "Yes" to Item 15, <strong>skip to Section 5</strong> and document your income. If you selected "No" to Item 15, <strong>skip to Section 6.</strong></td></tr>
                                        </table>
                                    </td>
                                </tr>

                            </table>
                        </td>
                    </tr>
                </table>

                <div class="clr mb_10"></div>

                <table width="100%" style="margin:5px;" class="style_3">
                    <tr>
                        <td style="width:100%; padding:5px; border:1px solid #333333;background: #f0f0f0;font-size: 9.5pt ">
                            <strong>Note:</strong> Any person who knowingly makes a false statement or misrepresentation on this form can be subject to penalties including fines, imprisonment, or both.
                        </td>
                    </tr>
                </table>
                <div class="clr mb_20"></div>

                <strong>SECTION 5A: AUTHORIZATION TO RETRIEVE FEDERAL TAX INFORMATION FROM THE IRS</strong>
                <div class="line_border_3"></div>
                <div class="clr mb_3"></div>

                <table width="100%" style="margin:5px;" class="style_3">
                    <tr>
                        <td style="width:100%; padding:5px; border:1px solid #333333;background: #f0f0f0;line-height: 1.5;">
                            This section is intended for borrowers holding Direct Loans only. If you have Federal Family Education Loan (FFEL) Program loans with a remaining balance, you must skip to section 5B.
                            <p>By accepting below, you will be: (1) consenting to the U.S. Department of Education disclosing certain information about you to the U.S. Department of the Treasury, Internal Revenue Service (IRS); (2) affirmatively approving the U.S. Department of Education obtaining your Federal Tax Information (FTI) from the IRS for certain purposes on an annual basis, as described below; and (3) agreeing that your approval will be ongoing until you fulfill your repayment obligations under an income-driven repayment (IDR) plan, withdraw from your IDR plan, or, as described below, revoke your approval and consent, as further described below. You are not required to provide your consent, approval, or agreement as a condition of eligibility for an IDR plan but, if approval and consent are not provided, you are required to provide alternative documentation of income (as described in Section 5B).</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <div class="pagebreak"></div>


    <!--    18C IDR 2021 CODED (PAGE 4) -->
    <table class="page_wrapper mrgn_idr" cellpadding="0" cellspacing="0">
        <tr>
            <td class="page_wrapper_inner_2 style_2" style="padding:0px 8px;">

                <strong>SECTION 5A: AUTHORIZATION TO RETRIEVE FEDERAL TAX INFORMATION FROM THE IRS (CONTINUED)</strong>
                <div class="line_border_3"></div>
                <div class="clr mb_3"></div>

                <table width="100%" border="0">
                    <tr style="font-size: 10pt;margin-top: 10px;">
                        <td width="80"><strong>Borrower Identifiers:</strong> Borrower Name: <input type="text" class="input_5" name="bn" value="<?php echo $fd['name']; ?>" style="width:34%;" /> SSN: <input type="text" class="input_5" name="bssn" style="width:25%;"  value="<?php echo $fd['ssn']; ?>" /></td>
                    </tr>
                </table>
                <div class="clr mb_15"></div>

                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="font-size:13px;">
                            <strong>By checking ‘I approve, consent, and agree’ below, I consent to, affirmatively approve of, and agree to, as applicable, the following:</strong><br />

                            <div class="clr mb_5"></div>

                            <ol style="margin:0px; padding:5px 0px 10px 20px;">
                                <li>The U.S. Department of Education may disclose my Social Security number (SSN)/Taxpayer IdentificationNumber (TIN), last name, and date of birth that I provided in Section 1 (Borrower Information) of this form, as well as my unique identifier and the tax year for which FTI is required, to the IRS for the U.S. Department of Education to receive my FTI for the purpose of, and to the extent necessary in, determining my eligibility for, or repayment obligations under, IDR plans as authorized under part D of title IV of the Higher Education Act of 1965, as amended, as described in 26 U.S.C. § 6103(l)(13)(A)</li>
                                <li>The U.S. Department of Education may use my FTI on an annual basis for the purposes of determining my eligibility for, and repayment obligations under, a qualifying IDR plan until I fulfill my repayment obligations under an IDR plan, withdraw from my IDR plan, or, as described below, revoke my approval and consent; and</li>
                                <li>The U.S. Department of Education may automatically execute the recertification of eligibility determination and repayment obligations for a qualifying IDR plan on an annual basis until I fulfill my repayment obligations under an IDR plan, withdraw from my IDR plan, or, as described below, revoke my approval and consent</li>
                            </ol>
                            <div class="clr mb_15"></div>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size:13px;">
                            <strong>By checking ‘I approve, consent, and agree’ below, I further understand that:</strong><br />

                            <div class="clr mb_5"></div>

                            <ol style="margin:0px; padding:5px 0px 10px 20px;">
                                <li>During recertification, my eligibility and monthly payment amount for a previously approved IDR plan may change based on the FTI that the U.S. Department of Education receives from the IRS when my IDR plan is automatically recertified on annual basis;</li>
                                <li>I am also providing my written consent for the redisclosure of my FTI by the U.S. Department of Education to the Office of Inspector General of the U.S. Department of Education for audit purposes, as described in 26 U.S.C. § 6103(l)(13)(D)(iv); and</li>
                                <li>I may revoke my consent for the disclosure of the SSN/TIN, last name, and date of birth information that I provided in Section 1 (Borrower Information) of this form, as well as my unique identifier and the tax year for which FTI is required, and my affirmative approval for the receipt and use of my FTI by the U.S. Department of Education within the user settings of my account at StudentAid.gov. (You must be logged into your account with your FSA ID in order to revoke approval and consent.) However, by revoking my affirmative approval and consent, I understand and acknowledge that the U.S. Department of Education will be unable to automatically determine my eligibility for, and repayment obligations under, an IDR plan on an annual basis, and will require that I, and my spouse (if applicable), provide alternative documentation of income on an annual basis if I wish to continue participating in an IDR plan.</li>
                            </ol>
                            <div class="clr mb_15"></div>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size:13px;">
                            <table width="100%" style="margin:5px;" class="style_3">
                                <tr>
                                    <td width="15" valign="top">
                                        <input type="checkbox" <?php if (isset($idr_formdata['consent']) && $idr_formdata['consent'] == "5a") {echo ' checked="checked"';}?> class="mt_5_" />
                                    </td>
                                    <td>
                                        I <strong>APPROVE, CONSENT</strong>, and <strong>AGREE</strong> and certify under penalty of perjury under the laws of the United States of America, that the foregoing is true and correct, and that I am the person named in Section 1 (Borrower Information) of this form providing consent to disclose and authorize the disclosure of my records, as set forth above. I further authorize the disclosure of my personally identifiable information, as outlined above, to the IRS for ED to receive my FTI for purposes of determining my eligibility for, or repayment obligations under, an IDR plan request. I understand that any falsification of this statement is punishable under the provisions of 18 U.S.C. § 1001 by a fine, imprisonment of not more than five years, or both, and that the knowing and willful request for or acquisition of a record pertaining to an individual under false pretenses is a criminal offense under the Privacy Act of 1974, as amended, subject to a fine of not more than $5,000 (5 U.S.C. § 552a(i)(3)). <strong>(Skip to Section 6)</strong>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size:13px;">
                            <table width="100%" style="margin:5px;" class="style_3">
                                <tr>
                                    <td width="15" valign="top">
                                        <input type="checkbox" <?php if (isset($idr_formdata['consent']) && $idr_formdata['consent'] == "5b") {echo ' checked="checked"';}?> class="mt_5_" />
                                    </td>
                                    <td>
                                        I <strong> DO NOT</strong> approve, consent, and agree to the disclosure of my information to the IRS for the U.S. Department of Education to receive my FTI, as described above. <strong>(Continue to Section 5B).</strong>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <div class="clr mb_5"></div>
            </td>
        </tr>
    </table>
    <div class="pagebreak"></div>

    <!--    18C IDR 2021 CODED (PAGE 4) -->
    <table class="page_wrapper mrgn_idr" cellpadding="0" cellspacing="0">
        <tr>
            <td class="page_wrapper_inner_2 style_2" style="padding:0px 8px;">

                <strong>SECTION 5B: INSTRUCTIONS FOR DOCUMENTING CURRENT INCOME</strong>
                <div class="line_border_3"></div>
                <div class="clr mb_3"></div>

                <table width="100%" border="0">
                    <tr style="font-size: 10pt;margin-top: 10px;">
                        <td width="80"><strong>Borrower Identifiers:</strong> Borrower Name: <input type="text" class="input_5" name="bn" value="<?php echo $fd['name']; ?>" style="width:34%;" /> SSN: <input type="text" class="input_5" name="bssn" style="width:25%;"  value="<?php echo $fd['ssn']; ?>" /></td>
                    </tr>
                </table>
                <div class="clr mb_15"></div>
                <table width="100%" border="0">
                    <tr style="font-size: 10pt;margin-top: 10px;">
                        <td>
                            If you were directed here from Section 5A, provide your most recent federal tax return or tax transcript, and <strong>skip to Section 6</strong>
                            <p>If you were directed here based on your answers in Section 4, you and your spouse (if applicable) must provide documentation of your current income instead of a federal tax return or tax transcript.</p>
                        </td>
                    </tr>
                </table>
                <div class="clr mb_15"></div>

                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="font-size:13px;">
                            <strong>This is the income you must document:</strong><br />

                            <div class="clr mb_5"></div>

                            <ul style="margin:0px; padding:5px 0px 10px 20px;">
                                <li>You must provide documentation of all taxable income you and your spouse (if applicable) currently receive</li>
                                <li>Taxable income includes, for example, income from employment, unemployment income, dividend income, interest income, tips, and alimony.</li>
                                <li>Do not provide documentation of untaxed income such as Supplemental Security Income, child support, or federal or state public assistance</li>
                            </ul>
                            <div class="clr mb_15"></div>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size:13px;">
                            <strong>This is how you document your income:</strong><br />

                            <div class="clr mb_5"></div>

                            <ul style="margin:0px; padding:5px 0px 10px 20px;">
                                <li>The date on any supporting documentation you provide must be no older than 90 days from the date you sign this form.</li>
                                <li>Documentation will usually include a pay stub or letter from your employer listing your gross pay</li>
                                <li>Write on your documentation how often you receive the income, for example, “twice per month” or “every other week."</li>
                                <li>You must provide at least one piece of documentation for each source of taxable income</li>
                                <li>If documentation is not available or you want to explain your income, attach a signed statement explaining each source of income and giving the name and the address of each source of income.</li>
                                <li>Copies of documentation are acceptable</li>
                            </ul>
                            <div class="clr mb_15"></div>
                            <strong>After gathering the appropriate documentation, continue to Section 6.</strong><br />
                        </td>
                    </tr>
                </table>
                <div class="clr mb_15"></div>

                <strong>SECTION 6: BORROWER REQUESTS, UNDERSTANDINGS, AUTHORIZATION AND CERTIFICATION</strong>
                <div class="line_border_3"></div>
                <div class="clr mb_3"></div>
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="font-size:13px;">
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
                        </td>
                    </tr>
                </table>
                <div class="clr mb_15"></div>
            </td>
        </tr>
    </table>
    <div class="pagebreak"></div>

    <!--    18C IDR 2021 CODED (PAGE 4) -->
    <table class="page_wrapper mrgn_idr" cellpadding="0" cellspacing="0">
        <tr>
            <td class="page_wrapper_inner_2 style_2" style="padding:0px 8px;">


                <strong>SECTION 6: BORROWER REQUESTS, UNDERSTANDINGS, AUTHORIZATION AND CERTIFICATION (CONTINUED)</strong>
                <div class="line_border_3"></div>
                <div class="clr mb_3"></div>

                <table width="100%" border="0">
                    <tr style="font-size: 10pt;margin-top: 10px;">
                        <td width="80"><strong>Borrower Identifiers:</strong> Borrower Name: <input type="text" class="input_5" name="bn" value="<?php echo $fd['name']; ?>" style="width:34%;" /> SSN: <input type="text" class="input_5" name="bssn" style="width:25%;"  value="<?php echo $fd['ssn']; ?>" /></td>
                    </tr>
                </table>
                <div class="clr mb_15"></div>
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="font-size:13px;">
                            If I am currently repaying my Direct Loans under the IBR plan and I am requesting a change to a different income-driven plan, I request a one-month reduced-payment forbearance in the amount of my current monthly IBR payment or $5, whichever is greater (unless I request another amount below or I decline the forbearance), to help me move from IBR to the new income-driven plan I requested.<br />

                            <div class="clr mb_5"></div>
                            <table width="100%">
                                <tr>
                                    <td valign="top" width="15">
                                        <input type="checkbox" <?php if (isset($idr_formdata['requested_reduced_payment_forbearance'])) {if ($idr_formdata['requested_reduced_payment_forbearance'] == "1") {echo ' checked="checked"';}}?> style="margin-top:-3px;" />
                                    </td>
                                    <td>
                                        <strong>I request</strong> a one-month reduced-payment forbearance in the amount of: &nbsp; &nbsp; &nbsp; <input type="text" class="input_2" value="$<?=$idr_formdata['reduced_payment_forbearance'] ?? ''?>" style="width:auto;" /> (must be at least $5).
                                    </td>
                                </tr>
                            </table>
                            <div class="clr mb_10"></div>

                            <strong>I understand</strong> that:
                            <div class="clr mb_5"></div>

                            <ul style="margin:0px; padding:5px 0px 10px 20px;">
                                <li>If I do not provide my loan holder with this completed form and any other required documentation, I will not be placed on the plan that I requested or my request for recertification or recalculation will not be processed.</li>
                                <li>I may choose a different repayment plan for any loans that are not eligible for income-driven repayment</li>
                                <li>If I requested a reduced-payment forbearance of less than $5 above, my loan holder will grant my forbearance for $5.</li>
                                <li>If I am requesting a change from the IBR Plan to a different income-driven repayment plan, I may decline the onemonth reduced payment forbearance described above by contacting my loan holder. If I decline the forbearance, I will be placed on the Standard Repayment Plan and cannot change repayment plans until I make one monthly payment under that plan.</li>
                                <li>If I am requesting the ICR plan, my initial payment amount will be the amount of interest that accrues each month on my loan until my loan holder receives the income documentation needed to calculate my payment amount. If I cannot afford the initial payment amount, I may request a forbearance by contacting my loan holder.</li>
                                <li>If I am married and I request the ICR plan, my spouse and I have the option of repaying our Direct Loans jointly under this plan. My loan servicer can provide me with information about this option.</li>
                                <li>If I have FFEL Program loans, my spouse may be required to give my loan holder access to their information in the National Student Loan Data System (NSLDS). If this applies to me, my loan holder will contact me with instructions.</li>
                                <li>My loan holder may grant me a forbearance while processing my application or to cover any period of delinquency that exists when I submit my application.</li>
                            </ul>
                            <div class="clr mb_15"></div>
                            <strong>I authorize</strong> the entity to which I submit this request and its agents to contact me regarding my request or my loans at any cellular telephone number that I provide now or in the future using automated telephone dialing equipment or artificial or prerecorded voice or text messages.
                            <div class="clr mb_15"></div>
                            <strong>If I approve</strong> e (checked the box in Section 5A) to authorize retrieval of FTI from the IRS, I further <strong>consent</strong> to the disclosure by the U.S. Department to the IRS of my personally identifiable information, as described in Section 5A, and <strong>agree</strong> to the conditions to permit the disclosure of my FTI for purposes of this IDR plan request.
                            <div class="clr mb_15"></div>
                            <strong>I certify</strong> under penalty of perjury under the laws of the United States of America, that all information I have provided on this form and in any accompanying documentation is true, complete, and correct to the best of my knowledge and belief. I further certify that I will repay my loans according to the terms of my promissory note and repayment schedule.
                            <div class="clr mb_10"></div>

                            <table width="100%" border="0">
                                <tr>
                                    <td style="width:20%;"><strong>Borrower's Signature</strong></td>
                                    <td style="width:50%;"><div class="input_2">&nbsp;</div></td>
                                    <td style="width:5%;"><strong>Date</strong></td>
                                    <td style="width:25%;"><input type="text" class="input_2" value="" /></td>
                                </tr>

                            </table>
                        </td>
                    </tr>
                </table>
                <div class="clr mb_15"></div>
            </td>
        </tr>
    </table>
    <div class="pagebreak"></div>

    <!--    18C IDR 2021 CODED (PAGE 5) -->
    <table class="page_wrapper mrgn_idr" cellpadding="0" cellspacing="0">
        <tr>
            <td class="page_wrapper_inner_2 style_2" style="padding:0px 8px;">

                <strong>SECTION 7: WHERE TO SEND THE COMPLETED FORM</strong>
                <div class="line_border_3"></div>
                <div class="clr"></div>

                <table width="100%"  style="font-size:13px;">
                    <tr>
                        <td width="50%" style="vertical-align:top; padding:2px 15px 0px 0px; border-right:2px solid #000;">
                            Return the completed form and any documentation to: (If no address is shown, return to your loan holder.)
                            <div class="clr mb_5"></div>
                            <textarea class="input_3" style="height:80px;"><?php echo $idr_page_5['textarea'][1]; ?></textarea>
                        </td>
                        <td width="50%" valign="top" style="padding:2px 0 0 15px;">
                            If you need help completing this form call: (If no phone number is shown, call your loan holder.)
                            <div class="clr mb_5"></div>
                            <textarea class="input_3" style="height:80px;"><?php echo $idr_page_5['textarea'][2]; ?></textarea>
                        </td>
                    </tr>
                </table>
                <div class="clr mb_5"></div>

                <strong>SECTION 8: INSTRUCTIONS FOR COMPLETING THE FORM</strong>
                <div class="line_border_3"></div>
                <div class="clr mb_5"></div>

                <table width="100%" style="font-size:13px;">
                    <tr>
                        <td>
                            Type or print using dark ink. Enter dates as month-day-year (mm-dd-yyyy). Example: March 14, 2023 = 03-14-2023. Include your name and account number on any documentation that you are required to submit with this form. Return the completed form and any required documentation to the address shown in Section 7.
                        </td>
                    </tr>
                </table>

                <div class="clr mb_10"></div>

                <strong>SECTION 9: DEFINITIONS</strong>
                <div class="line_border_3"></div>
                <div class="clr"></div>

                <table width="100%" style="font-size:13px;">
                    <tr>
                        <td valign="top">
                            <div class="clr mb_10"></div>
                            <strong style="font-size:15px;"><i>Common Definitions For All Plans:</i></strong>
                            <div class="clr mb_15"></div>

                            <strong>Capitalization</strong> is the addition of unpaid interest to the principal balance of your loan. This will increase the principal balance and the total cost of your loan.
                            <div class="clr mb_10"></div>

                            A <strong>deferment</strong> is a period during which you are entitled to postpone repayment of your loans. Interest is not generally charged to you during a deferment on your subsidized loans. Interest is always charged to you during a deferment on your unsubsidized loans.
                            <div class="clr mb_10"></div>

                            The <strong>William D. Ford Federal Direct Loan (Direct Loan) Program</strong> includes Direct Subsidized Loans, Direct Unsubsidized Loans, Direct PLUS Loans, and Direct Consolidation Loans.
                            <div class="clr mb_10"></div>

                            <strong>Family size</strong> always includes you and your children (including unborn children who will be born during the year for which you certify your family size), if the children will receive more than half their support from you
                            <div class="clr mb_10"></div>

                            For Direct Loan program borrowers who choose to provide consent: for any plan, family size includes your spouse only if you filed your federal tax return as married filing jointly unless you have indicated that you are separated from your spouse.
                            <div class="clr mb_10"></div>

                            For all FFEL program loan borrowers and Direct Loan program borrowers who choose not to provide consent or choose to provide alternative income documentation: for the PAYE, IBR, and ICR Plans, family size always includes your spouse. For the SAVE (formerly REPAYE) plan, family size includes your spouse unless your spouse's income is excluded from the calculation of your payment amount.
                            <div class="clr mb_10"></div>

                            For all plans, family size also includes other people only if they live with you now, receive more than half their support from you now, and will continue to receive this support for the year that you certify your family size. Support includes money, gifts, loans, housing, food, clothes, car, medical and dental care, and payment of college costs. Your family size may be different from the number of exemptions you claim for tax purposes.
                            <div class="clr mb_10"></div>

                            The <strong>Federal Family Education Loan (FFEL) Program</strong> includes Federal Stafford Loans (both subsidized and unsubsidized), Federal PLUS Loans, Federal Consolidation Loans, and Federal Supplemental Loans for Students (SLS).
                            <div class="clr mb_10"></div>

                            A <strong>forbearance</strong> is a period during which you are permitted to postpone making payments temporarily, allowed an extension of time for making payments, or temporarily allowed to make smaller payments than scheduled.
                            <div class="clr mb_10"></div>

                            The <strong>holder</strong> of your Direct Loans is the U.S. Department of Education (the Department). The holder of your FFEL Program loans may be a lender, secondary market, guaranty agency, or the Department. Your loan holder may use a servicer to handle billing, payment, repayment options, and other communications. References to “your loan holder” on this form mean either your loan holder or your servicer.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <div class="pagebreak"></div>

    <!--    18C IDR 2021 CODED (PAGE 5) -->
    <table class="page_wrapper mrgn_idr" cellpadding="0" cellspacing="0">
        <tr>
            <td class="page_wrapper_inner_2 style_2" style="padding:0px 8px;">

                <strong>SECTION 9: DEFINITIONS (CONTINUED)</strong>
                <div class="line_border_3"></div>
                <div class="clr mb_15"></div>


                <table width="100%" style="font-size:13px;">
                    <tr>
                        <td valign="top">
                            A <strong>partial financial hardship</strong> is an eligibility requirement for the PAYE and IBR plans. You have a partial financial hardship when the annual amount due on all of your eligible loans (and, if you are required to provide documentation of your spouse's income, the annual amount due on your spouse's eligible loans) exceeds what you would pay under PAYE or IBR.
                            <div class="clr mb_10"></div>

                            The annual amount due is calculated based on the greater of (1) the total amount owed on eligible loans at the time those loans initially entered repayment, or (2) the total amount owed on eligible loans at the time you initially request the PAYE or IBR plan. The annual amount due is calculated using a standard repayment plan with a 10-year repayment period, regardless of loan type. When determining whether you have a partial financial hardship for the PAYE plan, the Department will include any FFEL Program loans that you have into account even though those loans are not eligible to be repaid under the PAYE plan, except for: (1) a FFEL Program loan that is in default, (2) a Federal PLUS Loan made to a parent borrower, or (3) a Federal Consolidation Loan that repaid a Federal or Direct PLUS Loan made to a parent borrower.
                            <div class="clr mb_10"></div>

                            The <strong>poverty guideline amount</strong> is the figure for your state and family size from the poverty guidelines published annually by the U.S. Department of Health and Human Services (HHS) at aspe.hhs.gov/poverty- guidelines. If you are not a resident of a state identified in the poverty guidelines, your poverty guideline amount is the amount used for the 48 contiguous states.
                            <div class="clr mb_10"></div>

                            The <strong>standard repayment plan</strong> has a fixed monthly payment amount over a repayment period of up to 10 years for loans other than Direct or Federal Consolidation Loans, or up to 30 years for Direct and Federal Consolidation Loans.
                            <div class="clr mb_10"></div>

                            <strong style="font-size:15px;"><i>Definitions For The SAVE (formerly known as the REPAYE) PLAN:</i></strong>
                            <div class="clr mb_15"></div>

                            The <strong>Saving on a Valuable Education (SAVE) (formerly known as the Revised Pay As You Earn (REPAYE)) plan</strong> is a repayment plan with monthly payments that are generally equal to 10% of your discretionary income, divided by 12
                            <div class="clr mb_10"></div>

                            <strong>Discretionary income for the SAVE plan</strong> is the amount by which your income exceeds 225% of the poverty guideline amount.
                            <div class="clr mb_10"></div>

                            <strong>Eligible loans for the SAVE plan</strong> are Direct Loan Program loans other than: (1) a loan that is in default, (2) a Direct PLUS Loan made to a parent borrower, or (3) a Direct Consolidation Loan that repaid a Direct or Federal PLUS Loan made to a parent borrower.
                            <div class="clr mb_10"></div>

                            <strong style="font-size:15px;"><i>Definitions For The PAYE PLAN:</i></strong>
                            <div class="clr mb_15"></div>

                            The Pay As You Earn (PAYE) plan is a repayment plan with monthly payments that are generally equal to 10% of your discretionary income, divided by 12.
                            <div class="clr mb_10"></div>

                            Discretionary income for the PAYE plan is the amount by which your income exceeds 150% of the poverty guideline amount.
                            <div class="clr mb_10"></div>

                            Eligible loans for the PAYE plan are Direct Loan Program loans other than: (1) a loan that is in default, (2) a Direct PLUS Loan made to a parent borrower, or (3) a Direct Consolidation Loan that repaid a Direct or Federal PLUS Loan made to a parent borrower.
                            <div class="clr mb_10"></div>

                            You are a new borrower for the PAYE plan if: (1) you have no outstanding balance on a Direct Loan or FFEL Program loan as of October 1, 2007 or have no outstanding balance on a Direct Loan or FFEL Program loan when you obtain a new loan on or after October 1, 2007, and (2) you receive a disbursement of an eligible loan on or after October 1, 2011, or you receive a Direct Consolidation Loan based on an application received on or after October 1, 2011.
                            <div class="clr mb_10"></div>

                            <strong style="font-size:15px;"><i>Definitions For The IBR PLAN:</i></strong>
                            <div class="clr mb_15"></div>

                            The <strong>Income-Based Repayment (IBR) plan</strong> is a repayment plan with monthly payments that are generally equal to 15% (10% if you are a new borrower) of your discretionary income, divided by 12.
                            <div class="clr mb_10"></div>

                            <strong>Discretionary income for the IBR plan</strong> is the amount by which your adjusted gross income exceeds 150% of the poverty guideline amount.
                            <div class="clr mb_10"></div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <div class="pagebreak"></div>


    <!--    18C IDR 2021 CODED (PAGE 6) -->
    <table class="page_wrapper mrgn_idr" cellpadding="0" cellspacing="0">
        <tr>
            <td class="page_wrapper_inner_2 style_2" style="padding:0px 8px;">

                <strong>SECTION 9: DEFINITIONS (CONTINUED)</strong>
                <div class="line_border_3"></div>
                <div class="clr"></div>

                <table width="100%" style="font-size:13px">
                    <tr>
                        <td valign="top">

                            <strong>Eligible loans for the IBR plan</strong> are Direct Loan and FFEL Program loans other than: (1) a loan that is in default, (2) a Direct or Federal PLUS Loan made to a parent borrower, or (3) a Direct or Federal Consolidation Loan that repaid a Direct or Federal PLUS Loan made to a parent borrower.
                            <div class="clr mb_10"></div>

                            You are a <strong>new borrower for the IBR plan</strong> if (1) you have no outstanding balance on a Direct Loan or FFEL Program loan as of July 1, 2014 or (2) have no outstanding balance on a Direct Loan or FFEL Program loan when you obtain a new loan on or after July 1, 2014.
                            <div class="clr mb_15"></div>

                            <strong style="font-size:15px;"><i>Definitions For The ICR PLAN:</i></strong>
                            <div class="clr mb_15"></div>

                            The <strong>Income-Contingent Repayment (ICR) plan</strong> is a repayment plan with monthly payments that are the lesser of (1) what you would pay on a repayment plan with a fixed monthly payment over 12 years, adjusted based on your income or (2) 20% of your discretionary income divided by 12.
                            <div class="clr mb_10"></div>

                            <strong>Discretionary income for the ICR plan</strong> is the amount by which your adjusted gross income exceeds the poverty guideline amount for your state of residence and family size.
                            <div class="clr mb_10"></div>

                            <strong>Eligible loans for the ICR plan</strong> are Direct Loan Program loans other than: (1) a loan that is in default, (2) a Direct PLUS Loan made to a parent borrower, or (3) a Direct PLUS Consolidation Loan (based on an application received prior to July 1, 2006 that repaid Direct or Federal PLUS Loans made to a parent borrower). However, a Direct Consolidation Loan made based on an application received on or after July 1, 2006 that repaid a Direct or Federal PLUS Loan made to a parent borrower is eligible for the ICR plan.
                            <div class="clr mb_10"></div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

<?php
}
			?>
</body>
</html>
<?php

		}
	}
}

?>