/* ==========================================================================
   Turns one entry from ordersList into a row of the orders table, and
   keeps the small job of recolouring a row after its status changes.
   ========================================================================== */

function renderOrderRow(order) {
  var buttonStyle = (order.status === "pendingConfirmation") ? "buttonMain" : "buttonPlain";

  return '' +
    '<tr data-code="' + order.code + '" data-status="' + order.status +
       '" data-client="' + order.client + '" data-phone="' + order.phone +
       '" data-total="' + order.total + '" data-items="' + order.items +
       '" data-fulfilment="' + order.fulfilment + '">' +
      "<td>" + order.code + "</td>" +
      "<td>" + order.date + "</td>" +
      "<td>" + order.client + "</td>" +
      "<td>" + order.phone + "</td>" +
      "<td>" + order.total + "</td>" +
      "<td>" + renderStatusTag(orderStatuses, order.status) + "</td>" +
      '<td class="rowActions">' +
        '<button class="button buttonSmall ' + buttonStyle + '" type="button" data-open>Open</button>' +
      "</td>" +
    "</tr>";
}

/* Writes a new status into a row and recolours its label. */
function setOrderRowStatus(row, status) {
  row.setAttribute("data-status", status);
  setStatusTag(row.querySelector(".tag"), orderStatuses, status);
}
