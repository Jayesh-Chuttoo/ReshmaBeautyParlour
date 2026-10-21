/* ==========================================================================
   1. Write the address and the contact list from businessInfo.
   2. Build the questions from faqList.js.
   3. Keep only one question open at a time.
   The opening hours are built by the openingHours component on its own.
   ========================================================================== */

document.addEventListener("DOMContentLoaded", function () {

  // ---- 1. Contact details ----
  document.getElementById("salonAddress").innerHTML =
    businessInfo.address + "<br>" + businessInfo.addressDetail;

  document.getElementById("contactList").innerHTML = renderContactLinks(true);

  // ---- 2. The questions ----
  document.getElementById("questionList").innerHTML =
    faqList.map(renderQuestionItem).join("");

  // ---- 3. One question open at a time ----
  var questions = document.querySelectorAll(".question");

  questions.forEach(function (question) {
    // toggle fires whenever a details element opens or closes.
    question.addEventListener("toggle", function () {
      if (!question.open) { return; }

      questions.forEach(function (other) {
        if (other !== question) { other.open = false; }
      });
    });
  });
});
