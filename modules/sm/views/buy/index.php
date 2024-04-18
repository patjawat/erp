<?php
/** @var yii\web\View $this */

use app\modules\sm\components\SmHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$sm = SmHelper::GetInitailSm();


?>
<style>

.button {
  display: inline-block;
  padding: 15px 25px;
  font-size: 15px;
  cursor: pointer;
  text-align: center;
  text-decoration: none;
  outline: none;
  color: #fff;
  background-color: #04AA6D;
  border: none;
  border-radius: 15px;
  box-shadow: 0 9px #999;
}

.button:hover {background-color: #3e8e41}

.button:active {
  background-color: #3e8e41;
  box-shadow: 0 5px #666;
  transform: translateY(4px);
}
</style>
<body>

			<div class="card mb-0">
						<div class="card-body">
							<div class="row">
								<div class="col-md-12">
									<div class="profile-view">
										<div class="profile-basic">
										   <div class="card border-0">
												<div class="card-body">
													<h5 class="card-title"><i class="fa-solid fa-file"></i> ทะเบียนคุม 66-3103</h5>
													<table class="table border-0 table-striped-columns">
														<tbody>
															<tr>
																<td scope="row">ปีงบประมาณ</td>
																<td>2566</td>
																<td>อ้างอิงทะเบียนขอซื้อ/จ้าง</td>
																<td>-</td>
															
															</tr>
															<tr>
																<td scope="row">วันที่</td>
																<td>25/09/2566</td>
																<td>หน่วยงาน</td>
																<td>ศูนย์เทคโนโลยีสารสนเทศทางการแพทย์</td>
															</tr>
															<tr>
																<td scope="row">ผู้ร้องขอ</td>
																<td>นายจิตสง่า เพชรสุวรรณ</td>
																<td></td>
																<td></td>
															</tr>
														</tbody>
													<tfoot>
													</tfoot>
												</table>
									</div>
								</div>	
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row">&nbsp;</div>
						
					<div class="row">
                        <div class="col-lg-10"> 
							<div class="tab-content profile-tab-content">
								<!-- Projects Tab -->
								<div id="registration" class="tab-pane fade show active">
								<div class="row">
												<div class="col-md-6">
													<div class="card">
														<div class="card-header">
															<h4 class="card-title mb-0">บันทึกข้อความ</h4>
														</div>
														<div class="card-body">
															<form action="#">
																<div class="form-group">
																	<label>ส่วนราชการ</label>
																	<input type="text" class="form-control">
																</div>
																<div class="form-group">
																	<label>ปฏิบัติราชการแทน</label>
																	<input type="text" class="form-control">
																</div>
																<div class="form-group">
																	<label>คำสั่งจังหวัดเลขที่</label>
																	<input type="text" class="form-control">
																</div>
																<div class="form-group">
																	<label>ลงวันที่</label>
																	<input type="text" class="form-control">
																</div>
																<div class="form-group">
																	<label>หนังสือเรียน</label>
																	<input type="text" class="form-control">
																</div>
															
															</form>
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="card">
														<div class="card-header">
															<h4 class="card-title mb-0">แผนงานโครงการ</h4>
														</div>
														<div class="card-body">
															<form action="#">
																<div class="form-group">
																	<label>โครงการเลขที่</label>
																	<input type="text" class="form-control">
																</div>
																<div class="form-group">
																	<label>ชื่อโครงการ</label>
																	<input type="text" class="form-control">
																</div>
																<div class="form-group">
																	<label>รหัสอ้างอิง EGP</label>
																	<input type="text" class="form-control">
																</div>
																<div class="form-group">
																	<label>การเบิกจ่ายเงิน</label>
																	<input type="text" class="form-control">
																</div>
																<div class="form-group">
																	<label>รายการแผน EGP</label>
																	<input type="text" class="form-control">
																</div>
															
															</form>
														</div>
													</div>
												</div>
											</div>
											<div class="row">&nbsp;</div>
											<div class="row">
												<div class="col-md-6">
													<div class="card">
														<div class="card-header">
															<h4 class="card-title mb-0">ผู้อนุมัติเห็นชอบ</h4>
														</div>
														<div class="card-body">
															<form action="#">
																<div class="form-group">
																	<label>ชื่อผู้อนุมัติจ่าย</label>
																	<input type="text" class="form-control">
																</div>
																<div class="form-group">
																	<label>เจ้าหน้าที่พัสดุ</label>
																	<input type="text" class="form-control">
																</div>
																<div class="form-group">
																	<label>หัวหน้าเจ้าหน้าที่</label>
																	<input type="text" class="form-control">
																</div>
															</form>
														</div>
													</div>
												</div>
												<div class="col-md-6">
													<div class="card">
														<div class="card-header">
															<h4 class="card-title mb-0">อ้างอิงคำสั่ง</h4>
														</div>
														<div class="card-body">
															<form action="#">
																<div class="form-group">
																	<label>คำสั่ง</label>
																	<input type="text" class="form-control">
																</div>
																<div class="form-group">
																	<label>เลขที่คำสั่ง</label>
																	<input type="text" class="form-control">
																</div>
																<div class="form-group">
																	<label>ลงวันที่</label>
																	<input type="text" class="form-control">
																</div>
															</form>
														</div>
													</div>
												</div>
											</div>
											<div class="row">&nbsp;</div>
											<div class="row">
											<div class="col-md-12">
													<div class="card">
														<div class="card-header">
															<h4 class="card-title mb-0">วิธีการจัดซื้อจัดจ้าง</h4>
														</div>
														<div class="card-body">
															<form action="#">
															<div class="form-group">
																			<label>หัวเรื่องการจัดซื้อจัดจ้าง</label>
																			<input type="text" class="form-control">
																		</div>
															<div class="row">
																<div class="col-md-6">

																		<div class="form-group">
																			<label>วิธีซื้อหรือจ้าง</label>
																			<input type="text" class="form-control">
																		</div>
																		<div class="form-group">
																			<label>วิธีจัดหา</label>
																			<input type="text" class="form-control">
																		</div>
																		<div class="form-group">
																			<label>การพิจารณา</label>
																			<input type="text" class="form-control">
																		</div>
																		<div class="form-group">
																			<label>หมวดเงิน</label>
																			<input type="text" class="form-control">
																		</div>

																</div>
																<div class="col-md-6">
																		<div class="form-group">
																			<label>เงื่อนไข</label>
																			<input type="text" class="form-control">
																		</div>

																		<div class="form-group">
																			<label>ประเภทจัดหา</label>
																			<input type="text" class="form-control">
																		</div>
																		<div class="form-group">
																			<label>วันที่ต้องการ</label>
																			<input type="text" class="form-control">
																		</div>
																		<div class="form-group">
																			<label>ประเภทเงิน</label>
																			<input type="text" class="form-control">
																		</div>
																</div>

															</div>
																		<div class="form-group">
																			<label>เหตุผลการจัดหา</label>
																			<input type="text" class="form-control">
																		</div>
																		<div class="form-group">
																			<label>เหตุผลความจำเป็น</label>
																			<input type="text" class="form-control">
																		</div>

															
															</form>
														</div>
													</div>
												</div>

											</div>
														</div>
														<!-- /Projects Tab -->

														<!-- Task Tab -->
														<div id="director" class="tab-pane fade">

															<div class="row">
																<div class="col-lg-12">
																	<div class="card">
																		<div class="card-header">
																			<h4 class="card-title mb-0">กรรมการตรวจรับ</h4>
																		</div>
																		<div class="card-body">
																			<div class="table-responsive">
																				<table class="table table-bordered mb-0">
																					<thead>
																						<tr>
																							<th>คณะกรรมการ</th>
																							<th>ตำแหน่ง</th>
																						</tr>
																					</thead>
																					<tbody>
																						<tr>
																							<td>นายจิตสง่า เพชรสุวรรณ</td>
																							<td>ประธาน</td>
																						</tr>
																						<tr>
																							<td>นายกิตติพงษ์ ไสโสมา</td>
																							<td>กรรมการ</td>
																						</tr>
																					</tbody>
																				</table>
																			</div>
																		</div>
																	</div>
																</div>
															</div>
															<div class="row">&nbsp;</div>
															<div class="row">
																<div class="col-lg-12">
																	<div class="card">
																		<div class="card-header">
																			<h4 class="card-title mb-0">กรรมการกำหนดรายละเอียด</h4>
																		</div>
																		<div class="card-body">
																			<div class="table-responsive">
																				<table class="table table-bordered mb-0">
																					<thead>
																						<tr>
																							<th>คณะกรรมการ</th>
																							<th>ตำแหน่ง</th>
																						</tr>
																					</thead>
																					<tbody>
																					<tr>
																							<td>นายจิตสง่า เพชรสุวรรณ</td>
																							<td>ประธาน</td>
																						</tr>
																						<tr>
																							<td>นายกิตติพงษ์ ไสโสมา</td>
																							<td>กรรมการ</td>
																						</tr>
																					</tbody>
																				</table>
																			</div>
																		</div>
																	</div>
																</div>
															</div>

														</div>
														<!-- /Task Tab -->


														<!-- Task Tab -->
														<div id="orderlist" class="tab-pane fade">
														<div class="row">
															<div class="col-md-6">
																<div class="card">
																	<div class="card-header">
																		<h4 class="card-title mb-0">รายละเอียด</h4>
																	</div>
																	<div class="card-body">
																		<form action="#">
																			<div class="form-group">
																				<label>ประเภทจัดหา</label>
																				<input type="text" class="form-control">
																			</div>
																			<div class="form-group">
																				<label>เหตุผลความจำเป็น</label>
																				<input type="text" class="form-control">
																			</div>
																			<div class="form-group">
																				<label>เลขที่ใบเสนอราคา</label>
																				<input type="text" class="form-control">
																			</div>
																			<div class="form-group">
																				<label>บริษัท</label>
																				<input type="text" class="form-control">
																			</div>
																			<div class="form-group">
																				<label>เลขภาษี</label>
																				<input type="text" class="form-control">
																			</div>
																			<div class="form-group">
																				<label>ยอดนำเสนอ</label>
																				<input type="text" class="form-control">
																			</div>
																		
																		</form>
																	</div>
																</div>
															</div>
														
															<div class="col-md-6">
																<div class="card">
																	<div class="card-header">
																		<h4 class="card-title mb-0">ภาษีและส่วนลด</h4>
																	</div>
																	<div class="card-body">
																		<form action="#">
																			<div class="form-group">
																				<label>ภาษี</label>
																				<input type="text" class="form-control">
																			</div>
																			<div class="form-group">
																				<label>ส่วนลด</label>
																				<input type="text" class="form-control">
																			</div>
																			<div class="form-group">
																				<label>มูลค่าสินค้า</label>
																				<input type="text" class="form-control">
																			</div>
																			<div class="form-group">
																				<label>เปอร์เซ็นภาษี</label>
																				<input type="text" class="form-control">
																			</div>
																		
																			<div class="form-group">
																				<label>เป็นเงิน</label>
																				<input type="text" class="form-control">
																			</div>
																			<div class="form-group">
																				<label>รวมราคาสุทธิ</label>
																				<input type="text" class="form-control">
																			</div>

																		</form>
																	</div>
																</div>
															</div>
														</div>
														<div class="row">&nbsp;</div>
														<div class="row">
																<div class="col-lg-12">
																	<div class="card">
																		<div class="card-header">
																			<h4 class="card-title mb-0">รายการสั่งซื้อ  
																			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	
																			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																			ราคารวม  4,400.00  บาท</h4>
																		</div>
																		<div class="card-body">
																			<div class="table-responsive">
																				<table class="table table-bordered mb-0">
																					<thead>
																						<tr>
																							<th style="text-align: center;">ลำดับ</th>
																							<th style="text-align: center;">รายการและรายละเอียดที่ต้องการ</th>
																							<th style="text-align: center;">จำนวน</th>
																							<th style="text-align: center;">หน่วย</th>
																							<th style="text-align: center;">ราคา</th>
																							<th style="text-align: center;">รวม</th>
																						</tr>
																					</thead>
																					<tbody>
																					<tr>
																							<td>1</td>
																							<td>น้ำดื่มบรรจุ</td>
																							<td>200</td>
																							<td>ถัง</td>
																							<td>15.00</td>
																							<td>3,000.00</td>
																						</tr>
																						<tr>
																							<td>2</td>
																							<td>เกลือ</td>
																							<td>50</td>
																							<td>ถุง</td>
																							<td>20.00</td>
																							<td>1,000.00</td>
																						</tr>
																						<tr>
																							<td>3</td>
																							<td>กะปิ</td>
																							<td>20</td>
																							<td>ขวด</td>
																							<td>20.00</td>
																							<td>400.00</td>
																						</tr>
																					</tbody>
																				</table>
																			</div>
																		</div>
																	</div>
																</div>
															</div>


														</div>
														<!-- /Task Tab -->

														<!-- Task Tab -->
														<div id="inspection" class="tab-pane fade">
														<div class="row">
																<div class="col-lg-12">
																	<div class="card">
																		<div class="card-header">
																			<h4 class="card-title mb-0">รายละเอียดการตรวจรับ</h4>
																		</div>
																		<div class="card-body">
																			<div class="table-responsive">
																			<div class="card-body">
																		<form action="#">
																			<div class="form-group">
																				<label>กำหนดส่ง</label>
																				<input type="text" class="form-control">
																			</div>
																			<div class="form-group">
																				<label>เลขที่ใบส่งของ</label>
																				<input type="text" class="form-control">
																			</div>
																			<div class="form-group">
																				<label>มูลค่าสินค้า</label>
																				<input type="text" class="form-control">
																			</div>
																			<div class="form-group">
																				<label>ใบเสนอราคาเลขที่</label>
																				<input type="text" class="form-control">
																			</div>
																		
																			<div class="form-group">
																				<label>วันที่ตรวจรับ</label>
																				<input type="text" class="form-control">
																			</div>
																			<div class="form-group">
																				<label>เวลาตรวจรับ</label>
																				<input type="text" class="form-control">
																			</div>
																			<div class="form-group">
																				<label>ผลการตรวจสอบ</label>
																				<input type="text" class="form-control">
																			</div>
																			<div class="form-group">
																				<label>เจ้าหน้าที่ตรวจรับ</label>
																				<input type="text" class="form-control">
																			</div>
																			<div class="form-group">
																				<label>ค่าปรับ</label>
																				<input type="text" class="form-control">
																			</div>

																		</form>
																	</div>
																			</div>
																		</div>
																	</div>
																</div>
															</div>


														</div>
														<!-- /Task Tab -->

																		<!-- Task Tab -->
																		<div id="document" class="tab-pane fade">
														<div class="row">
																<div class="col-lg-12">
																	<div class="card">
																		<div class="card-header">
																			<h4 class="card-title mb-0">เอกสารจัดซ์้อจัดจ้าง</h4>
																		</div>
																		<div class="card-body">
																			<div class="table-responsive">
																				<div class="card-body">
																	
																				<div class="row">
																						<div class="col-lg-6">
																							<button class="btn btn-primary submit-btn">ขออนุมัติแต่งตั้ง กก.กำหนดรายละเอียด</button>
																						</div>
																						<div class="col-lg-6">
																							<button class="btn btn-primary submit-btn">ประกาศผู้ชนะ</button>
																						</div>
																				</div>
																				<br>
																				<div class="row">
																						<div class="col-lg-6">
																							<button class="btn btn-primary submit-btn">ขอความเห็นชอบและรายงานผล</button>
																						</div>
																						<div class="col-lg-6">
																							<button class="btn btn-primary submit-btn">ใบสั่งซื้อ สั่งจ้าง</button>
																						</div>
																				</div>
																				<br>
																				<div class="row">
																						<div class="col-lg-6">
																							<button class="btn btn-primary submit-btn">ขออนุมัติจัดซื้อจัดจ้าง</button>
																						</div>
																						<div class="col-lg-6">
																							<button class="btn btn-primary submit-btn">ใบตรวจรับ</button>
																						</div>
																				</div>
																				<br>
																				<div class="row">
																						<div class="col-lg-6">
																							<button class="btn btn-primary submit-btn">คุณลักษณะพัสดุ</button>
																						</div>
																						<div class="col-lg-6">
																							<button class="btn btn-primary submit-btn">รายงานผลการตรวจรับ</button>
																						</div>
																				</div>
																				<br>
																				<div class="row">
																						<div class="col-lg-6">
																							<button class="btn btn-primary submit-btn">บันทึกข้อความรายงานการขอซื้อ</button>
																						</div>
																						<div class="col-lg-6">
																							<button class="btn btn-primary submit-btn">แบบแสดงความบริสุทธิ์ใจ</button>
																						</div>
																				</div>
																				<br>
																				<div class="row">
																						<div class="col-lg-6">
																							<button class="btn btn-primary submit-btn">ผลการพิจารณาและขออนุมัติสั่งซื้อสั่งจ้าง</button>
																						</div>
																						<div class="col-lg-6">
																							<button class="btn btn-primary submit-btn">ขออนุมัติจ่ายเงินบำรุง</button>
																						</div>
																				</div>

																				</div>
																			</div>
																		</div>
																	</div>
																</div>
															</div>


														</div>
														<!-- /Task Tab -->
													</div>
												</div>
											<div class="col-lg-2">
												<div class="card">
													<div class="row">
														<div class="col-lg-12 col-md-12 col-sm-12 line-tabs">
															<ul class="nav flex-column">
																<li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#registration">ข้อมูลทะเบียนคุม</a></li>
																<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#director">กรรมการ</a></li>
																<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#orderlist">รายการสั่งซื้อ</a></li>
																<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#inspection">การตรวจรับ</a></li>
																<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#document">แบบฟร์อมการจัดซื้อ</a></li>
															</ul>

															<center><button class="btn btn-primary submit-btn">บันทึกข้อมูลทะเบียนคุม</button></center><br>
															
														</div>
														
													</div>
													
												</div>
											</div>	

										</div>


					
					


	
</body>          