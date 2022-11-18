<?php
	
	$this->data = [ // Глобальные переменные для работы
		
		'is_plugin' => false,
		'share' => [],
		'error' => 0,
		'date_adjust' => ((int) $this->config['date_adjust'] * 60),
		'mobile' => false,
		'url' => (!defined ('CLI') ? $_SERVER['HTTP_HOST'] : ''),
		'admin' => false,
		'modal' => false,
		'ajax' => false,
		'id' => $this->prepData ('id', $_REQUEST, 0),
		'user_id' => $this->prepData ('user_id', $_GET, 0),
		'sharding_tables_num' => [],
		
	];
	
	$this->data['db_date'] = $this->date ();
	
	define ('LISAS_DATE', $this->data['db_date']);
	
	foreach (['not_remember'] as $key)
		if (is_isset ($key, $_POST))
			$this->data[$key] = (int) $_POST[$key];
		elseif (is_isset ($key, $_SESSION))
			$this->data[$key] = (int) $_SESSION[$key];
		elseif (is_isset ($key, $_COOKIE))
			$this->data[$key] = (int) $_COOKIE[$key];
		else
			$this->data[$key] = 0;
			
	foreach (['page'] as $key)
		if (is_isset ($key, $_REQUEST))
			$this->data[$key] = intval_correct ($_REQUEST[$key], 1);
		else
			$this->data[$key] = 1;
			
	foreach (['query'] as $key)
		if (is_isset ($key, $_REQUEST))
			$this->data[$key] = str_correct (url_decode ($_REQUEST[$key]));
		else
			$this->data[$key] = '';
			
	foreach (['mod', 'action', 'area', 'type', 'user', 'name', 'hash'] as $key)
		$this->data[$key] = $this->prepData ($key, $_REQUEST, '');
	
	foreach (['doaction', 'search_params', 'get_user_name', 'lang'] as $key)
		$this->data[$key] = $this->prepData ($key, $_GET, '');
	
	foreach (['sub_area', 'search_area'] as $key) {
		
		if (is_isset ($key, $_GET))
			$this->data[$key] = url_decode ($_GET[$key]);
		else
			$this->data[$key] = '';
		
	}
	
	foreach (['subaction', 'captcha'] as $key)
		$this->data[$key] = $this->prepData ($key, $_POST, '');
	
	foreach (['title'] as $key)
		$this->data[$key] = $this->prepData ($key, $_REQUEST, '');
	
	foreach ($_GET as $key => $value)
		$this->data[$key] = $this->prepData ($key, $_GET, '');
	
	$this->fileTypes = [
		
		'deny' => [
			
			'file' => ['php', 'htm', 'htaccess', 'cgi', 'pl', 'asp', 'js'],
			'files' => [],
			
		],
		
		'allow' => [
			
			'images' => ['jpg', 'jpeg', 'jpe', 'png', 'gif'],
			'videos' => ['avi', 'mkv'],
			
			'fvideo' => ['flv', 'mp4', 'm4v', 'm4a'],
			'divx' => ['divx'],
			'audio' => ['mp3'],
			'flash' => ['swf'],
			
		],
		
	];
	
	$this->fileTypes['allow']['files'] = sep_prepare (lisas_strtolower ($this->config['files_type']), $this->fileTypes['deny']['files']);
	
	if (!defined ('CLI'))
		$this->data['full_url'] = $this->data['url'].$_SERVER['REQUEST_URI'];