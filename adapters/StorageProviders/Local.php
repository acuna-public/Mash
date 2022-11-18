<?php
	
	namespace Storage\Provider;
	
	class Local extends \Storage\Provider {
		
		function getName () {
			return 'local';
		}
		
		function getTitle () {
			return 'Local';
		}
		
		function version () {
			return '1.0';
		}
		
		function dir_make ($dir, $chmod = 0777) {
			make_dir ($dir, $chmod);
		}
		
		function list ($dir = '/', $recursive = 0, $max_level = 0) {
			return dir_scan ($dir, ['recursive' => $resursive]);
		}
		
		function read ($file): \InputStream {
			return new FileInputStream ($file);
		}
		
		function write ($content, $file) {
			
			$stream = new \FileOutputStream ($file);
			$stream->write ($content);
			
		}
		
		function copy ($local_file, $remote_file) {
			copy ($local_file, $remote_file);
		}
		
		function delete ($file) {
			
			if (is_file ($file))
				delete ($file);
			else
				dir_delete ($file);
			
		}
		
	}