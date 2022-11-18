/*
 popup 1.0 - jQuery plugin
 written by O! Interactive, Acuna
 http://ointeractive.ru
 
 Copyright (c) 2016 O! Interactive, Acuna (http://ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 1.0  20.04.2016
  Первый приватный релиз
  
 */

(function ($) {
  
  $.fn.popup = function (options) {
    
    options = $.extend ({
      
      'id': 'popup',
      'height': 400,
      
    }, options);
    
    $(this).tooltip ({
      
      'id': options['id'],
      'class': options['class'],
      
      'content': function (e) {
        return '<img alt="" src="' + e.data ('image') + '" class="popup-full"/>';
      },
      
      'style': function (e) {
        return { 'position':'absolute', 'display':'none', 'margin-top':'50px', 'margin-left':'-300px' };
      },
      
      'elementStyle': {
        
        '.popup-full': function (e) {
          
          var output = {}, size = options['height'], width = e.width (), height = e.height ();
          
          //if (width > size) output['width'] = size + 'px';
          //else if (height > size) output['height'] = size + 'px';
          
          output['height'] = size + 'px';
          
          return output;
          
        }
        
      }
      
    });
    
  };
  
})($);