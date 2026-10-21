/* ==========================================================================
   1. Fill in the order and the salon's Juice details.
   2. Copy buttons for the code, the amount and the Juice number.
   3. "I have paid" asks for confirmation first, then swaps itself for a
      message. The confirm pop-up comes from messageModal.js.

   Backend will handle this later: "order" will be the order that was just
   placed, sent by the server, instead of the example from orderExamples.js.
   ========================================================================== */

var order = exampleOrder;

document.addEventListener("DOMContentLoaded", function () {

  var total = orderTotal(order);
  var totalText = formatPrice(total);

  // ---- 1. The order and the salon's details ----
  document.getElementById("purchaseCode").textContent = order.code;
  document.getElementById("orderAmount").textContent = totalText;
  document.getElementById("payBefore").textContent = order.payBefore;
  document.querySelectorAll(".codeCopy").forEach(function (element) {
    element.textContent = order.code;
  });

  document.getElementById("juiceNumber").textContent = businessInfo.juiceNumber;
  document.getElementById("juiceAccount").textContent =
    "Account name: " + businessInfo.juiceAccountName;

  // The summary at the side, built with the shared order lines.
  var lines = order.lines.map(function (line) {
    return renderSummaryRow(line.label, formatPrice(line.amount));
  }).join("");

  if (order.delivery) {
    lines = lines + renderSummaryRow(order.delivery.label, formatPrice(order.delivery.amount));
  } else {
    lines = lines + renderSummaryRow("Pickup at the salon", formatPrice(0));
  }

  document.getElementById("paymentLines").innerHTML =
    lines + renderSummaryRow("Total", totalText, true);

  document.getElementById("statusLine").innerHTML = renderStatusTag(orderStatuses, order.status);

  document.getElementById("screenshotButton").href = whatsappLink(
    "Hello, here is my payment for " + order.code + " - " + totalText);

  // ---- 2. Copy buttons ----
  document.getElementById("copyCodeButton").addEventListener("click", function () {
    copyText(order.code, this);
  });

  document.getElementById("copyAmountButton").addEventListener("click", function () {
    copyText(String(total), this);
  });

  document.getElementById("copyJuiceButton").addEventListener("click", function () {
    copyText(businessInfo.juiceNumber.replace(/\s/g, ""), this);
  });

  // ---- 3. "I have paid" ----
  var paidButton = document.getElementById("paidButton");

  paidButton.addEventListener("click", function () {
    askToConfirm(
      "Have you sent the payment?",
      'Tap "Yes, I have paid" only after sending ' + totalText +
        " on MCB Juice and sending us the screenshot on WhatsApp.",
      "Yes, I have paid",
      function () {
        // Backend will handle this later: tell the server the client says
        // they paid, so the order moves to Pending confirmation.

        // Hide the button so it cannot be pressed twice.
        paidButton.hidden = true;
        document.getElementById("paidMessage").hidden = false;

        setStatusTag(document.querySelector("#statusLine .tag"), orderStatuses, "pendingConfirmation");
      }
    );
  });
});
