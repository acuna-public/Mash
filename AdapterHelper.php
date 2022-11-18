<?php
	
	namespace Mash;
	
	class AdapterHelper {
		
		public $adapters = [], $adapter;
		
		final function addAdapter (Adapter $adapter) {
			
			$this->adapters[$adapter->getName ()] = $adapter;
			return $this;
			
		}
		
		final function getAdapter ($name): Adapter {
			return $this->adapters[$name];
		}
		
	}