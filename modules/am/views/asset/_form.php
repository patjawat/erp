<?php
use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\modules\am\models\Asset;
use unclead\multipleinput\MultipleInput;

$title = Yii::$app->request->get('title');
$group = Yii::$app->request->get('group');
/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset $model */
/** @var yii\widgets\ActiveForm $form */

?>
<?php $this->beginBlock('page-action'); ?>
    <?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<style>
.modal-footer {
    display: none !important;
}
</style>

<?php $form = ActiveForm::begin([
    'id' => 'form-asset',
    'enableAjaxValidation' => true,
    'validationUrl' => ['/am/asset/validator'],
]); ?>

<?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true])->label(false) ?>
<?= $form->field($model, 'asset_group')->hiddenInput(['maxlength' => true])->label(false) ?>

<div class="row">

    <div class="col-xl-4 col-lg-4 col-md-12 col-sm-12">
        <div class="card">
            <div class="card-body">
                <div class="dropdown edit-field-half-left ml-2">
                    <div class="btn-icon btn-icon-sm btn-icon-soft-primary dropdown-toggle me-0 edit-field-icon"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-ellipsis"></i>
                    </div>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a href="#" class="dropdown-item select-photo">
                            <i class="fa-solid fa-file-image me-2 fs-5"></i>
                            <span>อัพโหลดภาพ</span>
                        </a>
                    </div>
                </div>

                <input type="file" id="my_file" style="display: none;" />
                <a href="#" class="select-photo">
                    <?= Html::img($model->showImg(), ['class' => 'avatar-profile object-fit-cover rounded', 'style' => 'max-width:100%;']) ?>
                </a>
            </div>
        </div>
    </div>
    <div class="col-xl-8 col-lg-8 col-md-12 col-sm-12">
        <?= $this->render('_form_detail' . $model->asset_group . '.php', ['model' => $model, 'form' => $form]) ?>
    </div>
    
 
</div>

<!-- ถ้าเป็นรถยนต์ -->
<?php if($model->assetItem?->category_id == 4):?>
    <?php echo $model->assetItem->id?>
<?= $this->render('asset_item',['model' => $model, 'form' => $form]) ?>
<?php endif;?>

<div class="form-group mt-4 d-flex justify-content-center">
    <?= AppHelper::BtnSave(); ?>
</div>
<?php ActiveForm::end(); ?>

<?php
$js = <<< JS
// JavaScript code here
JS;
$this->registerJs($js);
?>
