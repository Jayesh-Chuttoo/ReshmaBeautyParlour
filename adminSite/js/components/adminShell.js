/* ==========================================================================
   Builds the admin top bar and the side menu, which are the same on every
   admin screen.

   How an admin page uses it:
     <header id="adminHeader"></header>
     <nav id="adminMenu" data-page="orders"></nav>

   Add a screen to adminMenuLinks and it appears in every menu.
   ========================================================================== */

var adminMenuLinks = [
  { key: "stock", label: "Stock", file: "adminInventory.html" },
  { key: "orders", label: "Orders", file: "adminOrders.html" },
  { key: "bookings", label: "Bookings", file: "adminBookings.html" },
  { key: "images", label: "Pictures", file: "adminImages.html" },
  { key: "account", label: "Sign-in details", file: "adminAccount.html" }
];

function renderAdminHeader() {
  return '' +
    '<div class="headerInner">' +
      '<a class="adminTitle" href="adminInventory.html">' + adminSettings.businessName + " admin</a>" +
      '<span class="adminUser">Signed in as ' + adminAccount.email + " &middot; " +
        // Backend will handle this later: signing out ends the session on the server.
        '<a href="../index.html">Sign out</a></span>' +
    "</div>";
}

function renderAdminMenu(currentPage) {
  var items = "";

  adminMenuLinks.forEach(function (link) {
    var current = (link.key === currentPage) ? ' class="isCurrent"' : "";
    items = items + "<li><a" + current + ' href="' + link.file + '">' + link.label + "</a></li>";
  });

  // The client website is a separate site, so it opens in a new tab.
  items = items + '<li><a href="' + adminSettings.clientSiteAddress +
          'index.html" target="_blank" rel="noopener">View website</a></li>';

  return '<ul class="adminMenu">' + items + "</ul>";
}

document.addEventListener("DOMContentLoaded", function () {
  var header = document.getElementById("adminHeader");
  if (header) {
    header.className = "adminHeader";
    header.innerHTML = renderAdminHeader();
  }

  var menu = document.getElementById("adminMenu");
  if (menu) {
    menu.innerHTML = renderAdminMenu(menu.getAttribute("data-page"));
  }
});
