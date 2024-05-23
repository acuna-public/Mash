<?php
/*
 ========================================
 Mash Framework (c) 2010-2017, 2023
 ----------------------------------------
 https://mash.ointeractive.ru/
 ========================================
 O! Interactive (support@ointeractive.ru)
 ----------------------------------------
 Библиотека
 -- Работа с числами
 ========================================
*/
	
	if (!defined ('MASH')) die ('File must be started only through the main framework cover');
	
	define ('SUMB_DIGITS', '0123456789');
	define ('SUMB_SPECIAL', '!?@#~$%^&*№+=;:«»[]');
	define ('SUMB_SPECIAL_2', ',"\'\/()—');
	define ('SUMB_LETTERS_LOW', 'abcdefghijklmnopqrstuvwxyz');
	define ('SUMB_LETTERS_UP', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
	define ('MAXINT', 2147483647);
	
	function division ($x, $y) { // Безопасное деление $x на $y
		
		$x = (int) $x; $y = (int) $y;
		if ($y == 0) $y = 1;
		return ($x / $y);
		
	}
	
	function int2range ($x, $y) {
		
		$output = [];
		$num = division ($x, $y); // 1000
		
		foreach (range (1, $y) as $range) $output[$range] = [
			
			ceil ($num * ($range - 1)),
			ceil ($num * $range)
			
		];
		
		return $output;
		
	}
	
	function in_range ($num, $min, $max) {
		return ($num >= $min and $num <= $max);
	}
	
	function get_percent ($num, $limit) {
		
		$proc = ((100 * $num) / $limit);
		$proc = round ($proc, 2);
		
		return $proc;
		
	}
	
	function intval_correct ($int, $int_val = 0, $int_min = 0) { // Представляет $int как числовое значение. Если результат <= $int_min, то возвращает значение $int_val.
		
		$int = (int) $int;
		if ($int <= $int_min) $int = $int_val;
		
		return $int;
		
	}
	
	function intval_rcorrect ($int, $int_max) {
		
		$int = (int) $int;
		if ($int > $int_max) $int = $int_max;
		
		return $int;
		
	}
	
	function deduction ($first, $second, $positive = 1, $min = 0) { // Вычитает $second из $first, если $first положительно и больше $second.
		
		if ($positive) {
			
			$first = intval_correct ($first);
			$second = intval_correct ($second);
			
		}
		
		if ($first > 0 and $first > $second) $output = ($first - $second); else $output = 0;
		//if (isset ($min)) $output = intval_correct ($output, $min);
		
		return (int) $output;
		
	}
	
	function prep_currency ($string, $add_zero = 0, $comma = 0) { // Форматирует строку $string, добавляя ведущий 0, если нужно
		
		if ($comma) {
			
			$string = str_replace ('.', ',', $string);
			$sep = ',';
			
		} else {
			
			$string = str_replace (',', '.', $string);
			$sep = '.';
			
		}
		
		$string = explode ($sep, $string);
		
		if ($add_zero) {
			
			if (!$string[1] or $string[1] == '00') $string[1] = $sep.'00';
			elseif ($string[1] and lisas_strlen ($string[1]) == 1 and $string[1] <= 9) $string[1] = $sep.$string[1].'0';
			else $string[1] = $sep.$string[1];
			
		} else {
			
			if (!$string[1] or $string[1] == '0' or $string[1] == '00') $string[1] = '';
			elseif ($string[1] and lisas_strlen ($string[1]) == 1 and $string[1] <= 9) $string[1] = $sep.$string[1].'0';
			else $string[1] = $sep.$string[1];
			
		}
		
		$string = $string[0].$string[1];
		
		return $string;
		
	}
	
	function rand_r ($min, $max, $factor = 0, $i = 0) {
		
		$num = rand ($min, $max);
		
		if ($factor and $num%$factor) {
			++$i;
			
			$num = rand_r ($min, $max, $factor, $i);
			
		}
		
		return $num;
		
	}
	
	function strtonum ($string, $check, $magic) {
		
		$int32 = 4294967296; // 2^32
		$length = lisas_strlen ($string);
		
		for ($i = 0; $i < $length; ++$i) {
			
			$check *= $magic;
			
			if ($check >= $int32) {
				
				$check = ($check - $int32 * (int) ($check / $int32));
				$check = ($check < -($int32 / 2)) ? ($check + $int32) : $check;
				
			}
			
			$check += ord ($string[$i]);
			
		}
		
		return $check;
		
	}
	
	function num_convert ($n, $g = ' ', $c = 3) { // Разделяет строку $n разделителем $g через $c символов.
		
		if ($decimal = strstr ($n, '.')) $n = str_replace ($decimal, '', $n);
		return strrev (wordwrap (strrev (strval ($n)), $c, $g, 1)).$decimal;
		
	}
	
	function is_valid_credit_card ($s) {
		
		// оставить только цифры
		$s = lisas_strrev (preg_replace ('/[^\d]/', '', $s));
		
		$sum = 0;
		
		for ($i = 0; $i < lisas_strlen ($s); ++$i) {
			
			if (($i % 2) != 0) { // удвоить нечетные цифры и вычесть 9, если они больше 9
				
				$val = ($s[$i] * 2);
				if ($val > 9) $val -= 9;
				
			} else $val = $s[$i]; // использовать четные цифры как есть
			
			$sum += $val;
			
		}
		
		// число корректно если сумма равна 10
		return (($sum % 10) == 0);
		
	}
	
	function add_zero ($number, $num = 2) { // Добавляет ведущий нуль, если число $number меньше, чем его $num-значный эквивалент. Короче, нуль ведущий добавляет.
		
		if (is_numeric ($number) and lisas_strlen ($number) < $num) $number = '0'.$number;
		//if ($number == 00) $number = 0;
		
		return $number;
		
	}
	
	function fib ($item) {
		
		$a = 0;
		$b = 1;
		
		for ($i = 0; $i < $item; ++$i) {
			
			yield $a;
			
			$a = $b - $a;
			$b = $a + $b;
			
		}
		
	}
	
	function mash_number_format ($number, $decimals = 0, $decPoint = '.', $thousandsSep = ',') {
		
		$negation = ($number < 0) ? -1 : 1;
		$coefficient = pow (10, $decimals);
		$number = $negation * floor ((string) (abs ($number) * $coefficient)) / $coefficient;
		
		return number_format ($number, $decimals, $decPoint, $thousandsSep);
		
	}
	
	function prop ($from, $to, $delim = 100) {
		return ($from * 100) / $to;
	}
	
	function pos_round ($number, $precision = 0, $mode = PHP_ROUND_HALF_UP, $max = 10) {
		
		$output = round ($number, $precision, $mode);
		
		if ($output != 0) {
			
			while ($output == 0) {
				
				$precision++;
				
				if ($precision <= $max)
					$output = round ($number, $precision, $mode);
				else
					break;
				
			}
			
		}
		
		return $output;
		
	}