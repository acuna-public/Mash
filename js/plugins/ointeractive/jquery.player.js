/*
 Player 2.1 - jQuery plugin
 written by O! Interactive, Acuna
 http://ointeractive.ru
 
 Copyright (c) 2014-2015, 2017, 2019-2020 O! Interactive, Acuna (http://ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 Dependencies: jquery.ui.slider, jquery.marquee, jquery.prettyAjax, jquery.cookie
 
 1.0  24.10.2014
  Первый приватный релиз
  
 1.1  20.04.2015
  Некоторые исправления
  
 1.2  29.10.2015
  При изменении размера окна браузера осуществляется ресайз элемента в опции resizeElem
  
 1.3  16.12.2015
  Реализована возможность воспроизведения через флеш в браузерах, не имеющих поддержки тега <audio>
  
 1.4  07.01.2017
  + Поддержка сторонних тем оформления
  + Возможность проигрывания музыки из нескольких плееров на странице
  + Кнопки "Вперед", "Назад"
  + Вывод времени с начала композиции
  + Возможность вывода плейлиста
  
 1.5  13.09.2019
  + Громкость сохраняется в cookies
  + Колбэк при достижении определенного времени прослушивания в процентах
  + Вывод процента буферизации
 
 1.6  14.03.2020
  + Вывод ошибки если получен неверный JSON c сервера
  + Исправлен переход на следующий трек
  + Разделение кода на несколько файлов
 
 2.0  16.07.2020
  Воспроизведение осуществляется с помощью Web Audio API вместо тега <audio>
  Поддерживаемые форматы вынесены в отдельные плагины
  
 2.1  01.12.2020
  Shuffle, Repeat, Prev, Next
  
 */
  
  $.fn.player = function (options) {
    
    options = $.extend ({
      
      'method': 'get',
      'data': {},
      'otherData': {},
      'otherElements': [],
      'playlist': '',
      'timeout': 10000,
      'cache': true,
      'resizeElem': '.seek',
      'marquee': true,
      'theme': 'default',
      'url': '',
      'type': 'audio',
      
      'provider': null,
      
      'volume': 100,
      'listenEndPercent': 90,
      'mode': '',
      
      'wrapper': '.music-tracklist',
      
      'swfLocation': 'player.swf',
      'swfError': function () {},
      
      'ajaxTimeout': 10000,
      'ajaxAttemptsNum': 5,
      'ajaxSleepTime': 2,
      
      'ajaxBeforeSend': function () {},
      'ajaxComplete': function () {},
      
      'error': function (elem, text) {},
      'ajaxError': function (xhr, textStatus, errorThrown) {},
      'cantPlayError': function () {},
      
      'action': function (elem, action) {},
      'complete': function () {},
      'play': function () {},
      'ended': function () {},
      'stop': function () {},
      'resume': function () {},
      'close': function () {},
      'next': function () {},
      'buffered': function (elem, proc) {},
      
    }, options);
    
    if ($.cookie ('volume') > 0) options.volume = $.cookie ('volume');
    
    var provider = $.player.provider (options), i = 0;
    
    $(this).each (function () { // Все плееры с текущим селектором
      provider.elements.push ($(this));
    });
    
    if (!$.isArray (options.otherElements))
      options.otherElements = [options.otherElements];
    
    $.each (options.otherElements, function (key, value) { // Дополнительные плееры
      
      var elem = $(value);
      options.otherElements[provider.num] = elem; // Заменяем селекторы на объекты (возможно это костыль)
      
      provider.num++;
      provider.elements.push (elem);
      
    });
    
    $.each (provider.elements, function (key, self) { // Все плееры на странице (включая дополнительные)
      
      i++;
      
      self.addClass ('player-' + i);
      self.attr ('data-player-id', i); // data () не работает
      self.addClass (options.theme);
      
      self = $('.player-' + i);
      
      $.each (options.otherData, function (key, value) {
        self.data (key, value);
      });
      
      //self.find ($('.player .artist')).text (self.data ('artist'));
      //self.find ($('.player .title')).text (self.data ('title'));
      
      var
        windowWidth = $(window).width (),
        seekWidth = self.find ('.seek').width ();
      
      if (self.find ('resizeElem').length > 0) {
        
        //self.find (options.resizeElem).width ($(options.wrapper).width ());
        
        $(window).resize (function () {
          
          self.find ('resizeElem').width (seekWidth);
          var width = Math.ceil (($(this).width () * self.find ('resizeElem').width ()) / windowWidth);
          self.find ('resizeElem').width (width);
          
        });
        
      }
      
      self.find ('.seek').slider ({ // Прогресс
        
        'range': 'min',
        'min': 0,
        'value': 0,
        'step': 1,
        
        'slide': function (e, ui) { // Перематываем вручную
          
          provider.checkSongEnded (ui.value);
          //provider.song.currentTime = ui.value;
          
        }
        
      });
      
      self.find ('.volume').slider ({ // Звук
        
        'range': 'min',
        'value': options.volume,
        'min': 0,
        'max': 100,
        'step': 1,
        
        'slide': function (e, ui) {
          
          $.each (provider.elements, function (key, value) { // Регулируем звук на всех плеерах на странице
            
            value.find ('.volume').slider ({
              'value': ui.value
            });
            
          });
          
          $.cookie ('volume', ui.value);
          options.volume = ui.value;
          
        }
        
      });
      
      self.find ('.prev').click (function () {
        
        var elem = provider.getElem (-1);
        
        if (elem.length) {
          
          elem = provider.getPlayer (elem);
          provider.action (elem, 'play');
          
        }
        
        return false;
        
      });
      
      self.find ('.next').click (function () {
        
        var elem = provider.getElem (1);
        
        if (elem.length) {
          
          elem = provider.getPlayer (elem);
          provider.action (elem, 'play');
          
        }
        
        return false;
        
      });
      
      self.find ('.play').click (function () { // Нажали play
        
        if ($.cookie ('volume') > 0) options.volume = $.cookie ('volume');
        
        var elem = provider.getPlayer (self);
        provider.action (elem, 'play'); // 1 шаг
        
      });
      
      self.find ('.stop').click (function () { // Нажали стоп
        
        var elem = provider.getPlayer (self);
        provider.action (elem, 'stop');
        
        return false;
        
      });
      
      self.find ('.playlist').click (function () {
        
        var elem = self.find ('.playlist-area');
        
        if (elem.is (':hidden'))
          elem.show ();
        else
          elem.hide ();
        
        return false;
        
      });
      
    });
    
  };