<?php
	
	$this->config = array_extend ($this->config, $this->load->config);
	
	$this->config['meta_revisit_after'] = intval_correct ($this->config['meta_revisit_after'], 1);
	
	$this->config['upload_dir'] = str_replace ('{home_url}', $this->server['domain'], $this->config['upload_dir']);
	
	$this->config['upload_data'] = json_decode ($this->config['upload_data'], true);
	
	if (!not_empty ($this->config['timestamp_active'])) $this->config['timestamp_active'] = 'j F Y';
	//$this->config['global_tpl'] = intval_correct ($this->config['global_tpl']);
	
	$this->config['doctype'] = stripslashes ($this->config['doctype']);
	
	$this->config['email_theme_dir'] = $this->getRootDir ().'/templates_email';
	
	if (!$this->config['http_host'])
		$this->config['http_host'] = $this->server['url'];
	
	define ('HOME_URL', home_url ($this->config['http_host']));
	
	$home_url = home_url ($this->config['http_host'], 0);
	
	$this->http_dir = [
		
		'home_url' => $home_url,
		'php_self' => HOME_URL.'/index.php',
		'uploads' => HOME_URL.'/uploads',
		'system' => HOME_URL.'/system',
		'sources' => HOME_URL.'/sources',
		'theme' => $home_url.'/templates/'.$this->config['template'],
		'admin_theme' => home_url ($this->server['domain']).'/templates_admin/'.$this->config['admin_template'],
		'mail_theme_url' => home_url ($this->config['static_domain']).'/templates_email/'.$this->config['mail_template'],
		
	];
	
	$this->dirs['templates'] = $this->loadDir ('templates').'/'.$this->config['template'];