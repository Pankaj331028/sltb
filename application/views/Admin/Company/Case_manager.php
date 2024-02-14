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
      <h1><strong><?php echo $data["name"]; ?></strong></h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo base_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?php echo base_url('admin/company'); ?>">Company</a></li>
        <li><a href="<?php echo base_url('admin/case_manager'); ?>">Company User</a></li>
        
      </ol>
    </section>

    <!-- Main content -->
    <section class="content" style="background:#FFFFFF;">


      <div class="row">
        <!-- /.col -->
        <div class="col-md-12">
          
<?php	$this->load->view("template/alert.php");	?>
<div class="table-responsive">
<table id="show_datatable" class="table table-bordered table-striped">
        <thead>
        <tr class="info">
          <th width="1%">SNO</th>
          <th>Company</th>
          <th>ID</th>
          <th>Name</th>
          <th>Mobile</th>
          <th>Email</th>
          <th width="60">Reg Date</th>
          <th width="60">View</th>
          <th width="60">Delete</th>
        </tr>
        </thead>
        <tbody>
<?php
$sno = 0;
$sql = "SELECT * FROM users where (role='Company' or role='Company User') order by id desc limit 1000000";
$sql = "SELECT users_company.name as cname, users_company.slug as slug, users.* FROM users RIGHT JOIN users_company ON (users.role='Company' or users.role='Company User') and users.company_id=users_company.id order by users.id desc limit 1000000";

$query = $this->db->query($sql);
foreach ($query->result() as $row) {
?>
<tr id="dtbl_<?php echo $row->id; ?>">
	<td><?php echo ++$sno; ?></td>
    <td><a href="<?php echo base_url('admin/company/view/'.$row->company_id)?>"><?php echo $row->cname?></a></td>
    <td><?php echo $row->id; ?></td>
    <td><?php echo $row->name?> <?php echo $row->lname?></td>
    <td><a href="tel:<?php echo $row->phone?>"><?php echo $row->phone?></a></td>
    <td><a href="mailto:<?php echo $row->email?>"><?php echo $row->email?></a></td>
    <td><?php echo date('d M Y',strtotime($row->add_date)); ?></td>
    <td><a href="<?php echo base_url('admin/case_manager/view/'.$row->id)?>" title="View" class="btn btn-sm btn-primary"><i class="fa fa-eye" aria-hidden="true"></i> View</a></td>
    <td><?php if($row->role == "Company User") { ?><a title="Delete" href="<?php echo base_url('admin/delete/case_manager/'.$row->company_id.'/'.$row->id)?>" class="btn btn-sm btn-danger" onClick="return confirm('Are you sure?')"><i class="fa fa-trash" aria-hidden="true"></i> Delete</a><?php } ?></td>
</tr>
<?php	}	?>
        </tbody>
        
      </table>
</div>
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->

    </section>
    <!-- /.content -->
  </div>

<?php	$this->load->view("Admin/inc/footer");	?>

</div>
<?php	//$this->load->view("Admin/inc/template_js.php");	?>
<script src="<?php echo base_url('assets/crm/plugins/jQuery/jquery-2.2.3.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/crm/bootstrap/js/bootstrap.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/crm/plugins/datatables/jquery.dataTables.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/crm/plugins/datatables/dataTables.bootstrap.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/crm/plugins/slimScroll/jquery.slimscroll.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/crm/plugins/fastclick/fastclick.js'); ?>"></script>
<script src="<?php echo base_url('assets/crm/dist/js/app.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/crm/dist/js/demo.js'); ?>"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js" defer="defer"></script>

<script>
  $(function () {
    $('#show_datatable').DataTable({
      "paging": true,
      "lengthChange": true,
	  "pageLength": 50,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": true
    });
  });
</script>
</body>
</html>
