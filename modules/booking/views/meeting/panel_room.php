<?php
use yii\helpers\Html;
use app\modules\booking\models\Room;
$listRoom = Room::find()->where(['name' => 'meeting_room'])->all();
?>
<div class="card">
            <div class="card-header  bg-primary-gradient">
                <div class="d-flex justify-content-between align-items-center align-self-center">
                    <h5 class="mb-0 text-white"><i class="fa-solid fa-book"></i> รายการห้องประชุม</h5>
                    <?= html::a('<i class="fa-solid fa-gear"></i>', ['/booking/vehicle-status/index'], ['class' => 'btn btn-sm btn-light open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-2 mb-3">
                    <?php foreach ($listRoom as $item): ?>
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="status-indicator <?= $item->code ?>" style="background-color:<?= $item->data_json['color'] ?? 'var(--bs-primary)' ?>"></span><?= $item->title ?>
                            </div>
                            <div>
                                <span class="badge text-bg-light status_summary" id="status<?= $item->code ?>">0</span>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>