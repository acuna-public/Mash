<?php
	
	class Locale {
		
		public
			$lang_code = 'en',
			$def_lang = 'en',
			$lang_var = 'lang',
			$lang_locale = [],
			$client_lang = 'en',
			$charset = 'utf-8',
			$locale,
			$dirs = [],
			$lang_date = [];
		
		private
			$mash,
			$lang = [];
		
		function __construct (Mash $mash) {
			
			$this->mash = $mash;
			
			if (!defined ('CLI') and isset ($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
				
				$client_lang = explode ('-', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
				$this->client_lang = strtolower ($client_lang[0]);
				
			}
			
			if (is_isset ($this->lang_var, $_COOKIE))
				$this->lang_code = $_COOKIE[$this->lang_var];
			elseif (!defined ('CLI') and is_isset ($this->lang_var, $_SESSION))
				$this->lang_code = $_SESSION[$this->lang_var];
			elseif ($this->client_lang)
				$this->lang_code = $this->client_lang;
			elseif ($this->mash->config['language'])
				$this->lang_code = $this->mash->config['language'];
			elseif ($this->mash->data['lang'])
				$this->lang_code = $this->mash->data['lang'];
			
			$this->lang_code = sep_explode ($this->lang_code);
			$this->lang_code = $this->lang_code[0];
			
			if (file_exists ($this->mash->loadDir ('langs').'/localization/'.$this->lang_code.'.lng'))
				$this->lang_words ($this->lang_code); // TODO
			
			if (file_exists ($this->lang_path ($this->lang_code, 'locale')))
				$this->lang_words ($this->lang_code, 'locale');
			else
				$this->lang_words ('en', 'locale');
			
			if (file_exists ($this->mash->loadDir ('langs').'/site.php'))
				require $this->mash->loadDir ('langs').'/site.php';
			
			require $this->mash->loadMashFile (['files', 'locale']);
			
			if ($this->mash->server['sub_domain'] and in_array ($this->mash->server['sub_domain'], array_keys ($this->locale['langs']['code']['locale'])))
				$this->lang_code = $this->mash->server['sub_domain'];
			
			if ($this->mash->config['charset'])
				$this->charset = $this->mash->config['charset'];
			
		}
		
		function lang ($id) {
			return $this->lang[$id];
		}
		
		function lang_locale ($id) {
			return $this->lang_locale[$id];
		}
		
		private function lang_path ($code, $type = '') {
			
			if ($type) $code = $code.'.'.$type;
			
			if ($type == 'locale')
			$path = $this->mash->getMashDir ('langs').'/'.$code.'.lng';
			else
			$path = $this->mash->loadDir ('langs').'/localization/'.$code.'.lng';
			
			return $path;
			
		}
		
		function translate_files ($type, $files = [], $file_type = '') {
			
			if (!$files)
			$files = dir_scan ($this->dirs['templates'], ['recursive' => 1, 'allow_types' => 'htm']);
			
			if ($type == 'html')
			$expr = '([\'">])([\p{Cyrillic} \.\,\-\(\)\!]{2,}?)([\'"<\[])';
			elseif ($type == 'php')
			$expr = '(>)([\p{Cyrillic} \.\,\-\(\)\!]{2,}?)(</)';
			elseif ($type == 'js') {
				
				if ($file_type == 'locale')
				$expr = '=> ([\'])([0-9\p{Cyrillic} \.\,\-\+\(\)\!\:]{2,}?)([\'])';
				else
				$expr = '([\'])([\p{Cyrillic} \.\,\-\(\)\!\?]{2,}?)([\'])\,';
				
			} elseif ($type == 'db')
			$expr = '~(title)<>([\p{Cyrillic} \.\,\-\(\)\!\?]{2,}?)\|~iu';
			
			$deny_words = ['Facebook', 'Skype', 'ICQ', 'Twitter', 'Last.fm', 'Instagram', 'Tumblr', 'GitHub', 'ВКонтакте', 'Одноклассники', 'Soundarea'];
			
			foreach ($files as $file) {
				
				$content = file_get_content ($file);
				
				if ($type != 'db') $expr = '~'.$expr.'~iu';
				$content = preg_replace_callback ($expr, function ($match) use ($auth, $type, $file_type, $deny_words) {
					
					if (!is_numeric ($match[2]) and !in_array ($match[2], $deny_words)) {
						
						$i = $this->lang_words ($this->def_lang, $file_type);
						
						if ($file_type == 'locale')
						$old_i = array_search ($match[2], $this->lang_locale);
						else
						$old_i = array_search ($match[2], $this->lang);
						
						if ($old_i === false) {
							++$i;
							
							$handle = fopen ($this->lang_path ($this->def_lang, $file_type), 'a');
							
							if (fwrite ($handle, $match[2].NL))
							echo '<div style="color:green;">Слово '.$match[2].' успешно добавлено.</div>
';
							else
							echo '<div style="color:red;">Ошибка при добавлении слова '.$match[2].'.</div>
';
							
							fclose ($handle);
							
						} else $i = $old_i;
						
						if ($type == 'html')
						$output = $match[1].'{lang=\''.$i.'\'}'.$match[3];
						elseif ($type == 'js') {
							
							if ($file_type == 'locale')
							$output = '=> $auth->lang_locale['.$i.']';
							else
							$output = '$auth->lang ('.$i.'),';
							
						} elseif ($type == 'php')
						$output = $match[1].'{$auth->lang ('.$i.')}'.$match[3];
						elseif ($type == 'db')
						$output = $match[1].'<>'.$i.'|';
						
					} elseif ($type == 'db')
					$output = $match[1].'<>'.$match[2].'|';
					else
					$output = $match[1].$match[2].$match[3];
					
					return $output;
					
				}, $content);
				
				file_put_content ($content, $file);
				
			}
			
		}
		
		private function _translate_word ($value, $code) {
			
			$value = str_replace (['<br/>'], ['<br>'], $value);
			
			$value = google_translate ($value, $this->def_lang, $code, $this->mash->config['api']['google'][2]['api_key']);
			
			if (!is_array ($value)) {
				
				$deny_langs = [];
				
				if (!in_array ($code, $deny_langs)) {
					
					$value = preg_replace_callback ('~\((.+?)\)~i', function ($match2) {
						return trim ('('.lisas_ucfirst ($match2[1]).')');
					}, $value);
					
				}
				
				echo '<div style="color:green;">Слово <b>'.$value.'</b> успешно переведено на '.$code.'.</div>
';
				
				$value = str_replace (['<br>'], ['<br/>'], $value);
				
			}
			
			return $value;
			
		}
		
		private function _lang_words ($code, $type = '') {
			
			$words = file2array ($this->lang_path ($code, $type));
			
			$lang = [];
			foreach ($words as $word)
			$lang[] = trim ($word);
			
			return $lang;
			
		}
		
		private function lang_words ($lang, $type = '') {
			
			$i = 0;
			
			$lang = file2array ($this->lang_path ($lang, $type));
			
			foreach ($lang as $value) {
				++$i;
				
				if ($type == 'locale')
				$this->lang_locale[$i] = trim ($value);
				else
				$this->lang[$i] = trim ($value);
				
			}
			
			return $i;
			
		}
		
		private function __translate_id_word ($code, $id, $ru_lang, $en_lang, $type) {
			
			$i = $this->lang_words ($code, $type);
			
			foreach ($ru_lang as $key => $value) {
				
				$key = ($key + 1);
				
				if (in_array ($key, $id))
				$value = $this->_translate_word ($value, $code);
				else
				$value = $this->lang[$key];
				
				$this->_translate_word_action ($code, $key, $value, $type, $en_lang, $id);
				
			}
			
		}
		
		private function __translate_word ($code, $ru_lang, $en_lang, $type) {
			
			$i = $this->lang_words ($code, $type);
			
			foreach ($ru_lang as $key => $value)
			if (($key + 1) > $i) {
				
				$value = $this->_translate_word ($value, $code);
				$this->_translate_word_action ($code, $key, $value, $type, $en_lang);
				
			}
			
		}
		
		private function _translate_file ($code, $id, $ru_lang, $en_lang = [], $type = '') {
			
			if ($id)
			$this->__translate_id_word ($code, $id, $ru_lang, $en_lang, $type);
			else
			$this->__translate_word ($code, $ru_lang, $en_lang, $type);
			
		}
		
		function translate_file ($id = 0, $langs = [], $type = '') {
			
			$ru_lang = $this->_lang_words ($this->def_lang, $type);
			
			$this->_translate_file ('en', $id, $ru_lang, [], $type);
			$en_lang = $this->_lang_words ('en', $type);
			
			if (!$langs) $langs = $this->locale['langs']['code']['locale'];
			//$code = 'km';
			
			foreach ($langs as $code => $lang) {
				
				if (is_numeric ($code)) $code = $lang;
				
				if ($code != 'en' and $code != $this->def_lang)
				$this->_translate_file ($code, $id, $ru_lang, $en_lang, $type);
				
			}
			
			$this->lang_words ($this->lang_code);
			
		}
		
		private function _translate_word_action ($code, $key, $value, $type, $en_lang, $id = 0) {
			
			if ($value and !is_array ($value)) {
				
				if ($id) {
					if ($key == 1) $write_type = 'w'; else $write_type = 'a';
				} else $write_type = 'a';
				
				$handle = fopen ($this->lang_path ($code, $type), $write_type);
				
				if (!$id and array_search ($value, $en_lang) !== false) { // Не перевелось(
					
					$value = lisas_strtolower ($value);
					$value = $this->_translate_word ($value, $code);
					$value = lisas_ucfirst ($value);
					
				}
				
				if (!fwrite ($handle, $value.NL))
				/*echo '<div style="color:#999;">Слово '.$key.'. '.$value.' успешно добавлено на '.$code.'.</div>
';
				else*/
				echo '<div style="color:red;">Ошибка при переводе слова '.$key.'. '.$value.' на '.$code.'.</div>
';
				
				fclose ($handle);
				
			}
			
			return $value;
			
		}
		
		function translate_js () {
			
			require $this->mash->loadDir ('langs').'/js/site.php';
			
			$files = dir_scan ($this->mash->loadDir ('system').'/js', ['recursive' => 1, 'allow_types' => 'js']);
			
			foreach ($files as $file) {
				
				$content = file_get_content ($file);
				
				$content = preg_replace_callback ('~lang\[\'(.*?)\'\]~', function ($match) use ($js_lang) {
					return 'lang['.$js_lang[$match[1]].']';
				}, $content);
				
				file_put_content ($content, $file);
				
			}
			
		}
		
		function word_date ($date, $options = [], $debug = 0) {
			
			if (!($date instanceof Date))
				$date = new Date ($date);
			
			return $date->word ($this->lang_date, [$this->lang_locale (122), $this->lang_locale (123), $this->lang_locale (124)], $options, $debug);
			
		}
		
		function getLandCode ($name) {
			
			$code = array_search ($name, $this->locale['lands']['code']['locale']);
			
			if ($code === false) {
				
				foreach ($this->locale['lands']['code']['aliases'] as $code2 => $lands) {
					
					if (in_array ($name, $lands))
						return $code2;
					
				}
				
			}
			
			return $code;
			
		}
		
	}