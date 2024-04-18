<?php
use app\components\UserHelper;
use yii\bootstrap5\Html;
use yii\widgets\Pjax;

$this->title = 'ข้อมูลพื้นฐาน';

$this->params['breadcrumbs'][] = ['label' => 'พนักงาน', 'url' => ['index']];
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
												<div class="col-md-4">
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
												</div>
												<div class="col-md-8">
													<div class="row">
														<div class="col-md-2">
															<div class="nomal doj ">หน่วยงานต้นสังกัด</div>
															<div class="nomal doj ">หน่วยงานภายใน</div>
														</div>
														<div class="col-md-10">
															<div class="nomal doj ">รพ.อุบลรัตน์ สสจ.ขอนแก่น สำนักงานปลัดกระทรวงสาธารณสุข</div>
															<div class="nomal doj ">ศูนย์คอมพิวเตอร์</div>
														</div>
													</div>

													<div class=row>
														<div class="col-md-6">
															<div class=row>
																<div class="col-md-6">
																	<div class="nomal doj ">วันที่บรรจุเข้ารับราชการ</div>
																	<div class="nomal doj ">วันเข้าสู่หน่วยงานปัจจุบัน</div>
																	<div class="nomal doj ">วันครบเกษียณ</div>
																	<div class="nomal doj ">วุฒิในตำแหน่ง</div>
																	<div class="nomal doj ">สถานะปฏิบัติงาน</div>
																	<div class="nomal doj ">กบข.</div>
																</div>
																<div class="col-md-6">
																	<div class="nomal doj ">01/08/2566 </div>
																	<div class="nomal doj ">01/01/2558 </div>
																	<div class="nomal doj ">01/10/2573 </div>
																	<div class="nomal doj ">ปริญญาตรีสาธารสุขศาสตร์บัณฑิต</div>
																	<div class="nomal doj ">ดำรงตำแหน่ง </div>
																	<div class="nomal doj ">สมัคร </div>
																</div>
															</div>
														</div>
														<div class="col-md-6">
															<div class=row>
																<div class="col-md-6">
																	<div class="nomal doj ">อายุราชการ</div>
																	<div class="nomal doj ">ระยะเวลา</div>
																	<div class="nomal doj ">ระยะเวลาครบเกษียณ</div>
																</div>
																<div class="col-md-6">
																	<div class="nomal doj ">7 ปี 1 เดือน 16 วัน </div>
																	<div class="nomal doj ">5 ปี 1 เดือน 16 วัน</div>
																	<div class="nomal doj ">5 ปี 1 เดือน 16 วัน</div>
																</div>
															</div>
														</div>
													</div>
													
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
