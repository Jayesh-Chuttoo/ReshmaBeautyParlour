/* ==========================================================================
   Every picture the client website shows, so the admin can replace any of
   them from the Pictures page. imageSlotCard.js turns each one into a card.

   group   which filter it belongs to: site, services or products
   label   what the admin sees
   where   where it appears on the website
   size    the best size for the file, in pixels (width x height)
   path    where the file lives inside clientSite. It must match the path
           in the client site's data files (siteImages.js, services.js,
           products.js), otherwise the preview shows the wrong picture.

   Backend will handle this later: the server sends this list and saves the
   new file when the admin presses Save, and the client site reads the new
   path from the server. Until then, the two sites each keep their own list.
   ========================================================================== */

var imageGroups = [
  { key: "site", label: "Logo and banners" },
  { key: "services", label: "Services" },
  { key: "products", label: "Products" }
];

var imageSlots = [
  // ---- Logo, banners and page pictures (siteImages.js and services.js) ----
  { key: "logo", group: "site", label: "Logo", where: "Header of every page, and the browser tab",
    size: "400 x 400", path: "images/logoReshma.svg" },
  { key: "homeBanner", group: "site", label: "Home page banner", where: "Top of the home page",
    size: "1500 x 1200", path: "images/banners/homeBanner.jpg" },
  { key: "aboutSalon", group: "site", label: "Inside the salon", where: "Top of the about page",
    size: "1200 x 900", path: "images/about/aboutSalon.jpg" },
  { key: "hairServices", group: "site", label: "Hair services card", where: "Home page, What we do",
    size: "900 x 600", path: "images/categories/hairServices.jpg" },
  { key: "nailServices", group: "site", label: "Nail services card", where: "Home page, What we do",
    size: "900 x 600", path: "images/categories/nailServices.jpg" },
  { key: "otherServices", group: "site", label: "Other services card", where: "Home page, What we do",
    size: "900 x 600", path: "images/categories/otherServices.jpg" },

  // ---- One per service (services.js) ----
  { key: "cutAndBlowDry", group: "services", label: "Cut and blow-dry", where: "Services page",
    size: "900 x 600", path: "images/services/cutAndBlowDry.jpg" },
  { key: "colourAndHighlights", group: "services", label: "Colour and highlights", where: "Services page",
    size: "900 x 600", path: "images/services/colourAndHighlights.jpg" },
  { key: "classicManicure", group: "services", label: "Classic manicure", where: "Services page",
    size: "900 x 600", path: "images/services/classicManicure.jpg" },
  { key: "gelSetWithNailArt", group: "services", label: "Gel set with nail art", where: "Services page",
    size: "900 x 600", path: "images/services/gelSetWithNailArt.jpg" },
  { key: "threadingAndMiniFacial", group: "services", label: "Threading and mini facial", where: "Services page",
    size: "900 x 600", path: "images/services/threadingAndMiniFacial.jpg" },
  { key: "bridalAndEventMakeUp", group: "services", label: "Bridal and event make-up", where: "Services page",
    size: "900 x 600", path: "images/services/bridalAndEventMakeUp.jpg" },

  // ---- Product photos (products.js). The first photo of each product is
  //      the one on its card in the shop. ----
  { key: "floralSummerDress1", group: "products", label: "Floral summer dress, front view", where: "Shop card and product page",
    size: "1000 x 1250", path: "images/products/floralSummerDress1.jpg" },
  { key: "floralSummerDress2", group: "products", label: "Floral summer dress, side view", where: "Product page",
    size: "1000 x 1250", path: "images/products/floralSummerDress2.jpg" },
  { key: "floralSummerDress3", group: "products", label: "Floral summer dress, fabric close-up", where: "Product page",
    size: "1000 x 1250", path: "images/products/floralSummerDress3.jpg" },
  { key: "roseGoldLadiesWatch1", group: "products", label: "Rose gold ladies watch, front view", where: "Shop card and product page",
    size: "1000 x 1250", path: "images/products/roseGoldLadiesWatch1.jpg" },
  { key: "roseGoldLadiesWatch2", group: "products", label: "Rose gold ladies watch, on the wrist", where: "Product page",
    size: "1000 x 1250", path: "images/products/roseGoldLadiesWatch2.jpg" },
  { key: "embroideredKurta1", group: "products", label: "Embroidered kurta, front view", where: "Shop card and product page",
    size: "1000 x 1250", path: "images/products/embroideredKurta1.jpg" },
  { key: "embroideredKurta2", group: "products", label: "Embroidered kurta, embroidery close-up", where: "Product page",
    size: "1000 x 1250", path: "images/products/embroideredKurta2.jpg" },
  { key: "classicGoldWatch1", group: "products", label: "Classic gold watch, front view", where: "Shop card and product page",
    size: "1000 x 1250", path: "images/products/classicGoldWatch1.jpg" },
  { key: "cottonWrapTop1", group: "products", label: "Cotton wrap top, front view", where: "Shop card and product page",
    size: "1000 x 1250", path: "images/products/cottonWrapTop1.jpg" },
  { key: "printedSilkScarf1", group: "products", label: "Printed silk scarf, folded", where: "Shop card and product page",
    size: "1000 x 1250", path: "images/products/printedSilkScarf1.jpg" },
  { key: "printedSilkScarf2", group: "products", label: "Printed silk scarf, open", where: "Product page",
    size: "1000 x 1250", path: "images/products/printedSilkScarf2.jpg" }
];

/* Finds a group's label from its key, for the little tag on each card. */
function imageGroupLabel(key) {
  return imageGroups.find(function (group) { return group.key === key; }).label;
}
