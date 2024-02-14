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
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
<?php	$this->load->view("Site/inc/header_client");?>


<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><strong><?php echo $data["name"]; ?></strong></h1>
    </section>

    <!-- Main content -->
    <section class="content">


      <div class="row">
        <!-- /.col -->
        <div class="col-md-12">
<div>
<?php	$this->load->view("template/alert.php");?>

<?php
$intake_client_status_data = "No";
if (isset($client_data['intake_client_status']['status'])) {if ($client_data['intake_client_status']['status'] == "Complete") {base_url('account/intake/' . $intake_type ?? 'initial');}}

if ($intake_client_status_data == "Yes") {?>
<div class="row">
<div class="col-md-2"></div>
<div class="col-md-8">
<div class="alert alert-success text-center">
<p><i class="fa fa-thumbs-up" style="font-size:100px; margin-bottom:25px;"></i></p>
<p>Thank you for completing the <strong><?php echo $data["name"]; ?></strong>.</p>
<p>We will contact you shortly.</p>
<p>Once we have completed our analysis.</p>

<p>If you feel you made a mistake in the intake process, please email your case manager at <strong><a href="mailto:<?php echo $cmR['email']; ?>"><?php echo $cmR['email']; ?></a></strong> with the correction.</p>

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
		if ($intake_type == 'update') {
			$ansR = $this->default_model->get_arrby_tbl_single('intake_file_result', '*', "client_id='" . $GLOBALS["loguser"]["id"] . "' and intake_question_id='102'", '1');
		} else {
			$ansR = $this->default_model->get_arrby_tbl_single('intake_file_result', '*', "client_id='" . $GLOBALS["loguser"]["id"] . "' and intake_question_id='6'", '1');
		}

		$fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
		?>

<div style="background:#FFFFFF; margin:15px 0 30px 0; padding:15px; border:1px solid #F8F8F8; box-shadow:2px 2px 2px #EEEEEE;">


<table style="text-align:center; width:100%;">
<tr>	<td colspan="2">
<p style="font-size:18px;  text-align:center;"><strong>Total Federal Student Loan Debt </strong><br /><?php echo $fmt->formatCurrency(($ansR['student_total_all_loans_outstanding_principal'] + $ansR['student_total_all_loans_outstanding_interest']), "USD"); ?></p>
</td>	</tr>

<tr>	<td><p style="font-size:18px;  text-align:center;"><strong>Principal </strong><br /><?php echo $fmt->formatCurrency(($ansR['student_total_all_loans_outstanding_principal']), "USD"); ?></p></td>

<td><p style="font-size:18px;  text-align:center;"><strong>Interest </strong><br /><?php echo $fmt->formatCurrency(($ansR['student_total_all_loans_outstanding_interest']), "USD"); ?></p></td>

</tr>

</table>


<div>
<p><strong>Chronologic History of Loans:</strong></p>
<?php
$intake_file_result_id = $ansR['intake_file_id'];
		$client_id = $ansR['client_id'];
		$q = $this->db->query("SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' order by loan_date asc");
		foreach ($q->result_array() as $row) {
			?>
<div style="border:1px solid #ede7e7; padding:15px; background:#efefef42; margin-bottom:15px;">
<?php	if (isset($row['loan_date'])) {?><p style="margin-bottom:2px;"><strong>Date: </strong> <?php echo $row['loan_date']; ?></p><?php	}?>
<?php	if (isset($row['loan_attending_school'])) {?><p style="margin-bottom:2px;"><strong>School: </strong> <?php echo $row['loan_attending_school']; ?></p><?php	}?>
<?php	if (isset($row['loan_type'])) {?><p style="margin-bottom:2px;"><strong>Loan Type: </strong> <?php echo $row['loan_type']; ?></p><?php	}?>
<?php	if (isset($row['loan_dispersed_amount'])) {?><p style="margin-bottom:2px;"><strong>Origination amount: </strong> <?php echo $fmt->formatCurrency(($row['loan_dispersed_amount']), "USD"); ?></p><?php	}?>
<?php	if (isset($row['loan_outstanding_principal_balance'])) {?><p style="margin-bottom:2px;"><strong>Current Principal: </strong> <?php echo $fmt->formatCurrency(($row['loan_outstanding_principal_balance']), "USD"); ?></p><?php	}?>
<?php	if (isset($row['loan_outstanding_interest_balance'])) {?><p style="margin-bottom:2px;"><strong>Current Interest: </strong> <?php echo $fmt->formatCurrency(($row['loan_outstanding_interest_balance']), "USD"); ?></p><?php	}?>

<?php	if (isset($row['loan_interest_rate_type_description'])) {?><p style="margin-bottom:2px;"><strong>Loan Interest Rate Type Description: </strong> <?php echo $row['loan_interest_rate_type_description']; ?></p><?php	}?>

<?php	if (isset($row['loan_interest_rate'])) {?><p style="margin-bottom:2px;"><strong>Loan Interest Rate: </strong> <?php echo $row['loan_interest_rate'] . "%"; ?></p><?php	}?>

<?php	if (isset($row['loan_status'])) {?><p style="margin-bottom:0px;"><strong>Last status and date: </strong> <?php echo $row['loan_status_description']; ?> <?php	if (isset($row['loan_status_effective_date'])) {echo ", " . $row['loan_status_effective_date'];}?></p><?php	}?>

<?php
if (isset($row['loan_contact_type'])) {
				$caddress = "";

				if (trim($row['loan_contact_street_address_1']) != "") {$caddress = $row['loan_contact_street_address_1'];}
				if (trim($row['loan_contact_street_address_2']) != "") {$caddress .= " " . $row['loan_contact_street_address_2'];}
				if (trim($row['loan_contact_city']) != "") {$caddress .= ", " . $row['loan_contact_city'];}
				if (trim($row['loan_contact_state']) != "") {$caddress .= ", " . $row['loan_contact_state'];}
				if (trim($row['loan_contact_zip_code']) != "") {$caddress .= " - " . $row['loan_contact_zip_code'];}

				echo '<p style="margin-bottom:2px;"><strong>' . $row['loan_contact_type'] . ': </strong> ' . $row['loan_contact_name'] . ' &nbsp; - &nbsp; ' . $caddress . '</p>';
			}
			?>
<?php	if (isset($row['current_lender'])) {?><p style="margin-bottom:2px;"><strong>Current Lender: </strong> <?php echo $row['current_lender']; ?></p><?php	}?>
<?php	if (isset($row['current_guarantee_agency'])) {?><p style="margin-bottom:2px;"><strong>Current Guarantee Agency: </strong> <?php echo $row['current_guarantee_agency']; ?></p><?php	}?>

</div>
<?php	}?>
</div>

</div>
<?php	}?>


