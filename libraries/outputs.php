<?php
/*
 ========================================
 Mash Framework (c) 2010-2017, 2020
 ----------------------------------------
 https://mash.ointeractive.ru/
 ========================================
 O! Interactive (support@ointeractive.ru)
 ----------------------------------------
 Библиотека
 -- Представление данных
 ========================================
*/
	
	if (!defined ('MASH')) die ('File must be started only through the main framework cover');
	
	function a_link ($href, $title, $options = false, $alt = '') {
		
		if ($options or !is_array ($options)) {
			
			if (!is_array ($options)) {
				
				$data = $options;
				
				$options = ['rel' => [], 'ajax' => false];
				
						if ($data == 0) $options['ajax'] = true;
				elseif ($data == 2) $options['rel'][] = 'nofollow';
				elseif ($data != 3) $href .= '" target="_blank';
				
				$options['rel'][] = 'external';
				
			} else {
				
				if (!isset ($options['rel'])) $options['rel'] = [];
				$options['rel'] = make_array ($options['rel']);
				
				foreach ($options['rel'] as $rel)
				if (!in_array ($rel, $options['rel']))
				$options['rel'][] = $rel;
				
				if (!in_array ('external', $options['rel']))
				$options['rel'][] = 'external';
				
				if (isset ($options['data'])) {
					
					foreach ($options['data'] as $key => $value)
					$href .= '" data-'.$key.'="'.$value;
					
				}
				
				if (!isset ($options['ajax']))
					$options['ajax'] = true;
				
			}
			
		} else $options = ['rel' => [], 'ajax' => false];
		
		if ($options['ajax']) $href .= '" class="ajax';
		
		if ($alt) $href .= '" title="'.$alt;
		if ($options['rel']) $href .= '" rel="'.implode (' ', $options['rel']);
		
		$link = '<a href="'.$href.'">'.$title.'</a>';
		
		return $link;
		
	}
	
	function require_js ($input) {
		return '<script src="'.$input.'"></script>';
	}
	
	function require_css ($file, $i = 0, $num = 0) {
		return '<link type="text/css" href="'.$file.'" rel="stylesheet" media="all"/>';
	}
	
	function echo_js ($string, $nbsp = '	', $nbsp2 = '	 ') {
		return '<script>
'.$nbsp2.$nbsp2.'//<![CDATA[
'.$nbsp2.$nbsp2.$string.'//]]>
'.$nbsp.$nbsp.'</script>';
	}
	
	function show_js ($file, $minify = true) {
		
		$output = file_get_content ($file);
		if ($minify) $output = js_minify ($output);
		
		return echo_js ($output);
		
	}
	
	function w3c_encode ($string) {
		return repair_amps (str_replace (['&'], ['&amp;'], $string));
	}
	
	function w3c_decode ($string) {
		return str_replace (['&amp;'], ['&'], $string);
	}
	
	function js_alert ($text, $title = '') {
		
		if (!$title) $title = 'Уведомление';
		if (is_array ($text)) $text = mess2br ($text);
		
		return echo_js ('lisas_alert (\''.spech_encode ($text).'\', \''.spech_encode ($title).'\');');
		
	}
	
	function encode_system_tags ($str) { // Кодирует системные символы
		
		$find = ['$'];
		$replace = ['&#036;'];
		
		$str = str_replace ($find, $replace, $str);
		
		return $str;
		
	}
	
	function decode_system_tags ($str, $decode_all = true) { // Декодирует системные символы
		
		$find = [];
		$replace = [];
		
		$str = str_replace ($find, $replace, $str);
		
		return $str;
		
	}
	
	function rss_preg ($content, $type = '') {
		
		$content = decode_system_tags ($content);
		$content = str_replace (['&quot;'], ['"'], $content);
		
		return $content;
		
	}
	
	function prepare_tags ($post, $trim = 0) {
		
		if (not_empty ($post)) {
			
			$tags_array = [];
			$tag = sep_explode ($post);
			
			foreach ($tag as $tag)
			if (!is_email ($tag)) $tags_array[] = clearspecialchars (str_correct ($tag), 0);
			
			$tags_array = array_unique ($tags_array);
			$tags_array = trim_array ($tags_array, $trim);
			
			$tags = sep2_implode ($tags_array);
			
		} else $tags = '';
		
		return $tags;
		
	}
	
	function ajax_output ($content) {
		$content = stripslashes ($content);
		return $content;
	}
	
	function preg_prepspecialchars ($string) {
		return preg_replace (['/\s+/ms'], ['-'], $string);
	}
	
	/*1.Empty Nodes: Following will create an empty node. 

$books = array();	// or
$books = null;	// or
$books = '';
$xml = Array2XML::createXML('books', $books);
 
// all three cases above create <books/>

2.Attributes: Attributes can be added to any node by having a @attributes key in the array 

$books = array(
		'@attributes' => array(
				'type' => 'fiction',
				'year' => 2011,
				'bestsellers' => true
		)
);

$xml = Array2XML::createXML('books', $books);
 
// creates <books type="fiction" year="2011" bestsellers="true"/>

3.Node Value: For nodes without attributes, value can be assigned directly, else we need to have a @value key in the array. Following examples will make it clear 

$books = 1984;	// or
$books = array(
		'@value' = 1984
);
// creates <books>1984</books>
 
$books = array(
		'@attributes' => array(
				'type' => 'fiction'
		),
		'@value' = 1984
);
// creates <books type="fiction">1984</books>
 
$books = array(
		'@attributes' => array(
				'type' => 'fiction'
		),
		'book' => 1984
);

creates

<books type="fiction">
	<book>1984</book>
</books>
 
$books = array(
		'@attributes' => array(
				'type' => 'fiction'
		),
		'book'=> array('1984','Foundation','Stranger in a Strange Land')
);

creates 
<books type="fiction">
	<book>1984</book>
	<book>Foundation</book>
	<book>Stranger in a Strange Land</book>
</books>

4.Complex XML: Following example clarifies most of the usage of the library 

$books = array(
		'@attributes' => array(
				'type' => 'fiction'
		),
		'book' => array(
				array(
						'@attributes' => array(
								'author' => 'George Orwell'
						),
						'title' => '1984'
				),
				array(
						'@attributes' => array(
								'author' => 'Isaac Asimov'
						),
						'title' => array('@cdata'=>'Foundation'),
						'price' => '$15.61'
				),
				array(
						'@attributes' => array(
								'author' => 'Robert A Heinlein'
						),
						'title' =>	array('@cdata'=>'Stranger in a Strange Land'),
						'price' => array(
								'@attributes' => array(
										'discount' => '10%'
								),
								'@value' => '$18.00'
						)
				)
		)
);

creates

<books type="fiction">
	<book author="George Orwell">
		<title>1984</title>
	</book>
	<book author="Isaac Asimov">
		<title><![CDATA[Foundation]]></title>
		<price>$15.61</price>
	</book>
	<book author="Robert A Heinlein">
		<title><![CDATA[Stranger in a Strange Land]]</title>
		<price discount="10%">$18.00</price>
	</book>
</books>*/
	
	/*debug_html (array2xml ('config', [
		
		'book' => ['111' => 222, 'hhh' => 444],
		
	]));*/
	
	function array2xml ($elem, $array, $options = []) {
		
		$options = array_extend ($options, [
			
			'version' => '1.0',
			'encoding' => 'utf-8',
			'preserve_wspaces' => true,
			'format_output' => true,
			
		]);
		
		if (!$obj) {
			
			$dom = new DomDocument ($options['version'], $options['encoding']);
			
			$dom->preserveWhiteSpace = $options['preserve_wspaces'];
			$dom->formatOutput = $options['format_output'];
			
		}
		
		$xml = _array2xml ($dom, $array, $dom);
		
		return $xml->saveXML ();
		
	}
	
	function _array2xml ($obj, $data, $dom) {
		
		if (is_array ($data)) {
			
			foreach ($data as $key => $item) {
				
				if (is_numeric ($key)) $key = 'n'.$key;
				_array2xml ($obj->appendChild ($dom->createElement ($key)), $item, $dom);
				
			}
			
		} else $obj->appendChild ($dom->createTextNode ($data));
		
		return $obj;
		
	}
	
	/*				if (is_array ($data)) {
						
						if (isset ($data['@attributes'])) {
							
							foreach ($data['@attributes'] as $key => $value) {
								
								if (xml_is_valid_tag ($key))
								$node->setAttribute ($key, bool2str ($value));
								
							}
							
							unset ($data['@attributes']);
							
						}
						
						if (isset ($data['@value'])) {
							
							$node->appendChild ($xml->createTextNode (bool2str ($data['@value'])));
							unset ($data['@value']);
							
						} elseif (isset ($data['@cdata'])) {
							
							$node->appendChild ($xml->createCDATASection (bool2str ($data['@cdata'])));
							unset ($data['@cdata']);
							
						}
						
					}*/
					
	function xml2object ($str, $tag = '') {
		
		if (!is_array ($str)) {
			
			if ($tag) $str = '<'.$tag.'>'.$str.'</'.$tag.'>';
			$str = new SimpleXMLElement ($str, LIBXML_BIGLINES | LIBXML_COMPACT | LIBXML_PARSEHUGE | LIBXML_PEDANTIC | LIBXML_NOBLANKS | LIBXML_NOCDATA);
			
		}
		
		return $str;
		
	}
	
	function xml2array ($str, $tag = '') {
		
		if (!is_array ($str)) {
			
			$str = xml2object ($str, $tag);
			$str = object2array ($str);
			
		}
		
		return $str;
		
	}
	
	function object2array ($obj) {
		
		if (is_object($obj)) $obj = (array)$obj;
		if (is_array($obj)) {
				$new = array();
				foreach ($obj as $key => $val) {
						$new[$key] = object2array($val);
				}
		} else {
				$new = $obj;
		}

		return $new;
		
	}
	
	function array2object ($array) {
		
		$object = new StdClass;
		foreach ($array as $key => $value)
		$object->$key = $value;
		
		return $object;
		
	}
	
	function xml_is_valid_tag ($tag) {
		
		$pattern = '~^[a-z_]+[a-z0-9\:\-\.\_]*[^:]*$~i';
		return preg_match ($pattern, $tag, $matches) !== false and $matches[0] == $tag;
		
	}
	
	function dom_dump ($obj) {
		
		if ($classname = get_class ($obj)) {
			
			$retval = 'Instance of '.$classname.', node list:'.NL.NL;
			
			switch (true) {
				
				case ($obj instanceof DOMDocument):
					$retval .= 'XPath: '.$obj->getNodePath ().NL.$obj->saveXML ($obj).NL.NL;
				break;
				
				case ($obj instanceof DOMElement):
					$retval .= 'XPath: '.$obj->getNodePath ().NL.$obj->ownerDocument->saveXML ($obj).NL.NL;
				break;
				
				case ($obj instanceof DOMAttr):
					
					$retval .= 'XPath: '.$obj->getNodePath ().NL.$obj->ownerDocument->saveXML ($obj).NL.NL;
					//$retval .= $obj->ownerDocument->saveXML ($obj);
					
				break;
				
				case ($obj instanceof DOMNodeList):
					
					for ($i = 0; $i < $obj->length; $i++)
					$retval .= 'Item #'.$i.', XPath: '.$obj->item ($i)->getNodePath ().NL
.$obj->item ($i)->ownerDocument->saveXML ($obj->item ($i)).NL.NL;
					
				break;
				
				default:
					$retval = 'Instance of unknown class';
				break;
				
			}
			
		} else $retval = 'no elements';
		
		echo $retval;
		
	}
	
	function str_to_xpath ($str, $errors = 1) {
		
		if (!$errors) libxml_use_internal_errors (true);
		
		$doc = new DOMDocument ();
		$doc->loadHTML ('<?xml encoding="utf-8" ?>'.$str);
		return new DOMXPath ($doc);
		
	}
	
	function processChunk() {
		
		GLOBAL $CHUNKS, $PAYLOAD, $ITEMCOUNT;
		
		if ('' == $PAYLOAD)
			 return;
		$xp = fopen($file = "output-$CHUNKS.xml", "w");
		fwrite($xp, '<?xml version="1.0"?>'."\n");
			 fwrite($xp, "<root>");
					 fwrite($xp, $PAYLOAD);
			 fwrite($xp, "</root>");
		fclose($xp);
		print "Written $file\n";
		$CHUNKS++;
		$PAYLOAD		= '';
		$ITEMCOUNT	= 0;
		
	}
	
	function startElement($xml, $tag, $attrs = array()) {
			GLOBAL $PAYLOAD, $CHUNKS, $ITEMCOUNT, $CHUNKON;
			if (!($CHUNKS||$ITEMCOUNT))
					if ($CHUNKON == strtolower($tag))
							$PAYLOAD = '';
			$PAYLOAD .= "<$tag";
			foreach($attrs as $k => $v)
					$PAYLOAD .= " $k=".'"'.addslashes($v).'"';
			$PAYLOAD .= '>';
	}

	function endElement($xml, $tag) {
			GLOBAL $CHUNKON, $ITEMCOUNT, $ITEMLIMIT;
			dataHandler(null, "</$tag>");
			if ($CHUNKON == strtolower($tag))
					 if (++$ITEMCOUNT >= $ITEMLIMIT)
							 processChunk();
	}
	
	function dataHandler($xml, $data) {
			GLOBAL $PAYLOAD;
			$PAYLOAD .= $data;
	}
	
	function defaultHandler($xml, $data) {}
	
	function CreateXMLParser ($CHARSET, $bareXML = false) {
		
		$CURRXML = xml_parser_create($CHARSET);
		xml_parser_set_option( $CURRXML, XML_OPTION_CASE_FOLDING, false);
		xml_parser_set_option( $CURRXML, XML_OPTION_TARGET_ENCODING, $CHARSET);
		xml_set_element_handler($CURRXML, 'startElement', 'endElement');
		xml_set_character_data_handler($CURRXML, 'dataHandler');
		xml_set_default_handler($CURRXML, 'defaultHandler');
		if ($bareXML)
		xml_parse($CURRXML, '<?xml version="1.0"?>', 0);
		return $CURRXML;
		
	}
	
	function ChunkXMLBigFile ($file, $tag = 'item', $howmany = 5) {
		GLOBAL $CHUNKON, $CHUNKS, $ITEMLIMIT;

		// Every chunk only holds $ITEMLIMIT "$CHUNKON" elements at most.
		$CHUNKON	 = $tag;
		$ITEMLIMIT = $howmany;
		
		$xml = CreateXMLParser ('UTF-8', false);
		
		$fp = fopen($file, "r");
		
		$CHUNKS	= 0;
		
		while(!feof($fp)) {
			$chunk = fgets($fp, 10240);
			xml_parse($xml, $chunk, feof($fp));
		}
		
		xml_parser_free($xml);
		
		processChunk ();
		
	}
	
	function is_json ($str) {
		return ($str[0] == '[' or $str[0] == '{');
	}
	
	function is_valid_json ($string) {
		
		$error = true;
		
		if (!is_array ($string)) {
			
			$result = json2array ($string);
			
			switch (json_last_error ()) {
				
				default:
					$error = 'JSON error occured';
				break;
				
				case JSON_ERROR_NONE:
					$error = true;
				break;
				
				case JSON_ERROR_DEPTH:
					$error = 'The maximum stack depth has been exceeded';
				break;
				
				case JSON_ERROR_STATE_MISMATCH:
					$error = 'Invalid or malformed JSON';
				break;
				
				case JSON_ERROR_CTRL_CHAR:
					$error = 'Control character error, possibly incorrectly encoded';
				break;
				
				case JSON_ERROR_SYNTAX:
					$error = 'Syntax error, malformed JSON';
				break;
				
				// PHP >= 5.3.3
				case JSON_ERROR_UTF8:
					$error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
				break;
				
				// PHP >= 5.5.0
				case JSON_ERROR_RECURSION:
					$error = 'One or more recursive references in the value to be encoded';
				break;
				
				// PHP >= 5.5.0
				case JSON_ERROR_INF_OR_NAN:
					$error = 'One or more NAN or INF values in the value to be encoded';
				break;
				
				case JSON_ERROR_UNSUPPORTED_TYPE:
					$error = 'A value of a type that cannot be encoded was given';
				break;
				
			}
			
		}
		
		return $error;
		
	}
	
	function percent ($a, $b, $c = 100) {
		return (($a * $b) / $c);
	}
	
	function preg_account ($type = '@') {
		return '~'.$type.'([a-z0-9\-_.]+)(\b)~i';
	}
	
	function preg_tag ($type = '#') {
		return '~'.$type.'([a-z'.CUR_SYMBOLS.'0-9\-\_\.]+)(\s|\,|#|$)~iu';
	}
	
	function _is_social_elem ($elem, $str) {
		
		preg_match_all (preg_account ($elem), $str, $match);
		return $match;
		
	}
	
	function is_account ($str) {
		return _is_social_elem ('@', $str);
	}
	
	function match_tags ($str) {
		return new_preg_match_all (preg_tag (), $str);
	}
	
	function array2rss ($array, $c_data, $options = []) {
		
		$options = array_extend ($options, [
			
			'xml_version' => '1.0',
			'rss_version' => '2.0',
			'charset' => 'utf-8',
			
		]);
		
		$rss = '<?xml version="'.$options['xml_version'].'" encoding="'.$options['charset'].'"?>
';
		
		$_c_data = [];
		foreach ($c_data as $key => $value)
		$_c_data[] = [$key, [], $value];
		
		foreach ($array as $data) {
			
			$n_data = [];
			foreach ($data as $key => $value)
			$n_data[] = [$key, [], $value];
			
			$_c_data[] = ['item', [], $n_data];
			
		}
		
		$output = [['channel', [], $_c_data]];
		
		//print_r ($output);
		
		$c_array = [
			
			['rss', ['version' => $options['rss_version']], $output],
			
		];
		
		$rss .= array2html ($c_array);
		
		return $rss;
		
	}
	
	function array2json (array $str = [], int $flags = 0) {
		
		$flags |= JSON_UNESCAPED_UNICODE;
		$flags |= JSON_UNESCAPED_SLASHES;
		$flags |= JSON_THROW_ON_ERROR;
		$flags |= JSON_UNESCAPED_LINE_TERMINATORS;
		
		return json_encode ($str, $flags);
		
	}
	
	function json2array ($str, $flags = JSON_THROW_ON_ERROR, $depth = 512) {
		
		$flags |= JSON_INVALID_UTF8_SUBSTITUTE;
		$flags |= JSON_OBJECT_AS_ARRAY;
		
		return json_decode ($str, true, $depth, $flags);
		
	}
	
	function preg_image () {
		return "#<img.*?src=\"?'?([^\"'>]+)\"?'?.*?>#is";
	}
	
	function prep_var ($var) { // Получает тип переменной $var и форматирует ее вид.
		
		if (is_null ($var)) $output = null;
		elseif (is_bool ($var)) $output = ($var) ? true : false;
		elseif (is_integer ($var) or is_float ($var) or is_numeric ($var)) $output = $var;
		//elseif (is_array ($var)) $output = 'array ('.count ($var).')';
		elseif (is_object ($var)) $output = 'object ('.get_class ($var).')';
		elseif (is_resource ($var)) $output = 'resource ('.get_resource_type ($var).')';
		elseif (is_string ($var)) $output = '\''.$var.'\'';
		
		return $output;
		
	}
	
	function alt_name ($string, $length = 20) {
		
		$string = str_correct ($string, ['str_cut_length' => $length, 'str_cut_sep' => '']);
		$string = to_translit ($string, ['alt_name' => 1]);
		$string = str_clean ($string, NO_CLEAR_DIGITS);
		
		return $string;
		
	}
	
	function change_value_if_empty ($value, $new_value) { // Меняет значение $value на $new_value, если $value пустое.
		
		if (trim ($value) == '') $value = $new_value;
		return $value;
		
	}
	
	function row_compare ($row1, $row2, $row3) {
		
		$result = 1;
		//debug ($row1.' - '.$row2.' - '.$row3);
		
		if (not_empty ($row2)) { // Список разрешенных сайтов 
			
			if (in_array ($row1, sep_explode ($row2))) $result = 1; // Cайт в списке
			else $result = 0;
			
		}
		
		if (not_empty ($row3)) { // Список запрешенных сайтов
			
			if (in_array ($row1, sep_explode ($row3))) $result = 0; // Cайт в списке
			else $result = 1;
			
		}
		
		return $result;
		
	}