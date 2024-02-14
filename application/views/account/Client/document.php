<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php
$user = $GLOBALS["loguser"];
if (!isset($document_name)) {$document_name = "";}
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
<li class="active"><a href="<?php echo base_url("customer/document"); ?>"><i class="fa fa-upload"></i> Documents</a></li>
</ul>
<div class="tab-content">
<div class="active tab-pane" id="settings">

<?php	$this->load->view("template/alert.php");?>
<?php	@extract($client_data['client']);?>
<?php
if ($this->uri->segment(3) == "view") {
	$cnd = "document_id='" . $this->uri->segment(4) . "'";
	$cnd .= " and client_id='" . $GLOBALS["loguser"]["id"] . "'";

	$docR = $this->default_model->get_arrby_tbl('client_documents', '*', $cnd, '1');
	$docR = $docR["0"];
	if (!isset($docR['document_id'])) {redirect(base_url("customer/customer/document/" . $this->uri->segment(4)));exit;}
	@extract($docR);
	?>
<table class="table table-bordered">
<tr>	<th colspan="2"><a href="<?php echo base_url("customer/document/"); ?>"><i class="fa fa-long-arrow-left"></i> <strong>Back to Document</strong></a></th>	</tr>
<tr>	<th width="120">Document Title</th>	<td><?php echo $docR['document_name'] ?></td>	</tr>
<?php if ($intake_client_status_id == "0") {?>
<tr>	<th>Document File</th>	<td>
<?php	if ($docR['added_by'] == $GLOBALS["loguser"]["id"]) {?>
<form action="" method="post" enctype="multipart/form-data">
<button type="submit" name="submit_self_download" class="btn btn-primary"><i class="fa fa-download"></i> Download Now</button>
</form>
<?php	//} else if(trim($downloaded_date)!="") {	?><!--<span style="color:#0066CC;">Already Downloaded</span>--><?php	} else {?>

<form action="" method="post" enctype="multipart/form-data">
<button type="submit" name="submit_custom_download" class="btn btn-primary"><i class="fa fa-download"></i> Download Now</button>
</form>

<?php	}?>
</td>	</tr>
<?php } else {

		$file_name = $user['lname'] . " " . $user['name'] . " " . str_replace("Intake", "", $document_name) . " " . date('Y-m-d', strtotime($uploaded_date)) . ".pdf";

		?>
<tr>	<th>View File</th>	<td>
<a href="<?php echo base_url("customer/intake_form_document/" . $GLOBALS["loguser"]["id"] . "/" . $document_id . "/" . $file_name); ?>" target="_blank" class="btn btn-primary"><i class="fa fa-pdf-o"></i> View <?php echo $document_name; ?> Form</button>
</td>	</tr>

<?php }?>
<tr>	<th>Upload on</th>	<td><?php echo date('m/d/Y', strtotime($uploaded_date)); ?></td>	</tr>
<tr>	<th>Download on</th>	<td><?php	if (trim($downloaded_date) != "") {echo date('m/d/Y', strtotime($downloaded_date));} else {echo '<span style="color:red;">Not Download</span>';}?></td>	</tr>
</table>
<?php
} else {
	?>
<div class="row">
<div class="col-md-8">

<div class="table-responsive">
<table id="show_datatable" class="table table-bordered">
        <thead>
        <tr class="info">
          <!--<th width="1%">SNO</th>-->
          <th>Document</th>
          <th>Upload by</th>
          <th>Upload Date</th>
          <th>Download Date</th>
        </tr>
        </thead>
        <tbody>
<?php

	$this->db->query("UPDATE client_documents set document_name='Consolidation Form' where document_name='Consolidation Intake'");
	$this->db->query("UPDATE client_documents set document_name='IDR Form' where document_name='IDR Intake'");

	$sno = 0;
	$logid = $GLOBALS["loguser"]["id"];

	$q = $this->db->query("SELECT id,name,lname FROM users where id='" . $GLOBALS["loguser"]["id"] . "' or id='" . $GLOBALS["loguser"]["company_id"] . "' or id='" . $GLOBALS["loguser"]["parent_id"] . "' order by id desc");
	foreach ($q->result() as $r) {$arr_users[$r->id] = $r->name . " " . $r->lname;}

	$cnd = " client_id='" . $GLOBALS["loguser"]["id"] . "'";
	$query = $this->db->query("SELECT * FROM client_documents where $cnd and status='Active' order by document_id  desc limit 10000");
	foreach ($query->result() as $row) {
		?>
<tr id="dtbl_<?php echo $row->document_id; ?>">
	<!--<td><?php echo ++$sno; ?></td>-->
    <td>
		<?php echo $row->document_name; ?><br />
        <a href="<?php echo base_url('customer/document/view/' . $row->document_id) ?>" title="View Document" class="btn btn-sm btn-primary"><i class="fa fa-link" aria-hidden="true"></i> View</a>
        <?php	if ($row->downloaded_date == "" && $row->added_by == $logid) {?> &nbsp; <a title="Delete Document" href="<?php echo base_url('customer/document/delete/' . $row->document_id) ?>" class="btn btn-sm btn-danger" onClick="return confirm('Are you sure?')"><i class="fa fa-trash" aria-hidden="true"></i> Delete</a><?php	}?>
    </td>
    <td><?php echo $arr_users[$row->added_by]; ?></td>
    <td><?php echo date('m/d/Y', strtotime($row->uploaded_date)); ?></td>
    <td><?php if ($row->downloaded_date != "") {echo date('m/d/Y', strtotime($row->downloaded_date));} else {echo '<span style="color:red;">Not Download</span>';}?></td>

</tr>
<?php	}?>
        </tbody>

      </table>
</div>
</div>

<div class="col-md-4">

<div class="panel panel-primary">
  <div class="panel-heading"><i class="fa fa-upload"></i> <strong>Upload Document</strong></div>
  <div class="panel-body">

<form action="" method="post" enctype="multipart/form-data">
<input type="hidden" name="client_id" value="<?php echo $GLOBALS["loguser"]["id"] ?>" />
<input type="hidden" name="company_id" value="<?php echo $client_data['company']['id']; ?>" />
<input type="hidden" name="client_manager" value="<?php echo $client_data['case_manager']['id']; ?>" />

<div class="row">

<div class="form-group col-md-12">
<p style="color:red; font-size:14px;"><strong>NOTE:</strong> DO NOT UPLOAD YOUR STUDENTAID.gov FILE HERE. YOU ONLY UPLOAD IT IN THE INTAKE QUESTIONAIRRE.</p>
    <label for="inputName" class="control-label">Document Name *</label>
    <div><?php	echo form_input(['type' => 'text', 'name' => 'document_name', 'value' => trim($document_name), 'class' => 'form-control', 'required' => 'required']); ?></div>
</div>

<div class="form-group col-md-12">
    <label for="inputName" class="control-label">Document File *</label>
    <div><input type="file" name="file_client_document" class="form-control" accept="image/*, application/pdf, application/vnd.ms-excel, .csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/msword, application/vnd.ms-powerpoint, text/plain, .doc, .docx, .xls, .xlsx, .ppt, .pptx" required="required">

    <span style="color:#999999; font-size:12px;">Maximum 100MB file size allowed</span>
	<?php	if (isset($user['client_document'])) {
		if (file_exists($user['client_document'])) {{?><br /><a href="<?php echo base_url($user['client_document']) ?>" target="_blank">View Document</a><?php	}}
	}
	?>

    </div>
</div>

<?php	if (!isset($user['client_document'])) {?>
<div class="form-group col-md-12">
<p style="color:#999999; font-size:12px;">If you are uploading more than 1 document at this time, check the box to combine your uploads into a single document for easier management</p>
<div class="checkbox"><label><input type="checkbox" name="add_to_previous" value="Yes" />Add to previous upload</label></div>

</div>



<?php	}?>

</div>
<div class="col-md-12">
<p style="color: red; font-size:12px;">Documents you upload will not be reviewed until you complete the Intake.</p>
</div>
<div class="form-group"><button type="submit" name="Submit_" class="btn btn-primary btn-block">Submit</button></div>
</form>

  </div>
</div>


</div>

</div>
<?php	}?>
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
