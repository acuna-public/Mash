<?php
/*
 ========================================
 Mash Framework (c) 2013, 2020-2021
 ----------------------------------------
 https://mash.ointeractive.ru/
 ========================================
 O! Interactive (support@ointeractive.ru)
 ----------------------------------------
 Класс для работы с электронной почтой
 ========================================
*/
	
	namespace Messenger;
	
	class EMail extends \Messenger {
		
		public
			$langs = ['en', 'de', 'ru', 'et'];
		
		private
			$mail;
		
		function __construct (\Mash $mash) {
			
			parent::__construct ($mash->config);
			
			$this->mash = $mash;
			
			/*require 'phpmailer/Exception.php';
			require 'phpmailer/SMTP.php';
			require 'phpmailer/POP3.php';
			require 'phpmailer/PHPMailer.php';
			
			$this->mail = new \PHPMailer\PHPMailer\PHPMailer ();*/
			
			require 'phpmailer/PHPMailerAutoload.php';
			
			$this->mail = new \PHPMailer ();
			
			$this->mail->Debugoutput = function ($text) {
				debug ($text);
			};
			
			$this->config = array_extend ($mash->config, [
				
				'lang' => 'en',
				'root_dir' => '',
				'http_host' => '',
				'theme_dir' => '',
				'site_title' => '',
				'mail_sender' => '',
				'mail_reply' => '',
				'copy_to' => '',
				'hidden_copy_to' => '',
				'charset' => 'utf-8',
				'mail_wordwrap' => true,
				'mail_method' => 'smtp',
				'smtp_secure' => true,
				'smtp_port' => 465,
				'html' => true,
				
			]);
			
			if (!in_array ($this->config['lang'], $this->langs))
			$this->config['lang'] = 'en';
			
			$this->mail->SetLanguage ($this->config['lang'], dirname (__FILE__).'/phpmailer/language/');
			
			if (!$this->config) throw new \MashException ('Мail config not found');
			
			$this->config['root_dir'] = str_replace ('{home_url}', $this->config['http_host'], $this->config['root_dir']);
			$this->config['theme_dir'] = str_replace ('{home_url}', $this->config['http_host'], $this->config['theme_dir']);
			
			if (!$this->config['mail_sender'])
				$this->config['mail_sender'] = $this->config['smtp_user'];
			
			$this->mail->setFrom ($this->config['mail_sender'], $this->config['site_title']);
			
			if ($this->config['mail_reply']) {
				
				if (!$this->config['reply_name'])
					$this->config['reply_name'] = $this->config['site_title'];
				
				$this->mail->AddReplyTo ($this->config['mail_reply'], $this->config['reply_name']);
				
			}
			
			$this->mail->CharSet = $this->config['charset'];
			$this->mail->WordWrap = (int) $this->config['mail_wordwrap'];
			$this->mail->AltBody = 'To view the message please use an HTML compatible email viewer';
			
			$this->mail->isHTML ($this->config['html']);
			
			if ($this->config['mail_method'] == 'smtp') {
				
				$this->mail->isSMTP ();
				$this->mail->SMTPAuth = true;
				$this->mail->SMTPKeepAlive = true;
				$this->mail->Host = $this->config['smtp_host'];
				$this->mail->Port = $this->config['smtp_port'];
				$this->mail->Username = $this->config['smtp_user'];
				$this->mail->Password = $this->config['smtp_pass'];
				$this->mail->SMTPSecure = $this->config['smtp_secure'];
				
				$this->mail->SMTPOptions = [
					
					'ssl' => [
						
						'verify_peer' => false,
						'verify_peer_name' => false,
						'allow_self_signed' => true,
						
					],
					
				];
				
			}
			
			if ($this->config['copy_to'])
			foreach ($this->config['copy_to'] as $data)
			$this->mail->AddCC ($data[0], $data[1]);
			
			if ($this->config['hidden_copy_to'])
			foreach ($this->config['hidden_copy_to'] as $data)
			$this->mail->AddBCC ($data[0], $data[1]);
			
		}
		
		function getTitle () {
			return 'E-Mail';
		}
		
		function getName () {
			return 'email';
		}
		
		private function prep_file_name ($file) {
			return dash_filepath (str_replace (['{root_dir}', '{THEME}'], [$this->config['root_dir'], $this->config['email_theme_dir']], $file));
		}
		
		function _send (array $data) {
			
			if ($this->debug)
				$this->mail->SMTPDebug = \SMTP::DEBUG_SERVER;
			
			if (!isset ($data['name']))
				$data['name'] = '';
			
			$this->mail->addAddress ($data['email'], $data['name']);
			$this->mail->Subject = $this->parse_text_tags ($data['subject']);
			$this->mail->Body = $data['text'];
			
			if (isset ($data['attach']))
			foreach ($data['attach'] as $file)
			$this->mail->addStringAttachment ($this->prep_file_name ($file));
			
			if (!$this->mail->send ())
				throw new \MashException ($this->mail->ErrorInfo);
			
		}
		
		protected final function load_templ ($tpl_name, $data = []) {
			
			$time_before = $this->real_time ();
			$tpl_target = $this->tpl_path ($tpl_name);
			
			//if (!$tpl_name or !file_exists ($tpl_target))
			//echo 'Шаблон сообщения электронной почты '.$tpl_target.' не найден!';
			
			$template = file_get_content ($tpl_target);
			
			$data['THEME_EMAIL'] = $this->config['theme_url'];
			
			foreach ($data as $find => $replace)
			$template = str_replace ('{'.$find.'}', $replace, $template);
			
			$template = $this->mash->parse_global_tags ($template);
			
			$template = str_replace ('{year_copyright}', $this->mash->year_copyright ($this->mash->config['year_open'], 1), $template);
			
			$this->parse_time += $this->real_time () - $time_before;
			
			return $template;
			
		}
		
		protected function clear () {
			$this->mail->clearAddresses ();
		}
		
		function load ($data, $content) {
			
			$headers = <<<HTML
<title>{$data['subject']}</title>
<meta name="viewport" content="initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="Content-Type" content="text/html; charset={$this->config['charset']}">
HTML;
			
			$template = $this->load_templ ('main', [
				
				'headers' => $headers,
				'content' => $content,
				'date' => $this->mash->date->show (1),
				'receiver_name' => (isset ($data['body_data']['receiver_name']) ? $data['body_data']['receiver_name'] : $data['name']),
				
			]);
			
			return html_minify ($template);
			
		}
		
	}