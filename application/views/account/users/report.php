<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php
error_reporting(0);
@extract($_POST);

$sg_3 = $this->uri->segment(3);

if ($this->uri->segment(4) > 0) {
	if ($GLOBALS["loguser"]["role"] == "Company") {$cndvar = "company_id";} else { $cndvar = "company_id";}
// if($GLOBALS["loguser"]["role"] == "Company")  { $cndvar = "company_id"; } else {  $cndvar = "parent_id";  }
	$user = $this->default_model->get_arrby_tbl('users', '*', "role='Customer' and $cndvar='" . $GLOBALS["loguser"]["company_id"] . "' and id='" . $this->uri->segment(4) . "'", '1');
	$user = $user["0"];
	if (!isset($user['id'])) {redirect(base_url("account/customer"));exit;}
	@extract($user);
}
?>
<!DOCTYPE html>
<html>
<head>
<?php	$this->load->view("account/inc/head");?>
</head>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
<?php	$this->load->view("Site/inc/header");?>


<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><strong><?php echo $data["name"]; ?></strong></h1>
      <p><strong>Client:</strong>  <?php echo $user['lname']; ?>, <?php echo $user['name']; ?> (#<?php echo $user['id']; ?>)</p>
    </section>

    <!-- Main content -->
    <section class="content">

      <div class="row">
        <!-- /.col -->
        <div class="col-md-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
<?php if ($this->uri->segment(4) > 0) {?>
<li><a href="<?php echo base_url("account/customer/edit/" . $this->uri->segment(4)) ?>"><i class="fa fa-pencil"></i> Edit</a></li>
<li><a href="<?php echo base_url("account/customer/view/" . $this->uri->segment(4)) ?>"><i class="fa fa-eye"></i> View Client</a></li>
<li><a href="<?php echo base_url("account/customer/add_program/" . $this->uri->segment(4)) ?>"><i class="fa fa-file-text-o"></i> Programs</a></li>
<!--<li><a href="<?php echo base_url("account/customer/status/" . $this->uri->segment(4)) ?>"><i class="fa fa-file-text-o"></i> View Client Status</a></li>-->
<li><a href="<?php echo base_url("account/customer/document/" . $this->uri->segment(4)) ?>"><i class="fa fa-upload"></i> Documents</a></li>
<li class="active"><a href="<?php echo base_url("account/customer/report/" . $this->uri->segment(4)) ?>"><i class="fa fa-line-chart"></i> View Reminder Reports</a></li>
<?php	} else {?>
              <li class="active"><a href=""><i class="fa fa-user-plus"></i> <?php echo $data["name"]; ?></a></li>
              <li><a href="<?php echo base_url("account/customer"); ?>"><i class="fa fa-users"></i> Clients</a></li>
<?php	}?>
            </ul>
            <div class="tab-content">

              <div class="active tab-pane" id="settings">
<?php	$this->load->view("template/alert.php");?>


<?php

if (isset($data['report_list']['status_individual'])) {
	if ($data['report_list']['status_individual'] == "Success") {
		?>
<div>
<table class="table table-bordered">
<tr>	<th width="100">Date</th>	<td><?php echo date('m/d/Y h:i A', strtotime($data['report_list']['sent_date'])); ?></td>	</tr>
<tr>	<th>Client</th>	<td><?php echo $user['lname']; ?>, <?php echo $user['name']; ?></td>	</tr>
<tr>	<th>Program</th>	<td><?php	echo $data['report_list']['program_title']; ?></td>	</tr>
<tr>	<th>Step</th>	<td><?php	echo $data['report_list']['step_name']; ?></td>	</tr>
<tr>	<th>Subject</th>	<td><?php	echo $data['report_list']['subject']; ?></td>	</tr>
<tr>	<th>Email Body</th>	<td><?php	echo $data['report_list']['email_body']; ?></td>	</tr>
</table>
</div>
<?php
} else {
		echo '<div class="alert alert-danger">Something went wrong.</div>';
	}
} else {
	?>
<div>

<div class="table-responsive">
<table id="show_datatable" class="table table-bordered">
<thead>
<tr class="info">
  <th>Date</th>
  <th>Client</th>
  <th>Program</th>
  <th>Step</th>
  <th>Subject</th>

</tr>
</thead>
<tbody>
<?php	foreach ($data['report_list'] as $row) {?>
<tr>
	<td><?php echo date('m/d/Y h:i A', strtotime($row['sent_date'])); ?></td>
    <td><?php echo $user['lname']; ?>, <?php echo $user['name']; ?></td>
    <td><a href="<?php echo base_url("account/customer/report/" . $this->uri->segment(4) . "/" . $row['id']) ?>"><?php echo $row['program_title']; ?></a></td>
    <td><a href="<?php echo base_url("account/customer/report/" . $this->uri->segment(4) . "/" . $row['id']) ?>"><?php echo $row['step_name']; ?></a></td>
    <td><a href="<?php echo base_url("account/customer/report/" . $this->uri->segment(4) . "/" . $row['id']) ?>"><?php echo $row['subject']; ?></a></td>
</tr>
<?php	}?>
</tbody>
</tbody>
</table>
</div>


</table>


              </div>
<?php	}?>
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

<?php	$this->load->view("account/inc/footer");?>

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

<script type="text/javascript">

  $(function () {
    $('#show_datatable').DataTable({
      "paging": true,
      "lengthChange": false,
	  "pageLength": 50,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": true
    });
  });
</script>
</body>
</html>
