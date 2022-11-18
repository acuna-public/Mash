/*
 tooltip 1.2 - jQuery plugin
 written by O! Interactive, Acuna
 http://ointeractive.ru
 
 Copyright (c) 2014-2015 O! Interactive, Acuna (http://ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 Dependencies: none
 
 1.0  22.05.2014
  Первый приватный релиз
  
 1.1  18.06.2015
  + Реализована возможность скрытия по клику
  + Добавлена возможность вывода собственного контента
  
 */
  
  (function ($) {
    
    $.fn.tooltip = function (options) {
      
      options = $.extend ({
        
        'xOffset': 15,
        'yOffset': 25,
        'id': 'tooltip',
        'clickRemove': false,
        'content': null,
        'useElement': '',
        'style': null,
        'elementStyle': {},
        'class': 'tooltip-content',
        
      }, options);
      
      $(this).each (function () {
        
        var self = $(this), title = self.attr ('title');
        
        self.hover (
          
          function (e) {
            
            var content;
            
            if (options['content'])
              content = options['content'] (self, options);
            else if (options['useElement'])
              content = ('#' + options['useElement']).html ();
            else
              content = title;
            
            if (content != '' && $.type (content) != 'undefined') {
              
              self.attr ('title', '');
              
              $('body').append ('<div id="' + options['id'] + '" class="' + options['class'] + '">' + content + '</div>');
              
              var elem = $('#' + options['id']);
              
              if (!options['style'])
              options['style'] = function (e) {
                return { 'position':'absolute', 'top':(elem.visible () - options['yOffset']) + 'px', 'left':(e.pageX + options['xOffset']) + 'px', 'display':'none' };
              };
              
              elem.css (options['style'] (elem, self, options))
              .fadeIn ('fast');
              
              $.each (options['elementStyle'], function (key, value) {
                
                elem.find (key).each (function () {
                  $(this).css (value (elem, $(this), self, options));
                });
                
              });
              
            }
            
          },
          
          function () {
            
            $('#' + options['id']).remove ();
            self.attr ('title', title);
            
          }
          
        );
        
        self.mousemove (function (e) {
          
          $('#' + options['id'])
          .css ({ 'top':(e.pageY - options['yOffset']) + 'px', 'left':(e.pageX + options['xOffset']) + 'px' });
          
        });
        
        if (options['clickRemove'])
        self.mousedown (function (e) {
          
          $('#' + options['id']).remove ();
          self.attr ('title', title);
          
        });
        
      });
      
    };
    
  })($);