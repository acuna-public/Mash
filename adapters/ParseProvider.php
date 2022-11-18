<?php
	
	namespace Parser;
	
	abstract class Provider {
		
		protected $parser;
		
		function __construct (\Parse $parser) {
			$this->parser = $parser;
		}
		
		abstract function newInstance (\Parse $parser): \Parser\Provider;
		abstract function fromHTML ();
		abstract function toHTML ();
		
	}