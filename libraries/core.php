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
 -- Ядро
 ========================================
*/
	
	if (!defined ('MASH')) die ('File must be started only through the main framework cover');
	
	function lisas_version_compare ($required) {
		
		$required = str_replace ('.', '', $required);
		$kernel = load_kernel ();
		$version = str_replace ('.', '', $this->kernel['version']);
		
		if ($version >= $required) return true; else return false;
		
	}
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Работа с серийными номерами
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	function key_gen () { // Генерирует новый серийный номер.
		
		$key = [];
		for ($i = 1; $i <= KEY_PART_NUM; ++$i) $key[] = do_rand (KEY_PART_LENGTH, 2);
		$key = implode ('-', $key);
		
		return strtoupper ($key);
		
	}
	
	function key_implode ($array) { // Собирает серийный номер из массива $array.
		
		$key = implode ('-', $array);
		return strtoupper ($key);
		
	}
	
	function key_explode ($str) { // Разбирает серийный номер в массив.
		return explode ('-', $str);
	}
	
	function is_key_length ($key) {
		
		$lenght = ((KEY_PART_NUM * KEY_PART_LENGTH) + (KEY_PART_NUM - 1));
		if (lisas_strlen ($key) == $lenght) return true; else return false;
		
	}
	
	function is_key ($key) { // Проверяет ключ $key на соответствие формату
		
		$pattern = [];
		for ($i = 1; $i <= KEY_PART_NUM; ++$i)
		$pattern[] = '[A-Z0-9]{'.KEY_PART_LENGTH.'}';
		
		return preg_match ('~'.implode ('-', $pattern).'~', $key);
		
	}
	
	function key_not_empty ($key) {
		
		$str = '';
		for ($i = 1; $i <= KEY_PART_NUM; ++$i) $str .= '-';
		if (not_empty ($key) and $key != $str) return true; else return false;
		
	}
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Работа со датами
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	function to_days ($date) {
		return 719528 + floor (strtotime ($date) / (60 * 60 * 24));
	}
	
	function get_lang () {
		
		$langs = [];
		
		if (is_isset ('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
			
			// break up string into pieces (languages and q factors)
			preg_match_all ('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);
			
			if (count ($lang_parse[1])) {
				
				// create a list like â??enâ?? => 0.8
				$langs = array_combine ($lang_parse[1], $lang_parse[4]);
				// set default to 1 for any without q factor
				foreach ($langs as $lang => $val)
				if (!$val) $langs[$lang] = 1;
				
				// sort list based on value
				arsort ($langs, SORT_NUMERIC);
				
			}
			
		}
		
		//extract most important (first)
		foreach ($langs as $lang => $val) { break; }
		//if complex language simplify it
		if (stristr($lang,"-")) {$tmp = explode("-",$lang); $lang = $tmp[0]; }
		
		return $lang;
		
	}
	
	function file_get_content ($file, $debug = 0) {
		return trim (file_get_contents ($file));
	}
	
	function file_put_content ($content, $file, $chmod = 0666, $debug = 0) {
		
		if ($chmod == 1 or $debug) debug ($file);
		if (!is_numeric ($chmod)) $type = $chmod; else $type = 'w';
		
		$handle = fopen ($file, $type);
		if ($chmod > 1) chmod ($file, $chmod);
		fwrite ($handle, $content);
		fclose ($handle);
		
		return $content;
		
	}
	
	function strip_php_tags ($str) {
		return preg_replace (['~^<(\?|\%)\=?(php)?~i', '~([\%|\?]>*)$~', '~^\r\n\t\r\n\t~', '~\r\n\t\r\n$~'], '', $str);
	}
	
	function add_php_tags ($str, $closed = 0) {
		return "<?php".NL."\t".NL."\t".trim ($str).($closed ? NL."\t".NL."?>" : '');
	}
	
	function functions_find ($content) {
		
		preg_match_all ('~[=&!<>:\(\)\[\]\{\}\^\,\.\+\-\*\|\/]*[ \t]*([a-z0-9_]{2,})\s*\(~si', $content, $match);
		return $match[1];
		
	}
	
	function is_function ($name, $content) {
		return preg_show ($content, '~\bfunction\s+'.$name.'\s*\((.*?)\)\s*\{~si', '}')[0];
	}
	
	function is_class ($name, $content) {
		return preg_show ($content, '~\bclass\s+'.$name.'\s*\{~si', '}')[0];
	}
	
	function classes_find ($content) {
		
		preg_match_all ('~[=&!<>:\(\)\[\]\{\}\^\,\.\+\-\*\|\/]\s*new\s+([a-z0-9_]+)\s*[\,;\(\)]~si', $content, $match);
		return $match[1];
		
	}
	
	function preg_pattern ($type, $name) {
		
		switch ($type) {
			
			case 'class':
				$pattern = '~\b'.$type.'\s+'.$name.'\s*\{~si';
			break;
			
			case 'function':
				$pattern = '~\b\s+'.$name.'\s*\((.*?)\)\s*\{~si';
			break;
			
		}
		
		return $pattern;
		
	}
	
	function benchmark ($funcName, $numCycles = 10000) {
		
		$time_start = microtime (true);
		
		for ($i = 0; $i < $numCycles; ++$i) {
			
			clearstatcache ();
			$funcName (__FILE__); // or 'path/to/file.php'
			
		}
		
		$time_end = microtime (true);
		$time = ($time_end - $time_start);
		
		return [$funcName, $numCycles, $time];
		
	}
	
	function prep_secs ($time) {
		
		$mins = '--';
		$secs = '--';
		
		if ($time) {
			
			$time = floor ($time);
			
			$mins = floor ($time / 60);
			$secs = floor ($time % 60);
			
			if ($mins <= 9) $mins = '0'.$mins;
			if ($secs <= 9) $secs = '0'.$secs;
			
		}
		
		return [$mins, $secs];
		
	}