<?php

	if ($intake_type == 'update') {
		$q = 96;
		$a = 74;
	} else {
		$q = 0;
		$a = 0;
	}
	$table = '<label class="text-danger" id="prvtloanNote">Note: Do not enter the loans that are on your studentaid.gov report. All fields are required. If you do not know the answer enter Unknown if a text field. Note: You may be required to provide this information at a later date so looking it up now is better. For Balance and interest rate, make a best guess if you do not know the actual values.</label><table class="table table-bordered table-striped"><thead><tr>';

	foreach ($intake_question_data as $row) {
		if ($row['intake_question_type'] == 'Table') {
			$ansR = $this->crm_model->admin_intake_answer_by_client($GLOBALS["loguser"]["id"], $row['intake_question_id']);

			$onclick = '';
			$disp_none = '';
			$required_val = '';

			if ($row['intake_question_id'] > 34 && $row['intake_question_id'] < 45) {
				if ($intake_ans_34 == ($a + 0)) {
					$disp_none = "disp_none";
					$required_val = "none";
				}
			}

			/*if ($row['intake_question_id'] > 40 && $row['intake_question_id'] < 45) {
					if (strtolower($intake_ans_40) != "forbearance" && strtolower($intake_ans_40) != "forbearance/deferment" && strtolower($intake_ans_40) != "deferment" && strtolower($intake_ans_40) != "late" && strtolower($intake_ans_40) != "default") {
						$disp_none = "disp_none";
						$required_val = "none";
					}
				}
			*/

			if ($row['intake_question_id'] == ($q + 44)) {
				if ($intake_ans_43 != ($a + 58)) {
					$disp_none = "disp_none";
					$required_val = "none";
				}
			}

			$table .= '<th class="intake_head ' . $disp_none . '" id="intake_thead_' . ($intake_type == 'update' ? ($row['intake_question_id'] - 96) : $row['intake_question_id']) . '">' . $row['intake_question_body'] . '</th>';
		}
	}
	$table .= '</tr></thead><tbody>';
	$tr = [];

	foreach ($intake_question_data as $row) {
		$ansR = $this->crm_model->admin_intake_answer_by_client($GLOBALS["loguser"]["id"], $row['intake_question_id']);

		$onclick = '';
		$disp_none = '';
		$required_val = '';
		if ($row['intake_question_id'] == ($q + 8) || $row['intake_question_id'] == ($q + 9)) {
			if ($intake_ans_7 != ($a + 5)) {
				$disp_none = "disp_none";
				$required_val = "none";
			}
		}

		if ($row['intake_question_id'] > ($q + 11) && $row['intake_question_id'] < ($q + 17)) {
			if ($intake_ans_11 != ($a + 15)) {
				$disp_none = "disp_none";
				$required_val = "none";
			}
		}

		if ($row['intake_question_id'] > ($q + 23) && $row['intake_question_id'] < ($q + 27)) {
			if ($intake_ans_23 != ($a + 25)) {
				$disp_none = "disp_none";
				$required_val = "none";
			}
		}

		if ($row['intake_question_id'] > ($q + 24) && $row['intake_question_id'] < ($q + 27)) {
			if ($intake_ans_24 != ($a + 27)) {
				$disp_none = "disp_none";
				$required_val = "none";
			}
		}

		/*if ($row['intake_question_id'] > ($q + 25) && $row['intake_question_id'] < ($q + 27)) {
				if ($intake_ans_25 != ($a + 28)) {
					$disp_none = "disp_none";
					$required_val = "none";
				}
			}
		*/

		if ($row['intake_question_id'] > ($q + 28) && $row['intake_question_id'] < ($q + 32)) {$required_val = "none";}

		if ($row['intake_question_id'] == ($q + 33)) {
			if ($intake_ans_32 != ($a + 56)) {
				$disp_none = "disp_none";
				$required_val = "none";
			}
		}

		if ($row['intake_question_id'] == ($q + 33)) {
			if ($intake_ans_32 != ($a + 56)) {
				$disp_none = "disp_none";
				$required_val = "none";
			}
		}

		if ($row['intake_question_id'] > 34 && $row['intake_question_id'] < 45) {
			if ($intake_ans_34 == ($a + 0)) {
				$disp_none = "disp_none";
				$required_val = "none";
			}
		}

		/*if ($row['intake_question_id'] > 40 && $row['intake_question_id'] < 45) {
				if (strtolower($intake_ans_40) != "forbearance" && strtolower($intake_ans_40) != "forbearance/deferment" && strtolower($intake_ans_40) != "deferment" && strtolower($intake_ans_40) != "late" && strtolower($intake_ans_40) != "default") {
					$disp_none = "disp_none";
					$required_val = "none";
				}
			}
		*/

		if ($row['intake_question_id'] == ($q + 44)) {
			if ($intake_ans_43 != ($a + 58)) {
				$disp_none = "disp_none";
				$required_val = "none";
			}
		}

###########
		if ($row['intake_question_id'] == ($q + 7)) {
			$onclick = 'onChange="check_question_7(this.value)"';
			$intake_ans_7 = $ansR['intake_answer_id'];
			?>
<script type="text/javascript">
function check_question_7(val)
{
	if(val == (<?=$a + 5?>))
	{
		$("#intake_form_group_8 input[type=text]").attr("required","required");
		$("#intake_form_group_8, #intake_form_required_8").show('100');

		$("#intake_form_group_9 input[type=text]").attr("required","required");
		$("#intake_form_group_9, #intake_form_required_9").show('100');
	}
	else
	{
		$("#intake_form_group_8 input[type=text]").removeAttr("required");
		$("#intake_form_group_8, #intake_form_required_8").hide('100');

		$("#intake_form_group_9 input[type=text]").removeAttr("required");
		$("#intake_form_group_9, #intake_form_required_9").hide('100');
	}

}
setTimeout(function(){
check_question_7(<?=$intake_ans_7?>);
},2000);
</script>
<?php }

		if ($row['intake_question_id'] == ($q + 11)) {
			$onclick = 'onChange="check_question_11(this.value)"';
			$intake_ans_11 = $ansR['intake_answer_id'];
			?>
<script type="text/javascript">
function check_question_11(val)
{
	//if(val == '15' || val == '72')
	if(val == (<?=$a + 15?>))
	{
		$("#intake_form_group_12 input[type=radio]:first").attr("required","required");
		$("#intake_form_group_12, #intake_form_required_12").show('100');

		$("#intake_form_group_14 input[type=radio]:first").attr("required","required");
		$("#intake_form_group_14, #intake_form_required_14").show('100');

		if($("#intake_form_group_12 input[type=radio]:checked").val() == '16')
		{
			$("#intake_form_group_13 input[type=email]").attr("required","required");
			$("#intake_form_group_13, #intake_form_required_13").show('100');
		}
		else
		{
			$("#intake_form_group_13 input[type=email]").removeAttr("required");
			$("#intake_form_group_13, #intake_form_required_13").hide('100');
		}
	}
	else
	{
		$("#intake_form_group_12 input[type=radio]").removeAttr("required");
		$("#intake_form_group_12, #intake_form_required_12").hide('100');

		$("#intake_form_group_13 input[type=email]").removeAttr("required");
		$("#intake_form_group_13, #intake_form_required_13").hide('100');

		$("#intake_form_group_14 input[type=radio]").removeAttr("required");
		$("#intake_form_group_14, #intake_form_required_14").hide('100');
	}


	check_question_14($("#intake_form_group_14 input[type=radio]:checked").val());
}
setTimeout(function(){
check_question_11(<?=$intake_ans_11?>);
},2000);
</script>
<?php }

		if ($row['intake_question_id'] == ($q + 12)) {
			$onclick = 'onChange="check_question_12(this.value)"';
			$intake_ans_12 = $ansR['intake_answer_id'];
			?>
<script type="text/javascript">
function check_question_12(val)
{
	if(val == (<?=$a + 16?>))
	{
		$("#intake_form_group_13 input[type=email]").attr("required","required");
		$("#intake_form_group_13, #intake_form_required_13").show('100');
	}
	else
	{
		$("#intake_form_group_13 input[type=email]").removeAttr("required");
		$("#intake_form_group_13, #intake_form_required_13").hide('100');
	}
}
setTimeout(function(){
check_question_12(<?=$intake_ans_12?>);
},2000);
</script>
<?php	}
		if ($row['intake_question_id'] == ($q + 14)) {
			$onclick = 'onChange="check_question_14(this.value)"';
			$intake_ans_14 = $ansR['intake_answer_id'];
			?>
<script type="text/javascript">
function check_question_14(val)
{
	var val2 = $("#intake_form_group_11 input[type=radio]:checked").val();

	//if(val == '18' && (val2 == '15' || val2 == '72'))
	if(val2 == (<?=$a + 15?>))
	{

		if($("#intake_form_group_14 input[type=radio]:checked").is(":checked")) {} else {	$(".radio_group_14_1").prop('checked', 'true');	}

		var val = $("#intake_form_group_14 input[type=radio]:checked").val();

		if(val==(<?=$a + 18?>))
		{
			$("#intake_form_group_15 input[type=text]").removeAttr("required");
			$("#intake_form_group_15, #intake_form_required_15").hide('100');
			$("#intake_form_input_15").val("");

			$("#intake_form_group_16 input[type=text]").attr("required","required");
			$("#intake_form_group_16, #intake_form_required_16").show('100');
		} else if(val==(<?=$a + 19?>))
		{
			$("#intake_form_group_15 input[type=text]").attr("required","required");
			$("#intake_form_group_15, #intake_form_required_15").show('100');

			$("#intake_form_group_16 input[type=text]").attr("required","required");
			$("#intake_form_group_16, #intake_form_required_16").show('100');
		}


	}
	else
	{
		$("#intake_form_group_15 input[type=text]").removeAttr("required");
		$("#intake_form_group_15, #intake_form_required_15").hide('100');
		$("#intake_form_input_15").val("");

		$("#intake_form_group_16 input[type=text]").removeAttr("required");
		$("#intake_form_group_16, #intake_form_required_16").hide('100');
		$("#intake_form_input_16").val("0");
	}

	if(val2 != '<?=$a + 15?>' && val2 != '<?=$a + 72?>') {	$(".radio_group_14").removeAttr('checked');	}


}
setTimeout(function(){
check_question_14(<?=$intake_ans_14?>);
},2000);
</script>
<?php	}

		if ($row['intake_question_id'] == ($q + 21)) {
			$onclick = 'onChange="check_question_21(this.value)"';
			$intake_ans_21 = $ansR['intake_answer_id'];
			?>
<script type="text/javascript">
function check_question_21(val)
{
	if(val == (<?=$a + 20?>))
	{
		$("#intake_form_group_22 input[type=radio]:first").attr("required","required");
		$("#intake_form_group_22, #intake_form_required_22").show('100');
	}
	else
	{
		$("#intake_form_group_22 input[type=radio]").removeAttr("required");
		$("#intake_form_group_22, #intake_form_required_22").hide('100');
	}

}
setTimeout(function(){
check_question_21(<?=$intake_ans_21?>);
},2000);
</script>
<?php }

		if ($row['intake_question_id'] == ($q + 23)) {
			$onclick = 'onChange="check_question_23(this.value)"';
			$intake_ans_23 = $ansR['intake_answer_id'];
			?>
<script type="text/javascript">
function check_question_23(val)
{

	if(val == (<?=$a + 25?>))
	{
		$("#intake_form_group_24 input[type=radio]:first").attr("required","required");
		$("#intake_form_group_24, #intake_form_required_24").show('100');

		$("#intake_form_group_25 input[type=text]").attr("required","required");
		$("#intake_form_group_25, #intake_form_required_25").show('100');

		//$("#intake_form_group_26 input[type=checkbox]").attr("required","required");
		$("#intake_form_group_26, #intake_form_required_26").show('100');

		//$("#intake_form_group_27 input[type=radio]").attr("required","required");
		//$("#intake_form_group_27, #intake_form_required_27").show('100');
	}
	else
	{
		$("#intake_form_group_24 input[type=radio]").removeAttr("required");
		$("#intake_form_group_24, #intake_form_required_24").hide('100');

		$("#intake_form_group_25 input[type=text]").removeAttr("required");
		$("#intake_form_group_25, #intake_form_required_25").hide('100');

		$("#intake_form_group_26 input[type=checkbox]").removeAttr("required");
		$("#intake_form_group_26, #intake_form_required_26").hide('100');

		//$("#intake_form_group_27 input[type=radio]").removeAttr("required");
		//$("#intake_form_group_27, #intake_form_required_27").hide('100');
	}
}
setTimeout(function(){

check_question_23(<?=$intake_ans_23?>);
},2000);
</script>
<?php }

		if ($row['intake_question_id'] == ($q + 24)) {
			$onclick = 'onChange="check_question_24(this.value)"';
			$intake_ans_24 = $ansR['intake_answer_id'];
			?>
<script type="text/javascript">
function check_question_24(val)
{
	if(val == (<?=$a + 27?>))
	{
		$("#intake_form_group_25 input[type=text]").attr("required","required");
		$("#intake_form_group_25, #intake_form_required_25").show('100');

		//$("#intake_form_group_26 input[type=checkbox]").attr("required","required");
		$("#intake_form_group_26, #intake_form_required_26").show('100');

		//$("#intake_form_group_27 input[type=radio]").attr("required","required");
		//$("#intake_form_group_27, #intake_form_required_27").show('100');
	}
	else
	{
		$("#intake_form_group_25 input[type=text]").removeAttr("required");
		$("#intake_form_group_25, #intake_form_required_25").hide('100');

		$("#intake_form_group_26 input[type=checkbox]").removeAttr("required");
		$("#intake_form_group_26, #intake_form_required_26").hide('100');

		//$("#intake_form_group_27 input[type=radio]").removeAttr("required");
		//$("#intake_form_group_27, #intake_form_required_27").hide('100');
	}
}
setTimeout(function(){
check_question_24(<?=$intake_ans_24?>);
},2000);
</script>
<?php }

		/*if ($row['intake_question_id'] == ($q + 25)) {
			$onclick = 'onChange="check_question_25(this.value)"';
			$intake_ans_25 = $ansR['intake_answer_id'];
			*/
		?>
<script type="text/javascript">
/*function check_question_25(val)
{
	if(val == (<?=$a + 28?>))
	{
		//$("#intake_form_group_26 input[type=checkbox]").attr("required","required");
		$("#intake_form_group_26, #intake_form_required_26").show('100');

		//$("#intake_form_group_27 input[type=radio]").attr("required","required");
		//$("#intake_form_group_27, #intake_form_required_27").show('100');
	}
	else
	{
		$("#intake_form_group_26 input[type=checkbox]").removeAttr("required");
		$("#intake_form_group_26, #intake_form_required_26").hide('100');

		//$("#intake_form_group_27 input[type=radio]").removeAttr("required");
		//$("#intake_form_group_27, #intake_form_required_27").hide('100');
	}

}
setTimeout(function(){
check_question_25(<?=$intake_ans_25?>);
},2000);
*/
</script>
<?php
// }

		if ($row['intake_question_id'] == ($q + 28)) {
			$onclick = 'onChange="check_question_28(this.value)"';
			$intake_ans_28 = $ansR['intake_answer_id'];
			?>
<script type="text/javascript">
function check_question_28(val)
{
	$("#intake_form_group_29 input[type=text]").removeAttr("required");
	$("#intake_form_group_29, #intake_form_required_29").hide('100');

	$("#intake_form_group_30 input[type=radio]").removeAttr("required");
	$("#intake_form_group_30, #intake_form_required_30").hide('100');

	if(val == (<?=$a + 46?>))
	{
		$("#intake_form_group_29 input[type=text]").attr("required","required");
		$("#intake_form_group_29, #intake_form_required_29").show('100');

		$("#intake_form_group_30 input[type=radio]:first").attr("required","required");
		$("#intake_form_group_30, #intake_form_required_30").show('100');
	}
	else if(val == (<?=$a + 48?>))
	{
		$("#intake_form_group_30 input[type=radio]:first").attr("required","required");
		$("#intake_form_group_30, #intake_form_required_30").show('100');
	}
	else {}

}
setTimeout(function(){
check_question_28(<?=$intake_ans_28?>);
},2000);
</script>
<?php }

		if ($row['intake_question_id'] == ($q + 32)) {
			$onclick = 'onChange="check_question_32(this.value)"';
			$intake_ans_32 = $ansR['intake_answer_id'];
			?>
<script type="text/javascript">
function check_question_32(val)
{
	if(val == (<?=$a + 56?>))
	{
		//$("#intake_form_group_33 input[type=text]").attr("required","required");
		$("#intake_form_group_33, #intake_form_required_33").show('100');
	}
	else
	{
		$("#intake_form_group_33 input[type=file]").removeAttr("required");
		$("#intake_form_group_33, #intake_form_required_33").hide('100');
	}
}
setTimeout(function(){
check_question_32(<?=$intake_ans_32?>);
},2000);
</script>
<?php }

		if ($row['intake_question_id'] == ($q + 34)) {
			$onChange = 'check_question_34(this.value)';
			$intake_ans_34 = $ansR['intake_comment_body'];
			?>
<script type="text/javascript">
function check_question_34(val)
{

	if(val > 0 && val != undefined)
	{
		$('#prvtloanNote').show();
		var count35 = $(".intake_tbody").length;

		if(count35 > val){
			for(var i = count35; i > val; i--){
				$("#intake_bodyrow_"+(i-1)).remove();
			}
		}
		else{

			var html = '';

			for(var i = count35; i < val; i++){
				var h35 = $("#intake_bodyrow_0")[0].outerHTML;
				var h35val = $("#intake_tbody_35_0 input[type=text]:first")[0].value;
				var h36val = $("#intake_tbody_36_0 input[type=text]:first")[0].value;
				var h37val = $("#intake_tbody_37_0 input[type=text]:first")[0].value;
				var h38val = $("#intake_tbody_38_0 input[type=text]:first")[0].value;
				var h39val = $("#intake_tbody_39_0 input[type=text]:first")[0].value;
				var h40val = $("#intake_tbody_40_0 input[type=text]:first")[0].value;
				var h41val = $("#intake_tbody_41_0 input[type=text]:first")[0].value;
				var h42val = $("#intake_tbody_42_0 input[type=text]:first")[0].value;


				h35=h35.replace('value="'+h35val+'"','');
				h35=h35.replace('value="'+h36val+'"','value="0"');
				h35=h35.replace('value="'+h37val+'"','value="0"');
				h35=h35.replace('value="'+h38val+'"','');
				h35=h35.replace('value="'+h39val+'"','value="0"');
				h35=h35.replace('value="'+h40val+'"','');
				h35=h35.replace('value="'+h41val+'"','');
				h35=h35.replace('value="'+h42val+'"','value="0"');
				h35=h35.replaceAll('_0','_'+i);


				html += h35;
			}

			$("#intake_bodyrow_"+(count35-1)).closest('table').append(html);
		}

		$('.intake_head').show('100');

		// $(".intake_tbody_35 input[type=text]:first").attr("required","required");
		$(".intake_tbody_35, #intake_form_required_35").show('100');

		// $(".intake_tbody_36 input[type=text]:first").attr("required","required");
		$(".intake_tbody_36, #intake_form_required_36").show('100');

		// $(".intake_tbody_37 input[type=text]:first").attr("required","required");
		$(".intake_tbody_37, #intake_form_required_37").show('100');

		// $(".intake_tbody_38 input[type=text]:first").attr("required","required");
		$(".intake_tbody_38, #intake_form_required_38").show('100');

		// $(".intake_tbody_39 input[type=text]:first").attr("required","required");
		$(".intake_tbody_39, #intake_form_required_39").show('100');

		// $(".intake_tbody_40 input[type=text]:first").attr("required","required");
		$(".intake_tbody_40, #intake_form_required_40").show('100');

		// $(".intake_tbody_41 input[type=text]:first").attr("required","required");
		$(".intake_tbody_41, #intake_form_required_41").show('100');


		// $(".intake_tbody_42 input[type=text]:first").attr("required","required");
		$(".intake_tbody_42, #intake_form_required_42").show('100');

		// $("#intake_form_group_43 input[type=radio]").attr("required","required");
		$("#intake_form_group_43, #intake_form_required_43").show('100');

		//$("#intake_form_group_44 input[type=file]").attr("required","required");
		$("#intake_form_group_44, #intake_form_required_44").show('100');

		// $(".intake_tbody_40 input[type=text]:first").trigger('change');

		$(".intake_tbody_40").each(function(index,item){
			check_question_40($(this).find('input[type=text]:first').val(),$(this).find('input[type=text]:first'));
		});


		check_question_43($("#intake_form_group_43 input[type='radio']:checked").val());

	}
	else
	{
		$('#prvtloanNote').hide();
		$(".intake_tbody_35 input[type=text]").removeAttr("required");
		$("#intake_thead_35, .intake_tbody_35, #intake_form_required_35").hide('100');

		$(".intake_tbody_36 input[type=text]").removeAttr("required");
		$("#intake_thead_36, .intake_tbody_36, #intake_form_required_36").hide('100');

		$(".intake_tbody_37 input[type=text]").removeAttr("required");
		$("#intake_thead_37, .intake_tbody_37, #intake_form_required_37").hide('100');

		$(".intake_tbody_38 input[type=text]").removeAttr("required");
		$("#intake_thead_38,.intake_tbody_38, #intake_form_required_38").hide('100');

		$(".intake_tbody_39 input[type=text]").removeAttr("required");
		$("#intake_thead_39, .intake_tbody_39, #intake_form_required_39").hide('100');

		$(".intake_tbody_40 input[type=text]").removeAttr("required");
		$("#intake_thead_40, .intake_tbody_40, #intake_form_required_40").hide('100');

		$(".intake_tbody_41 input[type=text]").removeAttr("required");
		$("#intake_thead_41, .intake_tbody_41, #intake_form_required_41").hide('100');

		$(".intake_tbody_42 input[type=text]").removeAttr("required");
		$("#intake_thead_42, .intake_tbody_42, #intake_form_required_42").hide('100');

		$("#intake_form_group_43 input[type=radio]").removeAttr("required");
		$("#intake_form_group_43, #intake_form_required_43").hide('100');

		$("#intake_form_group_44 input[type=file]").removeAttr("required");
		$("#intake_form_group_44, #intake_form_required_44").hide('100');
	}
}
setTimeout(function(){
check_question_34(<?=$intake_ans_34?>);
},2000);
</script>
<?php }
		if ($row['intake_question_id'] == ($q + 45) || $row['intake_question_id'] == ($q + 46)) {
			$onChange = 'check_question_45(this,' . $row['intake_question_id'] . ')';
			$intake_ans_34 = $ansR['intake_comment_body'];
			?>
<script type="text/javascript">
function check_question_45(elem,id)
{

	var length=$(elem).val().length;
	$('#count_'+id).text(length);
}
setTimeout(function(){
check_question_45(<?=$intake_ans_34?>);
},2000);
</script>
<?php }

		if ($row['intake_question_id'] == ($q + 40)) {
			$onChange = 'check_question_40(this.value,this)';
			$intake_ans_40 = $ansR['intake_comment_body'];
			?>
<script type="text/javascript">
function check_question_40(val,elem)
{
	if(elem != undefined){
	if(val != undefined)
		val = val.toLowerCase();

	var id=$(elem).closest('td').attr('id').replace('intake_tbody_40_','');

	/*if(val== "forbearance" || val== "forbearance/deferment" || val== "deferment" || val== "late" || val== "default")
	{
		$("#intake_tbody_41_"+id+" input[type=text]").attr("required","required");
		$("#intake_tbody_41_"+id+", #intake_form_required_41").show('100');

		$("#intake_tbody_42_"+id+" input[type=text]").attr("required","required");
		$("#intake_tbody_42_"+id+", #intake_form_required_42").show('100');

		$("#intake_tbody_43 input[type=text]").attr("required","required");
		$("#intake_tbody_43, #intake_form_required_43").show('100');

		//$("#intake_tbody_44 input[type=text]").attr("required","required");
		$("#intake_tbody_44, #intake_form_required_44").show('100');
	}
	else
	{
		$("#intake_tbody_41_"+id+" input[type=text]").removeAttr("required");
		$("#intake_tbody_41_"+id+", #intake_form_required_41").hide('100');

		$("#intake_tbody_42_"+id+" input[type=text]").removeAttr("required");
		$("#intake_tbody_42_"+id+", #intake_form_required_42").hide('100');

		$("#intake_tbody_43 input[type=text]").removeAttr("required");
		$("#intake_tbody_43, #intake_form_required_43").hide('100');

		//$("#intake_tbody_44 input[type=text]").removeAttr("required");
		$("#intake_tbody_44, #intake_form_required_44").hide('100');
	}
	*/
}
}setTimeout(function(){
check_question_40(<?=$intake_ans_40?>);
},2000);
</script>
<?php }

		if ($row['intake_question_id'] == ($q + 43)) {
			$onclick = 'onChange="check_question_43(this.value)"';
			$intake_ans_43 = $ansR['intake_answer_id'];
			?>
<script type="text/javascript">
function check_question_43(val)
{
	if(val == (<?=$a + 58?>))
	{
		<?php
$client_document = $this->crm_model->document_decrypt($ansR['intake_file_location']);
			if (!file_exists($client_document)) {
				?>
		//$("#intake_form_group_44 input[type=file]").attr("required","required");
		<?php	}?>
		$("#intake_form_group_44").show('100');
		$("#intake_form_group_44, #intake_form_required_44").show('100');
	}
	else
	{
		$("#intake_form_group_44 input[type=file]").removeAttr("required");
		$("#intake_form_group_44").hide('100');
		$("#intake_form_group_44, #intake_form_required_44").hide('100');
	}
}
setTimeout(function(){
check_question_43(<?=$intake_ans_43?>);
},2000);
</script>
<?php }

		?>


<?php

		if ($row['intake_question_type'] == 'Comment') {
			?>
<div class="form-group <?php echo $disp_none; ?>" id="intake_form_group_<?php echo ($intake_type == 'update') ? ($row['intake_question_id'] - 96) : $row['intake_question_id']; ?>">
<label for="<?php echo ($intake_type == 'update') ? ($row['intake_question_id'] - 96) : $row['intake_question_id']; ?>"><?php echo $row['intake_question_body'];if ($row['question_required'] == 'Yes' || $row['question_required'] == 'No') {echo ' <span class="' . $disp_none . '" id="intake_form_required_' . (($intake_type == 'update') ? ($row['intake_question_id'] - 96) : $row['intake_question_id']) . '">*</span>';} ?></label>
<?php
$name = "intake_comment_result[" . $ansR['intake_comment_id'] . "]";
			$input_id = "intake_form_input_" . (($intake_type == 'update') ? ($row['intake_question_id'] - 96) : $row['intake_question_id']);
			if ($row['intake_question_id'] == ($q + 13)) {$input_type = 'email';} else if ($row['intake_question_id'] == ($q + 34)) {$input_type = 'number';} else { $input_type = 'text';}
			$input_data = ['type' => $input_type, 'class' => 'form-control', 'name' => $name, 'value' => $ansR['intake_comment_body'], 'id' => $input_id];
			if ($row['question_required'] == 'Yes' && $required_val == "") {$input_data['required'] = "required";}
			if ($row['intake_question_id'] == ($q + 34)) {
				$input_data['min'] = "0";
				$input_data['max'] = "500";
				$input_data['onChange'] = $onChange;}
			if ($row['intake_question_id'] == ($q + 45) || $row['intake_question_id'] == ($q + 46)) {
				$input_data['maxlength'] = "5000";
				$input_data['onKeyup'] = $onChange;}

			if ($row['intake_question_id'] >= ($q + 15) && $row['intake_question_id'] <= ($q + 20)) {$input_data['oninput'] = "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1').replace(/^0[^.]/, '0');";}

			if ($row['intake_question_id'] >= ($q + 15) && $row['intake_question_id'] <= ($q + 18)) {$input_data['maxlength'] = 7;}

			if ($row['intake_question_id'] == ($q + 19) || $row['intake_question_id'] == ($q + 20)) {$input_data['maxlength'] = 3;}

			echo form_input($input_data);

			if ($row['intake_question_id'] == ($q + 45) || $row['intake_question_id'] == ($q + 46)) {
				?>
				<label><span id="count_<?=(($intake_type == 'update') ? ($row['intake_question_id'] - 96) : $row['intake_question_id'])?>"><?=strlen($ansR['intake_comment_body'])?></span>/5000</label>
				<?php
}
			?>
</div>
<?php
} elseif ($row['intake_question_type'] == 'Radio' || $row['intake_question_type'] == 'Radio Group') {
			$radiogroups = $this->default_model->get_arrby_tbl('intake_answer', '*', "intake_question_id='" . $row['intake_question_id'] . "' and status_flag='Active'", '500');
			?>
<div class="form-group <?php echo $disp_none; ?>" id="intake_form_group_<?php echo ($intake_type == 'update') ? ($row['intake_question_id'] - 96) : $row['intake_question_id']; ?>">
<label for="<?php echo ($intake_type == 'update') ? ($row['intake_question_id'] - 96) : $row['intake_question_id']; ?>"><?php echo $row['intake_question_body'];if ($row['question_required'] == 'Yes' || $row['question_required'] == 'No') {echo ' <span class="' . $disp_none . '" id="intake_form_required_' . (($intake_type == 'update') ? ($row['intake_question_id'] - 96) : $row['intake_question_id']) . '">*</span>';} ?></label>
<div>
<?php	$ai = 1235;
			$aii = 0;foreach ($radiogroups as $radiogroup): $ai++;
				$aii++;?>																																																														<label class="radio-inline"><input type="radio"  name="intake_answer_result[<?php echo $ansR['intake_result_id']; ?>]" value="<?php echo $radiogroup['intake_answer_id']; ?>" <?php	if ($radiogroup['intake_answer_id'] == $ansR['intake_answer_id'] || $ai == 1) {echo " checked";}?> <?php if ($aii == 1) {if ($row['intake_question_id'] != ($q + 12) && $row['intake_question_id'] != '14' && $row['intake_question_id'] != ($q + 23) && $row['intake_question_id'] != '24' && $row['intake_question_id'] != '27' && $row['intake_question_id'] != ($q + 30) && $row['intake_question_id'] != '31') {echo ($row['question_required'] == 'Yes' && $required_val == "") ? 'required' : 'required';}}?> <?php	echo $onclick; ?> class="radio_group_<?php echo ($intake_type == 'update') ? ($row['intake_question_id'] - 96) : $row['intake_question_id']; ?> radio_group_<?php echo ($intake_type == 'update') ? ($row['intake_question_id'] - 96) : $row['intake_question_id'] . "_" . $aii; ?>" /> <?php echo $radiogroup['intake_answer_body']; ?></label> &nbsp;																																																														<?php endforeach;?>
</div>
</div>
<?php
} elseif ($row['intake_question_type'] == 'Checkbox') {
			$radiogroups = $this->default_model->get_arrby_tbl('intake_answer', '*', "intake_question_id='" . $row['intake_question_id'] . "'", '500');

			$chkarr = explode(",", $ansR['intake_answer_id_checkbox']);
			?>
<div class="form-group <?php echo $disp_none; ?>" id="intake_form_group_<?php echo (($intake_type == 'update') ? ($row['intake_question_id'] - 96) : $row['intake_question_id']); ?>">
<label for="<?php echo (($intake_type == 'update') ? ($row['intake_question_id'] - 96) : $row['intake_question_id']); ?>"><?php echo $row['intake_question_body'];if ($row['question_required'] == 'Yes' || $row['question_required'] == 'No') {echo ' <span class="' . $disp_none . '" id="intake_form_required_' . (($intake_type == 'update') ? ($row['intake_question_id'] - 96) : $row['intake_question_id']) . '">*</span>';} ?></label>

<div>
<?php	if (count($radiogroups) > 0) {?><input type="checkbox" name="intake_answer_result[<?php echo $ansR['intake_result_id']; ?>][]" value="0" checked style="display:none;" class="radio_group_<?php echo ($intake_type == 'update') ? ($row['intake_question_id'] - 96) : $row['intake_question_id']; ?>" /><?php	}?>
<?php	$ai = 0;foreach ($radiogroups as $radiogroup): $ai++;?>																																																														<label class="checkbox-inline"><input type="checkbox" name="intake_answer_result[<?php echo $ansR['intake_result_id']; ?>][]" value="<?php echo $radiogroup['intake_answer_id']; ?>" <?php	if (in_array($radiogroup['intake_answer_id'], $chkarr)) {echo " checked";}?> class="radio_group_<?php echo ($intake_type == 'update') ? ($row['intake_question_id'] - 96) : $row['intake_question_id']; ?>" /> <?php echo $radiogroup['intake_answer_body']; ?></label> &nbsp;																																																														<?php endforeach;?>
</div>
</div>
<?php
} elseif ($row['intake_question_type'] == 'File') {

			$client_document = $this->crm_model->document_decrypt($ansR['intake_file_location']);
			?>
<div class="form-group <?php echo $disp_none; ?>" id="intake_form_group_<?php echo (($intake_type == 'update') ? ($row['intake_question_id'] - 96) : $row['intake_question_id']); ?>">
<label for="<?php echo (($intake_type == 'update') ? ($row['intake_question_id'] - 96) : $row['intake_question_id']); ?>"><?php echo $row['intake_question_body'];if ($row['question_required'] == 'Yes') {echo ' <span class="' . $disp_none . '" id="intake_form_required_' . (($intake_type == 'update') ? ($row['intake_question_id'] - 96) : $row['intake_question_id']) . '">*</span>';} ?>
<?php	if ($row['intake_question_id'] == ($q + 6)) {?> &nbsp; <a href="<?php echo base_url("nslds-upload-instructions"); ?>" target="_blank" class="btn btn-default btn-xs" style="color:#0033CC;"><i class="fa fa-info-circle"></i> Upload Help</a><?php }?>
</label>
<input type="file" class="form-control" name="intake_file_result[<?php echo $ansR['intake_file_id']; ?>]" <?php if ($row['intake_question_id'] == ($q + 6)) {echo ' accept="text/plain"';} else {echo ' accept="image/*, application/pdf, application/vnd.ms-excel, .csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/msword, application/vnd.ms-powerpoint, text/plain, .doc, .docx, .xls, .xlsx, .ppt, .pptx"';}?> <?php if (!file_exists($client_document)) {echo ($row['question_required'] == 'Yes' && $required_val == "") ? 'required' : '';}?>>



<?php	if (file_exists($client_document)) {?>
<div class="alert" style="background:#d9edf7; border-color:#bce8f1; color:#009900; margin-top:10px;"><i class="fa fa-check-square-o"></i> <strong>File has been already uploaded.</strong></div>
<?php	}?>



<?php	if ($row['intake_question_id'] == ($q + 6)) {?>
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
<?php	}?>

</div>
<?php
} elseif ($row['intake_question_type'] == 'Table') {
			if (count($ansR) > 0) {
				$ai = 0;
				foreach ($ansR as $ansRv) {
					$tr[$ai][$row['intake_question_id']] = '<input type="text" class="form-control" name="intake_table_result[' . $row['intake_question_id'] . '][]" value="' . $ansRv['intake_comment_body'] . '" ' . ($ai == 1 ? (($row['question_required'] == 'Yes' && $required_val == "") ? 'required' : '') : '') . '>';
					?>

<?php
$ai++;
				}
			} else {

				$tr[0][$row['intake_question_id']] = '<input type="text" class="form-control" name="intake_table_result[' . $row['intake_question_id'] . '][]" value="' . (in_array($row['intake_question_id'], [$q + 36, $q + 37, $q + 39, $q + 42]) ? '0' : '') . '">';

			}
		}

	}
	foreach ($tr as $key => $value) {
		$table .= '<tr class="intake_tbody" id="intake_bodyrow_' . $key . '">';

		foreach ($value as $qid => $field) {
			$table .= '<td class="intake_tbody_' . ($intake_type == 'update' ? ($qid - 96) : $qid) . '" id="intake_tbody_' . ($intake_type == 'update' ? ($qid - 96) : $qid) . '_' . $key . '">' . $field . '</td>';
		}
		$table .= '</tr>';
	}
	$table .= '</tbody></table>';
	if ($intake_page_no == 7) {
		echo $table;
	}
	?>
