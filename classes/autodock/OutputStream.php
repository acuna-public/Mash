<?php
	
	abstract class OutputStream {
		
		protected $file;
		
		function __construct ($file) {
			
			$this->file = $file;
			
		}
		
		abstract function write ($content);
		
	}