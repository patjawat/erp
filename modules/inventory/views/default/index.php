<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'ระบบคลัง';
// $this->params['breadcrumbs'][] = ['label' => 'ระบบคลัง', 'url' => ['/inventory/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
            <path d="M16 3.128a4 4 0 0 1 0 7.744"></path>
            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
            <circle cx="9" cy="7" r="4"></circle>
        </svg>
        <?=$this->title?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?php echo $this->render('@app/modules/inventory/menu_dashbroad',['active' => 'index']) ?>
<?php $this->endBlock(); ?>


<!-- https://www.canva.com/ai/code/thread/f82e0038-8164-4102-82d3-085d4d193cd0 -->

<div class="row row-cols-1 row-cols-sm-3 row-cols-md-4 g-3 justify-content-center">

    <?php foreach($dataProvider->getModels() as $model):?>
\
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
        <!-- Card -->
        <div class="p-2 bg-white rounded transform transition-all hover-translate-y-n2 duration-300 shadow-lg hover-shadow mt-3 zoom-in">
            <!-- Image -->

            <?php echo Html::img($model->ShowImg(), ['class' => 'h-40 object-cover rounded img-fluid']) ?>
            <div class="p-2">
                <!-- Heading -->
                 <div class="d-flex justify-content-between">
                     <h2 class="font-weight-bold h5 mb-2"> <?=$model->warehouse_name ?></h2>
                     <div class="dropdown float-end">
                        <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-ellipsis"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <?= Html::a('<i class="fa-regular fa-pen-to-square me-2"></i> แก้ไข', ['/inventory/warehouse/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]); ?>
                            <?= Html::a('<i class="fa-solid fa-trash me-1"></i>ลบ', ['/inventory/warehouse/delete', 'id' => $model->id], ['class' => 'dropdown-item  delete-item']) ?>
                        </div>
                    </div>
                 </div>
                 <div class="d-flex justify-content-between">
                     <?= $model->avatarStack() ?>
                     <div>

                         <?php if($model->countOrderRequest() > 0):?>
                            <span class="badge rounded-pill text-bg-primary"><?=$model->countOrderRequest()?> </span>
                            <?php endif;?>
                        </div>
                 </div>
            </div>
            <!-- CTA -->

            <div class="d-grid gap-2 m-2" id="selectWarehouse<?= $model->id ?>">

                <?= html::a(' <i class="bi bi-shop fs-3"></i> เลือกคลัง', ['/inventory/warehouse/view','id' => $model->id], [
                    'class' => 'btn btn-primary text-white bg-purple-600 rounded-md selct-warehouse',
                    'data' => [
                        'title' => $model->warehouse_name,
                        'img' => $model->ShowImg(),
                        'warehouse_id' => $model->id
                    ]
                ]) ?>

        </div>
        </div>
    </div>

        <?php endforeach;?>
    </div>
<?php
$js = <<< JS


$('.selct-warehouse').click(function (e) { 
    e.preventDefault();
    
    let url = $(this).attr('href');
    let title = $(this).data('title');
    let img = $(this).data('img');
    let warehouse_id = $(this).data('warehouse_id');

    Swal.fire({
        title: 'ยืนยันการเลือกคลังสินค้า?',
        text: "คุณต้องการเลือก "+title+" หรือไม่?",
        imageUrl: img,
        // imageWidth: 300,
        imageHeight: 300,
        showCancelButton: true,
        confirmButtonText: 'ยืนยัน',
        cancelButtonText: 'ยกเลิก',
        // icon: 'question'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "POST", // แก้เป็น "POST" หรือ "GET" ตามที่ API รองรับ
                url: url,
                data: { warehouse_id: warehouse_id }, // ส่งค่า warehouse_id ไปด้วย
                dataType: "json",
                success: function (response) {
                    if(response.status == 'success'){
                        Swal.fire({
                        title: 'สำเร็จ!',
                        text: 'เลือก'+title+'เรียบร้อย',
                        icon: 'success',
                        timer: 1000,
                    }).then(() => {
                        // location.reload(); // รีโหลดหน้าหลังจากดำเนินการสำเร็จ
                        window.location.href = '/inventory/warehouse/index';
                    });
                    }
                    
                  
                },
                error: function () {
                    // Swal.fire({
                    //     title: 'เกิดข้อผิดพลาด!',
                    //     text: 'ไม่สามารถเลือกคลังสินค้าได้',
                    //     icon: 'error'
                    // });
                }
            });
        }
    });
});



JS;
$this->registerJS($js,View::POS_END);
?>