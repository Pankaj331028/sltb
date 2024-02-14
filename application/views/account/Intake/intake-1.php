<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php
error_reporting(0);
@extract($_POST);
@extract($_GET);
?>
<!DOCTYPE html>
<html>
<head>
<?php	$this->load->view("account/inc/head");?>
<style type="text/css">
.disp_none { display:none;}
</style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php	$this->load->view("account/inc/header");?>
<?php	$this->load->view("account/inc/leftnav");?>


<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><?php echo $data["name"]; ?></h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo base_url('account/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href=""><?php echo $data["name"]; ?></a></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">


      <div class="row">
        <!-- /.col -->
        <div class="col-md-12">
<div>
<?php	$this->load->view("template/alert.php");?>

<?php	if (isset($intake_client_status['status'])) {?>
<div class="row">
<div class="col-md-2"></div>
<div class="col-md-8">
<div class="alert alert-success text-center">
<p><i class="fa fa-thumbs-up" style="font-size:100px; margin-bottom:25px;"></i></p>
<p>Thank you for completing the <strong><?php echo $data["name"]; ?></strong>.</p>
<p>We will contact you shortly.</p>
<p>Once we have completed our analysis.</p>
</div>
</div>
</div>
<?php	} else {
	?>


<form action="" method="post" enctype="multipart/form-data">
<div class="box box-primary">
<div class="box-header" style="background:#3c8dbc; color:#FFFFFF;"><strong><?php echo $intake_page_data['intake_page_title']; ?></strong></div>

<div class="box-body">
<?php
if ($intake_page_no == 3) {
		$ansR = $this->default_model->get_arrby_tbl_single('intake_file_result', '*', "client_id='" . $GLOBALS["loguser"]["id"] . "' and intake_question_id='6'", '1');
		$client_document = $this->crm_model->document_decrypt($ansR['intake_file_location']);
		$file_data = read_file($client_document);
		$arr_file_data = preg_split("/\r\n|\n|\r/", $file_data);
		$arr_file_data_2 = array();
		$arr_file_data_rows = array();
		$total_federal_student_loan_debt = 0;
		$i = 0;
		foreach ($arr_file_data as $k => $v) {

			$arr_file_data_2[] = explode(":", $v);
			$vr = explode(":", $v);
			if ($vr[0] == "[Student Total All Loans Outstanding Principal" || $vr[0] == "[Student Total All Loans Outstanding Interest" || $vr[0] == "Student Total All Loans Outstanding Principal" || $vr[0] == "Student Total All Loans Outstanding Interest") {
				$rmv1 = ['$', ',', ' ', 'A', 'B'];
				$rmv2 = "";
				$total_federal_student_loan_debt += str_replace($rmv1, $rmv2, $vr[1]);
			}

			if ($vr[0] == "Loan Type" || $vr[0] == "Loan type") {
				$i++;
				$arr_file_data_rows[$i]['loan_type'] = $vr[1];}
			if ($vr[0] == "Loan Attending School" || $vr[0] == "Loan attending school") {$arr_file_data_rows[$i]['school'] = $vr[1];}
			if ($vr[0] == "Loan Date" || $vr[0] == "Loan date") {$arr_file_data_rows[$i]['date'] = $vr[1];}
			if ($vr[0] == "Loan Amount" || $vr[0] == "Loan amount") {$arr_file_data_rows[$i]['origination_amount'] = $vr[1];}
			if ($vr[0] == "Loan Outstanding Principal Balance" || $vr[0] == "Loan outstanding principal balance") {$arr_file_data_rows[$i]['current_principal'] = $vr[1];}
			if ($vr[0] == "Loan Outstanding Interest Balance" || $vr[0] == "Loan outstanding interest balance") {$arr_file_data_rows[$i]['current_interest'] = $vr[1];}
			if ($vr[0] == "Loan Status Description" || $vr[0] == "Loan status description") {$arr_file_data_rows[$i]['last_status'] = $vr[1];}
			if ($vr[0] == "Loan Status Effective Date" || $vr[0] == "Loan status effective date") {$arr_file_data_rows[$i]['last_status_date'] = $vr[1];}
		}

		$arr_file_data_rows2 = array();
		foreach ($arr_file_data_rows as $k => $v) {
			$arr_indx = explode("/", $v['date']);
			$iy = $arr_indx[2];
			$id = $arr_indx[1];
			$im = $arr_indx[0];
			//$arr_file_data_rows2[$iy][$im][$id]['loan_type'] = $v;
			$arr_file_data_rows2[$iy . $im . $id] = $v;
		}
		ksort($arr_file_data_rows2);

		/*
			print_r("<pre>");
			print_r($arr_file_data);
			print_r("</pre>");
			print_r("<pre>");
			print_r($arr_file_data_2);
			print_r("</pre>");
		*/
		?>
<!--<div style="background:#f8f8f8; padding:15px 0 15px 15px; border:1px #CCCCCC; box-shadow:2px 2px 2px #CCCCCC;">
<div style="width:100%; height:250px; overflow-y:scroll;">
<div style="padding:0 15px;"><?php echo nl2br($file_data); ?></div>
</div>
</div>-->
<?php
$fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
		?>
<div style="background:#F8F8F8; margin:15px 0 30px 0; padding:15px; border:1px solid #CCCCCC; box-shadow:2px 2px 2px #CCCCCC;">
<p style="font-size:18px;  text-align:center;"><strong>Total Federal Student Loan Debt </strong><br /><?php echo $fmt->formatCurrency($total_federal_student_loan_debt, "USD"); ?></p>

<div>
<?php
if (isset($arr_file_data_rows2)) {
			foreach ($arr_file_data_rows2 as $row) {
				?>
<div style="border:1px solid #999999; padding:15px; background:#9999FF; margin-bottom:15px;">
<?php	if (isset($row['date'])) {?><p style="margin-bottom:2px;"><strong>Date: </strong> <?php echo $row['date']; ?></p><?php	}?>
<?php	if (isset($row['school'])) {?><p style="margin-bottom:2px;"><strong>School: </strong> <?php echo $row['school']; ?></p><?php	}?>
<?php	if (isset($row['loan_type'])) {?><p style="margin-bottom:2px;"><strong>Loan Type: </strong> <?php echo $row['loan_type']; ?></p><?php	}?>
<?php	if (isset($row['origination_amount'])) {?><p style="margin-bottom:2px;"><strong>Origination amount: </strong> <?php echo $row['origination_amount']; ?></p><?php	}?>
<?php	if (isset($row['current_principal'])) {?><p style="margin-bottom:2px;"><strong>Current Principal: </strong> <?php echo $row['current_principal']; ?></p><?php	}?>
<?php	if (isset($row['current_interest'])) {?><p style="margin-bottom:2px;"><strong>Current Interest: </strong> <?php echo $row['current_interest']; ?></p><?php	}?>
<?php	if (isset($row['last_status'])) {?><p style="margin-bottom:0px;"><strong>Last status and date: </strong> <?php echo $row['last_status']; ?> <?php	if (isset($row['last_status_date'])) {echo ", " . $row['last_status_date'];}?></p><?php	}?>

</div>
<?php	}}?>
</div>

</div>
<?php	}?>


<?php
foreach ($intake_question_data as $row) {
		$ansR = $this->crm_model->admin_intake_answer_by_client($GLOBALS["loguser"]["id"], $row['intake_question_id']);

		$onclick = '';
		$disp_none = '';
		$required_val = '';
		if ($row['intake_question_id'] == '8' || $row['intake_question_id'] == '9') {
			if ($intake_ans_7 != 5) {
				$disp_none = "disp_none";
				$required_val = "none";
			}
		}

		if ($row['intake_question_id'] > 11 && $row['intake_question_id'] < 18) {
			if ($intake_ans_11 != 15) {
				$disp_none = "disp_none";
				$required_val = "none";
			}
		}

		if ($row['intake_question_id'] == 21) {
			if ($intake_ans_21 != 20) {
				$disp_none = "disp_none";
				$required_val = "none";
			}
		}

		if ($row['intake_question_id'] > 23 && $row['intake_question_id'] < 28) {
			if ($intake_ans_23 != 25) {
				$disp_none = "disp_none";
				$required_val = "none";
			}
		}

		if ($row['intake_question_id'] > 24 && $row['intake_question_id'] < 28) {
			if ($intake_ans_24 != 27) {
				$disp_none = "disp_none";
				$required_val = "none";
			}
		}

		if ($row['intake_question_id'] > 25 && $row['intake_question_id'] < 28) {
			if ($intake_ans_25 != 28) {
				$disp_none = "disp_none";
				$required_val = "none";
			}
		}

		if ($row['intake_question_id'] > 28 && $row['intake_question_id'] < 32) {$required_val = "none";}

		if ($row['intake_question_id'] == 33) {
			if ($intake_ans_32 != 56) {
				$disp_none = "disp_none";
				$required_val = "none";
			}
		}

		if ($row['intake_question_id'] == 33) {
			if ($intake_ans_32 != 56) {
				$disp_none = "disp_none";
				$required_val = "none";
			}
		}

		if ($row['intake_question_id'] > 34 && $row['intake_question_id'] < 45) {
			if ($intake_ans_34 == 0) {
				$disp_none = "disp_none";
				$required_val = "none";
			}
		}

		if ($row['intake_question_id'] > 40 && $row['intake_question_id'] < 45) {
			if ($intake_ans_40 != "Forbearance" && $intake_ans_40 != "Forbearance/deferment" && $intake_ans_40 != "deferment" && $intake_ans_40 != "Late" && $intake_ans_40 != "Default") {
				$disp_none = "disp_none";
				$required_val = "none";
			}
		}

		if ($row['intake_question_id'] == 44) {
			if ($intake_ans_43 != 58) {
				$disp_none = "disp_none";
				$required_val = "none";
			}
		}

###########
		if ($row['intake_question_id'] == '7') {
			$onclick = 'onChange="check_question_7(this.value)"';
			$intake_ans_7 = $ansR['intake_answer_id'];
			?>
<script type="text/javascript">
function check_question_7(val)
{
	if(val == '5')
	{
		$("#intake_form_group_8 input[type=text]").attr("required","required");
		$("#intake_form_group_8").show('100');

		$("#intake_form_group_9 input[type=text]").attr("required","required");
		$("#intake_form_group_9").show('100');
	}
	else
	{
		$("#intake_form_group_8 input[type=text]").removeAttr("required");
		$("#intake_form_group_8").hide('100');

		$("#intake_form_group_9 input[type=text]").removeAttr("required");
		$("#intake_form_group_9").hide('100');
	}

}
</script>
<?php }

		if ($row['intake_question_id'] == '11') {
			$onclick = 'onChange="check_question_11(this.value)"';
			$intake_ans_11 = $ansR['intake_answer_id'];
			?>
<script type="text/javascript">
function check_question_11(val)
{
	if(val == '15')
	{
		$("#intake_form_group_12 input[type=text]").attr("required","required");
		$("#intake_form_group_12").show('100');

		$("#intake_form_group_13 input[type=text]").attr("required","required");
		$("#intake_form_group_13").show('100');

		$("#intake_form_group_14 input[type=text]").attr("required","required");
		$("#intake_form_group_14").show('100');

		$("#intake_form_group_15 input[type=text]").attr("required","required");
		$("#intake_form_group_15").show('100');

		$("#intake_form_group_16 input[type=text]").attr("required","required");
		$("#intake_form_group_16").show('100');

		$("#intake_form_group_17 input[type=text]").attr("required","required");
		$("#intake_form_group_17").show('100');
	}
	else
	{
		$("#intake_form_group_12 input[type=text]").removeAttr("required");
		$("#intake_form_group_12").hide('100');

		$("#intake_form_group_13 input[type=text]").removeAttr("required");
		$("#intake_form_group_13").hide('100');

		$("#intake_form_group_14 input[type=text]").removeAttr("required");
		$("#intake_form_group_14").hide('100');

		$("#intake_form_group_15 input[type=text]").removeAttr("required");
		$("#intake_form_group_15").hide('100');

		$("#intake_form_group_16 input[type=text]").removeAttr("required");
		$("#intake_form_group_16").hide('100');

		$("#intake_form_group_17 input[type=text]").removeAttr("required");
		$("#intake_form_group_17").hide('100');
	}

}
</script>
<?php }

		if ($row['intake_question_id'] == '21') {
			$onclick = 'onChange="check_question_21(this.value)"';
			$intake_ans_21 = $ansR['intake_answer_id'];
			?>
<script type="text/javascript">
function check_question_21(val)
{
	if(val == '20')
	{
		$("#intake_form_group_22 input[type=text]").attr("required","required");
		$("#intake_form_group_22").show('100');
	}
	else
	{
		$("#intake_form_group_22 input[type=text]").removeAttr("required");
		$("#intake_form_group_22").hide('100');
	}

}
</script>
<?php }

		if ($row['intake_question_id'] == '23') {
			$onclick = 'onChange="check_question_23(this.value)"';
			$intake_ans_23 = $ansR['intake_answer_id'];
			?>
<script type="text/javascript">
function check_question_23(val)
{
	if(val == '25')
	{
		$("#intake_form_group_24 input[type=text]").attr("required","required");
		$("#intake_form_group_24").show('100');

		$("#intake_form_group_25 input[type=text]").attr("required","required");
		$("#intake_form_group_25").show('100');

		$("#intake_form_group_26 input[type=text]").attr("required","required");
		$("#intake_form_group_26").show('100');

		$("#intake_form_group_27 input[type=text]").attr("required","required");
		$("#intake_form_group_27").show('100');
	}
	else
	{
		$("#intake_form_group_24 input[type=text]").removeAttr("required");
		$("#intake_form_group_24").hide('100');

		$("#intake_form_group_25 input[type=text]").removeAttr("required");
		$("#intake_form_group_25").hide('100');

		$("#intake_form_group_26 input[type=text]").removeAttr("required");
		$("#intake_form_group_26").hide('100');

		$("#intake_form_group_27 input[type=text]").removeAttr("required");
		$("#intake_form_group_27").hide('100');
	}

}
</script>
<?php }

		if ($row['intake_question_id'] == '24') {
			$onclick = 'onChange="check_question_24(this.value)"';
			$intake_ans_24 = $ansR['intake_answer_id'];
			?>
<script type="text/javascript">
function check_question_24(val)
{
	if(val == '27')
	{
		$("#intake_form_group_25 input[type=text]").attr("required","required");
		$("#intake_form_group_25").show('100');

		$("#intake_form_group_26 input[type=text]").attr("required","required");
		$("#intake_form_group_26").show('100');

		$("#intake_form_group_27 input[type=text]").attr("required","required");
		$("#intake_form_group_27").show('100');
	}
	else
	{
		$("#intake_form_group_25 input[type=text]").removeAttr("required");
		$("#intake_form_group_25").hide('100');

		$("#intake_form_group_26 input[type=text]").removeAttr("required");
		$("#intake_form_group_26").hide('100');

		$("#intake_form_group_27 input[type=text]").removeAttr("required");
		$("#intake_form_group_27").hide('100');
	}

}
</script>
<?php }

		if ($row['intake_question_id'] == '25') {
			$onclick = 'onChange="check_question_25(this.value)"';
			$intake_ans_25 = $ansR['intake_answer_id'];
			?>
<script type="text/javascript">
function check_question_25(val)
{
	if(val == '28')
	{
		$("#intake_form_group_26 input[type=text]").attr("required","required");
		$("#intake_form_group_26").show('100');

		$("#intake_form_group_27 input[type=text]").attr("required","required");
		$("#intake_form_group_27").show('100');
	}
	else
	{
		$("#intake_form_group_26 input[type=text]").removeAttr("required");
		$("#intake_form_group_26").hide('100');

		$("#intake_form_group_27 input[type=text]").removeAttr("required");
		$("#intake_form_group_27").hide('100');
	}

}
</script>
<?php }

		if ($row['intake_question_id'] == '28') {
			$onclick = 'onChange="check_question_28(this.value)"';
			$intake_ans_28 = $ansR['intake_answer_id'];
			?>
<script type="text/javascript">
function check_question_28(val)
{
	if(val == '46')
	{
		$("#intake_form_group_29 input[type=text]").attr("required","required");
		$("#intake_form_group_29").show('100');

		$("#intake_form_group_30 input[type=text]").attr("required","required");
		$("#intake_form_group_30").show('100');

		$("#intake_form_group_31 input[type=text]").attr("required","required");
		$("#intake_form_group_31").show('100');
	}
	else if(val == '47')
	{
		$("#intake_form_group_29 input[type=text]").removeAttr("required");
		$("#intake_form_group_29").hide('100');

		$("#intake_form_group_30 input[type=text]").removeAttr("required");
		$("#intake_form_group_30").hide('100');

		$("#intake_form_group_31 input[type=text]").removeAttr("required");
		$("#intake_form_group_31").hide('100');
	}
	else
	{
		$("#intake_form_group_29 input[type=text]").removeAttr("required");
		$("#intake_form_group_29").hide('100');

		$("#intake_form_group_30 input[type=text]").removeAttr("required");
		$("#intake_form_group_30").hide('100');

		$("#intake_form_group_31 input[type=text]").attr("required","required");
		$("#intake_form_group_31").show('100');
	}

}
</script>
<?php }

		if ($row['intake_question_id'] == '32') {
			$onclick = 'onChange="check_question_32(this.value)"';
			$intake_ans_32 = $ansR['intake_answer_id'];
			?>
<script type="text/javascript">
function check_question_32(val)
{
	if(val == '56')
	{
		//$("#intake_form_group_33 input[type=text]").attr("required","required");
		$("#intake_form_group_33").show('100');
	}
	else
	{
		$("#intake_form_group_33 input[type=file]").removeAttr("required");
		$("#intake_form_group_33").hide('100');
	}
}
</script>
<?php }

		if ($row['intake_question_id'] == '34') {
			$onChange = 'check_question_34(this.value)';
			$intake_ans_34 = $ansR['intake_comment_body'];
			?>
<script type="text/javascript">
function check_question_34(val)
{
	if(val > 0)
	{
		//$("#intake_form_group_35 input[type=text]").attr("required","required");
		$("#intake_form_group_35").show('100');

		//$("#intake_form_group_36 input[type=text]").attr("required","required");
		$("#intake_form_group_36").show('100');

		//$("#intake_form_group_37 input[type=text]").attr("required","required");
		$("#intake_form_group_37").show('100');

		//$("#intake_form_group_38 input[type=text]").attr("required","required");
		$("#intake_form_group_38").show('100');

		//$("#intake_form_group_39 input[type=text]").attr("required","required");
		$("#intake_form_group_39").show('100');

		//$("#intake_form_group_40 input[type=text]").attr("required","required");
		$("#intake_form_group_40").show('100');

		//$("#intake_form_group_41 input[type=text]").attr("required","required");
		$("#intake_form_group_41").show('100');

		//$("#intake_form_group_42 input[type=text]").attr("required","required");
		$("#intake_form_group_42").show('100');

		//$("#intake_form_group_43 input[type=text]").attr("required","required");
		$("#intake_form_group_43").show('100');

		//$("#intake_form_group_44 input[type=text]").attr("required","required");
		$("#intake_form_group_44").show('100');


	}
	else
	{
		$("#intake_form_group_35 input[type=text]").removeAttr("required");
		$("#intake_form_group_35").hide('100');

		$("#intake_form_group_36 input[type=text]").removeAttr("required");
		$("#intake_form_group_36").hide('100');

		$("#intake_form_group_37 input[type=text]").removeAttr("required");
		$("#intake_form_group_37").hide('100');

		$("#intake_form_group_38 input[type=text]").removeAttr("required");
		$("#intake_form_group_38").hide('100');

		$("#intake_form_group_39 input[type=text]").removeAttr("required");
		$("#intake_form_group_39").hide('100');

		$("#intake_form_group_40 input[type=text]").removeAttr("required");
		$("#intake_form_group_40").hide('100');

		$("#intake_form_group_41 input[type=text]").removeAttr("required");
		$("#intake_form_group_41").hide('100');

		$("#intake_form_group_42 input[type=text]").removeAttr("required");
		$("#intake_form_group_42").hide('100');

		$("#intake_form_group_43 input[type=text]").removeAttr("required");
		$("#intake_form_group_43").hide('100');

		$("#intake_form_group_44 input[type=text]").removeAttr("required");
		$("#intake_form_group_44").hide('100');
	}
}
</script>
<?php }

		if ($row['intake_question_id'] == '40') {
			$onChange = 'check_question_40(this.value)';
			$intake_ans_40 = $ansR['intake_comment_body'];
			?>
<script type="text/javascript">
function check_question_40(val)
{
	if(val== "Forbearance" || val== "Forbearance/deferment" || val== "deferment" || val== "Late" || val== "Default")
	{
		$("#intake_form_group_41 input[type=text]").attr("required","required");
		$("#intake_form_group_41").show('100');

		$("#intake_form_group_42 input[type=text]").attr("required","required");
		$("#intake_form_group_42").show('100');

		$("#intake_form_group_43 input[type=text]").attr("required","required");
		$("#intake_form_group_43").show('100');

		$("#intake_form_group_44 input[type=text]").attr("required","required");
		$("#intake_form_group_44").show('100');
	}
	else
	{
		$("#intake_form_group_41 input[type=text]").removeAttr("required");
		$("#intake_form_group_41").hide('100');

		$("#intake_form_group_42 input[type=text]").removeAttr("required");
		$("#intake_form_group_42").hide('100');

		$("#intake_form_group_43 input[type=text]").removeAttr("required");
		$("#intake_form_group_43").hide('100');

		$("#intake_form_group_44 input[type=text]").removeAttr("required");
		$("#intake_form_group_44").hide('100');
	}
}
</script>
<?php }

		if ($row['intake_question_id'] == '43') {
			$onclick = 'onChange="check_question_43(this.value)"';
			$intake_ans_43 = $ansR['intake_answer_id'];
			?>
<script type="text/javascript">
function check_question_43(val)
{
	if(val == '58')
	{
		//$("#intake_form_group_44 input[type=file]").attr("required","required");
		$("#intake_form_group_44").show('100');
	}
	else
	{
		$("#intake_form_group_44 input[type=file]").removeAttr("required");
		$("#intake_form_group_44").hide('100');
	}
}
</script>
<?php }

		?>


<?php

		if ($row['intake_question_type'] == 'Comment') {
			?>
<div class="form-group <?php echo $disp_none; ?>" id="intake_form_group_<?php echo $row['intake_question_id']; ?>">
<label for="<?php echo $row['intake_question_id']; ?>"><?php echo $row['intake_question_body'];if ($row['question_required'] == 'Yes') {echo " *";} ?></label>
<?php
$name = "intake_comment_result[" . $ansR['intake_comment_id'] . "]";
			$input_id = "intake_form_inpur_" . $row['intake_question_id'];
			if ($row['intake_question_id'] == 34) {$input_type = 'number';} else { $input_type = 'text';}
			$input_data = ['type' => $input_type, 'class' => 'form-control', 'name' => $name, 'value' => $ansR['intake_comment_body'], 'id' => $input_id];
			if ($row['question_required'] == 'Yes' && $required_val == "") {$input_data['required'] = "required";}
			if ($row['intake_question_id'] == 34) {
				$input_data['min'] = "0";
				$input_data['max'] = "500";
				$input_data['onChange'] = $onChange;}
			echo form_input($input_data);
			?>
</div>
<?php
} elseif ($row['intake_question_type'] == 'Radio' || $row['intake_question_type'] == 'Radio Group') {
			$radiogroups = $this->default_model->get_arrby_tbl('intake_answer', '*', "intake_question_id='" . $row['intake_question_id'] . "' and status_flag='Active'", '500');
			?>
<div class="form-group <?php echo $disp_none; ?>" id="intake_form_group_<?php echo $row['intake_question_id']; ?>">
<label for="<?php echo $row['intake_question_id']; ?>"><?php echo $row['intake_question_body'];if ($row['question_required'] == 'Yes') {echo " *";} ?></label>
<div>
<?php	$ai = 0;foreach ($radiogroups as $radiogroup): $ai++;?>
	<label class="radio-inline"><input type="radio"  name="intake_answer_result[<?php echo $ansR['intake_result_id']; ?>]" value="<?php echo $radiogroup['intake_answer_id']; ?>" <?php	if ($radiogroup['intake_answer_id'] == $ansR['intake_answer_id'] || $ai == 1) {echo " checked";}?> <?php echo ($row['question_required'] == 'Yes' && $required_val == "") ? 'required' : '' ?> <?php	echo $onclick; ?> /> <?php echo $radiogroup['intake_answer_body']; ?></label> &nbsp;
	<?php endforeach;?>
</div>
</div>
<?php
} elseif ($row['intake_question_type'] == 'Checkbox') {
			$radiogroups = $this->default_model->get_arrby_tbl('intake_answer', '*', "intake_question_id='" . $row['intake_question_id'] . "'", '500');

			$chkarr = explode(",", $ansR['intake_answer_id_checkbox']);
			?>
<div class="form-group <?php echo $disp_none; ?>" id="intake_form_group_<?php echo $row['intake_question_id']; ?>">
<label for="<?php echo $row['intake_question_id']; ?>"><?php echo $row['intake_question_body'];if ($row['question_required'] == 'Yes') {echo " *";} ?></label>

<div>
<?php	$ai = 0;foreach ($radiogroups as $radiogroup): $ai++;?>
	<label class="checkbox-inline"><input type="checkbox" name="intake_answer_result[<?php echo $ansR['intake_result_id']; ?>][]" value="<?php echo $radiogroup['intake_answer_id']; ?>" <?php	if (in_array($radiogroup['intake_answer_id'], $chkarr)) {echo " checked";}?> /> <?php echo $radiogroup['intake_answer_body']; ?></label> &nbsp;
	<?php endforeach;?>
</div>
</div>
<?php
} elseif ($row['intake_question_type'] == 'File') {

			$client_document = $this->crm_model->document_decrypt($ansR['intake_file_location']);
			?>
<div class="form-group <?php echo $disp_none; ?>" id="intake_form_group_<?php echo $row['intake_question_id']; ?>">
<label for="<?php echo $row['intake_question_id']; ?>"><?php echo $row['intake_question_body'];if ($row['question_required'] == 'Yes') {echo " *";} ?></label>
<input type="file" class="form-control" name="intake_file_result[<?php echo $ansR['intake_file_id']; ?>]" accept="text/plain" <?php if (!file_exists($client_document)) {echo ($row['question_required'] == 'Yes' && $required_val == "") ? 'required' : '';}?>>

<?php	if (file_exists($client_document)) {?>
<div class="alert" style="background:#d9edf7; border-color:#bce8f1; color:#009900; margin-top:10px;"><i class="fa fa-check-square-o"></i> <strong>File has been already uploaded.</strong></div>
<?php	}?>
</div>
<?php
} elseif ($row['intake_question_type'] == 'Table') {
			?>
<div class="row">
<div class="col-md-12">
<div class="form-group <?php echo $disp_none; ?>" id="intake_form_group_<?php echo $row['intake_question_id']; ?>">
<label><?php echo $row['intake_question_body']; ?></label>
<?php $ai = 0;foreach ($ansR as $ansRv) {$ai++;?>
<input type="text" class="form-control" name="intake_comment_result[<?php echo $ansRv['intake_comment_id']; ?>]" value="<?php echo $ansRv['intake_comment_body']; ?>" <?php if ($ai == 1) {echo ($row['question_required'] == 'Yes' && $required_val == "") ? 'required' : '';}?> <?php if ($ai == 1 && $row['intake_question_id'] == 40) {?> onChange="<?php $onChange?>"<?php }?>>
<?php	}?>


</div>
</div>
</div>
<?php
}

	}?>
</div>


<div class="box-footer">
<?php
if ($intake_page_no > 1) {
		$prev_page_no = ($intake_page_no - 1);
		?>
<a href="<?php echo base_url('account/intake_form?intake_page_no=' . $prev_page_no) ?>" class="btn btn-primary pull-left">&laquo; Previous</a>
<?php	}?>
<?php	if ($intake_page_no == 8) {?>
<input type="submit" class="btn btn-primary pull-right" name="Submit_intake_answer" value="Submit" />
<?php	} else {?>
<input type="submit" class="btn btn-primary pull-right" name="Submit_intake_answer" value="Next &raquo;" />
<?php	}?>
</div>
</div>
</form>
<?php	}?>
</div>

        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->

    </section>
    <!-- /.content -->
  </div>

<?php	$this->load->view("account/inc/footer");?>

</div>
<?php	$this->load->view("Admin/inc/template_js.php");?>


</body>
</html>
