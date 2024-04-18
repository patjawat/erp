<?php
use yii\bootstrap5\Html;
use yii\helpers\Url;
?>
		<li> 
									<?=html::a('<i class="fa-solid fa-angles-left"></i> <span>กลับหน้าหลัก',['/']);?>
								</li>
						
							<li class="menu-title"> 
									<span>สำหรับผู้ดูแลระบบ</span>
								</li>
								<li> <?=html::a('<i class="fa-solid fa-building"></i> <span>ตั้งค่าหน่วยงาน</span>',['/settings/company']);?></li>
								<li> <?=html::a('<i class="fa-solid fa-at"></i> <span>ตั้งค่าอีเมล</span>',['/settings/email']);?></li>
							