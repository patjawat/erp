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

    <div class="row g-3 pt-3">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.25rem;"><i class="fa-solid fa-desktop"></i></div>
                    <div>
                        <div class="fs-4 fw-bold text-primary"><?= (int) $equipStats['total'] ?></div>
                        <div class="small text-muted">ทั้งหมด</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.25rem;"><i class="fa-solid fa-check"></i></div>
                    <div>
                        <div class="fs-4 fw-bold text-success"><?= (int) $equipStats['good'] ?></div>
                        <div class="small text-muted">สภาพดี</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.25rem;"><i class="fa-solid fa-circle"></i></div>
                    <div>
                        <div class="fs-4 fw-bold text-danger"><?= (int) $equipStats['damaged'] ?></div>
                        <div class="small text-muted">ชำรุด/รอซ่อม</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center bg-warning bg-opacity-25 text-body flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.25rem;"><i class="fa-solid fa-sack-dollar"></i></div>
                    <div>
                        <div class="fs-4 fw-bold text-primary"><?= Html::encode(number_format($equipStats['total_value'], 2)) ?></div>
                        <div class="small text-muted">รวมราคาแรกรับ (บาท)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body border-bottom py-3 px-3 px-md-4">
                    <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-lg-between gap-3">
                        <h6 class="mb-0 fw-semibold d-flex align-items-center gap-2 text-body">
                            <i class="fa-regular fa-file-lines text-primary"></i>
                            ทะเบียนคุมครุภัณฑ์
                        </h6>
                        <div class="d-flex flex-wrap align-items-center gap-2 w-50 w-lg-auto justify-content-start justify-content-lg-end ms-lg-auto">
                            <?= Html::a('<i class="fa-solid fa-circle-plus me-1"></i> ลงทะเบียน', ['create'], [
                                'class' => 'btn btn-sm btn-primary text-white shadow-sm',
                                'data-pjax' => 0,
                            ]) ?>
                            <div class="btn-group btn-group-sm" role="group" aria-label="มุมมอง">
                                <?= Html::a('<i class="fa-solid fa-table me-1"></i> ตาราง', $viewListUrl, [
                                    'class' => 'btn ' . ($isTableView ? 'btn-primary' : 'btn-outline-primary'),
                                    'data-pjax' => 0,
                                ]) ?>
                                <?= Html::a('<i class="fa-solid fa-grip me-1"></i> การ์ด', $viewGridUrl, [
                                    'class' => 'btn ' . (!$isTableView ? 'btn-primary' : 'btn-outline-primary'),
                                    'data-pjax' => 0,
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body py-3">
                    <?php echo $this->render('_search', ['model' => $searchModel]); ?>
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
