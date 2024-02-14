<?php	defined('BASEPATH') OR exit('No direct script access allowed');	?>
<?php
error_reporting(0);
@extract($_POST);
?>
<?php
include_once 'ckeditor/ckeditor.php';
require_once 'ckeditor/ckfinder/ckfinder.php';
$ckeditor = new CKEditor( ) ;
$ckeditor->basePath	= base_url('ckeditor/');
CKFinder::SetupCKEditor( $ckeditor, ('ckeditor/ckfinder/'));
?>
<!DOCTYPE html>
<html>
<head>
<?php	$this->load->view("Admin/inc/head");	?>
<link rel="stylesheet" href="<?php echo base_url('assets/crm/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css'); ?>">
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php	$this->load->view("Admin/inc/header");	?>
<?php	$this->load->view("Admin/inc/leftnav");	?>


<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><?php echo $data["name"]; ?></h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo base_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?php echo base_url('admin/pages'); ?>">Manage Pages</a></li>
        <li class="active"><?php echo $data["name"]; ?></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">

<?php
if($this->uri->segment(4) > 0) 
{
$res = $this->default_model->get_arrby_tbl('pages','*',"id='".$this->uri->segment(4)."'",'1');
$res = $res["0"];
@extract($res);
}
?>

      <div class="row">
        <!-- /.col -->
        <div class="col-md-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href=""><i class="fa fa-plus"></i> <?php echo $data["name"]; ?></a></li>
              <li><a href="<?php echo base_url("admin/pages"); ?>"><i class="fa fa-laptop"></i> Manage Pages</a></li>
            </ul>
            <div class="tab-content">
              
              <div class="active tab-pane" id="settings">
<?php	$this->load->view("template/alert.php");	?>

<form action="" method="post" class="form-horizontal" enctype="multipart/form-data">

<div class="form-group">
    <label for="inputName" class="col-sm-2 control-label">Name</label>
    <div class="col-sm-10"><?php	echo form_input(['type'=>'text','name'=>'name','value'=>$name,'class'=>'form-control','required'=>'required']);	?></div>
</div>

<div class="form-group">
    <label for="inputName" class="col-sm-2 control-label">Page URL</label>
    <div class="col-sm-10"><?php	echo form_input(['type'=>'text','name'=>'url','value'=>$url,'class'=>'form-control','required'=>'required']);	?></div>
</div>


<div class="form-group"><label for="inputName" class="col-sm-2 control-label">Page Descriptions</label>
<div class="col-sm-10"><?php	$ckeditor->editor('details',stripcslashes($details));	?></div></div>


<!--<div class="form-group">
    <label for="inputName" class="col-sm-2 control-label">Meta Title</label>
    <div class="col-sm-10"><textarea name="seo_title" class="form-control"><?php $seo_title?></textarea></div>
</div>

<div class="form-group">
    <label for="inputName" class="col-sm-2 control-label">Meta Keywords</label>
    <div class="col-sm-10"><textarea name="seo_keywords" class="form-control"><?php $seo_keywords?></textarea></div>
</div>

<div class="form-group">
    <label for="inputName" class="col-sm-2 control-label">Meta Description</label>
    <div class="col-sm-10"><textarea name="seo_description" class="form-control"><?php $seo_description?></textarea></div>
</div>-->

                  
                  <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                      <button type="submit" name="Submit_" class="btn btn-success">Submit</button>
                    </div>
                  </div>
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

<?php	$this->load->view("Admin/inc/footer");	?>

</div>
<?php	$this->load->view("Admin/inc/template_js.php");	?>

<script src="<?php echo base_url('assets/crm/plugins/jQuery/jquery-2.2.3.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/crm/bootstrap/js/bootstrap.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/crm/plugins/fastclick/fastclick.js'); ?>"></script>
<script src="<?php echo base_url('assets/crm/dist/js/app.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/crm/dist/js/demo.js'); ?>"></script>
<script src="https://cdn.ckeditor.com/4.5.7/standard/ckeditor.js"></script>
<script src="<?php echo base_url('assets/crm/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js'); ?>"></script>
<script>
  $(function () {
    // Replace the <textarea id="editor1"> with a CKEditor
    // instance, using default configuration.
    //CKEDITOR.replace('details');
    //bootstrap WYSIHTML5 - text editor
    //$(".textarea").wysihtml5();
  });
</script>
</body>
</html>
