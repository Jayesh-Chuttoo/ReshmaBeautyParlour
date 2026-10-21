/* ==========================================================================
   The form checking every page shares.

     markFieldError(field)   turn a field red and show its message
     clearFieldError(field)  put it back to normal
     isMobileNumber(value)   8 digits starting with 5, spaces ignored
     looksLikeEmail(value)   something @ something . something
     reportProblems(count)   one pop-up saying how many fields need fixing

   None of these touch styling directly: they add or remove the hasError
   class, and siteShell.css does the rest.
   ========================================================================== */

function markFieldError(field) {
  field.closest(".field").classList.add("hasError");
}

function clearFieldError(field) {
  field.closest(".field").classList.remove("hasError");
}

/* Clears several fields at once, before checking them again. */
function clearFieldErrors(fields) {
  fields.forEach(function (field) {
    if (field) { clearFieldError(field); }
  });
}

/* A Mauritian mobile number: 8 digits starting with 5.
   The spaces are removed first, so "5712 3456" is accepted. */
function isMobileNumber(value) {
  return /^5[0-9]{7}$/.test(value.replace(/\s/g, ""));
}

/* A simple email check. It is not perfect, and does not need to be:
   Backend will handle this later, and the server must check again. */
function looksLikeEmail(value) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim());
}

/* Tells the person how many fields still need attention. */
function reportProblems(count) {
  showMessage(
    "Check the form",
    count === 1
      ? "One field still needs attention. It is marked in red."
      : count + " fields still need attention. They are marked in red."
  );
}

/* Builds a code like ORD-7KQ2M9 or BK-4TM7QX.
   The letters 0, O, 1, I and L are left out so nobody reads a code wrongly
   over the phone. Backend will handle this later: real codes come from the
   server, because only the server can make sure they are unique. */
function makeCode(prefix) {
  var letters = "23456789ABCDEFGHJKMNPQRSTUVWXYZ";
  var code = prefix + "-";

  for (var i = 0; i < 6; i++) {
    code = code + letters.charAt(Math.floor(Math.random() * letters.length));
  }
  return code;
}
