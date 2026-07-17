(function () {
  'use strict';
  const form = document.querySelector('[data-medsop-form]');
  if (!form) return;
  const list = form.querySelector('[data-step-list]');
  const hint = form.querySelector('[data-step-hint]');

  function refresh() {
    const steps = Array.from(list.querySelectorAll('[data-step]'));
    steps.forEach(function (step, index) {
      step.querySelector('[data-step-number]').textContent = index + 1;
      step.querySelector('[data-step-label]').textContent = index + 1;
      step.querySelectorAll('input, textarea').forEach(function (field) {
        field.name = field.name.replace(/steps\[\d+\]/, 'steps[' + index + ']');
      });
    });
    hint.textContent = steps.length ? steps.length + ' ขั้นตอน' : 'กรุณาเพิ่มอย่างน้อย 1 ขั้นตอน';
    hint.classList.toggle('is-warn', steps.length === 0);
    form.querySelector('[data-save-document]').disabled = steps.length === 0;
  }

  form.querySelector('[data-add-step]').addEventListener('click', function () {
    const source = list.querySelector('[data-step]');
    const step = source.cloneNode(true);
    step.querySelectorAll('input, textarea').forEach(function (field) { field.value = ''; });
    list.appendChild(step);
    refresh();
    step.querySelector('input').focus();
  });
  list.addEventListener('click', function (event) {
    const button = event.target.closest('[data-remove-step]');
    if (!button) return;
    if (list.querySelectorAll('[data-step]').length === 1) {
      hint.textContent = 'เอกสารต้องมีอย่างน้อย 1 ขั้นตอน';
      hint.classList.add('is-warn');
      button.closest('[data-step]').querySelector('input').focus();
      return;
    }
    button.closest('[data-step]').remove();
    refresh();
  });
  document.addEventListener('keydown', function (event) {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 's') {
      event.preventDefault();
      if (form.reportValidity()) form.requestSubmit();
    }
  });
  form.addEventListener('submit', function () {
    const button = form.querySelector('[data-save-document]');
    button.disabled = true;
    button.textContent = 'กำลังบันทึก...';
  });
  refresh();
})();
