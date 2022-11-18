<?php
	
	$_ENV = [];
	
	$this->server['url'] = (!defined ('CLI') ? $_SERVER['HTTP_HOST'] : '');
	
	$this->server['host'] = get_domain ($this->server['url']);
	$this->server['host_nodot'] = str_replace ('.', '', $this->server['host']);
	
	$url_exp = explode ('.', $this->server['url']);
	
	if (count ($url_exp) > 2) {
		
		$this->server['sub_domain'] = $url_exp[0];
		//unset ($url_exp[0]);
		
	} else $this->server['sub_domain'] = '';
	
	$this->server['domain'] = implode ('.', $url_exp);
	$this->server['ip'] = get_ip ();
	
	if (!defined ('CLI')) {
		
		$this->server['agent'] = $_SERVER['HTTP_USER_AGENT'];
		$this->server['robot'] = get_value ($_SERVER['HTTP_USER_AGENT'], $this->robots);
		
	}
	
	$this->server['document_date'] = false;
	$this->server['referer'] = (is_isset ('HTTP_REFERER', $_SERVER) ? parse_url ($_SERVER['HTTP_REFERER']) : '');
	
	if (!is_isset ('http_host', $this->config)) {
		
		if (!$this->server['sub_domain'] or strlen ($this->server['sub_domain']) <= 3)
			$this->config['http_host'] = $this->server['domain'];
		else
			$this->config['http_host'] = $this->server['host'];
		
	}
	
	$this->server['home_url'] = home_url ($this->config['http_host']);
	$this->server['php_self'] = $this->server['home_url'].$_SERVER['PHP_SELF'];