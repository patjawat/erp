<?php

namespace app\components;

use Yii;
use yii\base\Component;
use app\models\Province;
// นำเข้า model ต่างๆ
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\modules\hr\models\Organization;

// ใช้งานเกี่ยวกับทรัพย์สิน
class AssetHelper extends Component
{
    public static function ListAssetType()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'asset_type'])->all(), 'code', 'title');
    }

    //
    public static function CheckAssetItem($typeName,$code, $title)
    {
        // try {
            $fsnNum = explode('/', $code);
            $catgorise = Categorise::find()->where(['name' => 'asset_item', 'code' => $fsnNum[0]])->one();
            $getType = Categorise::find()->where(['name' => 'asset_type', 'title' =>$typeName])->one();

            if (!$catgorise) {
                $model = new Categorise;
            }else{
                $model = $catgorise;
            }

            $model->title = $title;
            $model->code = $fsnNum[0];
            $model->category_id = $getType->code ?? 0; 
            $model->name = 'asset_item';
            $model->active = 1;
            $model->save();
            return $model;

        // } catch (\Throwable $th) {
        //     return false;
        // }
    }

    public static function findByName($name,$title)
    {
        $catgorise = Categorise::find()->where(['name' => $name, 'title' => $title])->one();
        if($catgorise){
            return $catgorise->code ?? 0;
        }else{
            return 0;
        }
    }

    public static function getCode($name,$code)
    {
        $catgorise = Categorise::find()->where(['name' => $name, 'code' => $code])->one();
        if($catgorise){
            return $catgorise;
        }else{
            return false;
        }
    }
}
