/*
 Strength 1.0 - jQuery plugin
 written by O! Interactive, Acuna
 http://ointeractive.ru
 
 Copyright (c) 2014 O! Interactive, Acuna (http://ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 Dependencies: none
 
 */
  
  (function ($) {
    
    $.fn.strength = function (options) {
      
      var defaults = {
        
        'target': '',
        'lang': ['Очень плохой', 'Плохой', 'Нормальный', 'Хороший', 'Отличный'],
        'showWords': 1,
        'showPercents': 0,
        
      }, self = this,
      
      defaultClasses = ['lowercase', 'uppercase', 'numeric', 'symbolsCommon', 'symbolsUncommon'],
      symbolsCommon = ' ,.?!',
      symbolsUncommon = '"£$%^&*()-_=+[]{};:\'@#~<>/\\|`¬¦';
      
      options = $.extend (defaults, options);
      
      var classesArray = {
        
        lowercase: { 'regex':/[a-z]/, 'size':26 },
        uppercase: { 'regex':/[A-Z]/, 'size':26 },
        numeric: { 'regex':/[0-9]/, 'size':10 },
        symbols: { 'characters':symbolsCommon + symbolsUncommon },
        symbolsCommon: { 'characters':symbolsCommon },
        symbolsUncommon: { 'characters':symbolsUncommon },
        hexadecimal: { 'regex':/[a-fA-F0-9]/, 'size':16 }
        
      };
      
      var classes = [];
      
      for (var i = 0; i < defaultClasses.length; ++i)
      classes.push (typeof defaultClasses[i] === 'string' ? classesArray[defaultClasses[i]] : defaultClasses[i]);
      
      var evaluateClass = function (charClass, password) {
        
        var chars, i;
        
        if (charClass.regex && charClass.regex.test (password))
        return charClass.size;
        else if (charClass.characters) {
          
          chars = charClass.characters;
          
          for (i = 0; i < chars.length; i++) {
            
            if (password.indexOf (chars[i]) > -1)
            return chars.length;
            
          }
          
        }
        
        return 0;
        
      };
      
      var
      langLength = options.lang.length,
      step = (100 / langLength);
      
      self.keyup (function () {
        
        var str = $(this).val (), alphabetSize = 0, word = '', nNum = 0, percent = 0;
        
        if (str) {
          
          for (i = 0; i < classes.length; ++i)
          alphabetSize += evaluateClass (classes[i], str);
          
          if (alphabetSize > 0) {
            
            percent = Math.log (alphabetSize) / Math.log (2) * str.length;
            percent = Math.min (1, percent / 100) * 100;
            percent = Math.round (percent);
            
            for (var i = 0; i < langLength; ++i)
            if (percent >= (step * i) && percent < (step * (i + 1))) {
              
              word = options.lang[i];
              nNum = nNum + (i + 1);
              
            } else if (percent < step && percent > 0) word = options.lang[0];
            
            if (percent >= 100) {
              
              word = options.lang[(langLength - 1)];
              nNum = langLength;
              
            }
            
          }
          
        }
        
        var output = '';
        
        if (options.showWords) output = word;
        else if (options.showPercents) output = percent + '%';
        
        var width = $('.strength-progress').width ();
        width = ((Math.ceil (width / 2)) - percent);
        
        $(options.target).html ('<div class="strength-progress step-' + nNum + '"><span style="width:' + percent + '%;"><p style="padding-left:' + width + 'px;">' + output + '</p></span></div>');
        
      });
      
    };
    
  })($);