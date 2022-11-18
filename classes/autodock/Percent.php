<?php
	
	class Percent {
		
		protected $value;
		
		function __construct ($value) {
			$this->value = $value;
		}
		
		function valueOf ($percent, $delim = 100) {
			return ($this->value * $percent) / $delim;
		}
		
	}