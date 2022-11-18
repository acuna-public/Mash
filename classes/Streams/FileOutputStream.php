<?php
	
	class FileOutputStream extends OutputStream {
		
		function __construct ($file) {
			parent::__construct ($file);
		}
		
		function write ($content) {
			file_put_contents ($this->file, $content);
		}
		
	}