<?php
	
	class File {
		
		protected $fp;
		public $file, $size = 0;
		
		protected const READ = 'rb', APPEND = 'ab', WRITE = 'wb';
		
		function __construct ($file) {
			
			$this->file = $file;
			
			if (file_exists ($this->file))
				$this->size = filesize ($this->file);
			
		}
		
		protected function open ($mode) {
			$this->fp = fopen ($this->file, $mode);
		}
		
		function read (): string {
			
			$this->open (self::READ);
			return fread ($this->fp, $this->size);
			
		}
		
		function clean () {
			$this->open (self::WRITE);
		}
		
		function write ($content) {
			
			//if (!$this->fp)
				$this->open (self::WRITE);
			
			return fwrite ($this->fp, $content);
			
		}
		
		function append ($content) {
			
			//if (!$this->fp)
				$this->open (self::APPEND);
			
			return fwrite ($this->fp, $content);
			
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
		
		function getChars () {
			
			if (!$this->fp)
				$this->open (self::READ);
			
			return !feof ($this->fp);
			
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
		
		function __toString () {
			return $this->file;
		}
		
	}