<?php
/*
 ========================================
 Mash Framework (c) 2010-2017
 ----------------------------------------
 https://mash.ointeractive.ru/
 ========================================
 O! Interactive (support@ointeractive.ru)
 ----------------------------------------
 -- Библиотеки
 --- Работа со строками
 ========================================
*/
	
	if (!defined ('MASH')) die ('File must be started only through the main framework cover');
	
	define ('BR', '<br/>');
	if (!defined ('NL')) define ('NL', PHP_EOL);
	
	define ('NO_CLEAR_DIGITS', 'NO_CLEAR_DIGITS');
	define ('CLEAR_SPACES', 'CLEAR_SPACES');
	define ('ENT_NOTAGS', 'ENT_NOTAGS');
	
	function spech_hard_encode ($txt, $encode = 1, $parse_quotes = 0) { // Кодирует $txt, преобразуя специальные и некоторые дополнительные символы в html-сущности, которые не смогут распарситься парсером.
		
		if ($encode) $txt = spech_encode ($txt, $parse_quotes);
		return str_replace (['&'], ['&amp;'], $txt);
		
	}
	
	function spech_soft_encode ($txt, $encode = 1, $parse_quotes = 0) {
		
		if ($encode) $txt = spech_encode ($txt, $parse_quotes);
		return str_replace (['&amp;'], ['&'], $txt);
		
	}
	
	function spech_decode ($txt, $options = '') { // Декодирует $txt, преобразуя ASCII в HTML
		
		$options = explode ('|', $options);
		
		if (in_array (ENT_NOTAGS, $options))
		$txt = add_slashes (decode_quotes ($txt));
		else
		$txt = html_entity_decode ($txt, ENT_QUOTES, CHARSET);
		
		return $txt;
		
	}
	
	function carve_content ($content, $start, $finish, $add_tpls = 0) { // Выводит контент $content от $start до $finish. Если они пусты, то весь $content.
		
		if (not_empty ($start) and not_empty ($finish)) {
			
			$start_pos = strpos ($content, $start);
			$sub_content = substr ($content, $start_pos, lisas_strlen ($content));
			$finish_pos = strpos ($sub_content, $finish) + lisas_strlen ($finish);
			
			$content = substr ($content, $start_pos, $finish_pos);
			if (!$add_tpls) $content = str_replace ([$start, $finish], '', $content);
			
		}
		
		return stripslashes ($content);
		
	}
	
	function trim_br ($str) { // Отрезаем теги перевода строки в начале и в конце строки $str
		
		$str1 = preg_replace_callback ('~^(<br\s*\/?>\s*)+(.+)$~', function ($match) {
			
			if (!$match[2]) $match[2] = $match[0];
			return $match[2];
			
		}, trim ($str));
		
		$str2 = preg_replace_callback ('~^(.+)+(<br\s*\/?>\s*)$~', function ($match) {
			
			if (!$match[1]) $match[1] = $match[0];
			return $match[1];
			
		}, $str1);
		
		if (!$str2) $str2 = $str1;
		
		return preg_replace ('~^(<br\s*\/?>\s*)+$~', '', $str2);
		
	}
	
	function nl_twice ($str) { // Не работает
		
		$str = trim ($str);
		return preg_replace ('#[*<br[^>]*>*]{3,}?#i', '<br/>', $str);
		
	}
	
	/*
	 Функция суффикса возраста
	*/
	
	function str_suffix ($num, $lang) {
		
		$num1 = substr ($num, -1);
		$num2 = substr ($num, -2);
		
		if ($num2 >= 10 and $num2 <= 20) $word = $lang[2]; // Малолетки и ну просто ппц какие старые (10-20 и 110-120)
		elseif ($num1 == 1) $word = $lang[0];
		elseif ($num1 >= 2 and $num1 <= 4) $word = $lang[1];
		else $word = $lang[2]; // Все остальные возрасты
		
		return $word;
		
	}
	
	/**
	 Функция склонения имен
	 (c) 2013-2015 Acuna. Изменение и снятие копирайтов разрешается только с согласия автора (acuna.public@gmail.com).
	 
	 string str_decl (array options, string sex[, string lang, array sex_array, array data])
	 
	 Склоняет имя options['first'] или просто options (только имя) в падеже options['case'] (по умолчанию: дательный (d)) согласно языку lang (по умолчанию: русский (ru)), и полу sex.
	 
	 Также вы можете использовать свои аргументы: sex_array - массив обозначений полов (по умолчанию: 1 - мужской, 2 - женский), data - сам массив замен. Если Вам кажется, что этот массив по умолчанию не содержит нужных вам преобразований - введите свой массив в этом аргументе, или отправьте свои пожелания на e-mail автора данной функции. Не редактируйте дефолтный массив в самой фунции!
	 
	 1.0	09.04.2013	Склоняет только в дательном и родительном падежах.
	 1.1	17.01.2014	Добавлен притяжательный падеж (кого? чей?).
	 1.2	06.09.2015	Добавлен творительный падеж (кем? чем?).
	 
	*/
	
	function str_decl ($options, $this_sex, $lang = 'ru', $sex_array = [], $data = []) {
		
		if (!is_array ($options)) $options = ['first' => $options];
		
		$options = array_extend ($options, [
			
			'case' => 'd',
			
		]);
		
		$content = $options['first'];
		
		if (!$sex_array) $sex_array = [1, 2];
		
		if (!$data) $data = [
			
			'ru' => [
				
				[ // Мужчины
					
					0 => [ // Добавляем буквы: Михаил, Ярослав, Никанор, Иван, Марк, Епафродит (а вруг :)), Никодим, Енох, что-то на "ч" (фантазия кончилась)
						
						'd' => ['л' => 'у', 'в' => 'у', 'р' => 'у', 'н' => 'у', 'к' => 'у', 'т' => 'у', 'м' => 'у', 'х' => 'у', 'ч' => 'у'],
						't' => ['л' => 'ом', 'в' => 'ом', 'р' => 'ом', 'н' => 'ом', 'к' => 'ом', 'т' => 'ом', 'м' => 'ом', 'х' => 'ом', 'ч' => 'ем'],
						'r' => ['л' => 'а', 'в' => 'а', 'р' => 'а', 'н' => 'а', 'к' => 'а', 'т' => 'а', 'м' => 'а', 'х' => 'а', 'ч' => 'а'],
						'pr' => ['л' => 'а', 'в' => 'а', 'р' => 'а', 'н' => 'а', 'к' => 'а', 'т' => 'а', 'м' => 'а', 'х' => 'а', 'ч' => 'а'],
						
					],
					
					1 => [ // Заменяем 1 букву: Сергей, Лука, Николя, что-то на "ь"
						
						'd' => ['й' => 'ю', 'а' => 'е', 'я' => 'е', 'ь' => 'ю'],
						'r' => ['й' => 'я', 'а' => 'у', 'я' => 'ю', 'ь' => 'я'],
						't' => ['й' => 'ем', 'а' => 'ой', 'я' => 'ей', 'ь' => 'ем'],
						'pr' => ['й' => 'я', 'а' => 'и', 'я' => 'и', 'ь' => 'и'],
						
					],
					
					2 => [ // Заменяем 2 буквы:
						
						'd' => [],
						'r' => [],
						't' => [],
						'pr' => [],
						
					],
					
					'const' => [ // Меняем имя полностью
						
						'd' => [],
						'r' => [],
						't' => [],
						'pr' => [],
						
					],
					
				],
				
				[ // Женщины
					
					0 => [ // Добавляем буквы:
						
						'd' => [],
						'r' => [],
						't' => ['ь' => 'ю'], // Жизель
						'pr' => [],
						
					],
					
					1 => [ // Заменяем 1 букву:
						
						'd' => ['а' => 'е', 'я' => 'е', 'ь' => 'и'], // Анна, Катя, Жизель (для любительниц замков и рыцарей...)
						't' => ['а' => 'ой', 'я' => 'ей'],
						'r' => ['а' => 'у', 'я' => 'ю'], // Анна, Катя
						'pr' => ['а' => 'ы', 'я' => 'и', 'ь' => 'и'], // Анна, Катя,	Жизель
						
					],
					
					2 => [ // Заменяем 2 буквы:
						
						'd' => ['ия' => 'ии'], // Ксения
						't' => ['ия' => 'ией'], // Ксения
						'r' => [],
						'pr' => ['ия' => 'ии', 'ка' => 'ки'], // Ксения, Катенька
						
					],
					
					'const' => [ // Меняем имя полностью
						
						'd' => ['ия' => 'ие'], // Ия
						't' => ['ия' => 'ией'], // Ия
						'r' => [],
						'pr' => ['ия' => 'ии'],
						
					],
					
				],
				
			],
			
		];
		
		$get_sex = 0;
		
		foreach ($sex_array as $key => $sex)
		if ($this_sex == $sex) $get_sex = $key; // Получили ключ пола
		
		if ($data[$lang])
		foreach ($data[$lang][$get_sex] as $num => $data2) {
			
			$start = lisas_substr ($options['first'], 0, (lisas_strlen ($options['first']) - $num));
			if ($num == 0 or $num == 'const') $cut = -1; else $cut = (0 - $num);
			$end = lisas_substr ($options['first'], $cut);
			
			if ($options['debug']) debug ($start);
			
			foreach ($data2[$options['case']] as $find => $replace) {
				
				if ($num == 'const' and lisas_strtolower ($find) == lisas_strtolower ($options['first']))
				$content = $options['first'][0].lisas_substr ($replace, 1);
				elseif ($end == $find)
				$content = $start.$replace;
				
			}
			
		}
		
		return $content;
		
	}
	
	//echo str_decl ('Сергей', 1);
	
	function str_correct ($str, $options = []) {
		
		$options = array_extend ($options, [
			
			'ucfirst' => true,
			'lower' => false,
			'add_slashes' => true,
			'word_wrap' => false,
			'word_wrap_sep' => '<br/>',
			'str_cut_length' => 0,
			'str_cut_sep' => ' ...',
			'plain' => true,
			'trim_spaces' => true,
			
		]);
		
		$str = strip_slashes (trim ($str));
		if ($options['trim_spaces']) $str = preg_replace ('~ +~', ' ', $str);
		
		if ($options['plain']) $str = spech_decode ($str);
		
		$str = correct_html ($str);
		
		if ($options['str_cut_length']) $str = str_cut ($str, (int) $options['str_cut_length'], $options['str_cut_sep']);
		if ($options['word_wrap']) $str = word_wrap ($str, (int) $options['word_wrap_length'], $options['word_wrap_sep']);
		
		if ($options['plain']) $str = strip_tags ($str);
		
		$str = htmlentities ($str, ENT_NOQUOTES | ENT_XHTML | ENT_IGNORE, CHARSET, false);
		$str = encode_system_tags ($str);
		
		if (!$options['plain'])
		$str = spech_decode ($str, ENT_NOQUOTES);
		
		if ($options['ucfirst']) $str = lisas_ucfirst ($str);
		elseif ($options['lower']) $str = lisas_strtolower ($str);
		
		if ($options['add_slashes']) $str = add_slashes ($str);
		
		return $str;
		
	}
	
	function bb_to_html ($str) {
		
		$tags = ['b', 'i', 's', 'u', 'pre', 'h1'];
		
		foreach ($tags as $tag)
		$str = preg_replace ('~\['.$tag.'\](.*?)\[/'.$tag.'\]~i', '<'.$tag.'>\\1</'.$tag.'>', $str);
		
		$tags = ['center'];
		
		foreach ($tags as $tag)
		$str = preg_replace ('~\['.$tag.'\](.*?)\[/'.$tag.'\]~si', '<div style="text-align:'.$tag.';">\\1</div>', $str);
		
		$tags = ['color'];
		
		foreach ($tags as $tag)
		$str = preg_replace ('~\['.$tag.'=(.+?)\](.*?)\[/'.$tag.'\]~i', '<span style="'.$tag.':\\1;">\\2</span>', $str);
		
		$str = preg_replace ('~\[img=(.+?)\](.*?)\[/img\]~i', '<a href="\\1" target="_blank"><img alt="" src="\\2"/></a>', $str);
		$str = preg_replace ('~\[img\](.*?)\[/img\]~i', '<img alt="" src="\\1"/>', $str);
		
		return lisas_nl2br ($str);
		
	}
	
	function str_highlight ($type, $str) {
		
		$str = lisas_nl2br (spech_encode ($str));
		$str = str_replace (['\\', "\t"], ['&#092;', '&nbsp;&nbsp;&nbsp;&nbsp;'], $str);
		
		$data = [];
		
		switch ($type) {
			
			case 'php': case 'js':
				
				$data['command'] = ['function', 'switch', 'case', 'if', 'else', 'elseif', 'define', 'str_replace'];
				$data['operator'] = ['=', '!', '+', '(', ')', '[', ']', '{', '}'];
				
			break;
			
		}
		
		foreach ($data as $key => $value)
		foreach ($value as $key2) {
			
			if ($key == 'operator') {
				
				$key2 = '\\'.$key2;
				$key2_ = $key2;
				
			} else $key2_ = $key2;
			
			$str = preg_replace ('~'.$key2.'~si', '<span class="'.$key.'">'.$key2_.'</span>', $str);
			
		}
		
		return '<div class="highlighter '.$type.'">'.$str.'</div>';
		
	}
	
	function srip_tags_content ($str, $tags) {
		
		foreach ($tags as $tag)
		$str = preg_replace ('/<'.$tag.'[^>]+>(.*?)<\/'.$tag.'>/si', '', $str);
		
		return $str;
		
	}
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Работа с разделителями
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	function _sep_sec_implode ($array, $like, $sep) {
		
		$count = count ($array);
		for ($i = 0; $i < $count; ++$i)
		//if ($sep == ',') $array[$i] = str_replace (',', '&#44;', $array[$i]);
		
		return $array;
		
	}
	
	function sep_implode ($array, $like = 0, $sep = ',', $debug = 0) {
		
		if ($debug) print_r ($array);
		
		if ($array) {
			
			$array = implode ($sep.' ', $array);
			if ($like) $array = $sep.$array.$sep;
			
			return $array;
			
		}
		
	}
	
	function sep2_implode ($array, $like = 0, $sep = ',') {
		
		if (is_array ($array)) $array = implode ($sep, $array);
		if ($like and $array) $array = $sep.$array.$sep;
		
		return $array;
		
	}
	
	function sep_sec_implode ($array, $like = 0, $sep = ',') {
		
		if ($array) {
			
			$array = _sep_sec_implode ($array, $like, $sep);
			$array = implode ($sep.' ', $array);
			
		}
		
		return $array;
		
	}
	
	function nl2sep ($text, $sep = ',') { // Конвертируем переносы строк в разделитель $sep, когда пишем.
		
		$text = str_replace ([',', "\r", "\n"], ['', '', $sep],	trim ($text));
		
		return $text;
		
	}
	
	function sep2nl ($text, $sep = ',', $sep2 = "\n") { // Конвертирует переносы строк в разделитель $sep, когда читаем.
		return nl_clean (sep ($text), $sep, $sep2);
	}
	
	function nl_clean ($str, $sep = "\n", $sep2 = '') {
		return str_replace (["\r", $sep], ['', $sep2], $str);
	}
	
	function correct_html ($str, $br = BR) {
		return str_ireplace (['<br>', BR, ' />'], [$br, $br, '/>'], $str);
	}
	
	function clean_spaces ($text) {
		return preg_replace ('~\s+~u', ' ', trim ($text));
	}
	
	function br_clean ($text) {
		return clean_spaces (preg_replace (array ('~\s*<br[/\s]*?>\s*~i', '~(<br/>){2,}~', '~(\s){2,}~'), ['<br/>', '<br/><br/>', ' '], $text));
	}
	
	function lisas_nl2br ($str, $clean = 1) {
		
		$find = ["\r", "\n"];
		$replace = ['', BR];
		
		$str = correct_html (str_replace ($find, $replace, $str));
		
		if ($clean) $str = br_clean ($str);
		
		return $str;
		
	}
	
	function sep2br ($text, $sep = ',') {
		return sep2nl ($text, $sep, '<br/>');
	}
	
	function br2sep ($text, $sep = ',') {
		return str_replace (["\r", "\n", '<br/>'], ['', $sep, $sep], trim ($text));
	}
	
	function sep_prepare ($array, $deny_files) {
		
		$output = [];
		$array = sep_force_explode ($array);
		$deny_files = sep_force_explode ($deny_files);
		
		foreach ($array as $key) {
			
			foreach ($deny_files as $deny) $key = str_replace ($deny, '', $key);
			
			$output[] = $key;
			
		}
		
		return array_delete_key ('', $output);
		
	}
	
	function sep_force_explode ($array) {
		
		if (!is_array ($array)) $array = sep_explode ($array);
		return $array;
		
	}
	
	function sep ($content, $like = 1, $sep = ',') {
		
		if (!is_array ($content)) {
			
			$content = str_replace ($sep.' ', $sep, $content);
			if ($like) $content = trim ($content, $sep);
			
		}
		
		return $content;
		
	}
	
	function sep_explode ($content, $like = 1, $sep = ',', $debug = 0) {
		
		if (isset ($content) and $content != '') {
			
			$content = sep ($content, $like, $sep);
			$content = explode ($sep, $content);
			
			if ($debug) print_r ($content);
			
		} else $content = [];
		
		return $content;
		
	}
	
	function encode_quotes ($str) {
		return str_replace (['"', '\''], ['&quot;', '&#039;'], strip_slashes (trim ($str)));
	}
	
	function decode_quotes ($str) {
		return trim (str_replace (['&quot;', '&#039;'], ['"', "'"], $str));
	}
	
	function spech_encode ($txt, $options = ENT_QUOTES) { // Кодирует $txt, преобразуя специальные символы в html-сущности.
		
		$options = explode ('|', $options);
		
		$txt = htmlspecialchars (strip_slashes ($txt), ENT_QUOTES | ENT_HTML401 | ENT_IGNORE);
		
		//$txt = repair_quotes ($txt);
		$txt = repair_amps ($txt);
		
		//if (!in_array (ENT_AMPS, $options))
		//$txt = str_replace ('&amp;', '&', $txt);
		
		return $txt;
		
	}
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Функции исправления
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	function repair_quotes ($string) {
		return str_replace ('&amp;quot;', '&quot;', $string);
	}
	
	function repair_amps ($string) {
		return str_replace (['&amp;amp;'], ['&amp;'], $string);
	}
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	function str_compare ($str1, $str1_length, $str2) { // Сравнивает строки $str1 и $str2 и выводит "true", если первые $str1_length строки $str1 равны первым $str1_length символам строки $str2.
		
		$str1_length = intval_correct ($str1_length);
		if (lisas_strtolower (str_cut ($str1, $str1_length, '')) == lisas_strtolower (str_cut ($str2, $str1_length, ''))) return true; else return false;
		
	}
	
	function str_replace_occurance ($find, $replace, $where, $occurance) {
		
		$pos = 0;
		for ($i = 0; $i <= $occurance; $i++)
		$pos = strpos ($where, $find, $pos);
		
		return substr_replace ($find, $replace, $pos, lisas_strlen ($find));
		
	}
	
	function str_rreplace ($find, $replace, $where) {
		
		$where = str_replace ($find, $replace, $where, $count);
		
		for ($i = 1; $i <= ($count + 1); ++$i)
		$where = str_replace ($find, $replace, $where);
		
		return $where;
		
	}
	
	function str_outline ($search, $text, $class = 'outline') { // Выделяет фрагмент $search текста $text и присваивает ему класс $class.
		
		$r = preg_split ('((>)|(<))', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
		
		for ($i = 0; $i < count ($r); ++$i) {
			
			if ($text[0] == lisas_strtoupper ($search[0])) $search = lisas_ucfirst ($search);
			
			if ($r[$i] == '<') {
				
				++$i;
				continue;
				
			}
			
			$r[$i] = preg_replace ('~('.$search.')~i', '<span class="'.$class.'">\\1</span>', $r[$i]);
			
		}
		
		return join ('', $r);
		
	}
	
	function str_not_allowed ($string) {
		if (preg_match ("/[\||\'|\<|\>|\"|\!|\]|\?|\$|\@|\/|\\\|\&\~\*\+]/", $string)) return true; else return false;
	}
	
	function str_clean ($str, $options = '', $sumb = '') { // Очищает строку $str от символов $sumb (строка. По умолчанию - только цифры и спец. символы)
		
		$options = explode ('|', $options);
		
		if (!$sumb) {
			
			if (in_array (NO_CLEAR_DIGITS, $options))
			$sumb = SUMB_SPECIAL.SUMB_SPECIAL_2;
			else
			$sumb = SUMB_DIGITS.SUMB_SPECIAL.SUMB_SPECIAL_2;
			
		}
		
		$letters = lisas_str_split ($sumb);
		
		foreach ($letters as $letter)
		$str = str_replace ($letter, '', $str);
		
		if (in_array (CLEAR_SPACES, $options))
		$str = str_replace (' ', '', $str);
		
		return $str;
		
	}
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	function str_rot ($s, $n = 13) {
		
		static $letters = 'AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz';
		
		$n = (int) $n % 26;
		
		if (!$n) return $s;
		if ($n < 0) $n += 26;
		if ($n == 13) return str_rot13 ($s);
		
		$rep = substr ($letters, $n * 2) . substr ($letters, 0, $n * 2);
		
		return strtr ($s, $letters, $rep);
		
	}
	
	function word_wrap ($string, $length, $sep = '<br/>') { // Разделяет строку $string разделителем $sep через каждые $length символов (обычно используется для переноса слов).
		
		$length = (int) $length;
		
		if ($length > 0) {
			
			$string = preg_split ('((>)|(<))', $string, - 1, PREG_SPLIT_DELIM_CAPTURE);
			
			$n = count ($string);
			
			for ($i = 0; $i < $n; ++$i) {
				
				if ($string[$i] == '<') ++$i;
				
				$string[$i] = preg_replace ('#([^\s\n\r]{'.$length.'})#i', '\\1'.$sep, $string[$i]);
				
			}
			
			$string = join ('', $string);
			
		}
		
		return $string;
		
	}
	
	function add_slashes ($string) { // Экранирует кавычки одним обратным слешем в $string
		
		$string = addslashes ($string);
		return preg_replace ('!\\\+(["\'])!', '\\\$1', $string);
		
	}
	
	function strip_slashes ($string) { // Удаляет обратные слеши у кавычек в $string
		return preg_replace ('~\\\+(["\'])~', '\\1', $string);
	}
	
	function add_parse_slashes ($string) {
		return str_replace ([':'], ['\:'], $string);
	}
	
	function strip_parse_slashes ($string) {
		return str_replace (['\:'], [':'], $string);
	}
	
	function concat ($what, $where, $implode = 1) {
		
		$where = sep_explode ($where);
		$what = make_array ($what);
		
		$is_found = 0;
		
		if ($where) {
			
			foreach ($what as $what1)
			if (!in_array ($what1, $where)) $where[] = $what1;
			
			if ($implode)
			$output = sep_implode ($where);
			else
			$output = $where;
			
		} else { // Еще ничего нет
			
			if ($implode)
			$output = sep_implode ($what);
			else
			$output = $what;
			
		}
		
		return $output;
		
	}
	
	function unconcat ($what, $where) {
		
		$list = sep_explode ($where);
		$i = 0;
		
		foreach ($list as $data) {
			
			if ($data == $what) unset ($list[$i]);
			++$i;
			
		}
		
		if (count ($list)) $output = sep2_implode ($list); else $output = '';
		
		return $output;
		
	}
	
	function trim_prefix ($row) {
		
		$row = explode ('_', $row);
		unset ($row[0]);
		$row = implode ('_', $row);
		
		return $row;
		
	}
	
	function delete_dot ($path) {
		return str_replace (['.'], '', $path);
	}
	
	function clearspecialchars ($content, $ucfirst = 1, $other_sumb = 1) { // Очищает слово от спец. символов, приводит к нижнему регистру, первый символ - к верхнему.
		
		if ($ucfirst) $content = lisas_strtolower ($content);
		
		$find1 = ['`', '~', '@', '#', '№', '$', ';', '¬', '^', '&', '*', '<', '>', ',', '|', '{', '}', '[', ']', '..', '%00', '[NULL]', '&nbsp;', '&amp;', '&quot;', '&laquo;', '&raquo;', '–'];
		
		$content = str_replace ($find1, '', $content);
		
		if ($other_sumb) {
			
			$content = strip_quotes ($content);
			
			$find = ['\\', '.', '/', '%', '-', '_'];
			$content = str_replace ($find, '', $content);
			
		}
		
		$content = str_replace (['ё', '	'], ['е', ' '], $content);
		
		$content = trim ($content);
		if ($ucfirst) $content = lisas_ucfirst ($content);
		
		return $content;
		
	}
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Работа с HTML
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	function html_encode ($tag, $attr = '', $content = '', $i = 0) {
		
		$nbsp = '	';
		
		//$i = 1;
		//$nbsp2 = str_repeat ($nbsp, ($i + 1));
		//$nbsp1 = str_repeat ($nbsp, $i);
		
		$nbsp2 = $nbsp2 = $nbsp;
		
		$output = '<'.trim ($tag);
		
		if ($attr) {
			
			$attrs = [];
			
			foreach ($attr as $key => $value) {
				
				if (is_array ($value)) {
					
					$values = [];
					foreach ($value as $key2 => $value2) $values[] = $key2.':'.$value2.';';
					$value = implode (' ', $values);
					
				}
				
				$attrs[] = trim ($key).'="'.trim ($value).'"';
				
			}
			
			$output .= ' '.implode (' ', $attrs);
			
		}
		
		if (is_array ($content))
		$output .= '>
'.array2html ($content).'
</'.$tag.'>
';
		elseif ($content)
		$output .= '>
'.trim ($content).'
</'.$tag.'>
';
		else
		$output .= '/>';
		
		return $output;
		
	}
	
	function array2html ($array, $i = 1) {
		
		$output = '';
		
		foreach ($array as $data) {
			//++$i;
			
			//debug ([$data[0], $i]);
			$output .= html_encode ($data[0], $data[1], $data[2], $i);
			
		}
		
		return $output;
		
	}
	
	function strpos_all ($what, $where) {
		
		$offset = 0;
		$output = [];
		
		while (($pos = strpos ($where, $what, $offset)) !== false) {
			
			$offset = $pos + strlen ($what);
			$output[] = $pos;
			
		}
		
		return $output;
		
	}
	
	function html_decode ($text) {
		
		$i = 0;
		$offset = 0;
		$output = [];
		
		preg_match_all ('~<(.+?)\s*(.*?)>(.+?)</\\1>~s', $text, $match1);
		preg_match_all ('~<([.^<]+?)\s*(.*?)/>~s', $text, $match2);
		preg_match_all ('~>(.*?)<~s', $text, $match3);
		
		//print_r ($match2);
		
		$i = 0;
		
		foreach ($match1[1] as $match) {
			
			$output[$i][0] = $match;
			++$i;
			
		}
		
		$i = 0;
		
		foreach ($match1[2] as $match) if ($match) {
			
			preg_match_all ('~\s*(.+?)=[\'"](.+?)[\'"]~s', $match, $match4);
			
			for ($i2 = 0; $i2 < count ($match4[1]); ++$i2) {
				
				$param[0] = trim ($match4[1][$i2]);
				$param[1] = strip_quotes (trim ($match4[2][$i2]));
				
				if ($param[0] == 'style') {
					
					$out = [];
					
					foreach (explode (';', $param[1]) as $param1) if ($param1) {
						
						$param1 = explode (':', $param1);
						$out[trim ($param1[0])] = trim ($param1[1]);
						
					}
					
					$param[1] = $out;
					
				} else $param[1] = explode (' ', $param[1]);
				
				$output[$i][1][$param[0]] = $param[1];
				
			}
			
			++$i;
			
		}
		
		$i = 0;
		
		if ($match1[3])
		foreach ($match1[3] as $match) {
			
			$output[$i][2] = html_decode ($match);
			++$i;
			
		}
		else $output = $text;
		
		return $output;
		
	}
	
	//$text = '<a href="fff" rel="external"><p style="width:100%;height:200px;">111</p></a><a href="#" target="_blank">333</a>Текст<img src="fff"/>';
	
	//print_r (html_decode ($text));
	
	function _array_to_html ($data, $debug, $nbsp = '') {
		
		$output = '';
		$nbsp .= '	';
		//if ($debug) print_r ($data);
		
		$output .= $nbsp.'<'.$data[0];
		
		if ($data[1]) foreach ($data[1] as $key => $value) {
			
			if (is_array ($value)) {
				
				$css_out = '';
				foreach ($value as $key2 => $value2)
				$css_out .= $key2.':'.$value2.'; ';
				$value = trim ($css_out);
				
			}
			
			$output .= ' '.$key.'="'.$value.'"';
			
		}
		
		if ($data[2]) {
			
			if (is_array ($data[2])) $data[2] = _array_to_html ($data[2], $debug, $nbsp);
			
			$output .= '>
'.$data[2].$nbsp.'</'.$data[0].'>
';
			
		} else $output .= '/>
';
		
		return $output;
		
	}
	
	function array_to_html ($array, $debug = 0) {
		
		$output = '';
		foreach ($array as $data) $output .= _array_to_html ($data, $debug);
		return $output;
		
	}
	
	function cat_tree2 ($cat_info, $parent_id = 0, $nbsp = '', $output = '') {
		
		$nbsp .= '&nbsp;&nbsp;&nbsp;';
		
		$root = [];
		
		foreach ($cat_info as $cats)
		if ($cats['parent_id'] == $parent_id) $root[] = $cats['id'];
		
		foreach ($root as $id) {
			
			$cat = $cat_info[$id];
			
			$output .= $nbsp.$cat['title'];
			$output = cat_tree2 ($cat_info, $id, $nbsp, $output);
			
		}
		
		return $output;
		
	}
	
	function dash_if_empty ($str, $sum = '-') {
		
		if (!not_empty ($str, 0)) $str = $sum;
		return $str;
		
	}
	
	function str2bool ($value) {
		
		if ($value === 'true' or $value === 1) $value = true;
		elseif ($value === 'false' or $value === 0) $value = false;
		
		return $value;
		
	}
	
	function bool2str ($value) {
		
		if ($value === true or $value === 1) $value = 'true';
		elseif ($value === false or $value === 0) $value = 'false';
		
		return $value;
		
	}
	
	function str_typo ($str, $options) {
		
		$data = [
			
			'—' => '&mdash;',
			'==' => '&equiv;',
			'...' => '&hellip;',
			'!=' => '&ne;',
			'<=' => '&le;',
			'>=' => '&ge;',
			'1/2' => '&frac12;',
			'1/4' => '&frac14;',
			'3/4' => '&frac34;',
			'+-' => '&plusmn;',
			'(c)' => '&copy;',
			'(tm)' => '&trade;',
			'§' => '&sect;',
			
		];
		
		foreach ($data as $find => $replace)
		$str = str_replace ($find, $replace, $str);
		
		$okposstack = array('0');
		$okpos = 0;
		$level = 0;
		$off = 0;
		
		while(true)
		{
			$p = strpos_ex($str, array("&laquo;", "&raquo;"), $off);
			if($p===false) break;
			if($p['str'] == "&laquo;")
			{
				if($level>0) if(!$this->is_on('no_bdquotes')) $this->inject_in($p['pos'], '&bdquo;');
				$level++;				
			}
			if($p['str'] == "&raquo;")
			{
				$level--;	
				if($level>0) if(!$this->is_on('no_bdquotes')) $this->inject_in($p['pos'], '&ldquo;');				
			}
			$off = $p['pos']+lisas_strlen($p['str']);
			if($level == 0) 
			{
				$okpos = $off;
				array_push($okposstack, $okpos);
			} elseif($level<0) // уровень стал меньше нуля
			{
				if(!$this->is_on('no_inches'))
				{
					do{
						$lokpos = array_pop($okposstack);
						$k = lisas_substr($str, $lokpos, $off-$lokpos);
						$k = str_replace('&bdquo;', '&laquo;', $k);
						$k = str_replace('&ldquo;', '&raquo;', $k);
						//$k = preg_replace("/(^|[^0-9])([0-9]+)\&raquo\;/ui", '\1\2&Prime;', $k, 1, $amount);
						
						$amount = 0;
						$__ax = preg_match_all("/(^|[^0-9])([0-9]+)\&raquo\;/ui", $k, $m);
						$__ay = 0;
						if($__ax)
						{
							$k = preg_replace_callback("/(^|[^0-9])([0-9]+)\&raquo\;/ui", 
								create_function('$m','global $__ax,$__ay; $__ay++; if($__ay==$__ax){ return $m[1].$m[2]."&Prime;";} return $m[0];'), 
								$k);
							$amount = 1;
						}
						
						
						
					} while(($amount==0) && count($okposstack));
					
					// успешно сделали замену
					if($amount == 1)
					{
						// заново просмотрим содержимое								
						$str = lisas_substr($str, 0, $lokpos). $k . lisas_substr($str, $off);
						$off = $lokpos;
						$level = 0;
						continue;
					}
					
					// иначе просто заменим последнюю явно на &quot; от отчаяния
					if($amount == 0)
					{	
						// говорим, что всё в порядке
						$level = 0;		
						$str = lisas_substr($str, 0, $p['pos']). '&quot;' . lisas_substr($str, $off);
						$off = $p['pos'] + lisas_strlen('&quot;');
						$okposstack = array($off);									
						continue;
					}
				}
			}
			
			
		}
		// не совпало количество, отменяем все подкавычки
		if($level != 0 ){
			
			// закрывающих меньше, чем надо
			if($level>0)
			{
				$k = lisas_substr($str, $okpos);
				$k = str_replace('&bdquo;', '&laquo;', $k);
				$k = str_replace('&ldquo;', '&raquo;', $k);
				$str = lisas_substr($str, 0, $okpos). $k;
			}
		}
		
		return $str;
		
	}
	
	function strpos_ex (&$haystack, $needle, $offset = null) {
		if(is_array($needle))
		{
			$m = false;
			$w = false;
			foreach($needle as $n)
			{
				$p = strpos($haystack, $n , $offset);
				if($p===false) continue;
				if($m === false)
				{
					$m = $p;
					$w = $n;
					continue;
				}
				if($p < $m)
				{
					$m = $p;
					$w = $n;
				}
			}
			if($m === false) return false;
			return array('pos' => $m, 'str' => $w);
		}
		return strpos($haystack, $needle, $offset);			
	}
	
	//echo str_typo ('"Эдиториум.ру" - сайт, созданный по материалам сборника "О редактировании и редакторах" Аркадия Эммануиловича Мильчина, который с 1944 года коллекционировал выдержки из статей, рассказов, 1/2 +- фельетонов, пародий, писем и книг, где так или иначе затрагивается тема редакторской работы. Эта коллекция легла в основу обширной антологии, представляющей историю и природу редактирования в первоисточниках.');
	
	function new_preg_match_all ($what, $str) {
		
		preg_match_all ($what, $str, $match);
		
		$output = [];
		
		for ($i = 0; $i < count ($match[0]); ++$i) {
			
			$data = [];
			foreach ($match as $key => $value)
			$output[$i][$key] = $value[$i];
			
		}
		
		return $output;
		
	}
	
	function str_cut ($string, $length, $end = ' ...') { // Резалка строк (обрезает до $length длины).
		
		$length = intval_correct ($length);
		
		if ($length and lisas_strlen ($string) > $length)
		$string = lisas_substr ($string, 0, $length).$end;
		
		return $string;
		
	}
	
	function nl_explode ($str) {
		
		$str = str_replace ("\r", '', $str);
		return explode ("\n", $str);
		
	}
	
	function not_empty ($content, $type = 1, $file = __FILE__) { // Проверка на пустоту
		return (
						(is_array ($content) and $content)
						or
						(trim ($content) != '')
					);
	}
	
	function strip_quotes ($text) {
		return str_replace (["'", '"'], '', stripslashes (trim ($text)));
	}
	
	function br2nl ($str) {
		return str_replace (["\r", "\n", BR], ['', '', NL], correct_html ($str));
	}
	
	function br_explode ($str) {
		return explode (BR, correct_html ($str));
	}
	
	function br_implode ($str) {
		return implode (BR, $str);
	}
	
	function preg_show ($str, $open, $close = '') {
		
		if ($close) {
			
			$pos = 0;
			$output = [];
			$raw = $open;
			
			preg_match ('~\\'.$open[0].'(.+?)\\'.$open[0].'~si', $open, $match);
			$open_s = substr ($match[1], -1);
			
			while (preg_match (str_replace ('[', '\\[', $open), $str, $match, PREG_OFFSET_CAPTURE, $pos)) {
				
				$bracket = 1;
				$start = $match[0][1];
				$pos = $offset = ($start + strlen ($match[0][0]));
				
				while ($bracket !== 0 and preg_match ('~\\'.$open_s.'|\\'.$close.'~', $str, $match2, PREG_OFFSET_CAPTURE, $offset)) {
					
					if ($match2[0][0] === $close) --$bracket; else ++$bracket;
					$offset = ($match2[0][1] + 1);
					
				}
				
				$output[] = substr ($str, $start, ($offset - $start));
				
			}
			
		} else $output = substr ($str, strpos ($str, $open));
		
		return $output;
		
	}
	
	function str_cmp ($str1, $str2) { // Посимвольно сравнивает две строки
		
		$output = '';
		$str1 = mb_string2array ($str1);
		$str2 = mb_string2array ($str2);
		
		foreach (range (0, max (count ($str1), count ($str2))) as $i) {
			
			if ($str1[$i] == $str2[$i])
			$output .= $str1[$i];
			else
			$output .= $str2[$i];
			
		}
		
		return $output;
		
	}
	
	function compare_tags ($a, $b) {
		
		if ($a['tag'] == $b['tag']) return 0;
		return strcasecmp ($a['tag'], $b['tag']);
		
	}
	
	/**
	 Генерирует случайное множество символов длины $num типа $type:
	 1 - цифры
	 2 - цифры и строчные буквы
	 3 - цифры, строчные и заглавные буквы
	 4 - цифры, строчные, заглавные буквы и символы
	 5 - строчные буквы
	 6 - заглавные буквы
	*/
	
	function do_rand ($num, $type = 1) {
		
		if ($type == 1 or $type == 2 or $type == 3 or $type == 4)
		$salt = SUMB_DIGITS; // 1
		if ($type == 2 or $type == 3 or $type == 4)
		$salt .= SUMB_LETTERS_LOW; // 2
		if ($type == 3 or $type == 4)
		$salt .= SUMB_LETTERS_UP; // 3
		if ($type == 4)
		$salt .= SUMB_SPECIAL; // 4
		
		if ($type == 5) $salt = SUMB_LETTERS_LOW; // 5
		if ($type == 6) $salt = SUMB_LETTERS_UP; // 6
		
		//debug ($salt);
		
		srand ((double) microtime () * 1000000);
		
		$rand = '';
		$len = (strlen ($salt) - 1);
		
		$salt = lisas_str_split ($salt);
		
		for ($i = 0; $i < $num; ++$i)
		$rand .= $salt[rand (0, $len)];
		
		return $rand;
		
	}
	
	function unary_quotes ($value) {
		return str_replace ('"', '\'', $value);
	}
	
	function _parse_value ($str, $i) {
		return str_ireplace (['{counter}'], [$i], $str);
	}
	
	function str_replace_json ($search, $replace, $subject) { 
		return json_decode (str_replace ($search, $replace, json_encode ($subject)));
	}
	
	
	function evfem_replace ($str, $debug = 0) {
		
		foreach ([
			
			'а' => 'a',
			'е' => 'e',
			'о' => 'o',
			'р' => 'p',
			'с' => 'c',
			'у' => 'y',
			'х' => 'x',
			
			'А' => 'A',
			'В' => 'B',
			'Е' => 'E',
			'К' => 'K',
			'М' => 'M',
			'Н' => 'H',
			'О' => 'O',
			'Р' => 'P',
			'С' => 'C',
			'Т' => 'T',
			'Х' => 'X',
			
		] as $find => $replace)
			$str = str_replace ($find, $replace, $str);
		
		return $str;
		
	}
	
	if (!function_exists ('str_ends_with')) {
		
		function str_ends_with ($haystack, $needle) {
			
			$length = strlen ($needle);
			return $length > 0 ? substr ($haystack, -$length) === $needle : true;
			
		}
		
	}
	