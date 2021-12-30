<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
class Common1_model extends CI_Model {
    public function checkLeave($params){
        //$sql = "SELECT * FROM `hrms_leave` WHERE `staff_id_fk` = ".$params['staff_id_fk']." AND `fromdate` =>"
    }

    public function getLeaveList($params){
        $sql = "SELECT * FROM `hrms_leave` INNER JOIN `hrms_staffmaster` ON `hrms_leave`.`staff_id_fk` = `hrms_staffmaster`.`id` WHERE (`fwd1` = ".$params['staff_id_fk']." and `fwd_1_status` = 0) OR (`fwd2` = ".$params['staff_id_fk']." and `fwd_2_status` = 0 and `fwd_1_status` = 1) OR (`fwd3` = ".$params['staff_id_fk']." and `fwd_3_status` = 0 and `fwd_2_status` = 1 and `fwd_1_status` = 1) OR (`fwd4` = ".$params['staff_id_fk']." and `fwd_4_status` = 0 and `fwd_3_status` = 1 and `fwd_2_status` = 1 and `fwd_1_status` = 1)";
        return $this->db->query($sql)->result();
    }
    public function getDelegationList($params){
        $sql = "SELECT * FROM `hrms_delegation` INNER JOIN `hrms_staffmaster` ON `hrms_delegation`.`staff_id_fk` = `hrms_staffmaster`.`id` WHERE (`fwd1` = ".$params['staff_id_fk']." and `fwd_1_status` = 0) OR (`fwd2` = ".$params['staff_id_fk']." and `fwd_2_status` = 0 and `fwd_1_status` = 1) OR (`fwd3` = ".$params['staff_id_fk']." and `fwd_3_status` = 0 and `fwd_2_status` = 1 and `fwd_1_status` = 1) OR (`fwd4` = ".$params['staff_id_fk']." and `fwd_4_status` = 0 and `fwd_3_status` = 1 and `fwd_2_status` = 1 and `fwd_1_status` = 1)";
        return $this->db->query($sql)->result();
    }

    public function getPermissionList($params){
        $sql = "SELECT * FROM `hrms_permission` INNER JOIN `hrms_staffmaster` ON `hrms_permission`.`staff_id_fk` = `hrms_staffmaster`.`id` WHERE (`fwd1` = ".$params['staff_id_fk']." and `fwd_1_status` = 0) OR (`fwd2` = ".$params['staff_id_fk']." and `fwd_2_status` = 0 and `fwd_1_status` = 1) OR (`fwd3` = ".$params['staff_id_fk']." and `fwd_3_status` = 0 and `fwd_2_status` = 1 and `fwd_1_status` = 1) OR (`fwd4` = ".$params['staff_id_fk']." and `fwd_4_status` = 0 and `fwd_3_status` = 1 and `fwd_2_status` = 1 and `fwd_1_status` = 1)";
        return $this->db->query($sql)->result();
    }

    public function getNightshiftList($params){
        $sql = "SELECT * FROM `hrms_nightshift` INNER JOIN `hrms_staffmaster` ON `hrms_nightshift`.`staff_id_fk` = `hrms_staffmaster`.`id` WHERE (`fwd1` = ".$params['staff_id_fk']." and `fwd_1_status` = 0) OR (`fwd2` = ".$params['staff_id_fk']." and `fwd_2_status` = 0 and `fwd_1_status` = 1) OR (`fwd3` = ".$params['staff_id_fk']." and `fwd_3_status` = 0 and `fwd_2_status` = 1 and `fwd_1_status` = 1) OR (`fwd4` = ".$params['staff_id_fk']." and `fwd_4_status` = 0 and `fwd_3_status` = 1 and `fwd_2_status` = 1 and `fwd_1_status` = 1)";
        return $this->db->query($sql)->result();
    }

    public function getRequestList($params){
        $sql =" SELECT * FROM `hrms_emp_request` WHERE 1=1";
        if($params['staff_id_fk'] != "ALL"){
            $sql .=" AND `staff_id_fk` = ".$params['staff_id_fk'];
        }

		if($params['from_date']){
           $sql .=" AND date(`date_added`) >='".date('Y-m-d',strtotime($params['from_date']))."'";
        }

		if($params['to_date']){
           $sql .=" AND date(`date_added`) <='".date('Y-m-d',strtotime($params['to_date']))."'";
        }

        return $this->db->query($sql)->result();
    }

