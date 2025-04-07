<?php
$this->title = 'Meetings';
$this->params['breadcrumbs'][] = $this->title;
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-car fs-x1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-car fs-x1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<style>
    .nav-pills .nav-link {
      color: #333;
      border-radius: 0;
      padding: 10px 20px;
    }
    .nav-pills .nav-link.active {
      background-color: transparent;
      border-bottom: 2px solid #0d6efd;
      color: #0d6efd;
      font-weight: 500;
    }
    .meeting-table {
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .meeting-table th {
      font-weight: normal;
      color: #666;
      padding: 15px;
      border-bottom: 1px solid #eee;
    }
    .meeting-table td {
      padding: 15px;
      vertical-align: middle;
      border-bottom: 1px solid #eee;
    }
    .status-badge {
      display: inline-block;
      padding: 5px 15px;
      border-radius: 20px;
      font-size: 0.8rem;
    }
    .status-badge.approved {
      background-color: #f8f9fa;
      color: #0d6efd;
    }
    .status-badge.confirmed {
      background-color: #212529;
      color: white;
    }
    .status-badge.rejected {
      background-color: #dc3545;
      color: white;
    }
    .status-badge.cancelled {
      background-color: #dc3545;
      color: white;
    }
    .name-title {
      font-weight: 500;
      margin-bottom: 0;
    }
    .department {
      font-size: 0.8rem;
      color: #6c757d;
    }
    .action-icon {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-right: 5px;
      cursor: pointer;
    }
    .action-icon.view {
      background-color: #f8f9fa;
    }
    .action-icon.approve {
      background-color: #e8f5e9;
      color: #4caf50;
    }
    .action-icon.reject {
      background-color: #ffebee;
      color: #f44336;
    }
  </style>
<div class="container">
<?=$this->render('menu')?>
    <!-- Navigation Pills -->
    <ul class="nav nav-pills mb-4" id="meetingTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-tab-pane" type="button" role="tab" aria-controls="all-tab-pane" aria-selected="true">ทั้งหมด</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending-tab-pane" type="button" role="tab" aria-controls="pending-tab-pane" aria-selected="false" tabindex="-1">รอการอนุมัติ</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved-tab-pane" type="button" role="tab" aria-controls="approved-tab-pane" aria-selected="false" tabindex="-1">อนุมัติแล้ว</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected-tab-pane" type="button" role="tab" aria-controls="rejected-tab-pane" aria-selected="false" tabindex="-1">ปฏิเสธ/ยกเลิก</button>
      </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="meetingTabsContent">
      <!-- All Meetings Tab -->
      <div class="tab-pane fade active show" id="all-tab-pane" role="tabpanel" aria-labelledby="all-tab" tabindex="0">
        <div class="meeting-table table-responsive">
          <table class="table table-borderless mb-0">
            <thead>
              <tr>
                <th>ผู้จอง</th>
                <th>ห้องประชุม</th>
                <th>วันที่</th>
                <th>เวลา</th>
                <th>สถานะ</th>
                <th class="text-end">จัดการ</th>
              </tr>
            </thead>
            <tbody>
              <!-- Row 1 -->
              <tr>
                <td>
                  <p class="name-title">นายสมชาย ใจดี</p>
                  <span class="department">แผนกบุคคล</span>
                </td>
                <td>ห้องประชุมใหญ่</td>
                <td>10 เม.ย. 2025</td>
                <td>09:00 - 12:00 น.</td>
                <td>
                  <span class="status-badge approved">รอการอนุมัติ</span>
                </td>
                <td class="text-end">
                  <div class="action-icon view">
                    <i class="bi bi-eye"></i>
                  </div>
                  <div class="action-icon approve d-inline-flex">
                    <i class="bi bi-check-lg"></i>
                  </div>
                  <div class="action-icon reject d-inline-flex">
                    <i class="bi bi-x-lg"></i>
                  </div>
                </td>
              </tr>
              
              <!-- Row 2 -->
              <tr>
                <td>
                  <p class="name-title">นางสาวสมหญิง รักดี</p>
                  <span class="department">แผนกการเงิน</span>
                </td>
                <td>ห้องประชุมเล็ก</td>
                <td>11 เม.ย. 2025</td>
                <td>13:00 - 16:00 น.</td>
                <td>
                  <span class="status-badge confirmed">อนุมัติแล้ว</span>
                </td>
                <td class="text-end">
                  <div class="action-icon view">
                    <i class="bi bi-eye"></i>
                  </div>
                </td>
              </tr>
              
              <!-- Row 3 -->
              <tr>
                <td>
                  <p class="name-title">นายวิชัย สุขใจ</p>
                  <span class="department">แผนกไอที</span>
                </td>
                <td>ห้องประชุมกลาง</td>
                <td>12 เม.ย. 2025</td>
                <td>10:00 - 11:30 น.</td>
                <td>
                  <span class="status-badge approved">รอการอนุมัติ</span>
                </td>
                <td class="text-end">
                  <div class="action-icon view">
                    <i class="bi bi-eye"></i>
                  </div>
                  <div class="action-icon approve d-inline-flex">
                    <i class="bi bi-check-lg"></i>
                  </div>
                  <div class="action-icon reject d-inline-flex">
                    <i class="bi bi-x-lg"></i>
                  </div>
                </td>
              </tr>
              
              <!-- Row 4 -->
              <tr>
                <td>
                  <p class="name-title">นางสาวนิกร ดีงาม</p>
                  <span class="department">แผนกการตลาด</span>
                </td>
                <td>ห้องประชุมใหญ่</td>
                <td>13 เม.ย. 2025</td>
                <td>14:00 - 16:00 น.</td>
                <td>
                  <span class="status-badge confirmed">อนุมัติแล้ว</span>
                </td>
                <td class="text-end">
                  <div class="action-icon view">
                    <i class="bi bi-eye"></i>
                  </div>
                </td>
              </tr>
              
              <!-- Row 5 -->
              <tr>
                <td>
                  <p class="name-title">นายสมศักดิ์ ยิ่งยง</p>
                  <span class="department">แผนกขาย</span>
                </td>
                <td>ห้องประชุมกลาง</td>
                <td>14 เม.ย. 2025</td>
                <td>09:30 - 12:00 น.</td>
                <td>
                  <span class="status-badge rejected">ปฏิเสธ</span>
                </td>
                <td class="text-end">
                  <div class="action-icon view">
                    <i class="bi bi-eye"></i>
                  </div>
                </td>
              </tr>
              
              <!-- Row 6 -->
              <tr>
                <td>
                  <p class="name-title">นางสาวรัตนา ดวงดี</p>
                  <span class="department">แผนกบุคคล</span>
                </td>
                <td>ห้องประชุมเล็ก</td>
                <td>15 เม.ย. 2025</td>
                <td>13:30 - 15:00 น.</td>
                <td>
                  <span class="status-badge cancelled">ยกเลิก</span>
                </td>
                <td class="text-end">
                  <div class="action-icon view">
                    <i class="bi bi-eye"></i>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pending Approval Tab -->
      <div class="tab-pane fade" id="pending-tab-pane" role="tabpanel" aria-labelledby="pending-tab" tabindex="0">
        <div class="meeting-table table-responsive">
          <table class="table table-borderless mb-0">
            <thead>
              <tr>
                <th>ผู้จอง</th>
                <th>ห้องประชุม</th>
                <th>วันที่</th>
                <th>เวลา</th>
                <th>สถานะ</th>
                <th class="text-end">จัดการ</th>
              </tr>
            </thead>
            <tbody>
              <!-- Row 1 -->
              <tr>
                <td>
                  <p class="name-title">นายสมชาย ใจดี</p>
                  <span class="department">แผนกบุคคล</span>
                </td>
                <td>ห้องประชุมใหญ่</td>
                <td>10 เม.ย. 2025</td>
                <td>09:00 - 12:00 น.</td>
                <td>
                  <span class="status-badge approved">รอการอนุมัติ</span>
                </td>
                <td class="text-end">
                  <div class="action-icon view">
                    <i class="bi bi-eye"></i>
                  </div>
                  <div class="action-icon approve d-inline-flex">
                    <i class="bi bi-check-lg"></i>
                  </div>
                  <div class="action-icon reject d-inline-flex">
                    <i class="bi bi-x-lg"></i>
                  </div>
                </td>
              </tr>
              
              <!-- Row 3 -->
              <tr>
                <td>
                  <p class="name-title">นายวิชัย สุขใจ</p>
                  <span class="department">แผนกไอที</span>
                </td>
                <td>ห้องประชุมกลาง</td>
                <td>12 เม.ย. 2025</td>
                <td>10:00 - 11:30 น.</td>
                <td>
                  <span class="status-badge approved">รอการอนุมัติ</span>
                </td>
                <td class="text-end">
                  <div class="action-icon view">
                    <i class="bi bi-eye"></i>
                  </div>
                  <div class="action-icon approve d-inline-flex">
                    <i class="bi bi-check-lg"></i>
                  </div>
                  <div class="action-icon reject d-inline-flex">
                    <i class="bi bi-x-lg"></i>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Approved Tab -->
      <div class="tab-pane fade" id="approved-tab-pane" role="tabpanel" aria-labelledby="approved-tab" tabindex="0">
        <div class="meeting-table table-responsive">
          <table class="table table-borderless mb-0">
            <thead>
              <tr>
                <th>ผู้จอง</th>
                <th>ห้องประชุม</th>
                <th>วันที่</th>
                <th>เวลา</th>
                <th>สถานะ</th>
                <th class="text-end">จัดการ</th>
              </tr>
            </thead>
            <tbody>
              <!-- Row 2 -->
              <tr>
                <td>
                  <p class="name-title">นางสาวสมหญิง รักดี</p>
                  <span class="department">แผนกการเงิน</span>
                </td>
                <td>ห้องประชุมเล็ก</td>
                <td>11 เม.ย. 2025</td>
                <td>13:00 - 16:00 น.</td>
                <td>
                  <span class="status-badge confirmed">อนุมัติแล้ว</span>
                </td>
                <td class="text-end">
                  <div class="action-icon view">
                    <i class="bi bi-eye"></i>
                  </div>
                </td>
              </tr>
              
              <!-- Row 4 -->
              <tr>
                <td>
                  <p class="name-title">นางสาวนิกร ดีงาม</p>
                  <span class="department">แผนกการตลาด</span>
                </td>
                <td>ห้องประชุมใหญ่</td>
                <td>13 เม.ย. 2025</td>
                <td>14:00 - 16:00 น.</td>
                <td>
                  <span class="status-badge confirmed">อนุมัติแล้ว</span>
                </td>
                <td class="text-end">
                  <div class="action-icon view">
                    <i class="bi bi-eye"></i>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Rejected/Cancelled Tab -->
      <div class="tab-pane fade" id="rejected-tab-pane" role="tabpanel" aria-labelledby="rejected-tab" tabindex="0">
        <div class="meeting-table table-responsive">
          <table class="table table-borderless mb-0">
            <thead>
              <tr>
                <th>ผู้จอง</th>
                <th>ห้องประชุม</th>
                <th>วันที่</th>
                <th>เวลา</th>
                <th>สถานะ</th>
                <th class="text-end">จัดการ</th>
              </tr>
            </thead>
            <tbody>
              <!-- Row 5 -->
              <tr>
                <td>
                  <p class="name-title">นายสมศักดิ์ ยิ่งยง</p>
                  <span class="department">แผนกขาย</span>
                </td>
                <td>ห้องประชุมกลาง</td>
                <td>14 เม.ย. 2025</td>
                <td>09:30 - 12:00 น.</td>
                <td>
                  <span class="status-badge rejected">ปฏิเสธ</span>
                </td>
                <td class="text-end">
                  <div class="action-icon view">
                    <i class="bi bi-eye"></i>
                  </div>
                </td>
              </tr>
              
              <!-- Row 6 -->
              <tr>
                <td>
                  <p class="name-title">นางสาวรัตนา ดวงดี</p>
                  <span class="department">แผนกบุคคล</span>
                </td>
                <td>ห้องประชุมเล็ก</td>
                <td>15 เม.ย. 2025</td>
                <td>13:30 - 15:00 น.</td>
                <td>
                  <span class="status-badge cancelled">ยกเลิก</span>
                </td>
                <td class="text-end">
                  <div class="action-icon view">
                    <i class="bi bi-eye"></i>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>