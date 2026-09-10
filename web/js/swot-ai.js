/**
 * SwotAiApp — เรียก AI วิเคราะห์กระดาน SWOT/SOAR แล้วแสดงผล (โมดูล swot เฟส 5)
 */
class SwotAiApp {
  constructor(config) {
    this.cfg = config;
    this.isSoar = !!config.isSoar;
    this.analysis = config.analysis || null;
    this.modalEl = document.getElementById('swotAiModal');
    this.bind();
  }

  bind() {
    const run = document.getElementById('swotAiRunBtn');
    const rerun = document.getElementById('swotAiRerunBtn');
    if (run) run.addEventListener('click', () => this.run());
    if (rerun) rerun.addEventListener('click', () => this.run());
    if (this.modalEl) {
      this.modalEl.addEventListener('shown.bs.modal', () => {
        if (this.analysis) { this.render(this.analysis); }
        else { this.showState('empty'); }
      });
    }
  }

  showState(s) {
    document.getElementById('swotAiLoading').classList.toggle('d-none', s !== 'loading');
    document.getElementById('swotAiEmpty').classList.toggle('d-none', s !== 'empty');
    document.getElementById('swotAiResult').classList.toggle('d-none', s !== 'result');
    document.getElementById('swotAiRerunBtn').classList.toggle('d-none', s !== 'result');
  }

  esc(s) {
    const d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
  }

  async run() {
    this.showState('loading');
    try {
      const res = await fetch(this.cfg.url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
          'X-CSRF-Token': this.cfg.csrf,
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: '',
      });
      const resp = await res.json();
      if (resp.success) {
        this.analysis = resp.analysis;
        this.render(resp.analysis);
      } else {
        alert(resp.message || 'วิเคราะห์ไม่สำเร็จ');
        this.showState(this.analysis ? 'result' : 'empty');
        if (this.analysis) this.render(this.analysis);
      }
    } catch (e) {
      alert('เกิดข้อผิดพลาดในการเรียก AI');
      this.showState(this.analysis ? 'result' : 'empty');
    }
  }

  render(a) {
    const box = document.getElementById('swotAiResult');
    const score = Math.max(0, Math.min(100, parseInt(a.healthScore, 10) || 0));
    const tone = score >= 70 ? 'success' : (score >= 40 ? 'warning' : 'danger');

    // highlights ที่เกี่ยวกับกรอบคิด
    const hl = a.highlights || {};
    const map = this.isSoar
      ? [['keyStrength', 'จุดแข็งเด่น', 'success'], ['majorOpportunity', 'โอกาสสำคัญ', 'info'], ['coreAspiration', 'ความปรารถนาหลัก', 'primary'], ['criticalResult', 'ผลลัพธ์สำคัญ', 'teal']]
      : [['keyStrength', 'จุดแข็งเด่น', 'success'], ['criticalWeakness', 'จุดอ่อนวิกฤต', 'danger'], ['majorOpportunity', 'โอกาสสำคัญ', 'info'], ['greatestThreat', 'อุปสรรคใหญ่', 'warning']];
    let hlHtml = '';
    map.forEach((m) => {
      if (hl[m[0]]) {
        hlHtml += '<div class="col-6"><div class="border rounded-3 p-2 h-100">' +
          '<div class="small fw-semibold text-' + (m[2] === 'teal' ? 'success' : m[2]) + '">' + m[1] + '</div>' +
          '<div class="small">' + this.esc(hl[m[0]]) + '</div></div></div>';
      }
    });

    let recHtml = '';
    (a.recommendations || []).forEach((r) => { recHtml += '<li>' + this.esc(r) + '</li>'; });

    let sugHtml = '';
    (a.suggestedStrategies || []).forEach((s) => {
      sugHtml += '<li class="mb-2">' +
        (s.cell ? '<span class="badge text-bg-secondary me-1">' + this.esc((s.cell || '').toUpperCase()) + '</span>' : '') +
        '<strong>' + this.esc(s.title) + '</strong>' +
        (s.description ? '<div class="small text-muted">' + this.esc(s.description) + '</div>' : '') +
        (s.aspiration ? '<div class="small"><i class="bi bi-flag text-primary"></i> มุ่งสู่: ' + this.esc(s.aspiration) + '</div>' : '') +
        (s.metric ? '<div class="small"><i class="bi bi-bullseye text-success"></i> ผลลัพธ์: ' + this.esc(s.metric) + '</div>' : '') +
        '</li>';
    });

    box.innerHTML =
      '<div class="d-flex align-items-center gap-3 mb-3">' +
        '<div class="text-center">' +
          '<div class="fs-3 fw-bold text-' + tone + '">' + score + '</div>' +
          '<div class="small text-muted">คะแนนความพร้อม</div>' +
        '</div>' +
        '<div class="flex-grow-1"><div class="progress" style="height:12px;">' +
          '<div class="progress-bar bg-' + tone + '" style="width:' + score + '%"></div>' +
        '</div><div class="mt-2">' + this.esc(a.summary) + '</div></div>' +
      '</div>' +
      (hlHtml ? '<div class="row g-2 mb-3">' + hlHtml + '</div>' : '') +
      (recHtml ? '<h6 class="fw-bold mt-3"><i class="bi bi-lightbulb text-warning me-1"></i>ข้อเสนอแนะ</h6><ul class="mb-3">' + recHtml + '</ul>' : '') +
      (sugHtml ? '<h6 class="fw-bold"><i class="bi bi-diagram-3 text-primary me-1"></i>กลยุทธ์ที่แนะนำ</h6><ul class="list-unstyled swot-ai-sug">' + sugHtml + '</ul>' : '') +
      (a.analyzedAt ? '<div class="text-muted small mt-2 text-end">วิเคราะห์เมื่อ ' + this.esc(a.analyzedAt) + '</div>' : '');

    this.showState('result');
  }
}
window.SwotAiApp = SwotAiApp;
