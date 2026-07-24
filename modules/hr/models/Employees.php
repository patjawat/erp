<?php

namespace app\modules\hr\models;

use app\components\AppHelper;
use app\components\CategoriseHelper;
use app\components\EmployeeHelper;
use app\components\ThaiDateHelper;
use app\models\Amphure;
use app\models\Categorise;
use app\models\District;
use app\models\Province;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\filemanager\models\Uploads;
use app\modules\health\models\HealthScreen;
use app\modules\hr\models\EmployeeDetail;
use app\modules\usermanager\models\User;
use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;


/**
 * This is the model class for table "employees".
 *
 * @property int           $id
 * @property int           $user_id
 * @property string|null   $ref
 * @property string|null   $avatar
 * @property resource|null $photo
 * @property string|null   $phone
 * @property string|null   $cid               เลขบัตรประชาชน
 * @property string|null   $email
 * @property string|null   $gender            เพศ
 * @property string|null   $prefix            คำนำหน้า
 * @property string        $fname             ชื่อ
 * @property string        $lname             นามสกุล
 * @property string|null   $fname_en          ชื่อ(TH)
 * @property string|null   $lname_en          นามสกุล(EN)
 * @property string|null   $birthday          วันเกิด
 * @property string|null   $join_date         เริ่มงาน
 * @property string|null   $end_date          ทำงานวันสุดท้าย
 * @property string|null   $address           ที่อยู่
 * @property int|null      $province          จังหวัด
 * @property int|null      $amphure           อำเภอ
 * @property int|null      $district          ตำบล
 * @property int|null      $zipcode           รหัสไปรษณีย์
 * @property int|null      $employee_type_id  ประเภทพนักงาน (ใหม่)
 * @property int|null      $employee_position_group_id กลุ่มตำแหน่งพนักงาน (ใหม่)
 * @property int|null      $employee_position_id ตำแหน่งพนักงาน (ใหม่)
 * @property int|null      $position          ตำแหน่ง
 * @property int|null      $department        แผนก/ฝ่าย
 * @property string|null   $status            แผนก/ฝ่าย
 * @property string|null   $data_json
 * @property string|null   $banking           ข้อมูลบัญชีธนาคาร
 * @property string|null   $family            สมาชิกในครอบครัว
 * @property string|null   $education         การศึกษา
 * @property string|null   $experience        ประสบการณ์
 * @property string|null   $emergency_contact ติดต่อในกรณีฉุกเฉิน
 * @property string|null   $updated_at
 * @property string|null   $created_at
 * @property int|null      $created_by        ผู้สร้าง
 * @property int|null      $updated_by        ผู้แก้ไข
 */
class Employees extends Yii\db\ActiveRecord
{
    public $show;

    public $fulladdress;
    public $fullname;
    public $fullname_en;
    public $age;
    public $age_y;
    public $blood_group;
    public $born;
    public $ethnicity;
    public $nationality;
    public $religion;
    public $marry;
    public $_age_generation;
    public $_female;
    public $_female_percen;
    public $_male;
    public $_male_percen;
    public $cnt;
    public $title;
    public $_groupname;
    public $_groupcode;
    public $_depcode;
    public $_position1;
    public $_position2;
    public $_position3;
    public $_position4;
    public $_position5;
    public $_position6;
    public $_position7;
    public $q_department;
    public $q;
    public $date_end;
    public $age_join_date;  // อายุราชการ
    public $all_status;  // สถานะทั้งหมด
    public $range1;  // ช่วงตัวเลข
    public $range2;  // ช่วงตัวเลข
    public $user_register; // สถานะลงทะเยียน

    public static function tableName()
    {
        return 'employees';
    }

    public static function primaryKey()
    {
        return ['id'];
    }

    public function rules()
    {
        return [
            [['user_id', 'fname', 'lname', 'phone', 'cid', 'branch'], 'required'],
            [['user_id', 'province', 'amphure', 'district', 'zipcode', 'department', 'created_by', 'updated_by', 'employee_type_id', 'employee_position_group_id', 'employee_position_id'], 'integer'],
            [['photo'], 'string'],
            [[
                'birthday',
                'data_json',
                'updated_at',
                'created_at',
                'cid',
                'code',
                'emp_id',
                'education',
                'employee_type_id',
                'employee_position_group_id',
                'employee_position_id',
                'position_group',
                'position_name',
                'position_number',
                'position_level',
                'position_type',
                'salary',
                'show',
                'cnt',
                'title',
                '_groupname',
                '_groupcode',
                '_depcode',
                '_position1',
                '_position2',
                '_position3',
                '_position4',
                '_position5',
                '_position6',
                '_position7',
                '_age_generation',
                '_female',
                '_male',
                '_female_percen',
                '_male_percen',
                'age_join_date',
                'fulladdress',
                'expertise',
                'position_manage',
                'age_y',
                'range1',
                'range2',
                'q_department',
                'user_register',
                'q',
                'branch',
                'work_shift'
            ], 'safe'],
            [['ref', 'avatar', 'email', 'address', 'status'], 'string', 'max' => 255],
            [['gender', 'prefix'], 'string', 'max' => 20],
            [['phone'], 'string', 'max' => 20],
            [['fname', 'lname', 'fname_en', 'lname_en'], 'string', 'max' => 200],
            ['phone', 'unique', 'targetClass' => 'app\modules\hr\models\Employees', 'message' => 'เบอร์โทรศัพท์ถูกใช้แล้ว'],
            // [['cid'], 'validateIdCard'],
        ];
    }

    public function checkOwner()
    {
        $model = self::find()->where(['fname' => $this->fname, 'lname' => $this->lname])->one();
        if (!$model) {
            $this->addError('fname', 'ไม่พบชื่อในระบบ');
            $this->addError('lname', 'ไม่พบนามสกุลในระบบ');
        }
    }

