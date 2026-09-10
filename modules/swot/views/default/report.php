<?php

use app\modules\swot\models\SwotBoard;
use app\modules\swot\models\SwotNote;
use app\components\AppHelper;
use yii\helpers\Html;
use yii\helpers\Json;

/** @var yii\web\View $this */
/** @var app\modules\swot\models\SwotBoard $model */
/** @var array $notesByQuadrant */
/** @var array $weightByQuadrant */
/** @var array $weightByCategory */

$isSoar = $model->isSoar();
$this->title = $model->title . ' — รายงาน';
$quadInfo = SwotNote::QUADRANT_INFO;

// เตรียมข้อมูลกราฟ
$quadLabels = [];
$quadValues = [];
foreach ($weightByQuadrant as $q => $sum) {
    $quadLabels[] = $quadInfo[$q]['code'] . ' · ' . $quadInfo[$q]['short'];
    $quadValues[] = (int) $sum;
}
$catLabels = [];
$catValues = [];
foreach (array_slice($weightByCategory, 0, 8, true) as $cat => $sum) {
    $catLabels[] = $cat;
    $catValues[] = (int) $sum;
}

// กลยุทธ์
$cellMeta = [
    'so' => 'SO · กลยุทธ์เชิงรุก', 'wo' => 'WO · กลยุทธ์เชิงปรับปรุง',
    'st' => 'ST · กลยุทธ์เชิงป้องกัน', 'wt' => 'WT · กลยุทธ์เชิงตั้งรับ',
];
$towsMatrix = is_array($model->tows_matrix) ? $model->tows_matrix : [];
$soarInitiatives = is_array($model->soar_matrix['initiatives'] ?? null) ? $model->soar_matrix['initiatives'] : [];
$tfLabels = ['quick-win' => 'ทำได้ทันที', 'short-term' => 'ระยะสั้น', 'medium-term' => 'ระยะกลาง', 'long-term' => 'ระยะยาว'];
$prLabels = ['high' => 'สูง', 'medium' => 'กลาง', 'low' => 'ต่ำ'];

