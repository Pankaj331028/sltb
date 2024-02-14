<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php
if ($GLOBALS["loguser"]["role"] == "Company" || $GLOBALS["loguser"]["role"] == "Company User") {
	if ($GLOBALS["loguser"]["role"] == "Company") {$cndvar = $cmidvar = "company_id";} else {
		$cndvar = "added_by";
		$cmidvar = "company_id";}
	// if($GLOBALS["loguser"]["role"] == "Company")  { $cndvar = $cmidvar = "company_id"; } else {	$cndvar = "added_by"; $cmidvar = "parent_id";	}

	$arr_ids = array();
	$sql = "SELECT id FROM users where role='Customer' and $cmidvar='" . $GLOBALS["loguser"]["company_id"] . "' order by id desc";
	$q = $this->db->query($sql);
	foreach ($q->result() as $r) {$arr_ids[$r->id] = $r->id;}

	if (count($arr_ids) > 0) {
		$ttl_2 = 0;
		$ttl_3 = 0;
		$arr_id = implode(",", $arr_ids);
		$q = $this->db->query("SELECT * FROM program_definitions where 1 group by program_title order by program_title");
		foreach ($q->result() as $r) {
			$q1 = $this->db->query("select distinct(client_id) as client_id from client_program_progress where client_id in ($arr_id) and program_id_primary='" . $r->program_definition_id . "' and status='Pending' and step_due_date>='" . date('Y-m-d') . "'");
			$n_2 = $q1->num_rows();

			$q1 = $this->db->query("select distinct(client_id) as client_id from client_program_progress where client_id in ($arr_id) and program_id_primary='" . $r->program_definition_id . "' and status='Pending' and step_due_date<'" . date('Y-m-d') . "'");
			$n_3 = $q1->num_rows();

			$ttl_2 += $n_2;
			$ttl_3 += $n_3;

			?>
<tr>
	<td><a href="<?php echo base_url('account/programs/' . $r->program_definition_id) ?>"><?php echo $r->program_title ?></a></td>
    <td style="color:green;"><?php echo $n_2; ?></td>
    <td style="color:red;"><?php echo $n_3; ?></td>
    <td><?php echo ($n_2 + $n_3) ?></td>
</tr>
<?php	}?>


<tr class="info">
	<th>Totals</th>
    <th><?php echo $ttl_2 ?></th>
    <th><?php echo $ttl_3 ?></th>
    <th><?php echo ($ttl_2 + $ttl_3) ?></th>
</tr>

<?php	} else {?>
<tr>	<td colspan="5" style="padding:50px 5px;">You have no client.</td>	</tr>
<?php	}}?>