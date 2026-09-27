/* ==========================================================================
   Turns one entry from bookingsList into a row of the bookings table.
   ========================================================================== */

function renderBookingRow(booking) {
  var buttonStyle = (booking.status === "requested") ? "buttonMain" : "buttonPlain";

  return '' +
    '<tr data-code="' + booking.code + '" data-status="' + booking.status +
       '" data-client="' + booking.client + '" data-phone="' + booking.phone +
       '" data-service="' + booking.service + '" data-asked="' + booking.asked +
       '" data-place="' + booking.place + '" data-negotiable="' + booking.negotiable +
       '" data-notes="' + booking.notes + '">' +
      "<td>" + booking.code + "</td>" +
      "<td>" + booking.client + "</td>" +
      "<td>" + booking.service + "</td>" +
      "<td>" + booking.asked + "</td>" +
      "<td>" + booking.place + "</td>" +
      "<td>" + renderStatusTag(bookingStatuses, booking.status) + "</td>" +
      '<td class="rowActions">' +
        '<button class="button buttonSmall ' + buttonStyle + '" type="button" data-open>Open</button>' +
      "</td>" +
    "</tr>";
}

/* Writes a new status into a row and recolours its label. */
function setBookingRowStatus(row, status) {
  row.setAttribute("data-status", status);
  setStatusTag(row.querySelector(".tag"), bookingStatuses, status);
}
