<?php 
use yii\helpers\Html;
?>
<div class="card">
    <div class="card-body">
        <h4 class="card-title">ที่ยังไม่ระบุประเภท</h4>
    </div>
</div>

<div
    class="table-responsive"
>
    <table
        class="table table-primary"
    >
        <thead>
            <tr>
                <th scope="col">รายการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($models as $model):?>
            <tr>
                <td>
                <div class="d-flex flex-column">
                    <div>
                        <?=Html::a($model['asset_name'],['/am/asset/view','id' => $model['id']],['class' =>  'open-modalx', 'data' => ['size' => 'modal-lg']])?>
                    </div>
                </div>    
            </td>
            </tr>
            <?php endforeach;?>
         
        </tbody>
    </table>
</div>
