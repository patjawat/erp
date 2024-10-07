<?php

namespace app\modules\purchase\models;

use app\components\SiteHelper;
use app\components\AppHelper;
use app\components\CategoriseHelper;
use app\components\UserHelper;
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
    public $action;
    public $old_data;
    public $auto_lot;
    public $order_type_name;
    public $vendor_name;
    public $vendor_address;
    public $vendor_phone;
    public $vendor_tax;
    public $account_name;
    public $account_number;
    public $set_date; // set ค่าลงวันที่

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
            [['qty', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['price'], 'number'],
            [[
                'data_json',
                'created_at',
                'updated_at',
                'deleted_at',
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
                'action',
                'old_data',
                'auto_lot',
                'q',
                'set_date',
                'vendor_name',
                'order_type_name'
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


    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // แปลงวันที่จาก พ.ศ. เป็น ค.ศ. ก่อนบันทึกในฐานข้อมูล
            $this->thai_year = AppHelper::YearBudget();

            return true;
        } else {
            return false;
        }
    }



    public function afterFind()
    {

        // try {

        $this->vatType = isset($this->data_json['vat']) ? $this->data_json['vat'] : '-';

        $vendor = $this->vendor;
        $this->vendor_name = isset($this->data_json['vendor_name']) ? $this->data_json['vendor_name'] : '-';
        $this->vendor_address = isset($this->data_json['vendor_address']) ? $this->data_json['vendor_address'] : '-';
        $this->vendor_phone = isset($this->data_json['vendor_phone']) ? $this->data_json['vendor_phone'] : '-';
        $this->account_name = isset($this->data_json['account_name']) ? $this->data_json['account_name']  : '-';
        $this->account_number = isset($this->data_json['account_number']) ? $this->data_json['account_number']  : '-';

        // } catch (\Throwable $th) {
        // }

        parent::afterFind();
    }


    // relation

    //เชื่อมกับ ผู้จำหน่าย
    public function getVendor()
    {
        return $this->hasOne(Categorise::class, ['code' => 'vendor_id'])->andOnCondition(['name' => 'vendor']);
    }

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

    //คำนวนเวลารับประหัน
    public function deliveryDay()
    {


        $sql = "SELECT 
            CASE
                WHEN TIMESTAMPDIFF(YEAR, :dateStart, :dateEnd) >= 1 THEN CONCAT(TIMESTAMPDIFF(YEAR, :dateStart, :dateEnd), ' ปี')
                WHEN TIMESTAMPDIFF(MONTH, :dateStart, :dateEnd) >= 1 THEN CONCAT(TIMESTAMPDIFF(MONTH, :dateStart, :dateEnd), ' เดือน')
                ELSE CONCAT(DATEDIFF(:dateEnd, :dateStart), ' วัน')
            END AS time_difference;";

        // $delivery_date = AppHelper::convertToGregorian($this->data_json['delivery_date']);
        // $warranty_date = AppHelper::convertToGregorian($this->data_json['warranty_date']);
        $delivery_date = $this->data_json['delivery_date'];
        $warranty_date = $this->data_json['warranty_date'];

        return Yii::$app->db->createCommand($sql)
            ->bindValue(':dateStart', $delivery_date)
            ->bindValue(':dateEnd', $warranty_date)
            ->queryScalar();
    }

    //แสดงข้อมูลผู่ตรวจสอบ
    public function showChecker()
    {
        return [
            'leader' => $this->getCheckerLeader(),
            'officer' => $this->getCheckerOfficer(),
        ];
    }

    //    หัวหน้าเห็นชอบ
    public function getCheckerLeader()
    {
        try {
            if ($this->data_json['pr_leader_confirm'] == 'N') {
                $userId = $this->data_json['leader1'];
                $text = '<i class="fa-regular fa-circle-stop text-danger"></i> ไม่เห็นชอบ';
            }

            if ($this->data_json['pr_officer_checker'] == 'N') {
                $userId = $this->data_json['leader1'];
                $text = '<i class="fa-regular fa-circle-stop text-danger"></i> ตรวจสอบไม่ผ่าน';
            }

            if ($this->data_json['pr_director_confirm'] == 'N') {
                // $userId = $this->data_json['leader1'];
                return '<i class="fa-regular fa-circle-stop text-danger"></i> ไม่อนุมัติ';
            }

            if ($this->data_json['pr_leader_confirm'] == '') {
                $userId = $this->data_json['leader1'];
                $text = '<i class="fa-regular fa-clock text-warning"></i> รอเห็นชอบ';
            } elseif ($this->data_json['pr_leader_confirm'] == 'Y' && $this->data_json['pr_officer_checker'] == '') {
                $userId = $this->data_json['leader1'];
                $text = '<i class="fa-regular fa-circle-check text-success fs-6"></i> เห็นชอบ | <i class="fa-regular fa-clock text-warning"></i> รอตรวจสอบ';
            } elseif ($this->data_json['pr_leader_confirm'] == 'Y' && $this->data_json['pr_officer_checker'] == 'Y' && $this->data_json['pr_director_confirm'] == '') {
                $userId = $this->data_json['pr_officer_checker_id'];
                $text = '<i class="fa-regular fa-circle-check text-success"></i> ตรวจสอบผ่าน | <i class="fa-regular fa-clock text-warning"></i> รออนุมัติ';
            } elseif ($this->data_json['pr_leader_confirm'] == 'Y' && $this->data_json['pr_officer_checker'] == 'Y' && $this->data_json['pr_director_confirm'] == 'Y') {
                return  '<i class="fa-regular fa-circle-check text-success"></i> อนุมัติ';
            }

            $employee = Employees::find()->where(['id' => $userId])->one();

            return $employee->getAvatar(false, $text);
        } catch (\Throwable $th) {
            return null;
        }
    }

    public function getCheckerOfficer()
    {

        try {
            $userId = $this->data_json['pr_officer_checker_id'];
            $employee = Employees::find()->where(['user_id' => $userId])->one();
            if ($this->data_json['pr_officer_checker']  == 'Y') {
                $text = '<i class="fa-regular fa-circle-check text-success"></i> เห็นชอบ';
            } else {
                $text = '<i class="fa-regular fa-circle-stop text-danger"></i> ไม่เห็นชอบ';
            }

            return $employee->getAvatar(false, $text);
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
            'avatar' => $img,
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
        return $employee->getAvatar(false, $this->data_json['order_type_name']);
    }

    // Avatar ของฉัน
    public  function getMe($msg = null)
    {
        return UserHelper::getMe($msg);
    }
    // ผู้ขอ
    public function getUserReq($msg =null)
    {
        try {
            $employee = Employees::find()->where(['user_id' => $this->created_by])->one();
            $a = $this->data_json['product_type_name'];
            // $text = $msg ? $msg : ($this->viewCreatedAt() . ' | ' . $employee->departmentName());
            $text = $msg ? $msg : ($this->viewCreatedAt());
            return [
                'employee' => $employee,
                'avatar' => $employee->getAvatar(false, $text),
                'department' => $employee->departmentName(),
                'fullname' => $employee->fullname,
                'position_name' => $employee->data_json['position_name_text'].$employee->data_json['position_level_text'],
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
                'position_name' => $employee->data_json['position_name_text'].$employee->data_json['position_level_text']
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

    //แสดงรายการกรรมการ
    public function ListCommittee()
    {
        return self::find()
            ->where(['name' => 'committee', 'category_id' => $this->id])
            ->orderBy(new \yii\db\Expression("JSON_EXTRACT(data_json, '\$.committee') asc"))
            ->all();
    }

    // กรรมการกำหนดรายละเอียด
    public function ListCommitteeDetail()
    {
        return self::find()
            ->where(['name' => 'committee_detail', 'category_id' => $this->id])
            ->orderBy(new \yii\db\Expression("JSON_EXTRACT(data_json, '\$.committee') asc"))
            ->all();
    }

    // คณะกรรมการ
    public function ShowCommittee()
    {
        try {
            $employee = Employees::find()->where(['id' => $this->data_json['employee_id']])->one();

            return [
                'avatar' => $employee->getAvatar(false),
                'fullname' => $employee->fullname,
                'department' => $employee->departmentName()
            ];
        } catch (\Throwable $th) {
            return [
                'avatar' => '',
                'fullname' => '',
                'department' => ''
            ];
        }
    }


    //  ภาพทีมคณะกรรมการ
    public function StackComittee()
    {
        // try {
        $data = '';
        $data .= '<div class="avatar-stack">';
        foreach (Order::find()->where(['name' => 'committee', 'category_id' => $this->id])->all() as $key => $item) {
            $emp = Employees::findOne(['id' => $item->data_json['employee_id']]);
            $data .= Html::a(
                Html::img('@web/img/placeholder-img.jpg', ['class' => 'avatar-sm rounded-circle shadow lazyload blur-up',
        'data' => [
            'expand' => '-20',
            'sizes' => 'auto',
            'src' =>$emp->showAvatar()
            ]
    ]),
                ['/purchase/order-item/update', 'id' => $item->id, 'name' => 'committee', 'title' => '<i class="fa-regular fa-pen-to-square"></i> กรรมการตรวจรับ'],
                [
                    'class' => 'open-modal',
                    'data' => [
                        'size' => 'modal-md',
                        "bs-trigger" => "hover focus",
                        "bs-toggle" => "popover",
                        "bs-placement" => "top",
                        "bs-title" => $item->data_json['committee_name'],
                        "bs-html" => "true",
                        "bs-content" => $emp->fullname . "<br>" . $emp->positionName()
                    ]
                ]
            );
        }
        $data .= '</div>';
        return $data;
        // } catch (\Throwable $th) {
        // }
    }


    //  ภาพทีมคณะกรรมการกำหนดรายละเอียด
    public function StackComitteeDetail()
    {
        try {
            $data = '';
            $data .= '<div class="avatar-stack">';
            foreach (Order::find()->where(['name' => 'committee_detail', 'category_id' => $this->id])->all() as $key => $item) {
                $emp = Employees::findOne(['id' => $item->data_json['employee_id']]);
                $data .= Html::a(
                    Html::img($emp->ShowAvatar(), ['class' => 'avatar-sm rounded-circle shadow']),
                    ['/purchase/order-item/update', 'id' => $item->id, 'name' => 'committee', 'title' => '<i class="fa-regular fa-pen-to-square"></i> กรรมการตรวจรับ'],
                    [
                        'class' => 'open-modal',
                        'data' => [
                            'size' => 'modal-md',
                            "bs-trigger" => "hover focus",
                            "bs-toggle" => "popover",
                            "bs-placement" => "top",
                            "bs-title" => $item->data_json['committee_name'],
                            "bs-html" => "true",
                            "bs-content" => $emp->fullname . "<br>" . $emp->positionName()
                        ]
                    ]
                );
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
    //นับจำนวนทั้งหมด
    public function SumQty()
    {
        try {
            $query = Yii::$app
                ->db
                ->createCommand('SELECT sum(qty) as total FROM `orders` WHERE category_id = :category_id;')
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
        return $name;
    }


    // คำนวน vat 
    function calculateVAT()
    {
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
                'vat' => number_format($vatAmount, 2),
                'total' => number_format($priceWithoutVAT, 2)
            ];
        } else if ($vat == 'EX') {
            $EX_vatAmount = $priceWithoutVAT * ($vatRate / 100);
            $EX_priceWithVAT =  $priceWithVAT + $vatAmount;
            return [
                'price' => $this->SumPo(),
                'vat' => number_format($EX_vatAmount, 2),
                'total' => number_format($EX_priceWithVAT, 2)
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


    //แสดงปีงบประมานทั้งหมดใน order
    public function ListGroupYear()
    {
        $model = self::find()
        ->select('thai_year')
        ->where(['name' => 'order'])
        ->groupBy('thai_year')
        ->all();
        return ArrayHelper::map($model,'thai_year','thai_year');
    }
    // แสดงชื่อคณะกรรมการ
    // public function getBoard()
    // {
    //     return self::find()->where(['category_id' => $this->id, 'name' => 'board'])->all();
    // }

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
        return ArrayHelper::map(Categorise::find()->where(['name' => 'asset_type'])->all(), 'code', 'title');
        // return ArrayHelper::map(Categorise::find()->andWhere(['in', 'name', ['product_type', 'asset_type', 'food_type', 'service_type']])->all(), 'code', 'title');
        // return ArrayHelper::map(Categorise::find()->andWhere(['name' =>'asst_item'])->all(), 'code',function($model){
        //     return $model->category_id.' - '.$model->title;
        // });
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

    public function ListItemTypeOrder()
    {
        $arr = [];
        try {

        $variable =  self::find()->where(['name' => 'order'])->all();
        foreach ($variable as $model) {
            $arr[] = ['id' => $model->data_json['order_type_name'],'name' => $model->data_json['order_type_name']];
        }
        return $arr;
                    //code...
                } catch (\Throwable $th) {
                    return $arr;
                }
    }

    //ร้อยละดำเนินการ
    function OrderProgress()
    {
        $total = Categorise::find()->where(['name' => 'order_status'])->count('id');
        $status = $this->status;
        if ($total == 0) {
            return 0;
        }
        return number_format((($status / $total) * 100), 0);
    }

    public function viewCreated()
    {
        return AppHelper::timeDifference($this->created_at);
    }

    public function viewUpdated()
    {
        return AppHelper::timeDifference($this->updated_at);
    }

    //แสดงสถานะ
    public function viewStatus()
    {
        $status = Categorise::find()->where(['name' => 'order_status', 'code' => $this->status])->one();
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

    // //รวมราคา
    // public static function SumOrderPrice()
    // {
    //     return static::find()
    //         ->where(['is not', 'pr_number', null])
    //         ->andWhere(['name' => 'order_item'])
    //         ->sum('price');
    // }

// 
    // รวมเงินทั้งหมด
   public function SummaryTotal()
   {
    return self::find()
    ->alias('o')
    ->innerJoin('orders i', 'i.category_id = o.id AND i.name = "order_item"')
    ->select(new Expression('FORMAT(IFNULL(SUM(i.price * i.qty), 0), 2) AS total'))
    ->andFilterWhere(['o.thai_year' => $this->thai_year])
    ->scalar();
   } 
// ผลรวมตามประเภทเงิน
   public function SummaryBudgetType()
   {

    // $sql = "SELECT b.code, b.title, IFNULL(SUM(i.price * i.qty), 0) AS total
    //     FROM categorise b
    //     LEFT JOIN orders o ON JSON_UNQUOTE(o.data_json->'$.pq_budget_type') = b.code
    //     LEFT JOIN orders as i ON i.category_id = o.id AND i.name = 'order_item'
    //     WHERE 
    //         b.`name` LIKE 'budget_type'
    //         AND b.code <> 8
    //         AND o.thai_year = :thai_year  -- Added condition here
    //     GROUP BY 
    //         b.code";
    $sql = "SELECT b.code,b.title,IFNULL(sum(i.price * i.qty),0) AS total
        FROM categorise b
        LEFT JOIN orders o ON JSON_UNQUOTE(o.data_json->'$.pq_budget_type') = b.code  
        LEFT JOIN orders as i ON i.category_id = o.id AND i.name = 'order_item'         
        WHERE b.`name` LIKE 'budget_type'
        AND b.code <> 8
        -- AND o.thai_year = :thai_year
             GROUP BY 
             
             b.code";

return  Yii::$app->db->createCommand($sql)
// ->bindValue(':thai_year',$this->thai_year)

->queryAll();
    // $model =   Categorise::find()
    // ->select(['b.code', new Expression('IFNULL(SUM(i.price * i.qty), 0) AS total')])
    // ->alias('b')
    // ->leftJoin('orders o', new Expression("JSON_UNQUOTE(o.data_json->'$.pq_budget_type') = b.code"))
    // ->leftJoin('orders i', 'i.category_id = o.id AND i.name = "order_item"')
    // ->where(['b.name' => 'budget_type'])
    // ->andWhere(['<>', 'b.code', 8])
    // ->andFilterWhere(['o.thai_year' => $this->thai_year])
    // ->groupBy('b.code');
    // ->all();
    // return $model;

    // $totalQuery = (new \yii\db\Query())
    // ->select(['b.code', 'b.title', 'IFNULL(SUM(i.price * i.qty), 0) AS total'])
    // ->from(['b' => Categorise::tableName()])
    // ->leftJoin('orders o', "JSON_UNQUOTE(o.data_json->'$.pq_budget_type') = b.code")
    // ->leftJoin('orders i', 'i.category_id = o.id AND i.name = "order_item"')
    // ->where(['like', 'b.name', 'budget_type'])
    // ->andWhere(['<>', 'b.code', 8])
    // ->andFilterWhere(['o.thai_year' => $this->thai_year])
    // ->groupBy('b.code');

    // return $totalQuery->all();
   }
    //ผลรวมวัสดุ
    public function SummaryMaterial($month)
    {
        $model = self::find()
            ->alias('o')
            ->innerJoin('orders i', 'i.category_id = o.id AND i.name = "order_item"')
            ->innerJoin('categorise item', 'item.code = i.asset_item AND item.name = "asset_item"')
            ->where(['o.group_id' => 4])
            ->andWhere(new Expression('MONTH(i.created_at) = :month', [':month' => $month]))
            ->andFilterWhere(['o.thai_year' => $this->thai_year])
            ->sum(new Expression('IFNULL(i.price * i.qty, 0)'));
        return  $model;
    }

        //ผลรวมวัสดุ
        public function SummaryAsset($month)
        {
            $model = self::find()
                ->alias('o')
                ->innerJoin('orders i', 'i.category_id = o.id AND i.name = "order_item"')
                ->innerJoin('categorise item', 'item.code = i.asset_item AND item.name = "asset_item"')
                ->where(['o.group_id' => 3])
                ->andWhere(new Expression('MONTH(i.created_at) = :month', [':month' => $month]))
                ->andFilterWhere(['o.thai_year' => $this->thai_year])
                ->sum(new Expression('IFNULL(i.price * i.qty, 0)'));
            return  $model;
        }
        //ผลรวมจ้างเหมา
        public function SummaryOutsource($month)
        {
            $model = self::find()
            ->alias('o')
            ->innerJoin('orders i', 'i.category_id = o.id AND i.name = "order_item"')
            ->innerJoin('categorise item', 'item.code = i.asset_item AND item.name = "asset_item"')
            ->where(['item.category_id' => 'M25'])
            ->andWhere(new Expression('MONTH(i.created_at) = :month', [':month' => $month]))
            ->andFilterWhere(['o.thai_year' => $this->thai_year])
            ->sum(new Expression('IFNULL(i.price * i.qty, 0)'));
            return  $model;
        }

        
    //นับจำนวนใบขอซื้อ
    public function prSummery()
    {
        // $price = Yii::$app->db->createCommand("SELECT IFNULL(SUM(i.qty * i.price),0) as total FROM `orders`  i INNER JOIN orders o ON o.id = i.category_id WHERE i.name = 'order_item' AND o.status = 1")->queryScalar();
        $total =  static::find()
        ->where(['name' => 'order', 'status' => 1])
        ->andFilterWhere(['thai_year' => $this->thai_year])
        ->count();
        $price = self::find()
                    ->alias('o')
                    ->innerJoin('orders i', 'o.id = i.category_id')
                    ->where(['i.name' => 'order_item', 'o.status' => 1])
                    ->andFilterWhere(['o.thai_year' => $this->thai_year])
                    ->sum(new Expression('IFNULL(i.qty * i.price, 0)'));
        return [
            'total' => $total,
            'price' => isset($price) ? $price : 0
        ];
    }

    //นับจำนวนทะเบียนคุม
    public static function pqSummery()
    {
        $total =  $total =  static::find()->where(['name' => 'order', 'status' => 2])->count();
        $price = Yii::$app->db->createCommand("SELECT IFNULL(SUM(i.qty * i.price),0) as total FROM `orders`  i INNER JOIN orders o ON o.id = i.category_id WHERE i.name = 'order_item' AND o.status = 2")->queryScalar();

        return [
            'total' => $total,
            'price' => $price
        ];
    }

    //นับจำนวนใบสั่งซื้อ
    public static function poSummery()
    {
        $total =  static::find()->where(['name' => 'order', 'status' => 3])->count();
        $price = Yii::$app->db->createCommand("SELECT IFNULL(SUM(i.qty * i.price),0) as total FROM `orders`  i INNER JOIN orders o ON o.id = i.category_id WHERE i.name = 'order_item' AND o.status = 3")->queryScalar();
        return [
            'total' => $total,
            'price' => $price
        ];
    }

        //ดำเนินการสงบัญชี เสร็จสิ้น
        public static function orderSuccess()
        {
            $total =  static::find()->where(['name' => 'order', 'status' => 6])->count();
            $price = Yii::$app->db->createCommand("SELECT IFNULL(SUM(i.qty * i.price),0) as total FROM `orders`  i INNER JOIN orders o ON o.id = i.category_id WHERE i.name = 'order_item' AND o.status = 3")->queryScalar();
            return [
                'total' => $total,
                'price' => $price
            ];
        }

}
