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
use app\modules\inventory\models\Stock;
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
 * @property int|null $asset_item รายการที่เก็บ
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

    public $q;
    public $vatType;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'orders';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['qty', 'created_by', 'updated_by'], 'integer'],
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
                'to_stock',
                'group_id',
                'asset_item',
                'discount_price',
                'q'
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
            'asset_item' => 'Item ID',
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


    public function afterFind()
    {
        try {

            $this->vatType = isset($this->data_json['vat']) ? $this->data_json['vat'] : '-';
        } catch (\Throwable $th) {
        }

        parent::afterFind();
    }


    // relation


    //เชื่อมกับ รับเข้า Stock
    public function getReceive()
    {
        return $this->hasOne(Stock::class, ['po_number' => 'po_number'])->andOnCondition(['name' => 'receive']);
    }
//เชื่อมกับ รับเข้า Stock Item
    public function getReceiveItem()
    {
        return $this->hasOne(Stock::class, ['po_number' => 'po_number'])->andOnCondition(['name' => 'receive_item']);
    }

    public function getProductType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'category_id'])->andOnCondition(['name' => 'product_type']);
    }
    public function getAssetGroup()
    {
        return $this->hasOne(Categorise::class, ['code' => 'group_id'])->andOnCondition(['name' => 'asset_type']);
    }

    public function getAssetType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'category_id'])->andOnCondition(['name' => 'asset_type']);
    }

    public function getProduct()
    {
        return $this->hasOne(Product::class, ['code' => 'asset_item'])->andOnCondition(['name' => 'asset_item', 'group_id' => $this->group_id]);
    }

    //  uploadFile
    public function Upload($name)
    {
        return FileManagerHelper::FileUpload($this->ref, $name);
    }

    //แสดงข้อมูลผู่ตรวจสอบ
    public function showChecker()
    {
           return [
            'leader' => $this->getCheckerLeader(),
            'officer' => $this->getCheckerOfficer(),
           ];
    }

   
    public function getCheckerLeader()
    {
        try {
            $userId = $this->data_json['leader1'];
        $emp = $this->getEmp($userId);
        if($this->data_json['pr_leader_confirm']  == 'Y'){
            $text = '<i class="fa-regular fa-circle-check text-success"></i> เห็นชอบ';
        }else{
            $text = '<i class="fa-regular fa-circle-stop text-danger"></i> ไม่เห็นชอบ';
        }
        return '<div class="d-flex">'. $emp['avatar'] . '
            <div class="avatar-detail text-truncate">
                <h6 class="mb-1 fs-13">'. $emp['fullname'] . '</h6>
                <p class="text-muted mb-0 fs-13">' . $text.'</p>
            </div>
        </div>';
        } catch (\Throwable $th) {
            return null;
        }
       
    }

    public function getCheckerOfficer()
    {
        try {

        $userId = $this->data_json['pr_officer_checker_id'];
        $emp = $this->getEmp($userId);
        if($this->data_json['pr_officer_checker']  == 'Y'){
            $text = '<i class="fa-regular fa-circle-check text-success"></i> เห็นชอบ';
        }else{
            $text = '<i class="fa-regular fa-circle-stop text-danger"></i> ไม่เห็นชอบ';
        }
        return '<div class="d-flex">'. $emp['avatar'] . '
            <div class="avatar-detail text-truncate">
                <h6 class="mb-1 fs-13">'. $emp['fullname'] . '</h6>
                <p class="text-muted mb-0 fs-13">' . $text.'</p>
            </div>
        </div>';
                    //code...
                } catch (\Throwable $th) {
                   return null;
                }
    }


    public function getEmp($userId)
    {

        $employee = Employees::find()->where(['id' => $userId])->one();
        $img = Html::img($employee->showAvatar(), ['class' => 'avatar avatar-sm bg-primary text-white']);
        return [
            'avatar' =>$img,
            'department' => $employee->departmentName(),
            'fullname' => $employee->fullname,
        ];
        // try {
           
        // } catch (\Throwable $th) {
        //     return [
        //         'avatar' => '',
        //         'department' => '',
        //         'fullname' => '',
        //     ];
        // }


    }

    public function orderAvatar()
    {
        $employee = Employees::find()->where(['user_id' => $this->created_by])->one();
        $img = Html::img($employee->showAvatar(), ['class' => 'avatar avatar-sm bg-primary text-white']);
        return '<div class="d-flex">'
            . $img . '
        <div class="avatar-detail text-truncate">
            <h6 class="mb-1 fs-15"  data-bs-toggle="tooltip" data-bs-placement="top"
            data-bs-custom-class="custom-tooltip"
            data-bs-title="ดูเพิ่มเติม..."><span>'
            . $employee->fullname . '</span>
            </h6>
            <p class="text-muted mb-0 fs-13">' . $this->data_json['order_type_name'].'</p>
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
            $img = Html::img($employee->showAvatar(), ['class' => 'avatar avatar-sm bg-primary text-white']);
            return [
                'avatar' => '<div class="d-flex">'. $img . '
                <div class="avatar-detail text-truncate">
                    <h6 class="mb-1 fs-13">'. $employee->fullname . '</h6>
                    <p class="text-muted mb-0 fs-13">' . $employee->departmentName().'</p>
                </div>
            </div>',
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
    public function ShowCommittee()
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


    //  ภาพทีมคณะกรรมการ
    public function StackComittee()
    {
        try {
            $data = '';
            $data .= '<div class="avatar-stack">';
            foreach (Order::find()->where(['name' => 'committee','category_id' => $this->id])->all() as $key => $avatar) {
                $emp = Employees::findOne(['id' => $avatar->data_json['employee_id']]);
                $data .= '<a href="javascript: void(0);" class="me-1" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-title="' . $emp->fullname . '">';
                $data .= Html::img($emp->ShowAvatar(), ['class' => 'avatar-sm rounded-circle shadow']);
                $data .= '</a>';
            }
            $data .= '</div>';
            return $data;
        } catch (\Throwable $th) {
        }
    }


    //  ภาพทีมคณะกรรมการกำหนดรายละเอียด
    public function StackComitteeDetail()
    {
        try {
            $data = '';
            $data .= '<div class="avatar-stack">';
            foreach (Order::find()->where(['name' => 'committee_detail','category_id' => $this->id])->all() as $key => $avatar) {
                $emp = Employees::findOne(['id' => $avatar->data_json['employee_id']]);
                $data .= '<a href="javascript: void(0);" class="me-1" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-title="' . $emp->fullname . '">';
                $data .= Html::img($emp->ShowAvatar(), ['class' => 'avatar-sm rounded-circle shadow']);
                $data .= '</a>';
            }
            $data .= '</div>';
            return $data;
        } catch (\Throwable $th) {
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
                ->createCommand('SELECT sum(price * qty) as total FROM `orders` WHERE category_id = :category_id;')
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


    function Vat()
    {

        $priceWithoutVAT = $this->SumPo();
        $vatRate = 7; // 7%

        $vatAmount = $priceWithoutVAT * ($vatRate / 100);
        $priceWithVAT = $priceWithoutVAT + $vatAmount;
        return [
            'price' =>  number_format($priceWithoutVAT, 2), //ราคาก่อน VAT
            'price2' =>  number_format($vatAmount, 2), //จำนวนเงิน VAT
            'price3' =>  number_format($priceWithVAT, 2), //ราคาหลังรวม VAT
        ];
    }

    //แสดงชื่อ  vat
    public function vatName()
    {
        switch ($this->vatType) {
            case 'NONE':
                $name =  'ไม่มี';
                break;
            case 'IN':
                $name =  'Vat ใน';
                break;
            case 'EX':
                $name =  'Vat นอก';
                break;
            default:
            $name =  'ไม่ระบุ';
                break;
        }
        return $name ;
    }


    // คำนวน vat 
    function calculateVAT() {
        $price = $this->SumPo();
        $discountAmount = $this->discount_price;
        $vatType = $this->vatType;
        $vatRate = 0.07;
    // ราคาก่อนหักส่วนลด
    $priceBeforeDiscount = $price;
    
    // ราคาหลังหักส่วนลด
    $priceAfterDiscount = $priceBeforeDiscount - $discountAmount;
    
    // คำนวณ VAT
    $vatAmount = 0;
    $priceBeforeVAT = $priceAfterDiscount;
    $priceAfterVAT = $priceAfterDiscount;

    switch ($vatType) {
        case 'IN':
            // ราคาที่ให้มาเป็นราคาหลังรวม VAT แล้ว
            $priceBeforeVAT = $priceAfterDiscount / (1 + $vatRate);
            $vatAmount = $priceAfterDiscount - $priceBeforeVAT;
            $priceAfterVAT = $priceAfterDiscount;
            break;
        case 'EX':
            // ราคาที่ให้มาเป็นราคาก่อน VAT
            $vatAmount = $priceAfterDiscount * $vatRate;
            $priceAfterVAT = $priceAfterDiscount + $vatAmount;
            break;
        case 'NONE':
            // ไม่มีการคิด VAT
            $vatAmount = 0;
            $priceAfterVAT = $priceAfterDiscount;
            break;
        default:
            // หากไม่ตรงกับกรณีใดๆ ให้ถือว่าไม่มีการคิด VAT
            break;
    }

    return [
        'priceBeforeVAT' => round($priceBeforeVAT, 2),
        'priceAfterVAT' => round($priceAfterVAT, 2),
        'vatAmount' => round($vatAmount, 2),
        'priceBeforeDiscount' => round($priceBeforeDiscount, 2),
        'priceAfterDiscount' => round($priceAfterDiscount, 2)
    ];
    }

    public function getVat()
    {

        $priceWithVAT = ($this->SumPo() - $this->discount_price);
        $vatRate = 7; // 7%
        $vatAmount = $priceWithVAT * ($vatRate / (100 + $vatRate));
        $priceWithoutVAT = $priceWithVAT - $vatAmount;

        $vat  = isset($this->data_json['vat'])  ? $this->data_json['vat'] : false;
        if ($vat == 'IN') {
           
            return [
                'price' => $this->SumPo(),
                'vat' => number_format($vatAmount,2),
                'total' => number_format($priceWithoutVAT,2)
            ];
        } else if ($vat == 'EX') {
            $EX_vatAmount = $priceWithoutVAT * ($vatRate / 100);
            $EX_priceWithVAT =  $priceWithVAT + $vatAmount;
            return [
                'price' => $this->SumPo(),
                'vat' => number_format($EX_vatAmount,2),
                'total' => number_format($EX_priceWithVAT,2)
            ];
        } else {
            return [
                'price' => $this->SumPo(),
                'vat' =>  '-',
                 'total' => $priceWithVAT
             ];
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

    // หมวดเงิน
    public function ListBudgetGroup()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'budget_group'])->all(), 'code', 'title');
    }

    

    // ประเภท
    public function ListBudgetdetail()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'budget_type'])->all(), 'code', 'title');
    }

    public function ListProductType()
    {
        return ArrayHelper::map(Categorise::find()->andWhere(['in', 'name', ['product_type', 'asset_type', 'food_type', 'service_type']])->all(), 'code', 'title');
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


    //ร้อยละดำเนินการ
    function OrderProgress() {
        $total = Categorise::find()->where(['name' => 'order_status'])->count('id');
        $status = $this->status;
        if ($total == 0) {
            return 0;
        }
        return number_format((($status / $total) * 100),0);
    }
    
    public function viewCreated(){
            return AppHelper::timeDifference($this->created_at);
    }

    public function viewUpdated(){
        return AppHelper::timeDifference($this->updated_at);
}

    //แสดงสถานะ
    public function viewStatus()
    {
        $status = Categorise::find()->where(['name' => 'order_status','code' => $this->status])->one();
        return [
            'status_name' => isset($status->title) ? $status->title : 'รอดำเนินการ',
            'progress' => $this->OrderProgress(),
            'color' =>  isset($status->data_json['color']) ? $status->data_json['color'] : '',
        ];
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
