<?php	defined('BASEPATH') OR exit('No direct script access allowed');
error_reporting(E_ALL ^ E_NOTICE);
extract($_POST);
extract($_GET);

class Integration extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->model(array('integration_model'));

		if (isset($_GET['log'])) {$this->session->set_userdata('userid', $_GET['log']);}

		@extract($_POST);
		if ($this->session->userdata('userid')) {
			$GLOBALS["loguser"] = $this->crm_model->get_login_user($this->session->userdata('userid'));
			$this->crm_model->validate_company_profile_status();

			//$this->crm_model->check_company_payment($GLOBALS["loguser"]["id"]);
		}

		$this->crm_model->create_company_slug();

	}

	//	Login default Page
	public function index() {
		$this->check_login_session(); // Check Login Session
		if ($GLOBALS["loguser"]["role"] != "Company") {redirect("account");exit;}
		$page_data = array();

		$page_data['partners'] = $this->integration_model->get_partners();
		$page_data['company_integrations'] = $this->integration_model->company_integrations($GLOBALS["loguser"]["id"]);
		$page_data['data']['name'] = "Integration Details";
		$page_data['data']['meta_title'] = "Integration Details";

		if (isset($_POST['Submit'])) {

			$this->form_validation->set_rules('partner_id', 'Partner', 'required'); //callback_check_partner
			$this->form_validation->set_rules('partner_account_id', 'Partner Account ID', 'required');

			$this->form_validation->set_rules('partner_account_login', 'Partner Account Login', 'required');
			$this->form_validation->set_rules('partner_account_pswd', 'Partner Account Password', 'required');

			if ($this->form_validation->run() == FALSE) {
				$this->session->set_flashdata('error', validation_errors());
			} else {
				$result = $this->integration_model->insert_integration($_POST);
				if (!$result) {
					$this->session->set_flashdata('error', 'Something went wrong. Please try again later or contact administrator');
				} else {
					$this->session->set_flashdata('success', 'Integration details saved successfully');
					redirect(base_url('integration'));
				}
			}
		}

		$this->load->view('integrations/index', $page_data);
	}

	//	Check Mobile Number Validation
	/*public function check_partner($partner) {

		$role = $GLOBALS["loguser"]["role"];
		if ($role == "Company") {$company_id = $GLOBALS["loguser"]["id"];} elseif ($role == "Company User") {$company_id = $GLOBALS["loguser"]["company_id"];} else { $company_id = "";}

		$res = array();
		$query = $this->db->query("CALL sp_check_company_partner(?,?)", [$company_id, $partner]);
		$res = $query->row_array();
		$this->integration_model->clear_result($query);

		if ($res['rowcount'] == ($_POST['type'] == 'add' ? 0 : 1)) {
			return true;
		} else {
			$this->form_validation->set_message('check_partner', "You have already added your details with this partner.");
			return false;
		}
	}
	*/

	//	Check Login Session
	public function check_login_session() {
		if (!$GLOBALS["loguser"]["id"]) {
			$cmpr = $this->crm_model->get_company_details($this->uri->segment(1));
			$this->session->set_userdata('redirect', current_url());
			if (isset($cmpr['id'])) {
				redirect(base_url($cmpr['slug'] . "/client_login"));
			} else {
				redirect(base_url("login"));
			}
			exit;
		}
	}

}
