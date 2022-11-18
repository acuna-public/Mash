<?php
	
	class HTMLParser extends Tokenizer {
		
		public $start = '<', $end = '>';
		
		function process (): array {
			
			$output = [];
			
			$this->next ();
			
			while ($this->read ()) {
				
				if ($this->ch == $this->start) {
					
					$this->next (); // <
					
					if (trim ($this->buffer))
					$output[] = ['text' => $this->buffer]; // Текст до тега
					
					$this->buffer = '';
					
					while (!$this->isSpace () and $this->ch != '/' and $this->ch != $this->end) { // Тег
						
						$this->buffer .= $this->ch;
						$this->next ();
						
					}
					
					$tag = ['tag' => $this->buffer];
					$this->buffer = '';
					
					$attrs = [];
					$key = ''; $value = '';
					
					while ($this->ch != '/' and $this->ch != $this->end) { // Аттрибуты
						
						if ($this->isSpace () or $this->ch == $this->end) {
							
							$attrs[$key] = $this->buffer;
							$this->buffer = '';
							
							$value = '';
							
						} elseif ($this->ch == '=') {
							
							$key = $this->buffer;
							$this->buffer = '';
							
						} else $this->buffer .= $this->ch;
						
						$this->next ();
						
					}
					
					$this->debug ($key, $value);
					
					$tag['attrs'] = $attrs;
					
					//$output[] = $tag;
					
					if ($this->ch == '/') {
						
						$this->buffer = '';
						
						while ($this->ch != $this->end) // Пропускаем все до закрытия
							$this->next ();
						
						break;
						
					} else {
						
						$tag = [];
						
						if ($this->buffer) $tag['text2'] = $this->buffer; // Текст до тега
						$this->buffer = '';
						
						//$tag['attrs'] = $attrs;
						
						//$output[] = $tag;
						
						$output[] = $this->process ();
						
					}
					
				} elseif ($this->ch == $this->end) {
					
					$tag = [];
					
					$this->buffer = '';
					
					$this->next ();
					
					$this->trimSpace ();
					
					while ($this->ch != $this->start) { // Текст
						
						$this->buffer .= $this->ch;
						$this->next ();
						
					}
					
					if ($this->buffer) $tag['text'] = $this->buffer;
					
					$output[] = $tag;
					
					continue;
					
				} else $this->buffer .= $this->ch;
				
				$this->next ();
				
			}
			
			//if ($this->buffer) $output[] = $this->buffer;
			
			return $output;
			
		}
		
	}
	
	$parser = new HTMLParser ('111<div class="ggg" 777="gg88g" >
		<b>
			111
			<i>
				hhh
				<jjj>222</jjj>
			</i>
		</b>
		gggggg
		<s>333</s>
	</div>');
	
	//print_r ($parser->process ());