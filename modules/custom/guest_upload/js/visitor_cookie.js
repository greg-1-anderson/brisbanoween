function visitorCookieCheckAndSet() {
  // Set the cookie if they dont already have it, but have accepted the privacy policy
  if (!visitorCookieExists(drupalSettings.guest_upload.cookie_name) && visitorCookieGetValue('cookie-agreed') == "2") {
    document.cookie = drupalSettings.guest_upload.cookie_name + "=" + randomId(6) + "; path=/";
  }

  // Revoke the cookie if they rejected the privacy policy and we have one.
  else if (visitorCookieExists(drupalSettings.guest_upload.cookie_name) && visitorCookieGetValue('cookie-agreed') != "2") {
  	document.cookie = drupalSettings.guest_upload.cookie_name + "=; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT";
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
