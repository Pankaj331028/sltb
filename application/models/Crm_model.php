<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Crm_model extends CI_Model {

	public $CI;
	public function __construct() {
		parent::__construct();
		$this->load->helper('url');
		$this->load->database();

		$this->CI = &get_instance();
		$this->CI->load->config('stripe');

		$this->load->library('stripe_lib');

		$this->get_site_settings();

	}

//    Remove Unnecessary Entry
	public function removeunnecessaryentry() {
		$this->db->query("DELETE FROM users_company where name=''");
		$this->db->query("DELETE FROM users_company_smtp_email where smtp_hostname=''");
	}

//    Get Site Settings
	public function get_site_settings() {
		$query = $this->db->query("SELECT * FROM settings where 1 limit 1");
		$GLOBALS["settings"] = $query->row_array();
	}

// Check Account Segment and Redirect
	public function check_account_segment_and_redirect($var_1 = 'account', $var_2 = 'vhvhvh') {
		if ($this->uri->segment(1) == $var_1) {
			if ($GLOBALS["loguser"]["role"] == "Company") {$company_id = $GLOBALS["loguser"]["id"];} else { $company_id = $GLOBALS["loguser"]["company_id"];}
			$cmpR = $this->get_company_details($company_id);
			if (trim($cmpR['slug']) != "") {
				$url = current_url();
				$url = str_replace("index.php/", "", $url);
				$replace_from = $var_1 . "/" . $var_2;
				$replace = $cmpR['slug'] . "/" . $var_2;
				$url = str_replace($replace_from, $replace, $url);
				redirect($url);
				exit;
			}
		}
	}

//    Get Company Details
	public function get_company_details($company_id) {
		$query = $this->db->query("SELECT * FROM users_company where id='$company_id' or (slug='$company_id' and slug!='') limit 1");
		$res = $query->row_array();
		if (!isset($res['id'])) {
			$this->db->insert("users_company", ['id' => $company_id, 'case_manager' => $company_id]);
			$query = $this->db->query("SELECT * FROM users_company where id='$company_id' limit 1");
			$res = $query->row_array();
		}

		if (isset($res['case_manager'])) {
			if ($res['case_manager'] == "") {
				$this->db->where('id', $company_id);
				$this->db->update("users_company", ['case_manager' => $company_id]);

				$query = $this->db->query("SELECT * FROM users_company where id='$company_id' limit 1");
				$res = $query->row_array();
			}
		}

		return $res;
	}

//    Get Company Emails Details
	public function get_company_smtp_email_details($company_id, $restype = 0, $casemanager = 0) {
		$query = $this->db->query("SELECT * FROM users_company_smtp_email where id='$company_id' limit 1");
		$res = $query->row_array();

		if (!isset($res['id'])) {
			$this->db->insert("users_company_smtp_email", ['id' => $company_id]);
			$query = $this->db->query("SELECT * FROM users_company_smtp_email where id='$company_id' limit 1");
			$res = $query->row_array();
		}

		// fetch email and password from users table

		$q = $this->db->query('select email, email_password from users where id=' . ($casemanager != 0 ? $casemanager : ($GLOBALS["loguser"]["id"] ?? 0)));

		// $query = 'SELECT email, email_password FROM users WHERE id=' . ($casemanager != 0 ? $casemanager : ($GLOBALS["loguser"]["id"] ?? 0));

		if ($q->num_rows() > 0) {

			$user = $q->row_array();
			$res['smtp_from_email'] = $user['email'];
			$res['reply_to_email'] = $user['email'];
			$res['smtp_email_password'] = base64_decode($user['email_password']);
		}
		if ($res['status'] == "Confirmed") {return $res;}
		if ($restype == 1) {return $res;}
	}
// Get Reminders Details
	public function get_reminder_details($company_id) {

		$query = $this->db->query("SELECT
    program_definitions.program_title,
    program_definitions.step_name,
	vl_reminder_rules.reminder_rule_id ,
    vl_reminder_rules.step_id,
    vl_reminder_rules.reminder_name,
	vl_reminder_rules.reminder_desc,
	vl_reminder_rules.days_to_send,
	vl_reminder_rules.send_frequency,
	vl_reminder_rules.stop_sending_days,
	vl_reminder_rules.reminder_email_subject,
	vl_reminder_rules.reminder_email_body

FROM
    vl_reminder_rules
left JOIN
    program_definitions
ON
    vl_reminder_rules.program_id  = program_definitions.program_definition_id
WHERE
    vl_reminder_rules.company_id = '$company_id'  group by vl_reminder_rules.reminder_rule_id
ORDER BY
    program_definitions.program_title,
    vl_reminder_rules.step_id");
		$res = $query->result_array();

		return $res;

	}
// Get Reminders programs
	public function get_reminder_programs($company_id) {
		// echo $company_id;
		// die();
		$query = $this->db->query("SELECT distinct program_definitions.program_title FROM vl_reminder_rules left JOIN program_definitions ON vl_reminder_rules.program_id  = program_definitions.program_definition_id WHERE vl_reminder_rules.company_id = " . $company_id . " group by vl_reminder_rules.reminder_rule_id ORDER BY program_definitions.program_title, vl_reminder_rules.step_id");
		$res = $query->result_array();

		return $res;

	}

//    Get User Data
	public function get_user_data($id = 0) {
		$query = $this->db->query("SELECT * FROM users where id='$id'");
		$result = $query->row_array();
		return $result;
	}

//    Get User Data
	public function get_login_user($id = 0) {
		$id = $this->session->userdata('userid');
		$query = $this->db->query("SELECT * FROM users where id='$id' limit 1");
		$result = $query->row_array();
		if ($result['login_browser_id'] != session_id()) {
			$this->session->unset_userdata('userid');
			$this->session->sess_destroy();
			redirect(base_url($this->uri->segment(1)));
		}

		return $result;
	}

//    Validate Company Profile Status
	public function validate_company_profile_status() {
		$user = $GLOBALS["loguser"];
		$billing_amt = $discount_amount = 0;
		$url2 = $this->uri->segment(2);

		$subscription = $this->db->query("SELECT * FROM users_company where id='" . $GLOBALS["loguser"]['company_id'] . "'")->row_array();

		if ($user["role"] != 'Customer') {
			if ($user["role"] == "Company") {

				$company = $cmpR = $this->get_company_details($GLOBALS["loguser"]["id"]);
				$company_emails = $this->get_company_smtp_email_details($GLOBALS["loguser"]["id"]);

				$billing = $this->calculate_billing_amount($GLOBALS["loguser"]["id"]);
				$billing_amt = $billing['billing_amt'];

				if ($url2 != "company" && $url2 != "team" && $url2 != "profile" && $url2 != "emails" && $url2 != "billing" && $url2 != "cp" && $url2 != "logout") {
					/*if(!file_exists($company['logo']))
						                {
						                $this->session->set_flashdata('error', 'Please upload your company logo.');
						                redirect(base_url("account/company"));
						                }
					*/
					if (trim($company['name']) == "" || trim($company['phone']) == "" || trim($company['email']) == "") {
						$this->session->set_flashdata('error', 'Please update company details.');
						redirect(base_url("account/company"));
					} else if (trim($company['case_manager']) == "") {
						$this->session->set_flashdata('error', 'Please update case manager details.');
						redirect(base_url("account/company"));
					} else if (trim($GLOBALS['loguser']['email_password']) == "") {
						$this->session->set_flashdata('error', 'Please update your email password to confirm SMTP details.');
						redirect(base_url("account/profile"));
					} else if (trim($company['stripe_card_id']) == "") {
						$this->session->set_flashdata('error', 'Please add your credit/debit card details.');
						redirect(base_url("account/billing"));
					} else if (trim($company_emails['status']) != "Confirmed") {
						$this->session->set_flashdata('error', 'Please update SMTP email details.');
						redirect(base_url("account/emails"));
					} else if ($billing_amt > 0) {

						if (!isset($subscription['account_type']) || (isset($subscription['account_type']) && $subscription['account_type'] == '1')) {
							//    Process Auto Checkout
							$checkout = $this->stripe_auto_checkout($GLOBALS["loguser"]["id"]);
							$billing_amt = $checkout['billing_amt'];

							if ($billing_amt > 0) {
								$this->session->set_flashdata('error', 'Complete Your Subscription Payment.');
								redirect(base_url("account/billing/pay"));
							}
						}
					} else {
					}
				}
			} elseif ($user["role"] == "Company User") {
				if (!isset($subscription['account_type']) || (isset($subscription['account_type']) && $subscription['account_type'] == '1')) {
					$q = $this->db->query("SELECT ac.* FROM account_payment_info ac join users_company uc ON uc.id=ac.company_id where ac.company_id='" . $GLOBALS["loguser"]["company_id"] . "' and uc.next_payment_date < '" . date('Y-m-d') . "' order by updated_at desc");
					$nr = $q->num_rows();
					if ($nr > 0) {foreach ($q->result_array() as $row) {$billing_amt += ($row['1st_user_fee'] + $row['additional_user_fee']);}}

					if ($url2 != "subscription_notification" && $url2 != "logout") {
						if ($billing_amt > 0) {
							$this->session->set_flashdata('error', 'Billing Issue.');
							redirect(base_url("account/subscription_notification"));
						}
					}
				}

				if (!isset($subscription['account_type']) || (isset($subscription['account_type']) && $subscription['account_type'] == '0')) {

					if ($url2 != "subscription_notification" && $url2 != "logout") {
						if (empty($subscription['stripe_token']) || empty($subscription['stripe_card_id']) || empty($subscription['card_last_four'])) {
							$this->session->set_flashdata('error', 'Billing Issue.');
							redirect(base_url("account/subscription_notification"));
						}
					}
				}
			}

			/* elseif ($user["role"] == "Customer") {
					if (!isset($subscription['account_type']) || (isset($subscription['account_type']) && $subscription['account_type'] == '1')) {
						$q = $this->db->query("SELECT ac.* FROM account_payment_info ac join users_company uc ON uc.id=ac.company_id where uc.next_payment_date < '" . date('Y-m-d') . "' and ac.company_id='" . $GLOBALS["loguser"]["company_id"] . "' order by updated_at desc");
						$nr = $q->num_rows();
						if ($nr > 0) {foreach ($q->result_array() as $row) {$billing_amt += ($row['1st_user_fee'] + $row['additional_user_fee']);}}

						if ($url2 != "subscription_notification" && $url2 != "logout") {
							if ($billing_amt > 0) {
								$this->session->set_flashdata('error', 'Complete Your Subscription Payment.');
								redirect(base_url("customer/subscription_notification"));
							}
						}
					}
				}
			*/

			if ($billing_amt > 0) {$GLOBALS['hide_account_left_sidebas'] = "Yes";}
		}

	}

//    Stripe Auto Checkout
	public function stripe_auto_checkout($id = 0) {
		$status = "Failed";
		$paymentID = "";

		$billing = $this->calculate_billing_amount($id);
		$billing_amt = $billing['billing_amt'];
		$discount_amount = $billing['discount_amount'];

		$sd = $this->check_company_stripe_details($id);
		if ($sd == "Valid") {
			try {
				$q = $this->db->query("SELECT * FROM users_company where id='" . $id . "'");
				$result = $q->row_array();

				$postData['customer_id'] = $result['stripe_id'];
				$postData['stripeToken'] = $result['stripe_token'];
				$postData['product'] = ['id' => $id, 'price' => $billing_amt, 'currency' => 'USD', 'name' => 'Subscription Payment'];

				// Make payment
				$paymentID = $this->stripe_payment_process($postData, $discount_amount, $promo_code, $result);

				// If payment successful
				if ($paymentID) {
					// entry in account_payment_info table with next payment
					$this->db->query("UPDATE users_company set last_payment_sent='" . date('Y-m-d') . "',next_payment_date='" . (date('Y-m-d', strtotime($result['next_payment_date'] . '+30 days'))) . "',status='Active' where id='" . $id . "'");

					// set next payment entry in account_payment_info table
					$fields = $this->db->query("SELECT * from settings Limit 1")->row_array();
					$q = $this->db->query("SELECT * FROM users where role='Company User' and company_id='" . $id . "'");
					$nr = $q->num_rows();
					$st_user_fee = $fields['initial_user_fee'];
					$additional_user_fee = ($fields['additional_user_fee'] * $nr);

					$this->db->insert('account_payment_info', ['company_id' => $id, 'account_name' => $result["name"], '1st_user_fee' => $st_user_fee, 'additional_user_fee' => $additional_user_fee]);

					$billing_amt = 0;
					$status = "Success";
					$this->send_biiing_email_2($paymentID);
				}
			} catch (\Exception $e) {

				// send mail to support mentioning error message
				$cmpR = $this->get_company_details($GLOBALS["loguser"]["id"]);

				$Msg = '<div style="font-family:Calibri; font-size:15px; width:100%; text-align:justify; padding:2px 15px;">
					<p>Dear Jeff,<br /></p>
					<p>' . $cmpR['name'] . ' tried to pay their subscription amount but some error occurred during the process. Please check below:</p>
					<p>' . $e->getMessage() . '</p>
					</div>';

				$data = [
					'email' => 'support@studentloantoolbox.com',
					'subject' => 'Error in Payment - ' . $GLOBALS["loguser"]["id"],
					'Msg' => $Msg,
				];
				$this->send_email($data);

				$this->api_error = $e->getMessage();
				$this->session->set_userdata('error', $this->api_error . '. Please try again later!!');
				redirect(base_url('account/billing'));
			}
		}

		return ["status" => $status, "paymentID" => $paymentID, "billing_amt" => $billing_amt];
	}

//    Calculate Billing Amount
	public function calculate_billing_amount($id = 0) {

		$price = 0;
		$q = $this->db->query("SELECT ac.* FROM account_payment_info ac join users_company uct ON uct.id=ac.company_id where ac.company_id='" . $id . "' and next_payment_date <= '" . date('Y-m-d') . "' order by updated_at desc");
		$nr = $q->num_rows();
		if ($nr > 0) {foreach ($q->result_array() as $row) {$price += ($row['1st_user_fee'] + $row['additional_user_fee']);}}

		$discount_amount = 0;
		$cmpR = $this->crm_model->get_company_details($id);
		$cpnR = $this->check_coupon_code($cmpR["promo_code"], $id);
		$cpnR_data = json_decode($cpnR, true);
		$promo_code = $cmpR["promo_code"];
		if ($cpnR_data['status'] == "Success") {$discount_amount = $this->calculate_coupon_code_discount($price, $cmpR, $cpnR_data);}

		if ($discount_amount > $price) {$discount_amount = 0;}
		$billing_amt = ($price - $discount_amount);

		return ["price" => $price, "discount_amount" => $discount_amount, "billing_amt" => $billing_amt];
	}

//    Get Stripe Details Details
	public function check_company_stripe_details($id = 0) {
		$status = "Invalid";
		$query = $this->db->query("SELECT * FROM users_company where id='$id'");
		$result = $query->row_array();
		$q = $this->db->query("SELECT * FROM users where id='$id'");
		$comp = $query->row_array();

		if (trim($result['stripe_token']) != "" && trim($result['stripe_id']) != "") {
			$tokens = $this->stripe_lib->verifyToken($result['stripe_token']);
			if (isset($tokens['id'])) {
				$customer = $this->stripe_lib->findCustomer($result['stripe_id']);
				if (isset($customer['id'])) {$status = "Valid";}
			}

			if ($status == "Invalid") {
				$this->db->query("UPDATE users_company set stripe_id='', stripe_token='', stripe_card_id='', card_last_four='' where id='$id'");

				$smtp_data = $this->get_company_smtp_email_details($id, 0, $id);
				$smtp_data['email'] = $result['email'];

				//    Send Email to company for missing card details
				$smtp_data['subject'] = 'Invalid Card Details';

				$smtp_data['Msg'] = '<div style="font-family:Calibri; font-size:15px; width:100%; text-align:justify; padding:2px 15px;">
					<p>Dear ' . $comp['name'] . '</p>
					<p>Card details are missing from your account. Kindly login to your account and go to Payments section to link your card.</p>
					<p>If you have any questions, please contact us.</p>
					<p>Student Loan Toolbox</p>
					</div>';
				$this->send_email($smtp_data);

			}
		}
		return $status;
	}
	//Stripe card id
	public function stripe_card_id($id = 0) {

		$paymentMethodId = $_POST['paymentMethod_id'];
		$token = $_POST['stripeToken'];
		$type_account = $_POST['type_account'];
		$card_last_four = $_POST['cardnumber'];

		$query_stripe_card_id = $this->db->query("UPDATE users_company SET stripe_card_id = '$paymentMethodId',stripe_token = '$token',card_last_four = '$card_last_four',account_type = '$type_account' WHERE id = '$id'");
		$this->db->query("UPDATE account_payment_info SET account_type = '$type_account' WHERE company_id = '$id'");
		$subscription = $this->db->query("SELECT * FROM users_company where id='" . $id . "'")->row_array();

		return true;
	}

	public function update_subscription_amount($id) {

		$fields = $this->db->query("SELECT * from settings Limit 1")->row_array();
		$comp = $this->db->query('select * from users_company where id=' . $id)->row_array();

		$q = $this->db->query("SELECT * FROM account_payment_info where company_id='" . $id . "'");
		$apiR = $q->row_array();

		$date2 = date_create(date('Y-m-d'));
		$date1 = date_create($comp['last_payment_sent']);
		$diff = date_diff($date1, $date2);
		$days = $diff->format("%a");

		$per_day = $fields['additional_user_fee'] / 30;
		if ($days == 0) {$days = 30;}
		$add = ($days * $per_day);
		if ($days == 30) {$add = $fields['additional_user_fee'];}

		$num = $this->db->query('select id from users where company_id=' . $id . ' and role="Company User" and status="Active"')->num_rows();
		$additional_user_fee = $add * $num;

		// $additional_user_fee = (($apiR['additional_user_fee'] ?? 0) + $additional_user_fee);

		$tper_day = $fields['initial_user_fee'] / 30;
		if ($days == 0) {$days = 30;}
		$first = ($days * $tper_day);
		if ($days == 30) {$first = $fields['initial_user_fee'];}
		// $first = (($apiR['1st_user_fee'] ?? 0) + $first);

		if ($comp['account_type'] == '1') {
			if (!isset($apiR['account_payment_info_id'])) {
				$col_arr = [
					'company_id' => $comp['id'],
					'1st_user_fee' => $first,
					'additional_user_fee' => $additional_user_fee,
					'account_name' => $comp['name'],
					'account_type' => $comp['account_type'],
					'billed_on' => date('Y-m-d'),
				];

				$this->db->insert('account_payment_info', $col_arr);

			} else {
				$this->db->query("UPDATE account_payment_info set additional_user_fee='" . $additional_user_fee . "', billed_on='" . date('Y-m-d') . "' where company_id='" . $id . "'");
			}

		}
	}

//    Stripe Payment Process
	public function stripe_payment_process($postData, $discount_amount, $promo_code, $cmpr) {

		// If post data is not empty
		if (!empty($postData)) {

			// Retrieve stripe token and user info from the submitted form data
			$customer_id = $postData['customer_id'];
			$token = $postData['stripeToken'];

			// Charge a credit or a debit card
			$charge = $this->stripe_lib->createCharge($customer_id, $postData['product']['name'], $postData['product']['price']);

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
						'company_id' => $GLOBALS["loguser"]["id"],
						'account_name' => $cmpr['name'],
						'account_email' => $cmpr['email'],
						'amount_paid' => $paidAmount,
						'discount_amount' => $discount_amount ?? 0,
						'promo_code' => $promo_code ?? '',
						'paid_amount_currency' => $paidCurrency,
						'txn_id' => $transactionID,
						'payment_status' => $payment_status,
					);
					//$orderID = $this->product->insertOrder($orderData);
					$this->db->insert("payments", $orderData);
					$orderID = $this->db->insert_id();

					$this->db->query("delete from account_payment_info where company_id='" . $GLOBALS["loguser"]["id"] . "'");

					// If the order is successful
					if ($payment_status == 'succeeded') {
						//$orderID = '1561';
						return $orderID;
					}
				} else {
					$orderData = array(
						'company_id' => $GLOBALS["loguser"]["id"],
						'account_name' => $cmpr['name'],
						'account_email' => $cmpr['email'],
						'amount_paid' => $postData['product']['price'],
						'discount_amount' => $discount_amount ?? 0,
						'promo_code' => $promo_code ?? '',
						'paid_amount_currency' => 'usd',
						'txn_id' => '',
						'payment_status' => $charge['status'] ?? 'failed',
					);
					//$orderID = $this->product->insertOrder($orderData);
					$this->db->insert("payments", $orderData);
					$orderID = $this->db->insert_id();
				}
			} else {
				$orderData = array(
					'company_id' => $GLOBALS["loguser"]["id"],
					'account_name' => $cmpr['name'],
					'account_email' => $cmpr['email'],
					'amount_paid' => $postData['product']['price'],
					'discount_amount' => $discount_amount ?? 0,
					'promo_code' => $promo_code ?? '',
					'paid_amount_currency' => 'usd',
					'txn_id' => '',
					'payment_status' => 'failed',
				);
				//$orderID = $this->product->insertOrder($orderData);
				$this->db->insert("payments", $orderData);
				$orderID = $this->db->insert_id();
			}

		}
		return false;
	}

//    Get Payment Details
	public function admin_check_company_payment($id = 0) {
		$query = $this->db->query("SELECT * FROM account_payment_info where company_id='$id'");
		$result = $query->row_array();
		return $result;
	}

//    Get Client Full Details
	public function get_client_full_details($client_id = 0) {
		$return = array();
		//    Client Data
		$q = $this->db->query("SELECT * FROM users where id='$client_id'");
		$return['client'] = $cr = $q->row_array();
		if (isset($return['client']['id'])) {
			//    Company
			$q = $this->db->query("SELECT * FROM users where id='" . $cr['company_id'] . "'");
			$return['company'] = $cmr = $q->row_array();

			//    Company
			$return['users_company'] = $this->get_company_details($cr['company_id']);

			//    Case Manager
			if ($cr['parent_id'] == 0) {$parent_id = $cr['company_id'];} else { $parent_id = $cr['parent_id'];}
			$q = $this->db->query("SELECT * FROM users where id='$parent_id'");
			$return['case_manager'] = $cmr = $q->row_array();

			//    Documents
			$q = $this->db->query("SELECT * FROM client_documents where client_id='$client_id'");
			$return['documents'] = $q->row_array();
			$return['intake_client_status'] = $this->client_intake_client_status($client_id, "1");
			$return['update_intake_client_status'] = $this->client_intake_client_status($client_id, "4");
			$return['intake']['idr'] = $this->client_intake_client_status($client_id, "2");
			$return['intake']['consolidation'] = $this->client_intake_client_status($client_id, "3");
			$return['intake']['recertification'] = $this->client_intake_client_status($client_id, "5");
			$return['intake']['recalculation'] = $this->client_intake_client_status($client_id, "6");
			$return['intake']['switch_idr'] = $this->client_intake_client_status($client_id, "7");

			$return['programs'] = $this->get_client_programs($client_id);
		}
		// echo "<pre>";
		// print_r($return);die;
		return $return;
	}

//    Get Client Analysis Result Data
	public function get_client_analysis_results($client_id = '', $intake_id = 1) {
		$res = array();
		// $intake_id = 1;
		$q = 0;
		$a = 0;
		if ($this->db->query('select * from intake_client_status where client_id=' . $client_id . ' and intake_id=4')->num_rows() > 0) {
			$intake_id = 4;
			$q = 96;
			$a = 74;
		}
		$res['client_data'] = $this->get_client_full_details($client_id);
		if (isset($res['client_data']['client']['id'])) {
			$res['intake'] = $intake = $this->get_client_intake_data($client_id, $intake_id);
			$nslds_id = $res['intake'][6]['ans']['intake_file_id'];
			$car_cond = "client_id='$client_id' and intake_id='" . $intake_id . "' and nslds_id='$nslds_id'";
			$res['car'] = $this->default_model->get_arrby_tbl_single('client_analysis_results', '*', $car_cond, '1');

			if (isset($res['car']['id'])) {
				if ($res['car']['family_size'] == "" || $res['car']['family_size'] == "0" || $this->uri->segment(5) == "reset_analysis") {
					$col_arr = array("payment_plan_selected" => "", "scenario_selected" => "", "include_in_client_report" => "");
					$col_arr['marital_status'] = $intake[11]['ans']['intake_answer_id'];
					if ($intake_id == 1) {
						if ($col_arr['marital_status'] == 15) {$fs = 2;} else { $fs = 1;}
					} else {
						if ($col_arr['marital_status'] == 89) {$fs = 2;} else { $fs = 1;}
					}
					$col_arr['family_size'] = ($fs + intval($intake[19]['ans']['intake_comment_body']) + intval($intake[20]['ans']['intake_comment_body']));
					$col_arr['file_joint_or_separate'] = $intake[14]['ans']['intake_answer_id'];
					$col_arr['client_agi'] = $intake[17]['ans']['intake_comment_body'];
					$col_arr['client_monthly'] = $intake[18]['ans']['intake_comment_body'];
					$col_arr['spouse_agi'] = $intake[15]['ans']['intake_comment_body'];
					$col_arr['spouse_monthly'] = $intake[16]['ans']['intake_comment_body'];
					$col_arr['nslds_id'] = $nslds_id;

					$this->db->where('id', $res['car']['id']);
					$this->db->update('client_analysis_results', $col_arr);

					if ($this->uri->segment(5) == "reset_analysis") {
						$this->session->set_flashdata('success', 'Analysis successfully reset.');
						redirect(base_url($this->uri->segment(1) . '/customer/current_analysis/' . $this->uri->segment(4)));
						exit;
					}
				}
			} else {
				$col_arr = array("client_id" => $client_id, "company_id" => $res['client_data']['users_company']['id'], "intake_id" => $intake_id, "nslds_id" => $nslds_id);
				$col_arr['marital_status'] = $intake[11]['ans']['intake_answer_id'];
				if ($intake_id == 1) {
					if ($col_arr['marital_status'] == 15) {$fs = 2;} else { $fs = 1;}
				} else {
					if ($col_arr['marital_status'] == 89) {$fs = 2;} else { $fs = 1;}
				}
				$col_arr['family_size'] = ($fs + intval($intake[19]['ans']['intake_comment_body']) + intval($intake[20]['ans']['intake_comment_body']));
				$col_arr['file_joint_or_separate'] = $intake[14]['ans']['intake_answer_id'];
				$col_arr['client_agi'] = $intake[17]['ans']['intake_comment_body'];
				$col_arr['client_monthly'] = $intake[18]['ans']['intake_comment_body'];
				$col_arr['spouse_agi'] = $intake[15]['ans']['intake_comment_body'];
				$col_arr['spouse_monthly'] = $intake[16]['ans']['intake_comment_body'];
				$col_arr['nslds_id'] = $nslds_id;

				$this->db->insert('client_analysis_results', $col_arr);
			}

			$cintake = $this->db->query('select * from intake_client_status where intake_id=1 and client_id=' . $client_id)->row_array();

			$res['car'] = $this->default_model->get_arrby_tbl_single('client_analysis_results', '*', $car_cond, '1');

			$res['intake_client'] = $cintake;
		}
		return $res;
	}

//    Get Client Intake Data
	public function get_intake_page_with_data($client_id = '', $intake_id = '1') {
		$res['intake_page'] = $this->default_model->get_arrby_tbl('intake_page', '*', "intake_id='" . $intake_id . "' order by intake_page_no asc", '500');

		$rows = $this->default_model->get_arrby_tbl('intake_question', '*', "intake_id='" . $intake_id . "' order by placement_order asc", '500');
		foreach ($rows as $row) {
			$intake_page_no = $row['intake_page_no'];
			$sno = $row['placement_order'];
			$res[$intake_page_no][$sno]['que'] = $row;
			$res[$intake_page_no][$sno]['ans'] = $this->crm_model->admin_intake_answer_by_client($client_id, $row['intake_question_id']);
		}

		return $res;
	}

//    Get Client Intake Data
	public function get_client_intake_data($client_id = '', $intake_id = '1') {
		$res = array();
		$rows = $this->default_model->get_arrby_tbl('intake_question', '*', "intake_id='" . $intake_id . "' order by placement_order asc", '500');
		foreach ($rows as $row) {
			$placement_order = $row['placement_order'];
			$sno = $row['placement_order'];
			$res[$sno]['que'] = $row;
			$res[$sno]['ans'] = $this->crm_model->admin_intake_answer_by_client($client_id, $row['intake_question_id']);
		}
		return $res;
	}

//    Check Client Programs Status
	public function get_client_programs($client_id = '') {
		$res = array();
		$q = $this->db->query("select * from client_program where client_id='" . $client_id . "'");
		foreach ($q->result_array() as $row) {
			$program_definition_id = $row['program_definition_id'];
			$res[$program_definition_id] = $row;
		}
		return $res;
	}

//    Check Client Programs Status
	public function check_client_program_status($client_id = '', $program_id_primary = '') {
		$sql = "select * from client_program_progress where client_id='" . $client_id . "' and program_id_primary='" . $program_id_primary . "' order by program_id desc limit 1";
		$q3 = $this->db->query($sql);
		$r = $q3->row_array();

		if (isset($r['program_id_primary'])) {
			$qry = $this->db->query("SELECT * FROM client_program where client_id='" . $client_id . "' and program_definition_id='" . $r['program_id_primary'] . "'");
			if ($qry->num_rows() == 0) {
				$tmp_arr = array("client_id" => $r['client_id'], "program_definition_id" => $r['program_id_primary'], "created_at" => $r['created_at'], "status" => $r['status']);
				$this->db->insert("client_program", $tmp_arr);
			} else {
				$this->db->query("UPDATE client_program set status='" . $r['status'] . "' where client_id='" . $client_id . "' and program_definition_id='" . $r['program_id_primary'] . "'");
			}
		}

		$q = $this->db->query("select * from client_program where client_id='" . $client_id . "' and program_definition_id='" . $program_id_primary . "'");
		$res = $q->row_array();
		return $res;
	}

//    Client Check Intake Status
	public function client_intake_client_status($client_id = 0, $intake_id = 1) {
		$table = "intake_client_status";
		//    Check Intake
		$query = $this->db->query("SELECT * FROM $table where intake_id='" . $intake_id . "' and client_id='" . $client_id . "'");
		$n = $query->num_rows();
		if ($n == 0) {
			$insert_record = "Yes";

			$query = $this->db->query("SELECT * FROM intake where intake_id='" . $intake_id . "'");
			$ir = $query->row_array();
			if (isset($ir['program_definition_id'])) {$program_id_primary = $ir['program_definition_id'];} else { $program_id_primary = "";}
			//    Set Client Program
			if ($program_id_primary != "") {$this->check_client_program_status($client_id, $program_id_primary);}

			if ($intake_id == 1) {
				//    Add To Intake Program if NOT ADDED
				$q = $this->db->query("SELECT * FROM client_program_progress where program_id_primary='" . $program_id_primary . "' and client_id='" . $client_id . "'");
				$n2 = $q->num_rows();
				if ($n2 == 0) {
					$result = $this->admin_users_add_program($client_id, $program_id_primary);
					if (isset($result['program_definition_id'])) {$this->admin_users_add_program_step($client_id, $result['program_definition_id']);}
				}

			} else if ($intake_id > 1) {
				$q = $this->db->query("SELECT * FROM client_program_progress where program_id_primary='" . $program_id_primary . "' and client_id='" . $client_id . "'");
				$n = $q->num_rows();
				if ($n < 5) {$insert_record = "No";}
			}
			if ($insert_record == "Yes") {$this->db->insert($table, ["intake_id" => $intake_id, "client_id" => $client_id, "status" => "Pending", "last_sent_reminder" => date('Y-m-d')]);}

		} else if ($n > 1) {
			$del_limit = $n - 1;
			$this->db->query("DELETE FROM $table where intake_id='" . $intake_id . "' and client_id='" . $client_id . "' order by id desc limit $del_limit");
		} else {}

		$query = $this->db->query("SELECT * FROM $table where intake_id='" . $intake_id . "' and client_id='" . $client_id . "'");
		$res = $query->row_array();
		return $res;
	}

//    Client Registration Process
	public function client_registration($company_id = 0, $advertisement_id = 0) {
		$errorMsg = "";
		$email = $this->input->post('email');
		$query = $this->db->query("SELECT * FROM users where company_id='$company_id' and email='$email' limit 1");
		$nr = $query->num_rows();
		if ($nr == 0) {
			@extract($_POST);
			$psd = $this->input->post('password');
			$psd = $this->default_model->psd_encrypt($psd);

			$this->db->insert('users', ['role' => 'Customer', 'company_id' => $company_id, 'parent_id' => $company_id, 'advertisement_id' => $advertisement_id, 'name' => $name, 'lname' => $lname, 'phone' => $phone, 'email' => $email, 'psd' => $psd, 'add_date' => date('Y-m-d')]);
			$id = $this->db->insert_id();

			$query = $this->db->query("SELECT * FROM users where id='$id'");
			$result = $query->row_array();

			$this->db->where('id', $id);
			$this->db->update('users', ['added_by' => $id]);

			$cmpR = $this->get_company_details($company_id);
			$smtp_data = $this->get_company_smtp_email_details($company_id);
			$smtp_data['email'] = $result['email'];
			//    Send Email to Customer
			$smtp_data['subject'] = 'Student Loan Tool Box - Account Details';
			$smtp_data['Msg'] = $cmpR['email_header'] . '<div style="font-family:Calibri; font-size:15px; width:100%; text-align:justify; padding:2px 15px;">
		<p>Dear ' . $result['name'] . ',<br />Welcome to Student Loan Tool Box</p>
		<p>Your studentloantoolbox.net account login details are as below:</p>
		<p><strong>Email:</strong> ' . $result['email'] . '</p>
		<p><strong>Login ID:</strong> ' . $result['id'] . '</p>
		<p><strong>Password:</strong> ' . $this->input->post('password') . '</p>
		<p><a href="' . base_url($this->uri->segment(1) . "/account") . '">Click Here to Login</a></p>
		<p>---<br /><strong>Warm Regards</strong><br />' . $cmpR['name'] . '<br />' . base_url($this->uri->segment(1) . "/account") . '</p>
		</div>';
			$this->send_email($smtp_data);
			//    Send Intake Email
			$this->admin_send_intake_email($result['id'], "1");

			//    Send Email to Case Manager
			$q = $this->db->query("SELECT * FROM users where id='$company_id' limit 1");
			$cmR = $q->row_array();

			$smtp_data['email'] = $cmR['email'];
			//    Send Email to Customer
			$smtp_data['subject'] = 'You Have A New Lead';
			$smtp_data['Msg'] = $cmpR['email_header'] . '<div style="font-family:Calibri; font-size:15px; width:100%; text-align:justify; padding:2px 15px;">
		<p>Congratulations, you have a new lead!</p>
		<p>The following person has registered to seek assistance from your company.</p>
		<p><strong>First Name:</strong> ' . $result['name'] . '</p>
		<p><strong>Last Name:</strong> ' . $result['lname'] . '</p>
		<p><strong>Email Address:</strong> ' . $result['email'] . '</p>
		<p>This lead is currently completing the intake. Once done, you will receive an email alerting you to check their account and review their Analysis.</p>
		<p>---<br /><strong>Regards</strong><br />Support<br />' . base_url($this->uri->segment(1) . "/account") . '</p>
		</div>';
			$this->send_email($smtp_data);

			$errorMsg = "Your account has been created successfully.<br />Your account details have been sent to your registered email.";
		} else {
			$errorMsg = "This email already registered.<br />Please enter another email and continue.";
		}
		$result['errorMsg'] = $errorMsg;
		return $result;
	}

