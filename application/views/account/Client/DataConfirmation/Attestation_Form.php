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
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Data Confirmation for Attestation</title>
	<link rel="stylesheet" href="<?php echo base_url('assets/crm/bootstrap/css/bootstrap.min.css'); ?>">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
	<style>
		@font-face {
		  font-family:times-new-roman;
		  src: url(<?php echo base_url('assets/font/times-new-roman.ttf'); ?>);
		}
		.container {
		    max-width: 1200px;
		    margin: auto;
		}
		body{
			font-family: times-new-roman;
			font-size: 16px;
			line-height: 24px;
		}
		table:not(.nostyle) {
		    width: 100%;
		}
		table:not(.nostyle)>thead {
		    padding: 20px 0px;
		    display: inline-block;
		}
		table:not(.nostyle)>td {
		    padding: 10px 0px;
		    display: inline-block;
		    font-family: times-new-roman;
		    font-size: 16px;
			line-height: 24px;
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
		    font-size: 16px;
			line-height: 24px;
		}
		label{
		    display: inline-block;
		    margin: 10px 0px;
		    font-family: times-new-roman;
		    font-size: 16px;
			line-height: 24px;
		}
		li {
		    padding: 15px 0;
		    font-family: times-new-roman;
		    font-size: 16px;
			line-height: 24px;
		}
		input.border-none {
		    border: 0;
		}
		p{
			font-family: times-new-roman;
			font-size: 16px;
			line-height: 24px;
		}
		h3 {
		    font-weight: normal;
		    text-decoration: underline;
		    font-family: times-new-roman;
			font-size: 16px;
			line-height: 24px;
		}
		.underline{
			text-decoration: underline;
		}
		.bold{font-weight: bold;}
		.mt-5{margin-top: 20px}
		.mt-3{margin-top: 10px}
		li li {
		    list-style: lower-roman;
		    float: left;
    		width: 100%;
		}

		.multiple_loans{
			width: 100%;
    		max-width: 1100px;
		}

		.multiple_loans input{
			width: 100px;
		}
		.subheadings:before {
		    content: attr(data-num);
		    display: inline-block;
		}
	</style>

</head>
<body>
	<form action="" method="post" enctype="multipart/form-data">
	<div style="position:fixed; width:100%;background:#F8F8F8; z-index:9999;padding: 10px 0;border-bottom: 1px solid #f0f0f0;">
		<div class="container">
			<div class="row text-center">
			    <h2 style="margin:5px 0 0 0px;"><strong>Data Confirmation for Attestation</strong></h2>
			</div>
			<hr style="margin-bottom:10px">
			<div class="row">
				<div class="text-center">
					<?php
if ($nslds_file_upload_status == "Uploaded") {
	?>
					<a href="javascript:void(0)" class="btn btn-primary" onClick="view_nslds_snapshot_body('<?php	echo base_url("account/view_nslds_snapshot/" . $client_id) ?>', 'nslds_snapshot_body')">NSLDS Snapshot</a> &nbsp;
					<?php
}

if ($cpp['step_completed_date'] == '' && $car['status'] != 'Approved') {
	?>
			    	<button type="submit" name="Submit_save" class="btn btn-success"><i class="fa fa-floppy-o" aria-hidden="true"></i> Save</button> &nbsp;
				    <button type="submit" name="Submit_approve" class="btn btn-primary"><i class="fa fa-check-square-o" aria-hidden="true"></i> Approve</button>
				    <?php
} else {
	if (isset($type) && $type == 'edit') {
		?>
		<a href="<?=base_url($this->uri->segment(1) . '/customer/add_program/' . $client_id)?>" class="btn btn-warning">< Back</a> &nbsp;
			    	<button type="submit" name="Submit_save" class="btn btn-success"><i class="fa fa-floppy-o" aria-hidden="true"></i> Update</button> &nbsp;
			    	<?php
if ($car['status'] != 'Approved') {
			?>
				    <button type="submit" name="Submit_approve" class="btn btn-primary"><i class="fa fa-check-square-o" aria-hidden="true"></i> Approve</button>
			    	<?php
}
	} else {
		?>
			    		<strong style="font-size:14px; color:#009900;"><i class="fa fa-check-square-o" aria-hidden="true"></i> Already Approved</strong>
			    	<?php
}
}?>

			    </div>
			</div>
		</div>
	</div>
	<div class="clr"></div>

	<div class="container">
		<div style="margin-top:170px">
			<div><?php	$this->load->view("template/alert.php");?></div>
			<table>
				<thead>
					<tr>
						<th>[Updated January 2023]</th>
					</tr>
				</thead>
				<tbody>
					<tr class="text-center">
						<td>IN THE UNITED STATES BANKRUPTCY COURT</br>
							FOR THE DISTRICT OF <input class="border-none" type="text" size="10" placeholder="Enter District" name="district" value="<?=$district?>"></br>
							<input class="border-none" type="text" value="<?=$local_district_information?>" name="local_district_information" placeholder="Enter Local District Information">
						</td>
					</tr>
					<tr>
						<td class="w-50">
							<label>In re:</label>
							<input type="text" name="debtor" value="<?=$debtor?>" placeholder="Enter Debtor">,<br>
							<label>Debtors.</label>
						</td>
						<td class="w-50">
							<label>Case No.</label>
							<input type="text" name="caseno" value="<?=$caseno?>">
							<br>
							<label>Chapter</label>
							<input type="text" name="chapter" value="<?=$chapter?>">
						</td>
					</tr>
					<tr>
						<td class="w-50">
							<input type="text" name="debtor_plaintiff" value="<?=$debtor_plaintiff?>" placeholder="Enter Debtor">,<br>
							<label>
								Plaintiff,
							</label>
						</td>
						<td class="w-50">
							<label>Adversary Pro</label>
							<input type="text" name="adversary_pro" value="<?=$adversary_pro?>">
						</td>
					</tr>
					<tr>
						<td class="w-50">
							<label>
								v.
							</label>
						</td>
					</tr>
					<tr>
						<td class="w-50">
							<label>
								UNITED STATES DEPARTMENT OF EDUCATION,
							</label>
							<input  type="text" placeholder="[et al.]" name="etal" value="<?=$etal?>">,
							<label>
								Defendant<span id="defendants" style="display: none;">s</span>
							</label>
							<!-- <input type="text" name="defendants" value="<?=$defendants?>"> -->
						</td>
					</tr>
					<tr class="text-center">
						<td>
							ATTESTATION OF <input class="bold" type="text" placeholder="Enter Name" value="<?=$attestation_name?>" name="attestation_name">IN SUPPORT<br>
							OF REQUEST FOR STIPULATION CONCEDING <br>
							DISCHARGEABILITY OF STUDENT LOANS
						</td>
					</tr>
					<tr class="text-center">
						<td><i>PLEASE NOTE: This Attestation should be submitted to the Assistant United States Attorney handling the case. It should not be filed with the court unless such a filing is directed by the court or an attorney.</i></td>
					</tr>
					<tr class="mt-5" style="display:block">
						<td>I, <input class="bold" type="text" placeholder="Enter Name" value="<?=$creator_name?>" name="creator_name">, make this Attestation in support of my claim that excepting the student loans described herein from discharge would cause an “undue hardship” to myself and my dependents within the meaning of 11 U.S.C.§523(a)(8). In support of this Attestation, I state the following under penalty of perjury:</td>
					</tr>
					<tr class="mt-5" style="display:block">
						<td>
							<ol>
								<h3 class="text-center subheadings" data-num="I.&nbsp;&nbsp;">
									PERSONAL INFORMATION
								</h3>

								<li>I am over the age of eighteen and am competent to make this Attestation.</li>

								<li>I reside at <input  type="text" value="<?=$address?>" placeholder="Enter Address" name="address">, in <input  type="text" value="<?=$county?>" placeholder="Enter County" name="county">,<input  type="text" value="<?=$state?>" placeholder="Enter State" name="state">.</li>

								<li>My household includes the following persons(including myself):<br>
									<div class="household row" style="width: 70%;">
										<table class="table table-bordered table-striped nostyle" style="width:50%;margin-top: 20px;">
											<thead>
												<tr>
													<th width="40%">Fullname</th>
													<th width="20%">Age</th>
													<th width="40%">Relation</th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td width="40%">
														<input type="text" placeholder="Enter full name" name="user_fullname" value="<?=$user_fullname?>">
													</td>
													<td width="20%">
														<input type="text" placeholder="Enter age" value="<?=$user_age?>" name="user_age">
													</td>
													<td width="40%">
														<input type="text" placeholder="Enter Relation" value="Self" name="user_relation" readonly>
													</td>
													<td>&nbsp;</td>
												</tr>
												<?php
