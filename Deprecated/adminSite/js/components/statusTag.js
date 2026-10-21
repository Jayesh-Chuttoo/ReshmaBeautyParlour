/* ==========================================================================
   The coloured label that shows a status, and the choices of a status
   filter. The words and colours come from adminData.js.

     renderStatusTag(orderStatuses, "awaitingPayment")
     setStatusTag(tagElement, bookingStatuses, "confirmed")
     renderStatusOptions(orderStatuses)    <option>s for a filter drop-down
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

function renderStatusOptions(statusList) {
  var options = '<option value="all">All statuses</option>';

  // Object.keys gives the status names in the order they are written.
  Object.keys(statusList).forEach(function (status) {
    options = options + '<option value="' + status + '">' + statusList[status].label + "</option>";
  });

  return options;
}
