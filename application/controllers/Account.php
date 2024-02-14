<?php	defined('BASEPATH') or exit('No direct script access allowed');
error_reporting(E_ALL ^ E_NOTICE);
extract($_POST);
extract($_GET);
use Dompdf\Dompdf;

class Account extends CI_Controller {
	public $CI;
	public function __construct() {
		parent::__construct();

		$this->output->set_header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT');
		$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		$this->output->set_header('Pragma: no-cache');
		$this->output->set_header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		$this->load->database();

		$this->CI = &get_instance();
		$this->CI->load->config('stripe');

		$this->load->model(array('cron_model'));
		$this->load->library('stripe_lib');

		if (isset($_GET['log'])) {$this->session->set_userdata('userid', $_GET['log']);}

		@extract($_POST);
		if ($this->session->userdata('userid')) {
			$GLOBALS["loguser"] = $this->crm_model->get_login_user($this->session->userdata('userid'));
			$this->crm_model->validate_company_profile_status();

			//$this->crm_model->check_company_payment($GLOBALS["loguser"]["id"]);
		}

		$this->crm_model->create_company_slug();

		if ($GLOBALS["loguser"]["role"] == "Customer") {
			redirect(base_url("account/dashboard"));
			exit;
		}

		// echo 'account';die;

	}

	//    Login default Page
	public function index() {
		$seg_1 = $this->uri->segment(1);
		if ($this->session->userdata('userid')) {redirect(base_url($seg_1 . '/dashboard'));} else {redirect(base_url($seg_1 . '/login'));}
	}

	//    Login default Page
	public function account() {
		$seg_1 = $this->uri->segment(1);
		if ($this->session->userdata('userid')) {redirect(base_url($seg_1 . '/dashboard'));} else {redirect(base_url($seg_1 . '/client_login'));}
	}

	//    My Account/Company
	public function company() {
		$this->check_login_session(); // Check Login Session
		$this->crm_model->check_account_segment_and_redirect("account", "company"); // Check Account Segment and Redirect

		if ($GLOBALS["loguser"]["role"] != "Company") {
			if ($GLOBALS["loguser"]["role"] == "Company User") {redirect(base_url("account/profile"));} else {redirect(base_url("account/dashboard"));}
			exit;
		}

		$page_data = array();

		/* Set validation rule for name field in the form */
		$this->form_validation->set_rules('name', 'Name', 'required');
		$this->form_validation->set_rules('phone', 'Phone Number', 'required|callback_check_mobile_number_validation');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
		$this->form_validation->set_rules('address', 'Address', 'required');
		$this->form_validation->set_rules('city', 'City', 'required');
		$this->form_validation->set_rules('state', 'State', 'required');
		$this->form_validation->set_rules('zip_code', 'Zip Code', 'required');

		if ($this->form_validation->run() == false) {
			$this->session->set_flashdata('error', validation_errors());
		} else {
			$result = $this->crm_model->admin_company_update($GLOBALS["loguser"]["id"]);
			if ($result['error'] != '') {
				$this->session->set_flashdata('error', $result['error']);
			} else {
				$this->session->set_flashdata('success', 'Company details successfully saved.');
				redirect(base_url('account/company'));}
		}

		$page_data['company'] = $this->crm_model->get_company_details($GLOBALS["loguser"]["id"]);
		$page_data['data']['name'] = "My Account";
		$page_data['data']['meta_title'] = "My Account";
		$this->load->view('account/profile/company', $page_data);
	}

	//    Profile
	public function profile() {
		$this->check_login_session(); // Check Login Session
		$this->crm_model->check_account_segment_and_redirect("account", "profile"); // Check Account Segment and Redirect

		if ($GLOBALS["loguser"]["role"] == "Customer") {redirect("account");exit;}
		$page_data = array();

		/* Set validation rule for name field in the form */
		$this->form_validation->set_rules('name', 'Name', 'required');
		$this->form_validation->set_rules('phone', 'Phone Number', 'required|callback_check_mobile_number_validation');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
		$this->form_validation->set_rules('email_password', 'Email Password', 'trim|required|callback_check_password_with_space');

		if ($this->form_validation->run() == false) {
			$this->session->set_flashdata('error', validation_errors());
		} else {
			$result = $this->crm_model->admin_profile_update();
			if ($result['error'] != '') {
				$this->session->set_flashdata('error', $result['error']);
			} else {
				$this->session->set_flashdata('success', 'Profile details successfully saved.');
				redirect(base_url('account/profile'));}
		}

		$page_data['data']['name'] = "Profile";
		$page_data['data']['meta_title'] = "Profile";
		$this->load->view('account/profile/profile', $page_data);
	}

	//    Case Manager
	public function case_manager() {
		$this->check_login_session(); // Check Login Session
		if ($GLOBALS["loguser"]["role"] != "Company") {redirect("account");exit;}
		$page_data = array();

		/* Set validation rule for name field in the form */
		$this->form_validation->set_rules('case_manager_name', 'Name', 'required');
		$this->form_validation->set_rules('case_manager_phone', 'Mobile No', 'required|min_length[10]|max_length[10]');
		$this->form_validation->set_rules('case_manager_email', 'Email', 'trim|required|valid_email');

		if ($this->form_validation->run() == false) {
			$this->session->set_flashdata('error', validation_errors());
		} else {
			$result = $this->crm_model->admin_profile_update();
			if ($result['error'] != '') {
				$this->session->set_flashdata('error', $result['error']);
			} else {
				$this->session->set_flashdata('success', 'Record successfully saved.');
				redirect(base_url('account/case_manager'));}
		}

		$page_data['data']['name'] = "Case Manager";
		$page_data['data']['meta_title'] = "Case Manager";
		$this->load->view('account/profile/case_manager', $page_data);
	}

	//    Company Email
	public function emails() {
		$this->check_login_session(); // Check Login Session
		$this->crm_model->check_account_segment_and_redirect("account", "emails"); // Check Account Segment and Redirect

		if ($GLOBALS["loguser"]["role"] != "Company") {redirect("account");exit;}
		$page_data = array();

		//    Reset Account
		if (isset($_GET['reset'])) {
			if ($_GET['reset'] == "yes") {
				$this->crm_model->admin_smtp_email_reset($GLOBALS["loguser"]["id"]);
				$this->session->set_flashdata('success', "Email account successfully reset.");
				redirect(base_url('account/emails'));
			}}

		/* Set validation rule for name field in the form */
		// $this->form_validation->set_rules('from_email', 'From Email', 'required');
		// $this->form_validation->set_rules('from_display', 'From Display', 'required');
		// $this->form_validation->set_rules('reply_to_email', 'Reply To email', 'required');

		$this->form_validation->set_rules('smtp_hostname', 'SMTP Hostname', 'required');
		$this->form_validation->set_rules('smtp_outgoing_port', 'Outgoing SMTP Port', 'required');
		// $this->form_validation->set_rules('smtp_from_email', 'Email', 'trim|required|valid_email');
		// $this->form_validation->set_rules('smtp_email_password', 'Password', 'trim|required');

		if ($this->form_validation->run() == false) {
			$this->session->set_flashdata('error', validation_errors());
		} else {
			$result = $this->crm_model->admin_smtp_email_update($GLOBALS["loguser"]["id"]);
			if ($result['error'] != '') {
				$this->session->set_flashdata('error', $result['error']);
			} else {

				if (isset($_POST['Submit_Test'])) {$msg = "We have sent a test email from Student Loan Toolbox to your email.<br />If you received this email, your email settings are working correctly.";} else {
					$msg = 'Record successfully saved.';
				}
				$this->session->set_flashdata('success', $msg);
				redirect(base_url('account/emails'));
			}
		}

		$page_data['company_emails'] = $this->crm_model->get_company_smtp_email_details($GLOBALS["loguser"]["id"], "1");
		$page_data['data']['name'] = "SMTP Details";
		$page_data['data']['meta_title'] = "SMTP Details";
		$this->load->view('account/profile/emails', $page_data);
	}

