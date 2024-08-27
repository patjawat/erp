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
								<h3 class="page-title">หมวด อาคารสิ่งปลูกสร้าง</h3>
								
							</div>
							<div class="col-auto float-end ms-auto">
								<a href="#" class="btn add-btn" data-bs-toggle="modal" data-bs-target="#add_building"><i class="fa fa-plus"></i> Add New </a>
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
											<th>ประเภทอาคารสิ่งปลูกสร้าง</th>
											<th>หมวดพัสดุ</th>
											<th>ประเภทพัสดุ</th>
											<th>รายละเอียด</th>
                                            <th>สถานะ</th>
											<th class="text-end">Action</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>1</td>
											<td>2000-852</td>
											<td>อาคารพักแพทย์ 10 ครอบครัว</td>
											<td>อาคารถาวร</td>
											<td>อาคาร / สิ่งปลูกสร้าง</td>
                                            <td>อาคาร คสล. 3 ชั้น พื้นที่ใช้สอยประมาณ 748 ตารางเมตร</td>
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
														<a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#edit_building"><i class="fa fa-pencil m-r-5"></i> Edit</a>
														<a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#delete_building"><i class="fa fa-trash-o m-r-5"></i> Delete</a>
													</div>
												</div>
											</td>
										</tr>


										<tr>
											<td>2</td>
											<td>099-003-0002</td>
											<td>โรงจอดรถ</td>
											<td>สิ่งปลูกสร้าง</td>
											<td>อาคาร / สิ่งปลูกสร้าง</td>
                                            <td>โรงจอดรถผู้รับบริการ</td>
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
														<a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#edit_building"><i class="fa fa-pencil m-r-5"></i> Edit</a>
														<a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#delete_building"><i class="fa fa-trash-o m-r-5"></i> Delete</a>
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
						<div class="modal custom-modal fade" id="add_building" role="dialog">
					<div class="modal-dialog modal-dialog-centered modal-xl" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title">เพิ่ม หมวดอาคารสิ่งปลูกสร้าง</h5>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body">
								<form>
								<div class="form-group">
										<label>ปลูกบนที่ดิน :<span class="text-danger">*</span></label>
										<input class="form-control" type="text">
									</div>
									<div class="form-group">
										<label>อัตราเสื่อม <span class="text-danger">*</span></label>
										<input class="form-control" type="text">
									</div>
									<div class="form-group">
										<label>ชื่อสิ่งปลูกสร้าง <span class="text-danger">*</span></label>
										<input class="form-control" type="text">
									</div>
									<div class="form-group">
										<label>	งบประมาณ <span class="text-danger">*</span></label>
										<input class="form-control" type="text">
									</div>
									<div class="form-group">
										<label>วิธีได้มา <span class="text-danger">*</span></label>
										<input class="form-control" type="text">
									</div>
									<div class="form-group">
										<label>วิธีการซื้อ <span class="text-danger">*</span></label>
										<input class="form-control" type="text">
									</div>
									<div class="form-group">
										<label>ใช้งบประมาณ <span class="text-danger">*</span></label>
										<input class="form-control" type="text">
									</div>
									<div class="form-group">
										<label>จำนวน <span class="text-danger">*</span></label>
										<input class="form-control" type="text">
									</div>
									<div class="form-group">
										<label>อายุการใช้งาน <span class="text-danger">*</span></label>
										<input class="form-control" type="text">
									</div>
									<div class="form-group">
										<label>ผู้รับผิดชอบ <span class="text-danger">*</span></label>
										<input class="form-control" type="text">
									</div>
									<div class="form-group">
										<label>วิศวกร <span class="text-danger">*</span></label>
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
				<div class="modal custom-modal fade" id="edit_building" role="dialog">
				<div class="modal-dialog modal-dialog-centered modal-xl" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title">แก้ไข หมวดอาคารสิ่งปลูกสร้าง</h5>
								<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body">
								<form>
									<div class="form-group">
										<label>ปลูกบนที่ดิน :<span class="text-danger">*</span></label>
										<input class="form-control" type="text">
									</div>
									<div class="form-group">
										<label>อัตราเสื่อม <span class="text-danger">*</span></label>
										<input class="form-control" type="text">
									</div>
									<div class="form-group">
										<label>ชื่อสิ่งปลูกสร้าง <span class="text-danger">*</span></label>
										<input class="form-control" type="text">
									</div>
									<div class="form-group">
										<label>	งบประมาณ <span class="text-danger">*</span></label>
										<input class="form-control" type="text">
									</div>
									<div class="form-group">
										<label>วิธีได้มา <span class="text-danger">*</span></label>
										<input class="form-control" type="text">
									</div>
									<div class="form-group">
										<label>วิธีการซื้อ <span class="text-danger">*</span></label>
										<input class="form-control" type="text">
									</div>
									<div class="form-group">
										<label>ใช้งบประมาณ <span class="text-danger">*</span></label>
										<input class="form-control" type="text">
									</div>
									<div class="form-group">
										<label>จำนวน <span class="text-danger">*</span></label>
										<input class="form-control" type="text">
									</div>
									<div class="form-group">
										<label>อายุการใช้งาน <span class="text-danger">*</span></label>
										<input class="form-control" type="text">
									</div>
									<div class="form-group">
										<label>ผู้รับผิดชอบ <span class="text-danger">*</span></label>
										<input class="form-control" type="text">
									</div>
									<div class="form-group">
										<label>วิศวกร <span class="text-danger">*</span></label>
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
				<div class="modal custom-modal fade" id="delete_building" role="dialog">
					<div class="modal-dialog modal-dialog-centered">
						<div class="modal-content">
							<div class="modal-body">
								<div class="form-header">
									<h3>ลบข้อมูลอาคารสิ่งปลูกสร้าง</h3>
									<p>ท่านแน่ใจที่จะลบข้อมูลอาคารสิ่งปลูกสร้าง ?</p>
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