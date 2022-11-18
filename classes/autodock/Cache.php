<?php
/*
 ========================================
 Mash Framework (c) 2010-2015
 ----------------------------------------
 https://mash.ointeractive.ru/
 ========================================
 O! Interactive (support@ointeractive.ru)
 ----------------------------------------
 Класс кеширования
 ========================================
*/
	
	if (!defined ('MASH')) die ('File must be started only through the main framework cover');
	
	class Cache {
		
		public $version = '1.4';
		public $dir = '';
		public $exp = 'json';
		public $allow_cache = 1;
		public $blank = 'N;';
		public $debug = 0;
		public $test = '';
		
		/**
		 Режим отладки:
		 
		 0 - Отключен
		 1 - Выводит полное имя файла кеша
		 
		*/
		
		private $dirs = ['components', 'modules'];
		
		function __construct () {
			
			$this->test = get_class ().' Подключен.<br/>';
			
		}
		
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		// Служебные функции
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
		private function prep_file ($file) {
		
			if (is_array ($file)) $file = implode_filepath ($file);
			return $file;
			
		}
		
		function file_name ($name) {
			
			$name = $this->prep_file ($name);
			return dash_filepath ($this->dir.DS.$name.'.'.$this->exp);
			
		}
		
		function name ($name, $is_module = 0) {
			
			if ($is_module)
			$name = 'modules/'.$this->mash->mod.'_'.$name;
			else
			$name = 'components/'.$name.'_'.$this->mash->mod;
			
			return $name;
			
		}
		
		private function file_content ($file, $debug) {
			
			$file = $this->file_name ($file);
			if ($debug) debug ($file);
			
			if (file_exists ($file))
			$file = file_get_content ($file);
			
			if ($this->is_clear ($file)) $file = '';
			if ($debug) debug ($file);
			
			return $file;
			
		}
		
		private function file_array ($file) {
			
			$array = file2array ($this->file_name ($file));
			if (!$array or $array[0] == $this->blank) $array = [];
			
			return $array;
			
		}
		
		function is_clear_file ($file) {
			
			$file = $this->output ($file);
			if ($this->is_clear ($file)) return true; else return false;
			
		}
		
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		// Работа с контентом
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
		function input ($data, $file, $cache = 1, $debug = 0) {
			
			if (!$this->allow_cache or !$cache) return false;
			
			$file = $this->file_name ($file);
			@write_file ($data, $file, 'wb+');
			if ($debug) debug ($file);
			
			return $data;
			
		}
		
		function output ($file, $cache = 1, $debug = 0) {
			
			if (!$this->allow_cache or !$cache) return false;
			
			if ($this->debug == 1 or $debug) debug ($file);
			return $this->file_content ($file);
			
		}
		
		function arrayInput ($data, $file, $debug = 0) {
			
			if ($this->debug == 1 or $debug) print_r ($data);
			
			if (!$file) echo ('Unknown cache file!');
			
			$file = $this->file_name ($file);
			if ($this->debug == 1 or $debug) debug ($file);
			
			make_dir (get_filepath ($file));
			write_file (array2json ($data), $file, 'wb+');
			
			return $data;
			
		}
		
		function arrayOutput ($file, $cache = 1, $debug = 0) {
			
			if ($cache) {
				
				$file = $this->file_content ($file, $debug);
				
				if ($file)
				$file = json2array ($file);
				else
				$file = false;
				
			} else $file = false;
			
			return $file;
			
		}
		
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		// Служебные функции
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
		function blank ($file) {
			
			$file = $this->file_name ($file);
			@write_file ($this->blank, $file);
			
		}
		
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		// Функции очистки
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
		function clear ($file = '', $debug = 0, $full_path = 1) {
			
			if ($file) {
				
				if ($full_path) $file = $this->file_name ($file);
				
				if (file_exists ($file)) @unlink ($file);
				
				if ($this->debug == 1 or $debug) debug ($file);
				
			} else dir_delete ($this->dir, ['del_dirs' => 0, 'allow_filetypes' => $this->exp], $debug);
			
		}
		
		function site_clear ($url, $debug = 0) { // Очищаем кеш на сайте $url
			dir_delete ($this->mash->getRootDir ().'/sites/'.$url.'/cache', ['del_dirs' => 0, 'allow_filetypes' => $this->exp], $debug);
		}
		
		function global_clear ($debug = 0) { // Очищаем кеш на всех сайтах системы
			foreach ($this->mash->site_urls () as $url) $this->site_clear ($url);
		}
		
		function all_clear ($mod, $debug = 0, $dir = '', $i = 0) { // Очищаем кеш компонентов и модуля $mod
			
			if (!$dir) $dir = $this->dir;
			
			foreach (scandir ($dir) as $file) {
				++$i;
				
				$full_file = $dir.'/'.$file;
				if ($i == 1) $this->clear ('modules', $debug);
				
				if (allow_filename ($file)) {
					
					if (is_file ($full_file) and get_filetype ($file) == $this->exp) {
						
						$file2 = explode ('_', get_filename ($full_file));
						
						if ($file2[0] == $mod or $file2[1] == $mod) $this->clear ($full_file, $debug, 0);
						
					} elseif (is_dir ($full_file) and in_array ($file, $this->dirs)) $this->all_clear ($mod, $debug, $full_file, $i);
					
				}
				
			}
			
		}
		
		function component_clear ($mod, $debug = 0) { // Очищаем кеш компонента $mod
			
			$dir = $this->dir.DS.'components';
			
			foreach (dir_scan ($dir) as $file)
			if (is_file ($file) and allow_filename ($file) and get_filetype ($file) == $this->exp) {
				
				$file2 = explode ('_', get_filename ($file));
				if ($file2[1] == $mod) $this->clear ($file, $debug, 0);
				
			}
			
		}
		
		function is_clear ($var) {
			if (!not_empty ($var) or $var == 'a:0:{}' or $var == 'N;')
			return true; else return false;
		}
		
	}