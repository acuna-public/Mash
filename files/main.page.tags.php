<?php
/*
 ========================================
 Mash Framework (c) 2010-2016, 2019
 ----------------------------------------
 https://mash.ointeractive.ru/
 ========================================
 O! Interactive (support@ointeractive.ru)
 ----------------------------------------
 Инициализация главного шаблона main.htm
 ========================================
*/
	
	if (!defined ('MASH')) die ('File must be started only through the main framework cover');
	
	if (!$this->site_offline) {
		
		// \$content = preg_replace_callback \('~\\\[(.+?)=\(\.\+\?\)\\\]\(\.\*\?\)\\\[/(.+?)\\\]~is', function \(\$match\)(.+?), \$content\);
		// \$repl_opt\['\1'\] = function \(\$match\)\3;
		
		// \$content = preg_replace_callback \('~\\\[(.+?)\\\]\(\.\*\?\)\\\[/(.+?)\\\]~is', function \(\$match\)(.+?), \$content\);
		// \$repl\['\1'\] = function \(\$match\)\3;
		
		// \$content = preg_replace_callback \('~\\\{(.+?)=\(\.\+\?\)\\\}~is', function \(\$match\)(.+?), \$content\);
		// \$repl_opt_tag\['\1'\] = function \(\$match\)\2;
		
		if ($this->tpl->found ('{template='))
		$this->tpl->set_preg_c ($this->tpl->preg2 ('tag_opt', 'template'), function ($match) {
			return $this->parse_global_tags ($this->tpl->_load ($match[1]));
		});
		
		$this->tpl->set ('SOURCES', $this->http_dir['sources']);
		//$this->tpl->set ('MBUTTONS', $this->http_dir['sources'].'/mbuttons/'.$config['mbuttons']);
		//$this->tpl->set ('FRICONS', $this->http_dir['sources'].'/fricons/'.$forum_config['fricons']);
		
		$this->tpl->set ('HOME_URL', HOME_URL);
		
		if ($this->tpl->found ('[aviable='))
		$this->tpl->set_preg_c ($this->tpl->preg2 ('hide_opt', 'aviable'), function ($match) {
			return $this->hide_content ($match[1], $match[2], 1);
		});
		
		if ($this->tpl->found ('[not_aviable')) {
			
			$this->tpl->set_preg_c ($this->tpl->preg2 ('hide', 'not_aviable'), function ($match) {
			return $this->hide_content ($match[1], '', 2);
		});
			
			$this->tpl->set_preg_c ($this->tpl->preg2 ('hide_opt', 'not_aviable'), function ($match) {
			return $this->hide_content ($match[1], $match[2], 0);
		});
			
		}
		
		if ($this->tpl->found ('[aviable_nested='))
		$this->tpl->set_preg_c ($this->tpl->preg2 ('hide_opt', 'aviable_nested'), function ($match) {
			return $this->hide_content ($match[1], $match[2], 1);
		});
		
		if ($this->tpl->found ('[not_aviable_nested')) {
			
			$this->tpl->set_preg_c ($this->tpl->preg2 ('hide', 'not_aviable_nested'), function ($match) {
			return $this->hide_content ($match[1], '', 2);
		});
			
			$this->tpl->set_preg_c ($this->tpl->preg2 ('hide_opt', 'not_aviable_nested'), function ($match) {
			return $this->hide_content ($match[1], $match[2], 0);
		});
			
		}
		
		if ($this->tpl->found ('[group='))
		$this->tpl->set_preg_c ($this->tpl->preg2 ('hide_opt', 'group'), function ($match) {
			return $this->auth->check_group ($match[1], $match[2], 1);
		});
		
		if ($this->tpl->found ('[not_group='))
		$this->tpl->set_preg_c ($this->tpl->preg2 ('hide_opt', 'not_group'), function ($match) {
			return $this->auth->check_group ($match[1], $match[2], 0);
		});
		
		if ($this->tpl->found ('{link='))
		$this->tpl->set_preg_c ($this->tpl->preg2 ('tag_opt', 'link'), function ($match) {
			return $this->link ($match[1]);
		});
		
		if ($this->tpl->found ('{date='))
		$this->tpl->set_preg_c ($this->tpl->preg2 ('tag_opt', 'date'), function ($match) {
			return $this->parse_date ($match[1]);
		});
		
		if ($this->tpl->found ('[config='))
		$this->tpl->set_preg_c ($this->tpl->preg2 ('hide_opt', 'config'), function ($match) {
			return $this->hide_config ('general', $match[1], $match[2]);
		});
		
		if ($this->tpl->found ('[mod_config='))
		$this->tpl->set_preg_c ($this->tpl->preg2 ('hide_opt', 'mod_config'), function ($match) {
			return $this->hide_config ('module', $match[1], $match[2]);
		});
		
		if ($this->tpl->found ('[users_config='))
		$this->tpl->set_preg_c ($this->tpl->preg2 ('hide_opt', 'users_config'), function ($match) {
			return $this->hide_config ('users', $match[1], $match[2]);
		});
		
		if ($this->tpl->found ('{config='))
		$this->tpl->set_preg_c ($this->tpl->preg2 ('tag_opt', 'config'), function ($match) {
			return $this->var_config ('general', $match[1]);
		});
		
		if ($this->tpl->found ('{mod_config='))
		$this->tpl->set_preg_c ($this->tpl->preg2 ('tag_opt', 'mod_config'), function ($match) {
			return $this->var_config ('module', $match[1]);
		});
		
		if ($this->tpl->found ('{users_config='))
		$this->tpl->set_preg_c ($this->tpl->preg2 ('tag_opt', 'users_config'), function ($match) {
			return $this->var_config ('users', $match[1]);
		});
		
		if ($this->tpl->found ('[config_status='))
		$this->tpl->set_preg_c ($this->tpl->preg2 ('hide_opt', 'config_status'), function ($match) {
			return $this->config_status ('general', $match[1], $match[2]);
		});
		
		if ($this->tpl->found ('[mod_config_status='))
		$this->tpl->set_preg_c ($this->tpl->preg2 ('hide_opt', 'mod_config_status'), function ($match) {
			return $this->config_status ('module', $match[1], $match[2]);
		});
		
		if ($this->tpl->found ('[users_config_status='))
		$this->tpl->set_preg_c ($this->tpl->preg2 ('hide_opt', 'users_config_status'), function ($match) {
			return $this->config_status ('users', $match[1], $match[2]);
		});
		
		if ($this->tpl->found ('{lang_file='))
		$this->tpl->set_preg_c ($this->tpl->preg2 ('tag_opt', 'lang_file'), function ($match) {
			return $this->var_lang ($match[1], 1);
		});
		
		$this->tpl->set ('mod_title', $this->module->getTitle ());
		//$this->tpl->set ('mod_url', $this->link ($this->data['mod']));
		
		if ($this->tpl->found ('[str_cut='))
		$this->tpl->set_preg_c ($this->tpl->preg2 ('hide_opt', 'str_cut'), function ($match) {
			return $this->str_cut ($match[1], $match[2]);
		});
		
		/*if ($mod_config['rss_stories']) {
			
			$this->tpl->set ('rss');
			$this->tpl->set2 ('/rss');
			
		} else $this->tpl->set_preg ('rss');*/ // TODO
		
		if ($this->tpl->found ('{user='))
		$this->tpl->set_preg_c ($this->tpl->preg2 ('tag_opt', 'user'), function ($match) {
			return $this->user_data ($match[1]);
		});
		
		if ($this->tpl->found ('{email='))
		$this->tpl->set_preg_c ($this->tpl->preg2 ('tag_opt', 'email'), function ($match) {
			return $this->build->email ($match[1]);
		});
		
		if ($this->tpl->found ('[group_right='))
		$this->tpl->set_preg_c ($this->tpl->preg2 ('hide_opt', 'group_right'), function ($match) {
			return $this->auth->check_group_rights ($match[1], $match[2], 1);
		});
		
		if ($this->tpl->found ('[not_group_right='))
		$this->tpl->set_preg_c ($this->tpl->preg2 ('hide_opt', 'not_group_right'), function ($match) {
			return $this->auth->check_group_rights ($match[1], $match[2], 0);
		});
		
		if ($this->tpl->found ('[mod_status='))
		$this->tpl->set_preg_c ($this->tpl->preg2 ('hide_opt', 'mod_status'), function ($match) {
			return $this->mod_status ($match[1], $match[2]);
		});
		
		//if ($full_parse) {
			
			if ($this->tpl->found ('{banner='))
			$this->tpl->set_preg_c ($this->tpl->preg2 ('tag_opt', 'banner'), function ($match) {
				return $this->build->banner ($match[1]);
			});
			
			//if ($this->tpl->found ('{static='))
			//$this->tpl->set_preg_c ($this->tpl->preg2 ('tag_opt', 'static'), function ($match) {
			//	return $this->build->static_page ($match[1]);
			//});
			
			if ($this->tpl->found ('{component='))
			$this->tpl->set_preg_c ($this->tpl->preg2 ('tag_opt', 'component'), function ($match) {
				return $this->loadComponent ('component', $match[1]);
			});
			
			if ($this->config ('torrent', 'status') == 'on') {
				
				$this->tpl->set ('torrent');
				$this->tpl->set2 ('/torrent');
				
			} else $this->tpl->set_preg ('torrent');
			
			if ($this->tpl->found ('[snippet='))
			$this->tpl->set_preg_c ($this->tpl->preg2 ('hide_opt', 'snippet'), function ($match) {
				return $this->loadComponent ('snippet_area', $match[1], $match[2]);
			});
			
			if ($this->tpl->found ('{snippet='))
			$this->tpl->set_preg_c ($this->tpl->preg2 ('tag_opt', 'snippet'), function ($match) {
				return $this->loadComponent ('snippet_tag', $match[1]);
			});
			
			if ($this->tpl->found ('[comp_status='))
			$this->tpl->set_preg_c ($this->tpl->preg2 ('hide_opt', 'comp_status'), function ($match) {
				return $this->comp_status ($match[1], $match[2]);
			});
			
		//}
		
		$search_area = 'stories';
		
		if ($this->module->getType () == 'users') {
			
			//$this->mod_lang ('search_title') = 'по пользователям';
			$search_area = 'users';
			
		}
		
		if (is_isset ('word', $_POST)) $search_value = $this->db->safesql ($_POST['word']); else $search_value = 'Поиск '.$this->mod_lang ('search_title');
		
		$search_options = <<<HTML
name="word" id="word" value="{$search_value}" onfocus="if (this.value == 'Поиск {$this->mod_lang ('search_title')}') this.value = '';" onblur="if (this.value == '') this.value = '{$search_value}';"/><input type="hidden" name="mod" value="search"/><input type="hidden" name="module" value="{$this->mod}"/><input type="hidden" name="area" value="{$search_area}"/><input type="hidden" name="type" value="block"/><input type="hidden" name="subaction" value="search"
HTML;
		
		$this->tpl->set ('search_options', $search_options);
		
		//$this->tpl->set ('pm_unread', $this->build->positive ($this->auth->member_id['pm_unread']));
		$this->tpl->set ('pm_all', $this->auth->member_id['pm_all']);
		
		if ($this->tpl->found ('{category='))
		$this->tpl->set_preg_c ($this->tpl->preg2 ('tag_opt', 'category'), function ($match) {
			return $this->cat_info ($match[1]);
		});
		
		if ($this->tpl->found ('{story='))
		$this->tpl->set_preg_c ($this->tpl->preg2 ('tag_opt', 'story'), function ($match) {
			return $this->story_info ($match[1]);
		});
		
		if ($this->data['is_plugin']) {
			
			$this->tpl->set ('plugin'); $this->tpl->set2 ('/plugin');
			$this->tpl->set_preg ('not_plugin');
			
		} else {
			
			$this->tpl->set ('not_plugin'); $this->tpl->set2 ('/not_plugin');
			$this->tpl->set_preg ('plugin');
			
		}
		
		if ($this->tpl->found ('{lang='))
		$this->tpl->set_preg_c ($this->tpl->preg2 ('tag_opt', 'lang'), function ($match) {
			
			$match[1] = strip_quotes ($match[1]);
			
			if (is_numeric ($match[1]))
			$output = $this->locale->lang ($match[1]);
			else
			$output = $this->var_lang ($match[1]);
			
			return $output;
			
		});
		// TODO
		if ($this->tpl->found ('[music_artist')) {
			
			$this->tpl->set_preg_c ($this->tpl->preg2 ('hide', 'music_artist'), function ($match) {
				return $this->link2 (['mod' => 'music', 'action' => $action, 'id' => $match[1]]);
			});
			
			$this->tpl->set_preg_c ($this->tpl->preg2 ('hide_opt', 'music_artist'), function ($match) {
				
				list ($artist, $sub_id) = explode ('|', strip_quotes ($match[1]));
				return $this->music->link_artist ('artist', $artist, $sub_id);
				
			});
			
		}
		
		if ($this->tpl->found ('[music_member')) {
			
			$this->tpl->set_preg_c ($this->tpl->preg2 ('hide', 'music_member'), function ($match) {
				
				$title_row = $this->aux->artist_name ($match[1]);
				return $this->music->link_artist ('member', $title_row['title'], $title_row['sub_id']);
				
			});
			
			$this->tpl->set_preg_c ($this->tpl->preg2 ('hide_opt', 'music_member'), function ($match) {
				
				list ($artist, $sub_id) = explode ('|', strip_quotes ($match[1]));
				return $this->music->link_artist ('member', $artist, $sub_id);
				
			});
			
		}
		
		if ($this->tpl->found ('[music_album'))
		$this->tpl->set_preg_c ($this->tpl->preg2 ('hide_opt', 'music_album'), function ($match) {
			
			list ($artist, $album) = explode ('|', strip_quotes ($match[1]));
			
			return a_link ($this->link2 (['mod' => 'music', 'action' => 'album', 'artist' => $artist, 'album' => $album]), spech_encode ($match[2]), $this->auth->is_new_window ());
			
		});
		
		if ($this->tpl->found ('[music_label')) {
			
			$this->tpl->set_preg_c ($this->tpl->preg2 ('hide', 'music_label'), function ($match) {
				
				$title_row = $this->aux->artist_name ($match[1]);
				return $this->music->link_label ('label', 0, $title_row['title'], $title_row['sub_id'], $match[2]);
				
			});
			
			$this->tpl->set_preg_c ($this->tpl->preg2 ('hide_opt', 'music_label'), function ($match) {
				
				list ($artist, $sub_id) = explode ('|', strip_quotes ($match[1]));
				return $this->music->link_label ('label', 0, $artist, $sub_id, $match[2]);
				
			});
			
		}
		
		if ($this->tpl->found ('[music_studio')) {
			
			$this->tpl->set_preg_c ($this->tpl->preg2 ('hide', 'music_studio'), function ($match) {
				
				$title_row = $this->aux->artist_name ($match[1]);
				return $this->music->link_label ('studio', 0, $title_row['title'], $title_row['sub_id'], $match[2]);
				
			});
			
			$this->tpl->set_preg_c ($this->tpl->preg2 ('hide_opt', 'music_studio'), function ($match) {
				
				list ($artist, $sub_id) = explode ('|', strip_quotes ($match[1]));
				return $this->music->link_label ('studio', 0, $artist, $sub_id, $match[1]);
				
			});
			
		}
		
		$this->tpl->set ('year_copyright', $this->year_copyright ($this->config['year_open'], 1));
		
		$int_tags = ['friends_invites_num', 'groups_invites_num', 'groups_demands_num'];
		
		foreach ($int_tags as $tag) {
			
			if (is_isset ('social_'.$tag, $this->auth->member_id) and $this->auth->member_id['social_'.$tag] > 0) {
				
				$this->tpl->set_strip ($tag);
				$this->tpl->set_preg ('not_'.$tag);
				
				$this->tpl->set_strip ('new_demands');
				$this->tpl->set_preg ('not_new_demands');
				
			} else {
				
				$this->tpl->set_strip ('not_'.$tag);
				$this->tpl->set_preg ($tag);
				
				$this->tpl->set_preg ('new_demands');
				$this->tpl->set_strip ('not_new_demands');
				
			}
			
			$this->tpl->set ($tag, (is_isset ('social_'.$tag, $this->auth->member_id) ? $this->auth->member_id['social_'.$tag] : 0));
			
		}
		
		if ($this->auth->member_id['pm_unread'] > 0) {
			
			$this->tpl->set_strip ('pm_unread');
			$this->tpl->set ('pm_unread_num', $this->auth->member_id['pm_unread']);
			
		} else {
			
			$this->tpl->set_preg ('pm_unread');
			$this->tpl->set ('pm_unread_num', 0);
			
		}
		
		$this->tpl->set ('friends_num', $this->auth->member_id['friends_num']);
		
		if ($this->isMainMod ()) {
			
			if ($this->auth->is_logged) {
				
				$this->tpl->set_strip ('social_logged');
				$this->tpl->set_preg ('social_not_logged');
				
			} else {
				
				$this->tpl->set_preg ('social_logged');
				$this->tpl->set_strip ('social_not_logged');
				
			}
			
		} else {
			
			$this->tpl->set_strip ('social_logged');
			$this->tpl->set_preg ('social_not_logged');
			
		}
		
		$this->tpl->set ('site_title', $title);
		
		$this->tpl->set ('headers', $headers);
		
		$this->tpl->set ('body', $body);
		
		if ($this->tpl->result['info']) {
			
			$this->tpl->result['info'] = '<!-- #info --><div id="lisas_info">
					'.$this->tpl->result['info'].'
				</div>
				<!-- #info -->';
				
		}
		
		$this->tpl->set ('info', $this->tpl->result['info']);
		
		$this->tpl->set ('content', '<!-- #content -->
							
'.$this->tpl->result['content'].'
							
							<!-- #content -->');
		
		$this->tpl->set ('footers', $footers);
		
		/*$lang_name = $this->locale['langs']['code']['locale'][$this->locale->lang_code]['title'];
		if (!$lang_name) $lang_name = '(не известно)';
		
		$this->tpl->set ('lang_name', $lang_name);
		$this->tpl->set ('lang_code', $this->locale->lang_code);*/
		
		/*$this->tpl->set_preg_c ('\{styles=(.*?)\}', function ($item) {
			
			return strip_quotes ($item[1]);
			
		});*/
		
		$this->data['share'] = [];
		
		if ($this->data['share']) {
			
			if (!$this->data['share']['url'])
			$this->data['share']['url'] = HOME_URL.$_SERVER['REQUEST_URI'];
			
			$this->tpl->set ('share_url', url_encode ($this->data['share']['url'], 0));
			
			if (!$this->data['share']['title'])
			$this->data['share']['title'] = $this->config['site_title'];
			$this->tpl->set ('share_title', $this->data['share']['title'].' - '.$this->data['share']['type']);
			
			if (!$this->data['share']['descr'])
			$this->data['share']['descr'] = $this->config['description'];
			$this->tpl->set ('share_descr', $this->data['share']['descr']);
			
			if (!$this->data['share']['image']) $this->data['share']['image'] = '';
			$this->tpl->set ('share_image', url_encode ($this->parse_home_url ($this->data['share']['image']), 0));
			
			$share_counts = [];
			
			$this->tpl->set ('share_counts', ($share_counts ? url_encode (array2json ($share_counts), 0) : ''));
			
		}
		
		$this->tpl->set_cond ((is_isset ('title', $this->data['share']) ? $this->data['share']['title'] : ''), 'share');
		
	} else require $this->loadDir ('system').'/require/offline.php';