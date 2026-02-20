<?php

namespace app\modules\inventoryV2\models;

use app\models\Categorise;
use app\models\Uploads;
use app\modules\filemanager\components\FileManagerHelper;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "stock_item".
 *
 * @property int $id
 * @property string $item_code รหัสพัสดุ
 * @property string $item_name ชื่อพัสดุ
 * @property int|null $category_id ID หมวดหมู่
 * @property float|null $min_qty จุดสั่งซื้อขั้นต่ำ
 * @property float|null $max_qty จุดสั่งซื้อขั้นสูง
 * @property int|null $is_asset เป็นครุภัณฑ์หรือไม่ (0=วัสดุ, 1=ครุภัณฑ์)
 * @property int|null $is_innovation เป็นบัญชีนวัตกรรมหรือไม่ (0=ไม่เป็น, 1=เป็น)
 * @property int|null $is_active สะานะ (0=ปิด, 1=เปิด)
 * @property string|null $ref ฟิลด์อ้างอิงภายนอก
 * @property string|null $data_json เก็บข้อมูลเสริมอื่นๆ รูปแบบ JSON
 * @property int|null $created_at เวลาที่สร้าง
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_at เวลาที่แก้ไขล่าสุด
 * @property int|null $updated_by ผู้แก้ไขล่าสุด
 */
class StockItem extends \yii\db\ActiveRecord
{


    public $auto;
    public $metter_type;
    public $unit;
    public $unit_name;
    public $q;

    public function init()
{
    parent::init();
    if ($this->isNewRecord) {
        // ตั้งค่าเริ่มต้นเป็น 1 เมื่อสร้างรายการใหม่
        $this->is_active = 1; 
    }
}

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stock_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['category_id', 'ref', 'data_json', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'default', 'value' => null],
            [['max_qty'], 'default', 'value' => 0.00],
            [['is_active'], 'default', 'value' => 0],
            [['item_code', 'item_name'], 'required'],
            [['is_asset', 'is_innovation', 'is_active', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['min_qty', 'max_qty'], 'number'],
            [['category_id', 'data_json', 'auto', 'metter_type', 'unit_name', 'unit', 'q'], 'safe'],
            [['item_code'], 'string', 'max' => 50],
            [['item_name', 'ref'], 'string', 'max' => 255],
            [['item_code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'item_code' => 'Item Code',
            'item_name' => 'Item Name',
            'category_id' => 'Category ID',
            'min_qty' => 'Min Qty',
            'max_qty' => 'Max Qty',
            'is_asset' => 'Is Asset',
            'is_innovation' => 'Is Innovation',
            'is_active' => 'Is Active',
            'ref' => 'Ref',
            'data_json' => 'Data Json',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    public function getStockItemItem()
    {
        return $this->hasOne(Categorise::class, ['code' => 'category_id'])->andOnCondition(['name' => 'asset_item']);
    }

    public function getStockItemType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'category_id'])->andOnCondition(['name' => 'asset_type']);
    }

