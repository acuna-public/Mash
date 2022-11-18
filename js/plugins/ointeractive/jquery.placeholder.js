/*
 Placeholder 1.0 - jQuery plugin
 written by O! Interactive, Acuna
 http://ointeractive.ru
 
 Copyright (c) 2014 O! Interactive, Acuna (http://ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 Dependencies: none
 
 */
  
  (function ($) {
    
    $.fn.placeholder = function (options) {
      
      options = $.extend ({
        
        'style': 'placeholder',
        
      }, options);
      
      $(this).each (function () {
        
        var
        self = $(this),
        value = self.attr ('placeholder');
        
        if (!value) value = '';
        
        if (value) {
          
          self.val (value).addClass (options.style);
          
          self.removeAttr ('placeholder');
          
          var showHolder = function () {
            
            if (self.val () == '') {
              
              self.addClass (options.style);
              self.val (value);
              
            }
            
          };
          
          var removeHolder = function () {
            
            self.removeClass (options.style);
            if (self.val () == value) self.val ('');
            
          };
          
          self.click (function () {
            removeHolder ();
          }).focus (function () {
            removeHolder ();
          }).blur (function () {
            showHolder ();
          });
          
        }
        
      });
      
    };
    
  })($);