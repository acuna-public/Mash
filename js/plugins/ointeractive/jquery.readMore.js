/*
 readMore 1.1 - jQuery plugin
 written by O! Interactive, Acuna
 http://ointeractive.ru
 
 Copyright (c) 2015, 2020 O! Interactive, Acuna (http://ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 Dependencies: none
 
 1.0  24.02.2015
  Первый приватный релиз
  
 1.1  14.08.2020
  Совместимость с Bootstrap
  
 */
  
  (function ($) {
    
    $.fn.readMore = function (options) {
      
      options = $.extend ({
        
        'slideHeight': 200,
        'langMore': 'Читать далее...',
        'langLess': 'Свернуть',
        'speed': 500,
        'maxElem': 100,
        'tab': 15,
        'scrollTop': true,
        
      }, options);
      
      $(this).each (function () {
        
        var rand = Math.floor (Math.random () * options['maxElem']);
        
        $(this).wrapInner ('<div class="read-more-wrapper elem-' + rand + '"></div>');
        var wrapper = $('.elem-' + rand);
        
        var defHeight = wrapper.height ();
        
        if (defHeight >= options['slideHeight']) {
          
          wrapper.after ('<div class="read-more-link link-' + rand + '"></div>');
          var readMore = $('.link-' + rand);
          
          wrapper.append ('<div class="read-more-gradient gradient-' + rand + '"></div>');
          var gradient = $('.gradient-' + rand);
          
          wrapper.css ({ 'height':options['slideHeight'] + 'px', 'overflow': 'hidden' });
          readMore.append ('<a href="#">' + options['langMore'] + '</a>');
          
          readMore.children ('a').click (function () {
            
            var curHeight = wrapper.height ();
            
            if (curHeight == options['slideHeight']) {
              
              wrapper.animate ({ 'height':defHeight }, 'normal');
              
              $(this).text (options['langLess']).addClass ('read-more-close');
              gradient.fadeOut ();
              
            } else {
              
              wrapper.animate ({ height:options['slideHeight'] }, options['speed']);
              
              $(this).text (options['langMore']);
              gradient.fadeIn ();
              
            }
            
            if (options['scrollTop'])
            $('body,html').animate ({ 'scrollTop':((wrapper.position ().top + options['slideHeight']) - options['tab']) }, options['speed']);
            
            return false;
            
          });
          
        }
        
      });
      
    };
    
  })($);