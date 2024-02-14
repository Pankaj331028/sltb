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
if (isset($client_data['intake_client_status']['status'])) {if ($client_data['intake_client_status']['status'] == "Complete") {base_url('account/intake/recertification');}}

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
foreach ($intake_question_data as $row) {
		$intake_question_id = $row['intake_question_id'];
		$question_required = $row['question_required'];
		$ansR = $this->crm_model->admin_intake_answer_by_client($GLOBALS["loguser"]["id"], $intake_question_id);

		$onclick = '';
		$disp_none = '';
		$required_val = '';
		if ($intake_question_id == '151') {
			$intake_ans_9 = $ansR['intake_answer_id'];
			$onclick = 'onChange="check_question_9(this.value)"';
		}

###########

		if ($row['intake_question_type'] == 'Comment') {
			$intake_comment_body = $ansR['intake_comment_body'];
			?>
<div class="form-group <?php echo $disp_none; ?>" id="intake_form_group_<?php echo $intake_question_id; ?>">
<label for="<?php echo $intake_question_id; ?>"><?php echo $row['intake_question_body'];if ($question_required == 'Yes') {echo ' <span class="' . $disp_none . '" id="intake_form_required_' . $intake_question_id . '">*</span>';} ?></label>
<?php
$name = "intake_comment_result[" . $ansR['intake_comment_id'] . "]";
			$input_id = "intake_form_input_" . $intake_question_id;
			if ($intake_question_id == 152) {$input_type = 'email';} elseif ($intake_question_id == 146) {$input_type = 'date';} else { $input_type = 'text';}
			$input_data = ['type' => $input_type, 'class' => 'form-control', 'name' => $name, 'value' => $intake_comment_body, 'id' => $input_id];
			if ($question_required == 'Yes' && $required_val == "") {$input_data['required'] = "required";}
			echo form_input($input_data);
			if ($intake_question_id == 145) {echo '<span>(Format as ###-##-####)</span>';}
			?>
</div>
<?php
} elseif ($row['intake_question_type'] == 'Radio' || $row['intake_question_type'] == 'Radio Group') {
			$radiogroups = $this->default_model->get_arrby_tbl('intake_answer', '*', "intake_question_id='" . $intake_question_id . "'", '500');
			?>
<div class="form-group <?php echo $disp_none; ?>" id="intake_form_group_<?php echo $intake_question_id; ?>">
<label for="<?php echo $intake_question_id; ?>"><?php echo $row['intake_question_body'];if ($question_required == 'Yes' || $question_required == 'No') {echo ' <span class="' . $disp_none . '" id="intake_form_required_' . $intake_question_id . '">*</span>';} ?></label>
<div>
<?php	foreach ($radiogroups as $radiogroup): ?>
<label class="radio-inline"><input type="radio"  name="intake_answer_result[<?php echo $ansR['intake_result_id']; ?>]" value="<?php echo $radiogroup['intake_answer_id']; ?>" <?php	if ($radiogroup['intake_answer_id'] == $ansR['intake_answer_id']) {echo " checked ";}
			echo $onclick;?> class="radio_group_<?php echo $intake_question_id; ?>" <?php if ($question_required == 'Yes') {echo 'required="required"';}?> /> <?php echo $radiogroup['intake_answer_body']; ?></label> &nbsp;
<?php endforeach;?>
</div>
</div>
<?php
}
	}
	?>
</div>


<div class="box-footer">
<?php
if ($intake_page_no > 1) {
		$prev_page_no = ($intake_page_no - 1);
		?>
<a href="<?php echo base_url('account/' . $iform . '?intake_page_no=' . $prev_page_no) ?>" class="btn btn-primary pull-left">&laquo; Previous</a>
<?php	}?>
<input type="submit" class="btn btn-primary pull-right" name="Submit_intake_answer" value="Next &raquo;" onClick="return intak_submit_<?php	echo $intake_page_no; ?>()" />
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
<script type="text/ecmascript">
function check_question_9(val)
{
	if(val == '136')
	{
		$("#intake_form_group_152 input[type=email]").attr("required","required");
		$("#intake_form_group_152, #intake_form_required_152").show('100');
	}
	else
	{
		$("#intake_form_group_152 input[type=email]").removeAttr("required");
		$("#intake_form_group_152, #intake_form_required_152").hide('100');
	}
}
check_question_9('<?php	echo $intake_ans_9; ?>');
</script>
</body>
</html>
