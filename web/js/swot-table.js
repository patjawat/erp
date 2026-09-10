/**
 * SwotTableApp — บันทึกอัตโนมัติหมวดหมู่/ค่าน้ำหนักในหน้าตาราง (โมดูล swot เฟส 2)
 */
class SwotTableApp {
  constructor(config) {
    this.cfg = config;
    this.bind();
  }

  bind() {
    document.querySelectorAll('.swot-cat').forEach((el) => {
      el.addEventListener('change', () => this.saveField(el, 'category', el.value.trim()));
    });
    document.querySelectorAll('.swot-wt').forEach((el) => {
      el.addEventListener('change', () => this.saveField(el, 'weight', el.value));
    });
  }

  async saveField(el, field, value) {
    const body = new URLSearchParams({ id: el.dataset.id, field: field, value: value });
    try {
      const res = await fetch(this.cfg.url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
          'X-CSRF-Token': this.cfg.csrf,
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: body.toString(),
      });
      const resp = await res.json();
      if (resp.success) {
        this.flash(el);
      } else {
        alert(resp.message || 'บันทึกไม่สำเร็จ');
      }
    } catch (e) {
      alert('เกิดข้อผิดพลาดในการบันทึก');
    }
  }

  flash(el) {
    el.classList.add('swot-saved');
    setTimeout(() => el.classList.remove('swot-saved'), 900);
  }
}
window.SwotTableApp = SwotTableApp;
