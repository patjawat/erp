<?php

use app\components\AppHelper;
use app\components\widgets\DataSummaryWidget;
use app\components\ThaiDateHelper;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;

/** @var yii\data\ActiveDataProvider $provider */
$this->title = 'รับผ้า — รอบเก็บผ้าซักฟอก';
$canManage = Yii::$app->user->can('laundry.manage');
?>
<div class="container-fluid py-3">
    <?= $this->render('../_nav', ['active' => 'collection']) ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 fw-bold mb-0"><i class="bi bi-basket3 me-2"></i>รับผ้า</h1>
        <div class="text-body-secondary small">ออกเก็บผ้าใช้แล้วตามหน่วยงาน → ชั่งแยกผ้าเปื้อน/ผ้าติดเชื้อเมื่อกลับโรงซัก</div>
    </div>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger d-flex align-items-center"><i class="bi bi-exclamation-triangle me-2"></i><?= Html::encode(Yii::$app->session->getFlash('error')) ?></div>
    <?php endif; ?>
    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success d-flex align-items-center"><i class="bi bi-check-circle me-2"></i><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
    <?php endif; ?>

    <?php if ($canManage): ?>
        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body">
                <h2 class="h6 fw-semibold mb-3"><i class="bi bi-plus-circle me-2"></i>เปิดรอบเก็บใหม่</h2>
                <?= Html::beginForm(['create'], 'post', ['class' => 'row g-3 align-items-end']) ?>
                    <div class="col-12 col-sm-4 col-lg-3">
                        <label class="form-label">วันที่ออกเก็บ</label>
                        <?= DatepickerThai::widget([
                            'name' => 'collection_date',
                            'value' => AppHelper::convertToThai(date('Y-m-d')),
                            'options' => ['id' => 'collection_date', 'class' => 'form-control', 'autocomplete' => 'off', 'placeholder' => 'วว/ดด/พ.ศ.', 'required' => true],
                        ]) ?>
                    </div>
                    <div class="col-12 col-sm-5 col-lg-6">
                        <label class="form-label">หมายเหตุ</label>
                        <?= Html::textInput('note', '', ['class' => 'form-control', 'maxlength' => 500, 'placeholder' => 'เช่น รอบเช้า / ผู้ออกเก็บ']) ?>
                    </div>
                    <div class="col-12 col-sm-3 col-lg-3">
                        <?= Html::submitButton('<i class="bi bi-box-arrow-in-down me-1"></i>เปิดรอบเก็บ', ['class' => 'btn btn-primary w-100']) ?>
                    </div>
                <?= Html::endForm() ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-body px-4 py-3"><h2 class="h6 fw-semibold mb-0"><i class="bi bi-list-ul me-2"></i>รายการรอบเก็บ</h2></div>
        <div class="card-body p-0"><div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light"><tr><th class="ps-4">เลขรอบ</th><th>วันที่เก็บ</th><th>สถานะ</th><th class="text-end pe-4">รายละเอียด</th></tr></thead>
                <tbody>
                <?php foreach ($provider->getModels() as $round): ?>
                    <tr>
                        <td class="ps-4 fw-semibold"><?= Html::encode($round->round_no) ?></td>
                        <td><?= Html::encode(ThaiDateHelper::formatThaiDate($round->collection_date)) ?></td>
                        <td>
                            <?php if ($round->status === 'CONFIRMED'): ?>
                                <span class="badge text-bg-success"><i class="bi bi-check-circle me-1"></i>ตรวจรับแล้ว</span>
                            <?php else: ?>
                                <span class="badge text-bg-warning"><i class="bi bi-pencil me-1"></i>กำลังบันทึก</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end pe-4"><?= Html::a('<i class="bi bi-box-arrow-up-right me-1"></i>เปิด', ['view', 'id' => $round->id], ['class' => 'btn btn-sm btn-outline-primary']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$provider->getModels()): ?><tr><td colspan="4" class="text-center text-body-secondary py-4"><i class="bi bi-inbox me-1"></i>ยังไม่มีรอบเก็บผ้า</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div></div>
        <div class="card-footer bg-body border-top py-3 px-4">
            <?= DataSummaryWidget::widget(['dataProvider' => $provider, 'pagerOptions' => []]) ?>
        </div>
    </div>
</div>
