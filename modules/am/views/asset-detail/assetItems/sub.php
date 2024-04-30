<?php
  use softark\duallistbox\DualListbox;
  use yii\widgets\ActiveForm;
  use app\modules\am\models\Asset;
  use yii\helpers\ArrayHelper;
  use yii\helpers\Url;
use app\modules\am\models\AssetDetail;
// $model = AssetDetail::findOne([]);
// $code = Yii::$app->request->get('code');
// $model = AssetDetail::findOne(['code' => $code]);
$item = Asset::find()->all();

$model = new AssetDetail;

$items = ArrayHelper::map($item,'code','code');
?>
<?php
    $options = [
        'multiple' => true,
        'size' => 20,
    ];
    // echo Html::activeListBox($model, $attribute, $items, $options);
    echo DualListbox::widget([
        'model' => $model,
        'attribute' => 'data_json',
        'items' => $items,
        'options' => $options,
        'clientOptions' => [
            'moveOnSelect' => false,
            'selectedListLabel' => 'Selected Items',
            'nonSelectedListLabel' => 'Available Items',
        ],
    ]);
?>
