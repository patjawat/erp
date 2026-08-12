<?php

use yii\helpers\Html;
use app\components\AppHelper;
use app\modules\purchase\models\Tor;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\Tor $model */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'เขียน TOR', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$badge = Tor::statusBadge($model->status);
$prices = $model->prices;
$sources = $model->countPriceSources();

/** เนื้อความที่เก็บเป็น HTML ผ่าน HtmlPurifier ตอนบันทึกแล้ว จึงแสดงตรง ๆ ได้ */
$html = function ($value) {
    return trim(strip_tags((string) $value)) === ''
        ? '<span class="text-muted">—</span>'
        : $value;
};
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
    <i class="bi bi-file-earmark-text"></i> <?= Html::encode($this->title) ?>
    <span class="badge text-bg-<?= $badge['color'] ?> align-middle"><?= $badge['label'] ?></span>
</h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
เลขที่ <?= Html::encode($model->doc_no ?: '—') ?> · ปีงบประมาณ <?= $model->thai_year ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<div class="d-flex flex-wrap gap-2">
    <?= Html::a('<i class="bi bi-arrow-left me-1"></i>ทะเบียน TOR', ['index'], ['class' => 'btn btn-sm btn-outline-secondary rounded-pill px-3']) ?>
    <?= Html::a('<i class="bi bi-file-earmark-word me-1"></i>ส่งออก Word', ['word', 'id' => $model->id], [
        'class' => 'btn btn-sm btn-outline-primary rounded-pill px-3',
        'target' => '_blank',
    ]) ?>
    <?= Html::a('<i class="bi bi-pencil me-1"></i>แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-primary rounded-pill px-3']) ?>
    <?= Html::a('<i class="bi bi-trash me-1"></i>ลบ', ['delete', 'id' => $model->id], [
        'class' => 'btn btn-sm btn-outline-danger rounded-pill px-3',
        'data' => [
            'confirm' => 'ยืนยันการลบ TOR "' . $model->title . '" ?',
            'method' => 'post',
        ],
    ]) ?>
</div>
<?php $this->endBlock(); ?>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">ข้อ 1 ข้อมูลทั่วไป</h6></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">ประเภทพัสดุ</dt>
                    <dd class="col-sm-8"><?= Html::encode($model->assetTypeName()) ?></dd>

                    <dt class="col-sm-4">วิธีจัดซื้อจัดจ้าง</dt>
                    <dd class="col-sm-8"><?= Html::encode($model->purchaseMethodName()) ?></dd>

                    <dt class="col-sm-4">จำนวน</dt>
                    <dd class="col-sm-8">
                        <?= $model->qty !== null ? rtrim(rtrim(number_format((float) $model->qty, 2), '0'), '.') : '—' ?>
                        <?= Html::encode($model->unit_name ?: '') ?>
                    </dd>

                    <dt class="col-sm-4">วงเงินงบประมาณ</dt>
                    <dd class="col-sm-8"><?= number_format((float) $model->budget, 2) ?> บาท</dd>

                    <dt class="col-sm-4">วันที่จัดทำ</dt>
                    <dd class="col-sm-8"><?= $model->tor_date ? AppHelper::convertToThai($model->tor_date) : '—' ?></dd>

                    <dt class="col-sm-4">เลขที่โครงการ e-GP</dt>
                    <dd class="col-sm-8"><?= Html::encode($model->egp_no ?: '—') ?></dd>

                    <dt class="col-sm-4">ผู้จัดทำ</dt>
                    <dd class="col-sm-8"><?= Html::encode($model->empName()) ?></dd>

                    <dt class="col-sm-4">วัตถุประสงค์</dt>
                    <dd class="col-sm-8"><?= $html($model->purpose) ?></dd>
                </dl>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">ข้อ 2 คุณลักษณะเฉพาะ</h6></div>
            <div class="card-body"><?= $html($model->spec) ?></div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">ข้อ 3 มาตรฐานและการรับประกัน</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="fw-semibold small text-muted mb-1">มาตรฐาน/การรับรองคุณภาพ</div>
                    <?= $html($model->standard) ?>
                </div>
                <div>
                    <div class="fw-semibold small text-muted mb-1">เงื่อนไขการรับประกัน</div>
                    <?= $html($model->warranty) ?>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">ข้อ 4 เงื่อนไขการส่งมอบและการชำระเงิน</h6></div>
            <div class="card-body">
                <dl class="row mb-3">
                    <dt class="col-sm-4">ระยะเวลาส่งมอบ</dt>
                    <dd class="col-sm-8"><?= $model->delivery_days ? $model->delivery_days . ' วันทำการ' : '—' ?></dd>
                    <dt class="col-sm-4">สถานที่ส่งมอบ</dt>
                    <dd class="col-sm-8"><?= Html::encode($model->delivery_place ?: '—') ?></dd>
                </dl>
                <div class="mb-3">
                    <div class="fw-semibold small text-muted mb-1">เงื่อนไขการส่งมอบ</div>
                    <?= $html($model->delivery_term) ?>
                </div>
                <div class="mb-3">
                    <div class="fw-semibold small text-muted mb-1">เงื่อนไขการชำระเงิน</div>
                    <?= $html($model->payment_term) ?>
                </div>
                <div>
                    <div class="fw-semibold small text-muted mb-1">คุณสมบัติผู้เสนอราคา</div>
                    <?= $html($model->vendor_qualification) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">ข้อ 5 ราคากลาง</h6></div>
            <div class="card-body">
                <?php if ($sources < 3): ?>
                    <div class="alert alert-warning py-2 small">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        สืบราคาแล้ว <?= $sources ?> แหล่ง ยังไม่ครบ 3 แหล่งตามที่ระเบียบกำหนด
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:36px" class="text-center">ที่</th>
                                <th>ผู้เสนอราคา</th>
                                <th class="text-end">ราคา</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prices as $i => $p): ?>
                                <tr>
                                    <td class="text-center text-muted"><?= $i + 1 ?></td>
                                    <td>
                                        <?= Html::encode($p->displayName()) ?>
                                        <?php if ($p->detail): ?>
                                            <div class="small text-muted"><?= Html::encode($p->detail) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end"><?= number_format((float) $p->price, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (!$prices): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">ยังไม่ได้บันทึกผลการสืบราคา</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2" class="text-end">ราคากลาง</th>
                                <th class="text-end"><?= number_format((float) $model->mid_price, 2) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <dl class="row mb-0 small">
                    <dt class="col-5">วิธีกำหนดราคากลาง</dt>
                    <dd class="col-7"><?= Html::encode($model->mid_method ?: '—') ?></dd>
                    <?php if ($model->mid_note): ?>
                        <dt class="col-5">หมายเหตุ</dt>
                        <dd class="col-7"><?= Html::encode($model->mid_note) ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>
    </div>
</div>
