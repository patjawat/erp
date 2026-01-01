<div class="card border-0 shadow-sm rounded-4 overflow-hidden mt-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr class="text-secondary fw-bold text-uppercase">
                    <th class="px-4 py-3" style="width: 40px;"><i class="bi bi-square text-muted"></i></th>
                    <th class="px-3 py-3">รายการ / รหัส</th>
                    <th class="px-3 py-3">ผู้ส่งคำขอ</th>
                    <th class="px-3 py-3">วันที่</th>
                    <th class="px-3 py-3 text-center">สถานะ</th>
                    <th class="px-3 py-3 text-end">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                <?php foreach($dataProvider->getModels() as $item):?>
                <tr>
                    <td class="px-4 py-3"><i class="bi bi-square text-light"></i></td>
                    <td class="px-3 py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-light d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                <i class="bi bi-calendar-event text-primary"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark"><?php echo $item->name?></div>
                                <div class="text-muted fw-bold" style="font-size: 10px;">LV-2568-001</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-3 py-3">
                        <div class="small fw-semibold text-dark">นางสาววิภา รักดี</div>
                        <div class="text-muted" style="font-size: 10px;">ผู้ป่วยนอก (OPD)</div>
                    </td>
                    <td class="px-3 py-3 fw-bold text-muted">2025-12-10</td>
                    <td class="px-3 py-3 text-center">
                        <span class="badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 text-uppercase" style="font-size: 10px;">Pending</span>
                    </td>
                    <td class="px-3 py-3 text-end">
                        <button class="btn btn-link text-success p-1"><i class="bi bi-check-circle fs-5"></i></button>
                        <button class="btn btn-link text-danger p-1"><i class="bi bi-x-circle fs-5"></i></button>
                    </td>
                </tr>
                <?php endforeach;?>
                <!-- <tr>
                    <td class="px-4 py-3"><i class="bi bi-square text-light"></i></td>
                    <td class="px-3 py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-light d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                <i class="bi bi-box-seam text-info"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">เบิกเวชภัณฑ์สิ้นเปลืองประจำเดือน</div>
                                <div class="text-muted fw-bold" style="font-size: 10px;">INV-68-102</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-3 py-3">
                        <div class="small fw-semibold text-dark">นายสมชาย ใจดี</div>
                        <div class="text-muted" style="font-size: 10px;">ห้องฉุกเฉิน (ER)</div>
                    </td>
                    <td class="px-3 py-3 fw-bold text-muted">2025-12-08</td>
                    <td class="px-3 py-3 text-center">
                        <span class="badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 text-uppercase" style="font-size: 10px;">Pending</span>
                    </td>
                    <td class="px-3 py-3 text-end">
                        <button class="btn btn-link text-success p-1"><i class="bi bi-check-circle fs-5"></i></button>
                        <button class="btn btn-link text-danger p-1"><i class="bi bi-x-circle fs-5"></i></button>
                    </td>
                </tr> -->
            </tbody>
        </table>
    </div>
</div>