<?php
/*
 ========================================
 Mash Framework (c) 2010-2015, 2019
 ----------------------------------------
 https://mash.ointeractive.ru/
 ========================================
 O! Interactive (support@ointeractive.ru)
 ----------------------------------------
 Инициализация главного шаблона main.htm
 ========================================
*/
	
	if (!defined ('MASH')) die ('Hacking attempt!');
	
	if (!in_array ($this->module->getType (), $this->no_nav_mods))
		$this->nav[] = $this->module->getTitle ();
	
	if ($this->data['page'] > 1)
		$this->nav[] = $this->locale->lang (416).' '.$this->data['page'];
	
	$this->site_offline = (($this->config['site_offline'] and !$this->auth->user_group[$this->auth->member_id['user_group']]['allow_offline']) or $this->auth->member_id['banned']);
	
	if ($this->site_offline)
	$this->header_settings['admin_templ'] = $this->config['admin_template'];
	
	$custom_js = [];
	
	$this->header_settings['lang'] = $this->locale->lang_code;
	$this->header_settings['templ'] = $this->config['template'];
	$this->header_settings['charset'] = $this->locale->charset;
	
	if ($custom_js)
	$this->header_settings['custom_js'] = sep2_implode ($custom_js);
	
	if ($this->site_offline)
	$this->header_settings['admin_templ'] = $this->config['admin_template'];
	
	$this->footer_settings['lang'] = $this->locale->lang_code;
	
	if ($this->site_offline)
	$this->footer_settings['admin_templ'] = $this->config['admin_template'];
	
	$js_vars = [
		
		'root_dir' => home_url ($this->server['host']),
		'php_self' => $this->server['php_self'],
		'theme_dir' => $this->http_dir['theme'],
		'admin_theme' => $this->http_dir['admin_theme'],
		'hash' => $this->auth->hash,
		'editor_type' => $this->config['editor_type'],
		'comments_editor_type' => $this->config['comments_editor_type'],
		'id' => $this->data['id'],
		'action' => $this->data['action'],
		'site_id' => $this->site['id'],
		'name' => $this->auth->member_id['first_name'],
		'user_id' => $this->auth->member_id['user_id'],
		'mod' => $this->mod,
		'category' => (is_isset ('category', $this->data) ? $this->data['category'] : ''),
		'captcha_type' => $this->config['captcha_type'],
		//'link_profile' => $this->mash->sa->link2 (['mod' => 'users', 'action' => 'profile', 'user_id' => 'AJAX']),
		//'link_pm' => $this->mash->sa->link ('pm', 'write', 'AJAX'),
		'is_logged' => $this->auth->is_logged,
		'alt_url' => $this->config['alt_url'],
		'increment' => 0,
		//'comm_type' => $mod_config['comments_type'],
		'user' => $this->data['user'],
		'ajax_connect' => 0,
		'map_width' => $this->mod_config ('map_width'),
		'map_height' => $this->mod_config ('map_height'),
		'map_zoom' => $this->mod_config ('map_zoom'),
		'timestamp' => time (),
		'max_file_size' => ($this->config['max_file_size'] / 1000),
		'max_file_size_show' => mksize ($this->config['max_file_size']),
		'max_file_num' => $this->config['max_file_num'],
		'player_id' => $this->auth->member_id['player_id'],
		'debug' => $this->debug,
		'user_group' => $this->auth->member_id['user_group'],
		'allow_edit_profiles' => (is_isset ($this->auth->member_id['user_group'], $this->auth->user_group) and $this->auth->user_group[$this->auth->member_id['user_group']]['allow_edit_profiles']),
		'google_maps' => false,
		
	];
	
	$js_vars['ajax_dir'] = $js_vars['root_dir'].'/ajax';
	
	if (is_isset ($this->auth->member_id['user_group'], $this->auth->user_group)) {
		
		if ($this->auth->user_group[$this->auth->member_id['user_group']]['allow_edit_stories'] or $this->auth->user_group[$this->auth->member_id['user_group']]['allow_edit_own_stories'])
			$js_vars['allow_edit_stories'] = 1;
		else
			$js_vars['allow_edit_stories'] = 0;
		
		if ($this->auth->user_group[$this->auth->member_id['user_group']]['allow_delete_stories'] or $this->auth->user_group[$this->auth->member_id['user_group']]['allow_delete_own_stories'])
			$js_vars['allow_delete_stories'] = 1;
		else
			$js_vars['allow_delete_stories'] = 0;
		
		if ($this->auth->user_group[$this->auth->member_id['user_group']]['allow_edit_comments'] or $this->auth->user_group[$this->auth->member_id['user_group']]['allow_edit_own_comments'])
			$js_vars['allow_edit_comments'] = 1;
		else
			$js_vars['allow_edit_comments'] = 0;
		
		if ($this->auth->user_group[$this->auth->member_id['user_group']]['allow_delete_comments'] or $this->auth->user_group[$this->auth->member_id['user_group']]['allow_delete_own_comments'])
			$js_vars['allow_delete_comments'] = 1;
		else
			$js_vars['allow_delete_comments'] = 0;
		
	}
	
	if (is_isset ('type', $_GET)) $js_vars['type'] = $_GET['type'];
	
	$js_vars['token'] = md5 ($this->auth->member_id['salt'].$js_vars['timestamp']);
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Заголовок
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	if (!$this->site_offline) {
		
		$this->nav[] = $this->config['site_title'];
		
		$title = implode (' - ', $this->nav);
		
		if ($this->config['site_offline'])
		$title .= $lang['site_offline_text'];
		
	} else $title = 'Сайт временно отключен';
	
	$body = '';
	$headers = '<title>'.spech_encode ($title).'</title>';
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Мета
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	$metas = [
		
		'one_tag' => [
			
			'charset' => $this->locale->charset,
			
		],
		
		'http-equiv' => [
			
			//'X-UA-Compatible' => 'IE=edge',
			//'Expires' => date (DATE_RFC822, strtotime ($this->config['meta_revisit_after'].' day')),
			
		],
		
		'standart' => [
			
			'lang' => $this->locale->lang_code,
			'author' => $this->year_copyright ($this->config['year_open']).' '.$this->config['copyright'],
			'robots' => 'index,follow',
			'viewport' => 'width=device-width, initial-scale=1',
			
		],
		
	];
	
	if (not_empty ($this->config['meta_compatible']))
	$metas['X-UA-Compatible'] = $this->config['meta_compatible'];
	
	$headers .= NL.'		';
	
	foreach ($metas as $name => $values) {
		
		if ($name == 'one_tag') {
			
			foreach ($values as $meta_key => $meta_value)
				$headers .= '
		<meta '.$meta_key.'="'.spech_encode ($meta_value).'"/>';
			
			$headers .= NL.'		';
			
		} elseif ($name == 'standart')
		foreach ($values as $meta_key => $meta_value)
			$headers .= '
		<meta name="'.$meta_key.'" content="'.spech_encode ($meta_value).'"/>';
		elseif ($name == 'open_graph')
		foreach ($values as $meta_key => $meta_value)
			$headers .= '
		<meta property="og:'.$meta_key.'" content="'.spech_encode ($meta_value).'"/>';
		else
		foreach ($values as $meta_key => $meta_value)
			$headers .= '
		<meta '.$name.'="'.$meta_key.'" content="'.spech_encode ($meta_value).'"/>';
		
	}
	
	$headers .= NL.'		';
	
	$this->scripts = '';
	
	if ($this->auth->is_logged/* and !$this->module->is_on*/) { // TODO
		
		$this->auth->member_id['pm_unread'] = (int) $this->auth->member_id['pm_unread'];
		$_COOKIE['pm_unread'] = $this->auth->member_id['pm_unread'];
		
		if ($this->mod != 'pm' and $this->auth->member_id['pm_unread'] and $this->auth->member_id['pm_unread'] >= $_COOKIE['pm_unread']) {
			
			$link_pm = $this->mash->sa->link ('pm', 'inbox');
			$pm_alert = 'pm_alert (\''.$link_pm.'\'); ';
			
			$this->scripts .= '
 $(function () { '.$pm_alert.'});';
			
		}
		
	}
	
	if ($this->scripts) $headers .= echo_js ($this->scripts);
	
	$favicons = [
		
		$this->getRootDir ().'/favicon.ico' => home_url ($this->config['http_host'], 0).'/favicon.ico',
		$this->dirs['templates'].'/favicon.ico' => $this->http_dir['theme'].'/favicon.ico',
		
	];
	
	foreach ($favicons as $f_path => $f_url) if (file_exists ($f_path)) {
		
		$headers .= NL.'		<link type="image/x-icon" href="'.$f_url.'" rel="shortcut icon"/>';
		break;
		
	}
	
	$headers .= NL.'		'.NL;
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Отдельные JavaScripts
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	$header = [];
	
	if ($header) {
		
		$i = 0;
		
		foreach ($header as $key => $value) {
			
			if ($i > 0) $headers .= NL;
			
			if ($key == 'css')
				$headers .= '		'.require_css ($value);
			
			$i++;
			
		}
		
	}
	
	$this->header_settings['type'] = 'css';
	
	$css_files = [
		
		home_url ($this->server['host'], 0).'/minify/minify.php?'.$this->minifierParams ($this->header_settings),
		
	];
	
	if (file_exists ($this->dirs['templates'].'/minify/minify.php')) {
		
		if (isset ($this->auth->member_id['options']['options']['big_font']))
		$this->header_settings['font_size'] = $this->auth->member_id['options']['options']['big_font'];
		
		if (isset ($this->auth->member_id['options']['options']['color']))
		$this->header_settings['color'] = $this->auth->member_id['options']['options']['color'];
		
		$css_files[] = $this->http_dir['theme'].'/minify/minify.php'.($this->header_settings ? '?'.$this->minifierParams ($this->header_settings) : '');
		
	}
	
	foreach ($css_files as $i => $file) {
		
		if ($i > 0) $headers .= NL;
		$headers .= '		'.require_css ($file);
		
	}
	
	$js_files = [];
	
	if ($js_vars['google_maps']) {
		
		$headers .= NL.'		'.NL;
		
		$js_files[] = '//maps.googleapis.com/maps/api/js?'.$this->minifierParams (['language' => $this->locale->lang_code, 'key' => 'AIzaSyBp1uC6FYc73MxTHBcqUoejbdqQlL6E6GA']);
		
	}
	
	foreach ($js_files as $i => $js_script) {
		
		if ($i > 0) $headers .= NL;
		$headers .= '		'.require_js ($js_script);
		
	}
	
	$js_files = [];
	
	$this->header_settings['type'] = 'js';
	$this->header_settings['position'] = 'header';
	
	$js_files[] = home_url ($this->server['host'], 0).'/minify/minify.php?'.$this->minifierParams ($this->header_settings);
	
	if (file_exists ($this->dirs['templates'].'/minify/minify.php')) {
		
		$js_files[] = $this->http_dir['theme'].'/minify/minify.php?'.$this->minifierParams ($this->header_settings);
		
	}
	
	$headers .= NL.'		'.NL;
	
	foreach ($js_files as $i => $js_script) {
		
		if ($i > 0) $headers .= NL;
		$headers .= '		'.require_js ($js_script);
		
	}
	
	/*if ($this->config ('users', 'open_id') and !$this->auth->is_logged) {
		
		$headers .= <<<HTML
<!--[if IE]>
<script type="text/javascript" language="javascript">
 $(document).ready (function () {
	$('.loginza').removeClass ('loginza');
 });
</script>
<![endif]-->
HTML;
		
		$headers .= NL.NL;
		
	}*/
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Переменные для JS
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	$hs_config = ['hs_caption', 'hs_show_thumbs'];
	
	foreach ($hs_config as $key) {
		
		if (!isset ($this->config[$key]))
			$this->config[$key] = false;
		
		$js_vars[$key] = $this->config[$key];
		
	}
	
	if (!$this->site_offline)
		$this->scripts .= $this->load->js_vars ('js_var', $js_vars).NL.'			';
	
	$footers = '';
	if ($this->scripts) $footers .= echo_js ($this->scripts);
	
	if (!$this->site_offline) {
		
		$this->footer_settings['type'] = 'js';
		$this->footer_settings['position'] = 'footer';
		$this->footer_settings['templ'] = $this->config['template'];
		$this->footer_settings['lang'] = $this->locale->lang_code;
		$this->footer_settings['charset'] = $this->locale->charset;
		
		$js_files = [
			
			home_url ($this->server['host'], 0).'/minify/minify.php?'.$this->minifierParams ($this->footer_settings),
			
		];
		
		if (file_exists ($this->dirs['templates'].'/minify/minify.php')) {
			
			$js_files[] = $this->http_dir['theme'].'/minify/minify.php?'.$this->minifierParams ($this->footer_settings);
			
		}
		
		$footers .= NL.'		'.NL;
		
		foreach ($js_files as $i => $js_script) {
			
			if ($i > 0) $footers .= NL;
			$footers .= '		'.require_js ($js_script, 'footer');
			
		}
		
	}
	
	/*if ($mod_config['rss_stories']) $headers .= '<link type="application/rss+xml" rel="alternate" href="'.HOME_URL.'/rss.xml" title="'.spech_encode ($this->config['this->title']).'"/>
';*/
	
	if (file_exists ($this->loadDir ('system').'/require/open_search.php'))		 $headers .= '
		<link type="application/opensearchdescription+xml" rel="search" href="'.HOME_URL.'/system/require/open_search.php" title="'.spech_encode ($this->config['this->title']).'"/>';
	
	if ($this->site_offline)
		$this->tpl->load ('offline');
	elseif ($this->data['mobile'])
		$this->tpl->load ('main.mobile');
	elseif ($this->mod != 'error')
		$this->tpl->load ('main');