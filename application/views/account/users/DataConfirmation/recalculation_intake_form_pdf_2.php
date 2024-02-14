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

	body {
	margin: 0px;
	padding:0px;
	color: #000000;
	font-family:Calibri;
	counter-reset: page <?=$total?>;
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
	.style_3 {color:#000000; font-size:9.5pt;}
	.em_ul_style { text-decoration:underline; font-style:normal;}
	.line_border_1 { width:100%; height:4px; margin:3px 0px; background:#000000;}
	.line_border_2 { width:100%; height:1px; margin:5px 0px; background:#000000;}
	.line_border_3 { width:100%; height:2px; margin:2px 0px 0px 0px; background:#000000;}

	.input_td { background:transparent; color:#777777; font-size:13px; padding:3px 5px; border-bottom:1px solid #888888; }
	.input_div_2 {width:auto; height:13px; font-size:12px; color:#000000; border-bottom:1px solid #000; padding:2px 5px;}
	.input_2 {width:100%; height:15px; padding:2px 2px 2px 2px; color:#444444; font-size:10pt; border:none; border-bottom:.2px solid #000;}
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

</style>
</head>
<body>
<?php
if ($intkR['intake_title'] == "IDR Intake" || $intkR['intake_title'] == "Consolidation Intake" || $intkR['intake_title'] == "Recertification Intake" || $intkR['intake_title'] == "Recalculation Intake" || $intkR['intake_title'] == "Switch IDR Intake") {
				$ssn = explode("-", $fd['ssn']);
				?>
	<div class="footer">
        <p style="position: relative;">Page <strong class="page"><?php echo $PAGE_NUM ?></strong> of <strong class="total_pages" style="position: absolute;left: 53.5%;">DOMPDF_PAGE_COUNT_PLACEHOLDER</strong></p>
    </div>
    <div>

		<!--    18C IDR 2021 CODED (PAGE 7) -->
	    <table class="page_wrapper mrgn_idr" cellpadding="0" cellspacing="0">
	        <tr>
	            <td class="page_wrapper_inner_2 style_2" style="padding:0px 8px;">
	            	<strong>SECTION 10: INCOME DRIVEN REPAYMENT PLAN REQUIREMENTS AND GENERAL INFORMATION</strong>
	                <div class="line_border_3"></div>
	                <div class="clr mb_15"></div>

	                <table width="100%" border="0">
	                    <tr style="font-size: 10pt;margin-top: 10px;">
	                        <td>See Table Below For Income Driven Repayment Plan Requirements and General Information.</td>
	                    </tr>
	                </table>
	                <div class="clr"></div>

					<table width="100%" cellspacing="0" bordercolor="#fff" border="1" class="style_3">

						<tr style="background:#000; color:#FFFFFF; font-weight:bold;">
						    <td style="padding:15px 0 15px 5px">Plan Feature</td>
						    <td style="padding:15px 0 15px 5px">SAVE (formerly REPAYE)</td>
						    <td style="padding:15px 0 15px 5px">PAYE</td>
						    <td style="padding:15px 0 15px 5px">IBR</td>
						    <td style="padding:15px 0 15px 5px">ICR</td>
						</tr>

						<tr valign="top" style="background:#EEEEEE;">
						    <td style="padding:10px"><strong>Payment Amount</strong></td>
						    <td style="padding:10px">Generally, 10% of discretionary income.</td>
						    <td style="padding:10px">Generally, 10% of discretionary income.</td>
						    <td style="padding:10px">Never more than 15% of discretionary income.</td>
						    <td style="padding:10px">Lesser of 20% of discretionary income or what you would pay under a repayment plan with fixed payments over 12 years, adjusted based on your income.</td>
						</tr>

						<tr valign="top">
						    <td style="padding:10px"><strong>Cap on Payment Amount</strong></td>
						    <td style="padding:10px">None. Your payment may exceed what you would have paid under the 10-year standard repayment plan.</td>
						    <td style="padding:10px">What you would have paid under the 10-year standard repayment plan when you entered the plan.</td>
						    <td style="padding:10px">What you would have paid under the 10-year standard repayment plan when you entered the plan.</td>
						    <td style="padding:10px">None. Your payment may exceed what you would have paid under the 10-year standard repayment plan.</td>
						</tr>


						<tr valign="top" style="background:#EEEEEE;">
						    <td style="padding:10px"><strong>Married Borrowers</strong></td>
						    <td style="padding:10px">Your payment will be based on the combined income and loan debt of you and your spouse only if you file a joint Federal income tax return, unless you and your spouse (1) are separated or (2) you are unable to reasonably access your spouse's income information.</td>
						    <td style="padding:10px">Your payment will be based on the combined income and loan debt of you and your spouse only if you file a joint Federal income tax return, unless you and your spouse (1) are separated or (2) you are unable to reasonably access your spouse's income information.</td>
						    <td style="padding:10px">Your payment will be based on the combined income and loan debt of you and your spouse only if you file a joint Federal income tax return, unless you and your spouse (1) are separated or (2) you are unable to reasonably access your spouse's income information.</td>
						    <td style="padding:10px">Your payment will be based on the combined income of you and your spouse only if you file a joint Federal income tax return, unless you and your spouse (1) are separated or (2) you are unable to reasonably access your spouse's income information.</td>
						</tr>


						<tr valign="top">
						    <td style="padding:10px"><strong>Borrower Responsibility for Interest</strong></td>
						    <td style="padding:10px">On all loans, you do not have to pay the difference between your monthly payment amount and the interest that accrues while you remain on the plan. </td>
						    <td style="padding:10px">On subsidized loans, you do not have to pay the difference between your monthly payment amount and the interest that accrues for your first 3 consecutive years in the plan.</td>
						    <td style="padding:10px">On subsidized loans, you do not have to pay the difference between your monthly payment amount and the interest that accrues for your first 3 consecutive years in the plan.</td>
						    <td style="padding:10px">You are responsible for paying all of the interest that accrues.</td>
						</tr>
					</table>

					<div class="clr"></div>

				</td>
			</tr>
		</table>
		<div class="pagebreak"></div>


		<!--    18C IDR 2021 CODED (PAGE 8) -->
		<table class="page_wrapper mrgn_idr" cellpadding="0" cellspacing="0" style="font-size:12px;">
			<tr>
	            <td class="page_wrapper_inner_2 style_2" style="padding:0px 8px;">
					<table width="100%" cellspacing="0" bordercolor="#fff" border="1" class="style_3">

						<tr style="background:#000; color:#FFFFFF; font-weight:bold;">
						    <td style="padding:12px 0 12px 5px">Plan Feature</td>
						    <td style="padding:12px 0 12px 5px">SAVE (formerly REPAYE)</td>
						    <td style="padding:12px 0 12px 5px">PAYE</td>
						    <td style="padding:12px 0 12px 5px">IBR</td>
						    <td style="padding:12px 0 12px 5px">ICR</td>
						</tr>
						<tr valign="top" style="background:#EEEEEE;">
						    <td style="padding:10px"><strong>Forgiveness Period</strong></td>
						    <td style="padding:10px">If you only have eligible loans that you received for undergraduate study, any remaining balance is forgiven after 20 years of qualifying repayment. If you have any eligible loans that you received for graduate or professional study, any remaining balance is forgiven after 25 years of qualifying repayment on all of your loans. Forgiveness may be taxable.</td>
						    <td style="padding:10px">Any remaining balance is forgiven after 20 years of qualifying repayment, and may be taxable.</td>
						    <td style="padding:10px">Any remaining balance is forgiven after no more than 25 years of qualifying repayment, and may be taxable.</td>
						    <td style="padding:10px">Any remaining balance is forgiven after 25 years of qualifying repayment, and may be taxable.</td>
						</tr>

						<tr valign="top">
						    <td style="padding:10px"><strong>Income Eligibility</strong></td>
						    <td style="padding:10px">None.</td>
						    <td style="padding:10px">You must have a "partial financial hardship".</td>
						    <td style="padding:10px">You must have a "partial financial hardship".</td>
						    <td style="padding:10px">None.</td>
						</tr>

						<tr valign="top" style="background:#EEEEEE;">
						    <td style="padding:10px"><strong>Borrower Eligibility</strong></td>
						    <td style="padding:10px">You must be a Direct Loan borrower with eligible loans.</td>
						    <td style="padding:10px">You must be a "new borrower" with eligible Direct Loans.</td>
						    <td style="padding:10px">You must be a Direct Loan or FFEL borrower with eligible loans.</td>
						    <td style="padding:10px">You must be a Direct Loan borrower with eligible loans.</td>
						</tr>

						<tr valign="top">
						    <td style="padding:10px"><strong>Recertify Income and Family Size</strong></td>
						    <td style="padding:10px">Annually. Failure to submit documentation by the deadline may result in an increase to your payment to ensure that your loan is paid in full over the lesser of 10 or the remainder of 20 or 25 years.</td>
						    <td style="padding:10px">Annually. Failure to submit documentation by the deadline may result in an increase of the payment amount to the 10-year standard payment amount.</td>
						    <td style="padding:10px">Annually. Failure to submit documentation by the deadline will result in the capitalization of interest and increase in payment amount to the 10-year standard payment amount.</td>
						    <td style="padding:10px">Annually. Failure to submit documentation by the deadline will result in the recalculation of your payment amount to be the 10-year standard payment amount.</td>
						</tr>

						<tr valign="top" style="background:#EEEEEE;">
						    <td style="padding:10px"><strong>Leaving the Plan</strong></td>
						    <td style="padding:10px">At any time, you may change to any other repayment plan for which you are eligible.</td>
						    <td style="padding:10px">At any time, you may change to any other repayment plan for which you are eligible.</td>
						    <td style="padding:10px">If you want to leave the plan, you will be placed on the standard repayment plan. You may not change plans until you have made one payment under that plan or a reduced-payment forbearance.</td>
						    <td style="padding:10px"> At any time, you may change to any other repayment plan for which you are eligible.</td>
						</tr>

						<tr valign="top">
						    <td style="padding:10px"><strong>Interest Capitalization</strong></td>
						    <td style="padding:10px">None</td>
						    <td style="padding:10px">None</td>
						    <td style="padding:10px">If you are determined to no longer have a “partial financial hardship”, fail to recertify your income by the deadline, or leave the plan, interest is capitalized.</td>
						    <td style="padding:10px">None</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</div>

<?php
}
			?>
</body></html>
<?php

		}
	}
}

?>