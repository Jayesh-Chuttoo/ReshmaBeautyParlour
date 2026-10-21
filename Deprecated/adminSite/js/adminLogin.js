/* ==========================================================================
   Checks both boxes are filled, compares them with adminAccount.js, then
   opens the stock screen.

   Backend will handle this later: the email and password are sent to the
   server, which checks them and starts the admin session. Nothing here is
   a real security check; it only opens the screens while we build them.
   ========================================================================== */

document.addEventListener("DOMContentLoaded", function () {

  // A reminder for whoever edits adminAccount.js: warn if the password
  // written there breaks the rules. Visible in the browser console (F12).
  var broken = checkPasswordRules(adminAccount.password);
  if (broken.length > 0) {
    console.warn("The password in adminAccount.js is missing: " + broken.join(", "));
  }

  var passwordField = document.getElementById("password");

  document.getElementById("showPassword").addEventListener("change", function () {
    passwordField.type = this.checked ? "text" : "password";
  });

  document.getElementById("loginForm").addEventListener("submit", function (event) {
    event.preventDefault();

    var emailField = document.getElementById("email");

    clearFieldErrors([emailField, passwordField]);

    var problems = 0;

    if (!looksLikeEmail(emailField.value)) {
      markFieldError(emailField);
      problems++;
    }

    if (passwordField.value === "") {
      markFieldError(passwordField);
      problems++;
    }

    if (problems > 0) { return; }

    // Emails are compared without capitals or spaces at the ends;
    // passwords are compared exactly.
    var emailMatches = emailField.value.trim().toLowerCase() === adminAccount.email.toLowerCase();
    var passwordMatches = passwordField.value === adminAccount.password;

    if (!emailMatches || !passwordMatches) {
      // One message for both cases, so nobody can find out which part was right.
      passwordField.value = "";
      showMessage("Sign in failed", "The email or the password is not right. Check both and try again.");
      return;
    }

    window.location.href = "pages/adminInventory.html";
  });
});
