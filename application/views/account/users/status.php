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
      <h1><?php echo $data["name"]; ?></h1>
      <p><strong>Client:</strong> <?php echo $user['name']; ?> <?php echo $user['lname']; ?> (#<?php echo $user['id']; ?>)</p>
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
<li><a href="<?php echo base_url("account/customer/add_program/" . $this->uri->segment(4)) ?>"><i class="fa fa-file"></i> Programs</a></li>
<li class="active"><a href="<?php echo base_url("account/customer/status/" . $this->uri->segment(4)) ?>"><i class="fa fa-file-text-o"></i> View Client Status</a></li>
<li><a href="<?php echo base_url("account/customer/document/" . $this->uri->segment(4)) ?>"><i class="fa fa-upload"></i> Documents</a></li>
<li><a href="<?php echo base_url("account/customer/report/" . $this->uri->segment(4)) ?>"><i class="fa fa-line-chart"></i> View Reports</a></li>
<?php	} else {?>
              <li class="active"><a href=""><i class="fa fa-user-plus"></i> <?php echo $data["name"]; ?></a></li>
              <li><a href="<?php echo base_url("account/customer"); ?>"><i class="fa fa-users"></i> Clients</a></li>
<?php	}?>
            </ul>
            <div class="tab-content">

              <div class="active tab-pane" id="settings">
<?php	$this->load->view("template/alert.php");?>
<div class="table-responsive">
<table id="show_datatable" class="table table-bordered table-striped">
    <thead>
    <tr class="info">
      <!--<th width="1%">SNO</th>-->
      <th>Program Title</th>
      <th>Step</th>
      <th>Step Name</th>
      <th>Step Duration</th>
      <th>Due Date</th>
      <th>Complete Date</th>
      <th>Created Date</th>
      <th>Status</th>
      <th>Action</th>
    </tr>
    </thead>
    <tbody>
<?php

$q = $this->db->query("SELECT * FROM program_definitions where 1");
foreach ($q->result() as $r) {
	$arr_pr[$r->program_definition_id] = $r;
}

$sno = 0;
if ($GLOBALS["loguser"]["role"] == "Company") {$fld_name = "company_id";} else { $fld_name = "added_by";}
$query = $this->db->query("SELECT * FROM client_program_progress where $fld_name='" . $GLOBALS["loguser"]["id"] . "' and client_id='" . $this->uri->segment(4) . "' order by created_at desc");
foreach ($query->result() as $row) {
	?>
<tr id="dtbl_<?php echo $row->id; ?>">
<!--<td><?php echo ++$sno; ?></td>-->
<td><?php echo $arr_pr[$row->program_id]->program_title ?></td>
<td><?php echo $row->step_id ?></td>
<td><?php echo $arr_pr[$row->program_id]->step_name; ?></td>
<td><?php echo $arr_pr[$row->program_id]->step_duration; ?> Day</td>
<td><?php echo date('m/d/Y', strtotime($row->step_due_date)); ?></td>
<td><?php if ($row->step_completed_date != '') {echo date('m/d/Y', strtotime($row->step_completed_date));}?></td>
<td><?php echo date('m/d/Y', strtotime($row->created_at)); ?></td>
<td>
<?php	if ($row->step_completed_date != '') {?><span style="color:green; font-weight:bold;">Completed</span><?php	} else {?><span style="color:red; font-weight:bold;">Pending</span><?php	}?>
</td>
<td>
<?php	if ($row->step_completed_date == '') {?>
<a title="Completed Task" href="<?php echo base_url('account/customer/status/' . $row->client_id . '/complete/' . $row->program_definition_id) ?>" class="btn btn-sm btn-primary" onClick="return confirm('Are you sure?')"><i class="fa fa-check" aria-hidden="true"></i> Completed Task</a>
<?php	}?>
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
<?php	$this->load->view("account/inc/template_js.php");?>
</body>
</html>
