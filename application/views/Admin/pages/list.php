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
      <h1><?php echo $data["name"]; ?></h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo base_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?php echo base_url('admin/pages'); ?>">Manage Pages</a></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">


      <div class="row">
        <!-- /.col -->
        <div class="col-md-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="<?php echo base_url("admin/pages"); ?>"><i class="fa fa-laptop"></i> Manage Pages</a></li>
              <li><a href="<?php echo base_url("admin/pages/new"); ?>"><i class="fa fa-user-plus"></i> Add New Page</a></li>
            </ul>
            <div class="tab-content">
              
              <div class="active tab-pane" id="settings">
<?php	$this->load->view("template/alert.php");	?>
<div class="table-responsive">
<table  class="table table-bordered table-striped" id="result_data_table">
        <thead>
        <tr class="info">
          <th width="1%">SNO</th>
          <th>Name</th>
          <?php if($GLOBALS["loguser"]["role"]=='Admin') { ?><th width="1%">Action</th><?php }	?>
        </tr>
        </thead>
        <tbody>
<?php
$sno = 0;
$query = $this->db->query("SELECT * FROM pages where 1 order by name asc limit 1000000");
foreach ($query->result() as $row) {



?>
<tr id="dtbl_<?php echo $row->id; ?>">
	<td><?php echo ++$sno; ?></td>
    <td><?php echo $row->name?></td>
    <?php if($GLOBALS["loguser"]["role"]=='Admin') { ?>
    <td>
    <a href="<?php echo base_url('admin/pages/edit/'.$row->id)?>" title="Edit"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a> &nbsp; 
    <a title="Delete" href="<?php echo base_url('admin/pages/delete/'.$row->id)?>" onClick="return confirm('Are you sure?')"><i class="fa fa-trash" aria-hidden="true"></i></a>
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
