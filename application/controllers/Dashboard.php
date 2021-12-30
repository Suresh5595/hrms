<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('api_new/common1_model');
		$this->load->model('common_model');
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
					if (isset($params['user_id']) && !empty($params['user_id'])) 
					{
						$from_date = date('Y-01-01');
						$to_date = date('Y-12-31');
						// permission
						$permission_hours=$this->common_model->query("select a.fromtime,a.totime,a.date from hrms_permission as a where a.main_status=1 AND (a.date>='".$from_date."' AND a.date<='".$to_date."') AND a.staff_id_fk=".$params['user_id']);						
						$permission_hours ? $permission_count=count($permission_hours) : $permission_count=0;
						// echo $str = $this->db->last_query();						
						$per_hours=0;
						if($permission_hours)
						foreach($permission_hours as $hours)
						{
							$time1 = strtotime($hours['fromtime']);
							$time2 = strtotime($hours['totime']);
							$per_hours += round(abs($time2 - $time1) / 3600,2);						
						}
						// nightshift
						$nightshift_hours=$this->common_model->query("select a.fromtime,a.totime from hrms_nightshift as a where a.main_status=1 AND (a.date>='".$from_date."' AND a.date<='".$to_date."') AND a.staff_id_fk=".$params['user_id']);						
						$nightshift_hours ? $nightshift_count=count($nightshift_hours) : $nightshift_count=0;
						$nishft_hours=0;
						if($nightshift_hours)
						foreach($nightshift_hours as $hours)
						{
							$time1 = strtotime($hours['fromtime']);
							$time2 = strtotime($hours['totime']);
							$nishft_hours += round(abs($time2 - $time1) / 3600,2);						
						}
						// delegation
						$delegation_hours=$this->common_model->query("select a.fromtime,a.totime from hrms_delegation as a where a.main_status=1 AND (a.date>='".$from_date."' AND a.date<='".$to_date."') AND a.staff_id_fk=".$params['user_id']);						
						$delegation_hours ? $delegation_count=count($delegation_hours) : $delegation_count=0;
						$delg_hours=0;
						if($delegation_hours)
						foreach($delegation_hours as $hours)
						{
							$time1 = strtotime($hours['fromtime']);
							$time2 = strtotime($hours['totime']);
							$delg_hours += round(abs($time2 - $time1) / 3600,2);						
						}						
						// leave
						$leaves=$this->common_model->query('select * from hrms_leave_type');												
						$lev_days=0;
						$leave_list=array();		
						if($leaves)				
						foreach($leaves as $leave)
						{					
							$type_days=0;								
							$leave_days=$this->common_model->query("select from_date,to_date,duration,type from hrms_leave WHERE main_status=1 AND (from_date>='".$from_date."' AND to_date<='".$to_date."') AND LOWER(type)='".strtolower($leave['type_name'])."' AND staff_id_fk=".$params['user_id']);						
							if($leave_days)
							foreach($leave_days as $days)
							{
								$date1 = strtotime($days['from_date']);
								$date2 = strtotime($days['to_date']);
								$date2+=(60*60*24)-1;
								// $lev_days += round(abs($date2 - $date1) /60/60/24,2);									
                                if($days['duration']==0){
									$lev_days += round(abs($date2 - $date1) /60/60/24,2);																	
								}
								else{						
									$count = round(abs($date2 - $date1) /60/60/24,2);			
									$lev_days += $count*0.5;
								}
								//$type_days += round(abs($date2 - $date1) /60/60/24,2);
                                if($days['duration']==0){
									$type_days += round(abs($date2 - $date1) /60/60/24,2);																								
								}
								else{									
									$count = round(abs($date2 - $date1) /60/60/24,2);			
									$type_days += $count*0.5;
								}																	
							}                            
							$leave_list[strtolower(str_replace(' ','_',trim($leave['type_name'])))]=$type_days;
							$leave_list[strtolower(str_replace(' ','_',trim($leave['type_name']).'_balance'))]=$leave['no_of_days']-$type_days;
						}
						
						$resp = array('emp_name' => get_val('name','biometricAccess',$params['user_id'],'hrms_staffmaster'),
									 'emp_code' => $params['user_id'],
									 'leave' => $lev_days,
									 'absent' =>  0,									 
									 'permission' => $permission_count,
									 'permission_hours' => $per_hours,
									 'late' => 0,
									 'late_hours' =>  0,									 									
									 'night_shift' => $nightshift_count,									 
									 'night_shift_hours' => $nishft_hours,
									 'delegation' => $delegation_count,
									 'delegation_hours' => $delg_hours);		  									 
					}
					else
					{
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');						      		
					}
					json_output($response['status'],array_merge($resp,$leave_list));
	        	}
			}
		}
	}

	public function welcome_details(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			$check_auth_user = $this->login->check_auth_user();
			if($check_auth_user == true){
	        	$response = $this->login->auth();
	        	if($response['status'] == 200){
					$params = json_decode(file_get_contents('php://input'), TRUE);
					if (isset($params['user_id']) && !empty($params['user_id'])) 
					{
                        $leave_pending=$this->common_model->query("SELECT count(*) FROM `hrms_leave` INNER JOIN `hrms_staffmaster` ON `hrms_leave`.`staff_id_fk` = `hrms_staffmaster`.`biometricAccess` WHERE (`fwd1` = ".$params['user_id']." and `fwd_1_status` = 0 AND main_status=0) OR (`fwd2` = ".$params['user_id']." and `fwd_2_status` = 0 and `fwd_1_status` = 1 AND main_status=0) OR (`fwd3` = ".$params['user_id']." and `fwd_3_status` = 0 and `fwd_2_status` = 1 and `fwd_1_status` = 1 AND main_status=0) OR (`fwd4` = ".$params['user_id']." and `fwd_4_status` = 0 and `fwd_3_status` = 1 and `fwd_2_status` = 1 and `fwd_1_status` = 1 AND main_status=0)");                        
                        $permission_pending=$this->common_model->query("SELECT count(*) FROM `hrms_permission` INNER JOIN `hrms_staffmaster` ON `hrms_permission`.`staff_id_fk` = `hrms_staffmaster`.`biometricAccess` WHERE (`fwd1` = ".$params['user_id']." and `fwd_1_status` = 0 AND main_status=0) OR (`fwd2` = ".$params['user_id']." and `fwd_2_status` = 0 and `fwd_1_status` = 1 AND main_status=0) OR (`fwd3` = ".$params['user_id']." and `fwd_3_status` = 0 and `fwd_2_status` = 1 and `fwd_1_status` = 1 AND main_status=0) OR (`fwd4` = ".$params['user_id']." and `fwd_4_status` = 0 and `fwd_3_status` = 1 and `fwd_2_status` = 1 and `fwd_1_status` = 1 AND main_status=0)");                        
                        $delegation_pending=$this->common_model->query("SELECT count(*) FROM `hrms_delegation` INNER JOIN `hrms_staffmaster` ON `hrms_delegation`.`staff_id_fk` = `hrms_staffmaster`.`biometricAccess` WHERE (`fwd1` = ".$params['user_id']." and `fwd_1_status` = 0 AND main_status=0) OR (`fwd2` = ".$params['user_id']." and `fwd_2_status` = 0 and `fwd_1_status` = 1 AND main_status=0) OR (`fwd3` = ".$params['user_id']." and `fwd_3_status` = 0 and `fwd_2_status` = 1 and `fwd_1_status` = 1 AND main_status=0) OR (`fwd4` = ".$params['user_id']." and `fwd_4_status` = 0 and `fwd_3_status` = 1 and `fwd_2_status` = 1 and `fwd_1_status` = 1 AND main_status=0)");                        
                        $nightshift_pending=$this->common_model->query("SELECT count(*) FROM `hrms_nightshift` INNER JOIN `hrms_staffmaster` ON `hrms_nightshift`.`staff_id_fk` = `hrms_staffmaster`.`biometricAccess` WHERE (`fwd1` = ".$params['user_id']." and `fwd_1_status` = 0 AND main_status=0) OR (`fwd2` = ".$params['user_id']." and `fwd_2_status` = 0 and `fwd_1_status` = 1 AND main_status=0) OR (`fwd3` = ".$params['user_id']." and `fwd_3_status` = 0 and `fwd_2_status` = 1 and `fwd_1_status` = 1 AND main_status=0) OR (`fwd4` = ".$params['user_id']." and `fwd_4_status` = 0 and `fwd_3_status` = 1 and `fwd_2_status` = 1 and `fwd_1_status` = 1 AND main_status=0)");                        
                        $request_pending=$leave_pending[0]['count(*)']+$permission_pending[0]['count(*)']+$delegation_pending[0]['count(*)']+$nightshift_pending[0]['count(*)'];                        

						$branch_id=get_val('branch_id','biometricAccess',$params['user_id'],'hrms_staffmaster');
						$birth_res=$this->common_model->query("select a.id,a.name,b.name as department from hrms_staffmaster as a Left join hrms_department as b on a.department_id=b.id where DATE_FORMAT(a.dob,'%m') = DATE_FORMAT(curdate(),'%m') AND DATE_FORMAT(a.dob,'%d') = DATE_FORMAT(curdate(),'%d') AND a.branch_id=".$branch_id);				                    
						$anniversary_res=$this->common_model->query("select a.id,a.name,b.name as department,TIMESTAMPDIFF(YEAR, date(a.doj), CURDATE()) AS anniversary from hrms_staffmaster as a Left join hrms_department as b on a.department_id=b.id where DATE_FORMAT(a.doj,'%d') = DATE_FORMAT(curdate(),'%d') AND DATE_FORMAT(a.doj,'%m') = DATE_FORMAT(curdate(),'%m') AND DATE_FORMAT(a.doj,'%Y') != DATE_FORMAT(curdate(),'%Y') AND a.branch_id=".$branch_id);								
						$new_res=$this->common_model->query("select a.id,a.name,b.name as department from hrms_staffmaster as a Left join hrms_department as b on a.department_id=b.id where date(a.doj) = CURDATE() AND a.branch_id=".$branch_id);												
						// echo'<pre>';print_r($branch_id);die();		
						$resp = array('request_pending' => $request_pending,
									'birthday' => $birth_res,
									'anniversary' =>  $anniversary_res,
									'new_joinee' =>  $new_res);						      								
					}
					else
					{
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');						      		
					}
					json_output($response['status'],$resp);
	        	}
			}
		}
	}
}
