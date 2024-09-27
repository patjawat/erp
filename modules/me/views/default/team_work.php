<?php

use yii\web\View;

?>

<div class="card" style="height:300px;">
    <div class="card-body">
        <h6>กลุ่ม/ทีมประสาน</h6>
    </div>
</div>
<?php
$js = <<< JS

JS;
$this->registerJs($js,View::POS_END);
?>