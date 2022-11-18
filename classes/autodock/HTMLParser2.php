<?php
	
	class HTMLParser2 extends Tokenizer {
		
		public $start = '<', $end = '>';
		
		function process (): array {
			
			$output = [];
			
			$this->next ();
			
			while ($this->read ()) {
				
				if ($this->ch == $this->start) {
					
					//if (trim ($this->buffer))
					$output[] = ['text' => $this->buffer]; // Текст до тега
					$this->buffer = '';
					
					$this->next (); // <
					
					if ($this->ch == '/') { // Закрывающий тег
						
						$this->buffer = '';
						
						while ($this->ch != $this->end) // Пропускаем все до закрытия
							$this->next ();
						
						break;
						
					} else {
						
						$tag = [];
						
						//if ($this->buffer) $tag['text2'] = $this->buffer; // Текст до тега
						//$this->buffer = '';
						
						//$tag['attrs'] = $attrs;
						
						//$output[] = $tag;
						
						$output[] = $this->process ();
						
					}
					
				} elseif ($this->ch == $this->end) {
					
					$output[] = ['tag' => $this->buffer]; // Тег
					$this->buffer = '';
					
					//continue;
					
				} else $this->buffer .= $this->ch;
				
				$this->next ();
				
			}
			
			//if ($this->buffer) $output[] = $this->buffer;
			
			return $output;
			
		}
		
	}
	
	$parser = new HTMLParser2 ('111<div class="ggg" 777="gg88g">
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