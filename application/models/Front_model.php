<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Front_model extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->load->helper('url');
		$this->load->database();
	}

	function sendmail($to, $subject, $Msg) {

		$MsgHeader = '<div style="font-family:Calibri; font-size:15px; width:100%; max-width:750px; text-align:justify; border:1px solid #eee; padding:2px 15px;">
<p><a href="' . base_url() . '"><img src="' . base_url('assets/img/logo.png') . '" alt="Student Loan Tool Box" /></a></p>
<hr style="border-color:#0066CC; background-color:#0066CC;" />';
		$MsgFooter = '<div><p style="color:#0033cc;"><strong>---<br />Thanks,<br /></strong>Student Loan Tool Box<br />' . base_url() . '</p></div></div>';

		$Msg = $MsgHeader . $Msg . $MsgFooter;

		$config = array(
			'protocol' => 'smtp',
			'smtp_host' => 'mail.studentloantoolbox.com',
			'smtp_port' => 465,
			'smtp_user' => 'support@studentloantoolbox.com',
			'smtp_pass' => 'SuPp0rt4SltB!2',
			'mailtype' => 'html',
			'charset' => 'utf-8',
			'wordwrap' => TRUE,
		);

		/*//$this->email->initialize($config);
			$this->email->from('support@studentloantoolbox.com', 'Student Loan Tool Box');
			foreach(explode(",",$to) as $email) {	$this->email->to($email);	}
			$this->email->subject($subject);
			$this->email->message($Msg);
			$this->email->set_mailtype('html');
			return $this->email->send();
		*/

		$this->load->library('phpmailer_lib');

		// PHPMailer object
		$mail = $this->phpmailer_lib->load();

		// SMTP configuration
		$mail->isSMTP();
		$mail->Host = $config['smtp_host'];
		$mail->SMTPDebug = 0;
		$mail->SMTPAuth = true;
		$mail->Username = $config['smtp_user'];
		$mail->Password = $config['smtp_pass'];
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

		$mail->setFrom('support@studentloantoolbox.com', 'Student Loan Tool Box');
		$mail->addReplyTo('support@studentloantoolbox.com', 'Student Loan Tool Box');

		// Add a recipient
		foreach (explode(",", $to) as $email) {$mail->addAddress($email);}

		// Email subject
		$mail->Subject = $subject;

		// Set email format to HTML
		$mail->isHTML(true);

		// Email body content
		$mailContent = $Msg;
		$mail->Body = $mailContent;

		// Send email
		return $mail->send();
	}

//	Check Login User
	function check_login_user() {
		if (!isset($GLOBALS["loguser"]["id"])) {redirect(base_url("login"));}
	}

//	Get Country List
	function get_arr_by_index($tbl = '', $indx = 'id', $val = 'name', $cnd = 1, $limit = 100000000) {
		$arr = array();
		$q = $this->db->query("SELECT $indx,$val FROM $tbl where $cnd limit $limit");
		foreach ($q->result_array() as $r) {
			$inx = $r[$indx];
			$arr[$inx] = $r[$val];}
		return $arr;
	}

//	Get Data by URL
	public function get_data_by_url($tbl, $url) {
		$q = $this->db->query("SELECT * FROM $tbl where url='$url' limit 1");
		$res = $q->row_array();
		if ($res['id'] != '') {$res['tblnm'] = $tbl;}
		return $res;
	}

//	Get Result By Table Name
	public function get_resultby_tbl($tbl = '', $filds = '*', $cnd = 1, $limit = 100000000) {
		$sql = "SELECT $filds FROM $tbl where $cnd limit $limit";
		$q = $this->db->query($sql);
		$res = $q->result();
		return $res;
	}

//	Get Result Array By Table Name
	public function get_arrby_tbl($tbl = '', $filds = '*', $cnd = 1, $limit = 100000000) {
		$sql = "SELECT $filds FROM $tbl where $cnd limit $limit";
		$q = $this->db->query($sql);
		$res = $q->result_array();
		return $res;
	}

//	Get Single Result By Table Name
	public function get_single_resultby_tbl($tbl = '', $filds = '*', $cnd = 1) {
		$sql = "SELECT $filds FROM $tbl where $cnd limit 1";
		$q = $this->db->query($sql);
		$res = $q->row_array();
		return $res;
	}

//	Get num of rows
	public function get_num_rows($tbl = '', $cnd = 1) {
		$sql = "SELECT * FROM $tbl where $cnd limit 5000000";
		$q = $this->db->query($sql);
		$res = $q->num_rows();
		return $res;
	}

//	Generate Numbers
	function generateNumber($length) {
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$count = mb_strlen($chars);

		for ($i = 0, $result = ''; $i < $length; $i++) {
			$index = rand(0, $count - 1);
			$result .= mb_substr($chars, $index, 1);
		}
		return $result;
	}

//	Password Check
	function checkPassword($pwd) {
		$error = '';
		if (strlen($pwd) < 6) {$error .= "Password too short, min charater limit 6.\n";}
		if (strlen($pwd) > 15) {$error .= "Password too long, max charater limit 15.\n";}
		if (!preg_match("#[0-9]+#", $pwd)) {$error .= "Password must include at least one number.\n";}
		if (!preg_match("#[a-zA-Z]+#", $pwd)) {$error .= "Password must include at least one letter.\n";}
		return $error;
	}

//	URL Rewrite
	function url_rewrite($url1) {
		$url1 = str_replace('/', '', $url1);
		$url1 = str_replace(' ', '-', $url1);
		$url1 = str_replace('(', '', $url1);
		$url1 = str_replace(')', '', $url1);
		$url1 = str_replace('{', '', $url1);
		$url1 = str_replace('}', '', $url1);
		$url1 = str_replace('[', '', $url1);
		$url1 = str_replace(']', '', $url1);
		$url1 = str_replace('@', '', $url1);
		$url1 = str_replace('--', '-', $url1);
		$url1 = str_replace('--', '-', $url1);
		$url1 = str_replace('--', '-', $url1);
		$url1 = str_replace('&', 'and', $url1);
		$url1 = str_replace(',', '', $url1);
		$url1 = str_replace('/', '', $url1);
		$url1 = str_replace('|', '', $url1);
		$url1 = str_replace("'", '', $url1);
		$url1 = str_replace('.', '', $url1);
		$url1 = str_replace('?', '', $url1);
		$url1 = str_replace('(', '', $url1);
		$url1 = str_replace(')', '', $url1);
		$url1 = str_replace('%', '', $url1);
		$url1 = str_replace('--', '-', $url1);
		$url1 = str_replace('--', '-', $url1);
		$url1 = str_replace('--', '-', $url1);
		return strtolower($url1);
	}

//	Encrypt Password
	function psd_encrypt($psd) {
		$psd = base64_encode($psd);
		for ($i = 1; $i <= 100; $i++) {$psd = md5($psd);}
		return base64_encode($psd);
	}

	function protect_user_device() {
		if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $_SERVER["HTTP_USER_AGENT"]) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($_SERVER["HTTP_USER_AGENT"], 0, 4))) {return ('Mobile');} else {return ('Desktop');}
	}

}
?>