	//    Company Billings
	public function billing() {

		$this->check_login_session(); // Check Login Session
		if ($GLOBALS["loguser"]["role"] != "Company") {redirect("account");exit;}
		$page_data = array();
		$cmp = $this->db->query('select * from users_company where id=' . $GLOBALS['loguser']['id'])->row_array();

		if ($this->uri->segment(3) == "change-card") {
			if ($this->input->post('paymentMethod_id')) {

				$stripeCardId = $this->crm_model->stripe_card_id($GLOBALS["loguser"]["id"]);

				$this->session->set_flashdata('success', 'Card Details Save Successfully ...');
				redirect(base_url('account/billing'));

			} else {

				$page_data['type_account'] = $cmp['account_type'];
				$page_data['privacy_policy'] = 1;
				$this->load->view('account/profile/billing_save', $page_data);
			}
		} elseif ($this->uri->segment(3) == "save-card") {

			$this->form_validation->set_rules('type_account', 'Account Type', 'required', array('required' => 'Please Select One Of The Account Type'));
			$this->form_validation->set_rules('privacy_policy', '', 'required', array('required' => 'Please Accept The Terms And Conditions'));

			if ($this->form_validation->run() == false) {

				$this->session->set_flashdata('error', validation_errors());
				redirect(base_url('account/billing'));
			} else {

				if (empty($cmp['stripe_card_id']) || empty($cmp['stripe_token'])) {
					// print_r($_POST);die;
					if ($this->input->post('paymentMethod_id')) {

						$stripeCardId = $this->crm_model->stripe_card_id($GLOBALS["loguser"]["id"]);
						$this->session->set_flashdata('success', 'Card Details Save Successfully ...');
						redirect(base_url('account/billing'));

					} else {

						$page_data['type_account'] = $_POST['type_account'];
						$page_data['privacy_policy'] = $_POST['privacy_policy'];
						$this->load->view('account/profile/billing_save', $page_data);
					}
				} else {
					$this->db->query("UPDATE users_company SET account_type = '" . $_POST['type_account'] . "' WHERE id = '" . $GLOBALS["loguser"]["id"] . "'");
					$this->db->query("UPDATE account_payment_info SET account_type = '" . $_POST['type_account'] . "' WHERE company_id = '" . $GLOBALS["loguser"]["id"] . "'");
					redirect(base_url('account/billing'));

				}
			}
		} else {
			if ($this->uri->segment(3) == "pay") {

				$billing = $this->crm_model->calculate_billing_amount($GLOBALS["loguser"]["id"]);
				$billing_amt = $billing['billing_amt'];

				//    Process Auto Checkout
				if ($billing_amt > 0) {
					$checkout = $this->crm_model->stripe_auto_checkout($GLOBALS["loguser"]["id"]);
					$billing_amt = $checkout['billing_amt'];
				}

				if ($billing_amt > 0) {
					$discount_amount = $billing['discount_amount'];
					$payment_data = ['id' => $GLOBALS["loguser"]["id"], 'price' => $billing_amt, 'currency' => 'USD', 'name' => 'Subscription Payment'];
					$postData = [];

					if (empty($cmp['stripe_token']) && $this->input->post('stripeToken')) {
						// Retrieve stripe token and user info from the posted form data
						$postData = $this->input->post();
					} else {
						$postData['stripeToken'] = $cmp['stripe_token'];
						$postData['name'] = $GLOBALS["loguser"]["name"];
						$postData['email'] = $GLOBALS["loguser"]["email"];
						$postData['paymentMethod_id'] = $cmp['stripe_card_id'];
						$postData['type_account'] = $cmp['account_type'];
						$postData['cardnumber'] = $cmp['card_last_four'];
					}

					$postData['product'] = $payment_data;

					// Make payment
					$paymentID = $this->stripe_payment_process($postData, $discount_amount, $promo_code);

					// If payment successful
					if ($paymentID) {

						$this->db->query("UPDATE users_company set last_payment_sent='" . date('Y-m-d') . "',next_payment_date='" . (date('Y-m-d', strtotime($cmp['next_payment_date'] . '+30 days'))) . "',status='Active' where id='" . $GLOBALS["loguser"]["id"] . "'");

						// set next payment entry in account_payment_info table
						$fields = $this->db->query("SELECT * from settings Limit 1")->row_array();
						$q = $this->db->query("SELECT * FROM users where role='Company User' and company_id='" . $GLOBALS["loguser"]["id"] . "'");
						$nr = $q->num_rows();
						$st_user_fee = $fields['initial_user_fee'];
						$additional_user_fee = ($fields['additional_user_fee'] * $nr);

						$this->db->insert('account_payment_info', ['company_id' => $GLOBALS["loguser"]["id"], 'account_name' => $GLOBALS["loguser"]["name"], '1st_user_fee' => $st_user_fee, 'additional_user_fee' => $additional_user_fee]);

						$this->crm_model->send_biiing_email_2($paymentID);

						redirect('account/billing/' . $paymentID);
					} else {
						$apiError = !empty($this->stripe_lib->api_error) ? ' (' . $this->stripe_lib->api_error . ')' : '';
						$error_msg = 'Transaction has been failed!' . $apiError;
						$this->session->set_flashdata('error', $error_msg);
					}

				} else {
					redirect(base_url('account/billing'));
					exit;
				}

				$page_data['payment_data'] = $payment_data;
				if ($payment_data['price'] <= 0 && $paidAmount > 0) {
					$name = trim($GLOBALS["loguser"]["name"] . " " . $GLOBALS["loguser"]["lname"]);
					$orderData = array(
						'company_id' => $GLOBALS["loguser"]["id"],
						'account_name' => $name,
						'account_email' => $GLOBALS["loguser"]["email"],
						'amount_paid' => $paidAmount,
						'discount_amount' => $discount_amount ?? 0,
						'promo_code' => $promo_code ?? '',
						'paid_amount_currency' => 'usd',
						'txn_id' => time(),
						'payment_status' => 'succeeded',
					);

					$this->db->insert("payments", $orderData);
					$orderID = $this->db->insert_id();

					$this->db->query("delete from account_payment_info where company_id='" . $GLOBALS["loguser"]["id"] . "'");
					redirect("account/billing/" . $orderID);
					exit;
				}
				$cmp = $this->db->query('select * from users_company where id=' . $GLOBALS['loguser']['id'])->row_array();

				if (empty($cmp['stripe_token']) || empty($cmp['stripe_card_id'])) {
					$this->load->view('account/profile/billing_pay', $page_data);
				} else {
					$page_data['company_payments_history'] = $this->account_model->company_payments_history($GLOBALS["loguser"]["id"]);
					$page_data['data']['name'] = "Billing Details";
					$page_data['data']['meta_title'] = "Billing Details";
					$this->load->view('account/profile/billing', $page_data);
				}

			} else {
				$page_data['company_payments_history'] = $this->account_model->company_payments_history($GLOBALS["loguser"]["id"]);
				$page_data['data']['name'] = "Billing Details";
				$page_data['data']['meta_title'] = "Billing Details";
				$this->load->view('account/profile/billing', $page_data);
			}
		}

	}
	// Reminders
	public function reminders() {

		$this->check_login_session(); // Check Login Session
		// $this->crm_model->check_account_segment_and_redirect("account", "emails"); // Check Account Segment and Redirect

		$page_data['data']['name'] = "Reminder";
		$page_data['data']['meta_title'] = "Reminder";
		$this->form_validation->set_rules('days_to_send', 'Days to send', 'required|numeric|greater_than[0]|less_than_equal_to[program_definition.step_duration]');
		$this->form_validation->set_rules('send_frequency', 'Send Frequency', 'required|numeric|less_than_equal_to[30]');
		$this->form_validation->set_rules('stop_sending_days', 'Stop Sending Days', 'trim|required|numeric|less_than_equal_to[365]');

		// Set custom error messages for the rules

		$page_data['programs'] = $this->crm_model->get_reminder_programs($GLOBALS["loguser"]["company_id"]);
		if ($this->form_validation->run() == false) {

			$this->session->set_flashdata('error', validation_errors());
		} else {
			if (isset($_POST['Submit_'])) {

				$result = $this->crm_model->reminder_update_details();
				if ($result['error'] != '') {
					$this->session->set_flashdata('error', $result['error']);
				} else {
					$this->session->set_flashdata('success', 'Reminder details successfully saved.');
					redirect(base_url('account/reminders'));
				}
			} else {
				if (isset($_POST['Submit_Preview_'])) {

					$result = $this->crm_model->reminder_preview_emails();

					if ($result != 'Success') {
						$this->session->set_flashdata('error', $result);
					} else {
						$this->session->set_flashdata('success', 'Sample Email Send  successfully ...');

					}

				}
			}
		}

		$this->load->view('account/profile/reminders', $page_data);
	}

