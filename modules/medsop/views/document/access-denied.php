<?php use yii\helpers\Html; $this->title = 'ไม่สามารถเปิดเอกสารได้'; ?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<div class="surface-card"><div class="surface-card__body"><div class="empty-block" role="alert"><h2>เอกสารนี้อยู่นอกขอบเขตสิทธิ์ของคุณ</h2><p>ผู้ใช้งานทั่วไปเปิดได้เฉพาะเอกสารที่เผยแพร่แล้วและเป็นของแผนกที่สังกัด</p><?= Html::a('กลับคลังเอกสาร', ['index'], ['class' => 'btn btn-primary']) ?></div></div></div>
