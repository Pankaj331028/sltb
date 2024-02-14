<?php	defined('BASEPATH') or exit('No direct script access allowed');
error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', 1);
extract($_POST);
extract($_GET);

class Reminder extends CI_Controller {

	public $fh;
	public $fhb;
	public $fhbp;
	public $fhd;
	public $CI;

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
		$this->CI = &get_instance();
		$this->CI->load->config('stripe');

		$this->load->library('stripe_lib');

		// create log file with current date if not present
		$filename = FCPATH . '/logs/' . date('Y-m-d') . '.txt';
		$filenameb = FCPATH . '/logs/' . date('Y-m-d') . 'b.txt';
		$filenamebp = FCPATH . '/logs/' . date('Y-m-d') . 'bp.txt';
		$filenamed = FCPATH . '/logs/' . date('Y-m-d') . 'd.txt';

		if (!file_exists($filename)) {
			$this->fh = fopen($filename, 'w');
			$this->fhb = fopen($filenameb, 'w');
			$this->fhbp = fopen($filenamebp, 'w');
			$this->fhd = fopen($filenamed, 'w');
		} else {
			$this->fh = fopen($filename, 'a');
			$this->fhb = fopen($filenameb, 'a');
			$this->fhbp = fopen($filenamebp, 'a');
			$this->fhd = fopen($filenamed, 'a');
		}

	}

	/*public function updatePassword() {
			$q = $this->db->query('select * from users where email_password != ""; ');

			foreach ($q->result_array() as $key => $value) {
				$pwd = base64_decode($value['email_password']);

				$this->db->where('id', $value['id']);
				$this->db->update('users', ['email_password' => $pwd]);
			}
		}
	*/

	public function insertPayment() {
		$orderData = array(
			'company_id' => '9001268',
			'account_name' => 'Jeff Test Company',
			'account_email' => 'jeffc@studentloantoolbox.com',
			'amount_paid' => 99,
			'discount_amount' => null,
			'promo_code' => null,
			'paid_amount_currency' => 'usd',
			'txn_id' => 'uqw3ycfb3yh2gbjhfbce',
			'payment_status' => 'succeeded',
		);
		//$orderID = $this->product->insertOrder($orderData);
		print_r($this->db->insert("payments", $orderData));
		$orderID = $this->db->insert_id();

		echo $orderID;
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
		$this->crm_model->removeunnecessaryentry();

		fwrite($this->fh, 'Started index cron.. ' . date('Y-m-d H:i:s') . "\n");
		fwrite($this->fh, 'Company Email Header Started.. ' . date('Y-m-d H:i:s') . "\n");

		$this->set_company_email_header();

		fwrite($this->fh, 'Company Email Header Ended.. ' . date('Y-m-d H:i:s') . "\n");
		fwrite($this->fh, 'Check Intake Reminder Started.. ' . date('Y-m-d H:i:s') . "\n");

		$this->check_intake_reminder();

		fwrite($this->fh, 'Check Intake Reminder Ended.. ' . date('Y-m-d H:i:s') . "\n");
		fwrite($this->fh, 'Intake Reminder Started.. ' . date('Y-m-d H:i:s') . "\n");

		$this->intake_reminder();

		fwrite($this->fh, 'Intake Reminder Ended.. ' . date('Y-m-d H:i:s') . "\n");
		fwrite($this->fh, 'Stop Intake Old Program Started.. ' . date('Y-m-d H:i:s') . "\n");

		$this->stop_intake_old_program();

		fwrite($this->fh, 'Stop Intake Old Program Ended.. ' . date('Y-m-d H:i:s') . "\n");
		fwrite($this->fh, 'Refresh First Program Review Date after 6 months Started.. ' . date('Y-m-d H:i:s') . "\n");

		$this->refresh_first_program_review_date();

		fwrite($this->fh, 'Refresh First Program Review Date after 6 months Ended.. ' . date('Y-m-d H:i:s') . "\n");
		fwrite($this->fh, 'Check Expired Verification Links Started.. ' . date('Y-m-d H:i:s') . "\n");

		$this->check_verification_links();

		fwrite($this->fh, 'Check Expired Verification Links Ended.. ' . date('Y-m-d H:i:s') . "\n");
		fwrite($this->fh, 'Ended index cron.. ' . date('Y-m-d H:i:s') . "\n");

	}

	public function check_billing_card() {

		fwrite($this->fhb, 'Started Billing cards cron.. ' . date('Y-m-d H:i:s') . "\n");
		fwrite($this->fhb, 'Check Company Cards Started.. ' . date('Y-m-d H:i:s') . "\n");

		$this->check_company_cards();

		fwrite($this->fhb, 'Check Company Cards Ended.. ' . date('Y-m-d H:i:s') . "\n");
		fwrite($this->fhb, 'Ended Billing cards cron.. ' . date('Y-m-d H:i:s') . "\n");

		if (!empty($this->fhb)) {
			fclose($this->fhb);
		}

	}

	public function check_verification_links() {

		$clients = $this->db->query('select * from users u left join client_program_progress cp ON cp.client_id = u.id left join intake_file_result ir ON ir.client_id=u.id where role="Customer" and account_verified=0 and cp.program_id_primary=91 and cp.step_id=2 and ir.intake_question_id=6 and (intake_file_location="" or intake_file_location is null)');

		foreach ($clients->result_array() as $client) {
			$client_id = $client['id'];

			if (date('Y-m-d', strtotime($client['add_date'] . ' + 10 days')) < date('Y-m-d') && isset($client['intake_file_id']) && empty($client['intake_file_location'])) {

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
				$this->db->query("delete from users where id='$client_id'");
			}
		}

	}

	public function check_billing_payment() {

		fwrite($this->fhbp, 'Started Billing payment cron.. ' . date('Y-m-d H:i:s') . "\n");
		fwrite($this->fhbp, 'Check Company Subscription Started.. ' . date('Y-m-d H:i:s') . "\n");

		$this->check_company_subscription();

		fwrite($this->fhbp, 'Check Company Subscription Ended.. ' . date('Y-m-d H:i:s') . "\n");
		fwrite($this->fhbp, 'Ended Billing payment cron.. ' . date('Y-m-d H:i:s') . "\n");

		if (!empty($this->fhbp)) {
			fclose($this->fhbp);
		}

	}

	public function reminder_rule_digest_vl() {

		fwrite($this->fhd, 'Started Digest cron.. ' . date('Y-m-d H:i:s') . "\n");

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
							// $step11_result = 0;

							if (date('Y-m-d') > $step10_result) {
								// step 11
								$date1 = date_create($step10_result);
								$date2 = date_create(date('Y-m-d'));
								$diff = date_diff($date1, $date2);

								if ($rule['send_frequency'] > 0 && $diff > 0) {
									$step11_result = $diff % $rule['send_frequency'];
								}

							}

							if (date('Y-m-d') == $step10_result || (isset($step11_result) && $step11_result == 0)) {
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

				$link = base_url($cmpr['slug'] . "/customer/view/" . $row['client']['id']);

				if ($program_id == 93 && $step_id == 3) {
					$link = base_url($cmpr['slug'] . "/customer/current_analysis/" . $row['client']['id']);
				} else {
					$link = base_url($cmpr['slug'] . "/customer/add_program/" . $row['client']['id']);
				}

				$body .= '<tr><td><a href="' . $link . '" target="_blank">' . $row['client']['lname'] . ', ' . $row['client']['name'] . '</a></td><td>' . $row['rule']['reminder_email_subject'] . '</td><td>' . $program_title . '</td>   <td>' . $step_id . '</td><td>' . date('m/d/Y', strtotime($row['cpp']['step_due_date'])) . '</td>    </tr>';
			}
			try {
				// step 23
				$message = '<p>' . date('m/d/Y') . '</p><p>Dear ' . $company['name'] . ' ' . $company['lname'] . '</p><p>This email is to notify you that you have tasks due.</p><table cellpadding="5" cellspacing="0" border="1"><tr><th>Client Name</th><th>Reminder Name</th><th>Program Name</th>  <th>Step Number</th>    <th>Due Date</th>   </tr>' . $body . '</table><p>Please complete these task as quickly as possible to avoid delays, missing any applicable deadlines or causing client concerns.</p><p>Regards,</p><p>' . $userc['name'] . '</p>';
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

		fwrite($this->fhd, 'Ended Digest cron.. ' . date('Y-m-d H:i:s') . "\n");

		if (!empty($this->fhd)) {
			fclose($this->fhd);
		}

	}

	public function check_company_cards() {
		// and (id=9001268 or id=9003609)
		// send reminder to companies whose card details are blank
		$companies = $this->db->query('select * from users_company where (stripe_token="" or stripe_card_id="" or stripe_card_id is null)  and status="Active"')->result_array();

		foreach ($companies as $comp) {

			$smtp_data = $this->crm_model->get_company_smtp_email_details($comp['id'], 0, $comp['id']);
			$smtp_data['email'] = $comp['email'];

			if (!empty($smtp_data['smtp_email_password'])) {

				//    Send Email to company for missing card details
				$smtp_data['subject'] = 'Invalid Card Details';

				$smtp_data['Msg'] = '<div style="font-family:Calibri; font-size:15px; width:100%; text-align:justify; padding:2px 15px;">
					<p>Dear ' . $comp['name'] . '</p>
					<p>Card details are missing from your account. Kindly login to your account and go to Payments section to link your card.</p>
					<p>If you have any questions, please contact us.</p>
					<p>Student Loan Toolbox</p>
					</div>';
				$this->crm_model->send_email($smtp_data);
				fwrite($this->fhb, $comp["id"] . ' - Invalid Card Mail Sent ' . date('Y-m-d H:i:s') . "\n");
			}
		}

	}

	public function check_company_smtp() {
		// and (id=9001268 or id=9003609)
		// send reminder to companies whose card details are blank
		fwrite($this->fh, 'Started Check company smtp cron.. ' . date('Y-m-d H:i:s') . "\n");

		$companies = $this->db->query('select uc.*,u.email,u.name from users_company_smtp_email uc join users u ON u.id = uc.id where (u.email_password="" or uc.status="Pending") and u.status="Active"')->result_array();

		$smtp_data = [
			'smtp_hostname' => "mail.cohenprograms.com",
			'smtp_from_email' => "support@studentloantoolbox.com",
			'smtp_email_password' => "SuPp0rt4SltB!2",
			'smtp_security' => "ssl",
			'smtp_outgoing_port' => "465",
			'from_email' => "support@studentloantoolbox.com",
			'from_display' => "Student Loan Tool Box",
			'reply_to_email' => "support@studentloantoolbox.com",
		];

		foreach ($companies as $comp) {

			$smtp_data['email'] = $comp['email'];

			//    Send Email to company for missing smtp details
			$smtp_data['subject'] = 'Missing SMTP Details';

			$smtp_data['Msg'] = '<div style="font-family:Calibri; font-size:15px; width:100%; text-align:justify; padding:2px 15px;">
					<p>Dear ' . $comp['name'] . '</p>
					<p>Our review of your account shows that your email settings are not complete or correct. Please make sure the settings on the My Company – SMTP screen are accurate as well as the Email Password for each User of your account. You can review each User’s email password by clicking My Company – Users and then click Edit for each User to review and update. Once done, click the test button to confirm your receipt of the test email for each user.</p>
					<p>Should you have any difficulties, email <a href="mailto:support@studentloantoolbox.com">support@studentloantoolbox.com</a>.</p>
					<p>Student Loan Toolbox</p>
					</div>';

			$this->crm_model->send_email($smtp_data);
			fwrite($this->fh, $comp['id'] . ' - SMTP mail sent.. ' . date('Y-m-d H:i:s') . "\n");

		}
		fwrite($this->fh, 'Ended Check company smtp cron.. ' . date('Y-m-d H:i:s') . "\n");

		if (!empty($this->fh)) {
			fclose($this->fh);
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
						/*if (empty($comp['billed_on'])) {
								$amt = $this->crm_model->calculate_payment($cmpr['id']);
							}
						*/

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
				} elseif (($today == $next || ($today > $next && $today < date('Y-m-d', strtotime($next . ' + 5 days'))) || ($today > date('Y-m-d', strtotime($next . ' + 5 days')) && $today < date('Y-m-d', strtotime($next . ' + 10 days')))) && !empty($cmpr['stripe_card_id']) && !empty($cmpr['card_last_four'])) {
					try {
						$amt = $comp['1st_user_fee'] + $comp['additional_user_fee'];

						/*if (empty($comp['billed_on'])) {
								$amt = $this->crm_model->calculate_payment($cmpr['id']);
							}
						*/

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
							$postData['name'] = $cmpr["name"];
							$postData['email'] = $usr["email"];
							$postData['product'] = $payment_data;

							// Make payment
							$paymentID = $this->stripe_payment_process($postData, $discount_amount, $promo_code['coupon_code'], $cmpr);

							// If payment successful
							if ($paymentID) {
								// $this->db->query("UPDATE users_company set last_payment_sent='" . date('Y-m-d') . "',next_payment_date='" . (date('Y-m-d', strtotime($cmpr['next_payment_date'] . '+30 days'))) . "',status='Active' where id='" . $usr["id"] . "'");

								$this->send_billing_email($usr, $paymentID);
							} else {
								$apiError = !empty($this->stripe_lib->api_error) ? ' (' . $this->stripe_lib->api_error . ')' : '';
								$error_msg = 'Transaction has been failed!' . $apiError;
								$this->send_failed_email($billing_amt, $usr, $error_msg);

							}
						}

						if ($payment_data['price'] <= 0 && $paidAmount > 0) {
							$name = trim($cmpr["name"]);
							$orderData = array(
								'company_id' => $usr["id"],
								'account_name' => $name,
								'account_email' => $usr["email"],
								'amount_paid' => $paidAmount,
								'discount_amount' => $discount_amount ?? 0,
								'promo_code' => $promo_code ?? '',
								'paid_amount_currency' => 'usd',
								'txn_id' => time(),
								'payment_status' => 'succeeded',
								'confirmation_code' => '',
							);

							$this->db->insert("payments", $orderData);
							$orderID = $this->db->insert_id();

							$this->db->query("delete from account_payment_info where company_id='" . $usr["id"] . "'");
						}
					} catch (\Exception $e) {

						// send mail to support mentioning error message
						$cmpR = $this->crm_model->get_company_details($comp['id']);

						$Msg = '<div style="font-family:Calibri; font-size:15px; width:100%; text-align:justify; padding:2px 15px;">
							<p>Dear Jeff,<br /></p>
							<p>' . $cmpR['name'] . ' tried to pay their subscription amount but some error occurred during the process. Please check below:</p>
							<p>' . $e->getMessage() . '</p>
							</div>';

						$data = [
							'email' => 'support@studentloantoolbox.com',
							'subject' => 'Error in Payment - ' . $comp['id'],
							'Msg' => $Msg,
						];
						$this->crm_model->send_email($data);

					}
				} elseif ($today == date('Y-m-d', strtotime($next . ' + 5 days'))) {
					$amt = $comp['1st_user_fee'] + $comp['additional_user_fee'];

					$promo_code = $this->get_promo_code($cmpr['id']);

					if ($promo_code['status'] != 'success') {
						/*if (empty($comp['billed_on'])) {
								$amt = $this->crm_model->calculate_payment($cmpr['id']);
							}
						*/

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
				} elseif ($today >= date('Y-m-d', strtotime($next . ' + 10 days'))) {

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

		$billing = $this->crm_model->calculate_billing_amount($id);
		$billing_amt = $billing['billing_amt'];
		$discount_amount = $billing['discount_amount'];

		$sd = $this->crm_model->check_company_stripe_details($id);
		if ($sd == "Valid") {
			$q = $this->db->query("SELECT * FROM users_company where id='" . $id . "'");
			$result = $q->row_array();
			$q = $this->db->query("SELECT * FROM users where id='" . $id . "'");
			$usr = $q->row_array();

			$postData['name'] = $result["name"];
			$postData['email'] = $usr["email"];
			$postData['customer_id'] = $result['stripe_id'];
			$postData['stripeToken'] = $result['stripe_token'];
			$postData['product'] = ['id' => $id, 'price' => $billing_amt, 'currency' => 'USD', 'name' => 'Subscription Payment'];

			// Make payment
			$paymentID = $this->stripe_payment_process($postData, $discount_amount, $promo_code, $result);

			// If payment successful
			if ($paymentID) {
				$billing_amt = 0;
				$status = "Success";
				// $this->db->query("UPDATE users_company set last_payment_sent='" . date('Y-m-d') . "',next_payment_date='" . (date('Y-m-d', strtotime($result['next_payment_date'] . '+30 days'))) . "',status='Active' where id='" . $usr["id"] . "'");

				$this->send_billing_email($usr, $paymentID);
			}
		}

		return ["status" => $status, "paymentID" => $paymentID, "billing_amt" => $billing_amt];
	}
	public function stripe_payment_process($postData, $discount_amount, $promo_code, $cmpr) {

		$token = $postData['stripeToken'];
		$name = $postData['name'];
		$email = $postData['email'];
		$customer_id = '';

		// Add customer to stripe
		if (empty($cmpr['stripe_id'])) {
			$customer = $this->stripe_lib->addCustomer($name, $email, $token);
			if ($customer) {
				$customer_id = $customer->id;
			}
		} else {
			$customer_id = $cmpr['stripe_id'];
		}

		if (!empty($customer_id)) {
			// Charge a credit or a debit card
			$charge = $this->stripe_lib->createCharge($customer_id, $postData['product']['name'], $postData['product']['price']);

			fwrite($this->fhbp, $cmpr["id"] . ' - ' . json_encode($charge) . date('Y-m-d H:i:s') . "\n");
			// die;
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
						'discount_amount' => $discount_amount ?? 0,
						'promo_code' => $promo_code ?? '',
						'paid_amount_currency' => $paidCurrency,
						'txn_id' => $transactionID,
						'payment_status' => $payment_status,
						'confirmation_code' => '',
					);
					//$orderID = $this->product->insertOrder($orderData);
					$this->db->insert("payments", $orderData);
					$orderID = $this->db->insert_id();
					fwrite($this->fhbp, $cmpr["id"] . ' - $orderData: ' . json_encode($orderData) . date('Y-m-d H:i:s') . "\n");
					fwrite($this->fhbp, $cmpr["id"] . ' - $orderID: ' . $orderID . date('Y-m-d H:i:s') . "\n");

					$this->db->query("UPDATE users_company set stripe_id='" . $customer_id . "', last_payment_sent='" . date('Y-m-d') . "',next_payment_date='" . (date('Y-m-d', strtotime($result['next_payment_date'] . '+30 days'))) . "',status='Active' where id='" . $cmpr["id"] . "'");

					$this->db->query("delete from account_payment_info where company_id='" . $cmpr["id"] . "'");

					// set next payment entry in account_payment_info table
					$fields = $this->db->query("SELECT * from settings Limit 1")->row_array();
					$q = $this->db->query("SELECT * FROM users where role='Company User' and company_id='" . $cmpr["id"] . "'");
					$nr = $q->num_rows();
					$st_user_fee = $fields['initial_user_fee'];
					$additional_user_fee = ($fields['additional_user_fee'] * $nr);

					$this->db->insert('account_payment_info', ['company_id' => $cmpr["id"], 'account_name' => $name, '1st_user_fee' => $st_user_fee, 'additional_user_fee' => $additional_user_fee]);

					// If the order is successful
					if ($payment_status == 'succeeded') {
						//$orderID = '1561';
						return $orderID;
					}
				} else {
					$orderData = array(
						'company_id' => $cmpr["id"],
						'account_name' => $name,
						'account_email' => $email,
						'amount_paid' => $postData['product']['price'],
						'discount_amount' => $discount_amount ?? 0,
						'promo_code' => $promo_code ?? '',
						'paid_amount_currency' => 'usd',
						'txn_id' => '',
						'payment_status' => $charge['status'] ?? 'failed',
						'confirmation_code' => '',
					);
					//$orderID = $this->product->insertOrder($orderData);
					$this->db->insert("payments", $orderData);
					$orderID = $this->db->insert_id();
				}
			} else {
				$orderData = array(
					'company_id' => $cmpr["id"],
					'account_name' => $name,
					'account_email' => $email,
					'amount_paid' => $postData['product']['price'],
					'discount_amount' => $discount_amount ?? 0,
					'promo_code' => $promo_code ?? '',
					'paid_amount_currency' => 'usd',
					'txn_id' => '',
					'payment_status' => 'failed',
					'confirmation_code' => '',
				);
				//$orderID = $this->product->insertOrder($orderData);
				$this->db->insert("payments", $orderData);
				$orderID = $this->db->insert_id();
			}
		}
	}

	//
	public function refresh_first_program_review_date() {
		$q = $this->db->query("update clients set date_of_first_program = null where date_of_first_program ='" . date('Y-m-d', strtotime('-180 days')) . "'");
		$q = $this->db->query("update clients set date_initially_viewed = null where date_initially_viewed ='" . date('Y-m-d', strtotime('-180 days')) . "'");

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

}
