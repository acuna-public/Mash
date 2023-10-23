<?php
	
	namespace DB;
	
	class PostgreSQL extends Adapter {
		
		function getName () {
			return 'postgres';
		}
		
		function getTitle () {
			return 'PostgreSQL';
		}
		
		function getVersion () {
			return '1.0';
		}
		
		function getDefConfig () {
			
			return [
				
				'port' => 5432,
				'user' => 'postgres',
				'password' => '',
				'collate' => 'utf8',
				
			];
			
		}
		
		function getConvertTypes () {
			
			return [
				
				'tinyint' => 'smallint',
				'smallint' => 'smallint',
				'mediumint' => 'integer',
				'int' => 'integer',
				'bigint' => 'bigint',
				
				'varbinary' => ['bit', 1],
				
				'bit' => ['bit varying', 1],
				
				'mediumtext' => 'text',
				'longtext' => 'text',
				
				'datetime' => 'timestamp',
				'year' => 'smallint',
				
			];
			
		}
		
		function version (): string {
			return implode ('/', pg_version ($this->db_id));
		}
		
		protected function doConnect () {
			
			$data = [
				
				'host' => $this->config['host'],
				'port' => $this->config['port'],
				'user' => $this->config['user'],
				'password' => $this->config['password'],
				'dbname' => $this->config['base_name'],
				'options' => '--client_encoding='.$this->config['collate'],
				
			];
			
			return pg_connect (http_build_fquery ($data, 0, [' ']));
			
		}
		
		protected function init () {
			pg_set_error_verbosity ($this->db_id, PGSQL_ERRORS_DEFAULT);
		}
		
		protected function increment ($data) {
			return strtoupper ('serial '.(($data[2] == 'null') ? 'null' : 'not null')).',';
		}
		
		protected function enum ($data, $items) {
			
			$new_col = array_search ($items, $types_val);
			
			if (!$new_col) {
				
				$query .= $this->prep_types ($data[0], strtoupper ($col), $items);
				$new_col = $col;
				
				$types_val[$col] = $items;
				
			} else $col = $new_col;
			
			return strtoupper ($col.' '.(($data[2] == 'null') ? 'null' : 'not null').' default \''.$data[3].'\'').',';
			
		}
		
		protected function uniqueIndexKeyword ($table, $key) {
			return 'CONSTRAINT '.$this->addquotes ($this->prefix ().$table.'_'.$key).' UNIQUE';
		}
		
		protected function prep_types ($type, $name, $value) {
			
			$query = '';
			
			switch ($type) {
				
				case 'enum':
					$query .= 'DROP TYPE IF EXISTS '.$name.' CASCADE;
CREATE TYPE '.$name.' AS enum('.$value.');';
					break;
				
				case 'tinyint':
					$query .= 'DROP DOMAIN '.$type.' CASCADE;
CREATE DOMAIN '.$type.' AS smallint CONSTRAINT '.$this->addquotes ('con_byte').' CHECK (VALUE >= 0 AND VALUE <= 255);';
					break;
					
				case 'tinytext':
					$query .= 'DROP DOMAIN '.$type.' CASCADE;
CREATE DOMAIN '.$type.' AS VARCHAR CONSTRAINT '.$this->addquotes ('con_byte').' CHECK (VALUE >= 0 AND VALUE <= 255);';
					break;
				
			}
			
			return $query;
			
		}
		
		function convert (string $content, \DB\Adapter $from): string {
			
			$output = '';
			
			$stream = new \SQLTokenizer ($content, $from, $this);
			$data = $stream->process ();
			//return array2json ($data);
			
			$types = $this->getConvertTypes ();
			$types_val = [];
			$query = '';
			
			foreach ($data as $item) {
				
				if (is_isset ('comment', $item))
					$output .= '-- '.$item['comment'].NL;
				
				if (is_isset ('query', $item)) {
					
					if (is_isset ('type', $item)) {
						
						$output .= '
DROP '.$item['type'].' IF EXISTS '.$this->addquotes ($item['table']).';
CREATE '.$item['type'].' '.$this->addquotes ($item['table']);
						
					}
					
					if (is_isset ('settings', $item))
						$output .= ' '.$item['settings'];
					
					$indexes = '';
					
					if (is_isset ('body', $item)) {
						
						$output .= ' ('.NL;
						$deny_types = [];
						
						foreach ($item['body'] as $i => $body) {
							
							if ($i > 0) $output .= ','.NL;
							
							if (is_isset ('column', $body))
								$output .= '	'.$this->addquotes ($body['column']);
							
							$serial = (isset ($body['default']) and $body['default'] === $from::AUTO_INCREMENT);
							
							if (is_isset ('type', $body) and $type = $body['type']) {
								
								if ($type['name'] == 'enum') {
									
									$items = '';
									$name = $type['name'];
									
									foreach ($type['length'] as $i2 => $item3) {
										
										if ($i2 > 0) $items .= ',';
										$items .= $this->value ($item3);
										
										$name .= '_'.$item3;
										
									}
									
									$output .= ' '.$name;
									
									if (!isset ($types_val[$type['name']]) or !in_array ($name, $types_val[$type['name']])) {
										
										$query .= 'DROP TYPE IF EXISTS '.$this->addquotes ($name).' CASCADE;
CREATE TYPE '.$this->addquotes ($name).' AS enum('.$items.');

';
										
										$types_val[$type['name']][] = $name;
										
									}
									
								} elseif ($type['name'] == 'tinyint') {
									
									$name = $type['name'].$type['length'];
									
									$output .= ' '.$name;
									
									if (!isset ($types_val[$type['name']]) or !in_array ($name, $types_val[$type['name']])) {
										
										$query .= 'DROP DOMAIN IF EXISTS '.$this->addquotes ($name).' CASCADE;
CREATE DOMAIN '.$this->addquotes ($name).' AS smallint CHECK (VALUE >= -'.str_repeat (9, $type['length']).' AND VALUE <= '.str_repeat (9, $type['length']).');

';
										
										$types_val[$type['name']][] = $name;
										
									}
									
								} else {
									
									if ($serial)
										$name = 'serial';
									else
										$name = $type['name'];
									
									if (!$serial and isset ($types[$type['name']]) and $name = $types[$type['name']] and is_array ($name)) {
										
										$output .= ' '.$name[0];
										
										if ($name[1] and isset ($type['length']))
											$output .= '('.$type['length'].')';
										
									} elseif (!$serial and !is_isset ($type['name'], $types)) {
										
										$output .= ' '.$name;
										
										if (isset ($type['length']))
											$output .= '('.$type['length'].')';
										
									} else $output .= ' '.$name;
									
								}
								
							}
							
							if (isset ($body['is_null']) and !$serial)
								$output .= ' '.($body['is_null'] ? $this::NULL : $this::NOT_NULL);
							
							if (isset ($body['default']) and !$serial) {
								
								if ($body['default'] === $from::NULL)
									$output .= ' DEFAULT '.$body['default'];
								else
									$output .= ' DEFAULT '.$this->value ($body['default']);
								
							}
							
							if (is_isset ('keys', $body)) {
								
								$relations = '';
								$i4 = 0;
								
								foreach ($body['keys'] as $i2 => $key) {
									
									if (is_isset ('type', $key) and $key['type'] != $from::FULLTEXT) {
										
										if ($i2 > 0) $output .= ','.NL;
										$output .= '	';
										
										$output .= $key['type'];
										
										if ($key['type'] != 'UNIQUE')
											$output .= ' '.$from::KEY;
										
										$output .= ' (';
										
										foreach ($key['value'] as $i3 => $value) {
											
											if ($i3 > 0) $output .= ', ';
											$output .= $this->addquotes ($value);
											
										}
										
										$output .= ')';
										
									} else { // Индекс
										
										foreach ($key['value'] as $value) {
											
											if (!in_array ($value, $deny_types)) {
												
												if ($i4 > 0) $relations .= ', ';
												
												$deny_types[] = $value;
												$relations .= $this->addquotes ($value);
												$i4++;
												
											}
											
										}
										
									}
									
								}
								
								if ($relations)
									$indexes .= 'CREATE INDEX CONCURRENTLY ON '.$this->addquotes ($item['table']).' ('.$relations.');
';
								
							}
							
						}
						
						$output .= NL.')';
						
					}
					
					if ($item['query'] != 'USE') $output .= ';'.NL.NL;
					if ($indexes) $output .= NL.$indexes;
					
				}
				
			}
			
			if ($query) $output = $query.$output;
			
			return $output;
			
		}
		
		function convert2 (string $content, \DB\Adapter $from): string {
			
			$output = '';
			$types_val = [];
			
			preg_match_all ('~(CREATE\s+TABLE)\s+(IF NOT EXISTS)*\s*(.+?)\(([a-z0-9]+?)\)\s*(.+?);~si', $content, $global_match);
			
			print_r ($global_match);
			
			foreach ($from->parser ($content) as $global_match) {
				
				$output .= $global_match['command'];
				
				if (is_isset ('table', $global_match))
					$output .= $this->addquotes ($global_match['table']);
				
				$query = '';
				
				if (is_isset ('body', $global_match)) {
					
					$output .= ' ('.NL;
					
					foreach ($global_match['body'] as $i => $str) {
						
						$data = [];
						$preg_data = [];
						$preg_data_c = [];
						
						$preg_data['([a-z0-9"\'_]+)\s+BIGINT\(([0-9]+)\)\s+([a-z ]+)\s+AUTO_INCREMENT'] = '\\1 BIGSERIAL';
						$preg_data['([a-z0-9"\'_]+)\s+([a-z]+)\(([0-9]+)\)\s+([a-z ]+)\s+AUTO_INCREMENT'] = '\\1 SERIAL';
						
						if ($i > 0) $output .= ','.NL;
						
						preg_match ('~([a-z0-9"`\'_]+)\s+([a-z]+)\((.+?)\)~si', $str, $match);
						
						if ($match) {
							
							if (!in_array ($match[2], $types_val)) {
								
								switch ($match[2]) {
									
									default: {
										
										if (isset ($this->getConvertTypes ()[$match[2]]) and $value = $this->getConvertTypes ()[$match[2]]) {
											
											if (is_array ($value) and $value[1])
												$value = $value[0].'('.$match[3].')';
											
											$preg_data['\s+'.$match[2].'\('.$match[3].'\)'] = ' '.$value;
											
										}
										
										break;
										
									}
									
									case 'enum': {
										
										$match[1] = strip_quotes ($match[1]);
										$query .= $this->prep_types ('enum', $match[1], $match[3]);
										
										$preg_data['\s+'.$match[2].'\('.$match[3].'\)'] = ' '.$match[1];
										
										break;
										
									}
									
									case 'tinyint': {
										
										$query .= $this->prep_types ('tinyint', $match[1], $match[3]);
										$preg_data['\s+'.$match[2].'\('.$match[3].'\)'] = ' '.$match[2];
										
										break;
										
									}
									
									case 'tinytext': {
										
										$query .= $this->prep_types ('tinytext', $match[1], $match[3]);
										$preg_data['\s+'.$match[2].'\('.$match[3].'\)'] = ' '.$match[2];
										
										break;
										
									}
									
								}
								
								$types_val[] = $match[2];
								
							}
							
						}
						
						$types = [
							
							'datetime' => 'timestamp',
							
						];
						
						foreach ($types as $key => $value)
							$preg_data['([a-z0-9"\'_]+)\s+'.$key.'\s+'] = '\\1 '.$value.' ';
						
						$preg_data['\sDEFAULT\s*["\'](.*?)["\']'] = ' DEFAULT E\'\\1\'';
						
						$indexes = ['PRIMARY KEY'];
						
						foreach ($indexes as $index) {
							
							$preg_data[$index.'\s+([a-z0-9"\'_]+)\s+\((.*?)\)'] = $index.' (\\2)';
							//$preg_data[$index.'\s+([a-z0-9"\'_]+)'] = $index.' (\\1)';
							
						}
						
						$preg_data['UNIQUE\s+INDEX\s+([a-z0-9"\'_]+)\s+\((.*?)\)'] = 'CONSTRAINT \\1 UNIQUE (\\2)';
						$preg_data['UNIQUE\s+INDEX\s+([a-z0-9"\'_]+)'] = 'CONSTRAINT \\1 UNIQUE (\\1)';
						
						$preg_data_c['UNIQUE\s+INDEX\s+([a-z0-9"_]+)\s*\((.*?)\)'] = function ($match) use ($global_match) {
								return 'CONSTRAINT "'.strip_quotes ($global_match[2].'_'.$match[1][0]).'" UNIQUE ('.$match[2].')';
						};
						
						/*$preg_data['UNIQUE\s+INDEX\s+([a-z0-9"\'_]+)\s+\((.*?)\)'] = 'UNIQUE (\\2)';
						$preg_data['UNIQUE\s+INDEX\s+([a-z0-9"\'_]+)'] = 'UNIQUE (\\1)';
						$preg_data['UNIQUE\s+INDEX\s+([a-z0-9"\'_]+)\s*\((.*?)\)'] = 'CONSTRAINT \\1 UNIQUE (\\2)';*/
						
						//$preg_data['\s+/\*(.*?)\*/'] = '';
						
						$preg_data['\)\s+ENGINE=(.+?);'] = ');';
						
						foreach ($data as $find => $replace)
							$str = str_replace ($find, $replace, $str);
						
						foreach ($preg_data_c as $find => $replace)
							$str = preg_replace_callback ('~'.$find.'~si', $replace, $str);
						
						foreach ($preg_data as $find => $replace)
							$str = preg_replace ('~'.$find.'~si', $replace, $str);
						
						$output .= '	'.$str;
						
					}
					
					$output .= ')';
					
				}
				
				$output .= ';'.NL.NL;
				$output .= $query;
				
			}
			
			return $output;
			
		}
		
		protected function indexKeys ($key, $index) {
			return ' ('.trim ($index, ', ').')';
		}
		
		function table_quote () {
			return '"';
		}
		
		public function limit ($offset, $num = 0) {
			
			if ($num)
				$limit = $num.' OFFSET '.$offset;
			else
				$limit = $offset;
			
			return $limit;
			
		}
		
		protected function errorCode () {
			return 0;
		}
		
		protected function errorText () {
			return pg_last_error ($this->db_id);
		}
		
		private $error;
		
		function doQuery ($query = [], $options = []) {
			
			if (is_isset ('type', $options)) {
				
				if ($options['type'] == self::INSERT and $options['return_col'])
					$query .= ' RETURNING '.$this->addquotes ($options['return_col']);
				elseif ($options['type'] == self::TRUNCATE)
					$query .= ' RESTART IDENTITY';
				
			}
			
			return pg_query ($this->db_id, $query);
			
		}
		
		protected function getAssoc ($query_id, $id) {
			return pg_fetch_assoc ($query_id);
		}
		
		protected function getRow ($query_id) {
			return pg_fetch_row ($query_id);
		}
		
		protected function getArray ($query_id) {
			return pg_fetch_array ($query_id);
		}
		
		function insert_id () {
			return $this->getRow ($this->query_id)[0];
		}
		
		function safesql ($source) {
			return pg_escape_string ($this->db_id, $source);
		}
		
		protected function doFree ($query_id) {
			pg_free_result ($query_id);
		}
		
		function close () {
			pg_close ($this->db_id);
		}
		
		function set_autoincrement ($table) {
			$this->query2 ('SELECT SETVAL('.$this->value ($table.'_id_seq').', (SELECT MAX(id) FROM '.$this->addquotes ($table).'))');
		}
		
		protected function rowsNum ($query_id) {
			return pg_num_rows ($query_id);
		}
		
	}