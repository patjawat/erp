<?php

namespace app\modules\am\models;

use app\components\AppHelper;
use app\components\CategoriseHelper;
use app\models\Categorise;
use app\modules\am\models\AssetItem;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\filemanager\models\Uploads;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

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
 */
class Asset extends \yii\db\ActiveRecord

{
    /**
     * {@inheritdoc}
     */
    public $show;
    public $asset_name;
    public $budget_year;
    public $supvendor;
    public $method_get;
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
    public $fsn_auto; //กำหนดการให้หมายเลขอัตโนมัติถ้า true;
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
            [['price', 'asset_status'], 'required'],
<<<<<<< HEAD
            [['on_year', 'receive_date', 'data_json','repair', 'updated_at', 'created_at', 'asset_name', 'asset_item', 'fsn_number', 'code', 'qty', 'fsn_auto', 'type_name', 'show', 'asset_group', 'asset_type', 'q', 'budget_type', 'purchase', 'owner', 'price1', 'price2', 'q_department', 'q_date', 'q_receive_date', 'q_month', 'q_year', 'department_name', 'asset_option', 'method_get','q_lastDay'], 'safe'],
=======
            [['on_year', 'receive_date', 'data_json', 'updated_at', 'created_at', 'asset_name', 'asset_item', 'fsn_number', 'code', 'qty', 'fsn_auto', 'type_name', 'show', 'asset_group', 'asset_type', 'q', 'budget_type', 'purchase', 'owner', 'price1', 'price2', 'q_department', 'q_date', 'q_receive_date', 'q_month', 'q_year', 'department_name', 'asset_option', 'method_get','q_lastDay', 'item_options'], 'safe'],
>>>>>>> refs/remotes/origin/main
            [['price'], 'number'],
            [['code'], 'unique'],
            [['life', 'department', 'depre_type', 'created_by', 'updated_by'], 'integer'],
            [['ref', 'code'], 'string', 'max' => 255],
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
            'asset_item' => 'ชื่อครุภัณฑ์',
            'fsn_number' => 'หมายเลขครุภัณฑ์',
            'receive_date' => 'วันที่รับเข้า',
            'on_year' => 'ปีงบประมาณ',
            'price' => 'ราคา',
            'life' => 'อายุการใช้งาน',
            'department' => 'ประจำอยู่หน่วยงาน',
            'depre_type' => 'ประเภทค่าเสื่อมราคา',
            'budget_year' => 'งบประมาณ',
            'asset_status' => 'สถานะ',
            'data_json' => 'Data Json',
            'updated_at' => 'วันเวลาแก้ไข',
            'created_at' => 'วันเวลาสร้าง',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
        ];
    }

