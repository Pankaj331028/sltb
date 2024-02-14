<?php	defined('BASEPATH') OR exit('No direct script access allowed');	?>
<?php
$sg_1 = $this->uri->segment(1);
$sg_2 = $this->uri->segment(2);
$sg_3 = $this->uri->segment(3);

?>
<header class="main-header">
    <nav class="navbar navbar-static-top">

      <div class="container-fluid">
        <div class="navbar-header" style="width:100%;">
<a href="<?php echo base_url($company_data['slug'].'/client_login'); ?>" class="navbar-brand" <?php	if(file_exists($company_data['logo'])) { echo ' style="padding-top:0px;"'; } ?>>
<?php	if(file_exists($company_data['logo'])) {	echo '<img src="'.base_url($company_data['logo']).'" alt="'.$company_data['name'].'" style="max-height:50px;" />';	} else { if(trim($company_data['name'])!="") { echo '<b>'.$company_data['name'].'</b>'; } else { echo '<b>'.$company_data['name'].'</b>'; } }
?>
</a>


<div class="navbar-custom-menu">
<ul class="nav navbar-nav">
  <li><a href="<?php echo base_url($company_data['slug'].'/client_login'); ?>"><i class="fa fa-user"></i> Login</a></li>
  <li><a href="<?php echo base_url($company_data['slug'].'/client_registration'); ?>"><i class="fa fa-user-plus"></i> Register</a></li>
</ul>
</div>
</div>

      </div>
      <!-- /.container-fluid -->
    </nav>
  </header>