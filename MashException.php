<?php
	
	if (!defined ('MASH')) die ('Hacking attempt!');
	
	class MashException extends Exception {
		
		function __construct ($message) {
			
			if (is_array ($message)) $message = array2json ($message);
			$this->message = $message;
			
		}
		
	}