<?php
	
	abstract class Messenger {
		
		public $debug = 0;
		
		protected
			$mash,
			$config = [],
			$error = [],
			$exp = 'htm',
			$parse_time = 0,
			$data = [];
			
		abstract function getName ();
		abstract function getTitle ();
		
		protected function load ($data, $content) {}
		abstract function _send (array $data);
		
		function __construct ($config = []) {
			$this->config = $config;
		}
		
		protected function parse_text_tags ($str) {
			return str_replace (['{site_title}'], [$this->config['site_title']], $str);
		}
		
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		// Функции загрузки
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
		private function tpl_path ($tpl_name) {
			
			$file = dash_filepath ($this->config['email_theme_dir'].'/'.$this->config['mail_template'].'/'.$tpl_name.'.'.$this->exp);
			
			if (!file_exists ($file)) $file = dash_filepath ($this->config['email_theme_dir'].'/Default/'.$tpl_name.'.'.$this->exp);
			
			return $file;
			
		}
		
		function addMessage ($data) {
			
			$this->data[] = $data;
			return $this;
			
		}
		
		function send ($tpl_name = '') {
			
			foreach ($this->data as $data) {
				
				if ($tpl_name) {
					
					foreach (dir_scan ($this->config['email_theme_dir'].'/'.$this->config['mail_template'].'/images', ['recursive' => 1, 'allow_types' => ['jpg', 'gif', 'png']]) as $file)
					$this->mail->AddEmbeddedImage ($this->prep_file_name ($file), get_filename ($file), get_filename ($file, 1));
					
					$data['text'] = $this->load ($data, $this->load_templ ($tpl_name, isset ($data['body_data']) ? $data['body_data'] : []));
					
				}
				
				$this->_send ($data);
				
			}
			
			$this->clear ();
			
		}
		
		protected function clear () {
			
		}
		
		protected function real_time () {
			
			list ($seconds, $micro_seconds) = explode (' ', microtime ());
			return ((float) $seconds + (float) $micro_seconds);
			
		}
		
	}