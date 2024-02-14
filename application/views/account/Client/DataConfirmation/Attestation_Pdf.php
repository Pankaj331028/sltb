<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php
$form_data = json_decode($car['form_data'], true);
if (is_array($form_data)) {@extract($form_data);}
if (!isset($household)) {
	$household = [
		'fullname' => [], 'age' => [], 'relation' => [],
	];}
if (!isset($car['multiple_loans'])) {
	$multiple_loans = [
		'loan_name' => [],
		'loan_type' => [],
		'loan_amount' => [],
		'monthly_payment' => [],
		'date_of_payoff' => [],
		'date_of_default' => [],
		'school_attended' => [],
		'degree_pursued' => [],
		'specialization' => [],
		'date_school_completed' => [],
		'type_of_degree' => [],
		'date_studies_ceased' => [],
	];} else { $multiple_loans = $car['multiple_loans'];}
if (!isset($other_incomes)) {
	$other_incomes = [
		'income_value' => [], 'income_detail' => [],
	];}
if (!isset($other_deductions)) {
	$other_deductions = [
		'deduction' => [], 'amount' => [],
	];} else {
	$other_deductions = [
		'deduction' => [], 'amount' => [],
	];}
if (!isset($vehicle_payments)) {
	$vehicle_payments = [
		'payment' => [], 'operating_costs' => [],
	];}
if (!isset($presume_repay_inability)) {$presume_repay_inability = [];}
if (!isset($repay_inability)) {$repay_inability = [];}
if (!isset($loan_payments)) {$loan_payments = [];}
if (!isset($additional_payments)) {$additional_payments = [];}
if (!isset($entities)) {
	$entities = [
		'name' => [], 'state' => [], 'type' => [],
	];}
if (!isset($parcels)) {
	$parcels = [
		'address' => [], 'name' => [], 'value' => [], 'balance' => [],
	];}
if (!isset($motor_vehicle_payments)) {
	$motor_vehicle_payments = [
		'make_model' => [], 'fair_market' => [], 'balance' => [],
	];}
if (!isset($correct_loan_info)) {$correct_loan_info = 'yes';}
if (!isset($student_loan_default)) {$student_loan_default = 'no';}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Data Confirmation for Attestation</title>
	<!-- <link rel="stylesheet" href="<?php echo base_url('assets/crm/bootstrap/css/bootstrap.min.css'); ?>"> -->
	<style>
			@font-face {
			font-family:times-new-roman;
			src: url(/assets/font/times-new-roman.ttf);
			}
			.container {
				max-width: 1200px;
				margin: auto;
			}
			body{
				font-family: times-new-roman;
				font-size: 15px;
/*				line-height: 24px;*/
			}
			table {
				width: 100%;
			}
			table thead {
				padding: 20px 0px;
				/*   display: block;*/
			}
			table tr,div {
				page-break-inside: auto;
				break-inside: auto;
				line-height: 18px;
			}
			table td {
/*				padding: 10px 0px;*/
				display: table-cell;
				font-family: times-new-roman;
				font-size: 15px;
				line-height: 24px;
				page-break-inside: auto;
				break-inside: auto;
			}
			.text-center {
				text-align: center;
			}
			.w-100 {
				width: 100%;
				float: left;
			}
			.w-50 {
				width: 50%;
				float: left;
			}
			.w-33 {
				width: 33.33%;
				float: left;
			}
			input {
				border: 0px;
				border-bottom: 1px solid #000;
				font-family: times-new-roman;
				font-size: 15px;
				line-height: 15px;
			}
			label{
				/*   display: grid;*/
				margin: 10px 0px;
				font-family: times-new-roman;
				font-size: 15px;
				line-height: 24px;
			}
			li {
				margin: 0px 0 30px;
				font-family: times-new-roman;
				font-size: 15px;
				line-height: 15px;
			}
			input.border-none {
				border: 0;
				line-height: 0;
			}
			p{
				font-family: times-new-roman;
				font-size: 15px;
				line-height: 24px;
			}

			h3 {
				font-weight: normal;
/*				text-decoration: underline;*/
				font-family: times-new-roman;
				font-size: 15px;
				line-height: 24px;
			}
			.underline{
				text-decoration: underline;
			}
			/*
			.bold{font-weight: bold;}
			.mt-10{margin-top: 30px}
			.mt-5{margin-top: 20px}
			.mt-3{margin-top: 10px}
			li li {
				list-style: lower-roman;
				padding: 15px 0px 0px;
			}*/

			.multiple_loans{
				width: 100%;
				max-width: 1100px;
			}

			.multiple_loans input{
				width: 100px;
			}

			span{
				font-weight: bold;
				min-width: 50px !important;
				border-bottom: 1px solid;
			}
			.expenses{
				width: 100%;
			}
			.expenses-in{
				width: 50%;
				display: inline-block;
			}
			.expenses-in-one{
				float: left;
			}

			.union-dues{
				margin-bottom: 70px;
			}
			.expenses-in-second{
				float: right;
			}
			.tou-may-offer{
				margin-bottom: 15px;
			}
			@page {
				margin: 1in;
				position: relative;
			}

			@media print {
				@page{
		  			size: portrait;
				}
				.landscape {
		  			page: rotated;
		  			transform-origin: top left;
		  			transform: translateX(100vw) rotate(90deg);
	      		}
				@page rotated {
		  			page-orientation: rotate-left;
		  			size: landscape;
	      		}
	      	}
	      	.landscape {
		  		page: rotated;
	  			transform-origin: top left;
	  			transform: translateX(100vw) rotate(90deg);
      		}
			@page rotated {
	  			page-orientation: rotate-left;
	  			size: landscape;
      		}

			#header {
				position: fixed; left: 0px; top: -50px; right: 0px;
			}

			.footer .page:after {
				content: counter(page);
			}
			.pagebreak {
				 display: block; height:0px; clear: both; page-break-after: always;
			}
			li,div,tr,td{
				clear: both;
			}
			.subheadings:before {
				content: attr(data-num);
				display: inline-block;
				margin: auto;
				text-align: center;
			}
			sup{
				font-size: 10px;
			}
			table.nostyle{
				border-collapse: collapse;
			}
			table.nostyle td, table.nostyle th {
				border: 1px solid #000;
				padding: 10px 1px;
			}
			.floatLeft {
				float: left;
			}

			.floatRight {
				float: right;
			}

			.floatClear {
				clear: both;
			}

			.w-50 {
				width: 50% !important;
			}
			em{
				font-style: inherit;
				line-height: 24px;
			}
			tr{
				page-break-after: auto;
			}
			table{
				page-break-inside: auto;
			}
			.footer {
			  	position: fixed;
			  	left: 0;
			    right: 0;
			    font-size: 0.9em;
			  	bottom: 0;
			  	background-color: white;
			  	margin: 0;
			  	padding: 0;
			}
			.supDiv{
				position: fixed;
			  	border-top: 0.1pt solid #aaa;
			  	padding-top: 5px;
				bottom: 125px;
/*				bottom: 150px;*/
				margin: 0;
				left: 0;
				right: 0;
				top: auto;
				font-size: 14px !important;
			}
			.supDiv2{
				position: fixed;
			  	border-top: 0.1pt solid #aaa;
			  	padding-top: 5px;
				bottom: 70px;
				margin: 0;
				left: 0;
				right: 0;
				top: auto;
				font-size: 14px !important;
			}
			.supDiv4{
				position: fixed;
			  	border-top: 0.1pt solid #aaa;
			  	padding-top: 5px;
				bottom: 80px;
				margin: 0;
				left: 0;
				right: 0;
				top: auto;
				font-size: 14px !important;
			}
			.supDiv3{
				page: sup3;
				position: fixed;
			  	border-top: 0.1pt solid #aaa;
			  	padding-top: 5px;
				bottom: 105px;
				margin: 0;
				left: 0;
				right: 0;
				top: auto;
				font-size: 14px !important;
			}
			#content{
				padding-bottom: 100px !important;
			}
			.overlapFootnote{
				bottom: 80px !important;
/*				bottom: 120px !important;*/
			}
			.noTopBorder{
				border-top: 0 !important;
			}

		</style>

