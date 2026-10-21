/* ==========================================================================
   Turns one entry from productsList into a card.
   A sold-out product keeps its place in the grid but gets a WhatsApp link
   instead of the button that opens the product.

   Used by home.js and shop.js:
     grid.innerHTML = productsList.map(renderProductCard).join("");
   ========================================================================== */

function renderProductCard(product) {
  var category = findProductCategory(product.category);
  var soldOut = isSoldOut(product);
  var productAddress = sitePath("pages/product.html?id=" + product.id);

  var tag = soldOut
    ? '<span class="tag tagGrey">Sold out</span>'
    : '<span class="tag ' + category.tagColour + '">' + category.label + "</span>";

  var action = soldOut
    ? '<a class="button buttonSmall buttonPlain" href="' + whatsappLink(
        "Hello, is the " + product.name + " coming back in stock?") + '">Ask on WhatsApp</a>'
    : '<a class="button buttonSmall buttonSoft" href="' + productAddress + '">View product</a>';

  var firstPhoto = product.photos.length ? product.photos[0].path : "";

  return '' +
    '<article class="card productCard' + (soldOut ? " isSoldOut" : "") +
        '" data-category="' + product.category +
        '" data-price="' + product.price + '" data-added="' + product.added + '">' +
      '<a href="' + productAddress + '">' +
        renderPicture(firstPhoto, product.name, "cardPicture") +
      "</a>" +
      '<div class="cardBody">' +
        tag +
        '<h3><a href="' + productAddress + '">' + product.name + "</a></h3>" +
        '<p class="price">' + formatPrice(product.price) + "</p>" +
        '<p class="small muted">' + product.note + "</p>" +
        action +
      "</div>" +
    "</article>";
}
