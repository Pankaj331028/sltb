<?php	defined('BASEPATH') OR exit('No direct script access allowed');	?>
<?php

$user = $GLOBALS["loguser"];
if(file_exists($user['image'])) {	$prf_img = $user['image'];	} else {	$prf_img = 'assets/img/user.jpg';	}
$sg_2 = $this->uri->segment(2);

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
      <p><?php $user["name"]?></p>
      <a href="<?php echo base_url('admin/profile'); ?>"><i class="fa fa-circle text-success"></i> Online</a>
    </div>
  </div>
  
  <ul class="sidebar-menu">
    <li class=" <?php if($sg_2 == 'dashboard') {	echo " active";	} ?>"><a href="<?php echo base_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
    
<li class="treeview"><a href="javascript:Void(0)"><i class="fa fa-user"></i><span>Company/Clients</span>
    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
  </a>
  <ul class="treeview-menu" style="display: none;">
<li class=" <?php if($sg_2 == 'company') {	echo " active";	} ?>"><a href="<?php echo base_url('admin/company'); ?>">&raquo; Company</a></li>
<li class=" <?php if($sg_2 == 'case_manager') {	echo " active";	} ?>"><a href="<?php echo base_url('admin/case_manager'); ?>">&raquo; Company User</a></li>
<li class=" <?php if($sg_2 == 'clients') {	echo " active";	} ?>"><a href="<?php echo base_url('admin/clients'); ?>">&raquo; Clients</a></li>
  </ul>
</li>

<li class="treeview"><a href="javascript:Void(0)"><i class="fa fa-credit-card-alt"></i> <span>Payments</span>
    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
  </a>
  <ul class="treeview-menu" style="display: none;">
    <li><a href="javascript:Void(0)">&raquo; Paid Payment</a></li>
    <li><a href="javascript:Void(0)">&raquo; Pending Payment</a></li>
  </ul>
</li>


<li class="treeview"><a href="javascript:Void(0)"><i class="fa fa-tags"></i> <span>Coupons</span>
    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
  </a>
  <ul class="treeview-menu" style="display: none;">
    <li><a href="javascript:Void(0)">&raquo; All Coupons</a></li>
    <li><a href="javascript:Void(0)">&raquo; Used Coupons</a></li>
  </ul>
</li>


<li class="treeview"><a href="javascript:Void(0)"><i class="fa fa-pie-chart"></i><span>Reports</span>
    <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
  </a>
  <ul class="treeview-menu" style="display: none;">
    <li><a href="javascript:Void(0)">&raquo; Company</a></li>
    <li><a href="javascript:Void(0)">&raquo; Clients</a></li>
    <li><a href="javascript:Void(0)">&raquo; Payments</a></li>
    <li><a href="javascript:Void(0)">&raquo; Coupons</a></li>
  </ul>
</li>

    
<li class=" <?php if($sg_2 == 'contact_us_history') {	echo " active";	} ?>"><a href="<?php echo base_url('admin/contact_us_history'); ?>"><i class="fa fa-envelope-o"></i> <span>Contact Us</span></a></li>
    
   
    
    
    
    
    





  </ul>
</section>
<!-- /.sidebar -->
</aside>