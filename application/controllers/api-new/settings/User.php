<?php
header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {
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
                    $where = array('is_login_portal' => 1);
					$results = $this->queries->query('hrms_staffmaster',$where,'result');
					if($results){
						foreach($results as $val){
							$data[] = array(
                                "biometricAccess" => $val->biometricAccess,
								"emp_code" => $val->id,
								"emp_name" => $val->name,
								"user_type_id" => $val->user_type_id_fk,
								"user_type_name" => get_val('usertypename','id',$val->user_type_id_fk,'hrms_user_type'),
                                "status" => $val->is_login_portal
							);
						}
						$resp = array('status' => 200,'message' => 'success','data' => $data);
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
					if ($params['staff_id_fk'] == ""){
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					}else{
						$where = array('id' => $params['staff_id_fk']);
						$result = $this->queries->query('hrms_staffmaster',$where,'row');
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
					if ($params['staff_id_fk'] == "" || $params['user_type_id_fk'] == ""  || $params['branch_id_fk'] == "" || $params['status'] == "") {
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					} else {
						$staff_id_fk = $params['staff_id_fk'];
						unset($params['staff_id_fk']);
						$status = $params['status'];
						unset($params['status']);
						$params['is_login_portal'] = $status;
						$branch_id_fk = $params['branch_id_fk'];
						unset($params['branch_id_fk']);
						if(!empty($branch_id_fk)){
							$params['assign_branch_ids'] = ','.$branch_id_fk.',';
						}else{
							$params['assign_branch_ids'] = '';
						}
		        		$resp = $this->queries->update('id',$staff_id_fk,'hrms_staffmaster',$params);
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
					if ($params['staff_id_fk'] == "" ) {
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					} else {
						$staff_id_fk = $params['staff_id_fk'];
						unset($params['staff_id_fk']);;
						$params['is_login_portal'] = 0;
						
		        		$this->queries->update('id',$staff_id_fk,'hrms_staffmaster',$params);
                        $resp = array('status' => 200,'message' => 'User Data Deleted Successfully');
					}
					json_output($respStatus,$resp);
		        }
			}
		}
	}

}
