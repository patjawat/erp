<?php
use app\modules\employees\models\Employees;
// use app\themes\assets\AppAsset;
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\web\View;
use yii\widgets\MaskedInput;
// $assets = AppAsset::register($this);
?>
<div class="container">

    <div class="row justify-content-center mt-5">
        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="sign-in-from">

                <h4 class="text-center mb-3 text-white">ลงทะเบียน</h4>
                <?php $form = ActiveForm::begin(['id' => 'form-signup','enableAjaxValidation' => false,]); ?>
                <div class="row">
                    <div class="col-6">
                        <?= $form->field($model, 'fname')->textInput(['autofocus' => true,'class' => 'form-control form-control-lg rounded-pill border-0'])->label('ชื่อ') ?>
                        <?= $form->field($model, 'username')->textInput(['autofocus' => true,'class' => 'form-control form-control-lg rounded-pill border-0'])->label('ชื่อเข้าใช้งาน') ?>
                        <?= $form->field($model, 'email')->textInput(['class' => 'form-control form-control-lg rounded-pill border-0'])->label('อีเมล') ?>
                    </div>
                    <div class="col-6">
                        <?= $form->field($model, 'lname')->textInput(['autofocus' => true,'class' => 'form-control form-control-lg rounded-pill border-0'])->label('นาสกุล') ?>
                        <?= $form->field($model, 'password')->passwordInput(['value' => '112233','class' => 'form-control form-control-lg rounded-pill border-0'])->label('รหัสผ่าน') ?>
                        <?= $form->field($model, 'phone')->textInput(['class' => 'form-control form-control-lg rounded-pill border-0'])->label('โทรศัพท์') ?>
                    </div>
                </div>

                <div class="d-inline-block w-100">
                    <div class="d-flex justify-content-between">

                        <div class="custom-control custom-checkbox d-inline-block mt-2 pt-1">
                            <input type="checkbox" class="custom-control-input" id="customCheck1" disabled="true">
                            <label class="custom-control-label text-white" for="customCheck1">ฉันยอมรับ
                                <?=Html::a('ข้อกำหนดและเงื่อนไข',['/site/conditions-register'],['class' => 'open-modal text-white']);?>
                            </label>
                        </div>

                        <div class="sign-info mt-2">
                    <span class="dark-color d-inline-block line-height-2 text-white">มีบัญชีอยู่แล้ว | 
                        <?=Html::a('เข้าสู่ระบบ',['site/login'],['class' => 'text-white'])?></span>
                </div>

                    </div>
                    <!-- <button type="submit" class="btn btn-primary float-right" id="btn-regster">Sign Up</button> -->
                    <div class="d-grid gap-2 mt-3">
                        <button class="btn btn-lg btn-primary account-btn rounded-pill" id="btn-regster" type="submit">
                            <i class="fa-solid fa-circle-check"></i> ลงทะเบียน</button>
                    </div>
                </div>
              
                <?php ActiveForm::end(); ?>

            </div>
        </div>
    </div>
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


<style>
#form-signup {
    /* width: 300px; */
    font-family: "kanit"
}

.form-label {
    margin-bottom: 0.5rem;
    color: #fff;
}

#form-signup input {
    background-color: #8495e2;;
    color: #fff;
}

.banner-container {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translate(-50%, -50%);

}

.banner>img {
    /* width: 120px; */
}

.account-page .main-wrapper .account-content .account-box {
    background-color: #ffffff;
    box-shadow: 3px 7px 20px 0px rgb(0 0 0 / 13%);
    margin: 0 auto;
    overflow: hidden;
    width: 480px;
    border-radius: 20px;
}

svg#clouds[_ngcontent-ng-c105734841] {
    position: fixed;
    bottom: -170px;
    left: -200px;
    z-index: -10;
    width: 2600px;
}
</style>

<svg data-aos="fade-up" data-aos-delay="300" _ngcontent-ng-c105734841="" id="clouds" xmlns="http://www.w3.org/2000/svg"
    width="2611.084" height="485.677" viewBox="0 0 2611.084 485.677">
    <title _ngcontent-ng-c105734841="">Gray Clouds Background</title>
    <path _ngcontent-ng-c105734841="" id="Path_39" data-name="Path 39"
        d="M2379.709,863.793c10-93-77-171-168-149-52-114-225-105-264,15-75,3-140,59-152,133-30,2.83-66.725,9.829-93.5,26.25-26.771-16.421-63.5-23.42-93.5-26.25-12-74-77-130-152-133-39-120-212-129-264-15-54.084-13.075-106.753,9.173-138.488,48.9-31.734-39.726-84.4-61.974-138.487-48.9-52-114-225-105-264,15a162.027,162.027,0,0,0-103.147,43.044c-30.633-45.365-87.1-72.091-145.206-58.044-52-114-225-105-264,15-75,3-140,59-152,133-53,5-127,23-130,83-2,42,35,72,70,86,49,20,106,18,157,5a165.625,165.625,0,0,0,120,0c47,94,178,113,251,33,61.112,8.015,113.854-5.72,150.492-29.764a165.62,165.62,0,0,0,110.861-3.236c47,94,178,113,251,33,31.385,4.116,60.563,2.495,86.487-3.311,25.924,5.806,55.1,7.427,86.488,3.311,73,80,204,61,251-33a165.625,165.625,0,0,0,120,0c51,13,108,15,157-5a147.188,147.188,0,0,0,33.5-18.694,147.217,147.217,0,0,0,33.5,18.694c49,20,106,18,157,5a165.625,165.625,0,0,0,120,0c47,94,178,113,251,33C2446.709,1093.793,2554.709,922.793,2379.709,863.793Z"
        transform="translate(142.69 -634.312)" fill="#ffff"></path>
</svg>


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