foreach ($household['fullname'] as $key => $value) {
	?>
												<tr>
													<td width="40%">
														<input type="text" placeholder="Enter full name" name="household[fullname][]" value="<?=$value?>">
													</td>
													<td width="20%">
														<input type="text" placeholder="Enter age" value="<?=$household['age'][$key]?>" name="household[age][]">
													</td>
													<td width="40%">
														<input type="text" placeholder="Enter Relation" value="<?=$household['relation'][$key]?>" name="household[relation][]">
													</td>
													<td>
														<a href="javascript:;" class="remove_household"><i class="fa fa-trash"></i></a>
													</td>
												</tr>
												<?php
}
?>
											</tbody>
										</table>
										<a href="javascript:;" style="float: right;" class="btn btn-primary" id="add_member">Add Member</a>
									</div>
								</li>
								<b><i>Questions four through eight request information related to your outstanding student loan debt and your educational history. The Department of Education will furnish this information tothe Assistant United States Attorney (“AUSA”) handling your case, and it should be provided toyou.If you agree that the information provided to you regarding your student loan debt and educational history is accurate, you may simply confirm that you agree, and these questions do not need to be completed. If you have not received the information from Education or the AUSA at thetime you arecompleting this form, or if theinformation is not accurate, you may answer these questions based upon your own knowledge. If you have more than one student loan which you are seeking to discharge in this adversary proceeding, please confirm that the AUSA has complete and accurate information for each loan, or provide that information for each loan.</i></b>

								<li>
									I confirm that the student loan information and educational history provided to me and attached to this Attestation is correct: <br>
									<input class="border" type="radio" value="yes" name="correct_loan_info" <?php if ($correct_loan_info == 'yes') {echo "checked";}?>> Yes
									<input class="border" type="radio" value="no" name="correct_loan_info" <?php if ($correct_loan_info == 'no') {echo "checked";}?> style="margin-left:10px"> No
									<input class="border" type="radio" value="other" name="correct_loan_info" <?php if ($correct_loan_info == 'other') {echo "checked";}?> style="margin-left:10px"> No Information Provided<br>
									<small>[If you answered anything other than “YES”, you must answer questions five through eight].</small><br>

									<div class="mt-5" id="multipleloan" style="<?php if ($correct_loan_info != 'no') {echo 'display: none;';}?>">
										Does client have multiple loans (not just two parts of a consolidation loan)?<br>

										<input class="border" type="radio" name="multiple_loan" value="yes" <?php if ($multiple_loan == 'yes') {echo "checked";}?>> Yes
										<input class="border" type="radio" name="multiple_loan" style="margin-left: 10px;" value="no" <?php if ($multiple_loan == 'no') {echo "checked";}?>> No
									</div>
								</li>
								<li>
									The outstanding balance of the student loan[s] I am seeking to discharge in this adversary proceeding is $<input type="text" placeholder="Loan Amount" value="<?=$sl_loan_amount?>" name="sl_loan_amount">.
								</li>

								<li>
									The current monthly payment on such loan[s] is $<input type="text" placeholder="Monthly Payment" value="<?=$sl_monthly_payment?>" name="sl_monthly_payment">. The loan[s] are scheduled to be repaid in <input type="date" placeholder="Date Of Payoff" value="<?=$sl_date_of_payoff?>" name="sl_date_of_payoff"> [OR] <input class="border" type="checkbox" name="student_loan_default" <?php if (isset($student_loan_default) && $student_loan_default == 'on') {echo 'checked';}?>> My student loan[s] went into default in <input type="date" placeholder="Date Of Default" value="<?=$sl_date_of_default?>" name="sl_date_of_default">.
								</li>

								<li>
									I incurred the student loan[s] I am seeking to discharge while attending <input type="text" placeholder="School Attended" value="<?=$sl_school_attended?>" name="sl_school_attended"> , where I was pursuing a <input type="text" placeholder="Degree Pursued" value="<?=$sl_degree_pursued?>" name="sl_degree_pursued"> degree with a specialization in <input type="text" placeholder="Specialization" value="<?=$sl_specialization?>" name="sl_specialization"> .
								</li>

								<li>
									In <input type="date" placeholder="Date Of School Completed" value="<?=$sl_date_school_completed?>" name="sl_date_school_completed">, I completed my course of study and received a <input type="text" name="sl_type_of_degree" placeholder="Type Of Degree" value="<?=$sl_type_of_degree?>"> degree [OR] In <input type="text" placeholder="Date Studies Ceased" value="<?=$sl_date_studies_ceased?>" name="sl_date_studies_ceased">, I left my course of study and did not receive a degree.
								</li>

							<div class="multiple_loans table-responsive row" style="width:100%;<?php if ($multiple_loan != 'yes') {echo 'display:none';}?>">
									<table class="table table-bordered table-striped nostyle" style="margin-top: 20px;width: 100%;">
										<thead>
											<tr>
												<th>Loan Name/Identifier</th>
												<th>Loan Type</th>
												<th>#5 Loan Amount</th>
												<th>#6 Monthly Payment</th>
												<th>#6 Date Of Payoff</th>
												<th>#6 Date Of Default</th>
												<th>#7 School Attended</th>
												<th>#7 Degree Pursued</th>
												<th>#7 Specialization</th>
												<th>#8 Date School Completed</th>
												<th>#8 Type Of Degree</th>
												<th>#8 Date Left School w/o Degree</th>
											</tr>
										</thead>
										<tbody>
											<?php
foreach ($multiple_loans['loan_name'] as $key => $value) {
	?>
											<tr>
												<td>
													<input type="text" name="multiple_loans[loan_name][]" value="<?=$value?>">
												</td>
												<td>
													<input type="text" value="<?=$multiple_loans['loan_type'][$key]?>" name="multiple_loans[loan_type][]">
												</td>
												<td>
													<input type="text" value="<?=$multiple_loans['loan_amount'][$key]?>" name="multiple_loans[loan_amount][]">
												</td>
												<td>
													<input type="text" value="<?=$multiple_loans['monthly_payment'][$key]?>" name="multiple_loans[monthly_payment][]">
												</td>
												<td>
													<input type="date" value="<?=$multiple_loans['date_of_payoff'][$key]?>" name="multiple_loans[date_of_payoff][]">
												</td>
												<td>
													<input type="date" value="<?=$multiple_loans['date_of_default'][$key]?>" name="multiple_loans[date_of_default][]">
												</td>
												<td>
													<input type="text" value="<?=$multiple_loans['school_attended'][$key]?>" name="multiple_loans[school_attended][]">
												</td>
												<td>
													<input type="text" value="<?=$multiple_loans['degree_pursued'][$key]?>" name="multiple_loans[degree_pursued][]">
												</td>
												<td>
													<input type="text" value="<?=$multiple_loans['specialization'][$key]?>" name="multiple_loans[specialization][]">
												</td>
												<td>
													<input type="date" value="<?=$multiple_loans['date_school_completed'][$key]?>" name="multiple_loans[date_school_completed][]">
												</td>
												<td>
													<input type="text" value="<?=$multiple_loans['type_of_degree'][$key]?>" name="multiple_loans[type_of_degree][]">
												</td>
												<td>
													<input type="date" value="<?=$multiple_loans['date_studies_ceased'][$key]?>" name="multiple_loans[date_studies_ceased][]">
												</td>
												<td>
													<a href="javascript:;" class="remove_loan"><i class="fa fa-trash"></i></a>
												</td>
											</tr>
											<?php
}
?>
										</tbody>
									</table>
								</div>
								<a href="javascript:;" style="float: right;<?php if ($multiple_loan != 'yes') {echo 'display:none';}?>" class="btn btn-primary" id="add_loan">Add Loan</a>
								<li class="mt-5">
									I am currently employed as a <input type="text" placeholder="Job Title" value="<?=$job_title?>" name="job_title"> .My employer’s name and address is <input type="text" placeholder="Employer Name and Address" name="employer_name_and_address" value="<?=$employer_name_and_address?>"> [OR] <input class="border" type="checkbox" name="not_employed" <?php if (isset($not_employed) && $not_employed == 'on') {echo 'checked';}?>> I am not currently employed.
								</li>

								<h3 class="text-center subheadings" data-num="II.&nbsp;&nbsp;">
									CURRENT INCOME AND EXPENSES
								</h3>

								<li>
									I do not have the ability to make payments on my student loans while maintaining a minimal standard of living for myself and my household. I submit the following information to demonstrate this:
								</li>

								<h4><b><i class="subheadings" data-num="A.&nbsp;&nbsp;" style="text-decoration:underline;">Household Gross Income</i></b></h4>

								<li>My current monthly household gross income from all sources is $ <input type="text" value="<?=$gross_income?>" name="gross_income" placeholder="Enter Gross Income"> <sup>1</sup>. This amount includes the following monthly amounts:<br>
									<div class="mt-5">
										<div>
											<input style="width: 30%;" type="text" placeholder="Gross Employment" value="<?=$gross_employment?>" name="gross_employment">  my gross income from employment (if any)
										</div>
										<div>
											<input style="width: 30%;" type="text" placeholder="Unemployment" name="unemployment_benefits" value="<?=$unemployment_benefits?>">  my unemployment benefits
										</div>
										<div>
											<input style="width: 30%;" type="text" placeholder="Social Security" name="social_security" value="<?=$social_security?>">  my Social Security Benefits
										</div>
										<div class="other_incomes" style="padding: 10px 0;">
											<a href="javascript:;" class="btn btn-primary" id="add_income" style="float:right;">Add Other Income</a>
											Other Incomes (If any):
											<?php
