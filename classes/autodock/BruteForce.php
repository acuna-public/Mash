<?php
	
	class BruteForce {
		
		public $symbols = [
			
			'abcdefghijklmnopqrstuvwxyz',
			'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
			'0123456789',
			'!#$%&\'\"()*+,-./:;<=>?@ [\]^_`{|}~',
			
		];
		
		public $start = [];
		
		protected $symbols2 = [], $length = 0, $types = [], $i = 0, $next = 0;
		
		protected function init () {
			
			if (!$this->symbols2) {
				
				foreach ($this->symbols as $symbols)
					foreach (mb_str_split ($symbols) as $symbol)
						$this->symbols2[] = $symbol;
				
			}
			
			if (!$this->types)
				foreach ($this->symbols as $i => $symbols)
					$this->types[] = $i;
			
			if ($this->start and !is_array ($this->start))
				$this->start = mb_str_split ($this->start);
			
			if (!$this->start)
				for ($i = 0; $i < $this->length; $i++)
					$this->start[] = $this->symbols[$this->types[0]][0]; // aaa
			
		}
		
		function allLexicographicRecur (array $data, int $index) {
			
			for ($i = 0; $i < count ($this->symbols2); $i++) {
				
				$data[$index] = $this->symbols2[$i];
				
				if ($index == ($this->length - 1)) {
					
					$out = implode ('', $data);
					
					if ($out == 'z!B2a') {
						
						debug_time ($out);
						break;
						
					}
					
				} else $this->allLexicographicRecur ($data, $index + 1);
				
			}
			
    }
		
		function generate (int $length) {
			
			$this->length = $length;
			
			$this->init ();
			
			$this->allLexicographicRecur ([], 0);
			
		}
		
	}