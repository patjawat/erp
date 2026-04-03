<?php
use yii\helpers\Html;
use chillerlan\QRCode\QRCode;
 $data = $model->code.'|'.isset($model->data_json['asset_type_text']) ? $model->data_json['asset_type_text'] : '-'.'|'.number_format($model->price, 2).'|'.Yii::$app->thaiFormatter->asDate($model->receive_date, 'short').'|'.(isset($model->data_json['department_name']) ? $model->data_json['department_name'] : '-');
 $qr = new QRCode();
?>
<div class="d-flex align-items-center bg-primary bg-opacity-10  p-2 rounded">
    <div class="flex-shrink-0">
        <img src="<?=$qr->render($data)?>" width="140">
    </div>
    <div class="flex-grow-1 ms-3">
        <ul class="list-inline">
            <li><i class="bi bi-check2-circle text-primary fs-5"></i>
                <span class="text-danger"><?=$model->code?><span>
            </li>
            <li><i class="bi bi-check2-circle text-primary fs-5"></i>
                <?=isset($model->data_json['asset_type_text']) ? $model->data_json['asset_type_text'] : '-'?>
            <li><i class="bi bi-check2-circle text-primary fs-5"></i>
                <?=number_format($model->price, 2)?>
                :: <?=Yii::$app->thaiFormatter->asDate($model->receive_date, 'short')?>
            </li>
            <li><i class="bi bi-check2-circle text-primary fs-5"></i>
                <?php if (isset($model->data_json['department_name']) && $model->data_json['department_name'] == ''): ?>
                <?=isset($model->data_json['department_name_old']) ? $model->data_json['department_name_old'] : ''?>
                <?php else: ?>
                <?=isset($model->data_json['department_name']) ? $model->data_json['department_name'] : ''?>
                <?php endif;?>
            </li>
            <ul>
    </div>
</div>
<div class="flex gap-2 mt-3">
    <?=Html::a('<i class="fa-solid fa-print"></i> QR-Code',['#'],['class' => 'btn btn-primary'])?>
    <?=Html::a('<i class="fa-solid fa-print"></i> Barcode',['#'],['class' => 'btn btn-primary'])?>
    <?=Html::a('<i class="fa-solid fa-sliders"></i> ตั้งค่าหน้ากระดาษ',['/am/asset/qrcode-setting','title' => '<i class="fa-solid fa-sliders"></i> กำหนดขนาดกระดาษา'],['class' => 'btn btn-secondary open-modal','data' => ['size' => 'modal-lg']])?>
</div>