</head>
<body style="margin:0">
  	<div id="header">
  		[Updated January 2023]
  	</div>

   	<div id="footer2" class="footer">
		<p class="text-center" style="margin:0;padding: 0;">- <span class="page"><?php echo $PAGE_NUM ?></span> -</p>
	</div>

	<div id="content">
		<table style="width:100%">
			<tbody>
				<tr class="text-center" style="width: 100%; text-align: center; margin: auto;">
					<td>IN THE UNITED STATES BANKRUPTCY COURT<br>
						FOR THE DISTRICT OF <span><?=$district?></span><br>
						<span><?=$local_district_information?></span>
					</td>
				</tr>
				<tr>
					<td>
						<div>
							<div class="floatLeft w-50">In re:</div>
							<div class="floatRight w-50">
								<em style="line-height:15px;width:20%;padding-right: 50px;">)</em>
								<em style="line-height:15px;width: 80%;"> 	</em>
							</div>
							<div class="floatClear"></div>
						</div>
						<div>
							<div class="floatLeft w-50"></div>
							<div class="floatRight w-50">
								<em style="line-height:15px;width:20%;padding-right: 50px;">)</em>
								<em style="line-height:15px;width: 80%;"></em>
							</div>
							<div class="floatClear"></div>
						</div>
						<div>
							<div class="floatLeft w-50"><span>&nbsp;<?=$debtor?></span></div>
							<div class="floatRight w-50">
								<em style="line-height:15px;width:20%;padding-right: 50px;">)</em>
								<em style="line-height:15px;width: 80%;">Case No. <span><?=$caseno?></span></em>
							</div>
							<div class="floatClear"></div>
						</div>
						<div>
							<div class="floatLeft w-50"></div>
							<div class="floatRight w-50">
								<em style="line-height:15px;width:20%;padding-right: 50px;">)</em>
								<em style="line-height:15px;width: 80%;">Chapter <span><?=$chapter?></span></em>
							</div>
							<div class="floatClear"></div>
						</div>
						<div>
							<div class="floatLeft w-50">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Debtors.</div>
							<div class="floatRight w-50">
								<em style="line-height:15px;width:20%;padding-right: 50px;">)</em>
								<em style="line-height:15px;width: 80%;"></em>
							</div>
							<div class="floatClear"></div>
						</div>
						<div>
							<div class="floatLeft w-50"></div>
							<div class="floatRight w-50">
								<em style="line-height:15px;width:20%;padding-right: 50px;">)</em>
								<em style="line-height:15px;width: 80%;"></em>
							</div>
							<div class="floatClear"></div>
						</div>
						<div>
							<div class="floatLeft w-50" style="border-bottom:1px solid"></div>
							<div class="floatRight w-50">
								<em style="line-height:15px;width:20%;padding-right: 50px;">)</em>
								<em style="line-height:15px;width: 80%;"></em>
							</div>
							<div class="floatClear"></div>
						</div>
						<div>
							<div class="floatLeft w-50"></div>
							<div class="floatRight w-50">
								<em style="line-height:15px;width:20%;padding-right: 50px;">)</em>
								<em style="line-height:15px;width: 80%;"></em>
							</div>
							<div class="floatClear"></div>
						</div>
						<div>
							<div class="floatLeft w-50"><span><?=$debtor_plaintiff?></span></div>
							<div class="floatRight w-50">
								<em style="line-height:15px;width:20%;padding-right: 50px;">)</em>
								<em style="line-height:15px;width: 80%;"></em>
							</div>
							<div class="floatClear"></div>
						</div>
						<div>
							<div class="floatLeft w-50">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Plaintiff,</div>
							<div class="floatRight w-50">
								<em style="line-height:15px;width:20%;padding-right: 50px;">)</em>
								<em style="line-height:15px;width: 80%;">Adversary Pro. <span><?=$adversary_pro?></span></em>
							</div>
							<div class="floatClear"></div>
						</div>
						<div>
							<div class="floatLeft w-50"></div>
							<div class="floatRight w-50">
								<em style="line-height:15px;width:20%;padding-right: 50px;">)</em>
								<em style="line-height:15px;width: 80%;"></em>
							</div>
							<div class="floatClear"></div>
						</div>
						<div>
							<div class="floatLeft w-50">v.</div>
							<div class="floatRight w-50">
								<em style="line-height:15px;width:20%;padding-right: 50px;">)</em>
								<em style="line-height:15px;width: 80%;"></em>
							</div>
							<div class="floatClear"></div>
						</div>
						<div>
							<div class="floatLeft w-50"></div>
							<div class="floatRight w-50">
								<em style="line-height:15px;width:20%;padding-right: 50px;">)</em>
								<em style="line-height:15px;width: 80%;"></em>
							</div>
							<div class="floatClear"></div>
						</div>
						<div>
							<div class="floatLeft w-50">UNITED STATES DEPARTMENT</div>
							<div class="floatRight w-50">
								<em style="line-height:15px;width:20%;padding-right: 50px;">)</em>
								<em style="line-height:15px;width: 80%;"></em>
							</div>
							<div class="floatClear"></div>
						</div>
						<div>
							<div class="floatLeft w-50">OF EDUCATION, <span><?=$etal?></span></div>
							<div class="floatRight w-50">
								<em style="line-height:15px;width:20%;padding-right: 50px;">)</em>
								<em style="line-height:15px;width: 80%;"></em>
							</div>
							<div class="floatClear"></div>
						</div>
						<div>
							<div class="floatLeft w-50"></div>
							<div class="floatRight w-50">
								<em style="line-height:15px;width:20%;padding-right: 50px;">)</em>
								<em style="line-height:15px;width: 80%;"></em>
							</div>
							<div class="floatClear"></div>
						</div>
						<div>
							<div class="floatLeft w-50">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Defendant<span id="defendants" style="display: none;">s</span></div>
							<div class="floatRight w-50">
								<em style="line-height:15px;width:20%;">)</em>
								<em style="line-height:15px;width: 80%;"></em>
							</div>
							<div class="floatClear"></div>
						</div>
						<div>
							<div class="floatLeft w-50"></div>
							<div class="floatRight w-50">
								<em style="line-height:15px;width:20%;">)</em>
								<em  style="line-height:15px;width: 80%;"></em>
							</div>
							<div class="floatClear"></div>
						</div>
						<div>
							<div class="floatLeft w-50" style="border-bottom:1px solid"></div>
							<div class="floatClear"></div>
						</div>
					</td>
				</tr>
				<tr class="text-center">
					<td>
						ATTESTATION OF <span><?=$attestation_name?></span> IN SUPPORT<br>
						OF REQUEST FOR STIPULATION CONCEDING <br>
						DISCHARGEABILITY OF STUDENT LOANS
					</td>
				</tr>
				<tr class="text-center">
					<td><i>PLEASE NOTE: This Attestation should be submitted to the Assistant United States Attorney handling the case. It should not be filed with the court unless such a filing is directed by the court or an attorney.</i></td>
				</tr>
				<tr>
					<td>
						<p style="margin-left: 30px;">I, <span><?=$creator_name?></span>, make this Attestation in support of my claim that excepting the student loans described herein from discharge would cause an “undue hardship” to myself and my dependents within the meaning of 11 U.S.C.§523(a)(8). In support of this Attestation, I state the following under penalty of perjury:
						</p>
					</td>
				</tr>
				<tr>
					<td>
						<h3 class="text-center subheadings">
							I.&nbsp;&nbsp;PERSONAL INFORMATION
						</h3>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatRight" style="width:95%; margin-left:10px;">
							1.<em style="margin-left: 40px;">I am over the age of eighteen and am competent to make this Attestation.</em>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatRight" style="width:95%; margin-left:10px;">
							2.<em style="margin-left: 40px;">I reside at <span class="address_span"><?=$address?></span>, in <span><?=$county?></span>,<span><?=$state?></span>.</em>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatRight" style="width:95%; margin-left:10px;">
							3.<em style="margin-left: 40px;">My household includes the following persons(including myself):</em>

							<div class="household row" style="width:80%;margin:auto">
								<table class="table table-bordered table-striped nostyle" style="margin-top: 20px;">
									<thead>
										<tr>
											<th>Fullname</th>
											<th width="30%">Age</th>
											<th>Relation</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>
												<?=$user_fullname?>
											</td>
											<td width="30%">
												<?=$user_age?>
											</td>
											<td>
												Self
											</td>
										</tr>
										<?php
