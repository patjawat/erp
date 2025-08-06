<div id="dashboard-section" class="section">
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="text-primary fw-bold mb-1">
                        <i class="fas fa-tachometer-alt me-2"></i>แดshboard ภาพรวมคลังวัสดุ
                    </h2>
                    <p class="text-muted">ข้อมูลสรุปและสถิติการจัดการคลังวัสดุแบบเรียลไทม์</p>
                </div>
            </div>

            <!-- Summary Statistics -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon me-3">
                                    <i class="fas fa-boxes"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0 fw-bold text-primary">3,247</h3>
                                    <p class="text-muted mb-0">รายการวัสดุทั้งหมด</p>
                                    <small class="text-success">
                                        <i class="fas fa-arrow-up me-1"></i>+5.2% จากเดือนที่แล้ว
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card warning">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon me-3" style="background: linear-gradient(135deg, var(--warning-orange), #f59e0b);">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0 fw-bold text-warning">18</h3>
                                    <p class="text-muted mb-0">วัสดุใกล้หมด</p>
                                    <small class="text-danger">
                                        <i class="fas fa-arrow-up me-1"></i>ต้องการสั่งซื้อเพิ่ม
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card success">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon me-3" style="background: linear-gradient(135deg, var(--success-green), #10b981);">
                                    <i class="fas fa-truck-loading"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0 fw-bold text-success">142</h3>
                                    <p class="text-muted mb-0">ตัดจ่ายวันนี้</p>
                                    <small class="text-success">
                                        <i class="fas fa-check me-1"></i>ปกติ
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon me-3" style="background: linear-gradient(135deg, #8b5cf6, #a78bfa);">
                                    <i class="fas fa-warehouse"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0 fw-bold" style="color: #8b5cf6;">6</h3>
                                    <p class="text-muted mb-0">คลังที่ใช้งาน</p>
                                    <small class="text-info">
                                        <i class="fas fa-info-circle me-1"></i>ทั้งหมด 6 แห่ง
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Recent Activity -->
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-line me-2"></i>กิจกรรมล่าสุดในคลัง
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>เวลา</th>
                                            <th>รหัสวัสดุ</th>
                                            <th>ชื่อวัสดุ</th>
                                            <th>ประเภท</th>
                                            <th>จำนวน</th>
                                            <th>คลัง</th>
                                            <th>ผู้ดำเนินการ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>15:42</td>
                                            <td><strong>MT-001</strong></td>
                                            <td>กระดาษ A4</td>
                                            <td><span class="stock-out">ตัดจ่าย</span></td>
                                            <td>50 รีม</td>
                                            <td>คลังกลาง</td>
                                            <td>นายสมชาย ใจดี</td>
                                        </tr>
                                        <tr>
                                            <td>15:35</td>
                                            <td><strong>MT-045</strong></td>
                                            <td>ปากกาลูกลื่น</td>
                                            <td><span class="stock-in">รับเข้า</span></td>
                                            <td>200 ด้าม</td>
                                            <td>คลัง A</td>
                                            <td>นางสาวมาลี สวยงาม</td>
                                        </tr>
                                        <tr>
                                            <td>15:20</td>
                                            <td><strong>MT-023</strong></td>
                                            <td>แฟ้มเอกสาร</td>
                                            <td><span class="stock-out">ตัดจ่าย</span></td>
                                            <td>25 เล่ม</td>
                                            <td>คลังย่อย</td>
                                            <td>นายประยุทธ์ ขยัน</td>
                                        </tr>
                                        <tr>
                                            <td>14:58</td>
                                            <td><strong>MT-067</strong></td>
                                            <td>เครื่องเขียน</td>
                                            <td><span class="stock-in">รับเข้า</span></td>
                                            <td>100 ชุด</td>
                                            <td>คลังกลาง</td>
                                            <td>นางสุดา อุตสาห์</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card mb-3">
                        <div class="card-header bg-warning text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>แจ้งเตือนวัสดุใกล้หมด
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <strong>MT-012</strong>
                                        <br><small class="text-muted">กระดาษถ่ายเอกสาร</small>
                                        <div class="progress progress-custom mt-2" style="width: 100px;">
                                            <div class="progress-bar bg-danger" style="width: 15%"></div>
                                        </div>
                                    </div>
                                    <span class="badge bg-danger">5 รีม</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <strong>MT-034</strong>
                                        <br><small class="text-muted">ปากกาเมจิก</small>
                                        <div class="progress progress-custom mt-2" style="width: 100px;">
                                            <div class="progress-bar bg-warning" style="width: 30%"></div>
                                        </div>
                                    </div>
                                    <span class="badge bg-warning">12 ด้าม</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <strong>MT-067</strong>
                                        <br><small class="text-muted">แฟ้มพลาสติก</small>
                                        <div class="progress progress-custom mt-2" style="width: 100px;">
                                            <div class="progress-bar bg-warning" style="width: 25%"></div>
                                        </div>
                                    </div>
                                    <span class="badge bg-warning">8 เล่ม</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-pie me-2"></i>สถิติการใช้งาน
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>เครื่องเขียน</span>
                                    <span>65%</span>
                                </div>
                                <div class="progress progress-custom">
                                    <div class="progress-bar" style="width: 65%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>อุปกรณ์สำนักงาน</span>
                                    <span>45%</span>
                                </div>
                                <div class="progress progress-custom">
                                    <div class="progress-bar" style="width: 45%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>วัสดุคอมพิวเตอร์</span>
                                    <span>30%</span>
                                </div>
                                <div class="progress progress-custom">
                                    <div class="progress-bar" style="width: 30%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>