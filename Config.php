<?php
	
	namespace Mash;
	
	class Config {
		
		protected $mash;
		public $config;
		
		function __construct (\Mash $mash) {
			$this->mash = $mash;
		}
		
		protected function setLibraries (array $libraries): array {
			return $libraries;
		}
		
		protected function getConfig (): array {
			return [];
		}
		
		final function process () {
			
			$this->config = array_extend ($this->getConfig (), [
				
				'libraries_dir' => 'MashLibraries',
				'prepare_output' => true,
				
			]);
			
			foreach ($this->setLibraries ([]) as $library) {
				
				list ($author, $name, $ver) = $library;
				
				$path = [__DIR__, '..', $this->config['libraries_dir'], $author, $name, $ver];
				
				foreach ($this->mash->scanCurrentDir ($path) as $file)
					require $file;
				
				$path[] = 'autodock';
				
				foreach ($this->mash->scanCurrentDir ($path, true) as $file)
					require $file;
					
			}
			
		}
		
		final function loadDir ($dir) {
			
			foreach ($this->mash->scanDir ([$dir]) as $file)
			require $file;
			
		}
		
	}