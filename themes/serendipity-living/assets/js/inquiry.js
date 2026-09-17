(function () {
  'use strict';
  var section = document.querySelector('[data-villa-inquiry]');
  if (!section) return;
  var form = section.querySelector('form');
  var modes = form.querySelectorAll('input[name="request_type"]');
  var nameInput = form.elements.name;
  var message = form.elements.message;

  function setMode(mode) {
    var layouts = mode === 'layouts';
    modes.forEach(function (input) { if (input.type === 'radio') input.checked = input.value === mode; });
    section.querySelector('[data-inquiry-title]').textContent = layouts ? 'Picture every possibility.' : 'Let’s take a closer look.';
    section.querySelector('[data-inquiry-description]').textContent = layouts
      ? 'Leave your email to download the villa layouts. Add a question if there’s anything else you’d like to know.'
      : 'Ask us a question or arrange a visit. Our team will help you explore the possibilities.';
    section.querySelector('[data-layout-detail]').hidden = !layouts;
    section.querySelector('[data-name-optional]').hidden = !layouts;
    section.querySelector('[data-name-required]').hidden = layouts;
    section.querySelector('[data-message-optional]').hidden = !layouts;
    section.querySelector('[data-message-required]').hidden = layouts;
    nameInput.required = !layouts;
    message.required = !layouts;
    if (layouts) {
      nameInput.removeAttribute('minlength');
      message.removeAttribute('minlength');
    } else {
      nameInput.setAttribute('minlength', '2');
      message.setAttribute('minlength', '10');
    }
    form.querySelector('[data-inquiry-submit]').innerHTML = layouts
      ? 'Download layouts <span aria-hidden="true">↓</span>'
      : 'Send inquiry <span aria-hidden="true">→</span>';
    form.querySelector('#inquiryResult').textContent = '';
  }

  modes.forEach(function (input) {
    input.addEventListener('change', function () { if (input.checked) setMode(input.value); });
  });
  document.querySelectorAll('[data-request-layouts]').forEach(function (link) {
    link.addEventListener('click', function (event) {
      event.preventDefault();
      setMode('layouts');
      section.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'start' });
      form.elements.email.focus({ preventScroll: true });
    });
  });
})();
