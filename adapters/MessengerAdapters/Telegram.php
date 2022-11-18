<?php
	
	namespace Messenger;
	
	class Telegram extends \Messenger {
		
		protected $token;
		
		function __construct ($token) {
			$this->token = $token;
		}
		
		function getTitle () {
			return 'Telegram';
		}
		
		function getName () {
			return 'telegram';
		}
		
		function _send (array $data) {
			
			$curl = new \Curl ();
			
			$curl->setData (['method' => \Curl::POST, 'url' => 'https://api.telegram.org/bot'.$this->token .'/sendMessage', 'post_fields' => [
				
				'chat_id' => $data['email'],
				'text' => str_replace ('<br/>', "\n", $data['text']),
				'parse_mode' => 'HTML'
				
			]]);
			
			$curl->process ()[0]->getContent ();
			
		}
		
		function send ($tpl_name = '') {
			
			foreach ($this->data as $data)
				$this->_send ($data);
			
		}
		
	}