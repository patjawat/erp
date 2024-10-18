
<div id="viewMainCart"></div>
<?php

use yii\helpers\Url;
use yii\web\View;
$viewMainCartUrl = Url::to(['/inventory/main-stock/view-cart']);
$js = <<< JS
ViewMainCar();
async function ViewMainCar()
    {
    await $.ajax({
        type: "get",
        url: "$viewMainCartUrl",
        dataType: "json",
        success: function (res) {
            $('#viewMainCart').html(res.content)
            $('.countMainItem').html(res.countItem)
            if(res.countItem < 1){
                $("#main-modal").modal("hide");

            }
            console.log(res.countItem);
        }
    });
    }
JS;
$this->registerJS($js,View::POS_END);

?>