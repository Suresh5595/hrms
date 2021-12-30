<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Login {
	private $ci;
	var $user_service   = "frontend-user";
    var $auth_key       = "zghrms";
	function __construct() {
		$this->ci =& get_instance();
    	$this->ci->load->database();
        $this->ci->load->helper('custom');
	}

	function check_auth_user(){
	    
		$user_service = $this->ci->input->get_request_header('user-Service', TRUE);
    $auth_key  = $this->ci->input->get_request_header('Auth-Key', TRUE);
    if($user_service == "frontend-user" && $auth_key == "sale_wizard"){
        return true;
    } else {
        return array('status' => 401,'message' => 'Unauthorized.');
    }
	}

	public function auth(){
	    
        $users_id  = $this->ci->input->get_request_header('User-ID', TRUE);
        $token     = $this->ci->input->get_request_header('Authorization-key', TRUE);
        // 
        $q  = $this->ci->db->select('expired_at')->from('hrms_user_auth')->where('user_id',$users_id)->where('token',$token)->get()->row();
        
        if($q == ""){
            return array('status' => 401,'message' => 'Unauthorized.');
        } else {
            
            if($q->expired_at < date('Y-m-d H:i:s')){
                return json_output(401,array('status' => 401,'message' => 'Your session has been expired.'));
            } else {
                
                $updated_at = date('Y-m-d H:i:s');
                $expired_at = date("Y-m-d H:i:s", strtotime('+12 hours'));
                $this->ci->db->where('user_id',$users_id)->where('token',$token)->update('hrms_user_auth',array('expired_at' => $expired_at,'updated_at' => $updated_at));
                
                return array('status' => 200,'message' => 'Authorized.');
            }
        }
    }

    public function check_login($params){
    	$q  = $this->ci->db->where('biometricAccess',$params['username'])->get('hrms_staffmaster')->row();
        if($q == ""){
            return array('status' => 400,'message' => 'User not found.');
        } else {
            $hashed_password = $q->pwd;
            $id              = $q->biometricAccess;
            if ($hashed_password == $params['password']) {
              if($q->is_login_portal == 0){
                return array('status' => 400,'message' => 'User Account is denied');
              }else{
                $last_login = date('Y-m-d H:i:s');
                $token = crypt(substr( md5(rand()), 0, 7),"st");
                $expired_at = date("Y-m-d H:i:s", strtotime('+12 hours'));
                $this->ci->db->trans_start();
                $this->ci->db->where('id',$id)->update('hrms_staffmaster',array('last_login' => $last_login));
                $this->ci->db->insert('hrms_user_auth',array('user_id' => $id,'token' => $token,'expired_at' => $expired_at));
                if ($this->ci->db->trans_status() === FALSE){
                $this->ci->db->trans_rollback();
                return array('status' => 500,'message' => 'Internal server error.');
                } else {
                $this->ci->db->trans_commit();
                $log_data = array(
                	"staff_id_fk" => $q->biometricAccess,
                	"type" =>$params['type'],
                	"device_id" => $params['device_id'],
                	"device_name" => $params['device_name'],
                	"device_brand" => $params['device_brand'],
                	"os_version" => $params['os_version'],
                	"os_name" => $params['os_name'],
                	"date_added" => date('Y-m-d H:i:s')
                );
                $this->ci->db->insert('hrms_staff_logs',$log_data);                
                return array(
                'status' => 200,
                'message' => 'Successfully login.',
                'data' => array(
	                'id' => $q->id, 
	                'emp_code' => $q->biometricAccess,
                    'profile_image' => "http://".$_SERVER['HTTP_HOST'].'/hrms_new/'.$q->profileimage,
	                'designation_id' => $q->designation_id,
                    'user_type_name' =>  get_val('usertypename','id',$q->user_type_id_fk,'hrms_user_type'),
                    'branch_id' => $q->branch_id,
                    'branch_name' => get_val('name','id',$q->branch_id,'hrms_branch'),
                    'user_type_id_fk' => $q->user_type_id_fk,
                    'designation_name' => get_val('name','id',$q->designation_id,'hrms_designation'),
	                'name' => $q->name, 
	                "username" => $q->biometricAccess,
	                'token' => $token,
	            )
                );
                }
              }
            } else {
               return array('status' => 400,'message' => 'Wrong password.');
            }
        }
    }

    public function logout(){
        $users_id  = $this->ci->input->get_request_header('User-ID', TRUE);
        $token     = $this->ci->input->get_request_header('Authorization-key', TRUE);
        $this->ci->db->where('user_id',$users_id)->where('token',$token)->delete('hrms_user_auth');
        return array('status' => 200,'message' => 'Successfully logout.');
    }
}