<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter PHPMailer Class
 *
 * This class enables SMTP email with PHPMailer
 *
 * @category    Libraries
 * @author      CodexWorld
 * @link        https://www.codexworld.com
 */

use PHPMailer\PHPMailer\PHPMailer;

class PHPMailer_Lib {
	public function __construct() {
		log_message('Debug', 'PHPMailer class is loaded.');
	}

	public function load() {
		// Include PHPMailer library files
		require_once APPPATH . 'libraries/PHPMailer/src/Exception.php';
		require_once APPPATH . 'libraries/PHPMailer/src/PHPMailer.php';
		require_once APPPATH . 'libraries/PHPMailer/src/SMTP.php';

		$mail = new PHPMailer;
		return $mail;
	}
}