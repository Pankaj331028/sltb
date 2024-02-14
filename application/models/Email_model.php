<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Email_model extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->load->helper('url');
		$this->load->database();
	}

	public function get_intake_program_reminder_tamplate($cR = array(), $cmpR, $prgmr, $step_id = '', $client_program_progress_id = '', $program_id = '', $step_due_date = '') {
		$res = '';
		$reminder_date_from = $step_due_date;
		$intake_id = 1;
		$q = 0;
		$a = 0;

		if ($this->db->query('select * from intake_client_status where client_id=' . $client_id . ' and intake_id=4')->num_rows() > 0) {
			$q = 96;
			$a = 74;
			$intake_id = 4;
		}
		$cltr = $cr = $cR;
		$client_id = $cR['id'];
		$company_id = $cmpR['id'];
		$cmr = $this->default_model->get_arrby_tbl_single('users', '*', "id='" . $cr['parent_id'] . "'", '1');
		$cmpR = $this->crm_model->get_company_details($company_id);
		$icsR = $this->crm_model->client_intake_client_status($cR['id'], $intake_id); //	Check Intake
		$iR = $this->default_model->get_arrby_tbl_single('intake', '*', "intake_id='" . $intake_id . "'", '1');

		if ($step_id == 2) {
			$cl_ec_id = $cR['id'] . "." . $intake_id . "." . $icsR['id'] . "." . $program_id . "." . $step_id;
			$cl_ec = strtr(base64_encode($cl_ec_id), '+/', '-_');
			$srl = base_url($cmpR['slug'] . "/" . $iR['intake_slug'] . "/stop/" . $cl_ec);
			$stop_reminder_link = '<a href="' . $srl . '" style="background:#FF0000; color:#FFFFFF; padding:5px 20px; font-weight:bold; text-decoration:none;">STOP REMINDER</a>';

			$reminder_email_subject = 'Your Free Student Loan Review - Reminder';
			$reminder_email_body = $cmpR['email_header'] . '<p>Hello ' . $cR['name'] . ',</p>
		<p>We want to remind you that you need to complete two simple steps to receive your Free student loan review.</p>
		<ol>
		<li>1. Complete your intake by going to <a href="' . $intake_link . '">' . $intake_link . '</a></li>
		<li>Upload your <a href="https://studentaid.gov">https://studentaid.gov</a> file which you need to download in txt format.</li>
		</ol>
		<p>Once you complete these steps, we will review your specific details and follow up with you. We endeavor to respond within 2 business days, but this may vary from time to time so please be patient.</p>
		<p>If you have decided not to continue with us at this time, Please click this link to stop any further reminders.</p>
		<p>' . $stop_reminder_link . '</p>
		<p>Regards,<br />' . $cmpR['name'] . '</p>';

			$col_arr_rr = ['client_program_progress_id' => $client_program_progress_id, 'program_id' => $program_id, 'step_id' => $step_id, 'company_id' => $company_id, 'client_id' => $client_id, 'days_to_send' => $prgmr['step_duration'], 'reminder_email_subject' => $reminder_email_subject, 'reminder_email_body' => $reminder_email_body, 'status_flag' => '1', 'to_whom' => '0', 'sent_to' => $cltr['email'], 'reminder_date_from' => $reminder_date_from];
			$this->db->insert('reminder_rules', $col_arr_rr); //	Insert Record

		} else if ($step_id == 4) {
			$q = $this->db->query("select * from intake_file_result where client_id='$client_id' and intake_question_id='" . ($q + 6) . "'");
			$ifr = $q->row_array();
			if (isset($ifr['intake_file_id'])) {
				$nslds_id = $ifr['intake_file_id'];
				$q = $this->db->query("select * from client_analysis_results where client_id='$client_id' and company_id='$company_id' and intake_id='$intake_id' and nslds_id='$nslds_id'");
				$car = $q->row_array();
				if (isset($car['id'])) {
					if ($car['par_csd'] == "We can help you") {

						$cl_ec_id = $cR['id'] . "." . $intake_id . "." . $icsR['id'] . "." . $program_id . "." . $step_id;
						$cl_ec = strtr(base64_encode($cl_ec_id), '+/', '-_');
						$srl = base_url($cmpR['slug'] . "/" . $iR['intake_slug'] . "/stop/" . $cl_ec);
						$stop_reminder_link = '<a href="' . $srl . '" style="background:#FF0000; color:#FFFFFF; padding:5px 20px; font-weight:bold; text-decoration:none;">STOP REMINDER</a>';

						if (trim($car['par_comment']) != "") {$par_comment = '<p>' . $car['par_comment'] . '</p>';} else { $par_comment = '';}
						$reminder_email_subject = 'Your Student Loan Review Results';
						$reminder_email_body = $cmpR['email_header'] . '<p>Dear ' . $cR['name'] . ',</p>
		<p>Thanks for taking the time to complete your intake. We have reviewed your details and have concluded that we can provide you with options that may resolve your situation.</p>
		<p>To discuss and plan your strategy, please follow these instructions:</p>
		<ol>
		<li>Please select a time from our calendar to set your meeting with your Student Loan Law attorney: <a href="' . $cmpR['calendar_link'] . '">' . $cmpR['calendar_link'] . '</a></li>
		<li>Please make your payment of <strong>$' . number_format($cmpR['analysis_fee'], 2) . '</strong> by going to: <a href="' . $cmpR['payment_link'] . '">' . $cmpR['payment_link'] . '</a></li>
		</ol>
		<p>If you have decided not to continue with us at this time, Please click this link to stop any further reminders.</p>
		<p>' . $stop_reminder_link . '</p>
		<p>If you have any questions, please email us at <a href="' . $cmr['email'] . '">' . $cmr['email'] . '</a>.</p>
		<p>We appreciate your choosing <strong>' . $cmpR['name'] . '</strong> and look forward to assisting you with your student loan matters.</p>
		<p>---<br /><strong>Regards</strong><br />' . $cmpR['name'] . '<br /><a href="' . base_url($cmpR['slug'] . "/account") . '">' . base_url($cmpR['slug'] . "/account") . '</a></p>';

						$reminder_date_from = date("Y-m-d");
						$col_arr_rr = ['client_program_progress_id' => $client_program_progress_id, 'program_id' => $program_id, 'step_id' => $step_id, 'company_id' => $company_id, 'client_id' => $client_id, 'days_to_send' => $prgmr['step_duration'], 'reminder_email_subject' => $reminder_email_subject, 'reminder_email_body' => $reminder_email_body, 'status_flag' => '1', 'to_whom' => '0', 'sent_to' => $cltr['email'], 'reminder_date_from' => $reminder_date_from];
						$this->db->insert('reminder_rules', $col_arr_rr); //	Insert Record
					}}}
		}

		return $res;
	}

