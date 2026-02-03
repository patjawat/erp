<?php

use app\models\Categorise;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\CategoriseSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Categorises';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="categorise-index">
   

    <?php Pjax::begin(['id' => 'chronic-diseases']); ?>
    <div class="row">
        <div class="col-10">
            <?php echo $this->render('_search', ['model' => $searchModel]); ?>
        </div>
        <div class="col-2">
        <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['create', 'title' => '<i class="fa-solid fa-circle-plus me-1"></i> เพิ่มโรคประจำตัว'], ['class' => 'btn btn-success open-modal', 'data' => ['size' => 'modal-md']]) ?>
    </div>
    </div>
    <div
        class="table-responsive" style="min-height: 300px;">
        <table
            class="table">
            <thead>
                <tr>
                    <th scope="col">ชื่อโรค</th>
                    <th scope="col" style="width: 100px;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dataProvider->getModels() as $item): ?>
                    <tr class="">
                        <td><?= $item->title?></td>
                        <td class="text-center align-middle">
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                            data-bs-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                        <div class="dropdown-menu">
                                            <? ?>
                                            <?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i>แก้ไข', ['/hr/chronic-diseases/update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-user-tag"></i> การศึกษา'], ['class' => 'dropdown-item open-modal-xx', 'data' => ['size' => 'modal-md', 'pjax' => '0']]) ?>

                                            <?= Html::a('<i class="fa-solid fa-trash me-1"></i>ลบ', ['/hr/chronic-diseases/delete', 'id' => $item->id], [
                                                'class' => 'dropdown-item delete-item',
                                            ]) ?>
                                        </div>
                                    </div>
                                </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>




</div>
<?php
$js = <<< JS


    \$("body").on("click", ".delete-item", function (e) {
        e.preventDefault();
        \$.ajax({
            type: "get",
            url: \$(this).attr('href'),
            dataType: "json",
            success: function (res) {
                 window.location.reload(true);
            }
        });
    });

JS;
$this->registerJS($js);
?>
    <?php Pjax::end(); ?>