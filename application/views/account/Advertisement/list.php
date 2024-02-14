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


<div class="content-wrapper"style="background:#FFFFFF;">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><strong><?php echo $data["name"]; ?></strong>
      	<a href="<?php echo base_url("account/advertisement/new"); ?>" class="btn btn-primary pull-right"><i class="fa fa-user-plus"></i> New Advertisement</a>
      </h1>
    </section>

    <!-- Main content -->
    <section class="content">


      <div class="row">
        <div class="col-md-12">
          <?php	$this->load->view("template/alert.php");?>
<div class="table-responsive">
<table id="show_datatable" class="table table-bordered">
        <thead>
        <tr class="info">
          <th width="1%">SNO</th>
          <th width="100">Action</th>
          <th>TITLE</th>
          <th width="150">Client Registration</th>
          <th width="100">Create Date</th>
        </tr>
        </thead>
        <tbody>
<?php
$sno = 0;

$role = $GLOBALS["loguser"]["role"];
if ($role == "Company") {$company_id = $GLOBALS["loguser"]["id"];} else if ($role == "Company User") {$company_id = $GLOBALS["loguser"]["company_id"];} else { $company_id = "";}

$q = $this->db->query("SELECT * FROM users_company where id='$company_id'");
$cmpR = $q->row_array();

$query = $this->db->query("SELECT * FROM users_advertisement where company_id='$company_id' order by id desc");
$rows = $query->result();
foreach ($rows as $row) {
	$q = $this->db->query("SELECT * FROM users where company_id='" . $row->company_id . "' and role='Customer' and advertisement_id='" . $row->id . "' order by id desc");
	$nr = $q->num_rows();
	?>
<tr>
	<td><?php echo ++$sno; ?></td>
    <td>
    <a href="<?php echo base_url('account/advertisement/view/' . $row->id) ?>" title="View" class="btn btn-sm btn-primary"><i class="fa fa-eye" aria-hidden="true"></i></a> &nbsp;
    <a href="<?php echo base_url('account/advertisement/edit/' . $row->id) ?>" title="Edit" class="btn btn-sm btn-info"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a> &nbsp;
    <?php if ($nr == 0) {?><a href="<?php echo base_url('account/advertisement/delete/' . $row->id) ?>" title="Delete" class="btn btn-sm btn-danger" onClick="return confirm('Are you sure?')"><i class="fa fa-trash" aria-hidden="true"></i></a><?php }?>
    </td>
    <td>
    <?php echo $row->name; ?><br />
    <a href="<?php echo base_url($cmpR['slug'] . "/client_registration/" . $row->code); ?>" target="_blank"><?php echo base_url($cmpR['slug'] . "/client_registration/" . $row->code); ?></a> &nbsp;&nbsp;&nbsp;
    <button title="Copy Link" class="btn btn-sm btn-primary" onclick="copyClipboard('<?php echo base_url($cmpR['slug'] . "/client_registration/" . $row->code); ?>')">Copy Link</button>
    </td>
    <td><a href="<?php echo base_url('account/advertisement/view/' . $row->id) ?>"><?php echo $nr; ?> Client</a></td>

    <td><?php echo date('m/d/Y', strtotime($row->add_date)) ?></td>

</tr>
<?php	}?>
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
function copyClipboard(text) {
  navigator.clipboard.writeText(text);

  // Alert the copied text
  alert("Copied Link: " + text);
}
function send_intake_email(uid){
  $.post("<?php	echo base_url("account/send_intake_email"); ?>", {uid: uid}, function(result){
    if(result == "Failed") {	swal( 'Failed', 'Something went wrong.', 'error');	} else {	swal( 'Success!', 'Intake email successfully sent.', 'success');	}
  });
}


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
