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
  
  $.fn.toggle = function (options) {
    
    options = $.extend ({
      
      'elem': 'li',
      'class': 'active',
			
    }, options);
    
    var self = $(this);
    
    if (!$.isArray (options.elem))
      options.elem = [options.elem];
    
    $.each (options.elem, function (key, value) {
      
      self.find (value).click (function () {
        
        self.find ('.' + options.class).removeClass (options.class);
        $(this).parent ().addClass (options.class);
        
        var currentTab = $(this).attr ('href');
        
        $(currentTab).siblings ().hide ();
        $(currentTab).show ();
        
        $('.ajax-show').show ();
        
        //$('.item-body .item-list-tabs.' + currentTab.replace ('#', '')).show ();
        
        // Let's do some thrash by Soundarea)
        
        //currentTab = $(this).parent ().parent ().parent ().attr ('id');
        //if (currentTab) $('.' + currentTab).show ();
        //if (id) $('.' + id).show ();
        
        //$(this).parent ().parent ().parent ().show ();
        
        return false;
        
      });
      
    });
    
  };