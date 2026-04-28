<?php

use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\widgets\Pjax;
use app\components\SiteHelper;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array{total:int,good:int,damaged:int,total_value:float} $equipStats */

$this->title = 'ครุภัณฑ์';
$this->params['breadcrumbs'][] = ['label' => 'ระบบบริหารทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;

$viewQuery = array_merge(Yii::$app->request->queryParams, []);
$viewListUrl = Url::to(array_merge(['/am/equip/index'], $viewQuery, ['view' => 'list']));
$viewGridUrl = Url::to(array_merge(['/am/equip/index'], $viewQuery, ['view' => 'grid']));
$isTableView = SiteHelper::getDisplay() !== 'grid';

?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 w-100">
    <h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
        <span class="text-primary"><i class="fa-solid fa-desktop"></i></span>
        ทะเบียนครุภัณฑ์
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<div class="d-flex flex-wrap gap-2 align-items-center justify-content-center justify-content-lg-end">
    <?= $this->render('@app/modules/am/menu', ['active' => 'equip']) ?>
</div>
<?php $this->endBlock(); ?>


<?php Pjax::begin(['id' => 'title-container', 'timeout' => 50000]); ?>
<?php $this->beginBlock('navbar_menu'); ?>
<?= $this->render('../default/menu', ['active' => 'asset']) ?>
<?php $this->endBlock(); ?>
<?php Pjax::end(); ?>

<div class="card">
    <div class="card-body p-3">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>

<?= $this->render('kpi_summary', ['equipStats' => $equipStats]) ?>

<div class="row g-3 mt-1">
    <div class="col-12">
        <div class="card">
             <div class="px-4 py-3 border-bottom d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3" style="border-color: rgb(241, 245, 249); background-color: rgba(248, 250, 252, 0.5);">
            <div class="d-flex align-items-center gap-2">
                <div class="p-2 rounded-3" style="background-color: rgb(219, 234, 254);"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1E4E91" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-list" aria-hidden="true">
                        <rect width="8" height="4" x="8" y="2" rx="1" ry="1"></rect>
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                        <path d="M12 11h4"></path>
                        <path d="M12 16h4"></path>
                        <path d="M8 11h.01"></path>
                        <path d="M8 16h.01"></path>
                    </svg></div>
                <h3 class="m-0 fw-bold" style="font-size: 16px; color: rgb(30, 41, 59);">รายการทะเบียนคุมครุภัณฑ์</h3><span class="badge rounded-pill fw-bold" style="background-color: rgb(226, 232, 240); color: rgb(71, 85, 105); font-size: 10px; padding: 4px 8px;"><?=$dataProvider->getTotalCount()?> รายการ</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex gap-2 p-1 rounded-3 border" style="background-color: rgb(241, 245, 249); border-color: rgb(226, 232, 240);">

                    <?= Html::a('<i class="fa-solid fa-table me-1"></i> ตาราง', $viewListUrl, [
                        'class' => 'btn ' . ($isTableView ? 'btn-primary' : 'btn-outline-primary'),
                        'data-pjax' => 0,
                    ]) ?>
                    <?= Html::a('<i class="fa-solid fa-grip me-1"></i> การ์ด', $viewGridUrl, [
                        'class' => 'btn ' . (!$isTableView ? 'btn-primary' : 'btn-outline-primary'),
                        'data-pjax' => 0,
                    ]) ?>

                </div><button class="btn text-white fw-semibold d-flex align-items-center gap-2 rounded-3 shadow-sm" style="background-color: rgb(30, 78, 145); font-size: 14px; padding: 8px 20px;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus" aria-hidden="true">
                        <path d="M5 12h14"></path>
                        <path d="M12 5v14"></path>
                    </svg> <span>ลงทะเบียนครุภัณฑ์</span></button>
            </div>
        </div>

            <div class="card-body p-0">
                <?php if ($isTableView): ?>
                    <?= $this->render('_list', [
                        'dataProvider' => $dataProvider,
                    ]) ?>
                <?php else: ?>
                    <?= $this->render('_grid', [
                        'dataProvider' => $dataProvider,
                    ]) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<span id="totalCount" class="d-none"><?= (int) $dataProvider->getTotalCount(); ?></span>

<?php
$equipIndexUrl = Json::encode(Url::to(['/am/equip/index']));
$js = <<< JS
$('#am-container').on('pjax:success', function() {
    $('#showTotalCount').text($('#totalCount').text());
    $.pjax.reload({ container:'#title-container', history:false, replace: false});
});

$('.delete-asset').click(function (e) {
    e.preventDefault();
    let url = $(this).attr('href');

    Swal.fire({
        title: 'คุณแน่ใจหรือไม่?',
        text: "ข้อมูลนี้จะถูกลบและไม่สามารถกู้คืนได้!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "post",
                url: url,
                dataType: "json",
                success: function (res) {
                    if (res.status == 'success') {
                        Swal.fire({
                            title: 'ลบข้อมูลสำเร็จ!',
                            text: 'รายการถูกลบเรียบร้อยแล้ว',
                            icon: 'success',
                            timer: 1000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = $equipIndexUrl;
                        });
                    } else {
                        Swal.fire(
                            'เกิดข้อผิดพลาด!',
                            res.message || 'ไม่สามารถลบข้อมูลได้',
                            'error'
                        );
                    }
                },
                error: function () {
                    Swal.fire(
                        'เกิดข้อผิดพลาด!',
                        'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้',
                        'error'
                    );
                }
            });
        }
    });
});
JS;
$this->registerJs($js);
?>