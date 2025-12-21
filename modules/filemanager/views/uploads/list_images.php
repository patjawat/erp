<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use app\modules\filemanager\components\FileManagerHelper;
?>
<div class="image-grid-container d-flex gap-3">
    <?php foreach ($uploads as $file): ?>

        <a class="image-link" href="<?=Url::to(['/filemanager/uploads/show','id' => $file->id]) ?>">
        <?= Html::img('@web/img/loading.gif', [
                    'class' => 'lazyload',
                    'data' => [
                        'expand' => '-20',
                        'sizes' => 'auto',
                        'src' => FileManagerHelper::getImg($file->id)
                    ],
                    'style'=>"width:250px; height:250px; object-fit:cover;"
                ]) ?>
        </a>
    <?php endforeach; ?>
</div>
<?php
// ลงทะเบียน CSS/JS ของ Magnific (หรือใส่ใน AppAsset)
$this->registerJsFile('@app/libs/magnific-popup/jquery.magnific-popup.js', ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerCssFile('@app/libs/magnific-popup/magnific-popup.css');

$js = <<<JS

$('.image-link').click(function (e) { 
   $("#main-modal").modal("toggle");
    
});
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