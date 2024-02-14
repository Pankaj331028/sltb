<?php	defined('BASEPATH') OR exit('No direct script access allowed');	?>
<?php	error_reporting(0);	?>
<!DOCTYPE html>
<html>
<head>
<?php	$this->load->view("account/inc/head");	?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php	$this->load->view("account/inc/header");	?>
<?php	$this->load->view("account/inc/leftnav");	?>


<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><?php echo $data["name"]; ?></h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo base_url('account/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?php echo base_url('account/document'); ?>">Document</a></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">


      <div class="row">
        <!-- /.col -->
        <div class="col-md-12">
          <div class="nav-tabs-custom">
<ul class="nav nav-tabs">
  <li class="active"><a href="<?php echo base_url("account/document"); ?>"><i class="fa fa-file-pdf-o"></i> Document</a></li>
  <li><a href="<?php echo base_url("account/document/new"); ?>"><i class="fa fa-upload"></i> Upload Document</a></li>
</ul>
            <div class="tab-content">
              
              <div class="active tab-pane" id="settings">
<?php	$this->load->view("template/alert.php");	?>
<div class="table-responsive">
<table id="show_datatable" class="table table-bordered table-striped">
        <thead>
        <tr class="info">
          <th width="1%">SNO</th>
          <th>Document</th>
          <th>Upload by</th>
		  <?php	if($GLOBALS["loguser"]["role"] == "Company") {	?><th>Company User</th>	<th>Client</th><?php	}	?>
          <?php	if($GLOBALS["loguser"]["role"] == "Company User") {	?><th>Client</th><?php	}	?>
          <th>Upload Date</th>
          <th>Download Date</th>
        </tr>
        </thead>
        <tbody>
<?php
$sno = 0;
$logid = $GLOBALS["loguser"]["id"];

$q = $this->db->query("SELECT * FROM users where 1 order by id desc");
foreach ($q->result() as $r) {	$arr_users[$r->id] = $r->name;	}	

$cnd = " added_by='$logid' or company_id='$logid' or client_manager='$logid' or client_id='$logid'";
$query = $this->db->query("SELECT * FROM client_documents where $cnd order by document_id  desc limit 10000");
foreach ($query->result() as $row) {
?>
<tr id="dtbl_<?php echo $row->id; ?>">
	<td><?php echo ++$sno; ?></td>
    <td>
		<?php echo $row->document_name; ?><br />
        <a href="<?php echo base_url('account/document/view/'.$row->document_id)?>" title="View Document" class="btn btn-sm btn-primary" target="_blank"><i class="fa fa-link" aria-hidden="true"></i> View</a>
        <?php	if($row->downloaded_date=="" && $row->added_by == $logid) {	?> &nbsp; <a title="Delete Document" href="<?php echo base_url('account/document/delete/'.$row->document_id)?>" class="btn btn-sm btn-danger" onClick="return confirm('Are you sure?')"><i class="fa fa-trash" aria-hidden="true"></i> Delete</a><?php	}	?>
    </td>
    <td><?php echo $arr_users[$row->added_by]; ?></td>
	<?php	if($GLOBALS["loguser"]["role"] == "Company") {	?><td><?php echo $arr_users[$row->client_manager]; ?></td><td><?php echo $arr_users[$row->client_id]; ?></td><?php	}	?>
    <?php	if($GLOBALS["loguser"]["role"] == "Company User") {	?><td><?php echo $arr_users[$row->client_id]; ?></td><?php	}	?>
    <td><?php echo date('m/d/Y',strtotime($row->uploaded_date)); ?></td>
    <td><?php if($row->downloaded_date!="") { echo date('m/d/Y',strtotime($row->downloaded_date)); } else {	echo '<span style="color:red;">Not Download</span>';	} ?></td>
    
</tr>
<?php	}	?>
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

<?php	$this->load->view("account/inc/footer");	?>

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
