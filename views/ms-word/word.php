<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\View;
?>


<?=Html::a('<i class="fa-solid fa-cloud-arrow-down"></i> ดาวน์โหลดเอกสาร', Url::to(Yii::getAlias('@web') . '/msword/temp/'.$filename.'.docx'), ['class' => 'btn btn-primary text-center mb-3','target' => '_blank','onclick' =>'return closeModal()'])?>
<iframe src='https://view.officeapps.live.com/op/embed.aspx?src=<?=Url::to(Yii::getAlias('@web') . '/msword/temp/'.$filename.'.docx', true);?>' width='100%' height='1000px' frameborder='0'>
</iframe>


<!-- </p><iframe src="https://docs.google.com/viewerng/viewer?url='<?php // Url::to(Yii::getAlias('@web') . '/msword/template_care_result.docx', true);?>'&embedded=true"  style="position: absolute;width:100%; height:800px;border: none;"></iframe> -->


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