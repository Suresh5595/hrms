<?php
header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
defined('BASEPATH') OR exit('No direct script access allowed');

class Setting extends CI_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->helper('json_output');
	}
	public function upload(){
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}else{
			$check_auth_user = $this->login->check_auth_user();
			if($check_auth_user == true){
	        	$response = $this->login->auth();
	        	$respStatus = $response['status'];
	        	if($response['status'] == 200){
	        		if(isset($_FILES['file_name'])){
	                    $params = json_decode(file_get_contents('php://input'), TRUE);
	                    $params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);
						$params['date_added'] = date('Y-m-d H:i:s');
						$error= array();
						$path = './uploads/files';
						$config['upload_path'] = $path;
						$config['allowed_types'] = 'jpg|png|jpeg';
						$config['remove_spaces'] = TRUE;
						$this->load->library('upload', $config);
						$this->upload->initialize($config);
						if (!$this->upload->do_upload('file_name')) {
						 	$error = array('error' => $this->upload->display_errors());
						} else {
							 $data = array('upload_data' => $this->upload->data());
					    }
					    if(empty($error)){
						    if (!empty($data['upload_data']['file_name'])) {
						 		$import_file = $data['upload_data']['file_name'];
						 	} else {
						 		$import_file = 0;
						    }
						    $data = array(
						    	"file_name" => $import_file,
						    	"url" => base_url().'uploads/files/'.$import_file,
						    	"file_type" => explode('.',$import_file)[1],
						    	"user_id_fk" => $params['user_id_fk'],
						    	"date_added" => $params['date_added'],
						    );
						    $this->queries->insert('hrms_uploads',$data);
							$resp = array('status' => 200,'message' => 'Uploaded Successfully','data' => $data);
						}else{
							return json_output(400,array('status' => 400,'message' => 'Bad request.',"errors"=> $errors,'data' => []));
						}
					}else{
						$respStatus = 400;
						$resp = array('status' => 400,'message' =>  'Fields Missing');
					}
					json_output($respStatus,$resp);
		        }
			}
		}
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
	        		$params = json_decode(file_get_contents('php://input'), TRUE);
                    $params['user_id_fk'] = $this->input->get_request_header('User-ID', TRUE);
					$results = $this->queries->query('hrms_uploads','','result');
					if($results){
						foreach($results as $result){
						    $upload_data[] = array(
						    	"file_name" => $result->file_name,
						    	"url" => $result->url,
						    	"file_type" => $result->file_type,
						    	"user_id_fk" => $result->user_id_fk,
						    	"date_added" => $result->date_added,
						    );
						}
						$resp = array('status' => 200,'message' => 'success','data' => $upload_data);
					}else{
						$resp = array('status' => 204,'message' =>  'Record Not Found','data' => []);
					}
    				json_output($response['status'],$resp);
	        	}
			}
		}
	}
}
