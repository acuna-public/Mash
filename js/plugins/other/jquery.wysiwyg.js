/*
 wysiwyg 1.0 - jQuery plugin
 written by O! Interactive, Acuna
 http://ointeractive.ru
 
 Copyright (c) 2015 O! Interactive, Acuna (http://ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 1.0  08.09.2015
  Первый приватный релиз
  
 */
	
	(function ($) {
		
		$.fn.wysiwyg = function (options) {
			
			options = $.extend ({
				
				'theme': 'default',
				'lang': { 'b':'Жирный', 'i':'Курсив', 'u':'Подчеркнутый', 's':'Зачеркнутый', },
        
			}, options);
      
			$(this).each (function () {
				
        var self = $(this);
        
        self.wrap ('<div class="wysiwyg ' + options['theme'] + '"></div>');
        self.before ('<ul class="controls"></ul>');
        
        var button = $('.controls');
        
        button.append ('<li class="color"><select id="color">'
+ '<optgroup><option value="red" style="color:red;">Красный</option></optgroup></select></li>');
        
        $.each (options['lang'], function (key, value) {
          button.append ('<li class="' + key + '"><a href="#" title="' + value + '">' + key + '</a></li>');
        });
        
        button.find ('li').each (function () {
          
          $(this).on ('click', function () {
            
            alert ($(this).text ());
            return false;
            
          });
          
        });
        
			});
			
		};
		
	})($);