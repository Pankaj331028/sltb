<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php
error_reporting(0);
@extract($_POST);

$role = $GLOBALS["loguser"]["role"];
if ($role == "Company") {$company_id = $GLOBALS["loguser"]["id"];} elseif ($role == "Company User") {$company_id = $GLOBALS["loguser"]["company_id"];} else { $company_id = "";}

$advr = $this->default_model->get_arrby_tbl('users_advertisement', '*', "company_id='" . $company_id . "' and id='" . $this->uri->segment(4) . "'", '1');
$advr = $advr["0"];
if (!isset($advr['id'])) {redirect(base_url("account/advertisement"));exit;}
@extract($advr);

$q = $this->db->query("SELECT * FROM users_company where id='" . $advr['company_id'] . "'");
$cmpR = $q->row_array();

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
      <h1><strong><?php echo $name; ?></strong></h1>
      <p><a href="<?php echo base_url($cmpR['slug'] . "/client_registration/" . $advr['code']); ?>" target="_blank"><?php echo base_url($cmpR['slug'] . "/client_registration/" . $advr['code']); ?></a></p>
    </section>

    <!-- Main content -->
    <section class="content">

      <div class="row">
        <!-- /.col -->
        <div class="col-md-12">
          <div class="nav-tabs-custom">

            <div class="tab-content">

              <div class="active tab-pane" id="settings">
<p><a href="<?php echo base_url("account/advertisement"); ?>"><i class="fa fa-long-arrow-left"></i> <strong>Back to Advertisement</strong></a></p>

<?php	$this->load->view("template/alert.php");?>


<div class="table-responsive">
<table id="show_datatable" class="table table-bordered">
        <thead>
        <tr class="info">
          <th width="1%">SNO</th>
          <th>Client Details</th>
          <?php	if ($GLOBALS["loguser"]["role"] == "Company") {?><th>Case Manager Details</th><?php	}?>
          <th width="100">View</th>
          <th width="60">Reg Date</th>
        </tr>
        </thead>
        <tbody>
<?php
$sno = 0;
$cnd = "role='Customer' and advertisement_id='" . $advr['id'] . "'";
$role = $GLOBALS["loguser"]["role"];
if ($role == "Company") {
	$cnd .= " and company_id='" . $GLOBALS["loguser"]["id"] . "'";

	$q = $this->db->query("SELECT * FROM users where (role='Company User' or role='Company') and (parent_id='" . $GLOBALS["loguser"]["id"] . "' or company_id='" . $GLOBALS["loguser"]["id"] . "') order by id desc");
	foreach ($q->result() as $r) {
		$arr_cm_name[$r->id] = $r->name;
		$arr_cm_phone[$r->id] = $r->phone;
		$arr_cm_email[$r->id] = $r->email;
	}
}

if ($role == "Company User") {$cnd .= " and company_id='" . $GLOBALS["loguser"]["company_id"] . "'";}
// if($role=="Company User") { $cnd .= " and parent_id='".$GLOBALS["loguser"]["id"]."'"; }
if ($role == "Company") {$company_id = $GLOBALS["loguser"]["id"];} elseif ($role == "Company User") {$company_id = $GLOBALS["loguser"]["company_id"];} else { $company_id = "";}

$sql = "SELECT * FROM users where $cnd order by id desc";
$query = $this->db->query($sql);
$rows = $query->result();
$tmp_cl_ids = array();
foreach ($rows as $row) {$tmp_cl_ids[] = $row->id;}

foreach ($rows as $row) {
	$client_id = $row->id;
	if ($row->parent_id == 0) {$row->parent_id = $row->company_id;}
	?>
<tr>
	<td><?php echo ++$sno; ?></td>

    <td>
		<span><i class="fa fa-user"></i> &nbsp; <?php echo $row->lname ?>, <?php echo $row->name ?></span><br />
        <span><i class="fa fa-phone"></i> &nbsp; <a href="tel:<?php echo $row->phone; ?>"><?php echo $row->phone; ?></a></span><br />
        <span><i class="fa fa-envelope-o"></i> &nbsp; <a href="mailto:<?php echo strtolower($row->email); ?>"><?php echo strtolower($row->email); ?></a></span>
    </td>

    <?php	if ($GLOBALS["loguser"]["role"] == "Company") {?>
    <td>
		<span><i class="fa fa-user"></i> &nbsp; <?php echo $arr_cm_name[$row->parent_id]; ?></span><br />
        <span><i class="fa fa-phone"></i> &nbsp; <a href="tel:<?php echo $arr_cm_phone[$row->parent_id] ?>"><?php echo $arr_cm_phone[$row->parent_id] ?></a></span><br />
        <span><i class="fa fa-envelope-o"></i> &nbsp; <a href="mailto:<?php echo $arr_cm_email[$row->parent_id] ?>"><?php echo $arr_cm_email[$row->parent_id] ?></a></span>
    </td>
	<?php	}?>

    <td><a href="<?php echo base_url('account/customer/view/' . $row->id) ?>" title="View" class="btn btn-sm btn-primary"><i class="fa fa-eye" aria-hidden="true"></i> View Client</a></td>

    <td><?php echo date('m/d/Y', strtotime($row->add_date)) ?></td>

</tr>
<?php	}?>
        </tbody>

      </table>
</div>


<div class="clr"></div>





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

<script type="text/javascript">

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
