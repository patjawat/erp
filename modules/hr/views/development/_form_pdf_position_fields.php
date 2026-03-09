<?php
/** @var app\models\Categorise $model */
/** @var kartik\form\ActiveForm $form */
?>
            <!-- Field Item: ส่วนราชการ -->
            <div class="card border rounded-3 mb-2 field-card" data-field="company_name" data-title="ชื่อส่วนราชการ">
                <div class="card-body p-2">
                    <div class="small fw-semibold mb-2 text-primary">ส่วนราชการ</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[company_name_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-x'])->label(false) ?>
                        <?= $form->field($model, 'data_json[company_name_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <!-- Field Item: ที่ -->
            <div class="card border rounded-3 mb-2 field-card" data-field="doc_number" data-title="(ที่)">
                <div class="card-body p-2">
                    <div class="small fw-semibold mb-2 text-primary">ที่(1234/1)</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[doc_number_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[doc_number_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <!-- Field Item: วันที่ -->
            <div class="card border rounded-3 mb-2 field-card" data-field="doc_date" data-title="วันที่หนังสือ">
                <div class="card-body p-2">
                    <div class="small fw-semibold mb-2 text-primary">วันที่</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[doc_date_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[doc_date_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="card border rounded-3 mb-2 field-card" data-field="fullname" data-title="(ด้วยข้าพเจ้า)">
                <div class="card-body p-2">
                    <div class="small fw-semibold mb-2 text-primary">ด้วยข้าพเจ้า</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[fullname_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[fullname_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="card border rounded-3 mb-2 field-card" data-field="fullname_signature" data-title="ชื่อ-นามสกุล(ผู้ขออนุญาติ)">
                <div class="card-body p-2">
                    <div class="small fw-semibold mb-2 text-primary">ผู้ขออนุญาติ</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[fullname_signature_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[fullname_signature_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>
            <div class="card border rounded-3 mb-2 field-card" data-field="position_signature" data-title="ตำแหน่ง(ผู้ขออนุญาติ)">
                <div class="card-body p-2">
                    <div class="small fw-semibold mb-2 text-primary">ตำแหน่งผู้ขอ</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[position_signature_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[position_signature_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>
            <div class="card border rounded-3 mb-2 field-card" data-field="fullname_signature_img" data-title="ลายเซ็นต์(ผู้ขออนุญาติ)">
                <div class="card-body p-2">
                    <div class="small fw-semibold mb-2 text-primary">ลายเซ็นต์(ผู้ขออนุญาติ)</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[fullname_signature_img_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[fullname_signature_img_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="card border rounded-3 mb-2 field-card" data-field="position" data-title="ตำแหน่ง(ด้วยข้าเจ้า)">
                <div class="card-body p-2">
                    <div class="small fw-semibold mb-2 text-primary">ด้วยข้าพเจ้า</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[position_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[position_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>



            <div class="card border rounded-3 mb-2 field-card" data-field="topic" data-title="(วัตถุประสงค์)">
                <div class="card-body p-2">
                    <div class="small fw-semibold mb-2 text-primary">วัตถุประสงค์</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[topic_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[topic_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="card border rounded-3 mb-2 field-card" data-field="location" data-title="(สถานที่ไป)">
                <div class="card-body p-2">
                    <div class="small fw-semibold mb-2 text-primary">สถานที่ไป</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[location_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[location_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="card border rounded-3 mb-2 field-card" data-field="date_start" data-title="(ในวันที่)">
                <div class="card-body p-2">
                    <div class="small fw-semibold mb-2 text-primary">วันที่ไป</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[date_start_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[date_start_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>
            <div class="card border rounded-3 mb-2 field-card" data-field="date_end" data-title="(ถึงวันที่)">
                <div class="card-body p-2">
                    <div class="small fw-semibold mb-2 text-primary">ถึงวันที่</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[date_end_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[date_end_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="card border rounded-3 mb-2 field-card" data-field="vehicle_date_start" data-title="(วันออกเดินทาง)">
                <div class="card-body p-2">
                    <div class="small fw-semibold mb-2 text-primary">วันออกเดินทาง</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[vehicle_date_start_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[vehicle_date_start_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="card border rounded-3 mb-2 field-card" data-field="vehicle_time_start" data-title="80:00">
                <div class="card-body p-2">
                    <div class="small fw-semibold mb-2 text-primary">เวลาออกเดินทาง</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[vehicle_time_start_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[vehicle_time_start_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>


            <div class="card border rounded-3 mb-2 field-card" data-field="vehicle_date_end" data-title="(วันกลับ)">
                <div class="card-body p-2">
                    <div class="small fw-semibold mb-2 text-primary">วันกลับ</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[vehicle_date_end_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[vehicle_date_end_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="card border rounded-3 mb-2 field-card" data-field="vehicle_time_end" data-title="16:00">
                <div class="card-body p-2">
                    <div class="small fw-semibold mb-2 text-primary">เวลากลับ</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[vehicle_time_end_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[vehicle_time_end_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="card border rounded-3 mb-2 field-card" data-field="claim_type_name" data-title="เบิกค่าใช้จ่ายจาก">
                <div class="card-body p-2">
                    <div class="small fw-semibold mb-2 text-primary">การเบิกเงิน</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[claim_type_name_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[claim_type_name_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>


            <div class="card border rounded-3 mb-2 field-card" data-field="total_days" data-title="1">
                <div class="card-body p-2">
                    <div class="small fw-semibold mb-2 text-primary">จำนวนวัน</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[total_days_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[total_days_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>



            <div class="card border rounded-3 mb-2 field-card" data-field="vehicle_type" data-title="เดินทางไปราชการโดย">
                <div class="card-body p-2">
                    <div class="small fw-semibold mb-2 text-primary">พาหนะเดินทาง</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[vehicle_type_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[vehicle_type_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="card border rounded-3 mb-2 field-card" data-field="assigned_to" data-title="(ชื่อ ผู้ปฏิบัติหน้าที่แทน)">
                <div class="card-body p-2">
                    <div class="small fw-semibold mb-2 text-primary">ผู้ปฏิบัติหน้าที่แทน</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[assigned_to_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[assigned_to_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>
            <div class="card border rounded-3 mb-2 field-card" data-field="assigned_to_position" data-title="ตำแหน่ง ผู้ปฏิบัติหน้าที่แทน">
                <div class="card-body p-2">
                    <div class="small fw-semibold mb-2 text-primary">ตำแหน่งผู้ปฏิบัติหน้าที่แทน</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[assigned_to_position_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[assigned_to_position_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>


            <div class="card border rounded-3 mb-2 field-card" data-field="assigned_to_signature" data-title="ชื่อผู้ปฏิบัติหน้าที่แทน(เซ็นต์)">
                <div class="card-body p-2">
                    <div class="small fw-semibold mb-2 text-primary">ลงชื่อผู้ปฏิบัติหน้าที่แทน</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[assigned_to_signature_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[assigned_to_signature_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>
            <div class="card border rounded-3 mb-2 field-card" data-field="assigned_to_signature_img" data-title="ชื่อผู้ปฏิบัติหน้าที่แทน(ลายเซ็นต์)">
                <div class="card-body p-2">
                    <div class="small fw-semibold mb-2 text-primary">ลายเซ็นต์ผู้ปฏิบัติหน้าที่แทน</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[assigned_to_signature_img_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[assigned_to_signature_img_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>
            <!-- <div class="card border rounded-3 mb-2 field-card" data-field="assigned_to_position_signature" data-title="ชื่อตำแหน่งผู้ปฏิบัติหน้าที่แทน(เซ็นต์)">
                <div class="card-body p-2">
                    <div class="small fw-semibold mb-2 text-primary">ลงชื่อตำแหน่งผู้ปฏิบัติหน้าที่แทน</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[assigned_to_position_signature_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[assigned_to_position_signature_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-y'])->label(false) ?>
                    </div>
                </div>
            </div> -->

            <div class="card border rounded-3 mb-2 field-card" data-field="member_fullname_start" data-title="ชื่อ-นามสกุล(คณะเดินทาง)">
                <div class="card-body p-2">
                    <div class="small fw-semibold mb-2 text-primary">ชื่อคณะเดินทาง</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[member_fullname_start_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[member_fullname_start_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="card border rounded-3 mb-2 field-card" data-field="member_position_start" data-title="ตำแหน่งคณะ(เดินทาง)">
                <div class="card-body p-2">
                    <div class="small fw-semibold mb-2 text-primary">ตำแหน่งคณะเดินทาง</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[member_position_start_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[member_position_start_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="card border rounded-3 mb-2 field-card" data-field="approve_date" data-title="วันที่ ผอ.อนุมัติ">
                <div class="card-body p-2">
                    <div class="small fw-semibold mb-2 text-primary">วันอนุมัติ</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[approve_date_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[approve_date_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="card border rounded-3 mb-2 field-card" data-field="leader_fullname" data-title="(หัวหน้ากลุ่มมงาน)">
                <div class="card-body p-2">
                    <div class="small fw-semibold mb-2 text-primary">ชื่อหัวหน้ากลุ่มงาน</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[leader_fullname_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[leader_fullname_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="card border rounded-3 mb-2 field-card" data-field="leader_date" data-title="(วันที่หัวหน้ากลุ่มมงาน อนุมัติ)">
                <div class="card-body p-2">
                    <div class="small fw-semibold mb-2 text-primary">วันที่หัวหน้าอนุมัติ</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[leader_date_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[leader_date_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>

            <div class="card border rounded-3 mb-2 field-card" data-field="director_signature_img" data-title="(ลายเซ็นต์ ผอ.)">
                <div class="card-body p-2">
                    <div class="small fw-semibold mb-2 text-primary">ลายเซ็นต์ ผอ.</div>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <?= $form->field($model, 'data_json[director_signature_img_x]', [
                            'addon' => ['prepend' => ['content' => 'X']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-x'])->label(false) ?>

                        <?= $form->field($model, 'data_json[director_signature_img_y]', [
                            'addon' => ['prepend' => ['content' => 'Y']]
                        ])->textInput(['type' => 'number', 'class' => 'form-control coord-y'])->label(false) ?>
                    </div>
                </div>
            </div>
