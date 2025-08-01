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

$this->title = 'แบบฟอร์มใบลา';
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="card"><?php $this->beginBlock('page-title'); ?>
    <i class="fa-solid fa-calendar-day fs-1"></i> <?= $this->title; ?>
    <?php $this->endBlock(); ?>
    <?php $this->beginBlock('sub-title'); ?>
    <?php $this->endBlock(); ?>

    <?php $this->beginBlock('page-action'); ?>
    <?php echo $this->render('@app/modules/hr/views/leave/menu') ?>
    <?php $this->endBlock(); ?>

    <?php $this->beginBlock('navbar_menu'); ?>
    <?=$this->render('@app/modules/hr/views/leave/menu',['active' => 'index'])?>
    <?php $this->endBlock(); ?>

    <div class="d-flex">
            <canvas id="pdfCanvas" width="794" height="1123" style="border:1px solid #ccc"></canvas>
            <div class="ms-5 mt-5">
                <div class="conteiner">

                
                 <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['create','title' => '<i class="fa-solid fa-circle-plus"></i> สร้างใหม่'], ['class' => 'btn btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-md']]) ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th class="text-center" scope="col" style="width: 5%">#ลำดับ</th>
                            <th scope="col" style="width: 15%">รหัส</th>
                            <th scope="col" style="width: 70%">ชื่อรายการ</th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider align-middle">
                        <?php foreach($dataProvider->getModels() as $key => $item):?>
                            <tr>
                                <td class="text-center fw-semibold"><?php echo (($dataProvider->pagination->offset + 1)+$key)?>
                            </td>
                            <td class="fw-semibold text-primary"><?=$item->code?></td>
                            <td><?=$item->title?></td>
                            
                        </tr>
                        <?php endforeach;?>
                    </tbody>
                    
                </table>
                </div>
            </div>
                

    </div>

    <br>
    <button id="addName">เพิ่มชื่อ</button>
    <button id="addDate">เพิ่มวันที่</button>
    <button id="exportLayout">บันทึก Layout</button>
    <pre id="output"></pre>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js"></script>
    <script>
    const canvas = new fabric.Canvas('pdfCanvas');

    // Load background (รูปแบบฟอร์มราชการ)
    fabric.Image.fromURL('<?= Yii::getAlias("@web/document-template/leave.png") ?>', function(img) {
        img.scaleToWidth(canvas.width);
        img.selectable = false;
        canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas));
    });

    // เพิ่ม field ตัวอย่าง
    document.getElementById('addName').onclick = function() {
        const text = new fabric.Text('{{full_name}}', {
            left: 100,
            top: 100,
            fontSize: 18,
            fill: 'black'
        });
        canvas.add(text);
    };

    document.getElementById('addDate').onclick = function() {
        const text = new fabric.Text('{{date}}', {
            left: 100,
            top: 150,
            fontSize: 18,
            fill: 'black'
        });
        canvas.add(text);
    };

    // Export Layout
    document.getElementById('exportLayout').onclick = function() {
        const objects = canvas.getObjects('text');
        const layout = objects.map(obj => ({
            field: obj.text.replace(/[{}]/g, ''),
            x: obj.left,
            y: obj.top,
            fontSize: obj.fontSize
        }));
        document.getElementById('output').innerText = JSON.stringify(layout, null, 2);

        // ส่งไป backend (ถ้าต้องการ)
        /*
        fetch('/document/save-layout?id=123', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(layout)
        });
        */
    };
    </script>




    <div class="card-body">
        <div class="d-flex justify-content-between align-item-center">
            <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['create','title' => '<i class="fa-solid fa-circle-plus"></i> สร้างใหม่'], ['class' => 'btn btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-lg']]) ?>

        </div>
    </div>
</div>