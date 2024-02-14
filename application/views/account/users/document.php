<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php
error_reporting(0);
@extract($_POST);

$sg_3 = $this->uri->segment(3);

if ($this->uri->segment(4) > 0) {
	if ($GLOBALS["loguser"]["role"] == "Company") {$cndvar = "company_id";} else { $cndvar = "company_id";}
// if($GLOBALS["loguser"]["role"] == "Company")  { $cndvar = "company_id"; } else {  $cndvar = "parent_id";  }
	$user = $this->default_model->get_arrby_tbl('users', '*', "role='Customer' and $cndvar='" . $GLOBALS["loguser"]["company_id"] . "' and id='" . $this->uri->segment(4) . "'", '1');
	$user = $user["0"];
	if (!isset($user['id'])) {redirect(base_url("account/customer"));exit;}
}
?>
<!DOCTYPE html>
<html>
<head>
<?php	$this->load->view("account/inc/head");?>
</head>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
<?php	$this->load->view("Site/inc/header");?>


<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="row">
        <div class="col-md-6">
          <h1><strong><?php if ($this->uri->segment(5) == "view") {echo "View Document";} else {echo $data["name"];}?></strong></h1>
          <p><strong>Client:</strong> <?php echo $user['name']; ?> <?php echo $user['lname']; ?> (#<?php echo $user['id']; ?>)</p>
        </div>
        <div class="col-md-6 text-right">
          <a href="javascript:;" onclick="deleteCustomer(<?=$user['id']?>)" title="Delete" class="btn btn-sm btn-danger" style="margin-top: 20px;">Delete Client</a>
        </div>
      </div>
    </section>

    <!-- Main content -->
    <section class="content">

      <div class="row">
        <!-- /.col -->
        <div class="col-md-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
<?php if ($this->uri->segment(4) > 0) {?>
<li><a href="<?php echo base_url("account/customer/edit/" . $this->uri->segment(4)) ?>"><i class="fa fa-pencil"></i> Edit</a></li>
<li><a href="<?php echo base_url("account/customer/view/" . $this->uri->segment(4)) ?>"><i class="fa fa-eye"></i> View Client</a></li>
<li><a href="<?php echo base_url("account/customer/add_program/" . $this->uri->segment(4)) ?>"><i class="fa fa-file-text-o"></i> Programs</a></li>
<!--<li><a href="<?php echo base_url("account/customer/status/" . $this->uri->segment(4)) ?>"><i class="fa fa-file-text-o"></i> View Client Status</a></li>-->
<li class="active"><a href="<?php echo base_url("account/customer/document/" . $this->uri->segment(4)) ?>"><i class="fa fa-upload"></i> Documents</a></li>
<!--<li><a href="<?php echo base_url("account/customer/report/" . $this->uri->segment(4)) ?>"><i class="fa fa-line-chart"></i> View Reminder Reports</a></li>-->
<?php	} else {?>
              <li class="active"><a href=""><i class="fa fa-user-plus"></i> <?php echo $data["name"]; ?></a></li>
              <li><a href="<?php echo base_url("account/customer"); ?>"><i class="fa fa-users"></i> Clients</a></li>
<?php	}?>
            </ul>
            <div class="tab-content">

              <div class="active tab-pane" id="settings">
<?php	$this->load->view("template/alert.php");?>
<?php
if ($this->uri->segment(5) == "view") {
	$cnd = "document_id='" . $this->uri->segment(6) . "'";
	if ($GLOBALS["loguser"]["role"] == "Customer") {$cnd .= " and client_id='" . $GLOBALS["loguser"]["id"] . "'";}
	if ($GLOBALS["loguser"]["role"] == "Company") {$cnd .= " and company_id ='" . $GLOBALS["loguser"]["id"] . "'";}
	if ($GLOBALS["loguser"]["role"] == "Company User") {$cnd .= " and added_by ='" . $GLOBALS["loguser"]["id"] . "'";}

	$docR = $this->default_model->get_arrby_tbl('client_documents', '*', $cnd, '1');
	$docR = $docR["0"];
	if (!isset($docR['document_id'])) {redirect(base_url("account/customer/document/" . $this->uri->segment(4)));exit;}
	@extract($docR);
	?>
<table class="table table-bordered">
<tr>	<th colspan="2"><a href="<?php echo base_url("account/customer/document/" . $this->uri->segment(4)) ?>"><i class="fa fa-long-arrow-left"></i> <strong>Back to Document</strong></a></th>	</tr>
<tr>	<th width="120">Document Title</th>	<td><?php echo $document_name; ?></td>	</tr>

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

		$file_name = $user['lname'] . " " . $user['name'] . " " . str_replace("Intake", "", $document_name) . " " . date('Y-m-d', strtotime($uploaded_date)) . "-Internal.pdf";

		?>

<tr>	<th>View File</th>	<td>
<a href="<?php echo base_url("account/intake_form_document/" . $this->uri->segment(4) . "/" . $document_id . "/" . $file_name); ?>" target="_blank" class="btn btn-primary"><i class="fa fa-pdf-o"></i> View <?php echo $document_name; ?> Form</button>
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

	$q = $this->db->query("SELECT id,name,lname FROM users where company_id='" . $logid . "' or parent_id='" . $logid . "' order by id desc");
	foreach ($q->result() as $r) {$arr_users[$r->id] = $r->name . " " . $r->lname;}

	$cnd = " (added_by='$logid' or company_id='$logid' or client_manager='$logid') and client_id='" . $this->uri->segment(4) . "'";
	$query = $this->db->query("SELECT * FROM client_documents where $cnd order by document_id  desc limit 10000");
	foreach ($query->result() as $row) {
		?>
<tr id="dtbl_<?php echo $row->id; ?>">
	<!--<td><?php echo ++$sno; ?></td>-->
    <td>
		<?php echo $row->document_name; ?><br />
        <a href="<?php echo base_url('account/customer/document/' . $this->uri->segment(4) . '/view/' . $row->document_id) ?>" title="View Document" class="btn btn-sm btn-primary"><i class="fa fa-link" aria-hidden="true"></i> View</a>
        <?php	if ($row->downloaded_date == "" && $row->added_by == $logid && $row->intake_client_status_id == "0") {?> &nbsp; <a title="Delete Document" href="<?php echo base_url('account/customer/document/' . $this->uri->segment(4) . '/delete/' . $row->document_id) ?>" class="btn btn-sm btn-danger" onClick="return confirm('Are you sure?')"><i class="fa fa-trash" aria-hidden="true"></i> Delete</a><?php	}?>
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
<input type="hidden" name="client_id" value="<?php echo $this->uri->segment(4) ?>" />

<div class="row">

<div class="form-group col-md-6">
    <label for="inputName" class="control-label">Document Name *</label>
    <div><?php	echo form_input(['type' => 'text', 'name' => 'document_name', 'value' => trim($document_name), 'class' => 'form-control', 'required' => 'required']); ?></div>
</div>

<div class="form-group col-md-6">
    <label for="inputName" class="control-label">Document File *</label>
    <div><input type="file" name="file_client_document" class="form-control" accept="image/*, application/pdf, application/vnd.ms-excel, .csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/msword, application/vnd.ms-powerpoint, text/plain, .doc, .docx, .xls, .xlsx, .ppt, .pptx" required="required">

    <?php	if (isset($user['client_document'])) {
		if (file_exists($user['client_document'])) {{?><br /><a href="<?php echo base_url($user['client_document']) ?>" target="_blank">View Document</a><?php	}}
	}
	?>
    </div>
</div>

</div>

<div class="form-group"><button type="submit" name="Submit_" class="btn btn-primary btn-block">Submit</button></div>

<p></p>

</form>

  </div>
</div>


</div>

</div>
<?php	}?>
              </div>
              <!-- /.tab-pane -->
            </div>
            <!-- /.tab-content -->
          </div>
          <!-- /.nav-tabs-custom -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->

    </section>
    <!-- /.content -->
  </div>

<div id="deleteCustomer" class="modal fade" role="dialog">
  <div class="modal-dialog ">
    <div class="modal-content">
      <form id="closeDelete">
        <div class="modal-body text-center">
          <h4>Are you sure you want to permanently delete this client form the system? You will not be able to recover this client once deleted.</h4>
        </div>
        <input type="hidden" name="customer_id" value="">
        <div class="modal-footer">
          <a id="deleteYes" href="javascript:;" class="btn btn-primary">Yes</a>
          <button type="submit" class="btn btn-danger" data-dismiss="modal">No</button>
        </div>
      </form>
    </div>
    <!-- base_url($sg_1 . "/customer_intake_form/" . $client_id . "/" . $tif['id']) -->
  </div>
</div>
<?php	$this->load->view("account/inc/footer");?>

</div>
<?php	$this->load->view("account/inc/template_js.php");?>

<script type="text/javascript">
  function deleteCustomer(id){

    $('#deleteYes').attr('href','<?php echo base_url('account/customer/delete/' . $user['id']) ?>');
    $('#closeDelete').focus();
    $('#closeDelete button[type=submit]').focus();
    $('#deleteCustomer').modal('show');
  }
</script>
</body>
</html>
