<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Billing_model extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->load->helper('url');
		$this->load->database();

		$this->crm_model->get_site_settings();

	}

//	Check Account Payment Info
	public function check_account_payment_info() {
		$q = $this->db->query("select * from users_company where 1 order by rand() limit 10");
		foreach ($q->result_array() as $row) {
			$billing = $this->crm_model->calculate_billing_amount($row["id"]);
			$billing_amt = $billing['billing_amt'];
			if ($billing_amt > 0) {
				$this->db->query("UPDATE users_company set status='Locked' where id='" . $row['id'] . "'");
			} else {
				$this->db->query("UPDATE users_company set status='Active' where id='" . $row['id'] . "'");
			}
		}

		$q = $this->db->query("select * from account_payment_info where account_name='' order by rand() limit 500");
		foreach ($q->result_array() as $row) {
			$q2 = $this->db->query("SELECT * FROM users_company where id='" . $row['company_id'] . "'");
			$cmpR = $q2->row_array();
			if (isset($cmpR['id'])) {
				$account_name = str_replace("'", "", $cmpR['name']);
				$subscription = $this->db->query("SELECT * FROM users_company where id='" . $row['company_id'] . "'")->row_array();
				if ($subscription['account_type'] == '1') {
					$this->db->query("UPDATE account_payment_info set account_name='" . $account_name . "' where company_id='" . $row['company_id'] . "' and account_payment_info_id='" . $row['account_payment_info_id'] . "'");
				}

			} else {
				$this->db->query("DELETE FROM account_payment_info where account_payment_info_id='" . $row['account_payment_info_id'] . "'");
			}
		}
	}

//	Check Recurring Payment
	public function check_recurring_payment() {
		$q = $this->db->query("SELECT id FROM users_company where next_payment_date!='' and next_payment_date<='" . date('Y-m-d') . "' limit 50");
		$rows = $q->result_array();
		foreach ($rows as $row) {$this->crm_model->check_recurring_payment($row['id']);}
	}

//	Set Company Next Payment Date
	public function set_company_next_payment_date() {
		$q = $this->db->query("select id from users_company where phone='' or email=''");
		foreach ($q->result_array() as $row) {
			$q = $this->db->query("select phone,email from users where id='" . $row['id'] . "'");
			$res = $q->row_array();
			$this->db->query("update users_company set phone='" . $res['phone'] . "',email='" . $res['email'] . "' where id='" . $row['id'] . "'");
		}

		/*$q = $this->db->query("select id,next_payment_date from users_company where (next_payment_date is NULL or next_payment_date='')");
			foreach ($q->result_array() as $row) {
				if ($row['next_payment_date'] == "") {
					$q = $this->db->query("select next_payment_date,last_payment_sent,add_date from users where id='" . $row['id'] . "'");
					$res = $q->row_array();
					$this->db->query("update users_company set add_date='" . $res['add_date'] . "',last_payment_sent='" . $res['last_payment_sent'] . "',next_payment_date='" . $res['next_payment_date'] . "' where id='" . $row['id'] . "'");
				}
			}
		*/
	}

//	Billing Email 1
	public function billing_advance_reminder() {
		$fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
		$reminder_date_from = date('Y-m-d');
		$reminder_date_from = date('Y-m-d', strtotime(($reminder_date_from) . ' + 5 days'));

		$q = $this->db->query("SELECT * FROM users_company WHERE next_payment_date IS NOT NULL AND next_payment_date = '$reminder_date_from' AND last_payment_sent != '" . date('Y-m-d') . "' ORDER BY next_payment_date ASC LIMIT 10");

		// $q = $this->db->query("select * from users_company where next_payment_date!='NULL' and next_payment_date='$reminder_date_from' and last_payment_sent!='".date('Y-m-d')."' order by next_payment_date asc limit 10");
		$rows = $q->result_array();

		foreach ($rows as $row) {

			$this->db->query("update users_company set last_payment_sent='" . date('Y-m-d') . "' where id='" . $row['id'] . "'");

			$total_amount = $this->crm_model->calculate_payment($row['id']);

			if ($total_amount > 0) {
				$cmpR = $this->crm_model->get_company_details($row['id']);
				$smtp_data = $this->crm_model->get_company_smtp_email_details($row['id'], 0, $row['id']);

				$smtp_data['email'] = $row['email'];
				$smtp_data['subject'] = "Subscription Renewal Advance Reminder";
				$smtp_data['Msg'] = $this->crm_model->slt_email_header() . '<div style="font-family:Calibri; font-size:15px; width:100%; max-width:750px; text-align:justify; padding:2px 15px;">
		<p>*** Subscription Renewal Notification ***</p>
		<p>This email is to remind you that your subscription to the Student Loan Toolbox will be expired in 5-days.</p>
<p>for ' . $fmt->formatCurrency($total_amount, 'USD') . '.  This will b charged to your credit card ending with ****' . $row['card_last_four'] . '.</p>
<p>Please be sure that this credit card is still valid to avoid interruption of service to your account.</p>
<p>If you no longer wish to use the Student Loan Toolbox, please login and cancel your subscription immediately. Subscriptions not cancelled within 24-hours of the renewal date will be charged.</p>
<p>Thank you for your continued support o the Student Loan Toolbox.</p>
		</div>';
				$this->crm_model->send_email($smtp_data);

				$this->db->insert('email_warning', ['company_id' => $cmpR['id'], 'email_id' => $cmpR['id'], 'to_whom_email' => $row['email'], 'sent_date' => date('Y-m-d')]);
			}
		}
	}

