/* ==========================================================================
   What is currently in the cart. The header reads it for the little number
   next to Cart, and the cart page turns each line into a card.

   productId  the product's id from products.js; the photo, name and
              category are read from there, so they are never written twice
   oldPrice   only filled in when the price changed since the item was
              added; the cart then shows a notice. Leave it out otherwise.

   Backend will handle this later: the real cart will live on the server.
   ========================================================================== */

var cartItems = [
  { productId: "floralSummerDress", size: "M", unitPrice: 1250, quantity: 1 },
  { productId: "printedSilkScarf", size: "One size", unitPrice: 550, oldPrice: 500, quantity: 2 }
];

/* Adds up the quantities so the header can show one number. */
function countCartItems() {
  var total = 0;
  cartItems.forEach(function (item) {
    total = total + item.quantity;
  });
  return total;
}
