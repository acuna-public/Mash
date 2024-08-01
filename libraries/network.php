<?php
/*
 ========================================
 Mash Framework (c) 2010-2017, 2019-2021
 ----------------------------------------
 https://mash.ointeractive.ru/
 ========================================
 O! Interactive (support@ointeractive.ru)
 ----------------------------------------
 Библиотеки
 -- Работа с сетью
 ========================================
*/
	
	if (!defined ('MASH')) die ('File must be started only through the main framework cover');
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Прокси
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	function _get_proxy_file () {
		
		if (file_exists (MASH_CACHE_DIR.'/proxy_get.txt'))
			return file_get_contents (MASH_CACHE_DIR.'/proxy_get.txt');
		
	}
	
	function proxy_get_array () {
		
		if ($file = _get_proxy_file ())
			return file2array (MASH_CACHE_DIR.'/'.$file.'.txt');
		else
			return [];
		
	}
	
	function proxy_check ($proxies, $options = []) {
		
		$curl = new Curl ();
		
		$options = array_extend ($options, ['success_codes' => [200, 201], 'check_url' => 'https://www.google.com', 'conn_timeout' => 5, 'timeout' => 5, 'limit' => 0]);
		
		if (!is_array ($proxies)) {
			
			$curl->setData (['method' => 'get', 'url' => $proxies]);
			$proxies = $curl->getItems ()[0]->getArray ();
			
		}
		
		foreach ($proxies as $i => $proxy) {
			
			if ($proxy and $proxy[0] != '#' and (($options['limit'] and $i < $options['limit']) or !$options['limit'])) {
				
				$options['method'] = 'get';
				$options['url'] = $options['check_url'];
				$options['proxy'] = $proxy;
				$options['user_agent'] = get_useragent (1);
				
				$curl->setData ($options)->setUserData (['num' => $i]);
				
			}
			
		}
		
		if ($file = _get_proxy_file ()) {
			
			if ($file == 'proxy1')
				$file2 = 'proxy2';
			else
				$file2 = 'proxy1';
			
		} else $file2 = 'proxy1';
		
		$file3 = new File (MASH_CACHE_DIR.'/'.$file2.'.txt');
		
		foreach ($curl->getItems () as $item) {
			
			try {
				
				if ($item->isOK ()) {debug ('---'.$proxies[$item->userData['num']]);
					$file3->rewrite ($proxies[$item->userData['num']].NL);
				}
				
			} catch (CurlException $e) {//debug ($e->getInfo ());
				// empty
			}
			
		}
		
		$file3 = new File (MASH_CACHE_DIR.'/proxy_get.txt');
		$file3->rewrite ($file2);
		
	}
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Юзерагенты
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	function _get_useragent_browser_os () {
		
		$frequencies = [
			
			34 => [
				
				89 => ['chrome', 'win'],
				9 => ['chrome', 'mac'],
				2 => ['chrome', 'linux'],
				
			],
			
			32 => [
				
				100 => ['iexplorer', 'win'],
				
			],
			
			25 => [
				
				83 => ['firefox', 'win'],
				16 => ['firefox', 'mac'],
				1 => ['firefox', 'linux'],
				
			],
			
			7 => [
				
				95 => ['safari', 'mac'],
				4 => ['safari', 'win'],
				1 => ['safari', 'linux'],
				
			],
			
			2 => [
				
				91 => ['opera', 'win'],
				6 => ['opera', 'linux'],
				3 => ['opera', 'mac'],
				
			],
			
		];
		
		$sum = 0;
		$rand = rand (1, 100);
		
		foreach ($frequencies as $freq => $freqs) {
			
			$sum += $freq;
			
			if ($rand <= $sum) {
				
				$sum = 0;
				$rand = rand (1, 100);
				
				foreach ($freqs as $freq => $choice) {
					
					$sum += $freq;
					if ($rand <= $sum) return $choice;
					
				}
				
			}
			
		}
		
	}
	
	function get_useragent ($random = 0, $lang = ['en-US']) {
		
		if ($random) {
			
			$nt_version = rand (5, 6).'.'.rand (0, 1);
			$osx_version = '10_'.rand (5, 7).'_'.rand (0, 9);
			
			list ($browser, $os) = _get_useragent_browser_os ();
			
			$proc = [
				
				'linux' => ['i686', 'x86_64'],
				'mac' => ['Intel', 'PPC', 'U; Intel', 'U; PPC'],
				'win' => ['foo'],
				
			];
			
			switch ($browser) {
				
				case 'firefox':
					
					$ver = array_random (array (
						
						'Gecko/'.date ('Ymd', rand (strtotime ('2011-1-1'), time ())).' Firefox/'.rand (5, 7).'.0',
						'Gecko/'.date ('Ymd', rand (strtotime ('2011-1-1'), time ())).' Firefox/'.rand (5, 7).'.0.1',
						'Gecko/'.date ('Ymd', rand (strtotime ('2010-1-1'), time ())).' Firefox/3.6.'.rand (1, 20),
						'Gecko/'.date ('Ymd', rand (strtotime ('2010-1-1'), time ())).' Firefox/3.8',
						
					));
					
					switch ($os) {
						
						default:
							$data = '(Windows NT '.$nt_version.'; '.array_random ($lang).'; rv:1.9.'.rand (0, 2).'.20) '.$ver;
						break;
						
						case 'linux':
							$data = '(X11; Linux '.array_random ($proc[$os]).'; rv:'.rand (5, 7).'.0) '.$ver;
						break;
						
						case 'mac':
							$data = '(Macintosh; '.array_random ($proc[$os]).' Mac OS X '.$osx_version.' rv:'.rand (2, 6).'.0) '.$ver;
						break;
						
					}
					
					$agent = 'Mozilla/5.0 '.$data;
					
				break;
				
				case 'safari':
					
					$version = rand (531, 535).'.'.rand (1, 50).'.'.rand (1, 7);
					
					if (rand (0, 1) == 0)
					$ver = rand (4, 5).'.'.rand (0, 1);
					else
					$ver = rand (4, 5).'.0.'.rand (1, 5);
					
					switch ($os) {
						
						default:
							$data = '(Windows; U; Windows NT '.$nt_version.') AppleWebKit/'.$version.' (KHTML, like Gecko) Version/'.$ver.' Safari/'.$version;
						break;
						
						case 'mac':
							$data = '(Macintosh; U; '.array_random ($proc[$os]).' Mac OS X '.$osx_version.' rv:'.rand (2, 6).'.0; '.array_random ($lang).') AppleWebKit/'.$version.' (KHTML, like Gecko) Version/'.$ver.' Safari/'.$version;
						break;
						
						case 'iphone':
							$data = '(iPod; U; CPU iPhone OS '.rand (3, 4).'_'.rand (0, 3).' like Mac OS X; '.array_random ($lang).') AppleWebKit/'.$version.' (KHTML, like Gecko) Version/'.rand (3, 4).'.0.5 Mobile/8B'.rand (111, 119).' Safari/6'.$version;
						break;
						
					}
					
					$agent = 'Mozilla/5.0 '.$data;
					
				break;
				
				case 'iexplorer':
					
					$ie_extra = array (
						
						'',
						'; .NET CLR 1.1.'.rand (4320, 4325).'',
						'; WOW64',
						
					);
					
					$version = rand (7, 9).'.0';
					$version2 = rand (3, 5).'.'.rand (0, 1);
					
					$data = '(compatible; MSIE '.$version.'; Windows NT '.$nt_version.'; Trident/'.$version2.')';
					
					$agent = 'Mozilla/5.0 '.$data;
					
				break;
				
				case 'opera':
					
					$op_extra = array (
						
						'',
						'; .NET CLR 1.1.'.rand (4320, 4325).'',
						'; WOW64',
						
					);
					
					$version = '2.9.'.rand (160, 190);
					$version2 = rand (10, 12).'.00';
					
					$arch = ['windows', 'linux'];
					
					switch (array_random ($arch)) {
						
						default:
							$data = '(Windows NT '.$nt_version.'; U; '.array_random ($lang).') Presto/'.$version.' Version/'.$version2;
						break;
						
						case 'linux':
							$data = '(X11; Linux '.array_random ($proc[$os]).'; U; '.array_random ($lang).') Presto/'.$version.' Version/'.$version2;
						break;
						
					}
					
					$agent = 'Opera/'.rand (8, 9).'.'.rand (10, 99).' '.$data;
					
				break;
				
				case 'chrome':
					
					$version = rand (531, 536).rand (0, 2);
					$version2 = rand (13, 15).'.0.'.rand (800, 899).'.0';
					
					switch ($os) {
						
						default:
							$data = '(Windows NT '.$nt_version.') AppleWebKit/'.$version.' (KHTML, like Gecko) Chrome/'.$version2.' Safari/'.$version;
						break;
						
						case 'linux':
							$data = '(X11; Linux '.array_random ($proc[$os]).') AppleWebKit/'.$version.' (KHTML, like Gecko) Chrome/'.$version2.' Safari/'.$version;
						break;
						
						case 'mac':
							$data = '(Macintosh; U; '.array_random ($proc[$os]).' Mac OS X '.$osx_version.') AppleWebKit/'.$version.' (KHTML, like Gecko) Chrome/'.$version2.' Safari/'.$version;
						break;
						
					}
					
					$agent = 'Mozilla/5.0 '.$data;
					
				break;
				
			}
			
		} else $agent = $_SERVER['HTTP_USER_AGENT'];
		
		return $agent;
		
	}
	
	function parse_useragent ($u_agent = '') {
		
		if (!$u_agent) $u_agent = get_useragent ();
		
		$empty = ['platform' => $platform, 'browser' => $browser, 'version' => $version];
		
		if (preg_match ('/\((.*?)\)/im', $u_agent, $parent_matches)) {
			
			preg_match_all ('/(?P<platform>BB\d+;|Android|CrOS|Tizen|iPhone|iPad|iPod|Linux|Macintosh|Windows(\ Phone)?|Silk|linux-gnu|BlackBerry|PlayBook|X11|(New\ )?Nintendo\ (WiiU?|3?DS)|Xbox(\ One)?)(?:\ [^;]*)?(?:;|$)/imx', $parent_matches[1], $result, PREG_PATTERN_ORDER);
			
			$priority = ['Xbox One', 'Xbox', 'Windows Phone', 'Tizen', 'Android', 'CrOS', 'X11'];
			
			$result['platform'] = array_unique ($result['platform']);
			
			if (count ($result['platform']) > 1) {
				
				if ($keys = array_intersect ($priority, $result['platform']))
				$platform = reset ($keys);
				else
				$platform = $result['platform'][0];
				
			} elseif (is_isset (0, $result['platform']))
			$platform = $result['platform'][0];
			
		}
		
		if ($platform == 'linux-gnu' or $platform == 'X11')
		$platform = 'Linux';
		elseif ($platform == 'CrOS')
		$platform = 'Chrome OS';
		
		preg_match_all ('%(?P<browser>Camino|Kindle(\ Fire)?|Firefox|Iceweasel|IceCat|Safari|MSIE|Trident|AppleWebKit|TizenBrowser|Chrome|Vivaldi|IEMobile|Opera|OPR|Silk|Midori|Edge|CriOS|UCBrowser|Puffin|SamsungBrowser|Baiduspider|Googlebot|YandexBot|bingbot|Lynx|Version|Wget|curl|Valve\ Steam\ Tenfoot|NintendoBrowser|PLAYSTATION\ (\d|Vita)+)(?:\)?;?)(?:(?:[:/ ])(?P<version>[0-9A-Z.]+)|/(?:[A-Z]*))%ix', $u_agent, $result, PREG_PATTERN_ORDER);
		
		if (!is_isset (0, $result['browser'])) {
			
			if (preg_match ('%^(?!Mozilla)(?P<browser>[A-Z0-9\-]+)(/(?P<version>[0-9A-Z.]+))?%ix', $u_agent, $result))
			return ['platform' => $platform ?: null, 'browser' => $result['browser'], 'version' => is_isset ('version', $result) ? $result['version'] ?: null : null];
			else
			return $empty;
			
		}
		
		if (preg_match ('/rv:(?P<version>[0-9A-Z.]+)/si', $u_agent, $rv_result))
		$rv_result = $rv_result['version'];
		
		$browser = $result['browser'][0];
		$version = $result['version'][0];
		
		$lowerBrowser = array_map ('strtolower', $result['browser']);
		
		$find = function ($search, &$key, &$value = null) use ($lowerBrowser) {
			
			if (is_array ($search))
			foreach ($search as $val) {
				
				$xkey = array_search (strtolower ($val), $lowerBrowser);
				
				if ($xkey !== false) {
					
					$value = $val;
					$key = $xkey;
					
					return true;
					
				}
				
			}
			
			return false;
			
		};
		
		$key = 0;
		$val = '';
		
		if ($browser == 'Iceweasel' or strtolower ($browser) == 'icecat') {
			
			$browser = 'Firefox';
			
		} elseif ($find ('Playstation Vita', $key)) {
			
			$platform = 'PlayStation Vita';
			$browser  = 'Browser';
			
		} elseif ($find (['Kindle Fire', 'Silk'], $key, $val)) {
			
			$browser  = $val == 'Silk' ? 'Silk' : 'Kindle';
			$platform = 'Kindle Fire';
			
			if (!($version = $result['version'][$key]) or !is_numeric ($version[0]))
			$version = $result['version'][array_search ('Version', $result['browser'])];
			
		} elseif ($find ('NintendoBrowser', $key) or $platform == 'Nintendo 3DS') {
			
			$browser = 'NintendoBrowser';
			$version = $result['version'][$key];
			
		} elseif ($find ('Kindle', $key, $platform)) {
			
			$browser = $result['browser'][$key];
			$version = $result['version'][$key];
			
		} elseif ($find ('OPR', $key)) {
			
			$browser = 'Opera Next';
			$version = $result['version'][$key];
			
		} elseif ($find ('Opera', $key, $browser)) {
			
			$find ('Version', $key);
			$version = $result['version'][$key];
			
		} elseif ($find ('Puffin', $key, $browser)) {
			
			$version = $result['version'][$key];
			
			if (strlen ($version) > 3) {
				
				$part = substr ($version, -2);
				
				if (ctype_upper ($part)) {
					
					$version = substr ($version, 0, -2);
					$flags = ['IP' => 'iPhone', 'IT' => 'iPad', 'AP' => 'Android', 'AT' => 'Android', 'WP' => 'Windows Phone', 'WT' => 'Windows'];
					
					if (is_isset ($part, $flags))
					$platform = $flags[$part];
					
				}
				
			}
			
		} elseif ($find (['IEMobile', 'Edge', 'Midori', 'Vivaldi', 'SamsungBrowser', 'Valve Steam Tenfoot', 'Chrome'], $key, $browser)) {
			
			$version = $result['version'][$key];
			
		} elseif ($rv_result and $find ('Trident', $key)) {
			
			$browser = 'MSIE';
			$version = $rv_result;
			
		} elseif ($find ('UCBrowser', $key)) {
			
			$browser = 'UC Browser';
			$version = $result['version'][$key];
			
		} elseif ($find ('CriOS', $key)) {
			
			$browser = 'Chrome';
			$version = $result['version'][$key];
			
		} elseif ($browser == 'AppleWebKit') {
			
			if ($platform == 'Android' and ! ($key = 0)) {
				
				$browser = 'Android Browser';
				
			} elseif (strpos ($platform, 'BB') === 0) {
				
				$browser  = 'BlackBerry Browser';
				$platform = 'BlackBerry';
				
			} elseif ($platform == 'BlackBerry' or $platform == 'PlayBook')
			$browser = 'BlackBerry Browser';
			else
			$find ('Safari', $key, $browser) or $find ('TizenBrowser', $key, $browser);
			
			$find ('Version', $key);
			$version = $result['version'][$key];
			
		} elseif ($pKey = preg_grep ('/playstation \d/i', array_map ('strtolower', $result['browser']))) {
			
			$pKey = reset ($pKey);
			$platform = 'PlayStation ' . preg_replace ('/[^\d]/i', '', $pKey);
			$browser  = 'NetFront';
			
		}
		
		return ['platform' => $platform ?: null, 'browser' => $browser ?: null, 'version' => $version ?: null];
		
	}
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Куки
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	function set_cookie ($name, $value, $expires = 0) {
		
		if ($_SERVER and !headers_sent () and !defined ('CLI')) {
			
			if ((int) $expires)
				$expires = time () + ($expires * 86400);
			else
				$expires = 0x7FFFFFFF;
			
			$domain = explode ('.', get_domain ($_SERVER['HTTP_HOST']));
			$domain_count = count ($domain);
			$domain_allow_count = -2;
			
			if ($domain_count > 2) {
				
				if (in_array ($domain[$domain_count - 2], ['com', 'net', 'org']) or $domain[$domain_count - 1] == 'ua')
				$domain_allow_count = -3;
				
				$domain = array_slice ($domain, $domain_allow_count);
				
			}
			
			$domain = '.'.implode ('.', $domain);
			
			if (PHP_VERSION < 5.2)
				return @setcookie ($name, $value, $expires, '/', $domain.'; HttpOnly');
			else
				return @setcookie ($name, $value, $expires, '/', $domain, null, true);
			
		}// else throw new MashException ('set_cookie (): Error! Headers already send.');
		
	}
	
	class upl_cookies implements ArrayAccess {
		private $_storage;
		
		public function __construct ($cookies) {
			$this->_storage = $cookies;
		}
		
		public function offsetExists ($offset) {
			return is_isset ($offset, $this->_storage);
		}
		
		public function offsetUnset ($offset) {
			unset ($this->_storage[$offset]);
		}
		
		public function offsetGet ($offset) {
			return ($this->offsetExists ($offset) ? $this->_storage[$offset] : '');
		}
		
		public function offsetSet ($offset, $value) {
			
			if (set_cookie ($offset, $value))
			$this->_storage[$offset] = $value;
			
		}
		
	}
	
	$_COOKIE = new upl_cookies ($_COOKIE);
	
	
	function get_screen_width () {
		return $_COOKIE['screen_width'];
	}
	
	function cookies_file2wget ($file, $domain = '') {
		
		$output = '';
		
		if ($content = file2array ($file))
		foreach ($content as $content)
		if ($content[0] != '#') {
			
			$content = preg_split ('~\s+~', $content);
			
			if ($content[5] and (($domain and ($content[0] == $domain or $content[0] == 'www.'.$domain or $content[0] == '.'.get_domain ($domain))) or !$domain))
			$output .= $content[5].'='.$content[6].'; ';
			
		}
		
		return trim ($output, '; ');
		
	}
	
	$devices = [
		
		'android' => 'android',
		'blackberry' => 'blackberry',
		'iphone' => '(iphone|ipod)',
		'ipad' => 'ipad',
		'opera' => 'opera mini',
		'palm' => '(avantgo|blazer|elaine|hiptop|palm|plucker|xiino)',
		'windows' => 'windows ce; (iemobile|ppc|smartphone)',
		'generic' => '(kindle|mobile|mmp|midp|o2|pda|pocket|psp|symbian|smartphone|treo|up.browser|up.link|vodafone|wap)',
		
	];
	
	function is_mobile () {
		
		if ($_SERVER['HTTP_X_WAP_PROFILE'] or $_SERVER['HTTP_PROFILE'])
		return true;
		elseif (strpos ($_SERVER['HTTP_ACCEPT'], 'text/vnd.wap.wml') !== false or strpos ($_SERVER['HTTP_ACCEPT'], 'application/vnd.wap.xhtml+xml') !== false)
		return true;
		elseif (get_device () != 'pc')
		return true;
		
	}
	
	function get_device () {
		global $devices;
		
		foreach ($devices as $device => $regexp)
		if (preg_match ('~'.$regexp.'~i', $_SERVER['HTTP_USER_AGENT']))
			return $device;
		else
			return null;
	 
	}
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Работа со ссылками
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	function home_url ($url, $sсheme = 1) {
		
		$url = get_domain ($url, 0, 0);
		if ($url[0] == 'www' and count ($url) > 3) unset ($url[0]);
		$url = implode ('.', $url);
		$php_self_2 = '';
		
		//$php_self= explode ('/', $_SERVER['PHP_SELF']);
		//if (count ($php_self) > 2) $php_self_2 = '/'.$php_self[1];
		
		if ($sсheme) $sсheme = get_http_scheme ().':'; else $sсheme = '';
		
		return $sсheme.'//'.$url.$php_self_2;
		
	}
	
	function get_http_scheme () {
		
		if (!is_isset ('REQUEST_SCHEME', $_SERVER)) {
			
			if (
				(isset ($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on')
				or
				(isset ($_SERVER['SERVER_PORT']) and $_SERVER['SERVER_PORT'] == 443)
			) return 'https';
			else
				return 'http';
			
		} else return $_SERVER['REQUEST_SCHEME'];
		
	}
	
	function url_end ($url) {
		
		$url = explode ('/', $url);
		return end ($url);
		
	}
	
	function get_domain ($url, $trim_www = 1, $implode = 1) {
		
		$url = url_no_sheme ($url);
		$url = explode ('/', $url);
		$url = reset ($url);
		$url = explode ('.', $url);
		if ($trim_www and $url[0] == 'www') unset ($url[0]);
		if ($implode) $url = implode ('.', $url);
		
		return $url;
		
	}
	
	function url_no_sheme ($url) {
		
		$url = explode ('://', $url);
		$url = end ($url);
		
		return $url;
		
	}
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Работа с IP
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	function get_ip () {
		
		if (is_isset ('HTTP_CLIENT_IP', $_SERVER))
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		elseif (is_isset ('HTTP_X_REAL_IP', $_SERVER))
			$ip = $_SERVER['HTTP_X_REAL_IP'];
		elseif (is_isset ('HTTP_X_FORWARDED_FOR', $_SERVER))
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		elseif (is_isset ('REMOTE_ADDR', $_SERVER))
			$ip = $_SERVER['REMOTE_ADDR'];
		else
			$ip = null;
		
		/*if($_SERVER) {
		if($_SERVER['HTTP_X_FORWARDED_FOR'])
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		elseif($_SERVER['HTTP_CLIENT_IP'])
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		else
			$ip = $_SERVER['REMOTE_ADDR'];
	}
	else {
		if(getenv('HTTP_X_FORWARDED_FOR'))
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		elseif(getenv('HTTP_CLIENT_IP'))
			$ip = getenv('HTTP_CLIENT_IP');
		else
			$ip = getenv('REMOTE_ADDR');
	}*/
		
		return $ip;
		
	}
	
	define ('LISAS_IP', get_ip ());
	
	function is_proxy ($ip) {
		
		$headers = [
			
			'HTTP_VIA',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_FORWARDED',
			'HTTP_CLIENT_IP',
			'HTTP_FORWARDED_FOR_IP',
			'VIA',
			'X_FORWARDED_FOR',
			'FORWARDED_FOR',
			'X_FORWARDED',
			'FORWARDED',
			'CLIENT_IP',
			'FORWARDED_FOR_IP',
			'HTTP_PROXY_CONNECTION',
			
		];
		
		$libProxy = 'No';
		$flagProxy = false;
		
		foreach ($headers as $i)
		if ($_SERVER[$i]) $flagProxy = true;
		
		if (in_array ($_SERVER['REMOTE_PORT'], [8080, 80, 6588, 8000, 3128, 553, 554]) || @fsockopen ($_SERVER['REMOTE_ADDR'], 80, $errno, $errstr, 30))
		$flagProxy = true;
		
		if ($flagProxy == true && is_isset ('REMOTE_ADDR', $_SERVER) && !empty ($_SERVER['REMOTE_ADDR'])) { // Proxy LookUp
			
			// Transparent Proxy
			// REMOTE_ADDR = proxy IP
			// HTTP_X_FORWARDED_FOR = your IP
			if (is_isset ('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty ($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] == $myIP)
			$libProxy = 'Transparent Proxy';
			
			// Simple Anonymous Proxy
			// REMOTE_ADDR = proxy IP
			// HTTP_X_FORWARDED_FOR = proxy IP
			elseif (is_isset ('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty ($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] == $_SERVER['REMOTE_ADDR'])
			$libProxy = 'Simple Anonymous (Transparent) Proxy';
			
			// Distorting Anonymous Proxy
			// REMOTE_ADDR = proxy IP
			// HTTP_X_FORWARDED_FOR = random IP address
			elseif (is_isset ('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty ($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != $_SERVER['REMOTE_ADDR'])
			$libProxy = 'Distorting Anonymous (Transparent) Proxy';
			
			// Anonymous Proxy
			// HTTP_X_FORWARDED_FOR = not determined
			// HTTP_CLIENT_IP = not determined
			// HTTP_VIA = determined
			elseif ($_SERVER['HTTP_X_FORWARDED_FOR'] == '' && $_SERVER['HTTP_CLIENT_IP'] == '' && !empty ($_SERVER['HTTP_VIA']))
			$libProxy = 'Anonymous Proxy';
			
			// High Anonymous Proxy
			// REMOTE_ADDR = proxy IP
			// HTTP_X_FORWARDED_FOR = not determined
			else
			$libProxy = 'High Anonymous Proxy';
			
		}
		
		return $libProxy;
		
	}
	
	function ip_subnet () {
		
		$a = explode ('.', LISAS_IP);
		$ip = $a[0].'.'.$a[1].'.*.*';
		
		return $ip;
		
	}
	
	function is_ip_subnet ($this_ip) {
		
		$ip = explode ('.', LISAS_IP);
		$this_ip = explode ('.', $this_ip);
		
		if ($ip[0].'.'.$ip[1] == $this_ip[0].'.'.$this_ip[1]) $result = 1;
		else $result = 0;
		
		return $result;
		
	}
	
	function check_ip ($ips) {
		
		$blockip = false;
		
		if (is_array ($ips)) {
			
			foreach ($ips as $ip_line) {
				
				$ip_arr = rtrim ($ip_line['ip']);
				
				$ip_check_matches = 0;
				$db_ip_split = explode ('.', $ip_arr);
				$this_ip_split = explode ('.', LISAS_IP);
				
				for ($i_i = 0; $i_i < 4; ++$i_i)
				if ($this_ip_split[$i_i] == $db_ip_split[$i_i] or $db_ip_split[$i_i] == '*') $ip_check_matches += 1;
				
				if ($ip_check_matches == 4) {
					$blockip = $ip_line['ip'];
					break;
				}
			
			}
			
		}
		
		return $blockip;
		
	}
	
	function allowed_ip ($ip_array) {
		
		$result = 0;
		
		$ip_array = trim ($ip_array);
		
		if (!$ip_array) die ('allowed_ip (): ip not found!');
		
		$ip_array = explode ($sep, $ip_array);
		
		$db_ip_split = explode ('.', LISAS_IP);
		
		foreach ($ip_array as $ip) {
			
			$ip_check_matches = 0;
			
			$this_ip_split = explode ('.', trim ($ip));
			
			for ($i_i = 0; $i_i < 4; ++$i_i) {
				
				if ($this_ip_split[$i_i] == $db_ip_split[$i_i] or $this_ip_split[$i_i] == '*') $ip_check_matches += 1;
				
			}
			
			if ($ip_check_matches == 4) $result = 1;
			
		}
		
		return $result;
		
	}
	
	function allow_ip ($ip) {
		
		if (!empty ($ip) and $ip == long2ip (ip2long ($ip))) {
			
			$reserved_ips = [
				
				['0.0.0.0', '2.255.255.255'],
				['10.0.0.0', '10.255.255.255'],
				['127.0.0.0', '127.255.255.255'],
				['169.254.0.0', '169.254.255.255'],
				['172.16.0.0', '172.31.255.255'],
				['192.0.2.0', '192.0.2.255'],
				['192.168.0.0', '192.168.255.255'],
				['255.255.255.0', '255.255.255.255']
				
			];
			
			foreach ($reserved_ips as $r) {
				
				$min = ip2long ($r[0]);
				$max = ip2long ($r[1]);
				
				if ((ip2long ($ip) >= $min) and (ip2long ($ip) <= $max)) return false;
				
			}
			
			return true;
			
		} else return false;
		
	}
	
	function resolve_ip ($host) {
		
		$ip = ip2long ($host);
		
		if ($ip === false or $ip == -1) $ip = ip2long (gethostbyname ($host));
		return $ip;
		
	}
	
	function allow_port ($port) {
		if (!not_empty ($port) or $port == 4662 or $port == 6699 or $port == 1214 or ($port >= 411 and $port <= 413) or ($port >= 6881 and $port <= 6889) or ($port >= 6346 and $port <= 6347)) return false; else return true;
	}
	
	function prepare_mail ($txt, $data = []) {
		return str_replace (['{home_url}', '{error}', '{version}', '<br/>', '<b>', '</b>'], ['http://'.$_SERVER['HTTP_HOST'], $data['error'], $data['version'], NL, '', ''], $txt);
	}
	
	function show_mess ($error, $color = 'red') {
		
		$error = make_array ($error);
		$error_array = [];
		
		foreach ($error as $error) $error_array[] = '<span style="color:'.$color.';">'.$error.'</span>';
		
		return $error_array;
		
	}
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// GZIP
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	function gzip_encoding () {
		
		$encoding = false;
		
		if (!headers_sent () and !connection_aborted () and function_exists ('ob_gzhandler') and !ini_get ('zlib.output_compression')) {
			
			if (strpos ($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false) $encoding = 'x-gzip';
			if (strpos ($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) $encoding = 'gzip';
			
		}
		
		return $encoding;
		
	}
	
	function gzip ($allow_gzip = 1, $encoding = '', $info = 1, $document_date = '', $level = 9, $mode = FORCE_GZIP) {
		
		if (not_empty ($document_date)) @header ('Last-Modified:'.date ('r', $document_date).' GMT');
		
		if ($allow_gzip) {
			
			if (!$encoding) $encoding = gzip_encoding ();
			
			if ($encoding) {
				
				$gzip_in = ob_get_contents ();
				$gzip_out = gzencode ($gzip_in, $level, $mode);
				
				ob_end_clean ();
				
				$gzip_in_lisas_strlen = lisas_strlen ($gzip_in);
				$gzip_out_lisas_strlen = lisas_strlen ($gzip_out);
				$gzip_out_percent = 100 - round (($gzip_out_lisas_strlen / $gzip_in_lisas_strlen) * 100);
				
				if ($info) $gzip_in .= '
<!-- При выводе использовалось сжатие '.$encoding.' -->
<!-- Размер до сжатия: '.$gzip_in_lisas_strlen.' байт -->
<!-- Размер после сжатия: '.$gzip_out_lisas_strlen.' байт -->
<!-- Процент сжатия: '.$gzip_out_percent.'% -->';
				
				@header ('Content-Encoding:'.$encoding);
				
				echo gzencode ($gzip_in, $level, $mode);
				
			}
			
		} else ob_end_flush ();
		
	}
	
	function get_compress_type () {
		
		$encoding = false;
		if (strstr ($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) $encoding = 'gzip';
		if (strstr ($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate')) $encoding = 'deflate';
		
		if (!strstr ($_SERVER['HTTP_USER_AGENT'], 'Opera') and preg_match ('/^Mozilla\/4\.0 \(compatible; MSIE ([0-9]\.[0-9])/i', $_SERVER['HTTP_USER_AGENT'], $matches)) {
			
			$version = floatval ($matches[1]);
			
			if ($version < 6 or ($version == 6 and !strstr ($_SERVER['HTTP_USER_AGENT'], 'EV1'))) $encoding = false;
			
		}
		
		return $encoding;
		
	}
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Работа со ссылками
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	function lisas_parse_url ($url) { // Парсим урлы. Стандартная parse_url () ведет себя некорректно в некоторых ситуациях. Эта функция лишена таких недостатков.
		
		$u_colon = explode (':', $url);
		$p_slash = explode ('/', str_replace ($u_colon[0].'://', '', $url));
		$p_dog = explode ('@', $p_slash[0]);
		$p_colon = explode (':', $p_slash[0]);
		$p_answ = explode ('?', $p_slash[end_key ($p_slash)]);
		$p_dash = explode ('#', $p_answ[1]);
		
		if ($p_dog[1]) {
			
			$d_colon = explode (':', $p_dog[0]);
			$d_colon2 = explode (':', $p_dog[1]);
			
			$host = $d_colon2[0];
			$port = $d_colon2[1];
			
		} else {
			
			$host = $p_colon[0];
			$port = $p_colon[1];
			
		}
		
		unset ($p_slash[0]);
		
		$full_query = implode ('/', $p_slash);
		$fp_dash = explode ('#', $full_query);
		
		if ($port) $string = $u_colon[2]; else $string = $u_colon[1];
		$path_array = explode ('/', str_replace ('//', '', $string));
		
		unset ($path_array[0]);
		unset ($path_array[end_key ($path_array)]);
		
		if (not_empty ($host)) {
			
			$h_dot = explode ('.', $host);
			if ($h_dot[0] == 'www') unset ($h_dot[0]);
			$host_nowww = implode ('.', $h_dot);
			
		}
		
		$query = [];
		$parts = explode ('&', $p_dash[0]);
		
		foreach ($parts as $qparts) {
			
			$qpart = explode ('=', $qparts);
			$query[$qpart[0]] = $qpart[1];
			
		}
		
		return ['full' => $url, 'protocol' => $u_colon[0], 'start' => $u_colon[0].'://', 'host' => $host, 'host_nowww' => $host_nowww, 'port' => $port, 'path_array' => $path_array, 'full_query' => $fp_dash[0], 'query' => $p_dash[0], 'query_array' => $query, 'anchor' => $p_dash[1], 'user' => $d_colon[0], 'pass' => $d_colon[1]];
		
	}
	
	function is_url_html ($url) {
		
		preg_match ('~<a\s+href\s*=\s*(.*?)>(.*?)<\s*/a\s*>~si', $url, $match);
		return $match;
		
	}
	
	function is_url_file ($url) {
		
		if (is_string ($url))
		return preg_match ('~^http[s]{0,1}://.+\.(.?){2,5}/.+\.(.?){1,5}$~i', $url);
		else
		return false;
		
	}
	
	function get_domain_dir ($string) {
		
		if (is_array ($string)) return '';
		
		$string = str_replace ('.php', '', $string);
		$string = trim (strip_tags ($string));
		$string = str_replace ('\\', '/', $string);
		$string = preg_replace ('/[^a-z0-9\/\_\-]+/mi', '', $string);
		
		return $string;
		
	}
	
	function url_encode ($str, $sep = '-', $debug = 0) {
		
		if (not_empty ($str) and substr ($str, 0, 1) != '%') {
			
			$str = stripslashes ($str);
			
			$replaces = [];
			
			$str = rawurlencode ($str);
			if (!is_numeric ($sep)) $replaces['%20'] = $sep;
			
			//if ($full) $replaces['_'] = '%5F';
			
			foreach ($replaces as $find => $replace)
				$str = str_replace ($find, $replace, $str);
			
			//debug ($str);
			
			//$str = htmlentities ($str);
			
		}
		
		return $str;
		
	}
	
	function url_decode ($str, $sep = '-', $debug = 0) {
		
		if ($debug) debug ($str);
		
		$replaces = [];
		
		if ($sep) $replaces[$sep] = ' ';
		//$replaces['%5F'] = '_';
		
		foreach ($replaces as $find => $replace)
		$str = str_replace ($find, $replace, $str);
		
		$str = spech_decode (rawurldecode ($str));
		
		return $str;
		
	}
	
	function base64_url_encode ($string) {
		return base64_encode (url_encode ($string));
	}
	
	function base64_url_decode ($string) {
		return url_decode (base64_decode ($string));
	}
	
	function encode_username ($user, $member_id_name = '') {
		
		if (!$user and $member_id_name) $user = $member_id_name;
		if (not_empty ($user)) $user = url_encode (htmlspecialchars (strip_tags (stripslashes (trim ($user))), ENT_QUOTES)); else $user = '';
		
		return $user;
		
	}
	
	function decode_username ($user, $member_id_name = '') {
		
		if (!$user and $member_id_name) $user = $member_id_name;
		if (not_empty ($user)) $user = url_decode ($user); else $user = '';
		
		return $user;
		
	}
	
	function http_build_fquery ($str, $encode = 1, $sep = [], $empty = true, $debug = 0) {
		
		$output = '';
		
		if (!isset ($sep[0])) $sep[0] = '&';
		if (!isset ($sep[1])) $sep[1] = '=';
		
		$i = 0;
		
		foreach ($str as $key => $value) {
		
			if ($value or $empty) {
				
				if ($i > 0) $output .= $sep[0];
				
				if ($encode == 1) $value = url_encode ($value);
				elseif ($encode == 2) $value = rawurlencode ($value);
				
				$output .= $key.$sep[1].$value;
				
			}
			
			++$i;
			
		}
		
		if ($debug) debug ($output);
		return $output;
		
	}
	
	function http_build_alt_url ($str, $debug = 0) {
		
		$output = '';
		
		foreach ($str as $key => $value)
		$output .= '/'.$key.'-'.rawurlencode ($value);
		
		if ($debug) debug ($output);
		return trim ($output, '/');
		
	}
	
	function http_build_rawquery ($str, $br = '', $debug = 0) {
		
		$output = '';
		
		foreach ($str as $key => $value)
		$output .= '&'.$key.'='.rawurlencode ($value).$br;
		
		if ($debug) debug ($output);
		return trim ($output, '&');
		
	}
	
	function http_unbuild_query ($str, $debug = 0) {
		
		$str2 = explode ('?', $str);
		$str2 = explode ('&', end ($str2));
		
		$output = [];
		
		foreach ($str2 as $str) {
			
			$str = explode ('=', $str);
			$output[$str[0]] = rawurldecode ($str[1]);
			
		}
		
		if ($debug) print_r ($output);
		return $output;
		
	}
	
	function http_unbuild_query2 ($str, $debug = 0) {
		
		$str2 = explode ('&', $str);
		
		$output = [];
		
		foreach ($str2 as $str) {
			
			$str = explode ('=', $str);
			$output[$str[0]] = rawurldecode ($str[1]);
			
		}
		
		return $output;
		
	}
	
	function is_on () {}
	
	function absolute_path ($path) {
		
		$path = str_replace (array ('/', '\\'), DS, $path);
		$parts = array_filter (explode (DS, $path), 'strlen');
		
		$absolutes = [];
		
		foreach ($parts as $part) {
		
			if ('.' == $part) continue;
			
			if ('..' == $part)
			array_pop ($absolutes);
			else
			$absolutes[] = $part;
			
		}
		
		return implode (DS, $absolutes);
		
	}
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Работа с E-Mail
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	function email_encode ($email, $encode = 1) {
		
		$email = str_replace (['@', '.'], ['//', '/'], $email);
		if ($encode) $email = base64_encode (strrev ($email));
		
		return $email;
		
	}
	
	function email_decode ($email, $encode = 1) {
		
		if ($encode) $email = strrev (base64_decode (url_decode ($email)));
		$email = str_replace (['//', '/'], ['@', '.'], $email);
		
		return $email;
		
	}
	
	function parse_email ($email) {
		
		$email = explode ('@', $email);
		
		$domain = $email[1];
		
		$email[1] = explode ('.', $email[1]);
		
		$zone = end ($email[1]);
		unset ($email[1][end_key ($email[1])]);
		
		return [
			
			'user' => $email[0],
			'host' => implode ('.', $email[1]),
			'domain' => $domain,
			'zone' => $zone,
			
		];
		
	}
	
	function hide_email ($email) {
		
		$email = parse_email ($email);
		
		$length = lisas_strlen ($email['user']);
		$center = ceil ($length / 2);
		
		$name = '';
		
		for ($i = 0; $i < $length; ++$i) {
			
			if ($i == 0 or $i == 4 or $i == $center or $i == ($length - 1))
			$name .= $email['user'][$i];
			else
			$name .= '*';
			
		}
		
		$length = lisas_strlen ($email['host']);
		$center = ceil ($length / 2);
		
		$host = '';
		
		for ($i = 0; $i < $length; ++$i) {
			
			if ($i == 0 or $i == 4 or $i == $center or $i == ($length - 1))
			$host .= $email['host'][$i];
			else
			$host .= '*';
			
		}
		
		return $name.'@'.$host.'.'.$email['zone'];
		
	}
	
	//print_r (lisas_parse_url ('http://www.domain.ru:80/query1/query2/file.php?key1=value2&key2=value#anchor'));
	
	define ('EMAIL_PATTERN', '[\.a-z0-9_\-]+[@][a-z0-9_\-]+([.][a-z0-9_\-]+)+[a-z]{1,4}');
	
	function is_email ($mail) {
		
		if (preg_match_all ('~'.EMAIL_PATTERN.'~iu', $mail, $match))
			return $match[0];
		else
			return false;
		
	}
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	function is_url ($url, $all = 0, $protos = 'http|https|ftp') {
		
		$pattern = '~^('.$protos.')://(-\.)?([^\s/?\.#-]+\.?)+(/[^\s]*)?$~ius';
		
		if ($all) {
			
			preg_match_all ($pattern, $url, $match);
			return $match[0];
			
		} else return preg_match ($pattern, $url);
		
	}
	
	function get_canonical_path ($path) {
		
		$domain = 0;
		$path = explode ('://', $path);
		if (!$path[1]) $path[1] = $path[0]; else $domain = 1;
		
		$path = explode_filepath ($path[1], '/');
		
		if ($domain) unset ($path[0]);
		
		return implode_filepath ($path, '/');
		
	}
	
	function emu_getallheaders () {
		
		foreach ($_SERVER as $name => $value) {
			
			$data = str_replace ('_', ' ', substr ($name, 5));
			$data = ucwords (lisas_strtolower ($data));
			$data = str_replace (' ', '-', $data);
			
			if (substr ($name, 0, 5) == 'HTTP_') $headers[$data] = $value;
			
		}
		
		return $headers;
		
	}
	
	function auth ($area) {
		
		header ('WWW-Authenticate: Basic realm="'.$area.'"');
		header ('HTTP/1.0 401 Unauthorized');
		
		echo '<h1>Access Denied</h1>';
		
		exit ();
		
	}
	
	function fileheader ($file_name) {
		@header ('Content-Disposition: attachment; filename="'.$file_name.'";');
	}
	
	function open_socket ($host, $url, $options) {
		
		$output = [];
		$error = [];
		
		if (!$options['port']) $options['port'] = 80;
		
		$handle = @fsockopen ($host, $options['port'], $error_number, $error_string);
		
		if ($handle) {
			
			//$url = substr ($url, 0, -1);
			$url = '/'.str_replace (' ', '%20', $url);
			
			$out = "GET ".$url." HTTP/1.0\r\nHost: ".$host."\r\n\r\n";
			
			if ($options['show_info']) echo $out;
			
			stream_set_timeout ($handle, 2);
			
			fwrite ($handle, $out);
			
			$response = [];
			$line_num = 0;
			
			while (!feof ($handle)) {
				
				$response[$line_num] = fgets ($handle, 4096);
				//if ($response[$line_num] === false) break; else
				++$line_num;
				
			}
			
			$record = 0;
			$str = '';
			
			foreach ($response as $line) {
				
				if ($record == 1) $str .= $line;
				elseif (substr ($line, 0, 1) == '<') $record = 1;
				elseif (preg_match ('~^HTTP\/1.[0-9]{1}\s([4-9]{1}[0-9]{2}.*)~', $line, $matches)) $error[1][] = 'Incorrect response type.';
				
			}
			
		} elseif ($error_number) $error[$error_number] = $error_string;
		elseif (!$handle) $error[1][] = 'Stream not found';
		
		if ($error) $output[0] = $error; else $output[1] = $str;
		
		return $output;
		
	}
	
	function tinysong_song_id ($ts_key, $artist, $album, $title, $delay = 10) {
		
		$content = file_get_content ('http://tinysong.com/s/'.url_encode ($title).'?format=json&limit=32&key='.$ts_key);
		
		if (!$content) {
			
			sleep ($delay);
			tinysong_song_id ($ts_key, $artist, $album, $title, $delay);
			
		}
		
		$content = json_decode ($content, true);
		
		$data = [];
		foreach ($content as $key => $value)
		$data[str_to_key ($value['ArtistName'])][str_to_key ($value['AlbumName'])][str_to_key ($value['SongName'])] = $value;
		
		//print_r ($data);
		
		return $data[str_to_key ($artist)][str_to_key ($album)][str_to_key ($title)]['SongID'];
		
	}
	
	function url_exists ($url, $code = 200) {
		
		$url = get_headers ($url);
		if ($url[0] == 'HTTP/1.0 '.$code.' OK') return true; else return false;
		
	}
	
	function get_place_location ($place) {
		
		$content = url_get_array ('https://maps.googleapis.com/maps/api/geocode/json', ['address' => url_encode ($place, 0)]);
		
		$output = [];
		
		if ($content['results'])
		foreach ($content['results'] as $result)
		if ($data = $result['geometry']['location'])
		$output[] = [
			
			'lat' => str_replace (',', '.', $data['lat']),
			'long' => str_replace (',', '.', $data['lng']),
			
		];
		
		return $output;
		
	}
	
	function upload_error_code ($error_code) {
		
		switch ($error_code) {
			
			case UPLOAD_ERR_OK: // 0
				$error_code = false;
			break;
			
			case UPLOAD_ERR_INI_SIZE: // 1
				$error_code = 'The uploaded file2array exceeds the upload_max_filesize directive in php.ini';
			break;
			
			case UPLOAD_ERR_FORM_SIZE: // 2
				$error_code = 'The uploaded file2array exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
			break;
			
			case UPLOAD_ERR_PARTIAL: // 3
				$error_code = 'The uploaded file2array was only partially uploaded';
			break;
			
			case UPLOAD_ERR_NO_FILE: // 4
				$error_code = 'No file2array was uploaded';
			break;
			
			case UPLOAD_ERR_NO_TMP_DIR: // 6
				$error_code = 'Can\'t find a PHP temporary folder';
			break;
			
			case UPLOAD_ERR_CANT_WRITE: // 7
				$error_code = 'Failed to write file2array to disk';
			break;
			
			case UPLOAD_ERR_EXTENSION: // 8
				$error_code = 'File upload stopped by extension';
			break;
			
		}
		
		return $error_code;
		
	}
	
	function spech_rss_encode ($txt) {
		return str_replace (['<![CDATA[', ']]>', '[', ']'], ['', '', '&#91;', '&#93;'], $txt);
	}
	
	function spech_rss_decode ($txt) {
		return str_replace (['<![CDATA[', ']]>', '&#91;', '&#93;'], ['', '', '[', ']'], $txt);
	}
	
	function css_minify ($content) {
		
		$data = [
			
			'/\*[^*]*\*+([^/][^*]*\*+)*/' => '',
			'[\r\n\t]' => '',
			'\s{2,}' => ' ',
			
		];
		
		$symbols = [':', ';', '{', '}', ',', '+', '>'];
		foreach ($symbols as $key) $data['\s*\\'.$key.'\s*'] = $key;
		
		foreach ($data as $find => $replace)
			$content = preg_replace ('~'.$find.'~', $replace, $content);
		
		//$content = str_replace (';}', '}', $content);
		
		return $content;
		
	}
	
	function js_minify ($content) {
		
		$data = [
			
			'/\*[^*]*\*+([^/][^*]*\*+)*/' => '',
			'[^\\\:"\']//.*?\n' => '',
			//'//\s+.*?\n' => '',
			'\s*\|\|\s*' => '||',
			'[\r]' => '',
			'[\n\t]' => ' ',
			'\s{2,}' => ' ',
			'\s+\+\s+' => '+',
			'\s*\-\s*' => '-',
			'\s*\+=\s*' => '+=',
			'\s*\-=\s*' => '-=',
			'\s*\!=\s*' => '!=',
			'\s+<\s+' => '<',
			'\s+>\s+' => '>',
			
		];
		
		foreach ($data as $find => $replace)
		$content = preg_replace ('~'.$find.'~', $replace, $content);
		
		/*$content = preg_replace_callback ('~/(.+?)/~', function ($item) {
			return str_replace ([', '], [',\s'], $item[1]);
		}, $content);*/
		
		$symbols = ['(', ')', '{', '}', '[', ']', ';', ':', '=', '&', '&&', '*'];
		
		foreach ($symbols as $key)
		$content = preg_replace ('~\s*\\'.$key.'\s*~', $key, $content);
		
		/*$content = preg_replace_callback ('~([=:])([\'])(.+?)([\'])~', function ($item) {
			
			$item[3] = str_replace (['(', '{', '- '], [' (', ' {', ' - '], $item[3]);
			return $item[1].$item[2].$item[3].$item[4];
			
		}, $content);*/
		
		/*$content = preg_replace_callback ('~([\{])(.+?)([\}]);~', function ($item) {
			
			$item[2] = str_replace ([', \''], [',\''], $item[2]);
			return $item[1].$item[2].$item[3].';';
			
		}, $content);*/
		
		return trim ($content);
		
	}
	
	function html_minify ($content) {
		
		$data = [
			
			'\s{2,}' => '',
			'[\r\n\t]' => '',
			
		];
		
		foreach ($data as $find => $replace)
		$content = preg_replace ('~'.$find.'~', $replace, $content);
		
		$content = str_replace ('<html', NL.'<html', $content);
		
		return trim ($content);
		
	}
	
	function reload_page ($url = '') {
		
		if (!$url) $url = $_SERVER['HTTP_REFERER'];
		$url = spech_decode ($url);
		
		@header ('Location: '.$url);
		
	}
	
	function file_header ($file_name) { // Выдает $file_name на скачивание
		@header ('Content-Disposition: attachment; filename="'.$file_name.'";');
	}
	
	function is_unleech () { // Защита от прямого вызова файла без реферера
		return (!is_isset ('HTTP_REFERER', $_SERVER) or get_domain ($_SERVER['HTTP_REFERER']) != get_domain ($_SERVER['HTTP_HOST']));
	}
	
	function build_headers ($headers) {
		
		$output = '';
		
		foreach ($headers as $key => $value)
		$output .= $key.': '.$value.NL;
		
		return $output;
		
	}
	
	function cookies_decode ($headers, $key = 'Set-Cookie') {
		
		$output = [];
		
		foreach ($headers as $i => $header) {
			
			foreach (explode ("\n", $header[$key]) as $line) {
				
				$headers = explode (';', $line);
				
				foreach ($headers as $header) {
					
					$header = explode ('=', $header);
					$output[$i][trim ($header[0])] = trim ($header[1]);
					
				}
				
			}
			
		}
		
		return $output;
		
	}
	
	function http_get_message ($code) {
		
		$messages = [
			
				0 => 'None',
			100 => 'Continue',
			101 => 'Switching Protocols',
			102 => 'Processing',
			105 => 'Name Not Resolved',
			
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			207 => 'Multi-Status',
			226 => 'IM Used',
			
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Moved Temporarily',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			307 => 'Temporary Redirect',
			
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Large',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			422 => 'Unprocessable Entity',
			423 => 'Locked',
			424 => 'Failed Dependency',
			425 => 'Unordered Collection',
			426 => 'Upgrade Required',
			428 => 'Precondition Required',
			429 => 'Too Many Requests',
			431 => 'Request Header Fields Too Large',
			434 => 'Requested host unavailable',
			449 => 'Retry With',
			451 => 'Unavailable For Legal Reasons',
			456 => 'Unrecoverable Error',
			
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			506 => 'Variant Also Negotiates',
			507 => 'Insufficient Storage',
			508 => 'Loop Detected',
			509 => 'Bandwidth Limit Exceeded',
			510 => 'Not Extended',
			511 => 'Network Authentication Required',
			
		];
		
		return (is_isset ($code, $messages) ? $messages[$code] : 'Wrong code');
		
	}
	
	function build_url ($start, $finish) {
		
		if ($finish[0] == '/') $finish = $start.$finish;
		return $finish;
		
	}
	
	function uuid () {
		
		$randomString = openssl_random_pseudo_bytes (16);
		$time_low = bin2hex (substr ($randomString, 0, 4));
		$time_mid = bin2hex (substr ($randomString, 4, 2));
		
		$time_hi_and_version = bin2hex (substr ($randomString, 6, 2));
		$clock_seq_hi_and_reserved = bin2hex (substr ($randomString, 8, 2));
		
		$node = bin2hex (substr ($randomString, 10, 6));

		/**
		 * Set the four most significant bits (bits 12 through 15) of the
		 * time_hi_and_version field to the 4-bit version number from
		 * Section 4.1.3.
		 * @see http://tools.ietf.org/html/rfc4122#section-4.1.3
		*/
		$time_hi_and_version = hexdec ($time_hi_and_version);
		$time_hi_and_version = $time_hi_and_version >> 4;
		$time_hi_and_version = $time_hi_and_version | 0x4000;

		/**
		 * Set the two most significant bits (bits 6 and 7) of the
		 * clock_seq_hi_and_reserved to zero and one, respectively.
		 */
		$clock_seq_hi_and_reserved = hexdec ($clock_seq_hi_and_reserved);
		$clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved >> 2;
		$clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved | 0x8000;
		
		return sprintf ('%08s-%04s-%04x-%04x-%012s', $time_low, $time_mid, $time_hi_and_version, $clock_seq_hi_and_reserved, $node);
		
	}