$chartData = Json::encode(['quadLabels' => $quadLabels, 'quadValues' => $quadValues, 'catLabels' => $catLabels, 'catValues' => $catValues, 'isSoar' => $isSoar]);
$this->registerJs("(function(){ if(typeof ApexCharts==='undefined'){return;} var d=$chartData;
  var q=document.getElementById('rptRadar');
  if(q){ new ApexCharts(q,{chart:{type:'radar',height:340,toolbar:{show:false}},series:[{name:'น้ำหนักรวม',data:d.quadValues}],labels:d.quadLabels,colors:[d.isSoar?'#0d6efd':'#198754'],fill:{opacity:.25},stroke:{width:2},markers:{size:4}}).render(); }
  var c=document.getElementById('rptCat');
  if(c && d.catValues.length){ new ApexCharts(c,{chart:{type:'bar',height:340,toolbar:{show:false}},series:[{name:'น้ำหนักรวม',data:d.catValues}],plotOptions:{bar:{horizontal:true,borderRadius:3}},colors:['#0d9488'],dataLabels:{enabled:true},xaxis:{categories:d.catLabels}}).render(); }
})();");
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($model->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>รายงานสรุปผลการวิเคราะห์<?php $this->endBlock(); ?>

<div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3 no-print">
    <?= Html::a('<i class="bi bi-arrow-left"></i> กลับกระดาน', ['board', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-secondary rounded-pill px-3']) ?>
    <div class="d-flex gap-2">
        <?= Html::a('<i class="bi bi-file-earmark-excel me-1"></i>Excel', ['export', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-success rounded-pill px-3']) ?>
        <?= Html::a('<i class="bi bi-printer me-1"></i>พิมพ์ / บันทึก PDF', 'javascript:window.print()', ['class' => 'btn btn-sm btn-primary rounded-pill px-3']) ?>
    </div>
</div>

<div id="swotReport" class="swot-report bg-white p-4">

    <!-- หัวรายงาน -->
    <div class="text-center border-bottom pb-3 mb-4">
        <div class="badge rounded-pill text-bg-<?= $isSoar ? 'primary' : 'success' ?> mb-2"><?= SwotBoard::frameworkLabel($model->framework) ?> Analysis</div>
        <h4 class="fw-bold mb-1"><?= Html::encode($model->title) ?></h4>
        <?php if ($model->objective): ?><p class="text-muted mb-2"><?= Html::encode($model->objective) ?></p><?php endif; ?>
        <div class="small text-muted">
            ปีงบประมาณ <?= $model->budget_year ?: '-' ?> ·
            ผู้จัดทำ <?= Html::encode($model->ownerName) ?> ·
            พิมพ์เมื่อ <?= AppHelper::convertToThai(date('Y-m-d')) ?>
        </div>
    </div>

    <?php $ai = is_array($model->ai_analysis) ? $model->ai_analysis : null; ?>
    <?php if ($ai): $sc = max(0, min(100, (int) ($ai['healthScore'] ?? 0))); $tone = $sc >= 70 ? 'success' : ($sc >= 40 ? 'warning' : 'danger'); ?>
    <div class="border rounded-3 p-3 mb-4 bg-light">
        <h6 class="fw-bold mb-2"><i class="bi bi-robot text-primary me-1"></i>บทวิเคราะห์โดย AI</h6>
        <div class="d-flex align-items-center gap-3 mb-2">
            <div class="text-center"><div class="fs-4 fw-bold text-<?= $tone ?>"><?= $sc ?></div><div class="small text-muted">ความพร้อม</div></div>
            <div class="flex-grow-1"><?= Html::encode((string) ($ai['summary'] ?? '')) ?></div>
        </div>
        <?php if (!empty($ai['recommendations'])): ?>
            <div class="small fw-semibold mt-2">ข้อเสนอแนะ:</div>
            <ul class="small mb-0"><?php foreach ($ai['recommendations'] as $r): ?><li><?= Html::encode($r) ?></li><?php endforeach; ?></ul>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- 1) ปัจจัย -->
    <h6 class="fw-bold mb-2"><i class="bi bi-1-circle-fill text-primary me-1"></i>ปัจจัยจากการวิเคราะห์</h6>
    <div class="row g-2 mb-4">
        <?php foreach ($model->quadrants() as $q):
            $info = $quadInfo[$q];
            $notes = $notesByQuadrant[$q] ?? [];
        ?>
        <div class="col-6">
            <div class="border rounded-3 h-100">
                <div class="px-2 py-1 text-white swot-tone-<?= $info['tone'] ?> rounded-top">
                    <strong><?= $info['code'] ?></strong> <?= $info['short'] ?> <span class="opacity-75">(<?= count($notes) ?>)</span>
                </div>
                <ul class="small mb-0 py-2 ps-4 pe-2">
                    <?php foreach ($notes as $n): ?>
                        <li><?= Html::encode($n->content) ?> <span class="text-muted">[<?= $n->weight ?>]</span></li>
                    <?php endforeach; ?>
                    <?php if (empty($notes)): ?><li class="text-muted fst-italic">— ไม่มี —</li><?php endif; ?>
                </ul>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- 2) กราฟ -->
    <h6 class="fw-bold mb-2"><i class="bi bi-2-circle-fill text-primary me-1"></i>สรุปน้ำหนัก</h6>
    <div class="row g-3 mb-4">
        <div class="col-6"><div class="border rounded-3 p-2"><div class="small fw-semibold mb-1">ภาพรวม 4 ด้าน</div><div id="rptRadar"></div></div></div>
        <div class="col-6"><div class="border rounded-3 p-2"><div class="small fw-semibold mb-1">ตามหมวดหมู่</div>
            <?php if ($catValues): ?><div id="rptCat"></div><?php else: ?><div class="text-muted small py-4 text-center">ยังไม่ได้จัดหมวดหมู่</div><?php endif; ?>
        </div></div>
    </div>

    <!-- 3) กลยุทธ์ -->
    <?php if ($isSoar): ?>
    <h6 class="fw-bold mb-2"><i class="bi bi-3-circle-fill text-primary me-1"></i>ริเริ่มเชิงกลยุทธ์ (S+O → A → R)</h6>
    <?php if (empty($soarInitiatives)): ?>
        <div class="text-muted small fst-italic">— ยังไม่มีริเริ่มเชิงกลยุทธ์ —</div>
    <?php else: ?>
        <ol class="small ps-3">
            <?php foreach ($soarInitiatives as $it): ?>
                <li class="mb-2">
                    <strong><?= Html::encode($it['title'] ?? '') ?></strong>
                    <?php if (!empty($it['timeframe'])): ?><span class="badge text-bg-light border"><?= $tfLabels[$it['timeframe']] ?? '' ?></span><?php endif; ?>
                    <?php if (!empty($it['description'])): ?><div class="text-muted">S+O: <?= Html::encode($it['description']) ?></div><?php endif; ?>
                    <?php if (!empty($it['aspiration'])): ?><div>🏁 มุ่งสู่: <?= Html::encode($it['aspiration']) ?></div><?php endif; ?>
                    <?php if (!empty($it['metric'])): ?><div class="text-success">🎯 ผลลัพธ์: <?= Html::encode($it['metric']) ?></div><?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
    <?php else: ?>
    <h6 class="fw-bold mb-2"><i class="bi bi-3-circle-fill text-primary me-1"></i>กลยุทธ์ (TOWS Matrix)</h6>
    <div class="row g-2">
        <?php foreach ($cellMeta as $key => $label):
            $items = (array) ($towsMatrix[$key] ?? []);
        ?>
        <div class="col-6">
            <div class="border rounded-3 h-100 p-2">
                <div class="fw-semibold small mb-1"><?= $label ?></div>
                <?php if (empty($items)): ?>
                    <div class="text-muted small fst-italic">— ยังไม่มีกลยุทธ์ —</div>
                <?php else: ?>
                    <ol class="small mb-0 ps-3">
                        <?php foreach ($items as $it): ?>
                            <li class="mb-1">
                                <strong><?= Html::encode($it['title'] ?? '') ?></strong>
                                <?php if (!empty($it['timeframe'])): ?><span class="badge text-bg-light border"><?= $tfLabels[$it['timeframe']] ?? '' ?></span><?php endif; ?>
                                <?php if (!empty($it['description'])): ?><div class="text-muted"><?= Html::encode($it['description']) ?></div><?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php $this->registerCss(<<<CSS
.swot-tone-success { background: #198754; } .swot-tone-danger { background: #dc3545; }
.swot-tone-info { background: #0dcaf0; } .swot-tone-warning { background: #fd7e14; }
.swot-tone-primary { background: #0d6efd; } .swot-tone-teal { background: #0d9488; }
@media print {
    body * { visibility: hidden !important; }
    #swotReport, #swotReport * { visibility: visible !important; }
    #swotReport { position: absolute; left: 0; top: 0; width: 100%; padding: 0 !important; }
    .no-print { display: none !important; }
    @page { margin: 1cm; }
}
CSS);
?>
