<?php	defined('BASEPATH') OR exit('No direct script access allowed');	?>
<?php
//ALTER TABLE `intake_client_status` ADD `form_data` TEXT NOT NULL AFTER `intake_id`;

error_reporting(0);
@extract($_POST);
$fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);

$sg_1 = $this->uri->segment(1);
$client_id = $this->uri->segment(3);
if($GLOBALS["loguser"]["role"] == "Customer") { $client_id = $GLOBALS["loguser"]["id"]; }

$user = $client_data["client"];
@extract($user);
$client_id = $id;
$program_id_primary = 97;
if($client_id!='') {	if(isset($car['id'])) {


// Submit Data
if(isset($_POST['Submit_save']) || isset($_POST['Submit_approve']))
{
	$error = "";
	
	
	
	if($error == "")
	{
		
		$form_data = json_encode($_POST);
		$this->db->query("UPDATE client_attestation set form_data='$form_data' where client_id='$client_id' limit 1");
		
		if(isset($_POST['Submit_approve']))
		{
			/*
			$this->db->query("UPDATE intake_client_status set exp_date='".date('Y-m-d')."', status2='Approved' where client_id='$client_id' and id='".$this->uri->segment(4)."' limit 1");
			
			$q = $this->db->query("SELECT * FROM client_program_progress where client_id='$client_id' and program_id_primary='$program_id_primary' and step_id='15' limit 1");
			$cpp = $q->row_array();
			$url = base_url('account/customer/status/'.$client_id.'/complete/'.$cpp['program_definition_id']."/redirect_to_document/".$ics['id']);
			redirect($url);
			exit;
			*/
		}
		
		$this->session->set_flashdata('error', "This feature is under development");
		redirect(base_url($sg_1.'/attestation_form/'.$client_id));
		exit;
		
	}
	else
	{
		$this->session->set_flashdata('error', $error);
	}
	redirect(base_url('account/customer_intake_form/'.$client_id.'/'.$ics['id']));
	exit;
}


$q = $this->db->query("SELECT * FROM client_program_progress where client_id='$client_id' and program_id_primary='$program_id_primary' and step_id='15' limit 1");
$cpp = $q->row_array();
$program_definition_id = $cpp['program_definition_id'];


$form_data = json_decode($car['form_data'], true);
if(is_array($form_data)) {	@extract($form_data);	}

/*
print_r("<pre>");
print_r($form_data);
print_r("</pre>");
echo "<hr />";
exit;
*/


?>
<!DOCTYPE html>
<html>
<head>
<?php
$page_data['data']['name'] = $page_data['data']['meta_title'] = "Attestation Form";

$this->load->view("account/inc/head", $page_data);	?>

</head>
<body style="padding-bottom:25px;">



<form action="" method="post" enctype="multipart/form-data">
<div style="position:fixed; top:0px; left:0px; width:100%; height:60px; background:#F8F8F8; z-index:9999;">
<div class="container">

<div class="row">
	<div class="col-md-7">
    	<h3 style="margin:5px 0 0 0px;"><strong>Data Confirmation for Attestation</strong></h3>
        <?php if($cpp['step_completed_date']=='' || $ics['status2']!='Approved') { } else {	?><span style="font-size:14px; color:#009900;"><i class="fa fa-check-square-o" aria-hidden="true"></i> Already Approved</span><?php } ?>        
    </div>
    <div class="col-md-5">

<div class="text-right" style="margin:10px 0 0 0;">
    <button type="submit" name="Submit_save" class="btn btn-success"><i class="fa fa-floppy-o" aria-hidden="true"></i> Save</button> &nbsp; 
    <button type="submit" name="Submit_approve" class="btn btn-primary"><i class="fa fa-check-square-o" aria-hidden="true"></i> Approve</button>
</div>

    </div>
</div>
</div>
</div>
<div class="clr"></div>
<div style="width:100%; height:80px;"></div>
<div class="clr"></div>

<div class="container">
<div><?php	$this->load->view("template/alert.php");	?></div>

<div>

<div class="row">

<div>
<div class="col-md-4 mb-3 mt-3"><label for="d" class="form-label">DEBTOR</label> <input type="text" class="form-control" name="debtor" value="<?php echo $debtor; ?>" /></div>

<div class="col-md-4 mb-3 mt-3"><label for="u" class="form-label">UNITED STATES DEPARTMENT OF EDUCATION</label> <input type="text" class="form-control" name="et_al" value="<?php echo $et_al; ?>" /></div>

<div class="col-md-4 mb-3 mt-3"><label for="c" class="form-label">Case No.</label> <input type="text" class="form-control" name="caseno" value="<?php echo $caseno; ?>" /></div>
<div class="col-md-4 mb-3 mt-3"><label for="c" class="form-label">Chapter</label> <input type="text" class="form-control" name="chapter" value="<?php echo $chapter; ?>" /></div>
<div class="col-md-4 mb-3 mt-3"><label for="c" class="form-label">Adversary Pro.</label> <input type="text" class="form-control" name="adversary_pro" value="<?php echo $adversary_pro; ?>" /></div>


<div class="col-md-4 mb-3 mt-3"><label for="c" class="form-label">Name</label> <input type="text" class="form-control" name="name" value="<?php echo $name; ?>" /></div>
<div class="clr"></div>



<div class="col-md-12"><h3 style="margin-top:20px;"><strong>I. PERSONAL INFORMATION</strong></h3></div>
<div class="col-md-4 mb-3 mt-3"><label for="c" class="form-label">Address</label> <input type="text" class="form-control" name="address" value="<?php echo $address; ?>" /></div>
<div class="col-md-4 mb-3 mt-3"><label for="c" class="form-label">Country</label> <input type="text" class="form-control" name="country" value="<?php echo $country; ?>" /></div>
<div class="col-md-4 mb-3 mt-3"><label for="c" class="form-label">State</label> <input type="text" class="form-control" name="state" value="<?php echo $state; ?>" /></div>
<div class="col-md-4 mb-3 mt-3"><label for="c" class="form-label">Full Name</label> <input type="text" class="form-control" name="fullname" value="<?php echo $fullname; ?>" /></div>
<div class="col-md-4 mb-3 mt-3"><label for="c" class="form-label">Age</label> <input type="text" class="form-control" name="age" value="<?php echo $age; ?>" /></div>
<div class="col-md-4 mb-3 mt-3"><label for="c" class="form-label">Self</label> <input type="text" class="form-control" name="self" value="<?php echo $self; ?>" /></div>
<div class="col-md-4 mb-3 mt-3"><label for="c" class="form-label">Relationship</label> <input type="text" class="form-control" name="relationship" value="<?php echo $relationship; ?>" /></div>


<div class="clr"></div>

<div class="col-md-12">
<p><strong>Questions four through nine request information related to your outstanding student loan debt and your educational history. The Department of Education will furnish this information to the Assistant United States Attorney (“AUSA”) handling your case, and it should be provided to you. If you agree that the information provided to you regarding your student loan debt and educational history is accurate, you may simply confirm that you agree, and these questions do not need to be completed. If you have not received the information from Education or the AUSA at the time you are completing this form, or if the information is not accurate, you may answer these questions based upon your own knowledge. If you have more than one student loan which you are seeking to discharge in this adversary proceeding, please confirm that the AUSA has complete and accurate information for each loan, or provide that information for each loan.</strong></p>



<div class="mb-3 mt-3">
<div><span> I confirm that the student loan information and educational history provided to me and attached to this Attestation is correct:</span></div>
<?php
$tmp_arr_1 = array("Yes"=>"Yes", "No"=>"No");
foreach($tmp_arr_1 as $k=>$v) {
?>
<label class="radio-inline"><input type="radio" name="radio_1" value="<?php echo $k; ?>" <?php if($radio_1 == $k) { echo " checked"; } ?> required="required"> <?php echo $v; ?></label> &nbsp; <?php } ?>
</div>

<p class="mb-3 mt-3">The outstanding balance of the student loan[s] I am seeking to discharge in this adversary proceeding is $ <input type="text" name="obots" value="<?php echo $obots; ?>" /></p>

<p class="mb-3 mt-3">The current monthly payment on such loan[s] is <input type="text" name="cmpmt" value="<?php echo $cmpmt; ?>" />. The loan[s] are scheduled to be repaid in <input type="month" name="date_of_payoff" value="<?php echo $date_of_payoff; ?>" /> [month and year] [OR] <label class="radio-inline"><input type="checkbox" name="checkbox_1" value="Yes" <?php if($checkbox_1 == "Yes") { echo " checked"; } ?> /> </label> My student loan[s] went into default in <input type="month" name="date_of_default" value="<?php echo $date_of_default; ?>" /> [month and year].</p>

<p class="mb-3 mt-3">I incurred the student loan[s] I am seeking to discharge while attending <input type="text" name="school_attended" value="<?php echo $school_attended; ?>" />, where I was pursuing a <input type="text" name="degree_pursued" value="<?php echo $degree_pursued; ?>" /> degree with a specialization in <input type="text" name="specialization" value="<?php echo $specialization; ?>" />.</p>

<p class="mb-3 mt-3">In <input type="month" name="date_school_completed" value="<?php echo $date_school_completed; ?>" /> [month and year], I completed my course of study and received a <input type="text" name="type_of_degree" value="<?php echo $type_of_degree; ?>" /> degree [OR] In <input type="month" name="date_studies_ceased" value="<?php echo $date_studies_ceased; ?>" /> [month and year], I left my course of study and did not receive a degree.</p>

<p class="mb-3 mt-3">I am currently employed as a <input type="text" name="job_title" value="<?php echo $job_title; ?>" />. My employer’s name and address is <input type="text" name="employer_name_and_address" value="<?php echo $employer_name_and_address; ?>" /> [OR] <label class="radio-inline"><input type="checkbox" name="checkbox_2" value="Yes" <?php if($checkbox_2 == "Yes") { echo " checked"; } ?> /> </label> I am not currently employed.</p>


</div>

<div class="clr"></div>




<div class="col-md-12">
<h3 style="margin-top:20px;"><strong>II. CURRENT INCOME AND EXPENSES</strong></h3>
<p>I do not have the ability to make payments on my student loans while maintaining a minimal standard of living for myself and my household. I submit the following information to demonstrate this:</p>
<h4 style="margin-top:15px;"><strong>A. Household Gross Income</strong></h4>

<p>My current monthly household <strong>gross</strong> income from all sources is $<input type="text" name="gross_income" value="<?php echo $gross_income; ?>" />.<sup>1</sup> This amount includes the following monthly amounts:</p>
<p><input type="text" name="gross_employment" value="<?php echo $gross_employment; ?>" /> my gross income from employment (if any)</p>
<p><input type="text" name="unemployment" value="<?php echo $unemployment; ?>" /> my unemployment benefits</p>
<p><input type="text" name="social_security" value="<?php echo $social_security; ?>" /> my Social Security Benefits</p>
<p><input type="text" name="other_income_1" value="<?php echo $other_income_1; ?>" /> other income 1 my <input type="text" name="input_income_1" value="<?php echo $input_income_1; ?>" /> input income 1 <span class="bg_cyan">[REPEAT UNTIL USER ENDS]</span></p>
<p><input type="text" name="other_household_employment" value="<?php echo $other_household_employment; ?>" /> gross income from employment of other members of household</p>
<p><input type="text" name="other_household_unemployment" value="<?php echo $other_household_unemployment; ?>" /> unemployment benefits received by other members of household</p>
<p><input type="text" name="other_household_social_security" value="<?php echo $other_household_social_security; ?>" /> Social Security benefits received by other members of household</p>
<p><input type="text" name="other_household_other_income" value="<?php echo $other_household_other_income; ?>" /> other income from any source received by other members of household
<br /><span class="bg_cyan">[PLEASE FORMAT SO NUMBERS AND TEXT FORM A VERTICAL LINE – RIGHT JUSTIFY NUMBERS, LEFT JUSTIFY TEXT]</span></p>

<p>The current monthly household gross income stated above (select which applies):<br />
<span class="bg_cyan">[ONLY ONE BOX CAN BE CHECKED BELOW. MUST APPEAR AS CHECK BOXES, NOT RADIO BUTTONS]</span></p>

<p>Will client be submitting tax returns, 2-months of pay stubs, or other alternative documents of income? Tax return <input type="checkbox" name="checkbox_12_1" value="Yes" <?php if($checkbox_12_1 == "Yes") { echo " checked"; } ?> /> , Pay stubs <input type="checkbox" name="checkbox_12_2" value="Yes" <?php if($checkbox_12_2 == "Yes") { echo " checked"; } ?> /> , Alternative documentation <input type="checkbox" name="checkbox_12_3" value="Yes" <?php if($checkbox_12_3 == "Yes") { echo " checked"; } ?> /></p>

<p><input type="checkbox" name="checkbox_12_4" value="Yes" <?php if($checkbox_12_4 == "Yes") { echo " checked"; } ?> /> Includes a monthly average of the gross income shown on the most recent tax return[s] filed for myself and other members of my household, which are attached, and the amounts stated on such tax returns have not changed materially since the tax year of such returns; OR</p>

<p><input type="checkbox" name="checkbox_12_5" value="Yes" <?php if($checkbox_12_5 == "Yes") { echo " checked"; } ?> /> Represents an average amount calculated from the most recent two months of gross income stated on four (4) consecutive paystubs from my current employment, which are attached; OR</p>

<p><input type="checkbox" name="checkbox_12_6" value="Yes" <?php if($checkbox_12_6 == "Yes") { echo " checked"; } ?> />  My current monthly household gross income is not accurately reflected on either recent tax returns or paystubs from current employment, and I have submitted instead the following documents verifying current gross household income from employment of household members: <input type="text" name="alternative_document_titles" value="<?php echo $alternative_document_titles; ?>" maxlength="500" /> <span class="bg_cyan">[ONLY REQUEST IF CHECK BOX THREE = TRUE. ALLOW 500 CHARACTERS]</span></p>

<p>13. In addition, I have submitted <input type="text" name="additional_document_titles" value="<?php echo $additional_document_titles; ?>" maxlength="500" /> <span class="bg_cyan">[ALLOW 500 CHARACTERS]</span> verifying the sources of income other than income from employment, as such income is not shown on [most recent tax return[s] or paystubs]. </p>

<h4 style="margin-top:15px;"><strong>B. <span style="text-decoration:underline;">Monthly Expenses</span></strong></h4>

<p>My current monthly household expenses do not exceed the amounts listed below based on the number of people in my household for the following categories [Indicate “yes” if your expenses do not exceed the referenced amounts]:</p>

<p>
(a)Living Expenses<br />
<span class="bg_cyan">[VERIFY NUMBER OF PEOPLE IN HOUSEHOLD (HOW MANY LINES FOR #3?) IF #3<5, GO TO (a), SKIP (b), GO TO (c). IF #3 >4, SKIP (a), GO TO (b), SKIP (c).]</span>
</p>

<table class="table">
<tr valign="top">
<td width="50" align="center">i.</td>
<td width="300"><strong>Food</strong><br />
$431 (one person)<br />
$779 (two persons)<br />
$903 (three persons)<br />
$1028 (four persons)</td>
<td>
	<label class="radio-inline"><input type="radio" name="radio_14_1" value="Yes" <?php if($radio_14_1 == "Yes") { echo " checked"; } ?> required="required"> Yes</label>
    <label class="radio-inline"><input type="radio" name="radio_14_1" value="No" <?php if($radio_14_1 == "No") { echo " checked"; } ?> required="required"> No</label>
</td>
</tr>


<tr valign="top">
<td align="center">ii.</td>
<td><strong>Housekeeping supplies</strong><br />
$40 (one person)<br />
$82 (two persons)<br />
$74 (three persons)<br />
$85 (four persons)</td>
<td>
	<label class="radio-inline"><input type="radio" name="radio_14_2" value="Yes" <?php if($radio_14_2 == "Yes") { echo " checked"; } ?> required="required"> Yes</label>
    <label class="radio-inline"><input type="radio" name="radio_14_2" value="No" <?php if($radio_14_2 == "No") { echo " checked"; } ?> required="required"> No</label>
</td>
</tr>

<tr valign="top">
<td align="center">iii.</td>
<td><strong>Apparel & Services</strong><br />
$99 (one person)<br />
$161 (two persons)<br />
$206 (three persons)<br />
$279 (four persons)</td>
<td>
	<label class="radio-inline"><input type="radio" name="radio_14_3" value="Yes" <?php if($radio_14_3 == "Yes") { echo " checked"; } ?> required="required"> Yes</label>
    <label class="radio-inline"><input type="radio" name="radio_14_3" value="No" <?php if($radio_14_3 == "No") { echo " checked"; } ?> required="required"> No</label>
</td>
</tr>

<tr valign="top">
<td align="center">iv.</td>
<td><strong>Personal care products and services<br />(non-medical)</strong><br />
$45 (one person)<br />
$42 (two persons)<br />
$78 (three persons)<br />
$96 (four persons)</td>
<td>
	<label class="radio-inline"><input type="radio" name="radio_14_4" value="Yes" <?php if($radio_14_4 == "Yes") { echo " checked"; } ?> required="required"> Yes</label>
    <label class="radio-inline"><input type="radio" name="radio_14_4" value="No" <?php if($radio_14_4 == "No") { echo " checked"; } ?> required="required"> No</label>
</td>
</tr>

<tr valign="top">
<td align="center">v.</td>
<td><strong>Uninsured medical costs</strong><br />
$75 (per individual under 65)<br />
$153 (per individual over 65)</td>
<td>
	<label class="radio-inline"><input type="radio" name="radio_14_5" value="Yes" <?php if($radio_14_5 == "Yes") { echo " checked"; } ?> required="required"> Yes</label>
    <label class="radio-inline"><input type="radio" name="radio_14_5" value="No" <?php if($radio_14_5 == "No") { echo " checked"; } ?> required="required"> No</label>
</td>
</tr>


<tr valign="top">
<td align="center">vi.</td>
<td><strong>Miscellaneous expenses<br />not included elsewhere on this Attestation:</strong><br />
$170 (one person)<br />
$306 (two persons)<br />
$349 (three persons)<br />
$412 (four persons)</td>
<td>
	<label class="radio-inline"><input type="radio" name="radio_14_6" value="Yes" <?php if($radio_14_6 == "Yes") { echo " checked"; } ?> required="required"> Yes</label>
    <label class="radio-inline"><input type="radio" name="radio_14_6" value="No" <?php if($radio_14_6 == "No") { echo " checked"; } ?> required="required"> No</label>
</td>
</tr>
</table>

<p><span style="text-decoration:underline;">(b)Households Greater Than Four Persons</span></p>
<p>If your household consists of more than four people, please provide your total expenses for the categories in Question 14(a): $ <input type="text" name="total_expenses" value="<?php echo $total_expenses; ?>" /> <span class="bg_cyan">[total expenses (one number) for family greater than 4 persons for Food, Housekeeping supplies, Apparel & Services, Personal care products and services (non-medical), Uninsured medical costs, and Miscellaneous expenses not included elsewhere on the Attestation]</span></p>
<p><span class="bg_cyan">[If you filed a Form 122A-2 Chapter 7 Means Test or 122C-2 Calculation of Disposable Income in your bankruptcy case, you may refer to lines 6 and 7 of those forms for information.]<sup>3</sup></span></p>

<p><span style="text-decoration:underline;">(c) Excess Expenses</span></p>
<hr />
<p><span class="dcp_hint"><sup>3</sup> Forms 122A-2 and 122C-2 are referred to collectively here as the “Means Test.” If you filed a Means Test in your bankruptcy case, you may refer to it for information requested here and in other expense categories below. If you did not file a Means Test, you may refer to your Schedule I and Form 106J – Your Expenses (Schedule J) in the bankruptcy case, which may also list information relevant to these categories. You should only use information from these documents if your expenses have not changed since you filed them.</span></p>


<p style="margin-top:20px;">If your current monthly household expenses exceed the amounts listed above for any of the categories in Question 13(a) and you would like the AUSA to consider such additional expenses as necessary, you may list those expenses and explain the need for such expenses here.</p>
<p><strong>Explanation of expenses greater than amounts listed in 13(a)</strong><br />
<textarea name="explanation_of_expenses" class="form-control"><?php echo $explanation_of_expenses; ?></textarea></p>

<p>15. My current monthly household expenses in the following categories are as follows:</p>
<p><span style="text-decoration:underline;">(a) Payroll Deductions</span></p>


<table class="table">
<tr valign="top">
<td width="50" align="center">i.</td>
<td width="450">Taxes, Medicare and Social Security<br /><span class="bg_yellow">You may refer to line 16 of the Means Test or Schedule I, line 5a</span></td>
<td>$<input type="text" name="tax_social_security" value="<?php echo $tax_social_security; ?>" /></td>
</tr>

<tr valign="top">
<td align="center">ii.</td>
<td>Contributions to retirement accounts<br /><span class="bg_yellow">You may refer to line 17 of the Means Test or Schedule I, line 5b and c</span></td>
<td>$<input type="text" name="retirement_cont" value="<?php echo $retirement_cont; ?>" /></td>
</tr>

<tr valign="top" class="tbt-0">
<td align="center"></td>
<td>Are these contributions required as a condition of your employment?</td>
<td>
	<label class="radio-inline"><input type="radio" name="radio_15_1" value="Yes" <?php if($radio_15_1 == "Yes") { echo " checked"; } ?> required="required"> Yes</label>
    <label class="radio-inline"><input type="radio" name="radio_15_1" value="No" <?php if($radio_15_1 == "No") { echo " checked"; } ?> required="required"> No</label>
</td>
</tr>

<tr valign="top">
<td align="center">iii.</td>
<td>Union dues<br /><span class="bg_yellow">You may refer to line 17 of the Means Test or Schedule I, line 5g</span></td>
<td>$<input type="text" name="union_dues" value="<?php echo $union_dues; ?>" /></td>
</tr>

<tr valign="top">
<td align="center">iv.</td>
<td>Life insurance<br /><span class="bg_yellow">You may refer to line 18 of the Means Test or Schedule I, line 5e</span></td>
<td>$<input type="text" name="life_insurance" value="<?php echo $life_insurance; ?>" /></td>
</tr>

<tr valign="top" class="tbt-0">
<td align="center"></td>
<td>Are the payments for a term policy covering your life?</td>
<td>
	<label class="radio-inline"><input type="radio" name="radio_15_2" value="Yes" <?php if($radio_15_2 == "Yes") { echo " checked"; } ?> required="required"> Yes</label>
    <label class="radio-inline"><input type="radio" name="radio_15_2" value="No" <?php if($radio_15_2 == "No") { echo " checked"; } ?> required="required"> No</label>
</td>
</tr>

<tr valign="top">
<td align="center">v.</td>
<td>Court-ordered alimony and child support<br /><span class="bg_yellow">You may refer to line 19 of the Means Test or Schedule I, line 5f</span></td>
<td>$<input type="text" name="divorce_support" value="<?php echo $divorce_support; ?>" /></td>
</tr>

<tr valign="top">
<td align="center">vi.</td>
<td>Health insurance<br /><span class="bg_yellow">You may refer to line 25 of the Means Test or Schedule I, line 5e</span></td>
<td>$<input type="text" name="health_insurance" value="<?php echo $health_insurance; ?>" /></td>
</tr>

<tr valign="top" class="tbt-0">
<td align="center"></td>
<td>Does the policy cover any persons other than yourself and your family members?</td>
<td>
	<label class="radio-inline"><input type="radio" name="radio_15_3" value="Yes" <?php if($radio_15_3 == "Yes") { echo " checked"; } ?> required="required"> Yes</label>
    <label class="radio-inline"><input type="radio" name="radio_15_3" value="No" <?php if($radio_15_3 == "No") { echo " checked"; } ?> required="required"> No</label>
</td>
</tr>

<tr valign="top">
<td align="center">vii.</td>
<td>Other payroll deductions<br /><input type="text" name="other_deductions" value="<?php echo $other_deductions; ?>" /></td>
<td>Other amount<br /><input type="text" name="other_amount" value="<?php echo $other_amount; ?>" /></td>
</tr>

</table>


<p><span style="text-decoration:underline;">(b) Housing Costs <sup>4</sup></span></p>
<p class="dcp_hint"><sup>4</sup> Forms 122A-2 and 122C-2 are referred to collectively here as the “Means Test.” If you filed a Means Test in your bankruptcy case, you may refer to it for information requested here and in other expense categories below. If you did not file a Means Test, you may refer to your Schedule I and Form 106J – Your Expenses (Schedule J) in the bankruptcy case, which may also list information relevant to these categories. You should only use information from these documents if your expenses have not changed since you filed them.<br /><br />You should list the expenses you actually pay in Housing Costs and Transportation Costs categories. If these expenses have not changed since you filed your Schedule J, you may refer to the expenses listed there, including housing expenses (generally on lines 4 through 6 of Schedule J) and transportation expenses (generally on lines 12, 15c and 17).</p>




<table class="table">
<tr valign="top">
<td width="50" align="center">i.</td>
<td width="450">Mortgage or rent payments</td>
<td>$<input type="text" name="mortgage_rent" value="<?php echo $mortgage_rent; ?>" /></td>
</tr>

<tr valign="top">
<td align="center">ii.</td>
<td>Property taxes (if paid separately)</td>
<td>$<input type="text" name="property_tax" value="<?php echo $property_tax; ?>" /></td>
</tr>

<tr valign="top">
<td align="center">iii.</td>
<td>iii.Homeowners or renters insurance<br />(if paid separately)</td>
<td>$<input type="text" name="insurance" value="<?php echo $insurance; ?>" /></td>
</tr>

<tr valign="top">
<td align="center">iv.</td>
<td>Home maintenance and repair<br />(average last 12 months’ amounts)</td>
<td>$<input type="text" name="maintenance" value="<?php echo $maintenance; ?>" /></td>
</tr>

<tr valign="top">
<td align="center">v.</td>
<td>Utilities (include monthly gas, electric
water, heating oil, garbage collection,
residential telephone service,
cell phone service, cable television, and internet service)</td>
<td>$<input type="text" name="utilities" value="<?php echo $utilities; ?>" /></td>
</tr>

</table>


<p><span style="text-decoration:underline;">(c)Transportation Costs</span></p>
<table class="table">
<tr valign="top">
<td width="50" align="center">i.</td>
<td width="450">Vehicle payments (itemize per vehicle)</td>
<td>$<input type="text" name="vehicle_payments" value="<?php echo $vehicle_payments; ?>" /></td>
</tr>

<tr valign="top">
<td align="center">ii.</td>
<td>Monthly average costs of operating vehicles (including gas, routine maintenance,
monthly insurance cost)</td>
<td>$<input type="text" name="operating_costs" value="<?php echo $operating_costs; ?>" /></td>
</tr>

<tr class="tbt-0"><td colspan="3"><span class="bg_cyan">i AND ii ALLOW REPEAT FOR MULTIPLE VEHICLES</span></td></tr>

<tr valign="top">
<td align="center">iii.</td>
<td>Public transportation costs</td>
<td>$<input type="text" name="public_transportation" value="<?php echo $public_transportation; ?>" /></td>
</tr>

</table>

<p><span style="text-decoration:underline;">(d)Other Necessary Expenses</span></p>

<table class="table">
<tr valign="top">
<td width="50" align="center">i.</td>
<td width="450">Court-ordered alimony and child support payments (if not deducted from pay)<br /><span class="bg_yellow">You may refer to line 19 of Form 122A-2 or 122C-2 or Schedule J, line 18</span></td>
<td>$<input type="text" name="divorce_support" value="<?php echo $divorce_support; ?>" /></td>
</tr>


<tr valign="top">
<td align="center">ii.</td>
<td>Babysitting, day care, nursery and preschool costs<br />
<span class="bg_yellow">You may refer to line 21 of Form 122A-2 or 122C-2 or Schedule J, line 8 <sup>5</sup></span></td>
<td>$<input type="text" name="child_care_costs" value="<?php echo $child_care_costs; ?>" /></td>
</tr>

<tr valign="top" class="tbt-0">
<td align="center"></td>
<td colspan="2">Explain the circumstances making it necessary for you to expend this amount:<br />
<textarea name="explanation_of_child_care_costs" class="form-control"><?php echo $explanation_of_child_care_costs; ?></textarea>
</td></tr>


<tr valign="top">
<td align="center">iii.</td>
<td>Health insurance<br />(if not deducted from pay)<br /><span class="bg_yellow">You may refer to line 25 of the Means Test or Schedule J, line 15b</span></td>
<td>$<input type="text" name="health_insurance" value="<?php echo $health_insurance; ?>" /></td>
</tr>

<tr valign="top" class="tbt-0">
<td align="center"></td>
<td>Does the policy cover any persons other than yourself and your family members?</td>
<td>
	<label class="radio-inline"><input type="radio" name="radio_15_4" value="Yes" <?php if($radio_15_4 == "Yes") { echo " checked"; } ?> required="required"> Yes</label>
    <label class="radio-inline"><input type="radio" name="radio_15_4" value="No" <?php if($radio_15_4 == "No") { echo " checked"; } ?> required="required"> No</label>
</td>
</tr>


<tr valign="top">
<td align="center">iv.</td>
<td>Life insurance<br />(if not deducted from pay)<br /><span class="bg_yellow">You may refer to line 25 of the Means Test or Schedule J, line 15a</span></td>
<td>$<input type="text" name="life_insurance" value="<?php echo $life_insurance; ?>" /></td>
</tr>

<tr valign="top" class="tbt-0">
<td align="center"></td>
<td>Are the payments for a term policy covering your life?</td>
<td>
	<label class="radio-inline"><input type="radio" name="radio_15_5" value="Yes" <?php if($radio_15_5 == "Yes") { echo " checked"; } ?> required="required"> Yes</label>
    <label class="radio-inline"><input type="radio" name="radio_15_5" value="No" <?php if($radio_15_5 == "No") { echo " checked"; } ?> required="required"> No</label>
</td>
</tr>


<tr valign="top">
<td align="center">v.</td>
<td>Dependent care (for elderly or disabled family members)<br />
<span class="bg_yellow">You may refer to line 26 of the Means Test or Schedule J, line 19</span></td>
<td>$<input type="text" name="dependent_care" value="<?php echo $dependent_care; ?>" /></td>
</tr>

<tr valign="top" class="tbt-0">
<td align="center"></td>
<td colspan="2">Explain the circumstances making it necessary for you to expend this amount:<br />
<textarea name="explanation_of_dependent_care_costs" class="form-control"><?php echo $explanation_of_dependent_care_costs; ?></textarea>
</td></tr>


<tr valign="top">
<td align="center">vi.</td>
<td>Payments on delinquent federal, state or local tax debt<br /><span class="bg_yellow">You may refer to line 35 of the Means Test or Schedule J, line 16</span></td>
<td>$<input type="text" name="delinquent_tax" value="<?php echo $delinquent_tax; ?>" /></td>
</tr>

<tr valign="top" class="tbt-0">
<td align="center"></td>
<td>Are these payments being made pursuant to an agreement with the taxing authority?</td>
<td>
	<label class="radio-inline"><input type="radio" name="radio_15_6" value="Yes" <?php if($radio_15_6 == "Yes") { echo " checked"; } ?> required="required"> Yes</label>
    <label class="radio-inline"><input type="radio" name="radio_15_6" value="No" <?php if($radio_15_6 == "No") { echo " checked"; } ?> required="required"> No</label>
</td>
</tr>


<tr valign="top">
<td align="center">vii.</td>
<td>Payments on other student loans I am not seeking to discharge</td>
<td>$<input type="text" name="other_student_loans" value="<?php echo $other_student_loans; ?>" /></td>
</tr>

<tr valign="top">
<td align="center">viii.</td>
<td>Other expenses I believe necessary for a minimal standard of living.</td>
<td>$<input type="text" name="other_expenses" value="<?php echo $other_expenses; ?>" /></td>
</tr>

<tr valign="top" class="tbt-0">
<td align="center"></td>
<td colspan="2">Explain the circumstances making it necessary for you to expend this amount:<br />
<textarea name="explanation_of_other_expenses" class="form-control"><?php echo $explanation_of_other_expenses; ?></textarea>
</td></tr>

</table>

<p class="dcp_hint"><sup>5</sup> Line 8 of Schedule J allows listing of expenses for “childcare and children’s education costs.” You should not list any educational expenses for your children here, aside from necessary nursery or preschool costs</p>



<p style="margin-top:20px;">16. After deducting the foregoing monthly expenses from my household gross income, I have <input type="text" name="amount" value="<?php echo $amount; ?>" /> [amount] [no, or amount] remaining income.</p>
<p>17. In addition to the foregoing expenses, I anticipate I will incur additional monthly expenses in the future for my, and my dependents’, basic needs that are currently not met<sup>6</sup>. These include the following:
<textarea name="explanation_of_anticipated_expenses" class="form-control"><?php echo $explanation_of_anticipated_expenses; ?></textarea>
</p>

<p class="dcp_hint"><sup>6</sup> If you have forgone expenses for any basic needs and anticipate that you will incur such expenses in the future, you may list them here and explain the circumstances making it necessary for you to incur such expenses</p>


<h3 style="margin-top:20px;"><strong>III. FUTURE INABILITY TO REPAY STUDENT LOANS</strong></h3>

<p>18. For the following reasons, it should be presumed that my financial circumstances are unlikely to materially improve over a significant portion of the repayment period (answer all that apply):</p>

<p><input type="checkbox" name="checkbox_18_1" value="Yes" <?php if($checkbox_18_1 == "Yes") { echo " checked"; } ?> /> I am over the age of 65.</p>

<p><input type="checkbox" name="checkbox_18_2" value="Yes" <?php if($checkbox_18_2 == "Yes") { echo " checked"; } ?> /> The student loans I am seeking to discharge have been in repayment status for at least 10 years (excluding any period during which I was enrolled as a student).</p>

<p><input type="checkbox" name="checkbox_18_3" value="Yes" <?php if($checkbox_18_3 == "Yes") { echo " checked"; } ?> /> I did not complete the education for which I incurred the student loan[s].</p>

<p><input type="checkbox" name="checkbox_18_4" value="Yes" <?php if($checkbox_18_4 == "Yes") { echo " checked"; } ?> /> I have a permanent disability or chronic injury which renders me unable to work or limits my ability to work.</p>

<p>Describe the disability or injury and its effects on your ability to work, and indicate whether you receive any governmental benefits attributable to this disability or injury:
<textarea name="explanation_of_disability" class="form-control"><?php echo $explanation_of_disability; ?></textarea></p>

<p><input type="checkbox" name="checkbox_18_5" value="Yes" <?php if($checkbox_18_5 == "Yes") { echo " checked"; } ?> /> I have been unemployed for at least five of the past ten years.</p>
<p>Please explain your efforts to obtain employment.
<textarea name="explanation_of_job_hunting" class="form-control"><?php echo $explanation_of_job_hunting; ?></textarea></p>


<p>19. For the following additional reasons, my financial circumstances are unlikely to materially improve over a significant portion of the repayment period (answer all that apply):</p>
<p><input type="checkbox" name="checkbox_19_1" value="Yes" <?php if($checkbox_19_1 == "Yes") { echo " checked"; } ?> /> I incurred student loans I am seeking to discharge in pursuit of a degree I was unable to complete for reasons other than the closure of the educational institution.</p>

<p>Describe your reasons for being unable to complete the degree: <textarea name="explanation_of_not_completing_degree" class="form-control"><?php echo $explanation_of_not_completing_degree; ?></textarea></p>

<p><p><input type="checkbox" name="checkbox_19_2" value="Yes" <?php if($checkbox_19_2 == "Yes") { echo " checked"; } ?> /> I am not currently employed.</p>

<p><input type="checkbox" name="checkbox_19_3" value="Yes" <?php if($checkbox_19_3 == "Yes") { echo " checked"; } ?> /> I am currently employed, but I am unable to obtain employment in the field for which I am educated or have received specialized training.<p>
<p>Describe reasons for inability to obtain such employment, and indicate if you have ever been able to obtain such employment: <textarea name="explanation_of_job_inability_to_obtain_particular_employment" class="form-control"><?php echo $explanation_of_job_inability_to_obtain_particular_employment; ?></textarea></p>

<p><input type="checkbox" name="checkbox_19_4" value="Yes" <?php if($checkbox_19_4 == "Yes") { echo " checked"; } ?> /> I am currently employed, but my income is insufficient to pay my loans and unlikely to increase to an amount necessary to make substantial payments on the student loans I am seeking to discharge.</p>

<p>Please explain why you believe this is so:<textarea name="explanation_of_insufficiency_of_income" class="form-control"><?php echo $explanation_of_insufficiency_of_income; ?></textarea></p>


<p><input type="checkbox" name="checkbox_19_5" value="Yes" <?php if($checkbox_19_5 == "Yes") { echo " checked"; } ?> /> Other circumstances exist making it unlikely I will be able to make payments for a significant part of the repayment period.</p>
<p>Explain these circumstances: <textarea name="explanation_of_other_circumstances" class="form-control"><?php echo $explanation_of_other_circumstances; ?></textarea></p>



<h3 style="margin-top:20px;"><strong>IV. PRIOR EFFORTS TO REPAY LOANS</strong></h3>

<p>20. I have made good faith efforts to repay the student loans at issue in this proceeding, including the following efforts:</p>

<p>21. Since receiving the student loans at issue, I have made a total of $<input type="text" name="amount_paid_on_loan" value="<?php echo $amount_paid_on_loan; ?>" /> in payments on the loans, including the following:</p>
<p><input type="checkbox" name="checkbox_21_1" value="Yes" <?php if($checkbox_21_1 == "Yes") { echo " checked"; } ?> /> regular monthly payments of $<input type="text" name="regular_payment_amount" value="<?php echo $regular_payment_amount; ?>" /> each.</p>

<p><input type="checkbox" name="checkbox_21_2" value="Yes" <?php if($checkbox_21_2 == "Yes") { echo " checked"; } ?> /> additional payments, including $<input type="text" name="additional_payment_amount" value="<?php echo $additional_payment_amount; ?>" /> <span class="bg_cyan">[ALLOW FOR ADDITIONAL INPUTS].</span></p>

<p>22. I have received <input type="text" name="forbearance_deferment_from_litigation_package" value="<?php echo $forbearance_deferment_from_litigation_package; ?>" /> forbearances or deferments, for a period totaling <input type="text" name="forbearance_deferment_months_from_litigation_package" value="<?php echo $forbearance_deferment_months_from_litigation_package; ?>" /> months.</p>

<p>23. I have attempted to contact the company that services or collects on my student loans or the Department of Education at least <input type="text" name="contact_number" value="<?php echo $contact_number; ?>" /> times.</p></p>

<p>24. I have sought to enroll in one or more “Income Driven Repayment Programs” or similar repayment programs offered by the Department of Education, including the following:
Description of efforts: <textarea name="explanation_of_idr_attempts" class="form-control"><?php echo $explanation_of_idr_attempts; ?></textarea></p>

<p>25.[If you did not enroll in such a program]. I have not enrolled in an “Income Driven Repayment Program” or similar repayment program offered by the Department of Education for the following reasons: <textarea name="explanation_of_why_no_idr" class="form-control"><?php echo $explanation_of_why_no_idr; ?></textarea></p>

<p>26. Describe any other facts indicating you have acted in good faith in the past in attempting to repay the loan, including efforts to obtain employment, maximize your income, or minimize your expenses: <textarea name="explanation_of_other_good_faith_facts" class="form-control"><?php echo $explanation_of_other_good_faith_facts; ?></textarea></p>

<h3 style="margin-top:20px;"><strong>V. CURRENT ASSETS</strong></h3>

<p>27. I own the following parcels of real estate:<br />
<span class="bg_cyan">[ALLOW REPEAT FOR MULTIPLE PARCELS]</span></p>

<p>Address:	<textarea name="address_27" class="form-control"><?php echo $address_27; ?></textarea></p>

<p>Owners:<sup>7</sup>	<textarea name="owners_name" class="form-control"><?php echo $owners_name; ?></textarea></p>

<p>Fair market value: $<input type="text" name="fair_market_value" value="<?php echo $fair_market_value; ?>" /></p>

<p>Total balance of mortgages and other liens. $<input type="text" name="total_balance_of_mortgages_and_other_liens" value="<?php echo $total_balance_of_mortgages_and_other_liens; ?>" /></p>


<p>28. I own the following motor vehicles:<br />
<span class="bg_cyan">[ALLOW REPEAT FOR MULTIPLE VEHICLES]</span><br />
Make and model:	<input type="text" name="make_and_model" value="<?php echo $make_and_model; ?>" /></p>

<p>Fair market value:	$<input type="text" name="fair_market_value" value="<?php echo $fair_market_value; ?>" />

<p>Total balance of Vehicle loans And other liens $<input type="text" name="total_balance_of_vehicle_loans" value="<?php echo $total_balance_of_vehicle_loans; ?>" /></p>

<p>29. I hold a total of $<input type="text" name="retirement_amount" value="<?php echo $retirement_amount; ?>" /> in retirement assets, held in 401k, IRA and similar retirement accounts.</p>
<p>I own the following interests in a corporation, limited liability company, partnership, or other entity:</p>

<p><span class="bg_cyan">[ALLOW REPEAT FOR MULTIPLE ENITIES]</span>
<table class="table">
<tr class="tbt-0">	<td>Name of entity</td>	<td>State incorporated<sup>8</sup></td>	<td>Type<sup>9</sup> and %age Interest</td>	</tr>
<tr class="tbt-0">
<td><input type="text" name="name_of_entity" value="<?php echo $name_of_entity; ?>" /></td>
<td><input type="text" name="state_incorporated" value="<?php echo $state_incorporated; ?>" /></td>
<td><input type="text" name="type_and_interest" value="<?php echo $type_and_interest; ?>" /></td>
</tr>
</table>

<p>31. I currently am anticipating receiving a tax refund totaling $<input type="text" name="tax_refund" value="<?php echo $tax_refund; ?>" />.</p>


<h3 style="margin-top:20px;"><strong>VI. ADDITIONAL CIRCUMSTANCES</strong></h3>

<p>32. I submit the following circumstances as additional support for my effort to discharge my student loans as an “undue hardship” under 11 U.S.C. §523(a)(8):
<textarea name="explanation_of_additional_circumstances" class="form-control"><?php echo $explanation_of_additional_circumstances; ?></textarea></p>

<p>Pursuant to 28 U.S.C. § 1746, I declare under penalty of perjury that the foregoing is true and correct.</p>

<div class="clr"></div>
<div>
<div style="width:250px; margin:5px 0 25px 0; float:right;">
<div style="border-top:1px solid #000000; margin-top:50px;">Signature:</div>
<div style="border-top:1px solid #000000; margin-top:50px;">Name:</div>
<div style="border-top:1px solid #000000; margin-top:50px;">Date:</div>
</div>
</div>

<div class="clr"></div>
<p class="dcp_hint"><sup>7</sup> List by name all owners of record (self and spouse, for example)</p>
<p class="dcp_hint"><sup>8</sup> The state, if any, in which the entity is incorporated. Partnerships, joint ventures and some other business entities might not be incorporated.</p>
<p class="dcp_hint"><sup>9</sup> For example, shares, membership interest, partnership interest</p>

<p></p>


</div>




<div class="clr mb_20"></div>

</div>

</div>






</div>
</div>
</form>
</div>
<?php	$this->load->view("Admin/inc/template_js.php");	?>


</body></html>
<?php

}
}
?>