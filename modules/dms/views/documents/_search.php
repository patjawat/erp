<?php

use app\components\DateFilterHelper;
use app\modules\hr\models\Organization;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\DocumentSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<?php $form = ActiveForm::begin([
    'action' => [$model->document_group],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
    'fieldConfig' => ['options' => ['class' => 'form-group mb-1 mr-2 me-2']] // spacing form field groups
]); ?>

<div class="row g-2 align-items-start">
    <div class="col-6 col-md-2">
        <?= $this->render('@app/components/ui/_date_filter', ['form' => $form, 'model' => $model, 'label' => false]) ?>
    </div>
    <div class="col-6 col-md-2">
        <?= $this->render('@app/components/ui/_date_start', ['form' => $form, 'model' => $model, 'label' => false]) ?>
    </div>
    <div class="col-6 col-md-2">
        <?= $this->render('@app/components/ui/_date_end', ['form' => $form, 'model' => $model, 'label' => false]) ?>
    </div>
    <div class="col-6 col-md-3">
        <?= $form->field($model, 'status')->widget(Select2::classname(), [
            'data' => $model->listStatus(),
            'options' => ['placeholder' => 'สถานะทั้งหมด'],
            'pluginOptions' => ['allowClear' => true],
        ])->label(false) ?>
    </div>
    <div class="col-6 col-md-3">
        <?= $form->field($model, 'document_org')->widget(Select2::classname(), [
            'data' => $model->ListDocumentOrg(),
            'options' => ['placeholder' => 'หน่วยงานทั้งหมด'],
            'pluginOptions' => ['allowClear' => true, 'tags' => true],
        ])->label(false); ?>
    </div>
     <div class="col-6">
        <div class="input-group mb-3">
            <span class="input-group-text bg-light text-muted border-end-0">
                <i class="bi bi-search"></i>
            </span>
            <?= $form->field($model, 'q', [
                'options' => ['tag' => false], // ลบ div wrapper ของฟิลด์ออกเพื่อให้เข้าชุดกับ input-group
            ])->textInput([
                'placeholder' => 'พิมพ์คำค้นหาที่นี่...',
                'class' => 'form-control border-start-0'
            ])->label(false) ?>
        </div>
    </div>
        <div class="col-lg-6 col-md-8">
            <div class="input-group">

                <?= $form->field($model, 'q_department', [
                    'template' => '{input}',
                    'options' => ['class' => 'flex-grow-1']
                ])->widget(\kartik\tree\TreeViewInput::className(), [
                    'id' => 'treeID',
                    'query' => Organization::find()->addOrderBy('root, lft'),
                    'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
                    'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
                    'fontAwesome' => true,
                    'asDropdown' => true,
                    'multiple' => false,

                    'options' => [
                        'placeholder' => 'เลือกหน่วยงาน...',
                        'class' => 'form-control'
                    ],

                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ])->label(false); ?>

                <button class="btn btn-primary px-4" type="submit">
                    ค้นหา
                </button>

                <button class="btn btn-outline-secondary"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseFilter">
                    <i class="fa-solid fa-filter"></i>
                </button>

            </div>
        </div>


   
</div>

<div class="collapse" id="collapseFilter">
    <div class="card card-body mb-3 shadow-sm border-primary">
        <p class="small text-muted mb-0">ตัวเลือกการกรองเพิ่มเติม...</p>


    </div>
</div>

<?= $form->field($model, 'document_group')->hiddenInput()->label(false) ?>
<?php ActiveForm::end(); ?>

<?php

$js = <<<JS

JS;
$this->registerJS($js, View::POS_END);

?>