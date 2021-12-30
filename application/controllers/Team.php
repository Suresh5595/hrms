<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Team extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('api_new/common1_model');

    }

    public function create_team(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			$check_auth_user = $this->login->check_auth_user();
			if($check_auth_user == true){
	        	$response = $this->login->auth();
	        	if($response['status'] == 200){
					$params = json_decode(file_get_contents('php://input'), TRUE);
					if ($params['team_name'] == "" || $params['team_head_id'] == "" || $params['company_id_fk'] == '' || $params['branch_id_fk'] == ''){
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					}else{
						$params['date_added'] = date('Y-m-d H:i:s');
						$members  = $params['members'];
						unset($params['members']);
						//$exist = $this->common_model->checkLeave($params);
						//if(!$exist){
		        			$team_id = $this->queries->insertByid('hrms_team',$params);
							if($members){
								foreach($members as $val){
									$team_members = array(
										"team_id_fk" => $team_id,
										"emp_id_fk"  => $val['emp_id_fk'],
										"date_added" => $params['date_added'],
									);
									$resp = $this->queries->insert('hrms_team_members',$team_members);
								}
							}else{
								$resp = array('status' => 200,'message' =>  'Team Created successfully but member list is empty');
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
	public function updateTeam(){
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
					if ($params['team_id'] == "" || $params['team_name'] == "" || $params['team_head_id'] == "" || $params['company_id_fk'] == '' || $params['branch_id_fk'] == '') {
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					} else {
                        $params['date_added'] = date('Y-m-d H:i:s');
						$team_id = $params['team_id'];
						unset($params['team_id']);
						$members = $params['members'];
						unset($params['members']);
						$resp = $this->queries->update('team_id',$team_id,'hrms_team',$params);
						$this->queries->delete('team_id_fk',$team_id,'hrms_team_members');
						if($members){
							foreach($members as $val){
								$team_members = array(
										"team_id_fk" => $team_id,
										"emp_id_fk"  => $val['emp_id_fk'],
										"date_added" => $params['date_added'],
									);
								$this->queries->insert('hrms_team_members',$team_members);
							}
						}
						
					}
					json_output($respStatus,$resp);
		        }
			}
		}
	}

	public function getTeamList(){
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
                    $data = [];
					if ($params['team_id'] == "") {
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					} else {
						$team_id = $params['team_id'];
						unset($params['team_id']);
						$where = array('team_id' => $team_id);
						$results = $this->queries->query('hrms_team',$where,'row');
						if($results){
							$data_member = [];
							$where1 = array("team_id_fk" => $results->team_id);
							$getMembers = $this->queries->query('hrms_team_members',$where1,'result');
							if($getMembers){
								foreach($getMembers as $member){
									$data_member[] = array(
										"team_id_fk" => $member->team_id_fk,
										"emp_id_fk" => $member->emp_id_fk,
										"emp_code" => get_val('biometricAccess','id',$member->emp_id_fk,'hrms_staffmaster'),
										"employee" => get_val('name','id',$member->emp_id_fk,'hrms_staffmaster'),
										"company_id" => get_val('company_id','id',$member->emp_id_fk,'hrms_staffmaster'),
										"company_name" => get_val('name','id',get_val('company_id','id',$member->emp_id_fk,'hrms_staffmaster'),'hrms_company'),
										"branch_id" => get_val('branch_id','id',$member->emp_id_fk,'hrms_staffmaster'),
										"branch_name" => get_val('name','id',get_val('branch_id','id',$member->emp_id_fk,'hrms_staffmaster'),'hrms_branch'),
										"department_id" => get_val('department_id','id',$member->emp_id_fk,'hrms_staffmaster'),
										"department_name" => get_val('name',"id",get_val('department_id','id',$member->emp_id_fk,'hrms_staffmaster'),'hrms_department'),
										"designation_id" => get_val('designation_id','id',$member->emp_id_fk,'hrms_staffmaster'),
										"designation_name" => get_val('name','id',get_val('designation_id','id',$member->emp_id_fk,'hrms_staffmaster'),'hrms_designation'),
									);
                                    
								}
							}
							$data = array(
								"team_id" => $results->team_id,
								"team_name" => $results->team_name,
								"team_company_id_fk" => $results->company_id_fk,
								"team_company_name" => get_val('name','id',$results->company_id_fk,'hrms_company'),
								"team_branch_id_fk" =>$results->branch_id_fk,
								"tean_branch_name" => get_val('name','id',$results->branch_id_fk,'hrms_branch'),
								"team_department_id_fk" =>$results->department_id_fk,
								"team_branch_name" => get_val('name','id',$results->department_id_fk,'hrms_branch'),
								"team_head_id" => $results->team_head_id,
								"team_head_emp_code" => get_val('biometricAccess','id',$results->team_head_id,'hrms_staffmaster'),
								"employee" => get_val('name','id',$results->team_head_id,'hrms_staffmaster'),
								"company_id" => get_val('company_id','id',$results->team_head_id,'hrms_staffmaster'),
								"company_name" => get_val('name','id',get_val('company_id','id',$results->team_head_id,'hrms_staffmaster'),'hrms_company'),
								"branch_id" => get_val('branch_id','id',$results->team_head_id,'hrms_staffmaster'),
								"branch_name" => get_val('name','id',get_val('branch_id','id',$results->team_head_id,'hrms_staffmaster'),'hrms_branch'),
							    "department_id" => get_val('department_id','id',$results->team_head_id,'hrms_staffmaster'),
							    "department_name" => get_val('name',"id",get_val('department_id','id',$results->team_head_id,'hrms_staffmaster'),'hrms_department'),
								"designation_id" => get_val('designation_id','id',$results->team_head_id,'hrms_staffmaster'),
								"designation_name" => get_val('name','id',get_val('designation_id','id',$results->team_head_id,'hrms_staffmaster'),'hrms_designation'),
								"members" => $data_member,

							);
						}
					}
                    if($data){
                    	$resp = array('status' => 200,'message' => 'success','data' => $data);
                    }else{
                        $resp = array('status' => 200,'message' => 'success','data' => []);
                    }
					json_output($respStatus,$resp);
		        }
			}
		}
	}

	public function getAllTeamList(){
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
                    $data = [];
						$results = $this->queries->query('hrms_team','','result');
						if($results){
							foreach($results as $result){
								$data_member = [];
								$where1 = array("team_id_fk" => $result->team_id);
								$getMembers = $this->queries->query('hrms_team_members',$where1,'result');
								if($getMembers){
									foreach($getMembers as $member){
										$data_member[] = array(
											"team_id_fk" => $member->team_id_fk,
											"emp_code" => get_val('biometricAccess','id',$member->emp_id_fk,'hrms_staffmaster'),
											"emp_id_fk" => $member->emp_id_fk,
											"employee" => get_val('name','id',$member->emp_id_fk,'hrms_staffmaster'),
											"company_id" => get_val('company_id','id',$member->emp_id_fk,'hrms_staffmaster'),
											"company_name" => get_val('name','id',get_val('company_id','id',$member->emp_id_fk,'hrms_staffmaster'),'hrms_company'),
											"branch_id" => get_val('branch_id','id',$member->emp_id_fk,'hrms_staffmaster'),
											"branch_name" => get_val('name','id',get_val('branch_id','id',$member->emp_id_fk,'hrms_staffmaster'),'hrms_branch'),
											"department_id" => get_val('department_id','id',$member->emp_id_fk,'hrms_staffmaster'),
											"department_name" => get_val('name',"id",get_val('department_id','id',$member->emp_id_fk,'hrms_staffmaster'),'hrms_department'),
											"designation_id" => get_val('designation_id','id',$member->emp_id_fk,'hrms_staffmaster'),
											"designation_name" => get_val('name','id',get_val('designation_id','id',$member->emp_id_fk,'hrms_staffmaster'),'hrms_designation'),
										);
	                                    
									}
								}
								$data[] = array(
									"team_id" => $result->team_id,
									"team_name" => $result->team_name,
									"team_company_id_fk" => $result->company_id_fk,
									"team_company_name" => get_val('name','id',$result->company_id_fk,'hrms_company'),
									"team_branch_id_fk" =>$result->branch_id_fk,
									"tean_branch_name" => get_val('name','id',$result->branch_id_fk,'hrms_branch'),
									"team_department_id_fk" =>$result->department_id_fk,
									"team_branch_name" => get_val('name','id',$result->department_id_fk,'hrms_branch'),
									"team_head_id" => $result->team_head_id,
									"team_head_emp_code" => get_val('biometricAccess','id',$result->team_head_id,'hrms_staffmaster'),
									"employee" => get_val('name','id',$result->team_head_id,'hrms_staffmaster'),
									"company_id" => get_val('company_id','id',$result->team_head_id,'hrms_staffmaster'),
									"company_name" => get_val('name','id',get_val('company_id','id',$result->team_head_id,'hrms_staffmaster'),'hrms_company'),
									"branch_id" => get_val('branch_id','id',$result->team_head_id,'hrms_staffmaster'),
									"branch_name" => get_val('name','id',get_val('branch_id','id',$result->team_head_id,'hrms_staffmaster'),'hrms_branch'),
								    "department_id" => get_val('department_id','id',$result->team_head_id,'hrms_staffmaster'),
								    "department_name" => get_val('name',"id",get_val('department_id','id',$result->team_head_id,'hrms_staffmaster'),'hrms_department'),
									"designation_id" => get_val('designation_id','id',$result->team_head_id,'hrms_staffmaster'),
									"designation_name" => get_val('name','id',get_val('designation_id','id',$result->team_head_id,'hrms_staffmaster'),'hrms_designation'),
									"members" => $data_member,

								);
							}
						}
                    if($data){
                    	$resp = array('status' => 200,'message' => 'success','data' => $data);
                    }else{
                        $resp = array('status' => 200,'message' => 'success','data' => []);
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

					if ($params['team_id'] == "") {

						$respStatus = 400;

						$resp = array('status' => 400,'message' =>  'Fields Missing');

					} else {

							$this->queries->delete('team_id',$params['team_id'],'hrms_team');
							$resp = $this->queries->delete('team_id_fk',$params['team_id'],'hrms_team_members');

					}

					json_output($respStatus,$resp);

		        }

			}

		}

	}
}
