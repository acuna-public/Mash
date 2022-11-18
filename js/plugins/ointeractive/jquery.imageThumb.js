/*
 imageThumb 1.1 - jQuery plugin
 written by O! Interactive, Acuna
 http://ointeractive.ru
 
 Copyright (c) 2016 O! Interactive, Acuna (http://ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 Dependencies: jquery.baloon
 
 1.0  16.11.2016
  Первый приватный релиз
  
 1.1  05.12.2016
  Реализована возможность вывода собственого контента
  
 */
  
  (function ($) {
    
    $._imageThumb = function (options, nested, self) {
      
      options = $.extend ({
        
        'onBeforeOpen': function () {},
        'onAfterOpen': function () {},
        'onLoad': function () {},
        'onOpen': function () {},
        
        'prev': function () {},
        'next': function () {},
        
        'class': 'image-thumb-area',
        'closeOut': true,
        'caption': true,
        'slideshowRepeat': false,
        'slideshowInterval': 5000,
        'opacity': 0.9,
        'controlsClass': 'large-dark',
        'controlsPosition': 'bottom center',
        'controlsOpacity': 0.75,
        'controlsAutohide': true,
        'controlsX': 0,
        'controlsY': 0,
        'theme': 'default',
        'closeButton': true,
        'showThumbs': true,
        'thumbsNum': 5,
        'showNumbers': true,
        'numbersPosition': 'caption',
        'show_pn_buttons': true,
        'width': 0,
        'height': 0,
        'padding': 100,
        
        'captions': {},
        
        'content': '.content',
        'imageThumbClass': '.image-thumb',
        'imageThumbPadding': 100,
        'imageThumbCaptions': {},
        'imageThumbOnOpen': function () {},
        'imageThumbCloseButton': true,
        
      }, options);
      
      var resize = function (elem, image, sWidth, sHeight, sTop, width, height) {
        
        var size = [];
        
        if (!width) width = image.data ('width');
        if (!height) height = image.data ('height');
        
        if (!sWidth) sWidth = $(window).width ();
        if (!sHeight) sHeight = $(window).height ();
        if (!sTop) sTop = $(window).scrollTop ();
        
        var
        content = elem.find (options['content']),
        size = $.elemDimentions (width, height, sWidth, sHeight, options['padding']),
        iWidth = size[0],
        iHeight = size[1];
        
        if (content.length) {
          
          var thisWidth = 300, thisHeight = 300;
          
          if (sHeight > sWidth) { // Портретный
            
            var nWidth = ($.elemNewSize ((sHeight - options['padding']), width, height) - options['padding']);
            
            if ((nWidth + thisWidth) > sWidth) {
              
              elem.addClass ('portrait');
              
              size = [iWidth, (iHeight + thisHeight)];
              
              if (size[1] > sHeight) {
                
                size = [$.elemNewSize ((sHeight - options['padding']), size[0], size[1]), (sHeight - options['padding'])];
                
                var height2 = $.elemNewSize (size[0], iWidth, iHeight);
                
                iHeight = $.elemNewSize (height2, iHeight, iWidth);
                iWidth = height2;
                
                size = [iWidth, ($.elemNewSize (iWidth, height, width) + thisHeight)];
                
              }
              
              content.css ({
                'width': (size[0] - 1) + 'px',
              });
              
              elem.find ('.overflow').css ({
                'height': (thisHeight - elem.find ('.add-form').height ()) + 'px',
              });
              
            } else {
              
              elem.removeClass ('portrait');
              
              var height2 = $.elemNewSize (nWidth, iHeight, iWidth);
              
              iWidth = $.elemNewSize (height2, iWidth, iHeight);
              iHeight = height2;
              
              size = [(iWidth + thisWidth + 1), iHeight];
              
              /*if (size[0] >= sWidth) {
                
                size = [(sWidth - options['padding']), $.elemNewSize ((sWidth - options['padding']), height, width)];
                
                var height2 = $.elemNewSize (size[1], iHeight, nWidth);
                
                iWidth = $.elemNewSize (height2, nWidth, iHeight);
                iHeight = height2;
                
                size = [($.elemNewSize (iHeight, width, height) + thisWidth + 1), iHeight];
                
              }*/
              
              elem.find ('.overflow').css ({
                'height': (iHeight - elem.find ('.add-form').height ()) + 'px',
              });
              
            }
            
          } else { // Ландшафтный
            
            elem.removeClass ('portrait');
            
            size = [(iWidth + thisWidth + 1), iHeight];
            
            if (size[0] >= sWidth) {
              
              size = [(sWidth - options['padding']), $.elemNewSize ((sWidth - options['padding']), height, width)];
              
              var height2 = $.elemNewSize (size[1], iHeight, iWidth);
              
              iWidth = $.elemNewSize (height2, iWidth, iHeight);
              iHeight = height2;
              
              size = resize (elem, image, sWidth, sHeight, sTop, ($.elemNewSize (iHeight, width, height) + 1), iHeight);
              
            }
            
            content.css ({
              'width': thisWidth + 'px',
            });
            
            elem.find ('.overflow').css ({
              'height': (iHeight - elem.find ('.add-form').height ()) + 'px',
            });
            
          }
          
          $('.gallery-controls.prev').css ({
            'left': ((size[0] - sWidth) / 2) + 'px',
          });
          
          $('.gallery-controls.next').css ({
            'right': ((size[0] - sWidth) / 2) + 'px',
          });
          
          $('.baloon-opacity').css ({
            
            'width': sWidth + 'px',
            'height': sHeight + 'px',
            'top': (iHeight / 2) + 'px',
            
          });
          
        }
        
        image.css ({
          
          'width': iWidth + 'px',
          'height': iHeight + 'px',
          
        });
        
        $.each (options['captions'], function (key, value) {
          
          if (value['content']) {
            
            var caption = elem.find ($('.caption.' + key));
            
            if (key == 'bottom-left')
            caption.css ({
              'top': (image.height () - caption.height ()) + 'px',
            });
            else if (key == 'top-right')
            caption.css ({
              
              'top': -(image.width () - parseInt (caption.css ('top'))) + 'px',
              'left': (image.width () - caption.width () - parseInt (caption.css ('right'))) + 'px',
              
            });
            else if (key == 'top-center' || key == 'bottom-center')
            caption.css ({
              
              'width': caption.width () + 'px',
              'margin-left': -(caption.width () / 2) + 'px',
              
            });
            
          }
          
        });
        
        elem.center (size[0], size[1], sTop);
        
        return size;
        
      };
      
      $.baloon (options['data'], {
        
        'class': options['class'],
        'openEffect': 'fade',
        
        'fadeTime': 0,
        'opacity': options['opacity'],
        'scroll': false,
        'closeOut': true,
        
        'onLoad': options['onLoad'],
        'padding': options['padding'],
        
        'onInit': function (elem, bOptions) {
          $.baloonClose ({ 'class':bOptions['class'], 'opacity':false });
        },
        
        'onOpen': function (elem, width, height, bOptions) {
          
          var baloon = $(elem);
          baloon.addClass (options['theme']);
          
          baloon.find ($('.image-area .image-area-img')).load (function () {
            
            var image = $(this);
            
            image.data ('width', image.width ());
            image.data ('height', image.height ());
            
            if (options['closeButton'] && !baloon.find ('.close').length)
            options['captions']['top-right'] = {
              
              'content': function (baloon2, data2) {
                
                $(baloon2).click (function () {
                  $.baloonClose ({ 'class':bOptions['class'], 'opacity':true, 'scroll':true, });
                });
                
              },
              
            };
            
            if (!nested) {
              
              image.imageThumb ({
                
                'closeButton': true,
                'class': 'image-thumb-area-nested',
                'closeOut': options['closeOut'],
                'padding': options['imageThumbPadding'],
                'captions': options['imageThumbCaptions'],
                'onOpen': options['imageThumbOnOpen'],
                'opacity': 0,
                'width': image.width (),
                'height': image.height (),
                
              }, true);
              
              image.click (function () {
                
                if ($.isFunction (options['onClick']))
                options['onClick'] (elem, image, options);
                else
                return false;
                
              });
              
              if (options['imageThumbClass'])
              baloon.find (options['imageThumbClass']).each (function () {
                
                $(this).imageThumb ({
                  
                  'class': 'image-thumb-area-nested-other',
                  'closeOut': options['closeOut'],
                  
                });
                
              });
              
            }
            
            $.each (options['captions'], function (key, value) {
              
              if (value['content']) {
                
                baloon.append ('<div class="caption ' + key + '"></div>');
                
                var
                captionSelf = '.caption.' + key;
                caption = baloon.find ($(captionSelf));
                
                if (key == 'top-center' || key == 'bottom-center')
                caption.css ({ 'left':'50%' });
                else if (key == 'bottom-left')
                caption.css ({ 'top':'50%' });
                
                if ($.isFunction (value['content'])) {
                  
                  if ($(self).length)
                  caption.html (value['content'] (captionSelf));
                  else
                  caption.html (value['content'] (captionSelf, value['data']));
                  
                } else caption.html (value['content']);
                
              }
              
            });
            
            var size = resize (baloon, image);
            
            options['onOpen'] (elem, image, size[0], size[1], options);
            
          });
          
          //options['onOpen'] (elem, width, height, options);
          
        },
        
        'onResize': function (elem, width, height, sWidth, sHeight, sTop, bOptions) {
          
          var image = $(elem).find ($('.image-area .image-area-img')),
          size = resize ($(elem), image, sWidth, sHeight, sTop);
          
          if ($.isFunction (options['onResize']))
          options['onResize'] (elem, size[0], size[1], sWidth, sHeight, sTop, options);
          else
          options['onOpen'] (elem, image, size[0], size[1], options);
          
        },
        
        'onClose': function () {
          $('.gallery-controls').remove ();
        },
        
      });
      
    };
    
    $.fn.imageThumb = function (options, nested) {
      
      options = $.extend ({
        
        'onBeforeOpen': function () {},
        'onAfterOpen': function () {},
        
      }, options);
      
      $(this).click (function () {
        
        options['onBeforeOpen'] (options, this);
        
        if (!options['data']) {
          
          var src, elem = $(this);
          
          if ($.isFunction (options['src']))
          src = options['src'] (this, options);
          else if (elem.data ('image-thumb-src'))
          src = elem.data ('image-thumb-src');
          //else if (elem.attr ('src').length)
          //src = elem.attr ('src');
          else
          src = elem.attr ('href');
          
          options['data'] = '<div class="image-area"><img alt="" src="' + src + '" class="image-area-img"/></div>';
          
        } else if ($.isFunction (options['data']))
        options['data'] = options['data'] (options, this);
        
        $._imageThumb (options, nested, this);
        
        options['onAfterOpen'] (options, this);
        
        return false;
        
      });
      
    };
    
    $.imageThumb = function (options, nested) {
      
      options = $.extend ({
        
        'onBeforeOpen': function () {},
        'onAfterOpen': function () {},
        
      }, options);
      
      if ($.isFunction (options['data']))
      options['data'] = options['data'] (options);
      
      options['onBeforeOpen'] (options);
    
      $._imageThumb (options, nested);
      
      options['onAfterOpen'] (options);
      
      return false;
      
    };
    
  })($);