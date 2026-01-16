<?php

use yii\helpers\Html;

?>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="d-flex gap-3">
                    <div class="position-relative">
                        <div class="rounded-4 shadow-sm d-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info" style="width: 64px; height: 64px;"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-cart" aria-hidden="true">
                                <circle cx="8" cy="21" r="1"></circle>
                                <circle cx="19" cy="21" r="1"></circle>
                                <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                            </svg></div>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-1"><?= $model->purchase->assetType->title ?? '-' ?></h5>
                        <div class="d-flex align-items-center gap-2 text-muted small">
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-light-subtle rounded-pill fw-medium px-2 py-1">
                            <?= $model?->purchase?->getEmployee()?->departmentName() ?? '-' ?>
                        </span>
                        <span class="vr opacity-25"></span><span>ผู้ขอ: <?= $model?->purchase?->getEmployee()->fullname ?? '-' ?></span>
                        <span class="vr opacity-25"></span><span>วันที่: <?= $model?->purchase?->viewCreatedAt()  ?? '-' ?></span>
                    </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-12">

                    <div class="d-flex  align-items-center  gap-2 mb-4">
                        <div class="p-2 bg-primary bg-opacity-10 rounded-circle text-primary"><svg
                                xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-file-text" aria-hidden="true">
                                <path
                                    d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z">
                                </path>
                                <path d="M14 2v5a1 1 0 0 0 1 1h5"></path>
                                <path d="M10 9H8"></path>
                                <path d="M16 13H8"></path>
                                <path d="M16 17H8"></path>
                            </svg>
                        </div>
                        <div>

                            <h6 class="fw-bold mb-0 text-dark">รายละเอียดและความจำเป็น</h6>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded-3 border border-light-subtle">
                        <p class="mb-0 text-dark"><?= $model->purchase->data_json['comment'] ?? '-' ?></p>
                    </div>
                </div>
                <div class="col-12">

                            <div class="d-flex  align-items-center  gap-2 mb-4">
                        <div class="p-2 bg-primary bg-opacity-10 rounded-circle text-primary"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-store text-primary" aria-hidden="true">
                            <path d="M15 21v-5a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v5"></path>
                            <path d="M17.774 10.31a1.12 1.12 0 0 0-1.549 0 2.5 2.5 0 0 1-3.451 0 1.12 1.12 0 0 0-1.548 0 2.5 2.5 0 0 1-3.452 0 1.12 1.12 0 0 0-1.549 0 2.5 2.5 0 0 1-3.77-3.248l2.889-4.184A2 2 0 0 1 7 2h10a2 2 0 0 1 1.653.873l2.895 4.192a2.5 2.5 0 0 1-3.774 3.244"></path>
                            <path d="M4 10.95V19a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8.05"></path>
                        </svg>
                        </div>
                        <div>

                            <h6 class="fw-bold mb-0 text-dark"> ร้านค้า/ผู้จัดจำหน่าย</h6>
                        </div>
                    </div>
                     <div class="p-3 bg-light rounded-3 border border-light-subtle">
                         <p class="mb-0 text-muted"><?= $model->purchase?->vendor?->title ?? '-'?></p>
                        </div>
                </div>
            </div>
            <hr class="border-light">
            <h6 class="fw-bold text-dark mb-3">รายการพัสดุ/ครุภัณฑ์</h6>
            <div class="table-responsive rounded-3 border border-light-subtle">
                <table class="table table-hover align-middle mb-0 text-sm">
                    <thead class="table-light">
                        <tr class="text-secondary" style="font-size: 0.85rem;">
                            <th class="py-3 ps-3 fw-semibold">รายการ</th>
                            <th class="py-3 text-center fw-semibold" style="width: 100px;">จำนวน</th>
                            <th class="py-3 text-end fw-semibold" style="width: 120px;">ราคา/หน่วย</th>
                            <th class="py-3 pe-3 text-end fw-semibold" style="width: 120px;">รวม (บาท)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($model->purchase->ListOrderItems() as $item): ?>
                            <tr>
                                <td class="ps-3 py-3 fw-medium text-dark">
                                    <?php
                                    try {
                                        echo $item->product->title;
                                    } catch (\Throwable $th) {
                                        // throw $th;
                                    }
                                    ?>
                                </td>
                                <td class="text-center py-3"><?= $item->qty ?> <span class="text-muted small"><?= $item->product->data_json['unit'] ?? '-' ?></span></td>
                                <td class="text-end py-3 text-muted">
                                    <?php
                                    try {
                                        echo number_format($item->price, 2);
                                    } catch (\Throwable $th) {
                                    }
                                    ?></td>
                                <td class="pe-3 py-3 text-end fw-semibold text-dark">
                                    <?php
                                    try {
                                        echo number_format(($item->qty * $item->price), 2);
                                    } catch (\Throwable $th) {
                                        // throw $th;
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="">
                            <td colspan="3" class="text-end py-3 text-dark">รวมเงิน</td>
                            <td class="pe-3 py-3 text-end text-dark fs-6"><span class="fw-semibold"><?= number_format($model->purchase->calculateVAT()['priceBeforeDiscount'], 2); ?></span>
                            </td>
                        </tr>
                        <tr class="">
                            <td colspan="3" class="text-end py-3 text-dark">ส่วนลดสินค้า(เป็นเงิน)</td>
                            <td class="pe-3 py-3 text-end text-dark fs-6"><?= ($model->purchase->discount_price == '' ?  '0.00' : number_format($model->purchase->discount_price, 2)); ?>
                            </td>
                        </tr>
                        <tr class="">
                            <td colspan="3" class="text-end py-3 text-dark">เงินหลังหักส่วนลด</td>
                            <td class="pe-3 py-3 text-end text-dark fs-6"><?= number_format($model->purchase->calculateVAT()['priceAfterDiscount'], 2) ?>
                            </td>
                        </tr>
                        <tr class="">
                            <td colspan="3" class="text-end py-3 text-dark">ภาษีมูลค่าเพิ่ม 7% (<code><?= $model->purchase->vatName() ?></code>)</td>
                            <td class="pe-3 py-3 text-end text-dark fs-6"><?=number_format($model->purchase->calculateVAT()['vatAmount'], 2); ?>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="table-light">


                        <tr class="">
                            <td colspan="3" class="text-end py-3 text-dark">ยอดรวมทั้งสิ้น</td>
                            <td class="pe-3 py-3 text-end text-dark fs-6"><span class="h6"><?= number_format($model->purchase->calculateVAT()['priceAfterVAT'], 2) ?></span></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
        <div class="col-lg-5">
            <div class="d-flex flex-column gap-3 h-100">
                <div class="card border-0 shadow-sm rounded-4 bg-primary text-white overflow-hidden position-relative">
                    <div class="position-absolute top-0 end-0 opacity-10 p-2"><svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calculator" aria-hidden="true">
                            <rect width="16" height="20" x="4" y="2" rx="2"></rect>
                            <line x1="8" x2="16" y1="6" y2="6"></line>
                            <line x1="16" x2="16" y1="14" y2="18"></line>
                            <path d="M16 10h.01"></path>
                            <path d="M12 10h.01"></path>
                            <path d="M8 10h.01"></path>
                            <path d="M12 14h.01"></path>
                            <path d="M8 14h.01"></path>
                            <path d="M12 18h.01"></path>
                            <path d="M8 18h.01"></path>
                        </svg></div>
                    <div class="card-body p-4 position-relative z-1"><p class="opacity-75 d-block mb-1 text-white">งบประมาณที่ใช้ในรายการนี้</p>
                        <h2 class="display-6 text-white mb-0"><?= number_format($model->purchase->calculateVAT()['priceAfterVAT'], 2) ?></h2>
                        <div class="mt-3 pt-3 border-top border-white border-opacity-25">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="opacity-75  text-white">ประเภทการจัดซื้อ</span>
                            <span class="fw-bold text-white"><?= $model->purchase->viewRequestType() ?></span></div>
                        </div>
                    </div>
                </div>
                <div class="card border-0 shadow-sm rounded-4 flex-grow-1">
                    <div class="card-body p-4">
                        <?php echo $this->render('../approve/level_approve_v2', ['model' => $model->purchase, 'name' => 'purchase']) ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- <div class="modal-footer border-top bg-white py-3 px-4"><button class="btn btn-light text-secondary border-0 fw-medium px-4 rounded-pill">ปิดหน้าต่าง</button>
        <div class="ms-auto d-flex gap-2"><button class="btn btn-outline-danger fw-medium px-4 rounded-pill border-2">ส่งกลับแก้ไข</button><button class="btn btn-primary fw-medium px-4 rounded-pill shadow-sm d-flex align-items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-check" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="m9 12 2 2 4-4"></path>
                </svg>อนุมัติรายการ</button>
        </div>
    </div> -->