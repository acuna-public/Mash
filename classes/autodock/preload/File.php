<?php
	
	class File {
		
		protected $fp;
		public $file;
		
		protected const READ = 'rb', APPEND = 'ab', WRITE = 'wb';
		
		function __construct ($file) {
			
			$this->file = $file;
			
		}
		
		protected function open ($mode) {
			$this->fp = fopen ($this->file, $mode);
		}
		
		function read (): string {
			
			$this->open (self::READ);
			return fread ($this->fp, $this->size ());
			
		}
		
		function clean () {
			$this->open (self::WRITE);
		}
		
		function write ($content) {
			
			//if (!$this->fp)
				$this->open (self::WRITE);
			
			return $this->_write ($content);
			
		}
		
		protected function _write ($content) {
			
			if (is_array ($content))
				$content = array2json ($content, JSON_PRETTY_PRINT);
			
			return fwrite ($this->fp, $content);
			
		}
		
		function append ($content) {
			
			//if (!$this->fp)
				$this->open (self::APPEND);
			
			return $this->_write ($content);
			
		}
		
		public $charIndex = 0;
		public $stringIndex = 0;
		
		function getChar ($offset = -1): string {
			
			if (!$this->fp)
				$this->open (self::READ);
			
			if ($offset == -1)
				$offset = $this->charIndex;
			
			fseek ($this->fp, $offset);
			
			$this->charIndex++;
			
			return fgetc ($this->fp);
			
		}
		
		function isEOF () {
			
			if (!$this->fp)
				$this->open (self::READ);
			
			return feof ($this->fp);
			
		}
		
		function getChars () {
			
			if (!$this->fp)
				$this->open (self::READ);
			
			return !$this->isEOF ();
			
		}
		
		function getString () {
			
			if (!$this->fp)
				$this->open (self::READ);
			
			$this->stringIndex++;
			
			return fgets ($this->fp);
			
		}
		
		function __destruct () {
			$this->close ();
		}
		
		function close () {
			if ($this->fp) fclose ($this->fp);
		}
		
		function exists () {
			return file_exists ($this->file);
		}
		
		function delete () {
			unlink ($this->file);
		}
		
		function size () {
			return ($this->exists () ? filesize ($this->file) : 0);
		}
		
		function rename ($to) {
			rename ($this->file, $to);
		}
		
		function __toString () {
			return $this->file;
		}
		
		function pathinfo () {
			return pathinfo ($this->file);
		}
		
		function move (\File $to) {
			return rename ($this->file, $to->file);
		}
		
	}