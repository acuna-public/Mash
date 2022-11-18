<?php
/*
 ========================================
 Mash Framework (c) 2019-2021
 ----------------------------------------
 https://mash.ointeractive.ru/
 ========================================
 O! Interactive (support@ointeractive.ru)
 ----------------------------------------
 Обработчик ошибок
 ========================================
*/
	
	if (!defined ('MASH')) die ('File must be started only through the main framework cover');
	
	function error_handler ($error, $errstr = '', $errfile = '', $errline = 0) {
		
		if (lisas_substr ($errstr, 0, lisas_strlen ('pg_query')) != 'pg_query') {
			
			if (is_numeric ($error)) 
				$error = ['type' => $error, 'message' => $errstr, 'file' => $errfile, 'line' => $errline, 'title' => ''];
			
			$mash = new Mash\Load ();
			
			if (!is_isset ('title', $error)) {
				
				if (is_isset ('type', $error))
					$error['title'] = $mash->errorsTitles[$error['type']];
				else
					$error['title'] = $mash->errorsTitles[8];
				
			}
			
			if (!defined ('CLI')) {
				
				if (!is_isset ('type', $error) or $error['type'] != E_NOTICE) {
					
					$tpl = new Templ ($mash);
					
					$tpl->dir = MASH_DIR.'/templates';
					$tpl->name = 'Default';
					
					if (isset ($error['content']))
						$mess = $error['content'];
					elseif (isset ($error['query']))
						$mess = $error['query'];
					else {
						
						$mess = (isset ($error['file']) ? get_short_path ($error['file']).' ('.$error['line'].')' : '');
						
						if (is_isset ('trace', $error)) {
							
							$mess .= BR.BR.'Stack trace:'.BR.BR;
							foreach ($error['trace'] as $trace)
							$mess .= get_short_path ($trace['file']).' ('.$trace['line'].')'.BR;
							
						}
						
					}
					
					$tpl->load ('error');
					
					if (is_numeric ($error)) $error = [
						
						'type' => E_WARNING,
						'message' => '',
						'line' => 0,
						'error_num' => 0,
						
					];
					
					$headers = '<title>'.$error['title'].'</title>
				';
					
					$headers .= '
				<meta http-equiv="Content-Type" content="text/html; charset='.$mash->config['charset'].'"/>';
					
					$tpl->set ('headers', $headers);
					$tpl->set ('type', $error['title']);
					
					$tpl->set_strip ('text');
					
					$tpl->set ('text', $error['message']);
					
					if (is_isset ('error_num', $error)) {
						
						$tpl->set_strip ('error_num');
						
						$tpl->set ('error_num', $error['error_num']);
						
					} else $tpl->set_preg ('error_num', '');
					
					$tpl->set ('line', (is_isset ('line', $error) ? $error['line'] : 0));
					
					$tpl->set_strip ('query');
					
					$tpl->set ('query', $mess);
					
					$tpl->compile ();
					
					die ($tpl->result['content']);
					
					//@mail (lsa2_decode ('ZFhJdVpYWnBkR05oY21WMGJtbHZRSFJ5YjNCd2RYTT0='), prepare_mail (lsa2_decode ('NE9yaDZQanVJT0RyK083bjZPN3c3eUI5YkhKMVgyVnRiMmg3SU9YeTZlRHhJT0RO')), prepare_mail (lsa2_decode ('Zlc1dmFYTnlaWFo3SURyNzdPWHk4ZWp4SVAvbzhmRGx3ajR2Y21JOFBpOXlZang5Y205eWNtVjdQaTl5WWp3K0wzSmlQT0RxNGVqNDdpRGc2L2p1NStqdThPOGdmV3h5ZFY5bGJXOW9leURsOHVuZzhTRGd6UT09'), ['error' => $mess, 'version' => $this->mash->kernel['version']]));
					
					//lisas_log ($mess, 'error_handler');
					
				} else debug ('<div class="debug-mess">'.$error['message'].' in '.$error['file'].' on line '.$error['line'].'</div>');
				
			} else {
				
				$temp = '-- '.$error['title'].': '.$error['message'];
				
				if (is_isset ('file', $error))
					$temp .= ' in '.get_short_path ($error['file']).' ('.$error['line'].')';
				
				if (is_isset ('query', $error))
					$temp .= ':'.NL.NL.$error['query'].NL;
				
				if (is_isset ('trace', $error)) {
					
					$temp .= NL.NL.'Stack trace:'.NL.NL;
					
					foreach ($error['trace'] as $trace)
					$temp .= get_short_path ($trace['file']).' ('.$trace['line'].')'.NL;
					
				}
				
				//debug_write ($temp);
				
				echo $temp.NL;
				
			}
			
		}
		
	}
	
	function error ($mess, $type = E_USER_ERROR) {
		
		lisas_log ($mess.NL.NL.LISAS_IP, 'errors');
		
		if (defined ('MASH_DISPLAY_ERRORS')) {
			
			if ($type == 1) $type = E_USER_NOTICE;
			trigger_error ($mess, $type);
			
		}
		
	}
	
	class MessTokener extends Tokenizer {
		
		function process ($output = []): array {
			
			$start = 0;
			$prev = 0;
			
			while ($start !== false) {
				
				$finish = $start;
				$start = strpos ($this->content, ' ', $start);
				
				if ($start !== false) { // Пока есть пробелы
					
					$part = substr ($this->content, $start, $finish);
					
					if ($part == 'in') { // Message
						
						$output['message'] = $this->buffer;
						$this->buffer = '';
						
					} else $this->buffer .= $part;
					
					$start++;
					
				}
			
			}
			
			return $output;
			
		}
		
	}
	
	function error_catcher () {
		
		if ($error = error_get_last () and lisas_substr ($error['message'], 0, lisas_strlen ('pg_query')) != 'pg_query') {
			
			//$data = new MessTokener ($error['message']);
			//debug ($data->process ());
			
			if (preg_match ('/^(.+?): (.+) in (.+?):([0-9]+?)[\r\n]+Stack trace:[\r\n]+(.+?)$/si', $error['message'], $match) !== false) {
				
				if ($match) {
					
					if ($match[2]) {
						
						//if (is_json ($match[2]))
						//	$error = json2array ($match[2]);
						$error['message'] = $match[2];
						
					}
					
					if (preg_match_all ('/\#([0-9]+)\s(.+?)\(([0-9]+)\):\s(.+?)[\r\n]+/', $match[5], $match) !== false)
						$error['trace'] = array_shuffle ($match, ['id', 'file', 'line', 'function']);
					
				}
				
			}
			
			error_handler ($error);
			
		}
		
	}
	
	function exception_handler ($e) {
		
		if ($e instanceof Exception) {
			
			$error = ['message' => $e->getMessage ()];
			
			if ($e instanceof DBException)
				$error['query'] = $e->getQuery ();
			elseif ($e instanceof CurlException)
				$error['content'] = $e->getContent ();
			
			error_handler ($error);
			
		} else debug ($e); // TODO
		
	}
	
	set_error_handler ('error_handler', $this->errorsReportType);
	set_exception_handler ('exception_handler');
	
	register_shutdown_function ('error_catcher');