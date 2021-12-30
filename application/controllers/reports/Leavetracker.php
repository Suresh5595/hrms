<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Leavetracker extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('api_new/common1_model');

    }

    public function index(){
        $method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			$check_auth_user = $this->login->check_auth_user();
			if($check_auth_user == true){
	        	$response = $this->login->auth();
	        	if($response['status'] == 200){
					$params = json_decode(file_get_contents('php://input'), TRUE);
					if ($params['type'] == ""){
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					}else{
						$result = $this->common1_model->getLeaveTracker($params);
						if($result){
							foreach($result as $val){
								if(strtolower($params['type']) == 'leave'){
									$data[] = array(
										"leave_id" => $val->leave_id,
										"emp_code" => $val->staff_id_fk,
										"emp_name" => get_val('name','id',$val->staff_id_fk,'hrms_staffmaster'),
										"type" => $val->type,
										"duration" => $val->duration,
										"reason" => $val->reason,
										"from_date" => $val->from_date,
										"to_date" => $val->to_date,
										"latitude" => $val->latitude,
										"longitude" => $val->longitude,
										"filename" => $val->filename,
										"fwd_1_status" => $val->fwd_1_status,
										"fwd_1_approval_date" => $val->fwd_1_approval_date,
										"fwd_2_status" => $val->fwd_2_status,
										"fwd_2_approval_date" => $val->fwd_2_approval_date,
										"fwd_3_status" => $val->fwd_3_status,
										"fwd_3_approval_date" => $val->fwd_3_approval_date,
										"fwd_4_status" => $val->fwd_4_status,
										"fwd_4_approval_date" => $val->fwd_4_approval_date,
										"fwd_1_emp_name" => get_val('name','id',get_val('fwd1','id',$val->staff_id_fk,'hrms_staffmaster'),'hrms_staffmaster'),
										"fwd_2_emp_name" => get_val('name','id',get_val('fwd2','id',$val->staff_id_fk,'hrms_staffmaster'),'hrms_staffmaster'),
										"fwd_3_emp_name" => get_val('name','id',get_val('fwd3','id',$val->staff_id_fk,'hrms_staffmaster'),'hrms_staffmaster'),
										"fwd_4_emp_name" => get_val('name','id',get_val('fwd4','id',$val->staff_id_fk,'hrms_staffmaster'),'hrms_staffmaster'),
										"main_status" => $val->main_status,
									);
								}

								if(strtolower($params['type']) == 'permission'){
									$data[] = array(
										"permission_id" => $val->permission_id,
										"emp_code" => $val->staff_id_fk,
										"emp_name" => get_val('name','id',$val->staff_id_fk,'hrms_staffmaster'),
										"date" => $val->date,
										"reason" => $val->reason,
										"from_time" => $val->fromtime,
										"totime" => $val->totime,
										"latitude" => $val->latitude,
										"longitude" => $val->longitude,
										"fwd_1_status" => $val->fwd_1_status,
										"fwd_1_approval_date" => $val->fwd_1_approval_date,
										"fwd_2_status" => $val->fwd_2_status,
										"fwd_2_approval_date" => $val->fwd_2_approval_date,
										"fwd_3_status" => $val->fwd_3_status,
										"fwd_3_approval_date" => $val->fwd_3_approval_date,
										"fwd_4_status" => $val->fwd_4_status,
										"fwd_4_approval_date" => $val->fwd_4_approval_date,
										"fwd_1_emp_name" => get_val('name','id',get_val('fwd1','id',$val->staff_id_fk,'hrms_staffmaster'),'hrms_staffmaster'),
										"fwd_2_emp_name" => get_val('name','id',get_val('fwd2','id',$val->staff_id_fk,'hrms_staffmaster'),'hrms_staffmaster'),
										"fwd_3_emp_name" => get_val('name','id',get_val('fwd3','id',$val->staff_id_fk,'hrms_staffmaster'),'hrms_staffmaster'),
										"fwd_4_emp_name" => get_val('name','id',get_val('fwd4','id',$val->staff_id_fk,'hrms_staffmaster'),'hrms_staffmaster'),
										"main_status" => $val->main_status,
									);
								}

								if(strtolower($params['type']) == 'nightshift'){
									$data[] = array(
										"nightshift_id" => $val->nightshift_id,
										"emp_code" => $val->staff_id_fk,
										"emp_name" => get_val('name','id',$val->staff_id_fk,'hrms_staffmaster'),
										"date" => $val->date,
										"purpose" => $val->purpose,
										"from_time" => $val->fromtime,
										"to_time" => $val->totime,
										"productionvolume" => $val->productionvolume,
										"client" => $val->client,
										"latitude" => $val->latitude,
										"longitude" => $val->longitude,
										"fwd_1_status" => $val->fwd_1_status,
										"fwd_1_approval_date" => $val->fwd_1_approval_date,
										"fwd_2_status" => $val->fwd_2_status,
										"fwd_2_approval_date" => $val->fwd_2_approval_date,
										"fwd_3_status" => $val->fwd_3_status,
										"fwd_3_approval_date" => $val->fwd_3_approval_date,
										"fwd_4_status" => $val->fwd_4_status,
										"fwd_4_approval_date" => $val->fwd_4_approval_date,
										"fwd_1_emp_name" => get_val('name','id',get_val('fwd1','id',$val->staff_id_fk,'hrms_staffmaster'),'hrms_staffmaster'),
										"fwd_2_emp_name" => get_val('name','id',get_val('fwd2','id',$val->staff_id_fk,'hrms_staffmaster'),'hrms_staffmaster'),
										"fwd_3_emp_name" => get_val('name','id',get_val('fwd3','id',$val->staff_id_fk,'hrms_staffmaster'),'hrms_staffmaster'),
										"fwd_4_emp_name" => get_val('name','id',get_val('fwd4','id',$val->staff_id_fk,'hrms_staffmaster'),'hrms_staffmaster'),
										"main_status" => $val->main_status,
									);
								}

								if(strtolower($params['type']) == 'delegation'){
									$data[] = array(
										"delegation_id" => $val->delegation_id,
										"emp_code" => $val->staff_id_fk,
										"emp_name" => get_val('name','id',$val->staff_id_fk,'hrms_staffmaster'),
										"date" => $val->date,
										"purpose" => $val->purpose,
										"from_time" => $val->fromtime,
										"to_time" => $val->totime,
										"venue" => $val->venue,
										"client" => $val->client,
										"location" => $val->location,
										"latitude" => $val->latitude,
										"longitude" => $val->longitude,
										"fwd_1_status" => $val->fwd_1_status,
										"fwd_1_approval_date" => $val->fwd_1_approval_date,
										"fwd_2_status" => $val->fwd_2_status,
										"fwd_2_approval_date" => $val->fwd_2_approval_date,
										"fwd_3_status" => $val->fwd_3_status,
										"fwd_3_approval_date" => $val->fwd_3_approval_date,
										"fwd_4_status" => $val->fwd_4_status,
										"fwd_4_approval_date" => $val->fwd_4_approval_date,
										"fwd_1_emp_name" => get_val('name','id',get_val('fwd1','id',$val->staff_id_fk,'hrms_staffmaster'),'hrms_staffmaster'),
										"fwd_2_emp_name" => get_val('name','id',get_val('fwd2','id',$val->staff_id_fk,'hrms_staffmaster'),'hrms_staffmaster'),
										"fwd_3_emp_name" => get_val('name','id',get_val('fwd3','id',$val->staff_id_fk,'hrms_staffmaster'),'hrms_staffmaster'),
										"fwd_4_emp_name" => get_val('name','id',get_val('fwd4','id',$val->staff_id_fk,'hrms_staffmaster'),'hrms_staffmaster'),
										"main_status" => $val->main_status,
									);
								}
							}
							$resp = array('status' => 200,'message' => 'success','data' => $data);
						}else{
							$resp = array('status' => 200,'message' => 'success','data' => []);
						}
					}
					json_output($response['status'],$resp);
	        	}
			}
		}
    }
}