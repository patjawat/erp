<?php

use app\components\widgets\DataSummaryWidget;
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
        <span class="text-primary-emphasis"><i class="fa-solid fa-desktop"></i></span>
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

<div class="equip-register-page">
<?= $this->render('kpi_summary', ['equipStats' => $equipStats]) ?>

<div class="card border shadow-sm">
    <div class="card-body p-3">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>

<div class="card border shadow-sm">
    <div class="card-header bg-body-tertiary px-4 py-3 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
            <div class="p-2 rounded-3 bg-primary-subtle text-primary-emphasis">
                <i data-lucide="notepad-text"></i>
            </div>
            <h5 class="m-0 fw-bold">รายการทะเบียนคุมครุภัณฑ์</h5>
            <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle rounded-pill fw-medium px-2 py-1">
                <?= number_format($dataProvider->getTotalCount(), 0) ?> รายการ
            </span>
        </div>
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="d-flex gap-2 p-1 rounded-3 border bg-body">
                <?= Html::a('<i class="fa-solid fa-table me-1"></i> ตาราง', $viewListUrl, [
                    'class' => 'btn ' . ($isTableView ? 'btn-primary' : 'btn-outline-primary'),
                    'data-pjax' => 0,
                ]) ?>
                <?= Html::a('<i class="fa-solid fa-grip me-1"></i> การ์ด', $viewGridUrl, [
                    'class' => 'btn ' . (!$isTableView ? 'btn-primary' : 'btn-outline-primary'),
                    'data-pjax' => 0,
                ]) ?>
            </div>
            <?= Html::a('<i class="fa-solid fa-circle-plus me-1"></i> สร้างใหม่', ['create'], [
                'class' => 'btn btn-primary fw-semibold d-flex align-items-center gap-2 rounded-3 shadow-sm',
                'data-pjax' => 0,
            ]) ?>
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
    <div class="card-footer bg-body border-top py-3 px-4">
        <?php
        echo DataSummaryWidget::widget([
            'dataProvider' => $dataProvider,
            'pagerOptions' => [],
        ]);
        ?>
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
