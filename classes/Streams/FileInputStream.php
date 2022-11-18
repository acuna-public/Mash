<?php
	
	class FileInputStream extends InputStream {
		
		function read (): string {
			return file_get_contents ($this->file);
		}
		
	}