<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
            <i class="fas fa-ambulance me-2"></i>
            ระบบจองรถโรงพยาบาล
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="#" id="dashboard-link">
                        <i class="fas fa-tachometer-alt me-1"></i> หน้าหลัก
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" id="calendar-link">
                        <i class="fas fa-calendar-alt me-1"></i> ปฏิทินการจอง
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" id="reservation-link">
                        <i class="fas fa-clipboard-list me-1"></i> รายการจอง
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" id="vehicles-link">
                        <i class="fas fa-car me-1"></i> ข้อมูลรถ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" id="drivers-link">
                        <i class="fas fa-id-card me-1"></i> ข้อมูลพนักงานขับรถ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" id="reports-link">
                        <i class="fas fa-chart-bar me-1"></i> รายงาน
                    </a>
                </li>
            </ul>
            <div class="d-flex align-items-center">
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button"
                        data-bs-toggle="dropdown">
                        <div class="user-avatar">
                            <span id="user-initial">A</span>
                        </div>
                        <span id="username">ผู้ใช้งาน</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" id="profile-link"><i
                                    class="fas fa-user me-2"></i>โปรไฟล์</a></li>
                        <li><a class="dropdown-item" href="#" id="settings-link"><i
                                    class="fas fa-cog me-2"></i>ตั้งค่า</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="#" id="switch-role-link"><i
                                    class="fas fa-exchange-alt me-2"></i>สลับบทบาท</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="#" id="logout-link"><i
                                    class="fas fa-sign-out-alt me-2"></i>ออกจากระบบ</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="container-fluid content-wrapper">
    <!-- Dashboard Section -->
    <div id="dashboard-section">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-4">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    <span id="dashboard-title">หน้าหลัก</span>
                    <span class="badge bg-primary ms-2" id="role-badge">ผู้ใช้งาน</span>
                </h2>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card dashboard-stats bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">การจองทั้งหมด</h6>
                                <h2 class="mb-0" id="total-reservations">24</h2>
                            </div>
                            <i class="fas fa-calendar fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-stats bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">การจองที่อนุมัติแล้ว</h6>
                                <h2 class="mb-0" id="approved-reservations">18</h2>
                            </div>
                            <i class="fas fa-check-circle fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-stats bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">การจองที่รอดำเนินการ</h6>
                                <h2 class="mb-0" id="pending-reservations">5</h2>
                            </div>
                            <i class="fas fa-clock fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-stats bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">รถที่พร้อมใช้งาน</h6>
                                <h2 class="mb-0" id="available-vehicles">8</h2>
                            </div>
                            <i class="fas fa-car fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Reservations and Calendar Preview -->
        <div class="row">
            <div class="col-lg-7 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">การจองล่าสุด</h5>
                        <button class="btn btn-sm btn-primary" id="new-reservation-btn">
                            <i class="fas fa-plus me-1"></i> จองรถ
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>รหัส</th>
                                        <th>วันที่</th>
                                        <th>จุดหมาย</th>
                                        <th>รถ</th>
                                        <th>สถานะ</th>
                                        <th>จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody id="recent-reservations">
                                    <tr>
                                        <td>R001</td>
                                        <td>15 ก.ค. 2568</td>
                                        <td>โรงพยาบาลศิริราช</td>
                                        <td>รถตู้ Toyota</td>
                                        <td><span class="badge bg-success">อนุมัติแล้ว</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-info view-btn" data-id="R001">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>R002</td>
                                        <td>16 ก.ค. 2568</td>
                                        <td>โรงพยาบาลรามาธิบดี</td>
                                        <td>รถเก๋ง Honda</td>
                                        <td><span class="badge bg-warning text-dark">รออนุมัติ</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-info view-btn" data-id="R002">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>R003</td>
                                        <td>18 ก.ค. 2568</td>
                                        <td>สำนักงานสาธารณสุขจังหวัด</td>
                                        <td>รถตู้ Toyota</td>
                                        <td><span class="badge bg-warning text-dark">รออนุมัติ</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-info view-btn" data-id="R003">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>R004</td>
                                        <td>20 ก.ค. 2568</td>
                                        <td>โรงพยาบาลจุฬาลงกรณ์</td>
                                        <td>รถตู้ Toyota</td>
                                        <td><span class="badge bg-success">อนุมัติแล้ว</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-info view-btn" data-id="R004">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>R005</td>
                                        <td>22 ก.ค. 2568</td>
                                        <td>กระทรวงสาธารณสุข</td>
                                        <td>รถเก๋ง Honda</td>
                                        <td><span class="badge bg-danger">ปฏิเสธ</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-info view-btn" data-id="R005">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">ปฏิทินการจองรถ</h5>
                    </div>
                    <div class="card-body">
                        <div id="dashboard-calendar"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Vehicles -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">รถที่พร้อมใช้งาน</h5>
                        <a href="#" class="btn btn-sm btn-primary" id="view-all-vehicles">ดูทั้งหมด</a>
                    </div>
                    <div class="card-body">
                        <div class="row" id="available-vehicles-list">
                            <div class="col-md-3 mb-3">
                                <div class="card vehicle-card h-100">
                                    <div class="bg-light text-center p-3">
                                        <i class="fas fa-shuttle-van fa-5x text-primary"></i>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title">รถตู้ Toyota</h5>
                                        <p class="card-text mb-1"><i class="fas fa-id-card me-2"></i>ทะเบียน:
                                            ฮท-1234</p>
                                        <p class="card-text mb-1"><i class="fas fa-users me-2"></i>ที่นั่ง: 12
                                            ที่นั่ง</p>
                                        <p class="card-text"><span class="badge bg-success">พร้อมใช้งาน</span></p>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <button class="btn btn-primary btn-sm w-100 reserve-vehicle-btn"
                                            data-id="V001">
                                            <i class="fas fa-calendar-plus me-1"></i> จองรถคันนี้
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card vehicle-card h-100">
                                    <div class="bg-light text-center p-3">
                                        <i class="fas fa-car fa-5x text-primary"></i>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title">รถเก๋ง Honda</h5>
                                        <p class="card-text mb-1"><i class="fas fa-id-card me-2"></i>ทะเบียน:
                                            กข-5678</p>
                                        <p class="card-text mb-1"><i class="fas fa-users me-2"></i>ที่นั่ง: 5
                                            ที่นั่ง</p>
                                        <p class="card-text"><span class="badge bg-success">พร้อมใช้งาน</span></p>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <button class="btn btn-primary btn-sm w-100 reserve-vehicle-btn"
                                            data-id="V002">
                                            <i class="fas fa-calendar-plus me-1"></i> จองรถคันนี้
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card vehicle-card h-100">
                                    <div class="bg-light text-center p-3">
                                        <i class="fas fa-ambulance fa-5x text-primary"></i>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title">รถพยาบาล</h5>
                                        <p class="card-text mb-1"><i class="fas fa-id-card me-2"></i>ทะเบียน:
                                            พบ-9012</p>
                                        <p class="card-text mb-1"><i class="fas fa-users me-2"></i>ที่นั่ง: 3
                                            ที่นั่ง</p>
                                        <p class="card-text"><span class="badge bg-success">พร้อมใช้งาน</span></p>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <button class="btn btn-primary btn-sm w-100 reserve-vehicle-btn"
                                            data-id="V003">
                                            <i class="fas fa-calendar-plus me-1"></i> จองรถคันนี้
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card vehicle-card h-100">
                                    <div class="bg-light text-center p-3">
                                        <i class="fas fa-truck fa-5x text-primary"></i>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title">รถกระบะ Toyota</h5>
                                        <p class="card-text mb-1"><i class="fas fa-id-card me-2"></i>ทะเบียน:
                                            บจ-3456</p>
                                        <p class="card-text mb-1"><i class="fas fa-users me-2"></i>ที่นั่ง: 4
                                            ที่นั่ง</p>
                                        <p class="card-text"><span class="badge bg-success">พร้อมใช้งาน</span></p>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <button class="btn btn-primary btn-sm w-100 reserve-vehicle-btn"
                                            data-id="V004">
                                            <i class="fas fa-calendar-plus me-1"></i> จองรถคันนี้
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Section -->
    <div id="calendar-section" style="display: none;">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-4"><i class="fas fa-calendar-alt me-2"></i>ปฏิทินการจองรถ</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-md-9 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">ปฏิทินการจอง</h5>
                    </div>
                    <div class="card-body">
                        <div id="main-calendar"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">ตัวกรอง</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">ประเภทรถ</label>
                            <div class="form-check">
                                <input class="form-check-input vehicle-filter" type="checkbox" value="van"
                                    id="van-filter" checked="">
                                <label class="form-check-label" for="van-filter">
                                    รถตู้
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input vehicle-filter" type="checkbox" value="car"
                                    id="car-filter" checked="">
                                <label class="form-check-label" for="car-filter">
                                    รถเก๋ง
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input vehicle-filter" type="checkbox" value="ambulance"
                                    id="ambulance-filter" checked="">
                                <label class="form-check-label" for="ambulance-filter">
                                    รถพยาบาล
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input vehicle-filter" type="checkbox" value="pickup"
                                    id="pickup-filter" checked="">
                                <label class="form-check-label" for="pickup-filter">
                                    รถกระบะ
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">สถานะ</label>
                            <div class="form-check">
                                <input class="form-check-input status-filter" type="checkbox" value="approved"
                                    id="approved-filter" checked="">
                                <label class="form-check-label" for="approved-filter">
                                    อนุมัติแล้ว
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input status-filter" type="checkbox" value="pending"
                                    id="pending-filter" checked="">
                                <label class="form-check-label" for="pending-filter">
                                    รออนุมัติ
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input status-filter" type="checkbox" value="rejected"
                                    id="rejected-filter">
                                <label class="form-check-label" for="rejected-filter">
                                    ปฏิเสธ
                                </label>
                            </div>
                        </div>
                        <button class="btn btn-primary w-100" id="apply-filters">
                            <i class="fas fa-filter me-1"></i> กรอง
                        </button>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">จองรถ</h5>
                    </div>
                    <div class="card-body">
                        <p>คลิกที่ปฏิทินเพื่อสร้างการจองใหม่ หรือคลิกที่ปุ่มด้านล่าง</p>
                        <button class="btn btn-primary w-100" id="calendar-new-reservation-btn">
                            <i class="fas fa-plus me-1"></i> จองรถใหม่
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reservation List Section -->
    <div id="reservation-section" style="display: none;">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-4"><i class="fas fa-clipboard-list me-2"></i>รายการจอง</h2>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">รายการจองทั้งหมด</h5>
                        <button class="btn btn-primary" id="list-new-reservation-btn">
                            <i class="fas fa-plus me-1"></i> จองรถใหม่
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3 mb-2">
                                <input type="text" class="form-control" placeholder="ค้นหา..."
                                    id="reservation-search">
                            </div>
                            <div class="col-md-3 mb-2">
                                <select class="form-select" id="reservation-status-filter">
                                    <option value="all">สถานะทั้งหมด</option>
                                    <option value="approved">อนุมัติแล้ว</option>
                                    <option value="pending">รออนุมัติ</option>
                                    <option value="rejected">ปฏิเสธ</option>
                                    <option value="completed">เสร็จสิ้น</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <select class="form-select" id="reservation-vehicle-filter">
                                    <option value="all">รถทั้งหมด</option>
                                    <option value="van">รถตู้</option>
                                    <option value="car">รถเก๋ง</option>
                                    <option value="ambulance">รถพยาบาล</option>
                                    <option value="pickup">รถกระบะ</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <button class="btn btn-primary w-100" id="reservation-filter-btn">
                                    <i class="fas fa-filter me-1"></i> กรอง
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>รหัส</th>
                                        <th>ผู้จอง</th>
                                        <th>วันที่จอง</th>
                                        <th>วันที่ใช้งาน</th>
                                        <th>จุดหมาย</th>
                                        <th>รถ</th>
                                        <th>พนักงานขับรถ</th>
                                        <th>สถานะ</th>
                                        <th>จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody id="reservation-list">
                                    <tr>
                                        <td>R001</td>
                                        <td>นายแพทย์สมชาย ใจดี</td>
                                        <td>10 ก.ค. 2568</td>
                                        <td>15 ก.ค. 2568</td>
                                        <td>โรงพยาบาลศิริราช</td>
                                        <td>รถตู้ Toyota</td>
                                        <td>นายมานะ รักงาน</td>
                                        <td><span class="badge bg-success">อนุมัติแล้ว</span></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-info view-reservation-btn" data-id="R001">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-warning edit-reservation-btn" data-id="R001">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger cancel-reservation-btn"
                                                    data-id="R001">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>R002</td>
                                        <td>นางสาวสมศรี มีสุข</td>
                                        <td>12 ก.ค. 2568</td>
                                        <td>16 ก.ค. 2568</td>
                                        <td>โรงพยาบาลรามาธิบดี</td>
                                        <td>รถเก๋ง Honda</td>
                                        <td>-</td>
                                        <td><span class="badge bg-warning text-dark">รออนุมัติ</span></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-info view-reservation-btn" data-id="R002">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-warning edit-reservation-btn" data-id="R002">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger cancel-reservation-btn"
                                                    data-id="R002">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>R003</td>
                                        <td>นายวิชัย สุขสันต์</td>
                                        <td>13 ก.ค. 2568</td>
                                        <td>18 ก.ค. 2568</td>
                                        <td>สำนักงานสาธารณสุขจังหวัด</td>
                                        <td>รถตู้ Toyota</td>
                                        <td>-</td>
                                        <td><span class="badge bg-warning text-dark">รออนุมัติ</span></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-info view-reservation-btn" data-id="R003">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-warning edit-reservation-btn" data-id="R003">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger cancel-reservation-btn"
                                                    data-id="R003">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>R004</td>
                                        <td>นางนิภา ดีงาม</td>
                                        <td>14 ก.ค. 2568</td>
                                        <td>20 ก.ค. 2568</td>
                                        <td>โรงพยาบาลจุฬาลงกรณ์</td>
                                        <td>รถตู้ Toyota</td>
                                        <td>นายสมศักดิ์ ขับดี</td>
                                        <td><span class="badge bg-success">อนุมัติแล้ว</span></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-info view-reservation-btn" data-id="R004">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-warning edit-reservation-btn" data-id="R004">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger cancel-reservation-btn"
                                                    data-id="R004">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>R005</td>
                                        <td>นายสมบัติ มั่งมี</td>
                                        <td>15 ก.ค. 2568</td>
                                        <td>22 ก.ค. 2568</td>
                                        <td>กระทรวงสาธารณสุข</td>
                                        <td>รถเก๋ง Honda</td>
                                        <td>-</td>
                                        <td><span class="badge bg-danger">ปฏิเสธ</span></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-info view-reservation-btn" data-id="R005">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-warning edit-reservation-btn" data-id="R005">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger cancel-reservation-btn"
                                                    data-id="R005">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>R006</td>
                                        <td>นางสาวรัตนา สวยงาม</td>
                                        <td>16 ก.ค. 2568</td>
                                        <td>25 ก.ค. 2568</td>
                                        <td>โรงพยาบาลศรีนครินทร์</td>
                                        <td>รถพยาบาล</td>
                                        <td>นายมานะ รักงาน</td>
                                        <td><span class="badge bg-success">อนุมัติแล้ว</span></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-info view-reservation-btn" data-id="R006">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-warning edit-reservation-btn" data-id="R006">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger cancel-reservation-btn"
                                                    data-id="R006">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>R007</td>
                                        <td>นายประเสริฐ ยิ้มแย้ม</td>
                                        <td>17 ก.ค. 2568</td>
                                        <td>28 ก.ค. 2568</td>
                                        <td>โรงพยาบาลมหาราช</td>
                                        <td>รถกระบะ Toyota</td>
                                        <td>นายสมศักดิ์ ขับดี</td>
                                        <td><span class="badge bg-success">อนุมัติแล้ว</span></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-info view-reservation-btn" data-id="R007">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-warning edit-reservation-btn" data-id="R007">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger cancel-reservation-btn"
                                                    data-id="R007">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <nav>
                            <ul class="pagination justify-content-center">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1">ก่อนหน้า</a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item">
                                    <a class="page-link" href="#">ถัดไป</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vehicles Section -->
    <div id="vehicles-section" style="display: none;">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-4"><i class="fas fa-car me-2"></i>ข้อมูลรถ</h2>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">รายการรถทั้งหมด</h5>
                        <button class="btn btn-primary" id="add-vehicle-btn" data-bs-toggle="modal"
                            data-bs-target="#vehicleModal">
                            <i class="fas fa-plus me-1"></i> เพิ่มรถ
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4 mb-2">
                                <input type="text" class="form-control" placeholder="ค้นหา..." id="vehicle-search">
                            </div>
                            <div class="col-md-4 mb-2">
                                <select class="form-select" id="vehicle-type-filter">
                                    <option value="all">ประเภทรถทั้งหมด</option>
                                    <option value="van">รถตู้</option>
                                    <option value="car">รถเก๋ง</option>
                                    <option value="ambulance">รถพยาบาล</option>
                                    <option value="pickup">รถกระบะ</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <select class="form-select" id="vehicle-status-filter">
                                    <option value="all">สถานะทั้งหมด</option>
                                    <option value="available">พร้อมใช้งาน</option>
                                    <option value="in-use">กำลังใช้งาน</option>
                                    <option value="maintenance">ซ่อมบำรุง</option>
                                </select>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>รหัส</th>
                                        <th>ประเภท</th>
                                        <th>ยี่ห้อ/รุ่น</th>
                                        <th>ทะเบียน</th>
                                        <th>จำนวนที่นั่ง</th>
                                        <th>ปีที่ผลิต</th>
                                        <th>สถานะ</th>
                                        <th>จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody id="vehicle-list">
                                    <tr>
                                        <td>V001</td>
                                        <td>รถตู้</td>
                                        <td>Toyota Commuter</td>
                                        <td>ฮท-1234</td>
                                        <td>12</td>
                                        <td>2565</td>
                                        <td><span class="badge bg-success">พร้อมใช้งาน</span></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-info view-vehicle-btn" data-id="V001">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-warning edit-vehicle-btn" data-id="V001">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger delete-vehicle-btn" data-id="V001">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>V002</td>
                                        <td>รถเก๋ง</td>
                                        <td>Honda Civic</td>
                                        <td>กข-5678</td>
                                        <td>5</td>
                                        <td>2566</td>
                                        <td><span class="badge bg-success">พร้อมใช้งาน</span></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-info view-vehicle-btn" data-id="V002">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-warning edit-vehicle-btn" data-id="V002">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger delete-vehicle-btn" data-id="V002">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>V003</td>
                                        <td>รถพยาบาล</td>
                                        <td>Toyota Hiace</td>
                                        <td>พบ-9012</td>
                                        <td>3</td>
                                        <td>2564</td>
                                        <td><span class="badge bg-success">พร้อมใช้งาน</span></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-info view-vehicle-btn" data-id="V003">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-warning edit-vehicle-btn" data-id="V003">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger delete-vehicle-btn" data-id="V003">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>V004</td>
                                        <td>รถกระบะ</td>
                                        <td>Toyota Hilux Revo</td>
                                        <td>บจ-3456</td>
                                        <td>4</td>
                                        <td>2565</td>
                                        <td><span class="badge bg-success">พร้อมใช้งาน</span></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-info view-vehicle-btn" data-id="V004">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-warning edit-vehicle-btn" data-id="V004">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger delete-vehicle-btn" data-id="V004">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>V005</td>
                                        <td>รถตู้</td>
                                        <td>Hyundai H1</td>
                                        <td>ลพ-7890</td>
                                        <td>11</td>
                                        <td>2563</td>
                                        <td><span class="badge bg-warning text-dark">กำลังใช้งาน</span></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-info view-vehicle-btn" data-id="V005">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-warning edit-vehicle-btn" data-id="V005">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger delete-vehicle-btn" data-id="V005">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>V006</td>
                                        <td>รถเก๋ง</td>
                                        <td>Toyota Camry</td>
                                        <td>กท-1122</td>
                                        <td>5</td>
                                        <td>2564</td>
                                        <td><span class="badge bg-danger">ซ่อมบำรุง</span></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-info view-vehicle-btn" data-id="V006">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-warning edit-vehicle-btn" data-id="V006">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger delete-vehicle-btn" data-id="V006">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <nav>
                            <ul class="pagination justify-content-center">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1">ก่อนหน้า</a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item">
                                    <a class="page-link" href="#">ถัดไป</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vehicle Maintenance -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">ประวัติการซ่อมบำรุง</h5>
                        <button class="btn btn-primary" id="add-maintenance-btn">
                            <i class="fas fa-plus me-1"></i> เพิ่มประวัติซ่อมบำรุง
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>รหัส</th>
                                        <th>รถ</th>
                                        <th>ประเภทการซ่อม</th>
                                        <th>วันที่เริ่ม</th>
                                        <th>วันที่เสร็จ</th>
                                        <th>ค่าใช้จ่าย</th>
                                        <th>สถานะ</th>
                                        <th>จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody id="maintenance-list">
                                    <tr>
                                        <td>M001</td>
                                        <td>Toyota Camry (กท-1122)</td>
                                        <td>ซ่อมเครื่องยนต์</td>
                                        <td>10 ก.ค. 2568</td>
                                        <td>20 ก.ค. 2568</td>
                                        <td>15,000 บาท</td>
                                        <td><span class="badge bg-warning text-dark">กำลังดำเนินการ</span></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-info view-maintenance-btn" data-id="M001">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-warning edit-maintenance-btn" data-id="M001">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>M002</td>
                                        <td>Toyota Hiace (พบ-9012)</td>
                                        <td>เปลี่ยนยาง</td>
                                        <td>5 มิ.ย. 2568</td>
                                        <td>5 มิ.ย. 2568</td>
                                        <td>8,000 บาท</td>
                                        <td><span class="badge bg-success">เสร็จสิ้น</span></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-info view-maintenance-btn" data-id="M002">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-warning edit-maintenance-btn" data-id="M002">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>M003</td>
                                        <td>Honda Civic (กข-5678)</td>
                                        <td>ตรวจเช็คระบบไฟฟ้า</td>
                                        <td>15 พ.ค. 2568</td>
                                        <td>16 พ.ค. 2568</td>
                                        <td>3,500 บาท</td>
                                        <td><span class="badge bg-success">เสร็จสิ้น</span></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-info view-maintenance-btn" data-id="M003">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-warning edit-maintenance-btn" data-id="M003">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
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

    <!-- Drivers Section -->
    <div id="drivers-section" style="display: none;">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-4"><i class="fas fa-id-card me-2"></i>ข้อมูลพนักงานขับรถ</h2>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">รายชื่อพนักงานขับรถ</h5>
                        <button class="btn btn-primary" id="add-driver-btn" data-bs-toggle="modal"
                            data-bs-target="#driverModal">
                            <i class="fas fa-plus me-1"></i> เพิ่มพนักงานขับรถ
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6 mb-2">
                                <input type="text" class="form-control" placeholder="ค้นหา..." id="driver-search">
                            </div>
                            <div class="col-md-6 mb-2">
                                <select class="form-select" id="driver-status-filter">
                                    <option value="all">สถานะทั้งหมด</option>
                                    <option value="available">พร้อมปฏิบัติงาน</option>
                                    <option value="busy">กำลังปฏิบัติงาน</option>
                                    <option value="leave">ลา</option>
                                </select>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>รหัส</th>
                                        <th>ชื่อ-นามสกุล</th>
                                        <th>เบอร์โทรศัพท์</th>
                                        <th>ใบขับขี่</th>
                                        <th>ประเภทรถที่ขับได้</th>
                                        <th>สถานะ</th>
                                        <th>จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody id="driver-list">
                                    <tr>
                                        <td>D001</td>
                                        <td>นายมานะ รักงาน</td>
                                        <td>081-234-5678</td>
                                        <td>1234567890</td>
                                        <td>รถตู้, รถพยาบาล</td>
                                        <td><span class="badge bg-success">พร้อมปฏิบัติงาน</span></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-info view-driver-btn" data-id="D001">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-warning edit-driver-btn" data-id="D001">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger delete-driver-btn" data-id="D001">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>D002</td>
                                        <td>นายสมศักดิ์ ขับดี</td>
                                        <td>089-876-5432</td>
                                        <td>0987654321</td>
                                        <td>รถตู้, รถเก๋ง, รถกระบะ</td>
                                        <td><span class="badge bg-warning text-dark">กำลังปฏิบัติงาน</span></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-info view-driver-btn" data-id="D002">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-warning edit-driver-btn" data-id="D002">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger delete-driver-btn" data-id="D002">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>D003</td>
                                        <td>นายสมชาย ใจเย็น</td>
                                        <td>062-345-6789</td>
                                        <td>2345678901</td>
                                        <td>รถเก๋ง, รถกระบะ</td>
                                        <td><span class="badge bg-danger">ลา</span></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-info view-driver-btn" data-id="D003">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-warning edit-driver-btn" data-id="D003">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger delete-driver-btn" data-id="D003">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>D004</td>
                                        <td>นายวิชัย รอบคอบ</td>
                                        <td>095-678-9012</td>
                                        <td>3456789012</td>
                                        <td>รถตู้, รถพยาบาล</td>
                                        <td><span class="badge bg-success">พร้อมปฏิบัติงาน</span></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-info view-driver-btn" data-id="D004">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-warning edit-driver-btn" data-id="D004">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger delete-driver-btn" data-id="D004">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <nav>
                            <ul class="pagination justify-content-center">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1">ก่อนหน้า</a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item">
                                    <a class="page-link" href="#">ถัดไป</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Driver Schedule -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">ตารางงานพนักงานขับรถ</h5>
                    </div>
                    <div class="card-body">
                        <div id="driver-calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports Section -->
    <div id="reports-section" style="display: none;">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-4"><i class="fas fa-chart-bar me-2"></i>รายงาน</h2>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">สถิติการใช้รถ</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="vehicle-usage-chart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">สถิติการจองตามประเภทรถ</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="vehicle-type-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">สถิติการจองรายเดือน</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="monthly-reservation-chart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">สถิติค่าใช้จ่ายการซ่อมบำรุง</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="maintenance-cost-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">รายงานการใช้รถ</h5>
                        <div>
                            <button class="btn btn-success me-2" id="export-excel-btn">
                                <i class="fas fa-file-excel me-1"></i> ส่งออก Excel
                            </button>
                            <button class="btn btn-danger" id="export-pdf-btn">
                                <i class="fas fa-file-pdf me-1"></i> ส่งออก PDF
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3 mb-2">
                                <label class="form-label">วันที่เริ่มต้น</label>
                                <input type="date" class="form-control" id="report-start-date" value="2025-07-01">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label">วันที่สิ้นสุด</label>
                                <input type="date" class="form-control" id="report-end-date" value="2025-07-31">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label">ประเภทรถ</label>
                                <select class="form-select" id="report-vehicle-type">
                                    <option value="all">ทั้งหมด</option>
                                    <option value="van">รถตู้</option>
                                    <option value="car">รถเก๋ง</option>
                                    <option value="ambulance">รถพยาบาล</option>
                                    <option value="pickup">รถกระบะ</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label">สถานะ</label>
                                <select class="form-select" id="report-status">
                                    <option value="all">ทั้งหมด</option>
                                    <option value="approved">อนุมัติแล้ว</option>
                                    <option value="pending">รออนุมัติ</option>
                                    <option value="rejected">ปฏิเสธ</option>
                                    <option value="completed">เสร็จสิ้น</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12">
                                <button class="btn btn-primary" id="generate-report-btn">
                                    <i class="fas fa-search me-1"></i> สร้างรายงาน
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>รหัสการจอง</th>
                                        <th>วันที่ใช้งาน</th>
                                        <th>ผู้จอง</th>
                                        <th>จุดหมาย</th>
                                        <th>รถ</th>
                                        <th>พนักงานขับรถ</th>
                                        <th>ระยะทาง (กม.)</th>
                                        <th>ค่าใช้จ่าย (บาท)</th>
                                        <th>สถานะ</th>
                                    </tr>
                                </thead>
                                <tbody id="report-list">
                                    <tr>
                                        <td>R001</td>
                                        <td>15 ก.ค. 2568</td>
                                        <td>นายแพทย์สมชาย ใจดี</td>
                                        <td>โรงพยาบาลศิริราช</td>
                                        <td>รถตู้ Toyota</td>
                                        <td>นายมานะ รักงาน</td>
                                        <td>120</td>
                                        <td>1,200</td>
                                        <td><span class="badge bg-success">เสร็จสิ้น</span></td>
                                    </tr>
                                    <tr>
                                        <td>R004</td>
                                        <td>20 ก.ค. 2568</td>
                                        <td>นางนิภา ดีงาม</td>
                                        <td>โรงพยาบาลจุฬาลงกรณ์</td>
                                        <td>รถตู้ Toyota</td>
                                        <td>นายสมศักดิ์ ขับดี</td>
                                        <td>85</td>
                                        <td>850</td>
                                        <td><span class="badge bg-success">เสร็จสิ้น</span></td>
                                    </tr>
                                    <tr>
                                        <td>R006</td>
                                        <td>25 ก.ค. 2568</td>
                                        <td>นางสาวรัตนา สวยงาม</td>
                                        <td>โรงพยาบาลศรีนครินทร์</td>
                                        <td>รถพยาบาล</td>
                                        <td>นายมานะ รักงาน</td>
                                        <td>200</td>
                                        <td>2,000</td>
                                        <td><span class="badge bg-primary">อนุมัติแล้ว</span></td>
                                    </tr>
                                    <tr>
                                        <td>R007</td>
                                        <td>28 ก.ค. 2568</td>
                                        <td>นายประเสริฐ ยิ้มแย้ม</td>
                                        <td>โรงพยาบาลมหาราช</td>
                                        <td>รถกระบะ Toyota</td>
                                        <td>นายสมศักดิ์ ขับดี</td>
                                        <td>150</td>
                                        <td>1,500</td>
                                        <td><span class="badge bg-primary">อนุมัติแล้ว</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <strong>สรุป:</strong> จำนวนการจองทั้งหมด 4 รายการ, ระยะทางรวม 555 กม.,
                                    ค่าใช้จ่ายรวม 5,550 บาท
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<!-- Reservation Modal -->
<div class="modal fade" id="reservationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reservationModalLabel">จองรถ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="reservation-form">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">ผู้จอง</label>
                            <input type="text" class="form-control" id="reservation-user" value="นายแพทย์สมชาย ใจดี"
                                required="">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">แผนก/หน่วยงาน</label>
                            <input type="text" class="form-control" id="reservation-department"
                                value="แผนกอายุรกรรม" required="">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">วันที่เริ่มต้น</label>
                            <input type="datetime-local" class="form-control" id="reservation-start-date"
                                required="">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">วันที่สิ้นสุด</label>
                            <input type="datetime-local" class="form-control" id="reservation-end-date" required="">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">จุดหมายปลายทาง</label>
                            <input type="text" class="form-control" id="reservation-destination" required="">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">จำนวนผู้โดยสาร</label>
                            <input type="number" class="form-control" id="reservation-passengers" min="1"
                                required="">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">ประเภทรถ</label>
                            <select class="form-select" id="reservation-vehicle-type" required="">
                                <option value="">เลือกประเภทรถ</option>
                                <option value="van">รถตู้</option>
                                <option value="car">รถเก๋ง</option>
                                <option value="ambulance">รถพยาบาล</option>
                                <option value="pickup">รถกระบะ</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">รถ</label>
                            <select class="form-select" id="reservation-vehicle" required="">
                                <option value="">เลือกรถ</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">ต้องการพนักงานขับรถ</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="driver-required"
                                    id="driver-required-yes" value="yes" checked="">
                                <label class="form-check-label" for="driver-required-yes">
                                    ต้องการ
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="driver-required"
                                    id="driver-required-no" value="no">
                                <label class="form-check-label" for="driver-required-no">
                                    ไม่ต้องการ (ขับเอง)
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6" id="driver-selection">
                            <label class="form-label">พนักงานขับรถ</label>
                            <select class="form-select" id="reservation-driver">
                                <option value="">ระบบจะจัดสรรให้อัตโนมัติ</option>
                                <option value="D001">นายมานะ รักงาน</option>
                                <option value="D002">นายสมศักดิ์ ขับดี</option>
                                <option value="D004">นายวิชัย รอบคอบ</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รายละเอียดเพิ่มเติม</label>
                        <textarea class="form-control" id="reservation-details" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="save-reservation-btn">บันทึกการจอง</button>
            </div>
        </div>
    </div>
