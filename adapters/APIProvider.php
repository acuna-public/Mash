<?php
	
	namespace Mash\API;
	
	abstract class Provider {
		
		protected $mash, $params = [];
		
		function __construct (\Mash\API $mash = null) {
			$this->mash = $mash;
		}
		
		function onInit () {}
		
		abstract function getType (): string;
		abstract function newInstance (\Mash\API $mash): Provider;
		abstract function toString (): string;
		
		protected final function error (int $code, string $message = ''): string {
			return $this->getError (['code' => $code, 'message' => http_get_message ($code)]);
		}
		
	}