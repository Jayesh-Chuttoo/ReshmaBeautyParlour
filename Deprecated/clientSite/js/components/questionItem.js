/* ==========================================================================
   Turns one entry from faqList into an open-and-close question box.
   details and summary open and close with no JavaScript at all.

   Used by about.js:
     holder.innerHTML = faqList.map(renderQuestionItem).join("");
   ========================================================================== */

function renderQuestionItem(entry) {
  return '' +
    '<details class="question">' +
      "<summary>" + entry.question + "</summary>" +
      '<p class="small">' + entry.answer + "</p>" +
    "</details>";
}
