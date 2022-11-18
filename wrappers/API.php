<?php
	
	namespace Mash;
	
	require 'Service.php';
	
	abstract class API extends Service {
		
		public $param = ['type' => ''];
		
		protected $providers = [], $lang = [];
		
		function addProvider (API\Provider $provider) {
			
			$this->providers[] = $provider;
			return $this;
			
		}
		
		protected function getConfig (array $data): array {
			return $data;
		}
		
		protected function setProvider (): ?API\Provider {
			
			foreach ($this->providers as $provider)
				if ($provider->getType () == $this->param['type'])
					return $provider;
			
			return ($this->providers ? $this->providers[0] : null);
			
		}
		
		protected function getTypes (): array {
			
			$types = [];
			
			foreach ($this->providers as $provider)
				$types[] = $provider->getType ();
			
			return $types;
			
		}
		
		protected function onShow (): string {
			
			$this->param = array_extend ($_GET, $this->param);
			
			$types = $this->getTypes ();
			$provider = $this->setProvider ();
			
			if (!$provider)
				throw new \Exception ('Provider with '.$this->param['type'].' type not found');
			elseif ($this->param['type'] and !in_array ($this->param['type'], $types)) {
				
				$mess = 'Provider with '.$this->param['type'].' type not found, allowed types: ';
				
				foreach ($types as $type) {
					
					if ($i > 0) $mess .= ', ';
					$mess .= $type;
					
				}
				
				throw new \Exception ($mess);
				
			} else $provider = $provider->newInstance ($this);
			
			//@header ('Content-type:'.$provider->getType ().'; Charset:'.$this->config['charset']);
			
			$provider->onInit ();
			
			return $provider->toString ();
			
		}
		
	}