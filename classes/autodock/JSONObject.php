<?php
	
	class JSONObject extends Tokenizer {
		
		protected $stack = [], $json;
		
		function __construct ($object) {
			
			parent::__construct ($object);
			
			$this->json = json2array ($this->content);
			
		}
		
		function process (): array {
			
			$key = '';
			$output = [];
			
			while ($this->read ()) {
				
				$this->next ();
				
				if ($this->ch == '{') {
					
					$output[] = $this->buffer;
					$this->buffer = '';
					
					array_push ($this->stack, $output);
					
					$output = [];
					
				} elseif ($this->ch == '}') {
					
					$output[] = $this->buffer;
					$this->buffer = '';
					
					$new = $output;
					
					$output = array_pop ($this->stack);
					
					$output[] = $new;
					
				} elseif ($this->ch == ':') {
					
					$this->trimSpace ();
					
					$key = $this->buffer;
					$this->buffer = '';
					
				} elseif ($this->ch == '\\') {
					
					$this->next ();
					$this->buffer .= $this->ch;
					
				} elseif ($this->ch == '"' or $this->ch == '\'')
					$this->trimSpace ();
				else
					$this->buffer .= $this->ch;
				
			}
			
			return $output;
			
		}
		
		function get (string $key) {
			
			if (isset ($this->json[$key]))
				return $this->json[$key];
			else
				throw new \Exception ('"'.$key.'" key not found');
			
		}
		
		function opt (string $key, $def) {
			
			if (isset ($this->json[$key]))
				return $this->json[$key];
			else
				return $def;
			
		}
		
		function getString (string $key): string {
			return $this->get ($key);
		}
		
		function optString (string $key, $def): string {
			return $this->opt ($key, $def);
		}
		
		function getInt (string $key): int {
			return $this->get ($key);
		}
		
		function optInt (string $key, $def): int {
			return $this->opt ($key, $def);
		}
		
		function getFloat (string $key): float {
			return $this->get ($key);
		}
		
		function optFloat (string $key, $def): float {
			return $this->opt ($key, $def);
		}
		
		function getDouble (string $key): double {
			return $this->get ($key);
		}
		
		function optDouble (string $key, $def): double {
			return $this->opt ($key, $def);
		}
		
		function getBool (string $key): bool {
			return $this->get ($key);
		}
		
		function optBool (string $key, $def): bool {
			return $this->opt ($key, $def);
		}
		
		function __toString () {
			return array2json ($this->json);
		}
		
	}