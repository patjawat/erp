<?php

namespace app\modules\am\models;

use Yii;
use yii\helpers\Html;
use yii\db\Expression;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use app\components\ThaiDateHelper;
use app\components\CategoriseHelper;
use app\modules\am\models\AssetItem;
use app\modules\hr\models\Employees;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\modules\hr\models\Organization;
use app\modules\filemanager\models\Uploads;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\am\services\AssetNumberGenerator;
use app\modules\am\models\AssetDetail;

/**
 * This is the model class for table "asset".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $name ชื่อครุภัณฑ์
 * @property string|null $fsn หมายเลขครุภัณฑ์
 * @property string|null $receive_date วันที่รับเข้า
 * @property float|null $price ราคา
 * @property int|null $life อายุการใช้งาน
 * @property int|null $department ประจำอยู่หน่วยงาน
 * @property int|null $depre_type ประเภทค่าเสื่อมราคา
 * @property int|null $budget_type งบประมาณ
 * @property string|null $data_json
 * @property string|null $updated_at วันเวลาแก้ไข
 * @property string|null $created_at วันเวลาสร้าง
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property int|null $on_year ปีงบประมาณ
 * @property int|null $supvendor ผู้แทนจำหน่าย
 * @property int|null $method_get วิธีได้มา
 * @property int|null $purchase การจัดซื้อ
 * @property int|null $budget_type ประเภทเงิน
 * @property int|null $asset_option รายละเอียดยี่ห้อครุภัณฑ์
 * @property int|null $type_name ชื่อประเภทครุภัณฑ์
 * @property int|null $vendor_name ผู้ขาย/ผู้จำหน่าย/ผู้บริจาค
 * @property int|null $purchase_text การได้มา
 * @property int|null $useful_life อายุการใช้งาน (ปี)
 * @property float|null $residual_value มูลค่าซาก
 * @property float|null $depreciation_rate อัตราค่าเสื่อม (% ต่อปี, ทศนิยม 2 ตำแหน่ง)
 * @property string|null $depreciation_method วิธีคำนวณค่าเสื่อม
 * @property string|null $gfmis รหัสโครงสร้างงบประมาณ(GFMIS)
 * @property string|null $lifecycle_status received|active|repair|disposed
 * @property string|null $qr_code_path Path to saved QR image
 */
class Asset extends \yii\db\ActiveRecord
{
    const LIFECYCLE_RECEIVED = 'received';
    const LIFECYCLE_ACTIVE = 'active';
    const LIFECYCLE_REPAIR = 'repair';
    const LIFECYCLE_DISPOSED = 'disposed';
    /**
     * {@inheritdoc}
     */
    public $show;

    // public $asset_name;
    public $budget_year;
    public $supvendor;
    public $method_get;
    public $po_number;
    public $budget_type;
    public $asset_option;
    public $serial_number;
    public $type_name;
    public $vendor_name;
    public $budget_type_name;
    public $department_name;
    public $purchase_text;
    public $asset_type;
    public $q;
    public $price1;
    public $price2;
    public $q_department;
    public $q_date;
    public $q_receive_date;
    public $q_month;
    public $q_year;
    public $q_lastDay;
    public $item_options;
    public $fsn_auto;  // กำหนดการให้หมายเลขอัตโนมัติถ้า true;

