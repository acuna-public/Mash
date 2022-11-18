  $.player.provider.flash = function () {
    
    return {
      
      newInstance: function (player, options) {
        
        var flash = {};
        
        player.create = function () {
          
          var
            fragment = document.createDocumentFragment (),
            doc = fragment.createElement ? fragment : document;
          
          var wrapper = doc.createElement ('div');
          
          var movie = options.swfLocation + '?type=' + options.type + '&datetime=' + new Date ().getTime ();
          
          wrapper.innerHTML = '\
<object\
  classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"\
  id="song-' + player.id + '-object"\
  width="1"\
  height="1"\
  style="position:absolute; left:-1px;"\
/>\
  <param name="movie" value="' + movie + '"/>\
  <param name="allowscriptaccess" value="always" />\
  <embed\
    name="song-' + player.id + '-object"\
    src="' + movie + '"\
    loop="false"\
    quality="high"\
    allowscriptaccess="always"\
    width="1"\
    height="1"\
  />\
</object>';
          
          var source = doc.createElement ('source');
          
          source.setAttribute ('src', options['answer']['success']['url']);
          source.setAttribute ('type', 'audio/mpeg');
          
          wrapper.appendChild (source);
          
          document.body.appendChild (wrapper);
          
        };
        
        player.play = function () {
          
          $(document).ready (function () {
            
            $.each (options.otherElements, function (key, value) {
              
              var elem = player.getPlayer (value);
              
              flash.url = options['answer']['success']['url'];
              flash.index = player.id;
              flash.object = getFlash ('song-' + player.id + '-object');
              flash.volume = player.prepVolume (options.volume);
              flash.elem = elem;
              flash.options = options;
              //flash.duration = 0;
              
              var time = player.songTime (elem.find ('.seek').slider ('value'));
              
              elem.find ('.seek').slider ({ // Слушаем песню
                
                'range': 'min', 
                'value': time[0],
                'max': 100,
                
                'slide': function (e, ui) {
                  
                  player.pause ();
                  player.skipTo (ui.value / 100); // Действие двиганья ползунка уходит во флеш
                  player.play ();
                  
                  var time = player.songTime ((ui.value * flash.duration) / 100);
                  elem.find ('.time').text (time[1] + ':' + time[2]);
                  
                }
                
              });
              
              /*var
                readyTimer,
                loadTimer,
                ios = (/(ipod|iphone|ipad)/i).test (navigator.userAgent),
                i = 0;
              
              readyTimer = setInterval (function () {
                
                var time = player.songTime (i);
                
                elem.find ('.seek').slider ({
                  
                  'range': 'min',
                  'max': 100,
                  'value': time[0],
                  
                }).removeClass ('progress');
                
                elem.find ('.time').text (time[1] + ':' + time[2]);
                ++i;
                
              }, 1000);*/
              
              //flash.object.load ();
              flash.object.play ();
              
            });
            
          });
          
        };
        
        player.pause = function () {
          flash.object.pause ();
        };
        
        player.resume = function () {
          flash.object.resume ();
        };
        
        player.setVolume = function (vol) {
          flash.object.setVolume (vol);
        };
        
        player.loadProgress = function (percent, duration) {
          
          flash.duration = duration;
          
          flash.self.find ('.seek').slider ({
            
            'range': 'min',
            'value': (percent * 100),
            'max': 100,
            
          }).addClass ('progress');
          
          var time = player.songTime ();
          flash.self.find ('.time').text (time[1] + ':' + time[2]);
          
        };
        
        player.updatePlayhead = function (level, duration) {
          
          flash.self.find ('.seek').slider ({
            
            'range': 'min',
            'value': (level * 100),
            'max': 100,
            
          }).removeClass ('progress');
          
          var time = player.songTime (level * duration);
          flash.self.find ('.time').text (time[1] + ':' + time[2]);
          
        };
        
        player.skipTo = function (percent) {
          flash.object.skipTo (percent);
        };
        
        player.trackEnded = function (type) {
          
          $.playerInit (flash.elem, 'ended', player, options);
          
          var time = player.songTime (flash.duration);
          flash.self.find ('.time').text (time[1] + ':' + time[2]);
          
          var
            elem = $('body').find (flash.options.element),
            nextSelf = elem.eq (flash.index + 1);
          
          if (nextSelf.length)
            $.playerInit (nextSelf, 'play', player, options);
          
        };
        
        player.loadFinished = function (duration) {
          flash.self.find ('.seek').removeClass ('progress');
        };
        
        player.loadError = function () {
          
          var text = flash.options.swfError;
          if (!text) text = 'Не удалось проиграть трек';
          
          flash.options.error (flash.elem, text);
          
        };
        
        player.next = function () {
          
          player.pause (); // Командуем флешу "Пауза" (то есть буквально "Стоп". Как такового стопа нет)...
          player.skipTo (0); // ... и перематываем песню на начало
          
          return player.songTime (flash.duration);
          
        };
        
        return player;
        
      }
      
    };
    
  };