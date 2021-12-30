<?php
header('Access-Control-Allow-Origin: *'); //for allow any domain, insecure
header('Access-Control-Allow-Headers: *'); //for allow any headers, insecure
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE'); //method allowed
header('Content-Type: multipart/form-data;');

defined('BASEPATH') OR exit('No direct script access allowed');
class Upload extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
	}
	public function index()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		if($method != 'POST'){
			json_output(400,array('status' => 400,'message' => 'Bad request.'));
		}
		else
		{
			//print_r($_POST);
			$fileUploadError='';
			$check_auth_user = $this->login->check_auth_user();
			if($check_auth_user == true){				
	        	$response = $this->login->auth();
				$respStatus = $response['status'];
	        	if($response['status'] == 200){
					// $params = json_decode(file_get_contents('php://input'), TRUE);
					$params=$_POST;					
					// if($params['file'] == ""){
					// 	$respStatus = 400;
					// 	$resp = array('status' => 400,'message' =>  'Fields Missing');
					// }
					// else{	
						setlocale(LC_ALL,'en_US.UTF-8');

						$config['allowed_types']='*';
						$config['file_name'] = 'file_'.time();
						$picPath='files/';
						$config['upload_path']=$picPath;
						if(!is_dir($picPath)){
							mkdir($picPath, 0777, TRUE);
						}
						$this->load->library('upload',$config);
						$this->upload->initialize($config);
						if($this->upload->do_upload('file'))
						{
							$img=$this->upload->data();
							$fileName=$img['file_name'];
							$fileUrl=base_url($picPath.$img['file_name']);
							$ext = pathinfo($fileName, PATHINFO_EXTENSION);
							$is_image = 0;
							if(in_array($ext, ["jpg","jpeg","png","gif"])){
								$is_image = 1;
							}
						}
						else
						{
							$fileUploadError=$this->upload->display_errors();
						}
						if($fileUploadError=='')
						{
							$resp['message']='File Uploaded';
							$resp['fileurl']=$fileUrl;
							$resp['filename']='files/'.$fileName;
							$resp['extension']=$ext;
							$resp['is_image']=$is_image;
						}
						else
						{
							$resp['status']=400;				
							$resp['message']=$fileUploadError;
						}
					// }	
					json_output($respStatus,$resp);
				}							
			}
		}
		
	}
}