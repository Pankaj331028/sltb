<?php	defined('BASEPATH') OR exit('No direct script access allowed');	?>

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
<p>This site is working fine now </p>
    </div>
</div>
<?php	$this->load->view("account/inc/footer");	?>

</div>
<?php	$this->load->view("account/inc/template_js.php");	?>

</body>
</html>
