<?php

namespace app\modules\inventory;

use Yii;
use app\modules\inventory\models\Warehouse;

/**
 * warehouse module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\inventory\controllers';

    /**
     * โหมดอ่านอย่างเดียว (Big-bang migration → V2):
     * - true  = ปิดการสร้าง/แก้ไข/ยืนยัน/ยกเลิกใบเบิก-จ่าย-รับใน V1 (อ่านได้อย่างเดียว)
     * - false = ใช้งานปกติ
     * Default true หลังเสร็จ migration — แก้ใน config/main.php ถ้าต้องการเปิดชั่วคราว
     * @var bool
     */
    public $frozen = true;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
    }

    /**
     * {@inheritdoc}
     * ซ่อม session('warehouse') ที่ยังค้างเป็น array (จาก StockOrder/StockOutController::setWarehouse รุ่นเก่า)
     * ให้เป็น object เหมือนที่โค้ดส่วนใหญ่ในโมดูลนี้คาดหวัง (ป้องกัน "Attempt to read property on array")
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        $warehouse = Yii::$app->session->get('warehouse');
        if (is_array($warehouse)) {
            $id = $warehouse['id'] ?? ($warehouse['warehouse_id'] ?? null);
            Yii::$app->session->set('warehouse', $id ? Warehouse::findOne($id) : null);
        }
        return true;
    }

    /**
     * เช็คว่าโมดูล V1 อยู่ในโหมด freeze (read-only) หรือไม่
     */
    public static function isFrozen()
    {
        $module = Yii::$app->getModule('inventory');
        return $module instanceof self ? (bool) $module->frozen : true;
    }
}
