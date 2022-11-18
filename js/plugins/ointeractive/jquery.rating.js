/*
 Rating 1.0 - jQuery plugin
 written by O! Interactive, Acuna
 http://ointeractive.ru
 
 Copyright (c) 2015 O! Interactive, Acuna (http://ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 Dependencies: none
 
 1.0  24.07.2015
  Первый приватный релиз
  
 */
  
  (function ($) {
    
    $.fn.rating = function (options) {
      
      options = $.extend ({
        
        'type': 'full',
        'stars': 5,
        'minimal': 0,
        'readOnly': false,
        'click': function () {},
        'callback': function () {},
        
      }, options);
      
      $(this).each (function () {
        
        var self = $(this);
        
        if (!self.find ('.vote-wrap').length) {
          
          var data = {
            
            'area': self.data ('area'),
            'type': self.data ('type'),
            'item-id': self.data ('item-id'),
            'value': parseFloat (self.data ('value')) || 0,
            'votes': parseFloat (self.data ('votes')) || 0,
            
          };
          
          if (data.value > options.stars) data.value = options.stars;
          if (data.value < 0) data.value = 0;
          
          var old = data.value;
          
          var
          voteWrap = $('<div class="vote-wrap"></div>'), // Обвертка всего
          voteBlock = $('<div class="vote-block"></div>'), // Обвертка для position relative
          voteStars = $('<div class="vote-stars"></div>'), // Звездочки
          voteBody = $('<div class="vote-body"></div>'), // Тело
          voteHover = $('<div class="vote-hover"></div>'); // При наведении
          
          self.html (voteWrap);
          
          var
          thisWidth = voteWrap.width (),
          thisHeight = voteWrap.height ();
          
          voteWrap.append (
            
            voteHover.css ({ // При наведении
              
              'width': (options.stars * thisWidth),
              'height': thisHeight,
              'cursor': 'pointer',
              
            })
            
          ).css ({
            'width': (options.stars * thisWidth),
          });
          
          if (options.readOnly)
          voteHover.css ({'cursor': 'default'});
          
          voteHover.append (voteBlock);
          
          voteBlock.append (
            
            voteStars, // Звездочки
            
            voteBody.css ({ // Тело
              
              'width': (data.value * thisWidth),
              'height': thisHeight,
              
            })
            
          );
          
          var left = 0, width = 0;
          
          voteHover.on ('mousemove mouseover', function (e) { // Проводим мышкой
            
            if (!options.readOnly) {
              
              left = e.clientX > 0 ? e.clientX: e.pageX;
              width = left - $(this).offset ().left - 2;
              
              var
              score = 0,
              max = thisWidth * options.stars,
              min = options.minimal * thisWidth;
              
              if (width > max) width = max;
              if (width < min) width = min;   
              
              score = Math.round (width / thisWidth * 10) / 10;
              // Округляем до 1 знака после запятой
              
              if (options.type == 'half')
              width = Math.ceil (width / thisWidth * 2) * thisWidth / 2;
              else if (options.type != 'float')                 
              width = Math.ceil (width / thisWidth) * thisWidth;  
              
              score = Math.round (width / thisWidth * 10) / 10; 
              
              voteBody.css ({
                
                'width': width,
                'background-position': 'left center',
                
              });
              
            }
            
          }).on ('mouseout',function () { // Убираем мышку
            
            if (!options.readOnly)
            voteBody.css ({
              
              'width': old * thisWidth,
              'background-position': 'left bottom',
              
            });
            
          }).on ('click.rating', function () { // Кликаем
            
            if (!options.readOnly) {
              
              var score = Math.round (width / thisWidth * 10) / 10;
              if (score > options.stars) score = options.stars;
              if (score < 0) score = 0;
              
              data.value = (data.value * data.votes + score) / (data.votes + 1);
              //alert ((data.value * data.votes + score) + '-' + (data.votes + 1));
              
              data.value = Math.round (data.value * 100) / 100;
              
              old = data.value;
              data.score = score;
              
              voteBody.css ({
                
                'width': data.value * thisWidth,
                'background-position': 'left bottom',
                
              });
              
              options.click (data);
              
              if (typeof options.callback == 'function')
              options.callback.apply (self, [data]);
              
              data.value = 0; // Yeah!
              //options.readOnly = true;
              
            }
            
          });
          
        }
        
      });
      
    };
    
  })($);