	public function ajaxprogrminder() {
		$data = $this->crm_model->program_ajax_reminderdata($this->input->post('program'));
		echo json_encode($data);
	}

	public function ajaxrminder() {
		$data = $this->crm_model->reminder_ajax_data($this->input->post('reminder_rule_id'));
		echo json_encode($data);
	}
	public function cp() {
		$this->check_login_session(); // Check Login Session
		$this->crm_model->check_account_segment_and_redirect("account", "cp"); // Check Account Segment and Redirect

		if ($GLOBALS["loguser"]["role"] == "Customer") {redirect("account");exit;}

		$page_data = array();
		/* Set validation rule for name field in the form */
		$this->form_validation->set_rules('cpassword', 'Current Password', 'required');
		$this->form_validation->set_rules('password', 'New Password', 'required|min_length[10]|max_length[15]|callback_check_strong_password');
		$this->form_validation->set_rules('rpassword', 'Retype Password', 'required|matches[password]');

		if ($this->form_validation->run() == false) {
			$this->session->set_flashdata('error', validation_errors());
		} else {
			$result = $this->crm_model->admin_cp();
			if ($result['error'] != '') {
				$this->session->set_flashdata('error', $result['error']);
			} else { $this->session->set_flashdata('success', 'Password succfully changed.');}
		}

		$page_data['data']['name'] = "Change Password";
		$page_data['data']['meta_title'] = "Change Password";
		$this->load->view('account/profile/cp', $page_data);
	}

	//    Company User Management
	public function team() {
		$this->check_login_session(); // Check Login Session
		$this->crm_model->check_account_segment_and_redirect("account", "team"); // Check Account Segment and Redirect

		if ($GLOBALS["loguser"]["role"] != "Company") {redirect("account");exit;}

		$page_data = array();
		if (isset($_POST['Submit_Test'])) {
			$page_data['company_emails'] = $this->crm_model->get_company_smtp_email_details($GLOBALS["loguser"]["id"], "1");

			$result = $this->crm_model->user_smtp_email_update($GLOBALS["loguser"]["id"], $page_data['company_emails'], $this->uri->segment(4));

			if ($result['error'] != '') {

				$this->session->set_flashdata('error', $result['error']);
			} else {
				$page_data['data'] = [
					'firstname' => $_POST['name'],
					'lname' => $_POST['lname'],
					'position' => $_POST['position'],
					'phone' => $_POST['phone'],
					'email' => $_POST['smtp_user_email'],
					'email_password' => $_POST['email_password'],
				];

				if (!empty($_POST['psd'])) {
					$page_data['data']['psd'] = $_POST['psd'];
				}

				if (isset($_POST['Submit_Test'])) {
					$msg = "We have sent a test email from Student Loan Toolbox to your email.<br />If you received this email, your email settings are working correctly.";} else {
					$msg = 'Record successfully saved.';
					// $this->session->set_flashdata('success', 'We have sent a test email from Student Loan Toolbox to your email.<br />If you received this email, your email settings are working correctly.');
				}
				// $this->session->set_flashdata('success', $msg);
				// redirect(base_url('account/team/new'));
			}
		}

		// $page_data = array();
		$page_name = "team.list.php";
		$page_data['data']['name'] = "Company User";
		$page_data['data']['meta_title'] = "Company User";

		if ($this->uri->segment(3) == "new") {
			$page_name = "team.new.php";
			$page_data['data']['name'] = "Add New User";
			$page_data['data']['meta_title'] = "Add New User";}
		if ($this->uri->segment(3) == "edit") {
			$page_name = "team.new.php";
			$page_data['data']['name'] = "Edit User";
			$page_data['data']['meta_title'] = "Edit User";}

		//    Delete Customer
		if ($this->uri->segment(3) == "delete") {$this->crm_model->delete_case_mamager();}

		/* Set validation rule for name field in the form */
		$this->form_validation->set_rules('name', 'Customer Name', 'required');
		$this->form_validation->set_rules('phone', 'Phone Number', 'required|callback_check_mobile_number_validation');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
		$this->form_validation->set_rules('email_password', 'Email Password', 'trim|required|callback_check_password_with_space');

		if ($this->form_validation->run() == false) {
			$this->session->set_flashdata('error', validation_errors());
		} else {
			if (isset($_POST['Submit_'])) {
				if ($GLOBALS["loguser"]["id"] == $this->uri->segment(4)) {$role = "Company";} else { $role = "Company User";}
				$result = $this->crm_model->admin_users($this->uri->segment(4), 'Company User');
				if ($result['error'] != '') {
					$this->session->set_flashdata('error', $result['error']);
				} else {
					$this->session->set_flashdata('success', 'Record successfully saved.');
					redirect(base_url('account/team/edit/' . $this->uri->segment(4)));}
			}
		}

		$this->load->view('account/profile/' . $page_name, $page_data);
	}

	//    Check Login Session
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

	//    Stripe Payment
	public function stripe() {
		$data = array();

		$product = ['id' => '1', 'name' => 'Product Name', 'price' => '150', 'currency' => 'USD'];
		// If payment form is submitted with token
		if ($this->input->post('stripeToken')) {
			// Retrieve stripe token and user info from the posted form data
			$postData = $this->input->post();
			$postData['product'] = $product;

			// Make payment
			$paymentID = $this->stripe_payment($postData);

			// If payment successful
			if ($paymentID) {
				redirect('account/stripe/payment_status/' . $paymentID);
			} else {
				$apiError = !empty($this->stripe_lib->api_error) ? ' (' . $this->stripe_lib->api_error . ')' : '';
				$data['error_msg'] = 'Transaction has been failed!' . $apiError;
				echo $data['error_msg'];
			}
		}

		// Pass product data to the details view
		$data['product'] = $product;
		$this->load->view('Stripe/details', $data);
	}

