<?php
use yii\helpers\Html;
?>
<div class="row">
            <div class="col-12">

                <div class="card rounded-4">
                   
                    <div class="card-body">
                        <div class="d-flex justify-content-between">

                            <div class="card-title">บุคลากรทั้งหมด</div>
                            <?=Html::a('แสดงทั้งหมด',['/hr/employees'],['class' => 'btn btn-sm btn-light'])?>
                        </div>
                   
                        <div class="table-responsive">
                            <table class="table text-nowrap">
                                <thead>
                                    <tr>
                                        <th scope="col">ชื่อ-นามสกุล</th>
                                        <th scope="col">แผนก/ฝ่าย</th>
                                        <th scope="col">สถานะ</th>
                                        <th class="text-center" style="width: 100px;">ดำเนินการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th scope="row">
                                            <div class="d-flex align-items-center lh-1">
                                                <div class="me-3"> <span class="avatar"> <img
                                                            src="https://spruko.com/demo/ynex/dist/assets/images/faces/2.jpg" class="avatar rounded-circle" alt=""> </span> </div>
                                                <div> <span class="d-block fw-semibold mb-1">นายปัจวัฒน์ ศรีบุญเรือง</span> <span
                                                        class="d-block text-muted fs-12">นักวิชาการคอมพิวเตอร์</span> </div>
                                            </div>
                                        </th>
                                        <td>diana.1116@demo.com</td>
                                        <td>
                                            
                                            <div class="dropdown"> <a href="javascript:void(0);"
                                                    class="btn btn-outline-secondary btn-sm" data-bs-toggle="dropdown"
                                                    aria-expanded="false"> Active<i
                                                        class="ri-arrow-down-s-line align-middle ms-1 d-inline-block"></i>
                                                </a>
                                                <ul class="dropdown-menu" role="menu">
                                                    <li><a class="dropdown-item" href="javascript:void(0);">Active</a>
                                                    </li>
                                                    <li><a class="dropdown-item" href="javascript:void(0);">Inactive</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                        <td class="text-center align-middle">
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown" aria-expanded="false"><i
                                        class="bx bx-dots-vertical-rounded fw-bold"></i></button>
                                <div class="dropdown-menu" style="">
                                    <??>
                                    <?=Html::a('<i class="bx bx-edit-alt me-1"></i>แก้ไข', ['/hr/employee-detail/update', 'id' => 1, 'title' => '<i class="fa-solid fa-user-tag"></i> การศึกษา'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']])?>

                                    <?=Html::a('<i class="bx bx-trash me-1"></i>ลบ', ['/hr/employee-detail/delete', 'id' => 1], [
'class' => 'dropdown-item delete-item',
])?>
                                </div>
                            </div>
                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <div class="d-flex align-items-center lh-1">
                                                <div class="me-3"> <span class="avatar avatar-rounded"> <img
                                                            src="https://spruko.com/demo/ynex/dist/assets/images/faces/8.jpg" class="avatar rounded-circle" alt=""> </span> </div>
                                                <div> <span class="d-block fw-semibold mb-1">นายเดชา สายบุญตั้ง</span> <span
                                                        class="d-block text-muted fs-12">นักจัดการงานทั่วไป</span> </div>
                                            </div>
                                        </th>
                                        <td>rose756@demo.com</td>
                                        <td>
                                            <div class="dropdown"> <a href="javascript:void(0);"
                                                    class="btn btn-outline-secondary btn-sm" data-bs-toggle="dropdown"
                                                    aria-expanded="false"> Active<i
                                                        class="ri-arrow-down-s-line align-middle ms-1 d-inline-block"></i>
                                                </a>
                                                <ul class="dropdown-menu" role="menu">
                                                    <li><a class="dropdown-item" href="javascript:void(0);">Active</a>
                                                    </li>
                                                    <li><a class="dropdown-item" href="javascript:void(0);">Inactive</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                        <td class="text-center align-middle">
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown" aria-expanded="false"><i
                                        class="bx bx-dots-vertical-rounded fw-bold"></i></button>
                                <div class="dropdown-menu" style="">
                                    <??>
                                    <?=Html::a('<i class="bx bx-edit-alt me-1"></i>แก้ไข', ['/hr/employee-detail/update', 'id' => 1, 'title' => '<i class="fa-solid fa-user-tag"></i> การศึกษา'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']])?>

                                    <?=Html::a('<i class="bx bx-trash me-1"></i>ลบ', ['/hr/employee-detail/delete', 'id' => 1], [
'class' => 'dropdown-item delete-item',
])?>
                                </div>
                            </div>
                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <div class="d-flex align-items-center lh-1">
                                                <div class="me-3"> <span class="avatar avatar-rounded"> <img
                                                            src="https://spruko.com/demo/ynex/dist/assets/images/faces/13.jpg" class="avatar rounded-circle" alt=""> </span> </div>
                                                <div> <span class="d-block fw-semibold mb-1">นายจิตสง่า เพชรสุวรรณ</span> <span
                                                        class="d-block text-muted fs-12">นักวิชาการคอมพิวเตอร์</span> </div>
                                            </div>
                                        </th>
                                        <td>gretchen.1.25@demo.com</td>
                                        <td>
                                            <div class="dropdown"> <a href="javascript:void(0);"
                                                    class="btn btn-outline-secondary btn-sm" data-bs-toggle="dropdown"
                                                    aria-expanded="false"> Active<i
                                                        class="ri-arrow-down-s-line align-middle ms-1 d-inline-block"></i>
                                                </a>
                                                <ul class="dropdown-menu" role="menu">
                                                    <li><a class="dropdown-item" href="javascript:void(0);">Active</a>
                                                    </li>
                                                    <li><a class="dropdown-item" href="javascript:void(0);">Inactive</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                        <td class="text-center align-middle">
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown" aria-expanded="false"><i
                                        class="bx bx-dots-vertical-rounded fw-bold"></i></button>
                                <div class="dropdown-menu" style="">
                                    <??>
                                    <?=Html::a('<i class="bx bx-edit-alt me-1"></i>แก้ไข', ['/hr/employee-detail/update', 'id' => 1, 'title' => '<i class="fa-solid fa-user-tag"></i> การศึกษา'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']])?>

                                    <?=Html::a('<i class="bx bx-trash me-1"></i>ลบ', ['/hr/employee-detail/delete', 'id' => 1], [
'class' => 'dropdown-item delete-item',
])?>
                                </div>
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