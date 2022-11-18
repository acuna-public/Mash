/*
 prettyScroll 1.0 - jQuery extension
 written by O! Interactive, Acuna
 http://ointeractive.ru
 
 Copyright (c) 2015 O! Interactive, Acuna (http://ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 1.0  27.05.2015
  Первый приватный релиз
  
 */
	
	(function ($) {
		
		$.scroll = function (options) {
			
			options = $.extend ({
				
        'timeOut': 500,
				'start': function () {},
				'stop': function () {},
				
			}, options);
      
      function scroll () {
        
        $(window).resize ();
        options.start (this);
        
        $(this).off ('scroll')[0].setTimeout (function () {
          
          $(this).on ('scroll', scroll);
          options.stop (this);
          
        }, options['timeOut']);
        
      };
      
      $(window).on ('scroll', scroll);
      
		};
		
	})($);