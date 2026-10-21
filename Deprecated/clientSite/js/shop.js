/* ==========================================================================
   Builds the chips and the cards, filters them by group and sorts them
   three ways.
   ========================================================================== */

var activeCategory = "all";

document.addEventListener("DOMContentLoaded", function () {

  // Build every card from the data, using the shared component.
  document.getElementById("productGrid").innerHTML =
    productsList.map(renderProductCard).join("");

  // Build the chips from the product groups in products.js.
  var chipHolder = document.getElementById("categoryChips");
  chipHolder.innerHTML = renderFilterChips("category", "All", productCategories);

  connectFilterChips(chipHolder, function (value) {
    activeCategory = value;
    filterProducts();
  });

  document.getElementById("sortSelect").addEventListener("change", function (event) {
    sortProducts(event.target.value);
  });

  sortProducts("newest");
  filterProducts();
});

/* Show only the cards in the chosen group. */
function filterProducts() {
  var cards = document.querySelectorAll(".productCard");
  var shown = 0;

  cards.forEach(function (card) {
    var matches = (activeCategory === "all" ||
                   card.getAttribute("data-category") === activeCategory);

    card.hidden = !matches;
    if (matches) { shown++; }
  });

  document.getElementById("productCount").textContent =
    "Showing " + shown + (shown === 1 ? " product" : " products");

  document.getElementById("emptyCategory").hidden = (shown > 0);
}

/* Re-order the cards inside the grid.
   The trick: adding an element that is already on the page MOVES it, so
   appending the sorted list rearranges the grid. */
function sortProducts(how) {
  var grid = document.getElementById("productGrid");

  // Array.from turns the list of elements into a real array we can sort.
  var cards = Array.from(grid.querySelectorAll(".productCard"));

  cards.sort(function (cardA, cardB) {
    var priceA = Number(cardA.getAttribute("data-price"));
    var priceB = Number(cardB.getAttribute("data-price"));
    var addedA = Number(cardA.getAttribute("data-added"));
    var addedB = Number(cardB.getAttribute("data-added"));

    if (how === "priceLow") { return priceA - priceB; }   // negative: A first
    if (how === "priceHigh") { return priceB - priceA; }
    return addedB - addedA;                               // newest first
  });

  cards.forEach(function (card) { grid.appendChild(card); });
}
