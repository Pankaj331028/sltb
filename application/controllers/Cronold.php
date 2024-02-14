<?php	defined('BASEPATH') or exit('No direct script access allowed');
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', 1);
extract($_POST);
extract($_GET);

class Cronold extends CI_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->library(array('session', 'email', 'form_validation', 'image_lib', 'pagination'));
		$this->load->helper(array('form', 'url', 'file', 'cookie'));
		$this->load->model(array('front_model', 'default_model', 'crm_model', 'programs_model', 'cron_model', 'billing_model', 'admin_model'));
		/*if (!isset($_SERVER['REMOTE_ADDR']) || (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != '127.0.0.1')) {
			    header("HTTP/1.1 301 Moved Permanently");
			    // header("Location: /");
			    header("Connection: close");
			    die('Access Denied');
			    }
		*/

	}

	public function encrypt_password($psd) {
		echo $this->default_model->psd_encrypt($psd);
	}

	public function stop_intake_old_program() {
		$q = $this->db->query('select * from client_program_progress where (program_id_primary=91 or program_id_primary=127) and status="Pending" and status_1="Pending" and step_due_date < "' . date('Y-m-d', strtotime('-60 days')) . '" ');

		$clients = $q->result_array();

		foreach ($clients as $key => $value) {
			$this->db->query("update client_program_progress set step_completed_date='" . date('Y-m-d') . "', status='Stop' where client_id='" . $value['client_id'] . "' and program_definition_id='" . $value['program_definition_id'] . "'");

			$this->db->query("update client_program_progress set status_1='Stop' where client_id='" . $value['client_id'] . "' and program_id_primary='" . $value['program_id_primary'] . "'");

			$this->db->query("update client_program set status='Stop' where client_id='" . $client_id . "' and program_definition_id='" . $value['program_id_primary'] . "'");

			//    Update Status Flag
			$this->db->where('program_definition_id', $value['program_definition_id']);
			$this->db->update('client_program_progress', ['reminder_status' => 0]);
		}
	}

	public function set_vl_reminder_rule() {
		$ids = $this->db->query('select GROUP_CONCAT(distinct company_id) as ids from vl_reminder_rules where company_id !=0')->row_array();

		if (isset($ids['ids'])) {
			$company_ids = $this->db->query('select id from users where role="Company" and id not in (' . $ids['ids'] . ')')->result_array();
			$reminder_company = $this->db->query("SELECT * FROM vl_reminder_rules where company_id='0'");
			$reminderData = $reminder_company->result_array();

			foreach ($company_ids as $id) {
				foreach ($reminderData as $reminder_data) {
					$reminder_data['company_id'] = $id['id'];
					unset($reminder_data['reminder_rule_id']);
					$this->db->insert('vl_reminder_rules', $reminder_data);
				}
			}
		}
	}

	public function set_intake_complete() {

		$ids = [9003705];
		// $ids = [9003707, 9003740, 9003771, 9003772, 9003775, 9003797, 9003798, 9003801, 9003802, 9003814, 9003815, 9003816, 9003817, 9003821, 9003122];

		foreach ($ids as $id) {

			$this->db->query("update intake_client_status set status='Complete' where client_id='$id' and intake_id=1");
			$client_id = $id;

			$cr = $this->default_model->get_arrby_tbl_single('users', '*', "id='" . $client_id . "'", '1');
			if (isset($cr['id'])) {
				$cmr = $this->default_model->get_arrby_tbl_single('users', '*', "id='" . $cr['parent_id'] . "'", '1');
				if (isset($cmr['id'])) {
					$sql = "SELECT * FROM client_program_progress where client_id='$client_id' and program_id_primary='91' and step_id='2' and status='Pending' limit 1";
					$q = $this->db->query($sql);
					$cppr = $q->row_array();
					if ($cppr['program_definition_id']) {
						$program_id = $cppr['program_definition_id'];
						$this->db->query("update reminder_rules set status_flag='0' where client_id='" . $client_id . "' and step_id='2' and program_id='" . $program_id . "'");
						$this->db->query("update client_program_progress set reminder_status=0 where client_id='" . $client_id . "' and step_id='2' and program_id='" . $program_id . "'");
						$this->crm_model->admin_users_add_program_step($client_id, $cppr['program_definition_id']);
					}

					$cmpr = $this->crm_model->get_company_details($cr['company_id']);
					$ir = $this->default_model->get_arrby_tbl_single('intake', '*', "intake_id='1'", '1');
					$smtp_data = $this->crm_model->get_company_smtp_email_details($cr['company_id'], 0, $cr['parent_id']);
					$review_link = base_url($cmpr['slug'] . "/customer/current_analysis/" . $client_id);

					$smtp_data['email'] = $cmr['email'];
					$smtp_data['subject'] = $ir['intake_title'] . ' is complete by ' . $cr['name'] . ' ' . $cr['lname'];
					$smtp_data['Msg'] = $cmpr['email_header'] . '<div style="font-family:Calibri; font-size:15px; width:100%; text-align:justify; padding:2px 15px;">
		<p>Dear ' . $cmr['name'] . ' ' . $cmr['lname'] . '</p>
		<p>' . $cr['name'] . ' ' . $cr['lname'] . ' has completed <strong>' . $ir['intake_title'] . '</strong> and ready for review.</p>
		<p><a href="' . $review_link . '">Click to Review</a></p>
		<p>Regards,</p>
		<p>' . $cmpr['name'] . '</p>
		</div>';

					$this->crm_model->send_email($smtp_data);
				}
			}
		}
	}

	//    Login default Page
	public function index() {
		$this->crm_model->removeunnecessaryentry(); // Remove Unnecessary Entry

		//    BILLING STRAT
		// $this->billing_model->check_account_payment_info();
		// $this->billing_model->set_company_next_payment_date();
		// $this->billing_model->check_recurring_payment();
		// $this->billing_model->billing_advance_reminder();
		// $this->billing_model->billing_reminder_due();
		// $this->billing_model->billing_reminder_due2();
		// $this->billing_model->billing_reminder_due3();
		//    END BILLING

		$this->check_company_cards();
		$this->check_company_subscription();

		$this->set_company_email_header();
		$this->check_intake_reminder();
		$this->intake_reminder();
		$this->stop_intake_old_program();

		//$this->reminder_rule_digest();
		// $this->customer_6month_reminder();
		$this->refresh_first_program_review_date();
		$this->reminder_rule_digest_vl();

		//$this->cron_send_email_next_step_reminder_to_case_manager();
		//$this->reminder_rule();
		//$this->cron_model->send_report_to_case_manager();

		echo "Cron Successfully Working.";

	}

	public function check_company_cards() {
		// and (id=9001268 or id=9003609)
		// send reminder to companies whose card details are blank
		$companies = $this->db->query('select * from users_company where (stripe_token="" or stripe_card_id="")  and status="Active"')->result_array();

		foreach ($companies as $comp) {

			$smtp_data = $this->crm_model->get_company_smtp_email_details($comp['id'], 0, $comp['id']);
			$smtp_data['email'] = $comp['email'];

			//    Send Email to company for missing card details
			$smtp_data['subject'] = 'Invalid Card Details';

			$smtp_data['Msg'] = '<div style="font-family:Calibri; font-size:15px; width:100%; text-align:justify; padding:2px 15px;">
					<p>Dear ' . $comp['name'] . '</p>
					<p>Card details are missing from your account. Kindly login to your account and go to Payments section to link your card.</p>
					<p>If you have any questions, please contact us.</p>
					<p>Student Loan Toolbox</p>
					</div>';
			$this->crm_model->send_email($smtp_data);
		}

	}

	public function check_company_subscription() {
		$fields = $this->db->query("SELECT * from settings Limit 1")->row_array();
		$fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
		// and (users.id=9001268 or users.id=9003609)
		$companies = $this->db->query('select * from account_payment_info ap join users on users.id = ap.company_id where users.status="Active" and ap.account_type="1"')->result_array();

		foreach ($companies as $comp) {
			$cmpr = $this->db->query('select * from users_company where id=' . $comp['id'])->row_array();

			if ($cmpr['status'] == 'Active') {
				$usr = $this->db->query('select * from users where id=' . $comp['id'])->row_array();

				$next = date('Y-m-d', strtotime($cmpr['next_payment_date']));
				$today = date('Y-m-d');

				// send advance reminder mail
				if ($today == date('Y-m-d', strtotime($next . ' - 5 days'))) {
					$amt = $comp['1st_user_fee'] + $comp['additional_user_fee'];

					$promo_code = $this->get_promo_code($cmpr['id']);

					if ($promo_code['status'] != 'success') {
						if (empty($comp['billed_on'])) {
							$amt = $this->crm_model->calculate_payment($cmpr['id']);
						}

						if ($amt > 0) {
							$cmpR = $this->crm_model->get_company_details($cmpr['id']);
							$smtp_data = $this->crm_model->get_company_smtp_email_details($cmpr['id'], 0, $cmpr['id']);

							$smtp_data['email'] = $cmpr['email'];
							$smtp_data['subject'] = "Student Loan Toolbox Advanced Billing Reminder";
							$smtp_data['Msg'] = $this->crm_model->slt_email_header() . '<div style="font-family:Calibri; font-size:15px; width:100%; max-width:750px; text-align:justify; padding:2px 15px;">
						<p>*** Subscription Renewal Notification ***</p>
						<p>This email is to let you know your card ending with ' . $cmpr['card_last_four'] . ' will be charged on ' . date('d M, Y', strtotime($cmpr['next_payment_date'])) . ' for your next month’s usage of Student Loan Toolbox. You do not need to take any action.</p>
						<p>If you have any questions, please contact <a href="mailto:support@studentloantoolbox.com">support@studentloantoolbox.com</a></p>
						<p>Regards</p>
						<p>Student Loan Toolbox</p>
						</div>';
							$this->crm_model->send_email($smtp_data);

							$this->db->insert('email_warning', ['company_id' => $cmpR['id'], 'email_id' => $cmpR['id'], 'to_whom_email' => $cmpr['email'], 'sent_date' => date('Y-m-d')]);
						}
					}
				} elseif ($today == $next) {
					$amt = $comp['1st_user_fee'] + $comp['additional_user_fee'];

					if (empty($comp['billed_on'])) {
						$amt = $this->crm_model->calculate_payment($cmpr['id']);
					}

					$billing_amt = $amt;
					$promo_code = $this->get_promo_code($cmpr['id']);

					//    Process Auto Checkout
					if ($billing_amt > 0) {
						$checkout = $this->stripe_auto_checkout($cmpr["id"], $promo_code['coupon_code']);
						$billing_amt = $checkout['billing_amt'];
					}

					if ($billing_amt > 0) {
						$discount_amount = $billing['discount_amount'];
						$payment_data = ['id' => $cmpr["id"], 'price' => $billing_amt, 'currency' => 'USD', 'name' => 'Subscription Payment'];

						$postData = [];
						$postData['stripeToken'] = $cmpr['stripe_token'];
						$postData['name'] = $usr["name"];
						$postData['email'] = $usr["email"];
						$postData['product'] = $payment_data;

						// Make payment
						$paymentID = $this->stripe_payment_process($postData, $discount_amount, $promo_code['coupon_code'], $cmpr);

						// If payment successful
						if ($paymentID) {

							$this->db->query("UPDATE users_company set last_payment_sent='" . date('Y-m-d') . "',next_payment_date='" . (date('Y-m-d', strtotime($cmpr['next_payment_date'] . '+30 days'))) . "',status='Active' where id='" . $usr["id"] . "'");

							$this->send_billing_email($usr, $paymentID);
						} else {
							$apiError = !empty($this->stripe_lib->api_error) ? ' (' . $this->stripe_lib->api_error . ')' : '';
							$error_msg = 'Transaction has been failed!' . $apiError;
							$this->send_failed_email($billing_amt, $usr, $error_msg);

						}
					}

					if ($payment_data['price'] <= 0 && $paidAmount > 0) {
						$name = trim($usr["name"] . " " . $usr["lname"]);
						$orderData = array(
							'company_id' => $usr["id"],
							'account_name' => $name,
							'account_email' => $usr["email"],
							'amount_paid' => $paidAmount,
							'discount_amount' => $discount_amount,
							'promo_code' => $promo_code,
							'paid_amount_currency' => 'usd',
							'txn_id' => time(),
							'payment_status' => 'succeeded',
						);

						$this->db->insert("payments", $orderData);
						$orderID = $this->db->insert_id();

						$this->db->query("delete from account_payment_info where company_id='" . $usr["id"] . "'");
					}
				} elseif ($today == date('Y-m-d', strtotime($next . ' + 5 days'))) {
					$amt = $comp['1st_user_fee'] + $comp['additional_user_fee'];

					$promo_code = $this->get_promo_code($cmpr['id']);

					if ($promo_code['status'] != 'success') {
						if (empty($comp['billed_on'])) {
							$amt = $this->crm_model->calculate_payment($cmpr['id']);
						}

						if ($amt > 0) {
							$cmpR = $this->crm_model->get_company_details($cmpr['id']);
							$smtp_data = $this->crm_model->get_company_smtp_email_details($cmpr['id'], 0, $cmpr['id']);

							$smtp_data['email'] = $cmpr['email'];
							$smtp_data['subject'] = "Late Payment Reminder";
							$smtp_data['Msg'] = $this->crm_model->slt_email_header() . '<div style="font-family:Calibri; font-size:15px; width:100%; max-width:750px; text-align:justify; padding:2px 15px;">
						<p>This email is to inform you that your payment to Student Loan Toolbox for ' . $fmt->formatCurrency($amt, 'USD') . ' has failed.</p>
						<p>Please sign into your student loan toolbox account and enter a valid card by ' . date('d M, Y', strtotime($cmpr['next_payment_date'] . '+10 days')) . ' or your account will revert to a Pay As You Go Account which could become more expensive than the monthly subscription.</p>
						<p>If you have any questions, please contact <a href="mailto:support@studentloantoolbox.com">support@studentloantoolbox.com</a></p>
						<p>Regards</p>
						<p>Student Loan Toolbox</p>
						</div>';
							$this->crm_model->send_email($smtp_data);

							$this->db->insert('email_warning', ['company_id' => $cmpR['id'], 'email_id' => $cmpR['id'], 'to_whom_email' => $cmpr['email'], 'sent_date' => date('Y-m-d')]);
						}
					}
				} elseif ($today == date('Y-m-d', strtotime($next . ' + 10 days'))) {

					$promo_code = $this->get_promo_code($cmpr['id']);

					if ($promo_code['status'] != 'success') {
						$this->db->query("UPDATE users_company SET account_type = '0' WHERE id = '" . $cmpr["id"] . "'");
						$this->db->query("UPDATE account_payment_info SET account_type = '0' WHERE company_id = '" . $cmpr["id"] . "'");

						$cmpR = $this->crm_model->get_company_details($cmpr['id']);
						$smtp_data = $this->crm_model->get_company_smtp_email_details($cmpr['id'], 0, $cmpr['id']);

						$smtp_data['email'] = $cmpr['email'];
						$smtp_data['subject'] = "Your Student Loan Toolbox Account Status Has Changed";
						$msg = '';

						if (empty($cmpr['stripe_token']) || empty($cmpr['stripe_card_id'])) {
							$msg = '<p>Please add your card details so that we can charge you according to the rules.</p>';
						}
						$smtp_data['Msg'] = $this->crm_model->slt_email_header() . '<div style="font-family:Calibri; font-size:15px; width:100%; max-width:750px; text-align:justify; padding:2px 15px;">
						<p>This email is to inform you that your account on Student Loan Toolbox has been reverted to a Pay As You Go account. This will allow you to continue to work with your clients, accept new ones and use all the services and features of the Student Loan Toolbox. However, you will be charged for each new client you review at $' . $fields['review_fee'] . ' and $' . $fields['program_fee'] . ' for each client that you add to a program for processing such as consolidation, IDR or Attestation.</p>' . $msg . '
						<p>You can put your account back on Subscription by logging into your account, changing the billing type back to Subscription and making an immediate payment to bring your subscription current.</p>
						<p>If you have any questions, please contact <a href="mailto:support@studentloantoolbox.com">support@studentloantoolbox.com</a></p>
						<p>Regards</p>
						<p>Student Loan Toolbox</p>
						</div>';
						$this->crm_model->send_email($smtp_data);

						$this->db->insert('email_warning', ['company_id' => $cmpR['id'], 'email_id' => $cmpR['id'], 'to_whom_email' => $cmpr['email'], 'sent_date' => date('Y-m-d')]);
					}

				}
			}
		}
	}

	public function send_billing_email($usr, $payment_id = 0) {
		$q = $this->db->query("SELECT * FROM payments where company_id='" . $usr["id"] . "' and payment_id='" . $payment_id . "'");
		$order = $q->row_array();
		if (isset($order['payment_id'])) {
			$fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);

			$query = $this->db->query("SELECT * FROM users_company where id='" . $order['company_id'] . "' limit 1");
			$result = $query->row_array();
			$q = $this->db->query("SELECT * FROM users where company_id='" . $order['company_id'] . "' and role='Company User' limit 1");
			$num = $query->num_rows();

			$smtp_data = $this->crm_model->get_company_smtp_email_details($order['company_id'], 0, $order['company_id']);
			$smtp_data['email'] = $result['email'];

			//    Send Email to Customer
			$smtp_data['subject'] = 'Student Loan Toolbox Receipt';

			$smtp_data['Msg'] = '<div style="font-family:Calibri; font-size:15px; width:100%; text-align:justify; padding:2px 15px;">
				<p>' . date("m/d/Y") . '</p>
				<p>This receipt is from Student Loan Toolbox. Your card ending with XXXX' . $result['card_last_four'] . ' was charged ' . $fmt->formatCurrency($order['amount_paid'], 'USD') . ' for 1 initial user and ' . $num . ' additional users on ' . date('d M, Y', strtotime($order['created_at'])) . '.</p>
				<p>If you have any questions, please contact <a href="mailto:support@studentloantoolbox.com">support@studentloantoolbox.com</a></p>
				<p>Regards</p>
				<p>Student Loan Toolbox</p>
				</div>';
			$this->crm_model->send_email($smtp_data);
		}
	}

	public function send_failed_email($amt, $usr, $error) {

		$fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);

		$query = $this->db->query("SELECT * FROM users_company where id='" . $usr['company_id'] . "' limit 1");
		$result = $query->row_array();

		$smtp_data = $this->crm_model->get_company_smtp_email_details($usr['company_id'], 0, $usr['company_id']);
		$smtp_data['email'] = $result['email'];

		//    Send Email to Customer
		$smtp_data['subject'] = 'Student Loan Toolbox Payment Failed';

		$smtp_data['Msg'] = '<div style="font-family:Calibri; font-size:15px; width:100%; text-align:justify; padding:2px 15px;">
				<p>' . date("m/d/Y") . '</p>
				<p>This email is to inform you that your payment to Student Loan Toolbox for ' . $fmt->formatCurrency($amt, 'USD') . 'on credit card ending with XXXX' . $result['card_last_four'] . ' has failed.</p>
				<p>This may occur if your credit card has expired or does not have enough room to accept the charge.</p>
				<p>Please sign into your student loan toolbox account and enter a valid card by ' . (date('d M, Y', strtotime($result['next_payment_date'] . '+10 days'))) . ' or your account will revert to a Pay As You Go Account which could become more expensive than the monthly subscription.</p>
				<p>If you have any questions, please contact <a href="mailto:support@studentloantoolbox.com">support@studentloantoolbox.com</a></p>
				<p>Regards</p>
				<p>Student Loan Toolbox</p>
				</div>';
		$this->crm_model->send_email($smtp_data);

	}

	public function get_promo_code($id) {
		$sql = "SELECT * FROM promo_code_usage where company_id='" . $id . "' order by id desc";
		$promo = $this->db->query($sql)->row_array();

		if (isset($promo['id'])) {
			$dtm = date("Y-m-d H:i:s");
			$sql = "SELECT * FROM promotional_codes where promo_code='" . $promo['promo_code'] . "' and promo_code_begins<='$dtm' and promo_code_ends>='$dtm' order by id desc limit 1";
			$q = $this->db->query($sql);
			$res = $q->row_array();
		}
		$sql = "SELECT * FROM promo_code_usage where promo_code='" . $promo['promo_code'] . "'";
		$q = $this->db->query($sql);
		$nr = $q->num_rows();
		if ($res['total_redemptions_available'] > $nr) {
			$sql = "SELECT * FROM promo_code_usage where company_id='" . $id . "' and promo_code='" . $promo['promo_code'] . "'";
			$chkq = $this->db->query($sql);
			$chkn = $chkq->num_rows();
			if ($chkn == 0) {
				$this->db->query("update users_company set promo_code='" . $promo['promo_code'] . "' where id='" . $id . "'");
				$status = "Success";
				$error = '';
			}
		}
		if ($status == "Failed") {$this->db->query("update users_company set promo_code='' where id='" . $id . "'");}

		$jdata = array("status" => $status, "message" => $error, "data" => $res, "coupon_code" => $promo['promo_code']);
		return $jdata;
	}

	public function stripe_auto_checkout($id = 0, $promo_code) {
		$status = "Failed";
		$paymentID = "";

		$billing = $this->calculate_billing_amount($id);
		$billing_amt = $billing['billing_amt'];
		$discount_amount = $billing['discount_amount'];

		$sd = $this->crm_model->check_company_stripe_details($id);
		if ($sd == "Valid") {
			$q = $this->db->query("SELECT * FROM users_company where id='" . $id . "'");
			$result = $q->row_array();
			$q = $this->db->query("SELECT * FROM users where id='" . $id . "'");
			$usr = $q->row_array();

			$postData['customer_id'] = $result['stripe_id'];
			$postData['stripeToken'] = $result['stripe_token'];
			$postData['product'] = ['id' => $id, 'price' => $billing_amt, 'currency' => 'USD', 'name' => 'Subscription Payment'];

			// Make payment
			$paymentID = $this->stripe_payment_process($postData, $discount_amount, $promo_code, $result);

			// If payment successful
			if ($paymentID) {
				$billing_amt = 0;
				$status = "Success";
				$this->db->query("UPDATE users_company set last_payment_sent='" . date('Y-m-d') . "',next_payment_date='" . (date('Y-m-d', strtotime($result['next_payment_date'] . '+30 days'))) . "',status='Active' where id='" . $usr["id"] . "'");
				$this->send_billing_email($usr, $paymentID);
			}
		}

		return ["status" => $status, "paymentID" => $paymentID, "billing_amt" => $billing_amt];
	}
	public function stripe_payment_process($postData, $discount_amount, $promo_code, $cmpr) {

		$token = $postData['stripeToken'];
		$name = $postData['name'];
		$email = $postData['email'];

		// Add customer to stripe
		$customer = $this->stripe_lib->addCustomer($name, $email, $token);

		if ($customer) {
			// Charge a credit or a debit card
			$charge = $this->stripe_lib->createCharge($customer->id, $postData['product']['name'], $postData['product']['price']);

			if ($charge) {
				// Check whether the charge is successful
				if ($charge['amount_refunded'] == 0 && empty($charge['failure_code']) && $charge['paid'] == 1 && $charge['captured'] == 1) {
					// Transaction details
					$transactionID = $charge['balance_transaction'];
					$paidAmount = $charge['amount'];
					$paidAmount = ($paidAmount / 100);
					$paidCurrency = $charge['currency'];
					$payment_status = $charge['status'];

					// Insert tansaction data into the database
					$orderData = array(
						'company_id' => $cmpr["id"],
						'account_name' => $name,
						'account_email' => $email,
						'amount_paid' => $paidAmount,
						'discount_amount' => $discount_amount,
						'promo_code' => $promo_code,
						'paid_amount_currency' => $paidCurrency,
						'txn_id' => $transactionID,
						'payment_status' => $payment_status,
					);
					//$orderID = $this->product->insertOrder($orderData);
					$this->db->insert("payments", $orderData);
					$orderID = $this->db->insert_id();
					$this->db->query("UPDATE users_company set last_payment_sent='" . date('Y-m-d') . "',next_payment_date='" . (date('Y-m-d', strtotime($result['next_payment_date'] . '+30 days'))) . "',status='Active' where id='" . $cmpr["id"] . "'");

					$this->db->query("delete from account_payment_info where company_id='" . $cmpr["id"] . "'");

					// If the order is successful
					if ($payment_status == 'succeeded') {
						//$orderID = '1561';
						return $orderID;
					}
				}
			}
		}
	}

	//
	public function refresh_first_program_review_date() {
		$q = $this->db->query("update clients set date_of_first_program = null where date_of_first_program ='" . date('Y-m-d', strtotime('-180 days')) . "'");
		$q = $this->db->query("update clients set date_initially_viewed = null where date_initially_viewed ='" . date('Y-m-d', strtotime('-180 days')) . "'");

	}
	//    Check Client Status
	public function check_client_status() {
		$this->cron_model->check_client_status();
	}

	//    Check Intake Status Client
	public function check_intake_status_client() {
		$this->cron_model->check_intake_status_client();
	}

	//    Check Client Current Programs
	public function check_client_current_program() {
		$this->cron_model->check_client_current_program();
	}

	//    Set Company Email Header
	public function set_company_email_header() {
		$q = $this->db->query("select id from users_company where email_header='' order by rand() limit 500");
		foreach ($q->result_array() as $row) {$this->crm_model->admin_company_email_header_update($row['id']);}
	}

	//    Check Initial Intake Status
	public function check_intake_reminder() {
		$q = $this->db->query("select id from users where role='Customer' order by rand() limit 500");
		foreach ($q->result_array() as $row) {
			$icsR = $this->default_model->get_arrby_tbl_single('intake_client_status', '*', "intake_id='1' and client_id='" . $row['id'] . "'", '1');
			if (!isset($icsR['id'])) {$this->crm_model->admin_send_intake_email($row['id'], "1");}
		}
	}

	//    Cron - Intake Reminder
	public function intake_reminder() {
		$q = $this->db->query("select id,client_id,intake_id from intake_client_status where status='Pending' and last_sent_reminder<='" . date('Y-m-d') . "' order by id asc limit 10");
		foreach ($q->result_array() as $row) {
			$this->crm_model->admin_send_intake_email($row['client_id'], $row['intake_id']);
		}
	}

	//    Cron - (Schedule-Payment Reminder) Analysis Saved � Perform Follow UP
	public function schedule_payment_reminder_client_analysis() {
		$q = $this->db->query("select id,client_id,company_id,intake_id,nslds_id from client_analysis_results where status='Saved' and last_sent_reminder<='" . date('Y-m-d') . "' order by id asc limit 10");
		foreach ($q->result_array() as $row) {
			$this->crm_model->send_email_analysis_saved_perform_follow_up_schedule_payment_reminder($row['client_id'], $row['company_id'], $row['intake_id'], $row['nslds_id']);
		}
	}

	//    3. Post-Analysis Follow Up Reminder to Client
	//    4. Next Step Reminder: to Case Manager
	//    Note, this reminder should be sent every day until the Client who caused the generation of this email is active in at least one Program.
	public function cron_send_email_next_step_reminder_to_case_manager() {
		$q = $this->db->query("select * from client_program where program_definition_id='91' and  status='Pending' and last_sent_reminder<='" . date('Y-m-d') . "' order by id asc limit 10");
		foreach ($q->result_array() as $row) {
			//$this->crm_model->send_email_next_step_reminder_to_case_manager($row['client_id'], $row['program_definition_id']);
			//    3. Post-Analysis Follow Up Reminder to Client
			//$this->crm_model->send_email_post_analysis_follow_up_reminder_to_client($row['client_id'], $row['program_definition_id']);
		}
	}

	//    Cron - Reminder Rule
	/*public function reminder_rule() {
	    $arr_programs = array();
	    $q = $this->db->query("select program_definition_id,step_id,program_title,step_name from program_definitions where 1");
	    foreach ($q->result_array() as $row) {
	    $id1 = $row['program_definition_id'];
	    $step_id = $row['step_id'];
	    $arr_programs[$id1][$step_id] = $row;
	    }

	    $reminder_date_from = date('Y-m-d');
	    $reminder_date_from = date('Y-m-d', strtotime(($reminder_date_from) . ' + 3 days'));

	    //    Send Email To Client
	    $q = $this->db->query("select * from reminder_rules where status_flag='1' and to_whom='0' and reminder_date_from<='$reminder_date_from' and last_sent!='" . date('Y-m-d') . "' order by reminder_date_from asc limit 1000");
	    $rows = $q->result_array();
	    foreach ($rows as $row) {
	    $program_id = $row['program_id'];
	    $step_id = $row['step_id'];
	    $col_arr = ["reminder_rule_id" => $row['reminder_rule_id'], "company_id" => $row['company_id'], "client_id" => $row['client_id'], "program_title" => $arr_programs[$program_id][$step_id]['program_title'], "step_id" => $row['step_id'], "sent_to" => $row['sent_to']];
	    $this->db->insert('reminder_history', $col_arr); //    Insert Record

	    // done by vl
	    $cm = $this->db->query('select * from users cm join users cl on cl.parent_id=cm.id and cl.id=' . $row['client_id'])->row_array();

	    $this->db->query("update reminder_rules set last_sent='" . date('Y-m-d') . "' where reminder_rule_id='" . $row['reminder_rule_id'] . "'");
	    $cmpR = $this->crm_model->get_company_details($row['company_id']);
	    $smtp_data = $this->crm_model->get_company_smtp_email_details($row['company_id'], 0, $cm['id']);
	    $smtp_data['email'] = $row['sent_to'];
	    $smtp_data['subject'] = $row['reminder_email_subject'];
	    $smtp_data['Msg'] = $cmpR['email_header'] . str_ireplace("<today_date>", date('m/d/Y'), $row['reminder_email_body']);
	    $this->crm_model->send_email($smtp_data);

	    if ($row['to_whom'] == "0") {
	    $col_arr = ["reminder_rule_id" => $row['reminder_rule_id'], "company_id" => $row['company_id'], "client_id" => $row['client_id'], "program_title" => $arr_programs[$program_id][$step_id]['program_title'], "step_name" => $arr_programs[$program_id][$step_id]['step_name'], "step_no" => $row['step_id'], "due_date" => $row['reminder_date_from'], "sent_to" => $row['sent_to'], "subject" => $smtp_data['subject'], "email_body" => $smtp_data['Msg']];
	    $this->db->insert('client_reminder_status', $col_arr); //    Insert Record
	    }

	    }
	    }
*/
	public function reminder_rule_digest_vl() {
		$q = $this->db->query("select * from client_program_progress where reminder_status=1 and (client_id,program_id_primary,step_id) in (select client_id,program_id_primary,max(step_id) as step_id from client_program_progress cp join users on users.id=cp.client_id where users.status='Active' and cp.step_completed_date is null and reminder_status=1 group by client_id,program_id_primary) ORDER BY `program_id_primary`,`step_id` desc");

		$array_total = $q->result_array();

		$array_total_rows = $q->num_rows();

		$case_manager_array = [];

		for ($array_row_id = 1; $array_row_id <= $array_total_rows; $array_row_id++) {
			$program_id = $array_total[$array_row_id - 1]['program_id'];
			$step_id = $array_total[$array_row_id - 1]['step_id'];
			$company_id = $array_total[$array_row_id - 1]['company_id'];

			$client = $this->db->query('select * from users where id=' . $array_total[$array_row_id - 1]['client_id'])->row_array();
			$program = $this->db->query('select * from program_definitions where program_definition_id=' . $program_id)->row_array();
			$cm = $this->db->query('select * from users where id=' . $client['parent_id'])->row_array();
			$company = $this->db->query('select * from users_company where id=' . $company_id)->row_array();

			if ($company['status'] == 'Active') {

				$q = $this->db->query("select * from vl_reminder_rules where company_id=" . $company_id . " and program_id=" . $program_id . " and step_id=" . $step_id . " and status_flag='1'");

				if ($q->num_rows() > 0) {
					$rules = $q->result_array();
					$date = $array_total[$array_row_id - 1]['created_at'];

					foreach ($rules as $rule) {
						// step 9
						$step9_result = date('Y-m-d', strtotime($date . " + " . ($rule['days_to_send'] + $rule['stop_sending_days']) . " days"));

						if (date('Y-m-d') < $step9_result) {
							// step 10
							$step10_result = date('Y-m-d', strtotime($date . " + " . $rule['days_to_send'] . " days"));
							$step11_result = 0;

							if (date('Y-m-d') > $step10_result) {
								// step 11
								$date1 = date_create($step10_result);
								$date2 = date_create(date('Y-m-d'));
								$diff = date_diff($date1, $date2);

								if ($rule['send_frequency'] > 0 && $diff > 0) {
									$step11_result = $diff % $rule['send_frequency'];
								}

							}

							if (date('Y-m-d') == $step10_result || $step11_result == 0) {
								// step 12
								if ($rule['to_whom'] != 0) {
									// step 13
									// set $data with information necessary to send digest mail

									$data = [
										'rule' => $rule,
										'cpp' => $array_total[$array_row_id - 1],
										'client' => $client,
										'program' => $program,
										'cm' => $cm,
									];

									if (!isset($case_manager_array[$cm['id']])) {
										$case_manager_array[$cm['id']] = [];
									}

									$case_manager_array[$cm['id']][] = $data;
								}
								if (in_array($rule['to_whom'], [0, 2])) {
									// step 15

									if ($rule['from_whom'] == 0) {
										// step 15a
										$smtp['from_email'] = 'support@studentloantoolbox.com';

									} elseif ($rule['from_whom'] == 1) {
										// step 15b

										$smtp = $this->crm_model->get_company_smtp_email_details($company_id, 0, $cm['id']);
										$smtp['from_email'] = $cm['email'];
									}

									// send mail to client
									try {
										// step 16
										$smtp['email'] = $client['email'];
										$subject = $rule['reminder_email_subject'];
										$body = $rule['reminder_email_body'];

										$subject = str_ireplace('<Client Name>', $client['name'] . ' ' . $client['lname'], $subject);

										$iR = $this->db->query('select * from intake where program_definition_id=' . ($program_id - $step_id + 1))->row_array();

										if (isset($iR['intake_slug'])) {
											if ($iR['intake_id'] == "1") {
												$intake_link = base_url($company['slug'] . "/" . $iR['intake_slug'] . "?intake_page_no=1&company=" . $company['slug']);
											} else {
												$intake_link = base_url($company['slug'] . "/" . $iR['intake_slug'] . "?intake_page_no=1");
											}
											$icsR = $this->crm_model->client_intake_client_status($client['id'], $iR['id']);

											$cl_ec_id = $client['id'] . "." . $iR['id'] . "." . $icsR['id'];
											$cl_ec = strtr(base64_encode($cl_ec_id), '+/', '-_');
											$srl = base_url($cmpR['slug'] . "/" . $iR['intake_slug'] . "/stop/" . $cl_ec);

											$stop_reminder_link = '<a href="' . $srl . '" style="background:#FF0000; color:#FFFFFF; padding:5px 20px; font-weight:bold; text-decoration:none;">STOP REMINDER</a>';
										} else {
											$intake_link = '';
											$q = $this->db->query("select * from client_program where status='Pending' and client_id='" . $client['id'] . "' and program_definition_id='$program_id'");
											$cpr = $q->row_array();

											$cl_ec_id = $client['id'] . "." . $program_id . "." . $cpr['id'];
											$cl_ec = strtr(base64_encode($cl_ec_id), '+/', '-_');
											$srl = base_url($company['slug'] . "/program/stop/" . $cl_ec);
											$stop_reminder_link = '<a href="' . $srl . '" style="background:#FF0000; color:#FFFFFF; padding:5px 20px; font-weight:bold; text-decoration:none;">STOP REMINDER</a>';
										}

										$review_link = base_url($company['slug'] . "/customer/current_analysis/" . $client['id']);
										$program_url = base_url($company['slug'] . "/program/" . $program_id);

										$body = str_ireplace('{Client Name}', $client['name'] . ' ' . $client['lname'], $body);
										$body = str_ireplace('{Intake Link}', $intake_link, $body);
										$body = str_ireplace('{Stop Reminder Link}', $stop_reminder_link, $body);
										$body = str_ireplace('{Company Name}', $company['name'], $body);
										$body = str_ireplace('{Case Manager Name}', $cm['name'] . ' ' . $cm['lname'], $body);
										$body = str_ireplace('{Intake Program Title}', $iR['intake_title'], $body);
										$body = str_ireplace('{Review Link}', $review_link, $body);
										$body = str_ireplace('{Analysis Fees}', $company['analysis_fee'], $body);
										$body = str_ireplace('{Company Payment Link}', $company['payment_link'], $body);
										$body = str_ireplace('{Case Manager Email}', $cm['email'], $body);
										$body = str_ireplace('{Company Account URL}', base_url($company['slug'] . "/account"), $body);
										$body = str_ireplace('{recertification date}', $client['recertification_date'], $body);
										$body = str_ireplace('{client portal link}', base_url($company['slug'] . "/account"), $body);
										$body = str_ireplace('{client portal url}', base_url($company['slug'] . "/account"), $body);
										$body = str_ireplace('{Program URL}', $program_url, $body);

										$smtp['subject'] = $subject;
										$smtp['Msg'] = $body;

										$email = $this->crm_model->send_email($smtp);

										// step 19
										if ($email == 'Success') {
											$col_arr = [
												'company_id' => $company_id,
												'client_id' => $client['id'],
												'program_title' => $program['program_title'],
												'step_id' => $rule['step_id'],
												'sent_to' => $client['id'],
											];

											$this->db->insert('vl_reminder_history', $col_arr);
										}

									} catch (\Exception $e) {
										// step 17

										$smtp['from_email'] = 'support@studentloantoolbox.com';
										$smtp['email'] = 'support@studentloantoolbox.com,apoorva.verve123@gmail.com';
										$smtp['subject'] = 'Error sending email reminder';
										$smtp['Msg'] = 'Dear Admin,<br/>There was an issue in sending email reminder to a client, ' . $client['name'] . ' ' . $client['lname'] . ' (ID:' . $client['id'] . ') with subject: ' . $rule['reminder_email_subject'] . '. Please find below the email details:<br><br><p>Reminder Rule ID: ' . $rule['reminder_rule_id'] . '<br>Client ID: ' . $client['id'] . '<br>Program Name: ' . $program['program_title'] . '<br>Step Number: ' . $rule['step_id'] . '<br>Error Message: ' . $e->getMessage() . '</p>';

										$email = $this->crm_model->send_email($smtp);
									}
								}
							}
						}
					}
				}
			}
		}
		// echo '<pre>';
		// print_r($case_manager_array);die;
		// step 22
		foreach ($case_manager_array as $id => $rows) {
			$q = $this->db->query("select * from users_company where id = $id");
			$userc = $q->row_array();
			$q = $this->db->query("select * from users where id = $id");
			$comp = $q->row_array();
			$q = $this->db->query("select * from users where id = " . $comp['company_id']);
			$company = $q->row_array();
			$cmpr = $this->db->query('select * from users_company where id=' . $company['id'])->row_array();

			$body = '';
			$message = '';

			foreach ($rows as $row) {
				$program_id = $row['rule']['program_id'];
				$step_id = $row['rule']['step_id'];
				$program_title = $row['program']['program_title'];
				$step_name = $row['program']['step_name'];

				$body .= '<tr><td><a href="' . base_url($cmpr['slug'] . "/customer/view/" . $row['client']['id']) . '" target="_blank">' . $row['client']['lname'] . ', ' . $row['client']['name'] . '</a></td><td>' . $row['rule']['reminder_email_subject'] . '</td><td>' . $program_title . '</td>	<td>' . $step_id . '</td><td>' . date('m/d/Y', strtotime($row['cpp']['step_due_date'])) . '</td>	</tr>';
			}
			try {
				// step 23
				$message = '<p>' . date('m/d/Y') . '</p><p>Dear ' . $company['name'] . ' ' . $company['lname'] . '</p><p>This email is to notify you that you have tasks due.</p><table cellpadding="5" cellspacing="0" border="1"><tr><th>Client Name</th><th>Reminder Name</th><th>Program Name</th>	<th>Step Number</th>	<th>Due Date</th>	</tr>' . $body . '</table><p>Please complete these task as quickly as possible to avoid delays, missing any applicable deadlines or causing client concerns.</p><p>Regards,</p><p>' . $userc['name'] . '</p>';
				// echo $body;die;
				$smtp = $this->crm_model->get_company_smtp_email_details($company['id'], 0, $id);
				$smtp['email'] = $comp['email'];
				$smtp['subject'] = 'Your Student Loan Toolbox Reminder Digest';
				$smtp['Msg'] = $userc['email_header'] . $message;
				if ($userc['status'] == "Active") {
					$email = $this->crm_model->send_email($smtp);

					if ($email == 'Success') {

						// step 25
						foreach ($rows as $row) {
							$col_arr = [
								'company_id' => $company['id'],
								'client_id' => $row['client']['id'],
								'program_title' => $row['program']['program_title'],
								'step_id' => $row['rule']['step_id'],
								'sent_to' => $id,
							];

							$this->db->insert('vl_reminder_history', $col_arr);
						}

					}
				}

			} catch (\Exception $e) {

				// step 24
				$smtp['from_email'] = 'support@studentloantoolbox.com';
				$smtp['email'] = 'support@studentloantoolbox.com,apoorva.verve123@gmail.com';
				$smtp['subject'] = 'Error sending email reminder digest';
				$smtp['Msg'] = 'Dear Admin,<br/>There was an issue in sending email reminder to case manager, ' . $row['cm']['name'] . ' ' . $row['cm']['lname'] . ' (ID:' . $row['cm']['id'] . ', Email: ' . $row['cm']['email'] . ') with subject: Task Reminder. Please find below the digest report with error message:<br><br><p>Error Message: ' . $e->getMessage() . '</p><p>' . $message . '</p>';

				$email = $this->crm_model->send_email($smtp);
			}
		}

		$q = $this->db->query("select * from vl_reminder_rules where program_id=0 and company_id!=0 and status_flag='1'");

		if ($q->num_rows() > 0) {
			$rules = $q->result_array();

			foreach ($rules as $rule) {

				$keyfield = explode('.', $rule['key_field']);
				$table = $keyfield[0];
				$field = $keyfield[1];

				$clients = $this->db->query('select * from users where role="Customer" and company_id=' . $rule['company_id'] . ' and status="Active"')->result_array();

				foreach ($clients as $client) {
					if ($table == 'users') {
						$date_arr = $this->db->query('select * from ' . $table . ' where id=' . $client['id'])->row_array();
					} else {
						$date_arr = $this->db->query('select * from ' . $table . ' where client_id=' . $client['id'])->row_array();
					}

					$date = $date_arr[$field];
					if (!empty($date)) {
						$cm = $this->db->query('select * from users where id=' . $client['parent_id'])->row_array();

						// step 9
						$step9_result = date('Y-m-d', strtotime($date . " + " . ($rule['days_to_send'] + $rule['stop_sending_days']) . " days"));

						if (date('Y-m-d') < $step9_result) {
							// step 10
							$step10_result = date('Y-m-d', strtotime($date . " + " . $rule['days_to_send'] . " days"));
							$step11_result = 0;

							if (date('Y-m-d') > $step10_result) {
								// step 11
								$date1 = date_create($step10_result);
								$date2 = date_create(date('Y-m-d'));
								$diff = date_diff($date1, $date2);

								if ($rule['send_frequency'] > 0 && $diff > 0) {
									$step11_result = $diff % $rule['send_frequency'];
								}

							}

							$emails = [];
							if (date('Y-m-d') == $step10_result || $step11_result == 0) {
								// step 12
								if ($rule['to_whom'] != 0) {
									// step 13

									$emails[] = $cm['email'];
								}
								if (in_array($rule['to_whom'], [0, 2])) {
									// step 15

									$emails[] = $client['email'];

								}
								$smtp['email'] = $emails;

								// send mail to client
								try {
									if ($rule['from_whom'] == 0) {
										// step 15a
										$smtp['from_email'] = 'support@studentloantoolbox.com';

									} elseif ($rule['from_whom'] == 1) {
										// step 15b

										$smtp = $this->crm_model->get_company_smtp_email_details($company_id, 0, $cm['id']);
										$smtp['from_email'] = $cm['email'];
									}
									// step 16
									$smtp['subject'] = $rule['reminder_email_subject'];
									$smtp['Msg'] = $rule['reminder_email_body'];

									$email = $this->crm_model->send_email($smtp);

									// step 19
									if ($email == 'Success') {

										if ($rule['to_whom'] != 1) {
											$col_arr = [
												'company_id' => $rule['company_id'],
												'client_id' => $client['id'],
												'program_title' => ' ',
												'step_id' => 0,
												'sent_to' => $client['id'],
											];

											$this->db->insert('vl_reminder_history', $col_arr);
										}

										if ($rule['to_whom'] != 0) {
											$col_arr = [
												'company_id' => $rule['company_id'],
												'client_id' => $client['id'],
												'program_title' => ' ',
												'step_id' => 0,
												'sent_to' => $cm['id'],
											];

											$this->db->insert('vl_reminder_history', $col_arr);
										}
									}

								} catch (\Exception $e) {
									// step 17

									$smtp['from_email'] = 'support@studentloantoolbox.com';
									$smtp['email'] = 'support@studentloantoolbox.com';
									$smtp['subject'] = 'Error sending email reminder';
									$smtp['Msg'] = 'Dear Admin,<br/>There was an issue in sending email reminder with subject: ' . $rule['reminder_email_subject'] . '. Please find below the email details:<br><br><p>Reminder Rule ID: ' . $rule['reminder_rule_id'] . '<br>Error Message: ' . $e->getMessage() . '</p>';

									$email = $this->crm_model->send_email($smtp);
								}
							}
						}
					}
				}

			}
		}

	}

	//    Cron - Reminder Rule Digest
	public function reminder_rule_digest() {
		$arr_programs = $arr_cmpR = $arr_smtp_data = $arr_company_id = $arr_reminder_email = array();
		$q = $this->db->query("select program_definition_id,step_id,program_title,step_name from program_definitions where 1");
		foreach ($q->result_array() as $row) {
			$id1 = $row['program_definition_id'];
			$step_id = $row['step_id'];
			$arr_programs[$id1][$step_id] = $row;
		}

		$reminder_date_from = date('Y-m-d');
		$reminder_date_from = date('Y-m-d', strtotime(($reminder_date_from) . ' + 3 days'));

		//    Get Company List
		$q = $this->db->query("select distinct(company_id) as company_id from reminder_rules where status_flag='1'and reminder_date_from<='$reminder_date_from' and last_sent!='" . date('Y-m-d') . "' order by reminder_date_from asc limit 100");
		$n = $q->num_rows();
		if ($n > 0) {
			$rows = $q->result_array();
			foreach ($rows as $row) {
				$company_id = $row['company_id'];
				$arr_company_id[$company_id] = $company_id;}

			$tmp_ids = implode(",", $arr_company_id);

			//    Get Company Details
			$q = $this->db->query("select * from users_company where id in ($tmp_ids)");
			$rows = $q->result_array();
			foreach ($rows as $row) {
				$company_id = $row['id'];
				$arr_cmpR[$company_id] = $row;}

			//    Get Company SMTP Details
			$q = $this->db->query("select * from users_company_smtp_email where id in ($tmp_ids)");
			$rows = $q->result_array();
			foreach ($rows as $row) {
				$company_id = $row['id'];
				$arr_smtp_data[$company_id] = $row;}

			//    Get Reminder List
			$q = $this->db->query("select * from reminder_rules where (company_id in ($tmp_ids)) and status_flag='1'and reminder_date_from<='$reminder_date_from' and last_sent!='" . date('Y-m-d') . "'");
			$rows = $q->result_array();
			foreach ($rows as $row) {
				$company_id = $row['company_id'];
				$sent_to = $row['sent_to'];
				$arr_reminder_email[$company_id][$sent_to][] = $row;
			}

			//    Get Client/Case Manager Details
			$q = $this->db->query("select id,role,name,lname,email,company_id,parent_id from users where company_id in ($tmp_ids) and (role='Company' or role='Company User' or role='Customer')");
			$rows = $q->result_array();
			foreach ($rows as $row) {
				$company_id = $row['company_id'];
				$email = $row['email'];
				$arr_users[$company_id][$email] = $row;}

			//    Compose Email
			foreach ($arr_reminder_email as $company_id => $rr) {
				$cmpR = $arr_cmpR[$company_id];
				$smtp_data = $arr_smtp_data[$company_id];

				$tr = "";
				foreach ($rr as $email => $rows) {
					foreach ($rows as $row) {
						$user = $arr_users[$company_id][$email];

						$program_id = $row['program_id'];
						$step_id = $row['step_id'];
						$program_title = $arr_programs[$program_id][$step_id]['program_title'];
						$step_name = $arr_programs[$program_id][$step_id]['step_name'];

						$tr .= '<tr>Client_Name_TD<td>' . $program_title . '</td>	<td>' . $step_id . '</td>	<td>' . $step_name . '</td>	<td>' . date('m/d/Y', strtotime($row['reminder_date_from'])) . '</td>	</tr>';
					}
				}

				$usr = $arr_users[$company_id][$email];

				$name = $usr['lname'] . ", " . $usr['name'];
				if ($usr['role'] == "Customer") {
					$client_name = $client_name_h = "";

					$q = $this->db->query("select name,lname,phone,email from users where id='" . $usr['parent_id'] . "'");
					$cmr = $q->row_array();
					$msg__ = '<p>If you have any questions, please contact ' . $cmr['lname'] . ', ' . $cmr['name'] . ' at <a href="' . $cmr['email'] . '">' . $cmr['email'] . '</a> or by calling ' . $cmr['phone'] . '.</p>';
				} else {
					$msg__ = "";
					$client_name = $name;
					$client_name_h = "<th>Client Name</th>";}

				$tr = str_ireplace("Client_Name_TD", $client_name, $tr);
				$message = '<p>' . date('m/d/Y') . '</p><p>Dear ' . $name . '</p><p>This email is to remind you that you have a task due.</p><table cellpadding="5" cellspacing="0" border="1"><tr>' . $client_name_h . '	<th>Program Name</th>	<th>Step Number</th>	<th>Step Name</th>	<th>Due Date</th>	</tr>' . $tr . '</table><p>Please complete the task as quickly as possible to avoid delays in processing.</p>' . $msg__ . '<p>Regards,</p>
			<p>' . $cmpR['name'] . '</p>';

				$smtp_data['email'] = $email;
				$smtp_data['subject'] = $subject = 'Task Reminder';
				$smtp_data['Msg'] = $Msg = $cmpR['email_header'] . $message;
				if ($cmpR['status'] == "Active") {$this->crm_model->send_email($smtp_data);}

				$this->db->query("update reminder_rules set last_sent='" . date('Y-m-d') . "' where company_id='$company_id' and sent_to='$email'");

				$col_arr = ["company_id" => $company_id, "client_id" => $usr['id'], "program_title" => $program_title, "step_id" => $step_id, "sent_to" => $email];
				$this->db->insert('reminder_history', $col_arr); //    Insert Record

				$col_arr = ["company_id" => $company_id, "client_id" => $usr['id'], "sent_to" => $email, "subject" => $subject, "email_body" => $Msg];
				$this->db->insert('client_reminder_status', $col_arr); //    Insert Record
			}
		}
	}

	//    6 Month reminder
	public function customer_6month_reminder() {
		$fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
		$reminder_date_from = date('Y-m-d');
		$rdf_1 = date('Y-m-d', strtotime(($reminder_date_from) . ' - 180 days')); // 180 Days (6 Months)
		$sql = "select * from users where role='Customer' and add_date='" . $rdf_1 . "' and last_payment_sent!='" . date('Y-m-d') . "' order by id asc limit 10";
		$q = $this->db->query($sql);
		$rows = $q->result_array();

		foreach ($rows as $row) {
			$this->db->query("update users set last_payment_sent='" . date('Y-m-d') . "' where id='" . $row['id'] . "'");

			$q = $this->db->query("SELECT * FROM users where id='" . $row['parent_id'] . "' limit 1");
			$cmr = $q->row_array();

			$cmpr = $this->crm_model->get_company_details($row['company_id']);
			$smtp_data = $this->crm_model->get_company_smtp_email_details($row['company_id'], 0, $cmr['id']);
			$smtp_data['email'] = $row['email'];
			$smtp_data['subject'] = "Hoping things are doing well";
			$smtp_data['Msg'] = $this->crm_model->slt_email_header() . '<div style="font-family:Calibri; font-size:15px; width:100%; max-width:750px; text-align:justify; padding:2px 15px;">
				<h3>' . $cmpr['name'] . '</h3>
				<p>Hello ' . $row['name'] . ',</p>
				<p>It�s been 6-months since ' . $cmpr['name'] . ' assisted you with your student loan concerns. We wanted to check in and see how you are doing.</p>
				<p>If your situation has improved, great! We�re very happy for you.  If not, and things have gotten worse, it might be a good idea for us to conduct a free review to see if we can lower your payment or help you resolve your worsening situation.</p>
				<p>You do not need to wait for your recertification if things are not going well in order to get more help.</p>
				<p>If any of the following are occurring, you may b able to lower your payment more:</p>
				<ul>
				<li>Received a reduction in your pay or hours</li>
				<li>Have become unemployed</li>
				<li>Increased your family size via marriage, birth or adoption</li>
				<li>Changed jobs to one which may qualify for Public Service Loan Forgiveness</li>
				<li>Have had your wages garnished</li>
				</ul>
				<p>If any of these occurred, contact us for a free review.</p>
				<p>Otherwise, if you are required to recertify, we will contact you in 3-months to get the process started so you recertify in time. You will also receive a note from your federal loan servicer for recertification prior to its due date.</p>
				<p>If you have any questions, please contact me at <a href="mailto:' . $cmr['email'] . '">' . $cmr['email'] . '</a></p>
				<p>Regards,<br />' . $cmr['name'] . ' ' . $cmr['lname'] . '</p>
			</div>';

			$this->crm_model->send_email($smtp_data);
		}
	}

	//    Check IDR/Consolidation Intake Status
	public function check_intake_idr_consoliation_intake() {
		$tmp_arr = array("1" => "Inital Intake", "2" => "IDR Intake", "3" => "Consoliation Intake");
		$tmp_arr = array("2" => "IDR Intake", "3" => "Consoliation Intake");
		foreach ($tmp_arr as $intake_id => $ir) {
			if ($intake_id == 2) {$program_id_primary = 23;} else if ($intake_id == 3) {$program_id_primary = 1;} else { $program_id_primary = "";}

			$i = 1;
			echo '<table cellpadding="5" cellspacing="0" border="1"><tr><td colspan="6"><strong>' . $ir . '</strong></td></tr>';
			$q = $this->db->query("select * from intake_client_status where intake_id='$intake_id' order by client_id asc");
			foreach ($q->result_array() as $row) {
				$clR = $this->default_model->get_arrby_tbl_single('users', '*', "id='" . $row['client_id'] . "'", '1');
				$qry = $this->db->query("SELECT * FROM client_program_progress where program_id_primary='" . $program_id_primary . "' and client_id='" . $row['client_id'] . "'");
				$n = $qry->num_rows();
				if ($n < 5) {$eligibility = "No";} else { $eligibility = "Eligible";}
				if ($intake_id == 1) {$eligibility = "Eligible";}

				if ($eligibility == "No") {$this->db->query("delete from intake_client_status where id='" . $row['id'] . "' and client_id='" . $row['client_id'] . "'");}

				echo '<tr><td>' . $i++ . '</td>	<td>' . $clR['id'] . '</td> <td>' . $clR['name'] . '</td>	<td>' . $row['intake_id'] . '</td>	<td>' . $row['status'] . '</td>	<td>' . $eligibility . '</td></tr>';
			}
			echo '</table><br /><br />';
		}
	}

	//    Check Client Programs Status
	public function cron_check_client_program_status() {
		echo '<table cellpadding="5" cellspacing="0" border="1"><tr><td colspan="6"><strong>Client Program Status</strong></td></tr>';
		$q = $this->db->query("select distinct(client_id) as client_id from client_program_progress where 1 order by client_id asc");
		foreach ($q->result_array() as $row) {
			$i = 1;
			$clR = $this->default_model->get_arrby_tbl_single('users', '*', "id='" . $row['client_id'] . "'", '1');
			echo '<tr><td colspan="6"><strong>' . $clR['name'] . '</strong></td></tr>';

			$sql = "select distinct(program_id_primary) as program_id_primary from client_program_progress where client_id='" . $row['client_id'] . "' order by program_id desc";
			$q2 = $this->db->query($sql);
			foreach ($q2->result_array() as $r) {
				$this->crm_model->check_client_program_status($row['client_id'], $r['program_id_primary']);
				echo '<tr><td>' . $i++ . '</td>	<td>' . $clR['id'] . '</td> <td>' . $clR['name'] . '</td>	<td>' . $r['program_id_primary'] . '</td></tr>';
			}
		}
		echo '</table><br /><br />';
	}

}
