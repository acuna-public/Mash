/*
 page 1.0 - jQuery plugin
 written by O! Interactive, Acuna
 http://ointeractive.ru
 
 Copyright (c) 2017 O! Interactive, Acuna (http://ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 1.0  08.01.2017
  Первый приватный релиз
  
 */

(function ($) {
  
  $.fn.page = function (options) {
    $._page (this, options);
  };
  
  $._page = function (self, options) {
    
    options = $.extend ({
      
      'action': 'click',
      'container': '#content',
      'data': {},
      'push': true,
      'scrollTime': 0,
      'timeout': 30000, // 30 сек (1*30*1000)
      
      'init': function () {},
      'success': function () {},
      'beforeSend': function () {},
      'error': function () {},
      'complete': function () {},
      
    }, options);
    
    $(self).off ().on (options['action'], function (event) {
      
      if (event.which <= 1 && !event.metaKey) {
        
        event.preventDefault ();
        
        if ($.isFunction (options['url']))
          options['url'] = options.url ();
        else
          options['url'] = $(this).attr ('href'); // Не self!
        
        options.init (self);
        $.page (self, this, options);
        
      } else window.location = options['url'];
      
      return false;
      
    });
    
  };
  
  $.page = function (elem, self, options) {
    
    //$._page (elem, options);
    
    if ($.pushState) {
      
      var settings = {
        
        'beforeSend': function (jqXHR, settings) {
          
          jqXHR.setRequestHeader ('X-PAGE', 'true');
          jqXHR.setRequestHeader ('X-Referer', window.location.href);
          
          options.beforeSend (jqXHR, settings);
          
        },
        
        'error': function (jqXHR, textStatus, errorThrown) {
          
          options.error (jqXHR, textStatus, errorThrown);
          if (textStatus !== 'abort') window.location = options['url'];
          
        },
        
        'complete': function (jqXHR, textStatus) {
          options.complete (jqXHR, textStatus);
        }
        
      };
      
      $.prettyAjax (options['url'], options['data'], function (data) {
        
        if ($.trim (data) && /<html/i.test (data)) { // Ошибок на странице нет
          
          var container = $(data).find (options['container']); // Находим контейнер в тексте ответа
          
          $(options['container']).html (container.html ()); // И перекрываем его новым контентом
          options.success (self, container);
          
          if (options['scrollTime'] > 0)
            $.scrollTo ({ 'time':options['scrollTime'] });
          
          var title = data.match (/<title[^>]*>([^<]+)<\/title>/i)[1];
          if (title) document.title = title;
          
          if (options['push'])
            window.history.pushState ({}, document.title, options['url']); // Меняем путь в адресной строке
          else if (options['replace'])
            window.history.replaceState ({}, document.title, options['url']);
          
          // Поддержка Google Analytics
          
          if ((options['replace'] || options['push']) && window._gaq)
          _gaq.push (['_trackPageview']);
          
          // Если ссыль содержит флаг - то переходим к нему
          
          var hash = window.location.hash.toString ();
          
          if (hash.length > 0 && hash.match (/^#!\/.*/)) {
            
            window.location.hash = hash;
            location = options['url'] + hash.substr (2);
            
          }
          
          $(window).bind ('popstate', function (event) {
            
            options['url'] = window.location.href;
            $.page (container, self, options);
            
          });
          
          //if ($.inArray ('state', $.event.props) < 0)
          //$.event.props.push ('state');
          
        } else window.location = options['url'];
        
      }, settings);
      
      //showProgress = true;
      
    }
    
  };
  
})($);