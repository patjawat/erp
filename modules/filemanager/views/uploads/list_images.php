<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use app\modules\filemanager\components\FileManagerHelper;
?>

<div class="image-grid-container">

<div class="row g-3"> <?php foreach ($uploads as $file): ?>
   <?php
            // ตรวจสอบนามสกุลไฟล์ (ปรับตาม property ที่คุณมี เช่น $file->extension หรือ $file->file_name)
            $ext = strtolower(pathinfo($file->file_name ?? '', PATHINFO_EXTENSION)); 
            // หรือถ้ามี field เก็บประเภทอยู่แล้วให้ใช้ตัวนั้นครับ
        ?>
        <div class="col-6 col-md-3 col-lg-2"> 
            <a class="image-link" href="<?=Url::to(['/filemanager/uploads/show','id' => $file->id]) ?>">
                <?= Html::img('@web/img/loading.gif', [
                    'class' => 'lazyload img-fluid', // img-fluid ทำให้รูปกว้างพอดีคอลัมน์
                    'data' => [
                        'src' => FileManagerHelper::getImg($file->id)
                    ],
                    'style'=>"aspect-ratio: 1/1; object-fit:cover; width:100%;"
                ]) ?>
            </a>
        </div>
    <?php endforeach; ?>
</div>

</div>
<?php
// ลงทะเบียน CSS/JS ของ Magnific (หรือใส่ใน AppAsset)
$this->registerJsFile('@app/libs/magnific-popup/jquery.magnific-popup.js', ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerCssFile('@app/libs/magnific-popup/magnific-popup.css');

$js = <<<JS

// $('.image-link').click(function (e) { 
//    $("#main-modal").modal("toggle");
    
// });
$('.image-grid-container').magnificPopup({
    delegate: 'a', // เลือก tag <a>
    type: 'image',
    gallery: {
      enabled: true // เปิดใช้งานโหมด Gallery เลื่อนซ้าย-ขวาได้
    }
});
JS;
$this->registerJs($js,View::POS_END);
?>