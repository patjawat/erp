<?php
/** @var yii\web\View $this */
use yii\helpers\Html;
?>
<h1><i class="fa-solid fa-user-shield"></i> ระบบยืนยันตัวตน</h1>

<p>
   กรุณาทำการลงทะเบียนเพื่อยืนยันตัวตัวและทำการเข้าสู่ระบบ
     <code><?=Html::a('ลงทะเบียน',['/auth/signup']) ?></code>.
</p>

<p>
   หามีข้อมูลแล้วกรุณายืนยันตัวตน
     <code><?=Html::a('เข้าสู่ระบบ',['/auth/login']) ?></code>.
</p>