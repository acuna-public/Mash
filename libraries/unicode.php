<?php
/*
 ========================================
 Mash Framework (c) 2010-2017, 2020
 ----------------------------------------
 https://mash.ointeractive.ru/
 ========================================
 O! Interactive (support@ointeractive.ru)
 ----------------------------------------
 -- Функции
 --- Работа с юникодом
 ========================================
*/
	
	if (!defined ('MASH')) die ('File must be started only through the main framework cover');
	
	if (!defined ('CHARSET'))
	define ('CHARSET', ini_get ('default_charset'));
	
	define ('CUR_SYMBOLS', 'а-яйёґєїіўѓќџђљњћ');
	
	function is_cyrillic ($str) {
		return preg_match ('~(['.CUR_SYMBOLS.']+)~iu', $str);
	}
	
	function unicode ($str) {
		
		$cyrillic = ['А','Б','В','Г','Д','Е','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','а','б','в','г','д','е','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я','Ё','ё'];
		
		$unicode = ['&#1040;','&#1041;','&#1042;','&#1043;','&#1044;','&#1045;','&#1046;','&#1047;','&#1048;','&#1049;','&#1050;','&#1051;','&#1052;','&#1053;','&#1054;','&#1055;','&#1056;','&#1057;','&#1058;','&#1059;','&#1060;','&#1061;','&#1062;','&#1063;','&#1064;','&#1065;','&#1066;','&#1067;','&#1068;','&#1069;','&#1070;','&#1071;','&#1072;','&#1073;','&#1074;','&#1075;','&#1076;','&#1077;','&#1078;','&#1079;','&#1080;','&#1081;','&#1082;','&#1083;','&#1084;','&#1085;','&#1086;','&#1087;','&#1088;','&#1089;','&#1090;','&#1091;','&#1092;','&#1093;','&#1094;','&#1095;','&#1096;','&#1097;','&#1098;','&#1099;','&#1100;','&#1101;','&#1102;','&#1103;','&#1025;','&#1105;'];
		
		return str_replace ($cyrillic, $unicode, $str);
		
	}
	
	function iso2uni ($iso) {
		
		$uni = '';
		$iso = convert_cyr_string	($iso, 'w', 'i');
		
		for ($i = 0; $i < lisas_strlen ($iso); ++$i) {
			
			$thischar = lisas_substr ($iso, $i, 1);
			$charcode = lisas_ord ($thischar);
			$uni .= ($charcode > 175) ? '&#'.(1040 + ($charcode - 176)).';' : $thischar;
			
		}
		
		return $uni; 
		
	}
	
	function lisas_ord ($str, &$offset = 0) {
		
		$code = ord (substr ($str, $offset, 1));
		
		if ($code >= 128) {	// 0xxxxxxx
			
			if ($code < 224) $bytesnumber = 2; // 110xxxxx
			else if ($code < 240) $bytesnumber = 3; // 1110xxxx
			else if ($code < 248) $bytesnumber = 4; // 11110xxx
			
			$codetemp = $code - 192 - (($bytesnumber > 2) ? 32 : 0) - (($bytesnumber > 3) ? 16 : 0);
			
			for ($i = 2; $i <= $bytesnumber; $i++) {
				
				$offset++;
				$code2 = ord (substr ($str, $offset, 1)) - 128; // 10xxxxxx
				$codetemp = ($codetemp * 64) + $code2;
				
			}
			
			$code = $codetemp;
			
		}
		
		$offset += 1;
		if ($offset >= strlen ($str)) $offset = -1;
		
		return $code;
		
	}
	
	function utf82win ($str) {
		
		for ($c = 0; $c < lisas_strlen ($str); ++$c) {
			
			$i = lisas_ord ($str[$c]);
			if ($i <= 127) $out .= $str[$c];
			
			if ($byte2) {
				
				$new_c2 = ($c1 & 3) * 64 + ($i & 63);
				$new_c1 = ($c1 >> 2) & 5;
				$new_i = $new_c1 * 256 + $new_c2;
				
				if ($new_i == 1025) $out_i = 168;
				else {
					if ($new_i == 1105) $out_i = 184; else $out_i = $new_i - 848;
				}
				
				@$out .= lisas_chr ($out_i);
				$byte2 = false;
				
			}
			
			if (($i >> 5) == 6) {
				
				$c1 = $i;
				$byte2 = true;
				
			}
			
		}
		
		return str_replace (["", "", ""], ['ї', 'є', 'i'], $out);
		
	}
	
	function lisas_chr ($dec) {
		
		if ($dec < 128) $utf = chr ($dec);
		else if ($dec < 2048) {
			
			$utf = chr(192 + (($dec - ($dec % 64)) / 64));
			$utf .= chr(128 + ($dec % 64));
			
		} else {
			
			$utf = chr (224 + (($dec - ($dec % 4096)) / 4096)); 
			$utf .= chr (128 + ((($dec % 4096) - ($dec % 64)) / 64)); 
			$utf .= chr (128 + ($dec % 64));
			
		}
		
		return $utf;
		
	}
	
	function is_utf8 ($str) {
		return preg_match ('%(?:[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2})+%xs', $str);
	}
	
	function from_ascii ($str) { // Концепт. Пока не пользуйтесь.
		return str_replace (['&#124;'], ['|'], $str);
	}
	
	function lisas_strtolower ($str) {
		return mb_strtolower ($str, CHARSET);
	}
	
	function lisas_strtoupper ($str) {
		return mb_strtoupper ($str, CHARSET);
	}
	
	function __lisas_ucfirst ($str) {
		
		$first = lisas_strtoupper (lisas_substr ($str, 0, 1));
		$other = lisas_substr ($str, 1, lisas_strlen ($str));
		
		return $first.$other;
		
	}
	
	function _lisas_ucfirst_sent ($str) {
		
		$output = '';
		$dot = '. ';
		
		foreach (explode ($dot, $str) as $str) {
			
			if (preg_match ('~^[a-z'.CUR_SYMBOLS.']~ui', $str))
			$str = __lisas_ucfirst ($str);
			
			$output .= $str.$dot;
			
		}
		
		return rtrim ($output, $dot);
		
	}
	
	function lisas_ucfirst ($str) { // Приводит первый символ к верхнему регистру
		return __lisas_ucfirst ($str);
	}
	
	function lisas_ucfirstl ($str) { // Приводит всю строку к нижнему регистру. Первый символ - к верхнему, предварительно приводя всю строку к нижнему регистру
		
		$str = lisas_strtolower ($str);
		return __lisas_ucfirst ($str);
		
	}
	
	function lisas_substr ($str, $offset, $length = null) {
		return mb_substr ($str, $offset, $length, CHARSET);
	}
	
	function lisas_ucword ($str) {
		return lisas_ucwords (lisas_strtolower ($str));
	}
	
	define ('ABBREVS', 0);
	
	function lisas_ucwords ($str, $options = ABBREVS) {
		
		$options = explode ('|', $options);
		
		$upper = 'nw|php';
		if (in_array (ABBREVS, $options)) $upper .= '|u2|usa|uk|tlc|dmx|abba|ussr|nsbm|dsbm';
		
		$lower = 'a|an|and|the|as|are|by|on|is|at|for|ago|till|in|to|into|onto|from|if|once|when|with|that|what|who|whom|why|of|over|per|than|up|via|vice|or|de|la|las|der|van|vit|von';
		
		$suffixes = '\'s';
		$prefixes = 'mc';
		
		$i = 0;
		$output = [];
		
		foreach (explode ('. ', $str) as $sentence) {
			
			$output2 = [];
			$words = explode (' ', $sentence);
			$count = count ($words);
			
			foreach ($words as $word) {
				++$i;
				
				if ($i > 1) $word = lisas_ucfirst ($word); // Первые буквы всегда заглавные
				
				if ($upper) // Аббревиатуры всегда заглавные
				$word = preg_replace_callback ('~\s+('.$upper.')\s+~i', function ($match) {
					return lisas_strtoupper ($match[1]);
				}, $word);
				
				if ($lower) // Предлоги
				$word = preg_replace_callback ('~^('.$lower.')$~i', function ($match) {
					return lisas_strtolower ($match[1]);
				}, $word);
				
				if ($suffixes) // 's
				$word = preg_replace_callback ('~(\w)('.$suffixes.')$~i', function ($match) {
					return $match[1].lisas_strtolower ($match[2]);
				}, $word);
				
				if ($prefixes) // Mc'
				$word = preg_replace_callback ('~^('.$prefixes.')(\W)(.+)~i', function ($match) {
					return lisas_ucfirstl ($match[1]).$match[2].lisas_ucfirst ($match[3]);
				}, $word);
				
				if (is_roman_number ($word)) $word = lisas_strtoupper ($word);
				
				$output2[] = $word;
				
			}
			
			$output2[0] = lisas_ucfirst ($output2[0]); // Первое слово в предложении
			$output2[($count - 1)] = lisas_ucfirst ($output2[($count - 1)]); // Последнее слово в предложении
			
			$output[] = implode (' ', $output2);
			
		}
		
		$str = implode ('. ', $output);
		
		return $str;
		
	}
	
	//echo lisas_ucwords ('W.A. B. of. Song\'S dub мир PHP usa vi of. cd-r Full-Length Of A MC\'solomon of. for the Draenor Productions glory');
	
	function lisas_strpos ($what, $where, $start = 0) {
		return mb_strpos ($where, $what, $start, CHARSET);
	}
	
	function lisas_stripos ($what, $where, $start = 0) {
		return mb_stripos ($where, $what, $start, CHARSET);
	}
	
	function lisas_stripos_all ($what, $where) {
		
		$s = 0; $i = 0; $str_pos = [];
		
		while (is_integer ($i)) {
			
			$i = lisas_stripos ($what, $where, $s);
			
			if (is_integer ($i)) {
				
				$str_pos[] = $i;
				$s = $i + lisas_strlen ($what);
				
			}
			
		}
		
		if ($str_pos) return $str_pos; else return false;
		
	}
	
	function lisas_strrev ($str) {
		
		$str = lisas_str_split ($str);
		return join ('', array_reverse ($str));
		
	}
	
	function lisas_str_split ($str, $split_length = 1) {
		
		if ($split_length > 1) {
			
			$return_value = [];
			$str_length = lisas_strlen ($str);
			
			for ($i = 0; $i < $str_length; $i += $split_length)
			$return_value[] = lisas_substr ($str, $i, $split_length);
			
			return $return_value;
			
		} elseif ($split_length == 1)
		return preg_split ('//u', $str, -1, PREG_SPLIT_NO_EMPTY);
		else
		return [$str];
		
	}
	
	function lisas_preg_split ($pattern, $str, $limit = -1) {
		return mb_split ($pattern, $str, $limit);
	}
	
	function is_roman_number ($number) {
		return preg_match ('~^(M{0,3})(D?C{0,3}|C[DM])(L?X{0,3}|X[LC])(V?I{0,3}|I[VX])$~', $number);
	}
	
	function ar2roman ($number) {
		
		$notation = [
			
			'|', // Один
			'[', // Пять
			']', // Десять
			
		];
		
		$numerals = [
			
			['I', 'V', 'X',],		// Единицы (1, 5, 10)
			['X', 'L', 'C',],		// Десятки (10, 50, 100)
			['C', 'D', 'M',],		// Сотни (100, 500, 1 000)
			['M', 'V|', 'X|',],	// Тысячи (1 000, 5 000, 10 000)
			['X|', 'L|', 'C|',], // Десятки тысяч (10 000, 50 000, 100 000)
			['C|', 'D|', 'M|',], // Сотни тысяч (100 000, 500 000, 1 000 000)
			['M|', '', '',],		 // Миллионы (1 000 000 - 3 999 999)
			
		];
		
		$num2not = [
			
			'0' => '',
			'1' => '|',
			'2' => '||',
			'3' => '|||',
			'4' => '|[',
			'5' => '[',
			'6' => '[|',
			'7' => '[||',
			'8' => '[|||',
			'9' => '|]',
			
		];
		
		$output = '';
		$number_string = lisas_strrev ((string) $number);
		$length = lisas_strlen ($number_string);
		
		for ($i = 0; $i < $length; ++$i) {
			
			$char = $number_string[$i];
			
			$num_map = $numerals[$i];
			$output = str_replace ($notation, $num_map, $num2not[$char]).$output;
			
		}
		
		return $output;
		
	}
	
	function roman2ar ($numerals) {
		
		$rom2num = [
			
			'I' => 1,
			'V' => 5,
			'X' => 10,
			'L' => 50,
			'C' => 100,
			'D' => 500,
			'M' => 1000,
			'V|' => 5000,
			'X|' => 10000,
			'L|' => 50000,
			'C|' => 100000,
			'D|' => 500000,
			'M|' => 1000000,
			
		];
		
		$number = 0;
		$numeral_string = lisas_strrev ((string) $numerals);
		$length = lisas_strlen ($numeral_string);
		
		$prev_number = false;
		$is_accented = false;
		
		for ($i = 0; $i < $length; ++$i) {
			
			$char = $numeral_string[$i];
			
			if ($char == '|') { // Тысячи
				
				$is_accented = true;
				continue;
				
			} elseif ($is_accented) {
			
				$char .= '|';
				$is_accented = false;
				
			}
			
			$num = $rom2num[$char];
			
			// Если предыдущее число, поделенное на 5 или 10, равно текущему числу - вычитаем его, иначе складываем
			
			if ($prev_number) {
				
				if (($prev_number / 5) == $num || ($prev_number / 10) == $num)
				$number -= $num;
				else
				$number += $num;
				
			} else $number += $num;
			
			$prev_number = $num;
			
		}
		
		return $number;
		
	}
	
	function win_to_utf8 ($s) {
		
		for ($i = 0, $m = lisas_strlen ($s); $i < $m; ++$i) {
			
			$c = ord ($s[$i]);
			
			if ($c <= 127) $t .= chr ($c);
			
			if ($c >= 192 and $c <= 207) $t .= chr (208).chr ($c - 48);
			if ($c >= 208 and $c <= 239) $t .= chr (208).chr ($c - 48);
			if ($c >= 240 and $c <= 255) $t .= chr (209).chr ($c - 112);
			if ($c == 184) $t .= chr (209).chr (209);
			if ($c == 168) $t .= chr (208).chr (129);
			
		}
		
		return $t;
		
	}
	
	function unichar2ords ($char, $encoding = 'UTF-8') {
		
		$char = mb_convert_encoding ($char, 'UCS-4', $encoding);
		$val = unpack ('N', $char);
		
		return $val[1];
		
	}
	
	function ords2unichar ($ords, $encoding = 'UTF-8'){
		
		$char = pack ('N', $ords);
		return mb_convert_encoding ($char, $encoding, 'UCS-4');
		
	}
	
	function unicode_str_rot ($str, $offset, $encoding = CHARSET) {
		
		$val = '';
		$array = mb_string2array ($str, $encoding);
		$len = count ($array);
		
		for ($i = 0; $i < $len; $i++)
		$val .= ords2unichar (unichar2ords ($array[$i], $encoding) + $offset, $encoding);
		
		return $val;
		
	}
	
	function unicode_sort ($a, $b) {
		
		$charOrder = ['a', 'b', 'c', 'd', 'e', 'é', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
		
		$a = lisas_strtolower ($a);
		$b = lisas_strtolower ($b);
		
		for ($i=0; $i < lisas_strlen ($a) && $i < lisas_strlen ($a); ++$i) {
			
			$chA = lisas_substr ($a, $i, 1);
			$chB = lisas_substr ($b, $i, 1);
			
			$valA = array_search ($chA, $charOrder);
			$valB = array_search ($chB, $charOrder);
			
			if ($valA == $valB) continue;
			if ($valA > $valB) return 1;
			
			return -1;
			
		}
		
		if (lisas_strlen ($a) == lisas_strlen ($b)) return 0;
		if (lisas_strlen ($a) > lisas_strlen ($b)) return -1;
		
		return 1;
		
	}
	
	function _all_letters_to_ASCII ($string) {
		return strtr (utf8_decode ($string), utf8_decode ('ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ'), 'SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy');
	}
	
	function htmlentities_encode ($str, $flags = ENT_NOQUOTES | ENT_HTML401 | ENT_IGNORE, $encoding = CHARSET) {
		return htmlentities ($str, $flags, $encoding, false);
	}
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Работа с кодировками
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	function detect_encoding ($string, $pattern_size = 50) {
		
		$list = ['cp1251', 'utf-8', 'ascii', '855', 'KOI8R', 'ISO-IR-111', 'CP866', 'KOI8U'];
		$c = strlen ($string);
		
		if ($c > $pattern_size) {
			
			$string = substr ($string, floor (($c - $pattern_size) / 2), $pattern_size);
			$c = $pattern_size;
			
		}
		
		$reg1 = '/(\xE0|\xE5|\xE8|\xEE|\xF3|\xFB|\xFD|\xFE|\xFF)/i';
		$reg2 = '/(\xE1|\xE2|\xE3|\xE4|\xE6|\xE7|\xE9|\xEA|\xEB|\xEC|\xED|\xEF|\xF0|\xF1|\xF2|\xF4|\xF5|\xF6|\xF7|\xF8|\xF9|\xFA|\xFC)/i';
		
		$mk = 10000;
		$enc = 'ascii';
		
		foreach ($list as $item) {
			
			$sample1 = @iconv ($item, 'cp1251', $string);
			
			$gl = @preg_match_all ($reg1, $sample1, $arr);
			$sl = @preg_match_all ($reg2, $sample1, $arr);
			
			if (!$gl or !$sl) continue;
			
			$k = abs (3 - ($sl / $gl));
			$k += $c - $gl - $sl;
			
			if ($k < $mk) {
				
				$enc = $item;
				$mk = $k;
				
			}
			
		}
		
		return $enc;
		
	}
	
	function from_translit ($string, $options = []) {
		
		$letters = [
			
			'Аy' => 'У', 'A' => 'А', 'B' => 'Б', 'V' => 'В', 'G' => 'Г', 'D' => 'Д', 'Ey' => 'Ей', 'E' => 'Е', 'Yo' => 'Ё', 'Zh' => 'Ж', 'Z' => 'З', 'Iy' => 'Ий', 'I' => 'И', 'Yu' => 'Ю', 'J' => 'Й', 'Kh' => 'Х', 'K' => 'К', 'L' => 'Л', 'M' => 'М', 'N' => 'Н', 'Oy' => 'Ой', 'O' => 'О', 'P' => 'П', 'R' => 'Р', 'S' => 'С', 'T' => 'Т', 'U' => 'У', 'Y' => 'Ий', 'Иий' => 'Ий', 'F' => 'Ф', 'H' => 'Х', 'C' => 'Ц', 'Ch' => 'Ч', 'Sh' => 'Ш', 'Sch' => 'Щ', 'Ye' => 'Э', 'Ya' => 'Я', 'yi' => 'Ї', 'ie' => 'Є', 'X' => 'Кс',
			
			'ay' => 'у', 'a' => 'а', 'b' => 'б', 'v' => 'в', 'g' => 'г', 'd' => 'д', 'ey' => 'ей', 'e' => 'е', 'yo' => 'ё', 'zh' => 'ж', 'z' => 'з', 'iy' => 'ий', 'i' => 'и', 'yu' => 'ю', 'j' => 'й', 'kh' => 'х', 'k' => 'к', 'l' => 'л', 'm' => 'м', 'n' => 'н', 'oy' => 'ой', 'o' => 'о', 'p' => 'п', 'r' => 'р', 's' => 'с', 't' => 'т', 'u' => 'у', 'y' => 'ий', 'иий' => 'ий', 'f' => 'ф', 'ch' => 'ч', 'h' => 'х', 'c' => 'ц', 'sh' => 'ш', 'sch' => 'щ', 'ye' => 'э', 'ya' => 'я', 'yi' => 'ї', 'ie' => 'є', 'x' => 'кс',
			
		];
		
		foreach ($letters as $find => $replace)
		$string = str_replace ($find, $replace, $string);
		
		return $string;
		
	}
	
	function to_translit ($str, $options = []) {
		
		if (is_array ($str)) return;
		
		$str = str_clean ($str, NO_CLEAR_DIGITS);
		$str = trim (strip_tags ($str));
		
		if ($options['alt_name']) $str = preg_prepspecialchars ($str);
		if ($options['bottom_gap']) $str = preg_replace ('/\s+/ms', '_', $str);
		
		if ($options['gost'])	{
			
			$arStrES = array ('ае', 'уе', 'ое', 'ые', 'ие', 'эе', 'яе', 'юе', 'ёе', 'ее', 'ье', 'ъе', 'ый', 'ий');
			$arStrOS = array ('аё', 'уё', 'оё', 'ыё', 'иё', 'эё', 'яё', 'юё', 'ёё', 'её', 'ьё', 'ъё', 'ый', 'ий');
			$arStrRS = array ('а$', 'у$', 'о$', 'ы$', 'и$', 'э$', 'я$', 'ю$', 'ё$', 'е$', 'ь$', 'ъ$', '@', '@');
			
			$replace = [
				
				'А' => 'A', 'а' => 'a', 'Б' => 'B', 'б' => 'b', 'В' => 'V', 'в' => 'v', 'Г' => 'G', 'г' => 'g', 'Д' => 'D', 'д' => 'd', 
				'Е' => 'Ye', 'е' => 'e', 'Ё' => 'Ye', 'ё' => 'e', 'Ж' => 'Zh', 'ж' => 'zh', 'З' => 'Z', 'з' => 'z', 'И' => 'I', 'и' => 'i', 
				'Й' => 'Y', 'й' => 'y', 'К' => 'K', 'к' => 'k', 'Л' => 'L', 'л' => 'l', 'М' => 'M', 'м' => 'm', 'Н' => 'N', 'н' => 'n', 
				'О' => 'O', 'о' => 'o', 'П' => 'P', 'п' => 'p', 'Р' => 'R', 'р' => 'r', 'С' => 'S', 'с' => 's', 'Т' => 'T', 'т' => 't', 
				'У' => 'U', 'у' => 'u', 'Ф' => 'F', 'ф' => 'f', 'Х' => 'Kh', 'х' => 'kh', 'Ц' => 'Ts', 'ц' => 'ts', 'Ч' => 'Ch', 'ч' => 'ch', 
				'Ш' => 'Sh', 'ш' => 'sh', 'Щ' => 'Shch', 'щ' => 'shch', 'Ъ' => '', 'ъ' => '', 'Ы' => 'Y', 'ы' => 'y', 'Ь' => '', 'ь' => '', 
				'Э' => 'E', 'э' => 'e', 'Ю' => 'Yu', 'ю' => 'yu', 'Я' => 'Ya', 'я' => 'ya', '@' => 'y', '$' => 'ye'
				
			];
			
			$str = str_replace ($arStrES, $arStrRS, $str);
			$str = str_replace ($arStrOS, $arStrRS, $str);
			$str = strtr ($str, $replace);
			
		} else {
			
			$letters = [
				
				'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'Ye', 'Ю' => 'Yu', 'Я' => 'Ya', 'Ї' => 'Yi', 'Є' => 'Ye',
				
				'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'ye', 'ю' => 'yu', 'я' => 'ya', 'ї' => 'i', 'є' => 'ie',
				
			];
			
			foreach ($letters as $find => $replace)
			$str = str_replace ($find, $replace, $str);
			
		}
		
		//$str = to_unicode ($str);
		
		if ($options['strip_sumb'])
		$str = str_replace ($options['strip_sumb'], '', $str);
		
		$str = str_replace (['.'], '-', $str);
		if ($options['bottom_gap']) $str = str_replace ('-', '_', $str);
		
		if ($options['alt_name']) $str = lisas_strtolower ($str); // Строчные буквы
		
		$str = trim ($str, '-');
		$str = preg_replace (['~_{2,}~ms', '~\-{2,}~ms'], ['_', '-'], $str);
		
		return $str;
		
	}
	
	//echo to_translit ('Втор№ая', ['alt_name' => 1]);
	
	function from_unicode ($t, $to = 'windows-1251') {
		return lisas_iconv ($t, CHARSET, $to);
	}
	
	function to_unicode ($t, $from = 'windows-1251') {
		return lisas_iconv ($t, $from, CHARSET);
	}
	
	function lisas_iconv ($t, $from, $to) {
		
		if ($from != $to and function_exists ('iconv')) {
			
			if ($from == 'UCS-2LE') $t = substr ($t, 0, -1);
			$t = iconv ($from, $to.'//TRANSLIT', $t);
			
		}
		
		return $t;
		
	}
	
	function lisas_strlen ($str) {
		return mb_strlen ($str, CHARSET);
	}
	
	function mb_string2array ($string, $encoding = CHARSET) {
		
		if (empty ($string)) return false;
		
		for ($strlen = mb_strlen ($string, $encoding); $strlen > 0;) {
			
			$array[] = mb_substr ($string, 0, 1, $encoding);
			$string = mb_substr ($string, 1, $strlen, $encoding);
			$strlen = ($strlen - 1);
			
		}
		
		return $array;
		
	}
	
	function to_utf8 ($dat) {
		
		if (is_array ($dat)) {
			
			$ret = [];
			
			foreach ($dat as $i => $d)
				$ret[$i] = to_utf8 ($d);
			
			return $ret;
			
		} elseif (is_string ($dat))
			return mb_convert_encoding ($dat, 'UTF-8', 'UTF-8');
		else
			return $dat;
		
	}