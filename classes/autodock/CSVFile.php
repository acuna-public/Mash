<?php
	
	class CSVFile extends \File {
		
		protected $titles = [];
		
		function getLine () {
			
			$string = rtrim ($this->getString (), "\r\n");
			
			if ($this->stringIndex == 1) {
				
				foreach (explode (',', $string) as $part)
					$this->titles[] = $part;
				
				$string = rtrim ($this->getString (), "\r\n");
				
			}
			
			$data = [];
			
			foreach (explode (',', $string) as $i => $part)
				if ($part)
					$data[$this->titles[$i]] = $part;
			
			return $data;
			
		}
		
	}