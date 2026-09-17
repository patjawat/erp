<?php

use app\components\widgets\DataSummaryWidget;
use yii\helpers\Html;

$this->title = 'รอบเก็บผ้าซักฟอก';
$canManage = Yii::$app->user->can('laundry.manage');
?>
<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
        <div>
            <h4 class="fw-bold mb-1"><?= Html::encode($this->title) ?></h4>
            <div class="text-muted">ออกเก็บตามหน่วยงาน แล้วชั่งแยกประเภทเมื่อกลับโรงซัก</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?= Html::a('บัญชีผ้าเป็นชิ้น', ['/laundry/inventory/index'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
            <?= Html::a('รอบซัก–อบ', ['/laundry/processing/index'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
            <?= Html::a('รายงานน้ำหนักรายหน่วยงาน', ['report'], ['class' => 'btn btn-outline-primary rounded-3']) ?>
        </div>
    </div>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger"><?= Html::encode(Yii::$app->session->getFlash('error')) ?></div>
    <?php endif; ?>

    <?php if ($canManage): ?>
        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body">
                <h5 class="fw-semibold">เริ่มรอบเก็บใหม่</h5>
                <?= Html::beginForm(['create'], 'post', ['class' => 'row g-3 align-items-end']) ?>
                    <div class="col-12 col-sm-4 col-lg-3">
                        <label class="form-label">วันที่ออกเก็บ</label>
                        <?= Html::input('date', 'collection_date', date('Y-m-d'), ['class' => 'form-control', 'required' => true]) ?>
                    </div>
                    <div class="col-12 col-sm-5 col-lg-6">
                        <label class="form-label">หมายเหตุ</label>
                        <?= Html::textInput('note', '', ['class' => 'form-control', 'maxlength' => 500]) ?>
                    </div>
                    <div class="col-12 col-sm-3 col-lg-3">
                        <?= Html::submitButton('เปิดรอบเก็บ', ['class' => 'btn btn-primary rounded-3 w-100']) ?>
                    </div>
                <?= Html::endForm() ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-body px-4 py-3"><h5 class="fw-semibold mb-0">รายการรอบเก็บ</h5></div>
        <div class="card-body p-0"><div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th class="ps-4">เลขรอบ</th><th>วันที่เก็บ</th><th>สถานะ</th><th class="text-end pe-4">รายละเอียด</th></tr></thead>
                <tbody>
                <?php foreach ($provider->getModels() as $round): ?>
                    <tr>
                        <td class="ps-4 fw-semibold"><?= Html::encode($round->round_no) ?></td>
                        <td><?= Html::encode($round->collection_date) ?></td>
                        <td><span class="badge <?= $round->status === 'CONFIRMED' ? 'bg-success' : 'bg-warning text-dark' ?>"><?= $round->status === 'CONFIRMED' ? 'ยืนยันแล้ว' : 'กำลังบันทึก' ?></span></td>
                        <td class="text-end pe-4"><?= Html::a('เปิด', ['view', 'id' => $round->id], ['class' => 'btn btn-sm btn-outline-primary rounded-3']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$provider->getModels()): ?><tr><td colspan="4" class="text-center text-muted py-4">ยังไม่มีรอบเก็บผ้า</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div></div>
        <div class="card-footer bg-body border-top py-3 px-4">
            <?= DataSummaryWidget::widget(['dataProvider' => $provider, 'pagerOptions' => []]) ?>
        </div>
    </div>
</div>
