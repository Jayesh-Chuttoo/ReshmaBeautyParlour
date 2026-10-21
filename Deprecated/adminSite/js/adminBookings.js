/* ==========================================================================
   1. Build the table from the bookings list.
   2. Search and a status filter.
   3. Opening a row fills the pop-up.
   4. Confirming needs a date and time, and a price for quoted services.
      Declining and cancelling need a message.
   ========================================================================== */

var openBookingRow = null;
var statusesNeedingMessage = ["declined", "cancelled"];

document.addEventListener("DOMContentLoaded", function () {

  // ---- 1. Build the rows and the status filter from the data ----
  document.getElementById("bookingRows").innerHTML = bookingsList.map(renderBookingRow).join("");
  document.getElementById("bookingStatusFilter").innerHTML = renderStatusOptions(bookingStatuses);

  // ---- 3. Opening a booking ----
  document.querySelectorAll("#bookingRows [data-open]").forEach(function (button) {
    button.addEventListener("click", function () {
      openBookingRow = button.closest("tr");
      fillBookingModal(openBookingRow);
      openModal("bookingModal");
    });
  });

  // ---- 2. Search and filter ----
  document.getElementById("bookingSearch").addEventListener("input", filterBookings);
  document.getElementById("bookingStatusFilter").addEventListener("change", filterBookings);

  document.getElementById("bookingNewStatus").addEventListener("change", updateVisibleFields);

  // ---- 4. Saving ----
  document.getElementById("bookingStatusForm").addEventListener("submit", function (event) {
    event.preventDefault();

    var status = document.getElementById("bookingNewStatus").value;
    var dateField = document.getElementById("confirmedDate");
    var timeField = document.getElementById("confirmedTime");
    var priceField = document.getElementById("agreedPrice");
    var messageField = document.getElementById("bookingMessage");

    clearFieldErrors([dateField, timeField, priceField, messageField]);

    var problems = 0;

    if (status === "confirmed") {
      if (dateField.value === "") {
        markFieldError(dateField);
        problems++;
      }
      if (timeField.value === "") {
        markFieldError(timeField);
        problems++;
      }

      // A quoted service cannot be confirmed without the agreed price.
      var isNegotiable = openBookingRow.getAttribute("data-negotiable") === "true";
      if (isNegotiable && (priceField.value === "" || Number(priceField.value) < 0)) {
        markFieldError(priceField);
        problems++;
      }
    }

    if (statusesNeedingMessage.indexOf(status) !== -1 && messageField.value.trim() === "") {
      markFieldError(messageField);
      problems++;
    }

    if (problems > 0) { return; }

    // Backend will handle this later: save the confirmed date, time, agreed
    // price and the message to the client.
    setBookingRowStatus(openBookingRow, status);

    closeModal("bookingModal");
    messageField.value = "";

    showMessage("Booking saved",
      openBookingRow.getAttribute("data-code") + " is now " + bookingStatuses[status].label + ".");

    countRequested();
    filterBookings();
  });

  countRequested();
  filterBookings();
});

/* Copies the row's details into the pop-up. */
function fillBookingModal(row) {
  document.getElementById("bookingModalTitle").textContent = row.getAttribute("data-code");
  document.getElementById("bookingModalStatus").textContent =
    bookingStatuses[row.getAttribute("data-status")].label;
  document.getElementById("bookingModalClient").textContent = row.getAttribute("data-client");
  document.getElementById("bookingModalPhone").textContent = row.getAttribute("data-phone");
  document.getElementById("bookingModalService").textContent = row.getAttribute("data-service");
  document.getElementById("bookingModalPlace").textContent = row.getAttribute("data-place");
  document.getElementById("bookingModalAsked").textContent = row.getAttribute("data-asked");

  var notes = row.getAttribute("data-notes");
  document.getElementById("bookingModalNotes").textContent = (notes === "" ? "No notes." : notes);

  document.getElementById("bookingModalWhatsapp").href =
    "https://wa.me/230" + row.getAttribute("data-phone") + "?text=" +
    encodeURIComponent("Hello, about your booking " + row.getAttribute("data-code"));

  document.getElementById("bookingNewStatus").value = "confirmed";
  updateVisibleFields();
}

/* Shows the date, time and price boxes only when they are needed. */
function updateVisibleFields() {
  var status = document.getElementById("bookingNewStatus").value;
  var isConfirming = (status === "confirmed");

  document.getElementById("confirmFields").hidden = !isConfirming;

  var isNegotiable = openBookingRow &&
                     openBookingRow.getAttribute("data-negotiable") === "true";

  document.getElementById("priceField").hidden = !(isConfirming && isNegotiable);

  var needsMessage = statusesNeedingMessage.indexOf(status) !== -1;
  document.getElementById("bookingMessageHint").textContent =
    needsMessage ? "Required for this change." : "Optional when confirming.";
}

function filterBookings() {
  var rows = document.querySelectorAll("#bookingRows tr");
  var searchText = document.getElementById("bookingSearch").value;
  var status = document.getElementById("bookingStatusFilter").value;

  var shown = filterTableRows(rows, searchText, ["code", "client", "phone"], function (row) {
    return status === "all" || status === row.getAttribute("data-status");
  });

  updateTableCount("bookingCount", "noBookings", shown, "booking", "bookings");
}

function countRequested() {
  var waiting = document.querySelectorAll('#bookingRows tr[data-status="requested"]').length;
  document.getElementById("requestedPill").textContent =
    waiting + (waiting === 1 ? " new request" : " new requests");
}
