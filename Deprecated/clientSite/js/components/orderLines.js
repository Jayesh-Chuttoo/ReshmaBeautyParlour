/* ==========================================================================
   The lines of an order summary, used by checkout, payment and tracking.

     renderSummaryRow("Delivery", "Rs 150")          a label and an amount
     renderSummaryRow("Total", "Rs 2,500", true)     the bold total line
     renderOrderItem(cartItem)                       photo, name, size, price
   ========================================================================== */

function renderSummaryRow(label, value, isTotal) {
  return '' +
    '<div class="summaryRow' + (isTotal ? " summaryTotal" : "") + '">' +
      "<span>" + label + "</span>" +
      "<span>" + value + "</span>" +
    "</div>";
}

function renderOrderItem(item) {
  var product = findProduct(item.productId);
  var itemWord = (item.quantity === 1) ? " item" : " items";

  return '' +
    '<div class="summaryItem">' +
      renderPicture(product.photos.length ? product.photos[0].path : "", product.name, "summaryPicture") +
      "<div>" +
        "<p>" + product.name + "</p>" +
        '<p class="small muted">Size ' + item.size + " &middot; " + item.quantity + itemWord + "</p>" +
      "</div>" +
      '<span class="small">' + formatPrice(item.unitPrice * item.quantity) + "</span>" +
    "</div>";
}
