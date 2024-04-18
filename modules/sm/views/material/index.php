<?php
/** @var yii\web\View $this */
?>
<style>
body {
  background-color: #FFFFFF;
}
</style>
<body>

	<!-- Page Header -->
    <div class="page-header">
						<div class="row align-items-center">
							<div class="col">
								<h3 class="page-title">หมวด วัสดุ</h3>
								
							</div>
							<div class="col-auto float-end ms-auto">
								<a href="#" class="btn add-btn" data-bs-toggle="modal" data-bs-target="#add_material"><i class="fa fa-plus"></i> Add New </a>
							</div>
						</div>
					</div>
					<!-- /Page Header -->
					
					<div class="row">
						<div class="col-md-12">
							<div class="table-responsive">
								<table class="table table-striped custom-table mb-0 datatable">
									<thead>
										<tr>
											<th style="width: 30px;">#</th>
											<th>เลขพัสดุ</th>
											<th>ประเภทวัสดุ</th>
                                            <th>รายการวัสดุ</th>
											<th>หมวดวัสดุ</th>
											<th>รายละเอียด</th>
                                            <th>สถานะ</th>
											<th class="text-end">Action</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>1</td>
											<td>22-00776</td>
                                            <td>หน้ากากช่วยหายใจ (Full Face Mask) Size M</td>
                                            <td>วัสดุสิ้นเปลือง</td>	
											<td>วัสดุการแพทย์ทั่วไป</td>						
											<td>หน้ากากช่วยหายใจ (Full Face Mask) Size M</td>
											<td>
												<div class="dropdown action-label">
													<a class="btn btn-white btn-sm btn-rounded dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
														<i class="fa fa-dot-circle-o text-success"></i> Active
													</a>
													<div class="dropdown-menu">
														<a class="dropdown-item" href="#"><i class="fa fa-dot-circle-o text-success"></i> Active</a>
														<a class="dropdown-item" href="#"><i class="fa fa-dot-circle-o text-danger"></i> Inactive</a>
													</div>
												</div>
											</td>
											<td class="text-end">
												<div class="dropdown dropdown-action">
													<a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
													<div class="dropdown-menu dropdown-menu-right">
														<a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#edit_material"><i class="fa fa-pencil m-r-5"></i> Edit</a>
														<a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#delete_material"><i class="fa fa-trash-o m-r-5"></i> Delete</a>
													</div>
												</div>
											</td>
										</tr>


										<tr>
											<td>2</td>
											<td>22-00775</td>
                                            <td>หน้ากากช่วยหายใจ (Full Face Mask) Size L</td>
                                            <td>วัสดุสิ้นเปลือง</td>	
											<td>วัสดุการแพทย์ทั่วไป</td>						
											<td>หน้ากากช่วยหายใจ (Full Face Mask) Size L</td>
											<td>
												<div class="dropdown action-label">
													<a class="btn btn-white btn-sm btn-rounded dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
														<i class="fa fa-dot-circle-o text-success"></i> Active
													</a>
													<div class="dropdown-menu">
														<a class="dropdown-item" href="#"><i class="fa fa-dot-circle-o text-success"></i> Active</a>
														<a class="dropdown-item" href="#"><i class="fa fa-dot-circle-o text-danger"></i> Inactive</a>
													</div>
												</div>
											</td>
											<td class="text-end">
												<div class="dropdown dropdown-action">
													<a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
													<div class="dropdown-menu dropdown-menu-right">
														<a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#edit_material"><i class="fa fa-pencil m-r-5"></i> Edit</a>
														<a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#delete_material"><i class="fa fa-trash-o m-r-5"></i> Delete</a>
													</div>
												</div>
											</td>
										</tr>


											
											
                                    




									</tbody>
								</table>
							</div>
						</div>
					</div>
                </div>
				<!-- /Page Content -->

						<!-- Add  Modal -->
						<div class="modal custom-modal fade" id="add_material" role="dialog">
					<div class="modal-dialog modal-dialog-centered modal-xl" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title">เพิ่ม หมวดวัสดุ</h5>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body">
								<form>
									<div class="form-group">
											<label>รหัสวัสดุ :<span class="text-danger">*</span></label>
											<input class="form-control" type="text">
										</div>
										<div class="form-group">
											<label>รหัสวัสดุเดิม <span class="text-danger">*</span></label>
											<input class="form-control" type="text">
										</div>
										<div class="form-group">
											<label>รหัส TPU <span class="text-danger">*</span></label>
											<input class="form-control" type="text">
										</div>
										<div class="form-group">
											<label>	รายการพัสดุ <span class="text-danger">*</span></label>
											<input class="form-control" type="text">
										</div>
										<div class="form-group">
											<label>	ชื่อวัสดุ <span class="text-danger">*</span></label>
											<input class="form-control" type="text">
										</div>
										<div class="form-group">
											<label>	รูปแบบ <span class="text-danger">*</span></label>
											<input class="form-control" type="text">
										</div>
										<div class="form-group">
											<label>	ส่วนประกอบ <span class="text-danger">*</span></label>
											<input class="form-control" type="text">
										</div>
										<div class="form-group">
											<label>	ชื่อพ้อง 1  <span class="text-danger">*</span></label>
											<input class="form-control" type="text">
										</div>
										<div class="form-group">
											<label>	ชื่อพ้อง 2  <span class="text-danger">*</span></label>
											<input class="form-control" type="text">
										</div>
										<div class="form-group">
											<label>	ลักษณะ <span class="text-danger">*</span></label>
											<input class="form-control" type="text">
										</div>
										<div class="form-group">
											<label>	กลุ่มวัสดุคลังหลัก <span class="text-danger">*</span></label>
											<input class="form-control" type="text">
										</div>
										<div class="form-group">
											<label>	หมวดวัสดุคลังหลัก <span class="text-danger">*</span></label>
											<input class="form-control" type="text">
										</div>
										<div class="form-group">
											<label>	กลุ่มวัสดุ <span class="text-danger">*</span></label>
											<input class="form-control" type="text">
										</div>
										<div class="form-group">
											<label>	หมวดวัสดุ <span class="text-danger">*</span></label>
											<input class="form-control" type="text">
										</div>
										<div class="form-group">
											<label>	ผู้ผลิต <span class="text-danger">*</span></label>
											<input class="form-control" type="text">
										</div>
										<div class="form-group">
											<label>	ผู้จำหน่ายล่าสุด <span class="text-danger">*</span></label>
											<input class="form-control" type="text">
										</div>
										<div class="form-group">
											<label>	ต้องการสั่งซื้อ <span class="text-danger">*</span></label>
											<input class="form-control" type="text">
										</div>
										<div class="form-group">
											<label>	รายละเอียด <span class="text-danger">*</span></label>
											<input class="form-control" type="text">
										</div>
										<div class="form-group">
											<label>	จำกัดคงคลังจำนวนต่ำสุด <span class="text-danger">*</span></label>
											<input class="form-control" type="text">
										</div>
										<div class="form-group">
											<label>	จำกัดคงคลังจำนวนสูงสุด <span class="text-danger">*</span></label>
											<input class="form-control" type="text">
										</div>
										<div class="form-group">
											<label>	หน่วยนับ <span class="text-danger">*</span></label>
											<input class="form-control" type="text">
										</div>
										<div class="form-group">
											<label>	หน่วยนับบรรจุ <span class="text-danger">*</span></label>
											<input class="form-control" type="text">
										</div>
										<div class="form-group">
											<label>	หน่วยนับบรรจุขนาดใหญ่ <span class="text-danger">*</span></label>
											<input class="form-control" type="text">
										</div>
									<div class="submit-section">
										<button class="btn btn-primary submit-btn">บันทึก</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
				<!-- /Add  Modal -->
				
				<!-- Edit  Modal -->
				<div class="modal custom-modal fade" id="edit_material" role="dialog">
				<div class="modal-dialog modal-dialog-centered modal-xl" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title">แก้ไข หมวดวัสดุ</h5>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body">
							<form>
										<div class="form-group">
													<label>รหัสวัสดุ :<span class="text-danger">*</span></label>
													<input class="form-control" type="text">
												</div>
												<div class="form-group">
													<label>รหัสวัสดุเดิม </label>
													<input class="form-control" type="text">
												</div>
												<div class="form-group">
													<label>รหัส TPU </label>
													<input class="form-control" type="text">
												</div>
												<div class="form-group">
													<label>	รายการพัสดุ <span class="text-danger">*</span></label>
													<input class="form-control" type="text">
												</div>
												<div class="form-group">
													<label>	ชื่อวัสดุ <span class="text-danger">*</span></label>
													<input class="form-control" type="text">
												</div>
												<div class="form-group">
													<label>	รูปแบบ </label>
													<input class="form-control" type="text">
												</div>
												<div class="form-group">
													<label>	ส่วนประกอบ </label>
													<input class="form-control" type="text">
												</div>
												<div class="form-group">
													<label>	ชื่อพ้อง 1  </label>
													<input class="form-control" type="text">
												</div>
												<div class="form-group">
													<label>	ชื่อพ้อง 2  </label>
													<input class="form-control" type="text">
												</div>
												<div class="form-group">
													<label>	ลักษณะ </label>
													<input class="form-control" type="text">
												</div>
												<div class="form-group">
													<label>	กลุ่มวัสดุคลังหลัก </label>
													<input class="form-control" type="text">
												</div>
												<div class="form-group">
													<label>	หมวดวัสดุคลังหลัก </label>
													<input class="form-control" type="text">
												</div>
												<div class="form-group">
													<label>	กลุ่มวัสดุ </label>
													<input class="form-control" type="text">
												</div>
												<div class="form-group">
													<label>	หมวดวัสดุ </label>
													<input class="form-control" type="text">
												</div>
												<div class="form-group">
													<label>	ผู้ผลิต </label>
													<input class="form-control" type="text">
												</div>
												<div class="form-group">
													<label>	ผู้จำหน่ายล่าสุด </label>
													<input class="form-control" type="text">
												</div>
												<div class="form-group">
													<label>	ต้องการสั่งซื้อ </label>
													<input class="form-control" type="text">
												</div>
												<div class="form-group">
													<label>	รายละเอียด </label>
													<input class="form-control" type="text">
												</div>
												<div class="form-group">
													<label>	จำกัดคงคลังจำนวนต่ำสุด </label>
													<input class="form-control" type="text">
												</div>
												<div class="form-group">
													<label>	จำกัดคงคลังจำนวนสูงสุด </label>
													<input class="form-control" type="text">
												</div>
												<div class="form-group">
													<label>	หน่วยนับ </label>
													<input class="form-control" type="text">
												</div>
												<div class="form-group">
													<label>	หน่วยนับบรรจุ </label>
													<input class="form-control" type="text">
												</div>
												<div class="form-group">
													<label>	หน่วยนับบรรจุขนาดใหญ่ </label>
													<input class="form-control" type="text">
												</div>

									<div class="submit-section">
										<button class="btn btn-primary submit-btn">บันทึก</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
				<!-- /Edit  Modal -->

				<!-- Delete  Modal -->
				<div class="modal custom-modal fade" id="delete_material" role="dialog">
					<div class="modal-dialog modal-dialog-centered">
						<div class="modal-content">
							<div class="modal-body">
								<div class="form-header">
									<h3>ลบข้อมูลวัสดุ</h3>
									<p>ท่านแน่ใจที่จะลบข้อมูลวัสดุ ?</p>
								</div>
								<div class="modal-btn delete-action">
									<div class="row">
										<div class="col-6">
											<a href="javascript:void(0);" class="btn btn-primary continue-btn">ยืนยัน</a>
										</div>
										<div class="col-6">
											<a href="javascript:void(0);" data-bs-dismiss="modal" class="btn btn-primary cancel-btn">ยกเลิก</a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- /Delete  Modal -->
				
            </div>
			<!-- /Page Wrapper -->
			
        </div>


</body>          