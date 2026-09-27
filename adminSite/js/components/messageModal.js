/* ==========================================================================
   Two pop-ups that every page needs, built once here instead of being
   written into fifteen HTML files:

     showMessage("Saved", "Your changes are live.")
       one message and a Close button

     askToConfirm("Remove this item?", "It will leave your cart.",
                  "Remove item", function () { ... })
       a question with a confirming button; the function you pass runs
       only if the person taps it

   Both pop-ups are added to the end of the page automatically.
   ========================================================================== */

/* Remembers what to run when the confirm button is tapped. */
var confirmAction = null;

function showMessage(title, text) {
  document.getElementById("messageModalTitle").textContent = title;
  document.getElementById("messageModalText").textContent = text;
  openModal("messageModal");
}

function askToConfirm(title, text, confirmLabel, whatToDo) {
  document.getElementById("confirmModalTitle").textContent = title;
  document.getElementById("confirmModalText").textContent = text;
  document.getElementById("confirmModalButton").textContent = confirmLabel;

  confirmAction = whatToDo;
  openModal("confirmModal");
}

document.addEventListener("DOMContentLoaded", function () {

  // insertAdjacentHTML("beforeend") adds this to the end of the page
  // without touching anything already there.
  document.body.insertAdjacentHTML("beforeend", '' +
    '<div class="modal" id="messageModal" role="dialog" aria-modal="true">' +
      '<div class="modalBox">' +
        '<button class="modalClose" type="button" data-close aria-label="Close">&times;</button>' +
        '<h3 id="messageModalTitle">Done</h3>' +
        '<p id="messageModalText"></p>' +
        '<div class="modalActions">' +
          '<button class="button buttonSoft" type="button" data-close>Close</button>' +
        "</div>" +
      "</div>" +
    "</div>" +

    '<div class="modal" id="confirmModal" role="dialog" aria-modal="true">' +
      '<div class="modalBox">' +
        '<button class="modalClose" type="button" data-close aria-label="Close">&times;</button>' +
        '<h3 id="confirmModalTitle">Are you sure?</h3>' +
        '<p id="confirmModalText"></p>' +
        '<div class="modalActions">' +
          '<button class="button buttonMain" type="button" id="confirmModalButton">Yes</button>' +
          '<button class="button buttonPlain" type="button" data-close>Cancel</button>' +
        "</div>" +
      "</div>" +
    "</div>");

  document.getElementById("confirmModalButton").addEventListener("click", function () {
    closeModal("confirmModal");

    if (confirmAction) {
      confirmAction();       // run whatever the page asked for
      confirmAction = null;  // forget it, so it cannot run twice
    }
  });
});
