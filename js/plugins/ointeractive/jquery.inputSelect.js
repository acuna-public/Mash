/*
 InputSelect 1.2 - jQuery plugin
 written by O! Interactive, Acuna
 http://ointeractive.ru
 
 Copyright (c) 2014-2015 O! Interactive, Acuna (http://ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 Dependencies: jquery.fastSearch
 
 */
  
  (function ($) {
    
    $.fn.inputSelect = function (options) {
      
      options = $.extend ({
        
        'autocompleteUrl': '',
        'autocompleteData': [],
        'autocompleteClick': function () {},
        'disabled': false,
        'inputName': '',
        'img_active': '',
        'delay': 100,
        'minLength': 1,
        'hideOverflow': false,
        'nextElem': '',
        
      }, options);
      
      $(this).each (function () {
        
        var
        self = $(this),
        wrapper = $('<div class="inputselect"></div>');
        
        self.append (wrapper);
        
        var
        this_id = self.data ('id'),
        this_title = self.data ('title'),
        
        input = $('<input type="text" value=""/>'),
        input_hidden = $('<input type="hidden" name="' + options.inputName + '" id="' + options.inputName + '" value=""/>');
        
        wrapper.after (input_hidden);
        
        if (this_title) input.val (this_title);
        
        if (this_id) {
          
          input_hidden.val (this_id);
          //options.disabled = false;
          
        }
        
        input.on ('change', function () {
          options.disabled = false;
        });
        
        if (options.disabled)
        input.prop ('disabled', true)
        .addClass ('empty');
        
        wrapper.append (input);
        
        var image = '<img alt="" src="' + options.imgInactive + '" style="vertical-align:middle;"/>';
        var image_active = '<a href="#">' + image + '</a>';
        
        img_active = '';
        
        if (options.disabled)
        img_active = $('<span class="image">' + image + '</span>');
        else
        img_active = $('<span class="image">' + image_active + '</span>');
        
        input.after (img_active);
        
        var result = $('<div class="inputselect-autocomplete autocomplete"></div>');
        
        self.append (result);
        
        var clear_input = function () {
          
          input.val ('');
          input_hidden.val ('');
          
          $(options.nextElem).find ('input').prop ('disabled', true);
          $(options.nextElem).find ('a').replaceWith (image);
          
        };
        
        input.on ('keyup', function () {
          if (!$(this).val ()) clear_input ();
        });
        
        var complete = function (self, elem) {
          
          self.find ('li').unbind ('click')
          .click (function () {
            
            var id = $(this).data ('id');
            
            input.val ($(this).text ());
            input_hidden.val (id);
            
            if (options.nextElem)
            $(options.nextElem).html ('');
            
            options.autocompleteClick ();
            if (!id) clear_input ();
            
            //if (options.nextElem)
            //$(options.nextElem).find ('input').val ('');
            
            result.fadeOut ();
            
            if (options.hideOverflow)
            overflow (1);
            
            return false;
            
          });
          
        };
        
        wrapper.find ('a').unbind ('click')
        .click (function () {
          
          $('.inputselect-autocomplete').fadeOut ();
          
          $(this).fastSearch ({
            
            'action': 'click',
            'file': options.autocompleteUrl,
            'data': options.autocompleteData,
            'result': result,
            'delay': options.delay,
            'minLength': options.minLength,
            'complete': complete,
            'hideOverflow': options.hideOverflow,
            
          });
          
          return false;
          
        });
        
        input.fastSearch ({
          
          'file': options.autocompleteUrl,
          'data': options.autocompleteData,
          'result': result,
          'delay': options.delay,
          'minLength': options.minLength,
          'complete': complete,
          'hideOverflow': options.hideOverflow,
          
        });
        
      });
      
    };
    
  })($);