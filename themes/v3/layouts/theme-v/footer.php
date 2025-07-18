<?php
use yii\helpers\Html;
use app\components\SiteHelper;
use app\components\UserHelper;
$me = UserHelper::GetEmployee();
?>
<footer class="footer">
    <div class="container-fluid">
        <div class="d-flex justify-content-between">
            <div class="d-felx flex-column">
                <span id="date"> copyright &#169; 2024 |
                    <?=Html::a('มูลนิธิรามาธิบดี','https://www.ramafoundation.or.th/')?></span>
                <span>Version <?php echo Yii::$app->version?></span>
				<p>ยอมรับข้อตกลงความเป็นส่วนตัว <?=$me->viewPdpaData()?></p>
            </div>


            <div class="d-flex justify-content-start gap-4">
                <?=Html::img('@web/banner/banner1.png',['style'=> 'width:70px'])?>

                <?=Html::img('@web/banner/banner2.png',['style'=> 'width:40px'])?>

                <?=Html::img('@web/banner/banner3.png',['style'=> 'width:40px'])?>

            </div>
            <div class="d-flex flex-column justify-content-start">
                <span><?=SiteHelper::getInfo()['website']?></span>
                <span><?=SiteHelper::getInfo()['address']?></span>
            </div>
        </div>
    </div>
</footer>