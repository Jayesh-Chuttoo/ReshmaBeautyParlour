/* ==========================================================================
   One filtering routine shared by the stock, orders and bookings tables.

   You give it the rows, the text typed in the search box, which data-
   attributes that text should be searched in, and a test of your own for
   anything extra (a status, a stock level). It hides the rows that do not
   match and returns how many are left.

   Example, from adminOrders.js:
     var shown = filterTableRows(rows, searchText, ["code", "client", "phone"],
       function (row) {
         return status === "all" || status === row.getAttribute("data-status");
       });
   ========================================================================== */

function filterTableRows(rows, searchText, searchFields, extraTest) {
  var wanted = searchText.trim().toLowerCase();
  var shown = 0;

  rows.forEach(function (row) {
    // Join the searchable columns into one string, then look inside it.
    var haystack = "";
    searchFields.forEach(function (field) {
      haystack = haystack + " " + row.getAttribute("data-" + field);
    });

    var matchesSearch = (wanted === "" || haystack.toLowerCase().indexOf(wanted) !== -1);
    var matchesRest = extraTest ? extraTest(row) : true;

    row.hidden = !(matchesSearch && matchesRest);

    if (!row.hidden) { shown = shown + 1; }
  });

  return shown;
}

/* Writes "5 orders" or "1 order" and shows the empty message when needed. */
function updateTableCount(countId, emptyId, shown, oneName, manyName) {
  document.getElementById(countId).textContent =
    shown + " " + (shown === 1 ? oneName : manyName);

  document.getElementById(emptyId).hidden = (shown > 0);
}
