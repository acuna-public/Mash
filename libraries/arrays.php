<?php
/*
 ========================================
 Mash Framework (c) 2010-2017, 2019
 ----------------------------------------
 https://mash.ointeractive.ru/
 ========================================
 O! Interactive (support@ointeractive.ru)
 ----------------------------------------
 Библиотека
 -- Массивы
 ========================================
*/
	
	if (!defined ('MASH')) die ('File must be started only through the main framework cover');
	
	function make_array ($content) { // Принудительно создает массив, даже если $content пуст.
		
		if (!is_array ($content)) {
			
			if (not_empty ($content))
			$content = [$content];
			else
			$content = [];
			
		}
		
		return $content;
		
	}
	
	function array_extend (...$arrays) { // Присваивает значения элементов массива $array массиву, если его элементы не установлены
		
		$result = $arrays[0];
		
		foreach ($arrays as $array) {
			
			foreach ($array as $key => $value) {
				
				if (is_array ($value) and isset ($result[$key]) and is_array ($result[$key]))
					$result[$key] = array_extend ($value, $result[$key]);
				elseif (!isset ($result[$key])/* or ($value and $result[$key] != $value)*/)
					$result[$key] = $value;
				
			}
			
		}
		
		return $result;
		
	}
	
	function array_extend2 (...$arrays) { // Присваивает значения элементов массива $array массиву, если его элементы не установлены
		
		$result = $arrays[0];
		
		foreach ($arrays as $array) {
			
			foreach ($array as $key => $value) {
				
				if (is_array ($value) and isset ($result[$key]) and is_array ($result[$key]))
					$result[$key] = array_extend ($value, $result[$key]);
				else
					$result[$key] = $value;
				
			}
			
		}
		
		return $result;
		
	}
	
	function array_keys_extend ($array, $keys) {
		
		$output = [];
		foreach ($keys as $i => $key)
		$output[$key] = $array[$i];
		
		return $output;
		
	}
	
	function array_random (array $array) {
		return $array[array_rand ($array, 1)];
	}
	
	function file2array ($file) {
		
		$output = [];
		
		$fp = fopen ($file, 'r');
		
		while (!feof ($fp))
			if ($str = trim (fgets ($fp)))
			$output[] = $str;
		
		fclose ($fp);
		
		return $output;
		
	}
	
	function _array_rand (array $array) {
		
		$count = count ($array);
		if ($count) $count = ($count - 1);
		
		return $array[rand (0, $count)];
		
	}
	
	function array_mt_rand (array $array, $num = 1) {
		
		if ($num > 1) {
			
			$output = [];
			foreach (range (0, $num) as $i)
			$output[] = _array_rand ($array);
			
		} else $output = _array_rand ($array);
		
		return $output;
		
	}
	
	function end_key (array $array) { // Ключ конечного элемента массива
		return (count ($array) - 1);
	}
	
	function end_value (array $array) {
		return $array[end_key ($array)];
	}
	
	function nl2array ($str) {
		
		if (!is_array ($str)) {
			
			$str = str_replace ("\r", '', $str);
			$str = explode ("\n", $str);
			
		}
		
		return $str;
		
	}
	
	function str_to_key ($str) {
		return lisas_strtolower (stripslashes (trim ($str)));
	}
	
	function get_value ($what, array $where) { // Проверяет наличие ключа в строке $where и возвращает значение $value при удаче.
		
		foreach ($where as $key => $value)
		if (strstr ($what, $key)) return $value;
		
		return false;
		
	}
	
	function row_array (array $array1, array $array2) {
		
		$i = 0;
		$row = [];
		
		foreach ($array1 as $id => $rows) {
			
			$row[$rows] = $array2[$i];
			++$i;
			
		}
		
		return $row;
		
	}
	
	function array_implode ($content, $sumb = '', $blank = 0) {
		
		$content = make_array ($content, $blank);
		$content = implode ($sumb, $content);
		return $content;
		
	}
	
	function build_array ($input, $deny_keys = [], $allow_keys = []) {
		
		if (is_array ($input)) {
			
			$row = [];
			
			foreach ($input as $key => $value)
			if (
				($deny_keys and !in_array ($key, $deny_keys)) or
				($allow_keys and in_array ($key, $allow_keys)) or
				(!$deny_keys and !$allow_keys)
			) $row[$key] = $value;
			
		} else $row = [$input];
		
		return $row;
		
	}
	
	function build_vq_array ($array, $blank = 1, $first_blank = 0, $deny_keys = []) {
		
		$array = build_array ($array, $deny_keys);
		
		if ($blank == 1) $blank = ' '; else $blank = '';
		$row = [];
		$i = 0;
		
		foreach ($array as $key => $value) {
			++$i;
			
			if ($first_blank and $i == 1) $first_blank = ' '; else $first_blank = '';
			
			$row[] = $first_blank.$key.$blank.'='.$blank.'"'.$value.'"';
			
		}
		
		return $row;
		
	}
	
	function array_empty ($array) {
		if (!not_empty ($array[0]) and !not_empty ($array[1])) return true; else return false;
	}
	
	function trim_array (array $array, $count) { // Сокращает массив $array до количества элементов $count
		
		$count = (int) $count;
		
		if ($count > 0 and count ($array) > $count) {
			
			if ($count >= 1) $count = $count - 1;
			
			$new_array = [];
			foreach (range (0, $count) as $num)
			$new_array[] = $array[$num];
			
			return $new_array;
			
		} else return $array;
		
	}
	
	function array_average_value (array $array1, array $array2, $id1) {
		
		$count_array1 = count ($array1);
		$count_array2 = count ($array2);
		
		$i = 0;
		
		foreach (range (0, $count_array1) as $range) {
			
			$min = $range * $count_array2;
			$max = $min + ($count_array2 - 1);
			
			foreach ($array2 as $id2 => $value) {
				
				if ($id1 >= $min and $id1 <= $max) $i = $id2;
				
			}
			
		}
		
		return $i;
		
	}
	
	function array_chear (array $array, $min, $max) { // Срез массива от $min до $max
		
		sort ($array);
		reset ($array);
		
		$array2 = [];
		for ($i = $min; $i < $max; ++$i) $array2[] = $array[$i];
		
		return $array2;
		
	}
	
	function array_move (array $array, $id, $dist) {
		
		$dist = (int) $dist;
		$value1 = $array[$id]; // Что переносим
		
		$real_count = count ($array) - 1;
		$real_count2 = $real_count + 1;
		
		$id2 = $id + $dist;
		
		if ($id > $real_count or $id2 < 0) { // С начала в конец
			
			$array[] = $value1;
			unset ($array[0]);
			
		} elseif ($id2 > $real_count or $id < 0) { // С конца в начало
			
			array_unshift ($array, $value1);
			unset ($array[$real_count2]);
			
		} else { // Просто вверх/вниз
			
			$array[$id] = $array[$id2];
			$array[$id2] = $value1;
			
		}
		
		return $array;
		
	}
	
	function num_array ($type, array $array) {
		
		/* Работа с числовыми массивами.
		$type может иметь следующие значения:
		min - выводит наименьшее число в массиве $array
		max - выводит наибольшее число в массиве $array */
		
		foreach ($array as $num) if (!(int) $num) die ('Error: num_arr: Массив $array может содержать только численные значения.');
		
		sort ($array);
		
		if ($type == 'min') return $array[0];
		if ($type == 'max') return end ($array);
		
	}
	
	function array_edit (array $array1, array $array2) {
		
		$keys = array_keys ($array2);
		
		$new_array = [];
		foreach ($array1 as $key2 => $value2) // Что заменить
		if (in_array ($key2, $keys)) $new_array[$key2] = $array2[$key2];
		else $new_array[$key2] = $value2;
		
		return $new_array;
		
	}
	
	function array_delete (array $what, array $where) { // Удаляем элементы массива $what из массива $where
		
		$output = [];
		
		$where = array_unique ($where);
		$what = array_unique ($what);
		
		$i = 0; // Сбрасываем счетчик ключей
		
		foreach (array_diff ($where, $what) as $value) {
			
			$output[$i] = $value;
			++$i;
			
		}
		
		return $output;
		
	}
	
	function array_delete_key ($keys, array $array, $trow = 0) {
		
		$keys = sep_force_explode ($keys);
		$array = sep_force_explode ($array);
		
		foreach ($keys as $key) {
			
			$find = array_search ($key, $array);
			if (($find != false and $find != null) or $trow) unset ($array[$find]);
			
		}
		
		return $array;
		
	}
	
	function array_rand_value (array $array) {
		return $array[rand (0, (count ($array) - 1))];
	}
	
	function in_array_key ($key, array $array) { // Проверяет ключ $key в массиве $array. Введена на замену стандартной array_search (), которая иногда ведет себя некорректно. Внимание! Если ключ $key пустой, и в массиве $array имеются пустые ключи, все-равно будет возвращено значение true.
		if (in_array ($key, array_keys ($array))) return true; else return false;
	}
	
	function array_merge_bivariate (array $arrays) { // Объединяет двумерные массивы $arrays
		
		$output = [];
		foreach ($arrays as $array) foreach ($array as $key => $value) $output[$key] = $value;
		
		return $output;
		
	}
	
	function array_merge_diff (array $old, array $new) {
		return array_merge ($old, array_diff ($new, $old));
	}
	
	function array_parse (array $array, $deny_keys = [], $no_empty = 0, $row = [], $debug = 0) {
		
		if ($deny_keys or $row) {
			
			$output = [];
			
			foreach ($array as $key => $value) {
				
				$allow = 0;
				
				if (!in_array ($key, $deny_keys)) {
					
					if ($no_empty and not_empty ($value)) $allow = 1;
					elseif (!$no_empty) {
						if ($row and $row[$key] != $value) $allow = 1;
					}
					
				}
				
				if ($allow) $output[$key] = $value;
				
			}
			
		} else $output = $array;
		
		if ($debug) print_r ($output);
		return $output;
		
	}
	
	function array_carve (array $array1, array $array2) {
		
		$i = 0;
		$output = [];
		
		foreach ($array1 as $key => $val)
		if (!in_array ($val, $array2)) {
			
			$output[$i] = $val;
			++$i;
			
		}
		
		return $output;
		
	}
	
	function array_search_part ($needle, array $array) {
		
		$output = [];
		
		if ($array)
		foreach ($array as $key => $value)
		if (stripos (lisas_strtolower ($value), lisas_strtolower ($needle)) !== false)
		$output[] = $key;
		
		return $output;
		
	}
	
	function prep_array (array $array) {
		
		foreach ($array as $key => $value) $array[$key] = prep_var ($value);
		return $array;
		
	}
	
	function is_assoc ($array) {
		return (is_array ($array) and array_values ($array) != $array);
	}
	
	function str_replace_assoc ($replace, $subject) { 
		return str_replace (array_keys ($replace), array_values ($replace), $subject);
	}
	
	function array_shuffle ($match, $cols = []) {
		
		$output = [];
		
		for ($i = 0; $i < count ($match[0]); ++$i) {
			
			$i3 = 0;
			
			for ($i2 = 1; $i2 < count ($match); ++$i2) {
				
				if ($cols[$i3]) $key = $cols[$i3]; else $key = $i3;
				$output[$i][$key] = $match[$i2][$i];
				++$i3;
				
			}
			
		}
		
		return $output;
		
	}
	
	function is_isset ($what, $where) {
		return (isset ($where[$what]) and $where[$what]);
	}
	
	function set_items ($items, $where, $def = '', $output = []): array {
		
		foreach ($items as $key)
			if (is_isset ($key, $where))
				$output[$key] = $where[$key];
			else
				$output[$key] = $def;
		
		return $output;
		
	}