// แสดงรูปภาพ
    public function ShowImg()
    {
        try {
            $model = Uploads::find()->where(['ref' => $this->ref, 'name' => 'asset'])->one();
            if ($model) {
                return FileManagerHelper::getImg($model->id);
            } else {
                return Yii::getAlias('@web') . '/img/placeholder-img.jpg';
            }
        } catch (\Throwable $th) {
            return Yii::getAlias('@web') . '/img/placeholder-img.jpg';
        }

    }

    //
    //ค่าเสื่อม
    public function xx()
    {
        $a = $this->price * 100;
        return $a;
    }
    //รูปภาพ

    //อายุ

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
            $this->asset_name = isset($this->data_json['asset_name_text']) ? $this->data_json['asset_name_text'] : '';
            $this->serial_number = isset($this->data_json['serial_number']) ? $this->data_json['serial_number'] : '';
            $this->type_name = isset($this->data_json['item']['data_json']['asset_type']['title']) ? $this->data_json['item']['data_json']['asset_type']['title'] : '';
            $this->vendor_name = isset($this->data_json['vendor']) ? $this->data_json['vendor']['title'] : '';
            $this->purchase_text = isset($this->data_json['purchase_text']) ? $this->data_json['purchase_text'] : '';
            $this->department_name = isset($this->data_json['department_name']) ? $this->data_json['department_name'] : '';
        } catch (\Throwable $th) {
            //throw $th;
        }

        // $this->asset_name = isset($this->data_json['name']) ? $this->data_json['name'] : '-';

        parent::afterFind();
    }

    public function beforeSave($insert)
    {

        try {

        // $this->receive_date = AppHelper::DateToDb($this->receive_date);

        if ($this->asset_group == 2) {
            // try {

            $vendor = isset($this->data_json['vendor_id']) ? Categorise::find()->where(['code' => $this->data_json['vendor_id'], 'name' => 'vendor'])->one() : '';
            // $Assetitem = AssetItem::find()->where(['code' => $this->asset_item,'name' => 'asset'])->one();
            $department = Organization::find()->where(['id' => $this->department])->one();
            $array2 = [
                'vendor_id' => isset($this->data_json['vendor_id']) ? $this->data_json['vendor_id'] : '',
                'vendor' => $vendor,
                // 'item' => $Assetitem,
                'department_name' => isset($department) ? $department->name : '',
                // 'service_life' => '',
                // 'depreciation' => '',
                // 'asset_name' => $Assetitem->title,
                // 'asset_type_text' => $Assetitem->assetType->title,
                // 'service_life' => $Assetitem->assetType->data_json['service_life'],
                // 'depreciation' => $Assetitem->assetType->data_json['depreciation'],
                // 'asset_type_text' => isset(CategoriseHelper::CategoriseByCodeName($this->data_json['asset_type'],'asset_type')->title) ? CategoriseHelper::CategoriseByCodeName($this->data_json['asset_type'],'asset_type')->title : '',
                'budget_type_text' => isset(CategoriseHelper::CategoriseByCodeName($this->data_json['budget_type'], 'budget_type')->title) ? CategoriseHelper::CategoriseByCodeName($this->data_json['budget_type'], 'budget_type')->title : '',
                'method_get_text' => isset(CategoriseHelper::CategoriseByCodeName($this->data_json['method_get'], 'method_get')->title) ? CategoriseHelper::CategoriseByCodeName($this->data_json['method_get'], 'method_get')->title : '',
                'purchase_text' => isset(CategoriseHelper::CategoriseByCodeName($this->purchase, 'purchase')->title) ? CategoriseHelper::CategoriseByCodeName($this->purchase, 'purchase')->title : '',
            ];
            $this->data_json = ArrayHelper::merge($this->data_json, $array2);
                        //code...
    
        }

        if ($this->asset_group == 3) {
            // try {

            $vendor = isset($this->data_json['vendor_id']) ? Categorise::find()->where(['code' => $this->data_json['vendor_id'], 'name' => 'vendor'])->one() : '';
            $Assetitem = AssetItem::find()->where(['code' => $this->asset_item, 'name' => 'asset_item'])->one();
            $department = Organization::find()->where(['id' => $this->department])->one();
            $array2 = [
                'vendor_id' => isset($this->data_json['vendor_id']) ? $this->data_json['vendor_id'] : '',
                'vendor' => $vendor,
                'item' => $Assetitem,
                'department_name' => isset($department) ? $department->name : '',
                // 'service_life' => '',
                // 'depreciation' => '',
                'asset_name' => $Assetitem->title,
                'asset_type_text' => $Assetitem->assetType->title,
                'service_life' => $Assetitem->assetType->data_json['service_life'],
                'depreciation' => $Assetitem->assetType->data_json['depreciation'],
                // 'asset_type_text' => isset(CategoriseHelper::CategoriseByCodeName($this->data_json['asset_type'],'asset_type')->title) ? CategoriseHelper::CategoriseByCodeName($this->data_json['asset_type'],'asset_type')->title : '',
                'budget_type_text' => isset(CategoriseHelper::CategoriseByCodeName($this->data_json['budget_type'], 'budget_type')->title) ? CategoriseHelper::CategoriseByCodeName($this->data_json['budget_type'], 'budget_type')->title : '',
                'method_get_text' => isset(CategoriseHelper::CategoriseByCodeName($this->data_json['method_get'], 'method_get')->title) ? CategoriseHelper::CategoriseByCodeName($this->data_json['method_get'], 'method_get')->title : '',
                'purchase_text' => isset(CategoriseHelper::CategoriseByCodeName($this->purchase, 'purchase')->title) ? CategoriseHelper::CategoriseByCodeName($this->purchase, 'purchase')->title : '',
                'item_options' => $this->item_options
            ];
            $this->data_json = ArrayHelper::merge($this->data_json, $array2);

            // สร้างรหัสอัตโนมัติ
            if ($this->fsn_auto == "1") {
                $year = substr((date('Y') + 543), -2, 2);
                $number = $this->asset_item . '/' . $year . '.';
                $this->code = \mdm\autonumber\AutoNumber::generate($number . '?');
            }

            // } catch (\Throwable $th) {
            //     //throw $th;
            // }
        }
    } catch (\Throwable $th) {
        //throw $th;
    }

        return parent::beforeSave($insert);
    }



        //Relationships
        public function getAssetItem()
        {
            return $this->hasOne(Categorise::class, ['code' => 'asset_item'])->andOnCondition(['name' => 'asset_item']);
        }

    public function Upload($ref, $name)
    {
        return FileManagerHelper::FileUpload($ref, $name);
    }

    // List Category
    // ค่าเสื่อม
    // public function ListDepreType()
    // {
    //     return [
    //         '1' => 'ครุภัณฑ์สำนักงาน',
    //         '2' => 'ครุภัณฑ์ไฟฟ้าและวิทยุ',
    //         '3' => 'ครุภัณฑ์ไฟฟ้าและวิทยุ เครื่องกำเนิดไฟฟ้า',
    //         '4' => 'ครุภัณฑ์โฆษณาและเผยแพร่',
    //         '5' => 'ครุภัณฑ์การเกษตร เครื่องมือและอุปกรณ์',
    //         '6' => 'ครุภัณฑ์การเกษตร เครื่องจักรกล',
    //         '7' => 'ครุภัณฑ์โรงงาน เครื่องมือและอุปกรณ์',
    //         '8' => 'ครุภัณฑ์โรงงาน เครื่องจักรกล',
    //         '9' => 'ครุภัณฑ์ก่อสร้าง เครื่องมือและอุปกรณ์',
    //         '10' => 'ครุภัณฑ์ก่อสร้าง เครื่องจักรกล',
    //         '11' => 'ครุภัณฑ์สำรวจ',
    //         '12' => 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์',
    //         '13' => 'ครุภัณฑ์คอมพิวเตอร์',
    //         '14' => 'ครุภัณฑ์การศึกษา',
    //         '15' => 'ครุภัณฑ์โรงงาน เครื่องมือและอุปกรณ์',
    //         '16' => 'ครุภัณฑ์งานบ้านงานครัว',
    //         '17' => 'ครุภัณฑ์กีฬา/กายภาพ',
    //         '18' => 'ครุภัณฑ์ดนตรี/นาฎศิลป์',
    //         '19' => 'ครุภัณฑ์อาวุธ',
    //         '20' => 'ครุภัณฑ์สนาม',
    //         '21' => 'ครุภัณฑ์อื่น',

    //     ];
    //     //  return CategoriseHelper::Nationality();
    // }

