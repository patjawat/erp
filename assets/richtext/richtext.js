/**
 * แถบเครื่องมือจัดรูปแบบข้อความสำหรับ <textarea data-richtext>
 *
 * ครอบ textarea เดิมด้วย toolbar + พื้นที่ contenteditable แล้ว sync เนื้อหากลับเข้า
 * textarea (ชื่อฟิลด์ไม่เปลี่ยน ฟอร์มเดิมส่งค่าได้เหมือนเดิม) ฝั่ง server กรอง HTML ซ้ำ
 * อีกชั้นด้วย app\components\RichText::sanitize()
 *
 * data attribute ที่รองรับ
 *   data-richtext        เปิดใช้งาน (ค่าเป็นอะไรก็ได้)
 *   data-rte-label       ป้ายกำกับสำหรับ screen reader
 *   data-rte-required    "true" = บังคับกรอก (ตรวจตอน submit)
 */
(function () {
  'use strict';

  var HTML_PROBE = /<(?:p|br|ul|ol|li|strong|em|b|i|u)\b[^>]*>/i;
  var COMMANDS = [
    { cmd: 'bold', icon: 'type-bold', label: 'ตัวหนา' },
    { cmd: 'italic', icon: 'type-italic', label: 'ตัวเอียง' },
    { cmd: 'underline', icon: 'type-underline', label: 'ขีดเส้นใต้' },
    { cmd: 'insertUnorderedList', icon: 'list-ul', label: 'รายการสัญลักษณ์' },
    { cmd: 'insertOrderedList', icon: 'list-ol', label: 'รายการลำดับเลข' },
    { cmd: 'removeFormat', icon: 'eraser', label: 'ล้างรูปแบบ' }
  ];

  function escapeText(text) {
    return text.replace(/[&<>"]/g, function (ch) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[ch];
    });
  }

  // ข้อมูลใหม่มีแท็ก HTML อยู่แล้ว ข้อมูลเก่าเป็นข้อความล้วน — คงบรรทัดด้วย <br>
  function seedHtml(textarea) {
    var raw = textarea.value;
    if (raw.trim() === '') return '';
    return HTML_PROBE.test(raw) ? raw : escapeText(raw).replace(/\r?\n/g, '<br>');
  }

  function hasContent(area) {
    return !!area.querySelector('ul, ol, li') || area.textContent.trim() !== '';
  }

  function sync(area) {
    var textarea = area._rteTextarea;
    if (!textarea) return;
    var filled = hasContent(area);
    textarea.value = filled ? area.innerHTML : '';
    area.classList.toggle('is-empty', !filled);
  }

  function updateButtons(area) {
    var toolbar = area.previousElementSibling;
    if (!toolbar) return;
    toolbar.querySelectorAll('.erp-rte__btn').forEach(function (btn) {
      var active = false;
      try { active = document.queryCommandState(btn.dataset.command); } catch (e) { active = false; }
      btn.classList.toggle('is-active', active);
      btn.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
  }

  function build(textarea) {
    if (textarea._rteReady) return;
    textarea._rteReady = true;

    var wrap = document.createElement('div');
    wrap.className = 'erp-rte';

    var toolbar = document.createElement('div');
    toolbar.className = 'erp-rte__toolbar';
    toolbar.setAttribute('role', 'toolbar');
    toolbar.setAttribute('aria-label', 'จัดรูปแบบข้อความ');
    COMMANDS.forEach(function (item) {
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'erp-rte__btn';
      btn.dataset.command = item.cmd;
      btn.title = item.label;
      btn.setAttribute('aria-label', item.label);
      btn.setAttribute('aria-pressed', 'false');
      btn.innerHTML = '<i class="bi bi-' + item.icon + '" aria-hidden="true"></i>';
      toolbar.appendChild(btn);
    });

    var area = document.createElement('div');
    area.className = 'erp-rte__area';
    area.contentEditable = 'true';
    area.setAttribute('role', 'textbox');
    area.setAttribute('aria-multiline', 'true');
    if (textarea.dataset.rteLabel) area.setAttribute('aria-label', textarea.dataset.rteLabel);
    if (textarea.dataset.rteRequired === 'true') area.setAttribute('aria-required', 'true');
    if (textarea.getAttribute('placeholder')) area.setAttribute('data-placeholder', textarea.getAttribute('placeholder'));
    if (textarea.rows) area.style.minHeight = Math.max(textarea.rows * 1.6, 4.8) + 'rem';
    area.innerHTML = seedHtml(textarea);

    area._rteTextarea = textarea;
    textarea._rteArea = area;

    wrap.append(toolbar, area);
    textarea.classList.add('visually-hidden');
    textarea.setAttribute('tabindex', '-1');
    textarea.setAttribute('aria-hidden', 'true');
    textarea.removeAttribute('required');
    textarea.after(wrap);
    sync(area);
  }

  function enhance(scope) {
    (scope || document).querySelectorAll('textarea[data-richtext]').forEach(build);
    try { document.execCommand('defaultParagraphSeparator', false, 'p'); } catch (e) {}
  }

  // จับเหตุการณ์ที่ระดับ document ครั้งเดียว รองรับ editor ที่ถูกสร้างทีหลัง
  document.addEventListener('click', function (event) {
    var btn = event.target.closest('.erp-rte__btn');
    if (!btn) return;
    event.preventDefault();
    var area = btn.closest('.erp-rte').querySelector('.erp-rte__area');
    area.focus();
    try { document.execCommand(btn.dataset.command, false, null); } catch (e) {}
    sync(area);
    updateButtons(area);
  });

  ['input', 'blur', 'paste'].forEach(function (type) {
    document.addEventListener(type, function (event) {
      var area = event.target.closest ? event.target.closest('.erp-rte__area') : null;
      if (area) setTimeout(function () { sync(area); }, 0);
    }, true);
  });

  ['keyup', 'mouseup', 'focus'].forEach(function (type) {
    document.addEventListener(type, function (event) {
      var area = event.target.closest ? event.target.closest('.erp-rte__area') : null;
      if (area) updateButtons(area);
    }, true);
  });

  // วางข้อความให้เป็น plain text เสมอ กัน markup แปลกปลอมจากการคัดลอก
  document.addEventListener('paste', function (event) {
    var area = event.target.closest ? event.target.closest('.erp-rte__area') : null;
    if (!area) return;
    event.preventDefault();
    var text = (event.clipboardData || window.clipboardData).getData('text/plain');
    try { document.execCommand('insertText', false, text); } catch (e) {}
    sync(area);
  }, true);

  // sync ทุกช่องก่อนส่งฟอร์ม เผื่อผู้ใช้กด submit ขณะเคอร์เซอร์ยังอยู่ในพื้นที่แก้ไข
  document.addEventListener('submit', function (event) {
    event.target.querySelectorAll('.erp-rte__area').forEach(sync);
  }, true);

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { enhance(); });
  } else {
    enhance();
  }

  window.erpRichText = { enhance: enhance, sync: sync };
})();
