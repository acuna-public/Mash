<?php
	
	class File {
		
		private $fp;
		public $file, $size;
		
		private const READ = 'rb', WRITE = 'ab', REWRITE = 'wb', ADD = 'w+b';
		
		function __construct ($file) {
			
			$this->file = $file;
			
			if (file_exists ($this->file))
				$this->size = filesize ($this->file);
			
		}
		
		private function open ($mode) {
			
			if (!$this->fp)
				$this->fp = fopen ($this->file, $mode);
			
		}
		
		function read (): string {
			
			$this->open (self::READ);
			return fread ($this->fp, $this->size);
			
		}
		
		function write ($content) {
			
			$this->open (self::WRITE);
			return fwrite ($this->fp, $content);
			
		}
		
		function rewrite ($content) {
			
			$this->open (self::REWRITE);
			return fwrite ($this->fp, $content);
			
		}
		
		function add ($content) {
			
			$this->open (self::ADD);
			return fwrite ($this->fp, $content);
			
		}
		
		function char ($offset): string {
			
			$this->open (self::READ);
			
			fseek ($this->fp, $offset);
			return fgetc ($this->fp);
			
		}
		
		function fgets () {
			
			$this->open (self::READ);
			
			return fgets ($this->fp);
			
		}
		
		function __destruct () {
			if ($this->fp) fclose ($this->fp);
		}
		
		function __toString () {
			return realpath ($this->file);
		}
		
	}