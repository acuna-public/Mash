<?php
	
	class Percent {
		
		protected $value;
		
		public $delim = 100;
		
		function __construct ($value) {
			$this->value = $value;
		}
		
		function valueOf ($percent) {
			
			if ($this->delim != 0)
				return ($this->value * $percent) / $this->delim;
			else
				throw new \Exception ('Division by zero');
			
		}
		
		function getDiff ($value) {
			
			$percent = new self ($value - $this->value);
			
			$percent->delim = $value;
			
			return $percent->valueOf (100);
			
		}
		
		function getDiff2 ($value) {
			return ($value / $this->value) * $this->delim;
		}
		
		function getTimes ($value) {
			return ($value / $this->value);
		}
		
		function plus ($percent) {
			return ($this->value * (1 + ($percent / $this->delim)));
		}
		
		function minus ($percent) {
			return ($this->value * (1 - ($percent / $this->delim)));
		}
		
		function getUpward ($value) {
			return (($this->value / $value) * $this->delim - $this->delim);
		}
		
		function getLesser ($value) {
			return ($this->delim - ($value / $this->value) * $this->delim);
		}
		
	}