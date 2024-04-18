<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\modules\am\models\Asset;
use app\modules\backoffice\models\AssetArticle;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use app\models\Categorise;
use app\components\AppHelper;
use yii\helpers\ArrayHelper;
use \yii\helpers\FileHelper;
use yii\helpers\BaseFileHelper;
use yii\db\Expression;
use app\modules\filemanager\models\Uploads;
use app\modules\hr\models\Organization;
use app\modules\hr\models\Employees;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ImportAssetController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex()
    {
        
        $this->Util();
        $this->Land();
        $this->Building();
       
        // ** Query 
        $sql = "SELECT 
        person.HR_CID,
                dep.HR_DEPARTMENT_SUB_SUB_ID,
                dep.HR_DEPARTMENT_SUB_SUB_NAME,
                a.VENDOR_ID,
                v.VENDOR_NAME,
                        asset_status.STATUS_NAME,
                        a.STATUS_ID,
                        a.ARTICLE_NAME as asset_name ,
                        a.YEAR_ID,
                        a.DECLINE_ID,
                        sd.DECLINE_NAME,
                        sd.OLD_YEAR,
                        sd.DECLINE_PERSEN,	
                        a.TYPE_ID,
                        st.SUP_TYPE_NAME,
                        a.ARTICLE_NUM as code,
                        IFNULL(a.SUP_FSN, '0000-000-0000') as asset_item,
                        concat('ยี่ห้อ ',b.BRAND_NAME,' รุ่น ',m.MODEL_NAME,' ขนาด ',s.SIZE_NAME,' สี ',c.COLOR_NAME) as detail,
                        a.SERIAL_NO as sn,
                        a.RECEIVE_DATE as receive_date,
                        a.PRICE_PER_UNIT as price,
                        a.BUDGET_id as budget_type,
                        s_bg.BUDGET_NAME as budget_name,
                        a.BUY_ID as purchase,
                        s_buy.BUY_NAME as purchase_name,
                        a.METHOD_ID  as method_get,
                        mt.METHOD_NAME as method_name,
                        a.SERIAL_NO as serial_number,
                        a.IMG
                                        FROM `asset_article`  a
                                        LEFT JOIN supplies_model m ON m.MODEL_ID = a.MODEL_ID
                                        LEFT JOIN supplies_size s ON s.SIZE_ID = a.SIZE_ID
                                        LEFT JOIN supplies_brand b ON b.BRAND_ID = a.BRAND_ID
                                        LEFT JOIN supplies_color c ON c.COLOR_ID = a.COLOR_ID
                                        LEFT JOIN supplies_method mt ON mt.METHOD_ID = a.METHOD_ID
                                        LEFT JOIN supplies_budget s_bg ON s_bg.BUDGET_ID = a.BUDGET_ID
                                        LEFT JOIN supplies_buy s_buy ON s_buy.BUY_ID = a.BUY_ID
                                        LEFT JOIN supplies_type st ON st.SUP_TYPE_ID = a.TYPE_ID
                                        LEFT JOIN supplies_decline sd ON sd.DECLINE_ID = a.DECLINE_ID
                                        LEFT JOIN asset_status  ON asset_status.STATUS_ID = a.STATUS_ID  
                                        LEFT JOIN supplies_vendor v ON v.VENDOR_ID = a.VENDOR_ID 
                                        LEFT JOIN hrd_department_sub_sub dep ON dep.HR_DEPARTMENT_SUB_SUB_ID = a.DEP_ID
                                        LEFT JOIN hrd_person person ON person.ID = a.PERSON_ID
                        ORDER BY `sd`.`DECLINE_NAME` ASC;";
        //  End Query
        $querys = Yii::$app->db2->createCommand($sql)->queryAll();
        foreach ($querys as $asset) {

            $ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
            $checkItem = Asset::findOne(['code' => $asset['code']]);
            $vendor = Categorise::findOne(['title' => $asset['VENDOR_NAME'],'name' => 'vendor']);
            $model = $checkItem ? $checkItem : new Asset([
                    'ref' => $ref
            ]);

            if(!$checkItem){
                $this->CreateDir($ref);
                if ($asset['IMG']) {
                    
                    $name = time() . '.jpg';
                    file_put_contents(Yii::getAlias('@app') . '/modules/filemanager/fileupload/' . $ref . '/' . $name, $asset['IMG']);
                    
                    $upload = new Uploads;
                    $upload->ref = $ref;
                    $upload->name = 'asset';
                    $upload->file_name = $name;
                    $upload->real_filename = $name;
                    $upload->type = 'jpg';
                    $upload->save(false);
                }
        }
            $model->asset_group = '3';
            $model->code = $asset['code'];
            $model->asset_item = $asset['asset_item'];
            $model->receive_date = $asset['receive_date'];
            $model->price = $asset['price'];
            $model->budget_type = $asset['purchase'];
            $model->purchase = $asset['purchase'];
            $model->receive_date = $asset['receive_date'];
            $model->asset_status = $asset['STATUS_ID'];
            $model->on_year  = $asset['YEAR_ID'];
            $model->owner = $asset['HR_CID'];
            $department = Organization::find()->where(['name' => $asset['HR_DEPARTMENT_SUB_SUB_NAME']])->one();
            $employee = Employees::find()->where(['cid' => $asset['HR_CID']])->one();
            $model->department = isset($department) ? $department->id : '';
            $model->data_json = [
                'owner_name' => isset($employee) ? $employee->fullname : '',
                'department_name' => isset($department)? $department->name : '',
                'department_name_old' => $asset['HR_DEPARTMENT_SUB_SUB_NAME'],
                'asset_group_name' => 'ครุภัณฑ์',
                'detail' => $asset['detail'],
                'method_get' => $asset['method_get'],
                'budget_type' => $asset['budget_type'],
                'budget_type_text' => $asset['budget_name'],
                'serial_number' => $asset['serial_number'],
                'asset_name' => $asset['asset_name'],
                'asset_type' => $asset['DECLINE_ID'], 
                'asset_type_text' => $asset['DECLINE_NAME'], 
                'decine_type' => $asset['DECLINE_ID'],
                'decine_text' => $asset['DECLINE_NAME'],
                'purchase_text' => $asset['purchase_name'],
                'status_name' => $asset['STATUS_NAME'],
                'service_life' => $asset['OLD_YEAR'],
                'depreciation' => $asset['DECLINE_PERSEN'],
                'vendor_id' =>  isset($vendor) ? $vendor->code : ''
            ];
           
            if ($model->save(false)) {
                // echo "นำเข้า " . (isset($department) ? $department->id : '-') . "\n";
                echo "นำเข้า " . $model->data_json['asset_name'] . "\n";
            } else {
                $asset['code'] . "เกิดข้อผิดพลาก \n";
            }

        }

        return ExitCode::OK;
    }

    //นำเข้าที่ดิน
    public static function Land()
    {
        $sql = "SELECT * FROM asset_land";
        $querys = Yii::$app->db2->createCommand($sql)->queryAll();
        foreach($querys as $query){
            $checkItem = Asset::findOne(['code' => $query['LAND_RAWANG']]);
            $model = $checkItem ? $checkItem : new Asset();
            $model->data_json = [
                'land_number' => $query['LAND_NUMBER'],
                'land_add' => $query['LAND_ADD'],
                'land_size' => $query['LAND_SIZE'],
                'land_size_ngan' => $query['LAND_SIZE_NGAN'],
                'land_size_tarangwa' => $query['LAND_SIZE_TARANGWA'],
                'land_owner' => $query['LAND_OWNER'],
            ];
            $model->asset_group =  1;
            $model->code =  $query['LAND_RAWANG'];
            echo $model->save(false);
        }
       
    }


     //นำเข้าที่ดิน
     public  function Building()
     {
        
         $sql = "SELECT * FROM asset_building";
         $querys = Yii::$app->db2->createCommand($sql)->queryAll();
         foreach($querys as $query){
            $ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
            // $checkItem = Asset::findOne(['code' => $query['BUILD_NAME']]);
            // $checkItem = Asset::findOne([new Expression("JSON_EXTRACT(data_json, '$.asset_name')") => $query['BUILD_NAME']]);
            $checkItem = Asset::find()->andFilterWhere(['=', new Expression("JSON_EXTRACT(asset.data_json, '$.asset_name')"), $query['BUILD_NAME']])->one();
            $model = $checkItem ? $checkItem : new Asset([
                'ref' =>  $ref
            ]);

            if(!$checkItem){
                $this->CreateDir($ref);
                if ($query['IMG']) {
                    
                    $name = time() . '.jpg';
                    file_put_contents(Yii::getAlias('@app') . '/modules/filemanager/fileupload/' . $ref . '/' . $name, $query['IMG']);
                    
                    $upload = new Uploads;
                    $upload->ref = $ref;
                    $upload->name = 'asset';
                    $upload->file_name = $name;
                    $upload->real_filename = $name;
                    $upload->type = 'jpg';
                    $upload->save(false);
                }
        }


            
            $model->asset_group = 2;
            $model->asset_item = $query['LAND_RAWANG'];
            $model->asset_item = $query['BUILD_NAME'];
            $model->price = $query['BUILD_NGUD_MONEY'];
            // $model->price = 200;
            $model->data_json = [
                'asset_name' => $query['BUILD_NAME']
            ];
            if( $model->save(false)){
               
                echo "นำเข้า ".$query['BUILD_NAME']. "\n";
            }else{
                echo "ผิดพลาด \n";

            }
            
        }
     }

    public static function Util()
    {
        //นำเข้าประเภททรัพย์สิน
        $typeQuerys = Yii::$app->db2->createCommand("SELECT 
        asset_status.STATUS_NAME,
        a.STATUS_ID,
        a.ARTICLE_NAME as asset_name ,
        a.YEAR_ID,
        a.DECLINE_ID,
        sd.DECLINE_NAME,
        sd.OLD_YEAR,
        sd.DECLINE_PERSEN,	
        a.TYPE_ID,
        st.SUP_TYPE_NAME,
        a.TYPE_SUB_ID,
        stm.SUP_TYPE_MASTER_NAME,
        a.ARTICLE_NUM as code,
        IFNULL(a.SUP_FSN, '0000-000-0000') as asset_item,
        concat('ยี่ห้อ ',b.BRAND_NAME,' รุ่น ',m.MODEL_NAME,' ขนาด ',s.SIZE_NAME,' สี ',c.COLOR_NAME) as detail,
        a.SERIAL_NO as sn,
        a.RECEIVE_DATE as receive_date,
        a.PRICE_PER_UNIT as price,
                a.BUDGET_id as budget_type,
                s_bg.BUDGET_NAME as budget_name,
                a.BUY_ID as purchase,
                s_buy.BUY_NAME as purchase_name,
                a.METHOD_ID  as method_get,
                mt.METHOD_NAME as method_name,
                a.SERIAL_NO as serial_number
                        FROM `asset_article`  a
                        LEFT JOIN supplies_model m ON m.MODEL_ID = a.MODEL_ID
                        LEFT JOIN supplies_size s ON s.SIZE_ID = a.SIZE_ID
                        LEFT JOIN supplies_brand b ON b.BRAND_ID = a.BRAND_ID
                        LEFT JOIN supplies_color c ON c.COLOR_ID = a.COLOR_ID
                        LEFT JOIN supplies_method mt ON mt.METHOD_ID = a.METHOD_ID
                        LEFT JOIN supplies_budget s_bg ON s_bg.BUDGET_ID = a.BUDGET_ID
                        LEFT JOIN supplies_buy s_buy ON s_buy.BUY_ID = a.BUY_ID
                        LEFT JOIN supplies_type st ON st.SUP_TYPE_ID = a.TYPE_ID
                        LEFT JOIN supplies_type_master stm ON stm.SUP_TYPE_MASTER_ID = st.SUP_TYPE_MASTER_ID
                        LEFT JOIN supplies_decline sd ON sd.DECLINE_ID = a.DECLINE_ID
                        LEFT JOIN asset_status  ON asset_status.STATUS_ID = a.STATUS_ID 
                        WHERE a.TYPE_ID IS NOT NULL
                            GROUP by a.DECLINE_ID;")->queryAll();
        foreach ($typeQuerys as $type) {
            $checkType =  Categorise::findOne(['title' => $type['DECLINE_NAME'],'name' => 'asset_type']);
            $getGroup =  Categorise::findOne(['title' => $type['SUP_TYPE_MASTER_NAME'],'name' => 'asset_group']);
            
            $assetType = $checkType ? $checkType : new Categorise();
            $assetType->category_id = $getGroup->code;
            $assetType->code = $type['DECLINE_ID'];
            $assetType->title = $type['DECLINE_NAME'];
            $assetType->name = 'asset_type';
            $assetType->data_json = [
                'group_name' => $type['SUP_TYPE_MASTER_NAME'],
                'service_life' => $type['OLD_YEAR'],
                'depreciation' => $type['DECLINE_PERSEN'],
            ];
            if( $assetType->save(false)){
               
                echo "นำเข้า ".$assetType->title. "\n";
            }else{
                echo "ผิดพลาด \n";

            }
            
        }


        //นำเข้าประเภททรัพย์สิน
        $itemQuerys = Yii::$app->db2->createCommand("SELECT 
        asset_status.STATUS_NAME,
        a.STATUS_ID,
        a.ARTICLE_NAME as asset_name ,
        a.YEAR_ID,
        a.DECLINE_ID,
        sd.DECLINE_NAME,
        sd.OLD_YEAR,
        sd.DECLINE_PERSEN,	
        a.TYPE_ID,
        st.SUP_TYPE_NAME,
        a.TYPE_SUB_ID,
        stm.SUP_TYPE_MASTER_NAME,
        a.ARTICLE_NUM as code,
        IFNULL(a.SUP_FSN, '0000-000-0000') as asset_item,
        concat('ยี่ห้อ ',b.BRAND_NAME,' รุ่น ',m.MODEL_NAME,' ขนาด ',s.SIZE_NAME,' สี ',c.COLOR_NAME) as detail,
        a.SERIAL_NO as sn,
        a.RECEIVE_DATE as receive_date,
        a.PRICE_PER_UNIT as price,
                a.BUDGET_id as budget_type,
                s_bg.BUDGET_NAME as budget_name,
                a.BUY_ID as purchase,
                s_buy.BUY_NAME as purchase_name,
                a.METHOD_ID  as method_get,
                mt.METHOD_NAME as method_name,
                a.SERIAL_NO as serial_number
                        FROM `asset_article`  a
                        LEFT JOIN supplies_model m ON m.MODEL_ID = a.MODEL_ID
                        LEFT JOIN supplies_size s ON s.SIZE_ID = a.SIZE_ID
                        LEFT JOIN supplies_brand b ON b.BRAND_ID = a.BRAND_ID
                        LEFT JOIN supplies_color c ON c.COLOR_ID = a.COLOR_ID
                        LEFT JOIN supplies_method mt ON mt.METHOD_ID = a.METHOD_ID
                        LEFT JOIN supplies_budget s_bg ON s_bg.BUDGET_ID = a.BUDGET_ID
                        LEFT JOIN supplies_buy s_buy ON s_buy.BUY_ID = a.BUY_ID
                        LEFT JOIN supplies_type st ON st.SUP_TYPE_ID = a.TYPE_ID
                        LEFT JOIN supplies_type_master stm ON stm.SUP_TYPE_MASTER_ID = st.SUP_TYPE_MASTER_ID
                        LEFT JOIN supplies_decline sd ON sd.DECLINE_ID = a.DECLINE_ID
                        LEFT JOIN asset_status  ON asset_status.STATUS_ID = a.STATUS_ID 
                            GROUP by a.SUP_FSN;")->queryAll();
        foreach ($itemQuerys as $item) {
            $checkItem =  Categorise::findOne(['code' => $item['asset_item'],'name' => 'asset_item']);
            $itemGroup =  Categorise::findOne(['title' => $item['DECLINE_NAME'],'name' => 'asset_type']);
            
            
            $assetItem = $checkItem ? $checkItem : new Categorise();
            // $assetType->category_id = isset($itemGroup->code) ? $itemGroup->code : '';
            if($itemGroup){
                $assetItem->category_id = $itemGroup->code;
                // echo "นำเข้า ".$itemGroup->code. "\n";

            }
            $assetItem->code = $item['asset_item'];
            $assetItem->title = $item['asset_name'];
            $assetItem->name = 'asset_item';
            // $assetType->data_json = [
            //     'group_name' => $type['SUP_TYPE_MASTER_NAME'],
            //     'service_life' => $type['OLD_YEAR'],
            //     'depreciation' => $type['DECLINE_PERSEN'],
            // ];
            if( $assetItem->save(false)){
               
                echo "นำเข้า ".$assetItem->title. "\n";
            }else{
                echo "ผิดพลาด \n";

            }
            
        }


        
        
        $unitQuerys = Yii::$app->db2->createCommand("SELECT * FROM supplies_unit")->queryAll();
        foreach ($unitQuerys as $unit) {
            $checkUnit =  Categorise::findOne(['title' => $unit['SUP_UNIT_NAME'],'name' => 'unit']);
            $assetUnit = $checkUnit ? $checkUnit : new Categorise();
            $assetUnit->name = 'unit';
            $assetUnit->title = $unit['SUP_UNIT_NAME'];
            $assetUnit->save(false);
        }

        $sqlVendor = "SELECT 
        a.VENDOR_ID,
        v.VENDOR_NAME,
        v.VENDOR_TAX_NUM,
                asset_status.STATUS_NAME,
                a.STATUS_ID,
                a.ARTICLE_NAME as asset_name ,
                a.YEAR_ID,
                a.DECLINE_ID,
                sd.DECLINE_NAME,
                sd.OLD_YEAR,
                sd.DECLINE_PERSEN,	
                a.TYPE_ID,
                st.SUP_TYPE_NAME,
                a.ARTICLE_NUM as code,
                IFNULL(a.SUP_FSN, '0000-000-0000') as asset_item,
                concat('ยี่ห้อ ',b.BRAND_NAME,' รุ่น ',m.MODEL_NAME,' ขนาด ',s.SIZE_NAME,' สี ',c.COLOR_NAME) as detail,
                a.SERIAL_NO as sn,
                a.RECEIVE_DATE as receive_date,
                a.PRICE_PER_UNIT as price,
                a.BUDGET_id as budget_type,
                s_bg.BUDGET_NAME as budget_name,
                a.BUY_ID as purchase,
                s_buy.BUY_NAME as purchase_name,
                a.METHOD_ID  as method_get,
                mt.METHOD_NAME as method_name,
                a.SERIAL_NO as serial_number,
                a.IMG
                                FROM `asset_article`  a
                                LEFT JOIN supplies_model m ON m.MODEL_ID = a.MODEL_ID
                                LEFT JOIN supplies_size s ON s.SIZE_ID = a.SIZE_ID
                                LEFT JOIN supplies_brand b ON b.BRAND_ID = a.BRAND_ID
                                LEFT JOIN supplies_color c ON c.COLOR_ID = a.COLOR_ID
                                LEFT JOIN supplies_method mt ON mt.METHOD_ID = a.METHOD_ID
                                LEFT JOIN supplies_budget s_bg ON s_bg.BUDGET_ID = a.BUDGET_ID
                                LEFT JOIN supplies_buy s_buy ON s_buy.BUY_ID = a.BUY_ID
                                LEFT JOIN supplies_type st ON st.SUP_TYPE_ID = a.TYPE_ID
                                LEFT JOIN supplies_decline sd ON sd.DECLINE_ID = a.DECLINE_ID
                                LEFT JOIN asset_status  ON asset_status.STATUS_ID = a.STATUS_ID  
                                LEFT JOIN supplies_vendor v ON v.VENDOR_ID = a.VENDOR_ID 
                ORDER BY v.VENDOR_ID ASC";
                 $vendorQuerys = Yii::$app->db2->createCommand($sqlVendor)->queryAll();
                 foreach ($vendorQuerys as $vendor) {
                     $checkVendor =  Categorise::findOne(['title' => $vendor['VENDOR_NAME'],'name' => 'vendor']);
                     $vendorModel = $checkVendor ? $checkVendor : new Categorise();
                     $vendorModel->name = 'vendor';
                     $vendorModel->code = $vendor['VENDOR_TAX_NUM'];
                     $vendorModel->title = $vendor['VENDOR_NAME'];
                     if($vendorModel->save(false)){

                        echo "นำเข้า ".$vendorModel->title. "\n";
                    }else{
                        echo "ผิดพลาด \n";
        
                    }
                     
                 }
        
    }

    public static function CreateDir($folderName)
{
    if ($folderName != null) {
        $basePath = Yii::getAlias('@app') . '/modules/filemanager/fileupload/';
        if (BaseFileHelper::createDirectory($basePath . $folderName, 0777)) {
            BaseFileHelper::createDirectory($basePath . $folderName . '/thumbnail', 0777);
        }
    }
    return;
}

}
