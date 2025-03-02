<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
// use yii\jui\DatePicker;
use yii\web\JsExpression;
use app\models\Categorise;
// use kartik\date\DatePicker;
// use kartik\date\DatePicker;
use kartik\date\DatePicker;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use kartik\sortable\Sortable;
use app\components\SiteHelper;
// use karatae99\datepicker\DatePicker;
use app\components\UserHelper;
use kartik\widgets\ActiveForm;
use app\widgets\FlatpickrWidget;
use yii\web\HtmlResponseFormatter;
use app\modules\hr\models\Employees;
use app\components\ApproveHelper;
use iamsaint\datetimepicker\Datetimepicker;
use app\widgets\Flatpickr\FlatpickrBuddhistWidget;
$totalNotification = ApproveHelper::Info()['total'];
$site = SiteHelper::getInfo();
$me = UserHelper::GetEmployee();
?>


<div class="page-title-box-line mb-5">
        <div class="d-flex justify-content-between align-items-center mt-5">
            <div class="page-title-line">

                <div class="d-flex gap-2">
                    <?=Html::img($site['logo'], ['class' => 'avatar avatar-md me-0 mt-2'])?>

                    <div class="avatar-detail">
                        <h5 class="mb-0 text-white text-truncate mt-3"><?php echo $site['company_name']?></h5>
                        <p class="text-white mb-0 fs-13">ERP Hospital</p>
                    </div>
                </div>
              
            </div>
            <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#staticBackdrop"
                aria-controls="staticBackdrop">
                <i class="fa-solid fa-bars fs-3"></i>
            </button>

        </div>
    </div>
    
    <?php $form = ActiveForm::begin([
    'id' => 'form-elave',
    'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/hr/leave/create-validator']
]); ?>

    
<div class="card">
    <div class="card-body">
sssss
    
    </div>
</div>

<?php ActiveForm::end(); ?>
