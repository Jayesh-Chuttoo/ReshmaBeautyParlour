/* ==========================================================================
   1. Change the sign-in email (needs the current password).
   2. Change the password: current one, new one twice, and the rules from
      passwordRules.js ticked off while typing.

   Backend will handle this later: both forms send their values to the
   server, which checks the current password, applies the same rules,
   stores the new password hashed, and signs other devices out.
   Until then nothing is kept after the page closes: to really change the
   details for now, edit js/data/adminAccount.js.
   ========================================================================== */

document.addEventListener("DOMContentLoaded", function () {

  document.getElementById("currentEmail").textContent = adminAccount.email;

  // ---- 1. Email ----
  document.getElementById("emailForm").addEventListener("submit", function (event) {
    event.preventDefault();

    var emailField = document.getElementById("newEmail");
    var passwordField = document.getElementById("emailPassword");

    clearFieldErrors([emailField, passwordField]);

    var problems = 0;

    if (!looksLikeEmail(emailField.value)) {
      markFieldError(emailField);
      problems++;
    }

    if (passwordField.value !== adminAccount.password) {
      markFieldError(passwordField);
      problems++;
    }

    if (problems > 0) { return; }

    var newEmail = emailField.value.trim();

    // For this visit only, so the header and this page show the new email.
    adminAccount.email = newEmail;
    document.getElementById("currentEmail").textContent = newEmail;
    document.querySelector(".adminUser").innerHTML =
      "Signed in as " + newEmail + ' &middot; <a href="../index.html">Sign out</a>';

    this.reset();
    showMessage("Email changed", "From now on, sign in with " + newEmail + ".");
  });

  // ---- 2. Password ----
  var newPasswordField = document.getElementById("newPassword");
  var checklist = document.getElementById("passwordChecklist");

  checklist.innerHTML = renderPasswordChecklist();

  // "input" fires on every key, so the list is ticked as the admin types.
  newPasswordField.addEventListener("input", function () {
    updatePasswordChecklist(checklist, newPasswordField.value);

    // Once every rule is met, take away the red from an earlier try.
    if (checkPasswordRules(newPasswordField.value).length === 0) {
      clearFieldError(newPasswordField);
    }
  });

  document.getElementById("showPasswords").addEventListener("change", function () {
    var type = this.checked ? "text" : "password";
    document.querySelectorAll("#passwordForm input[maxlength]").forEach(function (field) {
      field.type = type;
    });
  });

  document.getElementById("passwordForm").addEventListener("submit", function (event) {
    event.preventDefault();

    var currentField = document.getElementById("currentPassword");
    var repeatField = document.getElementById("repeatPassword");

    clearFieldErrors([currentField, newPasswordField, repeatField]);

    var problems = 0;

    if (currentField.value !== adminAccount.password) {
      markFieldError(currentField);
      problems++;
    }

    if (checkPasswordRules(newPasswordField.value).length > 0) {
      markFieldError(newPasswordField);
      problems++;
    }

    if (repeatField.value !== newPasswordField.value) {
      markFieldError(repeatField);
      problems++;
    }

    if (problems > 0) { return; }

    // For this visit only (see the note at the top).
    adminAccount.password = newPasswordField.value;

    this.reset();
    updatePasswordChecklist(checklist, "");
    showMessage("Password changed", "Use the new password the next time you sign in.");
  });
});
