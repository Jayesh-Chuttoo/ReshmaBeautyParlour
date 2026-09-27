/* ==========================================================================
   Example rows for the three admin screens. stockRow.js, orderRow.js and
   bookingRow.js turn each entry into a table row.
   Backend will handle this later: all three lists will come from the server.
   ========================================================================== */

var stockList = [
  { name: "Floral summer dress", category: "clothing", categoryLabel: "Clothing", size: "S", price: 1250, stock: 8 },
  { name: "Floral summer dress", category: "clothing", categoryLabel: "Clothing", size: "L", price: 1250, stock: 2 },
  { name: "Embroidered kurta", category: "clothing", categoryLabel: "Clothing", size: "M", price: 1600, stock: 2 },
  { name: "Cotton wrap top", category: "clothing", categoryLabel: "Clothing", size: "M", price: 750, stock: 0 },
  { name: "Rose gold ladies watch", category: "watches", categoryLabel: "Watches", size: "One size", price: 2400, stock: 5 },
  { name: "Printed silk scarf", category: "other", categoryLabel: "Other", size: "One size", price: 550, stock: 11 }
];

var ordersList = [
  {
    code: "ORD-7KQ2M9", date: "23 Sep", client: "Anjali R.", phone: "57123456",
    total: "Rs 2,500", status: "pendingConfirmation",
    items: "Floral summer dress (M), Printed silk scarf x2",
    fulfilment: "Delivery to Royal Road, Vacoas"
  },
  {
    code: "ORD-4XB8PT", date: "23 Sep", client: "Yash M.", phone: "59887766",
    total: "Rs 3,200", status: "pendingConfirmation",
    items: "Classic gold watch",
    fulfilment: "Pickup at the salon"
  },
  {
    code: "ORD-9NPD3C", date: "22 Sep", client: "Sarah L.", phone: "57990011",
    total: "Rs 1,250", status: "awaitingPayment",
    items: "Floral summer dress (S)",
    fulfilment: "Pickup at the salon"
  },
  {
    code: "ORD-2FHK6W", date: "21 Sep", client: "Priya D.", phone: "54221100",
    total: "Rs 1,700", status: "ready",
    items: "Embroidered kurta (M)",
    fulfilment: "Pickup at the salon"
  },
  {
    code: "ORD-8JRT5Q", date: "19 Sep", client: "Karan S.", phone: "57001122",
    total: "Rs 550", status: "completed",
    items: "Printed silk scarf",
    fulfilment: "Pickup at the salon"
  }
];

var bookingsList = [
  {
    code: "BK-4TM7QX", client: "Nadia B.", phone: "57334455",
    service: "Gel set with nail art", asked: "26 Sep, 15:00",
    place: "At the salon", status: "requested", negotiable: false,
    notes: "Would like short almond shape, pale pink."
  },
  {
    code: "BK-9WQ2HD", client: "Leela P.", phone: "59112233",
    service: "Bridal and event make-up", asked: "12 Oct, 08:00",
    place: "At her place, Floreal", status: "requested", negotiable: true,
    notes: "Wedding at 11:00, three people including the bride."
  },
  {
    code: "BK-6CXR8K", client: "Amina J.", phone: "54667788",
    service: "Cut and blow-dry", asked: "24 Sep, 10:00",
    place: "At the salon", status: "confirmed", negotiable: false,
    notes: "Regular client, shoulder length."
  },
  {
    code: "BK-3DLV5N", client: "Fatima K.", phone: "57889900",
    service: "Classic manicure", asked: "20 Sep, 11:00",
    place: "At the salon", status: "completed", negotiable: false,
    notes: ""
  }
];

/* What each status is called on screen, and which tag colour it uses.
   statusTag.js reads these. The client site has the same list
   (clientSite/js/data/statusLabels.js), so both sides use the same words. */
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