foreach ($other_incomes['income_value'] as $key => $value) {
	?>
											<div>
												<input type="text" placeholder="Other Income" value="<?=$value?>" name="other_incomes[income_value][]"> my <input type="text" placeholder="Describe Income" value="<?=$other_incomes['income_detail'][$key]?>" name="other_incomes[income_detail][]"><a href="javascript:;" class="remove_income"><i class="fa fa-trash"></i></a>
											</div>
											<?php
}
?>
										</div>
										<div>
											<input style="width: 30%;" type="text" placeholder="Other household employment" name="other_household_income" value="<?=$other_household_income?>">  Gross income from employment of other members of household
										</div>
										<div>
											<input style="width: 30%;" type="text" placeholder="Other household unemployment" name="other_household_unemployment" value="<?=$other_household_unemployment?>">  Unemployment benefits received by other members of household
										</div>
										<div>
											<input style="width: 30%;" type="text" placeholder="Other household Social Security" name="other_household_social_security" value="<?=$other_household_social_security?>">  Social Security benefits received by other members of household
										</div>
										<div>
											<input style="width: 30%;" type="text" placeholder="Other household other income" name="other_household_other_income" value="<?=$other_household_other_income?>">  Other income from any source received by other members of household
										</div>
									</div>
								</li>
								<p><sup>1</sup>“Gross income” means your income before any payroll deductions (for taxes, Social Security, health insurance, etc.) or deductions from other sources of income. You may have included information about your gross income on documents previously filed in your bankruptcy case , including Form B 106I, Schedule I - Your Income (Schedule I). If you filed your Schedule I within the past 18 months and the income information on those documents has not changed, you may refer to that document for the income information provided here. If you filed Schedule I more than 18 months prior to this Attestation, or your income has changed, you should provide your new income information</p>

								<li>The current monthly household gross income stated above(select which applies):</li>

								<p>Will client be submitting tax returns, 2-months of pay stubs, or other alternative documents of income? <br>Tax return(s) <input class="border" type="radio" value="tax_returns" <?php if ($income_proof_submitted == 'tax_returns') {echo "checked";}?> name="income_proof_submitted"> , Pay stubs <input class="border" type="radio" value="pay_stubs" <?php if ($income_proof_submitted == 'pay_stubs') {echo "checked";}?> name="income_proof_submitted"> , Alternative documentation <input class="border" type="radio" name="income_proof_submitted" value="alternative_documentation" <?php if ($income_proof_submitted == 'alternative_documentation') {echo "checked";}?>></p>

								<p><input class="border" type="radio" value="tax_returns" name="income_proof_submitted1" <?php if ($income_proof_submitted1 == 'tax_returns') {echo "checked";}?>> Includes a monthly average of the gross income shown on the most recent tax return[s] filed for myself and other members of my household, which are attached, and <br>the amounts stated on such tax returns have not changed materially since the tax year of such returns; OR</p>

								<p><input class="border" type="radio" value="pay_stubs" name="income_proof_submitted1" <?php if ($income_proof_submitted1 == 'pay_stubs') {echo "checked";}?>> Represents an average amount calculated from the most recent two months of gross income stated on four (4) consecutive paystubs from my current employment, which are attached; OR</p>

								<p><input class="border" type="radio" name="income_proof_submitted1" value="alternative_documentation" <?php if ($income_proof_submitted1 == 'alternative_documentation') {echo "checked";}?>> My current monthly household gross income is not accurately reflected on either <br>
								recent tax returns or pay stubs from current employment, and I have submitted instead the following documents verifying current gross household income from employment of household members: <br>
								<textarea placeholder="Alternative document titles" name="alternative_document_titles" rows="3" maxlength="2000" style="width: 100%" <?php if ($income_proof_submitted1 != 'alternative_documentation') {echo "disabled";}?>> <?=$alternative_document_titles?></textarea></p>

								<li>In addition, I have submitted <br>
									<textarea placeholder="Additional document titles" name="additional_document_titles" rows="3" maxlength="2000" style="width: 100%"><?=$additional_document_titles?></textarea>
									<br>
									verifying the sources of income other than income from employment, as such income is not shown on [most recent tax return[s] or paystubs].</li>


								<h4><b><i class="subheadings" data-num="B.&nbsp;&nbsp;" style="text-decoration:underline;">Monthly Expenses</i></b></h4>

								<li>My current monthly household expenses do/do not exceed the amounts listed below based on the number of people in my household for the following categories:

									<p>(a) Living Expenses<sup>2</sup> </p>

									<ul>
										<li>
											<div class="w-50">
												My expenses for food<br>
												$431(oneperson)<br>
												$779(two persons)<br>
												$903(three persons)<br>
												$1028(four persons)<br>
											</div>
											<div class="w-50">
												Do exceed <input type="radio" name="food_expense" value="yes" <?php if ($food_expense == 'yes') {echo "checked";}?> style="margin-right: 10px;">Do not exceed <input type="radio" name="food_expense" value="no" <?php if ($food_expense == 'no') {echo "checked";}?> style="margin-right: 10px;">
											</div>
										</li>
										<li>
											<div class="w-50">
												My expenses for housekeeping supplies<br>
												$40 (one person)<br>
												$82(two persons)<br>
												$74(three persons)<br>
												$85(fourpersons)<br>
											</div>
											<div class="w-50">
												Do exceed <input type="radio" name="housekeeping_expense" value="yes" <?php if ($housekeeping_expense == 'yes') {echo "checked";}?> style="margin-right: 10px;">Do not exceed <input type="radio" name="housekeeping_expense" value="no" <?php if ($housekeeping_expense == 'no') {echo "checked";}?> style="margin-right: 10px;">
											</div>
										</li>
										<li>
											<div class="w-50">
												My expenses for apparel & services<br>
												$99 (one person)<br>
												$161(twopersons)<br>
												$206(three persons)<br>
												$279(fourpersons)<br>
											</div>
											<div class="w-50">
												Do exceed <input type="radio" name="apparel_expense" value="yes" <?php if ($apparel_expense == 'yes') {echo "checked";}?> style="margin-right: 10px;">Do not exceed <input type="radio" name="apparel_expense" value="no" <?php if ($apparel_expense == 'no') {echo "checked";}?> style="margin-right: 10px;">
											</div>
										</li>
										<li>
											<div class="w-50">
												My expenses for (non-medical) personal care products and services<br>
												$45(oneperson)<br>
												$82(two persons)<br>
												$78(three persons)<br>
												$96(four persons)<br>
											</div>
											<div class="w-50">
												Do exceed <input type="radio" name="nonmedical_expense" value="yes" <?php if ($nonmedical_expense == 'yes') {echo "checked";}?> style="margin-right: 10px;">Do not exceed <input type="radio" name="nonmedical_expense" value="no" <?php if ($nonmedical_expense == 'no') {echo "checked";}?> style="margin-right: 10px;">
											</div>
										</li>
										<li>
											<div class="w-50">
												My miscellaneous expenses(not included elsewhere on this Attestation):<br>
												$170(oneperson)<br>
												$306(two persons)<br>
												$349(three persons)<br>
												$412(four persons)<br>
											</div>
											<div class="w-50">
												Do exceed <input type="radio" name="misc_expense" value="yes" <?php if ($misc_expense == 'yes') {echo "checked";}?> style="margin-right: 10px;">Do not exceed <input type="radio" name="misc_expense" value="no" <?php if ($misc_expense == 'no') {echo "checked";}?> style="margin-right: 10px;">
											</div>
										</li>

										<li>
											<div class="w-50">
												My total expenses in these categories<br>
												$785 (one person)<br>
												$1410 (two persons)<br>
												$1610 (three persons)<br>
												$1900 (four persons)<br>
												Add $344 per each additional member if more than four in household.<br>
											</div>
											<div class="w-50">
												Do exceed <input type="radio" name="total_expense" value="yes" <?php if ($total_expense == 'yes') {echo "checked";}?> style="margin-right: 10px;">Do not exceed <input type="radio" name="total_expense" value="no" <?php if ($total_expense == 'no') {echo "checked";}?> style="margin-right: 10px;">
											</div>
										</li>

									<p>
										<sup>2</sup>The living expenses listed in Question 14 and 15 have been adopted from the Internal Revenue Service Collection Financial Standards “National Standards” and “Local Standards” for the year in which this form is issued. This form is updated annually to reflect changes to these expenses.
									</p>

									<!-- <p>If you answered that your total expenses for any of the categories (i) through (v) exceed the applicable amount listed in those categories, and you would like the AUSA to consider your additional expenses for any such categories as necessary, you may list the total expenses for any such categories and explain the need for such expenses here. (You do not need to provide any additional information if you answered that your total expenses did not exceed the applicable amount listed in the subsection (vi)).<br>
											<p><input type="text" placeholder="Local Standards vs National Standards" name="local_national" value="<?=$local_national?>" style="width:50%"/></p>
											<textarea placeholder="Explanation of expenses greater than amounts listed in 14(a)" name="greater_expense_explanation" rows="5" style="width: 70%;"><?=$greater_expense_explanation?></textarea>
										</p> -->
									</ul>

									<p>(b) Uninsured medical costs</p>

									<p>
										<div style="display:flex;">
											<div class="w-50">
												My uninsured, out of pocket medical costs<br>
												<div class="mt-3">
													$75(per household member under 65)<br>
													$153(per household member 65 or older)<br>
												</div>
											</div>
											<div class="w-50">
												Do exceed <input type="radio" name="incured_medical_costs" value="yes" <?php if ($incured_medical_costs == 'yes') {echo "checked";}?> style="margin-right: 10px;">Do not exceed <input type="radio" name="incured_medical_costs" value="no" <?php if ($incured_medical_costs == 'no') {echo "checked";}?> style="margin-right: 10px;">
											</div>
										</div>
										<div class="mt-3">
											If you answered that your uninsured, out of pocket medical costs exceed the listed amounts for any household member, and you would like the AUSA to consider your additional expenses as necessary, you may list the household member’s total expenses and explain the need for such expenses here.

											<textarea placeholder="Explanation of expenses greater than amounts listed in 14(b)" name="greater_medical_explanation" rows="5" maxlength="2000" style="width: 100%;"><?=$greater_medical_explanation?></textarea>
										</div><br>
										<div class="mt-5">
											[If you filed a Form 122A-2 Chapter 7 Means Test or 122C-2 Calculation of Disposable Income in your bankruptcy case, you may refer to lines 6 and 7 of those forms for information.]<sup>3</sup>
										</div>
									</p>

								</li>
								<li>
									My current monthly household expenses in the following categories are as follows:

									<p>(a) Payroll Deductions</p>

									<ul>
										<li>
											<div class="w-50">
												Taxes, Medicare and Social Security
											</div>
											<div class="w-50">
												$<input type="text" placeholder="tax/social security" name="tax_deduction" value="<?=$tax_deduction?>">
											</div>
											<div class="w-100">[You may refer to line 16 of the Means Test or Schedule I, line 5a]</div>
										</li>

										<li>
											<div class="w-50">
												Contributions to retirement accounts
											</div>
											<div class="w-50">
												$<input type="text" value="<?=$retirement_cont?>" name="retirement_cont" placeholder="retirement contribution">
											</div>
											<div class="w-100">[You may refer to line 17 of the Means Test or Schedule I, line 5b and c]</div>
											<div class="w-50 mt-3">
												Are these contributions required<br>
												as a condition of your employment?
											</div>
											<div class="w-50 mt-3">
												YES <input type="radio" name="contribution_required" value="yes" <?php if ($contribution_required == 'yes') {echo "checked";}?>>/ NO <input type="radio" name="contribution_required" value="no" <?php if ($contribution_required == 'no') {echo "checked";}?>>
											</div>
										</li>

										<li>
											<div class="w-50">
												Union dues
											</div>
											<div class="w-50">
												$<input type="text" value="<?=$union_dues?>" name="union_dues" placeholder="Union Dues">
											</div>
											<div class="w-100">
												[You may refer to line 17 of the Means Test or Schedule I, line 5g]
											</div>
										</li>

										<li>
											<div class="w-50">
												Life insurance
											</div>
											<div class="w-50">
												$<input type="text" value="<?=$life_insurance?>" name="life_insurance" placeholder="Life Insurance">
											</div>
											<div class="w-100">
												[You may refer to line 18 of the Means Test or Schedule I, line 5e]
											</div>
											<div class="w-50 mt-3">
												Are the payments for a term policy<br>
												covering your life?
											</div>
											<div class="w-50 mt-3">
												YES <input type="radio" name="term_policy_covering" value="yes" <?php if ($term_policy_covering == 'yes') {echo "checked";}?>>/ NO <input type="radio" name="term_policy_covering" value="no" <?php if ($term_policy_covering == 'no') {echo "checked";}?>>
											</div>
										</li>

										<li>
											<div class="w-50">
												Court-ordered alimony and child support
											</div>
											<div class="w-50">
												$<input type="text" value="<?=$divorce_support?>" name="divorce_support" placeholder="Divorce Support">
											</div>
											<div class="w-100">
												[You may refer to line 19 of the Means Test or Schedule I, line 5f]
											</div>
										</li>

										<li>
											<div class="w-50">
												Health insurance
											</div>
											<div class="w-50">
												$<input type="text" value="<?=$health_insurance?>" name="health_insurance" placeholder="Health Insurance">
											</div>
											<div class="w-100">
												[You may refer to line 25 of the Means Test or Schedule I, line 5e]
											</div>
											<div class="w-50 mt-3">
												Does the policy cover any persons other than<br>
												yourself and your family members?
											</div>
											<div class="w-50 mt-3">
												YES <input type="radio" name="other_person_policy" value="yes" <?php if ($other_person_policy == 'yes') {echo "checked";}?>>/ NO <input type="radio" name="other_person_policy" value="no" <?php if ($other_person_policy == 'no') {echo "checked";}?>>
											</div>
										</li>

										<li>
											<div class="other_deductions">
												Other payroll deductions<br>
												<?php

