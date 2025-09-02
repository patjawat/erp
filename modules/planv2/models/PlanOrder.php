<?php

namespace app\modules\planv2\models;

use Yii;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\AssetHelper;
use app\modules\hr\models\Organization;
use app\modules\planv2\models\PlanOrderItem;

/**
 * This is the model class for table "plan_order".
 *
 * @property int $id
 * @property int|null $thai_year ปีงบประมาณ
 * @property int|null $department_id หน่วยงาน
 * @property string $plan_group_id ประเภทแผน: material, personnel, expenses
 * @property string|null $asset_group_id แยกประเภทพัสดุ/ครุภัณฑ์
 * @property string|null $asset_type_id แยกประเภทพัสดุ/ครุภัณฑ์
 * @property string|null $asset_category_id หมวดหมู่ของประเภททรัพย์สินย์
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
            [['thai_year', 'department_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['plan_group_id', 'thai_year', 'department_id', 'asset_group_id', 'price_ref'], 'required'],
            [['description'], 'string'],
            [
                [
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

    public function getBudge()
    {
        return $this->hasOne(Categorise::class, ['code' => 'plan_budget_type_id'])->andOnCondition(['name' => 'budget_type']);
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
