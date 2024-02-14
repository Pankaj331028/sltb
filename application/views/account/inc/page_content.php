<?php	defined('BASEPATH') OR exit('No direct script access allowed');	?>
<?php
if(isset($page['0']['details'])) {
if(trim($page['0']['details'])!='') {
?>
<div style="background-color:#da4e60; background-image:linear-gradient(to right, #01537e, #fb0a79); color:#FFFFFF; padding:10px; margin:15px 0;"><?php echo $page['0']['details']; ?></div>
<?php	}	}	?>