<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>

<?php

$user = $GLOBALS["loguser"];
$client_id = $GLOBALS["loguser"]["id"];

$arr_program_id = $this->array_model->arr_intake_program_id();
foreach ($arr_program_id as $program_id_primary => $intake_id) {

//	IDR Intake
	$intake2R = $this->default_model->get_arrby_tbl_single('client_program_progress', '*', "client_id='" . $client_id . "' and program_id_primary='$program_id_primary'", '1');
	if (isset($intake2R['program_id_primary'])) {
		$pdR = $this->default_model->get_arrby_tbl_single('program_definitions', '*', "program_definition_id='" . $intake2R['program_id'] . "'", '1');
		if ($pdR['send_intake'] != "" && $pdR['send_intake'] != 'NULL') {
			$iR = $this->default_model->get_arrby_tbl_single('intake', '*', "intake_id='$intake_id'", '1');
			$icsR = $this->default_model->get_arrby_tbl_single('intake_client_status', '*', "client_id='" . $client_id . "' and intake_id='$intake_id'", '1');
			if (!isset($icsR['status'])) {
				$this->db->insert("intake_client_status", ["client_id" => $client_id, "intake_id" => $intake_id, "add_date" => date("Y-m-d H:i:s"), "status" => "Pending"]);
			}
		}

	}
}

$company_slug = $client_data['users_company']['slug'];

echo '<li>';
if (isset($client_data['update_intake_client_status']['status'])) {

	if ($client_data['update_intake_client_status']['status'] == "Complete") {
		?>
<a href="<?php echo base_url($company_slug . "/intake/update"); ?>" style="background:#337ab7; color:#FFFFFF;"><i class="fa fa-check-square-o"></i> Update Intake</a>
<?php } else {?>
<a href="<?php echo base_url("account/update_intake_form?intake_page_no=1"); ?>"  style="background:#f39c12; color:#FFFFFF;"><i class="fa fa-file-text-o"></i> Update Intake</a>
<?php }} elseif (isset($client_data['intake_client_status']['status'])) {

	if ($client_data['intake_client_status']['status'] == "Complete") {
		?>
<a href="<?php echo base_url($company_slug . "/intake/initial"); ?>" style="background:#337ab7; color:#FFFFFF;"><i class="fa fa-check-square-o"></i> Initial Intake</a>
<?php } else {?>
<a href="<?php echo base_url("account/intake_form?intake_page_no=1"); ?>"  style="background:#f39c12; color:#FFFFFF;"><i class="fa fa-file-text-o"></i> Initial Intake</a>
<?php }} else {?>
<a href="<?php echo base_url("account/intake_form?intake_page_no=1"); ?>"  style="background:#f39c12; color:#FFFFFF;"><i class="fa fa-file-text-o"></i> Initial Intake</a>
<?php }
echo '</li>';?>



<?php if (isset($client_data['intake']['idr']['status'])) {
	echo '<li>';
	if ($client_data['intake']['idr']['status'] == "Complete") {
		?>
<a href="<?php echo base_url($company_slug . "/intake/idr"); ?>" style="background:#337ab7; color:#FFFFFF;"><i class="fa fa-check-square-o"></i> IDR Intake</a>
<?php } else {?>
<a href="<?php echo base_url($company_slug . '/idr_intake_form?intake_page_no=1'); ?>"  style="background:#f39c12; color:#FFFFFF;"><i class="fa fa-file-text-o"></i> IDR Intake</a>
<?php }
	echo '</li>';}?>



<?php if (isset($client_data['intake']['recertification']['status'])) {
	echo '<li>';
	if ($client_data['intake']['recertification']['status'] == "Complete") {
		?>
<a href="<?php echo base_url($company_slug . "/intake/recertification"); ?>" style="background:#337ab7; color:#FFFFFF;"><i class="fa fa-check-square-o"></i> Recertification Intake</a>
<?php } else {?>
<a href="<?php echo base_url($company_slug . '/recertification_intake_form?intake_page_no=1'); ?>"  style="background:#f39c12; color:#FFFFFF;"><i class="fa fa-file-text-o"></i> Recertification Intake</a>
<?php }
	echo '</li>';}?>



<?php if (isset($client_data['intake']['recalculation']['status'])) {
	echo '<li>';
	if ($client_data['intake']['recalculation']['status'] == "Complete") {
		?>
<a href="<?php echo base_url($company_slug . "/intake/recalculation"); ?>" style="background:#337ab7; color:#FFFFFF;"><i class="fa fa-check-square-o"></i> Recalculation Intake</a>
<?php } else {?>
<a href="<?php echo base_url($company_slug . '/recalculation_intake_form?intake_page_no=1'); ?>"  style="background:#f39c12; color:#FFFFFF;"><i class="fa fa-file-text-o"></i> Recalculation Intake</a>
<?php }
	echo '</li>';}?>



<?php if (isset($client_data['intake']['switch_idr']['status'])) {
	echo '<li>';
	if ($client_data['intake']['switch_idr']['status'] == "Complete") {
		?>
<a href="<?php echo base_url($company_slug . "/intake/switch_idr"); ?>" style="background:#337ab7; color:#FFFFFF;"><i class="fa fa-check-square-o"></i> Switch IDR Intake</a>
<?php } else {?>
<a href="<?php echo base_url($company_slug . '/switch_idr_intake_form?intake_page_no=1'); ?>"  style="background:#f39c12; color:#FFFFFF;"><i class="fa fa-file-text-o"></i> Switch IDR Intake</a>
<?php }
	echo '</li>';}?>


<?php if (isset($client_data['intake']['consolidation']['status'])) {
	echo '<li>';
	if ($client_data['intake']['consolidation']['status'] == "Complete") {
		?>
<a href="<?php echo base_url($company_slug . "/intake/consolidation"); ?>" style="background:#337ab7; color:#FFFFFF;"><i class="fa fa-check-square-o"></i> Consolidation Intake</a>
<?php } else {?>
<a href="<?php echo base_url($company_slug . '/consolidation_intake_form?intake_page_no=1'); ?>"  style="background:#f39c12; color:#FFFFFF;"><i class="fa fa-file-text-o"></i> Consolidation Intake</a>
<?php }
	echo '</li>';}?>



<?php
if (is_array($client_data['programs'])) {
	if (count($client_data['programs']) > 0) {
		?>
<!--<li <?php if ($this->uri->segment(2) == "program") {echo ' class="active"';}?>><a href="<?php echo base_url($company_slug . "/program"); ?>"><i class="fa fa-list-ul"></i> Programs</a></li>-->
<?php
}
}
?>