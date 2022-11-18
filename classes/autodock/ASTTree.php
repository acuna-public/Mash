<?php
	
	class ASTTree extends Tokenizer {
		
		function process (): array {
			
			$output = [];
			
			$this->next ();
			
			while ($this->read ()) {
				
				if ($this->ch == '{') {
					
					$output[] = $this->buffer;
					$this->buffer = '';
					
					$output[] = $this->process ();
					
				} elseif ($this->ch == '}') {
					
					$output[] = $this->buffer;
					$this->buffer = '';
					
					break;
					
				} elseif (trim ($this->ch))
					$this->buffer .= $this->ch;
				
				$this->next ();
				
			}
			
			if ($this->buffer) $output[] = $this->buffer;
			
			return $output;
			
		}
		
	}
	
	/*$parser = new ASTTree ('rberbt{
		gre{
			drbebr{
				frrr
			}tgn
		}btnt
	}webrtn');
	
	print_r ($parser->process ());*/