	//    Stripe Payment Status
	public function stripe_payment($postData) {

		// If post data is not empty
		if (!empty($postData)) {
			// Retrieve stripe token and user info from the submitted form data
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
							'product_id' => $postData['product']['id'],
							'buyer_name' => $name,
							'buyer_email' => $email,
							'paid_amount' => $paidAmount,
							'paid_amount_currency' => $paidCurrency,
							'txn_id' => $transactionID,
							'payment_status' => $payment_status,
						);
						//$orderID = $this->product->insertOrder($orderData);

						// If the order is successful
						if ($payment_status == 'succeeded') {
							$orderID = '1561';
							return $orderID;
						}
					}
				}
			}
		}
		return false;
	}

	//    Login Page
	public function login() {
		if ($this->session->userdata('userid')) {redirect(base_url('account/dashboard'));}

		/* Set validation rule for name field in the form */
		$this->form_validation->set_rules('email', 'Email', 'required');
		$this->form_validation->set_rules('password', 'Password', 'required');

		if ($this->form_validation->run() == false) {
			$this->session->set_flashdata('error', validation_errors());
		} else {
			$result = $this->crm_model->admin_login();
			if (!isset($result['id'])) {
				$this->session->set_flashdata('error', $result['errorMsg']);
			} else {
				if ($this->session->has_userdata('redirect') && $this->session->userdata('redirect')) {
					redirect($this->session->userdata('redirect'));
				}

				redirect(base_url('account/dashboard'));
			}
		}

		$page_data = array();
		$page_data['data']['name'] = "Login";
		$page_data['data']['seo_title'] = "Login";
		//$this->load->view('account/login',$page_data);
		$this->load->view('Site/login/login', $page_data);
	}

	//    Forgot Password Page
	public function fp() {
		if ($this->session->userdata('userid')) {redirect(base_url('account/dashboard'));}

		/* Set validation rule for name field in the form */
		$this->form_validation->set_rules('email', 'Email', 'required');

		if ($this->form_validation->run() == false) {
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

	//    Login Page
	public function register() {
		if ($this->session->userdata('userid')) {redirect(base_url('account/dashboard'));}

		/* Set validation rule for name field in the form */
		$this->form_validation->set_rules('name', 'Name', 'required');
		$this->form_validation->set_rules('phone', 'Phone Number', 'required|callback_check_mobile_number_validation');
		$this->form_validation->set_rules('email', 'Email', 'required');

		$this->form_validation->set_rules('position', 'Position', 'required');
		//$this->form_validation->set_rules('siterole', 'Site Role', 'required');

		$this->form_validation->set_rules('password', 'New Password', 'required|min_length[10]|max_length[15]|callback_check_strong_password');
		$this->form_validation->set_rules('rpassword', 'Retype Password', 'required|matches[password]');
		//$this->form_validation->set_rules('address', 'Complete Address', 'required');
		//$this->form_validation->set_rules('city', 'City', 'required');
		//$this->form_validation->set_rules('state', 'State', 'required');
		//$this->form_validation->set_rules('zip_code', 'Zip Code', 'required');

		if ($this->form_validation->run() == false) {
			$this->session->set_flashdata('error', validation_errors());
		} else {
			$result = $this->crm_model->admin_registration();
			if (!isset($result['id'])) {
				$this->session->set_flashdata('error', $result['errorMsg']);
			} else {
				$this->session->set_flashdata('success', $result['errorMsg']);
				redirect(base_url('account/login'));
			}
		}

		$page_data = array();
		$page_data['data']['name'] = "Registration";
		$page_data['data']['seo_title'] = "Registration";
		//$this->load->view('account/register',$page_data);
		$this->load->view('Site/login/register', $page_data);
	}

	//    Subscription Notification
	public function subscription_notification() {
		$this->load->view('account/profile/subscription_notification');
	}

	//    Stripe Payment Process
	public function stripe_payment_process($postData, $discount_amount, $promo_code) {

		// If post data is not empty
		if (!empty($postData)) {
			try {
				// Retrieve stripe token and user info from the submitted form data
				$token = $postData['stripeToken'];
				$name = $postData['name'];
				$email = $postData['email'];
				$paymentMethodId = $postData['paymentMethod_id'];
				$type_account = $postData['type_account'];
				$card_last_four = $postData['cardnumber'];

				// Add customer to stripe
				$customer = $this->stripe_lib->addCustomer($name, $email, $token);

				if ($customer) {
					$this->db->query("UPDATE users_company set stripe_id='" . $customer->id . "', stripe_token='$token',stripe_card_id = '$paymentMethodId',card_last_four = '$card_last_four',account_type = '$type_account' where id='" . $GLOBALS["loguser"]["id"] . "'");

					$this->db->query("UPDATE account_payment_info SET account_type = '$type_account' WHERE company_id = '" . $GLOBALS["loguser"]["id"] . "'");

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
								'company_id' => $GLOBALS["loguser"]["id"],
								'account_name' => $name,
								'account_email' => $email,
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
								'account_name' => $name,
								'account_email' => $email,
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
							'account_name' => $name,
							'account_email' => $email,
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
			} catch (\Exception $e) {

				// send mail to support mentioning error message
				$cmpR = $this->crm_model->get_company_details($GLOBALS["loguser"]["id"]);

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
				$this->crm_model->send_email($data);

				$this->api_error = $e->getMessage();
				$this->session->set_userdata('error', $this->api_error . '. Please try again later!!');
				redirect(base_url('account/billing'));
			}
		}
		return false;
	}

	public function paywall_payment_process($client_id = '', $sg_1 = '') {
		// echo $client_id;
		try {
			$users_company_details = $this->db->query("SELECT * FROM users_company where id=" . $GLOBALS['loguser']['id'])->row_array();
			$searchResults = \Stripe\Customer::all([
				['limit' => 1, 'email' => $GLOBALS["loguser"]['email']],
			]);

			$customerId = '';
			if (count($searchResults->data) != 0) {

				$customerId = $searchResults->data[0]->id;
				$payment = new \Stripe\PaymentMethod($users_company_details['stripe_card_id']);
				$paymentdata = $payment->attach(
					['customer' => $searchResults->data[0]->id]
				);

				$customer = \Stripe\Customer::update($searchResults->data[0]->id, array(
					'invoice_settings' => [
						'default_payment_method' => $users_company_details['stripe_card_id'],
					],
				));

			} else {
				$customer = \Stripe\Customer::create(array(
					'name' => $GLOBALS["loguser"]['name'],
					'email' => $GLOBALS["loguser"]['email'],
				));

				$customerId = $customer['id'];

				$payment = new \Stripe\PaymentMethod($users_company_details['stripe_card_id']);
				$paymentdata = $payment->attach(
					['customer' => $customer['id']]
				);

				$customer = \Stripe\Customer::update($customer['id'], array(
					'invoice_settings' => [
						'default_payment_method' => $users_company_details['stripe_card_id'],
					],
				));

			}
			$fields = $this->db->query("SELECT * from settings Limit 1")->row_array();
			if ($sg_1 == 'programId') {
				$itemPrice = number_format($fields['program_fee'], 2);
			} else {
				$itemPrice = number_format($fields['review_fee'], 2);
			}

			$itemPriceCents = ($itemPrice * 100);

			$PaymentIntent = \Stripe\PaymentIntent::create(array(
				'amount' => $itemPriceCents,
				'currency' => $this->CI->config->item('stripe_currency'),
				'customer' => $customerId,
				'payment_method' => $users_company_details['stripe_card_id'],
				'automatic_payment_methods' => [
					'enabled' => true,
				],
				'off_session' => true,
				'confirm' => true,
			));

			if (isset($PaymentIntent->charges->data[0]) && $PaymentIntent->charges->data[0]->id != '' && $PaymentIntent->charges->data[0]->paid == '1') {
				if ($sg_1 == 'programId') {
					$this->db->query("UPDATE clients set date_of_first_program='" . date('Y-m-d H:i:s') . "' where client_id ='" . $client_id . "'");
					$this->db->query("UPDATE users_company set account_type = '0' where id ='" . $GLOBALS['loguser']['id'] . "'");
					$this->db->query("UPDATE account_payment_info set account_type = '0' where company_id ='" . $GLOBALS['loguser']['id'] . "'");

					$orderData = array(
						'company_id' => $GLOBALS["loguser"]["id"],
						'account_name' => $users_company_details['name'],
						'account_email' => $users_company_details['email'],
						'amount_paid' => $itemPrice,
						'discount_amount' => 0,
						'promo_code' => '',
						'paid_amount_currency' => $this->CI->config->item('stripe_currency'),
						'txn_id' => $PaymentIntent->charges->data[0]->balance_transaction,
						'payment_status' => $PaymentIntent->charges->data[0]->status,
					);
					//$orderID = $this->product->insertOrder($orderData);
					$this->db->insert("payments", $orderData);

					return true;
				} else {
					$this->db->query("UPDATE clients set date_initially_viewed='" . date('Y-m-d H:i:s') . "'  where client_id ='" . $client_id . "'");
					$this->db->query("UPDATE users_company set account_type = '0' where id ='" . $GLOBALS['loguser']['id'] . "'");
					$this->db->query("UPDATE account_payment_info set account_type = '0' where company_id ='" . $GLOBALS['loguser']['id'] . "'");

					$orderData = array(
						'company_id' => $GLOBALS["loguser"]["id"],
						'account_name' => $users_company_details['name'],
						'account_email' => $users_company_details['email'],
						'amount_paid' => $itemPrice,
						'discount_amount' => 0,
						'promo_code' => '',
						'paid_amount_currency' => $this->CI->config->item('stripe_currency'),
						'txn_id' => $PaymentIntent->charges->data[0]->balance_transaction,
						'payment_status' => $PaymentIntent->charges->data[0]->status,
					);
					//$orderID = $this->product->insertOrder($orderData);
					$this->db->insert("payments", $orderData);

					$this->session->set_flashdata('success', ' Payment has been successfully received. You can now view the latest analysis for the client.');
					redirect(base_url($sg_1 . '/customer/current_analysis/' . $client_id));
				}

			} else {

				$orderData = array(
					'company_id' => $GLOBALS["loguser"]["id"],
					'account_name' => $users_company_details['name'],
					'account_email' => $users_company_details['email'],
					'amount_paid' => $itemPrice,
					'discount_amount' => 0,
					'promo_code' => '',
					'paid_amount_currency' => $this->CI->config->item('stripe_currency'),
					'txn_id' => '',
					'payment_status' => $PaymentIntent->charges->data[0]->status ?? 'failed',
				);
				//$orderID = $this->product->insertOrder($orderData);
				$this->db->insert("payments", $orderData);
				$this->session->set_userdata('error', 'We were unable to charge your card. Please try again later or check your card details.');
				redirect(base_url("account/dashboard"));

			}

		} catch (Exception $e) {

			// send mail to support mentioning error message
			$cmpR = $this->crm_model->get_company_details($GLOBALS["loguser"]["id"]);

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
			$this->crm_model->send_email($data);

			$this->api_error = $e->getMessage();
			$this->session->set_userdata('error', $this->api_error);

			redirect(base_url("account/dashboard"));

		}
		// $this->session->set_flashdata('success', 'kkkkkkk');
		// $this->session->set_flashdata('error', 'jj');
		// $this->session->set_flashdata('success', 'Record successfully saved.');
		// redirect(base_url("account/dashboard"));

		// $customer = $this->stripe_lib->addCustomer($GLOBALS["loguser"]['name'], $GLOBALS["loguser"]['email'], $users_company_details['stripe_card_id']);
		// $charge = $this->stripe_lib->createCharge($paymentdata['customer'], $GLOBALS["loguser"]['name'],$fields['review_fee']);

	}

	//    Check Strong Password
	public function check_strong_password($password) {
		$passwordErr = "";
		if (!preg_match("#[0-9]+#", $password)) {
			$passwordErr = "Your Password Must Contain At Least 1 Number!";
		} elseif (!preg_match("#[A-Z]+#", $password)) {
			$passwordErr = "Your Password Must Contain At Least 1 Capital Letter!";
		} elseif (!preg_match("#[a-z]+#", $password)) {
			$passwordErr = "Your Password Must Contain At Least 1 Lowercase Letter!";
		} elseif (!preg_match("#[\W]+#", $password)) {
			$passwordErr = "Your Password Must Contain At Least 1 Special Character!";
		} else {}

		if ($passwordErr != "") {
			$this->form_validation->set_message('check_strong_password', $passwordErr);
			return false;
		}
		return true;
	}

	//    Check email Password has no space
	public function check_password_with_space($password) {
		$passwordErr = "";
		if (preg_match("#[\s]+#", $password)) {
			$passwordErr = "Your Email Password Cannot Contain Space!";
		} else {}

		if ($passwordErr != "") {
			$this->form_validation->set_message('check_password_with_space', $passwordErr);
			return false;
		}
		return true;
	}

	//    Check Mobile Number Validation
	public function check_mobile_number_validation($mobile) {
		//$justNumbers = preg_replace('/\D/', $mobile);
		$arr_rep = ['-', ' ', '(', ')', '[', ']', '+'];
		$justNumbers = str_replace($arr_rep, '', $mobile);
		if (strlen($justNumbers) == 10) {
			return true;
		} else {
			$this->form_validation->set_message('check_mobile_number_validation', "Please add your full 10-digit phone number.");
			return false;
		}
	}

	//    Check Credit Card
	public function check_cc($cc, $extra_check = false) {
		$cards = array(
			"visa" => "(4\d{12}(?:\d{3})?)",
			"amex" => "(3[47]\d{13})",
			"jcb" => "(35[2-8][89]\d\d\d{10})",
			"maestro" => "((?:5020|5038|6304|6579|6761)\d{15}(?:\d\d)?)",
			"solo" => "((?:6334|6767)\d{12}(?:\d\d)?\d?)",
			"mastercard" => "(5[1-5]\d{14})",
			"switch" => "(?:(?:(?:4903|4905|4911|4936|6333|6759)\d{12})|(?:(?:564182|633110)\d{10})(\d\d)?\d?)",
		);
		$names = array("Visa", "American Express", "JCB", "Maestro", "Solo", "Mastercard", "Switch");
		$matches = array();
		$pattern = "#^(?:" . implode("|", $cards) . ")$#";
		$result = preg_match($pattern, str_replace(" ", "", $cc), $matches);
		if ($extra_check && $result > 0) {
			$result = (validatecard($cc)) ? 1 : 0;
		}
		//return ($result>0)?$names[sizeof($matches)-2]:false;
		if ($result > 0) {
			return true;
		}

		$this->form_validation->set_message('check_cc', "Invalid Card No");
		return false;
	}

	//    Logout
	public function logout() {
		$cmpR = $this->crm_model->get_company_details($GLOBALS["loguser"]["company_id"]);
		//$this->session->set_userdata('userid', '');
		$this->session->unset_userdata('userid');
		$this->session->sess_destroy();
		redirect(base_url($cmpR['slug'] . '/account'));
	}

	//    Company Advertisement Management
	public function advertisement() {
		$this->check_login_session(); // Check Login Session
		$this->crm_model->check_account_segment_and_redirect("account", "advertisement"); // Check Account Segment and Redirect

		$page_data = array();
		$page_name = "list.php";
		$page_data['data']['name'] = "Advertisement";
		$page_data['data']['meta_title'] = "Advertisement";
		if ($this->uri->segment(3) == "new") {
			$page_name = "new";
			$page_data['data']['name'] = "New Advertisement";
			$page_data['data']['meta_title'] = "New Advertisement";}
		if ($this->uri->segment(3) == "edit") {
			$page_name = "new";
			$page_data['data']['name'] = "Edit Advertisement";
			$page_data['data']['meta_title'] = "Edit Advertisement";}
		if ($this->uri->segment(3) == "view") {
			$page_name = "view";
			$page_data['data']['name'] = "View Advertisement";
			$page_data['data']['meta_title'] = "View Advertisement";}

		//    Add Edit Records
		if ($this->uri->segment(3) == "new" || $this->uri->segment(3) == "edit") {
			/* Set validation rule for name field in the form */
			$this->form_validation->set_rules('name', 'Name', 'trim|required');

			if ($this->form_validation->run() == false) {
				$this->session->set_flashdata('error', validation_errors());
			} else {
				$result = $this->crm_model->admin_advertisement($this->uri->segment(4), 'Customer');
				if ($result['error'] != '') {
					$this->session->set_flashdata('error', $result['error']);
				} else {
					$this->session->set_flashdata('success', 'Record successfully saved.');
					if ($this->uri->segment(3) == "new") {
						redirect(base_url('account/advertisement/view/' . $result['id']));
					} else {
						redirect(base_url('account/advertisement/edit/' . $this->uri->segment(4)));
					}
				}
			}
		}

		//    Delete Record
		if ($this->uri->segment(3) == "delete") {
			$role = $GLOBALS["loguser"]["role"];
			if ($role == "Company") {$company_id = $GLOBALS["loguser"]["id"];} elseif ($role == "Company User") {$company_id = $GLOBALS["loguser"]["company_id"];} else { $company_id = "";}

			$this->db->query("DELETE FROM users_advertisement where id='" . $this->uri->segment(4) . "' and company_id='$company_id'");
			$this->session->set_flashdata('success', 'Record successfully delete.');
			redirect(base_url('account/advertisement'));

		}

		$this->load->view('account/Advertisement/' . $page_name, $page_data);
	}

	//    Approve/reject user registration
	public function account_request($slug, $id, $status) {
		$this->check_login_session(); // Check Login Session

		$user = $this->db->query('select * from users where id = ' . base64_decode($id))->row_array();
		$smtp = $this->crm_model->get_company_smtp_email_details($GLOBALS["loguser"]['company_id']);
		$cmpR = $this->crm_model->get_company_details($GLOBALS["loguser"]['company_id']);

		if (isset($user['id'])) {
			$update = [
				'status' => ($status == 'approve') ? 'Active' : 'Rejected',
			];
			$this->db->where('id', $user['id']);
			$this->db->update('users', $update);

			if ($status == 'approve') {

				$this->account_model->add_case_manager_setting($user['id']);
				$this->crm_model->admin_create_payment_installment($user['id']); //    Create Payment Installment

				$query = $this->db->query("SELECT * FROM users where id=" . $user['id']);
				$result = $query->row_array();

				$subject = 'Student Loan Tool Box - Account Approval';
				$Msg = $cmpR['email_header'] . '<div style="font-family:Calibri; font-size:15px; width:100%; text-align:justify; padding:2px 15px;">
				<p>Dear ' . $result['name'] . ',<br />Welcome to Student Loan Tool Box</p>
				<p>Your account has been approved. Please login to below URL using the credentials shared to you before in your welcome mail.</p>
				<p><a href="' . base_url($cmpR['slug'] . "/account") . '">Click Here to Login</a></p>
				<p>---<br /><strong>Warm Regards</strong><br />Student Loan Tool Box<br />' . base_url() . '</p>
				</div>';
				$smtp['email'] = $result['email'];
				$smtp['Msg'] = $Msg;
				$smtp['subject'] = $subject;
				$this->crm_model->send_email($smtp);

			} else {
				$query = $this->db->query("SELECT * FROM users where id=" . $user['id']);
				$result = $query->row_array();

				$subject = 'Student Loan Tool Box - Account Rejected';
				$Msg = $cmpR['email_header'] . '<div style="font-family:Calibri; font-size:15px; width:100%; text-align:justify; padding:2px 15px;">
				<p>Dear ' . $result['name'] . ',<br /></p>
				<p>Company owner could not verify your account details and has rejected your request.</p>
				<p>---<br /><strong>Warm Regards</strong><br />Student Loan Tool Box<br />' . base_url() . '</p><p>If you have any questions, please contact company owner at <a href="mailto:' . $GLOBALS["loguser"]['email'] . '">' . $GLOBALS["loguser"]['email'] . '</a></p>

				</div>';
				$smtp['email'] = $result['email'];
				$smtp['Msg'] = $Msg;
				$smtp['subject'] = $subject;
				$this->crm_model->send_email($smtp);
			}
		} else {
			$this->session->set_flashdata('error', 'Invalid Request');
		}
		redirect(base_url($cmpR['slug'] . '/team'));
	}

	//    Company Clients Management
	public function customer() {
		$this->check_login_session(); // Check Login Session
		$this->crm_model->check_account_segment_and_redirect("account", "customer"); // Check Account Segment and Redirect

		$page_data = array();
		$seg_3 = $this->uri->segment(3);

		// echo $_GET['result'];die;
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

		//    Delete Customer
		if ($this->uri->segment(3) == "delete") {$this->crm_model->delete_customer();}

		//    Add Edit Document
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

				if ($this->form_validation->run() == false) {
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

		//    Add Edit Document
		if ($this->uri->segment(3) == "document") {
			if ($this->uri->segment(5) == "delete") {$this->crm_model->admin_document_self_delete($this->uri->segment(6));}
			if ($this->uri->segment(5) == "view") {
				if (isset($_POST['submit_self_download'])) {$this->crm_model->admin_document_self_download($this->uri->segment(6));}
				if (isset($_POST['submit_custom_download'])) {$this->crm_model->admin_document_custom_download($this->uri->segment(6));}
			}

			/* Set validation rule for name field in the form */
			$this->form_validation->set_rules('document_name', 'Document Name', 'required');
			//$this->form_validation->set_rules('file_client_document', 'Document File', 'required');

			if ($this->form_validation->run() == false) {
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

		//    Add Edit Records
		if ($this->uri->segment(3) == "stop_program") {
			$result = $this->programs_model->stop_program();
			$this->session->set_flashdata('success', 'Program successfully stop.');
			redirect(base_url('account/customer/add_program/' . $this->uri->segment(4)));
			exit;
		}

		//    Add Edit Records
		if ($this->uri->segment(3) == "add_program") {

			$client_id = $this->uri->segment(4);
			$red_url = "account/customer/add_program/" . $client_id;

			// $programId = $this->uri->segment(5);
			// if($programId){
			//     $accountType = $this->db->query("SELECT * from account_payment_info where `company_id` = {$GLOBALS['loguser']['id']}")->row_array();
			//     $clientData = $this->db->query("SELECT * from clients where `client_id` = {$client_id}")->row_array();

			//     if($accountType['account_type'] == 0){
			//         if(!$clientData['date_of_first_program']){

			//             $payment = $this->paywall_payment_process($client_id,'programId');
			//             $_POST['program_id'] = str_replace("program_","",$programId);
			//             $_POST['Submit_'] = '';
			//             // echo "<pre>";
			//             // print_r($_POST);
			//             // echo "</pre>";
			//             // die('111');

			//         }
			//     }
			// }

			if ($_POST['addpayg'] == 'addpayg') {
				$payment = $this->paywall_payment_process($client_id, 'programId');
			}

			if (isset($_POST['Submit_cps6'])) {
				if (isset($_POST['current_program'])) {
					$this->session->set_userdata('current_programtab', $_POST['current_program']);
				}
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

			if ($this->form_validation->run() == false) {

				$this->session->set_flashdata('error', validation_errors());
			} else {

				$result = $this->crm_model->admin_users_add_program($this->uri->segment(4), $_POST['program_id']);
				if ($result['error'] != '') {
					$this->session->set_flashdata('error', $result['error']);
				} else {
					$this->session->set_flashdata('success', 'Program successfully added.' . ($_POST['program_id'] == '127' ? 'Update Intake email is sent to client.' : ''));
					redirect(base_url('account/customer/status/' . $this->uri->segment(4) . '/complete/' . $result['program_definition_id']));
					//redirect(base_url('account/customer/add_program/'.$this->uri->segment(4)));
				}
			}

		}

		//    Complete Program Step
		if (($this->uri->segment(3) == "status" || $this->uri->segment(3) == "add_program") && $this->uri->segment(5) == "complete") {

			$cnd = "client_id='" . $this->uri->segment(4) . "' and program_definition_id='" . $this->uri->segment(6) . "'";
			$chkr = $this->default_model->get_arrby_tbl('client_program_progress', '*', $cnd, '1');

			if (!isset($chkr[0])) {redirect(base_url("account/customer/add_program/" . $this->uri->segment(4)));exit;}
			$chkr = $chkr["0"];

			$pr = $this->default_model->get_arrby_tbl('program_definitions', '*', "program_definition_id='" . $chkr['program_id'] . "'", '1');
			$pr = $pr["0"];
			if (stripos($this->uri->segment(7), 'current_') !== false) {
				$this->session->set_userdata('current_programtab', explode('_', $this->uri->segment(7))[1]);
			}

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

	//    Add/Edit Customer
	public function customer_add_edit() {
		$this->check_login_session(); // Check Login Session
		$this->crm_model->check_account_segment_and_redirect("account", "customer"); // Check Account Segment and Redirect

		$page_data = array();
		$seg_1 = $this->uri->segment(1);
		if ($this->uri->segment(3) == "edit") {
			$page_data['data']['name'] = $page_data['data']['meta_title'] = "Edit Client";
		} else { $page_data['data']['name'] = $page_data['data']['meta_title'] = "Create New Client";}

		/* Set validation rule for name field in the form */
		$this->form_validation->set_rules('name', 'Customer Name', 'required');
		$this->form_validation->set_rules('phone', 'Phone Number', 'required|callback_check_mobile_number_validation');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
		$this->form_validation->set_rules('parent_id', 'Case Manager', 'required');

		if ($this->form_validation->run() == false) {
			$this->session->set_flashdata('error', validation_errors());
		} else {
			$advertisement_id = "";
			$result = $this->account_model->add_client($this->uri->segment(4), $_POST['company_id'], $_POST['parent_id'], $advertisement_id);
			if ($result['error'] != '') {
				$this->session->set_flashdata('error', $result['error']);
			} else {
				$this->session->set_flashdata('success', 'Record successfully saved.');
				if ($this->uri->segment(3) == "new") {
					redirect(base_url($seg_1 . '/customer/view/' . $result['id']));
				} else {
					redirect(base_url($seg_1 . '/customer/edit/' . $this->uri->segment(4)));
				}
			}
		}

		$this->load->view('account/users/new', $page_data);
	}

	//    Customer Intake Summary
	public function customer_intake_summary() {
		$this->check_login_session(); // Check Login Session
		$this->crm_model->check_account_segment_and_redirect("account", "customer"); // Check Account Segment and Redirect

		$page_data = array();
		$seg_1 = $this->uri->segment(1);

		$client_id = $this->uri->segment(4);
		$intake_id = 1;

		if ($this->db->query('select * from intake_client_status where client_id=' . $client_id . ' and intake_id=4')->num_rows() > 0) {
			$intake_id = 4;
		}
		$page_data = $this->crm_model->get_client_analysis_results($client_id, $intake_id);

		$page_data['intake_data'] = $this->crm_model->get_intake_page_with_data($client_id, $intake_id);
		$page_data['intake_id'] = $intake_id;

		if ($GLOBALS["loguser"]["role"] == "Company") {$loginas = "company_id";} else { $loginas = "company_id";}
		// if ($GLOBALS["loguser"]["role"] == "Company") {$loginas = "company_id";} else { $loginas = "parent_id";}
		if ($page_data['client_data']['client'][$loginas] != $GLOBALS["loguser"]["company_id"]) {redirect(base_url($seg_1 . "/customer"));exit;}

		$page_data['data']['name'] = $page_data['data']['meta_title'] = "Intake Summary";
		$this->load->view('account/users/Intake/intake_summary', $page_data);
	}

	//    Customer Current Analysis
	public function customer_current_analysis() {
		$this->check_login_session(); // Check Login Session
		$this->crm_model->check_account_segment_and_redirect("account", "customer"); // Check Account Segment and Redirect

		$page_data = array();
		$seg_1 = $this->uri->segment(1);

		$client_id = $this->uri->segment(4);
		$page_data = $this->crm_model->get_client_analysis_results($this->uri->segment(4));

		if ($GLOBALS["loguser"]["role"] == "Company") {$loginas = "company_id";} else { $loginas = "company_id";}
		// if ($GLOBALS["loguser"]["role"] == "Company") {$loginas = "company_id";} else { $loginas = "parent_id";}

		if ($page_data['client_data']['client'][$loginas] != $GLOBALS["loguser"]["company_id"]) {redirect(base_url($seg_1 . "/customer"));exit;}

		if (isset($_GET['download'])) {$this->crm_model->intake_file_download($client_id, $_GET['download']);}

		//print_r("<pre>");
		//print_r($page_data['intake']);
		//exit;

		/* Set validation rule for name field in the form */
		//$this->form_validation->set_rules('internal_notes', 'Internal Notes', 'required');
		$check = 0;

		$rev = $this->db->query('select * from client_analysis_results where client_id=' . $client_id . ' order by intake_id desc')->row_array();

		if (isset($rev['id']) && !empty($rev['par_csd'])) {

			$check = $this->db->query('select count(cp.id) from client_program cp join program_definitions pd on cp.program_definition_id=pd.program_definition_id where cp.client_id=' . $client_id . ' and program_title not in ("DOJ Adversary Procedure","Administrative Discharges")')->num_rows();
		}

		if ($check > 0) {
			$this->form_validation->set_rules('scenario_selected', 'Select Scenario', 'required');
			$this->form_validation->set_rules('consent', 'IRS Approval', 'required');
			$this->form_validation->set_rules('payment_plan_selected', 'Payment Plan', 'required');
		}

		$this->form_validation->set_rules('client_id', 'Client id', 'required');

		if ($this->form_validation->run() == false) {
			$this->session->set_flashdata('error', validation_errors());
		} else {
			$result = $this->crm_model->admin_save_client_analysis_results();
			if ($result['error'] != '') {
				$this->session->set_flashdata('error', $result['error']);
			} else {
				if (isset($_POST['submit_ca_and_close'])) {
					redirect(base_url($seg_1 . '/customer/view/' . $this->uri->segment(4)));
				} else {
					$this->session->set_flashdata('success', 'Record successfully saved.');
					redirect(base_url($seg_1 . '/customer/current_analysis/' . $this->uri->segment(4)));
				}
			}
		}
		$intake_id = 1;

		if ($this->db->query('select * from intake_client_status where client_id=' . $client_id . ' and intake_id=4')->num_rows() > 0) {
			$intake_id = 4;
		}

		$page_data['data']['name'] = $page_data['data']['meta_title'] = "Current Analysis";
		$page_data['intake_id'] = $intake_id;
		$this->load->view('account/users/current_analysis', $page_data);
	}

	//    Print - Customer Current Analysis
	public function customer_current_analysis_print() {
		$this->check_login_session(); // Check Login Session
		$this->crm_model->check_account_segment_and_redirect("account", "customer"); // Check Account Segment and Redirect

		$page_data = array();
		$seg_1 = $this->uri->segment(1);
		$client_id = $this->uri->segment(4);
		$page_data = $this->crm_model->get_client_analysis_results($client_id);

		if ($GLOBALS["loguser"]["role"] == "Company") {$loginas = "company_id";} else { $loginas = "company_id";}
		// if ($GLOBALS["loguser"]["role"] == "Company") {$loginas = "company_id";} else { $loginas = "parent_id";}
		if ($page_data['client_data']['client'][$loginas] != $GLOBALS["loguser"]["company_id"]) {redirect(base_url($seg_1 . "/customer"));exit;}

		@extract($page_data['client_data']['client']);
		$intake_id = 1;

		$location = strtolower(trim($page_data['intake'][4]['ans']['intake_comment_body']));
		if ($location == "hawaii" || $location == "hi" || $location == "sh") {
			$location = "HI";
		} else if ($location == "alaska" || $location == "ak" || $location == "sa") {
			$location = "AK";
		} else {}

		$data_print = array("data_type" => "Print", "client_id" => $client_id, "company_id" => $company_id, "intake_id" => $intake_id, "nslds_id" => $page_data['intake'][6]['ans']['intake_file_id'], "location" => $location, "intake_file_result_id" => $page_data['intake'][6]['ans']['intake_file_id'], "scenario_selected" => $page_data['car']['scenario_selected'], "family_size" => $page_data['car']['family_size'], "marital_status" => $page_data['car']['marital_status'], "file_joint_or_separate" => $page_data['car']['file_joint_or_separate'], "client_agi" => $page_data['car']['client_agi'], "client_monthly" => $page_data['car']['client_monthly'], "spouse_agi" => $page_data['car']['spouse_agi'], "spouse_monthly" => $page_data['car']['spouse_monthly']);

		$page_data['data_print_text'] = $this->client_current_analysis_payment_scenario($data_print);
		$page_data['data']['name'] = $page_data['data']['meta_title'] = "Current Analysis";
		$this->load->view('account/users/current_analysis_print', $page_data);

		// Get output html
		$html = trim($this->output->get_output());

		// Load pdf library
		$this->load->library('pdf');

		// Load HTML content
		$this->dompdf->loadHtml($html);

		// (Optional) Setup the paper size and orientation
		//$this->dompdf->setPaper('A4', 'landscape');
		$this->dompdf->setPaper('DEFAULT_PDF_PAPER_SIZE', 'portrait');

		// Render the HTML as PDF
		$this->dompdf->render();

		// Output the generated PDF (1 = download and 0 = preview)
		$this->dompdf->stream("Current Analysis", array("Attachment" => 0));
		exit;

	}

	//    Customer Current Analysis Payment Scenario
	public function client_current_analysis_payment_scenario($data = array()) {
		if (isset($data['data_type'])) {$_POST = $data;}
		if (isset($_POST)) {
			@extract($_POST);
			$tdata = '';
			$res = $this->array_model->stlb_ca_payment_plan_scenario_group($marital_status, $file_joint_or_separate, $location);
			$res2 = $this->crm_model->client_current_analysis_payment_scenario($_POST, $res);

			$check = 0;

			$rev = $this->db->query('select * from client_analysis_results where client_id=' . $client_id . ' order by intake_id desc')->row_array();

			if (isset($rev['id']) && !empty($rev['par_csd'])) {
				$check = $this->db->query('select count(cp.id) from client_program cp join program_definitions pd on cp.program_definition_id=pd.program_definition_id where cp.client_id=' . $client_id . ' and program_title not in ("DOJ Adversary Procedure","Administrative Discharges")')->num_rows();
			}

			$required = ($check > 0) ? ' required' : '';

			$nslds_num = $this->db->query("SELECT * FROM nslds_loans where client_id='$client_id' and loan_outstanding_principal_balance > 0")->num_rows();

			if ($nslds_num == 0) {
				$required = '';
			}

			if (!isset($_POST['data_type'])) {
				$tdata = '<tr class="info" style="font-weight:bold;">	<td width="160">Scenario Name</td>';
				foreach ($res as $rows) {foreach ($rows as $row) {$tdata .= '<td width="110">' . $row . '</td>';}}
				$tdata .= '<!--<td>Formula</td>-->	<td>Notes</td></tr><tr class="info" style="font-weight:bold;"><td>Select Scenario <input type="radio" name="scenario_selected" value="" checked style="display:none;" /></td>';
				foreach ($res as $k => $rows) {
					foreach ($rows as $k2 => $row) {
						$snro = $k . '_' . $k2 . '_' . $row;
						$checked = "";
						if (trim($scenario_selected) != "" && $scenario_selected == $snro) {$checked = " checked";}
						$tdata .= '<td><input type="radio" name="scenario_selected" value="' . $snro . '" ' . $checked . $required . '></td>';
					}
				}
				$tdata .= '<td colspan="2"></td></tr>';
				$tdata .= '<tr>	<td>Payment Plans</td>	<td colspan="2"><select name="payment_plan_selected" class="form-control" style="max-width:200px;" ' . $required . '><option value="">Select Plan</option>';
				foreach ($res2 as $k => $row2) {
					$selected = "";
					if (trim($payment_plan_selected) != "" && $payment_plan_selected == ($row2['name'] == 'SAVE (Formerly REPAYE)' ? 'REPAYE' : $row2['name'])) {$selected = " selected";}
					$tdata .= '<option value="' . ($row2['name'] == 'SAVE (Formerly REPAYE)' ? 'REPAYE' : $row2['name']) . '" ' . $selected . '>' . $row2['name'] . '</option>';
				}

				$tdata .= '</select></td><td><div class="form-check"><input type="radio" name="consent" value="5a" class="form-check-input" ' . ($consent == '5a' ? 'checked' : '') . $required . '/> Client APPROVES of he IRS using tax returns to recertify every year.</div><div class="form-check"><input type="radio" name="consent" value="5b" class="form-check-input" ' . ($consent == '5b' ? 'checked' : '') . $required . '/> Client DOES NOT APPROVE of he IRS using tax returns to recertify every year.</div></td></tr>';

				foreach ($res2 as $k => $row2) {
					$tdata .= '<tr><td>' . $row2['name'] . '</td>';
					foreach ($res as $k => $rows) {
						foreach ($rows as $k2 => $row) {
							if (trim($row2['value'][$k2])) {
								if (strpos($row2['name'], "Standard Plan") !== false) {$tdid = "scnerio_plan_" . $k2;} else { $tdid = "scnerio_plan_" . rand("123", "999");}
								$tdata .= '<td id="' . $tdid . '">' . $row2['value'][$k2] . '</td>';
							}}
					}
					//$tdata .= '<td>'.$row2['formula'].'</td>';
					$tdata .= '<td>' . $row2['notes'] . '</td></tr>';
				}

				//$tdata .= '<tr>    <td>Formula Values</td> <td colspan="5">'.$res2[6]['formula_input'].'</td></tr>';

			} else {
				$tdata .= '<tr><td width="100">Payment Plan</td>	<td width="100">Monthly Payment Amount</td>	<td>Notes</td>	</tr>';

				foreach ($res2 as $j => $row2) {
					foreach ($res as $k => $rows) {
						foreach ($rows as $k2 => $row) {
							$snro = $k . '_' . $k2 . '_' . $row;
							if (trim($scenario_selected) != "" && $scenario_selected == $snro && $row2['value'][$k2] != "N/A") {
								if (!preg_match('/\bStandard Plan\b/', $row2['name'])) {
									$tdata .= '<tr valign="top"><td>' . $row2['name'] . '</td><td>' . $row2['value'][$k2] . '</td><td>' . $row2['notes'] . '</td></tr>';
								}
							}
						}
					}
				}

			}

			if (!isset($_POST['data_type'])) {echo $tdata;} else {return $tdata;}
		}
	}

	//    Customer Attestation Form
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

	//    Company Intake Client Status Change
	public function customer_intake_client_status_change() {
		$client_id = $this->uri->segment(3);
		$this->db->where(['client_id' => $client_id, 'id' => $this->uri->segment(4)]);
		$this->db->update('intake_client_status', ['status2' => 'Save']);

		$q = $this->db->query("SELECT * FROM intake_client_status where client_id='$client_id' and id='" . $this->uri->segment(4) . "'");
		$icsr = $q->row_array();
		$intake_id = $icsr['intake_id'];
		$this->crm_model->add_intake_document($client_id, $intake_id, $status = 'Hide');
	}

	//    Send Intake Email
	public function send_intake_email() {
		$result = $this->crm_model->admin_send_intake_email($_POST['uid']);
		if ($result['error'] != '') {echo "Failed";}
	}

	//    Stop Reminder Emails
	public function cap_stop_remonder() {
		$result = $this->crm_model->admin_cap_stop_remonder($_POST['client_id']);
	}

	//    Reset ca Save Internal Notes
	public function reset_ca_save_internal_notes() {
		$varname = "ca_in_" . $_POST['client_id'];

		$this->session->unset_userdata($varname);

		if (trim($_POST['internal_notes']) != "") {
			$this->session->set_userdata($varname, $_POST['internal_notes']);
		}
	}

	//    Intake Form
	public function view_nslds_snapshot() {
		$this->load->view('account/users/customer_view_snapshot');
	}

	//    View Client Intake Form
	public function view_client_intake_form() {
		$this->load->view('account/users/customer_view_intake_form');
	}

	//    Document Management
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

		//    Self Download
		if ($this->uri->segment(3) == "view") {
			if (isset($_POST['submit_self_download'])) {$this->crm_model->admin_document_self_download($this->uri->segment(4));}
			if (isset($_POST['submit_custom_download'])) {$this->crm_model->admin_document_custom_download($this->uri->segment(4));}
		}

		//    Add Edit Records
		if ($this->uri->segment(3) == "" || $this->uri->segment(3) == "new" || $this->uri->segment(3) == "edit") {
			/* Set validation rule for name field in the form */
			$this->form_validation->set_rules('document_name', 'Document Name', 'required');
			//$this->form_validation->set_rules('file_client_document', 'Document File', 'required');

			if ($this->form_validation->run() == false) {
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

	//    Dashboard
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

	//    Change Payment Plan Scenario
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

	//    Run Current Analysis Scenario Latest
	public function run_current_analysis_scenario_group() {
		@extract($_POST);
		$output = $this->crm_model->run_current_analysis_scenario_group($_POST);
		echo $output;
	}
}
