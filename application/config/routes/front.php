<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['stripe'] = 'stripe';
$route['stripe/index'] = 'stripe';

$route['home'] = 'home';

$route['what-we-do'] = 'home/pages';
$route['about-us'] = 'home/pages';
$route['pricing'] = 'home/pages';
$route['contact-us'] = 'home/contact_us';
$route['thankyou'] = 'home/pages';
$route['privacy-policy'] = 'home/pages';
$route['cookies-policy'] = 'home/pages';
$route['terms-and-conditions'] = 'home/pages';
$route['nslds-upload-instructions'] = 'home/nslds_upload_instructions';
$route['(:any)/dashboard'] = 'home/dashboard';
$route['(:any)/account_request/(:any)/(:any)'] = 'account/account_request/$1/$2/$3';

$route['client_report'] = 'cron/check_client_status';
$route['account/logout'] = 'account/logout';

//	Client / Customer
$route['(:any)/program'] = 'customer/client_program';
$route['(:any)/logout'] = 'customer/logout';
$route['(:any)/program/(:any)'] = 'customer/client_program';

$route['intake_form'] = 'customer/intake_form';
$route['intake_form/[any]'] = 'customer/intake_form';

$route['(:any)/customer_intake_form/(:any)/(:any)'] = 'home/customer_intake_form';
$route['(:any)/intake_form_document/(:any)/(:any)/(:any)'] = 'home/intake_form_document';
$route['(:any)/attestation_form/(:any)'] = 'account/customer_attestation_form';

$route['(:any)/client_login'] = 'home/client_login';
$route['(:any)/client_registration'] = 'home/client_registration';
$route['(:any)/client_registration/(:any)'] = 'home/client_registration';
$route['(:any)/verify_account/(:any)'] = 'home/verify_account';
$route['(:any)/fp'] = 'home/client_fp';

$route['(:any)/intake_form'] = 'customer/intake_form';
$route['(:any)/update_intake_form'] = 'customer/intake_form';
$route['(:any)/idr_intake_form'] = 'customer/idr_intake_form';
$route['(:any)/recertification_intake_form'] = 'customer/idr_intake_form';
$route['(:any)/recalculation_intake_form'] = 'customer/idr_intake_form';
$route['(:any)/switch_idr_intake_form'] = 'customer/idr_intake_form';
$route['(:any)/consolidation_intake_form'] = 'customer/idr_intake_form';

$route['(:any)/intake/(:any)'] = 'customer/intake_status';

//	STOP REMINDER
$route['(:any)/program/stop/(:any)'] = 'customer/stop_program_reminder';
$route['(:any)/analysis_reminder/stop/(:any)'] = 'customer/stop_analysis_reminder';
$route['(:any)/(:any)/stop/(:any)'] = 'customer/stop_intake_reminder';

//	Attestation
// attestation routes

$route['(:any)/attestation_form/(:any)'] = 'attestation/attestation';
$route['(:any)/attestation_form/view/(:any)'] = 'attestation/attestation_pdf';
$route['(:any)/attestation_form/approve/(:any)'] = 'attestation/attestation_approve';
$route['(:any)/attestation_form/edit/(:any)'] = 'attestation/attestation_edit';

//	END Attestation

//	Company Account
$route['(:any)/account'] = 'account/account';
$route['(:any)/company'] = 'account/company';
$route['(:any)/team'] = 'account/team';
$route['(:any)/team/(:any)'] = 'account/team';
$route['(:any)/team/(:any)/(:any)'] = 'account/team';
$route['(:any)/emails'] = 'account/emails';
$route['(:any)/billing'] = 'account/billing';
$route['(:any)/reminders'] = 'account/reminders';
$route['(:any)/ajaxrminder'] = 'account/ajaxrminder';
$route['(:any)/profile'] = 'account/profile';
$route['(:any)/cp'] = 'account/cp';
$route['(:any)/integrations'] = 'integration';

$route['account/cap_stop_remonder'] = 'account/cap_stop_remonder';

$route['(:any)/programs'] = 'account/programs';
$route['(:any)/programs/(:any)'] = 'account/programs';
$route['(:any)/programs/(:any)/late'] = 'account/programs';
$route['(:any)/programs/(:any)/(:any)'] = 'account/programs';
$route['(:any)/advertisement'] = 'account/advertisement';
$route['(:any)/advertisement/(:any)'] = 'account/advertisement';
$route['(:any)/advertisement/(:any)/(:any)'] = 'account/advertisement';

$route['(:any)/customer'] = 'account/customer';
$route['(:any)/customer/new'] = 'account/customer_add_edit';
$route['(:any)/customer/edit/(:any)'] = 'account/customer_add_edit';
$route['(:any)/customer/intake_summary/(:any)'] = 'account/customer_intake_summary';
$route['(:any)/customer/current_analysis/(:any)'] = 'account/customer_current_analysis';
$route['(:any)/customer/current_analysis_print/(:any)'] = 'account/customer_current_analysis_print';
$route['(:any)/customer/current_analysis/(:any)/reset_analysis'] = 'account/customer_current_analysis';

$route['(:any)/customer/(:any)'] = 'account/customer';
$route['(:any)/customer/(:any)/(:any)'] = 'account/customer';
$route['(:any)/customer/(:any)/(:any)/(:any)'] = 'account/customer';
$route['(:any)/customer/(:any)/(:any)/(:any)/(:any)'] = 'account/customer';
$route['(:any)/customer/(:any)/(:any)/(:any)/(:any)/(:any)'] = 'account/customer';
$route['(:any)/customer/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)'] = 'account/customer';
$route['(:any)/customer/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)'] = 'account/customer';
$route['(:any)/customer/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)'] = 'account/customer';

?>