<?php
use yii\helpers\Html;
use app\modules\hr\models\Organization;
use app\models\Categorise;
?>
<div class="branch">
                <?php foreach( Categorise::find()->where(['name' => 'organization','id' => $model->id])->all() as $key => $item):?>
                <div class="entry">
                    <span><?php echo $this->render('./card',['model' => $item,'level' => ($model->level+1)])?></span>
                </div>
                <?php endforeach;?>
            </div>