<?php
	
	abstract class Tokenizer {
		
		protected $content, $ch = '', $offset = 0, $length = 0, $buffer = '', $brackets_num = [], $file, $words = [];
		
		protected $spaces = " \n\t";
		
		function __construct ($object) {
			
			if ($object instanceof File) {
				
				$this->file = $object;
				$this->length = $this->file->size;
				
				$this->content = $this->file->read ();
				
			} else {
				
				$this->content = $object;
				$this->length = strlen ($this->content);
				
			}
			
		}
		
		protected final function read () {
			return ($this->offset < $this->length);
		}
		
		protected function next () {
			
			if ($this->file)
				$this->ch = $this->file->char ($this->offset);
			else
				$this->ch = $this->content[$this->offset];
			
			$this->offset++;
			
		}
		
		protected final function isChar (string $string): bool {
			return (lisas_strpos ($this->ch, $string) !== false);
		}
		
		protected final function isSpace () {
			return $this->isChar ($this->spaces);
		}
		
		protected function trimSpace () {
			while ($this->isSpace ()) $this->next ();
		}
		
		abstract function process (): array;
		
		protected function block ($start, $finish) {
			
			if (!isset ($this->brackets_num[$start]))
				$this->brackets_num[$start] = 0;
			
			if ($this->ch == $start)
				$this->brackets_num[$start]++;
			elseif ($this->ch == $finish)
				$this->brackets_num[$start]--;
			
		}
		
		protected function debug (...$mess) {
			
			if (!$mess) $mess = [$this->ch];
			echo '-- '.implode (' - ', $mess)."\n";
			
		}
		
	}