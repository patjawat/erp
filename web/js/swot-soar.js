/**
 * SwotSoarApp — รายการริเริ่มเชิงกลยุทธ์ของ SOAR (S+O → A → R) โมดูล swot เฟส 5 (แก้หลักการ SOAR)
 * SOAR ไม่จับคู่ข้ามช่องแบบ TOWS — เก็บเป็นรายการริเริ่มเดียว {initiatives:[...]}
 */
class SwotSoarApp {
  constructor(config) {
    this.cfg = config;
    this.items = Array.isArray(config.initiatives) ? config.initiatives : [];
    this.tfLabels = { 'quick-win': 'ทำได้ทันที', 'short-term': 'ระยะสั้น', 'medium-term': 'ระยะกลาง', 'long-term': 'ระยะยาว' };
    this.prLabels = { high: 'สำคัญสูง', medium: 'สำคัญกลาง', low: 'สำคัญต่ำ' };
    this.prTone = { high: 'danger', medium: 'warning', low: 'secondary' };
    this.modalEl = document.getElementById('swotInitModal');
    this.modal = this.modalEl ? bootstrap.Modal.getOrCreateInstance(this.modalEl) : null;
    this.bind();
    this.render();
  }

  bind() {
    const add = document.getElementById('swotInitAdd');
    if (add) add.addEventListener('click', () => this.openModal(null));
    const save = document.getElementById('swotInitSave');
    if (save) save.addEventListener('click', () => this.save());
  }

  esc(s) {
    const d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
  }

  render() {
    const box = document.getElementById('swotInitList');
    if (!box) return;
    if (!this.items.length) {
      box.innerHTML = '<div class="swot-cell-empty"><i class="bi bi-lightbulb"></i> ยังไม่มีริเริ่ม — กด “เพิ่มริเริ่ม” เพื่อแปลง S+O → A → R เป็นแผนปฏิบัติ</div>';
      return;
    }
    box.innerHTML = this.items.map((it, idx) => this.itemHtml(it, idx)).join('');
    box.querySelectorAll('[data-act="edit"]').forEach((b) => b.addEventListener('click', () => this.openModal(parseInt(b.dataset.idx, 10))));
    box.querySelectorAll('[data-act="del"]').forEach((b) => b.addEventListener('click', () => this.del(parseInt(b.dataset.idx, 10))));
  }

  itemHtml(it, idx) {
    let meta = '';
    if (it.timeframe && this.tfLabels[it.timeframe]) meta += '<span class="badge rounded-pill text-bg-light border"><i class="bi bi-clock"></i> ' + this.tfLabels[it.timeframe] + '</span>';
    if (it.priority) meta += '<span class="badge rounded-pill text-bg-' + (this.prTone[it.priority] || 'secondary') + '">' + (this.prLabels[it.priority] || it.priority) + '</span>';
    return '<div class="swot-strategy" style="border-left-color:#0d9488;">' +
      '<div class="d-flex justify-content-between align-items-start">' +
      '<div class="swot-strategy-title">' + this.esc(it.title) + '</div>' +
      '<span class="swot-strategy-actions text-nowrap ms-2">' +
      '<button type="button" data-act="edit" data-idx="' + idx + '" title="แก้ไข"><i class="bi bi-pencil"></i></button>' +
      '<button type="button" data-act="del" data-idx="' + idx + '" title="ลบ"><i class="bi bi-trash"></i></button>' +
      '</span></div>' +
      (it.description ? '<div class="swot-strategy-desc"><span class="text-muted">S+O:</span> ' + this.esc(it.description) + '</div>' : '') +
      (it.aspiration ? '<div class="swot-strategy-desc"><i class="bi bi-flag text-primary"></i> <span class="text-muted">มุ่งสู่:</span> ' + this.esc(it.aspiration) + '</div>' : '') +
      (it.metric ? '<div class="swot-strategy-desc"><i class="bi bi-bullseye text-success"></i> <span class="text-muted">ผลลัพธ์:</span> ' + this.esc(it.metric) + '</div>' : '') +
      (meta ? '<div class="swot-strategy-meta">' + meta + '</div>' : '') +
      '</div>';
  }

  openModal(idx) {
    const it = (idx !== null && idx !== undefined) ? this.items[idx] : null;
    document.getElementById('swotInitId').value = (idx !== null && idx !== undefined) ? idx : '';
    document.getElementById('swotInitTitle').value = it ? (it.title || '') : '';
    document.getElementById('swotInitDesc').value = it ? (it.description || '') : '';
    document.getElementById('swotInitAspiration').value = it ? (it.aspiration || '') : '';
    document.getElementById('swotInitMetric').value = it ? (it.metric || '') : '';
    document.getElementById('swotInitTimeframe').value = it ? (it.timeframe || '') : '';
    document.getElementById('swotInitPriority').value = it ? (it.priority || 'medium') : 'medium';
    document.getElementById('swotInitModalTitle').textContent = it ? 'แก้ไขริเริ่มเชิงกลยุทธ์' : 'เพิ่มริเริ่มเชิงกลยุทธ์';
    if (this.modal) this.modal.show();
    setTimeout(() => document.getElementById('swotInitTitle').focus(), 300);
  }

  save() {
    const title = document.getElementById('swotInitTitle').value.trim();
    if (!title) { document.getElementById('swotInitTitle').focus(); return; }
    const idxRaw = document.getElementById('swotInitId').value;
    const item = {
      id: 'so-' + Date.now(),
      title: title,
      description: document.getElementById('swotInitDesc').value.trim(),
      aspiration: document.getElementById('swotInitAspiration').value.trim(),
      metric: document.getElementById('swotInitMetric').value.trim(),
      timeframe: document.getElementById('swotInitTimeframe').value,
      priority: document.getElementById('swotInitPriority').value,
    };
    if (idxRaw !== '') {
      const idx = parseInt(idxRaw, 10);
      item.id = this.items[idx].id || item.id;
      this.items[idx] = item;
    } else {
      this.items.push(item);
    }
    this.render();
    if (this.modal) this.modal.hide();
    this.persist();
  }

  del(idx) {
    if (!confirm('ลบริเริ่มนี้?')) return;
    this.items.splice(idx, 1);
    this.render();
    this.persist();
  }

  async persist() {
    const body = new URLSearchParams({ matrix: JSON.stringify({ initiatives: this.items }) });
    try {
      const res = await fetch(this.cfg.saveUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
          'X-CSRF-Token': this.cfg.csrf,
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: body.toString(),
      });
      const resp = await res.json();
      if (!resp.success) alert(resp.message || 'บันทึกไม่สำเร็จ');
    } catch (e) {
      alert('เกิดข้อผิดพลาดในการบันทึก');
    }
  }
}
window.SwotSoarApp = SwotSoarApp;