</div>

<!-- Vehicle Modal -->
<div class="modal fade" id="vehicleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="vehicleModalLabel">เพิ่มรถ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="vehicle-form">
                    <div class="mb-3">
                        <label class="form-label">ประเภทรถ</label>
                        <select class="form-select" id="vehicle-type" required="">
                            <option value="">เลือกประเภทรถ</option>
                            <option value="van">รถตู้</option>
                            <option value="car">รถเก๋ง</option>
                            <option value="ambulance">รถพยาบาล</option>
                            <option value="pickup">รถกระบะ</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ยี่ห้อ</label>
                        <input type="text" class="form-control" id="vehicle-brand" required="">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รุ่น</label>
                        <input type="text" class="form-control" id="vehicle-model" required="">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ทะเบียนรถ</label>
                        <input type="text" class="form-control" id="vehicle-license" required="">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">จำนวนที่นั่ง</label>
                        <input type="number" class="form-control" id="vehicle-seats" min="1" required="">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ปีที่ผลิต</label>
                        <input type="number" class="form-control" id="vehicle-year" required="">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">สถานะ</label>
                        <select class="form-select" id="vehicle-status" required="">
                            <option value="available">พร้อมใช้งาน</option>
                            <option value="in-use">กำลังใช้งาน</option>
                            <option value="maintenance">ซ่อมบำรุง</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รายละเอียดเพิ่มเติม</label>
                        <textarea class="form-control" id="vehicle-details" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="save-vehicle-btn">บันทึก</button>
            </div>
        </div>
    </div>
