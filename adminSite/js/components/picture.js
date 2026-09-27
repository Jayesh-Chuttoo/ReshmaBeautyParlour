/* ==========================================================================
   Shows a picture that lives on the client website, with a soft coloured
   box in its place while the file does not exist yet.
   Same idea as clientSite/js/components/picture.js, but the path is read
   from the client site's address in adminSettings.js.

     renderPicture("images/banners/homeBanner.jpg", "Home page banner")
     renderPicture(blobAddress, "New picture")   a file just chosen by the admin
   ========================================================================== */

/* Paths inside clientSite get the client site's address in front.
   A full address (https:, blob:, data:) is left as it is. */
function pictureAddress(path) {
  if (/^(https?:|blob:|data:)/.test(path)) { return path; }
  return adminSettings.clientSiteAddress + path;
}

function renderPicture(path, altText, extraClass) {
  var classes = "picture" + (extraClass ? " " + extraClass : "");

  if (!path) {
    return '<span class="' + classes + ' isMissing" data-label="' + altText + '"></span>';
  }

  return '' +
    '<span class="' + classes + '" data-label="' + altText + '">' +
      '<img src="' + pictureAddress(path) + '" alt="' + altText + '" onerror="markPictureMissing(this)">' +
    "</span>";
}

function markPictureMissing(image) {
  image.onerror = null;
  image.parentNode.classList.add("isMissing");
}