foreach ($other_deductions['deduction'] as $key => $value) {
	?>
												<div class="one_deduct">
													<div class="w-50">
														<input type="text" value="<?=$value?>" name="other_deductions[deduction][]" placeholder="Other Deductions">
													</div>
													<div class="w-50">
														$<input type="text" value="<?=$other_deductions['amount'][$key]?>" name="other_deductions[amount][]" placeholder="Amount">
													<a href="javascript:;" class="remove_deduction"><i class="fa fa-trash"></i></a>
													</div>
												</div>
												<?php
}
?>
											</div>
											<a href="javascript:;" class="btn btn-primary" id="add_deduction" style="float:right;">Add Deduction</a>
										</li>
									</ul>

									<p> <sup>3</sup>Forms 122A-2 and 122C-2 are referred to collectively here as the “Means Test.” If you filed a Means Test in your bankruptcy case, you may refer to it for information requested here and in other expense categories below.If you did not file a Means Test,you may refer to your Schedule I and Form 106J – Your Expenses (Schedule J) in the bankruptcy case, which may also list information relevant to these categories. You should only use information from these documents if your expenses have not changed since you filed them.</p>

									<p>(b) Housing Costs<sup>4</sup></p>

									<ul>
										<li>
											<div class="w-50">
												Mortgage or rent payments
											</div>
											<div class="w-50">
												$<input type="text" value="<?=$mortgage_rent?>" name="mortgage_rent" placeholder="Mortgage/Rent">
											</div>
										</li>
										<li>
											<div class="w-50">
												Property taxes(if paid separately)
											</div>
											<div class="w-50">
												$<input type="text" value="<?=$property_tax?>" name="property_tax" placeholder="Property Tax">
											</div>
										</li>
										<li>
											<div class="w-50">
												Home owners or renters insurance<br>
												(if paid separately)
											</div>
											<div class="w-50">
												$<input type="text" value="<?=$renters_insurance?>" name="renters_insurance" placeholder="Insurance">
											</div>
										</li>
										<li>
											<div class="w-50">
												Home maintenance and repair<br>
		 										(average last 12 months’ amounts)
											</div>
											<div class="w-50">
												$<input type="text" value="<?=$home_maintenance?>" name="home_maintenance" placeholder="Home maintenance">
											</div>
										</li>
										<li>
											<div class="w-50">
												Utilities (include monthly gas, electric water, heating oil, garbage collection, residential telephone service, cellphone service,cabletelevision, and internet service)
											</div>
											<div class="w-50">
												$<input type="text" value="<?=$utilities?>" name="utilities" placeholder="Utilities">
											</div>
										</li>
									</ul>

									<p><sup>4</sup>You should list the expenses you actually pay in Housing Costs and Transportation Costs categories. If these expenses have not changed since you filed your Schedule J, you may refer to the expenses listed there, including housing expenses(generally onlines 4 through 6 of Schedule J) and transportation expenses (generally on lines 12, 15c and 17).</p>

										<a href="javascript:;" class="btn btn-primary" style="float:right" id="add_vehicle">Add Vehicle</a>
									<p>(c) Transportation Costs</p>

									<ul>
										<div class="multiple_vehicles">
											<?php

