<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Account_model extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->load->helper('url');
		$this->load->database();

		$this->crm_model->get_site_settings();

	}

//	Add Report to Case Manager
	public function add_case_manager_setting($id = 0) {
		$q = $this->db->query("select id from users_cm_setting where id='$id'");
		$res = $q->row_array();

		if (!isset($res['id'])) {$this->db->insert('users_cm_setting', ['id' => $id, 'last_report_send' => '1990-10-10']);}
	}

//	Stop Reminder Confirmation Email
	public function stop_reminder_confirmation_email($client_id = 0) {
		$q = $this->db->query("select * from users where id='$client_id'");
		$res = $q->row_array();

		if (isset($res['id'])) {
			$company_id = $res["company_id"];
			$cmr = $this->default_model->get_arrby_tbl_single('users', '*', "id='" . $res['parent_id'] . "'", '1');
			$cmpR = $this->crm_model->get_company_details($company_id);
			$smtp_data = $this->crm_model->get_company_smtp_email_details($company_id);

			//	Send EMail
			$smtp_data['email'] = $res['email'];
			$smtp_data['subject'] = "Confirmation e-mail";
			$smtp_data['Msg'] = $msg = 'You have decided not to continue with us at this time. We are confirming this and will stop all reminder e-mails to you. If you wish to contact us or to move forward at a later date, please e-mail us at <a href="mailto:' . $cmr['email'] . '">' . $cmr['email'] . '</a>, or call us at <a href="tel:' . $cmr['phone'] . '">' . $cmr['phone'] . '</a>.';
			$this->crm_model->send_email($smtp_data);

		} else {
			$msg = 'Invalid link';
		}

		return $msg;
	}

//	Get Client Reminder Report
	public function get_client_reminder_report($client_id = 0) {
		$id = $this->uri->segment(5);
		if ($id != "") {

			$q = $this->db->query("select * from client_reminder_status where client_id='$client_id' and id='$id'");
			$res = $q->row_array();
			if (isset($res['id'])) {$res["status_individual"] = "Success";} else { $res["status_individual"] = "Failed";}
		} else {
			$q = $this->db->query("select * from client_reminder_status where client_id='$client_id' order by id desc");
			$res = $q->result_array();
		}
		return $res;
	}

//	Company Payments History
	public function company_payments_history($company_id = 0) {
		$q2 = $this->db->query("select * from payments where company_id='$company_id' order by payment_id desc");
		$res = $q2->result_array();
		return $res;
	}

//	Update Users Current Program Date
	public function update_users_current_program_date($client_id = 0) {
		if ($client_id != '0') {
			$sql = "select * from client_program_progress where client_id='$client_id' order by step_due_date desc limit 1";
			$q2 = $this->db->query($sql);
			$r = $q2->row_array();
			if (isset($r['program_definition_id'])) {
				$this->db->query("clients users set current_program_date='" . $r['step_due_date'] . "' where client_id='$client_id'");
				//echo $r['step_due_date']." &&& ";
			}
		}
	}

