<?php
/*
 ========================================
 Mash Framework (c) 2010-2017, 2019
 ----------------------------------------
 https://mash.ointeractive.ru/
 ========================================
 O! Interactive (support@ointeractive.ru)
 ----------------------------------------
 -- Библиотеки
 --- Работа с файлами
 ========================================
*/
	
	if (!defined ('MASH')) die ('File must be started only through the main framework cover');
	
	define ('DS', DIRECTORY_SEPARATOR);
	
	function copy_dir ($from, $to, $options = [], $debug = 0) { // Копирует каталоги из $from в $to
		
		if ($debug) $s = microtime (true);
		
		make_dir ($to);
		if (!file_exists ($from)) die ('Error: copy_dir ('.$from.', '.$to.'): Исходный каталог не найден!');
		
		if (!$options['tread']) $options['tread'] = 'files';
		if (!$options['part']) $options['part'] = 'name';
		if (!isset ($options['subfolders'])) $options['subfolders'] = true;
		if (!$options['write_type']) $options['write_type'] = 'replace_all';
		if (!$options['obfusc_type']) $options['obfusc_type'] = 'lsob2';
		if (!$options['copyright_exp']) $options['copyright_exp'] = 'php, php4, php5';
		if (!$options['obfuscate_exp']) $options['obfuscate_exp'] = 'php, php4, php5';
		
		$options['include_dirs'] = dash_filepath_array ($options['include_dirs']);
		$options['include_filenames'] = sep_explode ($options['include_filenames']);
		$options['include_files'] = dash_filepath_array ($options['include_files']);
		$options['include_exp'] = sep_explode ($options['include_exp']);
		
		$options['exclude_filenames'] = sep_explode ($options['exclude_filenames']);
		$options['exclude_dirs'] = dash_filepath_array ($options['exclude_dirs']);
		$options['exclude_obfuscate_dirs'] = dash_filepath_array ($options['exclude_obfuscate_dirs']);
		$options['exclude_files'] = dash_filepath_array ($options['exclude_files']);
		$options['exclude_exp'] = sep_explode ($options['exclude_exp']);
		
		$options['copyright_exp'] = sep_explode ($options['copyright_exp']);
		$options['obfuscate_exp'] = sep_explode ($options['obfuscate_exp']);
		
		if (!$options['archive_type']) $options['archive_type'] = 'zip';
		
		$data = [
			
			'replace_types' => ['replace_all', 'replace_old', 'replace_synchro', 'skip_exist'],
			'write_types' => ['copyright', 'obfuscate'],
			'messages' => [1 => ['blue', 'Дописан'], 2 => ['green', 'Изменен'], 3 => ['red', 'Устарел']],
			'exclude_obfuscate_dir_files' => [],
			
		];
		
		if ($debug) echo 'Отработала за '.round ((microtime (true) - $s), 4).' сек.<br/>
<br/>
';
		
		_copy_dir ($from, $to, $options, $data, '', $debug);
		
		if ($options['archive']) {
		
			$archive = new lisas_archive ($options['archive_type'], $options['archive_name'], $options['zip_options']);
			$archive->pack ($to);
			
			if ($options['archive_comment']) {
				
				if ($options['archive_comment_text']) $archive->add_comment ($options['archive_comment_text'], $options['archive_comment_replaces']);
				elseif ($options['archive_comment_file']) $archive->add_comment_file ($options['archive_comment_file'], $options['archive_comment_replaces']);
				
			}
			
			$archive->close ();
			
		}
		
	}
	
	function del_file ($file, $old) {
		
		chdir (get_filepath ($file));
		$result = unlink ($file);
		chdir ($old);
		
		return $result;
		
	}
	
	function unzip ($file, $dir) {
		
		$zip_files = ['zip'];
		
		if (in_array (get_filetype ($file), $zip_files)) {
			
			$zip_handle = zip_open ($file);
			
			if (is_resource ($zip_handle)) {
				
				if (!is_dir ($dir)) mkdir ($dir, 0777);
				
				while ($zip_entry = zip_read ($zip_handle)) {
					
					if ($zip_entry) {
						
						$zip_name = zip_entry_name ($zip_entry);
						$zip_size = zip_entry_filesize ($zip_entry);
						
						if ($zip_size > 0 and $zip_name[lisas_strlen ($zip_name) - 1] != '/') {
							
							zip_entry_open ($zip_handle, $zip_entry, 'r');
							$fp = fopen ($dir.DS.$zip_name, 'wb+');
							fwrite ($fp, zip_entry_read ($zip_entry, $zip_size), $zip_size);
							fclose ($fp); @chmod ($dir.DS.$zip_name, 0775);
							zip_entry_close ($zip_entry);
							
						} else mkdir ($dir.DS.$zip_name, 0775);
						
					}
					
				}
				
				return true;
				
			} else {
				
				zip_close ($zip_handle);
				return false;
				
			}
			
		} else return false;
		
	}
	
	function write_file ($input, $file, $type = 'a', $to_unicode = 0) { // Пишет контент $input в файл $handle, открывая его типом $type
		
		$handle = fopen ($file, $type);
		
		if ($handle) {
			
			if (is_array ($input)) {
				
				$content = '';
				
				foreach ($input as $key => $ddd) {
					
					$ddd = trim ($ddd);
					
					if ($key + 1 == count ($input))
					$content .= $ddd;
					else
					$content .= $ddd.NL;
					
				}
				
			} else $content = $input;
			
			if ($to_unicode) $content = to_unicode ($content);
			
			fwrite ($handle, $content);
			fclose ($handle);
			//@chmod ($handle, 0666);
			
			return true;
			
		}
		
	}
	
	function end_filepath ($file) {
		
		$file = explode_filepath ($file);
		return end ($file);
		
	}
	
	function sec_filepath ($path, $ucfirst = 0, $other_sumb = 0) {
		
		$path = trim (spech_encode (strip_tags ($path), 0));
		$path = clearspecialchars ($path, $ucfirst, $other_sumb);
		
		return $path;
		
	}
	
	function prep_filepath ($path, $ucfirst = 0, $debug = 0) {
		
		$path = get_filename ($path, 1);
		$path = sec_filepath ($path, $ucfirst);
		if ($debug) debug ($path);
		
		return $path;
		
	}
	
	function dash_filepath_array ($array) {
		
		$output = [];
		foreach ($array as $path) $output[] = dash_filepath ($path);
		return $output;
		
	}
	
	function secure_filename ($file, $type, $debug = 0) { // Собираем готовое имя файла
		
		$file = get_filename ($file);
		$file = str_cut ($file, 40, '');
		$file = do_rand (11, 2).'_'.to_translit ($file, ['alt_name' => 1]);
		$file = prep_filepath ($file);
		$file .= '.'.$type;
		
		if ($debug) debug ($file);
		
		return $file;
		
	}
	
	function dir_rename ($path, $dest) {
		return rename (dash_filepath ($path), dash_filepath ($dest));
	}
	
	function is_remote_file ($url, $code = 200) {
		
		$code = (int) $code;
		return (bool) preg_match ('~HTTP/1\.\d\s+'.$code.'\s+OK~', @current (get_headers ($url)));
		
	}
	
	function mksize ($bytes, $value = false, $num = 2, $lang = ['b', 'kb', 'Mb', 'Gb', 'Tb']) {
		
		if ($value) {
			
			for ($i = 0; $i < count ($lang); ++$i)
			if ($lang[$i] == $value) {
				
				if ($i == 0)
					$output = $bytes;
				else
					$output = number_format ($bytes / pow (1024, $i), $num);
				
			}
			
		} else {
			
			for ($i = 0; $i < count ($lang); ++$i) {
				
				$pow = pow (1000, $i);
				
				if ($bytes >= $pow) {
					
					if ($i == 0) $output = $bytes;
					else
					$output = (round ($bytes / $pow * 100) / 100);
					
					$output .= ' '.$lang[$i];
					
				}
				
			}
			
		}
		
		return $output;
		
	}
	
	function decode_filesize ($string) {
		
		$type = substr ($string, -1);
		$size = substr ($string, 0, -1);
		$num = 0;
		
		switch ($type) {
			
			case 'b': $num = 1; break;
			case 'K': $num = 1024; break;
			case 'M': $num = pow (1024, 2); break;
			case 'G': $num = pow (1024, 3); break;
			case 'T': $num = pow (1024, 4); break;
			
		}
		
		if ($num) $bytes = ($size * $num); else $bytes = false;
		return $bytes;
		
	}
	
	function chmod_r ($path, $perm) { // | Доработать для файлов
		
		$handle = opendir ($path);
		
		while (($file = readdir ($handle)) !== false) {
			
			if (allow_filename ($file, 0)) {
				
				$file_path = $path.DS.$file;
				
				if (is_dir ($file)) {
					
					@chmod ($file_path, $perm);
					chmod_r ($file_path, $perm);
					
				} else @chmod ($file_path, $perm);
				
			}
			
			closedir ($handle);
			
		}
		
	}
	
	function file_str_replace ($replaces, $file, $chmod = 0666) { // str_replace () в файле $file.
		
		$content = file_get_content ($file);
		
		foreach ($replaces as $find => $replace)
		$content = str_replace ($find, $replace, $content);
		
		file_put_content ($content, $file, $chmod);
		
		return $content;
		
	}
	
	$pointer = 0;
	
	function fchunk ($handle, $open = '', $close = '', $chunk_size = 4096) {
		global $pointer;
		
		$buffer = '';
		
		if (!$open or !$close) {
			
			while (!feof ($handle))
			$buffer .= fread ($handle, $chunk_size);
			
		} else {
			
			$buffer = '';
			$store = false;
			$reading = true;
			$readBuffer = '';
			
			fseek ($handle, $pointer);
			
			while ($reading and !feof ($handle)) {
				
				$tmp = fread ($handle, $chunk_size);
				$readBuffer .= $tmp;
				
				$checkOpen = strpos ($tmp, $open); // Открывающий тег
				
				if (!$checkOpen and !$store) { // Еще нет в буфере
					
					$checkOpen = strpos ($readBuffer, $open);
					if ($checkOpen) $checkOpen = ($checkOpen % $chunk_size);
					
				}
				
				$checkClose = strpos ($tmp, $close); // Закрывающий тег
				
				if (!$checkClose and $store) { // Еще нет в буфере
					
					$checkClose = strpos ($readBuffer, $close);
					
					if ($checkClose)
					$checkClose = (($checkClose + strlen ($close)) % $chunk_size);
					
				} elseif ($checkClose) $checkClose += strlen ($close);
				
				if ($checkOpen !== false and !$store) { // Нашли первый открывающий тег
					
					if ($checkClose !== false) { // Нашли закрывающий тег
						
						// Содержимое между тегами
						$buffer .= substr ($tmp, $checkOpen, ($checkClose - $checkOpen));
						
						$pointer += $checkClose; // Устанавливаем положение
						$reading = false; // Закончили чтение
						
					} else {
						
						$buffer .= substr ($tmp, $checkOpen);
						$pointer += $chunk_size; // Устанавливаем положение
						$store = true;
						
					}
					
				} elseif ($checkClose !== false) { // Нашли закрывающий тег
					
					$buffer .= substr ($tmp, 0, $checkClose);
					$pointer += $checkClose;
					$reading = false;
					
				} elseif ($store) {
					
					$buffer .= $tmp;
					$pointer += $chunk_size;
					
				}
				
			}
			
		}
		
		return $buffer;
		
	}
	
	$pointer = 0;
	
	function fshow ($handle, $open, $close, $chunk_size = 512) {
		
		return $buffer;
		
	}
	
	function get_chmod ($file) {
		
		//if (!stristr (php_uname ('s'), 'windows'))
		return (file_exists ($file) ? (decoct (fileperms ($file)) % 1000) : false);
		
	}
	
	function google_url_hash ($string) {
		
		$check1 = strtonum ($string, 0x1505, 0x21);
		$check2 = strtonum ($string, 0, 0x1003F);
		
		$check1 >>= 2;
		$check1 = (($check1 >> 4) & 0x3FFFFC0) | ($check1 & 0x3F);
		$check1 = (($check1 >> 4) & 0x3FFC00) | ($check1 & 0x3FF);
		$check1 = (($check1 >> 4) & 0x3C000) | ($check1 & 0x3FFF);
		
		$t1 = (((($check1 & 0x3C0) << 4) | ($check1 & 0x3C)) << 2) | ($check2 & 0xF0F);
		$t2 = (((($check1 & 0xFFFFC000) << 4) | ($check1 & 0x3C00)) << 0xA) | ($check2 & 0xF0F0000);
		
		return ($t1 | $t2);
		
	}
	
	function google_check_url_hash ($hash) {
		
		$byte = 0;
		$flag = 0;
		
		$hash_str = sprintf ('%u', $hash) ;
		$length = lisas_strlen ($hash_str);
		
		for ($i = ($length - 1); $i >= 0; $i--) {
			
			$re = $hash_str[$i];
			
			if (1 === ($flag % 2)) {
				
				$re += $re;
				$re = (int) ($re / 10) + ($re % 10);
				
			}
			
			$byte += $re;
			++$flag;
			
		}
		
		$byte %= 10;
		
		if (0 !== $byte) {
			
			$byte = 10 - $byte;
			
			if (1 === ($flag % 2)) {
				
				if (1 === ($byte % 2)) $byte += 9;
				$byte >>= 1;
				
			}
			
		}
		
		return '7'.$byte.$hash_str;
		
	}
	
	function site_rate ($url) {
		
		$url = get_domain ($url);
		
		$xml = file_get_content ('http://bar-navig.yandex.ru/u?ver=2&show=32&url='.url_encode ('http://'.$url, 0));
		$cy = substr (strstr ($xml, 'value='), 6);
		$cy = strtok ($cy, '"');
		
		//
		
		$fp = fsockopen ('toolbarqueries.google.com', 80, $errno, $errstr, 30);
		
		if ($fp) {
			
			$out = "GET /tbr?client=navclient-auto&ch=".google_check_url_hash (google_url_hash ($url))
			."&features=Rank&q=info:".$url."&num=100&filter=0 HTTP/1.1\r\n";
			$out .= "Host: toolbarqueries.google.com\r\n";
			$out .= "User-Agent: Mozilla/4.0 (compatible; GoogleToolbar 2.0.114-big; Windows XP 5.1)\r\n";
			$out .= "Connection: Close\r\n\r\n";
			
			fwrite ($fp, $out);
			
			while (!feof ($fp)) {
				
				$data = fgets ($fp, 128);
				$pos = strpos ($data, 'Rank_');
				
				if ($pos !== false) $pr = substr ($data, $pos + 9);
				
			}
			
			fclose ($fp);
			
		}
		
		return array ('cy' => intval_correct ($cy, ''), 'pr' => intval_correct ($pr, ''));
		
	}
	
	class lisas_archive {
		
		protected $zip;
		protected $root = '';
		protected $options = ['rename_root' => ''];
		
		function __construct ($type, $name, $options = []) {
			
			$this->zip = new ZipArchive ();
			if (is_array ($options)) $this->options = $options;
			$this->type = $type;
			$this->name = dash_filepath ($name);
			
			switch ($this->type) {
				
				case 'zip':
					
					switch ($this->options['write_type']) {
						
						default: $write_type = ZipArchive::CREATE; break;
						case 'overwrite': $write_type = ZipArchive::OVERWRITE; break;
						case 'noexists': $write_type = ZipArchive::EXCL; break;
						case 'check': $write_type = ZipArchive::CHECKCONS; break;
						
					}
					
					if ($this->zip->open ($this->name, $write_type)!== true) die ('Error: lisas_archive ('.$type.', '.$this->name.'): Ошибка при создании архива '.$this->name.'.');
					
				break;
				
			}
			
		}
		
		private function _name_path ($file) {
			
			switch ($this->type) {
				
				case 'zip':
					
					$name = explode_filepath ($this->name);
					
					if (count ($name) > 1) {
						
						$path = get_filepath ($this->name);
						$file_name = get_filename ($file, 1);
						
						$output = $path.DS.$file_name;
						
					} else $output = $this->name;
					
				break;
				
			}
			
			return dash_filepath ($output);
			
		}
		
		private function _pack ($folder, $parent = '') {
			
			$root_key = 0;
			$explode_zip_path = explode_filepath ($folder);
			
			 // Маленький секрет: пути в архивах имеют вид папка1/файл, поэтому отбрасываем рут:
			 
			if (count ($explode_zip_path) > 1) {
				
				$this->root = $explode_zip_path[0];
				unset ($explode_zip_path[0]);
				unset ($explode_zip_path[1]);
				$root_key = 2;
				
			}
			
			$zip_path = implode_filepath ($explode_zip_path);
			
			if ($this->options['rename_root']) $explode_zip_path[$root_key] = $this->options['rename_root'];
			$zip_path2 = implode_filepath ($explode_zip_path);
			
			///
			
			switch ($this->type) {
				
				case 'zip':
					
					$this->zip->addEmptyDir ($zip_path2);
					
					$files = dir_scan ($folder, ['names_only' => 1]);
					
					foreach ($files as $file) {
						
						$file2 = $folder.DS.$file;
						
						if (is_dir ($file2))
						$this->_pack ($file2, $zip_path);
						elseif (is_file ($file2) and $this->_name_path ($file) != $this->name)
						$this->zip->addFile ($file2, from_unicode ( $zip_path2.DS.$file, 'cp866', 'windows-1251'));
						
					}
					
				break;
				
			}
			
		}
		
		function pack ($folder) {
			
			$folder = substr ($folder, -1) == '/' ? substr ($folder, 0, -1) : $folder;
			
			switch ($this->type) {
				
				case 'zip':
					$this->_pack ($folder);
				break;
				
			}
			
		}
		
		function add_comment ($text, $charset = 'windows-1251') {
			
			switch ($this->type) {
				
				case 'zip':
					$this->zip->setArchiveComment (from_unicode ($text, $charset));
				break;
				
			}
			
		}
		
		function add_comment_file ($file, $replaces = [], $charset = 'windows-1251') {
			
			switch ($this->type) {
				
				case 'zip':
					
					$text = file_get_content ($file);
					
					if ($replaces) foreach ($replaces as $find => $replace)
					$text = str_replace ($find, $replace, $text);
					
					$this->add_comment ($text, $charset);
					
				break;
				
			}
			
		}
		
		function close () {
			
			switch ($this->type) {
				
				case 'zip':
					$this->zip->close ();
				break;
				
			}
			
		}
		
	}
	
	function hmac_sha256 ($str, $secret, $bin = false) {
		return hash_hmac ('sha256', $str, $secret, $bin);
	}
	
	function get_mime_type ($file) {
		
		$exts = [
			
			'jpg'	=> 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'gif'	=> 'image/gif',
			'png'	=> 'image/png',
			
			'ico'	=> 'image/x-icon',
			
			'pdf'	=> 'application/pdf',
			
			'tif'	=> 'image/tiff',
			'tiff' => 'image/tiff',
			
			'svg'	=> 'image/svg+xml',
			'svgz' => 'image/svg+xml',
			
			'swf'	=> 'application/x-shockwave-flash',
			
			'zip'	=> 'application/zip',
			'gz'	 => 'application/x-gzip',
			'tar'	=> 'application/x-tar',
			'bz'	 => 'application/x-bzip',
			'bz2'	=> 'application/x-bzip2',
			'rar'	=> 'application/x-rar-compressed',
			'cab'	=> 'application/vnd.ms-cab-compressed',
			
			'exe'	=> 'application/x-msdownload',
			'msi'	=> 'application/x-msdownload',
			
			'txt'	=> 'text/plain',
			'asc'	=> 'text/plain',
			
			'htm'	=> 'text/html',
			'html' => 'text/html',
			'css'	=> 'text/css',
			'js'	 => 'text/javascript',
			
			'xml'	=> 'text/xml',
			'xsl'	=> 'application/xsl+xml',
			
			'mp3'	=> 'audio/mpeg',
			'ogg'	=> 'application/ogg',
			'wav'	=> 'audio/x-wav',
			
			'avi'	=> 'video/x-msvideo',
			'mpg'	=> 'video/mpeg',
			'mpeg' => 'video/mpeg',
			'mov'	=> 'video/quicktime',
			'flv'	=> 'video/x-flv',
			
			'php'	=> 'text/x-php',
			
		];
		
		$ext = strtolower (pathinfo ($file, PATHINFO_EXTENSION));
		
		if (is_isset ($ext, $exts)) return $exts[$ext];
		elseif (extension_loaded ('fileinfo') and is_isset ('MAGIC', $_ENV) and
		($finfo = finfo_open (FILEINFO_MIME, $_ENV['MAGIC']))) {
			
			if ($type = finfo_file ($finfo, $file)) {
				
				$type = explode (' ', str_replace ('; charset=', ';charset=', $type));
				$type = array_pop ($type);
				$type = explode (';', $type);
				$type = trim (array_shift ($type));
				
			}
			
			finfo_close ($finfo);
			
			if ($type !== false and lisas_strlen ($type) > 0) return $type;
			
		} else return 'application/octet-stream';
		
	}
	
	function file_crc ($file) {
		return dechex (crc32 (file_get_content ($file)));
	}
	
	function _replaces ($options, $file, $i = 0, $is_exp = 0, $debug = 0) {
		
		if (is_file ($file)) $name = get_filename ($file); else $name = get_filename ($file, 1);
		
		$replace = 0;
		
		if ($options['type'] and (($options['tread'] == 'files' and is_file ($file)) or ($options['tread'] == 'folders' and is_dir ($file)) or $options['tread'] == 'both') or $is_exp) { // Что обрабатывать
			
			if (($options['part'] == 'name' and !$is_exp) or ($options['part'] == 'type' and $is_exp) or $options['part'] == 'both') $replace = 1;
			
		}
		
		if ($replace) {
			
			foreach ($options['type'] as $t_key => $t_value) {
				
				if ($t_key != 'replace') $t_value = _parse_value ($t_value, $i);
				
				switch ($t_key) {
					
					case 'add_left': $name = $t_value.$name; break;
					case 'add_right': $name = $name.$t_value; break;
					case 'delete': $name = str_replace ($t_value, '', $name); break;
					case 'delete_left': $name = substr ($name, (int) $t_value); break;
					case 'delete_right': $name = substr ($name, 0, (int) $t_value); break;
					case 'replace': $name = str_replace ($t_value[0], _parse_value ($t_value[1], $i), $name); break;
					case 'uppercase': $name = lisas_strtoupper ($name); break;
					case 'lowercase': $name = lisas_strtolower ($name); break;
					case 'uppercase_first': $name = lisas_ucfirst ($name); break;
					case 'uppercase_first_all':
						
						$name = str_replace ('	', ' ', trim ($name));
						$words = [];
						$word = explode (' ', $name);
						
						foreach ($word as $word) $words[] = lisas_ucfirst ($word);
						$name = implode (' ', $words);
						
					break;
					
				}
				
			}
			
		}
		
		if ($debug) debug ($name);
		return $name;
		
	}
	
	function _copy_dir ($from, $to, $options, $data, $sep, $debug) {
		
		$i = 1;
		$sep .= '----';
		
		$dir = dir_scan ($from, ['names_only' => 1]);
		
		foreach ($dir as $file) {
			
			$from2 = dash_filepath ($from.DS.$file);
			$file_exp = get_filetype ($file);
			$file_path = get_filepath ($from2);
			$to_file_path = get_filepath ($to2);
			
			$name = _replaces ($options, $from2, $i);
			$exp = _replaces ($options, get_filetype ($file), $i, 1);
			$to3 = dash_filepath ($to.DS.$name);
			
			if (is_file ($from2)) $to2 = $to3.'.'.$exp; else $to2 = $to3;
			
			$allow_file = 1;
			$exclude_type = [];
			
			//foreach ($options['exclude_obfuscate_dirs'] as $obfuscate_dir)
			//if (lisas_strpos ($obfuscate_dir, $to_file_path) !== false) $data['exclude_obfuscate_dir_files'] = $to_file_path;
			
			if (in_array ($from2, $options['exclude_dirs'])) $allow_file = 0;
			if (is_file ($from2) and in_array ($file, $options['exclude_filenames'])) $allow_file = 0;
			if (is_file ($from2) and in_array ($from2, $options['exclude_files'])) $allow_file = 0;
			if (is_file ($from2) and in_array ($file_exp, $options['exclude_exp'])) $allow_file = 0;
			if (($options['write_type'] == 'replace_old' or $options['write_type'] == 'replace_synchro') and is_file ($from2) and filemtime ($to2) > filemtime ($from2)) $allow_file = 0;
			if ($options['write_type'] == 'skip_exist' and is_file ($from2) and file_exists ($to2)) $allow_file = 0;
			
			if ($debug and is_file ($from2)) {
				if (filemtime ($from2) < filemtime ($to2)) $exclude_type = 3; // Сообщение "Устарел".
			}
			
			if (allow_filename ($file, 0) and $allow_file) {
				
				//debug ($from.' - '.(in_array ($from, $options['exclude_dirs'])).' - '.$allow_file);
				
				if ($debug and is_file ($from2)) {
					
					if (!file_exists ($to2)) $exclude_type = 1; // Сообщение "Изменен".
					if (file_exists ($to2) and filemtime ($from2) > filemtime ($to2)) $exclude_type = 2; // Сообщение "Дописан".
					
				}
				
				//if ($options['write_type'] == 'replace_synchro' and is_file ($to2) and file_exists ($to2) and !file_exists ($from2)) debug ($to2);
				
				if ($debug and $exclude_type) echo '<span style="color:'.$data['messages'][$exclude_type][0].';">'.str_replace ($this->getRootDir (), '.', $from2).' '.$data['messages'][$exclude_type][1].'</span><br/>
';
				//echo $sep.$to2.'<br/>';
				
				if (is_file ($from2) and $allow_file) {
					++$i;
					
					copy ($from2, $to2);
				
					if ($options['obfuscate'] and in_array ($file_exp, $options['obfuscate_exp'])) {
						
						$text_from = file_get_content ($from2);
						if (strpos ($text_from, '<?php $v=') !== true) $text = obfuscate ($options['obfusc_type'], $text, $to2);
						
					}
					
					if ($options['copyright'] and in_array ($file_exp, $options['copyright_exp'])) {
						
						if (!$text) $text = file_get_content ($to2);
						
						if (substr ($text, 0, lisas_strlen ($options['copyright'])) != $options['copyright']) $text = '<?php'.NL.$options['copyright'].NL.'?>'.$text;
						
					}
					
					if ($text) file_put_content ($text, $to2);
					
				} elseif (is_dir ($from2) and $options['subfolders']) {
					
					make_dir ($to2);
					_copy_dir ($from2, $to2, $options, $data, $sep, $debug);
					
				}
				
			}
			
		}
		
	}
	
	function dir_size ($dir, $deny_dirs = false) {
		
		$totalsize = 0;
		
		if ($dirstream = @opendir ($dir)) {
			
			while (false !== ($filename = readdir ($dirstream))) {
				
				if (allow_filename ($filename) and $dir != $deny_dirs) {
					
					if (is_file ($dir.DS.$filename)) $totalsize += filesize ($dir.DS.$filename);
					
					if (is_dir ($dir.DS.$filename)) $totalsize += dir_size ($dir.DS.$filename);
					
				}
				
			}
			
		}
		
		closedir ($dirstream);
		
		return $totalsize;
		
	}
	
	function _dir_scan_name ($file, $options, $debug) {
		
		if ($options['unicode']) $file = to_unicode ($file, 'cp1251');
		return $file;
		
	}
	
	function _dir_scan_prep_files ($files, $root, $path, $debug) {
		
		$output = [];
		
		if ($files)
		foreach (make_array ($files) as $file) if ($file) {
			
			if ($path and $path != $root)
			$file = add_ds ($root).substr ($file, lisas_strlen (add_ds ($path)));
			
			$output[] = dash_filepath ($file);
			
		}
		
		return $output;
		
	}
	
	function _dir_scan_allow_file ($file, $allow, $deny, $debug = 0) {
		
		$output = (
			(
				($allow and
					(
						(is_array ($allow) and in_array ($file, $allow))
						or
						$file == dash_filepath ($allow)
					)
				)
			)
			or
			(
				($deny and
					(
						(is_array ($deny) and !in_array ($file, $deny))
						or
						$file != dash_filepath ($deny)
					)
				)
			)
			or (!$allow and !$deny)
		);
		
		if ($debug) print_r ([$file, $allow, $deny, $output]);
		
		return $output;
		
	}
	
	function _dir_scan ($dir, $options, $debug, $nbsp, $root = '', $output = []) {
		
		if (!$root) $root = $dir;
		
		foreach (scandir ($dir) as $file)
		if (allow_filename ($file)) {
			
			$file_name = dash_filepath ($dir.DS.$file);
			
			if (
				(
					_dir_scan_allow_file (get_filepath ($file_name), $options['allow_dirs'], $options['deny_dirs'])
					and
					(($options['dirs_only'] and is_dir ($file_name)) or !$options['dirs_only'])
				)
				and
				(
					_dir_scan_allow_file (get_filetype ($file_name), $options['allow_types'], $options['deny_types'])
					and
					(($options['files_only'] and is_file ($file_name)) or !$options['files_only'])
				)
			) {
				
				if ($options['names_only'])
					$name = _dir_scan_name ($file, $options, $debug);
				else
					$name = _dir_scan_name ($file_name, $options, $debug);
				
				if ($options['canonical_path'])
					$name = get_canonical ($name, $options['canonical_path']);
				
				$output[] = $name;
				
			}
			
			if ($options['recursive'] and is_dir ($file_name) and _dir_scan_allow_file (get_filepath ($file_name), $options['allow_dirs'], $options['deny_dirs']))
			$output = _dir_scan ($file_name, $options, $debug, $nbsp.$nbsp, $root, $output);
			
		}
		
		return $output;
		
	}
	
	function get_canonical ($name, $path) {
		return substr ($name, lisas_strlen (add_ds ($path)));
	}
	
	function dir_scan ($dir, $options = [], $debug = 0, $nbsp = '--') {
		
		$dir = trim_filepath ($dir);
		
		$options = array_extend ($options, [
			
			'files_only' => true,
			'dirs_only' => false,
			'names_only' => false,
			'canonical_path' => false,
			'recursive' => false,
			'unicode' => true,
			
			'allow_dirs' => [],
			'deny_dirs' => [],
			'allow_types' => [],
			'deny_types' => [],
			
		]);
		
		if ($options['dirs_only']) $options['files_only'] = false;
		
		$options['allow_dirs'] = _dir_scan_prep_files ($options['allow_dirs'], $dir, $options['canonical_path'], $debug);
		$options['deny_dirs'] = _dir_scan_prep_files ($options['deny_dirs'], $dir, $options['canonical_path'], $debug);
		
		$options['allow_types'] = make_array ($options['allow_types']);
		$options['deny_types'] = make_array ($options['deny_types']);
		
		$output = _dir_scan ($dir, $options, $debug, $nbsp);
		
		return $output;
		
	}
	
	function dir_cmp ($dir1, $dir2, $options = []) {
		
		$deny_files = dir_scan ($dir1, ['files_only' => 1, 'recursive' => 1]);
		return dir_scan ($dir2, ['files_only' => 1, 'recursive' => 1, 'deny_files' => $deny_files, 'canonical_path' => $dir1]);
		
	}
	
	function dir_cmpcmp ($dir1, $dir2, $options = []) {
		return dir_scan ($dir2, ['files_only' => 1, 'recursive' => 1, 'deny_files' => dir_cmp ($dir1, $dir2), 'canonical_path' => $options['canonical_path']]);
	}
	
	function dir_copy_cmp ($dir1, $dir2, $dir3, $options = []) {
		
		foreach (dir_cmpcmp ($dir1, $dir2, ['canonical_path' => $dir2]) as $file)
		file_copy (add_ds ($dir2).$file, add_ds ($dir3).$file, $options);
		
	}
	
	function file_copy ($dir1, $dir2, $options = []) {
		
		$options = array_extend ($options, [
			
			'rewrite' => 1,
			
		]);
		
		if (is_file ($dir1)) {
			
			if ($options['rewrite'] or (!$options['rewrite'] and !file_exists ($dir2))) {
				
				make_dir (get_filepath ($dir2));
				copy ($dir1, $dir2);
				
			}
			
		}
		
	}
	
	function allow_filename ($file, $full = 1) {
		
		$deny_files = ['.', '..'];
		
		if ($full) {
			$deny_files[] = 'Thumbs.db';
			$deny_files[] = 'desktop.ini';
		}
		
		if (in_array ($file, $deny_files)) return false; else return true;
		
	}
	
	function file_search ($find_file, $dir) {
		
		foreach (dir_scan ($dir) as $this_file)
		if ($this_file == $find_file) return true;
		
		return false;
		
	}
	
	function make_dir ($path, $chmod = 0777) {
		
		if (!file_exists ($path)) {
			if (mkdir ($path, $chmod, true)) return 1; else return 0;
		} else return 2;
		
	}
	
	function dir_delete ($dir, $options = [], $debug = 0) {
		
		$options = array_extend ($options, ['del_dirs' => 1]);
		$files = dir_scan ($dir, ['names_only' => 1]);
		
		foreach ($files as $file) {
			
			$del_file = 0;
			$full_file = $dir.DS.$file;
			$file_name = get_filetype ($file);
			
			if (!$options['allow_types']) $del_file = 1;
			elseif (in_array ($file_name, $options['allow_types'])) $del_file = 1;
			elseif ($file_name == $options['allow_types']) $del_file = 1;
			
			if (!$debug) {
				
				if (is_file ($full_file) and $del_file) unlink ($full_file);
				elseif (is_dir ($full_file)) dir_delete ($full_file, $options, $debug);
				
			} else debug ($full_file);
			
		}
		
		if ($options['del_dirs']) rmdir ($dir);
		
	}
	
	function explode_filename ($file) {
		
		if (file_exists ($file)) $file = realpath ($file);
		
		$path0 = explode (':', $file);
		$part1 = explode_filepath ($file);
		
		$file_name = $part1[end_key ($part1)];
		unset ($part1[end_key ($part1)]);
		
		$path = ($part1 ? implode_filepath ($part1).DS : '');
		
		if ($path0[1]) $drive = $part1[0].DS; else $drive = '';
		
		$name = explode ('.', $file_name);
		
		if (count ($name) > 1) {
			
			$type = $name[end_key ($name)];
			unset ($name[end_key ($name)]);
			
			$file_name = implode ('.', $name);
			
		} else $type = '';
		
		return ['full' => (($file == DS) ? '' : $file), 'drive' => $drive, 'path' => $path, 'path_name' => $path.$file_name, 'name' => $file_name, 'name_ext' => $file_name.($type ? '.'.$type : ''), 'exp' => $type];
		
	}
	
	function get_filesize ($file) {
		return mksize (@filesize ($file));
	}
	
	function dash_filepath ($file) {
		return str_replace (['/', '<\\'], [DS, '</'], $file);
	}
	
	function implode_filepath ($array, $sep = DS) {
		return implode ($sep, $array);
	}
	
	function explode_filepath ($path, $sep = DS) {
		
		if ($sep == DS) $path = dash_filepath ($path, $sep);
		return explode ($sep, $path);
		
	}
	
	function get_filepath ($path, $sep = DS) {
		
		if (!is_dir ($path)) {
			
			$path = explode_filepath ($path, $sep);
			unset ($path[end_key ($path)]);
			$path = implode_filepath ($path, $sep);
			
		}
		
		return $path;
		
	}
	
	function trim_filepath ($path, $sep = DS) {
		return rtrim ($path, $sep);
	}
	
	function get_filename ($name, $exp = 0, $sep = DS, $debug = 0) { // Выводит имя файла
		
		if ($name) {
			
			if (!is_array ($name)) {
				
				$name = explode ('#', $name);
				$name = explode ('?', $name[0]);
				$name = explode_filepath ($name[0], $sep);
				
				$name = explode ('.', end ($name));
				
			}
			
			if (!$exp and count ($name) > 1) unset ($name[end_key ($name)]);
			$name = implode ('.', $name);
			
		}
		
		return $name;
		
	}
	
	function get_filetype ($file) {
		
		if (!is_dir ($file)) {
			
			$file = explode ('.', get_filename ($file, 1));
			if (end ($file)) $file = strtolower (end ($file)); else $file = '';
			
		}
		
		return $file;
		
	}
	
	function dirname_r ($path, $num = 0) {
		
		if ($num > 0)
		return dirname (dirname_r ($path, --$num));
		else
		return dirname ($path);
		
	}
	
	function add_ds ($path, $sep = DS) {
		
		if (substr ($path, -1) != $sep) $path .= $sep;
		return $path;
		
	}
	
	function ds_rtrim ($path, $sep = DS) {
		
		if (substr ($path, -1) == $sep) $path = substr ($path, 0, -1);
		return $path;
		
	}
	
	function clear_filepath ($path) { // Очищает опасные символы в $path
		
		$find = ['<', '>', '\\', '//', '..', '%00', '[NULL]'];
		return str_replace ($find, '', $path);
		
	}
	
	function get_short_path ($file) {
		return str_replace ([dash_filepath ($_SERVER['DOCUMENT_ROOT']), dash_filepath (MASH_DIR)], '', $file);
	}
	
	function prep_file_str ($str) {
		return str_replace ("\r\n", "\n", trim ($str));
	}
	
	function download ($url, $file) {
		file_put_contents ($file, fopen ($url, 'r'));
	}