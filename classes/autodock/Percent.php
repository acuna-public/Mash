<?php
	
	class Percent {
		
		protected $value;
		
		public $delim = 100;
		
		function __construct ($value) {
			$this->value = $value;
		}
		
		function valueOf ($percent) {
			return ($this->value * $percent) / $this->delim;
		}
		
		function getDiff ($b) {
			
			if ($this->value > $b)
				return (($this->value - $b) * $this->delim) / $b;
			elseif ($this->value < $b)
				return (($b - $this->value) * $this->delim) / $this->value;
			else
				return 0;
			
		}
		
		function getTimes ($b) {
			
			if ($this->value > $b)
				return ($this->value / $b);
			elseif ($this->value < $b)
				return ($b / $this->value);
			else
				return 0;
			
		}
		
	}