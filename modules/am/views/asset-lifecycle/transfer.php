<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\AppHelper;

/** @var app\modules\am\models\Asset|null $asset */
/** @var array $departments */

$this->title = 'โอนย้ายครุภัณฑ์';
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
                    <p class="text-muted">กรอกหมายเลขครุภัณฑ์เพื่อเลือกรายการที่ต้องการโอนย้าย</p>
                    <form method="get" action="<?= Url::to(['transfer']) ?>" class="row g-2">
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
    <?php
    $currentLoc = is_array($asset->data_json) && isset($asset->data_json['location']) ? $asset->data_json['location'] : '';
    $currentDept = $asset->departmentName();
    ?>
    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom">
                    <h6 class="mb-0">ครุภัณฑ์: <?= Html::encode($asset->code) ?> — <?= Html::encode($asset->AssetitemName() ?: $asset->asset_name) ?></h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small">สถานที่ปัจจุบัน: <?= Html::encode($currentLoc ?: '-') ?> | หน่วยงานปัจจุบัน: <?= Html::encode($currentDept ?: '-') ?></p>
                    <form method="post" action="<?= Url::to(['transfer']) ?>">
                        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                        <input type="hidden" name="asset_id" value="<?= (int) $asset->id ?>">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">สถานที่ใหม่</label>
                                <input type="text" name="to_location" class="form-control" placeholder="อาคาร/ห้อง">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">หน่วยงานใหม่</label>
                                <select name="to_department" class="form-select">
                                    <option value="">-- เลือก --</option>
                                    <?php foreach ($departments as $id => $name): ?>
                                        <option value="<?= (int) $id ?>"><?= Html::encode($name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">หมายเหตุ</label>
                                <textarea name="remark" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">บันทึกการโอนย้าย</button>
                                <?= Html::a('ยกเลิก', ['/am/equip/index'], ['class' => 'btn btn-outline-secondary']) ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
