/**
 * SwotMatrixApp — สังเคราะห์กลยุทธ์ TOWS/SOAR (โมดูล swot เฟส 3)
 * เก็บสถานะทั้ง matrix ในหน่วยความจำ แก้แล้วบันทึกทั้งชุดผ่าน ajax
 */
class SwotMatrixApp {
  constructor(config) {
    this.cfg = config;
    this.matrix = {};
    (config.cells || []).forEach((c) => {
      this.matrix[c] = Array.isArray(config.matrix && config.matrix[c]) ? config.matrix[c] : [];
    });
    this.isSoar = !!config.isSoar;
    this.modalEl = document.getElementById('swotStrategyModal');
    this.modal = this.modalEl ? bootstrap.Modal.getOrCreateInstance(this.modalEl) : null;
    this.tfLabels = { 'quick-win': 'ทำได้ทันที', 'short-term': 'ระยะสั้น', 'medium-term': 'ระยะกลาง', 'long-term': 'ระยะยาว' };
    this.prLabels = { high: 'สำคัญสูง', medium: 'สำคัญกลาง', low: 'สำคัญต่ำ' };
    this.prTone = { high: 'danger', medium: 'warning', low: 'secondary' };
    this.bind();
    this.renderAll();
  }

  bind() {
    document.querySelectorAll('.swot-cell-add').forEach((btn) => {
      btn.addEventListener('click', () => this.openModal(btn.dataset.cell, null));
    });
    const saveBtn = document.getElementById('swotStrategySaveBtn');
    if (saveBtn) saveBtn.addEventListener('click', () => this.saveItem());
  }

  esc(s) {
    const d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
  }

  renderAll() {
    (this.cfg.cells || []).forEach((c) => this.renderCell(c));
  }

  renderCell(cell) {
    const box = document.querySelector('.swot-cell-items[data-cell="' + cell + '"]');
    if (!box) return;
    const items = this.matrix[cell] || [];
    if (!items.length) {
      box.innerHTML = '<div class="swot-cell-empty"><i class="bi bi-lightbulb"></i> ยังไม่มีกลยุทธ์ — กด + เพื่อเพิ่ม</div>';
      return;
    }
    box.innerHTML = items.map((it, idx) => this.itemHtml(cell, it, idx)).join('');
    box.querySelectorAll('[data-act="edit"]').forEach((b) => {
      b.addEventListener('click', () => this.openModal(cell, parseInt(b.dataset.idx, 10)));
    });
    box.querySelectorAll('[data-act="del"]').forEach((b) => {
      b.addEventListener('click', () => this.deleteItem(cell, parseInt(b.dataset.idx, 10)));
    });
  }

  itemHtml(cell, it, idx) {
    let meta = '';
    if (it.timeframe && this.tfLabels[it.timeframe]) {
      meta += '<span class="badge rounded-pill text-bg-light border"><i class="bi bi-clock"></i> ' + this.tfLabels[it.timeframe] + '</span>';
    }
    if (it.priority) {
      meta += '<span class="badge rounded-pill text-bg-' + (this.prTone[it.priority] || 'secondary') + '">' + (this.prLabels[it.priority] || it.priority) + '</span>';
    }
    if (this.isSoar && it.metric) {
      meta += '<span class="badge rounded-pill text-bg-light border"><i class="bi bi-bullseye"></i> ' + this.esc(it.metric) + '</span>';
    }
    return '<div class="swot-strategy">' +
      '<div class="d-flex justify-content-between align-items-start">' +
      '<div class="swot-strategy-title">' + this.esc(it.title) + '</div>' +
      '<span class="swot-strategy-actions text-nowrap ms-2">' +
      '<button type="button" data-act="edit" data-idx="' + idx + '" title="แก้ไข"><i class="bi bi-pencil"></i></button>' +
      '<button type="button" data-act="del" data-idx="' + idx + '" title="ลบ"><i class="bi bi-trash"></i></button>' +
      '</span></div>' +
      (it.description ? '<div class="swot-strategy-desc">' + this.esc(it.description) + '</div>' : '') +
      (meta ? '<div class="swot-strategy-meta">' + meta + '</div>' : '') +
      '</div>';
  }

  openModal(cell, idx) {
    const it = (idx !== null && idx !== undefined) ? this.matrix[cell][idx] : null;
    document.getElementById('swotStrategyCell').value = cell;
    document.getElementById('swotStrategyId').value = (idx !== null && idx !== undefined) ? idx : '';
    document.getElementById('swotStrategyTitle').value = it ? (it.title || '') : '';
    document.getElementById('swotStrategyDesc').value = it ? (it.description || '') : '';
    document.getElementById('swotStrategyTimeframe').value = it ? (it.timeframe || '') : '';
    document.getElementById('swotStrategyPriority').value = it ? (it.priority || 'medium') : 'medium';
    const metricEl = document.getElementById('swotStrategyMetric');
    if (metricEl) metricEl.value = it ? (it.metric || '') : '';
    document.getElementById('swotStrategyModalTitle').textContent = it ? 'แก้ไขกลยุทธ์' : 'เพิ่มกลยุทธ์';
    const badge = document.getElementById('swotStrategyCellBadge');
    if (badge) badge.textContent = cell.toUpperCase();
    if (this.modal) this.modal.show();
    setTimeout(() => document.getElementById('swotStrategyTitle').focus(), 300);
  }

  saveItem() {
    const cell = document.getElementById('swotStrategyCell').value;
    const idxRaw = document.getElementById('swotStrategyId').value;
    const title = document.getElementById('swotStrategyTitle').value.trim();
    if (!title) { document.getElementById('swotStrategyTitle').focus(); return; }
    const metricEl = document.getElementById('swotStrategyMetric');
    const item = {
      id: 'st-' + Date.now(),
      title: title,
      description: document.getElementById('swotStrategyDesc').value.trim(),
      timeframe: document.getElementById('swotStrategyTimeframe').value,
      priority: document.getElementById('swotStrategyPriority').value,
    };
    if (metricEl) item.metric = metricEl.value.trim();

    if (idxRaw !== '') {
      const idx = parseInt(idxRaw, 10);
      item.id = this.matrix[cell][idx].id || item.id;
      this.matrix[cell][idx] = item;
    } else {
      this.matrix[cell].push(item);
    }
    this.renderCell(cell);
    if (this.modal) this.modal.hide();
    this.persist();
  }

  deleteItem(cell, idx) {
    if (!confirm('ลบกลยุทธ์นี้?')) return;
    this.matrix[cell].splice(idx, 1);
    this.renderCell(cell);
    this.persist();
  }

  async persist() {
    const body = new URLSearchParams({ matrix: JSON.stringify(this.matrix) });
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
window.SwotMatrixApp = SwotMatrixApp;
