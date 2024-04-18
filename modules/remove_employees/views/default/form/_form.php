
<?php

use app\components\AppHelper;
use app\components\SiteHelper;
use iamsaint\datetimepicker\Datetimepicker;
use kartik\widgets\Select2;
use kartik\widgets\Typeahead;
use yii\helpers\Html;
// use yii\bootstrap5\ActiveForm;
use kartik\form\ActiveForm;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\MaskedInput;

/** @var yii\web\View $this */
/** @var app\models\Employees $model */
/** @var yii\widgets\ActiveForm $form */
?>




    <?php $form = ActiveForm::begin([
        'id' => 'form-employee',
    ]); ?>

        <?=$this->render('general',['form' => $form,'model' => $model])?>

<div class="d-flex justify-content-center">
    <?= SiteHelper::btnSave() ?>
</div>

<?php ActiveForm::end(); ?>

