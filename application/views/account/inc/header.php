<?php	defined('BASEPATH') OR exit('No direct script access allowed');	?>
<?php
$user = $GLOBALS["loguser"];
if($user["id"] == "") {	redirect(base_url("account"));	}
if(file_exists($user['image'])) {	$prf_img = $user['image'];	} else {	$prf_img = 'assets/img/user.jpg';	}


$logo_padng = '';
if(isset($GLOBALS["loguser"]["id"]))
{
	if($GLOBALS["loguser"]["role"] == "Company") {	$company_id = $GLOBALS["loguser"]["id"];	} else {	$company_id = $GLOBALS["loguser"]["company_id"];	}
	$company = $this->crm_model->get_company_details($company_id);
	if(file_exists($company['logo']))
	{
		$logo_padng = ' style="padding-top:3px; padding-bottom:3px;"';
		$logo_text = '<img src="'.base_url($company['logo']).'" alt="'.$company['name'].'" style="max-height:45px;" />';
	}
	else
	{
		$logo_text = '<span class="logo-mini"><b>'.$company['name'].'</b></span><span class="logo-lg"><b>'.$company['name'].'</b></span>';
	}
}
else
{
	$logo_text = '<span class="logo-mini"><b>SLT</b></span><span class="logo-lg"><b>Student Loan Tool Box</b></span>';
}



?>
<header class="main-header">
<a href="<?php echo base_url()?>" class="logo" <?php	echo $logo_padng;	?>><?php	echo $logo_text;	?></a>

<!-- Header Navbar: style can be found in header.less -->
<nav class="navbar navbar-static-top">
  <!-- Sidebar toggle button-->
  <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button"><span class="sr-only">Toggle Navigation</span></a>
  <!-- Navbar Right Menu -->
  <div class="navbar-custom-menu">
    <ul class="nav navbar-nav">

      
      <!--<li class="notifications-menu"><a href="<?php echo base_url('account'); ?>" class="dropdown-toggle"> <i class="fa fa-bell"></i> Notification <span class="label label-warning">0</span></a></li>-->
      
      
      <?php	if(isset($GLOBALS["loguser"]["id"])) {	?>
      <li><a href="<?php echo base_url('account'); ?>"><i class="fa fa-user"></i> My Account</a></li>
      <li><a href="<?php echo base_url('account/logout'); ?>"><i class="fa fa-sign-out"></i> Logout</a></li>
      <?php	}	else {	?>
      <li><a href="<?php echo base_url('account/'); ?>"><i class="fa fa-sign-in"></i> Login</a></li>
      <li><a href="<?php echo base_url('account/register'); ?>"><i class="fa fa-user-plus"></i> Register</a></li>
      <?php	}	?>
      
    </ul>
  </div>

</nav>
</header>