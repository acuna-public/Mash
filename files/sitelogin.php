<?php
/*
 ========================================
 Mash Framework (c) 2010-2015, 2020
 ----------------------------------------
 https://mash.ointeractive.ru/
 ========================================
 O! Interactive (support@ointeractive.ru)
 ----------------------------------------
 Процедуры авторизации / дизавторизации
 ========================================
*/
	
	if (!defined ('MASH')) die ('File must be started only through the main framework cover');
	
	$error = [];
	$check = 1;
	
	if ($this->mash->data['admin'] and $this->mash->config['admin_extra_login']) { // В админке
		$this->extra_login = 1;
		$area = 'Admin Area';
	} elseif (!$this->mash->data['admin'] and isset ($this->mash->configs['users']) and is_isset ('extra_login', $this->mash->configs['users'])) { // На сайте
		$this->extra_login = 1;
		$area = 'Site';
	}
	
	if (is_isset ('subaction', $_POST) == 'login' or $this->mash->data['action'] == 'login') { // Действие входа
		
		if ($this->mash->server['referer']['host'] != $_SERVER['HTTP_HOST']) die ('Hacking Attempt!');
		
		$login = $this->mash->db->safesql ($_POST['login']);
		$password = $this->mash->db->safesql ($_POST['password']);
		
		$values = [];
		if (is_email ($login)) $values['email'] = $login; else $values['name'] = $login;
		
		$row = $this->mash->db->super_query (['select', ['user_id', 'salt'], 'users', $values]);
		$password = lsa1 ($password, $row['user_id'], $row['salt']);
		
		$this->do_login = 1;
		
		//if (!$row['name']) $error[] = 'Пользователь "'.$login.'" не найден';
		
		if (!$this->flood_check (['module' => 'users', 'area' => 'login'], ($this->log_deny_min * 60), $this->log_attempts))
		$error[] = str_replace ('{min}', $this->log_deny_min, lsa2_decode ('TG9MUmc5RzkwTGpRdk5BZ2ZXNXBiWHNndDlDMTBJRFJ0ZENIMFNDdzBMTFF2dEM5MElIUklMWFFndEc1MElQUnNkQyswSURSdjlDKzBKL1FJQzZDMGJuUXNOQ0IwU0N3MEwzUUlMRFF0TkMrMElYUnN0QWd1TkM2MElMUmk5Ry8wTDdRdjlBZ3RkQ0IwYkxRSUxqUXU5Q3cwTC9RZ05HMTBJZlJnZEc0MENDTDBaTFE='));
		
		if ($this->extra_login) auth ($area);
		
	} elseif ($this->extra_login) {
		
		$login = $_SERVER['PHP_AUTH_USER'];
		
		$row = $this->mash->db->super_query2 ('SELECT user_id, salt FROM '.$this->mash->db->table ('users').' '.WHERE_USER.' name = '.$this->mash->db->value ($login));
		
		$password = lsa1 ($_SERVER['PHP_AUTH_PW'], $row['user_id'], $row['salt']);
		
		if (!$login or !$password) auth ($area);
		
	} elseif (is_isset ('user_id', $_COOKIE) and $_COOKIE['user_id'] > 0) {
		
		$login = $_COOKIE['user_id']; 
		$password = $_COOKIE['password'];
		
		$check = 0;
		
	} elseif (is_isset ('user_id', $_SESSION) and $_SESSION['user_id'] > 0) {
		
		$login = $_SESSION['user_id']; 
		$password = $_SESSION['password'];
		
		$check = 0;
		
	} else {
		
		$login = ''; 
		$password = '';
		
	}
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	if ($this->do_login) {
		
		$no_speedbar = 1;
		
		if (!not_empty ($login) or !not_empty ($password))
			$error[] = 'Введите логин и пароль';
		
	}
	
	$this->member_id ($login, $password, $check, 1);
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	if ($this->member_id['user_id']) { // Юзер найден
		
		if (in_array ($this->member_id['approve'], $this->unactive_groups))
			$this->member_id['user_group'] = 8; // Не активирован
		
		if (!count ($this->user_group)) $this->user_group[1]['allow_admin'] = 1; // Если групп вообще нет - разрешаем главному админу доступ в админку
		if ($this->mash->data['admin'] and $template != 'upload' and !$this->user_group[$this->member_id['user_group']]['allow_admin']) $error[] = 'У вас нет прав для входа в админ-панель!';
		
		if (!$error) {
			
			$this->is_logged = true;
			
			$this->member_id_data = $this->mash->tdb->super_query ('select', '', $this->member_id['user_data']);
			
			if ($this->data['not_remember']) {
				
				$_COOKIE['not_remember'] = 1;
				$_SESSION['not_remember'] = 1;
				
			} else {
				
				$_COOKIE['user_id'] = $this->member_id['user_id'];
				$_COOKIE['password'] = $this->member_id['password'];
				
			}
			
			$_SESSION['user_id'] = $this->member_id['user_id'];
			$_SESSION['password'] = $this->member_id['password'];
			$_SESSION['member_lasttime'] = $this->member_id['last_date'];
			$_SESSION['log_num'] = 0;
			
			$this->hash = $this->get_user_hash ($this->member_id['name'], $this->member_id['password']);
			
			$values = [
				
				'last_date' => LISAS_DATE,
				'last_ip' => LISAS_IP,
				
			];
			
			if (isset ($this->mash->configs['users']['log_hash'])) { // TODO
				
				srand ((double) microtime () * 1000000);
				
				$salt = 'abchefghjkmnpqrstuvwxyz0123456789';
				
				$hash = '';
				for ($i = 0; $i < 9; ++$i)
				$hash .= $salt[rand (0, 33)];
				
				$hash = md5 ($hash);
				
				$values['hash'] = $hash;
				$_COOKIE['hash'] = $hash;
				$_SESSION['hash'] = $hash;
				$this->member_id['hash'] = $hash;
				
			}
			
			$this->mash->db->query2 ('UPDATE LOW_PRIORITY '.$this->mash->db->table ('users').' SET '.$this->mash->db->update_values ($values).' '.WHERE_USER.' '.$this->mash->db->addquotes ('user_id').' = '.$this->mash->db->value ($this->member_id['user_id']));
			
			if ($this->do_login) reload_page ();
			
		}
		
	} else {
		
		if ($this->extra_login) auth ($area);
		
		if ($this->do_login and not_empty ($login) and not_empty ($password))
		$error[] = 'Введен неверный логин или пароль';
		
		define ('HASH', 0); // TODO
		$this->member_id['user_group'] = 5;
		
	}
	
	if ($this->mash->data['action'] == 'logout') {
		
		$login = ''; 
		$password = '';
		
		$_COOKIE['user_id'] = 0;
		$_COOKIE['password'] = '';
		$_COOKIE['not_remember'] = 0;
		$_COOKIE['new_pm'] = '';
		$_COOKIE['hash'] = '';
		$_COOKIE[session_name ()] = '';
		$_COOKIE['onl_session'] = 0;
		
		$_SESSION['user_id'] = 0;
		$_SESSION['password'] = '';
		$_SESSION['not_remember'] = 0;
		$_SESSION['log_num'] = 0;
		
		session_destroy ();
		session_unset ();
		
		$this->is_logged = false;
		$this->do_logout = true;
		
		//auth ($area);
		//if ($this->extra_login) auth ($area);
		
		if ($this->mash->data['admin'])
			reload_page ($ADMIN_PHP_SELF);
		else
			reload_page ();
		
	}
	
	if ($this->mash->error)
		$this->mash->data['login_error'] = $this->mash->error;
	
	@header ('Last-Modified: '.gmdate ('D, d M Y H:i:s').' GMT');
	@header ('Cache-Control: no-store, no-cache, must-revalidate');
	@header ('Cache-Control: post-check=0, pre-check=0', false);
	@header ('Pragma: no-cache');