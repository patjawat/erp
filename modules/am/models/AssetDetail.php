<?php

namespace app\modules\am\models;

use Yii;
use yii\db\Expression;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\modules\usermanager\models\User;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\hr\models\Employees;

/**
 * This is the model class for table "asset_detail".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $code
 * @property int|null $user_id
 * @property int|null $emp_id
 * @property string|null $name
 * @property string|null $data_json
 * @property string|null $updated_at
 * @property string|null $created_at
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 */
class AssetDetail extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public $ma; // การบำรุงรักษา
    public $accessories_item; //ครุภัณฑ์ภายใน
    public static function tableName()
    {
        return 'asset_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'emp_id', 'created_by', 'updated_by'], 'integer'],
            [[
                'data_json',
                'updated_at',
                'created_at',
                'date_start',
                'date_end',
                'ma',
                'accessories_item',
                'plan_date',
                'actual_date',
                'provider_type',
                'cal_result',
                'is_borrowed',
                'staff_id'
            ], 'safe'],
            [['ref', 'code', 'name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ref' => 'เลขที่อ้างอิง',
            'code' => 'รหัส/หมายเลขทรัพย์สิน',
            'user_id' => 'ผู้ใช้งานระบบ',
            'emp_id' => 'รหัสพนักงาน/ผู้รับผิดชอบ',
            'name' => 'ชื่อรายการครุภัณฑ์',

            // ส่วนการจัดการสอบเทียบ (Calibration)
            'plan_date' => 'วันที่ตามแผน (Plan)',
            'actual_date' => 'วันที่ดำเนินการจริง',
            'provider_type' => 'ประเภทผู้ให้บริการ (Provider)',
            'cal_result' => 'ผลการตรวจสอบ/สอบเทียบ',

            // ส่วนสถานะการหมุนเวียน
            'is_borrowed' => 'สถานะการยืมค้าง',
            'staff_id' => 'ผู้รับคืน', // เก็บ ID พนักงานที่ล็อกอินอยู่
            'data_json' => 'ข้อมูลรายละเอียดเพิ่มเติม (JSON)',
            'updated_at' => 'แก้ไขล่าสุดเมื่อ',
            'created_at' => 'วันที่บันทึก',
            'created_by' => 'ผู้บันทึก',
            'updated_by' => 'ผู้แก้ไข',
        ];
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

    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }
    public function getStaff()
    {
        return $this->hasOne(Employees::class, ['id' => 'staff_id']);
    }


    public function Upload($options = [])
    {
        // เช็กว่ามีการส่ง 'view' => true มาหรือไม่ ถ้าไม่มีให้ default เป็น false
        $view = isset($options['view']) ? $options['view'] : false;
        return FileManagerHelper::FileUpload($this->ref, $this->name, $view);
    }

    public function listImages()
    {
        $ref = $this->ref;
        return FileManagerHelper::listViewImages($ref);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // 1. แปลงวันที่หลัก
            if ($this->date_start && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $this->date_start)) {
                $this->date_start = AppHelper::DateToDb($this->date_start);
            }
            if ($this->date_end && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $this->date_end)) {
                $this->date_end = AppHelper::DateToDb($this->date_end);
            }

            // 2. จัดการ data_json (ป้องกันข้อมูลเดิมหาย)
            if (is_array($this->data_json)) {
                $data = $this->data_json;
                foreach ($data as $key => $value) {
                    // ตรวจสอบว่ามีรูปแบบ 00/00/0000 และเป็นฟิลด์วันที่
                    if (is_string($value) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
                        $data[$key] = AppHelper::DateToDb($value);
                    }
                }
                $this->data_json = $data;
            }
            return true;
        }
        return false;
    }

    //Relationships
    public function getAsset()
    {
        return $this->hasOne(Asset::class, ['code' => 'code']);
    }

    public function getAssetItem()
    {
        return $this->hasOne(Categorise::class, ['code' => 'asset_item'])->andOnCondition(['name' => 'asset_item']);
    }

    public function viewResult()
    {
        $resultHtml = '';
        if ($this->cal_result === 'pass') {
            $resultHtml = '<span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium p-1"><i class="bi bi-check-lg"></i> ผ่าน (Pass)</span>';
        } else {
            $resultHtml = '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill fw-medium"><i class="bi bi-x-lg"></i> ไม่ผ่าน (Fail)</span>';
        }

        return $resultHtml; // แสดงผล
    }

    /**
     * ฟังก์ชันสำหรับแสดง Label สถานะการยืม
     * @return string HTML Badge
     */
    public function getBorrowStatusLabel()
    {
        if ($this->is_borrowed == 1) {
            // กรณีถูกยืมอยู่: ใช้สีส้ม/เหลือง (Warning) เพื่อให้สะดุดตา
            return '<span class="badge rounded-pill bg-warning text-dark shadow-sm px-3">' .
                '<i class="bi bi-person-fill me-1"></i> ถูกยืม' .
                '</span>';
        }

        // กรณีว่าง: ใช้สีเขียว (Success) หรือสีฟ้าจางๆ ตามธีมระบบเดิม
        return '<span class="badge rounded-pill bg-opacity-10 text-success border border-success px-3">' .
            '<i class="bi bi-check-circle me-1"></i> คืน' .
            '</span>';
    }


    public function getReturnConditionLabel()
    {
        if (empty($this->actual_date)) {
            return '<span class="text-muted">-</span>';
        }

        if ($this->data_json['return_result'] === 'normal') {
            return '<span class="badge bg-success-subtle text-success border border-success px-3 rounded-pill"><i class="bi bi-check-circle me-1"></i>ปกติ</span>';
        }

        return '<span class="badge bg-danger-subtle text-danger border border-danger px-3 rounded-pill"><i class="bi bi-exclamation-triangle me-1"></i>ชำรุด</span>';
    }
}
