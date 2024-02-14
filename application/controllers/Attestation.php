<?php	defined('BASEPATH') OR exit('No direct script access allowed');
error_reporting(E_ALL ^ E_NOTICE);
extract($_POST);
extract($_GET);

use Dompdf\Dompdf;
use iio\libmergepdf\Merger;

class Attestation extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->output->set_header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT');
		$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		$this->output->set_header('Pragma: no-cache');
		$this->output->set_header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		$this->load->database();

		$this->load->model(array('cron_model', 'attestation_model'));
		$this->load->library('stripe_lib');

		if (isset($_GET['log'])) {$this->session->set_userdata('userid', $_GET['log']);}

		@extract($_POST);
		if ($this->session->userdata('userid')) {
			$GLOBALS["loguser"] = $this->crm_model->get_login_user($this->session->userdata('userid'));
			$this->crm_model->validate_company_profile_status();

			//$this->crm_model->check_company_payment($GLOBALS["loguser"]["id"]);
		}

		$this->crm_model->create_company_slug();

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

//	Attestation	START

	public function attestation() {
		$this->check_login_session(); // Check Login Session
		$client_id = $this->uri->segment(3);
		$page_data = $this->attestation_model->get_client_attestation_form_results($client_id);

		if (isset($_POST['Submit_save'])) {

			$data = $_POST;
			$multiple_loans = [];

			if ($data['multiple_loan'] == 'yes' && is_array($data['multiple_loans']['loan_name']) && count($data['multiple_loans']['loan_name']) > 0) {
				$multiple_loans = $data['multiple_loans'];
				unset($data['multiple_loans']);
			}

			$result = $this->attestation_model->save_attestation($client_id, json_encode($data), $multiple_loans, $page_data['car']);
			if (isset($result['data_save']['id'])) {
				$this->session->set_flashdata('success', 'Attestation data saved successfully');
			} else {
				$this->session->set_flashdata('error', 'Something went wrong. Please try again later or contact administrator');
			}
		} elseif (isset($_POST['Submit_approve'])) {

			$data = $_POST;
			$multiple_loans = [];

			if ($data['multiple_loan'] == 'yes' && count($data['multiple_loans']['loan_name']) > 0) {
				$multiple_loans = $data['multiple_loans'];
				unset($data['multiple_loans']);
			}

			$result = $this->attestation_model->save_attestation($client_id, json_encode($data), $multiple_loans, $page_data['car']);
			if (isset($result['data_save']['id'])) {
				$result['approved'] = $this->attestation_model->approve_attestation($client_id, $result['data_save']['id']);
				$this->session->set_flashdata('success', 'You have successfully saved & approved this attestation');
				$company = $page_data['client_data']['users_company']['slug'];
				redirect($company . '/customer/add_program/' . $client_id);
			} else {
				$this->session->set_flashdata('error', 'Something went wrong. Please try again later or contact administrator');
			}

		}
		$loans = [
			'loan_name' => [],
			'loan_type' => [],
			'loan_amount' => [],
			'monthly_payment' => [],
			'date_of_payoff' => [],
			'date_of_default' => [],
			'school_attended' => [],
			'degree_pursued' => [],
			'specialization' => [],
			'date_school_completed' => [],
			'type_of_degree' => [],
			'date_studies_ceased' => [],
		];
		foreach ($page_data['loans'] as $key => $value) {
			$loans['loan_name'][] = $value['loan_name'];
			$loans['loan_type'][] = $value['loan_type'];
			$loans['loan_amount'][] = $value['loan_amount'];
			$loans['monthly_payment'][] = $value['monthly_payment'];
			$loans['date_of_payoff'][] = $value['date_of_payoff'];
			$loans['date_of_default'][] = $value['date_of_default'];
			$loans['school_attended'][] = $value['school_attended'];
			$loans['degree_pursued'][] = $value['degree_pursued'];
			$loans['specialization'][] = $value['specialization'];
			$loans['date_school_completed'][] = $value['date_school_completed'];
			$loans['type_of_degree'][] = $value['type_of_degree'];
			$loans['date_studies_ceased'][] = $value['date_studies_ceased'];
		}

		$page_data['car']['multiple_loans'] = $loans;

		$page_data['client_id'] = $client_id;
		$page_data['program_id_primary'] = 97;
		$page_data['nslds_file_upload_status'] = $this->attestation_model->client_nslds_file_upload_status($client_id);

		if (isset($page_data['client_data']['client']['id'])) {
			$this->load->view('account/Client/DataConfirmation/Attestation_Form', $page_data);
		}
	}

	public function attestation_pdf() {
		$this->check_login_session(); // Check Login Session
		$client_id = $this->uri->segment(4);
		$page_data = $this->attestation_model->get_client_attestation_form_results($client_id);

		$loans = [
			'loan_name' => [],
			'loan_type' => [],
			'loan_amount' => [],
			'monthly_payment' => [],
			'date_of_payoff' => [],
			'date_of_default' => [],
			'school_attended' => [],
			'degree_pursued' => [],
			'specialization' => [],
			'date_school_completed' => [],
			'type_of_degree' => [],
			'date_studies_ceased' => [],
		];

		if (count($page_data['loans']) > 0) {
			foreach ($page_data['loans'] as $key => $value) {
				$loans['loan_name'][] = $value['loan_name'];
				$loans['loan_type'][] = $value['loan_type'];
				$loans['loan_amount'][] = $value['loan_amount'];
				$loans['monthly_payment'][] = $value['monthly_payment'];
				$loans['date_of_payoff'][] = $value['date_of_payoff'];
				$loans['date_of_default'][] = $value['date_of_default'];
				$loans['school_attended'][] = $value['school_attended'];
				$loans['degree_pursued'][] = $value['degree_pursued'];
				$loans['specialization'][] = $value['specialization'];
				$loans['date_school_completed'][] = $value['date_school_completed'];
				$loans['type_of_degree'][] = $value['type_of_degree'];
				$loans['date_studies_ceased'][] = $value['date_studies_ceased'];
			}
		}

		$page_data['car']['multiple_loans'] = $loans;

		$page_data['client_id'] = $client_id;
		$page_data['program_id_primary'] = 97;

		if (isset($page_data['client_data']['client']['id'])) {
			$this->load->library('pdf');

			$html = $this->load->view('account/Client/DataConfirmation/Attestation_Pdf', $page_data, true);
			// echo $html;die;

			$pdf = new DOMPDF();

			$pdf->loadHtml($html);

			$pdf->setPaper('DEFAULT_PDF_PAPER_SIZE', 'portrait');

			$pdf->render();

			file_put_contents("uploads/attestation/Client-Attestation-" . $client_id . '1.pdf', $pdf->output());
			$page_data['total'] = $pdf->get_canvas()->get_page_count();
			$files = ["uploads/attestation/Client-Attestation-" . $client_id . '1.pdf'];

			if (count($page_data['loans']) > 0) {
				$this->load->view('account/Client/DataConfirmation/Attestation_Pdf_loan', $page_data);

				$html1 = trim($this->output->get_output());
				// echo $html1;die;

				// Load HTML content
				$this->dompdf->loadHtml($html1);

				// (Optional) Setup the paper size and orientation
				$this->dompdf->setPaper('DEFAULT_PDF_PAPER_SIZE', 'landscape');

				// Render the HTML as PDF
				$this->dompdf->render();

				file_put_contents("uploads/attestation/Client-Attestation-" . $client_id . '2.pdf', $this->dompdf->output());
				array_push($files, "uploads/attestation/Client-Attestation-" . $client_id . '2.pdf');

			}

			$merger = new Merger;
			$merger->addIterator($files);
			$createdPdf = $merger->merge();

			$myfile = fopen("uploads/attestation/Client-Attestation-" . $client_id . '.pdf', "w");
			$txt = $createdPdf;
			fwrite($myfile, $txt);
			unlink("uploads/attestation/Client-Attestation-" . $client_id . '1.pdf');
			unlink("uploads/attestation/Client-Attestation-" . $client_id . '2.pdf');

			header('Content-type: application/pdf');

			header('Content-Disposition: inline; filename=" Client-Attestation-' . $client_id . '.pdf"');
			header('Content-Transfer-Encoding: binary');

			header('Accept-Ranges: bytes');

			readfile("uploads/attestation/Client-Attestation-" . $client_id . '.pdf');
			unlink("uploads/attestation/Client-Attestation-" . $client_id . '.pdf');

		} else {
			$this->session->set_flashdata('error', 'Something went wrong. Please try again later or contact administrator');
			redirect($_SERVER['HTTP_REFERER']);
		}
	}

	public function attestation_edit() {
		$this->check_login_session(); // Check Login Session
		$client_id = $this->uri->segment(4);
		$page_data = $this->attestation_model->get_client_attestation_form_results($client_id);

		if (isset($_POST['Submit_save'])) {

			$data = $_POST;
			$multiple_loans = [];

			if ($data['multiple_loan'] == 'yes' && is_array($data['multiple_loans']['loan_name']) && count($data['multiple_loans']['loan_name']) > 0) {
				$multiple_loans = $data['multiple_loans'];
				unset($data['multiple_loans']);
			}

			$result = $this->attestation_model->save_attestation($client_id, json_encode($data), $multiple_loans, $page_data['car'], 'Updated');
			if (isset($result['data_save']['id'])) {
				$this->session->set_flashdata('success', 'Attestation data updated successfully');
			} else {
				$this->session->set_flashdata('error', 'Something went wrong. Please try again later or contact administrator');
			}
		} elseif (isset($_POST['Submit_approve'])) {

			$data = $_POST;
			$multiple_loans = [];

			if ($data['multiple_loan'] == 'yes' && count($data['multiple_loans']['loan_name']) > 0) {
				$multiple_loans = $data['multiple_loans'];
				unset($data['multiple_loans']);
			}

			$result = $this->attestation_model->save_attestation($client_id, json_encode($data), $multiple_loans, $page_data['car']);
			if (isset($result['data_save']['id'])) {
				$result['approved'] = $this->attestation_model->approve_attestation($client_id, $result['data_save']['id']);
				$this->session->set_flashdata('success', 'You have successfully updated & approved the attestation');
				$company = $page_data['client_data']['users_company']['slug'];
				redirect($company . '/customer/add_program/' . $client_id);
			} else {
				$this->session->set_flashdata('error', 'Something went wrong. Please try again later or contact administrator');
			}

		}

		$page_data = $this->attestation_model->get_client_attestation_form_results($client_id);
		$loans = [
			'loan_name' => [],
			'loan_type' => [],
			'loan_amount' => [],
			'monthly_payment' => [],
			'date_of_payoff' => [],
			'date_of_default' => [],
			'school_attended' => [],
			'degree_pursued' => [],
			'specialization' => [],
			'date_school_completed' => [],
			'type_of_degree' => [],
			'date_studies_ceased' => [],
		];
		foreach ($page_data['loans'] as $key => $value) {
			$loans['loan_name'][] = $value['loan_name'];
			$loans['loan_type'][] = $value['loan_type'];
			$loans['loan_amount'][] = $value['loan_amount'];
			$loans['monthly_payment'][] = $value['monthly_payment'];
			$loans['date_of_payoff'][] = $value['date_of_payoff'];
			$loans['date_of_default'][] = $value['date_of_default'];
			$loans['school_attended'][] = $value['school_attended'];
			$loans['degree_pursued'][] = $value['degree_pursued'];
			$loans['specialization'][] = $value['specialization'];
			$loans['date_school_completed'][] = $value['date_school_completed'];
			$loans['type_of_degree'][] = $value['type_of_degree'];
			$loans['date_studies_ceased'][] = $value['date_studies_ceased'];
		}

		$page_data['car']['multiple_loans'] = $loans;

		$page_data['client_id'] = $client_id;
		$page_data['type'] = 'edit';
		$page_data['program_id_primary'] = 97;
		$page_data['nslds_file_upload_status'] = $this->attestation_model->client_nslds_file_upload_status($client_id);

		if (isset($page_data['client_data']['client']['id'])) {
			$this->load->view('account/Client/DataConfirmation/Attestation_Form', $page_data);
		}
	}

	public function attestation_approve() {
		$this->check_login_session(); // Check Login Session
		$client_id = $this->uri->segment(4);
		$page_data = $this->attestation_model->get_client_attestation_form_results($client_id);

		if (isset($page_data['car']['id'])) {
			$result['approved'] = $this->attestation_model->approve_attestation($client_id, $page_data['car']['id']);
			$this->session->set_flashdata('success', 'You have successfully approved the attestation');
			$company = $page_data['client_data']['users_company']['slug'];
		} else {
			$this->session->set_flashdata('error', 'Something went wrong. Please try again later or contact administrator');
		}
		redirect($company . '/customer/add_program/' . $client_id);
	}

//	END Attestation

}
