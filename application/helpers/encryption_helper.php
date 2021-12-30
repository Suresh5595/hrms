<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (! function_exists('encryption')) {


 // 	function encrypt($data) {
 // 		$key=key;
 //   		if(16 !== strlen($key)) $key = hash('MD5', $key, true);
 //   		$padding = 16 - (strlen($data) % 16);
 //   		$data .= str_repeat(chr($padding), $padding);
 //   		return  base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, str_repeat("\0", 16)));
 // 	}

 // 	function decrypt($data) {
 // 		$key=key;
 //  		$data = base64_decode($data);
 //  		if(16 !== strlen($key)) $key = hash('MD5', $key, true);
 //  		$data = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, str_repeat("\0", 16));
 //  		$padding = ord($data[strlen($data) - 1]); 
 //  		return substr($data, 0, -$padding); 
	// }

	    function encrypt($string) {

        $output = false;
        $encrypt_method = "AES-256-CBC";
        $secret_key = key;
        $secret_iv = key;

        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
        
        return $output;

    }

    function decrypt($string) {

        $output = false;
        $encrypt_method = "AES-256-CBC";
        $secret_key = key;
        $secret_iv = key;

        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);

        return $output;

    }

    function digits_set($data)
    {
        // get main CodeIgniter object
        $ci = get_instance();
       
        // Write your logic as per requirement
        return sprintf("%'.04d", $data);
    }

    function date_set($data){
    	 // get main CodeIgniter object
       
        $CI = get_instance();
        $CI->load->library('session');
       
        // Write your logic as per requirement
        return date($CI->session->userdata('date_setting_format')->date_format, strtotime($data));
    	
    }

    function viewme($data)
    {
       // return echo "<pre>". htmlspecialchars(print_r($data, true)). "</pre>";
    }

    function num_format_set($number){

        $CI = get_instance();
        $CI->load->library('session');

        $condion_check = $CI->session->userdata('date_setting_format')->number_format;
        
            if($condion_check == 'india_format'){
                setlocale(LC_MONETARY, 'en_IN');
                return money_format('%!i', $number);
            }elseif ($condion_check == 'dollar_format') {
                setlocale(LC_MONETARY, 'en_US');
                return money_format('%!i', $number);
            }elseif ($condion_check == 'normal_format') {
                return $number;
            }

    }

}
