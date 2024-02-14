<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php
error_reporting(0);
@extract($_POST);

$sg_1 = $this->uri->segment(1);
$sg_3 = $this->uri->segment(3);
$client_id = $this->uri->segment(4);

//	Get Client Full Details
$user = $client_data['client'];
@extract($client_data['client']);
$icsr = $client_data['intake_client_status'];
$cmr = $client_data['case_manager'];
$cmpR = $client_data['users_company'];

$company_id = $user['company_id'];
// $intake_id = 1;
$qu = 0;
$au = 0;

if ($intake_id == 4) {
	$qu = 96;
	$au = 74;
}

$initial_intake_status = "Pending";
if (isset($icsr['status'])) {if ($icsr['status'] == "Complete") {$initial_intake_status = "Complete";}}

$fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
?>
<!DOCTYPE html>
<html>
<head>
<?php	$this->load->view("account/inc/head");?>
<style type="text/css">
.height_50 { height:50px;}
.show_on_print { display:none;}
.alert_list {}
.alert_list li { margin-bottom:10px;}
.scn_tbl { margin-bottom:10px;}
.scn_tbl th, .scn_tbl td { border:1px solid #CCCCCC; padding:0px; font-size:14px; vertical-align:top;}
.scn_tbl ul { margin:0px; padding:0px;}
.scn_tbl li { list-style:none; border-bottom:1px solid #CCCCCC; padding:5px; }
.scn_tbl li .form-control { padding:1px 5px; max-height:23px;}


@media print {
.show_on_print { display:block;}
.hide_on_print { display:none;}
.panel-primary { border-color: #337ab7; }
.panel-primary .panel-heading { color: #ffffff; background-color: #337ab7; border-color: #337ab7; }

.alert_list {}
.alert_list li { margin-bottom:10px;}
.scn_tbl { margin-bottom:10px;}
.scn_tbl th, .scn_tbl td { border:1px solid #CCCCCC; padding:0px; font-size:14px; vertical-align:top;}
.scn_tbl ul { margin:0px; padding:0px;}
.scn_tbl li { list-style:none; border-bottom:1px solid #CCCCCC; padding:5px; }
.scn_tbl li .form-control { padding:1px 5px; max-height:23px;}

.table_border {}
.table_border th, .table_border td { padding:5px; vertical-align:top; border:1px solid #000000;}

}

</style>


</head>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
<?php	//$this->load->view("Site/inc/header");	?>

<div class="content-wrapper">
<div class="container-fluid hide_on_print" style="padding-top:15px; border-bottom:1px solid #CCCCCC;">
<div>
<div style="width:auto; float:left; font-size:25px;">
<a href="<?php echo base_url($sg_1 . '/dashboard'); ?>">
<?php	if (file_exists($client_data['users_company']['logo'])) {echo '<img src="' . base_url($client_data['users_company']['logo']) . '" alt="' . $client_data['users_company']['name'] . '" style="max-height:50px;" />';} else {if (trim($client_data['company']['company_name']) != "") {echo '<b>' . $client_data['company']['company_name'] . '</b>';} else {echo '<b>' . $client_data['users_company']['name'] . '</b>';}}
?>
</a>
</div>

</div>
</div>

<div style="background:#FFFFFF;">
<div class="container-fluid">
    <section class="content-header">
<div class="row">
<div class="col-md-8">
    <h3 style="margin-top:0px;"><strong>Analysis for </strong> <?php echo $intake[2]['ans']['intake_comment_body'] . ", " . $intake[1]['ans']['intake_comment_body']; ?> (#<?php echo $user['id']; ?>)
    	<?php
if ($intake_id == 4) {
	?>
    		- (Update Intake Program)
    		<?php

} elseif ($intake_id == 1) {
	?>
    		- (Intake Program)
    		<?php

}
?>
    </h3>
    <p>
    	<i class="fa fa-phone"></i> <a href="tel:<?php echo $intake[3]['ans']['intake_comment_body']; ?>"><?php echo substr($intake[3]['ans']['intake_comment_body'], 0, 3) . '-' . substr($intake[3]['ans']['intake_comment_body'], 3, 3) . '-' . substr($intake[3]['ans']['intake_comment_body'], 6); ?></a> &nbsp; &nbsp;
        <i class="fa fa-envelope"></i> <a href="mailto:<?php echo $user['email']; ?>"><?php echo $user['email']; ?></a> &nbsp; &nbsp;
    	<i class="fa fa-map-marker"></i> <?php echo $intake[4]['ans']['intake_comment_body']; ?>
    </p>
</div>

<div class="col-md-4">
<a href="<?php echo base_url("account/customer/view/" . $this->uri->segment(4)); ?>" class="btn btn-info hide_on_print pull-right"><i class="fa fa-long-arrow-left"></i> Back</a>
<a href="<?php echo base_url("account/customer/intake_summary/" . $this->uri->segment(4)); ?>" class="btn btn-primary hide_on_print pull-right" style="margin-right:10px;"><i class="fa fa-list"></i> Intake Summary</a>
</div>
</div>

      <?php	$this->load->view("template/alert.php");?>
    </section>

    <!-- Main content -->
    <section class="content">
<?php

//$q = $this->db->query("SELECT sum(loan_outstanding_principal_balance) as prncpl, sum(loan_outstanding_interest_balance) as intrst FROM nslds_loans where client_id='".$client_id."' and sltb_code_id='1'");
/*
$q = $this->db->query("SELECT sum(loan_outstanding_principal_balance) as prncpl, sum(loan_outstanding_interest_balance) as intrst FROM nslds_loans where client_id='" . $client_id . "' and intake_file_result_id=" . $intake[6]['ans']['intake_file_id'] . " and (loan_status='PZ' or loan_status='PD' or loan_status='PF' or loan_status='PM' or loan_status='PX' or loan_status='AL' or loan_status='BC' or loan_status='CS' or loan_status='DC' or loan_status='DD' or loan_status='DE' or loan_status='DI' or loan_status='DP' or loan_status='DS' or loan_status='DW' or loan_status='FC' or loan_status='OD' or loan_status='VA')");
 */

$q = $this->db->query("select sum(loan_outstanding_principal_balance) as prncpl, sum(loan_outstanding_interest_balance) as intrst FROM nslds_loans where client_id='" . $client_id . "' and intake_file_result_id=" . $intake[6]['ans']['intake_file_id'] . " and loan_type like '%parent%' and loan_outstanding_principal_balance > 0");

$nr_ppl = $q->num_rows();
$nr_pplR = $q->row_array();

$q = $this->db->query("SELECT sum(loan_outstanding_principal_balance) as prncpl, sum(loan_outstanding_interest_balance) as intrst FROM nslds_loans where client_id='" . $client_id . "' and is_ffel='1'");
$nr_ffel = $q->num_rows();
$nr_ffelR = $q->row_array();

$q = $this->db->query("SELECT * FROM nslds_loans where client_id='" . $client_id . "' and (loan_status='DA' or loan_status='DO' or loan_status='FB' or loan_status='ID' or loan_status='IG' or loan_status='IM' or loan_status='IP')");
$nr_stts_1 = $q->num_rows();

$q = $this->db->query("SELECT * FROM nslds_loans where client_id='" . $client_id . "' and (loan_status='DF' or loan_status='DL' or loan_status='DU' or loan_status='DX' or loan_status='DZ' or loan_status='XD')");
$nr_stts_2 = $q->num_rows();

$intake[43]['ans']['body'] = $this->default_model->get_arrby_tbl_single('intake_answer', '*', "intake_answer_id='" . $intake[43]['ans']['intake_answer_id'] . "' and intake_question_id='" . ($qu + 43) . "'", '1');

$client_document = $this->crm_model->document_decrypt($intake[6]['ans']['intake_file_location']);
//echo $client_document;

$location = strtolower(trim($intake[4]['ans']['intake_comment_body']));
if ($location == "hawaii" || $location == "hi" || $location == "sh") {
	$location = "HI";
} else if ($location == "alaska" || $location == "ak" || $location == "sa") {
	$location = "AK";
} else {}

#######	Federal Student Loan Debt	#####
$principal = $interest = $total_loan = 0;
$q = $this->db->query("SELECT sum(loan_outstanding_principal_balance) as prncpl, sum(loan_outstanding_interest_balance) as intrst FROM nslds_loans where client_id='" . $client_id . "'");
if ($q->num_rows() > 0) {
	$nlnR = $q->row_array();

	$principal = $nlnR['prncpl'];
	$interest = $nlnR['intrst'];
	$total_loan = ($principal + $interest);
}

if (!isset($rehab_required)) {$rehab_required = "0";}
if (!isset($consolidation_included)) {$consolidation_included = "0";}

$intake_file_result_id = $intake[6]['ans']['intake_file_id'];
$q = $this->db->query("SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' and loan_date<'2014-07-01' order by id asc");
$nr = $q->num_rows();
if ($nr == 0) {$ibrname = "New IBR";} else { $ibrname = "IBR";}

$consent = '';

if (isset($intake_client) && !empty($intake_client['id'])) {
	$formdata = (array) json_decode($intake_client['form_data']);
	$consent = $formdata['consent'];
}
?>


<form action="" method="post" enctype="multipart/form-data" name="current_analysis_form" id="current_analysis_form">
<input type="hidden" name="client_id" value="<?php	echo $client_id; ?>" />
<input type="hidden" name="company_id" value="<?php	echo $company_id; ?>" />
<input type="hidden" name="intake_id" value="<?php	echo $intake_id; ?>" />
<input type="hidden" name="nslds_id" value="<?php	echo $intake[6]['ans']['intake_file_id']; ?>" />
<input type="hidden" name="location" value="<?php	echo $location; ?>" />
<input type="hidden" name="intake_file_result_id" value="<?php	echo $intake_file_result_id; ?>" />
<input type="hidden" name="scenario_selected" value="<?php	echo $car['scenario_selected']; ?>" />
<input type="hidden" name="payment_plan_selected" value="<?php	echo $car['payment_plan_selected']; ?>" />
<input type="hidden" name="consent" value="<?php echo $consent; ?>" />



<input type="hidden" name="rehab_required" value="<?php	echo $rehab_required; ?>" />
<input type="hidden" name="consolidation_included" value="<?php	echo $consolidation_included; ?>" />
<input type="hidden" name="deferment_forbearance_status" value="0" />

<div class="box box-primary box-solid">
<div class="box-header with-border">
  <h3 class="box-title">1. ANALYSIS ALERTS</h3>
  <div class="box-tools pull-right"><button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button></div>
  <!-- /.box-tools -->
</div>
<!-- /.box-header -->
<div class="box-body" style="display: block;">

<ul class="alert_list">
<?php

if ($nr_ppl > 0) {if (($nr_pplR['prncpl'] + $nr_pplR['intrst']) > 0) {echo '<li>Parent PLUS loans present</li>';}}
if ($nr_ffel > 0) {if (($nr_ffelR['prncpl'] + $nr_ffelR['intrst']) > 0) {echo '<li>FFEL loans present</li>';}}
if ($nr_stts_1 > 0) {echo '<li><strong>Deferment/Forbearance active</strong>
	<div>
    <label class="radio-inline"><input type="radio" name="deferment_forbearance_status" value="1">Terminate deferment/forbearance immediately</label>
	<label class="radio-inline"><input type="radio" name="deferment_forbearance_status" value="2">Keep deferment/forbearance active</label>
    </div>
	</li>';}
if ($nr_stts_2 > 0) {
	echo '<li>
        	<strong>Defaulted loans detected</strong>
            <div>
            <div class="checkbox"><label><input type="checkbox" name="rehab_required" value="1" />Rehabilitation</label></div>
			<div  class="checkbox"><label><input type="checkbox" name="consolidation_included" value="1">Consolidation</label></div>
            </div>
        </li>';
	$in_default = 1;
} else { $in_default = 0;}

$arr_8 = explode(",", $intake[8]['ans']['intake_answer_id_checkbox']);
if (in_array(($au + 7), $arr_8)) {echo '<li>Borrower indicated that their school closed while attending.</li>';}
if (in_array(($au + 8), $arr_8)) {echo '<li>Borrower indicated that they were the victim of ID theft.</li>';}
if (in_array(($au + 9), $arr_8)) {echo '<li>Borrower indicated that they were defrauded by their school.</li>';}

if (trim($intake[9]['ans']['intake_comment_body']) != "") {echo '<li>Borrower indicated another reason the NSLDS information does not look correct. Specifically, they stated: <span style="color:#666666;">' . trim($intake[9]['ans']['intake_comment_body']) . '</span></li>';}

if (trim($intake[10]['ans']['intake_answer_id']) == ($au + 12)) {
	echo '<li>Borrower indicates they believe they have a spousal consolidation loan.</li>';
	$consolidation_included = 1;
	//if(!isset($consolidation_included) || ) {	$consolidation_included = 1;	} else {	if(!isset($rehab_required)) {	$rehab_required = 1;	}	}
} else { $rehab_required = 1;}

if (trim($intake[22]['ans']['intake_answer_id']) == ($au + 22)) {echo '<li>The Borrow may be eligible for Teacher Loan Forgiveness</li>';}

if ($intake[23]['ans']['intake_answer_id'] == ($au + 24) || $intake[24]['ans']['intake_answer_id'] == ($au + 26)) {echo '<li>Borrower indicates that they work for a qualifying PSLF employer.</li>';} else {

	if ($intake[25]['ans']['intake_answer_id'] == ($au + 28)) {
		$tmp_arr_q_26 = explode(",", $intake[26]['ans']['intake_answer_id_checkbox']);
		if (count($tmp_arr_q_26) > 0) {
			if (!in_array("43", $tmp_arr_q_26)) {
				echo '<li>E Borrower indicates that they work for a qualifying PSLF employer</li>';
			}}
	}
}

if (trim($intake[27]['ans']['intake_answer_id']) == ($au + 44)) {echo '<li>Borrower indicates that they have a disability that affects their ability to work.</li>';}

// Bankruptcy Alert
$bankruptcy_alert = "Yes";
if (isset($car['prong'])) {
	if (trim($car['prong']) != "") {

		$prong = json_decode($car['prong'], true);
		if (isset($prong[1])) {
			if ($prong[1] == "Yes") {
//if(isset($prong[3])) { if(count($prong[3])>0) {

				if (isset($prong['2_1']) || isset($prong['2_2']) || isset($prong['2_3_0']) || isset($prong['2_4']) || isset($prong['2_5']) || isset($prong['2_6'])) {$bankruptcy_alert = "No";}

//}}
			}}
	}}

if ($bankruptcy_alert == "Yes") {

	$int_and_30R = '';

	if (isset($intake[30]['ans']['intake_answer_id'])) {
		if ($intake[30]['ans']['intake_answer_id'] != ($au + 53) && trim($intake[30]['ans']['intake_answer_id']) != "" && trim($intake[30]['ans']['intake_answer_id']) > 0) {

			$int_and_30R = $this->default_model->get_arrby_tbl_single('intake_answer', '*', "intake_answer_id='" . $intake[30]['ans']['intake_answer_id'] . "'", '1');

			// echo '<li>Borrower indicates that they have filed or plan on filing a bankruptcy, specifically a Chapter <span style="color:#666666;">' . trim($int_and_30R['intake_answer_body']) . '</span>.</li>';
		}
	}

	if (trim($intake[28]['ans']['intake_answer_id']) == ($au + 46)) {
		if (trim($intake[29]['ans']['intake_comment_body']) != "") {echo '<li>Borrower\'s bankruptcy is currently in Chapter <span style="color:#666666;">' . trim($int_and_30R['intake_answer_body']) . '</span> which was filed on <span style="color:#666666;">' . trim($intake[29]['ans']['intake_comment_body']) . '</span></li>';}
	}
	if (trim($intake[28]['ans']['intake_answer_id']) == ($au + 48)) {
		if (trim($intake[29]['ans']['intake_comment_body']) != "") {echo '<li>Borrower is planning to file a bankruptcy, specifically Chapter <span style="color:#666666;">' . trim($int_and_30R['intake_answer_body']) . '</span></li>';}
	}

}
// END Bankruptcy Alert

if (isset($intake[31]['ans']['intake_answer_id'])) {
	if ($intake[31]['ans']['intake_answer_id'] == ($au + 54) && trim($intake[31]['ans']['intake_answer_id']) != "") {
		echo '<li>Borrower indicates that their wages are being garnished due to their student loan.</li>';
		$wage_garnishment_exists = 1;} else { $wage_garnishment_exists = 0;}} else { $wage_garnishment_exists = 0;}

if (isset($intake[32]['ans']['intake_answer_id'])) {
	if (trim($intake[32]['ans']['intake_answer_id']) == ($au + 56)) {

		$int_33file_text = '';

		if (isset($intake[33]['ans']['intake_file_id'])) {
			$document_33 = $this->crm_model->document_decrypt($intake[33]['ans']['intake_file_location']);
			if (file_exists($document_33)) {
				//$file_data = read_file($document_33);
				//$arr_file_data_33 = preg_split("/\r\n|\n|\r/", $file_data);
				//$int_33file_text = '<br /><a href="javascript:void(0)" class="btn btn-link" data-toggle="modal" data-target="#myModal_file_33">View File</a>';
				$int_33file_text = '<br /><a href="' . base_url($sg_1 . "/customer/current_analysis/" . $client_id . "?download=" . $intake[33]['ans']['intake_file_id']) . '" class="btn btn-info" target="_blank">View File</a>';

			}
		}
		echo '<li>Borrower indicates that they were sued for their Federal loans.' . $int_33file_text . '</li>';
	}
}

if (isset($intake[7]['ans']['intake_answer_id'])) {
	if (trim($intake[7]['ans']['intake_answer_id']) == ($au + 5)) {

		if (isset($intake[9]['ans']['intake_comment_body'])) {echo '<li>.' . $intake[9]['ans']['intake_comment_body'] . '</li>';}
	}
}

?>
</ul>

</div>
<!-- /.box-body -->
</div>


<input type="hidden" name="wage_garnishment_exists" value="<?php	echo $wage_garnishment_exists; ?>" />
<input type="hidden" name="in_default" value="<?php	echo $in_default; ?>" />




<div class="box box-primary box-solid">
<div class="box-header with-border">
    <h3 class="box-title">2. CLIENT GOALS FOR LOANS</h3>
    <div class="box-tools pull-right"><button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button></div>
</div>

  <div class="box-body">
<?php	if (isset($intake[45]['ans']['intake_comment_body'])) {if (trim($intake[45]['ans']['intake_comment_body']) != "") {echo '<p><strong>Client stated additional things they would like known:</strong> ' . $intake[45]['ans']['intake_comment_body'] . '</p>';}}?>

<?php	if (isset($intake[46]['ans']['intake_comment_body'])) {if (trim($intake[46]['ans']['intake_comment_body']) != "") {echo '<p><strong>Client\'s goals are:</strong> ' . $intake[46]['ans']['intake_comment_body'] . '</p>';}}?>

<?php	if (trim($intake[45]['ans']['intake_comment_body']) == "" && trim($intake[46]['ans']['intake_comment_body']) == "") {echo '<p>No data found.</p>';}?>

  </div>
</div>


<div class="box box-primary box-solid">
<div class="box-header with-border">
    <h3 class="box-title">3. PAYMENT SCENARIOS</h3>
    <div class="box-tools pull-right"><button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button></div>
</div>

  <div class="box-body">
<?php	//if(trim($intake[5]['ans']['intake_answer_id']) != "2") {	?>
<?php	if (trim($intake[5]['ans']['intake_answer_id']) != ($au + 2) || $initial_intake_status == "Complete") {
	?>

<div>
<div>
<table class="table table-bordered" style="width:auto;">
<tr>	<th width="150">Total Loan Balance</th>	<th width="150">Total Principal</th>	<th>Total Interest</th>	</tr>
<tr>
	<td><?php	echo $fmt->formatCurrency(round($total_loan), "USD") ?></td>
    <td><?php	echo $fmt->formatCurrency(round($principal), "USD") ?></td>
    <td><?php	echo $fmt->formatCurrency(round($interest), "USD") ?></td>
</tr>
</table>
</div>


<table class="table table-bordered">
<tr>	<td colspan="10"><strong>Client Information</strong></td>	</tr>
<tr><td colspan="10" class="psci_form"><div class="row">
<?php
$q = $this->db->query("select * from intake_answer where intake_question_id='" . ($qu + 11) . "' order by placement_order");
	$arr_q11 = $q->result_array();

	$q = $this->db->query("select * from intake_answer where intake_question_id='" . ($qu + 14) . "' order by placement_order");
	$arr_q14 = $q->result_array();

	if ($car['client_agi'] <= 0) {$car['client_agi'] = "";}
	if ($car['client_monthly'] <= 0) {$car['client_monthly'] = "";}
	if ($car['spouse_agi'] <= 0) {$car['spouse_agi'] = "";}
	if ($car['spouse_monthly'] <= 0) {$car['spouse_monthly'] = "";}

	$id_spouse_agi = $car['spouse_agi'];
	$id_spouse_monthly = $car['spouse_monthly'];

	$marital_status = $car['marital_status'];
	$file_joint_or_separate = $car['file_joint_or_separate'];

	$ca_fjs = $ca_sps_incm = "disp_block";
	if ($marital_status == ($au + 15)) {
		//if($file_joint_or_separate != "18") {	$ca_sps_incm = "disp_none";	}
	} else if ($marital_status == ($au + 72)) {
		$ca_fjs = $ca_sps_incm = "disp_none";
	} else if ($marital_status == ($au + 73)) {
		$ca_fjs = $ca_sps_incm = "disp_none";
	} else {
		$ca_fjs = $ca_sps_incm = "disp_none";
		$car['spouse_agi'] = $car['spouse_monthly'] = "";
	}

	$family_size_actual = ($intake[19]['ans']['intake_comment_body'] + $intake[20]['ans']['intake_comment_body'] + 1);
	if (isset($car['par_csd']) && trim($car['par_csd']) == "" && $car['marital_status'] == ($au + 15) && $car['file_joint_or_separate'] == ($au + 19)) {$car['family_size'] -= 1;}

	?>
<input type="hidden" id="marital_status_primary" value="<?php echo $car['marital_status']; ?>" />
<input type="hidden" id="file_joint_or_separate_primary" value="<?php echo $car['file_joint_or_separate']; ?>" />
<input type="hidden" id="intake_idtest" value="<?php echo $intake_id; ?>" />

<input type="hidden" id="id_spouse_agi" name="id_spouse_agi" value="<?php echo $id_spouse_agi; ?>" />
<input type="hidden" id="id_spouse_monthly" name="id_spouse_monthly" value="<?php echo $id_spouse_monthly; ?>" />


<div class="col-md-3"><div class="form-group"><label for="usr">Family Size</label><input type="number" class="form-control" name="family_size" id="family_size" value="<?php echo $car['family_size']; ?>" min="1" required onChange="change_cachangefjs()" /></div></div>
<div class="col-md-3"><div class="form-group"><label for="usr">Marital Status</label><select class="form-control" name="marital_status" id="ca_marital_status" onChange="change_camsfjs()"><?php foreach ($arr_q11 as $r) {?><option value="<?php echo $r['intake_answer_id']; ?>" <?php if ($r['intake_answer_id'] == $car['marital_status']) {echo " selected";}?>><?php echo $r['intake_answer_body']; ?></option><?php }?></select></div></div>

<div class="col-md-3"><div class="form-group ca_fjs <?php echo $ca_fjs; ?>"><label for="usr">File Joint or Separate</label>
<!--<select class="form-control" name="file_joint_or_separate" id="ca_file_joint_or_separate" onChange="change_camsfjs()">-->
<select class="form-control" name="file_joint_or_separate" id="ca_file_joint_or_separate" onChange="change_camsfjs()">
<?php foreach ($arr_q14 as $r) {?><option value="<?php echo $r['intake_answer_id']; ?>" <?php if ($r['intake_answer_id'] == $car['file_joint_or_separate']) {echo " selected";}?>><?php echo $r['intake_answer_body']; ?></option><?php }?></select></div></div>

<div class="clr"></div>

<div class="col-md-3"><div class="form-group"><label for="usr">Client AGI</label><input type="text" class="form-control" name="client_agi" value="<?php echo (float) $car['client_agi']; ?>" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1').replace(/^0[^.]/, '0');" /></div></div>
<div class="col-md-3"><div class="form-group"><label for="usr">Client Current Monthly (annualized)</label><input type="text" class="form-control" name="client_monthly" value="<?php echo (float) $car['client_monthly']; ?>" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1').replace(/^0[^.]/, '0');" /></div></div>
<div class="col-md-3"><div class="form-group ca_sps_incm <?php echo $ca_sps_incm; ?>"><label for="usr">Spouse AGI</label><input type="text" class="form-control" id="spouse_agi" name="spouse_agi" value="<?php echo (float) $car['spouse_agi']; ?>" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1').replace(/^0[^.]/, '0');" /></div></div>
  <!-- onChange="change_camsfjs()" -->
<div class="col-md-3"><div class="form-group ca_sps_incm <?php echo $ca_sps_incm; ?>"><label for="usr">Spouse Current Monthly (annualized) </label><input type="text" class="form-control" id="spouse_monthly" name="spouse_monthly" value="<?php echo (float) $car['spouse_monthly']; ?>" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1').replace(/^0[^.]/, '0');" /></div></div>
 <!-- onChange="change_camsfjs()" -->

<div class="col-md-12">
<button type="button" class="btn btn-primary" onClick="recalculate_ca_ps('<?php echo base_url("account/client_current_analysis_payment_scenario"); ?>')"><i class="fa fa-calculator"></i> Recalculate</button> &nbsp; &nbsp;

<a href="<?php	echo base_url($cmpR['slug'] . "/customer/current_analysis/" . $client_id . "/reset_analysis"); ?>" class="btn btn-info" onClick="return reset_current_analysis()"><i class="fa fa-refresh"></i> Reset</a>


</div>

</div></td></tr>
</table>

<div class="table-responsive"><table class="table table-bordered" id="car_tbl"></table></div>

<div class="alert alert-info" style="background:#d9edf7 !important; color:#31708f !important; border-color:#d9edf7;"><strong>Disclaimer:</strong> These numbers are estimates. Only the servicer can give exact numbers.</div>

</div>
<?php	} else {?>
<div>NSLD File not found.</div>
<?php	}?>
  </div>
</div>



<?php
if ($initial_intake_status == "Complete") {
	?>
<div style="margin:15px 0px;">
<a href="javascript:void(0)" class="btn btn-info" data-toggle="modal" data-target="#myModal_nslds" onClick="view_nslds_snapshot_body('<?php	echo base_url("account/view_nslds_snapshot/" . $client_id) ?>', 'nslds_snapshot_body')"><i class="fa fa-image"></i> View NSLDS Snapshot</a>

<a href="javascript:void(0)" class="btn btn-info" data-toggle="modal" data-target="#myModal_nslds_file"><i class="fa fa-file-text-o"></i> View NSLDS File</a> &nbsp;

</div>

<div id="myModal_nslds" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><strong>NSLDS Snapshot</strong></h4>
      </div>
      <div class="modal-body" id="nslds_snapshot_body"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<div id="myModal_nslds_file" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><strong>NSLDS File</strong></h4>
      </div>
      <div class="modal-body"><?php
$file_data = $this->crm_model->client_nslds_file_data($client_id);
	$arr_file_data = preg_split("/\r\n|\n|\r/", $file_data);
	foreach ($arr_file_data as $v) {echo $v . "<br />";}
	?></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>



<?php	} else {?>
<div class="alert" style="background-color:#f2dede; border-color:#ebccd1;">
<p style="color: #a94442; font-weight:bold;">NSLDS File not found</p>
<form action="" method="post" enctype="multipart/form-data">
<p><button type="submit" name="submit_send_intake" class="btn btn-primary">Send intake</button></p>
</form>
</div>
<?php	}?>


<div class="box box-primary box-solid">
<div class="box-header with-border">
  <h3 class="box-title">4. INTERNAL NOTES (Notes for you and your firm only)</h3>
  <div class="box-tools pull-right"><button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button></div>
</div>
<div class="box-body" style="display: block;">
<?php
$varname = "ca_in_" . $client_id;

if (trim($this->session->userdata($varname)) != "") {$car['internal_notes'] = $this->session->userdata($varname);}

$this->session->unset_userdata($varname);
?>

  	<div class="form-group"><textarea class="form-control" rows="5" name="internal_notes" id="ca_internal_notes"><?php echo $car['internal_notes']; ?></textarea></div>
  </div>
</div>



<div class="box box-primary box-solid">
<div class="box-header with-border">
  <h3 class="box-title" style="text-transform:uppercase;">5. Federal Loan Bankruptcy Discharge Evaluation</h3>
  <div class="box-tools pull-right"><button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button></div>
</div>

<div class="box-body" style="display: block;">

<?php	//if(trim($intake[28]['ans']['intake_answer_id'])=="46" || trim($intake[28]['ans']['intake_answer_id'])=="48") {

$arr_prong2 = ["1" => "I am over the age of 65",
	"2" => "The loans to be discharged have been in repayment status for at least 10 years (not including in-school time)",
	"3" => "I did not complete the education for which I received these loans",
	"4" => "I have a permanent disability or chronic injury which renders me unable to work or limits my ability to work",
	"5" => "I have been unemployed for at least 5 of the last 10 years",
	"6" => "Additional reasons for this prong",
];

$arr_prong2_3 = ["1" => "School closed", "2" => "Other reasons"];
$arr_prong2_6 = ["1" => "I am currently unemployed",
	"2" => "I am currently employed, but cannot work in the field in which I'm educated or received special training",
	"3" => "I am currently employed, but income not likely to increase enough to make substantial payments on the loans",
	"4" => "For other reasons I am not likely to make payments for a significant part of the repayment period"];

$arr_prong3 = ["1" => "I am or have made payments",
	"2" => "I have applied for a deferment or forbearance (other than in-school or grace period deferments)",
	"3" => "I have applied for an IDR plan",
	"4" => "I have applied for a federal consolidation loan",
	"5" => "I have responded to outreach from a servicer or collector",
	"6" => "I have engaged meaningfully with Education or my servicer, regarding payment options, forbearance and deferment options, or loan consolidation",
	"7" => "I have engaged meaningfully with a third party that has/can assist me in managing my student loan debt."];

if (isset($car['prong'])) {if (trim($car['prong']) != "") {$prong = json_decode($car['prong'], true);}}

?>

<div class="form-group">
<label for="5_1">Prong 1: Current Income and Expenses:</label><br />
Is your client's income minus expenses LESS than the Standard Repayment plan as calculated above <span id="section_5_sppa"></span>
<div>
<label class="radio-inline"><input type="radio" name="prong[1]" value="Yes" <?php if (isset($prong[1])) {if ($prong[1] == "Yes") {echo " checked";}}?> /> Yes</label> &nbsp;
<label class="radio-inline"><input type="radio" name="prong[1]" value="No" <?php if (isset($prong[1])) {if ($prong[1] == "No") {echo " checked";}}?> /> No</label>
</div>

<div style="background:#F8F8F8; padding:10px 15px; margin-top:15px; color:#000000;">
<p>Hint: If debtor has filed a bankruptcy within the past 18 months OR is in an active bankruptcy where schedules I and J have been modified within the past 18 months, use that information for this section. Means test lines 6 and 7 can also be used if not older than 18 months.</p>

<p>Otherwise, Use an estimated I/J or means test and calculate them, estimating income and expenses and then compare to the Standard Repayment.</p>
</div>

</div>

<div class="form-group" style="margin-top:25px;">
<label for="5_2">Prong 2: Future Inability to Repay Student Loans:</label>
<!--<input type="checkbox" name="" value="all" onChange="toggle_check_box_byclass(this, 'prong_chk_2')" />-->
<div style="margin-bottom:15px; font-style:italic;">Please check all of the following that apply to your client.</div>


<div class="checkbox"><label><input type="checkbox" class="prong_chk_2" name="prong[2_1]" value="<?php echo $arr_prong2[1]; ?>"<?php if (isset($prong['2_1'])) {if ($prong['2_1'] == $arr_prong2[1]) {echo " checked";}}?> /> <?php echo $arr_prong2[1]; ?></label></div>
<div class="checkbox"><label><input type="checkbox" class="prong_chk_2" name="prong[2_2]" value="<?php echo $arr_prong2[2]; ?>"<?php if (isset($prong['2_2'])) {if ($prong['2_2'] == $arr_prong2[2]) {echo " checked";}}?> /> <?php echo $arr_prong2[2]; ?></label></div>

<div>
<div class="checkbox"><label><input type="checkbox" class="prong_chk_2" name="prong[2_3_0]" id="prong_2_3_0" value="<?php echo $arr_prong2[3]; ?>"<?php if (isset($prong['2_3_0'])) {if ($prong['2_3_0'] == $arr_prong2[3]) {echo " checked";}}?> onChange="change_prong_2_3_0(this.id)" /> <strong><?php echo $arr_prong2[3]; ?></strong></label></div>


<?php
$disp_prong_2_3_0 = 'none';
if (isset($prong['2_3_0'])) {if ($prong['2_3_0'] == $arr_prong2[3]) {$disp_prong_2_3_0 = "block";}}
?>

<div id="disp_prong_2_3_0" style="margin-left:20px; display:<?php echo $disp_prong_2_3_0; ?>;">
	<div class="radio"><label><input type="checkbox" name="prong[2_3_1_0]" value="<?php echo $arr_prong2_3[1]; ?>" <?php if (isset($prong['2_3_1_0'])) {if ($prong['2_3_1_0'] == $arr_prong2_3[1]) {echo " checked";}}?> /> <?php echo $arr_prong2_3[1]; ?></label></div>
    <!--<div class="radio"><label><input type="checkbox" name="prong[2_3_1_1]" value="<?php echo $arr_prong2_3[2]; ?>"  onChange='$("#prong_2_3_1_other_reason").show();' <?php if (isset($prong['2_3_1_1'])) {if ($prong['2_3_1_1'] == $arr_prong2_3[2]) {echo " checked";}}?> /> <?php echo $arr_prong2_3[2]; ?></label></div>-->

    <?php
$disp_prong_2_3_1_other_reason = 'none';
$disp_prong_2_3_1_other_reason = 'block';
//if(isset($prong['2_3_1_0'])) { if($prong['2_3_1_0'] == $arr_prong2_3[2]) { $disp_prong_2_3_1_other_reason = "block"; } }
?>
    <div class="form-group" id="prong_2_3_1_other_reason" style="margin-left:20px; display:<?php echo $disp_prong_2_3_1_other_reason; ?>;"><label for="5_2" style="font-weight:normal;">Other reason</label><input type="text" class="form-control" name="prong[2_3_1][other_reason]" value="<?php if (isset($prong['2_3_1']['other_reason'])) {echo $prong['2_3_1']['other_reason'];}?>" /></div>

</div>

</div>


<div class="checkbox"><label><input type="checkbox" class="prong_chk_2" name="prong[2_4]" value="<?php echo $arr_prong2[4]; ?>" <?php if (trim($intake[27]['ans']['intake_answer_id']) == ($au + 44)) {echo " checked";}?> /> <?php echo $arr_prong2[4]; ?></label></div>
<div class="checkbox"><label><input type="checkbox" class="prong_chk_2" name="prong[2_5]" value="<?php echo $arr_prong2[5]; ?>" <?php if (isset($prong['2_5'])) {if ($prong['2_5'] == $arr_prong2[5]) {echo " checked";}}?> /> <?php echo $arr_prong2[5]; ?></label></div>

<div>
<div class="checkbox"><label><input type="checkbox" class="prong_chk_2" name="prong[2_6]" value="<?php echo $arr_prong2[6]; ?>" <?php if (isset($prong['2_6'])) {if ($prong['2_6'] == $arr_prong2[6]) {echo " checked";}}?> /> <strong><?php echo $arr_prong2[6]; ?></strong></label></div>

<div style="margin-left:20px;">
<?php foreach ($arr_prong2_6 as $k => $res_2_6) {
	$checked = "";
	if (isset($prong['2_6_1'])) {
		if ($prong['2_6_1'][$k] == $res_2_6) {$checked = " checked";}
		//$arr_res_2_6 = explode(", ", $prong['2_6_1']);
		//if(in_array($res_2_6, $arr_res_2_6)) {	$checked = " checked";	}
	}
	?>
	<div class="radio"><label><input type="checkbox" class="prong_chk_2" name="prong[2_6_1][<?php echo $k; ?>]" value="<?php echo $res_2_6; ?>" <?php echo $checked; ?> /> <?php echo $res_2_6; ?></label></div>
<?php }?>
</div>

</div>

</div>


<div class="form-group" style="margin-top:25px;">
<label for="5_2">Prong 3: Prior Efforts to Repay Loans:</label>
<!--<input type="checkbox" name="" value="all" onChange="toggle_check_box_byclass(this, 'prong_chk_3')" />-->
<div style="margin-bottom:15px; font-style:italic;">Please check all of the following that apply to your client.</div>

<?php foreach ($arr_prong3 as $k => $res_3) {
	$checked = "";
	if ($k == 7) {$checked = " checked";}
	if (isset($prong[3][$k])) {if ($prong[3][$k] == $res_3) {$checked = " checked";}}
	?>
	<div class="checkbox"><label><input type="checkbox" class="prong_chk_3" name="prong[3][<?php echo $k; ?>]" value="<?php echo $res_3; ?>" <?php echo $checked; ?> /> <?php echo $res_3; ?></label></div>
<?php }?>
</div>


<p>If you answered yes to Prong 1 and have checked at least one box in both Prongs 2 and 3, your client may be able to discharge their federal student loans through a bankruptcy adversary proceeding.</p>
<?php	//}	?>
  </div>
</div>


<div class="box box-primary box-solid">
<div class="box-header with-border">
  <h3 class="box-title" style="text-transform:uppercase;">6. PRIVATE STUDENT LOANS</h3>
  <div class="box-tools pull-right"><button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button></div>
</div>

  <div class="box-body" style="display: block;">
<?php	if (isset($intake[34]['ans']['intake_comment_body'])) {

	if (trim($intake[34]['ans']['intake_comment_body']) > 0) {
		?>
<div class="table-responsive">
<table class="table table-bordered" style="background:#FFFFFF;">
<tr style="background:#FFFFFF;">
    <th>Lender/Servicer</th>
    <th>Approximate <br>Balance</th>
    <th>Interest <br>Rate</th>
    <th>Co-signer</th>
    <th>Monthly <br>Payment</th>
    <th>Status</th>
    <th>Date of <br> Last Payment</th>
    <th>Last Payment Amount</th>
    <!--<th>Lawsuit or Judgment</th>
    <th>Copy of <br> Lawsuit <br> Download</th>-->
  </tr>

  <tr>
<?php
if ($intake_id == 1) {
			$tmp_arr = ["35", "36", "37", "38", "39", "40", "41", "42"];
		} else {
			$tmp_arr = ["131", "132", "133", "134", "135", "136", "137", "138"];
		}

		$tmp_arr_color = ["#8ebc8e", "#FFFFFF", "#cce7cc", "#FFFFFF", "#c9efc9", "#FFFFFF", "#8ebc8e", "#FFFFFF", "#cce7cc", "#FFFFFF", "#c9efc9", "#FFFFFF"];
		foreach ($tmp_arr as $v) {
			$i = 1;
			$query = $this->db->query("SELECT * FROM intake_comment_result where client_id='" . $client_id . "' and intake_question_id='$v' order by intake_comment_id asc");

			foreach ($query->result() as $row) {$tmp_arr_psl[$v][$i++] = $row->intake_comment_body;}
		}

		if (isset($intake[44]['ans']['intake_file_id'])) {
			$document_44 = $this->crm_model->document_decrypt($intake[44]['ans']['intake_file_location']);
			if (file_exists($document_44)) {
				//$file_data = read_file($document_44);
				//$arr_file_data_44 = preg_split("/\r\n|\n|\r/", $file_data);
				//$copy_of_lawsuit_download = '<a href="javascript:void(0)" class="btn btn-info" data-toggle="modal" data-target="#myModal_file_44">View File</a>';
				$copy_of_lawsuit_download = '<a href="' . base_url($sg_1 . "/customer/current_analysis/" . $client_id . "?download=" . $intake[44]['ans']['intake_file_id']) . '" class="btn btn-info" target="_blank">View File</a>';
			} else { $copy_of_lawsuit_download = "N/A";}
		} else { $copy_of_lawsuit_download = "N/A";}

		?>


<?php
for ($i = 1; $i <= count($tmp_arr_psl[($intake_id == 1) ? 35 : 131]); $i++) {
			if (trim($tmp_arr_psl[($intake_id == 1) ? 35 : 131][$i]) != "") {
				?>
<tr style="background:<?php echo $tmp_arr_color[($i - 1)]; ?>">
<?php	foreach ($tmp_arr as $v) {echo '<td>' . $tmp_arr_psl[$v][$i] . '</td>';}?>
<!--<td><?php	echo $intake[43]['ans']['body']['intake_answer_body']; ?></td>
<td><?php	echo $copy_of_lawsuit_download; ?></td>-->
</tr>
<?php	}
		}?>

</table>
</div>
<?php	if ($intake[43]['ans']['body']['intake_answer_body'] == "Yes") {?>
<p>Copy of Lawsuit Download</p>
<p><?php	echo $copy_of_lawsuit_download; ?></p>
<?php	}?>
<?php	} else {echo '<p>This Client indicated they have no Private Student Loans.</p>';}} else {echo '<p>This Client indicated they have no Private Student Loans.</p>';}?>

</div>

</div>



<div class="box box-primary box-solid">
	<div class="box-header with-border">
	  	<h3 class="box-title" style="text-transform:uppercase;">7. Additional Documents</h3>
	  	<div class="box-tools pull-right"><button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button></div>
	</div>
	<div class="box-body" style="display: block;">
		<?php

$logid = $GLOBALS["loguser"]["id"];

$q = $this->db->query("SELECT id,name,lname FROM users where company_id='" . $logid . "' or parent_id='" . $logid . "' order by id desc");
foreach ($q->result() as $r) {$arr_users[$r->id] = $r->name . " " . $r->lname;}

$cnd = " (added_by='$logid' or company_id='$logid' or client_manager='$logid') and client_id='" . $client_id . "'";
$query = $this->db->query("SELECT * FROM client_documents where $cnd order by document_id  desc limit 10000");
$documents = $query->result_array();

if (count($documents) > 0) {
	?>

<table class="table table-bordered"  style="background:#FFFFFF;width:50%">
<tr style="background:#FFFFFF;">
    <th>Document Name</th>
  </tr>

	<?php
foreach ($documents as $doc) {
		?>
  <tr>
  	<td><a href="<?php echo base_url('account/customer/document/' . $client_id . '/view/' . $doc['document_id']) ?>" title="View Document" target="_blank"><?=$doc['document_name']?></a></td>
</tr>
<?php
}
	?>
</table>
	<?php
} else {
	?>
<p>There are no additional documents from this client.</p>
	<?php
}
?>
	</div>
</div>





<div class="box box-primary box-solid">
<div class="box-header with-border">
  <h3 class="box-title" style="text-transform:uppercase;">8. Pre-Analysis Review</h3>
  <div class="box-tools pull-right"><button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button></div>
  <!-- /.box-tools -->
</div>

<div class="box-body" style="display: block;">

<?php
$edit_eligibility = "Yes";
if (isset($car['par_csd'])) {if (trim($car['par_csd']) == "") {$edit_eligibility = "Yes";} else { $edit_eligibility = "No";}}

?>
<input type="hidden" name="client_situation_dropdown" value="" />
<div class="form-group">
  <label for="usr">Please select from the below based on your review of your Client's situation</label>
<?php
$arr_par_csd = array("We can not assist you", "We can help you", "Skip email and continue Intake Program", "Skip email but do not continue with Intake Program");
?>
<select class="form-control" style="max-width:300px;" <?php if ($edit_eligibility == "Yes") {echo ' name="par_csd" required';} else {echo ' disabled';}?>>
	<option value="">Select your option</option>
<?php foreach ($arr_par_csd as $v) {?>
<option value="<?php echo $v; ?>" <?php if (isset($car['par_csd'])) {if (trim($car['par_csd']) == $v) {echo " selected";}}?>><?php echo $v; ?></option>
<?php	}?>

</select>
</div>

<div class="form-group">
  <label for="pwd">Include personalized comments to this person</label>
  <textarea class="form-control" maxlength="2000" rows="7" <?php if ($edit_eligibility == "Yes") {echo ' name="par_comment"';} else {echo " disabled";}?>><?php if (isset($car['par_comment'])) {echo $car['par_comment'];}?></textarea>
</div>

<?php	if ($edit_eligibility == "Yes") {?><button type="submit" class="btn btn-primary btn-block" name="submit_ca" style="max-width:500px;">Save Pre-qualification</button><?php	}?>

  </div>
</div>


<!--<div class="hide_on_print" style="margin-bottom:25px;">
<strong>Analysis Status</strong><br />
<span style=" display:none;"><label class="radio-inline"><input type="radio" name="status" value="Pending" checked />Pending</label> &nbsp; </span>
<label class="radio-inline"><input type="radio" name="status" value="Complete" <?php if ($status == "Complete") {echo " checked";}?> />Analysis Complete</label> &nbsp;
<label class="radio-inline"><input type="radio" name="status" value="Saved" <?php if ($status == "Saved") {echo " checked";}?> />Analysis Saved</label> &nbsp;
</div>-->


<?php	if (isset($car['par_csd'])) {if (trim($car['par_csd']) == "We can help you" || trim($car['par_csd']) == "Skip email and continue Intake Program") {?>
<p class="hide_on_print">
	<button type="submit" class="btn btn-primary" name="submit_ca">Save</button> &nbsp;
    <!--<button type="button" class="btn btn-info" name="submit_ca_and_close" data-toggle="modal" data-target="#myModal_ca_close">Close</button> &nbsp; -->
    <a href="<?php echo base_url("account/customer/view/" . $this->uri->segment(4)); ?>" class="btn btn-info">Close</a>
    <?php	if (trim($car['scenario_selected'])) {?><a href="<?php	echo base_url($sg_1 . "/customer/current_analysis_print/" . $client_id); ?>" target="_blank" class="btn btn-warning"><i class="fa fa-print"></i> Print</a><?php	}}?>
</p>
<?php	}?>

<div id="myModal_ca_close" class="modal fade" role="dialog">
  <div class="modal-dialog modal-sm">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Confirm</h4>
      </div>
      <div class="modal-body">You have made changes to the Scenario and or Notes section. Do you wish to Save Changes or Discard Changes?</div>
      <div class="modal-footer">
      	<button type="submit" name="submit_ca_and_close" class="btn btn-primary" onClick="$('#myModal_ca_close').modal('toggle')">Save</button>
        <a href="<?php echo base_url("account/customer/view/" . $this->uri->segment(4)); ?>" class="btn btn-danger">Discard</a>
      </div>
    </div>

  </div>
</div>


</form>

      <!-- /.row -->

    </section>
    <!-- /.content -->
</div>
</div>
  </div>





<?php	if (is_array($arr_file_data_33)) {?>
<div id="myModal_file_33" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">View File</h4>
      </div>
      <div class="modal-body"><?php	foreach ($arr_file_data_33 as $v) {echo $v . "<br />";}?></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>
<?php	}?>

<?php	if (is_array($arr_file_data_44)) {?>
<div id="myModal_file_44" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Copy of Lawsuit Download</h4>
      </div>
      <div class="modal-body"><?php	foreach ($arr_file_data_44 as $v) {echo $v . "<br />";}?></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>
<?php	}?>



<div class="hide_on_print"><?php	//$this->load->view("account/inc/footer");	?></div>

</div>
<?php	$this->load->view("account/inc/template_js");?>
<script type="text/javascript">
function change_prong_2_3_0()
{
	if(!$("#prong_2_3_0").is(':checked')) { $("#disp_prong_2_3_0").hide(); } else { $("#disp_prong_2_3_0").show(); }
}
</script>

<script type="text/javascript">
recalculate_ca_ps('<?php echo base_url("account/client_current_analysis_payment_scenario"); ?>');

$(document).on('click','[name=scenario_selected]',function(){
	var value = $('[name=scenario_selected]:checked').val();

	if(value.includes('AGI')){
		$(document).find('[name=consent][value=5b]').prop('checked',false);
	}else if(value.includes('Monthly')){
		$(document).find('[name=consent][value=5b]').prop('checked',true);
	}
})
</script>

<script type="text/javascript">
function reset_current_analysis()
{
	var cnf = confirm('Are you sure.');
	if(cnf)
	{
		var internal_notes = $("#ca_internal_notes").val();
		$.post("<?php echo base_url("account/reset_ca_save_internal_notes"); ?>", {client_id: '<?php echo $client_id; ?>', internal_notes: internal_notes}, function(result){});
	}
	return cnf;
}
</script>

</body>
</html>