foreach ($household['fullname'] as $key => $value) {
	?>
										<tr>
											<td>
												<?=$value?>
											</td>
											<td width="30%">
												<?=$household['age'][$key]?>
											</td>
											<td>
												<?=$household['relation'][$key]?>
											</td>
										</tr>
										<?php
}
?>
									</tbody>
								</table>
							</div>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<p>
							<b><i>Questions four through eight request information related to your outstanding student loan debt and your educational history. The Department of Education will furnish this information tothe Assistant United States Attorney (“AUSA”) handling your case, and it should be provided toyou.If you agree that the information provided to you regarding your student loan debt and educational history is accurate, you may simply confirm that you agree, and these questions do not need to be completed. If you have not received the information from Education or the AUSA at thetime you arecompleting this form, or if theinformation is not accurate, you may answer these questions based upon your own knowledge. If you have more than one student loan which you are seeking to discharge in this adversary proceeding, please confirm that the AUSA has complete and accurate information for each loan, or provide that information for each loan.</i></b>
						</p>
					</td>
				</tr>
				<tr>
					<td>
						<div>
							<!-- <div class="floatLeft" style="width:3%;margin-left: 40px;">
								4.
							</div> -->

						<div class="floatRight" style="width:95%; margin-left:10px;">
							4.<em style="margin-left: 40px;">I confirm that the student loan information and educational history provided to me and attached to this Attestation is correct:
								<input class="border" type="checkbox" value="yes" name="correct_loan_info" <?php if ($correct_loan_info == 'yes') {echo "checked";}?> onclick="return false"> Yes
									<input class="border" type="checkbox" value="no" name="correct_loan_info" <?php if ($correct_loan_info == 'no') {echo "checked";}?> style="margin-left:10px" onclick="return false"> No
									<input class="border" type="checkbox" value="other" name="correct_loan_info" <?php if ($correct_loan_info == 'other') {echo "checked";}?> onclick="return false" style="margin-left:10px"> No Information Provided</em>
							</div>
							<div class="floatClear"></div>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<div>
							<!-- <div class="floatLeft" style="width:3%;margin-left: 40px;"></div> -->

							<div class="floatRight" style="width:95%; margin-left:10px;">
								<em style="margin-left: 40px;">[If you answered anything other than “YES”, you must answer questions five through eight].</em>
							</div>
							<div class="floatClear"></div>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;margin-left: 40px;">
							5.
						</div> -->

						<div class="floatRight" style="width:95%; margin-left:10px;">
							5.<em style="margin-left: 40px;">The outstanding balance of the student loan[s] I am seeking to discharge in this adversary proceeding is $<span><?=$sl_loan_amount?></span>.  <span>(See Addendum Chart)</span></em>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;margin-left: 40px;">
							6.
						</div> -->

						<div class="floatRight" style="width:95%; margin-left:10px;">
							6.<em style="margin-left: 40px;">The current monthly payment on such loan[s] is $<span><?=$sl_monthly_payment?></span>. The loan[s] are scheduled to be repaid in <span><?=$sl_date_of_payoff?></span> [OR] <input class="border" type="checkbox" name="student_loan_default" <?php if (isset($student_loan_default) && $student_loan_default == 'yes') {echo 'checked';}?> value="yes" onclick="return false"> My student loan[s] went into default in <span><?=$sl_date_of_default?></span>.   <span>(See Addendum Chart)</span></em>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;margin-left: 40px;">
							7.
						</div> -->

						<div class="floatRight" style="width:95%; margin-left:10px;">
							7.<em style="margin-left: 40px;">I incurred the student loan[s] I am seeking to discharge while attending <span><?=$sl_school_attended?></span> , where I was pursuing a <span><?=$sl_degree_pursued?></span> degree with a specialization in <span><?=$sl_specialization?></span>.   <span>(See Addendum Chart)</span></em>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;margin-left: 40px;">
							8.
						</div> -->

						<div class="floatRight" style="width:95%; margin-left:10px;">
							8.<em style="margin-left: 40px;">In <span><?=$sl_date_school_completed?></span>, I completed my course of study and received a <span><?=$sl_type_of_degree?></span> degree [OR] In <span><?=$sl_date_studies_ceased?></span>, I left my course of study and did not receive a degree.   <span>(See Addendum Chart)</span></em>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;margin-left: 40px;">
							9.
						</div> -->

						<div class="floatRight" style="width:95%; margin-left:10px;">
							9.<em style="margin-left: 40px;">I am currently employed as a <span><?=$job_title?></span> .My employer’s name and address is <span class="employer_address_span"><?=$employer_name_and_address?></span> [OR] <input class="border" type="checkbox" name="not_employed" <?php if (isset($not_employed) && $not_employed == 'yes') {echo 'checked';}?> value="yes" onclick="return false"> I am not currently employed.</em>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<h3 class="text-center subheadings">
							II.&nbsp;&nbsp;CURRENT INCOME AND EXPENSES
						</h3>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;margin-left: 40px;">

						</div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							10.<em style="margin-left: 40px;">I do not have the ability to make payments on my student loans while maintaining a minimal standard of living for myself and my household. I submit the following information to demonstrate this:</em>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<h4 style="margin-left: 40px;">
							<b>
								<i class="subheadings">
									A.&nbsp;&nbsp;
									<span>Household Gross Income</span>
								</i>
							</b>
						</h4>
					</td>
				</tr>
				<tr>
					<td>
						<div>
							<!-- <div class="floatLeft" style="width:3%;">
								11
							</div> -->
							<div class="floatRight super1" style="width:95%; margin-left:10px;">
								11.<em style="margin-left: 40px;">My current monthly household gross income from all sources is $ <span><?=$gross_income?></span><sup>1</sup> . This amount includes the following monthly amounts:</em>
							</div>
							<div class="floatClear"></div>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="supDiv">
							<p style="line-height:10px;margin: 0;">
								<sup>1</sup>“Gross income” means your income before any payroll deductions (for taxes, Social Security, health insurance, etc.) or deductions from other sources of income. You may have included information about your gross income on documents previously filed in your bankruptcy case , including Form B 106I, Schedule I - Your Income (Schedule I). If you filed your Schedule I within the past 18 months and the income information on those documents has not changed, you may refer to that document for the income information provided here. If you filed Schedule I more than 18 months prior to this Attestation, or your income has changed, you should provide your new income information
							</p>
						</div>
					</td>
				</tr>
				<tr>
					<td>

						<!-- <div class="floatLeft" style="width:3%;"></div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							<div class="mt-5 ml-3">
								<div style="">
									<span><?=$gross_employment?></span>  my gross income from employment (if any)
								</div>
								<div>
									<span><?=$unemployment_benefits?></span>  my unemployment benefits
								</div>
								<div>
									<span><?=$social_security?></span>  my Social Security Benefits
								</div>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td>

						<!-- <div class="floatLeft" style="width:3%;"></div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							<div class="other_incomes" style="padding: 10px 0;">
								Other Incomes (If any):
								<?php
