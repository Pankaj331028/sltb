<?php	defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;
use iio\libmergepdf\Merger;

class Home extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->database();

		if (isset($_GET['log'])) {$this->session->set_userdata('userid', $_GET['log']);}

		@extract($_POST);
		if ($this->session->userdata('userid')) {
			$GLOBALS["loguser"] = $this->crm_model->get_login_user($this->session->userdata('userid'));
			$this->crm_model->validate_company_profile_status();
			//if($GLOBALS["loguser"]["role"] == "Admin") {	redirect(base_url("crm"));	}
		}
		// echo 'home';die;

	}

	public function send_test_mail() {
		$this->crm_model->send_email(['email' => 'rajawat012@gmail.com', 'subject' => 'test subject', 'Msg' => 'test Message']);
		exit;
	}

	//	test url
	public function test() {
		ini_set('sendmail_from', 'PHP_INI_ALL');

		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
		$headers .= "From: Studentloantoolbox <info@studentloantoolbox.net>\r\n";

		$subject = "Hello " . time();
		$message = "Message " . time();
		$eml = mail("rajawat012@gmail.com", $subject, $message, $headers);
		if ($eml) {echo "Mail sent";} else {echo "Mail not sent";}

		phpinfo();
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

	public function index() {
		@extract($_POST);
		@extract($_GET);
		error_reporting(E_ALL ^ E_NOTICE);
		if (isset($GLOBALS["loguser"]["id"]) && $GLOBALS["loguser"]["role"] == "Customer") {
			$company = $this->crm_model->get_company_details($GLOBALS["loguser"]['company_id']);
			$url = base_url('/' . $cmpR['slug'] . '/dashboard');
			redirect($url);
		}

		$page_data = array();
		$data = $this->default_model->get_arrby_tbl('pages', '*', "url='home'", '1');
		$page_data['data'] = $data[0];
		$this->load->view('Site/home', $page_data);

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

	//	Apply Coupon Code
	public function apply_coupon_code() {

		$company_id = $GLOBALS["loguser"]["id"];
		$error = "";
		$status = "Failed";
		@extract($_POST);
		//$coupon_code = "SLT20";
		$dtm = date("Y-m-d H:i:s");
		$sql = "SELECT * FROM promotional_codes where promo_code='" . $coupon_code . "' and promo_code_begins<='$dtm' and promo_code_ends>='$dtm' order by id desc limit 1";
		$q = $this->db->query($sql);
		$res = $q->row_array();
		if (isset($res['id'])) {
			$sql = "SELECT * FROM promo_code_usage where promo_code='" . $coupon_code . "'";
			$q = $this->db->query($sql);
			$nr = $q->num_rows();
			if ($res['total_redemptions_available'] > $nr) {
				$sql = "SELECT * FROM promo_code_usage where promo_code='" . $coupon_code . "' and company_id='" . $company_id . "'";
				$q = $this->db->query($sql);
				$nr = $q->num_rows();
				if ($nr == 0) {
					$this->db->query("update users_company set promo_code='$coupon_code' where id='" . $company_id . "'");
					$status = "Success";
					$error = '<span style="color:green;">Coupon applied.</span>';
				} else { $error = '<span style="color:#FF0000;">The promo code you entered is not valid. Please enter a new one or contact support to validate your promotional code.</span>';}

			} else { $error = '<span style="color:#FF0000;">The promo code you entered is not valid. Please enter a new one or contact support to validate your promotional code.</span>';}

		} else { $error = '<span style="color:#FF0000;">The promo code you entered is not valid. Please enter a new one or contact support to validate your promotional code.</span>';}

		$jdata = array("status" => $status, "message" => $error);
		echo json_encode($jdata);
	}

	//	Customer Intake Form
	public function customer_intake_form() {
		$this->check_login_session(); // Check Login Session

		$client_id = $this->uri->segment(3);
		if ($GLOBALS["loguser"]["role"] == "Customer") {$client_id = $GLOBALS["loguser"]["id"];}

		$this->intake_model->get_client_analysis_results_for_data_confirmation($client_id);
		$page_data = $this->crm_model->get_client_analysis_results($client_id);

		if (isset($page_data['client_data']['client']['id'])) {
			$page_data['page_data'] = $page_data;
			$page_data['ics'] = $this->default_model->getRowArray("SELECT * FROM intake_client_status where client_id='$client_id' and id='" . $this->uri->segment(4) . "' limit 1");
			if (isset($page_data['ics']['id'])) {
				$intake_id = 1;

				if ($this->db->query('select * from intake_client_status where client_id=' . $client_id . ' and intake_id=4')->num_rows() > 0) {
					$intake_id = 4;
				}
				$page_data['intake_type'] = ($intake_id == 1) ? 'initial' : 'update';
				$page_data['car'] = $this->default_model->getRowArray("SELECT * FROM client_analysis_results where client_id='$client_id' and intake_id='" . $intake_id . "' limit 1");
				if (isset($page_data['car']['id'])) {
					$intake_id = $page_data['ics']['intake_id'];
					$q = $this->db->query("SELECT * FROM intake where intake_id='$intake_id'");
					$page_data['intkR'] = $q->row_array();

					$this->load->view('account/users/DataConfirmation/' . $page_data['intkR']['intake_slug'], $page_data);
					//$this->load->view('account/users/Intake/customer_intake_form', $page_data);
				}
			}
		}
	}

	//	View Intake Form Document as PDF
	public function intake_form_document() {
		$this->check_login_session(); // Check Login Session

		$client_id = $this->uri->segment(3);
		if ($GLOBALS["loguser"]["role"] == "Customer") {$client_id = $GLOBALS["loguser"]["id"];}

		$page_data = $this->crm_model->get_client_analysis_results($client_id);

		if (isset($page_data['client_data']['client']['id'])) {
			$page_data['page_data'] = $page_data;

			$doc_id = $this->uri->segment(4);
			$page_data['docr'] = $this->default_model->getRowArray("SELECT * FROM client_documents where client_id='$client_id' and document_id='" . $doc_id . "' limit 1");
			if (isset($page_data['docr']['document_id'])) {
				$ics_id = $page_data['docr']['intake_client_status_id'];
				$page_data['ics'] = $this->default_model->getRowArray("SELECT * FROM intake_client_status where client_id='$client_id' and id='" . $ics_id . "' limit 1");
				if (isset($page_data['ics']['id'])) {
					$intake_id = 1;

					if ($this->db->query('select * from intake_client_status where client_id=' . $client_id . ' and intake_id=4')->num_rows() > 0) {
						$intake_id = 4;
					}
					$page_data['intake_type'] = ($intake_id == 1) ? 'initial' : 'update';
					$page_data['car'] = $this->default_model->getRowArray("SELECT * FROM client_analysis_results where client_id='$client_id' and intake_id='" . $intake_id . "' limit 1");
					if (isset($page_data['car']['id'])) {
						$intake_id = $page_data['ics']['intake_id'];
						$q = $this->db->query("SELECT * FROM intake where intake_id='$intake_id'");
						$page_data['intkR'] = $q->row_array();

						if (in_array($page_data['intkR']['intake_slug'], ['idr_intake_form', 'recertification_intake_form', 'recalculation_intake_form', 'switch_idr_intake_form'])) {
							$this->load->library('pdf');
							$html = $this->load->view("account/users/DataConfirmation/" . $page_data['intkR']['intake_slug'] . "_pdf", $page_data, true);

							$pdf = new DOMPDF();

							$pdf->loadHtml($html);

							$pdf->setPaper('DEFAULT_PDF_PAPER_SIZE', 'portrait');

							$pdf->render();

							$page_data['total'] = $pdf->get_canvas()->get_page_count();
							$this->load->view('account/users/DataConfirmation/' . $page_data['intkR']['intake_slug'] . '_pdf_2', $page_data);

							$html1 = trim($this->output->get_output());

							// Load HTML content
							$this->dompdf->loadHtml($html1);

							// (Optional) Setup the paper size and orientation
							$this->dompdf->setPaper('DEFAULT_PDF_PAPER_SIZE', 'landscape');

							// Render the HTML as PDF
							$this->dompdf->render();

							$html = $this->load->view("account/users/DataConfirmation/" . $page_data['intkR']['intake_slug'] . "_pdf_3", $page_data, true);
							// echo $html;die;

							$pdf1 = new DOMPDF();

							$pdf1->loadHtml($html);

							$pdf1->setPaper('DEFAULT_PDF_PAPER_SIZE', 'portrait');

							$pdf1->render();

							$page_data['total'] += $pdf1->get_canvas()->get_page_count();

							$this->injectPageCount($this->dompdf, $pdf, $pdf1);

							$this->injectPageCount($pdf, $this->dompdf, $pdf1);

							$this->injectPageCount($pdf1, $this->dompdf, $pdf);

							file_put_contents("uploads/" . $page_data['intkR']['intake_slug'] . "/" . $client_id . '1.pdf', $pdf->output());
							$files = ["uploads/" . $page_data['intkR']['intake_slug'] . "/" . $client_id . '1.pdf'];

							file_put_contents("uploads/" . $page_data['intkR']['intake_slug'] . "/" . $client_id . '2.pdf', $this->dompdf->output());
							array_push($files, "uploads/" . $page_data['intkR']['intake_slug'] . "/" . $client_id . '2.pdf');

							file_put_contents("uploads/" . $page_data['intkR']['intake_slug'] . "/" . $client_id . '3.pdf', $pdf1->output());
							array_push($files, "uploads/" . $page_data['intkR']['intake_slug'] . "/" . $client_id . '3.pdf');

							$merger = new Merger;
							$merger->addIterator($files);
							$createdPdf = $merger->merge();

							$myfile = fopen("uploads/" . $page_data['intkR']['intake_slug'] . "/" . $this->uri->segment(5) . '.pdf', "w");
							$txt = $createdPdf;
							fwrite($myfile, $txt);
							unlink("uploads/" . $page_data['intkR']['intake_slug'] . "/" . $client_id . '1.pdf');
							unlink("uploads/" . $page_data['intkR']['intake_slug'] . "/" . $client_id . '2.pdf');
							unlink("uploads/" . $page_data['intkR']['intake_slug'] . "/" . $client_id . '3.pdf');

							header('Content-type: application/pdf');

							header('Content-Disposition: inline; filename="' . "uploads/" . $page_data['intkR']['intake_slug'] . "/" . $this->uri->segment(5) . '.pdf"');
							header('Content-Transfer-Encoding: binary');

							header('Accept-Ranges: bytes');

							readfile("uploads/" . $page_data['intkR']['intake_slug'] . "/" . $this->uri->segment(5) . '.pdf');
							unlink("uploads/" . $page_data['intkR']['intake_slug'] . "/" . $this->uri->segment(5) . '.pdf');
						} else {
							$this->load->view('account/users/DataConfirmation/' . $page_data['intkR']['intake_slug'] . "_pdf", $page_data);
							//$this->load->view('account/users/Intake/view_document', $page_data);

							// Get output html
							$html = trim($this->output->get_output());
							// echo $html;die;

							// Load pdf library
							$this->load->library('pdf');

							// Load HTML content
							$this->dompdf->loadHtml($html);

							// (Optional) Setup the paper size and orientation
							//$this->dompdf->setPaper('A4', 'landscape');
							$this->dompdf->setPaper('DEFAULT_PDF_PAPER_SIZE', 'portrait');

							// Render the HTML as PDF
							$this->dompdf->render();

							$html = $this->load->view("account/users/DataConfirmation/idr_intake_form_pdf", $page_data, true);
							// echo $html;die;

							$pdf = new DOMPDF();

							$pdf->loadHtml($html);

							$pdf->setPaper('DEFAULT_PDF_PAPER_SIZE', 'portrait');

							$pdf->render();

							$page_data['total'] = $pdf->get_canvas()->get_page_count();

							$html1 = $this->load->view("account/users/DataConfirmation/idr_intake_form_pdf_2", $page_data, true);

							$pdf2 = new DOMPDF();
							// Load HTML content
							$pdf2->loadHtml($html1);

							// (Optional) Setup the paper size and orientation
							$pdf2->setPaper('DEFAULT_PDF_PAPER_SIZE', 'landscape');

							// Render the HTML as PDF
							$pdf2->render();
							$page_data['total'] += $pdf2->get_canvas()->get_page_count();

							$html = $this->load->view("account/users/DataConfirmation/idr_intake_form_pdf_3", $page_data, true);
							// echo $html;die;

							$pdf1 = new DOMPDF();

							$pdf1->loadHtml($html);

							$pdf1->setPaper('DEFAULT_PDF_PAPER_SIZE', 'portrait');

							$pdf1->render();

							$page_data['total'] += $pdf1->get_canvas()->get_page_count();

							$this->injectPageCount($pdf, $pdf1, $pdf2);
							$this->injectPageCount($pdf1, $pdf, $pdf2);
							$this->injectPageCount($pdf2, $pdf, $pdf1);

							file_put_contents("uploads/consolidation_intake_form/" . $client_id . '2.pdf', $this->dompdf->output());
							$files = ["uploads/consolidation_intake_form/" . $client_id . '2.pdf'];

							file_put_contents("uploads/consolidation_intake_form/" . $client_id . '1.pdf', $pdf->output());
							array_push($files, "uploads/consolidation_intake_form/" . $client_id . '1.pdf');
							file_put_contents("uploads/consolidation_intake_form/" . $client_id . '4.pdf', $pdf2->output());
							array_push($files, "uploads/consolidation_intake_form/" . $client_id . '4.pdf');

							file_put_contents("uploads/consolidation_intake_form/" . $client_id . '3.pdf', $pdf1->output());
							array_push($files, "uploads/consolidation_intake_form/" . $client_id . '3.pdf');

							$merger = new Merger;
							$merger->addIterator($files);
							$createdPdf = $merger->merge();

							if (file_exists('uploads/consolidation_intake_form/' . $this->uri->segment(5) . '.pdf')) {
								unlink('uploads/consolidation_intake_form/' . $this->uri->segment(5) . '.pdf');
							}

							$myfile = fopen('uploads/consolidation_intake_form/' . $this->uri->segment(5) . '.pdf', "w");
							$txt = $createdPdf;
							fwrite($myfile, $txt);
							unlink("uploads/consolidation_intake_form/" . $client_id . '1.pdf');
							unlink("uploads/consolidation_intake_form/" . $client_id . '2.pdf');
							unlink("uploads/consolidation_intake_form/" . $client_id . '3.pdf');
							unlink("uploads/consolidation_intake_form/" . $client_id . '4.pdf');

							header('Content-type: application/pdf');

							header('Content-Disposition: inline; filename="' . 'uploads/consolidation_intake_form/' . $this->uri->segment(5) . '.pdf"');
							header('Content-Transfer-Encoding: binary');

							header('Accept-Ranges: bytes');

							readfile('uploads/consolidation_intake_form/' . $this->uri->segment(5) . '.pdf');
							unlink('uploads/consolidation_intake_form/' . $this->uri->segment(5) . '.pdf');

							// Output the generated PDF (1 = download and 0 = preview)
							// $this->dompdf->stream('uploads/consolidation_intake_form/' . $this->uri->segment(5), array("Attachment" => 0));
						}
						exit;
					}
				}
			}
		}

	}

	public function injectPageCount(Dompdf $dompdf, $pdf, $pdf1, $pdf2 = null): void {
		/** @var CPDF $canvas */
		$canvas = $dompdf->getCanvas();
		$num2 = 0;

		if (!empty($pdf)) {

			$canvas1 = $pdf->getCanvas();
			$num2 = $canvas1->get_page_count();
		}

		if (!empty($pdf2)) {

			$canvas2 = $pdf2->getCanvas();
			$num2 += $canvas2->get_page_count();
		}

		if (!empty($pdf1)) {

			$canvas2 = $pdf1->getCanvas();
			$num2 += $canvas2->get_page_count();
		}
		$pdf = $canvas->get_cpdf();

		foreach ($pdf->objects as &$o) {
			if ($o['t'] === 'contents') {
				$o['c'] = str_replace('DOMPDF_PAGE_COUNT_PLACEHOLDER', $canvas->get_page_count() + $num2, $o['c']);
			}
		}
	}

	//	Page - nslds_upload_instructions
	public function nslds_upload_instructions() {
		$page_data = array("data" => ["name" => "Uploading Your NSLDS instructions", "seo_title" => "Uploading Your NSLDS instructions"]);
		$this->load->view('Site/nslds_upload_instructions', $page_data);
	}

	//	Pages
	public function pages() {
		@extract($_POST);
		@extract($_GET);
		error_reporting(E_ALL ^ E_NOTICE);

		$page_data = array();
		$data = $this->default_model->get_arrby_tbl('pages', '*', "url='" . $this->uri->segment(1) . "'", '1');
		if (isset($data[0])) {$page_data['data'] = $data[0];}
		$this->load->view('Site/home', $page_data);
	}

	//	Contact Us
	public function contact_us() {
		/* Set validation rule for name field in the form */
		$this->form_validation->set_rules('type_of_inquiry', 'Type of Inquiry', 'required');
		$this->form_validation->set_rules('name', 'Name', 'required');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
		if (isset($_POST['type_of_inquiry'])) {
			if ($_POST['type_of_inquiry'] == "Support" || $_POST['type_of_inquiry'] == "Billing") {
				if ($_POST['accno'] != "") {$this->form_validation->set_rules('accno', 'Account Number', 'required|callback_accno_check');}
				$this->form_validation->set_rules('phone', 'Phone Number', 'required|callback_check_mobile_number_validation');
			} else {
				if ($_POST['accno'] != "") {$this->form_validation->set_rules('accno', 'Account Number', 'required|callback_accno_check');}
				if ($_POST['phone'] != "") {$this->form_validation->set_rules('phone', 'Phone Number', 'required|callback_check_mobile_number_validation');}
			}
		}

		$this->form_validation->set_rules('captcha', "Captcha", 'required|callback_captcha_check');

		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('error', validation_errors());
		} else {
			$result = $this->crm_model->contact_us_form();
			if ($result['error'] != '') {
				$this->session->set_flashdata('error', $result['error']);
			} else {redirect(base_url("thankyou"));}
		}

		$captcha = $this->_generateCaptcha();
		$this->session->set_userdata('captchaWord', $captcha['word']);

		$page_data = array();
		$page_data['data']['name'] = "Contact Us";
		$page_data['data']['seo_title'] = "Contact Us";
		$page_data['data']['captcha'] = $captcha;
		$this->load->view('Site/contact-us', $page_data);
	}

	public function captcha_verify() {
		$curl = curl_init('https://www.google.com/recaptcha/api/siteverify');
		curl_setopt($curl, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		/*
			$headers = array(
			"Accept: application/json",
			"Content-Type: application/json",
			);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		 */
		$data = ['response' => $_POST['response'], 'secret' => $_POST['secret']];

		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));

		$resp = curl_exec($curl);
		curl_close($curl);
		echo $resp;
	}

	//	Client Registration Page
	public function client_registration() {
		if ($this->session->userdata('userid')) {redirect(base_url('account/dashboard'));}

		$page_data = array();
		$page_data['data']['name'] = "Registration";
		$page_data['data']['seo_title'] = "Registration";
		$page_data['company_data'] = $this->crm_model->get_company_details($this->uri->segment(1));
		if (isset($page_data['company_data']['id'])) {$page_data['company_smtp_data'] = $this->crm_model->get_company_smtp_email_details($page_data['company_data']['id']);}

		$advertisement_id = "";
		if ($this->uri->segment(3) != "") {
			$q = $this->db->query("SELECT * FROM users_advertisement where code='" . $this->uri->segment(3) . "' and company_id='" . $page_data['company_data']['id'] . "'");
			$chkR = $q->row_array();
			if (!isset($chkR['id'])) {
				redirect(base_url($this->uri->segment(1) . '/client_registration'));
				exit;
			} else { $advertisement_id = $chkR['id'];}
		}

		/* Set validation rule for name field in the form */
		$this->form_validation->set_rules('name', 'First Name', 'required');
		$this->form_validation->set_rules('lname', 'Last Name', 'required');
		$this->form_validation->set_rules('phone', 'Phone Number', 'required|callback_check_mobile_number_validation');
		$this->form_validation->set_rules('email', 'Email', 'required');
		$this->form_validation->set_rules('password', 'New Password', 'required|min_length[10]|max_length[15]|callback_check_strong_password');
		$this->form_validation->set_rules('rpassword', 'Retype Password', 'required|matches[password]');

		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('error', validation_errors());
		} else {
			$cmpR = $page_data['company_data'];
			$result = $this->account_model->add_client('0', $cmpR['id'], $cmpR['case_manager'], $advertisement_id);
			if (!isset($result['id'])) {
				$this->session->set_flashdata('error', $result['errorMsg']);
			} else {
				$this->session->set_flashdata('success', $result['msg']);
				redirect(base_url($this->uri->segment(1) . '/client_login'));
			}
		}

		$this->load->view('Site/login/register_client', $page_data);
	}

	public function verify_account() {
		$id = $this->uri->segment(3);
		$clientid = base64_decode($id);

		$client = $this->default_model->getRowArray("SELECT * FROM users where id='$clientid'");

		if (isset($client['id'])) {

			if (date('Y-m-d', strtotime($client['add_date'] . ' + 10 days')) >= date('Y-m-d')) {

				$this->db->where('id', $clientid);
				$this->db->update('users', ['account_verified' => 1]);
				$this->session->set_flashdata('success', 'Account Verified');
				redirect(base_url('/'));

			} else {

				$this->session->set_flashdata('error', 'Link Expired.');
				redirect(base_url('/'));
			}

		} else {

			$this->session->set_flashdata('error', 'Invalid Link. Please contact your case manager for more details.');
			redirect(base_url('/'));

		}
	}

	//	Client Login Page
	public function client_login() {
		if ($this->session->userdata('userid')) {redirect(base_url('account/dashboard'));}

		$page_data = array();
		$page_data['data']['name'] = "Login";
		$page_data['data']['seo_title'] = "Login";
		$page_data['company_data'] = $this->crm_model->get_company_details($this->uri->segment(1));
		if (isset($page_data['company_data']['id'])) {$page_data['company_smtp_data'] = $this->crm_model->get_company_smtp_email_details($page_data['company_data']['id']);}

		/* Set validation rule for name field in the form */
		$this->form_validation->set_rules('email', 'Email', 'required');
		$this->form_validation->set_rules('password', 'Password', 'required');

		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('error', validation_errors());
		} else {
			$result = $this->crm_model->admin_login($page_data['company_data']['id']);
			if (!isset($result['id'])) {
				$this->session->set_flashdata('error', $result['errorMsg']);
			} else {
				if ($this->session->has_userdata('redirect') && $this->session->userdata('redirect')) {
					redirect($this->session->userdata('redirect'));
				}
				redirect(base_url('account/dashboard'));}
		}

		$this->load->view('Site/login/login_client', $page_data);
	}

	//	Forgot Password Page
	public function client_fp() {
		if ($this->session->userdata('userid')) {redirect(base_url('account/dashboard'));}
		$page_data = array();
		$page_data['data']['name'] = "Forgot Password";
		$page_data['data']['seo_title'] = "Forgot Password";
		$page_data['company_data'] = $this->default_model->get_company($this->uri->segment(1));
		if (isset($page_data['company_data']['id'])) {$page_data['company_smtp_data'] = $this->crm_model->get_company_smtp_email_details($page_data['company_data']['id']);}
		/* Set validation rule for name field in the form */
		$this->form_validation->set_rules('email', 'Email', 'required');

		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('error', validation_errors());
		} else {
			if (isset($page_data['company_data']['id'])) {$company_id = $page_data['company_data']['id'];} else { $company_id = 0;}

			$result = $this->crm_model->admin_fp($company_id);
			if (!isset($result['id'])) {
				$this->session->set_flashdata('error', 'Invalid User ID.');
			} else {
				$this->session->set_flashdata('success', 'New password successfully sent on your email.');
				redirect(base_url($this->uri->segment(1) . '/client_login'));}
		}
		if ($this->uri->segment(1) == 'account') {$page = "fp";} else { $page = "fp_client";}
		$this->load->view('Site/login/' . $page, $page_data);
	}

	// this function will create captcha
	public function _generateCaptcha() {
		$vals = array(
			'img_path' => './captcha_images/',
			'img_url' => base_url('captcha_images/'),
			'img_width' => '100',
			'img_height' => 30,
			'img_id' => 'img_contact_captcha',
			'expiration' => 7200,
		);
		/* Generate the captcha */
		$data = create_captcha($vals);
		return $data;
	}

	//	Re Generte Captcha
	public function regeneratecaptcha() {
		$captcha = $this->_generateCaptcha();
		$this->session->set_userdata('captchaWord', $captcha['word']);
		echo json_encode($captcha);
	}

	//	Check Account No
	public function accno_check($accno) {

		$accno2 = "";
		$ar = $this->default_model->get_arrby_tbl('users', '*', "id='" . $accno . "'", '1');
		if (isset($ar[0])) {$accno2 = $ar[0]["id"];}

		if ($accno2 != "" && $accno == $accno2) {
			return TRUE;
		} else {
			$this->form_validation->set_message('accno_check', "Invalid Account Number");
			return FALSE;
		}
	}

	//	Check Captcha
	public function captcha_check($captcha) {

		if ($captcha == $this->session->userdata('captchaWord')) {
			return TRUE;
		} else {
			$this->form_validation->set_message('captcha_check', "Invalid Captcha Code");
			return FALSE;
		}
	}

	//	Check Strong Password
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

	//	Check Mobile Number Validation
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

	//	Page NOT FOUND
	public function not_found_404() {
		redirect('account');
	}

}