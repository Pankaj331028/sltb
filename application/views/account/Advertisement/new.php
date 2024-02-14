<?php	defined('BASEPATH') OR exit('No direct script access allowed');	?>
<?php
error_reporting(0);
@extract($_POST);

if($this->uri->segment(4) > 0) 
{

$role = $GLOBALS["loguser"]["role"];
if($role=="Company"){ $company_id=$GLOBALS["loguser"]["id"]; }elseif($role=="Company User"){ $company_id=$GLOBALS["loguser"]["company_id"]; } else { $company_id=""; }

$user = $this->default_model->get_arrby_tbl('users_advertisement','*',"company_id='".$company_id."' and id='".$this->uri->segment(4)."'",'1');
$user = $user["0"];
if(!isset($user['id'])) {	redirect(base_url("account/advertisement"));	exit;	}
@extract($user);
}
?>
<!DOCTYPE html>
<html>
<head>
<?php	$this->load->view("account/inc/head");	?>
</head>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
<?php	$this->load->view("Site/inc/header");	?>


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
          <div class="nav-tabs-custom">
           
            <div class="tab-content">
              
              <div class="active tab-pane" id="settings">
<p><a href="<?php echo base_url("account/advertisement"); ?>"><i class="fa fa-long-arrow-left"></i> <strong>Back to Advertisement</strong></a></p>

<?php	$this->load->view("template/alert.php");	?>

<form action="" method="post" enctype="multipart/form-data">
<div class="row">


<div class="form-group col-md-4">
    <label for="inputName" class="control-label">Name *</label>
    <div><?php	echo form_input(['type'=>'text','name'=>'name','value'=>$name,'class'=>'form-control', 'required'=>'required']);	?></div>
</div>

<div class="clr"></div>


</div>

<div class="form-group"><button type="submit" name="Submit_" class="btn btn-primary">Submit</button></div>
</form>
<div class="clr"></div>





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
