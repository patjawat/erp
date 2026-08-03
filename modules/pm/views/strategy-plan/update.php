<?php
use yii\helpers\Html;
$this->title = 'แก้ไขชุดแผนยุทธศาสตร์';
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'strategy']) ?><?php $this->endBlock();
?>
<div class="mb-4"><h1 class="h3 mb-1">แก้ไขชุดแผนยุทธศาสตร์</h1><p class="text-muted mb-0">แก้ไขได้เฉพาะฉบับร่าง</p></div>
<?= $this->render('_form', ['model' => $model]) ?>
