/*
 uploaderList 1.0 - jQuery plugin
 written by O! Interactive, Acuna
 http://ointeractive.ru
 
 Copyright (c) 2016 O! Interactive, Acuna (http://ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 Dependencies: jquery.ui.touch, jquery.ui.sortable
 
 1.0  06.11.2016
  Первый приватный релиз
  
 */
  
  (function ($) {
    
    $.fn.uploaderList = function (options) {
      
      options = $.extend ({
        
        'elem': 'uploader-list',
        'content': {},
        
        'onClick': function () {},
        'onDelete': function () {},
        'startFrom': 1,
        
        'sortableOnStart': function () {},
        'sortableOnStop': function () {},
        'sortableOnUpdate': function () {},
        'sortableOnTouch': function () {},
        
      }, options);
      
      var
      self = this,
      elem = $(self).find ('.' + options['elem']),
      image, images = '';
      
      if (!elem.length)
      $(self).append ('<ul class="' + options['elem'] + '"></ul>');
      
      elem = $(self).find ('.' + options['elem']);
      if (options['class']) elem.addClass (options['class']);
      
      $.each (options['content'], function (i, value) {
        
        if (isUrl (value['image']))
        image = '<a href="' + value['image'] + '" class="image-link"><img alt="" src="' + value['thumb'] + '" class="image"/></a><div class="delete-link"><a href="#"></a></div>'; else image = value['image'];
        
        images += '<li data-id="' + (parseInt (i) + parseInt (options['startFrom'])) + '"' + (value['id'] ? ' data-item-id="' + value['id'] + '"' : '') + '>' + image + '</li>';
        
      });
      
      elem.html (images);
      
      elem.find ('li').each (function (i) {
        
        var el = this;
        
        $(this).find ('.image-link').click (function () {
          options['onClick'] (this, i);
        });
        
        $(this).find ('.delete-link').click (function () {
          options['onDelete'] (this, i);
        });
        
      });
      
      if (options['sortableOnStart'] || options['sortableOnStop'] || options['sortableOnUpdate'])
      elem.sortable ({
        
        'start': function (event, ui) {
          
          var oldPos = $(ui.item).data ('id');
          options['sortableOnStart'] (event, ui, oldPos);
          
        },
        
        'stop': function (event, ui) {
          
          var oldPos = $(ui.item).data ('id');
          var newPos = (ui.item.index () + parseInt (options['startFrom']));
          
          if (newPos == oldPos)
          options['sortableOnTouch'] (event, ui, newPos);
          else
          options['sortableOnStop'] (event, ui, newPos, oldPos);
          
        },
        
        'update': function (event, ui) {
          
          var oldPos = $(ui.item).data ('id');
          var newPos = ui.item.index ();
          
          if (newPos != oldPos)
          options['sortableOnUpdate'] (event, ui, newPos, oldPos);
          
        },
        
      });
      
      if ($.isFunction (options['onOpen']))
      options['onOpen'] (self, options);
      
    };
    
  })($);