<?php
	
	class Storage {
		
		public $providers = [], $provider;
		
		final function addProvider (Storage\Provider $provider) {
			
			$this->providers[$provider->getName ()] = $provider;
			return $this;
			
		}
		
		final function getProvider ($name) {
			return $this->providers[$name];
		}
		
		function read ($file) {
			return $this->provider->read ($read)->read ();
		}
		
	}