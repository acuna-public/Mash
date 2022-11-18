<?php
/*
 ========================================
 Mash Framework (c) 2010-2017
 ----------------------------------------
 https://mash.ointeractive.ru/
 ========================================
 O! Interactive (support@ointeractive.ru)
 ----------------------------------------
 Библиотека
 -- Шифрование
 ========================================
*/
	
	if (!defined ('MASH')) die ('File must be started only through the main framework cover');
	
	function mksecret ($length = 20) {
		
		$salt = ['a','A','b','B','c','C','d','D','e','E','f','F','g','G','h','H','i','I','j','J','k','K','l','L','m','M','n','N','o','O','p','P','q','Q','r','R','s','S','t','T','u','U','v','V','w','W','x','X','y','Y','z','Z','1','2','3','4','5','6','7','8','9'];
		
		$string = '';
		
		for ($i = 1; $i <= $length; ++$i) {
			$ch = rand (0, count ($salt) - 1);
			$string .= $salt[$ch];
		}
		
		return $string;
		
	}
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Работа с шифрованными туннелями
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	$tunnel_sep = ['|', '<>', '::'];
	
	function _tunnel2array ($data, $decode, $debug) {
		global $tunnel_sep;
		
		$output = [];
		$data = explode ($tunnel_sep[2], $data);
		
		foreach ($data as $data) {
			
			$data = trim ($data, $tunnel_sep[0]);
			$data = explode ($tunnel_sep[0], $data);
			
			$i = 0;
			$output2 = [];
			
			foreach ($data as $data) {
				
				$data = explode ($tunnel_sep[1], $data);
				
				if (isset ($data[1]))
				$output2[trim ($data[0])] = trim ($data[1]);
				else
				$output2[$i] = trim ($data[0]);
				
				++$i;
				
			}
			
			$output[] = $output2;
			
		}
		
		if (!isset ($output[1])) $output = $output[0];
		
		return $output;
		
	}
	
	function tunnel2array ($data, $decode = 0, $debug = 0) {
		global $tunnel_sep;
		
		if ($decode) {
			
			if ($decode == 1) $data = base64_decode ($data);
			$data = url_decode ($data);
			
		}
		
		return _tunnel2array ($data, $decode, $debug);
		
	}
	
	function tunnel2array_file ($file, $decode = 0) {
		
		$strings = file2array ($file);
		
		$output = [];
		foreach ($strings as $str)
		$output[] = tunnel2array ($str, $decode);
		
		return $output;
		
	}
	
	function _array2tunnel ($key, $value, $sep) {
		return safe_array2tunnel ($key, $sep).$sep[1].safe_array2tunnel ($value, $sep);
	}
	
	function safe_array2tunnel ($string, $sep) {
		return str_replace ([$sep[0], $sep[1]], '', trim ($string));
	}
	
	function array2tunnel ($data, $encode = 0, $search = 0, $debug = 0) {
		global $tunnel_sep;
		
		$data2 = [];
		$output = [];
		
		if (is_array ($data))
		foreach ($data as $key => $value) {
			
			if (is_array ($value)) {
				
				$output = [];
				foreach ($value as $key2 => $value2)
				$output[] = _array2tunnel ($key2, $value2, $tunnel_sep);
				
				$data2[] = implode ($tunnel_sep[0], $output);
				
			} else $output[] = _array2tunnel ($key, $value, $tunnel_sep);
			
		}
		
		if (is_array ($value))
		$data = implode ($tunnel_sep[2], $data2);
		else
		$data = implode ($tunnel_sep[0], $output);
		
		if ($search) $data = $tunnel_sep[0].$data.$tunnel_sep[0];
		
		if ($encode) {
			
			$data = url_encode ($data);
			if ($encode == 1) $data = base64_encode ($data);
			
		}
		
		return $data;
		
	}
	
	function explode_options ($data) {
		return tunnel2array ($data);
	}
	
	function implode_options ($data) {
		return array2tunnel ($data);
	}
	
	function explode_options_file ($file) {
		return tunnel2array_file ($file);
	}
	
	function is_method ($method) {
		return ($_SERVER['REQUEST_METHOD'] === strtoupper ($method));
	}
	
	function preg ($what) {
		return "'\[".$what."\](.*?)\[/".$what."\]'si";
	}
	
	function file_str_len ($url) {
		
		$data = @file_get_content ($url);
		if ($data) $data = lisas_strlen ($data); else $data = 0;
		return $data;
		
	}
	
	function get_hash ($length = 32) {
		return do_rand ($length, 2);
	}
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// LisaS Secure Algorhytm (LSA)
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	function lsa1 ($str, $user_id, $salt = '', $is_on = 1) { // Невозвратимо шифрует с солью $salt строку $string.
		
		if (!$salt) $salt = lsa1_salt ();
		
		if ($is_on)
		$str = sha1 (sha1 ($str).$user_id.$salt);
		else
		$str = sha1 (sha1 ($str));
		
		return $str;
		
	}
	
	function lsa1_salt () { // Формирует соль для lsa1 ();
		return do_rand (6, 2);
	}
	
	function lsa2_encode ($str) {
		
		$str = strrev ($str);
		$str = base64_encode (base64_encode ($str));
		
		return $str;
		
	}
	
	function lsa2_decode ($str) {
		
		$str = base64_decode (base64_decode ($str));
		$str = strrev ($str);
		
		return $str;
		
	}
	
	function base64tolsa2_encode ($str) {
		
		$str = base64_decode ($str);
		$str = lsa2_encode ($str);
		
		return $str;
		
	}
	
	function key_pad ($key) {
		
		if (strlen ($key) > 32) return false;
		
		$sizes = [16, 24, 32];
		
		foreach ($sizes as $s) {
			
			while (strlen ($key) < $s) $key = $key."\0";
			if (strlen ($key) == $s) break;
			
		}
		
		return $key;
		
	}
	
	function hex_key_length ($key, $method) {
		
		$methods = [
			
			'AES-128-CBC' => hash ('sha128', $key),
			'AES-256-CBC' => hash ('sha256', $key),
			
		];
		
		if (!($enkey = $methods[$method]))
		$enkey = $key;
		
		return strlen ($enkey);
		
	}
	
	define ('MCRYPT_RAW_DATA', 1);
	
	function hex_encrypt ($data, $key = '', $options = MCRYPT_RAW_DATA, $method = MCRYPT_RIJNDAEL_128, $hash = '') {
		
		$iv_size = @mcrypt_get_iv_size ($method, MCRYPT_MODE_CBC);
		
		$iv = '';
		foreach (range (1, 16) as $i) $iv .= 'a';
		
		$encrypt = @mcrypt_encrypt ($method, md5 ($key), $data, MCRYPT_MODE_CBC, $iv);
		if ($options != MCRYPT_RAW_DATA) $encrypt = base64_encode ($encrypt);
		
		return $encrypt;
		
	}
	
	function hex_decrypt ($data, $key = '', $options = MCRYPT_RAW_DATA, $method = MCRYPT_RIJNDAEL_128, $hash = '') {
		
		if ($options != MCRYPT_RAW_DATA) $data = base64_decode ($data);
		
		$iv_size = @mcrypt_get_iv_size ($method, MCRYPT_MODE_CBC);
		
		$iv = '';
		foreach (range (1, 16) as $i) $iv .= 'a';
		
		return trim (@mcrypt_decrypt ($method, md5 ($key), $data, MCRYPT_MODE_CBC, $iv));
		
	}