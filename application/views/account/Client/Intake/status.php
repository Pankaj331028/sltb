<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php
$user = $GLOBALS["loguser"];
$client_id = $GLOBALS["loguser"]["id"];

?>
<!DOCTYPE html>
<html>
<head>
<?php	$this->load->view("account/inc/head");?>
</head>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
<?php	$this->load->view("Site/inc/header_client");?>


<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
<!-- Content Header (Page header) -->

<!-- Main content -->
<section class="content">

<div class="nav-tabs-custom">
<ul class="nav nav-tabs">
<li><a href="<?php echo base_url("customer/dashboard"); ?>"><i class="fa fa-pencil"></i> Your Information</a></li>
<?php	$this->load->view("account/Client/Inc/intake_link", ["client_data" => $client_data]);?>
<li><a href="<?php echo base_url("customer/document"); ?>"><i class="fa fa-upload"></i> Documents</a></li>
</ul>
<div class="tab-content">
<div class="active tab-pane" id="settings">

<?php	$this->load->view("template/alert.php");?>

<?php

if ($this->uri->segment(3) == "idr") {
	$name = "IDR";
	$intake_id_by_url = 2;
} else if ($this->uri->segment(3) == "consolidation") {
	$name = "Consolidation";
	$intake_id_by_url = 3;
} else if ($this->uri->segment(3) == "recertification") {
	$name = "Recertification";
	$intake_id_by_url = 5;
} else if ($this->uri->segment(3) == "recalculation") {
	$name = "Recalculation";
	$intake_id_by_url = 6;
} else if ($this->uri->segment(3) == "switch_idr") {
	$name = "Switch IDR";
	$intake_id_by_url = 7;
} else if ($this->uri->segment(3) == "update") {
	$name = "Update";
	$intake_id_by_url = 4;
} else {
	$name = "Initial";
	$intake_id_by_url = 1;}

?>


<?php

$q = 0;
$a = 0;
$inid = 1;

if ($name == 'Update') {
	$q = 96;
	$a = 74;
	$inid = 4;
}

