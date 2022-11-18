<?php
/*
 ========================================
 Mash Framework (c) 2010-2016
 ----------------------------------------
 https://mash.ointeractive.ru/
 ========================================
 O! Interactive (support@ointeractive.ru)
 ----------------------------------------
 Класс дополнительных полей
 (реально удобная вешь!)
 ========================================
*/
	
	if (!defined ('MASH')) die ('File must be started only through the main framework cover');
	
	class XFields {
		
		public
		$allowed_types = ['jpg', 'swf'], // Расширения файлов, допустимые к загрузке через поле типа "file".
		$uploads_dir = '.',
		$http_uploads_dir = '.',
		$db = 'xfields',
		$deny_fields = [], // Это вам не надо!
		$types = [],
		$templ = '',
		$base = '', $callbacks = [],
		$debug = 0;
		
		private
		$fields = [
			
			'text' => ['text', 'email', 'phone', 'link_search', 'search_text'],
			'textarea' => ['textarea'],
			'lower' => ['email', 'phone'],
			
		];
		
		private $mash;
		
		function __construct ($mash) {
			$this->mash = $mash;
		}
		
		private function field_type ($field_type, $array_type) {
			return in_array ($field_type, $this->fields[$array_type]);
		}
		
		private function _view ($row, $parse, $xf_data, $author = '', $cat_id = 0, $deny_fields = []) {
			
			if ($this->allow_field ($row['status'], $row['view'], $author) and !in_array ($row['name'], $deny_fields) and !in_array ($row['name'], $this->deny_fields)) {
				
				if ($xf_data)
				$xf_value = $parse->show_content ($xf_data[$row['name']], ['wysiwyg' => 0]);
				else
				$xf_value = '';
				
				if ($row['type'] == 'file' and not_empty ($xf_value)) {
					
					$xf_value = $this->http_uploads_dir.'/'.$xf_value;
					$this->mash->tpl->set ('file_show', get_filename ($auth->trim_date ($xf_value), 1));
					
					$this->mash->tpl->set2 ('file_show');
					$this->mash->tpl->set2 ('/file_show');
					
				} else $this->mash->tpl->set_preg ('file_show');
				
				$this->mash->tpl->set ('value', $xf_value);
				
				$options = '';
				$this->mash->tpl->set ('options', $options);
				
				if ($row['size']) {
					
					$this->mash->tpl->set2 ('size');
					$this->mash->tpl->set2 ('/size');
					
					$row['size'] = $row['size'].'px';
					
				} else {
					
					if ($this->field_type ($row['type'], 'textarea')) $row['size'] = '80px'; else $row['size'] = '';
					$this->mash->tpl->set_preg ('size');
					
				}
				
				$this->mash->tpl->set ('size', $row['size']);
				
				if (!$row['type']) $row['type'] = 'text';
				
				if ($row['oblige']) {
					
					$this->mash->tpl->set2 ('oblige');
					$this->mash->tpl->set2 ('/oblige');
					
				} else $this->mash->tpl->set_preg ('oblige');
				
				switch ($row['type']) {
					
					default: case 'text':
						
						$this->mash->tpl->set2 ('field_text');
						$this->mash->tpl->set2 ('/field_text');
						
						$this->mash->tpl->set_preg ('field_textarea');
						$this->mash->tpl->set_preg ('field_select');
						$this->mash->tpl->set_preg ('field_file');
						$this->mash->tpl->set_preg ('field_file_del');
						$this->mash->tpl->set_preg ('field_password');
						$this->mash->tpl->set_preg ('field_birthdate');
						$this->mash->tpl->set_preg ('field_skills');
						$this->mash->tpl->set_preg ('field_sex');
						
					break;
					
					case 'textarea':
						
						$this->mash->tpl->set2 ('field_textarea');
						$this->mash->tpl->set2 ('/field_textarea');
						
						$this->mash->tpl->set_preg ('field_text');
						$this->mash->tpl->set_preg ('field_select');
						$this->mash->tpl->set_preg ('field_file');
						$this->mash->tpl->set_preg ('field_file_del');
						$this->mash->tpl->set_preg ('field_password');
						$this->mash->tpl->set_preg ('field_birthdate');
						$this->mash->tpl->set_preg ('field_skills');
						$this->mash->tpl->set_preg ('field_sex');
						
					break;
					
					case 'password':
						
						$this->mash->tpl->set2 ('field_password');
						$this->mash->tpl->set2 ('/field_password');
						
						$this->mash->tpl->set_preg ('field_text');
						$this->mash->tpl->set_preg ('field_textarea');
						$this->mash->tpl->set_preg ('field_select');
						$this->mash->tpl->set_preg ('field_file');
						$this->mash->tpl->set_preg ('field_file_del');
						$this->mash->tpl->set_preg ('field_birthdate');
						$this->mash->tpl->set_preg ('field_skills');
						$this->mash->tpl->set_preg ('field_sex');
						
					break;
					
					case 'select':
						
						$this->mash->tpl->set2 ('field_select');
						$this->mash->tpl->set2 ('/field_select');
						
						$this->mash->tpl->set_preg ('field_text');
						$this->mash->tpl->set_preg ('field_textarea');
						$this->mash->tpl->set_preg ('field_file');
						$this->mash->tpl->set_preg ('field_file_del');
						$this->mash->tpl->set_preg ('field_password');
						$this->mash->tpl->set_preg ('field_birthdate');
						$this->mash->tpl->set_preg ('field_skills');
						$this->mash->tpl->set_preg ('field_sex');
						
						$this->mash->tpl->set ('select', $this->mash->build->dropdown2 (sep_explode ($row['content'])));
						
					break;
					
					case 'file':
						
						$this->mash->tpl->set2 ('field_file');
						$this->mash->tpl->set2 ('/field_file');
						
						if ($xf_data[$row['name']]) {
							
							$this->mash->tpl->set2 ('file_del');
							$this->mash->tpl->set2 ('/file_del');
							
						} else $this->mash->tpl->set_preg ('file_del');
						
						$this->mash->tpl->set_preg ('field_textarea');
						$this->mash->tpl->set_preg ('field_select');
						$this->mash->tpl->set_preg ('field_text');
						$this->mash->tpl->set_preg ('field_password');
						$this->mash->tpl->set_preg ('field_birthdate');
						$this->mash->tpl->set_preg ('field_skills');
						$this->mash->tpl->set_preg ('field_sex');
						
					break;
					
					case 'birthdate':
						
						$this->mash->tpl->set2 ('field_birthdate');
						$this->mash->tpl->set2 ('/field_birthdate');
						
						$this->mash->tpl->set_preg ('field_text');
						$this->mash->tpl->set_preg ('field_textarea');
						$this->mash->tpl->set_preg ('field_select');
						$this->mash->tpl->set_preg ('field_file');
						$this->mash->tpl->set_preg ('field_file_del');
						$this->mash->tpl->set_preg ('field_password');
						$this->mash->tpl->set_preg ('field_skills');
						$this->mash->tpl->set_preg ('field_sex');
						
						list ($bdate_y, $bdate_m, $bdate_d) = explode ('-', $xf_data[$row['name']]);
						
						if (!(int) $bdate_y) $bdate_y = '';
						$bdate_m = $this->mash->build->dropdown ($auth->locale['dates']['r'], '', $bdate_m, 0, 1);
						
						$bdate_d_array = [];
						for ($i = 1; $i <= 31; ++$i) $bdate_d_array[add_zero ($i)] = $i;
						
						$bdate_d = $this->mash->build->dropdown ($bdate_d_array, '', $bdate_d, 0, 1);
						
						$bdate_view_array = array ($auth->lang (490), $auth->lang (491), $auth->lang (492));
						$bdate_view_a = $this->mash->build->dropdown ($bdate_view_array, '', $xf_data['bdate_view'], 0);
						
						$this->mash->tpl->set ('bdate_view', $bdate_view_a);
						
						$this->mash->tpl->set ('bdate_y', $bdate_y);
						$this->mash->tpl->set ('bdate_m', $bdate_m);
						$this->mash->tpl->set ('bdate_d', $bdate_d);
						
					break;
					
					case 'skills':
						
						$this->mash->tpl->set2 ('field_skills');
						$this->mash->tpl->set2 ('/field_skills');
						
						$this->mash->tpl->set_preg ('field_text');
						$this->mash->tpl->set_preg ('field_textarea');
						$this->mash->tpl->set_preg ('field_select');
						$this->mash->tpl->set_preg ('field_file');
						$this->mash->tpl->set_preg ('field_file_del');
						$this->mash->tpl->set_preg ('field_birthdate');
						$this->mash->tpl->set_preg ('field_password');
						$this->mash->tpl->set_preg ('field_sex');
						
						$this->mash->tpl->set ('skills', $this->mash->sa->skills_selector ($xf_value));
						
					break;
					
					case 'sex':
						
						$this->mash->tpl->set2 ('field_sex');
						$this->mash->tpl->set2 ('/field_sex');
						
						$this->mash->tpl->set_preg ('field_text');
						$this->mash->tpl->set_preg ('field_textarea');
						$this->mash->tpl->set_preg ('field_select');
						$this->mash->tpl->set_preg ('field_file');
						$this->mash->tpl->set_preg ('field_file_del');
						$this->mash->tpl->set_preg ('field_birthdate');
						$this->mash->tpl->set_preg ('field_password');
						$this->mash->tpl->set_preg ('field_skills');
						
						$this->mash->tpl->set ('field_sex', $this->mash->build->dropdown (array (1 => $auth->lang (225), 2 => $auth->lang (226)), '', $xf_value));
						
					break;
					
				}
				
				$title = $auth->int_lang ($row['title']);
				$name = 'xfield['.$row['name'].']';
				
				$this->mash->tpl->set ('title', $title);
				$this->mash->tpl->set ('name', $name);
				
				if ($row['this_id']) $name = 'xfield-'.$row['this_id'];
				$this->mash->tpl->set ('id', $this->mash->build->parse_id ($name));
				
				$this->mash->tpl->set_cond ($row['check'], 'check');
				
				$this->mash->tpl->compile ('xfields');
				
			}
			
		}
		
		function view ($xf_data = [], $author = '', $cat_id = 0, $deny_fields = []) { // Вывод шаблона
			
			if (!is_array ($xf_data)) $xf_data = $this->mash->tdb->super_query ('select', '', $xf_data);
			
			$this->mash->tpl->load_templ ($this->target ($this->templ));
			
			$rows = $this->fields_array ($cat_id);
			
			foreach ($rows as $row)
			$this->_view ($row, $parse, $xf_data, $author, $cat_id, $deny_fields);
			
		}
		
		function db_write ($post, $xf_data = [], $deny_fields = []) { // Форматирование для записи в БД
			
			$data = $this->check ($post, $xf_data, $deny_fields);
			
			if ($data['error'])
			$data = $data['error'];
			else
			$data = $this->implode ($data['content']);
			
			return $data;
			
		}
		
		function bases ($base, $templ) {
			
			$this->base = $base;
			$this->templ = $templ;
			
		}
		
		function check ($post, $xf_data = [], $deny_fields = []) { // Подготовка к записи в БД
			
			$xf_data = $this->mash->tdb->super_query ('select', '', $xf_data);
			
			$content = [];
			$error = [];
			
			$rows = $this->fields_array ();
			
			foreach ($rows as $row) {
				
				if (!in_array ($row['name'], $deny_fields) and $row['write']) {
					
					switch ($row['type']) {
						
						default:
							
							if (!$row['length']) $row['length'] = 100;
							$content[$row['name']] = str_correct ($post[$row['name']], ['add_slashes' => false, 'str_cut_length' => $row['length']]);
							
							//if (!$content[$row['name']]) $error[] = 'ddd';
							
						break;
						
						case 'textarea':
							
							if (!$row['length']) $row['length'] = 1000;
							$content[$row['name']] = $parse->write_content (str_cut ($post[$row['name']], $row['length']), ['wysiwyg' => 0, 'filter' => 0]);
							
						break;
						
						case 'select':
							
							$options = sep_explode ($row['content']);
							
							//if ((int) $row[4] or $this->mash->auth->member_id['user_group'] == 1 or $row['type'])
							//$content[$row['name']] = $options[$post[$row['name']]];
							$content[$row['name']] = $post[$row['name']];
							//else
							//$content[$row['name']] = $xf_data[$row['name']];
							
						break;
						
						case 'file':
							
							$tmp_name = $_FILES['xfield']['tmp_name'][$row['name']];
							$name = $_FILES['xfield']['name'][$row['name']];
							
							$type = get_filetype ($name);
							$file_error = $_FILES['xfield']['error'][$row['name']];
							$size = $_FILES['xfield']['size'][$row['name']];
							
							if ($row['content']) $this->allowed_types = sep_explode ($row['content']);
							
							if ($tmp_name and !in_array ($type, $this->allowed_types)) $error[] = $this->mash->sa->lang ('upload_error_1', [$this->allowed_types, $type]);
							if ($this->mash->sa->max_file_size ($size, $lisas->config['max_file_size'])) $error[] = $this->mash->sa->lang ('upload_error_2', [$lisas->config['max_file_size'], $size]);
							
							make_dir ($this->uploads_dir.'/'.$this->mod.'/'.date ('Y_m'));
							
							if ($tmp_name) {
								
								$filetype = get_filetype ($name);
								$name = secure_filename ($name, $filetype);
								
								$full_name = $this->filepath ($name);
								
								if (copy ($tmp_name, $full_name)) { // Запись файла на сервер
									
									if ($xf_data[$row['name']])
									unlink ($this->filepath ($xf_data[$row['name']])); // Старый удаляем, если есть
									
									$content[$row['name']] = $name;
									
								} else die ('Не удалось загрузить файл!');
								
							} elseif ($post['del_file']) { // Чекбокс удаления
								
								if ($xf_data[$row['name']])
								unlink ($this->filepath ($xf_data[$row['name']]));
								
							} else $content[$row['name']] = $xf_data[$row['name']];
							
						break;
						
					}
					
					if ($row['oblige'] and !not_empty ($content[$row['name']]))
					$error[] = [1, $row['title']];
					
					if ($row['type'] == 'email' and not_empty ($content[$row['name']]) and !is_email ($content[$row['name']]))
					$error[] = [2, $row['title']];
					
					if ($row['match'] and $content[$row['name']] and !preg_match ('~'.$row['match'].'~i', $content[$row['name']]))
					$error[] = str_replace ('{title}', $auth->int_lang ($row['title']), $auth->int_lang ($row['error']));
					
				}
				
			}
			
			$output = ['content' => $content, 'error' => $error];
			
			return $output;
			
		}
		
		function target ($mod) {
			return $this->db.'/'.$mod;
		}
		
		function filepath ($file) {
			return $this->uploads_dir.'/'.$file;
		}
		
		function url_filepath ($file) {
			return $this->http_uploads_dir.'/'.$file;
		}
		
		function allow_field ($status, $view, $author) {
			
			$show = 0;
			
			if ($status == 1 or !isset ($status)) {
				
				if (!$view) $show = 1;
				elseif ($view == 1 and !not_empty ($author)) $show = 1;
				elseif ($view == 1 and not_empty ($author) and $this->mash->auth->member_id['name'] == $author) $show = 1;
				elseif ($view == 2 and $this->mash->auth->is_logged) $show = 1;
				elseif ($view == 3 and !$this->mash->auth->is_logged) $show = 1;
				
			}
			
			return $show;
			
		}
		
		function parse ($content, $xf_data, $author = '', $cat_id = 0) { // Парсинг контента из БД
			
			$xf_data = $this->explode ($xf_data);
			$rows = $this->fields_array ($cat_id);
			
			foreach ($rows as $row) {
				
				$cont2 = '';
				$name = preg_quote ($row['name'], "'");
				$cont = stripslashes ($xf_data[$row['name']]);
				
				if ($this->allow_field ($row['status'], $row['view'], $author) and trim ($cont) != '') {
					
					if (!$row['length']) $row['length'] = 100;
					
					switch ($row['type']) {
						
						default:
							$cont = str_correct ($cont, ['str_cut_length' => $row['length']]);
						break;
						
						case 'email':
							$cont = str_correct ($cont, ['ucfirst' => 0, 'str_cut_length' => $row['length']]);
						break;
						
						case 'select':
							
							$select = sep_explode ($row['content']);
							$cont2 = (int) $cont - 1;
							$cont2 = $select[intval_correct ($cont2)];
							
						break;
						
						case 'file':
							$cont = $this->url_filepath ($cont);
						break;
						
						case 'link_search':
							$cont = $this->callbacks[$row['type']] ($cont, $row);
						break;
						
						case 'link_search_array':
							
							$output = [];
							foreach (sep_explode ($cont) as $cont)
							$output[] = $this->callbacks['link_search'] ($cont, $row);
							
							$cont = sep2_implode ($output);
							
						break;
						
					}
					
					$content = str_replace ('[xfields]', '', $content);
					$content = str_replace ('[/xfields]', '', $content);
					
					$content = preg_replace ('~\{xfield=[\"\']'.$name.'[\"\']\}~i', $cont, $content);
					$content = preg_replace ('~\[xfield=[\"\']'.$name.'[\"\']\](.*?)\[/xfield\]~si', '\\1', $content);
					
					if ($cont2) {
						
						$content = preg_replace ('~\{xfield_id=[\"\']'.$name.'[\"\']\}~i', $cont2, $content);
						$content = preg_replace ('~\[xfield_id=[\"\']'.$name.'[\"\']\](.*?)\[/xfield_id\]~si', '\\1', $content);
						
					}
					
				} else {
					
					$content = preg_replace ('~\[xfields\](.*?)\[/xfields\]~si', '', $content);
					
					$content = preg_replace ('~\{xfield=[\"\']'.$name.'[\"\']\}~i', '', $content);
					$content = preg_replace ('~\[xfield=[\"\']'.$name.'[\"\']\](.*?)\[/xfield\]~si', '', $content);
					
					if ($cont2) {
						
						$content = preg_replace ('~\{xfield_id=[\"\']'.$name.'[\"\']\}~i', '', $content);
						$content = preg_replace ('~\[xfield_id=[\"\']'.$name.'[\"\']\](.*?)\[/xfield_id\]~si', '', $content);
						
					}
					
				}
				
			}
			
			return $content;
			
		}
		
		function parse_areas ($content) {
			
			$content = preg_replace_callback ('~\[xfield=(.+?)\](.*?)\[/xfield\]~is', function ($match) { return $this->_parse_areas ($match[1], $match[2]); }, $content);
			
			return $content;
			
		}
		
		function _parse_areas ($area, $content) {
			
			$area = strip_quotes ($area);
			$data = $this->mash->tdb->array_query ($this->target ($this->base));
			if (!$data[$area]['status']) $content = '';
			
			return stripslashes ($content);
			
		}
		
		function edit_parse ($content, $data) {
			
			$this->mash->tpl->load_templ ($this->target ($this->templ));
			
			if (strpos ($content, '{xfield_edit=') !== false)
			$content = preg_replace_callback ('~\{xfield_edit=(.+?)\}~is', function ($match) use ($data) {
				return $this->_edit_parse ($match[1], $data);
			}, $content);
			
			return $content;
			
		}
		
		function explode ($str) {
			return array2json (trim ($str, $tunnel_sep[0]));
		}
		
		function implode ($array) {
			return array2json ($array, 0, 1);
		}
		
		private function _edit_parse ($name, $data) {
			
			$parse = new lisas_parse ();
			$name = strip_quotes ($name);
			$this->mash->tpl->result['xfields'] = '';
			
			$user_data = $this->explode ($data);
			$field_data = $this->mash->tdb->array_query ($this->target ($this->base));
			
			$this->_view ($field_data[$name], $parse, $user_data);
			$output = $this->mash->tpl->result['xfields'];
			
			return $output;
			
		}
		
		function selector ($name, $xf_value = '') {
			
			$rows = $this->mash->tdb->get_row ($this->target ($this->base));
			
			foreach ($rows as $row) {
				
				$row = $this->mash->tdb->super_query ('select', '', $row);
				
				if ($row['name'] == $name) {
					
					if ($xf_value)
					$selected = '';
					else
					$selected = ' selected';
					
					$select .= '<option value=""'.$selected.'>&nbsp;</option>
';
					
					$i = 0;
					$content = sep_explode ($row['content']);
					
					foreach ($content as $value) {
						++$i;
						
						if ($xf_value and $i == $xf_value)
						$selected = ' selected';
						else
						$selected = '';
						
						$value = str_replace ("'", "&#039;", $value);
						
						$select .= '<option value="'.$i.'"'.$selected.'>'.$value.'</option>
';
						
					}
					
				}
				
			}
			
			return $select;
			
		}
		
		function is_oblige ($input, $field_data) {
			
			$array = [];
			
			foreach ($input as $xf_name => $xf_value)
			if ($field_data[$xf_name]['oblige'] and !not_empty ($xf_value, 1))
			$array[] = $field_data[$xf_name]['title'];
			
			return $array;
			
		}
		
		function get_options ($input, $type, $field_data, $row_type = 'type', $row_title = 'title') {
			
			$array = [];
			
			foreach ($input as $input_name => $input_value)
			if ($field_data[$input_name][$row_type] == $type) $array[] = ['name' => $input_name, 'value' => $input_value, 'title' => $field_data[$input_name][$row_title]];
			
			return $array;
			
		}
		
		function fields_array ($cat_id = 0) {
			return $this->mash->tdb->get_row2 ($this->target ($this->base), 'asc', $cat_id, (($this->debug == 2) ? 1 : 0));
		}
		
		function col_data ($col = 'type', $cat_id = 0) {
			
			$output = [];
			$rows = $this->fields_array ($cat_id);
			
			foreach ($rows as $row) {
				
				if ($col == 'type')
				$output[$row[$col]][] = $row;
				else
				$output[$row[$col]] = $row;
				
			}
			
			return $output;
			
		}
		
	}