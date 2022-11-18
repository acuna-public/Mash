<?php
	
	namespace Mash;
	
	abstract class Adapter {
		
		public $config;
		
		function __construct (array $config = []) {
			$this->config = $config;
		}
		
		abstract function getName ();
		
	}