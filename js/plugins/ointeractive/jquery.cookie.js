/*
 cookie 1.0 - jQuery plugin
 written by O! Interactive, Acuna
 http://ointeractive.ru
 
 Copyright (c) 2015 O! Interactive, Acuna (http://ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 Dependencies: none
 
 1.0  08.09.2015
  Первый приватный релиз
  
 */
  
  (function ($) {
    
    $.cookie = function (key, value, options) {
      
      options = $.extend ({
        raw: true,
      }, options);
      
      var stringifyCookieValue = function (value) {
        return encode (options.json ? JSON.stringify (value) : String (value));
      };
      
      var encode = function (value) {
        
        if (options.raw) value = encodeURIComponent (value);
        return value;
        
      };
      
      var decode = function (value) {
        
        value = value.trim ();
        if (options.raw) value = decodeURIComponent (value);
        
        return value;
        
      };
      
      var parseCookieValue = function (key) {
        
        if (key.indexOf ('"') === 0)
        key = key.slice (1, -1).replace (/\\"/g, '"').replace (/\\\\/g, '\\');
        
        try {
          
          key = decodeURIComponent (key.replace (/\+/g, ' '));
          return options.json ? JSON.parse (key) : key;
          
        } catch (e) {}
        
      };
      
      var read = function (value, converter) {
        
        if (options.raw) value = parseCookieValue (value);
        return $.isFunction (converter) ? converter (value) : value;
        
      };
      
      if (typeof value !== 'undefined' && !$.isFunction (value)) { // Пишем
        
        if (typeof options.expires === 'number') {
          
          var
          days = options.expires,
          t = options.expires = new Date ();
          
          t.setTime (+ t + days * 864e+5);
          
        }
        
        var result = (document.cookie = [
          
          encode (key),
          '=',
          stringifyCookieValue (value),
          options.expires ? '; expires=' + options.expires.toUTCString () : '',
          options.path    ? '; path=' + options.path : '',
          options.domain  ? '; domain=' + options.domain : '',
          options.secure  ? '; secure' : ''
          
        ].join (''));
        
      } else { // Читаем
        
        var result = [];
        var cookies = document.cookie ? document.cookie.split ('; ') : [];
        
        for (var i = 0, l = cookies.length; i < l; i++) {
          
          var
          parts = cookies[i].split ('='),
          name = decode (parts.shift ()),
          cookie = parts.join ('=');
          
          if (key && key === name) { // Указан ключ
            
            if (options.raw) cookie = parseCookieValue (cookie);
            
            if (cookie == 'true') cookie = true;
            else if (cookie == 'false') cookie = false;
            
            var result = cookie;
            
            break;
            
          }
          
          if (!key && (cookie = read (cookie)) !== undefined)
          result[name] = cookie;
          
        }
        
      }
      
      return result;
      
    };
    
  })($);