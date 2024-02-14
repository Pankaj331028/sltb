<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php	error_reporting(0);?>
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
      <h1>
      	<strong><?php echo $data["name"]; ?></strong>
      </h1>
    </section>

    <!-- Main content -->
    <section class="content">


      <div class="row">
        <!-- /.col -->
        <div class="col-md-12">

<div class="nav-tabs-custom">
<ul class="nav nav-tabs">
<?php	if ($GLOBALS["loguser"]["role"] == "Company") {?>
<li><a href="<?php echo base_url("account/company"); ?>"><i class="fa fa-globe"></i> Company</a></li>
<li class="active"><a href="<?php echo base_url("account/team"); ?>"><i class="fa fa-user"></i> Users</a></li>
<li><a href="<?php echo base_url("account/emails"); ?>"><i class="fa fa-envelope-o"></i> SMTP Emails</a></li>
<li><a href="<?php echo base_url("account/billing"); ?>"><i class="fa fa-credit-card-alt"></i> Payment</a></li>
<li><a href="<?php echo base_url("integration"); ?>"><i class="fa fa-share"></i> Integrations</a></li>
<li><a href="<?php echo base_url("account/reminders"); ?>"><i class="fa fa-credit-card-alt"></i> Reminders</a></li>
<?php	}?>
<li><a href="<?php echo base_url("account/profile"); ?>"><i class="fa fa-pencil"></i> My Profile</a></li>
<li><a href="<?php echo base_url("account/cp"); ?>"><i class="fa fa-lock"></i> Change Password</a></li>

</ul>
            <div class="tab-content">

<div class="active tab-pane" id="settings">
<div class="clr"></div>
<div style="padding-bottom:10px;">
<a href="<?php echo base_url("account/team/new"); ?>" class="btn btn-primary btn-flat pull-right"><i class="fa fa-user-plus"></i> Add New User</a>
<div class="clr"></div>
</div>
<div class="clr"></div>
<?php	$this->load->view("template/alert.php");?>
<div class="table-responsive">
<table id="show_datatable__" class="table table-bordered table-striped">
        <thead>
        <tr class="info">
          <!--<th width="1%">SNO</th>-->
          <th>Name</th>
          <th>Mobile</th>
          <th>Email</th>
          <th>Position</th>
          <th width="60">Reg Date</th>
          <th>Status</th>
          <th width="100">Action</th>
        </tr>
        </thead>
        <tbody>
<?php
$sno = 0;
$query = $this->db->query("SELECT * FROM users where (role='Company User' or role='Company') and company_id='" . $GLOBALS["loguser"]["id"] . "' order by id asc");
foreach ($query->result() as $row) {
	?>
<tr id="dtbl_<?php echo $row->id; ?>">
	<!--<td><?php echo ++$sno; ?></td>-->
    <td><?php echo $row->name ?> <?php echo $row->lname ?></td>
    <td><?php echo $row->phone ?></td>
    <td><a href="mailto:<?php echo $row->email; ?>"><?php echo $row->email; ?></a></td>
    <td><?php echo $row->position ?></td>
    <td><?php echo date('m/d/Y', strtotime($row->add_date)) ?></td>
    <td><?=$row->status?></td>
    <td>
    <a href="<?php echo base_url('account/team/edit/' . $row->id) ?>" title="Edit" class="btn btn-sm btn-info"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a> &nbsp;
    <?php	if ($row->role != 'Company') {?><a title="Delete" href="<?php echo base_url('account/team/delete/' . $row->id) ?>" class="btn btn-sm btn-danger" onClick="return confirm('Are you sure?')"><i class="fa fa-trash" aria-hidden="true"></i></a><?php	} else {
		if ($row->status == 'Pending' || $row->status == 'Rejected') {
			?>

    <a href="<?php echo base_url('account/account_request/' . base64_encode($row->id) . '/approve') ?>" title="Approve Account" class="btn btn-sm btn-success"><i class="fa fa-check-circle" aria-hidden="true"></i></a> &nbsp;
    <?php
if ($row->status == 'Pending') {
				?>
    <a href="<?php echo base_url('account/account_request/' . base64_encode($row->id) . '/reject') ?>" title="Reject Account" class="btn btn-sm btn-warning"><i class="fa fa-times" aria-hidden="true"></i></a> &nbsp;
      <?php
}
		}
	}?>
    </td>
</tr>
<?php	}?>
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
