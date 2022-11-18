<?php
	
	namespace Mash;
	
	abstract class Module {
		
		protected $mash;
		
		function __construct (\Mash $mash) {
			
			$this->mash = $mash;
			$this->mash->module = $this;
			
		}
		
		abstract function onShow ();
		
		function getName () {
			return $this->mash->modulesData[$this->mash->mod]['name'];
		}
		
		function getTitle () {
			return $this->mash->modulesData[$this->mash->mod]['title'];
		}
		
		function getType () {
			return $this->mash->modulesData[$this->mash->mod]['type'];
		}
		
		function isOn (): boolean {
			return true;
		}
		
	}