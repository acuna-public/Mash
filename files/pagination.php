<?php
/*
 ========================================
 Mash Framework (c) 2010-2016
 ----------------------------------------
 https://mash.ointeractive.ru/
 ========================================
 O! Interactive (support@ointeractive.ru)
 ----------------------------------------
 LisaS.Фреймворк
 -- Постраничная навигация
 ========================================
*/
	
	if (!defined ('MASH')) die ('File must be started only through the main framework cover');
	
	$pages_show = 7; // Это количество страниц будет показано без сокращений
	
	$count_all = intval_correct ($count_all);
	$per_page = intval_correct ($per_page);
	
	$pages_count = $this->pages_num ($count_all, $per_page); // Количество страниц
	
	$templ = '';
	
	if ($per_page and $pages_count > 1) {
		
		if ($no_fm) {
			
			$this->mash->tpl->load ('pagination');
			$this->mash->tpl->compile ('pagination');
			$templ = $this->mash->tpl->result['pagination'];
			
		} else $templ = $this->mash->load ($this->mash->config['template'], 'pagination');
		
		$pages = '';
		
		if ($page > 1)
		$templ = preg_replace ('~\[link_prev\](.*?)\[/link_prev\]~si', $this->link_page (($page - 1), '\\1', $link, $options), $templ);
		else
		$templ = preg_replace ('~\[link_prev\](.*?)\[/link_prev\]~si', '', $templ);
		
		if ($page) {
			
			if ($pages_count <= $pages_show) {
				
				for ($j = 1; $j <= $pages_count; ++$j)
				if ($j == $page) // Выбрана страница
				$pages .= '<li class="active"><span>'.$j.'</span></li>';
				else
				$pages .= $this->link_page ($j, $j, $link, $options);
				
			} else { // Уже больше, чем $pages_show страниц
				
				$start = 1;
				$end = $pages_show;
				
				$nav_prefix = '<li class="blank"><span>...</span></li>';
				
				if ($page > 5) {
					
					$start = ($page - 4);
					$end = ($start + 8);
					
					if ($end >= $pages_count) {
						
						$start = ($pages_count - $pages_show);
						$end = ($pages_count - 1);
						
					}
					
				}
				
				if ($start >= 2) $pages .= $this->link_page (1, 1, $link, $options).$nav_prefix;
				
				for ($j = $start; $j <= $end; ++$j)
				if ($j == $page) // Выбрана страница
				$pages .= '<li class="active"><span>'.$j.'</span></li>';
				else
				$pages .= $this->link_page ($j, $j, $link, $options);
			
				if ($page == $pages_count) // Выбрана последняя страница
				$pages .= '<li class="active"><span>'.$pages_count.'</span></li>';
				else
				$pages .= $nav_prefix.$this->link_page ($pages_count, $pages_count, $link, $options);
				
			}
			
		}
		
		$templ = str_replace ('{pages}', trim ($pages), $templ);
		
		if ($page < $pages_count) // Ссылка на следующую страницу
		$templ = preg_replace ('~\[link_next\](.*?)\[/link_next\]~si', $this->link_page (($page + 1), '\\1', $link, $options), $templ);
		else
		$templ = preg_replace ('~\[link_next\](.*?)\[/link_next\]~si', '', $templ);
		
	}