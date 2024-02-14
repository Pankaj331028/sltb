<?php	defined('BASEPATH') OR exit('No direct script access allowed');	?>
<?php
$type_of_inquiry = $accno = $name = $phone = $email = $inquiry_summary = $message = $accept = "";
@extract($_POST);
?>
<!DOCTYPE html>
<html>
<head>
<?php	$this->load->view("Site/inc/head");	?>
</head>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
<?php	$this->load->view("Site/inc/header");	?>
<?php	//$this->load->view("account/inc/leftnav");	?>


<div class="content-wrapper" style="background:#FFFFFF;">
    <div class="container" style="background:#FFFFFF;">
      <!-- Content Header (Page header) -->
      <!--<section class="content-header"><h1><?php	if(isset($data['name'])) {	echo $data['name'];	}	?></h1></section>-->
      <!-- Main content -->
      <section class="content"><div style="padding:25px 0px;">
	  
<h2><strong>Contact Us</strong></h2>
<hr style="margin-top:10px; border-top:1px solid #ccc;" />
<p>For any questions you may have please use the below form. We will respond as quickly as possible.</p>      

<div class="row">
<div class="col-md-8">
<div><?php	$this->load->view("Admin/inc/alert");	?></div>
<form action="" method="post" enctype="multipart/form-data">
<div class="row">
<div class="col-md-6">
<div class="form-group"><label for="usr">Type of Inquiry:</label>
<select type="text" class="form-control" name="type_of_inquiry" required onChange="change_type_of_inq(this.value)">
<option value="">Select</option>
<option value="Sales" <?php	if($type_of_inquiry == "Sales") { echo " selected"; } ?>>Sales</option>
<option value="Support" <?php	if($type_of_inquiry == "Support") { echo " selected"; } ?>>Support</option>
<option value="Billing" <?php	if($type_of_inquiry == "Billing") { echo " selected"; } ?>>Billing</option>
<option value="Other" <?php	if($type_of_inquiry == "Other") { echo " selected"; } ?>>Other</option>
</select>
</div></div>

<div class="col-md-6" style="display:none;"><div class="form-group"><label for="usr">Account Number:</label><input type="text" class="form-control tsb" name="accno" value="<?php echo $accno?>" /></div></div>

<div class="col-md-6"><div class="form-group"><label for="usr">Name:</label><input type="text" class="form-control" name="name" value="<?php echo $name; ?>" required></div></div>
<div class="col-md-6"><div class="form-group"><label for="usr">Email:</label><input type="email" class="form-control" name="email" value="<?php echo $email; ?>" required></div></div>
<div class="col-md-6"><div class="form-group"><label for="usr">Phone Number:</label><input type="text" class="form-control tsb" name="phone" value="<?php echo $phone; ?>"></div></div>
<!--<div class="col-md-4"><div class="form-group"><label for="usr">Inquiry Summary:</label><input type="text" class="form-control" name="inquiry_summary" value="<?php echo $inquiry_summary?>" maxlength="80" /></div></div>-->
<div class="col-md-12"><div class="form-group"><label for="usr">Message:</label><textarea class="form-control" name="message" maxlength="1000" required><?php $message?></textarea></div></div>

<div class="col-md-4">
<div class="form-group">
<label for="name">Captcha:</label><br />
<input name="captcha" type="text" class="form-control" required />
<span id="img_contact_captcha_div"><?php echo $data['captcha']['image']; ?></span> &nbsp; 
<span style="color:#0066CC; font-size:15px; cursor:pointer;" onClick="change_captcha_contact()"><i class="fa fa-refresh"></i> Refresh</span>
</div>
</div>

</div>


<p><label class="checkbox-inline"><input type="checkbox" name="accept" value="Yes" required />  By checking this box you agree that <strong>Student Loan Toolbox<sup>TM</sup></strong> can use your contact information to follow up on this request.</label></p>

<button type="submit" name="Submit_" class="btn btn-primary btn-block">Submit</button>
</form>
</div>
</div>  

<script type="text/javascript">

function change_captcha_contact()
{
	var url = "<?php echo base_url('home/regeneratecaptcha'); ?>";
	$.post(url, { name: 'Abc' },
	function(data,status){
	//swal("Data: " + data + "\nStatus: " + status);
	var obj = JSON.parse(data);
	$("#img_contact_captcha_div").html(obj.image);
	
	});	
}

function change_type_of_inq(val)
{
	if(val == "Support" || val == "Billing")
	{
		//$(".tsb").attr("required", "required");
		$(".tsb").removeAttr("required");
	}
	else
	{
		$(".tsb").removeAttr("required");
	}
}
change_type_of_inq('<?php $type_of_inquiry?>');
</script>


      </div></section>
      <!-- /.content -->
    </div>
    <!-- /.container -->
  </div>

<?php	$this->load->view("account/inc/footer");	?>

</div>
<?php	$this->load->view("account/inc/template_js.php");	?>

</body>
</html>
