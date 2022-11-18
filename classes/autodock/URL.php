<?php
	
	class URL {
		
		protected $url, $params, $curl, $item;
		
		function __construct (string $url, array $params = [], Curl $curl = null) {
			
			$this->url = $url;
			$this->params = $params;
			
			$this->curl = $curl;
			
			if (!$this->curl) {
				
				$this->curl = new Curl ();
				$this->curl->setData (['url' => $this->url, 'params' => $this->params]);
				
			}
			
			$this->item = $this->curl->process ()[0];
			
		}
		
		function getContent () {
			return $this->item->getArray ();
		}
		
		function getArray () {
			return $this->item->getArray ();
		}
		
		function getJSON () {
			return $this->item->getJSON ();
		}
		
		function getURL () {
			return $this->url;
		}
		
		function getParams () {
			return $this->params;
		}
		
		function __toString () {
			return $this->getURL ();
		}
		
	}