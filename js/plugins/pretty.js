/*
 pretty.js 1.2 - JS & jQuery Framework
 written by O! Interactive, Acuna
 http://ointeractive.ru
 
 Copyright (c) 2016 O! Interactive, Acuna (http://ointeractive.ru)
 Dual licensed under the MIT (MIT-LICENSE.txt)
 and GPL (GPL-LICENSE.txt) licenses.
 
 Built for jQuery library
 http://jquery.com
 
 1.0  06.04.2016
  Первый приватный релиз
	
 1.2  17.11.2016
  Добавлена функция elemDimentions
  
 */
	
	(function ($) {
		
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		// jQuery Plugins
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
		$.fn.delay = function (time, func) {
			
			this.each (function () {
				setTimeout (func, time);
			});
			
			return this;
			
		};
		
		$.fn.action = function (action, func) {
			this.off ().on (action, func);
		};
		
		$.fn.marquee = function () {
			
			var marquee = $(this).css ({ 'overflow':'hidden', 'width':'100%' });
			
			marquee.wrapInner ('<div>');
			marquee.find ('div').css ({ 'width':'200%' });
			
			var el = function  () {
				
				$(this).css ('margin-left', '0%');
				$(this).animate ({ 'margin-left':'-100%' }, 12000, 'linear', el);
				
			};
			
			el.call (marquee.find ('div'));
			
		};
		
		$.fn.scrollToAttribute = function (options) {
			$.scrollTo (this, options);
		};
		
		$.fn.setCursorPosition = function (position) {
			
			if (this.length == 0) return this;
			return $(this).setSelection (position, position);
			
		};
		
		$.fn.setSelection = function (selectionStart, selectionEnd) {
			
			if($(this).length == 0) return this;
			var input = $(this)[0];
			
			if (input.createTextRange) {
				
				var range = input.createTextRange ();
				
				range.collapse (true);
				range.moveEnd ('character', selectionEnd);
				range.moveStart ('character', selectionStart);
				range.select ();
				
			} else if (input.setSelectionRange) {
				
				input.focus ();
				input.setSelectionRange (selectionStart, selectionEnd);
				
			}
			
			return this;
			
		};
		
		$.fn.focusEnd = function () {
			
			if ($(this).length)
			$(this).setCursorPosition ($(this).val ().length);
			
			return this;
			
		};
		
		$.fn.preventOverflow = function () {
			
			return this.each (function () {
				
				var defaultDisplay = $(this).css ('display');
				var defaultHeight = $(this).height ();
				
				$(this).css ('display', 'table');
				
				var newHeight = $(this).height ();
				
				$(this).cs, ('display', defaultDisplay);
				
				if(newHeight > defaultHeight)
				$(this).height (newHeight);
				
			});
			
		};
		
		$.fn.value = function () {
			
			var
			obj = $(this),
			val = obj.val (),
			type = obj.attr ('type');
			
			if (type && type == 'checkbox') {
				
				var un_val = obj.attr ('data-unchecked');
				if (typeof (un_val) === 'undefined') un_val = 0;
				
				return (obj.prop ('checked') ? val : un_val);
				
			} else return val;
			
		};
		
		$.fn.clickOut = function (action) {
			
			var self = this;
			
			$(document).click (function (e) {
				
				if (e.target != $(self)[0] && !$(self).has (e.target).length && $.isFunction (action))
				action (self, e.target);
				
			});
			
		};
		
		$.fn.center = function (width, height, top) {
			
			var elem = $(this);
			if (!top) top = $(window).scrollTop ();
			
			var style = {
				
				'width': width + 'px',
				'height': height + 'px',
				'margin-left': -(width / 2) + 'px',
				'margin-top': (top - (height / 2)) + 'px',
				
			};
			
			if (!elem.css ('top')) style['top'] = '50%';
			if (!elem.css ('left')) style['left'] = '50%';
			
			elem.css (style);
			
		};
		
		$.fn.scrollEnd = function (action, options) {
			
			options = $.extend ({
				
				'end': 0,
				
			}, options);
			
			$(this).on ('scroll', function (e) {
				
				e.preventDefault ();
				e.stopPropagation ();
				
				if (($(this).scrollTop () + $(this).innerHeight () >= ($(this)[0].scrollHeight - options['end'])) && $.isFunction (action)) {
					
					action (options);
					$(this).unbind ('scroll');
					
				}
				
			});
			
		};
		
		$.fn.toggleShow = function (type, options) {
			
			options = $.extend ({
				
				'time': 1000,
				'effect': 'blind',
				
			}, options);
			
			if (type == 'hide')
			$(this).hide (options['effect'], {}, options['time']);
			else
			$(this).hide ().animate ({ 'height':'toggle' }, options['time']);
			
		};
		
		$.fn.elemResize = function () {
			
			var elem = $(this);
			
			$(window).resize (function () {
				
				var height = (($(this).height () * elem.height ()) / $(window).height ());
				
				elem.css ({
					'height': height + 'px',
				});
				
			});
			
		};
		
		$.fn.onResize = function (handleFunction) {
			
			var element = this;
			var lastWidth = element.width ();
			var lastHeight = element.height ();
			
			setInterval (function () {
				
				if (lastWidth === element.width () && lastHeight === element.height ()) return;
				if (typeof (handleFunction) == 'function') {
					
					handleFunction (element, lastWidth, lastHeight);
					lastWidth = element.width();
					lastHeight = element.height();
					
				}
				
			}, 100);
			
			return element;
			
		};
		
		$.fn.visible = function (handleFunction) {
			
			var x2 = ($(this).offset ().top + $(this).height ());
			var side = (x2 > $(window).height ()) ? 'bottom' : 'top';
			
			//alert ($(this).offset ().top);
			
			return ($(window).width () - ($(this).offset().left + $(this).outerWidth ()));
			
		};
		
		$.fn.outerHTML = function () {
			return $('<div>').append ($(this).clone ()).html ();
		};
		
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		// jQuery Wrap Functions
		// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
		/*$.getScript = function (url, is_cache, callb) {
			
			var cache = false, callback = null;
			
			if ($.is (function (is_cache)) {
				callback = is_cache;
				cache = callb || cache;
			} else {
				cache = is_cache || cache;
				callback = callb || callback;
			}
			
			var load = true;
			
			$('script[type="text/javascript"]').each (function () {
				return load = (url != $(this).attr ('src')); 
			});
			
			if (load) {
				
				$.ajax ({
					type: 'GET',
					url: url,
					success: callback,
					dataType: 'script',
					cache: cache
				});
				
			} else if ($.is	function (callback)) callback.call (this);
			
		};*/
		
		if (!$.browser) {
			
			$.browser = {};
			$.browser.mozilla = false;
			$.browser.webkit = false;
			$.browser.opera = false;
			$.browser.safari = false;
			$.browser.chrome = false;
			$.browser.msie = false;
			$.browser.android = false;
			$.browser.blackberry = false;
			$.browser.ios = false;
			$.browser.operaMobile = false;
			$.browser.windowsMobile = false;
			$.browser.mobile = false;
			
			var nAgt = navigator.userAgent;
			$.browser.ua = nAgt;
			
			$.browser.name = navigator.appName;
			$.browser.fullVersion  = '' + parseFloat (navigator.appVersion);
			$.browser.majorVersion = parseInt (navigator.appVersion,10);
			var nameOffset,verOffset,ix;
			
			// In Opera, the true version is after 'Opera' or after 'Version'
			if ((verOffset=nAgt.indexOf ('Opera')) !=- 1) {
				$.browser.opera = true;
				$.browser.name = 'Opera';
				$.browser.fullVersion = nAgt.substring (verOffset + 6);
				if ((verOffset=nAgt.indexOf ('Version')) !=- 1) 
				$.browser.fullVersion = nAgt.substring (verOffset + 8);
			}
			// In MSIE < 11, the true version is after 'MSIE' in userAgent
			else if ((verOffset=nAgt.indexOf ('MSIE')) !=- 1) {
				$.browser.msie = true;
				$.browser.name = 'Microsoft Internet Explorer';
				$.browser.fullVersion = nAgt.substring (verOffset + 5);
			}
			// In TRIDENT (IE11) => 11, the true version is after 'rv:' in userAgent
			else if (nAgt.indexOf ('Trident') !=- 1) {
				$.browser.msie = true;
				$.browser.name = 'Microsoft Internet Explorer';
				var start = nAgt.indexOf ('rv:') + 3;
				var end = start + 4;
				$.browser.fullVersion = nAgt.substring (start,end);
			}
			// In Chrome, the true version is after 'Chrome'
			else if ((verOffset=nAgt.indexOf ('Chrome')) !=- 1) {
				$.browser.webkit = true;
				$.browser.chrome = true;
				$.browser.name = 'Chrome';
				$.browser.fullVersion = nAgt.substring (verOffset + 7);
			}
			// In Safari, the true version is after 'Safari' or after 'Version'
			else if ((verOffset=nAgt.indexOf ('Safari')) !=- 1) {
				$.browser.webkit = true;
				$.browser.safari = true;
				$.browser.name = 'Safari';
				$.browser.fullVersion = nAgt.substring (verOffset + 7);
				if ((verOffset=nAgt.indexOf ('Version')) !=- 1) 
				$.browser.fullVersion = nAgt.substring (verOffset + 8);
			}
			// In Safari, the true version is after 'Safari' or after 'Version'
			else if ((verOffset=nAgt.indexOf ('AppleWebkit')) !=- 1) {
				$.browser.webkit = true;
				$.browser.name = 'Safari';
				$.browser.fullVersion = nAgt.substring (verOffset + 7);
				if ((verOffset=nAgt.indexOf ('Version')) !=- 1)
				$.browser.fullVersion = nAgt.substring (verOffset + 8);
			}
			// In Firefox, the true version is after 'Firefox'
			else if ((verOffset=nAgt.indexOf ('Firefox')) !=- 1) {
				$.browser.mozilla = true;
				$.browser.name = 'Firefox';
				$.browser.fullVersion = nAgt.substring (verOffset + 8);
			}
			// In most other browsers, 'name/version' is at the end of userAgent
			else if ((nameOffset=nAgt.lastIndexOf (' ')  + 1) < (verOffset=nAgt.lastIndexOf ('/'))) {
				
				$.browser.name = nAgt.substring (nameOffset,verOffset);
				$.browser.fullVersion = nAgt.substring (verOffset + 1);
				
				if ($.browser.name.toLowerCase () == $.browser.name.toUpperCase ())
				$.browser.name = navigator.appName;
				
			}
			
			/* Check all mobile environments */
			$.browser.android = (/Android/i).test (nAgt);
			$.browser.blackberry = (/BlackBerry/i).test (nAgt);
			$.browser.ios = (/iPhone|iPad|iPod/i).test (nAgt);
			$.browser.operaMobile = (/Opera Mini/i).test (nAgt);
			$.browser.windowsMobile = (/IEMobile/i).test (nAgt);
			$.browser.mobile = $.browser.android || $.browser.blackberry || $.browser.ios || $.browser.windowsMobile || $.browser.operaMobile;
			
			// trim the fullVersion string at semicolon/space if present
			if ((ix=$.browser.fullVersion.indexOf (';')) !=- 1) 
			$.browser.fullVersion=$.browser.fullVersion.substring (0,ix);
			if ((ix=$.browser.fullVersion.indexOf (' ')) !=- 1) 
			$.browser.fullVersion=$.browser.fullVersion.substring (0,ix);
			
			$.browser.majorVersion = parseInt ('' + $.browser.fullVersion,10);
			
			if (isNaN ($.browser.majorVersion)) {
				$.browser.fullVersion = '' + parseFloat (navigator.appVersion);
				$.browser.majorVersion = parseInt (navigator.appVersion,10);
			}
			
			$.browser.version = $.browser.majorVersion;
			
		}
		
		/**
			$.browser.name
			$.browser.fullVersion
			$.browser.version
			$.browser.majorVersion
			
			$.browser.msie
			$.browser.mozilla
			$.browser.opera
			$.browser.chrome
			$.browser.webkit
			
			$.browser.android
			$.browser.blackberry
			$.browser.ios
			$.browser.operaMobile
			$.browser.windowsMobile
			$.browser.mobile
		*/
		
		switch ($.browser.name) {
			
			case 'Microsoft Internet Explorer':
				$('html').attr ('class', 'ie ie' + $.browser.version);
			break;
			
			case 'Chrome':
				$('html').attr ('class', 'chrome chrome' + $.browser.version);
			break;
			
			case 'Opera':
				$('html').attr ('class', 'opera opera' + $.browser.version);
			break;
			
		}
		
		$.scrollTo = function (tag, options) {
			
			if ($.isObject (tag)) options = tag;
			
			options = $.extend ({
				
				'elem': 'html,body',
				'time': 1500,
				'padding': 0,
				
			}, options);
			
			var top = 0;
			if (!$.isObject (tag) && $(tag).length) top = $(tag).offset ().top;
			
			$(options['elem']).animate ({ 'scrollTop':(top - options['padding']) }, options['time']);
			
		};
		
		$.fn.scrollToAttribute = function (options) {
			$.scrollTo (this, options);
		};
		
		$.scrollToAnchor = function (tag, options) {
			$.scrollTo ('#' + tag, options);
		};
		
		$.screenWidth = function () {
			
			var width = $(document).width ();
			
			if (width && (($.cookie ('screen_width') != width) || !$.cookie ('screen_width')))
			$.cookie ('screen_width', width);
			
			return $.cookie ('screen_width');
			
		};
    
    var i = 0;
		
		$.prettyAjax = function (url, data, success, options) {
			
			var options = $.extend ({
				
				'url': url,
				'data': data,
				'success': success,
				'async': true,
				'cache': true,
				
        'method': 'get',
        'sendError': true,
        'attemptsNum': 2,
        'error': function () {},
				'timeout': 30000, // 30 сек (1*30*1000)
				
				'beforeSend': function () {},
				'complete': function () {},
				
			}, options);
			
      /*options.error = function (xhr, textStatus, errorThrown) {
        ++i;
        
        if (textStatus == 'parsererror' || i >= options.attemptsNum) {
          
          i = 0;
          options.error (xhr, textStatus, errorThrown);
          
        } else $.prettyAjax (url, data, success, options);
        
      };*/
      
			$.ajax (options).done (function () {
				return false;
			});
			
			return false;
			
		};
		
    $.joinObj = function (object) {
      
      var prepSlice = function (object, start, finish) {
        return object.slice (start, finish).replace ('"', '\'');
      };
      
      String.prototype.splice = function (idx, rem, s) {
        return (prepSlice (this, 0, idx) + s + prepSlice (this, idx + Math.abs (rem)));
      };
      
      return JSON.stringify (object, '\'');
      
    };
		
		$.elemNewSize = function (size, width, height, padding, debug) {
			
			if (!padding) padding = 0;
			if (debug) alert (size);
			size = Math.floor ((size * width) / height);
			//if (size > padding) output = (size - padding); else output = size;
			if (debug) alert (size);
			return size;
			
		};
		
		$.elemDimentions = function (width, height, sWidth, sHeight, padding, debug) {
			
			if (!sWidth) sWidth = $(window).width ();
			if (!sHeight) sHeight = $(window).height ();
			if (!padding) padding = 0;
			
			//alert ([width, height, sWidth, sHeight]);
			
			if (width > sWidth) { // Ширина больше исходной
				
				height = $.elemNewSize ((sWidth - padding), height, width, padding);
				width = (sWidth - padding);
				
				size = $.elemDimentions (width, height, sWidth, sHeight, padding, debug);
				//size = [width, height];
				
			} else if (height > sHeight) { // Высота больше исходной
				
				//sHeight = 100;
				width = $.elemNewSize ((sHeight - padding), width, height, padding, debug);
				height = (sHeight - padding);
				
				size = $.elemDimentions (width, height, sWidth, sHeight, padding, debug);
				//size = [width, height];
				
			}/* else if (width < sWidth) {
				
				width = $.elemNewSize ((sHeight - padding), width, height, padding, debug);
				height = (sHeight - padding);
				
				size = [width, height];
				
			} else if (height < sHeight) { // Высота меньше исходной
				
				height = $.elemNewSize ((sWidth - padding), height, width, padding);
				width = (sWidth - padding);
				
				size = [width, height];
				
			} */else size = [width, height];
			
			return size;
			
		};
		
		$.orientation = function () {
			
			var out;
			if ($(window).height () > $(window).width ()) out = 'portrait';
			else out = 'landscape';
			
			return out;
			
		};
		
		$.opacity = function (level, color, className, id, top) {
			
			if (!top) top = $(window).scrollTop ();
			
			if (level && !$('.opacity').length)
        $('body').append ('<div style="\
width: 100%; \
height: 100%; \
left: 0; \
top: ' + top + 'px; \
position: absolute; \
background: ' + color + '; \
-ms-filter: \'progid:DXImageTransform.Microsoft.Alpha(Opacity=' + (level * 100) + ')\'; \
filter: alpha(opacity=' + (level * 100) + '); \
-moz-opacity: ' + level + '; \
-khtml-opacity: ' + level + '; \
opacity: ' + level + '; \
z-index: 2;\
"class="opacity' + (className ? ' ' + className : '') + (id ? ' ' + id : '') + '"></div>');
			
		};
		
		$.opacityRemove = function (className) {
			$('.opacity' + (className ? '.' + className : '')).remove ();
		};
		
		$.isObject = function (obj) {
			return ($.type (obj) == 'object');
		};
		
		$.pushState = function () {
			return (window.history && window.history.pushState && window.history.replaceState
		&& !navigator.userAgent.match (/(iPod|iPhone|iPad|WebApps\/.+CFNetwork)/)) // Огрызки
		};
		
		$.getCursorPosition = function (e) {
			
			var posx = 0, posy = 0;
			if (!e) e = window.event;
			
			if (e.pageX || e.pageY) {
				
				posx = e.pageX;
				posy = e.pageY;
				
			} else if (e.clientX || e.clientY) {
				
				posx = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
				posy = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
				
			}
			
			return [posx, posy];
			
		};
		
	})($);
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// jQuery Functions
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	function reverse (items, func) {
		
    var reversed = Array();
    for ( var key in items )
		reversed.unshift(items[key]);
    
    if( func ){
        for( var key in reversed ){
          func(key, reversed[key]);
        }
    }
		
    return reversed;
		
	}
	
	function doneTimeout (timeout) {
		
		return $.Deferred (function (def) {
			window.setTimeout (def.resolve, timeout);
		}).promise ();
		
	}
		function elemCenterWidth (elem) {
		return (Math.ceil (($(window).width () - $(elem).width ()) / 2));
	}
	
	function elemCenterHeight (elem) {
		return (Math.ceil (($(window).height () - $(elem).height ()) / 2));
	}
	
	function clear (a, b) {
		
		var c = a.value;
		if (c == b) a.value = '';
		$(a).attr ('onblur', "if (this.value == '') this.value = '" + b + "';");
		
	}
	
	function getLeft (width) {
		return Math.ceil (($(window).width () - width) / 2);
	}
	
	function getTop (height) {
		return Math.ceil (($(window).height () - height) / 2);
	}
	
	function elemTop (area) {
		return getTop ($(area).height ());
	}
	
	function elemLeft (area) {
		return getLeft ($(area).width ());
	}
	
	function overflow (show) {
		
		if (show)
		$('html,body').css ('overflow', 'auto');
		else
		$('html,body').css ('overflow', 'hidden');
		
	}
	
	function debug (text) {
		
		if ($.isArray (text)) text = text.join (', ');
		alert (text);
		
	}
	
	function json2array (text) {
		
		if (!$.isObject (text)) text = $.parseJSON (text);
		return text;
		
	}
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Wrappers
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	window.onpopstate = function (e) {
		history.pushState ({}, '', history.location);
	};
	
	$(document).load (function () {
		LoadingBar ('page', 0);
	});
	
	var ajaxInProcess = false, showProgress = true;
	
	$(document)
	
	.ajaxStart (function () {
		
		ajaxInProcess = true;
		if (showProgress) LoadingBar ('page', 1);
		
	})
	
	.ajaxStop (function () {
		
		ajaxInProcess = false;
		LoadingBar ('page', 0);
		
	})
  
  .ajaxError (function (event, xhr, settings, thrownError) {
		console.log (thrownError + ': ' + xhr.responseText);
	});
  
	if (ajaxInProcess == true) request.abort ();
  
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// JS Functions
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	function sleep (time) {
		setTimeout (function () {}, time);
	}
	
	function getFileType (file) {
		
		if (file) {
			
			file = file.split ('?');
			file = file[0].split ('.').pop ();
			
		}
		
		return file;
		
	}
	
	function getFlash (movieName) {
    
		if (window.document[movieName])
      return window.document[movieName];
    else if (
      navigator.appName.indexOf ('Microsoft Internet') == -1
      &&
      document.embeds && document.embeds[movieName]
    )
      return document.embeds[movieName];
    else
      return document.getElementById (movieName);
    
	}
	
	function hasFlash () {
		
		if (navigator.plugins && navigator.plugins.length && navigator.plugins['Shockwave Flash'])
      return true;
		else if (navigator.mimeTypes && navigator.mimeTypes.length) {
			
			var mimeType = navigator.mimeTypes['application/x-shockwave-flash'];
			return mimeType && mimeType.enabledPlugin;
			
		} else {
			
			try {
				
				new ActiveXObject ('ShockwaveFlash.ShockwaveFlash');
				return true;
				
			} catch (e) {}
			
		}
		
		return false;
		
	}
	
	function canPlay (song, url) {
		return (song && song.canPlayType && (song.canPlayType ('audio/mpeg;') || (song.canPlayType ('audio/ogg;') && getFileType (url) == 'ogg')));
	}
	
	function random (min, max) {
		
		var output;
		
		if (max)
      output = Math.floor (Math.random () * (max - min + 1)) + min;
		else
      output = Math.floor (Math.random () * (min + 1));
		
		return output;
		
	}
	
	function getRandom (length) {
		
		var salt = '0123456789', rand = '', len = (salt.length - 1);
		for (i = 0; i < length; ++i) rand += salt[random (0, len)];
		
		return rand;
		
	}
  
	function popup (url, w, h) { // Благодарности уходят xtf.dk и Tony M. Такое должно быть дефолтным!
		
		var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
		var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;
		
		width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
		height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;
		
		var left = ((width / 2) - (w / 2)) + dualScreenLeft;
		var top = ((height / 2) - (h / 2)) + dualScreenTop;
		var newWindow = window.open (url, '_blank', 'scrollbars=yes,width=' + w + ',height=' + h + ',top=' + top + ',left=' + left);
		
		if (window.focus) newWindow.focus ();
		
	}
	
	function wordwrap (str, width, brk, cut) {
		
    brk = brk || 'n';
    width = width || 75;
    cut = cut || false;
		
    if (!str) { return str; }
		
    var regex = '.{1,' +width+ '}(\s|$)' + (cut ? '|.{' +width+ '}|.+$' : '|\S+?(\s|$)');
		
    return str.match( RegExp(regex, 'g') ).join( brk );
		
	}
	
	var
	SUMB_DIGITS = '0123456789',
	SUMB_SPECIAL = '!?@#~$%^&*№+=,:«»[]',
	SUMB_SPECIAL_2 = ',"\'/()—',
	SUMB_LETTERS_LOW = 'abcdefghijklmnopqrstuvwxyz',
	SUMB_LETTERS_UP = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	
	function bookmark_site (a) {
		
		title = document.title;
		url = document.location;
		
		try { // Internet Explorer
			window.external.AddFavorite (url, title); 
		} catch (e) {
			
			try { // Mozilla
				window.sidebar.addPanel (title, url, '');
			} catch (e) { // Opera
				
				if (typeof (opera) == 'object') {
					
					a.rel = 'sidebar';
					a.title = title;
					a.url = url;
					a.href = url;
					return true;
					
				} else alert (lang[322]);
				
			}
			
		}
		
		return false; 
		
	}
	
	function utf8_decode (str_data) {
		
		var string = '', i = 0, c = c1 = c2 = 0;
		
		while (i < str_data.length) {
			
			c = str_data.charCodeAt (i);
			
			if (c < 128) {
				
				string += String.fromCharCode (c);
				i++;
				
			} else if ((c > 191) && (c < 224)) {
				
				c2 = str_data.charCodeAt (i + 1);
				string += String.fromCharCode (((c & 31) << 6) | (c2 & 63));
				i += 2;
				
			} else {
				
				c2 = str_data.charCodeAt (i + 1);
				c3 = str_data.charCodeAt (i + 2);
				string += String.fromCharCode (((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
				
			}
			
		}
		
		return string;
		
	}
	
	function str_replace (search, replace, subject) {
		return subject.split (search).join (replace);
	}
	
	function url_encode (str) {
		
		var trans = [];
		var ret = [];
		
		for (var i = 0x410; i <= 0x44F; i++) trans[i] = i - 0x350; // А-Яа-я
		trans[0x401] = 0xA8; // Ё
		trans[0x451] = 0xB8; // ё
		
		for (var i = 0; i < str.length; i++) {
			
			var n = str.charCodeAt(i);
			
			if (typeof trans[n] != 'undefined') n = trans[n];
			if (n <= 0xFF) ret.push (n);
			
		}
		
		str = String.fromCharCode.apply (null, ret);
		
		return escape ((str).replace (/\+/g, '%20'));
		
	}
	
	function url_decode (str) {
		return decodeURIComponent ((str).replace (/\+/g, '%20'));
	}
	
	function get_sel (o) {
		
		if (document.selection) {
			
			if (is_ie) {
				
				document.getElementById (selField).focus ();
				ie_range_cache = document.selection.createRange ();
				
			}
			
			var s = document.selection.createRange (); 
			if (s.text) return s.text;
			
		} else if (typeof (o.selectionStart) == 'number') {
			
			if (o.selectionStart != o.selectionEnd) {
				
				var start = o.selectionStart;
				var end = o.selectionEnd;
				return (o.value.substr (start, end - start));
				
			}
			
		}
		
		return false;
		
	}
	
	function check_uncheck_all (row) {
		
		var frm = document.getElementById (row);
		
		for (var i = 0; i < frm.elements.length; i++) {
			
			var elmnt = frm.elements[i];
			
			if (elmnt.type == 'checkbox') {
				
				if (frm.master_box.checked == true)
				elmnt.checked = false;
				else
				elmnt.checked = true;
				
			}
			
		}
		
		if (frm.master_box.checked == true)
		frm.master_box.checked = false;
		else
		frm.master_box.checked = true;
		
	}
	
	function ClearForm (row) {
		
		var frm = document.getElementById (row);
		
		for (var i = 0; i < frm.length; i++) {
			
			var el = frm.elements[i];
			if (el.type == 'checkbox' || el.type == 'radio') { el.checked = 0; continue; }
			if (el.type == 'text' || el.type == 'textarea' || el.type == 'password') { el.value = ''; continue; }
			if (el.type == 'select-one' || el.type == 'select-multiple') { el.selectedIndex = 0; }
			
		}
		
	}
	
	function DoDiv (id) {
		
		var item = null;
		if (document.getElementById)
		item = document.getElementById(id);
		else if (document.all)
		item = document.all[id];
		else if (document.layers)
		item = document.layers[id];
		
		else if (item.style) {
			
			if (item.style.display == "none") item.style.display = "";
			else item.style.display = "none";
			
		} else item.visibility = "show";
		
	}
	
	function change_img (obj, file) {
		var obj = document.getElementById (obj);
		obj.src = file;
	}
	
	function ltrim (str) {
		var ptrn = /^\s+/;
		return str.replace (ptrn, '');
	}
	
	function rtrim (str) {
		var ptrn = /\s{2,}/g;
		return str.replace (ptrn, '');
	}
	
	function trim (str) {
		return ltrim (rtrim (str));
	}
	
	function print_r (arr, level) {
		
		var print_red_text = '';
		if (!level) level = 0;
		var level_padding = '';
		
		for (var i = 0; i < level + 1; ++i) level_padding += '&nbsp;&nbsp;&nbsp;&nbsp;';
		
		if (typeof (arr) == 'object') {
			
			for (var item in arr) {
				
				var value = arr[item];
				
				if (typeof (value) == 'object') {
					
					print_red_text += '<br/>' + level_padding + '"' + item + '":&nbsp;{';
					print_red_text += print_r (value, level + 1);
					
				} else print_red_text += '<br/>&nbsp;&nbsp;' + level_padding + '"' + item + '":&nbsp;"' + value + '",';
				
        print_red_text += '<br/>' + level_padding + '},';
        
			}
      
		} else print_red_text = ' ===> ' + arr + ' <=== (' + typeof (arr) + ')';
		
		return print_red_text;
		
	}
	
	function make_array (array) {
		
		if (!array) array = {};
		return array;
		
	}
	
	function intval (num) {
		
		if (typeof num == 'number' || typeof num == 'string') {
			
			num = num.toString ();
			
			var dotLocation = num.indexOf('.');
			
			if (dotLocation > 0) num = num.substr (0, dotLocation);
			if (isNaN (Number (num))) num = parseInt (num);
			
			if (isNaN (num)) return 0;
			return Number (num);
			
		} else if (typeof num == 'object' && num.length != null && num.length > 0) return 1;
		else if (typeof num == 'boolean' && num === true) return 1;
		else return 0;
		
	}
	
	function sleep (ms) {
		ms += new Date ().getTime ();
		while (new Date () < ms) {}
	}
	
	function scroll_title (title, time) {
		
		if (!time) time = 300;
		
		title = scrl.substring (1, title.length) + title.substring (0, 1);
		document.title = title;
		setTimeout ('scroll_title(' + title + ')', time);
		
	}
	
	function setNewField (which, formname) {
		
		if (which != selField) {
			
			fombj = formname;
			selField = which;
			
		}
		
	}
	
	function random (min, max) {
		
		var output = '';
		
		if (max)
		output = Math.floor (Math.random () * (max - min + 1)) + min;
		else
		output = Math.floor (Math.random () * (min + 1));
		
		return output;
		
	}
	
	function do_rand (num, type) {
		
		var salt = SUMB_DIGITS; // 1
		if (type == 2 || type == 3 || type == 4) salt += SUMB_LETTERS_LOW; // 2
		if (type == 3 || type == 4) salt += SUMB_LETTERS_UP; // 3
		if (type == 4) salt += SUMB_SPECIAL; // 4
		
		// srand ((double) microtime () * 1000000);
		
		var rand = 0,
		len = (salt.length - 1);
		
		for (i = 0; i < num; ++i) rand += salt[random (0, len)];
		
		return rand;
		
	}
	
	function intval_correct (int_this, int_val, int_min) { // Представляет int_this как числовое значение. Если результат <= int_min, то возвращает значение int_val.
		
		if (!int_val) int_val = 0;
		if (!int_min) int_min = 0;
		
		int_this = intval (int_this);
		if (int_this <= int_min) int_this = int_val;
		
		return int_this;
		
	}
	
	function remove (item, array) {
		
		var new_array = [];
		for (var i = 0; i < array.length; ++i)
		if (i != item) new_array[i] = array[i];
		
		return new_array;
		
  }
	
	function count (array) {
		
		var count = 0;
		for (var i in array) if (i) ++count;
		return count;
		
	}
  
  function is_email (email) {
    
    var regexp = /^[\.A-z0-9_\-]+[@][A-z0-9_\-]+([.][A-z0-9_\-]+)+[A-z]{1,4}$/;
    return (regexp.exec (email) ? true : false);
    
  }
	
	function LoadingBar (type, show, options) { // Индикатор загрузки
		
		var loading = '', left = 0, top = 0;
		
		options = make_array (options);
		
		if (!options['ajax_area']) options['ajax_area'] = '.ajaxLoading';
		if (!options['area']) options['area'] = 'body';
		
		if (show == 1) {
			
			if (type == 'page')
			loading = $('<div>', { 'class':'ajaxLoading' });
			else if (type == 'area')
			loading = $('<div>', { 'class':'ajaxLoading_h' });
			
			left = elemCenterWidth (loading);
			top = elemCenterHeight (loading);
			
			$(loading).css ({ left:left + 'px', top:top + 'px' });
			$(options['area']).append (loading);
			
		} else $(options['ajax_area']).fadeOut ('slow');
		
	}
	
	function strip_tags (str){
		return str.replace (/<[^>]+>/gi, '');
	}
	
	function html2text (str) {
		
		str = str.replace (/<br\s*[\/]?>/gi, "\n");
		str = strip_tags (str);
		
		return str;
		
	}