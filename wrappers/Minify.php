<?php
/*
 ========================================
 Mash Framework (c) 2014-2020
 ----------------------------------------
 http://www.mash.github.io/
 ========================================
 O! Interactive (support@ointeractive.ru)
 ----------------------------------------
 -- Сжатие JS и CSS-файлов
 ========================================
*/
	
	namespace Mash;
	
	require __DIR__.'/../Mash.php';
	
	abstract class Minify extends \Mash {
		
		public
			$show_out = 0,
			$js_langs = [],
			$js = true,
			$cache_dir, 
			$hash = 'b5r36grt57';
			
		protected $files = [], $locale;
		
		protected function getArea () {
			return '';
		}
		
		function onInit () {
			
			if (!$this->debug and is_unleech ()) die ();
			
			parent::onInit ();
			
			$this->locale = new \Locale ($this);
			
			//'admin_templ' => $_GET['admin_templ'],
			
			if (is_isset ('templ', $_GET))
				$this->data['templ'] = get_filename (url_decode ($_GET['templ']));
			else
				$this->data['templ'] = '';
			
			foreach (['plugins'] as $key)
			if (is_isset ($key, $_GET))
				$this->data[$key] = sep_explode (url_decode ($_GET[$key]));
			else
				$this->data[$key] = [];
			
			foreach (['position', 'color'] as $key) {
				
				if (is_isset ($key, $_GET))
					$this->data[$key] = $_GET[$key];
				else
					$this->data[$key] = '';
				
			}
			
			foreach (['charset'] as $key) {
				
				if (is_isset ($key, $_GET))
					$this->data[$key] = url_decode ($_GET[$key]);
				else
					$this->data[$key] = '';
				
			}
			
			foreach (['font_size', 'global_tpl'] as $key)
			if (is_isset ($key, $_GET))
				$this->data[$key] = (int) $_GET[$key];
			else
				$this->data[$key] = 0;
			
			if (!$this->data['charset']) $this->data['charset'] = 'utf-8';
			
			if ($this->data['font_size'])
				$this->data['font_size'] = 2;
			else
				$this->data['font_size'] = 1;
			
		}
		
		function onShow (): string {
			
			$this->cache_dir = $this->getRootDir ().'/cache/minify';
			$cache_file = $this->minifyCacheFile ();
			
			if ($this->debug or !file_exists ($cache_file)) {
				
				$this->content = $this->getContent ();
				
				if (!$this->debug) {
					
					make_dir ($this->cache_dir);
					file_put_content ($this->content, $cache_file);
					
				}
				
			} else $this->content = file_get_contents ($cache_file);
			
			if ($this->data['type'] == 'js')
				$type = 'javascript';
			elseif ($this->data['type'] == 'css')
				$type = 'css';
			
			@header ('Content-type: text/'.$type.'; charset:'.$this->data['charset']);
			
			//echo timer_stop ();
			
			return $this->content;
			
		}
		
		protected function jsHeaderSystemDirs (): array {
			return ['jquery'];
		}
		
		private function jsHeaderSystem () {
			
			$options = ['allow_types' => 'js', 'files_only' => 1];
			
			$allowed_js = [];
			
			foreach ($allowed_js as $file) $this->files[] = $file;
			
			$denied_js = [];
			
			if ($this->js)
			foreach ($this->jsHeaderSystemDirs () as $dir) { // Main jQuery
				
				$dir_files = dir_scan ($this->getMashDir ('js/'.$dir), $options);
				
				if ($file = end ($dir_files) and !in_array ($file, $denied_js))
					$this->files[] = $file;
				
			}
			
			$this->files[] = $this->getMashDir ('js/init').'/headers.js';
			
		}
		
		private function jsFooterSystem () {
			
			$options = ['allow_types' => 'js', 'files_only' => 1];
			
			if (is_isset ('admin_templ', $this->data))	{
				
				foreach (dir_scan ($this->getMashDir ('js/admin'), $options) as $file)
					if (!in_array ($file, $denied_js)) $this->files[] = $file;
				
			}
			
			if ($this->debug) {
				
				$ui_dev_dir = $this->getMashDir ('js/jquery-ui-dev');
				
				$denied_js = ['version.js']; // First
				
				foreach ($denied_js as $file)
					$this->files[] = $ui_dev_dir.'/'.$file;
				
				$options = ['allow_types' => 'js', 'files_only' => 1, 'names_only' => 1];
				
				$denied_js[] = 'core.js';
				
				foreach (dir_scan ($ui_dev_dir, $options) as $file)
					if (!in_array ($file, $denied_js))
						$this->files[] = $ui_dev_dir.'/'.$file;
				
				$denied_js = ['widgets/mouse.js'];
				
				foreach ($denied_js as $file)
					if (file_exists ($ui_dev_dir.'/'.$file))
					$this->files[] = $ui_dev_dir.'/'.$file;
				
				foreach (['widgets', 'effects'] as $dir) {
					
					if (file_exists ($ui_dev_dir.'/'.$dir))
					foreach (dir_scan ($ui_dev_dir.'/'.$dir, $options) as $file)
						if (!in_array ($dir.'/'.$file, $denied_js))
							$this->files[] = $ui_dev_dir.'/'.$dir.'/'.$file;
					
				}
				
			} else {
				
				$denied_js = [];
				
				foreach (dir_scan ($this->getMashDir ('js/jquery-ui'), $options) as $file)
					if (!in_array ($file, $denied_js)) $this->files[] = $file;
				
			}
			
			$options = ['allow_types' => 'js', 'files_only' => 1];
			
			$allowed_js = [];
			$denied_js = [];
			
			$dir_files = dir_scan ($this->getMashDir ('js/preload'), $options);
			foreach ($dir_files as $file) $this->files[] = $file;
			
			if ($this->data['plugins']) {
				
				$dir_files = [];
				
				foreach ($this->data['plugins'] as $file) {
					
					$file2 = $this->getMashDir ('js/plugins').'/'.get_filename ($file, 1).'.js';
					
					if (!file_exists ($file2))
					$file2 = $this->getMashDir ('js/plugins/ointeractive').'/'.get_filename ($file, 1).'.js';
					
					$this->files[] = $file2;
					
				}
				
			} else {
				
				$dir_files = dir_scan ($this->getMashDir ('js/plugins'), $options); // Cканим папку с плагинами...
				foreach ($dir_files as $file) $this->files[] = $file;
				
				$dir_files = dir_scan ($this->getMashDir ('js/plugins/ointeractive'), $options + ['recursive' => 1]);
				foreach ($dir_files as $file) $this->files[] = $file;
				
			}
			
			$dir_files = dir_scan ($this->getMashDir ('js/past_load'), $options);
			foreach ($dir_files as $file) $this->files[] = $file;
			
			foreach ($this->jsFooterSystemDirs () as $folder) {
				
				$dir_files = dir_scan ($this->getMashDir ('js/'.$folder), $options);
				
				foreach ($dir_files as $file)
				if (!in_array ($file, $denied_js) and !in_array ($file, $allowed_js))
				$this->files[] = $file;
				
			}
			
			$this->files[] = $this->getMashDir ('js/init').'/footers.js';
			
		}
		
		protected function jsFooterSystemDirs (): array {
			return ['extensions', 'functions'];
		}
		
		private function jsHeaderTemplate () {
			$this->content .= $this->js_vars_lang ('lang');
		}
		
		private function jsFooterTemplate () {
			
			$allowed_js = [];
			$denied_js = [];
			
			$options = ['allow_types' => 'js', 'files_only' => 1];
			
			foreach (dir_scan ($this->loadDir ('js'), $options) as $file)
			if (!in_array ($file, $denied_js) and !in_array ($file, $allowed_js))
			$this->files[] = $file;
			
			if (file_exists ($this->templ_dir.'js'))
			foreach (dir_scan ($this->templ_dir.'js', $options) as $file) // Сканим папку шаблона
			if (!in_array ($file, $denied_js))
				$this->files[] = $file;
			
		}
		
		protected function cssTemplateDirs (): array {
			return ['style'];
		}
		
		private function cssTemplate () {
			
			$options = ['allow_types' => 'css', 'files_only' => 1];
			
			foreach ($this->cssTemplateDirs () as $file) {
				
				$file = $this->templ_dir.'css'.DS.$this->hash.DS.$file.'.css';
				if (file_exists ($file)) $this->files[] = $file;
				
			}
			
		}
		
		private function css () {
			
			$options = ['allow_types' => 'css', 'files_only' => 1];
			
			foreach (dir_scan ($this->getRootDir ().DS.'css', $options) as $file)
				if (file_exists ($file)) $this->files[] = $file;
			
		}
		
		protected function cssSystemDirs (): array {
			return ['bootstrap-oldify', 'mash', 'mash-sizes', 'mash-colors', 'mash-media'];
		}
		
		private function cssSystem () {
			
			$options = ['allow_types' => 'css', 'files_only' => 1];
			
			foreach ($this->cssSystemDirs () as $file) {
				
				$file = $this->getMashDir ('css').DS.$file.'.css';
				if (file_exists ($file)) $this->files[] = $file;
				
			}
			
			$dir_files = dir_scan ($this->getMashDir ('css/autodock'), $options);
			foreach ($dir_files as $file) $this->files[] = $file;
			
			//$dir_files = dir_scan ($this->getMashDir ('css/pastload'), $options);
			//foreach ($dir_files as $file) $this->files[] = $file;
			
		}
		
		private $content = '', $templ_dir = '';
		
		protected function getContent () {
			
			$this->templ_dir = $this->loadDir ('templates/'.$this->data['templ']).DS;
			//$admin_templ_dir = $this->loadDir ('admin_templates/'.$this->data['admin_templ']).DS;
			
			if ($this->data['type'] == 'css') { // CSS
				
				if ($this->getArea () == 'system')
					$this->cssSystem ();
				elseif ($this->getArea () == 'templ')
					$this->cssTemplate ();
				else
					$this->css ();
				
				if ($this->show_out) print_r ($this->files);
				
				foreach ($this->files as $file) {
					
					$this->content .= file_get_contents ($file);
					if ($this->debug) $this->content .= NL.NL;
					
				}
				
				/*$this->content = preg_replace_callback ('~background([\-image]*):\s*url\s*\(\'(.+?)\'\)~i', function ($match) {
					
					$items = explode ('/', $match[2]);
					
					$items[0] = str_replace ('..', $this->server['host'].'/templates/'.$this->data['templ'], $items[0]);
					
					$output = '';
					foreach ($items as $item)
					$output .= $item.'/';
					
					return 'background'.$match[1].': url(\'//'.trim ($output, '/').'\');';
					
				}, $this->content);*/
				
				if (file_exists ($this->templ_dir.'css'.DS.'style.php'))
					require $this->templ_dir.'css'.DS.'style.php';
				
				if (!$this->debug)
					$this->content = css_minify ($this->content);
				
			} else { // JS
				
				if ($this->getArea () == 'system') {
					
					if ($this->data['position'] == 'header')
						$this->jsHeaderSystem ();
					elseif ($this->data['position'] == 'footer') // Подвал
						$this->jsFooterSystem ();
					
				} else {
					
					if ($this->data['position'] == 'header')
						$this->jsHeaderTemplate ();
					elseif ($this->data['position'] == 'footer') // Подвал
						$this->jsFooterTemplate ();
					
				}
				
				//$modified = 0;
				
				//foreach ($this->files as $file)
				//$modified += filemtime ($file);
				
				//@header ('Etag: '.$modified);
				
				if ($this->show_out) print_r ($this->files);
				
				foreach ($this->files as $file) {
					
					$file = file_get_contents ($file);
					$this->content .= $file;
					
					if (substr ($file, -1, 1) != ';') $this->content .= ';';
					if ($this->debug) $this->content .= NL.NL;
					
				}
				
				if (!$this->debug)
					$this->content = js_minify ($this->content);
				
				$sumbols = [1 => ' '];
				
				foreach ($sumbols as $key => $value)
					$this->content = str_replace ('::'.$key.'::', $value, $this->content);
				
			}
			
			return $this->content;
			
		}
		
		function getRootDir () {
			return __DIR__;
		}
		
		private function minifyCacheFile () {
			
			$file_name = [$this->data['type']];
			
			if ($this->data['position']) $file_name[] = $this->data['position'];
			if ($this->getArea ()) $file_name[] = $this->getArea ();
			
			if (is_isset ('templ', $this->data) and $this->data['templ'])
				$file_name[] = $this->data['templ'];
			
			if (is_isset ('admin_templ', $this->data) and $this->data['admin_templ'])
				$file_name[] = $this->data['admin_templ'];
			
			if ($this->data['type'] == 'css') {
				
				if ($this->data['color']) $file_name[] = $this->data['color'];
				if ($this->data['font_size']) $file_name[] = $this->data['font_size'];
				
			}
			
			if ($this->data['lang']) {
				
				if (!$this->is_lang_correct ($this->data['lang']))
					$this->data['lang'] = 'ru';
				
				$file_name[] = $this->data['lang'];
				
			}
			
			return $this->cache_dir.'/'.implode ('-', $file_name).'.cache';
			
		}
		
		function is_lang_correct ($lang) {
			return in_array ($lang, array_keys ($this->locale->locale['langs']['code']['locale']));
		}
		
		function js_vars_lang ($name) {
			
			$output = [];
			
			foreach ($this->js_langs as $code) {
				
				$value = $this->locale->lang ($code);
				if ($value) $output[$code] = $value;
				
			}
			
			return $this->load->js_vars ($name, $output);
			
		}
		
	}