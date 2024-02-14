<?php	defined('BASEPATH') OR exit('No direct script access allowed');	?>
<?php	@extract($GLOBALS["settings"]);	?>
<!DOCTYPE html>
<html>
<head>
<?php	$this->load->view("Admin/inc/head");	?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php	$this->load->view("Admin/inc/header");	?>
<?php	$this->load->view("Admin/inc/leftnav");	?>


<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Profile</h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo base_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?php echo base_url('admin/settings'); ?>">Settings</a></li>
        <li class="active">Update</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">

      <div class="row">
        
        <div class="col-md-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="<?php echo base_url("admin/settings"); ?>"><i class="fa fa-cog"></i> Settings</a></li>
            </ul>
            <div class="tab-content">
              
              <div class="active tab-pane" id="settings">
<?php	$this->load->view("template/alert.php");	?>
<?php echo form_open(base_url("admin/settings"),array('class'=>'form-horizontal','enctype'=>'multipart/form-data')); ?>

<div class="form-group">
    <label for="inputName" class="col-sm-2 control-label">Wallet Transfer Charge</label>
    <div class="col-sm-10"><?php echo form_input(['type'=>'text','name'=>'transfer_charge','value'=>$transfer_charge,'class'=>'form-control','required'=>'required']);	?></div>
</div>

<div class="form-group">
    <label for="inputName" class="col-sm-2 control-label">Purchase Commission</label>
    <div class="col-sm-10"><?php echo form_input(['type'=>'text','name'=>'purchase_commission','value'=>$purchase_commission,'class'=>'form-control','required'=>'required']);	?></div>
</div>

<div class="form-group">
    <label for="inputName" class="col-sm-2 control-label">Referral Commission</label>
    <div class="col-sm-10"><?php	echo form_input(['type'=>'text','name'=>'referral_commission','value'=>$referral_commission,'class'=>'form-control', 'required'=>'required']);	?></div>
</div>

                  <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                      <button type="submit" name="Submit_" class="btn btn-success">Submit</button>
                    </div>
                  </div>
<?php echo form_close(); ?>
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
</body>
</html>
