<?php

namespace app\modules\inventoryV2\models;

use app\models\Categorise;
use app\models\Uploads;
use app\modules\filemanager\components\FileManagerHelper;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * StockItem — Phase 2 ของแผน "ยุบ stock_item ไปรวมกับ categorise(asset_item, MATER)"
 *
 * คลาสนี้ "ห่อ" ตาราง `categorise` (ที่ filter name='asset_item' AND group_id='MATER')
 * เพื่อให้ controller/view เดิมที่อ้าง $model->item_code, item_name, min_qty, max_qty, is_active
 * และ StockItem::find()/findOne(['item_code' => ...]) ยังคงทำงานได้โดยไม่ต้องแก้
 *
 * Column mapping:
 *   item_code      <-> code
 *   item_name      <-> title
 *   min_qty        <-> qty_min
 *   max_qty        <-> qty_max
 *   is_active      <-> active
 *   is_asset       <-> data_json.is_asset
 *   is_innovation  <-> data_json.is_innovation
 *   unit_name      <-> data_json.unit_name
 *
 * Note สำคัญ — surrogate `id`:
 *   ID ที่เก็บใน DB ตอนนี้คือ categorise.id (ไม่ใช่ stock_item.id เดิม)
 *   ถ้ามี code/URL ที่ใช้ stock_item.id เป็น hard-coded — จะ resolve ไม่เจอ
 *   ให้เปลี่ยนไปใช้ item_code (code) เป็น identifier แทน
 */
class StockItem extends \yii\db\ActiveRecord
{
    // virtual attrs ที่ใช้สำหรับ form load/safeAttributes
    public $auto;
    public $metter_type;
    public $unit;
    public $unit_name;
    public $q;

    /**
     * Map alias → real column (ใช้ทั้ง findOne, findAll, และ StockItemQuery)
     * ต้องตรงกับ StockItemQuery::$aliases
     */
    public const COLUMN_ALIASES = [
        'item_code' => 'code',
        'item_name' => 'title',
        'min_qty'   => 'qty_min',
        'max_qty'   => 'qty_max',
        'is_active' => 'active',
    ];

    /** ส่งคำสั่ง SELECT/UPDATE/INSERT ไปที่ตาราง categorise */
    public static function tableName()
    {
        return 'categorise';
    }

    /** ใช้ ActiveQuery custom ที่ inject default scope + translate alias */
    public static function find()
    {
        return Yii::createObject(StockItemQuery::class, [get_called_class()]);
    }

    /**
     * Override findOne — รองรับ pattern เดิม:
     *   StockItem::findOne($itemCode)            -> หาด้วย code
     *   StockItem::findOne(['item_code' => ...]) -> alias เป็น code (ก่อน parent validate กับ schema)
     *   StockItem::findOne(['id' => $id])        -> หาด้วย categorise.id ตามปกติ
     *   StockItem::findOne($intId)               -> หาด้วย categorise.id (PK)
     */
    public static function findOne($condition)
    {
        if (is_string($condition) && !is_numeric($condition)) {
            // string ที่ไม่ใช่ตัวเลข -> ถือเป็น item_code
            return static::find()->andWhere(['code' => $condition])->one();
        }
        if (is_array($condition)) {
            $condition = static::translateAliasKeys($condition);
        }
        return parent::findOne($condition);
    }

    /**
     * Override findAll เพื่อ translate alias เช่นเดียวกับ findOne
     */
    public static function findAll($condition)
    {
        if (is_array($condition)) {
            $condition = static::translateAliasKeys($condition);
        }
        return parent::findAll($condition);
    }

    /**
     * แปลง alias key ใน hash condition → real column
     * จำเป็นเพราะ parent::findByCondition() เรียก filterCondition() ที่ validate กับ table schema
     * ก่อนที่ andWhere จะถูกเรียก (StockItemQuery::translateCondition จึงไม่ทันช่วย)
     */
    public static function translateAliasKeys(array $condition): array
    {
        $out = [];
        foreach ($condition as $key => $value) {
            if (is_string($key) && isset(self::COLUMN_ALIASES[$key])) {
                $out[self::COLUMN_ALIASES[$key]] = $value;
            } else {
                $out[$key] = $value;
            }
        }
        return $out;
    }

