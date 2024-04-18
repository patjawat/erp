<?php
use app\modules\employees\models\Employees;
use app\themes\assets\AppAsset;
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\web\View;
$assets = AppAsset::register($this);




?>
    <div class="row mt-5">
        <div class="col-6">
            <div class="sign-in-from">
                <h1 class="mb-0">ลงทะเบียน</h1>
                <?php $form = ActiveForm::begin(['id' => 'form-signup','enableAjaxValidation' => false,]); ?>
                <div class="row">
                    <div class="col-6">
                        <?= $form->field($model, 'fname')->textInput(['autofocus' => true,'value' => 'ปัจวัฒน์'])->label('ชื่อ') ?>
                    </div>
                    <div class="col-6">
                        <?= $form->field($model, 'lname')->textInput(['autofocus' => true,'value' => 'ศรีบุญเรือง'])->label('นาสกุล') ?>
                    </div>
                </div>
            
                <?= $form->field($model, 'username')->textInput(['autofocus' => true,'value' => 'patjawat']) ?>
                <?= $form->field($model, 'password')->passwordInput(['value' => '112233']) ?>

                <?= $form->field($model, 'email')->textInput(['value' => 'patjawat@local.com']) ?>
                <?= $form->field($model, 'phone')->textInput(['value' => '909748048']) ?>

                <div class="d-inline-block w-100">
                    <div class="custom-control custom-checkbox d-inline-block mt-2 pt-1">
                        <input type="checkbox" class="custom-control-input" id="customCheck1" disabled="true">
                            <label class="custom-control-label" for="customCheck1">ฉันยอมรับ
                                <?=Html::a('ข้อกำหนดและเงื่อนไข',['/site/conditions-register'],['class' => 'open-modal']);?>
                            </label>
                    </div>
                    <button type="submit" class="btn btn-primary float-right" id="btn-regster">Sign Up</button>
                </div>
                <div class="sign-info">
                    <span class="dark-color d-inline-block line-height-2">Already Have Account ?
                        <?=Html::a('เข้าสู่ระบบ',['site/login'])?></span>
                    <ul class="iq-social-media">
                        <li><a href="#"><i class="ri-facebook-box-line"></i></a></li>
                        <li><a href="#"><i class="ri-twitter-line"></i></a></li>
                        <li><a href="#"><i class="ri-instagram-line"></i></a></li>
                    </ul>
                </div>
                <?php ActiveForm::end(); ?>

            </div>
        </div>
    </div>


<?php
$js = <<< JS

$("#customCheck1").change(function() {
    var ischecked= $(this).is(':checked');
    if(!ischecked)
    // $('#btn-regster,#customCheck1').prop('disabled', true)
}); 

$('#form-signup').on('beforeSubmit', function (e) {
   e.preventDefault();
    var ischecked= $('#customCheck1').is(':checked');
    console.log(ischecked);
    // if(!ischecked){
    //     alert('คุณไม่ได้ยอมรับข้อกำหนดและเงื่อนไข')
    //     return false;
    // }else{
        $.ajax({
            type: "post",
            url: $(this).attr('href'),
            data: $(this).serialize(),
            dataType: "json",
            success: function (response) {
                if(!response.status)
                console.log(response);
                warning();
            }
        });
    // }
    return false;
});

JS;
$this->registerJs($js,View::POS_END);
?>