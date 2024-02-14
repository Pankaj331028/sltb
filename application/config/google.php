<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Google API Configuration
| -------------------------------------------------------------------
| 
| To get API details you have to create a Google Project
| at Google API Console (https://console.developers.google.com)
| 
|  client_id         string   Your Google API Client ID.
|  client_secret     string   Your Google API Client secret.
|  redirect_uri      string   URL to redirect back to after login.
|  application_name  string   Your Google application name.
|  api_key           string   Developer key.
|  scopes            string   Specify scopes
*/
$config['google']['client_id']        = '350218168587-ekqk59dkhva6ds0rsn24ut147rch12hq.apps.googleusercontent.com';
$config['google']['client_secret']    = 'P2NT0HsdZQX9ZfiDijTMqcZ2';
$config['google']['redirect_uri']     = 'http://localhost/codeigniter/user_authentication/';
$config['google']['application_name'] = 'Login to CodexWorld.com';
$config['google']['api_key']          = 'AIzaSyD8ZmJg4wJJQ2egnqBtO-WU6enRi8bpGiY';
$config['google']['scopes']           = array();