<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\backoffice\models\PersonSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="person-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'ID') ?>

    <?= $form->field($model, 'FINGLE_ID') ?>

    <?= $form->field($model, 'HR_CID') ?>

    <?= $form->field($model, 'HR_PREFIX_ID') ?>

    <?= $form->field($model, 'HR_FNAME') ?>

    <?php // echo $form->field($model, 'HR_LNAME') ?>

    <?php // echo $form->field($model, 'HR_EN_NAME') ?>

    <?php // echo $form->field($model, 'PAY') ?>

    <?php // echo $form->field($model, 'SEX') ?>

    <?php // echo $form->field($model, 'HR_BLOODGROUP_ID') ?>

    <?php // echo $form->field($model, 'HR_MARRY_STATUS_ID') ?>

    <?php // echo $form->field($model, 'HR_BIRTHDAY') ?>

    <?php // echo $form->field($model, 'HR_PHONE') ?>

    <?php // echo $form->field($model, 'HR_EMAIL') ?>

    <?php // echo $form->field($model, 'HR_FACEBOOK') ?>

    <?php // echo $form->field($model, 'HR_LINE') ?>

    <?php // echo $form->field($model, 'HR_HOME_NUMBER') ?>

    <?php // echo $form->field($model, 'HR_VILLAGE_NO') ?>

    <?php // echo $form->field($model, 'HR_ROAD_NAME') ?>

    <?php // echo $form->field($model, 'HR_SOI_NAME') ?>

    <?php // echo $form->field($model, 'PROVINCE_ID') ?>

    <?php // echo $form->field($model, 'AMPHUR_ID') ?>

    <?php // echo $form->field($model, 'TUMBON_ID') ?>

    <?php // echo $form->field($model, 'HR_VILLAGE_NAME') ?>

    <?php // echo $form->field($model, 'HR_ZIPCODE') ?>

    <?php // echo $form->field($model, 'HR_RELIGION_ID') ?>

    <?php // echo $form->field($model, 'HR_NATIONALITY_ID') ?>

    <?php // echo $form->field($model, 'HR_CITIZENSHIP_ID') ?>

    <?php // echo $form->field($model, 'HR_DEPARTMENT_ID') ?>

    <?php // echo $form->field($model, 'HR_DEPARTMENT_SUB_ID') ?>

    <?php // echo $form->field($model, 'HR_POSITION_ID') ?>

    <?php // echo $form->field($model, 'HR_FARTHER_NAME') ?>

    <?php // echo $form->field($model, 'HR_FARTHER_CID') ?>

    <?php // echo $form->field($model, 'HR_MATHER_NAME') ?>

    <?php // echo $form->field($model, 'HR_MATHER_CID') ?>

    <?php // echo $form->field($model, 'HR_STATUS_ID') ?>

    <?php // echo $form->field($model, 'HR_LEVEL_ID') ?>

    <?php // echo $form->field($model, 'HR_IMAGE') ?>

    <?php // echo $form->field($model, 'HR_USERNAME') ?>

    <?php // echo $form->field($model, 'HR_PASSWORD') ?>

    <?php // echo $form->field($model, 'DATE_TIME_UPDATE') ?>

    <?php // echo $form->field($model, 'DATE_TIME_CREATE') ?>

    <?php // echo $form->field($model, 'HR_STARTWORK_DATE') ?>

    <?php // echo $form->field($model, 'HR_WORK_REGISTER_DATE') ?>

    <?php // echo $form->field($model, 'HR_WORK_END_DATE') ?>

    <?php // echo $form->field($model, 'HR_PIC') ?>

    <?php // echo $form->field($model, 'HR_POSITION_NUM') ?>

    <?php // echo $form->field($model, 'HR_SALARY') ?>

    <?php // echo $form->field($model, 'MONEY_POSITION') ?>

    <?php // echo $form->field($model, 'IP_INSERT') ?>

    <?php // echo $form->field($model, 'IP_UPDATE') ?>

    <?php // echo $form->field($model, 'PCODE') ?>

    <?php // echo $form->field($model, 'PERSON_TYPE') ?>

    <?php // echo $form->field($model, 'PCODE_MAIN') ?>

    <?php // echo $form->field($model, 'USER_TYPE') ?>

    <?php // echo $form->field($model, 'HR_HIGH') ?>

    <?php // echo $form->field($model, 'HR_WEIGHT') ?>

    <?php // echo $form->field($model, 'PERMIS_ID') ?>

    <?php // echo $form->field($model, 'VCODE') ?>

    <?php // echo $form->field($model, 'VCODE_DATE') ?>

    <?php // echo $form->field($model, 'VGROUP_ID') ?>

    <?php // echo $form->field($model, 'NICKNAME') ?>

    <?php // echo $form->field($model, 'HR_PERSON_TYPE_ID') ?>

    <?php // echo $form->field($model, 'POSITION_IN_WORK') ?>

    <?php // echo $form->field($model, 'BOOK_BANK_NUMBER') ?>

    <?php // echo $form->field($model, 'BOOK_BANK_NAME') ?>

    <?php // echo $form->field($model, 'BOOK_BANK') ?>

    <?php // echo $form->field($model, 'BOOK_BANK_BRANCH') ?>

    <?php // echo $form->field($model, 'HR_DATE_PUT') ?>

    <?php // echo $form->field($model, 'HR_HOME_NUMBER_1') ?>

    <?php // echo $form->field($model, 'HR_HOME_NUMBER_2') ?>

    <?php // echo $form->field($model, 'HR_ROAD_NAME_1') ?>

    <?php // echo $form->field($model, 'HR_ROAD_NAME_2') ?>

    <?php // echo $form->field($model, 'HR_VILLAGE_NO_1') ?>

    <?php // echo $form->field($model, 'HR_VILLAGE_NO_2') ?>

    <?php // echo $form->field($model, 'HR_VILLAGE_NAME_1') ?>

    <?php // echo $form->field($model, 'HR_VILLAGE_NAME_2') ?>

    <?php // echo $form->field($model, 'PROVINCE_ID_1') ?>

    <?php // echo $form->field($model, 'PROVINCE_ID_2') ?>

    <?php // echo $form->field($model, 'AMPHUR_ID_1') ?>

    <?php // echo $form->field($model, 'AMPHUR_ID_2') ?>

    <?php // echo $form->field($model, 'TUMBON_ID_1') ?>

    <?php // echo $form->field($model, 'TUMBON_ID_2') ?>

    <?php // echo $form->field($model, 'HR_ZIPCODE_1') ?>

    <?php // echo $form->field($model, 'HR_ZIPCODE_2') ?>

    <?php // echo $form->field($model, 'HR_HOME_PHONE_1') ?>

    <?php // echo $form->field($model, 'HR_HOME_PHONE_2') ?>

    <?php // echo $form->field($model, 'SAME_ADDR_1') ?>

    <?php // echo $form->field($model, 'SAME_ADDR_2') ?>

    <?php // echo $form->field($model, 'HR_BANK_ID') ?>

    <?php // echo $form->field($model, 'HR_FINGLE1') ?>

    <?php // echo $form->field($model, 'HR_FINGLE2') ?>

    <?php // echo $form->field($model, 'HR_FINGLE3') ?>

    <?php // echo $form->field($model, 'LICEN') ?>

    <?php // echo $form->field($model, 'BOOK_BANK_OT_NUMBER') ?>

    <?php // echo $form->field($model, 'BOOK_BANK_OT_NAME') ?>

    <?php // echo $form->field($model, 'HR_BANK_OT_ID') ?>

    <?php // echo $form->field($model, 'BOOK_BANK_OT') ?>

    <?php // echo $form->field($model, 'BOOK_BANK_OT_BRANCH') ?>

    <?php // echo $form->field($model, 'MARRY_CID') ?>

    <?php // echo $form->field($model, 'MARRY_NAME') ?>

    <?php // echo $form->field($model, 'HR_DEPARTMENT_SUB_SUB_ID') ?>

    <?php // echo $form->field($model, 'HOS_USE_CODE') ?>

    <?php // echo $form->field($model, 'HR_KIND_ID') ?>

    <?php // echo $form->field($model, 'HR_KIND_TYPE_ID') ?>

    <?php // echo $form->field($model, 'LINE_NAME') ?>

    <?php // echo $form->field($model, 'LINE_TOKEN') ?>

    <?php // echo $form->field($model, 'LINE_TOKEN1') ?>

    <?php // echo $form->field($model, 'LINE_TOKEN2') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'HR_IMAGE_NAME') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'HR_AGENCY_ID') ?>

    <?php // echo $form->field($model, 'LEAVEDAY_ACTIVE') ?>

    <?php // echo $form->field($model, 'HR_SOI_NAME_1') ?>

    <?php // echo $form->field($model, 'HR_SOI_NAME_2') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