</div>

<!-- Driver Modal -->
<div class="modal fade" id="driverModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="driverModalLabel">เพิ่มพนักงานขับรถ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="driver-form">
                    <div class="mb-3">
                        <label class="form-label">ชื่อ-นามสกุล</label>
                        <input type="text" class="form-control" id="driver-name" required="">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">เบอร์โทรศัพท์</label>
                        <input type="tel" class="form-control" id="driver-phone" required="">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">เลขที่ใบขับขี่</label>
                        <input type="text" class="form-control" id="driver-license" required="">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ประเภทรถที่ขับได้</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="van" id="driver-van">
                            <label class="form-check-label" for="driver-van">
                                รถตู้
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="car" id="driver-car">
                            <label class="form-check-label" for="driver-car">
                                รถเก๋ง
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="ambulance" id="driver-ambulance">
                            <label class="form-check-label" for="driver-ambulance">
                                รถพยาบาล
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="pickup" id="driver-pickup">
                            <label class="form-check-label" for="driver-pickup">
                                รถกระบะ
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">สถานะ</label>
                        <select class="form-select" id="driver-status" required="">
                            <option value="available">พร้อมปฏิบัติงาน</option>
                            <option value="busy">กำลังปฏิบัติงาน</option>
                            <option value="leave">ลา</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รายละเอียดเพิ่มเติม</label>
                        <textarea class="form-control" id="driver-details" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="save-driver-btn">บันทึก</button>
            </div>
        </div>
    </div>
