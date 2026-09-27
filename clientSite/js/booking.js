/* ==========================================================================
   1. Fill the service list, and preselect the one the person clicked.
   2. Allow dates between tomorrow and 60 days from now.
   3. Show the address box for home visits.
   4. Check the form, then show the booking code.
   ========================================================================== */

document.addEventListener("DOMContentLoaded", function () {

  var serviceSelect = document.getElementById("service");
  var firstDate = document.getElementById("firstDate");
  var secondDate = document.getElementById("secondDate");

  // ---- 1. The service choices, built from the same list as the cards ----
  var choices = '<option value="">Choose a service</option>';

  // The value of each choice is the service's id; the text is its name.
  servicesList.forEach(function (service) {
    choices = choices +
      '<option value="' + service.id + '">' + service.name + " - " + service.price + "</option>";
  });

  serviceSelect.innerHTML = choices;

  document.getElementById("openingSummary").textContent = businessInfo.openingSummary;

  // services.html links here as booking.html?service=classicManicure
  var parameters = new URLSearchParams(window.location.search);
  var wantedService = parameters.get("service");

  if (wantedService) {
    serviceSelect.value = wantedService;
  }

  updateNegotiableNotice();
  serviceSelect.addEventListener("change", updateNegotiableNotice);

  document.getElementById("cancelWhatsapp").href = whatsappLink("");

  // ---- 2. Date limits ----
  firstDate.min = dateAsText(1);      // tomorrow
  firstDate.max = dateAsText(60);     // 60 days from now
  secondDate.min = dateAsText(1);
  secondDate.max = dateAsText(60);

  // ---- 3. At the salon or at my place ----
  document.querySelectorAll('input[name="place"]').forEach(function (radio) {
    radio.addEventListener("change", function () {
      var atHome = (radio.value === "offsite" && radio.checked);
      document.getElementById("placeAddressField").hidden = !atHome;
    });
  });

  // ---- 4. Sending the request ----
  document.getElementById("bookingForm").addEventListener("submit", function (event) {
    event.preventDefault();

    var nameField = document.getElementById("bookingName");
    var mobileField = document.getElementById("bookingMobile");
    var emailField = document.getElementById("bookingEmail");
    var timeField = document.getElementById("firstTime");
    var addressField = document.getElementById("placeAddress");

    clearFieldErrors([serviceSelect, firstDate, timeField, nameField,
                      mobileField, emailField, addressField]);

    var problems = 0;

    if (serviceSelect.value === "") {
      markFieldError(serviceSelect);
      problems++;
    }

    if (firstDate.value === "" || firstDate.value < firstDate.min || firstDate.value > firstDate.max) {
      markFieldError(firstDate);
      problems++;
    }

    if (timeField.value === "") {
      markFieldError(timeField);
      problems++;
    }

    if (nameField.value.trim() === "") {
      markFieldError(nameField);
      problems++;
    }

    if (!isMobileNumber(mobileField.value)) {
      markFieldError(mobileField);
      problems++;
    }

    if (emailField.value.trim() !== "" && !looksLikeEmail(emailField.value)) {
      markFieldError(emailField);
      problems++;
    }

    var atHome = document.querySelector('input[name="place"]:checked').value === "offsite";
    if (atHome && addressField.value.trim() === "") {
      markFieldError(addressField);
      problems++;
    }

    if (problems > 0) {
      reportProblems(problems);
      return;
    }

    // Backend will handle this later: the server creates the booking and
    // returns the real code.
    var code = makeCode("BK");
    document.getElementById("bookingCode").textContent = code;

    var serviceName = findService(serviceSelect.value).name;

    document.getElementById("bookingRecap").innerHTML =
      recapRow("Service", serviceName) +
      recapRow("Where", atHome ? "At your place" : "At the salon") +
      recapRow("Preferred", firstDate.value + " at " + timeField.value) +
      recapRow("Name", nameField.value.trim());

    document.getElementById("bookingWhatsapp").href = whatsappLink(
      "Hello, I sent booking request " + code + " for " + serviceName + ".");

    openModal("bookingSentModal");
  });
});

/* Shows the "price agreed with you" notice for quoted services. */
function updateNegotiableNotice() {
  var service = findService(document.getElementById("service").value);
  var isNegotiable = service ? service.priceIsAgreed : false;

  document.getElementById("negotiableNotice").hidden = !isNegotiable;
}

/* A date a number of days from today, written as 2026-09-24,
   which is the format a date input expects. */
function dateAsText(daysFromToday) {
  var date = new Date();
  date.setDate(date.getDate() + daysFromToday);

  var year = date.getFullYear();
  var month = String(date.getMonth() + 1).padStart(2, "0");   // months start at 0
  var day = String(date.getDate()).padStart(2, "0");

  return year + "-" + month + "-" + day;
}

function recapRow(label, value) {
  return "<div><span class='muted'>" + label + "</span><span>" + value + "</span></div>";
}
