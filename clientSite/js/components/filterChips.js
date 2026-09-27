/* ==========================================================================
   The round filter buttons on the services and shop pages.

     renderFilterChips("category", "All", serviceCategories)
       builds an "All" chip followed by one chip per entry. Each entry needs
       a key and a label.

     connectFilterChips(holder, function (value) { ... })
       highlights the chip that was tapped and tells your function its value.
   ========================================================================== */

function renderFilterChips(filterName, allLabel, entries) {
  var chips = '<button class="chip isActive" type="button" data-filter="' + filterName +
              '" data-value="all">' + allLabel + "</button>";

  entries.forEach(function (entry) {
    chips = chips + '<button class="chip" type="button" data-filter="' + filterName +
            '" data-value="' + entry.key + '">' + entry.label + "</button>";
  });

  return chips;
}

function connectFilterChips(holder, whenChosen) {
  var chips = holder.querySelectorAll(".chip");

  chips.forEach(function (chip) {
    chip.addEventListener("click", function () {
      chips.forEach(function (other) { other.classList.remove("isActive"); });
      chip.classList.add("isActive");

      whenChosen(chip.getAttribute("data-value"));
    });
  });
}

/* Taps a chip from code, for example when the address says ?category=hair. */
function chooseFilterChip(holder, value) {
  var chip = holder.querySelector('.chip[data-value="' + value + '"]');
  if (chip) { chip.click(); }
}
