// Toast Notification System
function showNotification(message) {
  let container = document.getElementById("notification-area");
  if (!container) {
    container = document.createElement("div");
    container.id = "notification-area";
    document.body.appendChild(container);
  }

  const toast = document.createElement("div");
  toast.className = "toast";
  toast.innerText = message;

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = "0";
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

// Robust Scroll to Top Function
function scrollToTop() {
  window.scrollTo({
    top: 0,
    behavior: "smooth",
  });

  // Fallback for older browsers or strict overflow containers
  document.documentElement.scrollTo({
    top: 0,
    behavior: "smooth",
  });
  document.body.scrollTop = 0;
  document.documentElement.scrollTop = 0;
}

// Bind event listeners when the page loads
document.addEventListener("DOMContentLoaded", function () {
  const backToTopBtn = document.getElementById("back-to-top");
  if (backToTopBtn) {
    backToTopBtn.addEventListener("click", scrollToTop);
  }
});

function filterProducts() {
  const searchInput = document
    .getElementById("product-search")
    .value.toLowerCase();
  const categorySelect = document
    .getElementById("category-select")
    .value.toLowerCase();
  const cards = document.querySelectorAll(".product-card");

  cards.forEach((card) => {
    const title = card.querySelector("h3").innerText.toLowerCase();
    const category = card.getAttribute("data-category").toLowerCase();

    const matchesSearch = title.includes(searchInput);
    const matchesCategory =
      categorySelect === "all" || category === categorySelect;

    if (matchesSearch && matchesCategory) {
      card.style.display = "block";
    } else {
      card.style.display = "none";
    }
  });
}