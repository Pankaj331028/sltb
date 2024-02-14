<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Integration_model extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->load->helper('url');
		$this->load->database();

		$this->crm_model->get_site_settings();

	}

	public function clear_result($query) {
		$query->next_result();
		$query->free_result();
	}

	public function company_integrations($company_id) {
		$res = array();
		$query = $this->db->query("CALL sp_get_company_integrations(?)", [$company_id]);
		$res = $query->result_array();
		$this->clear_result($query);

		return $res;
	}

	public function insert_integration($data) {

		$role = $GLOBALS["loguser"]["role"];
		if ($role == "Company") {$company_id = $GLOBALS["loguser"]["id"];} elseif ($role == "Company User") {$company_id = $GLOBALS["loguser"]["company_id"];} else { $company_id = "";}

		$query = $this->db->query("CALL sp_insert_update_company_integrations(?,?,?,?,?)", [$company_id, $data['partner_id'], $data['partner_account_id'], $data['partner_account_login'], $data['partner_account_pswd']]);

		$this->clear_result($query);

		return true;
	}

	public function get_partners() {
		$res = array();
		$query = $this->db->query("CALL sp_get_partners()");
		$res = $query->result_array();
		$arr = ['' => 'Select Partner'];

		foreach ($res as $row) {
			$arr[$row['partner_code']] = $row['partner_name'];
		}

		$this->clear_result($query);

		return $arr;
	}

}
?>