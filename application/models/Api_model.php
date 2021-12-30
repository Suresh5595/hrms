<?php

    if (!defined('BASEPATH')) exit('No direct script access allowed');

    class Api_model extends CI_Model {



        public function validate($username, $password) {

            $this->db->select('*');

            $this->db->from('hrms_staffmaster');

            // $this->db->join('usertype_table', 'usertype_table.UserTypeID = user_table.UserTypeID');

            $this->db->where('pwd', $password);

            $this->db->where('biometricAccess', $username);

            $this->db->where('status', 1);

            $query = $this->db->get();
            if ($query->num_rows() > 0) { 

                return $query->result_array();

            }

            return null;

        }



        public function get_user_details($emp_id) {

            $this->db->select('hrms_users.*,hrms_users.id as user_id');

            $this->db->select('hrms_staffmaster.*,hrms_branch.name as branch_name,hrms_company.name as company_name,hrms_designation.name as designation_name,hrms_department.name as department_name,train_branch.name as training_branch,join_branch.name as joining_branch');   

            $this->db->from('hrms_users');

            $this->db->join('hrms_user_type', 'hrms_user_type.id = hrms_users.usertype_id');

            $this->db->join('hrms_staffmaster', 'hrms_staffmaster.id = hrms_users.emp_id');

            $this->db->join('hrms_branch', 'hrms_branch.id = hrms_staffmaster.branch_id');

            $this->db->join('hrms_branch as join_branch', 'join_branch.id = hrms_staffmaster.joiningbranch');

            $this->db->join('hrms_branch as train_branch', 'train_branch.id = hrms_staffmaster.trainingbranch');

            $this->db->join('hrms_company', 'hrms_company.id = hrms_staffmaster.company_id');

            $this->db->join('hrms_department','hrms_department.id=hrms_staffmaster.department_id','left');

            $this->db->join('hrms_designation','hrms_designation.id=hrms_staffmaster.designation_id','left');

            $this->db->where('hrms_users.emp_id', $emp_id);

            $this->db->where('hrms_users.status', 1);

            $query = $this->db->get();

            // echo $this->db->last_query();

            return $query->row();

        }



        public function get_user_details_by_token($data) {

            

            $this->db->select('*');

            $this->db->from('hrms_users');

            // $this->db->join('usertype_table', 'usertype_table.UserTypeID = user_table.UserTypeID');

            $this->db->where('username', $data['username']);

            $this->db->where('access_token', $data['access_token']);

            $this->db->where('session_start_date <=', date('Y-m-d H:i:s'));

            $this->db->where('session_end_date >=', date('Y-m-d H:i:s'));

            $this->db->where('status', 1);

            

            $query = $this->db->get();

            if ($query->num_rows() > 0) { 

                return $query->result_array();

            }

            return NULL;

        }



        public function get_user_details_by_username($id) {

            $this->db->select('hrms_users.*');

            $this->db->select('hrms_staffmaster.*,hrms_branch.name as branch_name,hrms_company.name as company_name,hrms_designation.name as designation_name,hrms_department.name as department_name,train_branch.name as training_branch,join_branch.name as joining_branch');   

            $this->db->from('hrms_users');

            $this->db->join('hrms_user_type', 'hrms_user_type.id = hrms_users.usertype_id');

            $this->db->join('hrms_staffmaster', 'hrms_staffmaster.id = hrms_users.emp_id');

            $this->db->join('hrms_branch', 'hrms_branch.id = hrms_staffmaster.branch_id');

            $this->db->join('hrms_branch as join_branch', 'join_branch.id = hrms_staffmaster.joiningbranch');

            $this->db->join('hrms_branch as train_branch', 'train_branch.id = hrms_staffmaster.trainingbranch');

            $this->db->join('hrms_company', 'hrms_company.id = hrms_staffmaster.company_id');

            $this->db->join('hrms_department','hrms_department.id=hrms_staffmaster.department_id','left');

            $this->db->join('hrms_designation','hrms_designation.id=hrms_staffmaster.designation_id','left');

            $this->db->where('hrms_users.id', $id);

            $this->db->where('hrms_users.status', 1);

            

            $query = $this->db->get();

            

            return $query->row();

        }



        public function insert_data($spare, $table_name) {

            $this->db->insert($table_name, $spare);

            return $this->db->insert_id();

        }

        public function insert_company($data) {

            if($this->db->insert('hrms_company', $data)){

                return $this->db->insert_id();

            }

            return null;

            

        }



        public function update_company($company,$id){

            $this->db->where('id',$id);

            if($this->db->update('hrms_company', $company)){

                return true;

            }

            return null;



        }

        public function insert_user_type($data) {

            if($this->db->insert('hrms_user_type', $data)){

                $uset_type_id = $this->db->insert_id();

                $this->db->select('hrms_user_permission.*');

                $this->db->from('hrms_user_permission');

                $this->db->where('hrms_user_permission.user_type_id',1);

                $this->db->where('hrms_user_permission.acc_main',1);

                $module = $this->db->get();

                // echo $this->db->last_query();

                if ($module->num_rows() > 0) {

                    $module_data = $module->result_array();

                    

                    if($module_data){

                        foreach($module_data as $key => $module){

                            unset($module['id']);

                            $module['user_type_id'] = $uset_type_id;

                            $module['acc_main'] = 0;

                            $module['acc_all'] =0;

                            $module['acc_view'] = 0;

                            $module['acc_add'] =0;

                            $module['acc_edit'] = 0;

                            $module['acc_delete'] = 0;

                            $this->db->insert('hrms_user_permission',$module);

                        }

                    }

                }

                return $uset_type_id;

            }

            return null;

            

        }



        public function update_user_type($company,$id){

            $this->db->where('id',$id);

            if($this->db->update('hrms_user_type', $company)){

                return true;

            }

            return null;



        }public function insert_users($data) {

            if($this->db->insert('hrms_users', $data)){

                return $this->db->insert_id();

            }

            return null;

            

        }



        public function update_users($company,$id){

            $this->db->where('id',$id);

            if($this->db->update('hrms_users', $company)){

                return true;

            }

            return null;



        }

        public function insert_branch($data) {

            if($this->db->insert('hrms_branch', $data)){

                return $this->db->insert_id();

            }

            return null;

            

        }



        public function update_branch($branch,$id){

            $this->db->where('id',$id);

            if($this->db->update('hrms_branch', $branch)){

                return true;

            }

            return null;



        }



        public function insert_department($data) {

            if($this->db->insert('hrms_department', $data)){

                return $this->db->insert_id();

            }

            return null;

            

        }



        public function insert_kyc($data) {

            if($this->db->insert('hrms_document_upload', $data)){

                return $this->db->insert_id();

            }

            return null;

            

        }



        public function update_kyc($data,$id){

            $this->db->where('id',$id);

            if($this->db->update('hrms_document_upload', $data)){

                return true;

            }

            return null;



        }

        public function update_department($department,$id){

            $this->db->where('id',$id);

            if($this->db->update('hrms_department', $department)){

                return true;

            }

            return null;



        }

        public function insert_designation($data) {

            if($this->db->insert('hrms_designation', $data)){

                return $this->db->insert_id();

            }

            return null;

            

        }



        public function update_designation($department,$id){

            $this->db->where('id',$id);

            if($this->db->update('hrms_designation', $department)){

                return true;

            }

            return null;



        }

        public function insert_holiday($data) {

            if($this->db->insert('hrms_holiday', $data)){

                return $this->db->insert_id();

            }

            return null;

            

        }



        public function update_holiday($holiday,$id){

            $this->db->where('id',$id);

            if($this->db->update('hrms_holiday', $holiday)){

                return true;

            }

            return null;



        }

        public function insert_document($data) {

            if($this->db->insert('hrms_document', $data)){

                return $this->db->insert_id();

            }

            return null;

            

        }



        public function update_document($holiday,$id){

            $this->db->where('id',$id);

            if($this->db->update('hrms_document', $holiday)){

                return true;

            }

            return null;



        }

        public function insert_staffmaster($data) {

            if($this->db->insert('hrms_staffmaster', $data)){

                return $this->db->insert_id();

            }

            return null;

            

        }

        public function insert_reference($data) {

            if($this->db->insert('hrms_reference', $data)){

                return $this->db->insert_id();

            }

            return null;

            

        }

        public function insert_relation($data) {

            if($this->db->insert('hrms_relationship', $data)){

                return $this->db->insert_id();

            }

            return null;

            

        }

        public function insert_work_exp($data) {

            if($this->db->insert('hrms_work_exp', $data)){

                return $this->db->insert_id();

            }

            return null;

            

        }



        public function insert_hike($data) {

            if($this->db->insert('hrms_hike_history', $data)){

                return $this->db->insert_id();

            }

            return null;

            

        }



        public function insert_onboard($data) {

            if($this->db->insert('hrms_onboarding', $data)){

                return $this->db->insert_id();

            }

            return null;

            

        }

        public function insert_onboard_reference($data) {

            if($this->db->insert('hrms_onboarding_reference', $data)){

                return $this->db->insert_id();

            }

            return null;

            

        }

        public function insert_onboard_relation($data) {

            if($this->db->insert('hrms_onboarding_relationship', $data)){

                return $this->db->insert_id();

            }

            return null;

            

        }

        public function insert_onboard_work_exp($data) {

            if($this->db->insert('hrms_onboarding_work_exp', $data)){

                return $this->db->insert_id();

            }

            return null;

            

        }



        public function update_staffmaster($data,$id){

            $this->db->where('id',$id);

            if($this->db->update('hrms_staffmaster', $data)){

                return true;

            }

            return null;



        }

        public function update_reference($ref,$id){

            $this->db->where('id',$id);

            if($this->db->update('hrms_reference', $ref)){

                return true;

            }

            return null;



        }

        public function update_relation($rel,$rel_id){

            $this->db->where('id',$rel_id);

            if($this->db->update('hrms_relationship', $rel)){

                return true;

            }

            return null;



        }

        public function update_work_exp($work,$id){

            $this->db->where('id',$id);

            if($this->db->update('hrms_work_exp', $work)){

                return true;

            }

            return null;



        }

        public function update_hike($hike,$id){

            $this->db->where('id',$id);

            if($this->db->update('hrms_hike_history', $hike)){

                return true;

            }

            return null;



        }



        public function update_onboard_staffmaster($holiday,$id){

            $this->db->where('id',$id);

            if($this->db->update('hrms_onboarding', $holiday)){

                return true;

            }

            return null;



        }

        public function update_onboard_reference($ref,$id){

            $this->db->where('id',$id);

            if($this->db->update('hrms_onboarding_reference', $ref)){

                return true;

            }

            return null;



        }

        public function update_onboard_relation($rel,$rel_id){

            $this->db->where('id',$rel_id);

            if($this->db->update('hrms_onboarding_relationship', $rel)){

                return true;

            }

            return null;



        }

        public function update_onboard_work_exp($work,$id){

            $this->db->where('id',$id);

            if($this->db->update('hrms_onboarding_work_exp', $work)){

                return true;

            }

            return null;



        }



        public function insert_job_opening($data) {

            if($this->db->insert('hrms_job_openings', $data)){

                return $this->db->insert_id();

            }

            return null;

            

        }

        

        public function update_job_opening($work,$id){

            $this->db->where('id',$id);

            if($this->db->update('hrms_job_openings', $work)){

                return true;

            }

            return null;



        }



        public function insert_job_ques($data) {

            if($this->db->insert('hrms_job_questionnaire', $data)){

                return $this->db->insert_id();

            }

            return null;

            

        }

        

        public function update_job_ques($work,$id){

            $this->db->where('id',$id);

            if($this->db->update('hrms_job_questionnaire', $work)){

                return true;

            }

            return null;



        }



        public function insert_schedule($data) {

            if($this->db->insert('hrms_schedule', $data)){

                return $this->db->insert_id();

            }

            return null;

            

        }

        public function insert_schedule_batch($data) {

            if($this->db->insert_batch('hrms_schedule', $data)){

                return true;

            }

            return false;

            

        }

        

        public function update_schedule($work,$id){

            $this->db->where('id',$id);

            if($this->db->update('hrms_schedule', $work)){

                return true;

            }

            return null;



        }



        public function insert_call_history($data) {

            if($this->db->insert('hrms_call_history', $data)){

                return $this->db->insert_id();

            }

            return null;

            

        }

        public function insert_call_history_batch($data) {

            if($this->db->insert_batch('hrms_call_history', $data)){

                return true;

            }

            return false;

            

        }

        

        public function update_call_history($work,$id){

            $this->db->where('id',$id);

            if($this->db->update('hrms_call_history', $work)){

                return true;

            }

            return null;



        }



        public function insert_application($data) {

            if($this->db->insert('hrms_application', $data)){

                return $this->db->insert_id();

            }

            return null;

            

        }



        public function update_online_test($data,$id){

            $this->db->where('id',$id);

            if($this->db->update('hrms_online_test', $data)){

                return true;

            }

            return null;



        }



        public function insert_online_test($data) {

            if($this->db->insert('hrms_online_test', $data)){

                return $this->db->insert_id();

            }

            return null;

            

        }



        public function update_application($data,$id){

            $this->db->where('id',$id);

            if($this->db->update('hrms_application', $data)){

                return true;

            }

            return null;



        }



        public function get_data($table, $where, $column) {

            return $this->db->get_where($table, $where)->row($column);

        }



        public function duplicate_check($data,$table_name,$colum){

            $this->db->select('*');

            $this->db->from($table_name);

            $this->db->where($colum, $data);

            $this->db->where('status!=', 3);

            $query = $this->db->get();

            if ($query->num_rows() > 0) { 

                return 1;

            }

            else {

                return 0;

            }     

        }



        public function duplicate_checkedit($data,$table_name,$colum,$id) {

            $this->db->select('*');

            $this->db->from($table_name);

            $this->db->where($colum, $data);

            $this->db->where('id!=', $id);

            $this->db->where('status!=', 3);

            $query = $this->db->get();

            if ($query->num_rows() > 0) { 

                return 1;

            }

            else{

                return 0;

            }     

        }  

        

        public function get_company($id = null){

            $this->db->select('*');

            $this->db->from('hrms_company');

            $this->db->where('status!=','3');

            if($id){

                $this->db->where('id',$id);

            }

            $query = $this->db->get();

            if ($query->num_rows() > 0) { 

                return $query->result_array();

            }

            return NULL;

        }

        

        public function get_active_company(){

            $this->db->select('*');

            $this->db->from('hrms_company');

            $this->db->where('status','1');

            $query = $this->db->get();

            if ($query->num_rows() > 0) { 

                return $query->result_array();

            }

            return NULL;

        }



        public function get_branch($id = null){

            $this->db->select('hrms_branch.*,hrms_company.name as company_name');

            $this->db->from('hrms_branch');

            $this->db->join('hrms_company','hrms_company.id=hrms_branch.company_id');

            $this->db->where('hrms_branch.status!=','3');

            if($id){

                $this->db->where('hrms_branch.id',$id);

            }

            $query = $this->db->get();

            if ($query->num_rows() > 0) { 

                return $query->result_array();

            }

            return NULL;

        }

        public function get_branch_by_company($companyid = null){

            $this->db->select('hrms_branch.*,hrms_company.name as company_name');

            $this->db->from('hrms_branch');

            $this->db->join('hrms_company','hrms_company.id=hrms_branch.company_id');

            $this->db->where('hrms_branch.status','1');

            if($companyid){

                $this->db->where('hrms_branch.company_id',$companyid);

            }

            $query = $this->db->get();

            if ($query->num_rows() > 0) { 

                return $query->result_array();

            }

            return NULL;

        }

        public function get_department($id = null){

            $this->db->select('hrms_department.*,hrms_company.name as company_name');

            $this->db->from('hrms_department');

            $this->db->join('hrms_company','hrms_company.id=hrms_department.company_id');

            $this->db->where('hrms_department.status!=','3');

            if($id){

                $this->db->where('hrms_department.id',$id);

            }

            $query = $this->db->get();

            if ($query->num_rows() > 0) { 

                return $query->result_array();

            }

            return NULL;

        }

        public function get_designation($id = null){

            $this->db->select('hrms_designation.*,hrms_department.name as department_name,hrms_company.id as company_id,hrms_company.name as company_name');

            $this->db->from('hrms_designation');

            $this->db->join('hrms_department','hrms_department.id=hrms_designation.departmentid');
            $this->db->join('hrms_company','hrms_company.id = hrms_department.company_id');
            $this->db->where('hrms_designation.status!=','3');

            if($id){

                $this->db->where('hrms_designation.id',$id);

            }

            $query = $this->db->get();

            if ($query->num_rows() > 0) { 

                return $query->result_array();

            }

            return NULL;

        }

        public function get_holiday($id = null){

            $this->db->select('hrms_holiday.*,hrms_company.name as company_name,hrms_branch.name as branch_name');

            $this->db->from('hrms_holiday');

            $this->db->join('hrms_company','hrms_company.id=hrms_holiday.companyid');

            $this->db->join('hrms_branch','hrms_branch.id=hrms_holiday.branchid');

            $this->db->where('hrms_holiday.status!=','3');

            if($id){

                $this->db->where('hrms_holiday.id',$id);

            }

            $query = $this->db->get();

            if ($query->num_rows() > 0) { 

                return $query->result_array();

            }

            return NULL;

        }

        public function get_document($id = null){

            $this->db->select('*');

            $this->db->from('hrms_document');

            $this->db->where('status!=','3');

            if($id){

                $this->db->where('id',$id);

            }

            $query = $this->db->get();

            if ($query->num_rows() > 0) { 

                return $query->result_array();

            }

            return NULL;

        }

        public function get_kyc($id = null){

            $this->db->select('*');

            $this->db->from('hrms_document_upload');

            $this->db->where('status!=','3');

            if($id){

                $this->db->where('id',$id);

            }

            $query = $this->db->get();

            if ($query->num_rows() > 0) { 

                return $query->result_array();

            }

            return NULL;

        }

        

        public function get_application($data = array()){

            $this->db->select('hrms_application.*,hrms_designation.name as designation_name,hrms_department.name as department_name,hrms_company.name as company_name,hrms_branch.name as branch_name,hrms_job_openings.vacancy_type');

            $this->db->from('hrms_application');

            $this->db->join('hrms_job_openings','hrms_job_openings.id=hrms_application.job_opening_id','left');

            $this->db->join('hrms_branch','hrms_branch.id=hrms_job_openings.branch_id','left');

            $this->db->join('hrms_company','hrms_company.id=hrms_application.company_id','left');

            $this->db->join('hrms_department','hrms_department.id=hrms_job_openings.department_id','left');

            $this->db->join('hrms_designation','hrms_designation.id=hrms_job_openings.designation_id','left');

            $this->db->where('hrms_application.job_status!=','3');

            if(isset($data['id']) && !empty($data['id'])){

                $this->db->where('hrms_application.id',$data['id']);

            }

            if(isset($data['company_id']) && !empty($data['company_id'])){

                $this->db->where('hrms_application.company_id',$data['company_id']);

            }

            if(isset($data['application_no']) && !empty($data['application_no'])){

                $this->db->where('hrms_application.application_no',$data['application_no']);

            }

            if(isset($data['branch_id']) && !empty($data['branch_id'])){

                $this->db->where('hrms_job_openings.branch_id',$data['branch_id']);

            }

            if(isset($data['designation_id']) && !empty($data['designation_id'])){

                $this->db->where('hrms_job_openings.designation_id',$data['designation_id']);

            }

            if(isset($data['null']) && !empty($data['null'])){

                $this->db->where('hrms_application.assigned_to is NULL', null, false);

            }

            $query = $this->db->get();

            if ($query->num_rows() > 0) { 

                return $query->result_array();

            }

            return NULL;

        }



        public function get_application_grid($data = array()){

            $this->db->select('hrms_application.id,hrms_application.first_name,hrms_application.last_name,hrms_application.phone,hrms_application.whatsapp_no,hrms_application.application_no,hrms_application.application_type,hrms_application.overall_exp,hrms_application.current_comp_salary,hrms_application.resume,hrms_application.current_comp_salary,hrms_application.current_comp_salary,hrms_designation.name as designation_name,hrms_department.name as department_name,hrms_company.name as company_name,hrms_branch.name as branch_name,hrms_job_openings.vacancy_type,hrms_company.id as company_id,hrms_branch.id as branch_id,hrms_department.id as department_id,hrms_designation.id as designation_id,hrms_application.application_type,hrms_job_openings.recruiter1,hrms_job_openings.recruiter2,hrms_job_openings.recruiter3');

            $this->db->from('hrms_application');

            $this->db->join('hrms_job_openings','hrms_job_openings.id=hrms_application.job_opening_id','left');

            $this->db->join('hrms_branch','hrms_branch.id=hrms_job_openings.branch_id','left');

            $this->db->join('hrms_company','hrms_company.id=hrms_application.company_id','left');

            $this->db->join('hrms_department','hrms_department.id=hrms_job_openings.department_id','left');

            $this->db->join('hrms_designation','hrms_designation.id=hrms_job_openings.designation_id','left');

            $this->db->where('hrms_application.job_status!=','3');

            if(isset($data['application_id']) && !empty($data['application_id'])){

                $this->db->where('hrms_application.id',$data['application_id']);

            }

            if(isset($data['company_id']) && !empty($data['company_id'])){

                $this->db->where_in('hrms_application.company_id',$data['company_id']);

            }

            if(isset($data['branch_id']) && !empty($data['branch_id'])){

                $this->db->where_in('hrms_job_openings.branch_id',$data['branch_id']);

            }

            if(isset($data['designation_id']) && !empty($data['designation_id'])){

                $this->db->where_in('hrms_job_openings.designation_id',$data['designation_id']);

            }

            if(isset($data['department_id']) && !empty($data['department_id'])){

                $this->db->where_in('hrms_job_openings.department_id',$data['department_id']);

            }

            if(isset($data['application_type']) && !empty($data['application_type'])){

                $this->db->where('hrms_application.application_type',$data['application_type']);

            }

            if(isset($data['null']) && !empty($data['null'])){

                $this->db->where('hrms_application.assigned_to is NULL', null, false);

            }

            $query = $this->db->get();

            if ($query->num_rows() > 0) { 

                return $query->result_array();

            }

            return NULL;

        }



        public function get_staffmaster($id = null){

            $this->db->select('hrms_staffmaster.*,hrms_branch.name as branch_name,hrms_company.name as company_name,hrms_designation.name as designation_name,hrms_department.name as department_name,train_branch.name as training_branch,join_branch.name as joining_branch');

            $this->db->from('hrms_staffmaster');

            $this->db->join('hrms_branch', 'hrms_branch.id = hrms_staffmaster.branch_id');

            $this->db->join('hrms_branch as join_branch', 'join_branch.id = hrms_staffmaster.joiningbranch');

            $this->db->join('hrms_branch as train_branch', 'train_branch.id = hrms_staffmaster.trainingbranch');

            $this->db->join('hrms_company', 'hrms_company.id = hrms_staffmaster.company_id');

            $this->db->join('hrms_department','hrms_department.id=hrms_staffmaster.department_id','left');

            $this->db->join('hrms_designation','hrms_designation.id=hrms_staffmaster.designation_id','left');

            // $this->db->join('hrms_users','hrms_users.emp_id=hrms_staffmaster.id','left');

            // $this->db->join('hrms_user_type','hrms_user_type.id=hrms_users.usertype_id','left');

            $this->db->where('hrms_staffmaster.status!=','3');

            // $this->db->where('hrms_users.status','1');

            if($id){

                $this->db->where('hrms_staffmaster.id',$id);

            }

            $query = $this->db->get();

            if ($query->num_rows() > 0) { 

                $result = $query->result_array();

                foreach($result as $key => $val){

                    if($val['repfw1_comp'] != null){

                        $result[$key]['repfw1_comp_name'] = $this->db->get_where('hrms_company', array('id' => $val['repfw1_comp']))->row('name');

                    }else{

                        $result[$key]['repfw1_comp_name'] = null;

                    }

                    if($val['repfw2_comp'] != null){

                        $result[$key]['repfw2_comp_name'] = $this->db->get_where('hrms_company', array('id' => $val['repfw2_comp']))->row('name');

                    }else{

                        $result[$key]['repfw2_comp_name'] = null;

                    }

                    if($val['repfw3_comp'] != null){

                        $result[$key]['repfw3_comp_name'] = $this->db->get_where('hrms_company', array('id' => $val['repfw3_comp']))->row('name');

                    }else{

                        $result[$key]['repfw3_comp_name'] = null;

                    }

                    if($val['repfw4_comp'] != null){

                        $result[$key]['repfw4_comp_name'] = $this->db->get_where('hrms_company', array('id' => $val['repfw4_comp']))->row('name');

                    }else{

                        $result[$key]['repfw4_comp_name'] = null;

                    }

                    if($val['repfw1_brn'] != null){

                        $result[$key]['repfw1_brn_name'] = $this->db->get_where('hrms_branch', array('id' => $val['repfw1_brn']))->row('name');

                    }else{

                        $result[$key]['repfw1_brn_name'] = null;

                    }

                    if($val['repfw2_brn'] != null){

                        $result[$key]['repfw2_brn_name'] = $this->db->get_where('hrms_branch', array('id' => $val['repfw2_brn']))->row('name');

                    }else{

                        $result[$key]['repfw2_brn_name'] = null;

                    }

                    if($val['repfw3_brn'] != null){

                        $result[$key]['repfw3_brn_name'] = $this->db->get_where('hrms_branch', array('id' => $val['repfw3_brn']))->row('name');

                    }else{

                        $result[$key]['repfw3_brn_name'] = null;

                    }

                    if($val['repfw4_brn'] != null){

                        $result[$key]['repfw4_brn_name'] = $this->db->get_where('hrms_branch', array('id' => $val['repfw4_brn']))->row('name');

                    }else{

                        $result[$key]['repfw4_brn_name'] = null;

                    }

                    if($val['fwd1'] != null){

                        $result[$key]['fwd1_name'] = $this->db->get_where('hrms_staffmaster', array('id' => $val['fwd1']))->row('name');

                    }else{

                        $result[$key]['fwd1_name'] = null;

                    }

                    if($val['fwd2'] != null){

                        $result[$key]['fwd2_name'] = $this->db->get_where('hrms_staffmaster', array('id' => $val['fwd2']))->row('name');

                    }else{

                        $result[$key]['fwd2_name'] = null;

                    }

                    if($val['fwd3'] != null){

                        $result[$key]['fwd3_name'] = $this->db->get_where('hrms_staffmaster', array('id' => $val['fwd3']))->row('name');

                    }else{

                        $result[$key]['fwd3_name'] = null;

                    }

                    if($val['fwd4'] != null){

                        $result[$key]['fwd4_name'] = $this->db->get_where('hrms_staffmaster', array('id' => $val['fwd4']))->row('name');

                    }else{

                        $result[$key]['fwd4_name'] = null;

                    }

                    if($val['employee_sharing'] != null){

                        $this->db->select('name');

                        $this->db->from('hrms_company');

                        $this->db->where_in('id',explode(',',$val['employee_sharing']));

                        $data = $this->db->get()->result_array();



                        $employee_sharing = array_column($data, 'name');

                        $result[$key]['employee_sharing_name'] = implode(',',$employee_sharing);

                    }else{

                        $result[$key]['employee_sharing_name'] = null;

                    }

                    $this->db->select('hrms_staffmaster.user_type_id_fk,hrms_user_type.usertypename');

                    $this->db->from('hrms_staffmaster');

                    $this->db->join('hrms_user_type','hrms_user_type.id=hrms_staffmaster.user_type_id_fk');

                    $this->db->where('hrms_staffmaster.id',$val['id']);

                    $this->db->where('hrms_staffmaster.status',1);

                    $user = $this->db->get()->result_array();

                    if($user){

                        $result[$key]['usertypename'] = $user[0]['usertypename'];

                    }else{

                        $result[$key]['usertypename'] = '';

                    }

                    





                    $this->db->select('*');

                    $this->db->from('hrms_reference');

                    $this->db->where('emp_id',$val['id']);

                    $this->db->where('status!=','3');

                    $ref = $this->db->get()->result_array();

                    $result[$key]['reference'] = $ref;

                    $this->db->select('*');

                    $this->db->from('hrms_relationship');

                    $this->db->where('emp_id',$val['id']);

                    $this->db->where('status!=','3');

                    $relationship = $this->db->get()->result_array();

                    $result[$key]['relation'] = $relationship;

                    $this->db->select('*');

                    $this->db->from('hrms_work_exp');

                    $this->db->where('emp_id',$val['id']);

                    $this->db->where('status!=','3');

                    $work_exp = $this->db->get()->result_array();

                    $result[$key]['work_exp'] = $work_exp;

                    $this->db->select('hrms_hike_history.*,hrms_branch.name as branch_name,hrms_company.name as company_name,hrms_designation.name as designation_name,hrms_department.name as department_name,hrms_staffmaster.branch_id,hrms_staffmaster.company_id,hrms_staffmaster.department_id,hrms_staffmaster.designation_id,hrms_staffmaster.id as emp_id');

                    $this->db->from('hrms_hike_history');

                    $this->db->join('hrms_staffmaster', 'hrms_staffmaster.id = hrms_hike_history.emp_id');

                    $this->db->join('hrms_branch', 'hrms_branch.id = hrms_staffmaster.branch_id');

                    $this->db->join('hrms_company', 'hrms_company.id = hrms_staffmaster.company_id');

                    $this->db->join('hrms_department','hrms_department.id=hrms_staffmaster.department_id','left');

                    $this->db->join('hrms_designation','hrms_designation.id=hrms_staffmaster.designation_id','left');

                    $this->db->where('hrms_hike_history.emp_id',$val['id']);

                    $this->db->where('hrms_hike_history.status!=','3');

                    $hike = $this->db->get()->result_array();

                    $result[$key]['hike_history'] = $hike;

                    $this->db->select('*');

                    $this->db->from('hrms_document_upload');

                    $this->db->where('emp_id',$val['id']);

                    $this->db->where('mode','KYC');

                    $this->db->where('status!=','3');

                    $kyc = $this->db->get()->result_array();

                    $result[$key]['kyc'] = $kyc;

                    $this->db->select('*');

                    $this->db->from('hrms_document_upload');

                    $this->db->where('emp_id',$val['id']);

                    $this->db->where('mode','received_documents');

                    $this->db->where('status!=','3');

                    $received_documents = $this->db->get()->result_array();

                    $result[$key]['received_doc'] = $received_documents;

                    $this->db->select('*');

                    $this->db->from('hrms_bank');

                    $this->db->where('emp_id',$val['id']);

                    $this->db->where('status!=','3');

                    $bank = $this->db->get()->result_array();

                    $result[$key]['bank'] = $bank;

                }

                return $result;

            }

            return NULL;

        }

        public function get_staffmaster_id($id = null){

            $this->db->select('hrms_staffmaster.*');

            $this->db->from('hrms_staffmaster');

            $this->db->where('hrms_staffmaster.status!=','3');

            if($id){

                $this->db->where('hrms_staffmaster.id',$id);

            }

            $query = $this->db->get();

            if ($query->num_rows() > 0) {

                $result = $query->result_array();

                foreach($result as $key => $val){

                    $this->db->select('*');

                    $this->db->from('hrms_reference');

                    $this->db->where('emp_id',$val['id']);

                    $this->db->where('status!=','3');

                    $ref = $this->db->get()->result_array();

                    $result[$key]['reference'] = $ref;

                    $this->db->select('*');

                    $this->db->from('hrms_relationship');

                    $this->db->where('emp_id',$val['id']);

                    $this->db->where('status!=','3');

                    $relationship = $this->db->get()->result_array();

                    $result[$key]['relation'] = $relationship;

                    $this->db->select('*');

                    $this->db->from('hrms_work_exp');

                    $this->db->where('emp_id',$val['id']);

                    $this->db->where('status!=','3');

                    $work_exp = $this->db->get()->result_array();

                    $result[$key]['work_exp'] = $work_exp;

                    $this->db->select('hrms_hike_history.*,hrms_branch.name as branch_name,hrms_company.name as company_name,hrms_designation.name as designation_name,hrms_department.name as department_name,hrms_staffmaster.branch_id,hrms_staffmaster.company_id,hrms_staffmaster.department_id,hrms_staffmaster.designation_id,hrms_staffmaster.id as emp_id');

                    $this->db->from('hrms_hike_history');

                    $this->db->join('hrms_staffmaster', 'hrms_staffmaster.id = hrms_hike_history.emp_id');

                    $this->db->join('hrms_branch', 'hrms_branch.id = hrms_staffmaster.branch_id');

                    $this->db->join('hrms_company', 'hrms_company.id = hrms_staffmaster.company_id');

                    $this->db->join('hrms_department','hrms_department.id=hrms_staffmaster.department_id','left');

                    $this->db->join('hrms_designation','hrms_designation.id=hrms_staffmaster.designation_id','left');

                    $this->db->where('hrms_hike_history.emp_id',$val['id']);

                    $this->db->where('hrms_hike_history.status!=','3');

                    $hike = $this->db->get()->result_array();
                    $result[$key]['hike_history'] = $hike;

                    $this->db->select('*');

                    $this->db->from('hrms_document_upload');

                    $this->db->where('emp_id',$val['id']);

                    $this->db->where('mode','KYC');

                    $this->db->where('status!=','3');

                    $kyc = $this->db->get()->result_array();

                    $result[$key]['kyc'] = $kyc;

                    $this->db->select('*');

                    $this->db->from('hrms_document_upload');

                    $this->db->where('emp_id',$val['id']);

                    $this->db->where('mode','received_documents');

                    $this->db->where('status!=','3');

                    $received_documents = $this->db->get()->result_array();

                    $result[$key]['received_doc'] = $received_documents;

                    $this->db->select('*');

                    $this->db->from('hrms_bank');

                    $this->db->where('emp_id',$val['id']);

                    $this->db->where('status!=','3');

                    $bank = $this->db->get()->result_array();

                    $result[$key]['bank'] = $bank;

                }

                return $result;

            }

            return NULL;

        }

        public function get_onboard($id = null){

            $this->db->select('*');

            $this->db->from('hrms_onboarding');

            $this->db->where('status!=','3');

            if($id){

                $this->db->where('id',$id);

            }

            $query = $this->db->get();

            if ($query->num_rows() > 0) { 

                $result = $query->result_array();

                foreach($result as $key => $val){

                    $this->db->select('*');

                    $this->db->from('hrms_onboarding_reference');

                    $this->db->where('emp_id',$val['id']);

                    $this->db->where('status!=','3');

                    $ref = $this->db->get()->result_array();

                    $result[$key]['reference'] = $ref;

                    $this->db->select('*');

                    $this->db->from('hrms_onboarding_relationship');

                    $this->db->where('emp_id',$val['id']);

                    $this->db->where('status!=','3');

                    $relationship = $this->db->get()->result_array();

                    $result[$key]['relationship'] = $relationship;

                    $this->db->select('*');

                    $this->db->from('hrms_onboarding_work_exp');

                    $this->db->where('emp_id',$val['id']);

                    $this->db->where('status!=','3');

                    $work_exp = $this->db->get()->result_array();

                    $result[$key]['work_exp'] = $work_exp;

                }

                return $result;

            }

            return NULL;

        }



        public function get_job_opening($data = array()){

            $this->db->select('*');

            $this->db->from('hrms_job_openings');

            $this->db->where('status!=','3');

            if(isset($data['id']) && !empty($data['id'])){

                $this->db->where('id',$data['id']);

            }

            if(isset($data['company_id']) && !empty($data['company_id'])){

                $this->db->where('hrms_job_openings.company_id',$data['company_id']);

            }

            if(isset($data['branch_id']) && !empty($data['branch_id'])){

                $this->db->where('hrms_job_openings.branch_id',$data['branch_id']);

            }

            if(isset($data['designation_id']) && !empty($data['designation_id'])){

                $this->db->where('hrms_job_openings.designation_id',$data['designation_id']);

            }

            if(isset($data['department_id']) && !empty($data['department_id'])){

                $this->db->where('hrms_job_openings.department_id',$data['department_id']);

            }

            if(isset($data['pub_status']) && !empty($data['pub_status'])){

                $this->db->where('hrms_job_openings.pub_status',$data['pub_status']);

            }

            $query = $this->db->get();

            if ($query->num_rows() > 0) { 

                return $query->result_array();

            }

            return NULL;

        }

        public function get_job_ques($id = null){

            $this->db->select('*');

            $this->db->from('hrms_job_questionnaire');

            if($id){

                $this->db->where('id',$id);

            }

            $this->db->where('status!=','3');

            $query = $this->db->get();

            if ($query->num_rows() > 0) { 

                return $query->result_array();

            }

            return NULL;

        }

        

        public function get_schedule($id = null){

            $this->db->select('*');

            $this->db->from('hrms_schedule');

            if($id){

                $this->db->where('id',$id);

            }

            $this->db->where('status!=','3');

            $query = $this->db->get();

            if ($query->num_rows() > 0) { 

                return $query->result_array();

            }

            return NULL;

        }



        public function get_call_history($id = null){

            $this->db->select('*');

            $this->db->from('hrms_call_history');

            if($id){

                $this->db->where('id',$id);

            }

            $this->db->where('status!=','3');

            $query = $this->db->get();

            if ($query->num_rows() > 0) { 

                return $query->result_array();

            }

            return NULL;

        }



        public function get_online_test($id = null){

            $this->db->select('*');

            $this->db->from('hrms_online_test');

            if($id){

                $this->db->where('id',$id);

            }

            $this->db->where('status!=','3');

            $query = $this->db->get();

            if ($query->num_rows() > 0) { 

                return $query->result_array();

            }

            return NULL;

        }



        public function get_user_type($id = null){

            $this->db->select('*');

            $this->db->from('hrms_user_type');

            $this->db->where('status!=','3');

            if($id){

                $this->db->where('id',$id);

            }

            $query = $this->db->get();

            if ($query->num_rows() > 0) { 

                return $query->result_array();

            }

            return NULL;

        }

        

        public function get_users($id = null){

            $this->db->select('hrms_users.*,hrms_user_type.usertypename,hrms_staffmaster.name as emp_name,hrms_staffmaster.emp_code');

            $this->db->from('hrms_users');

            $this->db->join('hrms_user_type', 'hrms_user_type.id = hrms_users.usertype_id');

            $this->db->join('hrms_staffmaster', 'hrms_staffmaster.id = hrms_users.emp_id');

            // $this->db->join('hrms_company', 'hrms_company.id = hrms_users.company_id');

            $this->db->where('hrms_users.status!=','3');

            if($id){

                $this->db->where('hrms_users.id',$id);

            }

            $query = $this->db->get();

            

            if ($query->num_rows() > 0) { 

                return $query->result_array();

            }

            return NULL;

        }



        public function insert_source($data) {

            if($this->db->insert('hrms_source', $data)){

                return $this->db->insert_id();

            }

            return null;

            

        }



        public function update_source($source,$id){

            $this->db->where('id',$id);

            if($this->db->update('hrms_source', $source)){

                return true;

            }

            return null;



        }

        

        public function get_source($id = null){

            $this->db->select('*');

            $this->db->from('hrms_source');

            $this->db->where('status!=','3');

            if($id){

                $this->db->where('id',$id);

            }

            $query = $this->db->get();

            if ($query->num_rows() > 0) { 

                return $query->result_array();

            }

            return NULL;

        }

        

        public function get_active_source($id = null){

            $this->db->select('id,sourcename');

            $this->db->from('hrms_source');

            $this->db->where('status','1');

            if($id){

                $this->db->where('id',$id);

            }

            $query = $this->db->get();

            if ($query->num_rows() > 0) { 

                return $query->result_array();

            }

            return NULL;

        }



        public function insert_questions($data) {

            if($this->db->insert('hrms_questions', $data)){

                return $this->db->insert_id();

            }

            return null;

            

        }



        public function update_questions($questions,$id){

            $this->db->where('id',$id);

            if($this->db->update('hrms_questions', $questions)){

                return true;

            }

            return null;



        }

        

        public function get_questions($id = null){

            $this->db->select('*');

            $this->db->from('hrms_questions');

            $this->db->where('status!=','3');

            if($id){

                $this->db->where('id',$id);

            }

            $query = $this->db->get();

            if ($query->num_rows() > 0) { 

                return $query->result_array();

            }

            return NULL;

        }

        

        public function get_active_questions($id = null){

            $this->db->select('id,question,option1,option2,option3,option4,udf1,udf2,udf3,status');

            $this->db->from('hrms_questions');

            $this->db->where('status','1');

            if($id){

                $this->db->where('id',$id);

            }

            $query = $this->db->get();

            if ($query->num_rows() > 0) { 

                $results = $query->result_array();

                $fields = array("option1","option2","option3","option4","udf1","udf2","udf3");

                foreach($results as $keys => $value){

                    $i=0;

                     $results[$keys]['options'] = array();

                    foreach($value as $key=>$val){

                        if(in_array($key,$fields)){

                            if(!empty($val)){

                                $results[$keys]['options'][$key] = $val;

                                unset($results[$keys][$key]);

                            }

                        }

                        // echo $key.'<br>'.$val;

                    }

                    // $results[$keys]['option_count'] = $i;

                }

                return $results;

            }

            return NULL;

        }



        

        public function insert_bank($data) {

            if($this->db->insert('hrms_bank', $data)){

                return $this->db->insert_id();

            }

            return null;

            

        }



        public function update_bank($data,$id){

            $this->db->where('id',$id);

            if($this->db->update('hrms_bank', $data)){

                return true;

            }

            return null;



        }

        

        public function get_bank($id = null){

            $this->db->select('*');

            $this->db->from('hrms_bank');

            $this->db->where('status!=','3');

            if($id){

                $this->db->where('id',$id);

            }

            $query = $this->db->get();

            if ($query->num_rows() > 0) { 

                return $query->result_array();

            }

            return NULL;

        }



        public function check_duplicate($table,$field,$value,$id = null){

            $this->db->select('id');

            $this->db->from($table);

            $this->db->where($field,$value);

            if($id){

                $this->db->where('id!=',$id);

            }

            $query = $this->db->get();

            if ($query->num_rows() > 0) { 

                return $query->result_array();

            }

            return NULL;



        }

        public function check_branch_duplicate($table,$field,$value,$field1,$value1,$id = null){

            $this->db->select('id');

            $this->db->from($table);

            $this->db->where($field,$value);
            $this->db->where($field1,$value1);

            if($id){

                $this->db->where('id!=',$id);

            }

            $query = $this->db->get();

            if ($query->num_rows() > 0) { 

                return $query->result_array();

            }

            return NULL;



        }

    }