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
 -- Генераторы
 ========================================
*/
	
	if (!defined ('MASH')) die ('File must be started only through the main framework cover');
	
	define ('LISAS_FUNCTIONS', true);
	define ('KEY_PART_LENGTH', 6);
	define ('KEY_PART_NUM', 5);
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Работа с библиотеками
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	function support_library ($lib) {
		
		if (!function_exists ($lib))
		die ('The library '.$lib.' is not supported by your server!');
		else return true;
		
	}
	
	function mod_rewrite () {
		
		if (function_exists ('apache_get_modules')) {
			
			if (array_search ('mod_rewrite', apache_get_modules ())) $mod_rewrite = 1; else $mod_rewrite = 0;
			
		} else $mod_rewrite = 0;
		
		return $mod_rewrite;
		
	}
	
	function gd_about () {
		
		$output = [];
		
		if (function_exists ('gd_info')) {
			
			foreach (gd_info () as $key => $value) {
				
				if ($value) $value = 'Enabled'; else $value = 'Disabled';
				$output[] = $key.': '.$value;
				
			}
			
			$output = ', '.sep_implode ($output);
			
		}
		
		return $output;
		
	}
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Функции измерения времени работы
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	function measure_function_time ($v = '47846', $precision = 4, $loop_num = 1000) {
		
		$int = 1;
		
		$s = microtime (true);
		for ($i = 0; $i < $loop_num; ++$i) $int = intval ($v);
		$time_1 = (microtime (true) - $s);
		
		$s = microtime (true);
		for ($i = 0; $i < $loop_num; ++$i) $int = (int) $v;
		$time_2 = (microtime (true) - $s);
		
		$time_faster_value = $time_1 - $time_2;
		
		$output = '<ol>
<li>'.round ($time_1, $precision).'</li>
<li>'.round ($time_2, $precision).'</li>
</ol>
2 быстрее на: <b>'.round ($time_faster_value, $precision).'</b>';
		
		return $output;
		
	}
	
	function is_running ($file) {
		global $fh;
		
		if (!$fh) $fh = fopen ($file, 'r');
		if (flock ($fh, LOCK_EX | LOCK_NB)) return false; else return true;
		
	}
	
	function get_cpu_load ($maxload = 100, $sleep = 5) {
		
		if (stristr (PHP_OS, 'win')) {
			
			$wmi = new COM ('Winmgmts://');
			$server = $wmi->execquery ('SELECT LoadPercentage FROM Win32_Processor');
			
			$cpu_num = 0;
			$load_total = 0;
			
			foreach ($server as $cpu) {
				++$cpu_num;
				
				$load_total += $cpu->loadpercentage;
				
			}
			
			$load = round ($load_total / $cpu_num);
			
		} else {
			
			$load = sys_getloadavg ();
			
			if ($load[0] > $maxload) sleep ($sleep);
			$load = $load[0];
			
		}
		
		return (int) $load;
		
	}
	
	function count_ram () {
		
		if (function_exists ('memory_get_peak_usage')) $output = mksize (memory_get_peak_usage ()); else $output = false;
		
		return $output;
		
	}
	
	function gd_version () {
		
		ob_start ();
		phpinfo (8);
		
		$info = ob_get_contents ();
		
		ob_end_clean ();
		
		if (preg_match ("/\bgd\s+version\b[^\d\n\r]+?([\d\.]+)/i", $info, $matches)) $gd_version = $matches[1]; else $gd_version = 0;
		
		return $gd_version;
		
	}
	
	function get_constant_name ($category, $id) {
		
		$name = 0;
		
		foreach (get_defined_constants () as $key => $value)
		if (strlen ($key) > strlen ($category))
		if (substr ($key, 0, strlen ($category)) == $category)
		if ($value == $id) $name = $key;
		
		return $name;
		
	}