/**
 * SwotBoardApp — ลอจิกฝั่งหน้าเว็บของกระดาน SWOT/SOAR (โมดูล swot เฟส 1)
 * เพิ่ม/แก้/ลบ โพสต์อิท + ลากวางข้ามช่อง ผ่าน ajax
 */
class SwotBoardApp {
  constructor(config) {
    this.cfg = config;
    this.modalEl = document.getElementById('swotNoteModal');
    this.modal = this.modalEl ? bootstrap.Modal.getOrCreateInstance(this.modalEl) : null;
    this.zoomEl = document.getElementById('swotZoomModal');
    this.zoomModal = this.zoomEl ? bootstrap.Modal.getOrCreateInstance(this.zoomEl) : null;
    this.currentZoom = null;
    this.selectedColor = 'yellow';
    this.priorityLabels = { high: 'สูง', medium: 'กลาง', low: 'ต่ำ' };
    this.bind();
    this.wireZoom();
  }

  bind() {
    // ปุ่มเพิ่มในแต่ละช่อง
    document.querySelectorAll('.swot-add-btn').forEach((btn) => {
      btn.addEventListener('click', () => this.openModal(null, btn.dataset.quadrant));
    });

    // มอบหมาย event ให้การ์ด (edit/delete) + drag
    document.querySelectorAll('.swot-note').forEach((el) => this.wireNote(el));

    // drop zone แต่ละช่อง
    document.querySelectorAll('.swot-notes').forEach((zone) => this.wireDropZone(zone));

    // เลือกสี
    const picker = document.getElementById('swotColorPicker');
    if (picker) {
      picker.querySelectorAll('.swot-color-dot').forEach((dot) => {
        dot.addEventListener('click', () => this.selectColor(dot.dataset.color));
      });
    }

    // ปุ่มบันทึกใน modal
    const saveBtn = document.getElementById('swotNoteSaveBtn');
    if (saveBtn) saveBtn.addEventListener('click', () => this.save());
  }

  // ---------- modal ----------
  openModal(note, quadrant) {
    document.getElementById('swotNoteId').value = note ? note.dataset.id : '';
    document.getElementById('swotNoteQuadrant').value = quadrant || (note ? note.dataset.quadrant : '');
    document.getElementById('swotNoteContent').value = note ? note.dataset.content : '';
    document.getElementById('swotNoteWeight').value = note ? (note.dataset.weight || '3') : '3';
    document.getElementById('swotNotePriority').value = note ? (note.dataset.priority || 'medium') : 'medium';
    this.selectColor(note ? (note.dataset.color || 'yellow') : 'yellow');
    document.getElementById('swotNoteModalTitle').textContent = note ? 'แก้ไขประเด็น' : 'เพิ่มประเด็น';
    if (this.modal) this.modal.show();
    setTimeout(() => document.getElementById('swotNoteContent').focus(), 300);
  }

  selectColor(color) {
    this.selectedColor = color;
    document.querySelectorAll('#swotColorPicker .swot-color-dot').forEach((d) => {
      d.classList.toggle('selected', d.dataset.color === color);
    });
  }