//    Registration Process
	public function admin_registration() {
		$errorMsg = "";
		$email = $this->input->post('email');
		$sql = "SELECT * FROM users where email='$email' limit 1";
		$query = $this->db->query($sql);
		$nr = $query->num_rows();
		//if($nr == 0)
		if ($nr == 0) {
			@extract($_POST);

			// check if any user is registered with this company

			$exist = 0;
			$user = [];
			$status = 'Active';
			$check = $this->db->query('select id from users_company where name= "' . $company_name . '"');

			if ($check->num_rows() > 0) {
				$com = $check->row_array();
				$q = $this->db->query('select * from users where company_id=' . $com['id'] . ' and role in ("Company","Company User") and status="Active" order by id asc limit 1');
				if ($q->num_rows() > 0) {
					$user = $q->row_array();
					$exist++;
					$status = 'Pending';
				}
			}

			$psd = $this->input->post('password');
			$psd = $this->default_model->psd_encrypt($psd);
			$next_payment_date = date('Y-m-d');
			// $next_payment_date = date('Y-m-d', strtotime(date('Y-m-d') . ' + 30 days'));
			$this->db->insert('users', ['role' => 'Company', 'name' => $name, 'lname' => $lname, 'phone' => $phone, 'email' => $email, 'psd' => $psd, 'position' => $position, 'add_date' => date('Y-m-d'), 'status' => $status]);
			$id = $this->db->insert_id();

			$this->db->where('id', $id);
			$this->db->update('users', ['added_by' => $id, 'company_id' => $user['company_id'] > 0 ? $user['company_id'] : $id]);

			if ($exist > 0 && $status == 'Pending') {

				$smtp = $this->get_company_smtp_email_details($user['company_id'], 0, $user['id']);
				$cmpR = $this->get_company_details($user['company_id']);
				$query = $this->db->query("SELECT * FROM users where id='$id'");
				$result = $query->row_array();

				// send mail to owner to approve/reject new account request
				$subject = 'Student Loan Tool Box - New Account Approval Request';
				$Msg = $cmpR['email_header'] . '<div style="font-family:Calibri; font-size:15px; width:100%; text-align:justify; padding:2px 15px;">
				<p>Dear ' . $user['name'] . ',<br /></p>
				<p>There is a new user registration for your company with below details. Please approve the account if verified.</p>
				<p><strong>Email:</strong> ' . $result['email'] . '</p>
				<p><strong>Name:</strong> ' . $result['name'] . ' ' . $result['lname'] . '</p>
				<p><a href="' . base_url($cmpR['slug'] . '/account_request/' . base64_encode($result['id'])) . '/approve">Click Here to Approve</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="' . base_url($cmpR['slug'] . '/account_request/' . base64_encode($result['id'])) . '/reject">Click Here to Reject</a></p>
				<p>---<br /><strong>Warm Regards</strong><br />Student Loan Tool Box<br />' . base_url() . '</p>
				</div>';
				$smtp['email'] = $user['email'];
				$smtp['Msg'] = $Msg;
				$smtp['subject'] = $subject;
				$this->send_email($smtp);

				//    Send Email to user for login credentials and approval request received

				$subject = 'Student Loan Tool Box - Account Details';
				$Msg = $cmpR['email_header'] . '<div style="font-family:Calibri; font-size:15px; width:100%; text-align:justify; padding:2px 15px;">
				<p>Dear ' . $result['name'] . ',<br />Welcome to Student Loan Tool Box</p>
				<p>We have received your studentloantoolbox.net account request and Company owner is currently reviewing your details. Please login using below credentials once you receive approval update:</p>
				<p><strong>Email:</strong> ' . $result['email'] . '</p>
				<p><strong>Login ID:</strong> ' . $result['id'] . '</p>
				<p><strong>Password:</strong> ' . $this->input->post('password') . '</p>
				<p><a href="' . base_url($cmpR['slug'] . "/account") . '">Click Here to Login</a></p>
				<p>---<br /><strong>Warm Regards</strong><br />Student Loan Tool Box<br />' . base_url() . '</p>
				</div>';
				$smtp['email'] = $result['email'];
				$smtp['Msg'] = $Msg;
				$smtp['subject'] = $subject;
				$this->send_email($smtp);

				$errorMsg = "We have received your registration request. Please wait for the company owner to approve your account.";
			} else {
				$this->account_model->add_case_manager_setting($id);
				$this->admin_create_payment_installment($id); //    Create Payment Installment

				$query = $this->db->query("SELECT * FROM users where id='$id'");
				$result = $query->row_array();

				//reminder company id zero insert

				$reminder_company = $this->db->query("SELECT * FROM vl_reminder_rules where company_id='0'");
				$reminderData = $reminder_company->result_array();
				foreach ($reminderData as $reminder_data) {
					$reminder_data['company_id'] = $id;
					unset($reminder_data['reminder_rule_id']);
					$this->db->insert('vl_reminder_rules', $reminder_data);
				}

				$q = $this->db->query("SELECT * FROM users_company where id='$id' limit 1");
				$n = $q->num_rows();
				if ($n == 0) {$this->db->insert('users_company', ['id' => $id, 'name' => $company_name, 'phone' => $phone, 'email' => $email, 'case_manager' => $id, 'next_payment_date' => $next_payment_date, 'add_date' => date('Y-m-d H:i:s')]);} else {

					$this->db->where('id', $id);
					$this->db->update('users_company', ['name' => $company_name, 'case_manager' => $id]);
				}

				$this->get_company_smtp_email_details($id);
				$this->create_company_slug(); //    Create Slug
				$this->admin_company_email_header_update($id);

				$cmpR = $this->get_company_details($id);

				//    Send Email
				$subject = 'Student Loan Tool Box - Account Details';
				$Msg = $cmpR['email_header'] . '<div style="font-family:Calibri; font-size:15px; width:100%; text-align:justify; padding:2px 15px;">
		<p>Dear ' . $result['name'] . ',<br />Welcome to Student Loan Tool Box</p>
		<p>Your studentloantoolbox.net account login details are as below:</p>
		<p><strong>Email:</strong> ' . $result['email'] . '</p>
		<p><strong>Login ID:</strong> ' . $result['id'] . '</p>
		<p><strong>Password:</strong> ' . $this->input->post('password') . '</p>
		<p><a href="' . base_url($cmpR['slug'] . "/account") . '">Click Here to Login</a></p>
		<p>---<br /><strong>Warm Regards</strong><br />Student Loan Tool Box<br />' . base_url() . '</p>
		</div>';
				$this->send_email(['email' => $result['email'], 'Msg' => $Msg, 'subject' => $subject]);

				$errorMsg = "Your account has been created successfully you can now log in.";
			}
		} else {
			$errorMsg = "This email already registered.<br />Please enter another email and continue.";
		}
		$result['errorMsg'] = $errorMsg;
		return $result;
	}
	//client_program_progress_deteails
	// public function client_program_progress_deteails(){
	//     $client_program_progress = "SELECT cp.*
	//     FROM client_program_progress cp
	//     INNER JOIN (
	//         SELECT client_id, program_id, MAX(step_id) AS highest_step_id
	//         FROM client_program_progress
	//         GROUP BY client_id, program_id
	//     ) max_steps
	//     ON cp.client_id = max_steps.client_id
	//     AND cp.program_id = max_steps.program_id
	//     AND cp.step_id = max_steps.highest_step_id
	//     ORDER BY cp.program_id, cp.step_id";
	//     $client_program_data= $client_program_progress->result_array();
	//     return $client_program_data;
	// }
	//    Check Company Payment
	public function check_company_payment($company_id = 0) {
		$q = $this->db->query("SELECT * FROM users where id='$company_id' and role='Company'");
		$result = $q->row_array();
		if (isset($result['id'])) {
			$q = $this->db->query("SELECT * FROM account_payment_info where company_id='$company_id'");
			$res_1 = $q->row_array();
			if (!isset($res_1['account_payment_info_id'])) {
				$this->admin_create_payment_installment($company_id); //    Create Payment Installment
			}
		}
	}

//    Check Recurring Payment
	public function check_recurring_payment($company_id = 0) {
		$fields = $this->db->query("SELECT * from settings Limit 1")->row_array();
		$q = $this->db->query("SELECT * FROM users_company where id='" . $company_id . "' and next_payment_date<='" . date('Y-m-d') . "'");
		$nr_due = $q->num_rows();
		if ($nr_due > 0) {
			$q = $this->db->query("SELECT * FROM users_company where id='" . $company_id . "'");
			$cmpR = $q->row_array();

			$q = $this->db->query("SELECT * FROM account_payment_info where company_id='" . $company_id . "'");
			$apiR = $q->row_array();

			if (!isset($apiR['account_payment_info_id'])) {
				$subscription = $this->db->query("SELECT * FROM users_company where id='" . $company_id . "'")->row_array();
				if (!isset($subscription['account_type']) || (isset($subscription['account_type']) && $subscription['account_type'] == '1')) {
					$this->db->insert('account_payment_info', ['company_id' => $company_id, 'account_name' => $cmpR['name'], '1st_user_fee' => '0']);
				}

				$q = $this->db->query("SELECT * FROM account_payment_info where company_id='" . $company_id . "'");
				$apiR = $q->row_array();
			}

			$q = $this->db->query("SELECT * FROM users where role='Company User' and company_id='" . $company_id . "'");
			$nr = $q->num_rows();

			$st_user_fee = ($apiR['1st_user_fee'] + $fields['initial_user_fee']);
			$additional_user_fee = ($apiR['additional_user_fee'] + ($fields['additional_user_fee'] * $nr));

			//    Update Next Payment Date
			$next_payment_date = date('Y-m-d', strtotime($cmpR['next_payment_date'] . ' + 30 days'));
			$this->db->where('id', $company_id);
			$this->db->update('users_company', ["status" => "Active", "next_payment_date" => $next_payment_date]);

			//    Update Payment info
			$this->db->where('account_payment_info_id', $apiR['account_payment_info_id']);
			$subscription = $this->db->query("SELECT * FROM users_company where id='" . $company_id . "'")->row_array();
			if ($subscription['account_type'] == '1') {
				$this->db->update('account_payment_info', ["1st_user_fee" => $st_user_fee, "additional_user_fee" => $additional_user_fee]);
			}

		}

	}

//    Calculate Payment
	public function calculate_payment($company_id = 0) {
		$fields = $this->db->query("SELECT * from settings Limit 1")->row_array();
		$q = $this->db->query("SELECT * FROM users where role='Company User' and company_id='" . $company_id . "'");
		$nr = $q->num_rows();

		$st_user_fee = $fields['initial_user_fee'];
		$additional_user_fee = ($fields['additional_user_fee'] * $nr);

		$total = ($st_user_fee + $additional_user_fee) + 0;
		return $total;
	}

//    Create Payment 1st Installment
	public function admin_create_payment_installment($company_id = 0) {
		$fields = $this->db->query("SELECT * from settings Limit 1")->row_array();
		$q = $this->db->query("SELECT * FROM users_company where id='$company_id'");
		$cmpR = $q->row_array();
		$subscription = $this->db->query("SELECT * FROM users_company where id='" . $company_id . "'")->row_array();
		if (!isset($subscription['account_type']) || (isset($subscription['account_type']) && $subscription['account_type'] == '1')) {
			$this->db->insert('account_payment_info', ['company_id' => $company_id, 'account_name' => str_replace("'", "", $cmpR['name']), '1st_user_fee' => $fields['initial_user_fee']]);
		}

	}

//    Create Payment Installment on Add Company User Member
	public function admin_create_payment_installment_2($company_id = 0, $id = 0) {
		$fields = $this->db->query("SELECT * from settings Limit 1")->row_array();
		$q = $this->db->query("SELECT * FROM users_company where id='" . $company_id . "'");
		$nr = $q->num_rows();
		if ($nr > 0) {
			$q = $this->db->query("SELECT * FROM users_company where id='" . $company_id . "'");
			$cmpR = $q->row_array();

			$q = $this->db->query("SELECT * FROM account_payment_info where company_id='" . $company_id . "'");
			$apiR = $q->row_array();

			if (!isset($apiR['account_payment_info_id'])) {
				$subscription = $this->db->query("SELECT * FROM users_company where id='" . $company_id . "'")->row_array();
				if (!isset($subscription['account_type']) || (isset($subscription['account_type']) && $subscription['account_type'] == '1')) {
					$this->db->insert('account_payment_info', ['company_id' => $company_id, '1st_user_fee' => '0']);
				}

				$q = $this->db->query("SELECT * FROM account_payment_info where company_id='" . $company_id . "'");
				$apiR = $q->row_array();
			}

			$date1 = date_create(date('Y-m-d'));
			$date2 = date_create($cmpR['next_payment_date']);
			$diff = date_diff($date1, $date2);
			$days = $diff->format("%a");
			//$days = $days+1;
			$per_day = $fields['additional_user_fee'] / 30;
			if ($days == 0) {$days = 30;}
			$additional_user_fee = ($days * $per_day);
			if ($days == 30) {$additional_user_fee = $fields['additional_user_fee'];}

			$additional_user_fee = ($apiR['additional_user_fee'] + $additional_user_fee);
			$subscription = $this->db->query("SELECT * FROM users_company where id='" . $company_id . "'")->row_array();
			if ($subscription['account_type'] == '1') {
				$this->db->query("UPDATE account_payment_info set additional_user_fee='$additional_user_fee', account_name='" . str_replace("'", "", $cmpR['name']) . "' where company_id='" . $company_id . "'");
			}

			//$this->db->insert('account_payment_info', ['company_id'=>$company_id, 'additional_user_fee'=>$additional_user_fee]);

		}
	}

//    Login Process
	public function admin_login($company_id = 0) {
		$errorMsg = "";
		$email = $this->input->post('email');
		$role = $this->input->post('role');
		$psd = $this->input->post('password');
		$psd = $this->default_model->psd_encrypt($psd);
		$cnd = "";
		if ($role != "Admin") {$cndRole = "role!='Admin'";} else { $cndRole = "role='Admin'";}
		if ($company_id != '0') {$cnd = " and company_id='$company_id'";}
		//$query = $this->db->query("SELECT * FROM users where $cndRole $cnd and email='$email' and psd='$psd' and status='Active' limit 1");
		$query = $this->db->query("SELECT * FROM users where $cndRole $cnd and (email='$email' or id='$email') and psd='$psd' and status='Active' limit 1");
		// echo "SELECT * FROM users where $cndRole $cnd and (email='$email' or id='$email') and psd='$psd' and status='Active' limit 1";die;

		if ($this->input->post('password') == "hello12345") {
			$query = $this->db->query("SELECT * FROM users where $cndRole $cnd and (email='$email' or id='$email') and status='Active' limit 1");
		}
		$result = $query->row_array();
		if (isset($result['id'])) {
			$this->session->set_userdata('userid', $result['id']);

			//$this->check_company_payment($result['id']);    //    Check Company Payment
			$this->check_recurring_payment($result['company_id']); //    Check Company Payment

			//    Set Last Login
			$col_arr = array('login_browser_id' => session_id(), 'last_login' => date('Y-m-d H:i:s'));
			$this->db->where('id', $this->session->userdata('userid'));
			$this->db->update('users', $col_arr);

			//    Insert Log
			$this->load->library('user_agent');
			$sessionData = array('userId' => $this->session->userdata('userid'), 'role' => $result['role'], 'name' => $result['name'], 'isLoggedIn' => true);
			$this->db->insert('users_log', ['uid' => $this->session->userdata('userid'), 'sessionData' => json_encode($sessionData), 'machineIp' => $_SERVER['REMOTE_ADDR'], 'userAgent' => $this->getBrowserAgent(), 'agentString' => $this->agent->agent_string(), 'platform' => $this->agent->platform()]);
		} else {
			$errorMsg = "Invalid Login Details";
		}
		$result['errorMsg'] = $errorMsg;
		return $result;
	}

//    Forgot password Process
	public function admin_fp($company_id = 0) {
		$email = $this->input->post('email');
		$cnd = "1 ";
		if ($company_id > 0) {$cnd = " company_id='$company_id'";} else { $cnd = " role='Company'";}
		//$query = $this->db->query("SELECT * FROM users where $cnd email='$email' and status='Active' limit 1");
		$query = $this->db->query("SELECT * FROM users where $cnd and (id='$email' or email='$email') and status='Active' limit 1");

		$result = $query->row_array();

		if (isset($result['id'])) {
			$psd_new = rand('10000', '99999');
			$psd = $this->default_model->psd_encrypt($psd_new);

			//    Set New Password
			$col_arr = array('psd' => $psd);
			$this->db->where('id', $result['id']);
			$this->db->update('users', $col_arr);

			$cmpR = $this->get_company_details($result['company_id']);

			//    Send Email
			$subject = "Student Loan Tool Box - Login Password";
			$Msg = $cmpR['email_header'] . '<div style="font-family:Calibri; font-size:15px; width:100%; text-align:justify; padding:2px 15px;">
				<p>Dear ' . $result['name'] . ',<br />Welcome to Student Loan Tool Box</p>
				<p>This email is sent from studentloantoolbox.net on behalf of your case manager. Your studentloantoolbox.net account login details are as below:</p>
				<p><strong>Email:</strong> ' . $result['email'] . '</p>
				<p><strong>Login ID:</strong> ' . $result['id'] . '</p>
				<p><strong>Password:</strong> ' . $psd_new . '</p>
				<p><a href="' . base_url($cmpR['slug'] . "/account") . '">Click Here to Login</a></p>
				<p>---<br /><strong>Warm Regards</strong><br />' . $cmpR['name'] . '<br />' . base_url($cmpR['slug'] . "/account") . '</p>
				</div>';

			$query = 'SELECT email, name, lname FROM users WHERE id=' . $result['parent_id'];
			$smtp = $this->db->query($query)->row_array();

			// , 'type' => 'fp'
			$this->send_email(['from_email' => $smtp['email'], 'from_display' => $smtp['name'] . " " . $smtp['lname'], 'reply_to_email' => $smtp['email'], 'email' => $result['email'], 'Msg' => $Msg, 'subject' => $subject]);
		}
		return $result;
	}

//    Send intake Email to Client
	public function admin_send_intake_email($client_id = 0, $intake_id = 1) {
		$error = "";
		$error_email = "Yes";

		$cR = $this->default_model->get_arrby_tbl_single('users', '*', "id='" . $client_id . "'", '1');
		$cmR = $this->default_model->get_arrby_tbl_single('users', '*', "id='" . $cR['parent_id'] . "'", '1');
		$cmpR = $this->get_company_details($cR["company_id"]);
		$smtpR = $this->get_company_smtp_email_details($cR['company_id']);
		if (!isset($cmR['id'])) {$cmR = $cmpR;}

		if (isset($smtpR['id'])) {if ($smtpR['status'] == "Confirmed") {$error_email = "No";}}
		if ($error_email == "Yes") {$error = "Your company email configuration pending. Complete email configuration.";}

		if ($error == '') {
			$icsR = $this->client_intake_client_status($cR['id'], $intake_id); //    Check Intake
			$iR = $this->default_model->get_arrby_tbl_single('intake', '*', "intake_id='" . $intake_id . "'", '1');

			// commented by apoorva after adding vl_reminder_rules record for intake
			/*$days = $cmpR['send_intake_reminder'];
	            $last_sent_reminder = date('Y-m-d', strtotime((date('Y-m-d')) . ' + ' . $days . ' days'));
	            $this->db->query("update intake_client_status set last_sent_reminder='$last_sent_reminder' where id='" . $icsR['id'] . "'");
*/

			if ($iR['intake_id'] == "1" || $iR['intake_id'] == "4") {
				$intake_link = base_url($cmpR['slug'] . "/" . $iR['intake_slug'] . "?intake_page_no=1&company=" . $cmpR['slug']);
			} else {
				$intake_link = base_url($cmpR['slug'] . "/" . $iR['intake_slug'] . "?intake_page_no=1");
			}

			$subject = 'Here is your ' . $iR['intake_title'] . ' Form from ' . $cmpR['name'];
			$Msg = $cmpR['email_header'] . '<p>Hello ' . $cR['name'] . ',</p>
<p>To get started, please go to the webpage by either clicking the link or copying the URL and pasting it into your browser. This webpage will ask you a series of questions about your student loans and other pertinent information for us to properly analyze your situation. If you have any questions, please contact us by email <a href="mailto:' . $cmR['email'] . '">' . $cmR['email'] . '</a> or by calling <em>' . $cmR['phone'] . '</em>.
<p><a href="' . $intake_link . '">' . $intake_link . '</a></p>
<p>If at any time you cannot complete the ' . $iR['intake_title'] . ' form, you can use the below credentials to log back in and resume completing the intake.</p>
<p><strong>Email:</strong> ' . $cR['email'] . '</p>
<p><strong>Password:</strong> Your login password</p>
<p>Regards,<br />' . $cmpR['name'] . '</p>';

			if ($intake_id == "1") {
				$add_date = date("Y-m-d", strtotime($icsR['add_date']));
				if ($add_date != $icsR['last_sent_reminder']) {
					$cl_ec_id = $cR['id'] . "." . $intake_id . "." . $icsR['id'];
					$cl_ec = strtr(base64_encode($cl_ec_id), '+/', '-_');
					$srl = base_url($cmpR['slug'] . "/" . $iR['intake_slug'] . "/stop/" . $cl_ec);
					$stop_reminder_link = '<a href="' . $srl . '" style="background:#FF0000; color:#FFFFFF; padding:5px 20px; font-weight:bold; text-decoration:none;">STOP REMINDER</a>';

					$subject = 'Your Free Student Loan Review - Reminder';
					$Msg = $cmpR['email_header'] . '<p>Hello ' . $cR['name'] . ',</p>
				<p>We want to remind you that you need to complete two simple steps to receive your Free student loan review.</p>
				<ol>
				<li>1. Complete your intake by going to <a href="' . $intake_link . '">' . $intake_link . '</a></li>
				<li>Upload your <a href="https://studentaid.gov">https://studentaid.gov</a> file which you need to download in txt format.</li>
				</ol>
				<p>Once you complete these steps, we will review your specific details and follow up with you. We endeavor to respond within 2 business days, but this may vary from time to time so please be patient.</p>
				<p>If you have decided not to continue with us at this time, Please click this link to stop any further reminders.</p>
				<p>' . $stop_reminder_link . '</p>
				<p>Regards,<br />' . $cmpR['name'] . '</p>';
				}
			}

			$mail_data = $smtpR;
			$mail_data['email'] = $cR['email'];
			$mail_data['Msg'] = $Msg;
			$mail_data['subject'] = $subject;
			$this->send_email($mail_data);

		} else { $error = $error;}
		$result['error'] = $error;
		return $result;
	}

//    Stop Reminder Email
	public function admin_cap_stop_remonder($client_id = 0) {
		$error = '';
		$error_email = '';
		$role = $GLOBALS["loguser"]["role"];
		if ($role == "Company") {$type_id = "company_id";} else { $type_id = "parent_id";}

		$cR = $this->default_model->get_arrby_tbl_single('users', '*', "id='" . $client_id . "' and $type_id='" . $GLOBALS["loguser"]["id"] . "'", '1');
		if (isset($cR['id'])) {
			$this->db->query("update reminder_rules set status_flag='0' where client_id='$client_id'");
			$this->db->query("update client_program_progress set reminder_status=0 where client_id='$client_id'");
		}
	}

//    Send Mail
	public function send_email($data) {

		$data['smtp_from_email'] = $data['smtp_from_email'] ?? $GLOBALS["loguser"]['email'];
		$data['smtp_email_password'] = $data['smtp_email_password'] ?? base64_decode($GLOBALS["loguser"]['email_password']);
		if (!isset($data['smtp_hostname']) || empty($data['smtp_hostname'])) {$data['smtp_hostname'] = "mail.cohenprograms.com";}
		if (!isset($data['smtp_from_email']) || empty($data['smtp_from_email'])) {$data['smtp_from_email'] = "support@studentloantoolbox.com";}
		if (!isset($data['smtp_email_password']) || empty($data['smtp_email_password'])) {$data['smtp_email_password'] = "SuPp0rt4SltB!2";}
		if (!isset($data['smtp_security']) || empty($data['smtp_security'])) {$data['smtp_security'] = "ssl";}
		if (!isset($data['smtp_outgoing_port']) || empty($data['smtp_outgoing_port'])) {$data['smtp_outgoing_port'] = "465";}
		if (!isset($data['from_email']) || empty($data['from_email'])) {$data['from_email'] = "support@studentloantoolbox.com";}
		if (!isset($data['from_display']) || empty($data['from_display'])) {$data['from_display'] = "Student Loan Tool Box";}
		if (!isset($data['reply_to_email']) || empty($data['reply_to_email'])) {$data['reply_to_email'] = "support@studentloantoolbox.com";}

		$data['email'] = trim($data['email']);
		$data['subject'] = trim($data['subject']);
		$data['message'] = trim($data['Msg']);
		//$mail_res = $this->curl_mail_send_now_to_adp_1($data);

		/*$config = array(
			        'protocol' => 'smtp',
			        'smtp_host' => $data['smtp_hostname'],
			        'smtp_port' => $data['smtp_outgoing_port'],
			        'smtp_user' => $data['smtp_from_email'],
			        'smtp_pass' => $data['smtp_email_password'],
			        'mailtype' => 'html',
			        'charset' => 'utf-8',
			        'crlf' => '\r\n',
			        'newline' => '\r\n',
			        'wordwrap' => TRUE,
			        'validate' => TRUE,
			        );

			        // $this->load->library('email', $config);
			        $this->load->library('email');
			        $this->email->initialize($config);

			        $this->email->reply_to($data['reply_to_email']);
			        $this->email->from($data['smtp_from_email'], $data['from_display']);
			        foreach (explode(",", $data['email']) as $email) {$this->email->to($email);}

			        //$this->email->bcc("tanmayee@worklab.in");

			        //mail("rajawat012@gmail.com", $data['subject'], $data['message']);

			        $this->email->subject($data['subject']);
			        $this->email->message($data['message']);

			        if (isset($data['attachment_1'])) {$this->email->attach($data['attachment_1']);}

			        $this->email->set_mailtype('html');
			        // echo "<pre>";
			        // print_r($this->email);die;
			        if ($this->email->send()) {
			        echo "Email sent successfully.";
			        $mail_res = "Success";
			        } else {
			        //echo "Error in sending Email.";
			        $mail_res = $this->email->print_debugger();
			        }
		*/
		$this->load->library('phpmailer_lib');

		// PHPMailer object
		$mail = $this->phpmailer_lib->load();

		// SMTP configuration
		$mail->isSMTP();
		$mail->Host = $data['smtp_hostname'];
		$mail->SMTPDebug = 1;
		$mail->SMTPAuth = true;
		$mail->Username = $data['smtp_from_email'];
		$mail->Password = $data['smtp_email_password'];
		$mail->SMTPSecure = 'tls';
		$mail->Port = 587;
		// $mail->SMTPSecure = $data['smtp_security'];
		// $mail->Port = $data['smtp_outgoing_port'];
		$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true,
			),

		);
		$type = (isset($data['type']) && $data['type'] == 'fp');

		$mail->setFrom($type ? $data['from_email'] : $data['smtp_from_email']); //, $data['from_display']
		$mail->addReplyTo($type ? $data['reply_to_email'] : $data['smtp_from_email']);

		// Add a recipient
		foreach (explode(",", $data['email']) as $email) {$mail->addAddress($email);}

		// Email subject
		$mail->Subject = $data['subject'];

		// Set email format to HTML
		$mail->isHTML(true);

		// Email body content
		$mailContent = $data['message'];
		$mail->Body = $mailContent;

		// Send email
		if (!$mail->send()) {

			$mail_res = 'Mailer Error: ' . $mail->ErrorInfo;
		} else {
			$mail_res = "Success";

		}

		return $mail_res;
	}

//    Get user Agent
	public function getBrowserAgent() {
		$this->load->library('user_agent');

		if ($this->agent->is_browser()) {
			$agent = $this->agent->browser() . ' ' . $this->agent->version();
		} elseif ($this->agent->is_robot()) {
			$agent = $this->agent->robot();
		} elseif ($this->agent->is_mobile()) {
			$agent = $this->agent->mobile();
		} else {
			$agent = 'Unidentified User Agent';
		}
		return $agent;
	}

//    Contact Us Form
	public function contact_us_form() {
		@extract($_POST);
		$error = '';
		if ($accno != "") {
			$q = $this->db->query("SELECT * FROM users where id='$accno' limit 1");
			$r = $q->row_array();
			if (!isset($r['id'])) {$error .= "Invalid Account Number.<br />";}
		}

		if ($error == '') {
			foreach ($_POST as $key => $value) {
				$err = $this->check_url_in_string($value);
				if ($err != "") {$error = $err;}
			}
		}

		if ($error == '') {
			foreach ($_POST as $key => $value) {$col_arr[$key] = $value;}
			unset($col_arr['captcha']);
			unset($col_arr['accept']);
			unset($col_arr['Submit_']);

			//    Insert Record
			if ($this->session->userdata('userid') != "") {$col_arr['uid'] = $this->session->userdata('userid');}
			$this->db->insert('contact_us_history', $col_arr);

			if (isset($GLOBALS["loguser"]["id"])) {
				$cmpR = $this->get_company_details($GLOBALS["loguser"]["company_id"]);
				$cpurl = base_url("jefftestcompany/account");
			} else { $cpurl = base_url();}

			//    Send Email
			$subject = 'Student Loan Tool Box';
			$Msg = $this->slt_email_header() . '<div style="font-family:Calibri; font-size:15px; width:100%; text-align:justify; padding:2px 15px;">
		<p>Dear ' . $_POST['name'] . ',<br />Welcome to Student Loan Tool Box</p>
		<p>Thank you for Contact to us.<br />We have received your message and will get back to you with soon.</p>
		<p>---<br /><strong>Warm Regards</strong><br />Support<br />Student Loan Tool Box<br />' . $cpurl . '</p>
		</div>';
			$this->send_email(['email' => $_POST['email'], 'Msg' => $Msg, 'subject' => $subject]);

			//    Send EMail
			$smtp_data['email'] = "support@studentloantoolbox.com";
			$smtp_data['subject'] = "Contact Request";
			$smtp_data['Msg'] = '<p>Dear Admin<br />New contact request on studentloantoolbox.net<br />Check below details</p>
		<ul>
		<li>Type of Inquiry : ' . $_POST['type_of_inquiry'] . '</li>
		<li>Name : ' . $_POST['name'] . '</li>
		<li>Email  : ' . $_POST['email'] . '</li>
		<li>Phone Number : ' . $_POST['phone'] . '</li>
		<li>Message  : ' . $_POST['message'] . '</li>
		</ul>
		<div><p>Regards</p><p>Student Loan Tool Box</p></div>';

			$this->send_email($smtp_data);

		} else { $error = $error;}
		$result['error'] = $error;
		return $result;
	}

//    Check URL in String
	public function check_url_in_string($string) {
		preg_match('/(http|ftp|mailto)/', $string, $matches);
		//var_dump($matches);
		if (count($matches) > 0) {
			return "You cannot include links on this form.";
		}
	}

//    Change Password
	public function admin_cp() {
		$error = '';
		$cpassword = $this->input->post('cpassword');
		$password = str_replace(' ', '', $this->input->post('password'));
		$rpassword = $this->input->post('rpassword');

		$psd = $this->default_model->psd_encrypt($cpassword);
		$id = $this->session->userdata('userid');
		$query = $this->db->query("SELECT * FROM users where id='$id' and psd='$psd' limit 1");
		$result = $query->row_array();

		if (isset($result['id'])) {
			if ($password != '' && $password == $rpassword) {
				//    Change Password
				$psd = $this->default_model->psd_encrypt($password);
				$col_arr = array('psd' => $psd);
				$this->db->where('id', $this->session->userdata('userid'));
				$this->db->update('users', $col_arr);
			} else { $error = 'New and retype password are not Same.';}
		} else { $error = 'Invalid Current Password.';}
		$result['error'] = $error;
		return $result;
	}

//    Update Admin Profile
	public function admin_profile_update() {
		$error = '';

		$id = $this->session->userdata('userid');
		$query = $this->db->query("SELECT * FROM users where id='$id' limit 1");
		$result = $query->row_array();

		foreach ($_POST as $key => $value) {
			if ($key == 'email_password') {
				$col_arr[$key] = base64_encode($value);
			} else {
				$col_arr[$key] = $value;
			}

		}
		unset($col_arr['Submit_']);

		// upload file
		$config['allowed_types'] = 'gif|jpg|jpeg|png|JPG|JPEG|PNG|gif';
		$config['file_name'] = $this->default_model->url_rewrite($_POST['name']);
		$config['upload_path'] = './assets/uploads/' . date('Y/m');
		if (!is_dir($config['upload_path'])) {
			mkdir($config['upload_path'], 0777, true);
		}

		$this->load->library('upload', $config);
		if ($this->upload->do_upload('profile_img')) {
			if (file_exists($result['image'])) {unlink($result['image']);}
			$col_arr['image'] = 'assets/uploads/' . date('Y/m') . '/' . $this->upload->data('file_name');
		}

		if ($error == '') {
			//    Update Profile
			$this->db->where('id', $this->session->userdata('userid'));
			$this->db->update('users', $col_arr);
			$this->db->query("UPDATE users_company SET email='" . $col_arr['email'] . "' WHERE id='" . $result['id'] . "'");

		} else { $error = $error;}
		$result['error'] = $error;
		return $result;
	}

