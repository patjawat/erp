<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\hr\models\LeaveEntitlements;
/** @var yii\web\View $this */
/** @var app\modules\hr\models\LeaveEntitlementsSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'กำหนดสิทธิลาพักผ่อน';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/hr/views/leave/menu_settings') ?>
<?php $this->endBlock(); ?>
<?php Pjax::begin(['id' => 'leave']); ?>


<div class="card">
    <div class="card-body">

    <div class="d-flex justify-content-between">
            <h6>
                <i class="bi bi-ui-checks"></i> นโยบายการลา
                <span class="badge rounded-pill text-bg-primary"><?php echo $dataProvider->getTotalCount() ?></span>
                รายการ
            </h6>
        </div>
        
        <div class="d-flex justify-content-between  align-top align-items-center">
            <?php echo $this->render('_search', ['model' => $searchModel]); ?>
            <div class="d-flex gap-2">
                <?= Html::a('<i class="bi bi-plus-circle-fill"></i> กำหนดสิทธิรายบุคคล', ['create','title' => 'กำหนดสิทธิลาพักผ่อน'], ['class' => 'btn btn-primary open-modal','data' => ['size' => 'modal-md']]) ?>
                <?= Html::a('<i class="fa-solid fa-user-clock"></i> กำหนดสิทธิทั้งหมด', ['create-all','title' => 'กำหนดสิทธิลาพักผ่อนทั้งหมด'], ['class' => 'btn btn-warning create-all','data' => ['size' => 'modal-md']]) ?>
            </div>
        </div>

            <table
                class="table table-primary"
            >
                <thead>
                    <tr>
                        <th scope="col" style="width:80px;">ปีงบประมาณ</th>
                        <th scope="col">ชื่อ-นามสกุล</th>
                        <th scope="col" class="text-center">วันลาที่ได้</th>
                        <th scope="col" class="text-center">ใช้ไป</th>
                        <th scope="col" class="text-center">คงเหลือ</th>
                    </tr>
                </thead>
                <tbody class="align-middle">
                    <?php foreach($dataProvider->getModels() as $item):?>
                    <tr class="">
                        <td><?php echo $item->thai_year?></td>
                        <td scope="row"><?php echo $item->employee->getAvatar(false)?></td>
                        <td class="text-center fw-semibold"><?php echo $item->days;?></td>
                        <td class="text-center fw-semibold"><?php echo $item->getSummary()['leave_use'];?></td>
                        <td class="text-center fw-semibold"><?php echo $item->getSummary()['leave_total'];?></td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
            <div class="d-flex justify-content-center">

<?php echo  yii\bootstrap5\LinkPager::widget([
    'pagination' => $dataProvider->pagination,
    'firstPageLabel' => 'หน้าแรก',
    'lastPageLabel' => 'หน้าสุดท้าย',
    'options' => [
        'listOptions' => 'pagination pagination-sm',
        'class' => 'pagination-sm',
    ],
]); ?>
</div>

</div>
</div>

<?php
$createAllUrl = Url::to(['/hr/leave-entitlements/create-all']);
$js = <<< JS

$('.create-all').click(function (e) { 
    var thaiYear = $('#leaveentitlementssearch-thai_year').val();
    e.preventDefault();
    Swal.fire({
      title: "ยืนยัน?",
      text: "กำหนดสิทธิวันลาทั้งหมดในปีงบประมาณ "+thaiYear+"!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      cancelButtonText: "ยกเลิก!",
      confirmButtonText: "ใช่, ยืนยัน!"
    }).then((result) => {
      if (result.isConfirmed) {
        
        \$.ajax({
            url: "$createAllUrl",
            type: 'post',
            data: {
                thai_year: $('#leaveentitlementssearch-thai_year').val(),
            },
            dataType: 'json',
            success: async function (res) {
                if(res.status == 'success'){
                    $.pjax.reload({ container:res.container, history:false,replace: false,timeout: false})
                }else{
                    Swal.fire({
                      icon: 'error',
                      title: 'เกิดข้อผิดพลาด',
                      text: res.message,
                    })
                }
            }
        });

      }
    });
});
JS;
$this->registerJS($js,View::POS_END);
?>

<?php Pjax::end(); ?>