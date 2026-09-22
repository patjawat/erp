<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $categories */
/** @var array $registers */

$this->title = 'ทะเบียนคุมงานการเงิน';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = $this->title;

$statusMeta = [
    'ready'  => ['label' => 'พร้อมพัฒนา', 'badge' => 'bg-success-subtle text-success-emphasis', 'dot' => 'text-success'],
    'extend' => ['label' => 'ต่อยอด', 'badge' => 'bg-warning-subtle text-warning-emphasis', 'dot' => 'text-warning'],
    'todo'   => ['label' => 'ยังไม่มีข้อมูล', 'badge' => 'bg-danger-subtle text-danger-emphasis', 'dot' => 'text-danger'],
];

// จัดกลุ่มทะเบียนตามหมวด
$byCat = [];
foreach ($registers as $r) {
    $byCat[$r['cat']][] = $r;
}

$counts = ['ready' => 0, 'extend' => 0, 'todo' => 0];
foreach ($registers as $r) {
    $counts[$r['status']]++;
}

$this->beginBlock('page-title');
echo '<div class="d-flex align-items-center gap-2"><i class="bi bi-journals fs-4" aria-hidden="true"></i><h4 class="mb-0">' . Html::encode($this->title) . '</h4></div>';
$this->endBlock();
$this->beginBlock('sub-title');
echo 'ทะเบียนคุมที่งานการเงินต้องจัดทำ 18 เล่ม 5 หมวด — ฉายจากธุรกรรมในระบบผ่านรูปแบบทะเบียนมาตรฐาน';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'register']);
$this->endBlock();
?>

<div class="row g-3 mb-4">
    <?php foreach ([
        ['label' => 'พร้อมพัฒนา (มีข้อมูลแล้ว)', 'value' => $counts['ready'], 'class' => 'text-success-emphasis', 'icon' => 'bi-check-circle'],
        ['label' => 'ต่อยอดจากของเดิม', 'value' => $counts['extend'], 'class' => 'text-warning-emphasis', 'icon' => 'bi-tools'],
        ['label' => 'ยังไม่มีข้อมูลต้นทาง', 'value' => $counts['todo'], 'class' => 'text-danger-emphasis', 'icon' => 'bi-hourglass-split'],
    ] as $summary): ?>
        <div class="col-12 col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body py-3 d-flex align-items-center gap-3">
                    <i class="bi <?= $summary['icon'] ?> fs-2 <?= $summary['class'] ?>" aria-hidden="true"></i>
                    <div>
                        <div class="fs-3 fw-semibold <?= $summary['class'] ?>"><?= (int) $summary['value'] ?> <span class="fs-6 fw-normal text-body-secondary">เล่ม</span></div>
                        <div class="text-body-secondary small"><?= Html::encode($summary['label']) ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php foreach ($categories as $catKey => $cat): ?>
    <?php $rows = $byCat[$catKey] ?? []; if (!$rows) continue; ?>
    <section class="card shadow-sm mb-3" aria-labelledby="cat-<?= Html::encode($catKey) ?>">
        <div class="card-header bg-body d-flex align-items-center gap-2">
            <i class="bi <?= Html::encode($cat['icon']) ?>" aria-hidden="true"></i>
            <h5 class="mb-0" id="cat-<?= Html::encode($catKey) ?>"><?= Html::encode($cat['label']) ?></h5>
        </div>
        <div class="list-group list-group-flush">
            <?php foreach ($rows as $r): ?>
                <?php $meta = $statusMeta[$r['status']]; ?>
                <a href="<?= Url::to(['/finance/register/view', 'key' => $r['key']]) ?>"
                   class="list-group-item list-group-item-action py-3">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div class="min-w-0">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <span class="badge bg-secondary-subtle text-secondary-emphasis font-monospace"><?= Html::encode($r['no']) ?></span>
                                <strong><?= Html::encode($r['label']) ?></strong>
                                <span class="badge <?= $meta['badge'] ?>">
                                    <i class="bi bi-circle-fill <?= $meta['dot'] ?>" style="font-size:.5rem" aria-hidden="true"></i>
                                    <?= Html::encode($meta['label']) ?>
                                </span>
                            </div>
                            <small class="text-body-secondary"><i class="bi bi-database me-1" aria-hidden="true"></i><?= Html::encode($r['source']) ?></small>
                        </div>
                        <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis text-nowrap">เฟส <?= (int) $r['phase'] ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
<?php endforeach; ?>

<p class="text-body-secondary small mt-3">
    <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
    แผนพัฒนาเต็มดูที่ <code>docs/finance/control-registry-plan.md</code> — เฟส 0 กำลังทำ Register Layer กลางสำหรับทะเบียน "พร้อมพัฒนา"
</p>
