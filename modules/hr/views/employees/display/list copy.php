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
                    <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
                        <th class="fw-semibold" scope="col" style="width:100px">ชื่อ-นามสกุล</th>
                        <th class="fw-semibold" scope="col">ประเภท</th>
                        <th class="fw-semibold" scope="col" class="text-center" style="width: 280px;">สถานะ | เริ่มงาน</th>
                        <th class="fw-semibold" scope="col">อายุราชการ</th>
                        <th class="fw-semibold" scope="col">เหลืออีก | สิ้นสุดสัญญาจ้าง | เกษียรอายุ</th>
                    </tr>
                </thead>
                <tbody class="align-middle table-group-divider">
                <?php foreach($dataProvider->getModels() as $key => $item):?>
        <tr>
            <td class="text-center fw-semibold"><?php echo (($dataProvider->pagination->offset + 1)+$key)?></td>
                        <td class="text-truncate"><?= $item->getAvatar(false) ?></td>
                        <td><?=$item->positionType->title?></td>
                        <td class="align-middle">
                            <div class="d-flex flex-column">
                                <span>
                                    <label class="badge rounded-pill text-primary-emphasis bg-success-subtle me-1"><i
                                            class="bi bi-clipboard-check"></i> <?= $item->statusName() ?></label>
                                    | <i class="bi bi-calendar-check-fill"></i>
                                    <?= Yii::$app->thaiFormatter->asDate($item->join_date, 'medium') ?>
                                </span>

                            </div>
                        </td>
                        <td class="align-middle">
                            <div class="d-flex flex-column">
                                <span>
                                    <!-- <i class="bi bi-clock text-primary"></i> 1 ปี 2 เดือน 3 วัน -->
                                    <i class="bi bi-clock text-primary"></i> <?= $item->age_join_date['full'] ?>
                                </span>
                            </div>

                        </td>
                        <td class="align-middle">
                            <!-- กำหนดวันหมดอายุ -->
                            <div class="d-flex justify-content-between">
                                <div>
                                    <?= AppHelper::CountDown($item->Retire()['date']) ?>
                                </div>
                                <div>
                                    <i class="bi bi-calendar2-event"></i> <?= $item->Retire()['date']; ?>
                                </div>

                            </div>
                            <div class="progress progress-sm mt-3 w-100">
                                <div class="progress-bar bg-<?= $item->Retire()['color'] ?>" role="progressbar"
                                    <?= "style='width:" . $item->Retire()['progress'] . "%;'" ?> aria-valuenow="65"
                                    aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <!-- จบการกำหนดวันหมดอายุ -->
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
