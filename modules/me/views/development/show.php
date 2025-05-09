<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;

?>

    <iframe src="https://view.officeapps.live.com/op/embed.aspx?src=<?= Url::to(Yii::getAlias('@web') . '/msword/results/development/' . $filename . '?t=' . time(), true); ?>" width='100%' height='1000px' frameborder="0"></iframe>

    <!-- <iframe src="https://docs.google.com/gview?url=<?= Url::to(Yii::getAlias('@web') . '/msword/results/development/' . $filename, true); ?>&embedded=true" width='100%' height='1000px' frameborder="0"></iframe> -->

<?php
$js = <<< JS

   
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