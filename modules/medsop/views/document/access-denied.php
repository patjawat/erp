<?php use yii\helpers\Html; $this->title = 'ไม่สามารถเปิดเอกสารได้'; ?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<div class="card shadow-sm"><div class="card-body text-center py-5" role="alert"><h2 class="h5 fw-semibold">เอกสารนี้อยู่นอกขอบเขตสิทธิ์ของคุณ</h2><p class="text-body-secondary">ผู้ใช้งานทั่วไปเปิดได้เฉพาะเอกสารที่เผยแพร่แล้วและเป็นของแผนกที่สังกัด</p><?= Html::a('กลับคลังเอกสาร', ['index'], ['class' => 'btn btn-primary']) ?></div></div>
