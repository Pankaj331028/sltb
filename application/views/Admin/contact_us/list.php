<?php	defined('BASEPATH') OR exit('No direct script access allowed');	?>
<?php	error_reporting(0);	?>
<!DOCTYPE html>
<html>
<head>
<?php	$this->load->view("Admin/inc/head");	?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php	$this->load->view("Admin/inc/header");	?>
<?php	$this->load->view("Admin/inc/leftnav");	?>


<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><i class="fa fa-envelope-o"></i> <?php echo $data["name"]; ?></h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo base_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?php echo base_url('admin/contact_us_history'); ?>">Manage Contact Us</a></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">


      <div class="row">
        <!-- /.col -->
        <div class="col-md-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="<?php echo base_url("admin/contact_us_history"); ?>"><i class="fa fa-envelope-o"></i> Contact Us</a></li>
            </ul>
            <div class="tab-content">
              
              <div class="active tab-pane" id="settings">
<?php	$this->load->view("template/alert.php");	?>
<div class="table-responsive">
<table  class="table table-bordered table-striped" id="result_data_table">
        <thead>
        <tr class="info">
          <th width="1%">SNO</th>
          <th>Contact Details</th>
          <th>Message</th>
          <?php if($GLOBALS["loguser"]["role"]=='Admin') { ?><th width="1%">Action</th><?php }	?>
        </tr>
        </thead>
        <tbody>
<?php
$sno = 0;
$query = $this->db->query("SELECT * FROM contact_us_history where 1 order by id desc limit 1000000");
foreach ($query->result() as $row) {



?>
<tr id="dtbl_<?php echo $row->id; ?>">
	<td><?php echo ++$sno; ?></td>
    <td>
    	<strong>Name : </strong><?php echo $row->name?><br />
        <strong>Email : </strong><a href="mailto:<?php echo $row->email?>"><?php echo $row->email?></a><br />
        <?php	if($row->phone!='') { ?><strong>Phone : </strong><a href="tel:<?php echo $row->phone?>"><?php echo $row->phone?></a><br /><?php	}	?>
        <?php	if($row->accno>0) { ?><strong>Account Number : </strong><?php echo $row->accno?><?php	}	?>
    </td>
    
    <td>
    	<strong>Type of Inquiry : </strong><?php echo $row->type_of_inquiry?><br />
        <?php	if($row->inquiry_summary!="") { ?><strong>Inquiry Summary : </strong><?php echo $row->inquiry_summary?><br /><?php	}	?>
        <strong>Message : </strong><?php echo $row->message?><br />
    </td>
    
    <?php if($GLOBALS["loguser"]["role"]=='Admin') { ?>
    <td>
    <a title="Delete" href="<?php echo base_url('admin/contact_us_history/delete/'.$row->id)?>" onClick="return confirm('Are you sure?')"><i class="fa fa-trash" aria-hidden="true"></i></a>
    </td>
    <?php	}	?>
</tr>
<?php	}
if($sno == 0) {	?><tr><td colspan="10" style="color:red;">No record found.</td></tr><?php	}
?>
        </tbody>
        
      </table>
</div>
              </div>
              <!-- /.tab-pane -->
            </div>
            <!-- /.tab-content -->
          </div>
          <!-- /.nav-tabs-custom -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->

    </section>
    <!-- /.content -->
  </div>

<?php	$this->load->view("Admin/inc/footer");	?>

</div>
<?php	$this->load->view("Admin/inc/template_js.php");	?>

</body>
</html>
