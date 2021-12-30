<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Permission extends CI_Controller
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
					if ($params['staff_id_fk'] == "" || $params['date'] == "" || $params['reason'] == "" || $params['fromtime'] == "" || $params['totime'] == "" || $params['latitude'] == "" || $params['longitude'] == ""){
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					}else{
						$params['date_added'] = date('Y-m-d H:i:s');
						$params['main_status'] = 0;
						//$exist = $this->common_model->checkPermission($params);
						//if(!$exist){
		        			$resp = $this->queries->insert('hrms_permission',$params);
		        		/*}else{
		        			$resp = array('status' => 409,'message' =>  'Menu Name is already exist');
		        		}*/

					}
					json_output($response['status'],$resp);
	        	}
			}
		}
	}

	public function getPermissionList(){
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
		        		$resp = $this->common1_model->getPermissionList($params);		        		
						$data=array();
						if($resp)
						{
							foreach($resp as $res){
								$data[]=array("permission_id" => $res->permission_id, 
								"staff_id_fk" => $res->staff_id_fk, 
								"company_id" => get_val('name','id',$res->company_id,'hrms_company'),
								"branch_id" => get_val('name','id',$res->branch_id,'hrms_branch'),
								"emp_code" => $res->biometricAccess,
								"name" => $res->name,								
								"profileimage" => $res->profileimage, 								
								"biometricAccess" => $res->biometricAccess,								
								"fwd1" => $res->fwd1, 
								"fwd2" => $res->fwd2, 
								"fwd3" => $res->fwd3, 
								"fwd4" => $res->fwd4,
								"date" => $res->date, 								
								"reason" => $res->reason, 
								"fromtime" => $res->fromtime,
								"totime" => $res->totime, 								
								"latitude" => $res->latitude, 
								"longitude" => $res->longitude, 															
								"fwd_1_status" => $res->fwd_1_status, 
								"fwd_1_approval_date" => $res->fwd_1_approval_date, 
								"fwd_2_status" => $res->fwd_2_status, 
								"fwd_2_approval_date" => $res->fwd_2_approval_date, 
								"fwd_3_status" => $res->fwd_3_status, 
								"fwd_3_approval_date" => $res->fwd_3_approval_date, 
								"fwd_4_status" => $res->fwd_4_status, 
								"fwd_4_approval_date" => $res->fwd_4_approval_date, 
								"main_status" => $res->main_status);
							}
						}
					}
					json_output($response['status'],$data);
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
					if ($params['permission_id'] == "" || $params['staff_id_fk'] == "" || $params['date'] == "" || $params['reason'] == "" || $params['fromtime'] == "" || $params['totime'] == "" || $params['latitude'] == "" || $params['longitude'] == "") {
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					} else {
						$permission_id = $params['permission_id'];
						unset($params['permission_id']);
						/*$exist = $this->queries->updateCheckExist('menu_name',$params['menu_name'],'menu_id',$menu_id,'hrms_menu');
						if(!$exist){*/
		        			$resp = $this->queries->update('permission_id',$permission_id,'hrms_permission',$params);
		        		/*}else{
		        			$resp = array('status' => 409,'message' =>  'Menu Name is already exist');
		        		}*/
					}
					json_output($respStatus,$resp);
		        }
			}
		}
	}

	public function statusUpdate(){
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
                    $user_id = $this->input->get_request_header('User-ID', TRUE);
					if ($params['permission_id'] == "") {
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					} else {
						$permission_id = $params['permission_id'];
						unset($params['permission_id']);
						$getStaffdetail = $this->queries->query('hrms_staffmaster',array('id'=>get_val('staff_id_fk','permission_id',$permission_id,'hrms_permission')),'row');
						if($getStaffdetail){
							if($getStaffdetail->fwd1 == $user_id){
								$data['fwd_1_status'] = 1;
								$data['fwd_1_approval_date'] = date('Y-m-d H:i:s');
							}
							if($getStaffdetail->fwd2 == $user_id){
								$data['fwd_2_status'] = 1;
								$data['fwd_2_approval_date'] = date('Y-m-d H:i:s');
							}
							if($getStaffdetail->fwd3 == $user_id){
								$data['fwd_3_status'] = 1;
								$data['fwd_3_approval_date'] = date('Y-m-d H:i:s');
							}
							if($getStaffdetail->fwd4 == $user_id){
								$data['fwd_4_status'] = 1;
								$data['fwd_4_approval_date'] = date('Y-m-d H:i:s');
								$data['main_status'] = 1;
							}

		        			$resp = $this->queries->update('permission_id',$permission_id,'hrms_permission',$data);
		        		}else{
		        			$resp = array('status' => 400,'message' =>  'Status Updated Faild');
		        		}
					}
					json_output($respStatus,$resp);
		        }
			}
		}
	}

	public function cancelrequest(){
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
                    $user_id = $this->input->get_request_header('User-ID', TRUE);
					if ($params['permission_id'] == "") {
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					} else {
						$permission_id = $params['permission_id'];
						unset($params['permission_id']);

						$result = $this->queries->update('permission_id',$permission_id,'hrms_permission',array('main_status'=>2));
						if($result){
							$resp = array('status' => 200,'message' =>  'Your Cancel Request has been Success');
						}else{
							$resp = array('status' => 400,'message' =>  'Your Cancel Request Faild');
						}
						
					}
					json_output($respStatus,$resp);
		        }
			}
		}
	}

 public function getMobilePermissionList(){
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
		        		$resp = $this->common1_model->getMobilePermissionList($params);
						$data=array();
		        		if($resp){
							foreach($resp as $res){
								$designation_name='';
								if($res->fwd_1_status==0){									
									$designation_name='Pending';
								}
								else if($res->fwd_1_status==1 && $res->fwd_2_status==0){																			
									$designation_name=get_val('name','id',get_val('designation_id','id',$res->fwd1,'hrms_staffmaster'),'hrms_designation');
								}
								else if($res->fwd_1_status==1 && $res->fwd_2_status==1 && $res->fwd_3_status==0){									
									$designation_name=get_val('name','id',get_val('designation_id','id',$res->fwd2,'hrms_staffmaster'),'hrms_designation');
								}
								else if($res->fwd_1_status==1 && $res->fwd_2_status==1 && $res->fwd_3_status==1 && $res->fwd_4_status==0){									
									$designation_name=get_val('name','id',get_val('designation_id','id',$res->fwd3,'hrms_staffmaster'),'hrms_designation');
								}
								else if($res->fwd_1_status==1 && $res->fwd_2_status==1 && $res->fwd_3_status==1 && $res->fwd_4_status==1){								
									$designation_name=get_val('name','id',get_val('designation_id','id',$res->fwd4,'hrms_staffmaster'),'hrms_designation');
								}
								$data[]=array('emp_id'=>$res->emp_id,
								"name" => $res->name,
								"emp_code" => $res->biometricAccess,
								"profileimage" => $res->profileimage,
								"date" => $res->date,
								"fromtime" => $res->fromtime,
								"totime" => $res->totime,
								"permission_id" => $res->permission_id,								
								"current_status" => $designation_name,
								"main_status" => $res->main_status);
							}
						}
					}
					json_output($response['status'],$data);
	        	}
			}
		}
	}
}