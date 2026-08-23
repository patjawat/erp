<?php
use kartik\select2\Select2;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
$this->title='ผู้แทนคุณภาพ';
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?><?= $this->render('@app/modules/serviceProfile/menu',['active'=>'reviewers']) ?><?php $this->endBlock(); ?>
<div class="row g-3">
    <div class="col-12 col-xl-5">
        <div class="card bg-body border shadow-sm">
            <div class="card-header bg-body-tertiary py-3">
                <h2 class="h6 fw-semibold mb-0">เพิ่มผู้แทนคุณภาพ</h2>
            </div>
            <div class="card-body">
                <?php $form = ActiveForm::begin(); ?>
                <?= $form->field($model, 'owner_id')->widget(Select2::class, [
                    'data' => $ownerOptions,
                    'options' => ['placeholder' => 'ค้นหาหน่วยงานหรือทีมประสาน...'],
                    'pluginOptions' => ['allowClear' => true],
                ])->label('หน่วยงาน / ทีมประสาน') ?>
                <?= $form->field($model, 'employee_id')->widget(Select2::class, [
                    'data' => $employeeOptions,
                    'options' => ['placeholder' => 'ค้นหาชื่อบุคลากร...'],
                    'pluginOptions' => ['allowClear' => true],
                ])->label('ผู้แทนคุณภาพ') ?>
                <?= $form->field($model, 'is_lead')->checkbox(['label' => 'กำหนดเป็นผู้แทนคุณภาพหลัก']) ?>
                <div class="form-text mb-3">แต่ละหน่วยงานหรือทีมประสานมีผู้แทนได้หลายคน แต่มีผู้แทนหลักได้ 1 คน</div>
                <?= Html::submitButton('บันทึกผู้แทนคุณภาพ', ['class' => 'btn btn-primary w-100']) ?>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-7">
        <div class="card bg-body border shadow-sm">
            <div class="card-header bg-body-tertiary py-3">
                <h2 class="h6 fw-semibold mb-0">รายการที่กำหนดแล้ว</h2>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($rows as $row): $owner = $owners[$row->owner_type . ':' . (int) $row->owner_id] ?? null; ?>
                    <div class="list-group-item d-flex justify-content-between gap-3 py-3">
                        <div>
                            <div class="fw-semibold">
                                <?= Html::encode($row->employee?->fullname() ?? ('บุคลากร #' . $row->employee_id)) ?>
                                <?php if ($row->is_lead): ?><span class="badge bg-primary-subtle text-primary-emphasis ms-1">ผู้แทนหลัก</span><?php endif; ?>
                            </div>
                            <div class="small text-body-secondary"><?= Html::encode($owner?->name ?? ('หน่วยงาน #' . $row->owner_id)) ?></div>
                        </div>
                        <?= Html::a('<i class="bi bi-trash"></i>', ['delete-reviewer', 'id' => $row->id], [
                            'class' => 'btn btn-sm btn-outline-danger align-self-start',
                            'data-method' => 'post', 'data-confirm' => 'นำผู้แทนคุณภาพออก?',
                            'aria-label' => 'นำผู้แทนคุณภาพออก',
                        ]) ?>
                    </div>
                <?php endforeach; ?>
                <?php if (!$rows): ?><div class="p-4 text-center text-body-secondary">ยังไม่ได้กำหนดผู้แทนคุณภาพ</div><?php endif; ?>
            </div>
        </div>
    </div>
</div>
