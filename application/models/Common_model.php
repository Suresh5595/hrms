<?php
    if (!defined('BASEPATH')) exit('No direct script access allowed');
    class Common_model extends CI_Model {
        public function insert_data($spare, $table_name) {
            $this->db->insert($table_name, $spare);
            return $this->db->insert_id();
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

        public function get_val($select,$array,$table) {
            $this->db->select($select);
            $this->db->where($array);
            $this->db->from($table);
            $query = $this->db->get();
            if($query->num_rows()==1){
                $data=$query->row_array();
                return $value=$data[$select];
            }else{
                return '';
            }
        }

        public function query($qry)
        {
            $query = $this->db->query($qry);            
            if($query->num_rows()>0)
            {
                return $query->result_array();
            }
            else
            {
                return false;
            }
        }

        public function update($arr,$table,$value)
        {
            $this->db->where($arr);
            $this->db->update($table, $value); 
        }
    }