if ($name == "Initial" || $name == 'Update') {
	@extract($client_data['intake_client_status']);

	$ansR = $this->default_model->get_arrby_tbl_single('intake_comment_result', '*', "client_id='" . $client_id . "' and intake_question_id='" . ($q + 34) . "'", '1');
	/*if ($ansR['intake_comment_body'] == "0") {
			$total_loan = 0;
			$int_6R = $this->default_model->get_arrby_tbl_single('intake_file_result', '*', "client_id='" . $client_id . "' and intake_question_id='" . ($q + 6) . "'", '1');
			if (!isset($int_6R['student_total_all_loans_outstanding_principal'])) {$total_loan = 0;} else {
				$total_loan = ($int_6R['student_total_all_loans_outstanding_principal'] + $int_6R['student_total_all_loans_outstanding_interest']);
			}

			if ($total_loan == 0) {
				$this->db->query("delete from intake_client_status where client_id='$client_id' and intake_id='" . $inid . "'");
				$this->session->set_flashdata('error', "You have indicated you have no loans. Please go back and correct your information.");
				redirect(base_url("account/" . ($name == 'Initial' ? 'intake_form' : 'update_intake_form') . "?intake_page_no=1"));
				exit;
			}
		}
	*/

	if (isset($client_data['update_intake_client_status']['status'])) {
		if ($client_data['update_intake_client_status']['status'] == "Complete") {
			//echo '<p style="padding:25px 25px;"><strong>Completed:</strong> '.date('m/d/Y',strtotime($client_data['intake_client_status']['add_date'])).'</p>';
			?>

<h2 style="margin:5px 0 0 20px;"><strong><?php echo $name; ?> Intake</strong></h2>

<div class="row">
<div class="col-md-12">
    <div style="padding:15px 25px;"><p>Thank you for completing the <strong>Student Loan Intake</strong>.<br />We will contact you shortly.<br />Once we have completed our analysis.</p><p>If you feel you made a mistake in the intake process, please email your case manager at <strong><a href="mailto:<?php	echo $client_data['case_manager']['email']; ?>"><?php	echo $client_data['case_manager']['email']; ?></a></strong> with the correction.</p><!--<p><a href="'.base_url('account/intake_form?intake_page_no=1').'" class="btn btn-primary btn-flat">Resume Intake</a></p>-->
    <p><strong>Case Manager Details</strong></p>
    <p><strong>Name :</strong> <?php	echo $client_data['case_manager']['name'] . ' ' . $client_data['case_manager']['lname']; ?></p>
    <p><strong>Email :</strong> <a href="mailto:<?php	echo $client_data['case_manager']['email']; ?>"><?php	echo $client_data['case_manager']['email']; ?></a></p>
    </div>
</div>

<?php
$nslds = $this->db->query('select * from client_analysis_results where client_id = ' . $client_id . ' and intake_id = 1')->row_array();
			if (empty($nslds['scenario_selected']) && empty($nslds['payment_plan_selected'])) {
				?>
<div class="col-md-12">

<div style="padding:0px 25px;">
	<strong class="text-danger">Note: </strong><strong>If you have just completed the Intake, you do NOT need to upload your NSLDS (<a href="http://studentaid.gov/" target="_blank">studentaid.gov</a>) file again. This button allows you to upload the file at a later date if there is a change to your status or another situation arises.</strong>
</div>
</div>

<div class="col-md-4">
<div id="nslds_file_form" style="margin-top:15px; max-width:500px; background:#F8F8F8;">
<form action="" method="post" enctype="multipart/form-data">
<input type="hidden" name="client_id" value="<?php	echo $client_id; ?>" />
<div class="box box-primary">
<div class="box-header" style="background:#3c8dbc; color:#FFFFFF;">
	<strong>Update NSLDS</strong>
    <a href="<?php echo base_url("nslds-upload-instructions"); ?>" target="_blank" class="btn btn-default btn-xs pull-right" style="color:#0033CC;"><i class="fa fa-info-circle"></i> Upload Help</a>
</div>

<div class="box-body" style="background:#F8F8F8;">
<p>Update your <a href="https://studentaid.gov" target="_blank">studentaid.gov</a> report</p>

<div class="form-group">
<input type="file" class="form-control" name="intake_file_result" accept="text/plain" required />
</div>
<div>
<input type="submit" class="btn btn-primary" name="Submit_nslds" value="Save">
</div>

</div>

</div>
</form>
</div>
</div>
<?php

			}
			?>

<div class="col-md-8">
<div class="alert" style="background:#FCFCFC; border-color:#FCFCFC; color:#000000; margin-top:10px; display:none;">
<p><strong style="font-size:16px;">How to download the NSLDS file from Studentaid.gov</strong></p>
<hr style="margin-top:5px; margin-bottom:5px; border-color:#999999;" />
<div>
<ul style="padding-left:25px; line-height:30px;">
<li><strong>Step 1</strong> - Navigate your web browser to <a href="https://studentaid.gov" target="_blank" class="text-blue">https://studentaid.gov</a></li>
<li><strong>Step 2</strong> - On the Federal Student Aid homepage, click the <a href="https://studentaid.gov/fsa-id/sign-in" target="_blank" class="text-blue"><strong>Log In</strong></a> link.</li>
<li><strong>Step 3</strong> - Enter your <strong>FSA ID Username, Email, or Phone</strong> and <strong>Password</strong> and click Log In.</li>
<li><strong>Step 4</strong> - Read the Warning and click <strong>Accept</strong>.</li>
<li><strong>Step 5</strong> - On the Student Aid Dashboard, click <strong>View Details</strong>.</li>
<li><strong>Step 6</strong> - The Borrower Loan Details page is displayed. Click <strong>Download My Aid Data</strong> to download the <strong>NSLDS.txt file</strong>.</li>
<li><strong>Step 7</strong> - Read the displayed information and click <strong>Continue</strong>.</li>
<li><strong>Step 8</strong> - Clicking the <strong>Continue</strong> button will download your <strong>NSLDS.txt</strong> file to your computer.</li>
</ul>
</div>
</div>
</div>
</div>
<?php
} else {
			echo '<p style="padding:25px 25px;"><a href="' . base_url('account/' . ($name == 'Initial' ? 'intake_form' : 'update_intake_form') . '?intake_page_no=1') . '" class="btn btn-primary btn-flat">Update Intake</a></p>';
		}
	} elseif (isset($client_data['intake_client_status']['status'])) {
		if ($client_data['intake_client_status']['status'] == "Complete") {
			//echo '<p style="padding:25px 25px;"><strong>Completed:</strong> '.date('m/d/Y',strtotime($client_data['intake_client_status']['add_date'])).'</p>';
			?>

<h2 style="margin:5px 0 0 20px;"><strong><?php echo $name; ?> Intake</strong></h2>

<div class="row">
<div class="col-md-12">
    <div style="padding:15px 25px;"><p>Thank you for completing the <strong>Student Loan Intake</strong>.<br />We will contact you shortly.<br />Once we have completed our analysis.</p><p>If you feel you made a mistake in the intake process, please email your case manager at <strong><a href="mailto:<?php	echo $client_data['case_manager']['email']; ?>"><?php	echo $client_data['case_manager']['email']; ?></a></strong> with the correction.</p><!--<p><a href="'.base_url('account/intake_form?intake_page_no=1').'" class="btn btn-primary btn-flat">Resume Intake</a></p>-->
    <p><strong>Case Manager Details</strong></p>
    <p><strong>Name :</strong> <?php	echo $client_data['case_manager']['name'] . ' ' . $client_data['case_manager']['lname']; ?></p>
    <p><strong>Email :</strong> <a href="mailto:<?php	echo $client_data['case_manager']['email']; ?>"><?php	echo $client_data['case_manager']['email']; ?></a></p>
    </div>
</div>
<?php
$nslds = $this->db->query('select * from client_analysis_results where client_id = ' . $client_id . ' and intake_id = 1')->row_array();
			if (empty($nslds['scenario_selected']) && empty($nslds['payment_plan_selected'])) {
				?>
<div class="col-md-12">

<div style="padding:0px 25px;">
	<strong class="text-danger">Note: </strong><strong>If you have just completed the Intake, you do NOT need to upload your NSLDS (<a href="http://studentaid.gov/" target="_blank">studentaid.gov</a>) file again. This button allows you to upload the file at a later date if there is a change to your status or another situation arises.</strong>
</div>
</div>

<div class="col-md-4">
<div id="nslds_file_form" style="margin-top:15px; max-width:500px; background:#F8F8F8;">
<form action="" method="post" enctype="multipart/form-data">
<input type="hidden" name="client_id" value="<?php	echo $client_id; ?>" />
<div class="box box-primary">
<div class="box-header" style="background:#3c8dbc; color:#FFFFFF;">
	<strong>Update NSLDS</strong>
    <a href="<?php echo base_url("nslds-upload-instructions"); ?>" target="_blank" class="btn btn-default btn-xs pull-right" style="color:#0033CC;"><i class="fa fa-info-circle"></i> Upload Help</a>
</div>

<div class="box-body" style="background:#F8F8F8;">
<p>Update your <a href="https://studentaid.gov" target="_blank">studentaid.gov</a> report</p>

<div class="form-group">
<input type="file" class="form-control" name="intake_file_result" accept="text/plain" required />
</div>
<div>
<input type="submit" class="btn btn-primary" name="Submit_nslds" value="Save">
</div>

</div>

</div>
</form>
</div>
</div>
<?php
}
			?>

<div class="col-md-8">
<div class="alert" style="background:#FCFCFC; border-color:#FCFCFC; color:#000000; margin-top:10px; display:none;">
<p><strong style="font-size:16px;">How to download the NSLDS file from Studentaid.gov</strong></p>
<hr style="margin-top:5px; margin-bottom:5px; border-color:#999999;" />
<div>
<ul style="padding-left:25px; line-height:30px;">
<li><strong>Step 1</strong> - Navigate your web browser to <a href="https://studentaid.gov" target="_blank" class="text-blue">https://studentaid.gov</a></li>
<li><strong>Step 2</strong> - On the Federal Student Aid homepage, click the <a href="https://studentaid.gov/fsa-id/sign-in" target="_blank" class="text-blue"><strong>Log In</strong></a> link.</li>
<li><strong>Step 3</strong> - Enter your <strong>FSA ID Username, Email, or Phone</strong> and <strong>Password</strong> and click Log In.</li>
<li><strong>Step 4</strong> - Read the Warning and click <strong>Accept</strong>.</li>
<li><strong>Step 5</strong> - On the Student Aid Dashboard, click <strong>View Details</strong>.</li>
<li><strong>Step 6</strong> - The Borrower Loan Details page is displayed. Click <strong>Download My Aid Data</strong> to download the <strong>NSLDS.txt file</strong>.</li>
<li><strong>Step 7</strong> - Read the displayed information and click <strong>Continue</strong>.</li>
<li><strong>Step 8</strong> - Clicking the <strong>Continue</strong> button will download your <strong>NSLDS.txt</strong> file to your computer.</li>
</ul>
</div>
</div>
</div>
</div>
<?php
} else {
			?>

<?php
}
	} else {
		echo '<p style="padding:25px 25px;"><a href="' . base_url('account/' . ($name == 'Initial' ? 'intake_form' : 'update_intake_form') . '?intake_page_no=1') . '" class="btn btn-primary btn-flat">Start Intake</a></p>';
	}

} else {
	echo '<div class="row">';
	$arr_program_id = $this->array_model->arr_intake_program_id();

	foreach ($arr_program_id as $program_id_primary => $intake_id) {

		if ($intake_id == $intake_id_by_url) {
			echo '<div class="col-md-6">';

//	IDR Intake
			$intake2R = $this->default_model->get_arrby_tbl_single('client_program_progress', '*', "client_id='" . $client_id . "' and program_id_primary='$program_id_primary'", '1');

			if (isset($intake2R['program_id_primary'])) {
				$iR = $this->default_model->get_arrby_tbl_single('intake', '*', "intake_id='$intake_id'", '1');
				$icsR = $this->default_model->get_arrby_tbl_single('intake_client_status', '*', "client_id='" . $client_id . "' and intake_id='$intake_id'", '1');
				$ics = "Pending";
				if (isset($icsR['status'])) {if ($icsR['status'] == "Complete") {$ics = "Complete";}}

				echo '<h2 style="margin:5px 0 15px 0px;"><strong>' . $name . ' Intake</strong></h2>';

				if ($ics == "Complete") {
					?>
<div>


<div><p>Thank you for completing the <strong><?php	echo $iR['intake_title']; ?></strong>.</p><p>If you feel you made a mistake in the intake process, please email your case manager at <strong><a href="mailto:<?php	echo $client_data['case_manager']['email']; ?>"><?php	echo $client_data['case_manager']['email']; ?></a></strong> with the correction.</p><!--<p><a href="'.base_url('account/intake_form?intake_page_no=1').'" class="btn btn-primary btn-flat">Resume Intake</a></p>-->
<p><strong>Case Manager Details</strong></p>
<p><strong>Name :</strong> <?php	echo $client_data['case_manager']['name'] . ' ' . $client_data['case_manager']['lname']; ?></p>
<p><strong>Email :</strong> <a href="mailto:<?php	echo $client_data['case_manager']['email']; ?>"><?php	echo $client_data['case_manager']['email']; ?></a></p>
</div>
      </div>
<?php
} else {
					echo '<p>Your ' . $iR['intake_title'] . ' Intake still pending.</p><p><a href="' . base_url($client_data['users_company']['slug'] . '/' . $iR['intake_slug'] . '?intake_page_no=1') . '" class="btn btn-warning btn-flat"> &raquo; Complete ' . $iR['intake_title'] . ' Intake</a></p><br /><br />';
				}
			}
			echo '</div>';
		}
	}
	echo '</div>';
}

?>

<div class="clr"></div>

</div>
</div>
</div>


  <!-- /.row -->
</section>
<!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php	$this->load->view("account/inc/footer");?>

</div>
<?php	$this->load->view("account/inc/template_js.php");?>

</body>
</html>
