<?php
use yii\bootstrap5\Html;
use yii\helpers\Url;
use app\modules\sm\components\SmHelper;
?>

    <?php if(SmHelper::GetInitailSm()):?>
								<li> <?=html::a('<i class="fa-solid fa-angles-left"></i> <span>กลับหน้าหลัก',['/sm/default/clear']);?></li>		
	<?php else:?>
								<li> <?=html::a('<i class="fa-solid fa-angles-left"></i> <span>กลับหน้าหลัก',['/']);?></li>
								<li> <?=html::a('<i class="fa-solid fa-cube"></i> <span>การจัดซื้อจัดจ้าง</span>',['/sm/order']);?></li>
								<li> <?=html::a('<i class="fa-solid fa-cube"></i> <span>รายละเอียดการจัดซื้อจัดจ้าง</span>',['/sm/buy']);?></li>
								<!-- <li> <?=html::a('<i class="fa-solid fa-cube"></i> <span>จัดซื้อจัดจ้าง</span>',['/sm/buy']);?></li> -->
								<li> <?=html::a('<i class="fa-solid fa-cube"></i> <span>ขายทอดตลาด</span>',['/sm/sell']);?></li>
								<li class="menu-title"> 
									<span>ตั้งค่าข้อมูล</span>
								</li>
								<li> <?=html::a('<i class="fa-solid fa-building"></i> <span>อาคารสิ่งปลูกสร้าง</span>',['/sm/building']);?></li>
								<li> <?=html::a('<i class="fa-solid fa-desktop"></i> <span>ครุภัณฑ์</span>',['/sm/object']);?></li>
								<li> <?=html::a('<i class="fa-solid fa-archive"></i> <span>วัสดุ</span>',['/sm/material']);?></li>
								<li> <?=html::a('<i class="fa-solid fa-handshake-o"></i> <span>บริการ</span>',['/sm/service']);?></li>
	<?php endif;?>