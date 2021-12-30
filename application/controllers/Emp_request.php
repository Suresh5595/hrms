<?php



defined('BASEPATH') OR exit('No direct script access allowed');



class Emp_request extends CI_Controller

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

					if ($params['staff_id_fk'] == "" || $params['description'] == ""){

						$respStatus = 400;

						$resp = array('status' => 400,'message' =>  'Fields Missing');

					}else{

						$params['date_added'] = date('Y-m-d H:i:s');

						$params['status'] = 'Pending';

						//$exist = $this->common_model->checkLeave($params);

						//if(!$exist){

		        			$resp = $this->queries->insert('hrms_emp_request',$params);

		        		/*}else{

		        			$resp = array('status' => 409,'message' =>  'Menu Name is already exist');

		        		}*/



					}

					json_output($response['status'],$resp);

	        	}

			}

		}

	}



	public function getRequestList(){

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

		        			$results = $this->common1_model->getRequestList($params);

		        			if($results){

		        				foreach($results as $val){

		        					$data[] = array(

		        						"request_id" => $val->request_id,

		        						"staff_id_fk" => $val->staff_id_fk,

		        						"employee" => get_val('name','id',$val->staff_id_fk,'hrms_staffmaster'),

		        						"company_id" => get_val('company_id','id',$val->staff_id_fk,'hrms_staffmaster'),

		        						"company_name" => get_val('name','id',get_val('company_id','id',$val->staff_id_fk,'hrms_staffmaster'),'hrms_company'),

		        						"branch_id" => get_val('branch_id','id',$val->staff_id_fk,'hrms_staffmaster'),

		        						"branch_name" => get_val('name','id',get_val('branch_id','id',$val->staff_id_fk,'hrms_staffmaster'),'hrms_branch'),

		        						"department_id" => get_val('department_id','id',$val->staff_id_fk,'hrms_staffmaster'),

		        						"department_name" => get_val('name',"id",get_val('department_id','id',$val->staff_id_fk,'hrms_staffmaster'),'hrms_department'),

		        						"designation_id" => get_val('designation_id','id',$val->staff_id_fk,'hrms_staffmaster'),

		        						"designation_name" => get_val('name','id',get_val('designation_id','id',$val->staff_id_fk,'hrms_staffmaster'),'hrms_designation'),

		        						"description" => $val->description,

		        						"request_no" => $val->request_no,

		        						"notes" => $val->notes,

		        						"status" => $val->status,

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

					if ($params['request_id'] == "" || $params['staff_id_fk'] == "" || $params['request_no'] == "" || $params['notes'] == "" || $params['status'] == "") {

						$respStatus = 400;

						$resp = array('status' => 400,'message' =>  'Fields Missing');

					} else {

						$request_id = $params['request_id'];

						unset($params['request_id']);

						/*$exist = $this->queries->updateCheckExist('menu_name',$params['menu_name'],'menu_id',$menu_id,'hrms_menu');

						if(!$exist){*/

		        			$resp = $this->queries->update('request_id',$request_id,'hrms_emp_request',$params);

		        		/*}else{

		        			$resp = array('status' => 409,'message' =>  'Menu Name is already exist');

		        		}*/

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

					if ($params['request_id'] == "") {

						$respStatus = 400;

						$resp = array('status' => 400,'message' =>  'Fields Missing');

					} else {

						$request_id = $params['request_id'];

						unset($params['request_id']);



						$result = $this->queries->update('request_id',$request_id,'hrms_emp_request',array('status'=>'cancelled'));

						if($result){

							$resp = array('status' => 400,'message' =>  'Your Cancel Request has been Success');

						}else{

							$resp = array('status' => 400,'message' =>  'Your Cancel Request Faild');

						}

						

					}

					json_output($respStatus,$resp);

		        }

			}

		}

	}

}