<?php
use app\components\UserHelper;
use yii\bootstrap5\Html;
use yii\widgets\Pjax;

$this->title = 'ข้อมูลพื้นฐาน';

$this->params['breadcrumbs'][] = ['label' => 'บุคลากร', 'url' => ['/employees']];
$this->params['breadcrumbs'][] = $this->title;


$user  = UserHelper::GetUser();
?>


<div class="card mb-0">
						<div class="card-body">
							<div class="row">
								<div class="col-md-12">
									<div class="profile-view">
										<div class="profile-img-wrap">
											
											<?=UserHelper::Avatar()?>
											

										</div>
										<?php Pjax::begin(['id' => 'general-container']) ?>
										<div class="staff-msg">
										<div class="profile-basic">
											<div class="row">
												<!-- <div class="col-md-4">
													<div class="profile-info-left">
														<h3 class="user-name m-t-0 mb-0">จิตสง่า เพชรสุวรรณ</h3>
														<div class="row">
															<div class="col-md-4">
																<div class="nomal doj ">ตำแหน่งเลขที่ </div>
																<div class="nomal doj ">ตำแหน่งประสานงาน </div>
																<div class="nomal doj ">ตำแหน่งบริหาร </div>
																<div class="nomal doj ">ประเภท </div>
																<div class="nomal doj ">ระดับ </div>
																<div class="nomal doj ">ความเชี่ยวชาญ </div>
																<div class="nomal doj ">สถานะปฏิบัติงาน </div>
															</div>
															<div class="col-md-8">
																<div class="nomal doj ">66282</div>
																<div class="nomal doj ">นักจัดการทั่วไป</div>
																<div class="nomal doj ">-</div>
																<div class="nomal doj ">วิชาการ</div>
																<div class="nomal doj ">ชำนาญการ</div>
																<div class="nomal doj ">-</div>
																<div class="nomal doj ">ตรง จ. </div>
															</div>
		
														</div>
													</div>
												</div> -->

												<div class="col-md-5">
													<div class="profile-info-left">
														<h3 class="user-name m-t-0 mb-0">นายปัจวัฒน์ ศรีบุญเรือง</h3>
														<div class="staff-id">Mr.Patjawat Sriboonrouang</div>
														<div class="staff-id">รหัสประจำตัว ID : FT-0001</div>
														<div class="staff-id">เลขประจำตัวประชาชน : 1409900181748</div>
														<h6 class="staff-id">ชื่อตำแหน่ง : นักวิชาการคอมพิวเตอร์</h6>
														<h6 class="staff-id">ระดับตำแหน่ง : ชำนาญการพิเศษ</h6>
														<small class="staff-id">สังกัด : สำนักงานอธิการบดี</small><br>
														
														<!-- <div class="small doj text-muted">วันเดือนปีบรรจุ : 8 เมษายน พ.ศ.2565</div> -->
														<div class="staff-msg"><a class="btn btn-custom" href="chat.html"><i class="fa-solid fa-print"></i> พิมพ์ [PDF]</a></div>
													</div>
												</div>

												<div class="col-md-7">

												<ul class="personal-info">

												
														<li>
															<div class="title">สถานะปฏิบัติงาน:</div>
															<div class="text"><a href="">ปฏิบัติราชการ</a></div>
														</li>
														<li>
															<div class="title">ประเภทบุคลากร:</div>
															<div class="text"><a href="">ข้าราชการ (66282)</a></div>
														</li>
														<li>
															<div class="title">ประเภท:</div>
															<div class="text"><a href="">วิชาการ</a></div>
														</li>

													
														<li>
															<div class="title">วันเดือนปีบรรจุ:</div>
															<div class="text"><a href="">8 เมษายน พ.ศ.2565</a></div>
														</li>
														<!-- <li>
															<div class="title">วันครบกำหนดสัญญาจ้าง :</div>
															<div class="text"><a href="">8 เมษายน พ.ศ.2565</a></div>
														</li> -->
														<li>
															<div class="title">วันผ่านการทดลองงาน:</div>
															<div class="text">8 เมษายน พ.ศ.2565</div>
														</li>
														
														<li>
															<div class="title">เกษียณอายุราชการเมื่อ:</div>
															<div class="text">8 เมษายน พ.ศ.2565</div>
														</li>
														
														
														<li>
															<div class="title">อายุราชการ:</div>
															<div class="text">1 ปี 5 เดือน 3 วัน</div>
														</li>

														<li>
															<div class="title">วันออกจากราชการ:</div>
															<div class="text">1 ปี 5 เดือน 3 วัน</div>
														</li>
														
														
													</ul>


													

													
													
												</div>

												</div>
											</div>
										</div>

										<?php Pjax::end() ?>
										<div class="pro-edit">
											<?=Html::a('<i class="fas fa-pencil-alt"></i>',['/profile/form-general'],['class' => 'edit-icon open-modal']);?>
											<!-- <a data-bs-target="#profile_info" data-bs-toggle="modal"  class="edit-icon open-modal" href="#"><i class="fas fa-pencil-alt"></i></a> -->
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

<?= $this->render('tabs-box')?>
<?= $this->render('tabs-content')?>
