<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php
error_reporting(0);
@extract($_POST);

$sg_3 = $this->uri->segment(3);

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
      <h1><strong><?php echo $data["name"]; ?></strong></h1>
    </section>

    <!-- Main content -->
    <section class="content">

<?php
if ($this->uri->segment(4) > 0) {
	$user = $this->default_model->get_arrby_tbl('users', '*', "(role='Company User' or role='Company') and company_id='" . $GLOBALS["loguser"]["id"] . "' and id='" . $this->uri->segment(4) . "'", '1');
	$user = $user["0"];
	@extract($user);
}
?>
      <div class="row">
        <!-- /.col -->
        <div class="col-md-12">
<div class="nav-tabs-custom">
<ul class="nav nav-tabs">
<?php	if ($GLOBALS["loguser"]["role"] == "Company") {?>
<li><a href="<?php echo base_url("account/company"); ?>"><i class="fa fa-globe"></i> Company</a></li>
<li class="active"><a href="<?php echo base_url("account/team"); ?>"><i class="fa fa-user"></i> Users</a></li>
<li><a href="<?php echo base_url("account/emails"); ?>"><i class="fa fa-envelope-o"></i> SMTP Emails</a></li>
<li><a href="<?php echo base_url("account/billing"); ?>"><i class="fa fa-credit-card-alt"></i> Payment</a></li>
<li><a href="<?php echo base_url("integration"); ?>"><i class="fa fa-share"></i> Integrations</a></li>
<li><a href="<?php echo base_url("account/reminders"); ?>"><i class="fa fa-credit-card-alt"></i> Reminders</a></li>
<?php	}?>
<li><a href="<?php echo base_url("account/profile"); ?>"><i class="fa fa-pencil"></i> My Profile</a></li>
<li><a href="<?php echo base_url("account/cp"); ?>"><i class="fa fa-lock"></i> Change Password</a></li>

</ul>
            <div class="tab-content">

<div class="active tab-pane" id="settings">
<p><a href="<?php echo base_url("account/team"); ?>" class="btn-link">&laquo; Back to Users</a></p>

<?php	$this->load->view("template/alert.php");?>
<?php
$fields = $this->db->query("SELECT * from settings Limit 1")->row_array();
// $accountType = $this->db->query("SELECT * from account_payment_info where `company_id` = $GLOBALS['loguser']['id']")->row_array();
$accountType = $this->db->query("SELECT * from users_company where `id` = {$GLOBALS['loguser']['id']}")->row_array();
?>
<div class="row">
  <div class="form-group col-md-8">
    <?php if ($accountType['account_type'] == 1) {?>
    <span>Each additional user will add <strong>$<?php	echo $fields['additional_user_fee']; ?></strong> to your monthly subscription fee. There are no fees or limits to the number of clients you have under your current Subscription Account</span>
    <?php } else {?>
    <span>There is no fee to add this User to your Pay As You Go Account</span>
    <?php }?>
  </div>
</div>
<form action="" method="post" enctype="multipart/form-data">
<input type="hidden" name="added_by" value="<?php echo $GLOBALS["loguser"]["id"] ?>" />
<input type="hidden" name="parent_id" value="<?php echo $GLOBALS["loguser"]["id"] ?>" />
<input type="hidden" name="company_id" value="<?php echo $GLOBALS["loguser"]["id"] ?>" />
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

<div class="form-group col-md-4"><label>Position</label>
<select name="position" class="form-control" required>
<option value="">Select position</option>
<option value="Owner" <?php	if ($position == "Owner") {echo " selected";}?>>Owner</option>
<option value="Attorney" <?php	if ($position == "Attorney") {echo " selected";}?>>Attorney</option>
<option value="Paralegal" <?php	if ($position == "Paralegal") {echo " selected";}?>>Paralegal</option>
<option value="Administration" <?php	if ($position == "Administration") {echo " selected";}?>>Administration</option>
</select>
</div>

