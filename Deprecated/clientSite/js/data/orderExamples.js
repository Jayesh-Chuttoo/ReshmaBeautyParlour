/* ==========================================================================
   One example order and one example booking, so the payment and tracking
   pages have something to show before the backend exists.
   Change anything here and both pages follow.

   Backend will handle this later: the payment page gets the order that was
   just placed, and the tracking page gets whatever the code and phone
   number match. This file is then deleted.
   ========================================================================== */

var exampleOrder = {
  code: "ORD-7KQ2M9",
  status: "awaitingPayment",
  payBefore: "Friday 25 September, 18:00",
  lines: [
    { label: "Floral summer dress (M)", amount: 1250 },
    { label: "Printed silk scarf x2", amount: 1100 }
  ],
  delivery: { label: "Delivery to Royal Road, Vacoas", amount: 150 },  // null for pickup
  history: [
    { title: "Order placed", when: "23 September, 14:10", done: true },
    { title: "You said you paid", when: "23 September, 14:32", done: true },
    { title: "Payment confirmed", when: "Waiting for us to check MCB Juice", done: false },
    { title: "Ready", when: "We message you when it is ready", done: false }
  ],
  trackingStatus: "pendingConfirmation",
  salonMessage: "We will deliver on Thursday afternoon if that suits you."
};

var exampleBooking = {
  code: "BK-4TM7QX",
  status: "confirmed",
  intro: "Your appointment is set. See you then.",
  details: [
    { label: "Service", value: "Gel set with nail art" },
    { label: "Where", value: "At the salon, Curepipe" },
    { label: "You asked for", value: "26 September, 15:00" },
    { label: "Confirmed for", value: "26 September, 15:30" }
  ],
  agreedPrice: 1200,
  salonMessage: "Come with bare nails if you can, it saves 15 minutes."
};

/* Adds up an order: every line, plus delivery when there is one. */
function orderTotal(order) {
  var total = order.delivery ? order.delivery.amount : 0;
  order.lines.forEach(function (line) { total = total + line.amount; });
  return total;
}
