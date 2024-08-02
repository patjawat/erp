<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;

// นำเข้า model ต่างๆ
use app\modules\hr\models\Organization;
use app\models\Categorise;
use app\models\Province;


// ใช้งานเกี่ยวกับทรัพย์สิน
class AssetHelper extends Component
{

    public static function ListAssetType()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'asset_type'])->all(),'code','title');
    }

}
