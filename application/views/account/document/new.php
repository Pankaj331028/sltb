<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php
error_reporting(0);
@extract($_POST);

if ($this->uri->segment(4) > 0) {
	$cnd = "document_id='" . $this->uri->segment(4) . "'";
	if ($GLOBALS["loguser"]["role"] == "Customer") {$cnd .= " and client_id='" . $GLOBALS["loguser"]["id"] . "'";}
	if ($GLOBALS["loguser"]["role"] == "Company") {$cnd .= " and company_id ='" . $GLOBALS["loguser"]["id"] . "'";}
	if ($GLOBALS["loguser"]["role"] == "Company User") {$cnd .= " and added_by ='" . $GLOBALS["loguser"]["id"] . "'";}

	$docR = $this->default_model->get_arrby_tbl('client_documents', '*', $cnd, '1');
	$docR = $docR["0"];
	if (!isset($docR['document_id'])) {redirect(base_url("account/document"));exit;}
	@extract($docR);
}
?>
<!DOCTYPE html>
<html>
<head>
<?php	$this->load->view("account/inc/head");?>
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
        <li><a href="<?php echo base_url('account/document'); ?>">Document</a></li>
        <li class="active"><?php echo $data["name"]; ?></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">

      <div class="row">
        <!-- /.col -->
        <div class="col-md-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
<?php if ($this->uri->segment(4) > 0) {?><li class="active"><a href=""><i class="fa fa-pencil"></i> <?php echo $data["name"]; ?></a></li><?php	} else {?><li class="active"><a href=""><i class="fa fa-upload"></i> <?php echo $data["name"]; ?></a></li><?php	}?>
<li><a href="<?php echo base_url("account/document"); ?>"><i class="fa fa-file-pdf-o"></i> Manage Document</a></li>

            </ul>
            <div class="tab-content">

              <div class="active tab-pane" id="settings">
<?php	$this->load->view("template/alert.php");?>

<form action="" method="post" enctype="multipart/form-data">

<div class="row">

<?php	if ($GLOBALS["loguser"]["role"] == "Company" || $GLOBALS["loguser"]["role"] == "Company User") {

	$cnd = "role='Customer'";
	if ($GLOBALS["loguser"]["role"] == "Company") {$cnd .= " and company_id='" . $GLOBALS["loguser"]["id"] . "'";}
	if ($GLOBALS["loguser"]["role"] == "Company User") {$cnd .= " and company_id='" . $GLOBALS["loguser"]["company_id"] . "'";}
// if($GLOBALS["loguser"]["role"] == "Company User") { $cnd .= " and parent_id='".$GLOBALS["loguser"]["id"]."'"; }

	$q = $this->db->query("SELECT * FROM users where $cnd order by name asc");
	foreach ($q->result() as $r) {$arr_customer[$r->id] = $r->name . " [" . $r->email . "]";}

	?>
<div class="form-group col-md-4">
    <label for="inputName" class="control-label">Select Client *</label>
    <div><?php	echo form_dropdown('client_id', $arr_customer, $client_id, ['name' => 'client_id', 'class' => 'form-control', 'required' => 'required']); ?></div>
</div>
<?php	}?>

<div class="form-group col-md-4">
    <label for="inputName" class="control-label">Document Name *</label>
    <div><?php	echo form_input(['type' => 'text', 'name' => 'document_name', 'value' => trim($document_name), 'class' => 'form-control', 'required' => 'required']); ?></div>
</div>

<div class="form-group col-md-4">
    <label for="inputName" class="control-label">Document File</label>
    <div><?php	echo form_input(['type' => 'file', 'name' => 'file_client_document', 'class' => 'form-control', 'required' => 'required', 'accept' => 'image/*, application/pdf,application/vnd.ms-excel, .csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/msword, application/vnd.ms-powerpoint, text/plain']); ?>
    <?php	if (isset($user['client_document'])) {
	if (file_exists($user['client_document'])) {{?><br /><a href="<?php echo base_url($user['client_document']) ?>" target="_blank">View Document</a><?php	}}
}
?>
    </div>
</div>

</div>

<div class="form-group"><button type="submit" name="Submit_" class="btn btn-success">Submit</button></div>
</form>


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

<?php	$this->load->view("account/inc/footer");?>

</div>
<?php	$this->load->view("account/inc/template_js.php");?>
</body>
</html>