    public function init()
    {
        parent::init();
        if ($this->isNewRecord) {
            $this->active = 1;
            $this->name = 'asset_item';
            $this->group_id = 'MATER';
        }
    }

    /**
     * Hooks ก่อน save — บังคับให้ทุก record อยู่ใน scope asset_item/MATER เสมอ
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->name = 'asset_item';
        if (empty($this->group_id)) {
            $this->group_id = 'MATER';
        }
        // ถ้า set unit_name ผ่าน public prop -> เขียนกลับเข้า data_json
        if ($this->unit_name !== null && $this->unit_name !== '') {
            $j = $this->parseDataJson();
            $j['unit_name'] = $this->unit_name;
            $this->data_json = $j;
        }
        return true;
    }

    public function rules()
    {
        return [
            [['code', 'title'], 'required'],
            [['code'], 'string', 'max' => 255],
            [['title'], 'string'],
            [['name', 'group_id', 'category_id', 'ref'], 'string', 'max' => 255],
            [['data_json', 'ma_items'], 'safe'],
            [['qty_min', 'qty_max', 'qty'], 'number'],
            [['active'], 'integer'],
            [['code'], 'unique', 'targetAttribute' => ['code', 'name', 'group_id']],
            // virtual / alias attrs — เปิดให้ form load ได้ (ผ่าน __set magic)
            [['item_code', 'item_name', 'min_qty', 'max_qty', 'is_active',
              'is_asset', 'is_innovation', 'auto', 'metter_type', 'unit_name', 'unit', 'q'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'รหัสพัสดุ',
            'title' => 'ชื่อพัสดุ',
            'category_id' => 'ประเภท',
            'qty_min' => 'จำนวนต่ำสุด',
            'qty_max' => 'จำนวนสูงสุด',
            'active' => 'สถานะ',
            // alias เพื่อให้ ActiveForm field labels เดิมยังโชว์ได้
            'item_code' => 'รหัสพัสดุ',
            'item_name' => 'ชื่อพัสดุ',
            'min_qty' => 'จำนวนต่ำสุด',
            'max_qty' => 'จำนวนสูงสุด',
            'is_active' => 'สถานะ',
            'is_asset' => 'เป็นครุภัณฑ์',
            'is_innovation' => 'บัญชีนวัตกรรม',
        ];
    }

    // ===================== Virtual attribute getters / setters =====================
    public function getItem_code() { return $this->code; }
    public function setItem_code($v) { $this->code = $v; }

    public function getItem_name() { return $this->title; }
    public function setItem_name($v) { $this->title = $v; }

    public function getMin_qty() { return $this->qty_min; }
    public function setMin_qty($v) { $this->qty_min = $v; }

    public function getMax_qty() { return $this->qty_max; }
    public function setMax_qty($v) { $this->qty_max = $v; }

    public function getIs_active() { return (int) $this->active; }
    public function setIs_active($v) { $this->active = (int) $v; }

    public function getIs_asset()
    {
        $j = $this->parseDataJson();
        return (int) ($j['is_asset'] ?? 0);
    }
    public function setIs_asset($v)
    {
        $j = $this->parseDataJson();
        $j['is_asset'] = (int) $v;
        $this->data_json = $j;
    }

    public function getIs_innovation()
    {
        $j = $this->parseDataJson();
        return (int) ($j['is_innovation'] ?? 0);
    }
    public function setIs_innovation($v)
    {
        $j = $this->parseDataJson();
        $j['is_innovation'] = (int) $v;
        $this->data_json = $j;
    }

    private function parseDataJson()
    {
        if (is_array($this->data_json)) return $this->data_json;
        if (is_string($this->data_json) && $this->data_json !== '') {
            $d = json_decode($this->data_json, true);
            if (is_string($d)) {
                $d2 = json_decode($d, true);
                return is_array($d2) ? $d2 : [];
            }
            return is_array($d) ? $d : [];
        }
        return [];
    }

    // ===================== Relations =====================
    public function getStockItemItem()
    {
        return $this->hasOne(Categorise::class, ['code' => 'category_id'])
                    ->andOnCondition(['name' => 'asset_item']);
    }

    public function getStockItemType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'category_id'])
                    ->andOnCondition(['name' => 'asset_type']);
    }

    public function getCategoryType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'category_id'])
                    ->andOnCondition(['name' => 'asset_type']);
    }

    /** ดึงหน่วยนับจาก data_json */
    public function getUnitName()
    {
        $j = $this->parseDataJson();
        return $j['unit_name'] ?? null;
    }