foreach ($other_incomes['income_value'] as $key => $value) {
	?>
								<div>
									<span><?=$value?></span> my <span><?=$other_incomes['income_detail'][$key]?></span>
								</div>
								<?php
}
?>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td>

						<!-- <div class="floatLeft" style="width:3%;"></div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							<div>
								<span><?=$other_household_income?></span>  Gross income from employment of other members of household
							</div>
							<div>
								<span><?=$other_household_unemployment?></span>  Unemployment benefits received by other members of household
							</div>
							<div>
								<span><?=$other_household_social_security?></span>  Social Security benefits received by other members of household
							</div>
							<div>
								<span><?=$other_household_other_income?></span>  Other income from any source received by other members of household
							</div>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>

				<!-- <tr>
					<td>
						<div class="floatRight supfooter1" style="width:95%; margin-left:10px;">
							<p>
								<sup>1</sup>“Gross income” means your income before any payroll deductions (for taxes, Social Security, health insurance, etc.) or deductions from other sources of income. You may have included information about your gross income on documents previously filed in your bankruptcy case , including Form B 106I, Schedule I - Your Income (Schedule I). If you filed your Schedule I within the past 18 months and the income information on those documents has not changed, you may refer to that document for the income information provided here. If you filed Schedule I more than 18 months prior to this Attestation, or your income has changed, you should provide your new income information
							</p>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr> -->

				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;">12</div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							12.<em style="margin-left: 40px;">The current monthly household gross income stated above(select which applies):</em>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;"></div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							<p>Will client be submitting tax returns, 2-months of pay stubs, or other alternative documents of income? <br>Tax return(s) <input class="border" type="checkbox" onclick="return false" value="tax_returns" <?php if ($income_proof_submitted == 'tax_returns') {echo "checked";}?> name="income_proof_submitted"> , Pay stubs <input class="border" type="checkbox" onclick="return false" value="pay_stubs" <?php if ($income_proof_submitted == 'pay_stubs') {echo "checked";}?> name="income_proof_submitted"> , Alternative documentation <input class="border" type="checkbox" onclick="return false" name="income_proof_submitted" value="alternative_documentation" <?php if ($income_proof_submitted == 'alternative_documentation') {echo "checked";}?>></p>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatRight" style="width:95%; margin-left:10px;">

							<p><input class="border" type="checkbox" onclick="return false" value="tax_returns" name="income_proof_submitted1" <?php if ($income_proof_submitted1 == 'tax_returns') {echo "checked";}?>> Includes a monthly average of the gross income shown on the most recent tax return[s] filed for myself and other members of my household, which are attached, and <br>the amounts stated on such tax returns have not changed materially since the tax year of such returns; OR</p>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatRight" style="width:95%; margin-left:10px;">

							<p><input class="border" type="checkbox" onclick="return false" value="pay_stubs" name="income_proof_submitted1" <?php if ($income_proof_submitted1 == 'pay_stubs') {echo "checked";}?>> Represents an average amount calculated from the most recent two months of gross income stated on four (4) consecutive paystubs from my current employment, which are attached; OR</p>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatRight" style="width:95%; margin-left:10px;">

							<p><input class="border" type="checkbox" onclick="return false" name="income_proof_submitted1" value="alternative_documentation" <?php if ($income_proof_submitted1 == 'alternative_documentation') {echo "checked";}?>> My current monthly household gross income is not accurately reflected on either <br>
							recent tax returns or pay stubs from current employment, and I have submitted instead the following documents verifying current gross household income from employment of household members: <br>
							<p style="width: 100%;border:0;outline: 0;font-family: monospace;font-size: 13px;line-height: 15px;"> <?=$alternative_document_titles?></p>
						</p>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;">13</div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							13.<em style="margin-left: 40px;">In addition, I have submitted</em>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;"></div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							<p style="width: 100%;border:0;outline: 0;font-family: monospace;font-size: 13px;line-height: 15px;"><?=$additional_document_titles?></p>
							verifying the sources of income other than income from employment, as such income is not shown on [most recent tax return[s] or paystubs].
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<h4 style="margin-left:40px">
							<b>
								<i class="subheadings">
									B.&nbsp;&nbsp;
									<span>Monthly Expenses</span>
								</i>
							</b>
						</h4>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;">14.</div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							14.<em style="margin-left: 40px;">My current monthly household expenses do/do not exceed the amounts listed below based on the number of people in my household for the following categories:</em>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;"></div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							<p style="text-decoration:underline">(a) Living Expenses<sup>2</sup> </p>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatLeft" style="width: 50%;margin-left: 60px; vertical-align: text-top;">
							i.&nbsp;&nbsp;&nbsp;My expenses for food<br>
							<div style="margin-left:20px">
								$431(one person)<br>
								$779(two persons)<br>
								$903(three persons)<br>
								$1028(four persons)<br>
							</div>
						</div>
						<div class="floatRight" style="width: 40%; vertical-align: text-top;">
							Do exceed <input type="checkbox" onclick="return false" name="food_expense" value="yes" <?php if ($food_expense == 'yes') {echo "checked";}?> style="margin-right: 10px;">Do not exceed <input type="checkbox" onclick="return false" name="food_expense" value="no" <?php if ($food_expense == 'no') {echo "checked";}?> style="margin-right: 10px;">
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="supDiv2">
							<p style="line-height:10px;margin: 0;">
								<sup>2</sup>The living expenses listed in Question 14 and 15 have been adopted from the Internal Revenue Service Collection Financial Standards “National Standards” and “Local Standards” for the year in which this form is issued. This form is updated annually to reflect changes to these expenses.
							</p>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatLeft" style="width: 50%;margin-left: 60px; vertical-align: text-top;">
							ii.&nbsp;&nbsp;&nbsp;My expenses for housekeeping supplies
							<div style="margin-left:20px">
								$40 (one person)<br>
								$82(two persons)<br>
								$74(three persons)<br>
								$85(four persons)<br>
							</div>
						</div>
						<div class="floatRight" style="width: 40%; vertical-align: text-top;">
							Do exceed <input type="checkbox" onclick="return false" name="housekeeping_expense" value="yes" <?php if ($housekeeping_expense == 'yes') {echo "checked";}?> style="margin-right: 10px;">Do not exceed <input type="checkbox" onclick="return false" name="housekeeping_expense" value="no" <?php if ($housekeeping_expense == 'no') {echo "checked";}?> style="margin-right: 10px;">
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatLeft" style="width: 50%;margin-left: 60px; vertical-align: text-top;">
							iii.&nbsp;&nbsp;&nbsp;My expenses for apparel & services
							<div style="margin-left:20px">
								$99 (one person)<br>
								$161(two persons)<br>
								$206(three persons)<br>
								$279(four persons)<br>
							</div>
						</div>
						<div class="floatRight" style="width: 40%; vertical-align: text-top;">
							Do exceed <input type="checkbox" onclick="return false" name="apparel_expense" value="yes" <?php if ($apparel_expense == 'yes') {echo "checked";}?> style="margin-right: 10px;">Do not exceed <input type="checkbox" onclick="return false" name="apparel_expense" value="no" <?php if ($apparel_expense == 'no') {echo "checked";}?> style="margin-right: 10px;">
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatLeft" style="width: 50%;margin-left: 60px; vertical-align: text-top;">
							iv.&nbsp;&nbsp;&nbsp;My expenses for (non-medical) personal care products and services
							<div style="margin-left:20px">
								$45(one person)<br>
								$82(two persons)<br>
								$78(three persons)<br>
								$96(four persons)<br>
							</div>
						</div>
						<div class="floatRight" style="width: 40%; vertical-align: text-top;">
							Do exceed <input type="checkbox" onclick="return false" name="nonmedical_expense" value="yes" <?php if ($nonmedical_expense == 'yes') {echo "checked";}?> style="margin-right: 10px;">Do not exceed <input type="checkbox" onclick="return false" name="nonmedical_expense" value="no" <?php if ($nonmedical_expense == 'no') {echo "checked";}?> style="margin-right: 10px;">
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatLeft" style="width: 50%;margin-left: 60px; vertical-align: text-top;">
							v.&nbsp;&nbsp;&nbsp;My miscellaneous expenses(not included elsewhere on this Attestation):
							<div style="margin-left:20px">
								$170(one person)<br>
								$306(two persons)<br>
								$349(three persons)<br>
								$412(four persons)<br>
							</div>
						</div>
						<div class="floatRight" style="width: 40%; vertical-align: text-top;margin-left: auto;">
							Do exceed <input type="checkbox" onclick="return false" name="misc_expense" value="yes" <?php if ($misc_expense == 'yes') {echo "checked";}?> style="margin-right: 10px;">Do not exceed <input type="checkbox" onclick="return false" name="misc_expense" value="no" <?php if ($misc_expense == 'no') {echo "checked";}?> style="margin-right: 10px;">
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatLeft" style="width: 50%;margin-left: 60px; vertical-align: text-top;">
							vi.&nbsp;&nbsp;&nbsp;My total expenses in these categories
							<div style="margin-left:20px">
								$785 (one person)<br>
								$1410 (two persons)<br>
								$1610 (three persons)<br>
								$1900 (four persons)<br>
								Add $344 per each additional member if more than four in household.<br>
							</div>
						</div>
						<div class="floatRight" style="width: 40%; vertical-align: text-top;">
							Do exceed <input type="checkbox" onclick="return false" name="total_expense" value="yes" <?php if ($total_expense == 'yes') {echo "checked";}?> style="margin-right: 10px;">Do not exceed <input type="checkbox" onclick="return false" name="total_expense" value="no" <?php if ($total_expense == 'no') {echo "checked";}?> style="margin-right: 10px;">
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;"></div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							<p style="text-decoration:underline">(b) Uninsured medical costs</p>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatLeft" style="width: 50%;margin-left: 60px; vertical-align: text-top;">
							i.&nbsp;&nbsp;&nbsp;My uninsured, out of pocket medical costs<br>
							<div style="margin-left:20px">
								$75(per household member under 65)<br>
								$153(per household member 65 or older)<br>
							</div>
						</div>
						<div class="floatRight" style="width: 40%; vertical-align: text-top;">
							Do exceed <input type="checkbox" onclick="return false" name="incured_medical_costs" value="yes" <?php if ($incured_medical_costs == 'yes') {echo "checked";}?> style="margin-right: 10px;">Do not exceed <input type="checkbox" onclick="return false" name="incured_medical_costs" value="no" <?php if ($incured_medical_costs == 'no') {echo "checked";}?> style="margin-right: 10px;">
						</div>
						<div class="floatClear"></div>
						<div style="width: 100%;margin-left: 60px; vertical-align: text-top;">
							<p>
								If you answered that your uninsured, out of pocket medical costs exceed the listed amounts for any household member, and you would like the AUSA to consider your additional expenses as necessary, you may list the household member’s total expenses and explain the need for such expenses here.

								<p style="width: 100%;border:0;outline: 0;font-family: monospace;font-size: 13px;line-height: 15px;"><?=$greater_medical_explanation?></p>
							</p>
							<div class="mt-5">
								[If you filed a Form 122A-2 Chapter 7 Means Test or 122C-2 Calculation of Disposable Income in your bankruptcy case, you may refer to lines 6 and 7 of those forms for information.]<sup>3</sup>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="supDiv3">
							<p style="line-height:10px;margin: 0;">
								<sup>3</sup>Forms 122A-2 and 122C-2 are referred to collectively here as the “Means Test.” If you filed a Means Test in your bankruptcy case, you may refer to it for information requested here and in other expense categories below.If you did not file a Means Test,you may refer to your Schedule I and Form 106J – Your Expenses (Schedule J) in the bankruptcy case, which may also list information relevant to these categories. You should only use information from these documents if your expenses have not changed since you filed them.</p>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<div>
							<!-- <div class="floatLeft" style="width:3%;">15</div> -->
							<div class="floatRight" style="width:95%; margin-left:10px;">
								15.<em style="margin-left: 40px;">My current monthly household expenses in the following categories are as follows:</em>
							</div>
							<div class="floatClear"></div>
						</div>
						<div>
							<div class="floatLeft" style="width:3%;"></div>
							<div class="floatRight" style="width:95%; margin-left:auto;">
								<p style="text-decoration:underline">(a) Payroll Deductions</p>
							</div>
							<div class="floatClear"></div>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatLeft" style="width: 60%;margin-left: 60px; vertical-align: text-top;">
							i.&nbsp;&nbsp;&nbsp;Taxes, Medicare and Social Security<br>[You may refer to line 16 of the Means Test or Schedule I, line 5a]
						</div>
						<div class="floatRight" style="width: 30%; vertical-align: text-top;">
							<span>$<?=$tax_deduction?></span>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatLeft" style="width: 60%;margin-left: 60px; vertical-align: text-top;">
							ii.&nbsp;&nbsp;&nbsp;Contributions to retirement accounts<br>[You may refer to line 17 of the Means Test or Schedule I, line 5b and c]
						</div>
						<div class="floatRight" style="width: 30%; vertical-align: text-top;">
							<span>$<?=$retirement_cont?></span>
						</div>
						<div class="floatClear"></div>
						<div class="floatLeft" style="width: 60%;margin-left: 60px; vertical-align: text-top;">
							Are these contributions required as a condition of your employment?
						</div>
						<div class="floatRight" style="width: 30%; vertical-align: text-top;">
							YES <input type="checkbox" onclick="return false" name="contribution_required" value="yes" <?php if ($contribution_required == 'yes') {echo "checked";}?>>/ NO <input type="checkbox" onclick="return false" name="contribution_required" value="no" <?php if ($contribution_required == 'no') {echo "checked";}?>>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatLeft" style="width: 60%;margin-left: 60px; vertical-align: text-top;">
							iii.&nbsp;&nbsp;&nbsp;Union dues<br>[You may refer to line 17 of the Means Test or Schedule I, line 5g]
						</div>
						<div class="floatRight" style="width: 30%; vertical-align: text-top;">
							<span>$<?=$union_dues?></span>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatLeft" style="width: 60%;margin-left: 60px; vertical-align: text-top;">
							iv.&nbsp;&nbsp;&nbsp;Life insurance<br>[You may refer to line 18 of the Means Test or Schedule I, line 5e]
						</div>
						<div class="floatRight" style="width: 30%; vertical-align: text-top;">
							<span>$<?=$life_insurance?></span>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatLeft" style="width: 60%;margin-left: 60px; vertical-align: text-top;">
							Are the payments for a term policy covering your life?
						</div>
						<div class="floatRight" style="width: 30%; vertical-align: text-top;">
							YES <input type="checkbox" onclick="return false" name="term_policy_covering" value="yes" <?php if ($term_policy_covering == 'yes') {echo "checked";}?>>/ NO <input type="checkbox" onclick="return false" name="term_policy_covering" value="no" <?php if ($term_policy_covering == 'no') {echo "checked";}?>>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatLeft" style="width: 60%;margin-left: 60px; vertical-align: text-top;">
							v.&nbsp;&nbsp;&nbsp;Court-ordered alimony and child support<br>[You may refer to line 19 of the Means Test or Schedule I, line 5f]
						</div>
						<div class="floatRight" style="width: 30%; vertical-align: text-top;">
							<span>$<?=$divorce_support?></span>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatLeft" style="width: 60%;margin-left: 60px; vertical-align: text-top;">
							vi.&nbsp;&nbsp;&nbsp;Health insurance<br>[You may refer to line 25 of the Means Test or Schedule I, line 5e]
						</div>
						<div class="floatRight" style="width: 30%; vertical-align: text-top;">
							<span>$<?=$health_insurance?></span>
						</div>
						<div class="floatClear"></div>
						<div class="floatLeft" style="width: 60%;margin-left: 60px; vertical-align: text-top;">
							Does the policy cover any persons other than yourself and your family members?
						</div>
						<div class="floatRight" style="width: 30%; vertical-align: text-top;">
							YES <input type="checkbox" onclick="return false" name="other_person_policy" value="yes" <?php if ($other_person_policy == 'yes') {echo "checked";}?>>/ NO <input type="checkbox" onclick="return false" name="other_person_policy" value="no" <?php if ($other_person_policy == 'no') {echo "checked";}?>>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatLeft" style="width: 90%;margin-left: 60px; vertical-align: text-top;">
							vii.&nbsp;&nbsp;&nbsp;Other payroll deductions
							<?php

