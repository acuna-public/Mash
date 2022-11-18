<?php
	
	namespace Mash;
	
	require 'Service.php';
	
	abstract class CLI extends Service {
		
		public $argv = [], $params = [], $argsFiles = [], $argsFilesNum = 0, $start = 0;
		
		protected $args = [];
		
		function setArgs (array $args) {
			$this->args = $args;
		}
		
		protected function prepRootDir ($dir) {
			
			if (defined ('CLI')) {
				
				return dirname ($this->argv[$this->start]) . '/';
				
			} else return $dir;
			
		}
		
		function onInit () {
			
			parent::onInit ();
			
			global $argv;
			$this->argv = $argv;
			
			$this->addFile (['libraries', 'cli']);
			
			foreach ($this->files as $file) require $file;
			
			$i = $this->start;
			
			if ($this->argv)
			foreach ($this->argv as $key => $value)
			if ($key >= $this->start) {
				
				if ($value[0] == '-') { // Аргумент
					
					$data = explode ('=', $value);
					
					$value2 = '';
					
					if (count ($data) > 1)
					for ($i2 = 1; $i2 < end_key ($data); $i2++) {
						
						if ($i2 > 1) $value2 .= '=';
						$value2 .= $data[$i2]; // Собираем обратно значение, если оно вдруг имеет знак равенства
						
					}
					
					$key2 = $data[0];
					
					if (substr ($key2, 0, 2) == '--') { // Длинный
						
						$key3 = substr ($key2, 2);
						$this->params[$key3] = $this->argValue ($value2, $data, 1);
						
					} elseif (substr ($key2, 0, 1) == '-') { // Короткий
						
						$key3 = substr ($key2, 1);
						$this->params[$key3] = $this->argValue ($value2, $data, 1);
						
					}
					
				} else { // Файл
					
					$this->argsFiles[$i] = $value;
					if (is_numeric ($key)) ++$i;
					
				}
				
				foreach ($this->args as $arg => $data2) {
					
					$value = (is_isset ('def_val', $data2) ? $data2['def_val'] : '');
					
					if (is_isset ('short', $data2) and isset ($this->params[$arg[0]]))
						$this->params[$arg] = $value;
					elseif (!isset ($this->params[$arg]))
						$this->params[$arg] = $value;
					
				}
				
			}
			
			$this->argsFilesNum = count ($this->argsFiles);
			
		}
		
		function argValue ($value, $data, $index) {
			
			if (isset ($data[$index]))
				$value = str_replace ('^', "\n", $value);
			elseif ($value === 'false' or $value === 0)
				$value = false;
			else
				$value = true;
			
			return $value;
			
		}
		
		function system ($cmd) {
			
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
		
		function print ($text, $text_color = 0, $bg_color = self::MESS_BG_COLOR_DEFAULT, $text_type = self::MESS_TEXT_TYPE_INTENSIVE) {
			
			echo "\e[1m\e[".$text_type."m\e[".$text_color."m\e[".$bg_color."m".$text."\e[0m";
			
			flush ();
			ob_flush ();
			
		}
		
		function testPrint () {
			
			for ($i = 30; $i <= 39; $i++)
				$this->println ($i, $i);
			
			for ($i = 90; $i <= 97; $i++)
				$this->println ($i, 39, $i);
			
			for ($i = 40; $i <= 49; $i++)
				$this->println ($i, 39, $i);
			
			for ($i = 0; $i <= 8; $i++)
				$this->println ($i, 39, $i);
			
		}
		
		function stdin () {
			return trim (strtolower (fgets (STDIN)));
		}
		
	}