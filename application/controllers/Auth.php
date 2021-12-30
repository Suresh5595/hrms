<?php
header("Access-Control-Allow-Origin: *");
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

	public function __construct()
	{
        parent::__construct();

		$this->load->helper('json_output');
        $this->load->model('common_model');
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

    public function change_password()
	{	
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{						
			$check_auth_user = $this->login->check_auth_user();
			if($check_auth_user == true){								
	        	$response = $this->login->auth();
	        	if($response['status'] == 200){								
					$params = json_decode(file_get_contents('php://input'), TRUE);
					if($params['staff_id_fk'] == "" && $params['new_password'] == ""){
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					}else{
                        if($this->common_model->get_val('id',array('biometricAccess'=>$params['staff_id_fk'],'pwd'=>$params['old_password']),'hrms_staffmaster')!=''){
                            $this->common_model->update(array('biometricAccess'=>$params['staff_id_fk']),'hrms_staffmaster',array('pwd'=>$params['new_password']));
                            $resp = array('status' => 200,'message' =>  'Password Updated Successfully');
                        }else{
                            $resp = array('status' => 400,'message' =>  'Incorrect Old Password');
                        }	
                    }
					json_output($response['status'],$resp);
				}
			}
		}
	}	
}