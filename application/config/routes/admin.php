<?php
defined('BASEPATH') OR exit('No direct script access allowed');


$route['admin'] = 'admin';
$route['admin/login'] = 'admin/login';
$route['admin/fp'] = 'admin/fp';

$route['admin/dashboard'] = 'admin/dashboard';
$route['admin/profile'] = 'admin/profile';
$route['admin/cp'] = 'admin/cp';
$route['admin/logout'] = 'admin/logout';


$route['admin/company'] = 'admin/company';
$route['admin/delete/company/(:any)'] = 'admin/delete_record';
$route['admin/delete/company/(:any)/(:any)'] = 'admin/delete_record';
$route['admin/delete/case_manager/(:any)/(:any)'] = 'admin/delete_record';
$route['admin/delete/clients/(:any)/(:any)'] = 'admin/delete_record';



?>