  // ---------- ajax helper ----------
  async post(url, data) {
    const body = new URLSearchParams(data);
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        'X-CSRF-Token': this.cfg.csrf,
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: body.toString(),
    });
    return res.json();
  }

  // ---------- CRUD ----------
  async save() {
    const content = document.getElementById('swotNoteContent').value.trim();
    if (!content) {
      document.getElementById('swotNoteContent').focus();
      return;
    }
    const id = document.getElementById('swotNoteId').value;
    const quadrant = document.getElementById('swotNoteQuadrant').value;
    const payload = {
      board_id: this.cfg.boardId,
      id: id,
      quadrant: quadrant,
      content: content,
      weight: document.getElementById('swotNoteWeight').value,
      priority: document.getElementById('swotNotePriority').value,
      color: this.selectedColor,
    };
    const resp = await this.post(this.cfg.urls.save, payload);
    if (!resp.success) {
      alert(resp.message || 'บันทึกไม่สำเร็จ');
      return;
    }
    if (id) {
      this.replaceNote(resp.note);
    } else {
      this.appendNote(resp.note);
    }
    if (this.modal) this.modal.hide();
  }

  async del(el) {
    if (!confirm('ยืนยันการลบประเด็นนี้?')) return;
    const resp = await this.post(this.cfg.urls.delete, { id: el.dataset.id });
    if (!resp.success) {
      alert(resp.message || 'ลบไม่สำเร็จ');
      return;
    }
    const zone = el.closest('.swot-notes');
    el.remove();
    this.refreshEmpty(zone);
  }

  async move(el, newQuadrant) {
    const resp = await this.post(this.cfg.urls.move, { id: el.dataset.id, quadrant: newQuadrant });
    if (!resp.success) {
      alert(resp.message || 'ย้ายไม่สำเร็จ');
      return false;
    }
    el.dataset.quadrant = newQuadrant;
    return true;
  }

  // ---------- DOM rendering ----------
  buildNoteEl(note) {
    const cs = this.cfg.colors[note.color] || this.cfg.colors.yellow;
    const el = document.createElement('div');
    el.className = 'swot-note';
    el.setAttribute('draggable', 'true');
    el.dataset.id = note.id;
    el.dataset.quadrant = note.quadrant;
    el.dataset.content = note.content;
    el.dataset.color = note.color;
    el.dataset.priority = note.priority;
    el.dataset.weight = note.weight;
    el.dataset.category = note.category || '';
    el.style.background = cs.bg;
    el.style.borderColor = cs.border;

    const c = document.createElement('div');
    c.className = 'swot-note-content';
    c.textContent = note.content;

    const foot = document.createElement('div');
    foot.className = 'swot-note-foot';
    foot.innerHTML =
      '<span class="swot-weight" title="ค่าน้ำหนักความสำคัญ"><i class="bi bi-star-fill"></i> ' + note.weight + '</span>' +
      '<span class="swot-note-actions">' +
      '<button type="button" class="swot-edit" title="แก้ไข"><i class="bi bi-pencil"></i></button>' +
      '<button type="button" class="swot-del" title="ลบ"><i class="bi bi-trash"></i></button>' +
      '</span>';

    el.appendChild(c);
    el.appendChild(foot);
    this.wireNote(el);
    return el;
  }

  appendNote(note) {
    const zone = document.querySelector('.swot-notes[data-quadrant="' + note.quadrant + '"]');
    if (!zone) return;
    zone.appendChild(this.buildNoteEl(note));
    this.refreshEmpty(zone);
  }

  replaceNote(note) {
    const old = document.querySelector('.swot-note[data-id="' + note.id + '"]');
    if (!old) { this.appendNote(note); return; }
    const zoneOld = old.closest('.swot-notes');
    const el = this.buildNoteEl(note);
    old.replaceWith(el);
    this.refreshEmpty(zoneOld);
  }

  refreshEmpty(zone) {
    if (!zone) return;
    const quad = zone.closest('.swot-quadrant');
    const empty = quad ? quad.querySelector('.swot-empty') : null;
    if (empty) empty.classList.toggle('d-none', zone.querySelectorAll('.swot-note').length > 0);
  }

  // ---------- wiring ----------
  wireNote(el) {
    el.querySelector('.swot-edit').addEventListener('click', (e) => {
      e.stopPropagation();
      this.openModal(el, el.dataset.quadrant);
    });
    el.querySelector('.swot-del').addEventListener('click', (e) => {
      e.stopPropagation();
      this.del(el);
    });
    el.addEventListener('dragstart', (e) => {
      el.classList.add('swot-dragging');
      this._dragged = true;
      e.dataTransfer.setData('text/plain', el.dataset.id);
      e.dataTransfer.effectAllowed = 'move';
    });
    el.addEventListener('dragend', () => {
      el.classList.remove('swot-dragging');
      // กันไม่ให้ click ที่เกิดหลังลากเปิด zoom
      setTimeout(() => { this._dragged = false; }, 0);
    });
    // คลิกที่ใบโน้ต (ไม่ใช่ปุ่ม) = ขยายดู
    el.addEventListener('click', () => {
      if (this._dragged) return;
      this.openZoom(el);
    });
  }

  // ---------- zoom (ขยายดู) ----------
  wireZoom() {
    const editBtn = document.getElementById('swotZoomEdit');
    const delBtn = document.getElementById('swotZoomDel');
    if (editBtn) editBtn.addEventListener('click', () => {
      const el = this.currentZoom;
      if (this.zoomModal) this.zoomModal.hide();
      if (el) this.openModal(el, el.dataset.quadrant);
    });
    if (delBtn) delBtn.addEventListener('click', () => {
      const el = this.currentZoom;
      if (this.zoomModal) this.zoomModal.hide();
      if (el) this.del(el);
    });
  }

  openZoom(el) {
    this.currentZoom = el;
    const info = (this.cfg.quadInfo && this.cfg.quadInfo[el.dataset.quadrant]) || {};
    const cs = this.cfg.colors[el.dataset.color] || this.cfg.colors.yellow;
    const badge = document.getElementById('swotZoomQuad');
    badge.textContent = (info.code ? info.code + ' · ' : '') + (info.short || '');
    badge.style.background = cs.border;
    badge.style.color = '#111';
    document.getElementById('swotZoomContent').textContent = el.dataset.content || '';
    document.getElementById('swotZoomWeight').textContent = el.dataset.weight || '';
    document.getElementById('swotZoomPriority').textContent = this.priorityLabels[el.dataset.priority] || el.dataset.priority || '';
    const card = document.getElementById('swotZoomCard');
    if (card) { card.style.background = cs.bg; }
    if (this.zoomModal) this.zoomModal.show();
  }

  wireDropZone(zone) {
    zone.addEventListener('dragover', (e) => {
      e.preventDefault();
      zone.classList.add('swot-dragover');
    });
    zone.addEventListener('dragleave', () => zone.classList.remove('swot-dragover'));
    zone.addEventListener('drop', async (e) => {
      e.preventDefault();
      zone.classList.remove('swot-dragover');
      const dragging = document.querySelector('.swot-note.swot-dragging');
      if (!dragging) return;
      const newQuadrant = zone.dataset.quadrant;
      const fromZone = dragging.closest('.swot-notes');
      if (fromZone === zone) return;
      const ok = await this.move(dragging, newQuadrant);
      if (ok) {
        zone.appendChild(dragging);
        this.refreshEmpty(fromZone);
        this.refreshEmpty(zone);
      }
    });
  }
}
window.SwotBoardApp = SwotBoardApp;
