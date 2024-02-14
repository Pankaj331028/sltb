<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<title><?php if (isset($data['meta_title'])) {echo $data['meta_title'];} else {echo "Student Loan Toolbox";}?></title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<link rel="shortcut icon" href="<?php echo base_url('assets/img/favicon.ico'); ?>">

<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
<link rel="stylesheet" href="<?php echo base_url('assets/crm/bootstrap/css/bootstrap.min.css'); ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
<link rel="stylesheet" href="<?php echo base_url('assets/crm/plugins/jvectormap/jquery-jvectormap-1.2.2.css'); ?>">
<link rel="stylesheet" href="<?php echo base_url('assets/crm/plugins/datatables/dataTables.bootstrap.css'); ?>">
<link rel="stylesheet" href="<?php echo base_url('assets/crm/dist/css/AdminLTE.min.css'); ?>">
<link rel="stylesheet" href="<?php echo base_url('assets/crm/dist/css/skins/_all-skins.min.css'); ?>">
<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->

<!-- Hotjar Tracking Code for https://studentloantoolbox.net/ -->
<script src="<?php echo base_url('assets/crm/plugins/jQuery/jquery-2.2.3.min.js'); ?>"></script>
<!-- Bootstrap 3.3.6 -->
<script src="<?php echo base_url('assets/crm/bootstrap/js/bootstrap.min.js'); ?>"></script>

<script src="<?php echo base_url('assets/crm/plugins/fastclick/fastclick.js'); ?>"></script>
<script src="<?php echo base_url('assets/crm/dist/js/app.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/crm/plugins/sparkline/jquery.sparkline.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/crm/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/crm/plugins/jvectormap/jquery-jvectormap-world-mill-en.js'); ?>"></script>
<script src="<?php echo base_url('assets/crm/plugins/slimScroll/jquery.slimscroll.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/crm/plugins/chartjs/Chart.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/crm/dist/js/pages/dashboard2.js'); ?>"></script>
<script src="<?php echo base_url('assets/crm/dist/js/demo.js'); ?>"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js" defer="defer"></script>

<script>
    (function(h,o,t,j,a,r){
        h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
        h._hjSettings={hjid:3490962,hjsv:6};
        a=o.getElementsByTagName('head')[0];
        r=o.createElement('script');r.async=1;
        r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
        a.appendChild(r);
    })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
</script>