/**
 * Reshma Beauty Parlour - Admin Dashboard Application JS
 */

document.addEventListener("DOMContentLoaded", () => {
  initTabs();
  initImagePreview();
  initTableSearch();
  initAutoDismissAlerts();
});

/**
 * Tab Switching Component
 */
function switchTab(tabId) {
  // Hide all tab contents
  document
    .querySelectorAll(".tab-content")
    .forEach((el) => el.classList.add("hidden"));

  // Reset all tab buttons
  document.querySelectorAll(".tab-btn").forEach((btn) => {
    btn.classList.remove(
      "text-indigo-600",
      "border-b-2",
      "border-indigo-600",
      "font-semibold",
    );
    btn.classList.add("text-gray-500", "font-medium");
  });

  // Show active tab
  const targetTab = document.getElementById("tab-" + tabId);
  if (targetTab) {
    targetTab.classList.remove("hidden");
  }

  // Highlight active button
  const activeBtn = document.getElementById("btn-" + tabId);
  if (activeBtn) {
    activeBtn.classList.remove("text-gray-500", "font-medium");
    activeBtn.classList.add(
      "text-indigo-600",
      "border-b-2",
      "border-indigo-600",
      "font-semibold",
    );
  }

  // Save active tab in localStorage for persistence
  localStorage.setItem("active_admin_tab", tabId);
}

// Restore last active tab on page load if saved
window.addEventListener("load", () => {
  const savedTab = localStorage.getItem("active_admin_tab");
  if (savedTab && document.getElementById("tab-" + savedTab)) {
    switchTab(savedTab);
  }
});

/**
 * Modal Pop-Up Controls
 */
function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove("hidden");
    modal.classList.add("flex");
    document.body.style.overflow = "hidden"; // Prevent background scrolling
  }
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
    document.body.style.overflow = "auto";
  }
}

// Close modal when clicking outside content area
window.addEventListener("click", (e) => {
  if (e.target.classList.contains("fixed") && e.target.id) {
    closeModal(e.target.id);
  }
});

/**
 * Image Upload Live Preview Component
 */
function initImagePreview() {
  const imageInput = document.querySelector('input[name="product_image"]');
  if (!imageInput) return;

  imageInput.addEventListener("change", function (event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function (e) {
      // Check if preview container exists, if not create one dynamically
      let previewContainer = document.getElementById("image-preview-container");
      if (!previewContainer) {
        previewContainer = document.createElement("div");
        previewContainer.id = "image-preview-container";
        previewContainer.className = "mt-3 flex items-center space-x-3";
        imageInput.parentNode.appendChild(previewContainer);
      }

      previewContainer.innerHTML = `
                <img src="${e.target.result}" alt="Preview" class="w-16 h-16 object-cover rounded-lg border shadow-sm">
                <span class="text-xs text-gray-500">Selected: ${file.name}</span>
            `;
    };
    reader.readAsDataURL(file);
  });
}

/**
 * Real-Time Table Search Filtering
 */
function initTableSearch() {
  const searchInput = document.getElementById("tableSearch");
  if (!searchInput) return;

  searchInput.addEventListener("keyup", function () {
    const query = this.value.toLowerCase();
    // Find current active tab content table rows
    const activeTab = document.querySelector(".tab-content:not(.hidden)");
    if (!activeTab) return;

    const rows = activeTab.querySelectorAll("tbody tr");
    rows.forEach((row) => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(query) ? "" : "none";
    });
  });
}

/**
 * Auto-Dismiss Flash Notification Toasts
 */
function initAutoDismissAlerts() {
  const alertBox = document.getElementById("flash-alert");
  if (alertBox) {
    setTimeout(() => {
      alertBox.style.transition = "opacity 0.5s ease";
      alertBox.style.opacity = "0";
      setTimeout(() => alertBox.remove(), 500);
    }, 4000);
  }
}
