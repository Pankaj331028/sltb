<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php
error_reporting(0);
@extract($_POST);

$cr = $data['company']['data'];
$smtp = $data['company']['smtp'];

?>
<!DOCTYPE html>
<html>
<head>
<?php	$this->load->view("Admin/inc/head");?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php	$this->load->view("Admin/inc/header");?>
<?php	$this->load->view("Admin/inc/leftnav");?>


<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><strong><?php echo $data["name"]; ?></strong></h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo base_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?php echo base_url('admin/company'); ?>">Company</a></li>
        <li><a href="<?php echo base_url('admin/company/view/' . $cr['id']); ?>"><?php echo $cr['name']; ?></a></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <!-- /.col -->
        <div class="col-md-12">
<?php	$this->load->view("template/alert.php");?>

          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#v_overview" data-toggle="tab" aria-expanded="true"><i class="fa fa-laptop"></i> Overview</a></li>
              <li><a href="#v_clients" data-toggle="tab" aria-expanded="true"><i class="fa fa-th-list"></i> Client</a></li>
              <li><a href="#v_program_reports" data-toggle="tab" aria-expanded="true"><i class="fa fa-line-chart"></i> Program Reports</a></li>
              <li><a href="#v_cusers" data-toggle="tab" aria-expanded="true"><i class="fa fa-list-alt"></i> Company User</a></li>
              <li><a href="#v_smtp_email" data-toggle="tab" aria-expanded="true"><i class="fa fa-envelope"></i> SMTP Emails</a></li>
              <li><a href="#v_payment" data-toggle="tab" aria-expanded="true"><i class="fa fa-credit-card-alt"></i> Payment</a></li>
              <li><a href="#v_advertisement" data-toggle="tab" aria-expanded="true"><i class="fa fa-share-alt"></i> Advertisement</a></li>
            </ul>
            <div class="tab-content">

              <div class="active tab-pane" id="v_overview">
<div class="row">
<div class="col-md-12">

<div class="table-responsive">
<table class="table table-bordered">
<tr>	<th width="170">Company Name</th>	<td><?php echo $cr['name']; ?></td>	</tr>
<tr>	<th>Company ID</th>	<td><?php echo $cr['id']; ?></td>	</tr>
<tr>	<th>Reg Date</th>	<td><?php echo date('m/d/Y', strtotime($cr['add_date'])); ?></td>	</tr>

<tr>	<th>Client Portal URL</th>	<td><a href="<?php echo base_url($cr['slug'] . "/account"); ?>" target="_blank"><?php echo base_url($cr['slug'] . "/account"); ?></a></td>	</tr>
<tr>	<th>Company Logo</th>	<td><?php if (file_exists($cr['logo'])) {echo '<img src="' . base_url($cr['logo']) . '" style="max-height:70px;" />';}?></td>	</tr>
<tr>	<th>Address 1</th>	<td><?php echo $cr['address']; ?></td>	</tr>
<tr>	<th>Address 2</th>	<td><?php echo $cr['address_2']; ?></td>	</tr>
<tr>	<th>City</th>	<td><?php echo $cr['city']; ?></td>	</tr>
<tr>	<th>State</th>	<td><?php echo $cr['state']; ?></td>	</tr>
<tr>	<th>Zip</th>	<td><?php echo $cr['zip_code']; ?></td>	</tr>

<tr>	<th>Main Office Phone Number</th>	<td><?php echo $cr['phone']; ?></td>	</tr>
<tr>	<th>Main Office Email</th>	<td><?php echo $cr['email']; ?></td>	</tr>
<tr>	<th>Auto-Request New NSLDS for client every</th>	<td><?php echo $cr['auto_request_new_nslds_for_client_every']; ?></td>	</tr>

<!-- <tr>	<th>Send intake reminder</th>	<td><?php //echo $cr['send_intake_reminder']; ?></td>	</tr>
<tr>	<th>Send schedule payment reminder</th>	<td><?php //echo $cr['send_schedule_payment_reminder']; ?></td>	</tr>
<tr>	<th>Send analysis follow up reminder</th>	<td><?php //echo $cr['send_analysis_follow_up_reminder']; ?></td>	</tr> -->
<tr>	<th>Analysis fee</th>	<td><?php echo $cr['analysis_fee']; ?></td>	</tr>
<tr>	<th>Payment link</th>	<td><a href="<?php echo $cr['payment_link']; ?>" target="_blank"><?php echo $cr['payment_link']; ?></a></td>	</tr>
<!-- <tr>	<th>Calendar link</th>	<td><a href="<?php echo $cr['calendar_link']; ?>" target="_blank"><?php echo $cr['calendar_link']; ?></a></td>	</tr> -->

</table>
</div>

</div>



</div>
              </div>

              <div class="tab-pane" id="v_smtp_email">
<div class="row">
<div class="col-md-12">

