/* ==========================================================================
   Builds the filter chips and the cards from the data, then filters the
   cards by group and by where the service can be done. The two filters
   work together.
   The home page links here as services.html?category=hair, which chooses
   that group straight away.
   ========================================================================== */

var chosenCategory = "all";
var chosenPlace = "all";

document.addEventListener("DOMContentLoaded", function () {

  // Build every card from the data, using the shared component.
  document.getElementById("serviceGrid").innerHTML =
    servicesList.map(renderServiceCard).join("");

  // Build the two rows of chips from the same lists.
  var categoryChips = document.getElementById("categoryChips");
  var placeChips = document.getElementById("placeChips");

  categoryChips.innerHTML = renderFilterChips("category", "All", serviceCategories);
  placeChips.innerHTML = renderFilterChips("place", "Anywhere", servicePlaces);

  connectFilterChips(categoryChips, function (value) {
    chosenCategory = value;
    applyFilters();
  });

  connectFilterChips(placeChips, function (value) {
    chosenPlace = value;
    applyFilters();
  });

  // services.html?category=nails chooses Nails as the page opens.
  var wantedCategory = new URLSearchParams(window.location.search).get("category");
  if (wantedCategory) { chooseFilterChip(categoryChips, wantedCategory); }

  applyFilters();
});

/* Goes through every card once and decides whether to show it. */
function applyFilters() {
  var cards = document.querySelectorAll(".serviceCard");
  var shown = 0;

  cards.forEach(function (card) {
    var cardPlaces = card.getAttribute("data-place");   // "onsite offsite"

    var categoryMatches = (chosenCategory === "all" ||
                           chosenCategory === card.getAttribute("data-category"));

    // indexOf returns -1 when the word is not in the list at all.
    var placeMatches = (chosenPlace === "all" || cardPlaces.indexOf(chosenPlace) !== -1);

    card.hidden = !(categoryMatches && placeMatches);

    if (!card.hidden) { shown++; }
  });

  document.getElementById("resultCount").textContent =
    "Showing " + shown + (shown === 1 ? " service" : " services");

  document.getElementById("noResults").hidden = (shown > 0);
}