foreach ($other_deductions['deduction'] as $key => $value) {
	?>
								<div class="one_deduct">
									<div class="w-50">
										<span><?=$value?></span>
									</div>
									<div class="w-50">
										$<span><?=$other_deductions['amount'][$key]?></span>

									</div>
								</div>
											<?php
}
?>
						</div>

						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;"></div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							<p style="text-decoration:underline">(b) Housing Costs<sup>4</sup></p>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="supDiv2 overlapFootnote">
							<p style="line-height:12px;margin: 0;">
								<sup>4</sup>You should list the expenses you actually pay in Housing Costs and Transportation Costs categories. If these expenses have not changed since you filed your Schedule J, you may refer to the expenses listed there, including housing expenses(generally onlines 4 through 6 of Schedule J) and transportation expenses (generally on lines 12, 15c and 17).</p>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatLeft" style="width: 60%;margin-left: 60px; vertical-align: text-top;">
							i.&nbsp;&nbsp;&nbsp;Mortgage or rent payments
						</div>
						<div class="floatRight" style="width: 30%; vertical-align: text-top;">
							<span>$<?=$mortgage_rent?></span>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatLeft" style="width: 60%;margin-left: 60px; vertical-align: text-top;">
							ii.&nbsp;&nbsp;&nbsp;Property taxes(if paid separately)
						</div>
						<div class="floatRight" style="width: 30%; vertical-align: text-top;">
							<span>$<?=$property_tax?></span>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatLeft" style="width: 60%;margin-left: 60px; vertical-align: text-top;">
							iii.&nbsp;&nbsp;&nbsp;Home owners or renters insurance (if paid separately)
						</div>
						<div class="floatRight" style="width: 30%; vertical-align: text-top;">
							<span>$<?=$renters_insurance?></span>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatLeft" style="width: 60%;margin-left: 60px; vertical-align: text-top;">
							iv.&nbsp;&nbsp;&nbsp;Home maintenance and repair (average last 12 months’ amounts)
						</div>
						<div class="floatRight" style="width: 30%; vertical-align: text-top;">
							<span>$<?=$home_maintenance?></span>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatLeft" style="width: 60%;margin-left: 60px; vertical-align: text-top;">
							v.&nbsp;&nbsp;&nbsp;Utilities (include monthly gas, electric water, heating oil, garbage collection, residential telephone service, cellphone service,cabletelevision, and internet service)
						</div>
						<div class="floatRight" style="width: 30%; vertical-align: text-top;">
							<span>$<?=$utilities?></span>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>

						<!-- <div class="floatLeft" style="width:3%;"></div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							<p style="text-decoration:underline">(c) Transportation Costs</p>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;"></div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							<div class="multiple_vehicles">
								<?php