</div>
</div>
</div>


<div class="box-footer">
<?php
if ($intake_page_no > 1) {
		$prev_page_no = ($intake_page_no - 1);

		$client_id = $GLOBALS["loguser"]["id"];

		$tmpr = $this->default_model->get_arrby_tbl_single('intake_answer_result', '*', "client_id='" . $client_id . "' and intake_question_id='" . ($q + 5) . "'", '1');
		if ($tmpr['intake_answer_id'] == ($a + 2)) {$prev_page_no = 1;}

		?>
<a href="<?php echo base_url('account/' . $iform . '?intake_page_no=' . $prev_page_no) ?>" class="btn btn-primary pull-left">&laquo; Previous</a>
<?php	}?>
<?php	if ($intake_page_no == 8) {?>
<input type="submit" class="btn btn-primary pull-right" name="Submit_intake_answer" value="Submit" onClick="return intak_submit_<?php	echo $intake_page_no; ?>()" />
<?php	} else {?>
<input type="submit" class="btn btn-primary pull-right" name="Submit_intake_answer" value="Next &raquo;" onClick="return intak_submit_<?php	echo $intake_page_no; ?>()" />
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
<script type="text/javascript">

<?php

if ($intake_page_no == 4) {
	echo 'check_question_11(' . $intake_ans_11 . ');';
}

if ($intake_page_no == 5) {
	echo 'check_question_21(' . $intake_ans_21 . ');';
	?>

	var qval_23 = $("input:radio.radio_group_23:checked").val();
	var qval_24 = $("input:radio.radio_group_24:checked").val();
	// var qval_25 = $("input:radio.radio_group_25:checked").val();

	$("#intake_form_group_24 input[type=text]").removeAttr("required");
	$("#intake_form_group_24, #intake_form_required_24").hide('100');

	$("#intake_form_group_25 input[type=text]").removeAttr("required");
	$("#intake_form_group_25, #intake_form_required_25").hide('100');

	$("#intake_form_group_26 input[type=checkbox]").removeAttr("required");
	$("#intake_form_group_26, #intake_form_required_26").hide('100');


	if(qval_23 == '<?=$a + 25?>')
	{
		$("#intake_form_group_24 input[type=text]").attr("required","required");
		$("#intake_form_group_24, #intake_form_required_24").show('100');

		if(qval_24 == '<?=$a + 27?>')
		{
			$("#intake_form_group_25 input[type=text]").attr("required","required");
			$("#intake_form_group_25, #intake_form_required_25").show('100');

			/*if(qval_25 == '<?=$a + 28?>')
			{
			*/
				//$("#intake_form_group_26 input[type=checkbox]").attr("required","required");
				$("#intake_form_group_26, #intake_form_required_26").show('100');
			// }
		}
	}



<?php

}

if ($intake_page_no == 6) {
	echo 'check_question_28(' . $intake_ans_28 . ');';
}

if ($intake_page_no == 7) {
	echo 'check_question_34(' . $intake_ans_34 . ');';
	//echo 'check_question_43('.$intake_ans_43.');';
}

if ($intake_page_no == 8) {
	$ans34R = $this->default_model->get_arrby_tbl_single('intake_comment_result', '*', "client_id='" . $GLOBALS["loguser"]["id"] . "' and intake_question_id='" . ($q + 34) . "'", '1');
	if ($ans34R['intake_comment_body'] == "0") {
		//echo 'check_question_34_45('.$ans34R['intake_comment_body'].');';
	}
}
?>

function check_question_34_45(val)
{
	if(val == 0)
	{
		$("#intake_form_group_45 input[type=text]").removeAttr("required");
		$("#intake_form_group_45, #intake_form_required_45").hide('100');
	}
}

<?php
if ($intake_page_no == 2) {
	?>
$('form').submit(function(){

	$('[name=Submit_intake_answer]').hide();

})
<?php
}
?>
function intak_submit_5()
{
	var qval_23 = $("input:radio.radio_group_23:checked").val();
	var qval_24 = $("input:radio.radio_group_24:checked").val();
	// var qval_25 = $("input:radio.radio_group_25:checked").val();
	 // && qval_25 == '<?=$a + 28?>'
	if(qval_23 == '<?=$a + 25?>' && qval_24 == '<?=$a + 27?>')
	{
		if($('.radio_group_26:checked').length>1) {	return true;	} else {	alert("Please select\nWhich of the following services does your employer provide? Check all that apply.");	return false;	}

	}
}
</script>
</body>
</html>
