<?php
/*
 ========================================
 Mash Framework (c) 2013, 2015, 2017
 ----------------------------------------
 https://mash.ointeractive.ru/
 ========================================
 O! Interactive (support@ointeractive.ru)
 ----------------------------------------
 Класс работы с FTP
 ========================================
*/
	
	if (!defined ('MASH')) die ('File must be started only through the main framework cover');
	
	class FTP {
		
		public
		$debug = 0;
		
		private
		$config = [],
		$login, $connect, $sep = '/';
		
		function __construct ($config) {
			
			$this->config = array_extend ($config, [
				
				'connect_timeout' => 30,
				'passive_mode' => true,
				'port' => 21,
				
			]);
			
			$this->config['port'] = intval_correct ($this->config['port'], 21);
			
		}
		
		function connect () {
			
			if ($this->connect = ftp_connect ($this->config['server'], $this->config['port'], $this->config['connect_timeout'])) {
				
				if ($this->login = ftp_login ($this->connect, $this->config['login'], $this->config['password'])) {
					
					@ftp_pasv ($this->connect, $this->config['passive_mode']);
					
				} else $this->error ('Неверный логин или пароль FTP');
				
			} else $this->error ('Невозможно соединиться с FTP-сервером');
			
		}
		
		private function error ($text) {
			trigger_error ($text);
		}
		
		private function path ($file) {
			
			$file = add_ds ($this->config['remote_path'], $this->sep).$file;
			
			if ($file[0] != $this->sep)
			$file = $this->sep.$file;
			
			$file = str_replace ('\\', $this->sep, $file);
			
			return ds_rtrim ($file, $this->sep);
			
		}
		
		function _dir_make ($dir, $chmod = 0777) {
			
			$result = 0;
			$dirs = explode_filepath ($dir, $this->sep);
			
			foreach ($dirs as $dir)
			if (!@$this->dir_change ($dir))
			if ($result = @ftp_mkdir ($this->connect, $dir)) {
				
				$this->dir_change ($dir);
				if ($chmod) @ftp_chmod ($this->connect, $chmod, $dir);
				
			}
			
			return $result;
			
		}
		
		function dir_make ($dir, $chmod = 0777) {
			return $this->_dir_make ($this->path ($dir), $chmod);
		}
		
		function dir_change ($dir) {
			return ftp_chdir ($this->connect, $dir);
		}
		
		function dir_name () {
			return ftp_pwd ($this->connect);
		}
		
		private function _list ($dir, $recursive = 0, $max_level = 0, $i = 0) {
			
			if ($recursive) {
				
				$files = [];
				
				foreach (ftp_rawlist ($this->connect, $dir) as $file) {
					
					if ($i <= $max_level or !$max_level) {
						
						$tokens = explode (' ', $file);
						$filepath = $dir.$this->sep.$tokens[count ($tokens) - 1];
						
						if ($tokens[0][0] == 'd') {
							++$i;
							
							foreach ($this->_list ($filepath, $recursive, $max_level, $i) as $file)
							$files[] = $file;
							
						} else $files[] = $filepath;
						
					}
					
				}
				
			} else $files = ftp_nlist ($this->connect, $dir);
			
			return $files;
			
		}
		
		function list ($dir = '/', $recursive = 0, $max_level = 0) {
			return $this->_list ($this->path ($dir), $recursive, $max_level);
		}
		
		private function _copy ($local_file, $remote_file, $root = '') {
			
			if (is_dir ($local_file)) {
				
				if (!$root) $root = $local_file;
				
				foreach (dir_scan ($local_file, ['files_only' => 0]) as $file) {
					
					$canonical = get_canonical ($file, $root);
					
					$local = $local_file.DS.$canonical;
					$remote = $remote_file.$this->sep.$canonical;
					
					if (is_dir ($file)) {
						
						$this->_dir_make ($remote);
						$this->_copy ($local, $remote);
						
					} elseif (is_file ($file))
					$this->__copy ($local, $remote);
					
				}
				
			} elseif (is_file ($local_file))
			$this->__copy ($local_file, $remote_file);
			
		}
		
		private function __copy ($local_file, $remote_file) {
			return ftp_put ($this->connect, $remote_file, $local_file, FTP_BINARY);
		}
		
		function copy ($local_file, $remote_file) {
			return $this->_copy ($local_file, $this->path ($remote_file));
		}
		
		private function allow ($file) {
			return allow_filename (get_filename ($file, 1, $this->sep));
		}
		
		private function _dir_delete ($dir) {
			
			if (!$this->_file_delete ($dir) and $this->allow ($dir) and $files = $this->_list ($dir)) {
				
				foreach ($files as $file)
				$this->_dir_delete ($file);
				
				$result = ftp_rmdir ($this->connect, $dir);
				
			} else $result = 0;
			
			return $result;
			
		}
		
		function dir_delete ($dir) {
			return $this->_dir_delete ($this->path ($dir));
		}
		
		private function _file_delete ($file) {
			return @ftp_delete ($this->connect, $file);
		}
		
		function file_delete ($file) {
			return $this->_file_delete ($this->path ($file));
		}
		
		function mtime ($file) {
			return ftp_mdtm ($this->connect, '"'.$file.'"');
		}
		
		function __destruct () {
			if($this->connect) ftp_close ($this->connect);
		}
		
	}