if (gettype($vehicle_payments) == 'object' || gettype($vehicle_payments) == 'array') {
	foreach ($vehicle_payments['payment'] as $key => $value) {
		?>
								<div style="border: 1px solid #f0f0f0;padding: 10px 0;margin: 10px 0;height: 80px;" class="vehicle">
									<div style="display: block;width: 100%;height: 20px;">
										<div class="floatLeft" style="width: 60%;margin-left: 20px; vertical-align: text-top;">
											i.&nbsp;&nbsp;&nbsp;Vehicle payments(itemize per vehicle)
										</div>
										<div class="floatRight" style="width: 30%; vertical-align: text-top;">
											<span>$<?=$value?></span>
										</div>
										<div class="floatClear"></div>
									</div>
									<div style="display: block;width: 100%;">
										<div class="floatLeft" style="width: 60%;margin-left: 20px; vertical-align: text-top;">
											ii.&nbsp;&nbsp;&nbsp;Monthly average costs of operating vehicles (including gas, routine maintenance, monthly insurance cost)
										</div>
										<div class="floatRight" style="width: 30%; vertical-align: text-top;">
											<span>$<?=$vehicle_payments['operating_costs'][$key]?></span>
										</div>
										<div class="floatClear"></div>
									</div>
								</div>
								<?php
}
} else {
	?>
								<div style="border: 1px solid #f0f0f0;padding: 10px 0;margin: 10px 0;height: 60px;" class="vehicle">
									<div style="display: block;width: 100%;height: 25px;">
										<div class="floatLeft" style="width: 60%;margin-left: 20px; vertical-align: text-top;">
											i.&nbsp;&nbsp;&nbsp;Vehicle payments(itemize per vehicle)
										</div>
										<div class="floatRight" style="width: 30%; vertical-align: text-top;">
											<span>$<?=$vehicle_payments['payment'][$key]?></span>
										</div>
										<div class="floatClear"></div>
									</div>
									<div style="display: block;width: 100%;">
										<div class="floatLeft" style="width: 60%;margin-left: 20px; vertical-align: text-top;">
											ii.&nbsp;&nbsp;&nbsp;Monthly average costs of operating vehicles (including gas, routine maintenance, monthly insurance cost)
										</div>
										<div class="floatRight" style="width: 30%; vertical-align: text-top;">
											<span>$<?=$vehicle_payments['operating_costs'][$key]?></span>
										</div>
										<div class="floatClear"></div>
									</div>
								</div>
<?php
}
?>
							</div>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatLeft" style="width: 60%;margin-left: 60px; vertical-align: text-top;">
							iii.&nbsp;&nbsp;&nbsp;Public transportation costs
						</div>
						<div class="floatRight" style="width: 30%; vertical-align: text-top;">
							<span>$<?=$public_transportation?></span>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>

						<!-- <div class="floatLeft" style="width:3%;"></div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							<p style="text-decoration:underline">(d) Other Necessary Expenses</p>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatLeft" style="width: 60%;margin-left: 60px; vertical-align: text-top;">
							i.&nbsp;&nbsp;&nbsp;Court-ordered alimony and child support payments (if not deducted from pay)<br>[You may refer to line 19 of Form 122A-2 or 122C-2 or Schedule J, line 18]
						</div>
						<div class="floatRight" style="width: 30%; vertical-align: text-top;">
							<span>$<?=$other_divorce_support?></span>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatLeft" style="width: 60%;margin-left: 60px; vertical-align: text-top;">
							ii.&nbsp;&nbsp;&nbsp;Babysitting, day care, nursery and preschool costs<br>[You may refer to line 21 of Form 122A-2 or 122C-2 or Schedule J, line 8]<sup>5</sup>
						</div>
						<div class="floatRight" style="width: 30%; vertical-align: text-top;">
							<span>$<?=$child_care_costs?></span>
						</div>
						<div class="floatClear"></div>
						<div class="floatLeft" style="width: 95%;margin-left: 60px; vertical-align: text-top;">
							Explain the circumstances making it necessary for you to expend this amount:
							<p style="width: 100%;border:0;outline: 0;font-family: monospace;font-size: 13px;line-height: 15px;"><?=$child_care_costs_explanation?></p>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="supDiv4">
							<p style="line-height:10px;margin: 0;">
								<sup>5</sup>Line 8 of Schedule J allows listing of expenses for “child care and children’s education costs.” You should not list any educational expenses for your children here, aside from necessary nursery or preschool costs
							</p>
						</div>
					</td>
				</tr>













				<tr>
					<td>
						<div class="floatLeft" style="width: 60%;margin-left: 60px; vertical-align: text-top;">
							iii.&nbsp;&nbsp;&nbsp;Health insurance (if not deducted from pay)<br>[You may refer to line 25 of the Means Test or Schedule J, line 15b]
						</div>
						<div class="floatRight" style="width: 30%; vertical-align: text-top;">
							<span>$<?=$health_insurance_nopay?></span>
						</div>
						<div class="floatClear"></div>
						<div class="floatLeft" style="width: 60%;margin-left: 60px; vertical-align: text-top;">
							Does the policy cover any persons other than yourself and your family members?
						</div>
						<div class="floatRight" style="width: 30%; vertical-align: text-top;">
							YES <input type="checkbox" onclick="return false" name="other_person_policy_nopay" value="yes" <?php if ($other_person_policy_nopay == 'yes') {echo "checked";}?>>/ NO <input type="checkbox" onclick="return false" name="other_person_policy_nopay" value="no" <?php if ($other_person_policy_nopay == 'no') {echo "checked";}?>>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatLeft" style="width: 60%;margin-left: 60px; vertical-align: text-top;">
							iv.&nbsp;&nbsp;&nbsp;Life insurance (if not deducted from pay)<br>[You may refer to line 25 of the Means Test or Schedule J, line 15a]
						</div>
						<div class="floatRight" style="width: 30%; vertical-align: text-top;">
							<span>$<?=$life_insurance_nopay?></span>
						</div>
						<div class="floatClear"></div>
						<div class="floatLeft" style="width: 60%;margin-left: 60px; vertical-align: text-top;">
							Are the payments for a term policy covering your life?
						</div>
						<div class="floatRight" style="width: 30%; vertical-align: text-top;">
							YES <input type="checkbox" onclick="return false" name="term_policy_covering_nopay" value="yes" <?php if ($term_policy_covering_nopay == 'yes') {echo "checked";}?>>/ NO <input type="checkbox" onclick="return false" name="term_policy_covering_nopay" value="no" <?php if ($term_policy_covering_nopay == 'no') {echo "checked";}?>>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatLeft" style="width: 60%;margin-left: 60px; vertical-align: text-top;">
							v.&nbsp;&nbsp;&nbsp;Dependent care (for elderly or disabled	family members)<br>[You may refer to line 26 of the Means Test or Schedule J, line 19]
						</div>
						<div class="floatRight" style="width: 30%; vertical-align: text-top;">
							<span>$<?=$dependent_care?></span>
						</div>
						<div class="floatClear"></div>
						<div class="floatLeft" style="width: 95%;margin-left: 60px; vertical-align: text-top;">
							Explain the circumstances making it necessary for you to expend this amount:
							<p style="width: 100%;border:0;outline: 0;font-family: monospace;font-size: 13px;line-height: 15px;"><?=$dependent_care_costs_explanation?></p>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatLeft" style="width: 60%;margin-left: 60px; vertical-align: text-top;">
							vi.&nbsp;&nbsp;&nbsp;Payments on delinquent federal, state or local tax debt<br>[You may refer to line 35 of the Means Test or Schedule J, line 16]
						</div>
						<div class="floatRight" style="width: 30%; vertical-align: text-top;">
							<span>$<?=$delinquent_care?></span>
						</div>
						<div class="floatClear"></div>
						<div class="floatLeft" style="width: 60%;margin-left: 60px; vertical-align: text-top;">
							Are these payments being made pursuant to an agreement with the taxing authority?
						</div>
						<div class="floatRight" style="width: 30%; vertical-align: text-top;">
							YES <input type="checkbox" onclick="return false" name="taxing_authority" value="yes" <?php if ($taxing_authority == 'yes') {echo "checked";}?>>/ NO <input type="checkbox" onclick="return false" name="taxing_authority" value="no" <?php if ($taxing_authority == 'no') {echo "checked";}?>>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatLeft" style="width: 60%;margin-left: 60px; vertical-align: text-top;">
							vii.&nbsp;&nbsp;&nbsp;Payments on other student loans<br>I am not seeking to discharge
						</div>
						<div class="floatRight" style="width: 30%; vertical-align: text-top;">
							<span>$<?=$other_student_loans?></span>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="floatLeft" style="width: 60%;margin-left: 60px; vertical-align: text-top;">
							viii.&nbsp;&nbsp;&nbsp;Other expenses I believe necessary for a minimal standard of living.
						</div>
						<div class="floatRight" style="width: 30%; vertical-align: text-top;">
							<span>$<?=$other_expenses_living?></span>
						</div>
						<div class="floatClear"></div>
						<div class="floatLeft" style="width: 95%;margin-left: 60px; vertical-align: text-top;">
							Explain the circumstances making it necessary for you to expend this amount:<br>
							<p style="width: 100%;border:0;outline: 0;font-family: monospace;font-size: 13px;line-height: 15px;"><?=$other_expenses_living_explanation?></p>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;">16</div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							16.<em style="margin-left: 40px;">After deducting the foregoing monthly expenses from my household gross income, I have <span><?=$remaining_income?></span> [no, or amount] remaining income.</em>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;">17</div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							17.<em style="margin-left: 40px;">In addition to the foregoing expenses, I anticipate I will incur additional monthly expenses in the future for my, and my dependents’, basic needs that are currently not met<sup>6</sup>. These include the following:</em>
						</div>
						<div class="floatClear"></div>

						<!-- <div class="floatLeft" style="width:3%;"></div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							<!-- <h5 class=""> -->
								<p style="width: 100%;border:0;outline: 0;font-family: monospace;font-size: 13px;line-height: 15px;"><?=$anticipated_expenses_explanation?></p>
							<!-- </h5> -->
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="supDiv4">
							<p style="line-height:10px;margin: 0;">
								<sup>6</sup> If you have forgone expenses for any basic needs and anticipate that you will incur such expenses in the future, you may list them here and explain the circumstances making it necessary for you to incur such expenses
							</p>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<h3 class="text-center mt-5 subheadings">
							III.&nbsp;&nbsp;FUTURE INABILITY TO REPAY STUDENT LOANS
						</h3>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;">18</div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							18.<em style="margin-left: 40px;">For the following reasons, it should be presumed that my financial circumstances are unlikely to materially improve over a significant portion of the repayment period (answer all that apply):</em>
						</div>
						<div class="floatClear"></div>

						<!-- <div class="floatLeft" style="width:3%;"></div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							<div class="mt-3">
								<input class="border" type="checkbox" name="presume_repay_inability[]" value="over_age" <?php if (in_array('over_age', $presume_repay_inability)) {echo "checked";}?> onclick="return false"> I am over the age of 65.
							</div>

							<div class="mt-3">
								<input class="border" type="checkbox" name="presume_repay_inability[]" value="10_yrs_repay" <?php if (in_array('10_yrs_repay', $presume_repay_inability)) {echo "checked";}?> onclick="return false"> The student loans I am seeking to discharge have been in repayment status for at least 10 years (excluding any period during which I was enrolled as a student).
							</div>

							<div class="mt-3">
								<input class="border" type="checkbox" name="presume_repay_inability[]" value="incomplete_education" <?php if (in_array('incomplete_education', $presume_repay_inability)) {echo "checked";}?> onclick="return false"> I did not complete the education for which I incurred the student loan[s].
							</div>

							<div class="mt-3">
								Describe how not completing your degree has inhibited your future earning capacity:<br>
								<p style="width: 100%;border:0;outline: 0;font-family: monospace;font-size: 13px;line-height: 15px;"><?=$incomplete_degree_explanation?></p>
							</div>

							<div class="mt-3">
								<input class="border" type="checkbox" name="presume_repay_inability[]" value="disability" <?php if (in_array('disability', $presume_repay_inability)) {echo "checked";}?> onclick="return false"> I have a disability or chronic injury impacting my income potential.
							</div>

							<div class="mt-3">
								Describe the disability or injury and its effects on your ability to work,and indicate whether you receive any governmental benefits attributable to this disability or injury:<br>
								<p style="width: 100%;border:0;outline: 0;font-family: monospace;font-size: 13px;line-height: 15px;"><?=$disability_explanation?></p>
							</div>

							<div class="mt-3">
								<input class="border" type="checkbox" name="presume_repay_inability[]" value="unemployed" <?php if (in_array('unemployed', $presume_repay_inability)) {echo "checked";}?> onclick="return false"> I have been unemployed for at least five of the past ten years.
							</div>

							<div class="mt-3">
								Please explain your efforts to obtain employment.<br>
								<p style="width: 100%;border:0;outline: 0;font-family: monospace;font-size: 13px;line-height: 15px;"><?=$unemployment_explanation?></p>
							</div>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;">19</div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							19.<em style="margin-left: 40px;">For the following additional reasons, my financial circumstances are unlikely to materially improve over a significant portion of the repayment period (answer all that apply):</em>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>

						<!-- <div class="floatLeft" style="width:3%;"></div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							<div class="mt-3">
								<input class="border" type="checkbox" name="repay_inability[]" value="close_institution" <?php if (in_array('close_institution', $repay_inability)) {echo "checked";}?> onclick="return false"> I incurred student loans I am seeking to discharge in pursuit of a degree from an institution that is now closed.
							</div>

							<div class="mt-3">
								Describe how the school closure inhibited your future earnings capacity:<br>
								<p style="width: 100%;border:0;outline: 0;font-family: monospace;font-size: 13px;line-height: 15px;"><?=$school_closure_explanation?></p>
							</div>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>

						<!-- <div class="floatLeft" style="width:3%;"></div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							<div class="mt-3">
								<input class="border" type="checkbox" name="repay_inability[]" value="unemployed" <?php if (in_array('unemployed', $repay_inability)) {echo "checked";}?> onclick="return false"> I am not currently employed.
							</div>

							<div class="mt-3">
								<input class="border" type="checkbox" name="repay_inability[]" value="close_institution" <?php if (in_array('close_institution', $repay_inability)) {echo "checked";}?> onclick="return false">I am currently employed, but I am unable to obtain employment in the field for which I am educated or have received specialized training.
							</div>

							<div class="mt-3">
								Describe reasons for inability to obtain such employment, and indicate if you have ever been able to obtain such employment:<br>
								<p style="width: 100%;border:0;outline: 0;font-family: monospace;font-size: 13px;line-height: 15px;"><?=$employment_inability_explanation?></p>
							</div>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;"></div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							<div class="mt-3">
								<input class="border" type="checkbox" name="repay_inability[]" value="insufficient_income" <?php if (in_array('insufficient_income', $repay_inability)) {echo "checked";}?> onclick="return false"> I am currently employed, but my income is insufficient to pay my loans and unlikely to increase to an amount necessary to make substantial payments on the student loans I am seeking to discharge.
							</div>

							<div class="mt-3">
								Please explain why you believe this is so:<br>
								<p style="width: 100%;border:0;outline: 0;font-family: monospace;font-size: 13px;line-height: 15px;"><?=$insufficient_income_explanation?></p>
							</div>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;"></div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">

							<div class="mt-3">
								<input class="border" type="checkbox" name="repay_inability[]" value="other_circumstances" <?php if (in_array('other_circumstances', $repay_inability)) {echo "checked";}?> onclick="return false"> Other circumstances exist making it unlikely I will be able to make payments for a significant part of the repayment period.
							</div>

							<div class="mt-3">
								Explain these circumstances:<br>
								<p style="width: 100%;border:0;outline: 0;font-family: monospace;font-size: 13px;line-height: 15px;"><?=$other_circumstances_explanation?></p>
							</div>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<h3 class="text-center subheadings">
							IV.&nbsp;&nbsp;PRIOR EFFORTS TO REPAY LOANS
						</h3>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;">20</div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							20.<em style="margin-left: 40px;">I have made good faith efforts to repay the student loans at issue in this proceeding, including the following efforts:</em>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;">21</div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							21.<em style="margin-left: 40px;">Since receiving the student loans at issue, I have made a total of $ <span><?=$paid_amount?></span> in payments on the loans, including the following:</em>
						</div>
						<div class="floatClear"></div>

						<!-- <div class="floatLeft" style="width:3%;"></div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							<div class="mt-3">
								<input class="border" type="checkbox" name="loan_payments[]" value="regular_monthly_payment" <?php if (in_array('regular_monthly_payment', $loan_payments)) {echo "checked";}?> onclick="return false"> regular monthly payments of $ <span><?=$regular_payment_amount?></span> each.
							</div>

							<div class="mt-3 additional_payments">
								<input class="border" type="checkbox" name="loan_payments[]" value="additional_payment" <?php if (in_array('additional_payment', $loan_payments)) {echo "checked";}?> onclick="return false"> additional payments, including
								<?php
