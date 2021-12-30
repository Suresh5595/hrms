<?php
header("Access-Control-Allow-Origin: *");
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

	public function __construct()
	{
        parent::__construct();

		$this->load->helper('json_output');
	}

	public function login()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		} else {
			$check_auth_user = $this->login->check_auth_user();
			if($check_auth_user == true){
					$params = json_decode(file_get_contents('php://input'), TRUE);
		        
		        	$response = $this->login->check_login($params);
				json_output(200,$response);
			}
		}
	}

	public function logout()
	{	
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		} else {
			$check_auth_user = $this->login->check_auth_user();
			if($check_auth_user == true){
		        	$response = $this->login->logout();
				json_output($response['status'],$response);
			}
		}
	}
	
}
