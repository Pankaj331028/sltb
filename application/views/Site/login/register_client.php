<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php
$sg_1 = $this->uri->segment(1);
$sg_2 = $this->uri->segment(2);
$sg_3 = $this->uri->segment(3);

@error_reporting(E_ALL);
@extract($_POST);
if (!isset($name)) {$name = "";}
if (!isset($lname)) {$lname = "";}
if (!isset($phone)) {$phone = "";}
if (!isset($email)) {$email = "";}

?>
<!DOCTYPE html>
<html>
<head>
<?php	$this->load->view("Site/inc/head");?>
</head>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
<?php	if (isset($company_data['name'])) {$this->load->view("Site/inc/header_client_login");}?>


<div class="content-wrapper" style="background:url(<?php	echo base_url("assets/img/bg-home.jpg"); ?>); background-size:cover;">
    <div class="container">
      <!-- Content Header (Page header) -->
      <!--<section class="content-header"><h1><?php	if (isset($data['name'])) {echo $data['name'];}?></h1></section>-->
      <!-- Main content -->
      <section class="content"><div style="padding:50px 0px 0px 0px;;">
	  <div class="row">
      <div class="col-md-3"></div>
      <div class="col-md-6">

<div class="clr"></div>
<div class="panel panel-primary">
  <div class="panel-heading"><i class="fa fa-user"></i> <strong>Registration</strong></div>
  <div class="panel-body">
<?php	$this->load->view("account/inc/alert");?>

<?php
$error = "";
if (!isset($company_data['name'])) {
	$error = '<p>We apologize, but you need to get a new link from your Student Loan Professional. If you do not have a Student Loan Professional to assist you with your student loans, you can find one at:</p><p><a href="' . base_url() . '">' . base_url() . '</a></p>';
} else if (!isset($company_smtp_data['id'])) {
	//$error = '<p>We apologize, Your comapny\'s email configuration is pending. Contact your comapny admin.</p><p><a href="'.base_url().'">'.base_url().'</a></p>';
} else {}

if ($error == "") {
	?>
    <form action="" method="post" enctype="multipart/form-data">

<div class="row">

<div class="form-group col-md-6"><label>First Name *</label><input type="text" class="form-control" name="name" value="<?php echo $name; ?>" required></div>
<div class="form-group col-md-6"><label>Last Name *</label><input type="text" class="form-control" name="lname" value="<?php echo $lname; ?>" required></div>

<div class="form-group col-md-6"><label>Email Address *</label><input type="email" class="form-control" name="email" value="<?php echo $email; ?>" required></div>
<div class="form-group col-md-6"><label>Phone Number *</label><input type="text" class="form-control" name="phone" value="<?php echo $phone; ?>" required></div>



<div class="form-group col-md-6"><label>Password</label><input type="password" class="form-control" name="password" required></div>
<div class="form-group col-md-6"><label>Retype Password</label><input type="password" class="form-control" name="rpassword" required></div>

<div class="col-md-12"><p style="font-size:12px; margin:-10px 0 20px 0; color:#0066CC;">Password must be at least 10 alphanumeric characters and must contain at least 1 capital letter, 1 lower case, 1 number and 1 special character (!@#$).</p></div>


</div>
<div class="row" style="margin: auto;margin-bottom: 20px;">
  <p class="captchaMsg" style="display: none; font-size: 12px; color: red;"> Google could not verify your identity.</p>
<div id="captcha" class="g-recaptcha" data-sitekey="<?=GOOGLE_SITE_KEY?>"></div>
  </div>


      <div class="row">
        <div class="col-xs-12 text-left">
          <button type="submit" class="btn btn-primary btn-flat" id="register" disabled>Submit</button> &nbsp;
          <a href="<?php echo base_url($sg_1 . '/client_login') ?>" class="btn btn-danger btn-flat">Cancel</a>
        </div>
      </div>
    </form>

    <p>&nbsp;</p>
    <p class="text-center"><a href="<?php echo base_url($sg_1 . '/client_login'); ?>">Back to Login</a></p>
<?php	} else {echo $error;}?>
  </div>
</div>

      </div>
      </div>

      </div></section>
      <!-- /.content -->
    </div>
    <!-- /.container -->
  </div>

<?php	//$this->load->view("account/inc/footer");	?>

</div>
<?php	$this->load->view("account/inc/template_js.php");?>
<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit">
</script>
<script type="text/javascript">
  var widgetId1;
  var onloadCallback = function() {
    widgetId1 = grecaptcha.render('captcha', {
      'sitekey' : '<?=GOOGLE_SITE_KEY?>',
      'callback' : verifyCallback,
      'expired-callback': expCallback,
      'theme' : 'light'
    });
  };
  var expCallback = function() {
      grecaptcha.reset();
      $('#register').prop('disabled',true);
   };

  var verifyCallback = function(response) {

    var url = '/home/captcha_verify';
    $.ajax({
      'url': url,
      type: "POST",
      data: { response: response, 'secret': '<?=GOOGLE_SITE_SECRET?>' },
      success: function(data) {

        var res = JSON.parse(data);
        if (!res.success) {
          $('.captchaMsg').show();
          $('#register').prop('disabled',true);
        } else {
          $('.captchaMsg').hide();
          $('#register').prop('disabled',false);
        }
      } // end of success:
    });
  };
</script>
</body>
</html>
