/*
 myFunction 1.0 - jQuery plugin
 written by O! Interactive, Acuna
 http://ointeractive.ru
 
 Copyright (c) 2014,2016 O! Interactive, Acuna (http://ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 1.0  16.11.2014
  Первый приватный релиз
  
 */

(function ($) {
  
  $.fn.myFunction = function (options) {
    
    options = $.extend ({
      
      'action': 'keyup',
      'success': function () {},
      
    }, options);
    
    $(this).each (function () {
      
      $(this).on (options['action'], function (event) {
        options.success ($(this));
      });
      
    });
    
  };
  
})($);