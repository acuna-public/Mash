<?php
/*
 ========================================
 Mash Framework (c) 2013-2017, 2019-2022
 ----------------------------------------
 https://mash.ointeractive.ru/
 ========================================
 O! Interactive (support@ointeractive.ru)
 ----------------------------------------
 Класс баз данных
 ========================================
*/
	
	namespace DB;
	
	if (!defined ('MASH')) die ('File must be started only through the main framework cover');
	
	define ('COUNT', 'count');
	define ('LOWER', 'lower');
	define ('IN', 'IN');
	define ('IN_RAW', 'IN_RAW');
	define ('IN_LOWER', 'in_lower');
	define ('IN_NOT_NULL', 'IN_NOT_NULL');
	define ('LIKE', 'LIKE');
	define ('LIKE_LOWER', 'LIKE_LOWER');
	define ('MIN', 'MIN');
	define ('MAX', 'MAX');
	define ('DISTINCT', 'DISTINCT');
	
	abstract class Adapter {
		
		public
			$query_num = 0,
			$query_time = 0,
			$options = [],
			
			/**
			 * Уровень ошибок. Значения:
			 *
			 * 0 - Отключены
			 * 1 - Ошибки индексов
			 * 2 - Все ошибки
			 */
			
			$error_level = 0,
			
			/**
			 *
			 * Режим отладки. Значения:
			 *
			 * 1 - Выведет текст запроса
			 * 2 - Выведет текст, но не выполнит запрос
			 * 3 - Не выведет текст и не выполнит запрос
			 * 4 - Выполнит запрос и запишет его текст в файл
			 * 5 - Выполнит запросы и запишет их тексты в файл db_result.log
			 */
			
			$debug = 0,
			
			$test,
			$config = [],
			$show_error = true,
			$no_error_log_types = [self::TRUNCATE],
			$no_error_log_errors = [1053],
			$max_log_query_time = 3,
			$error_code = 0,
			$cache_data = [],
			$echo_queries_types = [],
			$version = '',
			$time_stop;
		
		const SELECT = 'select', INSERT = 'insert', UPDATE = 'update', DELETE = 'delete', TRUNCATE = 'truncate';
		
		const KEY = 'KEY', PRIMARY = 'PRIMARY', UNIQUE = 'UNIQUE', FULLTEXT = 'FULLTEXT', AUTO_INCREMENT = 'AUTO_INCREMENT', NULL = 'NULL', NOT_NULL = 'NOT NULL', CREATE = 'CREATE', DATABASE = 'DATABASE';
		
		static $SUM = 'SUM';
		
		protected
			$db_id = false,
			$connected = false,
			$query_list = [],
			$query_id = false,
			$sep = ['-- SEP --'],
			$server_version,
			$query_options = ['insert_cols' => 0],
			$query,
			$cache;
		
		const ASC = 'asc', DESC = 'desc';
		
		abstract function getName ();
		abstract function getTitle ();
		abstract function getVersion ();
		
		function __construct ($config = []) {
			$this->config = $config;
		}
		
		protected function getDefConfig () {
			return [];
		}
		
		function getConvertTypes () {
			return [];
		}
		
		abstract protected function doConnect ();
		
		function ping (): bool {
			return true;
		}
		
		protected function connectErrorCode () {
			return 0;
		}
		
		protected function connectErrorText () {
			return '';
		}
		
		abstract function version (): string;
		
		protected function errorCode () {
			return 0;
		}
		
		protected function errorText () {
			return '';
		}
		
		protected function selectDB (): bool {
			return true;
		}
		
		protected function init () {}
		
		abstract function doQuery ($query = [], $options = []);
		
		protected function setCharset () {}
		
		function setCache (\Cache $cache) {
			
			$this->cache = $cache;
			return $this;
			
		}
		
		function connect () {
			
			if ($this->config) {
				
				$this->config = array_extend ($this->config, $this->getDefConfig (), [
					
					'host' => '127.0.0.1',
					'collate' => 'utf8',
					'sharding' => false,
					'sharding_rows_num' => 10000,
					'prefix' => '',
					'base_name' => '',
					
				]);
				
				$this->options = array_extend ($this->options, [ // Глобальные настройки
					
					'error_templ' => true,
					'log_error' => true,
					'error_not_show_queries' => false,
					'session_read' => [],
					'session_write' => [],
					'cache' => false,
					
				]);
				
				$this->config['port'] = intval_correct ($this->config['port'], $this->getDefConfig ()['port']);
				
			}
			
			if ($this->config['base_name']) {
				
				$this->db_id = $this->doConnect ();
				$this->init ();
				
			}
			
			if ($this->db_id) {
				
				$this->version .= 'DB/'.$this->getVersion ().' '.$this->getName ().'/'.$this->version ();
				
				if (!$this->selectDB ())
					throw new \DBException ($this, $this->errorText (), $this->errorCode ());
				
				$this->setCharset ();
				
			} else if ($this->config['base_name'])
				throw new \DBException ($this, $this->connectErrorText (), $this->connectErrorCode (), '', 0);
			
		}
		
		private function prefix () {
			
			if ($this->config['prefix'])
				return $this->config['prefix'].'_';
			else
				return '';
			
		}
		
		function truncateTables (array $tables) {
			
			foreach ($tables as $table)
				$this->query (['truncate', 'table', $table]);
			
		}
		
		function table ($table, $col = '', $table_id = '', $add_quotes = 1, $options = []) {
			
			$table = array_implode ($table, '_', 1);
			
			//$table = (!is_isset ('no_db_name', $options) ? $this->config['base_name'].$this->table_quote ().'.'.$this->table_quote () : '');
			$table = $this->prefix ().$table;
			
			if ($table_id > 1) $table .= $table_id;
			
			if ($add_quotes) $table = $this->addquotes ($table);
			
			if (not_empty ($col)) $table .= '.'.$this->addquotes ($col);
			
			return $table;
			
		}
		
		private function _query ($type, $query, $debug, $options) {
			
			$this->setReportsType ();
			
			$time_start = timer_start ();
			
			if (isset ($options['type']) and $options['type'] == self::INSERT and $this->query_options['insert_cols'] <= 1) {
				
				if (!isset ($options['return_col']))
					$options['return_col'] = 'id';
				
			} else $options['return_col'] = '';
			
			//if (!$this->ping ()) $this->connect ();
			
			$this->query_id = $this->doQuery ($query, $options);
			$this->time_stop = timer_stop ($time_start);
			
			++$this->query_num;
			$this->query_time += $this->time_stop;
			
			$this->time_stop = round ($this->time_stop, 4);
			
			if ($debug == 1)
				debug ($this->query_num.'. '.$query.' ('.$this->time_stop.' сек.)');
			
			if ($debug == 4)
				debug_write ($query, 'db_result', 'sql');
			
			if (
				!in_array ($type, $this->no_error_log_types)
				and
				($debug == 5 or ($debug == 6 and $this->time_stop >= $this->max_log_query_time))
			)
				$this->log_record ('results', $query);
			
			if (in_array ($type, $this->echo_queries_types))
				debug ($this->query_num.'. '.$query.' ('.$this->time_stop.' сек.)');
			
			/*$this->query_list[] = [
				
				'resource' => $output,
				'time' => $this->time_stop, 
				'query' => $query,
				'num' => (count ($this->query_list) + 1),
				
			];*/
			
			if (!$this->query_id)
				throw new \DBException ($this, $this->errorText (), $this->errorCode (), $query);
			
			return $this->query_id;
			
		}
		
		function log_record ($type, $query) {
			
			debug_write_cont ('-- '._LINE_.'
-- Запрос '.$this->query_num.' ('.log_date ().') '.$this->time_stop.' сек.
-- '._LINE_.'

'.(!defined ('CLI') ? br2nl ($query) : $query).'

', 'db_'.$type, 'sql');
			
		}
		
		function insert_values ($post, $deny_keys = []) {
			
			$keys = [];
			$values = [];
			
			foreach ($post as $key => $value)
				if (!in_array ($key, $deny_keys)) {
					
					if (!is_array ($value)) $value = $this->safesql ($value);
					
					$keys[] = $this->addquotes ($key);
					$values[] = $this->value ($value);
					
				}
			
			$output = array (sep_implode ($keys), sep_implode ($values));
			
			return $output;
			
		}
		
		function update_values ($post, $deny_values = [], $no_empty = 0, $quotes = 1, $row = [], $options = []) {
			
			$values = [];
			//$options = array_extend ($options, ['parse' => 1]);
			
			if ($row) $post = array_parse ($post, $deny_values, 0, $row);
			
			foreach ($post as $key => $value) {
				
				if (!in_array ($key, $deny_values)) {
					
					if (is_array ($value)) {
						
						if (!isset ($value[2])) $value[2] = 1;
						
						if ($value[1])
							$value = $this->concat ($value[0], $value[1], $value[2]);
						else
							$value = $this->addquotes ($value[0]);
						
					} else {
						
						if ($quotes) $value = $this->safesql ($value);
						$value = $this->value ($value);
						
					}
					
					$values[] = $this->addquotes ($key).' = '.$value;
					
				}
				
			}
			
			$values = sep_implode ($values);
			
			return $values;
			
		}
		
		function chunk_query_start ($type, $what, $table, $options = [], $debug = 0) {
			
			$output = '';
			
			$table = $this->addquotes ($table);
			
			switch ($type) {
				
				case self::INSERT: {
					
					$output .= 'INSERT INTO '.$table.'
';
					
					if (!is_isset ('no_keys', $options)) {
						
						if (!is_isset ('deny_keys', $options))
							$options['deny_keys'] = [];
						
						$i = 0;
						
						$output .= '(';
						
						foreach ($what as $key => $value) {
							
							if (!in_array ($key, $options['deny_keys'])) {
								
								if ($i > 0) $output .= ', ';
								
								$output .= $this->addquotes ($key);
								++$i;
								
							}
							
						}
						
						$output .= ')
';
						
					}
					
					$output .= 'VALUES
';
					
					break;
					
				}
				
			}
			
			return $output;
			
		}
		
		private function chunk_query_part ($type, $part, $data, $options = []) {
			
			$output = '';
			
			switch ($type) {
				
				case self::INSERT: {
					
					if (!isset ($options['deny_keys']))
						$options['deny_keys'] = [];
					
					if ($part > 0) $output .= ',
';
					
					$output .= '(';
					
					$i = 0;
					
					foreach ($data as $key => $value) {
						
						if (!in_array ($key, $options['deny_keys'])) {
							
							if ($i > 0) $output .= ', ';
							
							$def_val = (isset ($where['cols'][$key]) ? $where['cols'][$key] : '');
							
							//if (!$value) $value = $def_val;
							
							if (
								($def_val === 'auto_increment' and $value and $value !== 'auto_increment') or
								$def_val !== 'auto_increment'
							)
								$output .= $this->value ($this->safesql ($value));
							
						}
						
						++$i;
						
					}
					
					$output .= ')';
					
					break;
					
				}
				
			}
			
			return $output;
			
		}
		
		protected function increment ($data) {
			return '';
		}
		
		protected function default ($data) {
			return strtoupper ($data[0].$length.' '.(($data[2] == 'null') ? 'null' : 'not null').' default '.$this->value ($data[3])).',';
		}
		
		protected function enum ($data, $items) {
			return '';
		}
		
		protected function primaryKeyKeyword () {
			return 'PRIMARY KEY';
		}
		
		protected function indexKeyword () {
			return 'INDEX';
		}
		
		protected function uniqueIndexKeyword ($table, $key) {
			return 'UNIQUE INDEX';
		}
		
		protected function indexKeys ($key, $index) {
			return '';
		}
		
		protected function engine ($engine) {
			return '';
		}
		
		function query_output ($data, $debug = 0) {
			
			$what2 = [];
			$output = '';
			$query = '';
			$where2 = '';
			
			if (isset ($data[4]) and !is_array ($data[4])) {
				
				$debug = $data[4];
				$data[4] = ['deny_keys' => []];
				
			} elseif (!isset ($data[4]['deny_keys']))
				$data[4]['deny_keys'] = [];
			
			$options = $data[4];
			if (!is_isset ('no_db_name', $options)) $options['no_db_name'] = false;
			
			$table_arr = make_array ($data[2]);
			if (is_array ($table_arr[0])) $table_arr[0] = implode ('_', $table_arr[0]);
			
			$table_raw = $this->table ($table_arr[0], '', (count ($table_arr) > 1 ? $table_arr[1] : 0), 0, $options);
			$data[2] = $this->addquotes ($table_raw);
			
			$no_where = 0;
			
			switch ($data[0]) {
				
				case self::INSERT: {
					
					$this->query_options = ['insert_cols' => 0];
					
					if ($data[1]) {
						
						if (is_assoc ($data[1])) {
							
							$query .= $this->chunk_query_start ($data[0], $data[1], $table_raw, $options);
							
							$query .= $this->chunk_query_part ($data[0], 0, $data[1]);
							
						} else {
							
							$query .= $this->chunk_query_start ($data[0], $data[1][0], $table_raw, $options);
							
							foreach ($data[1] as $i => $value) {
								
								$this->query_options['insert_cols']++;
								
								$query .= $this->chunk_query_part ($data[0], $i, $value);
								
							}
							
						}
						
					}
					
					$no_where = 1;
					
					break;
					
				}
				
				case self::UPDATE: {
					
					$data[1] = $this->update_values ($data[1], $options['deny_keys']);
					if ($data[1]) $query = 'UPDATE '.$data[2].' SET '.$data[1];
					
					break;
					
				}
				
				case self::SELECT: {
					
					if (is_array ($data[1])) {
						
						foreach ($data[1] as $key => $value) {
							
							if (is_numeric ($key)) $key = $value;
							$value = make_array ($value);
							
							if ($key === COUNT or $key == self::$SUM) {
								
								if (!$value[0]) $value[0] = strtolower ($key);
								if (!isset ($value[1]) or !$value[1]) $value[1] = '*';
								
								if ($value[1] != '*')
									$value[1] = $this->addquotes (strtolower ($value[1]));
								
								$output = $key.'('.$value[1].') AS '.$this->addquotes (strtolower ($value[0]));
								
							} elseif ($key === MIN or $key === MAX or $key === DISTINCT) {
								
								if (!$value[1]) $value[1] = $value[0];
									$output = $key.'('.strtolower ($this->addquotes ($value[0])).') AS '.$this->addquotes (strtolower ($value[1]));
								
							} else $output = $this->_prep_what ($value);
							
							$what2[] = $output;
							
						}
						
						$data[1] = sep_implode ($what2);
						
					} else {
						
						if ($data[1] === COUNT) $data[1] = 'COUNT(*) AS '.$this->addquotes ('count');
						elseif ($data[1]) $data[1] = $this->addquotes (lisas_strtolower ($data[1]));
						else $data[1] = '*';
						
					}
					
					$query = 'SELECT '.$data[1].'
FROM '.$data[2];
					
					break;
					
				}
				
				case self::DELETE:
					$query = 'DELETE FROM '.$data[2];
				break;
				
				case self::TRUNCATE:
					$query = 'TRUNCATE '.strtoupper ($data[1]).' '.$data[2];
				break;
				
				case 'drop':
					$query = 'DROP '.strtoupper ($data[1]).' IF EXISTS '.$data[2];
				break;
				
				case 'create': {
					
					$query = '';
					$no_where = 1;
					
					$options = array_extend ($options, [
						
						'engine' => 'InnoDB',
						'not_exists' => true,
					
					]);
					
					switch ($data[1]) {
						
						case 'table':
							
							$cols = '';
							$types_val = [];
							
							if (isset ($data[3]))
							foreach ($data[3]['cols'] as $col => $data) {
								
								if (!$options['compact']) $cols .= '	';
								$cols .= $this->addquotes ($col).' ';
								
								switch ($data[0]) {
									
									default:
										
										if ($data[3] === 'auto_increment' or $data[3] === 'serial') { // TODO
											$cols .= $this->increment ($data);
										} else {
											
											if ($data[0] = $this->getConvertTypes ()) {
												
												$new_type = $data[0][$data[0]];
												
												if (is_array ($new_type)) {
													
													$length = ($new_type[1] ? '('.$data[1].')' : '');
													$new_type = $new_type[0];
													
												} else $length = '';
												
												if ($new_type) $data[0] = $new_type;
												
											} else $length = ($data[1] ? '('.$data[1].')' : '');
											
											$cols .= $this->default ($data);
											
										}
										
										break;
									
									case 'text':
									case 'mediumtext':
									case 'longtext':
										$cols .= $data[0].' NULL,';
									break;
									
									case 'enum':
										
										$data[1] = make_array ($data[1]);
										
										$items = '';
										
										foreach ($data[1] as $i => $item) {
											
											if ($i > 0) $items .= ', ';
											$items .= $this->value ($item);
											
										}
										
										if (!isset ($data[3])) $data[3] = $data[1][0];
										
										$cols .= $this->enum ($data, $items);
										
									break;
									
								}
								
								if ($options['compact']) $cols .= ' '; else $cols .= NL;
								
							}
							
							$indexes = '';
							
							if ($data[3]['indexes']) {
								
								$keys = '';
								
								foreach ($data[3]['indexes'] as $data[0] => $data) {
									
									if (!is_array ($data)) $data = [$data => [$data]];
									
									foreach ($data as $key => $data) {
										
										$index = '';
										
										if (is_numeric ($key)) {
											
											if (is_array ($data)) {
												
												$key = $data[0];
												$data = [$data];
												
											} else $key = $data;
											
										}
										
										$data = make_array ($data);
										
										foreach ($data as $data2) {
											
											$data2 = make_array ($data2);
											$index .= $this->addquotes ($data2[0]).($data2[1] ? '('.$data2[1].')' : '').', ';
											
										}
										
										if (!$options['compact']) $keys .= '	';
										
										if ($data[0] == 'primary')
											$keys .= $this->primaryKeyKeyword ();
										elseif ($data[0] == 'key')
											$keys .= $this->indexKeyword ();
										elseif ($data[0] == 'unique')
											$keys .= $this->uniqueIndexKeyword ($table_arr[0], $key);
										
										$keys .= $this->indexKeys ($key, $index);
										
										if ($options['compact']) $keys .= ', '; else $keys .= ','.NL;
										
									}
									
								}
								
								if ($options['compact'])
									$indexes .= trim ($keys, ', ');
								else
									$indexes .= trim ($keys, ','.NL);
								
							}
							
							if (!$options['compact']) $indexes .= NL;
							
							if ($options['create_table_type'] == 'drop')
								$query .= 'DROP TABLE IF EXISTS '.$this->addquotes ($this->prefix ().$table_arr[0]).';

';
							
							if (!$options['compact']) $cols = rtrim ($cols);
							
							$query .= 'CREATE TABLE'.($options['not_exists'] ? ' IF NOT EXISTS ' : ' ').$this->addquotes ($this->prefix ().$table_arr[0]).' ('.($options['compact'] ? $cols : '
'.$cols.'
').$indexes.')';
							
							$query .= $this->engine ($options['engine']).';';
							
						break;
						
					}
					
					break;
					
				}
				
			}
			
			if (!$no_where and $query) {
				
				if (isset ($data[3])) {
					
					//if ($nested) $sep .= ' (';
					
					$where2 = $this->where ($data[3], $options);
					if (!$where2) $where2 = $this->condition ($data[3], [], 'AND');
					
					if ($where2) $query .= '
WHERE '.$where2;
					
				}
				
				if (is_isset ('group', $options)) {
					
					$options['group'] = make_array ($options['group']);
					
					$group = [];
					foreach ($options['group'] as $data2)
					$group[] = $this->addquotes ($data2);
					
					$query .= '
GROUP BY '.sep_implode ($group);
					
				}
				
				if (is_isset ('order', $options)) {
					
					if ($options['order'] == 'rand')
						$order = 'RAND()';
					else {
						
						$order = [];
						
						foreach ($options['order'] as $data2)
							$order[] = $this->addquotes ($data2[0]).' '.strtoupper ($data2[1]);
						$order = sep_implode ($order);
						
					}
					
					$query .= '
ORDER BY '.$order;
					
				}
				
				if (isset ($options['limit'])) {
					
					if (is_array ($options['limit']))
						$limit = $this->limit ($options['limit'][0], $options['limit'][1]);
					else
						$limit = $this->limit ($options['limit']);
					
					$query .= '
LIMIT '.$limit;
					
				}
				
			}
			
			if ($debug == 1) debug ($query);
			
			return $query;
			
		}
		
		private function where ($data, $options) {
			
			$i = 0;
			$where2 = '';
			
			foreach ($data as $key => $value) {
				
				if (is_numeric ($key) or $key == 'or') {
					
					$output = [];
					foreach ($value as $key => $value)
						$output[] = $this->_prep_items ($key, $value, $options['deny_keys']);
					
					if ($i > 0) $where2 .= NL."\tOR ";
					
					if (count ($output) > 1)
						$where2 .= '(
	'.implode ('
	AND ', $output).'
	)';
					else
						$where2 .= $output[0];
					
				} elseif ($key == 'and') {
					
					$output = [];
					foreach ($value as $key2 => $value2)
					$output[] = $this->addquotes ($key).' LIKE '.$this->value ('%'.$value2.'%');
					
					$where2 .= implode ('
	AND ', $output);
					
				} else {
					
					if ($i > 0) $where2 .= NL."\tAND ";
					$where2 .= $this->_prep_items ($key, $value, $options['deny_keys']);
					
				}
				
				$i++;
				
			}
			
			return $where2;
			
		}
		
		public function limit ($offset, $num = 0) {
			
			if ($num) $offset .= ','.$num;
			return $offset;
			
		}
		
		private function _prep_what ($value) {
			
			if (is_array ($value[0])) $value[0] = implode ('_', $value[0]);
			
			if (isset ($value[1]))
				$output = $this->table ($value[0], $value[1], $value[2], 0);
			else
				$output = $this->addquotes (strtolower ($value[0]));
			
			return $output;
			
		}
		
		private function _prep_items ($key, $value, $deny_keys, $add_quotes = 1) {
			
			$output = '';
			
			if (!in_array ($key, $deny_keys)) {
				
				if ($add_quotes) $key = $this->addquotes ($key);
				
				if (is_array ($value)) {
					
					if (count ($value) >= 2)
						$output = $this->_prep_items2 ($key, $value[1], $value[0]);
					else
						foreach ($value as $key2 => $value2)
							$output = $this->_prep_items2 ($key, $value2, $key2);
					
				} else $output = $this->_prep_items2 ($key, $value);
				
			}
			
			return $output;
			
		}
		
		private function _prep_items2 ($key, $value, $key2 = '=') {
			
			switch ($key2) {
				
				default:
					$output = $key.' '.$key2.' '.$this->value ($this->safesql ($value));
					break;
				
				case IN:
					$output = $key.' '.$key2.'('.$this->implode_values ($value).')';
					break;
				
				case IN_LOWER:
					
					$values = [];
					foreach ($value as $val)
					$values[] = lisas_strtolower ($val);
					
					$output = 'LOWER('.$key.') IN('.$this->implode_values ($values).')';
					
					break;
				
				case IN_NOT_NULL:
					$output = $key.' IN('.$this->implode_values ($value, 1).')';
					break;
				
				case IN_RAW:
					$output = $key.' IN('.$value.')';
					break;
				
				case LIKE:
					$output = $key.' '.$key2.' '.$this->value ($value);
					break;
				
				case LIKE_LOWER:
					$output = $key.' LIKE LOWER('.$this->value ($this->safesql ($value)).')';
					break;
				
				case LOWER:
					$output = 'LOWER('.$key.') = '.$this->value ($this->safesql ($value));
					break;
				
			}
			
			return $output;
			
		}
		
		private function _query_where ($array) { // Экспериментальная
			
			$output = [];
			
			foreach ($array as $key1 => $value1) {
				
				$output2 = '';
				
				foreach ($value1 as $key2 => $value2)
					$output[] = '('.$this->condition ($value2, [], $key2).')';
				
				$output2 .= implode (' '.strtoupper ($key1).' ', $output);
				
			}
			
			return $output2;
			
		}
		
		///
		
		private function _multi_query ($query, $data, $show_errors, $debug) {
			
			$data['base_name'] = $this->config['base_name'];
			$data['prefix'] = $this->prefix ($this->config['prefix'], 2);
			$data['collate'] = $this->config['collate'];
			$data['date'] = LISAS_DATE;
			
			foreach ($data as $find => $replace)
				$query = str_replace ('{'.$find.'}', $replace, $query);
			
			$this->query2 (trim ($query), [], $debug, $show_errors);
			
		}
		
		private function _multi_query_array ($query) {
			return explode ($this->sep[0], $query);
		}
		
		function multi_query ($query, $file = 0, $data = [], $show_errors = 1, $debug = 0) {
			
			if ($file) $query = file_get_content ($query);
			$queries = $this->_multi_query_array ($query);
			
			foreach ($queries as $query)
				$this->_multi_query ($query, $data, $show_errors, $debug);
			
		}
		
		function multi_query_id ($file, $id, $data = [], $show_errors = 1, $debug = 0) {
			
			$query = file_get_content ($file);
			$query = $this->_multi_query_array ($query);
			
			$this->_multi_query ($query[$id], $data, $show_errors, $debug);
			
		}
		
		function sep2_implode ($queries) {
			
			array_unshift ($queries, '');
			return implode ($this->sep[0], $queries);
			
		}
		
		abstract protected function getAssoc ($query_id, $id);
		abstract protected function getArray ($query_id);
		abstract protected function getRow ($query_id);
		abstract protected function rowsNum ($query_id);
		
		function get_row ($query_id = false, $id = -1) {
			
			if (!$query_id) $query_id = $this->query_id;
			return $this->getAssoc ($query_id, $id);
			
		}
		
		function get_array ($query_id = false) {
			
			if (!$query_id) $query_id = $this->query_id;
			return $this->getArray ($query_id);
			
		}
		
		function value_quote () {
			return '\'';
		}
		
		final function addquotes ($str) {
			return $this->table_quote ().$str.$this->table_quote ();
		}
		
		function stripquotes ($str) {
			return trim ($str, $this->table_quote ());
		}
		
		function id_query ($query, $col = 'id') {
			
			$this->query ($query);
			
			$output = [];
			while ($row = $this->get_row ())
			$output[] = $row[$col];
			
			return $output;
			
		}
		
		function id_assoc_query ($query, $col = 'id', $debug = 0) {
			
			$this->query ($query, $debug);
			
			$output = [];
			while ($row = $this->get_row ())
			$output[$row[$col]][] = $row;
			
			return $output;
			
		}
		
		function array_query ($query_id, $col = 'id', $deny_keys = [], $super = 0, $debug = 0) {
			
			$output = [];
			
			if (is_array ($query_id))
				$query_id = $this->query ($query_id, $debug);
			
			while ($row = $this->get_row ($query_id)) {
				
				foreach ($row as $key => $value) {
					
					if (!in_array ($key, $deny_keys)) {
						
						if (!$row[$col]) $row[$col] = $col;
						
						if ($super == 1)
							$output[] = $value;
						elseif ($super == 2)
							$output[$key] = $value;
						else
							$output[$row[$col]][$key] = $value;
						
					}
					
				}
				
			}
			
			return $output;
			
		}
		
		final function num_rows ($query_id = null) {
			
			if (!$query_id) $query_id = $this->query_id;
			return $this->rowsNum ($query_id);
			
		}
		
		protected function setReportsType () {}
		
		abstract function insert_id ();
		protected function getFields ($query_id) {}
		
		function get_result_fields ($query_id = null) {
			
			if (!$query_id) $query_id = $this->query_id;
			
			$fields = [];
			while ($field = $this->getFields ($query_id))
			$fields[] = $field;
			
			return $fields;
			
		}
		
		function safesql ($source) {
			
			$search = ["\\", "\x00", "\n", "\r", "\x1a", '\'', '"'];
			$replace = ["\\\\", "\\0", "\\n", "\\r", "\\Z", "\'", '\"'];
			
			return str_replace ($search, $replace, stripslashes ($source));
			
		}
		
		function safesql_in ($source) {
			return addslashes (addslashes (trim ($source)));
		}
		
		function safesql_out ($source) {
			return stripslashes ($source);
		}
		
		protected function doFree ($query_id) {}
		
		function free ($query_id = false) {
			
			if (!$query_id) $query_id = $this->query_id;
			$this->doFree ($query_id);
			
		}
		
		abstract function close ();
		
		function condition ($array, $deny_keys = [], $sep1 = 'OR', $row = [], $safesql = 1) {
			
			$output = [];
			
			if ($array)
			foreach ($array as $key => $value) {
				
				if (!in_array ($key, $deny_keys) and (($row and $row[$key] != $value) or !$row)) {
					
					if (is_array ($value)) {
						
						$op = $value[0];
						$value = $value[1];
						
					}
					
					if (!$op) $op = '=';
					
					if ($row) debug ($row[$key].$value);
					
					if (!str_compare ($key, 5, 'LOWER'))
						$key = $this->addquotes ($key);
					
					if ($safesql) $value = $this->safesql ($value);
					
					$output[] = $key.' '.$op.' '.$this->value ($value);
					
				}
				
			}
			
			$output = implode (' '.trim (strtoupper ($sep1)).' ', $output);
			return $output;
			
		}
		
		function concat ($row, $type = '+', $numb = 1) { // Если к колонке $row нужно прибавить или отнять число $numb.
			
			if (!$type) $type = '+';
			
			if (is_array ($row)) {
				
				$output = [];
				
				foreach ($row as $r) {
					
					$r[0] = $this->addquotes ($r[0]);
					
					if (!$r[1]) $r[1] = $type;
					if (!isset ($r[2])) $r[2] = $numb;
					
					$output[] = $r[0].' '.$r[1].' '.$r[2];
					
				}
				
				$output = sep_implode ($output);
				
			} else {
				
				$row = $this->addquotes ($row);
				
				$output = $row.' '.$type.' '.$numb;
				
			}
			
			return $output;
			
		}
		
		function implode_values ($array, $no_null = 0) {
			
			$output = '';
			$added = [];
			
			foreach ($array as $i => $value) {
				
				if (!in_array ($value, $added)) {
					
					if ($i > 0) $output .= ',';
					
					//if (!is_numeric ($value))
						$value = $this->value ($this->safesql ($value));
					
					if (!$no_null or $value)
						$output .= $value;
					
					$added[] = $value;
					
				}
				
			}
			
			return $output;
			
		}
		
		function timestamp ($date, $linux = 0) {
			
			if (not_empty ($date)) {
				
				if ($linux) $date = strtotime ($date);
				$date = date ('YmdHis', $date);
				
			} else $date = '0000-00-00 00:00:00';
			
			return $date;
			
		}
		
		private function prepare_partition_table ($key) {
			return $this->config['prefix'].'_'.$key;
		}
		
		function check_partitioning () {
			
			$check = $this->super_query2 ('SELECT '.addquotes ('variable_value').'
FROM '.addquotes ('information_schema').'.'.addquotes ('global_variables').'
WHERE LOWER('.addquotes ('variable_name').') = '.$this->value ('have_partitioning'));
			
			if (lisas_strtolower ($check['variable_value']) == 'yes') return true; else return false;
			
		}
		
		private function partition_tables ($debug = 0) {
			
			$this->query2 ('SELECT '.addquotes ('partition_name').'
FROM '.addquotes ('information_schema').'.'.addquotes ('partitions').'
WHERE '.addquotes ('partition_name').' IS NOT NULL AND '.addquotes ('table_schema').' = '.$this->value ($this->config['base_name']));
			
			$part_tables = [];
			while ($row = $this->get_row ())
				$part_tables[] = $row['partition_name'];
			
			if ($debug) print_r ($part_tables);
			
			return $part_tables;
			
		}
		
		function add_partition ($tables) {
			
			$result = [];
			
			if ($this->check_partitioning ()) {
				
				foreach ($tables as $key => $data) {
					
					switch ($data['type']) {
						
						default:
						case 'date':
							
							break;
						
					}
					
				}
				
			}
			
		}
		
		function do_partitioning ($table, $options, $debug = 0) {
			
			$result = [];
			
			$query = 'ALTER TABLE '.$this->table ($table).'
';
			
			switch ($options['type']) {
				
				default:
				case 'date':
					
					$start_year = $options['start_year'];
					if (!$start_year) $start_year = 2012;
					
					$this_year = date ('Y');
					
					$start_month = $options['start_month'];
					if (!$start_month) $start_month = 1;
					
					$this_month = 12;
					$month = date ('m');
					
					$query .= 'PARTITION BY RANGE (TO_DAYS('.$this->addquotes ($options['part_col']).')) (';
					
					for ($y_i = $start_year; $y_i <= $this_year; ++$y_i) { // Года
						
						if ($y_i == $this_year) $this_month = $month;
						
						for ($m_i = $start_month; $m_i <= $this_month; ++$m_i) { // Месяцы
							
							$name = 'p'.$y_i.'_'.add_zero ($m_i - 1);
							$date = $y_i.'-'.add_zero ($m_i).'-01';
							
							$query .= '
PARTITION '.$this->addquotes ($name).' VALUES LESS THAN (TO_DAYS('.$this->value ($date).')),';
							
						}
						
					}
					
					//$query = trim ($query, ',');
					
					$query .= '
PARTITION '.$this->addquotes ('pUnsort').' VALUES LESS THAN (MAXVALUE)';
					
					$query .= '
)';
					
					break;
				
				case 'id':
					
					$total = $this->super_query ([self::SELECT, COUNT, $table]);
					
					$query .= 'PARTITION BY RANGE ('.$this->addquotes ($options['part_col']).') (
';
					
					$options['period'] = intval_correct ($options['period'], 10000);
					$part_num = ceil (($total['count'] / $options['period']));
					
					$i2 = 0;
					
					for ($i = 1; $i <= $part_num; ++$i) {
						
						$i2 += $options['period'];
						$name = 'p'.add_zero ($i);
						
						$query .= 'PARTITION '.$this->addquotes ($name).' VALUES LESS THAN ('.$i2.'),
';
						
					}
					
					//$query = trim ($query, ',');
					
					$query .= 'PARTITION '.$this->addquotes ('pUnsort').' VALUES LESS THAN (MAXVALUE)';
					
					$query .= '
)';
					
					break;
				
			}
			
			if ($query) $this->query2 ($query, $debug);
			
			$result[] = 1;
			
			return $result;
			
		}
		
		function delete_partition ($table, $part = '') {
			$this->query2 ('ALTER TABLE '.$this->table ($table).' DROP PARTITION '.
				$this->addquotes ($this->prepare_partition_table ($table)), 1);
		}
		
		function sharding_table_id ($rows_num, $tables_num, $table_name, $file, $id, $options = []) {
			
			if ($this->config['sharding']) {
				
				if ($rows_num >= $this->config['sharding_rows_num']) {
					
					$tables_num = $tables_num + 1;
					if ($tables_num <= 1) $tables_num = 2;
					
					if (!$options['auto_increment'])
						$options['auto_increment'] = $rows_num;
					
					$this->multi_query_id ($file, $id, ['table_id' => $tables_num, 'auto_increment' => ($options['auto_increment'] + 1)]);
					
					$this->query ([self::UPDATE, [$table_name => $tables_num], 'sharding_tables_num']);
					
					$this->cache->clear ('sharding_tables_num');
					
				}
				
			} else $tables_num = 1;
			
			return $tables_num;
			
		}
		
		function sharding_table_num ($array, $table) {
			
			if ($this->config['sharding'] and is_isset ($table, $array))
				return $array[$table];
			else
				return 1;
			
		}
		
		function sharding_tables_num ($tables_num) {
			
			$output = [];
			$tables_num = intval_correct ($tables_num, 1);
			
			for ($i = 1; $i <= $tables_num; ++$i) {
				
				if ($i > 1) $i2 = $i; else $i2 = '';
				$output[] = $i2;
				
			}
			
			return $output;
			
		}
		
		private function prep_id_row ($row, $int, $encode) {
			
			if (!$int) {
				
				if ($encode) $row = url_encode ($row);
				$row = $this->value ($this->safesql ($row));
				
			}
			
			return $row;
			
		}
		
		function implode_ids ($query, $col = 'id', $int = 1, $encode = 0) {
			
			$ids = '';
			
			while ($row = $this->get_row ($query))
				$ids .= $this->prep_id_row ($row[$col], $int, $encode).',';
			
			return trim ($ids, ',');
			
		}
		
		function implode_ids_array ($array, $int = 1, $encode = 0, $blank = 1) {
			
			$output = '';
			foreach ($array as $id)
				if (($blank and $id) or !$blank)
					$output .= $this->prep_id_row ($id, $int, $encode).',';
			
			return trim ($output, ',');
			
		}
		
		/*
		function createStruct($shop){
			
			if(!isset($shop['db'])) return false;
			$tablesArray = array();
			
			$link = mysql_connect($shop['db']['host'], $shop['db']['user'], $shop['db']['password']);
			mysql_select_db($shop['db']['name'], $link);
			
			$resultTables = mysql_query("show tables", $link);
			if(mysql_affected_rows($link) > 0){
					while($table = mysql_fetch_array($resultTables)){
									$tablesArray[$table[0]] = array();
					}				
			}
			
			if(empty($tablesArray)) return false;
			
			foreach($tablesArray as $tableName => $tmpval){
					$resultFields = mysql_query('DESCRIBE '.$tableName, $link); 
					if(mysql_affected_rows($link) > 0){
							while($rowField = mysql_fetch_assoc($resultFields)){
									$tablesArray[$tableName][$rowField['Field']] = array(
											'type'	=> $rowField['Type'],
											'null'	=> $rowField['Null'],
											'key'	 => $rowField['Key'],
											'default' => $rowField['Default'],
											'extra' => $rowField['Extra']
									
									);
							}
					}			 
			}
			
			mysql_close($link);
			return $tablesArray;
			
		}
		
		$ideal = createStruct($shops[0]);
		$equal = createStruct($shops[$_SESSION['sc']]);
		
		if($equal === false || empty($equal)){
				$html = '<table><tr><td><h3>Проблемы с подключение к базе сайта <b>'.$s->shop_name.'</b>, возможно база не была создана, либо не верные коды доступа, либо прекратила свое существование.</h3></td></tr></table>';
		}else{
		
		$html = '<table>';
		$html .= '<tr><td><h3>'.$s->shop_name.'</h3></td></tr>';
		foreach($ideal as $table => $fields){
				if(!isset($equal[$table])){
						$html .= '<tr><td>Отсутствует таблица <b>'.$table.'</b></td></tr>';
				}else{
						$html .= '<tr><td>';
								foreach($ideal[$table] as $key => $fieldRow){
										if(!isset($equal[$table][$key])){
												$html .= 'В таблице <b>'.$table.'</b> не хватает поля <b>'.$key.'</b><br>';
										}else{
												foreach($ideal[$table][$key] as $okey => $oval){
														if($equal[$table][$key][$okey] !== $oval){
																
																$text = $equal[$table][$key][$okey];
																if($equal[$table][$key][$okey] === NULL){
																		$text = 'NULL';
																}elseif($equal[$table][$key][$okey] === ''){
																		$text = ' (ПУСТОТА) ';
																}elseif($equal[$table][$key][$okey] === TRUE){
																		$text = 'TRUE';
																}elseif($equal[$table][$key][$okey] === FALSE){
																		$text = 'FALSE';
																}
																
																$html .= 'Опции поля <b>'.$key.'</b> в таблице <b>'.$table.'</b> не идентичны. Идеал: '.$oval.' Исследуемый: '.$text.'<br>';
														}
												}
										}
								}
						
						$html .= '</td></tr>';
				}
				
				if(isset($equal[$table])){
						unset($equal[$table]);
						unset($ideal[$table]);
				}
		}
		
		*/
		
		// Запросы
		
		function query ($data, $debug = 0) {
			
			$query_id = null;
			$data = $this->options ($data);
			
			if ($query = $this->query_output ($data))
				$query_id = $this->query2 ($query, $data[5], $debug, $data[0], $data[1]);
			
			return $query_id;
			
		}
		
		private function cache_name ($table, $options = []) {
			
			$file = [];
			
			if ($this->config['base_name'])
				$file[] = $this->config['base_name'];
			
			if ($this->config['prefix'])
				$file[] = $this->config['prefix'];
			
			if (!is_isset ('table', $options) and $table)
				$options['table'] = $table;
			
			$options['table'] = explode ('_', $options['table']);
			
			foreach ($options['table'] as $col)
				$file[] = $col;
			
			if (is_isset ('file', $options))
				$file[] = $options['file'];
			
			return $file;
			
		}
		
		function query2 ($query, $options = [], $debug = 0, $type = '', $what = []) {
			
			if (!is_array ($options)) {
				
				$debug = $options;
				$options = [];
				
			}
			
			if ($this->debug > 0) $debug = $this->debug;
			
			if ($debug != 2 and $debug != 3) {
				
				if (is_isset ('cache', $options)) {
					
					if ($this->cache) {
						
						if (!is_array ($options['cache']))
							$options['cache'] = [];
						
						$options['cache']['file'] = $this->cache_name ($options['table'], $options['cache']);
						
						$output = $this->cache->arrayOutput ($options['cache']['file']);
						
						if (!$output) {
							
							if ($debug == 2) debug ($query);
							
							if ($query) {
								
								$this->query = $query;
								$this->_query ($type, $query, $debug, $options);
								
								if (!is_isset ('col', $options['cache']))
									$options['cache']['col'] = 'id';
								
								$output = $this->array_query ($this->query_id, $options['cache']['col'], [], is_isset ('super', $options));
								
								$this->cache->arrayInput ($output, $options['cache']['file']);
								
							}
							
							if (is_isset ('file_del', $options['cache'])) {
								
								$files = make_array ($options['cache']['file_del']);
								foreach ($files as $file) $this->cache->clear ($file);
								
							}
							
						}
						
					} else $output = [];
					
				} else $output = $this->_query ($type, $query, $debug, $options);
				
			}
			
			return $output;
			
		}
		
		function super_query2 ($query, $debug = 0, $options = [], $type = '') {
			
			$query .= '
LIMIT 1';
			
			if ($debug != 2) {
				
				$options['super'] = 2;
				$query = $this->query2 ($query, $options, $debug, $type);
				
				if (!is_array ($query)) {
					
					if ($row = $this->get_row ($query))
						$this->free ($query);
					
				} else $row = $query;
				
			} else $row = $query;
			
			return $row;
			
		}
		
		private function options ($data) {
			
			if (isset ($data)) {
				
				$data[5]['type'] = $data[0];
				$data[5]['table'] = $data[2];
				
			}
			
			return $data;
			
		}
		
		function super_query ($data, $debug = 0) {
			
			$data = $this->options ($data);
			$query = $this->query_output ($data);
			
			if ($this->options['session_read']) {
				
				if ($_SESSION[$this->options['session_read']] != $data[1]) { // TODO $data[1] - $what?
					
					$row = $this->super_query2 ($query, $debug, $data[5], $data[0]);
					
					$_SESSION[$this->options['session_write']] = array2json ($row);
					$_SESSION[$this->options['session_read']] = $data[1];
					
				} else $row = json_decode ($_SESSION[$this->options['session_write']], true);
				
			} else $row = $this->super_query2 ($query, $debug, $data[5], $data[0]);
			
			return $row;
			
		}
		
		function union_query ($query, $debug = 0) {
			
			$query = implode (')
UNION
(', $query).'
';
			
			return $this->query2 ('('.$query.')', $debug);
			
		}
		
		function union_super_query ($query, $debug) {
			
			$query = implode (')
UNION
(', $query).'
';
			
			return $this->super_query2 ('('.$query.')', $debug);
			
		}
		
		function count_query ($data, $col = 'count', $debug = 0) {
			
			if ((int) $col) $debug = $col;
			$row = $this->super_query ($data, $debug);
			
			return $row[$col];
			
		}
		
		function count_query2 ($query, $col = 'count', $debug = 0) {
			
			if ((int) $col) $debug = $col;
			$row = $this->super_query2 ($query, $debug);
			return $row[$col];
			
		}
		
		function increment_query ($query, $col, $debug = 0) {
			
			$row = $this->super_query2 ($query, $debug);
			$id = (int) $row[$col] + 1;
			
			return $id;
			
		}
		
		function loadCache (string $table, $col = 'id') {
			$this->cache_data[$table] = $this->query ([self::SELECT, '', $table, [], [], ['cache' => ['col' => $col]]]);
		}
		
		function clearCache ($tables = []) {
			
			if (!is_array ($tables)) $tables = [$tables];
			
			foreach ($tables as $table)
				$this->cache->clear ($this->cache_name ($table));
			
		}
		
		function table_quote () {
			return '"';
		}
		
		protected function prep_types ($type, $name, $value) {
			return '';
		}
		
		public $vocabulary = [
			
			'CREATE',
			'TABLE',
			'DATABASE',
			'USE',
			'NOT',
			'NULL',
			'DEFAULT',
			'AUTO_INCREMENT',
			
		];
		
		function convert (string $content, \DB\Adapter $from): string {
			return $content;
		}
		
		function array2dump ($fp, $row, $table, $options = [], $num = 0, $type = 'devided') {
			
			if ($num == 0) {
				
				if ($type == 'devided') {
					
					//fwrite ($fp, $this->query_output (['drop', 'table', $table, [], ['no_db_name' => 1]]).NL);
					fwrite ($fp, $this->query_output (['create', 'table', $table, $options, ['no_db_name' => 1, 'compact' => 1]]).NL);
					
				} else {
					
					//fwrite ($fp, $this->query_output (['drop', 'table', $table, [], ['no_db_name' => 1]]).NL.NL);
					fwrite ($fp, $this->query_output (['create', 'table', $table, $options, ['no_db_name' => 1]]).NL.NL);
					fwrite ($fp, $this->chunk_query_start (self::INSERT, $row, $table, $options, ['no_db_name' => 1]));
					
				}
				
				$num = $id;
				
			}
			
			if ($type == 'devided')
				fwrite ($fp, $this->query_output ([self::INSERT, $row, $table, $options, ['no_db_name' => 1, 'compact' => 1, 'no_keys' => 1]]).NL);
			else
				fwrite ($fp, $this->chunk_query_part ([self::INSERT, $row, $options]));
			
			return $num;
			
		}
		
		final function value ($value) {
			return $this->value_quote ().$value.$this->value_quote ();
		}
		
		function unescape ($value) {
			return $value;
		}
		
		function set_autoincrement ($table) {}
		
	}