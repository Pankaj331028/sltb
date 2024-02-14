<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Attestation_model extends Crm_model {

	function __construct() {
		parent::__construct();
		$this->load->helper('url');
		$this->load->model('crm_model');
		$this->load->database();

		$this->get_site_settings();

	}

	public function clear_result($query) {
		$query->next_result();
		$query->free_result();
	}

	//	Get Site Settings
	public function get_site_settings() {
		$query = $this->db->query("CALL sp_get_settings()");
		$GLOBALS["settings"] = $query->row_array();
		$this->clear_result($query);
	}

	//	Get Company Details
	public function get_company_details($company_id) {
		$query = $this->db->query("CALL sp_get_company_details(?)", [$company_id]);
		$res = $query->row_array();
		$this->clear_result($query);

		return $res;
	}

	public function get_client_programs($client_id = '') {
		$res = array();
		$q = $this->db->query("CALL sp_get_client_programs(?)", [$client_id]);
		foreach ($q->result_array() as $row) {
			$program_definition_id = $row['program_definition_id'];
			$res[$program_definition_id] = $row;
		}
		$this->clear_result($q);
		return $res;
	}

	public function get_client_details($client_id = 0) {
		$return = array();
		//	Client Data
		$q = $this->db->query("CALL sp_get_client_details(?)", [$client_id]);
		$return['client'] = $cr = $q->row_array();
		$this->clear_result($q);
		if (isset($return['client']['id'])) {
			//	Company
			$q = $this->db->query("CALL sp_get_client_details(?)", [$cr['company_id']]);
			$return['company'] = $cmr = $q->row_array();
			$this->clear_result($q);

			//	Company
			$return['users_company'] = $this->get_company_details($cr['company_id']);

			//	Case Manager
			if ($cr['parent_id'] == 0) {$parent_id = $cr['company_id'];} else { $parent_id = $cr['parent_id'];}
			$q = $this->db->query("CALL sp_get_client_details(?)", [$parent_id]);
			$return['case_manager'] = $cmr = $q->row_array();
			$this->clear_result($q);

			//	Documents
			$q = $this->db->query("CALL sp_get_client_documents(?)", [$client_id]);
			$return['documents'] = $q->row_array();
			$this->clear_result($q);

			/*$return['intake_client_status'] = $this->client_intake_client_status($client_id, "1");
				$return['intake']['idr'] = $this->client_intake_client_status($client_id, "2");
				$return['intake']['consolidation'] = $this->client_intake_client_status($client_id, "3");
			*/

			$return['programs'] = $this->get_client_programs($client_id);
		}
		return $return;
	}

	//	Get Client NSLDS File Upload Status
	public function client_nslds_file_upload_status($client_id = '') {
		$file_data = "";
		$file_status = 'File not found';
		$query = $this->db->query('CALL sp_get_client_nslds_status(?)', [$client_id]);
		$res = $query->row_array();

		$nslds_file_error = "";
		if (isset($res['intake_file_id'])) {
			$client_document = $this->document_decrypt($res['intake_file_location']);
			if (file_exists($client_document)) {
				$file_data = read_file($client_document);
				$file_status = "Uploaded";
				if (trim($file_data) == "") {$file_status = 'File not found';}
			} else { $file_status = 'File not found';}
		}
		return $file_status;
	}

	public function get_client_attestation_form_results($client_id) {
		$res = array();
		$res['client_data'] = $this->get_client_details($client_id);

		if (isset($res['client_data']['client']['id'])) {
			$company_id = $res['client_data']['client']['company_id'];
			$query = $this->db->query("CALL sp_get_client_attestation_result(?,?)", [$client_id, $company_id]);
			$res['car'] = $query->row_array();
			$this->clear_result($query);
			$query = $this->db->query("CALL sp_get_client_attestation_loans(?,?)", [$client_id, $res['car']['id']]);
			$res['loans'] = $query->result_array();
			$this->clear_result($query);
			$query = $this->db->query("CALL sp_get_client_program_progress(?)", [$client_id]);
			$res['cpp'] = $query->row_array();
			$this->clear_result($query);
		}
		return $res;
	}

	public function save_attestation($client_id, $data, $loans, $attest, $action = '') {
		$res = array();
		$company_id = 0;
		$client_data = $this->get_client_details($client_id);
		$attest_id = isset($attest['id']) ? $attest['id'] : 0;

		if (isset($client_data['client']['id'])) {
			$company_id = $client_data['client']['company_id'];
		}

		$q = $this->db->query('CALL sp_save_client_attestation_result(?,?,?,?,?)', [$company_id, $client_id, $data, $attest_id, $action]);
		$res['data_save'] = $q->row_array();
		$this->clear_result($q);
		$res['loan_saved'] = 0;

		$this->db->query('delete from client_attestation_loan_info where client_attestation_id = ' . $res['data_save']['id']);
		if (isset($res['data_save']['id']) && count($loans) > 0) {
			foreach ($loans['loan_name'] as $key => $value) {

				$q = $this->db->query('CALL sp_save_client_attestation_loans(?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [$res['data_save']['id'], $client_id, $value, $loans['loan_type'][$key], $loans['loan_amount'][$key], $loans['monthly_payment'][$key], $loans['date_of_payoff'][$key], $loans['date_of_default'][$key], $loans['school_attended'][$key], $loans['degree_pursued'][$key], $loans['specialization'][$key], $loans['date_school_completed'][$key], $loans['type_of_degree'][$key], $loans['date_studies_ceased'][$key]]);
				$this->clear_result($q);
				$res['loan_saved'] += 1;
			}
		}
		return $res;
	}

	public function approve_attestation($client_id, $attest_id) {
		$client_data = $this->get_client_details($client_id);
		$res = [];

		if (isset($client_data['client']['id'])) {
			$company_id = $client_data['client']['company_id'];
			$q = $this->db->query('CALL sp_approve_attestation(?,?,?,?)', [$attest_id, $client_id, $company_id, date('Y-m-d')]);
			$res = $q->row_array();
			$this->clear_result($q);

			if (isset($res['program_definition_id'])) {
				$step_start_date = date('Y-m-d');
				$step_due_date = date('Y-m-d', strtotime($step_start_date . ' + ' . $res['step_duration'] . ' days'));
				$ddtr = $this->crm_model->calculate_weekends($step_start_date, $step_due_date);
				if ($ddtr['weekenddays'] > 0) {
					$step_start_date = $step_due_date;
					$step_due_date = date('Y-m-d', strtotime($step_start_date . ' + ' . $ddtr['weekenddays'] . ' days'));
					$ddtr = $this->crm_model->calculate_weekends($step_start_date, $step_due_date);

					if ($ddtr['weekenddays'] > 0) {
						$step_start_date = $step_due_date;
						$step_due_date = date('Y-m-d', strtotime($step_start_date . ' + ' . $ddtr['weekenddays'] . ' days'));
						$ddtr = $this->crm_model->calculate_weekends($step_start_date, $step_due_date);

						if ($ddtr['weekenddays'] > 0) {
							$step_start_date = $step_due_date;
							$step_due_date = date('Y-m-d', strtotime($step_start_date . ' + ' . $ddtr['weekenddays'] . ' days'));
							$ddtr = $this->crm_model->calculate_weekends($step_start_date, $step_due_date);

						}
					}
				}
				$q = $this->db->query('CALL sp_add_program_step(?,?,?,?,?,?)', [$client_id, $step_due_date, $company_id, $res['program_definition_id'], 97, 16]);
			}
		}
		return $res;
	}

}
?>