
  <style>
    .navbar-brand {
      font-weight: bold;
    }
    .nav-link {
      color: #333;
    }
    .nav-link:hover {
      color: #0d6efd;
    }
    .card {
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }
    .status-badge {
      font-size: 0.85rem;
      padding: 5px 10px;
    }
    .star-rating input {
      display: none;
    }
    .star-rating label {
      cursor: pointer;
      font-size: 25px;
      color: #ccc;
    }
    .star-rating input:checked ~ label {
      color: #ffcc00;
    }
    .star-rating label:hover,
    .star-rating label:hover ~ label {
      color: #ffcc00;
    }
    .dashboard-card {
      border-left: 4px solid;
      transition: transform 0.3s;
    }
    .dashboard-card:hover {
      transform: translateY(-5px);
    }
    .progress {
      height: 10px;
    }
  </style>
</head>
<body>
  <!-- Navbar เมนูส่วนบน -->
  <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <div class="container">
      <a class="navbar-brand" href="#">
        <i class="fas fa-tools me-2"></i>ระบบแจ้งซ่อมออนไลน์
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link active" href="#" id="homeLink">
              <i class="fas fa-home me-1"></i>หน้าหลัก
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#" id="newRequestLink">
              <i class="fas fa-plus-circle me-1"></i>แจ้งซ่อมใหม่
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#" id="historyLink">
              <i class="fas fa-history me-1"></i>ประวัติการแจ้งซ่อม
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#" id="adminLink">
              <i class="fas fa-user-cog me-1"></i>สำหรับผู้ดูแลระบบ
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#" id="logoutLink">
              <i class="fas fa-sign-out-alt me-1"></i>ออกจากระบบ
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- หน้าหลัก User Dashboard -->
  <div class="container mt-4 mb-5" id="homeSection">
    <div class="row">
      <div class="col-12">
        <h2><i class="fas fa-tachometer-alt me-2"></i>แผงควบคุม</h2>
        <hr>
      </div>
    </div>
    <div class="row">
      <div class="col-md-3">
        <div class="card dashboard-card" style="border-left-color: #0d6efd;">
          <div class="card-body">
            <h5 class="card-title">รอดำเนินการ</h5>
            <h2>5</h2>
            <div class="progress">
              <div class="progress-bar bg-primary" style="width: 25%"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card dashboard-card" style="border-left-color: #ffc107;">
          <div class="card-body">
            <h5 class="card-title">กำลังดำเนินการ</h5>
            <h2>8</h2>
            <div class="progress">
              <div class="progress-bar bg-warning" style="width: 40%"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card dashboard-card" style="border-left-color: #20c997;">
          <div class="card-body">
            <h5 class="card-title">เสร็จสิ้น</h5>
            <h2>12</h2>
            <div class="progress">
              <div class="progress-bar bg-success" style="width: 60%"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card dashboard-card" style="border-left-color: #dc3545;">
          <div class="card-body">
            <h5 class="card-title">ยกเลิก</h5>
            <h2>2</h2>
            <div class="progress">
              <div class="progress-bar bg-danger" style="width: 10%"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-4">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h5><i class="fas fa-clipboard-list me-2"></i>งานซ่อมล่าสุด</h5>
          </div>
          <div class="card-body">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>เลขที่</th>
                  <th>วันที่แจ้ง</th>
                  <th>ประเภท</th>
                  <th>รายละเอียด</th>
                  <th>สถานะ</th>
                  <th>ดำเนินการ</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>R-00125</td>
                  <td>20/03/2025</td>
                  <td>ซ่อมทั่วไป</td>
                  <td>ไฟฟ้าดับในห้องประชุม</td>
                  <td><span class="badge bg-warning text-dark status-badge">กำลังดำเนินการ</span></td>
                  <td><button class="btn btn-sm btn-primary">ติดตาม</button></td>
                </tr>
                <tr>
                  <td>R-00124</td>
                  <td>19/03/2025</td>
                  <td>ครุภัณฑ์</td>
                  <td>เครื่องปริ้นเตอร์ไม่ทำงาน</td>
                  <td><span class="badge bg-success status-badge">เสร็จสิ้น</span></td>
                  <td><button class="btn btn-sm btn-success">ให้คะแนน</button></td>
                </tr>
                <tr>
                  <td>R-00123</td>
                  <td>18/03/2025</td>
                  <td>ซ่อมทั่วไป</td>
                  <td>ท่อน้ำรั่วในห้องน้ำ</td>
                  <td><span class="badge bg-primary status-badge">รอดำเนินการ</span></td>
                  <td><button class="btn btn-sm btn-primary">ติดตาม</button></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card">
          <div class="card-header">
            <h5><i class="fas fa-calendar-alt me-2"></i>ปฏิทินงานซ่อม</h5>
          </div>
          <div class="card-body">
            <div class="alert alert-info">
              <i class="fas fa-info-circle me-2"></i>มีงานซ่อมนัดหมาย 3 รายการในสัปดาห์นี้
            </div>
            <ul class="list-group">
              <li class="list-group-item d-flex justify-content-between align-items-center">
                20/03/2025 - ซ่อมแอร์ห้องประชุม
                <span class="badge bg-primary rounded-pill">9:00</span>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                21/03/2025 - ซ่อมโต๊ะทำงาน
                <span class="badge bg-primary rounded-pill">13:30</span>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                22/03/2025 - เปลี่ยนหลอดไฟ
                <span class="badge bg-primary rounded-pill">10:00</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- แบบฟอร์มแจ้งซ่อม -->
  <div class="container mt-4 mb-5 d-none" id="newRequestSection">
    <div class="row">
      <div class="col-12">
        <h2><i class="fas fa-tools me-2"></i>แจ้งซ่อมใหม่</h2>
        <hr>
      </div>
    </div>
    <div class="row">
      <div class="col-md-8">
        <div class="card">
          <div class="card-body">
            <form>
              <div class="mb-3">
                <label class="form-label">ประเภทการแจ้งซ่อม</label>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="repairType" id="typeGeneral" checked>
                  <label class="form-check-label" for="typeGeneral">
                    แจ้งซ่อมทั่วไป (ไฟฟ้า, ประปา, อาคาร)
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="repairType" id="typeEquipment">
                  <label class="form-check-label" for="typeEquipment">
                    แจ้งซ่อมครุภัณฑ์ (คอมพิวเตอร์, เครื่องใช้สำนักงาน)
                  </label>
                </div>
              </div>
              
              <div class="mb-3" id="equipmentSection" style="display: none;">
                <label for="equipmentId" class="form-label">รหัสครุภัณฑ์</label>
                <div class="input-group">
                  <input type="text" class="form-control" id="equipmentId" placeholder="ระบุรหัสครุภัณฑ์">
                  <button class="btn btn-outline-secondary" type="button"><i class="fas fa-search"></i></button>
                </div>
                <small class="form-text text-muted">สามารถค้นหาจากรหัสครุภัณฑ์ที่ติดอยู่บนอุปกรณ์</small>
              </div>
              
              <div class="mb-3">
                <label for="location" class="form-label">สถานที่</label>
                <input type="text" class="form-control" id="location" placeholder="ระบุสถานที่ เช่น อาคาร/ชั้น/ห้อง">
              </div>
              
              <div class="mb-3">
                <label for="problemTitle" class="form-label">หัวข้อปัญหา</label>
                <input type="text" class="form-control" id="problemTitle" placeholder="ระบุหัวข้อปัญหาอย่างย่อ">
              </div>
              
              <div class="mb-3">
                <label for="problemDetail" class="form-label">รายละเอียดปัญหา</label>
                <textarea class="form-control" id="problemDetail" rows="4" placeholder="อธิบายรายละเอียดปัญหาที่พบ"></textarea>
              </div>
              
              <div class="mb-3">
                <label for="urgency" class="form-label">ระดับความเร่งด่วน</label>
                <select class="form-select" id="urgency">
                  <option value="low">ปกติ (ภายใน 3 วันทำการ)</option>
                  <option value="medium">เร่งด่วน (ภายใน 24 ชั่วโมง)</option>
                  <option value="high">เร่งด่วนมาก (ภายใน 4 ชั่วโมง)</option>
                </select>
              </div>
              
              <div class="mb-3">
                <label for="imageUpload" class="form-label">แนบรูปภาพ (ไม่เกิน 3 รูป)</label>
                <input class="form-control" type="file" id="imageUpload" multiple accept="image/*">
                <div class="mt-2" id="imagePreview"></div>
              </div>
              
              <div class="mb-3">
                <label for="contactNumber" class="form-label">เบอร์ติดต่อกลับ</label>
                <input type="text" class="form-control" id="contactNumber" placeholder="ระบุเบอร์โทรศัพท์ที่สามารถติดต่อได้">
              </div>
              
              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-2"></i>ส่งแจ้งซ่อม</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      
      <div class="col-md-4">
        <div class="card mb-4">
          <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>คำแนะนำการแจ้งซ่อม</h5>
          </div>
          <div class="card-body">
            <ul class="list-unstyled">
              <li><i class="fas fa-check-circle text-success me-2"></i>ระบุตำแหน่งให้ชัดเจน</li>
              <li><i class="fas fa-check-circle text-success me-2"></i>อธิบายปัญหาอย่างละเอียด</li>
              <li><i class="fas fa-check-circle text-success me-2"></i>แนบรูปภาพประกอบ</li>
              <li><i class="fas fa-check-circle text-success me-2"></i>ให้เบอร์ติดต่อที่สามารถติดต่อได้จริง</li>
            </ul>
          </div>
        </div>
        
        <div class="card">
          <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>ข้อควรระวัง</h5>
          </div>
          <div class="card-body">
            <p>กรณีที่พบปัญหาฉุกเฉินที่อาจก่อให้เกิดอันตราย เช่น ไฟฟ้าลัดวงจร ท่อน้ำแตก กรุณาติดต่อแผนกซ่อมบำรุงโดยตรงที่เบอร์ 1234 หรือ 5678</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- หน้าติดตามงานซ่อม -->
  <div class="container mt-4 mb-5 d-none" id="historySection">
    <div class="row">
      <div class="col-12">
        <h2><i class="fas fa-history me-2"></i>ประวัติการแจ้งซ่อม</h2>
        <hr>
      </div>
    </div>
    
    <div class="row mb-3">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body">
            <form class="row g-3">
              <div class="col-md-4">
                <input type="text" class="form-control" placeholder="ค้นหาจากเลขที่หรือหัวข้อปัญหา">
              </div>
              <div class="col-md-3">
                <select class="form-select">
                  <option selected>สถานะทั้งหมด</option>
                  <option>รอดำเนินการ</option>
                  <option>กำลังดำเนินการ</option>
                  <option>เสร็จสิ้น</option>
                  <option>ยกเลิก</option>
                </select>
              </div>
              <div class="col-md-3">
                <select class="form-select">
                  <option selected>ประเภททั้งหมด</option>
                  <option>ซ่อมทั่วไป</option>
                  <option>ซ่อมครุภัณฑ์</option>
                </select>
              </div>
              <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>ค้นหา</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>เลขที่</th>
                    <th>วันที่แจ้ง</th>
                    <th>ประเภท</th>
                    <th>หัวข้อปัญหา</th>
                    <th>สถานที่</th>
                    <th>สถานะ</th>
                    <th>ความคืบหน้า</th>
                    <th>ดำเนินการ</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>R-00125</td>
                    <td>20/03/2025</td>
                    <td>ซ่อมทั่วไป</td>
                    <td>ไฟฟ้าดับในห้องประชุม</td>
                    <td>อาคาร A ชั้น 2 ห้อง 201</td>
                    <td><span class="badge bg-warning text-dark status-badge">กำลังดำเนินการ</span></td>
                    <td>
                      <div class="progress">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 50%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                    </td>
                    <td><button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#trackModal"><i class="fas fa-eye me-1"></i>ติดตาม</button></td>
                  </tr>
                  <tr>
                    <td>R-00124</td>
                    <td>19/03/2025</td>
                    <td>ครุภัณฑ์</td>
                    <td>เครื่องปริ้นเตอร์ไม่ทำงาน</td>
                    <td>อาคาร B ชั้น 1 ห้องธุรการ</td>
                    <td><span class="badge bg-success status-badge">เสร็จสิ้น</span></td>
                    <td>
                      <div class="progress">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                    </td>
                    <td><button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#ratingModal"><i class="fas fa-star me-1"></i>ให้คะแนน</button></td>
                  </tr>
                  <tr>
                    <td>R-00123</td>
                    <td>18/03/2025</td>
                    <td>ซ่อมทั่วไป</td>
                    <td>ท่อน้ำรั่วในห้องน้ำ</td>
                    <td>อาคาร A ชั้น 1 ห้องน้ำชาย</td>
                    <td><span class="badge bg-primary status-badge">รอดำเนินการ</span></td>
                    <td>
                      <div class="progress">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 10%" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                    </td>
                    <td><button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#trackModal"><i class="fas fa-eye me-1"></i>ติดตาม</button></td>
                  </tr>
                  <tr>
                    <td>R-00122</td>
                    <td>15/03/2025</td>
                    <td>ครุภัณฑ์</td>
                    <td>เก้าอี้สำนักงานชำรุด</td>
                    <td>อาคาร C ชั้น 3 ห้อง 305</td>
                    <td><span class="badge bg-success status-badge">เสร็จสิ้น</span></td>
                    <td>
                      <div class="progress">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                    </td>
                    <td><button class="btn btn-sm btn-secondary"><i class="fas fa-check me-1"></i>ให้คะแนนแล้ว</button></td>
                  </tr>
                  <tr>
                    <td>R-00121</td>
                    <td>10/03/2025</td>
                    <td>ซ่อมทั่วไป</td>
                    <td>ประตูเปิดไม่ได้</td>
                    <td>อาคาร B ชั้น 2 ห้อง 204</td>
                    <td><span class="badge bg-danger status-badge">ยกเลิก</span></td>
                    <td>
                      <div class="progress">
                        <div class="progress-bar bg-danger" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                    </td>
                    <td><button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#trackModal"><i class="fas fa-eye me-1"></i>ดูรายละเอียด</button></td>
                  </tr>
                </tbody>
              </table>
            </div>
            <nav aria-label="Page navigation">
              <ul class="pagination justify-content-center">
                <li class="page-item disabled">
                  <a class="page-link" href="#" tabindex="-1" aria-disabled="true">ก่อนหน้า</a>
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

  <!-- หน้า Admin จัดการงานซ่อม -->
  <div class="container-fluid mt-4 mb-5 d-none" id="adminSection">
    <div class="row">
      <div class="col-md-2">
        <div class="list-group">
          <a href="#" class="list-group-item list-group-item-action active">
            <i class="fas fa-tachometer-alt me-2"></i>แผงควบคุม
          </a>
          <a href="#" class="list-group-item list-group-item-action">
            <i class="fas fa-clipboard-list me-2"></i>รายการแจ้งซ่อม
          </a>
          <a href="#" class="list-group-item list-group-item-action">
            <i class="fas fa-tools me-2"></i>จัดการงานซ่อม
          </a>
          <a href="#" class="list-group-item list-group-item-action">
            <i class="fas fa-warehouse me-2"></i>คลังอะไหล่
          </a>
          <a href="#" class="list-group-item list-group-item-action">
            <i class="fas fa-building me-2"></i>บริษัทคู่สัญญา
          </a>
          <a href="#" class="list-group-item list-group-item-action">
            <i class="fas fa-clipboard-check me-2"></i>รายงานสรุป
          </a>
          <a href="#" class="list-group-item list-group-item-action">
            <i class="fas fa-users me-2"></i>จัดการผู้ใช้งาน
          </a>
          <a href="#" class="list-group-item list-group-item-action">
            <i class="fas fa-cog me-2"></i>ตั้งค่าระบบ
          </a>
        </div>
      </div>
      
      <div class="col-md-10">
        <div class="card">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>แผงควบคุมผู้ดูแลระบบ</h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-3">
                <div class="card bg-primary text-white">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                      <div>
                        <h6 class="card-title">งานทั้งหมด</h6>
                        <h2 class="mb-0">152</h2>
                      </div>
                      <div>
                        <i class="fas fa-tasks fa-3x"></i>
                      </div>
                    </div>
                    <small>เพิ่มขึ้น 12% จากเดือนที่แล้ว</small>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="card bg-warning text-dark">