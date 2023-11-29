<?php
	
	class Math {
		
		static function normalize ($value, $min, $max) {
			return ($value - $min) / ($max - $min);
		}
		
		static function denormalize ($normalized, $min, $max) {
			return ($normalized * ($max - $min) + $min);
		}
		
	}