    // ตรวจสอลหมายเลขบัตรประชาชน
    public function validateIdCard()
    {
        try {
            $id = str_split(str_replace('-', '', $this->cid));  // ตัดรูปแบบและเอา ตัวอักษร ไปแยกเป็น array $id
            $sum = 0;
            $total = 0;
            $digi = 13;

            for ($i = 0; $i < 12; ++$i) {
                $sum = $sum + (intval($id[$i]) * $digi);
                --$digi;
            }
            $total = (11 - ($sum % 11)) % 10;

            if ($total != $id[12]) {  // ตัวที่ 13 มีค่าไม่เท่ากับผลรวมจากการคำนวณ ให้ add error
                $this->addError('cid', 'หมายเลขบัตรประชาชนไม่ถูกต้อง');
            }
            // code...
        } catch (\Throwable $th) {
            // throw $th;
        }
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'ref' => 'Ref',
            'avatar' => 'Avatar',
            'photo' => 'Photo',
            'phone' => 'หมายเลขโทรศัพท์',
            'cid' => 'เลขบัตรประชาชน',
            'email' => 'Email',
            'gender' => 'เพศ',
            'prefix' => 'คำนำหน้า',
            'fname' => 'ชื่อ',
            'lname' => 'นามสกุล',
            'fname_en' => 'ชื่อ(TH)',
            'lname_en' => 'นามสกุล(EN)',
            'birthday' => 'วันเกิด',
            'address' => 'ที่อยู่',
            'province' => 'จังหวัด',
            'amphure' => 'อำเภอ',
            'district' => 'ตำบล',
            'zipcode' => 'รหัสไปรษณีย์',
            'employee_type_id' => 'ประเภทพนักงาน (ใหม่)',
            'employee_position_group_id' => 'กลุ่มตำแหน่งพนักงาน (ใหม่)',
            'employee_position_id' => 'ตำแหน่งพนักงาน (ใหม่)',
            'position' => 'ตำแหน่ง',
            'department' => 'แผนก/ฝ่าย',
            'position_manage' => 'ตำแหน่งบริหาร',
            'education' => 'การศึกษา',
            'status' => 'สถานะ',
            'join_date' => 'วันที่เริ่มงาน',
            'branch' => 'สาขา',
            'work_shift' => 'ประเภทเวร',
            'data_json' => 'Data Json',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
        ];
    }

    private const LEGACY_EMPLOYEE_TYPE_MAP = [
        'PT1' => 1,
        'PT2' => 2,
        'PT3' => 3,
        'PT4' => 4,
        'PT5' => 5,
        'PT6' => 4,
        'PT7' => 4,
        '1' => 1,
        '2' => 2,
        '3' => 3,
        '4' => 4,
        '5' => 5,
        '6' => 4,
        '7' => 4,
    ];

    private function normalizeLegacyValue($value)
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function legacyEmployeeTypeId($legacyValue)
    {
        $legacyValue = $this->normalizeLegacyValue($legacyValue);
        if ($legacyValue === null) {
            return null;
        }

        $legacyKey = strtoupper($legacyValue);
        if (isset(self::LEGACY_EMPLOYEE_TYPE_MAP[$legacyKey])) {
            return self::LEGACY_EMPLOYEE_TYPE_MAP[$legacyKey];
        }
        if (isset(self::LEGACY_EMPLOYEE_TYPE_MAP[$legacyValue])) {
            return self::LEGACY_EMPLOYEE_TYPE_MAP[$legacyValue];
        }

        if (ctype_digit($legacyValue)) {
            $code = Categorise::find()
                ->select('code')
                ->where(['id' => (int) $legacyValue, 'name' => 'position_type'])
                ->scalar();
            if ($code !== null) {
                $code = strtoupper(trim((string) $code));
                if (isset(self::LEGACY_EMPLOYEE_TYPE_MAP[$code])) {
                    return self::LEGACY_EMPLOYEE_TYPE_MAP[$code];
                }
            }
        }

        return null;
    }

    private function legacyCategoriseCode($legacyValue, string $name)
    {
        $legacyValue = $this->normalizeLegacyValue($legacyValue);
        if ($legacyValue === null) {
            return null;
        }

        if (ctype_digit($legacyValue)) {
            $code = Categorise::find()
                ->select('code')
                ->where(['id' => (int) $legacyValue, 'name' => $name])
                ->scalar();
            if ($code !== null) {
                $code = trim((string) $code);
                if ($code !== '') {
                    return $code;
                }
            }
        }

        return $legacyValue;
    }

    private function legacyEmployeePositionGroupId($legacyValue)
    {
        $legacyCode = $this->legacyCategoriseCode($legacyValue, 'position_group');
        if ($legacyCode === null) {
            return null;
        }

        $groupId = EmployeePositionGroup::find()
            ->select('id')
            ->where(['legacy_code' => $legacyCode])
            ->scalar();

        return $groupId !== null ? (int) $groupId : null;
    }

    private function legacyEmployeePositionId($legacyValue)
    {
        $legacyCode = $this->legacyCategoriseCode($legacyValue, 'position_name');
        if ($legacyCode === null) {
            return null;
        }

        $positionId = EmployeePosition::find()
            ->select('id')
            ->where(['legacy_code' => $legacyCode])
            ->scalar();

        return $positionId !== null ? (int) $positionId : null;
    }

    private function syncEmployeeMasterFields(): void
    {
        if (!$this->hasAttribute('employee_type_id') || !$this->hasAttribute('employee_position_group_id') || !$this->hasAttribute('employee_position_id')) {
            return;
        }

        if (
            Yii::$app->db->getTableSchema(EmployeeType::tableName(), true) === null ||
            Yii::$app->db->getTableSchema(EmployeePositionGroup::tableName(), true) === null ||
            Yii::$app->db->getTableSchema(EmployeePosition::tableName(), true) === null
        ) {
            return;
        }

        if (empty($this->employee_type_id)) {
            $this->employee_type_id = $this->legacyEmployeeTypeId($this->position_type);
        }

        if (empty($this->employee_position_group_id)) {
            $this->employee_position_group_id = $this->legacyEmployeePositionGroupId($this->position_group);
        }

        if (empty($this->employee_position_id)) {
            $this->employee_position_id = $this->legacyEmployeePositionId($this->position_name);
        }
    }

    public function beforeSave($insert)
    {
        $this->birthday = AppHelper::DateToDb($this->birthday);
        try {
            $this->cid = AppHelper::SaveCid($this->cid);
        } catch (\Throwable $th) {
            //throw $th;
        }
        if ($this->prefix == 'นาย') {
            $this->gender = 'ชาย';
        } else {
            $this->gender = 'หญิง';
        }
        $this->syncEmployeeMasterFields();
        // ป้องกัน Array to string conversion: คอลัมน์ data_json รับ string (JSON) แต่ฟอร์มโหลดเป็น array
        if (is_array($this->data_json)) {
            $this->data_json = json_encode($this->data_json, JSON_UNESCAPED_UNICODE);
        }

        return parent::beforeSave($insert);
    }

    public function afterFind()
    {
        try {
            // decode data_json จาก DB (string) เป็น array เพื่อให้อ่านค่าและแสดงในฟอร์มได้ถูกต้อง
            if (is_string($this->data_json)) {
                $this->data_json = json_decode($this->data_json, true) ?? [];
            }
            if (!is_array($this->data_json)) {
                $this->data_json = [];
            }
            if ($this->UpdateFormDetail()['new_fullname']) {  // ถ้ามีการเปลี่ยนชื่อ
                $this->fullname = $this->UpdateFormDetail()['new_fullname'];
            } else {
                $this->fullname = $this->prefix . $this->fname . ' ' . $this->lname;
            }
            $this->date_end = AppHelper::DateFormDb($this->UpdateFormDetail()['date_end']);
            $this->fullname_en = ($this->prefix == 'นาย' ? 'Mr.' : 'Miss.') . $this->fname_en . ' ' . $this->lname_en;
            $this->birthday = AppHelper::DateFormDb($this->birthday);
            $this->age_join_date = AppHelper::Age(AppHelper::DateFormDb($this->joinDate()));
            $this->age = AppHelper::Age($this->birthday)['year'];
            $this->blood_group = isset($this->data_json['blood_group']) ? $this->data_json['blood_group'] : null;
            $this->born = isset($this->data_json['born']) ? $this->data_json['born'] : null;
            $this->ethnicity = isset($this->data_json['ethnicity']) ? $this->data_json['ethnicity'] : null;
            $this->nationality = isset($this->data_json['nationality']) ? $this->data_json['nationality'] : null;
            $this->religion = isset($this->data_json['religion']) ? $this->data_json['religion'] : null;
            $this->marry = isset($this->data_json['marry']) ? $this->data_json['marry'] : null;
            $this->fulladdress = $this->address . ' ' . (isset($this->data_json['address2']) ? $this->data_json['address2'] : null);
        } catch (\Throwable $th) {
        }
        $this->age_y = AppHelper::Age($this->birthday, true)['year'];
        parent::afterFind();
    }


    public function Upload($ref, $name)
    {
        return FileManagerHelper::FileUpload($ref, $name);
    }

    //แสดงประเภทของการทำงาน ปกติ,หรือ 8 ชั่วโมง
    public function viewWorkType()
    {
        $workTypename = '';
        switch ($this->work_shift) {
            case 'normal':
                $workTypename = 'ปกติ';
                break;

            case 'shift':
                $workTypename = 'เวร 8 ชั่วโมง';
                break;

            default:
                $workTypename = '';
                break;
        }
        return $workTypename;
    }
    // ข้อมูลเบื้องต้นของบุคลากร
    public function getInfo()
    {
        try {
            return [
                'id' => $this->id,
                'fullname' => $this->fullname,
                'img' => $this->getImg(),
                'photo' => $this->showAvatar(),
                'avatar' => $this->getAvatar(false),
                'position' => $this->positionName(),
                'position_type' => $this->positionTypeName(),
                'employee_type' => $this->employeeTypeName(),
                'employee_position_group' => $this->employeePositionGroupName(),
                'employee_position' => $this->employeePositionName(),
                'department' => $this->department,
                'department_name' => $this->departmentName(),
                'signature' => $this->signature(),
                'phone' => $this->phone,
                'healthData' => $this->healthData()
            ];
        } catch (\Throwable $th) {
            return [
                'id' => '',
                'fullname' => '',
                'photo' => '',
                'avatar' => '',
                'position' => '',
                'position_type' => '',
                'employee_type' => '',
                'employee_position_group' => '',
                'employee_position' => '',
                'department' => '',
                'department_name' => '',
                'healthData' => ''
            ];
        }
        return [
            'id' => $this->id,
            'fullname' => $this->fullname,
            'photo' => $this->showAvatar(),
            'avatar' => $this->getAvatar(false),
            'position' => $this->positionName(),
            'position_type' => $this->positionTypeName(),
            'employee_type' => $this->employeeTypeName(),
            'employee_position_group' => $this->employeePositionGroupName(),
            'employee_position' => $this->employeePositionName(),
            'department' => $this->department,
            'department_name' => $this->departmentName(),
        ];
    }
    public function getImg()
    {
        return Html::img('@web/img/loading.gif', [
            'class' => 'avatar avatar-sm bg-primary text-white lazyload',
            'data' => [
                'expand' => '-20',
                'sizes' => 'auto',
                'src' => $this->showAvatar(),
            ],
        ]);
    }


    public function getAvatar($showAge = true, $msg = '')
    {
        $img = Html::img('@web/img/loading.gif', [
            'class' => 'avatar avatar-sm bg-primary text-white lazyload',
            'data' => [
                'expand' => '-20',
                'sizes' => 'auto',
                'src' => $this->showAvatar(),
            ],
        ]);

        $fullname = $this->fullname;
        $position = $this->positionName();
        $msg = $msg;

        if ($msg !== '') {
            return <<< HTML
        <div class="d-flex align-items-center">
            {$img}
            <div class="avatar-detail">
                <p class="mb-0 small fw-bold text-muted">{$fullname}</p>
                <p class="text-muted mb-0 fs-12">{$msg}</p>
            </div>
        </div>
        HTML;
        }

        $age = $showAge ? '<p class="text-muted">อายุ ' . Html::encode($this->age) . '</p>' : '';

        return <<<HTML
    <div class="d-flex align-items-center">
        {$img}
        <div class="avatar-detail">
            <h6 class="mb-0 fs-14"  
               data-bs-toggle="tooltip" 
               data-bs-placement="top"
               data-bs-custom-class="custom-tooltip"
               data-bs-title="ดูเพิ่มเติม...">
                {$fullname}
            </h6>
            <p class="text-muted mb-0 fs-12">{$position}</p>
            {$age}
        </div>
    </div>
    HTML;
    }


    public function isDirector()
    {

        return EmployeeHelper::isDirector($this->user_id);
    }
    /**
     * หาปีที่เกษียณอายุ ครบ 60 ปี โดยกำหนดให้เป็นสิ้นเดือนกันยายนของปีนั้นๆ.
     *
     * @return string คืนค่าวันที่เกษียณอายุ
     */
    public function Retire()
    {
        try {
            $birthday = AppHelper::DateToDb($this->birthday);
            $age = 60;  // รับค่าอายุที่จะเกษียณ

            $color = '';

            if (substr($birthday, 5, 2) >= 10) {
                ++$age;
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
                $color = 'success';
            } elseif (100 - $progress >= 30) {
                $color = 'warning';
            } else {
                $color = 'danger';
            }

            return [
                'date' => AppHelper::DateFormDb($date_retire),
                'progress' => 100 - $progress,
                'color' => $color,
            ];

            // code...
        } catch (\Throwable $th) {
            return [
                'date' => '0000-00-00',
                'progress' => 0,
                'color' => 'danger',
            ];
        }
    }

    // ครบ 60 ปี
    public function year60()
    {
        try {
            $sql = '';

            $date =  \Yii::$app
                ->db
                ->createCommand('SELECT DATE_ADD(:date, INTERVAL 60 YEAR)')
                ->bindValue('date', AppHelper::DateToDb($this->birthday))
                ->queryScalar();
            return ThaiDateHelper::formatThaiDate($date);

            // $date = explode('-', AppHelper::DateToDb($this->birthday));
            // return Yii::$app->thaiFormatter->asDate(($date[0] + 60) . '-' . $date[1] . '-' . $date[2], 'medium');
            // code...
        } catch (\Throwable $th) {
            // throw $th;
        }
    }

    public function leftYear60()
    {
        return \Yii::$app
            ->db
            ->createCommand('SELECT (60-FLOOR(DATEDIFF(CURRENT_DATE, :date) / 365)) AS age')
            ->bindValue('date', AppHelper::DateToDb($this->birthday))
            ->queryScalar();
    }

    // การครลกำหนด
    public function due()
    {
        $employeeTypeId = $this->hasAttribute('employee_type_id') ? ($this->employee_type_id ?: $this->legacyEmployeeTypeId($this->position_type)) : $this->legacyEmployeeTypeId($this->position_type);

        if ((int) $employeeTypeId === 4) {
            $text = 'ครบกำหนดสัญญา';
        } else {
            $text = 'ครบกำหนดเกษียณ';
        }

        return [
            'label' => $text,
            'due_date' => '',
        ];
    }
    // Category List

    // ตำแหน่งปัจจุบัน
    public function nowPosition()
    {
        $model = EmployeeDetail::find()
            ->where(['emp_id' => $this->id, 'name' => 'position'])
            ->orderBy([
                new Expression("JSON_EXTRACT(data_json, '\$.date_start') asc"),
                'id' => SORT_DESC,
            ])
            ->one();
        if ($model) {
            return [
                'date_start' => (isset($model->data_json['date_start']) ? $model->data_json['date_start'] : ''),
                'position_name' => (isset($model->data_json['position_name_text']) ? $model->data_json['position_name_text'] : ''),
                'position_number' => (isset($model->data_json['position_nposition_numberme_text']) ? $model->data_json['position_number'] : ''),
            ];
        } else {
            return [
                'date_start' => '',
                'position_name' => '',
                'position_number' => '',
            ];
        }
    }

    // ประวัติการดำรงตำแหน่งล่าสุด (date_start มากสุด) → ตั้งแต่วันที่ / รายการเคลื่อนไหว / เลขประจำตำแหน่ง
    public function latestPositionData()
    {
        $model = EmployeeDetail::find()
            ->where(['emp_id' => $this->id, 'name' => 'position'])
            ->orderBy([
                new Expression("JSON_EXTRACT(data_json, '\$.date_start') desc"),
                'id' => SORT_DESC,
            ])
            ->one();

        if (!$model) {
            return ['date_start' => '', 'movement' => '', 'position_number' => ''];
        }

        $json = is_array($model->data_json) ? $model->data_json : [];

        return [
            'date_start' => $json['date_start'] ?? '',
            'movement' => $json['statuslist'] ?? '',
            'position_number' => $json['position_number'] ?? '',
        ];
    }

    // count form position Show Dashbroad
    // แสดงหน้า dashboard
    public function WorkgroupSummary($dep_id)
    {
        $data = [];
        foreach (EmployeeType::listItems() as $key => $value) {
            $data[] = self::find()->where(['department' => $dep_id, 'employee_type_id' => (int) $key])->count();
        }

        return $data;
    }

    // กลุ่มงาน
    public function leader()
    {
        try {
            $model = Categorise::findOne(['code' => $this->empDepartment->category_id, 'name' => 'workgroup']);
            $leader = isset($model->data_json['leader']) ? $model->data_json['leader'] : null;
            if ($leader) {
                return self::findOne($leader);
            } else {
                return null;
            }

            return null;
            // code...
        } catch (\Throwable $th) {
            return null;
        }
    }

    public function leaderUser()
    {
        try {
            $model = Organization::find()->where(['id' => $this->department])->one();
            $employee = self::find()->where(['id' => $model->data_json['leader1']])->one();

            if ($model) {
                return [
                    'avatar' => $employee->getAvatar(false),
                    'leader1' => $model->data_json['leader1'],
                    'leader1_fullname' => $employee->fullname,
                    'leader1_position' => isset($employee->data_json['position_name_text']) ? $employee->data_json['position_name_text'] : '',
                    'leader2' => $model->data_json['leader2'],
                    'leader2_fullname' => $model->data_json['leader2_fullname'],
                ];
            } else {
                return [
                    'avatar' => '',
                    'leader1_user_id' => '',
                    'leader1' => '',
                    'leader1_fullname' => '',
                    'leader2' => '',
                    'leader2_fullname' => '',
                ];
            }
        } catch (\Throwable $th) {
            return [
                'avatar' => '',
                'leader1' => '',
                'leader1_user_id' => '',
                'leader1_fullname' => '',
                'leader2' => '',
                'leader2_fullname' => '',
            ];
        }
    }
    public function generalMenu()
    {
        return [
            [
                'title' => 'ข้อมูลพื้นฐาน',
                'icon' => '<i data-lucide="user-round" class="lucide-icon text-primary"></i>',
                'name' => '',
                'subtitle' => 'ข้อมูลพื้นฐานตามบัตรประชาชน',
                'count' => 0,
            ],
            [
                'title' => 'ข้อมูลตรวจสุขภาพประจำปี',
                'icon' => '<i data-lucide="heart-pulse" class="lucide-icon text-primary"></i>',
                'name' => 'health',
                'subtitle' => 'ข้อมูลตรวจสุขภาพประจำปี',
                'count' => 0,
            ],
            [
                'title' => 'ข้อมูลประวัติการดำรงตำแหน่ง',
                'icon' => '<i data-lucide="briefcase" class="lucide-icon text-primary"></i>',
                'name' => 'position',
                'subtitle' => 'ข้อมูลการบรรจุ/ต่อสัญญาจ้าง/เลื่อนขั้น',
                'count' => count($this->positions),
            ],
            [
                'title' => 'ประวัติคำอธิบายงาน (JD)',
                'icon' => '<i data-lucide="file-text" class="lucide-icon text-primary"></i>',
                'name' => 'job_description_history',
                'subtitle' => 'ดู JD ทุก Revision และช่วงเวลาที่มีผล',
                'count' => $this->getJdHistoryCount(),
            ],
            [
                'title' => 'ข้อมูลการศึกษา',
                'icon' => '<i data-lucide="graduation-cap" class="lucide-icon text-primary"></i>',
                'name' => 'education',
                'subtitle' => 'ประวัติการศึกษา/คุณวุฒิต่างๆ',
                'count' => count($this->educations),
            ],
            [
                'title' => 'ข้อมูลครอบครัว',
                'icon' => '<i data-lucide="users-2" class="lucide-icon text-primary"></i>',
                'name' => 'family',
                'subtitle' => 'ประวัติสมาชิกในครอบครัว',
                'count' => 0,
            ],
            [
                'title' => 'รางวัลเชิดชูเกียรติ',
                'icon' => '<i data-lucide="trophy" class="lucide-icon text-primary"></i>',
                'name' => 'award',
                'subtitle' => 'ประวัติการรับรางวัลต่างๆ',
                'count' => 0,
            ],
            [
                'title' => 'ข้อมูลประวัติเครื่องราชอิสริยาภรณ์',
                'icon' => '<i data-lucide="medal" class="lucide-icon text-primary"></i>',
                'name' => 'insignia',
                'subtitle' => 'เหรียญ และตรา อันเป็นเครื่องประดับยศ',
                'count' => 0,
            ],
            [
                'title' => 'ข้อมูลการเปลี่ยนชื่อและสกุล',
                'icon' => '<i data-lucide="file-signature" class="lucide-icon text-primary"></i>',
                'name' => 'rename',
                'subtitle' => 'ประวัติการเปลี่ยนชื่อ นามสกุล',
                'count' => 0,
            ],
            [
                'title' => 'ข้อมูลใบประกอบวิชาชีพ',
                'icon' => '<i data-lucide="id-card" class="lucide-icon text-primary"></i>',
                'name' => 'license',
                'subtitle' => 'ใบอนุญาตต่างๆ/ใบประกอบวิชาชีพ',
                'count' => 0,
            ],
            [
                'title' => 'ข้อมูลการอบรมดูงาน',
                'icon' => '<i data-lucide="plane-landing" class="lucide-icon text-primary"></i>',
                'name' => 'develop',
                'subtitle' => 'ประวัติการสัมมนา ฝึกอบรม ดูงาน ศึกษาต่อ',
                'count' => 0,
            ],
            [
                'title' => 'Training Roadmap',
                'icon' => '<i data-lucide="signpost" class="lucide-icon text-primary"></i>',
                'name' => 'training_roadmap',
                'url' => ['/hr/training-roadmap/employee', 'emp_id' => $this->id],
                'subtitle' => 'แผนพัฒนา สมรรถนะ และจุดประเมิน',
                'count' => $this->getTrainingPlans()->count(),
            ],
            [
                'title' => 'การรับทุน',
                'icon' => '<i data-lucide="hand-coins" class="lucide-icon text-primary"></i>',
                'name' => 'scholarships',
                'subtitle' => 'ประวัติการรับทุน',
                'count' => 0,
            ],
            [
                'title' => 'ข้อมูลการรับโทษทางวินัย',
                'icon' => '<i data-lucide="gavel" class="lucide-icon text-primary"></i>',
                'name' => 'blame',
                'subtitle' => 'ประวัติการรับโทษทางวินัย',
                'count' => 0,
            ],
            [
                'title' => 'ข้อมูลสวัสดิการ',
                'icon' => '<i data-lucide="heart-handshake" class="lucide-icon text-primary"></i>',
                'name' => 'benefit',
                'subtitle' => 'สิทธิประโยชน์ สวัสดิการที่ได้รับ',
                'count' => 0,
            ],
            [
                'title' => 'ข้อมูลปฏิบัติหน้าที่/ราชการ',
                'icon' => '<i data-lucide="cog" class="lucide-icon text-primary"></i>',
                'name' => 'position_manage',
                'subtitle' => 'ประวัติการแต่งตั้งตำแหน่งบริหาร',
                'count' => 0,
            ],
            [
                'title' => 'ลายเซ็น',
                'icon' => '<i data-lucide="pen-tool" class="lucide-icon text-primary"></i>',
                'name' => 'signature',
                'subtitle' => 'ลายเซ็น',
                'count' => 0,
            ],
        ];
    }

    /**
     * Groups the employee profile navigation without changing the legacy
     * menu definitions or their routes.
     */
    public function generalMenuGroups()
    {
        $items = [];
        foreach ($this->generalMenu() as $item) {
            $items[$item['name']] = $item;
        }

        $definitions = [
            [
                'key' => 'general',
                'title' => 'ข้อมูลทั่วไป',
                'subtitle' => 'ประวัติส่วนบุคคลและการทำงาน',
                'icon' => 'user-round',
                'items' => [
                    '', 'position', 'job_description_history', 'education',
                    'family', 'rename', 'license', 'award', 'insignia',
                    'scholarships', 'benefit', 'position_manage', 'blame', 'signature',
                ],
            ],
            [
                'key' => 'development',
                'title' => 'การพัฒนาและประเมินผล',
                'subtitle' => 'การอบรมและเส้นทางพัฒนา',
                'icon' => 'chart-no-axes-combined',
                'items' => ['develop', 'training_roadmap'],
            ],
            [
                'key' => 'health',
                'title' => 'สุขภาพและความปลอดภัย',
                'subtitle' => 'ข้อมูลสุขภาพที่เกี่ยวข้องกับงาน',
                'icon' => 'heart-pulse',
                'items' => ['health'],
            ],
        ];

        $groups = [];
        foreach ($definitions as $definition) {
            $groupItems = [];
            foreach ($definition['items'] as $name) {
                if (array_key_exists($name, $items)) {
                    $groupItems[] = $items[$name];
                }
            }
            if ($groupItems) {
                $definition['items'] = $groupItems;
                $groups[] = $definition;
            }
        }

        return $groups;
    }

    /** จำนวนหัวข้อในคำอธิบายงาน (JD) ของพนักงาน */
    public function getJdSectionCount()
    {
        if (!class_exists(\app\modules\jd\models\JdEmployee::class)) {
            return 0;
        }
        $jd = \app\modules\jd\models\JdEmployee::findCurrent((int) $this->id);
        return $jd ? count($jd->sections) : 0;
    }

    public function getCurrentJd()
    {
        return \app\modules\jd\models\JdEmployee::findCurrent((int) $this->id);
    }

    public function getJdHistory()
    {
        return $this->hasMany(\app\modules\jd\models\JdEmployee::class, ['emp_id' => 'id'])
            ->orderBy(['revision_no' => SORT_DESC, 'effective_from' => SORT_DESC, 'id' => SORT_DESC]);
    }

    public function getJdHistoryCount(): int
    {
        return (int) \app\modules\jd\models\JdEmployee::find()->where(['emp_id' => $this->id])->count();
    }

    // คำนำหน้า
    public function ListPrefixTh()
    {
        return CategoriseHelper::PrefixTh();
    }

    // ครอบครัว
    public function ListFamilyRelation()
    {
        return CategoriseHelper::FamilyRelation();
    }

    public function ListPrefixEn()
    {
        return CategoriseHelper::PrefixEn();
    }

    // ภูมิลำเนาเดิม
    public function ListBorn()
    {
        return CategoriseHelper::Born();
    }

    // สัญชาติ
    public function ListEthnicity()
    {
        return CategoriseHelper::Nationality();
    }

    // เชื่อชาติ
    public function ListNationality()
    {
        return CategoriseHelper::Nationality();
    }

    // สถานภาพสมรส
    public function ListMarry()
    {
        return CategoriseHelper::Marry();
    }

    // หมู่โลหิต
    public function ListBlood()
    {
        return CategoriseHelper::Blood();
    }

    // ศาสนา
    public function ListReligion()
    {
        return CategoriseHelper::Religion();
    }

    // ประเภทการเปลียนชื่อ
    public function ListRenameType()
    {
        return CategoriseHelper::RenameType();
    }

    public function ListEmployeeType()
    {
        return EmployeeType::listItems();
    }

    public function ListEmployeePositionGroup($employeeTypeId = null)
    {
        return EmployeePositionGroup::listItems();
    }

    public function ListEmployeePosition($employeeTypeId = null, $groupId = null)
    {
        return EmployeePosition::listItems($employeeTypeId, $groupId);
    }

    public function ListPositionType()
    {
        return EmployeeType::listItems();
    }

    // ระดับของข้าราชการ
    public function ListPositionLevel()
    {
        return CategoriseHelper::PositionLevel();
    }

    // กลุ่มงาน
    public function ListPositionGroup()
    {
        return EmployeePositionGroup::listItems();
    }

    // ตำแหน่งบริหาร
    public function ListPositionManage()
    {
        return CategoriseHelper::PositionManage();
    }

    // ความเชี่ยวชาญ
    public function ListExpertise()
    {
        return CategoriseHelper::Expertise();
    }

    // ชื่อตำแหน่ง
    public function ListPositionName()
    {
        return EmployeePosition::listItems();
    }

    // ชื่อตำแหน่ง Ajax Template
    public function ListPositionNameAjaxTemplate()
    {
        return EmployeePosition::listItems();
    }

    // สถานะ
    public function ListStatus()
    {
        return CategoriseHelper::EmpStatus();
    }

    // แผนก
    public static function ListDepartment()
    {
        return CategoriseHelper::Department();
    }

    // รายการ สัมมนา ฝึกอบรม ดูงาน ศึกษาต่อ และข้อมูลรายงาน
    public function ListDevelop()
    {
        return CategoriseHelper::Develop();
    }

    // ลักษณะการไป
    public function ListFollowby()
    {
        return CategoriseHelper::Followby();
    }
    // End Category list


    //ส่ง line
    public function SendMsg($msg) {}
    // คำนวนวันที่เริ่มต้นทำงานจากการแต่งตั้ง
    public function joinDate()
    {
        try {
            // $model = EmployeeDetail::find()->where(['name' => 'position', 'emp_id' => $this->id])->all();
            // return count($model);
            $queryCheck = \Yii::$app
                ->db
                ->createCommand("SELECT count(id) FROM employee_detail WHERE emp_id = :emp_id AND name = 'position'")
                ->bindValue(':emp_id', $this->id)
                ->queryScalar();
            // return count($model);
            if ($queryCheck >= 2) {
                $sql = "SELECT CAST(JSON_UNQUOTE(JSON_EXTRACT(e.data_json,'\$.date_start'))  AS DATE) as date_start FROM employee_detail e WHERE e.emp_id = $this->id AND JSON_EXTRACT(e.data_json,'\$.date_start') > (SELECT e2.data_json->'\$.date_start' as date_start FROM employee_detail e2 WHERE e2.emp_id =  $this->id AND JSON_EXTRACT(e2.data_json,'\$.status') = '2' ORDER BY date_start desc limit 1) limit 1;";
                $query = \Yii::$app
                    ->db
                    ->createCommand($sql)
                    //  ->bindParam(':emp_id', '2')
                    ->queryOne();

                //  return $query;
                if ($query) {
                    return $query['date_start'];
                } else {
                    $data = EmployeeDetail::find()
                        ->where(['emp_id' => $this->id, 'name' => 'position'])
                        ->orderBy([
                            new Expression("JSON_EXTRACT(data_json, '\$.date_start') asc"),
                            'id' => SORT_DESC,
                        ])
                        ->one();

                    return $data->data_json['date_start'];
                }
            } else {
                $data = EmployeeDetail::find()
                    ->where(['emp_id' => $this->id, 'name' => 'position'])
                    ->orderBy([
                        new Expression("JSON_EXTRACT(data_json, '\$.date_start') asc"),
                        'id' => SORT_DESC,
                    ])
                    ->one();

                return $data->data_json['date_start'];
            }
            // code...
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function workLife()
    {
        return AppHelper::Age(AppHelper::DateFormDb($this->joinDate()), false);
        // AppHelper::Age($this->birthday, true);
        // return $this->birthday;
        // return $this->joinDate();
    }
    // อายุงานคำนวนปี
    public function workYear()
    {
        return AppHelper::Age(AppHelper::DateFormDb($this->joinDate()), true);
    }


    // วันลาออก เกษียร
    public function endDate()
    {
        $sql = "SELECT data_json->'\$.date_start' as date_start FROM employee_detail WHERE emp_id = 1 AND JSON_EXTRACT(data_json,'\$.status') = '2' ORDER BY data_json->'\$.date_start' ASC limit 1";
        $query = \Yii::$app
            ->db
            ->createCommand($sql)
            ->queryOne();

        return $query['date_start'];
    }

    // แสดงตำแหน่งล่าสุด

    public function UpdateFormDetail()
    {
        $position = $this->getDetail('position');
        $rename = $this->getDetail('rename');

        return [
            'date_start' => isset($position->data_json['date_start']) ? $position->data_json['date_start'] : '',
            'date_end' => isset($position->data_json['date_end']) ? $position->data_json['date_end'] : '',
            'old_fullname' => $rename ? $rename->old_fullname : null,
            'new_fullname' => $rename ? $rename->new_fullname : null,
        ];
    }

    // สมาชิกที่อยู่ในแผนกเดียวกัน
    public function listMenberOnDep()
    {
        return self::find()
            ->where(['department' => $this->department])
            ->all();
    }

    public function getDetail($name)
    {
        $id = $this->id;
        $sql = "SELECT *,JSON_EXTRACT(data_json, '\$.date_start') AS date_start FROM `employee_detail` WHERE name=:name AND emp_id = :emp_id ORDER by date_start desc LIMIT 1;";
        $query = \Yii::$app
            ->db
            ->createCommand($sql)
            ->bindParam(':emp_id', $id)
            ->bindParam(':name', $name)
            ->queryOne();
        if ($query && ($model = EmployeeDetail::findOne($query['id'])) !== null) {
            return $model;
        }
    }


    //คะแนนการประเมินของแต่ละปี
    public function pointYear()
    {
        try {
            $sql = "SELECT x.* 
                FROM (
                    SELECT 
                        data_json->>'$.point' AS point,
                        (IF(MONTH(STR_TO_DATE(data_json->>'$.date_start', '%Y-%m-%d')) > 9,
                            YEAR(STR_TO_DATE(data_json->>'$.date_start', '%Y-%m-%d')) + 1,
                            YEAR(STR_TO_DATE(data_json->>'$.date_start', '%Y-%m-%d')) ) + 543) AS thai_year
                    FROM `employee_detail`
                    WHERE name = 'position' 
                    AND emp_id = :emp_id
                    AND data_json->>'$.point' IS NOT NULL
                    AND data_json->>'$.point' <> ''
                ) AS x 
                GROUP BY x.thai_year;";
            return  Yii::$app->db->createCommand($sql, [':emp_id' => $this->id])->queryAll();
        } catch (\Throwable $th) {
            return [];
        }
    }

    // คะแนนการประเมินในปี
    public function point($thai_year)
    {
        $sql = "SELECT x.* FROM(SELECT 
	data_json->>'$.point' as point,
    data_json->>'$.date_start' AS date_start,
     (IF(MONTH(STR_TO_DATE(data_json->>'$.date_start', '%Y-%m-%d')) > 9,YEAR(STR_TO_DATE(data_json->>'$.date_start', '%Y-%m-%d')) + 1,
            YEAR(STR_TO_DATE(data_json->>'$.date_start', '%Y-%m-%d')) ) + 543) AS thai_year
        FROM `employee_detail` WHERE name = 'position' AND emp_id = :emp_id) as x where x.thai_year = :thai_year";
        return    Yii::$app->db->createCommand($sql, [':thai_year' => $thai_year, ':emp_id' => $this->id])->queryAll();
    }

    // สิทวันลาพักผ่อนสะสม
    public function LeaveLimit()
    {
        $sql = "SELECT 
                        CASE 
                            WHEN pt.code IN ('PT1', 'PT6') THEN 
                                CASE 
                                    WHEN TIMESTAMPDIFF(YEAR, e.join_date, CURDATE()) >= 10 THEN 30
                                    WHEN TIMESTAMPDIFF(YEAR, e.join_date, CURDATE()) >= 1 THEN 10
                                    ELSE 0
                                END
                            WHEN pt.code IN ('PT2', 'PT3') THEN 
                                CASE 
                                    WHEN TIMESTAMPDIFF(YEAR, e.join_date, CURDATE()) >= 1 THEN 15
                                    ELSE 0
                                END
                            WHEN pt.code IN ('PT5') THEN 
                                CASE 
                                    WHEN TIMESTAMPDIFF(YEAR, e.join_date, CURDATE()) >= 0.5 THEN 0
                                    ELSE 0
                                END
                            ELSE 0
                        END AS leave_limit
                    FROM `employees` e
                     LEFT JOIN categorise pt ON pt.code = e.position_type AND pt.name = 'position_type'
                    WHERE e.status = 1 
                    AND e.id = :id;";

        $command = Yii::$app->db->createCommand($sql);
        $command->bindValue(':id', $this->id);
        return $command->queryScalar();
    }

    // section Relationships
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getProvincName()
    {
        if ($this->province) {
            return $this->hasOne(Province::class, ['id' => 'province']);
        } else {
            return null;
        }
    }

    public function getAmphureName()
    {
        return $this->hasOne(Amphure::class, ['id' => 'amphure']);
    }

    public function getDistrictcName()
    {
        return $this->hasOne(District::class, ['id' => 'district']);
    }

    public function getPdpa()
    {
        return $this->hasOne(EmployeeDetail::class, ['emp_id' => 'id'])->andOnCondition(['name' => 'pdpa']);
    }

    public function viewPdpaData()
    {
        try {
            $pdpaDate = $this->pdpa?->data_json['date'];
            return Yii::$app->thaiDate->toThaiDate($pdpaDate, true, false);
        } catch (\Throwable $th) {
            return '-';
        }
    }
    // แสดงสถานะ
    //      public function statusName()
    //      {
    //          $model = CategoriseHelper::CategoriseByCodeName($this->status, 'emp_status');
    //          if ($model) {
    //              return $model->title;
    //          } else {
    //              return null;
    //          }
    //      }
    // สถานะ
    public function statusName()
    {
        return isset($this->statusName) ? $this->statusName->title : $this->status;
    }

    public function employeeTypeName()
    {
        try {

                return $this->employeeType->title;

            // return $this->positionTypeName();
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function employeePositionGroupName()
    {
        try {
            if (
                $this->hasAttribute('employee_position_group_id')
                && Yii::$app->db->getTableSchema(EmployeePositionGroup::tableName(), true) !== null
                && isset($this->employeePositionGroup)
                && isset($this->employeePositionGroup->title)
                && $this->employeePositionGroup->title != ''
            ) {
                return $this->employeePositionGroup->title;
            }

            return $this->positionGroupName();
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function employeePositionName()
    {
        try {
            if (
                $this->hasAttribute('employee_position_id')
                && Yii::$app->db->getTableSchema(EmployeePosition::tableName(), true) !== null
                && isset($this->employeePosition)
                && isset($this->employeePosition->title)
                && $this->employeePosition->title != ''
            ) {
                return $this->employeePosition->title;
            }

            return $this->positionName();
        } catch (\Throwable $th) {
            return false;
        }
    }

    // แสดงชื่อตำแหน่ง
    public function positionName($arr = [])
    {
        try {

            $level = $this->positionLevelName() ? $this->positionLevelName() : '';
            $title = '';
            $position = $this->employeePosition;
            if ((!isset($position) || empty($position->title)) && trim((string) $this->position_name) !== '') {
                $legacyPositionId = $this->legacyEmployeePositionId($this->position_name);
                if ($legacyPositionId !== null) {
                    $position = EmployeePosition::findOne($legacyPositionId);
                }
            }

            if (isset($position) && !empty($position->title)) {
                $title = $position->title;
            }

            if (array_key_exists('icon', $arr) && $arr['icon'] == true) {
                $isIcon = '<i class="bi bi-check2-circle text-primary me-1"></i>';
            } else {
                $isIcon = null;
            }
            return (isset($this->status) && $title !== '') ? $isIcon . $title . ' ' . $level : '-';
            //code...
        } catch (\Throwable $th) {
            return '-';
        }
    }

    /**
     * ตรวจว่ามีข้อมูลตำแหน่งสำหรับระบบทั่วไป (เช่น โมดูล me) หรือไม่
     * นับทั้งคอลัมน์ position_name และประวัติตำแหน่งใน employee_detail (แท็บตำแหน่งใน HR)
     * เพื่อไม่ให้ถูกบล็อกทั้งที่หน้า HR แสดงรายการตำแหน่งแล้ว
     */
    public function hasPersonnelPositionConfigured(): bool
    {
        if (trim((string) ($this->position_name ?? '')) !== '' || ($this->hasAttribute('employee_position_id') && !empty($this->employee_position_id))) {
            return true;
        }
        try {
            foreach ($this->positions as $detail) {
                $text = trim((string) ($detail->data_json['position_name_text'] ?? ''));
                if ($text !== '' && strcasecmp($text, '-') !== 0) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return false;
    }

    // แสดงประเภทตำแหน่ง
    public function positionTypeName()
    {
        try {
            $employeeType = $this->employeeType;
            if ((!isset($employeeType) || empty($employeeType->title)) && trim((string) $this->position_type) !== '') {
                $legacyTypeId = $this->legacyEmployeeTypeId($this->position_type);
                if ($legacyTypeId !== null) {
                    $employeeType = EmployeeType::findOne($legacyTypeId);
                }
            }

            if (isset($employeeType) && !empty($employeeType->title)) {
                return $employeeType->title;
            }

            return trim((string) $this->position_type) !== '' ? $this->position_type : '-';
        } catch (\Throwable $th) {
            return false;
        }
    }

    // แสดงประเภท/กลุ่มงาน
    public function positionGroupName()
    {
        try {
            $group = $this->employeePositionGroup;
            if ((!isset($group) || empty($group->title)) && trim((string) $this->position_group) !== '') {
                $legacyGroupId = $this->legacyEmployeePositionGroupId($this->position_group);
                if ($legacyGroupId !== null) {
                    $group = EmployeePositionGroup::findOne($legacyGroupId);
                }
            }

            if (isset($group) && !empty($group->title)) {
                return $group->title;
            }

            return trim((string) $this->position_group) !== '' ? $this->position_group : '-';
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function positionManageName()
    {
        // return isset($this->positionManage) ? $this->positionManage->title : $this->position_manage;
        return '-';
    }


    // แสดงระดับของข้าราชการ
    public function positionLevelName()
    {
        return $this->positionLevel ? $this->positionLevel->title : $this->position_level;
    }

    public function educationName()
    {
        $model = CategoriseHelper::CategoriseByCodeName($this->education, 'education');

        return $model ? $model->title : '-';
        // return isset($this->educations) ? $this->educations->title : $this->education;
    }

    public function departmentName()
    {

        try {
            return $this->empDepartment?->name ?? 'ไม่ระบุ';
        } catch (\Throwable $th) {
            return 'ไม่ระบุ';
        }
    }

    // แยกโครงสร้างสังกัดของบุคลากรเป็น กลุ่มงาน (lvl 1) และ หน่วยงาน (lvl 2)
    // ถ้าบุคลากรสังกัด node ระดับ 2 -> unit = node, group = parent (lvl 1)
    // ถ้าสังกัด node ระดับ 0-1 -> group = node, ไม่มี unit
    public function orgUnits()
    {
        $group = null; // กลุ่มงาน
        $unit = null;  // หน่วยงาน
        try {
            $node = $this->empDepartment;
            if ($node) {
                if ((int) $node->lvl >= 2) {
                    $unit = $node;
                    $group = $node->parents(1)->one();
                } else {
                    $group = $node;
                }
            }
        } catch (\Throwable $th) {
        }

        return ['group' => $group, 'unit' => $unit];
    }

    // เช็คว่าบุคลากรเป็นหัวหน้า (leader1) ของ organization node ที่ระบุหรือไม่
    public function isOrgLeader($node)
    {
        if (!$node) {
            return false;
        }
        $json = $node->data_json;
        if (!is_array($json)) {
            $json = json_decode((string) $json, true) ?: [];
        }
        $leader = isset($json['leader1']) && $json['leader1'] !== '' ? (int) $json['leader1'] : null;

        return $leader !== null && $leader === (int) $this->id;
    }

    public function expertiseName()
    {
        return isset($this->empExpertise) ? $this->empExpertise->title : $this->expertise;
    }

    public function getstatusName()
    {
        return $this->hasOne(Categorise::class, ['code' => 'status'])->andOnCondition(['name' => 'emp_status']);
    }

    //เชื่อมกับแผนก/กลุ่มงาน
    public function getEmpDepartment()
    {
        return $this->hasOne(Organization::class, ['id' => 'department']);
    }
    //สิทการใช้งาน
    public function getAuthAssignment()
    {
        return $this->hasOne(AuthAssignment::className(), ['user_id' => 'user_id']);
    }

    public function getPositionName()
    {
        return $this->hasOne(EmployeePosition::class, ['id' => 'employee_position_id']);
    }

    public function getPositionType()
    {
        return $this->hasOne(EmployeeType::class, ['id' => 'employee_type_id']);
    }

    public function getPositionLevel()
    {
        return $this->hasOne(Categorise::class, ['code' => 'position_level'])->andOnCondition(['name' => 'position_level']);
    }

    // Relation ประเภท/กลุ่มงาน
    public function getPositionGroup()
    {
        return $this->hasOne(EmployeePositionGroup::class, ['id' => 'employee_position_group_id']);
    }

    public function getEmployeeType()
    {
        return $this->hasOne(EmployeeType::class, ['id' => 'employee_type_id']);
    }

    public function getEmployeePositionGroup()
    {
        return $this->hasOne(EmployeePositionGroup::class, ['id' => 'employee_position_group_id']);
    }

    public function getEmployeePosition()
    {
        return $this->hasOne(EmployeePosition::class, ['id' => 'employee_position_id']);
    }

    public function getEmpExpertise()
    {
        return $this->hasOne(Categorise::class, ['code' => 'expertise'])->andOnCondition(['name' => 'position_expertise']);
    }

    public function getEmpPosition()
    {
        return $this->hasMany(Categorise::class, ['emp_id' => 'id']);
    }


    // detail Items

    // public function getAvatar()
    // {
    //     return $this->hasOne(Uploads::class, ['ref' => 'ref']);
    // }

    // การศึกษ
    public function getEducations()
    {
        return $this
            ->hasMany(EmployeeDetail::class, ['emp_id' => 'id'])
            ->orderBy([
                new Expression("JSON_EXTRACT(data_json, '\$.date_end') desc"),
                'id' => SORT_DESC,
            ])
            ->andOnCondition(['name' => 'education']);
    }

    // ประวัติการดำรงตำแหน่ง
    public function getPositions()
    {
        return $this
            ->hasMany(EmployeeDetail::class, ['emp_id' => 'id'])
            ->orderBy([
                new Expression("JSON_EXTRACT(data_json, '\$.date_start') desc"),
                'id' => SORT_DESC,
            ])
            ->andOnCondition(['name' => 'position']);
    }

    // ประวัติการเปลี่ยนชื่อ
    public function getHisRename()
    {
        return $this->hasMany(EmployeeDetail::class, ['emp_id' => 'id'])->andOnCondition(['name' => 'rename']);
    }

    //  ข้อมูลประวัติการตรวจสุขภาพ
    public function getHealth()
    {
        return $this->hasMany(EmployeeDetail::class, ['emp_id' => 'id'])->andOnCondition(['name' => 'health']);
    }

    // ใบอนุญาต
    public function getLicenses()
    {
        return $this->hasMany(EmployeeDetail::class, ['emp_id' => 'id'])->andOnCondition(['name' => 'license_name']);
    }

    // ประวัติครอบครัว
    public function getFamilys()
    {
        return $this->hasMany(EmployeeDetail::class, ['emp_id' => 'id'])->andOnCondition(['name' => 'family']);
    }

    // รางวัลเชิดชูเกียรติ
    public function getAwards()
    {
        return $this->hasMany(EmployeeDetail::class, ['emp_id' => 'id'])->andOnCondition(['name' => 'award']);
    }

    // เครื่องราชอิสริยาภรณ์
    public function getInsignias()
    {
        return $this->hasMany(EmployeeDetail::class, ['emp_id' => 'id'])->andOnCondition(['name' => 'insignia']);
    }

    // ประวัติการ สัมมนา ฝึกอบรม ดูงาน ศึกษาต่อ
    public function getDevelop()
    {
        return $this->hasMany(EmployeeDetail::class, ['emp_id' => 'id'])->andOnCondition(['name' => 'develop']);
    }

    // ประวัติการรับทุน
    public function getScholarships()
    {
        return $this->hasMany(EmployeeDetail::class, ['emp_id' => 'id'])->andOnCondition(['name' => 'scholarships']);
    }

    // ข้อมูลการรับโทษทางวินัย
    public function getBlames()
    {
        return $this->hasMany(EmployeeDetail::class, ['emp_id' => 'id'])->andOnCondition(['name' => 'blame']);
    }

    // ข้อมูลสวัสดิการ
    public function getBenefits()
    {
        return $this->hasMany(EmployeeDetail::class, ['emp_id' => 'id'])->andOnCondition(['name' => 'benefit']);
    }

    // ประวัติการรับตำแหน่งบริกสน
    public function getPositionManage()
    {
        return $this->hasMany(EmployeeDetail::class, ['emp_id' => 'id'])->andOnCondition(['name' => 'position_manage']);
    }
    //ทะเบียนอบรม/ประชุม/ดูงาน
    public function getDevelopmentMenber()
    {
        return $this->hasMany(DevelopmentDetail::class, ['emp_id' => 'id'])->andOnCondition(['name' => 'member']);
    }

    /**
     * Training Roadmaps assigned to this employee.
     */
    public function getTrainingPlans()
    {
        return $this->hasMany(EmployeeTrainingPlan::class, ['emp_id' => 'id'])
            ->orderBy(['id' => SORT_DESC]);
    }


    // End Relationships

    public function ShowAvatar($class = null)
    {
        try {
            $model = Uploads::find()->where(['ref' => $this->ref, 'name' => $class ? $class : 'avatar'])->one();
            return Url::to(['/filemanager/uploads/get-image', 'id' => $model->id]);
        } catch (\Throwable $th) {
            return \Yii::getAlias('@web') . '/img/placeholder_cid.png';
        }
    }
    //file ลายเซ็น
    public function signature()
    {
        try {
            $model = Uploads::find()->where(['ref' => $this->ref, 'name' => 'signature'])->one();
            if ($model) {
                $filepath = FileManagerHelper::getUploadPath() . $this->ref . '/' . $model->real_filename;
                //  return FileManagerHelper::getImg($model->id);
                return $filepath;
            } else {
                return null;
            }
        } catch (\Throwable $th) {
            // throw $th;
            return null;
        }
    }

    //แสดงรายเซ็น
    public function SignatureShow()
    {
        try {
            $model = Uploads::find()->where(['ref' => $this->ref, 'name' => 'signature'])->one();
            if ($model) {
                return FileManagerHelper::getImg($model->id);
            } else {
                return null;
            }
        } catch (\Throwable $th) {
            // throw $th;
            return null;
        }
    }
    // การดึง File จาก Path โดยตรง
    public function SignatureFilePath()
    {
        try {
            $model = Uploads::find()->where(['ref' => $this->ref, 'name' => 'signature'])->one();
            if ($model) {
                return FileManagerHelper::getFilePath($model->id);
            } else {
                return null;
            }
        } catch (\Throwable $th) {
            // throw $th;
            return null;
        }
    }



    public function fullname()
    {
        return $this->prefix . $this->fname . ' ' . $this->lname;
    }

    //คำนวนสิทธิวันลาสะสม
    public function LeaveRole()
    {
        $sql = "SELECT 
                concat(e.fname,' ',e.lname) as fullname,
                e.position_type,
                pt.title,
                DATEDIFF(CURDATE(), e.join_date) / 365 AS years_of_service,
                    CASE 
                        -- ข้าราชการและลูกจ้างประจำ
                        WHEN pt.code IN ('PT1','PT6') THEN 
                            CASE 
                                WHEN DATEDIFF(CURDATE(), join_date) / 365 >= 10 THEN 30
                                WHEN DATEDIFF(CURDATE(), join_date) / 365 >= 1 THEN 10
                                ELSE 0
                            END
                            -- พนักงานราชการและพนักงานกระทรวงสาธารณสุข
                        WHEN pt.code IN ('PT2', 'PT3') THEN 
                            CASE 
                                WHEN DATEDIFF(CURDATE(), join_date) / 365 >= 1 THEN LEAST(15, 15 + 0) -- รวมปีปัจจุบัน + สะสม
                                WHEN DATEDIFF(CURDATE(), join_date) / 365 < 0.5 THEN 0
                                ELSE 0
                            END

                        -- ลูกจ้างชั่วคราวและลูกจ้างรายวัน
                        WHEN pt.code IN ('PT5') THEN 
                            CASE 
                                WHEN DATEDIFF(CURDATE(), join_date) / 365 >= 0.5 THEN 0
                                ELSE 0
                            END

                        -- Default เผื่อสำหรับพนักงานประเภทอื่น
                    ELSE 0
                    END AS leave_days
                    FROM `employees` e
                    LEFT JOIN categorise pt ON pt.code = e.position_type AND pt.name ='position_type'
                    WHERE e.status = 1 AND e.id <> 1 AND e.id = :id";
        $querys = Yii::$app->db->createCommand($sql)
            ->bindValue(':id', $this->id)
            ->queryOne();
        if ($querys !== false) {
            $emp = $querys;
        } else {
            $emp = null;
        }

        return $emp;
    }

    public function teamGroup()
    {
        try {
            return TeamGroupDetail::find()
                ->joinWith('teamGroup') // join กับ TeamGroup ผ่านความสัมพันธ์
                ->where(['team_group_detail.emp_id' => $this->id])
                ->andWhere(['team_group_detail.name' => 'committee'])
                ->all();
        } catch (\Throwable $th) {

            return [];
        }
    }

    //หนังสือที่ส่งถึงฉัน
    public function listDocumentMe()
    {
        // $emp = UserHelper::GetEmployee();
        // $department = $emp->department;
        $emp = $this->id;
        $department = $this->department;

        $sql = "SELECT `documents`.id,thai_year, TRIM(doc_number) AS doc_number,topic 
            FROM `documents_detail` 
            LEFT JOIN `documents` ON `documents_detail`.`document_id` = `documents`.`id` 
            WHERE (`to_id` = :department) AND (`name` = 'department')
            
            UNION
            
            SELECT  `documents`.id,thai_year, TRIM(doc_number) AS doc_number,topic  
            FROM `documents_detail` 
            LEFT JOIN `documents` ON `documents_detail`.`document_id` = `documents`.`id` 
            WHERE (`to_id` = :emp) AND (`name` = 'tags')";

        $querys = Yii::$app->db->createCommand($sql)
            ->bindValue(':department', $department)
            ->bindValue(':emp', $emp)
            ->queryAll();
        return $querys;
    }

    //ห้อมูลการตรวจสุขภาพ
    public function healthData()
    {
        $latestHealth = HealthScreen::find()
    ->where(['emp_id' => $this->id])
    // แก้ไขจาก $.screeningDate เป็น $.screening_date ให้ตรงกับข้อมูลจริง
    ->orderBy(['date_checkup' => SORT_DESC])
    ->one();

        // การเรียกใช้งาน
        if ($latestHealth) {
            return [
            'id' => $latestHealth->id,    
            'result' => $latestHealth->getBmiResult()
            ];
        }else{
            return [
                'id' => '',
                'result' => []
            ];
        }
    }

    /**
     * รายการนัดตรวจสุขภาพที่กำลังจะมาถึง (วันที่นัด >= วันนี้)
     * ใช้แจ้งเตือนในหน้า /me
     * @return HealthScreen[]
     */
    public function getUpcomingHealthAppointments()
    {
        $today = date('Y-m-d');
        return HealthScreen::find()
            ->where(['emp_id' => $this->id])
            ->andWhere(['not', ['appointment_date' => null]])
            ->andWhere(['>=', 'appointment_date', $today])
            ->andWhere(['in', 'health_status', ['SCREEN', 'CONFIRM']])
            ->orderBy(['appointment_date' => SORT_ASC])
            ->limit(5)
            ->all();
    }
}