foreach ($additional_payments as $key => $value) {
	?>
								$ <span><?=$value?></span>&nbsp;&nbsp;
								<?php }?>.
							</div>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;">22</div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							22.<em style="margin-left: 40px;">I have received <span><?=$forbearance?></span> forbearances or deferments. I spent a period totaling <span><?=$forbearance_months?></span> months in forbearance or deferment.</em>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;">23</div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							23.<em style="margin-left: 40px;">I have attempted to contact the company that services or collects on my student loans or the Department of Education regarding payment options, forbearance and deferment options, or loan consolidation at least <span><?=$contact_number?></span> times.</em>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;">24</div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							24.<em style="margin-left: 40px;">I have sought to enroll in one or more “Income Driven Repayment Programs” or similar repayment programs offered by the Department of Education, including the following:</em>
						</div>
						<div class="floatClear"></div>

						<!-- <div class="floatLeft" style="width:3%;"></div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							<div class="mt-3">
								Description of efforts:<br>
								<p style="width: 100%;border:0;outline: 0;font-family: monospace;font-size: 13px;line-height: 15px;"><?=$idr_explanation?></p>
							</div>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;">25</div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							25.<em style="margin-left: 40px;">[If you did not enroll in such a program].I have not enrolled in an “Income Driven Repayment Program” or similar repayment program offered by the Department of Education for the following reasons:</em>
						</div>
						<div class="floatClear"></div>

						<!-- <div class="floatLeft" style="width:3%;"></div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							<div class="mt-3">
								<p style="width: 100%;border:0;outline: 0;font-family: monospace;font-size: 13px;line-height: 15px;"><?=$no_idr_explanation?></p>
							</div>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;">26</div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							26.<em style="margin-left: 40px;">Describe any other facts indicating you have acted in good faith in the past in attempting to repay the student loan(s) you are seeking to discharge. These may include efforts to obtain employment, maximize your income,or minimize your expenses. They also may include any efforts you made to apply for a federal loan consolidation, respond to outreach from a loan servicer or collector, or engage meaningfully with a third party, you believed would assist you in managing your student loan debt.</em>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>

						<!-- <div class="floatLeft" style="width:3%;"></div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							<div class="mt-3">
								<p style="width: 100%;border:0;outline: 0;font-family: monospace;font-size: 13px;line-height: 15px;"><?=$faith_facts_explanation?></p>
							</div>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<h3 class="text-center subheadings">
							V.&nbsp;&nbsp;CURRENT ASSETS
						</h3>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;">27</div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							27.<em style="margin-left: 40px;">I own the following parcels of real estate:</em>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>

						<!-- <div class="floatLeft" style="width:3%;"></div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							<div class="multiple_parcels">

									<?php
