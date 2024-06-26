<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\components\AppHelper;
use app\components\CategoriseHelper;
use app\models\Categorise;
use app\modules\am\models\Asset;
use app\modules\am\models\AssetItem;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\filemanager\models\Uploads;
use app\modules\helpdesk\models\Helpdesk;
use app\modules\hr\models\EmployeeDetail;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\console\Controller;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseConsole;
use yii\helpers\Json;
use Yii;

/**
 * update แก้ไขรายการตำแหน่ให้เป็นล่าสุด
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UpdateAssetController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex()
    {
        // if (BaseConsole::confirm("Are you sure?")) {

        $models = Asset::find()->where(['id' => 451])->all();
        // echo $models->id;
        foreach ($models as $model) {
            $asset = Asset::findOne($model->id);
            // echo $asset->asset_item . "\n";
            $Assetitem = AssetItem::find()->where(['code' => $asset->asset_item, 'name' => 'asset_item'])->one();
            echo $Assetitem->category_id . "\n";

            try {
                if ($asset->asset_group == 2) {
                    // try {
                    $vendor = isset($asset->data_json['vendor_id']) ? Categorise::find()->where(['code' => $asset->data_json['vendor_id'], 'name' => 'vendor'])->one() : '';
                    $department = Organization::find()->where(['id' => $asset->department])->one();
                    $array2 = [
                        'vendor_id' => isset($asset->data_json['vendor_id']) ? $asset->data_json['vendor_id'] : '',
                        'vendor' => $vendor,
                        'department_name' => isset($department) ? $department->name : '',
                        'budget_type_text' => isset(CategoriseHelper::CategoriseByCodeName($asset->data_json['budget_type'], 'budget_type')->title) ? CategoriseHelper::CategoriseByCodeName($asset->data_json['budget_type'], 'budget_type')->title : '',
                        'method_get_text' => isset(CategoriseHelper::CategoriseByCodeName($asset->data_json['method_get'], 'method_get')->title) ? CategoriseHelper::CategoriseByCodeName($asset->data_json['method_get'], 'method_get')->title : '',
                        'purchase_text' => isset(CategoriseHelper::CategoriseByCodeName($asset->purchase, 'purchase')->title) ? CategoriseHelper::CategoriseByCodeName($asset->purchase, 'purchase')->title : '',
                    ];
                    $asset->data_json = ArrayHelper::merge($asset->data_json, $array2);
                    // code...
                }

                if ($asset->asset_group == 3) {
                    // try {

                    $vendor = isset($asset->data_json['vendor_id']) ? Categorise::find()->where(['code' => $asset->data_json['vendor_id'], 'name' => 'vendor'])->one() : '';
                    $Assetitem = AssetItem::find()->where(['code' => $asset->asset_item, 'name' => 'asset_item'])->one();
                    $department = Organization::find()->where(['id' => $asset->department])->one();
                    $array2 = [
                        'vendor_id' => isset($asset->data_json['vendor_id']) ? $asset->data_json['vendor_id'] : '',
                        'vendor' => $vendor,
                        'item' => $Assetitem,
                        'department_name' => isset($department) ? $department->name : '',
                        'asset_name' => $Assetitem->title,
                        'asset_type_text' => $Assetitem->assetType->title,
                        'service_life' => $Assetitem->assetType->data_json['service_life'],
                        'depreciation' => $Assetitem->assetType->data_json['depreciation'],
                        'budget_type_text' => isset(CategoriseHelper::CategoriseByCodeName($asset->data_json['budget_type'], 'budget_type')->title) ? CategoriseHelper::CategoriseByCodeName($asset->data_json['budget_type'], 'budget_type')->title : '',
                        'method_get_text' => isset(CategoriseHelper::CategoriseByCodeName($asset->data_json['method_get'], 'method_get')->title) ? CategoriseHelper::CategoriseByCodeName($asset->data_json['method_get'], 'method_get')->title : '',
                        'purchase_text' => isset(CategoriseHelper::CategoriseByCodeName($asset->purchase, 'purchase')->title) ? CategoriseHelper::CategoriseByCodeName($asset->purchase, 'purchase')->title : '',
                    ];
                    $asset->data_json = ArrayHelper::merge($asset->data_json, $array2);
                    $asset->save(false);
                    echo $asset->asset_group . "\n";
                }
            } catch (\Throwable $th) {
                // throw $th;
            }
        }
    }
}
