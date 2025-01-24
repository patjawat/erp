<?php
use yii\web\View;
use yii\helpers\Url;
echo "<pre>";
print_r($model->user->line_id);
echo "</pre>";

?>
<span class="send-msg">Send Msg</span>
<?php
$url = Url::to(['/hr/employees/send-msg','id' => $model->id]);
$js = <<< JS

$('.send-msg').click(function (e) { 
    e.preventDefault();
    $.ajax({
        type: "get",
        url: "$url",
        dataType: "json",
        success: function () {
            console.log(res);
        }
    });
    
});
JS;
$this->registerJs($js,View::POS_END);
?>