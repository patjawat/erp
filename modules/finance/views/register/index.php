<?php

use app\modules\finance\services\FinanceRegisterService;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $categories */
/** @var array $registers */

$this->title = 'ทะเบียนคุมงานการเงิน';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = $this->title;

// จัดกลุ่มตามหมวด + คิดสถานะจริงจาก builder (พร้อมใช้งานเมื่อมี builder แล้ว)
$byCat = [];
$ready = 0;
$soon = 0;
foreach ($registers as $r) {
    $r['ready'] = FinanceRegisterService::isImplemented($r['key']);
    $byCat[$r['cat']][] = $r;
    $r['ready'] ? $ready++ : $soon++;
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
    <div class="col-12 col-md-6">
        <div class="card h-100 shadow-sm"><div class="card-body py-3 d-flex align-items-center gap-3">
            <i class="bi bi-check-circle fs-2 text-success-emphasis" aria-hidden="true"></i>
            <div><div class="fs-3 fw-semibold text-success-emphasis"><?= (int) $ready ?> <span class="fs-6 fw-normal text-body-secondary">เล่ม</span></div>
            <div class="text-body-secondary small">พร้อมใช้งาน (พิมพ์/Excel ได้)</div></div>
        </div></div>
    </div>
    <?php if ($soon): ?>
    <div class="col-12 col-md-6">
        <div class="card h-100 shadow-sm"><div class="card-body py-3 d-flex align-items-center gap-3">
            <i class="bi bi-hourglass-split fs-2 text-body-secondary" aria-hidden="true"></i>
            <div><div class="fs-3 fw-semibold text-body-secondary"><?= (int) $soon ?> <span class="fs-6 fw-normal text-body-secondary">เล่ม</span></div>
            <div class="text-body-secondary small">เร็ว ๆ นี้</div></div>
        </div></div>
    </div>
    <?php endif; ?>
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
                <a href="<?= Url::to(['/finance/register/view', 'key' => $r['key']]) ?>"
                   class="list-group-item list-group-item-action py-3">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div class="min-w-0">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <span class="badge bg-secondary-subtle text-secondary-emphasis font-monospace"><?= Html::encode($r['no']) ?></span>
                                <strong><?= Html::encode($r['label']) ?></strong>
                            </div>
                            <small class="text-body-secondary"><i class="bi bi-database me-1" aria-hidden="true"></i><?= Html::encode($r['source']) ?></small>
                        </div>
                        <?php if ($r['ready']): ?>
                            <span class="badge rounded-pill bg-success-subtle text-success-emphasis text-nowrap"><i class="bi bi-check2 me-1" aria-hidden="true"></i>พร้อมใช้งาน</span>
                        <?php else: ?>
                            <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis text-nowrap">เร็ว ๆ นี้</span>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
<?php endforeach; ?>

<p class="text-body-secondary small mt-3">
    <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
    ทะเบียนคุมทั้งหมดฉายจากธุรกรรมจริงในระบบ — เปิดแต่ละเล่มเพื่อดู/กรอง/พิมพ์/ส่งออก Excel
</p>
