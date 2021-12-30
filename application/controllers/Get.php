<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Get extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();        
        $this->load->model('common_model');
    }

    public function check_login(){		
		$users_id  = $this->input->get_request_header('User-ID', TRUE);
        $token     = $this->input->get_request_header('Authorization-key', TRUE);             
        		
	$result = $this->common_model->query('SELECT expired_at FROM hrms_user_auth WHERE user_id='.$users_id.' AND token="'.$token.'" order by id desc limit 1');
        
        if($result == ""){
            json_output(401,array('status' => false,'message' => 'Unauthorized.'));
        } 
		else{
			if($result[0]['expired_at'] < date('Y-m-d H:i:s'))
			{
				json_output(401,array('status' => false,'message' => 'Your session has been expired.'));				
			} else {
				json_output(200,array('status' => true,'message' => 'Authorized.'));
			}
		}
	}

	public function my_approvals(){		
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{						
			$check_auth_user = $this->login->check_auth_user();
			if($check_auth_user == true){								
	        	$response = $this->login->auth();
	        	if($response['status'] == 200){								
					$params = json_decode(file_get_contents('php://input'), TRUE);
					if($params['staff_id_fk'] == ""){
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					}else{
                        $leave = $this->common_model->query("SELECT b.name,b.biometricAccess,b.profileimage,a.leave_id,a.type,a.duration,a.reason,a.from_date,a.to_date,a.filename,b.fwd1,b.fwd2,b.fwd3,b.fwd4,a.fwd_1_status,a.fwd_2_status,a.fwd_3_status,a.fwd_4_status,a.main_status FROM `hrms_leave` as a INNER JOIN `hrms_staffmaster` as b ON `a`.`staff_id_fk` = `b`.`biometricAccess` WHERE (b.fwd1 = ".$params['staff_id_fk']." and a.fwd_1_status !=0) OR (b.fwd2 = ".$params['staff_id_fk']." and a.fwd_2_status !=0) OR (b.fwd3 = ".$params['staff_id_fk']." and a.fwd_3_status !=0) OR (b.fwd4 = ".$params['staff_id_fk']." and a.fwd_4_status !=0)");						
                       $permission = $this->common_model->query("SELECT b.name,b.biometricAccess,b.profileimage,a.permission_id,a.date,a.reason,a.fromtime,a.totime,a.duration,b.fwd1,b.fwd2,b.fwd3,b.fwd4,a.fwd_1_status,a.fwd_2_status,a.fwd_3_status,a.fwd_4_status,a.main_status FROM `hrms_permission` as a INNER JOIN `hrms_staffmaster` as b ON `a`.`staff_id_fk` = `b`.`biometricAccess` WHERE (b.fwd1 = ".$params['staff_id_fk']." and a.fwd_1_status !=0) OR (b.fwd2 = ".$params['staff_id_fk']." and a.fwd_2_status !=0) OR (b.fwd3 = ".$params['staff_id_fk']." and a.fwd_3_status !=0) OR (b.fwd4 = ".$params['staff_id_fk']." and a.fwd_4_status !=0)");										
						$nightshift = $this->common_model->query("SELECT b.name,b.biometricAccess,b.profileimage,a.nightshift_id,a.date,a.purpose,a.fromtime,a.totime,a.duration,b.fwd1,b.fwd2,b.fwd3,b.fwd4,a.fwd_1_status,a.fwd_2_status,a.fwd_3_status,a.fwd_4_status,a.main_status FROM `hrms_nightshift` as a INNER JOIN `hrms_staffmaster` as b ON `a`.`staff_id_fk` = `b`.`biometricAccess` WHERE (b.fwd1 = ".$params['staff_id_fk']." and a.fwd_1_status !=0) OR (b.fwd2 = ".$params['staff_id_fk']." and a.fwd_2_status !=0) OR (b.fwd3 = ".$params['staff_id_fk']." and a.fwd_3_status !=0) OR (b.fwd4 = ".$params['staff_id_fk']." and a.fwd_4_status !=0)");						
						$delegation = $this->common_model->query("SELECT b.name,b.biometricAccess,b.profileimage,a.delegation_id,a.date,a.purpose,a.fromtime,a.totime,a.duration,b.fwd1,b.fwd2,b.fwd3,b.fwd4,a.fwd_1_status,a.fwd_2_status,a.fwd_3_status,a.fwd_4_status,a.main_status FROM `hrms_delegation` as a INNER JOIN `hrms_staffmaster` as b ON `a`.`staff_id_fk` = `b`.`biometricAccess` WHERE (b.fwd1 = ".$params['staff_id_fk']." and a.fwd_1_status !=0) OR (b.fwd2 = ".$params['staff_id_fk']." and a.fwd_2_status !=0) OR (b.fwd3 = ".$params['staff_id_fk']." and a.fwd_3_status !=0) OR (b.fwd4 = ".$params['staff_id_fk']." and a.fwd_4_status !=0)");						
												
                        $resp=array('leave'=>$leave,
									'permission'=>$permission,
									'nightshift'=>$nightshift,
									'delegation'=>$delegation);						
					}
					json_output($response['status'],$resp);
	        	}
			}
		}
	}
	
    public function my_team(){		
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{						
			$check_auth_user = $this->login->check_auth_user();
			if($check_auth_user == true){								
	        	$response = $this->login->auth();
	        	if($response['status'] == 200){								
					$params = json_decode(file_get_contents('php://input'), TRUE);
					if($params['staff_id_fk'] == ""){
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					}else{                        
		        		$resp = $this->common_model->query('SELECT a.team_head_id,a.team_name,c.name,c.biometricAccess,c.designation_id,d.name as designation_name FROM hrms_team as a INNER JOIN hrms_team_members as b ON b.team_id_fk=a.team_id INNER JOIN hrms_staffmaster as c ON c.biometricAccess=b.emp_id_fk LEFT JOIN hrms_designation as d ON d.id=c.designation_id WHERE a.team_head_id='.$params['staff_id_fk'].' order by a.team_id asc');                        if(!$resp){
                            $resp=array();
                        }
                    }
					json_output($response['status'],$resp);
	        	}
			}
		}
	}       

	public function my_holidays(){		
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{						
			$check_auth_user = $this->login->check_auth_user();
			if($check_auth_user == true){								
	        	$response = $this->login->auth();
	        	if($response['status'] == 200){								
					$params = json_decode(file_get_contents('php://input'), TRUE);
					if($params['staff_id_fk'] == ""){
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					}else{						
                        $company_id = $this->common_model->get_val('company_id',array('biometricAccess'=>$params['staff_id_fk']),'hrms_staffmaster');
                        $branch_id = $this->common_model->get_val('branch_id',array('biometricAccess'=>$params['staff_id_fk']),'hrms_staffmaster');
		        		$result = $this->common_model->query('SELECT * FROM hrms_holiday WHERE status=1 AND companyid='.$company_id.' AND branchid='.$branch_id.' AND date>="'.date('Y-01-01').'" AND date<="'.date('Y-12-31').'" order by date asc');				
                        $resp=array();
		        		if($result){
							foreach($result as $res){
                                $resp[]=array("id" => $res['id'], 
								"date" => $res['date'], 
								"companyid" => $res['companyid'],
								"branchid" => $res['branchid'],
                                "branch_name" => get_val('name','id',$res['branchid'],'hrms_branch'),
                                "description" => $res['description']);
                            }
						}
					}
					json_output($response['status'],$resp);
	        	}
			}
		}
	}
}