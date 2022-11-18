/*
 tagsInput 1.2 - jQuery plugin.
 written by O! Interactive, Acuna
 http://ointeractive.ru
 
 Copyright (c) 2014-2015 O! Interactive, Acuna (http://ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 Dependencies: jQuery.fastSearch
 
 1.0  07.12.2014
  Первый публичный релиз
  
 1.1  31.03.2015
  + Добавлена поддержка числовых идентификаторов
  
 1.2  23.04.2015
  + Добавлена возможность ввода кастомных идентификаторов и заголовков
  
 */
  
  (function ($) {
    
    $.fn.tagsInput = function (options) {
      
      options = $.extend ({
        
        'autocompleteUrl': '',
        'autocompleteData': [],
        'dataUrl': '',
        'data': [],
        'defaultText': '',
        'delay': 100,
        'minLength': 1,
        'paired': false,
        'closeImg': 'close.png',
        'inputName': 'tags',
        'inputText': 'Начните вводить слово',
        'max': 0,
        'thisClass': 'normal',
        'autocompleteHideOverflow': false,
        
      }, options);
      
      $(this).each (function () {
        
        var self = $(this), insert_num = 0, tags = [];
        
        options.max = (options.max - 1);
        if (options.max < 0) options.max = 0;
        
        var key = {
          
          'backspace': 8,
          'tab': 9,
          'enter': 13,
          'escape': 27,
          'space': 32,
          'page_up': 33,
          'page_down': 34,
          'end': 35,
          'home': 36,
          'left': 37,
          'up': 38,
          'right': 39,
          'down': 40,
          'numpad_enter': 108,
          'comma': 188,
          'del': 46,
          
        };
        
        var data = {
          
          'ids': String (self.data ('ids')),
          'values': String (self.data ('values')),
          
        };
        
        var wrapper = $('<div class="tagsinput-wrapper ' + options.thisClass + '"></div>').css ({ 'overflow':'hidden', 'height':'auto', });
        self.append (wrapper);
        
        var elem = $('<div class="tagsinput ' + options.thisClass + '"></div>');
        wrapper.append (elem);
        
        var input = $('<input type="text" value="' + options.inputText + '" class="input" id="' + options.inputName + '" autocomplete="off"/>').css ({ 'width':'auto', 'border':'none' });
        wrapper.append (input);
        
        var input_resizer = $('<tester/>')
        .css ({
          
          'position': 'absolute',
          'top': -9999,
          'left': -9999,
          'width': 'auto',
          'whiteSpace': 'nowrap'
          
        });
        
        wrapper.append (input_resizer);
        
        var input_hidden = $('<input type="hidden" name="' + options.inputName + '" value=""/>');
        wrapper.after (input_hidden);
        
        var sub_id = $('<input type="hidden" value=""/>');
        wrapper.after (sub_id);
        
        var result = $('<div class="tagsinput-autocomplete autocomplete"></div>');
        self.append (result);
        
        var add_tag = function (elem, value, id, not_empty) {
          
          if (value) {
            
            value = value.trim ();
            
            if (value && $.inArray (value, tags) === -1 && insert_num <= options.max) {
              
              sub_id.val (insert_num);
              
              elem.append ('<span id="tag-' + insert_num + '" data-id="' + id + '" data-tag-id="' + insert_num + '">' + value + '<a href="#" class="del"><img alt="" src="' + options.closeImg + '"/></a></span>');
              
              tags[insert_num] = id;
              input_hidden.val (tags);
              //alert (input_hidden.val ());
              
              elem.find ('a.del').unbind ('click')
              .click (function () {
                
                clear_input ();
                remove_tag (tags, elem, $(this).parent ().data ('tag-id'));
                return false;
                
              });
              
              insert_num++;
              if (!not_empty) input.val ('');
              
            }
            
            input.width (input_resizer.width () + 30);
            resize_area ();
            
            elem.addClass ('active');
            
          }
          
        };
        
        var resize_area = function () {
          
          if ((wrapper[0].clientHeight < wrapper[0].scrollHeight) || (wrapper[0].clientWidth < wrapper[0].scrollWidth))
          wrapper.css ({ 'height':(self.height () + 15) });
          
        };
        
        var clear_input = function (text) {
          
          if (typeof text == 'undefined') text = options.inputText;
          
          input.val (text)
          .addClass ('clear')
          .css ({ 'width':'auto' })
          .removeClass ('active');
          
        };
        
        var move_tag = function (type, elem) {
          
          elem = $(elem);
          
          var id = sub_id.val ();
          if (id <= 0) id = 0;
          if (id >= insert_num) id = (insert_num - 1);
          
          elem.find ('span').siblings ().removeClass ('selected');
          elem.find ('#tag-' + id).addClass ('selected');
          
          if (type == 'left') sub_id.val (--id);
          else if (type == 'right') sub_id.val (++id);
          
        };
        
        var remove_tag = function (tags, elem, id) {
          
          delete tags[id];
          
          input_hidden.val (tags);
          elem.find ('#tag-' + id).remove ();
          //$('.result').text (input_hidden.val ());
          --insert_num;
          
        };
        
        var complete = function (self, elem) {
          
          self.find ('li').unbind ('click')
          .click (function () {
            
            add_tag (elem, $(this).find ('span').text (), $(this).data ('id'));
            
            $(result).fadeOut ();
            overflow (1);
            clear_input ('');
            input.focus ();
            
            return false;
            
          });
          
        };
        
        if (data['ids'] && data['values'] && data['ids'] != 'undefined' && data['values'] != 'undefined') {
          
          var ids = data['ids'].split (',');
          var values = data['values'].split (',');
          
          $.each (values, function (i, data) {
            add_tag (elem, data, ids[i], 1);
          });
          
        }
        
        if (options.data_url) {
          
          $.getJSON (options.data_url, options.data, function ($json) {
            
            $.each ($json, function (i, data) {
              add_tag (elem, data, 0, 1);
            });
            
          });
          
        }
        
        input.on ('keyup keydown blur update', function () {
          
          var value = $(this).val ();
          
          if (value.match (/[A-z0-9]+/g)) {
            
            var escaped = value.replace (/&/g, '&amp;').replace (/\s/g, ' ').replace (/</g, '&lt;').replace (/>/g, '&gt;');
            input_resizer.html (escaped);
            input.width (input_resizer.width () + 30).addClass ('active');
            resize_area ();
            
          }
          
        }).on ('keydown', function (e) {
          
          var value = $(this).val ();
          
          switch (e.which) {
            
            case key.tab:
            case key.right:
            //case key.numpad_enter:
            case key.comma:
              
              if ((options.paired && options.autocompleteUrl) || !options.autocompleteUrl)
              add_tag (elem, value);
              
            break;
            
            /*case key.up:
              move_tag ('left', elem);
            break;
            
            case key.down:
              move_tag ('right', elem);
            break;
            
            case key.escape:
            case key.left:
            case key.del:
              
              --insert_num;
              remove_tag (tags, elem, insert_num);
              
            break;*/
            
          }
          
        }).click (function () {
          $(this).removeClass ('clear').val ('');
        }).on ('blur', function () {
          
          var value = $(this).val ();
          
          if (options.paired)
          add_tag (elem, value);
          else
          clear_input ();
          
        });
        
        if (options.autocompleteUrl) input.fastSearch ({
          
          'action': 'keyup',
          'file': options.autocompleteUrl,
          'data': options.autocompleteData,
          'hideOverflow': options.autocompleteHideOverflow,
          'result': result,
          'delay': options.delay,
          'minLength': options.minLength,
          'complete': complete,
          'addElem': elem,
          
        });
        
      });
      
    };
    
  })($);