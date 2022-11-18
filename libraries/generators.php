<?php
/*
 ========================================
 Mash Framework (c) 2010-2017
 ----------------------------------------
 https://mash.ointeractive.ru/
 ========================================
 O! Interactive (support@ointeractive.ru)
 ----------------------------------------
 Библиотека
 -- Генераторы
 ========================================
*/
	
	if (!defined ('MASH')) die ('File must be started only through the main framework cover');
	
	function poem_rand ($data = []) {
		
		if (!$data) $data = [
			
			[
				
				'Я помню',
				'Не помню',
				'Забыть бы',
				'Какое',
				'Угробил',
				'Хреново',
				'Открою',
				
			],
			
			[
				
				'странное',
				'некое',
				'вкусное',
				'пьяное',
				'свинское',
				'четкое',
				'сраное',
				'нужное',
				'конское',
				
			],
			
			[
				
				'затменье:',
				'хотенье:',
				'варенье:',
				'творенье:',
				'везенье:',
				'рожденье:',
				'смущенье:',
				'печенье:',
				'ученье:',
				
			],
			
			[
				
				'под косячком',
				'на клабдище',
				'в моих мечтах',
				'под скальпилем',
				'в моих штанах',
				'из-за угла',
				'в моих ушах',
				'из головы',
				
			],
			
			[
				
				'добилась ты,',
				'торчат кресты,',
				'стихов листы,',
				'мои трусы,',
				'поют дрозды,',
				'из темноты,',
				
			],
			
			'как',
			
			[
				
				'детородное',
				'психотропное',
				'кайфоломное',
				'очевидное',
				'у воробушков',
				'эдакое вот',
				'нам не чуждое',
				'благородное',
				
			],
			
			[
				
				'сиденье,',
				'паренье,',
				'сужденье,',
				'врещенье,',
				'сношенье,',
				'смятенье,',
				'теченье,',
				'паденье,',
				'сплетенье,',
				
			],
			
			'как',
			
			[
				
				'сторож',
				'символ',
				'правда',
				'ангел',
				'водка',
				'пиво',
				'жопа',
				
			],
			
			[
				
				'вечной',
				'просит',
				'грязной',
				'липкой',
				'нахрен',
				'в пене',
				'женской',
				'жаждет',
				
			],
			
			[
				
				'мерзлоты.',
				'суеты.',
				'наркоты.',
				'срамоты.',
				'школоты.',
				'типа ты.',
				'простоты.',
				'наготы.',
				
			],
			
		];
		
		$output = [];
		
		foreach ($data as $key => $value) {
			
			if (is_array ($value))
			$output[] = $value[array_rand ($value)];
			else
			$output[] = $value;
			
		}
		
		return implode (' ', $output);
		
	}
	
	function _lorem_ipsum_sents ($sents, $parag_num, $sents_num, $sents_count) {
		
		if ($sents_count < ($parag_num * $sents_num)) {
			
			$i = $sents_count;
			
			for ($i2 = 0; $i2 < $sents_count; ++$i2) {
				
				$sents[$i] = $sents[$i2];
				++$i;
				
			}
			
			$sents = _lorem_ipsum_sents ($sents, $parag_num, $sents_num, $i);
			
		}
		
		return $sents;
		
	}
	
	function lorem_ipsum ($parag_num = 1, $sents_num = 40, $rand = false, $parags_breaks = '<p>', $breaks_num = 2, $text = 'en', $sents_sep = '. ') {
		
		$output = '';
		
		if ($text) {
			
			if (in_array ($text, ['en', 'ru']))
			$text = file_get_content (MASH_DIR.'libraries/lorem_ipsum/'.$text.'.txt');
			
			$parags = nl_explode ($text);
			
			if ($rand) $parag_id = rand (1, (count ($parags) - 1)); else $parag_id = 0;
			$sents = explode ('. ', trim ($parags[$parag_id]));
			
			$parag_num = intval_correct ($parag_num, 1);
			$sents_num = intval_correct ($sents_num, 1);
			
			$sents = _lorem_ipsum_sents ($sents, $parag_num, $sents_num, count ($sents));
			
			for ($i = 0; $i < $parag_num; ++$i) {
				
				$start = ($i * $sents_num);
				$finish = ($sents_num + $start);
				
				$sents_array = [];
				for ($i2 = $start; $i2 < $finish; ++$i2)
				$sents_array[] = trim (rtrim ($sents[$i2], '.'));
				
				$sents_imp = implode ($sents_sep, $sents_array);
				
				$breaks = '';
				if ($parags_breaks != '<p>')
				for ($i3 = 0; $i3 < $breaks_num; ++$i3) $breaks .= $parags_breaks;
				
				if ($parags_breaks == '<p>')
				$output .= '<p>'.$sents_imp.'.</p>
';
				else
				$output .= $sents_imp.'.'.$breaks;
				
			}
			
			$output = trim_br ($output);
			
		}
		
		return $output;
		
	}
	
	function unit_convert ($num, $from, $to) {
		
		$units = [
			
			[
				
				'mm' => 0.1,
				'cm' => 1,
				'm' => 100,
				'dm' => 1000,
				
				'верста' => 106680,
				'сажень косая' => 248,
				'сажень казенная' => 213.36,
				'сажень маховая' => 178,
				'аршин' => 71.12,
				'локоть' => 45,
				'пядь' => 17.78,
				'ладонь' => 7.5,
				'вершок' => 4.445,
				'перст' => 2,
				'ноготь' => 1.11125,
				
				'фут' => 30.48,
				'дюйм' => 2.54,
				'линия' => 0.254,
				'точка' => 0.0254,
				
			],
			
			[
				
				'л' => 1,
				
				'кадка' => 839.69,
				'половник' => 419.84,
				'четверть' => 209.912,
				'осьмина' => 104.956,
				'четверик' => 26.2387,
				'ведро' => 13.1192,
				'штоф' => 1.6398,
				'гарнец' => 3.2798,
				'стакан' => 0.2733,
				
			],
			
			[
				
				'штука' => 1,
				'дюжина' => 12,
				'гросс' => 144,
				
			],
			
			[
				
				'лист' => 1,
				'десть' => 24,
				'стопа' => 480,
				
			],
			
		];
		
		foreach ($units as $type => $values)
		foreach ($values as $key => $value)
		if ($key == $from)
		return ($num * ($units[$type][$from] / $units[$type][$to]));
		
	}
	
	//echo unit_convert (1, 'десть', 'стопа');