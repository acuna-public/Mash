<?php
/*
 ========================================
 Mash Framework (c) 2010-2017
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
	
	function cli_exec ($cmd, $out = 0, $debug = 0) {
		
		if ($debug == 1) echo ($cmd);
		elseif ($debug == 2) lisas_log ($cmd);
		
		if ($out) {
			
			$cmd .= ' 2>&1';
			exec ($cmd, $output, $error);
			
			if (is_array ($output))
			foreach ($output as $value) echo $value.NL;
			
			//if ($error) echo NL.$error.NL;
			
		} else passthru ($cmd, $output);
		
	}
	
	function _cli_arg_value ($value, $data) {
		
		if (isset ($data))
		$value = str_replace ('^', NL, $value);
		else
		$value = true;
		
		return str2bool ($value);
		
	}
	
	function cli_get_argv (array $argv, $args = [], $start = 0) {
		//global $argv;
		
		$i = $start;
		$output = [];
		$argv = make_array ($argv);
		
		foreach ($argv as $key => $value)
		if ($key >= $start) {
			
			if ($value[0] == '-') { // Аргумент
				
				$data = explode ('=', $value);
				
				$key2 = $data[0];
				
				$value2 = '';
				foreach (range (1, (count ($data) - 1)) as $i2)
				if ($data[$i2] != $key2)
				$value2 .= $data[$i2].'='; // Собираем обратно значение, если оно вдруг имеет знак равенства
				
				$value2 = trim ($value2, '=');
				
				if (substr ($key2, 0, 2) == '--') { // Длинный
					
					$key3 = substr ($key2, 2);
					$output[$key3] = _cli_arg_value ($value2, $data[1]);
					
				} elseif (strlen (substr ($key2, 1)) == 1) { // Короткий
					
					$key3 = substr ($key2, 1);
					$output[$key3] = _cli_arg_value ($value2, $data[1]);
					
				}
				
				foreach ($args as $arg => $data2) {
					
					if ($data2['short'] and isset ($output[$arg[0]])) {
						
						$output[$arg] = $output[$arg[0]];
						unset ($output[$arg[0]]);
						
					}
					
					if (!is_isset ($arg, $output) and is_isset ('def_val', $data2))
					$output[$arg] = $data2['def_val'];
					
				}
				
			} else { // Файл
				
				$output[$i] = _cli_arg_value ($value, 1);
				if (is_numeric ($key)) ++$i;
				
			}
			
		}
		
		return $output;
		
	}
	
	//$_ARG = cli_get_argv ();
	
	function cli_is_args_empty ($debug = 0) {
		global $_ARG;
		
		$num = 0;
		foreach ($_ARG as $key => $value)
		if (!is_numeric ($key)) ++$num;
		
		if ($debug) debug ($num);
		
		return ($num ? false : true);
		
	}
	
	function cli_is_argv_file ($key, $value, $start = 0) {
		return (is_numeric ($key) and $key >= $start and $value);
	}
	
	function cli_argv_files ($start = 0) {
		global $_ARG;
		
		$files = [];
		
		foreach ($_ARG as $key => $value)
		if (cli_is_argv_file ($key, $value, $start))
		$files[] = $value;
		
		return $files;
		
	}
	
	function cli_path ($key = 0) {
		global $argv;
		
		return dirname (realpath ($argv[$key]));
		
	}
	
	function load_ini_file ($file, $debug = 0) {
		
		$strings = file2array ($file);
		if ($debug) debug ($file);
		
		$output = [];
		
		foreach ($strings as $string) {
			
			$string = explode ('=', $string);
			$output[$string[0]] = trim ($string[1]);
			
		}
		
		return $output;
		
	}
	
	function cli_system ($cmd) {
		
		$pp = proc_open ($cmd, [STDIN, STDOUT, STDERR], $pipes);
		if (!$pp) return 127;
		return proc_close ($pp);
		
	}
	
	function is_x64 () {
		return (strstr (php_uname ('m'), '64') ? true : false);
	}
	
	function yn_correct ($value, $result, $results = ['y', 'n']) {
		
		if (!in_array ($value, $results)) $value = $result;
		return $value;
		
	}
	
	define ('CLI_MESSAGE_TEXT_COLOR_BLACK', 30);
	define ('CLI_MESSAGE_TEXT_COLOR_RED', 31);
	define ('CLI_MESSAGE_TEXT_COLOR_GREEN', 32);
	define ('CLI_MESSAGE_TEXT_COLOR_YELLOW', 33);
	define ('CLI_MESSAGE_TEXT_COLOR_BLUE', 34);
	define ('CLI_MESSAGE_TEXT_COLOR_VIOLET', 35);
	define ('CLI_MESSAGE_TEXT_COLOR_SEA', 36);
	define ('CLI_MESSAGE_TEXT_COLOR_GRAY', 37);
	
	define ('CLI_MESSAGE_BG_COLOR_BLACK', 40);
	define ('CLI_MESSAGE_BG_COLOR_RED', 41);
	define ('CLI_MESSAGE_BG_COLOR_GREEN', 42);
	define ('CLI_MESSAGE_BG_COLOR_YELLOW', 43);
	define ('CLI_MESSAGE_BG_COLOR_BLUE', 44);
	define ('CLI_MESSAGE_BG_COLOR_VIOLET', 45);
	define ('CLI_MESSAGE_BG_COLOR_SEA', 46);
	define ('CLI_MESSAGE_BG_COLOR_GRAY', 47);
	
	define ('CLI_MESSAGE_TEXT_TYPE_DEFAULT', 0);
	define ('CLI_MESSAGE_TEXT_TYPE_INTENSIVE', 1);
	define ('CLI_MESSAGE_TEXT_TYPE_UNDERLINE', 4);
	define ('CLI_MESSAGE_TEXT_TYPE_BLINK', 5);
	define ('CLI_MESSAGE_TEXT_TYPE_INVERSE', 7);
	
	function cli_message ($text, $text_color = CLI_MESSAGE_TEXT_COLOR_GRAY, $bg_color = CLI_MESSAGE_BG_COLOR_BLACK, $text_type = CLI_MESSAGE_TEXT_TYPE_INTENSIVE) {
		return "\e[1m\e[".$text_type."m\e[".$text_color."m\e[".$bg_color."m".$text."\e[0m";
	}
	
	function _update_res_nullterm ($string) {
		
		$output = '';
		foreach (str_split ($string) as $symbol)
		$output .= $symbol."\0";
		
		return $output;
		
	}
	
	function pad32 ($string, $extra = 2) {
		
		$length = (4 - ((strlen ($string) + $extra) & 3));
		
		if ($length < 4)
		$string = $string.str_repeat ("\0", $length);
		
		return rtrim ($string, "\0");
		
	}
	
	function _update_res_addlen ($string) {
		return pack ('C', (strlen (to_unicode ($string, 'ascii')) + 2)).$string;
	}
	
	function _update_res_string ($key, $value) {
		
		$value = _update_res_nullterm ($value);
		
		$result = pack ('CC', intdiv (strlen ($value), 2), 1); // wValueLength, wType
		$result = $result._update_res_nullterm ($key);
		$result = pad32 ($result).$value;
		
		return _update_res_addlen ($result);
		
	}
	
	function _update_res_val ($key, $value) {
		
		$result = pack ('CC', strlen ($value), 0); // wValueLength, wType
		$result = $result._update_res_nullterm ($key);
		$result = pad32 ($result).$value;
		
		return _update_res_addlen ($result);
		
	}
	
	function _update_res_string_table ($key, $data) {
		
		$result = pack ('CC', 0, 1); // wValueLength, wType
		$result = $result._update_res_nullterm ($key);
		
		foreach ($data['StringFileInfo'] as $key => $value) {
			
			$result = $result._update_res_string ($key, $value);
			$result = pad32 ($result);
			
		}
		
		return _update_res_addlen ($result);
		
	}
	
	function update_res ($file, $options, $debug = 0) {
		
		$options['FileVersion'] = $options['ProductVersion'];
		
		list ($major, $minor, $sub, $build) = explode ('.', $options['FileVersion']);
		
		$pack = pack ('lllllllllllll',
			
			'-17890115',							// dwSignature
			'0x00010000',							// dwStrucVersion
			($major << 16) | $minor,	// dwFileVersionMS
			($sub << 16) | $build,		// dwFileVersionLS
			($major << 16) | $minor,	// dwProductVersionMS
			($sub << 16) | $build,		// dwProductVersionLS
			'0x0000003f',							// dwFileFlagsMask
			($debug ? 3 : 0),					// dwFileFlags
			'0x00040004',							// dwFileOS
			((get_filetype ($file) == 'dll') ? 2 : 1),	// dwFileType
			'0x00000000',							// dwFileSubtype
			'0x00000000',						 // dwFileDateMS
			'0x00000000'							// dwFileDateLS
			
		);
		
		$result = pack ('CC', strlen ($pack), 0);	// wValueLength, wType
		$result = $result._update_res_nullterm ('VS_VERSION_INFO');
		$result = pad32 ($result).$pack;
		
		$data = [];
		
		$struct = [
			
			'Comments',
			'CompanyName',
			'FileDescription',
			'FileVersion',
			'InternalName',
			'LegalCopyright',
			'LegalTrademarks',
			'OriginalFilename',
			'ProductName',
			'ProductVersion',
			
		];
		
		if (!$options['InternalName'])
		$options['InternalName'] = get_filename ($file, 1);
		
		if (!$options['OriginalFilename'])
		$options['OriginalFilename'] = get_filename ($file, 1);
		
		foreach ($struct as $key)
		$data['StringFileInfo'][$key] = $options[$key];
		
		$data['VarFileInfo']['Translation'] = pack ('CC', '0x409', 1252);
		
		$string_ver = pack ('CC', 0, 1);	// wValueLength, wType
		$string_ver = $string_ver._update_res_nullterm ('StringFileInfo');
		//$string_ver = pad32 ($string_ver)._update_res_string_table ('040904b0', $sdata);
		$string_ver = pad32 ($string_ver)._update_res_string_table ('040904E4', $data);
		$string_ver = _update_res_addlen ($string_ver);
		
		$val_ver = pack ('CC', 0, 1);	// wValueLength, wType
		$val_ver = $val_ver._update_res_nullterm ('VarFileInfo');
		$val_ver = pad32 ($val_ver);
		
		foreach ($data['VarFileInfo'] as $key => $value) {
			
			$val_ver = $val_ver._update_res_val ($key, $value);
			$val_ver = pad32 ($val_ver);
			
		}
		
		$val_ver = _update_res_addlen ($val_ver);
		
		$result = pad32 ($result).$string_ver.$val_ver;
		
		return $result;
		
	}