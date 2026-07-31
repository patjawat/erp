<?php

use yii\web\View;
use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use app\modules\hr\models\Organization;
use app\modules\helpdesk2\models\Helpdesk;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk2\models\HelpdeskSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>
<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    'fieldConfig' => ['options' => ['class' => 'form-group mb-0']],
    'options' => [
        'data-pjax' => 0,
    ],
]); ?>

<div class="row g-2 align-items-end">
    <div class="col-6 col-lg-2">
        <?= $this->render('@app/components/ui/_date_filter', ['form' => $form, 'model' => $model, 'label' => false]) ?>
    </div>
    <div class="col-6 col-lg-2">
        <?= $this->render('@app/components/ui/_date_start', ['form' => $form, 'model' => $model, 'label' => false]) ?>
    </div>
    <div class="col-6 col-lg-2">
        <?= $this->render('@app/components/ui/_date_end', ['form' => $form, 'model' => $model, 'label' => false]) ?>
    </div>
    <div class="col-6 col-lg-3">
        <?php
        $statusOptions = Helpdesk::repairStatusOptions();
        echo $form->field($model, 'status')->widget(Select2::classname(), [
            'data' => $statusOptions,
            'options' => [
                'placeholder' => 'สถานะ (เลือกได้หลายค่า)',
                'multiple' => true,
            ],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label(false);
        ?>
    </div>
    <div class="col-12 col-lg-3">
        <div class="d-flex align-items-end gap-2">
            <div class="flex-grow-1">
                <?= $this->render('@app/components/ui/input_emp', ['form' => $form, 'model' => $model, 'label' => false, 'placeholder' => 'ผู้แจ้งซ่อม']) ?>
            </div>
            <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', [
                'class' => 'btn btn-sm btn-primary flex-shrink-0',
                'aria-label' => 'ค้นหา',
                'title' => 'ค้นหา',
            ]) ?>
            <button class="btn btn-sm btn-outline-primary flex-shrink-0" type="button"
                data-bs-toggle="collapse" data-bs-target="#collapseFilter"
                aria-expanded="false" aria-controls="collapseFilter"
                aria-label="ตัวกรองเพิ่มเติม" title="ตัวกรองเพิ่มเติม">
                <i class="fa-solid fa-filter"></i>
            </button>
        </div>
    </div>
</div>

<div class="row g-2 mt-1">
    <div class="col-12 col-lg-8">
        <?= $form->field($model, 'q')->textInput(['class' => 'form-control', 'placeholder' => 'ค้นหาเลขที่ / ชื่อ / สถานที่ / รายละเอียดปัญหา'])->label(false); ?>
    </div>
    <div class="col-12 col-lg-4">
        <?= $form->field($model, 'q_department')->widget(\kartik\tree\TreeViewInput::className(), [
            'name' => 'department',
            'id' => 'treeID',
            'query' => Organization::find()->addOrderBy('root, lft'),
            'value' => 1,
            'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
            'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
            'fontAwesome' => true,
            'asDropdown' => true,
            'multiple' => false,
            'options' => ['disabled' => false, 'allowClear' => true, 'class' => 'close'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label(false); ?>
    </div>
</div>

<div class="collapse mt-2" id="collapseFilter">
    <div class="row g-2">
        <div class="col-12 col-lg-3">
            <?= $form->field($model, 'thai_year')->widget(Select2::classname(), [
                'data' => $model->ListThaiYear(),
                'options' => ['placeholder' => 'ปีงบประมาณทั้งหมด'],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ])->label(false); ?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
thaiDatepicker('#helpdesksearch-date_start,#helpdesksearch-date_end')
JS;
$this->registerJS($js, View::POS_END);
?>
