<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php
error_reporting(0);
@extract($_POST);

if ($this->uri->segment(4) > 0) {
	if ($GLOBALS["loguser"]["role"] == "Company") {$cndvar = "company_id";} else { $cndvar = "company_id";}
// if($GLOBALS["loguser"]["role"] == "Company")  { $cndvar = "company_id"; } else {    $cndvar = "parent_id";  }
	$user = $this->default_model->get_arrby_tbl('users', '*', "role='Customer' and $cndvar='" . $GLOBALS["loguser"]["company_id"] . "' and id='" . $this->uri->segment(4) . "'", '1');
	$user = $user["0"];
	if (!isset($user['id'])) {redirect(base_url("account/customer"));exit;}
	@extract($user);
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
              <h1><strong><?php echo $data["name"]; ?></strong></h1>
              <?php if ($this->uri->segment(4) > 0) {?><p><strong>Client:</strong> <?php echo $user['name']; ?> <?php echo $user['lname']; ?> (#<?php echo $user['id']; ?>)</p><?php	}?>
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

<?php if ($this->uri->segment(4) > 0) {?>
 <ul class="nav nav-tabs">
<li class="active"><a href=""><i class="fa fa-pencil"></i> Edit</a></li>
<li><a href="<?php echo base_url("account/customer/view/" . $this->uri->segment(4)) ?>"><i class="fa fa-eye"></i> View Client</a></li>
<li><a href="<?php echo base_url("account/customer/add_program/" . $this->uri->segment(4)) ?>"><i class="fa fa-file-text-o"></i> Programs</a></li>
<!--<li><a href="<?php echo base_url("account/customer/status/" . $this->uri->segment(4)) ?>"><i class="fa fa-file-text-o"></i> View Client Status</a></li>-->
<li><a href="<?php echo base_url("account/customer/document/" . $this->uri->segment(4)) ?>"><i class="fa fa-upload"></i> Documents</a></li>
<!--<li><a href="<?php echo base_url("account/customer/report/" . $this->uri->segment(4)) ?>"><i class="fa fa-line-chart"></i> View Reminder Reports</a></li>-->
</ul>
<?php	}?>
            <div class="tab-content">

              <div class="active tab-pane" id="settings">
<p><a href="<?php echo base_url("account/customer"); ?>"><i class="fa fa-long-arrow-left"></i> <strong>Back to Manage Clients</strong></a></p>

<?php	$this->load->view("template/alert.php");?>

<form action="" method="post" enctype="multipart/form-data">
<input type="hidden" name="added_by" value="<?php echo $GLOBALS["loguser"]["id"] ?>" />
<?php	if ($GLOBALS["loguser"]["role"] == "Company") {?>
<input type="hidden" name="company_id" value="<?php echo $GLOBALS["loguser"]["id"] ?>" />
<?php	} else {?>
<input type="hidden" name="company_id" value="<?php echo $GLOBALS["loguser"]["parent_id"] ?>" />
<?php	}?>
<input type="hidden" name="status" value="Active" />

<div class="row">


<div class="form-group col-md-4">
    <label for="inputName" class="control-label">First Name *</label>
    <div><?php	echo form_input(['type' => 'text', 'name' => 'name', 'value' => $name, 'class' => 'form-control', 'required' => 'required']); ?></div>
</div>

<div class="form-group col-md-4">
    <label for="inputName" class="control-label">Last Name *</label>
    <div><?php	echo form_input(['type' => 'text', 'name' => 'lname', 'value' => $lname, 'class' => 'form-control', 'required' => 'required']); ?></div>
</div>
<div class="clr"></div>

<div class="form-group col-md-4">
    <label for="inputName" class="control-label">Phone Number *</label>
    <div><?php	echo form_input(['type' => 'text', 'name' => 'phone', 'value' => $phone, 'class' => 'form-control', 'required' => 'required']); ?></div>
</div>

<div class="form-group col-md-4">
    <label for="inputName" class="control-label">Email </label>
    <div><?php	echo form_input(['type' => 'mail', 'name' => 'email', 'value' => $email, 'class' => 'form-control', 'required' => 'required']); ?></div>
</div>
<div class="clr"></div>

<?php
if ($GLOBALS["loguser"]["role"] == "Company") {
	$cmq = $this->db->query("SELECT * FROM users where (role='Company' or role='Company User') and (id='" . $GLOBALS["loguser"]["id"] . "' or parent_id='" . $GLOBALS["loguser"]["id"] . "') order by name asc");
	$arr_cm[] = "Select Case Manager";
} else {
	$cmq = $this->db->query("SELECT * FROM users where role='Company User' and id='" . $GLOBALS["loguser"]["id"] . "' order by name asc");
}

foreach ($cmq->result() as $cmr) {$arr_cm[$cmr->id] = $cmr->name . " " . $cmr->lname;}

?>
<div class="form-group col-md-4">
    <label for="inputName" class="control-label">Case Manager </label>
    <div><?php	echo form_dropdown('parent_id', $arr_cm, $parent_id, ['class' => 'form-control', 'required' => 'required']); ?></div>
</div>



<?php	if ($this->uri->segment(4) == "") {?>
<div class="form-group col-md-4">
    <label for="inputName" class="control-label">Password </label>
    <div><?php	echo form_input(['type' => 'text', 'name' => 'psd', 'value' => "", 'class' => 'form-control']); ?></div>
</div>
<?php	}?>



<div class="form-group col-md-4">
    <label for="inputName" class="control-label">Recertification Date </label>
    <div><?php echo form_input(['type' => 'date', 'name' => 'recertification_date', 'value' => $recertification_date, 'class' => 'form-control']); ?></div>
    <small>
        <?php
if (!$recert_updated) {
	?>
                Pulled from NSLDS
                <?php
} else {
	?>
                Updated Manually
                <?php
}
?>
    </small>
</div>
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
