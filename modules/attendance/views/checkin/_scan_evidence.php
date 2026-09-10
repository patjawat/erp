<?php
use yii\helpers\Html;
$data=is_array($model->data_json)?$model->data_json:[];
$raw=$data['raw_scan']??null;
if (!$raw) return;
?>
<section class="mt-3 p-3 border rounded-3">
<h2 class="h6">เวลาสแกนต้นฉบับ</h2>
<p class="mb-1"><?= Html::encode($raw['at']??'') ?></p>
<p class="small text-body-secondary mb-0">เก็บหลักฐานเวลาที่รับครั้งแรก การแก้เวลาและการจับคู่เวรมีประวัติแยกต่างหาก</p>
</section>
