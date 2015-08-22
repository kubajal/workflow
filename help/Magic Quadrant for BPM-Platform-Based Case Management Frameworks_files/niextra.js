function setNICookie(cookieName,cookieValue) {
 var today = new Date();
 var expire = new Date();
 expire.setTime(today.getTime() + 3600000*24*1095);
 document.cookie = cookieName+"="+encodeURIComponent(cookieValue)
                 + ";expires="+expire.toGMTString()
				 + ";domain="+NTPT_IDCOOKIE_DOMAIN 
				 + ";path=/";
}

function getNICookie(cookieName) {
   var start = document.cookie.indexOf(cookieName+'=');
   var len = start+cookieName.length+1;
   if ((!start) && (cookieName != document.cookie.substring(0,cookieName.length))) return null;
   if (start == -1) return null;
   var end = document.cookie.indexOf(';',len);
   if (end == -1) end = document.cookie.length;
   return unescape(document.cookie.substring(len,end));
}