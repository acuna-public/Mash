/*
 checkField 1.0 - jQuery plugin
 written by O! Interactive, Acuna
  https://ointeractive.ru
 
 Copyright (c) 2015 O! Interactive, Acuna (http://ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 Dependencies: jquery.prettyAjax
 
 1.0  01.04.2015
  Первый приватный релиз
  
 */
  
  (function ($) {
    
    $.fn.checkField = function (options) {
      
      options = $.extend ({
        
        'method': 'get',
        'file': '',
        'data': {},
        'action': 'blur',
        'minLength': 4,
        'minLengthError': '',
        'success': function () {},
        'area': '',
        
      }, options);
      
      $(this).each (function () {
        
        var self = $(this);
        
        self.on (options.action, function () {
          
          var val = $(this).val ();
          
          if (options.minLength) {
            
            if (val.length > 0 && val.length <= options.minLength && options.minLengthError) {
              
              var error = options.minLengthError;
              $(options.area).show ().html (error.replace ('%min_length%', options.minLength));
              
              if (options.fieldErrorClass)
              $(this).addClass (options.fieldErrorClass);
              
            } else {
              
              $(options.area).hide ();
              
              if (options.fieldErrorClass)
              $(this).removeClass (options.fieldErrorClass);
              
            }
            
          } else if (options.file) $.ajax ({
            
            'method': options.method,
            'url': options.file,
            'data': options.data,
            
          });
          
          options.success (val);
          
        });
        
      });
      
    };
    
  })($);