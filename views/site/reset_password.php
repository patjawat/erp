<?php
use app\components\SiteHelper;
use yii\bootstrap5\ActiveForm;
$this->title = "รีเซ็ตรหัสผ่าน";

?>
<div class="sign-in-from">
                            <h1 class="mb-0">รีเซ็ตรหัสผ่าน</h1>
                            <?php $form = ActiveForm::begin([
                        'id' => 'form-reset'
                        ]); ?>

                                <div class="form-group">
                                    <?= $form->field($model, 'password')->passwordInput()->label('กำหนดรหัสผ่าน') ?>
                                    <?= $form->field($model, 'password_')->passwordInput()->label('ยืนรหัสผ่าน') ?>
                                    <?= $form->field($model, 'token')->hiddenInput()->label(false) ?>
                                </div>

                                <div class="d-inline-block w-100">
                                    <?=SiteHelper::BtnSave('ตกลง')?>
                                </div>
                                <?php ActiveForm::end(); ?>
                        </div>

                        <div class="d-flex justify-content-center gap-5 banner-container">
    <div data-aos="fade-up" data-aos-delay="400">

        <?=Html::img('@web/banner/banner1.jpg',['style'=> 'width:150px'])?>
    </div>
    <div data-aos="fade-up" data-aos-delay="500">
        <?=Html::img('@web/banner/banner2.png',['style'=> 'width:100px'])?>
    </div>
    <div data-aos="fade-up" data-aos-delay="600">
        <?=Html::img('@web/banner/banner3.png',['style'=> 'width:100px'])?>
    </div>

</div>
