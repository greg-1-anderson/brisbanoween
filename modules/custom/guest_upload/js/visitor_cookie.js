function visitorCookieCheckAndSet() {
  if (!visitorCookieExists(drupalSettings.guest_upload.cookie_name) && (!drupalSettings.guest_upload.use_eu_cookie_compliance_module || (visitorCookieGetValue('cookie-agreed') > 0))) {
    document.cookie = drupalSettings.guest_upload.cookie_name + "=" + randomId(6) + "; path=/";
  }  
}

function visitorCookieExists(cookie_name) {
  return Boolean(document.cookie.split(';').filter((item) => item.trim().startsWith(cookie_name + '=')).length);
}

function visitorCookieGetValue(cookie_name) {
  return ("; " + document.cookie).split("; " + cookie_name + "=").pop().split(";").shift();
}

function randomId(length) {
   var result           = '';
   var characters       = 'abcdefghjkmnpqrtuvwxyz234689';
   var charactersLength = characters.length;
   for ( var i = 0; i < length; i++ ) {
      result += characters.charAt(Math.floor(Math.random() * charactersLength));
   }
   return result;
}

visitorCookieCheckAndSet();
