<?php 
use yii\helpers\Html;
?>
<div
    class="table-responsive"
>
    <table
        class="table table-primary"
    >
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">รายการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($models as $model):?>
            <tr>
                <td>
                <div class="d-flex flex-column">
                    <div>
                        <?=Html::a($model['asset_title'],['/am/setting/view-item','id' => $model['asset_item_id']],['class' =>  'open-modal', 'data' => ['size' => 'modal-lg']])?>
                    </div>
                </div>    
            </td>
            </tr>
            <?php endforeach;?>
         
        </tbody>
    </table>
</div>
