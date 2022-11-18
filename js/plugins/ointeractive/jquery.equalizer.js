/*
 Toggle 1.0 - jQuery plugin
 written by O! Interactive, Acuna
 http://ointeractive.ru
 
 Copyright (c) 2020 O! Interactive, Acuna (http://ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 1.0  31.07.2020
  Первый приватный релиз
  
 */
  
  $.fn.equalizer = function (options) {
    
    options = $.extend ({
      
      'elem': [],
      
    }, options);
    
    const MAX_BAR_HEIGHT = 5;
    
    var bars = '';
    for (var i = 0; i < 15; i++)
    bars += '<div class="equalizer-bar"><span></span></div>';
    
    $(this).html (bars);
    
    var setRandomBars = function () {
      
      const bars = document.getElementsByClassName ('equalizer-bar');
      
      for(let i = 0; i < bars.length; i++) {
         
        let spans = bars[i].getElementsByTagName('span');
        let activeSpanCount = getActiveSpans(spans);
        let newHeight = getRandomHeight(MAX_BAR_HEIGHT);
        
        for(let j = 0; j < spans.length; j++) {
          
          if(newHeight > activeSpanCount) {
            spans[j].style.opacity = '1';
          } else if(j > newHeight) {        
            spans[j].style.opacity = '0';
          }
          
          // set little opacity
          let upperSpan = MAX_BAR_HEIGHT - j;
          if(newHeight > MAX_BAR_HEIGHT && upperSpan < 5) {
            spans[j].style.opacity = '0.' + upperSpan;
          }
          
        }
      }
    }

    // Returns the number of active spans
    var getActiveSpans = function (spans) {
      let counter = 0;
       
      for(let i = 0; i < spans.length; i++) {
        if(spans[i].style.opacity > 0) counter++;
      }
      
      return counter;
    }

    var getRandomHeight = function (maxBarHeight) {
      return Math.round(Math.random() * (maxBarHeight - 1)) + 1;
    }
    
    var addBarSpans = function () {
      const bars = document.getElementsByClassName('equalizer-bar');
      
      let html = '';
      for(let j = 0; j < MAX_BAR_HEIGHT; j++) {
        html += '<span></span>';
      }  
      
      for(let i = 0; i < bars.length; i++) {
        bars[i].innerHTML = html;
      }
    }
    
    addBarSpans();
    
    setInterval(() => {
      setRandomBars();
    }, 200);
    
  };