<?php
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\AppHelper;
use yii\bootstrap5\LinkPager;
use yii\bootstrap5\Breadcrumbs;

$this->title = 'ทะเบียนประวัติ';
$this->params['breadcrumbs'][] = $this->title;
?>

            <table class="table table-striped">
                <thead>
                    <tr>
                    <th class="text-center" style="width:30px">ลำดับ</th>
                        <th scope="col" style="width:100px">ชื่อ-นามสกุล</th>
                        <th scope="col">ประเภท</th>
                        <th scope="col">แผนก/ฝ่าย</th>
                        <th scope="col" class="text-center">เริ่มงาน</th>
                        <th scope="col" class="text-center">ประเภทงาน</th>
                        <th scope="col">อายุราชการ</th>
                        <th scope="col">สถานะ</th>
                        <th scope="col" style="width:250px">เหลืออีก | สิ้นสุดสัญญาจ้าง</th>
                        <th class="fw-semibold text-center" scope="col" style="width:100px">ดำเนินการ</th>

                    </tr>
                </thead>
                <tbody class="align-middle table-group-divider">
                <?php foreach($dataProvider->getModels() as $key => $item):?>
        <tr>
            <td class="text-center"><?php echo (($dataProvider->pagination->offset + 1)+$key)?></td>
                        <td class="text-truncate">
                            <?= Html::a($item->getAvatar(false),['/hr/employees/view','id' => $item->id]) ?></td>
                        <td><?=$item->positionType?->title ?? 'ไม่ระบุ'?></td>
                        <td class="text-truncate"><?= $item->departmentName() ?></td>
                        <td class="align-middle">
                             <?php
                                    try {
                                        echo Yii::$app->thaiFormatter->asDate($item->joinDate(), 'medium');
                                    } catch (\Throwable $th) {
                                    }
                                    ?>
                        </td>
                        <td class="text-center align-middle"><?=$item->viewWorkType()?></td>
                        <td class="align-middle">
                             <?= $item->age_join_date['full'] ?>                   
                        </td>
                        <td class="align-middle">
                           <label class="badge rounded-pill text-primary-emphasis bg-success-subtle me-1"><i
                                            class="bi bi-clipboard-check"></i> <?= $item->statusName() ?></label>               
                        </td>
                        
                        <td class="align-middle">
                            <!-- กำหนดวันหมดอายุ -->
                            <div class="d-flex justify-content-between">
                                <div>
                                    <?= AppHelper::CountDown($item->Retire()['date']) ?>
                                </div>

                            </div>
                            <div class="progress progress-sm mt-3 w-100">
                                <div class="progress-bar bg-<?= $item->Retire()['color'] ?>" role="progressbar"
                                    <?= "style='width:" . $item->Retire()['progress'] . "%;'" ?> aria-valuenow="65"
                                    aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <!-- จบการกำหนดวันหมดอายุ -->
                        </td>
                        <td class="text-end">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                            id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                            จัดการ
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                            <li><?= Html::a('<i class="fa-solid fa-pen-to-square me-1"></i>แก้ไข', ['update', 'id' => $item->id], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?></li>
                        </ul>
                    </div>
                </td>
                    </tr>
                    
                    <?php endforeach; ?>
                </tbody>
            </table>
