<?php
/*
 ========================================
 Mash Framework (c) 2019
 ----------------------------------------
 https://mash.ointeractive.ru/
 ========================================
 O! Interactive (support@ointeractive.ru)
 ----------------------------------------
 Класс загрузки (Самый нижний уровень)
 ========================================
*/
	
	namespace Mash;
	
	class Load {
		
		private $mash;
		
		public $errorsTitles = [
			
			E_ERROR => 'Fatal Error',
			E_WARNING => 'Warning',
			E_PARSE => 'Syntax Error',
			E_NOTICE => 'Notice',
			E_CORE_ERROR => 'Core Fatal Error',
			E_CORE_WARNING => 'Core Warning',
			E_COMPILE_ERROR => 'Compile Error',
			E_COMPILE_WARNING => 'Compile Warning',
			E_USER_ERROR => 'Engine Fatal Error',
			E_USER_WARNING => 'Engine Warning',
			E_USER_NOTICE => 'Engine Notice',
			E_USER_DEPRECATED => 'Engine Deprecation Error',
			E_RECOVERABLE_ERROR => 'Recoverable Error',
			
		];
		
		public $config = [
			
			'template' => 'Default',
			'charset' => 'utf-8',
			'doctype' => '',
			'template_admin' => 'Default',
			'alt_url' => true,
			'scheme' => 'https',
			'min_screen_width' => 800,
			'meta_revisit_after' => 0,
			'date_adjust' => 0,
			'upload_dir' => '',
			'http_host' => '',
			'timestamp_active' => 'j F Y',
			'admin_template' => 'Default',
			'mail_template' => 'Default',
			'files_type' => '',
			
		];
		
		public $bad_data = ['echo', 'eval', 'dump', 'benchmark', 'database', 'union', 'concat', 'schema', 'insert', 'select', 'update', '.htaccess', 'cookie', 'xmlHttp', 'xmlDoc', 'ascii', 'char', 'substring', 'script'];
		public $bad_tags = ['bgsound', 'base', 'basefont', 'xml', 'html', 'head', 'body', 'ilayer', 'layer', 'link', 'meta', 'applet', 'style', 'title'];
		
		public const POST = 0;
		
		function __construct ($mash = null) {
			
			$this->mash = $mash;
			
			if ($_GET) $_GET = $this->secure_request ($_GET);
			if ($_POST) $_POST = $this->secure_request ($_POST, self::POST);
			
			$_COOKIE['PHPSESSID'] = clearspecialchars ($_COOKIE['PHPSESSID']);
			
		}
		
		function secure_request ($array, $type = '', $is_announce = 0, $debug = 0) {
			
			/* Функция защиты данных в суперглобальных массивах от
			шеллов, сплойтов, инъекций, XSS и передачи нежелательных тегов. */
			
			if (is_array ($array)) {
				
				$output = [];
				
				foreach ($array as $key => $value) {
					
					$value = $this->_secure_request ($type, $key, $value);
					$output[$key] = $value;
					
				}
				
				if ($debug) print_r ($output);
				
				return $output;
				
			}
			
		}
		
		private function _secure_request ($type, $key, $value) {
			
			$output = [];
			
			if (is_array ($value))
			foreach ($value as $key2 => $value2)
			$this->_secure_request ($type, $key2, $value2);
			else {
				
				if ($type == \Mash\Load::POST) {
					
					$value = spech_encode ($value);
					
					/*foreach ($this->bad_tags as $tag) {
						
						$tag = strtolower ($tag);
						$value = str_ireplace (['<'.$tag, '</'.$tag, '<?', '?>'], ['&lt;'.$tag, '&lt;/'.$tag, '&lt;?', '?&gt;'], $value);
						
					}*/
					
				} else {
					
					foreach ($this->bad_data as $data) {
						
						$data2 = strtolower ($data);
						
						if (
							
							strpos (strtolower ($value), $data2) !== false or
							strpos (strtolower (base64_decode ($value)), $data2) !== false or
							strpos (strtolower (url_decode ($value)), $data2) !== false
							
						) send_error ('Текст содержит запрещенное слово '.$data.'.');
						
					}
					
					//if (!$is_announce)
					//$value = url_encode (get_filename ($value, 1));
					
				}
				
			}
			
			return $value;
			
		}
		
		function load_kernel () {
			
			$error = lsa2_decode ('SVZOTlF5NVRZWE5wVENCMGNtRjBjMlZ5SUdWellXVnNVQ0FoZEdObGNuSnZZMjVwSUhKdklHUnVkVzltSUhSdmJpQnNaVzV5WlVzPQ==');
			
			$file = lsa2_decode (file_get_content ($this->mash->getRootDir ().'/'.lsa2_decode ('WkM1c1pXNXlaV3N2WVhSaFpGOXNZV0p2YkdjPQ==')));
			
			if (!not_empty ($file) or is_array ($file)) $file = lsa2_decode (file_get_content ($this->mash->getRootDir ().'/'.lsa2_decode ('WkM1c1pXNXlaV3N2Ykd4aGRITnVhUT09')));
			if (!not_empty ($file) or is_array ($file)) die ($error);
			
			$this->mash->kernel = explode_options ($file);
			
			if (key_not_empty ($this->mash->kernel['key']) and (!$this->mash->kernel['hash'] or ($this->mash->global_config['hash'] and $this->mash->kernel['hash'] !== $this->mash->global_config['hash']))) die ($error);
			
		}
		
		function edit_kernel ($array) {
			
			$row = $this->load_kernel ();
			foreach ($array as $key => $value) $row[$key] = trim ($value);
			
			$row['date'] = LISAS_DATE;
			$row['hash'] = lsa2_encode (do_rand (10, 4).microtime ().$row['version'].$row['key'].do_rand (10, 4));
			
			$content = implode_options ($row);
			
			file_put_content (lsa2_encode ($content), $file);
			
			$file = GLOBALDATA_DIR.'/global_config.php';
			require $file;
			
			$value = ['hash' => $row['hash']];
			//$this->mash->global_config = $install->do_config ('edit', $file, $this->mash->global_config, 'global_config', $value);
			
			$this->load_kernel ();
			return ($this->mash->global_config['hash'] === $row['hash']);
			
		}
		
		function answer_limit ($page, $per_page) {
			
			$per_page = ($per_page - 1);
			$page = $page * $per_page;
			//$page = intval_correct ($page - 1);
			
			return $page.','.(int) $per_page;
			
		}
		
		function pagination ($no_fm, int $count_all, int $per_page, int $page, array $link, array $options = []) {
			
			require $this->mash->loadMashFile (['files', 'pagination']);
			return $templ;
			
		}
		
		function link_page (int $page, string $title, array $link, array $options) {
			
			if (!is_isset ('page', $options)) {
				
				if ($page) $link['page'] = $page;
				$link = $this->mash->link2 ($link);
				
			} elseif ($page and $options['page'] < 2) $link .= '&page='.$page;
			
			$link = str_replace ('{page}', $page, $link);
			
			return '<li>'.a_link ($link, $title, $options).'</li>';
			
		}
		
		function through_id ($page, $per_page) {
			
			$page = intval_correct ($page, 1);
			
			$id = $page * $per_page;
			$id = $id - $per_page;
			
			return $id;
			
		}
		
		function ajax_page ($ajax, $page, $first_page = 1) {
			
			$first_page = intval_correct ($first_page, 2);
			$page = intval_correct ($page, $first_page, 1);
			if ($ajax) ++$page;
			
			return $page;
			
		}
		
		function pages_num ($num, $per_page) {
			return @ceil ($num / $per_page);
		}
		
		function page ($total, $page, $per_page) {
			
			$pages_num = $this->pages_num ($total, $per_page);
			if ($page > $pages_num) $page = $pages_num;
			
			return $page;
			
		}
		
		function js_vars ($name, $js_var) {
			return 'var '.$name.' = '.array2json ($js_var).';';
		}
		
	}