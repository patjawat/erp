<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\AppHelper;

$title = '<i data-lucide="heart-pulse"></i> ข้อมูลประวัติการตรวจสุขภาพ';
?>



<div id="health-list"></div>
<?php
$url = \yii\helpers\Url::to(['/health/health-screen/list-me']);
$js = <<<JS
loadHealthList();
    function loadHealthList() {
        $.ajax({
            type: "get",
            url: "$url",
            dataType: "json",
            success: function (response) {
                $('#health-list').html(response.content);
            }
        });
    }
JS;
$this->registerJs($js);
?>