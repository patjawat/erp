<?php
use yii\bootstrap5\Html;

?>
<h3 class="card-title">ครอบครัว
                            <?=Html::a('<i class="fas fa-pencil-alt"></i>',['/employees/family/create','id' => $model->id],['class' => 'edit-icon open-modal','data' => [
                                                    // 'size' => 'modal-xl',         
                                                    ],]);?>
                            <!-- <a href="#" class="edit-icon" data-bs-toggle="modal"
                                data-bs-target="#emergency_contact_modal"><i class="fas fa-pencil-alt"></i></a> -->
                        </h3>

                        <div class="">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">ความสัมพันธ์</th>
                                        <th scope="col">บุคคล</th>
                                        <th scope="col">วันเกิด</th>
                                        <th scope="col">เลขที่บัตรประชาชน</th>
                                        <th scope="col">เอกสารอ้างอิง</th>
                                        <th scope="col">หมายเหตุ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="">
                                        <td scope="row">R1C1</td>
                                        <td>R1C2</td>
                                        <td>R1C3</td>
                                        <td>R1C3</td>
                                        <td>R1C3</td>
                                        <td>R1C3</td>
                                    </tr>
                                    <tr class="">
                                        <td scope="row">Item</td>
                                        <td>Item</td>
                                        <td>Item</td>
                                        <td>Item</td>
                                        <td>Item</td>
                                        <td>Item</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>