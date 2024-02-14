<?php	defined('BASEPATH') OR exit('No direct script access allowed');	?>
<?php
error_reporting(0);
@extract($_POST);
?>
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
      <h1><?php echo $data["name"]; ?></h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo base_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?php echo base_url('admin/company'); ?>">Manage Company Admin</a></li>
        <li class="active"><?php echo $data["name"]; ?></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">

<?php
if($this->uri->segment(4) > 0) 
{
$user = $this->default_model->get_arrby_tbl('users','*',"role='Company' and id='".$this->uri->segment(4)."'",'1');
$user = $user["0"];
@extract($user);
}
?>

      <div class="row">
        <!-- /.col -->
        <div class="col-md-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href=""><i class="fa fa-user-plus"></i> <?php echo $data["name"]; ?></a></li>
              <li><a href="<?php echo base_url("admin/company"); ?>"><i class="fa fa-users"></i> Manage Company Admin</a></li>
            </ul>
            <div class="tab-content">
              
              <div class="active tab-pane" id="settings">
<?php	$this->load->view("template/alert.php");	?>

<div class="row">
<div class="col-md-12">
<form action="" method="post" enctype="multipart/form-data">
<input type="hidden" name="added_by" value="<?php echo $GLOBALS["loguser"]["id"]?>" />

<div class="row">

<div class="form-group col-md-4">
    <label for="inputName" class="control-label">Status</label>
    <div>
	<select name="status" class="form-control">
    	<option value="Active">Active</option>
        <option value="Inactive" <?php if($status == 'Inactive') { ?> selected<?php } ?>>Inactive</option>
    </select>
</div>
</div>


<div class="form-group col-md-4">
    <label for="inputName" class="control-label">Name *</label>
    <div><?php	echo form_input(['type'=>'text','name'=>'name','value'=>$name,'class'=>'form-control', 'required'=>'required']);	?></div>
</div>

<div class="form-group col-md-4">
    <label for="inputName" class="control-label">Mobile No *</label>
    <div><?php	echo form_input(['type'=>'number','name'=>'phone','value'=>$phone,'class'=>'form-control', 'required'=>'required']);	?></div>
</div>

<div class="form-group col-md-4">
    <label for="inputName" class="control-label">Email *</label>
    <div><?php	echo form_input(['type'=>'mail','name'=>'email','value'=>$email,'class'=>'form-control', 'required'=>'required']);	?></div>
</div>

<div class="form-group col-md-4">
    <label for="inputName" class="control-label">Case Manager Name </label>
    <div><?php	echo form_input(['type'=>'text','name'=>'case_manager_name','value'=>$case_manager_name,'class'=>'form-control']);	?></div>
</div>

<div class="form-group col-md-4">
    <label for="inputName" class="control-label">Case Manager Phone </label>
    <div><?php	echo form_input(['type'=>'text','name'=>'case_manager_phone','value'=>$case_manager_phone,'class'=>'form-control']);	?></div>
</div>

<div class="form-group col-md-4">
    <label for="inputName" class="control-label">Case Manager Email </label>
    <div><?php	echo form_input(['type'=>'mail','name'=>'case_manager_email','value'=>$case_manager_email,'class'=>'form-control']);	?></div>
</div>

<div class="form-group col-md-4">
    <label for="inputName" class="control-label">Password</label>
    <div><?php	echo form_input(['type'=>'text','name'=>'psd','value'=>"",'class'=>'form-control']);	?></div>
</div>

<div class="form-group col-md-4">
    <label for="inputName" class="control-label">State *</label>
    <div><?php	echo form_input(['type'=>'text','name'=>'state','value'=>trim($state),'class'=>'form-control', 'required'=>'required']);	?></div>
</div>

<div class="form-group col-md-4">
    <label for="inputName" class="control-label">City *</label>
    <div><?php	echo form_input(['type'=>'text','name'=>'city','value'=>trim($city),'class'=>'form-control', 'required'=>'required']);	?></div>
</div>

<div class="form-group col-md-4">
    <label for="inputName" class="control-label">Zip Code *</label>
    <div><?php	echo form_input(['type'=>'text','name'=>'zip_code','value'=>trim($zip_code),'class'=>'form-control', 'required'=>'required']);	?></div>
</div>

<div class="form-group col-md-12">
    <label for="inputName" class="control-label">Complete Address *</label>
    <div><?php	echo form_textarea(['name'=>'address','value'=>trim($address),'class'=>'form-control', 'rows'=>'2', 'required'=>'required']);	?></div>
</div>


<div class="form-group col-md-4">
    <label for="inputName" class="control-label">Profile Image</label>
    <div><?php	echo form_input(['type'=>'file','name'=>'profile_img','class'=>'form-control']);	?>
    <a class="btn-link">(Width:150px; Height:150px;)</a>
    <?php	if($user['image']!='' && $user['image']!=' ') {	?><br /><img src="<?php echo base_url($user['image'])?>" width="80" alt="<?php echo $user['name']; ?>" /><?php	}	?>
    </div>
</div>

<div class="form-group col-md-4">
    <label for="inputName" class="control-label">Company Logo</label>
    <div><?php	echo form_input(['type'=>'file','name'=>'logo_img','class'=>'form-control']);	?>
    <a class="btn-link">(Max Width:500px; Max Height:500px;)</a>
    <?php	if($user['logo']!='' && $user['logo']!=' ') {	?><br /><img src="<?php echo base_url($user['logo']); ?>" width="80" alt="Logo" /><?php	}	?>
    </div>
</div>

</div>

<div class="panel panel-default">
  <div class="panel-heading"><strong>SMTP Details</strong></div>
  <div class="panel-body">
<div class="row">

<div class="form-group col-md-4">
    <label for="inputName" class="control-label">SMTP Hostname *</label>
    <div><?php	echo form_input(['type'=>'text','name'=>'smtp_hostname','value'=>trim($smtp_hostname),'class'=>'form-control', 'required'=>'required']);	?></div>
</div>

<div class="form-group col-md-4">
    <label for="inputName" class="control-label">Outgoing SMTP Port *</label>
    <div><?php	echo form_input(['type'=>'text','name'=>'smtp_outgoing_port','value'=>trim($smtp_outgoing_port),'class'=>'form-control', 'required'=>'required']);	?></div>
</div>

<div class="form-group col-md-4">
    <label for="inputName" class="control-label">SMTP Security *</label>
    <div><?php	echo form_dropdown('smtp_security', ['None', 'SSL', 'TLS'], trim($smtp_security), ['class'=>'form-control', 'required'=>'required']);	?></div>
</div>

<div class="form-group col-md-4">
    <label for="inputName" class="control-label">SMTP From Email *</label>
    <div><?php	echo form_input(['type'=>'email','name'=>'smtp_from_email','value'=>trim($smtp_from_email),'class'=>'form-control', 'required'=>'required']);	?></div>
</div>

<div class="form-group col-md-4">
    <label for="inputName" class="control-label">SMTP Email Password *</label>
    <div><?php	echo form_input(['type'=>'text','name'=>'smtp_email_password','value'=>trim($smtp_email_password),'class'=>'form-control', 'required'=>'required']);	?></div>
</div>





</div>
  </div>
</div>

<div class="form-group"><button type="submit" name="Submit_" class="btn btn-success">Submit</button></div>
</form>
</div>



</div>
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
