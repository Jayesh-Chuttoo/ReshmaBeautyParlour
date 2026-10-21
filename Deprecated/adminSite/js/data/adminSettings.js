/* ==========================================================================
   Settings the whole admin site shares.

   clientSiteAddress  where the client website lives. The Pictures page
                      uses it to show the pictures that are on the website
                      now, and the menu uses it for "View website".
                      While both folders sit side by side on your computer,
                      "../../clientSite/" works from inside adminSite/pages/.
                      Once the client site is online, put its real address,
                      ending with a slash, e.g. "https://www.reshmabeauty.mu/"

   Backend will handle this later: the settings will come from the server.
   ========================================================================== */

var adminSettings = {
  businessName: "Reshma Beauty Parlour",
  clientSiteAddress: "../../clientSite/",
  lowStockLevel: 3,                 // at or below this, a stock row turns pink
  maxPictureSizeMb: 2,              // bigger picture files are refused
  pictureTypes: ["image/jpeg", "image/png", "image/webp", "image/svg+xml"]
};

/* Turns 2350 into "Rs 2,350" so every price looks the same. */
function formatPrice(amount) {
  return "Rs " + amount.toLocaleString("en-US");
}
