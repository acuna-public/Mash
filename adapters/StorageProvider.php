<?php
	
	namespace Storage;
	
	abstract class Provider {
		
		abstract function getName ();
		abstract function getTitle ();
		abstract function version ();
		
		function connect () {}
		
		abstract function dir_make ($dir, $chmod = 0777);
		function dir_change ($dir) {}
		function dir_name () {}
		
		abstract function list ($dir = '/', $recursive = 0, $max_level = 0);
		
		abstract function read ($file): \InputStream;
		abstract function write ($content, $file);
		
		abstract function copy ($local_file, $remote_file);
		
		abstract function delete ($dir);
		
	}