//	Billing Email 3
	public function billing_reminder_due() {
		$fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
		$reminder_date_from = date('Y-m-d');
		$rdf_1 = date('Y-m-d', strtotime(($reminder_date_from) . ' +30 days')); //	+1 Day
		$rdf_2 = date('Y-m-d', strtotime(($reminder_date_from) . ' +29 days')); //	+2 Day
		$rdf_3 = date('Y-m-d', strtotime(($reminder_date_from) . ' +28 days')); //	+3 Day
		$rdf_4 = date('Y-m-d', strtotime(($reminder_date_from) . ' +27 days')); //	+4 Day
		$rdf_5 = date('Y-m-d', strtotime(($reminder_date_from) . ' +26 days')); //	+5 Day

		$q = $this->db->query("SELECT * FROM users_company WHERE next_payment_date IS NOT NULL AND (next_payment_date = '$rdf_1' OR next_payment_date = '$rdf_2' OR next_payment_date = '$rdf_3' OR next_payment_date = '$rdf_4' OR next_payment_date = '$rdf_5') AND last_payment_sent != '" . date('Y-m-d') . "' ORDER BY next_payment_date ASC LIMIT 10");
		// $q = $this->db->query("select * from users_company where next_payment_date!='NULL' and (next_payment_date='$rdf_1' or next_payment_date='$rdf_2' or next_payment_date='$rdf_3' or next_payment_date='$rdf_4' or next_payment_date='$rdf_5') and last_payment_sent!='".date('Y-m-d')."' order by next_payment_date asc limit 10");
		$rows = $q->result_array();

		foreach ($rows as $row) {
			$this->db->query("update users_company set last_payment_sent='" . date('Y-m-d') . "' where id='" . $row['id'] . "'");

			$q = $this->db->query("SELECT * FROM account_payment_info where company_id='" . $row['id'] . "' limit 1");
			$result = $q->row_array();
			if (isset($result['account_payment_info_id'])) {
				$fdate = date('Y-m-d', strtotime(($row['next_payment_date']) . ' + 5 days'));
				$total_amount = ($result['1st_user_fee'] + $result['additional_user_fee']);
				$cmpR = $this->crm_model->get_company_details($row['id']);
				$smtp_data = $this->crm_model->get_company_smtp_email_details($row['id'], 0, $row['id']);
				$smtp_data['email'] = $row['email'];
				$smtp_data['subject'] = "Subscription Renewal Past Due – Your account is about to be locked.";
				$smtp_data['Msg'] = $this->crm_model->slt_email_header() . '<div style="font-family:Calibri; font-size:15px; width:100%; max-width:750px; text-align:justify; padding:2px 15px;">
		<p>' . date("m/d/Y") . '</p>
		<p>This email is to alert you that the subscription renewal for your Student Loan Toolbox account has not been paid. This may have occurred due to:</p>
		<ol>
			<li>Your credit card has expired</li>
			<li>Your credit card did not accept the charge</li>
		</ol>
		<p>Please login to your account and update the credit card immediately. Failure to do so by ' . date("m/d/Y", strtotime($fdate)) . ' will result in your account being locked. You will be able to login and make payment to reinstate your account. Failure to bring your account current within 30-days will result in your account and all of its data being permanently deleted from the Student Loan Toolbox.</p>
		<p>Regards,</p>
<p>Student Loan Toolbox</p>
		</div>';
				$this->crm_model->send_email($smtp_data);

				$this->db->insert('email_warning', ['company_id' => $cmpR['id'], 'email_id' => $cmpR['id'], 'to_whom_email' => $row['email'], 'sent_date' => date('Y-m-d')]);
			}
		}
	}