<div class="table-responsive">
<table class="table table-bordered">
<!-- <tr>	<th width="170">From Email</th>	<td><?php //echo $smtp['from_email']; ?></td>	</tr> -->
<!-- <tr>	<th>From Display</th>	<td><?php //echo $smtp['from_display']; ?></td>	</tr> -->
<tr>	<th>Reply To email</th>	<td><?php echo $smtp['reply_to_email']; ?></td>	</tr>
<tr>	<th>SMTP Hostname</th>	<td><?php echo $smtp['smtp_hostname']; ?></td>	</tr>
<tr>	<th>Outgoing SMTP Port</th>	<td><?php echo $smtp['smtp_outgoing_port']; ?></td>	</tr>
<tr>	<th>SMTP Security</th>	<td><?php echo $smtp['smtp_security']; ?></td>	</tr>
<tr>	<th>SMTP From Email</th>	<td><?php echo $smtp['smtp_from_email']; ?></td>	</tr>
<tr>	<th>SMTP Email Password</th>	<td>*******</td>	</tr>

</table>
</div>


</div>



</div>
              </div>
<div class="tab-pane" id="v_cusers">
<div class="table-responsive">
<table class="table table-bordered table-striped show_datatable">
        <thead>
        <tr class="info">
          <th width="1%">SNO</th>
          <th width="1%">ID</th>
          <th>Name</th>
          <th>Mobile</th>
          <th>Email</th>
          <th width="60">Reg Date</th>
          <th width="1%">View</th>
          <th width="1%">Delete</th>
        </tr>
        </thead>
        <tbody>
<?php
$sno = 0;
foreach ($data['company']['cm']['list'] as $row) {
	$arr_cm[$row->id] = array("name" => $row->name, "lname" => $row->lname, "phone" => $row->phone, "email" => $row->email);
	?>
<tr id="dtbl_<?php echo $row->id; ?>">
	<td><?php echo ++$sno; ?></td>
    <td><?php echo $row->id ?></td>
    <td><?php echo $row->name ?></td>
    <td><a href="tel:<?php echo $row->phone ?>"><?php echo $row->phone ?></a></td>
    <td><a href="mailto:<?php echo $row->email ?>"><?php echo $row->email ?></a></td>
    <td><?php echo date('m/d/Y', strtotime($row->add_date)); ?></td>
    <td><a href="<?php echo base_url('admin/company/view/' . $row->id) ?>" title="View" class="btn btn-sm btn-primary"><i class="fa fa-eye" aria-hidden="true"></i> View</a></td>
    <td><a title="Delete" href="<?php echo base_url('admin/delete/case_manager/' . $row->company_id . '/' . $row->id) ?>" class="btn btn-sm btn-danger" onClick="return confirm('Are you sure?')"><i class="fa fa-trash" aria-hidden="true"></i> Delete</a></td>
</tr>
<?php	}?>
        </tbody>

      </table>
</div>
</div>

<div class="tab-pane" id="v_clients">
<div class="table-responsive">
<table class="table table-bordered table-striped show_datatable">
        <thead>
        <tr class="info">
          <th width="1%">SNO</th>
          <th width="1%">ID</th>
          <th>Name</th>
          <th>Mobile</th>
          <th>Email</th>
          <th>Case Manager</th>
          <th width="60">Reg Date</th>
          <th width="1%">View</th>
          <th width="1%">Delete</th>
        </tr>
        </thead>
        <tbody>
<?php
$sno = 0;
foreach ($data['company']['clients']['list'] as $row) {
	?>
<tr id="dtbl_<?php echo $row->id; ?>">
	<td><?php echo ++$sno; ?></td>
    <td><?php echo $row->id ?></td>
    <td><?php echo $row->name . ' ' . $row->lname ?></td>
    <td><a href="tel:<?php echo $row->phone ?>"><?php echo $row->phone ?></a></td>
    <td><a href="mailto:<?php echo $row->email ?>"><?php echo $row->email ?></a></td>
    <td><?php	echo $arr_cm[$row->parent_id]['name'] . ' ' . $arr_cm[$row->parent_id]['lname']; ?></td>
    <td><?php echo date('m/d/Y', strtotime($row->add_date)); ?></td>
    <td><a href="<?php echo base_url('admin/company/view/' . $row->id) ?>" title="View" class="btn btn-sm btn-primary"><i class="fa fa-eye" aria-hidden="true"></i> View</a></td>
    <td><a title="Delete" href="<?php echo base_url('admin/delete/clients/' . $row->company_id . '/' . $row->id) ?>" class="btn btn-sm btn-danger" onClick="return confirm('Are you sure?')"><i class="fa fa-trash" aria-hidden="true"></i> Delete</a></td>
</tr>
<?php	}?>
        </tbody>

      </table>
</div>
</div>

<div class="tab-pane" id="v_program_reports">
<p style="padding:20px 0 150px 0;">Coming Soon..</p>
</div>

<div class="tab-pane" id="v_payment">
<p style="padding:20px 0 150px 0;">Coming Soon..</p>
</div>

<div class="tab-pane" id="v_advertisement">
<p style="padding:20px 0 150px 0;">Coming Soon..</p>
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

<?php	$this->load->view("Admin/inc/footer");?>

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
    $('.show_datatable').DataTable({
      "paging": true,
      "lengthChange": false,
	  "pageLength": 50,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": false
    });
  });
</script>
</body>
</html>
