<?php	defined('BASEPATH') OR exit('No direct script access allowed');
error_reporting(E_ALL ^ E_NOTICE);
extract($_POST);
extract($_GET);

class Admin extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->library(array('session', 'email', 'form_validation', 'image_lib', 'pagination', 'CSVReader'));
		$this->load->helper(array('form', 'url', 'file', 'cookie'));
		$this->load->model(array('front_model', 'default_model', 'crm_model', 'admin_model', 'programs_model'));

		@extract($_POST);

		$GLOBALS["loguser"] = $this->admin_model->get_login_user($this->session->userdata('adminId'));

		$hst = $_SERVER['HTTP_HOST'];
		if ($hst == "test.studentloantoolbox.net" || $hst == "studentloantoolbox.net") {redirect(base_url());}

	}

	//	Login default Page
	public function index() {
		if ($this->session->userdata('adminId')) {redirect(base_url('admin/dashboard'));} else {redirect(base_url('admin/login'));}
	}

	//	Login Page
	public function login() {
		if ($this->session->userdata('adminId')) {redirect(base_url('admin/dashboard'));}

		/* Set validation rule for name field in the form */
		$this->form_validation->set_rules('email', 'Email', 'required');
		$this->form_validation->set_rules('password', 'Password', 'required');

		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('error', validation_errors());
		} else {
			$result = $this->admin_model->login();
			if (!isset($result['id'])) {
				$this->session->set_flashdata('error', 'Invalid Login Details');
			} else {redirect(base_url('admin/dashboard'));}
		}

		$page_data = array();
		$page_data['data']['name'] = "Login";
		$page_data['data']['meta_title'] = "Login";
		$this->load->view('Admin/login', $page_data);
	}

	//	Forgot Password Page
	public function fp() {
		if ($this->session->userdata('adminId')) {redirect(base_url('admin/dashboard'));}

		/* Set validation rule for name field in the form */
		$this->form_validation->set_rules('email', 'Email', 'required');

		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('error', validation_errors());
		} else {
			$result = $this->admin_model->fp();
			if (!isset($result['id'])) {
				$this->session->set_flashdata('error', 'Invalid User ID.');
			} else {
				$this->session->set_flashdata('success', 'New password successfully sent on your email.');
				redirect(base_url('admin/fp'));}
		}

		$page_data = array();
		$page_data['data']['name'] = "Forgot Password";
		$page_data['data']['meta_title'] = "Forgot Password";
		$this->load->view('Admin/fp', $page_data);
	}

	//	Dashboard
	public function dashboard() {
		@extract($_POST);
		@extract($_GET);
		error_reporting(E_ALL ^ E_NOTICE);

		$page_data = array();
		$page_data['data']['name'] = "Dashboard";
		$page_data['data']['meta_title'] = "Dashboard";
		$this->load->view('Admin/dashboard', $page_data);
	}

	//	Settings
	public function settings() {
		$page_data = array();

		/* Set validation rule for name field in the form */
		$this->form_validation->set_rules('name', 'Name', 'required');
		$this->form_validation->set_rules('phone', 'Mobile No', 'required|min_length[10]|max_length[10]');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');

		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('error', validation_errors());
		} else {
			$result = $this->admin_model->settings_update();
			if ($result['error'] != '') {
				$this->session->set_flashdata('error', $result['error']);
			} else {
				$this->session->set_flashdata('success', 'Settings details successfully saved.');
				redirect(base_url('admin/settings'));}
		}

		$page_data['data']['name'] = "Settings";
		$page_data['data']['meta_title'] = "Settings";
		$this->load->view('Admin/settings/settings', $page_data);
	}

	//	Profile
	public function profile() {
		$page_data = array();

		/* Set validation rule for name field in the form */
		$this->form_validation->set_rules('name', 'Name', 'required');
		$this->form_validation->set_rules('phone', 'Mobile No', 'required|min_length[10]|max_length[10]');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');

		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('error', validation_errors());
		} else {
			$result = $this->admin_model->profile_update();
			if ($result['error'] != '') {
				$this->session->set_flashdata('error', $result['error']);
			} else {
				$this->session->set_flashdata('success', 'Profile details successfully saved.');
				redirect(base_url('admin/profile'));}
		}

		$page_data['data']['name'] = "Profile";
		$page_data['data']['meta_title'] = "Profile";
		$this->load->view('Admin/profile/profile', $page_data);
	}

	//	Change Password
	public function cp() {
		$page_data = array();
		/* Set validation rule for name field in the form */
		$this->form_validation->set_rules('cpassword', 'Current Password', 'required');
		$this->form_validation->set_rules('password', 'New Password', 'required');
		$this->form_validation->set_rules('rpassword', 'Retype Password', 'required');

		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('error', validation_errors());
		} else {
			$result = $this->admin_model->cp();
			if ($result['error'] != '') {
				$this->session->set_flashdata('error', $result['error']);
			} else { $this->session->set_flashdata('success', 'Password successfully changed.');}
		}

		$page_data['data']['name'] = "Change Password";
		$page_data['data']['meta_title'] = "Change Password";
		$this->load->view('Admin/profile/cp', $page_data);
	}

	//	Logout
	public function logout() {
		$this->session->set_userdata('adminId', '');
		$this->session->unset_userdata('adminId');
		$this->session->sess_destroy();
		redirect(base_url('admin'));
	}

	//	Company Management
	public function company() {
		if ($GLOBALS["loguser"]["role"] != "Admin") {redirect('crm/dashboard');} //	Check Access
		if ($this->uri->segment(3) == "view") {
			$this->company_view();
		} else {
			$page_data = array("data" => ["name" => "Manage Company", "meta_title" => "Manage Company"]);
			$this->load->view('Admin/Company/list', $page_data);
		}
	}

	//	Company View
	public function company_view() {
		if ($GLOBALS["loguser"]["role"] != "Admin") {redirect('crm/dashboard');} //	Check Access
		$res = $this->admin_model->get_company_full($this->uri->segment(4));
		if (isset($res['data']['id'])) {
			$cr = $res['data'];

			$page_data = array("data" => ["name" => $cr['name'], "meta_title" => $cr['name'], "company" => $res]);

			$this->load->view('Admin/Company/View', $page_data);
		} else {
			redirect(base_url("admin/company"));
		}
	}

	//	Case Manabger / Company User Management
	public function case_manager() {
		if ($GLOBALS["loguser"]["role"] != "Admin") {redirect('crm/dashboard');} //	Check Access

		$page_data = array("data" => ["name" => "Company User", "meta_title" => "Company User"]);
		$this->load->view('Admin/Company/Case_manager', $page_data);
	}

	//	Client Management
	public function clients() {
		if ($GLOBALS["loguser"]["role"] != "Admin") {redirect('crm/dashboard');} //	Check Access

		$page_data = array("data" => ["name" => "Manage Client", "meta_title" => "Manage Client"]);
		$this->load->view('Admin/Company/Clients', $page_data);
	}

	//	Delete Record
	public function delete_record() {
		$res = $this->admin_model->delete_record();
		$this->session->set_flashdata('success', 'Record successfully deleted.');
		redirect($_SERVER['HTTP_REFERER']);
	}

	//	Customer Management
	public function customer() {
		$page_data = array();
		$page_name = "customer.list.php";
		$page_data['data']['name'] = "Manage Company Client";
		$page_data['data']['meta_title'] = "Manage Company Client";
		if ($this->uri->segment(3) == "new") {
			$page_name = "customer.new.php";
			$page_data['data']['name'] = "Add New Client";
			$page_data['data']['meta_title'] = "Add New Client";}
		if ($this->uri->segment(3) == "edit") {
			$page_name = "customer.new.php";
			$page_data['data']['name'] = "Edit Client";
			$page_data['data']['meta_title'] = "Edit Client";}
		if ($this->uri->segment(3) == "view") {
			$user = $this->default_model->get_arrby_tbl('users', '*', "role='Customer' and id='" . $this->uri->segment(4) . "'", '1');
			$user = $user["0"];
			if ($user['id'] == "") {redirect("crm/customer");}

			$page_name = "customer.view.php";
			$page_data['data']['name'] = "#" . $user['name'] . " (" . $user['id'] . ")";
			$page_data['data']['meta_title'] = "#" . $user['name'] . " (" . $user['id'] . ")";

			//	Add Sponsor ID
			if (isset($_POST['Submit_sponsor_id'])) {
				$result = $this->crm_model->add_sponsor_id();
				if ($result['error'] != '') {
					$this->session->set_flashdata('error', $result['error']);
				} else {redirect("crm/customer/view/" . $this->uri->segment(4));}
			}

		}

		//	Delete User
		if ($this->uri->segment(3) == "delete") {
			$this->db->where(array('id' => $this->uri->segment(4), "role" => "Customer"));
			$this->db->delete('users');
			$this->session->set_flashdata('success', 'Record successfully delete.');
			redirect(base_url('admin/customer'));
		}

		/* Set validation rule for name field in the form */
		$this->form_validation->set_rules('name', 'Customer Name', 'required');
		$this->form_validation->set_rules('phone', 'Mobile No', 'required|min_length[10]|max_length[10]');
		//$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');

		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('error', validation_errors());
		} else {
			$result = $this->crm_model->admin_users($this->uri->segment(4), 'Customer');
			if ($result['error'] != '') {
				$this->session->set_flashdata('error', $result['error']);
			} else {
				$this->session->set_flashdata('success', 'Record successfully saved.');
				redirect(base_url('admin/customer'));}
		}

		$this->load->view('Admin/users/' . $page_name, $page_data);
	}

	//	Page Management
	public function pages() {
		$page_data = array();
		$page_name = "list";
		$page_data['data']['name'] = "Manage Pages";
		$page_data['data']['meta_title'] = "Manage Pages";
		if ($this->uri->segment(3) == "new") {
			$page_name = "new";
			$page_data['data']['name'] = "Add New Page";
			$page_data['data']['meta_title'] = "Add New Page";}
		if ($this->uri->segment(3) == "edit") {
			$page_name = "new";
			$page_data['data']['name'] = "Edit Page";
			$page_data['data']['meta_title'] = "Edit Page";}

		//	Delete User
		if ($this->uri->segment(3) == "delete") {
			$res = $this->default_model->get_arrby_tbl('pages', '*', "id='" . $this->uri->segment(4) . "'", '1');
			$res = $res["0"];
			unlink($res["image"]);

			$this->db->where(array('id' => $this->uri->segment(4)));
			$this->db->delete('pages');
			$this->session->set_flashdata('success', 'Record successfully delete.');
			redirect(base_url('admin/pages'));
		}

		/* Set validation rule for name field in the form */
		$this->form_validation->set_rules('name', 'Name', 'required');

		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('error', validation_errors());
		} else {
			$result = $this->crm_model->crm_pages($this->uri->segment(4));
			if ($result['error'] != '') {
				$this->session->set_flashdata('error', $result['error']);
			} else {
				$this->session->set_flashdata('success', 'Record successfully saved.');
				redirect(base_url('admin/pages'));}
		}

		$this->load->view('Admin/pages/' . $page_name, $page_data);
	}

	//	Contact Us History Management
	public function contact_us_history() {
		$page_data = array();
		$page_data['data']['name'] = "Manage Contact Us";
		$page_data['data']['meta_title'] = "Manage Contact Us";

		//	Delete User
		if ($this->uri->segment(3) == "delete") {
			$this->db->where(array('id' => $this->uri->segment(4)));
			$this->db->delete('contact_us_history');
			$this->session->set_flashdata('success', 'Record successfully delete.');
			redirect(base_url('admin/contact_us_history'));
		}

		$this->load->view('Admin/contact_us/list', $page_data);
	}

}
