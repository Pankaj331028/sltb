<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php
$sg_1 = $this->uri->segment(1);
$sg_2 = $this->uri->segment(2);
$sg_3 = $this->uri->segment(3);

$logo_padng = '';
if (isset($GLOBALS["loguser"]["id"])) {
	if ($GLOBALS["loguser"]["role"] == "Company") {$company_id = $GLOBALS["loguser"]["id"];} else { $company_id = $GLOBALS["loguser"]["company_id"];}
	$company = $this->crm_model->get_company_details($company_id);

	$logo_link = $company['slug'] . "/dashboard";
	if (file_exists($company['logo'])) {
		$logo_padng = ' style="padding-top:3px; padding-bottom:3px;"';
		$logo_text = '<img src="' . base_url($company['logo']) . '" alt="' . $company['name'] . '" style="max-height:45px;" />';
	} else {
		$logo_text = '<span class="logo-lg"><b>' . $company['name'] . '</b></span>';
	}
} else {
	$logo_link = "";
	$logo_text = '<span class="logo-lg"><b>Student Loan Tool Box</b></span>';
}

?>
<header class="main-header">
    <nav class="navbar navbar-static-top">

      <div class="container-fluid">
        <div class="navbar-header">
        <a href="<?php echo base_url($logo_link) ?>" class="navbar-brand" <?php	echo $logo_padng; ?>><?php	echo $logo_text; ?></a>
         <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse"><i class="fa fa-bars"></i></button>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse pull-left" id="navbar-collapse">
          <ul class="nav navbar-nav">
<?php	if (isset($GLOBALS["loguser"]["id"]) && !isset($GLOBALS['hide_account_left_sidebas'])) {?>
<li <?php if ($sg_2 == 'dashboard' && $sg_3 == '') {echo ' class="active"';}?>><a href="<?php echo base_url('account/dashboard'); ?>">Dashboard</a></li>
<li <?php if ($sg_2 == 'customer') {echo ' class="active"';}?>><a href="<?php echo base_url('account/customer'); ?>">Manage Clients</a></li>
<li <?php if ($sg_2 == 'programs') {echo ' class="active"';}?>><a href="<?php echo base_url('account/programs'); ?>">Program Reports</a></li>
<?php if ($GLOBALS["loguser"]["role"] == 'Company') {?><li <?php if ($sg_2 == 'advertisement') {echo ' class="active"';}?>><a href="<?php echo base_url('account/advertisement'); ?>">Advertisement</a></li><?php }?>
<li <?php if ($sg_2 == 'profile') {echo ' class="active"';}?>><a href="<?php echo base_url('account/company'); ?>">My Account</a></li>
<li <?php if ($sg_1 == 'contact-us') {echo ' class="active"';}?>><a href="<?php echo base_url('contact-us'); ?>">Contact Us</a></li>
<?php	} else {?>
<li <?php if ($sg_1 == '') {echo ' class="active"';}?>><a href="<?php echo base_url() ?>">Home</a></li>
<!--<li <?php if ($sg_1 == 'what-we-do') {echo ' class="active"';}?>><a href="<?php echo base_url('what-we-do'); ?>">What We Do</a></li>-->
<li <?php if ($sg_1 == 'pricing') {echo ' class="active"';}?>><a href="<?php echo base_url('pricing'); ?>">Pricing</a></li>
<li <?php if ($sg_1 == 'contact-us') {echo ' class="active"';}?>><a href="<?php echo base_url('contact-us'); ?>">Contact Us</a></li>
<li <?php if ($sg_1 == 'about-us') {echo ' class="active"';}?>><a href="<?php echo base_url('about-us'); ?>">About Us</a></li>
<?php	}?>
          </ul>

        </div>
        <!-- /.navbar-collapse -->
        <!-- Navbar Right Menu -->

<div class="navbar-custom-menu">
<ul class="nav navbar-nav">
  <?php	if (isset($GLOBALS["loguser"]["id"])) {?>
  <?php	if ($sg_1 != 'account') {?><li><a href="<?php echo base_url('account/company'); ?>"><i class="fa fa-user"></i> My Account</a></li><?php	}?>
  <li><a href="<?php echo base_url('account/logout'); ?>"><i class="fa fa-sign-out"></i> Logout</a></li>
  <?php	} else {?>
  <li><a href="<?php echo base_url('account/company'); ?>"><i class="fa fa-sign-in"></i> Login</a></li>
  <li><a href="<?php echo base_url('account/register'); ?>"><i class="fa fa-user-plus"></i> Register</a></li>
  <?php	}?>
</ul>
</div>
        <!-- /.navbar-custom-menu -->
      </div>
      <!-- /.container-fluid -->
    </nav>
  </header>