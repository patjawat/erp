<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;
use app\components\DateFilterHelper;
use app\modules\hr\models\Organization;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\meetingsearch $model */
/** @var yii\widgets\ActiveForm $form */
?>
<style>
    .offcanvas-footer {
        padding: 1rem 1rem;
        border-top: 1px solid #dee2e6;
    }
</style>
<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    'fieldConfig' => ['options' => ['class' => 'form-group mb-0']],
    'options' => [
        'data-pjax' => 0
    ],
]); ?>
<?= $this->render('@app/components/ui/_filter', [
    'form' => $form,
    'model' => $model,
    'label' => false,
    'status' => $model->listStatus()
])
?>
<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-12">
        <?= $form->field($model, 'title')->textInput(['placeholder' => 'เรื่องการประชุ'])->label(false) ?>
    </div>
    <div class="col-lg-6 col-md-6 col-sm-12">
        <?= $form->field($model, 'room_id')->widget(Select2::classname(), [
            'data' => $model->listRooms(),
            'options' => ['placeholder' => 'ห้องประชุมทั้งหมด'],
            'pluginOptions' => [
                'tags' => true,  // เปิดให้เพิ่มค่าใหม่ได้
                'allowClear' => true,
            ],
        ])->label(false) ?>

    </div>
</div>

<div class="collapse mt-3" id="collapseFilter">
    <!-- การกรองแบบละเอียด -->
    <div class="row">
        <div class="col-3">
            <?= $form->field($model, 'thai_year')->widget(Select2::classname(), [
                'data' => $model->ListThaiYear(),
                'options' => ['placeholder' => 'ปีงบประมาณทั้งหมด'],
                'pluginOptions' => [
                    'allowClear' => true,
                    // 'width' => '120px',
                ],
            ])->label(false); ?>

        </div>
    </div>


</div>

<?php ActiveForm::end(); ?>


<?php

$js = <<< JS

    thaiDatepicker('#meetingsearch-date_start,#meetingsearch-date_end')

    $("#meetingsearch-date_start").on('change', function() {
            $('#meetingsearch-thai_year').val(null).trigger('change');
            $('#meetingsearch-date_filter').val(null).trigger('change');
        });
        $("#meetingsearch-date_end").on('change', function() {
            $('#meetingsearch-thai_year').val(null).trigger('change');
            $('#meetingsearch-date_filter').val(null).trigger('change');
    });

JS;
$this->registerJS($js, View::POS_END);

?>