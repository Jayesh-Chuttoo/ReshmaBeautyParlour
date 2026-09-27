/* ==========================================================================
   Turns one entry from servicesList into a card.
   The label colour comes from the service's group in serviceCategories.

   Used by services.js:
     grid.innerHTML = servicesList.map(renderServiceCard).join("");
   ========================================================================== */

function renderServiceCard(service) {
  var category = findServiceCategory(service.category);

  var whereLabel = (service.places.length === 2)
    ? "At the salon or at your place"
    : (service.places[0] === "onsite" ? "At the salon only" : "At your place only");

  // The optional line under the price
  var priceNote = service.priceNote
    ? '<p class="small muted">' + service.priceNote + "</p>"
    : "";

  return '' +
    '<article class="card serviceCard" data-category="' + service.category +
        '" data-place="' + service.places.join(" ") + '">' +
      renderPicture(service.image, service.name, "cardPicture") +
      '<div class="cardBody">' +
        '<span class="tag ' + category.tagColour + '">' + category.label + "</span>" +
        "<h3>" + service.name + "</h3>" +
        '<p class="small">' + service.description + "</p>" +
        '<ul class="serviceMeta small">' +
          "<li>" + service.duration + "</li>" +
          "<li>" + whereLabel + "</li>" +
        "</ul>" +
        '<p class="price">' + service.price + "</p>" +
        priceNote +
        '<a class="button buttonSmall buttonMain" href="' +
          sitePath("pages/booking.html?service=" + service.id) + '">Request booking</a>' +
      "</div>" +
    "</article>";
}
