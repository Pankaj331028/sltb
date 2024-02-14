<?php	defined('BASEPATH') OR exit('No direct script access allowed');	?>
<?php
$user = $GLOBALS["loguser"];
if(file_exists($user['image'])) {	$prf_img = $user['image'];	} else {	$prf_img = 'assets/img/user.jpg';	}
$sg_2 = $this->uri->segment(2);

if(!isset($GLOBALS['hide_account_left_sidebas']))
{
?>
<aside class="main-sidebar">
<!-- sidebar: style can be found in sidebar.less -->
<section class="sidebar">
  <!-- Sidebar user panel -->
  <div class="user-panel">
    <div class="pull-left image">
      <img src="<?php echo base_url($prf_img)?>" class="img-circle" alt="User Image" style="width:45px; height:45px;" />
    </div>
    <div class="pull-left info">
      <p><?php $user["name"]?><br />(<?php $user["id"]?>)</p>
      <p></p>
    </div>
  </div>
  
  <ul class="sidebar-menu">
    <li class=" <?php if($sg_2 == 'dashboard') {	echo " active";	} ?>"><a href="<?php echo base_url('account/dashboard'); ?>"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>

	
<?php	if($GLOBALS["loguser"]["role"] == "Company") {	?>
    <li class="treeview <?php if($sg_2 == 'team') {	echo " active";	} ?>">
      <a href="#"><i class="fa fa-user"></i> Company User</span> <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>
      <ul class="treeview-menu">
        <li><a href="<?php echo base_url('account/team'); ?>"><i class="fa fa-list"></i> Manage User</a></li>
        <li><a href="<?php echo base_url('account/team/new'); ?>"><i class="fa fa-plus"></i> Create New User</a></li>
      </ul>
    </li>
<?php	}	?>


<?php	if($GLOBALS["loguser"]["role"] == "Company" || $GLOBALS["loguser"]["role"] == "Company User") {	?>
    <li class="treeview <?php if($sg_2 == 'customer') {	echo " active";	} ?>">
      <a href="#"><i class="fa fa-user"></i> <span>Client Management</span> <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>
      <ul class="treeview-menu">
        <li><a href="<?php echo base_url('account/customer'); ?>"><i class="fa fa-list"></i> Manage Client</a></li>
        <li><a href="<?php echo base_url('account/customer/new'); ?>"><i class="fa fa-user-plus"></i> Create New Client</a></li>
      </ul>
    </li>
    
    
    <li class="treeview <?php if($sg_2 == 'programs') {	echo " active";	} ?>">
      <a href="#"><i class="fa fa-calendar"></i> <span>Program Management</span> <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>
      <ul class="treeview-menu">
<?php
$q = $this->db->query("SELECT * FROM program_definitions where 1 group by program_title order by program_title");
foreach ($q->result() as $r) {
?>
        <li><a href="<?php echo base_url('account/programs/'.$r->program_definition_id)?>"><i class="fa fa-list"></i> <?php echo $r->program_title?></a></li>
<?php	}	?>
      </ul>
    </li>
    
<?php	}	?>

	<li class="treeview <?php if($sg_2 == 'document') {	echo " active";	} ?>">
      <a href="#"><i class="fa fa-file-pdf-o"></i> <span>Document</span> <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>
      <ul class="treeview-menu">
        <li><a href="<?php echo base_url('account/document'); ?>"><i class="fa fa-list"></i> Manage Document</a></li>
        <li><a href="<?php echo base_url('account/document/new'); ?>"><i class="fa fa-upload"></i> Upload Document</a></li>
      </ul>
    </li>
    
    <?php	if($GLOBALS["loguser"]["role"] == "Customer") {	?>
    <li class=" <?php if($sg_2 == 'intake_form') {	echo " active";	} ?>"><a href="<?php echo base_url('account/intake_form'); ?>"><i class="fa fa-file-text-o"></i> <span>Intake Form</span></a></li>
    <?php	}	?>

    
    <li class="treeview <?php if($sg_2 == 'profile' || $sg_2 == 'cp' || $sg_2 == 'emails' || $sg_2 == 'billing') {	echo " active";	} ?>">
      <a href="#"><i class="fa fa-cog"></i> Settings</span> <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>
      <ul class="treeview-menu">
        <li><a href="<?php echo base_url('account/profile'); ?>"><i class="fa fa-user"></i> My Account</a></li>
<?php	if($GLOBALS["loguser"]["role"] == "Company") {	?>
		<li><a href="<?php echo base_url('account/case_manager'); ?>"><i class="fa fa-user-plus"></i> Case Manager</a></li>
        <li><a href="<?php echo base_url('account/emails'); ?>"><i class="fa fa-envelope-o"></i> Emails </a></li>
        <li><a href="<?php echo base_url('account/billing'); ?>"><i class="fa fa-file-text-o"></i> Billing</a></li>
<?php	}	?>
        <li><a href="<?php echo base_url('account/cp'); ?>"><i class="fa fa-lock"></i> Change Password</a></li>
      </ul>
    </li>

<li><a href="<?php echo base_url('contact-us'); ?>"><i class="fa fa-envelope-o"></i> <span>Contact Us</span></a></li>



  </ul>
</section>
<!-- /.sidebar -->
</aside>
<?php	}	?>