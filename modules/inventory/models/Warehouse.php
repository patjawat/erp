<?php

namespace app\modules\inventory\models;

use app\components\AppHelper;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\filemanager\models\Uploads;
use app\modules\hr\models\Employees;
use app\models\Categorise;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use Yii;

/**
 * This is the model class for table "warehouses".
 *
 * @property int $warehouse_id รหัสคลังสินค้า
 * @property string $warehouse_name ชื่อคลังสินค้า
 * @property string $warehouse_code รหัสคลัง เช่น รหัส รพ. รพสต.
 * @property string $warehouse_type ประเภทการเคลื่อนไหว (MAIN = คลังหลัก, SUB = ตลังย่อย, BRANCH = สาขา รพสต.)
 * @property int $is_main เป็นคลังหลักหรือไม่ (true = คลังหลัก, false = คลังย่อย)
 */
class Warehouse extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'warehouses';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['warehouse_name', 'warehouse_type'], 'required'],
            [['warehouse_type'], 'string'],
            [['is_main'], 'integer'],
            [['data_json', 'created_at', 'updated_at','category_id','department','warehouse_type'], 'safe'],
            [['warehouse_name', 'warehouse_code'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'warehouse_name' => 'ชื่อคลัง',
            'warehouse_code' => 'รหัสคลัง',
            'warehouse_type' => 'ประเภทคลัง',
            'is_main' => 'เป็นคลังหลัก',
        ];
    }


    //ร้อยละปริมานเข้าออกของแต่ละคลัง
    public function TransactionStock()
    {
        $sqlold = "select x.*, 
        ROUND(((x.transaction_in / x.total) *100),0) as stockin,
        ROUND(((x.transaction_out / x.total) *100),0) as stockout FROM (
        SELECT thai_year, (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'IN' AND warehouse_id = :warehouse_id) as transaction_in, 
        (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE transaction_type = 'OUT' AND warehouse_id = :warehouse_id) as transaction_out, 
        (SELECT IFNULL(CONVERT(SUM(qty), UNSIGNED),0) FROM stock_events WHERE warehouse_id = :warehouse_id) as total ,
        warehouse_id
        FROM stock_events GROUP BY thai_year) as x";

        $sql = "select x.*,ROUND(((x.qty / total) * 100),0) as progress FROM(SELECT s.*,(SELECT sum(qty) FROM stock WHERE qty > 0) as total FROM stock s WHERE warehouse_id = :warehouse_id AND qty > 0) as x;";
        $query = Yii::$app->db->createCommand($sql)
        ->bindValue(':warehouse_id', $this->id)
        ->queryOne();
        return $query;
    }
    //  ภาพทีมผู้ดูและคลัง
    public function avatarStack()
    {
        try {
            $data = '';
            $data .= '<div class="avatar-stack">';
            foreach ($this->data_json['officer'] as $key => $avatar) {
                $emp = Employees::findOne(['user_id' => $avatar]);
                $data .= '<a href="javascript: void(0);" class="me-1" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-title="' . $emp->fullname . '">';
                $data .= Html::img($emp->ShowAvatar(), ['class' => 'avatar-sm rounded-circle shadow']);
                $data .= '</a>';
            }
            $data .= '</div>';
            return $data;
        } catch (\Throwable $th) {
        }
    }

    // ผู้ที่มีสิทรับผิดชอลคลัง
    public function listUserstore()
    {
        $sql = "SELECT concat(emp.fname,' ',emp.lname) as fullname,emp.user_id FROM employees emp
        INNER JOIN user ON user.id = emp.user_id
        INNER JOIN auth_assignment auth ON auth.user_id = user.id
        where auth.item_name = :item_name";
        $querys = Yii::$app
            ->db
            ->createCommand($sql)
            ->bindValue(':item_name', 'warehouse')
            ->queryAll();
        return ArrayHelper::map($querys, 'user_id', 'fullname');
    }

    public function ShowImg($class = null)
    {
        // try {
        $model = Uploads::find()->where(['ref' => $this->ref, 'name' => $class ? $class : 'warehouse'])->one();
        if ($model) {
            return FileManagerHelper::getImg($model->id);
        } else {
            return Yii::getAlias('@web') . '/images/store1.jpg';
        }
        // } catch (\Throwable $th) {
        //     // throw $th;
        //     return Yii::getAlias('@web') . '/images/store1.jpg';
        // }
    }

    //ม๔ลค่าสินค้าแยกตาม Stock
    public function SumPice()
    {

        // $sql = "SELECT IFNULL(sum(qty * unit_price),0) as total FROM stock WHERE warehouse_id = :warehouse_id AND thai_year <> :thai_year";
        $sql = "SELECT IFNULL(sum(qty * unit_price),0) as total FROM stock WHERE warehouse_id = :warehouse_id";
        $model =  Yii::$app->db->createCommand($sql, [
            ':warehouse_id' => $this->id,
            // ':thai_year' => AppHelper::YearBudget()
            ])->queryScalar();
            return number_format($model,2);
    }

    // ยอดรับเข้า
    public function SumPiceIn()
    {
        // $sql = "SELECT COALESCE(sum(qty* unit_price),0) as total FROM stock_events  WHERE transaction_type ='IN' AND order_status = 'success' AND warehouse_id  = :warehouse_id";
        $sql = "SELECT COALESCE(sum(qty* unit_price),0) as total FROM stock  where  warehouse_id  = :warehouse_id AND thai_year = :thai_year";
        $model =  Yii::$app->db->createCommand($sql, [
            ':warehouse_id' => $this->id,
            ':thai_year' => AppHelper::YearBudget()
            ])->queryScalar();
            return $model;
    }
// ยอดจ่ายออก
    public function SumPiceOut()
    {
        $sql = "SELECT COALESCE(sum(qty* unit_price),0) as total FROM stock_events  WHERE transaction_type ='OUT' AND order_status = 'success' AND warehouse_id  = :warehouse_id";
        $model =  Yii::$app->db->createCommand($sql, [
            ':warehouse_id' => $this->id,
            ])->queryScalar();
            return $model;
    }


   

//แสดงจำนวนรายการที่ขอเบิก
    public function countOrderRequest()
    {
            // return StockEvent::find()->where(['warehouse_id' => $this->id,'name' => 'order', 'order_status' => 'pending'])->count('id');
            return StockEvent::find()->where(['warehouse_id' => $this->id,'name' => 'order', 'order_status' => 'pending'])->count('id');
    }
    //แสดงประเภทสินค้าบริการ
    public function ListOrderType()
    {
        return ArrayHelper::map(Categorise::find()->andWhere(['in', 'name', ['asset_type']])->all(), 'code', 'title');
    }

    public function ListGroup()
    {
        return ArrayHelper::map(self::find()->where(['warehouse_type' => 'MAIN'])->all(), 'id', 'warehouse_name');
    }
}
