/*
 autoresize 1.0 - jQuery plugin
 written by O! Interactive, Acuna
 http://ointeractive.ru
 
 Copyright (c) 2016 O! Interactive, Acuna (http://ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 1.0  13.06.2016
  Первый приватный релиз
  
*/

(function ($) {
  
  $.fn.autoResize = function (options) {
    
    var options = $.extend ({
      
      'onResize': function (){},
      'animate': true,
      'animateDuration': 150,
      'animateCallback': function (){},
      'extraSpace': 0,
      'limit': 500,
      'minHeight': 50,
      'autoHeight': true,
      
    }, options);
    
    $(this).filter ('textarea').each (function () {
      
      var textarea = $(this).css ({ 'resize':'none', 'overflow-y':'hidden' }),
      
      origHeight = textarea.height (),
      
      clone = (function () {
        
        var
        props = ['height', 'width', 'lineHeight', 'textDecoration', 'letterSpacing'],
        propOb = {};
        
        $.each (props, function (i, prop){
          propOb[prop] = textarea.css (prop);
        });
        
        return textarea.clone ().removeAttr ('id').removeAttr ('name').css ({
          
          'position': 'absolute',
          'top': 0,
          'left': -9999
          
        }).css (propOb)
        .attr ('tabIndex', '-1')
        .insertBefore (textarea)
        .addClass ('auto-resize');
        
      })(),
      
      lastScrollTop,
      
      updateSize = function () {
        
        clone.height (0).val ($ (this).val ()).scrollTop (10000);
        
        var
        scrollTop = Math.max (clone.scrollTop (), origHeight) + options.extraSpace,
        toChange = $ (this).add (clone);
        
        if (lastScrollTop !== scrollTop) {
          
          lastScrollTop = scrollTop;
          
          if (scrollTop >= options.limit && options.limit != 0) {
            
            $ (this).css ({ 'overflow-y':'', 'height':options.limit+'px' });
            return;
            
          }
          
        }
        
        options['onResize'] (this, origHeight, options);
        
        options.animate && textarea.css ('display') === 'block' ?
        toChange.stop ().animate ({height:scrollTop}, options.animateDuration, options.animateCallback) :
        toChange.height (scrollTop);
        
      };
      
      var value = textarea.val ();
      
      if (options.autoHeight && value > textarea.height) {
        
        var height = (value.length / 2);
        if (height < options.minHeight) height = options.minHeight;
        
        textarea.css ({ 'height':height + 'px', 'overflow-y':'auto' });
        
      }
      
      textarea.unbind ('.dynSiz')
      .on ('keyup.dynSiz', updateSize)
      .on ('keydown.dynSiz', updateSize)
      .on ('change.dynSiz', updateSize);
      
    });
    
    return this;
    
  };

  })(jQuery);