/* ==========================================================================
   The service groups and the services. serviceCard.js turns each service
   into a card, categoryCard.js turns each group into a card on the home page.

   Pictures: each path is written from the top of the clientSite folder.
   Save your photo with that name, or change the path to your file's name.
   Backend will handle this later: both lists will come from the server.
   ========================================================================== */

/* The three groups. "tagColour" is the colour of the little label on every
   card of that group (tag, tagLilac, tagRose or tagBlush from siteShell.css). */
var serviceCategories = [
  {
    key: "hair",
    label: "Hair",
    tagColour: "tagLilac",
    title: "Cuts, colour and treatments",
    text: "Blow-dry, straightening, colour and bridal styling. From Rs 600.",
    buttonLabel: "See hair services",
    image: "images/categories/hairServices.jpg"
  },
  {
    key: "nails",
    label: "Nails",
    tagColour: "tagRose",
    title: "Manicure, pedicure, gel",
    text: "Classic and gel sets, nail art and refills. From Rs 450.",
    buttonLabel: "See nail services",
    image: "images/categories/nailServices.jpg"
  },
  {
    key: "other",
    label: "Other",
    tagColour: "tag",
    title: "Facials, threading, make-up",
    text: "Event make-up is priced after we talk about what you want.",
    buttonLabel: "See all other services",
    image: "images/categories/otherServices.jpg"
  }
];

/* Where a service can be done. Used by the filter on the services page. */
var servicePlaces = [
  { key: "onsite", label: "At the salon" },
  { key: "offsite", label: "At your place" }
];

/* "priceIsAgreed: true" means the price is agreed with the client instead of
   being fixed. The booking form shows a notice for these, and the admin must
   enter the agreed price before confirming. */
var servicesList = [
  {
    id: "cutAndBlowDry",
    name: "Cut and blow-dry",
    category: "hair",
    places: ["onsite", "offsite"],
    image: "images/services/cutAndBlowDry.jpg",
    description: "A wash, a cut and a finish. Bring a photo if you have one in mind.",
    duration: "About 60 minutes",
    price: "Rs 800",
    priceNote: "",
    priceIsAgreed: false
  },
  {
    id: "colourAndHighlights",
    name: "Colour and highlights",
    category: "hair",
    places: ["onsite"],
    image: "images/services/colourAndHighlights.jpg",
    description: "Full colour, roots or highlights. Final price depends on length and product.",
    duration: "About 2 hours",
    price: "From Rs 1,500",
    priceNote: "Price agreed with you before we start.",
    priceIsAgreed: true
  },
  {
    id: "classicManicure",
    name: "Classic manicure",
    category: "nails",
    places: ["onsite", "offsite"],
    image: "images/services/classicManicure.jpg",
    description: "Shape, cuticles, buff and polish in the colour you choose.",
    duration: "About 45 minutes",
    price: "Rs 450",
    priceNote: "",
    priceIsAgreed: false
  },
  {
    id: "gelSetWithNailArt",
    name: "Gel set with nail art",
    category: "nails",
    places: ["onsite"],
    image: "images/services/gelSetWithNailArt.jpg",
    description: "A full gel set. Tell us the design you want in the notes.",
    duration: "About 90 minutes",
    price: "Rs 1,200",
    priceNote: "",
    priceIsAgreed: false
  },
  {
    id: "threadingAndMiniFacial",
    name: "Threading and mini facial",
    category: "other",
    places: ["onsite", "offsite"],
    image: "images/services/threadingAndMiniFacial.jpg",
    description: "Eyebrow and upper lip threading with a short cleansing facial.",
    duration: "About 40 minutes",
    price: "Rs 600",
    priceNote: "",
    priceIsAgreed: false
  },
  {
    id: "bridalAndEventMakeUp",
    name: "Bridal and event make-up",
    category: "other",
    places: ["onsite", "offsite"],
    image: "images/services/bridalAndEventMakeUp.jpg",
    description: "Trial, full look and touch-ups. Every booking is quoted individually.",
    duration: "Half or full day",
    price: "Price on request",
    priceNote: "Describe your event in the notes and we will send a price.",
    priceIsAgreed: true
  }
];

/* Finds a service by its id. Returns undefined when nothing matches. */
function findService(id) {
  return servicesList.find(function (service) { return service.id === id; });
}

/* Finds a group by its key, so a card can read its label and colour. */
function findServiceCategory(key) {
  return serviceCategories.find(function (category) {
    return category.key === key;
  });
}
