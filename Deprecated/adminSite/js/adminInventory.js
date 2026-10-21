/* ==========================================================================
   1. Build the table from the stock list.
   2. Search and two filters, all working on the same rows.
   3. Changing a stock number recolours the row and confirms.
   4. Edit and Add open pop-up forms.
   ========================================================================== */

document.addEventListener("DOMContentLoaded", function () {

  // ---- 1. Build the rows using the shared component ----
  document.getElementById("stockRows").innerHTML = stockList.map(renderStockRow).join("");
  document.getElementById("lowStockLevel").textContent = adminSettings.lowStockLevel;

  document.querySelectorAll("#stockRows tr").forEach(colourStockRow);
  connectRowButtons();

  // ---- 2. Search and filters ----
  document.getElementById("stockSearch").addEventListener("input", filterStock);
  document.getElementById("categoryFilter").addEventListener("change", filterStock);
  document.getElementById("stockFilter").addEventListener("change", filterStock);

  // ---- 4a. Saving an edit ----
  document.getElementById("editProductForm").addEventListener("submit", function (event) {
    event.preventDefault();

    var priceField = document.getElementById("editPrice");
    var photoField = document.getElementById("editPhoto");
    clearFieldErrors([priceField, photoField]);

    var problems = 0;

    if (priceField.value === "" || Number(priceField.value) < 0) {
      markFieldError(priceField);
      problems++;
    }
    if (!photoIsFine(photoField, "editPhotoError")) {
      problems++;
    }

    if (problems > 0) { return; }

    // Backend will handle this later: save the edited product.
    // The photo, if one was chosen, goes with it: photoField.files[0].
    closeModal("editProductModal");
    photoField.value = "";
    showMessage("Product saved", "The changes are now live on the website.");
  });

  // ---- 4b. Adding a product ----
  document.getElementById("addProductForm").addEventListener("submit", function (event) {
    event.preventDefault();

    var nameField = document.getElementById("newName");
    var priceField = document.getElementById("newPrice");
    var stockField = document.getElementById("newStock");
    var photoField = document.getElementById("newPhoto");

    clearFieldErrors([nameField, priceField, stockField, photoField]);

    var problems = 0;

    if (nameField.value.trim() === "") {
      markFieldError(nameField);
      problems++;
    }
    if (priceField.value === "" || Number(priceField.value) < 0) {
      markFieldError(priceField);
      problems++;
    }
    if (stockField.value === "" || Number(stockField.value) < 0) {
      markFieldError(stockField);
      problems++;
    }
    if (!photoIsFine(photoField, "newPhotoError")) {
      problems++;
    }

    if (problems > 0) { return; }

    // Backend will handle this later: create the product on the server,
    // with the photo in photoField.files[0] if one was chosen.
    // Here the new entry is added to the list and the row is built with
    // the same component the rest of the table uses.
    var newItem = {
      name: nameField.value.trim(),
      category: document.getElementById("newCategory").value,
      categoryLabel: document.getElementById("newCategory").selectedOptions[0].textContent,
      size: "One size",
      price: Number(priceField.value),
      stock: Number(stockField.value)
    };

    document.getElementById("stockRows")
      .insertAdjacentHTML("afterbegin", renderStockRow(newItem));

    colourStockRow(document.querySelector("#stockRows tr"));
    connectRowButtons();
    filterStock();

    closeModal("addProductModal");
    this.reset();
    showMessage("Product added", newItem.name + " is now in the list.");
  });

  filterStock();
});

/* The stock boxes and Edit buttons have to be connected again whenever a
   new row is added, so this lives in its own function. */
function connectRowButtons() {

  document.querySelectorAll(".stockInput").forEach(function (input) {
    // Only connect a box once, however many times this function runs.
    if (input.dataset.ready) { return; }
    input.dataset.ready = "yes";

    // change fires when the person leaves the box, not on every keystroke.
    input.addEventListener("change", function () {
      var row = input.closest("tr");
      var newStock = Number(input.value);

      if (newStock < 0 || isNaN(newStock)) {
        newStock = 0;
        input.value = 0;
      }

      row.setAttribute("data-stock", newStock);
      colourStockRow(row);

      // Backend will handle this later: save the new stock number.
      showMessage("Stock saved",
        row.cells[0].textContent + " now shows " + newStock + " in stock.");

      filterStock();   // the row may no longer match the low-stock filter
    });
  });

  document.querySelectorAll("[data-edit]").forEach(function (button) {
    if (button.dataset.ready) { return; }
    button.dataset.ready = "yes";

    button.addEventListener("click", function () {
      document.getElementById("editProductName").textContent = button.getAttribute("data-edit");
      openModal("editProductModal");
    });
  });
}

/* Uses the shared table filter, with one extra test of its own. */
function filterStock() {
  var rows = document.querySelectorAll("#stockRows tr");
  var searchText = document.getElementById("stockSearch").value;
  var category = document.getElementById("categoryFilter").value;
  var level = document.getElementById("stockFilter").value;

  var shown = filterTableRows(rows, searchText, ["name"], function (row) {
    var stock = Number(row.getAttribute("data-stock"));

    var categoryMatches = (category === "all" || category === row.getAttribute("data-category"));

    var levelMatches = true;
    if (level === "low") { levelMatches = (stock > 0 && stock <= adminSettings.lowStockLevel); }
    if (level === "out") { levelMatches = (stock === 0); }

    return categoryMatches && levelMatches;
  });

  updateTableCount("stockCount", "noRows", shown, "row", "rows");
}

/* An optional photo box: fine when empty, otherwise the file must pass
   checkPictureFile from formField.js. The reason is written under the box. */
function photoIsFine(photoField, errorId) {
  var file = photoField.files[0];
  if (!file) { return true; }

  var problem = checkPictureFile(file);
  if (problem === "") { return true; }

  document.getElementById(errorId).textContent = problem;
  markFieldError(photoField);
  return false;
}