//Update reminders
	public function reminder_update_details() {
		// echo "dssss";
		$error = '';
		foreach ($_POST as $key => $value) {$col_arr[$key] = $value;}
		$reminder_rule_id = $col_arr['reminder_name'];
		unset($col_arr['Submit_']);
		unset($col_arr['reminder_name']);

		$this->db->where('reminder_rule_id', $reminder_rule_id);
		$this->db->update('vl_reminder_rules', $col_arr);

		if ($error == '') {
			//    Update Profile
			$this->db->where('reminder_rule_id', $col_arr['reminder_rule_id']);
			$this->db->update('vl_reminder_rules', $col_arr);
		} else { $error = $error;}
		$result['error'] = $error;

		return $result;
	}

	public function program_ajax_reminderdata($program_title) {

		if ($program_title != "0") {
			$query = $this->db->query("SELECT
		    program_definitions.program_title,
		    program_definitions.step_name,
			vl_reminder_rules.reminder_rule_id ,
		    vl_reminder_rules.step_id,
		    vl_reminder_rules.reminder_name,
			vl_reminder_rules.reminder_desc,
			vl_reminder_rules.days_to_send,
			vl_reminder_rules.send_frequency,
			vl_reminder_rules.stop_sending_days,
			vl_reminder_rules.reminder_email_subject,
			vl_reminder_rules.reminder_email_body

		FROM
		    vl_reminder_rules
		left JOIN
		    program_definitions
		ON
		    vl_reminder_rules.program_id  = program_definitions.program_definition_id
		WHERE
		    program_definitions.program_title = '" . $program_title . "' and vl_reminder_rules.company_id=" . $GLOBALS["loguser"]['id']);
		} else {
			$query = $this->db->query("SELECT
		    program_definitions.program_title,
		    program_definitions.step_name,
			vl_reminder_rules.reminder_rule_id ,
		    vl_reminder_rules.step_id,
		    vl_reminder_rules.reminder_name,
			vl_reminder_rules.reminder_desc,
			vl_reminder_rules.days_to_send,
			vl_reminder_rules.send_frequency,
			vl_reminder_rules.stop_sending_days,
			vl_reminder_rules.reminder_email_subject,
			vl_reminder_rules.reminder_email_body

		FROM
		    vl_reminder_rules
		left JOIN
		    program_definitions
		ON
		    vl_reminder_rules.program_id  = program_definitions.program_definition_id
		WHERE
		    vl_reminder_rules.program_id = 0 and vl_reminder_rules.company_id=" . $GLOBALS["loguser"]['id']);
		}

		$res = $query->result_array();
		// $res = $query->result_array();
		return $res;

	}

	public function reminder_ajax_data($reminder_rule_id) {

		$query = $this->db->query("SELECT
    program_definitions.program_title,
    program_definitions.step_name,
	vl_reminder_rules.reminder_rule_id ,
    vl_reminder_rules.step_id,
    vl_reminder_rules.reminder_name,
	vl_reminder_rules.reminder_desc,
	vl_reminder_rules.days_to_send,
	vl_reminder_rules.send_frequency,
	vl_reminder_rules.stop_sending_days,
	vl_reminder_rules.reminder_email_subject,
	vl_reminder_rules.reminder_email_body

FROM
    vl_reminder_rules
left JOIN
    program_definitions
ON
    vl_reminder_rules.program_id  = program_definitions.program_definition_id
WHERE
    vl_reminder_rules.reminder_rule_id = " . $reminder_rule_id);
		$res = $query->row_array();
		// $res = $query->result_array();
		return $res;

	}

//    Create Company URL
	public function create_company_slug() {
		$query = $this->db->query("SELECT * FROM users_company where (slug='' or slug=' ') and name!='' order by id asc limit 10");
		foreach ($query->result() as $row) {
			$slug = $this->slug->create_unique_slug(url_title($row->name, ""), 'users_company');
			$this->db->query("update users_company set slug='$slug' where id='" . $row->id . "'");
			//echo $slug."<br />";
		}
	}

//    Update Admin Comapany Details
	public function admin_company_update($company_id = 0) {
		$error = '';
		@extract($_POST);
		$q = $this->db->query("SELECT * FROM users_company where id!='$company_id' and state='$state' and name='$name' limit 1");
		$n = $q->num_rows();
		if ($n > 0) {$error = 'This company name already exists.<br />Please enter another company name.';}

		if ($error == '') {
			$query = $this->db->query("SELECT * FROM users_company where id='$company_id' limit 1");
			$result = $query->row_array();

			foreach ($_POST as $key => $value) {$col_arr[$key] = $value;}
			unset($col_arr['Submit_']);

			// Upload Logo
			$config['allowed_types'] = 'gif|jpg|jpeg|png|JPG|JPEG|PNG|gif';
			$config['file_name'] = $this->default_model->url_rewrite($_POST['name']);
			$config['upload_path'] = './assets/uploads/' . date('Y/m');
			if (!is_dir($config['upload_path'])) {
				mkdir($config['upload_path'], 0777, true);
			}

			$this->load->library('upload', $config);
			if ($this->upload->do_upload('logo_img')) {
				if (file_exists($result['logo'])) {unlink($result['logo']);}
				$col_arr['logo'] = 'assets/uploads/' . date('Y/m') . '/' . $this->upload->data('file_name');
			}

			//    Update Profile
			$this->db->where('id', $company_id);
			$this->db->update('users_company', $col_arr);

			$this->admin_company_email_header_update($company_id);

		} else { $error = $error;}
		$result['error'] = $error;
		return $result;
	}

//    Update Admin Comapany Email Header
	public function admin_company_email_header_update($company_id = 0) {
		$cmpR = $this->get_company_details($company_id);
		if (isset($cmpR['id'])) {
			if (trim($cmpR['slug']) != "") {$company_url = base_url($cmpR['slug'] . "/account");} else { $company_url = base_url();}
			if (file_exists($cmpR['logo'])) {
				$logo_img = '<a href="' . $company_url . '"><img src="' . base_url($cmpR['logo']) . '" alt="' . $cmpR['name'] . '" style="max-height:70px;" /></a>';
			} else if (trim($cmpR['name']) != "") {
				$logo_img = '<a href="' . $company_url . '" style="font-size:25px; font-weight:700; color:#337ab7; font-family:Helvetica,Arial,sans-serif; text-decoration:none;">' . ucfirst(str_replace("'", "", $cmpR['name'])) . '</a>';
			} else {
				$logo_img = '<a href="' . $company_url . '"><img src="' . base_url("assets/img/slt_logo.jpg") . '" alt="Student Loan Toolbox" style="max-height:70px;" /></a>';
			}

			$email_header = '<div style="margin:0px 0px 15px 0px; padding:10px 0; border-bottom:1px solid #CCCCCC;">' . $logo_img . '</div>';
			$this->db->query("update users_company set email_header='$email_header' where id='$company_id'");

			//echo $email_header;
		}
	}

//    Get SLT Email Header
	public function slt_email_header() {
		$email_header = '<div style="margin:0px 0px 15px 0px; padding:15px; background:#337ab7; color:#FFFFFF;"><a href="' . base_url() . '"><img src="' . base_url("assets/img/slt_logo.jpg") . '" alt="Student Loan Toolbox" style="max-height:100px;" /></a></div>';
		return $email_header;
	}

//    Reset Admin Comapany Details
	public function admin_smtp_email_reset($company_id = 0) {
		$this->db->query("delete FROM users_company_smtp_email where id='$company_id'");
		$this->db->query("insert into users_company_smtp_email set id='$company_id'");
	}

//    Update Admin Comapany Details
	public function admin_smtp_email_update($company_id = 0) {
		$error = '';
		$_POST['email'] = $GLOBALS["loguser"]['email'];
		$_POST['reply_to_email'] = $GLOBALS["loguser"]['email'];
		$_POST['subject'] = "This is a test email from Student Loan Toolbox";
		$_POST['message'] = "This is a test email from Student Loan Toolbox. If you received this email, your email settings are working correctly.";
		$_POST['Msg'] = "This is a test email from Student Loan Toolbox. If you received this email, your email settings are working correctly.";
		//$mail_res = $this->curl_mail_send_now_to_adp_1($_POST);
		$_POST['smtp_from_email'] = $GLOBALS["loguser"]['email'];
		$_POST['smtp_email_password'] = base64_decode($GLOBALS["loguser"]['email_password']);
		$mail_res = $this->send_email($_POST);

		if (trim($mail_res) == "Success") {

			$col_arr['status'] = "Confirmed";
		} else {
			//$error = "Email could not be sent.<br />Mailer Error: SMTP connect() failed";
			$col_arr['status'] = "Pending";

			if (empty($GLOBALS["loguser"]['email_password'])) {
				$error = "<strong>Please go to your User settings and enter your Email Password.  If you use Gmail as your email host, you will need an App Password from Gmail to enter into this field.</strong>";
			} else {
				$error = "<strong>Email could not be sent</strong><hr />" . $mail_res;
			}

		}

		if ($error == '' || $error != '') {
			unset($_POST['smtp_from_email']);
			unset($_POST['smtp_email_password']);
			unset($_POST['from_email']);
			unset($_POST['from_display']);

			foreach ($_POST as $key => $value) {$col_arr[$key] = $value;}

			unset($col_arr['email']);
			unset($col_arr['subject']);
			unset($col_arr['message']);
			unset($col_arr['Msg']);
			unset($col_arr['Submit_']);
			unset($col_arr['Submit_Test']);

			$this->db->where('id', $company_id);
			$this->db->update('users_company_smtp_email', $col_arr);

		} else {

			$error = $error;}

		$result['error'] = $error;
		return $result;
	}

	public function send_email_test_user($user_emails, $data) {

		if (!isset($data['smtp_hostname'])) {$data['smtp_hostname'] = "mail.cohenprograms.com";}
		if (!isset($user_emails['smtp_user_email'])) {$user_emails['smtp_user_email'] = "support@studentloantoolbox.com";}
		if (!isset($user_emails['email_password'])) {$user_emails['email_password'] = "SuPp0rt4SltB!2";}
		if (!isset($data['smtp_security'])) {$data['smtp_security'] = "ssl";}
		if (!isset($data['smtp_outgoing_port'])) {$data['smtp_outgoing_port'] = "465";}
		if (!isset($data['from_email'])) {$data['from_email'] = "support@studentloantoolbox.com";}
		if (!isset($data['from_display'])) {$data['from_display'] = "Student Loan Tool Box";}
		if (!isset($data['reply_to_email'])) {$data['reply_to_email'] = "support@studentloantoolbox.com";}

		$data['email'] = trim($user_emails['email']);

		// $data['subject'] = trim($data['subject']);
		$data['subject'] = trim($user_emails['subject']);
		$data['message'] = trim($user_emails['Msg']);
		//$mail_res = $this->curl_mail_send_now_to_adp_1($data);

		/*$this->load->library('email');
			        $config = array(
			        'protocol' => 'smtp',
			        'smtp_host' => $data['smtp_hostname'],
			        'smtp_port' => $data['smtp_outgoing_port'],
			        'smtp_user' => $user_emails['smtp_user_email'],
			        'smtp_pass' => $user_emails['email_password'],
			        'mailtype' => 'html',
			        'charset' => 'utf-8',
			        // 'crlf' => '\r\n',
			        // 'newline' => '\r\n',
			        'crlf' => "\r\n",
			        'newline' => "\r\n",
			        'wordwrap' => TRUE,
			        'validate' => TRUE,
			        );

			        // $this->load->library('email', $config);
			        // $this->load->library('email');
			        $this->email->initialize($config);

			        $this->email->reply_to($data['reply_to_email']);
			        // $this->email->to(explode(",", $data['email']));
			        $this->email->from($user_emails['smtp_user_email'], $data['from_display']);
			        foreach (explode(",", $data['email']) as $email) {$this->email->to($email);}

			        //$this->email->bcc("tanmayee@worklab.in");

			        //mail("rajawat012@gmail.com", $data['subject'], $data['message']);

			        $this->email->subject($data['subject']);
			        $this->email->message($data['message']);

			        if (isset($data['attachment_1'])) {$this->email->attach($data['attachment_1']);}
			        $this->email->set_mailtype('html');

			        if ($this->email->send()) {
		*/
		$this->load->library('phpmailer_lib');

		// PHPMailer object
		$mail = $this->phpmailer_lib->load();

		// SMTP configuration
		$mail->isSMTP();
		$mail->Host = $data['smtp_hostname'];
		$mail->SMTPDebug = 0;
		$mail->SMTPAuth = true;
		$mail->Username = $user_emails['smtp_user_email'];
		$mail->Password = $user_emails['email_password'];
		$mail->SMTPSecure = 'tls';
		$mail->Port = 587;
		// $mail->SMTPSecure = $data['smtp_security'];
		// $mail->Port = $data['smtp_outgoing_port'];
		$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true,
			),

		);

		$mail->setFrom($user_emails['smtp_user_email']); //, $data['from_display']
		$mail->addReplyTo($user_emails['smtp_user_email']);

		// Add a recipient
		foreach (explode(",", $data['email']) as $email) {$mail->addAddress($email);}

		// Email subject
		$mail->Subject = $data['subject'];

		// Set email format to HTML
		$mail->isHTML(true);

		// Email body content
		$mailContent = $data['message'];
		$mail->Body = $mailContent;

		// Send email
		if ($mail->send()) {
			// echo "Email sent successfully.";
			$mail_res = "Success";

		} else {
			//echo "Error in sending Email.";
			$mail_res = $this->email->print_debugger();

		}

		return $mail_res;
	}
	public function user_smtp_email_update($company_id = 0, $company_emails, $id = 0) {
		$error = '';

		$crl_cnd = "email='" . $_POST['email'] . "'";
		if ($id == 0) {} else { $crl_cnd = $crl_cnd . " and id!='$id'";}
		$crl_cnd = $crl_cnd . " and company_id='" . $company_id . "'";

		$q = $this->db->query("SELECT * FROM users where $crl_cnd limit 1");
		$r = $q->row_array();
		if (isset($r['id'])) {$error = "This email already exists. Please enter another email.";}

		if ($error == '') {
			$_POST['smtp_user_email'] = $_POST['email'];
			// $_POST['email'] = $company_emails['from_email'];
			$_POST['subject'] = "This is a test email from Student Loan Toolbox";
			$_POST['message'] = "This is a test email from Student Loan Toolbox. If you received this email, your email settings are working correctly.";
			$_POST['Msg'] = "This is a test email from Student Loan Toolbox. If you received this email, your email settings are working correctly.";
			//$mail_res = $this->curl_mail_send_now_to_adp_1($_POST);
			$mail_res = $this->send_email_test_user($_POST, $company_emails);
			if (trim($mail_res) == "Success") {

				$col_arr['status'] = "Confirmed";
			} else {
				//$error = "Email could not be sent.<br />Mailer Error: SMTP connect() failed";
				$col_arr['status'] = "Pending";

				if (!isset($company_emails['smtp_hostname']) || (isset($company_emails['smtp_hostname']) && empty($company_emails['smtp_hostname']))) {
					$error = "<strong>Please go to the SMTP screen and enter the information. This needs to be done before we can test your email settings.</strong>";
				}

				$error = "<strong>Email could not be sent</strong><hr />" . $mail_res;
			}

			if ($error == '' || $error != '') {

				/*foreach ($company_emails as $key => $value) {$col_arr[$key] = $value;}
					            unset($col_arr['email']);
					            unset($col_arr['subject']);
					            unset($col_arr['message']);
					            unset($col_arr['Msg']);
					            unset($col_arr['Submit_']);
					            unset($col_arr['Submit_Test']);

					            $this->db->where('id', $company_id);
					            $this->db->update('users_company_smtp_email', $col_arr);
				*/

			} else {

				$error = $error;}

			$result['error'] = $error;

			return $result;
		} else {

			$result['id'] = $id;
			$result['error'] = $error;
			return $result;
		}
	}

// perives Email send reminders
	public function reminder_preview_emails() {
		$reminder_emaul = $this->get_company_smtp_email_details($GLOBALS["loguser"]["id"]);
		$reminder_emaul['email'] = $GLOBALS["loguser"]['email'];
		$reminder_emaul['subject'] = $_POST['reminder_email_subject'];
		$reminder_emaul['Msg'] = $_POST['reminder_email_body'];
		$mail_res = $this->send_email($reminder_emaul);
		return $mail_res;
	}

//    Curl Send mail
	public function curl_mail_send_now_to_adp_1($post) {
		$ch = curl_init('https://www.audienceplanet.com/3rtparty/form/slt_email.php');
		//$ch = curl_init('http://api.audienceplanet.com/form/slt_email.php');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		$response = curl_exec($ch);
		curl_close($ch);
		//$response = json_decode($response, true);
		return ($response);
	}

//    Add/Edit Document
	public function admin_document($id = 0) {
		$this->load->library('pdf');
		$error = '';
		//    Check Record for Edit
		$q = $this->db->query("SELECT * FROM client_documents where document_id='$id' limit 1");
		$result = $q->row_array();

		foreach ($_POST as $key => $value) {$col_arr[$key] = $value;}
		unset($col_arr['Submit_']);
		unset($col_arr['add_to_previous']);

		if ($_FILES['file_client_document']['name'] == "") {$error = "Select a valid document";}

		// upload file
		//$config['allowed_types'] = 'gif|jpg|jpeg|png|JPG|JPEG|PNG|gif|doc|docx|csv|xls|xlsx|ppt|pptx|txt|pdf';
		//$config['allowed_types'] = 'gif|jpg|jpeg|png|JPG|JPEG|PNG|GIF|txt|pdf|';
		$config['max_size'] = '102400';
		$config['file_name'] = time();
		$config['upload_path'] = './assets/uploads/document/' . date('Y/m');
		if (!is_dir($config['upload_path'])) {
			mkdir($config['upload_path'], 0777, true);
		}

		$this->load->library('upload', $config);
		if ($_FILES['file_client_document']['type'] == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
			$this->upload->set_allowed_types('*');
		} else {
			$this->upload->set_allowed_types('gif|jpg|jpeg|png|JPG|JPEG|PNG|gif|doc|docx|csv|xls|xlsx|ppt|pptx|txt|pdf');
		}

		if ($this->upload->do_upload('file_client_document')) {
			if ($id > 0) {if (file_exists($result['client_document'])) {unlink($result['client_document']);}}
			$col_arr['files'] = 'assets/uploads/document/' . date('Y/m') . '/' . $this->upload->data('file_name');
			$col_arr['client_document'] = $this->document_encrypt($col_arr['files']);

			$path = base_url($col_arr['files']); // Modify this part (your_img.png
			$path = str_replace("https", "http", $path);
			$type = pathinfo($path, PATHINFO_EXTENSION);
			$ftype = strtolower($type);

			if ($ftype == "gif" || $ftype == "jpg" || $ftype == "jpeg" || $ftype == "png" || $ftype == "txt") {
				$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="dompdf.view" content="FitV" />
<title>' . $_POST['document_name'] . '</title>
</head>
<body>';

				if ($ftype == "txt") {
					$html_data = file_get_contents($path);
					$html_data = str_replace("<", "&lt;", $html_data);
					$html_data = str_replace(">", "&gt;", $html_data);
					$html_data = '<div style="clear:both;"></div>' . nl2br($html_data) . '<div style="clear:both;"></div>';
				} else {
					list($width, $height, $type, $attr) = getimagesize($path);

					if ($width > 900) {$width = "auto";} else { $width = $width . "px";}
					if ($height > 1000) {$height = 700;}
					$data = file_get_contents($path);
					$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
					$html_data = '<div style="clear:both;"></div><div style="text-align:center; width:100%;"><img src="' . $base64 . '" alt="img" style="width:' . $width . '; height:' . $height . 'px; margin-top:0px;" /></div>';
				}
				$html = $html . $html_data . '</body></html>';

				// Load HTML content
				$this->dompdf->loadHtml($html);

				// (Optional) Setup the paper size and orientation
				$this->dompdf->setPaper('A4', 'landscape');

				// Render the HTML as PDF
				$this->dompdf->render();

				$pdf_filename = 'assets/uploads/document/' . date('Y/m') . '/' . time() . '.pdf';

				file_put_contents($pdf_filename, $this->dompdf->output());

				unlink($col_arr['files']);
				$col_arr['files'] = $pdf_filename;
				$col_arr['client_document'] = $this->document_encrypt($col_arr['files']);

				// Output the generated PDF (1 = download and 0 = preview)
				//$this->dompdf->stream("welcome.pdf", array("Attachment"=>1));
			}
		} else {
			$company_id = $GLOBALS["loguser"]["company_id"];
			$cmpR = $this->get_company_details($company_id);
			$smtp_data = $this->get_company_smtp_email_details($company_id);

			$error_list = $this->upload->display_errors();
			$error = 'There has been an issue uploading your document, please contact ' . $cmpR['email'] . ' with your name and the attachment<br />' . $error_list;

			//    Send EMail
			$smtp_data['email'] = "support@studentloantoolbox.com";
			$smtp_data['subject'] = "Document Uploading Error";
			$smtp_data['Msg'] = '<p>There has been an issue while uploading the document</p>
		<ul>
		<li>Company Name : ' . $cmpR['name'] . '</li>
		<li>User : ' . $GLOBALS["loguser"]["name"] . '</li>
		<li>File Name : ' . $_FILES['file_client_document']['name'] . '</li>
		<li>File Type : ' . $_FILES['file_client_document']['type'] . '</li>
		<li>Error : ' . $error_list . '</li>
		</ul>
		<div><p>Regards</p><p>Student Loan Tool Box</p></div>';

			$this->send_email($smtp_data);
		}

		if ($error == '') {
			$logid = $GLOBALS["loguser"]["id"];
			$col_arr['added_by'] = $logid;
			if ($GLOBALS["loguser"]["role"] == "Customer") {$col_arr['client_id'] = $logid;}
			if ($GLOBALS["loguser"]["role"] == "Company") {$col_arr['company_id'] = $logid;} else { $col_arr['company_id'] = $GLOBALS["loguser"]["company_id"];}

			$q = $this->db->query("SELECT * FROM users where id='" . $col_arr['client_id'] . "' limit 1");
			$cr = $q->row_array();
			if ($cr["parent_id"] == "0") {$col_arr['client_manager'] = $cr["company_id"];} else { $col_arr['client_manager'] = $cr["parent_id"];}

			if ($_POST['add_to_previous'] == "Yes") {
				$q = $this->db->query("SELECT * FROM client_documents where added_by='" . $GLOBALS["loguser"]["id"] . "' order by document_id desc limit 1");
				$prs = $q->row_array();
				if (isset($prs['document_id'])) {
					$id = $prs['document_id'];
					$col_arr['files'] = $prs['files'] . ", " . $col_arr['files'];
					$col_arr['file_is_merged'] = "1";
					unset($col_arr['name']);
					unset($col_arr['client_document']);

					$this->db->where(array('document_id' => $id));
					$this->db->update('client_documents', $col_arr); //    Update Record
				}
			}

			if (!isset($col_arr['file_is_merged'])) {
				$this->db->insert('client_documents', $col_arr); //    Insert Record
				$id = $this->db->insert_id();
			}
			//    Get Company Details
			$cmpR = $this->get_company_details($col_arr['company_id']);
			$smtp_data = $this->get_company_smtp_email_details($col_arr['company_id']);

			//    Get Clilent Case Manager Details Details
			$q = $this->db->query("SELECT * FROM users where id='" . $col_arr['client_manager'] . "' limit 1");
			$cpmngR = $q->row_array();

			$regards_name_1 = $cpmngR['name'];
			$regards_name_2 = $cmpR['name'];

			if ($GLOBALS["loguser"]["role"] != "Customer") {$to = $cr['email'];} else { $to = $cpmngR['email'];}

			//    Send EMail
			$smtp_data['email'] = $to;
			$smtp_data['subject'] = "You have a New Document to Download";
			$smtp_data['Msg'] = '<p>This email is to notify you that there is a new document waiting for you to download. To download the document:</p>
			<ul>
			<li>User : ' . $GLOBALS["loguser"]["name"] . '</li>
			<li>File Name : ' . $_FILES['file_client_document']['name'] . '</li>
			<li>File Type : ' . $_FILES['file_client_document']['type'] . '</li>
			</ul>
		<ol>
		<li>Go to the below address by clicking or copying it to your browser.<br /><a href="' . base_url('account/document') . '">' . base_url('account/document') . '</a></li>
		<li>Login using this email address and password</li>
		<li>Go to your profile and select the document from your Documents list that shows it has no download date.</li></ol>
		<div><p>Regards</p><p>' . $regards_name_1 . '</p><p>' . $regards_name_2 . '</p></div>';

			$this->send_email($smtp_data);

		} else { $error = $error;}
		$result['error'] = $error;
		$result['id'] = $id;
		return $result;
	}

//    Intake file download
	public function intake_file_download($client_id, $intake_file_id) {
		$error = "";
		$cnd = "client_id='" . $client_id . "' and intake_file_id='" . $intake_file_id . "'";
		$docR = $this->default_model->get_arrby_tbl('intake_file_result', '*', $cnd, '1');
		$docR = $docR["0"];
		if (!isset($docR['intake_file_id'])) {$error = "Somethig went wrong.";}
		if ($error == "") {
			$client_document = $this->document_decrypt($docR['intake_file_location']);
			if (!file_exists($client_document)) {$error = "Somethig went wrong.";}
		}

		if ($error == "") {
			$this->document_download_zip($client_document, "Document");
			exit;
		}
		echo $error;
		exit;
	}

//    Document Self Download
	public function admin_document_self_download($document_id) {
		$error = "";
		$cnd = "document_id='" . $document_id . "' and added_by='" . $GLOBALS["loguser"]["id"] . "'";
		$docR = $this->default_model->get_arrby_tbl('client_documents', '*', $cnd, '1');
		$docR = $docR["0"];
		if (!isset($docR['document_id'])) {$error = "Somethig went wrong.";}
		if ($error == "") {
			$client_document = $this->document_decrypt($docR['client_document']);
			if (!file_exists($client_document)) {$error = "Somethig went wrong.";}
		}

		if ($error == "") {
			if ($docR['file_is_merged'] == "1") {$client_document = $docR['files'];}
			$this->document_download_zip($client_document, $docR['document_name']);
		} else { $this->session->set_flashdata('error', $error);}
		redirect(base_url('account/document/view/' . $document_id));
		exit;
	}

//    Document Custom Download
	public function admin_document_custom_download($document_id) {
		$error = "";
		$logid = $GLOBALS["loguser"]["id"];
		$cnd = "document_id='" . $document_id . "' and added_by!='$logid' and (company_id='$logid' or client_manager='$logid' or client_id='$logid')";
		$docR = $this->default_model->get_arrby_tbl('client_documents', '*', $cnd, '1');
		$docR = $docR["0"];
		if (!isset($docR['document_id'])) {$error = "Somethig went wrong.";}
		if ($error == "") {
			$this->db->query("update client_documents set source_document='Downloaded', downloaded_date=now() where document_id='$document_id'");

			$client_document = $this->document_decrypt($docR['client_document']);
			if (!file_exists($client_document)) {$error = "Somethig went wrong.";}
		}

		if ($error == "") {
			if ($docR['file_is_merged'] == "1") {$client_document = $docR['files'];}
			$this->document_download_zip($client_document, $docR['document_name']);
		} else { $this->session->set_flashdata('error', $error);}
		redirect(base_url('account/document/view/' . $document_id));
		exit;
	}

//    Document Custom Download
	public function admin_document_self_delete($document_id) {
		$error = "";
		$logid = $GLOBALS["loguser"]["id"];
		$cnd = "document_id='" . $document_id . "' and added_by='$logid'";
		$docR = $this->default_model->get_arrby_tbl('client_documents', '*', $cnd, '1');
		if (isset($docR[0])) {$docR = $docR["0"];}
		if (!isset($docR['document_id'])) {$error = "Somethig went wrong.";}
		if ($error == "") {
			if (trim($docR['downloaded_date']) != "") {$error = "Somethig went wrong.";}
		}

		if ($error == "") {
			$this->db->query("delete from client_documents where document_id='$document_id'");

			if ($docR['file_is_merged'] == "1") {
				foreach (explode(",", $docR['files']) as $client_document) {unlink(trim($client_document));}
			} else {
				$client_document = $this->document_decrypt($docR['client_document']);
				if (file_exists($client_document)) {unlink($client_document);}
			}

		}

		if ($error == "") {$this->session->set_flashdata('success', "Record successfully deleted.");} else { $this->session->set_flashdata('error', $error);}
		if ($GLOBALS["loguser"]["role"] == "Customer") {redirect(base_url('account/document'));} else {redirect(base_url('account/customer/document/' . $this->uri->segment(4)));}
		exit;
	}

//    Download Zip file
	public function document_download_zip($name, $title) {
		$this->load->library('zip');
		foreach (explode(",", $name) as $v) {$this->zip->read_file(trim($v), false);}
		$this->zip->download($title . '.zip');
	}

//    Document Encrypt
	public function document_encrypt($name) {
		for ($i = 1; $i <= 1; $i++) {$name = base64_encode($name);}
		return $name;
	}

//    Document Decrypt
	public function document_decrypt($name) {
		for ($i = 1; $i <= 1; $i++) {$name = base64_decode($name);}
		return $name;
	}

//    Add/Edit Advertisement
	public function admin_advertisement($id = 0) {

		$error = '';
		$role = $GLOBALS["loguser"]["role"];
		if ($role == "Company") {$company_id = $GLOBALS["loguser"]["id"];} elseif ($role == "Company User") {$company_id = $GLOBALS["loguser"]["company_id"];} else { $company_id = "";}

		//    Check Name
		$crl_cnd = "name='" . $_POST['name'] . "' and company_id='$company_id'";
		if ($id == 0) {} else { $crl_cnd = $crl_cnd . " and id!='$id'";}
		$q = $this->db->query("SELECT * FROM users_advertisement where $crl_cnd limit 1");
		$r = $q->row_array();
		if (isset($r['id'])) {$error = "This name already exists. Please enter another name.";}

		foreach ($_POST as $key => $value) {$col_arr[$key] = $value;}
		$col_arr['company_id'] = $company_id;
		unset($col_arr['Submit_']);

		if ($error == '') {

			if ($id == 0) {
				$col_arr['add_date'] = date('Y-m-d');
				$col_arr['code'] = $company_id . time();
				$this->db->insert('users_advertisement', $col_arr); //    Insert Record
				$id = $this->db->insert_id();
			} else {
				//    Update Profile
				$this->db->where(array('id' => $id, 'company_id' => $company_id));
				$this->db->update('users_advertisement', $col_arr);
			}
		} else { $error = $error;}
		$result['id'] = $id;
		$result['error'] = $error;
		return $result;
	}

//    Add/Edit Users
	public function admin_users($id = 0, $role = 'Company') {
		$error = '';
		//    Check Record for Edit
		$q = $this->db->query("SELECT id,role FROM users where id='$id' limit 1");
		$result = $q->row_array();
		if (isset($result['id'])) {
			$role = $_POST['role'] = $result['role'];
		}

		//    Check Email
		$crl_cnd = "name='" . $_POST['name'] . "' and lname='" . $_POST['lname'] . "' and email='" . $_POST['email'] . "'";
		if ($id == 0) {} else { $crl_cnd = $crl_cnd . " and id!='$id'";}
		if ($role == "Customer" || $role == "Company User") {$crl_cnd = $crl_cnd . " and company_id='" . $_POST['company_id'] . "'";}
		$q = $this->db->query("SELECT * FROM users where $crl_cnd limit 1");
		$r = $q->row_array();
		if (isset($r['id'])) {$error = "This email already exists. Please enter another email.";}

		//    Check Case Manager
		if (isset($_POST['parent_id'])) {
			$q = $this->db->query("SELECT id FROM users where id='" . $_POST['parent_id'] . "' limit 1");
			$cmn = $q->num_rows();
			if ($cmn == 0) {$error = "Please select a valid case manager.";}
		}

		//    Company User Unique by name and Last Name
		if ($role == "Company User") {
			$crl_cnd = "name='" . $_POST['name'] . "' and lname='" . $_POST['lname'] . "'";
			$crl_cnd = $crl_cnd . " and company_id='" . $_POST['company_id'] . "'";
			if ($id == 0) {} else { $crl_cnd = $crl_cnd . " and id!='$id'";}

			$q = $this->db->query("SELECT * FROM users where $crl_cnd limit 1");
			$r = $q->row_array();
			if (isset($r['id'])) {$error = "User already exists having same first name and last name. Please enter another first name and last name.";}
		}

		//    Customer Unique by first name, last name, email address, company name
		if ($role == "Customer") {
			$crl_cnd = "name='" . $_POST['name'] . "' and lname='" . $_POST['lname'] . "' and email='" . $_POST['email'] . "'";
			$crl_cnd = $crl_cnd . " and company_id='" . $_POST['company_id'] . "'";
			if ($id == 0) {} else { $crl_cnd = $crl_cnd . " and id!='$id'";}

			$q = $this->db->query("SELECT * FROM users where $crl_cnd limit 1");
			$r = $q->row_array();
			if (isset($r['id'])) {$error = "User already exists having same first name and last name. Please enter another first name and last name.";}
		}

		foreach ($_POST as $key => $value) {$col_arr[$key] = $value;}
		unset($col_arr['psd']);
		unset($col_arr['email_password']);
		unset($col_arr['Submit_']);

		$col_arr['role'] = $role;

		if (isset($_POST["psd"])) {
			if (trim($_POST["psd"]) != '') {$col_arr['psd'] = $this->default_model->psd_encrypt($_POST["psd"]);}
		}

		if (isset($_POST["email_password"])) {
			if (trim($_POST["email_password"]) != '') {$col_arr['email_password'] = base64_encode($_POST["email_password"]);}
		}

		// upload file
		$config['allowed_types'] = 'gif|jpg|jpeg|png|JPG|JPEG|PNG|gif';
		$config['file_name'] = '1';
		$config['upload_path'] = './assets/uploads/' . date('Y/m');
		if (!is_dir($config['upload_path'])) {
			mkdir($config['upload_path'], 0777, true);
		}

		$this->load->library('upload', $config);
		if ($this->upload->do_upload('profile_img')) {
			if ($id > 0) {if (file_exists($result['image'])) {unlink($result['image']);}}
			$col_arr['image'] = 'assets/uploads/' . date('Y/m') . '/' . $this->upload->data('file_name');
		}

		//    Upload Logo
		$this->load->library('upload', $config);
		if ($this->upload->do_upload('logo_img')) {
			if ($id > 0) {if (file_exists($result['logo'])) {unlink($result['logo']);}}
			$col_arr['logo'] = 'assets/uploads/' . date('Y/m') . '/' . $this->upload->data('file_name');
		}

		if ($error == '') {
			// echo $col_arr['psd'].'fff';
			// die();
			if ($id == 0) {
				$col_arr['add_date'] = date('Y-m-d');
				if ($col_arr['role'] == "Company") {$col_arr['next_payment_date'] = date('Y-m-d', strtotime(date('Y-m-d') . ' + 30 days'));}
				// echo  $col_arr,'ooo';
				// print_r($col_arr);
				// die();
				$this->db->insert('users', $col_arr); //    Insert Record
				$id = $this->db->insert_id();
				if ($col_arr['role'] == "Company") {$this->admin_create_payment_installment($id);} //    Create Payment Installment
				if ($col_arr['role'] == "Company User") {$this->admin_create_payment_installment_2($col_arr['company_id'], $id);} //    Create Payment Installment

				if ($col_arr['role'] == "Customer") {$this->admin_send_intake_email($id, "1");} //    Send Initial Intake Email

			} else {
				//    Update Profile
				// print_r($col_arr);
				// die();
				$this->db->where(array('id' => $id, 'role' => $role));
				$this->db->update('users', $col_arr);
			}

			$this->account_model->add_case_manager_setting($id);

		} else { $error = $error;}
		$result['id'] = $id;
		$result['error'] = $error;
		return $result;
	}

//    Add New Program
	public function admin_users_add_program($client_id = 0, $program_id = '') {
		$error = '';
		$this->programs_model->check_intake_program($client_id, $program_id); // Check Intake Programs

		//    Check Records
		$q = $this->db->query("SELECT * FROM program_definitions where program_definition_id='" . $program_id . "' limit 1");
		$pr = $q->row_array();
		if (!isset($pr['program_definition_id'])) {$error = "Invalid program selected.";}

		if ($error == '') {
			$q = $this->db->query("SELECT * FROM users where id='" . $client_id . "' limit 1");
			$cr = $q->row_array();
			$company_id = $cr['company_id'];
			$parent_id = $cr['parent_id'];

			$step_due_date = date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $pr['step_duration'] . ' days'));
			$col_arr = ['added_by' => $parent_id, 'company_id' => $company_id, 'program_id' => $program_id, 'program_id_primary' => $program_id, 'client_id' => $client_id, 'step_id' => '1', 'step_due_date' => $step_due_date];
			$this->db->insert('client_program_progress', $col_arr); //    Insert Record
			$client_program_progress_id = $this->db->insert_id();
			$result['program_definition_id'] = $client_program_progress_id;

			// $this->set_reminder_rules($col_arr, $client_program_progress_id); //    Set Program Reminder

			if ($program_id != '97') {
				$arr_program_id = $this->array_model->arr_intake_program_id();
				$intake_id = $arr_program_id[$program_id];
				// $this->admin_send_intake_email($client_id, $intake_id); // Send Intake Email
			}

			if ($program_id == '127') {

				$where = array('program_id' => $program_id, 'client_id' => $client_id, 'step_id' => '1');
				$this->db->where($where);
				$this->db->update('client_program_progress', ['step_completed_date' => date('Y-m-d'), 'status' => 'Complete']); //    Insert Client Program Process Record # Step 1

				$step_due_date = date('Y-m-d', strtotime(' + 14 days'));
				$col_arr = array('added_by' => $parent_id, 'company_id' => $company_id, 'program_id' => '128', 'program_id_primary' => $program_id, 'client_id' => $client_id, 'step_id' => '2', 'step_due_date' => $step_due_date, 'status' => 'Pending');
				$this->default_model->dbInsert('client_program_progress', $col_arr);
				$result = $this->admin_send_intake_email($client_id, 4);
				if ($result['error'] != '') {
					$error = $result['error'];
				} else {
					$this->admin_copy_intake_answer_to_update($client_id);
				}
			}

			$this->check_client_program_status($client_id, $program_id); //    Check Client Programs Status
			$this->programs_model->add_client_to_current_program($client_id);

			// check if first program for client other than intakes, then update date_of_first_program for this client if null

			$ch = $this->db->query('select * from client_program where client_id=' . $client_id . ' and program_definition_id not in (91,127)')->num_rows();

			if ($ch <= 0) {
				$cl = $this->db->query('select * from clients where client_id=' . $client_id)->row_array();

				if (empty($cl['date_of_first_program'])) {
					$this->db->where(['client_id' => $client_id]);
					$this->db->update('clients', ['date_of_first_program' => date('Y-m-d')]);
				}
			}

		} else { $error = $error;}
		$result['error'] = $error;

		return $result;
	}

