<?php
use app\modules\am\models\AssetDetail;
use app\modules\helpdesk\models\Helpdesk;
use yii\helpers\Html;

$repair = Helpdesk::findOne(['code' => $model->code]);
$modelCar = AssetDetail::find()->where(['name' => 'tax_car', 'code' => $model->code])->orderBy(['date_start' => SORT_DESC])->one();
?>
    <?= $this->render('general_detail', ['model' => $model]) ?>
