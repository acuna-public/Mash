/*
 baloon 1.0 - jQuery plugin
 written by O! Interactive, Acuna
 http://ointeractive.ru
 
 Copyright (c) 2016 O! Interactive, Acuna (http://ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 Dependencies: none
 
 1.0  08.06.2016
  Первый приватный релиз
  
 */
  
  (function ($) {
    
    $.baloon = function (text, options) {
      
      options = $.extend ({
        
        'class': 'baloon-default',
        'closeButton': false,
        'closeElem': '.close',
        'width': 0,
        'height': 0,
        'fadeTime': 6000,
        'opacity': 0,
        'opacityColor': '#000',
        'padding': 0,
        'scroll': true,
        'closeOut': true,
        'openDelay': 1000,
        
        'onResize': function () {},
        'onClose': function () {},
        'onInit': function () {},
        
      }, options);
      
      if (!options['scroll']) {
        
        //$('body').wrap ('<div style="overflow:hidden;"></div>');
        $('html,body').css ('overflow', 'hidden');
        
      }
      
      if (options['opacity'])
      $.opacity (options['opacity'], options['opacityColor'], options['class']);
      
      //if (options['openDelay'])
      //$('.opacity').hide ().delay (options['openDelay']).show ();
      
      var elem = '.baloon.' + options['class'];
      options['onInit'] (elem, options);
      
      $('body').append ('<span class="baloon ' + options['class'] + '">' + text + '</span>');
      elem = $(elem);
      
      if (options['closeButton']) {
        
        $('body').append ('<div class="baloon-close ' + options['class'] + '"></div>');
        
        $('.baloon-close.' + options['class']).click (function () {
          
          options['onClose'] (elem, options);
          $.baloonClose ({ 'class':options['class'], 'opacity':options['opacity'], 'scroll':true, });
          
        });
        
      }
      
      if (options['openEffect'])
      elem.hide ().fadeIn (1000);
      
      var
      iWidth = options['width'],
      iHeight = options['height'];
      
      if ($.isFunction (options['onLoad']))
      options['onLoad'] ('.baloon.' + options['class'], iWidth, iHeight, options);
      
      if (!iWidth) iWidth = elem.width ();
      if (!iHeight) iHeight = elem.height ();
      
      var imgResize = function (width, height, top, debug) {
        
        var size = $.elemDimentions (iWidth, iHeight, width, height, options['padding'], debug);
        
        elem.center (size[0], size[1], top);
        
        if (options['opacity'])
        $('.opacity.' + options['class']).css ({
          
          'width': width + 'px',
          'height': height + 'px',
          'top': top + 'px',
          
        });
        
        return size;
        
      };
      
      var size = imgResize ($(window).width (), $(window).height (), $(window).scrollTop ());
      
      if ($.isFunction (options['onOpen']))
      options['onOpen'] ('.baloon.' + options['class'], size[0], size[1], options);
      
      if ($.isFunction (options['onClick']))
      options['onClick'] ('.baloon.' + options['class'], options);
      
      elem.css ({
        
        'margin-left': -(elem.outerWidth () / 2) + 'px',
        'margin-top': ($(window).scrollTop () - (elem.outerHeight () / 2)) + 'px',
        
      });
      
      $(window).resize (function () {
        
        var width = $(this).width (), height = $(this).height (), top = $(this).scrollTop ();
        var size = imgResize (width, height, top);
        
        options['onResize'] ('.baloon.' + options['class'], size[0], size[1], width, height, top, options);
        
      });
      
      $(window).scroll (function () {
        
        elem.css ({
          
          'margin-left': -(elem.outerWidth () / 2) + 'px',
          'margin-top': ($(window).scrollTop () - (elem.outerHeight () / 2)) + 'px',
          
        });
        
      });
      
      if (options['fadeTime'])
      elem.fadeOut (options['fadeTime']);
      
      elem.find (options['closeElem']).click (function () {
        
        $.baloonClose ({ 'class':options['class'], 'opacity':options['opacity'], 'scroll':true, });
        options['onClose'] (elem, options);
        
      });
      
      if (options['closeOut'])
      $('.baloon').clickOut (function (elem, target) {
        
        options['onClose'] (elem, options);
        $.baloonClose ({ 'class':options['class'], 'opacity':options['opacity'], 'scroll':true, });
        
      });
      
    };
    
    $.baloonOverflow = function () {
      $('html,body').css ('overflow', 'auto');
    };
    
    $.baloonClose = function (options, callback) {
      
      //LoadingBar ('page', 1);
      
      var elem = $('.baloon.' + options['class']);
      
      if (elem.length) {
        
        if (options['scroll']) $.baloonOverflow ();
        
        if (options['opacity']) $.opacityRemove (options['class']);
        $('.baloon-close.' + options['class']).remove ();
        elem.remove ();
        
        if (callback != null)
          callback ('.baloon.' + options['class'], options);
        
      }
      
      //LoadingBar ('page', 0);
      
    };
    
  })($);