	public function getReferralList($params){
        $sql =" SELECT * FROM `hrms_emp_referral` WHERE 1=1";
        if($params['staff_id_fk'] != "ALL"){
            $sql .=" AND  `staff_id_fk` = ".$params['staff_id_fk'];
        }

		if($params['from_date']){
           $sql .=" AND date(`date_added`) >='".date('Y-m-d',strtotime($params['from_date']))."'";
        }

		if($params['to_date']){
           $sql .=" AND date(`date_added`) <='".date('Y-m-d',strtotime($params['to_date']))."'";
        }

        return $this->db->query($sql)->result();
    }

    public function getLeaveTracker($params){
        $sql = "SELECT * FROM hrms_".$params['type']." WHERE 1=1";
        if($params['from_date']){
            if($params['type'] == 'leave'){
                $sql .=" AND date(`from_date`) >='".date('Y-m-d',strtotime($params['from_date']))."'";
            }else{
                $sql .=" AND date(`date`) >='".date('Y-m-d',strtotime($params['from_date']))."'";
            }
        }

        if($params['to_date']){
            if($params['type'] == 'leave'){
                $sql .=" AND date(`to_date`) <='".date('Y-m-d',strtotime($params['to_date']))."'";
            }else{
                $sql .=" AND date(`date`) <='".date('Y-m-d',strtotime($params['to_date']))."'";
            }
        }
        return $this->db->query($sql)->result();
    }

    public function getMobileLeaveList($params){     
        $sql = "SELECT a.id as emp_id,a.name,a.biometricAccess,a.profileimage,a.fwd1,a.fwd2,a.fwd3,a.fwd4,a.designation_id,b.type,b.duration,b.reason,b.from_date,b.to_date,b.leave_id,b.fwd_1_status,b.fwd_2_status,b.fwd_3_status,b.fwd_4_status,b.main_status,b.date_added
        FROM `hrms_staffmaster` as a
        INNER JOIN `hrms_leave` as b ON b.staff_id_fk = a.biometricAccess 
        WHERE a.biometricAccess =".$params['staff_id_fk']." AND b.main_status!=2 ORDER BY leave_id desc";                      
        return $this->db->query($sql)->result();
    }

    public function getMobilePermissionList($params){
        $sql = "SELECT a.id as emp_id,a.name,a.biometricAccess,a.profileimage,a.fwd1,a.fwd2,a.fwd3,a.fwd4,a.designation_id,b.date,b.reason,b.fromtime,b.totime,b.duration,b.permission_id,b.fwd_1_status,b.fwd_2_status,b.fwd_3_status,b.fwd_4_status,b.main_status,b.date_added
        FROM `hrms_staffmaster` as a
        INNER JOIN `hrms_permission` as b ON b.staff_id_fk = a.biometricAccess 
        WHERE a.biometricAccess =".$params['staff_id_fk']." AND b.main_status!=2 ORDER BY permission_id desc";                      
        return $this->db->query($sql)->result();
    }

    public function getMobileNightshiftList($params){
        $sql = "SELECT a.id as emp_id,a.name,a.biometricAccess,a.profileimage,a.fwd1,a.fwd2,a.fwd3,a.fwd4,a.designation_id,b.date,b.purpose,b.fromtime,b.totime,b.nightshift_id,b.duration,b.fwd_1_status,b.fwd_2_status,b.fwd_3_status,b.fwd_4_status,b.main_status,b.date_added
        FROM `hrms_staffmaster` as a
        INNER JOIN `hrms_nightshift` as b ON b.staff_id_fk = a.biometricAccess 
        WHERE a.biometricAccess =".$params['staff_id_fk']." AND b.main_status!=2 ORDER BY nightshift_id desc";                      
        return $this->db->query($sql)->result();
    }

    public function getMobileDelegationList($params){
        $sql = "SELECT a.id as emp_id,a.name,a.biometricAccess,a.profileimage,a.fwd1,a.fwd2,a.fwd3,a.fwd4,a.designation_id,b.date,b.purpose,b.fromtime,b.totime,b.delegation_id,b.duration,b.client,b.venue,b.location,b.fwd_1_status,b.fwd_2_status,b.fwd_3_status,b.fwd_4_status,b.main_status,b.date_added
        FROM `hrms_staffmaster` as a
        INNER JOIN `hrms_delegation` as b ON b.staff_id_fk = a.biometricAccess 
        WHERE a.biometricAccess =".$params['staff_id_fk']." AND b.main_status!=2 ORDER BY delegation_id desc";                      
        return $this->db->query($sql)->result();
    }

