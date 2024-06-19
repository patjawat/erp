<?php
use yii\helpers\Html;
?>
<!-- กรรมการตรวจรับ -->

<div class="d-flex justify-content-between align-items-center">
    <h5><i class="fa-solid fa-circle-info text-primary"></i> กรรมมการตรวจรับ</h5>

    <?= Html::a('<i class="fa-solid fa-circle-plus me-1"></i> เพิ่ม', ['/purchase/pq-order/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-sm btn-light open-modal', 'data' => ['size' => 'modal-xl']]) ?>

</div>

<table class="table table-primary">
    <thead>
        <tr>
            <th scope="col">คณะกรรมการ</th>
            <th scope="col">กำแหน่ง</th>
            <th scope="col" style="width: 100px;">ดำเนินการ</th>
        </tr>
    </thead>
    <tbody>
        <tr class="">
            <td scope="row">
                <div class="d-flex"><img class="avatar avatar-sm bg-primary text-white" src="/img/placeholder_cid.png"
                        alt="">
                    <div class="avatar-detail">
                        <h6 class="mb-1 fs-15" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-custom-class="custom-tooltip" data-bs-title="ดูเพิ่มเติม..."><a class=""
                                href="/hr/employees/view?id=1">Administrator Lastname</a>
                        </h6>
                        <p class="text-muted mb-0 fs-13"><i
                                class="fa-solid fa-circle-exclamation text-danger me-1"></i>ไม่ระบุตำแหน่ง
                            <code>(-)</code>
                        </p>

                    </div>
                </div>
            </td>
            <td>กรรมการ</td>
            <td>
                <a name="" id="" class="btn btn-sm btn-warning" href="#" role="button"><i class="fa-regular fa-pen-to-square"></i></a>
                <a name="" id="" class="btn btn-sm btn-danger" href="#" role="button"><i class="fa-solid fa-trash"></i></a>
            </td>
        </tr>
        <tr class="">
            <td scope="row">
                <div class="d-flex"><img class="avatar avatar-sm bg-primary text-white" src="/img/placeholder_cid.png"
                        alt="">
                    <div class="avatar-detail">
                        <h6 class="mb-1 fs-15" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-custom-class="custom-tooltip" data-bs-title="ดูเพิ่มเติม..."><a class=""
                                href="/hr/employees/view?id=1">Administrator Lastname</a>
                        </h6>
                        <p class="text-muted mb-0 fs-13"><i
                                class="fa-solid fa-circle-exclamation text-danger me-1"></i>ไม่ระบุตำแหน่ง
                            <code>(-)</code>
                        </p>

                    </div>
                </div>
            </td>
            <td>กรรมการ</td>
            <td>
                <a name="" id="" class="btn btn-sm btn-warning" href="#" role="button"><i class="fa-regular fa-pen-to-square"></i></a>
                <a name="" id="" class="btn btn-sm btn-danger" href="#" role="button"><i class="fa-solid fa-trash"></i></a>
            </td>
        </tr>
    </tbody>
</table>