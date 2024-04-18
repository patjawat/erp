<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h4 class="card-title"><i class="fas fa-palette"></i> ตั้งค่าสี</h4>
        </div>
        <div class="color-switchers d-flex gap-3 p-3 justify-content-center">
            <button type="button" class="switch-color btn rounded-circle border-4 border-light text-light"
                data-color-name="purple"  data-color="#6f81da" style="background-color:#6f81da;">
                A
            </button>
            <button type="button" class="switch-color btn rounded-circle border-4 border-light text-light"
                data-color-name="cyan"  data-color="#078da9"style="background-color:#078da9;">
                B
            </button>

            <button type="button" class="switch-color btn rounded-circle border-4 border-light text-light"
                data-color-name="blue" data-color="#0866ad" style="background-color:#0866ad;">
                C
            </button>

            <button type="button" class="switch-color btn rounded-circle border-4 border-light text-light"
                data-color-name="pink" data-color="#B0578D" style="background-color:#B0578D;">
                C
            </button>

        </div>
    </div>
</div>

<?php
$urlChangColor = Url::to(['/setting/set-color']);
$js = <<< JS

$('.switch-color').click(function (e) { 
    e.preventDefault();
    var color = $(this).data('color');
    var colorName = $(this).data('color-name');
    document.documentElement.setAttribute('data-bs-theme', colorName)
    $.ajax({
        type: "get",
        url: "$urlChangColor",
        data:{color:color,colorname:colorName},
        dataType: "json",
        success: function (res) {
            console.log(res);
        }
    });
});

JS;
$this->registerJS($js,View::POS_END);
?>