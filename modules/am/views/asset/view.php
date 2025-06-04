<?php
use yii;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\widgets\DetailView;
use yii\base\ErrorException;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset $model */

$this->title = 'ทะเบียนทรัพย์สิน';
$this->params['breadcrumbs'][] = ['label' => 'Assets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
$title = Yii::$app->request->get('title');
$group = Yii::$app->request->get('group');
?>

<?php $this->beginBlock('page-title'); ?>
<?=$this->title;?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('../default/menu',['active' => 'asset'])?>
<?php $this->endBlock(); ?>
<style>
.field-asset-q {
    margin-bottom: 0px !important;
}
</style>

<?php Pjax::begin(['id' => 'am-container','timeout' => 50000 ]); ?>

<div class="asset-view">
    <?php if($model->asset_group == 1):?>
    <?=$this->render('@app/modules/am/views/asset/asset_detail_group_1',['model' => $model])?>
    <?php endif?>

    <?php if($model->asset_group == 2):?>
    <?=$this->render('@app/modules/am/views/asset/asset_detail_group_2',['model' => $model])?>
    <?php endif?>

    <?php if($model->asset_group == 3):?>
    <?=$this->render('@app/modules/am/views/asset/asset_detail_group_3',['model' => $model])?>

    <?= $model->asset_group == 3 ? $this->render('./asset_detail',['model' => $model,'searchModel' => $searchModel,
    'dataProvider' => $dataProvider]) :  ''?>

    <?php endif?>
</div>

<?php
$js = <<< JS


$('.delete-asset').click(function (e) { 
    e.preventDefault();
    let url = $(this).attr('href');

    Swal.fire({
        title: 'คุณแน่ใจหรือไม่?',
        text: "ข้อมูลนี้จะถูกลบและไม่สามารถกู้คืนได้!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
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
                            timer: 1000, // ตั้งค่าให้ Swal ปิดอัตโนมัติหลัง 1 วินาที
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = '/am/asset'; // Redirect หลังจาก timer หมด
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
$this->registerJS($js);

?>
<?php Pjax::end(); ?>