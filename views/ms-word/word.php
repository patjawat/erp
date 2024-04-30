<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\View;
?>


<?php // Html::a('<i class="fa-solid fa-cloud-arrow-down"></i> ดาวน์โหลดเอกสาร', Url::to(Yii::getAlias('@web') . '/msword/results/'.$filename.'.docx'), ['class' => 'btn btn-primary text-center mb-3','target' => '_blank','onclick' =>'return closeModal()'])?>
<!-- <iframe src='https://view.officeapps.live.com/op/embed.aspx?src=<?=Url::to(Yii::getAlias('@web') . '/msword/results/'.$filename, true);?>' width='100%' height='1000px' frameborder='0'>
</iframe> -->
    <iframe src="https://docs.google.com/gview?url=<?=Url::to(Yii::getAlias('@web') . '/msword/results/'.$filename, true);?>&embedded=true" width='100%' height='1000px frameborder="0"></iframe>

<?php
$js = <<< JS
$('.download-file').click(function (e) { 
    e.preventDefault();
    // beforLoadModal()
    // window.location.href = $(this).attr('href');
    $(this).attr({ target: "_blank",
       href:$(this).attr('href')
       });
    
});

JS;
$this->registerJs($js,View::POS_END);
?>