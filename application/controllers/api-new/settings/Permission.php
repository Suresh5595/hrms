<?php
header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
defined('BASEPATH') OR exit('No direct script access allowed');

class Permission extends CI_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->helper('json_output');
	}

	public function getPermission(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'GET' && $method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			$check_auth_user = $this->login->check_auth_user();
			if($check_auth_user == true){
	        	$response = $this->login->auth();
	        	if($response['status'] == 200){
	        		$params = json_decode(file_get_contents('php://input'), TRUE);
					if ($params['user_type_id_fk'] == "") {
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					} else {
						$results = $this->menu_permission->getPermission($params['user_type_id_fk']);
						if($results){
							$resp = array('status' => 200,'message' => 'success','data' => $results);
						}else{
							$resp = array('status' => 204,'message' =>  'Record Not Found','data' => []);
						}
					}
    				json_output($response['status'],$resp);
	        	}
			}
		}
	}

	public function savePermission(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'GET' && $method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			$check_auth_user = $this->login->check_auth_user();
			if($check_auth_user == true){
	        	$response = $this->login->auth();
	        	if($response['status'] == 200){
	        		$params = json_decode(file_get_contents('php://input'), TRUE);
					$params['permission_list'] = (array) $params['permission_list'];
					if (empty($params['permission_list']) || $params['user_type_id_fk'] == "") {
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					} else {
						$resp = $this->menu_permission->savePermission($params['permission_list'],$params['user_type_id_fk']);
					}
    				json_output($response['status'],$resp);
	        	}
			}
		}
	}
}
