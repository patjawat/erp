<?php
use yii\helpers\Html;
?>
<div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <p><i class="fa-solid fa-circle-info"></i> อันดับวัสดุจัดซื้อมากที่สุด</p>
                    <div class="dropdown float-end">
                        <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-ellipsis"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <?= Html::a('<i class="bi bi-gear me-2"></i>ตั้งค่า', ['/sm/product'], ['class' => 'dropdown-item']) ?>
                        </div>
                    </div>
                </div>
                <div class="testimonial-group overflow-auto">
                    <div class="row flex-nowrap">
                        <?php for ($i = 0; $i < 12; $i++): ?>
                        <div class="col-3">
                            <div class="card">
                                <!-- <img class="card-img-top"
                                    src="https://angular.spruko.com/vexel/preview/assets/images/shop/1.png"
                                    alt="Title" /> -->
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="d-flex flex-column">
                                            <span>เก้าอี้นั่ง</span>
                                            <span class="text-light">วัสดุสำนักงาน</span>
                                        </div>
                                        <span class="badge text-bg-primary ">วัสดุสำนักงาน</span>
                                    </div>
                                    <span>คงเหลือ 10 ชิ้น</span>
                                </div>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>

                </div>



            </div>
        </div>