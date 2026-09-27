/* ==========================================================================
   The product groups and the products. productCard.js turns each product
   into a card; product.html shows one product in full.

   id       a short name with no spaces, used in the address:
            product.html?id=floralSummerDress
   added    the bigger the number, the newer the product
   photos   the first one is used on the card. Paths are written from the
            top of the clientSite folder: save your photo with that name,
            or change the path to your file's name.
   sizes    "left" is how many are in stock. 0 greys the size out.
            When every size is at 0 the product shows as sold out.

   Backend will handle this later: both lists will come from the server.
   ========================================================================== */

var productCategories = [
  { key: "clothing", label: "Clothing", tagColour: "tagBlush" },
  { key: "watches", label: "Watches", tagColour: "tagLilac" },
  { key: "other", label: "Other", tagColour: "tag" }
];

var productsList = [
  {
    id: "floralSummerDress",
    name: "Floral summer dress",
    category: "clothing",
    price: 1250,
    added: 6,
    note: "Sizes S to XL",
    description: "Light cotton dress with a small floral print, short sleeves and a tie at the waist. Machine washable at 30 degrees.",
    photos: [
      { path: "images/products/floralSummerDress1.jpg", label: "Front view" },
      { path: "images/products/floralSummerDress2.jpg", label: "Side view" },
      { path: "images/products/floralSummerDress3.jpg", label: "Fabric close-up" }
    ],
    sizes: [
      { label: "S", left: 8 },
      { label: "M", left: 5 },
      { label: "L", left: 2 },
      { label: "XL", left: 0 }
    ]
  },
  {
    id: "roseGoldLadiesWatch",
    name: "Rose gold ladies watch",
    category: "watches",
    price: 2400,
    added: 5,
    note: "One size",
    description: "Slim rose gold case with a mesh strap and a quartz movement.",
    photos: [
      { path: "images/products/roseGoldLadiesWatch1.jpg", label: "Front view" },
      { path: "images/products/roseGoldLadiesWatch2.jpg", label: "On the wrist" }
    ],
    sizes: [{ label: "One size", left: 5 }]
  },
  {
    id: "embroideredKurta",
    name: "Embroidered kurta",
    category: "clothing",
    price: 1600,
    added: 4,
    note: "Only 2 left in M",
    description: "Cotton kurta with embroidery at the neck and cuffs.",
    photos: [
      { path: "images/products/embroideredKurta1.jpg", label: "Front view" },
      { path: "images/products/embroideredKurta2.jpg", label: "Embroidery close-up" }
    ],
    sizes: [
      { label: "S", left: 3 },
      { label: "M", left: 2 },
      { label: "L", left: 4 }
    ]
  },
  {
    id: "classicGoldWatch",
    name: "Classic gold watch",
    category: "watches",
    price: 3200,
    added: 3,
    note: "One size",
    description: "Gold-tone case, white dial and a leather strap.",
    photos: [
      { path: "images/products/classicGoldWatch1.jpg", label: "Front view" }
    ],
    sizes: [{ label: "One size", left: 4 }]
  },
  {
    id: "cottonWrapTop",
    name: "Cotton wrap top",
    category: "clothing",
    price: 750,
    added: 2,
    note: "Back in stock soon",
    description: "Soft cotton top that wraps and ties at the side.",
    photos: [
      { path: "images/products/cottonWrapTop1.jpg", label: "Front view" }
    ],
    sizes: [
      { label: "S", left: 0 },
      { label: "M", left: 0 },
      { label: "L", left: 0 }
    ]
  },
  {
    id: "printedSilkScarf",
    name: "Printed silk scarf",
    category: "other",
    price: 550,
    added: 1,
    note: "One size",
    description: "Square silk scarf with a printed border.",
    photos: [
      { path: "images/products/printedSilkScarf1.jpg", label: "Folded" },
      { path: "images/products/printedSilkScarf2.jpg", label: "Open" }
    ],
    sizes: [{ label: "One size", left: 11 }]
  }
];

/* Small helpers so no page has to repeat these tests. */
function findProduct(id) {
  return productsList.find(function (product) { return product.id === id; });
}

function findProductCategory(key) {
  return productCategories.find(function (category) { return category.key === key; });
}

function isSoldOut(product) {
  return product.sizes.every(function (size) { return size.left === 0; });
}
