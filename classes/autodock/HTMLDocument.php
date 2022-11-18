<?php
	
	class HTMLDocument {
		
		public $dom, $text;
		public static $cache = [];
		
		private $element;
		
		const version = '1.1', charset = 'utf-8';
		
		public function __construct ($str = '', $charset = '') {
			
			libxml_use_internal_errors (true);
			
			if (!$charset) {
				
				$this->dom = new DOMDocument ('1.0', self::charset);
				$this->dom->loadHTML ('<?xml encoding="'.self::charset.'">'.$str); // Dirty fix
				
				foreach ($this->dom->childNodes as $item) {
					
					if ($item->nodeType == XML_PI_NODE) {
						
						$this->dom->removeChild ($item); // Remove hack
						break;
						
					}
					
				}
				
				$this->dom->encoding = self::charset; // Insert proper
				
			} else {
				
				$this->dom = new DOMDocument ('1.0', $charset);
				$this->dom->loadHTML ($str);
				
			}
			
			$this->dom->preserveWhiteSpace = false;
			
			$this->errors = libxml_get_errors ();
			
			$this->text = $this->dom->textContent;
			
			libxml_clear_errors ();
			
		}
		
		function text () {
			return $this->text;
		}
		
		protected function newInstance (): HTMLElement {
			return new HTMLElement ($this->dom);
		}
		
		function find ($selector, $offset = -1, $cache = true) {
			return $this->newInstance ()->find ($selector, $offset, $cache);
		}
		
		public function findStart ($selector, $offset = -1) {
			return $this->newInstance ()->findStart ($selector, $offset);
		}
		
		public function findEnd ($selector, $offset = -1) {
			return $this->newInstance ()->findEnd ($selector, $offset);
		}
		
		public function findContains ($selector, $offset = -1) {
			return $this->newInstance ()->findContains ($selector, $offset);
		}
		
		public function __invoke ($selector) {
			return $this->find ($selector);
		}
		
		static function testSelectors () {
			
			$selectors = [
				
				'elem' => '//elem',
				'parent child' => '//parent//child',
				'parent > child' => '//parent/child',
				'parent + child' => '//child/following::parent', // TODO
				'elem1, elem2' => '//elem1 | //elem2',
				'.class' => '//*[contains(concat(\' \', normalize-space(@class), \' \'), \' class \')]',
				'#id' => '//*[@id=\'id\']',
				'[attr]' => '//*[@attr]',
				'[attr=value]' => '//*[@attr=\'value\']',
				'[attr*=value]' => '//*[contains(@attr, \'value\')]',
				'[attr^=value]' => '//*[starts-with(@attr, \'value\')]',
				'[attr$=value]' => '//*[ends-with(@attr, \'value\')]',
				'[attr|=value]' => '//*[starts-with(@attr, \'value-\')]', // TODO
				'[attr~=value]' => '//*[contains(concat(\' \', normalize-space(@attr), \' \'), \' value \')]',
				':first-child' => '//*[1]',
				':last-child' => '//*[last()]',
				':nth-child(1)' => '//*[position() = 1]',
				':nth-child(3n+0)' => '//*[(position() - 0) mod 3 = 0 and position() >= 0]',
				':nth-child(even)' => '//*[position() mod 2 = 0 and position() >= 0]',
				':nth-child(odd)' => '//*[(position() - 1) mod 2 = 0 and position() >= 1]',
				
			];
			
			$results = [];
			
			foreach ($selectors as $selector => $xpath2) {
				
				$xpath = HTMLElement::selector2xpath ($selector);
				
				if ($xpath == $xpath2)
					$results[] = [1, $selector, $xpath];
				else
					$results[] = [0, $selector, $xpath, $xpath2];
				
			}
			
			return $results;
			
		}
		
		function __toString () {
			return $this->newInstance ()->html ();
		}
		
	}
	
	/*foreach (HTMLDocument::testSelectors () as $result)
		if ($result[0])
			debug ('<span style="color:green;"><b>'.$result[1].'</b>: '.$result[2].'</span>');
		else
			debug ('<span style="color:red;"><b>'.$result[1].'</b>: '.$result[2].' <i>but expected</i> '.$result[3].'</span>');
		*/
	
	class HTMLElement {
		
		public $dom, $text, $tag, $attrs = [];
		
		public $textNodes = [XML_TEXT_NODE];
		
		private $errors;
		
		function __construct ($dom) {
			$this->dom = $dom;
		}
		
		function getDom ($nodes) {
			
			if (!($nodes instanceof DOMDocument)) {
				
				$dom = new DOMDocument ('1.0', HTMLDocument::charset);
				$root = $dom->createElement ('root');
				
				$dom->appendChild ($root);
				
				foreach ($nodes->childNodes as $child) {
					
					$child = $dom->importNode ($child, true);
					$root->appendChild ($child);
					
				}
				
			} else $dom = $this->dom;
			
			return $dom;
			
		}
		
		protected static function getRegexp () {
			
			$child = '(first|last|nth)-child';
			$expr = '(\((?P<expr>[^\)]+)\))';
			
			$tag = '(?P<tag>[a-z0-9]+)?';
			$attr = '(\[(?P<attr>\S+?)(\=(?P<value>[^\]]+))?\]+)?';
			$id = '(\#(?P<id>[^\s:>#\.]+))?';
			$class = '(\.(?P<class>[^\s:>]+))?';
			$pseudo = '(:(?P<pseudo>'.$child.')'.$expr.'?)?';
			$rel = '\s*(?P<rel>[\>\,\+])?';
			
			return '/'.$tag.$attr.$id.$class.$pseudo.$rel.'/isS';
			
		}
		
		static function selector2xpath ($selector, $offset = -1, $cache = true, $rel = '', $level = 0) {
			
			$key = $selector.($rel ? $rel : '*');
			
			if (!$cache or !isset (HTMLDocument::$cache[$key])) {
				
				if (preg_match (self::getRegexp (), $selector, $match)) {
					
					$brackets = [];
					
					if (is_isset ('id', $match))
						$brackets[] = '@id=\''.$match['id'].'\'';
					
					if (is_isset ('attr', $match)) {
						
						if (is_isset ('value', $match)) {
							
							$replaces = [
								
								'*' => 'contains',
								'^' => 'starts-with',
								'$' => 'ends-with',
								
							];
							
							if (isset ($replaces[substr ($match['attr'], -1)]) and $type = $replaces[substr ($match['attr'], -1)])
								$brackets[] = $type.'(@'.substr ($match['attr'], 0, -1).', \''.$match['value'].'\')';
							elseif (substr ($match['attr'], -1) == '~')
								$brackets[] = $replaces['*'].'(concat(\' \', normalize-space(@'.substr ($match['attr'], 0, -1).'), \' \'), \' '.$match['value'].' \')';
							elseif (substr ($match['attr'], -1) == '|')
								$brackets[] = $replaces['^'].'(@'.substr ($match['attr'], 0, -1).', \''.$match['value'].'-\')';
							else
								$brackets[] = '@'.$match['attr'].'=\''.$match['value'].'\'';
							
						} else $brackets[] = '@'.$match['attr'];
						
					}
					
					if (is_isset ('class', $match)) {
						
						foreach (explode ('.', $match['class']) as $class) // .class1.class2
							$brackets[] = 'contains(concat(\' \', normalize-space(@class), \' \'), \' '.$class.' \')';
						
					}
					
					if (is_isset ('pseudo', $match)) {
						
						if ($match['pseudo'] == 'first-child')
							$brackets[] = '1';
						elseif ($match['pseudo'] == 'last-child')
							$brackets[] = 'last()';
						elseif ($match['pseudo'] == 'nth-child')
						if (is_isset ('expr', $match)) {
							
							$e = $match['expr'];
							
							if ($e == 'odd')
								$brackets[] = '(position() - 1) mod 2 = 0 and position() >= 1';
							elseif ($e == 'even')
								$brackets[] = 'position() mod 2 = 0 and position() >= 0';
							elseif (preg_match ('/^[0-9]+$/', $e))
								$brackets[] = 'position() = '.$e;
							elseif (preg_match ('/^((?P<mul>[0-9]+)n\+)(?P<pos>[0-9]+)$/is', $e, $esubs)) {
								
								if (is_isset ('mul', $esubs))
									$brackets[] = '(position() - '.$esubs['pos'].') mod '.$esubs['mul'].' = 0 and position() >= '.$esubs['pos'].'';
								else
									$brackets[] = $e;
								
							}
							
						}
						
					}
					
					$query = '';
					
					//debug ($match);
					
					if ($rel != '+' or $level > 1) {
						
						if ($rel == ',') $query .= ' | ';
						if ($rel != '+') $query .= ($rel == '>' ? '/' : '//');
						
						if (is_isset ('tag', $match))
							$query .= $match['tag'];
						else
							$query .= '*';
						
						$query .= (($c = count ($brackets)) ? ($c > 1 ? '[('.implode (') and (', $brackets).')]' : '['.implode (' and ', $brackets).']') : '');
						
					} elseif ($rel == '+') {
						
						$query .= (($c = count ($brackets)) ? ($c > 1 ? '[('.implode (') and (', $brackets).')]' : '['.implode (' and ', $brackets).']') : '');
						
						if (isset ($match['rel']) and $match['rel'] == '+')
							$query .= '/following::';
						
						$query .= (($c = count ($brackets)) ? ($c > 1 ? '[('.implode (') and (', $brackets).')]' : '['.implode (' and ', $brackets).']') : '');
						
					}
					
					++$level;
					
					if (strpos ($selector, '+') !== false) {
						
						$parts = preg_split ('/\s+\+\s+/', $selector);
						
						if (isset ($parts[1]) and !is_numeric ($parts[1])) {
							
							$query .= self::selector2xpath ($parts[1], $offset, $cache, '+', $level);
							$query .= self::selector2xpath ($parts[0], $offset, $cache, '+', $level);
							
						}
						
					} elseif ($left = trim (substr ($selector, strlen ($match[0]))))
						$query .= self::selector2xpath ($left, $offset, $cache, (isset ($match['rel']) ? $match['rel'] : ''));
					
					if ($cache) HTMLDocument::$cache[$key] = $query;
					
				}
				
			} else $query = HTMLDocument::$cache[$key];
			
			//if ($offset >= 0) $query .= '['.($offset + 1).']';
			
			return $query;
			
		}
		
		public function find (string $selector, int $offset = -1) {
			
			$xpath = new DOMXpath ($this->getDom ($this->dom));
			
			$query = self::selector2xpath ($selector, $offset, true);
			
			if ($nodes = $xpath->query ($query)) {
				
				if ($offset >= 0) { // Конкретный элемент
					
					$node = new self ($nodes->item ($offset));
					return $node->setNode ();
					
				} else { // Все элементы сразу
					
					$elements = [];
					
					foreach ($nodes as $node) {
						
						$node = new self ($node);
						$elements[] = $node->setNode ();
						
					}
					
					return $elements;
					
				}
				
			} else throw new \Exception ('Malformed XPath: '.$query);
			
		}
		
		public function findStart (string $selector, int $offset = -1) {
			
			if ($selector[0] == '.')
				return $this->find ('[class^='.substr ($selector, 1).']', $offset);
			elseif ($selector[0] == '#')
				return $this->find ('[id^='.substr ($selector, 1).']', $offset);
			
		}
		
		public function findEnd (string $selector, int $offset = -1) {
			
			if ($selector[0] == '.')
				return $this->find ('[class$='.substr ($selector, 1).']', $offset);
			elseif ($selector[0] == '#')
				return $this->find ('[id$='.substr ($selector, 1).']', $offset);
			
		}
		
		public function findContains (string $selector, int $offset = -1) {
			
			if ($selector[0] == '.')
				return $this->find ('[class*='.substr ($selector, 1).']', $offset);
			elseif ($selector[0] == '#')
				return $this->find ('[id*='.substr ($selector, 1).']', $offset);
			
		}
		
		public function getErrors () {
			return $this->errors;
		}
		
		public function toXml () {
			return $this->dom->saveXML ();
		}
		
		function setNode (): ?HTMLElement {
			
			if ($this->dom) {
				
				$this->tag = $this->dom->nodeName;
				$this->text = $this->dom->nodeValue;
				
				if ($this->dom->hasAttributes ()) {
					
					foreach ($this->dom->attributes as $attr) {
						
						$this->{$attr->nodeName} = $attr->nodeValue;
						$this->attrs[$attr->nodeName] = $attr->nodeValue;
						
					}
					
				}
				
				return $this;
				
			} else return null;
			
		}
		
		function childs ($offset = -1) {
			
			$array = [];
			
			if ($this->dom->hasChildNodes ()) {
				
				foreach ($this->dom->childNodes as $i => $child) {
					
					$node = new self ($child);
					
					if ($offset >= 0) {
						
						if ($i == $offset)
							return $node->setNode ();
						
					} else $array[] = $node->setNode ();
					
				}
				
			}
			
			return $array;
			
		}
		
		function isText () {
			return ($this->tag == '#text');
		}
		
		function outerHtml () {
			return $this->dom->ownerDocument->saveHTML ($this->dom);
		}
		
		function html () {
			return $this->innerHtml ();
		}
		
		function text () {
			return $this->text;
		}
		
		function innerHtml () {
			
			$text = '';
			
			if ($this->dom->hasChildNodes ()) {
				
				foreach ($this->dom->childNodes as $child) {
					
					if (in_array ($child->nodeType, $this->textNodes))
						$text .= $child->nodeValue;
					else
						$text .= $child->ownerDocument->saveHTML ($child);
					
				}
				
			}
			
			return $text;
			
		}
		
		function remove ($selector = '', $offset = -1): HTMLElement {
			
			if ($selector) {
				
				$elem = new self ($this->dom);
				$elem->dom = $elem->getDom ($elem->dom);
				
				$xpath = new DOMXpath ($elem->dom);
				
				$query = self::selector2xpath ($selector, $offset, true);
				
				if ($nodes = $xpath->query ($query)) {
					
					if ($offset >= 0) {
						
						$node = $nodes->item ($offset);
						$node->parentNode->removeChild ($node);
						
					} else foreach ($nodes as $node)
						$node->parentNode->removeChild ($node);
					
				}
				
				$query = self::selector2xpath ('root', 0);
				
				if ($nodes = $xpath->query ($query))
					$elem->dom = $nodes->item (0);
				//print_r ($elem->innerHtml ());
			}// else $this->dom->parentNode->removeChild ($this->dom);
			
			$elem = new self ($elem->dom);
			
			return $elem->setNode ();
			
		}
		
		function __toString () {
			return $this->html ();
		}
		
	}