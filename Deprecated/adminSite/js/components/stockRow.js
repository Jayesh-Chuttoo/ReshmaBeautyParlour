/* ==========================================================================
   Turns one entry from stockList into a row of the stock table.
   The row carries its name, category and stock so the filters can read them
   without asking the server for anything.
   ========================================================================== */

function renderStockRow(item) {
  var stateTag = (item.stock === 0)
    ? '<span class="tag tagGrey">Sold out</span>'
    : '<span class="tag">Active</span>';

  return '' +
    '<tr data-name="' + item.name + '" data-category="' + item.category +
       '" data-stock="' + item.stock + '">' +
      "<td>" + item.name + "</td>" +
      "<td>" + item.categoryLabel + "</td>" +
      "<td>" + item.size + "</td>" +
      "<td>" + formatPrice(item.price) + "</td>" +
      '<td><input class="stockInput" type="number" min="0" value="' + item.stock + '"></td>' +
      "<td>" + stateTag + "</td>" +
      '<td class="rowActions">' +
        '<button class="button buttonSmall buttonPlain" type="button" data-edit="' +
          item.name + " (" + item.size + ')">Edit</button>' +
      "</td>" +
    "</tr>";
}

/* Colours a row by how much stock is left. */
function colourStockRow(row) {
  var stock = Number(row.getAttribute("data-stock"));

  row.classList.remove("isLow", "isOut");

  if (stock === 0) {
    row.classList.add("isOut");
  } else if (stock <= adminSettings.lowStockLevel) {
    row.classList.add("isLow");
  }
}
