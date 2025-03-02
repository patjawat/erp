<?php

use yii\web\View;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\Booking $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Bookings', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="row">
    <div class="col-6">
        <div class="flex-shrink-0 rounded p-5 mb-3"
            style="background-image:url(<?php echo $model->room ? $model->room->showImg() :  ''?>);background-size:cover;background-repeat:no-repeat;background-position:center;height:258px;">

        </div>
    </div>
    <div class="col-6">
        <div class="card">
            <div class="card-body">
            
            <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'label' => 'วันที่',
                'format' => 'html',
                'value' => Yii::$app->thaiFormatter->asDate($model->date_start, 'full')
            ],
            [
                'label' => 'เวลา',
                'format' => 'html',
                'value' =>   $model->time_start.' - '.$model->time_end
            ],
            'reason',

            [
                'label' => 'กลุ่มบุคคลเป้าหมาย',
                'format' => 'html',
                'value' => function($model){
                    return   $model->data_json['employee_point'] ?? '-';
                }
            ],
            [
                'label' => 'จำนวนผู้ร่วมประชุม',
                'format' => 'html',
                'value' => function($model){
                    return   $model->data_json['employee_total'] ?? '-';
                }
            ],
            [
                'label' => 'เบอร์ติดต่อ',
                'format' => 'html',
                'value' => function($model){
                    return   $model->data_json['phone'] ?? '-';
                }
            ],
            [
                'label' => 'รายการอุปกรณ์ที่ต้องการ',
                'format' => 'html',
                'value' => function($model){
                    return   $model->listAccessoryUse();
                }
            ],
            
            

            
        ],
    ]) ?>

            </div>
        </div>

    </div>
</div>

<?php if(count($model->listMembers) > 0):?>
    <div class="alert alert-primary p-2" role="alert">

<div class="d-flex justify-content-between align-items-center gap-3">
    
    <h6 class="text-center text-primary"><i class="fa-solid fa-circle-exclamation text-warning fs-/"></i> ผู้เข้าร่วมประชุมจะได้รับการแจ้งเตือนข้อความหลังจากที่ห้องประชุมได้รับการจัดสสร</h6>

</div>
</div>


<?php endif;?>

    <div class="d-flex justify-content-center gap-3">
        <?php echo Html::a('จัดสรร',['/booking/meeting/room-status'],['class' => 'btn btn-primary shadow rounded-pill room-status','data' => [
            'title' => 'จัดสรร',
            'id' => $model->id,
            'status' => 'Approve'
        ]])?>

        <?php echo Html::a('ไม่จัดสรรค',['/booking/meeting/room-status'],['class' => 'btn btn-danger shadow rounded-pill room-status','data' => [
             'title' => 'ยกเลิอก',
            'id' => $model->id,
            'status' => 'Reject'
        ]])?>

    </div>

<?php
$js = <<< JS

$("body").on("click", ".room-status", function (e) {

    e.preventDefault();
    Swal.fire({
        title: "ยืนยัน?",
        text: $(this).data('title')+"ห้องประชุม!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "ยกเลิก!",
        confirmButtonText: "ใช่, ยืนยัน!"
        }).then((result) => {
        if (result.isConfirmed) {
            beforLoadModal()
            \$.ajax({
                url: $(this).attr('href'),
                type: 'post',
                data:{id: $(this).data('id'),status:$(this).data('status')},
                dataType: 'json',
                success: async function (res) {
                    if(res.status == 'success') {
                        success()
                        closeModal()
                        location.reload(true)
                        // success()
                        // await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                    }
                }
            });

        }
        });
});



$('#roomCancel').click(function (e) { 
    e.preventDefault();
    Swal.fire({
        title: "ยืนยัน?",
        text: "จัดสรรห้องประชุม!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "ยกเลิก!",
        confirmButtonText: "ใช่, ยืนยัน!"
        }).then((result) => {
        if (result.isConfirmed) {
            beforLoadModal()
            \$.ajax({
                url: $(this).attr('href'),
                type: 'post',
                data:{id: $(this).data('id')},
                dataType: 'json',
                success: async function (res) {
                    if(res.status == 'success') {
                        success()
                        closeModal()
                        location.reload(true)
                        // success()
                        // await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                    }
                }
            });

        }
        });
});

JS;
$this->registerJS($js,View::POS_END)
    ?>