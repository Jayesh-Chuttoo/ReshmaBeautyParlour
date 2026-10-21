/* ==========================================================================
   Shows one product from products.js. Which one is read from the address:
   product.html?id=floralSummerDress

   1. fill in the name, price, description and photos
   2. switch the big photo, and open it larger in a pop-up
   3. build the size buttons and remember the chosen size
   4. change the quantity
   5. add to cart, with a size required first
   ========================================================================== */

var thisProduct = null;
var chosenSize = "";
var chosenPhoto = 0;

document.addEventListener("DOMContentLoaded", function () {

  var productId = new URLSearchParams(window.location.search).get("id");
  thisProduct = findProduct(productId);

  // No product with that id: show the message instead of an empty page.
  if (!thisProduct) {
    document.getElementById("productLayout").hidden = true;
    document.getElementById("productMissing").hidden = false;
    return;
  }

  // ---- 1. Text ----
  var category = findProductCategory(thisProduct.category);

  document.title = thisProduct.name + " - " + businessInfo.name;
  document.getElementById("crumbCategory").textContent = category.label;
  document.getElementById("productTag").innerHTML =
    '<span class="tag ' + category.tagColour + '">' + category.label + "</span>";
  document.getElementById("productName").textContent = thisProduct.name;
  document.getElementById("productPrice").textContent = formatPrice(thisProduct.price);
  document.getElementById("productDescription").textContent = thisProduct.description;
  document.getElementById("quantityHint").textContent =
    "Maximum " + businessInfo.maxPerCartLine + " of the same item per order.";

  document.getElementById("productPoints").innerHTML =
    "<li>Pickup at the salon, or delivery for " + formatPrice(businessInfo.deliveryFee) + "</li>" +
    "<li>Paid with MCB Juice after you place the order</li>" +
    "<li>Exchange within 3 days, unworn, with the purchase code</li>";

  // ---- 2. Photos ----
  showPhoto(0);

  // One thumbnail per photo. With a single photo there is nothing to switch.
  var thumbRow = document.getElementById("thumbRow");
  if (thisProduct.photos.length > 1) {
    thumbRow.innerHTML = thisProduct.photos.map(function (photo, index) {
      return '<button class="thumb' + (index === 0 ? " isActive" : "") +
             '" type="button" data-photo="' + index + '" aria-label="' + photo.label + '">' +
               renderPicture(photo.path, photo.label) +
             "</button>";
    }).join("");
  }

  thumbRow.querySelectorAll(".thumb").forEach(function (thumb) {
    thumb.addEventListener("click", function () {
      thumbRow.querySelectorAll(".thumb").forEach(function (other) {
        other.classList.remove("isActive");
      });
      thumb.classList.add("isActive");
      showPhoto(Number(thumb.getAttribute("data-photo")));
    });
  });

  // ---- 3. Sizes ----
  var sizeRow = document.getElementById("sizeRow");

  sizeRow.innerHTML = thisProduct.sizes.map(function (size) {
    var note = "";
    if (size.left === 0) {
      note = '<span class="sizeNote">Unavailable</span>';
    } else if (size.left <= businessInfo.lowStockLevel) {
      note = '<span class="sizeNote">Only ' + size.left + " left</span>";
    }

    return '<button class="sizeButton" type="button" data-size="' + size.label + '"' +
           (size.left === 0 ? " disabled" : "") + ">" + size.label + note + "</button>";
  }).join("");

  // A product with one size only gets that size chosen for the client.
  var available = thisProduct.sizes.filter(function (size) { return size.left > 0; });

  sizeRow.querySelectorAll(".sizeButton").forEach(function (button) {
    button.addEventListener("click", function () {
      // Disabled buttons are ignored by the browser, so nothing extra is needed.
      sizeRow.querySelectorAll(".sizeButton").forEach(function (other) {
        other.classList.remove("isChosen");
      });
      button.classList.add("isChosen");

      chosenSize = button.getAttribute("data-size");
      document.getElementById("sizeError").classList.remove("isVisible");
    });
  });

  if (available.length === 1 && thisProduct.sizes.length === 1) {
    sizeRow.querySelector(".sizeButton").click();
  }

  // Nothing left in any size: the button cannot be used.
  if (isSoldOut(thisProduct)) {
    var addButton = document.getElementById("addToCartButton");
    addButton.disabled = true;
    addButton.textContent = "Sold out";
  }

  // ---- 4. Quantity ----
  var quantityInput = document.getElementById("quantityInput");
  quantityInput.max = businessInfo.maxPerCartLine;

  document.getElementById("plusButton").addEventListener("click", function () {
    var current = Number(quantityInput.value);
    if (current < businessInfo.maxPerCartLine) {
      quantityInput.value = current + 1;
    }
  });

  document.getElementById("minusButton").addEventListener("click", function () {
    var current = Number(quantityInput.value);
    if (current > 1) {
      quantityInput.value = current - 1;
    }
  });

  // ---- 5. Add to cart ----
  document.getElementById("addToCartButton").addEventListener("click", function () {
    if (chosenSize === "") {
      document.getElementById("sizeError").classList.add("isVisible");
      return;
    }

    var quantity = Number(quantityInput.value);

    // Backend will handle this later: save the cart line on the server.
    document.getElementById("addedSummary").textContent =
      quantity + " x " + thisProduct.name + " (size " + chosenSize + ") - " +
      formatPrice(quantity * thisProduct.price);

    openModal("addedModal");

    // Show the number beside Cart going up, so the page feels alive.
    var cartCount = document.getElementById("cartCount");
    cartCount.textContent = Number(cartCount.textContent) + quantity;
  });
});

/* Puts one photo in the big frame and connects the zoom pop-up to it. */
function showPhoto(index) {
  chosenPhoto = index;

  var photo = thisProduct.photos[index];
  var holder = document.getElementById("mainPhotoHolder");

  holder.innerHTML = photo
    ? renderPicture(photo.path, thisProduct.name + ", " + photo.label.toLowerCase(), "mainPhoto")
    : renderPicture("", thisProduct.name, "mainPhoto");

  holder.querySelector(".mainPhoto").addEventListener("click", function () {
    // No zoom while the photo file is missing.
    if (this.classList.contains("isMissing") || !photo) { return; }

    document.getElementById("photoModalPicture").innerHTML =
      renderPicture(photo.path, thisProduct.name + ", larger view");
    openModal("photoModal");
  });
}
