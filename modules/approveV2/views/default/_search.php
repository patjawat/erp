<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\approve\models\ApproveSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="approve-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
    <div class="d-flex flex-row gap-2">

        <?= $this->render('@app/components/ui/_date_start',['form' => $form,'model' => $model,'label' => false])?>

        <?= $this->render('@app/components/ui/_date_end',['form' => $form,'model' => $model,'label' => false])?>
        <div style="width:200px">
            <?=$this->render('@app/components/ui/input_emp',['form' => $form,'model' => $model,'label' => false,'placeholder' => $emp_label])?>
        </div>
        <?= $form->field($model, 'status')->dropDownList(
            [
                'Pending' => 'รอเห็นชอบ',
                'Pass' => 'เห็นชอบ',
                'Reject' => 'ไม่เห็นชอบ',
            ],
            [
                'prompt' => '--- เลือกสถานะ ---', // ข้อความเริ่มต้น
                'class' => 'form-select shadow-sm', // ใช้คลาส Bootstrap 5
            ]
        )->label(false) ?>
         <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', ['class' => 'btn btn-primary']);?>
          <?= Html::button('<i class="fa-solid fa-check"></i> อนุมัติที่เลือก', [
                'class' => 'btn btn-success btn-approve-reject',
                'type' => 'button',
                'data-status' => 'Pass' // สำหรับส่งไป controller
            ]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>