<?php	defined('BASEPATH') OR exit('No direct script access allowed');	?>
<?php
@error_reporting(E_ALL);
@extract($_POST);
if(!isset($name)) {	$name = "";	}
if(!isset($lname)) {	$lname = "";	}
if(!isset($phone)) {	$phone = "";	}
if(!isset($email)) {	$email = "";	}
if(!isset($position)) {	$position = "";	}
if(!isset($siterole)) {	$siterole = "";	}
if(!isset($company_name)) {	$company_name = "";	}

if(!isset($address)) {	$address = "";	}
if(!isset($state)) {	$state = "";	}
if(!isset($city)) {	$city = "";	}
if(!isset($zip_code)) {	$zip_code = "";	}
?>
<!DOCTYPE html>
<html>
<head>

<?php	$this->load->view("account/inc/head");	?>
</head>

<body class="hold-transition login-page" style="background:url(<?php echo base_url('assets/img/bg-home.jpg'); ?>); background-size:100%;">
<div class="container-fluid">
<div class="row">
<div class="col-md-2"></div>
<div class="col-md-8">
<div class="login-box" style="width:100%;">
  
  <div class="login-box-body">
    <div class="login-logo"><a href="<?php echo base_url(''); ?>"><b>Student Loan Tool Box</b></a></div>
    <p class="login-box-msg"><strong>Registration</strong></p>
    
    <?php	$this->load->view("account/inc/alert");	?>
    
    <form action="" method="post" enctype="multipart/form-data">
<div class="row">

<div class="form-group col-md-6"><label>First Name *</label><input type="text" class="form-control" name="name" value="<?php echo $name; ?>" required></div>
<div class="form-group col-md-6"><label>Last Name *</label><input type="text" class="form-control" name="lname" value="<?php echo $lname; ?>" required></div>

<div class="form-group col-md-6"><label>Email Address *</label><input type="email" class="form-control" name="email" value="<?php echo $email; ?>" required></div>
<div class="form-group col-md-6"><label>Phone Number *</label><input type="text" class="form-control" name="phone" value="<?php echo $phone; ?>" required></div>

<div class="form-group col-md-6"><label>Position</label>
<select name="position" class="form-control" required>
<option value="">Select your position</option>
<option value="Owner" <?php	if($position == "Owner") { echo " selected"; } ?>>Owner</option>
<option value="Attorney" <?php	if($position == "Attorney") { echo " selected"; } ?>>Attorney</option>
<option value="Paralegal" <?php	if($position == "Paralegal") { echo " selected"; } ?>>Paralegal</option>
<option value="Administration" <?php	if($position == "Administration") { echo " selected"; } ?>>Administration</option>
</select>
</div>

<!--<div class="form-group col-md-6"><label>Site Role</label>
<select name="siterole" class="form-control" required>
<option value="">Select your role</option>
<option value="Company Admin" <?php	if($siterole == "Company Admin") { echo " selected"; } ?>>Company Admin</option>
<option value="Company User" <?php	if($siterole == "Company User") { echo " selected"; } ?>>Company User</option>
</select>
</div>-->

<div class="form-group col-md-6"><label>Company Name *</label><input type="text" class="form-control" name="company_name" value="<?php echo $company_name?>" required></div>

<div class="form-group col-md-6"><label>Password</label><input type="password" class="form-control" name="password" required></div>
<div class="form-group col-md-6"><label>Retype Password</label><input type="password" class="form-control" name="rpassword" required></div>

<div class="col-md-12"><p style="font-size:12px; margin:-10px 0 20px 0; color:#0066CC;">Password must be at least 10 alphanumeric characters and must contain at least 1 capital letter, 1 lower case, 1 number and 1 special character (!@#$).</p></div>


</div>

      
      <div class="row">
        <div class="col-xs-12 text-left">
          <button type="submit" class="btn btn-primary btn-flat">Submit</button> &nbsp; 
          <a href="<?php echo base_url()?>" class="btn btn-danger btn-flat">Cancel</a>
        </div>
      </div>
    </form>

    <p>&nbsp;</p>
    <p class="text-center"><a href="<?php echo base_url('account/login'); ?>">Back to Login</a></p>

  </div>
  <!-- /.login-box-body -->
</div>
</div>
</div>
</div>
<?php	$this->load->view("account/inc/template_js");	?>
</body>
</html>