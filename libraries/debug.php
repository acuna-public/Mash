<?php
/*
 ========================================
 Mash Framework (c) 2010-2017, 2020
 ----------------------------------------
 https://mash.ointeractive.ru/
 ========================================
 O! Interactive (support@ointeractive.ru)
 ----------------------------------------
 -- Библиотеки
 --- Работа со строками
 ========================================
*/
	
	if (!defined ('MASH')) die ('File must be started only through the main framework cover');
	
	define ('_LINE_', '----------------------------------------------');
	define ('NBSP', '&nbsp;&nbsp;');
	
	function timer_start () { // Запускает таймер для измерения времени работы скрипта
		return _timer_init ();
	}
	
	define ('TIME_START', timer_start ());
	
	function debug_write_cont ($mess, $file = 'result', $type = 'log', $debug = 0) {
		
		$file_name = _debug_file ($file, $type, $debug);
		if ($debug) debug ($file_name);
		
		$file = fopen ($file_name, 'a');
		
		$mess = str_replace ('&nbsp;', '	', $mess);
		
		fwrite ($file, $mess.NL);
		fclose ($file);
		
	}
	
	function mess2br ($mess, $sep = BR) {
		return implode ($sep, $mess);
	}
	
	function lisas_log ($mess, $file = 'result', $sep = NL, $title = '') {
		
		if (is_array ($mess)) $mess = mess2br ($mess, $sep);
		debug_write_cont (log_text ($mess, $title), $file);
		
	}
	
	function log_text ($mess = '', $title = '') {
		
		if (!$title) $title = log_date ();
		
		$output = _LINE_.'
'.$title.'
'._LINE_.'

';
		
		if ($mess) $output .= strip_tags (br2nl ($mess)).NL.NL;
		
		return $output;
		
	}
	
	function log_date ($date = 'd.m.Y H:i:s') {
		return date ($date);
	}
	
	function debug_write ($message = '', $file = 'result', $type = 'log', $debug = 0) {
		
		if (is_array ($message)) $message = print_a ($message);
		
		if (!not_empty ($message, 0)) $message = NL;
		if (!$file or $file == 1) $file = LISAS_DATE.'_'.do_rand (3, 1).'.'.$type;
		
		debug_write_cont ($message, $file, $type, $debug);
		
		return $message;
		
	}
	
	function debug_incr ($file = 'result', $start = 0) {
		
		$file = _debug_file ($file, 'log');
		
		$i = intval_correct (file_get_content ($file), $start);
		++$i;
		
		file_put_contents ($file, $i);
		
		return $i;
		
	}
	
	function _debug_file ($file, $type, $debug = 0) {
		
		make_dir (ROOT_DIR.'/logs');
		$file = dash_filepath (ROOT_DIR.'/logs/'.$file.'.'.$type);
		if ($debug) debug ($file);
		
		return $file;
		
	}
	
	function del_log ($file, $type = 'log', $debug = 0) {
		
		$file = _debug_file ($file, $type, $debug);
		if (file_exists ($file)) unlink ($file);
		
	}
	
	function _debug_content ($file, $debug = 0) {
		return file_get_content (_debug_file ($file, 'log', $debug));
	}
	
	function _timer_init () { // Инициализирует таймер для измерения времени работы скрипта
		
		list ($sec, $m_sec) = explode (' ', microtime ());
		return ((float) $m_sec + (float) $sec);
		
	}
	
	function timer_stop ($time_start, $file = '') { // Останавливает таймер и выводит время работы скрипта, полученное из timer_start ().
		
		$output = (_timer_init () - $time_start);
		if ($file) $output .= ' in <b>'.$file.'</b>.';
		
		return $output;
		
	}
	
	function debug_mess_implode ($mess, $sep = ' - ') {
		
		if (is_array ($mess)) $mess = implode ($sep, $mess);
		return $mess;
		
	}
	
	if (!defined ('LISAS_FRAMEWORK_DEBUG_STYLE'))
	define ('LISAS_FRAMEWORK_DEBUG_STYLE', 'color:red;');
	
	function debug_time ($mess = '', $file = '') {
		debug ($mess.' ('.work_time (2).')', $file);
	}
	
	function debug ($mess = '', $file = '') {
		global $_ARG;
		
		if (is_array ($mess)) $mess = print_a ($mess);
		$mess = parse_debug_mess ($mess);
		
		if (!$_ARG and !defined ('EMBEDED') and !defined ('CLI')) {
			
			//if ($file) $mess .= ' in '.$file;
			echo '<span class="debug-mess">// '.$mess.'</span><br/>
';
			
			flush ();
			ob_flush ();
			
		} else {
			
			echo '-- '.$mess.NL;
			
			flush ();
			ob_flush ();
			
		}
		
	}
	
	function parse_debug_mess ($mess) {
		
		$mess = lisas_nl2br ($mess, 0);
		$mess = str_replace (["\t", '<br/>'.NL, '	'], ['&nbsp;&nbsp;&nbsp;', '<br/>'.NL, '&nbsp;&nbsp;&nbsp;'], $mess);
		
		return $mess;
		
	}
	
	function print_a ($array) {
		return array2json ($array, defined ('CLI') ? 0 : JSON_PRETTY_PRINT);
	}
	
	/*$types = [
		
		'options' => [
			
			'links_new_window',
			'big_font',
			
		],
		
		'notify' => [
			
			['ggg' => ['111']],
			
		],
		
		'rights' => [],
		
	];*/
	
	function _debug_html ($mess) {
		
		$mess = spech_encode ($mess);
		$mess = parse_debug_mess ($mess);
		
		return $mess;
		
	}
	
	function debug_html ($mess) {
		debug (_debug_html ($mess));
	}
	
	function debug_html_file ($file) {
		debug_html (file_get_content ($file));
	}
	
	function work_time ($round = 0) {
		
		$time = timer_stop (TIME_START);
		if ($round) $time = round ($time, $round);
		
		return $time;
		
	}
	
	function finish_time ($file = '') {
		
		$array = round (work_time (), 3);
		if ($file) $array .= ' - '.$file;
		
		debug ($array);
		
	}