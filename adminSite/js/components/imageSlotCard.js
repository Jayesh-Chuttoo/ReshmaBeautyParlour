/* ==========================================================================
   One card on the Pictures page: the picture that is on the website now,
   where it appears, and the controls to replace it.

     renderImageSlotCard(slot)      builds the card from one imageSlots entry
     connectImageSlotCard(card)     makes Choose and Save work on that card

   The file box is hidden and the label after it is styled as a button, so
   the admin sees "Choose a picture" instead of the browser's own file box.
   ========================================================================== */

function renderImageSlotCard(slot) {
  var inputId = "file-" + slot.key;

  return '' +
    '<article class="card imageSlot" data-key="' + slot.key + '" data-group="' + slot.group +
        '" data-name="' + slot.label + '">' +
      '<div class="imageSlotPreview">' + renderPicture(slot.path, slot.label) + "</div>" +
      '<div class="cardBody">' +
        '<span class="tag">' + imageGroupLabel(slot.group) + "</span>" +
        "<h3>" + slot.label + "</h3>" +
        '<p class="small muted">' + slot.where + "</p>" +
        '<p class="small muted">Best size: ' + slot.size + " pixels</p>" +
        '<p class="small slotPath">' + slot.path + "</p>" +
        '<p class="small slotChosen" hidden></p>' +

        '<div class="slotActions">' +
          '<input class="visuallyHidden" type="file" id="' + inputId + '" accept="' +
            adminSettings.pictureTypes.join(",") + '">' +
          '<label class="button buttonSmall buttonPlain slotChoose" for="' + inputId + '">Choose a picture</label>' +
          '<button class="button buttonSmall buttonMain" type="button" data-save disabled>Save picture</button>' +
        "</div>" +
      "</div>" +
    "</article>";
}

function connectImageSlotCard(card) {
  var input = card.querySelector('input[type="file"]');
  var saveButton = card.querySelector("[data-save]");
  var chosenText = card.querySelector(".slotChosen");
  var preview = card.querySelector(".imageSlotPreview");
  var label = card.getAttribute("data-name");

  input.addEventListener("change", function () {
    var file = input.files[0];
    if (!file) { return; }

    // checkPictureFile (formField.js) returns a sentence when the file is refused.
    var problem = checkPictureFile(file);
    if (problem) {
      input.value = "";
      showMessage("This picture cannot be used", problem);
      return;
    }

    // Show the new picture straight away. createObjectURL gives the chosen
    // file a temporary address the page can display; nothing is sent yet.
    preview.innerHTML = renderPicture(URL.createObjectURL(file), label);
    preview.classList.add("isNew");

    chosenText.textContent = "New: " + file.name + " (not saved yet)";
    chosenText.hidden = false;
    saveButton.disabled = false;
  });

  saveButton.addEventListener("click", function () {
    // Backend will handle this later: upload input.files[0] for this slot
    // (card's data-key), store it, and give the client site the new path.
    saveButton.disabled = true;
    preview.classList.remove("isNew");
    chosenText.textContent = "Saved: " + input.files[0].name;

    showMessage("Picture saved", label + " now shows the new picture on the website.");
  });
}
