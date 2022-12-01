<?php
	
	namespace Mash;
	
	abstract class Service extends \Mash {
		
		public
			$curl,
			$cache,
			$db,
			$db_config = [],
			$locale,
			$proxies = [];
		
		function __construct () {
			
			parent::__construct ();
			
			if (!defined ('CLI') and php_sapi_name () == 'cli')
				define ('CLI', true);
			
		}
		
		protected function prepRootDir ($dir) {
			return $dir;
		}
		
		protected function onInit () {
			
			$this->curl = new \Curl ();
			
			$this->curl->setOptions (['proxy' => $this->proxies]);
			//$this->curl->setReportType (\Curl::REPORT_TIMEOUT_ACTION);
			
			$this->db_config = array_extend ($this->db_config, [
				
				'provider' => 'mysqli',
				
			]);
			
			$this->addDBProvider (new \DB\MySQL ($this->db_config));
			$this->addDBProvider (new \DB\PostgreSQL ($this->db_config));
			
			$this->db = $this->getDBProvider ($this->db_config['provider']);
			
			$this->cache = new \Cache ();
			
			$this->cache->dir = $this->loadDir ('cache');
			
			$this->db->setCache ($this->cache);
			
			$this->db->connect ();
			
			$this->locale = new \Locale ($this);
			
		}
		
		final function setProxies (array $proxies) {
			$this->proxies = $proxies;
		}
		
		function link2 ($data) {}
		
		const
			MESS_TEXT_COLOR_BLACK = 30,
			MESS_TEXT_COLOR_RED = 31,
			MESS_TEXT_COLOR_GREEN = 32,
			MESS_TEXT_COLOR_YELLOW = 33,
			MESS_TEXT_COLOR_BLUE = 34,
			MESS_TEXT_COLOR_VIOLET = 35,
			MESS_TEXT_COLOR_SEA = 36,
			MESS_TEXT_COLOR_GRAY = 37,
			MESS_TEXT_COLOR_DEFAULT = 39,
			MESS_TEXT_COLOR_DARK_GRAY = 90,
			MESS_TEXT_COLOR_LIGHT_RED = 91,
			MESS_TEXT_COLOR_LIGHT_GREEN = 92,
			MESS_TEXT_COLOR_LIGHT_YELLOW = 93,
			MESS_TEXT_COLOR_LIGHT_BLUE = 94,
			MESS_TEXT_COLOR_LIGHT_MAGENTA = 95,
			MESS_TEXT_COLOR_LIGHT_CYAN = 96,
			MESS_TEXT_COLOR_WHITE = 97,
			
			MESS_BG_COLOR_BLACK = 40,
			MESS_BG_COLOR_RED = 41,
			MESS_BG_COLOR_GREEN = 42,
			MESS_BG_COLOR_YELLOW = 43,
			MESS_BG_COLOR_BLUE = 44,
			MESS_BG_COLOR_VIOLET = 45,
			MESS_BG_COLOR_SEA = 46,
			MESS_BG_COLOR_GRAY = 47,
			MESS_BG_COLOR_DEFAULT = 49,
			
			MESS_TEXT_TYPE_DEFAULT = 0,
			MESS_TEXT_TYPE_INTENSIVE = 1,
			//MESS_TEXT_TYPE_DIM = 2,
			//MESS_TEXT_TYPE_STDOUT = 3,
			MESS_TEXT_TYPE_UNDERLINE = 4,
			MESS_TEXT_TYPE_BLINK = 5,
			MESS_TEXT_TYPE_INVERSE = 7,
			MESS_TEXT_TYPE_HIDDEN = 8;
		
		function print ($text, $text_color = 0, $bg_color = self::MESS_BG_COLOR_DEFAULT, $text_type = self::MESS_TEXT_TYPE_INTENSIVE) {
			
			echo $text;
			
			flush ();
			ob_flush ();
			
		}
		
		function println ($text = '', $text_color = 0, $bg_color = self::MESS_BG_COLOR_DEFAULT, $text_type = self::MESS_TEXT_TYPE_INTENSIVE) {
			if ($text) $this->print ($text.NL, $text_color, $bg_color, $text_type);
		}
		
		protected $oldMess = '';
		
		function printr ($text = '', $text_color = 0, $bg_color = self::MESS_BG_COLOR_DEFAULT, $text_type = self::MESS_TEXT_TYPE_INTENSIVE) {
			
			$text2 = $text;
			
			if (strlen ($text) < strlen ($this->oldMess))
				$text .= str_repeat (' ', (strlen ($this->oldMess) - strlen ($text)));
			
			$text .= "\r";
			
			$this->oldMess = $text2;
			
			$this->print ($text, $text_color, $bg_color, $text_type);
			
		}
		
		function sendCode ($code, $version = '1.1') {
			
			$mess = http_get_message ($code);
			
			if (substr (php_sapi_name (), 0, 3) == 'cgi')
				@header ('Status: '.$mess);
			else
				@header ('HTTP/'.$version.' '.$code.' '.$mess);
			
		}
		
	}