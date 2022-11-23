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
		
		static function testSelectors (int $offset = -1, bool $cache = true) {
			
			$selectors = [
				
				'elem' => '//elem',
				'parent child' => '//parent//child',
				'parent > child' => '//parent/child',
				'parent + child' => '//child/following::parent', // TODO
				'elem1, elem2' => '//elem1 | //elem2',
				'.class' => '//*[contains (concat (\' \', normalize-space (@class), \' \'), \' class \')]',
				'#id' => '//*[@id=\'id\']',
				'[attr]' => '//*[@attr]',
				'[attr1][attr2]' => '//*[@attr1 and @attr2]',
				'[attr=value]' => '//*[@attr=\'value\']',
				'[attr*=value]' => '//*[contains (@attr, \'value\')]',
				'[attr^=value]' => '//*[starts-with (@attr, \'value\')]',
				'[attr$=value]' => '//*[ends-with (@attr, \'value\')]',
				'[attr|=value]' => '//*[starts-with (@attr, \'value-\')]', // TODO
				'[attr~=value]' => '//*[contains (concat (\' \', normalize-space (@attr), \' \'), \' value \')]',
				':first-child' => '//*[1]',
				':last-child' => '//*[last ()]',
				':nth-child(1)' => '//*[position () = 1]',
				':nth-child(3n+0)' => '//*[(position () - 0) mod 3 = 0 and position () >= 0]',
				':nth-child(even)' => '//*[position () mod 2 = 0 and position () >= 0]',
				':nth-child(odd)' => '//*[(position () - 1) mod 2 = 0 and position () >= 1]',
				
			];
			
			$results = [];
			
			foreach ($selectors as $selector => $xpath2) {
				
				$xpath = HTMLElement::selector2xpath ($selector, $offset, $cache);
				
				if ($xpath == HTMLElement::offset ($xpath2, $offset))
					$results[] = [1, $selector, $xpath];
				else
					$results[] = [0, $selector, $xpath, HTMLElement::offset ($xpath2, $offset)];
				
			}
			
			return $results;
			
		}
		
		function __toString () {
			return $this->newInstance ()->html ();
		}
		
		static function test ($selector = '') {
			
			if (!$selector) {
				
				$output = '<table>
	';
				
				foreach (HTMLDocument::testSelectors () as $result) {
					
					if ($result[0])
						$output .= '<tr style="color:green;">
		<td style="padding-right:30px;">'.$result[1].'</td>
		<td>'.$result[2].'</td>
	</tr>
	';
					else
						$output .= '<tr style="color:red;">
		<td style="padding-right:30px;">'.$result[1].'</td>
		<td>'.$result[2].' <i>but expected</i> '.$result[3].'</td>
	</tr>
	';
					
				}
				
				$output .= '<table>';
				
			} else $output = HTMLElement::selector2xpath ($selector);
			
			return $output;
			
		}
		
	}
	
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
			$id = '(\#(?P<id>[^\s:>#\.]+))?';
			$class = '(\.(?P<class>[^\s:>]+))?';
			$pseudo = '(:(?P<pseudo>'.$child.')'.$expr.'?)?';
			$attr = '(\[(?P<attr>\S+?)(=(?P<value>[^\]]+))?\]+)?';
			$rel = '\s*(?P<rel>[\>\,\+])?';
			
			return '/'.$id.$class.$pseudo.$tag.$attr.$rel.'/isS';
			
		}
		
		static function selector2xpath (string $selector, int $offset = -1, bool $cache = true) {
			
			$query = '';
			
			if (!$cache or !isset (HTMLDocument::$cache[$selector])) {
				
				if (preg_match_all (self::getRegexp (), $selector, $match)) {
					
					$replaces = [
						
						'*' => 'contains',
						'^' => 'starts-with',
						'$' => 'ends-with',
						
					];
					
					//print_r ($match);
					
					$rel = '';
					$i2 = 0;
					
					foreach ($match[0] as $i => $item) if (trim ($item)) {
						
						$brackets = [];
						
						if ($match['id'][$i])
							$brackets[] = '@id=\''.$match['id'][$i].'\'';
						
						$bracket = '';
						$i3 = 0;
						$attr = trim ($match['attr'][$i2]);
						
						if ($rel != '>') {
							
							while ($attr = trim ($match['attr'][$i2])) {
								
								if ($i3 > 0) $bracket .= ' and ';
								
								if ($value = $match['value'][$i2]) {
									
									$last = substr ($attr, -1);
									
									if (isset ($replaces[$last]) and $type = $replaces[$last])
										$bracket .= $type.' (@'.substr ($attr, 0, -1).', \''.$value.'\')';
									elseif ($last == '~')
										$bracket .= $replaces['*'].' (concat (\' \', normalize-space (@'.substr ($attr, 0, -1).'), \' \'), \' '.$value.' \')';
									elseif ($last == '|')
										$bracket .= $replaces['^'].' (@'.substr ($attr, 0, -1).', \''.$value.'-\')';
									else
										$bracket .= '@'.$attr.'=\''.$value.'\'';
									
								} else $bracket .= '@'.$attr;
								
								$i2++;
								$i3++;
								
							}
							
							if ($rel) $i2++;
							
						}
						
						if ($bracket) $brackets[] = $bracket;
						
						if ($match['class'][$i])
							foreach (explode ('.', $match['class'][$i]) as $class) // .class1.class2
								$brackets[] = 'contains (concat (\' \', normalize-space (@class), \' \'), \' '.$class.' \')';
						
						if ($pseudo = $match['pseudo'][$i]) {
							
							if ($pseudo == 'first-child')
								$brackets[] = '1';
							elseif ($pseudo == 'last-child')
								$brackets[] = 'last ()';
							elseif ($pseudo == 'nth-child')
							if ($e = $match['expr'][$i]) {
								
								if ($e == 'odd')
									$brackets[] = '(position () - 1) mod 2 = 0 and position () >= 1';
								elseif ($e == 'even')
									$brackets[] = 'position () mod 2 = 0 and position () >= 0';
								elseif (preg_match ('/^[0-9]+$/', $e))
									$brackets[] = 'position () = '.$e;
								elseif (preg_match ('/^((?P<mul>[0-9]+)n\+)(?P<pos>[0-9]+)$/is', $e, $esubs)) {
									
									if (is_isset ('mul', $esubs))
										$brackets[] = '(position () - '.$esubs['pos'].') mod '.$esubs['mul'].' = 0 and position () >= '.$esubs['pos'];
									else
										$brackets[] = $e;
									
								}
								
							}
							
						}
						
						if ($brackets or $match['tag'][$i])
						if (!$attr or $match['rel'][$i] != '>') {
							
							if ($rel == ',')
								$query .= ' | ';
							elseif ($rel == '+')
								$query .= '/following::';
							
							if ($rel == '>')
								$query .= '/';
							else
								$query .= '//';
							
							if ($tag = $match['tag'][$i])
								$query .= $tag;
							else
								$query .= '*';
							
						}
						
						if ($c = count ($brackets)) {
							
							if ($c > 1)
								$query .= '[('.implode ('', $brackets).')]';
							else
								$query .= '['.implode ('', $brackets).']';
							
						}
						
						if ($match['rel'][$i])
							$rel = $match['rel'][$i];
						
					}
					
					if ($cache) HTMLDocument::$cache[$selector] = $query;
					
				}
				
			} else $query = HTMLDocument::$cache[$selector];
			
			return $query;
			
		}
		
		static function offset ($query, $offset) {
			
			if ($offset > -1) $query .= '['.($offset + 1).']';
			return $query;
			
		}
		
		public function find (string $selector, int $offset = -1, bool $cache = true) {
			
			$xpath = new DOMXpath ($this->getDom ($this->dom));
			
			$query = self::selector2xpath ($selector, $offset, $cache);
			
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
		
		function remove (string $selector = '', int $offset = -1, bool $cache = true): HTMLElement {
			
			if ($selector) {
				
				$elem = new self ($this->dom);
				$elem->dom = $elem->getDom ($elem->dom);
				
				$xpath = new DOMXpath ($elem->dom);
				
				$query = self::selector2xpath ($selector, $offset, $cache);
				
				if ($nodes = $xpath->query ($query)) {
					
					if ($offset >= 0) {
						
						$node = $nodes->item ($offset);
						$node->parentNode->removeChild ($node);
						
					} else foreach ($nodes as $node)
						$node->parentNode->removeChild ($node);
					
				}
				
				$query = self::selector2xpath ('root', 0, $cache);
				
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