//	Billing Email 4 : Your Account is about to be deleted
	public function billing_reminder_due2() {
		$fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
		$reminder_date_from = date('Y-m-d');
		$rdf_1 = date('Y-m-d', strtotime(($reminder_date_from) . ' + 20 days')); //	+10 Day
		$rdf_2 = date('Y-m-d', strtotime(($reminder_date_from) . ' + 15 days')); //	+15 Day
		$rdf_3 = date('Y-m-d', strtotime(($reminder_date_from) . ' + 10 days')); //	+20 Day
		$rdf_4 = date('Y-m-d', strtotime(($reminder_date_from) . ' + 5 days')); //	+25 Day
		$rdf_5 = date('Y-m-d', strtotime(($reminder_date_from) . ' + 1 days')); //	+29 Day
		$q = $this->db->query("SELECT * FROM users_company WHERE next_payment_date IS NOT NULL
                       AND (next_payment_date = '$rdf_1' OR next_payment_date = '$rdf_2'
                            OR next_payment_date = '$rdf_3' OR next_payment_date = '$rdf_4'
                            OR next_payment_date = '$rdf_5')
                       AND last_payment_sent != '" . date('Y-m-d') . "'
                       ORDER BY next_payment_date ASC
                       LIMIT 10");

		// $q = $this->db->query("select * from users_company where next_payment_date!='NULL' and (next_payment_date='$rdf_1' or next_payment_date='$rdf_2' or next_payment_date='$rdf_3' or next_payment_date='$rdf_4' or next_payment_date='$rdf_5') and last_payment_sent!='".date('Y-m-d')."' order by next_payment_date asc limit 10");
		$rows = $q->result_array();

		foreach ($rows as $row) {
			$this->db->query("update users_company set last_payment_sent='" . date('Y-m-d') . "' where id='" . $row['id'] . "'");

			$q = $this->db->query("SELECT * FROM account_payment_info where company_id='" . $row['id'] . "' limit 1");
			$result = $q->row_array();
			if (isset($result['account_payment_info_id'])) {
				$fdate = date('Y-m-d', strtotime(($row['next_payment_date']) . ' + 30 days'));
				$total_amount = ($result['1st_user_fee'] + $result['additional_user_fee']);
				$cmpR = $this->crm_model->get_company_details($row['id']);
				$smtp_data = $this->crm_model->get_company_smtp_email_details($row['id'], 0, $row['id']);
				$smtp_data['email'] = $row['email'];
				$smtp_data['subject'] = "Your account is about to be Deleted";
				$smtp_data['Msg'] = $this->crm_model->slt_email_header() . '<div style="font-family:Calibri; font-size:15px; width:100%; max-width:750px; text-align:justify; padding:2px 15px;">
		<p>' . date("m/d/Y") . '</p>
		<p>This email is to alert you that the subscription renewal for your Student Loan toolbox account has not been paid and your account is about to be permanently deleted.</p>
		<p>Please login to your account and update the credit card immediately. Failure to do so by ' . date("m/d/Y", strtotime($fdate)) . ' will result in your account being permanently deleted. You will not be able to retrieve any of your account or client data once this occurs</p>
		<p>If you have any questions, please contact us.</p>
		<p>Regards,</p>
		<p>Student Loan Toolbox</p>
		</div>';
				$this->crm_model->send_email($smtp_data);

				$this->db->insert('email_warning', ['company_id' => $cmpR['id'], 'email_id' => $cmpR['id'], 'to_whom_email' => $row['email'], 'sent_date' => date('Y-m-d')]);
			}
		}
	}

//	Billing Email 5 : Your Account has been permanently deleted
	public function billing_reminder_due3() {
		$fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
		$reminder_date_from = date('Y-m-d');
		$rdf_1 = date('Y-m-d', strtotime(($reminder_date_from) . ' + 1 days')); // 31 Days

		$sql = "SELECT * FROM users_company WHERE next_payment_date <= '$rdf_1' ORDER BY next_payment_date ASC LIMIT 10";

		// $sql = "select * from users_company where next_payment_date<='$rdf_1' order by next_payment_date asc limit 10";
		$q = $this->db->query($sql);
		$rows = $q->result_array();

		foreach ($rows as $row) {
			$this->db->query("update users_company set last_payment_sent='" . date('Y-m-d') . "' where id='" . $row['id'] . "'");
			$q = $this->db->query("SELECT * FROM account_payment_info where company_id='" . $row['id'] . "' limit 1");
			$result = $q->row_array();
			if (isset($result['account_payment_info_id'])) {
				$cmpR = $this->crm_model->get_company_details($company_id);
				$smtp_data = $this->crm_model->get_company_smtp_email_details($row['id'], 0, $row['id']);
				$smtp_data['email'] = $row['email'];
				$smtp_data['subject'] = "Your Account has been permanently deleted";
				$smtp_data['Msg'] = $this->crm_model->slt_email_header() . '<div style="font-family:Calibri; font-size:15px; width:100%; max-width:750px; text-align:justify; padding:2px 15px;">
		<p>' . date("m/d/Y") . '</p>
		<p>This email is to inform you that your account has been deleted from the Student Loan Toolbox.</p>
		<p>This occurred because you did not respond to the 10 emails sent to you indicating you needed to make a payment before this action was taken.</p>
		<p>Deletion means that your account, users, clients and all related data has been permanently removed from the Student Loan Toolbox system and cannot be retrieved.</p>
		<p>We�re sorry to see you go.</p>
		<p>Regards,</p>
		<p>Student Loan Toolbox</p>
		</div>';

				//$this->crm_model->send_email($smtp_data);
				//$this->crm_model->delete_account(['id'=>$row['id'], 'type'=>'Company']);
			}
		}
	}

}
?>