//	Send Account Details
	public function send_account_dedtails($client_id = 0, $password = '') {
		$cr = $this->default_model->getRowArray("SELECT * FROM users where id='$client_id'");

		if (isset($cr['id']) && $password != '') {
			$cmpR = $this->default_model->get_company($cr['company_id']);
			$smtp_data = $this->default_model->get_company_smtp($cr['company_id']);

			$smtp_data['email'] = $cr['email'];
			//	Send Email to Customer
			$smtp_data['subject'] = 'Student Loan Tool Box - Account Details';
			$smtp_data['Msg'] = $cmpR['email_header'] . '<div style="font-family:Calibri; font-size:15px; width:100%; text-align:justify; padding:2px 15px;">
		<p>Dear ' . $cr['name'] . ' ' . $cr['lname'] . ',<br />Welcome to Student Loan Tool Box</p>
		<p>Your studentloantoolbox.net account login details are as below:</p>
		<p><strong>Email:</strong> ' . $cr['email'] . '</p>
		<p><strong>Login ID:</strong> ' . $cr['id'] . '</p>
		<p><strong>Password:</strong> ' . $password . '</p>
		<p><a href="' . base_url($cmpR['slug'] . "/account") . '">Click Here to Login</a></p>
		<p>---<br /><strong>Warm Regards</strong><br />' . $cmpR['name'] . '<br />' . base_url($cmpR['slug'] . "/account") . '</p>
		</div>';
			$this->crm_model->send_email($smtp_data);

			// send verification link valid upto 10 days
			$smtp_data['subject'] = 'Student Loan Tool Box - Please Verify Your Account';
			$smtp_data['Msg'] = $cmpR['email_header'] . '<div style="font-family:Calibri; font-size:15px; width:100%; text-align:justify; padding:2px 15px;">
		<p>Welcome ' . $cr['name'] . ' ' . $cr['lname'] . ',</p>
		<p>This email is sent from studentloantoolbox.net on behalf of your case manager. To protect your privacy and confirm you want an account where you can work with us on your Student Loans, we need you to click the below link or copy and paste it into your browser and login. If you do not complete this verification within 10 days, your account will be deleted.</p>
		<p><strong>Verification Link:</strong></p>
		<p><a href="' . base_url($cmpR['slug'] . "/verify_account/" . base64_encode($cr['id'])) . '">' . base_url($cmpR['slug'] . "/verify_account/" . base64_encode($cr['id'])) . '</a></p>
		<p>---<br /><strong>Warm Regards</strong><br />' . $cmpR['name'] . '<br />' . base_url($cmpR['slug'] . "/account") . '</p>
		</div>';
			$this->crm_model->send_email($smtp_data);

		}
	}

//	Send Leads to Case Manager
	public function lead_to_case_manager($client_id = 0) {
		$cr = $this->default_model->getRowArray("SELECT * FROM users where id='$client_id' and role='Customer'");
		if (isset($cr['id'])) {
			$cmr = $this->default_model->getRowArray("SELECT * FROM users where id='" . $cr['parent_id'] . "'");
			if (isset($cmr['id'])) {
				$cmpR = $this->default_model->get_company($cr['company_id']);
				$smtp_data = $this->default_model->get_company_smtp($cr['company_id']);

				$smtp_data['email'] = $cmr['email'];
				//	Send Email to Customer
				$smtp_data['subject'] = 'You Have A New Lead';
				$smtp_data['Msg'] = $cmpR['email_header'] . '<div style="font-family:Calibri; font-size:15px; width:100%; text-align:justify; padding:2px 15px;">
			<p>Congratulations, you have a new lead!</p>
			<p>The following person has registered to seek assistance from your company.</p>
			<p><strong>First Name:</strong> ' . $cr['name'] . '</p>
			<p><strong>Last Name:</strong> ' . $cr['lname'] . '</p>
			<p><strong>Email Address:</strong> ' . $cr['email'] . '</p>
			<p>This lead is currently completing the intake. Once done, you will receive an email alerting you to check their account and review their Analysis.</p>
			<p>---<br /><strong>Regards</strong><br />Support<br />' . base_url($cmpR['slug'] . "/account") . '</p>
			</div>';
				$this->crm_model->send_email($smtp_data);
			}
		}
	}

}
?>