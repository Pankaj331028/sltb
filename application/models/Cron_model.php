<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Cron_model extends CI_Model {

function __construct(){
parent::__construct();
$this->load->helper('url');
$this->load->database();

$this->crm_model->get_site_settings();

}




//	Send Report to Case Manager
public function send_report_to_case_manager()
{
	$q = $this->db->query("select * from users where role='Company' or role='Company User'");
	foreach($q->result_array() as $row)
	{
		$this->account_model->add_case_manager_setting($row['id']);
	}
	
	$last_report_send = date('Y-m-d', strtotime(date('Y-m-d'). ' -1 days'));
	
	
	$q = $this->db->query("select * from users_cm_setting where last_report_send!='$last_report_send' order by rand() limit 500");
	foreach($q->result_array() as $row)
	{
		$cm_id = $row['id'];
		
		$this->db->query("update users_cm_setting set last_report_send='$last_report_send' where id='$cm_id'");
		
		$q = $this->db->query("select id,name,lname from users where parent_id='$cm_id'");
		$n = $q->num_rows();
		if($n > 0)
		{
			$tmp_arr = $client_name = array();
			foreach ($q->result_array() as $row)
			{
				$client_id = $row['id'];
				$tmp_arr[] = $row['id'];
				$client_name[$client_id] = $row['lname'].", ".$row['name'];
			}
			$tmp_ids = implode(",", $tmp_arr);
			
			$tr = '';
			
			$d1 = $last_report_send." 00:00:00";
			$d2 = $last_report_send." 23:59:59";
			$srch .= " and (sent_date between '$d1' and '$d2')";
			
			$q = $this->db->query("select * from client_reminder_status where (client_id in ($tmp_ids)) and (sent_date between '$d1' and '$d2')");
			$n = $q->num_rows();
			if($n > 0)
			{
				foreach($q->result_array() as $row)
				{
					$client_id = $row['client_id'];
					$tr .= '<tr> <td>'.$client_name[$client_id].'</td>	<td>'.$row['program_title'].'</td>	<td>'.$row['step_no'].'</td>	<td>'.$row['step_name'].'</td>	<td>'.date('m/d/Y',strtotime($row['due_date'])).'</td>	</tr>';
				}
				
				$q = $this->db->query("select * from users where id='$cm_id'");
				$cmr = $q->row_array();
				
				$cmpr = $this->crm_model->get_company_details($cmr['company_id']);
				$smtp_data = $this->crm_model->get_company_smtp_email_details($cmr['company_id']);
				
				
				$smtp_data['email'] = $cmr['email'];
				$smtp_data['subject'] = "Reminder Report - ".date("m/d/Y",strtotime($last_report_send));
				
				$smtp_data['Msg'] = $cmpr['email_header'].'<div style="font-family:Calibri; font-size:15px; width:100%; text-align:justify; padding:2px 15px;">
			<p>Dear '.$cmr['name'].' '.$cmr['lname'].'</p>
			<p>This email is to remind you that you have a task due.</p>
			<table cellpadding="5" cellspacing="0" border="1">
			<tr><th>Client Name</th>	<th>Program Name</th>	<th>Step Number</th>	<th>Step Name</th>	<th>Due Date</th>	</tr>
			'.$tr.'
			</table>
			<p>Regards,</p>
			<p>'.$cmpr['name'].'</p>
			</div>';
			
				$this->crm_model->send_email($smtp_data);
			}
		}
	}
}



//	Check Client Status
public function check_client_status()
{
	$arr_intake = $arr_program = array();
	$trintake_title = "";
	$q = $this->db->query("select * from intake where 1 order by intake_id asc");
	foreach($q->result_array() as $row)
	{
		$iid = $row['intake_id'];
		$program_definition_id = $row['program_definition_id'];
		
		$pr = $this->default_model->getRowArray("select * from program_definitions where program_definition_id='$program_definition_id'");
		$arr_intake[$iid] = $row;
		$arr_program[$program_definition_id] = $pr;
		$trintake_title .= '<td>'.$pr['program_title'].'</td>';		
	}
	
	
	$q = $this->db->query("select * from users_company where 1");
	foreach($q->result_array() as $row)
	{
		$company_id = $row['id'];
		$arr_company[$company_id] = $row;
	}
	
	
	
	$i = 1;
	$tr = '<table border="1" cellpadding="5" cellspacing="0">';
	$tr .= '<tr style="background:#337ab7; color:#FFFFFF; font-weight:bold;"><td>SNO</td> <td>COMPANY</td> <td>NAME</td> <td>EMAIL</td> <td>CLIENT ID</td> <td>Current Program</td> <td>NSLD Uploaded</td>	'.$trintake_title.' </tr>';
	$q = $this->db->query("select * from users where role='Customer' and (status2 is NULL or status2='') order by id asc limit 500000");
	foreach($q->result_array() as $row)
	{
		$client_id = $row['id'];
		$advertisement_id = $row['advertisement_id'];
		$sql = "select * from clients where client_id='$client_id'";
		if($this->default_model->getNumRows($sql) == 0) {	$this->default_model->dbInsert("clients", ["client_id"=>$client_id, "advertisement_id"=>$advertisement_id]);	}
		
		$clr = $this->default_model->getRowArray($sql);
		//echo $clr['client_id']."<br />";
		
		$trintake_title = '';
		foreach($arr_intake as $k=>$v)
		{
			$pd_id = $v['program_definition_id'];
			$n = $this->default_model->getNumRows("select * from client_program_progress where client_id='$client_id' and program_id_primary='".$pd_id."'");
			
			if($n == 0)
			{
				$trintake_title .= '<td style="background:#FF0000;">N/A</td>';
			}
			else
			{
				$trintake_title .= '<td style="background:green;">Available</td>';
				// Upadte Current Program
				$sql = "select * from client_program_progress where client_id='$client_id' and program_id_primary='".$pd_id."' order by step_id desc limit 1";
				$tmpr = $this->default_model->getRowArray($sql);
				$cpd = $tmpr['step_due_date'];
				$this->default_model->dbUpdate('clients', ["current_program"=>$pd_id, "current_program_date"=>$cpd], ["client_id"=>$client_id]);
			}
			
			if($pd_id == 91 && $n == 0)
			{
				$col_arr_tmp = array();
				
				
				$todaydate = $created_at = $row['add_date'];
				$added_by = $row['parent_id'];
				$company_id = $row['company_id'];
				//	Step 1
				$col_arr = array('added_by'=>$added_by, 'company_id'=>$company_id, 'program_id'=>91, 'program_id_primary'=>91, 'client_id'=>$client_id, 'step_id'=>'1', 'created_at'=>$created_at, 'step_due_date'=>$todaydate, 'step_completed_date'=>$todaydate, 'status'=>'Complete', 'status_1'=>'Complete');
				$cpprid = $this->default_model->dbInsert('client_program_progress', $col_arr);	//	Insert Client Program Process Record # Step 1
				
				//	Step 2
				$step_due_date = date('Y-m-d', strtotime($todaydate. ' + 14 days'));
				$col_arr = array('added_by'=>$added_by, 'company_id'=>$company_id, 'program_id'=>'92', 'program_id_primary'=>91, 'client_id'=>$client_id, 'step_id'=>'2', 'created_at'=>$created_at, 'step_due_date'=>$step_due_date, 'step_completed_date'=>$step_due_date, 'status'=>'Complete', 'status_1'=>'Complete');
				$col_arr['program_definition_id'] = $this->default_model->dbInsert('client_program_progress', $col_arr);	//	Insert Client Program Process Record # Step 2
				
				//	Step 3
				$created_at = $step_due_date;
				$step_due_date = date('Y-m-d', strtotime($step_due_date. ' + 3 days'));
				$col_arr = array('added_by'=>$added_by, 'company_id'=>$company_id, 'program_id'=>'93', 'program_id_primary'=>91, 'client_id'=>$client_id, 'step_id'=>'3', 'created_at'=>$created_at, 'step_due_date'=>$step_due_date, 'step_completed_date'=>$step_due_date, 'status'=>'Complete', 'status_1'=>'Complete');
				$col_arr['program_definition_id'] = $this->default_model->dbInsert('client_program_progress', $col_arr);	//	Insert Client Program Process Record # Step 3
				
				//	Step 4
				$created_at = $step_due_date;
				$step_due_date = date('Y-m-d', strtotime($step_due_date. ' + 7 days'));
				$col_arr = array('added_by'=>$added_by, 'company_id'=>$company_id, 'program_id'=>'94', 'program_id_primary'=>91, 'client_id'=>$client_id, 'step_id'=>'4', 'created_at'=>$created_at, 'step_due_date'=>$step_due_date, 'step_completed_date'=>$step_due_date, 'status'=>'Complete', 'status_1'=>'Complete');
				$col_arr['program_definition_id'] = $this->default_model->dbInsert('client_program_progress', $col_arr);	//	Insert Client Program Process Record # Step 4
				
				//	Step 5
				$created_at = $step_due_date;
				$step_due_date = date('Y-m-d', strtotime($step_due_date. ' + 2 days'));
				$col_arr = array('added_by'=>$added_by, 'company_id'=>$company_id, 'program_id'=>'96', 'program_id_primary'=>91, 'client_id'=>$client_id, 'step_id'=>'5', 'created_at'=>$created_at, 'step_due_date'=>$step_due_date, 'step_completed_date'=>$step_due_date, 'status'=>'Complete', 'status_1'=>'Complete');
				$col_arr['program_definition_id'] = $this->default_model->dbInsert('client_program_progress', $col_arr);	//	Insert Client Program Process Record # Step 5
				
			}
			
		}
		
		$nslds_file_upload_status = $this->crm_model->client_nslds_file_upload_status($client_id);
		if($nslds_file_upload_status == "Uploaded") { $nslds_status = '<td style="background:green;">Uploaded</td>'; } else { $nslds_status = '<td style="background:#FF0000;">N/A</td>'; }
		
		$company_id = $row['company_id'];
		$program_definition_id = $clr['current_program'];
		
		$tr .= '<tr><td>'.$i++.'</td> <td>'.$arr_company[$company_id]['name'].'</td> <td>'.$row['name'].' '.$row['lname'].'</td> <td>'.$row['email'].'</td> <td>'.$row['id'].'</td> <td>'.$arr_program[$program_definition_id]['program_title'].'</td>  '.$nslds_status.' '.$trintake_title.' </tr>';
	}
	
	$tr .= '</table>';
	echo $tr;	
}






//	Check Users Current Program Date
public function check_users_current_program_date()
{
	$q = $this->db->query("select client_id from clients where (current_program_date is NULL or current_program_date='') order by rand() limit 500");
	foreach($q->result_array() as $row)
	{
		$client_id = $row['client_id'];
		$this->account_model->update_users_current_program_date($client_id);
		echo $client_id."<br />";
	}
}




//	Check Intake Status Client
public function check_intake_status_client()
{
	//	Check from Intake
	$q = $this->db->query("select distinct(client_id) as client_id from intake_client_status where 1");
	foreach($q->result_array() as $row)
	{
		$client_id = $row['client_id'];
		$q = $this->db->query("select id,company_id from users where id='".$client_id."'");
		$cr = $q->row_array();
		if(!isset($cr['id'])) {	$this->db->query("delete from intake_client_status where client_id='".$client_id."'");	}
	}
	
	$tmp_arr = array("client_program_progress","reminder_rules", "reminder_history", "client_analysis_results");
	foreach($tmp_arr as $table)
	{
		echo '<hr /><strong>'.$table.'</strong><hr />';
		$q = $this->db->query("select distinct(client_id) as client_id, company_id from $table where 1");
		foreach($q->result_array() as $row)
		{
			$client_id = $row['client_id'];
			$company_id = $row['company_id'];
			echo $client_id." and ".$client_id."<br />";
			
			$q = $this->db->query("select id from users where id='".$client_id."'");
			$cr = $q->row_array();
			if(!isset($cr['id']))
			{
				echo "<hr />".$client_id." and ".$client_id."<hr />";
				//$this->admin_model->delete_customer($company_id, $client_id);
			}
		}
	}
}


//	Check Client Current Programs
public function check_client_current_program()
{
	$i = 1;
	$j = 1;
	echo '<table cellpadding="5" cellspacing="0" border="1"><tr><td>SNO</td><td>Not In Program</td> <td>Intake</td> <td>Status</td> <td>IDR</td> <td>Consolidation</td></tr>';
	$q = $this->db->query("select id,company_id,add_date from users where role='Customer' and (current_program  is NULL or current_program='') order by id asc");
	//$q = $this->db->query("select id,company_id,add_date from users where role='Customer' and id='9001681' order by id asc");
	foreach($q->result_array() as $row)
	{		
		$client_id = $row['id'];
		$company_id = $row['company_id'];
		$n = $this->default_model->getNumRows("select program_id_primary from client_program_progress where client_id='".$client_id."'");
		if($n == 0)
		{
			$q = $this->db->query("select * from intake_client_status where client_id='".$client_id."'");
			$icsr = $q->row_array();
			
			$n1 = $this->default_model->getNumRows("select * from intake_client_status where client_id='".$client_id."' and intake_id='1'");
			$n2 = $this->default_model->getNumRows("select * from intake_client_status where client_id='".$client_id."' and intake_id='2'");
			$n3 = $this->default_model->getNumRows("select * from intake_client_status where client_id='".$client_id."' and intake_id='3'");

$this->db->query("update users set current_program='91' where id='".$client_id."'");	//	Add to Intake Program

$step_due_date = date('Y-m-d', strtotime($row['add_date']. ' + 14 days'));
$col_arr_1 = array("added_by"=>$company_id, "company_id"=>$company_id, "program_id"=>91, "program_id_primary"=>91, "client_id"=>$client_id, "step_id"=>1, "step_due_date"=>$row['add_date'], "step_completed_date"=>$row['add_date'], "created_at"=>$row['add_date'], "status"=>"Complete");

$col_arr_2 = array("added_by"=>$company_id, "company_id"=>$company_id, "program_id"=>92, "program_id_primary"=>91, "client_id"=>$client_id, "step_id"=>2, "step_due_date"=>$step_due_date, "created_at"=>$row['add_date'], "status"=>"Pending");

$this->default_model->dbInsert("client_program_progress", $col_arr_1);	//	Add Step 1
$this->default_model->dbInsert("client_program_progress", $col_arr_2);	//	Add Step 2


if($icsr['status'] == "Complete")
{
	
$this->db->query("update client_program_progress set step_completed_date='".$row['add_date']."',status='Complete' where client_id='$client_id' and program_id='92' and step_id='2'");

	$step_due_date = date('Y-m-d', strtotime($row['add_date']. ' + 3 days'));
	$col_arr_3 = array("added_by"=>$company_id, "company_id"=>$company_id, "program_id"=>93, "program_id_primary"=>91, "client_id"=>$client_id, "step_id"=>3, "step_due_date"=>$step_due_date, "created_at"=>$row['add_date'], "status"=>"Pending");
	
	$q = $this->db->query("select * from client_analysis_results where client_id='".$client_id."'");
	$car = $q->row_array();
	
	if(isset($car['id']))
	{
		$col_arr_3['step_due_date'] = date('Y-m-d', strtotime($car['created_at']. ' + 3 days'));
		
		if($car['par_csd'] == "We can help you")
		{
			$col_arr_3['step_completed_date'] = $car['created_at'];
			$col_arr_3['status'] = "Complete";
			
			$step_due_date = date('Y-m-d', strtotime($row['step_completed_date']. ' + 7 days'));
			$step4 = "Yes";
			
		} else if($car['par_csd'] == "We can not assist you")
		{
			$col_arr_3['step_completed_date'] = $car['created_at'];
			$col_arr_3['status'] = $col_arr_3['status_1'] = "Stop";
			$this->db->query("update client_program_progress set status_1='Stop' where client_id='".$client_id."' and program_id_primary='91'");
		}
	}
	
	
	$this->default_model->dbInsert("client_program_progress", $col_arr_3);	//	Add Step 3
	
	if(isset($step4))
	{
		$qry = $this->db->query("select * from client_program_progress where client_id='$client_id' and step_id='3' and program_id_primary='91' limit 1");
		$row2 = $qry->row_array();
		if(isset($row2['program_definition_id']))
		{	
			$this->db->query("update client_program_progress set step_completed_date='',status='Pending' where program_definition_id='".$row2['program_definition_id']."'");
			$this->crm_model->admin_users_add_program_step($client_id, $row2['program_definition_id']);
		}
	}
	
}
			echo '<tr><td>'.$i++.'</td><td>'.$client_id.'</td> <td>'.$n1.'</td> <td>'.$icsr['status'].'</td> <td>'.$n2.'</td> <td>'.$n3.'</td></tr>';
		}
		else
		{
			
		}
	}
	echo '</table>';
}


//	Set client current program
public function set_client_current_program()
{
	$q = $this->db->query("select id,parent_id,company_id,add_date from users where (current_program  is NULL or current_program='')");
	foreach($q->result_array() as $row)
	{
		$client_id = $row['id'];
		$company_id = $row['company_id'];
		$added_by = $row['parent_id'];
		$q2 = $this->db->query("select program_id_primary from client_program_progress where client_id='".$row['id']."' order by program_definition_id desc limit 1");
		$cppr = $q2->row_array();
		if(isset($cppr['program_id_primary']))
		{
			$q3 = $this->db->query("select program_id_primary from client_program_progress where client_id='".$row['id']."' and program_id_primary='91' order by program_definition_id desc limit 1");
			$cppr2 = $q3->row_array();
			if(isset($cppr2['program_id_primary']))
			{
				echo "Found &&&& ";
			}
			else
			{	
				$q4 = $this->db->query("select program_id_primary from client_program_progress where client_id='".$row['id']."' and program_id_primary!='91' order by program_definition_id desc limit 1");
				$chkn = $q4->row_array();
				if($chkn == 0)
				{
					echo "Not Found Incorrect &&&& ";
				}
				else
				{
					$todaydate = $created_at = $row['add_date'];
					
					//	Step 1
					$col_arr = array('added_by'=>$added_by, 'company_id'=>$company_id, 'program_id'=>91, 'program_id_primary'=>91, 'client_id'=>$client_id, 'step_id'=>'1', 'created_at'=>$created_at, 'step_due_date'=>$todaydate, 'step_completed_date'=>$todaydate, 'status'=>'Complete', 'status_1'=>'Complete');
					$cpprid = $this->default_model->dbInsert('client_program_progress', $col_arr);	//	Insert Client Program Process Record # Step 1
					
					//	Step 2
					$step_due_date = date('Y-m-d', strtotime($todaydate. ' + 14 days'));
					$col_arr = array('added_by'=>$added_by, 'company_id'=>$company_id, 'program_id'=>'92', 'program_id_primary'=>91, 'client_id'=>$client_id, 'step_id'=>'2', 'created_at'=>$created_at, 'step_due_date'=>$step_due_date, 'step_completed_date'=>$step_due_date, 'status'=>'Complete', 'status_1'=>'Complete');
					$col_arr['program_definition_id'] = $this->default_model->dbInsert('client_program_progress', $col_arr);	//	Insert Client Program Process Record # Step 2
					
					//	Step 3
					$created_at = $step_due_date;
					$step_due_date = date('Y-m-d', strtotime($step_due_date. ' + 3 days'));
					$col_arr = array('added_by'=>$added_by, 'company_id'=>$company_id, 'program_id'=>'93', 'program_id_primary'=>91, 'client_id'=>$client_id, 'step_id'=>'3', 'created_at'=>$created_at, 'step_due_date'=>$step_due_date, 'step_completed_date'=>$step_due_date, 'status'=>'Complete', 'status_1'=>'Complete');
					$col_arr['program_definition_id'] = $this->default_model->dbInsert('client_program_progress', $col_arr);	//	Insert Client Program Process Record # Step 3
					
					//	Step 4
					$created_at = $step_due_date;
					$step_due_date = date('Y-m-d', strtotime($step_due_date. ' + 7 days'));
					$col_arr = array('added_by'=>$added_by, 'company_id'=>$company_id, 'program_id'=>'94', 'program_id_primary'=>91, 'client_id'=>$client_id, 'step_id'=>'4', 'created_at'=>$created_at, 'step_due_date'=>$step_due_date, 'step_completed_date'=>$step_due_date, 'status'=>'Complete', 'status_1'=>'Complete');
					$col_arr['program_definition_id'] = $this->default_model->dbInsert('client_program_progress', $col_arr);	//	Insert Client Program Process Record # Step 4
					
					//	Step 5
					$created_at = $step_due_date;
					$step_due_date = date('Y-m-d', strtotime($step_due_date. ' + 2 days'));
					$col_arr = array('added_by'=>$added_by, 'company_id'=>$company_id, 'program_id'=>'96', 'program_id_primary'=>91, 'client_id'=>$client_id, 'step_id'=>'5', 'created_at'=>$created_at, 'step_due_date'=>$step_due_date, 'step_completed_date'=>$step_due_date, 'status'=>'Complete', 'status_1'=>'Complete');
					$col_arr['program_definition_id'] = $this->default_model->dbInsert('client_program_progress', $col_arr);	//	Insert Client Program Process Record # Step 5
					
				}
			}
			
			echo $client_id."<br />";
			
			//$this->db->query("update users set current_program='".$cppr['program_id_primary']."' where id='".$row['id']."'");
		}
		else
		{
			
		}
	}
}







}
?>