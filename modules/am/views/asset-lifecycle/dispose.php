<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\AppHelper;
use app\widgets\datepicker\DatepickerThai;

/** @var app\modules\am\models\Asset|null $asset */

$this->title = 'จำหน่ายครุภัณฑ์';
$this->params['breadcrumbs'][] = ['label' => 'จัดการทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = ['label' => 'ครุภัณฑ์', 'url' => ['/am/equip/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid px-2 px-md-3 pb-3">
    <div class="row g-3">
        <div class="col-12">
            <h4 class="fw-semibold mb-0"><?= Html::encode($this->title) ?></h4>
        </div>
    </div>

    <?php if (!$asset): ?>
    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted">กรอกหมายเลขครุภัณฑ์ที่ต้องการจำหน่าย</p>
                    <form method="get" action="<?= Url::to(['dispose']) ?>" class="row g-2">
                        <div class="col-auto">
                            <input type="text" name="code" class="form-control" placeholder="หมายเลขครุภัณฑ์" required>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">ค้นหา</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom">
                    <h6 class="mb-0">ครุภัณฑ์: <?= Html::encode($asset->code) ?> — <?= Html::encode($asset->AssetitemName() ?: $asset->asset_name) ?></h6>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= Url::to(['dispose']) ?>">
                        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                        <input type="hidden" name="asset_id" value="<?= (int) $asset->id ?>">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">วิธีจำหน่าย</label>
                                <input type="text" name="disposal_method" class="form-control" placeholder="เช่น ขายทอดตลาด, โอนออก, ชำรุด" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">วันที่จำหน่าย</label>
                                <?= DatepickerThai::widget(['name' => 'disposal_date', 'value' => AppHelper::DateFormDb(date('Y-m-d'))]) ?>
                            </div>
                            <div class="col-12">
                                <label class="form-label">หมายเหตุ</label>
                                <textarea name="remark" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-danger">บันทึกจำหน่าย</button>
                                <?= Html::a('ยกเลิก', ['/am/equip/view-asset', 'id' => $asset->id], ['class' => 'btn btn-outline-secondary']) ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
