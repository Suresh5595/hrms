<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('form', 'url'));
        $this->load->model('Api_model');

    }

    public function index()
    {
        // $consumer = $this->Request->request('username');
        //$users = $this->db->get_where('hrms_users', array())->result_array();
        //$content = '<table class="table table-bordered"><tbody>';
        //if($users){
            //foreach($users as $user){
                //$content .= "<tr><td><b>Emp Code:</b>".$user['username']."</td><td><b>OTP:</b>".$user['otp']."</td></tr>";
            //}
        //}
        //$content .= "</tbody></table>";
        // echo "<pre>";
        // print_r($users);
        // die;
        echo "<h1>Welcome HRMS</h1><br>";
       //echo $content;
    }

    
    public function api_login()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        if (!empty($json_input)) {
            $data = json_decode($json_input, true);

            if (!empty($data['password']) && !empty($data['username'])) {
                $username = $data['username'];
                $password =  $data['password'];
                $result = $this->Api_model->validate($username, $password);
// echo "<pre>";print_r($result);die;
                if ($result) {
                    $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
                    $length_of_string = 10;
                    $token = substr(str_shuffle($str_result),0, $length_of_string); // Random Token Generate
                    $get_user_data = $this->Api_model->get_user_details($result[0]['id']);
//                     echo "<pre>";
// print_r($get_user_data);
// die;
                    if($get_user_data){
                        if(strlen($token) < 10){
                            $len = 10 - strlen($token);
                            $token = $token.''.substr(str_shuffle($str_result),0, $len);
                        }
                        $device_token = $data['device_token'];
                        $device_Type = $data['device_type'];
                        $update_user = [];
                        $update_user['access_token'] = $token;
                        $update_user['session_start_date'] = date('Y-m-d H:i:s');
                        $update_user['session_end_date'] = date('Y-m-d H:i:s',strtotime("+7 day"));
    
                        $update_user['device_token'] = $device_token;
                        $update_user['device_type'] = $device_Type;
                        $this->db->where('id', $get_user_data->user_id);
                        $this->db->update('hrms_users', $update_user);
                        
                        $expired_at = date("Y-m-d H:i:s", strtotime('+12 hours'));
                        $this->db->insert('hrms_user_auth',array('user_id' => 2500,'token' => $token,'expired_at' => $expired_at));
                        
                        // echo $this->db->last_query();
                        $log=array();
                        $log['user_id'] = $get_user_data->user_id;
                        $get_user_data = $this->Api_model->get_user_details_by_username($get_user_data->user_id);
                        $output = array('status' => 'success', 'message' => 'User login successfull!', 'data' => $get_user_data);
                        echo json_encode($output);
                    }else{
                        $output = array('status' => 'error', 'message' => 'Incorrect Username or password!');
                        echo json_encode($output);
                    }
                    
                    
                    // $log['access_token'] = $token;
                    // $log['device_type'] = $data['device_type'];
                    // $log['log'] = 'In';
                    // $log['ip'] = $this->input->ip_address();
                    // $log['browser'] = $this->agent->browser();
                    // $this->db->insert('vms_log_history',$log);
                   
                    
                    
                } else {
                    $output = array('status' => 'error', 'message' => 'Incorrect Username or password!');
                    echo json_encode($output);
                }
            }else{
                $output = array('status' => 'error', 'message' => 'Enter Username and password');
                echo json_encode($output);
            }
            exit;
        }
    }

    public function get_user_menu(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        	    if (isset($data['usertype_id']) && !empty($data['usertype_id'])) {
                    $module_data = [];
                    $this->db->select('hrms_user_permission.*,hrms_modules.user_module_name,hrms_module_section.user_section_name');
                    $this->db->from('hrms_user_permission');
                    $this->db->join('hrms_modules','hrms_modules.id = hrms_user_permission.module_id','left');
                    $this->db->join('hrms_module_section','hrms_module_section.id = hrms_user_permission.section_id','left');
                    $this->db->where('hrms_user_permission.user_type_id',$data['usertype_id']);
                    $this->db->where('hrms_user_permission.acc_main',1);
                    $module = $this->db->get();
                    // echo $this->db->last_query();
                    if ($module->num_rows() > 0) {
                        $module_data = $module->result_array();
                        $module_id=  array_column($module_data,'module_id');
                        $section_id=  array_filter(array_column($module_data,'section_id'));
                        // echo "<pre>";
                        // print_r($module_id);
                        // print_r($section_id);
                        // die;
                        $this->db->select('hrms_modules.id,hrms_modules.user_module_name,hrms_modules.user_module_key as title,hrms_modules.icon,hrms_modules.path,hrms_modules.class, hrms_user_permission.acc_main, hrms_user_permission.acc_all, hrms_user_permission.acc_view, hrms_user_permission.acc_add, hrms_user_permission.acc_edit, hrms_user_permission.acc_delete,hrms_user_permission.module_id,hrms_user_permission.section_id,hrms_user_permission.user_type_id,hrms_modules.class,hrms_modules.icon,hrms_modules.path');
                        $this->db->from('hrms_modules');
                        $this->db->join('hrms_user_permission','hrms_user_permission.module_id = hrms_modules.id');
                        $this->db->where_in('hrms_modules.id',array_unique($module_id));
                        $this->db->group_by('hrms_modules.id');
                        $module_data = $this->db->get()->result_array();
                        // echo $this->db->last_query();
                        //  echo "<pre>";
                        // print_r($module_data);
                        // print_r($section_id);
                        // die;
                        if($module_data){
                            foreach($module_data as $key => $module){
                                $this->db->select('hrms_module_section.id,hrms_module_section.user_section_name,hrms_module_section.path,hrms_module_section.icon,hrms_module_section.class,hrms_module_section.user_section_key as title, hrms_user_permission.acc_main, hrms_user_permission.acc_all, hrms_user_permission.acc_view, hrms_user_permission.acc_add, hrms_user_permission.acc_edit, hrms_user_permission.acc_delete,hrms_user_permission.module_id,hrms_user_permission.section_id,hrms_user_permission.user_type_id');
                                $this->db->from('hrms_module_section');
                                $this->db->join('hrms_user_permission','hrms_user_permission.section_id = hrms_module_section.id');
                                $this->db->where('hrms_module_section.module_id',$module['id']);
                                $this->db->where_in('hrms_module_section.id',$section_id);
                                $module_data[$key]['sub_menu'] = $this->db->get()->result_array();
                            }
                        }
        
                    }
                    if($module_data){
                        $output = array('status' => 'success', 'message' => 'Menu list',"data" => $module_data);
                        echo json_encode($output);
                    }else{
                        $output = array('status' => 'error', 'message' => 'Menu list',"data" => $module_data);
                        echo json_encode($output);
                    }
                }else{
                    $output = array('status' => 'error', 'message' => 'Enter User type ID');
                    echo json_encode($output);
                }
        	}else{
        	    echo json_encode($response);
        	}
    	}
        
    }

    public function get_menu_list(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
            $module_data = [];
            if (isset($data['usertype_id']) && !empty($data['usertype_id'])) {
            $user_type_id=$data['usertype_id'];
            $this->db->select('hrms_modules.id,hrms_modules.user_module_name,hrms_modules.user_module_key as title,hrms_modules.icon,hrms_modules.path,hrms_modules.class, hrms_user_permission.acc_main, hrms_user_permission.acc_all, hrms_user_permission.acc_view, hrms_user_permission.acc_add, hrms_user_permission.acc_edit, hrms_user_permission.acc_delete,hrms_modules.id as module_id,hrms_user_permission.section_id,hrms_modules.class,hrms_modules.icon,hrms_modules.path');
            $this->db->from('hrms_modules');
            $this->db->join('hrms_user_permission','hrms_user_permission.module_id = hrms_modules.id','left');
            // $this->db->where('hrms_user_permission.user_type_id',$data['user_type_id']);
            $this->db->group_by('hrms_modules.id');
            $module_data = $this->db->get()->result_array();
            // echo $this->db->last_query();
            //  echo "<pre>";
            // print_r($module_data);
            // print_r($section_id);
            // die;
            if($module_data){
                foreach($module_data as $key => $module){
                    $this->db->select('hrms_module_section.id,hrms_module_section.user_section_name,hrms_module_section.path,hrms_module_section.icon,hrms_module_section.class,hrms_module_section.user_section_key as title, hrms_user_permission.acc_main, hrms_user_permission.acc_all, hrms_user_permission.acc_view, hrms_user_permission.acc_add, hrms_user_permission.acc_edit, hrms_user_permission.acc_delete,hrms_module_section.module_id,hrms_module_section.id as section_id');
                    $this->db->from('hrms_module_section');
                    $this->db->join('hrms_user_permission','hrms_user_permission.section_id = hrms_module_section.id','left');
                    $this->db->where('hrms_module_section.module_id',$module['id']);
                    // $this->db->where_in('hrms_module_section.id',$section_id);
                    $this->db->group_by('hrms_module_section.id');
                    $module_data[$key]['sub_menu'] = $this->db->get()->result_array();
                }
            }
            
            if($module_data){
                foreach($module_data as $key=>$val){
                    $update_data = [];
                    $this->db->select('hrms_modules.id,hrms_modules.user_module_name,hrms_modules.user_module_key as title,hrms_modules.icon,hrms_modules.path,hrms_modules.class, hrms_user_permission.acc_main, hrms_user_permission.acc_all, hrms_user_permission.acc_view, hrms_user_permission.acc_add, hrms_user_permission.acc_edit, hrms_user_permission.acc_delete,hrms_user_permission.module_id,hrms_user_permission.section_id,hrms_modules.class,hrms_modules.icon,hrms_modules.path');
                    $this->db->from('hrms_modules');
                    $this->db->join('hrms_user_permission','hrms_user_permission.module_id = hrms_modules.id','left');
                    $this->db->where('hrms_user_permission.module_id',$val['id']);
                    $this->db->where('hrms_user_permission.section_id IS NULL');
                    $this->db->where('hrms_user_permission.user_type_id',$user_type_id);
                    $this->db->group_by('hrms_modules.id');
                    $module_per = $this->db->get()->result_array();
                    if($module_per){
                        $module_data[$key]['acc_main'] = $module_per[0]['acc_main'];
                        $module_data[$key]['acc_all'] = $module_per[0]['acc_all'];
                        $module_data[$key]['acc_view'] = $module_per[0]['acc_view'];
                        $module_data[$key]['acc_add'] = $module_per[0]['acc_add'];
                        $module_data[$key]['acc_edit'] = $module_per[0]['acc_edit'];
                        $module_data[$key]['acc_delete'] = $module_per[0]['acc_delete'];
                    }else{
                        $module_data[$key]['acc_main'] = 0;
                        $module_data[$key]['acc_all'] =0;
                        $module_data[$key]['acc_view'] = 0;
                        $module_data[$key]['acc_add'] = 0;
                        $module_data[$key]['acc_edit'] = 0;
                        $module_data[$key]['acc_delete'] = 0;
                    }
                    
                    if($val['sub_menu']){
                        foreach($val['sub_menu'] as $keys => $value){
                            $this->db->select('hrms_modules.id,hrms_modules.user_module_name,hrms_modules.user_module_key as title,hrms_modules.icon,hrms_modules.path,hrms_modules.class, hrms_user_permission.acc_main, hrms_user_permission.acc_all, hrms_user_permission.acc_view, hrms_user_permission.acc_add, hrms_user_permission.acc_edit, hrms_user_permission.acc_delete,hrms_user_permission.module_id,hrms_user_permission.section_id,hrms_modules.class,hrms_modules.icon,hrms_modules.path');
                            $this->db->from('hrms_modules');
                            $this->db->join('hrms_user_permission','hrms_user_permission.module_id = hrms_modules.id','left');
                            $this->db->where('hrms_user_permission.module_id',$val['id']);
                            $this->db->where('hrms_user_permission.section_id',$value['id']);
                            $this->db->where('hrms_user_permission.user_type_id',$user_type_id);
                            $this->db->group_by('hrms_modules.id');
                            $module_sec = $this->db->get()->result_array();
                            if($module_sec){
                                $module_data[$key]['sub_menu'][$keys]['acc_main'] = $module_sec[0]['acc_main'];
                                $module_data[$key]['sub_menu'][$keys]['acc_all'] = $module_sec[0]['acc_all'];
                                $module_data[$key]['sub_menu'][$keys]['acc_view'] = $module_sec[0]['acc_view'];
                                $module_data[$key]['sub_menu'][$keys]['acc_add'] = $module_sec[0]['acc_add'];
                                $module_data[$key]['sub_menu'][$keys]['acc_edit'] = $module_sec[0]['acc_edit'];
                                $module_data[$key]['sub_menu'][$keys]['acc_delete'] = $module_sec[0]['acc_delete'];
                            }else{
                                $module_data[$key]['sub_menu'][$keys]['acc_main'] = 0;
                                $module_data[$key]['sub_menu'][$keys]['acc_all'] =0;
                                $module_data[$key]['sub_menu'][$keys]['acc_view'] = 0;
                                $module_data[$key]['sub_menu'][$keys]['acc_add'] = 0;
                                $module_data[$key]['sub_menu'][$keys]['acc_edit'] = 0;
                                $module_data[$key]['sub_menu'][$keys]['acc_delete'] = 0;
                            }
                        }
                    }
                }
                
                $output = array('status' => 'success', 'message' => 'Menu list',"data" => $module_data);
                echo json_encode($output);
            }else{
                $output = array('status' => 'error', 'message' => 'Menu list',"data" => $module_data);
                echo json_encode($output);
            }
        }else{
            $output = array('status' => 'error', 'message' => 'Enter User type ID');
            echo json_encode($output);
        }
        	}else{
        	    echo json_encode($response);
        	}
    	}
    }

    public function update_user_access(){
        $json_input = file_get_contents('php://input'); // JSON Input
        if (!empty($json_input)) {
            $data = json_decode($json_input, true);
            $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
            if (isset($data) && !empty($data)) {
                if($data['data']){
                    $user_type_id = $data['user_type_id'];
                    foreach($data['data'] as $key=>$val){
                        $update_data = [];
                        $update_data['acc_main'] = $val['acc_main'];
                        $update_data['acc_all'] = $val['acc_all'];
                        $update_data['acc_view'] = $val['acc_view'];
                        $update_data['acc_add'] = $val['acc_add'];
                        $update_data['acc_edit'] = $val['acc_edit'];
                        $update_data['acc_delete'] = $val['acc_delete'];
                        $update_data['updated_on'] = date('Y-m-d H:i:s');
                        $module_per = $this->db->get_where('hrms_user_permission', array('module_id'=>$val['module_id'],"section_id"=>$val['section_id'],"user_type_id"=>$user_type_id))->result_array();
                        if($module_per){
                            $this->db->where('module_id',$val['module_id']);
                            $this->db->where('section_id',$val['section_id']);
                            $this->db->where('user_type_id',$user_type_id);
                            $this->db->update('hrms_user_permission',$update_data);
                        }else{
                            unset($update_data['updated_on']);
                            $update_data['user_type_id'] = $user_type_id;
                            $update_data['module_id'] = $val['module_id'];
                            $update_data['section_id'] = $val['section_id'];
                            $update_data['status'] = 1;
                            $this->db->insert('hrms_user_permission',$update_data);
                        }
                        
                        if($val['sub_menu']){
                            foreach($val['sub_menu'] as $value){
                                $update_data = [];
                                $update_data['acc_main'] = $value['acc_main'];
                                $update_data['acc_all'] = $value['acc_all'];
                                $update_data['acc_view'] = $value['acc_view'];
                                $update_data['acc_add'] = $value['acc_add'];
                                $update_data['acc_edit'] = $value['acc_edit'];
                                $update_data['acc_delete'] = $value['acc_delete'];
                                $update_data['updated_on'] = date('Y-m-d H:i:s');
                                $module_per = $this->db->get_where('hrms_user_permission', array('module_id'=>$val['module_id'],"section_id"=>$value['section_id'],"user_type_id"=>$user_type_id))->result_array();
                                if($module_per){
                                    $this->db->where('module_id',$val['module_id']);
                                    $this->db->where('section_id',$value['section_id']);
                                    $this->db->where('user_type_id',$user_type_id);
                                    $this->db->update('hrms_user_permission',$update_data);
                                }else{
                                    unset($update_data['updated_on']);
                                    $update_data['user_type_id'] = $user_type_id;
                                    $update_data['module_id'] = $val['module_id'];
                                    $update_data['section_id'] = $value['section_id'];
                                    $update_data['status'] = 1;
                                    $this->db->insert('hrms_user_permission',$update_data);
                                }
                            }
                        }
                    }
                    $output = array('status' => 'success', 'message' => 'User Menu access updated successfull!');
                    echo json_encode($output);
                }
            }else{
                $output = array('status' => 'error', 'message' => 'Enter User ID');
                echo json_encode($output);
            }
        }else{
        	    echo json_encode($response);
        	}
    	}
        }
    }
    
    public function api_logout(){
        $json_input = file_get_contents('php://input'); // JSON Input
        if (!empty($json_input)) {
            $data = json_decode($json_input, true);
            if (isset($data['user_id']) && !empty($data['user_id'])) {
                $data = json_decode($json_input, true);
                $device_token = $data['device_token'];
                $device_Type = $data['device_type'];
                $update_user = [];
                $update_user['session_end_date'] = date('Y-m-d H:i:s');

                $update_user['device_token'] = $device_token;
                $update_user['device_type'] = $device_Type;
                $this->db->where('id', $data['user_id']);
                $this->db->update('hrms_users', $update_user);
                $log=array();
                $log['user_id'] = $data['user_id'];
                $log['device_type'] = $data['device_type'];
                $log['log'] = 'Out';
                $log['ip'] = $this->input->ip_address();
                $log['browser'] = $this->agent->browser();
                $this->db->insert('hrms_log_history',$log);
                $token     = $this->input->get_request_header('authorizationkey', TRUE);
                $this->db->where('user_id',$data['user_id'])->where('token',$token)->delete('hrms_user_auth');
                $output = array('status' => 'success', 'message' => 'User Logout successfull!');
                echo json_encode($output);

            }else{
                $output = array('status' => 'error', 'message' => 'Enter User ID');
                echo json_encode($output);
            }
        }

    }

    public function check_valid_token_or_not($token,$user_id) {
        $this->db->select('user.*');
        $this->db->from('hrms_users as user');
        // $this->db->join('ireporting_field_force as field_force', 'field_force.User_inserted_id = user.id', 'left');
        $this->db->where('user.id', $user_id);
        $this->db->where('user.access_token',$token);
        $this->db->where('user.status', 1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return NULL;
       
    }

    public function session_check(){
        $json_input = file_get_contents('php://input'); // JSON Input
        if (!empty($json_input)) {
            $data = json_decode($json_input, true);
            if (!empty($data['username']) && !empty($data['access_token'])) {
                $user_name = $data['username'];
                $access_token = $data['access_token'];
                $user_data = $this->Api_model->get_user_details_by_token($data);
                if ($user_data) {
                    $output = array('status_code'=>200,'status' => 'success', 'message' => 'Token Verified');
                    echo json_encode($output);
                } else {
                    $output = array('status_code'=>400,'status' => 'error', 'message' => 'Session Expired');
                    echo json_encode($output);
                }
            }else{
                $output = array('status_code'=>400,'status' => 'error', 'message' => 'Enter UserID and Access token');
                echo json_encode($output);
            }
        }
    }

    public function company()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $result = $this->Api_model->get_company();
        if ($result) {
            $output = array('status' => 'success', 'message' => 'Company List', 'data' => $result);
            echo json_encode($output);
        } else {
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function user_type()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $result = $this->Api_model->get_user_type();
        if ($result) {
            $output = array('status' => 'success', 'message' => 'User type List', 'data' => $result);
            echo json_encode($output);
        } else {
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function users()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $result = $this->Api_model->get_users();
        if ($result) {
            $output = array('status' => 'success', 'message' => 'Users List', 'data' => $result);
            echo json_encode($output);
        } else {
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function branch()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $result = $this->Api_model->get_branch();
        if ($result) {
            $output = array('status' => 'success', 'message' => 'Branch List', 'data' => $result);
            echo json_encode($output);
        } else {
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function department()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $result = $this->Api_model->get_department();
        if ($result) {
            $output = array('status' => 'success', 'message' => 'Department List', 'data' => $result);
            echo json_encode($output);
        } else {
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function designation()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $result = $this->Api_model->get_designation();
        if ($result) {
            $output = array('status' => 'success', 'message' => 'Designation List', 'data' => $result);
            echo json_encode($output);
        } else {
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function holiday()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $result = $this->Api_model->get_holiday();
        if ($result) {
            $output = array('status' => 'success', 'message' => 'Holiday List', 'data' => $result);
            echo json_encode($output);
        } else {
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function document()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $result = $this->Api_model->get_document();
        if ($result) {
            $output = array('status' => 'success', 'message' => 'Document List', 'data' => $result);
            echo json_encode($output);
        } else {
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function kyc()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $result = $this->Api_model->get_kyc();
        if ($result) {
            $output = array('status' => 'success', 'message' => 'KYC List', 'data' => $result);
            echo json_encode($output);
        } else {
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function job_opening()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $result = $this->Api_model->get_job_opening(array());
        if ($result) {
            $output = array('status' => 'success', 'message' => 'job opening List', 'data' => $result);
            echo json_encode($output);
        } else {
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function staffmaster()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $result = $this->Api_model->get_staffmaster();
        if ($result) {
            $output = array('status' => 'success', 'message' => 'Staff List', 'data' => $result);
            echo json_encode($output);
        } else {
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function onboard()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $result = $this->Api_model->get_onboard();
        if ($result) {
            $output = array('status' => 'success', 'message' => 'Staff List', 'data' => $result);
            echo json_encode($output);
        } else {
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function application()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $result = $this->Api_model->get_application($data);
        if ($result) {
            $output = array('status' => 'success', 'message' => 'Application List', 'data' => $result);
            echo json_encode($output);
        } else {
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function application_list()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $data['null'] = 1;
        $result = $this->Api_model->get_application($data);
        if ($result) {
            $output = array('status' => 'success', 'message' => 'Application List', 'data' => $result);
            echo json_encode($output);
        } else {
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function get_application_grid()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $result = $this->Api_model->get_application_grid($data);
        if ($result) {
            $output = array('status' => 'success', 'message' => 'Application List', 'data' => $result);
            echo json_encode($output);
        } else {
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    

    public function job_ques()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
         $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $result = $this->Api_model->get_job_ques();
        if ($result) {
            $output = array('status' => 'success', 'message' => 'Job Ques List', 'data' => $result);
            echo json_encode($output);
        } else {
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function schedule()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
         $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $result = $this->Api_model->get_schedule();
        if ($result) {
            $output = array('status' => 'success', 'message' => 'Schedule List', 'data' => $result);
            echo json_encode($output);
        } else {
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function online_test()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $result = $this->Api_model->get_online_test();
        if ($result) {
            $output = array('status' => 'success', 'message' => 'Online test List', 'data' => $result);
            echo json_encode($output);
        } else {
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function call_history()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $result = $this->Api_model->get_call_history();
        if ($result) {
            $output = array('status' => 'success', 'message' => 'Call History List', 'data' => $result);
            echo json_encode($output);
        } else {
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function get_company()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if ($data['id']) {
            $result = $this->Api_model->get_company($data['id']);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Company List', 'data' => $result);
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'No data found');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Company Id');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function get_active_company()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $result = $this->Api_model->get_active_company();
        if ($result) {
            $output = array('status' => 'success', 'message' => 'Company Active List', 'data' => $result);
            echo json_encode($output);
        } else {
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function get_user_type()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if ($data['id']) {
            $result = $this->Api_model->get_user_type($data['id']);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'User type List', 'data' => $result);
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'No data found');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter User type Id');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }
    public function get_users()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if ($data['id']) {
            $result = $this->Api_model->get_users($data['id']);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Users List', 'data' => $result);
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'No data found');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter users Id');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }
    public function get_branch()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if ($data['id']) {
            $result = $this->Api_model->get_branch($data['id']);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Branch List', 'data' => $result);
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'No data found');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Branch Id');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }
    public function get_branch_by_company()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if ($data['company_id']) {
            $result = $this->Api_model->get_branch_by_company($data['company_id']);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Branch List', 'data' => $result);
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'No data found');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Company Id');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
        
    }
    public function get_department()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if ($data['id']) {
            $result = $this->Api_model->get_department($data['id']);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Department List', 'data' => $result);
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'No data found');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Department Id');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }
    public function get_designation()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
         $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if ($data['id']) {
            $result = $this->Api_model->get_designation($data['id']);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Designation List', 'data' => $result);
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'No data found');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Designation Id');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }
    public function get_holiday()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if ($data['id']) {
            $result = $this->Api_model->get_holiday($data['id']);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Holiday List', 'data' => $result);
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'No data found');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Holiday Id');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }
    public function get_document()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if ($data['id']) {
            $result = $this->Api_model->get_document($data['id']);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Document List', 'data' => $result);
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'No data found');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Document Id');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }
    public function get_kyc()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if ($data['id']) {
            $result = $this->Api_model->get_kyc($data['id']);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'KYC List', 'data' => $result);
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'No data found');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Document Id');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function get_staffmaster()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($data['enc_id']) && $data['enc_id'] !=''){
            $id = $data['enc_id'];
            $data['id'] = $this->encrypt->decode($id);
        }        
        if ($data['id']) {
            $result = $this->Api_model->get_staffmaster_id($data['id']);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Staff List', 'data' => $result);
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'No data found');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Staff Id');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }
    public function get_onboard()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
         $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if ($data['id']) {
            $result = $this->Api_model->get_onboard($data['id']);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Staff List', 'data' => $result);
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'No data found');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Staff Id');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function get_application()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
         $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if ($data) {
            $result = $this->Api_model->get_application($data);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Application List', 'data' => $result);
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'No data found');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Application Id');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function get_job_opening()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if ($data) {
            $result = $this->Api_model->get_job_opening($data);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Staff List', 'data' => $result);
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'No data found');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Staff Id');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function get_job_ques()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if ($data['id']) {
            $result = $this->Api_model->get_job_ques($data['id']);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Job Ques List', 'data' => $result);
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'No data found');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Job ques Id');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function get_schedule()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if ($data['id']) {
            $result = $this->Api_model->get_schedule($data['id']);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Schedule List', 'data' => $result);
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'No data found');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Schedule Id');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function get_online_test()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if ($data['id']) {
            $result = $this->Api_model->get_online_test($data['id']);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Online test List', 'data' => $result);
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'No data found');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Online Test Id');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function get_call_history()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if ($data['id']) {
            $result = $this->Api_model->get_call_history($data['id']);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Call History List', 'data' => $result);
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'No data found');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Online Test Id');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function add_company()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $company = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($company['name']) && isset($company['code']) && isset($company['status']) && $company['name'] != '' && $company['code'] != '' && $company['status'] != '') {
           
                if ($company['code']) {
                    $check_duplicate = $this->Api_model->check_duplicate('hrms_company', 'code', $company['code']);
                    //    echo $this->db->last_query();die;
                    if ($check_duplicate) {
                        $output = array('status' => 'error', 'message' => 'Code Already Exists');
                        echo json_encode($output);
                        exit;
                    }
                }

                if ($company['name']) {
                    $check_duplicate = $this->Api_model->check_duplicate('hrms_company', 'name', $company['name']);
                    //    echo $this->db->last_query();die;
                    if ($check_duplicate) {
                        $output = array('status' => 'error', 'message' => 'Name Already Exists');
                        echo json_encode($output);
                        exit;
                    }
                }
                $result = $this->Api_model->insert_company($company);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Company Added Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Company not Added');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function update_company()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $company = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($company['id']) && $company['id'] != '') {
           
                if ($company['code']) {
                    $check_duplicate = $this->Api_model->check_duplicate('hrms_company', 'code', $company['code'], $company['id']);
                    if ($check_duplicate) {
                        $output = array('status' => 'error', 'message' => 'Code Already Exists');
                        echo json_encode($output);
                        exit;
                    }
                }

                if ($company['name']) {
                    $check_duplicate = $this->Api_model->check_duplicate('hrms_company', 'name', $company['name'], $company['id']);
                    if ($check_duplicate) {
                        $output = array('status' => 'error', 'message' => 'Name Already Exists');
                        echo json_encode($output);
                        exit;
                    }
                }

                $result = $this->Api_model->update_company($company, $company['id']);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Company Updated Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Company not Updated');
                    echo json_encode($output);
                }
           
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function delete_company()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $company = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($company['id']) && $company['id'] != '') {
           
                $company['status'] = 3;
                $result = $this->Api_model->update_company($company, $company['id']);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Company Deleted Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Company not Deleted');
                    echo json_encode($output);
                }
           
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function add_user_type()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $user_type = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($user_type['usertypename']) && isset($user_type['mode']) && isset($user_type['description']) && $user_type['usertypename'] != '' && $user_type['mode'] != '' && $user_type['description'] != '' && isset($user_type['status']) && $user_type['status'] != '') {
           
                $result = $this->Api_model->insert_user_type($user_type);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'User type Added Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'User type not Added');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function update_user_type()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $user_type = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($user_type['id']) && $user_type['id'] != '' && isset($user_type['usertypename']) && isset($user_type['mode']) && isset($user_type['description']) && $user_type['usertypename'] != '' && $user_type['mode'] != '' && $user_type['description'] != '' && isset($user_type['status']) && $user_type['status'] != '') {
           
               
                $result = $this->Api_model->update_user_type($user_type, $user_type['id']);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'User type Updated Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'User type not Updated');
                    echo json_encode($output);
                }
           
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }
    
    public function delete_user_type()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $user_type = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($user_type['id']) && $user_type['id'] != '') {
           
                $user_type['status'] = 3;
                $result = $this->Api_model->update_user_type($user_type, $user_type['id']);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'User type Deleted Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'User type not Deleted');
                    echo json_encode($output);
                }
           
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }
    
    public function add_users()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $users = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($users['usertype_id']) && isset($users['branch_id']) && $users['usertype_id'] != '' && $users['branch_id'] != '' && isset($users['status']) && $users['status'] != '') {
                if ($users['emp_id']) {
                    $check_duplicate = $this->Api_model->check_duplicate('hrms_users', 'emp_id', $users['emp_id']);
                    //    echo $this->db->last_query();die;
                    if ($check_duplicate) {
                        $output = array('status' => 'error', 'message' => 'User Already Exists');
                        echo json_encode($output);
                        exit;
                    }
                }
                $result = $this->Api_model->insert_users($users);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Users Added Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Users not Added');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function update_users()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $users = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($users['id']) && $users['id'] != '' && isset($users['usertype_id']) && isset($users['branch_id']) && $users['usertype_id'] != '' && $users['branch_id'] != '' && isset($users['status']) && $users['status'] != '') {
                if ($users['code']) {
                    $check_duplicate = $this->Api_model->check_duplicate('hrms_users', 'emp_id', $users['emp_id'], $users['id']);
                    //    echo $this->db->last_query();die;
                    if ($check_duplicate) {
                        $output = array('status' => 'error', 'message' => 'User Already Exists');
                        echo json_encode($output);
                        exit;
                    }
                }
               
                $result = $this->Api_model->update_users($users, $users['id']);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Users Updated Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Users not Updated');
                    echo json_encode($output);
                }
           
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }
    
    public function delete_users()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $users = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($users['id']) && $users['id'] != '') {
           
            $users['status'] = 3;
                $result = $this->Api_model->update_users($users, $users['id']);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Users Deleted Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Users not Deleted');
                    echo json_encode($output);
                }
           
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function add_branch()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $Branch = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($Branch['name']) && isset($Branch['code']) && isset($Branch['company_id']) && isset($Branch['status']) && $Branch['name'] != '' && $Branch['code'] != '' && $Branch['company_id'] != '' && $Branch['status'] != '') {
                if ($Branch['code']) {
                    $check_duplicate = $this->Api_model->check_duplicate('hrms_branch', 'code', $Branch['code']);
                    //    echo $this->db->last_query();die;
                    if ($check_duplicate) {
                        $output = array('status' => 'error', 'message' => 'Code Already Exists');
                        echo json_encode($output);
                        exit;
                    }
                }
                if ($Branch['name']) {
                    $check_duplicate = $this->Api_model->check_duplicate('hrms_branch', 'name', $Branch['name']);
                    //    echo $this->db->last_query();die;
                    if ($check_duplicate) {
                        $output = array('status' => 'error', 'message' => 'Name Already Exists');
                        echo json_encode($output);
                        exit;
                    }
                }
                $result = $this->Api_model->insert_branch($Branch);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Branch Added Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Branch not Added');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function update_branch()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $Branch = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($Branch['id']) && $Branch['id'] != '' && isset($Branch['name']) && isset($Branch['code']) && isset($Branch['company_id']) && isset($Branch['status']) && $Branch['name'] != '' && $Branch['code'] != '' && $Branch['company_id'] != '' && $Branch['status'] != '') {
            $id = $Branch['id'];
            if ($Branch['code']) {
                $check_duplicate = $this->Api_model->check_duplicate('hrms_branch', 'code', $Branch['code'], $Branch['id']);
                //    echo $this->db->last_query();die;
                if ($check_duplicate) {
                    $output = array('status' => 'error', 'message' => 'Code Already Exists');
                    echo json_encode($output);
                    exit;
                }
            }

            if ($Branch['name']) {
                $check_duplicate = $this->Api_model->check_duplicate('hrms_branch', 'name', $Branch['name'], $Branch['id']);
                //    echo $this->db->last_query();die;
                if ($check_duplicate) {
                    $output = array('status' => 'error', 'message' => 'Name Already Exists');
                    echo json_encode($output);
                    exit;
                }
            }

            $result = $this->Api_model->update_branch($Branch, $id);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Branch Updated Succesfully');
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'Branch not Updated');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }
    
    public function delete_branch()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $Branch = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($Branch['id']) && $Branch['id'] != '') {
            $id = $Branch['id'];
            $Branch['status'] = 3;
            // $Branch['updatedon'] = date('Y-m-d H:i:s');
            $result = $this->Api_model->update_branch($Branch, $id);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Branch Deleted Succesfully');
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'Branch not Updated');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function add_department()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $department = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($department['name']) && isset($department['company_id']) && isset($department['status']) && $department['name'] != '' && $department['company_id'] != '' && $department['status'] != '') {
            
                $result = $this->Api_model->insert_department($department);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Department Added Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Department not Added');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function update_department()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $department = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($department['id']) && $department['id'] != '' && isset($department['name']) && isset($department['company_id']) && isset($department['status']) && $department['name'] != '' && $department['company_id'] != '' && $department['status'] != '') {
            $id = $department['id'];
            // $department['updatedon'] = date('Y-m-d H:i:s');
            $result = $this->Api_model->update_department($department, $id);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Department Updated Succesfully');
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'Department not Updated');
                echo json_encode($output);
            }
            
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function delete_department()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $department = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($department['id']) && $department['id'] != '') {
            $id = $department['id'];
            $department['status'] = 3;
            // $department['updatedon'] = date('Y-m-d H:i:s');
            $result = $this->Api_model->update_department($department, $id);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Department Deleted Succesfully');
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'Department not Deleted');
                echo json_encode($output);
            }
            
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function add_designation()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $designation = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($designation['name']) && isset($designation['departmentid']) && isset($designation['status']) && $designation['name'] != '' && $designation['departmentid'] != '' && $designation['status'] != '') {
            
                $result = $this->Api_model->insert_designation($designation);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Designation Added Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Designation not Added');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function update_designation()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $designation = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($designation['id']) && $designation['id'] != '' && isset($designation['name']) && isset($designation['departmentid']) && isset($designation['status']) && $designation['name'] != '' && $designation['departmentid'] != '' && $designation['status'] != '') {
            $id = $designation['id'];
            // $designation['updatedon'] = date('Y-m-d H:i:s');
            $result = $this->Api_model->update_designation($designation, $id);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Designation Updated Succesfully');
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'Designation not Updated');
                echo json_encode($output);
            }            
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }
    
    public function delete_designation()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $designation = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($designation['id']) && $designation['id'] != '') {
            $id = $designation['id'];
            $designation['status'] = 3;
            // $designation['updatedon'] = date('Y-m-d H:i:s');
            $result = $this->Api_model->update_designation($designation, $id);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Designation Deleted Succesfully');
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'Designation not Deleted');
                echo json_encode($output);
            }            
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function add_holiday()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $holiday = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($holiday['companyid']) && isset($holiday['date']) && isset($holiday['branchid']) && $holiday['companyid'] != '' && $holiday['date'] != '' && $holiday['branchid'] != '') {
            
                $holiday['date'] = date('Y-m-d', strtotime($holiday['date']));
                // $holiday['status'] = 1;
                $result = $this->Api_model->insert_holiday($holiday);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Holiday Added Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Holiday not Added');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function update_holiday()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $holiday = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($holiday['id']) && $holiday['id'] != '' && isset($holiday['companyid']) && isset($holiday['date']) && isset($holiday['branchid']) && $holiday['companyid'] != '' && $holiday['date'] != '' && $holiday['branchid'] != '') {
            $id = $holiday['id'];
            // $holiday['updatedon'] = date('Y-m-d H:i:s');
            $holiday['date'] = date('Y-m-d', strtotime($holiday['date']));
            // $holiday['status'] = $data['status'];
            $result = $this->Api_model->update_holiday($holiday, $id);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Holiday Updated Succesfully');
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'Holiday not Updated');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }
    
    public function delete_holiday()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $holiday = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($holiday['id']) && $holiday['id'] != '') {
            $id = $holiday['id'];
            $holiday['status'] = 3;
            // $holiday['updatedon'] = date('Y-m-d H:i:s');
            $result = $this->Api_model->update_holiday($holiday, $id);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Holiday Deleted Succesfully');
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'Holiday not Deleted');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function add_document()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $document = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($document['docname']) && $document['docname'] != ''){
                $document['status'] = 1;
                $result = $this->Api_model->insert_document($document);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Document Added Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Document not Added');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function update_document()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $document = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($document['id']) && $document['id'] != '' && isset($document['docname']) && $document['docname'] != '') {
                $id = $document['id'];
                $document['updatedon'] = date('Y-m-d H:i:s');
                $result = $this->Api_model->update_document($document, $id);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Document Updated Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Document not Updated');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }
    
    public function delete_document()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $document = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($document['id']) && $document['id'] != '') {
                $id = $document['id'];
                $document['status'] = 3;
                $document['updatedon'] = date('Y-m-d H:i:s');
                $result = $this->Api_model->update_document($document, $id);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Document Deleted Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Document not Deleted');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function add_job_opening()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $job_opening = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($job_opening['company_id']) && $job_opening['company_id'] != '' && isset($job_opening['branch_id']) && $job_opening['branch_id'] != '' && isset($job_opening['jobtitle']) && $job_opening['jobtitle'] != '' && isset($job_opening['jobtype']) && $job_opening['jobtype'] != '' && isset($job_opening['noofopenings']) && $job_opening['noofopenings'] != '' && isset($job_opening['designation_id']) && $job_opening['designation_id'] != '' && isset($job_opening['designation_desc']) && $job_opening['designation_desc'] != '' && isset($job_opening['is_validate']) && $job_opening['is_validate'] != '' && isset($job_opening['vacancy_type']) && $job_opening['vacancy_type'] != '' && isset($job_opening['status']) && $job_opening['status'] != '' && isset($job_opening['department_id']) && $job_opening['department_id'] != ''){
            $job_opening['validity'] = (isset($job_opening['validity'])&&$job_opening['validity']!='')?date('Y-m-d', strtotime($job_opening['validity'])):'';
            
                $result = $this->Api_model->insert_job_opening($job_opening);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'job opening Added Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'job opening not Added');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function update_job_opening()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $job_opening = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($job_opening['id']) && $job_opening['id'] != '' && isset($job_opening['company_id']) && $job_opening['company_id'] != '' && isset($job_opening['branch_id']) && $job_opening['branch_id'] != '' && isset($job_opening['jobtitle']) && $job_opening['jobtitle'] != '' && isset($job_opening['jobtype']) && $job_opening['jobtype'] != '' && isset($job_opening['noofopenings']) && $job_opening['noofopenings'] != '' && isset($job_opening['designation_id']) && $job_opening['designation_id'] != '' && isset($job_opening['designation_desc']) && $job_opening['designation_desc'] != '' && isset($job_opening['is_validate']) && $job_opening['is_validate'] != '' && isset($job_opening['vacancy_type']) && $job_opening['vacancy_type'] != '' && isset($job_opening['status']) && $job_opening['status'] != '' && isset($job_opening['department_id']) && $job_opening['department_id'] != '') {
                $id = $job_opening['id'];
                $job_opening['validity'] = (isset($job_opening['validity'])&&$job_opening['validity']!='')?date('Y-m-d', strtotime($job_opening['validity'])):'';
                $job_opening['updatedon'] = date('Y-m-d H:i:s');
                $result = $this->Api_model->update_job_opening($job_opening, $id);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'job opening Updated Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'job opening not Updated');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function update_job_opening_hr_approve(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $job_opening = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($job_opening['id']) && $job_opening['id'] != '' && isset($job_opening['approvedby_hr']) && $job_opening['approvedby_hr'] != '' && isset($job_opening['approvedstatus_hr']) && $job_opening['approvedstatus_hr'] != '') {
            $id = $job_opening['id'];
            $job_opening['approvedon_hr'] = date('Y-m-d H:i:s');
            $job_opening['updatedon'] = date('Y-m-d H:i:s');
            $result = $this->Api_model->update_job_opening($job_opening, $id);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'job opening Updated Succesfully');
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'job opening not Updated');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function update_job_opening_md_approve(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $job_opening = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($job_opening['id']) && $job_opening['id'] != '' && isset($job_opening['approvedby_md']) && $job_opening['approvedby_md'] != '' && isset($job_opening['approvedstatus_md']) && $job_opening['approvedstatus_md'] != '') {
            $id = $job_opening['id'];
            $job_opening['approvedon_md'] = date('Y-m-d H:i:s');
            $job_opening['updatedon'] = date('Y-m-d H:i:s');
            $result = $this->Api_model->update_job_opening($job_opening, $id);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'job opening Updated Succesfully');
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'job opening not Updated');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function assign_recruiter(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $job_opening = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($job_opening['id']) && $job_opening['id'] != '' && ((isset($job_opening['recruiter1']) && $job_opening['recruiter1'] != '') || (isset($job_opening['recruiter2']) && $job_opening['recruiter2'] != '') || (isset($job_opening['recruiter3']) && $job_opening['recruiter3'] != ''))) {
            $id = $job_opening['id'];
            if(isset($job_opening['recruiter1']) && $job_opening['recruiter1'] != ''){
                $job_opening['recruiter1_on'] = date('Y-m-d H:i:s');
            }
            if(isset($job_opening['recruiter2']) && $job_opening['recruiter2'] != ''){
                $job_opening['recruiter2_on'] = date('Y-m-d H:i:s');
            }
            if(isset($job_opening['recruiter3']) && $job_opening['recruiter3'] != ''){
                $job_opening['recruiter3_on'] = date('Y-m-d H:i:s');
            }
            $job_opening['updatedon'] = date('Y-m-d H:i:s');
            $result = $this->Api_model->update_job_opening($job_opening, $id);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'job opening Updated Succesfully');
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'job opening not Updated');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function publish_job_opening()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $job_opening = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($job_opening['id']) && $job_opening['id'] != '' && isset($job_opening['pub_status']) && $job_opening['pub_status'] != '' && isset($job_opening['pub_by']) && $job_opening['pub_by'] != '') {
                $id = $job_opening['id'];
                $job_opening['pub_date'] = date('Y-m-d');
                $job_opening['updatedon'] = date('Y-m-d H:i:s');
                $result = $this->Api_model->update_job_opening($job_opening, $id);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'job opening Updated Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'job opening not Updated');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }
    
    public function delete_job_opening()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $job_opening = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($job_opening['id']) && $job_opening['id'] != '') {
                $id = $job_opening['id'];
                $job_opening['status'] = 3;
                $job_opening['updatedon'] = date('Y-m-d H:i:s');
                $result = $this->Api_model->update_job_opening($job_opening, $id);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'job opening Delete Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'job opening not Deleted');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function add_job_ques()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $job_ques = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($job_ques['jobapply_id']) && $job_ques['jobapply_id'] != '' && isset($job_ques['status']) && $job_ques['status'] != '' && isset($job_ques['createdby']) && $job_ques['createdby'] != ''){
                $result = $this->Api_model->insert_job_ques($job_ques);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'job Quesions Added Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'job Quesions not Added');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }
    

    public function update_job_ques()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $job_ques = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($job_ques['id']) && $job_ques['id'] != '' && isset($job_ques['jobapply_id']) && $job_ques['jobapply_id'] != '' && isset($job_ques['status']) && $job_ques['status'] != '') {
                $id = $job_ques['id'];
                $job_ques['updateon'] = date('Y-m-d H:i:s');
                $result = $this->Api_model->update_job_ques($job_ques, $id);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'job Quesions Updated Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'job Quesions not Updated');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }
    public function delete_job_ques()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $job_ques = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($job_ques['id']) && $job_ques['id'] != '') {
                $id = $job_ques['id'];
                $job_ques['status'] = 3;
                $job_ques['updateon'] = date('Y-m-d H:i:s');
                $result = $this->Api_model->update_job_ques($job_ques, $id);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'job Quesions Deleted Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'job Quesions not Deleted');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function add_schedule()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if($data['schedule']){
            $schedule_insert = array();
            foreach($data['schedule'] as $key => $schedule){
                if (isset($schedule['jobapply_id']) && $schedule['jobapply_id'] != '' && isset($schedule['status']) && $schedule['status'] != '' && isset($schedule['createdby']) && $schedule['createdby'] != ''){
                    $schedule['schedule_date'] = date('Y-m-d H:i:s',strtotime($schedule['schedule_date']));
                    $schedule_insert[] = $schedule;
                } else {
                    $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
                    echo json_encode($output);
                    exit;
                }
            }
            $result = $this->Api_model->insert_schedule_batch($schedule_insert);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Schedule Added Succesfully');
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'Schedule not Added');
                echo json_encode($output);
            }
        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function update_schedule()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $schedule = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($schedule['id']) && $schedule['id'] != '' && isset($schedule['jobapply_id']) && $schedule['jobapply_id'] != '' && isset($schedule['status']) && $schedule['status'] != '') {
                $id = $schedule['id'];
                $schedule['schedule_date'] = date('Y-m-d H:i:s',strtotime($schedule['schedule_date']));
                $schedule['updatedon'] = date('Y-m-d H:i:s');
                $result = $this->Api_model->update_schedule($schedule, $id);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Schedule Updated Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Schedule not Updated');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        } 
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function delete_schedule()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $schedule = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($schedule['id']) && $schedule['id'] != '') {
                $id = $schedule['id'];
                $schedule['status'] = 3;
                $schedule['updatedon'] = date('Y-m-d H:i:s');
                $result = $this->Api_model->update_schedule($schedule, $id);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Schedule Deleted Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Schedule not Deleted');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        } 
    }else{
        	   echo json_encode($response); 
        	}
        }
    }    

    public function add_call_history()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if($data['call_history']){
            $call_history_insert = array();
            foreach($data['call_history'] as $key => $call_history){
                if (isset($call_history['jobapply_id']) && $call_history['jobapply_id'] != '' && isset($call_history['time']) && $call_history['time'] != '' && isset($call_history['duration']) && $call_history['duration'] != '' && isset($call_history['status']) && $call_history['status'] != '' && isset($call_history['createdby']) && $call_history['createdby'] != ''){
                    $call_history['time'] = date('Y-m-d h:i:s',strtotime($call_history['time']));
                    $call_history_insert[] = $call_history;
                } else {
                    $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
                    echo json_encode($output);
                    exit;
                }
            }
            $result = $this->Api_model->insert_call_history_batch($call_history_insert);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Call History Added Succesfully');
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'Call History not Added');
                echo json_encode($output);
            }
        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
        
    }

    public function update_call_history()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $call_history = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($call_history['id']) && $call_history['id'] != '' && isset($call_history['jobapply_id']) && $call_history['jobapply_id'] != '' && isset($call_history['time']) && $call_history['time'] != '' && isset($call_history['duration']) && $call_history['duration'] != '' && isset($call_history['status']) && $call_history['status'] != '' ) {
                $id = $call_history['id'];
                $call_history['time'] = date('Y-m-d h:i:s',strtotime($call_history['time']));
                $result = $this->Api_model->update_call_history($call_history, $id);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Call history Updated Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Call history not Updated');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        } 
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }
    
    public function delete_call_history()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $call_history = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($call_history['id']) && $call_history['id'] != '') {
                $id = $call_history['id'];
                $call_history['status'] = 3;
                $result = $this->Api_model->update_call_history($call_history, $id);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Call history Deleted Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Call history not Deleted');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        } 
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function add_online_test()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $online_test = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($online_test['jobapply_id']) && $online_test['jobapply_id'] != '' && isset($online_test['status']) && $online_test['status'] != '' && isset($online_test['createdby']) && $online_test['createdby'] != ''){
            if(isset($online_test['linkgenerate_dt']) && !empty($online_test['linkgenerate_dt']))
                $online_test['linkgenerate_dt'] = date('Y-m-d H:i:s',strtotime($online_test['linkgenerate_dt']));
            if(isset($online_test['result_dt']) && !empty($online_test['result_dt']))
                $online_test['result_dt'] = date('Y-m-d H:i:s',strtotime($online_test['result_dt']));
            $result = $this->Api_model->insert_online_test($online_test);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Online Test Added Succesfully');
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'Online Test not Added');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
            exit;
        } 
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function update_online_test()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $online_test = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($online_test['id']) && $online_test['id'] != '' && isset($online_test['jobapply_id']) && $online_test['jobapply_id'] != '' && isset($online_test['status']) && $online_test['status'] != '' && isset($online_test['createdby']) && $online_test['createdby'] != ''){
            if(isset($online_test['linkgenerate_dt']) && !empty($online_test['linkgenerate_dt']))
                $online_test['linkgenerate_dt'] = date('Y-m-d H:i:s',strtotime($online_test['linkgenerate_dt']));
            if(isset($online_test['result_dt']) && !empty($online_test['result_dt']))
                $online_test['result_dt'] = date('Y-m-d H:i:s',strtotime($online_test['result_dt']));
            $id = $online_test['id'];
            $online_test['updatedon'] = date('Y-m-d H:i:s');
            $result = $this->Api_model->update_online_test($online_test,$id);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Online Test Updated Succesfully');
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'Online Test not Updated');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
            exit;
        }   
    }else{
        	   echo json_encode($response); 
        	}
        }
    }
    
    public function delete_online_test()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $online_test = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($online_test['id']) && $online_test['id'] != ''){
            $id = $online_test['id'];
            $online_test['status'] = 3;
            $online_test['updatedon'] = date('Y-m-d H:i:s');
            $result = $this->Api_model->update_online_test($online_test,$id);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Online Test Deleted Succesfully');
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'Online Test not Deleted');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
            exit;
        } 
    }else{
        	   echo json_encode($response); 
        	}
        }
    }
    
    public function add_application()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        
        $application = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($application['first_name']) && $application['first_name'] != '' && isset($application['last_name']) && $application['last_name'] != '' && isset($application['email']) && $application['email'] != '' && isset($application['whatsapp_no']) && $application['whatsapp_no'] != '' && isset($application['dob']) && $application['dob'] != '' && isset($application['gender']) && $application['gender'] != '' && isset($application['marital_status']) && $application['marital_status'] != '' && isset($application['residence_address']) && $application['residence_address'] != '' && isset($application['permanent_address']) && $application['permanent_address'] != '' && isset($application['expected_date_join']) && $application['expected_date_join'] != '' && isset($application['relocate_willing']) && $application['relocate_willing'] != '' && isset($application['language_speak']) && $application['language_speak'] != '' && isset($application['language_write']) && $application['language_write'] != '' && isset($application['job_status']) && $application['job_status'] != '' && isset($application['company_id']) && $application['company_id'] != '' && isset($application['job_opening_id']) && $application['job_opening_id'] != '' && isset($application['resume']) && $application['resume'] != '' && isset($application['source']) && $application['source'] != ''){
                $application['expected_date_join'] = date('Y-m-d',strtotime($application['expected_date_join']));
                $application['dob'] = date('Y-m-d',strtotime($application['dob']));
                $from = new DateTime($application['dob']);
                $to   = new DateTime('today');
                $application['age'] = $from->diff($to)->y;
                $application['job_status'] = "Applied";
                $this->db->select('*');
                $this->db->from('hrms_application');
                $query = $this->db->get();        
                if ($query->num_rows() > 0) {
                    $application_count = $query->result_array();
                }else{
                    $application_count = [];
                }
        
                $prefix_number = 'ZGAPP-'.digits_set(count($application_count) + 1);
                $application['application_no'] = $prefix_number;
                
                $result = $this->Api_model->insert_application($application);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Application Added Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Application not Added');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function update_application()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $application = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($application['id']) && $application['id'] != '' && isset($application['first_name']) && $application['first_name'] != '' && isset($application['last_name']) && $application['last_name'] != '' && isset($application['email']) && $application['email'] != '' && isset($application['whatsapp_no']) && $application['whatsapp_no'] != '' && isset($application['dob']) && $application['dob'] != '' && isset($application['gender']) && $application['gender'] != '' && isset($application['marital_status']) && $application['marital_status'] != '' && isset($application['residence_address']) && $application['residence_address'] != '' && isset($application['permanent_address']) && $application['permanent_address'] != '' && isset($application['expected_date_join']) && $application['expected_date_join'] != '' && isset($application['relocate_willing']) && $application['relocate_willing'] != '' && isset($application['language_speak']) && $application['language_speak'] != '' && isset($application['language_write']) && $application['language_write'] != '' && isset($application['job_status']) && $application['job_status'] != '' && isset($application['company_id']) && $application['company_id'] != '' && isset($application['job_opening_id']) && $application['job_opening_id'] != '' && isset($application['source']) && $application['source'] != '') {
                $id = $application['id'];
                $application['expected_date_join'] = date('Y-m-d',strtotime($application['expected_date_join']));
                $application['dob'] = date('Y-m-d',strtotime($application['dob']));
                $from = new DateTime($application['dob']);
                $to   = new DateTime('today');
                $application['age'] = $from->diff($to)->y;
                $application['updatedon'] = date('Y-m-d H:i:s');
                $result = $this->Api_model->update_application($application, $id);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Application Updated Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Application not Updated');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function update_application_status(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $application = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($application['id']) && $application['id'] != '') {
                $id = $application['id'];
                $application['updatedon'] = date('Y-m-d H:i:s');
                $result = $this->Api_model->update_application($application, $id);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Application Updated Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Application not Updated');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function update_application_assign(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $application = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($application['id']) && $application['id'] != '' && isset($application['assigned_to']) && $application['assigned_to'] != '') {
                $id = $application['id'];
                $application['assigned_date'] = date('Y-m-d');
                $application['updatedon'] = date('Y-m-d H:i:s');
                $result = $this->Api_model->update_application($application, $id);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Application Updated Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Application not Updated');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }
    
    public function delete_application()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $application = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($application['id']) && $application['id'] != '') {
                $id = $application['id'];
                $application['status'] = 3;
                $application['updatedon'] = date('Y-m-d H:i:s');
                $result = $this->Api_model->update_application($application, $id);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Application Deleted Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Application not Deleted');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function add_kyc()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $kyc = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($kyc['emp_id']) && $kyc['emp_id'] != '' && isset($kyc['doc_id']) && $kyc['doc_id'] != '' && isset($kyc['filename']) && $kyc['filename'] != '' && isset($kyc['createdby']) && $kyc['createdby'] != ''){
            $kyc['mode'] = 'KYC';   
            $kyc['status'] = 1;   

            $result = $this->Api_model->insert_kyc($kyc);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'KYC Added Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'KYC not Added');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }
    public function update_kyc()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $kyc = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($kyc['id']) && $kyc['id'] != '' && isset($kyc['emp_id']) && $kyc['emp_id'] != '' && isset($kyc['doc_id']) && $kyc['doc_id'] != '' && isset($kyc['filename']) && $kyc['filename'] != '' && isset($kyc['updatedby']) && $kyc['updatedby'] != '') {
                $id = $kyc['id'];
                $kyc['updatedon'] = date('Y-m-d H:i:s');
                $result = $this->Api_model->update_kyc($kyc, $id);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Kyc Updated Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Kyc not Updated');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function add_staffmaster()
    {
        $this->load->library('email');
        $json_input = file_get_contents('php://input'); // JSON Input
        $staffmaster = json_decode($json_input, true);
$check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($staffmaster['company_id']) && isset($staffmaster['dob']) && isset($staffmaster['branch_id']) && isset($staffmaster['mobno']) && isset($staffmaster['mail_id']) && isset($staffmaster['maritalstatus']) &&  isset($staffmaster['gender']) && isset($staffmaster['paddress']) && isset($staffmaster['taddress'])&& isset($staffmaster['jobstatus']) && isset($staffmaster['status']) && $staffmaster['company_id'] != '' && $staffmaster['dob'] != '' && $staffmaster['branch_id'] != '' && $staffmaster['mobno'] != '' && $staffmaster['mail_id'] != '' && $staffmaster['maritalstatus'] != '' && $staffmaster['gender'] != '' && $staffmaster['paddress'] != '' && $staffmaster['taddress'] != '' && $staffmaster['jobstatus'] != '' && $staffmaster['status'] != '' && isset($staffmaster['name']) && $staffmaster['name'] != '' && isset($staffmaster['biometricAccess']) && $staffmaster['biometricAccess'] != '') {

            if ($staffmaster['biometricAccess']) {
                $check_duplicate = $this->Api_model->check_duplicate('hrms_staffmaster', 'biometricAccess', $staffmaster['biometricAccess']);
                //    echo $this->db->last_query();die;
                if ($check_duplicate) {
                    $output = array('status' => 'error', 'message' => 'BiometricAccess Already Exists');
                    echo json_encode($output);
                    exit;
                }
            }

            $reference_data = (isset($staffmaster['reference']) && $staffmaster['reference'])?$staffmaster['reference']:[];
            $relation_data = (isset($staffmaster['relation']) && $staffmaster['relation'])?$staffmaster['relation']:[];
            $work_exp_data = (isset($staffmaster['work_exp']) && $staffmaster['work_exp'])?$staffmaster['work_exp']:[];
            $hike_history = (isset($staffmaster['hike_history']) && $staffmaster['hike_history'])?$staffmaster['hike_history']:[];
            $banks = (isset($staffmaster['bank']) && $staffmaster['bank'])?$staffmaster['bank']:[];
            $kyc = (isset($staffmaster['kyc']) && $staffmaster['kyc'])?$staffmaster['kyc']:[];
            $received_doc = (isset($staffmaster['received_doc']) && $staffmaster['received_doc'])?$staffmaster['received_doc']:[];
            // $relation_data = $staffmaster['relation'];
            // $work_exp_data = $staffmaster['work_exp'];
            // $hike_history = $staffmaster['hike_history'];
            unset($staffmaster['reference']);
            unset($staffmaster['relation']);
            unset($staffmaster['work_exp']);
            unset($staffmaster['hike_history']);
            unset($staffmaster['kyc']);
            unset($staffmaster['received_doc'],$staffmaster['bank']);

            $this->db->select('*');
            $this->db->from('hrms_staffmaster');
            $query = $this->db->get();        
            if ($query->num_rows() > 0) {
                $staff = $query->result_array();
            }else{
                $staff = [];
            }
        
                $prefix_number = 'ZGEMP-'.digits_set(count($staff) + 1);
                // $staffmaster['emp_code'] = $prefix_number;
                $staffmaster['dob'] = date('Y-m-d', strtotime($staffmaster['dob']));
                $staffmaster['doj'] = (isset($staffmaster['doj'])&&$staffmaster['doj']!='')?date('Y-m-d', strtotime($staffmaster['doj'])):null;
                $staffmaster['relieveddt'] = (isset($staffmaster['relieveddt'])&&$staffmaster['relieveddt']!='')?date('Y-m-d', strtotime($staffmaster['relieveddt'])):null;
                // $holiday['status'] = 1;
                // $this->db->trans_start();
                // $this->db->trans_strict(FALSE);
                $staffmaster['onboard_status'] = 0;
                $staffmaster['status'] = $staffmaster['jobstatus'];
                $staff_id = $this->Api_model->insert_staffmaster($staffmaster);
                // echo $this->db->last_query();
                // die;
                // echo $staff_id;
                if($staff_id){
                    /*$enc_id = $this->encrypt->encode($staff_id);
                    $url = 'https://hrm.zerogravitygroups.com/#/onboarding';
                    $vendor_update['onboard_link'] = $link = $url."/?id=".$enc_id."";
                    $this->db->where('id',$staff_id);
                    $this->db->update('hrms_staffmaster',$vendor_update);
                    $this->email->set_mailtype("html");
                    $this->email->from('zg@gmail.com', 'ZG'); 
                    $this->email->to($staffmaster['mail_id']);
                    $this->email->subject('Welcome'); 
                    $html = "Dear ". $staffmaster['name']."<br>";
                    $html .= "Your Onboard Link <a href='".$link."'>click Here</a><br>";
                    $html .= "Thanks From ZG";
                    $this->email->message($html); 

                    //Send mail 
                    $this->email->send();*/
                    // refrence data
                    $m=0;
                    if($reference_data){
                        foreach($reference_data as $ref){
                            // echo "<pre>";
                            // print_r($ref);
                            // if(isset($ref['company_id']) && $ref['company_id'] != '' && isset($ref['branch_id']) && $ref['branch_id'] != '' && isset($ref['refname']) && $ref['refname'] != '' && isset($ref['relationship']) && $ref['relationship'] != '' && isset($ref['refmob']) && $ref['refmob'] != ''){
                                $ref['emp_id'] = $staff_id;
                                $ref['company_id'] = $staffmaster['company_id'];
                                $ref['branch_id'] = $staffmaster['branch_id'];
                                $this->Api_model->insert_reference($ref);
                                // echo $this->db->last_query();
                                // echo "<br>";

                            // }else{
                            //     $m++;
                                // $this->db->trans_complete();
                                // echo $this->db->trans_status();
                                // if ($this->db->trans_status() === FALSE) {
                                //     $this->db->trans_rollback();
                                // } 
                                // $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
                                // echo json_encode($output);
                                
                            // }
                        }
                    }
                    // relation data
                    if($relation_data){
                        foreach($relation_data as $rel){
                            // if(isset($rel['company_id']) && $rel['company_id'] != '' && isset($rel['branch_id']) && $rel['branch_id'] != '' && isset($rel['relname']) && $rel['relname'] != '' && isset($rel['relationship']) && $rel['relationship'] != '' && isset($rel['relmob']) && $rel['relmob'] != ''){
                                $rel['emp_id'] = $staff_id;
                                $rel['company_id'] = $staffmaster['company_id'];
                                $rel['branch_id'] = $staffmaster['branch_id'];
                                $this->Api_model->insert_relation($rel);
                                // echo $this->db->last_query();
                                // echo "<br>";

                            // }else{
                            //     $m++;
                                // $this->db->trans_complete();
                                // if ($this->db->trans_status() === FALSE) {
                                //     $this->db->trans_rollback();
                                // } 
                                // $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
                                // echo json_encode($output);
                                // exit;
                            // }
                        } 
                    }
                    // Work Exp data
                    if($work_exp_data){
                        foreach($work_exp_data as $work){
                            // if(isset($work['company_id']) && $work['company_id'] != '' && isset($work['branch_id']) && $work['branch_id'] != '' && isset($work['relname']) && $work['relname'] != '' && isset($work['relationship']) && $work['relationship'] != '' && isset($work['relmob']) && $work['relmob'] != ''){
                                $work['emp_id'] = $staff_id;
                                $work['exp_from'] = date('Y-m-d',strtotime($work['exp_from']));
                                $work['exp_to'] = date('Y-m-d',strtotime($work['exp_to']));
                                $this->Api_model->insert_work_exp($work);
                                // echo $this->db->last_query();
                                // echo "<br>";

                            /*}else{
                                $this->db->trans_complete();
                                if ($this->db->trans_status() === FALSE) {
                                    $this->db->trans_rollback();
                                } 
                                $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
                                echo json_encode($output);
                                exit;
                            }*/
                        }
                    }
                    // Work Exp data
                    if($banks){
                        foreach($banks as $bank){
                            // if(isset($work['company_id']) && $work['company_id'] != '' && isset($work['branch_id']) && $work['branch_id'] != '' && isset($work['relname']) && $work['relname'] != '' && isset($work['relationship']) && $work['relationship'] != '' && isset($work['relmob']) && $work['relmob'] != ''){
                                $bank['emp_id'] = $staff_id;
                                $this->Api_model->insert_bank($bank);
                                // echo $this->db->last_query();
                                // echo "<br>";

                            /*}else{
                                $this->db->trans_complete();
                                if ($this->db->trans_status() === FALSE) {
                                    $this->db->trans_rollback();
                                } 
                                $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
                                echo json_encode($output);
                                exit;
                            }*/
                        }
                    }
                    // Hike hisory data
                    if($hike_history){
                        foreach($hike_history as $hike){
                            $hikes= array();
                            $hikes['emp_id'] = $staff_id;
                            $hikes['nhikedate'] = date('Y-m-d H:i:s',strtotime($hike['hikedate']));
                            $hikes['nhike_per'] = $hike['hike_per'];
                            $hikes['nhikeamount'] = $hike['hikeamount'];
                            // $hikes['current_salary'] = $staffmaster['basicsalary'];
                            // $hikes['hiked_salary'] = $staffmaster['basicsalary']+$hike['hikeamount'];
                            $hikes['notes'] = (isset($hike['notes']))?$hike['notes']:null;
                            $this->Api_model->insert_hike($hikes);
                        }
                    }

                    // Kyc hisory data
                    if($kyc){
                        foreach($kyc as $val){
                            $val['emp_id'] = $staff_id;
                            $val['mode'] = 'KYC';
                            $this->Api_model->insert_kyc($val);
                            // echo $this->db->last_query();
                            //     echo "<br>";
                        }
                    }
                    // received_doc hisory data
                    if($received_doc){
                        foreach($received_doc as $val){
                            $val['emp_id'] = $staff_id;
                            $val['mode'] = 'received_documents';
                            $this->Api_model->insert_kyc($val);
                            // echo $this->db->last_query();
                            //     echo "<br>";
                        }
                    }
                    $output = array('status' => 'success', 'message' => 'Staff Added Succesfully');
                    echo json_encode($output);
                }else{
                    $output = array('status' => 'error', 'message' => 'Staff not Added');
                    echo json_encode($output);
                    exit;
                    
                }
                // if($m>0){
                //     $this->db->trans_rollback();
                //     $result = false;
                //     $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
                //     echo json_encode($output);
                //     exit;
                // }
                // $this->db->trans_complete();
                // if ($this->db->trans_status() === FALSE) {
                //     $this->db->trans_rollback();
                //     $result = false;
                // } 
                // else {
                //     // echo "11";
                //     $this->db->trans_commit();
                //     $result = true;
                // }
                
                // if ($result) {
                //     $output = array('status' => 'success', 'message' => 'Staff Added Succesfully');
                //     echo json_encode($output);
                // } else {
                //     $output = array('status' => 'error', 'message' => 'Staff not Added');
                //     echo json_encode($output);
                // }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function update_staffmaster()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $staffmaster = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        // $data2 = $this->input->post();
        // $staffmaster = array_merge($data1,$data2);
        if (isset($staffmaster['id']) && $staffmaster['id'] != '' && isset($staffmaster['company_id']) && isset($staffmaster['dob']) && isset($staffmaster['branch_id']) && isset($staffmaster['mobno']) && isset($staffmaster['mail_id']) && isset($staffmaster['maritalstatus']) &&  isset($staffmaster['gender']) && isset($staffmaster['paddress']) && isset($staffmaster['taddress'])&& isset($staffmaster['jobstatus']) && isset($staffmaster['status']) && $staffmaster['company_id'] != '' && $staffmaster['dob'] != '' && $staffmaster['branch_id'] != '' && $staffmaster['mobno'] != '' && $staffmaster['mail_id'] != '' && $staffmaster['maritalstatus'] != '' && $staffmaster['gender'] != '' && $staffmaster['paddress'] != '' && $staffmaster['taddress'] != '' && $staffmaster['jobstatus'] != '' && $staffmaster['status'] != '' && isset($staffmaster['name']) && $staffmaster['name'] != '' && isset($staffmaster['biometricAccess']) && $staffmaster['biometricAccess'] != '') {

            if ($staffmaster['biometricAccess']) {
                $check_duplicate = $this->Api_model->check_duplicate('hrms_staffmaster', 'biometricAccess', $staffmaster['biometricAccess'],$staffmaster['id']);
                //    echo $this->db->last_query();die;
                if ($check_duplicate) {
                    $output = array('status' => 'error', 'message' => 'BiometricAccess Already Exists');
                    echo json_encode($output);
                    exit;
                }
            }

                $reference_data = (isset($staffmaster['reference']) && $staffmaster['reference'])?$staffmaster['reference']:[];
                $relation_data = (isset($staffmaster['relation']) && $staffmaster['relation'])?$staffmaster['relation']:[];
                $work_exp_data = (isset($staffmaster['work_exp']) && $staffmaster['work_exp'])?$staffmaster['work_exp']:[];
                $hike_history = (isset($staffmaster['hike_history']) && $staffmaster['hike_history'])?$staffmaster['hike_history']:[];
                $kyc = (isset($staffmaster['kyc']) && $staffmaster['kyc'])?$staffmaster['kyc']:[];
                $received_doc = (isset($staffmaster['received_doc']) && $staffmaster['received_doc'])?$staffmaster['received_doc']:[];
                $banks = (isset($staffmaster['bank']) && $staffmaster['bank'])?$staffmaster['bank']:[];

                unset($staffmaster['reference']);
                unset($staffmaster['relation']);
                unset($staffmaster['work_exp']);
                unset($staffmaster['hike_history']);
                unset($staffmaster['kyc']);
                unset($staffmaster['received_doc'],$staffmaster['bank']);
            
                $staff_id = $staffmaster['id'];
                $staffmaster['updatedon'] = date('Y-m-d H:i:s');
                $staffmaster['dob'] = date('Y-m-d', strtotime($staffmaster['dob']));
                $staffmaster['doj'] = (isset($staffmaster['doj'])&&$staffmaster['doj']!='')?date('Y-m-d', strtotime($staffmaster['doj'])):'';
                // $holiday['status'] = $data['status'];
                // $this->db->trans_start();
                // $this->db->trans_strict(FALSE);
                // $result = $this->Api_model->update_staffmaster($staffmaster, $staff_id);
                if(isset($staffmaster['onboard_status']) && $staffmaster['onboard_status'] == 1){
                    $staffmaster['onboard_date'] = date('Y-m-d H:i:s');
                }
                $staffmaster['status'] = $staffmaster['jobstatus'];
                if($this->Api_model->update_staffmaster($staffmaster, $staff_id)){
                    // refrence data
                    if($reference_data){
                        foreach($reference_data as $ref){
                                $ref_id = $ref['id'];
                                if($ref_id != ''){
                                    $ref['emp_id'] = $staff_id;
                                    $ref['company_id'] = $staffmaster['company_id'];
                                    $ref['branch_id'] = $staffmaster['branch_id'];
                                    $ref['updatedon'] = date('Y-m-d H:i:s');
                                    $ref['updatedby'] = $staffmaster['updatedby'];
                                    $this->Api_model->update_reference($ref,$ref_id);
                                }else{
                                    unset($ref['id']);
                                    $ref['emp_id'] = $staff_id;
                                    $ref['company_id'] = $staffmaster['company_id'];
                                    $ref['branch_id'] = $staffmaster['branch_id'];
                                    $ref['createdby'] = $staffmaster['updatedby'];
                                    $this->Api_model->insert_reference($ref);
                                }
                                

                        }
                    }
                    // relation data
                    if($relation_data){
                        foreach($relation_data as $rel){
                                $rel_id = $rel['id'];
                                if($rel_id != ''){
                                    $rel['emp_id'] = $staff_id;
                                    $rel['company_id'] = $staffmaster['company_id'];
                                    $rel['branch_id'] = $staffmaster['branch_id'];
                                    $rel['updatedon'] = date('Y-m-d H:i:s');
                                    $rel['updatedby'] = $staffmaster['updatedby'];
                                    $this->Api_model->update_relation($rel,$rel_id);
                                }else{
                                    unset($rel['id']);
                                    $rel['emp_id'] = $staff_id;
                                    $rel['company_id'] = $staffmaster['company_id'];
                                    $rel['branch_id'] = $staffmaster['branch_id'];
                                    $rel['createdby'] = $staffmaster['updatedby'];
                                    $this->Api_model->insert_relation($rel);
                                }
                                

                        }
                    }
                    // Work Exp data
                    if($work_exp_data){
                        foreach($work_exp_data as $work){
                                $id = $work['id'];
                                if($id != ''){
                                    $work['emp_id'] = $staff_id;
                                    $work['exp_from'] = date('Y-m-d',strtotime($work['exp_from']));
                                    $work['exp_to'] = date('Y-m-d',strtotime($work['exp_to']));
                                    $work['updatedon'] = date('Y-m-d H:i:s');
                                    $work['updatedby'] = $staffmaster['updatedby'];
                                    $this->Api_model->update_work_exp($work,$id);
                                }else{
                                    unset($work['id']);
                                    $work['emp_id'] = $staff_id;
                                    $work['exp_from'] = date('Y-m-d',strtotime($work['exp_from']));
                                    $work['exp_to'] = date('Y-m-d',strtotime($work['exp_to']));
                                    $work['createdby'] = $staffmaster['updatedby'];
                                    $this->Api_model->insert_work_exp($work);
                                }
                                

                        }
                    }
                    // Bank data
                    if($banks){
                        foreach($banks as $bank){
                                $id = $bank['id'];
                                if($id != ''){
                                    $bank['emp_id'] = $staff_id;
                                    $bank['updated_on'] = date('Y-m-d H:i:s');
                                    $bank['updated_by'] = $staffmaster['updatedby'];
                                    $this->Api_model->update_bank($bank,$id);
                                }else{
                                    unset($bank['id']);
                                    $bank['emp_id'] = $staff_id;
                                    $bank['created_by'] = $staffmaster['updatedby'];
                                    $this->Api_model->insert_bank($bank);
                                }
                                

                            
                        }
                    }
                    // Kyc hisory data
                    if($kyc){
                        foreach($kyc as $val){
                            $id = $val['id'];
                            if($id != ''){
                                $val['emp_id'] = $staff_id;
                                $val['mode'] = 'KYC';
                                $val['updatedon'] = date('Y-m-d H:i:s');
                                $val['updatedby'] = $staffmaster['updatedby'];
                                $this->Api_model->update_kyc($val,$id);
                            }else{
                                unset($val['id']);
                                $val['emp_id'] = $staff_id;
                                $val['mode'] = 'KYC';
                                $val['createdby'] = $staffmaster['updatedby'];
                                $this->Api_model->insert_kyc($val);
                            }
                            
                        }
                    }
                    // received_doc hisory data
                    if($received_doc){
                        foreach($received_doc as $val){
                            $id = $val['id'];
                            if($id != ''){
                                $val['emp_id'] = $staff_id;
                                $val['mode'] = 'received_documents';
                                $val['updatedon'] = date('Y-m-d H:i:s');
                                $ref['updatedby'] = $staffmaster['updatedby'];
                                $this->Api_model->update_kyc($val,$id);
                            }else{
                                unset($val['id']);
                                $val['emp_id'] = $staff_id;
                                $val['mode'] = 'received_documents';
                                $val['createdby'] = $staffmaster['updatedby'];
                                $this->Api_model->insert_kyc($val,$id);
                            }
                            
                        }
                    }
                    $result = true;
                }else{
                    
                    $result = false;
                }

                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Staff Updated Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Staff not Updated');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function delete_staffmaster()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $staffmaster = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        // $data2 = $this->input->post();
        // $staffmaster = array_merge($data1,$data2);
        if (isset($staffmaster['id']) && $staffmaster['id'] != '') {

            
                $staff_id = $staffmaster['id'];
                $staffmaster['updatedon'] = date('Y-m-d H:i:s');
                // $holiday['status'] = $data['status'];
                $staffmaster['status'] = 3;
                $staffmaster['jobstatus'] = 3;
                $result = $this->Api_model->update_staffmaster($staffmaster, $staff_id);


                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Staff Updated Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Staff not Updated');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function add_onboarding_emp()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $staffmaster = json_decode($json_input, true);
        // $data1 = json_decode($json_input, true);
        // $data2 = $this->input->post();
        // $staffmaster = array_merge($data1,$data2);
$check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($staffmaster['dob']) && isset($staffmaster['mobno']) && isset($staffmaster['mail_id']) && isset($staffmaster['maritalstatus']) && isset($staffmaster['age']) && isset($staffmaster['gender']) && isset($staffmaster['paddress']) && isset($staffmaster['taddress']) && isset($staffmaster['status']) &&  $staffmaster['dob'] != '' && $staffmaster['mobno'] != '' && $staffmaster['mail_id'] != '' && $staffmaster['maritalstatus'] != '' && $staffmaster['age'] != '' && $staffmaster['gender'] != '' && $staffmaster['paddress'] != '' && $staffmaster['taddress'] != '' && $staffmaster['status'] != '' && isset($staffmaster['name']) && $staffmaster['name'] != '') {

            $reference_data = (isset($staffmaster['reference']) && $staffmaster['reference'])?$staffmaster['reference']:[];
            $relation_data = (isset($staffmaster['relation']) && $staffmaster['relation'])?$staffmaster['relation']:[];
            $work_exp_data = (isset($staffmaster['work_exp']) && $staffmaster['work_exp'])?$staffmaster['work_exp']:[];
            // $relation_data = $staffmaster['relation'];
            // $work_exp_data = $staffmaster['work_exp'];
            // $hike_history = $staffmaster['hike_history'];
            unset($staffmaster['reference']);
            unset($staffmaster['relation']);
            unset($staffmaster['work_exp']);
            $staffmaster['entry_date'] = date('Y-m-d');
            
            $this->db->select('*');
            $this->db->from('hrms_onboarding');
            $query = $this->db->get();        
            if ($query->num_rows() > 0) {
                $staff = $query->result_array();
            }else{
                $staff = [];
            }
        
                $prefix_number = 'ONEMP-'.digits_set(count($staff) + 1);
                $staffmaster['emp_code'] = $prefix_number;
                $staffmaster['dob'] = (isset($staffmaster['dob'])&&$staffmaster['dob']!='')?date('Y-m-d', strtotime($staffmaster['dob'])):'';
                // $holiday['status'] = 1;
                $this->db->trans_start();
                $this->db->trans_strict(FALSE);
                $staff_id = $this->Api_model->insert_onboard($staffmaster);
                // echo $staff_id;
                if($staff_id){
                    // refrence data
                    $m=0;
                    if($reference_data){
                        foreach($reference_data as $ref){
                            // echo "<pre>";
                            // print_r($ref);
                            // if(isset($ref['company_id']) && $ref['company_id'] != '' && isset($ref['branch_id']) && $ref['branch_id'] != '' && isset($ref['refname']) && $ref['refname'] != '' && isset($ref['relationship']) && $ref['relationship'] != '' && isset($ref['refmob']) && $ref['refmob'] != ''){
                                $ref['emp_id'] = $staff_id;
                                $this->Api_model->insert_onboard_reference($ref);
                                // echo $this->db->last_query();
                                // echo "<br>";

                            // }else{
                            //     $m++;
                                // $this->db->trans_complete();
                                // echo $this->db->trans_status();
                                // if ($this->db->trans_status() === FALSE) {
                                //     $this->db->trans_rollback();
                                // } 
                                // $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
                                // echo json_encode($output);
                                
                            // }
                        }
                    }
                    // relation data
                    if($relation_data){
                        foreach($relation_data as $rel){
                            // if(isset($rel['company_id']) && $rel['company_id'] != '' && isset($rel['branch_id']) && $rel['branch_id'] != '' && isset($rel['relname']) && $rel['relname'] != '' && isset($rel['relationship']) && $rel['relationship'] != '' && isset($rel['relmob']) && $rel['relmob'] != ''){
                                $rel['emp_id'] = $staff_id;
                                $this->Api_model->insert_onboard_relation($rel);
                                // echo $this->db->last_query();
                                // echo "<br>";

                            // }else{
                            //     $m++;
                                // $this->db->trans_complete();
                                // if ($this->db->trans_status() === FALSE) {
                                //     $this->db->trans_rollback();
                                // } 
                                // $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
                                // echo json_encode($output);
                                // exit;
                            // }
                        } 
                    }
                    // Work Exp data
                    if($work_exp_data){
                        foreach($work_exp_data as $work){
                            // if(isset($work['company_id']) && $work['company_id'] != '' && isset($work['branch_id']) && $work['branch_id'] != '' && isset($work['relname']) && $work['relname'] != '' && isset($work['relationship']) && $work['relationship'] != '' && isset($work['relmob']) && $work['relmob'] != ''){
                                $work['emp_id'] = $staff_id;
                                $this->Api_model->insert_onboard_work_exp($work);
                                // echo $this->db->last_query();
                                // echo "<br>";

                            /*}else{
                                $this->db->trans_complete();
                                if ($this->db->trans_status() === FALSE) {
                                    $this->db->trans_rollback();
                                } 
                                $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
                                echo json_encode($output);
                                exit;
                            }*/
                        }
                    }
                }
                $this->db->trans_complete();
                if ($this->db->trans_status() === FALSE) {
                    $this->db->trans_rollback();
                    $result = false;
                } 
                else {
                    // echo "11";
                    $this->db->trans_commit();
                    $result = true;
                }
                
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Employee Added Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Employee not Added');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function update_unboard(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $staffmaster = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($staffmaster['id']) && $staffmaster['id'] != ''){
            $relation_data = $staffmaster['relation'];
            $work_exp_data = $staffmaster['work_exp'];
            $kyc = $staffmaster['kyc'];
            $received_doc = $staffmaster['received_doc'];
            $staff_id = $staffmaster['id'];
            if($relation_data){
                foreach($relation_data as $rel){
                    $rel['emp_id'] = $staff_id;
                    $rel['company_id'] = $staffmaster['company_id'];
                    $rel['branch_id'] = $staffmaster['branch_id'];
                    $rel['Open'] = 'Open';
                    $this->Api_model->insert_relation($rel);
                        
                } 
            }
            // Work Exp data
            if($work_exp_data){
                foreach($work_exp_data as $work){
                    $work['emp_id'] = $staff_id;
                    $this->Api_model->insert_work_exp($work);
                        
                }
            }
            // Kyc hisory data
            if($kyc){
                foreach($kyc as $val){
                    $val['emp_id'] = $staff_id;
                    $val['mode'] = 'KYC';
                    $this->Api_model->insert_kyc($val);
                }
            }
            // received_doc hisory data
            if($received_doc){
                foreach($received_doc as $val){
                    $val['emp_id'] = $staff_id;
                    $val['mode'] = 'received_documents';
                    $this->Api_model->insert_kyc($val);
                }
            }
            $output = array('status' => 'success', 'message' => 'Employee Updated Succesfully');
            echo json_encode($output);

        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
}else{
        	   echo json_encode($response); 
        	}
        }
    }
    
    public function update_onboarding_emp()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $staffmaster = json_decode($json_input, true);
        // $data2 = $this->input->post();
        // $staffmaster = array_merge($data1,$data2);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($staffmaster['id']) && $staffmaster['id'] != '' && isset($staffmaster['dob']) && isset($staffmaster['mobno']) && isset($staffmaster['mail_id']) && isset($staffmaster['maritalstatus']) && isset($staffmaster['age']) && isset($staffmaster['gender']) && isset($staffmaster['paddress']) && isset($staffmaster['taddress']) && isset($staffmaster['status']) &&  $staffmaster['dob'] != '' && $staffmaster['mobno'] != '' && $staffmaster['mail_id'] != '' && $staffmaster['maritalstatus'] != '' && $staffmaster['age'] != '' && $staffmaster['gender'] != '' && $staffmaster['paddress'] != '' && $staffmaster['taddress'] != '' && $staffmaster['status'] != '' && isset($staffmaster['name']) && $staffmaster['name'] != '') {
                $reference_data = $staffmaster['reference'];
                $relation_data = $staffmaster['relation'];
                $work_exp_data = $staffmaster['work_exp'];
                unset($staffmaster['reference']);
                unset($staffmaster['relation']);
                unset($staffmaster['work_exp']);
                $staffmaster['updatedon'] = date('Y-m-d H:i:s');
                $staff_id = $staffmaster['id'];
                $staffmaster['dob'] = (isset($staffmaster['dob'])&&$staffmaster['dob']!='')?date('Y-m-d', strtotime($staffmaster['dob'])):'';
                // $holiday['status'] = $data['status'];
                $this->db->trans_start();
                $this->db->trans_strict(FALSE);
                $result = $this->Api_model->update_onboard_staffmaster($staffmaster, $staff_id);

                if($staff_id){
                    // refrence data
                    if($reference_data){
                        foreach($reference_data as $ref){
                            // if(isset($ref['company_id']) && $ref['company_id'] != '' && isset($ref['branch_id']) && $ref['branch_id'] != '' && isset($ref['refname']) && $ref['refname'] != '' && isset($ref['relationship']) && $ref['relationship'] != '' && isset($ref['refmob']) && $ref['refmob'] != '' && isset($ref['id']) && $ref['id'] != ''){
                                $ref_id = $ref['id'];
                                $ref['emp_id'] = $staff_id;
                                $ref['updatedon'] = date('Y-m-d H:i:s');
                                $this->Api_model->update_onboard_reference($ref,$ref_id);

                            // }else{
                            //     $this->db->trans_complete();
                            //     if ($this->db->trans_status() === FALSE) {
                            //         $this->db->trans_rollback();
                            //     } 
                            //     $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
                            //     echo json_encode($output);
                            //     exit;
                            // }
                        }
                    }
                    // relation data
                    if($relation_data){
                        foreach($relation_data as $rel){
                            // if(isset($rel['company_id']) && $rel['company_id'] != '' && isset($rel['branch_id']) && $rel['branch_id'] != '' && isset($rel['relname']) && $rel['relname'] != '' && isset($rel['relationship']) && $rel['relationship'] != '' && isset($rel['relmob']) && $rel['relmob'] != '' && isset($rel['id']) && $rel['id'] != ''){
                                $rel_id = $rel['id'];
                                $rel['emp_id'] = $staff_id;
                                $rel['updatedon'] = date('Y-m-d H:i:s');
                                $this->Api_model->update_onboard_relation($rel,$rel_id);

                            // }else{
                            //     $this->db->trans_complete();
                            //     if ($this->db->trans_status() === FALSE) {
                            //         $this->db->trans_rollback();
                            //     } 
                            //     $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
                            //     echo json_encode($output);
                            //     exit;
                            // }
                        }
                    }
                    // Work Exp data
                    if($work_exp_data){
                        foreach($work_exp_data as $work){
                            // if(isset($work['company_id']) && $work['company_id'] != '' && isset($work['branch_id']) && $work['branch_id'] != '' && isset($work['relname']) && $work['relname'] != '' && isset($work['relationship']) && $work['relationship'] != '' && isset($work['relmob']) && $work['relmob'] != ''){
                                $id = $work['id'];
                                $work['emp_id'] = $staff_id;
                                $work['updatedon'] = date('Y-m-d H:i:s');
                                $this->Api_model->update_onboard_work_exp($work,$id);

                            /*}else{
                                $this->db->trans_complete();
                                if ($this->db->trans_status() === FALSE) {
                                    $this->db->trans_rollback();
                                } 
                                $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
                                echo json_encode($output);
                                exit;
                            }*/
                        }
                    }
                }
                $this->db->trans_complete();
                if ($this->db->trans_status() === FALSE) {
                    $this->db->trans_rollback();
                    $result = false;
                } 
                else {
                    $this->db->trans_commit();
                    $result = true;
                }

                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Employee Updated Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Employee not Updated');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function delete_onboarding_emp()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $staffmaster = json_decode($json_input, true);
        // $data2 = $this->input->post();
        // $staffmaster = array_merge($data1,$data2);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($staffmaster['id']) && $staffmaster['id'] != '') {
                
                $staffmaster['updatedon'] = date('Y-m-d H:i:s');
                $staff_id = $staffmaster['id'];
                $staffmaster['status'] = 3;
                $result = $this->Api_model->update_onboard_staffmaster($staffmaster, $staff_id);

                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Employee Deleted Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Employee not Deleted');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function duplicate_check()
    {
        $data = $this->input->post('data');
        $table_name = $this->input->post('table_name');
        $colum = $this->input->post('colum');
        $data = $this->Common_model->duplicate_check($data, $table_name, $colum);
        print_r($data);
    }

    public function duplicate_checkedit()
    {
        // echo '<pre>'; print_r($this->input->post());exit;
        $id = $this->input->post('id');
        $data = $this->input->post('data');
        $table_name = $this->input->post('table_name');
        $colum = $this->input->post('colum');
        $data = $this->Common_model->duplicate_checkedit($data, $table_name, $colum, $id);
        print_r($data);
    }

    public function get_employees(){
$check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $this->db->select('emp.*,hrms_designation.name as designation_name,hrms_department.name as departmenet_name,hrms_company.name as company_name,hrms_branch.name as branch_name');
        $this->db->from('hrms_staffmaster as emp');
        $this->db->join('hrms_branch','hrms_branch.id=emp.branch_id');
        $this->db->join('hrms_company','hrms_company.id=emp.company_id');
        $this->db->join('hrms_department','hrms_department.id=emp.department_id');
        $this->db->join('hrms_designation','hrms_designation.id=emp.designation_id');
        $this->db->where('emp.status',1);
        $query = $this->db->get();
        if($query->num_rows() > 0){
            $result = $query->result_array();
            $data = array();
            foreach($result as $key => $val){
                $data[$val['company_name']][$val['branch_name']][$val['designation_name']][] = $val; 
            }
            if($data){
                $output = array('status' => 'success', 'message' => 'Employees List', 'data' => $data);
                echo json_encode($output);
            }
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }

    }

    public function get_test(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($data['username']) && !empty($data['username']) && isset($data['dep_id']) && !empty($data['dep_id']) && isset($data['address']) && !empty($data['address']) && isset($data['phone_no']) && !empty($data['phone_no']) && isset($data['email']) && !empty($data['email']) && isset($data['type']) && !empty($data['type'])){
            $url = 'https://mylearning.zerogravityphotography.in/api/save_open_test';
            $master_data=array(
                "username" => $data['username'],
                "dep_id" => $data['dep_id'],
                "address" => $data['address'],
                "phone_no" => $data['phone_no'],
                "email" => $data['email'],
                "type" => $data['type']
            );

            $ch = curl_init($url);

            // $postString1 = http_build_query($master_data);
            $request_data = json_encode($master_data);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request_data);  
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            curl_close($ch);
            // echo "<pre>";
            // print_r($response);
            // die;
            $response = json_decode($response, true);
            if(isset($response['Success']) && $response['Success'] == "true"){
                $update = array();
                $update['test_id'] =$response['data']['Data'][0]['Id'];
                $update['test_link'] =$response['data']['Data'][0]['Link'];
                $update['test_status'] ="Open";
                $update['linkgenerate_dt'] =date('Y-m-d');
                $update['updateon'] =date('Y-m-d H:i:s');
                $this->db->where('jobapply_id',$data['jobapply_id']);
                if($this->db->update('hrms_online_test',$update)){
                    $output = array('status' => 'success', 'message' => 'Test data Saved', 'data' => $response);
                    echo json_encode($output);
                }else{
                    $output = array('status' => 'error', 'message' => 'Test Not saved');
                    echo json_encode($output);
                }
            }else{
                $output = array('status' => 'error', 'message' => 'Test Not saved');
                echo json_encode($output);
            }
        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function save_open_test(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($data['id']) && !empty($data['id']) && isset($data['username']) && !empty($data['username']) && isset($data['dep_id']) && !empty($data['dep_id']) && isset($data['address']) && !empty($data['address']) && isset($data['phone_no']) && !empty($data['phone_no']) && isset($data['email']) && !empty($data['email']) && isset($data['type']) && !empty($data['type']) && isset($data['empid']) && !empty($data['empid'])){
            $url = 'https://mylearning.zerogravityphotography.in/api/save_open_test';
            $master_data=array(
                "id" => $data['id'],
                "empid" => $data['empid'],
                "username" => $data['username'],
                "dep_id" => $data['dep_id'],
                "address" => $data['address'],
                "phone_no" => $data['phone_no'],
                "email" => $data['email'],
                "type" => $data['type']
            );

            $ch = curl_init($url);

            // $postString1 = http_build_query($master_data);
            $request_data = json_encode($master_data);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request_data);  
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            curl_close($ch);
            // echo "<pre>";
            // print_r($response);
            // die;
            $response = json_decode($response, true);
            if(isset($response['Success']) && $response['Success'] == "true"){
                $update = array();
                // $update['test_id'] =$response['data']['Data'][0]['Id'];
                // $update['test_link'] =$response['data']['Data'][0]['Link'];
                $update['test_status'] ="Close";
                // $update['linkgenerate_dt'] =date('Y-m-d');
                $update['updateon'] =date('Y-m-d H:i:s');
                $this->db->where('jobapply_id',$data['jobapply_id']);
                if($this->db->update('hrms_online_test',$update)){
                    $output = array('status' => 'success', 'message' => 'Test data Saved', 'data' => $response);
                    echo json_encode($output);
                }else{
                    $output = array('status' => 'error', 'message' => 'Test Not saved');
                    echo json_encode($output);
                }
            }else{
                $output = array('status' => 'error', 'message' => 'Test Not saved');
                echo json_encode($output);
            }
        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function file_upload(){
        $input = $this->input->post();
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($_FILES['filename']) && $_FILES['filename'] != ''){
            if(!is_dir('./assets/uploads/')){
                mkdir('./assets/uploads/', 0777, true); 
            }
            $config['upload_path']          = './assets/uploads/';
            $config['allowed_types']        = '*';
            // $new_name                       = $application_no.'_profile.jpg';
            // $config['file_name']            = $new_name;
            
            $this->load->library('upload', $config);
            $this->upload->initialize($config);

            if ( ! $this->upload->do_upload('filename'))
            {
                    // $error = array('error' => $this->upload->display_errors());
                    // $file_name = '';     
                    // $application['filename'] = 'noimg.jpg';        
            }
            else
            {   
                    $data = array('upload_data' => $this->upload->data());
                    $upload_data = $this->upload->data(); 
                    $file_name =   $upload_data['file_name'];
                    $application['filelink'] = 'assets/uploads/'.$file_name;
            }
                if ($application) {
                    $output = array('status' => 'success', 'message' => 'File uploded Succesfully',"Data" => $application);
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'File uploded not Added');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function result(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($data['userId']) && !empty($data['userId'])){
            $url = 'https://mylearning.zerogravityphotography.in/api/result';
            $master_data=array(
                "userId" => $data['userId']
            );

            $ch = curl_init($url);

            // $postString1 = http_build_query($master_data);
            $request_data = json_encode($master_data);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request_data);  
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            curl_close($ch);
            // echo "<pre>";
            // print_r($response);
            // die;
            $response = json_decode($response, true);
            if(isset($response['Success']) && $response['Success'] == "true"){
                $update = array();
                $update['duration'] =$response['Data'][0]['Duration'];
                $update['result'] =$response['Data'][0]['Status'];
                $update['result_percentage'] = $response['Data'][0]['Percentage'];
                $update['result_dt'] =date('Y-m-d');
                $update['updateon'] =date('Y-m-d H:i:s');
                $this->db->where('test_id',$data['userId']);
                if($this->db->update('hrms_online_test',$update)){
                    $output = array('status' => 'success', 'message' => 'Result data Saved', 'data' => $response);
                    echo json_encode($output);
                }else{
                    $output = array('status' => 'error', 'message' => 'Result Not saved');
                    echo json_encode($output);
                }
            }else{
                $output = array('status' => 'error', 'message' => 'Result Not saved');
                echo json_encode($output);
            }
        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    //Source Function start - by mohaseen//
    public function source()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $result = $this->Api_model->get_source();
        if ($result) {
            $output = array('status' => 'success', 'message' => 'Source List', 'data' => $result);
            echo json_encode($output);
        } else {
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function get_source()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if ($data['id']) {
            $result = $this->Api_model->get_source($data['id']);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Source List', 'data' => $result);
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'No data found');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Source Id');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    } 

    public function get_active_source()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $result = $this->Api_model->get_active_source($data);
        if ($result) {
            $output = array('status' => 'success', 'message' => 'Source List', 'data' => $result);
            echo json_encode($output);
        } else {
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    } 

    public function add_source()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $source = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($source['sourcename']) && isset($source['status']) && $source['sourcename'] != '' && $source['status'] != '') {
           
                if ($source['sourcename']) {
                    $check_duplicate = $this->Api_model->check_duplicate('hrms_source', 'sourcename', $source['sourcename']);
                    if ($check_duplicate) {
                        $output = array('status' => 'error', 'message' => 'Source Already Exists');
                        echo json_encode($output);
                        exit;
                    }
                }
                $result = $this->Api_model->insert_source($source);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Source Added Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Source not Added');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function update_source()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $source = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($source['id']) && $source['id'] != '' && isset($source['sourcename']) && $source['sourcename'] != '' && isset($source['status']) && $source['status'] != '') {
           
                if ($source['sourcename']) {
                    $check_duplicate = $this->Api_model->check_duplicate('hrms_source', 'sourcename', $source['sourcename'], $source['id']);
                    if ($check_duplicate) {
                        $output = array('status' => 'error', 'message' => 'Source Already Exists');
                        echo json_encode($output);
                        exit;
                    }
                }
                $result = $this->Api_model->update_source($source, $source['id']);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Source Updated Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Source not Updated');
                    echo json_encode($output);
                }
           
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function delete_source()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $source = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($source['id']) && $source['id'] != '') {
           
                $source['status'] = 3;
                $result = $this->Api_model->update_source($source, $source['id']);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Source Deleted Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Source not Deleted');
                    echo json_encode($output);
                }
           
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }
    //Source Function end//

    //Questions Function start - by mohaseen//
    public function questions()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $result = $this->Api_model->get_questions();
        if ($result) {
            $output = array('status' => 'success', 'message' => 'Questions List', 'data' => $result);
            echo json_encode($output);
        } else {
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function get_questions()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if ($data['id']) {
            $result = $this->Api_model->get_questions($data['id']);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Questions List', 'data' => $result);
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'No data found');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Questions Id');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    } 

    public function get_active_questions()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $result = $this->Api_model->get_active_questions();
        if ($result) {
            $output = array('status' => 'success', 'message' => 'Questions List', 'data' => $result);
            echo json_encode($output);
        } else {
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    } 

    public function add_questions()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $questions = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($questions['question']) && isset($questions['status']) && $questions['question'] != '' && $questions['status'] != '' && isset($questions['option2']) && $questions['option2'] != '' && isset($questions['option1']) && $questions['option1'] != '' && isset($questions['created_by']) && $questions['created_by'] != '') {
           
                if ($questions['question']) {
                    $check_duplicate = $this->Api_model->check_duplicate('hrms_questions', 'question', $questions['question']);
                    if ($check_duplicate) {
                        $output = array('status' => 'error', 'message' => 'Question Already Exists');
                        echo json_encode($output);
                        exit;
                    }
                }
                $result = $this->Api_model->insert_questions($questions);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Questions Added Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Questions not Added');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function update_questions()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $questions = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($questions['id']) && $questions['id'] != '' && isset($questions['question']) && isset($questions['status']) && $questions['question'] != '' && $questions['status'] != '' && isset($questions['option2']) && $questions['option2'] != '' && isset($questions['option1']) && $questions['option1'] != '' && isset($questions['updated_by']) && $questions['updated_by'] != '') {
           
                if ($questions['question']) {
                    $check_duplicate = $this->Api_model->check_duplicate('hrms_questions', 'question', $questions['question'], $questions['id']);
                    if ($check_duplicate) {
                        $output = array('status' => 'error', 'message' => 'Questions Already Exists');
                        echo json_encode($output);
                        exit;
                    }
                }
                $questions['update_on'] = date('Y-m-d H:i:s');
                $result = $this->Api_model->update_questions($questions, $questions['id']);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Questions Updated Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Questions not Updated');
                    echo json_encode($output);
                }
           
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function delete_questions()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $questions = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($questions['id']) && $questions['id'] != '') {
           
                $questions['status'] = 3;
                $result = $this->Api_model->update_questions($questions, $questions['id']);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Questions Deleted Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Questions not Deleted');
                    echo json_encode($output);
                }
           
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }
    //Questions Function end//

    //Bank Function start - by mohaseen//
    public function bank()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $result = $this->Api_model->get_bank();
        if ($result) {
            $output = array('status' => 'success', 'message' => 'Bank List', 'data' => $result);
            echo json_encode($output);
        } else {
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function get_bank()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $data = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if ($data['id']) {
            $result = $this->Api_model->get_bank($data['id']);
            if ($result) {
                $output = array('status' => 'success', 'message' => 'Bank List', 'data' => $result);
                echo json_encode($output);
            } else {
                $output = array('status' => 'error', 'message' => 'No data found');
                echo json_encode($output);
            }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Bank Id');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }  

    public function add_bank()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $bank = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($bank['emp_id']) && $bank['emp_id'] != '' && isset($bank['account_name']) && $bank['account_name'] != '' && isset($bank['account_number']) && $bank['account_number'] != '' && isset($bank['bank_name']) && $bank['bank_name'] != '' && isset($bank['ifsc']) && $bank['ifsc'] != '' && isset($bank['branch_name']) && $bank['branch_name'] != '' && isset($bank['type']) && $bank['type'] != '' && isset($bank['status']) && $bank['status'] != '' && isset($bank['created_by']) && $bank['created_by'] != '') {
           
                $result = $this->Api_model->insert_bank($bank);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Bank Added Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Bank not Added');
                    echo json_encode($output);
                }
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function update_bank()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $bank = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($bank['id']) && $bank['id'] != '' && isset($bank['emp_id']) && $bank['emp_id'] != '' && isset($bank['account_name']) && $bank['account_name'] != '' && isset($bank['account_number']) && $bank['account_number'] != '' && isset($bank['bank_name']) && $bank['bank_name'] != '' && isset($bank['ifsc']) && $bank['ifsc'] != '' && isset($bank['branch_name']) && $bank['branch_name'] != '' && isset($bank['type']) && $bank['type'] != '' && isset($bank['status']) && $bank['status'] != '' && isset($bank['updated_by']) && $bank['updated_by'] != '') {
           
                $bank['update_on'] = date('Y-m-d H:i:s');
                $result = $this->Api_model->update_bank($bank, $bank['id']);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Bank Updated Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Bank not Updated');
                    echo json_encode($output);
                }
           
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function delete_bank()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $bank = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($bank['id']) && $bank['id'] != '') {
           
                $bank['status'] = 3;
                $result = $this->Api_model->update_bank($bank, $bank['id']);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Bank Deleted Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Bank not Deleted');
                    echo json_encode($output);
                }
           
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }
    //Bank Function end//
    public function delete_work_exp()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $work_exp = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($work_exp['id']) && $work_exp['id'] != '') {
           
                $work_exp['status'] = 3;
                $work_exp['updatedon'] = date('Y-m-d H:i:s');
                $result = $this->Api_model->update_work_exp($work_exp, $work_exp['id']);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Work Exp Deleted Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Work Exp not Deleted');
                    echo json_encode($output);
                }
           
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }
    public function delete_reference()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $ref = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($ref['id']) && $ref['id'] != '') {
           
                $ref['status'] = 3;
                $ref['updatedon'] = date('Y-m-d H:i:s');
                $result = $this->Api_model->update_reference($ref, $ref['id']);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Reference Deleted Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Reference not Deleted');
                    echo json_encode($output);
                }
           
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }
    public function delete_relation()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $rel = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($rel['id']) && $rel['id'] != '') {
           
                $rel['status'] = 3;
                $rel['updatedon'] = date('Y-m-d H:i:s');
                $result = $this->Api_model->update_relation($rel, $rel['id']);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Relation Deleted Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Relation not Deleted');
                    echo json_encode($output);
                }
           
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }
    public function delete_kyc()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $kyc = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($kyc['id']) && $kyc['id'] != '') {
           
                $kyc['status'] = 3;
                $kyc['updatedon'] = date('Y-m-d H:i:s');
                $result = $this->Api_model->update_kyc($kyc, $kyc['id']);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'KYC Deleted Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'KYC not Deleted');
                    echo json_encode($output);
                }
           
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }
    public function delete_received_doc()
    {
        $json_input = file_get_contents('php://input'); // JSON Input
        $received_doc = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if (isset($received_doc['id']) && $received_doc['id'] != '') {
           
                $received_doc['status'] = 3;
                $received_doc['updatedon'] = date('Y-m-d H:i:s');
                $result = $this->Api_model->update_kyc($received_doc, $received_doc['id']);
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Recieved Document Deleted Succesfully');
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'Recieved Document not Deleted');
                    echo json_encode($output);
                }
           
        } else {
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function update_staff_master_notification(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($input['emp_id']) && $input['emp_id'] != ''){
            $id = $input['emp_id'];
            unset($input['emp_id']);
            $this->db->where('id',$id);
            if($this->db->update('hrms_staffmaster',$input)){
                $output = array('status' => 'success', 'message' => 'Staff Master Updated Succesfully');
                echo json_encode($output);
            }else{
                $output = array('status' => 'error', 'message' => 'Staff Master not Updated');
                echo json_encode($output);
            }

        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }
/*public function check_in(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        if(isset($input['emp_id']) && $input['emp_id'] != ''){
            $check_in = array();
            $check_in['emp_id'] = $input['emp_id'];
            $check_in['check_lat'] = $input['check_lat'];
            $check_in['check_long'] = $input['check_long'];
            $check_in['mode'] = 1;
            $check_in['status'] = 2;
            $check_in['check_time'] = date('Y-m-d H:i:s');
            if($this->db->insert('hrms_check_in',$check_in)){
                $checkin_id = $this->db->insert_id();
                $staffmaster = $this->db->get_where('hrms_staffmaster', array('id'=>$input['emp_id']))->result_array();
                if($staffmaster[0]['is_wfh']==1 && $staffmaster[0]['is_alarm']==1){
                    $start_office_time = date('Y-m-d H:i:s',strtotime($staffmaster[0]['start_office_time']));
                    $end_office_time = date('Y-m-d H:i:s',strtotime($staffmaster[0]['end_office_time']));
                    $count = $staffmaster[0]['alarm_count'];
                    $times = [date('Y-m-d 18:15:00'),date('Y-m-d 18:40:00'),date('Y-m-d 19:15:00'),date('Y-m-d 19:45:00')];
                    for($i=1;$i<=$count;$i++){
                        $int= mt_rand(strtotime($staffmaster[0]['start_office_time']),strtotime($staffmaster[0]['end_office_time']));
                        // $times[]= date('Y-m-d H:i:s',$int);
                    }
                    sort($times);
                    $output = array('status' => 'success', 'message' => 'User Checked In Succesfully',"checkin_id" => $checkin_id,"Time" => $times,"is_wfh"=>$staffmaster[0]['is_wfh'],"is_authorized"=>$staffmaster[0]['is_authorized'],"is_alarm"=>$staffmaster[0]['is_alarm'],"is_capturepic"=>$staffmaster[0]['is_capturepic'],"is_verifyotp"=>$staffmaster[0]['is_verifyotp']);
                    echo json_encode($output);
                    die;
                }
                $output = array('status' => 'success', 'message' => 'User Checked In Succesfully',"is_wfh"=>$staffmaster[0]['is_wfh'],"is_authorized"=>$staffmaster[0]['is_authorized'],"is_alarm"=>$staffmaster[0]['is_alarm'],"is_capturepic"=>$staffmaster[0]['is_capturepic'],"is_verifyotp"=>$staffmaster[0]['is_verifyotp']);
                echo json_encode($output);
            }else{
                $output = array('status' => 'error', 'message' => 'Check in not inserted');
                echo json_encode($output);
            }
        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
    }*/
    
    public function check_in(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($input['emp_id']) && $input['emp_id'] != ''){
            $checkin_data = $this->db->get_where('hrms_check_in', array('emp_id'=>$input['emp_id'],'check_time>'=>date('Y-m-d')))->result_array();
            $staffmaster = $this->db->get_where('hrms_staffmaster', array('id'=>$input['emp_id']))->result_array();
            if(empty($checkin_data)){
                $check_in = array();
                $check_in['emp_id'] = $input['emp_id'];
                $check_in['check_lat'] = $input['check_lat'];
                $check_in['check_long'] = $input['check_long'];
                $check_in['mode'] = 1;
                $check_in['status'] = 2;
                $check_in['check_time'] = date('Y-m-d H:i:s');
                if($this->db->insert('hrms_check_in',$check_in)){
                    $checkin_id = $this->db->insert_id();
                    
                    if($staffmaster[0]['is_wfh']==1 && $staffmaster[0]['is_alarm']==1){
                        $start_office_time = date('Y-m-d H:i:s',strtotime($staffmaster[0]['start_office_time']));
                        $end_office_time = date('Y-m-d H:i:s',strtotime($staffmaster[0]['end_office_time']));
                        if(date('Y-m-d H:i:s') > date('Y-m-d 13:00:00')){
                            // $staffmaster[0]['start_office_time'] = date('Y-m-d 15:i:s');
                            $count = $staffmaster[0]['alarm_count']/2;
                            $count = round($count);
                        }else{
                            $count = $staffmaster[0]['alarm_count'];
                        }
                        
                        // $times = [date('Y-m-d 18:40:00'),date('Y-m-d 19:15:00'),date('Y-m-d 19:40:00')];
                        $int_date = [];
                        $times_array =[];
                        for($i=1;$i<=$count;$i++){
                            $int= mt_rand(strtotime($staffmaster[0]['start_office_time']),strtotime($staffmaster[0]['end_office_time']));
                            $times['emp_id']= $input['emp_id'];
                            $times['int_date']= date('Y-m-d H:i:s',$int);
                            $int_date[] = date('Y-m-d H:i:s',$int);
                            $times_array[]=$times;
                            
                        }
                        $price = array_column($times_array, 'int_date');

                        array_multisort($price, SORT_ASC, $times_array);
                        $this->db->insert_batch('hrms_interval_time',$times_array);
                        sort($int_date);
                        $output = array('status' => 'success', 'message' => 'User Checked In Succesfully',"checkin_id" => $checkin_id,"Time" => $int_date,"is_wfh"=>$staffmaster[0]['is_wfh'],"is_authorized"=>$staffmaster[0]['is_authorized'],"is_alarm"=>$staffmaster[0]['is_alarm'],"is_capturepic"=>$staffmaster[0]['is_capturepic'],"is_verifyotp"=>$staffmaster[0]['is_verifyotp']);
                        echo json_encode($output);
                        die;
                    }
                    $output = array('status' => 'success', 'message' => 'User Checked In Succesfully',"is_wfh"=>$staffmaster[0]['is_wfh'],"is_authorized"=>$staffmaster[0]['is_authorized'],"is_alarm"=>$staffmaster[0]['is_alarm'],"is_capturepic"=>$staffmaster[0]['is_capturepic'],"is_verifyotp"=>$staffmaster[0]['is_verifyotp']);
                    echo json_encode($output);
                }else{
                    $output = array('status' => 'error', 'message' => 'Check in not inserted');
                    echo json_encode($output);
                }
            }else{
                $check_in = array();
                $check_in['emp_id'] = $input['emp_id'];
                $check_in['check_lat'] = $input['check_lat'];
                $check_in['check_long'] = $input['check_long'];
                $check_in['mode'] = 1;
                $check_in['status'] = 2;
                $check_in['check_time'] = date('Y-m-d H:i:s');
                if($this->db->insert('hrms_check_in',$check_in)){
                    $checkin_id = $this->db->insert_id();
                    $checkin_data = $this->db->get_where('hrms_interval_time', array('emp_id'=>$input['emp_id'],"int_date<"=>date('Y-m-d H:i:s'),"alarmtime"=>null))->result_array();
                    if($checkin_data){
                        $this->db->where('id',$checkin_data[0]['id']);
                        $this->db->update('hrms_interval_time',array('alarmtime'=>date('Y-m-d H:i:s'),"responsetime"=>date('Y-m-d H:i:s')));

                    }
                    $checkin_data = $this->db->get_where('hrms_interval_time', array('emp_id'=>$input['emp_id'],"int_date>"=>date('Y-m-d H:i:s'),"alarmtime"=>null))->result_array();
                    $int_date = array_column($checkin_data,'int_date');
                    $output = array('status' => 'success', 'message' => 'User Checked In Succesfully',"checkin_id" => $checkin_id,"Time" => $int_date,"is_wfh"=>$staffmaster[0]['is_wfh'],"is_authorized"=>$staffmaster[0]['is_authorized'],"is_alarm"=>$staffmaster[0]['is_alarm'],"is_capturepic"=>$staffmaster[0]['is_capturepic'],"is_verifyotp"=>$staffmaster[0]['is_verifyotp']);
                    echo json_encode($output);
                    // echo $this->db->last_query();
                    // echo "<pre>";
                    // print_r($checkin_data);
                    // die;
                }

            }
            
        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function check_out(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($input['emp_id']) && $input['emp_id'] != ''){
            $check_in = array();
            $check_in['emp_id'] = $input['emp_id'];
            $check_in['check_lat'] = $input['check_lat'];
            $check_in['check_long'] = $input['check_long'];
            $check_in['mode'] = 2;
            $check_in['status'] = 2;
            $check_in['check_time'] = date('Y-m-d H:i:s');
            if($this->db->insert('hrms_check_in',$check_in)){
                $checkout_id = $this->db->insert_id();
                $output = array('status' => 'success', 'message' => 'User Checked OUT Succesfully',"checkout_id" => $checkout_id);
                echo json_encode($output);
            }else{
                $output = array('status' => 'error', 'message' => 'Check OUT not inserted');
                echo json_encode($output);
            }
        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function alarm_off(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($input['emp_id']) && $input['emp_id'] != ''){
            $check_in = array();
            $check_in['emp_id'] = $input['emp_id'];
            $check_in['check_lat'] = $input['check_lat'];
            $check_in['check_long'] = $input['check_long'];
            $check_in['mode'] = 3;
            $check_in['status'] = 1;
            $check_in['check_time'] = date('Y-m-d H:i:s');
            $check_in['alaramstarttime'] = $input['alaramstarttime'];
            $check_in['capturelink'] = $input['capturelink'];
            if($this->db->insert('hrms_check_in',$check_in)){
                $this->db->where('emp_id',$input['emp_id']);
                $this->db->where('int_date',$input['alaramstarttime']);
                $this->db->update('hrms_interval_time',array('alarmtime'=>$input['alaramstarttime'],"responsetime"=>date('Y-m-d H:i:s')));
                $output = array('status' => 'success', 'message' => 'User Alarm off Succesfully');
                echo json_encode($output);
            }else{
                $output = array('status' => 'error', 'message' => 'Alarm off not inserted');
                echo json_encode($output);
            }
        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    //otp generate
    public function otp_generate(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($input['emp_code']) && $input['emp_code'] != ''){
            $staff_id = $this->db->get_where('hrms_staffmaster', array('emp_code' => $input['emp_code']))->row('id');

            
            $user = array();
            $user['otp']=rand(1000, 9999);
            $user['otp_generate_date'] = date('Y-m-d H:i:s');
            $user['otp_expiry_date'] = date('Y-m-d H:i:s',strtotime('+1 hour'));
            $this->db->where('emp_id',$staff_id);
            if($this->db->update('hrms_users',$user)){
                $output = array('status' => 'success', 'message' => 'OTP generated Succesfully','OTP'=>$user['otp']);
                echo json_encode($output);
            }else{
                $output = array('status' => 'error', 'message' => 'OTP not Generated');
                echo json_encode($output);
            }
        }else{
            $output = array('status' => 'error', 'message' => 'Enter Employee code');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    //otp verify
    public function otp_verify(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($input['emp_code']) && $input['emp_code'] != '' && isset($input['otp']) && $input['otp'] != '' && isset($input['checkin_id']) && $input['checkin_id'] != ''){
            $staff_id = $this->db->get_where('hrms_staffmaster', array('emp_code' => $input['emp_code']))->row('id');
            $this->db->select('id');
            $this->db->from('hrms_users');
            $this->db->where('emp_id',$staff_id);
            $this->db->where('otp',$input['otp']);
            $this->db->where('otp_expiry_date>=',date('Y-m-d H:i:s'));
            $query = $this->db->get();
            if ($query->num_rows() > 0) { 
                $users = $query->result_array();
                $checkin = array();
                $checkin['capturelink'] = $input['capturelink'];
                $checkin['status'] = 1;
                $this->db->where('emp_id',$staff_id);
                $this->db->where('id',$input['checkin_id']);
                if($this->db->update('hrms_check_in',$checkin)){
                    $output = array('status' => 'success', 'message' => 'OTP Verified Succesfully');
                    echo json_encode($output);
                }

            }else{
                $output = array('status' => 'error', 'message' => 'OTP Expired');
                echo json_encode($output);
            }
        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    //hikelineup
    public function add_hike(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($input['emp_id']) && $input['emp_id'] != ''){
            $hike_history = $this->db->get_where('hrms_hike_history', array('emp_id' => $input['emp_id']))->row();
            if($hike_history){
                $hike = [];
                $hike['emp_id'] = $input['emp_id'];
                $hike['hikedate'] = date('y-m-d H:i:s',strtotime($input['hikedate']));
                $hike['nhikedate'] = date('y-m-d H:i:s',strtotime($input['nhikedate']));
                if($this->db->insert('hrms_hike_history',$input)){
                    $staff['basicsalary'] = $input['hiked_salary'];
                    $staff['updatedon'] = date('Y-m-d H:i:s');
                    $this->db->where('id',$input['emp_id']);
                    if($this->db->update('hrms_staffmaster',$staff)){
                        $output = array('status' => 'success', 'message' => 'Hike added Succesfully');
                        echo json_encode($output);
                    }
                }
            }else{
                $staffmaster = $this->db->get_where('hrms_staffmaster', array('id' => $input['emp_id']))->row('basicsalary');
                $hikes= array();
                $hikes['emp_id'] = $input['emp_id'];
                $hikes['nhikedate'] = date('Y-m-d H:i:s',strtotime($input['hikedate']));
                $hikes['nhike_per'] = $input['hike_per'];
                $hikes['nhikeamount'] = $input['hikeamount'];
                $hikes['current_salary'] = $staffmaster['basicsalary'];
                $hikes['hiked_salary'] = $staffmaster['basicsalary']+$input['hikeamount'];
                $hikes['notes'] = (isset($hike['notes']))?$hike['notes']:null;
                if($this->db->insert('hrms_hike_history',$input)){
                    $output = array('status' => 'success', 'message' => 'Hike added Succesfully');
                    echo json_encode($output);
                }
            }
            
        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    //commitment list
    public function hike_commitment(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
            $this->db->select('hrms_staffmaster.biometricAccess,hrms_hike_history.id,hrms_hike_history.nhike_tenure,hrms_hike_history.nhikedate,hrms_hike_history.nhikeamount,hrms_hike_history.nhike_per,hrms_staffmaster.name,hrms_staffmaster.emp_code,hrms_branch.name as branch_name,hrms_company.name as company_name,hrms_designation.name as designation_name,hrms_department.name as department_name,hrms_staffmaster.branch_id,hrms_staffmaster.company_id,hrms_staffmaster.department_id,hrms_staffmaster.designation_id,hrms_staffmaster.id as emp_id');
            $this->db->from('hrms_hike_history');
            $this->db->join('hrms_staffmaster', 'hrms_staffmaster.id = hrms_hike_history.emp_id');
            $this->db->join('hrms_branch', 'hrms_branch.id = hrms_staffmaster.branch_id');
            $this->db->join('hrms_company', 'hrms_company.id = hrms_staffmaster.company_id');
            $this->db->join('hrms_department','hrms_department.id=hrms_staffmaster.department_id','left');
            $this->db->join('hrms_designation','hrms_designation.id=hrms_staffmaster.designation_id','left');
            if (isset($input['emp_id']) && $input['emp_id'] != '') {
                  $this->db->where('hrms_hike_history.emp_id', $input['emp_id']);
            }
            $this->db->where('hrms_hike_history.status',1);
            $this->db->order_by('hrms_hike_history.id','desc');
            $this->db->group_by('hrms_hike_history.emp_id');
            $query = $this->db->get();
            if ($query->num_rows() > 0) { 
                $result = $query->result_array();
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Hike Commitment List', 'data' => $result);
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'No data found');
                    echo json_encode($output);
                }
            }else{
                $output = array('status' => 'error', 'message' => 'No data found');
                echo json_encode($output);
            }
        	}else{
        	   echo json_encode($response); 
        	}
        }
        // }else{
        //     $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
        //     echo json_encode($output);
        // }
    }

    //Processed list
    public function hike_processed(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        // if(isset($input['emp_id']) && $input['emp_id'] != ''){
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
            $this->db->select('hrms_staffmaster.biometricAccess,hrms_hike_history.id,hrms_hike_history.hikedate,hrms_hike_history.hikeamount,hrms_hike_history.hike_per,hrms_hike_history.current_salary,hrms_hike_history.hiked_salary,hrms_hike_history.notes,hrms_staffmaster.name,hrms_staffmaster.emp_code,hrms_branch.name as branch_name,hrms_company.name as company_name,hrms_designation.name as designation_name,hrms_department.name as department_name,hrms_staffmaster.branch_id,hrms_staffmaster.company_id,hrms_staffmaster.department_id,hrms_staffmaster.designation_id,hrms_staffmaster.id as emp_id');
            $this->db->from('hrms_hike_history');
            $this->db->join('hrms_staffmaster', 'hrms_staffmaster.id = hrms_hike_history.emp_id');
            $this->db->join('hrms_branch', 'hrms_branch.id = hrms_staffmaster.branch_id');
            $this->db->join('hrms_company', 'hrms_company.id = hrms_staffmaster.company_id');
            $this->db->join('hrms_department','hrms_department.id=hrms_staffmaster.department_id','left');
            $this->db->join('hrms_designation','hrms_designation.id=hrms_staffmaster.designation_id','left');
            if (isset($input['emp_id']) && $input['emp_id'] != '') {
                $this->db->where('hrms_hike_history.emp_id', $input['emp_id']);
            }
            $this->db->where('hrms_hike_history.status',1);
            $query = $this->db->get();
            if ($query->num_rows() > 0) { 
                $result = $query->result_array();
                if ($result) {
                    $output = array('status' => 'success', 'message' => 'Hike Processed List', 'data' => $result);
                    echo json_encode($output);
                } else {
                    $output = array('status' => 'error', 'message' => 'No data found');
                    echo json_encode($output);
                }
            }else{
                $output = array('status' => 'error', 'message' => 'No data found');
                echo json_encode($output);
            }
    }else{
        	   echo json_encode($response); 
        	}
        }
        // }else{
        //     $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
        //     echo json_encode($output);
        // }
    }

    public function hike_delete(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($input['id']) && $input['id'] != ''){
            $this->db->where('id',$input['id']);
            $hike_history['status'] = 3;
            $hike_history['updatedon'] = date('Y-m-d H:i:s');
            if($this->db->update('hrms_hike_history',$hike_history)){
                $output = array('status' => 'success', 'message' => 'Hike Deleted Succesfully');
                echo json_encode($output);
            }else{
                $output = array('status' => 'error', 'message' => 'Hike Not Deleted');
                echo json_encode($output);
            }
        }else{

        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }

    //update staff notice
    public function update_staff_notice(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($input['emp_id']) && $input['emp_id'] != ''){
            $staff['is_notice'] = ($input['is_notice'] == 'Yes')?1:0;
            $staff['notice_issue_date'] = date('Y-m-d H:i:s',strtotime($input['notice_issue_date']));
            $staff['notice_end_date'] = date('Y-m-d H:i:s',strtotime($input['notice_end_date']));
            $staff['noticeissued_by'] = $input['noticeissued_by'];
            $this->db->where('id',$input['emp_id']);
            if($this->db->update('hrms_staffmaster',$staff)){
                $output = array('status' => 'success', 'message' => 'Staff updated Succesfully');
                echo json_encode($output);
            }else{
                $output = array('status' => 'error', 'message' => 'Staff Not updated');
                echo json_encode($output);
            }
        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    //update staff notice approvl
    public function update_staff_notice_approval(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($input['emp_id']) && $input['emp_id'] != ''){
            $staff['noticeapproved_by'] = $input['noticeapproved_by'];
            $staff['noticeapproved_on'] = date('Y-m-d H:i:s',strtotime($input['noticeapproved_on']));
            $this->db->where('id',$input['emp_id']);
            if($this->db->update('hrms_staffmaster',$staff)){
                $output = array('status' => 'success', 'message' => 'Staff updated Succesfully');
                echo json_encode($output);
            }else{
                $output = array('status' => 'error', 'message' => 'Staff Not updated');
                echo json_encode($output);
            }
        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    //Salary Report
    public function salary_report(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        // if($input){
$check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
            $this->db->select('hrms_company.name as company_name,hrms_staffmaster.name as empname,hrms_staffmaster.emp_code,hrms_designation.name as designation_name,hrms_department.name as department_name,hrms_staffmaster.profileimage,hrms_staffmaster.mobno,hrms_staffmaster.gender,hrms_staffmaster.hometown,hrms_staffmaster.area,hrms_staffmaster.settled_in,hrms_staffmaster.basicsalary,hrms_branch.name as branch_name');
            $this->db->from('hrms_staffmaster');
            $this->db->join('hrms_branch', 'hrms_branch.id = hrms_staffmaster.branch_id');
            $this->db->join('hrms_company', 'hrms_company.id = hrms_staffmaster.company_id');
            $this->db->join('hrms_department','hrms_department.id=hrms_staffmaster.department_id','left');
            $this->db->join('hrms_designation','hrms_designation.id=hrms_staffmaster.designation_id','left');
            $this->db->where('hrms_staffmaster.status', 1);
            if($input['company_id']){
                $this->db->where_in('hrms_staffmaster.company_id', $input['company_id']);
            }
            if($input['emp_code']){
                $this->db->where_in('hrms_staffmaster.emp_code', $input['emp_code']);
            }
            if($input['branch_id']){
                $this->db->where_in('hrms_staffmaster.branch_id', $input['branch_id']);
            }
            if($input['department_id']){
                $this->db->where_in('hrms_staffmaster.department_id', $input['department_id']);
            }
            if($input['designation_id']){
                $this->db->where_in('hrms_staffmaster.designation_id', $input['designation_id']);
            }
            if($input['salary']){
                $salary = explode('-',$input['salary']);
                $from_salary = preg_replace('/[^A-Za-z0-9\-]/', '', $salary[0]);
                $to_salary = preg_replace('/[^A-Za-z0-9\-]/', '', $salary[1]);
                $this->db->where_in('hrms_staffmaster.>=', $from_salary);
                $this->db->where_in('hrms_staffmaster.basicsalary<=', $to_salary);
            }
            $query = $this->db->get();
            if ($query->num_rows() > 0) { 
                $data = $query->result_array();
            }
            if($data){
                $output = array('status' => 'success', 'message' => 'Staff List', 'data' => $data);
                echo json_encode($output);
            }else{
                $output = array('status' => 'error', 'message' => 'No data found');
                echo json_encode($output);
            }
        	}else{
        	   echo json_encode($response); 
        	}
        }

        // }else{
        //     $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
        //     echo json_encode($output);
        // }
    }

    //Salary Report
    public function hike_report(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        // if($input){
$check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
            $this->db->select('hrms_company.name as company_name,hrms_staffmaster.name as empname,hrms_staffmaster.emp_code,hrms_designation.name as designation_name,hrms_department.name as department_name,hrms_staffmaster.profileimage,hrms_staffmaster.mobno,hrms_staffmaster.gender,hrms_staffmaster.hometown,hrms_staffmaster.area,hrms_staffmaster.settled_in,hrms_staffmaster.basicsalary,hrms_branch.name as branch_name,hrms_hike_history.hikedate,hrms_hike_history.hikeamount,hrms_hike_history.hike_per,hrms_hike_history.hike_tenure,hrms_hike_history.nhikedate,hrms_hike_history.nhikeamount,hrms_hike_history.nhike_per');
            $this->db->from('hrms_staffmaster');
            $this->db->join('hrms_branch', 'hrms_branch.id = hrms_staffmaster.branch_id');
            $this->db->join('hrms_hike_history', 'hrms_hike_history.emp_id = hrms_staffmaster.id');
            $this->db->join('hrms_company', 'hrms_company.id = hrms_staffmaster.company_id');
            $this->db->join('hrms_department','hrms_department.id=hrms_staffmaster.department_id','left');
            $this->db->join('hrms_designation','hrms_designation.id=hrms_staffmaster.designation_id','left');
            $this->db->where('hrms_staffmaster.status', 1);
            if($input['company_id']){
                $this->db->where_in('hrms_staffmaster.company_id', $input['company_id']);
            }
            if($input['emp_code']){
                $this->db->where_in('hrms_staffmaster.emp_code', $input['emp_code']);
            }
            if($input['branch_id']){
                $this->db->where_in('hrms_staffmaster.branch_id', $input['branch_id']);
            }
            if($input['department_id']){
                $this->db->where_in('hrms_staffmaster.department_id', $input['department_id']);
            }
            if($input['designation_id']){
                $this->db->where_in('hrms_staffmaster.designation_id', $input['designation_id']);
            }
            if($input['from_date']){
                $this->db->where_in('hrms_hike_history.nhikedate>=', date('Y-m-d',strtotime($input['from_date'])));
            }
            if($input['to_date']){
                $this->db->where_in('hrms_hike_history.nhikedate<=', date('Y-m-d',strtotime($input['to_date'])));
            }
            
            $query = $this->db->get();
            if ($query->num_rows() > 0) { 
                $data = $query->result_array();
            }
            if($data){
                $output = array('status' => 'success', 'message' => 'Staff List', 'data' => $data);
                echo json_encode($output);
            }else{
                $output = array('status' => 'error', 'message' => 'No data found');
                echo json_encode($output);
            }
        	}else{
        	   echo json_encode($response); 
        	}
        }

        // }else{
        //     $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
        //     echo json_encode($output);
        // }
    }

    //Incentive
    public function get_incentive(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $this->db->select('hrms_staffmaster.biometricAccess,hrms_company.name as company_name,hrms_staffmaster.name as empname,hrms_staffmaster.emp_code,hrms_designation.name as designation_name,hrms_department.name as department_name,hrms_staffmaster.profileimage,hrms_staffmaster.mobno,hrms_staffmaster.gender,hrms_staffmaster.hometown,hrms_staffmaster.area,hrms_staffmaster.settled_in,hrms_staffmaster.basicsalary,hrms_branch.name as branch_name,hrms_staffmaster.branch_id,hrms_staffmaster.company_id,hrms_staffmaster.department_id,hrms_staffmaster.designation_id,hrms_staffmaster.id as emp_id,hrms_incentive.*');
        $this->db->from('hrms_incentive');
        $this->db->join('hrms_staffmaster', 'hrms_staffmaster.id = hrms_incentive.emp_id');
        $this->db->join('hrms_branch', 'hrms_branch.id = hrms_staffmaster.branch_id');
        $this->db->join('hrms_company', 'hrms_company.id = hrms_staffmaster.company_id');
        $this->db->join('hrms_department','hrms_department.id=hrms_staffmaster.department_id','left');
        $this->db->join('hrms_designation','hrms_designation.id=hrms_staffmaster.designation_id','left');
        $this->db->where('hrms_staffmaster.status', 1);
        if(isset($input['id'])){
            $this->db->where_in('hrms_incentive.id', $input['id']);
        }
        if(isset($input['emp_id'])){
            $this->db->where_in('hrms_incentive.emp_id', $input['emp_id']);
        }
        
        $query = $this->db->get();
        $data = array();
        if ($query->num_rows() > 0) { 
            $data = $query->result_array();
        }
        if($data){
            $output = array('status' => 'success', 'message' => 'Incentive List', 'data' => $data);
            echo json_encode($output);
        }else{
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }

    }
    
    // Add incentive
    public function add_incentive(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($input['emp_id']) && $input['emp_id'] != ''){
            if($this->db->insert('hrms_incentive',$input)){
                $output = array('status' => 'success', 'message' => 'Incentive added Succesfully');
                echo json_encode($output);
            }else{
                $output = array('status' => 'error', 'message' => 'Incentive not Added');
                echo json_encode($output);
            }

        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    // Add incentive
    public function update_incentive(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($input['id']) && $input['id'] != ''){
            $input['updatedon'] = date('Y-m-d H:i:s');
            $this->db->where('id',$input['id']);
            if($this->db->update('hrms_incentive',$input)){
                $output = array('status' => 'success', 'message' => 'Incentive Updated Succesfully');
                echo json_encode($output);
            }else{
                $output = array('status' => 'error', 'message' => 'Incentive not Updated');
                echo json_encode($output);
            }

        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    // Add incentive
    public function delete_incentive(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($input['id']) && $input['id'] != ''){
            $input['updatedon'] = date('Y-m-d H:i:s');
            $this->db->where('id',$input['id']);
            if($this->db->update('hrms_incentive',$input)){
                $output = array('status' => 'success', 'message' => 'Incentive Deleted Succesfully');
                echo json_encode($output);
            }else{
                $output = array('status' => 'error', 'message' => 'Incentive not Deleted');
                echo json_encode($output);
            }

        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function staff_import()
    {
        $this->load->library('excel');
        $duplicate_datas = [];

        $product_not_avail  = [];
        $batch_not_avail    = [];

        $duplicate_list     = [];
        
        // Upload Function ## Parameter $folder_name
        $config['upload_path']          = './attachments/staffmasters/';
        $config['allowed_types']        = 'xlsx|xls|pdf|docx';
        $config['max_size']             = 10000; 

        $this->load->library('upload', $config);

        if ( ! $this->upload->do_upload('upload_files'))
        {
                    $error = array('error' => $this->upload->display_errors());
                    $file_name = '';
                    $file = $error;                     
        }
        else
        {
                $data = array('upload_data' => $this->upload->data());
                $upload_data = $this->upload->data(); 
                $file_name =   $upload_data['file_name'];                        
                $file = $file_name;
        }
        // File Path
        $inputFileName = FCPATH . "attachments/staffmasters/".$file;
        
        
        // Process
        try {
            $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch (Exception $e) {
            die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME)
                    . '": ' . $e->getMessage());
        }
        $allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
        
        $arrayCount = count($allDataInSheet);
        $flag = 0;
        $createArray = array(   
            'company',
            'branch',
            'name',
            'dob',
            'age',
            'mobileno',
            'mailid',
            'maritalstatus',
            'gender',
            'hometown',
            'area',
            'settledin',
            'paddress',
            'taddress',
            'empcode',
            'department',
            'designation',
            'doj',
            'pwd',
            'salary',
            'trainingbranch',
            'joiningbranch',
            'atttype',
            'starttime',
            'endtime',
            'durationhrs',
            'wfh',
            'is_authorized',
            'is_alarm',
            'is_capturepic',
            'is_verifyotp',
            'alarm_count',
            'status'
        );
        $makeArray = array(
            'company'                    => 'company',
            'branch'                       => 'branch',
            'name'                       => 'name',
            'dob'                       => 'dob',
            'age'                          => 'age',
            'mobileno'                         => 'mobileno',
            'mailid'                          => 'mailid',
            'maritalstatus'                          => 'maritalstatus',
            'gender'                     => 'gender',
            'hometown'                        => 'hometown',
            'area'                     => 'area',
            'settledin'                     => 'settledin',
            'paddress'                   => 'paddress',
            'taddress'                        => 'taddress',
            'empcode'                          => 'empcode',
            'department'                        => 'department',
            'designation'                        => 'designation',
            'doj'                         => 'doj',
            'pwd'                           => 'pwd',
            'salary'                            => 'salary',
            'trainingbranch'                         => 'trainingbranch',
            'joiningbranch'                        => 'joiningbranch',
            'atttype'                       => 'atttype',
            'starttime'                        => 'starttime',
            'endtime'                        => 'endtime',
            'durationhrs'                         => 'durationhrs',
            'wfh'                           => 'wfh',
            'is_authorized'                            => 'is_authorized',
            'is_alarm'                         => 'is_alarm',
            'is_capturepic'                        => 'is_capturepic',
            'is_verifyotp'                       => 'is_verifyotp',
            'alarm_count'                             => 'alarm_count',
            'status'                     => 'status'        
        );
        
        $SheetDataKey = array();
        foreach ($allDataInSheet as $dataInSheet) {
            foreach ($dataInSheet as $key => $value) {
                if (in_array(trim($value), $createArray)) {
                    $value = preg_replace('/\s+/', '', $value);
                    $SheetDataKey[trim($value)] = $key;
                } else {
                    
                    
                }
            }
        }
        $data = array_diff_key($makeArray, $SheetDataKey);

        if (empty($data)) {
            $flag = 1;
        }
            // var_dump($flag);

        if ($flag == 1) {
            $company_id = $this->get_company_id(filter_var(trim($allDataInSheet['2'][$SheetDataKey['company']]), FILTER_SANITIZE_STRING));
           
            $final_data = [];
            $not_add = [];
            for ($i = 2; $i <= $arrayCount; $i++) {
                if(filter_var(trim($allDataInSheet[$i][$SheetDataKey['name']]), FILTER_SANITIZE_STRING) != '' && filter_var(trim($allDataInSheet[$i][$SheetDataKey['company']]), FILTER_SANITIZE_STRING) != '' && filter_var(trim($allDataInSheet[$i][$SheetDataKey['branch']]), FILTER_SANITIZE_STRING) != ''){
                $Data[$i]  = array(
                    'emp_code'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['empcode']]), FILTER_SANITIZE_STRING),
                    'company'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['company']]), FILTER_SANITIZE_STRING),
                    'branch'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['branch']]), FILTER_SANITIZE_STRING),
                    'name'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['name']]), FILTER_SANITIZE_STRING),
                    'dob'    => $allDataInSheet[$i][$SheetDataKey['dob']],
                    'age'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['age']]), FILTER_SANITIZE_STRING),
                    'mobileno'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['mobileno']]), FILTER_SANITIZE_STRING),
                    'mailid'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['mailid']]), FILTER_SANITIZE_STRING),
                    'maritalstatus'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['maritalstatus']]), FILTER_SANITIZE_STRING),
                    'gender'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['gender']]), FILTER_SANITIZE_STRING),
                    'hometown'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['hometown']]), FILTER_SANITIZE_STRING),
                    'area'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['area']]), FILTER_SANITIZE_STRING),
                    'settledin'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['settledin']]), FILTER_SANITIZE_STRING),
                    'paddress'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['paddress']]), FILTER_SANITIZE_STRING),
                    'taddress'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['taddress']]), FILTER_SANITIZE_STRING),
                    'empcode'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['empcode']]), FILTER_SANITIZE_STRING),
                    'department'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['department']]), FILTER_SANITIZE_STRING),
                    'designation'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['designation']]), FILTER_SANITIZE_STRING),
                    'doj'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['doj']]), FILTER_SANITIZE_STRING),
                    'pwd'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['pwd']]), FILTER_SANITIZE_STRING),
                    'salary'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['salary']]), FILTER_SANITIZE_STRING),
                    'trainingbranch'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['trainingbranch']]), FILTER_SANITIZE_STRING),
                    'joiningbranch'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['joiningbranch']]), FILTER_SANITIZE_STRING),
                    'atttype'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['atttype']]), FILTER_SANITIZE_STRING),
                    'starttime'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['starttime']]), FILTER_SANITIZE_STRING),
                    'endtime'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['endtime']]), FILTER_SANITIZE_STRING),
                    'durationhrs'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['durationhrs']]), FILTER_SANITIZE_STRING),
                    'wfh'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['wfh']]), FILTER_SANITIZE_STRING),
                    'is_authorized'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['is_authorized']]), FILTER_SANITIZE_STRING),
                    'is_alarm'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['is_alarm']]), FILTER_SANITIZE_STRING),
                    'is_capturepic'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['is_capturepic']]), FILTER_SANITIZE_STRING),
                    'is_verifyotp'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['is_verifyotp']]), FILTER_SANITIZE_STRING),
                    'alarm_count'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['alarm_count']]), FILTER_SANITIZE_STRING),
                    'status'    => filter_var(trim($allDataInSheet[$i][$SheetDataKey['status']]), FILTER_SANITIZE_STRING)
                );

                    $fetchData[$i]['company_id']                = $company_id = $this->get_company_id($Data[$i]['company']);
                    $fetchData[$i]['branch_id']           = $branch_id = $this->get_branch_id($Data[$i]['branch'],$company_id);

                    $fetchData[$i]['emp_code']               = $Data[$i]['emp_code'];                            

                    $fetchData[$i]['name']               = $Data[$i]['name'];      
                    $fetchData[$i]['dob']              = date("Y-m-d", strtotime($Data[$i]['dob']));     
                    $fetchData[$i]['mobno']               = $Data[$i]['mobileno'];      
                    $fetchData[$i]['mail_id']               = $Data[$i]['mailid'];      
                    $fetchData[$i]['maritalstatus']          = $Data[$i]['maritalstatus'];      
                    $fetchData[$i]['age']             = $Data[$i]['age'];      

                    $fetchData[$i]['gender']          = $Data[$i]['gender'];         
                    $fetchData[$i]['hometown']          = $Data[$i]['hometown'];      
                    $fetchData[$i]['area']             = $Data[$i]['area'];      
                    $fetchData[$i]['settled_in']               = $Data[$i]['settledin'];      
                    $fetchData[$i]['paddress']      = $Data[$i]['paddress'];      

                    $fetchData[$i]['taddress']                = $Data[$i]['taddress'];      
                    $fetchData[$i]['department_id']                  = $department_id = $this->get_department_id($Data[$i]['department'],$company_id);   
                    $fetchData[$i]['designation_id']                   = $designation_id = $this->get_designation_id($Data[$i]['designation'],$department_id,$company_id);   

                         
                    $fetchData[$i]['pwd']                   = $Data[$i]['pwd'];      
                    $fetchData[$i]['basicsalary']                    = $Data[$i]['salary'];      

                    $fetchData[$i]['doj']                       = date("Y-m-d", strtotime($Data[$i]['doj']));
                    $fetchData[$i]['trainingbranch']                 = $this->get_branch_id($Data[$i]['trainingbranch']);
                    $fetchData[$i]['joiningbranch']                = $this->get_branch_id($Data[$i]['joiningbranch']);
                    $fetchData[$i]['atttype']                = $Data[$i]['atttype'];
                    $fetchData[$i]['start_office_time']              = date("H:i:s", strtotime($Data[$i]['starttime']));

                    $fetchData[$i]['end_office_time']     = date("H:i:s", strtotime($Data[$i]['endtime']));
                    $fetchData[$i]['is_wfh']     = ($Data[$i]['wfh']=='TRUE')?1:0;
                    $fetchData[$i]['is_authorized']     = ($Data[$i]['is_authorized']=='TRUE')?1:0;
                    $fetchData[$i]['is_alarm']     = ($Data[$i]['is_alarm']=='YES')?1:0;
                    $fetchData[$i]['is_capturepic']     = ($Data[$i]['is_capturepic']=='YES')?1:0;
                    $fetchData[$i]['is_verifyotp']     = ($Data[$i]['is_verifyotp']=='YES')?1:0;
                    $fetchData[$i]['alarm_count']     = ($Data[$i]['alarm_count'])?$Data[$i]['alarm_count']:0;
                    $fetchData[$i]['status']     = ($Data[$i]['status'])?$Data[$i]['status']:1;
                    $fetchData[$i]['jobstatus']     = ($Data[$i]['status'])?$Data[$i]['status']:1;
                    $fetchData[$i]['pwd'] = '12345';
                    $fetchData[$i]['biometricAccess'] = $Data[$i]['emp_code'];
                    // echo "<pre>";
                    // print_r($fetchData[$i]);
                    // die;
                    // echo $i;
                    if($fetchData[$i]['company_id'] && $fetchData[$i]['branch_id'] && $fetchData[$i]['name'] && $fetchData[$i]['emp_code']){
                        $final_data[]=$fetchData[$i];
                    }else{
                        
                    }     
                }   
            } 
            if(count($final_data) == ($arrayCount-1)){
                $this->load->library('email');
                foreach($final_data as $key =>$val){
                    $final_data[$key]['onboard_status'] = 0;
                    if($this->db->insert('hrms_staffmaster', $final_data[$key])){

                    }else{
                        $final_data[$key]['last_query'] = $this->db->last_query();
                        $not_add[]=$final_data[$key];
                    }
                    // echo $this->db->last_query();
                    // die;
                    $staffmaster_id = $this->db->insert_id();
                    $enc_id = $this->encrypt->encode($staffmaster_id);
                    $url = 'https://hrm.zerogravitygroups.com/#/onboarding';
                    $vendor_update['onboard_link'] = $link = $url."/?id=".$enc_id."";
                    $this->db->where('id',$staffmaster_id);
                    $this->db->update('hrms_staffmaster',$vendor_update);
                    // $this->email->set_mailtype("html");
                    // $this->email->from('zg@gmail.com', 'ZG'); 
                    // $this->email->to($final_data[$key]['mail_id']);
                    // $this->email->subject('Welcome'); 
                    // $html = "Dear ". $final_data[$key]['name']."<br>";
                    // $html .= "Your Onboard Link <a href='".$link."'>click Here</a><br>";
                    // $html .= "Thanks From ZG";
                    // $this->email->message($html); 

                    // //Send mail 
                    // $this->email->send(); 
                }
                // $this->db->insert_batch('hrms_staffmaster', $final_data); 
                $output = array('status' => 'success', 'message' => 'Staff Added Succesfully',"Not added data"=>$not_add);
                echo json_encode($output);
            }else{
                $output = array('status' => 'success', 'message' => 'Staff Not Added',"Not added data"=>$not_add);
                echo json_encode($output);
            } 
        }else {
            echo "Please import correct file";
        }
    }

    public function get_company_id($company_name)
    {
        $result_data = $this->db->get_where('hrms_company', array('name' => $company_name))->row();
        if($result_data){
            return $result_data->id;
        }            
        return null;
    }
    
    public function get_branch_id($branch_name,$company_id = null)
    {
        if($company_id != null)
            $result_data = $this->db->get_where('hrms_branch', array('name' => $branch_name,"company_id" => $company_id))->row();
        else
            $result_data = $this->db->get_where('hrms_branch', array('name' => $branch_name))->row();
        if($result_data){
            return $result_data->id;
        }            
        return null;
    }

    public function get_department_id($department_name,$company_id = null)
    {
        if($company_id != null)
            $result_data = $this->db->get_where('hrms_department', array('name' => $department_name,"company_id" => $company_id))->row();
        else
            $result_data = $this->db->get_where('hrms_department', array('name' => $department_name))->row();
        if($result_data){
            return $result_data->id;
        }            
        return null;
    }

    public function get_designation_id($designation_name,$department_id = null,$company_id = null)
    {
        $result_data = $this->db->get_where('hrms_designation', array('name' => $designation_name,"departmentid"=>$department_id,"company_id" => $company_id))->row();
        if($result_data){
            return $result_data->id;
        }            
        return null;
    }

    // Notice 
    public function notice(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $this->db->select('hrms_notice.*');
        $this->db->select('hrms_staffmaster.biometricAccess,hrms_staffmaster.name,hrms_staffmaster.emp_code,hrms_branch.name as branch_name,hrms_company.name as company_name,hrms_designation.name as designation_name,hrms_department.name as department_name,hrms_staffmaster.branch_id,hrms_staffmaster.company_id,hrms_staffmaster.department_id,hrms_staffmaster.designation_id,hrms_staffmaster.id as emp_id');
        $this->db->join('hrms_staffmaster', 'hrms_staffmaster.id = hrms_notice.emp_id');
        $this->db->join('hrms_branch', 'hrms_branch.id = hrms_staffmaster.branch_id');
        $this->db->join('hrms_company', 'hrms_company.id = hrms_staffmaster.company_id');
        $this->db->join('hrms_department','hrms_department.id=hrms_staffmaster.department_id','left');
        $this->db->join('hrms_designation','hrms_designation.id=hrms_staffmaster.designation_id','left');
        $this->db->from('hrms_notice');
        $this->db->where('hrms_notice.status!=',3);
        $query = $this->db->get();
        $data = array();
        if ($query->num_rows() > 0) { 
            $data = $query->result_array();
        }
        if($data){
            $output = array('status' => 'success', 'message' => 'Notice List', 'data' => $data);
            echo json_encode($output);
        }else{
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
    }else{
        	   echo json_encode($response); 
        	}
        }
    }
    public function get_notice(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $this->db->select('hrms_notice.*');
       
        $this->db->from('hrms_notice');
        if(isset($input['id']) && $input['id'] != ''){
            $this->db->where('hrms_notice.id',$input['id']);
        }
        if(isset($input['emp_id']) && $input['emp_id'] != ''){
            $this->db->where('hrms_notice.emp_id',$input['emp_id']);
        }
        $this->db->where('hrms_notice.status!=',3);
        $query = $this->db->get();
        $data = array();
        if ($query->num_rows() > 0) { 
            $data = $query->result_array();
        }
        if($data){
            $output = array('status' => 'success', 'message' => 'Notice List', 'data' => $data);
            echo json_encode($output);
        }else{
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function add_notice(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($input['emp_id']) && $input['emp_id'] != ''){
            $notice = $input;
            $this->db->select('*');
            $this->db->from('hrms_notice');
            $query = $this->db->get();        
            if ($query->num_rows() > 0) {
                $staff = $query->result_array();
            }else{
                $staff = [];
            }
        
                $prefix_number = digits_set(count($staff) + 1);
            $notice['reference_no'] = $prefix_number;
            $notice['resigantion_date'] = date('Y-m-d',strtotime($notice['resigantion_date']));
            $notice['lastworkingdate'] = date('Y-m-d',strtotime($notice['lastworkingdate']));
            $notice['notice_status'] = "Pending";
            if($this->db->insert('hrms_notice',$notice)){
                $output = array('status' => 'success', 'message' => 'Notice Inserted Successfully');
                echo json_encode($output);
            }else{
                $output = array('status' => 'error', 'message' => 'Notice Not inserted');
                echo json_encode($output);
            }
        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function update_notice(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($input['emp_id']) && $input['emp_id'] != '' && isset($input['id']) && $input['id'] != ''){
            $notice = $input;
            $notice['resigantion_date'] = date('Y-m-d',strtotime($notice['resigantion_date']));
            $notice['lastworkingdate'] = date('Y-m-d',strtotime($notice['lastworkingdate']));
            $notice['updatedon'] = date('Y-m-d H:i:s');
            $this->db->where('id',$notice['id']);
            if($this->db->update('hrms_notice',$notice)){
                $output = array('status' => 'success', 'message' => 'Notice Updated Successfully');
                echo json_encode($output);
            }else{
                $output = array('status' => 'error', 'message' => 'Notice Not Updated');
                echo json_encode($output);
            }
        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function delete_notice(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($input['id']) && $input['id'] != ''){
            $notice = $input;
            $notice['status'] = 3;
            $notice['updatedon'] = date('Y-m-d H:i:s');
            $this->db->where('id',$notice['id']);
            if($this->db->update('hrms_notice',$notice)){
                $output = array('status' => 'success', 'message' => 'Notice Deleted Successfully');
                echo json_encode($output);
            }else{
                $output = array('status' => 'error', 'message' => 'Notice Not Deleted');
                echo json_encode($output);
            }
        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function notice_approve(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($input['id']) && $input['id'] != ''){
            $notice = $input;
            $notice['updatedon'] = date('Y-m-d H:i:s');
            $notice['notice_approvedon'] = date('Y-m-d H:i:s');
            $this->db->where('id',$notice['id']);
            if($this->db->update('hrms_notice',$notice)){
                $output = array('status' => 'success', 'message' => 'Notice Updated Successfully');
                echo json_encode($output);
            }else{
                $output = array('status' => 'error', 'message' => 'Notice Not Updated');
                echo json_encode($output);
            }
        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function notice_handover_status(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($input['id']) && $input['id'] != ''){
            $notice = $input;
            $notice['updatedon'] = date('Y-m-d H:i:s');
            $this->db->where('id',$notice['id']);
            if($this->db->update('hrms_notice',$notice)){
                $output = array('status' => 'success', 'message' => 'Notice Updated Successfully');
                echo json_encode($output);
            }else{
                $output = array('status' => 'error', 'message' => 'Notice Not Updated');
                echo json_encode($output);
            }
        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function notice_extension(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($input['id']) && $input['id'] != ''){
            $notice = $input;
            $notice['updatedon'] = date('Y-m-d H:i:s');
            $notice['extended_lastdate'] = date('Y-m-d',strtotime($notice['extended_lastdate']));
            $this->db->where('id',$notice['id']);
            if($this->db->update('hrms_notice',$notice)){
                $output = array('status' => 'success', 'message' => 'Notice Updated Successfully');
                echo json_encode($output);
            }else{
                $output = array('status' => 'error', 'message' => 'Notice Not Updated');
                echo json_encode($output);
            }
        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function notice_status(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($input['id']) && $input['id'] != ''){
            $notice = $input;
            $notice['updatedon'] = date('Y-m-d H:i:s');
            $this->db->where('id',$notice['id']);
            if($this->db->update('hrms_notice',$notice)){
                $output = array('status' => 'success', 'message' => 'Notice Updated Successfully');
                echo json_encode($output);
            }else{
                $output = array('status' => 'error', 'message' => 'Notice Not Updated');
                echo json_encode($output);
            }
        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    // Termination 
    public function termination(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $this->db->select('hrms_termination.*');
        $this->db->select('hrms_staffmaster.biometricAccess,hrms_staffmaster.name,hrms_staffmaster.emp_code,hrms_branch.name as branch_name,hrms_company.name as company_name,hrms_designation.name as designation_name,hrms_department.name as department_name,hrms_staffmaster.branch_id,hrms_staffmaster.company_id,hrms_staffmaster.department_id,hrms_staffmaster.designation_id,hrms_staffmaster.id as emp_id');
        $this->db->join('hrms_staffmaster', 'hrms_staffmaster.id = hrms_termination.emp_id');
        $this->db->join('hrms_branch', 'hrms_branch.id = hrms_staffmaster.branch_id');
        $this->db->join('hrms_company', 'hrms_company.id = hrms_staffmaster.company_id');
        $this->db->join('hrms_department','hrms_department.id=hrms_staffmaster.department_id','left');
        $this->db->join('hrms_designation','hrms_designation.id=hrms_staffmaster.designation_id','left');
        $this->db->from('hrms_termination');
        $this->db->where('hrms_termination.mode',1);
        $this->db->where('hrms_termination.status!=',3);
        $query = $this->db->get();
        $data = array();
        if ($query->num_rows() > 0) { 
            $data = $query->result_array();
        }
        if($data){
            $output = array('status' => 'success', 'message' => 'Termination List', 'data' => $data);
            echo json_encode($output);
        }else{
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }
    public function get_termination(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $this->db->select('hrms_termination.*');
       
        $this->db->from('hrms_termination');
        if(isset($input['id']) && $input['id'] != ''){
            $this->db->where('hrms_termination.id',$input['id']);
        }
        if(isset($input['emp_id']) && $input['emp_id'] != ''){
            $this->db->where('hrms_termination.emp_id',$input['emp_id']);
        }
        $this->db->where('hrms_termination.status!=',3);
        $this->db->where('hrms_termination.mode',1);
        $query = $this->db->get();
        $data = array();
        if ($query->num_rows() > 0) { 
            $data = $query->result_array();
        }
        if($data){
            $output = array('status' => 'success', 'message' => 'Termination List', 'data' => $data);
            echo json_encode($output);
        }else{
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function add_termination(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($input['emp_id']) && $input['emp_id'] != ''){
            $termination = $input;
            $termination['mode'] = 1;
            if(isset($termination['is_salary']) && $termination['is_salary'] != ''){
                $termination['salaryapproved_on'] = date('Y-m-d');
            }
            if(isset($termination['is_return_doc']) && $termination['is_return_doc'] != ''){
                $termination['return_doc_approved_on'] = date('Y-m-d');
            }
            if(isset($termination['is_experence_letter']) && $termination['is_experence_letter'] != ''){
                $termination['experience_letter_approved_on'] = date('Y-m-d');
            }
            $termination['date_termination'] = date('Y-m-d',strtotime($termination['date']));
            unset($termination['date']);
            if($this->db->insert('hrms_termination',$termination)){
                $output = array('status' => 'success', 'message' => 'Termination Inserted Successfully');
                echo json_encode($output);
            }else{
                $output = array('status' => 'error', 'message' => 'Termination Not inserted');
                echo json_encode($output);
            }
        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function update_termination(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($input['id']) && $input['id'] != ''){
            $termination = $input;
            $termination['mode'] = 1;
            if(isset($termination['is_salary']) && $termination['is_salary'] == 1){
                $termination['salaryapproved_on'] = date('Y-m-d');
            }
            if(isset($termination['is_return_doc']) && $termination['is_return_doc'] != ''){
                $termination['return_doc_approved_on'] = date('Y-m-d');
            }
            if(isset($termination['is_experence_letter']) && $termination['is_experence_letter'] != ''){
                $termination['experience_letter_approved_on'] = date('Y-m-d');
            }
            if(isset($termination['date']) && $termination['date'] != '')
                $termination['date_termination'] = date('Y-m-d',strtotime($termination['date']));
            unset($termination['date']);
            $termination['updated_on'] = date('Y-m-d H:i:s');
            $this->db->where('id',$termination['id']);
            if($this->db->update('hrms_termination',$termination)){
                $output = array('status' => 'success', 'message' => 'Termination Updated Successfully');
                echo json_encode($output);
            }else{
                $output = array('status' => 'error', 'message' => 'Termination Not Updated');
                echo json_encode($output);
            }
        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function delete_termination(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($input['id']) && $input['id'] != ''){
            $termination = $input;
            $termination['status'] = 3;
            $termination['updated_on'] = date('Y-m-d H:i:s');
            $this->db->where('id',$termination['id']);
            if($this->db->update('hrms_termination',$termination)){
                $output = array('status' => 'success', 'message' => 'Termination Deleted Successfully');
                echo json_encode($output);
            }else{
                $output = array('status' => 'error', 'message' => 'Termination Not Deleted');
                echo json_encode($output);
            }
        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }
    // Absconded 
    public function absconded(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $this->db->select('hrms_termination.*,hrms_termination.date_termination as date_absconded');
        $this->db->select('hrms_staffmaster.name,hrms_staffmaster.emp_code,hrms_branch.name as branch_name,hrms_company.name as company_name,hrms_designation.name as designation_name,hrms_department.name as department_name,hrms_staffmaster.branch_id,hrms_staffmaster.company_id,hrms_staffmaster.emp_code,hrms_staffmaster.department_id,hrms_staffmaster.designation_id,hrms_staffmaster.id as emp_id');
        $this->db->join('hrms_staffmaster', 'hrms_staffmaster.id = hrms_termination.emp_id');
        $this->db->join('hrms_branch', 'hrms_branch.id = hrms_staffmaster.branch_id');
        $this->db->join('hrms_company', 'hrms_company.id = hrms_staffmaster.company_id');
        $this->db->join('hrms_department','hrms_department.id=hrms_staffmaster.department_id','left');
        $this->db->join('hrms_designation','hrms_designation.id=hrms_staffmaster.designation_id','left');
        $this->db->from('hrms_termination');
        $this->db->where('hrms_termination.mode',2);
        $this->db->where('hrms_termination.status!=',3);
        $query = $this->db->get();
        $data = array();
        if ($query->num_rows() > 0) { 
            $data = $query->result_array();
        }
        if($data){
            $output = array('status' => 'success', 'message' => 'Termination List', 'data' => $data);
            echo json_encode($output);
        }else{
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }
    public function get_absconded(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        $this->db->select('hrms_termination.*,hrms_termination.date_termination as date_absconded');
       
        $this->db->from('hrms_termination');
        if(isset($input['id']) && $input['id'] != ''){
            $this->db->where('hrms_termination.id',$input['id']);
        }
        if(isset($input['emp_id']) && $input['emp_id'] != ''){
            $this->db->where('hrms_termination.emp_id',$input['emp_id']);
        }
        $this->db->where('hrms_termination.status!=',3);
        $this->db->where('hrms_termination.mode',2);
        $query = $this->db->get();
        $data = array();
        if ($query->num_rows() > 0) { 
            $data = $query->result_array();
        }
        if($data){
            $output = array('status' => 'success', 'message' => 'Termination List', 'data' => $data);
            echo json_encode($output);
        }else{
            $output = array('status' => 'error', 'message' => 'No data found');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function add_absconded(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($input['emp_id']) && $input['emp_id'] != ''){
            $termination = $input;
            $termination['mode'] = 2;
            if(isset($termination['is_salary']) && $termination['is_salary'] != ''){
                $termination['salaryapproved_on'] = date('Y-m-d');
            }
            if(isset($termination['is_return_doc']) && $termination['is_return_doc'] != ''){
                $termination['return_doc_approved_on'] = date('Y-m-d');
            }
            if(isset($termination['is_experence_letter']) && $termination['is_experence_letter'] != ''){
                $termination['experience_letter_approved_on'] = date('Y-m-d');
            }
            $termination['date_termination'] = date('Y-m-d',strtotime($termination['date']));
            unset($termination['date']);
            if($this->db->insert('hrms_termination',$termination)){
                $output = array('status' => 'success', 'message' => 'Abscond Inserted Successfully');
                echo json_encode($output);
            }else{
                $output = array('status' => 'error', 'message' => 'Abscond Not inserted');
                echo json_encode($output);
            }
        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function update_absconded(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($input['id']) && $input['id'] != ''){
            $termination = $input;
            $termination['mode'] = 2;
            if(isset($termination['is_salary']) && $termination['is_salary'] == 1){
                $termination['salaryapproved_on'] = date('Y-m-d');
            }
            if(isset($termination['is_return_doc']) && $termination['is_return_doc'] != ''){
                $termination['return_doc_approved_on'] = date('Y-m-d');
            }
            if(isset($termination['is_experence_letter']) && $termination['is_experence_letter'] != ''){
                $termination['experience_letter_approved_on'] = date('Y-m-d');
            }
            if(isset($termination['date']) && $termination['date'] != '')
                $termination['date_termination'] = date('Y-m-d',strtotime($termination['date']));
            unset($termination['date']);
            $termination['updated_on'] = date('Y-m-d H:i:s');
            $this->db->where('id',$termination['id']);
            if($this->db->update('hrms_termination',$termination)){
                $output = array('status' => 'success', 'message' => 'Abscond Updated Successfully');
                echo json_encode($output);
            }else{
                $output = array('status' => 'error', 'message' => 'Abscond Not Updated');
                echo json_encode($output);
            }
        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function delete_absconded(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($input['id']) && $input['id'] != ''){
            $termination = $input;
            $termination['status'] = 3;
            $termination['updated_on'] = date('Y-m-d H:i:s');
            $this->db->where('id',$termination['id']);
            if($this->db->update('hrms_termination',$termination)){
                $output = array('status' => 'success', 'message' => 'Abscond Deleted Successfully');
                echo json_encode($output);
            }else{
                $output = array('status' => 'error', 'message' => 'Abscond Not Deleted');
                echo json_encode($output);
            }
        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }

    public function sending_email(){
        $this->load->library('email');
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $this->db->select('hrms_staffmaster.mail_id,name');
            $this->db->from('hrms_staffmaster');
            $this->db->where('hrms_staffmaster.status!=','3');
            if($input['id']){
                $this->db->where('hrms_staffmaster.id',$input['id']);
            }
            $query = $this->db->get();
            $staffmaster= [];
            if ($query->num_rows() > 0) {
                $staffmaster = $query->result_array();
            }
        if($staffmaster){
            foreach($staffmaster  as $val){
                
                $this->email->set_mailtype("html");
                $this->email->from('zg@gmail.com', 'ZG'); 
                $this->email->to($val['mail_id']);
                $this->email->subject('Welcome'); 
                $html = "Dear ". $val['name']."<br>";
                // $html .= "Your Onboard Link <a href='".$link."'>click Here</a><br>";
                // $html .= '<b>'.$input['content'].'</b>';
                $html .= "Thanks From ZG";
                // echo $val['mail_id'];
                $this->email->message($html); 
        
                //Send mail 
                if($this->email->send()){
                    
                }else{
                    echo "0";
                }
                $this->email->clear(TRUE);
            }
            $output = array('status' => 'success', 'message' => 'Email Sent Successfully');
            echo json_encode($output);
        }else{
            $output = array('status' => 'error', 'message' => 'Email Not sent');
                echo json_encode($output);
        }
        
    }

    public function user_access_update(){
        $json_input = file_get_contents('php://input'); // JSON Input
        $input = json_decode($json_input, true);
        $check_auth_user = $this->login->check_auth_user();
        if($check_auth_user == true){
            
        	$response = $this->login->auth();
        	if($response['status'] == 200){
        if(isset($input['emp_code']) && $input['emp_code'] != '' && isset($input['access_token']) && $input['access_token'] != ''){
            $staffmaster = $this->db->get_where('hrms_staffmaster', array('emp_code'=>$input['emp_code']))->result_array();
            if($staffmaster){
                $this->db->where('emp_id',$staffmaster[0]['id']);
                if($this->db->update('hrms_users',array('access_token'=>$input['access_token']))){
                    $output = array('status' => 'success', 'message' => 'User Updated Successfully');
                    echo json_encode($output);
                }else{
                    $output = array('status' => 'error', 'message' => 'User Not Updated');
                    echo json_encode($output);
                }
            }else{
                $output = array('status' => 'error', 'message' => 'No User Found Fields');
                echo json_encode($output);
            }


        }else{
            $output = array('status' => 'error', 'message' => 'Enter Mandetory Fields');
            echo json_encode($output);
        }
        	}else{
        	   echo json_encode($response); 
        	}
        }
    }
}
