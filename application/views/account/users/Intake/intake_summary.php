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
    <h3 style="margin-top:0px;"><strong>Intake Summary for </strong> <?php echo $intake[2]['ans']['intake_comment_body'] . ", " . $intake[1]['ans']['intake_comment_body']; ?> (#<?php echo $user['id']; ?>)</h3>
    <p>
    	<i class="fa fa-phone"></i> <a href="tel:<?php echo $intake[3]['ans']['intake_comment_body']; ?>"><?php echo $intake[3]['ans']['intake_comment_body']; ?></a> &nbsp; &nbsp;
        <i class="fa fa-envelope"></i> <a href="mailto:<?php echo $user['email']; ?>"><?php echo $user['email']; ?></a> &nbsp; &nbsp;
    	<i class="fa fa-map-marker"></i> <?php echo $intake[4]['ans']['intake_comment_body']; ?>
    </p>
</div>

<div class="col-md-4">
<a href="<?php echo base_url("account/customer/current_analysis/" . $this->uri->segment(4)); ?>" class="btn btn-info hide_on_print pull-right"><i class="fa fa-long-arrow-left"></i> Back to Analysis</a>

</div>
</div>

      <?php	$this->load->view("template/alert.php");?>
    </section>

    <!-- Main content -->
    <section class="content">

<form action="" method="post" enctype="multipart/form-data" name="current_analysis_form" id="current_analysis_form">

<?php
foreach ($intake_data['intake_page'] as $page) {
	$intake_page_no = $page['intake_page_no'];
	?>
<div class="box box-primary box-solid">
<div class="box-header with-border">
  <h3 class="box-title"><?php echo $page['intake_page_title']; ?></h3>
  <div class="box-tools pull-right"><button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button></div>
</div>

<div class="box-body" style="display: block;">
<?php
foreach ($intake_data[$intake_page_no] as $k => $row) {
		$que = $row['que'];
		$ans = $row['ans'];
		?>
<div style=" padding:15px; background:#F8F8F8; margin-bottom:20px;">
	<p><strong style="color:#330000;"><?php	echo $k . ") " . $que['intake_question_body']; ?></strong></p>
    <div style="padding:0 0 0 15px;">
	<?php
if ($que['intake_question_type'] == "Comment") {
			echo '<p style="color:#0543cf;">' . $ans['intake_comment_body'] . '</p>';
		} else if ($que['intake_question_type'] == 'Checkbox') {

			$tmp_arr = array();
			$intake_answer_id_checkbox = $ans['intake_answer_id_checkbox'];
			foreach (explode(",", $intake_answer_id_checkbox) as $intake_answer_id) {
				if ($intake_answer_id != "0") {
					$q = $this->db->query("SELECT * FROM intake_answer where intake_answer_id='$intake_answer_id'");
					$ans = $q->row_array();
					$tmp_arr[] = $ans['intake_answer_body'];
				}
			}
			echo '<p style="color:#0543cf;">' . implode(", ", $tmp_arr) . '</p>';
			$tmp_arr = array();

		} else if ($que['intake_question_type'] == 'Radio' || $que['intake_question_type'] == 'Radio Group' || $que['intake_question_type'] == 'Checkbox') {
			$intake_answer_id = $ans['intake_answer_id'];
			$q = $this->db->query("SELECT * FROM intake_answer where intake_answer_id='$intake_answer_id'");
			$ans = $q->row_array();
			echo '<p style="color:#0543cf;">' . $ans['intake_answer_body'] . '</p>';
		} else if ($que['intake_question_type'] == "File") {
			$intake_file_location = $ans['intake_file_location'];
			$client_document = $this->crm_model->document_decrypt($intake[6]['ans']['intake_file_location']);
			if (file_exists($client_document)) {echo '<p><a href="' . base_url($client_document) . '" target="_blank">View File</a></p>';} else {echo '<p>No file found.</p>';}
		} else if ($que['intake_question_type'] == "Table") {

			echo "<ol>";
			foreach ($ans as $r) {if (trim($r['intake_comment_body']) != "") {echo '<li><span style="color:#0543cf;">' . $r['intake_comment_body'] . "</span></li>";}}
			echo "</ol>";
		}
		?>
    </div>
</div>
<div class="clr"></div>
<?php	}?>
</div>
<!-- /.box-body -->
</div>
<?php	}?>

</form>
    </section>
    <!-- /.content -->
</div>
</div>
</div>

<?php	$this->load->view("account/inc/template_js");?>


</body>
</html>
