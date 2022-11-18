<?php
/*
 ========================================
 Mash Framework (c) 2010-2015, 2019-2020
 ----------------------------------------
 https://mash.ointeractive.ru/
 ========================================
 O! Interactive (support@ointeractive.ru)
 ----------------------------------------
 Класс авторизации
 ========================================
*/
	
	if (!defined ('MASH')) die ('File must be started only through the main framework cover');
	
	class Auth {
		
		public
			$member_id, $member_id_data, $user_group, $emails = [],
			
			$user_cols = ['user_id' => 0, 'name' => '', 'first_name' => '', 'banned' => 0, 'player_id' => 0, 'user_group' => 4, 'salt' => '', 'pm_unread' => 0, 'pm_all' => 0, 'friends_num' => 0, 'page_name' => ''],
			$data = ['error' => '', 'cancel' => false, 'not_remember' => 0],
			$providers = [],
			
			//$count_logins = 1,
			$log_attempts = 5,
			$log_deny_min = 5,
			$uncond_login = 0,
			
			$is_logged = false,
			$do_login = false,
			$do_logout = false,
			$unactive_groups = [0, 2, 3],
			$check = 1,
			$hash,
			
			$extra_login = 0;
			
		private
			$mash,
			$provider,
			$client_lang = '';
		
		function __construct ($mash) {
			
			$this->mash = $mash;
			
			if (file_exists ($this->mash->loadFile (['emails'])))
				require $this->mash->loadFile (['emails']);
			
			require $this->mash->loadMashFile (['files', 'sitelogin']);
			
			//debug ($_COOKIE['user_id'].' - '.$_SESSION['user_id']);
			
		}
		
		function member_id ($login, $password, $check, $check_pass = 1, $debug = 0) {
			
			if (not_empty ($login)) {
				
				$values = [];
				
				if (is_email ($login)) $values['email'] = $login;
				elseif ($check) $values['name'] = $this->mash->db->safesql ($login);
				else $values['user_id'] = (int) $login;
				
				if ($check_pass) $values['password'] = $password;
				
				//$debug = 1;
				$this->member_id = $this->mash->db->super_query (['select', '', 'users', $values], $debug);
				
			} else {
				
				foreach ($this->user_cols as $key => $value)
					$this->member_id[$key] = $value;
				
			}
			
		}
		
		function is_flooder ($mod, $area, $log_deny_min, $log_attempts = 1) { // Нехило так боремся с флудерами
			
			$is_deleted = 0;
			$is_inserted = 0;
			$log_attempts = intval_correct ($log_attempts, 1);
			
			$row = $this->mash->db->super_query2 ('SELECT * FROM '.$this->mash->db->table ('flood').' '.WHERE.' module = '.$this->mash->db->value ($mod).' AND area = '.$this->mash->db->value ($area).' AND ip = '.$this->mash->db->value (get_ip ()).' ORDER BY date DESC');
			
			if ($row['date']) {
				
				if ($this->mash->data['db_date'] > $this->mash->date->add_min ($log_deny_min, $row['date'])) { // Прошло $log_deny_min минут
					
					$this->mash->db->query2 ('DELETE FROM '.$this->mash->db->table ('flood').' '.WHERE.' date < '.$this->mash->db->value ($this_date));
					$row['count'] = 1;
					$is_deleted = 1;
					
				} else {
					
					$this->mash->db->query2 ('UPDATE '.$this->mash->db->table ('flood').' SET count = '.$this->mash->db->concat ('count').', date = '.$this->mash->db->value ($this->mash->data['db_date']).' '.WHERE.' date = '.$this->mash->db->value ($row['date']).'');
					++$row['count'];
					
				}
				
			}
			
			if (!$row['date'] or $is_deleted) {
				
				$this->mash->db->query2 ('INSERT INTO '.$this->mash->db->table ('flood').' (site_id, module, area, ip, count, date) VALUES ("'.$this->mash->site['id'].'", "'.$mod.'", "'.$area.'", "'.get_ip ().'", 1, "'.$this->mash->data['db_date'].'")');
				
			}
			
			if ($row['count'] > $log_attempts) return $row['count']; else return false;
			
		}
		
		function trim_date ($row) { // Отрезает дату (первую часть массива) в строке $row. Дата должна быть отделена от основной части строки символом "_".
			
			$row = get_filename ($row, 1);
			$row = explode ('_', $row);
			if (count ($row) > 1) unset ($row[0]);
			$row = implode ('_', $row);
			
			return $row;
			
		}
		
		function _explode_settings ($data, $debug = 0) {
			
			$data = json2array ($data);
			if ($debug) print_r ($data);
			
			return $data;
			
		}
		
		function explode_settings ($data, $user_data = [], $debug = 0) {
			
			$data = json2array ($data);
			if ($debug) print_r ($data);
			
			if (!$user_data) $user_data = [];
			
			return array_extend ($data, $user_data, $debug);
			
		}
		
		function implode_settings ($data, $user_data = []) {
			
			$data = array_extend ($data, $user_data);
			return json_encode ($data);
			
		}
		
		function lang_change ($lang) {
			
			$lang = strtolower ($lang);
			
			$_COOKIE[$this->lang_var] = $lang;
			$_SESSION[$this->lang_var] = $lang;
			
			return $lang;
			
		}
		
		function unique_id () { // Уникальный номер пользователя. Используется в операциях высокого уровня (генерация ссылок на скачивание и т. п.). Создается единожды при регистрации и нигде не отображается. Узнать его можно только выполнив запрос в БД.
			return do_rand (8);
		}
		
		function is_register ($where) {
			
			$output = [];
			foreach ($where as $key => $value)
			if ($value) $output[$key] = $value;
			
			if ($output)
			return $this->mash->db->super_query (['select', ['user_id', 'password', 'page_name'], 'users', $output]);
			
		}
		
		function login_user ($user_id, $password) { // Авторизируем юзера с id $user_id и паролем $password;
			
			$user_id = (int) $user_id;
			
			$_COOKIE['user_id'] = $user_id;
			$_COOKIE['password'] = $password;
			
			$_SESSION['user_id'] = $user_id;
			$_SESSION['password'] = $password;
			
			return true;
			
		}
		
		function email_mess ($type, $type2, $message) {
			
			$link = home_url ($this->mash->server['url']).$_SERVER['REQUEST_URI'];
			
			if ($type == 'error') {
				
				$subject = 'На сайте "'.$this->mash->config['site_title'].'" произошла ошибка';
				$body = 'На странице <a href="'.$link.'">'.$link.'</a> произошла ошибка:<br/><br/>'.$message;
				
			}
			
			$this->mash->db->query (['insert', ['site_id' => $this->mash->site['id'], 'name' => $this->emails[$type2][0], 'email' => $this->emails[$type2][1], 'subject' => $subject, 'body' => $body, 'date' => $this->mash->data['db_date']], 'emails']);
			
		}
		
		function flood_check ($options, $time, $count = 0, $debug = 0) {
			
			if (isset ($this->user_group[$this->member_id['user_group']]['edit_user_options']) and !$this->user_group[$this->member_id['user_group']]['edit_user_options']) {
				
				$options['site_id'] = $this->mash->site['id'];
				
				if ($this->member_id['user_id'])
				$options['user_id'] = $this->member_id['user_id'];
				else
				$options['ip'] = get_ip ();
				
				$row = $this->mash->db->super_query (['select', ['id', 'date', 'count'], 'flood', $options], $debug);
				
				if ($row['id']) {
					
					$time_online = strtotime ($row['date']) + $time;
					
					//debug (date ('H:i:s', $this->mash->data['db_date']));
					//debug (date ('H:i:s', $time_online));
					
					if ($count) {
						
						if ($row['count'] < $count) {
							
							$this->mash->db->query (['update', ['count' => ['count', '+']], 'flood', ['id' => $row['id']]]);
							++$row['count'];
							
							$result = true;
							
						}
						
					}
					
					if ($row['count'] >= $count or !$count) {
						
						if ($this->mash->data['db_date'] > $time_online) {
							
							$this->mash->db->query (['delete', '', 'flood', ['id' => $row['id']]]);
							$result = true;
							
						} else $result = false;
						
					}
					
				} else {
					
					$options['site_id'] = $this->mash->site['id'];
					$options['user_id'] = (int) $this->member_id['user_id'];
					$options['date'] = $this->mash->data['db_date'];
					$options['ip'] = get_ip ();
					
					$this->mash->db->query (['insert', $options, 'flood'], $debug);
					
					$result = true;
					
				}
				
			} else $result = true;
			
			return $result;
			
		}
		
		function int_lang ($title) {
			
			if (is_numeric ($title)) $title = $this->lang ($title);
			return $title;
			
		}
		
		function userGroups () {
			
			$output = [];
			
			foreach ($this->user_group as $id => $value)
			if (!$this->denied_user_group ($id)) $output[$id] = $this->user_group[$id]['group_name'];
			
			return $output;
			
		}
		
		function denied_user_group ($id) {
			return (in_array ((int) $id, [5, 7, 8]));
		}
		
		function is_new_window () {
			return (isset ($this->member_id['options']['options']) and $this->member_id['options']['options']['links_new_window']);
		}
		
		function check_group ($groups, $block, $action = 1) {
			
			$groups = strip_quotes ($groups);
			$groups = sep_explode ($groups);
			
			if ($action) {
				if (!in_array ($this->member_id['user_group'], $groups)) $block = '';
			} else {
				if (in_array ($this->member_id['user_group'], $groups)) $block = '';
			}
			
			return stripslashes ($block);
			
		}
		
		function check_group_rights ($right, $content, $action = 1) {
			
			$right = strip_quotes ($right);
			
			if ($action) {
				if (!$this->user_group[$this->member_id['user_group']][$right]) $content = '';
			} else {
				if ($this->user_group[$this->member_id['user_group']][$right]) $content = '';
			}
			
			return stripslashes ($content);
			
		}
		
		function user_group_allow ($value) {
			return $this->auth->user_group[$this->auth->member_id['user_group']][$value];
		}
		
		function is_author ($user_id, $allow_admin = 1, $debug = 0) {
			
			$is_author = 0;
			if ($this->mash->debug == 1) $allow_admin = 0;
			
			if ($debug) debug ($user_id);
			
			if (($allow_admin and $this->user_group_allow ('edit_user_options')) or ($this->is_logged and $this->member_id['user_id'] == $user_id)) $is_author = 1;
			
			return $is_author;
			
		}
		
		function is_online ($last_date, $user_online = 0) {
			
			if (!$user_online) $user_online = $this->configs['users']['user_online'];
			
			$time_online = strtotime ($last_date) + ((int) $user_online * 60);
			return ($this->mash->data['db_date'] <= $time_online);
			
		}
		
		function create_pass_hash ($password) {
			
			$passhash = mksecret ().$password.mksecret ();
			$passhash = md5 ($passhash);
			
			return $passhash;
			
		}
		
		function create_pass_key ($name, $password) {
			
			$passkey = $name.$this->mash->data['db_date'].$this->create_pass_hash ($password);
			$passkey = md5 ($passkey);
			$passkey = $this->mash->db->safesql ($passkey);
			
			return $passkey;
			
		}
		
		function get_user_hash ($name, $password) {
			
			$hash = lisas_strtolower ($_SERVER['HTTP_HOST'].$name.md5 ($password).$this->mash->date->show ('Ymd'));
			$hash = md5 ($hash);
			
			return $hash;
			
		}
		
	}