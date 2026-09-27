/* ==========================================================================
   Turns one entry from serviceCategories into a card on the home page.
   The button opens the services page with that group already chosen.

   Used by home.js:
     grid.innerHTML = serviceCategories.map(renderCategoryCard).join("");
   ========================================================================== */

function renderCategoryCard(category) {
  return '' +
    '<article class="card categoryCard">' +
      renderPicture(category.image, category.label + " services", "cardPicture") +
      '<div class="cardBody">' +
        '<span class="tag ' + category.tagColour + '">' + category.label + "</span>" +
        "<h3>" + category.title + "</h3>" +
        '<p class="small">' + category.text + "</p>" +
        '<a class="button buttonSmall buttonSoft" href="' +
          sitePath("pages/services.html?category=" + category.key) + '">' + category.buttonLabel + "</a>" +
      "</div>" +
    "</article>";
}
