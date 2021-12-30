<?php
defined('BASEPATH') OR exit('No direct script access allowed');
if(!function_exists('get_val'))
{
	function get_val($get,$wf,$wv,$tbl)
	{
		$CI=get_instance();
		$CI->load->model('common_model');
		$wr=array($wf=>$wv);
		return $CI->common_model->get_val($get,$wr,$tbl);
	}
}


if(!function_exists('main_status'))
{
	function main_status($status=0)
	{
		$main_status=array(0=>'Pending',1=>'Approved',2=>'Canceled',3=>'Rejected');
		return $main_status[$status];
	}
}

if(!function_exists('current_status'))
{
	function current_status($fwd_4_status=0,$fwd_3_status=0,$fwd_2_status=0,$fwd_1_status=0,$fwd4='',$fwd3='',$fwd2='',$fwd1='')
	{					
		if($fwd_4_status!=0)
		{																	
		    $status='4 - '.main_status($fwd_4_status).' by '.get_val('name','id',get_val('designation_id','biometricAccess',$fwd4,'hrms_staffmaster'),'hrms_designation');						
		}
		else if($fwd_3_status!=0 && $fwd_4_status==0)
		{																		
			$status='3 - '.main_status($fwd_3_status).' by '.get_val('name','id',get_val('designation_id','biometricAccess',$fwd3,'hrms_staffmaster'),'hrms_designation');						
		}
		else if($fwd_2_status!=0 && $fwd_3_status==0 && $fwd_4_status==0)
		{																		
			$status='2 - '.main_status($fwd_2_status).' by '.get_val('name','id',get_val('designation_id','biometricAccess',$fwd2,'hrms_staffmaster'),'hrms_designation');						
		}
		else if($fwd_1_status!=0 && $fwd_2_status==0 && $fwd_3_status==0 && $fwd_4_status==0)
		{												
			$status='1 - '.main_status($fwd_1_status).' by '.get_val('name','id',get_val('designation_id','biometricAccess',$fwd1,'hrms_staffmaster'),'hrms_designation');						
		}								
		else
		{									
			$status='Pending';
		}
		return $status;
	}
}
?>