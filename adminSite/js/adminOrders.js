/* ==========================================================================
   1. Build the table from the orders list.
   2. Search and a status filter.
   3. Opening a row fills the pop-up from that row.
   4. Some status changes cannot be saved without a message.
   ========================================================================== */

var openRow = null;

// The statuses that need a message to the client before they can be saved.
var statusesNeedingMessage = ["cancelled", "awaitingPayment"];

document.addEventListener("DOMContentLoaded", function () {

  // ---- 1. Build the rows and the status filter from the data ----
  document.getElementById("orderRows").innerHTML = ordersList.map(renderOrderRow).join("");
  document.getElementById("statusFilter").innerHTML = renderStatusOptions(orderStatuses);

  // ---- 3. Opening an order ----
  document.querySelectorAll("#orderRows [data-open]").forEach(function (button) {
    button.addEventListener("click", function () {
      openRow = button.closest("tr");
      fillOrderModal(openRow);
      openModal("orderModal");
    });
  });

  // ---- 2. Search and filter ----
  document.getElementById("orderSearch").addEventListener("input", filterOrders);
  document.getElementById("statusFilter").addEventListener("change", filterOrders);

  // The hint under the message box changes with the chosen status.
  document.getElementById("newStatus").addEventListener("change", function (event) {
    var needed = statusesNeedingMessage.indexOf(event.target.value) !== -1;
    document.getElementById("messageHint").textContent =
      needed ? "Required for this change." : "Optional for most changes.";
  });

  // ---- 4. Saving a status ----
  document.getElementById("statusForm").addEventListener("submit", function (event) {
    event.preventDefault();

    var status = document.getElementById("newStatus").value;
    var messageField = document.getElementById("statusMessage");

    clearFieldError(messageField);

    var needsMessage = statusesNeedingMessage.indexOf(status) !== -1;

    if (needsMessage && messageField.value.trim() === "") {
      markFieldError(messageField);
      return;
    }

    // Backend will handle this later: save the status, write the history
    // entry with who changed it and when, and release stock if cancelled.
    setOrderRowStatus(openRow, status);

    closeModal("orderModal");
    messageField.value = "";

    showMessage("Status saved",
      openRow.getAttribute("data-code") + " is now " + orderStatuses[status].label + ".");

    countPending();
    filterOrders();
  });

  countPending();
  filterOrders();
});

/* Copies the row's details into the pop-up. */
function fillOrderModal(row) {
  document.getElementById("orderModalTitle").textContent = row.getAttribute("data-code");
  document.getElementById("orderModalStatus").textContent =
    orderStatuses[row.getAttribute("data-status")].label;
  document.getElementById("orderModalClient").textContent = row.getAttribute("data-client");
  document.getElementById("orderModalPhone").textContent = row.getAttribute("data-phone");
  document.getElementById("orderModalTotal").textContent = row.getAttribute("data-total");
  document.getElementById("orderModalFulfilment").textContent = row.getAttribute("data-fulfilment");
  document.getElementById("orderModalItems").textContent = row.getAttribute("data-items");

  document.getElementById("orderModalWhatsapp").href =
    "https://wa.me/230" + row.getAttribute("data-phone") + "?text=" +
    encodeURIComponent("Hello, about your order " + row.getAttribute("data-code"));
}

/* Uses the shared table filter, searching three columns at once. */
function filterOrders() {
  var rows = document.querySelectorAll("#orderRows tr");
  var searchText = document.getElementById("orderSearch").value;
  var status = document.getElementById("statusFilter").value;

  var shown = filterTableRows(rows, searchText, ["code", "client", "phone"], function (row) {
    return status === "all" || status === row.getAttribute("data-status");
  });

  updateTableCount("orderCount", "noOrders", shown, "order", "orders");
}

/* Counts the orders still waiting to be checked. */
function countPending() {
  var waiting = document.querySelectorAll('#orderRows tr[data-status="pendingConfirmation"]').length;
  document.getElementById("pendingPill").textContent = waiting + " waiting to be checked";
}
