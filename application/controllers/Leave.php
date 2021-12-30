<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Leave extends CI_Controller
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
					if ($params['staff_id_fk'] == "" || $params['type'] == "" || $params['duration'] == "" || $params['reason'] == "" || $params['from_date'] == "" || $params['to_date'] == ""){
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					}else{
						$params['date_added'] = date('Y-m-d H:i:s');
						$params['main_status'] = 0;
						//$exist = $this->common_model->checkLeave($params);
						//if(!$exist){
		        			$resp = $this->queries->insert('hrms_leave',$params);
		        		/*}else{
		        			$resp = array('status' => 409,'message' =>  'Menu Name is already exist');
		        		}*/

					}
					json_output($response['status'],$resp);
	        	}
			}
		}
	}

	public function getLeaveList(){
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
		        		$resp = $this->common1_model->getLeaveList($params);		        		
						$data=array();
						if($resp)
						{
							foreach($resp as $res){
								$data[]=array("leave_id" => $res->leave_id, 
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
								"type" => $res->type, 
								"duration" => $res->duration,
								"reason" => $res->reason, 
								"from_date" => $res->from_date, 
								"to_date" => $res->to_date,
								"latitude" => $res->latitude, 
								"longitude" => $res->longitude, 
								"filename" => $res->filename, 								
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
					if ($params['leave_id'] == "" || $params['type'] == "" || $params['duration'] == "" || $params['reason'] == "" || $params['from_date'] == "" || $params['to_date'] == "") {
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					} else {
						$leave_id = $params['leave_id'];
						unset($params['leave_id']);
						/*$exist = $this->queries->updateCheckExist('menu_name',$params['menu_name'],'menu_id',$menu_id,'hrms_menu');
						if(!$exist){*/
		        			$resp = $this->queries->update('leave_id',$leave_id,'hrms_leave',$params);
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
					if ($params['leave_id'] == "") {
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					} else {
						$leave_id = $params['leave_id'];
						unset($params['leave_id']);
						$getStaffdetail = $this->queries->query('hrms_staffmaster',array('id'=>get_val('staff_id_fk','leave_id',$leave_id,'hrms_leave')),'row');
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
							$resp = $this->queries->update('leave_id',$leave_id,'hrms_leave',$data);
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
					if ($params['leave_id'] == "") {
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					} else {
						$leave_id = $params['leave_id'];
						unset($params['leave_id']);

						$result = $this->queries->update('leave_id',$leave_id,'hrms_leave',array('main_status'=>2));
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

    public function getMobileLeaveList(){		
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
		        		$resp = $this->common1_model->getMobileLeaveList($params);
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
								"type" => $res->type,
								"duration" => $res->duration,
								"reason" => $res->reason,
								"from_date" => $res->from_date,
								"to_date" => $res->to_date,
								"leave_id" => $res->leave_id,
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