if (gettype($parcels) == 'object' || gettype($parcels) == 'array') {
	foreach ($parcels['address'] as $key => $value) {
		?>
								<div style="border: 1px solid #f0f0f0;padding: 10px;margin: 10px 0;">

									<div class="mt-3">
										Address: <span><?=$value?></span>
									</div>

									<div class="mt-3">
										Owners:<sup>7</sup>
										<?php
foreach ($parcels['name'][$key] as $k => $name) {
			?>
										<span><?=$name?></span>&nbsp;&nbsp;
										<?php
}
		?>
									</div>

									<div class="mt-3">
										Fair market value: $ <span><?=$parcels['value'][$key]?></span>
									</div>

									<div class="mt-3">
										Total balance of mortgages and other liens.	<?php
if (isset($parcels['balance'][$key])) {
			foreach ($parcels['balance'][$key] as $k => $bal) {
				?>
										<span><?=$bal?></span>&nbsp;&nbsp;
										<?php
}
		}
		?>
									</div>
								</div>
									<?php
}
}
?>
							</div>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="supDiv2" style="bottom:90px">
							<p style="line-height:6px;margin: 0;">
								<sup>7</sup> List by name all owners of record (self and spouse, for example)
							</p>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;">28</div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							28.<em style="margin-left: 40px;">I own the following motor vehicles:</em>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;"></div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							<div style="margin:10px 0;" class="motor_vehicles">
								<?php

if (gettype($motor_vehicle_payments) == 'object' || gettype($motor_vehicle_payments) == 'array') {
	foreach ($motor_vehicle_payments['make_model'] as $key => $value) {
		?>
									<div class="motor" style="border: 1px solid #f0f0f0;padding: 10px;margin: 10px 0;height:80px;">
										<div class="mt-3" style="height:20px">
											Make and model: <span><?=$value?></span>
										</div>

										<div class="mt-3" style="height:20px">
												Fair market value:
													<span>$<?=$motor_vehicle_payments['fair_market'][$key]?></span>
										</div>

										<div class="mt-3">
												Total balance of Vehicle loans And other liens:
													<span>$<?=$motor_vehicle_payments['balance'][$key]?></span>
										</div>
									</div>
								<?php
}
}
?>
							</div>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;">29</div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							29.<em style="margin-left: 40px;">I hold a total of $ <span><?=$retirement_amount?></span> in retirement assets, held in 401k, IRA and similar retirement accounts.</em>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;">30</div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							30.<em style="margin-left: 40px;">I own the following interests in a corporation, limited liability company, partnership, or other entity:</em>
						</div>
						<div class="floatClear"></div>

						<?php if (count($entities['name']) > 0) {
	?>
						<!-- <div class="floatLeft" style="width:3%;"></div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							<table class="table table-bordered table-bordered nostyle" width="100%">
								<thead>
									<tr>
									<th>Name Of Entity</th>
									<th>State Incorporated<sup>8</sup></th>
									<th>Type & Percentage Interest<sup>9</sup></th>
									</tr>
								</thead>
								<tbody class="entity_table">
									<?php
foreach ($entities['name'] as $key => $value) {
		?>
										<tr>
											<td>
												<span><?=$value?></span>
											</td>
											<td>
												<span><?=$entities['state'][$key]?></span>
											</td>
											<td>
												<span><?=$entities['type'][$key]?></span>
											</td>
										</tr>
									<?php
}
	?>
								</tbody>
							</table>
						</div>
					<?php }?>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="supDiv2 noTopBorder">
							<p style="line-height:15px;margin: 0;">
								<sup>8</sup> The state, if any, in which the entity is incorporated. Partnerships, joint ventures and some other business entities might not be incorporated.
								<br>
								<sup>9</sup> For example, shares, membership interest, partnership interest
							</p>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;">31</div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							31.<em style="margin-left: 40px;">I currently am anticipating receiving a tax refund totaling $ <span><?=$tax_refund?></span>.</em>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td>

						<h3 class="text-center subheadings">
							VI.&nbsp;&nbsp;ADDITIONAL CIRCUMSTANCES
						</h3>
					</td>
				</tr>
				<tr>
					<td>
						<!-- <div class="floatLeft" style="width:3%;">32</div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							32.<em style="margin-left: 40px;">I submit the following circumstances as additional support for my effort to discharge my student loans as an “undue hardship” under 11 U.S.C. §523(a)(8):</em>
						</div>
						<div class="floatClear"></div>

						<!-- <div class="floatLeft" style="width:3%;"></div> -->
						<div class="floatRight" style="width:95%; margin-left:10px;">
							<div class="mt-3">
								<p style="width: 100%;border:0;outline: 0;font-family: monospace;font-size: 13px;line-height: 15px;"><?=$additional_circumstances_explanation?></p>
							</div>

							<div class="mt-3">
								Pursuant to 28 U.S.C.§1746, I declare under penalty of perjury that the foregoing is true and correct.
							</div>
						</div>
						<div class="floatClear"></div>
					</td>
				</tr>
				<tr>
					<td class="text-right" style="text-align:right;">
						<div style="margin-bottom: 10px;">
							<div style="border-bottom: 1px solid #000; width: 120px;height: 30px; margin-left: auto;">
								<span style="border-bottom: 0;"><?=isset($signature) && !empty($signature) ? $signature : ''?></span>
							</div>
							<label>Signature</label>
						</div>
						<div style="margin-bottom: 10px;">
							<div style="border-bottom: 1px solid #000; width: 120px;height: 30px; margin-left: auto;">
								<span style="border-bottom: 0;"><?=isset($sign_name) && !empty($sign_name) ? $sign_name : ''?></span>
							</div>
							<label>Name</label>
						</div>
						<div style="margin-bottom: 10px;">
							<div style="border-bottom: 1px solid #000; width: 120px;height: 30px; margin-left: auto;">
								<span style="border-bottom: 0;"><?=isset($sign_date) && !empty($sign_date) ? $sign_date : ''?></span>
							</div>
							<label>Date</label>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</body>
</html>