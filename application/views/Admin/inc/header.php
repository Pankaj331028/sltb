<?php	defined('BASEPATH') OR exit('No direct script access allowed');	?>
<?php
$user = $GLOBALS["loguser"];
if($user["id"] == "") {	redirect(base_url("admin"));	}
if(file_exists($user['image'])) {	$prf_img = $user['image'];	} else {	$prf_img = 'assets/img/user.jpg';	}

?>
<header class="main-header">



<a href="<?php echo base_url($this->uri->segment(1))?>" class="logo">
<!-- mini logo for sidebar mini 50x50 pixels -->
<span class="logo-mini"><b>Admin</b></span>
<!-- logo for regular state and mobile devices -->
<span class="logo-lg"><b>Admin</b></span>
</a>


<!-- Header Navbar: style can be found in header.less -->
<nav class="navbar navbar-static-top">
  <!-- Sidebar toggle button-->
  <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
    <span class="sr-only">Toggle Navigation</span>
  </a>
  <!-- Navbar Right Menu -->
  <div class="navbar-custom-menu">
    <ul class="nav navbar-nav">

      
      
      
      
      <!-- User Account: style can be found in dropdown.less -->
      <li class="dropdown user user-menu">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
          <img src="<?php echo base_url($prf_img)?>" class="user-image" alt="User Image">
          <span class="hidden-xs"><?php echo $user['name']; ?></span>
        </a>
        <ul class="dropdown-menu">
          <!-- User image -->
          <li class="user-header">
            <img src="<?php echo base_url($prf_img)?>" class="img-circle" alt="User Image">

            <p>
              <?php echo $GLOBALS["loguser"]["name"]; ?>
              <small>Member since - <?php date("d M Y",strtotime($GLOBALS["loguser"]["add_date"]))?></small>
            </p>
          </li>
          <!-- Menu Body -->
          <li class="user-body">
            <div class="row">
              <div class="col-xs-12 text-center"><a href="<?php echo base_url('admin/cp'); ?>"><i class="fa fa-lock"></i> Change Password</a></div>
            </div>
            <!-- /.row -->
          </li>
          <!-- Menu Footer-->
          <li class="user-footer">
            <div class="pull-left"><a href="<?php echo base_url('admin/profile'); ?>" class="btn btn-default btn-flat">Profile</a></div>
            <div class="pull-right"><a href="<?php echo base_url('admin/logout'); ?>" class="btn btn-default btn-flat">Sign out</a></div>
          </li>
        </ul>
      </li>
    </ul>
  </div>

</nav>
</header>