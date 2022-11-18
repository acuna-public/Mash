  $.player = {
    
    'provider': function (options) {
      
      var provider = {
        
        'id': 0,
        'elem': null,
        'prev': 0,
        'selected': null,
        'almostEnd': false,
        'autoSeek': true,
        'canPlay': false,
        'elements': [],
        'num': 0,
        
        'next': function () {},
        
        'change': function (self, find, replace) {
          
          self.find ('.' + find).toggleClass (find + ' ' + replace);
          self.find ('.' + find + '-area').hide ();
          
          //self.find ('.' + replace).css ('display', 'block'); // Не inline
          self.find ('.' + replace + '-area').css ('display', 'block'); // Не inline
          
          self.find ('.' + replace).unbind ('click').click (function () {
            
            provider.action (self, replace);
            return false;
            
          });
          
        },
        
        'ajax': function (self, action, success, i, error) {
          
          if (!success) success = options[action];
          
          options.data.area = action;
          
          var aoptions = {
            
            'method': options.method,
            'timeout': options.ajaxTimeout,
            
            'error': function (xhr, textStatus, errorThrown) {
              
              provider.change (self, 'pause', 'play');
              options.ajaxError (xhr, textStatus, errorThrown);
              
            },
            
            'beforeSend': options.ajaxBeforeSend,
            'complete': options.ajaxComplete,
            'dataType': 'json', // JSON должен приходить всегда
            
          };
          
          if (typeof (options.output) !== 'undefined')
          success = function (result) {
            options.output (result);
          };
          
          $.prettyAjax (options.ajaxUrl, options.data, success, aoptions);
          
        },
        
        'showMarquee': function (elem) {
          
          if (options.marquee)
          elem.marquee2 ({
            
            speed: 5,
            gap: 20,
            delayBeforeStart: 0,
            direction: 'left',
            startVisible: true,
            duplicated: true,
            pauseOnHover: true
            
          });
          
        },
        
        'setTitle': function (action) {
          
          if (provider.canPlay)
            document.title = "\u25b6 " + options.answer.success.artist + '   —  ' + options.answer.success.title;
          
        },
        
        'resetTitle': function (action) {
          
          if (provider.canPlay)
            document.title = document.title.substring (2);
          
        },
        
        'prepVolume': function (vol) {
          return (vol / 100);
        },
        
        'checkSongEnded': function (value) {
          
          if (!provider.almostEnd && provider.autoSeek && prop (value, provider.duration ()) >= options.listenEndPercent) {
            
            provider.almostEnd = true;
            provider.ajax ('ended');
            
          }
          
        },
        
        'songTime': function (time, rawSecs) {
          
          var mins = '--', secs = '--';
          
          if (time) {
            
            time = Math.floor (time);
            
            if (!isNaN (time)) {
              
              mins = Math.floor (time / 60);
              
              if (rawSecs)
                secs = time;
              else
                secs = Math.floor (time % 60);
              
              if (mins <= 9) mins = '0' + mins;
              if (secs <= 9) secs = '0' + secs;
              
            }
            
          }
          
          return [time, mins, secs];
          
        },
        
        'getPlayer': function (value, debug) {
          
          if (debug) alert (value.data ('player-id'));
          return $('.player-' + value.data ('player-id'));
          
        },
        
        'processPlayers': function (find, replace) {
          
          $.each (options.otherElements, function (key, value) {
            provider.change (provider.getPlayer (value), find, replace);
          });
          
        },
        
        'lastId': -1,
        
        'getElem': function (id) {
          
          var num;
          
          if (options.mode == 'shuffle') {
            
            num = rand (0, provider.elements.length - 1);
            
            if (num == provider.lastId)
              num = 0;
            else
              provider.lastId = num;
            
          } else if (options.mode == 'repeat') {
            
            num = provider.id;
            
          } else {
            
            num = provider.id + id - 1;
            if (num < 0) num = 0;
            
          }
          
          return provider.elements[num];
          
        },
        
        'start': function (self) {
          
          provider.prev = provider.id; // Сохраняем прошлый id
          provider.id = self.data ('player-id');
          provider.elem = $('.player-' + provider.id);
          
          provider.create ();
          
          if (!provider.canPlay)
            provider.setProvider ($.player.provider.flash ()).create ();
          
          if (provider.prev)
            provider.action ($('.player-' + provider.prev), 'stop', self); // Закрываем предыдущий плеер
          
          provider.setTitle ('play');
          
          provider.play ();
          
          var playlist = '';
          
          $.each (provider.elements, function (key, elem) { // Все плееры на странице (включая дополнительные)
            
            if (elem.data ('area') && elem.data ('area') == self.data ('area'))
              playlist += '\
    <div class="plyr-item">\
      \
      <div class="plyr-item-poster">' + elem.find ('.cover').attr ('src') + '</div>\
      \
      <div class="flex">\
        <div class="plyr-item-title h-1x">\
          ' + elem.find ('.title').text () + '\
        </div>\
        <div class="plyr-item-author text-sm text-fade">\
          ' + elem.find ('.artist').text () + '\
        </div>\
      </div>\
      <button class="plyr-item-close close text">×</button>\
    </div>'; // TODO
            
          });
          
          $.each (options.otherElements, function (key, value) { // Обрабатываем этот и дополнительные плееры
            
            var elem = provider.getPlayer (value);
            
            elem.find ('.playlist-area').html (playlist);
            
            elem.data ('id', self.data ('id')); // Передаем id трека во все дополнительные плееры чтобы можно было слушать и в них
            
            provider.change (elem, 'play', 'pause');
            
            var data = options['answer']['success'];
            
            elem.find ('.artist').text (data['artist']);
            elem.find ('.title').text (data['title']);
            elem.find ('.cover').css ('background-image', 'url(\'' + data['cover'] + '\')');
            
            provider.showMarquee (elem.find ('.marquee'));
            
            elem.find ('.volume').slider ({ // Двигаем ползунок звука
              
              'range': 'min',
              'step': 1,
              'min': 0,
              'max': 100,
              
              'slide': function (e, ui) {
                
                provider.setVolume (provider.prepVolume (ui.value));
                
                elem.each (function () {
                  
                  $(this).find ('.volume').slider ({
                    'value': ui.value
                  });
                  
                });
                
                $.cookie ('volume', ui.value);
                
              }
              
            });
            
          });
          
        },
        
        setProvider: function (instance) {
          return instance.newInstance (provider, options);
        },
        
        action: function (self, action, old) { // Святая святых
          
          //alert (action);
          //if (provider.canPlay || (!provider.canPlay && hasFlash ())) {
          
          if (!provider.loaded) {
            
            provider.loaded = true;
            options.otherElements.push (self); // Добавляем текущий плеер
            
          }// else options.otherElements[provider.num] = self;
          
          options.action (self, action);
          
          switch (action) {
            
            case 'play': { // Действие проигрывания (инициализация плеера)
              
              if (provider.isPaused ()) { // Песня стоит на паузе, возобновляем прослушивание
                
                provider.resume ();
                
                provider.setTitle (action);
                provider.processPlayers ('play', 'pause');
                
              } else { // Песня не играет, начинаем играть
                
                //if (!options['answer']) { // Если не установлен тег src - получаем URL аяксом (да, мы произносим его как "аякс")
                  
                  options.data.data = {
                    
                    'id': self.data ('id'),
                    
                  };
                  
                  provider.ajax (self, action, function (data) {
                    
                    options['answer'] = data;
                    
                    if (self.data ('url'))
                      options['answer']['success']['url'] = self.data ('url');
                    
                    if (options['answer']['success'])
                      
                      if (self.data ('artist'))
                        options['answer']['success']['artist'] = self.data ('artist');
                      
                      if (self.data ('title'))
                        options['answer']['success']['title'] = self.data ('title');
                      
                      if (options['answer']['success']['url'] &&
                        (
                          equals (options['answer']['success']['url'], 'http://')
                          ||
                          equals (options['answer']['success']['url'], 'https://')
                        )
                      )
                        provider.start (self); // 2 шаг
                    else {
                      
                      provider.action (self, 'error');
                      options.error (self, options.answer.error);
                      
                    }
                    
                  });
                  
                //} else provider.start (self);
                
              }
              
              break;
              
            }
            
            case 'pause': { // Действие паузы
              
              if (!provider.isPaused ()) {
                
                if (provider.isPlaying ()) provider.pause ();
                
                provider.ajax (self, action);
                provider.resetTitle (action);
                provider.processPlayers ('pause', 'play');
                
              }
              
              break;
              
            }
            
            case 'error': {
              
              provider.processPlayers ('pause', 'play');
              
              self.find ('.play').click (function () {
                return false;
              }); // Больше не разрешаем играть, все-равно не найдена
              
              //provider.action (self, 'stop'); // Закрываем текущий плеер
              
              break;
              
            }
            
            case 'stop': case 'next': { // next аналогична stop, только не сбрасывает тайтл сайта
              
              if (action == 'stop')
                provider.resetTitle (action);
              else
                provider.ajax (self, action);
              
              provider.next ();
              
              $.each (options.otherElements, function (key, value) {
                
                var elem = provider.getPlayer (value);
                
                provider.change (elem, 'pause', 'play');
                
                elem.find ('.seek').slider ({
                  
                  'range': 'min',
                  'min': 0,
                  'value': 0,
                  
                }); // Сдвигаем ползунок прогресса прослушивания на начало у всех плееров на странице
                
              });
              
              if (old) options.otherElements[provider.num] = old;
              
              var nextSelf = provider.getElem (1);
              
              if (action == 'next' && nextSelf.length)
                provider.action (nextSelf, 'play'); // Инициализируем следующий за текущим плеер
              
              break;
              
            }
            
          }
          
          options.complete (self, action);
          
        },
      
      };
      
      if (!options.provider)
        options.provider = $.player.provider.audio ();
      
      return provider.setProvider (options.provider);
      
    },
    
  };