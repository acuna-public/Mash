<?php
	
	class Date {
		
		public $date, $time = 0, $explode = [], $debug;
		
		const SECOND = 1;
		const MINUTE = (self::SECOND * 60);
		const HOUR = (self::MINUTE * 60);
		const DAY = (self::HOUR * 12);
		const DAYNIGHT = (self::DAY * 2);
		const WEEK = (self::DAYNIGHT * 7);
		const MONTH = (self::WEEK * 4);
		const YEAR = (self::MONTH * 12);
		
		function __construct ($date = '', $options = [], $debug = 0) {
			
			if ($date instanceof Date)
				$date = $date->getTime ();
			
			$this->date = $date;
			
			$options = array_extend ($options, [
				
				'adjust' => 0,
				'strtotime' => 1,
				
			]);
			
			$this->debug = $debug;
			
			if (!is_numeric ($this->date) or strlen ($this->date) == 4) {
				
				if (!$this->empty ()) {
					
					$this->explode = $this->explode ();
					
					if ($year = $this->is_year ()) $this->date = $year.'-00-00';
					
					if ($options['strtotime'])
						$this->time = strtotime ($this->date);
					
					if ($options['adjust'])
						$this->time += $options['adjust'];
					
				} else {
					
					if (!$this->date) $this->date = time ();
					$this->time = $this->date;
					
					if ($options['adjust'])
						$this->time += $options['adjust'];
					
					$this->explode = [0];
					
				}
				
			} else {
				
				$this->time = $this->date;
				$this->explode = [$this->time];
				
			}
			
		}
		
		function empty () {
			return (!$this->date or $this->date == '0000-00-00');
		}
		
		function is_year () {
			
			if (
				strlen ($this->explode[0]) == 4
				and
				(
					!$this->explode[2] or $this->explode[2] == '00' or !$this->explode[3] or $this->explode[3] == '00'
				)
			) $date = $this->explode[1];
			else $date = null;
			
			return $date;
			
		}
		
		function show ($type = 0, $date = 0, $debug = 0) {
			
			if (!$date) $date = $this->time;
			
			if ($type) {
				
				switch ($type) {
					
					case 1:	$type = 'j F Y H:i:s'; break;
					case 2:	$type = 'j-F-Y H:i:s'; break;
					case 3:	$type = 'j m Y H:i:s'; break;
					case 4:	$type = 'd.m.Y H:i:s'; break;
					case 5:	$type = 'j F Y';			 break;
					case 6:	$type = 'd.m.Y';			 break;
					case 7:	$type = 'H:i';				 break;
					case 8:	$type = 'Y-m-d';			 break;
					case 9:	$type = 'j F Y H:i';	 break;
					case 10: $type = 'Y-m-d H:i';	 break;
					case 11: $type = 'd.m.Y H:i';	 break;
					
				}
				
			} else $type = 'YmdHis';
			
			if ($year = $this->is_year ())
				$date = $year;
			elseif (!$this->empty ())
				$date = date ($type, $date);
			
			return $date;
			
		}
		
		function age ($lang = ['год', 'года', 'лет'], $debug = 0) { // Функция вывода возраста по дате $date вида yyyy или yyyy-mm-dd. По умолчанию используется русская локаль. Если вы хотите использовать другой язык, добавьте массив $lang с тремя значениями.
			
			if ($debug) debug ($this->time);
			
			$date = new DateTime ($this->time);
			$now = new DateTime ();
			
			$interval = $now->diff ($date);
			$word = str_suffix ($interval->y, $lang);
			
			return $interval->y.' '.$word;
			
		}
		
		private function _add ($sec, $date = 0) { // Время через $sec секунд
			
			if (!$date) $date = time ();
			
			if (is_numeric ($sec))
				$date = ($date + $sec);
			else
				$date = strtotime ($sec, $date);
			
			return $this->show (0, $date);
			
		}
		
		//function add ($min) { // Время через $min секунд
		//	return $this->_add (' + '.$min.' seconds');
		//}
		
		function add_min ($min) { // Время через $min минут
			return $this->_add (' + '.$min.' minute');
		}
		
		private function _add_day ($day, $date = 0) {
			return $this->_add (' + '.$day.' day', $date);
		}
		
		function add_day ($day) { // Время через $day дней
			return $this->_add_day ($day);
		}
		
		function is_new_day ($date, $num = 1) { // Возвращает true, если прошло $num суток с даты $date
			return ($this->format () > $this->_add_day ($num, $date));
		}
		
		function explode () {
			
			if (!$this->empty () and !is_numeric ($this->date)) {
				
				$d = preg_split ('~[\s\-:\./\/]~', $this->date);
				
				if (isset ($d[0])) $y = $d[0]; else $y = '0000';
				if (isset ($d[1])) $m = $d[1]; else $m = '00';
				if (isset ($d[2])) $d = $d[2]; else $d = '00';
				if (isset ($d[3])) $h = $d[3]; else $h = '00';
				if (isset ($d[4])) $mn = $d[4]; else $mn = '00';
				if (isset ($d[5])) $s = $d[5]; else $s = '00';
				
				if ($this->debug) debug ([$date, $y, $m, $d, $h, $mn, $s]);
				
				if ($m == '00' or $d == '00') {
					
					$date = ($y + 1).'-'.$m.'-'.$d;
					if ($h and $mn and $s) $date .= ' '.$h.':'.$mn.':'.$s;
					
				} elseif ($m and $d and (!$h or !$mn or !$s))
					$date = $y.'-'.$m.'-'.$d;
				else
					$date = $this->date;
				
				$output = [$date, $y, $m, $d, $h, $mn, $s];
				
			} else $output = [$this->date, $this->date, '00', '00', '00', '00', '00'];
			
			return $output;
			
		}
		
		function word ($locale = [], $lang = ['Вчера', 'Сегодня', 'Завтра'], $options = [], $debug = 0) {
			
			if (!$this->empty ()) {
				
				$options = array_extend ($options, [
					
					'type1' => 'j F Y G:i:s',
					'type2' => 'G:i:s',
					
				]);
				
				$date_null = 0;
				
				if (is_isset ('date_null', $options)) {
					
					$d = $this->explode ();
					
					if ($d[2] == '00' or $d[3] == '00') {
						
						$date_null = 1;
						$options['type1'] = 'Y';
						
					} elseif (!$d[4] or !$d[5] or !$d[6]) {
						
						$date_null = 1;
						$options['type1'] = 'j F Y';
						
					}
					
				}
				
				if (!is_isset ('force', $options)) {
					
					$this->date = new self();
					$date = $this->date->show ('Ymd');
					
					/*if (!$date_null and $date == $date) { // Сегодня
						
						$output = $lang[1];
						if ($this_date = $this->lang ($options['type2'], $locale))
						$output .= ', '.$this_date;
						
					} elseif (!$date_null and $date <= date ('Ymd', ($this->time - 86400))) { // Вчера
						
						$output = $lang[0];
						if ($this_date = $this->lang ($options['type2'], $locale))
						$output .= ', '.$this_date;
						
					} elseif (!$date_null and $date >= date ('Ymd', ($this->time + 86400))) { // Завтра
						
						$output = $lang[2];
						if ($this_date = $this->lang ($options['type2'], $locale))
						$output .= ', '.$this_date;
						
					} else*/if ($year = $this->is_year ()) // Год
						$output = $year.(is_isset ('end', $options) ? ' '.$options['end'] : '');
					else // Просто дата
						$output = $this->lang ($options['type1'], $locale).(is_isset ('end', $options) ? ' '.$options['end'] : '');
					
				} else $output = $this->lang ($options['type1'], $locale).(is_isset ('end', $options) ? ' '.$options['end'] : '');
				
				if ($debug) debug ($date.' - '.$date);
				
			} else $output = '';
			
			return $output;
			
		}
		
		private function lang ($format, $lang = []) {
			return strtr (@date ($format, $this->time), $lang);
		}
		
		function prep ($time = 1) {
			
			list ($date, $y, $m, $d, $h, $mn, $s) = $this->explode;
			
			if (!is_numeric ($date)) {
				
				$date = '';
				$m = $y;
				$y = $date;
				
			}
			
			$out = [];
			if (strlen ($y) == 4) $out[] = $y; else $out[] = '0000';
			if ($m) $out[] = $m; else $out[] = '00';
			if ($d) $out[] = $d; else $out[] = '00';
			
			$date = implode ('-', $out);
			
			$out = [];
			
			if ($time) {
				
				if ($h) $out[] = $h; else $out[] = '00';
				if ($mn) $out[] = $mn; else $out[] = '00';
				if ($s) $out[] = $s; else $out[] = '00';
				
			}
			
			$time = implode (':', $out);
			
			return $date.($time ? ' '.$time : '');
			
		}
		
		function add (int $type, int $num = 1): Date {
			
			$this->time += ($type * $num);
			
			return $this;
			
		}
		
		function isNew (): bool {
			return (time () > $this->time);
		}
		
		function getTime () {
			return $this->time;
		}
		
		function getTimeInMillis () {
			return ($this->getTime () * 1000);
		}
		
		function format ($format) {
			return date ($format, $this->time);
		}
		
		function __toString () {
			return $this->format ('d.m.Y H:i:s');
		}
		
	}