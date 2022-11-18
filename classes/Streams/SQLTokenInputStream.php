<?php
	
	class SQLTokenizer extends Tokenizer {
		
		private $from, $to;
		protected $brackets_num = [], $block = 0;
		private $output = [];
		
		protected const KEY = 'KEY', PRIMARY = 'PRIMARY', UNIQUE = 'UNIQUE', FULLTEXT = 'FULLTEXT', AUTO_INCREMENT = 'AUTO_INCREMENT', NULL = 'NULL', NOT_NULL = 'NOT NULL', CREATE = 'CREATE', DATABASE = 'DATABASE';
		
		protected $vocabulary = [
			
			'CREATE',
			'TABLE',
			'DATABASE',
			'USE',
			'NOT',
			'NULL',
			'DEFAULT',
			'AUTO_INCREMENT',
			
		];
		
		function __construct (string $content, DB\Provider $from, DB\Provider $to) {
			
			parent::__construct ($content);
			
			$this->from = $from;
			$this->to = $to;
			
		}
		
		protected function parserInit () {
			
			$this->brackets_num = [
				
				'(' => [0, 0],
				$this->to->table_quote () => [0],
				
			];
			
		}
		
		protected final function brackets_num ($type) {
			
			if ($this->char == $type)
				$this->brackets_num[$type]++;
			
		}
		
		protected function addKey ($key, $string) {
			
			$this->trimSpace ();
			$this->buffer = '';
			
			$this->output[$this->block]['body'][$string]['keys'][$key]['value'] = [];
			
			if ($this->char == '(') { // Значение
				
				$this->next ();
				
				while ($this->char != ')') {
					
					if ($this->char == ',') {
						
						$this->next ();
						
						$this->output[$this->block]['body'][$string]['keys'][$key]['value'][] = $this->buffer;
						$this->buffer = '';
						
					} elseif ($this->char != $this->from->table_quote ())
						$this->buffer .= $this->ch;
					
					$this->next ();
					
				}
				
				$this->output[$this->block]['body'][$string]['keys'][$key]['value'][] = $this->buffer;
				
			} else {
				
				if ($this->char == $this->from->table_quote ())
					$this->next (); // Первую кавычку пропускаем
				
				while (!$this->isSpace () and $this->ch != $this->from->table_quote ()) { // Читаем до первого пробела или кавычки
					
					$this->buffer .= $this->ch;
					$this->next ();
					
				}
				
				$this->output[$this->block]['body'][$string]['keys'][$key]['name'] = $this->buffer;
				
				if ($this->char == $this->from->table_quote ())
					$this->next (); // Последнюю кавычку пропускаем
				
				$this->trimSpace ();
				$this->buffer = '';
				
				if ($this->char == '(') { // Значение
					
					$this->next ();
					
					while ($this->char != ')') {
						
						if ($this->char == ',') {
							
							$this->next ();
							
							$this->output[$this->block]['body'][$string]['keys'][$key]['value'][] = $this->buffer;
							$this->buffer = '';
							
						} elseif ($this->char != $this->from->table_quote ())
							$this->buffer .= $this->ch;
						
						$this->next ();
						
					}
					
					$this->output[$this->block]['body'][$string]['keys'][$key]['value'][] = $this->buffer;
					
				}
				
			}
			
		}
		
		protected function keysTypes () {
			return [$this->from::PRIMARY, $this->from::UNIQUE, $this->from::FULLTEXT];
		}
		
		function process (): array {
			
			$i = 0;
			
			$this->parserInit ();
			
			while ($this->read ()) {
				
				$this->next ();
				
				if ($this->isSpace () or $this->ch == $this->to->table_quote ()) {
					
					if (in_array ($this->buffer, $this->to->vocabulary)) {
						
						if ($i == 0)
							$this->output[$this->block]['query'] = $this->buffer;
						elseif ($i == 1)
							$this->output[$this->block]['type'] = $this->buffer;
						
						$this->buffer = '';
						
						$i++;
						
					} elseif ($this->buffer == '--') { // Комментарий
						
						$this->buffer = '';
						$this->trimSpace ();
						
						while ($this->char != "\n" and $this->ch != "\r") {
							
							$this->buffer .= $this->ch;
							$this->next ();
							
						}
						
						$this->output[$this->block]['comment'] = $this->buffer;
						$this->buffer = '';
						
						$this->block++;
						
					} elseif ($this->buffer) {
						
						if ($this->buffer == 'IFNOTEXISTS') {
							
							$this->output[$this->block]['if_not_exists'] = true;
							$this->buffer = '';
							
						} else {
							
							$this->output[$this->block]['table'] = $this->buffer;
							
							if (!isset ($this->output[$this->block]['if_exists']) and $this->output[$this->block]['type'] != $this->from::DATABASE and $this->output[$this->block]['query'] == $this->from::CREATE)
								$this->output[$this->block]['if_not_exists'] = false;
							
						}
						
					}
					
				} elseif ($this->char == '(') { // Читаем в скобках
					
					$this->output[$this->block]['table'] = $this->buffer;
					
					$this->next ();
					$this->brackets_num['('][0]++;
					
					$this->trimSpace ();
					
					$this->buffer = '';
					
					$i = 0;
					$string = 0;
					$key = 0;
					$is_key = false;
					
					while ($this->brackets_num['('][0] > 0) {
						
						if ($this->char == '(')
							$this->brackets_num['('][0]++;
						elseif ($this->char == ')')
							$this->brackets_num['('][0]--;
						
						if ($this->brackets_num['('][0] > 0) { // Последнюю скобку не пишем
							
							if ($this->char == ',') { // Конец строки, чистим все
								
								if ($i == 1 and $this->buffer)
									$this->output[$this->block]['body'][$string]['type']['name'] = $this->buffer;
								
								if ($this->buffer == $this->from::AUTO_INCREMENT)
									$this->output[$this->block]['body'][$string]['default'] = $this->buffer;
								
								if (!$is_key) $string++;
								$i = 0;
								$this->buffer = '';
								$is_key = false;
								
							} elseif ($this->isSpace ()) { // Встретили пробел
								
								if ($this->buffer) {
									
									if ($i == 0) { // Колонка
										
										if (in_array ($this->buffer, $this->keysTypes ($this->from))) { // Встретили ключ
											
											$this->output[$this->block]['body'][$string]['keys'][$key]['type'] = $this->buffer;
											$this->buffer = '';
											
										} elseif ($this->buffer == $this->from::KEY) { // KEY
											
											$is_key = true;
											$this->addKey ($key, $string, $this->from);
											$key++;
											
										} else $this->output[$this->block]['body'][$string]['column'] = $this->buffer;
										
									} elseif ($i == 1) {
										
										if ($this->buffer == $this->from::KEY) { // PRIMARY KEY
											
											$is_key = true;
											
											$this->addKey ($key, $string, $this->from);
											
											$key++;
											
										} elseif (!isset ($this->output[$this->block]['body'][$string]['keys'][$key]) and !isset ($this->output[$this->block]['body'][$string]['type']['length'])) // Тип данных (если не имеет длины)
											$this->output[$this->block]['body'][$string]['type']['name'] = $this->buffer;
										
									} elseif ($i == 2) {
										
										if ($this->buffer == 'NOT') { // NOT NULL
											
											$this->trimSpace ();
											$this->buffer .= ' ';
											
											while (!$this->isSpace ()) {
												
												$this->buffer .= $this->ch;
												$this->next ();
												
											}
											
										}
										
										$this->output[$this->block]['body'][$string]['is_null'] = ($this->buffer == $this->from::NULL);
										
									} elseif ($this->buffer == 'DEFAULT') {
										
										$this->buffer = '';
										
										$this->trimSpace ();
										
										while ($this->char != ',' and $this->ch != ')') { // Читаем до запятой (конец строки) или скобки (конец блока)
											
											if ($this->char != $this->from->value_quote ())
												$this->buffer .= $this->ch;
											
											$this->next ();
											
											if ($this->char == "\r") $this->trimSpace ();
											
										}
										
										if (is_numeric ($this->buffer))
											$this->buffer = (int) $this->buffer;
										
										$this->output[$this->block]['body'][$string]['default'] = $this->buffer;
										
										$i++;
										continue; // Идем дальше, чтобы не пропускалась запятая
										
									}
									
									$i++;
									
								}
								
								$this->buffer = '';
								
							} elseif ($this->char != $this->from->table_quote ()) { // Все остальное
								
								if ($i == 1) { // Тип данных
									
									if ($this->char == '(') { // Имеет длину
										
										$name = $this->buffer;
										$this->output[$this->block]['body'][$string]['type']['name'] = $name;
										
										$this->buffer = '';
										$value = [];
										
										$this->next (); // Скобку пропускаем
										$this->trimSpace (); // Пробелы тоже
										
										while ($this->char != ')') {
											
											if ($name == 'enum' and $this->ch == ',') {
												
												$value[] = $this->buffer;
												$this->buffer = '';
												
											} elseif ($this->char != $this->from->value_quote ())
												$this->buffer .= $this->ch;
											
											$this->next ();
											
										}
										
										if ($name == 'enum') {
											
											$value[] = $this->buffer;
											$this->output[$this->block]['body'][$string]['type']['length'] = $value;
											
										} else {
											
											if (is_numeric ($this->buffer))
												$this->buffer = (int) $this->buffer;
											
											$this->output[$this->block]['body'][$string]['type']['length'] = $this->buffer;
											
										}
										
										continue;
										
									}
									
								}
								
								$this->buffer .= $this->ch;
								
							}
							
						}
						
						$this->next ();
						
					}
					
					$name = '';
					$this->trimSpace (); // Трем пробелы
					$this->buffer = '';
					
					while ($this->char != ';') {
						
						if ($this->char == '=') { // ENGINE=MyISAM
							
							$name = $this->buffer; // ENGINE
							$this->buffer = '';
							
						} elseif ($this->buffer == 'DEFAULT')
							$this->buffer = '';
						elseif ($this->isSpace ()) {
							
							$this->output[$this->block][strtolower ($name)] = $this->buffer;
							$this->buffer = '';
							
						} else $this->buffer .= $this->ch;
						
						$this->next ();
						
					}
					
					if ($this->char == ';') { // Новый запрос, все чистим
						
						$this->output[$this->block][strtolower ($name)] = $this->buffer;
						
						$this->buffer = '';
						$this->block++;
						
						$i = 0;
						$string = 0;
						
					}
					
				} elseif ($this->char == '/') {
					
					$this->buffer = $this->ch;
					$this->next ();
					
					while ($this->char != ';') { // Читаем до конца
						
						$this->buffer .= $this->ch;
						$this->next ();
						
					}
					
					if ($this->char == ';') $i = 0;
					
					$this->output[$this->block]['settings'] = $this->buffer;
					$this->buffer = '';
					
					if ($i == 0) $this->block++;
					
				} elseif ($this->char == ';') { // Новый запрос, все чистим
					
					$this->output[$this->block]['table'] = $this->buffer;
					
					$this->buffer = '';
					$this->block++;
					$i = 0;
					
				} elseif ($this->char != $this->from->table_quote ())
					$this->buffer .= $this->ch;
				
			}
			
			return $this->output;
			
		}
		
	}