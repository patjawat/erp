<?php

namespace app\modules\hr\models;

use app\components\AppHelper;
use app\components\CategoriseHelper;
use app\models\Categorise;
use app\modules\filemanager\components\FileManagerHelper;
use dstotijn\yii2jsv\JsonSchemaValidator;
use Yii;

/**
 * This is the model class for table "employee_detail".
 *
 * @property int $id
 * @property int|null $emp_id
 * @property string|null $name
 * @property string|null $data_json
 * @property string|null $updated_at
 * @property string|null $created_at
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 */
class EmployeeDetail extends \yii\db\ActiveRecord
{
    public $education;  // ระดับการศึกษา
    public $major;  // สาขาวิชาเอก
    public $institute;  // สำเร็จจากสถาบัน
    public $rename_type;  // รายการที่เปลี่ยน
    public $old_prefix;  // คำนำหน้าชื่อเดิม
    public $old_fname;  // ชื่อเดิม
    public $old_lname;  // สกุลเดิม
    public $old_fullname;  // ชื่อสกุลเดิม
    public $new_fullname;  // ชื่อสกุลใหม่
    public $family_fullname;  // ชื่อสกุลใหม่
    public $status;  // สถานะ
    public $status_name;  // สถานะ
    public $send_date;  // วันที่เสนอขอ
    public $result_date;  // วันที่รับรางวัล
    public $award_name;  // ชื่อรางวัล
    public $award_company_name;  // หน่วยงานที่มอบรางวัล
    public $salary;  // เงินเดือน
    public $date_start;  // วันเริ่มต้น
    public $date_end;  // วันสิ้นสุด
    public $bmi;  // ดัชนีมวลกาย
    public $bloodPressure;  // ความดันโลหิต
    public $thai_year;  // ความดันโลหิต

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'employee_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['emp_id', 'created_by', 'updated_by'], 'integer'],
            [['data_json', 'updated_at', 'created_at', 'ref', 'status_name', 'status', 'bmi', 'thai_year'], 'safe'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'emp_id' => 'Emp ID',
            'name' => 'Name',
            'data_json' => 'Data Json',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
        ];
    }

    public function afterFind()
    {
        $this->bmi = isset($this->data_json['bmi']) ? $this->data_json['bmi'] : null;  // ดัชนีมวลกาย
        $this->bloodPressure = isset($this->data_json['bloodPressure']) ? $this->data_json['bloodPressure'] : null;  // ดัชนีมวลกาย
        // try {
        // ประวัติการศึกษา
        $this->education = isset($this->data_json['education']) ? $this->data_json['education'] : null;  // การศึกษา
        $this->institute = isset($this->data_json['institute']) ? $this->data_json['institute'] : null;  // จบจากสถาบัน
        $this->major = isset($this->data_json['major']) ? $this->data_json['major'] : null;  // วิชาเอก

        // ประวัติการเปลี่ยนชื่อ
        $this->rename_type = isset($this->data_json['rename_type']) ? $this->data_json['rename_type'] : null;  // รายการที่เปลี่ยน
        $this->old_fullname = ((isset($this->data_json['old_prefix']) ? $this->data_json['old_prefix'] : null) . (isset($this->data_json['old_fname']) ? $this->data_json['old_fname'] : null) . ' ' . (isset($this->data_json['old_lname']) ? $this->data_json['old_lname'] : null)) ?: null;  // สกุลเดิม
        $this->new_fullname = ((isset($this->data_json['new_prefix']) ? $this->data_json['new_prefix'] : null) . (isset($this->data_json['new_fname']) ? $this->data_json['old_fname'] : null) . ' ' . (isset($this->data_json['new_lname']) ? $this->data_json['new_lname'] : null)) ?: null;  // สกุลใหม่
        $this->family_fullname = ((isset($this->data_json['prefix']) ? $this->data_json['prefix'] : null) . (isset($this->data_json['fname']) ? $this->data_json['fname'] : null) . ' ' . (isset($this->data_json['lname']) ? $this->data_json['lname'] : null)) ?: null;  // ชื่อเต็มครอบครัว
        $this->status = isset($this->data_json['status']) ? $this->data_json['status'] : null;  // สถานะ
        $this->status_name = $this->GetStatusName();  // สถานะ
        $this->send_date = isset($this->data_json['send_date']) ? $this->data_json['send_date'] : null;  // วันที่เสนอชื่อรางวัล
        $this->result_date = isset($this->data_json['result_date']) ? $this->data_json['result_date'] : null;  // วันที่รับรางวัล
        $this->award_name = isset($this->data_json['award_name']) ? $this->data_json['award_name'] : null;  // ชื่อรางวัล
        // $this->award_company_name = isset($this->data_json['award_company_name']) ? $this->data_json['award_company_name'] : null; //หน่วยงานที่มอบรางวัล
        $this->date_start = isset($this->data_json['date_start']) ? AppHelper::DateFormDb($this->data_json['date_start']) : null;  // วันเริ่มต้น
        $this->date_end = isset($this->data_json['date_end']) ? AppHelper::DateFormDb($this->data_json['date_end']) : null;  // วันเริ่มต้น
        try {
            $this->salary = (isset($this->data_json['salary']) || $this->data_json['salary'] > 0) ? $this->data_json['salary'] : 0;  // หน่วยงานที่มอบรางวัล
            // code...
        } catch (\Throwable $th) {
            // throw $th;
            $this->salary = 0;
        }

        // } catch (\Throwable $th) {
        // }
    }

    public function Upload($ref, $name)
    {
        return FileManagerHelper::FileUpload($ref, $name);
    }

    // detail Items
    public function getEducations()
    {
        return $this->belongsTo(Employees::class, ['id' => 'emp_id']);
    }

    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    // สถานะ
    public function GetStatusName()
    {
        $model = CategoriseHelper::CategoriseByCodeName($this->status, 'emp_status');
        if ($model) {
            return $model->title;
        } else {
            return false;
        }
    }

    // นับปี พ.ศ.

    public function rangYearText()
    {
        try {
            $year1 = explode('-', $this->data_json['date_start'])[0] + 543;
            $year2 = explode('-', $this->data_json['date_end'])[0] + 543;
            return $year1 . ' - ' . $year2;
        } catch (\Throwable $th) {
            return false;
        }
    }

    // public function dateStartTh(){

    //    $this->data_json['date_start']) ? AppHelper::DateFormDb($item->data_json['date_start']) : ''

    // แสดงตำแหน่ง

    public function positionName()
    {
        try {
            $data = is_array($this->data_json) ? $this->data_json : [];
            return $data['employee_position_text']
                ?? $data['position_name_text']
                ?? '-';
            // return $this->positionName->positionGroup->positionType->title;
        } catch (\Throwable $th) {
            return false;
        }
    }

    // แสดงประเภทตำแหน่ง

    public function positionTypeName()
    {
        try {
            $data = is_array($this->data_json) ? $this->data_json : [];
            return $data['employee_type_text']
                ?? $data['position_type_text']
                ?? '-';
            // return $this->positionName->positionGroup->positionType->title;
        } catch (\Throwable $th) {
            return false;
        }
    }

    // แสดงประเภท/กลุ่มงาน
    public function positionGroupName()
    {
        try {
            $data = is_array($this->data_json) ? $this->data_json : [];
            return $data['employee_position_group_text']
                ?? $data['position_group_text']
                ?? '-';
        } catch (\Throwable $th) {
            return false;
        }
    }

    // ระดับการสึกษา
    public function GetEducationItems()
    {
        return CategoriseHelper::Education();
    }

    // วิชาเอก
    public function GetMajorItems()
    {
        return CategoriseHelper::Major();
    }

    // จบจากสถาบัน
    public function GetInstituteItems()
    {
        return CategoriseHelper::Institute();
    }
    //โรคประจำตัว
    public function getChronicDiseasesList()
    {
        return Categorise::find()->where(['name' => 'chronic_diseases'])->all();
    }

    // แสดงรายการตำแหน่งบริหาร
    public function PositionManageList()
    {
        $sql = 'SELECT
            t1.id, t1.root, t1.lft, t1.rgt, t1.lvl, 
            t1.id as position_id,
            t1.name as position_name, 
            t2.id as position_group_id,
            t2.name as position_group_name,
            t3.id as position_type_id,
            t3.name as position_type_name
            FROM tree t1 
            JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1
            JOIN tree t3 ON t2.lft BETWEEN t3.lft AND t3.rgt AND t2.lvl = t3.lvl + 1
            WHERE t2.name = :name';

        $query = Yii::$app
            ->db
            ->createCommand($sql)
            ->bindValue(':name', 'บริหาร')
            ->queryAll();
        return $query;
    }


    public function getBmiResult()
    {
        try {
            $bmi = $this->data_json['bmi'] ?? 0;
            return AppHelper::getBmiResult($bmi);
        } catch (\Throwable $th) {
            return NULL;
        }
    }

    //สรุปภาพรวมการคัดการองสุขภาพพนักงาน
    // public function healthSummary()
    // {
    //     $totalEmp = Employees::find()->where(['status' => 1])
    //         ->andWhere(['<>', 'id', 1])->count();

    //     $stats = EmployeeDetail::find()
    //         ->select([
    //             'screened' => "COUNT(*)",
    //             'thin' => "SUM(CASE WHEN data_json->'$.bmi' < 18.5 THEN 1 ELSE 0 END)",
    //             'normal' => "SUM(CASE WHEN data_json->'$.bmi' >= 18.5 AND data_json->'$.bmi' < 23 THEN 1 ELSE 0 END)",
    //             'overweight' => "SUM(CASE WHEN data_json->'$.bmi' >= 23 AND data_json->'$.bmi' < 25 THEN 1 ELSE 0 END)",
    //             'obese1' => "SUM(CASE WHEN data_json->'$.bmi' >= 25 AND data_json->'$.bmi' < 30 THEN 1 ELSE 0 END)",
    //             'obese2' => "SUM(CASE WHEN data_json->'$.bmi' >= 30 THEN 1 ELSE 0 END)",
    //         ])
    //         ->where(['name' => 'health'])
    //         ->andWhere(new \yii\db\Expression("data_json->'$.thai_year' = :year", [':year' => $this->thai_year]))
    //         ->asArray()
    //         ->one();

    //     $screened = (int)$stats['screened'];

    //     // ฟังก์ชันช่วยคำนวณ %
    //     $getPercent = function ($value, $total) {
    //         return ($total > 0) ? number_format(($value / $total) * 100, 1) : "0.0";
    //     };

    //     return [
    //         'total_emp' => $totalEmp,
    //         'screened' => $screened,
    //         'percent_screened' => $getPercent($screened, $totalEmp),
    //         'details' => [
    //             [
    //                 'label' => 'น้ำหนักน้อย / ผอม',
    //                 'count' => (int)$stats['thin'],
    //                 'percent' => $getPercent($stats['thin'], $screened),
    //                 'color' => 'info'
    //             ],
    //             [
    //                 'label' => 'ปกติ (สุขภาพดี)',
    //                 'count' => (int)$stats['normal'],
    //                 'percent' => $getPercent($stats['normal'], $screened),
    //                 'color' => 'success'
    //             ],
    //             [
    //                 'label' => 'ท้วม / เริ่มอ้วน',
    //                 'count' => (int)$stats['overweight'],
    //                 'percent' => $getPercent($stats['overweight'], $screened),
    //                 'color' => 'warning'
    //             ],
    //             [
    //                 'label' => 'อ้วนระดับ 1',
    //                 'count' => (int)$stats['obese1'],
    //                 'percent' => $getPercent($stats['obese1'], $screened),
    //                 'color' => 'danger'
    //             ],
    //             [
    //                 'label' => 'อ้วนระดับ 2 (อ้วนมาก)',
    //                 'count' => (int)$stats['obese2'],
    //                 'percent' => $getPercent($stats['obese2'], $screened),
    //                 'color' => 'dark'
    //             ],
    //         ]
    //     ];
    // }
}
