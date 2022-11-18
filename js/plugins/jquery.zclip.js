/*
 zclip 1.0 - jQuery plugin for ZeroClipboard library
 written by O! Interactive, Acuna
 http://ointeractive.ru
 
 Copyright (c) 2016 O! Interactive, Acuna (http://ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 1.0  08.06.2016
  Первый приватный релиз
  
 */
	
	(function ($) {
		
		$.fn.zclip = function (options) {
			
			options = $.extend ({
				
				'swfPath': 'ZeroClipboard.swf',
				
				'ready': function () {},
				'afterCopy': function () {},
				'error': function () {},
				'noflash': function () {},
				
				'text': '',
				
			}, options);
      
			$(this).each (function () {
				
				ZeroClipboard.config ({
					
					'swfPath': options['swfPath'],
					
				});
				
				var that = $(this), client = new ZeroClipboard (that);
				
				if ($.type (options['text']) == 'function')
				options['text'] = options['text'](that, client);
				
				options['text'] = options['text'].toString ();
				
				client.setText (options['text']);
				
				client.on ('ready', function (client) {
					options['ready'](client, that);
				});
				
				client.on ('afterCopy', function (event) {
					options['afterCopy'](event.data['text/plain'], that);
				});
				
				client.on ('error', function (event) {
					options['error'](event, that);
				});
				
				that.on ('click', function (e) {
					
					//e.preventDefault ();
					
					var hasFlash = function () {
						
						if (navigator.plugins && navigator.plugins.length && navigator.plugins['Shockwave Flash'])
						return true;
						else if (navigator.mimeTypes && navigator.mimeTypes.length) {
							
							var mimeType = navigator.mimeTypes['application/x-shockwave-flash'];
							return mimeType && mimeType.enabledPlugin;
							
						} else {
							
							try {
								
								var ax = new ActiveXObject ('ShockwaveFlash.ShockwaveFlash');
								return true;
								
							} catch (e) {}
							
						}
						
						return false;
						
					};
					
					if (!hasFlash ()) {
						options['noflash'](options['text']);
					}
					
				});
				
			});
			
		};
		
	})($);