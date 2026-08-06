<?php
use yii\helpers\Html;
$this->title = 'สร้างชุดแผนยุทธศาสตร์';
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'strategy']) ?><?php $this->endBlock();
?>
<div class="d-flex justify-content-between align-items-start gap-3 mb-4"><div><h1 class="h3 mb-1">สร้างชุดแผนยุทธศาสตร์</h1><p class="text-muted mb-0">กำหนดกรอบเวลา วิสัยทัศน์ และข้อมูลอ้างอิงก่อนลงโครงสร้าง</p></div></div>
<?= $this->render('_form', ['model' => $model]) ?>
