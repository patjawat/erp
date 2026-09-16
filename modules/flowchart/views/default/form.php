<?php

use app\modules\flowchart\models\Flowchart;
use app\modules\flowchart\models\FlowchartStep;
use app\modules\settings\models\OrgUnit;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var Flowchart $model */
/** @var string $stepsJson */

$this->title = 'ป้อนขั้นตอน: ' . $model->title;
$this->registerJsFile('@web/vendor/mermaid/mermaid.min.js', ['position' => \yii\web\View::POS_HEAD]);

$typeOptions = [];
foreach (FlowchartStep::TYPE_INFO as $k => $info) {
    $typeOptions[$k] = $info['label'];
}
$unitGroups = OrgUnit::groupedForSelect((int) ($model->budget_year ?: date('Y')), $model->org_unit_id ? (int) $model->org_unit_id : null);
?>
<?php $this->beginBlock('page-title'); ?>ป้อนขั้นตอน<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?><?= Html::encode($model->code ? $model->code . ' · ' : '') ?><?= Html::encode($model->title) ?><?php $this->endBlock(); ?>

<?= Html::beginForm(['update', 'id' => $model->id], 'post', ['id' => 'fc-form']) ?>
<div class="row g-3 fc-editor">

    <!-- ซ้าย: ป้อนข้อมูล -->
    <div class="col-12 col-xl-7">
        <!-- ข้อมูลผัง -->
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-12 col-md-8">
                        <label class="form-label fw-semibold small mb-1">ชื่อกระบวนการ <span class="text-danger">*</span></label>
                        <?= Html::activeTextInput($model, 'title', ['class' => 'form-control', 'required' => true, 'maxlength' => 255]) ?>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label fw-semibold small mb-1">ประเภท</label>
                        <?= Html::activeDropDownList($model, 'category', Flowchart::CATEGORY_LABELS, ['class' => 'form-select', 'prompt' => '— ไม่ระบุ —']) ?>
                    </div>
                    <div class="col-12 col-md-8">
                        <label class="form-label fw-semibold small mb-1">หน่วยงาน / กลุ่มงาน / ทีมประสาน</label>
                        <?= Html::activeDropDownList($model, 'org_unit_id', $unitGroups, ['class' => 'form-select', 'prompt' => '— เลือกหน่วยงาน (เว้นว่างได้) —']) ?>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label fw-semibold small mb-1">รหัสผัง</label>
                        <input type="text" class="form-control bg-body-secondary" value="<?= Html::encode($model->code ?: '(ออกให้อัตโนมัติ)') ?>" readonly>
                        <div class="form-text" style="font-size:.72rem;">อักษรย่อหน่วย = รหัสนำ · เปลี่ยนหน่วยแล้วรหัสจะออกใหม่</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small mb-1">คำอธิบาย/วัตถุประสงค์</label>
                        <?= Html::activeTextarea($model, 'description', ['class' => 'form-control', 'rows' => 2]) ?>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label fw-semibold small mb-1">ทิศทางผัง</label>
                        <?= Html::activeDropDownList($model, 'diagram_dir', [
                            Flowchart::DIR_TD => 'บนลงล่าง (TD)',
                            Flowchart::DIR_LR => 'ซ้ายไปขวา (LR)',
                        ], ['class' => 'form-select', 'id' => 'fc-dir']) ?>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label fw-semibold small mb-1">สถานะ</label>
                        <?= Html::activeDropDownList($model, 'status', [
                            Flowchart::STATUS_DRAFT => 'ฉบับร่าง',
                            Flowchart::STATUS_PUBLISHED => 'เผยแพร่แล้ว',
                        ], ['class' => 'form-select']) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ตารางขั้นตอน -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-body d-flex align-items-center justify-content-between">
                <span class="fw-semibold"><i class="bi bi-list-ol me-1"></i> ขั้นตอนการทำงาน</span>
                <span class="small text-muted">เพิ่มทีละขั้น ระบบวาดผังให้อัตโนมัติ</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0 fc-steps">
                        <thead class="table-light">
                            <tr>
                                <th style="width:34px;"></th>
                                <th style="width:44px;">#</th>
                                <th style="width:140px;">ประเภท</th>
                                <th>ขั้นตอน</th>
                                <th style="width:150px;">ผู้รับผิดชอบ</th>
                                <th style="width:40px;"></th>
                            </tr>
                        </thead>
                        <tbody id="fc-rows"><!-- rows rendered by JS --></tbody>
                    </table>
                </div>
                <div class="p-2 border-top">
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" id="fc-add">
                        <i class="bi bi-plus-lg me-1"></i> เพิ่มขั้นตอน
                    </button>
                    <span class="small text-muted ms-2">แถวประเภท "ตัดสินใจ" กำหนดปลายทางเมื่อ ใช่/ไม่ ได้</span>
                </div>
            </div>
        </div>

        <div class="d-grid d-sm-flex justify-content-sm-end gap-2 mt-3">
            <?= Html::submitButton('<i class="bi bi-check-lg me-1"></i> บันทึกผัง', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('ยกเลิก', ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>

    <!-- ขวา: พรีวิวผังสด -->
    <div class="col-12 col-xl-5">
        <div class="card shadow-sm border-0 fc-preview-card">
            <div class="card-header bg-body d-flex align-items-center justify-content-between">
                <span class="fw-semibold"><i class="bi bi-eye me-1"></i> พรีวิวผัง</span>
                <span class="small text-muted">อัปเดตอัตโนมัติ</span>
            </div>
            <div class="card-body">
                <div id="fc-preview" class="fc-preview text-center"></div>
                <div id="fc-legend" class="fc-legend small mt-2"></div>
            </div>
        </div>
    </div>
</div>

<?= Html::hiddenInput('steps', '', ['id' => 'fc-steps-json']) ?>
<?= Html::endForm() ?>

<template id="fc-row-tpl">
    <tr class="fc-row">
        <td class="text-center text-muted fc-drag" title="ลากเพื่อจัดลำดับ" style="cursor:grab;"><i class="bi bi-grip-vertical"></i></td>
        <td class="text-center fw-semibold fc-num"></td>
        <td>
            <select class="form-select form-select-sm fc-f" data-f="type">
                <?php foreach ($typeOptions as $k => $label): ?>
                    <option value="<?= $k ?>"><?= Html::encode($label) ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm fc-f" data-f="title" placeholder="ข้อความในกล่อง">
            <div class="fc-branch mt-1" hidden>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">ใช่ →</span>
                    <select class="form-select fc-f fc-branch-sel" data-f="branch_yes"></select>
                    <span class="input-group-text">ไม่ →</span>
                    <select class="form-select fc-f fc-branch-sel" data-f="branch_no"></select>
                </div>
            </div>
            <div class="fc-extra mt-1" hidden>
                <div class="row g-1">
                    <div class="col-6"><input type="text" class="form-control form-control-sm fc-f" data-f="related_doc" placeholder="เอกสารที่เกี่ยวข้อง"></div>
                    <div class="col-6"><input type="text" class="form-control form-control-sm fc-f" data-f="duration" placeholder="ระยะเวลา"></div>
                </div>
            </div>
            <button type="button" class="btn btn-link btn-sm p-0 fc-toggle-extra text-muted" style="font-size:.75rem;">+ เอกสาร/เวลา</button>
        </td>
        <td><input type="text" class="form-control form-control-sm fc-f" data-f="actor" placeholder="ผู้รับผิดชอบ"></td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-link text-danger p-0 fc-del" title="ลบขั้นตอน"><i class="bi bi-x-lg"></i></button>
        </td>
    </tr>
</template>

<?php
$dataUrl = Url::to(['update', 'id' => $model->id]);
$isDark = 'document.documentElement.getAttribute("data-bs-theme")==="dark"';
$this->registerJs(<<<JS
(function(){
  var initSteps = {$stepsJson};
  var SHAPES = {
    start:['([','])'], process:['[',']'], decision:['{','}'],
    document:['[/','/]'], subprocess:['[[',']]'], end:['([','])']
  };
  var PALETTE = [
    ['#E0F2FE','#0284C7','#0C4A6E'],['#DCFCE7','#16A34A','#14532D'],
    ['#FEF3C7','#D97706','#78350F'],['#FCE7F3','#DB2777','#831843'],
    ['#F3E8FF','#9333EA','#581C87'],['#CFFAFE','#0891B2','#164E63'],
    ['#FFEDD5','#EA580C','#7C2D12'],['#E2E8F0','#475569','#1E293B']
  ];
  var steps = Array.isArray(initSteps) && initSteps.length ? initSteps.map(normalize) : [
    {type:'start', title:'เริ่ม'}, {type:'process', title:''}, {type:'end', title:'จบ'}
  ].map(normalize);

  function normalize(s){
    return {
      type: s.type || 'process', title: s.title || '', actor: s.actor || '',
      related_doc: s.related_doc || '', duration: s.duration || '', note: s.note || '',
      branch_yes: (s.branch_yes===0||s.branch_yes)?String(s.branch_yes):'',
      branch_no: (s.branch_no===0||s.branch_no)?String(s.branch_no):''
    };
  }

  var tbody = document.getElementById('fc-rows');
  var tpl = document.getElementById('fc-row-tpl');
  var jsonInput = document.getElementById('fc-steps-json');
  var previewEl = document.getElementById('fc-preview');
  var legendEl = document.getElementById('fc-legend');
  var dirEl = document.getElementById('fc-dir');
  var form = document.getElementById('fc-form');
  var mermaidReady = false, renderSeq = 0, renderTimer = null;

  function render(){
    tbody.innerHTML = '';
    steps.forEach(function(s, i){
      var node = tpl.content.firstElementChild.cloneNode(true);
      node.querySelector('.fc-num').textContent = (i+1);
      node.querySelectorAll('.fc-f').forEach(function(el){
        var f = el.getAttribute('data-f');
        if (el.tagName === 'SELECT' && el.classList.contains('fc-branch-sel')) return;
        el.value = s[f] != null ? s[f] : '';
      });
      // branch selects
      var isDec = s.type === 'decision';
      node.querySelector('.fc-branch').hidden = !isDec;
      if (isDec){
        node.querySelectorAll('.fc-branch-sel').forEach(function(sel){
          var f = sel.getAttribute('data-f');
          sel.innerHTML = '<option value="">— เลือกขั้น —</option>' + steps.map(function(t, j){
            if (j === i) return '';
            var lbl = (j+1) + '. ' + (t.title || labelOf(t.type));
            return '<option value="'+(j+1)+'">'+escapeHtml(lbl)+'</option>';
          }).join('');
          sel.value = s[f] || '';
        });
      }
      tbody.appendChild(node);
    });
    serialize();
    schedulePreview();
  }

  function serialize(){ jsonInput.value = JSON.stringify(steps); }

  function labelOf(t){
    return ({start:'เริ่ม',process:'ดำเนินการ',decision:'ตัดสินใจ',document:'เอกสาร',subprocess:'กระบวนการย่อย',end:'จบ'})[t] || t;
  }
  function escapeHtml(s){ return String(s).replace(/[&<>"]/g, function(c){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'})[c]; }); }
  function esc(s){ return String(s).replace(/\\r?\\n/g,'<br/>').replace(/"/g,'#quot;').trim(); }

  function buildMermaid(){
    var dir = dirEl.value === 'LR' ? 'LR' : 'TD';
    if (!steps.length) return 'flowchart '+dir+'\\n  e["ยังไม่มีขั้นตอน"]';
    var L = ['flowchart '+dir];
    steps.forEach(function(s, i){
      var sh = SHAPES[s.type] || SHAPES.process;
      L.push('  n'+i+sh[0]+'"'+esc(s.title || labelOf(s.type))+'"'+sh[1]);
    });
    steps.forEach(function(s, i){
      if (s.type === 'end') return;
      if (s.type === 'decision'){
        if (s.branch_yes) L.push('  n'+i+' -->|ใช่| n'+(parseInt(s.branch_yes,10)-1));
        if (s.branch_no) L.push('  n'+i+' -->|ไม่| n'+(parseInt(s.branch_no,10)-1));
        return;
      }
      if (i+1 < steps.length) L.push('  n'+i+' --> n'+(i+1));
    });
    // สีตามผู้รับผิดชอบ
    var map = {}, order = [];
    steps.forEach(function(s){
      var a = (s.actor||'').trim();
      if (a && !(a in map)){ map[a] = order.length; order.push(a); }
    });
    order.forEach(function(a, idx){
      var c = PALETTE[idx % PALETTE.length];
      L.push('  classDef actor'+idx+' fill:'+c[0]+',stroke:'+c[1]+',color:'+c[2]+',stroke-width:1px;');
    });
    var groups = {};
    steps.forEach(function(s, i){
      var a = (s.actor||'').trim();
      if (a && a in map){ (groups[map[a]] = groups[map[a]] || []).push('n'+i); }
    });
    Object.keys(groups).forEach(function(k){ L.push('  class '+groups[k].join(',')+' actor'+k+';'); });
    return L.join('\\n');
  }

  function renderLegend(){
    var map = {}, order = [];
    steps.forEach(function(s){ var a=(s.actor||'').trim(); if(a && !(a in map)){ map[a]=order.length; order.push(a);} });
    if (!order.length){ legendEl.innerHTML=''; return; }
    legendEl.innerHTML = order.map(function(a, idx){
      var c = PALETTE[idx % PALETTE.length];
      return '<span class="fc-legend-item me-2"><span class="fc-swatch" style="background:'+c[0]+';border-color:'+c[1]+'"></span>'+escapeHtml(a)+'</span>';
    }).join('');
  }

  function schedulePreview(){
    clearTimeout(renderTimer);
    renderTimer = setTimeout(doPreview, 250);
  }
  async function doPreview(){
    renderLegend();
    if (!mermaidReady || !window.mermaid) return;
    var code = buildMermaid();
    var id = 'fcm'+(++renderSeq);
    try {
      var out = await window.mermaid.render(id, code);
      previewEl.innerHTML = out.svg;
    } catch(e){
      previewEl.innerHTML = '<div class="text-danger small py-3"><i class="bi bi-exclamation-triangle"></i> วาดผังไม่สำเร็จ ตรวจข้อความในขั้นตอน</div>';
    }
  }

  // events
  tbody.addEventListener('input', onFieldChange);
  tbody.addEventListener('change', onFieldChange);
  function onFieldChange(e){
    var el = e.target.closest('.fc-f'); if (!el) return;
    var tr = el.closest('.fc-row'); var i = [].indexOf.call(tbody.children, tr);
    if (i < 0) return;
    var f = el.getAttribute('data-f');
    steps[i][f] = el.value;
    if (f === 'type'){ render(); return; }   // เปลี่ยนประเภท -> re-render (โชว์/ซ่อน branch)
    serialize(); schedulePreview();
  }
  tbody.addEventListener('click', function(e){
    var del = e.target.closest('.fc-del');
    if (del){ var tr=del.closest('.fc-row'); var i=[].indexOf.call(tbody.children,tr); if(i>=0){ steps.splice(i,1); render(); } return; }
    var tg = e.target.closest('.fc-toggle-extra');
    if (tg){ var ex = tg.parentElement.querySelector('.fc-extra'); ex.hidden = !ex.hidden; }
  });
  document.getElementById('fc-add').addEventListener('click', function(){
    steps.push(normalize({type:'process', title:''})); render();
    var rows = tbody.children; rows[rows.length-1].querySelector('[data-f="title"]').focus();
  });
  dirEl.addEventListener('change', schedulePreview);
  form.addEventListener('submit', serialize);

  // drag reorder (แถว)
  var dragIdx = null;
  tbody.addEventListener('mousedown', function(e){ if(e.target.closest('.fc-drag')){ var tr=e.target.closest('.fc-row'); tr.setAttribute('draggable','true'); dragIdx=[].indexOf.call(tbody.children,tr); } });
  tbody.addEventListener('dragstart', function(e){ var tr=e.target.closest('.fc-row'); dragIdx=[].indexOf.call(tbody.children,tr); e.dataTransfer.effectAllowed='move'; });
  tbody.addEventListener('dragover', function(e){ e.preventDefault(); });
  tbody.addEventListener('drop', function(e){
    e.preventDefault();
    var tr=e.target.closest('.fc-row'); if(!tr||dragIdx===null) return;
    var to=[].indexOf.call(tbody.children,tr);
    var m=steps.splice(dragIdx,1)[0]; steps.splice(to,0,m); dragIdx=null; render();
  });

  // init mermaid then first render
  window.addEventListener('load', function(){
    if (window.mermaid){
      try {
        window.mermaid.initialize({ startOnLoad:false, securityLevel:'loose', flowchart:{ htmlLabels:true, useMaxWidth:true }, theme: ({$isDark}) ? 'dark' : 'default' });
        mermaidReady = true;
      } catch(e){}
    }
    doPreview();
  });

  render();
})();
JS);
?>

<style>
.fc-steps td { vertical-align: top; }
.fc-preview svg { max-width: 100%; height: auto; }
.fc-preview-card { position: sticky; top: 1rem; }
.fc-legend-item { display: inline-flex; align-items: center; white-space: nowrap; }
.fc-swatch { display:inline-block; width:12px; height:12px; border-radius:3px; border:1px solid; margin-right:4px; }
</style>
