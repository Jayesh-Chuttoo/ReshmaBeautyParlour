/* ==========================================================================
   Turns one entry from cartItems into a row of the cart.
   The name, photo and group are read from the product in products.js.
   The index is passed in so each row's controls have their own id.

   Used by cart.js:
     holder.innerHTML = cartItems.map(renderCartLine).join("");
   ========================================================================== */

function renderCartLine(item, index) {
  var product = findProduct(item.productId);
  var category = findProductCategory(product.category);

  // The quantity choices, 1 up to the limit in businessInfo.
  var options = "";
  for (var number = 1; number <= businessInfo.maxPerCartLine; number++) {
    var selected = (number === item.quantity) ? " selected" : "";
    options = options + '<option value="' + number + '"' + selected + ">" + number + "</option>";
  }

  return '' +
    '<article class="cartLine panel" data-price="' + item.unitPrice + '" data-index="' + index + '">' +
      renderPicture(product.photos.length ? product.photos[0].path : "", product.name, "cartLinePicture") +

      '<div class="cartLineInfo">' +
        "<h3>" + product.name + "</h3>" +
        '<p class="small muted">Size ' + item.size + " &middot; " + category.label + "</p>" +
        '<p class="small">' + formatPrice(item.unitPrice) + " each</p>" +
        '<button class="linkButton" type="button" data-remove="' + product.name + '">Remove</button>' +
      "</div>" +

      '<div class="cartLineRight">' +
        '<label class="small muted" for="quantity' + index + '">Quantity</label>' +
        '<select class="lineQuantity" id="quantity' + index + '">' + options + "</select>" +
        '<p class="price lineTotal">' + formatPrice(item.unitPrice * item.quantity) + "</p>" +
      "</div>" +
    "</article>";
}
