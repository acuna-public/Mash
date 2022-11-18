<?php
/*
 ========================================
 Mash Framework (c) 2010-2016
 ----------------------------------------
 https://mash.ointeractive.ru/
 ========================================
 O! Interactive (support@ointeractive.ru)
 ----------------------------------------
 Класс парсера
 ========================================
*/
	
	if (!defined ('MASH')) die ('File must be started only through the main framework cover');
	
	// = function \(\$match\) \{ (.+?) \};
	// = function \(\$match\) \{\n\t\t\t\t\1\n\t\t\t\};\n\t\t\t
	
	class Parse {
		
		public
			$allow_php = false,
			$allow_code = true,
			$allow_image = false,
			$allow_url = true,
			$options = ['filter' => 1],
			$xss = true,
			$decode_tags = [],
			$debug = 0,
			$text_length = 0,
			$lite_parse = false,
			$word_wrap = 30,
			$callbacks = [],
			$smiles_dir = '',
			$smiles_pack = 0,
			$smiles_data = [],
			$providers = [];
		
		private
			$allowed_tags = [],
			$standart_tags = ['b', 'i', 'u', 's'],
			$deny_attr = [],
			$single_tags = ['br', 'hr', 'input', 'img', 'col'],
			$filter_mode = true,
			$font_sizes = [1 => 8, 2 => 10, 3 => 12, 4 => 14, 5 => 18, 6 => 24, 7 => 36],
			$config = [ 'editor_type' => 'text', 'smiles' => '1'],
			$wiki_url = 'http://ru.wikipedia.org/wiki',
			$lisas_wiki_url = 'http://wiki.mash.github.io/wiki',
			$filter_lower_attr_value = [],
			$code_text = [],
			$code_count = 0,
			$replace_tags = ['strong' => 'b'],
			$replace_tags2 = ['<span style="padding-left:15px;"></span>' => 'tab'],
			$align_tags = ['left', 'right', 'center', 'justify'],
			$editor_types = ['text', 'html', 'bbcodes', 'wysiwyg'];
		
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
		private $mash;
		
		function __construct ($mash, $config = []) {
			
			$this->mash = $mash;
			
			$this->config = array_extend ($config, $this->config);
			
			if (!in_array ($this->config['editor_type'], $this->editor_types))
			$this->config['editor_type'] = 'text';
			
			if ($this->config['editor_type'] != 'text') {
				
				foreach ($this->standart_tags as $tag)
				$this->allowed_tags[] = strtolower ($tag);
				
				if ($this->config['editor_type'] == 'wysiwyg') {
					
					$allowed_tags = ['div', 'span', 'p', 'strong', 'em', 'ul', 'li', 'ol', 'center'];
					
					foreach ($allowed_tags as $tag)
					$this->allowed_tags[] = strtolower ($tag);
					
					foreach ($this->single_tags as $tag)
					$this->allowed_tags[] = strtolower ($tag);
					
					if ($this->allow_url) $this->allowed_tags[] = 'a';
					for ($i = 1; $i <= 6; ++$i) $this->allowed_tags[] = 'h'.$i;
					
					if ($this->allow_image) {
						
						$this->allowed_tags[] = 'img';
						$this->allowed_tags[] = 'iframe';
						
					}
					
				}
				
			}
			
			$this->allowed_tags = array_unique ($this->allowed_tags);
			$this->deny_attr = array_unique ($this->deny_attr);
			
			//print_r ($this->allowed_tags);
			//print_r ($this->deny_attr);
			
			//if ($mod == 'static') $this->decode_tags[] = 'template';
			
		}
		
		function setProvider (Parser\Provider $provider) {
			$this->providers[] = $provider;
		}
		
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		// Функции парсинга
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
		function decode ($type, $content) {
			
			switch ($type) {
				
				case 'code':
					$output = '<div class="bb-'.$type.'">'.$content.'</div>';
				break;
				
			}
			
			return $output;
			
		}
		
		private function replaces ($txt, $data, $data_preg) {
			
			$txt = stripslashes ($txt);
			
			foreach ($data as $find => $replace)
			$txt = str_replace ($find, $replace, $txt);
			
			foreach ($data_preg as $find => $replace)
			$txt = preg_replace ($find, $replace, $txt);
			
			return $txt;
			
		}
		
		public $data = [], $data_preg = [], $data_preg_c = [];
		
		function toHTML ($content) { // Форматирование контента при выводе
			
			$content = strip_slashes ($content);
			
			$this->data = [];
			$this->data_preg = [];
			$this->data_preg_c = [];
			
			foreach ($this->providers as $provider)
				$provider->newInstance ($this)->toHTML ();
			
			foreach ($this->data_preg_c as $find => $replace)
				$content = preg_replace_callback ('~'.$find.'~si', $replace, $content);
			
			return $content;
			
		}
		
		function fromHTML ($content) { // Кодирует html в bb (для редактирования)
			
			$content = strip_slashes ($content);
			$content = $this->word_filter ($content, $this->filter_mode);
			
			foreach ($this->decode_tags as $tag)
			$content = preg_replace ('~\{'.$tag.'=(.+?)\}~is', '&~123;'.$tag.'=\\1&~125;', $content);
			
			$this->data = [];
			$this->data_preg = [];
			$this->data_preg_c = [];
			
			if ($this->config['editor_type'] != 'text') {
				
				if ($this->config['editor_type'] == 'wysiwyg') {
					
					$this->data_preg_c['~\[img\](.*?)\[/img\]~is'] = function ($match) {
						return $this->build_image ($match[1]);
					};
					
					$this->data_preg_c['~\[img\s*=(.+?)\s*\](.*?)\s*\[/img\]~is'] = function ($match) {
						return $this->build_image ($match[2], $match[1]);
					};
					
				}
				
				$this->data_preg_c[$this->area_encode ('quote')] = function ($match) {
					return $match[1];
				};
				
				$this->data_preg_c[$this->area_encode ('code')] = function ($match) {
					return $this->encode ('code', $match[1]);
				};
				
				$this->data_preg_c[$this->area_encode ('image')] = function ($match) {
					return $this->encode_image ($match[1]);
				};
				
				$this->data_preg_c[$this->area_encode ('thumb')] = function ($match) {
					return $this->encode_image ($match[1], 'thumb');
				};
				
				foreach ($this->standart_tags as $tag)
				$this->data_preg['~<'.$tag.'>(.+?)</'.$tag.'>~is'] = '['.$tag.']\\1[/'.$tag.']';
				
				foreach ($this->replace_tags as $key => $value)
				$this->data_preg['~<'.$key.'>(.+?)</'.$key.'>~is'] = '['.$value.']\\1[/'.$value.']';
				
				foreach ($this->replace_tags2 as $key => $value)
				$this->data[$key] = '['.$value.']';
				
				$this->data_preg_c['~<img.*?src=\'?\'?([^\"\'>]+)\"?\'?.*?>~is'] = function ($match) {
					return $this->encode_image ('', 'img', $match[1]);
				};
				/*$this->data_preg_c['~<a.*?href=\"?'?mailto:([^\"\'>]+)\"?\'?.*?>(.*?)</a>~is'] = function ($match) {
					return $this->encode ('email', $match[2], $match[1]);
				};
				*/
				
				$tags = ['color', 'font'];
				
				foreach ($tags as $tag) {
					
					switch ($tag) {
						
						case 'color': $tag2 = 'color'; break;
						case 'font': $tag2 = 'font-family'; break;
						
					}
					
					$this->data_preg['~<span style="'.$tag2.':(.+?);">(.*?)</span>~is'] = '['.strtolower ($tag).'=\\1]\\2[/'.strtolower ($tag).']';
					
				}
				
				$this->data_preg_c['~<span style="font\-size\:(.+?)pt; line-height\:100%;">(.*?)</span>~is'] = function ($match) {
					return $this->encode_font_size ($match[1], $match[2]);
				};
				
				foreach ($this->align_tags as $tag)
				$this->data_preg['~<div style="text\-align:'.$tag.';">(.*?)</div>~is'] = '[align='.strtolower ($tag).']\\1[/align]';
				
				$this->data_preg_c[$this->area_encode ('wiki')] = function ($match) {
					return $this->encode_wiki ($match[1]);
				};
				
				$this->data_preg['~\&\#91;attachment=(.*?)\&\#93;~is'] = '<p>&~91;attachment=\\1&~93;</p>';
				
				$this->data_preg_c['~<a.*?href=["\'](.*?)["\'].*?>(.*?)</a>~is'] = function ($match) {
					return $this->url_encode ($match[1], $match[2]);
				};
				
				foreach ($this->smile ($this->smiles_pack) as $smile) { // Парсим смайлы из папки
					
					$name = get_filename ($smile);
					
					$this->data_preg_c[$this->area_encode ('smile\-'.$name)] = function ($match) use ($name) {
						
						return preg_replace_callback ('~<img alt="'.$name.'" src="(.*?)"/>~si', function ($match2) {
							return $this->encode_smile ($match2[1]);
						}, $match[1]);
						
					};
					
				}
				
			}
			
			foreach ($this->providers as $provider)
				$provider->newInstance ($this)->fromHTML ();
			
			foreach ($this->data as $find => $replace)
			$content = str_replace ($find, $replace, $content);
			
			foreach ($this->data_preg as $find => $replace)
			$content = preg_replace ($find, $replace, $content);
			
			foreach ($this->data_preg_c as $find => $replace)
			$content = preg_replace_callback ($find, $replace, $content);
			
			// ~~~~~~~~~~~~~~~~~~~~~~~~~~~
			
			if ($this->config['editor_type'] != 'wysiwyg') $content = br2nl ($content);
			
			//if ($this->config['editor_type'] == 'wysiwyg')
			//$content = spech_hard_encode ($content);
			
			return $content;
			
		}
		
		private function encode_smile ($src) {
			
			$smiles = $this->smiles_data ()['replaces'];
			$name = get_filename ($src);
			$symbol = $smiles[$name];
			
			$src = explode_filepath ($src);
			$pack = $src[deduction (end_key ($src), 1)];
			
			if ($pack != $this->smiles_pack)
			$smile = '[smile='.$pack.']'.$name.'[/smile]';
			elseif ($symbol = $smiles[$name])
			$smile = $symbol;
			else
			$smile = ':'.$name.':';
			
			return $smile;
			
		}
		
		private function smiles_data ($pack = 0) {
			
			if (!$pack) $pack = $this->smiles_pack;
			return $this->smiles_data[$pack];
			
		}
		
		private function url_encode ($href, $title = '') {
			
			$href = strip_quotes ($href);
			$title = strip_quotes ($title);
			
			if (get_domain ($href) == get_domain ($title) or !$title)
			$output = $href;
			else
			$output = '[url='.$title.']'.$href.'[/url]';
			
			if ($call = $this->callbacks['url_encode'])
			$output = $call ($output, $href, $title, $this->config);
			
			return $output;
			
		}
		
		function bb_lite_encode ($content) {
			
			$data = [];
			$data_preg = [];
			$data_preg['~(<br[^>]*>){4,}?~i'] = '';
			
			// ~~~~~~~~~~~~~~~~~~~~~~~~~~~
			
			foreach ($data as $find => $replace)
			$content = str_replace ($find, $replace, $content);
			
			foreach ($data_preg as $find => $replace)
			$content = preg_replace ($find, $replace, $content);
			
			// ~~~~~~~~~~~~~~~~~~~~~~~~~~~
			
			return $content;
			
		}
		
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
		function encode_smiles ($content) { // Делает смайлы нечитаемыми для парсера
			return str_replace ([':', ']', '('], ['&~58;', '&~41;', '&~40;'], $content);
		}
		
		private function _clean ($text) {
			
			//debug_html ($text);
			
			$text = br_clean ($text);
			
			$data = [
				
				//BR.'<!--Area:' => '<!--Area:',
				'<!--/Area-->'.BR.BR => '<!--/Area-->'.BR,
				//'<!--/Area-->'.BR => '<!--/Area-->',
				
			];
			
			foreach ($data as $find => $replace)
			$text = str_replace ($find, $replace, $text);
			
			$data = array (
				
				//'<div style="text\-align:\s*(left|center|right);">\s*</div>' => '<div style="text\-align:\\1;">&nbsp;</div>',
				'<p><div style="text\-align:\s*(left|center|right);">(.*?)</div>(.*?)</p>' => '<div style="text\-align:\\1;">\\2</div>',
				
			);
			
			foreach ($data as $find => $replace)
			$text = preg_replace ('~'.$find.'~is', $replace, $text);
			
			return $text;
			
		}
		
		function write_content ($content, $options = [], $debug = 0) { // Декодирует bb в html (для записи)
			
			$data_preg = []; $data_preg_c = []; $data_preg2 = [];
			
			//$content = decode_system_tags ($content);
			
			//$content = preg_replace_callback ("~\[no_parse\](.*?)\[/no_parse\]~is", function ($match) { return $this->decode_type ('no_parse', $match[1]); }, $content);
			
			$content = str_correct ($content, ['plain' => false, 'str_cut_length' => $this->text_length, 'word_wrap' => $this->word_wrap, 'add_slashes' => false]);
			
			$content = $this->filter ($content);
			
			if ($this->code_count) {
				
				foreach ($this->code_text as $key_find => $key_replace) {
					
					$find[] = $key_find;
					$replace[] = $key_replace;
					
				}
				
				$content = str_replace ($find, $replace, $content);
				
			}
			
			$this->code_count = 0;
			$this->code_text = [];
			
			$content = correct_html (lisas_nl2br ($content));
			//$content = add_parse_slashes ($content);
			
			$data = [];
			
			//foreach (is_url ($content, 1) as $url)
			//if ($link = $aux->url_reduct ($url))
			//$data[$url] = a_link ($link, $link, 2);
			
			// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
			
			if ($this->config['editor_type'] == 'bbcodes' or $this->config['editor_type'] == 'wysiwyg') {
				
				$aligns = implode ('|', $this->align_tags);
				
				//$data_preg['~<p style="text\-align:\s*('.$aligns.');">(.*?)</p>~si'] = '<span style="text\-align:\\1;">\\2</span>';
				
				if (!$this->lite_parse) {
					
					$data_preg['~<center>(.*?)</center>~is'] = '<span style="text\-align:center;">\\1</span>';
					
					foreach ($this->align_tags as $tag)
					$data_preg['~\[align='.$tag.'\](.*?)\[/align\]~si'] = '<span style="text\-align:\\1;">\\2</span>';
					
					foreach ($this->replace_tags2 as $key => $value)
					$data['['.$value.']'] = $key;
					
					$data_preg_c['~\[size=\s*(.+?)\s*\](.*?)\[/size\]~is'] = function ($match) {
						return $this->decode_font ('size', $match[1], $match[2]);
					};
					
					$data_preg_c['~\[font=\s*(.+?)\s*\](.*?)\[/font\]~is'] = function ($match) {
						return $this->decode_font ('font', $match[1], $match[2]);
					};
					
					$data_preg_c['~\[color=\s*(.+?)\s*\](.*?)\[/color\]~is'] = function ($match) {
						return $this->decode_font ('color', $match[1], $match[2]);
					};
					
					$data_preg_c['~\[img\](.*?)\[/img\]~is'] = function ($match) {
						return $this->decode_image ($match[1], $this->config['tag_img_width'], '', 0, 0, $this->allow_image);
					};
					
					$data_preg_c['~\[img\s*=(.+?)\s*\](.*?)\s*\[/img\]~is'] = function ($match) {
						return $this->decode_image ($match[2], $this->config['tag_img_width'], $match[1], 0, 0, $this->allow_image);
					};
					
					$data_preg_c['~\[thumb\](.*?)\[/thumb\]~is'] = function ($match) {
						return $this->decode_image ($match[1], $this->config['tag_img_width'], '', 1, 1, $this->allow_image);
					};
					
					$data_preg_c['~\[thumb\s*=(.+?)\s*\](.*?)\s*\[/thumb\]~is'] = function ($match) {
						return $this->decode_image ($match[2], $this->config['tag_img_width'], $match[1], 1, 1, $this->allow_image);
					};
					
				}
				
			}
			
			if ($this->config['editor_type'] != 'text') {
				
				foreach ($this->standart_tags as $tag)
				$content = preg_replace ('~\['.$tag.'\](.*?)\[/'.$tag.'\]~si', '<'.$tag.'>\\1</'.$tag.'>', $content);
				
				$data_preg_c['~\[wiki\](.*?)\s*\[/wiki\]~is'] = function ($match) {
					return $this->decode_type ('wiki', 'wiki', $match[1]);
				};
				
				$data_preg_c['~\[wiki\s*=(.+?)\s*\](.*?)\s*\[/wiki\]~is'] = function ($match) {
					return $this->decode_type ('wiki', 'wiki', $match[1], $match[2]);
				};
				
				$data_preg_c['~\[lisas_wiki\](.*?)\s*\[/lisas_wiki\]~is'] = function ($match) {
					return $this->decode_type ('wiki', 'lisas', $match[1]);
				};
				
				$data_preg_c['~\[lisas_wiki\s*=(.+?)\s*\](.*?)\s*\[/lisas_wiki\]~is'] = function ($match) {
					return $this->decode_type ('wiki', 'lisas', $match[1], $match[2]);
				};
				
				$data_preg_c['~\[code\](.*?)\[/code\]~is'] = function ($match) {
					return $this->decode_code ($match[1]);
				};
				
				$data_preg_c['~\[url\](.*?)\[/url\]~is'] = function ($match) {
					
					return a_link (preg_replace_callback ('~<a.*?href=["\'](.*?)["\'].*?>(.*?)</a>~is', function ($match2) {
						return $this->url_encode ($match2[1]);
					}, $match[1]), $match[1], 2);
					
				};
				
				$data_preg_c['~\[url\s*=(.+?)\s*\](.*?)\s*\[/url\]~is'] = function ($match) use ($aux) {
					
					$match[1] = $aux->explode_opt ($match[1]);
					
					return a_link (preg_replace_callback ('~<a.*?href=["\'](.*?)["\'].*?>(.*?)</a>~is', function ($match2) {
						return $this->url_encode ($match2[1], $match2[2]);
					}, $match[2]), $match[1][0], 2);
					
				};
				
				$data_preg_c['~\[leech\](.*?)\[/leech\]~is'] = function ($match) {
					return $this->url_decode ($match[1], '', $this->allow_url, 1);
				};
				
				$data_preg_c['~\[leech\s*=(.+?)\s*\](.*?)\s*\[/leech\]~is'] = function ($match) {
					return $this->url_decode ($match[1], $match[2], $this->allow_url, 1);
				};
				
				if (strpos ($content, '[quote') !== false) {
					
					$data_preg_c['~\[quote\](.*?)\[/quote\]~is'] = function ($match) {
						return $this->decode_quote ($match[1]);
					};
					
					$data_preg_c['~\[quote=(.+?)\](.*?)\[/quote\]~is'] = function ($match) {
						return $this->decode_quote ($match[2], $match[1]);
					};
					
				}
				
				$smiles = $this->smiles_data ();
				
				if ($smiles['replaces'])
				foreach ($smiles['replaces'] as $find => $replace)
				$data[$replace] = $this->decode_smile ($this->smiles_pack, $find.'.'.$smiles['exp'], get_filename ($find));
				
				foreach ($this->smile ($this->smiles_pack) as $smile) { // Парсим смайлы из папки
					
					$name = get_filename ($smile);
					$data[':'.$name.':'] = $this->decode_smile ((int) $this->smiles_pack, $smile, $name);
					
					$data_preg_c['~\[smile=(.+?)\]('.$name.')\[/smile\]~is'] = function ($match) {
						
						$match[1] = (int) $match[1];
						$smiles = $this->smiles_data ($match[1]);
						
						if (is_file ($this->smiles_dir.'/'.$match[1].'/'.$match[2].'.'.$smiles['exp']))
						$match[0] = $this->decode_smile ($match[1], $match[2], $match[2], 0);
						
						return $match[0];
						
					};
					
				}
				
			}
			
			$data_preg2['~>{2,}~'] = '&gt;&gt;';
			$data_preg2['~<{2,}~'] = '&lt;&lt;';
			
			// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
			
			if ($call = $this->callbacks['write_content'])
			foreach ($call['preg_c'] ($content, $this->config) as $key => $value)
			$data_preg_c[$key] = $value;
			
			//foreach ($this->plugin ('class', 'parse', 'write_content') as $plugin) require $plugin;
			
			foreach ($data as $find => $replace)
			$content = str_ireplace ($find, $replace, $content);
			
			foreach ($data_preg as $find => $replace)
			$content = preg_replace ($find, $replace, $content);
			
			foreach ($data_preg_c as $find => $replace)
			$content = preg_replace_callback ($find, $replace, $content);
			
			foreach ($data_preg2 as $find => $replace)
			$content = preg_replace ($find, $replace, $content);
			
			//debug_html ($content);
			$content = $this->_clean ($content);
			
			// ~~~~~~~~~~~~~~~~~~~~~~~~~~~
			
			//$content = encode_system_tags ($content);
			
			/*$data = array (
				
				'~&#91;media_artist&#93;(.*?)&#91;/media_artist&#93;~is' => '[media_artist]\\1[/media_artist]',
				'~&#91;media_artist\s*=(.+?)\s*&#93;(.*?)\s*&#91;/media_artist&#93;~is' => '[media_artist=\\1]\\2[/media_artist]',
				
				'~&#91;media_album\s*=(.+?)\s*&#93;(.*?)\s*&#91;/media_album&#93;~is' => '[media_album=\\1]\\2[/media_album]',
				
			);
			
			foreach ($data as $find => $replace)
			$content = preg_replace ($find, $replace, $content);
			
			foreach ($this->decode_tags as $tag)
			$content = preg_replace ('~&#123;'.$tag.'=(.+?)&#125;~is', '{'.$tag.'=\\1}', $content);*/
			
			$content = trim_br (add_slashes ($content));
			//debug_html ($content);
			
			return $content;
			
		}
		
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		// Функции кодирования отдельных частей
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
		private function decode_smile ($pack, $smile, $name, $exp = 1) {
			
			if (!$exp and $data = $this->smiles_data ($pack)) {
				
				if (!$data['exp']) $data['exp'] = 'png';
				$smile .= '.'.$data['exp'];
				
			}
			
			$src = $this->smiles_url.'/'.$pack.'/'.$smile;
			
			$code = '<img alt="'.$name.'" src="'.$src.'"/>';
			
			return $this->area_decode ('smile-'.$name, $code);
			
		}
		
		function encode ($type, $txt, $url = '') {
			
			$options = '';
			$data = [];
			//$html = str_get_html ($txt);
			
			if ($type == 'code') {
				
				$e = $html->find ('div[class=bb-scriptcode]', 0);
				$content = $e->plaintext;
				
			} elseif ($type == 'url' or $type == 'email') {
				
				if ($url) {
					
					$url = $this->_home_url ($url);
					$url = str_replace ('&amp;', '&', $url);
					$options .= '='.$url;
					
				}
				
			}
			
			return '['.$type.$options.']'.$content.'[/'.$type.']';
			
		}
		
		private function _decode_leech ($url) {
			
			if (strpos ($url, '/out.php') !== false) {
				
				$url = str_replace ('==', '', $url);
				$url = explode ('?', $url);
				$url = end ($url);
				$url = explode ('=', $url);
				
				$url = @base64_decode (rawurldecode (end ($url)));
				$url = str_replace ('&amp;', '&', $url);
				
			}
			
			return $url;
			
		}
		
		private function parse_url ($url, $title) {
			return $this->url_decode ($url, str_correct ($title, ['ucfirst' => false]));
		}
		
		function encode_raw_url ($url, $title, $leech = 1) {
			
			if ($leech) $url = $this->_decode_leech ($url);
			return a_link ($url, $title);
			
		}
		
		function encode_font_size ($size, $txt) {
			
			$size = array_search ($size, $this->font_sizes);
			return '[size='.$size.']'.$txt.'[/size]';
			
		}
		
		function encode_tag ($type, $title, $options) {
			
			if ($options) $options = '='.$options;
			$type = strtolower ($type);
			
			return '['.$type.$options.']'.$title.'[/'.$type.']';
			
		}
		
		function encode_image ($txt, $type = 'img', $url = '') {
			
			$html = str_get_html (strip_slashes ($txt));
			$alt = ''; $align = ''; $options = '';
			
			$e = $html->find ('img', 0);
			$alt = $e->alt;
			
			if (not_empty ($alt, 1)) {
				
				$find = ['&#039;', '&quot;', '&amp;'];
				$replace = ['"', '\'', '&'];
				
				$options .= str_replace ($find, $replace, $alt);
				
			}
			
			$e = $html->find ('img', 0);
			$align = $e->align;
			//if ($align != 'left' and $align != 'right') $align = 'center';
			
			if ($options) $options = '='.$options;
			
			if (!$url) {
				
				if ($type == 'thumb') {
					
					$e = $html->find ('a', 0);
					$url = $e->href;
					
				} else {
					
					$e = $html->find ('img', 0);
					$url = $e->src;
					
				}
				
			}
			
			if (check_home ($url)) {
				
				$this_url = lisas_parse_url ($url);
				$url = '/'.$this_url['full_query'];
				
			}
			
			$output = '['.$type.$options.']'.$url.'[/'.$type.']';
			if ($align) $output = '['.$align.']'.$output.'[/'.$align.']';
			
			return $output;
			
		}
		
		function encode_wiki ($txt) {
			
			$options = '';
			$html = str_get_html ($txt);
			
			$e = $html->find ('a', 0);
			$url = $e->href;
			
			$word = url_decode (url_end ($url));
			$title = $e->plaintext;
			
			if (get_domain ($url) == get_domain ($this->lisas_wiki_url)) $type = 'lisas_wiki'; else $type = 'wiki';
			
			if ($word != $title) $options .= $word;
			
			return $this->encode_tag ($type, $title, $options);
			
		}
		
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		// Функции декодирования отдельных частей (запись)
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
		function decode_quote ($text, $title = '') {
			
			$text = lisas_ucfirst (trim_br ($text));
			
			if ($title)
			$output = '[quote='.$title.']'.$text.'[/quote]';
			else
			$output = '[quote]'.$text.'[/quote]';
			
			return $this->area_decode ('quote', $output);
			
		}
		
		function decode_image ($url, $img_width, $options = '', $link = 0, $thumb = 0, $build_img = 1, $alt = '') {
			
			$alt = ''; $size = '';
			
			$url = $this->url_decode ($url, '', 0, 0, 1);
			$url2 = $this->url_decode ($url, '', 0);
			$filename = get_filename ($url, 1);
			
			if ($build_img) {
				
				$option = explode ('|', strip_quotes ($options));
				$area = 'image';
				
				if ($option[0]) $alt = str_correct ($option[0], ['str_cut_length' => 200, 'word_wrap' => true, 'word_wrap_length' => $this->config['word_wrap']]);
				$alt = spech_rss_decode ($alt);
				
				$image_link = $this->_home_url ($url, 0);
				
				if ($thumb) {
					
					$area = 'thumb';
					
					$align = $option[1];
					if ($align == 'left' or $align == 'right') $align = ' align="'.$align.'"';
					
					$thumb_url = str_replace ($filename, 'thumbs/'.$filename, $url);
					$image_link .= '" onclick="return hs.expand (this);';
					
				}
				
				if ($thumb_url) $url = $thumb_url;
				$size = image_css_resize (getimagesize ($url2), 0, $img_width);
				
				$image_url = $this->_home_url ($url, 0);
				
				$output = '<div class="image"><img alt="'.$alt.'" src="'.$image_url.'"'.$align.' style="'.$size.'border:none;"/></div>';
				
				if ($link) $output = a_link ($image_link, $output);
				if ($thumb) $output = $this->area_decode ($area, $output);
				
			}
			
			return $output;
			
		}
		
		function decode_font ($type, $style, $text) {
			
			$style = preg_replace (array ('~^(.+?)(?:;|$)~', '~\[&\(\)\.%\[\]<>\'\"\]~', '~\[^\d\w\#\-\_\s\]~s'), ['\\1', '', ''], $style);
			
			if ($type == 'size') {
				
				$style = (int) $style;
				if ($this->font_sizes[$style]) $text = '<span style="font-size:'.$this->font_sizes[$style].'pt; line-height:100%;">'.$text.'</span>';
				
			} elseif ($type == 'font') $text = '<span style="font-family:'.$style.';">'.$text.'</span>';
			elseif ($type == 'color') $text = '<span style="color:'.$style.';">'.$text.'</span>';
			
			return $text;
			
		}
		
		function prep_images ($txt, $height) {
			
			$txt = preg_replace_callback ('~\[img\](.*?)\[/img\]~is', function ($match) use ($height) {
				return $this->decode_image ($match[1], $height, '', 0, 0, 1);
			}, $txt);
			
			$txt = preg_replace_callback ('~\[img\s*=(.+?)\s*\](.*?)\s*\[/img\]~is', function ($match) use ($height) {
				return $this->decode_image ($match[2], $height, $match[1], 0, 0, 1);
			}, $txt);
			
			return $txt;
			
		}
		
		function url_decode ($url, $title = '', $build_link = 1, $leech = 0, $is_image = 0, $url_blank = 0, $debug = 0) {
			
			$options = [];
			$url = strip_quotes ($url);
			
			if ($title) { // Заголовок
				
				$title = str_replace (['&nbsp;', '&amp;amp;'], [' ', '&amp;'], strip_slashes ($title));
				$title = preg_replace ('~javascript:~i', 'javascript&~58;', $title);
				
				$url_denied_exp = '/([\.,\?]|&~33;)$/';
				if (preg_match ($url_denied_exp, $title)) $title = preg_replace ($url_denied_exp, '', $title);
				
				$title = trim ($title);
				
			}
			
			// URL
			
			$url = urldecode ($this->get_domain ($url));
			if (!$is_image) $url = $this->_home_url ($url);
			
			if (!preg_match ('~^(http|news|https|ed2k|ftp|aim|mms)://|(magnet:?)~', $url)) $url = 'http://'.$url;
			
			if (!$this->check_home ($url) or $this->options['url_blank']) {
				
				$options['target'] = 'blank';
				$options['rel'] = 'nofollow';
				
			}
			
			//foreach ($this->plugin ('class', 'parse', 'url_decode') as $plugin) require $plugin;
			
			$raw_url = $url;
			
			if (!$this->check_home ($url) and strpos ($url, HOME_URL.'/'.$global_config['admin_path']) === false and /*strpos ($url, HOME_URL.'/'.$global_config['super_admin_path']) === false and */$url[0] != '/' and $leech) { // Анлич
				
				$url = $this->mash->sa->link ('leech', $url);
				$blank = 1;
				
			} else $url = $this->_decode_leech ($url);
			
			if (!$title) $title = $raw_url;
			$url = trim ($url);
			
			if ($build_link) $url = a_link ($url, $title, $options);
			
			return $url;
			
		}
		
		function _home_url ($url, $site_url = 1) {
			
			if ($site_url) $sheme = HOME_URL; else $sheme = '';
			
			$url2 = url_no_sheme ($url);
			if ($url2[0] == '/') $url = $sheme.$url2;
			return $url;
			
		}
		
		function decode_noparse ($content) {
			
			$content = '[noparse]'.decode_system_tags (htmlspecialchars ($content)).'[/noparse]';
			
			return $content;
			
		}
		
		function decode_code ($code) {
			
			/*$code = br2nl (spech_decode ($code));
			$code = str_replace (["\t", '\\'], ['	', '\\\\'], $code);
			
			if (lisas_substr ($code, 0, 2) != '<?')
			$code = '<?php
	'.$code.'
?>';
			
			$code = highlight_string ($code, true);
			$code = str_replace (['&amp;#'], ['&#'], $code);*/
			
			return $this->area_decode ('code', '<div class="bb-scriptcode">'.trim ($code).'</div>');
			
		}
		
		function decode_type ($type, $option1, $option2 = '', $option3 = '') { // Декодер по типам
			
			if ($type == 'wiki') {
				
				if ($option1 == 'lisas') $url = $this->lisas_wiki_url; else $url = $this->wiki_url;
				
				$option2 = url_end ($option2);
				$link = $url.'/'.url_encode (lisas_ucfirst (str_replace (' ', '_', $option2)));
				
				if (!$option3) $option3 = $option2;
				
				$output = $this->area_decode ('wiki', a_link ($link, str_correct ($option3), ['target' => 'blank', 'rel' => 'nofollow']));
				
			} elseif ($type = 'no_parse') {
				
				$output = spech_encode ($option1);
				$output = encode_system_tags ($output);
				$output = $this->encode_smiles ($output);
				
			}
			
			return $output;
			
		}
		
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		// Функции примитивных построений объектов
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
		function build_image ($url, $options = '') {
			
			$alt = '';
			$options = explode ('|', $options);
			if ($options[0]) $alt = $options[0];
			
			$align = $options[1];
			if ($align != 'left' and $align != 'right') $align = 'center';
			
			return '<img alt="'.$alt.'" src="'.$url.'" style="text-align:'.$align.'; border:none;"/>';
			
		}
		
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		// Функции очистки кода
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
		function filter ($txt) { // Корректируем теги
			
			//if ($this->options['filter'])
			while ($txt != $this->_filter ($txt))
			$txt = $this->_filter ($txt);
			
			return $txt;
			
		}
		
		private function _filter ($source) {
			
			$pre_tag = '';
			$post_tag = $source;
			
			$open_tag_start = strpos ($source, '<');
			
			while ($open_tag_start !== false) {
				
				$pre_tag .= substr ($post_tag, 0, $open_tag_start);
				$post_tag = substr ($post_tag, $open_tag_start);
				
				$fromopen_tag = substr ($post_tag, 1);
				$open_tag_end = strpos ($fromopen_tag, '>');
				
				if ($open_tag_end === false) break;
				$open_tag_nested = strpos ($fromopen_tag, '<');
				
				if ($open_tag_nested !== false and $open_tag_nested < $open_tag_end) {
					
					$pre_tag .= substr ($post_tag, 0, ($open_tag_nested + 1));
					$post_tag = substr ($post_tag, ($open_tag_nested + 1));
					$open_tag_start = strpos ($post_tag, '<');
					
					continue;
					
				}
				
				$open_tag_nested = (strpos ($fromopen_tag, '<') + $open_tag_start + 1);
				$current_tag = substr ($fromopen_tag, 0, $open_tag_end);
				$tag_length = strlen ($current_tag);
				
				if (!$open_tag_end) {
					
					$pre_tag .= $post_tag;
					$open_tag_start = strpos ($post_tag, '<');
					
				}
				
				$left_tag = $current_tag;
				$space = strpos ($left_tag, ' ');
				
				if (substr ($current_tag, 0, 1) == '/') {
					
					$is_close_tag = true;
					list ($tag_name) = explode (' ', $current_tag);
					$tag_name = substr ($tag_name, 1);
					
				} else {
					
					$is_close_tag = false;
					list ($tag_name) = explode (' ', $current_tag);
					
				}
				
				$tag_name = strtolower ($tag_name);
				$tag_name = str_replace ('/', '', $tag_name);
				
				/*if (
					
					$this->xss and (
						
						!$tag_name or
						!preg_match ('~^[a-z0-9]*$~i', $tag_name) or
						!in_array ($tag_name, $this->allowed_tags)
						
					)
					
				) { // Удаляем запрещенные теги
					
					$post_tag = substr ($post_tag, ($tag_length + 2));
					$open_tag_start = strpos ($post_tag, '<');
					
					continue;
					
				}*/
				
				$set_attr = [];
				
				while ($space !== false) {
					
					$start_space = substr ($left_tag, ($space + 1));
					$next_space = strpos ($start_space, ' ');
					$open_quote = strpos ($start_space, '"');
					$close_quote = strpos (substr ($start_space, ($open_quote + 1)), '"') + ($open_quote + 1);
					
					if (strpos ($start_space, '=') !== false) {
						
						if ($open_quote !== false and strpos (substr ($start_space, ($open_quote + 1)), '"') !== false)
						$attr = substr ($start_space, 0, ($close_quote + 1));
						else
						$attr = substr ($start_space, 0, $next_space);
						
					} else $attr = substr ($start_space, 0, $next_space);
					
					if (!$attr) $attr = $start_space;
					$set_attr[] = $attr;
					
					$left_tag = substr ($start_space, strlen ($attr));
					
					$space = strpos ($left_tag, ' ');
					
				}
				
				if (in_array ($tag_name, $this->allowed_tags)) {
					$lt = '<'; $gt = '>';
				} else {
					$lt = '&lt;'; $gt = '&gt;';
				}
				
				$pre_tag = $this->_prepare_tags ($lt, $gt, $pre_tag, $is_close_tag, $fromopen_tag, $set_attr, $tag_name);
				
				$post_tag = substr ($post_tag, ($tag_length + 2));
				$open_tag_start = strpos ($post_tag, '<');
				
			}
			
			$pre_tag .= $post_tag;
			
			return $pre_tag;
			
		}
		
		private function _prepare_tags ($lt, $gt, $pre_tag, $is_close_tag, $fromopen_tag, $set_attr, $tag_name) {
			
			if (!$is_close_tag) {
				
				$set_attr = $this->_filter_attr ($set_attr, $tag_name);
				$pre_tag .= $lt.$tag_name;
				
				for ($i = 0; $i < count ($set_attr); ++$i) $pre_tag .= ' '.$set_attr[$i];
				
				if (strpos ($fromopen_tag, $gt.'/'.$tag_name) and in_array ($tag_name, $this->single_tags)) $pre_tag .= '/'.$gt; else $pre_tag .= $gt;
				
				//debug ($fromopen_tag);
				
			} else $pre_tag .= $lt.'/'.$tag_name.$gt;
			
			return $pre_tag;
			
		}
		
		private function _filter_attr ($set_attr, $tag_name) {
			
			$output = [];
			
			for ($i = 0; $i < count ($set_attr); ++$i) {
				
				if (!$set_attr[$i]) continue;
				
				$set_attr[$i] = trim ($set_attr[$i]);
				$exp = strpos ($set_attr[$i], '=');
				
				if ($exp === false) $set_sub_attr = [$set_attr[$i]];
				else $set_sub_attr = array (substr ($set_attr[$i], 0, $exp), substr ($set_attr[$i], $exp + 1));
				
				list ($set_sub_attr[0]) = explode (' ', $set_sub_attr[0]);
				$set_sub_attr[0] = strtolower ($set_sub_attr[0]); // Имена
				
				$set_sub_attr[1] = strip_slashes ($set_sub_attr[1]);
				
				if (
					
					$this->xss and (
						
						!preg_match ('/^[a-z]*$/i', $set_sub_attr[0]) or
						in_array ($set_sub_attr[0], $this->deny_attr) or
						substr ($set_sub_attr[0], 0, 2) == 'on'
						
					)
					
				) continue; // Запрещенные аттрибуты
				
				if ($set_sub_attr[1]) {
					
					$set_sub_attr[1] = str_replace (['&~', '"'], '', $set_sub_attr[1]);
					$set_sub_attr[1] = preg_replace ('/\s+/', ' ', $set_sub_attr[1]);
					
					if (substr ($set_sub_attr[1], 0, 1) == "'" and substr ($set_sub_attr[1], (strlen ($set_sub_attr[1]) - 1), 1) == "'")
					$set_sub_attr[1] = substr ($set_sub_attr[1], 1, (strlen ($set_sub_attr[1]) - 2));
					
				}
				
				if (in_array ($set_sub_attr[0], $this->filter_lower_attr_value)) $set_sub_attr[1] = strtolower ($set_sub_attr[1]); // Значения
				
				//$set_sub_attr[1] = spech_encode ($set_sub_attr[1]);
				
				if ((strpos ($set_sub_attr[1], 'expression') !== false and $set_sub_attr[0] == 'style') or strpos ($set_sub_attr[1], 'javascript:') !== false or strpos ($set_sub_attr[1], 'behaviour:') !== false or strpos ($set_sub_attr[1], 'vbscript:') !== false or strpos ($set_sub_attr[1], 'mocha:') !== false or (strpos ($set_sub_attr[1], 'data:') !== false and $set_sub_attr[0] == 'href') or (strpos ($set_sub_attr[1], 'data:') !== false and $set_sub_attr[0] == 'data') or (strpos ($set_sub_attr[1], 'data:') !== false and $set_sub_attr[0] == 'src') or ($set_sub_attr[0] == 'href' and strpos ($set_sub_attr[1], $this->config['admin_path']) !== false and preg_match ('/[?&%<\[\]]/', $set_sub_attr[1])) or strpos ($set_sub_attr[1], 'livescript:') !== false) continue;
				
				if (!in_array ($set_sub_attr[0], $this->deny_attr)) {
					
					if ($set_sub_attr[1]) $output[] = $set_sub_attr[0].'="'.$set_sub_attr[1].'"';
					elseif ($set_sub_attr[1] == '0') $output[] = $set_sub_attr[0].'="0"';
					else $output[] = $set_sub_attr[0].'=""';
					
				}
				
			}
			
			return $output;
			
		}
		
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		// Служебные функции
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
		function area_encode ($type) {
			return '~<!--Area:'.$type.'-->(.*?)<!--/Area-->~is';
		}
		
		function area_decode ($type, $content) {
			
			$type = lisas_ucfirstl ($type);
			return '<!--Area:'.$type.'-->'.$content.'<!--/Area-->';
			
		}
		
		private function word_filter ($source, $encode = 1) {
			
			if ($encode) {
				
				foreach ($this->mash->tdb->get_row ('wordfilter') as $row) {
					
					$row = $this->mash->tdb->super_query ('select', '', $row);
					
					if ($row['wholeword']) $row['find'] = ' '.$row['find'].' ';
					
					$source = str_ireplace ($row['find'], $this->area_decode ('filter', $row['replace']), $source);
					
				}
				
			}
			
			return $source;
			
		}
		
		private function check_home ($url) {
			if (get_domain ($url) == get_domain (HOME_URL)) return true; else return false;
		}
		
		private function get_domain ($url) {
			
			$url = str_correct ($url, ['ucfirst' => false]);
			
			$find = ['document.cookie', ' ', '<', '>', 'javascript:', '/data:'];
			$replace = ['', '%20', '&~60;', '&~62;', '', ''];
			
			$url = str_replace ($find, $replace, $url);
			$url = strip_parse_slashes ($url);
			
			return $url;
			
		}
		
		private function smile ($name = '') {
			
			$smiles = [];
			$options = ['names_only' => 1];
			
			if ($dir = $this->smiles_dir) {
				
				if ($name) {
					
					$dir .= '/'.$name;
					$options['files_only'] = 1;
					
				} else $options['dirs_only'] = 1;
				
				foreach (dir_scan ($dir, $options) as $smile)
				//if (($name and $smile != $name.'.db' and $smile != $name.'.json') or !$name)
				$smiles[] = $smile;
				
			}
			
			return $smiles;
			
		}
		
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
	}