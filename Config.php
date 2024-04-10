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
			
			foreach ($this->setLibraries ([]) as $library)
			foreach ($library as $library => $dirs) {
				
				list ($author, $name, $ver) = $dirs;
				
				$path = [__DIR__, '..', $this->config['libraries_dir'], $author, $name, $ver];
				
				foreach ($this->mash->scanCurrentDir ($path) as $file)
					require $file;
				
				foreach ($this->getLibrariesDirs ()[$library] as $dir) {
					
					$path2 = $path;
					$path2[] = $dir;
					
					$dir = implode (DS, $path2);
					
					foreach ($this->mash->scanCurrentDir ($dir, true) as $file)
						require $file;
					
				}
				
				$path2 = $path;
				$path2[] = 'autodock';
				
				foreach ($this->mash->scanCurrentDir ($path2, true) as $file)
					require $file;
				
			}
			
		}
		
		protected function getLibrariesDirs () {
			return [];
		}
		
		final function loadDir ($dir) {
			
			foreach ($this->mash->scanDir ([$dir]) as $file)
			require $file;
			
		}
		
	}