<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Programs_model extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->load->helper('url');
		$this->load->database();

		$this->crm_model->get_site_settings();

	}

//	Check Intake Program
	public function stop_program() {
		$client_id = $this->uri->segment(4);
		$program_id_primary = $this->uri->segment(5);
		$status = $_POST['status'];

		if ($GLOBALS["loguser"]["role"] == "Company") {$cndvar = "company_id";} else { $cndvar = "company_id";}
		// if ($GLOBALS["loguser"]["role"] == "Company") {$cndvar = "company_id";} else { $cndvar = "parent_id";}
		$user = $this->default_model->get_arrby_tbl('users', '*', "role='Customer' and $cndvar='" . $GLOBALS["loguser"]["company_id"] . "' and id='" . $client_id . "'", '1');
		$user = $user["0"];
		if (!isset($user['id'])) {redirect(base_url("account/customer"));exit;}

		//	Update Stage
		$this->db->where(["client_id" => $client_id, "program_id_primary" => $program_id_primary, "status" => "Pending"]);
		$this->db->update('client_program_progress', ['status' => $status, 'step_completed_date' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d')]);

		//	Update client_program Status
		$this->db->where(["client_id" => $client_id, "program_definition_id" => $program_id_primary]);
		$this->db->update('client_program', ['status' => $status]);
	}

//	No Further Action
	public function program_nfa($client_id = 0, $program_definition_id = 0, $program_id_primary = 0) {
		$uri_7 = $this->uri->segment(7);
		$arr_tmp = array("nfa" => "No Further Action", "dnc" => "Do not Continue", "cwsap" => "Continue without selecting a Program");
		$status = $arr_tmp[$uri_7];

		//	Update Status Flag
		//$this->db->where(["client_program_progress_id"=>$program_definition_id]);
		$this->db->where(["client_id" => $client_id]);
		$this->db->update('reminder_rules', ['status_flag' => '0']);

		//	Update Stage
		$this->db->where(["program_definition_id" => $program_definition_id]);
		$this->db->update('client_program_progress', ['status' => "Complete", 'step_completed_date' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d')]);

		$q = $this->db->query("SELECT * FROM client_program_progress where program_definition_id='$program_definition_id'");
		$col_arr = $q->row_array();

		unset($col_arr['program_definition_id']);
		$col_arr['added_by'] = $GLOBALS["loguser"]["id"];
		$col_arr['program_id'] = $col_arr['program_id'] + 1;
		$col_arr['step_id'] = $col_arr['step_id'] + 1;
		$col_arr['status'] = $status;
		$col_arr['updated_at'] = date('Y-m-d H:i:s');
		$col_arr['step_due_date'] = $col_arr['step_completed_date'] = date('Y-m-d');

		$this->db->insert('client_program_progress', $col_arr); //	Insert Record

		//	Update client_program Status
		$this->db->where(["client_id" => $client_id, "program_definition_id" => $program_id_primary]);
		$this->db->update('client_program', ['status' => $status]);
	}

//	Get Client Attestation Form Result Data
	public function get_client_attestation_form_results($client_id = '') {
		$res = array();
		$res['client_data'] = $this->crm_model->get_client_full_details($client_id);

		if (isset($res['client_data']['client']['id'])) {
			$company_id = $res['client_data']['client']['company_id'];
			$car = $this->default_model->get_arrby_tbl_single('client_attestation', '*', "client_id='$client_id'", '1');
			if (!isset($car['id'])) {
				$cppr = $this->default_model->get_arrby_tbl_single('client_program_progress', '*', "client_id='$client_id' and program_id_primary='97' and step_id='15'", '1');
				if (isset($cppr['program_definition_id'])) {
					$col_arr = array("company_id" => $company_id, "client_id" => $client_id, "status" => "Pending");
					$this->db->insert('client_attestation', $col_arr);
				}
			}
			$res['car'] = $this->default_model->get_arrby_tbl_single('client_attestation', '*', "client_id='$client_id'", '1');
		}
		return $res;
	}

//	Get program reports list
	public function get_program_reports_list($type = "Current") {
		$role_log = $GLOBALS["loguser"]["role"];
		$id_log = $GLOBALS["loguser"]["company_id"];
		// $id_log = $GLOBALS["loguser"]["id"];
		$seg_1 = $this->uri->segment(1);
		$program_definition_id = $this->uri->segment(3);

		if ($role_log == "Company") {$cmidvar = "company_id";} else { $cmidvar = "company_id";}
		// if ($role_log == "Company") {$cmidvar = "company_id";} else { $cmidvar = "parent_id";}

		$arr_ids = array();
		$sql = "SELECT id FROM users where role='Customer' and $cmidvar='" . $id_log . "' order by id desc";
		$q = $this->db->query($sql);
		foreach ($q->result() as $r) {$arr_ids[$r->id] = $r->id;}

		if (count($arr_ids) > 0) {
			$arr_id = implode(",", $arr_ids);

			$prgr = $this->default_model->get_arrby_tbl('program_definitions', '*', "program_definition_id ='" . $program_definition_id . "'", '1');
			$prgr = $prgr["0"];
			if (!isset($prgr['program_definition_id'])) {redirect(base_url($seg_1 . "/dashboard"));exit;}
			@extract($prgr);

			//	Get Program Steps Title
			$str = $this->default_model->get_arrby_tbl('program_definitions', '*', "program_title ='" . $prgr['program_title'] . "' order by step_id asc", '500');
			foreach ($str as $k => $v) {
				$id = $v['program_definition_id'];
				$stepr[$id] = $v;
				$step_id = $v['step_id'];
				$stepr_by_step[$step_id] = $v;}
			$last_step_id = $step_id;
			$last_program_id = $id;

			//	Get Company Users List
			if ($role_log == "Company") {
				$q = $this->db->query("SELECT * FROM users where (role='Company User' or role='Company') and (parent_id='" . $id_log . "' or company_id='" . $id_log . "') order by id desc");
				foreach ($q->result() as $r) {$arr_cm[$r->id] = array("name" => $r->name, "lname" => $r->lname, "phone" => $r->phone, "email" => $r->email);}
			}

			$arr_cpp = array();
			$arr_cpp_cids = $arr_cpp_cids_tmp = array();

			if ($role_log == "Company") {$cnd_1 = " and company_id='" . $id_log . "'";} else { $cnd_1 = " and added_by='" . $id_log . "'";}

			$cnd = "client_id in ($arr_id) and status='Pending' and program_id_primary='" . $prgr['program_definition_id'] . "'";
			if ($type == "Late") {$cnd .= " and step_due_date<'" . date('Y-m-d') . "'";} else { $cnd .= " and step_due_date>='" . date('Y-m-d') . "'";}

			$q2 = $this->db->query("select distinct(client_id) as client_id from client_program_progress where $cnd order by step_id desc");
			foreach ($q2->result() as $r2) {
				$arr_cpp_cids_tmp[] = $r2->client_id;
				$cnd2 = "program_id_primary='" . $prgr['program_definition_id'] . "' and client_id='" . $r2->client_id . "' and status_1='Pending'";
				$q = $this->db->query("select * from client_program_progress where $cnd2 order by step_id asc");
				foreach ($q->result() as $r) {
					$arr_cpp[$r->client_id]['step_id'] = $r->step_id;
					$arr_cpp[$r->client_id][$r->step_id] = $r;
					$arr_cpp_cids[$r->client_id] = $r->client_id;
				}
			}

			//	Clients List
			if (count($arr_cpp_cids) > 0) {
				$arr_client = array();
				$q = $this->db->query("SELECT * FROM users where role='Customer' and id in (" . implode(",", $arr_cpp_cids) . ") order by id desc");
				foreach ($q->result() as $r) {$arr_client[$r->id] = $r;}
			}

			array_unique($arr_cpp_cids_tmp);
			$res = array("prgr" => $prgr, "stepr" => $stepr, "stepr_by_step" => $stepr_by_step, "arr_cm" => $arr_cm, "arr_client" => $arr_client, "arr_cpp_cids_tmp" => $arr_cpp_cids_tmp, "arr_cpp" => $arr_cpp);
			return $res;
		}

	}

//	Get program reports list
	public function get_client_program_reports_list($client_id = "") {
		if ($client_id != "") {
			$q = $this->db->query("SELECT * FROM program_definitions where 1");
			foreach ($q->result() as $r) {$arr_pr[$r->program_definition_id] = $r;}

			$q2 = $this->db->query("select distinct(program_id_primary) as pid from client_program_progress where client_id='$client_id' and step_id='1' order by step_id asc");
			foreach ($q2->result() as $r2) {
				$q3 = $this->db->query("select * from client_program_progress where client_id='$client_id' and program_id_primary='$r2->pid' order by step_id desc");
				$q4 = $this->db->query("select * from intake where program_definition_id='$r2->pid'");
				$ir = $q4->row();
				$q5 = $this->db->query("select * from intake_client_status where client_id='$client_id' and intake_id='$ir->intake_id'");
				$q6 = $this->db->query("select * from client_analysis_results where client_id='$client_id' and intake_id='$ir->intake_id'");

				$arr_program[$r2->pid] = array("id" => $r2->pid, "program_title" => $arr_pr[$r2->pid]->program_title, "list" => $q3->result());
				$arr_program[$r2->pid]["intake"] = $ir;
				$arr_program[$r2->pid]["ics"] = $q5->row();
				$arr_program[$r2->pid]["car"] = $q6->row();
			}

			$res = array("arr_pr" => $arr_pr, "arr_program" => $arr_program);

		} else { $res = array();}

		return $res;
	}

//	Check Intake Program
	public function check_intake_program($client_id = "", $program_id = "") {
		if ($client_id != "" && $program_id != "") {
			if ($program_id != '91') {
				$sql = "update client_program_progress set step_completed_date='" . date('Y-m-d H:i:s') . "', updated_at='" . date('Y-m-d') . "', status='Select Program and add the Client' where client_id='$client_id' and program_id_primary='91' and status='Pending'";
				$this->db->query($sql);

				$this->db->query("update client_program_progress set status_1='Complete' where client_id='$client_id' and program_id_primary='91'");

				$q = $this->db->query("SELECT * FROM client_program_progress where client_id='$client_id' and program_id_primary='91' and step_id='6'");
				if ($q->num_rows() == 0) {
					$q = $this->db->query("SELECT * FROM client_program_progress where client_id='$client_id' and program_id_primary='91' and step_id='5'");
					$col_arr = $q->row_array();

					unset($col_arr['program_definition_id']);
					$col_arr['added_by'] = $GLOBALS["loguser"]["id"];
					$col_arr['company_id'] = $GLOBALS["loguser"]["id"];
					$col_arr['client_id'] = $client_id;
					$col_arr['program_id'] = $col_arr['program_id'] + 1;
					$col_arr['step_id'] = $col_arr['step_id'] + 1;
					$col_arr['status'] = "Complete";
					$col_arr['updated_at'] = date('Y-m-d H:i:s');
					$col_arr['step_due_date'] = $col_arr['step_completed_date'] = date('Y-m-d');

					$this->db->insert('client_program_progress', $col_arr); //	Insert Record
				}

			}
		}
	}

	public function get_client_program($client_id = "", $program_id = "") {
		return $this->default_model->getRowArray("SELECT * FROM client_program where client_id='$client_id' and program_definition_id='$program_id' limit 1");
	}

//	Check Intake Program
	public function add_client_to_current_program($client_id = "") {
		$q = $this->db->query("select program_id_primary,step_due_date from client_program_progress where client_id='" . $client_id . "' order by program_definition_id desc limit 1");
		$cppr = $q->row_array();
		$this->db->query("update clients set current_program='" . $cppr['program_id_primary'] . "', current_program_date='" . $cppr['step_due_date'] . "' where client_id='" . $client_id . "'");
	}

//	Check Intake Program
	public function add_intake_program($client_id = "", $program_id = "", $intake_id = "") {
		$program_id = !empty($program_id) ? $program_id : 91;
		$intake_id = !empty($intake_id) ? $intake_id : 1;
		$cr = $this->default_model->get_client($client_id);
		if (isset($cr['id'])) {
			$cm_id = $cr['parent_id'];
			$company_id = $cr['company_id'];
			$cmpR = $this->default_model->get_company($company_id);
			if (isset($cmpR['id'])) {
				$cmR = $this->default_model->get_cm($cm_id);
				$cpR = $this->get_client_program($client_id, $program_id);
				if (!isset($cpR['id'])) {
					if (isset($GLOBALS["loguser"]["id"])) {$added_by = $GLOBALS["loguser"]["id"];} else { $added_by = $company_id;}
					$cprid = $this->default_model->dbInsert('client_program', ['client_id' => $client_id, 'program_definition_id' => $program_id]); //	Insert Record

					$todaydate = date("Y-m-d");

					$col_arr = array('client_id' => $client_id, 'intake_id' => $intake_id);
					$icsid = $this->default_model->dbInsert('intake_client_status', $col_arr); //	Insert Client intake_client_status

					$col_arr = array('added_by' => $added_by, 'company_id' => $company_id, 'program_id' => $program_id, 'program_id_primary' => $program_id, 'client_id' => $client_id, 'step_id' => '1', 'step_due_date' => $todaydate, 'step_completed_date' => $todaydate, 'status' => 'Complete');
					$cpprid = $this->default_model->dbInsert('client_program_progress', $col_arr); //	Insert Client Program Process Record # Step 1

					$step_due_date = date('Y-m-d', strtotime($todaydate . ' + 14 days'));
					$col_arr = array('added_by' => $added_by, 'company_id' => $company_id, 'program_id' => '92', 'program_id_primary' => $program_id, 'client_id' => $client_id, 'step_id' => '2', 'step_due_date' => $step_due_date, 'status' => 'Pending');
					$col_arr['program_definition_id'] = $this->default_model->dbInsert('client_program_progress', $col_arr); //	Insert Client Program Process Record # Step 2

					$this->set_intake_reminder_rules($cr, $cmpR, $cmR, $program_id, $col_arr);
				}
			}
		}
	}

//	Set Intake Reminder
	public function set_intake_reminder_rules($cr = array(), $cmpR = array(), $cmr = array(), $program_id_primary = "", $data = array()) {
		$client_id = $cr['id'];
		$reminder_date_from = $data['step_due_date'];
		$reminder_date_from = date("Y-m-d");
		//	Case Manager
		$case_manager_name = $cmr['name'] . " " . $cmr['lname'];
		$case_manager_email = $cmr['email'];
		$case_manager_phone = $cmr['phone'];

		$prgmr = $this->default_model->getRowArray("SELECT * FROM program_definitions where program_definition_id='" . $data['program_id'] . "' limit 1");
		$ir = $this->default_model->getRowArray("SELECT * FROM intake where program_definition_id='" . $data['program_id_primary'] . "' limit 1");
		$icsR = $this->default_model->getRowArray("SELECT * FROM intake_client_status where client_id='" . $client_id . "' and intake_id='" . $ir['intake_id'] . "' limit 1");

		$cl_ec_id = $cr['id'] . "." . $ir['intake_id'] . "." . $icsR['id'] . "." . $data['program_id'] . "." . $prgmr['step_id'];

		$cl_ec = strtr(base64_encode($cl_ec_id), '+/', '-_');
		$srl = base_url($cmpR['slug'] . "/" . $ir['intake_slug'] . "/stop/" . $cl_ec);
		$stop_reminder_link = '<a href="' . $srl . '" style="background:#FF0000; color:#FFFFFF; padding:5px 20px; font-weight:bold; text-decoration:none;">STOP REMINDER</a>';
		$intake_link = base_url($cmpR['slug'] . "/" . $ir['intake_slug']);

		$reminder_email_subject = 'Your Free Student Loan Review - Reminder';
		$reminder_email_body = $cmpR['email_header'] . '<p>Hello ' . $cr['name'] . ' ' . $cr['lname'] . ',</p>
	<p>We want to remind you that you need to complete two simple steps to receive your Free student loan review.</p>
	<ol>
	<li>1. Complete your intake by going to <a href="' . $intake_link . '">' . $intake_link . '</a></li>
	<li>Upload your <a href="https://studentaid.gov">https://studentaid.gov</a> file which you need to download in txt format.</li>
	</ol>
	<p>Once you complete these steps, we will review your specific details and follow up with you. We endeavor to respond within 2 business days, but this may vary from time to time so please be patient.</p>
	<p>If you have decided not to continue with us at this time, Please click this link to stop any further reminders.</p>
	<p>' . $stop_reminder_link . '</p>
	<p>Regards,<br />' . $cmpR['name'] . '</p>';

		$col_arr_rr = ['client_program_progress_id' => $data['program_definition_id'], 'program_id' => $data['program_id'], 'step_id' => $prgmr['step_id'], 'company_id' => $cmpR['id'], 'client_id' => $cr['id'], 'days_to_send' => $prgmr['step_duration'], 'reminder_email_subject' => $reminder_email_subject, 'reminder_email_body' => $reminder_email_body, 'status_flag' => '1', 'to_whom' => '0', 'sent_to' => $cr['email'], 'reminder_date_from' => $reminder_date_from];
		$this->default_model->dbInsert('reminder_rules', $col_arr_rr); //	Insert Client intake_client_status

	}

}
?>