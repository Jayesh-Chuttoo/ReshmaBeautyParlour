/* ==========================================================================
   Builds the footer from businessInfo, so the address and phone number are
   written in one place only.

   How a page uses it:
     <footer id="siteFooter"></footer>
   The links follow data-root on the <body>, like the header.
   ========================================================================== */

var footerLinks = [
  { label: "Services", file: "pages/services.html" },
  { label: "Shop", file: "pages/shop.html" },
  { label: "Request a booking", file: "pages/booking.html" },
  { label: "Track order or booking", file: "pages/track.html" }
];

function renderSiteFooter() {
  var pageLinks = footerLinks.map(function (link) {
    return '<li><a href="' + sitePath(link.file) + '">' + link.label + "</a></li>";
  }).join("");

  return '' +
    '<div class="wrap">' +
      '<div class="footerGrid">' +

        "<div>" +
          "<h4>" + businessInfo.name + "</h4>" +
          '<p class="small">' + businessInfo.address + "<br>" + businessInfo.openingSummary + "</p>" +
        "</div>" +

        "<div>" +
          "<h4>Pages</h4>" +
          "<ul>" + pageLinks + "</ul>" +
        "</div>" +

        "<div>" +
          "<h4>Talk to us</h4>" +
          "<ul>" + renderContactLinks(false) + "</ul>" +
        "</div>" +

      "</div>" +
      '<p class="footerBottom">&copy; 2026 Infinity Digital Solutions. All Rights Reserved.</p>' +
    "</div>";
}

document.addEventListener("DOMContentLoaded", function () {
  var holder = document.getElementById("siteFooter");
  if (!holder) { return; }

  holder.className = "siteFooter";
  holder.innerHTML = renderSiteFooter();
});
