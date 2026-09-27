/* ==========================================================================
   The list of ways to reach the salon: phone, WhatsApp, email and the
   social pages. The footer and the about page both use it, so a new phone
   number or a new Instagram page only has to be typed in businessInfo.js.

     renderContactLinks(true)    with the social pages
     renderContactLinks(false)   phone, WhatsApp and email only
   ========================================================================== */

function renderContactLinks(withSocial) {
  var items =
    '<li><a href="tel:' + businessInfo.phone.replace(/\s/g, "") + '">' + businessInfo.phone + "</a></li>" +
    '<li><a href="' + whatsappLink("") + '">WhatsApp</a></li>' +
    '<li><a href="mailto:' + businessInfo.email + '">' + businessInfo.email + "</a></li>";

  if (withSocial) {
    businessInfo.socialLinks.forEach(function (link) {
      items = items + '<li><a href="' + link.address + '">' + link.label + "</a></li>";
    });
  }

  return items;
}
