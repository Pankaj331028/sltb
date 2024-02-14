<?php   
	function gotodashboard(){
		if(!isset($_SERVER['HTTP_REFERER'])){
			//redirect('account/dashboard?redirect=yes');	
			header('Location:https://localhost/rajesh/studentloantoolbox/index.php/account/dashboard?redirect=yes');
		}
	}	
?>