<?php	defined('BASEPATH') OR exit('No direct script access allowed');	?>
<!DOCTYPE html>
<html>
<head>
<?php	$this->load->view("account/inc/head");	?>
</head>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
<?php
$hc_data['client_data'] = $client_data;
$this->load->view("Site/inc/header_client", $hc_data['client_data']);

$client_id = $GLOBALS["loguser"]["id"];
$company_slug = $client_data['users_company']['slug'];


if(is_array($client_data['programs']))
{
	if(count($client_data['programs'])>0)
	{
		foreach($client_data['programs'] as $row)
		{
			$program_definition_id = $row['program_definition_id'];
			$q = $this->db->query("select * from program_definitions where program_definition_id='".$program_definition_id."' limit 1");
			$arr_programs['list'][$program_definition_id] = $q->row_array();
			
			$q2 = $this->db->query("select * from client_program_progress where client_id='$client_id' and program_id_primary='".$program_definition_id."' order by step_id desc");
			$arr_programs['steps'][$program_definition_id] = $q2->result();
		}
	}
}

if(!is_array($arr_programs)) {	redirect(base_url($company_slug."/program/")); exit;	}



$arr_intake_program_id = $this->array_model->arr_intake_program_id();

$q = $this->db->query("SELECT * FROM intake_client_status where client_id='$client_id'  order by id desc");
foreach($q->result_array() as $res)
{
	$intake_id = $res['intake_id'];
	$arr_ics[$intake_id] = $res;
}




$q = $this->db->query("SELECT * FROM program_definitions where 1");
foreach ($q->result() as $r) 
{
	$arr_pr[$r->program_definition_id] = $r;
}


?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
<!-- Content Header (Page header) -->

<!-- Main content -->
<section class="content">
<?php	@extract($client_data['client']);	?>

<h2><strong>Programs</strong></h2>
<p><a href="<?php echo base_url($company_slug."/account"); ?>" class="btn btn-primary">Back</a></p>
<div><?php	$this->load->view("template/alert.php");	?></div>


<div class="nav-tabs-custom">
<ul class="nav nav-tabs">
<?php
$i=1;
$seg_3 = $this->uri->segment(3);
foreach($arr_programs['list'] as $row)
{
	$pdi = $row['program_definition_id'];	
	if($pdi == $seg_3)
	{
		$active_tab_id = $pdi;
		$cls_1 = ' class="active"';
		$aria_expanded = 'true';
	} else if($i == 1 && trim($seg_3)=="")
	{
		$active_tab_id = $pdi;
		$cls_1 = ' class="active"';
		$aria_expanded = 'true';
	} else {
		$cls_1 = '';
		$aria_expanded = 'false';
	}
?>
<li <?php	echo $cls_1;	?>>
	<a href="#tab_<?php echo $pdi; ?>" data-toggle="tab" aria-expanded="<?php echo $aria_expanded; ?>"><i class="fa fa-list"></i> <?php echo $row['program_title']; ?></a>
</li>
<?php $i++;	} ?>
</ul>

<div class="tab-content" style="padding:0px;">
<?php
foreach($arr_programs['list'] as $res)
{
	$program_definition_id = $res['program_definition_id'];
	if($res['program_definition_id'] == $active_tab_id)
	{
		$cls_1 = ' class="active tab-pane"';
	}else {
		$cls_1 = ' class="tab-pane"';
	}
?>
<div <?php echo $cls_1; ?> id="tab_<?php echo $res['program_definition_id']; ?>">
<div class="table-responsive">
<table class="table table-bordered show_datatable">
    <thead>
    <tr class="info" style="text-transform:uppercase;">
      <th width="1%">Step</th>
      <th>Step Name</th>
      <th width="120">Step Duration</th>
      <th width="110">Due Date</th>
      <th width="120">Complete Date</th>
      <th width="1%">Status</th>
    </tr>
    </thead>
    <tbody>
<?php
foreach ($arr_programs['steps'][$program_definition_id] as $row)
{
?>
<tr id="dtbl_<?php echo $row->program_definition_id; ?>">
<td><?php echo $row->step_id; ?></td>
<td><?php echo $arr_pr[$row->program_id]->step_name; ?></td>
<td><?php echo $arr_pr[$row->program_id]->step_duration; ?> Day</td>
<td><?php echo date('m/d/Y',strtotime($row->step_due_date)); ?></td>
<td><?php if($row->step_completed_date!='') { echo date('m/d/Y',strtotime($row->step_completed_date)); }	?></td>
<td>
<?php	if(trim($row->step_completed_date)!='' && $row->status=='Complete') {	?><span style="color:green; font-weight:bold;">Completed</span><?php	} else if($row->status=='Stop') {	?><span style="color:red; font-weight:bold;">Stop</span><?php	} else  {	?><span style="color:yellow; font-weight:bold;">Pending</span><?php	}	?>
</td>

</tr>
<?php	}	?>
    </tbody>
  </table>
</div>
<div class="clr"></div>
</div>
<?php } ?>
</div>
</div>

  <!-- /.row -->
</section>
<!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php	$this->load->view("account/inc/footer");	?>

</div>
<?php	$this->load->view("account/inc/template_js.php");	?>

</body>
</html>
