<?php
/*
 ========================================
 Mash Framework (c) 2010-2017
 ----------------------------------------
 https://mash.ointeractive.ru/
 ========================================
 O! Interactive (support@ointeractive.ru)
 ----------------------------------------
 Класс работы с изображениями
 ========================================
*/
	
	if (!defined ('MASH')) die ('File must be started only through the main framework cover');
	
	class image {
		
		public $data = [], $dest_data = [];
		
		private
		$config = [],
		$mod_config = [];
		
		function __construct ($image) {
			global $config, $mod_config;
			
			$this->config = $config;
			$this->mod_config = $mod_config;
			
			$info = $this->get_size ($image);
			
			if ($info[2] == 2) {
				$this->data['format'] = 'jpg';
				$this->data['src'] = imageCreateFromJPEG ($image);
			} elseif ($info[2] == 3) {
				$this->data['format'] = 'png';
				$this->data['src'] = imageCreateFromPNG ($image);
			} elseif ($info[2] == 1) {
				$this->data['format'] = 'gif';
				$this->data['src'] = imageCreateFromGIF ($image);
			} else @unlink ($image);
			
			if ($this->data['src']) {
				
				$this->data['width'] = imagesX ($this->data['src']);
				$this->data['height'] = imagesY ($this->data['src']);
				
				$this->data['thumb_width'] = $this->data['width'];
				$this->data['thumb_height'] = $this->data['height'];
				
			}
			
			$this->data['source'] = $image;
			
		}
		
		private function get_size ($image) {
			return getimagesize ($image);
		}
		
		function text ($text, $size, $font_file, $x = 0, $y = 0, $color = [255, 255, 255, 0], $angle = 0, $inking = 0, $inking_color = [0, 0, 0, 0], $inking_angle = 0) {
			
			if ($inking > 0) { // Обводка
				
				$x_i = [$inking, 0, $inking, 0, -$inking, -$inking, $inking, 0, -$inking];
				$y_i = [0, -$inking, -$inking, 0, 0, -$inking, $inking, $inking, $inking];
				
				for ($i2 = 0; $i2 <= 8; ++$i2)
				$this->text ($text, $size, $font_file, ($x + $x_i[$i2]), ($y + $y_i[$i2]), $this->color ($inking_color[0], $inking_color[1], $inking_color[2], $inking_color[3]), $inking_angle);
				
			}
			
			return imageTTFText ($this->data['src'], $size, $angle, $x, $y, $this->color ($color[0], $color[1], $color[2], $color[3]), $font_file, $text);
			
		}
		
		private function text_box ($text, $size, $font_file, $angle = 0) {
			return imageTTFBbox ($size, $angle, $font_file, $text);
		}
		
		function resize ($size, $output = false) {
			
			if ($size) {
				
				$size = image_resize ($size, $this->data['width'], $this->data['height']);
				
				$this->data['thumb_width'] = $size[0];
				$this->data['thumb_height'] = $size[1];
				
				$this->create ($this->data['thumb_width'], $this->data['thumb_height']);
				
				imageCopyResampled ($this->data['image'], $this->data['src'], 0, 0, 0, 0, $this->data['thumb_width'], $this->data['thumb_height'], $this->data['width'], $this->data['height']);
				
				$this->data['src'] = $this->data['image'];
				
				if ($size[2] and $size[3])
				$this->crop ($size[2], $size[3], $sizes[2], $sizes[3]);
				
			}
			
		}
		
		private function color ($r, $g, $b, $alpha = 0) {
			
			if ($alpha)
			return imageColorAllocateAlpha ($this->data['image'], $r, $g, $b, $alpha);
			else
			return imageColorAllocate ($this->data['image'], $r, $g, $b);
			
		}
		
		function create ($width, $height, $alpha = 0) {
			
			$this->data['image'] = imageCreateTrueColor ($width, $height);
			$this->alpha ($this->data['image']);
			
			if ($this->data['image']) {
				
				$this->dest_data['width'] = imagesX ($this->data['image']);
				$this->dest_data['height'] = imagesY ($this->data['image']);
				
			}
			
			$white = $this->color (255, 255, 255, $alpha);
			
			imageFill ($this->data['image'], 0, 0, $white);
			
		}
		
		function crop ($width, $height, $top = 0, $left = 0) {
			
			$this->create ($width, $height);
			
			if ($this->data['thumb_width'] <= $width)
			$res = imageCopyResampled ($this->data['image'], $this->data['src'], (floor (($width - $this->data['thumb_width']) / 2)), 0, 0, 0, $this->data['thumb_width'], $height, $this->data['thumb_width'], $height);
			elseif ($this->data['thumb_height'] >= $height)
			$res = imageCopyResampled ($this->data['image'], $this->data['src'], 0, 0, $left, $top, $width, $height, $width, $height);
			
			if ($res) $this->data['src'] = $this->data['image'];
			
			return $res;
			
		}
		
		function supply ($width, $height) {
			
			$this->create ($width, $height);
			
			$new_width = floor (($width - $this->data['width']) / 2);
			$new_height = floor (($height - $this->data['height']) / 2);
			
			imageCopyResampled ($this->data['image'], $this->data['src'], $new_width, $new_height, 0, 0, $this->data['width'], $this->data['height'], $width, $height);
			
			$this->data['src'] = $this->data['image'];
			
		}
		
		private function alpha ($image) {
			
			if ($this->data['format'] == 'png') {
				
				imageAlphaBlending ($image, false);
				imageSaveAlpha ($image, true);
				
			}
			
		}
		
		function save ($dest = null) {
			
			$output = false;
			
			if ($this->data['format'] == 'png') {
				
				$this->alpha ($this->data['src']);
				$output = imagePNG ($this->data['src'], $dest);
				
			} elseif ($this->data['format'] == 'jpg') {
				
				if ($this->config['jpeg_quality'])
				$output = imageJPEG ($this->data['src'], $dest, $this->config['jpeg_quality']);
				else
				$output = imageJPEG ($this->data['src'], $dest);
				
			} elseif ($this->data['format'] == 'gif')
			$output = imageGIF ($this->data['src'], $dest);
			
			imageDestroy ($this->data['src']);
			//imageDestroy ($this->data['image']);
			
			return $output;
			
		}
		
		function watermarks () {
			global $tpl, $mod;
			
			return array (
				
				'light' => $this->tpl->get_dir ('image/watermark_light.png', 1),
				'dark' => $this->tpl->get_dir ('/image/watermark_dark.png', 1),
				
			);
			
		}
		
		function insert_watermark ($w_image, $min_image, $type = 0) { // Накладываем ватермарк $w_image, если изображение больше $min_image.
			
			$margin = 7;
			
			$this->data['width'] = imagesX ($this->data['src']);
			$this->data['height'] = imagesY ($this->data['src']);
			
			list ($w_info[0], $w_info[1]) = $this->get_size ($w_image['light']);
			
			if ($type == 2) {
				
				$w_x = rand ($margin, $this->data['width'] - $margin - $w_info[0]);
				$w_y = rand ($margin, $this->data['height'] - $margin - $w_info[1]);
				
			} else {
				
				$w_x = $this->data['width'] - $margin - $w_info[0];
				$w_y = $this->data['height'] - $margin - $w_info[1];
				
			}
			
			$w_x2 = $w_x + $w_info[0];
			$w_y2 = $w_y + $w_info[1];
			
			if ($w_x < 0 or $w_y < 0 or $w_x2 > $this->data['width'] or $w_y2 > $this->data['height'] or $this->data['width'] < $min_image or $this->data['height'] < $min_image) return;
			
			$pic = imageCreateTrueColor (1, 1);
			
			imageCopyResampled ($pic, $this->data['src'], 0, 0, $w_x, $w_y, 1, 1, $w_info[0], $w_info[1]);
			
			$rgb = imageColorAt ($pic, 0, 0);
			
			$r = ($rgb >> 16) & 0xFF;
			$g = ($rgb >> 8) & 0xFF;
			$b = $rgb & 0xFF;
			
			$max = min ($r, $g, $b);
			$min = max ($r, $g, $b);
			$lightness = (double) (($max + $min) / 510.0);
			imageDestroy ($pic);
			
			$wm_image = ($lightness < 0.5) ? $w_image['light'] : $w_image['dark'];
			$w = imageCreateFromPNG ($wm_image);
			
			imageAlphaBlending ($this->data['src'], true);
			imageAlphaBlending ($w, true);
			
			if ($this->mod_config['w_type'] == 3) {
				
				$num_x = floor ($this->data['width'] / $w_info[0]);
				$num_y = floor ($this->data['height'] / $w_info[1]);
				
				for ($i = 0; $i <= $num_x; ++$i) {
					
					$w_x = $w_info[0] * $i;
					
					if ($i > 0) imageCopy ($this->data['src'], $w, $w_x, $w_y, 0, 0, $w_info[0], $w_info[1]);
					
					for ($x = 0; $x <= $num_y; ++$x) {
						
						$w_y = $w_info[1] * $x;
						imageCopy ($this->data['src'], $w, $w_x, $w_y, 0, 0, $w_info[0], $w_info[1]);
						
					}
					
				}
				
			} else imageCopy ($this->data['src'], $w, $w_x, $w_y, 0, 0, $w_info[0], $w_info[1]);
			
			imageDestroy ($w);
			
		}
		
		function erase_watermark ($watermark_image, $target = '') { // Удаляем ватермарк $watermark_image и выводим в изображение $target. Функция эксперементальная: убирает не до конца, на разных типах серверов вычисляет по-разному, может работать некорректно.
			
			$watermark = imageCreateFromPNG ($watermark_image);
			
			list ($image_width, $image_height) = getimagesize ($this->data['source']);
			
			$dest = imageCreateTrueColor ($image_width, $image_height);
			
			for ($y = 0; $y < $image_height; ++$y) { // Высота
				
				for ($x = 0; $x < $image_width; ++$x) { // Ширина
					
					$source_rgb = $this->get_color ($this->data['src'], $x, $y);
					$watermark_rgb = $this->get_color ($watermark, $x, $y);
					
					$red = $this->unblend ($source_rgb[0], $watermark_rgb[0], $watermark_rgb[3]);
					$green = $this->unblend ($source_rgb[1], $watermark_rgb[1], $watermark_rgb[3]);
					$blue = $this->unblend ($source_rgb[2], $watermark_rgb[2], $watermark_rgb[3]);
					
					$pixelcolor = ($red << 16) | ($green << 8) | $blue;
					imageSetPixel ($dest, $x, $y, $pixelcolor);
					
				}
				
			}
			
		}
		
		function get_color ($image, $x, $y) { // Получаем цвет изображения
			
			$rgb = imageColorAt ($image, $x, $y);
			
			$r = ($rgb >> 16) & 0xFF;
			$g = ($rgb >> 8) & 0xFF;
			$b = $rgb & 0xFF;
			$alpha = abs ((($rgb >> 24) & 0xFF) / 127 - 1);
			
			return [$r, $g, $b, $alpha];
			
		}
		
		private function unblend ($dest, $color, $alpha) { // Обращаем цвет изображения в исходный цвет
			
			if ($alpha != 1) {
				
				$color = ($dest - $alpha * $color) / (1 - $alpha);
				$color = $color < 0 ? 0 : round ($color);
				$color = $color > 255 ? 255 : $color;
				
			}
			
			return $color;
			
		}
		
		private function path ($dir, $file = '') {
			
			$path = MASH_DIR.'classes/autodock/images';
			$path .= '/'.$dir;
			if ($file) $path .= '/'.$file;
			
			return $path;
			
		}
		
		function overlay ($type, $amount = 100) {
			
			$this->create ($this->data['width'], $this->data['height'], 127);
			
			imageFilledRectangle ($this->data['image'], 0, 0, $this->data['width'], $this->data['height'], $bg);
			
			$png = imageCreateFromPNG ($this->path ('overlays', $type.'.png'));
			
			$width = imagesX ($png);
			$height = imagesY ($png);
			
			imageCopyResampled ($this->data['image'], $png, 0, 0, 0, 0, $this->data['width'], $this->data['height'], $width, $height);
			
			$comp = imageCreateTrueColor ($width, $height);
			
			imageCopy ($comp, $this->data['src'], 0, 0, 0, 0, $width, $height);
			imageCopy ($comp, $this->data['image'], 0, 0, 0, 0, $width, $height);
			
			imageCopyMerge ($this->data['src'], $comp, 0, 0, 0, 0, $width, $height, $amount);
			
			imageDestroy ($comp);
			
		}
		
		function filter ($filter, $arg1 = null, $arg2 = null, $arg3 = null, $arg4 = null) {
			
			if (is_array ($filter)) {
				
				$files = dir_scan ($this->path ('filters'), ['allow_types' => 'php']);
				
				foreach ($files as $file)
				if (in_array (get_filename ($file), $filter))
				require $file;
				
			} else switch (func_num_args ()) {
				
				case 1: imageFilter ($this->data['src'], $filter); break;
				case 2: imageFilter ($this->data['src'], $filter, $arg1); break;
				case 3: imageFilter ($this->data['src'], $filter, $arg1, $arg2); break;
				case 4: imageFilter ($this->data['src'], $filter, $arg1, $arg2, $arg3); break;
				case 5: imageFilter ($this->data['src'], $filter, $arg1, $arg2, $arg3, $arg4); break;
				
			}
			
		}
		
		function text_watermark ($text, $font_size = 1, $white = [255, 255, 255], $black = [0, 0, 0]) {
			
			if ($text) {
				
				$rgb = imageColorAt ($this->data['src'], ($this->data['width'] - 5), ($this->data['height'] - 5));
				
				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;
				
				$min = max ($r, $g, $b);
				$max = min ($r, $g, $b);
				$lightness = (double) (($max + $min) / 510.0);
				
				if ($lightness < 0.5)
				$color = $this->color ($white[0], $white[1], $white[2]);
				else
				$color = $this->color ($black[0], $black[1], $black[2]);
				
				imageString ($this->data['src'], $font_size, (($this->data['width'] - lisas_strlen ($text) * 5) - 3), ($this->data['height'] - 11), $text, $color);
				
			}
			
		}
		
		function meme ($data, $rand = 0) {
			
			$font = MASH_DIR.'fonts/impact.ttf'; // Файл шрифта
			$thickness = 2; // Толщина обводки
			
			$text_color_c = [0, 0, 0]; // Обводка
			$text_color = [255, 255, 255]; // Текст
			
			$font_size_top = intval_correct ($data[0][1], 34);
			$font_size_bottom = intval_correct ($data[1][1], 34);
			
			$text_tops = nl_explode ($data[0][0]);
			$text_bottoms = nl_explode ($data[1][0]);
			
			$text_top_array = [];
			$text_bottom_array = [];
			
			$i = 0;
			
			if ($rand) shuffle ($text_tops);
			
			foreach ($text_tops as $text_top) { // Верх
				++$i;
				
				$text_top = str_correct ($text_top);
				$box_top = $this->text_box ($text_top, $font_size_top, $font);
				
				if ($rand) {
					
					$text_top = lisas_ucfirst ($text_top);
					
					$x_top = 10 + rand (0, 200);
					$y_top = 15 + (($font_size_top + 15) * $i);
					
				} else {
					
					$x_top = ceil (($this->data['width'] - $box_top[2]) / 2);
					$y_top = (($font_size_top + 15) * $i);
					
				}
				
				$this->text ($text_top, $font_size_top, $font, $x_top, $y_top, $text_color, 0, $thickness, $text_color_c); // Текст
				
				$text_top_array[] = $text_top;
				
			}
			
			$i = 0;
			$bottom_count = intval_correct ((count ($text_bottoms) - 1), 1);
			
			if (count ($text_bottoms) == 1)
			$i3 = 0;
			else
			$i3 = $bottom_count;
			
			$pos = [];
			
			foreach ($text_bottoms as $text_bottom) { // Низ
				++$i;
				
				$text_bottom = str_correct ($text_bottom);
				$y_bottom = ($this->data['height'] + 30) - (($font_size_bottom + 20) * $i);
				
				$pos[$i3] = [$y_bottom];
				$text_bottom_array[] = $text_bottom;
				
				--$i3;
				
			}
			
			foreach ($text_bottom_array as $id => $text) {
				
				$box_bottom = $this->text_box ($text, $font_size_bottom, $font);
				$x_bottom = ceil (($this->data['width'] - $box_bottom[2]) / 2);
				
				$this->text ($text, $font_size_bottom, $font, $x_bottom, $pos[$id][0], $text_color, 0, $thickness, $text_color_c); // Текст
				
			}
			
		}
		
		function text_x_right ($word, $font_size, $font) {
			
			$box = $this->text_box ($word, $font_size, $font);
			
			$width = abs ($box[4] - $box[0]);
			return ($this->data['width'] - $width);
			
		}
		
		function text_y_bottom ($word, $font_size, $font) {
			
			$box = $this->text_box ($word, $font_size, $font);
			
			$width = abs ($box[4] - $box[0]);
			return $this->data['height'];
			
		}
		
		private function height_size ($size, $max_height) {
			return ceil (($this->data['height'] * $size) / $max_height);
		}
		
		function alphabet ($letter, $word, $position = 1, $max_height = 450) {
			
			$font = MASH_DIR.'fonts/impact.ttf';
			
			$text_color_c = [0, 0, 0]; // Обводка
			$text_color = [255, 255, 255]; // Текст
			
			$font_size_top = $this->height_size (100, $max_height);
			$font_size_bottom = $this->height_size (35, $max_height);
			
			$padding = 10;
			
			$letter = str_correct (url_decode ($letter));
			$word = str_correct (url_decode ($word), ['str_cut_length' => 20, 'str_cut_sep' => '']);
			
			if ($position == 2) { // Буква справа
				
				$x_top = ($this->text_x_right ($letter, $font_size_top, $font) - $this->height_size (($padding * 2), $max_height));
				$x_bottom = $this->height_size (($padding * 2), $max_height);
				
			} else {
				
				$x_top = $this->height_size (($padding + 5), $max_height);
				$x_bottom = ($this->text_x_right ($word, $font_size_bottom, $font) - $this->height_size (($padding * 2), $max_height));
				
			}
			
			$y_top = ($font_size_top + $this->height_size ((($padding * 2) + 5), $max_height));
			$y_bottom = ($this->data['height'] - $this->height_size ((($padding * 2) + 5), $max_height));
			
			$this->text ($letter, $font_size_top, $font, $x_top, $y_top, $text_color, 0, 2, $text_color_c);
			$this->text ($word, $font_size_bottom, $font, $x_bottom, $y_bottom, $text_color, 0, 2, $text_color_c);
			
		}
		
		function demotivator ($data) {
			
			$font = MASH_DIR.'fonts/times.ttf';
			
			// Размер черного прямоугольника, который будем рисовать
			
			$tx = ($this->data['width'] * 0.1);
			$ty = ($this->data['width'] * 0.1);
			
			$bx = ($this->data['width'] + $tx);
			$by = ($this->data['height'] + $ty);
			
			$dx = ($this->data['width'] * 0.01); // Смещение. Необходимо для рисования рамки
			$dy = ($this->data['width'] * 0.01);
			
			// Создаем новое изображение
			
			$width = ($bx + $tx);
			$height = ($by + $tx * 2.6);
			
			$this->create ($width, $height);
			$black = ImageColorAllocate ($this->data['image'], 0, 0, 0);
			
			// Масштабирование
			imageCopyResized ($this->data['image'], $this->data['src'], $tx, $ty, 0, 0, $bx - $tx, $this->data['height'], $this->data['width'], $this->data['height']);
			
			// Расчет смещений для рисования рамки
			
			$y1 = $ty; // Верх
			$x2 = $bx; // Право
			$y2 = ($this->data['height'] + $ty); // Низ
			$x1 = ($tx); // Лево
			
			$col = $this->color (255, 255, 255); // Цвет слоганов
			$i_col = $this->color (255, 255, 255); // Цвет рамки
			
			// Рамки на изображении
			
			ImageRectangle ($this->data['image'], ($x1 - 5), ($y1 - 5), ($x2 + 5), ($y2 + 5), $i_col);
			ImageRectangle ($this->data['image'], ($x1 - 4), ($y1 - 4), ($x2 + 3), ($y2 + 4), $i_col);
			
			$data[0][0] = str_correct ($data[0][0]);
			$data[1][0] = str_correct ($data[1][0]);
			
			// Пишем слоганы, сначала с X = 0, чтобы получить линейные размеры текста
			$s1 = imageTTFText ($this->data['image'], (0.06 * $bx), 0, $dx, ($by + $ty), $col, $font, $data[0][0]);
			$s2 = imageTTFText ($this->data['image'], (0.035 * $bx), 0, $dx, ($by + $ty + 0.08 * $bx), $col, $font, $data[1][0]);
			
			// 1 слоган не помещается в картинку - обрезаем
			//if (($s1[2] - $s1[0]) > $bx + $tx)
			
			$dx = (($bx + $tx) - ($s1[2] - $s1[0])) / 2; // Смещение. Эта величина определяет центровку текста для 1-го слогана
			
			$padding = 8;
			
			// 1 слоган
			
			imageFilledRectangle ($this->data['image'], 0, ($y2 + $padding), ($bx + $tx), ($by + $tx * 2.8), $black);
			imageTTFText ($this->data['image'], (0.06 * $bx), 0, $dx, ($by + 1.1 * $ty + $padding), $col, $font, $data[0][0]);
			
			$dx = ((($bx + $tx) - ($s2[2] - $s2[0])) / 2); // Смещение для 2-го слогана
			
			// 2 слоган
			
			if ($dx < 0) {
				
				// Текст не умещается в картинку, масштабируем
				
				$s = ($s2[2] - $s2[0]);
				$size = (0.035 * $bx * $bx) / $s;
				$s2 = imageTTFText ($this->data['image'], $size, 0, $dx, ($by + $ty + 0.08 * $bx), $col, $font, $data[1][0]);
				
				$dx = (($bx + $tx) - ($s2[2] - $s2[0])) / 2;
				
				imageFilledRectangle ($this->data['image'], 0, ($by + 1.2 * $tx), ($bx + $tx), ($by + $tx * 2.6), $black);
				imageTTFText ($this->data['image'], $size, 0, $dx, ($by + $ty + 0.08 * $bx), $col, $font, $data[1][0]);
				
			} else	{
				
				$size = (0.035 * $bx);
				
				imageFilledRectangle ($this->data['image'], 0, ($by + 1.4 * $tx + $padding), ($bx + $tx), ($by + $tx * 2.3), $black);
				imageTTFText ($this->data['image'], $size, 0, $dx, ($by + $ty + 0.08 * $bx + $padding), $col, $font, $data[1][0]);
				
			}
			
			//header ('Content-type: image/jpeg');
			$this->data['src'] = $this->data['image'];
			
			$this->data['width'] = $width;
			$this->data['height'] = $height;
			
		}
		
	}
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Работа с изображениями
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	function is_image ($image) {
		
		$info = @getimagesize ($image);
		if ($info[2]) $result = $info[2]; else $result = false;
		
		return $result;
		
	}
	
	function create_image ($image) {
		
		$type = is_image ($image);
		
				if ($type == 1) $image = imagecreatefromgif ($image);
		elseif ($type == 2) $image = imagecreatefromjpeg ($image);
		elseif ($type == 3) $image = imagecreatefrompng ($image);
		else echo 'create_image (): '.$image.' is incorrect image type!';
		
		return $image;
		
	}
	
	function imagestringright ($c, $len1 = 86, $len2 = 5) { // Если вы пишите на картинке через imagestring (), эта функция сдвигает текст вправо до упора (до $len1).
		$output = $len1 - (lisas_strlen ($c) * $len2);
		return $output;
		
	}
	
	function image_text ($str, $font_size = 8, $rgb = [0, 0, 0], $font_file = '') {
		
		if (!$font_file) $font_file = __DIR__.'/../fonts/arial.ttf';
		
		//$font = imageloadfont ($font_file);
		
		$bbox = imagettfbbox ($font_size, 0, $font_file, $str);
		//print_r ($bbox);
		
		$font = $font_size;
		
		$width = ($bbox[4] + $font_size);
		$height = ($bbox[1] + $font_size);
		
		//380 = 19 * 20
		//$str = imagefontheight ($font);
		
		$img = @imagecreatetruecolor ($width, $height)
		or
		die ('Cannot Initialize new GD image stream');
		
		imagesavealpha ($img, true);
		$trans_colour = imagecolorallocatealpha ($img, 0, 0, 0, 127);
		imagefill ($img, 0, 0, $trans_colour);
		
		list ($r, $g, $b) = $rgb;
		$text_color = imagecolorallocate ($img, $r, $g, $b);
		
		imagettftext ($img, $font_size, 0, 0, $font_size, $text_color, $font_file, $str);
		
		imagepng ($img);
		imagedestroy ($img);
		
		//@header ('Content-Type: image/png');
		
	}
	
	function imagestringcentered ($img, $font, $cy, $text, $color) {
		
		while (strlen ($text) * imagefontwidth ($font) > imagesx ($img))
		if ($font > 1) $font--; else break;
		
		imagestring ($img, $font, imagesx ($img) / 2 - strlen ($text) * imagefontwidth ($font) / 2, $cy, $text, $color);
		
	}
	
	function write_pic () {
		
		$pic = imagecreatefrompng ($this->getRootDir ().'/templates/image/1px.png');
		$color = imagecolorallocate ($pic, 250, 0, 0);
		$w = 220; $h = 260;
		imagettftext ($pic, 26, 0, $w, $h, $color, "Times", "Simona");
		
	}
	
	function imageheader ($file) {
		
		$type = get_filetype ($file);
		
		if ($type == 'jpg' or $type == 'jpeg' or $type == 'jpe')
		@header ('Content-type: image/jpeg');
		elseif ($type == 'png')
		@header ('Content-type: image/png');
		elseif ($type == 'gif')
		@header ('Content-type: image/gif');
		else die ($file.' is unknown image type!');
		
	}
	
	function image_css_resize ($size, $width, $height) { // Получает корректную строку в формате CSS размеров изображения $image, если ее размеры больше $width или $height
		
		$string = [];
		$output = [];
		
		if ($height > $width or $height == $width) {
			
			$string[0] = 'width:'.$size.'px;';
			$output[0] = $size;
			
		} elseif ($width > $height or $height == $width) {
			
			$string[1] = 'height:'.$size.'px;';
			$output[1] = $size;
			
		}
		
		return [$string[0], $string[1], $output[0], $output[1]];
		
	}
	
	function _image_resize_width ($size, $width, $height, $num = 0) {
		return floor ($width * ($size / $height)) + $num;
	}
	
	function _image_resize_height ($size, $width, $height, $num = 0) {
		return floor ($height * ($size / $width)) + $num;
	}
	
	function image_resize ($size, $width, $height) {
		
		$new_width2 = 0;
		$new_height2 = 0;
		
		$size = make_array ($size);
		
		if (($size[0] and $width < $size[0]) or ($size[1] and $height < $size[1])) {
			
			$new_width = $width;
			$new_height = $height;
			
		} elseif ((isset ($size[0]) and isset ($size[1]) and !$size[1]) or (!isset ($size[1]) and $width > $height)) { // Принудительно по ширине (150x0)
			
			$new_width = $size[0];
			$new_height = _image_resize_height ($size[0], $width, $height);
			
		} elseif (isset ($size[0]) and isset ($size[1]) and !$size[0]) { // Принудительно по высоте (0x150)
			
			$new_width = _image_resize_width ($size[1], $width, $height);
			$new_height = $size[1];
			
		} elseif (!isset ($size[1])) { // По высоте или по ширине (150)
			
			if ($width > $height) { // Ширина больше высоты
				
				$new_width = _image_resize_width ($size[0], $width, $height);
				$new_height = $size[0];
				
			} elseif ($height > $width) { // Высота больше ширины
				
				$new_width = $size[0];
				$new_height = _image_resize_height ($size[0], $width, $height);
				
			} elseif ($width == $height) { // Равные
				
				$new_width = $size[0];
				$new_height = $size[0];
				
			}
			
		} elseif ($size[0] and $size[1]) { // Принудительно (150x150)
			
			if ($size[0] > $width) { // Ширина больше исходной
				
				$new_width = $size[0];
				$new_height = _image_resize_height ($size[0], $width, $height);
				
			} elseif ($size[1] > $height) { // Высота больше исходной
				
				$new_width = _image_resize_width ($size[1], $width, $height, 1);
				$new_height = $size[1];
				
			} elseif ($size[0] < $width) { // Ширина меньше исходной
				
				$new_width = $size[0];
				$new_height = _image_resize_height ($size[0], $width, $height, 1);
				
			} elseif ($size[1] < $height) { // Высота меньше исходной
				
				$new_width = _image_resize_width ($size[1], $width, $height);
				$new_height = $size[1];
				
			} else {
				
				$new_width = $size[0];
				$new_height = $size[1];
				
			}
			
			$new_width2 = $size[0];
			$new_height2 = $size[1];
			
		}
		
		return [$new_width, $new_height, $new_width2, $new_height2];
		
	}
	
	function hex2rgb ($hex) {
		return [
			hexdec (substr ($hex, 1, 2)),
			hexdec (substr ($hex, 3, 2)),
			hexdec (substr ($hex, 5, 2)),
		];
	}
	
	function rgb2shade ($rgb, $type) {
		
		$perc = 7.5;
		
		if ($type == 'lighter')
		$newShade = [
			
			255 - (255 - $rgb[0]) + $perc,
			255 - (255 - $rgb[1]) + $perc,
			255 - (255 - $rgb[2]) + $perc,
			
		];
		else
		$newShade = [
			
			$rgb[0] - $perc,
			$rgb[1] - $perc,
			$rgb[2] - $perc,
			
		];
		
		return $newShade;
		
	}
	
	function get_ico_info ($image_file) {
		
		$fp = fopen ($image_file, 'rb');
		$data = fread ($fp, 22);
		
		$header_format =
		'AReserved1/' . # Get the first 6 bytes
		'A6Version/' . # Get the first 6 bytes
		'CWidth/' .	 # Get the next 2 bytes
		'CHeight/' .	# Get the next 2 bytes
		'CColors/' .		# Get the next 1 byte
		'@11/' .			 # Jump to the 12th byte
		'AReserved2/' .			 # Jump to the 12th byte
		'CPlanes/' .			 # Jump to the 12th byte
		'C2BitsPerPixel/' .			 # Jump to the 12th byte
		'C1Aspect';		# Get the next 1 byte
		
		fclose ($fp);
		
		return unpack ($header_format, $data);
		
	}