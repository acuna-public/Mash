<?php
/*
 ========================================
 Mash Framework (c) 2010-2017, 2019-2022
 ----------------------------------------
 https://mash.ointeractive.ru/
 ========================================
 O! Interactive (support@ointeractive.ru)
 ----------------------------------------
 -- cURL
 ========================================
*/
	
	/**
	 1.1
		+ Добавлен вывод рандомного useragent'а
		+ Реализована работа с SSL (без сертификатов)
		+ Добавлена возможность указания собственных настроек
		+ Добавлена возможность указания собственных заголовков
		
	 1.2
		+ Осуществляется редирект при получении HTTP заголовка "Location:".
		+ Реализована работа с прокси
		+ Сообщения с кодом более 300 теперь считаются ошибками
		+ Исправлена работа в безопасном режиме
		
	 1.3
		+ Добавлена возможность указания логина и пароля для доступа к серверу
		+ Добавлена возможность авторизации
		+ Добавлена возможность указания кодировки для декодирования запроса
		
	 1.4	10.08.2015
		+ Реализована возможность ввода своих кодов успеха
		
	 1.5	28.10.2015
		+ Реализована возможность вывода собственного обработчика ошибок
		+ При получении кода ошибки при установленном параметре $data['attempts_num'] (по умолчанию не установлен) запрос будет повторен $data['attempts_num'] раз, при каждом повторении скрипт будет засыпать на $data['sleep_time'] сек., если установлено значение $data['sleep_time'] (по умолчанию - 2 сек.).
		+ Контроль времени выполнения запросов
		
	 1.6	05.11.2016
		+ Добавлен расширенный вывод ошибок в случае получения кода ошибок или в режиме отладки
		
	 1.7	28.01.2017
		+ Реализована многопоточность
		+ Реализована возможность работы через прокси
		
	 1.8	19.07.2017
		+ Добавлена расшифровка значений опций
		+ Ответ в режиме отладки теперь раскладывается в массив, если получен в JSON // TODO
		
	 2.0	11.07.2019
		ООП
		
	 2.1	26.06.2020
		+ Расширенная поддержка прокси
		
	 2.2	06.09.2020
		+ Добавлен возврат кода сервера при методе HEAD
		+ Мелкие исправления
		
	 2.3	01.12.2022
		+ Оптимизация кода
		+ Мелкие исправления
		
	*/
	
	class Curl {
		
		const GET = 'get', POST = 'post', PUT = 'put', PATCH = 'patch', DELETE = 'delete', HEAD = 'head';
		const REPORT_CODE_ERROR = 1, REPORT_TIMEOUT_ERROR = 2, REPORT_QUERY_ERROR = 3, REPORT_TIMEOUT_ACTION = 4, REPORT_QUERY = 5, SERVICE_INFO = 6;
		
		public
			$queryTime = 0,
			$date,
			$debug = 0,
			$queryNum = 0,
			$processNum = 0,
			$item, $info,
			$user_data = [],
			$query_options = [],
			$nocustom_types,
			$deny_headers = ['Content-Length', 'Expect'],
			$timeout_errors = ['Operation timed out', 'Connection timed out'],
			$proxy_num = 0,
			$report_type = 0,
			
			$settings = [
				
				'streams_num' => 30,
				'date_adjust' => 0,
				
			],
			
			$queryOptions = [],
			$curl_version,
			$safe_mode;
		
		const VERSION = '2.1';
		
		protected
			$def_options,
			$query = [],
			$handle,
			$multi_handle,
			$time_start,
			$query_data = [],
			$options = [];
		
		function __construct () {
			
			require_once 'CurlItem.php';
			require_once 'CurlException.php';
			
			$this->safe_mode = @ini_get ('safe_mode');
			$this->curl_version = curl_version ();
			
			$this->multi_handle = curl_multi_init ();
			
			$this->nocustom_types = [self::GET, self::POST, self::PUT];
			
			$this->def_options = [
				
				'method' => self::GET,
				'conn_timeout' => 30,
				'query_timeout' => 30,
				'sleep_time' => 5,
				'follow_location' => true,
				'ssl_verify_peer' => true,
				'ssl_verify_host' => 2,
				'max_redir_num' => 30,
				'attempts_num' => 30,
				'success_codes' => [200, 204, 206],
				'show_error_result' => true,
				'return_headers' => false,
				'no_signal' => 1,
				'output_file' => '',
				'user_agent' => '',
				'referer' => '',
				'headers' => [],
				'proxy' => [],
				'encoding' => '',
				'cookies' => [],
				'access' => [],
				'post_fields' => [],
				'params' => [],
				'files' => [],
				'ignore_curl_errors' => [],
				
				'success_action' => function (Curl $curl) {},
				
				'error_if_result_empty' => false,
				'encode_http_params' => 3,
				
				'code_error' => function (Curl $curl) {
					
					$data = $this->mess_array ($curl);
					$data[] = $curl->item->message;
					
					debug_write (debug_mess_implode ($data), 'curl_code_error');
					
				},
				
				'timeout_error' => function (Curl $curl) {
					debug_write (debug_mess_implode ($this->mess_array ($curl)), 'curl_timeout_error');
				},
				
				'query_action' => function (Curl $curl) {
					debug_write (debug_mess_implode ($this->mess_array ($curl)), 'curl_query');
				},
				
				'timeout_action' => function (Curl $curl) {
					debug_write (debug_mess_implode ($this->mess_array ($curl)), 'curl_timeout_error');
				},
				
			];
			
		}
		
		function setOptions ($settings) {
			$this->settings = array_extend ($settings, $this->settings);
		}
		
		function setReportType (int $type) {
			
			$this->debug = $type;
			return $this;
			
		}
		
		function mess_array () {
			
			$info = $this->item->getInfo ();
			
			return [$this->item->date->show (4), $info['url'], $info['http_code'].' '.$info['message'], $this->item->data['sleep_time'], $this->item->attempt_num, $this->item->queryOptions[CURLOPT_PROXY]];
			
		}
		
		function setData ($data, $user_data = [], $i = 0) {
			
			$this->query_data[$i][] = $data;
			
			if ($user_data)
				$this->user_data[$i][] = $user_data;
			
			return $this;
			
		}
		
		function getUserData ($i = 0) {
			return $this->user_data[$i];
		}
		
		function setOption ($key, $value) {
			$this->options[$key] = $value;
		}
		
		private function query ($i, $num) {
			
			$data = array_extend ($this->query_data[$i][$num], $this->def_options);
			
			$this->queryOptions = $this->options;
			
			foreach ($this->settings as $key => $value)
				$data[$key] = $value;
			
			if (!$data['user_agent'])
				$data['user_agent'] = get_useragent (1);
			
			$this->queryOptions[CURLOPT_USERAGENT] = $data['user_agent'];
			
			//foreach ($data['custom_opt'] as $key => $value) // TODO
			//	$this->queryOptions[$key] = $value;
			
			$data['url'] = trim ($data['url'].(count ($data['params']) ? '?'.http_build_fquery ($data['params'], 2) : ''));
			$this->queryOptions[CURLOPT_URL] = $data['url'];
			
			if ($data['url'] != $data['referer'])
				$this->queryOptions[CURLOPT_REFERER] = $data['referer'];
			
			if ($data['no_signal'])
				$this->queryOptions[CURLOPT_NOSIGNAL] = 1;
			
			//$this->queryOptions[CURLOPT_IPRESOLVE] = CURL_IPRESOLVE_V4;
			
			$this->queryOptions[CURLOPT_TIMEOUT] = $data['query_timeout'];
			$this->queryOptions[CURLOPT_CONNECTTIMEOUT] = $data['conn_timeout'];
			
			if ($data['output_file'])
				$this->queryOptions[CURLOPT_STDERR] = $data['output_file'];
			
			switch ($data['method']) {
				
				case self::GET:
					$this->queryOptions[CURLOPT_HTTPGET] = true;
					break;
				
				case self::POST: {
					
					if ($data['files']) {
						
						$data['post_fields']['file'] = new \CurlFile (realpath ($data['files'][0]), 'image/jpeg');
						//$data['headers']['Content-Length'] = filesize ($data['files'][0]);
						
					}
					
					if ($data['post_fields']) {
						
						if (isset ($data['data_type']) and $data['data_type'] == 'json') {
							
							$data['post_fields'] = array2json ($data['post_fields']);
							
							$data['headers']['Content-Type'] = 'application/json';
							//$data['headers']['Content-Length'] = strlen ($data['post_fields']);
							
						} else {
							
							if ($data['encode_http_params'] == 3)
								$data['post_fields'] = http_build_query ($data['post_fields']);
							else
								$data['post_fields'] = http_build_fquery ($data['post_fields'], $data['encode_http_params']);
							
						}
						
						$this->queryOptions[CURLOPT_POSTFIELDS] = $data['post_fields'];
						
					}
					
					$this->queryOptions[CURLOPT_POST] = true;
					
					break;
					
				}
				
				case self::PUT: {
					
					$this->queryOptions[CURLOPT_PUT] = true;
					
					if ($data['file'])
						$this->queryOptions[CURLOPT_FILE] = $data['file'];
					elseif ($data['in_file'])
						$this->queryOptions[CURLOPT_INFILE] = $data['in_file'];
					
					if ($data['in_file_size'])
						$this->queryOptions[CURLOPT_INFILESIZE] = $data['in_file_size'];
					
					break;
					
				}
				
				case self::HEAD: {
					
					$this->queryOptions[CURLOPT_NOBODY] = true;
					//$this->queryOptions[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_1;
					
					break;
					
				}
				
			}
			
			if ($data['method'] and !in_array ($data['method'], $this->nocustom_types))
				$this->queryOptions[CURLOPT_CUSTOMREQUEST] = strtoupper ($data['method']);
			
			$this->queryOptions[CURLOPT_SSL_VERIFYPEER] = $data['ssl_verify_peer'];
			$this->queryOptions[CURLOPT_SSL_VERIFYHOST] = $data['ssl_verify_host'];
			
			$headers = [];
			
			foreach ($data['headers'] as $key => $value) {
				
				if (lisas_substr ($key, 0, 2) != 'x-') {
					
					$key2 = '';
					foreach (explode ('-', $key) as $key)
					$key2 .= ucfirst ($key).'-';
					
					$key = trim ($key2, '-');
					
				}// else $key = strtoupper ($key);
				
				$headers[] = $key.': '.$value;
				
			}
			
			$data['headers'] = $headers;
			
			if ($headers) $this->queryOptions[CURLOPT_HTTPHEADER] = $headers;
			
			if ($data['proxy'])
				foreach ($this->change_proxy ($data['proxy']) as $key => $value)
					$this->queryOptions[$key] = $value;
			
			//$this->queryOptions[CURLOPT_DNS_SERVERS] = '8.8.8.8';
			
			if ($c_options = $data['cookies']) {
				
				if (is_isset ('file_input', $c_options))
					$this->queryOptions[CURLOPT_COOKIEFILE] = dash_filepath ($c_options['file_input']);
				
				if (is_isset ('file_output', $c_options))
					$this->queryOptions[CURLOPT_COOKIEJAR] = dash_filepath ($c_options['file_output']);
				
				if (isset ($c_options['text']))
					$this->queryOptions[CURLOPT_COOKIE] = $c_options['text'];
				
			}
			
			if ($a_options = $data['access'])
			$this->queryOptions[CURLOPT_USERPWD] = $a_options['login'].':'.$a_options['password'];
			
			if ($data['encoding'])
				$this->queryOptions[CURLOPT_ENCODING] = $data['encoding'];
			
			if ($this->safe_mode) {
				
				$this->queryOptions[CURLOPT_RETURNTRANSFER] = false;
				$this->queryOptions[CURLOPT_HEADER] = true;
				
			} else {
				
				if ($data['follow_location']) {
					
					$this->queryOptions[CURLOPT_FOLLOWLOCATION] = true;
					$this->queryOptions[CURLOPT_AUTOREFERER] = true;
					
					if ($data['max_redir_num'])
						$this->queryOptions[CURLOPT_MAXREDIRS] = $data['max_redir_num'];
					
				} else $this->queryOptions[CURLOPT_FOLLOWLOCATION] = false;
				
				$this->queryOptions[CURLOPT_HEADER] = $data['return_headers'];
				
				$this->queryOptions[CURLOPT_RETURNTRANSFER] = true;
				
			}
			
			if ($this->debug == self::SERVICE_INFO) {
				
				$this->queryOptions[CURLOPT_HEADER] = true;
				$this->queryOptions[CURLOPT_VERBOSE] = true;
				
				$curl_log = fopen ('php://temp', 'w+');
				$this->queryOptions[CURLOPT_STDERR] = $curl_log;
				
			}
			
			if (is_isset ($i, $this->user_data) and is_isset ($num, $this->user_data[$i]))
				$data['user_data'] = $this->user_data[$i][$num];
			else
				$data['user_data'] = [];
			
			return $data;
			
		}
		
		function change_proxy ($proxies) {
			
			$output = [];
			
			$proxy = trim ($proxies[mt_rand (0, count ($proxies) - 1)]);
			
			$parts = explode ('@', $proxy);
			
			if (count ($parts) > 1) {
				
				$proxy = $parts[1];
				$output[CURLOPT_PROXYUSERPWD] = $parts[0];
				
			}
			
			$output[CURLOPT_PROXY] = $proxy;
			
			return $output;
			
		}
		
		function make_query ($data, $debug = 0) {
			
			++$this->queryNum;
			
			$this->handle = curl_init ();
			
			foreach ($this->queryOptions as $key => $value)
				curl_setopt ($this->handle, $key, $value);
			
			$add_handle = curl_multi_add_handle ($this->multi_handle, $this->handle);
			
			if ($add_handle != CURLM_OK)
				throw new CurlFatalException (curl_multi_strerror ($add_handle));
			
			$this->query[(int) $this->handle] = $data;
			
		}
		
		function getData ($i = 0) {
			return (isset ($this->query_data[$i]) ? $this->query_data[$i] : []);
		}
		
		function process ($i = 0, $callback = null): array {
			
			$output = [];
			
			if (is_isset ($i, $this->query_data)) {
				
				$this->processNum++;
				$this->time_start = timer_start ();
				
				$total = count ($this->query_data[$i]);
				
				$streams_num = intval_correct ($this->settings['streams_num'], $total);
				$streams_num = intval_rcorrect ($streams_num, $total);
				
				$this->query = [];
				
				for ($i2 = 0; $i2 < $streams_num; ++$i2) // Добавляем указатели только разрешенного количества запросов
					$this->make_query ($this->query ($i, $i2));
				
				do { // Выполняем запросы
					
					while (curl_multi_exec ($this->multi_handle, $running) == CURLM_CALL_MULTI_PERFORM);
					
					curl_multi_select ($this->multi_handle);
					
					while ($info = curl_multi_info_read ($this->multi_handle)) { // Запрос выполнен успешно, получаем и обрабатываем нужные данные
						
						$this->info = $info;
						
						$this->item = new Curl\Item ($this);
						
						$this->item->data = $this->query[(int) $this->info['handle']];
						
						if ($this->item->data['url']) {
							
							if ($this->debug == self::REPORT_QUERY)
								$this->item->data['query_action'] ($this);
							
							if (
								$this->item->data['proxy'] and
								!$this->item->isOK ()
							) {
								
								foreach ($this->change_proxy ($this->item->data['proxy']) as $key => $value)
									$this->queryOptions[$key] = $value; // TODO Item
								
								$this->make_query ($this->item->data);
								++$running; // Important!
								
							} else {
								
								if ($i2 < $total) {
									
									$this->make_query ($this->query ($i, $i2)); // Продожаем делать запросы дальше
									++$i2;
									
								}
								
								if ($this->item->check_handle ()) {
									
									if ($callback)
										$callback ($this->item);
									else
										$output[] = $this->item;
									
								} else ++$running;
								
							}
							
							$this->remove_handle ($this->info['handle']);
							
						} else throw new \CurlException ('URL is empty', $this);
						
					}
					
				} while ($running);
				
				$this->queryTime += timer_stop ($this->time_start);
				
				unset ($this->query_data[$i]);
				unset ($this->user_data[$i]);
				
			}
			
			return $output;
			
		}
		
		private function remove_handle ($handle) {
			
			unset ($this->query[(int) $handle]);
			curl_multi_remove_handle ($this->multi_handle, $handle);
			
		}
		
		static function error (\Exception $e) {
			
			return [
				
				'message' => $e->getMessage (),
				'file' => $e->getFile (),
				'line' => $e->getLine (),
				//'error_num' => $e->getCode (),
				'trace' => $e->getTrace (),
				
			];
			
		}
		
		function auth ($token) {
			return 'Bearer '.$token;
		}
		
		function get ($data): Curl\Item {
			
			$data['method'] = self::GET;
			
			$this->setData ($data);
			return $this->process ()[0];
			
		}
		
		function clear () {
			
			$this->query_data = [];
			$this->user_data = [];
			
		}
		
		function __destruct () {
			curl_multi_close ($this->multi_handle);
		}
		
	}