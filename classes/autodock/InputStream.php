<?php
	
	abstract class InputStream {
		
		public $file;
		
		function __construct (File $file) {
			$this->file = $file;
		}
		
	}