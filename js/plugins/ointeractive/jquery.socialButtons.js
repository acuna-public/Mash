/*
 socialButtons 1.0 - jQuery plugin
 written by O! Interactive, Acuna
 http://ointeractive.ru
 
 Copyright (c) 2015 O! Interactive, Acuna (http://ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 Dependencies: none
 
 1.0  02.03.2015
  Первый приватный релиз
  
 1.1  08.04.2015
  + Добавлен вызов попапов для шаринга
  / Счетчики Odnoklassniki и Google+
  + Кнопка LinkedIn
  
 */
  
  (function ($) {
    
    $.fn.socialButtons = function (options) {
      
      var options = $.extend ({
        
        'theme': 'default', // default, default-large
        'display': 'inline', // inline, col
        'style': 'logo,count', // logo; logo,count; logo,title; logo,title,count;
        'cellColorStyle': '10',
        'countStyle': 'static', // static, animated
        'countAnimateSpeed': 2,
        'timeout': 3000,
        'iconsDir': '',
        'services': 'vk,facebook,moymir,ok,twitter,google,pinterest',
        
      }, options);
      
      $(this).each (function () {
        
        $(this).append ('<ul class="plugin-social-buttons ' + options['display'] + '"></ul>');
        var elem = $('.plugin-social-buttons');
        
        if (options['theme'] != 'default') elem.addClass (options['theme']);
        
        var lang = {
          
          'vk': 'В Контакте',
          'facebook': 'Facebook',
          'moymir': 'Мой мир',
          'rss': 'RSS',
          'ok': 'Одноклассники',
          'twitter': 'Twitter',
          'google': 'Google+',
          'pinterest': 'Pinterest',
          'linkedin': 'LinkedIn',
          
        };
        
        if (!$.isArray (options['services']))
        options['services'] = options['services'].split (',');
        
        for (var i = 0; i < options['services'].length; ++i) {
          
          var itemOptions = $.extend ({
            
            'name': '',
            'count': 0,
            'title': '',
            'shareUrl': '',
            'callback': function () {},
            
          }, options['services'][i]);
          
          if (!itemOptions['name'])
          itemOptions['name'] = options['services'][i];
          
          elem.append ('<li class="elem' + i + '"></li>');
          $('.elem' + i).socialButtonsWrapper ($(this), options, itemOptions, lang);
          
        }
        
      });
      
    };
    
    $.fn.socialButtonsWrapper = function (self, options, itemOptions, lang) {
      
      var
      elem = $(this),
      rows = options['cellColorStyle'].split ('');
      
      var showCount = function (count) {
        
        if (rows.length > 1) {
          
          var displayCount = '';
          count = parseInt (count, 10);
          
          if (count > 1000000) {
            
            count = count / 1000000;
            count = count.toFixed (1);
            displayCount = count + ' M';
            
          } else if (count > 1000) {
            
            count = count / 1000;
            count = count.toFixed (1);
            displayCount = count + ' K';
            
          } else displayCount = count;
          
          if (count > 0 && (styles[0] == 'count' || styles[1] == 'count' || styles[2] == 'count')) {
            
            if (rows[1] == 0)
            elem.append ('<span class="button no-color">' + displayCount + '</span>');
            else
            elem.append ('<span class="button color ' + itemOptions['name'] + '">' + displayCount + '</span>');
            
          }
          
        }
        
      };
      
      var countAnimate = function (start, stop, step) {
        
        var count = 0;
        
        if (start + step >= stop) {
          
          count = stop;
          showCount (count);
          
        } else {
          
          count = start + step;
          showCount (count);
          
          setTimeout (function () {
            countAnimate (start + step, stop, step)
          }, options['countAnimateSpeed']);
          
        }
        
      };
      
      var showData = function (count) {
        
        if (options['countStyle'] == 'animated') {
          
          var stop = parseInt (count, 10);
          step = Math.round (stop / 50);
          
          countAnimate (0, stop, step);
          
        } else showCount (count);
        
      };
      
      var getData = function (url, success, type) {
        
        if (!type) type = 'jsonp';
        
        $.ajax ({
          
          'url': url,
          'timeout': options['timeout'],
          'dataType': type,
          'success': success,
          
        });
        
      };
      
      // Buttons
      
      if (rows[0] == 0)
      elem.append ('<span class="button no-color"></span>');
      else
      elem.append ('<span class="button color ' + itemOptions['name'] + '"></span>');
      
      var styles = options['style'].split (',');
      
      if (styles[0] == 'logo')
      elem.find ('.button:first').addClass ('image').html ('<img src="' + options['iconsDir'] + '/' + itemOptions['name'] + '.png' + '"/>');
      
      if (!itemOptions['title'])
      itemOptions['title'] = lang[itemOptions['name']];
      
      if (styles[0] == 'title')
      elem.find ('.button:first').html (itemOptions['title']);
      
      if (styles[0] == 'logo' && styles[1] == 'title' && !styles[2])
      elem.find ('.button:last').html (itemOptions['title']);
      
      //if (styles[0] == 'logo' && styles[1] == 'title' && styles[2])
      //elem.find ('.cell-1').html (itemOptions['title']);
      
      // Services
      
      var
      url = self.data ('url'),
      title = encodeURI (self.data ('title'));
      
      switch (itemOptions['name']) {
        
        default:
          
          if (counts = self.data ('counts')) {
            
            counts = jQuery.parseJSON (decodeURIComponent (counts));
            if (count = counts[itemOptions['name']])
            itemOptions['count'] = count;
            
          }
          
          showData (itemOptions['count']);
          
          width = 626;
          height = 436;
          
        break;
        
        case 'vk':
          
          // Thanks to Dimox
          
          getData ('https://vk.com/share.php?act=count&url=' + url);
          
          if (!window.VK) window.VK = {};
          
          window.VK.Share = {
            
            count: function (idx, num) {
              showData (num);
            }
            
          };
          
          //debug ('https://vk.com/share.php?act=count&url=' + url + '');
          
          itemOptions['shareUrl'] = 'https://vk.com/share.php?url=' + url + '&title=' + title;
          
          width = 550;
          height = 330;
          
        break;
        
        case 'facebook':
          
          getData ('http://graph.facebook.com/' + url, function (c) {
            showData (c.comments);
          });
          
          //debug ('http://graph.facebook.com/' + url);
          
          itemOptions['shareUrl'] = 'https://www.facebook.com/sharer/sharer.php?u=' + url;
          
          width = 600;
          height = 250;
          
        break;
        
        case 'twitter':
          
          /*getData ('http://cdn.api.twitter.com/1/urls/count.json?url=' + url + '&callback=?', function (c) {
            showData (c.count);
          });*/
          
          itemOptions['shareUrl'] = 'https://twitter.com/intent/tweet?url=' + url + '&text=' + title;
          
          width = 600;
          height = 260;
          
        break;
        
        case 'moymir':
          
          getData ('http://connect.mail.ru/share_count?url_list=' + url + '&callback=1&func=?', function (c) {
            if (c = c[url]) showData (c.shares);
          });
          
          itemOptions['shareUrl'] = 'http://connect.mail.ru/share?share_url=' + url + '&title=' + title;
          
          width = 550;
          height = 360;
          
        break;
        
        case 'ok':
          
          // Thanks to Dimox
          
          getData ('http://www.odnoklassniki.ru/dk?st.cmd=extLike&ref=' + url);
          
          if (!window.ODKL) window.ODKL = {};
          
          window.ODKL.updateCount = function (idx, num) {
            showData (num);
          };
          
          //debug ('https://share.yandex.net/counter/odnoklassniki/?url=' + url);
          
          itemOptions['shareUrl'] = 'http://connect.ok.ru/dk?st.cmd=WidgetSharePreview&service=odnoklassniki&st.shareUrl=' + url + '&st.utext=' + title;
          
          width = 550;
          height = 360;
          
        break;
        
        case 'google':
          
          getData ('http://share.yandex.ru/gpp.xml?url=' + url);
          
          if (!window.services) window.services = {};
          
          window.services.gplus = {
            
            cb: function (num) {
              showData (num);
            }
            
          };
          
          itemOptions['shareUrl'] = 'https://plus.google.com/share?url=' + url + '&hl=' + self.data ('lang');
          
          width = 700;
          height = 500;
          
        break;
        
        case 'pinterest':
          
          getData ('https://api.pinterest.com/v1/urls/count.json?url=' + url + '&callback=?', function (c) {
            showData (c.count);
          });
          
          itemOptions['shareUrl'] = 'https://pinterest.com/pin/create/button/?url=' + url + '&description=' + title;
          
          width = 630;
          height = 270;
          
        break;
        
        case 'linkedin':
          
          getData ('http://www.linkedin.com/countserv/count/share?format=jsonp&callback=?&url=' + url, function (c) {
            showData (c.count);
          });
          
          //debug ('http://www.linkedin.com/countserv/count/share?format=json&url=' + url);
          
          itemOptions['shareUrl'] = 'http://www.linkedin.com/shareArticle?source=&title=' + title + '&url=' + url;
          
          width = 626;
          height = 436;
          
        break;
        
        case 'stumbleupon':
          
          getData ('https://www.stumbleupon.com/services/1.01/badge.getinfo?&format=jsonp&url=' + url, function (c) {
            showData (c.count);
          });
          
          itemOptions['shareUrl'] = '';
          
          width = 626;
          height = 436;
          
        break;
        
      }
      
      if (itemOptions['shareUrl'])
      elem.wrap ('<a href="' + itemOptions['shareUrl'] + '" onclick="popup(\'' + itemOptions['shareUrl'] + '\', ' + width + ', ' + height + '); return false;" title="' + itemOptions['title'] + '"></a>');
      
    };
    
  })($);