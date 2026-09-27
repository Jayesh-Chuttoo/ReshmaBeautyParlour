/* ==========================================================================
   One place for every detail about the salon. The header, the footer, the
   about page, the payment screen and the WhatsApp links all read from here.
   Backend will handle this later: these values will come from a settings
   table the admin can edit.
   ========================================================================== */

var businessInfo = {
  name: "Reshma Beauty Parlour",
  tagline: "Salon & boutique",
  phone: "+230 583 22233",
  whatsappNumber: "23058322233",      //  used in wa.me links
  email: "hello@reshmabeauty.mu",
  address: "Petit Raffray, Mauritius",
  addressDetail: "Opposite the market, first floor",
  openingSummary: "Tuesday to Sunday, 9:00 to 18:00",

  // One line per row of the opening hours table on the about page.
  // "days" lists which days the row covers: 0 is Sunday, 1 is Monday...
  // Leave "hours" empty for a closed day.
  openingHours: [
    { label: "Monday", days: [1], hours: "" },
    { label: "Tuesday to Friday", days: [2, 3, 4, 5], hours: "9:00 - 18:00" },
    { label: "Saturday", days: [6], hours: "8:30 - 18:00" },
    { label: "Sunday", days: [0], hours: "9:00 - 13:00" }
  ],

  // Shown under Contact on the about page and in the footer.
  // Put the real page addresses here; remove a line to hide it.
  socialLinks: [
    { label: "Instagram", address: "https://www.instagram.com" },
    { label: "Facebook", address: "https://www.facebook.com" }
  ],

  juiceNumber: "58322233",
  juiceAccountName: "Reshma Beauty Parlour Ltd",

  deliveryFee: 150,        // rupees, added once per order
  maxPerCartLine: 10,      // most of one item someone can order
  lowStockLevel: 3,        // at or below this, stock counts as low
  orderExpiryHours: 48
};

/* Builds a WhatsApp link with a message already written in it.
   encodeURIComponent makes the text safe to put inside a web address. */
function whatsappLink(message) {
  return "https://wa.me/" + businessInfo.whatsappNumber +
         (message ? "?text=" + encodeURIComponent(message) : "");
}

/* Turns 2350 into "Rs 2,350" so every price on the site looks the same. */
function formatPrice(amount) {
  return "Rs " + amount.toLocaleString("en-US");
}
