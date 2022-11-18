<?php
/*
 ========================================
 Mash Framework (c) 2013
 ----------------------------------------
 https://mash.ointeractive.ru/
 ========================================
 O! Interactive (support@ointeractive.ru)
 ----------------------------------------
 Класс шаблонизатора
 ========================================
*/
	
	if (!defined ('MASH')) die ('File must be started only through the main framework cover');
	
	class Templ {
		
		public
			$dir = '',
			$template = '',
			$exp = 'htm',
			$copy = '',
			$result = ['info' => '', 'content' => ''],
			$assign_result = '',
			$parse_time = 0,
			$timeStart,
			$name = '',
			$mod = '',
			$debug = 0, // Режим отладки
			
			/**
			 0 - Отключен
			 1 - Выводит пути ко всем шаблонам
			 2 - Выводит содержимое шаблона, пропущенное через функцию
			 htmlspecialchars ();
			*/
			
			$test = '',
			
			$options = [
				
				'full' => true,
				'mess_full' => true,
				'not_show_queries' => false,
				
			],
			
			$dirs = [];
			
		private
			$mash,
			$set_data = [],
			$set_preg_data = [],
			$set_preg_c_data = [],
			
			$assign_array = [],
			$assign_array2 = [];
		
		function __construct ($mash) {
			
			$this->test = get_class ().' подключен.<br/>';
			$this->timeStart = timer_start ();
			
			$this->mash = $mash;
			
		}
		
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		// Функции загрузки
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
		function load ($tpl_name = 'content', $debug = 0) {
			
			$tpl_target = $this->get_file ($tpl_name);
			
			if (!$tpl_name or !$tpl_target or !file_exists ($tpl_target))
				throw new MashException ($tpl_name.' not found');
			
			if ($debug == 1 or $this->debug == 1) debug ($tpl_target);
			
			$this_timeStart = timer_start ();
			
			$this->template = file_get_contents ($tpl_target);
			//$this->template = $this->_parse_tags ($this->template);
			
			$this->copy = $this->template;
			
			$this->parse_time += timer_stop ($this_timeStart);
			
		}
		
		function _load ($tpl_name, $debug = 0) {
			
			$tpl_target = $this->get_file (strip_quotes ($tpl_name));
			
			$tpl_name = strip_quotes ($tpl_name);
			
			if (!$tpl_name or !file_exists ($tpl_target))
				return '<b>'.$tpl_name.'</b> not found';
			
			if ($debug == 1 or $this->debug == 1) debug ($tpl_target);
			
			$content = file_get_contents ($tpl_target);
			
			if ($debug == 2 or $this->debug == 2)
				debug_html ($content);
			
			return $content;
			
		}
		
		private function _parse_tags ($content) {
			
			$data = array (
				
				'~\[foreach=(.+?)\](.*?)\[/foreach\]~ies' => '\$this->_foreach ("\\1", "\\2")',
				
			);
			
			foreach ($data as $find => $replace)
			$content = preg_replace ($find, $replace, $content);
			
			return $content;
			
		}
		
		private function _foreach ($options, $content) {
			
			$options = strip_quotes ($options);
			$options = explode_options ($options);
			
			$output = '';
			
			for ($i = 0; $i <= $options['limit']; ++$i) {
				
				$content = preg_replace ('~{(.*?)\['.$options['name'].'\]}~ies', '\$this->assign_array["\\1"]['.$i.']', $content);
				
				$output .= $content;
				
			}
			
			return $output;
			
		}
		
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		// Функции замены
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
		function assign ($find, $replace) {
			$this->assign_array[$find] = $replace;
		}
		
		private function _set ($find, $replace) {
			
			if ($this->debug == 3) debug (array ($find, spech_encode ($replace)));
			
			if (is_array ($replace) and count ($replace)) {
				
				foreach ($replace as $key => $value)
				$this->set ($key, $value);
				
			} else $this->set_data[$find] = $replace;
			
		}
		
		private function _set_preg ($find, $replace) {
			
			if (is_array ($replace) and count ($replace)) {
				
				foreach ($replace as $key => $value)
				$this->set_preg ($key, $value);
				
			} else $this->set_preg_data[$find] = $replace;
			
		}
		
		private function _set_preg_c ($find, $replace) {
			
			if (is_array ($replace) and count ($replace)) {
				
				foreach ($replace as $key => $value)
				$this->set_preg_c ($key, $value);
				
			} else $this->set_preg_c_data[$find] = $replace;
			
		}
		
		function set ($find, $replace = '') {
			if ($find) $this->_set ('{'.$find.'}', $replace);
		}
		
		function set2 ($find, $replace = '') {
			if ($find and $find != '/') $this->_set ('['.$find.']', $replace);
		}
		
		function preg ($find) {
			return '~\['.$find.'\](.*?)\[/'.$find.'\]~si';
		}
		
		function set_preg ($find, $replace = '') {
			if ($find) $this->_set_preg ($this->preg ($find), $replace);
		}
		
		function set_preg_c ($find, $replace) {
			if ($find) $this->_set_preg_c ($find, $replace);
		}
		
		function set_preg_c_preg ($find, $replace = '') {
			if ($find) $this->set_preg_c ($this->preg ($find), $replace);
		}
		
		function set_preg_c_preg2 ($find, $replace = '') {
			if ($find) $this->set_preg_c ('['.$find.'=(.*)\](.*?)\[/'.$find.'\]', $replace);
		}
		
		function set_strip ($name) {
			
			$this->set2 ($name);
			$this->set2 ('/'.$name);
			
		}
		
		function set_cond ($cond, $name, $name2 = '', $content = '') {
			
			if ($cond) {
				
				$this->set_strip ($name);
				$this->set_preg ($name2, $content);
				
			} else {
				
				$this->set_strip ($name2);
				$this->set_preg ($name, $content);
				
			}
			
		}
		
		function set_cond2 ($cond, $name, $content = '') {
			if ($cond) $this->set_strip ($name); else $this->set_preg ($name, $content);
		}
		
		function set_tag ($cond, $name, $content) {
			if ($cond) $this->set ($name, $content); else $this->set ($name);
		}
		
		function set_link ($name, $link) {
			
			$this->set2 ($name, '<a href="'.$link.'">');
			$this->set2 ('/'.$name, '</a>');
			
		}
		
		function set_aviable ($name, $value) {
			
			if ($value)
			$this->_set_preg ('~\[aviable=[\'\"]{0,1}'.$name.'[\'\"]{0,1}\](.*?)\[/aviable\]~si', '\\1');
			else
			$this->_set_preg ('~\[aviable=[\'\"]{0,1}'.$name.'[\'\"]{0,1}\](.*?)\[/aviable\]~si', '');
			
		}
		
		function set_preg_old ($find, $replace = '') {
			if ($find) $this->_set_preg ($find, $replace);
		}
		
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		// Функции компиляции
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
		function compile ($templ = 'content', $debug = 0) {
			
			$timeStart = timer_start (1);
			
			foreach ($this->set_data as $find => $replace)
				$this->copy = str_replace ($find, $replace, $this->copy);
			
			foreach ($this->set_preg_data as $find => $replace)
			$this->copy = preg_replace ($find, $replace, $this->copy);
			
			foreach ($this->set_preg_c_data as $find => $replace)
			$this->copy = preg_replace_callback ($find, $replace, $this->copy);
			
			if (isset ($this->result[$templ]))
			$this->result[$templ] .= $this->copy;
			else
			$this->result[$templ] = $this->copy;
			
			if ($debug) print_r ($this->result);
			
			$this->_clear ();
			$this->parse_time += timer_stop ($timeStart);
			
		}
		
		function assign_compile ($name) {
			$this->assign_array2[$name] = $this->assign_array;
		}
		
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		// Функции очистки
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
		function _clear () {
			
			$this->set_data = [];
			$this->set_preg_data = [];
			
			$this->copy = $this->template;
			
		}
		
		function clear () {
			
			$this->set_data = [];
			$this->set_preg_data = [];
			$this->set_preg_c_data = [];
			
			$this->copy = null;
			$this->template = null;
			
		}
		
		function global_clear () {
			
			$this->set_data = [];
			$this->set_preg_data = [];
			$this->set_preg_c_data = [];
			
			$this->result = [];
			$this->copy = null;
			$this->template = null;
			
		}
		
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		// Служебные функции
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
		function get_file ($tpl_name = false, $no_exp = 0, $debug = 0) {
			
			if ($tpl_name and $no_exp)
				$name = '/'.$tpl_name;
			elseif ($tpl_name and !$no_exp)
				$name = '/'.$tpl_name.'.'.$this->exp;
			else
				$name = '';
			
			if ($this->options['full']) {
				
				if ($tpl_name) {
					
					$a = [
						
						//SITE_DIR.'/templates/names/'.$this->name.$name,
						$this->dir.'/'.$this->name.$name,
						//SITE_DIR.'/templates'.$name,
						$this->dir.$name,
						$this->dir.'/'.$this->mod.$name,
						$this->dir.'/'.$this->name.'/'.$this->mod.$name,
						$this->dir.'/Default/'.$name,
						
					];
					
					foreach ($a as $t_id => $t_path)
					if (file_exists ($t_path)) {
						
						$file = $t_path;
						break;
						
					}
					
					if ($debug) debug ($file);
					
				} else $file = $this->dir;
				
			} else $file = $this->dir.'/'.$this->name.$name;
			
			$file = dash_filepath ($file);
			if ($this->debug == 1 or $debug) debug ($file);
			
			return $file;
			
		}
		
		function found ($what) {
			return (strpos ($this->copy, $what) !== false);
		}
		
		function encode ($value, $debug = 0) {
			
			$value = spech_encode (stripslashes ($value));
			
			$find = ['&#123;THEME&#125;', '&#123;HOME_URL&#125;'];
			$replace = ['{THEME}', '{HOME_URL}'];
			
			$str = str_replace ($find, $replace, $value);
			
			$find = array ('#\&\#123\;link\=\'(.*?)\'\&\#125\;#si', '#\&\#123\;admin_link\=\'(.*?)\'\&\#125\;#si');
			$replace = ['{link=\'\\1\'}', '{admin_link=\'\\1\'}']; // TODO
			
			$str = preg_replace ($find, $replace, $str);
			
			if ($debug) debug ($value);
			
			return $value;
			
		}
		
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		// Функции готовых частей
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
		function message ($message, $link_back = 0, $link_name = 1, $type = 'message', $br = 1, $lisas_nl2br = 0, $type2 = '') {
			global $lang; // TODO
			
			if (is_array ($message)) $message = mess2br ($message);
			if ($lisas_nl2br) $message = parse_debug_mess ($message);
			
			$bs_messages = [
				
				'error' => 'alert-danger',
				'message' => 'alert-warning',
				
			];
			
			if ($this->options['mess_full']) {
				
				$this->load ('message');
				
				$this->set ('message', $message);
				$this->set ('title', 'Сообщение');
				$this->set ('type', $bs_messages[$type]);
				
				if ($link_back) {
					
					$this->set2 ('back', '');
					$this->set2 ('/back', '');
					
					if ($link_name == 2 or ($link_back == 2 and $link_name == 1)) $link_name = $lang['link_main'];
					elseif ($link_name == 1) $link_name = $lang['link_back'];
					
					$this->set ('link_name', $link_name);
					
					if ($link_back == 1) $link_back = $_SERVER['HTTP_REFERER'];
					elseif ($link_back == 2) $link_back = HOME_URL;
					
					$this->set ('link_back', $link_back);
					
				} else {
					
					$this->set_preg ('back');
					
					$this->set ('link_back');
					$this->set ('link_name');
					
				}
				
				if ($type2) {
					
					$this->compile ($type);
					return $this->result[$type];
					
				} else $this->compile ();
				
				$this->clear ();
				
			} else echo $message;
			
		}
		
		function sql_message ($message) {
			
			if (!$this->options['not_show_queries'])
			$this->message ($message, 0, 0, 'info', 1, 1);
			
		}
		
		function error ($message, $code = 0, $error = '', $type = '') {
			
			if ($error)
			$this->mash->auth->email_mess ('error', 'webmaster', $error);
			
			if ($code > 0) send_code ($code);
			
			return $this->message ($message, 0, 0, 'error', 1, 0, $type);
			
		}
		
		function echo_message ($message, $type = '') {
			return $this->message ($message, 0, 0, '', 1, 0, $type);
		}
		
		function echo_sql_message ($message, $type = '') {
			return $this->message ($message, 0, 0, '', 1, 1, $type);
		}
		
		function preg2 ($type, $key) {
			
			switch ($type) {
				
				case 'hide': $output = '~\['.$key.'\](.*?)\[/'.$key.'\]~'; break;
				case 'hide_opt': $output = '~\['.$key.'=(.+?)\](.*?)\[/'.$key.'\]~'; break;
				case 'tag_opt': $output = '~\{'.$key.'=(.+?)\}~'; break;
				
			}
			
			return $output;
			
		}
		
	}