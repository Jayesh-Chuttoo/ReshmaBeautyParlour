/* ==========================================================================
   The three small helpers every pop-up on the site uses, plus the two
   behaviours they all share (click the background to close, press Escape
   to close). Load this before any component that opens a pop-up.
   ========================================================================== */

/* Show a pop-up by its id. */
function openModal(modalId) {
  var modal = document.getElementById(modalId);
  if (!modal) { return; }

  modal.classList.add("isOpen");
  document.body.style.overflow = "hidden";   // stop the page behind scrolling
}

/* Hide a pop-up by its id. */
function closeModal(modalId) {
  var modal = document.getElementById(modalId);
  if (!modal) { return; }

  modal.classList.remove("isOpen");
  document.body.style.overflow = "";
}

/* Copy a piece of text and briefly change the button label so the person
   knows it worked. Used for the purchase code and the Juice number. */
function copyText(text, button) {
  if (navigator.clipboard) {
    navigator.clipboard.writeText(text);
  }
  if (!button) { return; }

  var original = button.textContent;
  button.textContent = "Copied";
  setTimeout(function () { button.textContent = original; }, 1500);
}

/* Set up once, for every pop-up on every page. */
document.addEventListener("DOMContentLoaded", function () {

  // The dark background, the X, and any button marked data-close all close it.
  document.addEventListener("click", function (event) {
    var target = event.target;
    var clickedBackdrop = target.classList.contains("modal");
    var clickedCloser = target.hasAttribute("data-close");

    if (clickedBackdrop || clickedCloser) {
      var modal = target.closest(".modal");
      if (modal) { closeModal(modal.id); }
    }
  });

  // Escape closes whichever pop-up is open.
  document.addEventListener("keydown", function (event) {
    if (event.key !== "Escape") { return; }

    var openOne = document.querySelector(".modal.isOpen");
    if (openOne) { closeModal(openOne.id); }
  });
});
