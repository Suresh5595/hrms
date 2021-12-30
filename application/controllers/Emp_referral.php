<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Emp_referral extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('api_new/common1_model');

    }

    public function apply(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			$check_auth_user = $this->login->check_auth_user();
			if($check_auth_user == true){
	        	$response = $this->login->auth();
	        	if($response['status'] == 200){
					$params = json_decode(file_get_contents('php://input'), TRUE);
					if ($params['staff_id_fk'] == "" || $params['type'] == "" || $params['name'] == "" || $params['phone'] == "" || $params['notes'] == ""){
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					}else{
						$params['date_added'] = date('Y-m-d H:i:s');
						//$exist = $this->common_model->checkLeave($params);
						//if(!$exist){
		        			$resp = $this->queries->insert('hrms_emp_referral',$params);
		        		/*}else{
		        			$resp = array('status' => 409,'message' =>  'Menu Name is already exist');
		        		}*/

					}
					json_output($response['status'],$resp);
	        	}
			}
		}
	}

	public function getReferralList(){
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
						$params['date_added'] = date('Y-m-d H:i:s');
						//$exist = $this->common_model->checkLeave($params);
						//if(!$exist){
		        			$results = $this->common1_model->getReferralList($params);
		        			if($results){
		        				foreach($results as $val){
		        					$data[] = array(
		        						"referral_id" => $val->referral_id,
		        						"staff_id_fk" => $val->staff_id_fk,
										"biometricAccess" => $val->biometricAccess,
		        						"employee" => get_val('name','id',$val->staff_id_fk,'hrms_staffmaster'),
		        						"company_id" => get_val('company_id','id',$val->staff_id_fk,'hrms_staffmaster'),
		        						"company_name" => get_val('name','id',get_val('company_id','id',$val->staff_id_fk,'hrms_staffmaster'),'hrms_company'),
		        						"branch_id" => get_val('branch_id','id',$val->staff_id_fk,'hrms_staffmaster'),
										"branch_name" => get_val('name','id',get_val('branch_id','id',$val->staff_id_fk,'hrms_staffmaster'),'hrms_branch'),
		        						"department_id" => get_val('department_id','id',$val->staff_id_fk,'hrms_staffmaster'),
		        						"department_name" => get_val('name',"id",get_val('department_id','id',$val->staff_id_fk,'hrms_staffmaster'),'hrms_department'),
		        						"designation_id" => get_val('designation_id','id',$val->staff_id_fk,'hrms_staffmaster'),
		        						"designation_name" => get_val('name','id',get_val('designation_id','id',$val->staff_id_fk,'hrms_staffmaster'),'hrms_designation'),
		        						"notes" => $val->notes,
		        						"date_added" => $val->date_added
		        					);
		        				}
		        				$resp = array('status' => 200,'message' => 'success','data' => $data);
		        			}else{
								$resp = array('status' => 200,'message' => 'success','data' => []);
							}
		        		/*}else{
		        			$resp = array('status' => 409,'message' =>  'Menu Name is already exist');
		        		}*/

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
					if ($params['referral_id'] == "" || $params['staff_id_fk'] == "" || $params['type'] == "" || $params['name'] == "" || $params['phone'] == "" || $params['notes'] == "") {
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					} else {
						$referral_id = $params['referral_id'];
						unset($params['referral_id']);
						/*$exist = $this->queries->updateCheckExist('menu_name',$params['menu_name'],'menu_id',$menu_id,'hrms_menu');
						if(!$exist){*/
		        			$resp = $this->queries->update('referral_id',$referral_id,'hrms_emp_referral',$params);
		        		/*}else{
		        			$resp = array('status' => 409,'message' =>  'Menu Name is already exist');
		        		}*/
					}
					json_output($respStatus,$resp);
		        }
			}
		}
	}
}
