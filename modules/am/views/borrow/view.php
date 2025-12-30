<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\components\AppHelper;

/* @var $this yii\web\View */
/* @var $model app\models\BorrowLog */

$this->title = 'รายละเอียดการยืม';
$this->params['breadcrumbs'][] = ['label' => 'รายการยืม-คืน', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="borrow-log-view">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-file-earmark-text text-primary"></i> <?= Html::encode($this->title) ?></h4>
        <div class="action-buttons">
            <?php if (empty($model->data_json['actual_date'])): ?>
                <?= Html::a('<i class="bi bi-arrow-return-left"></i> รับคืนเครื่องมือ', ['borrow-return', 'id' => $model->id], [
                    'class' => 'btn btn-success rounded-pill px-4 shadow-sm open-modal',
                    'data-size' => 'modal-lg'
                ]) ?>
            <?php else: ?>
                <?php Html::a('<i class="bi bi-printer"></i> พิมพ์ใบรับคืน', ['print-receipt', 'id' => $model->id], [
                    'class' => 'btn btn-outline-secondary rounded-pill px-4',
                    'target' => '_blank'
                ]) ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold border-bottom">
                    <i class="bi bi-clock-history me-1 text-info"></i> ข้อมูลบันทึกการยืม
                </div>
                <div class="card-body p-0">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-hover mb-0'],
                        'attributes' => [
                            // [
                            //     'attribute' => 'ref',
                            //     'label' => 'เลขที่อ้างอิง',
                            //     'contentOptions' => ['class' => 'fw-bold text-primary'],
                            // ],
                            [
                                'label' => 'ผู้ยืม / หน่วยงาน',
                                'format' => 'raw',
                                'value' => function($model) {
                                    return $model->employee->getAvatar(false);
                                }
                            ],
                            [
                                'attribute' => 'date_start',
                                'label' => 'วันที่เริ่มยืม',
                                'value' => AppHelper::convertToThai($model->date_start),
                            ],
                            [
                                'attribute' => 'date_end',
                                'label' => 'กำหนดคืน',
                                'contentOptions' => ['class' => 'text-danger fw-bold'],
                                'value' => AppHelper::convertToThai($model->date_end),
                            ],
                            [
                                'label' => 'วันที่คืนจริง',
                                'value' => $model->data_json['actual_date'] ? AppHelper::convertToThai($model->data_json['actual_date']) : '(ยังไม่คืน)',
                                'contentOptions' => ['class' => $model->data_json['actual_date'] ? 'text-success' : 'text-muted italic'],
                            ],
                            [
                                'format' => 'raw',
                                'label' => 'ผู้รับคืน',
                                'value' => $model->staff?->getAvatar(false) ?? '-' // Relation ไปยัง User/Employee
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light fw-bold">
                    <i class="bi bi-box-seam me-1 text-primary"></i> ครุภัณฑ์ที่ถูกยืม
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-microscope display-4 text-secondary"></i>
                    </div>
                    <h5 class="fw-bold mb-1"><?= Html::encode($model->asset->asset_name) ?></h5>
                    <p class="text-muted small">รหัส: <?= Html::encode($model->asset->code) ?></p>
                    <hr>
                    <div class="row text-start small">
                        <div class="col-6 text-muted">Serial No:</div>
                        <div class="col-6 fw-bold"><?= Html::encode($model->asset->data_json['serial_number'] ?? '-') ?></div>
                        <div class="col-6 text-muted mt-2">สภาพหลังใช้งาน:</div>
                        <div class="col-6 mt-2">
                            <?= $model->getReturnConditionLabel() // ฟังก์ชันแสดง Badge ปกติ/ชำรุด ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm border-start border-4 border-info">
                <div class="card-body">
                    <label class="fw-bold small text-muted text-uppercase">หมายเหตุ / ปัญหาที่พบ</label>
                    <p class="mb-0 mt-1"><?= nl2br(Html::encode($model->data_json['note'] ?: 'ไม่มีข้อมูลหมายเหตุ')) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>