//	Add/Edit Cleint
	public function add_client($id = 0, $company_id = 0, $parent_id = 0, $advertisement_id = 0) {
		$error = '';
		@extract($_POST);

		//	Check Record for Edit
		$q = $this->db->query("SELECT * FROM users where id='$id' limit 1");
		$result = $q->row_array();
		if (isset($result['id'])) {
			$id = $result['id'];
			$company_id = $result['company_id'];}

		//	Check Email
		$crl_cnd = "";
		if ($id == 0) {} else { $crl_cnd = " and id!='$id'";}
		$q = $this->db->query("SELECT * FROM users where company_id='" . $company_id . "' and email='" . $email . "' $crl_cnd limit 1");
		$r = $q->row_array();
		if (isset($r['id'])) {$error = "This email already exists. Please enter another email.";}

		//	Check Case Manager
		if (isset($parent_id)) {
			$q = $this->db->query("SELECT id FROM users where company_id='" . $company_id . "' and id='" . $parent_id . "' and (role='Company' or role='Company User') limit 1");
			$cmn = $q->num_rows();
			if ($cmn == 0) {$error = "Please select a valid case manager.";}
		}

		//	Customer Unique by first name, last name, email address, company name
		$crl_cnd = "name='" . $_POST['name'] . "' and lname='" . $_POST['lname'] . "' and email='" . $_POST['email'] . "'";
		$crl_cnd = $crl_cnd . " and company_id='" . $company_id . "'";
		if ($id == 0) {} else { $crl_cnd = $crl_cnd . " and id!='$id'";}

		$q = $this->db->query("SELECT * FROM users where $crl_cnd limit 1");
		$r = $q->row_array();
		if (isset($r['id'])) {$error = "User already exists having same first name and last name. Please enter another first name and last name.";}

		foreach ($_POST as $key => $value) {$col_arr[$key] = $value;}
		unset($col_arr['psd']);
		unset($col_arr['password']);
		unset($col_arr['rpassword']);
		unset($col_arr['g-recaptcha-response']);
		unset($col_arr['Submit_']);
		$col_arr['role'] = "Customer";

		if (isset($col_arr['recertification_date']) && !empty($col_arr['recertification_date'])) {$col_arr['recert_updated'] = true;}
		if (isset($_POST['password'])) {$_POST["psd"] = $_POST['password'];}
		if (isset($_POST["psd"])) {if (trim($_POST["psd"]) != '') {$col_arr['psd'] = $this->default_model->psd_encrypt($_POST["psd"]);}}

		if ($error == '') {
			// upload file
			$config['allowed_types'] = 'gif|jpg|jpeg|png|JPG|JPEG|PNG|gif';
			$config['file_name'] = '1';
			$config['upload_path'] = './assets/uploads/' . date('Y/m');
			if (!is_dir($config['upload_path'])) {
				mkdir($config['upload_path'], 0777, TRUE);
			}

			$this->load->library('upload', $config);
			if ($this->upload->do_upload('profile_img')) {
				if ($id > 0) {if (file_exists($result['image'])) {unlink($result['image']);}}
				$col_arr['image'] = 'assets/uploads/' . date('Y/m') . '/' . $this->upload->data('file_name');
			}

			if ($id == 0) {
				$col_arr['company_id'] = $company_id;
				$col_arr['parent_id'] = $parent_id;
				$col_arr['advertisement_id'] = $advertisement_id;
				//$col_arr['current_program'] = 91;
				$client_id = $id = $this->default_model->dbInsert('users', $col_arr); //	Insert Record

				$ac = $this->db->query('select account_type from users_company where id=' . $company_id)->row_array();

				$this->default_model->dbInsert("clients", ["client_id" => $client_id, "advertisement_id" => $advertisement_id, "current_program" => 91, "current_program_date" => date('Y-m-d'), 'client_account_type' => $ac['account_type']]);

				$program_id = 91;
				$intake_id = 1;
				$this->programs_model->add_intake_program($client_id, $program_id, $intake_id); //	Add Intake Program
				//$this->crm_model->admin_send_intake_email($id, "1");	//	Send Initial Intake Email

				$this->email_model->send_account_dedtails($client_id, $_POST["psd"]);
				if (!isset($GLOBALS["loguser"]["id"])) {$this->email_model->lead_to_case_manager($client_id);}

				$this->programs_model->add_client_to_current_program($client_id);
				$this->intake_model->add_intake_question($client_id, $intake_id); //	Add Intake Question

				$result['msg'] = "Your account has been successfully created.";
			} else {
				//	Update Profile
				$this->db->where(array('id' => $id, 'company_id' => $company_id, 'role' => 'Customer'));
				$this->db->update('users', $col_arr);
			}

		} else { $error = $error;}
		$result['id'] = $id;
		$result['error'] = $error;

		return $result;
	}

}
?>