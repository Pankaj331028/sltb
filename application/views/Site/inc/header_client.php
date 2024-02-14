<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php
$sg_1 = $this->uri->segment(1);
$sg_2 = $this->uri->segment(2);
$sg_3 = $this->uri->segment(3);

?>
<header class="main-header">
    <nav class="navbar navbar-static-top">

      <div class="container-fluid">
        <div class="navbar-header" style="width:100%;">
<a href="<?php echo base_url('account/dashboard'); ?>" class="navbar-brand" <?php	if (file_exists($client_data['users_company']['logo'])) {echo ' style="padding-top:0px;"';}?>>
<?php	if (file_exists($client_data['users_company']['logo'])) {echo '<img src="' . base_url($client_data['users_company']['logo']) . '" alt="' . $client_data['users_company']['name'] . '" style="max-height:50px;" />';} else {if (trim($client_data['company']['name']) != "") {echo '<b>' . $client_data['users_company']['name'] . '</b>';} else {echo '<b>' . $client_data['users_company']['name'] . '</b>';}}
?>
</a>


<div class="navbar-custom-menu">
<ul class="nav navbar-nav">
  <li><a href="<?php	echo base_url($this->uri->segment(1) . '/company'); ?>"><i class="fa fa-user"></i> My Account</a></li>
  <li><a href="<?php	echo base_url($this->uri->segment(1) . '/logout'); ?>"><i class="fa fa-sign-out"></i> Logout</a></li>
</ul>
</div>
</div>

      </div>
      <!-- /.container-fluid -->
    </nav>
  </header>