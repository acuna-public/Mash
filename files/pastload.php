<?php
	
	if ($this->sites ()) {
		
		$this->sites = $this->db->query (['select', '', 'sites', [], [], ['cache' => ['col' => 'url']]]);
		$this->mirrors = $this->db->query (['select', '', 'sites_mirrors', ['url' => $this->server['url']], [], ['cache' => ['col' => 'url']]]);
		
	}
	
	if (is_isset ($this->server['url'], $this->sites)) {
		
		$this->site = $this->sites[$this->server['url']];
		
		if (!$this->site['approve'])
			throw new MashException ('Site '.$this->site['url'].' is disabled');
		
	} elseif (is_isset ($this->server['url'], $this->mirrors)) {
		
		$this->mirror = $this->mirrors[$this->server['url']];
		
		if (!$this->mirror['approve'])
			throw new MashException ('Site mirror '.$this->mirror['url'].' is disabled');
		
		$sites = [];
		
		foreach ($this->sites as $key => $value)
			$sites[$value['id']] = $value;
		
		$this->site = $sites[$this->mirror['site_id']];
		
	} else {
		
		$this->site['id'] = 1;
		$this->site['url'] = $this->server['url'];
		
	}
	
	if ($this->site['url'] == $this->server['sub_domain'].'.'.$this->server['domain']) {
		
		$this->server['domain'] = $this->server['sub_domain'].'.'.$this->server['domain'];
		unset ($this->server['sub_domain']);
		
	}
	
	if ($this->modules ())
		$this->modulesData = $this->db->query (['select', '', 'modules', [], ['order' => [['position', 'asc']]], ['cache' => ['col' => 'name']]]);
	
	$this->modTypes = is_isset ($this->mod, $this->modulesData) ? sep_explode ($this->modulesData[$this->mod]['sub_type']) : [];
	
	if (
		$this->module->getType () == 'stories' and
		$this->module->getName () and
		(!is_isset ('global_fm_tables', $this->config) or !$this->config['global_fm_tables'])
	)
		$this->moduleTable = $this->module->getName ();
	
	define ('WHERE_2', 'WHERE '.$this->db->addquotes ('site_id').' = '.$this->db->value ($this->site['id']));
	define ('WHERE', WHERE_2.' AND');
	
	if ($this->config['global_register'])
		$where_user = 'WHERE '.$this->db->addquotes ('site_id').' != '.$this->db->value (0);
	else
		$where_user = WHERE_2;
	
	define ('WHERE_USER_2', $where_user);
	define ('WHERE_USER', $where_user.' AND');
	
	if ($this->config['global_groups'])
		$where_group = 'WHERE '.$this->db->addquotes ('site_id').' != '.$this->db->value (0);
	else
		$where_group = WHERE_2;
	
	define ('WHERE_GROUP_2', $where_group);
	define ('WHERE_GROUP', $where_group.' AND');
	
	//$this->load->load_kernel ();
	
	/**
	 
	 Загрузка ядра. Полученные переменные:
	 
	 $this->kernel['product'] - продукт
	 $this->kernel['version'] - версия движка
	 $this->kernel['edition'] - редакция движка
	 $this->kernel['date']		- дата данной сборки 
	 $this->kernel['hash']		- хеш данной сборки 
	 $this->kernel['key']		 - серийный номер системы
	 
	*/
	
	$this->version = (is_isset ('version', $this->kernel) ? $this->kernel['version'] : '1.0');