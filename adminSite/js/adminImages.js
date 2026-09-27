/* ==========================================================================
   1. Build one card per picture from imageSlots.js.
   2. Search by name and filter by group. The shared table filter works on
      cards too, because it only reads data- attributes and hides elements.
   The Choose and Save buttons on each card are run by imageSlotCard.js.
   ========================================================================== */

document.addEventListener("DOMContentLoaded", function () {

  // ---- 1. The cards ----
  var grid = document.getElementById("imageGrid");
  grid.innerHTML = imageSlots.map(renderImageSlotCard).join("");
  grid.querySelectorAll(".imageSlot").forEach(connectImageSlotCard);

  // ---- 2. Search and filter ----
  var groupOptions = '<option value="all">All pictures</option>';
  imageGroups.forEach(function (group) {
    groupOptions = groupOptions + '<option value="' + group.key + '">' + group.label + "</option>";
  });
  document.getElementById("groupFilter").innerHTML = groupOptions;

  document.getElementById("imageSearch").addEventListener("input", filterImages);
  document.getElementById("groupFilter").addEventListener("change", filterImages);

  filterImages();
});

function filterImages() {
  var cards = document.querySelectorAll(".imageSlot");
  var searchText = document.getElementById("imageSearch").value;
  var group = document.getElementById("groupFilter").value;

  var shown = filterTableRows(cards, searchText, ["name"], function (card) {
    return group === "all" || group === card.getAttribute("data-group");
  });

  updateTableCount("imageCount", "noImages", shown, "picture", "pictures");
}
