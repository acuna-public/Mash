/*
 Form 1.0 - jQuery plugin
 written by O! Interactive, Acuna
 http://ointeractive.ru
 
 Copyright (c) 2014,2016 O! Interactive, Acuna (http://ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 Dependencies: none
 
 1.0  06.04.2016
  Первый приватный релиз
  
 */
  
  (function ($) {
    
    $.fn.form = function (options) {
      
      options = $.extend ({
        
        'success': function () {},
        
      }, options);
      
      $(this).each (function () {
        
        $(this).on ('submit', function (event) {
          
          var datas = {}, data = {}, elem = $(this);
          
          event.preventDefault ();
          event.stopImmediatePropagation ();
          
          elem.find ('input').each (function () {
            
            var
            name = $(this).attr ('name'),
            value = $(this).value ();
            
            if ($.type (name) != 'undefined')
            datas[name] = value;
            
          });
          
          elem.find ('textarea').each (function () {
            
            var
            name = $(this).attr ('name'),
            value = $(this).value ();
            
            if ($.type (name) != 'undefined')
            datas[name] = value;
            
          });
          
          elem.find ('select').each (function () {
            
            var
            name = $(this).attr ('name'),
            value = $(this).value ();
            
            if ($.type (name) != 'undefined')
            datas[name] = value;
            
          });
          
          var data_names = {};
          
          $.each (datas, function (key, value) {
            
            if (match = key.match (/(.+?)\[(.*?)\]/)) {
              
              if (match[2]) {
                
                data_names[match[2]] = value;
                data[match[1]] = data_names;
                
              } else {
                
                var data_names2 = {};
                
                $.each (value, function (key2, value2) {
                  data_names2[key2] = value2;
                });
                
                data[match[1]] = data_names2;
                
              }
              
            } else data[key] = value;
            
          });
          
          options.success (elem, data);
          
        });
        
      });
      
    };
    
  })($);