/* ==========================================================================
   The coloured label that shows a status, like "Awaiting payment".
   The words and colours come from statusLabels.js.

     renderStatusTag(orderStatuses, "awaitingPayment")
     setStatusTag(tagElement, bookingStatuses, "confirmed")   // recolour one
   ========================================================================== */

function renderStatusTag(statusList, status) {
  var entry = statusList[status];
  return '<span class="tag ' + entry.colour + '" data-status="' + status + '">' + entry.label + "</span>";
}

function setStatusTag(tagElement, statusList, status) {
  var entry = statusList[status];
  tagElement.textContent = entry.label;
  tagElement.className = "tag " + entry.colour;
  tagElement.setAttribute("data-status", status);
}
