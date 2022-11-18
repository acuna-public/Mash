/*
 countdown 1.0 - jQuery plugin
 written by O! Interactive, Acuna; Thanks to Martin Angelov
 http://ointeractive.ru
 
 Copyright (c) 2015 O! Interactive, Acuna (http://ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 1.0  09.08.2015
  Первый приватный релиз
  
 */
  
  (function ($) {
    
    $.fn.countdown = function (options) {
      
      options = $.extend ({
        
        'timestamp': 0,
        'callback': function () {},
        
      }, options);
      
      var
        days = 24 * 60 * 60,
        hours = 60 * 60,
        minutes = 60;
      
      $(this).each (function () {
        
        var self = $(this);
        
        (function tick () {
          
          var left, d, h, m, s;
          
          // Осталось времени
          left = Math.floor ((options.timestamp - (new Date ())) / 1000);
          if (left < 0) left = 0;
          
          // Осталось дней
          d = Math.floor (left / days);
          updateDuo (0, 1, d);
          left -= d * days;
          
          // Осталось часов
          h = Math.floor (left / hours);
          updateDuo (2, 3, h);
          left -= h * hours;
          
          // Осталось минут
          m = Math.floor (left / minutes);
          updateDuo (4, 5, m);
          left -= m * minutes;
          
          // Осталось секунд
          s = left;
          updateDuo (6, 7, s);
          
          options.callback (d, h, m, s);
          
          setTimeout (tick, 1000); // Повторяем вызов каждую секунду
          
        })();
        
        function switchDigit (position, number) {
          
          var digit = position.find ('.digit');
          
          if (digit.is (':animated')) return false;
          if (position.data ('digit') == number) return false;
          
          position.data ('digit', number);
          
          var replacement = $('<span>', {
            
            'class': 'digit',
            'css': { 'top':'-2.1em', 'opacity': 0 },
            'html': number
            
          });
          
          digit
          .before (replacement)
          .removeClass ('static')
          .animate ({ 'top':'2.5em', 'opacity':0 }, 'fast', function () {
            digit.remove ();
          });
          
          replacement
          .delay (100)
          .animate ({ 'top':0, 'opacity':1 }, 'fast', function () {
            replacement.addClass ('static');
          });
          
        };
        
        function updateDuo (minor, major, value) {
          
          var positions = self.find ('.position');
          
          switchDigit (positions.eq (minor), Math.floor (value / 10) % 10);
          switchDigit (positions.eq (major), value % 10);
          
        };
        
        self.addClass ('countdownHolder');
        
        $.each (['Days', 'Hours', 'Minutes', 'Seconds'], function (i) {
          
          $('<span class="count' + this + '">').html (
            
            '<span class="position">\
              <span class="digit static">0</span>\
            </span>\
            <span class="position">\
              <span class="digit static">0</span>\
            </span>'
            
          ).appendTo (self);
          
          if (this != 'Seconds')
          self.append ('<span class="countDiv countDiv' + i + '"></span>');
          
        });
        
      });
      
    };
    
  })($);