// มูลค่าทรัพย์สินทั้งหมด
    public function TotalPrice()
    {
        return self::find()->select('price')->sum();
    }

    //มูลค่าครุภัณฑ์
    public function TotalPriceByGroup2()
    {
        return self::find()->select('price')->where(['asset_group' => 2])->sum();
    }

    //นับจำนวนรายการที่อยู่ในประเภท
    public function CountItemOnType()
    {
        $id = $this->code;
        $sql = "SELECT count(c.id) FROM categorise c
          LEFT JOIN categorise t ON t.code = c.category_id
           WHERE c.name = 'asset_item'
           AND t.category_id = :id";
        $query = Yii::$app->db->createCommand($sql)
            ->bindParam(':id', $id)
            ->queryScalar();
        return $query;
    }

    //ดึงข้อมูลครุภัรฑ์
    public function AssetitemName()
    {
        return isset($this->data_json['asset_name']) ? $this->data_json['asset_name'] : null;
    }

    //ดึงข้อประเภท
    public function AssetTypeName()
    {
        return isset($this->data_json['asset_type_text']) ? $this->data_json['asset_type_text'] : null;
    }

    public function statusName()
    {
        $model = CategoriseHelper::CategoriseByCodeName($this->asset_status, 'asset_status');
        if ($model) {
            return $model->title;
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

    public function ListType()
    {
        return ArrayHelper::map(AssetItem::find()->where(['name' => 'asset_type'])->all(), 'code', 'title');
    }

    public function ListAssetitem()
    {
        return ArrayHelper::map(AssetItem::find()->where(['name' => 'asset_item'])->all(), 'code', 'title');
    }

        // แสดงรายการอาคารสิ่งก่อสร้าง
        public function ListBuildingItems()
        {
            return ArrayHelper::map(AssetItem::find()
            ->where(['name' => 'asset_item'])
            ->andWhere(['category_id' => 8])
            ->all(), 'code', 'title');
        }

    public function ListVendor()
    {
        return CategoriseHelper::Vendor();
    }

    public function ListMethodget()
    {
        return ArrayHelper::map(AssetItem::find()->where(['name' => 'method_get'])->all(), 'code', 'title');
    }


    public function ListPurchase()
    {
        return ArrayHelper::map(AssetItem::find()->where(['name' => 'purchase'])->all(), 'code', 'title');

    }

    public function ListOnYear()
    {
        $sql = "SELECT on_year FROM `asset` WHERE on_year IS NOT NULL GROUP BY on_year DESC";
        $query = Yii::$app->db->createCommand($sql)->queryAll();
        return ArrayHelper::map($query, 'on_year', 'on_year');

    }

    public function ListBudgetdetail()
    {
        return ArrayHelper::map(AssetItem::find()->where(['name' => 'budget_type'])->all(), 'code', 'title');

    }

    public function ListAssetstatus()
    {
        return ArrayHelper::map(AssetItem::find()->where(['name' => 'asset_status'])->all(), 'code', 'title');

    }

    public function ListMaintainpm()
    {
        return ArrayHelper::map(AssetItem::find()->where(['name' => 'maintain_pm'])->all(), 'code', 'title');

    }

    public function ListTestcal()
    {
        return ArrayHelper::map(AssetItem::find()->where(['name' => 'test_cal'])->all(), 'code', 'title');

    }

    public function ListAssetrisk()
    {
        return ArrayHelper::map(AssetItem::find()->where(['name' => 'asset_risk'])->all(), 'code', 'title');
    }

    //ผู้รับผิดชอบ
    public function getOwner()
    {
        try {
            $cid = $this->owner;
            $employee = Employees::find()->where(['cid' => $cid])->one();
            return $employee->getAvatar(false);
        } catch (\Throwable $th) {
            return null;
        }

    }
    public function Retire()
    {
        try {

            $birthday = AppHelper::DateToDb($this->receive_date);
            $age = $this->data_json["service_life"];
            $color = "";

            if (substr($birthday, 5, 2) >= 10) {
                $age += 1;
            }
            // ถ้าเลยปีงบประมาณแล้ว ให้ไปอยู่ในปีข้างหน้า
            $date_retire = (substr($birthday, 0, 4) + $age) . "-09-30"; //สิ้นปีงบประมาณ หน่วยงานราชการ
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

            //code...
        } catch (\Throwable $th) {
            //throw $th;
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

        $sqlMonth = "SELECT LAST_DAY(:d1) As date";
        $queryMonth = Yii::$app->db->createCommand('SELECT LAST_DAY(:d1)')
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
    (SELECT data_json->'$.service_life' FROM asset WHERE id = :id) as service_life,
    (SELECT (price/CAST(data_json->'$.service_life' as UNSIGNED)) FROM asset WHERE id = :id) as year_price,
    (SELECT (price/CAST(data_json->'$.service_life' as UNSIGNED) / 12) FROM asset WHERE id = :id) as month_price,
    (SELECT CAST(data_json->'$.depreciation' as UNSIGNED) FROM asset WHERE id = :id) as depreciation
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
        $querys = Yii::$app->db->createCommand($sql)
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
        return $this->asset_item == '2310-001-0003' ? true : false;
    }

    //ตรวจสอบว่าเป็นครุภัณฑ์วิทยาศาสตร์และการแพทย์
    public function isMedical()
    {
        try {

            $data = explode('-', $this->asset_item);
            return $data[0] == '6515' ? true : false;
            
        } catch (\Throwable $th) {
            return false;
        }
    }

}
