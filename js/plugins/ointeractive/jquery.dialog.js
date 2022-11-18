/*
 prettyDialog 1.1 - jQuery extension
 written by O! Interactive, Acuna
 http://ointeractive.ru
 
 Copyright (c) 2015 O! Interactive, Acuna (http://ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 1.0  21.05.2015
  Первый приватный релиз
  
 1.1  08.09.2015
  Реализована возможность вывода собственной функции в случае успешного создания диалогового окна.
  
 */
  
  (function ($) {
    
    $.dialog = function (options, lang) {
      
      options = $.extend ({
        
        'type': 'alert',
        'shade': true,
        'open': function () {},
        'class': 'pretty',
        'width': 460,
        'opacity': 0.7,
        'opacityColor': '#000',
        'draggable': false,
        'titleClose': true,
        'outClose': false,
        'buttons': {},
        'scroll': false,
        'maxHeight': 600,
        
      }, options);
      
      if ($.type (options['oblige']) == 'undefined') options['oblige'] = true;
      
      var notEmpty = function (text) {
        return ((options['oblige'] && text) || !options['oblige']);
      };
      
      if (!lang) lang = {
        
        1: 'OK',
        2: 'Отмена',
        
      };
      
      if ($.type (options['content']) == 'undefined') options['content'] = '';
      
      if (options['type'] == 'prompt') {
        
        if (options['readOnly'])
        options['readOnly'] = ' onclick="this.select ();" readonly="readonly"';
        
        if ($.type (options['value']) == 'undefined') options['value'] = '';
        
        options['content'] = '<div class="prompt-content">' + options['content'] + '</div><div class="prompt-input"><input type="text" name="' + text + '" id="' + text + '" value="' + options['value'] + '"' + options['readOnly'] + '/></div>';
        
      }
      
      if (!options['elem'])
      options['elem'] = 'popup_' + new Date ().getTime ();
      
      $.opacity (options['opacity'], options['opacityColor'], options['class'], options['elem']);
      
      var text = options['elem'] + '_text';
      $('#' + options['elem']).remove ();
      
      $('body').append ('<div class="dialog ' + options['class'] + '" id="' + options['elem'] + '"></div>');
      
      var elem = $('#' + options['elem']);
      
      elem
        .append ('<div class="dialog-header">' + options['title'] + '\
  <div class="close-button">\
    <div class="ui-widget-header ui-icon ui-icon-closethick"></div>\
  </div>\
</div>')
        .append ('<div class="dialog-content ' + options['type'] + '">' + options['content'] + '</div>');
      
      var tabs = elem.find ('.ui-tabs-content');
      
      if (tabs.length) {
        
        var menu = tabs.find ('ul').eq (0);
        elem.find ('.dialog-header').html (menu);
        //menu.remove ();
        
        $(menu.find ('li').eq (0)).addClass ('active');
        
        $(menu.find ($('li a')).eq (0).attr ('href')).siblings ().each (function () {
          $(this).hide ();
        });
        
        menu.find ('li').each (function () {
          
          var el = $(this);
          
          el.hover (function () {
            $(this).css ('color', '#000');
          });
          
          el.click (function () {
            
            $(this).siblings ().each (function () {
              
              $($(this).find ('a').attr ('href')).hide ();
              $(this).removeClass ('active');
              
            });
            
            $($(this).find ('a').attr ('href')).show ();
            $(this).addClass ('active');
            
          });
          
        });
        
      }
      
      if (options['submit']) {
        
        if ($.isFunction (options['cancel']))
        options['buttons']['cancel'] = {
          
          'text': lang[2],
          
          'click': function () {
            
            var value = $('#' + text).val ();
            if ((options['cancel'] && options['cancel'] (value, '#' + options['elem'], text, options)) || !options['cancel']) {
              
              options['scroll'] = false;
              $.dialogClose (options);
              
            }
            
            return false;
            
          }
          
        };
        
        options['buttons']['submit'] = {
          
          'text': lang[1],
          
          'click': function () {
            
            var value = $('#' + text).val ();
            
            if (notEmpty (value) || $.type (value) == 'undefined') {
              
              if ((options['submit'] && options['submit'] (value, '#' + options['elem'], text, options) == true) || $.type (options['submit']) == 'undefined')
              $.dialogClose (options);
              
            } else if (options['type'] == 'prompt' && options['oblige'])
            elem.find ('#' + text).addClass ('ui-state-error');
            
            return false;
            
          }
          
        };
        
      }
      
      if (Object.keys (options['buttons']).length) {
        
        var actions = ['click'];
        elem.append ('<div class="dialog-buttons"></div>');
        
        $.each (options['buttons'], function (key, value) {
          
          elem.find ('.dialog-buttons').append ('<div class="dialog-button ' + key + (value['class'] ? ' ' + value['class'] : '') + '">' + value['text'] + '</div>');
          
          $.each (value, function (key2, value2) {
            
            if (value[key2])
            elem.find ('.dialog-button.' + key + (value['class'] ? '.' + value['class'] : '')).on (key2, function () {
              value[key2] ();
            });
            
          });
          
        });
        
      }
      
      if (options['subClass']) elem.addClass (options['subClass']);
      
      if (options['titleClose'])
      elem.find ('.dialog-header .close-button').click (function () {
        $.dialogClose (options);
      });
      
      if (options['outClose'])
      $('body').click (function () {
        $.dialogClose (options);
      });
      
      if ($.isFunction (options['onScrollBottom']))
      elem.find ('.dialog-content').scrollEnd (function () {
        options['onScrollBottom'] ('#' + options['elem'], options);
      }, { 'end':200 });
      
      if (!options['scroll']) $('html,body').css ('overflow', 'hidden');
      if (options['open']) options['open'] ('#' + options['elem'], options);
      
      var height;
      if (options['height']) height = options['height']; else height = elem.outerHeight ();
      
      if (options['maxHeight'] && elem.height () >= $(window).height ()) {
        
        options['height'] = (options['maxHeight'] - elem.find ('.dialog-header').height () - elem.find ('.dialog-buttons').height ());
        
        elem.find ('.dialog-content').css ({ 'height':options['height'] + 'px' });
        elem.css ({ 'height':options['maxHeight'] + 'px' });
        
      }
      
      elem.css ({
        
        'top': '50%',
        'left': '50%',
        'width': options['width'] + 'px',
        'margin-left': -(options['width'] / 2) + 'px',
        'margin-top': ($(window).scrollTop () - (height / 2)) + 'px',
        
      });
      
      if (options['height']) {
        
        options['height'] = (height - elem.find ('.dialog-header').height () - elem.find ('.dialog-buttons').height ());
        
        elem.find ('.dialog-content').css ({ 'height':options['height'] + 'px' });
        
        elem.css ({
          'margin-top': ($(window).scrollTop () - (elem.outerHeight () / 2)) + 'px'
        });
        
      }
      
      elem.find ('.dialog-content').onResize (function (el) {
        el.css ({ 'height':el.height () + 'px' });
      });
      
      /*elem.dialog ({
        
        'create': function () {
          
          if (!options['scroll']) $('html,body').css ('overflow', 'hidden');
          if (options['open']) options['open'] (this, options);
          
        },
        
        'title':options['title'], 'show':'fade', 'hide':'fade', 'width':options['width'], 'height':options['height'], 'overflow':'scroll', 'modal':options['shade'], 'dialogClass':options['class'], 'buttons':buttons, 'open':open, 'close':close, 'position':position, 'draggable':options['draggable'],
        
      });
      
      /*if (options['shade'])
      elem.dialog ('widget')
      .next ('.ui-widget-overlay')
      .addClass (options['class']);*/
      
      //elem.draggable ();
      
    };
    
    $.dialogClose = function (options) {
      
      var elem = '#' + options['elem'];
      
      if (!options['scroll'] || options['hideScroll'])
      $('html,body').css ('overflow', 'auto');
      
      if (!options['closed']) {
        
        $(elem).remove ();
        
        if ($.type (options['close']) == 'function')
        options['close'] (elem);
        
      }
      
      if (options['opacity'] || $.type (options['opacity']) == 'undefined') {
        
        if (options['elem'])
        $.opacityRemove (options['elem']);
        else
        $('.opacity').remove ();
        
      }
      
    };
    
  })($);