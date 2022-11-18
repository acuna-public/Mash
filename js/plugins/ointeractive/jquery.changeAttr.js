/*
 changeAttr 1.0 - jQuery plugin
 written by O! Interactive, Acuna
 http://ointeractive.ru
 
 Copyright (c) 2015 O! Interactive, Acuna (http://ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 Dependencies: none
 
 1.0  06.08.2015
  Первый приватный релиз
  
 */
  
  (function ($) {
    
    $.fn.changeAttr = function (options) {
      
      options = $.extend ({
        
        'class': 'selected',
        'nested': false,
        'removeSiblings': true,
        
        'click': function () {},
        'hover': function () {},
        
      }, options);
      
      $(this).each (function () {
        
        var self = $(this);
        
        self.find ('li').click (function () {
          
          var
          elem = $(this),
          dataElem = elem.data ('elem'),
          value = elem.data ('value');
          
          if (options['nested']) elem = elem.find ('a');
          if (dataElem) options['outputElem'] = '.' + dataElem;
          
          if (elem.hasClass (options['class'])) {
            
            elem.removeClass (options['class']);
            $(options['outputElem']).val ('');
            
          } else {
            
            elem.addClass (options['class']);
            $(options['outputElem']).val (value);
            
          }
          
          if (options['removeSiblings']) {
            
            if (options['nested'])
            $(this).siblings ().find ('a').removeClass (options['class']);
            else
            $(this).siblings ().removeClass (options['class']);
            
          }
          
          options.click (self, value, $(options['outputElem']).val ());
          
        }).hover (function () {
          
          options.hover (self, $(this).data ('value'), $(options['outputElem']).val ());
          
        });
        
      });
      
    };
    
  })($);