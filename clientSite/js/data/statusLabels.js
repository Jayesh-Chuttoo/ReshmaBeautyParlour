/* ==========================================================================
   What each order and booking status is called on screen, and which tag
   colour it uses. statusTag.js reads these. The admin site has the same
   list, so the client and the salon always see the same words.
   ========================================================================== */

var orderStatuses = {
  awaitingPayment: { label: "Awaiting payment", colour: "tagBlush" },
  pendingConfirmation: { label: "Pending confirmation", colour: "tagLilac" },
  confirmed: { label: "Payment confirmed", colour: "tag" },
  ready: { label: "Ready", colour: "tag" },
  completed: { label: "Completed", colour: "tagGrey" },
  cancelled: { label: "Cancelled", colour: "tagGrey" }
};

var bookingStatuses = {
  requested: { label: "Request received", colour: "tagBlush" },
  confirmed: { label: "Booking confirmed", colour: "tagLilac" },
  completed: { label: "Completed", colour: "tagGrey" },
  declined: { label: "Not available", colour: "tagGrey" },
  cancelled: { label: "Cancelled", colour: "tagGrey" }
};
