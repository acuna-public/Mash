<?php
/*
 ========================================
 Mash Framework (c) 2010-2015
 ----------------------------------------
 https://mash.ointeractive.ru/
 ========================================
 O! Interactive (support@ointeractive.ru)
 ----------------------------------------
 Вывод тегов в main.htm
 ========================================
*/
	
	if (!defined ('MASH')) die ('File must be started only through the main framework cover');
	
	$this->tpl->copy = $this->tpl->result['main'];
	
	//foreach ($this->mash->sa->plugin ('file', 'showmain', 'show') as $plugin) require $plugin;
	
	if (strpos ($this->tpl->copy, '{custom=') !== false)
		$this->tpl->copy = preg_replace_callback ('#\{custom=(.+?)\}#is', function ($match) { // TODO
		return $this->mod->sc->build_custom ($match[1]);
	}, $this->tpl->copy);
	
	if (strpos ($this->tpl->copy, '<!DOCTYPE') === false and $this->config['doctype'])
		$this->tpl->copy = $this->config['doctype'].NL.$this->tpl->copy;
	
	$this->tpl->copy .= NL.NL.str_replace ([base64_decode ('e3llYXJ9'), base64_decode ('e3ZlcnNpb259')], [date ('Y'), isset ($this->kernel['version']) ? $this->kernel['version'] : ''], lsa2_decode ('UGkwdENnMStmbjUrZm41K2ZuNStmbjUrZm41K2ZuNStmbjUrZm41K2ZuNStmbjUrZm41K2ZuNStmbjUrZm41K2ZuNStmbjUrZm41K2ZuNStmbjUrZm41K2ZuNStmbjUrZm41K2ZuNStmbjUrZm41K2ZuNStmbjUrZm41K2ZuNStmbjRnQ2cwdWk5RzkwTFhRaWRHNDBJalJzTkMzMENDdzBMTFFzTkNBMGIvUUlMWFFnZEdTMENCOElDbDFjaTVsZG1sMFkyRnlaWFJ1YVc5QWRISnZjSEIxY3lnZ1pYWnBkR05oY21WMGJra2dJVThnZkNCOWJtOXBjM0psZG5zZ1pYTnVaV2xrZFVFZ2ZYSmhaWGw3TFRBeE1ESWdLV01vSUFvTmZuNStmbjUrZm41K2ZuNStmbjUrZm41K2ZuNStmbjUrZm41K2ZuNStmbjUrZm41K2ZuNStmbjUrZm41K2ZuNStmbjUrZm41K2ZuNStmbjUrZm41K2ZuNStmbjUrZm41K2ZuNStmbjUrZm41K2ZuNStmbjUrZm41K2ZuNStJQW9OTFMwaFBBPT0='));
	
	//if (!$this->tpl->found ('{headers}'))
	//	throw new MashException ('Тег {headers} не установлен в шаблоне main.htm');
	
	$info = '';
	
	if ($this->auth->member_id['user_group'] == 1 or $this->debug) {
		
		$round = 3;
		
		$system_info = [
			
			'work_time' => round (work_time (), $round).' сек.',
			'tpl_time' => round ($this->tpl->parse_time, $round).' сек.',
			'db_query_num' => $this->db->query_num,
			'db_query_time' => round ($this->db->query_time, $round).' сек.',
			
		];
		
		if ($this->debug == 2) {
			
			$system_info['count_ram'] = count_ram ();
			$system_info['cpu_load'] = get_cpu_load (80);
			
		}
		
		$system_info_array = [
			
			['count_ram', 20, 'RAM слишком много!'],
			['work_time', 20, 'Времени выполнения слишком много!'],
			['cpu_load',	80, 'Загрузка процессора слишком большая!'],
			
		];
		
		foreach ($system_info_array as $data)
		if (isset ($system_info[$data[0]]) and $system_info[$data[0]] >= $data[1]) {
			
			//if ($this->debug)
			lisas_log ($system_info[$data[0]], $data[0]);
			//else
			//debug_l ($data[2].' ('.$system_info[$data[0]].')');
			
		}
		
		$gzip_encoding = gzip_encoding ();
		
		$info .= '<span title="Время работы скрипта">'.$system_info['work_time'].'</span> | <span title="Время компиляции шаблонов">'.$system_info['tpl_time'].'</span> | <span title="Количество SQL-запросов">'.$system_info['db_query_num'].'</span> | <span title="Время выполнения SQL-запросов">'.$system_info['db_query_time'].'</span>';
		
		if ($this->debug == 2)
			$info .= '| <span title="Затрачено оперативной памяти">'.$system_info['count_ram'].'</span>| <span title="Уровень загрузки CPU">'.$system_info['cpu_load'].'%</span>';
		
		if ($this->config['gzip'] and $gzip_encoding)
			$info .= '| <span title="Сжатие страниц" style="color:green;">'.$gzip_encoding.'</span>';
		else
			$info .= '| <span title="Сжатие страниц" style="color:red;">off</span>';
		
	}
	
	$this->tpl->copy = str_replace ('{system_info}', $info, $this->tpl->copy); // Вывод служебной информации в теле страниц
	
	//if (!$is_install and $this->db->query_num >= 40) die (base64_decode ('wvvv7uvt5e3uIO3l5O7v8/Hy6OzuIOHu6/z47uUg6u7r6Pfl8fLi7iDn4O/w7vHu4iDiIMHELiDC7ufs7ubt7iwg6Ozl6+gg7OXx8u4g4PLg6uAg6OvoIPPx8uDt7uLq4CDt5erg9+Xx8uLl7e379SDk7u/u6+3l7ejpLg=='));
	
	//if (!$this->tpl->found ($tpl_preg, $this->tpl->copy) or count ($c_data) <= 1) die (echo_message (lsa2_decode ('SWIzUXRkQzcwTExRdnRDOTBMRFFndEdCMFlQUklMWFF2ZEFnZ3RHNTBMRFFnTkc0MEwvUXZ0Q2EwQT09')));
	
	//$this->tpl->copy = str_replace ($tpl_preg, $tpl_content, $this->tpl->copy);