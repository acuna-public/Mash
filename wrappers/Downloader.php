<?php
	
	namespace Mash;
	
	require 'CLI.php';
	
	abstract class Downloader extends CLI {
		
		function onInit () {
			
			if (is_isset ('proxy', $this->args))
				$this->setProxies (sep_explode ($this->args['proxy']));
			
			if (!is_isset ('output', $this->args))
				$this->args['output'] = 'mysql';
			
			if (!is_isset ('host', $this->args))
				$this->args['host'] = '127.0.0.1';
			
			if (!is_isset ('user', $this->args))
				$this->args['user'] = 'root';
			
			if (!is_isset ('password', $this->args))
				$this->args['password'] = 'root';
			
			$this->db_config = [
				
				'provider' => $this->args['output'],
				'host' => $this->args['host'],
				'base_name' => $this->args['db'],
				'user' => $this->args['user'],
				'password' => $this->args['password'],
				'prefix' => '',
				
			];
			
			parent::onInit ();
			
		}
		
	}