    public function getStockBalance($warehouse_id)
    {
        $balance = StockBalance::find()
            ->where(['item_code' => $this->code, 'warehouse_id' => $warehouse_id])
            ->sum('balance_qty');
        return $balance ?: 0;
    }

    // ===================== List helpers =====================
    public function listAssetType()
    {
        $items = Categorise::find()->where(['category_id' => 4, 'name' => 'asset_type'])->all();
        return ArrayHelper::map($items, 'code', 'title');
    }

    public function listMatterType()
    {
        return [
            'วัสดุสิ้นเปลือง' => 'วัสดุสิ้นเปลือง',
            'วัสดคงทน' => 'วัสดคงทน',
        ];
    }

    public function listPurchaseType()
    {
        return [
            'ราคาสืบเขต' => 'ราคาสืบเขต',
            'ราคาสืบจังหวัด' => 'ราคาสืบจังหวัด',
            'ราคาจัดซื้อ รพ.' => 'ราคาจัดซื้อ รพ.',
        ];
    }

    /**
     * สร้างรหัสพัสดุถัดไปสำหรับ category — query จาก categorise(asset_item, MATER)
     * รูปแบบเดิม: {categoryId}-{seq}
     */
    public static function nextCode($categoryId)
    {
        return Yii::$app->db->createCommand("
            SELECT CONCAT(
                :prefix,
                IFNULL(
                    MAX(CAST(SUBSTRING_INDEX(code, '-', -1) AS UNSIGNED)),
                    0
                ) + 1
            ) AS next_code
            FROM categorise
            WHERE name = 'asset_item'
              AND group_id = 'MATER'
              AND category_id = :category_id
              AND code LIKE :code_like
        ")->bindValues([
            ':prefix' => $categoryId . '-',
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
            $filepath = Yii::getAlias('@webroot') . '/img/placeholder-img.jpg';
            $type = pathinfo($filepath, PATHINFO_EXTENSION);
            $data = file_get_contents($filepath);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
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

    public function ViewTypeName()
    {
        try {
            return [
                'title' => isset($this->stockItemType->title) ? $this->stockItemType->title : 'ไม่ได้ระบุ',
                'code'  => (isset($this->data_json['unit']) ? $this->data_json['unit'] : '-'),
            ];
        } catch (\Throwable $th) {
            return ['title' => '', 'code' => ''];
        }
    }

    public function AvatarXl()
    {
        return '<div class="d-flex">
                        ' . Html::img($this->ShowImg(), ['class' => 'avatar']) . '
                            <div class="avatar-detail">
                                <h5 class="mb-15" data-bs-toggle="tooltip" data-bs-placement="top">' . $this->title . '</h5>
                                    <p class="text-primary mb-0 fs-6">' . (isset($this->stockItemType) ? $this->stockItemType->title : '-') . ' <code>(' . (isset($this->data_json['unit']) ? $this->data_json['unit'] : '-') . ')</code></p>
                            </div>
        </div>';
    }

    public static function ListStockItemType()
    {
        return ArrayHelper::map(
            Categorise::find()->where(['name' => 'asset_type', 'category_id' => [4]])->all(),
            'code',
            'title'
        );
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