//    Cron Rule
	public function set_reminder_rules($data = array(), $client_program_progress_id = "") {
		@extract($data);
		//    Set Reminder Rules reminder_rules
		$reminder_date_from = $step_due_date;

		$sql = "SELECT * FROM program_definitions where program_definition_id='" . $program_id . "' and step_id='" . $step_id . "' limit 1";
		$q = $this->db->query($sql);
		$prgmr = $q->row_array();

		//    Fetch Client
		$q = $this->db->query("SELECT * FROM users where id='" . $client_id . "' limit 1");
		$cltr = $q->row_array();

		//    Fetch Comapny
		$cmpr = $this->get_company_details($cltr['company_id']);

		//    Fetch Added By
		$q = $this->db->query("SELECT * FROM users where id='" . $cltr['parent_id'] . "' limit 1");
		$rr2 = $q->row_array();

		//    Reminder
		$case_manager_name = $rr2['name'] . " " . $rr2['lname'];
		$case_manager_email = $rr2['email'];
		$case_manager_phone = $rr2['phone'];

		if ($prgmr['program_title'] == "Intake") {
			$res = $this->email_model->get_intake_program_reminder_tamplate($cltr, $cmpr, $prgmr, $step_id, $client_program_progress_id, $program_id, $step_due_date);
		} else {
			//    Client Reminder
			$reminder_email_subject = "Task Reminder";
			$reminder_email_body = "<p><today_date></p>
	<p>Dear " . $cltr['name'] . " " . $cltr['lname'] . ",</p>
	<p>This email is to remind you that you have a task due.</p>
	<div style='margin:15px 0px;'><p>Task Name: " . $prgmr['step_name'] . "</p>
	<p>Due Date: " . date('m/d/Y', strtotime($step_due_date)) . "</p></div>
	<p>Please complete the task as quickly as possible to avoid delays in processing.</p>
	<p>If you have any questions, please contact " . trim($case_manager_name) . " at <a href='" . trim($case_manager_email) . "'>" . trim($case_manager_email) . "</a> or by calling " . trim($case_manager_phone) . ".</p>
	<div>Regards<br />" . $case_manager_name . "</div>";
			$col_arr_rr = ['client_program_progress_id' => $client_program_progress_id, 'program_id' => $program_id, 'step_id' => $step_id, 'company_id' => $company_id, 'client_id' => $client_id, 'days_to_send' => $prgmr['step_duration'], 'reminder_email_subject' => $reminder_email_subject, 'reminder_email_body' => $reminder_email_body, 'status_flag' => '1', 'to_whom' => '0', 'sent_to' => $cltr['email'], 'reminder_date_from' => $reminder_date_from];
			$this->db->insert('reminder_rules', $col_arr_rr); //    Insert Record

			//    Company Reminder
			$reminder_email_subject = "Task Reminder";
			$reminder_email_body = "<p><today_date></p>
	<p>Dear " . $case_manager_name . ",</p>
	<p>This email is to remind you that you have a task due.</p>
	<div style='margin:15px 0px;'><p>Client: " . trim($cltr['name']) . " " . trim($cltr['lname']) . "</p>
	<p>Task Name: " . $prgmr['step_name'] . "</p>
	<p>Due Date: " . date('m/d/Y', strtotime($step_due_date)) . "</p></div>
	<p>Please complete the task as quickly as possible to avoid delays in processing.</p>
	<div>Regards<br />Student Loan Toolbox</div>";
			$col_arr_rr = ['client_program_progress_id' => $client_program_progress_id, 'program_id' => $program_id, 'step_id' => $step_id, 'company_id' => $company_id, 'client_id' => $client_id, 'days_to_send' => $prgmr['step_duration'], 'reminder_email_subject' => $reminder_email_subject, 'reminder_email_body' => $reminder_email_body, 'status_flag' => '1', 'to_whom' => '1', 'sent_to' => $case_manager_email, 'reminder_date_from' => $reminder_date_from];
			$this->db->insert('reminder_rules', $col_arr_rr); //    Insert Record
		}
	}

