<?php
	
	class CSVFile extends \File {
		
		protected $titles = [];
		
		function getData () {
			
			$data = [];
			
			if (!$this->isEOF ()) {
				
				$string = rtrim ($this->getString (), "\r\n");
				
				if ($this->stringIndex == 1) {
					
					foreach (explode (',', $string) as $part)
						$this->titles[] = $part;
					
					$string = rtrim ($this->getString (), "\r\n");
					
				}
				
				foreach (explode (',', $string) as $i => $part)
					$data[$this->titles[$i]] = $part;
					
			}
			
			return $data;
			
		}
		
	}