if (gettype($vehicle_payments) == 'object' || gettype($vehicle_payments) == 'array') {
	foreach ($vehicle_payments['payment'] as $key => $value) {
		?>
											<div style="display:flex;border: 1px solid #f0f0f0;padding: 10px;margin: 10px 0;" class="vehicle">
												<ul>
													<li>
														<div class="w-50">
															Vehicle payments(itemize per vehicle)
														</div>
														<div class="w-50">
															$<input type="text" class="vehicle_<?=$key + 1?>" value="<?=$value?>" name="vehicle_payments[payment][]" placeholder="Vehicle Payments">
														</div>
													</li>
													<li>
														<div class="w-50">
															Monthly average costs of operating vehicles (including gas, routine maintenance, monthly insurance cost)
														</div>
														<div class="w-50">
															$<input type="text" class="vehicle_<?=$key + 1?>" value="<?=$vehicle_payments['operating_costs'][$key]?>" name="vehicle_payments[operating_costs][]" placeholder="Operating Costs">
														</div>
													</li>
												</ul>
												<a href="javascript:;" class="remove_vehicle" id="vehicle_<?=$key + 1?>" style="float:right;<?php if ($key == 0) {echo "";}?>"><i class="fa fa-trash"></i></a>
											</div>
											<?php
}
} else {
	?>
											<div style="display:flex;border: 1px solid #f0f0f0;padding: 10px;margin: 10px 0;" class="vehicle">
												<ul>
													<li>
														<div class="w-50">
															Vehicle payments(itemize per vehicle)
														</div>
														<div class="w-50">
															$<input type="text" class="vehicle_1" value="<?=$vehicle_payments['payment'][$key]?>" name="vehicle_payments[payment][]" placeholder="Vehicle Payments">
														</div>
													</li>
													<li>
														<div class="w-50">
															Monthly average costs of operating vehicles (including gas, routine maintenance, monthly insurance cost)
														</div>
														<div class="w-50">
															$<input type="text" class="vehicle_1" value="<?=$vehicle_payments['operating_costs'][$key]?>" name="vehicle_payments[operating_costs][]" placeholder="Operating Costs">
														</div>
													</li>
												</ul>
												<a href="javascript:;" class="remove_vehicle" id="vehicle_<?=$key + 1?>" style="float:right;<?php if ($key == 0) {echo "";}?>"><i class="fa fa-trash"></i></a>
											</div>
	<?php
}
?>
										</div>


										<li>
											<div class="w-50">
												Public transportation costs
											</div>
											<div class="w-50">
												$<input type="text" value="<?=$public_transportation?>" name="public_transportation" placeholder="Public Transportation">
											</div>
										</li>
									</ul>

									<p>(d) Other Necessary Expenses</p>

									<ul>
										<li>
											<div class="w-50">
												Court-ordered alimony and child support payments <br>
												(if not deducted from pay)
											</div>
											<div class="w-50">
												$<input type="text" value="<?=$other_divorce_support?>" name="other_divorce_support" placeholder="Divorce Support">
											</div>
											<div class="w-100">
												[You may refer to line 19 of Form 122A-2 or 122C-2 or Schedule J, line 18]
											</div>
										</li>
										<li>
											<div class="w-50">
												Babysitting, day care, nursery and preschool costs
											</div>
											<div class="w-50">
												$<input type="text" value="<?=$child_care_costs?>" name="child_care_costs" placeholder="Child Care Costs">
											</div>
											<div class="w-100">
												[You may refer to line 21 of Form 122A-2 or 122C-2 or Schedule J, line 8]<sup>5</sup>
											</div>
											<div class="w-100">
												Explain the circumstances making it necessary for you to expend this amount:
											</div>
											<div class="w-100">
												<textarea name="child_care_costs_explanation" maxlength="2000" style="width: 100%;" placeholder="Explanation of Child Care Costs" rows="5"><?=$child_care_costs_explanation?></textarea>
											</div>
										</li>
										<li>
											<div class="w-50">
												Health insurance<br>
												(if not deducted from pay)
											</div>
											<div class="w-50">
												$<input type="text" value="<?=$health_insurance_nopay?>" name="health_insurance_nopay" placeholder="Health Insurance">
											</div>
											<div class="w-100">
												[You may refer to line 25 of the Means Test or Schedule J, line 15b]
											</div>
											<div class="w-50">
												Does the policy cover any persons other than yourself and your family members?
											</div>
											<div class="w-50">
												YES <input type="radio" name="other_person_policy_nopay" value="yes" <?php if ($other_person_policy_nopay == 'yes') {echo "checked";}?>>/ NO <input type="radio" name="other_person_policy_nopay" value="no" <?php if ($other_person_policy_nopay == 'no') {echo "checked";}?>>
											</div>
										</li>
										<li>
											<div class="w-50">
												Life insurance<br>
												(if not deducted from pay)
											</div>
											<div class="w-50">
												$<input type="text" value="<?=$life_insurance_nopay?>" name="life_insurance_nopay" placeholder="Life Insurance">
											</div>
											<div class="w-100">
												[You may refer to line 25 of the Means Test or Schedule J, line 15a]
											</div>
											<div class="w-50">
												Are the payments for a term policy covering your life?
											</div>
											<div class="w-50">
												YES <input type="radio" name="term_policy_covering_nopay" value="yes" <?php if ($term_policy_covering_nopay == 'yes') {echo "checked";}?>>/ NO <input type="radio" name="term_policy_covering_nopay" value="no" <?php if ($term_policy_covering_nopay == 'no') {echo "checked";}?>>
											</div>
										</li>

										<p><sup>5</sup>Line 8 of Schedule J allows listing of expenses for “child care and children’s education costs.” You should not list any educational expenses for your children here, aside from necessary nursery or preschool costs</p>

										<li>
											<div class="w-50">
												Dependent care (for elderly or disabled	family members)
											</div>
											<div class="w-50">
												$<input type="text" value="<?=$dependent_care?>" name="dependent_care" placeholder="Dependent Care">
											</div>
											<div class="w-100">
												[You may refer to line 26 of the Means Test or Schedule J, line 19]<br>
												Explain the circumstances making it necessary for you to expend this amount:<br>
												<textarea name="dependent_care_costs_explanation" maxlength="2000" style="width: 100%;" placeholder="Explanation of Dependent Care Costs" rows="5"><?=$dependent_care_costs_explanation?></textarea>
											</div>
										</li>
										<li>
											<div class="w-50">
												Payments on delinquent federal, state or local tax debt
											</div>
											<div class="w-50">
												$<input type="text" value="<?=$delinquent_care?>" name="delinquent_care" placeholder="Delinquent Care">
											</div>
											<div class="w-100">
												[You may refer to line 35 of the Means Test or Schedule J, line 16]
											</div>
											<div class="w-50">
												Are these payments being made pursuant to an agreement with the taxing authority?
											</div>
											<div class="w-50">
												YES <input type="radio" name="taxing_authority" value="yes" <?php if ($taxing_authority == 'yes') {echo "checked";}?>>/ NO <input type="radio" name="taxing_authority" value="no" <?php if ($taxing_authority == 'no') {echo "checked";}?>>
											</div>
										</li>
										<li>
											<div class="w-50">
												Payments on other student loans
											</div>
											<div class="w-50">
												$<input type="text" value="<?=$other_student_loans?>" name="other_student_loans" placeholder="Other Loan Payments">
											</div>
											<div class="w-100">
												I am not seeking to discharge
											</div>
										</li>
										<li>
											<div class="w-50">
												Other expenses I believe necessary for a minimal standard of living.
											</div>
											<div class="w-50">
												$<input type="text" value="<?=$other_expenses_living?>" name="other_expenses_living" placeholder="Other expenses">
											</div>
											<div class="w-100">
												Explain the circumstances making it necessary for you to expend this amount:<br>
												<textarea name="other_expenses_living_explanation" maxlength="2000" style="width: 100%;" placeholder="Explanation of Other Expenses" rows="5"><?=$other_expenses_living_explanation?></textarea>
											</div>
										</li>
									</ul>

								</li>

								<li>
									After deducting the foregoing monthly expenses from my household gross income, I have <input type="text" value="<?=$remaining_income?>" name="remaining_income" placeholder="$ amount"> [no, or amount] remaining income.
								</li>

								<li>
									In addition to the foregoing expenses, I anticipate I will incur additional monthly expenses in the future for my, and my dependents’, basic needs that are currently not met<sup>6</sup>. These include the following:
								</li>

								<h5 class="">
									<textarea name="anticipated_expenses_explanation" maxlength="2000" style="width: 100%;" placeholder="Explanation of Anticipated Expenses" rows="5"><?=$anticipated_expenses_explanation?></textarea>
								</h5>

								<p> <sup>6</sup>If you have forgone expenses for any basic needs and anticipate that you will incur such expenses in the future, you may list them here and explain the circumstances making it necessary for you to incur such expenses</p>

								<h3 class="text-center mt-5 subheadings" data-num="III.&nbsp;&nbsp;">
									FUTURE INABILITY TO REPAY STUDENT LOANS
								</h3>

								<li>
									For the following reasons, it should be presumed that my financial circumstances are unlikely to materially improve over a significant portion of the repayment period (answer all that apply):

									<div class="mt-3">
										<input class="border" type="checkbox" name="presume_repay_inability[]" value="over_age" <?php if (in_array('over_age', $presume_repay_inability)) {echo "checked";}?>> I am over the age of 65.
									</div>

									<div class="mt-3">
										<input class="border" type="checkbox" name="presume_repay_inability[]" value="10_yrs_repay" <?php if (in_array('10_yrs_repay', $presume_repay_inability)) {echo "checked";}?>> The student loans I am seeking to discharge have been in repayment status for at least 10 years (excluding any period during which I was enrolled as a student).
									</div>

									<div class="mt-3">
										<input class="border" type="checkbox" name="presume_repay_inability[]" value="incomplete_education" <?php if (in_array('incomplete_education', $presume_repay_inability)) {echo "checked";}?>> I did not complete the education for which I incurred the student loan[s].
									</div>

									<div class="mt-3">
										Describe how not completing your degree has inhibited your future earning capacity:<br>
										<textarea name="incomplete_degree_explanation" maxlength="2000" style="width: 100%;" placeholder="Explanation of Non-Completion of Degree" rows="5"><?=$incomplete_degree_explanation?></textarea>
									</div>

									<div class="mt-3">
										<input class="border" type="checkbox" name="presume_repay_inability[]" value="disability" <?php if (in_array('disability', $presume_repay_inability)) {echo "checked";}?>> I have a disability or chronic injury impacting my income potential.
									</div>

									<div class="mt-3">
										Describe the disability or injury and its effects on your ability to work,and indicate whether you receive any governmental benefits attributable to this disability or injury:<br>
										<textarea name="disability_explanation" maxlength="2000" style="width: 100%;" placeholder="Explanation of Disability" rows="5"><?=$disability_explanation?></textarea>
									</div>

									<div class="mt-3">
										<input class="border" type="checkbox" name="presume_repay_inability[]" value="unemployed" <?php if (in_array('unemployed', $presume_repay_inability)) {echo "checked";}?>> I have been unemployed for at least five of the past ten years.
									</div>

									<div class="mt-3">
										Please explain your efforts to obtain employment.<br>
										<textarea name="unemployment_explanation" maxlength="2000" style="width: 100%;" placeholder="Explanation of Job Hunting" rows="5"><?=$unemployment_explanation?></textarea>
									</div>
								</li>

								<li>
									For the following additional reasons, my financial circumstances are unlikely to materially improve over a significant portion of the repayment period (answer all that apply):
									<div class="mt-3">
										<input class="border" type="checkbox" name="repay_inability[]" value="close_institution" <?php if (in_array('close_institution', $repay_inability)) {echo "checked";}?>> I incurred student loans I am seeking to discharge in pursuit of a degree from an institution that is now closed.
									</div>

									<div class="mt-3">
										Describe how the school closure inhibited your future earnings capacity:<br>
										<textarea name="school_closure_explanation" maxlength="2000" style="width: 100%;" placeholder="Explanation of School Closure" rows="5"><?=$school_closure_explanation?></textarea>
									</div>

									<div class="mt-3">
										<input class="border" type="checkbox" name="repay_inability[]" value="unemployed" <?php if (in_array('unemployed', $repay_inability)) {echo "checked";}?>> I am not currently employed.
									</div>

									<div class="mt-3">
										<input class="border" type="checkbox" name="repay_inability[]" value="close_institution" <?php if (in_array('close_institution', $repay_inability)) {echo "checked";}?>>I am currently employed, but I am unable to obtain employment in the field for which I am educated or have received specialized training.
									</div>

									<div class="mt-3">
										Describe reasons for inability to obtain such employment, and indicate if you have ever been able to obtain such employment:<br>
										<textarea name="employment_inability_explanation" maxlength="2000" style="width: 100%;" placeholder="Explanation of Inability to obtain particular employment" rows="5"><?=$employment_inability_explanation?></textarea>
									</div>
									<div class="mt-3">
										<input class="border" type="checkbox" name="repay_inability[]" value="insufficient_income" <?php if (in_array('insufficient_income', $repay_inability)) {echo "checked";}?>> I am currently employed, but my income is insufficient to pay my loans and unlikely to increase to an amount necessary to make substantial payments on the student loans I am seeking to discharge.
									</div>

									<div class="mt-3">
										Please explain why you believe this is so:<br>
										<textarea name="insufficient_income_explanation" maxlength="2000" style="width: 100%;" placeholder="Explanation of insufficient income" rows="5"><?=$insufficient_income_explanation?></textarea>
									</div>

									<div class="mt-3">
										<input class="border" type="checkbox" name="repay_inability[]" value="other_circumstances" <?php if (in_array('other_circumstances', $repay_inability)) {echo "checked";}?>> Other circumstances exist making it unlikely I will be able to make payments for a significant part of the repayment period.
									</div>

									<div class="mt-3">
										Explain these circumstances:<br>
										<textarea name="other_circumstances_explanation" maxlength="2000" style="width: 100%;" placeholder="Explanation of Other Circumstances" rows="5"><?=$other_circumstances_explanation?></textarea>
									</div>

								</li>

								<h3 class="text-center subheadings" data-num="IV.&nbsp;&nbsp;">
									PRIOR EFFORTS TO REPAY LOANS
								</h3>

								<li>
									I have made good faith efforts to repay the student loans at issue in this proceeding, including the following efforts:
								</li>

								<li>
									Since receiving the student loans at issue, I have made a total of $ <input type="text" value="<?=$paid_amount?>" name="paid_amount" placeholder="Paid amount on Loan"> in payments on the loans, including the following:
									<div class="mt-3">
										<input class="border" type="checkbox" name="loan_payments[]" value="regular_monthly_payment" <?php if (in_array('regular_monthly_payment', $loan_payments)) {echo "checked";}?>> regular monthly payments of $ <input type="text" value="<?=$regular_payment_amount?>" name="regular_payment_amount" placeholder="Regular Payment Amount"> each.
									</div>

									<div class="mt-3 additional_payments">
										<input class="border" type="checkbox" name="loan_payments[]" value="additional_payment" <?php if (in_array('additional_payment', $loan_payments)) {echo "checked";}?>> additional payments, including
										<?php
