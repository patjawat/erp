<?php

namespace app\modules\purchase\models;

use app\components\AppHelper;
use app\components\CategoriseHelper;
use app\models\Categorise;
use app\modules\am\models\AssetItem;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\filemanager\models\Uploads;
use app\modules\helpdesk\models\Helpdesk;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use Yii;

/**
 * This is the model class for table "order".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $name ชื่อตารางเก็บข้อมูล
 * @property string|null $category_id หมวดหมูหลักที่เก็บ
 * @property string|null $code รหัส
 * @property int|null $item_id รายการที่เก็บ
 * @property float|null $price ราคา
 * @property int|null $amount จำนวน
 * @property string|null $data_json
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 */
class Order extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['item_id', 'amount', 'created_by', 'updated_by'], 'integer'],
            [['price'], 'number'],
            [['data_json', 'created_at', 'updated_at', 'pr_number', 'po_number', 'status', 'approve'], 'safe'],
            [['ref', 'name', 'category_id', 'code'], 'string', 'max' => 255],
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
            'name' => 'Name',
            'category_id' => 'Category ID',
            'code' => 'Code',
            'item_id' => 'Item ID',
            'price' => 'Price',
            'amount' => 'amount',
            'data_json' => 'Data Json',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
        ];
    }

    // relation

    public function getProductType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'category_id'])->andOnCondition(['name' => 'product_type']);
    }

    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'item_id'])->andOnCondition(['name' => 'product_item']);
    }

    public function getVendor()
    {
        return $this->hasOne(Categorise::class, ['code' => 'data_json[vendor]'])->andOnCondition(['name' => 'vendor']);
    }

    // ผู้ขอ
    public function getUserReq()
    {
        try {
            $employee = Employees::find()->where(['user_id' => $this->created_by])->one();

            return [
                'avatar' => $employee->getAvatar(false),
                'department' => $employee->departmentName()
            ];
        } catch (\Throwable $th) {
            return [
                'avatar' => '',
                'department' => ''
            ];
        }
    }

    // หัวหน้าผู้ตรวจสแบ
    public function viewLeaderUser()
    {
        try {
            $employee = Employees::find()->where(['id' => $this->data_json['leader1']])->one();

            return [
                'avatar' => $employee->getAvatar(false),
                'department' => $employee->departmentName()
            ];
        } catch (\Throwable $th) {
            return [
                'avatar' => '',
                'department' => '',
            ];
        }
    }

    // แสเงวันที่ขอ

    public function viewCreatedAt()
    {
        return Yii::$app->thaiFormatter->asDateTime($this->created_at, 'php:d/m/Y H:i:s');
    }

    // แสดงวันที่ต้องการใช้าน

    public function viewDueDate()
    {
        return Yii::$app->thaiFormatter->asDate($this->data_json['due_date'], 'php:d/m/Y');
    }

    public function SumPo()
    {
        try {
            $query = Yii::$app
                ->db
                ->createCommand('SELECT sum(price * amount) as total FROM `order` WHERE category_id = :category_id;')
                ->bindValue(':category_id', $this->id)
                ->queryScalar();
            if ($query > 0) {
                return $query;
            } else {
                return 0;
            }
        } catch (\Throwable $th) {
            return 10;
        }
    }

    // วิธีซื้อหรือจ้าง
    public function ListPurchase()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'purchase'])->all(), 'code', 'title');
    }

    // วิธีจัดหา
    public function ListMethodget()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'method_get'])->all(), 'code', 'title');
    }

    // ประเภท
    public function ListBudgetdetail()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'budget_type'])->all(), 'code', 'title');
    }

    public function ListProductType()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'product_type'])->all(), 'code', 'title');
    }

    public function ListPr()
    {
        return ArrayHelper::map(self::find()->where(['name' => 'pr'])->all(), 'code', 'code');
    }

    public function ListProduct()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'product_item'])->all(), 'id', 'title');
    }

    public function ListPrStatus()
    {
        return Categorise::find()->where(['name' => 'pr_status'])->all();
    }

    public function ListVendor()
    {
        return CategoriseHelper::Vendor();
    }

    public function viewPrStatus()
    {
        $status = Categorise::findOne(['code' => $this->status, 'name' => 'pr_status']);
        if ($status) {
            if ($this->approve == 'N') {
                return '<label class="badge rounded-pill text-primary-emphasis bg-danger-subtle fs-6 text-truncate"><i class="fa-regular fa-circle-xmark text-danger"></i> ' . $status->title . ' | ไม่อนุมัติ</label>';
            } else {
                return '<label class="badge rounded-pill text-primary-emphasis bg-success-subtle fs-6 text-truncate"><i class="bi bi-clipboard-check"></i> ' . $status->title . '</label>';
            }
        } else {
            return '';
        }
    }

    public function ListPoStatus()
    {
        return Categorise::find()->where(['name' => 'po_status'])->all();
    }
}