    public function getCategoryType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'category_id'])->andOnCondition(['name' => 'asset_type']);
    }

    /**
     * ดึงหน่วยนับจาก data_json
     */
    public function getUnitName()
    {
        if ($this->data_json) {
            $dataJson = is_string($this->data_json) ? json_decode($this->data_json, true) : $this->data_json;
            return $dataJson['unit_name'] ?? null;
        }
        return null;
    }


    public function getStockBalance($warehouse_id)
{
    $balance = StockBalance::find()
        ->where(['item_code' => $this->item_code, 'warehouse_id' => $warehouse_id])
        ->sum('balance_qty');
        
    return $balance ?: 0;
}



    public function listAssetType()
    {
        $items = Categorise::find()->where(['category_id' => 4, 'name' => 'asset_type'])->all();
        return ArrayHelper::map($items, 'code', 'title');
    }


    public function listMatterType()
    {
        $items = [
            'วัสดุสิ้นเปลือง' => 'วัสดุสิ้นเปลือง',
            'วัสดคงทน' => 'วัสดคงทน',
        ];
        return $items;
    }

    public function listPurchaseType()
    {
        $items = [
            'ราคาสืบเขต' => 'ราคาสืบเขต',
            'ราคาสืบจังหวัด' => 'ราคาสืบจังหวัด',
            'ราคาจัดซื้อ รพ.' => 'ราคาจัดซื้อ รพ.',
        ];
        return $items;
    }

    //สร้างรหัสวัสดุ
    public static function nextCode($categoryId)
    {
        return Yii::$app->db->createCommand("
        SELECT  CONCAT(
        '$categoryId-', 
        IFNULL(
            MAX(CAST(SUBSTRING_INDEX(item_code, '-', -1) AS UNSIGNED)), 
            0
        ) + 1
    ) AS next_code
        FROM stock_item
        WHERE category_id = :category_id
          AND item_code LIKE :code_like
    ")->bindValues([
            ':category_id' => $categoryId,
            ':code_like' => $categoryId . '-%',
        ])->queryScalar();
    }




    public function ShowImg()
    {
        $model = Uploads::find()->where(['ref' => $this->ref])->one();
        if ($model) {
            return FileManagerHelper::getImg($model->id);
        } else {
            // return Yii::getAlias('@web') . '/img/placeholder-img.jpg';
            $filepath = Yii::getAlias('@webroot') . '/img/placeholder-img.jpg';
            $type = pathinfo($filepath, PATHINFO_EXTENSION);
            $data = file_get_contents($filepath);
            return $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
    }

    public function Avatar()
    {
        return '<div class="d-flex">
        ' . Html::img($this->ShowImg(), ['class' => 'avatar']) . '
                                <div class="avatar-detail">
                                    <h6 class="mb-1 fs-15" data-bs-toggle="tooltip" data-bs-placement="top">
                                        ' . $this->title . '
                                    </h6>
                                    <span class="text-primary">' . $this->code . '</span>
                                </div>
                            </div>';
    }

    //แสดงสำหรับ line
    public function AvatarLine($msg = null)
    {
        return '<div class="d-flex">
        ' . Html::img($this->ShowImg(), ['class' => 'avatar']) . '
                                <div class="avatar-detail">
                                    <h6 class="mb-1 fs-15" data-bs-toggle="tooltip" data-bs-placement="top">
                                        ' . $this->title . '
                                    </h6>
                                    <span class="text-primary">' . $msg . '</span>
                                </div>
                            </div>';
    }



    //แสดงรูปแบบประเภท
    public function ViewTypeName()
    {
        try {

            $model =  Categorise::find()->where(['name' => $this->name])->one();

            return [
                'title' =>  isset($this->stockItemType->title) ? $this->stockItemType->title : 'ไม่ได้ระบุ',
                'code' => (isset($model->data_json['unit']) ? $model->data_json['unit'] : '-')
            ];
        } catch (\Throwable $th) {
            return [
                'title' =>  '',
                'code' => ''
            ];
        }
    }

    public function AvatarXl()
    {
        return '<div class="d-flex">
                        ' . Html::img($this->ShowImg(), ['class' => 'avatar']) . '
                            <div class="avatar-detail">
                                <h5 class="mb-15" data-bs-toggle="tooltip" data-bs-placement="top">' . $this->title . '</h5>
                                    <p class="text-primary mb-0 fs-6">' . isset($this->stockItemType) ? $this->stockItemType->title : '-' . ' <code>(' . (isset($this->data_json['unit']) ? $this->data_json['unit'] : '-') . ')</code></p>
                            </div>
        </div>';
    }
    public static function ListStockItemType()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'asset_type', 'category_id' => [4]])->all(), 'code', 'title');
    }

    public function listUnit()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'unit'])->all(), 'title', 'title');
    }

    public function ListStockItemUnit()
    {
        return Categorise::find()->where(['category_id' => $this->id, 'name' => 'StockItem_unit'])->all();
    }
}