<div class="form-group col-md-4">
    <label for="inputName" class="control-label">Mobile No *</label>
    <div><?php	echo form_input(['type' => 'text', 'name' => 'phone', 'value' => $phone, 'class' => 'form-control', 'required' => 'required']); ?></div>
</div>
<div class="clr"></div>


<div class="form-group col-md-4">
    <label for="inputName" class="control-label">Email *</label>
    <div><?php	echo form_input(['type' => 'mail', 'name' => 'email', 'value' => $email, 'class' => 'form-control', 'required' => 'required']); ?></div>
</div>
<div class="form-group col-md-4">
  <label for="e">Toolbox Password</label>
  <div class="input-group">
    <?= //(!empty($psd) ? base64_decode($psd) : '')?>
    <input type="password" name="psd" class="form-control input-md" id="psd" value="">
    <span class="input-group-addon"><a href="javascript:;" id="psdShow1"><i class="fa fa-eye-slash"></i></a></span>
  </div>
    <span>(This is the password you will use to access this software)</span>
</div>
<div class="clr"></div>
<div class="form-group col-md-4">
  <label for="e">SMTP Email Password</label>
  <div class="input-group">
    <input type="password" name="email_password" class="form-control input-md" id="email_password" value="<?=(!empty($email_password) ? base64_decode($email_password) : '')?>">
    <span class="input-group-addon"><a href="javascript:;" id="pwdShow"><i class="fa fa-eye-slash"></i></a></span>
  </div>
  <?php //echo form_input(['type' => 'text', 'name' => 'email_password', 'value' => (!empty($user['email_password']) ? base64_decode($user['email_password']) : ''), 'class' => 'form-control', 'required' => 'required']); ?>
</div>

<div class="form-group col-md-4">
    <label for="inputName" class="control-label"> Calendar Link</label>
    <div><?php	echo form_input(['type' => 'text', 'name' => 'calendar_link', 'value' => "$calendar_link", 'class' => 'form-control', 'required' => 'required']); ?></div>
    <span>(Please enter a link to the online calendar your clients can use to book an analysis. If clients will never book with you enter No_Calender. This is required.)</span>
  </div>

<!--<div class="form-group col-md-4">
    <label for="inputName" class="control-label">Profile Image</label>
    <div><?php	echo form_input(['type' => 'file', 'name' => 'profile_img', 'class' => 'form-control']); ?>
    <a class="btn-link">(Width:150px; Height:150px;)</a>
    <?php	if ($user['image'] != '' && $user['image'] != ' ') {?><br /><img src="<?php echo base_url($user['image']) ?>" width="80" alt="<?php echo $user['name']; ?>" /><?php	}?>
    </div>
</div>-->

</div>

<div class="form-group">
  <button type="submit" name="Submit_" class="btn btn-primary">Submit</button>
  <!-- <a href="<?php	echo base_url('account/emails'); ?>" class="btn btn-default" >Test Email</a> -->
  <button type="submit" name="Submit_Test" class="btn btn-warning">Test Email</button>
</div>
</form>
<!-- base_url("account/testemails") -->
<form action="<?=base_url('account/emails')?>">
<!-- <button type="submit" name="Submit_Test" class="btn btn-warning">Test Email</button> -->
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

<script type="text/javascript">

    $('#pwdShow').click(function(){

      var field = $('#email_password');
      var type = field.attr('type');

      if(type == 'password'){
        field.attr('type','text');
        $(this).find('i.fa').addClass('fa-eye-slash').removeClass('fa-eye');
      }else{
        field.attr('type','password');
        $(this).find('i.fa').addClass('fa-eye').removeClass('fa-eye-slash');
      }

    })

    $('#psdShow').click(function(){

      var field = $('#psd');
      var type = field.attr('type');

      if(type == 'password'){
        field.attr('type','text');
        $(this).find('i.fa').addClass('fa-eye-slash').removeClass('fa-eye');
      }else{
        field.attr('type','password');
        $(this).find('i.fa').addClass('fa-eye').removeClass('fa-eye-slash');
      }

    })
</script>

</body>
</html>
