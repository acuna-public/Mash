<?php
	
	class JSONObject2 extends Tokenizer {
		
		private $level = 0, $output = [], $key, $value = '';
		public $quote = '"', $curly_open = '{', $curly_close = '}', $square_open = '[', $square_close = ']', $colon = ':', $comma = ',';
		
		function __construct ($array) {
			
			parent::__construct ($array);
			
			debug ($this->content);
			
			$this->brackets_num = [
				
				[
					$this->curly_open => 0,
					//$this->square_open => 0
				]
				
			];
			
		}
		
		function process (): array {
			
			$content = [];
			
			if ($this->length > 0) {
				
				$this->next ();
				//debug ($this->char);
				
				if ($this->char == $this->comma or $this->length == 1) {
					
					$content[] = $this->buffer;
					$this->buffer = '';
					
				} else $this->buffer .= $this->ch;
				
				/*if ($this->char == $this->curly_open) { // Ассоциированный
					
					$this->brackets_num[$this->level][$this->curly_open] = 0;
					
					$this->block ($this->curly_open, $this->curly_close);
					$this->level++;
					
					$this->next (); // "
					$this->next ();
					
					$this->key = '';
					
					while ($this->char != $this->quote) { // Ключ
						
						$this->key .= $this->ch;
						$this->next ();
						
					}
					
					$this->next (); // :
					$content[$this->key] = $this->process ($content);
					
				} elseif ($this->char == $this->curly_close) { // }
					
					$this->level--;
					$this->block ($this->curly_open, $this->curly_close);
					
					if ($this->brackets_num[$this->level][$this->curly_open] == 0) {
						
						if ($this->buffer) $content[] = $this->buffer;
						$this->buffer = '';
						
					}
					
				} else $this->buffer .= $this->ch;*/
				
				$content = $this->process ($content);
				
				/*if ($this->char == $this->comma) {
					
					$content[] = $this->buffer;
					$this->buffer = '';
					
				} elseif (!$this->isSpace ()) {
					
					$this->buffer .= $this->ch;
					$content = $this->process ($content);
					
				}*/
				
				/*if ($this->char == $this->curly_open) { // Ассоциированный
					
					$this->brackets_num[$this->level][$this->curly_open] = 0;
					
					$this->block ($this->curly_open, $this->curly_close);
					$this->level++;
					
					$this->next (); // "
					$this->next ();
					
					$this->buffer = '';
					
					while ($this->char != $this->quote) { // Ключ
						
						$this->buffer .= $this->ch;
						$this->next ();
						
					}
					
					$this->next (); // :
					
					$this->output[$this->buffer] = $this->output;
					$content = $this->process ($content);
					
				} elseif ($this->char == $this->curly_close) { // }
					
					$this->level--;
					$this->block ($this->curly_open, $this->curly_close);
					
					if ($this->brackets_num[$this->level][$this->curly_open] == 0) {
						
					}
					
				}/* elseif ($this->char == ',') { // Строка или число
					
					$content[$this->buffer] = $this->process ($content);
					
				} else {
					
					$this->next ();
					
					$this->output = '';
					
					while ($this->char != $this->quote) { // Значение
						
						$this->output .= $this->ch;
						$this->next ();
						
					}
					
				}*/
				
			}
			
			return $content;
			
		}
		
		protected function block ($start, $finish) {
			
			if ($this->char == $start)
				$this->brackets_num[$this->level][$start]++;
			elseif ($this->char == $finish)
				$this->brackets_num[$this->level][$start]--;
			
		}
		
		function __destruct () {
			print_r ($this->brackets_num);
		}
		
	}