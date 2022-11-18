<?php
	
	namespace Curl;
	
	class Item {
		
		private
			$curl,
			$read_info = [],
			$handle;
			
		public
			$handle_info,
			$date,
			$offset,
			$data,
			$message,
			$attempt_num = 0,
			$content,
			$redirect_codes = [301, 302],
			$attempts_codes = [429, 502]; // Много попыток
			
		function __construct (\Curl $curl) {
			
			$this->curl = $curl;
			
			$this->handle = $this->curl->info['handle'];
			$this->handle_info = curl_getinfo ($this->curl->info['handle']);
			
			$this->date = new \Date (time (), ['date_adjust' => $this->curl->options['date_adjust']]);
			
		}
		
		function isOK () {
			return ($this->curl->info['result'] == CURLE_OK);
		}
		
		function setUserData ($data) {
			
			$this->user_data[] = $data;
			return $this;
			
		}
		
		function getUserData () {
			return $this->data['user_data'];
		}
		
		function getContent () {
			
			if (!$this->content) {
				
				if ($this->curl->safe_mode)
					$this->content = curl_redir_exec ($this->handle, $this->data['max_redir_num']);
				else
					$this->content = curl_multi_getcontent ($this->handle);
				
				$timeout = false;
				
				foreach ($this->curl->timeout_errors as $error)
				if (lisas_strpos ($error, $this->message) !== false) {
					
					$timeout = true;
					break;
					
				}
				
				if (
					/*(
							!$this->isOK ()
						and
							!in_array ($this->curl->info['result'], $this->data['ignore_curl_errors'])
					)
					or*/
						!in_array ($this->handle_info['http_code'], $this->data['success_codes'])
					or
					(
							$this->data['error_if_result_empty']
						and
							$this->data['method'] != \Curl::HEAD
						and
							$this->content !== '0'
						and
							!$this->content
					)
					or
						$timeout
				) {
					
					if ($this->curl->debug >= \Curl::REPORT_CODE_ERROR)
						$this->data['code_error'] ($this->curl);
					
					throw new \CurlException ('Curl error', $this);
					
				}
				
			}
			
			if ($this->data['success_action'])
				$this->data['success_action'] ($this->curl);
			
			return $this->content;
			
		}
		
		function check_handle () {
			
			if (in_array ($this->handle_info['http_code'], $this->attempts_codes) and $this->data['method'] != \Curl::HEAD) {
				
				if ($this->data['attempts_num'] > 0) {
					
					++$this->attempt_num;
					
					if ($this->attempt_num <= $this->data['attempts_num']) {
						
						if (is_isset ('proxy', $this->curl->options)) {
							
							foreach ($this->curl->change_proxy () as $key => $value)
								$this->data['options'][$key] = $value;
							
						}
						
						//$this->data['options'][CURLOPT_TIMEOUT] *= 10;
						
						if ($this->curl->debug == \Curl::REPORT_TIMEOUT_ACTION)
							$this->data['timeout_action'] ($this->curl);
						
						if ($this->data['sleep_time']) {
							
							sleep ($this->data['sleep_time']); // Спим...
							$this->data['sleep_time'] *= 2;
							
						}
						
						$this->curl->make_query ($this->data); // ... и пробуем снова
						
						return false;
						
					} else {
						
						if ($this->curl->debug >= \Curl::REPORT_TIMEOUT_ERROR) // Исчерпали все попытки
							$this->data['timeout_error'] ($this->curl);
						
						throw new \CurlException ('Too many requests', $this->curl->item);
						
					}
					
				} else {
					
					if ($this->curl->debug >= \Curl::REPORT_TIMEOUT_ERROR)
						$this->data['timeout_error'] ($this->curl);
					
					throw new \CurlException ('Too many requests', $this->curl->item);
					
				}
				
			} else {
				
				$this->attempt_num = 0;
				
				if ($this->data['success_action'])
					$this->data['success_action'] ($this->curl);
				
				return true;
				
			}
			
		}
		
		function getHTML ($charset = '') {
			return str_get_html ($this->getContent (), $charset);
		}
		
		function getArray () {
			return explode ("\n", prep_file_str ($this->getContent ()));
		}
		
		function getJSON () {
			return json2array ($this->getContent ());
		}
		
		private function show_value ($key, $items) {
			return (!$items or (is_array ($items) and in_array ($key, $items)) or $key == $items);
		}
		
		function getInfo ($items = []) {
			
			if (!$this->read_info) {
				
				$info = [
					
					'url' => $this->data['url'],
					'method' => strtoupper ($this->data['method']),
					'error_code' => curl_errno ($this->handle),
					'message' => curl_error ($this->handle),
					'curl_version' => 'cURL/'.$this->curl->curl_version['version'].' '.$this->curl->curl_version['ssl_version'].' libz/'.$this->curl->curl_version['libz_version'].' ('.$this->curl->curl_version['features'].') Curl/'.\Curl::VERSION,
					'post_fields' => $this->data['post_fields'],
					
				];
				
				foreach ($info as $key => $value)
					if ($this->show_value ($key, $items))
						$this->read_info[$key] = $value;
				
        if ($this->show_value ('const', $items)) {
          
          $const = get_defined_constants (true)['curl'];
          
          foreach ($this->curl->info as $key => $value) {
            
            if ($this->show_value ($key, $items)) {
              
              if ($key == 'handle') 
                $value = (string) $value;
              elseif ($key == 'result')
                $value = array_search ($value, $const);
              
              $this->read_info['const'][$key] = $value;
              
            }
            
          }
          
        }
				
				foreach ($this->handle_info as $key => $value) {
					
					if ($this->show_value ($key, $items)) {
						
						$this->read_info[$key] = $value;
						
						if ($key == 'http_code') {
							
							$this->read_info['http_message'] = http_get_message ($value);
							
							if (!is_isset ('message', $this->read_info))
								$this->read_info['message'] = $this->read_info['http_message'];
							
						}
						
					}
					
				}
				
				if ($this->show_value ('options', $items)) {
					
					if (!is_isset ('options', $this->read_info))
						$this->read_info['options'] = [];
					
					foreach ($const as $key => $value)
					foreach ($this->data['options'] as $key2 => $value2)
					if ($key2 == $value)
					$this->read_info['options'][$key] = $value2;
					
				}
        
        /*$this->read_info['answer_headers'] = [];
        
        foreach (get_headers ($info['url']) as $i => $header) {
          
          if ($i > 2) {
            
            $header = explode (':', $header);
            $this->read_info['answer_headers'][$header[0]] = trim ($header[1]);
            
          }
          
        }*/
        
			}
			
			if (is_array ($items))
				return $this->read_info;
			elseif (isset ($this->read_info[$items]))
				return $this->read_info[$items];
			else
				return '';
			
		}
		
		function getCode () {
			return $this->handle_info['http_code'];
		}
		
		function getMessCode () {
			return $this->getCode ().' '.$this->read_info['message'];
		}
		
		function getQueryText () {
			
			$headers = 'curl -X '.strtoupper ($this->data['method']).' "'.$this->data['url'].'" --compressed';
			
			foreach ($this->data['options'][CURLOPT_HTTPHEADER] as $value) {
				
				list ($key, $value) = explode (': ', $value);
				
				if (!in_array ($key, $this->curl->deny_headers))
				$headers .= ' -H "'.$key.': '.$value.'"';
				
			}
			
			if ($this->data['options'][CURLOPT_COOKIE])
				$headers .= ' --cookie "'.$this->data['options'][CURLOPT_COOKIE].'"';
			
			if ($this->data['referer'])
				$headers .= ' -H "Referer: '.$this->data['referer'].'"';
			
			if (is_isset ('post_fields', $this->handle_info))
				$headers .= ' --data "'.implode (' ', $this->handle_info['post_fields']).'"';
			elseif (is_isset ('post_fields', $this->data))
				$headers .= ' --data \''.$this->data['post_fields'].'\'';
			
			return $headers;
			
		}
		
		function getHeaders () { // TODO
			
			$headers = [];
			
			if ($this->content and $this->handle_info['http_code'] != 404) {
				
				$destr = [];
				
				$header_text = substr ($this->content, 0, strpos ($this->content, NL.NL));
				$header_text = explode (NL, $header_text);
				
				foreach ($header_text as $i => $line) if ($i > 0) {
					
					list ($key, $value) = explode (': ', $line);
					
					if (in_array ($key, array_keys ($this->headers)))
						$destr[$key][] = $value;
					else
						$headers[$key] = $value;
					
				}
				
				foreach ($destr as $key => $value)
				$headers[$key] = implode ('; ', $value);
				
			}
			
			ksort ($headers);
			
			return $headers;
			
		}
		
		function __toString () {
			return $this->getInfo ('url');
		}
		
	}