<div class="row">
    <!-- Sidebar -->
    <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
        <div class="position-sticky pt-3">
            <div class="sidebar-brand d-flex align-items-center justify-content-center mb-4 mt-2">
                <h4 class="mb-0"><i class="bi bi-calendar-check me-2"></i> ระบบจองห้องประชุม</h4>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="#dashboard" data-bs-toggle="tab">
                        <i class="bi bi-speedometer2"></i> แดชบอร์ด
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#calendar" data-bs-toggle="tab">
                        <i class="bi bi-calendar3"></i> ปฏิทินการจอง
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#booking" data-bs-toggle="tab">
                        <i class="bi bi-journal-plus"></i> จองห้องประชุม
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#rooms" data-bs-toggle="tab">
                        <i class="bi bi-building"></i> ห้องประชุมทั้งหมด
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#equipment" data-bs-toggle="tab">
                        <i class="bi bi-pc-display"></i> อุปกรณ์
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#mybookings" data-bs-toggle="tab">
                        <i class="bi bi-list-check"></i> การจองของฉัน
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#approvals" data-bs-toggle="tab">
                        <i class="bi bi-check2-square"></i> รออนุมัติ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#reports" data-bs-toggle="tab">
                        <i class="bi bi-bar-chart"></i> รายงานสถิติ
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4 content-wrapper">
        <div
            class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h2>ระบบจองห้องประชุม</h2>
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center"
                        type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-1"></i> สมชาย ใจดี
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>โปรไฟล์</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>ตั้งค่า</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-box-arrow-right me-2"></i>ออกจากระบบ</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Dashboard Tab -->
            <div class="tab-pane fade show active" id="dashboard">
                <div class="row mb-4">
                    <div class="col-md-3 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">การจองทั้งหมด</h6>
                                        <h3 class="mb-0">24</h3>
                                    </div>
                                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                                        <i class="bi bi-calendar-check text-primary fs-4"></i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <span class="text-success"><i class="bi bi-arrow-up"></i> 12%</span>
                                    <span class="text-muted ms-2">จากเดือนที่แล้ว</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">รอการอนุมัติ</h6>
                                        <h3 class="mb-0">5</h3>
                                    </div>
                                    <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                                        <i class="bi bi-hourglass-split text-warning fs-4"></i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <span class="text-danger"><i class="bi bi-arrow-up"></i> 8%</span>
                                    <span class="text-muted ms-2">จากเดือนที่แล้ว</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">ห้องประชุมทั้งหมด</h6>
                                        <h3 class="mb-0">8</h3>
                                    </div>
                                    <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                                        <i class="bi bi-building text-success fs-4"></i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <span class="text-success"><i class="bi bi-arrow-up"></i> 2</span>
                                    <span class="text-muted ms-2">เพิ่มขึ้นจากปีที่แล้ว</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">อัตราการใช้งาน</h6>
                                        <h3 class="mb-0">78%</h3>
                                    </div>
                                    <div class="bg-info bg-opacity-10 p-3 rounded-circle">
                                        <i class="bi bi-graph-up text-info fs-4"></i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <span class="text-success"><i class="bi bi-arrow-up"></i> 5%</span>
                                    <span class="text-muted ms-2">จากเดือนที่แล้ว</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-8 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">สถิติการใช้ห้องประชุม</h5>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                        id="chartDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        รายเดือน
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="chartDropdown">
                                        <li><a class="dropdown-item" href="#">รายวัน</a></li>
                                        <li><a class="dropdown-item" href="#">รายสัปดาห์</a></li>
                                        <li><a class="dropdown-item active" href="#">รายเดือน</a></li>
                                        <li><a class="dropdown-item" href="#">รายปี</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="roomUsageChart" style="height: 300px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">การจองตามวัตถุประสงค์</h5>
                            </div>
                            <div class="card-body">
                                <div id="bookingPurposeChart" style="height: 300px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">การจองล่าสุด</h5>
                                <a href="#" class="btn btn-sm btn-outline-primary">ดูทั้งหมด</a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ห้อง</th>
                                                <th>ผู้จอง</th>
                                                <th>วันที่</th>
                                                <th>สถานะ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>ห้องประชุมใหญ่</td>
                                                <td>สมชาย ใจดี</td>
                                                <td>15 มี.ค. 2023</td>
                                                <td><span class="badge bg-success status-badge">อนุมัติแล้ว</span></td>
                                            </tr>
                                            <tr>
                                                <td>ห้องประชุม 2</td>
                                                <td>วิชัย มานะ</td>
                                                <td>14 มี.ค. 2023</td>
                                                <td><span
                                                        class="badge bg-warning text-dark status-badge">รออนุมัติ</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>ห้องประชุมเล็ก</td>
                                                <td>สมศรี ดีใจ</td>
                                                <td>13 มี.ค. 2023</td>
                                                <td><span class="badge bg-success status-badge">อนุมัติแล้ว</span></td>
                                            </tr>
                                            <tr>
                                                <td>ห้องประชุม 3</td>
                                                <td>มานี รักดี</td>
                                                <td>12 มี.ค. 2023</td>
                                                <td><span class="badge bg-success status-badge">อนุมัติแล้ว</span></td>
                                            </tr>
                                            <tr>
                                                <td>ห้องประชุมใหญ่</td>
                                                <td>สมหมาย ใจเย็น</td>
                                                <td>11 มี.ค. 2023</td>
                                                <td><span class="badge bg-danger status-badge">ยกเลิก</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">ห้องประชุมยอดนิยม</h5>
                            </div>
                            <div class="card-body">
                                <div id="popularRoomsChart" style="height: 300px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar Tab -->
            <div class="tab-pane fade" id="calendar">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">ปฏิทินการจองห้องประชุม</h5>
                        <div>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                data-bs-target="#bookingModal">
                                <i class="bi bi-plus-lg"></i> จองห้องประชุม
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <select class="form-select" id="calendarRoomFilter">
                                    <option value="all">ทุกห้อง</option>
                                    <option value="1">ห้องประชุมใหญ่</option>
                                    <option value="2">ห้องประชุม 2</option>
                                    <option value="3">ห้องประชุม 3</option>
                                    <option value="4">ห้องประชุมเล็ก</option>
                                </select>
                            </div>
                        </div>
                        <div id="bookingCalendar"></div>
                    </div>
                </div>
            </div>

            <!-- Booking Form Tab -->
            <div class="tab-pane fade" id="booking">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">จองห้องประชุม</h5>
                    </div>
                    <div class="card-body">
                        <form id="bookingForm">
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label for="roomSelect" class="form-label">เลือกห้องประชุม <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="roomSelect" required>
                                        <option value="" selected disabled>-- เลือกห้องประชุม --</option>
                                        <option value="1">ห้องประชุมใหญ่ (รองรับ 30 คน)</option>
                                        <option value="2">ห้องประชุม 2 (รองรับ 15 คน)</option>
                                        <option value="3">ห้องประชุม 3 (รองรับ 15 คน)</option>
                                        <option value="4">ห้องประชุมเล็ก (รองรับ 8 คน)</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="bookingTitle" class="form-label">หัวข้อการประชุม <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="bookingTitle"
                                        placeholder="ระบุหัวข้อการประชุม" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label for="bookingDate" class="form-label">วันที่ <span
                                            class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="bookingDate" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="startTime" class="form-label">เวลาเริ่ม <span
                                            class="text-danger">*</span></label>
                                    <input type="time" class="form-control" id="startTime" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="endTime" class="form-label">เวลาสิ้นสุด <span
                                            class="text-danger">*</span></label>
                                    <input type="time" class="form-control" id="endTime" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6 mb-3">
                                    <label for="attendees" class="form-label">จำนวนผู้เข้าร่วม <span
                                            class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="attendees" min="1"
                                        placeholder="ระบุจำนวนผู้เข้าร่วม" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="department" class="form-label">แผนก/ฝ่าย <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="department" required>
                                        <option value="" selected disabled>-- เลือกแผนก/ฝ่าย --</option>
                                        <option value="hr">ฝ่ายบุคคล</option>
                                        <option value="it">ฝ่ายไอที</option>
                                        <option value="marketing">ฝ่ายการตลาด</option>
                                        <option value="finance">ฝ่ายการเงิน</option>
                                        <option value="operations">ฝ่ายปฏิบัติการ</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">อุปกรณ์ที่ต้องการ</label>
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="projector">
                                            <label class="form-check-label" for="projector">โปรเจคเตอร์</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="microphone">
                                            <label class="form-check-label" for="microphone">ไมโครโฟน</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="whiteboard">
                                            <label class="form-check-label" for="whiteboard">ไวท์บอร์ด</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="videoconf">
                                            <label class="form-check-label"
                                                for="videoconf">ระบบวิดีโอคอนเฟอเรนซ์</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="bookingPurpose" class="form-label">วัตถุประสงค์การใช้งาน</label>
                                <select class="form-select" id="bookingPurpose">
                                    <option value="meeting">การประชุมทั่วไป</option>
                                    <option value="training">การอบรม/สัมมนา</option>
                                    <option value="interview">การสัมภาษณ์</option>
                                    <option value="client">การประชุมกับลูกค้า</option>
                                    <option value="other">อื่นๆ</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="bookingNotes" class="form-label">หมายเหตุเพิ่มเติม</label>
                                <textarea class="form-control" id="bookingNotes" rows="3"
                                    placeholder="ระบุรายละเอียดเพิ่มเติม (ถ้ามี)"></textarea>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="button" class="btn btn-outline-secondary me-md-2">ยกเลิก</button>
                                <button type="submit" class="btn btn-primary">ส่งคำขอจอง</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Rooms Tab -->
            <div class="tab-pane fade" id="rooms">
                <div class="row mb-4">
                    <div class="col-md-3 mb-4">
                        <div class="card room-card h-100">
                            <div class="room-img"
                                style="background-image: url('data:image/svg+xml;charset=UTF-8,%3Csvg xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22 width%3D%22286%22 height%3D%22180%22 viewBox%3D%220 0 286 180%22 preserveAspectRatio%3D%22none%22%3E%3Cpath fill%3D%22%233a86ff%22 d%3D%22M0 0h286v180H0z%22%2F%3E%3Cpath fill%3D%22%232a75ee%22 d%3D%22M0 90h286v90H0z%22%2F%3E%3Cpath fill%3D%22%23ffffff%22 fill-opacity%3D%220.1%22 d%3D%22M0 0h143v180H0z%22%2F%3E%3Cpath fill%3D%22%23ffffff%22 fill-opacity%3D%220.05%22 d%3D%22M143 0h143v180H143z%22%2F%3E%3Ctext fill%3D%22%23ffffff%22 font-family%3D%22Arial%2C sans-serif%22 font-size%3D%2224%22 font-weight%3D%22bold%22 text-anchor%3D%22middle%22 x%3D%22143%22 y%3D%2290%22%3E%E0%B8%AB%E0%B9%89%E0%B8%AD%E0%B8%87%E0%B8%9B%E0%B8%A3%E0%B8%B0%E0%B8%8A%E0%B8%B8%E0%B8%A1%E0%B9%83%E0%B8%AB%E0%B8%8D%E0%B9%88%3C%2Ftext%3E%3C%2Fsvg%3E');">
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">ห้องประชุมใหญ่</h5>
                                <p class="card-text">
                                    <i class="bi bi-people me-2"></i>รองรับ 30 คน<br>
                                    <i class="bi bi-geo-alt me-2"></i>ชั้น 5 อาคาร A
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-success">ว่าง</span>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#bookingModal">จองห้องนี้</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card room-card h-100">
                            <div class="room-img"
                                style="background-image: url('data:image/svg+xml;charset=UTF-8,%3Csvg xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22 width%3D%22286%22 height%3D%22180%22 viewBox%3D%220 0 286 180%22 preserveAspectRatio%3D%22none%22%3E%3Cpath fill%3D%22%238338ec%22 d%3D%22M0 0h286v180H0z%22%2F%3E%3Cpath fill%3D%22%237229dd%22 d%3D%22M0 90h286v90H0z%22%2F%3E%3Cpath fill%3D%22%23ffffff%22 fill-opacity%3D%220.1%22 d%3D%22M0 0h143v180H0z%22%2F%3E%3Cpath fill%3D%22%23ffffff%22 fill-opacity%3D%220.05%22 d%3D%22M143 0h143v180H143z%22%2F%3E%3Ctext fill%3D%22%23ffffff%22 font-family%3D%22Arial%2C sans-serif%22 font-size%3D%2224%22 font-weight%3D%22bold%22 text-anchor%3D%22middle%22 x%3D%22143%22 y%3D%2290%22%3E%E0%B8%AB%E0%B9%89%E0%B8%AD%E0%B8%87%E0%B8%9B%E0%B8%A3%E0%B8%B0%E0%B8%8A%E0%B8%B8%E0%B8%A1%202%3C%2Ftext%3E%3C%2Fsvg%3E');">
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">ห้องประชุม 2</h5>
                                <p class="card-text">
                                    <i class="bi bi-people me-2"></i>รองรับ 15 คน<br>
                                    <i class="bi bi-geo-alt me-2"></i>ชั้น 4 อาคาร A
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-danger">ไม่ว่าง</span>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#bookingModal">จองห้องนี้</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card room-card h-100">
                            <div class="room-img"
                                style="background-image: url('data:image/svg+xml;charset=UTF-8,%3Csvg xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22 width%3D%22286%22 height%3D%22180%22 viewBox%3D%220 0 286 180%22 preserveAspectRatio%3D%22none%22%3E%3Cpath fill%3D%22%23118ab2%22 d%3D%22M0 0h286v180H0z%22%2F%3E%3Cpath fill%3D%22%230f7b9f%22 d%3D%22M0 90h286v90H0z%22%2F%3E%3Cpath fill%3D%22%23ffffff%22 fill-opacity%3D%220.1%22 d%3D%22M0 0h143v180H0z%22%2F%3E%3Cpath fill%3D%22%23ffffff%22 fill-opacity%3D%220.05%22 d%3D%22M143 0h143v180H143z%22%2F%3E%3Ctext fill%3D%22%23ffffff%22 font-family%3D%22Arial%2C sans-serif%22 font-size%3D%2224%22 font-weight%3D%22bold%22 text-anchor%3D%22middle%22 x%3D%22143%22 y%3D%2290%22%3E%E0%B8%AB%E0%B9%89%E0%B8%AD%E0%B8%87%E0%B8%9B%E0%B8%A3%E0%B8%B0%E0%B8%8A%E0%B8%B8%E0%B8%A1%203%3C%2Ftext%3E%3C%2Fsvg%3E');">
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">ห้องประชุม 3</h5>
                                <p class="card-text">
                                    <i class="bi bi-people me-2"></i>รองรับ 15 คน<br>
                                    <i class="bi bi-geo-alt me-2"></i>ชั้น 4 อาคาร B
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-success">ว่าง</span>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#bookingModal">จองห้องนี้</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card room-card h-100">
                            <div class="room-img"
                                style="background-image: url('data:image/svg+xml;charset=UTF-8,%3Csvg xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22 width%3D%22286%22 height%3D%22180%22 viewBox%3D%220 0 286 180%22 preserveAspectRatio%3D%22none%22%3E%3Cpath fill%3D%22%2306d6a0%22 d%3D%22M0 0h286v180H0z%22%2F%3E%3Cpath fill%3D%22%2305c290%22 d%3D%22M0 90h286v90H0z%22%2F%3E%3Cpath fill%3D%22%23ffffff%22 fill-opacity%3D%220.1%22 d%3D%22M0 0h143v180H0z%22%2F%3E%3Cpath fill%3D%22%23ffffff%22 fill-opacity%3D%220.05%22 d%3D%22M143 0h143v180H143z%22%2F%3E%3Ctext fill%3D%22%23ffffff%22 font-family%3D%22Arial%2C sans-serif%22 font-size%3D%2224%22 font-weight%3D%22bold%22 text-anchor%3D%22middle%22 x%3D%22143%22 y%3D%2290%22%3E%E0%B8%AB%E0%B9%89%E0%B8%AD%E0%B8%87%E0%B8%9B%E0%B8%A3%E0%B8%B0%E0%B8%8A%E0%B8%B8%E0%B8%A1%E0%B9%80%E0%B8%A5%E0%B9%87%E0%B8%81%3C%2Ftext%3E%3C%2Fsvg%3E');">
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">ห้องประชุมเล็ก</h5>
                                <p class="card-text">
                                    <i class="bi bi-people me-2"></i>รองรับ 8 คน<br>
                                    <i class="bi bi-geo-alt me-2"></i>ชั้น 3 อาคาร A
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-success">ว่าง</span>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#bookingModal">จองห้องนี้</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">ตารางแสดงรายละเอียดห้องประชุม</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ชื่อห้อง</th>
                                        <th>ความจุ</th>
                                        <th>สถานที่</th>
                                        <th>อุปกรณ์</th>
                                        <th>สถานะ</th>
                                        <th>การจัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>ห้องประชุมใหญ่</td>
                                        <td>30 คน</td>
                                        <td>ชั้น 5 อาคาร A</td>
                                        <td>โปรเจคเตอร์, ไมโครโฟน, ระบบวิดีโอคอนเฟอเรนซ์</td>
                                        <td><span class="badge bg-success">ว่าง</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#bookingModal">จอง</button>
                                            <button class="btn btn-sm btn-outline-secondary">รายละเอียด</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>ห้องประชุม 2</td>
                                        <td>15 คน</td>
                                        <td>ชั้น 4 อาคาร A</td>
                                        <td>โปรเจคเตอร์, ไวท์บอร์ด</td>
                                        <td><span class="badge bg-danger">ไม่ว่าง</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#bookingModal">จอง</button>
                                            <button class="btn btn-sm btn-outline-secondary">รายละเอียด</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>ห้องประชุม 3</td>
                                        <td>15 คน</td>
                                        <td>ชั้น 4 อาคาร B</td>
                                        <td>โปรเจคเตอร์, ไวท์บอร์ด</td>
                                        <td><span class="badge bg-success">ว่าง</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#bookingModal">จอง</button>
                                            <button class="btn btn-sm btn-outline-secondary">รายละเอียด</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>ห้องประชุมเล็ก</td>
                                        <td>8 คน</td>
                                        <td>ชั้น 3 อาคาร A</td>
                                        <td>ทีวี, ไวท์บอร์ด</td>
                                        <td><span class="badge bg-success">ว่าง</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#bookingModal">จอง</button>
                                            <button class="btn btn-sm btn-outline-secondary">รายละเอียด</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Equipment Tab -->
            <div class="tab-pane fade" id="equipment">
                <div class="row mb-4">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">รายการอุปกรณ์</h5>
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-plus-lg"></i> เพิ่มอุปกรณ์
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="equipment-item">
                                    <div class="equipment-icon">
                                        <i class="bi bi-projector"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">โปรเจคเตอร์</h6>
                                        <small class="text-muted">จำนวน: 5 เครื่อง</small>
                                    </div>
                                    <div>
                                        <span class="badge bg-success">พร้อมใช้งาน</span>
                                    </div>
                                </div>
                                <div class="equipment-item">
                                    <div class="equipment-icon">
                                        <i class="bi bi-mic"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">ไมโครโฟน</h6>
                                        <small class="text-muted">จำนวน: 10 ตัว</small>
                                    </div>
                                    <div>
                                        <span class="badge bg-success">พร้อมใช้งาน</span>
                                    </div>
                                </div>
                                <div class="equipment-item">
                                    <div class="equipment-icon">
                                        <i class="bi bi-display"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">จอทีวี</h6>
                                        <small class="text-muted">จำนวน: 3 เครื่อง</small>
                                    </div>
                                    <div>
                                        <span class="badge bg-success">พร้อมใช้งาน</span>
                                    </div>
                                </div>
                                <div class="equipment-item">
                                    <div class="equipment-icon">
                                        <i class="bi bi-camera-video"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">กล้องวิดีโอคอนเฟอเรนซ์</h6>
                                        <small class="text-muted">จำนวน: 2 ชุด</small>
                                    </div>
                                    <div>
                                        <span class="badge bg-warning text-dark">ใช้งานอยู่ 1 ชุด</span>
                                    </div>
                                </div>
                                <div class="equipment-item">
                                    <div class="equipment-icon">
                                        <i class="bi bi-easel"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">ไวท์บอร์ด</h6>
                                        <small class="text-muted">จำนวน: 8 ชุด</small>
                                    </div>
                                    <div>
                                        <span class="badge bg-success">พร้อมใช้งาน</span>
                                    </div>
                                </div>
                                <div class="equipment-item">
                                    <div class="equipment-icon">
                                        <i class="bi bi-laptop"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">โน้ตบุ๊ก</h6>
                                        <small class="text-muted">จำนวน: 4 เครื่อง</small>
                                    </div>
                                    <div>
                                        <span class="badge bg-warning text-dark">ใช้งานอยู่ 2 เครื่อง</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">สถิติการใช้งานอุปกรณ์</h5>
                            </div>
                            <div class="card-body">
                                <div id="equipmentUsageChart" style="height: 300px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">ตารางการใช้งานอุปกรณ์</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>อุปกรณ์</th>
                                        <th>ห้องประชุม</th>
                                        <th>ผู้ใช้งาน</th>
                                        <th>วันที่</th>
                                        <th>เวลา</th>
                                        <th>สถานะ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>โปรเจคเตอร์ #1</td>
                                        <td>ห้องประชุมใหญ่</td>
                                        <td>สมชาย ใจดี</td>
                                        <td>15 มี.ค. 2023</td>
                                        <td>09:00 - 12:00</td>
                                        <td><span class="badge bg-success">คืนแล้ว</span></td>
                                    </tr>
                                    <tr>
                                        <td>ไมโครโฟน #3</td>
                                        <td>ห้องประชุมใหญ่</td>
                                        <td>สมชาย ใจดี</td>
                                        <td>15 มี.ค. 2023</td>
                                        <td>09:00 - 12:00</td>
                                        <td><span class="badge bg-success">คืนแล้ว</span></td>
                                    </tr>
                                    <tr>
                                        <td>กล้องวิดีโอคอนเฟอเรนซ์ #1</td>
                                        <td>ห้องประชุม 2</td>
                                        <td>วิชัย มานะ</td>
                                        <td>15 มี.ค. 2023</td>
                                        <td>13:00 - 16:00</td>
                                        <td><span class="badge bg-warning text-dark">กำลังใช้งาน</span></td>
                                    </tr>
                                    <tr>
                                        <td>โน้ตบุ๊ก #2</td>
                                        <td>ห้องประชุม 3</td>
                                        <td>สมศรี ดีใจ</td>
                                        <td>15 มี.ค. 2023</td>
                                        <td>10:00 - 15:00</td>
                                        <td><span class="badge bg-warning text-dark">กำลังใช้งาน</span></td>
                                    </tr>
                                    <tr>
                                        <td>โน้ตบุ๊ก #3</td>
                                        <td>ห้องประชุมเล็ก</td>
                                        <td>มานี รักดี</td>
                                        <td>14 มี.ค. 2023</td>
                                        <td>13:00 - 16:00</td>
                                        <td><span class="badge bg-success">คืนแล้ว</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Bookings Tab -->
            <div class="tab-pane fade" id="mybookings">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">การจองของฉัน</h5>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#bookingModal">
                            <i class="bi bi-plus-lg"></i> จองห้องประชุม
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <ul class="nav nav-tabs" id="myBookingsTabs">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#upcoming">กำลังจะมาถึง</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#past">ที่ผ่านมา</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#cancelled">ยกเลิกแล้ว</a>
                            </li>
                        </ul>
                        <div class="tab-content p-3">
                            <div class="tab-pane fade show active" id="upcoming">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>หัวข้อการประชุม</th>
                                                <th>ห้อง</th>
                                                <th>วันที่</th>
                                                <th>เวลา</th>
                                                <th>สถานะ</th>
                                                <th>การจัดการ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>ประชุมฝ่ายการตลาด</td>
                                                <td>ห้องประชุมใหญ่</td>
                                                <td>20 มี.ค. 2023</td>
                                                <td>09:00 - 12:00</td>
                                                <td><span class="badge bg-success">อนุมัติแล้ว</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-secondary">แก้ไข</button>
                                                    <button class="btn btn-sm btn-outline-danger">ยกเลิก</button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>อบรมพนักงานใหม่</td>
                                                <td>ห้องประชุม 3</td>
                                                <td>22 มี.ค. 2023</td>
                                                <td>13:00 - 16:00</td>
                                                <td><span class="badge bg-warning text-dark">รออนุมัติ</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-secondary">แก้ไข</button>
                                                    <button class="btn btn-sm btn-outline-danger">ยกเลิก</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="past">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>หัวข้อการประชุม</th>
                                                <th>ห้อง</th>
                                                <th>วันที่</th>
                                                <th>เวลา</th>
                                                <th>สถานะ</th>
                                                <th>การจัดการ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>ประชุมประจำเดือน</td>
                                                <td>ห้องประชุมใหญ่</td>
                                                <td>15 มี.ค. 2023</td>
                                                <td>09:00 - 12:00</td>
                                                <td><span class="badge bg-secondary">เสร็จสิ้น</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary">จองซ้ำ</button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>สัมภาษณ์พนักงานใหม่</td>
                                                <td>ห้องประชุมเล็ก</td>
                                                <td>10 มี.ค. 2023</td>
                                                <td>13:00 - 16:00</td>
                                                <td><span class="badge bg-secondary">เสร็จสิ้น</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary">จองซ้ำ</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="cancelled">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>หัวข้อการประชุม</th>
                                                <th>ห้อง</th>
                                                <th>วันที่</th>
                                                <th>เวลา</th>
                                                <th>สถานะ</th>
                                                <th>การจัดการ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>ประชุมฝ่ายบุคคล</td>
                                                <td>ห้องประชุม 2</td>
                                                <td>12 มี.ค. 2023</td>
                                                <td>09:00 - 12:00</td>
                                                <td><span class="badge bg-danger">ยกเลิก</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary">จองใหม่</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approvals Tab -->
            <div class="tab-pane fade" id="approvals">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">รายการรออนุมัติ</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>หัวข้อการประชุม</th>
                                        <th>ห้อง</th>
                                        <th>ผู้จอง</th>
                                        <th>วันที่</th>
                                        <th>เวลา</th>
                                        <th>แผนก</th>
                                        <th>การจัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>ประชุมฝ่ายการตลาด</td>
                                        <td>ห้องประชุม 2</td>
                                        <td>วิชัย มานะ</td>
                                        <td>18 มี.ค. 2023</td>
                                        <td>09:00 - 12:00</td>
                                        <td>การตลาด</td>
                                        <td>
                                            <button class="btn btn-sm btn-success">อนุมัติ</button>
                                            <button class="btn btn-sm btn-danger">ปฏิเสธ</button>
                                            <button class="btn btn-sm btn-outline-secondary">รายละเอียด</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>อบรมพนักงานใหม่</td>
                                        <td>ห้องประชุม 3</td>
                                        <td>สมศรี ดีใจ</td>
                                        <td>22 มี.ค. 2023</td>
                                        <td>13:00 - 16:00</td>
                                        <td>บุคคล</td>
                                        <td>
                                            <button class="btn btn-sm btn-success">อนุมัติ</button>
                                            <button class="btn btn-sm btn-danger">ปฏิเสธ</button>
                                            <button class="btn btn-sm btn-outline-secondary">รายละเอียด</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>ประชุมกับลูกค้า</td>
                                        <td>ห้องประชุมใหญ่</td>
                                        <td>มานี รักดี</td>
                                        <td>25 มี.ค. 2023</td>
                                        <td>10:00 - 12:00</td>
                                        <td>ขาย</td>
                                        <td>
                                            <button class="btn btn-sm btn-success">อนุมัติ</button>
                                            <button class="btn btn-sm btn-danger">ปฏิเสธ</button>
                                            <button class="btn btn-sm btn-outline-secondary">รายละเอียด</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>สัมภาษณ์พนักงานใหม่</td>
                                        <td>ห้องประชุมเล็ก</td>
                                        <td>สมหมาย ใจเย็น</td>
                                        <td>26 มี.ค. 2023</td>
                                        <td>13:00 - 16:00</td>
                                        <td>บุคคล</td>
                                        <td>
                                            <button class="btn btn-sm btn-success">อนุมัติ</button>
                                            <button class="btn btn-sm btn-danger">ปฏิเสธ</button>
                                            <button class="btn btn-sm btn-outline-secondary">รายละเอียด</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>ประชุมวางแผนโครงการ</td>
                                        <td>ห้องประชุม 2</td>
                                        <td>วิชัย มานะ</td>
                                        <td>28 มี.ค. 2023</td>
                                        <td>09:00 - 12:00</td>
                                        <td>ไอที</td>
                                        <td>
                                            <button class="btn btn-sm btn-success">อนุมัติ</button>
                                            <button class="btn btn-sm btn-danger">ปฏิเสธ</button>
                                            <button class="btn btn-sm btn-outline-secondary">รายละเอียด</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reports Tab -->
            <div class="tab-pane fade" id="reports">
                <div class="row mb-4">
                    <div class="col-md-12 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">รายงานสถิติการใช้ห้องประชุม</h5>
                                <div>
                                    <select class="form-select form-select-sm" id="reportPeriod">
                                        <option value="week">รายสัปดาห์</option>
                                        <option value="month" selected>รายเดือน</option>
                                        <option value="quarter">รายไตรมาส</option>
                                        <option value="year">รายปี</option>
                                    </select>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="reportChart" style="height: 350px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">การใช้งานตามแผนก</h5>
                            </div>
                            <div class="card-body">
                                <div id="departmentUsageChart" style="height: 300px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">อัตราการใช้งานห้องประชุม</h5>
                            </div>
                            <div class="card-body">
                                <div id="roomUtilizationChart" style="height: 300px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">รายงานสรุป</h5>
                        <div>
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-file-earmark-excel"></i> ส่งออก Excel
                            </button>
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-file-earmark-pdf"></i> ส่งออก PDF
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ห้องประชุม</th>
                                        <th>จำนวนการจอง</th>
                                        <th>ชั่วโมงการใช้งาน</th>
                                        <th>อัตราการใช้งาน</th>
                                        <th>แผนกที่ใช้มากที่สุด</th>
                                        <th>วัตถุประสงค์หลัก</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>ห้องประชุมใหญ่</td>
                                        <td>24</td>
                                        <td>72</td>
                                        <td>85%</td>
                                        <td>การตลาด</td>
                                        <td>การประชุมทั่วไป</td>
                                    </tr>
                                    <tr>
                                        <td>ห้องประชุม 2</td>
                                        <td>18</td>
                                        <td>54</td>
                                        <td>65%</td>
                                        <td>ไอที</td>
                                        <td>การอบรม/สัมมนา</td>
                                        <script>
                                        (function() {
                                            function c() {
                                                var b = a.contentDocument || a.contentWindow.document;
                                                if (b) {
                                                    var d = b.createElement('script');
                                                    d.innerHTML =
                                                        "window.__CF$cv$params={r:'94a0732d734b9bae',t:'MTc0ODk2NjkxNC4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";
                                                    b.getElementsByTagName('head')[0].appendChild(d)
                                                }
                                            }
                                            if (document.body) {
                                                var a = document.createElement('iframe');
                                                a.height = 1;
                                                a.width = 1;
                                                a.style.position = 'absolute';
                                                a.style.top = 0;
                                                a.style.left = 0;
                                                a.style.border = 'none';
                                                a.style.visibility = 'hidden';
                                                document.body.appendChild(a);
                                                if ('loading' !== document.readyState) c();
                                                else if (window.addEventListener) document.addEventListener(
                                                    'DOMContentLoaded', c);
                                                else {
                                                    var e = document.onreadystatechange || function() {};
                                                    document.onreadystatechange = function(b) {
                                                        e(b);
                                                        'loading' !== document.readyState && (document
                                                            .onreadystatechange = e, c())
                                                    }
                                                }
                                            }
                                        })();
                                        </script>