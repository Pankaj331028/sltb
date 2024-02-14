<?php	defined('BASEPATH') OR exit('No direct script access allowed');	?>
<?php
error_reporting(0);
@extract($_POST);

$sg_3 = $this->uri->segment(3);

if($this->uri->segment(4) > 0) 
{
	$cnd = "document_id='".$this->uri->segment(4)."'";
	if($GLOBALS["loguser"]["role"] == "Customer")  { $cnd .= " and client_id='".$GLOBALS["loguser"]["id"]."'"; }
	if($GLOBALS["loguser"]["role"] == "Company")  { $cnd .= " and company_id ='".$GLOBALS["loguser"]["id"]."'"; }
	if($GLOBALS["loguser"]["role"] == "Company User")  { $cnd .= " and added_by ='".$GLOBALS["loguser"]["id"]."'"; }

	$docR = $this->default_model->get_arrby_tbl('client_documents','*', $cnd, '1');
	$docR = $docR["0"];
	if(!isset($docR['document_id'])) {	redirect(base_url("account/document"));	exit;	}
	@extract($docR);
}
?>
<!DOCTYPE html>
<html>
<head>
<?php	$this->load->view("account/inc/head");	?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php	$this->load->view("account/inc/header");	?>
<?php	$this->load->view("account/inc/leftnav");	?>


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
<li class="active"><a href="<?php echo base_url("account/document/view/".$this->uri->segment(4))?>"><i class="fa fa-eye"></i> View Document</a></li>
<li><a href="<?php echo base_url("account/document"); ?>"><i class="fa fa-file-pdf-o"></i> Manage Document</a></li>
            </ul>
            <div class="tab-content">
              
              <div class="active tab-pane" id="settings">
<?php	$this->load->view("template/alert.php");	?>

<table class="table table-bordered">
<tr>	<th width="120">Document Title</th>	<td><?php $document_name?></td>	</tr>
<tr>	<th>Document File</th>	<td>
<?php	if($docR['added_by'] == $GLOBALS["loguser"]["id"]) {	?>
<form action="" method="post" enctype="multipart/form-data">
<button type="submit" name="submit_self_download" class="btn btn-primary"><i class="fa fa-download"></i> Download Now</button>
</form>
<?php	} else if(trim($downloaded_date)!="") {	?><span style="color:#0066CC;">Already Downloaded</span><?php	} else {	?>

<form action="" method="post" enctype="multipart/form-data">
<button type="submit" name="submit_custom_download" class="btn btn-primary"><i class="fa fa-download"></i> Download Now</button>
</form>

<?php	}	?>
</td>	</tr>
<tr>	<th>Upload on</th>	<td><?php echo date('m/d/Y',strtotime($uploaded_date)); ?></td>	</tr>
<tr>	<th>Download on</th>	<td><?php	if(trim($downloaded_date)!="") { echo date('m/d/Y',strtotime($downloaded_date)); } else {	echo '<span style="color:red;">Not Download</span>';	}	?></td>	</tr>
</table>





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

<?php	$this->load->view("account/inc/footer");	?>

</div>
<?php	$this->load->view("account/inc/template_js.php");	?>
</body>
</html>