foreach ($additional_payments as $key => $value) {
	?>
										$ <input type="text" value="<?=$value?>" name="additional_payments[]" placeholder="Additional Amount">&nbsp;&nbsp;
										<?php }?>.
										<a href="javascript:;" class="btn btn-primary" id="add_amount">Add Amount</a>
									</div>
								</li>

								<li>
									I have received <input type="text" value="<?=$forbearance?>" name="forbearance" placeholder="forbearance + deferment from litigation package"> forbearances or deferments. I spent a period totaling <input type="text" value="<?=$forbearance_months?>" name="forbearance_months" placeholder="forbearance + deferment months from litigation package"> months in forbearance or deferment.
								</li>

								<li>
									I have attempted to contact the company that services or collects on my student loans or the Department of Education regarding payment options, forbearance and deferment options, or loan consolidation at least <input type="text" value="<?=$contact_number?>" name="contact_number" placeholder="Contact Number"> times.
								</li>

								<li>
									I have sought to enroll in one or more “Income Driven Repayment Programs” or similar repayment programs offered by the Department of Education, including the following:
									<div class="mt-3">
										Description of efforts:<br>
										<textarea name="idr_explanation" maxlength="2000" style="width: 100%;" placeholder="Explanation of IDR Attempts" rows="5"><?=$idr_explanation?></textarea>
									</div>
								</li>

								<li>
									[If you did not enroll in such a program].I have not enrolled in an “Income Driven Repayment Program” or similar repayment program offered by the Department of Education for the following reasons:
									<div class="mt-3">
										<textarea name="no_idr_explanation" maxlength="2000" style="width: 100%;" placeholder="Explanation of No IDR" rows="5"><?=$no_idr_explanation?></textarea>
									</div>
								</li>

								<li>
									Describe any other facts indicating you have acted in good faith in the past in attempting to repay the student loan(s) you are seeking to discharge. These may include efforts to obtain employment, maximize your income,or minimize your expenses. They also may include any efforts you made to apply for a federal loan consolidation, respond to outreach from a loan servicer or collector, or engage meaningfully with a third party, you believed would assist you in managing your student loan debt.
									<div class="mt-3">
										<textarea name="faith_facts_explanation" maxlength="2000" style="width: 100%;" placeholder="Explanation of Other Good Faith Facts" rows="5"><?=$faith_facts_explanation?></textarea>
									</div>
								</li>
								<h3 class="text-center subheadings" data-num="V.&nbsp;&nbsp;">
									CURRENT ASSETS
								</h3>

								<li>
									I own the following parcels of real estate:<br>
									<div class="multiple_parcels">

									<?php
// echo "<pre>";
// print_r($parcels['name']);die;
if (gettype($parcels) == 'object' || gettype($parcels) == 'array') {
	foreach ($parcels['address'] as $key => $value) {
		?>
									<div style="border: 1px solid #f0f0f0;padding: 10px;margin: 10px 0;" class="parcel">
										<a href="javascript:;" class="remove_parcel" style="float:right;<?php if ($key == 0) {}?>"><i class="fa fa-trash"></i></a>
										<div class="mt-3">
											Address: <textarea  name="parcels[address][]" rows="3" cols="30"><?=$value?></textarea>
										</div>

										<div class="mt-3">
											Owners:<sup>7</sup>
											<?php
foreach ($parcels['name'][$key] as $k => $name) {
			?>
											<span><input type="text" name="parcels[name][<?=$key?>][]" value="<?=$name?>"><a href="javascript:;" class="remove_name" style="<?php if ($k == 0) {echo "display:none";}?>"><i class="fa fa-trash"></i></a></span>&nbsp;&nbsp;
											<?php
}
		?>
		<a href="javascript:;" class="add_name" id="name_<?=$key?>"><i class="fa fa-plus" data-toggle="tooltip" title="Click to add more Owners"></i></a>
										</div>

										<div class="mt-3">
											Fair market value: $ <input type="text" value="<?=$parcels['value'][$key]?>" name="parcels[value][]">
										</div>

										<div class="mt-3">
											Total balance of mortgages and other liens.	<?php
// echo "<pre>";
		// print_r($parcels['balance']);die;
		foreach ($parcels['balance'][$key] as $k => $bal) {
			?>
											<input type="text" name="parcels[balance][<?=$key?>][]" value="<?=$bal?>">&nbsp;&nbsp;
											<?php
}
		?>
		<a href="javascript:;" class="add_balance" id="balance_<?=$key?>"><i class="fa fa-plus" data-toggle="tooltip" title="Click to add balance"></i></a>
										</div>
									</div>
									<?php
}
} else {
	?>
									<div style="border: 1px solid #f0f0f0;padding: 10px;margin: 10px 0;" class="parcel">
										<div class="mt-3">
											Address: <textarea name="parcels[address][]" rows="3" cols="30"></textarea>
										</div>

										<div class="mt-3">
											Owners:<sup>7</sup> <span><input type="text" name="parcels[name][0][]"><a href="javascript:;" class="remove_name" style="display:none"><i class="fa fa-trash"></i></a>
		<a href="javascript:;" class="add_name"><i class="fa fa-plus" data-toggle="tooltip" title="Click to add more owners"></i></a>
										</div>

										<div class="mt-3">
											Fair market value: $ <input type="text" name="parcels[value][]">
										</div>

										<div class="mt-3">
											Total balance of mortgages and other liens.	$ <input type="text" name="parcels[balance][0][]">
		<a href="javascript:;" class="add_balance"><i class="fa fa-plus" data-toggle="tooltip" title="Click to add balance"></i></a>
										</div>
									</div>
									<?php
}
?>
									</div>
									<a href="javascript:;" style="float: right;" class="btn btn-primary" id="add_parcel">Add Parcel</a>
								</li>

									<p> <sup>7</sup> List by name all owners of record (self and spouse, for example)</p>

								<li style="margin:10px 0" class="motor_vehicles">
									<a href="javascript:;" style="float: right;" class="btn btn-primary" id="add_motor_vehicle">Add Vehicle</a>
									I own the following motor vehicles:<br>
									<!-- <input type="text" value="<?=$vehicle_owners?>" name="vehicle_owners" placeholder="Vehicle Owners"> -->

									<?php
