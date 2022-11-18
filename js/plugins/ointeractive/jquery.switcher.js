/*
 Switcher 1.1 - jQuery plugin
 written by O! Interactive, Acuna
 http://ointeractive.ru
 
 Copyright (c) 2015-2016 O! Interactive, Acuna (http://ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 Dependencies: none
 
 1.0  09.08.2015
  Первый приватный релиз
  
 1.1  07.01.2016
  Исправлена ошибка, при которой свитчер отрабатывал дважды, даже если уже был создан ранее
  
 */
  
  (function ($) {
    
    $.fn.switcher = function (options) {
      
      $(this).each (function () {
        
        var self = $(this);
        
        if (!self.find ('.switcher-wrapper').length) {
          
          var sel = '';
          if (self.data ('select')) sel = ' checked="checked"';
          
          self.append (
  '<div class="switcher-wrapper">\
    <input type="checkbox" name="' + self.data ('name') + '" id="' + self.data ('id') + '" value="' + self.data ('value') + '" class="switcher-checkbox"' + sel + '/>\
    <label class="switcher-label" for="' + self.data ('id') + '">\
      <span class="switcher-inner"></span>\
      <span class="switcher-switch"></span>\
    </label>\
  </div>');
          
        }
        
      });
      
    };
    
  })($);