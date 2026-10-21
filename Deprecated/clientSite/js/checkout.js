/* ==========================================================================
   1. Build the order recap from the cart data, with the shared order lines.
   2. Show the address box and the delivery fee only when delivery is chosen.
   3. Check the form, then show the purchase code.
   ========================================================================== */

var subtotal = 0;

document.addEventListener("DOMContentLoaded", function () {

  // ---- 1. The recap ----
  cartItems.forEach(function (item) {
    subtotal = subtotal + (item.unitPrice * item.quantity);
  });

  document.getElementById("summaryItems").innerHTML = cartItems.map(renderOrderItem).join("");
  document.getElementById("deliveryFeeLabel").textContent = formatPrice(businessInfo.deliveryFee);
  showTotals(false);

  // ---- 2. Pickup or delivery ----
  document.querySelectorAll('input[name="fulfilment"]').forEach(function (radio) {
    radio.addEventListener("change", function () {
      var wantsDelivery = (radio.value === "delivery" && radio.checked);

      document.getElementById("addressField").hidden = !wantsDelivery;
      showTotals(wantsDelivery);
    });
  });

  // ---- 3. Placing the order ----
  document.getElementById("checkoutForm").addEventListener("submit", function (event) {
    event.preventDefault();

    var nameField = document.getElementById("fullName");
    var mobileField = document.getElementById("mobile");
    var emailField = document.getElementById("email");
    var addressField = document.getElementById("address");
    var acknowledgeBox = document.getElementById("paymentAck");

    clearFieldErrors([nameField, mobileField, emailField, addressField, acknowledgeBox]);

    var problems = 0;

    if (nameField.value.trim() === "") {
      markFieldError(nameField);
      problems++;
    }

    if (!isMobileNumber(mobileField.value)) {
      markFieldError(mobileField);
      problems++;
    }

    // Email is optional, so it is only checked if something was typed.
    if (emailField.value.trim() !== "" && !looksLikeEmail(emailField.value)) {
      markFieldError(emailField);
      problems++;
    }

    // The address is only needed when delivery is chosen.
    var deliveryChosen = document.querySelector('input[name="fulfilment"]:checked').value === "delivery";
    if (deliveryChosen && addressField.value.trim() === "") {
      markFieldError(addressField);
      problems++;
    }

    if (!acknowledgeBox.checked) {
      markFieldError(acknowledgeBox);
      problems++;
    }

    if (problems > 0) {
      reportProblems(problems);
      return;
    }

    // Backend will handle this later: the server creates the order, reserves
    // the stock and sends back the real purchase code.
    document.getElementById("newOrderCode").textContent = makeCode("ORD");
    openModal("orderPlacedModal");
  });
});

/* Writes the subtotal, the delivery line when needed, and the total. */
function showTotals(withDelivery) {
  var total = withDelivery ? subtotal + businessInfo.deliveryFee : subtotal;

  document.getElementById("summaryTotals").innerHTML =
    renderSummaryRow("Subtotal", formatPrice(subtotal)) +
    (withDelivery ? renderSummaryRow("Delivery", formatPrice(businessInfo.deliveryFee)) : "") +
    renderSummaryRow("Total", formatPrice(total), true);
}
