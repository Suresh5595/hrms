<?php
header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
defined('BASEPATH') OR exit('No direct script access allowed');

class Menu extends CI_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->helper('json_output');
	}

	public function getlist(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'GET' && $method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			$check_auth_user = $this->login->check_auth_user();
			if($check_auth_user == true){
	        	$response = $this->login->auth();
	        	if($response['status'] == 200){
					$results = $this->queries->query('hrms_menu','','result');
					if($results){
						$resp = array('status' => 200,'message' => 'success','data' => $results);
					}else{
						$resp = array('status' => 204,'message' =>  'Record Not Found','data' => []);
					}
    				json_output($response['status'],$resp);
	        	}
			}
		}
	}

	public function view(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			$check_auth_user = $this->login->check_auth_user();
			if($check_auth_user == true){
	        	$response = $this->login->auth();
	        	if($response['status'] == 200){
					$params = json_decode(file_get_contents('php://input'), TRUE);
					if ($params['menu_id'] == ""){
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					}else{
						$where = array('menu_id' => $params['menu_id']);
						$result = $this->queries->query('hrms_menu',$where,'row');
						if($result){
							$resp = array('status' => 200,'message' => 'success','data' => $result);
						}else{
							$resp = array('status' => 200,'message' => 'success','data' => []);
						}

					}
					json_output($response['status'],$resp);
	        	}
			}
		}
	}

	public function create(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			$check_auth_user = $this->login->check_auth_user();
			if($check_auth_user == true){
	        	$response = $this->login->auth();
	        	$respStatus = $response['status'];
	        	if($response['status'] == 200){
                    $params = json_decode(file_get_contents('php://input'), TRUE);
                    $params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);
					if ($params['menu_name'] == "" || $params['url'] == "" || $params['status'] == "") {
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					} else {
						$params['date_added'] = date('Y-m-d H:i:s');
						$exist = $this->queries->checkExist('menu_name',$params['menu_name'],'hrms_menu');
						if(!$exist){
		        			$resp = $this->queries->insert('hrms_menu',$params);
		        		}else{
		        			$resp = array('status' => 409,'message' =>  'Menu Name is already exist');
		        		}
					}
					json_output($respStatus,$resp);
		        }
			}
		}
	}


	public function update(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			$check_auth_user = $this->login->check_auth_user();
			if($check_auth_user == true){
	        	$response = $this->login->auth();
	        	$respStatus = $response['status'];
	        	if($response['status'] == 200){
                    $params = json_decode(file_get_contents('php://input'), TRUE);
                    $params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);
					if ($params['menu_id'] == "" || $params['menu_name'] == ""  || $params['url'] == "" || $params['status'] == "") {
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					} else {
						$params['date_added'] = date('Y-m-d H:i:s');
						$menu_id = $params['menu_id'];
						unset($params['menu_id']);
						$exist = $this->queries->updateCheckExist('menu_name',$params['menu_name'],'menu_id',$menu_id,'hrms_menu');
						if(!$exist){
		        			$resp = $this->queries->update('menu_id',$menu_id,'hrms_menu',$params);
		        		}else{
		        			$resp = array('status' => 409,'message' =>  'Menu Name is already exist');
		        		}
					}
					json_output($respStatus,$resp);
		        }
			}
		}
	}

	public function delete(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			$check_auth_user = $this->login->check_auth_user();
			if($check_auth_user == true){
	        	$response = $this->login->auth();
	        	$respStatus = $response['status'];
	        	if($response['status'] == 200){
                    $params = json_decode(file_get_contents('php://input'), TRUE);
                    $params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);
					if ($params['menu_id'] == "") {
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					} else {
							$resp = $this->queries->delete('menu_id',$params['menu_id'],'hrms_menu');
					}
					json_output($respStatus,$resp);
		        }
			}
		}
	}

}