if (gettype($motor_vehicle_payments) == 'object' || gettype($motor_vehicle_payments) == 'array') {
	foreach ($motor_vehicle_payments['make_model'] as $key => $value) {
		?>
									<div class="motor" style="border: 1px solid #f0f0f0;padding: 10px;margin: 10px 0;display: grid;">
										<a href="javascript:;" class="remove_motor_vehicle" style="text-align:right;<?php if ($key == 0) {echo "display:none";}?>"><i class="fa fa-trash"></i></a>
										<div class="mt-3">
											<div class="w-50">
												Make and model:
											</div>
											<div class="w-50">
												<input type="text" value="<?=$value?>" name="motor_vehicle_payments[make_model][]" placeholder="Make & Model">
											</div>
										</div>

										<div class="mt-3">
											<div class="w-50">
												Fair market value:
											</div>
											<div class="w-50 fair_markets">
												$<input type="text" value="<?=$motor_vehicle_payments['fair_market'][$key]?>" name="motor_vehicle_payments[fair_market][]" placeholder="Value">
											</div>
										</div>

										<div class="mt-3">
											<div class="w-50">
												Total balance of Vehicle loans And other liens:
											</div>
											<div class="w-50 motor_balances">
												$<input type="text" value="<?=$motor_vehicle_payments['balance'][$key]?>" name="motor_vehicle_payments[balance][]" placeholder="Balance">
											</div>
										</div>
									</div>
									<?php
}
} else {
	?>
									<div class="motor" style="border: 1px solid #f0f0f0;padding: 10px;margin: 10px 0;display: grid;">
										<div class="mt-3">
											<div class="w-50">
												Make and model:
											</div>
											<div class="w-50">
												<input type="text" name="motor_vehicle_payments[make_model][]" placeholder="Make & Model">
											</div>
										</div>

										<div class="mt-3">
											<div class="w-50">
												Fair market value:
											</div>
											<div class="w-50 fair_markets">
												$<input type="text" name="motor_vehicle_payments[fair_market][]" placeholder="Value">
											</div>
										</div>

										<div class="mt-3">
											<div class="w-50">
												Total balance of Vehicle loans And other liens:
											</div>
											<div class="w-50 motor_balances">
												$<input type="text" name="motor_vehicle_payments[balance][]" placeholder="Balance">
											</div>
										</div>
									</div>
								<?php
}
?>
								</li>

								<li class="mt-3">
									I hold a total of $ <input type="text" value="<?=$retirement_amount?>" name="retirement_amount" placeholder="Retirement Amount"> in retirement assets, held in 401k, IRA and similar retirement accounts.
								</li>

								<li>
									I own the following interests in a corporation, limited liability company, partnership, or other entity:

									<table class="table table-bordered table-bordered nostyle">
										<thead>
											<th>Name Of Entity</th>
											<th>State Incorporated<sup>8</sup></th>
											<th>Type & Percentage Interest<sup>9</sup></th>
											<th>&nbsp;</th>
										</thead>
										<tbody class="entity_table">
											<?php
foreach ($entities['name'] as $key => $value) {
	?>
												<tr>
													<td>
														<input type="text" name="entities[name][]" value="<?=$value?>">
													</td>
													<td>
														<input type="text" name="entities[state][]" value="<?=$entities['state'][$key]?>">
													</td>
													<td>
														<input type="text" name="entities[type][]" value="<?=$entities['type'][$key]?>">
													</td>
													<td><a href="javascript:;" class="remove_entity"><i class="fa fa-trash"></i></a></td>
												</tr>
											<?php
}
?>
										</tbody>
									</table>
									<a href="javascript:;" style="float:right;" class="btn btn-primary" id="add_entity">Add Entity</a>
								</li>

								<li>
									I currently am anticipating receiving a tax refund totaling $ <input type="text" value="<?=$tax_refund?>" name="tax_refund" placeholder="Tax Refund">.
								</li>

								<h3 class="text-center subheadings" data-num="VI.&nbsp;&nbsp;">
									ADDITIONAL CIRCUMSTANCES
								</h3>

								<li>
									I submit the following circumstances as additional support for my effort to discharge my student loans as an “undue hardship” under 11 U.S.C. §523(a)(8):<br>

									<div class="mt-3">
										<textarea name="additional_circumstances_explanation" maxlength="2000" style="width: 100%;" placeholder="Explanation of Additional Circumstances" rows="5"><?=$additional_circumstances_explanation?></textarea>
									</div>

									<div class="mt-3">
										Pursuant to 28 U.S.C.§1746, I declare under penalty of perjury that the foregoing is true and correct.
									</div>
								</li>

							</ol>
						</td>
					</tr>
					<tr>
						<td class="w-50">&nbsp;
						</td>
						<td class="w-50 text-right">
							<input type="text" name="signature" value="<?=$signature?>">
							<br>
							<label>Signature</label>
							<br>
							<input type="text" name="sign_name" value="<?=$sign_name?>">
							<br>
							<label>Name</label>
							<br>
							<input type="text" name="sign_date" value="<?=$sign_date?>">
							<br>
							<label>Date</label>
						</td>
					</tr>
					<tr>
						<td>
							<p> <sup>8</sup> The state, if any, in which the entity is incorporated. Partnerships, joint ventures and some other business entities might not be incorporated.</p>
							<p><sup>9</sup> For example, shares, membership interest, partnership interest</p>
						</td>
					</tr>
				</tbody>
			</table>
			</div>
		</div>
	</form>
