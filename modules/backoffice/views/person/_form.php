<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\backoffice\models\Person $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="person-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'FINGLE_ID')->textInput() ?>

    <?= $form->field($model, 'HR_CID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_PREFIX_ID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_FNAME')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_LNAME')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_EN_NAME')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'PAY')->dropDownList([ 'Y' => 'Y', 'N' => 'N', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'SEX')->dropDownList([ 'F' => 'F', 'M' => 'M', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'HR_BLOODGROUP_ID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_MARRY_STATUS_ID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_BIRTHDAY')->textInput() ?>

    <?= $form->field($model, 'HR_PHONE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_EMAIL')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_FACEBOOK')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_LINE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_HOME_NUMBER')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_VILLAGE_NO')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_ROAD_NAME')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_SOI_NAME')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'PROVINCE_ID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'AMPHUR_ID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'TUMBON_ID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_VILLAGE_NAME')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_ZIPCODE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_RELIGION_ID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_NATIONALITY_ID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_CITIZENSHIP_ID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_DEPARTMENT_ID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_DEPARTMENT_SUB_ID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_POSITION_ID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_FARTHER_NAME')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_FARTHER_CID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_MATHER_NAME')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_MATHER_CID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_STATUS_ID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_LEVEL_ID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_IMAGE')->textInput() ?>

    <?= $form->field($model, 'HR_USERNAME')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_PASSWORD')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'DATE_TIME_UPDATE')->textInput() ?>

    <?= $form->field($model, 'DATE_TIME_CREATE')->textInput() ?>

    <?= $form->field($model, 'HR_STARTWORK_DATE')->textInput() ?>

    <?= $form->field($model, 'HR_WORK_REGISTER_DATE')->textInput() ?>

    <?= $form->field($model, 'HR_WORK_END_DATE')->textInput() ?>

    <?= $form->field($model, 'HR_PIC')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_POSITION_NUM')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_SALARY')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'MONEY_POSITION')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'IP_INSERT')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'IP_UPDATE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'PCODE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'PERSON_TYPE')->dropDownList([ 'hos' => 'Hos', 'hos_mini' => 'Hos mini', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'PCODE_MAIN')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'USER_TYPE')->dropDownList([ 'SUPER' => 'SUPER', 'ADMIN' => 'ADMIN', 'USER' => 'USER', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'HR_HIGH')->textInput() ?>

    <?= $form->field($model, 'HR_WEIGHT')->textInput() ?>

    <?= $form->field($model, 'PERMIS_ID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'VCODE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'VCODE_DATE')->textInput() ?>

    <?= $form->field($model, 'VGROUP_ID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'NICKNAME')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_PERSON_TYPE_ID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'POSITION_IN_WORK')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'BOOK_BANK_NUMBER')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'BOOK_BANK_NAME')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'BOOK_BANK')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'BOOK_BANK_BRANCH')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_DATE_PUT')->textInput() ?>

    <?= $form->field($model, 'HR_HOME_NUMBER_1')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_HOME_NUMBER_2')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_ROAD_NAME_1')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_ROAD_NAME_2')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_VILLAGE_NO_1')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_VILLAGE_NO_2')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_VILLAGE_NAME_1')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_VILLAGE_NAME_2')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'PROVINCE_ID_1')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'PROVINCE_ID_2')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'AMPHUR_ID_1')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'AMPHUR_ID_2')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'TUMBON_ID_1')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'TUMBON_ID_2')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_ZIPCODE_1')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_ZIPCODE_2')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_HOME_PHONE_1')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_HOME_PHONE_2')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'SAME_ADDR_1')->dropDownList([ 'True' => 'True', 'False' => 'False', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'SAME_ADDR_2')->dropDownList([ 'True' => 'True', 'False' => 'False', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'HR_BANK_ID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_FINGLE1')->textInput() ?>

    <?= $form->field($model, 'HR_FINGLE2')->textInput() ?>

    <?= $form->field($model, 'HR_FINGLE3')->textInput() ?>

    <?= $form->field($model, 'LICEN')->textInput() ?>

    <?= $form->field($model, 'BOOK_BANK_OT_NUMBER')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'BOOK_BANK_OT_NAME')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_BANK_OT_ID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'BOOK_BANK_OT')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'BOOK_BANK_OT_BRANCH')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'MARRY_CID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'MARRY_NAME')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_DEPARTMENT_SUB_SUB_ID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HOS_USE_CODE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_KIND_ID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_KIND_TYPE_ID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'LINE_NAME')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'LINE_TOKEN')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'LINE_TOKEN1')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'LINE_TOKEN2')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'HR_IMAGE_NAME')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'HR_AGENCY_ID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'LEAVEDAY_ACTIVE')->dropDownList([ 'True' => 'True', 'False' => 'False', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'HR_SOI_NAME_1')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HR_SOI_NAME_2')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