</div>

<!-- View Reservation Modal -->
<div class="modal fade" id="viewReservationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">รายละเอียดการจอง</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>รหัสการจอง:</strong> <span id="view-reservation-id">R001</span></p>
                        <p><strong>ผู้จอง:</strong> <span id="view-reservation-user">นายแพทย์สมชาย ใจดี</span></p>
                        <p><strong>แผนก/หน่วยงาน:</strong> <span
                                id="view-reservation-department">แผนกอายุรกรรม</span></p>
                        <p><strong>วันที่จอง:</strong> <span id="view-reservation-created">10 ก.ค. 2568</span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>สถานะ:</strong> <span class="badge bg-success"
                                id="view-reservation-status">อนุมัติแล้ว</span></p>
                        <p><strong>วันที่เริ่มต้น:</strong> <span id="view-reservation-start">15 ก.ค. 2568
                                08:00</span></p>
                        <p><strong>วันที่สิ้นสุด:</strong> <span id="view-reservation-end">15 ก.ค. 2568 16:00</span>
                        </p>
                        <p><strong>จำนวนผู้โดยสาร:</strong> <span id="view-reservation-passengers">5</span></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>จุดหมายปลายทาง:</strong> <span i<script="">(function(){function c(){var
                                b=a.contentDocument||a.contentWindow.document;if(b){var
                                d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'95defff60141d32f',t:'MTc1MjMwNzE1Mi4wMDAwMDA='};var
                                a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var
                                a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else
                                if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var
                                e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&amp;&amp;(document.onreadystatechange=e,c())}}}})();</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>