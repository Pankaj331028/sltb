<?php	defined('BASEPATH') OR exit('No direct script access allowed');
error_reporting(E_ALL ^ E_NOTICE);
extract($_POST);
extract($_GET);

class Customer extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->output->set_header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT');
		$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		$this->output->set_header('Pragma: no-cache');
		$this->output->set_header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		$this->load->database();

		$this->load->model(array('cron_model', 'attestation_model'));
		$this->load->library('stripe_lib');

		if (isset($_GET['log'])) {$this->session->set_userdata('userid', $_GET['log']);}

		@extract($_POST);
		if ($this->session->userdata('userid')) {
			$GLOBALS["loguser"] = $this->crm_model->get_login_user($this->session->userdata('userid'));
			$this->crm_model->validate_company_profile_status();

			//$this->crm_model->check_company_payment($GLOBALS["loguser"]["id"]);
		}

		$this->crm_model->create_company_slug();

		if ($GLOBALS["loguser"]["role"] != "Customer") {
			redirect(base_url("account/dashboard"));
			exit;
		}

	}

	//	Login default Page
	public function index() {
		$seg_1 = $this->uri->segment(1);
		if ($this->session->userdata('userid')) {redirect(base_url($seg_1 . '/dashboard'));} else {redirect(base_url($seg_1 . '/login'));}

	}

	//	Login default Page
	public function account() {
		$seg_1 = $this->uri->segment(1);
		if ($this->session->userdata('userid')) {redirect(base_url($seg_1 . '/dashboard'));} else {redirect(base_url($seg_1 . '/client_login'));}
	}

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

	//	Subscription Notification
	function subscription_notification() {
		$this->load->view('account/profile/subscription_notification');
	}

	//	Forgot Password Page
	public function fp() {
		if ($this->session->userdata('userid')) {redirect(base_url('account/dashboard'));}

		/* Set validation rule for name field in the form */
		$this->form_validation->set_rules('email', 'Email', 'required');

		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('error', validation_errors());
		} else {
			$result = $this->crm_model->admin_fp();
			if (!isset($result['id'])) {
				$this->session->set_flashdata('error', 'Invalid Email.');
			} else {
				$this->session->set_flashdata('success', 'A new password was successfully sent to your email.');
				redirect(base_url('account/login'));}
		}

		$page_data = array();
		$page_data['data']['name'] = "Forgot Password";
		$page_data['data']['seo_title'] = "Forgot Password";
		//$this->load->view('account/fp',$page_data);
		$this->load->view('Site/login/fp', $page_data);
	}

	//	Dashboard
	public function dashboard() {
		$this->check_login_session(); // Check Login Session
		$this->crm_model->check_account_segment_and_redirect("account", "dashboard"); // Check Account Segment and Redirect

		@extract($_POST);
		@extract($_GET);
		error_reporting(E_ALL ^ E_NOTICE);

		$page_data = array();
		$page_data['data']['name'] = "Dashboard";
		$page_data['data']['meta_title'] = "Dashboard";
		if ($GLOBALS["loguser"]["role"] == "Customer") {
			$page_data['client_data'] = $this->crm_model->get_client_full_details($GLOBALS["loguser"]["id"]);
			$this->load->view('account/Client/dashboard', $page_data);
		} else {
			$this->load->view('account/dashboard', $page_data);
		}
	}

	//	Logout
	public function logout() {
		$cmpR = $this->crm_model->get_company_details($GLOBALS["loguser"]["company_id"]);
		//$this->session->set_userdata('userid', '');
		$this->session->unset_userdata('userid');
		$this->session->sess_destroy();
		redirect(base_url($cmpR['slug'] . '/account'));
	}

	//	Customer Stop Intake Notification
	public function stop_intake_reminder() {
		$page_data = $this->intake_model->stop_intake_reminder();

		//$page_data = array("error_type"=>$error_type, "msg"=>$msg);
		$this->load->view('Site/ConfirmationEmail', $page_data);
	}

	//	Customer Stop Analysis Reminder Notification
	public function stop_analysis_reminder() {
		$seg_4 = $this->uri->segment(4);
		$seg_4 = base64_decode(strtr($seg_4, '-_', '+/'));
		$arr = explode(".", $seg_4);
		$client_id = $arr[0];
		$intake_id = $arr[1];
		$car_id = $arr[2];

		$icsR = $this->default_model->get_arrby_tbl_single('client_analysis_results', '*', "client_id='" . $client_id . "' and intake_id='" . $intake_id . "' and id='" . $car_id . "'", '1');
		if (isset($icsR['id'])) {
			$sql = "update client_program_progress set status='Stop', reminder_status=0, step_completed_date='" . date("Y-m-d") . "' where client_id='$client_id' and (program_id_primary='1' or program_id_primary='23' or program_id_primary='40' or program_id_primary='178' or program_id_primary='193')";
			$this->db->query($sql);

			$this->db->query("update client_analysis_results set last_sent_reminder='2050-12-12' where id='" . $icsR['id'] . "'");

			$error_type = "Success";
			$msg = $this->account_model->stop_reminder_confirmation_email($client_id); // Stop Reminder Confirmation Email
			//$msg = 'Schedule-Payment Reminder notification successfully stoped now.';
			//$this->session->set_flashdata('success', $msg);
		} else {
			$error_type = "Error";
			$msg = 'Invalid link';
			//$this->session->set_flashdata('error', $msg);
		}

		$page_data = array("error_type" => $error_type, "msg" => $msg);
		$this->load->view('Site/ConfirmationEmail', $page_data);
		//redirect(base_url($this->uri->segment(1)."/account"));
		//exit;
	}

	//	Customer Stop Program Reminder Notification
	public function stop_program_reminder() {
		$seg_4 = $this->uri->segment(4);
		$seg_4 = base64_decode(strtr($seg_4, '-_', '+/'));
		$arr = explode(".", $seg_4);
		$client_id = $arr[0];
		$program_definition_id = $arr[1];
		$cp_id = $arr[2];

		$icsR = $this->default_model->get_arrby_tbl_single('client_program', '*', "client_id='" . $client_id . "' and program_definition_id='" . $program_definition_id . "' and id='" . $cp_id . "'", '1');
		if (isset($icsR['id'])) {
			$this->db->query("update client_program set status='Stop' where id='$cp_id'");

			$sql = "update client_program_progress set status='Stop',reminder_status=0, step_completed_date='" . date("Y-m-d") . "' where client_id='$client_id' and program_id_primary='$program_definition_id' order by program_id desc limit 1";
			$this->db->query($sql);

			$error_type = "Success";
			$msg = $this->account_model->stop_reminder_confirmation_email($client_id); // Stop Reminder Confirmation Email
			//$msg = 'Reminder notification successfully stoped now.';
			//$this->session->set_flashdata('success', $msg);
		} else {
			$error_type = "Error";
			$msg = 'Invalid link';
			//$this->session->set_flashdata('error', $msg);
		}

		$page_data = array("error_type" => $error_type, "msg" => $msg);
		$this->load->view('Site/ConfirmationEmail', $page_data);
		//redirect(base_url($this->uri->segment(1)."/account"));
		//exit;
	}

	//	Customer Attestation Form
	public function customer_attestation_form() {
		$this->check_login_session(); // Check Login Session

		$client_id = $this->uri->segment(3);
		if ($GLOBALS["loguser"]["role"] == "Customer") {$client_id = $GLOBALS["loguser"]["id"];}
		$page_data = $this->programs_model->get_client_attestation_form_results($client_id);

		if (isset($page_data['client_data']['client']['id'])) {
			if (isset($page_data['car']['id'])) {
				$this->load->view('account/users/DataConfirmation/Attestation_Form', $page_data);
			}
		}
	}

	//	Company Intake Client Status Change
	public function customer_intake_client_status_change() {
		$client_id = $this->uri->segment(3);
		$this->db->where(['client_id' => $client_id, 'id' => $this->uri->segment(4)]);
		$this->db->update('intake_client_status', ['status2' => 'Save']);

		$q = $this->db->query("SELECT * FROM intake_client_status where client_id='$client_id' and id='" . $this->uri->segment(4) . "'");
		$icsr = $q->row_array();
		$intake_id = $icsr['intake_id'];
		$this->crm_model->add_intake_document($client_id, $intake_id, $status = 'Hide');
	}

	//	Send Intake Email
	public function send_intake_email() {
		$result = $this->crm_model->admin_send_intake_email($_POST['uid']);
		if ($result['error'] != '') {echo "Failed";}
	}

	//	Stop Reminder Emails
	public function cap_stop_remonder() {
		$result = $this->crm_model->admin_cap_stop_remonder($_POST['client_id']);
	}

	//	Reset ca Save Internal Notes
	public function reset_ca_save_internal_notes() {
		$varname = "ca_in_" . $_POST['client_id'];

		$this->session->unset_userdata($varname);

		if (trim($_POST['internal_notes']) != "") {
			$this->session->set_userdata($varname, $_POST['internal_notes']);
		}
	}

	//	Intake Form
	public function view_nslds_snapshot() {
		$this->load->view('account/users/customer_view_snapshot');
	}

	//	View Client Intake Form
	public function view_client_intake_form() {
		$this->load->view('account/users/customer_view_intake_form');
	}

	//	Document Management
	public function document() {

		$this->check_login_session(); // Check Login Session
		$page_data = array();
		$page_name = "list";
		$page_data['data']['name'] = "Document";
		$page_data['data']['meta_title'] = "Document";
		if ($this->uri->segment(3) == "new") {
			$page_name = "new";
			$page_data['data']['name'] = "Upload Document";
			$page_data['data']['meta_title'] = "Upload Document";}
		if ($this->uri->segment(3) == "edit") {
			$page_name = "new";
			$page_data['data']['name'] = "Edit Document";
			$page_data['data']['meta_title'] = "Edit Document";}
		if ($this->uri->segment(3) == "view") {
			$page_name = "view";
			$page_data['data']['name'] = "View Document";
			$page_data['data']['meta_title'] = "View Document";}
		if ($this->uri->segment(3) == "delete") {$this->crm_model->admin_document_self_delete($this->uri->segment(4));}

		//	Self Download
		if ($this->uri->segment(3) == "view") {
			if (isset($_POST['submit_self_download'])) {$this->crm_model->admin_document_self_download($this->uri->segment(4));}
			if (isset($_POST['submit_custom_download'])) {$this->crm_model->admin_document_custom_download($this->uri->segment(4));}
		}

		//	Add Edit Records
		if ($this->uri->segment(3) == "" || $this->uri->segment(3) == "new" || $this->uri->segment(3) == "edit") {
			/* Set validation rule for name field in the form */
			$this->form_validation->set_rules('document_name', 'Document Name', 'required');
			//$this->form_validation->set_rules('file_client_document', 'Document File', 'required');

			if ($this->form_validation->run() == FALSE) {
				$this->session->set_flashdata('error', validation_errors());
			} else {
				$result = $this->crm_model->admin_document();
				if ($result['error'] != '') {
					$this->session->set_flashdata('error', $result['error']);
				} else {
					$this->session->set_flashdata('success', 'Record successfully saved.');
					redirect(base_url('account/document'));}
			}
		}

		if ($GLOBALS["loguser"]["role"] == "Customer") {
			$page_data['client_data'] = $this->crm_model->get_client_full_details($GLOBALS["loguser"]["id"]);
			$this->load->view('account/Client/document', $page_data);
		} else {
			$this->load->view('account/document/' . $page_name, $page_data);
		}
	}

	//	Company Clients Management
	public function customer() {
		$this->check_login_session(); // Check Login Session
		$this->crm_model->check_account_segment_and_redirect("account", "customer"); // Check Account Segment and Redirect

		$page_data = array();
		$seg_3 = $this->uri->segment(3);
		$page_name = "list";
		$page_data['data']['name'] = $page_data['data']['meta_title'] = "Manage Client";

		if ($this->uri->segment(3) == "view") {
			$page_name = "view";
			$page_data['data']['name'] = $page_data['data']['meta_title'] = "View Client";}
		if ($this->uri->segment(3) == "add_program") {
			$page_name = "program";
			$page_data['data']['name'] = "Programs";
			$page_data['data']['meta_title'] = "Programs";}
		if ($this->uri->segment(3) == "status") {
			$page_name = "status";
			$page_data['data']['name'] = "Client Status";
			$page_data['data']['meta_title'] = "Client Status";}
		if ($this->uri->segment(3) == "document") {
			$page_name = "document";
			$page_data['data']['name'] = $page_data['data']['meta_title'] = "Documents";}
		if ($this->uri->segment(3) == "report") {
			$page_name = "report";
			$page_data['data']['name'] = $page_data['data']['meta_title'] = "View Reminder Reports";
			$page_data['data']['report_list'] = $this->account_model->get_client_reminder_report($this->uri->segment(4));
		}

		//	Delete Customer
		if ($this->uri->segment(3) == "delete") {$this->crm_model->delete_customer();}

		//	Add Edit Document
		if ($this->uri->segment(3) == "view") {
			if (isset($_POST['submit_send_intake'])) {
				$result = $this->crm_model->admin_send_intake_email($this->uri->segment(4));
				if ($result['error'] != '') {
					$this->session->set_flashdata('error', $result['error']);
				} else {
					$this->session->set_flashdata('success', 'Intake email successfully sent.');
					redirect(base_url('account/customer/view/' . $this->uri->segment(4)));}
			} else {
				/* Set validation rule for name field in the form */
				$this->form_validation->set_rules('client_id', 'Client Id', 'required');

				if ($this->form_validation->run() == FALSE) {
					$this->session->set_flashdata('error', validation_errors());
				} else {
					$result = $this->crm_model->admin_renew_nslds_file($this->uri->segment(4));
					if ($result['error'] != '') {
						$this->session->set_flashdata('error', $result['error']);
					} else {
						$this->session->set_flashdata('success', 'Record successfully saved.');
						redirect(base_url('account/customer/view/' . $this->uri->segment(4)));}
				}
			}
		}

		//	Add Edit Document
		if ($this->uri->segment(3) == "document") {
			if ($this->uri->segment(5) == "delete") {$this->crm_model->admin_document_self_delete($this->uri->segment(6));}
			if ($this->uri->segment(5) == "view") {
				if (isset($_POST['submit_self_download'])) {$this->crm_model->admin_document_self_download($this->uri->segment(6));}
				if (isset($_POST['submit_custom_download'])) {$this->crm_model->admin_document_custom_download($this->uri->segment(6));}
			}

			/* Set validation rule for name field in the form */
			$this->form_validation->set_rules('document_name', 'Document Name', 'required');
			//$this->form_validation->set_rules('file_client_document', 'Document File', 'required');

			if ($this->form_validation->run() == FALSE) {
				$this->session->set_flashdata('error', validation_errors());
			} else {
				$result = $this->crm_model->admin_document();
				if ($result['error'] != '') {
					$this->session->set_flashdata('error', $result['error']);
				} else {
					$this->session->set_flashdata('success', 'Record successfully saved.');
					redirect(base_url('account/customer/document/' . $this->uri->segment(4)));}
			}
		}

		//	Add Edit Records
		if ($this->uri->segment(3) == "stop_program") {
			$result = $this->programs_model->stop_program();
			$this->session->set_flashdata('success', 'Program successfully stop.');
			redirect(base_url('account/customer/add_program/' . $this->uri->segment(4)));
			exit;
		}

		//	Add Edit Records
		if ($this->uri->segment(3) == "add_program") {
			$client_id = $this->uri->segment(4);
			$red_url = "account/customer/add_program/" . $client_id;

			if (isset($_POST['Submit_cps6'])) {
				if (isset($_POST['cd'])) {
					if ($_POST['cd'] == "Select Program and add the Client") {
						if (trim($_POST['cps6_program_id']) != "") {
							$red_url = "account/customer/status/" . $client_id . "/complete/" . $_POST['program_definition_id'] . "/spaatc/" . $_POST['cps6_program_id'];
						} else {
							$this->session->set_flashdata('error', "Please select a program.");
						}
					} else if ($_POST['cd'] == "Do not Continue") {
						$red_url = "account/customer/status/" . $client_id . "/complete/" . $_POST['program_definition_id'] . "/dnc";
					} else { $red_url = "account/customer/status/" . $client_id . "/complete/" . $_POST['program_definition_id'] . "/cwsap";}
				} else {

				}
				redirect(base_url($red_url));
				exit;
			}

			/* Set validation rule for name field in the form */
			$this->form_validation->set_rules('program_id', 'Program', 'required');

			if ($this->form_validation->run() == FALSE) {
				$this->session->set_flashdata('error', validation_errors());
			} else {
				$result = $this->crm_model->admin_users_add_program($this->uri->segment(4), $_POST['program_id']);
				if ($result['error'] != '') {
					$this->session->set_flashdata('error', $result['error']);
				} else {
					$this->session->set_flashdata('success', 'Program successfully added.');
					redirect(base_url('account/customer/status/' . $this->uri->segment(4) . '/complete/' . $result['program_definition_id']));
					//redirect(base_url('account/customer/add_program/'.$this->uri->segment(4)));
				}
			}

		}

		//	Complete Program Step
		if (($this->uri->segment(3) == "status" || $this->uri->segment(3) == "add_program") && $this->uri->segment(5) == "complete") {
			$cnd = "client_id='" . $this->uri->segment(4) . "' and program_definition_id='" . $this->uri->segment(6) . "'";
			$chkr = $this->default_model->get_arrby_tbl('client_program_progress', '*', $cnd, '1');

			if (!isset($chkr[0])) {redirect(base_url("account/customer/add_program/" . $this->uri->segment(4)));exit;}
			$chkr = $chkr["0"];

			$pr = $this->default_model->get_arrby_tbl('program_definitions', '*', "program_definition_id='" . $chkr['program_id'] . "'", '1');
			$pr = $pr["0"];

			$result = $this->crm_model->admin_users_add_program_step($this->uri->segment(4), $this->uri->segment(6));
			if ($result['error'] != '') {
				$this->session->set_flashdata('error', $result['error']);
			} else {

				if ($this->uri->segment(7) == "redirect_to_document" && $this->uri->segment(8) != "") {
					redirect(base_url('account/customer_intake_form/' . $this->uri->segment(4) . '/' . $this->uri->segment(8)));
					exit;
				} else {
					$this->session->set_flashdata('success', 'The Client has completed the <strong>' . $pr['program_title'] . ' (' . $pr['step_name'] . ')</strong>');
					redirect(base_url('account/customer/add_program/' . $this->uri->segment(4)));
				}
			}

		}

		$this->load->view('account/users/' . $page_name, $page_data);
	}

	//	Dashboard
	public function programs() {
		$this->check_login_session(); // Check Login Session
		$this->crm_model->check_account_segment_and_redirect("account", "programs"); // Check Account Segment and Redirect

		@extract($_POST);
		@extract($_GET);
		error_reporting(E_ALL ^ E_NOTICE);

		$page_data = array();
		$page_data['data']['name'] = "Programs";
		$page_data['data']['meta_title'] = "Programs";
		if ($this->uri->segment(3) != "") {
			$page_data['program_dadta'] = $this->programs_model->get_program_reports_list(ucfirst($this->uri->segment(4)));
			$this->load->view('account/Programs/list', $page_data);
		} else {
			$this->load->view('account/Programs/dashboard', $page_data);
		}
	}

	//	Intake Form
	public function intake_status() {

		$this->check_login_session(); // Check Login Session
		$this->crm_model->check_account_segment_and_redirect("account", "intake"); // Check Account Segment and Redirect
		@extract($_POST);
		@extract($_GET);
		error_reporting(E_ALL ^ E_NOTICE);

		$page_data = array();

		if ($this->uri->segment(3) == "idr") {
			$name = "IDR";
		} else if ($this->uri->segment(3) == "consolidation") {
			$name = "Consolidation";
		} else if ($this->uri->segment(3) == "recertification") {
			$name = "Recertification";
		} else if ($this->uri->segment(3) == "recalculation") {
			$name = "Recalculation";
		} else if ($this->uri->segment(3) == "switch_idr") {
			$name = "Switch IDR";
		} else if ($this->uri->segment(3) == "update") {
			$name = "Update";
		} else { $name = "Initial";}

		$page_data['data']['name'] = $page_data['data']['meta_title'] = $name . " Intake";
		if ($GLOBALS["loguser"]["role"] == "Customer") {
			/* Set validation rule for name field in the form */
			$this->form_validation->set_rules('client_id', 'Client Id', 'required');

			if ($this->form_validation->run() == FALSE) {
				$this->session->set_flashdata('error', validation_errors());
			} else {
				$result = $this->crm_model->admin_renew_nslds_file($GLOBALS["loguser"]["id"], $name);

				if ($result['error'] != '') {
					$this->session->set_flashdata('error', $result['error']);
				} else {
					$this->session->set_flashdata('success', 'Record successfully saved.');
					redirect(base_url('account/intake/' . $this->uri->segment(3)));}
			}

			if ($name == "Initial") {$this->crm_model->admin_intake_check($GLOBALS["loguser"]["id"], $name);}
			$page_data['client_data'] = $this->crm_model->get_client_full_details($GLOBALS["loguser"]["id"]);
			$this->load->view('account/Client/Intake/status', $page_data);
		} else {
			$this->load->view('account/dashboard', $page_data);
		}
	}

	//	Client Program
	public function client_program() {
		$this->check_login_session(); // Check Login Session
		if ($GLOBALS["loguser"]["role"] != "Customer") {redirect(base_url("account"));}

		@extract($_POST);
		@extract($_GET);
		error_reporting(E_ALL ^ E_NOTICE);

		$page_data = array();
		$page_data['data']['name'] = $page_data['data']['meta_title'] = "Program";
		$page_data['client_data'] = $this->crm_model->get_client_full_details($GLOBALS["loguser"]["id"]);

		$this->load->view('account/Client/program', $page_data);
	}

	//	Intake Form
	public function intake_form() {

		if (!$GLOBALS["loguser"]["id"]) {
			$this->session->set_userdata('redirect', current_url());
			if (isset($_GET['company'])) {redirect(base_url($_GET['company'] . "/client_login"));} else {
				redirect(base_url($this->uri->segment(1) . "/client_login"));}
		}
		if ($GLOBALS["loguser"]["role"] != "Customer") {redirect(base_url($_GET['company'] . "/client_login"));}

		$iform = $this->uri->segment(2);

		$check = $this->db->query('select * from intake_client_status where client_id=' . $GLOBALS["loguser"]["id"] . ' and intake_id=4')->num_rows();

		if ($check > 0) {
			$iform = 'update_intake_form';
		}

		if ($this->uri->segment(1) == "account") {

			$cmpR = $this->crm_model->get_company_details($GLOBALS["loguser"]["company_id"]);
			if (isset($_GET['intake_page_no'])) {$intake_page_no = $_GET['intake_page_no'];} else { $intake_page_no = 1;}

			redirect(base_url($cmpR['slug'] . "/" . $iform . "?intake_page_no=" . $intake_page_no));
			exit;
		}

		@extract($_POST);
		@extract($_GET);
		error_reporting(E_ALL ^ E_NOTICE);
		if (!isset($_GET['intake_page_no'])) {$intake_page_no = 1;}

		$intake_data = $this->default_model->get_arrby_tbl_single('intake', '*', "intake_slug='" . $iform . "'", '1');
		$intake_id = $intake_data['intake_id'];
		$intake_page_data = $this->default_model->get_arrby_tbl_single('intake_page', '*', "intake_id='" . $intake_id . "' and intake_page_no='" . $intake_page_no . "'", '1');
		$intake_page_no = $intake_page_data['intake_page_no'];

		$intake_question_data = $this->default_model->get_arrby_tbl('intake_question', '*', "intake_id='" . $intake_id . "' and intake_page_no='" . $intake_page_no . "' order by placement_order asc", '500');

		//$intake_question_data = $this->default_model->get_arrby_tbl('intake_question','*',"intake_id='".$intake_id."' order by placement_order asc",'500');

		if (isset($_POST['Submit_intake_answer'])) {
			$res = $this->crm_model->admin_save_intake_answer_by_client($intake_page_no, $intake_id);
			if ($res['error'] != "") {$this->session->set_flashdata('error', $res['error']);}
		}

		$this->crm_model->admin_intake_check_step($intake_page_no, $check); //	Check Intake Step

		$client_id = $GLOBALS["loguser"]["id"];

		$page_data = array();
		$page_data['data']['name'] = $intake_data['intake_title'];
		$page_data['data']['meta_title'] = $intake_data['intake_title'];

		$page_data['intake_data'] = $intake_data;
		$page_data['intake_page_data'] = $intake_page_data;
		$page_data['intake_question_data'] = $intake_question_data;
		$page_data['intake_client_status'] = $this->crm_model->client_intake_client_status($client_id, $check > 0 ? 4 : "1");
		$page_data['intake_type'] = $check > 0 ? 'update' : 'initial';

		$page_data['cmR'] = $this->default_model->get_arrby_tbl_single('users', '*', "parent_id='" . $GLOBALS["loguser"]["id"] . "'", '1');
		$page_data['client_data'] = $this->crm_model->get_client_full_details($client_id);
		$page_data['iform'] = $iform;

		$this->load->view('account/Client/Intake/intake_form', $page_data);
	}

	//	IDR Intake Form
	public function idr_intake_form() {
		if (!$GLOBALS["loguser"]["id"]) {
			$this->session->set_userdata('redirect', current_url());
			redirect(base_url($this->uri->segment(1) . "/client_login"));}
		if ($GLOBALS["loguser"]["role"] != "Customer") {redirect(base_url($this->uri->segment(1) . "/client_login"));}

		@extract($_POST);
		@extract($_GET);
		error_reporting(E_ALL ^ E_NOTICE);
		if (!isset($_GET['intake_page_no'])) {$intake_page_no = 1;}

		$intake_data = $this->default_model->get_arrby_tbl_single('intake', '*', "intake_slug='" . $this->uri->segment(2) . "'", '1');
		if (!isset($intake_data['intake_id'])) {redirect(base_url("account/dashboard"));}
		$intake_id = $intake_data['intake_id'];
		$intake_page_data = $this->default_model->get_arrby_tbl_single('intake_page', '*', "intake_id='" . $intake_id . "' and intake_page_no='" . $intake_page_no . "'", '1');
		$intake_page_no = $intake_page_data['intake_page_no'];

		$intake_question_data = $this->default_model->get_arrby_tbl('intake_question', '*', "intake_id='" . $intake_id . "' and intake_page_no='" . $intake_page_no . "' order by placement_order asc", '500');

		//$intake_question_data = $this->default_model->get_arrby_tbl('intake_question','*',"intake_id='".$intake_id."' order by placement_order asc",'500');

		if (isset($_POST['Submit_intake_answer'])) {
			$res = $this->crm_model->admin_save_intake_answer_by_client_2($intake_page_no, $intake_id);
			if ($res['error'] != "") {$this->session->set_flashdata('error', $res['error']);}
		}

		//$this->crm_model->admin_intake_check_step($intake_page_no);	//	Check Intake Step

		$client_id = $GLOBALS["loguser"]["id"];
		$page_data = array();
		$page_data['data']['name'] = $intake_data['intake_title'];
		$page_data['data']['meta_title'] = $intake_data['intake_title'];

		$page_data['intake_data'] = $intake_data;
		$page_data['intake_page_data'] = $intake_page_data;
		$page_data['intake_question_data'] = $intake_question_data;
		$page_data['intake_client_status'] = $this->crm_model->client_intake_client_status($client_id, $intake_id);

		$page_data['cmR'] = $this->default_model->get_arrby_tbl_single('users', '*', "parent_id='" . $GLOBALS["loguser"]["id"] . "'", '1');
		$iform = 'intake_form';

		$check = $this->db->query('select * from intake_client_status where client_id=' . $GLOBALS["loguser"]["id"] . ' and intake_id=4')->num_rows();

		if ($check > 0) {
			$iform = 'update_intake_form';
		}

		$page_data['iform'] = $iform;
		$page_data['client_data'] = $this->crm_model->get_client_full_details($GLOBALS["loguser"]["id"]);
		$this->load->view('account/Client/Intake/' . $intake_data['intake_slug'], $page_data);
	}

	//	Change Payment Plan Scenario
	public function change_payment_plan_scenario() {
		$scenario_selected = $_POST['scenario_selected'];
		if (is_numeric($scenario_selected)) {
			$payment_plan_scenario = $this->array_model->stlb_payment_plan_scenario();
			$pps = $payment_plan_scenario[$scenario_selected];
		} else {
			$payment_plan_scenario_group = $this->array_model->stlb_payment_plan_scenario_group();
			$garr = explode(" ", $scenario_selected);
			$g1 = $garr[0];
			$g2 = $garr[1];
			$pps = $payment_plan_scenario[$g1][$g2];
		}
		$pps_1 = explode(" ", $pps["name"]);
		$pps_2 = str_split($pps["group"]);

		$res = array();
		if ($pps_1[0] == "Single") {$res['marital_status'] = "Single";} else { $res['marital_status'] = "Married";}
		if ($pps_1[1] == "AGI") {$res['use_agi_or_monthly'] = "AGI";} else { $res['use_agi_or_monthly'] = "Monthly";}
		if ($pps_1[0] == "Single" || $pps_1[0] == "MFS" || $scenario_selected > 24) {$res['file_joint_or_separate'] = "Separate";} else { $res['file_joint_or_separate'] = "Joint";}

		//echo $pps["name"]. "/ ". $pps["group"]. " / ".$pps_2[0];
		echo json_encode($res);
	}

	//	Run Current Analysis Scenario Latest
	public function run_current_analysis_scenario_group() {
		@extract($_POST);
		$output = $this->crm_model->run_current_analysis_scenario_group($_POST);
		echo $output;
	}

}
