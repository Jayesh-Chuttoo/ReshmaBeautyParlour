/* ==========================================================================
   Checks the two boxes, then shows the example order or booking from
   orderExamples.js. The "nothing found" message uses the shared pop-up.
   ========================================================================== */

document.addEventListener("DOMContentLoaded", function () {

  fillOrderResult(exampleOrder);
  fillBookingResult(exampleBooking);

  document.getElementById("lookupForm").addEventListener("submit", function (event) {
    event.preventDefault();

    var codeField = document.getElementById("lookupCode");
    var mobileField = document.getElementById("lookupMobile");

    clearFieldErrors([codeField, mobileField]);

    var code = codeField.value.trim().toUpperCase();
    var problems = 0;

    if (code === "") {
      markFieldError(codeField);
      problems++;
    }

    if (!isMobileNumber(mobileField.value)) {
      markFieldError(mobileField);
      problems++;
    }

    // The red messages under the fields are enough here, so no pop-up.
    if (problems > 0) { return; }

    // Hide whatever was shown last time.
    document.getElementById("orderResult").hidden = true;
    document.getElementById("bookingResult").hidden = true;

    // Backend will handle this later: the server looks up the code together
    // with the phone number. For now the first letters decide what is shown.
    if (code.indexOf("ORD-") === 0) {
      showResult("orderResult");
    } else if (code.indexOf("BK-") === 0) {
      showResult("bookingResult");
    } else {
      // The same message is used for a wrong code and a wrong number, so
      // nobody can find a real order by trying codes one after another.
      showMessage("Nothing found",
        "No order or booking matches that code and number. Check both and try again.");
    }
  });
});

/* Writes one order into the order panel. */
function fillOrderResult(order) {
  document.getElementById("orderResultCode").textContent = order.code;
  document.getElementById("orderResultStatus").innerHTML =
    renderStatusTag(orderStatuses, order.trackingStatus);

  document.getElementById("orderTimeline").innerHTML = order.history.map(function (step) {
    return '<li' + (step.done ? ' class="isDone"' : "") + ">" +
             "<h4>" + step.title + "</h4>" +
             '<p class="small muted">' + step.when + "</p>" +
           "</li>";
  }).join("");

  var lines = order.lines.map(function (line) {
    return renderSummaryRow(line.label, formatPrice(line.amount));
  }).join("");

  if (order.delivery) {
    lines = lines + renderSummaryRow(order.delivery.label, formatPrice(order.delivery.amount));
  }

  document.getElementById("orderResultLines").innerHTML =
    lines + renderSummaryRow("Total", formatPrice(orderTotal(order)), true);

  fillSalonMessage("orderResultNote", order.salonMessage);

  document.getElementById("orderWhatsapp").href = whatsappLink("Hello, about order " + order.code);
}

/* Writes one booking into the booking panel. */
function fillBookingResult(booking) {
  document.getElementById("bookingResultCode").textContent = booking.code;
  document.getElementById("bookingResultStatus").innerHTML =
    renderStatusTag(bookingStatuses, booking.status);
  document.getElementById("bookingResultIntro").textContent = booking.intro;

  var lines = booking.details.map(function (detail) {
    return renderSummaryRow(detail.label, detail.value);
  }).join("");

  if (booking.agreedPrice) {
    lines = lines + renderSummaryRow("Agreed price", formatPrice(booking.agreedPrice), true);
  }

  document.getElementById("bookingResultLines").innerHTML = lines;

  fillSalonMessage("bookingResultNote", booking.salonMessage);

  document.getElementById("bookingWhatsapp").href = whatsappLink("Hello, about booking " + booking.code);
}

/* The salon's note is optional: with no message the box stays hidden. */
function fillSalonMessage(elementId, message) {
  var box = document.getElementById(elementId);
  box.textContent = message ? "Message from the salon: " + message : "";
  box.hidden = !message;
}

function showResult(elementId) {
  var panel = document.getElementById(elementId);
  panel.hidden = false;
  panel.scrollIntoView({ behavior: "smooth", block: "start" });
}
