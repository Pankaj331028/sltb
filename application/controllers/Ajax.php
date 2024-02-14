<?php	defined('BASEPATH') OR exit('No direct script access allowed');


class Ajax extends CI_Controller {

function __construct(){
parent::__construct();
$this->load->database();
$this->load->model(array('default_model','front_model','login_model','account_model','programs_model'));
$this->load->helper(array('form', 'url', 'file', 'cookie'));

extract($_POST);
$GLOBALS["login_data"] = $this->login_model->get_login_user();

}

	public function index()
	{
		echo "Coming Soon...";
	}
	
	//	Get Customer Name by ID
	public function ajax_get_customer_name_by_id()
	{
		$qry = $this->db->query("SELECT * FROM users where id='".trim($_POST['id'])."' order by name asc limit 1");
		$res = $qry->row_array();
		if(isset($res['name'])) {	echo '<span style="color:blue;">'.$res['name'].'</span>';	} else { echo '<span style="color:red;">Invalid Id</span>'; }
		exit;
	}
	
	
	
	
	




}

