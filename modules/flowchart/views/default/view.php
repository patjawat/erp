<?php

use app\modules\flowchart\components\FlowchartDocRenderer;
use app\modules\flowchart\components\MermaidBuilder;
use app\modules\flowchart\models\Flowchart;
use yii\helpers\Html;
use yii\helpers\Json;

/** @var yii\web\View $this */
/** @var Flowchart $model */

$this->title = $model->title;
$this->registerJsFile('@web/vendor/mermaid/mermaid.min.js', ['position' => \yii\web\View::POS_HEAD]);

$mermaid = MermaidBuilder::build($model);
$colorMap = MermaidBuilder::actorColorMap($model->steps);
$statusTone = $model->isPublished() ? 'success' : 'secondary';
$hasSteps = !empty($model->steps);
?>
<?php $this->beginBlock('page-title'); ?>ผังกระบวนการ<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?><?= Html::encode($model->code ? $model->code . ' · ' : '') ?><?= Html::encode($model->title) ?><?php $this->endBlock(); ?>

<div class="fc-view">
    <!-- แถบเครื่องมือ -->
    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="badge rounded-pill bg-<?= $statusTone ?>-subtle text-<?= $statusTone ?>-emphasis"><?= Html::encode($model->statusLabel()) ?></span>
            <?php if ($model->categoryLabel()): ?>
                <span class="badge rounded-pill bg-body-secondary text-body-secondary"><?= Html::encode($model->categoryLabel()) ?></span>
            <?php endif; ?>
            <?php if ($model->unitName() !== ''): ?>
                <span class="small text-muted"><i class="bi bi-diagram-2 me-1"></i><?= Html::encode($model->unitName()) ?></span>
            <?php endif; ?>
            <span class="small text-muted"><i class="bi bi-list-ol me-1"></i><?= count($model->steps) ?> ขั้นตอน</span>
            <span class="small text-muted"><i class="bi bi-calendar3 me-1"></i>ปีงบ <?= $model->budget_year ?: '-' ?></span>
            <span class="small text-muted"><i class="bi bi-person me-1"></i><?= Html::encode($model->ownerName) ?></span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?= Html::a('<i class="bi bi-pencil-square me-1"></i>แก้ไขขั้นตอน', ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-primary rounded-pill']) ?>
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" id="fc-dl-png"><i class="bi bi-image me-1"></i>ดาวน์โหลด PNG</button>
            <?= Html::a('<i class="bi bi-printer me-1"></i>พิมพ์เอกสาร', ['print', 'id' => $model->id], ['class' => 'btn btn-sm btn-success rounded-pill', 'target' => '_blank']) ?>
            <?= Html::a('<i class="bi bi-arrow-left me-1"></i>กลับคลัง', ['index'], ['class' => 'btn btn-sm btn-outline-secondary rounded-pill']) ?>
        </div>
    </div>

    <?php if (trim((string) $model->description) !== ''): ?>
        <div class="alert alert-light border small mb-3"><i class="bi bi-info-circle me-1"></i><?= nl2br(Html::encode($model->description)) ?></div>
    <?php endif; ?>

    <?php if (!$hasSteps): ?>
        <div class="text-center text-muted py-5 border rounded-3 bg-body-tertiary">
            <i class="bi bi-diagram-3 d-block mb-2" style="font-size:2.5rem;"></i>
            <p class="mb-1">ยังไม่มีขั้นตอนในผังนี้</p>
            <p class="small mb-3">กด "แก้ไขขั้นตอน" เพื่อเริ่มป้อนกระบวนการ</p>
            <?= Html::a('<i class="bi bi-pencil-square me-1"></i>ป้อนขั้นตอน', ['update', 'id' => $model->id], ['class' => 'btn btn-primary rounded-pill px-4']) ?>
        </div>
    <?php else: ?>
    <div class="row g-3">
        <!-- ผัง -->
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-body fw-semibold"><i class="bi bi-diagram-3 me-1"></i> ผังกระบวนการ</div>
                <div class="card-body">
                    <div id="fc-view-diagram" class="text-center"></div>
                    <?php if ($colorMap): ?>
                        <div class="fc-legend small mt-2 border-top pt-2">
                            <span class="text-muted me-2">ผู้รับผิดชอบ:</span>
                            <?php foreach ($colorMap as $actor => $c): ?>
                                <span class="fc-legend-item me-2">
                                    <span class="fc-swatch" style="background:<?= $c['fill'] ?>;border-color:<?= $c['stroke'] ?>"></span><?= Html::encode($actor) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- ตารางกระบวนการ -->
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-body fw-semibold"><i class="bi bi-table me-1"></i> ตารางกระบวนการ (เอกสาร)</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <?= FlowchartDocRenderer::procedureTableHtml($model) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if ($hasSteps): ?>
<?php
$mermaidJson = Json::encode($mermaid);
$fileName = Json::encode(($model->code ?: 'flowchart') . '.png');
$isDark = 'document.documentElement.getAttribute("data-bs-theme")==="dark"';
$this->registerJs(<<<JS
(function(){
  var code = {$mermaidJson};
  var el = document.getElementById('fc-view-diagram');
  window.addEventListener('load', async function(){
    if (!window.mermaid){ el.innerHTML='<div class="text-muted small py-3">โหลดตัวเรนเดอร์ผังไม่ได้</div>'; return; }
    try {
      window.mermaid.initialize({ startOnLoad:false, securityLevel:'loose', flowchart:{ htmlLabels:true, useMaxWidth:true }, theme: ({$isDark}) ? 'dark' : 'default' });
      var out = await window.mermaid.render('fcview', code);
      el.innerHTML = out.svg;
    } catch(e){
      el.innerHTML = '<div class="text-danger small py-3"><i class="bi bi-exclamation-triangle"></i> วาดผังไม่สำเร็จ</div>';
    }
  });

  // ดาวน์โหลด PNG จาก SVG ที่เรนเดอร์แล้ว (ฝั่งเบราว์เซอร์)
  document.getElementById('fc-dl-png').addEventListener('click', function(){
    var svg = el.querySelector('svg'); if (!svg){ return; }
    var clone = svg.cloneNode(true);
    var bbox = svg.getBoundingClientRect();
    var w = Math.max(bbox.width, 320), h = Math.max(bbox.height, 200);
    clone.setAttribute('width', w); clone.setAttribute('height', h);
    var data = new XMLSerializer().serializeToString(clone);
    var img = new Image();
    var svgBlob = new Blob([data], {type:'image/svg+xml;charset=utf-8'});
    var url = URL.createObjectURL(svgBlob);
    img.onload = function(){
      var scale = 2, canvas = document.createElement('canvas');
      canvas.width = w*scale; canvas.height = h*scale;
      var ctx = canvas.getContext('2d');
      ctx.fillStyle = '#ffffff'; ctx.fillRect(0,0,canvas.width,canvas.height);
      ctx.scale(scale, scale); ctx.drawImage(img, 0, 0, w, h);
      URL.revokeObjectURL(url);
      canvas.toBlob(function(blob){
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob); a.download = {$fileName};
        document.body.appendChild(a); a.click(); a.remove();
      }, 'image/png');
    };
    img.onerror = function(){ URL.revokeObjectURL(url); };
    img.src = url;
  });
})();
JS);
?>
<?php endif; ?>

<style>
#fc-view-diagram svg, .fc-preview svg { max-width: 100%; height: auto; }
.fc-legend-item { display: inline-flex; align-items: center; white-space: nowrap; }
.fc-swatch { display:inline-block; width:12px; height:12px; border-radius:3px; border:1px solid; margin-right:4px; }
.fc-proc-table th { background: var(--bs-tertiary-bg); font-size:.82rem; }
.fc-proc-table td { font-size:.85rem; }
.fc-sym svg { color: var(--bs-secondary-color); }
</style>
