<?php
	
	/*error_reporting (E_ALL);
	
	@ini_set ('display_errors', true);
	@ini_set ('display_startup_errors', true);
	@ini_set ('html_errors', true);
	@ini_set ('error_reporting', E_ALL);*/
	
	session_start ();
	
	define ('MASH', true);
	define ('MASH_DIR', dirname (__FILE__));
	define ('MASH_CACHE_DIR', MASH_DIR.'/cache');
	
	require 'Load.php';
	require 'MashException.php';
	
	abstract class Mash {
		
		public $debug = 0, $configs = [], $config = [], $mashConfig, $error, $kernel = '', $global_config = [], $date, $data = [], $server = [], $robots, $tpl_exp = 'htm', $dateFormat = 'YmdHis';
		public $lang_date = []; //
		
		public $allow_php_include = true;
		public $errorsReportType = E_ALL;
		public $errorsType = 0;
		
		protected $dirs = [], $files = [], $params = [], $isInit = false, $isConstruct = false;
		
		const
			VERSION = '1.5.0';
		
		function __construct () {
			
			ob_start ();
			ob_implicit_flush (0);
			
			$this->isConstruct = true;
			
		}
		
		protected function mashConfig () {
			
			return [
				
				'config_file' => 'Config.php',
				
			];
			
		}
		
		function getConfig (): Mash\Config {
			return new Mash\Config ($this);
		}
		
		protected function onLoad () {}
		protected function onInit () {}
		protected function onConfig () {}
		protected function onShow () {}
		
		function init () {
			
			if ($this->isConstruct) {
				
				$this->onLoad ();
				
				error_reporting ($this->errorsReportType);
				
				@ini_set ('display_errors', ($this->errorsType == 1));
				@ini_set ('display_startup_errors', ($this->errorsType == 1));
				@ini_set ('html_errors', ($this->errorsType == 1));
				@ini_set ('error_reporting', $this->errorsReportType);
				
				if (!defined ('MASH_DISPLAY_ERRORS'))
					define ('MASH_DISPLAY_ERRORS', true);
				
				require $this->loadMashFile (['libraries', 'debug']);
				require $this->loadMashFile (['libraries', 'filesystem']);
				
				define ('ROOT_DIR', $this->getRootDir ()); // Прокладка для функций
				define ('LOG_WARNINGS', true);
				
				require $this->loadMashFile (['Adapter']);
				
				$this->addFile (['libraries', 'arrays'])
						 ->addFile (['libraries', 'system'])
						 ->addFile (['libraries', 'unicode'])
						 ->addFile (['libraries', 'strings'])
						 ->addFile (['libraries', 'core'])
						 ->addFile (['libraries', 'math'])
						 ->addFile (['libraries', 'images'])
						 ->addFile (['libraries', 'network'])
						 ->addFile (['libraries', 'outputs'])
						 ->addFile (['libraries', 'security'])
						 ->addFile (['libraries', 'uncli'])
						 ->addFolder (MASH_DIR.'/classes/autodock/preload')
						 ->addFolder (MASH_DIR.'/classes/autodock')
						 ->addFolder (MASH_DIR.'/classes/Streams');
				
				require $this->loadMashFile (['libraries', 'errors']);
				
				foreach ($this->files as $file) require $file;
				
				$this->files = [];
				
				foreach ($this->scanMashDir (['exceptions']) as $file) require $file;
				foreach ($this->scanMashDir (['adapters']) as $file) require $file;
				
				foreach ($this->scanMashDir (['adapters', 'DBAdapters']) as $file) require $file;
				foreach ($this->scanMashDir (['adapters', 'MessengerAdapters']) as $file) require $file;
				
				require $this->loadMashFile (['libraries', 'pastload']);
				require $this->loadMashFile (['classes', 'Curl']);
				
				$this->load = new Mash\Load ($this);
				
				if (!$this->config) {
					
					if (file_exists ($this->loadDir ('configs')))
					foreach ($this->scanDir ('configs') as $file) {
						
						require $file;
						$this->configs[get_filename ($file)] = $config;
						
					}
					
				} else $this->configs['config'] = $this->config;
				
				if (!isset ($this->configs['config']))
					$this->configs['config'] = [];
				
				$this->onConfig ();
				
				$this->config = array_extend ($this->configs['config'], [
					
					'date_adjust' => 0,
					'files_type' => '',
					'charset' => 'utf-8',
					'template' => 'Default',
					'site_title' => 'Mash',
					'smtp_host' => 'smtp.gmail.com',
					'language' => 'en',
					'upload_data' => '',
					'static_domain' => '',
					
				]);
				
				if (is_isset ('global_config', $this->configs))
				$this->global_config = $this->configs['global_config'];
				
				require $this->loadMashFile (['files', 'data']);
				
				$this->date = new \Date ($this->time (), ['strtotime' => 0]);
				
				//require $this->loadMashFile (['Adapter']);
				//require $this->loadMashFile (['AdapterHelper']);
				require $this->loadMashFile (['files', 'robots']);
				require $this->loadMashFile (['files', 'server']);
				
				$mash_config = $this->mashConfig ();
				
				if (file_exists ($this->getRootDir ().'/'.$mash_config['config_file']))
					$file = $this->getRootDir ().'/'.$mash_config['config_file'];
				elseif (file_exists (__DIR__.'/'.$mash_config['config_file']))
					$file = __DIR__.'/'.$mash_config['config_file'];
				else
					$file = '';
				
				if ($file) {
					
					require $file;
					
					if ($this->mashConfig = $this->getConfig ())
						$this->mashConfig->process ();
					
				}
				
				$this->onInit ();
				
				$this->isInit = true;
				
			} else throw new \Exception ('Parent constructor don\'t called');
			
		}
		
		final function show () {
			
			if (!$this->isInit) $this->init ();
			return $this->onShow ();
			
		}
		
		protected function addFile (array $file) {
			
			$this->files[] = $this->loadMashFile ($file);
			return $this;
			
		}
		
		function scan_dir ($dir, $resursive = false) {
			return dir_scan ($dir, ['allow_types' => 'php', 'recursive' => $resursive]);
		}
		
		public function scanDir ($path, $resursive = false) {
			return $this->scan_dir ($this->loadDir ($path), $resursive);
		}
		
		protected function scanMashDir ($path) {
			return $this->scan_dir ($this->getMashDir ($path));
		}
		
		private function addFolder ($folder, $resursive = false) {
			
			foreach ($this->files as $file) require $file;
			
			$this->files = [];
			foreach ($this->scan_dir ($folder, $resursive) as $file)
			$this->files[] = $file;
			
			return $this;
			
		}
		
		protected abstract function getRootDir ();
		
		function loadFile (array $path) {
			return $this->getRootDir ().DS.$this->implodePath ($path).'.php';
		}
		
		function loadMashFile (array $path) {
			return $this->getMashDir ($path).'.php';
		}
		
		function implodePath ($path) {
			
			if (is_array ($path))
				$path = implode (DIRECTORY_SEPARATOR, $path);
			
			return $path;
			
		}
		
		function loadDir ($path, $require = false) {
			
			$dir = $this->getRootDir ().DIRECTORY_SEPARATOR.$this->implodePath ($path);
			
			if ($require) require $dir;
			return $dir;
			
		}
		
		function getMashDir ($path, $require = false) {
			
			$dir = __DIR__.DIRECTORY_SEPARATOR.$this->implodePath ($path);
			
			if ($require) require $dir;
			return $dir;
			
		}
		
		function getLibrariesDir ($path) {
			return $this->mashConfig->getLibrariesDir ().DS.$this->implodePath ($path);
		}
		
		function scanCurrentDir ($path, $recursive = false) {
			return $this->scan_dir ($this->implodePath ($path), $recursive);
		}
		
		function prepData (string $key, array $array, $def) {
			
			if (isset ($array[$key])) {
				
				if (is_numeric ($def))
					return (int) $array[$key];
				else
					return $array[$key];
				
			} else $array[$key] = $def;
			
		}
		
		function time () {
			return (time () + $this->data['date_adjust']);
		}
		
		function date ($date = 0) {
			
			if (!$date) $date = $this->time ();
			return date ($this->dateFormat, $date);
			
		}
		
		protected $dbAdapters = [];
		
		final function addDBProvider (DB\Adapter $provider) {
			
			$this->dbAdapters[$provider->getName ()] = $provider;
			return $this;
			
		}
		
		final function getDBProvider (string $name): DB\Adapter {
			return $this->dbAdapters[$name];
		}
		
		function getWorkTime ($round = 3) {
			return work_time ($round);
		}
		
		function load ($config_tpl, $tpl_name, $sys = 0) {
			
			if ($sys)
				$temp_name = $this->getMashDir (['templates', $tpl_name]).'.'.$this->tpl_exp;
			else
				$temp_name = $this->getMashDir (['templates', $config_tpl, $tpl_name]).'.'.$this->tpl_exp;
			
			if (!$tpl_name or !file_exists ($temp_name))
				echo echo_message ('Шаблон '.$temp_name.' не найден.');
			else
				return file_get_content ($temp_name);
			
		}
		
	}