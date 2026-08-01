<?php

namespace app\modules\plan\models;

use Yii;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\AssetHelper;
use app\modules\hr\models\Organization;
use app\modules\plan\models\PlanOrderItem;

/**
 * This is the model class for table "plan_order".
 *
 * @property int $id
 * @property int|null $thai_year ปีงบประมาณ
 * @property int|null $department_id หน่วยงาน
 * @property string $plan_group_id ประเภทแผน: material, personnel, expenses
 * @property string|null $asset_group_id แยกประเภทพัสดุ/ครุภัณฑ์
 * @property string|null $asset_type_id แยกประเภทพัสดุ/ครุภัณฑ์
 * @property string|null $asset_category_id หมวดหมู่ของประเภททรัพย์สิน
 * @property string $title ชื่อแผน
 * @property string|null $description รายละเอียด
 * @property string $start_date วันที่เริ่มแผน
 * @property string $end_date วันที่สิ้นสุดแผน
 * @property float|null $budget_total งบประมาณรวม
 * @property float|null $budget_used งบที่ใช้ไปแล้ว
 * @property string|null $status สถานะ: draft, submitted, approved, completed
 * @property string $emp_id ผู้ขอ
 * @property string|null $data_json ยานพาหนะ
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class PlanOrder extends \yii\db\ActiveRecord
{
    /**
     * เกณฑ์ราคาต่อหน่วยของครุภัณฑ์ต่ำกว่าเกณฑ์ (บาท)
     * ครุภัณฑ์ที่ราคา/หน่วย < ค่านี้ ให้จัดเป็น INV_03 (ค่าครุภัณฑ์ต่ำกว่าเกณฑ์)
     * *** แก้ค่านี้จุดเดียวหากระเบียบเปลี่ยน ***
     */
    const MINOR_ASSET_THRESHOLD = 10000;

    /**
     * หมวดพัสดุ (asset_group) ที่แสดงในแผนพัสดุ => plan_category ปลายทาง
     * (ครุภัณฑ์ต่ำกว่าเกณฑ์ไม่แยกหมวด ใช้ราคากรองเป็น INV_03)
     * asset_group code: 1 ที่ดิน, 2 อาคาร, 3 สิ่งปลูกสร้าง, 4 ครุภัณฑ์, 7 วัสดุ
     */
    const ASSET_GROUP_TO_CATEGORY = [
        '1' => 'INV_02', // ที่ดิน -> ค่าที่ดินและสิ่งก่อสร้าง
        '2' => 'INV_02', // อาคาร
        '3' => 'INV_02', // สิ่งปลูกสร้าง
        '4' => 'INV_01', // ครุภัณฑ์ (ราคา<เกณฑ์ -> INV_03)
        '7' => 'OPS_03', // วัสดุ -> ค่าวัสดุ
    ];

    /**
     * map ประเภทวัสดุ (asset_type M*) -> plan_item ใต้ ค่าวัสดุ (OPS_03)
     * ใช้ตอนเลือกหมวด=วัสดุ (ผู้ใช้เลือกช่องเดียว = ประเภทวัสดุ) ระบบตั้ง plan_item ให้เอง
     * ตัวที่ไม่มีคู่ -> P78 (วัสดุอื่นๆ) ; ทุกตัวรวมยอดขึ้น "ค่าวัสดุ" เหมือนกัน
     */
    const ASSET_TYPE_TO_VASDU_ITEM = [
        'M1' => 'P85', 'M2' => 'P86', 'M3' => 'P82', 'M4' => 'P80', 'M5' => 'P84',
        'M6' => 'P87', 'M7' => 'P79', 'M9' => 'P88', 'M10' => 'P89', 'M12' => 'P81',
        'M18' => 'P83', 'M19' => 'P91', 'M20' => 'P79', 'M22' => 'P90', 'M23' => 'P92',
        'M24' => 'P93', 'M26' => 'P90',
    ];

    public static function vasduItemForAssetType($assetTypeCode)
    {
        return self::ASSET_TYPE_TO_VASDU_ITEM[(string) $assetTypeCode] ?? 'P78';
    }

    /**
     * แปลงหมวดพัสดุ (asset_group) เป็น plan_type_id + plan_category_id
     * @param string $assetGroupCode รหัส asset_group (1-7)
     * @param float|null $unitPrice ราคาต่อหน่วย (ใช้แยกครุภัณฑ์ต่ำกว่าเกณฑ์)
     * @return array{plan_type_id:string, plan_category_id:string}|null
     */
    public static function mapAssetGroupToPlan($assetGroupCode, $unitPrice = null)
    {
        $code = (string) $assetGroupCode;
        if (!isset(self::ASSET_GROUP_TO_CATEGORY[$code])) {
            return null;
        }
        $category = self::ASSET_GROUP_TO_CATEGORY[$code];

        // ครุภัณฑ์ราคาต่อหน่วยต่ำกว่าเกณฑ์ -> ครุภัณฑ์ต่ำกว่าเกณฑ์
        if ($code === '4' && $unitPrice !== null && (float) $unitPrice > 0 && (float) $unitPrice < self::MINOR_ASSET_THRESHOLD) {
            $category = 'INV_03';
        }

        $type = strpos($category, 'INV') === 0 ? 'INV' : 'OPS';
        return ['plan_type_id' => $type, 'plan_category_id' => $category];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'plan_order';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [
                [
                    'thai_year',
                    'department_id',
                    'asset_group_id',
                    'asset_type_id',
                    'asset_category_id',
                    'description',
                    'data_json',
                    'created_at',
                    'updated_at',
                    'created_by',
                    'updated_by',
                    'deleted_at',
                    'deleted_by'
                ],
                'default',
                'value' => null
            ],
            [['budget_used'], 'default', 'value' => 0.00],
            [['status'], 'default', 'value' => 'draft'],
            [
                [
                    'month_1',
                    'month_2',
                    'month_3',
                    'month_4',
                    'month_5',
                    'month_6',
                    'month_7',
                    'month_8',
                    'month_9',
                    'month_10',
                    'month_11',
                    'month_12',
                    'order_price'
                ],
                'default',
                'value' => 0
            ],
            [['thai_year', 'department_id', 'plan_unit_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['plan_group_id', 'thai_year', 'department_id', 'asset_group_id', 'price_ref'], 'required'],
            [['description', 'reference'], 'string'],
            [
                [
                    'plan_category_id',
                    'plan_item_id',
                    'data_json',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                    'title',
                    'emp_id',
                    'month_1',
                    'month_2',
                    'month_3',
                    'month_4',
                    'month_5',
                    'month_6',
                    'month_7',
                    'month_8',
                    'month_9',
                    'month_10',
                    'month_11',
                    'month_12',
                    'order_price',
                    'plan_type_item_id',
                    'plan_type_id',
                    'wage_type_id',
                    'plan_budget_type_id'
                ],
                'safe'
            ],
            [['budget_total', 'budget_used'], 'number'],
            [['plan_group_id'], 'string', 'max' => 50],
            [['asset_group_id', 'asset_type_id', 'asset_category_id', 'title', 'emp_id'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'thai_year' => 'ปีงบประมาณ',
            'department_id' => 'Department ID',
            'plan_group_id' => 'Plan Group ID',
            'asset_group_id' => 'Asset Group ID',
            'asset_type_id' => 'Asset Type ID',
            'wage_type_id' => 'ค่าจ้าง',
            'asset_category_id' => 'Asset Category ID',
            'title' => 'Title',
            'description' => 'Description',
            'budget_total' => 'Budget Total',
            'budget_used' => 'Budget Used',
            'status' => 'Status',
            'emp_id' => 'Emp ID',
            'data_json' => 'Data Json',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'deleted_at' => 'Deleted At',
            'deleted_by' => 'Deleted By',
        ];
    }


    /**
     * ก่อนบันทึกทุกครั้ง: ถ้ามี plan_item_id ที่ผูกกับ plan_item จริง
     * ให้ derive plan_category_id + plan_type_id จากสาย item เสมอ
     * เพื่อกันข้อมูลปนเปื้อน (ฟอร์มบางตัว hardcode/hidden ค่าหมวดที่ผิด เช่น PER_04, PE, OE)
     * source of truth เดียว = plan_item_id -> plan_item.category_id -> plan_category -> plan_type
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // หมวด=วัสดุ (asset_group 7): ผู้ใช้เลือกช่องเดียว (ประเภทวัสดุ) -> ตั้ง plan_item ให้อัตโนมัติ
        if ((string) $this->asset_group_id === '7' && !empty($this->asset_type_id)) {
            $this->plan_item_id = self::vasduItemForAssetType($this->asset_type_id);
        }

        if (!empty($this->plan_item_id)) {
            $chain = (new \yii\db\Query())
                ->select(['cat' => 'i.category_id', 'type' => 'c.category_id'])
                ->from(['i' => 'categorise'])
                ->leftJoin(['c' => 'categorise'], 'c.code = i.category_id AND c.name = :cat', [':cat' => 'plan_category'])
                ->where(['i.name' => 'plan_item', 'i.code' => $this->plan_item_id])
                ->one();

            if ($chain && $chain['cat'] !== null) {
                $this->plan_category_id = $chain['cat'];
                if ($chain['type'] !== null) {
                    $this->plan_type_id = $chain['type'];
                }
            }
        }

        // เฟส 2: ผูกทะเบียนหน่วยงาน (org_unit) แบบ dual-write
        if (empty($this->plan_unit_id) && !empty($this->department_id) && !empty($this->thai_year)) {
            // ฟอร์มเดิม (เลือกหน่วยจากผัง) -> เติม plan_unit_id จากหน่วยในโครงสร้างปีเดียวกัน
            $this->plan_unit_id = (new \yii\db\Query())
                ->select('id')->from('org_unit')
                ->where(['thai_year' => (int) $this->thai_year, 'source' => 'structure', 'ref_id' => (int) $this->department_id])
                ->scalar() ?: null;
        } elseif (!empty($this->plan_unit_id) && empty($this->department_id)) {
            // ฟอร์มใหม่ (เลือกจากทะเบียน) -> เติม department_id กลับถ้าเป็นหน่วยในโครงสร้าง (manual = null)
            $refId = (new \yii\db\Query())
                ->select('ref_id')->from('org_unit')
                ->where(['id' => (int) $this->plan_unit_id, 'source' => 'structure'])
                ->scalar();
            if ($refId) {
                $this->department_id = (int) $refId;
            }
        }

        return true;
    }

    public function getAssetGroup()
    {
        return $this->hasOne(Categorise::class, ['code' => 'asset_group_id'])->andOnCondition(['name' => 'asset_group']);
    }

    public function getAssetType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'asset_type_id'])->andOnCondition(['name' => 'asset_type']);
    }


    public function getAssetCategory()
    {
        return $this->hasOne(Categorise::class, ['code' => 'asset_category_id'])->andOnCondition(['name' => 'asset_category']);
    }

        public function getPlanItem()
    {
        return $this->hasOne(PlanItem::class, ['code' => 'plan_item_id'])->andOnCondition(['name' => 'plan_item']);
    }


    public function getBudge()
    {
        return $this->hasOne(Categorise::class, ['code' => 'plan_budget_type_id'])->andOnCondition(['name' => 'budget_type']);
    }

    /** หน่วยงานในทะเบียนกลาง (org_unit) — ผูกด้วย plan_unit_id */
    public function getPlanUnit()
    {
        return $this->hasOne(\app\modules\settings\models\OrgUnit::class, ['id' => 'plan_unit_id']);
    }


    public function getPlanType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'plan_type_id'])->andOnCondition(['name' => 'plan_type']);
    }
    public function getPlanTypeItem()
    {
        return $this->hasOne(Categorise::class, ['code' => 'plan_type_item_id'])->andOnCondition(['name' => 'plan_type_item']);
    }

    public function getWageType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'wage_type_id'])->andOnCondition(['name' => 'plan_wage_type']);
    }




    public function getPlanItems()
    {
        return $this->hasMany(PlanOrderItem::class, ['plan_order_id' => 'id']);
    }


    public function ListThaiYear()
    {
        $model = self::find()
            ->select('thai_year')
            ->groupBy('thai_year')
            ->orderBy(['thai_year' => SORT_DESC])
            ->asArray()
            ->all();

        $year = AppHelper::YearBudget();
        $isYear = [['thai_year' => $year]];  // ห่อด้วย array เพื่อให้รูปแบบตรงกัน
        // รวมข้อมูล
        $model = ArrayHelper::merge($isYear, $model);
        return ArrayHelper::map($model, 'thai_year', 'thai_year');
    }

    public function departmentName()
    {
        $model =  Organization::findOne(['id' => $this->department_id]);
        if ($model) {
            return $model->name;
        } else {
            return '-';
        }
    }

    public function listBudgetType()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'budget_type'])->all(), 'code', 'title');
    }



    public function listPriceRef()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'price_ref'])->all(), 'code', 'title');
    }

    public function countStatus($statusCode = null, $planGroup = null)
    {
        return self::find()
            ->andFilterWhere(['status' => $statusCode])
            ->andFilterWhere(['thai_year' => $this->thai_year])
            ->andFilterWhere(['plan_group_id' => $planGroup])
            ->count();
    }

    /** ยอดเงินรวม (order_price) ตามสถานะ/กลุ่ม (คู่กับ countStatus) */
    public function sumStatus($statusCode = null, $planGroup = null)
    {
        return (float) self::find()
            ->andFilterWhere(['status' => $statusCode])
            ->andFilterWhere(['thai_year' => $this->thai_year])
            ->andFilterWhere(['plan_group_id' => $planGroup])
            ->sum('order_price');
    }


    public function viewStatus()
    {
        switch ($this->status) {
            case 'draft':
                $title = 'ฉบับร่าง';
                break;
                $title = '';
            case 'submit':
                $title = 'ส่งคำขอ';
                break;
            case 'approve':
                $title = 'อนุมัติ';
                break;
            case 'renew':
                $title = 'ปรับแผน';
                break;
            case 'reject':
                $title = 'ไม่อนุมัติ';
                break;
            default:
                $title = '';
                break;
        }

        return [
            'title' => $title,
            'view' => $title,

        ];
    }

    /**
     * รวมยอดสำหรับหน้า "ติดตามแผนรายจ่าย" (/plan/overview)
     * รวมผ่านสายที่เชื่อถือได้เท่านั้น: plan_order.plan_item_id -> plan_item.category_id
     * -> plan_category -> plan_type (ไม่ใช้ plan_type_id/plan_category_id บน plan_order ที่ปนเปื้อน)
     *
     * คืนค่าโครงสร้าง:
     *   [
     *     'types' => [
     *        'PER' => ['title'=>..., 'categories'=>[ ['code','title','m1'..'m12','total'], ... ],
     *                  'sub'=>['m1'..'m12'=>..,'total'=>..]],
     *        ...
     *     ],
     *     'grand' => ['m1'..'m12'=>..,'total'=>..],
     *   ]
     * ทุก plan_category จะถูกแสดงเสมอ (LEFT JOIN) แม้ยอดเป็น 0
     *
     * @param int $thaiYear ปีงบประมาณ (พ.ศ.)
     * @param string|null $status กรองเฉพาะสถานะ (เช่น 'approve'); null = ทุกสถานะ
     * @return array
     */
    public static function overviewByType($thaiYear, $status = null)
    {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[] = "COALESCE(SUM(o.month_$i),0) AS m$i";
        }
        $monthSelect = implode(",\n", $months);

        // กรองสถานะไว้ใน ON ของ LEFT JOIN เพื่อให้ทุกหมวดยังแสดง (ยอด 0) แม้ไม่มีแผนตรงสถานะ
        $params = [':year' => $thaiYear];
        $statusCond = '';
        if ($status !== null && $status !== '' && $status !== 'all') {
            $statusCond = ' AND o.status = :status';
            $params[':status'] = $status;
        }

        $sql = "
            SELECT
                t.code AS type_code, t.title AS type_title,
                c.code AS cat_code, c.title AS cat_title,
                COALESCE(SUM(o.order_price),0) AS total,
                $monthSelect
            FROM categorise t
            JOIN categorise c ON c.category_id = t.code AND c.name = 'plan_category'
            LEFT JOIN categorise i ON i.category_id = c.code AND i.name = 'plan_item'
            LEFT JOIN plan_order o ON o.plan_item_id = i.code AND o.thai_year = :year{$statusCond}
            WHERE t.name = 'plan_type'
            GROUP BY t.code, t.title, c.code, c.title
            ORDER BY FIELD(t.code,'PER','OPS','INV','OTH'), c.code
        ";

        $rows = Yii::$app->db->createCommand($sql, $params)->queryAll();

        $keys  = ['total', 'm1', 'm2', 'm3', 'm4', 'm5', 'm6', 'm7', 'm8', 'm9', 'm10', 'm11', 'm12'];
        $blank = array_fill_keys($keys, 0.0);

        $types = [];
        $grand = $blank;

        foreach ($rows as $r) {
            $tc = $r['type_code'];
            if (!isset($types[$tc])) {
                $types[$tc] = [
                    'title'      => $r['type_title'],
                    'categories' => [],
                    'sub'        => $blank,
                ];
            }

            $cat = ['code' => $r['cat_code'], 'title' => $r['cat_title']];
            foreach ($keys as $k) {
                $val = (float) $r[$k];
                $cat[$k] = $val;
                $types[$tc]['sub'][$k] += $val;
                $grand[$k] += $val;
            }
            $types[$tc]['categories'][] = $cat;
        }

        return ['types' => $types, 'grand' => $grand];
    }

    public static function listOverviewSummary($thaiYear, $categoryId)
    {
        $sql = "
                SELECT 
                    c.code,
                    c.title,
                    :thai_year AS thai_year,
                       IFNULL(SUM(p.month_1), 0)  AS m1,
                        IFNULL(SUM(p.month_2), 0)  AS m2,
                        IFNULL(SUM(p.month_3), 0)  AS m3,
                        IFNULL(SUM(p.month_4), 0)  AS m4,
                        IFNULL(SUM(p.month_5), 0)  AS m5,
                        IFNULL(SUM(p.month_6), 0)  AS m6,
                        IFNULL(SUM(p.month_7), 0)  AS m7,
                        IFNULL(SUM(p.month_8), 0)  AS m8,
                        IFNULL(SUM(p.month_9), 0)  AS m9,
                        IFNULL(SUM(p.month_10), 0) AS m10,
                        IFNULL(SUM(p.month_11), 0) AS m11,
                        IFNULL(SUM(p.month_12), 0) AS m12,
                    (
                        IFNULL(SUM(p.month_1),0)  + IFNULL(SUM(p.month_2),0)  + IFNULL(SUM(p.month_3),0) +
                        IFNULL(SUM(p.month_4),0)  + IFNULL(SUM(p.month_5),0)  + IFNULL(SUM(p.month_6),0) +
                        IFNULL(SUM(p.month_7),0)  + IFNULL(SUM(p.month_8),0)  + IFNULL(SUM(p.month_9),0) +
                        IFNULL(SUM(p.month_10),0) + IFNULL(SUM(p.month_11),0) + IFNULL(SUM(p.month_12),0)
                    ) AS total
                FROM categorise c
                LEFT JOIN plan_order p 
                    ON p.plan_type_id = c.code
                    AND p.thai_year = :thai_year
                WHERE c.`name` = 'plan_type'
                AND c.category_id = :category_id
                GROUP BY c.code, c.title
            ";

        $rows = Yii::$app->db->createCommand($sql, [
            ':thai_year'   => $thaiYear,
            ':category_id' => $categoryId,
        ])->queryAll();

        return $rows;
    }
}
