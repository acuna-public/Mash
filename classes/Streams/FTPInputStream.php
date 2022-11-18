<?php
	
	class FTPInputStream extends InputStream {
		
		private $conn_id, $fp;
		
		function __construct ($file) {
			parent::__construct ($file);
		}
		
		function read (): string {
			
		}
		
	}