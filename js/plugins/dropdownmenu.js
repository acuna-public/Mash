  var
  menuwidth = '165px',
  disappeardelay = 1E3,
  hidemenu_onclick = 'yes',
  ie4 = document.all,
  ns6 = document.getElementById && !document.all;
  
	if (ie4 || ns6) document.write ('<div id="dropmenudiv" style="display:none; position:absolute; width:' + menuwidth + ';" onmouseover="clearhidemenu ();" onmouseout="dynamichide (event);"></div>');
  
  function getposOffset (a, b) {
    
    for (var c = b == 'left' ? a.offsetLeft : a.offsetTop, d = a.offsetParent; d != null;) {
      
      c = b == 'left' ? c + d.offsetLeft : c + d.offsetTop;
      d = d.offsetParent;
      
    }
    
    return c;
    
  }
  
	function showhide (a, b, c, d, e) {
    
    if (ie4 || ns6) dropmenuobj.style.left = dropmenuobj.style.top = -500;
    
    if (e != '') {
      
      dropmenuobj.widthobj = dropmenuobj.style;
      dropmenuobj.widthobj.width = e;
      
    }
    
    if (b.type == 'click' && $('#dropmenudiv').css ('display') == 'none' || b.type == 'mouseover')
    $('#dropmenudiv').fadeTo ('fast', 0.9);
    else
    b.type == 'click' && $('#dropmenudiv').fadeOut ('fast');
    
  }
  
  function iecompattest () {
    return document.compatMode && document.compatMode != 'BackCompat' ? document.documentElement : document.body;
  }
  
	function clearbrowseredge (a, b){
    
    var c = 0;
    
    if (b == 'rightedge') {
      
      var d = ie4 && !window.opera ? iecompattest ().scrollLeft + iecompattest ().clientWidth - 15 : window.pageXOffset + window.innerWidth - 15;
      
      dropmenuobj.contentmeasure = dropmenuobj.offsetWidth;
      
      if (d - dropmenuobj.x < dropmenuobj.contentmeasure)
      c = dropmenuobj.contentmeasure - a.offsetWidth
      
    } else {
      
      var e = ie4 && !window.opera ? iecompattest ().scrollTop : window.pageYOffset;
      
      d = ie4 && !window.opera ? iecompattest ().scrollTop + iecompattest ().clientHeight - 15 : window.pageYOffset + window.innerHeight - 18;
      
      dropmenuobj.contentmeasure = dropmenuobj.offsetHeight;
      
      if (d - dropmenuobj.y < dropmenuobj.contentmeasure) {
        
        c = dropmenuobj.contentmeasure + a.offsetHeight;
        
        if (dropmenuobj.y - e < dropmenuobj.contentmeasure)
        c = dropmenuobj.y + a.offsetHeight - e;
        
      }
      
    }
    
    return c;
    
  }
  
  function populatemenu (a) {
    if (ie4 || ns6) dropmenuobj.innerHTML = a.join ('');
  }
  
	function dropdownmenu (a, b, c, d) {
    
		if (!d) d = 190;
    
    if (window.event)
    event.cancelBubble = true;
    else
    b.stopPropagation && b.stopPropagation ();
    
    clearhidemenu ();
    
    dropmenuobj = document.getElementById ? document.getElementById ('dropmenudiv') : dropmenudiv;
    
    populatemenu (c);
    
    if (ie4 || ns6) {
      
      showhide (dropmenuobj.style, b, 'visible', 'hidden', d + 'px');
      
      dropmenuobj.x = getposOffset (a, 'left');
      dropmenuobj.y = getposOffset (a, 'top');
      
      dropmenuobj.style.left = dropmenuobj.x - clearbrowseredge (a, 'rightedge') + 'px';
      dropmenuobj.style.top = dropmenuobj.y - clearbrowseredge (a, 'bottomedge') + 
      a.offsetHeight + 'px';
      
    }
    
    return clickreturnvalue ();
    
  }
  
	function clickreturnvalue () {
    return ie4 || ns6 ? false : true;
  }
  
  function contains_ns6 (a, b) {
    
    for (; b.parentNode;) if ((b = b.parentNode) == a) return true;
    return false;
    
  }
  
  function dynamichide (a){
    
    if (ie4 && !dropmenuobj.contains (a.toElement))
    delayhidemenu ();
    else
    ns6 && a.currentTarget != a.relatedTarget && !contains_ns6 (a.currentTarget, a.relatedTarget) && delayhidemenu ();
    
  }
  
	function hidemenu () {
    
    if (typeof dropmenuobj != 'undefined')
    if (ie4 || ns6) $('#dropmenudiv').fadeOut ('fast');
    
  }
  
	function delayhidemenu () {
    //if (ie4 || ns6) delayhide = setTimeout ('hidemenu ()', disappeardelay);
  }
  
  function clearhidemenu () {
    typeof delayhide != 'undefined' && clearTimeout (delayhide);
  }
  
  if (hidemenu_onclick == 'yes') document.onclick = hidemenu;