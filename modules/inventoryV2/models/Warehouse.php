<?php

namespace app\modules\inventoryV2\models;

use Yii;
use yii\db\Expression;
use yii\helpers\Html;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\modules\hr\models\Employees;
use app\modules\filemanager\models\Uploads;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\hr\models\Organization;

/**
 * This is the model class for table "warehouses".
 * ย้ายมาจาก modules/inventory เพื่อแยกใช้ใน inventoryV2
 *
 * @property int $id
 * @property string $warehouse_name ชื่อคลังสินค้า
 * @property string $warehouse_code รหัสคลัง
 * @property string $warehouse_type MAIN=คลังหลัก, SUB=คลังย่อย, BRANCH=รพสต.
 * @property int $is_main เป็นคลังหลักหรือไม่
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
            [['data_json', 'created_at', 'updated_at', 'category_id', 'department', 'warehouse_type'], 'safe'],
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

    /**
     * ภาพทีมผู้ดูแลคลัง (avatar stack)
     */
    public function avatarStack()
    {
        try {
            $data = '';
            $data .= '<div class="avatar-stack">';
            foreach ($this->data_json['officer'] as $key => $avatar) {
                $emp = Employees::findOne(['user_id' => $avatar]);
                if ($emp) {
                    $data .= '<a href="javascript: void(0);" class="me-1" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-title="' . Html::encode($emp->fullname ?? '') . '">';
                    $data .= Html::img($emp->ShowAvatar(), ['class' => 'avatar-sm rounded-circle shadow']);
                    $data .= '</a>';
                }
            }
            $data .= '</div>';
            return $data;
        } catch (\Throwable $th) {
            return '';
        }
    }

    /**
     * ผู้ที่มีสิทธิรับผิดชอบคลัง
     */
    public function listUserstore()
    {
        $sql = "SELECT concat(emp.fname,' ',emp.lname) as fullname,emp.user_id FROM employees emp
        INNER JOIN user ON user.id = emp.user_id
        INNER JOIN auth_assignment auth ON auth.user_id = user.id
        where auth.item_name = :item_name";
        $querys = Yii::$app->db->createCommand($sql)->bindValue(':item_name', 'warehouse')->queryAll();
        return ArrayHelper::map($querys, 'user_id', 'fullname');
    }

    /**
     * รูปภาพคลัง
     */
    public function ShowImg($class = null)
    {
        $model = Uploads::find()->where(['ref' => $this->ref, 'name' => $class ?: 'warehouse'])->one();
        if ($model) {
            return FileManagerHelper::getImg($model->id);
        }
        return Yii::getAlias('@web') . '/images/store1.jpg';
    }

    public function TransactionStock()
    {
        $sql = "select x.*,ROUND(((x.qty / total) * 100),0) as progress FROM(SELECT s.*,(SELECT sum(qty) FROM stock WHERE qty > 0) as total FROM stock s WHERE warehouse_id = :warehouse_id AND qty > 0) as x;";
        return Yii::$app->db->createCommand($sql)->bindValue(':warehouse_id', $this->id)->queryOne();
    }

    public function SumPiceIn()
    {
        $sql = "SELECT COALESCE(sum(qty* unit_price),0) as total FROM stock  where  warehouse_id  = :warehouse_id AND thai_year = :thai_year";
        return Yii::$app->db->createCommand($sql, [
            ':warehouse_id' => $this->id,
            ':thai_year' => AppHelper::YearBudget(),
        ])->queryScalar();
    }

    public function SumPiceOut()
    {
        $sql = "SELECT COALESCE(sum(qty* unit_price),0) as total FROM stock_events  WHERE transaction_type ='OUT' AND order_status = 'success' AND warehouse_id  = :warehouse_id";
        return Yii::$app->db->createCommand($sql, [':warehouse_id' => $this->id])->queryScalar();
    }

    /**
     * แสดงประเภทคลัง (badge)
     */
    public function viewWarehouseType()
    {
        switch ($this->warehouse_type) {
            case 'MAIN':
                return '<span class="badge text-bg-warning text-dark"><i class="fa-solid fa-crown"></i> คลังหลัก</span>';
            case 'SUB':
                return '<span class="badge text-bg-secondary">คลังย่อย</span>';
            case 'BRANCH':
                return '<span class="badge text-bg-info">สาขา รพสต.</span>';
            default:
                return '<span class="badge text-bg-light text-dark">ไม่ระบุ</span>';
        }
    }

    public function departmentName()
    {
        $department = Organization::findOne(['id' => $this->department]);
        return $department ? $department->name : 'ไม่ระบุ';
    }

    /**
     * จำนวนใบขอเบิกรอดำเนินการ (ใช้ StockOrder ของ inventoryV2)
     */
    public function countOrderRequest()
    {
        return StockOrder::find()
            ->where([
                'main_warehouse_id' => $this->id,
                'order_type' => 'OUT',
                'source_type' => 'REQUEST',
            ])
            ->andWhere(['status' => 'DRAFT'])
            ->count();
    }

    public function ListOrderType()
    {
        return ArrayHelper::map(Categorise::find()->andWhere(['in', 'name', ['asset_type']])->andWhere(['category_id' => 4])->all(), 'code', 'title');
    }

    public function ListGroup()
    {
        return ArrayHelper::map(self::find()->where(['warehouse_type' => 'MAIN'])->all(), 'id', 'warehouse_name');
    }

    /**
     * คลังหลักสำหรับ dropdown ใบรับเข้า
     * แสดงเฉพาะคลังที่ผู้ใช้ล็อกอินถูกกำหนดเป็นเจ้าหน้าที่ (data_json.officer)
     * @return Warehouse[]
     */
    public static function findMainWarehousesForReceive()
    {
        if (Yii::$app->user->isGuest) {
            return [];
        }

        if (Yii::$app->user->can('admin')) {
            return self::find()
                ->where(['warehouse_type' => 'MAIN'])
                ->andWhere(['or', ['delete' => null], ['delete' => '']])
                ->orderBy('warehouse_name')
                ->all();
        }

        $userId = (string) Yii::$app->user->id;
        return self::find()
            ->where(['warehouse_type' => 'MAIN'])
            ->andWhere(['or', ['delete' => null], ['delete' => '']])
            ->andWhere(new Expression("JSON_CONTAINS(COALESCE(data_json,'{}'), '\"$userId\"', '$.officer')"))
            ->orderBy('warehouse_name')
            ->all();
    }

    /**
     * รหัสแผนก/ฝ่ายที่มีสิทธิเบิก (จากฟิลด์ department — อาจเป็นตัวเดียวหรือหลายตัวคั่นด้วย comma)
     * @return int[]
     */
    public function getDepartmentIds()
    {
        if ($this->department === null || $this->department === '') {
            return [];
        }
        $raw = is_array($this->department) ? implode(',', $this->department) : (string) $this->department;
        $parts = preg_split('/\s*,\s*/', trim($raw), -1, PREG_SPLIT_NO_EMPTY);
        $ids = [];
        foreach ($parts as $p) {
            if (is_numeric($p) && (int) $p > 0) {
                $ids[] = (int) $p;
            }
        }
        return array_values(array_unique($ids));
    }

    /**
     * เช็คว่าแผนกที่กำหนดอยู่ในการกำหนดแผนก/ฝ่ายที่มีสิทธิเบิกของคลังนี้หรือไม่
     * @param int|null $departmentId รหัสแผนก (เช่น จาก Employees.department)
     * @return bool
     */
    public function allowsDepartment($departmentId)
    {
        if ($departmentId === null || $departmentId === '') {
            return false;
        }
        $allowed = $this->getDepartmentIds();
        return in_array((int) $departmentId, $allowed, true);
    }

    /**
     * คลังย่อยสำหรับ dropdown / รายการ
     * แสดงเฉพาะคลังย่อยที่ user ที่ล็อกอินถูกกำหนดเป็นเจ้าหน้าที่ (data_json.officer)
     * ใช้สำหรับ "คลังที่รับของ" ในใบขอเบิก — เจ้าหน้าที่คลังย่อยเบิกของจากคลังหลักมาคลังตัวเอง
     * @return Warehouse[]
     */
    public static function findSubWarehousesForUser()
    {
        if (Yii::$app->user->isGuest) {
            return [];
        }

        $userId = (string) Yii::$app->user->id;
        return self::find()
            ->where(['warehouse_type' => 'SUB'])
            ->andWhere(['or', ['delete' => null], ['delete' => '']])
            ->andWhere(new Expression("JSON_CONTAINS(COALESCE(data_json,'{}'), '\"$userId\"', '$.officer')"))
            ->orderBy('warehouse_name')
            ->all();
    }

    /**
     * คลังย่อยที่ user ที่ล็อกอินมีสิทธิ "บันทึกการจ่ายพัสดุ" (เบิกของไปใช้งาน)
     * แสดงเฉพาะคลังย่อยที่แผนกของ user (Employees.department)
     * อยู่ใน warehouses.department (กำหนดแผนก/ฝ่ายที่มีสิทธิเบิก)
     * @return Warehouse[]
     */
    public static function findSubWarehousesForDepartment()
    {
        $userId = Yii::$app->user->isGuest ? null : Yii::$app->user->id;
        $departmentId = null;
        if ($userId && class_exists(Employees::class)) {
            $emp = Employees::findOne(['user_id' => $userId]);
            if ($emp && isset($emp->department) && $emp->department !== null && $emp->department !== '') {
                $departmentId = (int) $emp->department;
            }
        }
        if ($departmentId === null || $departmentId === 0) {
            return [];
        }

        $all = self::find()
            ->where(['warehouse_type' => 'SUB'])
            ->andWhere(['or', ['delete' => null], ['delete' => '']])
            ->orderBy('warehouse_name')
            ->all();

        return array_values(array_filter($all, function (Warehouse $w) use ($departmentId) {
            return $w->allowsDepartment($departmentId);
        }));
    }

    /**
     * คลังทั้งหมดที่ user เข้าถึงได้ (MAIN + SUB)
     * ใช้สำหรับ dropdown สลับคลังในหน้า admin (เช่น stock-min-max)
     * รวมเฉพาะคลังที่ตัวเองรับผิดชอบ (officer) หรือคลังย่อยที่แผนกตัวเองมีสิทธิ
     * @return Warehouse[]
     */
    public static function findAllAccessibleWarehouses()
    {
        $main = self::findMainWarehousesForReceive();
        $sub = self::findSubWarehousesForUser();
        $merged = array_merge($main, $sub);

        $seen = [];
        $result = [];
        foreach ($merged as $w) {
            if (isset($seen[$w->id])) {
                continue;
            }
            $seen[$w->id] = true;
            $result[] = $w;
        }
        return $result;
    }

    /**
     * รหัสประเภทวัสดุ (category code) ที่คลังนี้รับเข้าได้
     * จาก data_json['item_type'] ที่ตั้งในฟอร์มคลัง (DualListbox)
     * @return string[] array of codes หรือ [] ถ้าไม่จำกัด (รับได้ทุกประเภท)
     */
    public function getAllowedItemTypeCodes()
    {
        if (empty($this->data_json['item_type']) || !is_array($this->data_json['item_type'])) {
            return [];
        }
        return $this->data_json['item_type'];
    }

    /**
     * เช็คว่าคลังนี้รับพัสดุประเภทนี้ (category_id) ได้หรือไม่
     * @param string|null $categoryCode รหัสประเภทใน StockItem (category_id)
     * @return bool ถ้าไม่มีการกำหนดประเภทที่รับ = รับได้ทั้งหมด
     */
    public function allowsItemType($categoryCode)
    {
        $allowed = $this->getAllowedItemTypeCodes();
        if (empty($allowed)) {
            return true;
        }
        return in_array((string) $categoryCode, array_map('strval', $allowed), true);
    }
}
