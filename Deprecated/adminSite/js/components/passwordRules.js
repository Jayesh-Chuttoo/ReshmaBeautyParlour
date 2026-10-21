/* ==========================================================================
   The password rules, written once for the whole admin site.

     checkPasswordRules("Reshma@1")
       returns the list of rules the password breaks; empty means it is fine.

     renderPasswordChecklist()
       a small list that ticks each rule off while the admin types.
     updatePasswordChecklist(listElement, password)
       re-ticks the list for the current password.

   The rules: at least 1 capital letter, 1 small letter, 1 number and
   1 symbol, and 8 characters at most.
   Backend will handle this later: the server must check the same rules,
   because anything checked in the browser can be skipped.
   ========================================================================== */

var passwordMaxLength = 8;

var passwordRules = [
  { key: "capital", label: "1 capital letter", test: function (text) { return /[A-Z]/.test(text); } },
  { key: "small", label: "1 small letter", test: function (text) { return /[a-z]/.test(text); } },
  { key: "number", label: "1 number", test: function (text) { return /[0-9]/.test(text); } },
  // A symbol is anything that is not a letter, a number or a space.
  { key: "symbol", label: "1 symbol, like @ # ! ?", test: function (text) { return /[^A-Za-z0-9\s]/.test(text); } },
  { key: "length", label: passwordMaxLength + " characters or fewer",
    test: function (text) { return text.length > 0 && text.length <= passwordMaxLength; } }
];

function checkPasswordRules(password) {
  return passwordRules.filter(function (rule) {
    return !rule.test(password);
  }).map(function (rule) {
    return rule.label;
  });
}

function renderPasswordChecklist() {
  return passwordRules.map(function (rule) {
    return '<li data-rule="' + rule.key + '">' + rule.label + "</li>";
  }).join("");
}

function updatePasswordChecklist(listElement, password) {
  passwordRules.forEach(function (rule) {
    var item = listElement.querySelector('[data-rule="' + rule.key + '"]');
    item.classList.toggle("isMet", rule.test(password));
  });
}
