<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Queries {
	private $ci;
	function __construct() {
		$this->ci =& get_instance();
    	$this->ci->load->database();
	}
	public function query($table=NULL,$where=NULL,$type=NULL,$notwhere=NULL,$field=NULL,$joinsArray=NULL,$group=NULL,$like=NULL,$limit=NULL){
		if(!empty($field)){
			$this->ci->db->select($field);
		}
		if(!empty($table)){
		 	$this->ci->db->from($table);
		}

		if(!empty($joinsArray)){
	        foreach($joinsArray as $joinRow){
	          	if(!empty($joinRow['joinType'])){
	                $this->ci->db->join($joinRow['table'],$joinRow['tableJoin'],$joinRow['joinType']);
	           	}else{ 
	           		$this->ci->db->join($joinRow['table'],$joinRow['tableJoin']); 
	           	} 
	        }
        }

        if(!empty($where)){ 
        	if(is_array($where)){
        		foreach($where as $key => $val){
        			$this->ci->db->where($key,$val);
        		}
        	}else{
        		$this->ci->ci->db->where($where); 
        	}  
     	}

        if(!empty($notwhere)){ 
            if(is_array($notwhere)){
                foreach($notwhere as $key => $val){
                    $this->ci->db->where($key.'!='.$val);
                }
            }else{
                $this->ci->ci->db->where($notwhere); 
            }  
        }

     	if(!empty($like)){
     		$this->ci->db->like($like);   
     	}

        if(!empty($group)){
        	$this->ci->db->GROUP_BY($group); 
        }
                
        if(!empty($limit)){
        	$this->ci->db->limit($limit['limit'],$limit['start']); 
        }
		$result = $this->ci->db->get();
        if($type == "result"){  
        	return $result->result(); 
        }
    	if($type == 'row'){
    	   return $result->row();   
    	}
	}

	public function insert($tbl_name,$data){
		$this->ci->db->trans_start();
		$this->ci->db->insert($tbl_name,$data);
		if($this->ci->db->trans_status() === FALSE){
			$this->ci->db->trans_rollback();
			return array('status' => 500,'message' => 'Internal server error.');
		}else{
			$this->ci->db->trans_commit();
			return array('status' => 200,'message' => 'Inserted Successfully');
		}
    }

    public function insertByid($tbl_name,$data){
        $this->ci->db->insert($tbl_name,$data);
        $insert_id = $this->ci->db->insert_id();
        return  $insert_id;
    }

    public function update($column_name,$column_value,$tbl_name,$data){
      	$this->ci->db->trans_start();
      	$this->ci->db->where($column_name,$column_value);
      	$this->ci->db->update($tbl_name,$data);
      	if($this->ci->db->trans_status() === FALSE){
        	$this->ci->db->trans_rollback();
        	return array('status' => 500,'message' => 'Internal server error.');
      	}else{
        	$this->ci->db->trans_commit();
        	return array('status' => 200,'message' => 'Updated Successfully');
      	}
    }
    public function delete($column_name,$column_value,$tbl_name){
        $this->ci->db->trans_start();
        $this->ci->db->where($column_name,$column_value);
        $this->ci->db->delete($tbl_name);
        if ($this->ci->db->trans_status() === FALSE){
            $this->ci->db->trans_rollback();
            return array('status' => 500,'message' => 'Internal server error.');
        } else {
            $this->ci->db->trans_commit();
            return array('status' => 200,'message' => 'Deleted Successfully');
        }
    }

    public function checkExist($column_name,$column_value,$table_name){
        return $this->ci->db->where($column_name,$column_value)->get($table_name)->row();
    }

    public function updateCheckExist($column_name,$column_value,$column_name1,$column_value1,$table_name){
        return $this->ci->db->where($column_name,$column_value)->where($column_name1.'!='.$column_value1)->get($table_name)->row();
    }
}