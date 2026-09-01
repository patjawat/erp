<?php
use yii\helpers\Html;
$label=$group==='deduction'?'รายการจ่าย':'รายการรับ';
$this->title='เพิ่ม'.$label;
$this->beginBlock('page-title'); ?><div class="d-flex align-items-center gap-2"><i class="bi bi-plus-circle fs-4"></i><h4 class="mb-0"><?= Html::encode($this->title) ?></h4></div><?php $this->endBlock();
$this->beginBlock('sub-title'); ?>เพิ่มชื่อรายการเพียงครั้งเดียว แล้วจึงกำหนดรายชื่อบุคลากร<?php $this->endBlock();
$this->beginBlock('page-action'); echo $this->render('@app/modules/finance/menu',['active'=>'payroll']); $this->endBlock();
?>
<?= $this->render('_menu',['active'=>$group==='compensation'?'compensation-income':($group==='deduction'?'monthly-expense':'monthly-income')]) ?>
<section class="card shadow-sm"><div class="card-header bg-body px-3 px-md-4 py-3"><h5 class="mb-0"><?= Html::encode($this->title) ?></h5></div>
<?= Html::beginForm(['save-item-type'],'post') ?><input type="hidden" name="group" value="<?= Html::encode($group) ?>"><div class="card-body"><label class="form-label" for="item-name">ชื่อ<?= Html::encode($label) ?></label><input class="form-control" id="item-name" name="name" maxlength="255" required autofocus placeholder="เช่น เงินเดือน, ค่า พ.ต.ส., ค่า ฉ.11"></div><div class="card-footer bg-body d-flex justify-content-end gap-2"><?= Html::a('ยกเลิก',['employee-items','group'=>$group],['class'=>'btn btn-outline-secondary']) ?><button class="btn btn-primary" type="submit">บันทึก</button></div><?= Html::endForm() ?></section>
