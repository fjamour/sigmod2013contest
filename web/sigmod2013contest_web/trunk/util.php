<?php
/*
 * Copyright (C) 2013 by
 * Fuad Jamour <fjamour@gmail.com>
 * All rights reserved.
 * MIT license.
*/
?>

<?php
	function validate_name($team_name, &$err_msg)
	{
		if(preg_match('/^[a-zA-Z0-9_]{3,32}$/', $team_name)) {
			return true;
		} else {
			$err_msg = 'Team name should be a string of alphanumerics between 3 and 32 characters long.';
			return false;
		}
	}

	function validate_email($email, &$err_msg)
	{
		if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return true;
		} else {
			$err_msg = 'Please enter a valid email address.';
			return false;
		}
	}

	function validate_instit($instit, &$err_msg)
	{
		if(preg_match('/^[a-zA-Z0-9_ ()]{3,64}$/', $instit)) {
			return true;
		} else {
			$err_msg = 'Please enter a valid institution name.';
			return false;
		}
	}

	function validate_country($country, &$err_msg)
	{
		if(preg_match('/^[a-zA-Z ]{2,64}$/', $country)) {
			return true;
		} else {
			$err_msg = 'Please choose a country from the list.';
			return false;
		}
	}

	function validate_password($password, $password2, &$err_msg)
	{
		if(strlen($password) >= 6 && strlen($password) <= 32) {
			if($password == $password2) {
				return true;
			} else {
				$err_msg = 'Passwords you entered do not match.';
				return false;
			}
		} else {
			$err_msg = 'Password should be between 6 and 32 characters long';
			return false;
		}
	}

	function validate_lib_filename($file, &$err_msg)
	{
		$pass_name  = $file['name']  == 'libcore.so' || $file['name']  == 'impl.tar.gz';
		$pass_err   = $file['error'] == 0;
		$pass_size  = $file['size']  < 2097152;//max file size is 2MB

		if(!$pass_err)
			$err_msg = 'Error while uploading the file!';
		if(!$pass_name)
			$err_msg = 'The file name should be <strong>libcore.so</strong> or <strong>impl.tar.gz</strong>.';
		if(!$pass_size)
			$err_msg = 'The file is too big!';

		return ($pass_name && $pass_err && $pass_size);

	}

	function validate_submission_notes($notes, &$err_msg)
	{
		/*Should not really be very strict here, the db script will sanitize the string anyway*/
		if(strlen($notes) <= 256) {
			return true;
		} else {
			$err_msg = 'Notes should not exceed 256 character.';
			return false;
		}
	}

	function validate_timestamp($timestamp)
	{
		return preg_match('/^[0-9]{10}$/', $timestamp);
	}
   
	$salt ='rand_nun';
	function simple_encrypt($text)
	{
		return openssl_encrypt($text, 'AES-128-CBC', $salt);
	}

	function simple_decrypt($text)
	{
		return openssl_decrypt($text, 'AES-128-CBC', $salt);
	}

	function encrypt_timestamp($timestamp, $type)
	{
		$t = '';
		if($type == 'lib')
			$t = 's';
		elseif($type == 'log')
			$t = 'l';
		$enc = simple_encrypt($t . $timestamp);
		return $enc;
	}

	function decrypt_timestamp($enc_timestamp, &$timestamp, &$type)
	{
		$dec = simple_decrypt($enc_timestamp);
		if(strlen($dec) != 11)
			return false;
		if(substr($dec, 0, 1) != 's' && substr($dec, 0, 1) != 'l')
			return false;
		if(!is_numeric(substr($dec, 1)))
			return false;
		if(intval(substr($dec, 1)) < 1357567683 || intval(substr($dec, 1)) > 2357567683)
			return false;

		if(substr($dec, 0, 1) == 's')
			$type = 'lib';
		elseif(substr($dec, 0, 1) == 'l')
			$type = 'log';
		$timestamp = substr($dec, 1);
		return true;
	}
	
	//http://stackoverflow.com/questions/1634782/what-is-the-most-accurate-way-to-retrieve-a-users-correct-ip-address-in-php
	function get_ip_address()
	{
		foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
		    if (array_key_exists($key, $_SERVER) === true){
		        foreach (explode(',', $_SERVER[$key]) as $ip){
		            $ip = trim($ip); // just to be safe
		            return $ip;
		            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
		                return $ip;
		            }
		        }
		    }
		}
	}
?>