<?php	//$this->load->view("Admin/inc/template_js.php");?>

	<div id="myModal_nslds" class="modal fade" role="dialog" style="position: absolute;z-index: 9999;">
	  <div class="modal-dialog modal-lg">

	    <!-- Modal content-->
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal">&times;</button>
	        <h4 class="modal-title">NSLDS Snapshot</h4>
	      </div>
	      <div class="modal-body" id="nslds_snapshot_body"></div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	      </div>
	    </div>

	  </div>
	</div>
	<script src="<?php echo base_url('assets/crm/plugins/jQuery/jquery-2.2.3.min.js'); ?>"></script>
	<script src="<?php echo base_url('assets/crm/bootstrap/js/bootstrap.min.js'); ?>"></script>
	<script type="text/javascript">
		function view_nslds_snapshot_body(snapshot_url, msg_disp_id) {
			$.post(snapshot_url, {client_id: 'client_id'},function(data, status) {
				var tab = window.open('NSLDS Screenshot', '_blank');
				tab.document.write(data);
			});
			return false;
		}

		$('[data-toggle=tooltip]').tooltip();

		$('[name=etal]').on('input blur',function(){
			if($(this).val()!=''){
				$('#defendants').show();
			}else{
				$('#defendants').hide();
			}
		})

		$('[name=address]').on('input blur',function(){

			if($(this).val() != ''){
				var width = $(this).val().length * 8 + 10;

				$(this).css('width', width + 'px');
			}
		})

		$('[name=employer_name_and_address]').on('input blur',function(){

			if($(this).val() != ''){
				var width = $(this).val().length * 8 + 10;

				$(this).css('width', width + 'px');
			}
		})

		$('#add_member').click(function(){
			var html = '<tr><td width="40%"><input type="text" placeholder="Enter full name" name="household[fullname][]" ></td><td width="20%"><input type="text" placeholder="Enter age" name="household[age][]"></td><td width="40%"><input type="text" placeholder="Enter Relation" name="household[relation][]"></td><td><a href="javascript:;" class="remove_household"><i class="fa fa-trash"></i></a></td></tr>';
			$('.household table tbody').append(html);
		})

		$(document).on('click','.remove_household',function(){
			$(this).closest('tr').remove();
		})

		$('#add_income').click(function(){
			var html = '<div><input type="text" placeholder="Other Income" name="other_incomes[income_value][]"> my <input type="text" placeholder="Describe Income" name="other_incomes[income_detail][]"><a href="javascript:;" class="remove_income"><i class="fa fa-trash"></i></a></div>';
			$('.other_incomes').append(html);
		})

		$(document).on('click','.remove_income',function(){
			$(this).closest('div').remove();
		})


		$('[name=multiple_loan]').click(function(){
			if($('[name=multiple_loan]:checked').val()=='yes'){
				$('.multiple_loans').show();
				$('#add_loan').show();

				$('[name=sl_loan_amount]').attr('disabled',true).val('See Attached');
				$('[name=sl_monthly_payment]').attr('disabled',true).val('See Attached');
				$('[name=sl_date_of_payoff]').attr('disabled',true).val('See Attached');
				$('[name=sl_school_attended]').attr('disabled',true).val('See Attached');
				$('[name=sl_degree_pursued]').attr('disabled',true).val('See Attached');
				$('[name=sl_specialization]').attr('disabled',true).val('See Attached');
				$('[name=sl_date_school_completed]').attr('disabled',true).val('See Attached');
				$('[name=sl_type_of_degree]').attr('disabled',true).val('See Attached');
				$('[name=sl_date_studies_ceased]').attr('disabled',true).val('See Attached');
				$('[name=sl_date_of_default]').attr('disabled',true).val('See Attached');
			}
			else{
				$('.multiple_loans').hide();
				$('#add_loan').hide();
				$('[name=sl_loan_amount]').attr('disabled',false).val('<?=$sl_loan_amount?>');
				$('[name=sl_monthly_payment]').attr('disabled',false).val('<?=$sl_monthly_payment?>');
				$('[name=sl_date_of_payoff]').attr('disabled',false).val('<?=$sl_date_of_payoff?>');
				$('[name=sl_school_attended]').attr('disabled',false).val('<?=$sl_school_attended?>');
				$('[name=sl_degree_pursued]').attr('disabled',false).val('<?=$sl_degree_pursued?>');
				$('[name=sl_specialization]').attr('disabled',false).val('<?=$sl_specialization?>');
				$('[name=sl_date_school_completed]').attr('disabled',false).val('<?=$sl_date_school_completed?>');
				$('[name=sl_type_of_degree]').attr('disabled',false).val('<?=$sl_type_of_degree?>');
				$('[name=sl_date_studies_ceased]').attr('disabled',false).val('<?=$sl_date_studies_ceased?>');
				$('[name=sl_date_of_default]').attr('disabled',false).val('<?=$sl_date_of_default?>');
			}
		})

		$('#add_deduction').click(function(){
			var html = '<div class="one_deduct"><div class="w-50"><input type="text" name="other_deductions[deduction][]" placeholder="Other Deductions"></div><div class="w-50">$<input type="text" name="other_deductions[amount][]" placeholder="Amount"><a href="javascript:;" class="remove_deduction"><i class="fa fa-trash"></i></a></div></div>';
			$('.other_deductions').append(html);
		})

		$(document).on('click','.remove_deduction',function(){
			$(this).closest('div.one_deduct').remove();
		})

		$('#add_entity').click(function(){
			var html = '<tr><td><input type="text" name="entities[name][]"></td><td><input type="text" name="entities[state][]"></td><td><input type="text" name="entities[type][]"></td><td><a href="javascript:;" class="remove_entity"><i class="fa fa-trash"></i></a></td></tr>';
			$('.entity_table').append(html);
		})

		$(document).on('click','.remove_entity',function(){
			$(this).closest('tr').remove();
		})

		$('#add_vehicle').click(function(){
			var count = $('[name="vehicle_payments[payment][]"]').length+1;
			var html = '<div style="display:flex;border: 1px solid #f0f0f0;padding: 10px;margin: 10px 0;" class="vehicle"><ul><li><div class="w-50">Vehicle payments(itemize per vehicle)</div><div class="w-50">$<input type="text" class="vehicle_'+count+'" name="vehicle_payments[payment][]" placeholder="Vehicle Payments"></div></li><li><div class="w-50">Monthly average costs of operating vehicles (including gas, routine maintenance, monthly insurance cost)</div><div class="w-50">$<input type="text" class="vehicle_'+count+'" name="vehicle_payments[operating_costs][]" placeholder="Operating Costs"></div></li></ul><a href="javascript:;" class="remove_vehicle" id="vehicle_'+count+'" style="float:right;"><i class="fa fa-trash"></i></a></div>';
			$('.multiple_vehicles').append(html);

			/*var html = '<div>$<input type="text" class="vehicle_'+count+'" name="vehicle_payments[operating_costs][]" placeholder="Operating Cost">\<a href="javascript:;" class="remove_vehicle" id="costs_'+count+'"><i class="fa fa-trash"></i></a></div>';
			$('.multiple_costs').append(html);*/
		})

		$(document).on('click','.remove_vehicle',function(){
			// var id=$(this).attr('id').split('_');
			$(this).closest('div.vehicle').remove();
		})

		$('#add_parcel').click(function(){
			var html = '<div style="border: 1px solid #f0f0f0;padding: 10px;margin: 10px 0;" class="parcel"><a href="javascript:;" class="remove_parcel" style="float:right;"><i class="fa fa-trash"></i></a><div class="mt-3">Address: <textarea name="parcels[address][]" rows="3" cols="30"></textarea></div><div class="mt-3">Owners:<sup>7</sup> <span><input type="text" name="parcels[name][0][]"><a href="javascript:;" class="remove_name" style="display:none"><i class="fa fa-trash"></i></a><span><a href="javascript:;" class="add_name"><i class="fa fa-plus" data-toggle="tooltip" title="Click to add more owners"></i></a></div><div class="mt-3">Fair market value: $ <input type="text" name="parcels[value][]"></div><div class="mt-3">Total balance of mortgages and other liens.	$ <input type="text" name="parcels[balance][0][]"><a href="javascript:;" class="add_balance"><i class="fa fa-plus" data-toggle="tooltip" title="Click to add balance"></i></a></div></div>';
			$('.multiple_parcels').append(html);
		})

		$(document).on('click','.remove_parcel',function(){
			$(this).closest('div').remove();
		})

		$('#add_motor_vehicle').click(function(){
			var count = $('[name="motor_vehicle_payments[make_model][]"]').length+1;
			var html = '<div class="motor" style="border: 1px solid #f0f0f0;padding: 10px;margin: 10px 0;display: grid;"><a href="javascript:;" class="remove_motor_vehicle" style="text-align:right"><i class="fa fa-trash"></i></a><div class="mt-3"><div class="w-50">Make and model:</div><div class="w-50"><input type="text" name="motor_vehicle_payments[make_model][]" placeholder="Make & Model"></div></div><div class="mt-3"><div class="w-50">Fair market value:</div><div class="w-50 fair_markets">$<input type="text" name="motor_vehicle_payments[fair_market][]" placeholder="Value"></div></div><div class="mt-3"><div class="w-50">Total balance of Vehicle loans And other liens:</div><div class="w-50 motor_balances">$<input type="text" name="motor_vehicle_payments[balance][]" placeholder="Balance"></div></div></div>';
			$('.motor_vehicles').append(html);
		})

		$(document).on('click','.add_name',function(){
			var id = $(this).attr('id');

			if(id != undefined){
				id = id.split('_')[1];
				$(this).closest('div').append('<span><input type="text" name="parcels[name]['+id+'][]"><a href="javascript:;" class="remove_name"><i class="fa fa-trash"></i></a></span>&nbsp;&nbsp;');
			}
			else
				$(this).closest('div').append('<span><input type="text" name="parcels[name][0][]"><a href="javascript:;" class="remove_name"><i class="fa fa-trash"></i></a></span>&nbsp;&nbsp;');

			if($(this).closest('.parcel').find('.remove_name').length > 1){
				$(this).closest('.parcel').find('.remove_name').show();
			}
			else{
				$(this).closest('.parcel').find('.remove_name').hide();
			}
		})

		$(document).on('click','.add_balance',function(){
			var id = $(this).attr('id');

			if(id != undefined){
				id = id.split('_')[1];
				$(this).closest('div').append('<input type="text" name="parcels[balance]['+id+'][]">&nbsp;&nbsp;');
			}
			else{
				$(this).closest('div').append('<input type="text" name="parcels[balance][0][]">&nbsp;&nbsp;');
			}
		})

		$(document).on('click','.remove_name',function(){
			$(this).closest('span').remove();
		})


		$(document).on('click','.remove_motor_vehicle',function(){
			$(this).closest('div.motor').remove();
		})

		$('#add_loan').click(function(){
			var html = '<tr><td><input type="text" name="multiple_loans[loan_name][]"></td><td><input type="text" name="multiple_loans[loan_type][]"></td><td><input type="text" name="multiple_loans[loan_amount][]"></td><td><input type="text" name="multiple_loans[monthly_payment][]"></td><td><input type="date" name="multiple_loans[date_of_payoff][]" ></td><td><input type="date" name="multiple_loans[date_of_default][]"></td><td><input type="text" name="multiple_loans[school_attended][]"></td><td><input type="text" name="multiple_loans[degree_pursued][]" ></td><td><input type="text" name="multiple_loans[specialization][]"></td><td><input type="date" name="multiple_loans[date_school_completed][]"></td><td><input type="text" name="multiple_loans[type_of_degree][]" ></td><td><input type="date" name="multiple_loans[date_studies_ceased][]"></td><td><a href="javascript:;" class="remove_loan"><i class="fa fa-trash"></i></a></td></tr>';
			$('.multiple_loans table tbody').append(html);
		})

		$('#add_amount').click(function(){
			var html = '$<input type="text" name="additional_payments[]" placeholder="Additional Amount">&nbsp;&nbsp;';
			$('.additional_payments').append(html);
		})

		$(document).on('click','.remove_loan',function(){
			$(this).closest('tr').remove();
		})

		$('[name=correct_loan_info]').click(function(){
			if($('[name=correct_loan_info]:checked').val()=='yes'){
				$('#multipleloan').hide();
			}
			else{
				$('#multipleloan').show();
			}
		})

		$('[name=not_employed]').click(function(){
			if($(this).is(':checked')){
				$('[name=job_title]').val('');
				$('[name=job_title]').attr('disabled',true);
				$('[name=employer_name_and_address]').val('');
				$('[name=employer_name_and_address]').attr('disabled',true);
			}
			else{
				$('[name=job_title]').attr('disabled',false);
				$('[name=employer_name_and_address]').attr('disabled',false);
			}
		})

		$('[name=student_loan_default]').click(function(){
			if($(this).is(':checked')){
				$('[name=date_of_payoff]').val('');
				$('[name=date_of_payoff]').attr('disabled',true);
				$('[name=date_of_default]').val('');
				$('[name=date_of_default]').attr('disabled',false);
			}
			else{
				$('[name=date_of_payoff]').val('');
				$('[name=date_of_payoff]').attr('disabled',false);
				$('[name=date_of_default]').val('');
				$('[name=date_of_default]').attr('disabled',true);
			}
		})

		$('[name=date_studies_ceased]').on('input blur',function(){
			if($(this).val()!=''){
				$('[name=date_school_completed]').val('');
				$('[name=type_of_degree]').val('');
			}
		})

		$('[name=income_proof_submitted]').click(function(){
			// $('[name=income_proof_submitted1]').prop('checked',false);
			$('[name=income_proof_submitted1][value="'+$('[name=income_proof_submitted]:checked').val()+'"]').prop('checked',true);
			if($('[name=income_proof_submitted]:checked').val()=='alternative_documentation'){
				$('[name=alternative_document_titles]').attr('disabled',false);
			}else{
				$('[name=alternative_document_titles]').attr('disabled',true);
			}
		})

		$('[name=income_proof_submitted1]').click(function(){
			$('[name=income_proof_submitted][value="'+$('[name=income_proof_submitted1]:checked').val()+'"]').prop('checked',true);
			if($('[name=income_proof_submitted1]:checked').val()=='alternative_documentation'){
				$('[name=alternative_document_titles]').attr('disabled',false);
			}else{
				$('[name=alternative_document_titles]').attr('disabled',true);
			}
		})
	</script>
</body>
</html>