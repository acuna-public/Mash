<?php
	
	class Range {
		
		protected $start, $end;
		
		protected $part, $stop = false;
		
		function __construct ($start, $end) {
			
			$this->start = $start;
			$this->end = $end;
			
		}
		
		function hasPart ($length): bool {
			
			if (!$this->stop) {
				
				$part = round ($this->end / $length); // 1000 / 10 = 100
				
				$end = ($this->start + $part);
				
				if ($end < $this->end) {
					
					$start = $this->start;
					if ($start > 0) $start++;
					
					$this->part = [$start, $end];
					$this->start += $part;
					
					return true;
					
				} else {
					
					$start = $this->start;
					if ($start > 0) $start++;
					
					$this->part = [$start, $this->end];
					$this->stop = true;
					
					return true;
					
				}
				
			}
			
			return false;
			
		}
		
		function getPart () {
			return $this->part;
		}
		
	}