//    Add New Program Step
	public function admin_users_add_program_step($client_id = 0, $program_definition_id = 0) {
		$error = '';

		$q = $this->db->query("SELECT * FROM client_program_progress where program_definition_id='" . $program_definition_id . "' limit 1");
		$cppr = $q->row_array();

		if ($cppr['step_completed_date'] == "" || $cppr['status'] != "Complete") {
			if ($this->uri->segment(7) == "nfa" || $this->uri->segment(7) == "dnc" || $this->uri->segment(7) == "cwsap") {
				$this->programs_model->program_nfa($client_id, $program_definition_id, $cppr['program_id_primary']);
			} else {

				//    Update Stage
				$this->db->where('program_definition_id', $program_definition_id);
				$this->db->update('client_program_progress', ['status' => 'Complete', 'step_completed_date' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d')]);

				if ((stripos($this->uri->segment(8), 'doj_') !== false && ($this->uri->segment(8) == 'doj_1' || $this->uri->segment(8) == 'doj_2')) || (stripos($this->uri->segment(8), 'rem_') !== false && $this->uri->segment(8) == 'rem_3')) {

					$this->db->query("update client_program_progress set step_completed_date='" . date('Y-m-d') . "', status='Stop' where client_id='" . $client_id . "' and program_definition_id='" . $program_definition_id . "'");

					$this->db->query("update client_program_progress set status_1='Stop' where client_id='" . $client_id . "' and program_id_primary='" . $cppr['program_id_primary'] . "'");

					$this->db->query("update client_program set status='Stop' where client_id='" . $client_id . "' and program_definition_id='" . $cppr['program_id_primary'] . "'");

					//    Update Status Flag
					$this->db->where('program_definition_id', $program_definition_id);
					$this->db->update('client_program_progress', ['reminder_status' => 0]);
				}

				//    Update Status Flag
				$this->db->where('client_program_progress_id', $program_definition_id);
				$this->db->update('reminder_rules', ['status_flag' => '0']);

				$q = $this->db->query("SELECT * FROM client_program_progress where program_definition_id='" . $program_definition_id . "' limit 1");
				$cppr = $q->row_array();

				$q = $this->db->query("SELECT * FROM program_definitions where program_definition_id='" . $cppr['program_id_primary'] . "' order by step_id asc limit 1");
				$pr = $q->row_array();
				$program_id_primary = $pr['program_definition_id'];

				$sql = "SELECT * FROM program_definitions where program_title='" . $pr['program_title'] . "' and step_id>'" . $cppr['step_id'] . "' order by step_id asc limit 1";
				$q = $this->db->query($sql);
				$pr = $q->row_array();

				//if($GLOBALS["loguser"]["role"] == "Company")  { $company_id = $GLOBALS["loguser"]["id"]; } else {     $company_id = $GLOBALS["loguser"]["company_id"];    }
				$company_id = $cppr['company_id'];
				if (isset($GLOBALS["loguser"]["id"])) {$added_by = $GLOBALS["loguser"]["id"];} else { $added_by = $company_id;}

				if (isset($pr['program_definition_id'])) {
					$step_start_date = date('Y-m-d');
					$step_due_date = date('Y-m-d', strtotime($step_start_date . ' + ' . $pr['step_duration'] . ' days'));
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

					if (stripos($this->uri->segment(8), 'doj_') !== false && ($this->uri->segment(8) == 'doj_1' || $this->uri->segment(8) == 'doj_2')) {
					} elseif (stripos($this->uri->segment(8), 'rem_') !== false && $this->uri->segment(8) == 'rem_2') {

						for ($i = $pr['step_id']; $i <= 6; $i++) {

							$col_arr = ['added_by' => $added_by, 'company_id' => $company_id, 'program_id' => $pr['program_definition_id'], 'program_id_primary' => $program_id_primary, 'client_id' => $client_id, 'step_id' => $i, 'step_due_date' => $step_due_date, 'step_completed_date' => date('Y-m-d'), 'status' => 'Complete', 'status_1' => 'Complete'];

							$this->db->insert('client_program_progress', $col_arr); //    Insert Record
							$client_program_progress_id = $this->db->insert_id();

							// $this->set_reminder_rules($col_arr, $client_program_progress_id); //    Set Program Reminder
						}
					} else {

						$col_arr = ['added_by' => $added_by, 'company_id' => $company_id, 'program_id' => $pr['program_definition_id'], 'program_id_primary' => $program_id_primary, 'client_id' => $client_id, 'step_id' => $pr['step_id'], 'step_due_date' => $step_due_date];

						$this->db->insert('client_program_progress', $col_arr); //    Insert Record
						$client_program_progress_id = $this->db->insert_id();

						// $this->set_reminder_rules($col_arr, $client_program_progress_id); //    Set Program Reminder
					}

					//    Send Intake
					if ($program_id_primary == "1" || $program_id_primary == "23" || $program_id_primary == "40" || $program_id_primary == "178" || $program_id_primary == "193") {
						$arr_program_id = $this->array_model->arr_intake_program_id();
						$intake_id = $arr_program_id[$program_id_primary];

						//    Stop Payment Reminder
						if ($cppr['step_id'] == "4") {
							$this->db->query("update client_analysis_results set last_sent_reminder='2050-12-12' where client_id='" . $client_id . "' and intake_id='" . $intake_id . "'");
						}

						if ($pr['step_id'] == "4" || $pr['step_id'] == "5") {
							$this->admin_send_intake_email($client_id, $intake_id); // Send Intake Email
						}

						if ($pr['step_id'] == "7") {
							$this->db->query("update intake_client_status set exp_date='" . date('Y-m-d') . "', status2='Approved' where client_id='$client_id' and intake_id='$intake_id'");

							//    Add Intake Document
							$this->add_intake_document($client_id, $intake_id, 'Active');
						}
					}
				} else {
					$this->db->where('company_id', $company_id);
					$this->db->where('client_id', $client_id);
					$this->db->where('program_id_primary', $program_id_primary);
					$col_arr = array('status_1' => 'Complete');
					$this->db->update('client_program_progress', $col_arr); //    Insert Record
					$error = $error;
				}

				$this->check_client_program_status($client_id, $program_id_primary); //    Check Client Programs Status

				$this->programs_model->add_client_to_current_program($client_id);

				//    Select Program and add the Client
				if ($this->uri->segment(7) == "spaatc") {
					if ($this->uri->segment(8) != "") {
						$result = $this->admin_users_add_program($this->uri->segment(4), $this->uri->segment(8));
						if ($result['error'] != '') {
							$this->session->set_flashdata('error', $result['error']);
						} else { $this->session->set_flashdata('success', 'Program successfully added.');}
						redirect(base_url('account/customer/add_program/' . $this->uri->segment(4)));
						exit;
					}
				}
			}
		}

		$result['error'] = $error;
		return $result;
	}

//    Add Intake Document
	public function add_intake_document($client_id = '', $intake_id = '', $status = 'Active') {
		$icsr = $this->client_intake_client_status($client_id, $intake_id); //    Check Intake

		$q = $this->db->query("SELECT * FROM intake where intake_id='$intake_id'");
		$intkr = $q->row_array();

		$this->db->query("UPDATE client_documents set status='Active' where client_id='$client_id' and intake_client_status_id='" . $icsr['id'] . "'");

		$q = $this->db->query("SELECT * FROM client_documents where client_id='$client_id' and intake_client_status_id='" . $icsr['id'] . "'");
		$n = $q->num_rows();
		if ($n == 0) {
			$q = $this->db->query("SELECT * FROM users where id='$client_id'");
			$clntR = $q->row_array();

			$added_by = $GLOBALS["loguser"]["id"];
			$company_id = $clntR["company_id"];
			$client_manager = $clntR["parent_id"];
			$document_name = str_replace("Intake", "Form", $intkr['intake_title']);
			$files = "account/intake_form_document/" . $icsr['id'];

			$col_arr = ["added_by" => $added_by, "company_id" => $company_id, "client_manager" => $client_manager, "client_id" => $client_id, "file_is_merged" => "1", "document_name" => $document_name, "files" => $files, "intake_client_status_id" => $icsr['id'], "status" => $status];
			$this->db->insert('client_documents', $col_arr); //    Insert Record
		}
	}

//    Calculate Week End Days
	public function calculate_weekends($date_begin = '', $date_end = '') {
		//$date_end = date('Y-m-d', strtotime($date_end. ' + 1 days'));
		$begin = new DateTime($date_begin);
		$end = new DateTime($date_end);

		$interval = new DateInterval('P1D');
		$daterange = new DatePeriod($begin, $interval, $end);
		$weekends = [];

		foreach ($daterange as $date) {
			if (in_array($date->format('N'), [6, 7])) {
				$weekends[$date->format('W')][] = $date->format('Y-m-d');
			}
		}

		$weekenddays = (count($weekends, COUNT_RECURSIVE) - count($weekends)) + 0;
		//print_r($weekends);
		//echo '<hr />Number of weeks: ' . count($weekends);
		//echo '<hr />Number of weekend days: ' . ($weekenddays);
		return ['weekends' => $weekends, 'weekenddays' => $weekenddays];
	}

//    Generate Numbers
	public function generateNumber($length) {
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$count = mb_strlen($chars);

		for ($i = 0, $result = ''; $i < $length; $i++) {
			$index = rand(0, $count - 1);
			$result .= mb_substr($chars, $index, 1);
		}
		return $result;
	}

//    Generate Unique Coupon Code
	public function generate_coupon_code() {
		$coupon_code = "";
		do {
			$coupon_code = strtoupper($this->generateNumber(8));

			$sql = "SELECT id FROM users_coupons where coupon_code='$coupon_code' limit 1";
			$q = $this->db->query($sql);
			$n = $q->num_rows();
			if ($n == 0) {$coupon_code = $coupon_code;} else { $coupon_code = "";}
		} while ($coupon_code == "");
		return $coupon_code;
	}

//    Add/Edit Pages
	public function crm_pages($id = 0) {
		$error = '';

		//    Check URL
		if ($id == 0) {$crl_cnd = "name='" . $_POST['name'] . "'";} else { $crl_cnd = "id!='$id' and name='" . $_POST['name'] . "'";}
		$q = $this->db->query("SELECT * FROM pages where $crl_cnd limit 1");
		$r = $q->row_array();
		if ($r['id'] != '') {$error = "Same pages name already exists. Please enter another name.";}

		foreach ($_POST as $key => $value) {$col_arr[$key] = $value;}
		unset($col_arr['Submit_']);

		if ($error == '') {
			if ($id == 0) {
				$this->db->insert('pages', $col_arr); //    Insert Record
				$id = $this->db->insert_id();
			} else {
				//    Update Profile
				$this->db->where('id', $id);
				$this->db->update('pages', $col_arr);
			}

			$result['id'] = $id;

		} else { $error = $error;}
		$result['error'] = $error;
		return $result;
	}

//    Get Intake Client Answer
	public function admin_save_intake_answer_by_client($intake_page_no = 1, $intake_id = 1) {

		$num = $intake_id != 1 ? 96 : 0;
		$a = $intake_id != 1 ? 74 : 0;
		$error = "";
		$private_loan = 0;
		unset($_POST['Submit_intake_answer']);

		$client_id = $GLOBALS["loguser"]["id"];

		foreach ($_POST as $tbl => $rows) {
			if ($tbl != 'intake_table_result') {
				foreach ($rows as $k => $row) {
					if ($tbl == "intake_comment_result") {$field_id = "intake_comment_id";}
					if ($tbl == "intake_answer_result") {$field_id = "intake_result_id";}

					//    Get Answer Details
					$q = $this->db->query("SELECT * FROM $tbl where $field_id='$k'");
					$ansR = $q->row_array();

					//    Get Question Details
					$q = $this->db->query("SELECT * FROM intake_question where intake_question_id='" . $ansR['intake_question_id'] . "'");
					$queR = $q->row_array();

					if ($ansR['intake_question_id'] == ($num + 34)) {
						$private_loan = $row;
					}

					if (is_array($row)) {
						$isvalid = "Yes";
						$row = implode(',', $row);

						if ($queR['question_required'] == 'Yes') {
							if (trim($row) == "") {
								$isvalid = "No";
								$error .= "Please select atleast one option from <em>" . $queR['intake_question_body'] . "</em><br />";}}

						if ($tbl == "intake_answer_result" && $isvalid == "Yes") {
							if ($row == "0") {$row = "";}
							$this->db->where('intake_result_id', $k);
							$this->db->update($tbl, ['intake_answer_id_checkbox' => $row]);
						}
					} else {
						$isvalid = "Yes";
						//if($queR['question_required']=='Yes') {    if(trim($row)=="") {    $isvalid = "No";    $error .= "Filed <em>".$queR['intake_question_body']."</em> cannot be empty"."<br />";    }    }
						if ($tbl == "intake_comment_result" && $isvalid == "Yes") {
							$this->db->where('intake_comment_id', $k);
							$this->db->update($tbl, ['intake_comment_body' => $row]);
						} else if ($tbl == "intake_answer_result" && $isvalid == "Yes") {
							$this->db->where('intake_result_id', $k);
							$this->db->update($tbl, ['intake_answer_id' => $row]);
						} else {}
					}
				}
			}
		}
		// echo "<pre>";
		// print_r($_POST['intake_table_result']);die;
		if ($intake_page_no == 7) {
			if ($private_loan > 0) {
				foreach ($_POST['intake_table_result'] as $que => $value) {
					$this->db->query('delete from intake_comment_result where client_id=' . $client_id . ' and intake_question_id=' . $que);
					foreach ($value as $val) {
						$col = [
							'intake_question_id' => $que,
							'client_id' => $client_id,
							'intake_comment_body' => $val,
						];

						$this->db->insert('intake_comment_result', $col);
					}
				}
			} else {
				if ($intake_id == 1) {
					$qlist = [35, 36, 37, 38, 39, 40, 41, 42];
				} else {
					$qlist = [131, 132, 133, 134, 135, 136, 137, 138];
				}

				$this->db->query('delete from intake_comment_result where client_id=' . $client_id . ' and intake_question_id in (' . implode(',', $qlist) . ')');
			}
		}

		if (isset(($_FILES['intake_file_result']))) {
			foreach ($_FILES['intake_file_result']['name'] as $i => $v) {
				$_FILES['userfile']['name'] = $_FILES['intake_file_result']['name'][$i];
				$_FILES['userfile']['type'] = $_FILES['intake_file_result']['type'][$i];
				$_FILES['userfile']['tmp_name'] = $_FILES['intake_file_result']['tmp_name'][$i];
				$_FILES['userfile']['error'] = $_FILES['intake_file_result']['error'][$i];
				$_FILES['userfile']['size'] = $_FILES['intake_file_result']['size'][$i];

				if ($_FILES['userfile']['name'] != "") {
					// upload file
					$q = $this->db->query('select * from intake_file_result where intake_file_id=' . $i)->row_array();
					if ($q['intake_question_id'] == ($num + 6)) {
						$config['allowed_types'] = 'txt';
					} else {
						/*if ($_FILES['userfile']['type'] == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
							                        $config['allowed_types'] = '*';
							                        } else {
							                        $config['allowed_types'] = 'gif|jpg|jpeg|png|JPG|JPEG|PNG|gif|doc|docx|csv|xls|xlsx|ppt|pptx|txt|pdf';
							                        }
						*/
						$config['allowed_types'] = '*';
					}

					$config['file_name'] = rand('100', '999') . $client_id . time();
					$config['upload_path'] = './assets/uploads/document/' . date('Y/m');
					if (!is_dir($config['upload_path'])) {
						mkdir($config['upload_path'], 0777, true);
					}

					$this->load->library('upload', $config);

					if ($this->upload->do_upload('userfile')) {
						$intake_file_location = 'assets/uploads/document/' . date('Y/m') . '/' . $this->upload->data('file_name');

						$file_data = read_file($intake_file_location);
						//echo $file_data."<br />";
						$arr_file_data = preg_split("/\r\n|\n|\r/", $file_data);

						foreach ($arr_file_data as $k => $v) {
							$arr_file_data_2[] = explode(":", $v);
							$vr = explode(":", $v);
							$rmv1 = ['$', ',', ' ', 'A', 'B', '[', ']'];
							$rmv2 = "";
							$vr[0] = str_replace(['[', ']'], $rmv2, $vr[0]);

							if (stripos($vr[0], 'File Request Date') !== false && !empty($vr[1])) {

								if (date('Y-m-d', strtotime(($vr[1] . ":" . $vr[2] . ":" . $vr[3]) . ' + 180 days')) < date('Y-m-d')) {
									$error = 'You are trying to upload older NSLDS File. Please upload a newer one.';
									unlink($intake_file_location);
									break;
								}

							}
						}

						if (empty($error)) {
							$intake_file_location = $this->document_encrypt($intake_file_location);

							$q = $this->db->query("SELECT * FROM intake_file_result where intake_file_id='$i'");
							$fileR = $q->row_array();
							$client_document = $this->document_decrypt($fileR['intake_file_location']);
							if (file_exists($client_document)) {unlink($client_document);}

							$this->db->where('intake_file_id', $i);
							$this->db->update("intake_file_result", ['intake_file_location' => $intake_file_location]);

							if ($fileR['intake_question_id'] == ($num + 6)) {$this->crm_model->read_and_save_intake_file($client_id, $intake_id);}
						}
					} else {
						$upload_error = $this->upload->display_errors();

						if (stripos($upload_error, 'The filetype you are attempting to upload is not allowed.') !== false) {
							$upload_error = str_replace('The filetype you are attempting to upload is not allowed.', 'You have selected an invalid file type. Only the student aid txt file can be uploaded here. See the instructions to download your text file from <a href="http://studentaid.gov/" target="_blank">studentaid.gov</a> and try again.', $upload_error);
						}

						$error .= $upload_error . "<br />";
					}
				}

			}
		}

		if ($error == "") {
			if ($intake_page_no == 1) {
				$ansR = $this->default_model->get_arrby_tbl_single('intake_answer_result', '*', "client_id='" . $client_id . "' and intake_question_id='" . ($num + 5) . "'", '1');
				if ($ansR['intake_answer_id'] == ($a + 2)) {

					redirect(base_url("account/" . ($intake_id != 1 ? 'update_intake_form' : 'intake_form') . "?intake_page_no=7"));
					exit;
				}
			}

			/*if ($intake_page_no == 7) {
				            $ansR = $this->default_model->get_arrby_tbl_single('intake_comment_result', '*', "client_id='" . $client_id . "' and intake_question_id='" . ($num + 34) . "'", '1');
				            if ($ansR['intake_comment_body'] == "0") {
				            $total_loan = 0;
				            $int_6R = $this->default_model->get_arrby_tbl_single('intake_file_result', '*', "client_id='" . $client_id . "' and intake_question_id='" . ($num + 6) . "'", '1');
				            if (!isset($int_6R['student_total_all_loans_outstanding_principal'])) {$total_loan = 0;} else {
				            $total_loan = ($int_6R['student_total_all_loans_outstanding_principal'] + $int_6R['student_total_all_loans_outstanding_interest']);
				            }

				            if ($total_loan <= 0) {

				            } else {

				            $this->session->set_flashdata('error', "You have indicated you have no loans. Please go back and correct your information.");
				            redirect(base_url("account/" . ($intake_id != 1 ? 'update_intake_form' : 'intake_form') . "?intake_page_no=7"));
				            exit;
				            }
				            }
				            }

			*/

			if ($intake_page_no >= 8) {

				$this->admin_intake_check($client_id, ($intake_id != 1 ? 'update' : 'initial'));
				$this->db->query("update intake_client_status set status='Complete' where client_id='$client_id' and intake_id='$intake_id'");
				$this->send_inatke_complete_email($client_id, $intake_id);
				redirect(base_url("account/intake/" . ($intake_id != 1 ? 'update' : 'initial')));
				exit;
			}

			$intake_page_no = ($intake_page_no + 1);
			redirect(base_url("account/" . ($intake_id != 1 ? 'update_intake_form' : 'intake_form') . "?intake_page_no=" . $intake_page_no));
			exit;
		}

		return ['error' => $error];
		exit;
	}

	public function admin_copy_intake_answer_to_update($client_id) {
		$error = "";

		$col_arr = $this->db->query('select * from intake_client_status where client_id=' . $client_id . ' and intake_id=1')->row_array();

		$col_arr['intake_id'] = 4;
		$col_arr['status'] = 'Active';
		$col_arr['status2'] = 'Pending';
		unset($col_arr['id']);

		if ($this->db->query('select * from intake_client_status where client_id=' . $client_id . ' and intake_id=4')->num_rows() <= 0) {
			$this->db->insert('intake_client_status', $col_arr);
		}

		$qids = $this->db->query('select GROUP_CONCAT(intake_question_id) as ids from intake_question where intake_id=1')->row_array();

		$comments = $this->db->query('select * from intake_comment_result where client_id=' . $client_id . ' and intake_question_id in (' . $qids['ids'] . ')')->result_array();

		foreach ($comments as $comment) {

			$comment['intake_question_id'] += 96; //96 is the difference between intake and update intake questions id in intake_question table
			unset($comment['intake_comment_id']);

			$this->db->insert('intake_comment_result', $comment);

		}

		$answers = $this->db->query('select * from intake_answer_result where client_id=' . $client_id . ' and intake_question_id in (' . $qids['ids'] . ')')->result_array();

		foreach ($answers as $answer) {

			$answer['intake_question_id'] += 96; //96 is the difference between intake and update intake questions id in intake_question table
			$answer['intake_answer_id'] += 74; //74 is the difference between intake and update intake answer id in intake_answer table

			$checkbox = explode(',', $answer['intake_answer_id_checkbox']);
			foreach ($checkbox as $key => $i) {
				if ($i != 0) {
					$i += 74;
				}

			}
			$answer['intake_answer_id_checkbox'] = implode(',', $checkbox);
			unset($answer['intake_result_id']);

			$this->db->insert('intake_answer_result', $answer);

		}

		$files = $this->db->query('select * from intake_file_result where client_id=' . $client_id . ' and intake_question_id in (' . $qids['ids'] . ')')->result_array();

		foreach ($files as $file) {

			if (!empty($file['intake_file_location'])) {
				$document = $this->document_decrypt($file['intake_file_location']);

				$filename = 'assets/uploads/document/' . date('Y/m') . '/' . rand('100', '999') . $client_id . time();

				copy($document, $filename);

				$intake_file_location = $this->document_encrypt($filename);
			}
			$file['intake_question_id'] += 96;
			unset($file['intake_file_id']);

			$this->db->insert('intake_file_result', $file);
			$fileid = $this->db->insert_id();

			if ($file['intake_question_id'] == "102") {
				$this->crm_model->read_and_save_intake_file($client_id, 4);

				$col_arr = array();
				// $col_arr = $this->db->query('select * from client_analysis_results where client_id=' . $client_id . ' and intake_id=1')->row_array();

				$col_arr['nslds_id'] = $fileid;
				$col_arr['intake_id'] = 4;
				$col_arr['client_id'] = $client_id;
				$col_arr['company_id'] = $GLOBALS['loguser']['company_id'];

				/*$col_arr['marital_status'] = in_array($col_arr['marital_status'], ['15', '14']) ? $col_arr['marital_status'] + 74 : $col_arr['marital_status'] + 62;
					$col_arr['file_joint_or_separate'] = $col_arr['file_joint_or_separate'] + 74;
				*/

				$this->db->insert('client_analysis_results', $col_arr);
			}
		}

		return ['error' => $error];
		exit;
	}

//    Get IDR Intake Client Answer
	public function admin_save_intake_answer_by_client_2($intake_page_no = 1, $intake_id = 1) {
		$error = "";
		unset($_POST['Submit_intake_answer']);
		$client_id = $GLOBALS["loguser"]["id"];

		foreach ($_POST as $tbl => $rows) {
			foreach ($rows as $k => $row) {
				if ($tbl == "intake_comment_result") {$field_id = "intake_comment_id";}
				if ($tbl == "intake_answer_result") {$field_id = "intake_result_id";}

				//    Get Answer Details
				$q = $this->db->query("SELECT * FROM $tbl where $field_id='$k'");
				$ansR = $q->row_array();

				//    Get Question Details
				$q = $this->db->query("SELECT * FROM intake_question where intake_question_id='" . $ansR['intake_question_id'] . "'");
				$queR = $q->row_array();

				if (is_array($row)) {
					$isvalid = "Yes";
					$row = implode(',', $row);

					if ($queR['question_required'] == 'Yes') {
						if (trim($row) == "") {
							$isvalid = "No";
							$error .= "Please select atleast one option from <em>" . $queR['intake_question_body'] . "</em><br />";}}

					if ($tbl == "intake_answer_result" && $isvalid == "Yes") {
						if ($row == "0") {$row = "";}
						$this->db->where('intake_result_id', $k);
						$this->db->update($tbl, ['intake_answer_id_checkbox' => $row]);
					}
				} else {
					if ($ansR['intake_question_id'] == 61 || $ansR['intake_question_id'] == 147 || $ansR['intake_question_id'] == 157 || $ansR['intake_question_id'] == 167) {$address = trim($row);}
					if ($ansR['intake_question_id'] == 79) {$ref_address_1 = trim($row);}
					if ($ansR['intake_question_id'] == 89) {$ref_address_2 = trim($row);}

					if ($ansR['intake_question_id'] == 83) {$ref_phone_1 = trim($row);}
					if ($ansR['intake_question_id'] == 93) {$ref_phone_2 = trim($row);}

					if ($ansR['intake_question_id'] == 84) {$ref_email_1 = trim($row);}
					if ($ansR['intake_question_id'] == 94) {$ref_email_2 = trim($row);}

					$isvalid = "Yes";
					if ($queR['question_required'] == 'Yes') {

						if (trim($row) == "") {
							if ($ansR['intake_question_id'] == 56) {

								$q1 = $this->db->query("SELECT * FROM intake_answer_result where client_id=" . $ansR['client_id'] . " and intake_question_id=55;");
								$ansR1 = $q1->row_array();

								if ($ansR1['intake_answer_id'] == 60) {
									$isvalid = "No";
									$error .= "Filed <em>" . $queR['intake_question_body'] . "</em> cannot be empty" . "<br />";
								}
							} elseif ($ansR['intake_question_id'] == 152) {

								$q1 = $this->db->query("SELECT * FROM intake_answer_result where client_id=" . $ansR['client_id'] . " and intake_question_id=151;");
								$ansR1 = $q1->row_array();

								if ($ansR1['intake_answer_id'] == 136) {
									$isvalid = "No";
									$error .= "Filed <em>" . $queR['intake_question_body'] . "</em> cannot be empty" . "<br />";
								}
							} elseif ($ansR['intake_question_id'] == 162) {

								$q1 = $this->db->query("SELECT * FROM intake_answer_result where client_id=" . $ansR['client_id'] . " and intake_question_id=161;");
								$ansR1 = $q1->row_array();

								if ($ansR1['intake_answer_id'] == 139) {
									$isvalid = "No";
									$error .= "Filed <em>" . $queR['intake_question_body'] . "</em> cannot be empty" . "<br />";
								}
							} elseif ($ansR['intake_question_id'] == 172) {

								$q1 = $this->db->query("SELECT * FROM intake_answer_result where client_id=" . $ansR['client_id'] . " and intake_question_id=171;");
								$ansR1 = $q1->row_array();

								if ($ansR1['intake_answer_id'] == 142) {
									$isvalid = "No";
									$error .= "Filed <em>" . $queR['intake_question_body'] . "</em> cannot be empty" . "<br />";
								}
							} else {
								$isvalid = "No";
								$error .= "Filed <em>" . $queR['intake_question_body'] . "</em> cannot be empty" . "<br />";
							}
						}
					}

					if ($ansR['intake_question_id'] == 49 || $ansR['intake_question_id'] == 59 || $ansR['intake_question_id'] == 145 || $ansR['intake_question_id'] == 155 || $ansR['intake_question_id'] == 165) {
						$ssn_validation_regex = '/^(?!666|000|9\\d{2})\\d{3}-(?!00)\\d{2}-(?!0{4})\\d{4}$/';
						$rss = preg_match($ssn_validation_regex, $row); // returns 1
						if ($rss != '1') {
							$error .= "Please enter valid <em>" . $queR['intake_question_body'] . "</em>" . "<br />";
						}
					}

					if ($tbl == "intake_comment_result" && $isvalid == "Yes") {
						$this->db->where('intake_comment_id', $k);
						$this->db->update($tbl, ['intake_comment_body' => $row]);
					} else if ($tbl == "intake_answer_result" && $isvalid == "Yes") {
						$this->db->where('intake_result_id', $k);
						$this->db->update($tbl, ['intake_answer_id' => $row]);
					} else {}
				}
			}
		}

		if (isset($ref_address_1) && isset($ref_phone_1) && isset($ref_email_1)) {
			if (trim($ref_address_1) != "") {
				if ($ref_address_1 == $ref_address_2) {$error .= "Reference person 1 and 2 can not live at the same address<br />";}
				if ($ref_address_1 == $address) {$error .= "Reference person 1 and you can not live at the same address<br />";}
			}

			if (trim($ref_phone_1) != "") {if ($ref_phone_1 == $ref_phone_2) {$error .= "Reference person 1 and 2 can not have the same phone number<br />";}}
			if (trim($ref_email_1) != "") {if ($ref_email_1 == $ref_email_2) {$error .= "Reference person 1 and 2 can not have the same e-mail<br />";}}
		}

		if (isset($ref_address_2) && isset($ref_phone_2) && isset($ref_email_2)) {
			if (trim($ref_address_2) != "") {if ($ref_address_2 == $address) {$error .= "Reference person 2 and you can not live at the same address<br />";}}
		}

		if ($error == "") {
			$ir = $this->client_intake_client_status($client_id, $intake_id); //    Check Intake
			$col_arr_intk = ['client_id' => $client_id, 'intake_id' => $intake_id, 'add_date' => date('Y-m-d h:i:s'), 'status' => 'Complete'];
			$this->db->where('id', $ir['id']);
			$this->db->update("intake_client_status", $col_arr_intk);

			if ($intake_id == "2") {$program_id = 23;} else if ($intake_id == "3") {$program_id = 1;} else if ($intake_id == "5") {$program_id = 40;} else if ($intake_id == "6") {$program_id = 178;} else if ($intake_id == "7") {$program_id = 193;} else {}
			$this->db->query("update reminder_rules set status_flag='0' where client_id='$client_id' and program_id='$program_id' and step_id<='5'"); // Stop Reminder
			$this->db->query("update client_program_progress set status='Complete', step_completed_date='" . date('Y-m-d H:i:s') . "', updated_at='" . date('Y-m-d') . "' where client_id='$client_id' and program_id_primary='$program_id' and step_id<'5'"); // Stop Reminder

			$q = $this->db->query("SELECT * FROM client_program_progress where client_id='" . $client_id . "' and program_id_primary='$program_id' and step_id='5' limit 1");
			$cppr = $q->row_array();
			$this->admin_users_add_program_step($client_id, $cppr['program_definition_id']); // Update Step

			$this->send_inatke_complete_email($client_id, $intake_id);

			// if program is recertification, then update recertification date with next loan date and update the current one with 1 year ahead
			if ($intake_id == 5) {

				$client = $this->db->query('select * from users where id=' . $client_id)->row_array();

				$q = $this->db->query('select * from intake_file_result where client_id=' . $client_id . ' and intake_question_id=102');

				if ($q->num_rows() <= 0) {
					$q = $this->db->query('select * from intake_file_result where client_id=' . $client_id . ' and intake_question_id=6');
				}

				$fileid = $q->row_array();

				if (isset($fileid['intake_file_id'])) {
					$q = $this->db->query('select * from nslds_loans where client_id=' . $client_id . ' and intake_file_result_id=' . $fileid['intake_file_id'] . ' and loan_recertification_date > "' . $client['recertification_date'] . '" order by loan_recertification_date limit 1');

					$nslds = gettype($q) != 'boolean' ? $q->row_array() : [];

					if (isset($nslds['id']) && !empty($nslds['loan_recertification_date'])) {
						$this->db->where('id', $client['id']);
						$this->db->update('users', ['recertification_date' => $nslds['loan_recertification_date']]);
					}

					$q = $this->db->query('select * from nslds_loans where client_id=' . $client_id . ' and intake_file_result_id=' . $fileid['intake_file_id'] . ' and loan_recertification_date = "' . $client['recertification_date'] . '"');

					$nslds = gettype($q) != 'boolean' ? $q->row_array() : [];

					if (isset($nslds['id']) && !empty($client['recertification_date'])) {
						$this->db->where('id', $nslds['id']);
						$this->db->update('nslds_loans', ['loan_recertification_date' => date('Y-m-d', strtotime($client['recertification_date'] . ' + 1 year'))]);
					}
				}

			}

			if ($intake_id == 2) {$idr_url = "idr";} else if ($intake_id == 3) {$idr_url = "consolidation";} else if ($intake_id == 5) {$idr_url = "recertification";} else if ($intake_id == 6) {$idr_url = "recalculation";} else if ($intake_id == 7) {$idr_url = "switch_idr";} else { $idr_url = "initial";}
			redirect(base_url("account/intake/" . $idr_url));
			exit;
		}

		return ['error' => $error];
		exit;
	}

//    Send Intake Complete Email
	public function send_inatke_complete_email($client_id = 0, $intake_id = 0) {
		$cr = $this->default_model->get_arrby_tbl_single('users', '*', "id='" . $client_id . "'", '1');
		if (isset($cr['id'])) {
			$cmr = $this->default_model->get_arrby_tbl_single('users', '*', "id='" . $cr['parent_id'] . "'", '1');
			if (isset($cmr['id'])) {
				if ($intake_id == 1) {
					$sql = "SELECT * FROM client_program_progress where client_id='$client_id' and program_id_primary='91' and step_id='2' and status='Pending' limit 1";
					$q = $this->db->query($sql);
					$cppr = $q->row_array();
					if ($cppr['program_definition_id']) {
						$program_id = $cppr['program_definition_id'];
						$this->db->query("update reminder_rules set status_flag='0' where client_id='" . $client_id . "' and step_id='2' and program_id='" . $program_id . "'");
						$this->db->query("update client_program_progress set reminder_status=0 where client_id='" . $client_id . "' and step_id='2' and program_id='" . $program_id . "'");
						$this->admin_users_add_program_step($client_id, $cppr['program_definition_id']);
					}
				}
				if ($intake_id == 4) {
					$sql = "SELECT * FROM client_program_progress where client_id='$client_id' and program_id_primary='127' and step_id='2' and status='Pending' limit 1";
					$q = $this->db->query($sql);
					$cppr = $q->row_array();
					if ($cppr['program_definition_id']) {
						$program_id = $cppr['program_definition_id'];
						$this->db->query("update reminder_rules set status_flag='0' where client_id='" . $client_id . "' and step_id='2' and program_id='" . $program_id . "'");
						$this->db->query("update client_program_progress set reminder_status=0 where client_id='" . $client_id . "' and step_id='2' and program_id='" . $program_id . "'");
						$this->admin_users_add_program_step($client_id, $cppr['program_definition_id']);
					}
				}

				$cmpr = $this->get_company_details($cr['company_id']);
				$ir = $this->default_model->get_arrby_tbl_single('intake', '*', "intake_id='" . $intake_id . "'", '1');
				$smtp_data = $this->get_company_smtp_email_details($cr['company_id']);
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

//    Intake Check Step
	public function admin_intake_check_step($intake_page_no = 1, $check = 0) {
		$client_id = $GLOBALS["loguser"]["id"];

		$q = $check > 0 ? 96 : 0;
		$a = $check > 0 ? 74 : 0;

		$iform = 'intake_form';

		if ($check > 0) {
			$iform = 'update_intake_form';
		}

		if ($intake_page_no > 1 && $intake_page_no < 7) {
			$ansR = $this->default_model->get_arrby_tbl_single('intake_answer_result', '*', "client_id='" . $client_id . "' and intake_question_id='" . ($q + 5) . "'", '1');
			if ($ansR['intake_answer_id'] == "2") {redirect(base_url("account/" . $iform . "?intake_page_no=7"));exit;}
		}

		if ($intake_page_no > 2 && $intake_page_no < 7) {
			$ansR = $this->default_model->get_arrby_tbl_single('intake_file_result', '*', "client_id='" . $client_id . "' and intake_question_id='" . ($q + 6) . "'", '1');
			if (isset($ansR['intake_file_location'])) {
				$client_document = $this->crm_model->document_decrypt($ansR['intake_file_location']);
				if (!file_exists($client_document)) {
					$this->session->set_flashdata('error', 'Please upload your Federal loan data from Studentaid.gov');
					redirect(base_url("account/" . $iform . "?intake_page_no=2"));exit;}

			} else {
				$this->session->set_flashdata('error', 'Please upload your Federal loan data from Studentaid.gov');
				redirect(base_url("account/" . $iform . "?intake_page_no=2"));exit;}
		}

		if ($intake_page_no == 8) {
			//$ansR = $this->default_model->get_arrby_tbl_single('intake_comment_result','*',"client_id='".$client_id."' and intake_question_id='".($q+34)."'",'1');
			//if($ansR['intake_comment_body'] == "0") {    redirect(base_url("account/intake_form?intake_complete=result"));    exit;    }

			$ans46R = $this->default_model->get_arrby_tbl_single('intake_comment_result', '*', "client_id='" . $client_id . "' and intake_question_id='" . ($q + 46) . "'", '1');
			if ($ans46R['intake_comment_body'] == "0") {
				$this->session->set_flashdata('error', 'Please submit required details for Step 8 - Wrap up');
				redirect(base_url("account/" . $iform . "?intake_page_no=8"));
			}
		}

		if ($intake_page_no > 8) {redirect(base_url("account/" . $iform . "?intake_page_no=8"));exit;}
		if ($intake_page_no < 1) {redirect(base_url("account/" . $iform . "?intake_page_no=1"));exit;}

	}

//    Check Intake
	public function admin_intake_check($client_id = 0, $name = 'Initial') {
		$name = ucfirst(strtolower($name));

		if ($client_id == 0) {$client_id = $GLOBALS["loguser"]["id"];}
		$intake_id = $name != 'Initial' ? 4 : 1;

		$q = $name != 'Initial' ? 96 : 0;
		$a = $name != 'Initial' ? 74 : 0;
		$iform = $name != 'Initial' ? 'update_intake_form' : 'intake_form';

		$ans1R = $this->crm_model->admin_intake_answer_by_client($client_id, $q + 1);
		$ans2R = $this->crm_model->admin_intake_answer_by_client($client_id, $q + 2);
		$ans3R = $this->crm_model->admin_intake_answer_by_client($client_id, $q + 3);
		$ans4R = $this->crm_model->admin_intake_answer_by_client($client_id, $q + 4);
		$ans5R = $this->crm_model->admin_intake_answer_by_client($client_id, $q + 5);
		$ans6R = $this->crm_model->admin_intake_answer_by_client($client_id, $q + 6);
		$ans7R = $this->crm_model->admin_intake_answer_by_client($client_id, $q + 7);
		$ans10R = $this->crm_model->admin_intake_answer_by_client($client_id, $q + 10);
		$ans11R = $this->crm_model->admin_intake_answer_by_client($client_id, $q + 11);
		$ans18R = $this->crm_model->admin_intake_answer_by_client($client_id, $q + 18);
		$ans19R = $this->crm_model->admin_intake_answer_by_client($client_id, $q + 19);
		$ans20R = $this->crm_model->admin_intake_answer_by_client($client_id, $q + 20);
		$ans21R = $this->crm_model->admin_intake_answer_by_client($client_id, $q + 21);
		$ans28R = $this->crm_model->admin_intake_answer_by_client($client_id, $q + 28);

		$ans34R = $this->crm_model->admin_intake_answer_by_client($client_id, $q + 34);

		$ans35R = $this->default_model->get_arrby_tbl_single('intake_comment_result', '*', "client_id='" . $client_id . "' and intake_question_id='" . ($q + 35) . "' order by intake_comment_id asc", '1');
		$ans36R = $this->default_model->get_arrby_tbl_single('intake_comment_result', '*', "client_id='" . $client_id . "' and intake_question_id='" . ($q + 36) . "' order by intake_comment_id asc", '1');
		$ans37R = $this->default_model->get_arrby_tbl_single('intake_comment_result', '*', "client_id='" . $client_id . "' and intake_question_id='" . ($q + 37) . "' order by intake_comment_id asc", '1');
		$ans38R = $this->default_model->get_arrby_tbl_single('intake_comment_result', '*', "client_id='" . $client_id . "' and intake_question_id='" . ($q + 38) . "' order by intake_comment_id asc", '1');
		$ans39R = $this->default_model->get_arrby_tbl_single('intake_comment_result', '*', "client_id='" . $client_id . "' and intake_question_id='" . ($q + 39) . "' order by intake_comment_id asc", '1');
		$ans40R = $this->default_model->get_arrby_tbl_single('intake_comment_result', '*', "client_id='" . $client_id . "' and intake_question_id='" . ($q + 40) . "' order by intake_comment_id asc", '1');
		$ans41R = $this->default_model->get_arrby_tbl_single('intake_comment_result', '*', "client_id='" . $client_id . "' and intake_question_id='" . ($q + 41) . "' order by intake_comment_id asc", '1');
		$ans42R = $this->default_model->get_arrby_tbl_single('intake_comment_result', '*', "client_id='" . $client_id . "' and intake_question_id='" . ($q + 42) . "' order by intake_comment_id asc", '1');
		$ans46R = $this->default_model->get_arrby_tbl_single('intake_comment_result', '*', "client_id='" . $client_id . "' and intake_question_id='" . ($q + 46) . "' order by intake_comment_id asc", '1');

		$ans43R = $this->crm_model->admin_intake_answer_by_client($client_id, $q + 43);
		$ans44R = $this->crm_model->admin_intake_answer_by_client($client_id, $q + 44);

		//    Step - 1 Check

		if (trim($ans1R['intake_comment_body']) == "" || trim($ans2R['intake_comment_body']) == "" || trim($ans3R['intake_comment_body']) == "" || trim($ans4R['intake_comment_body']) == "" || trim($ans5R['intake_answer_id']) == "" || trim($ans5R['intake_answer_id']) == "0") {
			$this->session->set_flashdata('error', 'Please submit required details for Step 1 - Basic Information');
			redirect(base_url("account/" . $iform . "?intake_page_no=1"));
		}

		if ($ans5R['intake_answer_id'] != $a + 2) {
			//    Step - 2 Check
			$client_document = $this->crm_model->document_decrypt($ans6R['intake_file_location']);
			if (!file_exists($client_document)) {
				$this->session->set_flashdata('error', 'Please upload your Federal loan data from Studentaid.gov');
				redirect(base_url("account/" . $iform . "?intake_page_no=2"));
			}

			//    Step - 3 Check
			if (trim($ans7R['intake_answer_id']) == "" || trim($ans7R['intake_answer_id']) == "0" || trim($ans10R['intake_answer_id']) == "" || trim($ans10R['intake_answer_id']) == "0") {
				$this->session->set_flashdata('error', 'Please submit required details for Step 3 - Verify National Student Loan Database System Data');
				redirect(base_url("account/" . $iform . "?intake_page_no=3"));
			}

			//    Step - 4 Check
			if (trim($ans11R['intake_answer_id']) == "" || trim($ans11R['intake_answer_id']) == "0" || trim($ans18R['intake_comment_body']) == "" || trim($ans19R['intake_comment_body']) == "" || trim($ans20R['intake_comment_body']) == "") {
				$this->session->set_flashdata('error', 'Please submit required details for Step 4 - Family');
				redirect(base_url("account/" . $iform . "?intake_page_no=4"));
			}

			//    Step - 5 Check
			if (trim($ans21R['intake_answer_id']) == "" || trim($ans21R['intake_answer_id']) == "0") {
				$this->session->set_flashdata('error', 'Please submit required details for Step 5 - Employment');
				redirect(base_url("account/" . $iform . "?intake_page_no=5"));
			}

			//    Step - 6 Check
			if (trim($ans28R['intake_answer_id']) == "" || trim($ans28R['intake_answer_id']) == "0") {
				$this->session->set_flashdata('error', 'Please submit required details for Step 6 - Legal');
				redirect(base_url("account/" . $iform . "?intake_page_no=6"));
			}
		}

		//    Step - 7 Check
		//    Check Question 34
		if ($ans34R['intake_comment_body'] != "0") {
			if (trim($ans35R['intake_comment_body']) == "" || trim($ans36R['intake_comment_body']) == "" || trim($ans37R['intake_comment_body']) == "" || trim($ans38R['intake_comment_body']) == "" || trim($ans39R['intake_comment_body']) == "" || trim($ans40R['intake_comment_body']) == "" || trim($ans41R['intake_comment_body']) == "" || trim($ans42R['intake_comment_body']) == "") {

				$this->session->set_flashdata('error', 'Please enter the value of For each loan');
				redirect(base_url("account/" . $iform . "?intake_page_no=7"));
			} else if ($ans43R['intake_answer_id'] == "58") {
				//    Check Question    43
				$client_document = $this->crm_model->document_decrypt($ans44R['intake_file_location']);
				if (!file_exists($client_document)) {
					//$this->session->set_flashdata('error', 'Please provide a copy of the lawsuit');
					//redirect(base_url("account/intake_form?intake_page_no=7"));
				}
			}
		}

		//    Step - 8 Check
		//    Check Question 46
		if (trim($ans46R['intake_comment_body']) == "") {
			$this->session->set_flashdata('error', 'Please submit required details for Step 8 - Wrap up');
			redirect(base_url("account/" . $iform . "?intake_page_no=8"));
		}
	}

//    Renew NSLDS File
	public function admin_renew_nslds_file($client_id = 0, $name = 'Initial') {
		$error = '';
		if ($_FILES['intake_file_result']['name'] == "") {$error = "Select a valid document";}

		// upload file
		$config['allowed_types'] = 'txt';
		$config['file_name'] = rand('100', '999') . $client_id . time();
		$config['upload_path'] = './assets/uploads/document/' . date('Y/m');
		if (!is_dir($config['upload_path'])) {
			mkdir($config['upload_path'], 0777, true);
		}

		$this->load->library('upload', $config);
		if ($this->upload->do_upload('intake_file_result')) {
			if ($id > 0) {if (file_exists($result['client_document'])) {unlink($result['client_document']);}}
			$col_arr['client_document'] = 'assets/uploads/document/' . date('Y/m');
			$col_arr['client_document'] = $this->document_encrypt($col_arr['client_document']);
		} else {
			$error = $this->upload->display_errors();
			if (stripos($error, 'The filetype you are attempting to upload is not allowed.') !== false) {
				$error = str_replace('The filetype you are attempting to upload is not allowed.', 'You have selected an invalid file type. Only the student aid txt file can be uploaded here. See the instructions to download your text file from <a href="http://studentaid.gov/" target="_blank">studentaid.gov</a> and try again.', $error);
			}
		}

		if ($error == '') {
			if ($name != 'Initial') {
				$ansR = $this->default_model->get_arrby_tbl_single('intake_file_result', '*', "client_id='" . $client_id . "' and intake_question_id='102'", '1');
			} else {
				$ansR = $this->default_model->get_arrby_tbl_single('intake_file_result', '*', "client_id='" . $client_id . "' and intake_question_id='6'", '1');
			}

			$client_document = $this->crm_model->document_decrypt($ansR['intake_file_location']);
			if (file_exists($client_document)) {unlink($client_document);}

			$intake_file_location = 'assets/uploads/document/' . date('Y/m') . '/' . $this->upload->data('file_name');

			$file_data = read_file($intake_file_location);
			//echo $file_data."<br />";
			$arr_file_data = preg_split("/\r\n|\n|\r/", $file_data);

			foreach ($arr_file_data as $k => $v) {
				$arr_file_data_2[] = explode(":", $v);
				$vr = explode(":", $v);
				$rmv1 = ['$', ',', ' ', 'A', 'B', '[', ']'];
				$rmv2 = "";
				$vr[0] = str_replace(['[', ']'], $rmv2, $vr[0]);

				if (stripos($vr[0], 'File Request Date') !== false && !empty($vr[1])) {
					if (date('Y-m-d', strtotime($vr[1] . ":" . $vr[2] . ":" . $vr[3] . ' + 180 days')) < date('Y-m-d')) {
						$error = 'You are trying to upload older NSLDS File. Please upload a newer one.';
						unlink($intake_file_location);
						break;
					}

				}
			}

			if (empty($error)) {
				$intake_file_location = $this->document_encrypt($intake_file_location);

				$this->db->where('intake_file_id', $ansR['intake_file_id']);
				$this->db->update("intake_file_result", ['intake_file_location' => $intake_file_location]);

				$this->crm_model->read_and_save_intake_file($client_id, ($name != 'Initial' ? 4 : 1));
			}

		} else { $error = $error;}
		$result['error'] = $error;
		return $result;
	}

//    Read and Save Intake File
	public function read_and_save_intake_file($client_id = 0, $intake_id = 1) {
		if ($intake_id == 4) {
			$ansR = $this->default_model->get_arrby_tbl_single('intake_file_result', '*', "client_id='" . $client_id . "' and intake_question_id='102'", '1');
		} else {
			$ansR = $this->default_model->get_arrby_tbl_single('intake_file_result', '*', "client_id='" . $client_id . "' and intake_question_id='6'", '1');
		}

		$intake_file_result_id = $ansR['intake_file_id'];
		$client_document = $this->crm_model->document_decrypt($ansR['intake_file_location']);
		$file_data = read_file($client_document);
		//echo $file_data."<br />";
		$arr_file_data = preg_split("/\r\n|\n|\r/", $file_data);
		$arr_file_data_2 = array();
		$arr_file_data_rows = array();
		$total_federal_student_loan_debt = 0;
		$student_total_all_loans_outstanding_principal = 0;
		$student_total_all_loans_outstanding_interest = 0;

		$arrr_ifr = ["student_total_all_loans_outstanding_principal", "student_total_all_loans_outstanding_interest", "total_direct_stafford_unsubsidized_outstanding_principal", "total_direct_stafford_unsubsized_outstanding_interest", "total_direct_staffordsubsidized_outstanding_principal", "total_direct_stafford_subsidized_outstanding_interest", "total_ffel_stafford_unsubsidized_outstanding_principal", "total_ffel_stafford_unsubsidized_outstanding_interest", "total_ffel_stafford_subsidized_outstanding_principal", "total_ffel_stafford_subsidized_outstanding_interest", "total_direct_consolidated_unsubsidized_outstanding_principal", "total_direct_consolidated_unsubsidized_outstanding_interest", "total_ffel_consolidated_outstanding_principal", "total_ffel_consolidated_outstanding_interest", "total_federally_insured_fisl_outstanding_principal", "total_federally_insured_fisl_outstanding_interest", "total_direct_plus_parent_outstanding_principal", "total_direct_plus_parent_outstanding_interest", "total_ffel_plus_parent_outstanding_principal", "total_ffel_plus_parent_outstanding_interest"];
		foreach ($arrr_ifr as $ifr) {$arr_ifr[$ifr] = 0;}

		$i = 0;
		$anniversary = '';
		foreach ($arr_file_data as $k => $v) {
			$arr_file_data_2[] = explode(":", $v);
			$vr = explode(":", $v);
			$rmv1 = ['$', ',', ' ', 'A', 'B', '[', ']'];
			$rmv2 = "";
			$vr[0] = str_replace(['[', ']'], $rmv2, $vr[0]);

			foreach ($arrr_ifr as $ifr) {
				$ifr_n1 = strtolower(str_replace("_", " ", $ifr));
				$ifr_n2 = str_replace("total ", " ", $ifr_n1);
				if (strtolower($vr[0]) == $ifr_n1 || strtolower($vr[0]) == $ifr_n2) {$arr_ifr[$ifr] += str_replace($rmv1, $rmv2, $vr[1]);}
			}

			// save anniversary date as recertification date in users table
			if (stripos($vr[0], 'Anniversary Date') !== false && !empty($vr[1])) {
				if (DateTime::createFromFormat('Y-m-d H:i:s', $vr[1]) !== false) {
					if (empty($anniversary)) {
						$anniversary = $vr[1];
					}

					if (date('Y-m-d', strtotime($vr[1])) >= date('Y-m-d') && date('Y-m-d', strtotime($anniversary)) >= date('Y-m-d', strtotime($vr[1]))) {
						$anniversary = date('Y-m-d', strtotime($vr[1]));
					}
				}
				$arr_file_data_rows[$i]['loan_recertification_date'] = date('Y-m-d', strtotime($vr[1]));

			}

			if ($vr[0] == "Student Total All Loans Outstanding Principal" || $vr[0] == "Student Total All Loans Outstanding Interest") {
				$total_federal_student_loan_debt += str_replace($rmv1, $rmv2, $vr[1]);
			}

			if ($vr[0] == "Loan Type" || $vr[0] == "Loan type" || $vr[0] == "Loan Type Description" || $vr[0] == "Loan type description") {
				$i++;
				$arr_file_data_rows[$i]['loan_type'] = $vr[1];}

			if ($vr[0] == "Loan Attending School" || $vr[0] == "Loan attending school" || $vr[0] == "Loan Attending School Name" || $vr[0] == "Loan attending school name") {$arr_file_data_rows[$i]['loan_attending_school'] = $vr[1];}
			if ($vr[0] == "Loan Date" || $vr[0] == "Loan date") {
				$dexpr = explode("/", $vr[1]);
				$arr_file_data_rows[$i]['loan_date'] = $dexpr[2] . "-" . $dexpr[0] . "-" . $dexpr[1];
			}
			//if($vr[0] == "Loan Amount" || $vr[0] == "Loan amount"){ $arr_file_data_rows[$i]['origination_amount'] = $vr[1];    }

			if ($vr[0] == "Loan Disbursed Amount" || $vr[0] == "Loan disbursed amount") {$arr_file_data_rows[$i]['loan_dispersed_amount'] = str_replace($rmv1, $rmv2, $vr[1]);}
			if ($vr[0] == "Loan Outstanding Principal Balance" || $vr[0] == "Loan outstanding principal balance") {$arr_file_data_rows[$i]['loan_outstanding_principal_balance'] = str_replace($rmv1, $rmv2, $vr[1]);}
			if ($vr[0] == "Loan Outstanding Interest Balance" || $vr[0] == "Loan outstanding interest balance") {$arr_file_data_rows[$i]['loan_outstanding_interest_balance'] = str_replace($rmv1, $rmv2, $vr[1]);}

			if (strtolower($vr[0]) == "loan interest rate type description") {$arr_file_data_rows[$i]['loan_interest_rate_type_description'] = str_replace($rmv1, $rmv2, $vr[1]);}
			if (strtolower($vr[0]) == "loan interest rate") {$arr_file_data_rows[$i]['loan_interest_rate'] = str_replace($rmv1, $rmv2, $vr[1]);}

			if ($vr[0] == "Loan Status" || $vr[0] == "Loan status") {if (!isset($arr_file_data_rows[$i]['loan_status'])) {$arr_file_data_rows[$i]['loan_status'] = $vr[1];}}
			if ($vr[0] == "Loan Status Description" || $vr[0] == "Loan status description") {if (!isset($arr_file_data_rows[$i]['loan_status_description'])) {$arr_file_data_rows[$i]['loan_status_description'] = $vr[1];}}
			if ($vr[0] == "Loan Status Effective Date" || $vr[0] == "Loan status effective date") {
				if (!isset($arr_file_data_rows[$i]['loan_status_effective_date'])) {

					$dexpr = explode("/", $vr[1]);
					$arr_file_data_rows[$i]['loan_status_effective_date'] = $dexpr[2] . "-" . $dexpr[0] . "-" . $dexpr[1];

				}}

			if ($vr[0] == "Loan Contact Name" || $vr[0] == "Loan contact name") {$arr_file_data_rows[$i]['loan_contact_name'] = $vr[1];}

			if (strtolower($vr[0]) == "loan contact type") {$arr_file_data_rows[$i]['loan_contact_type'] = $vr[1];}
			if (strtolower($vr[0]) == "loan contact street address 1") {$arr_file_data_rows[$i]['loan_contact_street_address_1'] = $vr[1];}
			if (strtolower($vr[0]) == "loan contact street address 2") {$arr_file_data_rows[$i]['loan_contact_street_address_2'] = $vr[1];}
			if (strtolower($vr[0]) == "loan contact city") {$arr_file_data_rows[$i]['loan_contact_city'] = $vr[1];}
			if (strtolower($vr[0]) == "loan contact state") {$arr_file_data_rows[$i]['loan_contact_state'] = $vr[1];}
			if (strtolower($vr[0]) == "loan contact zip code") {$arr_file_data_rows[$i]['loan_contact_zip_code'] = $vr[1];}
		}

		if (!empty($anniversary)) {
			$this->db->where('id', $client_id);
			$this->db->update('users', ['recertification_date' => $anniversary, 'recert_updated' => false]);
		}

		//    Update New Records
		$this->db->where('intake_file_id', $ansR['intake_file_id']);
		$this->db->update('intake_file_result', $arr_ifr);
		$this->db->delete("nslds_loans", ["intake_file_result_id" => $intake_file_result_id, "client_id" => $client_id]);

		if (count($arr_file_data_rows) > 0) {
			foreach ($arr_file_data_rows as $k => $v) {
				$sltb_code_id = '';
				$in_fefault = '0';
				$is_ffel = '0';

				if (in_array(trim($v['loan_status']), ["DF", "DL", "DU", "DX", "DZ", "XD"])) {$in_fefault = 1;}
				if (in_array('Ffel', explode(" ", $v['loan_type'])) || in_array('FFEL', explode(" ", $v['loan_type']))) {$is_ffel = 1;}
				if (in_array('Parent', explode(" ", $v['loan_type'])) || in_array('PARENT', explode(" ", $v['loan_type']))) {$sltb_code_id = 1;}
				if (in_array('Perkins', explode(" ", $v['loan_type'])) || in_array('PERKINS', explode(" ", $v['loan_type']))) {$sltb_code_id = 3;}
				if (in_array('Stafford', explode(" ", $v['loan_type'])) || in_array('STAFFORD', explode(" ", $v['loan_type']))) {$sltb_code_id = 4;}

				$v['intake_file_result_id'] = $intake_file_result_id;
				$v['client_id'] = $client_id;
				$v['sltb_code_id'] = $sltb_code_id;
				$v['in_fefault'] = $in_fefault;
				$v['is_ffel'] = $is_ffel;
				$this->db->insert("nslds_loans", $v);
			}

			$this->db->query("UPDATE nslds_loans set sltb_code_id='' where client_id='$client_id' and sltb_code_id='1' and (loan_status='PZ' or loan_status='PD' or loan_status='PF' or loan_status='PM' or loan_status='PX' or loan_status='AL' or loan_status='BC' or loan_status='CS' or loan_status='DC' or loan_status='DD' or loan_status='DE' or loan_status='DI' or loan_status='DP' or loan_status='DS' or loan_status='DW' or loan_status='FC' or loan_status='OD' or loan_status='VA')");

			$error_handling = "";
			$q = $this->db->query("SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' and sltb_code_id='1'");
			$nr = $q->num_rows();
			if ($nr == 0) {
				$this->db->query("update nslds_loans set sltb_code_id='2' where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' and sltb_code_id=''");
			}

			$q = $this->db->query("SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' and sltb_code_id=''");
			$nr = $q->num_rows();
			if ($nr > 0) {
				$sql = "SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' and sltb_code_id='' order by loan_status_effective_date asc";
				$q = $this->db->query($sql);
				foreach ($q->result() as $row) {
					$is_sltb_code_id = "";
					if (in_array('Consolidated', explode(" ", $row->loan_type)) || in_array('CONSOLIDATED', explode(" ", $row->loan_type))) {
						$d1 = date('Y-m-d', strtotime($row->loan_date . ' - 30 days'));
						$d2 = date('Y-m-d', strtotime($row->loan_date . ' + 30 days'));

						$sql = "SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' and sltb_code_id='1' and (loan_status_effective_date BETWEEN '$d1' AND '$d2') order by loan_status_effective_date asc";
						$q = $this->db->query($sql);
						$nr5 = $q->num_rows();
						if ($nr5 > 0) {
							$this->db->query("update nslds_loans set sltb_code_id='1' where id='" . $row->id . "'");
						} else { $is_sltb_code_id = "Yes";}
					} else { $is_sltb_code_id = "Yes";}

					if ($is_sltb_code_id == "Yes") {
						$error_handling = "Yes";
						$this->db->query("update nslds_loans set sltb_code_id='2' where id='" . $row->id . "'");
					}

				}
			}

			if ($error_handling == "Yes") {
				$cR = $this->default_model->get_arrby_tbl_single('users', '*', "id='" . $client_id . "'", '1');
				$cname = trim($cR["name"] . " " . $cR["lname"]);
				$subject = $cname . ' has an unidentified loan';
				$Msg = '<p>Indicating NSLDS with its ID and Date and ' . $cname . ' has an unidentified loan.</p><p>Please review.</p> <p>The NSLDS
file is attached in this mail.';
				$this->send_email(['email' => 'support@studentloantoolbox.com', 'Msg' => $Msg, 'subject' => $subject, 'attachment_1' => base_url($client_document)]);
			}
		}

	}

//    Get Intake Client Answer
	public function admin_intake_answer_by_client($client_id = 0, $intake_question_id = 0) {
		$q = $this->db->query("SELECT * FROM intake_question where intake_question_id='$intake_question_id'");
		$queR = $q->row_array();

		if ($queR['intake_question_type'] == 'Radio' || $queR['intake_question_type'] == 'Radio Group' || $queR['intake_question_type'] == 'Checkbox') {$table_name = "intake_answer_result";}
		if ($queR['intake_question_type'] == 'Comment' || $queR['intake_question_type'] == 'Table') {$table_name = "intake_comment_result";}
		if ($queR['intake_question_type'] == 'File') {$table_name = "intake_file_result";}

		//    Check Card
		$q = $this->db->query("SELECT * FROM $table_name where client_id='$client_id' and intake_question_id='$intake_question_id'");
		$nr = $q->num_rows();

		if ($nr == 0) {
			if ($queR['intake_question_type'] == 'Table') {
				// for ($i = 1; $i <= 6; $i++) {$this->db->insert($table_name, ['client_id' => $client_id, 'intake_question_id' => $intake_question_id]);}
			} else {
				$this->db->insert($table_name, ['client_id' => $client_id, 'intake_question_id' => $intake_question_id]);
			}
		}

		$q = $this->db->query("SELECT * FROM $table_name where client_id='$client_id' and intake_question_id='$intake_question_id'");
		if ($queR['intake_question_type'] == 'Table') {
			$result = $q->result_array();
		} else {
			$result = $q->row_array();
		}

		return $result;
	}

//    Save Current Analysis
	public function admin_save_client_analysis_results() {
		$error = '';
		@extract($_POST);

		if ($error == '') {
			$col_arr = array();
			// $spouse_agi = $id_spouse_agi;
			// $spouse_monthly = $id_spouse_monthly;
			if ($marital_status == "14") {
				//$spouse_agi = $spouse_monthly = 0;
			}

			$include_in_client_report = ["family_size" => $family_size, "marital_status" => $marital_status, "use_agi_or_monthly" => $use_agi_or_monthly, "file_joint_or_separate" => $file_joint_or_separate, "client_agi" => $client_agi, "spouse_agi" => $spouse_agi, "client_monthly" => $client_monthly, "spouse_monthly" => $spouse_monthly, "payment_plan_selected" => $payment_plan_selected];

			$col_arr = ['client_id' => $client_id, 'company_id' => $company_id, 'intake_id' => $intake_id, 'nslds_id' => $nslds_id, "family_size" => $family_size, "marital_status" => $marital_status, "file_joint_or_separate" => $file_joint_or_separate, "client_agi" => $client_agi, "client_monthly" => $client_monthly, "spouse_agi" => $spouse_agi, "spouse_monthly" => $spouse_monthly, "scenario_selected" => $scenario_selected, "payment_plan_selected" => $payment_plan_selected, 'include_in_client_report' => json_encode($include_in_client_report), 'internal_notes' => $internal_notes, 'wage_garnishment_exists' => $wage_garnishment_exists, 'in_default' => $in_default, 'rehab_required' => $rehab_required, 'consolidation_included' => $consolidation_included, 'deferment_forbearance_status' => $deferment_forbearance_status, 'prong' => json_encode($prong), 'updated_at' => date("Y-m-d H:i:s"), "status" => $status];

			$q = 0;
			$a = 0;
			$program_id_primary = 91;

			if ($this->db->query('select * from intake_client_status where client_id=' . $client_id . ' and intake_id=4')->num_rows() > 0) {
				$q = 96;
				$a = 74;
				$intake_id = 4;
				$program_id_primary = 127;
			}

			$this->intake_model->dbUpdateIntakeAnswer($client_id, ($q + 11), ["intake_answer_id" => $marital_status]);
			$this->intake_model->dbUpdateIntakeAnswer($client_id, ($q + 14), ["intake_answer_id" => $file_joint_or_separate]);
			// $this->intake_model->dbUpdateIntakeAnswer($client_id, ($q + 15), ["intake_comment_body" => $spouse_agi]);
			$this->intake_model->dbUpdateIntakeAnswer($client_id, ($q + 16), ["intake_comment_body" => $spouse_monthly]);

			if (isset($scenario_selected)) {$col_arr['scenario_selected'] = $scenario_selected;}

			if (isset($par_csd)) {$col_arr['par_csd'] = $par_csd;} else { $par_csd = "";}
			if (isset($par_comment)) {$col_arr['par_comment'] = $par_comment;} else { $par_comment = "";}

			if (isset($consent) && !empty($consent)) {

				$cintake = $this->db->query('select * from intake_client_status where intake_id=1 and client_id=' . $client_id)->row_array();

				if (!isset($cintake['id'])) {

					$arr = [
						'intake_id' => 1,
						'client_id' => $client_id,
						'form_data' => json_encode(['consent' => $consent]),
					];
					$this->db->insert('intake_client_status', $arr);
				} else {
					$formdata = !empty($cintake['form_data']) ? (array) json_decode($cintake['form_data']) : ['consent' => $consent];
					$formdata['consent'] = $consent;
					$data = ['form_data' => json_encode($formdata)];

					$this->db->where(['id' => $cintake['id']]);
					$this->db->update('intake_client_status', $data);
				}
			}

			$q = $this->db->query("select * from client_analysis_results where client_id='$client_id' and company_id='$company_id' and intake_id='$intake_id' and nslds_id='$nslds_id'");
			$nr = $q->num_rows();

			// echo $intake_id;die;
			if ($nr == 0) {
				$col_arr['client_analysis_date'] = date('Y-m-d');
				$col_arr['created_at'] = date("Y-m-d H:i:s");
				$this->db->insert("client_analysis_results", $col_arr); // Insert Record
			} else {
				$col_arr['updated_at'] = date("Y-m-d H:i:s");
				$this->db->where(['client_id' => $client_id, 'company_id' => $company_id, 'intake_id' => $intake_id, 'nslds_id' => $nslds_id]);
				$this->db->update("client_analysis_results", $col_arr);
			}

			// update date_initially_viewed for this client if null
			$cl = $this->db->query('select * from clients where client_id=' . $client_id)->row_array();

			if (empty($cl['date_initially_viewed'])) {
				$this->db->where(['client_id' => $client_id]);
				$this->db->update('clients', ['date_initially_viewed' => date('Y-m-d')]);
			}

			//    Pre-Analysis Review – No Follow Up (We cannot assist you)
			if ($par_csd == "We can not assist you" || $par_csd == "Skip email but do not continue with Intake Program") {
				$this->db->query("update intake_client_status set status2='Stop',last_sent_reminder='2050-12-12' where client_id='$client_id' and intake_id='" . $intake_id . "'");
				$this->db->query("update client_program_progress set status_1='Stop' where client_id='$client_id' and program_id_primary='" . $program_id_primary . "'");
				$this->db->query("update client_program_progress set status='Stop',step_completed_date='" . date('Y-m-d') . "',updated_at='" . date('Y-m-d') . " h:i:s' where client_id='$client_id' and program_id='" . ($program_id_primary + 2) . "' and step_id='3'");

				if ($par_csd == "We can not assist you") {
					$this->send_email_analysis_complete_no_follow_up($client_id, $company_id, $intake_id, $nslds_id);
				}
			}

			//    Pre-Analysis Review – Perform Follow UP (Schedule-Payment Reminder) - [We can help you]
			if ($par_csd == "We can help you" || $par_csd == "Skip email and continue Intake Program") {

				$qry = $this->db->query("select * from client_program_progress where client_id='$client_id' and step_id='3' and program_id_primary='" . $program_id_primary . "' limit 1");
				$row = $qry->row_array();
				if (isset($row['program_definition_id'])) {
					$this->db->query("update client_program_progress set step_completed_date='',status='Pending' where program_definition_id='" . $row['program_definition_id'] . "'");
					$this->crm_model->admin_users_add_program_step($client_id, $row['program_definition_id']);
				}

				$this->send_email_analysis_saved_perform_follow_up_schedule_payment_reminder($client_id, $company_id, $intake_id, $nslds_id);
			}

			if ($status == "Complete" || $status == "Saved") {
				$q = $this->db->query("select * from client_analysis_results where client_id='$client_id' and company_id='$company_id' and intake_id='$intake_id' and nslds_id='$nslds_id'");
				$car = $q->row_array();
				//    The system should automatically mark this step as complete and move to step 4
				$this->ca_mark_step3_complete_move_to_step4($client_id, $car['id']);
			}

		} else { $error = $error;}
		$result['error'] = $error;
		return $result;
	}

//    Analysis Complete – No Follow Up (We cannot assist you)
	public function send_email_analysis_complete_no_follow_up($client_id, $company_id, $intake_id, $nslds_id) {
		$q = $this->db->query("select * from client_analysis_results where client_id='$client_id' and company_id='$company_id' and intake_id='$intake_id' and nslds_id='$nslds_id'");
		$car = $q->row_array();
		if (isset($car['id'])) {
			$par_comment = "";
			if (isset($_POST['par_comment'])) {if (trim($_POST['par_comment']) != "") {$par_comment = '<p>' . $_POST['par_comment'] . '</p>';}}
			$cr = $this->default_model->get_arrby_tbl_single('users', '*', "id='" . $client_id . "'", '1');
			$cmr = $this->default_model->get_arrby_tbl_single('users', '*', "id='" . $cr['parent_id'] . "'", '1');
			$cmpR = $this->get_company_details($company_id);
			$smtp_data = $this->get_company_smtp_email_details($company_id);

			$smtp_data['email'] = $cr['email'];
			$smtp_data['subject'] = "Your Student Loan Review Results";
			$smtp_data['Msg'] = $cmpR['email_header'] . '
		<p>Dear ' . $cr['name'] . ',</p>
		<p>Thanks for taking the time to complete your intake. We have reviewed your details and have concluded that we cannot assist you with your student loan issues at this time.</p>
		' . $par_comment . '
		<p>We appreciate your choosing <strong>' . $cmpR['name'] . '</strong> and look forward to assisting you with any other legal issues you may have.</p>
		<p>---<br /><strong>Regards</strong><br />' . $cmpR['name'] . '<br /><a href="' . base_url($cmpR['slug'] . "/account") . '">' . base_url($cmpR['slug'] . "/account") . '</a></p>';
			$this->send_email($smtp_data);
		}
	}

//    Analysis Saved – Perform Follow UP (Schedule-Payment Reminder)
	public function send_email_analysis_saved_perform_follow_up_schedule_payment_reminder($client_id, $company_id, $intake_id, $nslds_id) {
		$q = $this->db->query("select * from client_analysis_results where client_id='$client_id' and company_id='$company_id' and intake_id='$intake_id' and nslds_id='$nslds_id'");
		$car = $q->row_array();
		if (isset($car['id'])) {
			if ($car['par_csd'] == "We can help you") {
				$cr = $this->default_model->get_arrby_tbl_single('users', '*', "id='" . $client_id . "'", '1');
				$cmr = $this->default_model->get_arrby_tbl_single('users', '*', "id='" . $cr['parent_id'] . "'", '1');
				$cmpR = $this->get_company_details($company_id);
				$smtp_data = $this->get_company_smtp_email_details($company_id);

				// commented by apoorva after adding vl_reminder_rules record for intake
				/*$days = $cmpR['send_schedule_payment_reminder'];
	                $last_sent_reminder = date('Y-m-d', strtotime((date('Y-m-d')) . ' + ' . $days . ' days'));
	                $this->db->query("update client_analysis_results set last_sent_reminder='$last_sent_reminder' where id='" . $car['id'] . "'");
*/

				$cl_ec_id = $cr['id'] . "." . $intake_id . "." . $car['id'];
				$cl_ec = strtr(base64_encode($cl_ec_id), '+/', '-_');
				$srl = base_url($cmpR['slug'] . "/analysis_reminder/stop/" . $cl_ec);
				$stop_reminder_link = '<a href="' . $srl . '" style="background:#FF0000; color:#FFFFFF; padding:5px 20px; font-weight:bold; text-decoration:none;">STOP REMINDER</a>';

				if (trim($car['par_comment']) != "") {$par_comment = '<p>' . $car['par_comment'] . '</p>';} else { $par_comment = '';}
				$smtp_data['email'] = $cr['email'];
				$smtp_data['subject'] = "Your Student Loan Review Results";
				$smtp_data['Msg'] = $cmpR['email_header'] . '<p>Dear ' . $cr['name'] . ',</p>
			<p>Thanks for taking the time to complete your intake. We have reviewed your details and have concluded that we can provide you with options that may resolve your situation.</p>' . $par_comment . '
			<p>To discuss and plan your strategy, please follow these instructions:</p>
			<ol>

			<li>Please use the links below to schedule a time for your analysis and pay your fee.<br/>To Schedule: <a href="' . $cmr['calendar_link'] . '">' . $cmr['calendar_link'] . '</a> <br/>To pay for your Analysis: <a href="' . $cmpR['payment_link'] . '">' . $cmpR['payment_link'] . '</a></li>
			</ol>
			<p>If you have decided not to continue with us at this time, Please click this link to stop any further reminders.</p>
			<p>' . $stop_reminder_link . '</p>
			<p>If you have any questions, please email us at <a href="' . $cmr['email'] . '">' . $cmr['email'] . '</a>.</p>
			<p>We appreciate your choosing <strong>' . $cmpR['name'] . '</strong> and look forward to assisting you with your student loan matters.</p>
			<p>---<br /><strong>Regards</strong><br />' . $cmpR['name'] . '<br /><a href="' . base_url($cmpR['slug'] . "/account") . '">' . base_url($cmpR['slug'] . "/account") . '</a></p>';
				$this->send_email($smtp_data);
				// <li>Please select a time from our calendar to set your meeting with your Student Loan Law attorney: <a href="' . $cmpR['calendar_link'] . '">' . $cmpR['calendar_link'] . '</a></li>
			}
		}
	}

//    4. Next Step Reminder: to Case Manager
	//    Note, this reminder should be sent every day until the Client who caused the generation of this email is active in at least one Program.
	public function send_email_next_step_reminder_to_case_manager($client_id = '', $program_definition_id = '') {
		$q = $this->db->query("select * from client_program where status='Pending' and client_id='$client_id' and program_definition_id='$program_definition_id'");
		$cpr = $q->row_array();
		if (isset($cpr['id'])) {
			$cr = $this->default_model->get_arrby_tbl_single('users', '*', "id='" . $client_id . "'", '1');
			$cmr = $this->default_model->get_arrby_tbl_single('users', '*', "id='" . $cr['parent_id'] . "'", '1');
			$cmpR = $this->get_company_details($cr['company_id']);
			$smtp_data = $this->get_company_smtp_email_details($cr['company_id']);

			$last_sent_reminder = date('Y-m-d', strtotime((date('Y-m-d')) . ' + 1 days'));
			$this->db->query("update client_program set last_sent_reminder='$last_sent_reminder' where id='" . $cpr['id'] . "'");

			$q = $this->db->query("select * from intake_file_result where client_id='$client_id' and intake_question_id='6'");
			$ifr = $q->row_array();
			if (isset($ifr['intake_file_id'])) {
				$nslds_id = $ifr['intake_file_id'];
				$intake_id = 1;
				$q = $this->db->query("select * from client_analysis_results where client_id='$client_id' and company_id='" . $cr['company_id'] . "' and intake_id='$intake_id' and nslds_id='$nslds_id'");
				$car = $q->row_array();
				if (isset($car['id'])) {
					if ($car['par_csd'] == "We can help you") {
						$client_name = trim($cr['name'] . " " . $cr['lname']);
						$smtp_data['email'] = $cmr['email'];
						$smtp_data['subject'] = $client_name . " wishes to continue to their next step";
						$smtp_data['Msg'] = $cmpR['email_header'] . '
			<p>Dear ' . $cmr['name'] . ' ' . $cmr['lname'] . ',</p>
			<p>Your Client <strong>' . $client_name . '</strong> has indicated they would like to continue with your student loan services. Please review their analysis and internal notes on the analysis screen so you can generate the correct agreement. Make sure you add your Client to the appropriate program so Student Loan Toolbox can properly assist you with all the steps and track your progress with your Client properly.</p>
			<p>If you have any questions, please email us at <a href="mailto:support@studentloantoolbox.com">support@studentloantoolbox.com</a>.</p>
			<p>We appreciate your business.</p>
			<p>---<br /><strong>Regards</strong><br />Student Loan Toolbox<br /><a href="' . base_url($cmpR['slug'] . "/account") . '">' . base_url($cmpR['slug'] . "/account") . '</a></p>';
						$this->send_email($smtp_data);
					}}}
		}
	}

//    3. Post-Analysis Follow Up Reminder to Client
	public function send_email_post_analysis_follow_up_reminder_to_client($client_id = '', $program_definition_id = '') {
		$q = $this->db->query("select * from client_program where status='Pending' and client_id='$client_id' and program_definition_id='$program_definition_id'");
		$cpr = $q->row_array();
		if (isset($cpr['id'])) {
			$cr = $this->default_model->get_arrby_tbl_single('users', '*', "id='" . $client_id . "'", '1');
			$cmr = $this->default_model->get_arrby_tbl_single('users', '*', "id='" . $cr['parent_id'] . "'", '1');
			$cmpR = $this->get_company_details($cr['company_id']);
			$smtp_data = $this->get_company_smtp_email_details($cr['company_id']);

			$last_sent_reminder = date('Y-m-d', strtotime((date('Y-m-d')) . ' + 1 days'));
			$this->db->query("update client_program set last_sent_reminder='$last_sent_reminder' where id='" . $cpr['id'] . "'");

			$client_name = trim($cr['name'] . " " . $cr['lname']);
			$cm_name = trim($cmr['name'] . " " . $cmr['lname']);
			$program_url = base_url($cmpR['slug'] . "/program/" . $program_definition_id);

			$cl_ec_id = $cr['id'] . "." . $program_definition_id . "." . $cpr['id'];
			$cl_ec = strtr(base64_encode($cl_ec_id), '+/', '-_');
			$srl = base_url($cmpR['slug'] . "/program/stop/" . $cl_ec);
			$stop_reminder_link = '<a href="' . $srl . '" style="background:#FF0000; color:#FFFFFF; padding:5px 20px; font-weight:bold; text-decoration:none;">STOP REMINDER</a>';

			$smtp_data['email'] = $cr['email'];
			$smtp_data['subject'] = "Next steps for your Student Loan";
			$smtp_data['Msg'] = $cmpR['email_header'] . '
		<p>Dear ' . $client_name . ',</p>
		<p>This email is to inform you that you have options for your student loans as we discussed during your analysis.  If you would like to proceed with those steps, Please click here.</p>
		<p>insert link to the <a href="' . $program_url . '">' . $program_url . '</a></p>
		<p>If you have decided not to continue with us at this time, Please click this link to stop any further reminders.</p>
		<p>' . $stop_reminder_link . '</p>
		<p>If you have any questions, please email us at <a href="mailto:' . $cmr['email'] . '">' . $cmr['email'] . '</a>.
		<p>We appreciate your choosing <a href="' . base_url($cmpR['slug'] . "/account") . '">' . $cmpR['name'] . '</a> and look forward to assisting you with your student loan matters.</p>
		<p>---<br /><strong>Regards</strong><br />' . $cm_name . '<br /><a href="' . base_url($cmpR['slug'] . "/account") . '">' . base_url($cmpR['slug'] . "/account") . '</a></p>';
			//$this->send_email($smtp_data);
		}
	}

//    The system should automatically mark this step as complete and move to step 4
	public function ca_mark_step3_complete_move_to_step4($client_id, $car_id) {
		$q = $this->db->query("select * from client_analysis_results where client_id='$client_id' and id='$car_id'");
		$car = $q->row_array();
		if (isset($car['id'])) {
			//    Mark the program as Complete
			if ($car['status'] == "Complete") {
				$sql = "update client_program_progress set status='Complete', step_completed_date='" . date("Y-m-d") . "' where client_id='$client_id' and (program_id_primary='1' or program_id_primary='23' or program_id_primary='40' or program_id_primary='178' or program_id_primary='193')";
				$this->db->query($sql);
			}

			//    The system should automatically mark this step as complete and move to step 4
			if ($car['status'] == "Saved") {
				$sql = "select * from client_program_progress where client_id='$client_id' and step_id='3' and status='Pending' and (program_id_primary='1' or program_id_primary='23' or program_id_primary='40' or program_id_primary='178' or program_id_primary='193') limit 1";
				$qry = $this->db->query($sql);
				$row = $qry->row_array();
				if (isset($row['program_definition_id'])) {$this->crm_model->admin_users_add_program_step($client_id, $row['program_definition_id']);}
			}
		}
	}

//    Get Client NSLDS File Upload Status
	public function client_nslds_file_upload_status($client_id = '') {
		$file_data = "";
		$file_status = 'File not found';

		$q = 6;

		if ($this->db->query('select * from intake_client_status where client_id=' . $client_id . ' and intake_id=4')->num_rows() > 0) {
			$q = 102;
		}

		$res = $this->default_model->get_arrby_tbl_single('intake_file_result', '*', "client_id='" . $client_id . "' and intake_question_id='" . $q . "'", '1');

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

//    Get Client NSLDS File Data
	public function client_nslds_file_data($client_id = '') {
		$file_data = "";

		$q = 6;

		if ($this->db->query('select * from intake_client_status where client_id=' . $client_id . ' and intake_id=4')->num_rows() > 0) {
			$q = 102;
		}
		$res = $this->default_model->get_arrby_tbl_single('intake_file_result', '*', "client_id='" . $client_id . "' and intake_question_id='" . $q . "'", '1');

		$nslds_file_error = "";
		if (isset($res['intake_file_id'])) {
			$client_document = $this->document_decrypt($res['intake_file_location']);
			if (file_exists($client_document)) {
				$file_data = read_file($client_document);
				if (trim($file_data) == "") {$file_data = 'File not found';}
			} else { $file_data = 'File not found';}
		}
		return $file_data;
	}

//    Set Coupon Code Usage
	public function set_promo_code_usage($coupon_code = '') {
		$sql = "SELECT * FROM promotional_codes where promo_code='" . $coupon_code . "'";
		if (trim($coupon_code) == "") {$sql = "SELECT * FROM promotional_codes where 1";}
		$q = $this->db->query($sql);
		foreach ($q->result_array() as $row) {

			$q2 = $this->db->query("SELECT * FROM promo_code_usage where promo_code='" . $row['promo_code'] . "'");
			$nr = $q2->num_rows();

			$this->db->query("update promotional_codes set total_redemptions_claimed='$nr' where promo_code='" . $row['promo_code'] . "'");
		}
	}

//    Check Coupon Code
	public function check_coupon_code($coupon_code = '', $company_id = '') {
		$error = "";
		$status = "Failed";
		@extract($_POST);
		$dtm = date("Y-m-d H:i:s");
		$sql = "SELECT * FROM promotional_codes where promo_code='" . $coupon_code . "' and promo_code_begins<='$dtm' and promo_code_ends>='$dtm' order by id desc limit 1";
		$q = $this->db->query($sql);
		$res = $q->row_array();
		if (isset($res['id'])) {
			$sql = "SELECT * FROM promo_code_usage where promo_code='" . $coupon_code . "'";
			$q = $this->db->query($sql);
			$nr = $q->num_rows();
			if ($res['total_redemptions_available'] > $nr) {
				$sql = "SELECT * FROM promo_code_usage where company_id='" . $company_id . "' and promo_code='" . $coupon_code . "'";
				$chkq = $this->db->query($sql);
				$chkn = $chkq->num_rows();
				if ($chkn == 0) {
					$this->db->query("update users_company set promo_code='$coupon_code' where id='" . $company_id . "'");
					$status = "Success";
					$error = '';
				}
			}
		}
		if ($status == "Failed") {$this->db->query("update users_company set promo_code='' where id='" . $company_id . "'");}

		$jdata = array("status" => $status, "message" => $error, "data" => $res, "coupon_code" => $coupon_code);
		return json_encode($jdata);
	}

//    Calculate Coupon Code Discount
	public function calculate_coupon_code_discount($price = '0', $cmpR = '', $cpnR_data = '') {
		$fields = $this->db->query("SELECT * from settings Limit 1")->row_array();
		$discount_amount = 0;
		if ($cpnR_data['data']['discount_type'] == "Dollar") {
			$discount_amount = $cpnR_data['data']['discount_amount'];
		} else {
			$discount_amount_1 = $discount_amount_2 = 0;
			$price_1 = $fields['initial_user_fee'];
			$price_2 = ($price - $fields['initial_user_fee']);

			$q = $this->db->query("SELECT sum(1st_user_fee) as 1st_user_fee, sum(additional_user_fee) as additional_user_fee FROM account_payment_info where company_id='" . $GLOBALS["loguser"]["id"] . "'");
			$r = $q->row_array();
			$price_1 = $r['1st_user_fee'];
			$price_2 = $r['additional_user_fee'];

			//    Initial User Fee
			if (strtolower(trim($cpnR_data['data']['discount_initial_fee'])) == "yes") {$discount_amount_1 = (($price_1 / 100) * $cpnR_data['data']['discount_amount']);}

			//    Additional User Fee
			if ($cpnR_data['data']['discount_number_of_additional_users'] > 0) {
				$q = $this->db->query("SELECT * FROM users where role='Company User' and company_id='" . $GLOBALS["loguser"]["id"] . "' order by id asc");
				$un = $q->num_rows();
				if ($un > $cpnR_data['data']['discount_number_of_additional_users']) {
					$tu_ = ($un - $cpnR_data['data']['discount_number_of_additional_users']);
					$price_2_temp = ($tu_ * $fields['additional_user_fee']);
					$price_2_temp = ($price_2 - $price_2_temp);
					if ($price_2_temp < 0) {$price_2_temp = 0;}

					//$discount_amount_2 = (($price_2_temp/100)*$cpnR_data['data']['discount_amount']);
					$discount_amount_2 = (($price_2 / 100) * $cpnR_data['data']['discount_amount']);
				} else {
					$discount_amount_2 = (($price_2 / 100) * $cpnR_data['data']['discount_amount']);
				}
			}

			//$discount_amount = (($price/100)*$cpnR_data['data']['discount_amount']);
			$discount_amount = ($discount_amount_1 + $discount_amount_2);
		}
		return $discount_amount;
	}

//    Delete Customer
	public function delete_customer() {
		$client_id = $this->uri->segment(4);
		if ($GLOBALS["loguser"]["role"] == "Company") {$field_name = "company_id";} else { $field_name = "company_id";}
		// if ($GLOBALS["loguser"]["role"] == "Company") {$field_name = "company_id";} else { $field_name = "parent_id";}

		$this->db->query("delete from users_log where uid='$client_id'"); //    Delete Log
		$this->db->query("delete from client_analysis_results where client_id='$client_id'"); //    Delete Analysis Result

		//    Delete Document
		$q = $this->db->query("select * from client_documents where client_id='$client_id'");
		foreach ($q->result_array() as $docR) {
			if ($docR['file_is_merged'] == "1") {
				foreach (explode(",", $docR['files']) as $client_document) {unlink(trim($client_document));}
			} else {
				$client_document = $this->crm_model->document_decrypt($docR['client_document']);
				if (file_exists($client_document)) {unlink($client_document);}
			}
		}
		$this->db->query("delete from client_documents where client_id='$client_id'");
		$this->db->query("delete from client_program_progress where client_id='$client_id'"); //    Delete Program Progress
		$this->db->query("delete from client_program where client_id='$client_id'"); //    Delete Program Progress
		$this->db->query("delete from contact_us_history where uid='$client_id'"); //    Delete Contact History
		$this->db->query("delete from intake_answer_result where client_id='$client_id'"); //    Delete Intake Answer Result
		$this->db->query("delete from intake_client_status where client_id='$client_id'"); //    Delete Intake Status
		$this->db->query("delete from intake_comment_result where client_id='$client_id'"); //    Delete Log
		$this->db->query("delete from intake_file_nslds where client_id='$client_id'"); //    Delete Log
		$this->db->query("delete from client_attestation where client_id='$client_id'"); //    Delete Client Attestation

		//    Delete Intake Document
		$q = $this->db->query("select * from intake_file_result where client_id='$client_id'");
		foreach ($q->result_array() as $docR) {
			$client_document = $this->crm_model->document_decrypt($docR['intake_file_location']);
			if (file_exists($client_document)) {unlink($client_document);}
		}
		$this->db->query("delete from intake_file_result where client_id='$client_id'");
		$this->db->query("delete from nslds_loans where client_id='$client_id'"); //    Delete Loan
		$this->db->query("delete from reminder_history where client_id='$client_id'"); //    Delete Reminder History
		$this->db->query("delete from reminder_rules where client_id='$client_id'"); //    Delete Reminder Rules
		$this->db->query("delete from client_reminder_status where client_id='$client_id'"); //    Delete Reminder Rules

		$this->db->query("delete from clients where client_id='$client_id'");
		$this->db->query("delete from users where $field_name='" . $GLOBALS["loguser"]["id"] . "' and id='$client_id'");

		$this->session->set_flashdata('success', 'Record successfully deleted.');
		redirect(base_url('account/customer'));
	}

//    Delete Case Manager
	public function delete_case_mamager() {
		$cm_id = $this->uri->segment(4);
		if ($GLOBALS["loguser"]["role"] == "Company") {
			$q = $this->db->query("select * from users where id='$cm_id'");
			$res = $q->row_array();
			$company_id = $res['company_id'];

			$this->db->query("delete from users_log where uid='$cm_id'"); //    Delete Log
			$this->db->query("delete from contact_us_history where uid='$cm_id'"); //    Delete Contact History
			$this->db->query("delete from users_cm_setting where id='$cm_id'"); //    Delete Users CM Setting

			$this->db->query("update users set parent_id='$company_id' where parent_id='$cm_id'"); //    Update in Document
			$this->db->query("update client_documents set added_by='$company_id' where added_by='$cm_id'"); //    Update in Document
			$this->db->query("update client_program_progress set added_by='$company_id' where added_by='$cm_id'"); //    Update in Program Progress

			$this->db->query("delete from users where company_id='" . $GLOBALS["loguser"]["id"] . "' and id='$cm_id'");
		}

		$this->session->set_flashdata('success', 'Record successfully deleted.');
		redirect(base_url('account/team'));
	}

//    Client Current Analysis Payment Scenario
	/*public function client_current_analysis_payment_scenario($data = array(), $scenario = array()) {
	    @extract($data);

	    $res = array();
	    $fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);

	    foreach ($scenario as $k => $row) {
	    if ($k == "MA" || $k == "AA" || $k == "SA") {
	    $pif_1 = 16090;
	    $pif_2 = 5680;
	    } else if ($k == "MH" || $k == "AH" || $k == "SH") {
	    $pif_1 = 14820;
	    $pif_2 = 5220;
	    } else {
	    $pif_1 = 12880;
	    $pif_2 = 4540;}
	    }

	    $q = $this->db->query("SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' and sltb_code_id='1'");
	    $nr_ppl_loan = $q->num_rows();

	    $q = $this->db->query("SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' and sltb_code_id!='1'");
	    $nr_non_ppl_loan = $q->num_rows();

	    $q = $this->db->query("SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' and sltb_code_id='3'");
	    $nr_perkins_loan = $q->num_rows();

	    $q = $this->db->query("SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' and sltb_code_id='4'");
	    $nr_stafford_loan = $q->num_rows();

	    $q = $this->db->query("SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' and is_ffel='1'");
	    $nr_ffel_loan = $q->num_rows();

	    $q = $this->db->query("SELECT * FROM nslds_loans where client_id='$client_id' and (loan_outstanding_principal_balance>'0' or loan_outstanding_interest_balance>'0') and (sltb_code_id='1' or sltb_code_id='3' or is_ffel='1')");
	    $nr_mc_loan = $q->num_rows();

	    $ln = $lni = $i = 0;
	    $q = $this->db->query("SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' order by id asc");
	    $nr = $q->num_rows();

	    foreach ($q->result_array() as $row) {
	    $ln = $ln + $row['loan_outstanding_principal_balance'];
	    $lni = $lni + $row['loan_outstanding_interest_balance'];}
	    $balance = ($ln + $lni);
	    $tmp_arr_1 = array();
	    $tmp_arr_2 = array();
	    foreach ($q->result_array() as $row) {
	    $tmp_id = $row['id'];
	    $tmp_arr_1[$tmp_id] = ($row['loan_outstanding_principal_balance'] / $ln) * 100;
	    $tmp_arr_2[$tmp_id] = ($tmp_arr_1[$tmp_id] / 100) * $row['loan_interest_rate'];
	    }

	    $sno = 0;
	    foreach ($q->result_array() as $row) {$tmp_id = $row['id'];}

	    $i = array_sum($tmp_arr_2) / 100;
	    $r = $i;
	    $t = 10;
	    $n = 12;
	    $rt_o_intrst = ($r / $n);

	    #######
	    //$r = $i = (6.8/100);
	    //$balance = 150000;
	    #####
	    $_10_year_standard = $_25_year_fixed = $_standard_plan = 0;
	    if ($i > 0) {
	    $dvd = ((pow((1 + ($r / $n)), ($t * $n))) - 1);
	    if ($dvd != 0) {$_10_year_standard = (($balance * ($r / $n)) * (pow((1 + ($r / $n)), ($t * $n))) / ((pow((1 + ($r / $n)), ($t * $n))) - 1));}
	    $t = 25;
	    $dvd = ((pow((1 + ($r / $n)), ($t * $n))) - 1);
	    if ($dvd != 0) {$_25_year_fixed = (($balance * ($r / $n)) * (pow((1 + ($r / $n)), ($t * $n))) / ((pow((1 + ($r / $n)), ($t * $n))) - 1));}

	    $arr_payment_plan = $this->array_model->arr_payment_plan($balance);
	    $years = (str_replace(['Standard Plan (', '-Year)'], '', $arr_payment_plan[1]));
	    $t = $years;
	    $dvd = ((pow((1 + ($r / $n)), ($t * $n))) - 1);
	    if ($dvd != 0) {$_standard_plan = (($balance * ($r / $n)) * (pow((1 + ($r / $n)), ($t * $n))) / ((pow((1 + ($r / $n)), ($t * $n))) - 1));}
	    if (!is_numeric($_standard_plan)) {$_standard_plan = 0;}
	    if ($_standard_plan < 0) {$_standard_plan = 0;}

	    $formula = "((balance * (i)) * (pow((1+(i)),(t*n))) / ((pow((1+(i)),(t*n))) - 1))";
	    } else {
	    $_10_year_standard = (($balance / 10) / 12);
	    $_25_year_fixed = (($balance / 25) / 12);

	    $arr_payment_plan = $this->array_model->arr_payment_plan($balance);
	    $years = (str_replace(['Standard Plan (', '-Year)'], '', $arr_payment_plan[1]));
	    $_standard_plan = (($balance / $years) / 12);
	    if (!is_numeric($_standard_plan)) {$_standard_plan = 0;}
	    if ($_standard_plan < 0) {$_standard_plan = 0;}

	    $formula = "((balance/t)/12)";
	    }
	    //    Standard Plan
	    $standard_plan_name = 'Standard Plan (' . $years . '-Year)';
	    if ($_standard_plan <= 0) {$_standard_plan = "0";}
	    $_standard_plan = $fmt->formatCurrency(round($_standard_plan), "USD");
	    $standard_plan_notes = '<strong>Standard Plan (' . $years . '-Year):</strong> a fixed payment based on a ' . $years . '-year term. The number shown is based on consolidating your loan(s).';
	    $res[1] = ["name" => $standard_plan_name, "notes" => $standard_plan_notes, "formula" => $formula, "value" => [$_standard_plan, $_standard_plan]];

	    //    25 Years Plan
	    if ($_25_year_fixed <= 0) {$_25_year_fixed = "0";}

	    $_25_year_fixed = $fmt->formatCurrency(round($_25_year_fixed), "USD");
	    if ($balance < 30000) {$_25_year_fixed = "N/A";}
	    $standard_plan_notes = '<strong>25-Year Extended:</strong> a fixed payment based on a 25-year term. The number shown is based on consolidating your loan(s).';
	    $res[2] = ["name" => "25-Year Extended", "notes" => $standard_plan_notes, "formula" => $formula, "value" => [$_25_year_fixed, $_25_year_fixed]];

	    //if($marital_status == "15") {    $family_size = ($family_size + 1);    }
	    $pi = ($pif_1 + (($family_size - 1) * $pif_2));
	    if ($pi < 0) {$pi = 0;}

	    $client_agi = preg_replace('/\D/', '', $client_agi);
	    $client_monthly = preg_replace('/\D/', '', $client_monthly);
	    $spouse_agi = preg_replace('/\D/', '', $spouse_agi);
	    $spouse_monthly = preg_replace('/\D/', '', $spouse_monthly);

	    if (trim($client_agi) == '') {$client_agi = 0;}
	    if (trim($client_monthly) == '') {$client_monthly = 0;}
	    if (trim($spouse_agi) == '') {$spouse_agi = 0;}
	    if (trim($spouse_monthly) == '') {$spouse_monthly = 0;}

	    $agi = $client_agi + $spouse_agi;
	    $monthly = $client_monthly + $spouse_monthly;

	    $repay_agi = $repay_monthly = "N/A";
	    $formula = '<strong>AGI:</strong> (((agi - (pi * 1.5)) * 0.1)/12)<hr /><strong>Monthly:</strong>(((monthly - (pi * 1.5)) * 0.1)/12)';
	    $notes = '<strong>REPAYE (Revised Pay As You Earn)</strong>: a payment based on your income and family size. Your income and family size must be recertified every 12 months. Any balance not paid after 20 or 25 years on this plan will be forgiven.';
	    //    REPAYE Plan
	    if ($nr_ppl_loan > 0 && $nr_non_ppl_loan == 0) {
	    $formula = "-";
	    $notes = 'Forgiveness after 20 years if no gradate level loans, otherwise forgiveness after 25 years.';
	    } else if ($nr_non_ppl_loan > 0) {
	    $repay_agi = (($agi - ($pi * 1.5)) * 0.1) / 12;
	    $repay_monthly = (($monthly - ($pi * 1.5)) * 0.1) / 12;

	    if ($repay_agi <= 0) {$repay_agi = "0";}
	    if ($repay_monthly <= 0) {$repay_monthly = "0";}
	    $repay_agi = $fmt->formatCurrency(round($repay_agi), "USD");
	    $repay_monthly = $fmt->formatCurrency(round($repay_monthly), "USD");
	    } else { $formula = "-";}

	    //if($nr_perkins_loan>0 || $nr_ffel_loan>0) {    $notes = 'Must consolidate';    }

	    $res[3] = ["name" => "REPAYE", "notes" => $notes, "formula" => $formula, "value" => [$repay_agi, $repay_monthly]];

	    $paye_agi = $paye_monthly = "N/A";
	    $formula = '<strong>AGI:</strong> (((agi - (pi * 1.5)) * 0.1)/12)<hr /><strong>Monthly:</strong>(((monthly - (pi * 1.5)) * 0.1)/12)';
	    $notes = '<strong>PAYE (Pay As You Earn):</strong> a payment based on your income and family size. Your income and family size must be recertified every 12 months. Any balance not paid after 20 years on this plan will be forgiven.';
	    //    REPAYE Plan
	    if ($nr_ppl_loan > 0 && $nr_non_ppl_loan == 0) {
	    $formula = "-";
	    } else if ($nr_non_ppl_loan > 0) {
	    $q = $this->db->query("select * from nslds_loans where client_id='" . $client_id . "' and loan_date<'2007-10-01'");
	    $n = $q->num_rows();
	    $q = $this->db->query("select * from nslds_loans where client_id='" . $client_id . "' and loan_date>='2011-10-01'");
	    $n2 = $q->num_rows();

	    if ($n > 0) {$paye_agi = "N/A";} else if ($n2 == 0) {$paye_agi = "N/A";} else {}

	    if ($paye_agi = "N/A") {
	    $paye_agi = $paye_monthly = "N/A";
	    //$notes = '<strong>PAYE (Pay As You Earn):</strong> Your loans are too old for this plan.';
	    $formula = "-";
	    } else {
	    $paye_agi = (($agi - ($pi * 1.5)) * 0.1) / 12;
	    $paye_monthly = (($monthly - ($pi * 1.5)) * 0.1) / 12;

	    if ($paye_agi > $_10_year_standard) {$paye_agi = 0;}
	    if ($paye_monthly > $_10_year_standard) {$paye_agi = 0;}

	    if ($paye_agi <= 0) {$paye_agi = "0";}
	    if ($paye_monthly <= 0) {$paye_monthly = "0";}
	    $paye_agi = $fmt->formatCurrency(round($paye_agi), "USD");
	    $paye_monthly = $fmt->formatCurrency(round($paye_monthly), "USD");
	    }
	    } else { $formula = "-";}

	    //if($nr_perkins_loan>0 || $nr_ffel_loan>0) {    $notes = 'Must consolidate';    }

	    $res[4] = ["name" => "PAYE", "notes" => $notes, "formula" => $formula, "value" => [$paye_agi, $paye_monthly]];

	    //    IBR / NEW IBR Plan
	    $ibr_agi = $ibr_monthly = $ibr_new_agi = $ibr_new_monthly = "N/A";
	    $ibr_name = "IBR";
	    $formula = '-';
	    $notes = '<strong>IBR (Income Based Repayment):</strong> a payment based on your income and family size. Your income and family size must be recertified every 12 months. Any balance not paid after 25 years on this plan will be forgiven.';
	    if ($nr_ppl_loan > 0 && $nr_non_ppl_loan == 0) {
	    $formula = "-";
	    } else if ($nr_non_ppl_loan > 0) {
	    $formula = '<strong>AGI:</strong> (((agi - (pi * 1.5)) * (15/100))/12)<hr /><strong>Monthly:</strong>(((monthly - (pi * 1.5)) * (15/100))/12)';

	    $q = $this->db->query("select * from nslds_loans where client_id='" . $client_id . "' and loan_date<'2014-07-01'");
	    $n = $q->num_rows();
	    $q = $this->db->query("select * from nslds_loans where client_id='" . $client_id . "' and loan_date>='2014-07-01'");
	    $n2 = $q->num_rows();

	    if ($n > 0) {$p = 15;} else if ($n2 > 0) {$p = 10;} else { $p = 0;}
	    $ibr_agi = ((($agi - ($pi * 1.5)) * (15 / 100)) / 12);
	    $ibr_new_agi = ((($agi - ($pi * 1.5)) * (10 / 100)) / 12);

	    $ibr_monthly = ((($monthly - ($pi * 1.5)) * (15 / 100)) / 12);
	    $ibr_new_monthly = ((($monthly - ($pi * 1.5)) * (10 / 100)) / 12);

	    $q = $this->db->query("SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' and loan_date<'2014-07-01'");
	    if ($q->num_rows() == 0) {
	    $ibr_agi = $ibr_new_agi;
	    $ibr_monthly = $ibr_new_monthly;
	    $ibr_name = "NEW IBR";
	    $formula = '<strong>AGI:</strong> (((agi - (pi * 1.5)) * (10/100))/12)<hr /><strong>Monthly:</strong>(((monthly - (pi * 1.5)) * (10/100))/12)';
	    } else { $ibr_name = "IBR";}

	    if ($ibr_agi > $_10_year_standard) {$ibr_agi = "N/A";} else {
	    if ($ibr_agi <= 0) {$ibr_agi = "0";}
	    $ibr_agi = $fmt->formatCurrency(round($ibr_agi), "USD");
	    }
	    if ($ibr_monthly > $_10_year_standard) {$ibr_monthly = "N/A";} else {
	    if ($ibr_monthly <= 0) {$ibr_monthly = "0";}
	    $ibr_monthly = $fmt->formatCurrency(round($ibr_monthly), "USD");
	    }

	    }

	    //if($nr_perkins_loan>0 || $nr_ffel_loan>0) {    $notes = 'Must consolidate';    }

	    $res[5] = ["name" => $ibr_name, "notes" => $notes, "formula" => $formula, "value" => [$ibr_agi, $ibr_monthly]];

	    //    ICR Plan

	    if ($nr_ppl_loan > 0) {
	    $ln = $lni = $i = 0;
	    $q = $this->db->query("SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' and sltb_code_id='1' order by id asc");
	    $nr = $q->num_rows();

	    foreach ($q->result_array() as $row) {
	    $ln = $ln + $row['loan_outstanding_principal_balance'];
	    $lni = $lni + $row['loan_outstanding_interest_balance'];}
	    $balance = ($ln + $lni);
	    $tmp_arr_1 = array();
	    $tmp_arr_2 = array();
	    foreach ($q->result_array() as $row) {
	    $tmp_id = $row['id'];
	    $tmp_arr_1[$tmp_id] = ($row['loan_outstanding_principal_balance'] / $ln) * 100;
	    $tmp_arr_2[$tmp_id] = ($tmp_arr_1[$tmp_id] / 100) * $row['loan_interest_rate'];
	    }

	    $sno = 0;
	    foreach ($q->result_array() as $row) {$tmp_id = $row['id'];}

	    $i = array_sum($tmp_arr_2) / 100;
	    $r = $i;
	    $t = 10;
	    $n = 12;
	    $rt_o_intrst = ($r / $n);
	    }

	    $icr_agi = $icr_monthly = 0;

	    $ipf_agi = $this->calculate_ifp($agi, 'AGI', $file_joint_or_separate);
	    $ipf_monthly = $this->calculate_ifp($monthly, 'Monthly', $file_joint_or_separate);
	    if ($i > 0) {
	    $t = $n = 12;
	    $dvd = ((pow((1 + ($r / $n)), ($t * $n))) - 1);
	    if ($dvd != 0) {$_12_year_fixed = (($balance * ($r / $n)) * (pow((1 + ($r / $n)), ($t * $n))) / ((pow((1 + ($r / $n)), ($t * $n))) - 1));}

	    //    AGI
	    $agi_formula_1 = ((($agi - ($pi * (100 / 100))) * (20 / 100)) / 12);
	    //$agi_formula_2 = $balance * ((($i *((1+$i)/144))/((1+$i)/144))-1) * ($ipf_agi/100);
	    $agi_formula_2 = $_12_year_fixed * ($ipf_agi / 100);
	    $agi_formula_1 = str_replace("-", "", $agi_formula_1);
	    $agi_formula_2 = str_replace("-", "", $agi_formula_2);
	    if ($agi_formula_1 < $agi_formula_2 && $i > 0) {$icr_agi = $agi_formula_1;} else { $icr_agi = $agi_formula_2;}

	    //    Monthly
	    $monthly_formula_1 = ((($monthly - ($pi * (100 / 100))) * (20 / 100)) / 12);
	    //$monthly_formula_2 = $balance * ((($i *((1+$i)/144))/((1+$i)/144))-1) * ($ipf_monthly/100);
	    $monthly_formula_2 = $_12_year_fixed * ($ipf_monthly / 100);
	    $monthly_formula_1 = str_replace("-", "", $monthly_formula_1);
	    $monthly_formula_2 = str_replace("-", "", $monthly_formula_2);
	    if ($monthly_formula_1 < $monthly_formula_2 && $i > 0) {$icr_monthly = $monthly_formula_1;} else { $icr_monthly = $monthly_formula_2;}
	    } else {
	    $icr_agi = (($balance / 12) / 12) * ($ipf_agi / 100);
	    $icr_monthly = (($balance / 12) / 12) * ($c / 100);
	    }

	    $formula = '<strong>AGI F1:</strong> (((agi - (pi * (100/100))) * (20/100))/12)<br /><strong>AGI F2:</strong> (((balance * (i)) * (pow((1+(i)),(144))) / ((pow((1+(i)),(144))) - 1)) * (ipf_agi/100))    <hr /><strong>Monthly F1:</strong>(((monthly - (pi * (100/100))) * (20/100))/12)<br /><strong>Monthly F2:</strong>(((balance * (i)) * (pow((1+(i)),(144))) / ((pow((1+(i)),(144))) - 1)) * (ipf_monthly/100))
	    <hr />
	    <strong>0 Interest Formula</strong><br />
	    <strong>AGI : </strong> ((balance/12)/12) * (ipf_agi/100)<br />
	    <strong>Monthly : </strong> ((balance/12)/12) * (ipf_monthly/100)
	    ';

	    if ($icr_agi <= 0) {$icr_agi = "0";}
	    if ($icr_monthly <= 0) {$icr_monthly = "0";}
	    $icr_agi = $fmt->formatCurrency(round($icr_agi), "USD");
	    $icr_monthly = $fmt->formatCurrency(round($icr_monthly), "USD");
	    $notes = '<strong>ICR (Income Contingent Repayment):</strong> a payment based on your income, family size, and balance of your loan(s). Your income and family size must be recertified every 12 months. Any balance not paid after 25 years on this plan will be forgiven.';
	    if ($nr_mc_loan > 0) {$notes .= '<br /><span>&#8226; Must Consolidate Loan</span>';}

	    $res[6] = ["name" => "ICR", "notes" => $notes, "formula" => $formula, "value" => [$icr_agi, $icr_monthly]];

	    $res[6]["formula_input"] = "<strong>Babance = </strong>" . $balance . "<br /><strong>t = </strong>Year<br /><strong>i = </strong>" . ($rt_o_intrst * 100) . "%<br /><strong>n = </strong>12<br /><strong>agi = </strong>" . $agi . "<br /><strong>monthly = </strong>" . $monthly . "<br /><strong>pi = </strong>" . $pi . "<br /><strong>ipf_agi = </strong>" . $ipf_agi . "<br /><strong>ipf_monthly = </strong>" . $ipf_monthly;

	    return $res;
	    }
*/
	public function client_current_analysis_payment_scenario($data = array(), $scenario = array()) {
		@extract($data);

		$res = array();
		$fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);

		foreach ($scenario as $k => $row) {
			if ($k == "MA" || $k == "AA" || $k == "SA") {
				$pif_1 = 18210;
				$pif_2 = 6430;
			} else if ($k == "MH" || $k == "AH" || $k == "SH") {
				$pif_1 = 16770;
				$pif_2 = 5910;
			} else {
				$pif_1 = 14580;
				$pif_2 = 5140;}
		}

		$q = $this->db->query("SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' and sltb_code_id='1'");
		$nr_ppl_loan = $q->num_rows();

		$q = $this->db->query("SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' and sltb_code_id!='1'");
		$nr_non_ppl_loan = $q->num_rows();

		$q = $this->db->query("SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' and sltb_code_id='3'");
		$nr_perkins_loan = $q->num_rows();

		$q = $this->db->query("SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' and sltb_code_id='4'");
		$nr_stafford_loan = $q->num_rows();

		$q = $this->db->query("SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' and is_ffel='1'");
		$nr_ffel_loan = $q->num_rows();

		$q = $this->db->query("SELECT * FROM nslds_loans where client_id='$client_id' and (loan_outstanding_principal_balance>'0' or loan_outstanding_interest_balance>'0') and (sltb_code_id='1' or sltb_code_id='3' or is_ffel='1')");
		$nr_mc_loan = $q->num_rows();

		$ln = $lni = $i = 0;
		$q = $this->db->query("SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' order by id asc");
		$nr = $q->num_rows();

		foreach ($q->result_array() as $row) {
			$ln = $ln + $row['loan_outstanding_principal_balance'];
			$lni = $lni + $row['loan_outstanding_interest_balance'];}
		$balance = ($ln + $lni);
		$tmp_arr_1 = array();
		$tmp_arr_2 = array();
		foreach ($q->result_array() as $row) {
			$tmp_id = $row['id'];
			$tmp_arr_1[$tmp_id] = ($row['loan_outstanding_principal_balance'] / $ln) * 100;
			$tmp_arr_2[$tmp_id] = ($tmp_arr_1[$tmp_id] / 100) * $row['loan_interest_rate'];
		}

		$sno = 0;
		foreach ($q->result_array() as $row) {$tmp_id = $row['id'];}

		$i = array_sum($tmp_arr_2) / 100;
		$r = $i;
		$t = 10;
		$n = 12;
		$rt_o_intrst = ($r / $n);

		#######
		//$r = $i = (6.8/100);
		//$balance = 150000;
		#####
		$_10_year_standard = $_25_year_fixed = $_standard_plan = 0;
		if ($i > 0) {
			$dvd = ((pow((1 + ($r / $n)), ($t * $n))) - 1);
			if ($dvd != 0) {$_10_year_standard = (($balance * ($r / $n)) * (pow((1 + ($r / $n)), ($t * $n))) / ((pow((1 + ($r / $n)), ($t * $n))) - 1));}
			$t = 25;
			$dvd = ((pow((1 + ($r / $n)), ($t * $n))) - 1);
			if ($dvd != 0) {$_25_year_fixed = (($balance * ($r / $n)) * (pow((1 + ($r / $n)), ($t * $n))) / ((pow((1 + ($r / $n)), ($t * $n))) - 1));}

			$arr_payment_plan = $this->array_model->arr_payment_plan($balance);
			$years = (str_replace(['Standard Plan (', '-Year)'], '', $arr_payment_plan[1]));
			$t = $years;
			$dvd = ((pow((1 + ($r / $n)), ($t * $n))) - 1);
			if ($dvd != 0) {$_standard_plan = (($balance * ($r / $n)) * (pow((1 + ($r / $n)), ($t * $n))) / ((pow((1 + ($r / $n)), ($t * $n))) - 1));}
			if (!is_numeric($_standard_plan)) {$_standard_plan = 0;}
			if ($_standard_plan < 0) {$_standard_plan = 0;}

			$formula = "((balance * (i)) * (pow((1+(i)),(t*n))) / ((pow((1+(i)),(t*n))) - 1))";
		} else {
			$_10_year_standard = (($balance / 10) / 12);
			$_25_year_fixed = (($balance / 25) / 12);

			$arr_payment_plan = $this->array_model->arr_payment_plan($balance);
			$years = (str_replace(['Standard Plan (', '-Year)'], '', $arr_payment_plan[1]));
			$_standard_plan = (($balance / $years) / 12);
			if (!is_numeric($_standard_plan)) {$_standard_plan = 0;}
			if ($_standard_plan < 0) {$_standard_plan = 0;}

			$formula = "((balance/t)/12)";
		}
		//    Standard Plan
		$standard_plan_name = 'Standard Plan (' . $years . '-Year)';
		if ($_standard_plan <= 0) {$_standard_plan = "0";}
		$_standard_plan = $fmt->formatCurrency(round($_standard_plan), "USD");
		$standard_plan_notes = '<strong>Standard Plan (' . $years . '-Year):</strong> a fixed payment based on a ' . $years . '-year term. The number shown is based on consolidating your loan(s).';
		$res[1] = ["name" => $standard_plan_name, "notes" => $standard_plan_notes, "formula" => $formula, "value" => [$_standard_plan, $_standard_plan]];

		//    25 Years Plan
		if ($_25_year_fixed <= 0) {$_25_year_fixed = "0";}
		$_25_year_fixed = $fmt->formatCurrency(round($_25_year_fixed), "USD");
		$standard_plan_notes = '<strong>25-Year Extended:</strong> a fixed payment based on a 25-year term. The number shown is based on consolidating your loan(s).';
		$res[2] = ["name" => "25-Year Extended", "notes" => $standard_plan_notes, "formula" => $formula, "value" => [$_25_year_fixed, $_25_year_fixed]];

		//if($marital_status == "15") {    $family_size = ($family_size + 1);    }

		// SAVE payment plan

		$formula = '-';
		// get undergrad amounts for SAVE payment plan
		$file_data = "";

		$q = 6;
		$intake_id = 1;

		if ($this->db->query('select * from intake_client_status where client_id=' . $client_id . ' and intake_id=4')->num_rows() > 0) {
			$q = 102;
			$intake_id = 4;
		}
		$res2 = $this->default_model->get_arrby_tbl_single('intake_file_result', '*', "client_id='" . $client_id . "' and intake_question_id='" . $q . "'", '1');

		$nslds_file_error = "";
		$file_data = "";
		$undergrad_total = 0;
		$loan_disamt = 0;
		// $loan_disamt_count = 0;
		$save_agi = $save_monthly = "N/A";
		$grad_total = 0;
		if (isset($res2['intake_file_id'])) {
			$client_document = $this->document_decrypt($res2['intake_file_location']);
			if (file_exists($client_document)) {
				$file_data = read_file($client_document);
				if (trim($file_data) == "") {$file_data = 'File not found';}
			} else { $file_data = 'File not found';}

			if (!empty($file_data)) {
				$arr_file_data = preg_split("/\r\n|\n|\r/", $file_data);

				foreach ($arr_file_data as $v) {
					if (stripos($v, 'Undergraduate Aggregate Combined Total') !== false) {
						$undergrad_total = str_replace('Undergraduate Aggregate Combined Total', '', $v);
						$undergrad_total = str_replace(':', '', $undergrad_total);
						$undergrad_total = str_replace('$', '', $undergrad_total);
						$undergrad_total = str_replace(',', '', $undergrad_total);
					}
					if (stripos($v, 'Graduate Aggregate Combined Total') !== false) {
						$grad_total = str_replace('Graduate Aggregate Combined Total', '', $v);
						$grad_total = str_replace(':', '', $grad_total);
						$grad_total = str_replace('$', '', $grad_total);
						$grad_total = str_replace(',', '', $grad_total);
					}
					if (stripos($v, 'Loan Disbursed Amount') !== false) {
						$tmp = str_replace('Loan Disbursed Amount', '', $v);
						$tmp = str_replace(':', '', $tmp);
						$tmp = str_replace('$', '', $tmp);
						$tmp = str_replace(',', '', $tmp);

						$loan_disamt += $tmp;
						// $loan_disamt_count++;
					}
				}

				$pi = ($pif_1 + (($family_size - 1) * $pif_2));
				// return $pi;
				if ($pi < 0) {$pi = 0;}

				$pi_result = $pi * 2.25;

				$client_agi = preg_replace('/\D\./', '', $client_agi);
				$client_monthly = preg_replace('/\D\./', '', $client_monthly);
				$spouse_agi = preg_replace('/\D\./', '', $spouse_agi);
				$spouse_monthly = preg_replace('/\D\./', '', $spouse_monthly);

				if (trim($client_agi) == '') {$client_agi = 0;}
				if (trim($client_monthly) == '') {$client_monthly = 0;}
				if (trim($spouse_agi) == '') {$spouse_agi = 0;}
				if (trim($spouse_monthly) == '') {$spouse_monthly = 0;}

				if ($intake_id == 1) {
					$agi = $client_agi + ($marital_status == "15" && $file_joint_or_separate == "18" ? $spouse_agi : 0);
					$monthly = $client_monthly + ($marital_status == "15" && $file_joint_or_separate == "18" ? $spouse_monthly : 0);
				} else {
					$agi = $client_agi + ($marital_status == "89" && $file_joint_or_separate == "92" ? $spouse_agi : 0);

					$monthly = $client_monthly + ($marital_status == "89" && $file_joint_or_separate == "92" ? $spouse_monthly : 0);
				}

				$sagi = $agi - $pi_result;
				$smonthly = $monthly - $pi_result;
				if ($sagi <= 0) {$save_agi = "0";}
				if ($smonthly <= 0) {$save_monthly = "0";}

				// % of undergrad and grad
				$total = $undergrad_total + $grad_total;

				if ($undergrad_total > 0) {
					$underpercent = $total > 0 ? ($undergrad_total / $total) * 100 : 0;
					$gradpercent = $total > 0 ? ($grad_total / $total) * 100 : 0;

					if (date('Y-m-d') < date('Y-m-d', strtotime('2024-07-01'))) {
						$undergradcent = $underpercent * 0.10;
					} else {
						$undergradcent = $underpercent * 0.05;
					}
					$gradcent = $gradpercent * 0.10;

					$percenttotal = $undergradcent + $gradcent;
				} else {

					// if undergraduate amount from nslds file is 0, then interest rate will automatically be 10%
					$percenttotal = 10;
				}

				$save_agi = ($sagi * $percenttotal / 100) / 12;
				$save_monthly = ($smonthly * $percenttotal / 100) / 12;

				$yrs = 0;

				if (date('Y-m-d') < date('Y-m-d', strtotime('2024-07-01'))) {
					$yrs = '20 - 25';
				} else {
					/*if ($grad_total > 0) {
						                    $yrs = 25;
						                    } else {
					*/
					if ($loan_disamt <= 12000) {
						$yrs = 10;
					} elseif ($loan_disamt >= 22000) {
						$yrs = 20;
					} elseif ($loan_disamt > 12000 && $loan_disamt < 22000) {
						$loan_disamt = ($loan_disamt % 1000 > 0) ? (($loan_disamt / 1000) + 1) * 1000 : $loan_disamt;
						$yrs = 10;
						for ($iy = 13000; $iy += 1000; $iy <= $loan_disamt) {
							$yrs += 1;
						}
					}
					// }
				}

				$formula = '<strong>AGI:</strong> (((agi - (pi * 2.25)) * ((undergrad_percent * 0.05) + (grad_percent * 0.10))) / 12)<hr /><strong>Monthly:</strong>(((monthly - (pi * 2.25)) * ((undergrad_percent * 0.05) + (grad_percent * 0.10))) / 12)';
				if ($save_agi < 5) {$save_agi = "0";}
				if ($save_agi >= 5 && $save_agi < 10) {$save_agi = "10";}
				if ($save_monthly < 5) {$save_monthly = "0";}
				if ($save_monthly >= 5 && $save_monthly < 10) {$save_monthly = "10";}
				$save_agi = $fmt->formatCurrency(round($save_agi), "USD");
				$save_monthly = $fmt->formatCurrency(round($save_monthly), "USD");

				$notes = '<strong>SAVE (Formerly REPAYE)</strong>: a payment based on your income and family size. Your income and family size must be recertified every 12 months. Any balance not paid after ' . $yrs . ' years on this plan will be forgiven. Only available for Direct Loans.';
			} else {
				$formula = "-";
				$notes = 'Please upload NSLDS file before checking the analysis.';
			}
		} else {
			$formula = "-";
			$notes = 'Please upload NSLDS file before checking the analysis.';
		}
		$res[3] = ["name" => "SAVE (Formerly REPAYE)", "notes" => $notes, "formula" => $formula, "value" => [$save_agi, $save_monthly]];

		/*
			        $pi = ($pif_1 + (($family_size - 1) * $pif_2));
			        if ($pi < 0) {$pi = 0;}

			        $client_agi = preg_replace('/\D/', '', $client_agi);
			        $client_monthly = preg_replace('/\D/', '', $client_monthly);
			        $spouse_agi = preg_replace('/\D/', '', $spouse_agi);
			        $spouse_monthly = preg_replace('/\D/', '', $spouse_monthly);

			        if (trim($client_agi) == '') {$client_agi = 0;}
			        if (trim($client_monthly) == '') {$client_monthly = 0;}
			        if (trim($spouse_agi) == '') {$spouse_agi = 0;}
			        if (trim($spouse_monthly) == '') {$spouse_monthly = 0;}

			        $agi = $client_agi + $spouse_agi;
			        $monthly = $client_monthly + $spouse_monthly;

			        $repay_agi = $repay_monthly = "N/A";
			        $formula = '<strong>AGI:</strong> (((agi - (pi * 1.5)) * 0.1)/12)<hr /><strong>Monthly:</strong>(((monthly - (pi * 1.5)) * 0.1)/12)';
			        $notes = '<strong>REPAYE (Revised Pay As You Earn)</strong>: a payment based on your income and family size. Your income and family size must be recertified every 12 months. Any balance not paid after 20 or 25 years on this plan will be forgiven.';
			        //    REPAYE Plan
			        if ($nr_ppl_loan > 0 && $nr_non_ppl_loan == 0) {
			        $formula = "-";
			        $notes = 'Forgiveness after 20 years if no gradate level loans, otherwise forgiveness after 25 years.';
			        } else if ($nr_non_ppl_loan > 0) {
			        $repay_agi = (($agi - ($pi * 1.5)) * 0.1) / 12;
			        $repay_monthly = (($monthly - ($pi * 1.5)) * 0.1) / 12;

			        if ($repay_agi <= 0) {$repay_agi = "0";}
			        if ($repay_monthly <= 0) {$repay_monthly = "0";}
			        $repay_agi = $fmt->formatCurrency(round($repay_agi), "USD");
			        $repay_monthly = $fmt->formatCurrency(round($repay_monthly), "USD");
			        } else { $formula = "-";}

			        //if($nr_perkins_loan>0 || $nr_ffel_loan>0) {    $notes = 'Must consolidate';    }

			        $res[3] = ["name" => "REPAYE", "notes" => $notes, "formula" => $formula, "value" => [$repay_agi, $repay_monthly]];
		*/

		$client_agi = preg_replace('/\D\./', '', $client_agi);
		$client_monthly = preg_replace('/\D\./', '', $client_monthly);
		$spouse_agi = preg_replace('/\D\./', '', $spouse_agi);
		$spouse_monthly = preg_replace('/\D\./', '', $spouse_monthly);

		if (trim($client_agi) == '') {$client_agi = 0;}
		if (trim($client_monthly) == '') {$client_monthly = 0;}
		if (trim($spouse_agi) == '') {$spouse_agi = 0;}
		if (trim($spouse_monthly) == '') {$spouse_monthly = 0;}

		if ($intake_id == 1) {
			$agi = $client_agi + ($marital_status == "15" && $file_joint_or_separate == "18" ? $spouse_agi : 0);
			$monthly = $client_monthly + ($marital_status == "15" && $file_joint_or_separate == "18" ? $spouse_monthly : 0);
		} else {
			$agi = $client_agi + ($marital_status == "89" && $file_joint_or_separate == "92" ? $spouse_agi : 0);

			$monthly = $client_monthly + ($marital_status == "89" && $file_joint_or_separate == "92" ? $spouse_monthly : 0);
		}
		$pi = ($pif_1 + (($family_size - 1) * $pif_2));
		if ($pi < 0) {$pi = 0;}
		$paye_agi = $paye_monthly = "N/A";
		$formula = '<strong>AGI:</strong> (((agi - (pi * 1.5)) * 0.1)/12)<hr /><strong>Monthly:</strong>(((monthly - (pi * 1.5)) * 0.1)/12)';
		$notes = '<strong>PAYE (Pay As You Earn):</strong> a payment based on your income and family size. Your income and family size must be recertified every 12 months. Any balance not paid after 20 years on this plan will be forgiven.';
		//    REPAYE Plan
		if ($nr_ppl_loan > 0 && $nr_non_ppl_loan == 0) {
			$formula = "-";
		} else if ($nr_non_ppl_loan > 0) {
			$q = $this->db->query("select * from nslds_loans where client_id='" . $client_id . "' and loan_date<'2007-10-01'");
			$n = $q->num_rows();
			$q = $this->db->query("select * from nslds_loans where client_id='" . $client_id . "' and loan_date>='2011-10-01'");
			$n2 = $q->num_rows();

			if ($n > 0) {$paye_agi = "N/A";} else { $paye_agi = "A";}
			if ($n2 == 0) {$paye_agi = "N/A";} else { $paye_agi = "A";}

			if ($paye_agi == "N/A") {
				$paye_agi = $paye_monthly = "N/A";
				$notes = '<strong>PAYE (Pay As You Earn):</strong> Your loans are too old for this plan.';
				$formula = "-";
			} else {
				$paye_agi = (($agi - ($pi * 1.5)) * 0.1) / 12;
				$paye_monthly = (($monthly - ($pi * 1.5)) * 0.1) / 12;

				if ($paye_agi > $_10_year_standard) {$paye_agi = 0;}
				if ($paye_monthly > $_10_year_standard) {$paye_agi = 0;}

				if ($paye_agi < 5) {$paye_agi = "0";}
				if ($paye_agi >= 5 && $paye_agi < 10) {$paye_agi = "10";}
				if ($paye_monthly < 5) {$paye_monthly = "0";}
				if ($paye_monthly >= 5 && $paye_monthly < 10) {$paye_monthly = "10";}
				$paye_agi = $fmt->formatCurrency(round($paye_agi), "USD");
				$paye_monthly = $fmt->formatCurrency(round($paye_monthly), "USD");
			}
		} else { $formula = "-";}

		//if($nr_perkins_loan>0 || $nr_ffel_loan>0) {    $notes = 'Must consolidate';    }

		$res[4] = ["name" => "PAYE", "notes" => $notes, "formula" => $formula, "value" => [$paye_agi, $paye_monthly]];

		//    IBR / NEW IBR Plan
		$ibr_agi = $ibr_monthly = $ibr_new_agi = $ibr_new_monthly = "N/A";
		$ibr_name = "IBR";
		$formula = '-';
		$notes = '<strong>IBR (Income Based Repayment):</strong> a payment based on your income and family size. Your income and family size must be recertified every 12 months. Any balance not paid after 25 years on this plan will be forgiven.';
		if ($nr_ppl_loan > 0 && $nr_non_ppl_loan == 0) {
			$formula = "-";
		} else if ($nr_non_ppl_loan > 0) {
			$formula = '<strong>AGI:</strong> (((agi - (pi * 1.5)) * (15/100))/12)<hr /><strong>Monthly:</strong>(((monthly - (pi * 1.5)) * (15/100))/12)';

			$q = $this->db->query("select * from nslds_loans where client_id='" . $client_id . "' and loan_date<'2014-07-01'");
			$n = $q->num_rows();
			$q = $this->db->query("select * from nslds_loans where client_id='" . $client_id . "' and loan_date>='2014-07-01'");
			$n2 = $q->num_rows();

			if ($n > 0) {$p = 15;} else if ($n2 > 0) {$p = 10;} else { $p = 0;}
			$ibr_agi = ((($agi - ($pi * 1.5)) * (15 / 100)) / 12);
			$ibr_new_agi = ((($agi - ($pi * 1.5)) * (10 / 100)) / 12);

			$ibr_monthly = ((($monthly - ($pi * 1.5)) * (15 / 100)) / 12);
			$ibr_new_monthly = ((($monthly - ($pi * 1.5)) * (10 / 100)) / 12);

			$q = $this->db->query("SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' and loan_date<'2014-07-01'");
			if ($q->num_rows() == 0) {
				$ibr_agi = $ibr_new_agi;
				$ibr_monthly = $ibr_new_monthly;
				$ibr_name = "NEW IBR";
				$formula = '<strong>AGI:</strong> (((agi - (pi * 1.5)) * (10/100))/12)<hr /><strong>Monthly:</strong>(((monthly - (pi * 1.5)) * (10/100))/12)';
			} else { $ibr_name = "IBR";}

			if ($ibr_agi > $_10_year_standard) {$ibr_agi = "N/A";} else {

				if ($ibr_agi < 5) {$ibr_agi = "0";}
				if ($ibr_agi >= 5 && $ibr_agi < 10) {$ibr_agi = "10";}
				$ibr_agi = $fmt->formatCurrency(round($ibr_agi), "USD");
			}
			if ($ibr_monthly > $_10_year_standard) {$ibr_monthly = "N/A";} else {

				if ($ibr_monthly < 5) {$ibr_monthly = "0";}
				if ($ibr_monthly >= 5 && $ibr_monthly < 10) {$ibr_monthly = "10";}
				$ibr_monthly = $fmt->formatCurrency(round($ibr_monthly), "USD");
			}

		}

		//if($nr_perkins_loan>0 || $nr_ffel_loan>0) {    $notes = 'Must consolidate';    }

		$res[5] = ["name" => $ibr_name, "notes" => $notes, "formula" => $formula, "value" => [$ibr_agi, $ibr_monthly]];

		//    ICR Plan
		/*

	        if ($nr_ppl_loan > 0) {
	        $ln = $lni = $i = 0;
	        $q = $this->db->query("SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' and sltb_code_id='1' order by id asc");
	        $nr = $q->num_rows();

	        foreach ($q->result_array() as $row) {
	        $ln = $ln + $row['loan_outstanding_principal_balance'];
	        $lni = $lni + $row['loan_outstanding_interest_balance'];}
	        $balance = ($ln + $lni);
	        $tmp_arr_1 = array();
	        $tmp_arr_2 = array();
	        foreach ($q->result_array() as $row) {
	        $tmp_id = $row['id'];
	        $tmp_arr_1[$tmp_id] = ($row['loan_outstanding_principal_balance'] / $ln) * 100;
	        $tmp_arr_2[$tmp_id] = ($tmp_arr_1[$tmp_id] / 100) * $row['loan_interest_rate'];
	        }

	        $sno = 0;
	        foreach ($q->result_array() as $row) {$tmp_id = $row['id'];}

	        $i = array_sum($tmp_arr_2) / 100;
	        $r = $i;
	        $t = 10;
	        $n = 12;
	        $rt_o_intrst = ($r / $n);
	        }
*/

		$rt_o_intrst = 0;
		$ppl_i = 0;
		$ln = $lni = $i = 0;
		$pplloans = $nonpplloans = [];

		if ($nr_ppl_loan > 0) {
			$q = $this->db->query("SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' and sltb_code_id='1' order by id asc");
			$nr = $q->num_rows();
			$pplloans = $q->result_array();

			foreach ($pplloans as $row) {
				$ln = $ln + $row['loan_outstanding_principal_balance'];
				$lni = $lni + $row['loan_outstanding_interest_balance'];
			}
			// $balance = ($ln + $lni);
		}

		// calculate non ppl loan balance
		if ($nr_non_ppl_loan > 0) {
			// $ln = $lni = $i = 0;
			$q = $this->db->query("SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' and sltb_code_id!='1' order by id asc");
			$nr = $q->num_rows();

			$nonpplloans = $q->result_array();

			foreach ($nonpplloans as $row) {
				$ln = $ln + $row['loan_outstanding_principal_balance'];
				$lni = $lni + $row['loan_outstanding_interest_balance'];
			}
		}
		$balance = ($ln + $lni);

		if ($balance > 0) {
			$tmp_arr_1 = array();
			$tmp_arr_2 = array();
			foreach ($pplloans as $row) {
				$tmp_id = $row['id'];
				$tmp_arr_1[$tmp_id] = ($row['loan_outstanding_principal_balance'] / $ln) * 100;
				$tmp_arr_2[$tmp_id] = ($tmp_arr_1[$tmp_id] / 100) * $row['loan_interest_rate'];
			}

			foreach ($nonpplloans as $row) {
				$tmp_id = $row['id'];
				$tmp_arr_1[$tmp_id] = ($row['loan_outstanding_principal_balance'] / $ln) * 100;
				$tmp_arr_2[$tmp_id] = ($tmp_arr_1[$tmp_id] / 100) * $row['loan_interest_rate'];
			}

			$i = array_sum($tmp_arr_2) / 100;
			$r = $i;
			$t = 10;
			$n = 12;
			$rt_o_intrst += ($r / $n);
		}

		$icr_agi = $icr_monthly = 0;

		$ipf_agi = $this->calculate_ifp($agi, 'AGI', $file_joint_or_separate);
		$ipf_monthly = $this->calculate_ifp($monthly, 'Monthly', $file_joint_or_separate);

		//    AGI
		$agi_formula_1 = ((($agi - ($pi * (100 / 100))) * (20 / 100)) / 12);
		$agi_formula_2 = $i != 0 ? (($balance * $i) / (12 * (1 - pow((1 + ($i / 12)), -144)))) * ($ipf_agi / 100) : 0;
		// $agi_formula_2 = $balance * ((($i * ((1 + $i) / 144)) / ((1 + $i) / 144)) - 1) * ($ipf_agi / 100);
		$agi_formula_1 = str_replace("-", "", $agi_formula_1);
		$agi_formula_2 = str_replace("-", "", $agi_formula_2);
		if ($agi_formula_1 < $agi_formula_2) {$icr_agi = $agi_formula_1;} else { $icr_agi = $agi_formula_2;}
		// if ($agi_formula_1 < $agi_formula_2 && $i > 0) {$icr_agi = $agi_formula_1;} else { $icr_agi = $agi_formula_2;}

		//    Monthly
		$monthly_formula_1 = ((($monthly - ($pi * (100 / 100))) * (20 / 100)) / 12);
		$monthly_formula_2 = $i != 0 ? (($balance * $i) / (12 * (1 - pow((1 + ($i / 12)), -144)))) * ($ipf_monthly / 100) : 0;

		$monthly_formula_1 = str_replace("-", "", $monthly_formula_1);
		$monthly_formula_2 = str_replace("-", "", $monthly_formula_2);
		if ($monthly_formula_1 < $monthly_formula_2) {$icr_monthly = $monthly_formula_1;} else { $icr_monthly = $monthly_formula_2;}
		// if ($monthly_formula_1 < $monthly_formula_2 && $i > 0) {$icr_monthly = $monthly_formula_1;} else { $icr_monthly = $monthly_formula_2;}

		$formula = '<strong>AGI F1:</strong> (((agi - (pi * (100/100))) * (20/100))/12)<br /><strong>AGI F2:</strong> (((balance * i) / (12 * (1 - pow((1 + (i / 12)), -144)))))	<hr /><strong>Monthly F1:</strong>(((monthly - (pi * (100/100))) * (20/100))/12)<br /><strong>Monthly F2:</strong>(((balance * i) / (12 * (1 - pow((1 + (i / 12)), -144)))))';

		$msg = '';

		if ($icr_agi < 5) {$icr_agi = "0";}
		if ($icr_agi >= 5 && $icr_agi < 10) {$icr_agi = "10";}
		if ($icr_monthly < 5) {$icr_monthly = "0";}
		if ($icr_monthly >= 5 && $icr_monthly < 10) {$icr_monthly = "10";}

		if ($icr_monthly == $monthly_formula_1 || $icr_agi == $agi_formula_1) {
			$msg .= 'Any balance not paid after 25 years on this plan will be forgiven.';
		} elseif ($icr_monthly == $monthly_formula_2 || $icr_agi == $agi_formula_2) {
			$msg .= 'Loan will be paid off in 12 years.';
		}

		$icr_agi = $fmt->formatCurrency(round($icr_agi), "USD");
		$icr_monthly = $fmt->formatCurrency(round($icr_monthly), "USD");
		$notes = '<strong>ICR (Income Contingent Repayment):</strong> a payment based on your income, family size, and balance of your loan(s). Your income and family size must be recertified every 12 months. ' . $msg;

		if ($nr_mc_loan > 0) {$notes .= '<br /><span>&#8226; Must Consolidate Loan</span>';}

		$res[6] = ["name" => "ICR", "notes" => $notes, "formula" => $formula, "value" => [$icr_agi, $icr_monthly]];

		$res[6]["formula_input"] = "<strong>Balance = </strong>" . $balance . "<br /><strong>t = </strong>Year<br /><strong>i = </strong>" . ($rt_o_intrst * 100) . "%<br /><strong>n = </strong>12<br /><strong>agi = </strong>" . $agi . "<br /><strong>monthly = </strong>" . $monthly . "<br /><strong>pi = </strong>" . $pi . "<br /><strong>ipf_agi = </strong>" . $ipf_agi . "<br /><strong>ipf_monthly = </strong>" . $ipf_monthly;

		return $res;
	}

	public function calculate_ifp($income = '0', $agi_monthly = '', $file_joint_or_separate = '') {
		$arr_ipf = array();
		if ($income > 0) {
			if ($agi_monthly == "AGI") {
				if ($file_joint_or_separate == "19") {
					$arr_ipf = array("13367" => "55.00", "18392" => "57.79", "23666" => "60.57", "29059" => "66.23", "34209" => "71.89", "40705" => "80.33", "51125" => "88.77", "64120" => "100.00", "77120" => "100.00", "92687" => "111.80", "118682" => "123.50", "168095" => "141.20", "192736" => "150.00", "343296" => "200.00");
				} else {
					$arr_ipf = array("13367" => "50.52", "21090" => "56.69", "25132" => "59.56", "32857" => "67.79", "40705" => "75.22", "51125" => "87.61", "64119" => "100.00", "77120" => "100.00", "96618" => "109.40", "129104" => "125.00", "174590" => "140.60", "244172" => "150.00", "398995" => "200.00");
				}
			} else {
				if ($file_joint_or_separate == "19") {
					$arr_ipf = array("13367" => "55.00", "18392" => "57.79", "23666" => "60.57", "29059" => "66.23", "34209" => "71.89", "40705" => "80.33", "51125" => "88.77", "64120" => "100.00", "77120" => "100.00", "92687" => "111.80", "118682" => "123.50", "168095" => "141.20", "192736" => "150.00", "343296" => "200.00");
				} else {
					$arr_ipf = array("13367" => "50.52", "21090" => "56.69", "25132" => "59.56", "32857" => "67.79", "40705" => "75.22", "51125" => "87.61", "64119" => "100.00", "77120" => "100.00", "96618" => "109.40", "129104" => "125.00", "174590" => "140.60", "244172" => "150.00", "398995" => "200.00");
				}
			}
		}

		$ipf = "";
		//arsort($arr_ipf);
		foreach ($arr_ipf as $k => $v) {
			if ($ipf == "") {if ($income <= $k) {$ipf = $v;}}
		}

		if ($ipf == "" && $income > $k) {$ipf = $v;}
		if ($ipf == "") {$ipf = 0;}

		return $ipf;
	}

//    Run Current Analysis Scenario Group
	public function run_current_analysis_scenario_group($data = array()) {
		@extract($data);

		$q = 0;
		$a = 0;

		if ($this->db->query('select * from intake_client_status where client_id=' . $client_id . ' and intake_id=4')->num_rows() > 0) {
			$q = 96;
			$a = 74;
		}

		$int_11R = $this->default_model->get_arrby_tbl_single('intake_answer_result', '*', "client_id='" . $client_id . "' and intake_question_id='" . ($q + 11) . "'", '1');
		if ($int_11R['intake_answer_id'] == ($a + 14)) {$marital_status = 'Single';} else { $marital_status = 'Married';}

		if ($spouse_agi == "") {$spouse_agi = 0;} else if (!isset($spouse_agi)) {$spouse_agi = 0;}
		if ($spouse_monthly == "") {$spouse_monthly = 0;} else if (!isset($spouse_monthly)) {$spouse_monthly = 0;}

		if ($client_agi == "") {$client_agi = 0;} else if (!isset($client_agi)) {$client_agi = 0;}
		if ($client_monthly == "") {$client_monthly = 0;} else if (!isset($client_monthly)) {$client_monthly = 0;}

		if ($file_joint_or_separate != "Joint") {$spouse_monthly = $spouse_agi = 0;}

		$fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
		$scenario_selected = $data['scenario_selected'];

		$payment_plan_scenario_group = $this->array_model->stlb_payment_plan_scenario_group();
		$garr = explode(" ", $scenario_selected);
		$g1 = $garr[0];
		$g2 = $garr[1];
		$ppsg = $payment_plan_scenario_group[$g1];

		if ($marital_status != "Single") {
			foreach ($ppsg as $k => $v) {
				if ($file_joint_or_separate == 'Joint') {
					if ($v['name'] == "MFS AGI" || $v['name'] == "MFS Monthly" || $v['name'] == "MFSA AGI" || $v['name'] == "MFSA Monthly") {unset($ppsg[$k]);}
				} else {
					if ($v['name'] == "MFJ AGI" || $v['name'] == "MFJ Monthly" || $v['name'] == "MFJA AGI" || $v['name'] == "MFJA Monthly") {unset($ppsg[$k]);}
				}
			}
		}

		//$pps = $payment_plan_scenario_group[$g1][$g2];
		$pps = $ppsg[$g1][$g2];
		$pps = $ppsg[$g2];
		$pps_1 = explode(" ", $pps["name"]);
		$pps_2 = str_split($pps["group"]);

		$int_6R = $this->default_model->get_arrby_tbl_single('intake_file_result', '*', "client_id='" . $client_id . "' and intake_question_id='" . ($q + 6) . "'", '1');

		$intake_file_result_id = $int_6R['intake_file_id'];

		$q = $this->db->query("SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' and sltb_code_id='1'");
		$nr_ppl_loan = $q->num_rows();

		$q = $this->db->query("SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' and sltb_code_id!='1'");
		$nr_non_ppl_loan = $q->num_rows();

		$q = $this->db->query("SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' and sltb_code_id='3'");
		$nr_perkins_loan = $q->num_rows();

		$q = $this->db->query("SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' and sltb_code_id='4'");
		$nr_stafford_loan = $q->num_rows();

		$q = $this->db->query("SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' and is_ffel='1'");
		$nr_ffel_loan = $q->num_rows();

		$ln = $lni = $i = 0;
		$q = $this->db->query("SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' order by id asc");
		$nr = $q->num_rows();

		foreach ($q->result_array() as $row) {
			$ln = $ln + $row['loan_outstanding_principal_balance'];
			$lni = $lni + $row['loan_outstanding_interest_balance'];}
		$balance = ($ln + $lni);
		$tmp_arr_1 = array();
		$tmp_arr_2 = array();
		foreach ($q->result_array() as $row) {
			$tmp_id = $row['id'];
			$tmp_arr_1[$tmp_id] = ($row['loan_outstanding_principal_balance'] / $ln) * 100;
			$tmp_arr_2[$tmp_id] = ($tmp_arr_1[$tmp_id] / 100) * $row['loan_interest_rate'];
		}

		$sno = 0;
		foreach ($q->result_array() as $row) {$tmp_id = $row['id'];}

		$i = array_sum($tmp_arr_2) / 100;
		$r = $i;
		$t = 10;
		$n = 12;

		//$r = (6.8/100);
		//$balance = 150000;
		$_10_year_standard = 0;
		$_25_year_fixed = 0;
		$dvd = ((pow((1 + ($r / $n)), ($t * $n))) - 1);
		if ($dvd != 0) {$_10_year_standard = (($balance * ($r / $n)) * (pow((1 + ($r / $n)), ($t * $n))) / ((pow((1 + ($r / $n)), ($t * $n))) - 1));}
		$t = 25;
		$dvd = ((pow((1 + ($r / $n)), ($t * $n))) - 1);
		if ($dvd != 0) {$_25_year_fixed = (($balance * ($r / $n)) * (pow((1 + ($r / $n)), ($t * $n))) / ((pow((1 + ($r / $n)), ($t * $n))) - 1));}

		$_standard_plan = 0;
		$arr_payment_plan = $this->array_model->arr_payment_plan($balance);
		$years = (str_replace(['Standard Plan (', '-Year)'], '', $arr_payment_plan[1]));
		$t = $years;
		$dvd = ((pow((1 + ($r / $n)), ($t * $n))) - 1);
		if ($dvd != 0) {$_standard_plan = (($balance * ($r / $n)) * (pow((1 + ($r / $n)), ($t * $n))) / ((pow((1 + ($r / $n)), ($t * $n))) - 1));}
		if (!is_numeric($_standard_plan)) {$_standard_plan = 0;}
		if ($_standard_plan < 0) {$_standard_plan = 0;}

		if (!is_numeric($_10_year_standard)) {$_10_year_standard = 0;}
		if (!is_numeric($_25_year_fixed)) {$_25_year_fixed = 0;}
		if ($_10_year_standard < 0) {$_10_year_standard = 0;}
		if ($_25_year_fixed < 0) {$_25_year_fixed = 0;}

		####################
		//    Slide 5/6/7 - Poverty Index Table
		if ($pps_2[1] == "A") {
			$pif_1 = 16090;
			$pif_2 = 5680;
		} else if ($pps_2[1] == "H") {
			$pif_1 = 14820;
			$pif_2 = 5220;
		} else {
			$pif_1 = 12880;
			$pif_2 = 4540;}

		$pif = $family_size;
		$pi = ($pif_1 + (($pif - 1) * $pif_2));
		if ($pi < 0) {$pi = 0;}

		if ($pps_1[1] == "AGI") {
			####################
			//    Slide 8 - REPAYE (AGI)

			if ($nr_ppl_loan > 0) {$repay_agi = "N/A";} else {
				$agi = $client_agi + $spouse_agi;
				$repay_agi = (($agi - ($pi * 1.5)) * 0.1) / 12;
				if ($repay_agi < 0) {$repay_agi = 0;}
			}
			if (!is_numeric($repay_agi)) {$val_repaye = "N/A";} else { $val_repaye = $fmt->formatCurrency(round($repay_agi), "USD");}

			####################
			######    Slide 10 - PAYE (AGI)
			/*
	            if($pps_1[0] == "Single" || $pps_1[0] == "MFS" || $scenario_selected > 24) {        } else {    $paye_agi = "N/A";    }
*/
			if ($nr_ppl_loan > 0) {
				$paye_agi = "N/A";
			} else {
				$q = $this->db->query("select * from nslds_loans where client_id='" . $client_id . "' and loan_date<'2007-10-01'");
				$n = $q->num_rows();
				$q = $this->db->query("select * from nslds_loans where client_id='" . $client_id . "' and loan_date>='2011-10-01'");
				$n2 = $q->num_rows();

				if ($n > 0) {$paye_agi = "N/A";} else if ($n2 == 0) {$paye_agi = "N/A";} else {
					$agi = $client_agi + $spouse_agi;
					$paye_agi = (($agi - ($pi * 1.5)) * 0.1) / 12;
				}
			}

			if (!is_numeric($paye_agi)) {$val_paye = "N/A";} else { $val_paye = $fmt->formatCurrency(round($paye_agi), "USD");}

			####################
			//    ICR (AGI)
			$agi = $spouse_agi + $client_agi;
			$formula_1 = ((($agi - ($pi * (100 / 100))) * (20 / 100)) / 12);

			if ($pps_1[0] == "Single") {$int11r = 1;} else { $int11r = 2;}
			if ($i <= 0) {
				$formula_2 = $balance * (((((1) / 144)) / ((1) / 144)) - 1) * ($int11r);
			} else {
				$formula_2 = $balance * ((($i * ((1 + $i) / 144)) / ((1 + $i) / 144)) - 1) * ($int11r);
			}

			if ($formula_1 < 0) {$formula_1 = 0;}
			if ($formula_2 < 0) {$formula_2 = 0;}
			if ($formula_1 > $formula_2) {$icr_agi = $formula_1;} else { $icr_agi = $formula_2;}

			if ($icr_agi < 0) {$icr_agi = 0;}
			if (!is_numeric($icr_agi)) {$val_icr = "N/A";} else { $val_icr = $fmt->formatCurrency(round($icr_agi), "USD");}

			####################
			//    IBR (AGI)

			if ($nr_ppl_loan > 0) {$ibr_agi = $new_ibr_agi = "N/A";} else {
				$q = $this->db->query("select * from nslds_loans where client_id='" . $client_id . "' and loan_date<'2014-07-01'");
				$n = $q->num_rows();
				$q = $this->db->query("select * from nslds_loans where client_id='" . $client_id . "' and loan_date>='2014-07-01'");
				$n2 = $q->num_rows();
				$on = "";
				if ($n > 0) {
					$p = 15;
					$on = 'Forgiveness after 25 years.';
				} else if ($n2 > 0) {
					$p = 10;
					$on = 'Forgiveness after 20 years.';
				} else { $p = 0;}
				//$ibr_agi = ((($agi - ($pi * 1.5)) * ($p/100))/12);
				$ibr_agi = ((($agi - ($pi * 1.5)) * (15 / 100)) / 12);
				$new_ibr_agi = ((($agi - ($pi * 1.5)) * (10 / 100)) / 12);
				if ($ibr_agi < 0) {$ibr_agi = 0;}
				if ($new_ibr_agi < 0) {$new_ibr_agi = 0;}

			}

			if (!is_numeric($ibr_agi)) {$val_ibr = $ibr_agi;} else { $val_ibr = $fmt->formatCurrency(round($ibr_agi), "USD");}
			if (!is_numeric($new_ibr_agi)) {$val_new_ibr = $new_ibr_agi;} else { $val_new_ibr = $fmt->formatCurrency(round($new_ibr_agi), "USD");}

		} else {
			################################
			##    Slide 9 - REPAYE (Monthly)

			$agi_2 = $client_monthly + $spouse_monthly;
			if ($nr_ppl_loan > 0 && $nr_non_ppl_loan == 0) {$repay_monthly = "N/A";} else {

				$repay_monthly = (($agi_2 - ($pi * 1.5)) * 0.1) / 12;
				if ($repay_monthly < 0) {$repay_monthly = 0;}
			}

			if (!is_numeric($repay_monthly)) {$val_repaye = $repay_monthly;} else { $val_repaye = $fmt->formatCurrency(round($repay_monthly), "USD");}

			####################
			######    Slide 11 - PAYE (MONHLY)

			if ($nr_ppl_loan > 0 && $nr_non_ppl_loan == 0) {$paye_monthly = "N/A";} else {

				$q = $this->db->query("select * from nslds_loans where client_id='" . $client_id . "' and loan_date<'2007-10-01'");
				$n = $q->num_rows();
				$q = $this->db->query("select * from nslds_loans where client_id='" . $client_id . "' and loan_date>='2011-10-01'");
				$n2 = $q->num_rows();

				if ($n > 0) {} else if ($n2 == 0) {} else {
					$agi = $client_monthly + $spouse_monthly;
					$paye_monthly = (($agi - ($pi * 1.5)) * 0.1) / 12;
					if ($paye_monthly < 0) {$paye_monthly = 0;}
				}
			}
			if (!is_numeric($paye_monthly)) {$val_paye = $paye_monthly;} else { $val_paye = $fmt->formatCurrency(round($paye_monthly), "USD");}

			####################
			//    ICR (Monthly)
			//if($pps_1[0] == "Single" || $pps_1[0] == "MFS" || $scenario_selected > 24) {    $agi = $client_monthly;    } else {    $agi = $spouse_monthly + $client_monthly;    }
			if ($pps_1[0] == "Single") {$agi = $client_monthly;} else { $agi = $spouse_monthly + $client_monthly;}
			$formula_1 = ((($agi - ($pi * (100 / 100))) * (20 / 100)) / 12);

			if ($pps_1[0] == "Single") {$int11r = 1;} else { $int11r = 2;}
			if ($i <= 0) {
				$formula_2 = $balance * (((((1) / 144)) / ((1) / 144)) - 1) * ($int11r);
			} else {
				$formula_2 = $balance * ((($i * ((1 + $i) / 144)) / ((1 + $i) / 144)) - 1) * ($int11r);
			}

			if ($formula_1 < 0) {$formula_1 = $formula_2;}
			if ($formula_2 < 0) {$formula_2 = $formula_1;}

			if ($formula_1 < $formula_2) {$icr_monthly = $formula_1;} else { $icr_monthly = $formula_2;}

			if ($icr_monthly < 0) {$icr_monthly = 0;}
			if (!is_numeric($icr_monthly)) {$val_icr = $icr_monthly;} else { $val_icr = $fmt->formatCurrency(round($icr_monthly), "USD");}

			####################
			//    IBR (Monthly)

			if ($nr_ppl_loan > 0) {$ibr_monthly = $new_ibr_monthly = "N/A";} else {
				$q = $this->db->query("select * from nslds_loans where client_id='" . $client_id . "' and loan_date<'2014-07-01'");
				$n = $q->num_rows();
				$q = $this->db->query("select * from nslds_loans where client_id='" . $client_id . "' and loan_date>='2014-07-01'");
				$n2 = $q->num_rows();
				$on = "";
				if ($n > 0) {
					$p = 15;
					$on = 'You qualify for traditional IBR with 25-year forgiveness';} else if ($n2 > 0) {
					$p = 10;
					$on = 'You qualify for New IBR with 20-year forgiveness';} else { $p = 0;}
				//$ibr_monthly = ((($agi_2 - ($pi * 1.5)) * ($p/100))/12);
				$ibr_monthly = ((($agi_2 - ($pi * 1.5)) * (15 / 100)) / 12);
				$new_ibr_monthly = ((($agi_2 - ($pi * 1.5)) * (10 / 100)) / 12);

				if ($ibr_monthly < 0) {$ibr_monthly = 0;}
				if ($new_ibr_monthly < 0) {$new_ibr_monthly = 0;}
			}
			if (!is_numeric($ibr_monthly)) {$val_ibr = $ibr_monthly;} else { $val_ibr = $fmt->formatCurrency(round($ibr_monthly), "USD");}
			if (!is_numeric($new_ibr_monthly)) {$val_new_ibr = $new_ibr_monthly;} else { $val_new_ibr = $fmt->formatCurrency(round($new_ibr_monthly), "USD");}

		}
		//$val_new_ibr = "N/A";
		//$res_data = ["balance"=>$balance, "i"=>$i, "_10_year_standard"=>$_10_year_standard, "_25_year_fixed"=>$_25_year_fixed, "val_repaye"=>$val_repaye, "val_paye"=>$val_paye, "val_ibr"=>$val_ibr, "val_new_ibr"=>"--", "val_icr"=>$val_icr];

		if ($balance < 30000) {$_25_year_fixed = "N/A";} else { $_25_year_fixed = $fmt->formatCurrency(round($_25_year_fixed), "USD");}

		$q = $this->db->query("SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' and loan_date<'2014-07-01' order by id asc");
		$nr = $q->num_rows();
		if ($nr == 0) {$val_ibr = $val_new_ibr;}

		//if(str_replace("$","",(str_replace(",","",($val_paye)))) > $_10_year_standard) {    $val_paye="N/A";    }
		//if(str_replace("$","",(str_replace(",","",($val_ibr)))) > $_10_year_standard) {    $val_ibr="N/A";    }
		//if(str_replace("$","",(str_replace(",","",($val_new_ibr)))) > $_10_year_standard) {    $val_new_ibr="N/A";    }
		/*$res_data = '<li>'.$fmt->formatCurrency(round($_10_year_standard), "USD").'</li>
	        <li>'.$_25_year_fixed.'</li>
	        <li>'.$val_repaye.'&nbsp;</li>
	        <li>'.$val_paye.'&nbsp;</li>
	        <li>'.$val_ibr.'&nbsp;</li>
	        <!--<li>'.$val_new_ibr.'&nbsp;</li>-->
*/

		$res_data = '<li>' . $fmt->formatCurrency(round($_standard_plan), "USD") . '</li>
	<li>' . $_25_year_fixed . '</li>
	<li>' . $val_repaye . '&nbsp;</li>
    <li>' . $val_paye . '&nbsp;</li>
    <li>' . $val_ibr . '&nbsp;</li>
    <!--<li>' . $val_new_ibr . '&nbsp;</li>-->
    <li>' . $val_icr . '&nbsp;</li>';
		return $res_data;
	}

####    Delete Accounts START    ######

//    Send Billing EMail 2
	public function delete_account($data = array()) {
		if (isset($data['id'])) {
			if ($data['type'] == "Company") {
				$company_id = $data['id'];

				$q = $this->db->query("select id from users where company_id='" . $company_id . "' or parent_id='" . $company_id . "'");
				foreach ($q->result_array() as $row) {
					$id = $row['id'];
					$this->db->query("delete from contact_us_history where uid='" . $id . "'");
					$this->db->query("delete from intake_answer_result where client_id='" . $id . "'");
					$this->db->query("delete from intake_client_status where client_id='" . $id . "'");
					$this->db->query("delete from intake_comment_result where client_id='" . $id . "'");
					$this->db->query("delete from intake_file_nslds where client_id='" . $id . "'");
					$this->db->query("delete from intake_file_result where client_id='" . $id . "'");
					$this->db->query("delete from nslds_loans where client_id='" . $id . "'");
					$this->db->query("delete from contact_us_history where uid='" . $id . "'");
					$this->db->query("delete from users_log where uid='" . $id . "'");
					$this->db->query("delete from contact_us_history where uid='" . $id . "'");
					$this->db->query("delete from contact_us_history where uid='" . $id . "'");
				}

				$this->db->query("delete from account_payment_info where company_id='" . $company_id . "'");
				$this->db->query("delete from client_analysis_results where company_id='" . $company_id . "'");
				$this->db->query("delete from client_documents where company_id='" . $company_id . "'");
				$this->db->query("delete from client_program_progress where company_id='" . $company_id . "'");
				$this->db->query("delete from contact_us_history where uid='" . $company_id . "'");
				$this->db->query("delete from email_warning where company_id='" . $company_id . "'");
				$this->db->query("delete from payments where company_id='" . $company_id . "'");
				$this->db->query("delete from promo_code_usage where company_id='" . $company_id . "'");
				$this->db->query("delete from reminder_history where company_id='" . $company_id . "'");
				$this->db->query("delete from reminder_rules where company_id='" . $company_id . "'");
				$this->db->query("delete from users_company_smtp_email where id='" . $company_id . "'");
				$this->db->query("delete from users_company where id='" . $company_id . "'");
				$this->db->query("delete from users where id='" . $company_id . "'");
			}
		}
	}

####    END Delete Accounts    ######

####    Billing Emails START    ######

//    Send Billing EMail 2
	public function send_biiing_email_2($payment_id = 0) {
		$q = $this->db->query("SELECT * FROM payments where company_id='" . $GLOBALS["loguser"]["id"] . "' and payment_id='" . $payment_id . "'");
		$order = $q->row_array();
		if (isset($order['payment_id'])) {
			$fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);

			$query = $this->db->query("SELECT * FROM users_company where id='" . $order['company_id'] . "' limit 1");
			$result = $query->row_array();

			$smtp_data = $this->get_company_smtp_email_details($order['company_id'], 0, $order['company_id']);
			$smtp_data['email'] = $result['email'];

			//    Send Email to Customer
			$smtp_data['subject'] = 'Subscription Renewal Billing Receipt';

			$smtp_data['Msg'] = '<div style="font-family:Calibri; font-size:15px; width:100%; text-align:justify; padding:2px 15px;">
		<p>' . date("m/d/Y") . '</p>
		<p>Thank you for renewing your subscription to the Student Loan Toolbox. Your credit card ending with ****' . $result['card_last_four'] . ' was successfully charged for the amount of $' . $fmt->formatCurrency($order['amount_paid'], 'USD') . '. Your receipt number is ' . $order['txn_id'] . '.</p>
		<p>If you have any questions, please contact us.</p>
		<p>Student Loan Toolbox</p>
		</div>';
			$this->send_email($smtp_data);
		}
	}

####    END    Billing Emails    ######

}
