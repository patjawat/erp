<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/** @var yii\web\View $this */
?>

<div class="card">
    <div class="card-body">
        <h4 class="card-title">เอกสารต่างๆที่ต้องพิมพ์</h4>
    </div>
</div>

<div class="row">
    <div class="col-4">
        <div class="card">
            <div class="card-body">


<ul class="list-inline">

    <li><?= Html::a('<i class="fa-regular fa-file-word"></i> ขออนุมัติแต่งตั้ง กก. กำหนดรายละเอียด', ['/ms-word/purchase_1'], ['class' => 'open-modal-x', 'data' => ['size' => 'modal-xl']]) ?></li>
    <li><?= Html::a('<i class="fa-regular fa-file-word"></i> ขอความเห็นชอบและรายงานผล', ['/ms-word/purchase_2'], ['class' => 'open-modal-x', 'data' => ['size' => 'modal-xl']]) ?></li>
    <li><?= Html::a('<i class="fa-regular fa-file-word"></i> ขออนุมัติจัดซื้อจัดจ้าง', ['/ms-word/purchase_3'], ['class' => 'open-modal-x', 'data' => ['size' => 'modal-xl']]) ?></li>
    <li><?= Html::a('<i class="fa-regular fa-file-word"></i> รายการคุณลักษณะพัสดุ', ['/ms-word/purchase_4'], ['class' => 'open-modal-x', 'data' => ['size' => 'modal-xl']]) ?></li>
    <li><?= Html::a('<i class="fa-regular fa-file-word"></i> บันทึกข้อความรายงานการขอซื้อ', ['/ms-word/purchase_5'], ['class' => 'open-modal-x', 'data' => ['size' => 'modal-xl']]) ?></li>
    <li><?= Html::a('<i class="fa-regular fa-file-word"></i> รายงานผลการพิจารณาและขออนุมัติสั่งซื้อสั่งจ้าง', ['/ms-word/purchase_6'], ['class' => 'open-modal-x', 'data' => ['size' => 'modal-xl']]) ?></li>
    <li><?= Html::a('<i class="fa-regular fa-file-word"></i> ประกาศผู้ชนะการเสนอราคา', ['/ms-word/purchase_7'], ['class' => 'open-modal-x', 'data' => ['size' => 'modal-xl']]) ?></li>
    <li><?= Html::a('<i class="fa-regular fa-file-word"></i> ใบสั่งซื้อ/สั่งจ้าง', ['/ms-word/purchase_8'], ['class' => 'open-modal-x', 'data' => ['size' => 'modal-xl']]) ?></li>
    <li><?= Html::a('<i class="fa-regular fa-file-word"></i> ใบตรวจรับการจัดซื้อ/จัดจ้าง', ['/ms-word/purchase_9'], ['class' => 'open-modal-x', 'data' => ['size' => 'modal-xl']]) ?></li>
    <li><?= Html::a('<i class="fa-regular fa-file-word"></i> รายงานผลการตรวจรับ', ['/ms-word/purchase_10'], ['class' => 'open-modal-x', 'data' => ['size' => 'modal-xl']]) ?></li>
    <li><?= Html::a('[<i class="fa-regular fa-file-word"></i> แบบแสดงความบริสุทธิ์ใจ', ['/ms-word/purchase_11'], ['class' => 'open-modal-x', 'data' => ['size' => 'modal-xl']]) ?></li>
    <li><?= Html::a('<i class="fa-regular fa-file-word"></i> ขออนุมัติจ่ายเงินบำรุง', ['/ms-word/purchase_12'], ['class' => 'open-modal-x', 'data' => ['size' => 'modal-xl']]) ?></li>
    <li><?= Html::a('<i class="fa-regular fa-file-word"></i> ทะเบียนทรัพย์สิน', ['/ms-word/asset'], ['class' => 'open-modal-x', 'data' => ['size' => 'modal-xl']]) ?></li>
    <li><?= Html::a('ตัวอย่าง', ['/ms-word/example'], ['class' => 'open-modal-x', 'data' => ['size' => 'modal-xl']]) ?></li>
    <li><?= Html::a('ใบขอซื้อ', ['/ms-word/bill'], ['class' => 'open-modal-x', 'data' => ['size' => 'modal-xl']]) ?></li>
    <li><?= Html::a('Stockcard', ['/ms-word/stockcard','id' => 1], ['class' => 'open-modal-x', 'data' => ['size' => 'modal-xl']]) ?></li>

</ul>

</div>
        </div>
        
</div>
    <div class="col-8">
        <div class="card">
            <div class="card-body" id="showFrame">
            
            </div>
        </div>
        
    </div>
</div>

<?php
$urlGetFrame = Url::to(['/ms-word/view']);
$js = <<< JS

    \$('.open-modal-x').click(function (e) { 
        var url = \$(this).attr('href')
        e.preventDefault();
        \$.ajax({
            type: "get",
            url: url,
            dataType: "json",
            beforeSend: function() {
                \$('#showFrame').html('<h1 class="text-center">กำลังโหลด...</h1>')
        },
            success: function (res) {
                \$('#showFrame').html(res.content)
            }
        });
        
    });
    // getFrame()
    // function getFrame(){
    //     \$.ajax({
    //         type: "get",
    //         url: "$urlGetFrame",
    //         dataType: "json",
    //         success: function (res) {
    //             \$('#showFrame').html(res.content)
    //         }
    //     });
    // }
    \$('.download-file').click(function (e) { 
        e.preventDefault();
        // beforLoadModal()
        // window.location.href = \$(this).attr('href');
        \$(this).attr({ target: "_blank",
           href:\$(this).attr('href')
           });
        
    });

    JS;
$this->registerJs($js, View::POS_END);
?>