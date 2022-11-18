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
    return regexp.exec (email);
    
  }
	
	function isUrl (url, protos) {
		
		if (!protos) protos = 'http|https|ftp';
		var regexp = /^(http|https|ftp):\/\/(-\.)?([^\s\/?\.#-]+\.?)+(\/[^\s]*)?$/;
		
		return regexp.test (url);
		
	}
  
  function equals (haystack, needle) {
    return (haystack.substring (0, needle.length) == needle);
  }
  
  function prop (val, total, delim) {
    
    if (!delim) delim = 100;
    return ((val * total) / delim)
    
  }
  
  function rand (min, max) {
    return Math.floor (Math.random () * (max - min + 1)) + min;
  }