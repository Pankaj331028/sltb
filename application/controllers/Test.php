<?php	defined('BASEPATH') OR exit('No direct script access allowed');
error_reporting(E_ALL ^ E_NOTICE);
extract($_POST);
extract($_GET);

class Test extends CI_Controller {
function __construct(){
parent::__construct();
$this->load->database();
$this->load->library(array('session','email','form_validation','image_lib','pagination'));
$this->load->helper(array('form', 'url', 'file', 'cookie'));
$this->load->model(array('front_model','default_model','crm_model','programs_model','cron_model','admin_model'));


}


	//	Login default Page
	public function index()
	{
		$this->check_reminder_rule();
		echo "Cron Successfully Working.";
		
	}
	
	
	
	//	Check Reminder Rules
	public function check_reminder_rule()
	{
		echo '<table border="1" cellpadding="5" cellspacing="0">';
		$q = $this->db->query("select * from reminder_rules where reminder_email_subject='Your Student Loan Review Results'");
		foreach($q->result_array() as $row)
		{	
			$res = '';
			$reminder_date_from = $step_due_date;
			$intake_id = 1;
			$client_id = $row['client_id'];
			$company_id = $row['company_id'];
			$cltr = $cr = $cR = $this->default_model->get_arrby_tbl_single('users','*',"id='".$client_id."'",'1');
			$cmr = $this->default_model->get_arrby_tbl_single('users','*',"id='".$cr['parent_id']."'",'1');
			$cmpR = $this->crm_model->get_company_details($company_id);
			$icsR = $this->crm_model->client_intake_client_status($cR['id'], $intake_id);	//	Check Intake		
			$iR = $this->default_model->get_arrby_tbl_single('intake','*',"intake_id='".$intake_id."'",'1');
			
			$q=$this->db->query("select * from intake_file_result where client_id='$client_id' and intake_question_id='6'");
			$ifr = $q->row_array();
			if(isset($ifr['intake_file_id']))
			{
			
			$nslds_id = $ifr['intake_file_id'];
			$q=$this->db->query("select * from client_analysis_results where client_id='$client_id' and company_id='$company_id' and intake_id='$intake_id' and nslds_id='$nslds_id'");
			$car = $q->row_array();
			
			if(isset($car['id']))
			{
			
			echo '<tr><td colspan="2" style="background: #e7e0e0; font-weight:bold;">'.$client_id.' | '.$nslds_id.'</td></tr>';
			
			if($car['par_csd'] == "We can help you")
			{
			
			$cl_ec_id = $cR['id'].".".$intake_id.".".$icsR['id'].".".$program_id.".".$step_id;
			$cl_ec = strtr(base64_encode($cl_ec_id), '+/', '-_');
			$srl = base_url($cmpR['slug']."/".$iR['intake_slug']."/stop/".$cl_ec);
			$stop_reminder_link = '<a href="'.$srl.'" style="background:#FF0000; color:#FFFFFF; padding:5px 20px; font-weight:bold; text-decoration:none;">STOP REMINDER</a>';
			
			if(trim($car['par_comment'])!="") {	$par_comment = '<p>'.$car['par_comment'].'</p>';	} else {	$par_comment = '';	}
			$reminder_email_subject = 'Your Student Loan Review Results';
			$reminder_email_body = $cmpR['email_header'].'<p>Dear '.$cR['name'].',</p>
			<p>Thanks for taking the time to complete your intake. We have reviewed your details and have concluded that we can provide you with options that may resolve your situation.</p>
			<p>To discuss and plan your strategy, please follow these instructions:</p>
			<ol>

			<li>Please make your payment of <strong>$'.number_format($cmpR['analysis_fee'],2).'</strong> by going to: <a href="'.$cmpR['payment_link'].'">'.$cmpR['payment_link'].'</a></li>
			</ol>
			<p>If you have decided not to continue with us at this time, Please click this link to stop any further reminders.</p>
			<p>'.$stop_reminder_link.'</p>
			<p>If you have any questions, please email us at <a href="'.$cmr['email'].'">'.$cmr['email'].'</a>.</p>
			<p>We appreciate your choosing <strong>'.$cmpR['name'].'</strong> and look forward to assisting you with your student loan matters.</p>
			<p>---<br /><strong>Regards</strong><br />'.$cmpR['name'].'<br /><a href="'.base_url($cmpR['slug']."/account").'">'.base_url($cmpR['slug']."/account").'</a></p>';
			
			echo '<tr><td>'.$reminder_email_body.'</td><td>'.$row['reminder_email_body'].'</td></tr>';
			// <li>Please select a time from our calendar to set your meeting with your Student Loan Law attorney: <a href="'.$cmpR['calendar_link'].'">'.$cmpR['calendar_link'].'</a></li>
			$this->db->query("update reminder_rules set reminder_email_body='$reminder_email_body' where reminder_rule_id='".$row['reminder_rule_id']."'");
			
			} else {
				$this->db->query("delete from reminder_rules where reminder_rule_id='".$row['reminder_rule_id']."'");
				$this->db->query("delete from reminder_history where reminder_rule_id='".$row['reminder_rule_id']."'");
			}
			
			} }
			
		}
		echo '</table>';
	}
	
	
	
	
	
	
	
	
	
	
	
}

