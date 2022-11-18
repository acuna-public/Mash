/*
 FastSearch 1.2 - jQuery plugin
 written by O! Interactive, Acuna
 http://ointeractive.ru
 
 Copyright (c) 2014 - 2015 O! Interactive, Acuna (http://ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 1.0  23.11.2014
  First private release
  
 1.1  29.11.2014
  + Bind action added
  
 1.2  31.03.2015
  Some bugs fixed
	
 */
	
	(function ($) {
		
		$.fn.fastSearch = function (options) {
			
			options = $.extend ({
				
				method: 'get',
				file: '',
				data: {},
				action: 'keyup',
				beforeSend: function () {},
				complete: function () {},
				result: '',
				delay: 0,
				minLength: 3,
				addElem: '',
				closeOnFade: true,
				hideOverflow: true,
				
			}, options);
			
			var that = this, js_vars = [], opened = 0;
			
			if (that.length > 0) {
				
				that.attr ('autocomplete', 'off');
				
				$('body').click (function (e) {
					
					if ($(e.target).closest (options.result).length === 0)
					close_window ();
					
				});
				
				if (options.closeOnFade) $(options.result).hide ();
				
				var close_window = function () {
					
					if (options.closeOnFade) {
						
						$(options.result).fadeOut ();
						//if (options.hideOverflow) $('html,body').css ({ 'overflow':'auto' });
						
					}
					
				};
				
				var show = function (elem) {
					
					var value = '';
					if (elem) value = $(elem).val ();
					
					if ((js_vars['search_value'] != value && value.length >= options.minLength) || !elem) {
						
						clearInterval (js_vars['search_delay']);
						
						js_vars['search_delay'] = setInterval (function () {
							
							clearInterval (js_vars['search_delay']);
							
							options.beforeSend (that, value);
							options.data['query'] = value;
							
							$.ajax ({
								
								method: options.method,
								url: options.file,
								data: options.data,
								
							}).done (function (c) {
								
								$(options.result).html (c).fadeIn ();
								//.position ({ my:'left top', at:'left bottom', of:that, collision:'fit flip', });
								
								var title = $(options.result).find ('.elem-title');
								
								if (title.text ().length)
								title.css ('width', title.text ().length);
								
								options.complete (options.result, options.addElem);
								if (options.hideOverflow) $('html,body').css ({ 'overflow':'hidden' });
								
							});
							
							js_vars['search_value'] = value;
							
						}, options.delay);
						
						opened = 1;
						
					} else close_window ();
					
				};
				
				if (options.action == 'keyup')
				that.on (options.action, function () {
					show (this);
				});
				else show ();
				
			}
			
		};
		
	})(jQuery);