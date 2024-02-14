<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Intake_model extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->load->helper('url');
		$this->load->database();

		$this->crm_model->get_site_settings();

	}

//	Add Intake Question
	public function add_intake_question($client_id = 0, $intake_id = 1) {
		$rows = $this->default_model->get_arrby_tbl('intake_question', '*', "intake_id='" . $intake_id . "' order by placement_order asc", '500');
		foreach ($rows as $row) {
			$this->crm_model->admin_intake_answer_by_client($client_id, $row['intake_question_id']);
		}
	}

//	Client analysis results for data confirmation
	public function get_client_analysis_results_for_data_confirmation($client_id = 0) {
		$redirect = "No";
		$sg_1 = $this->uri->segment(1);
		$sg_4 = $this->uri->segment(4);
		$cr = $this->default_model->getRowArray("select * from users where id='$client_id'");
		if (isset($cr['id'])) {
			$ics = $this->default_model->getRowArray("select * from intake_client_status where client_id='$client_id' and id='$sg_4'");
			if (isset($ics['id'])) {
				$intake_id = $ics['intake_id'];
				foreach ($this->array_model->arr_intake_program_id() as $k => $v) {if ($v == $intake_id) {$program_id_primary = $k;}}

				$intkR = $this->default_model->getRowArray("SELECT * FROM intake where intake_id='$intake_id'");
				$cpp = $this->default_model->getRowArray("SELECT * FROM client_program_progress where client_id='$client_id' and program_id_primary='$program_id_primary' and step_id='6' limit 1");
				$program_definition_id = $cpp['program_definition_id'];
				$program_id_primary = $cpp['program_id_primary'];

			} else { $redirect = "Yes";}
		} else { $redirect = "Yes";}

		//	Redirect if Something wrong
		if ($redirect == "Yes") {
			redirect($sg_1 . "/account");
			exit;
		}

	}

//	Update Intake Answer
	public function dbUpdateIntakeAnswer($client_id = 0, $intake_question_id = 0, $col_arr = array()) {
		$q = $this->db->query("SELECT * FROM intake_question where intake_question_id='$intake_question_id'");
		$queR = $q->row_array();

		if ($queR['intake_question_type'] == 'Radio' || $queR['intake_question_type'] == 'Radio Group' || $queR['intake_question_type'] == 'Checkbox') {$table_name = "intake_answer_result";}
		if ($queR['intake_question_type'] == 'Comment' || $queR['intake_question_type'] == 'Table') {$table_name = "intake_comment_result";}
		if ($queR['intake_question_type'] == 'File') {$table_name = "intake_file_result";}

		$condition = array("client_id" => $client_id, "intake_question_id" => $intake_question_id);
		$this->default_model->dbUpdate($table_name, $col_arr, $condition);
	}

//	Customer Stop Intake Notification
	public function stop_intake_reminder() {
		$seg_4 = $this->uri->segment(4);
		$seg_4 = base64_decode(strtr($seg_4, '-_', '+/'));
		$arr = explode(".", $seg_4);
		$client_id = $arr[0];
		$intake_id = $arr[1];
		$ics_id = $arr[2];

		$icsR = $this->default_model->getRowArray("SELECT * FROM intake_client_status where client_id='" . $client_id . "' and intake_id='" . $intake_id . "' and id='" . $ics_id . "' limit 1");
		if (isset($icsR['id']) && isset($arr[3]) && isset($arr[4])) {
			$this->db->query("update intake_client_status set last_sent_reminder='2050-12-12' where id='" . $icsR['id'] . "'");

			$program_id = $arr[3];
			$step_id = $arr[4];
			$this->db->query("update reminder_rules set status_flag='0' where client_id='" . $client_id . "' and step_id='" . $step_id . "' and program_id='" . $program_id . "'");
			$this->db->query("update client_program_progress set reminder_status=0 where client_id='" . $client_id . "' and step_id='" . $step_id . "' and program_id='" . $program_id . "'");

			$cppR = $this->default_model->getRowArray("SELECT * FROM client_program_progress where client_id='" . $client_id . "' and program_id='" . $program_id . "' limit 1");
			$this->db->query("update client_program_progress set step_completed_date='" . date('Y-m-d') . "', status='Stop' where program_definition_id='" . $cppR['program_definition_id'] . "'");
			$this->db->query("update client_program_progress set status_1='Stop' where client_id='" . $client_id . "' and program_id_primary='" . $cppR['program_id_primary'] . "'");

			$this->db->query("update client_program set status='Stop' where client_id='" . $client_id . "' and program_definition_id='" . $cppR['program_id_primary'] . "'");

			$error_type = "Success";
			$msg = $this->account_model->stop_reminder_confirmation_email($client_id); // Stop Reminder Confirmation Email
			//$msg = 'Intake reminder notification successfully stoped now.';
			//$this->session->set_flashdata('success', $msg);
		} else {
			$error_type = "Error";
			$msg = 'Invalid link';
			//$this->session->set_flashdata('error', $msg);
		}

		$page_data = array("error_type" => $error_type, "msg" => $msg);
		return $page_data;
		//redirect(base_url($this->uri->segment(1)."/account"));
		//exit;
	}

}
?>