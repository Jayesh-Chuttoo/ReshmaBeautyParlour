/* ==========================================================================
   Builds the header, the navigation and the mobile menu button, and runs
   the open-and-close behaviour for small screens.

   How a page uses it:
     <body data-root="../">
       <div id="siteHeader" data-page="shop"></div>

       data-page  (on the placeholder) which link is the current page
       data-root  (on the body) "" from index.html, "../" from pages/

   Change the menu here once and every page gets the change.
   ========================================================================== */

var navigationLinks = [
  { key: "home", label: "Home", file: "index.html" },
  { key: "services", label: "Services", file: "pages/services.html" },
  { key: "shop", label: "Shop", file: "pages/shop.html" },
  { key: "about", label: "About", file: "pages/about.html" },
  { key: "track", label: "Track order", file: "pages/track.html" },
  { key: "cart", label: "Cart", file: "pages/cart.html" }
];

function renderSiteHeader(currentPage) {
  var links = "";

  navigationLinks.forEach(function (link) {
    // The cart link carries the little number of items.
    var extra = "";
    if (link.key === "cart") {
      extra = ' <span class="cartCount" id="cartCount">' + countCartItems() + "</span>";
    }

    var current = (link.key === currentPage) ? ' class="isCurrent"' : "";

    links = links +
      "<li><a" + current + ' href="' + sitePath(link.file) + '">' + link.label + extra + "</a></li>";
  });

  return '' +
    '<div class="wrap headerInner">' +
      '<a class="brand" href="' + sitePath("index.html") + '">' +
        renderPicture(siteImages.logo, "", "brandLogo") +
        "<span>" +
          '<span class="brandName">' + businessInfo.name + "</span>" +
          '<span class="brandTag">' + businessInfo.tagline + "</span>" +
        "</span>" +
      "</a>" +
      '<button class="menuButton" type="button" aria-expanded="false" aria-label="Open menu">&#9776;</button>' +
      '<nav class="siteNav"><ul>' + links + "</ul></nav>" +
    "</div>";
}

/* Fill the placeholder as soon as the page is ready. */
document.addEventListener("DOMContentLoaded", function () {
  var holder = document.getElementById("siteHeader");
  if (!holder) { return; }

  holder.className = "siteHeader";
  holder.innerHTML = renderSiteHeader(holder.getAttribute("data-page"));

  // The browser tab icon follows the logo, so changing the logo in
  // siteImages.js (or in the admin) changes it too.
  var tabIcon = document.querySelector('link[rel="icon"]');
  if (tabIcon) { tabIcon.href = sitePath(siteImages.logo); }

  // The menu button only exists once the header above has been built,
  // which is why this listener is set up here and not in a separate file.
  var menuButton = holder.querySelector(".menuButton");
  var nav = holder.querySelector(".siteNav");

  menuButton.addEventListener("click", function () {
    var isOpen = nav.classList.toggle("isOpen");
    menuButton.setAttribute("aria-expanded", isOpen ? "true" : "false");
    menuButton.textContent = isOpen ? "\u2715" : "\u2630";
  });
});
