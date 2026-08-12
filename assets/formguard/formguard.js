/**
 * กันการส่งฟอร์มซ้ำจากการกดปุ่มบันทึกหลายครั้ง
 *
 * เมื่อฟอร์มถูกส่งสำเร็จ (ผ่าน validation ฝั่งหน้าเว็บแล้ว) จะปิดปุ่ม submit
 * ทั้งหมดของฟอร์มนั้นและแสดงข้อความกำลังบันทึก ถ้าเบราว์เซอร์ยกเลิกการส่ง
 * (กด Back หรือ validation ฝั่ง server ตีกลับ) ปุ่มจะกลับมาใช้งานได้เอง
 *
 * ปิดการทำงานเฉพาะฟอร์มได้ด้วย data-allow-resubmit="true"
 */
(function () {
  'use strict';

  var BUSY_CLASS = 'is-submitting';

  function buttonsOf(form) {
    return form.querySelectorAll('button[type="submit"], input[type="submit"], button:not([type])');
  }

  function lock(form) {
    if (form.dataset.allowResubmit === 'true') return;
    if (form.dataset.submitLocked === '1') return;
    form.dataset.submitLocked = '1';

    buttonsOf(form).forEach(function (btn) {
      btn.disabled = true;
      btn.classList.add(BUSY_CLASS);
      if (!btn.dataset.originalHtml) btn.dataset.originalHtml = btn.innerHTML;
      if (btn.tagName === 'BUTTON') {
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>กำลังบันทึก...';
      }
    });
  }

  function unlock(form) {
    if (form.dataset.submitLocked !== '1') return;
    delete form.dataset.submitLocked;
    buttonsOf(form).forEach(function (btn) {
      btn.disabled = false;
      btn.classList.remove(BUSY_CLASS);
      if (btn.dataset.originalHtml) btn.innerHTML = btn.dataset.originalHtml;
    });
  }

  document.addEventListener('submit', function (event) {
    if (event.defaultPrevented) return;
    var form = event.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (form.dataset.submitLocked === '1') { event.preventDefault(); return; }
    // รอให้ handler อื่น (เช่น validation ของ ActiveForm) ตัดสินใจก่อน
    setTimeout(function () { if (!event.defaultPrevented) lock(form); }, 0);
  }, false);

  // ActiveForm ตีกลับเมื่อ validation ฝั่งหน้าเว็บไม่ผ่าน ต้องปลดล็อกให้แก้ไขต่อได้
  document.addEventListener('afterValidate', function (event) {
    var form = event.target;
    if (form instanceof HTMLFormElement && form.querySelector('.has-error, .is-invalid')) unlock(form);
  }, true);

  // กลับมาที่หน้าเดิมด้วยปุ่ม Back — เบราว์เซอร์คืนหน้าจาก cache พร้อมปุ่มที่ถูกปิดไว้
  window.addEventListener('pageshow', function (event) {
    if (event.persisted) document.querySelectorAll('form').forEach(unlock);
  });
})();
