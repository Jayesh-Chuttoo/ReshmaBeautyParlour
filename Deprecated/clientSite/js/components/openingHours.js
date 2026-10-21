/* ==========================================================================
   The opening hours table on the about page, built from
   businessInfo.openingHours. Today's row is highlighted.

   How a page uses it:
     <div id="openingHours"></div>
   ========================================================================== */

function renderOpeningHours() {
  var today = new Date().getDay();     // 0 is Sunday, 1 is Monday...

  return businessInfo.openingHours.map(function (row) {
    var isToday = row.days.indexOf(today) !== -1;
    var hours = row.hours
      ? "<span>" + row.hours + "</span>"
      : '<span class="muted">Closed</span>';

    return '<div class="hoursRow' + (isToday ? " isToday" : "") + '">' +
             "<span>" + row.label + "</span>" + hours +
           "</div>";
  }).join("");
}

document.addEventListener("DOMContentLoaded", function () {
  var holder = document.getElementById("openingHours");
  if (holder) { holder.innerHTML = renderOpeningHours(); }
});
