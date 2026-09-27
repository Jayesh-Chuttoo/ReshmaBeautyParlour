/* ==========================================================================
   Every picture on the website goes through this component, so a missing
   file never shows as a broken image: a soft coloured box with the picture's
   name is shown instead, until you put the real file in place.

     sitePath("images/products/floralSummerDress1.jpg")
       adds "../" in front when the page lives inside pages/.
       It reads data-root from the <body> tag of the page.

     renderPicture(path, altText, extraClass)
       returns the HTML for one picture, ready to put in a card.

     <div data-picture="homeBanner" data-alt="Inside the salon"></div>
       any element like this is filled with the picture of that name
       from siteImages.js as soon as the page is ready.
   ========================================================================== */

function sitePath(path) {
  var root = document.body.getAttribute("data-root") || "";
  return root + path;
}

function renderPicture(path, altText, extraClass) {
  var classes = "picture" + (extraClass ? " " + extraClass : "");

  // No path at all: show the empty box straight away.
  if (!path) {
    return '<span class="' + classes + ' isMissing" data-label="' + altText + '"></span>';
  }

  // onerror runs if the file cannot be found. It swaps the image for the box.
  return '' +
    '<span class="' + classes + '" data-label="' + altText + '">' +
      '<img src="' + sitePath(path) + '" alt="' + altText + '" onerror="markPictureMissing(this)">' +
    "</span>";
}

/* Called by the image itself when its file is missing. */
function markPictureMissing(image) {
  image.onerror = null;                              // never run twice
  image.parentNode.classList.add("isMissing");
}

/* Fills every element marked with data-picture. */
function fillPictureSlots() {
  document.querySelectorAll("[data-picture]").forEach(function (holder) {
    var name = holder.getAttribute("data-picture");
    var altText = holder.getAttribute("data-alt") || "";

    holder.innerHTML = renderPicture(siteImages[name], altText);
  });
}

document.addEventListener("DOMContentLoaded", fillPictureSlots);
