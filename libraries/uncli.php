<?php
/*
 ========================================
 Mash Framework (c) 2010-2016
 ----------------------------------------
 https://mash.ointeractive.ru/
 ========================================
 O! Interactive (support@ointeractive.ru)
 ----------------------------------------
 Не-CLI функции
 ========================================
*/
	
	if (!defined ('MASH')) die ('File must be started only through the main framework cover');
	
	$small_letters_array = ['а','б','в','г','д','е','ё','ж','з','и','й',
'к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'];
	$large_letters_array = ['А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','A','B','C','D','E','F','G','H','I','L','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Функции отладки
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	function showorhide ($title, $id, $content) {
	
		$output = '<a href="javascript://" onclick="ShowOrHide (\''.$id.'\'); return false;">'.$title.'</a>
<div id="'.$id.'" style="display:none;">'.$content.'</div>';
		
		return $output;
		
	}
	
	function get_id ($name, $categories, $row_type = 'alt_name', $id_type = 'id', $lower = 0) {
		
		$id = '';
		
		if ($categories)
		foreach ($categories as $cats)
		if (($lower and lisas_strtolower ($cats[$row_type]) == lisas_strtolower ($name)) or (!$lower and $cats[$row_type] == $name)) $id = $cats[$id_type];
		
		return $id;
		
	}
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Работа с датами
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	function birthdate_empty ($date) {
		if ($date == '0000-00-00') return true; else return false;
	}
	
	function check_date ($date) {
		
		$error = [];
		
		list ($y, $m, $d, $h, $mn, $s) = explode_date ($date);
		$y = (int) $y; $m = (int) $m; $d = (int) $d; $h = (int) $h; $mn = (int) $mn; $s = (int) $s;
		
		if ($y) {
			
			if ($y <= (date ('Y') - 100) or $y >= (date ('Y') - 5)) $error[] = 3; // Год слишком большой
			elseif (lisas_strlen ($y) != 4) $error[] = 2; // Ошибка в годе (не равен 4 символам)
			
		} else $error[] = 1; // Нет года
		
		if ($m < 1) $error[] = 4; // Нет месяца
		if ($d == 31 and ($m == 2 or $m == 4 or $m == 6 or $m == 9 or $m == 11)) $error[] = 5; // Ошибка в месяце (31 день в четных)
		if ($m > 12) $error[] = 6; // Ошибка в месяце (больше 12)
		
		if ($d < 1) $error[] = 7; // Дня нет
		if ($m == 2 and $d > 29) $error[] = 8; // Ошибка в дне (февраль)
		if ($d > 31) $error[] = 9; // Ошибка в дне (больше 31)
		
		if ($h < 0) $error[] = 10; // Нет часов
		if ($h > 23) $error[] = 11; // Ошибка в часах
		if ($mn < 0) $error[] = 12; // Нет минут
		if ($mn > 59) $error[] = 13; // Ошибка в минутах
		if ($s < 0) $error[] = 14; // Нет секунд
		if ($s > 59) $error[] = 15; // Ошибка в секундах
		
		if ($error) $output = $error; else $output = 1;
		
		return $output;
		
	}
	
	function correct_date ($date) {
		
		list ($y, $m, $d, $h, $mn, $s) = explode_date ($date);
		$check_date = check_date ($date);
		
		if ($check_date != 1) foreach ($check_date as $date_error)
		switch ($date_error) {
			
			case 1: case 2: $y = '0001'; break;
			case 3: $m = 1; break;
			case 4: $m = 30; break;
			case 5: $m = 12; break;
			case 6: $d = 1; break;
			case 7: $d = 28; break;
			case 8: $d = 31; break;
			case 9: $h = 0; break;
			case 10: $h = 23; break;
			case 11: $mn = 0; break;
			case 12: $mn = 59; break;
			case 13: $s = 0; break;
			case 14: $s = 59; break;
			
		}
		
		$date = $y.'-'.add_zero ($m).'-'.add_zero ($d);
		if ($h and $mn and $s) $date .= ' '.add_zero ($h).':'.add_zero ($mn).':'.add_zero ($s);
		
		return $date;
		
	}
	
	function date_period ($year, $month, $type = 0) { // Получение информации о текущем периоде времени. Выводится в date ().
		
		// $type пожет принимать следующие значения:
		
		// 0	- Текущий период
		// -1 - Предыдущий период
		// 1	- Следующий период
		
		return mktime (0, 0, 0, ($month + (int) $type), 7, $year);
		
	}
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Работа с массивами
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	///Работа с массивами
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	function obfuscate ($lib, $text, $file = '') {
		
		require 'lib/'.$lib.'.php';
		return $text;
		
	}
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Работа с числами
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Служебные функции
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	/*function elem_exists ($array) { // Возвращает false, если в массиве нет элементов
		
		$array_count = count ($array) - 1;
		
		if ($array_count <= 0) return false;
		else return true;
		
	}*/