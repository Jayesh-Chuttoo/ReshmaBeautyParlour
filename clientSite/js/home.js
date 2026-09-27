/* ==========================================================================
   1. Show one card per service group, using the shared category card.
   2. Show the three newest products, using the shared product card.
   3. Check the small question form inside its pop-up.
   ========================================================================== */

document.addEventListener("DOMContentLoaded", function () {

  // ---- 1. The service groups ----
  document.getElementById("categoryCards").innerHTML =
    serviceCategories.map(renderCategoryCard).join("");

  // ---- 2. The three newest products ----
  // slice() makes a copy, so sorting it does not reorder the real list.
  // The biggest "added" number comes first; slice(0, 3) keeps three.
  var newest = productsList.slice().sort(function (a, b) {
    return b.added - a.added;
  }).slice(0, 3);

  document.getElementById("newProducts").innerHTML =
    newest.map(renderProductCard).join("");

  // ---- 3. The question form ----
  document.getElementById("questionForm").addEventListener("submit", function (event) {
    event.preventDefault();   // stop the page reloading

    var nameField = document.getElementById("questionName");
    var messageField = document.getElementById("questionMessage");

    clearFieldErrors([nameField, messageField]);

    var problems = 0;

    // trim() removes spaces at both ends, so " " counts as empty.
    if (nameField.value.trim() === "") {
      markFieldError(nameField);
      problems++;
    }

    if (messageField.value.trim() === "") {
      markFieldError(messageField);
      problems++;
    }

    if (problems > 0) { return; }

    // Backend will handle this later: send the question to the server.
    closeModal("questionModal");
    this.reset();

    showMessage("Question sent", "Thank you. We will reply on WhatsApp, usually the same day.");
  });
});
