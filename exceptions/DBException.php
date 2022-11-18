<?php
	
	class DBException extends Exception {
		
		protected $nl, $query;
		
		function __construct ($db, $error, $error_num, $query = '') {
			
			parent::__construct ('', $error_num);
			
			$this->nl = (defined ('CLI') ? NL : BR);
			$this->query = $query;
			
			//if ($this->query) $this->query = preg_replace ('~([0-9a-z]){32}~i', '********************************', parse_debug_mess ($this->query));
			
			//if (!$this->mash->torrent->is_announce) {
				
				if (!in_array ($error_num, $db->no_error_log_errors)) {
					
					$error_mess = '';
					$lines = nl_explode ($error);
					
					foreach ($lines as $i => $line) {
						
						if ($i > 0) {
							
							$this->message .= $this->nl;
							$error_mess .= $this->nl;
							
						}
						
						if (!defined ('CLI'))
							$line = str_replace (' ', '&nbsp;', $line);
						
						$error_mess .= '-- '.$line;
						
						$this->message .= $line;
						
					}
					
					$mess = $error_mess.' ('.$error_num.')';
					
					if (!$db->options['error_not_show_queries'] and $this->query) {
						
						if (!defined ('CLI')) $this->query = nl2br ($this->query);
						$mess .= ':'.$this->nl.$this->nl.$this->query;
						
					}
					
					$db->log_record ('errors', $mess);
					
				}
				
				/*if ($this->show_error) {
					
					if ($db->options['error_templ'])
						error_handler (['title' => $db->getTitle ().' error', 'message' => $this->message, 'query' => $this->query]);
					else
						echo ('-- '.$this->message.': '.$this->nl.$this->nl.$this->query.$this->nl); // TODO
					
				}*/
				
			/*} else {
				
				// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
				// LisaS Torrent Tracker by Acuna
				// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
				$error_text = $db->getTitle ().' Fatal Error '.$error_num.': '.$error.
					' ('.$this->query.').';
				//debug_write ($error_text);
				$this->mash->torrent->error ($error_text); // Ошибка уходит в торрент-клиент.
				// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
				///LisaS Torrent Tracker by Acuna
				// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
				
			}*/
			
		}
		
		public function getQuery () {
			return $this->query;
		}
		
	}