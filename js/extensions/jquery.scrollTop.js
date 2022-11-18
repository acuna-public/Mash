/*
 scrollTop 1.0 - jQuery extension
 written by O! Interactive, Acuna
 http://ointeractive.ru
 
 Copyright (c) 2014 O! Interactive, Acuna (http://ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 1.0  27.05.2015
  Первый приватный релиз
  
 */
	
	(function ($) {
		
		$.scrollTop = function ($options) {
			
			$options = $.extend ({
				
        'fadeTime': 1000,
        'hideTime': 3000,
        'scrollTime': 800,
        
				'start': function () {},
				'stop': function () {},
				
			}, $options);
      
      $('body').append ('<div class="scrollUp"></div>');
      var $elem = $('.scrollUp');
      
      var hideScroll = function () {
        $elem.fadeOut ($options['fadeTime']);
      };
      
      var showScroll = function () {
        
        $elem.fadeIn ($options['fadeTime']);
        
        setTimeout (function () {
          hideScroll ();
        }, $options['hideTime']);
        
      };
      
      $.scroll ({
        
        'start': function () {
          $options.start (this);
        },
        
        'stop': function () {
          $options.stop (this);
          showScroll ();
        },
        
      });
      
      $elem.click (function () {
        
        $('html,body').animate ({
          'scrollTop': 0
        }, $options['scrollTime']);
        
      });
      
		};
		
	})($);