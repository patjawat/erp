<?php

/**
 * @see http://www.yiiframework.com/
 *
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\Uploads;
use yii\console\ExitCode;
use app\models\Categorise;
use yii\console\Controller;
use yii\helpers\FileHelper;
use yii\helpers\BaseConsole;
use app\components\AppHelper;
use app\modules\inventory\models\Stock;
use app\modules\inventory\models\StockEvent;
use app\modules\filemanager\components\FileManagerHelper;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 *
 * @since 2.0
 * คลังวัสดุเทคนิคการแพทย์
 */
class ImportStockYahaController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     *
     * @return int Exit code
     */
    public function actionIndex($message = 'hello world')
    {
        echo $message . "\n";

        $this->M7();
        return ExitCode::OK;
    }

    // วัสดุวิสำนักงาน
    public function actionM1()
    {
        $data = [
            'thai_year' => 2568,
            'warehouse_id' => 1,
            'assettype' => 'M1',
            'categoryName' => 'วัสดุวิสำนักงาน',
            'category_id' => 346,
            'code' => 'IN-680002',
            'items' => [
                ['title' => 'กระดาษกาวชิ้น A12', 'unit' => 'แพ็ค', 'unit_price' => 35.0, 'qty' => 116],
                
            ]
        ];
        $this->Import($data);
    }


    // วัสดุงานบ้านงานครัว
    public function actionM3()
    {
        $data = [
            'thai_year' => 2568,
            'warehouse_id' => 1,
            'assettype' => 'M3',
            'categoryName' => 'วัสดุงานบ้านงานครัว',
            'category_id' => 347,
            'code' => 'IN-680003',
            'items' => [
                ['title' => 'กรวยกระดาษ (1*25)', 'unit' => 'แถว','unit_price' =>44.94,'qty' =>22],
               
            ]
        ];
        $this->Import($data);
    }

    // วัสดุคอมพิวเตอร์
    public function actionM12()
    {
        $data = [
            'thai_year' => 2568,
            'warehouse_id' => 1,
            'assettype' => 'M12',
            'categoryName' => 'วัสดุคอมพิวเตอร์',
            'category_id' => 348,
            'code' => 'IN-680004',
            'items' => [
                ['title' => 'หมึก PB285A', 'unit' => 'กล่อง','unit_price' =>290.00,'qty' =>61],
               
            ]
        ];
        $this->Import($data);
    }



    // วัสดุทันตกรรม
    public function actionM19()
    {
        $data = [
            'thai_year' => 2568,
            'warehouse_id' => 21,
            'assettype' => 'M19',
            'categoryName' => 'วัสดุทันตกรรม',
            'category_id' => 349,
            'code' => 'IN-680005',
            'items' => [
                ['title' => 'Round 008', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
               
            ]
        ];
        $this->Import($data);
    }



    public static function Import($data)
    {
        if (BaseConsole::confirm('Are you sure?')) {
            $total = 0;

            // clear
            $oldItems = Categorise::find()->where([
                'name' => 'asset_item',
                'group_id' => 4,
                'category_id' => $data['assettype']
            ])->all();

            

            if (!empty($oldItems)) {
                foreach ($oldItems as $oldItem) {
                    $clearUpload = Uploads::find()->where(['ref' => $oldItem->ref])->one();
                    if ($clearUpload) {
                        FileManagerHelper::removeUploadDir($clearUpload->ref);
                    }
                    $check = Categorise::findOne($oldItem->id);
                    if($check){
                        $check->delete();
                    }
                }
            }
            // ############################

            foreach ($data['items'] as $key => $value) {
                $itemCode = $data['assettype'] . '-' . ($key + 1);
                $asetItem = Categorise::findOne(['name' => 'asset_item', 'code' => $itemCode, 'title' => $value['title']]);
                // echo $itemCode."\n";
                $unit = Categorise::findOne(['name' => 'unit', 'title' => $value['unit']]);
                // ถ้าไม่มีหน่วยให้สร้างใหม่
                if (!$unit) {
                    $newUnit = new Categorise([
                        'name' => 'unit',
                        'title' => $value['unit'],
                        'active' => 1,
                    ]);
                    $newUnit->save(false);
                }
                // ถ้าไม่มีประวัสดุใฟ้สร้างมห่
                if (!$asetItem) {
                    $newItem = new Categorise([
                        'ref' => substr(\Yii::$app->getSecurity()->generateRandomString(), 10),
                        'name' => 'asset_item',
                        'group_id' => 4,
                        'category_id' => $data['assettype'],
                        'code' => $itemCode,
                        'title' => $value['title'],
                        'data_json' => [
                            'unit' => $value['unit'],
                            'sub_title' => '',
                            'price_name' => '',
                            'category_name' => $data['categoryName'],
                            'asset_type_name' => '',
                        ],
                    ]);
                    $newItem->save(false);
                }

                $qty = (int) explode('.', $value['qty'])[0];

                $lot = \mdm\autonumber\AutoNumber::generate('LOT' . substr(AppHelper::YearBudget(), 2) . '-?????');
                $ref = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);

                // $checkModel = StockEvent::findOne([])
                $model = new StockEvent([
                    'ref' => $ref,
                    'lot_number' => $lot,
                    'name' => 'order_item',
                    'code' => $data['code'],
                    'category_id' => $data['category_id'],
                    'transaction_type' => 'IN',
                    'thai_year' => $data['thai_year'],
                    'asset_item' => $itemCode,
                    'warehouse_id' => $data['warehouse_id'],
                    'qty' => $value['qty'],
                    'unit_price' => (float) $value['unit_price'],
                    'order_status' => 'pending',
                    'data_json' => [
                        'req_qty' => '0',
                        'exp_date' => '',
                        'mfg_date' => '',
                        'item_type' => 'ยอดยกมา',
                        'po_number' => '',
                        'pq_number' => '',
                        'asset_type' => '',
                        'receive_date' => '',
                        'asset_type_name' => '',
                        'employee_fullname' => 'Administrator Lastname',
                        'employee_position' => 'นักวิชาการคอมพิวเตอร์',
                        'employee_department' => 'งานซ่อมบำรุง',
                    ],
                    'created_by' => 1,
                    'updated_by' => 1,
                ]);
                // echo (DOUBLE) $value['unit_price'],"\n";
                if ($model->save(false)) {
                    echo 'นำเข้า ' . $data['code'] . ' รหัส : ' . $data['code'] . "สำเร็จ! \n";
                } else {
                    echo 'นำเข้า ' . $data['code'] . ' รหัส : ' . $data['code'] . "ผิดพลาด! \n";
                }
                $sum = $qty * (int) $value['unit_price'];
                $total += $sum;
            }
            echo $total;
        }
    }

    // วัสดุวิทยาศาสตร์หรือการแพทย์ สำเร็จ!!
    public function actionM7()
    {
        $thai_year = 2568;
        $warehouse_id = 2;
        $assettype = 'M7';
        $categoryName = 'วัสดุวิทยาศาสตร์หรือการแพทย์';
        $category_id = 1;
        $code = 'IN-680001';

        $data = [
            ['title' => 'AIR WAY 60 mm. No.0', 'unit' => 'อัน', 'unit_price' => 20.0, 'qty' => 30],
        ];

        if (BaseConsole::confirm('Are you sure?')) {
            $total = 0;

            foreach ($data as $key => $value) {
                $itemCode = $assettype . '-' . ($key + 1);
                $asetItem = Categorise::findOne(['name' => 'asset_item', 'code' => $itemCode, 'title' => $value['title']]);
                // echo $itemCode."\n";
                $unit = Categorise::findOne(['name' => 'unit', 'title' => $value['unit']]);
                // ถ้าไม่มีหน่วยให้สร้างใหม่
                if (!$unit) {
                    $newUnit = new Categorise([
                        'name' => 'unit',
                        'title' => $value['unit'],
                        'active' => 1,
                    ]);
                    $newUnit->save(false);
                }
                // ถ้าไม่มีประวัสดุใฟ้สร้างมห่
                if (!$asetItem) {
                    $newItem = new Categorise([
                        'name' => 'asset_item',
                        'group_id' => 4,
                        'category_id' => $assettype,
                        'code' => $itemCode,
                        'title' => $value['title'],
                        'data_json' => [
                            'unit' => $value['unit'],
                            'sub_title' => '',
                            'price_name' => '',
                            'category_name' => $categoryName,
                            'asset_type_name' => '',
                        ],
                    ]);
                    $newItem->save(false);
                }

                $qty = (int) explode('.', $value['qty'])[0];

                $lot = \mdm\autonumber\AutoNumber::generate('LOT' . substr(AppHelper::YearBudget(), 2) . '-?????');
                $ref = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);

                // $checkModel = StockEvent::findOne([])
                $model = new StockEvent([
                    'ref' => $ref,
                    'lot_number' => $lot,
                    'name' => 'order_item',
                    'code' => $code,
                    'category_id' => $category_id,
                    'transaction_type' => 'IN',
                    'thai_year' => $thai_year,
                    'asset_item' => $itemCode,
                    'warehouse_id' => $warehouse_id,
                    'qty' => $value['qty'],
                    'unit_price' => (float) $value['unit_price'],
                    'order_status' => 'pending',
                    'data_json' => [
                        'req_qty' => '0',
                        'exp_date' => '',
                        'mfg_date' => '',
                        'item_type' => 'ยอดยกมา',
                        'po_number' => '',
                        'pq_number' => '',
                        'asset_type' => '',
                        'receive_date' => '',
                        'asset_type_name' => '',
                        'employee_fullname' => 'Administrator Lastname',
                        'employee_position' => 'นักวิชาการคอมพิวเตอร์',
                        'employee_department' => 'งานซ่อมบำรุง',
                    ],
                    'created_by' => 1,
                    'updated_by' => 1,
                ]);
                // echo (DOUBLE) $value['unit_price'],"\n";
                if ($model->save(false)) {
                    echo 'นำเข้า ' . $code . ' รหัส : ' . $code . "สำเร็จ! \n";
                } else {
                    echo 'นำเข้า ' . $code . ' รหัส : ' . $code . "ผิดพลาด! \n";
                }
                $sum = $qty * (int) $value['unit_price'];
                $total += $sum;
            }
            echo $total;
        }
    }


    // วัสดุวิทยาศาสตร์หรือการแพทย์ แก้ไขยอกนำเข้าผิด
    public function actionUpdateStockM7()
    {
        $thai_year = 2568;
        $warehouse_id = 2;
        $assettype = 'M7';
        $categoryName = 'วัสดุวิทยาศาสตร์หรือการแพทย์';
        $category_id = 1;
        $code = 'IN-680001';

        $data = [
            ['title' => 'AIR WAY 60 mm. No.0', 'unit' => 'อัน','unit_price' =>20.00,'qty' =>30,'code' =>'M7-1','lot_number' =>'LOT68-00001'],

        ];

        if (BaseConsole::confirm('Are you sure?')) {
            $total = 0; // ตรวจสอบให้แน่ใจว่าเริ่มต้นเป็นตัวเลข
        
            foreach ($data as $key => $value) {
                
                $asetItem = Categorise::findOne(['name' => 'asset_item', 'code' => $value['code']]);
               
                if ($asetItem) {
                    $stockEvent = StockEvent::findOne(['lot_number' => $value['lot_number']]);
                    $stock = Stock::findOne(['lot_number' => $value['lot_number']]);
                    $stockEvent->qty = $value['qty'];
                    $stockEvent->unit_price = $value['unit_price'];
                    $stockEvent->save(false);
                    $stock->qty = $value['qty'];
                    $stock->unit_price = $value['unit_price'];
                    $stock->save(false);
                    // echo $asetItem->title . "\n";
                    // $$asetItem = $value['title']
                    // echo $value['title']."\n";
                    // echo $stockEvent->code.$stockEvent->name."\n";
                    echo $stockEvent->qty." ".$stock->qty." ".$value['qty']."\n";
                    // echo $stock->qty." ".$value['qty']."\n";
                }else{
                    $total++; // ใช้การเพิ่มค่าที่ถูกต้อง
                    echo $value['title'] . "\n";
                    
                }
            }
        
            echo "Total: " . $total . "\n";
        }
        
    }

    // วัสดุการแพทย์ทั่วไป
    public function actionM22()
    {
        // คลังวัสดุกายภาพบำบัด
        $warehouse_id = 4;
        $assettype = 'M22';
        $categoryName = 'วัสดุการแพทย์ทั่วไป';
        $category_id = 1228;
        $code = 'IN-680014';

        $data = [
            ['code' => '22-00162', 'title' => 'กระดา', 'unit' => 'อัน', 'qty' => '11.00', 'unit_price' => '374.5'],
        ];

        if (BaseConsole::confirm('Are you sure?')) {
            $total = 0;
            foreach ($data as $key => $value) {
                $asetItem = Categorise::findOne(['name' => 'asset_item', 'code' => $value['code'], 'title' => $value['title']]);
                $unit = Categorise::findOne(['name' => 'unit', 'title' => $value['unit']]);
                // ถ้าไม่มีหน่วยให้สร้างใหม่
                if (!$unit) {
                    $newUnit = new Categorise([
                        'name' => 'unit',
                        'title' => $value['unit'],
                        'active' => 1,
                    ]);
                    $newUnit->save(false);
                }
                // ถ้าไม่มีประวัสดุใฟ้สร้างมห่
                if (!$asetItem) {
                    $newItem = new Categorise([
                        'name' => 'asset_item',
                        'group_id' => 4,
                        'category_id' => $assettype,
                        'code' => $value['code'],
                        'title' => $value['title'],
                        'data_json' => [
                            'unit' => $value['unit'],
                            'sub_title' => '',
                            'price_name' => '',
                            'category_name' => $categoryName,
                            'asset_type_name' => '',
                        ],
                    ]);
                    $newItem->save(false);
                }

                $qty = (int) explode('.', $value['qty'])[0];

                $lot = \mdm\autonumber\AutoNumber::generate('LOT' . substr(AppHelper::YearBudget(), 2) . '-?????');
                $ref = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);
                $model = new StockEvent([
                    'ref' => $ref,
                    'lot_number' => $lot,
                    'name' => 'order_item',
                    'code' => $value['code'],
                    'category_id' => $category_id,
                    'transaction_type' => 'IN',
                    'asset_item' => $code,
                    'warehouse_id' => $warehouse_id,
                    'qty' => $value['qty'],
                    'unit_price' => (float) $value['unit_price'],
                    'order_status' => 'pending',
                    'data_json' => [
                        'req_qty' => '0',
                        'exp_date' => '',
                        'mfg_date' => '',
                        'item_type' => 'ยอดยกมา',
                        'po_number' => '',
                        'pq_number' => '',
                        'asset_type' => '',
                        'receive_date' => '',
                        'asset_type_name' => '',
                        'employee_fullname' => 'Administrator Lastname',
                        'employee_position' => 'นักวิชาการคอมพิวเตอร์',
                        'employee_department' => 'งานซ่อมบำรุง',
                    ],
                    'created_by' => 1,
                    'updated_by' => 1,
                ]);
                // echo (DOUBLE) $value['unit_price'],"\n";
                if ($model->save(false)) {
                    echo 'นำเข้า ' . $value['code'] . ' รหัส : ' . $value['code'] . "สำเร็จ! \n";
                } else {
                    echo 'นำเข้า ' . $value['code'] . ' รหัส : ' . $value['code'] . "ผิดพลาด! \n";
                }
                $sum = $qty * (int) $value['unit_price'];
                $total += $sum;
            }
            echo $total;
        }
    }

}
