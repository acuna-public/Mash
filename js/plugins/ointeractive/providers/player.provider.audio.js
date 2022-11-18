  $.player.provider.audio = function () {
    
    return {
      
      newInstance: function (player, options) {
        
        var audio, init = false;
        
        player.create = function () {
          
          if (!audio) {
            
            try {
              
              audio = new Audio ();
              
              audio.src = options['answer']['success']['url'];
              
              player.canPlay = true;
              
            } catch (e) {
              player.canPlay = false;
            }
            
          } else audio.src = options['answer']['success']['url'];
          
        };
        
        player.play = function () {
          
          if (!player.isPlaying ()) audio.play ();
          
          audio.volume = player.prepVolume (options.volume);
          
          player.setTime (player.elem.find ('.seek').slider ('value')); // Если время выбрали ползунком еще до проигрывания
          
          audio.addEventListener ('loadeddata', function () {
            
            var dtime = player.songTime (audio.duration), buffer = 0;
            
            audio.addEventListener ('timeupdate', function () { // Играем песню
              
              $.each (options.otherElements, function (key, value) {
                
                var elem = player.getPlayer (value);
                
                if (audio.buffered && audio.buffered.length > 0) { // Буферизация
                  
                  audio.addEventListener ('progress', function () {
                    //elem.find ('.progress').css ('width', Math.ceil ((audio.buffered.end (0) * 100) / audio.duration) + '%');
                  });
                  
                }
                
                player.checkSongEnded (audio.currentTime);
                
                var time = player.songTime (audio.currentTime);
                
                elem.find ('.seek').slider ({
                  
                  'range': 'min',
                  'value': time[0],
                  'max': audio.duration,
                  
                  'slide': function (e, ui) { // Перематываем вручную
                    
                    if (!options.debug) player.autoSeek = false; // Отключаем коллбек того, что песня заканчивается для предотвращения накруток прослушиваний при перемотке вручную
                    player.setTime (ui.value);
                    
                  }
                  
                });
                
                if (audio.currentTime) {
                  
                  elem.find ('.time').text (time[1] + ':' + time[2]);
                  elem.find ('.duration').text (dtime[1] + ':' + dtime[2]);
                  
                }
                
              });
              
            });
            
            audio.addEventListener ('ended', function () { // Песня кончилась
              
              player.almostEnd = false;
              player.autoSeek = true;
              
              var elem = $ ('.player-' + player.id);
              
              player.action (elem, 'next'); // Переходим к следующему треку
              
            });
            
          });
          
        };
        
        player.pause = function () {
          audio.pause ();
        };
        
        player.resume = function () {
          audio.play ();
        };
        
        player.setTime = function (time) {
          audio.currentTime = time;
        };
        
        player.next = function () {
          
          if (player.isPlaying ()) audio.pause ();
          player.setTime (0);
          
          audio.addEventListener ('loadeddata', function () {
            
            var time = player.songTime (this.duration);
            var self = $ ('.player-' + player.prev);
            
            self.find ('.time').text (time[1] + ':' + time[2]); // Меняем время на длительность песни
            
          });
          
        };
        
        player.isPaused = function () {
          return (audio && audio.paused);
        };
        
        player.isPlaying = function () {
          return (
            audio &&
            audio.currentTime > 0 &&
            !audio.paused &&
            !audio.ended &&
            audio.readyState > 2
          );
        };
        
        player.duration = function () {
          return (audio ? audio.duration : 0);
        };
        
        player.setVolume = function (value) {
          if (audio) audio.volume = value;
        };
        
        player.isInit = function () {
          return audio;
        };
        
        return player;
        
      },
      
    };
    
  };