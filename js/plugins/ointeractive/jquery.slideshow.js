/*
 slideshow 1.0 - jQuery plugin
 written by O! Interactive, Acuna
 http://ointeractive.ru
 
 Copyright (c) 2017 O! Interactive, Acuna (http:// ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 1.0  04.02.2017
  Первый приватный релиз
  
 */

(function ($) {
  
  $.fn.slideshow = function (options) {
    
    options = $.extend ({
      
      'slideTime': 700,
      'waitTime': 5000,
      'infinity': true,
      'hoverStop': false,
      'hoverResumeTime': 10,
      'effect': 'left',
      'paginateType': 'list',
      
    }, options);
    
    var elem = $(this), oldContent, paginate = '',
    content = elem.find ('.slider-content'), i = 0,
    bgElem = $(options['bgElem']),
    slide = content.find ('.slide'),
    height = elem.outerHeight ();
    
    content.wrap ('<div class="slider-handler" style="overflow:hidden;"></div>');
    
    slide.each (function () {
      ++i;
      
      var
      thisSlide = $(this),
      id = thisSlide.data ('id'),
      image = thisSlide.find ('img');
      
      if (!id) {
        
        if (options['paginateType'] == 'num') id = i;
        else id = '';
        
      }
      
      paginate += '<span>' + id + '</span>';
      
      if (image.length) {
        
        thisSlide.css ('height', height + 'px');
        image.css ('height', (height + 'px')); // Растягиваем картинку на всю высоту слайда
        
      }
      
      elem.find ('.slider-page.next').css ({ 'left':elem.find ('.slider-handler').outerWidth () + 'px' });
      
      thisSlide.css ({ 'width':elem.width () + 'px' });
      thisSlide.find ('.descr').css ({ 'width':(elem.width () - (parseInt (thisSlide.find ('.descr').css ('padding')) * 2)) + 'px' });
      
    });
    
    elem.find ('.slider-paginate').addClass (options['paginateType']);
    
    if (slide.length > 1)
    elem.find ('.slider-paginate').append (paginate);
    
    var rotate = function (elem2, index, slide2) { // Переход к слайду
      
      var content2 = elem2.find ('.slider-content');
      
      if (!index) index = 0;
      if (!slide2) slide2 = content2.find ('.slide').eq (0);
      
      var
      width = slide2.outerWidth (),
      animate = { 'left': -(index * width) + 'px' }; // Длина прокрутки;
      
      content2.css ({ 'width':(width * content2.find ('.slide').length) + 'px' }); // Ширина проскальзывания слайдов
      
      elem2.find ($('.slider-paginate span.active')).removeClass ('active');
      elem2.find ($('.slider-paginate span')).eq (index).addClass ('active');
      
      switch (options['effect']) {
        
        default:
          
          content2.css (animate);
          content2.show (options['effect'], options['slideTime']);
          
        break;
        
        case 'left':
          content2.animate (animate, options['slideTime']);
        break;
        
      }
      
      /*if (oldContent) {
        
        index = 0;
        oldcontent2.remove ();
        
      } else */
      
    },
    
    rotation = function (elem2, index) { // Смена слайда
      
      if (!index) index = 0;
      
      var
      content2 = elem2.find ('.slider-content'),
      slide2 = content2.find ('.slide'), play,
      slidesNum2 = slide2.length;
      
      if (options['waitTime'] > 0)
      play = setInterval (function () {
        
        if (options['infinity'] && index >= (slidesNum2 - 1)) {
          
          //oldContent = slide2.eq (0);
          //content2.append (oldcontent2.outerHTML ());
          //content2.css (options['effect'], 0);
          //++slidesNum2;
          
          index = 0;
          
        } else ++index;
        
        rotate (elem2, index, slide2);
        
      }, options['waitTime']);
      
      var _rotate = function (thisIndex) {
        
        if (thisIndex < 0) index = thisIndex = (slidesNum2 - 1);
        if (thisIndex > (slidesNum2 - 1) || thisIndex < 0) index = thisIndex = 0;
        
        rotate (elem2, thisIndex);
        clearInterval (play);
        
        /*if (options['resumeTime'] > 0)
        setTimeout (function () { // Возобновляем показ, если установлено
          rotation (elem2, thisIndex);
        }, options['resumeTime']);*/
        
      };
      
      elem2.find ($('.slider-paginate span')).click (function () {
        
        _rotate ($(this).index ());
        return false;
        
      });
      
      elem2.find ($('.slider-page.prev')).click (function () {
        
        _rotate (--index);
        return false;
        
      });
      
      elem2.find ($('.slider-page.next')).click (function () {
        
        _rotate (++index);
        return false;
        
      });
      
      if (options['hoverStop']) //[TODO]
      slide2.hover (
        
        function () { // Наведение на слайд
          
          rotate (elem2, $(this).index ());
          clearInterval (play);
          
        },
        
        function () { // Снимаем курсор
          
          if (options['hoverResumeTime'] > 0)
          setTimeout (function () { // Возобновляем показ, если установлено
            rotation (elem2, $(this).index ());
          }, options['hoverResumeTime']);
          
        }
        
      );
      
    };
    
    rotate (elem);
    rotation (elem);
    
    if (bgElem.length) { // Фоновый слайдер
      
      var height = elem.outerHeight ();
      elem.css ({ 'margin-bottom':-height + 'px', 'top':-((bgElem.outerHeight () + height) / 2) + 'px' }); // Оттягиваем отступ по высоте основного слайдера
      
    }
    
    return this;
    
  };
  
})(jQuery);