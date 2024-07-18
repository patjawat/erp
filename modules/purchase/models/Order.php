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
use app\modules\sm\models\Product;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
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
 * @property int|null $product_id รายการที่เก็บ
 * @property float|null $price ราคา
 * @property int|null $qty จำนวน
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
            [['product_id', 'qty', 'created_by', 'updated_by'], 'integer'],
            [['price'], 'number'],
            [[
                'data_json',
                'created_at',
                'updated_at',
                'pr_number',
                'pq_number',
                'po_number',
                'status',
                'approve',
                'vendor_id',
                'to_stock'
            ], 'safe'],
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
            'product_id' => 'Item ID',
            'price' => 'Price',
            'vendor_id' => 'ผู้จำหน่าย',
            'qty' => 'qty',
            'to_stock' => 'จำนวนที่รับเข้าคลังแล้ว',
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
        return $this->hasOne(Product::class, ['id' => 'product_id'])->andOnCondition(['name' => 'product_item']);
    }

    //  uploadFile
    public function Upload($name)
    {
        return FileManagerHelper::FileUpload($this->ref, $name);
    }

    public function orderAvatar()
    {
        $employee = Employees::find()->where(['user_id' => $this->created_by])->one();
        $img = Html::img($employee->showAvatar(), ['class' => 'avatar avatar-sm bg-primary text-white']);
        return '<div class="d-flex">'
            . $img . '
        <div class="avatar-detail">
            <h6 class="mb-1 fs-15"  data-bs-toggle="tooltip" data-bs-placement="top"
            data-bs-custom-class="custom-tooltip"
            data-bs-title="ดูเพิ่มเติม..."><span>'
            . $employee->fullname . '</span>
            </h6>
            <p class="text-muted mb-0 fs-13">' . $this->data_json['product_type_name'] . ' (<code>' . $this->viewCreatedAt(). '</code>)</p>
        </div>
    </div>';
    }

    // Avatar ของฉัน
    public static function getMe()
    {
try {
    $employee = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
    return [
        'avatar' => $employee->getAvatar(false),
        'department' => $employee->departmentName(),
        'fullname' => $employee->fullname,
    ];

} catch (\Throwable $th) {
    return [
        'avatar' => '',
        'department' => '',
        'fullname' => '',
    ];
}
    }
    // ผู้ขอ
    public function getUserReq()
    {
        try {
            $employee = Employees::find()->where(['user_id' => $this->created_by])->one();

            return [
                'avatar' => $this->orderAvatar(),
                'department' => $employee->departmentName(),
                'fullname' => $employee->fullname,
                'position_name' => $employee->positionName(),
                'product_type_name' => $this->data_json['product_type_name']
            ];
        } catch (\Throwable $th) {
            return [
                'avatar' => '',
                'department' => '',
                'fullname' => '',
                'position_name' => '',
                'product_type_name' => ''
            ];
        }
    }

    // หัวหน้าผู้ตรวจสแบ
    public function viewLeaderUser()
    {
        try {
            $employee = Employees::find()->where(['id' => $this->data_json['leader1']])->one();

            return [
                'id' => $employee->user_id,
                'avatar' => $employee->getAvatar(false),
                'department' => $employee->departmentName(),
                'fullname' => $employee->fullname,
                'position_name' => $employee->positionName()
            ];
        } catch (\Throwable $th) {
            return [
                'avatar' => '',
                'department' => '',
                'fullname' => '',
                'position_name' => '',
            ];
        }
    }

    // คณะกรรมการ
    public function ShowBoard()
    {
        try {
            $employee = Employees::find()->where(['id' => $this->data_json['employee_id']])->one();

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
                ->createCommand('SELECT sum(price * qty) as total FROM `order` WHERE category_id = :category_id;')
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


    public function listPrOrder()
    {

        return self::find()->where(['name' => 'pr_item', 'pr_number' => $this->pr_number])->all();
    }
    

    public function ListStatus()
    {
        return Categorise::find()->where(['name' => 'order_status'])->all();
    }

    public function ListOrderItems()
    {
        return self::find()
            ->where(['name' => 'order_item', 'category_id' => $this->id])
            ->all();
    }

    // แสดงชื่อคณะกรรมการ
    public function getBoard()
    {
        return self::find()->where(['category_id' => $this->id, 'name' => 'board'])->all();
    }

    // วิธีซื้อหรือจ้าง
    public function ListPurchase()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'purchase'])->all(), 'code', 'title');
    }

    // คณะกรรมการ
    public function ListBoard()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'board'])->all(), 'code', 'title');
    }

    // วิธีจัดหา
    public function ListMethodget()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'method_get'])->all(), 'code', 'title');
    }

    // เงื่อนไข
    public function ListPurchaseCondition()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'purchase_condition'])->all(), 'code', 'title');
    }

    // ประเภท
    public function ListBudgetdetail()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'budget_type'])->all(), 'code', 'title');
    }

    public function ListProductType()
    {
        return ArrayHelper::map(Categorise::find()->andWhere(['in', 'name', ['product_type', 'asset_type','food_type','service_type']])->all(), 'code', 'title');
    }

    public function ListPr()
    {
        return ArrayHelper::map(self::find()->where(['name' => 'pr'])->all(), 'code', 'code');
    }

    public function ListProduct()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'product_item'])->all(), 'id', 'title');
    }

    // public function ListPrStatus()
    // {
    //     return Categorise::find()->where(['name' => 'pr_status'])->all();
    // }

    public function ListVendor()
    {
        return CategoriseHelper::Vendor();
    }

    // public function viewPrStatus()
    // {
    //     $status = Categorise::findOne(['code' => $this->status, 'name' => 'pr_status']);
    //     if ($status) {
    //         if ($this->approve == 'N') {
    //             return '<label class="badge rounded-pill text-primary-emphasis bg-danger-subtle fs-6 text-truncate"><i class="fa-regular fa-circle-xmark text-danger"></i> ' . $status->title . ' | ไม่อนุมัติ</label>';
    //         } else {
    //             return '<label class="badge rounded-pill text-primary-emphasis bg-success-subtle fs-6 text-truncate"><i class="bi bi-clipboard-check"></i> ' . $status->title . '</label>';
    //         }
    //     } else {
    //         return '';
    //     }
    // }

    public function viewStatus()
    {
        $data = $this->data_json;

        if($this->status == 2){
            if($data['pr_leader_confirm'] == 'Y'){
                return 'หัวหน้า <i class="bi bi-check2-circle"></i> เห็นชอบ';
            }else{
                return 'หัวหน้า <i class="bi bi-x-circle"></i> ไม่เป็นชอบ';
            }
        }

        if($this->status == 3){
            if($data['pr_leader_confirm'] == 'Y'){
                return ' <i class="bi bi-check2-circle"></i>  ผู้อำนวยการเห็นชอบ';
            }else{
                return '<i class="bi bi-x-circlef"></i> ผู้อำนวยการไม่เป็นชอบ';
            }
        }

        if($this->status == 4){
                return 'ตรวจรับวัสดุ';
        }

        if($this->status == 5){
            return 'รับเข้าคลัง';
    }

    if($this->status == 6){
        return 'ส่งบัญชี';
}

    }

    public function ListPoStatus()
    {
        return Categorise::find()->where(['name' => 'po_status'])->all();
    }

        //รวมราคา
        public static function SumOrderPrice()
        {
            return static::find()
            ->where(['is not', 'pr_number', null])
            ->andWhere(['name' => 'order_item'])
                ->sum('price');
        }

    //นับจำนวนใบขอซื้อ
    public static function countPrOrder()
    {
        return static::find()
            ->where(['is not', 'pr_number', null])
            ->andWhere(['name' => 'order'])
            ->count();
    }

    //นับจำนวนทะเบียนคุม
    public static function countPqOrder()
    {
        return static::find()
            ->where(['is not', 'pq_number', null])
            ->andWhere(['name' => 'order'])
            ->count();
    }

    //นับจำนวนใบสั่งซื้อ
    public static function countPoOrder()
    {
        return static::find()
            ->where(['is not', 'po_number', null])
            ->andWhere(['name' => 'order'])
            ->count();
    }
}
