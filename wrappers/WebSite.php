<?php
	
	namespace Mash;
	
	require 'Service.php';
	
	abstract class WebSite extends Service {
		
		public $nav = [], $data = [], $fileTypes = [], $http_dir = [], $site = ['id' => 1], $mirror = [], $site_offline = false, $api_config, $templ, $mod, $modTypes, $load, $moduleTable = 'stories', $mod_config = [], $mod_lang = [], $style_settings = [], $header_settings = [], $footer_settings = [], $no_nav_mods = [], $scripts = '', $safe_mode = false, $module, $modulesData = [], $is_rss = false, $localSites = ['127.0.0.1'], $dir_prefix, $modules_dir;
		public $tpl, $auth;
		
		protected $repl = [], $repl_opt = [], $repl_opt_tag = [];
		
		private $sites = [], $mirrors = [];
		
		function onInit () {
			
			parent::onInit ();
			
			require $this->loadMashFile (['files', 'config']);
			
			define ('SITE_DIR', $this->loadDir ('sites'));
			
			$this->safe_mode = @ini_get ('safe_mode');
			$this->dir_prefix = date ('Y_m');
			
			/*if (in_array ($_SERVER['SERVER_ADDR'], $this->localSites)) {
				
				$this->debug = 1;
				//$this->errorsReportType = E_ALL ^ E_NOTICE;
				
			}*/
			
			$this->tpl = new \Templ ($this);
			$this->auth = new \Auth ($this);
			
			$this->mod = $this->getModule ();
			if (!$this->mod) $this->mod = $this->getMainModule ();
			
			$this->tpl->dir = $this->loadDir ('templates');
			$this->tpl->mod = $this->mod;
			$this->tpl->name = $this->config['template'];
			
			if (is_isset ($this->mod, $this->configs))
				$this->mod_config = $this->configs[$this->mod];
			
			$lang_file = $this->loadFile (['langs', 'modules', $this->mod]);
			
			if (file_exists ($lang_file)) {
				
				require $lang_file;
				$this->mod_lang[$this->mod] = $lang;
				
			}
			
		}
		
		protected function getModule () {
			return $this->data['mod'];
		}
		
		protected function loadModule (string $mod) {
			
			$load = false;
			
			$modules_dir = $this->loadDir ('modules');
			
			if (file_exists ($modules_dir.'/'.$mod.'/'.$mod.'.php')) {
				
				require $modules_dir.'/'.$mod.'/'.$mod.'.php';
				$load = true;
				
			} elseif (file_exists ($modules_dir.'/'.$mod.'.php')) {
				
				require $modules_dir.'/'.$mod.'.php';
				$load = true;
				
			}
			
			return $load;
			
		}
		
		function onShow () {
			
			if ($this->loadModule ($this->mod)) {
				
				require $this->loadMashFile (['files', 'pastload']);
				
				$this->module->onShow ();
				
				require $this->loadMashFile (['files', 'show.main']);
				require $this->loadMashFile (['files', 'main.page.tags']);
				
				$this->tpl->compile ('main');
				
				//$this->onMain ();
				
				require $this->loadMashFile (['files', 'main.page']);
				
				$this->tpl->compile ('main2');
				
				return $this->parse_global_tags ($this->tpl->result['main2']);
				
			} else return $this->tpl_error (404);
			
		}
		
		protected function sites () {
			return true;
		}
		
		protected function modules () {
			return true;
		}
		
		function getModulesDir () {
			return $this->loadDir ('modules');
		}
		
		function getModuleDir () {
			return $this->loadDir (['modules', $this->mod]);
		}
		
		function getMainModule () {
			return '';
		}
		
		function isMainModule () {
			return $this->mod == $this->getMainModule ();
		}
		
		function is_this_mod ($mod) {
			return ($mod == $this->mod or $mod == $this->module->getType () or in_array ($mod, $this->modTypes));
		}
		
		function modulesTypes () {
			
			$output = [];
			foreach ($this->modulesData as $data)
			$output[$data['type']] = $data;
			
			return $output;
			
		}
		
		private function mod_status ($mod, $content) {
			
			$option = $this->explode_options ($mod);
			if (!$option[1]) $option[1] = 'approve';
			
			if (!$this->modulesData[$option[0]][$option[1]]) $content = '';
			return stripslashes ($content);
			
		}
		
		private function comp_status ($mod, $content) {
			
			$option = $this->explode_options ($mod);
			if (!$option[1]) $option[1] = 'approve';
			
			if (!$comp[$option[0]][$option[1]]) $content = '';
			return stripslashes ($content);
			
		}
		
		function config ($mod, $key) {
			return (isset ($this->configs[$mod][$key]) ? $this->config[$mod][$key] : null);
		}
		
		function mod_config ($key) {
			return (is_isset ($key, $this->mod_config) ? $this->mod_config[$key] : null);
		}
		
		function mod_lang ($key) {
			return (is_isset ($key, $this->mod_lang) ? $this->mod_lang[$key] : '');
		}
		
		function isMainMod () {
			return ($this->mod == $this->getMainModule ());
		}
		
		function site_urls () {
			
			$row = $this->db->serialize_query ('SELECT * FROM '.$this->db->table ('sites'));
			
			$output = [];
			foreach ($row as $row2) $output[] = $row2['url'];
			
			return $output;
			
		}
		
		function site_dirs () {
			
			$output = [];
			foreach ($this->site_urls () as $site_url) $output[] = $this->getRootDir ().'/sites/'.$site_url;
			return $output;
			
		}
		
		function lang_date ($format, $stamp = false) {
			
			if (!$stamp) $stamp = $this->server['time'];
			return lang_date ($format, $stamp, $this->auth->lang_date);
			
		}
		
		function var_lang ($match, $file = 0) {
			
			$content = strip_quotes ($match);
			$content = explode ('|', $content);
			
			$output = '';
			
			if ($file) {
				
				require $this->loadDir ('langs').'/'.$content[0].'.php';
				$output = $lang[$content[1]];
				
			} else {
				
					
				if ($content[0] == $this->mod_tag) $output = $mod_lang[$content[1]];
				elseif (!$content[1]) $output = $lang[$content[0]];
				
			}
			
			return $output;
			
		}
		
		function explode_options ($str) {
			return explode ('|', strip_quotes ($str));
		}
		
		function parse_date ($date) {
			
			$option = $this->explode_options ($date);
			
			if (not_empty ($option[1]))
			$date = $this->lang_date ($option[0], strtotime ($option[1]));
			else
			$date = $this->lang_date ($option[0]);
			
			return $date;
			
		}
		
		function year_copyright ($year, $formatted = 0) {
			
			if ($this->date->show ('Y') > $year) {
				
				if ($formatted)
					$year .= '<span class="mdash">&mdash;</span>';
				else
					$year .= '-';
				
				$year .= $this->date->show ('Y');
				
			}
			
			return $year;
			
		}
		
		function loadComponent ($comp_type, $comp_name, $content = '', $include_file = 'php') {
			
			if (!$this->allow_php_include) return 'Подключение компонентов запрещено!';
			
			$content = strip_quotes ($content);
			$option = $this->explode_options ($comp_name);
			
			$comp_name = $option[0];
			
			$error = [];
			
			if ($comp_type == 'snippet_tag' or $comp_type == 'snippet_area')
			$dir_name = $this->loadDir ('system').'/snippets';
			else
			$dir_name = $this->loadDir ('components');
			
			$file_name = $dir_name.'/'.$comp_name.'.'.$include_file;
			$dir_file_name = $dir_name.'/'.$comp_name.'/'.$comp_name.'.'.$include_file;
			
			if (get_chmod ($dir_name.'/'.$comp_name) == 0777)
			return 'Файл '.$file_name.' находится в папке, которая доступна для записи (CHMOD 777). В целях безопасности подключение файлов из таких папок невозможно. Измените права на папку.';
			
			if ($comp_type == 'component' or $comp_type == 'snippet_tag') ob_start ();
			
			if (!file_exists ($file_name) and !file_exists ($dir_file_name))
			return 'Компонент <b>'.$comp_name.'</b> не найден.';
			elseif (file_exists ($dir_file_name)) require $dir_file_name;
			else require $file_name;
			
			$content = '';
			
			if ($comp_type == 'component' or $comp_type == 'snippet_tag')
				$content = ob_get_clean ();
			
			return $content;
			
		}
		
		function parse_global_tags ($content, $full_parse = 0, $stripslashes = 0) {
			
			// \$content = preg_replace_callback \('~\\\[(.+?)=\(\.\+\?\)\\\]\(\.\*\?\)\\\[/(.+?)\\\]~is', function \(\$match\)(.+?), \$content\);
			// \$this->repl_opt\['\1'\] = function \(\$match\)\3;
			
			// \$content = preg_replace_callback \('~\\\[(.+?)\\\]\(\.\*\?\)\\\[/(.+?)\\\]~is', function \(\$match\)(.+?), \$content\);
			// \$this->repl\['\1'\] = function \(\$match\)\3;
			
			// \$content = preg_replace_callback \('~\\\{(.+?)=\(\.\+\?\)\\\}~is', function \(\$match\)(.+?), \$content\);
			// \$this->repl_opt_tag\['\1'\] = function \(\$match\)\2;
			
			$content = str_replace ('{THEME}', $this->http_dir['theme'], $content);
			$content = str_replace ('{THEME_ADMIN}', $this->http_dir['admin_theme'], $content);
			
			if (strpos ($content, '{template=') !== false)
			$this->repl_opt_tag['template'] = function ($match) {
				return $this->tpl->_load ($match[1]);
			};
			
			if ($this->auth->is_logged) {
				
				$content = str_replace ('[logged]', '', $content);
				$content = str_replace ('[/logged]', '', $content);
				$content = preg_replace ('~\[not_logged\](.*?)\[/not_logged\]~si', '', $content);
				
				$content = str_replace ('[logged2]', '', $content);
				$content = str_replace ('[/logged2]', '', $content);
				$content = preg_replace ('~\[not_logged2\](.*?)\[/not_logged2\]~si', '', $content);
				
			} else {
				
				$content = str_replace ('[not_logged]', '', $content);
				$content = str_replace ('[/not_logged]', '', $content);
				$content = preg_replace ('~\[logged\](.*?)\[/logged\]~si', '', $content);
				
				$content = str_replace ('[not_logged2]', '', $content);
				$content = str_replace ('[/not_logged2]', '', $content);
				$content = preg_replace ('~\[logged2\](.*?)\[/logged2\]~si', '', $content);
				
			}
			
			$content = str_replace ('{SOURCES}', $this->http_dir['sources'], $content);
			//$content = str_replace ('{MBUTTONS}', $this->http_dir['sources'].'/mbuttons/'.$this->config['mbuttons'], $content);
			//$content = str_replace ('{FRICONS}', $this->http_dir['sources'].'/fricons/'.$forum_config['fricons'], $content);
			
			$content = str_replace ('{HOME_URL}', HOME_URL, $content);
			//$content = str_replace ('{do_mod}', $do_mod, $content);
			
			if (strpos ($content, '[group=') !== false)
			$this->repl_opt['group'] = function ($match) {
				return $this->check_group ($match[1], $match[2], 1);
			};
			
			if (strpos ($content, '[not_group=') !== false)
			$this->repl_opt['not_group'] = function ($match) {
				return $this->check_group ($match[1], $match[2], 0);
			};
			
			if (strpos ($content, '{link=') !== false)
			$this->repl_opt_tag['link'] = function ($match) {
				return $this->link ($match[1]);
			};
			
			if (strpos ($content, '{date=') !== false)
			$this->repl_opt_tag['date'] = function ($match) {
				return $this->parse_date ($match[1]);
			};
			
			if (strpos ($content, '[config=') !== false)
			$this->repl_opt['config'] = function ($match) {
				return $this->hide_config ('general', $match[1], $match[2]);
			};
			
			if (strpos ($content, '[mod_config=') !== false)
			$this->repl_opt['mod_config'] = function ($match) {
				return $this->hide_config ('module', $match[1], $match[2]);
			};
			
			if (strpos ($content, '[users_config=') !== false)
			$this->repl_opt['users_config'] = function ($match) {
				return $this->hide_config ('users', $match[1], $match[2]);
			};
			
			if (strpos ($content, '{config=') !== false)
			$this->repl_opt_tag['config'] = function ($match) {
				return $this->var_config ('general', $match[1]);
			};
			
			if (strpos ($content, '{mod_config=') !== false)
			$this->repl_opt_tag['mod_config'] = function ($match) {
				return $this->var_config ('module', $match[1]);
			};
			
			if (strpos ($content, '{users_config=') !== false)
			$this->repl_opt_tag['users_config'] = function ($match) {
				return $this->var_config ('users', $match[1]);
			};
			
			if (strpos ($content, '[config_status=') !== false)
			$this->repl_opt['config_status'] = function ($match) {
				return $this->config_status ('general', $match[1], $match[2]);
			};
			
			if (strpos ($content, '[mod_config_status=') !== false)
			$this->repl_opt['mod_config_status'] = function ($match) {
				return $this->config_status ('module', $match[1], $match[2]);
			};
			
			if (strpos ($content, '[users_config_status=') !== false)
			$this->repl_opt['users_config_status'] = function ($match) {
				return $this->config_status ('users', $match[1], $match[2]);
			};
			
			if (strpos ($content, '{lang_file=') !== false)
			$this->repl_opt_tag['lang_file'] = function ($match) {
				return $this->var_lang ($match[1], 1);
			};
			
			$content = str_replace ('{mod_title}', $this->module->getTitle (), $content);
			
			if (strpos ($content, '[str_cut=') !== false)
			$this->repl_opt['str_cut'] = function ($match) {
				return $this->str_cut ($match[1], $match[2]);
			};
			
			if (is_isset ('rss_stories', $this->mod_config)) { // TODO
				
				$content = str_replace ('[rss]', '', $content);
				$content = str_replace ('[/rss]', '', $content);
				
			} else $content = preg_replace ("'\[rss\](.*?)\[/rss\]'si", "", $content);
			
			if (strpos ($content, '{user=') !== false)
			$this->repl_opt_tag['user'] = function ($match) {
				return $this->user_data ($match[1]);
			};
			
			if (strpos ($content, '{email=') !== false)
			$this->repl_opt_tag['email'] = function ($match) {
				return $this->build->email ($match[1]);
			};
			
			if (strpos ($content, '[group_right=') !== false)
			$this->repl_opt['group_right'] = function ($match) {
				return $this->check_group_rights ($match[1], $match[2], 1);
			};
			
			if (strpos ($content, '[not_group_right=') !== false)
			$this->repl_opt['not_group_right'] = function ($match) {
				return $this->check_group_rights ($match[1], $match[2], 0);
			};
			
			if (strpos ($content, '[mod_status=') !== false)
			$this->repl_opt['mod_status'] = function ($match) {
				return $this->mod_status ($match[1], $match[2]);
			};
			
			if ($full_parse) {
				
				if (strpos ($content, '{banner=') !== false)
				$this->repl_opt_tag['banner'] = function ($match) {
					return $this->build->banner ($match[1]);
				};
				
				//if (strpos ($content, '{static=') !== false)
				//$this->repl_opt_tag['static'] = function ($match) {
				//	return $this->build->static_page ($match[1]);
				//};
				
				if ($torrent_config['status'] == 'on') {
					
					$content = str_replace ('[torrent]', '', $content);
					$content = str_replace ('[/torrent]', '', $content);
					
				} else $content = preg_replace ("'\[torrent\](.*?)\[/torrent\]'si", "", $content);
				
				if (strpos ($content, '{component=') !== false)
				$this->repl_opt_tag['component'] = function ($match) {
					return $this->load_component ('component', $match[1]);
				};
				
				if (strpos ($content, '[snippet=') !== false)
				$this->repl_opt['snippet'] = function ($match) {
					return $this->load_component ('snippet_area', $match[1], $match[2]);
				};
				
				if (strpos ($content, '{snippet=') !== false)
				$this->repl_opt_tag['snippet'] = function ($match) {
					return $this->load_component ('snippet_tag', $match[1]);
				};
				
				if (strpos ($content, '[comp_status=') !== false)
				$this->repl_opt['comp_status'] = function ($match) {
					return $this->comp_status ($match[1], $match[2]);
				};
				
			}
			
			if ($this->data['is_plugin']) {
				
				$content = str_replace ('[plugin]', '', $content); $content = str_replace ('[/plugin]', '', $content);
				$content = preg_replace ("'\[not_plugin\](.*?)\[/not_plugin\]'si", '', $content);
				
			} else {
				
				$content = str_replace ('[not_plugin]', '', $content); $content = str_replace ('[/not_plugin]', '', $content);
				$content = preg_replace ("'\[plugin\](.*?)\[/plugin\]'si", '', $content);
				
			}
			
			if (strpos ($content, '{lang=') !== false)
			$this->repl_opt_tag['lang'] = function ($match) {
				
				$match[1] = strip_quotes ($match[1]);
				
				if (is_numeric ($match[1]))
					$output = $this->lang ($match[1]);
				else
					$output = $this->var_lang ($match[1]);
				
				return $output;
				
			};
			
			$content = str_replace ('{year_copyright}', $this->year_copyright ($this->config['year_open'], 1), $content);
			
			foreach ($this->repl as $key => $value)
			$content = preg_replace_callback ($this->tpl->preg2 ('hide', $key).'si', $value, $content);
			
			foreach ($this->repl_opt as $key => $value)
			$content = preg_replace_callback ($this->tpl->preg2 ('hide_opt', $key).'si', $value, $content);
			
			foreach ($this->repl_opt_tag as $key => $value)
			$content = preg_replace_callback ($this->tpl->preg2 ('tag_opt', $key).'si', $value, $content);
			
			if ($stripslashes) $content = stripslashes ($content);
			
			return $content;
			
		}
		
		function __destruct () {
			
			if ($this->tpl) $this->tpl->global_clear ();
			
		}
		
		function minifierParams ($params) {
			return w3c_encode (http_build_fquery ($params, 1, [], false));
		}
		
		final function addModule (Module $module) {
			$this->module = $module;
		}
		
		function tpl_error ($code) {
			
			if ($this->mod != 'error') {
				
				$this->mod = 'error';
				$this->data['id'] = $code;
				
				return $this->onShow ();
				
			}
			
		}
		
		function getAdminPanel (WebSite $mash): ?AdminPanel {
			return null;
		}
		
		public $textColors = [
			
			self::MESS_TEXT_COLOR_BLACK => 'black',
			self::MESS_TEXT_COLOR_RED => 'red',
			self::MESS_TEXT_COLOR_GREEN => 'green',
			self::MESS_TEXT_COLOR_YELLOW => 'yellow',
			self::MESS_TEXT_COLOR_BLUE => 'blue',
			
		];
		
		function println ($text = '', $text_color = self::MESS_TEXT_COLOR_DEFAULT, $bg_color = self::MESS_BG_COLOR_BLACK, $text_type = self::MESS_TEXT_TYPE_INTENSIVE) {
			if ($text) $this->print ($text.BR, $text_color, $bg_color, $text_type);
		}
		
		function print ($text, $text_color = self::MESS_TEXT_COLOR_GRAY, $bg_color = self::MESS_BG_COLOR_BLACK, $text_type = self::MESS_TEXT_TYPE_INTENSIVE) {
			
			$output = '<span style="color:';
			
			if (isset ($this->textColors[$text_color]))
				$output .= $this->textColors[$text_color];
			else
				$output .= $this->textColors[self::MESS_TEXT_COLOR_BLACK];
			
			$output .= '; whitespace:nowrap;">'.$text.'</span>';
			
			echo $output;
			
			flush ();
			ob_flush ();
			
		}
		
		function printr ($text = '', $text_color = self::MESS_TEXT_COLOR_DEFAULT, $bg_color = self::MESS_BG_COLOR_BLACK, $text_type = self::MESS_TEXT_TYPE_INTENSIVE) {
			$this->print ($text.BR, $text_color, $bg_color, $text_type);
		}
		
	}