    public function getApproveLeaveList($params){
        $sql = "SELECT * FROM `hrms_leave` INNER JOIN `hrms_staffmaster` ON `hrms_leave`.`staff_id_fk` = `hrms_staffmaster`.`biometricAccess` WHERE (`fwd1` = ".$params['staff_id_fk']." and `fwd_1_status` = 0 AND main_status=0) OR (`fwd2` = ".$params['staff_id_fk']." and `fwd_2_status` = 0 and `fwd_1_status` = 1 AND main_status=0) OR (`fwd3` = ".$params['staff_id_fk']." and `fwd_3_status` = 0 and `fwd_2_status` = 1 and `fwd_1_status` = 1 AND main_status=0) OR (`fwd4` = ".$params['staff_id_fk']." and `fwd_4_status` = 0 and `fwd_3_status` = 1 and `fwd_2_status` = 1 and `fwd_1_status` = 1 AND main_status=0)";
     return $this->db->query($sql)->result();
    }


    public function getApprovePermissionList($params){
        $sql = "SELECT * FROM `hrms_permission` INNER JOIN `hrms_staffmaster` ON `hrms_permission`.`staff_id_fk` = `hrms_staffmaster`.`biometricAccess` WHERE (`fwd1` = ".$params['staff_id_fk']." and `fwd_1_status` = 0 AND main_status=0) OR (`fwd2` = ".$params['staff_id_fk']." and `fwd_2_status` = 0 and `fwd_1_status` = 1 AND main_status=0) OR (`fwd3` = ".$params['staff_id_fk']." and `fwd_3_status` = 0 and `fwd_2_status` = 1 and `fwd_1_status` = 1 AND main_status=0) OR (`fwd4` = ".$params['staff_id_fk']." and `fwd_4_status` = 0 and `fwd_3_status` = 1 and `fwd_2_status` = 1 and `fwd_1_status` = 1 AND main_status=0)";
     return $this->db->query($sql)->result();
    }

    public function getApproveNightshiftList($params){
        $sql = "SELECT * FROM `hrms_nightshift` INNER JOIN `hrms_staffmaster` ON `hrms_nightshift`.`staff_id_fk` = `hrms_staffmaster`.`biometricAccess` WHERE (`fwd1` = ".$params['staff_id_fk']." and `fwd_1_status` = 0 AND main_status=0) OR (`fwd2` = ".$params['staff_id_fk']." and `fwd_2_status` = 0 and `fwd_1_status` = 1 AND main_status=0) OR (`fwd3` = ".$params['staff_id_fk']." and `fwd_3_status` = 0 and `fwd_2_status` = 1 and `fwd_1_status` = 1 AND main_status=0) OR (`fwd4` = ".$params['staff_id_fk']." and `fwd_4_status` = 0 and `fwd_3_status` = 1 and `fwd_2_status` = 1 and `fwd_1_status` = 1 AND main_status=0)";
     return $this->db->query($sql)->result();
    }

    public function getApproveDelegationList($params){
        $sql = "SELECT * FROM `hrms_delegation` INNER JOIN `hrms_staffmaster` ON `hrms_delegation`.`staff_id_fk` = `hrms_staffmaster`.`biometricAccess` WHERE (`fwd1` = ".$params['staff_id_fk']." and `fwd_1_status` = 0 AND main_status=0) OR (`fwd2` = ".$params['staff_id_fk']." and `fwd_2_status` = 0 and `fwd_1_status` = 1 AND main_status=0) OR (`fwd3` = ".$params['staff_id_fk']." and `fwd_3_status` = 0 and `fwd_2_status` = 1 and `fwd_1_status` = 1 AND main_status=0) OR (`fwd4` = ".$params['staff_id_fk']." and `fwd_4_status` = 0 and `fwd_3_status` = 1 and `fwd_2_status` = 1 and `fwd_1_status` = 1 AND main_status=0)";
     return $this->db->query($sql)->result();
    }

}