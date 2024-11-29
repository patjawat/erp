<?php
use app\models\Categorise;
?>
<div class="table-responsive">
                                <h6>ระเบียบการลา</h6>
                                    <table
                                        class="table table-striped table-hover"
                                    >
                                        <thead>
                                            <tr>
                                                <th scope="col">สิทธิ</th>
                                                <th scope="col">วัน</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $num = 1; foreach(Categorise::find()->where(['name' => 'leave_type'])->all() as $leaveType):?>
                                            <tr class=" <?php echo $model->leave_type_id == $leaveType->code ? 'table-primary' : ''?>">
                                                <td scope="row"><?php echo $num++?>. <?= $leaveType->title ?></td>
                                                <td>ไม่เกิน 30 วันทำการ</td>
                                            </tr>
                                            <?php endforeach;?>
                                           
                                        </tbody>
                                    </table>
                                </div>