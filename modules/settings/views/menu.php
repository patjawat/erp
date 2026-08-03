<?php
use yii\helpers\Html;
$layout = app\components\SiteHelper::getInfo()['layout'];
?>
<?php if($layout == 'horizontal'):?>
<li class="nav-item">
    <?=Html::a('<i class="fa-solid fa-house-medical-flag me-1"></i> ข้อมูลองค์กร',['/settings/company'],['class' => 'nav-link ' . (isset($active) && $active == 'company' ? 'active' : '')])?>
</li>
<li class="nav-item">
    <?=Html::a('<i class="fa-solid fa-sitemap me-1"></i> ทะเบียนหน่วยงาน',['/settings/org-unit'],['class' => 'nav-link ' . (isset($active) && $active == 'org-unit' ? 'active' : '')])?>
</li>
<li class="nav-item">
    <?=Html::a('<i class="fas fa-palette me-1"></i> ตั้งค่าสี',['/setting'],['class' => 'nav-link ' . (isset($active) && $active =='color' ? 'active' : '')])?>
</li>
<li class="nav-item">
    <?=Html::a('<i class="fa-solid fa-user-shield me-1"></i> ระบบจัดการผู้ใช้งาน',['/usermanager/user'],['class' => 'nav-link ' . (isset($active) && $active =='user' ? 'active' : '')])?>
</li>
<li class="nav-item">
    <?=Html::a('<i class="fa-brands fa-telegram me-1"></i> Telegram',['/settings/telegram'],['class' => 'nav-link ' . (isset($active) && $active =='telegram' ? 'active' : '')])?>
</li>
<li class="nav-item">
    <?=Html::a('<i class="fa-brands fa-line me-1"></i> Line-OA',['/settings/line-official'],['class' => 'nav-link ' . (isset($active) && $active =='line' ? 'active' : '')])?>
</li>
<li class="nav-item">
    <?=Html::a('<i class="fa-solid fa-user-tag me-1"></i> ตั้งค่าบุคลากร',['/hr/categorise'],['class' => 'nav-link ' . (isset($active) && $active =='line' ? 'employee' : '')])?>
</li>
<li class="nav-item">
    <?=Html::a('<i class="fa-solid fa-folder me-1"></i> ตั้งค่าประเภททรัพย์สิน',['/am/setting'],['class' => 'nav-link ' . (isset($active) && $active =='line' ? 'am-setting' : '')])?>
</li>
<li class="nav-item">
    <?=Html::a('<i class="fa-solid fa-hashtag me-1"></i> รูปแบบ FSN ครุภัณฑ์',['/am/setting/fsn-format'],['class' => 'nav-link ' . (isset($active) && $active == 'fsn-format' ? 'active' : '')])?>
</li>
<li class="nav-item">
    <?=Html::a('<i class="fa-solid fa-file-pdf me-1"></i> เทมเพลต PDF',['/pdf-template/template'],['class' => 'nav-link ' . (isset($active) && $active == 'pdf-template' ? 'active' : '')])?>
</li>
<li class="nav-item">
    <?=Html::a('<i class="fa-solid fa-arrows-rotate me-1"></i> อัปเดตระบบ',['/settings/update'],['class' => 'nav-link ' . (isset($active) && $active == 'update' ? 'active' : '')])?>
</li>
<?php endif;?>