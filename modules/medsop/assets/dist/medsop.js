(function () {
  'use strict';

  const codePattern = document.querySelector('#code-pattern');
  if (codePattern) {
    const sopPrefix = document.querySelector('#sop-prefix');
    const preview = document.querySelector('[data-code-preview]');
    const refreshCodePreview = function () {
      preview.textContent = codePattern.value
        .replaceAll('{type}', (sopPrefix.value || 'SP').toUpperCase())
        .replaceAll('{org}', 'OPD')
        .replaceAll('{year}', String(new Date().getFullYear() + 543))
        .replaceAll('{sequence}', '0001');
    };
    [codePattern, sopPrefix].forEach(function (field) { field.addEventListener('input', refreshCodePreview); });
    refreshCodePreview();
  }

  const form = document.querySelector('[data-medsop-form]');
  if (!form) return;

  const list = form.querySelector('[data-step-list]');
  const hint = form.querySelector('[data-step-hint]');
  const saveButton = form.querySelector('[data-save-document]');
  const saveSpinner = form.querySelector('[data-save-spinner]');
  const saveLabel = form.querySelector('[data-save-label]');
  const documentTypeInputs = Array.from(form.querySelectorAll('input[name="Document[document_type]"]'));
  const documentCodeLabel = form.querySelector('[data-document-code-label]');
  const documentTitleLabel = form.querySelector('[data-document-title-label]');
  const relatedDocumentLabel = form.querySelector('[data-related-document-label]');
  const relatedDocumentHint = form.querySelector('[data-related-document-hint]');
  let saving = false;

  function selectedDocumentType() {
    const selected = documentTypeInputs.find(function (input) { return input.checked; });
    return selected ? selected.value : 'SOP';
  }

  function hasSelectedParentSop() {
    return Array.from(form.querySelectorAll('[data-related-links] [data-document-select]')).some(function (select) {
      const option = select.options[select.selectedIndex];
      return option && option.value && option.textContent.trim().indexOf('SOP ·') === 0;
    });
  }

  function refreshDocumentTypeGuidance() {
    const type = selectedDocumentType();
    const isWi = type === 'WI';
    if (documentCodeLabel) documentCodeLabel.textContent = type + ' Code';
    if (documentTitleLabel) documentTitleLabel.textContent = 'ชื่อเอกสาร ' + type;
    if (relatedDocumentLabel) relatedDocumentLabel.textContent = isWi ? 'SOP หลักที่ WI ฉบับนี้อ้างอิง' : 'SOP หรือ WI ที่เกี่ยวข้อง';
    if (relatedDocumentHint) {
      relatedDocumentHint.textContent = isWi
        ? 'จำเป็นต้องเลือก SOP ที่สร้างไว้แล้วอย่างน้อย 1 ฉบับ เพื่อใช้เป็นโครงสร้างหลักของ WI'
        : 'ค้นหาและเลือกเอกสารที่สร้างไว้ สามารถเพิ่มได้หลายรายการ';
      relatedDocumentHint.classList.toggle('text-danger', isWi && !hasSelectedParentSop());
    }
  }

  documentTypeInputs.forEach(function (input) {
    input.addEventListener('change', refreshDocumentTypeGuidance);
  });

  function prepareDocumentPicker(row) {
    if (!row) return;
    const select = row.querySelector('[data-document-select]');
    if (!select || select._medsopOptions) return;
    select._medsopOptions = Array.from(select.options).map(function (option) {
      return { value: option.value, text: option.textContent };
    });
  }

  function filterDocumentPicker(input) {
    const row = input.closest('[data-related-link], [data-step-related-link]');
    const select = row && row.querySelector('[data-document-select]');
    if (!select) return;
    prepareDocumentPicker(row);
    const query = input.value.trim().toLocaleLowerCase('th');
    const currentValue = select.value;
    const visible = select._medsopOptions.filter(function (option) {
      return option.value === '' || option.value === currentValue || option.text.toLocaleLowerCase('th').indexOf(query) !== -1;
    });
    select.replaceChildren.apply(select, visible.map(function (item) {
      const option = document.createElement('option');
      option.value = item.value;
      option.textContent = item.text;
      return option;
    }));
    select.value = currentValue;
  }

  function refresh() {
    const steps = Array.from(list.querySelectorAll('[data-step]'));
    steps.forEach(function (step, index) {
      step.querySelector('[data-step-number]').textContent = index + 1;
      step.querySelector('[data-step-label]').textContent = index + 1;
      const removeButton = step.querySelector('[data-remove-step]');
      removeButton.setAttribute('aria-label', 'ลบขั้นตอนที่ ' + (index + 1));

      step.querySelectorAll('input, textarea').forEach(function (field) {
        const key = field.name.match(/\[(title|description|caution)\]$/);
        if (key) {
          field.name = 'steps[' + index + '][' + key[1] + ']';
          field.id = 'step-' + index + '-' + key[1];
          const label = field.parentElement.querySelector('label');
          if (label) label.htmlFor = field.id;
        }
      });
      const stepId = step.querySelector('[data-step-id]');
      if (stepId) stepId.name = 'steps[' + index + '][id]';
      const mediaInput = step.querySelector('[data-media-input]');
      if (mediaInput) {
        mediaInput.name = 'steps[' + index + '][media][]';
        mediaInput.id = 'step-' + index + '-media';
        const mediaLabel = step.querySelector('label[for*="-media"]');
        if (mediaLabel) mediaLabel.htmlFor = mediaInput.id;
      }
      step.querySelectorAll('[data-keep-media]').forEach(function (field) {
        field.name = 'steps[' + index + '][keep_media][]';
      });
      Array.from(step.querySelectorAll('[data-step-related-link]')).forEach(function (row, linkIndex) {
        prepareDocumentPicker(row);
        const select = row.querySelector('select');
        if (select) select.name = 'steps[' + index + '][related_links][' + linkIndex + '][document_id]';
      });
      refreshImageCount(step.querySelector('[data-media-picker]'));
    });

    hint.textContent = steps.length ? steps.length + ' ขั้นตอน' : 'กรุณาเพิ่มอย่างน้อย 1 ขั้นตอน';
    hint.classList.toggle('text-danger', steps.length === 0);
    saveButton.disabled = steps.length === 0;
  }

  function showAlert(message, type) {
    const oldAlert = form.querySelector('[data-form-alert]');
    if (oldAlert) oldAlert.remove();
    const alert = document.createElement('div');
    alert.className = 'alert alert-' + type + ' d-flex align-items-start gap-2';
    alert.setAttribute('role', 'alert');
    alert.setAttribute('data-form-alert', '');
    alert.setAttribute('tabindex', '-1');
    alert.innerHTML = '<i class="bi bi-exclamation-circle mt-1" aria-hidden="true"></i><div></div>';
    alert.querySelector('div').textContent = message;
    form.prepend(alert);
    alert.focus({ preventScroll: true });
    alert.scrollIntoView({ block: 'center' });
  }

  function setSaving(isSaving) {
    saveButton.disabled = isSaving;
    saving = isSaving;
    saveSpinner.classList.toggle('d-none', !isSaving);
    saveLabel.textContent = isSaving ? 'กำลังบันทึก' : 'บันทึกเอกสาร';
    saveButton.setAttribute('aria-busy', isSaving ? 'true' : 'false');
  }

  function focusFirstInvalid() {
    const invalid = form.querySelector('.is-invalid, [aria-invalid="true"], :invalid');
    if (invalid) {
      invalid.focus();
      invalid.scrollIntoView({ block: 'center', behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
    }
  }

  function fieldLabel(field) {
    if (!field) return '';
    const container = field.closest('.form-group, [data-step]');
    const label = (field.id && form.querySelector('label[for="' + CSS.escape(field.id) + '"]')) || (container && container.querySelector('.form-label, .control-label'));
    let text = label ? label.textContent.trim() : '';
    if (!text && field.name && field.name.indexOf('document_type') !== -1) text = 'ประเภทเอกสาร';
    if (!text && field.name) text = field.name;
    const step = field.closest('[data-step]');
    if (step && field.name && /\[title\]$/.test(field.name)) {
      const number = Array.from(list.querySelectorAll('[data-step]')).indexOf(step) + 1;
      text = 'ชื่อขั้นตอนที่ ' + number;
    }
    return text.replace(/\s*\*\s*$/, '');
  }

  function invalidLabels(fields) {
    return Array.from(new Set(fields.map(fieldLabel).filter(Boolean)));
  }

  form.addEventListener('invalid', function (event) {
    event.preventDefault();
    event.target.classList.add('is-invalid');
    event.target.setAttribute('aria-invalid', 'true');
  }, true);

  form.addEventListener('input', function (event) {
    if (event.target.matches('input, textarea, select') && event.target.checkValidity()) {
      event.target.classList.remove('is-invalid');
      event.target.removeAttribute('aria-invalid');
    }
  });

  form.addEventListener('change', function (event) {
    if (event.target.matches('input, textarea, select') && event.target.checkValidity()) {
      event.target.classList.remove('is-invalid');
      event.target.removeAttribute('aria-invalid');
    }
  });

  form.querySelector('[data-add-step]').addEventListener('click', function () {
    const source = list.querySelector('[data-step]');
    const step = source.cloneNode(true);
    step.querySelectorAll('input, textarea').forEach(function (field) {
      field.value = '';
      field.classList.remove('is-invalid');
    });
    const stepId = step.querySelector('[data-step-id]');
    if (stepId) stepId.value = '0';
    step.querySelectorAll('[data-existing-media], [data-new-media]').forEach(function (item) { item.remove(); });
    step.querySelectorAll('[data-step-related-link]').forEach(function (item) { item.remove(); });
    const preview = step.querySelector('[data-media-preview]');
    if (preview) preview.classList.add('d-none');
    list.appendChild(step);
    refresh();
    step.querySelector('input').focus();
  });

  list.addEventListener('click', function (event) {
    const mediaTrigger = event.target.closest('[data-trigger-media]');
    if (mediaTrigger) {
      mediaTrigger.closest('[data-media-picker]').querySelector('[data-media-input]').click();
      return;
    }
    const addStepLink = event.target.closest('[data-add-step-link]');
    if (addStepLink) {
      const step = addStepLink.closest('[data-step]');
      const links = step.querySelector('[data-step-related-links]');
      const template = form.querySelector('[data-step-related-document-row]');
      const row = template.content.firstElementChild.cloneNode(true);
      links.appendChild(row);
      refresh();
      prepareDocumentPicker(row);
      row.querySelector('[data-document-search]').focus();
      return;
    }
    const removeStepLink = event.target.closest('[data-remove-step-link]');
    if (removeStepLink) {
      removeStepLink.closest('[data-step-related-link]').remove();
      refresh();
      return;
    }
    const mediaRemove = event.target.closest('[data-remove-media]');
    if (mediaRemove) {
      const item = mediaRemove.closest('.medsop-media-item');
      const picker = mediaRemove.closest('[data-media-picker]');
      const fileIndex = item && item.getAttribute('data-file-index');
      if (fileIndex !== null && fileIndex !== '') {
        const input = picker.querySelector('[data-media-input]');
        const transfer = new DataTransfer();
        Array.from(input.files).forEach(function (file, index) {
          if (index !== Number(fileIndex)) transfer.items.add(file);
        });
        input.files = transfer.files;
        renderMediaPreview(picker);
      } else if (item) {
        item.remove();
        if (!picker.querySelector('.medsop-media-item')) picker.querySelector('[data-media-preview]').classList.add('d-none');
        refreshImageCount(picker);
      }
      return;
    }
    const button = event.target.closest('[data-remove-step]');
    if (!button) return;
    if (list.querySelectorAll('[data-step]').length === 1) {
      hint.textContent = 'เอกสารต้องมีอย่างน้อย 1 ขั้นตอน';
      hint.classList.add('text-danger');
      button.closest('[data-step]').querySelector('input').focus();
      return;
    }
    const nextFocus = button.closest('[data-step]').previousElementSibling || button.closest('[data-step]').nextElementSibling;
    button.closest('[data-step]').remove();
    refresh();
    if (nextFocus) nextFocus.querySelector('input').focus();
  });

  function formatFileSize(bytes) {
    return bytes >= 1048576 ? (bytes / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(bytes / 1024)) + ' KB';
  }

  function refreshImageCount(picker) {
    if (!picker) return;
    const existingCount = picker.querySelectorAll('[data-existing-media][data-media-kind="image"]').length;
    const input = picker.querySelector('[data-media-input]');
    const newCount = input ? Array.from(input.files).filter(function (file) { return file.type.indexOf('image/') === 0; }).length : 0;
    const count = existingCount + newCount;
    const hint = picker.querySelector('[data-image-count]');
    const trigger = picker.querySelector('[data-trigger-media]');
    if (hint) {
      hint.textContent = 'รูปภาพ ' + count + ' จาก 5 ภาพ';
      hint.classList.toggle('text-danger', count > 5);
    }
    if (trigger) trigger.setAttribute('data-image-count', String(count));
  }

  function renderMediaPreview(picker) {
    const input = picker.querySelector('[data-media-input]');
    const preview = picker.querySelector('[data-media-preview]');
    preview.querySelectorAll('[data-new-media]').forEach(function (item) { item.remove(); });
    Array.from(input.files).forEach(function (file, index) {
      const item = document.createElement('article');
      item.className = 'medsop-media-item';
      item.setAttribute('data-new-media', '');
      item.setAttribute('data-file-index', index);
      item.setAttribute('data-media-kind', file.type.indexOf('image/') === 0 ? 'image' : 'video');
      const visual = document.createElement(file.type.indexOf('image/') === 0 ? 'img' : 'div');
      if (visual.tagName === 'IMG') {
        visual.src = URL.createObjectURL(file);
        visual.alt = '';
        visual.onload = function () { URL.revokeObjectURL(visual.src); };
      } else {
        visual.className = 'medsop-media-video';
        visual.innerHTML = '<i class="bi bi-play-btn" aria-hidden="true"></i>';
      }
      const meta = document.createElement('div');
      meta.className = 'medsop-media-item__meta';
      const name = document.createElement('span');
      name.textContent = file.name + ' · ' + formatFileSize(file.size);
      name.title = file.name;
      const remove = document.createElement('button');
      remove.type = 'button';
      remove.className = 'btn btn-sm btn-outline-danger';
      remove.setAttribute('data-remove-media', '');
      remove.setAttribute('aria-label', 'นำ ' + file.name + ' ออก');
      remove.innerHTML = '<i class="bi bi-x-lg" aria-hidden="true"></i>';
      meta.append(name, remove);
      item.append(visual, meta);
      preview.appendChild(item);
    });
    preview.classList.toggle('d-none', !preview.querySelector('.medsop-media-item'));
    refreshImageCount(picker);
  }

  list.addEventListener('change', function (event) {
    if (!event.target.matches('[data-media-input]')) return;
    const picker = event.target.closest('[data-media-picker]');
    const existingImageCount = picker.querySelectorAll('[data-existing-media][data-media-kind="image"]').length;
    const selectedImageCount = Array.from(event.target.files).filter(function (file) { return file.type.indexOf('image/') === 0; }).length;
    if (existingImageCount + selectedImageCount > 5) {
      event.target.value = '';
      showAlert('แต่ละขั้นตอนเพิ่มรูปได้สูงสุด 5 ภาพ', 'danger');
      renderMediaPreview(picker);
      return;
    }
    const invalid = Array.from(event.target.files).find(function (file) {
      const isImage = file.type.indexOf('image/') === 0;
      return (isImage && file.size > 10 * 1024 * 1024) || (!isImage && file.size > 100 * 1024 * 1024);
    });
    if (invalid) {
      event.target.value = '';
      showAlert('ไฟล์ ' + invalid.name + ' มีขนาดใหญ่เกินกำหนด', 'danger');
    }
    renderMediaPreview(picker);
  });

  const relatedLinks = form.querySelector('[data-related-links]');
  const addRelatedLink = form.querySelector('[data-add-related-link]');
  function renumberRelatedLinks() {
    if (!relatedLinks) return;
    Array.from(relatedLinks.querySelectorAll('[data-related-link]')).forEach(function (row, index) {
      prepareDocumentPicker(row);
      const select = row.querySelector('select');
      if (select) select.name = 'related_links[' + index + '][document_id]';
    });
  }
  if (addRelatedLink && relatedLinks) {
    addRelatedLink.addEventListener('click', function () {
      const template = form.querySelector('[data-related-document-row]');
      const row = template.content.firstElementChild.cloneNode(true);
      relatedLinks.appendChild(row);
      renumberRelatedLinks();
      prepareDocumentPicker(row);
      row.querySelector('[data-document-search]').focus();
    });
    relatedLinks.addEventListener('click', function (event) {
      const button = event.target.closest('[data-remove-related-link]');
      if (!button) return;
      button.closest('[data-related-link]').remove();
      renumberRelatedLinks();
    });
  }

  form.addEventListener('input', function (event) {
    if (event.target.matches('[data-document-search]')) {
      filterDocumentPicker(event.target);
    }
  });

  form.addEventListener('change', function (event) {
    if (event.target.matches('[data-related-links] [data-document-select]')) {
      refreshDocumentTypeGuidance();
    }
  });

  document.addEventListener('keydown', function (event) {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 's') {
      event.preventDefault();
      form.requestSubmit();
    }
  });

  function submitForm() {
    if (saving) return false;
    if (selectedDocumentType() === 'WI' && !hasSelectedParentSop()) {
      refreshDocumentTypeGuidance();
      showAlert('เอกสาร WI ต้องเลือก SOP หลักที่สร้างไว้แล้วอย่างน้อย 1 ฉบับ', 'danger');
      const firstRelatedSelect = form.querySelector('[data-related-links] [data-document-select]');
      if (firstRelatedSelect) firstRelatedSelect.focus();
      return false;
    }
    if (!form.checkValidity()) {
      const invalidFields = Array.from(form.querySelectorAll(':invalid'));
      invalidFields.forEach(function (field) {
        field.classList.add('is-invalid');
        field.setAttribute('aria-invalid', 'true');
      });
      const labels = invalidLabels(invalidFields);
      showAlert('กรุณากรอกข้อมูลให้ครบ: ' + labels.join(', '), 'danger');
      focusFirstInvalid();
      return false;
    }

    setSaving(true);
    window.fetch(form.action || window.location.href, {
      method: 'POST',
      body: new window.FormData(form),
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
      credentials: 'same-origin'
    }).then(function (response) {
      if (!response.ok) throw new Error('ระบบตอบกลับด้วยรหัส ' + response.status);
      return response.json();
    }).then(function (payload) {
      if (!payload.success) {
        if (window.jQuery && window.jQuery.fn.yiiActiveForm) {
          window.jQuery(form).yiiActiveForm('updateMessages', payload.validation || {}, true);
        }
        const serverFields = Object.keys(payload.validation || {}).map(function (id) { return document.getElementById(id); }).filter(Boolean);
        serverFields.forEach(function (field) {
          field.classList.add('is-invalid');
          field.setAttribute('aria-invalid', 'true');
        });
        const labels = invalidLabels(serverFields);
        const message = labels.length ? 'กรุณาตรวจสอบ: ' + labels.join(', ') : (payload.message || 'กรุณาตรวจสอบข้อมูลที่ระบุ');
        throw new Error(message);
      }
      window.location.assign(payload.redirect);
    }).catch(function (error) {
      setSaving(false);
      showAlert(error.message || 'เชื่อมต่อระบบไม่ได้ กรุณาตรวจสอบเครือข่ายแล้วลองอีกครั้ง', 'danger');
      window.setTimeout(focusFirstInvalid, 0);
    });
    return false;
  }

  if (window.jQuery) {
    window.jQuery(form).on('beforeSubmit', submitForm);
  } else if (window.fetch) {
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      submitForm();
    });
  }

  refresh();
  refreshDocumentTypeGuidance();
})();

(function () {
  document.addEventListener('click', function (event) {
    const trigger = event.target.closest('[data-medsop-confirm]');
    if (!trigger || trigger.dataset.medsopConfirmed === 'true') return;

    event.preventDefault();
    event.stopImmediatePropagation();
    if (typeof window.Swal === 'undefined') return;

    window.Swal.fire({
      title: trigger.dataset.confirmTitle || 'ยืนยันการดำเนินการ',
      text: trigger.dataset.confirmText || 'กรุณาตรวจสอบข้อมูลก่อนดำเนินการต่อ',
      icon: 'question',
      showCancelButton: true,
      reverseButtons: true,
      focusCancel: true,
      confirmButtonColor: '#0d6efd',
      cancelButtonColor: '#64748b',
      confirmButtonText: trigger.dataset.confirmLabel || 'ยืนยัน',
      cancelButtonText: 'ยกเลิก'
    }).then(function (result) {
      if (!result.isConfirmed) return;
      trigger.dataset.medsopConfirmed = 'true';
      trigger.click();
    });
  });
})();
