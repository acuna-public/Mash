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
 -- Аудио
 ========================================
*/
	
	if (!defined ('MASH')) die ('File must be started only through the main framework cover');
	
	function riplog_to_unicode ($content) {
		
		if (mb_detect_encoding ($content) != 'ASCII' and mb_detect_encoding ($content) != 'UTF-8')
		$content = to_unicode ($content, 'UCS-2LE');
		
		return $content;
		
	}
	
	function riplog2array ($file, $type = 'audio') {
		
		$data = [];
		
		if ($content = trim (file_get_contents ($file)))
		switch ($type) {
			
			case 'audio':
				
				$content = riplog_to_unicode ($content);
				
				if ($content) {
					
					$content = str_replace (["\r"], [''], $content);
					$content = preg_replace ('~={2,}~', '', $content);
					//debug ($content);
					
					preg_match ('~Exact Audio Copy (.+) from~i', $content, $match1);
					preg_match ('~EAC extraction logfile from (.+)~i', $content, $match2);
					
					if ($match1 and $match2) {
						
						$data['ripper'] = 'Exact Audio Copy';
						$data['ripper_type'] = 'eac';
						$data['version'] = trim ($match1[1]);
						
					} elseif ($match2) {
						
						$data['ripper'] = 'Exact Audio Copy';
						$data['ripper_type'] = 'eac_pre99';
						$data['version'] = 'prebeta 9';
						
					}
					
					if ($data['ripper_type'] == 'eac')
					$match = $match2;
					elseif ($data['ripper_type'] == 'eac_pre99')
					preg_match ('~EAC extraction logfile from (.+) for (.+)~i', $content, $match);
					
					if ($match) $data['English'] = 'yes'; else $data['English'] = 'no';
					$data['log_date'] = trim ($match[1]);
					
					preg_match ('~(Used drive)\s+:\s+(.+)~i', $content, $match);
					preg_match ('~(.+)\s+Adapter:\s+([0-9]+)\s+ID:\s+([0-9]+)~i', $match[2], $match2);
					
					$data[$match[1]]['name'] = trim ($match2[1]);
					$data[$match[1]]['Adapter'] = trim ($match2[2]);
					$data[$match[1]]['ID'] = trim ($match2[3]);
					
					if ($data['ripper_type'] == 'eac') {
						
						$rows = [
							
							'read' => ['Read mode', 'Utilize accurate stream', 'Defeat audio cache', 'Make use of C2 pointers'],
							
							'offset' => ['Read offset correction', 'Overread into Lead-In and Lead-Out', 'Fill up missing offset samples with silence', 'Delete leading and trailing silent blocks', 'Null samples used in CRC calculations', 'Used interface', 'Gap handling'],
							
							'output' => ['Used output format', 'Selected bitrate', 'Quality', 'Add ID3 tag', 'Command line compressor', 'Additional command line options']
							
						];
						
						foreach ($rows as $type => $rows)
						foreach ($rows as $row) {
							
							preg_match ('~'.$row.'\s*:\s+(.+)~i', $content, $match);
							$data[$type][$row] = trim ($match[1]);
							
						}
						
						preg_match_all ('~([0-9]+)\s+\|\s+(.+)\s+\|\s+(.+)\s+\|\s+(.+)\s+\|\s+(.+)~', $content, $match);
						
						for ($i = 0; $i < count ($match[0]); ++$i) {
							
							$start = explode ('.', $match[2][$i]);
							$length = explode ('.', $match[3][$i]);
							
							$data['toc'][add_zero ($match[1][$i])] = [
								
								'start' => trim ($match[2][$i]),
								'length' => trim ($match[3][$i]),
								'start_show' => trim ($start[0]),
								'length_show' => trim ($length[0]),
								'start_sector' => trim ($match[4][$i]),
								'end_sector' => trim ($match[5][$i]),
								
							];
							
						}
						
						if (preg_match_all ('~Track\s+([0-9]+)\s+(.+)\s+\(confidence\s+([0-9]+)\)\s+\[(.+)\]\s+\(AR\s+v([0-9]+)\)~i', $content, $match))
						for ($i = 0; $i < count ($match[0]); ++$i) {
							
							$data['accurate_rip'][$match[1][$i]] = [
								
								'status' => trim ($match[2][$i]),
								'confidence' => trim ($match[3][$i]),
								'ar_version' => trim ($match[5][$i]),
								
							];
							
							preg_match ('~(.+)\]\,\s+(.+)\s+\[(.+)~', $match[4][$i], $match2);
							
							if ($match2) {
								
								$rip_id = $match2[1];
								$data['accurate_rip'][$match[1][$i]]['returned_rip_id'] = trim ($match2[3]);
								
							} else {
								
								$rip_id = $match[4][$i];
								$data['accurate_rip'][$match[1][$i]]['status'] = trim ($match[2][$i]);
								
							}
							
							$data['accurate_rip'][$match[1][$i]]['rip_id'] = trim ($rip_id);
							
						} elseif (preg_match_all ('~Track\s+([0-9]+)\s+(.+)~i', $content, $match))
						for ($i = 0; $i < count ($match[0]); ++$i)
						$data['accurate_rip'][add_zero ($match[1][$i])] = [
							
							'status' => trim ($match[2][$i]),
							
						];
						
						$rows = ['Filename', 'Peak level', 'Extraction speed', 'Range quality', 'Test CRC', 'Copy CRC'];
						
						foreach ($rows as $row) {
							
							preg_match ('~'.$row.'\s+(.+)~i', $content, $match);
							$data[$row] = trim ($match[1]);
							
						}
						
						$data['ctdb'] = [];
						
						if (preg_match ('~\[CTDB\s+TOCID:\s+(.+)\]\s+(.+)~i', $content, $match)) {
							
							$data['ctdb']['toc_id'] = trim ($match[1]);
							$data['ctdb']['status'] = trim ($match[2]);
							
							preg_match ('~Submit\s+result:\s+(.+)~i', $content, $match2);
							
							$data['ctdb']['uploaded_status'] = str_replace ($data['ctdb']['toc_id'].' ', '', trim ($match2[1]));
							
							$data['ctdb']['results'] = [];
							
							if (preg_match ('~Track\s+\|\s+CTDB\s+Status(.+)\n{2,}~si', $content, $match2))
							//if (preg_match_all ('~\[(.+)\]\s+\(([0-9]+)/([0-9]+)\)\s+(.+)~', $content, $match2[1]))
							if (preg_match_all ('~([0-9]+)\s+\|\s+\((.+?)\)\s+(.+)~', $match2[1], $match))
							for ($i = 0; $i < count ($match[0]); ++$i)
							$data['ctdb']['results'][$i] = [
								
								'id' => trim ($match[1][$i]),
								'attempt' => trim ($match[2][$i]),
								'status' => trim ($match[3][$i]),
								
							];
							
						}
						
						preg_match ('~Log checksum\s+(.+)~i', $content, $match);
						$data['Log checksum'] = trim ($match[1]);
						
					} elseif ($data['ripper_type'] == 'eac_pre99') {
						
						preg_match ('~(Read mode)\s+:\s+(.+)\,\s+(.+)\,\s+(.+)~i', $content, $match);
						
						$explode = explode (' with ', $match[2]);
						//print_r ($match);
						
						$data['read'][$match[1]] = trim ($explode[0]);
						
						$data['read']['Utilize accurate stream'] = (($match[3] == 'accurate stream') ? 'Yes' : 'No');
						$data['read']['Defeat audio cache'] = (($match[4] == 'disable cache') ? 'Yes' : 'No');
						
						if ($explode[1] == 'NO C2') $value = 'No'; else $value = 'Yes';
						$data['read']['Make use of C2 pointers'] = $value;
						
						preg_match ('~(Used output format)\s+:\s+(.+)\s{3}\((.+)\)~i', $content, $match);
						$data['output'][$match[1]] = trim ($match[3]);
						$data['output']['Command line compressor'] = trim ($match[2]);
						
						preg_match ('~([0-9]+)\s+kBit/s~i', $content, $match);
						$data['output']['Selected bitrate'] = trim ($match[1]);
						
						preg_match ('~(Additional command line options)\s+:\s+(.+)~i', $content, $match);
						$data['output'][$match[1]] = trim ($match[2]);
						
						$rows = ['Read offset correction', 'Overread into Lead-In and Lead-Out', 'Fill up missing offset samples with silence', 'Delete leading and trailing silent blocks'];
						
						foreach ($rows as $row) {
							
							preg_match ('~'.$row.'\s+:\s+(.+)~i', $content, $match);
							$data['offset'][$row] = trim ($match[1]);
							
						}
						
						$rows = ['Peak level', 'Range quality', 'CRC'];
						
						foreach ($rows as $row) {
							
							preg_match ('~'.$row.'\s+(.+)~i', $content, $match);
							$data[$row] = trim ($match[1]);
							
						}
						
					}
					
					preg_match_all ('~End of status report~i', $content, $match);
					$data['logs_count'] = count ($match[0]);
					
				}
				
			break;
			
		}
		
		//print_r ($data);
		
		return $data;
		
	}
	
	//print_r (riplog2array (dirname (__FILE__).'/Blood Stronghold - From Sepulchral Remains....log'));
	
	function riplogcheck ($full_file, $type = 'audio') {
		
		$error = [];
		
		switch ($type) {
			
			case 'audio':
				
				$file = explode_filename ($full_file);
				
				if (!is_file ($full_file)) $error[1] = 'File '.$full_file.' not found!';
				elseif ($file['exp'] != 'log') $error[2] = 'Filetype of '.$full_file.' must be ".log".';
				else {
					
					$data = riplog2array ($full_file, $type);
					
					switch ($data['ripper_type']) {
						
						default: $error[3] = 'Ripper is not supported.'; break;
						
						case 'eac': case 'eac_pre99':
							
							if ($data['English'] != 'yes') $error[4] = 'Log language must be English.';
							
							if ($data['Used drive']) {
								
								$virtual_drives = ['DTSOFT	BDROM', 'DiscSoftVirtual'];
								if (in_array ($data['Used drive']['name'], $virtual_drives)) $error[5] = 'Virtual drive used.';
								
							} else $error[6] = 'Could not verify used drive.';
							
							if ($read_mode = $data['read']['Read mode']) {
								
								$read_modes = ['Secure'];
								
								if (!in_array ($read_mode, $read_modes))
								$error[7] = $read_mode.' read mode not recommended. Use '.implode (' or ', $read_modes).' read mode instead.';
								
							} else $error[8] = 'Could not verify read mode.';
							
							if (!$data['offset']['Read offset correction']) $error[9] = 'Read offset correction cannot be zero.';
							
							if ($data['offset']['Gap handling'] == 'Not detected') $error[10] = 'Gap handling must be detected or null.';
							
							if ($data['ripper_type'] == 'eac') {
								
								if (!$data['Test CRC'] or !$data['Copy CRC']) $error[11] = 'Test and Copy mode must be used.';
								
								if ($data['Test CRC'] != $data['Copy CRC']) $error[12] = 'Test CRC and Copy CRC must be equal.';
								
							}
							
							$ar_types = ['accurately ripped', 'not present in database'];
							
							if ($data['accurate_rip'])
							foreach ($data['accurate_rip'] as $key => $value)
							if (!in_array ($value['status'], $ar_types))
							$error[13][$key] = $value['status'];
							
							if ($data['ctdb']['results'])
							foreach ($data['ctdb']['results'] as $key => $value)
							if ($value['status'] != 'Accurately ripped')
							$error[14][$key] = $value['status'];
							
							if ($data['ripper_type'] == 'eac' and $data['Add ID3 tag'] == 'Yes' and is_file ($file['path'].$file['name'].'.flac'))
							$error[15] = 'ID3 tags should be added only in MP3 files. Use vorbis comments instead.';
							
							$rows = [
								
								'read' => [
									
									'Utilize accurate stream' => 'Yes',
									'Defeat audio cache' => 'Yes',
									'Make use of C2 pointers' => 'No',
									
								],
								
								'offset' => [
									
									'Fill up missing offset samples with silence' => 'Yes',
									'Delete leading and trailing silent blocks' => 'No',
									
								],
								
							];
							
							if ($data['ripper_type'] == 'eac')
							$rows['offset']['Null samples used in CRC calculations'] = 'Yes';
							
							$i = 16;
							
							foreach ($rows as $type => $rows)
							foreach ($rows as $key => $value) {
								
								if ($data[$type][$key] != $value)
								$error[$i] = '"'.$key.'" must be "'.$value.'".';
								
								++$i;
								
							}
							
							if (!$data['Log checksum']) $error[20] = 'Log checksum not set.';
							
							if ($data['logs_count'] > 1) $error[21] = 'There is must be only one log in file.';
							
						break;
						
					}
					
				}
				
			break;
			
		}
		
		return $error;
		
	}
	
	function cue2array ($file) {
		
		$output = [];
		
		if (get_filetype ($file) == 'cue') {
			
			$num = [];
			$lines = file2array ($file);
			
			foreach ($lines as $line) {
				
				if (substr ($line, 0, 3) == 'REM') {
					
					if (substr ($line, 4, 5) == 'GENRE')
					$output['genre'] = strip_quotes (substr ($line, 10));
					if (substr ($line, 4, 4) == 'DATE')
					$output['date'] = trim (substr ($line, 9));
					if (substr ($line, 4, 6) == 'DISCID')
					$output['disc_id'] = trim (substr ($line, 11));
					if (substr ($line, 4, 7) == 'COMMENT')
					$output['comment'] = strip_quotes (substr ($line, 12));
					
				}
				
				if (substr ($line, 0, 9) == 'PERFORMER')
				$output['performer'] = strip_quotes (substr ($line, 10));
				
				if (substr ($line, 0, 5) == 'TITLE')
				$output['title'] = to_unicode (strip_quotes (substr ($line, 6)));
				
				if (substr ($line, 0, 4) == 'FILE') {
					
					$expl = explode ('"', $line);
					$output['file'] = $expl[1];
					
					$output['file_type'] = trim (lisas_strtolower ($expl[2]));
					
				}
				
				$expl1 = explode (' ', substr ($line, 2));
				$expl2 = explode (' ', substr ($line, 4));
				
				if ($expl1[0] == 'TRACK' and $expl1[1])
				$num['track'] = $expl1[1];
				if ($expl2[0] == 'INDEX' and $expl2[1])
				$num['index'] = $expl2[1];
				
				if (substr ($line, 2, 5) == 'TRACK')
				$output['tracks'][$num['track']]['type'] = trim (lisas_strtolower ($expl1[2]));
				
				if (substr ($line, 4, 5) == 'TITLE')
				$output['tracks'][$num['track']]['title'] = strip_quotes (substr ($line, 9));
				
				if (substr ($line, 4, 9) == 'PERFORMER')
				$output['tracks'][$num['track']]['performer'] = strip_quotes (substr ($line, 13));
				
				if (substr ($line, 4, 5) == 'FLAGS')
				$output['tracks'][$num['track']]['flags'] = strip_quotes (substr ($line, 9));
				
				if (substr ($line, 4, 5) == 'INDEX')
				$output['tracks'][$num['track']]['indexes'][$num['index']] = trim ($expl2[2]);
				
			}
			
		}
		
		return $output;
		
	}
	
	function toc2array ($file) {
		
		$output = [];
		
		if (get_filetype ($file) == 'toc') {
			
			$i = 0; $i2 = 0;
			$num = [];
			
			$content = str_replace ("\r", '', file_get_content ($file));
			
			preg_match_all ('#CD_DA#', $content, $data);
			$output['type'] = $data[0][0];
			
			preg_match_all ('#LANGUAGE_MAP \{(.*?)\}#si', $content, $data);
			
			$data = nl_explode (trim ($data[1][0]));
			
			foreach ($data as $data) {
				
				$data = explode (':', $data);
				$output['cd_text']['language_map'][trim ($data[0])] = trim ($data[1]);
				
			}
			
			preg_match_all ('#CD_TEXT \{(.*?)\}#si', $content, $data3);
			//print_r ($data3);
			
			preg_match_all ('#LANGUAGE ([0-9]+) \{(.*?)\}#si', $data3[0], $data);
			//print_r ($data);
			
			preg_match_all ('#"(.*?)"#', $data[2][0], $data2);
			
			$output['cd_text']['language'][$data[1][0]]['performer'] = $data2[1][1];
			$output['cd_text']['language'][$data[1][0]]['title'] = $data2[1][0];
			
			preg_match_all ('#SIZE_INFO \{(.*?)\}#si', $content, $data2);
			//print_r ($data[1]);
			
			$data2 = nl_explode (trim ($data2[1][0]));
			
			$i3 = 0;
			
			foreach ($data2 as $data2) {
				
				$data2 = trim ($data2, ' ..,');
				$data2 = str_replace ('	', ' ', $data2);
				
				$output['cd_text']['size_info'][$i3] = $data2;
				++$i3;
				
			}
			
			preg_match_all ('#// Track [0-9]+#', $content, $data);
			
			$i3 = 0;
			
			foreach ($data[0] as $track) {
				++$i; ++$i3;
				
				$i = add_zero ($i);
				
				preg_match_all ('#TRACK .*#', $content, $data);
				
				$data = explode (' ', $data[0][$i2]);
				$output['tracks'][$i]['options'][] = $data[1];
				
				preg_match_all ('#.*COPY#', $content, $data);
				$output['tracks'][$i]['options'][] = $data[0][$i2];
				
				preg_match_all ('#.*PRE_EMPHASIS#', $content, $data);
				$output['tracks'][$i]['options'][] = $data[0][$i2];
				
				preg_match_all ('#.*TWO_CHANNEL_AUDIO#', $content, $data);
				$output['tracks'][$i]['options'][] = $data[0][$i2];
				
				preg_match_all ('#"(.*?)"#', trim ($line[8]), $data1);
				preg_match_all ('#"(.*?)"#', trim ($line[7]), $data2);
				
				$output['tracks'][$i]['cd_text']['language'][$line2[1]]['performer'] = $data1[1][0];
				$output['tracks'][$i]['cd_text']['language'][$line2[1]]['title'] = $data2[1][0];
				
				preg_match_all ('#FILE .*#', $content, $data);
				preg_match_all ('#".*"#', $data[0][$i2], $data2);
				
				$data = explode (' ', $data[0][$i2]);
				
				$output['tracks'][$i]['file'] = strip_quotes ($data2[0][0]);
				$output['tracks'][$i]['time'] = end ($data);
				$output['tracks'][$i]['time_start'] = prev ($data);
				
				++$i2;
				
			}
			
		}
		
		return $output;
		
	}