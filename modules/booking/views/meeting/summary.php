 <!-- Summary Cards -->
 <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">การจองทั้งหมด</p>
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h2 class="fw-semibold mb-0"><?=$searchModel->countStatus()?></h2>
                                <small class="text-success">0 จากเดือนที่แล้ว</small>
                            </div>
                            <div class="icon-circle">
                                <i class="bi bi-calendar"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">การจองที่กำลังจะถึง</p>
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h2 class="fw-semibold mb-0"><?=$searchModel->upComing()?></h2>
                                
                                <small class="text-muted">ใน 7 วันข้างหน้า</small>
                            </div>
                            <div class="icon-circle">
                                <i class="bi bi-clock"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">การจองที่อนุมัติแล้ว</p>
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                            <h2 class="fw-semibold mb-0"><?=$searchModel->countStatus('Pass')?></h2>
                                <small class="text-muted">จาก <?=$searchModel->countStatus()?> การจอง</small>
                            </div>
                            <div class="icon-circle">
                                <i class="bi bi-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">การจองที่รอการอนุมัติ</p>
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                            <h2 class="fw-semibold mb-0"><?=$searchModel->countStatus('Pending')?></h2>
                                <small class="text-muted">จาก <?=$searchModel->countStatus()?> การจอง</small>
                            </div>
                            <div class="icon-circle">
                                <i class="bi bi-clock-history"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>