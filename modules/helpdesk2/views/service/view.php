<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\bootstrap5\LinkPager;
use app\modules\sm\models\Order;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\OrderSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = '';
$this->params['breadcrumbs'][] = 'ระบบงานซ่อม';
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนงานซ่อม', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'ทะเบียนงานซ่อม';

?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">

        <?= $this->title; ?>
    </h4>
</div>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('action'); ?>
<?php echo $this->render('@app/modules/helpdesk2/menu', ['active' => 'index']) ?>
<?php $this->endBlock(); ?>



<div class="row g-4">

    <div class="col-12 col-md-4">
        <div class="card h-100">
            <div class="card-header p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <i class="bi bi-person-circle"></i> ข้อมูลผู้แจ้งซ่อม
                    </div>
                    <?= $model->viewCreateDateTime() ?>
                </div>
            </div>

            <div class="card-body">
                <div class="d-flex align-items-center mb-4">
                    <div class="bg-light rounded-3 p-3 me-3">
                        <?php
                        echo Html::img('@web/img/loading.gif', [
                            'class' => 'rounded-4 me-3 shadow lazyload',
                            'width' => '40',
                            'height' => '40',
                            'data' => [
                                'expand' => '-20',
                                'sizes' => 'auto',
                                'src' => $model->emp->getImg()
                            ]
                        ]); ?>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0"><?= $model->emp->fullname ?></h6>
                        <small class="text-muted"><?= $model->emp->departmentName() ?></small>
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 mb-4">
                    <p class="fw-bold text-danger mb-1"><i class="fa-solid fa-triangle-exclamation"></i> อาการเสียที่ระบุ:</p>
                    <p class="mb-0 text-dark"><?= $model->title ?></p>
                </div>

                <div class="section-title"><i class="bi bi-geo-alt"></i> สถานที่/ทรัพย์สิน</div>
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">รหัสทรัพย์สิน:</span>
                        <span class="fw-medium"><?= $model->asset_name ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">สถานที่:</span>
                        <span class="fw-medium"><?= $model->data_json['location'] ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">ความเร่งด่วน:</span>
                        <span class="fw-medium"><?= $model->viewUrgent()['view'] ?></span>
                    </li>

                </ul>
            </div>
            <?= $item->repairStatus?->title ?? '-' ?>
        </div>
    </div>



    <div class="col-12 col-md-8">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">ข้อมูลการซ่อม</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-3">
                    <dt class="col-sm-4">ผู้รับผิดชอบ:</dt>
                    <dd class="col-sm-8"><?= $model->StackTeam() ?></dd>

                    <dt class="col-sm-4">วันที่รับเรื่อง:</dt>
                    <dd class="col-sm-8"><?= $model->viewReceiveDate() ?></dd>

                </dl>
                <div id="showFormFormStatus"></div>
            </div>
        </div>
    </div>
</div>

<div class="mt-5">

    <ul class="nav nav-pills" id="filledTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home"
                type="button" role="tab">
                บันทึกการซ่อม
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="team-tab" data-bs-toggle="tab" data-bs-target="#team" type="button"
                role="tab">
                ผู้ร่วมดำเนินงาน
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="repairParts-tab" data-bs-toggle="tab" data-bs-target="#repairParts"
                type="button" role="tab">
                อะไหล่ที่ใช้
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="repairExpenses-tab" data-bs-toggle="tab" data-bs-target="#repairExpenses"
                type="button" role="tab">
                ค่าใช้จ่าย
            </button>
        </li>
    </ul>
    <div class="tab-content" id="filledTabsContent">
        <div class="tab-pane fade show active" id="home" role="tabpanel">

            <div class="card">
                <div class="card-body">
                    <div class="mt-3">
                        <div id="showFormFormServiceRecord"></div>
                    </div>
                    <div id="showTimeline"></div>
                </div>
            </div>

        </div>
        <div class="tab-pane fade" id="team" role="tabpanel">
            <div id="showFormTeam"></div>
            <div id="showListTeam"></div>
        </div>
        <div class="tab-pane fade" id="repairExpenses" role="tabpanel">
            <div id="showListExpenses"></div>
        </div>
        <div class="tab-pane fade" id="repairParts" role="tabpanel">

        </div>
    </div>
</div>



<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">รูปภาพประกอบ</h6>
        </div>
        <div class="card-body">
            <?= $model->imageRequest ?>

        </div>
    </div>
</div>
</div>
<?php

$urlFormServiceRecord = Url::to(['/helpdesk/service-record/create', 'helpdesk_id' => $model->id]);
$urlTimeline = Url::to(['/helpdesk/service-record/timeline', 'helpdesk_id' => $model->id]);
$urlExpenses = Url::to(['/helpdesk/expenses', 'helpdesk_id' => $model->id]);

$urlFormTeam = Url::to(['/helpdesk/team/create', 'helpdesk_id' => $model->id]);
$urllistTeam = Url::to(['/helpdesk/team/list', 'helpdesk_id' => $model->id]);
$urllistTeam = Url::to(['/helpdesk/team/list', 'helpdesk_id' => $model->id]);
$urlFormStatus = Url::to(['/helpdesk/service/update-status', 'id' => $model->id]);
$js = <<<JS

loadFormServiceRecord()
loadTimeline()
loadFormTeam()
loadListTeam()
loadFormStatus()
loadExpenses()

function loadFormStatus()
{
    $.ajax({
        type: "get",
        url: "$urlFormStatus",
        dataType: "json",
        success: function (response) {
            $('#showFormFormStatus').html(response.content)
        }
    });
}


function loadFormServiceRecord()
{
    $.ajax({
        type: "get",
        url: "$urlFormServiceRecord",
        dataType: "json",
        success: function (response) {
            $('#showFormFormServiceRecord').html(response.content)
        }
    });
}

function loadTimeline()
{
    $.ajax({
        type: "get",
        url: "$urlTimeline",
        dataType: "json",
        success: function (response) {
            $('#showTimeline').html(response.content)
        }
    });
}

function loadFormTeam()
{
    $.ajax({
        type: "get",
        url: "$urlFormTeam",
        dataType: "json",
        success: function (response) {
            $('#showFormTeam').html(response.content)
        }
    });
}

function loadListTeam()
{
    $.ajax({
        type: "get",
        url: "$urllistTeam",
        dataType: "json",
        success: function (response) {
            $('#showListTeam').html(response.content)
            console.log('loadsuccess');
            
        }
    });
}
function loadExpenses()
{
    $.ajax({
        type: "get",
        url: "$urlExpenses",
        dataType: "json",
        success: function (response) {
            $('#showListExpenses').html(response.content)
            console.log('loadsuccess');
            
        }
    });
}

JS;

$this->registerJs($js);
?>