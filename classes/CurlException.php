<?php
	
	class CurlException extends DomainException {
		
		public $item;
		private $type, $url;
		
		function __construct ($type, $item = null) {
			
			$this->type = $type;
			$this->item = $item;
			
			if ($this->item) {
				
				$info = $this->getInfo ();
				
				$this->code = $info['http_code'];
				$this->url = $info['url'];
				$this->message = $this->getMessCode ();
				
			}
			
		}
		
		function getType () {
			return $this->type;
		}
		
		function getURL () {
			return $this->url;
		}
		
		function getInfo (): array {
			return ($this->item ? $this->item->getInfo () : []);
		}
		
		function getContent () {
			return $this->item->content;
		}
		
		function getJSONArray () {
			return json2array ($this->getContent ());
		}
		
		function getMessCode () {
			return $this->item->getMessCode ().' ('.$this->url.')';
		}
		
	}
	
	class CurlFatalException extends DomainException {
		
		function __construct ($message) {
			$this->message = $message;
		}
		
	}