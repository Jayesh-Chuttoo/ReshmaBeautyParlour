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


// Check and toggle onsite visit visibility dynamically
function checkOnsiteAvailability() {
  const serviceNameInput = document.getElementById("modal-service-name").value;
  const peopleInput = document.getElementById("booking-people");
  const onsiteContainer = document.getElementById("onsite-container");
  const onsiteCheckbox = document.getElementById("onsite-visit");

  const peopleCount = parseInt(peopleInput.value) || 1;
  const isBridal = serviceNameInput.toLowerCase().includes("bridal makeup");

  // Show only if it's Bridal Makeup and people count is > 5
  if (isBridal && peopleCount > 5) {
    onsiteContainer.style.display = "block";
  } else {
    onsiteContainer.style.display = "none";
    if (onsiteCheckbox) onsiteCheckbox.checked = false;
  }
}

// Booking Modal Management
function openBookingModal(serviceName = "General Consultation", price = 0) {
  const modal = document.getElementById("booking-modal");
  const serviceInput = document.getElementById("modal-service-name");
  const peopleInput = document.getElementById("booking-people");
  
  if (serviceInput) {
    serviceInput.value = serviceName + (price > 0 ? ` (Rs ${price.toFixed(2)})` : "");
  }
  
  if (peopleInput) {
    peopleInput.value = 1; // Reset to 1 on open
  }
  
  checkOnsiteAvailability();
  
  if (modal) {
    modal.style.display = "flex";
  }
}

function closeBookingModal() {
  const modal = document.getElementById("booking-modal");
  if (modal) {
    modal.style.display = "none";
  }
}

function handleBookingSubmit(event) {
  event.preventDefault();
  const serviceName = document.getElementById("modal-service-name").value;
  const clientName = document.getElementById("client-name").value;
  const peopleCount = document.getElementById("booking-people").value;
  const onsiteChecked = document.getElementById("onsite-visit")?.checked || false;
  
  closeBookingModal();
  
  let notificationText = `Booking added for ${clientName} - ${serviceName} (${peopleCount} person${peopleCount > 1 ? 's' : ''})`;
  if (onsiteChecked) {
    notificationText += ` [Onsite Visit Requested]`;
  }
  
  if (typeof showNotification === "function") {
    showNotification(notificationText);
  } else {
    alert(notificationText);
  }
  
  // Reset form inputs
  document.getElementById("booking-form").reset();
}

// Close modal when clicking outside of modal-content box
window.addEventListener("click", function (event) {
  const modal = document.getElementById("booking-modal");
  if (event.target === modal) {
    closeBookingModal();
  }
});