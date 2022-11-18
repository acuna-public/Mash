<?php
/*
 ========================================
 Mash Framework (c) 2010-2014, 2016
 ----------------------------------------
 https://mash.ointeractive.ru/
 ========================================
 O! Interactive (support@ointeractive.ru)
 ----------------------------------------
 Класс управления текстовыми Базами
 Данных
 LisaS.TextSQL (с) 2010-2014, 2016 Acuna
 Копирование и изменение разрешается
 только с согласия автора
 (at@ointeractive.ru).
 ========================================
*/
	
	if (!defined ('MASH')) die ('File must be started only through the main framework cover');
	
	class TextDB {
		
		public
		$dir = '.',
		$dir1 = '.',
		$dir2 = '.',
		$base = '',
		$exp = 'db',
		$version = '2.0',
		$error = [],
		$debug = 0,
		$options = ['error_templ' => false, 'debug' => 1];
		
		private
		$charset = 'utf-8',
		$sep2 = ['-- '];
		
		/**
		 
		 2.0	20.12.2016
			
			Механизм переписан для JSON
		 
		*/
		
		/**
		 Режим отладки. Значения:
		 
		 1 - Выведет полный путь к файлу.
		 2 - Выведет полный путь к файлу, но не запишет данные в файл.
		 3 - Выведет готовые строки запроса функций super_query () и implode ().
		 4 - Выведет готовые строки запроса функций super_query () и implode (), но не запишет данные в файл.
		 5 - Не запишет данные в файл.
		 
		*/
		
		private $mash;
		
		function __construct ($mash) {
			$this->mash = $mash;
		}
		
		function file ($table, $type = 0, $debug = 0) { // Получение пути к файлу
			
			if ($type == 0) $this->dir = $this->dir1; else $this->dir2;
			
			if ($this->base) $dir = $this->dir.'/'.$this->base; else $dir = $this->dir;
			
			$target = $dir.'/'.$table.'.'.$this->exp;
			
			if ($this->debug == 1 or $this->debug == 2 or $debug) debug ($target);
			
			return $target;
			
		}
		
		private function _get_row ($table, $order, $type, $debug) {
			
			$content = $this->explode (file_get_content ($this->file ($table, $type, $debug)));
			
			if (is_json ($content)) {
				if ($order == 'desc') krsort ($content);
			} else $this->error[1] = $this->file ($table, $type);
			
			if ($this->error) $this->error ($this->error); else return $content;
			
		}
		
		function get_row ($table, $order = 'asc', $type = 0, $debug = 0) {
			return $this->_get_row ($table, $order, $type, $debug);
		}
		
		function get_row2 ($table, $order = 'asc', $type = 0, $debug = 0) {
			return $this->_get_row ($table, $order, $type, $debug);
		}
		
		function get_global_row ($table, $order = 'asc', $debug = 0) {
			return $this->get_row ($table, $order, 1, $debug);
		}
		
		function num_rows ($table) {
			return count ($this->get_row ($table));
		}
		
		function safesql ($string) { // Очистка строки $string от разделителей. В отличии от MySQL, нет нужды прогонять данные через эту функцию при вводе, т. к. она уже используется в запросах super_query ().
			return strip_slashes ($string);
		}
		
		function super_query ($type, $what, $where, $blank = '', $debug = 0) { // Простой запрос
			
			$output = [];
			$keys_array = [];
			
			if ($type == 'insert') { // Добавление
				
				$where = $this->super_query ('select', '', $where);
				
				foreach ($what as $key => $value) {
					
					$key = $this->safesql ($key);
					$value = $this->safesql ($value);
					
					$where[$key] = $value;
					
				}
				
				$output = $this->implode ($where);
				
			} elseif ($type == 'select') { // Выборка
				
				if (not_empty ($where))
				$output = $this->explode ($where, 0, $debug);
				
			} elseif ($type == 'update') { // Обновление
				
				if (!is_array ($where)) $where = $this->super_query ('select', '', $where);
				
				foreach ($what as $key => $value)
				if (is_array ($value)) {
					
					$value1 = $where[$value[0]];
					
					if ($value[1] == '+' or $value[1] == '-') {
						
						if ($value[1] == '+') $value[1] = ($value1 + 1); else $value[1] = ($value1 - 1);
						$value[1] = intval_correct ($value[1]);
						
					}
					
					$where[$value[0]] = $value[1];
					
				} else $where[$key] = $value;
				
				$output = array2json ($where, 0, 1);
				
			} elseif ($type == 'delete') { // И удаление
				
				if (!is_array ($where)) $where = $this->super_query ('select', '', $where);
				
				foreach ($where as $key2 => $value2)
				if ($key2 != $what) $output[$key2] = $value2;
				
				$output = array2json ($output);
				
			} else $this->error[2] = $type;
			
			if ($this->debug == 3 or $this->debug == 4) debug ($output);
			if ($this->error) $this->error ($this->error); else return $output;
			
		}
		
		function query ($type, $table, $id, $dist = 0, $set_type = 'ununique') { // Запрос с записью (работа с файлами .db)
			
			$output = [];
			$open_type = 'w';
			$write_file = 1;
			
			if ($type == 'select') { $table1 = $table; $table = $id; $id = $table1; }
			
			if (!is_array ($id) and $type != 'select') $id = $this->get_id ($table, $id);
			
			$rows = $this->get_row ($table);
			$count_rows = count ($rows);
			
			switch ($type) {
				
				default: $this->error[2] = $type; break;
				
				case 'select': // Выборка
					
					$write_file = 0;
					$id = make_array ($id);
					//print_r ($id);
					$i = 0;
					
					foreach ($rows as $row) {
						
						foreach ($id as $key => $value) {
							
							if (is_numeric ($key) and !$value) $id2 = $value; else $id2 = $key;
							
							$id3 = $this->super_query ('select', 'id', $row);
							$row = $this->super_query ('select', $id2, $row);
							
							if (is_numeric ($key) and !$value) $output[$id3] = $row;
							elseif ($id3 == $id[0]) $output = $row;
							
						}
						
					}
					
				break;
				
				case 'insert': // Вставка новой строки
					
					$open_type = 'a';
					
					$output0 = '';
					$id['id'] = $this->insert_id ($table);
					
					$output2 = $this->implode ($id);
					
					foreach ($rows as $key => $string) {
						
						if ($key + 1 == $count_rows) $output0 = NL;
						
						$string = explode ($tunnel_sep[0], $string);
						$id = explode ($tunnel_sep[1], $string[0]);
						unset ($string[0]); // Отбросили id
						
						$string = array2json ($string); // И собрали массив
						
						if ($string == $output2) $this->error[3] = $id[1];
						
					}
					
					$output = $output0.$output1.$output2;
					
				break;
				
				case 'update': // Редактирование значения столбца
					
					$string = $rows[$id]; // Нужная строка
					
					$new_string = $this->super_query ('update', $dist, $string);
					
					foreach ($rows as $key => $old_string)
					if ($key == $id) $output[] = $new_string; else $output[] = $old_string;
					
				break;
				
				case 'delete': // Удаление
					
					unset ($rows[$id]);
					foreach ($rows as $old_string)
					$output[] = $old_string;
					
				break;
				
				case 'move': // Перемещение
					
					$output = array_move ($rows, $id, $dist);
					
				break;
				
			}
			
			if ($this->error) $this->error ($this->error);
			elseif ($this->debug != 2 and $this->debug != 4 and $this->debug != 5 and $write_file) write_file ($output, $this->file ($table), $open_type);
			
			return $output;
			
		}
		
		function id_query ($table, $id, $debug = 0) { // Запрос выборки строки с идентификатором $id в таблице $table. Массив на выходе. Не имеет смысла в MySQL.
			
			$row = $this->get_row ($table);
			$row = $row[$this->get_array_id ($id)];
			
			$row = $this->super_query ('select', '', $row, '', $debug);
			
			return $row;
			
		}
		
		function array_query ($table, $col = 'name', $type = 0) { // Выполняет запрос и формирует двумерный массив с ключом $col
			
			$output = [];
			foreach ($this->get_row ($table, 'asc', $type) as $row)
			$output[$row[$col]] = $row;
			
			return $output;
			
		}
		
		function get_array_id ($id) {
			return intval_correct ($id) - 1;
		}
		
		function get_id ($table, $id, $row_name = 'id') {
			
			foreach ($this->get_row ($table) as $key => $string) {
				
				$id2 = $this->super_query ('select', $row_name, $string);
				if ((int) $id == $id2) return $key;
				
			}
			
		}
		
		function explode ($string) {
			return json2array ($string);
		}
		
		function implode ($array, $pars = 0) { // Собирает массив в строку формата LisaS.TextSQL
			
			if ($pars) {
				
				$parse = new lisas_parse;
				
				$parse->lite_parse = true;
				$parse->allow_code = true;
				
			}
			
			$output = [];
			$array = make_array ($array);
			
			foreach ($array as $key => $value) {
				
				if ($pars) $value = $parse->decode_content ($value, ['wysiwyg' => 0, 'filter' => 0]);
				$output[$key] = $value;
				
			}
			
			$output = array2json ($output);
			
			if ($this->debug == 3 or $this->debug == 4) debug ($output);
			
			return $output;
			
		}
		
		function get_ids ($table, $row_name = 'id') { // Получает все идентификаторы строк таблицы $table
			
			$ids = [];
			foreach ($this->get_row ($table) as $string)
			$ids[] = $this->super_query ('select', $row_name, $string);
			
			return $ids;
			
		}
		
		function insert_id ($table) {
			
			$id = $this->get_ids ($table); // Получает все id таблицы $table
			return (num_array ('max', $id)) + 1; // Находим самый большой и прибавляем единицу
			
		}
		
		function error_array ($num, $data) {
			
			$error_array = [
				
				0 => 'Неверный код ошибки!',
				1 => 'Файл <b>{data}</b> не найден, либо имеет неверное содержимое.',
				2 => '<b>{data}</b> - неверный тип запроса!',
				3 => 'Эта строка уже присутствует в БД под id <b>{data}</b>.',
				4 => 'Колонки не могут иметь числовые названия!',
				
			];
			
			$num = (int) $num;
			return str_replace ('{data}', str_replace (ROOT_DIR, '', $data), $error_array[$num]);
			
		}
		
		function error ($error) {
			
			$num = array_keys ($error);
			$error = $this->error_array ($num[0], $error[$num[0]]);
			$error = dash_filepath ($error);
			$temp = $lisas->load_templ ($lisas->config['template'], 'text_db_error', 1);
			
			$headers = '<title>TextSQL Fatal Error</title>
';
			$headers .= '<meta http-equiv="Content-Type" content="text/html; charset='.$this->charset.'"/>
';
			
			$temp = str_replace ('{headers}', $headers, $temp);
			$temp = str_replace ('{body}', '', $temp);
			
			$temp = str_replace ('{error}', $error, $temp);
			$temp = str_replace ('{error_num}', $num[0], $temp);
			$temp = str_replace ('{THEME_ADMIN}', $http_dir['admin_theme'], $temp);
			
			$temp = $lisas->config['doctype'].NL.$temp;
			
			if ($this->options['error_templ']) {
				
				if ($this->options['debug'])
				lisas_error ($error);
				else
				echo $error;
				
			} else die ($temp);
			
		}
		
		function migrate ($file, $options = []) {
			
			$files = [];
			
			if (is_dir ($file))
			$files = dir_scan ($file, ['recursive' => 1, 'allow_types' => 'db', 'deny_files' => [$options['deny_files']]]);
			else
			$files = $file;
			
			foreach ($files as $file) {
				
				$i = 0;
				$output = [];
				
				$strings = file2array ($file);
				
				foreach ($strings as $str) {
					
					$str = trim ($str, $tunnel_sep[0]);
					
					foreach (explode ($tunnel_sep[0], $str) as $str) {
						
						$str = explode ($tunnel_sep[1], $str);
						
						$str_list = explode ($tunnel_sep[3], $str[1]);
						if (count ($str_list) > 1) $str[1] = $str_list;
						
						$output[$i][$str[0]] = $str[1];
						
					}
					
					++$i;
					
				}
				
				//print_r (json2array ($this->implode ($output)));
				file_put_content ($this->implode ($output), $file);
				
			}
			
		}
		
	}