<div id="dashboard" class="page-section active">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2><i class="fas fa-tachometer-alt me-2 text-primary"></i>แดชบอร์ด</h2>
                            <div class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                วันที่ 15 ธันวาคม 2567
                            </div>
                        </div>

                        <!-- Stats Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="stat-card">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="text-muted mb-1">วัสดุทั้งหมด</h6>
                                            <div class="stat-number">1,247</div>
                                        </div>
                                        <div class="text-primary fs-1">
                                            <i class="fas fa-boxes"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="text-muted mb-1">ประเภทวัสดุ</h6>
                                            <div class="stat-number">89</div>
                                        </div>
                                        <div class="text-success fs-1">
                                            <i class="fas fa-layer-group"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="text-muted mb-1">วัสดุใกล้หมด</h6>
                                            <div class="stat-number text-warning">15</div>
                                        </div>
                                        <div class="text-warning fs-1">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="text-muted mb-1">รออนุมัติ</h6>
                                            <div class="stat-number text-info">7</div>
                                        </div>
                                        <div class="text-info fs-1">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Chart -->
                        <div class="chart-container">
                            <h5 class="mb-3"><i class="fas fa-chart-bar me-2"></i>การเบิก/รับวัสดุรายเดือน</h5>
                            <canvas id="monthlyChart" height="515" width="1545" style="display: block; box-sizing: border-box; height: 257px; width: 772px;"></canvas>
                        </div>

                        <!-- Recent Activities -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="table-container">
                                    <h6 class="mb-3"><i class="fas fa-history me-2"></i>กิจกรรมล่าสุด</h6>
                                    <div class="list-group list-group-flush">
                                        <div class="list-group-item d-flex align-items-center">
                                            <i class="fas fa-arrow-down text-success me-3"></i>
                                            <div>
                                                <div class="fw-bold">รับวัสดุเข้าคลัง</div>
                                                <small class="text-muted">กระดาษ A4 - 500 แผ่น</small>
                                            </div>
                                            <small class="text-muted ms-auto">10:30</small>
                                        </div>
                                        <div class="list-group-item d-flex align-items-center">
                                            <i class="fas fa-arrow-up text-danger me-3"></i>
                                            <div>
                                                <div class="fw-bold">ตัดจ่ายวัสดุ</div>
                                                <small class="text-muted">ปากกาลูกลื่น - 20 ด้าม</small>
                                            </div>
                                            <small class="text-muted ms-auto">09:15</small>
                                        </div>
                                        <div class="list-group-item d-flex align-items-center">
                                            <i class="fas fa-check text-info me-3"></i>
                                            <div>
                                                <div class="fw-bold">อนุมัติการเบิก</div>
                                                <small class="text-muted">ใบเบิก #WH-2024-001</small>
                                            </div>
                                            <small class="text-muted ms-auto">08:45</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="table-container">
                                    <h6 class="mb-3"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>แจ้งเตือนวัสดุใกล้หมด</h6>
                                    <div class="list-group list-group-flush">
                                        <div class="list-group-item stock-alert">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <div class="fw-bold">กระดาษ A4</div>
                                                    <small class="text-muted">คงเหลือ: 50 แผ่น</small>
                                                </div>
                                                <span class="badge bg-danger">วิกฤต</span>
                                            </div>
                                        </div>
                                        <div class="list-group-item stock-alert">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <div class="fw-bold">ปากกาลูกลื่น</div>
                                                    <small class="text-muted">คงเหลือ: 15 ด้าม</small>
                                                </div>
                                                <span class="badge bg-warning">ต่ำ</span>
                                            </div>
                                        </div>
                                        <div class="list-group-item stock-alert">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <div class="fw-bold">ลวดเย็บกระดาษ</div>
                                                    <small class="text-muted">คงเหลือ: 25 กล่อง</small>
                                                </div>
                                                <span class="badge bg-warning">ต่ำ</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>