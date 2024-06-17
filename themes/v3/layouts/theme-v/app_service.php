<?php
use app\components\UserHelper;
use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="d-none d-lg-inline-flex ms-2 dropdown" data-aos="zoom-in" data-aos-delay="100">
                <button data-bs-toggle="dropdown" aria-haspopup="true" type="button" id="page-header-app-dropdown"
                    aria-expanded="false" class="btn header-item notify-icon">
                    <i class="bx bx-customize"></i>
                </button>
                <div aria-labelledby="page-header-app-dropdown"
                    class="dropdown-menu-lg dropdown-menu-right dropdown-menu" style="width: 600px;">
                    <div class="px-lg-2">

                        <h6 class="text-center mt-3"><i class="fa-solid fa-grip"></i> บริการ</h6>

                        <!-- App Service -->
                        <div class="row p-3">
                        <div class="col-4 mt-1">
                                <a href="<?= Url::to(['/helpdesk/default/repair-select', 'title' => '<i class="fa-regular fa-circle-check"></i> เลือกประเภทการซ่อม']); ?>"
                                    class="open-modal" data-title="xxx">
                                    <div
                                        class="d-flex flex-column align-items-center justify-content-center bg-light p-3 rounded-2">
                                        <i class="fa-solid fa-triangle-exclamation fs-3"></i>
                                        <div>แจ้งซ่อม</div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-4 mt-1">
                                <a href="<?= Url::to(['/purchase/pr-order/create', 'name' => 'order', 'title' => '<i class="bi bi-plus-circle"></i> เพิ่มใบขอซื้อ-ขอจ้าง']); ?>"
                                class="open-modal" data-title="xxx">
                                    <div
                                        class="d-flex flex-column align-items-center justify-content-center bg-light p-3 rounded-2">
                                        <i class="fa-solid fa-bag-shopping fs-3"></i>
                                        <div>ข้อซื้อ-ขอจ้าง</div>
                                    </div>
                                </a>
                            </div>

                            <!-- <div class="col-4">
                                <div
                                    class="d-flex flex-column align-items-center justify-content-center bg-light p-3 rounded-2">
                                    <?= html::img('@web/images/svg-icons/leave.svg', ['width' => '50px']) ?>
                                    <div>ระบบลา</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div
                                    class="d-flex flex-column align-items-center justify-content-center bg-light p-3 rounded-2">
                                    <?= html::img('@web/images/svg-icons/booking.svg', ['width' => '50px']) ?>
                                    <div>ระบบจองรถ</div>
                                </div>
                            </div>

                            <div class="col-4">
                                <div
                                    class="d-flex flex-column align-items-center justify-content-center bg-light p-3 rounded-2">
                                    <?= html::img('@web/images/svg-icons/meeting.svg', ['width' => '50px']) ?>
                                    <div>ระบบจองห้องประชุม</div>
                                </div>
                            </div>

                            <div class="col-4 mt-3">
                                <div
                                    class="d-flex flex-column align-items-center justify-content-center bg-light p-3 rounded-2">
                                    <?= html::img('@web/images/svg-icons/document.svg', ['width' => '50px']) ?>
                                    <div>ระบบสารบัญ</div>
                                </div>
                            </div>
                            <div class="col-4 mt-3">
                                <div
                                    class="d-flex flex-column align-items-center justify-content-center bg-light p-3 rounded-2">
                                    <?= html::img('@web/images/svg-icons/check-list.svg', ['width' => '50px']) ?>
                                    <div>ระบบความเสี่ยง</div>
                                </div>
                            </div> -->
                           

                        </div>

                    </div>
                </div>
            </div>