/* ==========================================================================
   Builds the lines from the cart data, keeps the totals right, and asks
   before removing anything. The confirm pop-up comes from messageModal.js,
   so this page has no pop-up HTML of its own.
   ========================================================================== */

document.addEventListener("DOMContentLoaded", function () {

  // Build the lines using the shared component.
  document.getElementById("cartLines").innerHTML = cartItems.map(renderCartLine).join("");

  // ---- Price changes ----
  // Any line with an oldPrice gets a sentence in the notice above the cart.
  var changes = cartItems.filter(function (item) {
    return item.oldPrice && item.oldPrice !== item.unitPrice;
  }).map(function (item) {
    return "The price of the " + findProduct(item.productId).name.toLowerCase() +
           " changed from " + formatPrice(item.oldPrice) + " to " + formatPrice(item.unitPrice) +
           " since you added it.";
  });

  if (changes.length > 0) {
    var notice = document.getElementById("priceNotice");
    notice.textContent = changes.join(" ") + " The new price is used below.";
    notice.hidden = false;
  }

  // ---- Quantity drop-downs ----
  document.querySelectorAll(".lineQuantity").forEach(function (select) {
    select.addEventListener("change", recalculateTotals);
  });

  // ---- Remove buttons ----
  document.querySelectorAll("[data-remove]").forEach(function (button) {
    button.addEventListener("click", function () {
      // closest() walks up the page until it finds the whole line.
      var line = button.closest(".cartLine");
      var name = button.getAttribute("data-remove");

      askToConfirm(
        "Remove this item?",
        name + " will be taken out of your cart.",
        "Remove item",
        function () {
          // Backend will handle this later: delete the line from the saved cart.
          line.remove();
          recalculateTotals();
        }
      );
    });
  });

  recalculateTotals();
});

/* Works out each line total, then the summary at the side. */
function recalculateTotals() {
  var lines = document.querySelectorAll(".cartLine");
  var subtotal = 0;
  var itemCount = 0;

  lines.forEach(function (line) {
    var unitPrice = Number(line.getAttribute("data-price"));
    var quantity = Number(line.querySelector(".lineQuantity").value);
    var lineTotal = unitPrice * quantity;

    line.querySelector(".lineTotal").textContent = formatPrice(lineTotal);

    subtotal = subtotal + lineTotal;
    itemCount = itemCount + quantity;
  });

  document.getElementById("summaryItems").textContent = itemCount;
  document.getElementById("summarySubtotal").textContent = formatPrice(subtotal);
  document.getElementById("summaryTotal").textContent = formatPrice(subtotal);
  document.getElementById("cartCount").textContent = itemCount;

  // When nothing is left, swap the list and summary for the empty message.
  var cartIsEmpty = (lines.length === 0);
  document.getElementById("emptyCart").hidden = !cartIsEmpty;
  document.getElementById("cartSummary").hidden = cartIsEmpty;
  document.getElementById("priceNotice").hidden =
    cartIsEmpty || document.getElementById("priceNotice").textContent === "";
}