    public static function tableName()
    {
        return 'asset';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // useful_life ไม่ required แล้ว — ถูกล็อกจากหมวดทรัพย์สินใน beforeSave (หลัง validation)
            [['price', 'asset_status','asset_condition'], 'required'],
            [['q_department', 'asset_group_id', 'asset_type_id', 'asset_category_id', 'deleted_at', 'deleted_by', 'on_year', 'receive_date', 'data_json', 'device_items', 'updated_at', 'created_at', 'asset_name', 'asset_item_id', 'fsn_number', 'code', 'gfmis', 'qty', 'fsn_auto', 'type_name', 'show', 'asset_group_id', 'asset_type', 'q', 'budget_type', 'purchase', 'owner', 'price1', 'price2', 'q_date', 'q_receive_date', 'q_month', 'q_year', 'department_name', 'asset_option', 'method_get', 'po_number', 'q_lastDay', 'item_options', 'group_id', 'license_plate', 'car_type', 'depreciation_rate', 'depreciation_method', 'lifecycle_status', 'qr_code_path','asset_kind','risk_level'], 'safe'],
            [['risk_level'], 'in', 'range' => ['H', 'M', 'L', null], 'strict' => false],
            [['price', 'residual_value', 'depreciation_rate'], 'number'],
            [['code'], 'unique'],
            [['department', 'depre_type', 'created_by', 'updated_by', 'useful_life'], 'integer'],
            [['ref', 'code'], 'string', 'max' => 255],
            [['gfmis'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ref' => 'Ref',
            'name' => 'ชื่อครุภัณฑ์',
            'fsn' => 'ครุภัณฑ์',
            'asset_item_id' => 'ชื่อครุภัณฑ์',
            'fsn_number' => 'หมายเลขครุภัณฑ์',
            'gfmis' => 'รหัสโครงสร้างงบประมาณ(GFMIS)',
            'receive_date' => 'วันที่รับเข้า',
            'on_year' => 'ปีงบประมาณ',
            'price' => 'ราคา',
            'life' => 'อายุการใช้งาน',
            'department' => 'ประจำอยู่หน่วยงาน',
            'depre_type' => 'ประเภทค่าเสื่อมราคา',
            'budget_year' => 'งบประมาณ',
            'asset_status' => 'สถานะ',
            'license_plate' => 'เลขทะเบียนรถ',
            'car_type' => 'ประเภทการใช้งานรถยนต์',
            'useful_life' => 'อายุการใช้งาน (ปี)',
            'residual_value' => 'มูลค่าซาก',
            'depreciation_rate' => 'อัตราค่าเสื่อม (%)',
            'depreciation_method' => 'วิธีคำนวณค่าเสื่อม',
            'data_json' => 'Data Json',
            'updated_at' => 'วันเวลาแก้ไข',
            'created_at' => 'วันเวลาสร้าง',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'lifecycle_status' => 'สถานะวงจรชีวิต',
            'qr_code_path' => 'QR Code',
            'asset_kind' => 'ประเภททรัพย์สิน',
            'risk_level' => 'ระดับความเสี่ยง',
            'asset_condition' => 'สภาพครุภัณฑ์',
        ];
    }

    /** ประวัติวงจรชีวิต (โอนย้าย/ซ่อม/จำหน่าย เก็บใน asset_detail name=lifecycle) */
    public function getTransactions()
    {
        return $this->hasMany(AssetDetail::class, ['asset_id' => 'id'])
            ->andWhere(['name' => AssetDetail::NAME_LIFECYCLE])
            ->orderBy(['created_at' => SORT_DESC]);
    }

    public function getRepairTransactions()
    {
        return $this->hasMany(AssetDetail::class, ['asset_id' => 'id'])
            ->andWhere(['name' => AssetDetail::NAME_LIFECYCLE])
            ->andWhere("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.transaction_type')) IN ('REPAIR', 'RETURN')")
            ->orderBy(['created_at' => SORT_DESC]);
    }

    /**
     * รายการ "ประเภท" ตามกลุ่มทรัพย์สิน — EQUIP=ครุภัณฑ์, STRUCT=สิ่งปลูกสร้าง ฯลฯ
     * (ใช้ร่วมกันระหว่างฟอร์มครุภัณฑ์และสิ่งปลูกสร้าง ต่างกันแค่ group_id)
     */
    public function listAssetType($group = 'EQUIP')
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'asset_type', 'group_id' => $group])->all(), 'code', 'title');
    }

    public function listAssetCategory()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'asset_category'])->all(), 'code', 'title');
    }

    public function ListAssetCondition()
    {
        return ArrayHelper::map(
            AssetCondition::find()
                ->where(['is_active' => 1])
                ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])
                ->all(),
            'id',
            'name'
        );
    }




    public function departmentName()
    {
        $department = Organization::findOne(['id' => $this->department]);
        if ($department) {
            return $department->name;
        } else {
            return $this->department;
        }
    }

    // อัปเดตรหัส FSN ลง AssetItem เพื่อให้ /am/asset-item/list-item แสดงรหัสล่าสุด และเมื่อเลือกรายการเดิมจะดึง FSN มาใช้ได้
    public function updateFsn()
    {
        if (empty($this->asset_item_id)) {
            return;
        }
        $value = trim((string) ($this->code ?? $this->fsn_number ?? ''));
        if ($value === '') {
            return;
        }
        $assetItem = AssetItem::find()->where(['id' => $this->asset_item_id])->one();
        if ($assetItem !== null) {
            $assetItem->fsn = $value;
            $assetItem->save(false);
        }
    }

    //แสดงรูปภาพแบบวงกลม
    public function Avatar()
    {
        return '<div class="d-flex">
        ' . Html::img($this->ShowImg()['image'], ['class' => 'avatar border border-secondary']) . '
                                <div class="avatar-detail">
                                    <h6 class="mb-1 fs-15" data-bs-toggle="tooltip" data-bs-placement="top">
                                        ' . $this->AssetitemName() . '
                                    </h6>
                                    <p class="text-primary mb-0 fs-13">' . $this->code . '</p>
                                </div>
                            </div>';
    }

    // แสดงรูปภาพ
    public function ShowImg()
    {
        try {
            $model = Uploads::find()->where(['ref' => $this->ref, 'name' => 'asset'])->one();
            if ($model) {
                return [
                    'image' => FileManagerHelper::getImg($model->id),
                    'isFile' => true,
                ];
            } else {
                return [
                    'image' => Yii::getAlias('@web') . '/img/placeholder-img.jpg',
                    'isFile' => false,
                ];
            }
        } catch (\Throwable $th) {
            return [
                'image' => '',
                'isFile' => false,
            ];
        }
    }

    /**
     * Calculate annual depreciation using model's residual_value (for backward compatibility).
     * For Thai government accounting standard (residual = 1 baht), use AssetDepreciationService::getAnnualDepreciationForAsset() or generateDepreciationSchedule().
     * Straight line: (purchase_price - residual_value) / useful_life.
     * @return float|null Annual depreciation amount or null
     */
    public function calculateDepreciation()
    {
        $price = (float) $this->price;
        $residual = $this->residual_value !== null && $this->residual_value !== '' ? (float) $this->residual_value : 0;
        $years = $this->useful_life ? (int) $this->useful_life : null;

        if ($years === null || $years <= 0) {
            return null;
        }

        $method = $this->depreciation_method ?? 'straight_line';
        if ($method === 'straight_line') {
            return ($price - $residual) / $years;
        }
        // Extensible for declining_balance etc. later
        return ($price - $residual) / $years;
    }

    public function behaviors()
    {
        return [
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => ['updated_at'],
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function afterFind()
    {
        try {
            // $this->receive_date = AppHelper::DateFormDb($this->receive_date);
            $this->budget_year = isset($this->data_json['budget_year_text']) ? $this->data_json['budget_year_text'] : null;
            $this->supvendor = isset($this->data_json['supvendor_text']) ? $this->data_json['supvendor_text'] : null;
            $this->method_get = isset($this->data_json['method_get_text']) ? $this->data_json['method_get_text'] : null;
            $this->budget_type = isset($this->data_json['budget_type_text']) ? $this->data_json['budget_type_text'] : null;
            $this->asset_option = isset($this->data_json['asset_option']) ? $this->data_json['asset_option'] : null;
            // $this->asset_name = isset($this->data_json['asset_name_text']) ? $this->data_json['asset_name_text'] : '';
            $this->serial_number = isset($this->data_json['serial_number']) ? $this->data_json['serial_number'] : '';
            $this->type_name = isset($this->data_json['item']['data_json']['asset_type']['title']) ? $this->data_json['item']['data_json']['asset_type']['title'] : '';
            $this->vendor_name = isset($this->data_json['vendor']) ? $this->data_json['vendor']['title'] : '';
            $this->asset_type = isset($this->data_json['asset_type']) ? $this->data_json['asset_type'] : '';
            $this->purchase_text = isset($this->data_json['purchase_text']) ? $this->data_json['purchase_text'] : '';
            $this->department_name = isset($this->data_json['department_name']) ? $this->data_json['department_name'] : '';
            if ($this->hasAttribute('depreciation_rate')) {
                $dr = $this->getAttribute('depreciation_rate');
                if (($dr === null || $dr === '') && isset($this->data_json['depreciation'])) {
                    $raw = $this->data_json['depreciation'];
                    if ($raw !== null && $raw !== '') {
                        $normalized = str_replace(',', '.', preg_replace('/\s+/u', '', (string) $raw));
                        if ($normalized !== '' && is_numeric($normalized)) {
                            $fv = round((float) $normalized, 2);
                            if ($fv >= 0) {
                                $this->depreciation_rate = $fv;
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $th) {
            // throw $th;
        }

        // $this->asset_name = isset($this->data_json['name']) ? $this->data_json['name'] : '-';

        parent::afterFind();
    }

    public function beforeSave($insert)
    {
        // flow ทะเบียนครุภัณฑ์: ล็อกค่าเสื่อมจากหมวด + fsn_number เป็นหมายเลขเต็ม (code = mirror)
        $this->applyEquipCategoryRules();

        try {
            if ($this->asset_group_id == 2) {
                // try {

                $vendor = isset($this->data_json['vendor_id']) ? Categorise::find()->where(['code' => $this->data_json['vendor_id'], 'name' => 'vendor'])->one() : '';
                $department = Organization::find()->where(['id' => $this->department])->one();
                $array2 = [
                    'vendor_id' => isset($this->data_json['vendor_id']) ? $this->data_json['vendor_id'] : '',
                    'vendor' => $vendor,
                    'department_name' => isset($department) ? $department->name : '',
                    'budget_type_text' => isset(CategoriseHelper::CategoriseByCodeName($this->data_json['budget_type'], 'budget_type')->title) ? CategoriseHelper::CategoriseByCodeName($this->data_json['budget_type'], 'budget_type')->title : '',
                    'method_get_text' => isset(CategoriseHelper::CategoriseByCodeName($this->data_json['method_get'], 'method_get')->title) ? CategoriseHelper::CategoriseByCodeName($this->data_json['method_get'], 'method_get')->title : '',
                    'purchase_text' => isset(CategoriseHelper::CategoriseByCodeName($this->purchase, 'purchase')->title) ? CategoriseHelper::CategoriseByCodeName($this->purchase, 'purchase')->title : '',
                ];
                $this->data_json = ArrayHelper::merge($this->data_json, $array2);
                // code...
            }

            if ($this->asset_group_id == 3) {
                // try {

                $vendor = isset($this->data_json['vendor_id']) ? Categorise::find()->where(['code' => $this->data_json['vendor_id'], 'name' => 'vendor'])->one() : '';
                $Assetitem = AssetItem::find()->where(['code' => $this->asset_item_id, 'name' => 'asset_item_id'])->one();
                $department = Organization::find()->where(['id' => $this->department])->one();
                $array2 = [
                    'vendor_id' => isset($this->data_json['vendor_id']) ? $this->data_json['vendor_id'] : '',
                    'vendor' => $vendor,
                    'item' => $Assetitem,
                    'department_name' => isset($department) ? $department->name : '',
                    'asset_name' => $Assetitem->title,
                    'asset_type_text' => $Assetitem->assetType->title,
                    'service_life' => $Assetitem->assetType->data_json['service_life'],
                    'depreciation' => $Assetitem->assetType->data_json['depreciation'],
                    'budget_type_text' => isset(CategoriseHelper::CategoriseByCodeName($this->data_json['budget_type'], 'budget_type')->title) ? CategoriseHelper::CategoriseByCodeName($this->data_json['budget_type'], 'budget_type')->title : '',
                    'method_get_text' => isset(CategoriseHelper::CategoriseByCodeName($this->data_json['method_get'], 'method_get')->title) ? CategoriseHelper::CategoriseByCodeName($this->data_json['method_get'], 'method_get')->title : '',
                    'purchase_text' => isset(CategoriseHelper::CategoriseByCodeName($this->purchase, 'purchase')->title) ? CategoriseHelper::CategoriseByCodeName($this->purchase, 'purchase')->title : '',
                ];
                $this->data_json = ArrayHelper::merge($this->data_json, $array2);

                // Auto-fill asset number (code) when empty – do not overwrite existing
                $categoryForCode = $this->asset_item_id ?: $this->fsn_number;
                if ((trim((string) $this->code) === '') && $categoryForCode !== '' && $categoryForCode !== null) {
                    $this->code = AssetNumberGenerator::generate($categoryForCode);
                }
                // Legacy: สร้างรหัสอัตโนมัติ when fsn_auto and code still empty
                if ($this->fsn_auto == '1' && trim((string) $this->code) === '' && $this->asset_item_id) {
                    $year = substr(AppHelper::YearBudget(), -2, 2);
                    $number = $this->asset_item_id . '/' . $year . '.';
                    $this->code = \mdm\autonumber\AutoNumber::generate($number . '?');
                }

                // } catch (\Throwable $th) {
                //     //throw $th;
                // }
            }
        } catch (\Throwable $th) {
            // throw $th;
        }

        if ($this->hasAttribute('depreciation_rate') && ($this->asset_group_id == 4 || $this->asset_group_id === '4')) {
            $this->syncDepreciationRateToDataJson();
        }

        if ($this->lifecycle_status === null || $this->lifecycle_status === '') {
            $this->lifecycle_status = $insert ? self::LIFECYCLE_RECEIVED : self::LIFECYCLE_ACTIVE;
        }
        return parent::beforeSave($insert);
    }

    /**
     * ให้ data_json.depreciation สอดคล้องกับคอลัมน์ depreciation_rate (รายงาน/legacy ที่อ่านจาก JSON)
     */
    protected function syncDepreciationRateToDataJson(): void
    {
        $dj = is_array($this->data_json) ? $this->data_json : [];
        $rate = $this->depreciation_rate;
        if ($rate === null || $rate === '') {
            unset($dj['depreciation']);
            $this->data_json = $dj;

            return;
        }
        $v = round((float) $rate, 2);
        $this->depreciation_rate = $v;
        $dj['depreciation'] = $v;
        $this->data_json = $dj;
    }

    /**
     * กติกาของ flow ทะเบียนครุภัณฑ์ที่ผูกกับ "หมวดทรัพย์สิน" (asset_category_id = categorise.code = FSN prefix):
     *
     * 1) ค่าเสื่อม (useful_life / depreciation_rate) — หมวดเป็นแหล่งเดียว "ล็อก" override ค่าที่ผู้ใช้ส่งมาเสมอ
     * 2) หมายเลขครุภัณฑ์ — `fsn_number` เก็บหมายเลขเต็ม, `code` เป็น mirror ของ `fsn_number`
     *    (QR / asset_detail.code / isCar / sequence generator ยังอ้าง code ได้เหมือนเดิม)
     *
     * รองรับช่วงเปลี่ยนผ่านฟอร์มเก่า: ฟอร์มเดิมส่งหมายเลขเต็มมาที่ code และส่ง prefix มาที่ fsn_number
     * ถ้ายังไม่มีหมายเลขเต็มเลย จะ generate จาก prefix + ปีงบประมาณ (ทำเฉพาะตอน save จริง ไม่กิน sequence ตอน ajax validate)
     */
    protected function applyEquipCategoryRules(): void
    {
        $prefix = trim((string) $this->asset_category_id);
        if ($prefix === '') {
            return; // ไม่ใช่ flow ที่ผูกกับหมวดทรัพย์สิน (เช่น group 2/3 legacy) — ไม่ยุ่ง
        }

        // 1) ค่าเสื่อมจากหมวด (หมวดเป็นแหล่งเดียว) — override เฉพาะเมื่อหมวดกำหนดค่าไว้
        //    ถ้าหมวดยังไม่กำหนด (NULL) คงค่าเดิมของ asset ไว้ ไม่ลบทิ้ง (กันข้อมูลค่าเสื่อมเดิมหาย)
        $category = AssetCategory::find()
            ->where(['name' => 'asset_category', 'code' => $prefix])
            ->one();
        if ($category !== null) {
            if ($category->useful_life !== null && $category->useful_life !== '') {
                $this->useful_life = $category->useful_life;
            }
            if ($category->depreciation_rate !== null && $category->depreciation_rate !== '') {
                $this->depreciation_rate = $category->depreciation_rate;
            }
        }

        // 2) หมายเลขครุภัณฑ์เต็ม + mirror
        $looksFull = static function ($s): bool {
            $s = trim((string) $s);
            return $s !== '' && strpos($s, '/') !== false;
        };

        if ($looksFull($this->fsn_number)) {
            $number = trim((string) $this->fsn_number);      // ฟอร์มใหม่: เลขเต็มอยู่ที่ fsn_number แล้ว
        } elseif ($looksFull($this->code)) {
            $number = trim((string) $this->code);            // ฟอร์มเก่า: เลขเต็มอยู่ที่ code
        } else {
            $number = AssetNumberGenerator::generate($prefix, $this->on_year); // ยังไม่มี → สร้างใหม่
        }

        if ($number !== '') {
            $this->fsn_number = $number;
            $this->code = $number;
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $code = trim((string) ($this->code ?? ''));
        if ($code !== '' && ($this->qr_code_path === null || $this->qr_code_path === '')) {
            $path = $this->generateQrCodeFile();
            if ($path !== null) {
                static::updateAll(['qr_code_path' => $path], ['id' => $this->id]);
            }
        }
        if ($insert && $this->hasAttribute('lifecycle_status')) {
            try {
                $schema = Yii::$app->db->getTableSchema(AssetDetail::tableName(), true);
                if ($schema !== null && $schema->getColumn('asset_id') !== null) {
                    $d = new AssetDetail();
                    $d->name = AssetDetail::NAME_LIFECYCLE;
                    $d->asset_id = $this->id;
                    $d->code = $this->code;
                    $d->data_json = [
                        'transaction_type' => AssetDetail::TYPE_RECEIVE,
                        'to_location' => isset($this->data_json['location']) ? (string) $this->data_json['location'] : null,
                        'to_department' => $this->department,
                        'remark' => 'รับเข้า',
                    ];
                    $d->save(false);
                }
            } catch (\Throwable $e) {
                // ignore if table/column not available
            }
        }
    }

    /**
     * Generate QR image file (asset number). Saves under web/uploads/asset-qr. Returns web path or null.
     */
    public function generateQrCodeFile()
    {
        $code = trim((string) ($this->code ?? ''));
        if ($code === '') {
            return null;
        }
        $dir = Yii::getAlias('@webroot/uploads/asset-qr');
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $safe = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $code);
        $filename = $this->id . '_' . $safe . '.png';
        $fullPath = $dir . '/' . $filename;
        try {
            $result = Builder::create()
                ->writer(new PngWriter())
                ->data($code)
                ->size(200)
                ->build();
            $png = $result->getString();
            if ($png !== '' && $png !== false) {
                file_put_contents($fullPath, $png);
                return '/uploads/asset-qr/' . $filename;
            }
        } catch (\Throwable $e) {
            // skip on error
        }
        return null;
    }

    public function landSize()
    {
        $sizes = [
            ($this->data_json['land_size'] ?? 0) > 0 ? $this->data_json['land_size'] . ' ไร่' : null,
            ($this->data_json['land_size_ngan'] ?? 0) > 0 ? $this->data_json['land_size_ngan'] . ' งาน' : null,
            ($this->data_json['land_size_tarangwa'] ?? 0) > 0 ? $this->data_json['land_size_tarangwa'] . ' ตารางวา' : null,
        ];

        return implode('', array_filter($sizes));
    }



    public function budgetTypeName()
    {
        try {
            return CategoriseHelper::CategoriseByCodeName($this->data_json['budget_type'], 'budget_type')->title;
        } catch (\Throwable $th) {
            return '-';
        }
    }

    /** วิธีการได้มา (ชื่อจาก categorise name=method_get) */
    public function methodGetName()
    {
        try {
            $cat = CategoriseHelper::CategoriseByCodeName($this->data_json['method_get'] ?? null, 'method_get');
            return $cat ? $cat->title : '-';
        } catch (\Throwable $th) {
            return '-';
        }
    }

       public function getAssetStatus()
    {
        return $this->hasOne(AssetStatus::class, ['id' => 'asset_status']);
    }

    /**
     * Relation สำหรับดึงข้อมูลจากตาราง asset_condition
     */
    public function getAssetCondition()
    {
        return $this->hasOne(AssetCondition::class, ['id' => 'asset_condition']);
    }

    public function getAssetGroup()
    {
        return $this->hasOne(AssetGroup::class, ['code' => 'asset_group_id'])->andOnCondition(['name' => 'asset_group']);
    }
    public function getAssetType()
    {
        return $this->hasOne(AssetType::class, ['code' => 'asset_type_id'])->andOnCondition(['name' => 'asset_type']);;
    }

    public function getAssetCategory()
    {
        return $this->hasOne(AssetCategory::class, ['code' => 'asset_category_id'])->andOnCondition(['name' => 'asset_category']);;
    }

    /**
     * พนักงานผู้รับผิดชอบ (asset.owner เก็บค่า cid ตรงกับ employees.cid)
     */
    public function getOwnerEmployee()
    {
        return $this->hasOne(Employees::class, ['cid' => 'owner']);
    }

    // วิธีการได้มา
    public function getPurchaseName()
    {
        return $this->hasOne(Categorise::class, ['code' => 'purchase'])->andOnCondition(['name' => 'purchase']);
    }


    public function Upload($options = [])
    {
        // เช็กว่ามีการส่ง 'view' => true มาหรือไม่ ถ้าไม่มีให้ default เป็น false
        $view = isset($options['view']) ? $options['view'] : false;
        return FileManagerHelper::FileUpload($this->ref, 'asset', $view);
    }

    public function listShowImage()
    {
        return FileManagerHelper::listViewImages($this->ref);
    }


    // มูลค่าทรัพย์สินทั้งหมด
    public function TotalPrice()
    {
        return self::find()->select('price')->sum();
    }

    // มูลค่าครุภัณฑ์
    public function TotalPriceByGroup2()
    {
        return self::find()->select('price')->where(['asset_group_id' => 2])->sum();
    }

    // นับจำนวนรายการที่อยู่ในประเภท
    public function CountItemOnType()
    {
        $id = $this->code;
        $sql = "SELECT count(c.id) FROM categorise c
          LEFT JOIN categorise t ON t.code = c.category_id
           WHERE c.name = 'asset_item_id'
           AND t.category_id = :id";
        $query = Yii::$app
            ->db
            ->createCommand($sql)
            ->bindParam(':id', $id)
            ->queryScalar();
        return $query;
    }

    // ดึงข้อมูลครุภัรฑ์
    public function AssetitemName()
    {
        return isset($this->data_json['asset_name']) ? $this->data_json['asset_name'] : null;
    }

    // ดึงข้อประเภท
    public function AssetTypeName()
    {
        return isset($this->data_json['asset_type_text']) ? $this->data_json['asset_type_text'] : null;
    }

    public function statusName()
    {
       return $this->assetStatus->name ?? '-';
    }

    public function vendorName()
    {
        $model = CategoriseHelper::CategoriseByCodeName($this->data_json['vendor_id'], 'vendor');
        if ($model) {
            return $model->title;
        }
    }

    public function viewReceiveDate()
    {
        if ($this->receive_date) {
            return ThaiDateHelper::formatThaiDate($this->receive_date);
        } else {
            return '-';
        }
    }


    public function viewStatus()
    {
        $status = $this->asset_status;
        // $data = ['icon' => '','color' => ''];
        switch ($status) {
            case $status == 1:
                $data = ['icon' => '', 'color' => 'success'];
                break;
            case $status == 2:
                $data = ['icon' => '<i class="fa-solid fa-circle-xmark"></i>', 'color' => 'secondary'];
                break;
            case $status == 3:
                $data = ['icon' => '<i class="fa-regular fa-circle-pause"></i>', 'color' => 'danger'];
                break;
            case $status == 4:
                $data = ['icon' => '<i class="fa-solid fa-right-left"></i>', 'color' => 'info'];
                break;
            case $status == 5:
                $data = ['icon' => '<i class="fa-solid fa-triangle-exclamation"></i>', 'color' => 'warning'];
                break;

            default:
                $data = ['icon' => '', 'color' => ''];
                break;
        }

        return '<span class="badge bg-' . $data['color'] . ' bg-opacity-10 text-success border border-' . $data['color'] . '-subtle rounded-pill fw-medium px-2 py-1">' . $this->statusName() . '</span>';
    }

    /** สร้าง QR Code (data URI) สำหรับแสดงใน img - ใช้ endroid/qr-code */
    public function QrCode()
    {
        $code = trim((string) ($this->code ?? ''));
        if ($code === '') {
            return null;
        }
        try {
            $result = Builder::create()
                ->writer(new PngWriter())
                ->data($code)
                ->size(200)
                ->build();
            return $result->getDataUri();
        } catch (\Throwable $e) {
            return null;
        }
    }
    // หน่วยงาน
    public function ListDepartment()
    {
        return CategoriseHelper::Department();
    }

    // ผู้รับผิดชอบ
    public function ListEmployees()
    {
        //   return ArrayHelper::map(Employees::find()->all(),'cid','fname');
        return ArrayHelper::map(Employees::find()->all(), 'cid', function ($model) {
            return $model->prefix . $model->fname . ' ' . $model->lname;
        });
    }

    //หน่วยนับ
    public function listUnit()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'unit'])->all(), 'title', 'title');
    }

    //ยี่ห้อ
    public function listBand()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'band'])->all(), 'title', 'title');
    }
    //ยี่ห้อ
    public function listModel()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'model'])->all(), 'title', 'title');
    }
    //สี
    public function listColor()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'color'])->all(), 'title', 'title');
    }

    public function ListType()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'asset_type'])->all(), 'code', 'title');
    }

    public function ListAssetitem()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'asset_item_id', 'group_id' => 3])->all(), 'code', 'title');
    }

    // แสดงรายการอาคารสิ่งก่อสร้าง
    public function ListBuildingItems()
    {
        return ArrayHelper::map(Categorise::find()
            ->where(['name' => 'asset_item_id'])
            ->andWhere(['category_id' => 1])
            ->all(), 'code', 'title');
    }

    public function ListVendor()
    {
        return CategoriseHelper::Vendor();
    }

    public function ListMethodget()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'method_get'])->all(), 'code', 'title');
    }

    public function ListPurchase()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'purchase'])->all(), 'code', 'title');
    }

    // รายการยี่ห้อ
    public function ListBrand()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'brand'])->all(), 'code', 'title');
    }

    // รายการรุ่น
    public function ListAssetModel()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'asset_model'])->all(), 'code', 'title');
    }

    // รายการระบบปฏิบัติการ
    public function ListOs()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'os'])->all(), 'code', 'title');
    }

    // รายการ CPU
    public function ListCpu()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'cpu'])->all(), 'code', 'title');
    }

    public function ListOnYear()
    {
        // $sql = 'SELECT on_year FROM `asset` WHERE on_year IS NOT NULL GROUP BY on_year DESC';
        $sql = 'SELECT on_year FROM `asset` WHERE on_year IS NOT NULL GROUP BY on_year ORDER BY on_year DESC';
        $query = Yii::$app->db->createCommand($sql)->queryAll();
        return ArrayHelper::map($query, 'on_year', 'on_year');
    }

    public function ListBudgetdetail()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'budget_type'])->all(), 'code', 'title');
    }

    /** หมวดสิ่งปลูกสร้าง (level 1) — ใช้ใน seg-control */
    public function ListStructureGroup()
    {
        return ArrayHelper::map(
            Categorise::find()
                ->where(['name' => 'structure_type', 'category_id' => null, 'active' => 1])
                ->orderBy(['id' => SORT_ASC])
                ->all(),
            'code',
            'title'
        );
    }

    /** รายการ leaf ภายใต้หมวด (level 2) — ใช้ใน select cascade */
    public function ListStructureTypeByGroup($groupCode)
    {
        if (empty($groupCode)) {
            return [];
        }
        return ArrayHelper::map(
            Categorise::find()
                ->where(['name' => 'structure_type', 'category_id' => $groupCode, 'active' => 1])
                ->orderBy(['id' => SORT_ASC])
                ->all(),
            'code',
            'title'
        );
    }

    /**
     * คืนค่า default ค่าเสื่อมของ leaf — ถ้า data_json เปล่าให้ดึงจากหมวด
     * @return array{useful_life:?int, depreciation_rate:?float, group_code:?string, group_title:?string, allow_other_note:bool}
     */
    public function getStructureDefaults($leafCode)
    {
        $empty = [
            'useful_life' => null,
            'depreciation_rate' => null,
            'group_code' => null,
            'group_title' => null,
            'allow_other_note' => false,
        ];
        if (empty($leafCode)) {
            return $empty;
        }
        $leaf = Categorise::find()->where(['name' => 'structure_type', 'code' => $leafCode])->one();
        if (!$leaf) {
            return $empty;
        }

        $leafJson = is_array($leaf->data_json)
            ? $leaf->data_json
            : (is_string($leaf->data_json) ? (json_decode($leaf->data_json, true) ?: []) : []);

        $parent = !empty($leaf->category_id)
            ? Categorise::find()->where(['name' => 'structure_type', 'code' => $leaf->category_id])->one()
            : null;
        $parentJson = $parent && is_array($parent->data_json)
            ? $parent->data_json
            : ($parent && is_string($parent->data_json) ? (json_decode($parent->data_json, true) ?: []) : []);

        return [
            'useful_life'       => $leafJson['useful_life']       ?? $parentJson['useful_life']       ?? null,
            'depreciation_rate' => $leafJson['depreciation_rate'] ?? $parentJson['depreciation_rate'] ?? null,
            'group_code'        => $parent->code  ?? null,
            'group_title'       => $parent->title ?? null,
            'allow_other_note'  => !empty($leafJson['allow_other_note']),
        ];
    }

    /**
     * ชุด code ของ node ที่ "มีลูก" (ถูกใช้เป็น category_id ของ node อื่น) — ใช้เช็คว่า
     * node หนึ่งเป็นหมวด/หมวดย่อย (ต้อง cascade ต่อ) หรือเป็น leaf จริง โดยไม่ยิง query ต่อ node
     * @return string[]
     */
    public function getStructureChildCodes()
    {
        return Categorise::find()
            ->where(['name' => 'structure_type'])
            ->andWhere(['not', ['category_id' => null]])
            ->select('category_id')
            ->distinct()
            ->column();
    }

    /**
     * เดินขึ้นจาก code ที่ให้มาไปตาม category_id จนถึงราก คืน path เรียงจากบนลงล่าง
     * ใช้ resolve หมวด/หมวดย่อย/leaf ที่ถูกต้องปัจจุบันจาก leaf code เสมอ (leaf ไม่เคยย้ายที่
     * แม้หมวดของมันจะถูกจัดกลุ่มใหม่ในภายหลัง)
     * @return array<int,array{code:string,title:string}>
     */
    public function getStructureAncestryFor($code)
    {
        $path = [];
        $current = $code;
        $guard = 0;
        while (!empty($current) && $guard < 5) {
            $row = Categorise::find()->where(['name' => 'structure_type', 'code' => $current])->one();
            if (!$row) {
                break;
            }
            array_unshift($path, ['code' => $row->code, 'title' => $row->title]);
            $current = $row->category_id;
            $guard++;
        }
        return $path;
    }

    public function ListAssetstatus()
    {
        return ArrayHelper::map(AssetStatus::find()->all(), 'id', 'name');
    }

    public function ListMaintainpm()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'maintain_pm'])->all(), 'code', 'title');
    }

    public function ListTestcal()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'test_cal'])->all(), 'code', 'title');
    }

    public function ListAssetrisk()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'asset_risk'])->all(), 'code', 'title');
    }

    // ผู้รับผิดชอบ
    public function getOwner()
    {
        try {
            $id = $this->owner;
            $employee = Employees::find()->where(['id' => $id])->one();
            return $employee->getAvatar(false);
        } catch (\Throwable $th) {
            return null;
        }
    }

    public function Retire()
    {
        try {
            $birthday = $this->receive_date;

            $age = $this->data_json['service_life'];
            $color = '';

            if (substr($birthday, 5, 2) >= 10) {
                $age += 1;
            }
            // ถ้าเลยปีงบประมาณแล้ว ให้ไปอยู่ในปีข้างหน้า
            $date_retire = (substr($birthday, 0, 4) + $age) . '-09-30';  // สิ้นปีงบประมาณ หน่วยงานราชการ
            // return $date_retire;
            $currentDate = new \DateTime();
            $date1 = new \DateTime($birthday);
            $date2 = new \DateTime($date_retire);
            $totalDays = $date1->diff($date2)->days;
            $currentDays = $date1->diff($currentDate)->days;
            $progress = ($currentDays / $totalDays) * 100;
            if (100 - $progress >= 70) {
                $color = '#198754';
            } elseif (100 - $progress >= 50) {
                $color = '#5a9e23';
            } elseif (100 - $progress >= 40) {
                $color = '#ffc107';
            } elseif (100 - $progress >= 20) {
                $color = '#e69a24';
            } else {
                $color = '#dc3545';
            }
            return [
                'date' => AppHelper::DateFormDb($date_retire),
                'progress' => 100 - $progress,
                'color' => $color,
            ];
        } catch (\Throwable $th) {
            // throw $th;
        }
    }

    // ดึงข้อมูลผู้ขาย
    public function getVendor()
    {
        $vendorId = isset($this->data_json['vendor_id']) ? $this->data_json['vendor_id'] : 0;
        $model = Categorise::findOne(['code' => $vendorId]);
        if ($model) {
            return $model;
        } else {
            return false;
        }
    }

    // คิดค่าเสื่อมของเดือน
    public function Depreciation($month, $year)
    {
        // if(isset($q_month)){

        $d1 = ($year - 543) . '-' . $month . '-01';

        $sqlMonth = 'SELECT LAST_DAY(:d1) As date';
        $queryMonth = Yii::$app
            ->db
            ->createCommand('SELECT LAST_DAY(:d1)')
            ->bindValue(':d1', $d1)
            ->queryScalar();
        // ->getRawSql();
        // return $queryMonth;

        $sql = "select xx.*,
    (xx.month_price * date_number) as total_month_price,
    (xx.price -(xx.month_price * date_number)) as total
    from (select
    LAST_DAY(m1) as date,
    (TIMESTAMPDIFF(MONTH, :receive_date ,LAST_DAY(m1))+1)  as date_number,
    DAYOFMONTH(LAST_DAY(DATE_FORMAT(m1, '%Y-%m-%d'))) as days_of_month,
    DATEDIFF(DATE_FORMAT(DATE_FORMAT(m1, '%Y-%m-%d') + INTERVAL 1 YEAR,'%Y-%m-%d'),DATE_FORMAT(m1, '%Y-%m-%d')) AS days_of_year,
    (select price FROM asset where id =:id) as price,
    :receive_date as receive_date,
    (SELECT data_json->'\$.service_life' FROM asset WHERE id = :id) as service_life,
    (SELECT (price/CAST(data_json->'\$.service_life' as UNSIGNED)) FROM asset WHERE id = :id) as year_price,
    (SELECT (price/CAST(data_json->'\$.service_life' as UNSIGNED) / 12) FROM asset WHERE id = :id) as month_price,
    (SELECT CAST(data_json->'\$.depreciation' as UNSIGNED) FROM asset WHERE id = :id) as depreciation
    from
    (
    select (:receive_date - INTERVAL DAYOFMONTH(:receive_date)-1 DAY)
    +INTERVAL m MONTH as m1
    from
    (
    select @rownum:=@rownum+1 as m from
    (select 1 union select 2 union select 3 union select 4) t1,
    (select 1 union select 2 union select 3 union select 4) t2,
    (select 1 union select 2 union select 3 union select 4) t3,
    (select 1 union select 2 union select 3 union select 4) t4,
    (select @rownum:=-1) t0
    ) d1
    ) d2
    where m1<= DATE_FORMAT(:receive_date + INTERVAL :service_life YEAR,'%Y-%m-%d')
    order by m1)as xx where xx.date = :date;";
        $querys = Yii::$app
            ->db
            ->createCommand($sql)
            ->bindValue(':id', $this->id)
            ->bindValue(':receive_date', $this->receive_date)
            ->bindValue(':service_life', $this->data_json['service_life'])
            ->bindValue(':date', $queryMonth)
            // ->getRawSql();
            ->queryOne();
        return $querys;
        // }

        return
            [
                'date' => isset($querys['date']) ? $querys['date'] : '',
                'depreciation' => isset($querys['depreciation']) ? $querys['depreciation'] : 'ไม่ระบุบ',
                'last_price' => isset($querys['month_price']) ? ($querys['month_price'] + $querys['total']) : 0,
                'month_price' => isset($querys['month_price']) ? $querys['month_price'] : 0,
                'total_month_price' => isset($querys['total_month_price']) ? $querys['total_month_price'] : 0,
                'total' => isset($querys['total']) ? $querys['total'] : 0,
            ];
    }

    // ตรวจสอบว่าเป็นรถครุภัณฑ์ยานพาหนะและขนส่ง
    public function isCar()
    {
        $sql = "SELECT count(a.id) FROM asset a
        INNER JOIN categorise asset_item_id ON asset_item_id.code = a.asset_item_id AND asset_item_id.name = 'asset_item_id'
        INNER JOIN categorise asset_type ON asset_type.code = asset_item_id.category_id AND asset_type.name = 'asset_type'
        WHERE asset_type.code = 4 AND a.code = :code";
        $query = Yii::$app
            ->db
            ->createCommand($sql)
            ->bindValue(':code', $this->code)
            ->queryScalar();
        // return $this->asset_item_id == '2310-001-0003' ? true : false;
        return $query > 0 ? true : false;
        // }
    }

    // ตรวจสอบว่าเป็นคอมพิวเตอร์
    public function isComputer()
    {
        try {
            $data = explode('-', $this->asset_item_id);
            $code = $data[0] . '-' . $data[1];
            return $code == '7440-001' ? true : false;
        } catch (\Throwable $th) {
            return false;
        }
    }

    // ตรวจสอบว่าเป็นครุภัณฑ์วิทยาศาสตร์และการแพทย์
    public function isMedical()
    {
        try {
            $data = explode('-', $this->asset_item_id);
            return $data[0] == '6515' ? true : false;
        } catch (\Throwable $th) {
            return false;
        }
    }

    // ตรวจสอบว่าเป็นครุภัณฑ์วิทยาศาสตร์และการแพทย์
    public function isRepair()
    {
        try {
            $data = explode('-', $this->asset_item_id);
            return $data[0] == '6515' ? true : false;
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function NextId()
{
    $prefix = $this->asset_group_id;

    $last = self::find()
        ->where(['asset_group_id' => $prefix])
        ->andWhere(['like', 'code', $prefix . '-%', false])
        ->orderBy(['id' => SORT_DESC])
        ->one();

    if ($last) {
        $parts = explode('-', $last->code);
        $number = (int)$parts[1] + 1;
    } else {
        $number = 1;
    }

    return $prefix . '-' . $number;
}
/**
     * ฟังก์ชันแสดง Badge ของ "สถานะ"
     */
    public function getStatusBadge()
    {
        if (!$this->assetStatus) {
            return $this->renderBadge('ไม่ระบุ', 'secondary');
        }
        // เรียกใช้ renderBadge โดยส่งชื่อ และ class สีที่เก็บไว้ใน DB
        return $this->renderBadge($this->assetStatus->name, $this->assetStatus->color_css);
    }

    /**
     * ฟังก์ชันแสดง Badge ของ "สภาพ"
     */
    public function getConditionBadge()
    {
        if (!$this->assetCondition) {
             return $this->renderBadge('ไม่ระบุ', 'secondary');
        }
        
        // กำหนดสีให้แต่ละสภาพการใช้งาน (เนื่องจาก condition ไม่มีสีใน DB)
        $conditionColors = [
            'good' => 'success',    // ดี (เขียว)
            'fair' => 'info',       // พอใช้ (ฟ้า)
            'worn' => 'warning',    // เสื่อมสภาพ (เหลือง)
            'damaged' => 'danger'   // ชำรุด (แดง)
        ];
        
        $colorCss = $conditionColors[$this->asset_condition] ?? 'secondary';
        return $this->renderBadge($this->assetCondition->name, $colorCss);
    }

    /**
     * Badge แสดง "ระดับความเสี่ยง" — ใช้ไอคอนนำ (ต่างจาก status/condition ที่ใช้จุด) เพื่อให้สแกนแยกหมวดได้
     */
    public function getRiskLevelBadge()
    {
        $level = (string) ($this->risk_level ?? '');

        $map = [
            'L' => ['label' => 'ต่ำ',   'theme' => 'success', 'icon' => 'fa-shield-halved'],
            'M' => ['label' => 'กลาง', 'theme' => 'warning', 'icon' => 'fa-triangle-exclamation'],
            'H' => ['label' => 'สูง',  'theme' => 'danger',  'icon' => 'fa-circle-exclamation'],
        ];

        if (!isset($map[$level])) {
            return '<span class="text-body-tertiary small" title="ยังไม่ประเมิน">—</span>';
        }

        $info = $map[$level];
        $themes = [
            'success' => ['bg' => 'rgb(236, 253, 245)', 'text' => 'rgb(4, 120, 87)',  'border' => 'rgb(167, 243, 208)'],
            'warning' => ['bg' => 'rgb(254, 252, 232)', 'text' => 'rgb(161, 98, 7)',  'border' => 'rgb(254, 240, 138)'],
            'danger'  => ['bg' => 'rgb(255, 241, 242)', 'text' => 'rgb(190, 18, 60)', 'border' => 'rgb(254, 205, 211)'],
        ];
        $color = $themes[$info['theme']];

        return '<span class="badge rounded-pill fw-bold border d-inline-flex align-items-center justify-content-center gap-1" '
             . 'title="ระดับความเสี่ยง: ' . $info['label'] . '" '
             . 'style="background-color: ' . $color['bg'] . '; color: ' . $color['text'] . '; border-color: ' . $color['border'] . '; font-size: 11px; padding: 4px 10px; line-height: 1;">'
             . '<i class="fa-solid ' . $info['icon'] . '" aria-hidden="true" style="font-size: 10px;"></i>'
             . '<span>' . $info['label'] . '</span>'
             . '</span>';
    }

    /**
     * องค์ประกอบหลักในการสร้าง HTML Badge (ตาม UI ที่คุณต้องการ)
     */
    private function renderBadge($text, $theme)
    {
        // กำหนดชุดสีอ้างอิงตามที่คุณออกแบบไว้ (โทนพาสเทลแบบ Tailwind)
        $themes = [
            'success' => [ // สีเขียว
                'bg' => 'rgb(236, 253, 245)', 'text' => 'rgb(4, 120, 87)', 'border' => 'rgb(167, 243, 208)', 'dot' => 'rgb(16, 185, 129)'
            ],
            'info' => [ // สีฟ้า
                'bg' => 'rgb(240, 249, 255)', 'text' => 'rgb(3, 105, 161)', 'border' => 'rgb(186, 230, 253)', 'dot' => 'rgb(14, 165, 233)'
            ],
            'warning' => [ // สีเหลือง/ส้ม
                'bg' => 'rgb(254, 252, 232)', 'text' => 'rgb(161, 98, 7)', 'border' => 'rgb(254, 240, 138)', 'dot' => 'rgb(234, 179, 8)'
            ],
            'danger' => [ // สีแดง
                'bg' => 'rgb(255, 241, 242)', 'text' => 'rgb(190, 18, 60)', 'border' => 'rgb(254, 205, 211)', 'dot' => 'rgb(244, 63, 94)'
            ],
            'secondary' => [ // สีเทา
                'bg' => 'rgb(243, 244, 246)', 'text' => 'rgb(55, 65, 81)', 'border' => 'rgb(229, 231, 235)', 'dot' => 'rgb(107, 114, 128)'
            ],
        ];

        // เลือกชุดสี ถ้าไม่มีให้ใช้สีเทา (secondary)
        $color = $themes[$theme] ?? $themes['secondary'];

        // สร้าง HTML
        return '<span class="badge rounded-pill fw-bold border d-inline-flex align-items-center justify-content-center gap-1" ' .
               'style="background-color: '.$color['bg'].'; color: '.$color['text'].'; border-color: '.$color['border'].'; font-size: 11px; padding: 4px 10px;">' .
               '<span class="rounded-circle" style="width: 6px; height: 6px; background-color: '.$color['dot'].';"></span>' .
               $text .
               '</span>';
    }
}
