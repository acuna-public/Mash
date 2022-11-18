<?php
	
	function curl_query ($data, $curl = null, $debug = 0): array {
		
		if (!$curl) $curl = new Curl ();
		
		$curl->debug = $debug;
		
		foreach ($data as $data) {
			
			$data2 = array_keys_extend ($data, ['method', 'url', 'params', 'options']);
			
			$curl->setData ($data2);
			
			if ($data2['options'])
				$curl->setOptions ($data2['options']);
			
		}
		
		return $curl->process ();
		
	}
	
	function url_get_item ($url, $params = [], $options = [], $curl = null): Curl\Item {
		return curl_query ([['get', $url, $params, $options]], $curl)[0];
	}
	
	function url_get_content ($url, $params = [], $options = []) {
		return url_get_item ($url, $params, $options)->getContent ();
	}
	
	function url_get_array ($url, $params = [], $options = []) {
		return json2array (url_get_content ($url, $params, $options));
	}
	
	function url_get_xml ($url, $params = [], $options = []) {
		return simplexml_load_string (url_get_content ($url, $params, $options));
	}
	
	function str_get_html ($str, $charset_to = '', $charset_from = 'utf-8') {
		
		if ($charset_to)
			$str = mb_convert_encoding ($str, $charset_from, $charset_to);
		
		return new HTMLDocument ($str);
		
	}
	
	function file_get_html ($str, $charset_to = '', $charset_from = 'utf-8') {
		return str_get_html (file_get_contents ($str), $charset);
	}
	
	function url_get_html ($str, $params = [], $options = []) {
		return str_get_html (url_get_content ($str, $params, $options));
	}